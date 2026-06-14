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
        add_action(
            'admin_init',
            [$this, 'handle_reply']
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
        add_submenu_page(
            null,
            'Conversation',
            'Conversation',
            'manage_options',
            'askiviki-conversation',
            [$this, 'conversation_page']
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
            "
            SELECT *
            FROM {$wpdb->prefix}askiviki_wa_messages m1
            WHERE id = (
                SELECT id
                FROM {$wpdb->prefix}askiviki_wa_messages m2
                WHERE m2.phone = m1.phone
                ORDER BY created_at DESC
                LIMIT 1
            )
            ORDER BY created_at DESC
            "
        );
        ?>
        <div class="wrap">
            <?php if (isset($_GET['reply_sent']))
            {
                ?>
                    <div class="notice notice-success is-dismissible">
                        <p>Reply sent successfully.</p>
                    </div>
            <?php
            }
            ?>
            <h1>WhatsApp Inbox</h1>
            <?php
                $total_conversations =
                $wpdb->get_var(
                                "
                    SELECT COUNT(
                        DISTINCT phone
                    )
                    FROM {$wpdb->prefix}askiviki_wa_messages
                    "
                );
            ?>
            <p>
                Total Conversations:
                <strong>
                    <?php echo esc_html(
                        $total_conversations
                    ); ?>
                </strong>
            </p>
            <table class="widefat striped">

                <thead>
                <tr>
                    <th>Phone</th>
                    <th>Last Message</th>
                    <th>Last Activity</th>
                    <th>Open</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($messages as $message): ?>

                    <tr>

                        <td>
                            <?php echo esc_html(
                                $message->phone
                            ); ?>
                        </td>

                        <td>
                            <?php echo esc_html(
                                wp_trim_words(
                                    $message->message,
                                    10
                                )
                            ); ?>
                        </td>

                        <td>
                            <?php echo esc_html(
                                $message->created_at
                            ); ?>
                        </td>

                        <td>
                            <a
                                class="button button-primary"
                                href="<?php echo admin_url(
                                    'admin.php?page=askiviki-conversation&phone=' .
                                    urlencode($message->phone)
                                ); ?>">
                                Open Chat
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>

            </table>

        </div>
        <?php
    }
    public function handle_reply()
    {
        if (!isset($_POST['askiviki_send_reply'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['askiviki_reply_nonce'],'askiviki_reply_message')) {
            return;
        }

        $phone = sanitize_text_field(
            $_POST['phone']
        );

        $message = sanitize_textarea_field(
            $_POST['reply_message']
        );

        $service =
            new AskIViki_WA_Service();

        $service->send_message(
            $phone,
            $message
        );
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix .
            'askiviki_wa_messages',
            [
                'wa_id'       => '',
                'phone'       => $phone,
                'message'     => $message,
                'message_type'=> 'admin_reply',
                'created_at'  => current_time('mysql')
            ]
        );

        wp_redirect(
            add_query_arg(
                [
                    'page'       => 'askiviki-conversation',
                    'phone'      => $phone,
                    'reply_sent' => '1'
                ],
                admin_url('admin.php')
            )
        );
        exit;
    }
    public function conversation_page()
    {
        global $wpdb;

        $phone = sanitize_text_field(
            $_GET['phone'] ?? ''
        );

        $table = $wpdb->prefix . 'askiviki_wa_messages';

        $messages = [];

        if (!empty($phone)) {

            $messages = $wpdb->get_results(
                $wpdb->prepare(
                    "
                SELECT *
                FROM {$table}
                WHERE phone = %s
                ORDER BY created_at ASC
                ",
                    $phone
                )
            );
        }
        ?>

        <div class="wrap">

            <?php if (isset($_GET['reply_sent'])) : ?>

                <div class="notice notice-success is-dismissible">
                    <p>Reply sent successfully.</p>
                </div>

            <?php endif; ?>

            <p>
                <a
                        href="<?php echo admin_url(
                            'admin.php?page=askiviki-wa-inbox'
                        ); ?>"
                        class="button">
                    ← Back to Chats
                </a>
            </p>

            <?php if (empty($phone)) : ?>

                <h1>Conversation</h1>

                <div class="notice notice-info">
                    <p>
                        Please open a conversation from the Chats page.
                    </p>
                </div>

                <?php return; ?>

            <?php endif; ?>

            <h1>
                Conversation:
                <?php echo esc_html($phone); ?>
            </h1>

            <div style="
            max-width:800px;
            background:#fff;
            padding:20px;
            border:1px solid #ddd;
        ">

                <?php if (empty($messages)) : ?>

                    <div class="notice notice-info">
                        <p>
                            No messages found for this conversation.
                        </p>
                    </div>

                <?php else : ?>

                    <?php foreach ($messages as $message) : ?>

                        <div style="
                                margin:10px 0;
                                padding:10px;
                                border-radius:10px;
                                background:
                        <?php echo
                        $message->message_type === 'admin_reply'
                            ? '#dcf8c6'
                            : '#f1f1f1';
                        ?>;
                                text-align:
                        <?php echo
                        $message->message_type === 'admin_reply'
                            ? 'right'
                            : 'left';
                        ?>;
                                ">

                            <strong>

                                <?php echo
                                $message->message_type === 'admin_reply'
                                    ? 'Admin'
                                    : 'Customer';
                                ?>

                            </strong>

                            <br>

                            <?php echo esc_html(
                                $message->message
                            ); ?>

                            <br>

                            <small style="color:#666;">
                                <?php echo esc_html(
                                    $message->created_at
                                ); ?>
                            </small>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

                <form method="post" style="margin-top:20px;">

                    <?php wp_nonce_field(
                        'askiviki_reply_message',
                        'askiviki_reply_nonce'
                    ); ?>

                    <input
                            type="hidden"
                            name="phone"
                            value="<?php echo esc_attr($phone); ?>">

                    <textarea
                            autofocus
                            name="reply_message"
                            rows="4"
                            style="width:100%;"
                            placeholder="Type your reply..."></textarea>

                    <br><br>

                    <input
                            type="submit"
                            name="askiviki_send_reply"
                            class="button button-primary"
                            value="Send Reply">

                </form>

            </div>

        </div>

        <?php
    }
}