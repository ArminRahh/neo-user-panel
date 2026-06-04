<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neo_render_panel_layout() {
    if ( ! is_user_logged_in() ) {
         wp_redirect(home_url('/login/'));
    }

    $current_user = wp_get_current_user();
    ob_start(); ?>

<div class="neo-wrapper">
   <header class="neo-top-header">
      <div class="neo-header-right">
         <button id="neo-menu-toggle" class="neo-btn-icon" aria-label="منو">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round">
               <line x1="3" y1="12" x2="21" y2="12"></line>
               <line x1="3" y1="6" x2="21" y2="6"></line>
               <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
         </button>
         <!-- نمایش لوگو و نام سایت -->
         <div class="neo-brand">
            <?php 
               // نمایش لوگوی سایت از تنظیمات وردپرس
               if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                   the_custom_logo(); 
               } else {
                   // اگر لوگو تنظیم نشده بود، نام سایت را نمایش بده
                   echo '<strong class="neo-brand-name">' . esc_html( get_bloginfo( 'name' ) ) . '</strong>';
               }
               ?>
         </div>
      </div>

      <!-- سمت چپ: آیکون‌های تیکت، اعلان و پروفایل -->
      <div class="neo-header-left">
         <div class="neo-header-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round">
               <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span class="neo-item-text">تیکت‌ها</span>
         </div>

         <div class="neo-header-item neo-profile-dropdown">
            <div class="neo-profile-icon" id="neo-profile-icon-toggle">
               <svg viewBox="0 0 25 25" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
               </svg>
            </div>

            <!-- پاپ‌آپ دراپ‌داون -->
            <div class="neo-dropdown-menu" id="neo-dropdown-menu">
               <div class="neo-dropdown-header">
                  <strong><?php echo esc_html( $current_user->display_name ); ?></strong>

                  <?php
                    // استخراج نام نمایشی نقش کاربر (فارسی شده توسط وردپرس/ووکامرس)
                    global $wp_roles;
                    if ( ! isset( $wp_roles ) ) {
                        $wp_roles = new WP_Roles();
                    }
                    $role_slug = ! empty( $current_user->roles ) ? $current_user->roles[0] : '';
                    $role_name = $role_slug && isset( $wp_roles->role_names[ $role_slug ] ) 
                                 ? translate_user_role( $wp_roles->role_names[ $role_slug ] ) 
                                 : 'کاربر سایت';
                    ?>
                  <div>
                     <span class="neo-role"><?php echo esc_html( $role_name ); ?></span>
                     <span class="neo-user-id">کد کاربری: <?php echo esc_html( $current_user->ID ); ?></span>
                  </div>
               </div>

               <div class="neo-dropdown-item">
                  <a href="#" data-tab="account">
                     <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                     </svg>
                     حساب کاربری
                  </a>
               </div>

               <div class="neo-dropdown-item">
                  <a href="#">
                     <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                     </svg>
                     همکاری در فروش
                  </a>
               </div>

               <div class="neo-dropdown-item neo-logout-item">
                  <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
                     <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                     </svg>
                     خروج از حساب
                  </a>
               </div>
            </div>
         </div>
      </div>
   </header>

   <!-- سایدبار منوها -->
   <aside class="neo-sidebar">
      
      <ul class="neo-menu">
         <li class="neo-menu-item">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="neo-back-to-site" id="neo-smart-back">
               <i class="fa fa-home"></i> بازگشت به سایت
            </a>
         </li>
         <li class="neo-menu-item active" data-tab="dashboard">
            <i class="fa fa-tachometer"></i> پیشخوان
         </li>
         <li class="neo-menu-item" data-tab="orders">
            <i class="fa fa-shopping-cart"></i> سفارش‌های من
         </li>
         <li class="neo-menu-item" data-tab="wallet">
            <i class="fa fa-credit-card"></i> کیف پول
         </li>
         <li class="neo-menu-item" data-tab="tickets">
            <i class="fa fa-support"></i> پشتیبانی و تیکت
         </li>
         <li class="neo-menu-item" data-tab="account">
            <i class="fa fa-cog"></i> تنظیمات حساب
         </li>
         
         <?php 
            // دریافت وضعیت فروشنده
            require_once plugin_dir_path(__FILE__) . 'includes/vendor/vendor-core.php';
            // $vendor_status = Neo_Vendor_Core::get_vendor_status(); 
            $vendor_status = 'approved'; 
            ?>
            <!-- <?php if ( $vendor_status === 'approved' ) : ?> -->
            <!-- منوی فروشندگان (برای کاربران تایید شده) -->
            <li class="neo-menu-item neo-has-submenu" data-tab="vendor-dashboard">
               <i></i> پنل فروشندگان
            </li>
            <li class="neo-menu-item " data-tab="vendor-products">
               <i class="dashicons dashicons-products"></i>
               مدیریت محصولات
            </li>
            <?php else : ?>
               <!-- منوی ثبت نام (برای کاربران عادی یا در انتظار تایید) -->
               <li class="neo-menu-item" data-tab="vendor-register">
                  <i class="fa fa-cog"></i> فروشنده شوید
               </li>
            <?php endif; ?>
      </ul>
   </aside>
   <div id="neo-sidebar-overlay"></div>
   <!-- محفظه اصلی برای لود محتوا با AJAX -->
   <main class="neo-main-content">
      <div id="neo-loader" class="neo-loader-hidden">
         <span class="neo-spinner"></span> در حال بارگذاری...
      </div>
      <div id="neo-content-area">
         <?php echo neo_get_dashboard_html();?>
      </div>
   </main>
   <!-- پاپ آپ تایید خروج -->
   <div id="neo-logout-modal" class="neo-modal-overlay">
      <div class="neo-modal-box">
         <h3 class="neo-modal-title">خروج از حساب کاربری</h3>
         <p class="neo-modal-desc">آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟</p>

         <div class="neo-modal-actions">
            <button id="neo-cancel-logout" class="neo-btn neo-btn-secondary">انصراف</button>
            <!-- دریافت لینک خروج امن وردپرس -->
            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" id="neo-confirm-logout"
               class="neo-btn neo-btn-danger">بله، خارج می‌شوم</a>
         </div>
      </div>
   </div>

