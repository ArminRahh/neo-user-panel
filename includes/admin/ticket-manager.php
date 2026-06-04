<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// کالبد صفحه مدیریت تیکت‌ها
function neo_ticket_manager_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // بررسی اینکه آیا در حال مشاهده یک تیکت خاص هستیم یا لیست تیکت‌ها
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
    $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

    echo '<div class="wrap">';
    
    if ($action === 'view' && $ticket_id > 0) {
        // نمایش صفحه پاسخ به تیکت
        echo '<h1 class="wp-heading-inline">مشاهده و پاسخ به تیکت #' . $ticket_id . '</h1>';
        echo '<a href="?page=neo-tickets" class="page-title-action">بازگشت به لیست</a>';
        echo '<hr class="wp-header-end">';
        
        // در اینجا فرم پاسخ و تاریخچه پیام‌ها را لود خواهیم کرد
        neo_admin_ticket_reply_view($ticket_id);
    } else {
        // نمایش لیست تیکت‌ها
        echo '<h1 class="wp-heading-inline">مدیریت تیکت‌های کاربران</h1>';
        echo '<hr class="wp-header-end">';
        
        // در اینجا جدول لیست تیکت‌ها را لود خواهیم کرد
        neo_admin_ticket_list_view();
    }
    
    echo '</div>';
}

