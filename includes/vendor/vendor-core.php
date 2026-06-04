<?php
// مسیر: neo-user-panel/includes/vendor/vendor-core.php
if ( ! defined( 'ABSPATH' ) ) exit;

class Neo_Vendor_Core {
    
    const DB_VERSION = '1.0.1';
    public function __construct() {
        
        // بارگذاری فایل‌های وابسته
        require_once plugin_dir_path(__FILE__) . 'vendor-ajax.php';

        // بررسی و ساخت جداول دیتابیس در زمان اجرای پنل ادمین
        add_action('admin_init', [$this, 'upgrade_database']);
    }

        /**
     * بررسی نسخه فعلی دیتابیس و اجرای ساخت/آپدیت جدول در صورت نیاز
     */
    public function upgrade_database() {
        $installed_version = get_option('neo_vendor_db_version', '0.0.0');

        if (version_compare($installed_version, self::DB_VERSION, '<')) {
            $this->create_tables();
            update_option('neo_vendor_db_version', self::DB_VERSION);
        }
    }

    /**
     * ساخت جدول اختصاصی فروشندگان با استفاده از dbDelta
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $table_name = $wpdb->prefix . 'neo_vendors';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            store_name varchar(255) NOT NULL,
            store_slug varchar(255) NOT NULL,
            national_code varchar(20) DEFAULT '' NOT NULL,
            phone varchar(20) DEFAULT '' NOT NULL,
            shaba_number varchar(50) DEFAULT '' NOT NULL,
            address text NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        $table_name = $wpdb->prefix . 'neo_vendor_products';
        $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        vendor_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        vendor_price decimal(10,2) NOT NULL DEFAULT '0.00',
        vendor_stock int(11) NOT NULL DEFAULT 0,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY vendor_id (vendor_id),
        KEY product_id (product_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * دریافت وضعیت فروشندگی کاربر جاری
     * خروجی: none, pending, approved, rejected
     */
    public static function get_vendor_status($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // اگر کاربری لاگین نبود
        if (!$user_id) {
            return 'none';
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'neo_vendors';

        // خواندن وضعیت از جدول اختصاصی
        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));

        return $status ? $status : 'none';
    }

}

new Neo_Vendor_Core();