</div>

<?php return ob_get_clean();
}

add_action( 'wp_ajax_neo_load_tab', 'neo_ajax_handler' );
function neo_ajax_handler() {
    check_ajax_referer( 'neo_panel_secure_nonce', 'security' );

    $tab = isset( $_POST['tab'] ) ? sanitize_text_field( $_POST['tab'] ) : 'dashboard';
    
    // انتخابگر تب های سایدبار
    $response_html = '';

    switch ( $tab ) {
        

        case 'dashboard':
		    $html = neo_get_dashboard_html();
			wp_send_json_success( array( 'html' => $html ) );		  
            break;
                case 'orders':
            // دریافت سفارشات کاربر فعلی از ووکامرس
            $customer_orders = wc_get_orders( array(
                'customer_id' => get_current_user_id(),
                'limit'       => -1, // نمایش همه سفارشات
                'status'      => 'any',
            ) );

            ob_start(); ?>
            <div class="neo-fade-in">
               <div class="neo-flex-between">
                  <h2> تاریخچه سفارشات</h2>
                  <!-- باکس جستجوی زنده با جاوااسکریپت -->
                  <div class="neo-search-box">
                     <input type="text" id="neo-order-search" class="neo-input" placeholder="جستجو در سفارشات...">
                  </div>
               </div>

               <?php if ( empty( $customer_orders ) ) : ?>
               <div class="neo-empty-state">
                  <p>شما هنوز هیچ سفارشی ثبت نکرده‌اید.</p>
               </div>
               <?php else : ?>
               <div class="neo-table-responsive">
                  <table class="neo-table" id="neo-orders-table">
                     <thead>
                        <tr>
                           <th>شماره سفارش</th>
                           <th>تاریخ</th>
                           <th>وضعیت</th>
                           <th>مبلغ کل</th>
                           <th>عملیات</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ( $customer_orders as $order ) : 
                           $order_id = $order->get_id();
                           $status = $order->get_status();
                           $status_name = wc_get_order_status_name( $status );
                           // تعیین کلاس رنگی بر اساس وضعیت
                           $badge_class = 'neo-badge-' . $status; 
                        ?>
                        <tr class="neo-order-row">
                           <td data-label="شماره سفارش">#<?php echo esc_html( $order->get_order_number() ); ?></td>
                           <td data-label="تاریخ"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></td>
                           <td data-label="وضعیت">
                              <span class="neo-badge <?php echo esc_attr( $badge_class ); ?>">
                                 <?php echo esc_html( $status_name ); ?>
                              </span>
                           </td>
                           <td data-label="مبلغ کل"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                           <td data-label="عملیات">
                              <button class="neo-btn neo-btn-sm neo-view-order-btn"
                                 data-order-id="<?php echo esc_attr( $order_id ); ?>">
                                 مشاهده فاکتور
                              </button>
                           </td>
                        </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
               <?php endif; ?>
            </div>

            <!-- ساختار مودال (پاپ‌آپ) برای نمایش جزئیات سفارش -->
            <div id="neo-order-modal" class="neo-modal">
               <div class="neo-modal-content">
                  <span class="neo-modal-close">&times;</span>
                  <div id="neo-modal-body">
                     <!-- محتوای جزئیات با AJAX اینجا لود میشود -->
                  </div>
               </div>
            </div>
            <?php
            $response_html = ob_get_clean();
            break;

                       case 'wallet':
            // 1. دریافت موجودی واقعی کاربر
            $current_balance = get_user_meta( get_current_user_id(), 'neo_wallet_balance', true );
            if ( empty( $current_balance ) ) {
                $current_balance = 0;
            }

            // آیدی محصول "شارژ کیف پول" (این عدد را با آیدی محصولی که ساختید جایگزین کنید)
            $recharge_product_id = 123; 

            // 2. دریافت تراکنش های واقعی (از سفارشات ووکامرس)
            $transactions = array();
            if ( class_exists( 'WooCommerce' ) ) {
                $customer_orders = wc_get_orders( array(
                    'customer_id' => get_current_user_id(),
                    'limit'       => 30, // نمایش 30 تراکنش آخر
                ) );

                foreach ( $customer_orders as $order ) {
                    $is_recharge = false;
                    foreach ( $order->get_items() as $item ) {
                        if ( $item->get_product_id() == $recharge_product_id ) {
                            $is_recharge = true;
                            break;
                        }
                    }
                    if ( $is_recharge ) {
                        $transactions[] = array(
                            'id'      => $order->get_order_number(),
                            'date'    => wc_format_datetime( $order->get_date_created(), 'Y/m/d' ),
                            'amount'  => $order->get_total(),
                            'type'    => 'شارژ حساب',
                            'gateway' => $order->get_payment_method_title(),
                            'status'  => wc_get_order_status_name( $order->get_status() )
                        );
                    }
                }
            }
            ?>
            <div class="neo-fade-in">
                <!-- کارت موجودی و فرم شارژ -->
                <div class="neo-wallet-header">
                    <div class="neo-balance-card">
                        <h4>موجودی فعلی شما</h4>
                        <div class="neo-balance-amount">
                            <?php echo number_format( $current_balance ); ?> <small>تومان</small>
                        </div>
                    </div>
                    <div class="neo-recharge-section">
                        <h3>💳 شارژ کیف پول</h3>
                        <form id="neo-recharge-form" class="neo-form">
                            <!-- انتخاب مبلغ -->
                            <div class="neo-form-group">
                                <label>مبلغ شارژ را انتخاب کنید:</label>
                                <div class="neo-amount-options">
                                    <a href="#" id="neo-show-custom-amount">مبلغ دلخواه</a>

                                    <div id="neo-preset-container" class="neo-fade-in">
                                        <select id="neo-preset-amount" class="neo-input">
                                            <option value="">-- انتخاب کنید --</option>
                                            <option value="50000">50,000 تومان</option>
                                            <option value="100000">100,000 تومان</option>
                                            <option value="200000">200,000 تومان</option>
                                            <option value="500000">500,000 تومان</option>
                                        </select>
                                    </div>

                                    <div id="neo-custom-container" style="display: none; position: relative;" class="neo-fade-in">
                                        <input type="number" id="neo-custom-amount" class="neo-input"
                                               placeholder="مبلغ دلخواه خود را وارد کنید... (مثلا: 150000)" min="1000"
                                               style="padding-left: 40px;">
                                        <button type="button" id="neo-hide-custom-amount" class="neo-icon-btn" title="بازگشت به مبالغ پیشنهادی">✖</button>
                                    </div>
                                </div>
                            </div>

                            <!-- انتخاب درگاه به صورت پویا از ووکامرس -->
                            <div class="neo-form-group">
                                <label>انتخاب درگاه پرداخت:</label>
                                <div class="neo-gateway-selector">
                                    <?php
                                    if ( class_exists( 'WooCommerce' ) ) {
                                        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                                        if ( ! empty( $available_gateways ) ) {
                                            $first_gateway = true;
                                            foreach ( $available_gateways as $gateway ) {
                                                // درگاه کیف پول را از لیست شارژ مخفی می کنیم تا با خودش شارژ نشود
                                                if ( $gateway->id === 'wallet' ) continue; 
                                                
                                                $checked = $first_gateway ? 'checked' : '';
                                                $first_gateway = false;
                                                ?>
                                                <label class="neo-gateway-option">
                                                    <input type="radio" name="gateway" value="<?php echo esc_attr( $gateway->id ); ?>" <?php echo $checked; ?>>
                                                    <span class="neo-gateway-box">
                                                        <?php 
                                                        $icon = $gateway->get_icon();
                                                        echo !empty($icon) ? $icon . ' ' : '💳 ';
                                                        echo esc_html( $gateway->get_title() ); 
                                                        ?>
                                                    </span>
                                                </label>
                                                <?php
                                            }
                                        } else {
                                            echo '<p style="color:red;font-size:13px;">هیچ درگاه پرداختی در سایت فعال نیست.</p>';
                                        }
                                    } else {
                                        echo '<p style="color:red;font-size:13px;">افزونه ووکامرس نصب نیست.</p>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div id="neo-wallet-message" style="display:none; margin-top:15px; padding:10px; border-radius:8px;"></div>

                            <button type="submit" class="neo-btn neo-btn-primary" style="margin-top: 25px; width: 100%; justify-content: center;">
                                <span class="neo-btn-text">پرداخت و شارژ حساب</span>
                                <span class="neo-btn-loader" style="display:none;">⏳ در حال انتقال به بانک...</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- تاریخچه تراکنش‌ها -->
                <h3 style="margin-top: 40px; color: var(--neo-primary);">🧾 تاریخچه تراکنش‌ها</h3>
                <div class="neo-table-responsive">
                    <table class="neo-table">
                        <thead>
                            <tr>
                                <th>شماره سفارش</th>
                                <th>تاریخ</th>
                                <th>نوع</th>
                                <th>درگاه</th>
                                <th>مبلغ (تومان)</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if ( ! empty( $transactions ) ) : 
                            foreach ( $transactions as $trx ) : 
                                $status_class = ($trx['status'] === 'تکمیل شده') ? 'neo-text-success' : 'neo-text-danger';
                        ?>
                            <tr>
                                <td><?php echo esc_html( $trx['id'] ); ?></td>
                                <td><?php echo esc_html( $trx['date'] ); ?></td>
                                <td><?php echo esc_html( $trx['type'] ); ?></td>
                                <td><?php echo esc_html( $trx['gateway'] ); ?></td>
                                <td class="neo-text-success">
                                    +<?php echo number_format( $trx['amount'] ); ?>
                                </td>
                                <td class="<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $trx['status'] ); ?></td>
                            </tr>
                        <?php 
                            endforeach; 
                        else :
                        ?>
                            <tr><td colspan="6" style="text-align:center;">هیچ تراکنشی یافت نشد.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php

            $response_html = ob_get_clean();
            break;

         case 'tickets':
            ob_start();
            // بررسی کنید مسیر فایل دقیقاً درست باشد
            $ticket_view_file = NEO_PANEL_DIR . 'includes/ticket/views/ticket-content.php';
            if ( file_exists( $ticket_view_file ) ) {
               include $ticket_view_file;
            } else {
               echo '<p>خطا: فایل قالب تیکت یافت نشد.</p>';
            }
            $response_html = ob_get_clean();
            wp_send_json_success( array( 'html' => $response_html ) );
            
            break;

         case 'vendor-register':
            ob_start();
            $vendor_view_path = plugin_dir_path( __FILE__ ) . 'includes/vendor/views/vendor-register.php';
            
            if ( file_exists( $vendor_view_path ) ) {
               include $vendor_view_path;
            } else {
               echo '<p>فایل فرم ثبت‌نام یافت نشد.</p>';
            }
            
            $response_html = ob_get_clean();
            break;
         case 'vendor-products':
            ob_start();
            $vendor_view_path = plugin_dir_path( __FILE__ ) . 'includes/vendor/views/vendor-products.php';
            
            if ( file_exists( $vendor_view_path ) ) {
               include $vendor_view_path;
            } else {
               echo '<p>فایل فرم ثبت‌نام یافت نشد.</p>';
            }
            
            $response_html = ob_get_clean();
               break;

         case 'vendor-dashboard':
            ob_start();
            $vendor_view_path = plugin_dir_path( __FILE__ ) . 'includes/vendor/views/vendor-dashboard.php';
            
            if ( file_exists( $vendor_view_path ) ) {
               include $vendor_view_path;
            } else {
               echo '<p>فایل فرم ثبت‌نام یافت نشد.</p>';
            }
            
            $response_html = ob_get_clean();
            break;

         case 'account':
         $user_id = get_current_user_id();
         $user_info = get_userdata( $user_id );
         
         // دریافت اطلاعات حمل و نقل ووکامرس
         $shipping_first_name = get_user_meta( $user_id, 'shipping_first_name', true );
         $shipping_last_name  = get_user_meta( $user_id, 'shipping_last_name', true );
         $shipping_address_1  = get_user_meta( $user_id, 'shipping_address_1', true );
         $shipping_city       = get_user_meta( $user_id, 'shipping_city', true );
         $shipping_postcode   = get_user_meta( $user_id, 'shipping_postcode', true );

         ob_start(); ?>
         <div class="neo-fade-in">
            <h2>تنظیمات حساب کاربری</h2>
            <p class="neo-text-muted"> در این بخش میتوانید اطلاعات شخصی حساب یا آدرس حمل و نقل خود را ویرایش کنید.</p>

            <form id="neo-account-form" class="neo-form">
               <!-- بخش اطلاعات پایه -->
               <div class="neo-form-section">
                  <h3>اطلاعات شخصی</h3>
                  <div class="neo-form-grid">
                     <div class="neo-form-group">
                        <label>نام</label>
                        <input type="text" name="first_name" class="neo-input"
                           value="<?php echo esc_attr($user_info->first_name); ?>">
                     </div>
                     <div class="neo-form-group">
                        <label>نام خانوادگی</label>
                        <input type="text" name="last_name" class="neo-input"
                           value="<?php echo esc_attr($user_info->last_name); ?>">
                     </div>
                     <div class="neo-form-group">
                        <label>ایمیل (ضروری)</label>
                        <input type="email" name="user_email" class="neo-input"
                           value="<?php echo esc_attr($user_info->user_email); ?>" required>
                     </div>
                  </div>

                  <h4 style="margin-top:20px;font-size:18px;">تغییر رمز عبور (اختیاری)</h4>
                  <div class="neo-form-grid">
                     <div class="neo-form-group">
                        <label>رمز عبور جدید</label>
                        <input type="password" name="new_password" class="neo-input" placeholder="خالی بگذارید تا تغییر نکند">
                     </div>
                  </div>
               </div>

               <!-- بخش اطلاعات حمل و نقل ووکامرس -->
               <div class="neo-form-section" style="margin-top: 30px;">
                  <h3> اطلاعات حمل و نقل</h3>
                  <div class="neo-form-grid">
                     <div class="neo-form-group">
                        <label>نام گیرنده</label>
                        <input type="text" name="shipping_first_name" class="neo-input"
                           value="<?php echo esc_attr($shipping_first_name); ?>">
                     </div>
                     <div class="neo-form-group">
                        <label>نام خانوادگی گیرنده</label>
                        <input type="text" name="shipping_last_name" class="neo-input"
                           value="<?php echo esc_attr($shipping_last_name); ?>">
                     </div>
                     <div class="neo-form-group">
                        <label>شهر</label>
                        <input type="text" name="shipping_city" class="neo-input"
                           value="<?php echo esc_attr($shipping_city); ?>">
                     </div>
                     <div class="neo-form-group">
                        <label>کد پستی</label>
                        <input type="text" name="shipping_postcode" class="neo-input"
                           value="<?php echo esc_attr($shipping_postcode); ?>">
                     </div>
                     <div class="neo-form-group" style="grid-column: 1 / -1;">
                        <label>آدرس دقیق</label>
                        <textarea name="shipping_address_1" class="neo-input"
                           rows="3"><?php echo esc_textarea($shipping_address_1); ?></textarea>
                     </div>
                  </div>
               </div>

               <div id="neo-form-message" style="display:none; margin-top:15px; padding:10px; border-radius:8px;"></div>

               <button type="submit" class="neo-btn neo-btn-primary">
                  <span class="neo-btn-text">ذخیره تغییرات</span>
                  <span class="neo-btn-loader" style="display:none;">⏳</span>
               </button>
            </form>
         </div>
         <?php
            $response_html = ob_get_clean();
            break;

        default:
            $response_html = '<p>محتوایی یافت نشد.</p>';
            break;
         }

    wp_send_json_success( array( 'html' => $response_html ) );
}
// پردازشگر ذخیره اطلاعات حساب کاربری
add_action( 'wp_ajax_neo_save_account_data', 'neo_save_account_data_handler' );
function neo_save_account_data_handler() {
    check_ajax_referer( 'neo_panel_secure_nonce', 'security' );
    
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'لطفاً وارد سایت شوید.' ) );
    }

    $user_id = get_current_user_id();

    // بروزرسانی اطلاعات پایه کاربر
    $user_data = array(
        'ID'         => $user_id,
        'first_name' => sanitize_text_field( $_POST['first_name'] ),
        'last_name'  => sanitize_text_field( $_POST['last_name'] ),
        'user_email' => sanitize_email( $_POST['user_email'] ),
    );

    // اگر رمز عبور وارد شده بود، آن را هم تغییر بده
    if ( ! empty( $_POST['new_password'] ) ) {
        $user_data['user_pass'] = $_POST['new_password'];
    }

    $user_update_result = wp_update_user( $user_data );

    if ( is_wp_error( $user_update_result ) ) {
        wp_send_json_error( array( 'message' => $user_update_result->get_error_message() ) );
    }

    // بروزرسانی متادیتای حمل و نقل ووکامرس
    update_user_meta( $user_id, 'shipping_first_name', sanitize_text_field( $_POST['shipping_first_name'] ) );
    update_user_meta( $user_id, 'shipping_last_name', sanitize_text_field( $_POST['shipping_last_name'] ) );
    update_user_meta( $user_id, 'shipping_city', sanitize_text_field( $_POST['shipping_city'] ) );
    update_user_meta( $user_id, 'shipping_postcode', sanitize_text_field( $_POST['shipping_postcode'] ) );
    update_user_meta( $user_id, 'shipping_address_1', sanitize_textarea_field( $_POST['shipping_address_1'] ) );

    wp_send_json_success( array( 'message' => 'اطلاعات با موفقیت بروزرسانی شد!' ) );
}

