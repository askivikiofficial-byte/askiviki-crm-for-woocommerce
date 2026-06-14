<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_WooCommerce_Hooks
{
    public function __construct()
    {
        add_action(
            'woocommerce_new_order',
            [$this, 'admin_new_order']
        );
        add_action(
            'woocommerce_order_status_processing',
            [$this, 'order_processing']
        );

        add_action(
            'woocommerce_order_status_completed',
            [$this, 'order_completed']
        );

        add_action(
            'woocommerce_order_status_cancelled',
            [$this, 'order_cancelled']
        );
    }

    public function order_processing($order_id)
    {
        if (
            get_option(
                'askiviki_wa_notify_processing',
                'yes'
            ) !== 'yes'
        ) {
            return;
        }
        $order = wc_get_order($order_id);

        $service = new AskIViki_WA_Service();

        $sent = $service->send_template_message(
            $order->get_billing_phone(),
            get_option(
                'askiviki_wa_processing_template_name',
                'order_processing'
            ),
            [
                'customer_name' =>
                    $order->get_billing_first_name(),

                'order_id' =>
                    $order->get_order_number(),

                'order_total' =>
                    number_format(
                        (float)$order->get_total(),
                        2
                    )
            ]
        );
        if (!$sent) {

            error_log(
                '[AskIViki] Processing fallback triggered'
            );

            $template = get_option(
                'askiviki_wa_processing_template'
            );

            $message = $this->parse_template(
                $template,
                $order,
                'processing'
            );

            $service->send_message(
                $order->get_billing_phone(),
                $message
            );
        }
    }
    public function order_completed($order_id)
    {
        if (
            get_option(
                'askiviki_wa_notify_completed',
                'yes'
            ) !== 'yes'
        ) {
            return;
        }
        $order = wc_get_order($order_id);

        $service = new AskIViki_WA_Service();

        $sent = $service->send_template_message(
            $order->get_billing_phone(),
            get_option(
                'askiviki_wa_completed_template_name',
                'order_completed'
            ),
            [
                'customer_name' =>
                    $order->get_billing_first_name(),

                'order_id' =>
                    $order->get_order_number(),

                'order_total' =>
                    number_format(
                        (float)$order->get_total(),
                        2
                    )
            ]
        );
        if (!$sent) {

            error_log(
                '[AskIViki] Completed fallback triggered'
            );

            $template = get_option(
                'askiviki_wa_completed_template'
            );

            $message = $this->parse_template(
                $template,
                $order,
                'completed'
            );

            $service->send_message(
                $order->get_billing_phone(),
                $message
            );
        }
    }
    public function order_cancelled($order_id)
    {
        if (
            get_option(
                'askiviki_wa_notify_cancelled',
                'yes'
            ) !== 'yes'
        ) {
            return;
        }
        $order = wc_get_order($order_id);

        $service = new AskIViki_WA_Service();

        $sent = $service->send_template_message(
            $order->get_billing_phone(),
            get_option(
                'askiviki_wa_cancelled_template_name',
                'order_cancelled'
            ),
            [
                'customer_name' =>
                    $order->get_billing_first_name(),

                'order_id' =>
                    $order->get_order_number(),

                'order_total' =>
                    number_format(
                        (float)$order->get_total(),
                        2
                    )
            ]
        );
        if (!$sent) {

            error_log(
                '[AskIViki] Cancelled fallback triggered'
            );

            $template = get_option(
                'askiviki_wa_cancelled_template'
            );

            $message = $this->parse_template(
                $template,
                $order,
                'cancelled'
            );

            $service->send_message(
                $order->get_billing_phone(),
                $message
            );
        }
    }
    private function send_order_message($order, $message)
    {
        if (!$order) {
            return;
        }

        $phone = $order->get_billing_phone();

        if (empty($phone)) {
            return;
        }
        $service = new AskIViki_WA_Service();
        $service->send_message($phone, $message);
    }
    private function parse_template(
        $template,
        $order,
        $status
    )
    {
        return str_replace(
            [
                '{customer_name}',
                '{order_id}',
                '{order_total}',
                '{site_name}',
                '{status}'
            ],
            [
                $order->get_billing_first_name(),
                $order->get_order_number(),
                number_format(
                    (float)$order->get_total(),
                    2
                ),
                get_bloginfo('name'),
                ucfirst($status)
            ],
            $template
        );
    }
    public function admin_new_order($order_id)
    {
        if (
            get_option(
                'askiviki_wa_admin_notifications',
                'yes'
            ) !== 'yes'
        ) {
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $admin_phone = get_option(
            'askiviki_wa_phone'
        );

        $message = sprintf(
            "🛒 New Order Received\n\nOrder: #%s\nCustomer: %s\nPhone: %s\nTotal: ₹%s",
            $order->get_order_number(),
            $order->get_billing_first_name(),
            $order->get_billing_phone(),
            number_format(
                (float)$order->get_total(),
                2
            )
        );

        $service = new AskIViki_WA_Service();

        $service->send_message(
            $admin_phone,
            $message
        );
    }
}