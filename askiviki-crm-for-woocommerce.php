<?php
/**

 * Plugin Name: Ask I Viki CRM for WooCommerce
 * Plugin URI: https://askiviki.simpletechgroups.com/
 * Description: Customer messaging, CRM, support inbox, quick replies and WooCommerce notifications powered by the Meta Cloud API.
 * Version: 1.0.0
 * Author: Ask I Viki
 * Author URI: https://askiviki.simpletechgroups.com/
 * Text Domain: askiviki-crm-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 11.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package AskIVikiCRMForWooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}
add_action(
    'before_woocommerce_init',
    function () {

        if (
        class_exists(
            \Automattic\WooCommerce\Utilities\FeaturesUtil::class
        )
        ) {

            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }
);
require_once plugin_dir_path(__FILE__) . 'includes/class-loader.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-whatsapp-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-woocommerce-hooks.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-webhook.php';

new AskIViki_WA_Admin();
new AskIViki_WA_Settings();
new AskIViki_WA_WooCommerce_Hooks();
new AskIViki_WA_Webhook();

register_activation_hook(
    __FILE__,
    ['AskIViki_WA_Activator', 'activate']
);

register_deactivation_hook(
    __FILE__,
    ['AskIViki_WA_Deactivator', 'deactivate']
);