// دریافت جزئیات یک سفارش خاص برای مودال
add_action( 'wp_ajax_neo_get_order_details', 'neo_get_order_details_handler' );
function neo_get_order_details_handler() {
    check_ajax_referer( 'neo_panel_secure_nonce', 'security' );

    $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
    if ( ! $order_id ) {
        wp_send_json_error( array( 'message' => 'سفارش یافت نشد.' ) );
    }

    $order = wc_get_order( $order_id );
    
    // بررسی امنیتی: آیا این سفارش متعلق به کاربر فعلی است؟
    if ( ! $order || $order->get_customer_id() !== get_current_user_id() ) {
        wp_send_json_error( array( 'message' => 'شما اجازه دسترسی به این سفارش را ندارید.' ) );
    }

    ob_start(); ?>
<div class="neo-invoice">
   <h3>جزئیات سفارش #<?php echo esc_html( $order->get_order_number() ); ?></h3>
   <p class="neo-text-muted">ثبت شده در <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></p>

   <table class="neo-table" style="margin-top: 20px;">
      <thead>
         <tr>
            <th>محصول</th>
            <th>تعداد</th>
            <th>قیمت کل</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ( $order->get_items() as $item_id => $item ) : 
                    $product = $item->get_product();
                ?>
         <tr>
            <td><?php echo esc_html( $item->get_name() ); ?></td>
            <td><?php echo esc_html( $item->get_quantity() ); ?></td>
            <td><?php echo wp_kses_post( wc_price( $order->get_line_total( $item, true, true ) ) ); ?></td>
         </tr>
         <?php endforeach; ?>
      </tbody>
      <tfoot>
         <tr>
            <th colspan="2" style="text-align: left;">مبلغ کل سفارش:</th>
            <th><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></th>
         </tr>
      </tfoot>
   </table>
</div>
<?php
    $html = ob_get_clean();
    wp_send_json_success( array( 'html' => $html ) );
}

