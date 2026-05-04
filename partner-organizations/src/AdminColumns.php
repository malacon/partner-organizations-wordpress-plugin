<?php
/**
 * Partner Organization admin columns.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class AdminColumns implements Hookable
{
    public function register(): void
    {
        add_filter('manage_' . PostType::SLUG . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . PostType::SLUG . '_posts_custom_column', [$this, 'render_column'], 10, 2);
    }

    public function columns(array $columns): array
    {
        $columns['partner_organization_website'] = __('Website', 'partner-organizations');
        return $columns;
    }

    public function render_column(string $column_name, int $post_id): void
    {
        if ('partner_organization_website' !== $column_name) {
            return;
        }

        $website_url = (string) get_post_meta($post_id, MetaBoxes::WEBSITE_META_KEY, true);
        if ('' === $website_url) {
            echo '&mdash;';
            return;
        }

        printf('<a href="%1$s" rel="noopener noreferrer">%2$s</a>', esc_url($website_url), esc_html($website_url));
    }
}
