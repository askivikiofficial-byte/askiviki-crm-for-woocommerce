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
        add_action(
            'admin_init',
            [$this, 'save_customer_note']
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
        global $wpdb;

        $unread_count =
            (int) $wpdb->get_var(
                "
        SELECT COUNT(
            DISTINCT phone
        )
        FROM {$wpdb->prefix}askiviki_wa_messages
        WHERE is_read = 0
        ");
        add_submenu_page(
            'woocommerce',
            'WhatsApp Inbox',
            'WhatsApp Inbox' .
            (
            $unread_count > 0
                ? ' <span class="awaiting-mod">' .
                $unread_count .
                '</span>'
                : ''
            ),
            'manage_woocommerce',
            'askiviki-wa-inbox',
            [ $this, 'inbox_page' ]
        );
        add_submenu_page(
            'woocommerce',
            'Quick Replies',
            'Quick Replies',
            'manage_woocommerce',
            'askiviki-quick-replies',
            [
                $this,
                'quick_replies_page'
            ]
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
        <hr>
        <p style="color:#777;">
            Ask I Viki CRM
            Version 1.0.0 Beta
        </p>
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
        $selected_tag =
            sanitize_text_field(
                $_GET['tag'] ?? ''
            );
        $search =
            sanitize_text_field(
                $_GET['search'] ?? ''
            );
        if (!empty($selected_tag)) {

            $messages = $wpdb->get_results(
                $wpdb->prepare(
                    "
        SELECT m1.*
        FROM {$wpdb->prefix}askiviki_wa_messages m1
        INNER JOIN {$wpdb->prefix}askiviki_wa_customer_notes n
            ON n.phone = m1.phone
        WHERE n.tags LIKE %s
        AND m1.id = (
            SELECT id
            FROM {$wpdb->prefix}askiviki_wa_messages m2
            WHERE m2.phone = m1.phone
            ORDER BY m2.created_at DESC
            LIMIT 1
        )
        ORDER BY m1.created_at DESC
        ",
                    '%' . $wpdb->esc_like($selected_tag) . '%'
                )
            );
        }
        $attention =
            sanitize_text_field(
                $_GET['attention'] ?? ''
            );
        if ($attention === '1') {

            $messages = array_filter(
                $messages,
                function ($message) use ($wpdb) {

                    $unread = $wpdb->get_var(
                        $wpdb->prepare(
                            "
                            SELECT COUNT(*)
                            FROM {$wpdb->prefix}askiviki_wa_messages
                            WHERE phone = %s
                            AND is_read = 0
                            ",
                            $message->phone
                        )
                    );
                    error_log(
                        '[Attention Filter] Phone: ' .
                        $message->phone .
                        ' Unread: ' .
                        $unread
                    );
                    return $unread > 0;
                }
            );
        }
        if (!empty($search)){
            $messages = array_filter( $messages, function ($message) use ( $search ) {
                    if ( stripos( $message->phone, $search ) !== false ) {
                        return true;
                    }
                    $orders = wc_get_orders([
                                    'billing_phone' =>
                                        $message->phone,
                                    'limit' => 1
                                ]);
                    if ( !empty($orders) ) {
                        $customer_name = $orders[0]->get_formatted_billing_full_name();
                        return stripos( $customer_name, $search ) !== false;
                    }
                global $wpdb;

                $count =$wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}askiviki_wa_messages WHERE phone = %s AND message LIKE %s",
                            $message->phone,
                            '%' . $search . '%'
                        )
                    );
                if ($count > 0) {
                    return true;
                }
                    return false;
                }
            );
        }
        usort($messages, function ( $a,$b) use ($wpdb) {
                $note_a = $wpdb->get_row( $wpdb->prepare(
                            "
                                    SELECT *
                                    FROM {$wpdb->prefix}askiviki_wa_customer_notes
                                    WHERE phone = %s
                                    LIMIT 1
                                    ",
                                            $a->phone
                                        )
                                    );
                $note_b = $wpdb->get_row( $wpdb->prepare(
                            "
                                    SELECT *
                                    FROM {$wpdb->prefix}askiviki_wa_customer_notes
                                    WHERE phone = %s
                                    LIMIT 1
                                    ",
                                            $b->phone
                                        )
                                    );

                $score_a = 0;
                $score_b = 0;
            if ( $note_a && $note_a->is_pinned ) {
                $score_a += 100;
            }
            if ( $note_b && $note_b->is_pinned ) {
                $score_b += 100;
            }
            if ( $note_a && $note_a->priority_level === 'urgent' ) {
                $score_a += 50;
            }
            if ( $note_b && $note_b->priority_level === 'urgent' ) {
                $score_b += 50;
            }
            if ( $note_a && $note_a->priority_level === 'high' ) {
                $score_a += 25;
            }
            if ( $note_b && $note_b->priority_level === 'high' ) {
                $score_b += 25;
            }
            if ( $note_a && $note_a->is_vip ) {
                $score_a += 10;
            }
            if ( $note_b && $note_b->is_vip ) {
                $score_b += 10;
            }
            return $score_b <=> $score_a;
        }
        );
        $all_tags = $wpdb->get_col(
            "
    SELECT tags
    FROM {$wpdb->prefix}askiviki_wa_customer_notes
    WHERE tags <> ''
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
            <div style="background:#fff;padding:20px;border:1px solid #ddd;margin-bottom:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                <h1 style="margin:0;">
                    Ask I Viki CRM
                </h1>
                <p style="color:#666;margin-top:10px;">
                    WhatsApp Customer Support Center
                </p>
            </div>
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
            $vip_customers =
                (int) $wpdb->get_var(
                    "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}askiviki_wa_customer_notes
        WHERE is_vip = 1
        "
                );

            $urgent_customers =
                (int) $wpdb->get_var(
                    "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}askiviki_wa_customer_notes
        WHERE priority_level = 'urgent'
        "
                );

            $pinned_customers =
                (int) $wpdb->get_var(
                    "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}askiviki_wa_customer_notes
        WHERE is_pinned = 1
        "
                );

            $unread_conversations =
                (int) $wpdb->get_var(
                    "
        SELECT COUNT(
            DISTINCT phone
        )
        FROM {$wpdb->prefix}askiviki_wa_messages
        WHERE is_read = 0
        "
                );
            $active_today =
                (int) $wpdb->get_var(
                    "
        SELECT COUNT(
            DISTINCT phone
        )
        FROM {$wpdb->prefix}askiviki_wa_messages
        WHERE DATE(created_at) =
        CURDATE()
        "
                );
            ?>
            <div style="display:flex;flex-wrap:wrap;gap:15px;flex-wrap:wrap;margin:20px 0;">
                <div style="background:#fff;border:1px solid #ddd;padding:20px;min-width:180px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h3>Total Chats</h3>
                    <p style="font-size:28px;font-weight:bold;margin:0;">
                    <?php echo esc_html(
                    $total_conversations
                    ); ?>
                    </p>
                </div>
                <div style="background:#fff;border:1px solid #ddd;padding:20px;min-width:180px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h3>⭐ VIP</h3>
                    <p style="font-size:28px;font-weight:bold;margin:0;">
                    <?php echo esc_html(
                    $vip_customers
                    ); ?>
                    </p>
                </div>
                <div style="background:#fff;border:1px solid #ddd;padding:20px;min-width:180px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h3>🚨 Urgent</h3>
                    <p style="font-size:28px;font-weight:bold;margin:0;">
                    <?php echo esc_html(
                    $urgent_customers
                    ); ?>
                    </p>
                </div>
                <div style="background:#fff;border:1px solid #ddd;padding:20px;min-width:180px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h3>📌 Pinned</h3>
                    <p style="font-size:28px;font-weight:bold;margin:0;">
                    <?php echo esc_html(
                    $pinned_customers
                    ); ?>
                    </p>
                </div>
                <div style="background:#fff;border:1px solid #ddd;padding:20px;min-width:180px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h3>🔴 Unread</h3>
                    <p style="font-size:28px;font-weight:bold;margin:0;">
                        <?php echo esc_html(
                            $unread_conversations
                        ); ?>
                    </p>
                </div>
                <div style="background:#fff;border:1px solid #ddd;padding:20px;min-width:180px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h3>🟢 Active Today</h3>
                    <p style="font-size:28px;font-weight:bold;margin:0;">
                        <?php echo esc_html(
                            $active_today
                        ); ?>
                    </p>
                </div>
            </div>
            <form method="get" style="margin-bottom:20px;">
                <input type="hidden" name="page" value="askiviki-wa-inbox">
                <input type="text" name="search" placeholder="Search phone, customer name..." value="<?php echo esc_attr( $_GET['search'] ?? '' ); ?>" style="width:300px;">
                <input type="submit" class="button" value="Search">
            </form>
            <form method="get">

                <input
                        type="hidden"
                        name="page"
                        value="askiviki-wa-inbox">

                <select name="tag">

                    <option value="">
                        All Tags
                    </option>

                    <?php

                    $unique_tags = [];

                    foreach ($all_tags as $tag_string) {

                        $tags = array_map(
                            'trim',
                            explode(',', $tag_string)
                        );

                        foreach ($tags as $tag) {

                            if (
                                !empty($tag) &&
                                !in_array(
                                    $tag,
                                    $unique_tags,
                                    true
                                )
                            ) {
                                $unique_tags[] = $tag;
                            }
                        }
                    }

                    sort($unique_tags);

                    foreach ($unique_tags as $tag) :
                        ?>

                        <option
                                value="<?php echo esc_attr($tag); ?>"
                            <?php selected(
                                $selected_tag,
                                $tag
                            ); ?>>

                            <?php echo esc_html($tag); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <select
                        name="attention">

                    <option value="">
                        All Chats
                    </option>

                    <option
                            value="1"
                        <?php selected($attention, '1'); ?>>
                        🔴 Attention Required
                    </option>

                </select>

                <input
                        type="submit"
                        class="button"
                        value="Filter">

            </form>

            <br>
            <p> Found <strong> <?php echo esc_html( count($messages) ); ?> </strong> conversation(s). </p>
            <table class="widefat striped">

                <thead>
                <tr>
                    <th>Phone</th>
                    <th>Last Message</th>
                    <th>Last Activity</th>
                    <th>Status</th>
                    <th>Tags</th>
                    <th>Priority</th>
                    <th>Open</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($messages)) : ?>

                    <tr>
                        <td colspan="5">
                            No conversations found.
                        </td>
                    </tr>
                <?php else:?>
                    <?php foreach ($messages as $message): ?>
                        <?php

                        $tags = $wpdb->get_var(
                            $wpdb->prepare(
                                "
        SELECT tags
        FROM {$wpdb->prefix}askiviki_wa_customer_notes
        WHERE phone = %s
        ",
                                $message->phone
                            )
                        );
                        $customer_note =
                            $wpdb->get_row(
                                $wpdb->prepare(
                                    "
            SELECT *
            FROM {$wpdb->prefix}askiviki_wa_customer_notes
            WHERE phone = %s
            LIMIT 1
            ",
                                    $message->phone
                                )
                            );

                        $unread = $wpdb->get_var(
                            $wpdb->prepare(
                                "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}askiviki_wa_messages
        WHERE phone = %s
        AND is_read = 0
        ",
                                $message->phone
                            )
                        );
                        $last_customer_message =
                            $wpdb->get_var(
                                $wpdb->prepare(
                                    "
            SELECT message
            FROM {$wpdb->prefix}askiviki_wa_messages
            WHERE phone = %s
            AND (
                message_type = 'text'
                OR message_type = 'incoming'
            )
            ORDER BY created_at DESC
            LIMIT 1
            ",
                                    $message->phone
                                )
                            );
                        $last_activity =
                            strtotime(
                                $message->created_at
                            );

                        $is_recent =
                            (
                                time() -
                                $last_activity
                            ) < 3600;
                        ?>
                        <tr>

                            <td>
                                <?php echo esc_html($message->phone); ?>

                                <?php if ($unread > 0) : ?>
                                    <span style="
                                        background:red;
                                        color:#fff;
                                        padding:2px 8px;
                                        border-radius:20px;
                                        margin-left:5px;
                                        font-weight:bold;
                                    ">
                                        <?php echo esc_html($unread); ?>
                                    </span>
                                <?php endif; ?>
                                <small style="color:#666;display:block;">

                                    <?php echo esc_html(
                                        wp_trim_words(
                                            $last_customer_message,
                                            8
                                        )
                                    ); ?>

                                </small>
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
                                <?php if ($is_recent) : ?>
                                    <span style="color:#008a20;font-weight:bold;">
                                        🟢 Active
                                    </span>
                                <?php else : ?>
                                    <span style="color:#777;font-weight:bold;">
                                        ⚪ Idle
                                    </span>
                                <?php endif; ?>
                                <br>

                                <small style="color:#666;">

                                    <?php echo esc_html(
                                        human_time_diff(
                                            $last_activity,
                                            current_time('timestamp')
                                        )
                                    ); ?>

                                    ago

                                </small>
                            </td>

                            <td>
                                <?php
                                if (!empty($tags)) {
                                    foreach ( explode(',', $tags) as $tag ) {
                                        ?>
                                        <span style="
                                    background:#2271b1;
                                    color:#fff;
                                    padding:3px 8px;
                                    border-radius:20px;
                                    margin-right:3px;
                                ">
                            <?php
                            echo esc_html(trim($tag));
                            ?>
                                </span>
                                        <?php
                                    }
                                }
                                ?>
                            </td>

                            <td>
                                <?php if ( $customer_note && $customer_note->is_vip ) {
                                    echo '⭐ VIP';
                                }
                                if ( $customer_note && $customer_note->priority_level === 'urgent' ) {
                                    echo ' 🚨 Urgent';
                                }
                                ?>

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
                <?php endif; ?>

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
            $wpdb->prefix .'askiviki_wa_messages',
            [
                'wa_id'       => '',
                'phone'       => $phone,
                'message'     => $message,
                'message_type'=> 'admin_reply',
                'created_at'  => current_time('mysql')
            ]
        );
        $wpdb->update(
            $wpdb->prefix .'askiviki_wa_messages',
            [
                'is_read' => 1
            ],
            [
                'phone' => $phone
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
        $wpdb->update(
            $wpdb->prefix .'askiviki_wa_messages',
            [
                'is_read' => 1
            ],
            [
                'phone' => $phone
            ]
        );

        $orders = wc_get_orders([
            'limit' => -1
        ]);

        $customer_orders = [];

        foreach ($orders as $order) {

            $order_phone = preg_replace(
                '/[^0-9]/',
                '',
                $order->get_billing_phone()
            );

            $chat_phone = preg_replace(
                '/[^0-9]/',
                '',
                $phone
            );

            if (strlen($order_phone) === 10) {
                $order_phone = '91' . $order_phone;
            }

            if (strlen($chat_phone) === 10) {
                $chat_phone = '91' . $chat_phone;
            }

            if ($order_phone === $chat_phone) {
                $customer_orders[] = $order;
            }
        }

        $total_orders = count(
            $customer_orders
        );

        $total_spend = 0;

        foreach ($customer_orders as $order) {

            $total_spend +=
                (float) $order->get_total();
        }

        $last_order = !empty($customer_orders)
            ? end($customer_orders)
            : null;

        $customer_name = '';
        $customer_email = '';
        $billing_address = '';
        $shipping_address = '';
        $customer_since = '';
        $average_order_value = 0;

        if ($last_order) {

            $customer_name =
                $last_order
                    ->get_formatted_billing_full_name();
            $customer_email =
                $last_order->get_billing_email();

            $billing_address =
                $last_order->get_formatted_billing_address();

            $shipping_address =
                $last_order->get_formatted_shipping_address();

            $first_order =
                !empty($customer_orders)
                    ? reset($customer_orders)
                    : null;

            $customer_since =
                $first_order &&
                $first_order->get_date_created()
                    ? $first_order
                    ->get_date_created()
                    ->date('Y-m-d')
                    : '';

            $average_order_value =
                $total_orders > 0
                    ? $total_spend / $total_orders
                    : 0;
        }

        $customer_note =
            $wpdb->get_row(
                $wpdb->prepare(
                    "
            SELECT *
            FROM {$wpdb->prefix}askiviki_wa_customer_notes
            WHERE phone = %s
            LIMIT 1
            ",
                    $phone
                )
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
            <?php if (isset($_GET['note_saved'])) : ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Customer notes saved.
                    </p>
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

            <div style="background:#fff;border:1px solid #ddd;padding:20px;margin-bottom:20px;border-radius:8px;">
                <h1 style="margin:0;">
                    <?php echo esc_html($customer_name ?: 'Unknown Customer'); ?>
                </h1>
                <p style="margin:5px 0 15px;color:#666;">
                    <?php echo esc_html($phone); ?>
                </p>
                <div style="margin-top:10px;">
                    <?php if (!empty($customer_note->is_vip)) : ?>
                        <span style="background:#fff3cd;color:#856404;padding:4px 10px;border-radius:20px;margin-right:5px;font-weight:bold;">
                            ⭐ VIP
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($customer_note->is_pinned)) : ?>
                        <span style="background:#d1ecf1;color:#0c5460;padding:4px 10px;border-radius:20px;margin-right:5px;font-weight:bold;">
                            📌 Pinned
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($customer_note->priority) && $customer_note->priority === 'urgent') : ?>
                        <span style="background:#f8d7da;color:#721c24;padding:4px 10px;border-radius:20px;font-weight:bold;">
                            🚨 Urgent
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:20px;margin-bottom:20px;">
                <div style="
            flex:1 1 350px;
            min-width:300px;
            background:#fff;
            padding:20px;
            border:1px solid #ddd;
            margin-bottom:20px;
        ">

                    <h2>Customer Profile</h2>

                    <p>
                        <strong>Name:</strong>
                        <?php echo esc_html(
                            $customer_name ?: 'Unknown'
                        ); ?>
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        <?php echo esc_html($phone); ?>
                    </p>

                    <p>
                        <strong>Email:</strong>
                        <?php echo esc_html(
                            $customer_email ?: '-'
                        ); ?>
                    </p>

                    <p>
                        <strong>Total Orders:</strong>
                        <?php echo esc_html(
                            $total_orders
                        ); ?>
                    </p>

                    <p>
                        <strong>Total Spend:</strong>
                        ₹<?php echo esc_html(
                            number_format(
                                $total_spend,
                                2
                            )
                        ); ?>
                    </p>

                    <p>
                        <strong>Customer Since:</strong>
                        <?php echo esc_html(
                            $customer_since ?: '-'
                        ); ?>
                    </p>

                    <p>
                        <strong>
                            Average Order Value:
                        </strong>

                        ₹<?php echo esc_html(
                            number_format(
                                $average_order_value,
                                2
                            )
                        ); ?>
                    </p>

                    <p>
                        <strong>
                            Billing Address:
                        </strong>

                        <br>

                        <?php echo wp_kses_post(
                            $billing_address ?: '-'
                        ); ?>
                    </p>

                    <p>
                        <strong>
                            Shipping Address:
                        </strong>

                        <br>

                        <?php echo wp_kses_post(
                            $shipping_address ?: '-'
                        ); ?>
                    </p>

                    <?php if ($last_order): ?>

                        <p>
                            <strong>Last Order:</strong>
                            #<?php echo esc_html(
                                $last_order->get_order_number()
                            ); ?>
                        </p>

                        <p>
                            <strong>Status:</strong>
                            <?php echo esc_html(
                                wc_get_order_status_name(
                                    $last_order->get_status()
                                )
                            ); ?>
                        </p>

                        <p>
                            <a
                                class="button"
                                href="<?php echo admin_url(
                                    'post.php?post=' .
                                    $last_order->get_id() .
                                    '&action=edit'
                                ); ?>">
                                View Order
                            </a>
                        </p>

                    <?php endif; ?>

                </div>
                <div style="
            flex:2 1 700px;
            min-width:300px;
    background:#fff;
    padding:20px;
    border:1px solid #ddd;
    margin-bottom:20px;