// پردازش درخواست شارژ کیف پول
add_action( 'wp_ajax_neo_process_recharge', 'neo_process_recharge_handler' );
// پردازش درخواست شارژ و انتقال به درگاه بانک
function neo_process_recharge_handler() {
    check_ajax_referer( 'neo_panel_secure_nonce', 'security' );

    $amount  = isset( $_POST['amount'] ) ? intval( $_POST['amount'] ) : 0;
    $gateway = isset( $_POST['gateway'] ) ? sanitize_text_field( $_POST['gateway'] ) : '';

    if ( $amount < 1000 ) {
        wp_send_json_error( array( 'message' => 'مبلغ شارژ باید بیشتر از 1,000 تومان باشد.' ) );
    }

    if ( empty( $gateway ) ) {
        wp_send_json_error( array( 'message' => 'لطفاً یک درگاه پرداخت انتخاب کنید.' ) );
    }

    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_send_json_error( array( 'message' => 'سیستم فروشگاه (ووکامرس) فعال نیست.' ) );
    }

    // آیدی محصول مخفی "شارژ کیف پول" را اینجا وارد کنید (مهم)
    $recharge_product_id = 123; // <--- این عدد را به آیدی محصول خود تغییر دهید

    // خالی کردن سبد خرید (برای اینکه کاربر همزمان محصولات دیگر را با شارژ کیف پول نخرد)
    WC()->cart->empty_cart();

    // تنظیم درگاهی که کاربر در پنل انتخاب کرده به عنوان درگاه پیش‌فرض صفحه پرداخت
    WC()->session->set( 'chosen_payment_method', $gateway );

    // افزودن محصول به سبد خرید به همراه متای مبلغ دلخواه کاربر
    $cart_item_data = array( 'custom_wallet_recharge_amount' => $amount );
    WC()->cart->add_to_cart( $recharge_product_id, 1, 0, array(), $cart_item_data );

    // ارسال موفقیت آمیز و هدایت کاربر مستقیماً به صفحه تسویه حساب (Checkout)
    wp_send_json_success( array(
        'message'      => 'در حال انتقال به صفحه پرداخت...',
        'redirect_url' => wc_get_checkout_url()
    ) );
}

