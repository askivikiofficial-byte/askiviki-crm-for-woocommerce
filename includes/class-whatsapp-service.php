<?php
class AskIViki_WA_Service {

    public function send_message($phone, $message) {

        error_log("WhatsApp: {$phone} - {$message}");

        return true;
    }
}