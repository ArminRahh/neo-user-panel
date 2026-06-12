jQuery(document).ready(function ($) {
  let searchTimer = null;
  let loadRetryTimer = null;

  function getResultsBody() {
    return $("#neo-search-results-body");
  }

  function getPaginationBox() {
    return $("#neo-pagination-container");
  }

  function getKeyword() {
    const $input = $("#neo-search-keyword");
    return $input.length ? $.trim($input.val()) : "";
  }

  function getCategoryId() {
    const $select = $("#neo-filter-category");
    return $select.length ? $select.val() : "";
  }

  function renderLoading() {
    const $container = getResultsBody();
    if (!$container.length) return;

    $container.html(`
      <tr class="neo-loading-row">
        <td colspan="5" style="text-align:center; padding:20px;">
          در حال دریافت لیست محصولات...
        </td>
      </tr>
    `);
  }

  function renderError(message) {
    const $container = getResultsBody();
    if (!$container.length) return;

    $container.html(`
      <tr>
        <td colspan="5" style="text-align:center; padding:20px; color:#b91c1c;">
          ${message}
        </td>
      </tr>
    `);
  }

  function loadAllSiteProducts(page = 1) {
    const $container = getResultsBody();
    const $pagination = getPaginationBox();

    if (!$container.length) return;

    renderLoading();

    $.ajax({
      url: neo_ajax_obj.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "neo_vendor_search_products",
        keyword: getKeyword(),
        category_id: getCategoryId(),
        page: page,
        nonce: neo_ajax_obj.nonce,
      },
      success: function (response) {
        if (response && response.success && response.data) {
          if (typeof response.data.html !== "undefined") {
            $container.html(response.data.html);
          } else {
            renderError("خروجی محصولات از سرور دریافت نشد.");
          }

          // pagination
          if (
            response.data.pagination !== undefined &&
            response.data.pagination !== null &&
            response.data.pagination !== ""
          ) {
            $pagination.html(response.data.pagination);
          } else {
            $pagination.html("");
          }
        } else {
          renderError(
            response?.data?.message || "خطا در دریافت داده‌ها از سرور.",
          );
          $pagination.html("");
        }
      },
      error: function () {
        renderError("خطا در ارتباط با سرور.");
        $pagination.html("");
      },
    });
  }

  function tryLoadWhenReady(page = 1) {
    let tries = 0;
    const maxTries = 25;

    if (loadRetryTimer) {
      clearInterval(loadRetryTimer);
    }

    loadRetryTimer = setInterval(function () {
      tries++;

      if ($("#neo-search-results-body").length) {
        clearInterval(loadRetryTimer);
        loadRetryTimer = null;
        loadAllSiteProducts(page);
      } else if (tries >= maxTries) {
        clearInterval(loadRetryTimer);
        loadRetryTimer = null;
      }
    }, 200);
  }

  // لود اولیه اگر تب از اول داخل DOM باشد
  if ($("#neo-search-results-body").length) {
    loadAllSiteProducts(1);
  } else {
    tryLoadWhenReady(1);
  }

  // هر بار ورود به تب افزودن محصول، دوباره لود کن
  $(document).on("click", '[data-tab="vendor-add-product"]', function () {
    setTimeout(function () {
      if ($("#neo-search-results-body").length) {
        loadAllSiteProducts(1);
      } else {
        tryLoadWhenReady(1);
      }
    }, 300);
  });

  // جستجو با debounce
  $(document).on("input", "#neo-search-keyword", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      loadAllSiteProducts(1);
    }, 400);
  });

  // تغییر دسته‌بندی
  $(document).on("change", "#neo-filter-category", function () {
    loadAllSiteProducts(1);
  });

  // کلیک pagination
  $(document).on("click", ".neo-pagination a", function (e) {
    e.preventDefault();

    const page = $(this).data("page") || $(this).attr("data-page") || 1;
    loadAllSiteProducts(parseInt(page, 10));
  });

  // افزودن محصول
  $(document).on("click", ".add-product-btn", function () {
    const $btn = $(this);
    const productId = $btn.data("product-id");

    if (!productId) {
      alert("آیدی محصول نامعتبر است.");
      return;
    }

    $btn.prop("disabled", true).text("در حال افزودن...");

    $.ajax({
      url: neo_ajax_obj.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "neo_vendor_add_product",
        product_id: productId,
        nonce: neo_ajax_obj.nonce,
      },
      success: function (response) {
        if (response && response.success) {
          $btn
            .removeClass("neo-btn-primary")
            .addClass("neo-btn-secondary")
            .text("اضافه شده")
            .prop("disabled", true);
        } else {
          alert(response?.data?.message || "خطا در افزودن محصول.");
          $btn.prop("disabled", false).text("افزودن محصول");
        }
      },
      error: function () {
        alert("خطا در ارتباط با سرور.");
        $btn.prop("disabled", false).text("افزودن محصول");
      },
    });
  });

  /* ==========================================
     بخش ثبت نام فروشگاه (نمایش فوری کادر در انتظار تایید شما)
     ========================================== */
  $(document).on("submit", "#neo-vendor-register-form", function (e) {
    e.preventDefault(); // جلوگیری قطعی از رفرش ناگهانی صفحه

    const $form = $(this);
    const $submitBtn = $("#neo-vendor-submit-btn");
    const $messageDiv = $("#neo-vendor-form-message");
    const $headerDiv = $(".neo-vendor-header"); // هدر فرم شما

    // ایجاد FormData برای پشتیبانی از ارسال فایل
    const formData = new FormData(this);

    // تغییر حالت دکمه و پاک کردن پیام‌های قبلی
    $submitBtn.prop("disabled", true).text("در حال ارسال و آپلود مدارک...");
    $messageDiv.html("").removeClass("neo-alert-danger neo-alert-success");

    $.ajax({
      url: neo_ajax_obj.ajax_url,
      type: "POST",
      data: formData,
      contentType: false, // الزامی برای ارسال فایل
      processData: false, // الزامی برای ارسال فایل
      dataType: "json",
      success: function (response) {
        if (response && response.success) {
          // ۱. پنهان کردن فرم و هدر آن به صورت انیمیشنی ملایم
          $form.slideUp(400);
          $headerDiv.slideUp(400, function () {
            // ۲. قرار دادن دقیق ساختار و استایل کلاس هشدار زرد رنگ (pending) شما به جای کل بدنه فرم
            $(".neo-vendor-register-container").html(`
                <div class="neo-alert neo-alert-warning" style="display:none; padding: 40px; text-align: center; border-radius: 12px; margin: 20px 0;">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor" style="margin-bottom: 15px; color: #d97706;">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                    <h3 style="color: #b45309; margin-bottom: 10px; font-weight: bold;">ثبت نام با موفقیت انجام شد</h3>
                    <p style="font-size: 15px; line-height: 1.8; color: #451a03; margin: 0;">
                        درخواست شما با موفقیت ثبت شده و در انتظار تایید مدیریت است.
                    </p>
                </div>
            `);

            // نمایش کادر با انیمیشن ورود افکت‌دار
            $(".neo-alert-warning").slideDown(400);
          });
        } else {
          // نمایش دقیق خطای ارسالی از PHP بدون پنهان کردن فرم
          const errorMsg = response.data?.message || "خطایی رخ داده است.";
          $messageDiv.html(
            '<div class="neo-alert neo-alert-danger">' + errorMsg + "</div>",
          );
          $submitBtn.prop("disabled", false).text("ثبت درخواست و ارسال مدارک");
        }
      },
      error: function () {
        $messageDiv.html(
          '<div class="neo-alert neo-alert-danger">اختلالی در ارتباط با سرور رخ داده است. لطفاً ارتباط خود را بررسی کنید.</div>',
        );
        $submitBtn.prop("disabled", false).text("ثبت درخواست و ارسال مدارک");
      },
    });
  });
});