function neo_get_dashboard_html() {
    $current_user = wp_get_current_user();
    $first_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;

    // آمار ووکامرس
    $customer_orders = wc_get_orders(array('customer' => $current_user->ID, 'limit' => -1));
    $total_orders = count($customer_orders);
    $processing_orders = 0;
    $completed_orders = 0;
    foreach ($customer_orders as $order) {
        $status = $order->get_status();
        if ($status === 'processing' || $status === 'on-hold') $processing_orders++;
        elseif ($status === 'completed') $completed_orders++;
    }
    $wallet_balance = get_user_meta($current_user->ID, 'neo_wallet_balance', true) ?: 0;

    ob_start(); ?>
<div class="neo-dashboard-welcome neo-fade-in">
   <h2> سلام <?php echo esc_html($first_name); ?> خوش آمدید.</h2>
   <p>در اینجا میتوانید نگاهی سریع به وضعیت حساب کاربری خود داشته باشید !</p>
</div>
<div class="neo-dashboard-stats neo-fade-in">
   <div class="neo-stat-card"><span>کل سفارش‌ها: <?php echo $total_orders; ?></span></div>
   <div class="neo-stat-card"><span>در حال پردازش: <?php echo $processing_orders; ?></span></div>
   <div class="neo-stat-card"><span>تکمیل شده: <?php echo $completed_orders; ?></span></div>
   <div class="neo-stat-card"><span>کیف پول: <?php echo wc_price($wallet_balance); ?></span></div>
   <div class="neo-body-overlay"></div>

</div>
<?php
    return ob_get_clean();
}
// ==========================================
// منطق اختصاصی ووکامرس برای شارژ کیف پول
// ==========================================

