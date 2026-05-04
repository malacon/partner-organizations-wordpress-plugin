<?php
/**
 * Public REST API routes.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Rest implements Hookable
{
    private const NAMESPACE = 'partner-organizations/v1';

    public function __construct(
        private Cache $cache,
        private RateLimiter $rate_limiter,
        private QueryBehavior $query_behavior
    ) {
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/partners', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'partners'],
            'permission_callback' => '__return_true',
            'args' => [
                'category' => [
                    'sanitize_callback' => 'sanitize_title',
                    'default' => '',
                ],
                'page' => [
                    'validate_callback' => [$this, 'is_positive_integer_param'],
                    'default' => 1,
                ],
                'per_page' => [
                    'validate_callback' => [$this, 'is_positive_integer_param'],
                    'default' => 20,
                ],
            ],
        ]);
    }

    public function is_positive_integer_param(mixed $value): bool
    {
        if (is_int($value)) {
            return $value > 0;
        }

        if (! is_string($value) || '' === $value || ! ctype_digit($value)) {
            return false;
        }

        return (int) $value > 0;
    }

    public function partners(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        if (! $this->rate_limiter->is_allowed($this->rate_limiter->client_id())) {
            return new \WP_Error('partner_organizations_rate_limited', __('Rate limit exceeded.', 'partner-organizations'), [
                'status' => 429,
            ]);
        }

        $page = $this->positive_int_from_request($request, 'page', 1);
        $per_page = min(100, $this->positive_int_from_request($request, 'per_page', 20));
        if ($page <= 0 || $per_page <= 0) {
            return new \WP_Error('partner_organizations_invalid_pagination', __('page and per_page must be positive integers.', 'partner-organizations'), ['status' => 400]);
        }

        $category = sanitize_title((string) $request->get_param('category'));
        $cache_key = 'rest_partners_' . md5(wp_json_encode([
            'category' => $category,
            'page' => $page,
            'per_page' => $per_page,
        ]));
        $cached = $this->cache->get($cache_key);
        if (false !== $cached) {
            return rest_ensure_response($cached);
        }

        $envelope = $this->build_envelope($category, $page, $per_page);
        $this->cache->set($cache_key, $envelope);

        return rest_ensure_response($envelope);
    }

    private function build_envelope(string $category, int $page, int $per_page): array
    {
        $term = null;
        if ('' !== $category) {
            $term = get_term_by('slug', $category, Taxonomy::SLUG);
            if (! $term instanceof \WP_Term) {
                return $this->empty_envelope($page, $per_page);
            }
        }

        $query_args = [
            'paged' => $page,
            'posts_per_page' => $per_page,
        ];
        if ($term instanceof \WP_Term) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => Taxonomy::SLUG,
                    'field' => 'term_id',
                    'terms' => [(int) $term->term_id],
                ],
            ];
        }

        $query = new \WP_Query($this->query_behavior->public_query_args($query_args));
        $items = array_map([$this, 'format_partner_organization'], $query->posts);

        return [
            'data' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => (int) $query->found_posts,
                'total_pages' => (int) $query->max_num_pages,
            ],
        ];
    }

    private function format_partner_organization(\WP_Post $post): array
    {
        $website_url = (string) get_post_meta($post->ID, MetaBoxes::WEBSITE_META_KEY, true);

        return [
            'id' => (int) $post->ID,
            'name' => get_the_title($post),
            'website_url' => '' === $website_url ? null : esc_url_raw($website_url, ['http', 'https']),
            'logo' => $this->format_logo($post),
            'category' => $this->format_category($post),
        ];
    }

    private function format_logo(\WP_Post $post): ?array
    {
        $logo_id = (int) get_post_thumbnail_id($post);
        if ($logo_id <= 0) {
            return null;
        }

        $url = wp_get_attachment_image_url($logo_id, 'full');
        if (false === $url) {
            return null;
        }

        return [
            'id' => $logo_id,
            'url' => esc_url_raw($url),
            'alt' => (string) get_post_meta($logo_id, '_wp_attachment_image_alt', true),
        ];
    }

    private function format_category(\WP_Post $post): ?array
    {
        $terms = get_the_terms($post, Taxonomy::SLUG);
        if (! is_array($terms) || [] === $terms) {
            return null;
        }

        $term = $terms[0];

        return [
            'id' => (int) $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        ];
    }

    private function empty_envelope(int $page, int $per_page): array
    {
        return [
            'data' => [],
            'meta' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => 0,
                'total_pages' => 0,
            ],
        ];
    }

    private function positive_int_from_request(\WP_REST_Request $request, string $name, int $default): int
    {
        $value = $request->get_param($name);
        if (null === $value || '' === $value) {
            return $default;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return 0;
    }
}
