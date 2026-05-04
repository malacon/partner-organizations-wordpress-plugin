<?php
/**
 * Public Partner Directory shortcode.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Shortcode implements Hookable
{
    public function __construct(private QueryBehavior $query_behavior)
    {
    }

    public function register(): void
    {
        add_shortcode('partner_directory', [$this, 'render']);
    }

    public function render(array|string $attributes = []): string
    {
        $attributes = shortcode_atts([
            'category' => '',
            'per_page' => 20,
        ], (array) $attributes, 'partner_directory');

        $query_args = $this->query_behavior->public_query_args([
            'posts_per_page' => max(1, min(100, absint($attributes['per_page']))),
            'no_found_rows' => true,
        ]);

        $category = sanitize_title((string) $attributes['category']);
        if ('' !== $category) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => Taxonomy::SLUG,
                    'field' => 'slug',
                    'terms' => $category,
                ],
            ];
        }

        $partner_query = new \WP_Query($query_args);

        wp_enqueue_style(
            'partner-organizations-directory',
            PARTNER_ORGANIZATIONS_URL . 'assets/css/partner-directory.css',
            [],
            PARTNER_ORGANIZATIONS_VERSION
        );

        ob_start();
        require PARTNER_ORGANIZATIONS_DIR . 'templates/partner-directory.php';
        wp_reset_postdata();

        return (string) ob_get_clean();
    }
}
