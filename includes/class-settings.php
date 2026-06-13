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
            'askiviki_wa_phone'
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
            'askiviki_wa_test_number'
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
            'askiviki_wa_phone_id'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_access_token'
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
            'askiviki_wa_enabled'
        );
        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_notify_processing'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_notify_completed'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_notify_cancelled'
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
            'askiviki_wa_processing_template'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_completed_template'
        );

        register_setting(
            'askiviki_wa_group',
            'askiviki_wa_cancelled_template'
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
}