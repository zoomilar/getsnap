<?php
/**
 * Plugin Name: GetSnap
 * Description: Visual search suite for your Woocommerce store
 * Version: 1.0
 * Author: GetSnap
 * License: GPL v2 or later
 * Text Domain: https://getsnap.eu
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-getsnap.php';

function run_getsnap() {
    $plugin = new GetSnap();
    $plugin->run();
}

run_getsnap();