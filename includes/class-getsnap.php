<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-getsnap-loader.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-getsnap-admin.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-getsnap-public.php';

class GetSnap {

    protected $loader;

    public function __construct() {
        $this->loader = new GetSnap_Loader();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function define_admin_hooks() {
        $admin = new GetSnap_Admin();
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
        // Add more hooks for the admin as needed
    }

    private function define_public_hooks() {
        $public = new GetSnap_Public();
        $this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
        // Add more hooks for the public side as needed
    }

    public function run() {
        $this->loader->run();
    }

}