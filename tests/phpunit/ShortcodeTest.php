<?php
/**
 * Partner Directory shortcode tests.
 *
 * @package PartnerOrganizations
 */

final class ShortcodeTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        do_action('init');
    }

    public function test_partner_directory_renders_only_published_partner_organizations_alphabetically(): void
    {
        $zeta_id = self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'publish',
            'post_title' => 'Zeta Organization',
        ]);
        update_post_meta($zeta_id, PartnerOrganizations\MetaBoxes::WEBSITE_META_KEY, 'https://zeta.example');

        self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'draft',
            'post_title' => 'Draft Organization',
        ]);

        self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'publish',
            'post_title' => 'Alpha Organization',
        ]);

        $output = do_shortcode('[partner_directory]');

        $this->assertStringContainsString('Alpha Organization', $output);
        $this->assertStringContainsString('Zeta Organization', $output);
        $this->assertStringNotContainsString('Draft Organization', $output);
        $this->assertLessThan(strpos($output, 'Zeta Organization'), strpos($output, 'Alpha Organization'));
        $this->assertStringContainsString('href="https://zeta.example"', $output);
        $this->assertStringNotContainsString('href=""', $output);
    }

    public function test_partner_directory_filters_by_partner_category_slug(): void
    {
        $education = self::factory()->term->create([
            'taxonomy' => PartnerOrganizations\Taxonomy::SLUG,
            'name' => 'Education',
            'slug' => 'education',
        ]);
        $corporate = self::factory()->term->create([
            'taxonomy' => PartnerOrganizations\Taxonomy::SLUG,
            'name' => 'Corporate',
            'slug' => 'corporate',
        ]);

        $education_partner = self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'publish',
            'post_title' => 'Education Partner',
        ]);
        wp_set_object_terms($education_partner, [$education], PartnerOrganizations\Taxonomy::SLUG);

        $corporate_partner = self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'publish',
            'post_title' => 'Corporate Partner',
        ]);
        wp_set_object_terms($corporate_partner, [$corporate], PartnerOrganizations\Taxonomy::SLUG);

        $output = do_shortcode('[partner_directory category="education"]');

        $this->assertStringContainsString('Education Partner', $output);
        $this->assertStringNotContainsString('Corporate Partner', $output);
    }

    public function test_partner_directory_renders_friendly_empty_results_message(): void
    {
        $output = do_shortcode('[partner_directory category="does-not-exist"]');

        $this->assertStringContainsString('No partner organizations found.', $output);
    }
}
