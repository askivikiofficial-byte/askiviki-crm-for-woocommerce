<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Webhook
{
    public function __construct()
    {
        add_action(
            'rest_api_init',
            [$this, 'register_routes']
        );
    }

    public function register_routes()
    {
        register_rest_route(
            'askiviki/v1',
            '/webhook',
            [
                'methods' => 'GET',
                'callback' => [$this, 'verify'],
                'permission_callback' => '__return_true'
            ]
        );

        register_rest_route(
            'askiviki/v1',
            '/webhook',
            [
                'methods' => 'POST',
                'callback' => [$this, 'receive'],
                'permission_callback' => '__return_true'
            ]
        );
    }

    public function verify($request)
    {
        $mode = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';

        if (
            $mode === 'subscribe' &&
            $token === get_option('askiviki_wa_verify_token')
        ) {

            header('Content-Type: text/plain');

            echo $challenge;

            exit;
        }

        return new WP_Error(
            'forbidden',
            'Verification failed'
        );
    }

    public function receive($request)
    {
        global $wpdb;

        $data = json_decode(
            $request->get_body(),
            true
        );

        $statuses =
            $data['entry'][0]['changes'][0]
            ['value']['statuses']
            ?? [];
        $messages =
            $data['entry'][0]['changes'][0]
            ['value']['messages']
            ?? [];

        foreach ($statuses as $status) {

            $result = $wpdb->update(
                $wpdb->prefix . 'askiviki_wa_logs',
                [
                    'status' => $status['status']
                ],
                [
                    'message_id' => $status['id']
                ]
            );
        }

        error_log(
            '[AskIViki Incoming] ' .
            wp_json_encode($messages)
        );
        foreach ($messages as $message) {

            $result = $wpdb->insert(
                $wpdb->prefix .
                'askiviki_wa_messages',
                [
                    'wa_id' =>
                        $message['id'] ?? '',

                    'phone' =>
                        $message['from'] ?? '',

                    'message' =>
                        $message['text']['body']
                        ?? '',

                    'message_type' =>
                        $message['type']
                        ?? 'text',

                    'created_at' =>
                        current_time('mysql')
                ]
            );

            error_log(
                '[AskIViki Insert Result] ' .
                $result
            );

            error_log(
                '[AskIViki DB Error] ' .
                $wpdb->last_error
            );
        }

        return [
            'success' => true
        ];
    }
}