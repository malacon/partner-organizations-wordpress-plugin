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
        add_action('save_post_' . PostType::SLUG, [$this, 'enforce_single_partner_category'], 20);
    }

    public function register_taxonomy(): void
    {
        register_taxonomy(self::SLUG, [PostType::SLUG], [
            'labels' => [
                'name' => __('Partner Categories', 'partner-organizations'),
                'singular_name' => __('Partner Category', 'partner-organizations'),
            ],
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'capabilities' => [
                'manage_terms' => 'manage_partner_categories',
                'edit_terms' => 'edit_partner_categories',
                'delete_terms' => 'delete_partner_categories',
                'assign_terms' => 'assign_partner_categories',
            ],
            'show_in_rest' => true,
        ]);
    }

    public function enforce_single_partner_category(int $post_id): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $terms = get_the_terms($post_id, self::SLUG);
        if (! is_array($terms) || count($terms) <= 1) {
            return;
        }

        wp_set_object_terms($post_id, [(int) $terms[0]->term_id], self::SLUG);
    }
}
