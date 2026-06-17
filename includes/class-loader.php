<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_notices', function () {

    if (!class_exists('WooCommerce')) {

        echo '<div class="notice notice-error">';
        echo '<p><strong>Ask I Viki CRM for WooCommerce</strong> requires WooCommerce.</p>';
        echo '</div>';

    }

});