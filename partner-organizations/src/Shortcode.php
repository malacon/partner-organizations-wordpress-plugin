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
            'per_page' => 20,
        ], (array) $attributes, 'partner_directory');

        $query = new \WP_Query($this->query_behavior->public_query_args([
            'posts_per_page' => max(1, min(100, absint($attributes['per_page']))),
        ]));

        if (! $query->have_posts()) {
            return '<p>' . esc_html__('No Partner Organizations found.', 'partner-organizations') . '</p>';
        }

        ob_start();
        echo '<ul class="partner-directory">';
        while ($query->have_posts()) {
            $query->the_post();
            printf('<li class="partner-directory__item"><a href="%1$s">%2$s</a></li>', esc_url(get_permalink()), esc_html(get_the_title()));
        }
        echo '</ul>';
        wp_reset_postdata();

        return (string) ob_get_clean();
    }
}
