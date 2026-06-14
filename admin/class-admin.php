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
        add_submenu_page(
            'woocommerce',
            'WhatsApp Logs',
            'WhatsApp Logs',
            'manage_options',
            'askiviki-wa-logs',
            [$this, 'logs_page']
        );
        add_submenu_page(
            'woocommerce',
            'WhatsApp Inbox',
            'WhatsApp Inbox',
            'manage_options',
            'askiviki-wa-inbox',
            [$this, 'inbox_page']
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
        if (!isset($_POST['askiviki_send_test'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['askiviki_test_nonce'],'askiviki_send_test')) {
            return;
        }

        $phone = get_option(
            'askiviki_wa_test_number'
        );

        $service = new AskIViki_WA_Service();

        if (get_option('askiviki_wa_use_template','no') === 'yes') {

            $service->send_template(
                $phone,
                get_option(
                    'askiviki_wa_template_name',
                    'hello_world'
                )
            );

        } else {

            $service->send_message(
                $phone,
                'Hello from Ask I Viki WooCommerce WhatsApp'
            );
        }

        add_settings_error(
            'askiviki_wa',
            'test_sent',
            'Test message triggered successfully.',
            'updated'
        );
    }
    public function logs_page()
    {
        global $wpdb;

        $logs = $wpdb->get_results(
            "SELECT *
         FROM {$wpdb->prefix}askiviki_wa_logs
         ORDER BY id DESC
         LIMIT 100"
        );

        ?>
        <div class="wrap">

            <h1>WhatsApp Logs</h1>

            <table class="widefat striped">

                <thead>
                <tr>
                    <th>ID</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($logs as $log): ?>

                    <tr>
                        <td><?php echo esc_html($log->id); ?></td>
                        <td><?php echo esc_html($log->phone); ?></td>
                        <td><?php echo esc_html($log->status); ?></td>
                        <td><?php echo esc_html($log->message_id); ?></td>
                        <td><?php echo esc_html($log->created_at); ?></td>
                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>
        <?php
    }
    public function inbox_page()
    {
        global $wpdb;

        $messages = $wpdb->get_results(
            "SELECT *
        FROM {$wpdb->prefix}askiviki_wa_messages
        ORDER BY created_at DESC
        LIMIT 100"
        );
        ?>
        <div class="wrap">

            <h1>WhatsApp Inbox</h1>

            <table class="widefat striped">

                <thead>
                <tr>
                    <th>ID</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($messages as $message): ?>

                    <tr>
                        <td><?php echo esc_html($message->id); ?></td>
                        <td><?php echo esc_html($message->phone); ?></td>
                        <td><?php echo esc_html($message->message); ?></td>
                        <td><?php echo esc_html($message->message_type); ?></td>
                        <td><?php echo esc_html($message->created_at); ?></td>
                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>
        <?php
    }
}