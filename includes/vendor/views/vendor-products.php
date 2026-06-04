<?php
    if ( ! defined( 'ABSPATH' ) ) exit;

    // درون فایل vendor-products.php
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
?>

<div class="neo-vendor-products-container">
    <div class="neo-tab-header">
        <div class="neo-header-main">
            <h2 class="neo-tab-title">لیست محصولات شما</h2>
        </div>
        
        <!-- فیلترهای سریع مشابه ترب -->
        <div class="neo-filter-bar">
            <div class="neo-search-box">
                <input type="text" id="neo-product-search" placeholder="جستجوی نام محصول یا SKU...">
            </div>
            <div class="neo-filter-options">
                <select id="neo-filter-status">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="publish">منتشر شده</option>
                    <option value="pending">در انتظار تایید</option>
                    <option value="outofstock">ناموجود</option>
                </select>
            </div>
        </div>
    </div>

    <div class="neo-table-wrapper">
        <table class="neo-vendor-table">
            <thead>
                <tr>
                    <th>تصویر</th>
                    <th>نام محصول</th>
                    <th>قیمت (تومان)</th>
                    <th>موجودی</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vendor_products)) : ?>
                <?php foreach ($vendor_products as $item) : 
                    $product = wc_get_product($item->product_id);
                    $image_url = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                    $image_url = $image_url ? $image_url : wc_placeholder_img_src();
                ?>
                
            
                <tr class="neo-product-row">
                    <td class="col-img"><?php echo esc_url($image_url); ?></td>
                    <td class="col-name"><strong><a href="<?php echo esc_html($item->post_title); ?>" target="_blank"></a></strong></td>
                    <td class="col-price">
                        <input type="number" class="neo-vendor-price-input" data-item-id="<?php echo esc_attr($item->id); ?>" value="<?php echo esc_attr($item->vendor_price); ?>" placeholder="0">
                    </td>
                    <td class="col-stock">
                        <input type="number" class="neo-vendor-stock-input" data-item-id="<?php echo esc_attr($item->id); ?>" value="<?php echo esc_attr($item->vendor_stock); ?>" placeholder="0">
                    </td>
                    <td class="col-status">
                        <?php if($item->status == 'active'): ?>
                                <span class="neo-status-badge active">فعال</span>
                            <?php else: ?>
                                <span class="neo-status-badge inactive">غیرفعال</span>
                            <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <div class="action-flex">
                            <button class="neo-btn-icon view" title="مشاهده در سایت"><i class="dashicons dashicons-visibility"></i></button>
                            <button class="neo-btn-icon edit" title="ویرایش کامل" data-item-id="<?php echo esc_attr($item->id); ?>">
                                <i class="dashicons dashicons-edit-page"></i>
                            </button>
                            <button class="neo-btn-icon delete" title="حذف" data-item-id="<?php echo esc_attr($item->id); ?>">
                                <i class="dashicons dashicons-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px;">هیچ محصولی در لیست شما یافت نشد. برای شروع، یک محصول اضافه کنید.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
