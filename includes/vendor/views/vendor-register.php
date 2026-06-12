<?php
if (! defined('ABSPATH')) exit;

// دریافت وضعیت فروشنده از متد استاتیک دیتابیس
$vendor_status = Neo_Vendor_Core::get_vendor_status();

// بررسی ادمین
$is_admin = current_user_can('manage_options');
?>

<div class="neo-vendor-register-container">

    <?php if ($is_admin) : ?>

        <div class="neo-alert neo-alert-info">
            شما با نقش مدیر وارد شده‌اید. مدیران نیازی به ثبت درخواست فروشندگی ندارند.
        </div>

    <?php elseif ($vendor_status === 'approved') : ?> <!-- اصلاح شد: تطابق وضعیت تایید شده دیتابیس با کلاس فعال شما -->

        <div class="neo-alert neo-alert-success">
            حساب فروشندگی شما فعال است و می‌توانید از منوهای فروشنده استفاده کنید.
        </div>

    <?php elseif ($vendor_status === 'pending') : ?>

        <div class="neo-alert neo-alert-warning">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
                10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
            درخواست شما با موفقیت ثبت شده و در انتظار تایید مدیریت است.
        </div>

    <?php elseif ($vendor_status === 'rejected') : ?>

        <div class="neo-alert neo-alert-danger">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 
                10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 
                12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 
                8.41 7 12 10.59 15.59 7 17 8.41 
                13.41 12 17 15.59z" />
            </svg>
            متاسفانه درخواست فروشندگی شما تایید نشد. جهت کسب اطلاعات بیشتر با پشتیبانی تماس بگیرید.
        </div>

    <?php else : ?>

        <div class="neo-vendor-header">
            <h3>فرم درخواست فروشندگی</h3>
            <p>برای شروع فروش در پلتفرم ما، لطفاً اطلاعات زیر را با دقت تکمیل نمایید.</p>
        </div>

        <form id="neo-vendor-register-form" method="post" enctype="multipart/form-data">

            <div class="neo-form-row">
                <div class="neo-form-group">
                    <label>نام فروشگاه <span class="neo-required">*</span></label>
                    <input type="text" name="store_name" required placeholder="مثال: فروشگاه اتونیک">
                </div>

                <div class="neo-form-group">
                    <label>آدرس اینترنتی (URL) فروشگاه</label>
                    <input type="url" name="store_url" placeholder="https://your-domain.com">
                </div>
            </div>

            <div class="neo-form-row">

                <div class="neo-form-group">
                    <label>کد ملی صاحب امتیاز <span class="neo-required">*</span></label>
                    <input type="text"
                        name="national_code"
                        pattern="\d{10}"
                        maxlength="10"
                        title="کد ملی باید دقیقا ۱۰ رقم باشد"
                        required
                        placeholder="0000000000">
                </div>

                <div class="neo-form-group">
                    <label>شماره شبا (بدون IR) <span class="neo-required">*</span></label>

                    <div class="neo-input-group-ltr" style="display:flex;direction:ltr;">
                        <span class="neo-input-prefix"
                            style="padding:10px;background:#eee;border:1px solid #ddd;">IR</span>

                        <input type="text"
                            name="iban"
                            pattern="\d{24}"
                            maxlength="24"
                            title="شماره شبا باید ۲۴ رقم باشد"
                            placeholder="123456789012345678901234"
                            style="flex:1;">
                    </div>

                </div>

            </div>

            <div class="neo-form-group">
                <label>آدرس فیزیکی فروشگاه / دفتر <span class="neo-required">*</span></label>
                <textarea name="address" rows="3" required></textarea>
            </div>

            <div class="neo-form-group">
                <label>آپلود مدارک (کارت ملی / جواز کسب) <span class="neo-required">*</span></label>

                <input type="file"
                    name="documents"
                    accept=".jpg,.jpeg,.png,.pdf"
                    required>

                <small style="color:#666;">
                    فرمت‌های مجاز: JPG, PNG, PDF — حداکثر حجم: ۲ مگابایت
                </small>
            </div>

            <div id="neo-vendor-form-message"></div>

            <button type="submit"
                class="neo-btn neo-btn-primary"
                id="neo-vendor-submit-btn">
                ثبت درخواست و ارسال مدارک
            </button>

            <?php wp_nonce_field('neo_vendor_nonce', 'neo_vendor_security'); ?>
            <input type="hidden" name="action" value="neo_register_vendor">

        </form>

    <?php endif; ?>

</div>