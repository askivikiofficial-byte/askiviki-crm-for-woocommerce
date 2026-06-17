<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Settings {

    public function __construct() {

        add_action('admin_init', [$this, 'register_settings']);

    }

    public function register_settings() {

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_phone',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        add_settings_section(
            'askiviki_wa_main',
            'WhatsApp Settings',
            null,
            'askiviki-whatsapp'
        );

        add_settings_field(
            'askiviki_wa_phone',
            'Admin WhatsApp Number',
            [$this, 'phone_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_test_number',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        add_settings_field(
            'askiviki_wa_test_number',
            'Test Number',
            [$this, 'test_number_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_phone_id',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_access_token',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_phone_id',
            'Phone Number ID',
            [$this, 'phone_id_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_access_token',
            'Access Token',
            [$this, 'access_token_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_enabled',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_notify_processing',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_notify_completed',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_notify_cancelled',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_enabled',
            'Enable WhatsApp',
            [$this, 'enabled_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        add_settings_field(
            'askiviki_wa_notify_processing',
            'Processing Notifications',
            [$this, 'processing_notification_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_notify_completed',
            'Completed Notifications',
            [$this, 'completed_notification_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_notify_cancelled',
            'Cancelled Notifications',
            [$this, 'cancelled_notification_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_processing_template',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_completed_template',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_cancelled_template',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_processing_template',
            'Processing Template',
            [$this, 'processing_template_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_completed_template',
            'Completed Template',
            [$this, 'completed_template_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_cancelled_template',
            'Cancelled Template',
            [$this, 'cancelled_template_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_admin_notifications',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_admin_notifications',
            'Admin Notifications',
            [$this, 'admin_notifications_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_verify_token',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_verify_token',
            'Webhook Verify Token',
            [$this, 'verify_token_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_use_template',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_template_name',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_template_language',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_use_template',
            'Use Template Messages',
            [$this, 'use_template_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_template_name',
            'Template Name',
            [$this, 'template_name_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_template_language',
            'Template Language',
            [$this, 'template_language_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_processing_template_name',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_completed_template_name',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_cancelled_template_name',
            [
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        add_settings_field(
            'askiviki_wa_processing_template_name',
            'Processing Template Name',
            [$this, 'processing_template_name_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_completed_template_name',
            'Completed Template Name',
            [$this, 'completed_template_name_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );

        add_settings_field(
            'askiviki_wa_cancelled_template_name',
            'Cancelled Template Name',
            [$this, 'cancelled_template_name_field'],
            'askiviki-whatsapp',
            'askiviki_wa_main'
        );
    }

    public function phone_field() {

        $value = get_option('askiviki_wa_phone', '');

        echo '<input type="text"
                     name="askiviki_wa_phone"
                     value="' . esc_attr($value) . '"
                     class="regular-text">';
    }
    public function test_number_field()
    {
        $value = get_option(
            'askiviki_wa_test_number',
            ''
        );

        echo '<input type="text"
            name="askiviki_wa_test_number"
            value="' . esc_attr($value) . '"
            class="regular-text">';
    }
    public function phone_id_field()
    {
        $value = get_option('askiviki_wa_phone_id', '');

        echo '<input type="text"
        name="askiviki_wa_phone_id"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }

    public function access_token_field()
    {
        $value = get_option('askiviki_wa_access_token', '');

        echo '<input type="password"
        name="askiviki_wa_access_token"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }
    public function processing_notification_field()
    {
        $value = get_option(
            'askiviki_wa_notify_processing',
            'yes'
        );

        ?>
        <input
            type="checkbox"
            name="askiviki_wa_notify_processing"
            value="yes"
            <?php checked($value, 'yes'); ?>
        >
        Enable Processing Notifications
        <?php
    }
    public function completed_notification_field()
    {
        $value = get_option(
            'askiviki_wa_notify_completed',
            'yes'
        );

        ?>
        <input
            type="checkbox"
            name="askiviki_wa_notify_completed"
            value="yes"
            <?php checked($value, 'yes'); ?>
        >
        Enable Completed Notifications
        <?php
    }
    public function cancelled_notification_field()
    {
        $value = get_option(
            'askiviki_wa_notify_cancelled',
            'yes'
        );

        ?>
        <input
            type="checkbox"
            name="askiviki_wa_notify_cancelled"
            value="yes"
            <?php checked($value, 'yes'); ?>
        >
        Enable Cancelled Notifications
        <?php
    }
    public function enabled_field()
    {
        $value = get_option(
            'askiviki_wa_enabled',
            'yes'
        );
        ?>
        <input
            type="checkbox"
            name="askiviki_wa_enabled"
            value="yes"
            <?php checked($value, 'yes'); ?>
        >
        Enable WhatsApp Notifications
        <?php
    }
    public function processing_template_field()
    {
        $value = get_option(
            'askiviki_wa_processing_template',
            "Hi {customer_name},

Your order #{order_id} is now Processing.

Total: ₹{order_total}"
        );

        ?>
        <textarea
            name="askiviki_wa_processing_template"
            rows="6"
            cols="70"><?php echo esc_textarea($value); ?></textarea>

        <p class="description">
            Variables:
            {customer_name},
            {order_id},
            {order_total},
            {site_name}
        </p>
        <?php
    }
    public function completed_template_field()
    {
        $value = get_option(
            'askiviki_wa_completed_template',
            "Hi {customer_name},

Your order #{order_id} is now Completed.

Total: ₹{order_total}"
        );

        ?>
        <textarea
            name="askiviki_wa_completed_template"
            rows="6"
            cols="70"><?php echo esc_textarea($value); ?></textarea>

        <p class="description">
            Variables:
            {customer_name},
            {order_id},
            {order_total},
            {site_name}
        </p>
        <?php
    }
    public function cancelled_template_field()
    {
        $value = get_option(
            'askiviki_wa_cancelled_template',
            "Hi {customer_name},

Your order #{order_id} is now Cancelled.

Total: ₹{order_total}"
        );

        ?>
        <textarea
            name="askiviki_wa_cancelled_template"
            rows="6"
            cols="70"><?php echo esc_textarea($value); ?></textarea>

        <p class="description">
            Variables:
            {customer_name},
            {order_id},
            {order_total},
            {site_name}
        </p>
        <?php
    }
    public function admin_notifications_field()
    {
        $value = get_option(
            'askiviki_wa_admin_notifications',
            'yes'
        );

        ?>
        <input
            type="checkbox"
            name="askiviki_wa_admin_notifications"
            value="yes"
            <?php checked($value, 'yes'); ?>
        >
        Enable Admin Notifications
        <?php
    }
    public function verify_token_field()
    {
        $value = get_option(
            'askiviki_wa_verify_token',
            'askiviki-secret-2026'
        );

        echo '<input
        type="text"
        name="askiviki_wa_verify_token"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }
    public function use_template_field()
    {
        $value = get_option(
            'askiviki_wa_use_template',
            'no'
        );
        ?>
        <input
                type="checkbox"
                name="askiviki_wa_use_template"
                value="yes"
            <?php checked($value, 'yes'); ?>
        >
        Enable Template Messages
        <?php
    }
    public function template_name_field()
    {
        $value = get_option(
            'askiviki_wa_template_name',
            'hello_world'
        );

        echo '<input
        type="text"
        name="askiviki_wa_template_name"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }
    public function template_language_field()
    {
        $value = get_option(
            'askiviki_wa_template_language',
            'en_US'
        );

        echo '<input
        type="text"
        name="askiviki_wa_template_language"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }
    public function processing_template_name_field()
    {
        $value = get_option(
            'askiviki_wa_processing_template_name',
            'order_processing'
        );

        echo '<input type="text"
        name="askiviki_wa_processing_template_name"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }

    public function completed_template_name_field()
    {
        $value = get_option(
            'askiviki_wa_completed_template_name',
            'order_completed'
        );

        echo '<input type="text"
        name="askiviki_wa_completed_template_name"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }

    public function cancelled_template_name_field()
    {
        $value = get_option(
            'askiviki_wa_cancelled_template_name',
            'order_cancelled'
        );

        echo '<input type="text"
        name="askiviki_wa_cancelled_template_name"
        value="' . esc_attr($value) . '"
        class="regular-text">';
    }
}