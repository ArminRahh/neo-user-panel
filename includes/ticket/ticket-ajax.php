<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_neo_submit_new_ticket', 'neo_ajax_submit_new_ticket');
add_action('wp_ajax_nopriv_neo_submit_new_ticket', 'neo_ajax_submit_new_ticket');

function neo_ajax_submit_new_ticket() {
    // ۱. بررسی اعتبار کاربر
    if (!is_user_logged_in()) {
        wp_send_json_error('شما باید وارد حساب کاربری خود شوید.');
    }

    $user_id = get_current_user_id();

    // ۲. دریافت و پاک‌سازی اطلاعات
    $title      = isset($_POST['ticket_title']) ? sanitize_text_field($_POST['ticket_title']) : '';
    $department = isset($_POST['ticket_department']) ? sanitize_text_field($_POST['ticket_department']) : '';
    $priority   = isset($_POST['ticket_priority']) ? sanitize_text_field($_POST['ticket_priority']) : 'normal';
    $message    = isset($_POST['ticket_message']) ? sanitize_textarea_field($_POST['ticket_message']) : '';

    if (empty($title) || empty($department) || empty($message)) {
        wp_send_json_error('لطفاً تمامی فیلدهای ضروری را پر کنید.');
    }

    global $wpdb;
    $tickets_table = $wpdb->prefix . 'neo_tickets';
    $replies_table = $wpdb->prefix . 'neo_ticket_replies';

    // ۳. ذخیره تیکت اصلی در دیتابیس
    $inserted_ticket = $wpdb->insert(
        $tickets_table,
        array(
            'user_id'    => $user_id,
            'title'      => $title,
            'department' => $department,
            'priority'   => $priority,
            'status'     => 'open',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if (!$inserted_ticket) {
        wp_send_json_error('خطا در ذخیره تیکت در دیتابیس.');
    }

    $ticket_id = $wpdb->insert_id;

    // ۴. ذخیره متن پیام در جدول پاسخ‌ها (به عنوان اولین پیام تیکت)
    $wpdb->insert(
        $replies_table,
        array(
            'ticket_id' => $ticket_id,
            'user_id'   => $user_id,
            'message'   => $message,
            'created_at'=> current_time('mysql')
        ),
        array('%d', '%d', '%s', '%s')
    );

    // ارسال پاسخ موفقیت‌آمیز به JS
    wp_send_json_success(array(
        'message' => 'تیکت با موفقیت ایجاد شد',
        'ticket_id' => $ticket_id
    ));
}
/**
 * دریافت و نمایش محتوای سینگل تیکت (چت تیکت) با AJAX در فرانت‌اند
 */
add_action('wp_ajax_neo_load_single_ticket', 'neo_ajax_load_single_ticket');
function neo_ajax_load_single_ticket() {
    global $wpdb;
    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
    $user_id = get_current_user_id();

    if (!$ticket_id || !$user_id) {
        wp_send_json_error(['message' => 'درخواست نامعتبر است.']);
    }

    $table_tickets = $wpdb->prefix . 'neo_tickets';
    $table_replies = $wpdb->prefix . 'neo_ticket_replies';

    // بررسی وجود تیکت و اینکه متعلق به همین کاربر باشد
    $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_tickets} WHERE id = %d AND user_id = %d", $ticket_id, $user_id));
    if (!$ticket) {
        wp_send_json_error(['message' => 'تیکت یافت نشد.']);
    }

    // دریافت تاریخچه چت (پاسخ‌ها)
    $replies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_replies} WHERE ticket_id = %d ORDER BY created_at ASC", $ticket_id));

    ob_start();
    ?>
    <div class="neo-ticket-header">
        <h3>موضوع: <?php echo esc_html($ticket->title); ?></h3>
        <button id="neo-back-from-single" class="neo-btn neo-btn-secondary">
            بازگشت به لیست
        </button>
    </div>
    
    <div class="neo-ticket-chat-box">
        <!-- پیام اولیه کاربر -->
        <div class="chat-message">
            <div style="font-size: 11px; color: #555; margin-bottom: 8px; font-weight: bold;">
                شما - <?php echo wp_date('Y-m-d H:i', strtotime($ticket->created_at)); ?>
            </div>
            <div style="line-height: 1.6; color: #014aa9;font-size:14px;"><?php echo nl2br(esc_html($ticket->message)); ?></div>
        </div>

        <!-- لیست پاسخ‌ها -->
        <?php if (!empty($replies)): foreach ($replies as $reply): 
            $is_admin = ($reply->sender_type === 'admin');
            $bg_color = $is_admin ? '#fbeaea' : '#e5f0fa';
            $border_color = $is_admin ? '#d63638' : '#2271b1';
            $sender_name = $is_admin ? 'پشتیبانی سایت' : 'شما';
        ?>
            <div class="chat-message" style="background: <?php echo $bg_color; ?>; padding: 12px; border-radius: 5px; margin-bottom: 10px; border-right: 4px solid <?php echo $border_color; ?>;">
                <div style="font-size: 11px; color: #555; margin-bottom: 8px; font-weight: bold;">
                    <?php echo $sender_name; ?> - <?php echo wp_date('Y-m-d H:i', strtotime($reply->created_at)); ?>
                </div>
                <div style="line-height: 1.6; color: #333;font-size:14px;"><?php echo nl2br(esc_html($reply->message)); ?></div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- فرم ارسال پاسخ جدید توسط کاربر -->
    <?php if ($ticket->status !== 'closed'): ?>
        <form id="neo-reply-ticket-form">
            <input type="hidden" name="ticket_id" value="<?php echo intval($ticket->id); ?>">
            <div class="neo-frm-reply-msge">
                <label>ارسال پاسخ جدید:</label>
                <textarea name="reply_message" rows="3" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
            </div>
            <button type="submit">ارسال پاسخ</button>
            <div id="neo-reply-message" style="margin-top: 10px;"></div>
        </form>
    <?php else: ?>
        <div style="background: #fbeaea; color: #d63638; padding: 10px; border-radius: 4px; text-align: center; border: 1px solid #f8cbcb;font-size:14px;">
            این تیکت بسته شده است و امکان ارسال پاسخ جدید وجود ندارد.
        </div>
    <?php endif; ?>

    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