// 1. تغییر قیمت محصول شارژ در سبد خرید به مبلغ دلخواه کاربر
add_action('woocommerce_before_calculate_totals', 'neo_set_custom_wallet_price', 10, 1);
function neo_set_custom_wallet_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_wallet_recharge_amount'])) {
            $cart_item['data']->set_price($cart_item['custom_wallet_recharge_amount']);
        }
    }
}

// 2. افزایش موجودی کاربر به محض تکمیل پرداخت موفق سفارش
// گوش دادن به ووکامرس: اگر پرداختی موفق بود، کیف پول را شارژ کن!
add_action( 'woocommerce_payment_complete', 'neo_wallet_recharge_payment_complete' );
add_action( 'woocommerce_order_status_completed', 'neo_wallet_recharge_payment_complete' );
add_action( 'woocommerce_order_status_processing', 'neo_wallet_recharge_payment_complete' );

function neo_wallet_recharge_payment_complete( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    // بررسی از طریق meta (مسیر اصلی)
    $is_recharge = $order->get_meta( '_is_neo_wallet_recharge' );
    $is_done     = $order->get_meta( '_neo_wallet_recharge_done' );

    // اگر meta نداره، بررسی از طریق product_id (سازگاری با مسیر قدیمی)
    if ( $is_recharge !== 'yes' ) {
        $recharge_product_id = 10667;
        foreach ( $order->get_items() as $item ) {
            if ( (int) $item->get_product_id() === $recharge_product_id ) {
                $is_recharge = 'yes';
                break;
            }
        }
    }

    // اگر سفارش شارژ نیست یا قبلاً پردازش شده، خروج
    if ( $is_recharge !== 'yes' || $is_done === 'yes' ) {
        return;
    }

    $user_id = $order->get_customer_id();
    $amount  = floatval( $order->get_total() );

    if ( ! $user_id || $amount <= 0 ) return;

    // واریز از طریق تابع هسته‌ای (ثبت تراکنش + آپدیت موجودی)
    neo_wallet_add_transaction(
        $user_id,
        $amount,
        'credit',
        'شارژ حساب از طریق درگاه پرداخت (سفارش #' . $order_id . ')'
    );

    // علامت‌گذاری برای جلوگیری از شارژ مجدد
    $order->update_meta_data( '_neo_wallet_recharge_done', 'yes' );

    // تکمیل اتوماتیک سفارش
    if ( $order->get_status() !== 'completed' ) {
        $order->update_status( 'completed', 'شارژ کیف پول کاربری موفقیت آمیز بود.' );
    }
    $order->save();
}

