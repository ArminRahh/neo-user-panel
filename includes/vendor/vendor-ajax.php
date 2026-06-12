<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_neo_register_vendor', 'neo_register_vendor_handler');
function neo_register_vendor_handler()
{
    if (
        !isset($_POST['neo_vendor_security']) ||
        !wp_verify_nonce($_POST['neo_vendor_security'], 'neo_vendor_nonce')
    ) {
        wp_send_json_error(['message' => 'درخواست نامعتبر است.']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'لطفا ابتدا وارد حساب کاربری شوید.']);
    }

    $user_id = get_current_user_id();

    $store_name    = sanitize_text_field($_POST['store_name'] ?? '');
    $store_url     = esc_url_raw($_POST['store_url'] ?? '');
    $national_code = sanitize_text_field($_POST['national_code'] ?? '');
    $iban          = sanitize_text_field($_POST['iban'] ?? '');
    $address       = sanitize_textarea_field($_POST['address'] ?? '');

    if (!$store_name || !$national_code || !$iban || !$address) {
        wp_send_json_error(['message' => 'لطفاً تمامی فیلدهای ضروری را پر کنید.']);
    }

    if (!preg_match('/^\d{10}$/', $national_code)) {
        wp_send_json_error(['message' => 'کد ملی معتبر نیست.']);
    }

    if (!preg_match('/^\d{24}$/', $iban)) {
        wp_send_json_error(['message' => 'شماره شبا معتبر نیست.']);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'neo_vendors';

    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, status FROM $table WHERE user_id = %d",
        $user_id
    ));

    if ($existing) {
        if ($existing->status === 'active') {
            wp_send_json_error(['message' => 'حساب فروشندگی شما قبلاً فعال شده است.']);
        }
        wp_send_json_error(['message' => 'شما قبلاً درخواست ثبت کرده‌اید.']);
    }

    // ✅ آپلود فایل
    if (empty($_FILES['documents']['name'])) {
        wp_send_json_error(['message' => 'آپلود مدارک الزامی است.']);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $file = $_FILES['documents'];

    if ($file['size'] > 2 * 1024 * 1024) {
        wp_send_json_error(['message' => 'حجم فایل نباید بیشتر از ۲ مگابایت باشد.']);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];

    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error(['message' => 'فرمت فایل مجاز نیست.']);
    }

    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (isset($upload['error'])) {
        wp_send_json_error(['message' => $upload['error']]);
    }

    $inserted = $wpdb->insert(
        $table,
        [
            'user_id'       => $user_id,
            'store_name'    => $store_name,
            'store_slug'    => sanitize_title($store_url ?: $store_name),
            'national_code' => $national_code,
            'shaba_number'  => $iban,
            'address'       => $address,
            'documents'     => $upload['url'],
            'status'        => 'pending',
            'created_at'    => current_time('mysql'),
        ],
        ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );

    if ($inserted) {
        wp_send_json_success(['message' => 'درخواست شما ثبت شد و در انتظار تایید مدیریت است.']);
    }

    wp_send_json_error(['message' => 'خطا در ثبت درخواست: ' . $wpdb->last_error]);
}

/**
 * پردازش ایجکس تغییر وضعیت فروشنده توسط مدیر
 */
add_action('wp_ajax_neo_update_vendor_status', 'neo_update_vendor_status_callback');

