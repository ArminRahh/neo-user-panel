<?php

if (! defined('ABSPATH')) exit;

function neo_vendor_manager_page_html()
{
    if (! current_user_can('manage_options')) return;

    global $wpdb;
    $table_name = $wpdb->prefix . 'neo_vendors';

    // بررسی اینکه آیا کاربر خاصی برای مشاهده انتخاب شده است؟
    $view_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    echo '<div class="wrap" style="font-family: tahoma, arial;">';

    if ($view_user_id > 0) {
        // --- بخش نمایش جزئیات یک فروشنده خاص ---
        $vendor = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE user_id = %d", $view_user_id));

        if (! $vendor) {
            echo '<div class="notice notice-error"><p>فروشنده‌ای با این مشخصات یافت نشد.</p></div>';
            echo '<p><a href="?page=neo-vendor-manager" class="button">بازگشت به لیست</a></p>';
            return;
        }

        echo '<h1 class="wp-heading-inline">بررسی درخواست: ' . esc_html($vendor->store_name) . '</h1>';
        echo '<a href="?page=neo-vendor-manager" class="page-title-action">بازگشت به لیست</a>';
        echo '<hr class="wp-header-end">';

?>
        <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <table class="form-table">
                <tr>
                    <th scope="row">نام فروشگاه</th>
                    <td><strong><?php echo esc_html($vendor->store_name); ?></strong></td>
                </tr>
                <tr>
                    <th scope="row">کد ملی</th>
                    <td><code><?php echo esc_html($vendor->national_code); ?></code></td>
                </tr>
                <tr>
                    <th scope="row">شماره شبا</th>
                    <td style="direction: ltr; text-align: right;">IR-<?php echo esc_html($vendor->shaba_number); ?></td>
                </tr>
                <tr>
                    <th scope="row">آدرس</th>
                    <td><?php echo nl2br(esc_html($vendor->address)); ?></td>
                </tr>
                <tr>
                    <th scope="row">مدارک ارسالی</th>
                    <td>
                        <?php
                        $doc_url = $vendor->documents;
                        $ext = pathinfo($doc_url, PATHINFO_EXTENSION);
                        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                            echo '<a href="' . esc_url($doc_url) . '" target="_blank"><img src="' . esc_url($doc_url) . '" style="max-width: 100%; border: 1px solid #ddd; border-radius: 5px;" /></a>';
                        } else {
                            echo '<a href="' . esc_url($doc_url) . '" target="_blank" class="button">دانلود/مشاهده فایل مدارک</a>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">وضعیت فعلی</th>
                    <td class="vendor-status-cell">
                        <?php
                        if ($vendor->status == 'pending') echo '<span style="color:#d97706; font-weight:bold;">در انتظار تایید</span>';
                        elseif ($vendor->status == 'approved') echo '<span style="color:#059669; font-weight:bold;">تایید شده</span>';
                        elseif ($vendor->status == 'rejected') echo '<span style="color:#dc2626; font-weight:bold;">رد شده</span>';
                        ?>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <input type="hidden" id="m-userid" value="<?php echo $view_user_id; ?>">
                <button type="button" class="button button-large neo-action-btn" data-action="approve" style="color: #059669; border-color: #059669;">تایید و فعال‌سازی </button>
                <button type="button" class="button button-large neo-action-btn" data-action="reject" style="color: #ef4444; border-color: #dc2626; margin-right: 10px;">رد درخواست</button>
                <span id="ajax-loader" style="display:none; margin-right: 15px;">در حال ثبت...</span>
            </div>
            <div id="m-ajax-feedback" style="margin-top: 15px; font-weight: bold;"></div>
        </div>
    <?php

    } else {

        // ۱. پردازش فیلتر وضعیت
        $status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : '';
        $where_clause = "";
        $query_args = array();

        if (!empty($status_filter)) {
            $where_clause = " WHERE status = %s ";
            $query_args[] = $status_filter;
        }

        // ۲. تنظیمات صفحه‌بندی
        $per_page = 10;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        // ۳. شمارش کل رکوردها (با احتساب فیلتر)
        $total_query = "SELECT COUNT(id) FROM {$table_name} {$where_clause}";
        if (!empty($where_clause)) {
            $total_items = $wpdb->get_var($wpdb->prepare($total_query, $query_args));
        } else {
            $total_items = $wpdb->get_var($total_query);
        }
        $total_pages = ceil($total_items / $per_page);

        // ۴. دریافت کوئری اصلی محدود شده
        $data_query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $final_args = $query_args;
        $final_args[] = $per_page;
        $final_args[] = $offset;

        $vendors = $wpdb->get_results($wpdb->prepare($data_query, $final_args));
    ?>
        <h1 class="wp-heading-inline">مدیریت درخواست‌های فروشندگی</h1>
        <hr class="wp-header-end">

        <!-- بخش فیلتر وضعیت به سبک وردپرس -->
        <div class="tablenav top" style="margin-top: 20px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <form method="get" action="" style="display: flex; align-items: center; gap: 5px;">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
                <select name="status_filter" style="height: 30px;">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>>در انتظار تایید</option>
                    <option value="approved" <?php selected($status_filter, 'approved'); ?>>تایید شده</option>
                    <option value="rejected" <?php selected($status_filter, 'rejected'); ?>>رد شده</option>
                </select>
                <input type="submit" class="button action" value="فیلتر">
                <?php if (!empty($status_filter)) : ?>
                    <a href="?page=<?php echo esc_attr($_GET['page']); ?>" class="button">پاکسازی فیلتر</a>
                <?php endif; ?>
            </form>

            <div class="tablenav-pages">
                <span class="displaying-num" style="font-weight: bold;"><?php echo $total_items; ?> مورد ثبت‌شده</span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>آیدی</th>
                    <th>نام فروشگاه</th>
                    <th>کد ملی</th>
                    <th>وضعیت</th>
                    <th>تاریخ ثبت‌نام</th>
                    <th style="width: 100px;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($vendors) : ?>
                    <?php foreach ($vendors as $vendor) : ?>
                        <tr>
                            <td>#<?php echo esc_html($vendor->user_id); ?></td>
                            <td><strong><?php echo esc_html($vendor->store_name); ?></strong></td>
                            <td><code><?php echo esc_html($vendor->national_code); ?></code></td>
                            <td>
                                <?php
                                if ($vendor->status == 'pending') echo 'در انتظار';
                                elseif ($vendor->status == 'approved') echo '<span style="color:#02c702;">تایید شده</span>';
                                elseif ($vendor->status == 'rejected') echo '<span style="color:red;">رد شده</span>';
                                ?>
                            </td>
                            <td><?php echo ($vendor->created_at !== '0000-00-00 00:00:00') ? esc_html(wp_date('Y/m/d', strtotime($vendor->created_at))) : '---'; ?></td>
                            <td>
                                <a href="?page=neo-vendor-manager&user_id=<?php echo $vendor->user_id; ?>" class="button button-small">بررسی و اقدام</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">هیچ درخواستی یافت نشد.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- انتهای جدول و نمایش صفحه‌بندی -->
        <?php if ($total_pages > 1) : ?>
            <div class="tablenav bottom" style="margin-top: 15px; display: flex; justify-content: flex-end;">
                <div class="tablenav-pages">
                    <span class="pagination-links">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo; قبلی'),
                            'next_text' => __('بعدی &raquo;'),
                            'total' => $total_pages,
                            'current' => $current_page,
                            'add_args' => array('status_filter' => $status_filter)
                        ));
                        ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
<?php
    }
    echo '</div>';
}
