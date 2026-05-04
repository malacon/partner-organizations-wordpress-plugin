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
        flush_rewrite_rules();
    }
}
