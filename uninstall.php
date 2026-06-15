<?php

    if (!defined('WP_UNINSTALL_PLUGIN')) {
        exit;
    }

    global $wpdb;

    /*
    |--------------------------------------------------------------------------
    | Delete Plugin Options
    |--------------------------------------------------------------------------
    */

    $options = [
        'askiviki_wa_phone',
        'askiviki_wa_test_number',
        'askiviki_wa_phone_id',
        'askiviki_wa_access_token',
        'askiviki_wa_enabled',
        'askiviki_wa_notify_processing',
        'askiviki_wa_notify_completed',
        'askiviki_wa_notify_cancelled',
        'askiviki_wa_processing_template',
        'askiviki_wa_completed_template',
        'askiviki_wa_cancelled_template',
        'askiviki_wa_admin_notifications',
        'askiviki_wa_verify_token',
        'askiviki_wa_use_template',
        'askiviki_wa_template_name',
        'askiviki_wa_template_language',
        'askiviki_wa_processing_template_name',
        'askiviki_wa_completed_template_name',
        'askiviki_wa_cancelled_template_name',
    ];

    foreach ($options as $option) {
        delete_option($option);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Custom Tables
    |--------------------------------------------------------------------------
    */

    $tables = [
        'askiviki_wa_messages',
        'askiviki_wa_customer_notes',
        'askiviki_wa_quick_replies',
        'askiviki_wa_logs',
    ];

    foreach ($tables as $table) {
        $wpdb->query(
            "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}"
        );
    }