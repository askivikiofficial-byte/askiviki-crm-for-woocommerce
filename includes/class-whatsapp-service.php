<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Service
{
    public function send_message($phone, $message)
    {
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
        if (is_wp_error($response)) {
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);

        return $code >= 200 && $code < 300;
    }
    private function format_phone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }
}