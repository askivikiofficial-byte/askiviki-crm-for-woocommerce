<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Service
{
    public function send_message($phone, $message)
    {
        if (
            get_option(
                'askiviki_wa_enabled',
                'yes'
            ) !== 'yes'
        ) {
            error_log(
                '[AskIViki WA] WhatsApp disabled'
            );

            return false;
        }
        $phone_id   = get_option('askiviki_wa_phone_id');
        $token      = get_option('askiviki_wa_access_token');
        $phone      = $this->format_phone($phone);
        $body = [
            'messaging_product' => 'whatsapp',
            'to'                => $phone,
            'type'              => 'text',
            'text' => [
                'body' => $message
            ]
        ];
        if (get_option('askiviki_wa_debug') === 'yes') {
            error_log('[AskIViki WA] Phone: ' . $phone);
        }
        if (empty($phone_id) || empty($token) || empty($phone)) {
            return false;
        }
        $response = wp_remote_post(
            "https://graph.facebook.com/v25.0/{$phone_id}/messages",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json'
                ],
                'body'    => wp_json_encode($body),
                'timeout' => 30
            ]
        );
        $response_body = [];

        if (!is_wp_error($response)) {

            $response_body = json_decode(
                wp_remote_retrieve_body($response),
                true
            );
        }

        $message_id = $response_body['messages'][0]['id'] ?? '';

        $status = is_wp_error($response)
            ? 'failed'
            : 'sent';

        $this->save_log(
            null,
            $phone,
            $status,
            $message,
            wp_remote_retrieve_body($response),
            $message_id
        );
        if (is_wp_error($response)) {
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);

        return $code >= 200 && $code < 300;
    }
    public function send_template_message(
        $phone,
        $template_name,
        $parameters = []
    )
    {
        if (
            get_option(
                'askiviki_wa_enabled',
                'yes'
            ) !== 'yes'
        ) {
            return false;
        }

        $phone_id = get_option(
            'askiviki_wa_phone_id'
        );

        $token = get_option(
            'askiviki_wa_access_token'
        );

        $phone = $this->format_phone(
            $phone
        );

        if (
            empty($phone_id) ||
            empty($token) ||
            empty($phone)
        ) {
            return false;
        }

        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $template_name,
                'language' => [
                    'code' => get_option(
                        'askiviki_wa_template_language',
                        'en_US'
                    )
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => array_map(
                            function ($value, $key) {
                                return [
                                    'type'           => 'text',
                                    'parameter_name' => $key,
                                    'text'           => (string) $value
                                ];
                            },
                            $parameters,
                            array_keys($parameters)
                        )
                    ]
                ]
            ]
        ];

        error_log(
            '[AskIViki Template Body] ' .
            wp_json_encode($body)
        );
        $response = wp_remote_post(
            "https://graph.facebook.com/v25.0/{$phone_id}/messages",
            [
                'headers' => [
                    'Authorization' =>
                        'Bearer ' . $token,
                    'Content-Type' =>
                        'application/json'
                ],
                'body' =>
                    wp_json_encode($body),
                'timeout' => 30
            ]
        );

        $response_body = [];

        if (!is_wp_error($response)) {
            $response_body = json_decode(
                wp_remote_retrieve_body(
                    $response
                ),
                true
            );
        }

        $message_id =
            $response_body['messages'][0]['id']
            ?? '';

        $status =
            is_wp_error($response)
                ? 'failed'
                : 'sent';

        $this->save_log(
            null,
            $phone,
            $status,
            'Template: ' . $template_name,
            wp_remote_retrieve_body(
                $response
            ),
            $message_id
        );

        if (is_wp_error($response)) {
            return false;
        }

        $code =
            wp_remote_retrieve_response_code(
                $response
            );

        return $code >= 200 && $code < 300;
    }
    public function send_template(
        $phone,
        $template_name = 'hello_world'
    )
    {
        return $this->send_template_message(
            $phone,
            $template_name
        );
    }
    private function format_phone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }
    private function save_log($order_id,$phone,$status,$message,$response,$message_id = '')
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'askiviki_wa_logs',
            [
                'order_id'   => $order_id,
                'phone'      => $phone,
                'status'     => $status,
                'message'    => $message,
                'response'   => maybe_serialize($response),
                'message_id' => $message_id,
                'created_at' => current_time('mysql')
            ]
        );
    }
}