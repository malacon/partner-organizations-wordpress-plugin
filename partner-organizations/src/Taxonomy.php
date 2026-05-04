<?php
/**
 * Partner Category taxonomy registration.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Taxonomy implements Hookable
{
    public const SLUG = 'partner_category';

    public function register(): void
    {
        add_action('init', [$this, 'register_taxonomy']);
    }

    public function register_taxonomy(): void
    {
        register_taxonomy(self::SLUG, [PostType::SLUG], [
            'labels' => [
                'name' => __('Partner Categories', 'partner-organizations'),
                'singular_name' => __('Partner Category', 'partner-organizations'),
            ],
            'hierarchical' => true,
            'public' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'partner-category'],
        ]);
    }
}
