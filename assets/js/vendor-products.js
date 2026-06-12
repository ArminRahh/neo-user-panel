jQuery(document).ready(function ($) {
  // ۱. عملیات ذخیره قیمت و موجودی جدید
  $(document).on("click", ".neo-save-product-ajax", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var itemId = $btn.data("item-id");
    var $row = $btn.closest("tr");
    var priceVal = $row.find(".neo-vendor-price-input").val();
    var stockVal = $row.find(".neo-vendor-stock-select").val();

    $btn.prop("disabled", true).css("opacity", "0.5");

    $.ajax({
      url: neo_ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "neo_save_vendor_product_changes",
        item_id: itemId,
        price: priceVal,
        stock: stockVal,
        nonce: neo_ajax_object.nonce,
      },
      success: function (response) {
        if (response.success) {
          var newStatus = response.data.status;
          var $statusCell = $row.find(".col-status");

          if (newStatus === "active") {
            $statusCell.html(
              '<span class="neo-badge active-badge">فعال</span>',
            );
          } else {
            $statusCell.html(
              '<span class="neo-badge inactive-badge" title="برای فعال‌سازی ابتدا قیمت معتبری وارد کنید">غیرفعال</span>',
            );
          }
          alert("تغییرات با موفقیت ذخیره شد.");
        } else {
          alert("خطا: " + response.data);
        }
        $btn.prop("disabled", false).css("opacity", "1");
      },
      error: function () {
        alert("خطایی در ارتباط با سرور رخ داد.");
        $btn.prop("disabled", false).css("opacity", "1");
      },
    });
  });

  // ۲. عملیات حذف داینامیک محصول از لیست فروشنده
  $(document).on("click", ".neo-delete-product-ajax", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var itemId = $btn.data("item-id");
    var $row = $btn.closest("tr");

    if (confirm("آیا از حذف این محصول از لیست فروشندگی خود مطمئن هستید؟")) {
      $btn.prop("disabled", true);

      $.ajax({
        url: neo_ajax_object.ajax_url,
        type: "POST",
        data: {
          action: "neo_delete_vendor_product",
          item_id: itemId,
          nonce: neo_ajax_object.nonce,
        },
        success: function (response) {
          if (response.success) {
            $row.fadeOut(400, function () {
              $(this).remove();
              if ($(".neo-vendor-table tbody tr").length === 0) {
                $(".neo-vendor-table tbody").html(
                  '<tr><td colspan="6" class="neo-empty-cell">هیچ محصولی در لیست شما یافت نشد.</td></tr>',
                );
              }
            });
          } else {
            alert("حذف ناموفق بود: " + response.data);
            $btn.prop("disabled", false);
          }
        },
        error: function () {
          alert("خطا در سرور.");
          $btn.prop("disabled", false);
        },
      });
    }
  });
});
jQuery(document).ready(function ($) {
  // فرمت‌دهی خودکار هنگام تایپ قیمت
  $(document).on("input", ".neo-price-format", function () {
    // حذف تمام کاراکترهای غیر عددی به جز کاما
    var value = $(this)
      .val()
      .replace(/[^0-9]/g, "");

    if (value !== "") {
      // تبدیل به عدد و فرمت‌دهی ۳ رقمی
      var formattedValue = parseInt(value, 10).toLocaleString("en-US");
      $(this).val(formattedValue);
    } else {
      $(this).val("");
    }
  });
});

jQuery(document).ready(function ($) {
  // گوش دادن به تغییرات فیلد جستجو
  $(document).on("input", "#neo-product-search", function () {
    var value = $(this).val().toLowerCase().trim();

    // پیدا کردن تمام ردیف‌های جدول محصولات
    var $rows = $(".neo-vendor-table tbody tr.neo-product-row");

    $rows.each(function () {
      // پیدا کردن متن نام محصول در ستون دوم
      var productName = $(this).find(".col-name strong").text().toLowerCase();

      if (productName.indexOf(value) > -1) {
        $(this).show(); // نمایش اگر کلمه پیدا شد
      } else {
        $(this).hide(); // مخفی کردن اگر کلمه پیدا نشد
      }
    });

    // نمایش پیام "نتیجه‌ای یافت نشد"
    var visibleCount = $(
      ".neo-vendor-table tbody tr.neo-product-row:visible",
    ).length;
    if (visibleCount === 0) {
      if ($("#no-results-msg").length === 0) {
        $(".neo-vendor-table tbody").append(
          '<tr id="no-results-msg"><td colspan="6" style="text-align:center;padding:20px;">محصولی با این نام یافت نشد.</td></tr>',
        );
      }
    } else {
      $("#no-results-msg").remove();
    }
  });
});
