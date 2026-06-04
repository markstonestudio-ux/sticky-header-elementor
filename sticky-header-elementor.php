<?php
/**
 * Plugin Name: Sticky Header for Elementor
 * Description: Añade comportamiento sticky header totalmente personalizable a secciones y contenedores de Elementor.
 * Version:     1.0.0
 * Author:      Marco Dev
 * License:     GPL-2.0+
 * Text Domain: she
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'SHE_VERSION', '1.0.0' );
define( 'SHE_PATH',    plugin_dir_path( __FILE__ ) );
define( 'SHE_URL',     plugin_dir_url( __FILE__ ) );

final class Sticky_Header_Elementor {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            return;
        }
        add_action( 'elementor/init', [ $this, 'load_extension' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    public function load_extension() {
        require_once SHE_PATH . 'includes/class-she-extension.php';
        SHE_Extension::instance();
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'she-sticky-header', SHE_URL . 'assets/css/sticky-header.css', [], SHE_VERSION );
        wp_enqueue_script( 'she-sticky-header', SHE_URL . 'assets/js/sticky-header.js', [], SHE_VERSION, true );

        $breakpoints = [ 'mobile' => 767, 'tablet' => 1024 ];
        if ( class_exists( '\Elementor\Plugin' ) ) {
            $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
            if ( $kit ) {
                $bp_m = $kit->get_settings_for_display( 'viewport_mobile' );
                $bp_t = $kit->get_settings_for_display( 'viewport_tablet' );
                if ( ! empty( $bp_m['size'] ) ) $breakpoints['mobile'] = $bp_m['size'];
                if ( ! empty( $bp_t['size'] ) ) $breakpoints['tablet'] = $bp_t['size'];
            }
        }
        wp_localize_script( 'she-sticky-header', 'sheConfig', [ 'breakpoints' => $breakpoints ] );
    }

    public function admin_notice_missing_elementor() {
        printf(
            '<div class="notice notice-error"><p><strong>Sticky Header for Elementor</strong> requiere que Elementor este instalado y activado.</p></div>'
        );
    }
}

Sticky_Header_Elementor::instance();
