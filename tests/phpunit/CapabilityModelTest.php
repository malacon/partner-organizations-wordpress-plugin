<?php
/**
 * Partner Organization capability model tests.
 *
 * @package PartnerOrganizations
 */

final class CapabilityModelTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();

        do_action('plugins_loaded');
        PartnerOrganizations\Activation::activate();
        do_action('init');
    }

    public function test_partner_organization_post_type_uses_partner_specific_capabilities(): void
    {
        $post_type = get_post_type_object(PartnerOrganizations\PostType::SLUG);

        $this->assertNotNull($post_type);
        $this->assertTrue($post_type->map_meta_cap);
        $this->assertSame('edit_partner', $post_type->cap->edit_post);
        $this->assertSame('read_partner', $post_type->cap->read_post);
        $this->assertSame('delete_partner', $post_type->cap->delete_post);
        $this->assertSame('edit_partners', $post_type->cap->edit_posts);
        $this->assertSame('edit_others_partners', $post_type->cap->edit_others_posts);
        $this->assertSame('publish_partners', $post_type->cap->publish_posts);
        $this->assertSame('create_partners', $post_type->cap->create_posts);
    }

    public function test_partner_category_taxonomy_uses_partner_specific_capabilities(): void
    {
        $taxonomy = get_taxonomy(PartnerOrganizations\Taxonomy::SLUG);

        $this->assertNotFalse($taxonomy);
        $this->assertSame('manage_partner_categories', $taxonomy->cap->manage_terms);
        $this->assertSame('edit_partner_categories', $taxonomy->cap->edit_terms);
        $this->assertSame('delete_partner_categories', $taxonomy->cap->delete_terms);
        $this->assertSame('assign_partner_categories', $taxonomy->cap->assign_terms);
    }

    public function test_activation_creates_partner_manager_role_and_grants_administrators_partner_capabilities(): void
    {
        $partner_manager = get_role(PartnerOrganizations\Capabilities::ROLE);
        $administrator = get_role('administrator');

        $this->assertNotNull($partner_manager);
        $this->assertNotNull($administrator);

        foreach (PartnerOrganizations\Capabilities::all() as $capability) {
            $this->assertTrue($partner_manager->has_cap($capability), "Partner manager missing {$capability}.");
            $this->assertTrue($administrator->has_cap($capability), "Administrator missing {$capability}.");
        }
    }

    public function test_deactivation_does_not_remove_partner_manager_role_or_capabilities(): void
    {
        PartnerOrganizations\Deactivation::deactivate();

        $partner_manager = get_role(PartnerOrganizations\Capabilities::ROLE);

        $this->assertNotNull($partner_manager);
        $this->assertTrue($partner_manager->has_cap('edit_partners'));
        $this->assertTrue($partner_manager->has_cap('manage_partner_categories'));
    }

    public function test_partner_manager_can_manage_partner_organizations_and_subscriber_cannot(): void
    {
        $owner_id = self::factory()->user->create(['role' => PartnerOrganizations\Capabilities::ROLE]);
        $manager_id = self::factory()->user->create(['role' => PartnerOrganizations\Capabilities::ROLE]);
        $subscriber_id = self::factory()->user->create(['role' => 'subscriber']);
        $post_id = self::factory()->post->create([
            'post_author' => $owner_id,
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'publish',
        ]);

        wp_set_current_user($manager_id);
        $this->assertTrue(current_user_can('read'));
        $this->assertTrue(current_user_can('upload_files'));
        $this->assertTrue(current_user_can('create_partners'));
        $this->assertTrue(current_user_can('edit_post', $post_id));
        $this->assertTrue(current_user_can('publish_partners'));
        $this->assertTrue(current_user_can('delete_post', $post_id));
        $this->assertTrue(current_user_can('manage_partner_categories'));
        $this->assertTrue(current_user_can('assign_partner_categories'));

        wp_set_current_user($subscriber_id);
        $this->assertFalse(current_user_can('upload_files'));
        $this->assertFalse(current_user_can('create_partners'));
        $this->assertFalse(current_user_can('edit_post', $post_id));
        $this->assertFalse(current_user_can('publish_partners'));
        $this->assertFalse(current_user_can('delete_post', $post_id));
        $this->assertFalse(current_user_can('manage_partner_categories'));
        $this->assertFalse(current_user_can('assign_partner_categories'));
    }
}
