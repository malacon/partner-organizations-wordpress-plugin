<?php
/**
 * Partner Organization meta boxes.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class MetaBoxes implements Hookable
{
    public const WEBSITE_META_KEY = '_partner_organization_website_url';
    private const NONCE_ACTION = 'partner_organization_details';
    private const NONCE_NAME = 'partner_organization_details_nonce';

    public function __construct(private Cache $cache)
    {
    }

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . PostType::SLUG, [$this, 'save'], 10, 2);
    }

    public function add_meta_boxes(): void
    {
        add_meta_box(
            'partner-organization-details',
            __('Partner Organization Details', 'partner-organizations'),
            [$this, 'render_details'],
            PostType::SLUG,
            'normal',
            'default'
        );
    }

    public function render_details($post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        $website_url = (string) get_post_meta($post->ID, self::WEBSITE_META_KEY, true);
        ?>
        <p>
            <label for="partner-organization-website-url"><?php echo esc_html__('Website URL', 'partner-organizations'); ?></label>
            <input
                type="url"
                id="partner-organization-website-url"
                name="partner_organization_website_url"
                value="<?php echo esc_attr($website_url); ?>"
                class="widefat"
            />
        </p>
        <?php
    }

    public function save(int $post_id, $post): void
    {
        if (! isset($_POST[self::NONCE_NAME]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $website_url = isset($_POST['partner_organization_website_url'])
            ? esc_url_raw(wp_unslash($_POST['partner_organization_website_url']))
            : '';

        if ('' === $website_url) {
            delete_post_meta($post_id, self::WEBSITE_META_KEY);
        } else {
            update_post_meta($post_id, self::WEBSITE_META_KEY, $website_url);
        }

        $this->cache->flush();
    }
}