">

                    <h2>Order History</h2>

                    <?php if (empty($customer_orders)) : ?>

                        <p>No orders found.</p>

                    <?php else : ?>
                        <p>

                            <strong>
                                Total Orders:
                            </strong>

                            <?php echo esc_html(
                                $total_orders
                            ); ?>

                        </p>
                        <div style="overflow-x:auto;">
                            <table class="widefat striped">

                                <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tbody>

                                <?php foreach (
                                    array_reverse($customer_orders)
                                    as $order
                                ) : ?>

                                    <tr>

                                        <td>
                                            #<?php echo esc_html(
                                                $order->get_order_number()
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo esc_html(
                                                $order->get_date_created()
                                                    ? $order->get_date_created()->date(
                                                    'Y-m-d'
                                                )
                                                    : '-'
                                            ); ?>
                                        </td>

                                        <td>
                                        <span class="button">
                                            <?php echo esc_html(
                                                wc_get_order_status_name(
                                                    $order->get_status()
                                                )
                                            ); ?>
                                        </span>
                                        </td>

                                        <td>
                                            ₹<?php echo esc_html(
                                                number_format(
                                                    (float)$order->get_total(),
                                                    2
                                                )
                                            ); ?>
                                        </td>

                                        <td>

                                            <a
                                                class="button"
                                                href="<?php echo admin_url(
                                                    'post.php?post=' .
                                                    $order->get_id() .
                                                    '&action=edit'
                                                ); ?>">

                                                View

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

            <div style="
    background:#fff;
    padding:20px;
    border:1px solid #ddd;
    margin-bottom:20px;
