<?php
/*
Plugin Name: پنل کاربری مدرن نئو
Description: پنل کاربری اختصاصی مبتنی بر AJAX برای ووکامرس
Version: 1.0.0
Author: ArminRah
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

define( 'NEO_PANEL_VERSION', '1.0.0' );
define( 'NEO_PANEL_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEO_PANEL_URL', plugin_dir_url( __FILE__ ) );

require_once NEO_PANEL_DIR . 'neo-user-panel.php';
require_once plugin_dir_path(__FILE__) . 'includes/vendor/vendor-core.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/wallet/wallet-core.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/wallet/wallet-gateway.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ticket/ticket-core.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ticket/ticket-ajax.php';
if (is_admin()) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin/admin-menu.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ticket-manager.php';
}
// 2. راه‌اندازی (Initialize) ماژول‌ها
Neo_Ticket_Core::init();

add_action( 'wp_enqueue_scripts', 'neo_panel_enqueue_assets' );

function neo_panel_enqueue_assets() {
    // 1. استایل اصلی پنل
    wp_enqueue_style(
        'neo-panel-style',
        NEO_PANEL_URL . 'assets/css/style.css',
        array(),
        NEO_PANEL_VERSION
    );

    // 2. استایل ماژول تیکت (جدید)
    wp_enqueue_style(
        'neo-ticket-style',
        NEO_PANEL_URL . 'assets/css/ticket.css',
        array('neo-panel-style'), // وابسته به استایل اصلی
        NEO_PANEL_VERSION
    );
    wp_enqueue_style( 'neo-vendor-style', plugin_dir_url( __FILE__ ) . 'assets/css/vendor.css', array(), '1.0.0' );
    // 3. اسکریپت اصلی پنل
    wp_enqueue_script(
        'neo-panel-script',
        NEO_PANEL_URL . 'assets/js/app.js',
        array('jquery'),
        NEO_PANEL_VERSION,
        true
    );

    // 4. اسکریپت کیف پول (اصلاح وابستگی به neo-panel-script)
    wp_enqueue_script(
        'neo-wallet-js',
        NEO_PANEL_URL . 'assets/js/wallet.js',
        array( 'neo-panel-script' ), 
        '1.0.0',
        true
    );

    // 5. اسکریپت ماژول تیکت (جدید)
    wp_enqueue_script(
        'neo-ticket-script',
        NEO_PANEL_URL . 'assets/js/ticket.js',
        array( 'neo-panel-script' ), // وابسته به اسکریپت اصلی پنل
        NEO_PANEL_VERSION,
        true
    );
    wp_enqueue_script( 'neo-vendor-js', plugin_dir_url( __FILE__ ) . 'assets/js/vendor.js', array('jquery'), '1.0.0', true );
    // ارسال متغیرهای AJAX به جاوااسکریپت
    wp_localize_script( 'neo-vendor-js', 'neo_ajax_obj', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'neo_panel_secure_nonce' )
    ));
}

add_filter( 'body_class', 'neo_panel_body_class' );
function neo_panel_body_class( $classes ) {
    if ( is_page() && has_shortcode( get_post()->post_content, 'neo_user_panel' ) ) {
        $classes[] = 'neo-panel-page';
    }
    return $classes;
}
add_shortcode( 'neo_user_panel', 'neo_render_panel_layout' );
?>
