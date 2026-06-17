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

    $askiviki_options = [
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

    foreach ($askiviki_options as $askiviki_option) {
        delete_option($askiviki_option);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Custom Tables
    |--------------------------------------------------------------------------
    */

    $askiviki_tables = array(
        $wpdb->prefix . 'askiviki_wa_messages',
        $wpdb->prefix . 'askiviki_wa_customer_notes',
        $wpdb->prefix . 'askiviki_wa_quick_replies',
        $wpdb->prefix . 'askiviki_wa_logs',
    );

    foreach ( $askiviki_tables as $askiviki_table ) {

        $askiviki_table = esc_sql( $askiviki_table );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Required during plugin uninstall to remove plugin-owned tables.
        $wpdb->query(
            "DROP TABLE IF EXISTS `{$askiviki_table}`"
        );
    }