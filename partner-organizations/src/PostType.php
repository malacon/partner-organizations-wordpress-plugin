<?php
/**
 * Partner Organization post type registration.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class PostType implements Hookable
{
    public const SLUG = 'partner_org';

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
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'partner-organizations'],
        ]);
    }
}
