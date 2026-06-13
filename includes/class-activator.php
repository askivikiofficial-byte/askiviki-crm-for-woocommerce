<?php

if (!defined('ABSPATH')) {
    exit;
}

class AskIViki_WA_Activator {

    public static function activate() {

        add_option('askiviki_wa_enabled', 'yes');

    }

}