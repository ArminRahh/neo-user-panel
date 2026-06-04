jQuery(document).ready(function ($) {

    // متغیر سراسری neoVendorAjax قبلاً در vendor-core.php لود شده است

    $('#neo-vendor-register-form').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = $('#neo-vendor-submit-btn');
        let messageDiv = $('#neo-vendor-form-message');

        // --- اعتبارسنجی سمت کاربر ---
        let nationalCode = form.find('input[name="national_code"]').val();
        if (!/^\d{10}$/.test(nationalCode)) {
            messageDiv.html('<div class="neo-alert neo-alert-danger">کد ملی وارد شده معتبر نیست. باید ۱۰ رقم باشد.</div>');
            return;
        }

        let iban = form.find('input[name="iban"]').val();
        if (!/^\d{24}$/.test(iban)) {
            messageDiv.html('<div class="neo-alert neo-alert-danger">شماره شبا وارد شده معتبر نیست. باید ۲۴ رقم باشد.</div>');
            return;
        }

        // --- آماده‌سازی داده‌ها برای ارسال فایل ---
        let formData = new FormData(this);
        formData.append('action', 'neo_vendor_register_submit');
        formData.append('security', neoVendorAjax.nonce);

        submitBtn.prop('disabled', true).text('در حال ارسال مدارک...');
        messageDiv.html('');

        // --- ارسال درخواست AJAX ---
        $.ajax({
            url: neo_ajax_obj.ajax_url, // آدرسی که در فایل اصلی پاس داده بودید
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    // نمایش پیام موفقیت (میتوانید از SweetAlert یا Toast استفاده کنید)
                    alert(response.data.message);
                    // رفرش صفحه برای نمایش وضعیت "در انتظار تایید"
                    location.reload();
                } else {
                    alert(response.data.message || 'خطایی رخ داد.');
                    submitBtn.prop('disabled', false).html(originalButtonText);
                }
            },
            error: function () {
                alert('خطا در ارتباط با سرور.');
                submitBtn.prop('disabled', false).html(originalButtonText);
            }
        });
    });

    $(document).on('click', '#neo-btn-open-product-vendor', function (e) {
        e.preventDefault();

        // لیست تیکت‌ها را مخفی کن و فرم ثبت تیکت را نمایش بده
        $('#neo-dashboard-vendor').fadeOut(200, function () {
            $('#neo-add-product-vendor').fadeIn(200);
        });
    });
    $(document).on('click', '#neo-order-list-vendor', function (e) {
        e.preventDefault();

        // لیست تیکت‌ها را مخفی کن و فرم ثبت تیکت را نمایش بده
        $('#neo-add-product-vendor').fadeOut(200, function () {
            $('#neo-dashboard-vendor').fadeIn(200);
        });
    });


    // واکشی لیست محصولات
    function loadSiteProducts() {
        $.ajax({
            url: neo_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'neo_vendor_search_products',
                nonce: neo_ajax_obj.nonce
            },
            beforeSend: function () {
                $('#neo-search-results-body').html('<tr><td colspan="4">در حال بارگذاری...</td></tr>');
            },
            success: function (response) {
                if (response.success) {
                    renderProducts(response.data.products);
                } else {
                    $('#products-table-body').html('<tr><td colspan="4">' + response.data.message + '</td></tr>');
                }
            },
            error: function () {
                $('#products-table-body').html('<tr><td colspan="4">خطا در بارگذاری محصولات</td></tr>');
            }
        });
    }

    // رندر محصولات در جدول
    function renderProducts(products) {
        let html = '';

        if (products.length === 0) {
            html = '<tr><td colspan="4">محصولی یافت نشد</td></tr>';
        } else {
            products.forEach(function (product) {
                let buttonHtml = product.is_added
                    ? '<button class="neo-btn-secondary" disabled>اضافه شده</button>'
                    : '<button class="neo-btn-primary add-product-btn" data-product-id="' + product.id + '">افزودن محصول</button>';

                html += '<tr>' +
                    '<td>' + product.id + '</td>' +
                    '<td>' + product.title + '</td>' +
                    '<td>' + product.date + '</td>' +
                    '<td>' + buttonHtml + '</td>' +
                    '</tr>';
            });
        }

        $('#neo-search-results-body').html(html);
    }

    // بارگذاری اولیه
    loadSiteProducts();

    // افزودن محصول
    $(document).on('click', '.add-product-btn', function () {
        let btn = $(this);
        let productId = btn.data('product-id');

        $.ajax({
            url: neo_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'neo_vendor_add_product',
                nonce: neo_ajax_obj.nonce,
                product_id: productId
            },
            beforeSend: function () {
                btn.prop('disabled', true).text('در حال افزودن...');
            },
            success: function (response) {
                if (response.success) {
                    btn.removeClass('neo-btn-primary').addClass('neo-btn-secondary').text('اضافه شده');
                } else {
                    alert(response.data.message);
                    btn.prop('disabled', false).text('افزودن محصول');
                }
            },
            error: function () {
                alert('خطا در افزودن محصول');
                btn.prop('disabled', false).text('افزودن محصول');
            }
        });
    });




    // function fetchVendorProducts(searchTerm = '', categoryId = '') {
    //     // Show loading indicator
    //     const loadingDiv = $('#neo-search-loading');
    //     const resultsBody = $('#neo-search-results-body');

    //     loadingDiv.show();
    //     resultsBody.html(''); // Clear previous results

    //     $.ajax({
    //         url: neo_ajax_obj.ajax_url,
    //         type: 'POST',
    //         data: {
    //             action: 'neo_vendor_search_products',
    //             nonce: neo_ajax_obj.nonce,
    //             search_term: searchTerm,
    //             category_id: categoryId
    //         },
    //         success: function (response) {
    //             console.log('Full response:', response);
    //             console.log('response.success:', response.success);
    //             console.log('response.data:', response.data);
    //             console.log('response.data.html:', response.data ? response.data.html : 'NO DATA');

    //             $('#neo-search-loading').hide();
    //             $('#neo-search-results-body').css('opacity', '1');

    //             if (response.success) {
    //                 $('#neo-search-results-body').html(response.data.html);
    //             } else {
    //                 $('#neo-search-results-body').html('<tr><td colspan="5" style="text-align:center; color:red; padding: 20px;">' + (response.data || 'محصولی یافت نشد.') + '</td></tr>');
    //             }
    //         },

    //         error: function (xhr, status, error) {
    //             console.error("AJAX Error:", status, error);
    //             resultsBody.html('<tr><td colspan="5">خطای ارتباط با سرور. لطفا اتصال اینترنت خود را بررسی کنید.</td></tr>');
    //         },
    //         complete: function () {
    //             // Hide loading indicator
    //             loadingDiv.hide();
    //             console.log('8. AJAX call is complete.');
    //         }
    //     });
    // }


    // // ۱. فراخوانی تابع در لحظه لود صفحه (نمایش همه محصولات در ابتدا)
    // fetchVendorProducts();

    // // ۲. فیلتر خودکار با تغییر دسته‌بندی
    // $('#neo-search-category').on('change', function () {
    //     fetchVendorProducts();
    // });

    // // ۳. فیلتر خودکار با تایپ کلمه کلیدی (با تاخیر ۵۰۰ میلی‌ثانیه برای جلوگیری از هنگ کردن سرور)
    // var typingTimer;
    // var doneTypingInterval = 500;

    // $('#neo-search-keyword').on('keyup', function () {
    //     clearTimeout(typingTimer);
    //     typingTimer = setTimeout(function () {
    //         fetchVendorProducts();
    //     }, doneTypingInterval);
    // });

    // $('#neo-search-keyword').on('keydown', function () {
    //     clearTimeout(typingTimer);
    // });

    // // ==========================================
    // // عملیات افزودن به لیست
    // // ==========================================
    // $(document).on('click', '.neo-add-to-my-list', function (e) {
    //     e.preventDefault();
    //     var button = $(this);
    //     var productId = button.data('product-id');

    //     if (button.hasClass('loading')) return;

    //     var originalText = button.text();
    //     button.addClass('loading').text('در حال افزودن...');
    //     button.css('opacity', '0.7');

    //     $.ajax({
    //         url: neo_ajax_obj.ajax_url,
    //         type: 'POST',
    //         data: {
    //             action: 'neo_vendor_add_product',
    //             product_id: productId
    //         },
    //         success: function (response) {
    //             if (response.success) {
    //                 button.text('اضافه شد').removeClass('neo-add-to-my-list loading').addClass('added');
    //                 button.css({ 'background-color': '#28a745', 'color': '#fff', 'border-color': '#28a745', 'opacity': '1', 'cursor': 'default' });
    //                 button.prop('disabled', true);
    //             } else {
    //                 alert(response.data || 'خطایی رخ داده است.');
    //                 button.removeClass('loading').text(originalText).css('opacity', '1');
    //             }
    //         },
    //         error: function () {
    //             alert('خطای ارتباط با سرور.');
    //             button.removeClass('loading').text(originalText).css('opacity', '1');
    //         }
    //     });
    // });

});




