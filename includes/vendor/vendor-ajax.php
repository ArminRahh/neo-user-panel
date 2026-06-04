<?php
// مسیر: neo-user-panel/includes/vendor/vendor-ajax.php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_ajax_neo_register_vendor', 'neo_register_vendor_handler');

function neo_register_vendor_handler() {
    // 1. بررسی امنیت (Nonce)
    if (!isset($_POST['neo_vendor_security']) || !wp_verify_nonce($_POST['neo_vendor_security'], 'neo_vendor_nonce')) {
        wp_send_json_error(['message' => 'درخواست نامعتبر است.']);
    }

    // 2. بررسی لاگین بودن کاربر
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'لطفا ابتدا وارد حساب کاربری خود شوید.']);
    }

    // 3. دریافت و پاکسازی داده‌ها (Sanitization)
    $store_name    = sanitize_text_field($_POST['store_name'] ?? '');
    $store_url     = sanitize_text_field($_POST['store_url'] ?? '');
    $national_code = sanitize_text_field($_POST['national_code'] ?? '');
    $iban          = sanitize_text_field($_POST['iban'] ?? '');

    // بررسی فیلدهای ضروری
    if (empty($store_name) || empty($national_code) || empty($iban)) {
        wp_send_json_error(['message' => 'لطفاً تمامی فیلدهای ضروری را پر کنید.']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'neo_vendors';

    // 4. بررسی اینکه آیا کاربر قبلاً درخواست داده است یا خیر
    $existing_request = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d", 
        $user_id
    ));

    if ($existing_request) {
        wp_send_json_error(['message' => 'شما قبلاً درخواست فروشندگی ثبت کرده‌اید.']);
    }

    // 5. ذخیره اطلاعات در دیتابیس
    $inserted = $wpdb->insert(
        $table_name,
        [
            'user_id'       => $user_id,
            'store_name'    => $store_name,
            'store_slug'    => sanitize_title($store_url ?: $store_name), // ساخت نامک از آدرس یا نام
            'national_code' => $national_code,
            'shaba_number'  => $iban,
            'status'        => 'pending',
            'created_at'    => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s', '%s', '%s', '%s'] // فرمت داده‌ها
    );

    if ($inserted) {
        wp_send_json_success(['message' => 'درخواست شما با موفقیت ثبت شد و در انتظار تایید مدیریت است.']);
    } else {
        wp_send_json_error(['message' => 'خطایی در ثبت درخواست رخ داد. لطفاً مجدداً تلاش کنید.']);
    }
}

add_action('wp_ajax_neo_vendor_add_product', 'neo_vendor_add_product_handler');

function neo_vendor_add_product_handler() {
    // بررسی لاگین بودن کاربر
    if (!is_user_logged_in()) {
        wp_send_json_error('شما وارد سایت نشده‌اید.');
    }

    // در صورت نیاز می‌توانید nonce را هم چک کنید:
    // check_ajax_referer('neo_app_nonce', 'nonce');

    $user_id = get_current_user_id();
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error('آیدی محصول نامعتبر است.');
    }

    global $wpdb;
    
    // ۱. ابتدا باید آیدی فروشنده را از جدول neo_vendors پیدا کنیم
    $table_vendors = $wpdb->prefix . 'neo_vendors';
    $vendor_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_vendors WHERE user_id = %d", $user_id));

    if (!$vendor_id) {
        wp_send_json_error('شما به عنوان فروشنده ثبت نشده‌اید.');
    }

    // ۲. بررسی اینکه آیا این محصول قبلاً برای این فروشنده ثبت شده یا خیر
    $table_products = $wpdb->prefix . 'neo_vendor_products';
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_products WHERE vendor_id = %d AND product_id = %d",
        $vendor_id,
        $product_id
    ));

    if ($exists) {
        wp_send_json_error('این محصول قبلاً در لیست شما وجود دارد.');
    }

    // ۳. افزودن محصول به جدول
    $inserted = $wpdb->insert(
        $table_products,
        [
            'vendor_id'  => $vendor_id,
            'product_id' => $product_id,
            'vendor_price' => 0, // قیمت پیش‌فرض صفر
            'vendor_stock' => 0, // موجودی پیش‌فرض صفر
            'status'     => 'active'
        ],
        ['%d', '%d', '%f', '%d', '%s']
    );

    if ($inserted) {
        wp_send_json_success('محصول با موفقیت اضافه شد.');
    } else {
        wp_send_json_error('خطا در ذخیره‌سازی اطلاعات.');
    }
}

// اضافه کردن اکشن برای جستجوی محصول


add_action('wp_ajax_neo_vendor_search_products', 'neo_vendor_search_products_handler');

function neo_vendor_search_products_handler() {
    
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    if ($category_id > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_id,
            ),
        );
    }

    if (!empty($keyword)) {
        $args['s'] = $keyword;
    }

    $query = new WP_Query($args);
    
    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            $thumbnail = get_the_post_thumbnail(get_the_ID(), array(50, 50));
            $categories = wp_get_post_terms(get_the_ID(), 'product_cat', array('fields' => 'names'));
            
            echo '<tr>';
            echo '<td>' . ($thumbnail ? $thumbnail : '<span>—</span>') . '</td>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . ($product->get_sku() ? $product->get_sku() : '—') . '</td>';
            echo '<td>' . (!empty($categories) ? implode(', ', $categories) : '—') . '</td>';
            echo '<td><a href="' . get_edit_post_link(get_the_ID()) . '">ویرایش</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5" style="text-align:center; padding: 20px;">محصولی یافت نشد.</td></tr>';
    }

    wp_reset_postdata();

    wp_send_json_success(array(
        'html' => ob_get_clean()
    ));
}


