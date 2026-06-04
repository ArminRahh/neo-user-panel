<?php
// جلوگیری از دسترسی مستقیم
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. ساخت جدول دیتابیس برای تراکنش‌ها
 */
add_action( 'admin_init', 'neo_wallet_create_tables' );
function neo_wallet_create_tables() {
    if ( get_option( 'neo_wallet_db_version' ) != '1.0' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'neo_wallet_transactions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            amount decimal(15,2) NOT NULL,
            type varchar(10) NOT NULL,
            description text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( 'neo_wallet_db_version', '1.0' );
    }
}

/**
 * 2. دریافت موجودی فعلی کاربر
 */
function neo_get_wallet_balance( $user_id ) {
    $balance = get_user_meta( $user_id, 'neo_wallet_balance', true );
    return $balance ? floatval( $balance ) : 0;
}

/**
 * 3. ثبت تراکنش جدید و آپدیت خودکار موجودی
 */
function neo_wallet_transaction( $user_id, $amount, $type, $description ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'neo_wallet_transactions';
    
    // ثبت گزارش تراکنش
    $wpdb->insert( $table_name, [
        'user_id'     => $user_id,
        'amount'      => $amount,
        'type'        => $type,
        'description' => $description,
        'created_at'  => current_time( 'mysql' )
    ]);

    // محاسبه و ذخیره موجودی جدید
    $current_balance = neo_get_wallet_balance( $user_id );
    $new_balance = ( $type === 'credit' ) ? ( $current_balance + $amount ) : ( $current_balance - $amount );
    
    update_user_meta( $user_id, 'neo_wallet_balance', $new_balance );
    
    return $new_balance;
}
// ریدایرکت خودکار به پنل کاربری بعد از پرداخت موفق شارژ کیف پول
add_action( 'template_redirect', 'neo_wallet_redirect_after_checkout' );

function neo_wallet_redirect_after_checkout() {
    // بررسی می‌کنیم که آیا در صفحه "سفارش دریافت شد" (تشکر) ووکامرس هستیم؟
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        global $wp;
        
        $order_id = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : 0;
        
        if ( $order_id ) {
            $order = wc_get_order( $order_id );
            
            // بررسی می‌کنیم که آیا این سفارش مختص شارژ کیف پول افزونه ماست؟
            if ( $order && $order->get_meta( '_is_neo_wallet_recharge' ) === 'yes' ) {
                
                // جستجو برای پیدا کردن آدرس برگه‌ای که شورت‌کد پنل کاربری را دارد
                $panel_url = home_url(); // آدرس پیش‌فرض در صورت پیدا نشدن برگه
                $pages = get_pages();
                foreach ( $pages as $page ) {
                    if ( has_shortcode( $page->post_content, 'neo_user_panel' ) ) {
                        $panel_url = get_permalink( $page->ID );
                        break;
                    }
                }
                
                // اضافه کردن یک پارامتر به URL برای تشخیص اینکه از درگاه برگشته است
                $redirect_url = add_query_arg( 'recharge', 'success', $panel_url );
                
                // ریدایرکت مستقیم به پنل کاربری
                wp_redirect( $redirect_url );
                exit;
            }
        }
    }
}
