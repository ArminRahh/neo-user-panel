jQuery(document).ready(function ($) {
  // در ادمین، وردپرس متغیر جهانی ajaxurl را به صورت پیش‌فرض دارد
  const ajaxEndPoint =
    typeof ajaxurl !== "undefined"
      ? ajaxurl
      : typeof neo_admin_obj !== "undefined"
        ? neo_admin_obj.ajax_url
        : "/wp-admin/admin-ajax.php";

  $(document).on("click", ".neo-action-btn", function (e) {
    e.preventDefault();

    const $btn = $(this);
    const action = $btn.data("action");
    const userId = $("#m-userid").val(); // آیدی کاربر که در صفحه جزئیات ادمین هست
    const $feedback = $("#m-ajax-feedback");
    const $loader = $("#ajax-loader");

    const statusMap = {
      approve: "approved",
      reject: "rejected",
    };

    if (!userId) {
      alert("خطا: آیدی کاربر یافت نشد.");
      return;
    }

    if (!confirm("آیا از تغییر وضعیت این درخواست مطمئن هستید؟")) return;

    $loader.show();
    $(".neo-action-btn").prop("disabled", true);

    $.ajax({
      url: ajaxEndPoint,
      type: "POST",
      dataType: "json",
      data: {
        action: "neo_update_vendor_status",
        user_id: userId,
        status: statusMap[action],
      },
      success: function (response) {
        $loader.hide();
        if (response && response.success) {
          $feedback.html(
            `<div style="color:#059669; background:#ecfdf5; padding:10px; border-radius:5px; margin-top:15px; border:1px solid #10b981;">${response.data.message}</div>`,
          );
          // هدایت به لیست اصلی پس از موفقیت
          setTimeout(() => {
            window.location.href = "admin.php?page=neo-vendor-manager";
          }, 1500);
        } else {
          $(".neo-action-btn").prop("disabled", false);
          const errorMsg = response?.data?.message || "خطایی رخ داد.";
          $feedback.html(
            `<div style="color:#dc2626; margin-top:10px;">${errorMsg}</div>`,
          );
        }
      },
      error: function () {
        $loader.hide();
        $(".neo-action-btn").prop("disabled", false);
        $feedback.html(
          '<div style="color:#dc2626; margin-top:10px;">خطا در ارتباط با سرور ادمین.</div>',
        );
      },
    });
  });
});
