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
        $order = wc_get_order($order_id);

        $phone = $order->get_billing_phone();

        $message = sprintf(
            "Hi %s,\n\nYour order #%s is now Processing.\n\nTotal: ₹%s",
            $order->get_billing_first_name(),
            $order->get_order_number(),
            number_format((float) $order->get_total(), 2)
        );

        $service = new AskIViki_WA_Service();
        $service->send_message($phone, $message);
    }

    public function order_completed($order_id)
    {
        $order = wc_get_order($order_id);

        $phone = $order->get_billing_phone();

        $message = sprintf(
            "Hi %s,\n\nYour order #%s has been Completed.\n\nThank you for shopping with us.",
            $order->get_billing_first_name(),
            $order->get_order_number()
        );

        $service = new AskIViki_WA_Service();
        $service->send_message($phone, $message);
    }

    public function order_cancelled($order_id)
    {
        $order = wc_get_order($order_id);

        $phone = $order->get_billing_phone();

        $message = sprintf(
            "Hi %s,\n\nYour order #%s has been Cancelled.",
            $order->get_billing_first_name(),
            $order->get_order_number()
        );

        $service = new AskIViki_WA_Service();
        $service->send_message($phone, $message);
    }
}