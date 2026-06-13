<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_WooCommerce_Hooks
{
    public function __construct()
    {
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

        $template = get_option(
            'askiviki_wa_processing_template'
        );

        $message = $this->parse_template(
            $template,
            $order,
            'processing'
        );

        $this->send_order_message(
            $order,
            $message
        );
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

        $template = get_option(
            'askiviki_wa_completed_template'
        );

        $message = $this->parse_template(
            $template,
            $order,
            'completed'
        );

        $this->send_order_message(
            $order,
            $message
        );
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

        $template = get_option(
            'askiviki_wa_cancelled_template'
        );

        $message = $this->parse_template(
            $template,
            $order,
            'cancelled'
        );

        $this->send_order_message(
            $order,
            $message
        );
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
}