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
    }

    public function phone_field() {

        $value = get_option('askiviki_wa_phone', '');

        echo '<input type="text"
                     name="askiviki_wa_phone"
                     value="' . esc_attr($value) . '"
                     class="regular-text">';
    }
}