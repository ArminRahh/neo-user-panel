<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Neo_Ticket_Core {

    /**
     * راه‌اندازی ماژول تیکت
     */
    public static function init() {
        // اجرای بررسی دیتابیس فقط در پیشخوان وردپرس
        add_action('admin_init', [__CLASS__, 'create_tables']);
        
        // هوک لود کردن استایل و اسکریپت اختصاصی تیکت
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_ticket_assets']);
    }
	public static function enqueue_ticket_assets() {
        wp_enqueue_style( 'neo-ticket-style', NEO_PANEL_URL . 'assets/css/ticket.css', array(), NEO_PANEL_VERSION );
        wp_enqueue_script( 'neo-ticket-script', NEO_PANEL_URL . 'assets/js/ticket.js', array('jquery'), NEO_PANEL_VERSION, true );
    }
    /**
     * بررسی اینکه آیا جداول قبلا نصب شده‌اند یا خیر
     */
    public static function check_installation() {
        // از ورژن‌گذاری استفاده می‌کنیم تا در آینده اگر جدولی آپدیت شد، بتوانیم راحت تغییرات را اعمال کنیم
        $db_version = '1.0.0';
        
        if ( get_option( 'neo_ticket_db_version' ) !== $db_version ) {
            self::create_tables();
            update_option( 'neo_ticket_db_version', $db_version );
        }
    }

    /**
     * ساخت جداول دیتابیس با استفاده از dbDelta
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_tickets = $wpdb->prefix . 'neo_tickets';
        $table_replies = $wpdb->prefix . 'neo_ticket_replies';

        // دقت کنید: فاصله مضاعف بعد از PRIMARY KEY برای dbDelta الزامی است
        $sql_tickets = "CREATE TABLE $table_tickets (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            department varchar(100) NOT NULL,
            priority varchar(50) DEFAULT 'normal' NOT NULL,
            status varchar(50) DEFAULT 'open' NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql_replies = "CREATE TABLE $table_replies (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            message longtext NOT NULL,
            attachment_url varchar(255) DEFAULT '' NOT NULL,
            is_admin tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY ticket_id (ticket_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        // اجرای کوئری‌ها
        dbDelta( $sql_tickets );
        dbDelta( $sql_replies );
    }
}
