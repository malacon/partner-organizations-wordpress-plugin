<?php
/**
 * Plugin Name: Partner Organizations
 * Description: Manage and display a public directory of Partner Organizations.
 * Version: 0.1.0
 * Author: Craig Baker
 * Text Domain: partner-organizations
 * Requires PHP: 8.1
 * Requires at least: 6.6
 *
 * @package PartnerOrganizations
 */

if (! defined('ABSPATH')) {
    exit;
}

define('PARTNER_ORGANIZATIONS_VERSION', '0.1.0');
define('PARTNER_ORGANIZATIONS_FILE', __FILE__);
define('PARTNER_ORGANIZATIONS_DIR', plugin_dir_path(__FILE__));
define('PARTNER_ORGANIZATIONS_URL', plugin_dir_url(__FILE__));
define('PARTNER_ORGANIZATIONS_BASENAME', plugin_basename(__FILE__));

$partner_organizations_composer_autoload = PARTNER_ORGANIZATIONS_DIR . 'vendor/autoload.php';

if (is_readable($partner_organizations_composer_autoload)) {
    require_once $partner_organizations_composer_autoload;
} else {
    require_once PARTNER_ORGANIZATIONS_DIR . 'src/Autoloader.php';
    PartnerOrganizations\Autoloader::register();
}

register_activation_hook(PARTNER_ORGANIZATIONS_FILE, [PartnerOrganizations\Activation::class, 'activate']);
register_deactivation_hook(PARTNER_ORGANIZATIONS_FILE, [PartnerOrganizations\Deactivation::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    PartnerOrganizations\Plugin::instance()->boot();
});
