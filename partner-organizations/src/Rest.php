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
                'page' => [
                    'sanitize_callback' => 'absint',
                    'default' => 1,
                ],
                'per_page' => [
                    'sanitize_callback' => 'absint',
                    'default' => 20,
                ],
            ],
        ]);
    }

    public function partners(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $client_id = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (! $this->rate_limiter->is_allowed($client_id)) {
            return new \WP_Error('partner_organizations_rate_limited', __('Rate limit exceeded.', 'partner-organizations'), ['status' => 429]);
        }

        $page = max(1, (int) $request->get_param('page'));
        $per_page = max(1, min(100, (int) $request->get_param('per_page')));
        $cache_key = 'rest_partners_' . $page . '_' . $per_page;
        $cached = $this->cache->get($cache_key);
        if (false !== $cached) {
            return rest_ensure_response($cached);
        }

        $query = new \WP_Query($this->query_behavior->public_query_args([
            'paged' => $page,
            'posts_per_page' => $per_page,
        ]));

        $items = [];
        foreach ($query->posts as $post) {
            $items[] = [
                'id' => (int) $post->ID,
                'title' => get_the_title($post),
                'url' => get_permalink($post),
                'website_url' => (string) get_post_meta($post->ID, MetaBoxes::WEBSITE_META_KEY, true),
            ];
        }

        $this->cache->set($cache_key, $items);
        return rest_ensure_response($items);
    }
}
