<?php
/**
 * Plugin activation behavior.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Activation
{
    public static function activate(): void
    {
        (new PostType())->register_post_type();
        (new Taxonomy())->register_taxonomy();
        self::create_default_partner_categories();
        flush_rewrite_rules();
    }

    private static function create_default_partner_categories(): void
    {
        foreach (['Education', 'Nonprofit', 'Corporate'] as $term_name) {
            if (term_exists($term_name, Taxonomy::SLUG)) {
                continue;
            }

            wp_insert_term($term_name, Taxonomy::SLUG);
        }
    }
}
