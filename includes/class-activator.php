<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Activator {

    public static function activate() {

        add_option('askiviki_wa_enabled', 'yes');

        global $wpdb;

        $table_name = $wpdb->prefix . 'askiviki_wa_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id BIGINT UNSIGNED NULL,
        message_id VARCHAR(255) NULL,
        phone VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        message LONGTEXT NOT NULL,
        response LONGTEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);

    }

}