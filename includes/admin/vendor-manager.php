<?php
/**
 * Admin Vendor Management Page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function neo_vendor_manager_page_html() {
    // بررسی سطح دسترسی کاربر
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'neo_vendors';

    // دریافت لیست فروشندگان از جدول اختصاصی
    $vendors = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC");

    ?>
    <div class="wrap">
        <h1>مدیریت درخواست‌های فروشندگی</h1>
        
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>آیدی کاربر</th>
                    <th>نام فروشگاه</th>
                    <th>کد ملی</th>
                    <th>شماره شبا</th>
                    <th>وضعیت</th>
                    <th>تاریخ ثبت‌نام</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $vendors ) : ?>
                    <?php foreach ( $vendors as $vendor ) : ?>
                        <tr>
                            <td><?php echo esc_html( $vendor->user_id ); ?></td>
                            <td><?php echo esc_html( $vendor->store_name ); ?></td>
                            <td><?php echo esc_html( $vendor->national_code ); ?></td>
                            <td><?php echo esc_html( $vendor->shaba_number ); ?></td>
                            <td>
                                <?php 
                                    if($vendor->status == 'pending') echo '<span style="color:orange;">در انتظار تایید</span>';
                                    elseif($vendor->status == 'approved') echo '<span style="color:green;">تایید شده</span>';
                                    elseif($vendor->status == 'rejected') echo '<span style="color:red;">رد شده</span>';
                                ?>
                            </td>
                            <td><?php echo esc_html( $vendor->created_at ); ?></td>
                            <td>
                                <!-- دکمه‌های عملیات برای تایید یا رد -->
                                <a href="#" class="button button-primary">بررسی</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">هیچ درخواست فروشندگی یافت نشد.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