function neo_admin_ticket_list_view() {
    global $wpdb;
    $table_tickets = $wpdb->prefix . 'neo_tickets';
    // اگر جدول پاسخ‌ها (replies) را هنوز نساخته‌اید، در کوئری زیر بخش مربوط به آن را حذف کنید.
    
    // بررسی وجود جدول در دیتابیس برای جلوگیری از ارور
    if($wpdb->get_var("SHOW TABLES LIKE '$table_tickets'") != $table_tickets) {
        echo '<div class="wrap"><h1>مدیریت تیکت‌ها</h1><p>جدول تیکت‌ها در دیتابیس یافت نشد.</p></div>';
        return;
    }

    // واکشی تیکت‌ها از دیتابیس به همراه نام کاربر
    $tickets = $wpdb->get_results( "
        SELECT 
            t.id, 
            t.title, 
            t.department, 
            t.status, 
            t.created_at, 
            u.display_name AS user_name
        FROM $table_tickets t
        LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
        ORDER BY t.created_at DESC
        LIMIT 50
    ");
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">لیست تیکت‌های پشتیبانی</h1>
        <hr class="wp-header-end">

        <table class="wp-list-table widefat fixed striped table-view-list mt-4">
            <thead>
                <tr>
                    <th style="width: 50px;">شناسه</th>
                    <th>موضوع</th>
                    <th>کاربر</th>
                    <th>دپارتمان</th>
                    <th>وضعیت</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tickets)) : ?>
                    <?php foreach ($tickets as $ticket) : ?>
                        <tr>
                            <td>#<?php echo esc_html($ticket->id); ?></td>
                            <td><strong><?php echo esc_html($ticket->title); ?></strong></td>
                            <td><?php echo esc_html($ticket->user_name ? $ticket->user_name : 'کاربر نامشخص'); ?></td>
                            <td><?php echo esc_html($ticket->department); ?></td>
                            <td>
                                <?php 
                                    $status_class = 'status-open'; 
                                    $status_label = 'باز';
                                    
                                    if ($ticket->status === 'closed') {
                                        $status_class = 'status-closed';
                                        $status_label = 'بسته شده';
                                    } elseif ($ticket->status === 'answered') {
                                        $status_class = 'status-answered';
                                        $status_label = 'پاسخ داده شده';
                                    }
                                    
                                    echo '<span class="neo-status-badge ' . esc_attr($status_class) . '">' . esc_html($status_label) . '</span>';
                                ?>
                            </td>
                            <td><?php echo wp_date('Y-m-d H:i', strtotime($ticket->created_at)); ?></td>
                            <td>
                                <a href="?page=neo-tickets&action=view&ticket_id=<?php echo intval( $ticket->id ); ?>" class="button button-primary button-small">مشاهده</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="neo-text-center" style="text-align: center; padding: 20px;">هیچ تیکتی یافت نشد.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function neo_admin_ticket_reply_view($ticket_id) {
    global $wpdb;
    $table_tickets = $wpdb->prefix . 'neo_tickets';
    $table_replies = $wpdb->prefix . 'neo_ticket_replies'; // اطمینان حاصل کنید این جدول در دیتابیس ساخته شده باشد

    // 1. پردازش فرم ارسال پاسخ یا تغییر وضعیت
    if ( isset($_POST['submit_ticket_reply']) && isset($_POST['reply_content']) && isset($_POST['neo_reply_nonce']) ) {
        if ( wp_verify_nonce($_POST['neo_reply_nonce'], 'neo_ticket_reply_action') ) {
            $reply_content = sanitize_textarea_field($_POST['reply_content']);
            $new_status    = sanitize_text_field($_POST['ticket_status']);

            // اگر متنی نوشته شده بود، آن را به عنوان پاسخ ذخیره کن
            if ( ! empty($reply_content) ) {
                $wpdb->insert(
                    $table_replies,
                    array(
                        'ticket_id'  => $ticket_id,
                        'user_id'    => get_current_user_id(), // آیدی مدیر که در حال پاسخگویی است
                        'message'    => $reply_content,        // فرض بر این است که نام ستون پیام message است
                        'created_at' => current_time('mysql')
                    )
                );
            }
            
            // بروزرسانی وضعیت تیکت (مثلاً تبدیل به 'پاسخ داده شده' یا 'بسته شده')
            $wpdb->update(
                $table_tickets,
                array( 'status' => $new_status ),
                array( 'id' => $ticket_id )
            );

            echo '<div class="notice notice-success is-dismissible"><p>تیکت با موفقیت بروزرسانی شد.</p></div>';
        }
    }

    // 2. واکشی اطلاعات اصلی تیکت
    $ticket = $wpdb->get_row($wpdb->prepare("
        SELECT t.*, u.display_name AS user_name, u.user_email 
        FROM {$table_tickets} t 
        LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID 
        WHERE t.id = %d
    ", $ticket_id));

    if ( ! $ticket ) {
        echo '<div class="notice notice-error"><p>تیکت مورد نظر یافت نشد.</p></div>';
        return;
    }

    // 3. واکشی پیام‌های قبلی (پاسخ‌ها)
    $replies = array();
    // بررسی وجود جدول برای جلوگیری از خطای دیتابیس در صورت ساخته نشدن جدول
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_replies'") == $table_replies ) {
        $replies = $wpdb->get_results($wpdb->prepare("
            SELECT r.*, u.display_name AS user_name 
            FROM {$table_replies} r 
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID 
            WHERE r.ticket_id = %d 
            ORDER BY r.created_at ASC
        ", $ticket_id));
    }
    ?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- ستون اصلی: نمایش پیام‌ها و فرم پاسخ -->
            <div id="post-body-content">
                <!-- لیست پاسخ‌ها -->
                <?php if ( ! empty($replies) ) : ?>
                    <?php foreach ( $replies as $reply ) : 
                        // بررسی اینکه آیا پاسخ دهنده خود کاربر است یا مدیر
                        $is_admin = ( $reply->user_id != $ticket->user_id ) ? true : false;
                        $msg_class = $is_admin ? 'neo-admin-reply' : 'neo-user-reply';
                    ?>
                        <div class="neo-ticket-message-box <?php echo $msg_class; ?>">
                            <div class="neo-msg-header">
                                <strong><?php echo esc_html($reply->user_name ? $reply->user_name : 'پشتیبان'); ?> <?php echo $is_admin ? '<span class="badge">پشتیبان</span>' : ''; ?></strong>
                                <span><?php echo wp_date('Y-m-d H:i', strtotime($reply->created_at)); ?></span>
								<span><strong>وضعیت تیکت:</strong> 
                            <?php 
                                if ( $ticket->status === 'open' ) echo '<span style="color:#00a32a;">باز</span>';
                                elseif ( $ticket->status === 'closed' ) echo '<span style="color:#d63638;">بسته شده</span>';
                                elseif ( $ticket->status === 'answered' ) echo '<span style="color:#2271b1;">پاسخ داده شده</span>';
                                else echo esc_html($ticket->status);
                            ?>
                        </span>
                            </div>
                            <div class="neo-msg-body">
                                <?php echo wpautop(esc_html($reply->message)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- فرم ارسال پاسخ -->
                <div class="neo-reply-form">
                    <h3>ارسال پاسخ</h3>
                    <form action="" method="post">
                        <?php wp_nonce_field('neo_ticket_reply_action', 'neo_reply_nonce'); ?>
                        
                        <textarea name="reply_content" rows="6" style="width:100%; margin-bottom: 15px;" placeholder="پاسخ خود را بنویسید..."></textarea>
                        
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <label for="ticket_status"><strong>وضعیت تیکت پس از ارسال:</strong></label>
                            <select name="ticket_status" id="ticket_status">
                                <option value="answered" <?php selected($ticket->status, 'open'); ?>>پاسخ داده شده</option>
                                <option value="open" <?php selected($ticket->status, 'answered'); ?>>باز نگه داشتن</option>
                                <option value="closed" <?php selected($ticket->status, 'closed'); ?>>بستن تیکت</option>
                            </select>
                        </div>
                        
                        <input type="submit" name="submit_ticket_reply" class="button button-primary button-large" value="ارسال پاسخ و بروزرسانی">
                    </form>
                </div>
            </div>

            <!-- ستون کناری: اطلاعات تیکت -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="postbox">
                    <h2 class="hndle" style="padding: 10px 15px;"><span>اطلاعات تیکت #<?php echo $ticket->id; ?></span></h2>
                    <div class="inside">
                        <p><strong>فرستنده:</strong> <?php echo esc_html($ticket->user_name ? $ticket->user_name : 'کاربر نامشخص'); ?></p>
                        <p><strong>ایمیل:</strong> <a href="mailto:<?php echo esc_attr($ticket->user_email); ?>"><?php echo esc_html($ticket->user_email); ?></a></p>
                        <hr>
                        <p><strong>موضوع:</strong> <?php echo esc_html($ticket->title); ?></p>
                        <p><strong>دپارتمان:</strong> <?php echo esc_html($ticket->department); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .neo-ticket-message-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .neo-msg-header {
            padding: 10px 15px;
            background: #f9f9f9;
            border-bottom: 1px solid #ccd0d4;
            display: flex;
            justify-content: space-between;
            color: #50575e;
        }
        .neo-msg-body {
            padding: 5px 15px;
            font-size: 14px;
            line-height: 1.6;
        }
        .neo-admin-reply {
            border-right: 4px solid #2271b1; /* مشخص کردن پیام ادمین با نوار آبی */
        }
        .neo-user-reply {
            border-right: 4px solid #00a32a; /* مشخص کردن پیام کاربر با نوار سبز */
        }
        .neo-msg-header .badge {
            background: #2271b1;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
        }
        .neo-reply-form {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
    </style>
    <?php
    
}

