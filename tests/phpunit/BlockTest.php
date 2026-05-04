<?php
/**
 * Partner Directory block tests.
 *
 * @package PartnerOrganizations
 */

final class BlockTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        do_action('init');
    }

    public function test_partner_directory_block_is_registered(): void
    {
        $block = WP_Block_Type_Registry::get_instance()->get_registered(PartnerOrganizations\Block::NAME);

        $this->assertNotNull($block);
        $this->assertSame('Partner Directory', $block->title);
        $this->assertArrayHasKey('categorySlug', $block->attributes);
        $this->assertTrue(is_callable($block->render_callback));
    }

    public function test_partner_directory_block_reuses_shortcode_rendering_with_category_filter(): void
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
        update_post_meta($education_partner, PartnerOrganizations\MetaBoxes::WEBSITE_META_KEY, 'https://education.example');

        $corporate_partner = self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => 'publish',
            'post_title' => 'Corporate Partner',
        ]);
        wp_set_object_terms($corporate_partner, [$corporate], PartnerOrganizations\Taxonomy::SLUG);

        $output = render_block([
            'blockName' => PartnerOrganizations\Block::NAME,
            'attrs' => [
                'categorySlug' => 'education',
            ],
        ]);

        $this->assertStringContainsString('Education Partner', $output);
        $this->assertStringContainsString('href="https://education.example"', $output);
        $this->assertStringNotContainsString('Corporate Partner', $output);
        $this->assertStringNotContainsString('href=""', $output);
    }
}
