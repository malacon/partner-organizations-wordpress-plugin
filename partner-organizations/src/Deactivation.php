<?php
/**
 * Plugin deactivation behavior.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Deactivation
{
    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
