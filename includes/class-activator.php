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

        $messages_table =
            $wpdb->prefix .
            'askiviki_wa_messages';

        $sql .= "CREATE TABLE $messages_table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        wa_id VARCHAR(255) NOT NULL,
        customer_name VARCHAR(255) NULL,
        phone VARCHAR(30) NOT NULL,
        message LONGTEXT NOT NULL,
        message_type VARCHAR(50) DEFAULT 'text',
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

        $notes_table =
            $wpdb->prefix .
            'askiviki_wa_customer_notes';

        $sql .= "
        CREATE TABLE {$notes_table} (
        
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        
        phone VARCHAR(30) NOT NULL,
        
        tags VARCHAR(255) NULL,
        
        notes LONGTEXT NULL,
        
        is_vip TINYINT(1) DEFAULT 0,
        
        priority_level VARCHAR(20) DEFAULT 'normal',
        
        is_pinned TINYINT(1) DEFAULT 0,
        
        created_at DATETIME NOT NULL,
        
        updated_at DATETIME NULL,
        
        PRIMARY KEY (id),
        
        KEY phone (phone)
        
        ) {$charset_collate};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);

    }

}