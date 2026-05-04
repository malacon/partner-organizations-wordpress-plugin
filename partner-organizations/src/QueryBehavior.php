<?php
/**
 * Shared Partner Organization query behavior.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class QueryBehavior implements Hookable
{
    public function register(): void
    {
        add_action('pre_get_posts', [$this, 'adjust_archive_query']);
    }

    public function public_query_args(array $args = []): array
    {
        return array_merge([
            'post_type' => PostType::SLUG,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
            'posts_per_page' => 20,
        ], $args);
    }

    public function adjust_archive_query($query): void
    {
        if (is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive(PostType::SLUG)) {
            return;
        }

        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }
}
