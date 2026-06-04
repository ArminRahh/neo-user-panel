<?php
// از دسترسی مستقیم جلوگیری می‌کنیم
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}
global $wpdb;
    $current_user_id = get_current_user_id();
    $table_tickets = $wpdb->prefix . 'neo_tickets';
    
    // فراخوانی تیکت‌های کاربر فعلی از دیتابیس
    $user_tickets = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$table_tickets} 
        WHERE user_id = %d 
        ORDER BY created_at DESC
    ", $current_user_id));
    
?>

<!-- بخش محتوای تیکت ها -->
<div id="neo-content-tickets" class="neo-tab-content">
    <div class="neo-ticket-header">
        <h3 class="neo-panel-title">پشتیبانی و تیکت‌ها</h3>
        <button id="neo-show-create-ticket" class="neo-btn btn-ticket-primary">ثبت تیکت جدید</button>
    </div>

    <!-- نمای لیست تیکت ها -->
    <div id="neo-ticket-list-view">
        <table class="neo-table-ticket">
            <thead>
                <tr>
                    <th>شماره تیکت</th>
                    <th>عنوان</th>
                    <th>بخش</th>
                    <th>وضعیت</th>
                    <th>آخرین بروزرسانی</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody id="neo-ticket-list-body">
                    <?php if ( ! empty($user_tickets) ) : ?>
                        <?php foreach ( $user_tickets as $ticket ) : ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td><?php echo esc_html($ticket->id); ?></td>
                                <td><?php echo esc_html($ticket->title); ?></td>
                                <td><?php echo esc_html($ticket->department); ?></td>
                                <td>
                                    <?php 
                                        if ( $ticket->status === 'open' ) echo '<span style="color:#00a32a;">در انتظار پاسخ</span>';
                                        elseif ( $ticket->status === 'closed' ) echo '<span style="color:#d63638;">بسته شده</span>';
                                        elseif ( $ticket->status === 'answered' ) echo '<span style="color:#2271b1;">پاسخ داده شده</span>';
                                        else echo esc_html($ticket->status);
                                    ?>
                                </td>
                                <td dir="ltr"><?php echo wp_date('Y-m-d H:i', strtotime($ticket->updated_at ?? $ticket->created_at)); ?></td>
                                <td>
                                    <button class="neo-btn-single-view-ticket" data-ticket-id="<?php echo esc_attr($ticket->id); ?>" style="background: transparent; color: #2271b1; border: 1px solid #2271b1; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                        مشاهده
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" style="padding: 20px; text-align: center; color: #777;">شما تاکنون تیکتی ثبت نکرده‌اید.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
        </table>
    </div>
     <!-- نمای مشاهده یک تیکت (چت) - در ابتدا مخفی است -->
    <div id="neo-ticket-single-view" style="display: none;">
        <!-- محتوای چت تیکت با AJAX در اینجا لود می‌شود -->
    </div>                   
    <!-- نمای فرم ایجاد تیکت (پیش‌فرض مخفی) -->
    <div id="neo-ticket-create-view" style="display: none;">
        <button id="neo-back-to-tickets" class="neo-btn btn-ticket-secondary">بازگشت به لیست</button>
        <form id="neo-create-ticket-form" class="neo-form" enctype="multipart/form-data">
            <div class="neo-form-row">
                <div class="neo-form-group">
                    <label>عنوان تیکت *</label>
                    <input type="text" name="ticket_title" class="neo-input-ticket" placeholder="موضوع مشکل یا سوال شما" required>
                </div>
                <div class="neo-form-group">
                    <label>دپارتمان *</label>
                    <select name="ticket_department" class="neo-input" required>
                        <option value="support">پشتیبانی فنی</option>
                        <option value="sales">فروش و مالی</option>
                        <option value="management">مدیریت</option>
                    </select>
                </div>
            </div>
            
            <div class="neo-form-group">
                <label>متن پیام *</label>
                <textarea name="ticket_message" class="neo-input" rows="5" placeholder="جزئیات درخواست خود را بنویسید..." required></textarea>
            </div>

            <div class="neo-form-group">
                <label>پیوست فایل (اختیاری)</label>
                <input type="file" name="ticket_attachment" class="neo-input-file" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <div class="neo-form-actions">
                <button type="submit" class="neo-btn btn-ticket-success">ارسال تیکت</button>
				<div id="neo-ticket-message" style="display:none; margin-top:15px; padding:10px; border-radius:5px;"></div>
            </div>
        </form>
    </div>
</div>
