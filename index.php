<?php
/*
Plugin Name: پنل کاربری مدرن نئو
Description: پنل کاربری اختصاصی مبتنی بر AJAX برای ووکامرس
Version: 1.0.0
Author: ArminRah
*/

if (! defined('ABSPATH')) {
    exit;
}

define('NEO_PANEL_VERSION', '1.0.0');
define('NEO_PANEL_DIR', plugin_dir_path(__FILE__));
define('NEO_PANEL_URL', plugin_dir_url(__FILE__));

require_once NEO_PANEL_DIR . 'neo-user-panel.php';
require_once plugin_dir_path(__FILE__) . 'includes/vendor/vendor-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/wallet/wallet-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/wallet/wallet-gateway.php';
require_once plugin_dir_path(__FILE__) . 'includes/ticket/ticket-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/ticket/ticket-ajax.php';

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-menu.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/ticket-manager.php';
}

// 2. راه‌اندازی (Initialize) ماژول‌ها
Neo_Ticket_Core::init();

add_action('wp_enqueue_scripts', 'neo_panel_enqueue_assets');

function neo_panel_enqueue_assets()
{
    // 1. استایل اصلی پنل
    wp_enqueue_style(
        'neo-panel-style',
        NEO_PANEL_URL . 'assets/css/style.css',
        array(),
        NEO_PANEL_VERSION
    );

    // 2. استایل ماژول تیکت
    wp_enqueue_style(
        'neo-ticket-style',
        NEO_PANEL_URL . 'assets/css/ticket.css',
        array('neo-panel-style'), // وابسته به استایل اصلی
        NEO_PANEL_VERSION
    );

    // ۳. استایل عمومی فروشنده
    wp_enqueue_style(
        'neo-vendor-style',
        NEO_PANEL_URL . 'assets/css/vendor.css',
        array(),
        '1.0.0'
    );

    // ۴. استایل اختصاصی جدول و محصولات فروشنده (جدید)
    wp_enqueue_style(
        'neo-vendor-products-style',
        NEO_PANEL_URL . 'assets/css/vendor-products.css',
        array('neo-vendor-style'),
        '1.0.0'
    );

    // ۵. فعال‌سازی آیکون‌های استاندارد وردپرس (Dashicons) در فرانت‌اند
    wp_enqueue_style('dashicons');

    // ۶. اسکریپت اصلی پنل
    wp_enqueue_script(
        'neo-panel-script',
        NEO_PANEL_URL . 'assets/js/app.js',
        array('jquery'),
        NEO_PANEL_VERSION,
        true
    );

    // ۷. اسکریپت کیف پول
    wp_enqueue_script(
        'neo-wallet-js',
        NEO_PANEL_URL . 'assets/js/wallet.js',
        array('neo-panel-script'),
        '1.0.0',
        true
    );

    // ۸. اسکریپت ماژول تیکت
    wp_enqueue_script(
        'neo-ticket-script',
        NEO_PANEL_URL . 'assets/js/ticket.js',
        array('neo-panel-script'),
        NEO_PANEL_VERSION,
        true
    );

    // ۹. اسکریپت عمومی فروشندگان
    wp_enqueue_script(
        'neo-vendor-js',
        NEO_PANEL_URL . 'assets/js/vendor.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // ۱۰. اسکریپت اختصاصی دکمه‌های ویرایش، حذف و ذخیره محصولات فروشنده (جدید)
    wp_enqueue_script(
        'neo-vendor-products-js',
        NEO_PANEL_URL . 'assets/js/vendor-products.js',
        array('jquery', 'neo-vendor-js'),
        '1.0.0',
        true
    );

    // ارسال متغیرهای اختصاصی AJAX برای اسکریپت محصولات فروشنده
    wp_localize_script('neo-vendor-products-js', 'neo_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('neo_vendor_product_nonce')
    ));

    // ارسال متغیرهای AJAX عمومی سیستم فروشندگان
    wp_localize_script('neo-vendor-js', 'neo_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('neo_panel_secure_nonce')
    ));
}

/**
 * بارگذاری اسکریپت‌های مورد نیاز در پنل ادمین وردپرس
 */
add_action('admin_enqueue_scripts', 'neo_admin_enqueue_assets');

function neo_admin_enqueue_assets($hook)
{
    // فقط در صفحه‌ی مدیریت فروشندگان اسکریپت را لود کن تا تداخلی با بقیه ادمین نداشته باشد
    if (strpos($hook, 'neo-vendor-manager') === false) {
        return;
    }

    // تصحیح متغیر آدرس ادمین
    wp_enqueue_style(
        'neo-vendor-admin-css',
        NEO_PANEL_URL . 'assets/css/vendor-admin.css',
        array(),
        '1.0.0'
    );

    // لود کردن اسکریپت مخصوص ادمین
    wp_enqueue_script(
        'neo-vendor-admin-js',
        NEO_PANEL_URL . 'assets/js/vendor-admin.js',
        array('jquery'),
        NEO_PANEL_VERSION,
        true
    );

    // ارسال متغیرها برای ادمین
    wp_localize_script('neo-vendor-admin-js', 'neo_admin_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('neo_admin_secure_nonce')
    ));
}

add_filter('body_class', 'neo_panel_body_class');
function neo_panel_body_class($classes)
{
    if (is_page() && has_shortcode(get_post()->post_content, 'neo_user_panel')) {
        $classes[] = 'neo-panel-page';
    }
    return $classes;
}

add_shortcode('neo_user_panel', 'neo_render_panel_layout');
    