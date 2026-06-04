<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// ثبت منوی تنظیمات در پیشخوان وردپرس
add_action('admin_menu', 'neo_register_admin_menu');
require_once plugin_dir_path(__FILE__) . 'vendor-manager.php';
function neo_register_admin_menu() {
    // منوی اصلی
    add_menu_page(
        'تنظیمات پنل نئو', // عنوان صفحه
        'پنل کاربری نئو',   // عنوان منو
        'manage_options',  // سطح دسترسی
        'neo-settings',    // اسلاگ منو
        'neo_settings_page_html', // تابع نمایش محتوا
        'dashicons-admin-users',  // آیکون
        50                 // موقعیت
    );

    // زیرمنوی مدیریت تیکت‌ها
    add_submenu_page(
        'neo-settings',
        'مدیریت تیکت‌ها',
        'تیکت‌ها',
        'manage_options',
        'neo-tickets',
        'neo_ticket_manager_page_html'
    );
    // زیرمنوی مدیریت فروشندگان (این بخش باید اضافه شود)
    add_submenu_page(
        'neo-settings',             // اسلاگ منوی والد (منوی اصلی پنل)
        'مدیریت فروشندگان',         // عنوان صفحه (Title)
        'فروشندگان',                // عنوان منو در سایدبار
        'manage_options',           // سطح دسترسی (ادمین)
        'neo-vendor-manager',       // اسلاگ اختصاصی این صفحه
        'neo_vendor_manager_page_html' // نام تابعی که در vendor-manager.php قرار دارد
    );
}

// کالبد صفحه تنظیمات
function neo_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>تنظیمات پنل کاربری نئو</h1>
        <form method="post" action="options.php">
            <!-- فیلدهای تنظیمات در آینده اینجا قرار می‌گیرند -->
            <p>این بخش برای تنظیمات کلی پنل (مثل دپارتمان‌ها، پیام‌ها و...) استفاده خواهد شد.</p>
        </form>
    </div>
    <?php
}
