<?php
if (! defined('ABSPATH')) exit;

global $wpdb;
$user_id = get_current_user_id();

// دریافت ID فروشنده
$table_vendors = $wpdb->prefix . 'neo_vendors';
$vendor_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_vendors WHERE user_id = %d", $user_id));

// اگر فروشنده معتبر است، محصولاتش را واکشی می‌کنیم
if ($vendor_id) {
    $table_products = $wpdb->prefix . 'neo_vendor_products';

    // واکشی محصولات فروشنده همراه با عنوان محصول از جدول پست‌ها
    $vendor_products = $wpdb->get_results($wpdb->prepare("
            SELECT vp.*, p.post_title 
            FROM $table_products AS vp
            INNER JOIN {$wpdb->posts} AS p ON vp.product_id = p.ID
            WHERE vp.vendor_id = %d AND p.post_status = 'publish'
            ORDER BY vp.created_at DESC
        ", $vendor_id));
} else {
    $vendor_products = [];
}

// برای اطمینان از لود شدن استایل آیکون‌های وردپرس در فرانت‌اند
wp_enqueue_style('dashicons');
?>

<div class="neo-vendor-products-container">
    <div class="neo-tab-header">
        <div class="neo-header-main">
            <h2 class="neo-tab-title">لیست محصولات شما</h2>
        </div>

        <!-- فیلترهای سریع -->
        <div class="neo-filter-bar">
            <div class="neo-search-box">
                <input type="text" id="neo-product-search" placeholder="جستجوی نام محصول...">
            </div>
        </div>
    </div>

    <div class="neo-table-wrapper">
        <table class="neo-vendor-table">
            <thead>
                <tr>
                    <th style="width: 80px; text-align: center;">تصویر</th>
                    <th>نام محصول</th>
                    <th style="width: 160px;">قیمت (تومان)</th>
                    <th style="width: 140px;">موجودی</th>
                    <th style="width: 100px; text-align: center;">وضعیت</th>
                    <th style="width: 150px; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vendor_products)) : ?>
                    <?php foreach ($vendor_products as $item) :
                        $product = wc_get_product($item->product_id);
                        if (!$product) continue;

                        $image_id = $product->get_image_id();
                        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        $image_url = $image_url ? $image_url : wc_placeholder_img_src();
                        $product_link = get_permalink($item->product_id);
                    ?>

                        <tr class="neo-product-row" data-row-id="<?php echo esc_attr($item->id); ?>">
                            <!-- تصویر -->
                            <td class="col-img" style="text-align: center; vertical-align: middle;">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($item->post_title); ?>" class="neo-p-thumb">
                            </td>

                            <!-- نام محصول -->
                            <td class="col-name" style="vertical-align: middle;">
                                <strong>
                                    <a href="<?php echo esc_url($product_link); ?>" target="_blank" class="neo-product-link">
                                        <?php echo esc_html($item->post_title); ?>
                                    </a>
                                </strong>
                            </td>

                            <!-- فیلد قیمت -->
                            <td class="col-price" style="vertical-align: middle;">
                                <div class="neo-input-wrapper">
                                    <?php
                                    $clean_price = !empty($item->vendor_price) ? intval($item->vendor_price) : '';
                                    // فرمت اولیه قیمت هنگام لود صفحه
                                    $formatted_price = !empty($clean_price) ? number_format($clean_price) : '';
                                    ?>
                                    <input type="text"
                                        inputmode="numeric"
                                        pattern="[0-9,]*"
                                        class="neo-vendor-price-input neo-price-format"
                                        data-item-id="<?php echo esc_attr($item->id); ?>"
                                        value="<?php echo esc_attr($formatted_price); ?>"
                                        placeholder="مثال: ۴۵,۰۰۰"
                                        style="text-align: center;">
                                </div>
                            </td>

                            <!-- سلکت باکس موجودی جدید -->
                            <td class="col-stock" style="vertical-align: middle;">
                                <select class="neo-vendor-stock-select" data-item-id="<?php echo esc_attr($item->id); ?>">
                                    <option value="1" <?php selected($item->vendor_stock > 0); ?>>موجود</option>
                                    <option value="0" <?php selected($item->vendor_stock == 0); ?>>ناموجود</option>
                                </select>
                            </td>

                            <!-- وضعیت تایید مدیریت -->
                            <td class="col-status" style="text-align: center; vertical-align: middle;">
                                <?php if ($item->status == 'active' && floatval($item->vendor_price) > 0): ?>
                                    <span class="neo-badge active-badge">فعال</span>
                                <?php else: ?>
                                    <span class="neo-badge inactive-badge" title="برای فعال‌سازی ابتدا قیمت معتبری وارد کنید">غیرفعال</span>
                                <?php endif; ?>
                            </td>

                            <!-- دکمه‌های عملیات هماهنگ شده با داش‌آیکون -->
                            <td class="col-actions" style="text-align: center; vertical-align: middle;">
                                <div class="action-flex">
                                    <a href="<?php echo esc_url($product_link); ?>" target="_blank" class="neo-action-btn view-btn" title="مشاهده در سایت">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </a>
                                    <button type="button" class="neo-action-btn save-btn neo-save-product-ajax" title="ذخیره تغییرات قیمت و موجودی" data-item-id="<?php echo esc_attr($item->id); ?>">
                                        <span class="dashicons dashicons-saved"></span>
                                    </button>
                                    <button type="button" class="neo-action-btn delete-btn neo-delete-product-ajax" title="حذف از لیست من" data-item-id="<?php echo esc_attr($item->id); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="neo-empty-cell">هیچ محصولی در لیست شما یافت نشد. برای شروع، یک محصول اضافه کنید.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>