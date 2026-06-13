<?php
/**
 * Plugin Name: Ask I Viki WooCommerce WhatsApp
 * Plugin URI: https://askiviki.simpletechgroups.com
 * Description: Send WooCommerce order notifications through WhatsApp.
 * Version: 1.0.0
 * Author: Vigneshwaran P
 * Author URI: https://askiviki.simpletechgroups.com
 * License: GPL v2 or later
 * Text Domain: askiviki-whatsapp
 */

if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__) . 'includes/class-loader.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-whatsapp-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-woocommerce-hooks.php';

new AskIViki_WA_Admin();
new AskIViki_WA_Settings();
new AskIViki_WA_WooCommerce_Hooks();

register_activation_hook(
    __FILE__,
    ['AskIViki_WA_Activator', 'activate']
);

register_deactivation_hook(
    __FILE__,
    ['AskIViki_WA_Deactivator', 'deactivate']
);