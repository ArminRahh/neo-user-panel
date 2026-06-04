jQuery(document).ready(function ($) {

    // وقتی روی دکمه "ثبت تیکت جدید" کلیک شد
    $(document).on('click', '#neo-show-create-ticket', function (e) {
        e.preventDefault();

        // لیست تیکت‌ها را مخفی کن و فرم ثبت تیکت را نمایش بده
        $('#neo-ticket-list-view').fadeOut(200, function () {
            $('#neo-ticket-create-view').fadeIn(200);
        });
    });

    // وقتی روی دکمه "بازگشت به لیست" کلیک شد (در صورتی که این دکمه را در HTML دارید)
    $(document).on('click', '#neo-back-to-tickets', function (e) {
        e.preventDefault();

        // فرم را مخفی کن و لیست تیکت‌ها را برگردان
        $('#neo-ticket-create-view').fadeOut(200, function () {
            $('#neo-ticket-list-view').fadeIn(200);

            // خالی کردن فرم بعد از بازگشت (اختیاری)
            if ($('#neo-new-ticket-form').length) {
                $('#neo-new-ticket-form')[0].reset();
            }
        });
    });
    $(document).on('submit', '#neo-create-ticket-form', function (e) {
        e.preventDefault();

        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var messageBox = form.find('#neo-ticket-message'); // گرفتن باکس پیام
        var formData = new FormData(this);

        formData.append('action', 'neo_submit_new_ticket');

        // در صورت نیاز به استفاده از نانس امنیتی
        if (typeof neo_ajax_obj !== 'undefined' && neo_ajax_obj.nonce) {
            formData.append('security', neo_ajax_obj.nonce);
        }

        // پنهان کردن پیام قبلی در شروع کار
        messageBox.hide().removeClass('success error').text('');
        submitBtn.prop('disabled', true).text('در حال ارسال...');

        $.ajax({
            url: neo_ajax_obj.ajax_url, // استفاده از آدرس داینامیک
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    // نمایش پیام موفقیت با رنگ سبز
                    messageBox.css({ 'background-color': '#d4edda', 'color': '#155724', 'border': '1px solid #c3e6cb' })
                        .text('تیکت شما با موفقیت ثبت شد!')
                        .fadeIn();

                    form[0].reset();

                    // بعد از 2 ثانیه، مخفی کردن پیام و بازگشت به لیست
                    setTimeout(function () {
                        $('#neo-ticket-create-view').fadeOut(200, function () {
                            $('#neo-ticket-list-view').fadeIn(200);
                            messageBox.hide(); // مخفی کردن پیام برای دفعه بعد
                        });
                    }, 2000);

                } else {
                    // نمایش پیام خطا با رنگ قرمز
                    messageBox.css({ 'background-color': '#f8d7da', 'color': '#721c24', 'border': '1px solid #f5c6cb' })
                        .text('خطا: ' + (response.data || 'مشکلی پیش آمد'))
                        .fadeIn();
                }
            },
            error: function () {
                // خطای ارتباط با سرور
                messageBox.css({ 'background-color': '#f8d7da', 'color': '#721c24', 'border': '1px solid #f5c6cb' })
                    .text('خطایی در ارتباط با سرور رخ داد.')
                    .fadeIn();
            },
            complete: function () {
                submitBtn.prop('disabled', false).text('ارسال تیکت');
            }
        });
    });

});
jQuery(document).ready(function ($) {

    // کلیک روی دکمه مشاهده تیکت (دریافت چت با اجاکس)
    $(document).on('click', '.neo-btn-single-view-ticket', function (e) {
        e.preventDefault();
        var ticketId = $(this).data('ticket-id');
        var $btn = $(this);
        var originalText = $btn.html();

        // تغییر وضعیت دکمه به حالت لودینگ
        $btn.html('کمی صبر...');
        $btn.prop('disabled', true);

        $.ajax({
            url: neo_ajax_obj.ajax_url, // آدرسی که قبلا در wp_localize_script پاس داده‌اید
            type: 'POST',
            data: {
                action: 'neo_load_single_ticket',
                ticket_id: ticketId
            },
            success: function (response) {
                if (response.success) {
                    // پنهان کردن لیست تیکت‌ها
                    $('#neo-ticket-list-view').fadeOut(200, function () {
                        // قرار دادن محتوای چت در دیو مربوطه و نمایش آن
                        $('#neo-ticket-single-view').html(response.data.html).fadeIn(200);
                    });
                } else {
                    alert(response.data.message || 'خطا در دریافت اطلاعات تیکت');
                }
            },
            error: function () {
                alert('خطای ارتباط با سرور.');
            },
            complete: function () {
                // بازگرداندن دکمه به حالت اولیه
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });

    // دکمه بازگشت از صفحه چت تیکت به لیست تیکت‌ها
    $(document).on('click', '#neo-back-from-single', function (e) {
        e.preventDefault();
        $('#neo-ticket-single-view').fadeOut(200, function () {
            $(this).empty(); // پاک کردن محتوای قبلی برای جلوگیری از تداخل
            $('#neo-ticket-list-view').fadeIn(200);
        });
    });
    // $(document).on('click', '.neo-btn-view-ticket', function (e) {
    //     e.preventDefault();
    //     var ticketId = $(this).data('ticket-id');
    //     var $btn = $(this);
    //     var originalText = $btn.html();

    //     // تغییر وضعیت دکمه به حالت لودینگ
    //     $btn.html('کمی صبر...');
    //     $btn.prop('disabled', true);

    //     $.ajax({
    //         url: neo_ajax_obj.ajax_url,
    //         type: 'POST',
    //         data: {
    //             action: 'neo_load_single_ticket',
    //             ticket_id: ticketId
    //         },
    //         success: function (response) {
    //             if (response.success) {
    //                 // پنهان کردن لیست تیکت‌ها
    //                 $('#neo-ticket-list-view').fadeOut(200, function () {
    //                     // قرار دادن محتوای چت در دیو مربوطه و نمایش آن
    //                     $('#neo-ticket-single-admin-view').html(response.data.html).fadeIn(200);
    //                 });
    //             } else {
    //                 alert(response.data.message || 'خطا در دریافت اطلاعات تیکت');
    //             }
    //         },
    //         error: function () {
    //             alert('خطای ارتباط با سرور.');
    //         },
    //         complete: function () {
    //             // بازگرداندن دکمه به حالت اولیه
    //             $btn.html(originalText);
    //             $btn.prop('disabled', false);
    //         }
    //     });
    // });

    // // دکمه بازگشت از صفحه چت تیکت به لیست تیکت‌ها
    // $(document).on('click', '#neo-back-from-single', function (e) {
    //     e.preventDefault();
    //     $('#neo-ticket-single-admin-view').fadeOut(200, function () {
    //         $(this).empty(); // پاک کردن محتوای قبلی برای جلوگیری از تداخل
    //         $('#neo-ticket-list-view').fadeIn(200);
    //     });
    // });

});
// کلیک روی دکمه مشاهده تیکت (دریافت چت با اجاکس)
