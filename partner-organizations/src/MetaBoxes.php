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
        add_action('init', [$this, 'register_meta']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . PostType::SLUG, [$this, 'save'], 10, 2);
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    public function register_meta(): void
    {
        register_post_meta(PostType::SLUG, self::WEBSITE_META_KEY, [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => [$this, 'sanitize_website_url'],
            'auth_callback' => static fn (mixed $allowed, string $meta_key, int $post_id): bool => current_user_can('edit_post', $post_id),
        ]);
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
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if (! isset($_POST[self::NONCE_NAME]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $raw_website_url = isset($_POST['partner_organization_website_url'])
            ? sanitize_text_field(wp_unslash($_POST['partner_organization_website_url']))
            : '';
        $website_url = $this->sanitize_website_url($raw_website_url);

        if ('' !== $raw_website_url && '' === $website_url) {
            $this->flag_invalid_url_notice($post_id);
            return;
        }

        if ('' === $website_url) {
            delete_post_meta($post_id, self::WEBSITE_META_KEY);
        } else {
            update_post_meta($post_id, self::WEBSITE_META_KEY, $website_url);
        }

        $this->cache->flush();
    }

    public function sanitize_website_url(mixed $value): string
    {
        $url = trim((string) $value);
        if ('' === $url) {
            return '';
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        $scheme = strtolower((string) wp_parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        return esc_url_raw($url, ['http', 'https']);
    }

    public function admin_notices(): void
    {
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        if ($post_id <= 0 || ! get_transient($this->invalid_url_notice_key($post_id))) {
            return;
        }

        delete_transient($this->invalid_url_notice_key($post_id));
        add_settings_error(
            'partner_organizations',
            'partner_organization_invalid_website_url',
            __('Website URL was not saved. Enter an empty value or a URL beginning with http:// or https://.', 'partner-organizations'),
            'error'
        );
        settings_errors('partner_organizations');
    }

    private function flag_invalid_url_notice(int $post_id): void
    {
        set_transient($this->invalid_url_notice_key($post_id), 1, MINUTE_IN_SECONDS);
    }

    private function invalid_url_notice_key(int $post_id): string
    {
        return 'partner_organization_invalid_website_url_' . $post_id;
    }
}