function neo_update_vendor_status_callback()
{
    // ۱. بررسی امنیت و نشست ادمین
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'شما دسترسی کافی برای این عملیات را ندارید.']);
    }

    // ۲. بررسی پارامترهای ارسالی
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $status  = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    if (! $user_id || ! in_array($status, ['approved', 'rejected'])) {
        wp_send_json_error(['message' => 'اطلاعات ارسالی نامعتبر است.']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'neo_vendors';

    // ۳. بروزرسانی وضعیت در دیتابیس
    $updated = $wpdb->update(
        $table_name,
        ['status' => $status],
        ['user_id' => $user_id],
        ['%s'],
        ['%d']
    );

    if ($updated !== false) {
        // ایجاد استایل جدید وضعیت برای برگشت به کلاینت جهت بروزرسانی لحظه‌ای
        $status_html = '';
        if ($status === 'approved') {
            $status_html = '<span class="badge" style="background:#ecfdf5; color:#059669; padding: 4px 8px; border-radius: 4px; border:1px solid #a7f3d0; font-weight:bold;">تایید شده</span>';

            // در صورت تایید، نقش کاربر را در صورت نیاز می‌توان در اینجا به فروشنده (vendor) تغییر داد:
            // $user = new WP_User( $user_id );
            // $user->set_role( 'vendor' ); 

        } elseif ($status === 'rejected') {
            $status_html = '<span class="badge" style="background:#fef2f2; color:#dc2626; padding: 4px 8px; border-radius: 4px; border:1px solid #fecaca; font-weight:bold;">رد شده</span>';
        }

        wp_send_json_success([
            'message'     => 'وضعیت با موفقیت بروزرسانی شد.',
            'status_html' => $status_html
        ]);
    } else {
        wp_send_json_error(['message' => 'خطایی در ثبت اطلاعات در دیتابیس رخ داد.']);
    }
}


add_action('wp_ajax_neo_vendor_add_product', 'neo_vendor_add_product_handler');
function neo_vendor_add_product_handler()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'شما وارد سایت نشده‌اید.']);
    }

    check_ajax_referer('neo_panel_secure_nonce', 'nonce');

    $user_id    = get_current_user_id();
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(['message' => 'آیدی محصول نامعتبر است.']);
    }

    global $wpdb;

    // 1. پیدا کردن آیدی فروشنده از جدول اصلی فروشندگان
    $table_vendors = $wpdb->prefix . 'neo_vendors';
    $vendor_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_vendors WHERE user_id = %d",
        $user_id
    ));

    // // 2. بررسی دسترسی: اگر فروشنده نبود و ادمین هم نبود، اجازه نده
    // if (!$vendor_id && !current_user_can('manage_options')) {
    //     wp_send_json_error(['message' => 'حساب کاربری شما به عنوان فروشنده یافت نشد.']);
    // }

    // // 3. اگر ادمین است ولی در جدول فروشندگان نیست، برای تست یک رکورد برایش فرض کن یا خطا بده
    // if (!$vendor_id && current_user_can('manage_options')) {
    //     wp_send_json_error(['message' => 'آرمین عزیز، شما ادمین هستید اما در جدول neo_vendors ثبت نشده‌اید. ابتدا باید به عنوان فروشنده تایید شوید.']);
    // }

    $table_products = $wpdb->prefix . 'neo_vendor_products';

    // 4. بررسی تکراری نبودن
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_products WHERE vendor_id = %d AND product_id = %d",
        $vendor_id,
        $product_id
    ));

    if ($exists) {
        wp_send_json_error(['message' => 'این محصول قبلاً در لیست شما وجود دارد.']);
    }

    // 5. درج در دیتابیس
    $inserted = $wpdb->insert(
        $table_products,
        [
            'vendor_id'    => $vendor_id,
            'product_id'   => $product_id,
            'vendor_price' => 0,
            'vendor_stock' => 0,
            'status'       => 'active',
        ],
        ['%d', '%d', '%f', '%d', '%s']
    );

    if ($inserted) {
        wp_send_json_success(['message' => 'محصول با موفقیت اضافه شد.']);
    } else {
        // برای دیباگ بهتر، خطای دیتابیس را اینجا می‌نویسیم
        wp_send_json_error(['message' => 'خطا در ذخیره‌سازی: ' . $wpdb->last_error]);
    }
}


function neo_render_compact_pagination($current_page, $total_pages)
{
    if ($total_pages <= 1) {
        return '';
    }

    $current_page = max(1, (int) $current_page);
    $total_pages   = max(1, (int) $total_pages);

    $html = '<div class="neo-pagination">';

    // دکمه قبلی
    if ($current_page > 1) {
        $html .= '<a href="#" class="neo-page-nav neo-page-prev" data-page="' . esc_attr($current_page - 1) . '">قبلی</a>';
    }

    // محاسبه بازه 5تایی
    $window_size = 2;
    $half_window = floor($window_size / 2);

    $start = $current_page - $half_window;
    $end   = $current_page + $half_window;

    if ($start < 1) {
        $end += (1 - $start);
        $start = 1;
    }

    if ($end > $total_pages) {
        $start -= ($end - $total_pages);
        $end = $total_pages;
    }

    if ($start < 1) {
        $start = 1;
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="current">' . esc_html($i) . '</span>';
        } else {
            $html .= '<a href="#" data-page="' . esc_attr($i) . '">' . esc_html($i) . '</a>';
        }
    }

    // دکمه بعدی
    if ($current_page < $total_pages) {
        $html .= '<a href="#" class="neo-page-nav neo-page-next" data-page="' . esc_attr($current_page + 1) . '">بعدی</a>';
    }

    $html .= '</div>';

    return $html;
}

