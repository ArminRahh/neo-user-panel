<?php

/**
 * Vendor Dashboard View
 * UI: Modern Dashboard Cards & Quick Actions
 */

if (! defined('ABSPATH')) exit; // جلوگیری از دسترسی مستقیم

$user_id = get_current_user_id();
// در اینجا می‌توانید آمارهای واقعی را از دیتابیس کوئری بزنید
$total_sales = 0; // نمونه
$active_products = 0; // نمونه
$pending_orders = 0; // نمونه
// دریافت دسته‌بندی‌های ووکامرس برای دراپ‌داون جستجو
$product_categories = get_terms(array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
));
?>

<div class="neo-content-vendor">
    <div class="neo-dashboard-actions">
        <div class="neo-dashboard-header">
            <h2 class="neo-tab-title">پیشخوان فروشنده</h2>
            <p class="neo-tab-subtitle">خلاصه‌ای از فعالیت‌های فروشگاه شما در یک نگاه</p>
        </div>
    </div>

    <div id="neo-dashboard-vendor">
        <div class="neo-stats-grid">
            <div class="neo-stat-vendor-card card-blue">
                <div class="neo-stat-icon">
                    <i class="dashicons dashicons-cart"></i>
                </div>
                <div class="neo-stat-info">
                    <span class="neo-stat-label">فروش کل</span>
                    <span class="neo-stat-value"><?php echo number_format($total_sales); ?> <small>تومان</small></span>
                </div>
            </div>

            <div class="neo-stat-vendor-card card-green">
                <div class="neo-stat-icon">
                    <i class="dashicons dashicons-products"></i>
                </div>
                <div class="neo-stat-info">
                    <span class="neo-stat-label">محصولات فعال</span>
                    <span class="neo-stat-value"><?php echo $active_products; ?></span>
                </div>
            </div>

            <div class="neo-stat-vendor-card card-orange">
                <div class="neo-stat-icon">
                    <i class="dashicons dashicons-clock"></i>
                </div>
                <div class="neo-stat-info">
                    <span class="neo-stat-label">سفارشات در انتظار</span>
                    <span class="neo-stat-value"><?php echo $pending_orders; ?></span>
                </div>
            </div>
        </div>

        <!-- بخش لیست تراکنش‌های اخیر یا اعلان‌ها -->
        <div class="neo-recent-activity">
            <h3>آخرین وضعیت فروش</h3>
            <div class="neo-table-responsive">
                <table class="neo-table">
                    <thead>
                        <tr>
                            <th>شناسه سفارش</th>
                            <th>محصول</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 20px;">داده‌ای برای نمایش وجود ندارد.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>