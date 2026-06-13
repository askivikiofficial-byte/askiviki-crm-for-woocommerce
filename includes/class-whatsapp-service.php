<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Service
{
    public function send_message($phone, $message)
    {
        error_log(
            sprintf(
                '[AskIViki WA] %s => %s',
                $phone,
                $message
            )
        );

        return true;
    }
}