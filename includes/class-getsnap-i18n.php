<?php

class GetSnap_i18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'getsnap',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
