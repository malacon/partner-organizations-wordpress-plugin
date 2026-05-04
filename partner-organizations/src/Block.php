<?php
/**
 * Gutenberg Partner Directory block.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Block implements Hookable
{
    public const NAME = 'partner-organizations/partner-directory';

    public function __construct(private Shortcode $shortcode)
    {
    }

    public function register(): void
    {
        add_action('init', [$this, 'register_block']);
    }

    public function register_block(): void
    {
        if (\WP_Block_Type_Registry::get_instance()->is_registered(self::NAME)) {
            return;
        }

        register_block_type(
            PARTNER_ORGANIZATIONS_DIR . 'blocks/partner-directory',
            [
                'render_callback' => [$this, 'render'],
            ]
        );
    }

    public function render(array $attributes = []): string
    {
        $category_slug = '';

        if (isset($attributes['categorySlug']) && is_string($attributes['categorySlug'])) {
            $category_slug = sanitize_title($attributes['categorySlug']);
        }

        return $this->shortcode->render([
            'category' => $category_slug,
        ]);
    }
}
