<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action(
            'admin_init',
            [$this, 'handle_test_message']
        );
    }

    public function register_menu() {

        add_submenu_page(
            'woocommerce',
            'Ask I Viki WhatsApp',
            'Ask I Viki WhatsApp',
            'manage_options',
            'askiviki-whatsapp',
            [$this, 'settings_page']
        );

    }

    public function settings_page()
    {
        ?>
        <div class="wrap">

            <?php settings_errors('askiviki_wa'); ?>

            <h1>Ask I Viki WooCommerce WhatsApp</h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('askiviki_wa_group');
                do_settings_sections('askiviki-whatsapp');
                submit_button('Save Settings');
                ?>
            </form>

            <hr>

            <h2>Test WhatsApp</h2>

            <form method="post">

                <?php
                wp_nonce_field(
                    'askiviki_send_test',
                    'askiviki_test_nonce'
                );
                ?>

                <input
                        type="submit"
                        name="askiviki_send_test"
                        class="button button-primary"
                        value="Send Test Message">

            </form>

        </div>
        <?php
    }
    public function handle_test_message()
    {
        if (
        !isset($_POST['askiviki_send_test'])
        ) {
            return;
        }

        if (
        !wp_verify_nonce(
            $_POST['askiviki_test_nonce'],
            'askiviki_send_test'
        )
        ) {
            return;
        }

        $phone = get_option(
            'askiviki_wa_test_number'
        );

        $service = new AskIViki_WA_Service();

        $service->send_message(
            $phone,
            'Hello from Ask I Viki WooCommerce WhatsApp'
        );

        add_settings_error(
            'askiviki_wa',
            'test_sent',
            'Test message triggered successfully.',
            'updated'
        );
    }
}