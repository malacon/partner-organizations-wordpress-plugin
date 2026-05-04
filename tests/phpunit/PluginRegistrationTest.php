<?php
/**
 * Plugin registration smoke tests.
 *
 * @package PartnerOrganizations
 */

final class PluginRegistrationTest extends WP_UnitTestCase
{
    public function test_partner_organization_post_type_is_registered(): void
    {
        do_action('init');

        $this->assertTrue(post_type_exists(PartnerOrganizations\PostType::SLUG));
    }
}