add_action('wp_ajax_neo_vendor_search_products', 'neo_vendor_search_products_handler');
function neo_vendor_search_products_handler()
{
    check_ajax_referer('neo_panel_secure_nonce', 'nonce');

    $paged       = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $keyword     = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 10, // تعداد در هر صفحه
        'paged'          => $paged,
        'post_status'    => 'publish',
    ];

    if ($category_id > 0) {
        $args['tax_query'] = [['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $category_id]];
    }
    if (!empty($keyword)) {
        $args['s'] = $keyword;
    }

    $query = new WP_Query($args);
    $total_pages = $query->max_num_pages;

    ob_start();
    if ($query->have_posts()) {
        // ... (همان حلقه نمایش tr که در پیام قبلی دادم) ...
        while ($query->have_posts()) {
            $query->the_post();
            $pid = get_the_ID();
            $product = wc_get_product($pid);
            // رندر کردن ردیف‌های جدول (مشابه کد قبلی)
?>
            <tr>
                <td><?php echo get_the_post_thumbnail($pid, [50, 50]) ?: '—'; ?></td>
                <td><?php the_title(); ?></td>
                <td><?php echo $product ? $product->get_sku() : '—'; ?></td>
                <td><?php echo strip_tags(wc_get_product_category_list($pid)); ?></td>
                <td><button class="add-product-btn" data-product-id="<?php echo $pid; ?>">افزودن</button></td>
            </tr>
<?php
        }
    } else {
        echo '<tr><td colspan="5" style="text-align:center;">محصولی یافت نشد.</td></tr>';
    }
    $html = ob_get_clean();

    $pagination = '';

    if ($query->max_num_pages > 1) {
        $pagination .= '<div class="neo-pagination">';

        for ($i = 1; $i <= $query->max_num_pages; $i++) {
            if ($i == $paged) {
                $pagination .= '<span class="current">' . $i . '</span>';
            } else {
                $pagination .= '<a href="#" data-page="' . esc_attr($i) . '">' . $i . '</a>';
            }
        }

        $pagination .= '</div>';
    }
    $pagination = neo_render_compact_pagination($paged, $query->max_num_pages);


    wp_send_json_success([
        'html'        => $html,
        'total_pages' => $total_pages,
        'current_page' => $paged,
        'pagination' => $pagination,
    ]);
}
/**
 * AJAX Handler: ذخیره تغییرات قیمت و موجودی محصول فروشنده
 */
add_action('wp_ajax_neo_save_vendor_product_changes', 'neo_save_vendor_product_changes_handler');
function neo_save_vendor_product_changes_handler()
{

    global $wpdb;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    // ۱. دریافت قیمت به صورت رشته، حذف کاماها و سپس تبدیل به عدد اعشاری
    $raw_price = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '0';
    $clean_price = str_replace(',', '', $raw_price);
    $price = floatval($clean_price);
    $stock_status = isset($_POST['stock']) ? intval($_POST['stock']) : 0; // 1 برای موجود، 0 برای ناموجود

    if (!$item_id) {
        wp_send_json_error('شناسه مورد معتبر نیست.');
    }

    $table_products = $wpdb->prefix . 'neo_vendor_products';
    // منطق جدید: تعیین وضعیت فعال/غیرفعال بر اساس قیمت
    $status = ($price > 0) ? 'active' : 'inactive';

    $updated = $wpdb->update(
        $table_products,
        array(
            'vendor_price' => $price,
            'vendor_stock' => ($stock_status === 1) ? 10 : 0,
            'status'       => $status
        ),
        array('id' => $item_id),
        array('%f', '%d', '%s'),
        array('%d')
    );

    if ($updated !== false) {
        wp_send_json_success(array(
            'message' => 'تغییرات با موفقیت ذخیره شد.',
            'status'  => $status
        ));
    } else {
        wp_send_json_error('ذخیره تغییرات در پایگاه داده با خطا مواجه شد.');
    }
}

/**
 * AJAX Handler: حذف محصول از لیست فروشنده
 */
add_action('wp_ajax_neo_delete_vendor_product', 'neo_delete_vendor_product_handler');
function neo_delete_vendor_product_handler()
{
    global $wpdb;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

    if (!$item_id) {
        wp_send_json_error('شناسه نامعتبر است.');
    }

    $table_products = $wpdb->prefix . 'neo_vendor_products';

    // عملیات حذف فیزیکی رکورد محصول فروشنده
    $deleted = $wpdb->delete($table_products, array('id' => $item_id), array('%d'));

    if ($deleted) {
        wp_send_json_success('محصول با موفقیت حذف شد.');
    } else {
        wp_send_json_error('امکان حذف محصول در دیتابیس وجود نداشت.');
    }
}