">

                <h2>Internal Notes & Tags</h2>

                <?php

                $tags = [];

                if ( !empty($customer_note->tags)) {
                    $tags = array_map('trim',explode(',',$customer_note->tags));
                }

                ?>

                <?php if (!empty($tags)) : ?>

                    <p>

                        <?php foreach ($tags as $tag) : ?>

                            <span style="
                display:inline-block;
                background:#2271b1;
                color:#fff;
                padding:4px 10px;
                border-radius:20px;
                margin-right:5px;
                margin-bottom:5px;
            ">

                🏷 <?php echo esc_html($tag); ?>

            </span>

                        <?php endforeach; ?>

                    </p>

                <?php endif; ?>

                <form method="post">

                    <?php wp_nonce_field(
                        'askiviki_save_customer_note',
                        'askiviki_note_nonce'
                    ); ?>

                    <input
                            type="hidden"
                            name="customer_phone"
                            value="<?php echo esc_attr(
                                $phone
                            ); ?>">

                    <p>

                        <label>
                            <strong>Tags</strong>
                        </label>

                        <br>

                        <input
                                type="text"
                                name="customer_tags"
                                style="width:100%;"
                                value="<?php echo esc_attr(
                                    $customer_note->tags ?? ''
                                ); ?>"
                                placeholder="VIP, Frequent Buyer">

                    </p>

                    <p>

                        <label>

                            <input
                                type="checkbox"
                                name="is_vip"
                                value="1"

                                <?php checked(
                                    $customer_note->is_vip ?? 0,
                                    1
                                ); ?>>

                            VIP Customer

                        </label>

                    </p>

                    <p>

                        <label>

                            Priority Level

                        </label>

                        <br>

                        <select
                            name="priority_level">

                            <option
                                value="normal"
                                <?php selected(
                                    $customer_note->priority_level ?? '',
                                    'normal'
                                ); ?>>

                                Normal

                            </option>

                            <option
                                value="high"
                                <?php selected(
                                    $customer_note->priority_level ?? '',
                                    'high'
                                ); ?>>

                                High

                            </option>

                            <option
                                value="urgent"
                                <?php selected(
                                    $customer_note->priority_level ?? '',
                                    'urgent'
                                ); ?>>

                                Urgent

                            </option>

                        </select>

                    </p>

                    <p>

                        <label>

                            <input
                                type="checkbox"
                                name="is_pinned"
                                value="1"

                                <?php checked(
                                    $customer_note->is_pinned ?? 0,
                                    1
                                ); ?>>

                            Pin Customer

                        </label>

                    </p>

                    <p>

                        <label>
                            <strong>Internal Notes</strong>
                        </label>

                        <br>

                        <textarea
                                name="customer_notes"
                                rows="8"
                                style="width:100%;"><?php

                            echo esc_textarea(
                                $customer_note->notes ?? ''
                            );

                            ?></textarea>

                    </p>

                    <p>

                        <input
                                type="submit"
                                name="askiviki_save_note"
                                class="button button-primary"
                                value="Save Notes">

                    </p>

                </form>

            </div>

            <div style="
            max-width:800px;
            background:#fff;
            padding:20px;
            border:1px solid #ddd;
            max-height:500px;
            overflow-y:auto;
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
                                padding:12px;
                                border-radius:12px;
                                max-width:70%;
                                word-wrap:break-word;
                                background:
                        <?php echo
                        $message->message_type === 'admin_reply'
                            ? '#dcf8c6'
                            : '#f1f1f1';
                        ?>;
                                margin-left:
                        <?php echo
                        $message->message_type === 'admin_reply'
                            ? 'auto'
                            : '0';
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
                            <?php if ( $message->message_type === 'admin_reply' ) : ?>
                                <br>
                                <small style="color:#008a20;font-weight:bold;">
                                    ✓ Sent
                                </small>
                            <?php endif; ?>

                            <br>

                            <small style="color:#666;">
                                <?php echo esc_html(
                                    $message->created_at
                                ); ?>
                            </small>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>
                <div style="position:sticky;bottom:0;background:#fff;border-top:1px solid #ddd;z-index:10;box-shadow:0 -2px 8px rgba(0,0,0,.08);">
                    <form method="post" style="margin-top:20px;">

                        <?php wp_nonce_field(
                            'askiviki_reply_message',
                            'askiviki_reply_nonce'
                        );
                        $quick_replies = $wpdb->get_results( " SELECT * FROM {$wpdb->prefix}askiviki_wa_quick_replies ORDER BY title ASC ");
                        ?>
                        <?php if (!empty($quick_replies)) : ?>

                            <div style="margin-bottom:15px;">
                                <h3 style="margin-top:0;margin-bottom:10px;">
                                    ⚡ Quick Replies
                                </h3>
                                <?php foreach ( $quick_replies as $reply ) : ?>
                                    <button type="button" class="button quick-reply-btn" data-message="<?php echo esc_attr($reply->message ); ?>">
                                        <?php echo esc_html( $reply->title ); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

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
                    <script>
                        document.querySelectorAll('.quick-reply-btn').forEach(function(btn){
                                    btn.addEventListener('click',function(){
                                            document.querySelector('textarea[name="reply_message"]').value += this.dataset.message;
                                        }
                                    );
                            });

                    </script>
                </div>
            </div>

        </div>

        <?php
    }
    public function save_customer_note()
    {
        if (
        !isset(
            $_POST['askiviki_save_note']
        )
        ) {
            return;
        }

        if (
        !wp_verify_nonce(
            $_POST['askiviki_note_nonce'],
            'askiviki_save_customer_note'
        )
        ) {
            return;
        }

        global $wpdb;

        $phone = sanitize_text_field(
            $_POST['customer_phone']
        );

        $tags = sanitize_text_field(
            $_POST['customer_tags']
        );

        $notes = sanitize_textarea_field(
            $_POST['customer_notes']
        );
        $is_vip = isset($_POST['is_vip']) ? 1 : 0;

        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

        $priority_level =
            sanitize_text_field(
                $_POST['priority_level']
            );

        $existing =
            $wpdb->get_var(
                $wpdb->prepare(
                    "
                SELECT id
                FROM {$wpdb->prefix}askiviki_wa_customer_notes
                WHERE phone = %s
                ",
                    $phone
                )
            );

        if ($existing) {

            $wpdb->update(
                $wpdb->prefix .'askiviki_wa_customer_notes',
                [
                    'tags' => $tags,
                    'notes' => $notes,
                    'priority_level' => $priority_level,
                    'is_vip' => $is_vip,
                    'is_pinned' => $is_pinned,
                    'updated_at' =>
                        current_time('mysql')
                ],
                [
                    'id' => $existing
                ]
            );

        } else {

            $wpdb->insert(
                $wpdb->prefix .'askiviki_wa_customer_notes',
                [
                    'phone' => $phone,
                    'tags' => $tags,
                    'notes' => $notes,
                    'priority_level' => $priority_level,
                    'is_vip' => $is_vip,
                    'is_pinned' => $is_pinned,
                    'created_at' =>
                        current_time('mysql')
                ]
            );
        }

        wp_redirect(
            add_query_arg(
                [
                    'page' =>
                        'askiviki-conversation',
                    'phone' => $phone,
                    'note_saved' => 1
                ],
                admin_url(
                    'admin.php'
                )
            )
        );

        exit;
    }
    public function quick_replies_page()
    {
    global $wpdb;

    $table = $wpdb->prefix .'askiviki_wa_quick_replies';

    if ( isset($_POST['save_quick_reply'])) {

        $wpdb->insert(
            $table,
            [
                'title' =>
                    sanitize_text_field(
                        $_POST['title']
                    ),

                'message' =>
                    sanitize_textarea_field(
                        $_POST['message']
                    ),

                'created_at' =>
                    current_time(
                        'mysql'
                    ),

                'updated_at' =>
                    current_time(
                        'mysql'
                    )
            ]
        );
        echo '
            <div class="notice notice-success is-dismissible">
                <p>
                    Quick reply saved successfully.
                </p>
            </div>';
    }
    ?>
    <div class="wrap">
        <h1>
            Quick Replies
        </h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>
                        Title
                    </th>
                    <td>
                        <input type="text" name="title" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th>
                        Message
                    </th>
                    <td>
                        <textarea name="message" rows="6" cols="60" required></textarea>
                    </td>
                </tr>
            </table>
            <p>
                <input type="submit" name="save_quick_reply" class="button button-primary" value="Save Reply">
            </p>
        </form>
        <?php
        $replies = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );
        ?>
        <h2> Saved Replies </h2>
        <table class="widefat striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Message</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ( $replies as $reply ) : ?>
                <tr>
                    <td> <?php echo esc_html( $reply->id ); ?> </td>
                    <td> <?php echo esc_html( $reply->title ); ?> </td>
                    <td> <?php echo esc_html( $reply->message ); ?> </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
        <?php
    }
        
}