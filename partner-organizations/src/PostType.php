<?php
/**
 * Partner Organization post type registration.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class PostType implements Hookable
{
    public const SLUG = 'partner';

    public function register(): void
    {
        add_action('init', [$this, 'register_post_type']);
    }

    public function register_post_type(): void
    {
        register_post_type(self::SLUG, [
            'labels' => [
                'name' => __('Partner Organizations', 'partner-organizations'),
                'singular_name' => __('Partner Organization', 'partner-organizations'),
                'add_new_item' => __('Add New Partner Organization', 'partner-organizations'),
                'edit_item' => __('Edit Partner Organization', 'partner-organizations'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'thumbnail'],
            'capability_type' => ['partner', 'partners'],
            'map_meta_cap' => true,
            'capabilities' => [
                'create_posts' => 'create_partners',
            ],
            'show_in_rest' => true,
        ]);
    }
}
