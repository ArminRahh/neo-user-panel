document.addEventListener("DOMContentLoaded", function () {
  const menuItems = document.querySelectorAll(
    ".neo-menu-item:not(.neo-logout)",
  );
  const contentArea = document.getElementById("neo-content-area");
  const loader = document.getElementById("neo-loader");

  menuItems.forEach((item) => {
    item.addEventListener("click", function () {
      // جلوگیری از کلیک روی تب فعال
      if (this.classList.contains("active")) return;

      // تغییر کلاس فعال در منوها
      menuItems.forEach((m) => m.classList.remove("active"));
      this.classList.add("active");

      const targetTab = this.getAttribute("data-tab");

      // نمایش لودینگ و محو کردن محتوای قبلی
      loader.classList.remove("neo-loader-hidden");
      contentArea.style.opacity = "0.3";

      // ساخت بسته اطلاعاتی برای ارسال به سرور
      const formData = new FormData();
      formData.append("action", "neo_load_tab");
      formData.append("tab", targetTab);
      formData.append("security", neo_ajax_obj.nonce);

      // ارسال درخواست AJAX به روش مدرن (Fetch API)
      fetch(neo_ajax_obj.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // تزریق محتوای جدید
            contentArea.innerHTML = data.data.html;
          } else {
            contentArea.innerHTML =
              '<p style="color:red;">خطایی رخ داد. لطفا دوباره تلاش کنید.</p>';
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          contentArea.innerHTML =
            '<p style="color:red;">خطا در ارتباط با سرور.</p>';
        })
        .finally(() => {
          // مخفی کردن لودینگ و نمایش مجدد محتوا
          loader.classList.add("neo-loader-hidden");
          contentArea.style.opacity = "1";

          // ایجاد یک افکت نرم برای ورود محتوا
          contentArea.animate(
            [
              { transform: "translateY(10px)", opacity: 0 },
              { transform: "translateY(0)", opacity: 1 },
            ],
            { duration: 300, fill: "forwards" },
          );
        });
    });
  });
  // مدیریت ارسال فرم حساب کاربری (چون فرم با AJAX لود می‌شود، از Event Delegation استفاده می‌کنیم)
  contentArea.addEventListener("submit", function (e) {
    if (e.target && e.target.id === "neo-account-form") {
      e.preventDefault(); // جلوگیری از رفرش صفحه

      const form = e.target;
      const submitBtn = form.querySelector('button[type="submit"]');
      const btnText = submitBtn.querySelector(".neo-btn-text");
      const btnLoader = submitBtn.querySelector(".neo-btn-loader");
      const messageBox = document.getElementById("neo-form-message");

      // تغییر وضعیت دکمه به حالت لودینگ
      submitBtn.disabled = true;
      btnText.style.display = "none";
      btnLoader.style.display = "inline";
      messageBox.style.display = "none";

      // جمع‌آوری اطلاعات فرم
      const formData = new FormData(form);
      formData.append("action", "neo_save_account_data");
      formData.append("security", neo_ajax_obj.nonce);

      // ارسال به سرور
      fetch(neo_ajax_obj.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          messageBox.style.display = "block";
          if (data.success) {
            messageBox.style.backgroundColor = "#d1fae5";
            messageBox.style.color = "#065f46";
            messageBox.innerHTML = "✅ " + data.data.message;
            form.querySelector('input[name="new_password"]').value = ""; // پاک کردن فیلد رمز
          } else {
            messageBox.style.backgroundColor = "#fee2e2";
            messageBox.style.color = "#991b1b";
            messageBox.innerHTML = "❌ خطا: " + data.data.message;
          }
        })
        .catch((error) => {
          messageBox.style.display = "block";
          messageBox.style.backgroundColor = "#fee2e2";
          messageBox.style.color = "#991b1b";
          messageBox.innerHTML = "❌ خطای ارتباط با سرور.";
        })
        .finally(() => {
          // بازگرداندن دکمه به حالت عادی
          submitBtn.disabled = false;
          btnText.style.display = "inline";
          btnLoader.style.display = "none";
        });
    }
  });

  // --- ویژگی ۱: جستجوی زنده (Live Search) در جدول سفارشات ---
  contentArea.addEventListener("input", function (e) {
    if (e.target && e.target.id === "neo-order-search") {
      const searchTerm = e.target.value.toLowerCase();
      const tableRows = document.querySelectorAll(".neo-order-row");

      tableRows.forEach((row) => {
        const textContent = row.textContent.toLowerCase();
        if (textContent.includes(searchTerm)) {
          row.style.display = ""; // نمایش ردیف
        } else {
          row.style.display = "none"; // مخفی کردن ردیف
        }
      });
    }
  });

  // --- ویژگی ۲: باز کردن مودال فاکتور با AJAX ---
  contentArea.addEventListener("click", function (e) {
    // اگر روی دکمه "مشاهده فاکتور" کلیک شد
    if (e.target && e.target.classList.contains("neo-view-order-btn")) {
      const btn = e.target;
      const orderId = btn.getAttribute("data-order-id");
      const modal = document.getElementById("neo-order-modal");
      const modalBody = document.getElementById("neo-modal-body");

      // تغییر متن دکمه به حالت لودینگ
      const originalText = btn.innerHTML;
      btn.innerHTML = "در حال دریافت... ⏳";
      btn.disabled = true;

      // درخواست AJAX برای دریافت جزئیات
      const formData = new FormData();
      formData.append("action", "neo_get_order_details");
      formData.append("security", neo_ajax_obj.nonce);
      formData.append("order_id", orderId);

      fetch(neo_ajax_obj.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            modalBody.innerHTML = data.data.html;
            modal.style.display = "flex"; // نمایش مودال
          } else {
            alert("خطا: " + data.data.message);
          }
        })
        .catch((error) => console.error("Error:", error))
        .finally(() => {
          // بازگرداندن دکمه به حالت اولیه
          btn.innerHTML = originalText;
          btn.disabled = false;
        });
    }

    // اگر روی دکمه بستن (x) یا فضای بیرون مودال کلیک شد، مودال بسته شود
    const modal = document.getElementById("neo-order-modal");
    if (modal) {
      if (
        e.target.classList.contains("neo-modal-close") ||
        e.target === modal
      ) {
        modal.style.display = "none";
      }
    }
  });

  // --- منطق UI فرم کیف پول (مبلغ دلخواه) ---
  contentArea.addEventListener("click", function (e) {
    // 1. کلیک روی لینک "+ یا مبلغ دلخواه"
    if (e.target && e.target.id === "neo-show-custom-amount") {
      e.preventDefault();

      // مخفی کردن سلکت باکس و خالی کردن مقدار آن
      document.getElementById("neo-preset-container").style.display = "none";
      document.getElementById("neo-preset-amount").value = "";

      // نمایش فیلد مبلغ دلخواه و مخفی کردن خود لینک
      document.getElementById("neo-custom-container").style.display = "block";
      document.getElementById("neo-custom-amount").focus(); // فوکوس خودکار روی فیلد
      e.target.style.display = "none";
    }

    // 2. کلیک روی دکمه ضربدر قرمز (بستن مبلغ دلخواه)
    if (e.target && e.target.id === "neo-hide-custom-amount") {
      e.preventDefault();

      // مخفی کردن فیلد مبلغ دلخواه و خالی کردن مقدار آن
      document.getElementById("neo-custom-container").style.display = "none";
      document.getElementById("neo-custom-amount").value = "";

      // نمایش مجدد سلکت باکس و لینک آبی
      document.getElementById("neo-preset-container").style.display = "block";
      document.getElementById("neo-show-custom-amount").style.display =
        "inline";
    }
  });

  // --- منطق پاپ‌آپ خروج (Logout Modal) ---
  const logoutModal = document.getElementById("neo-logout-modal");
  const cancelLogoutBtn = document.getElementById("neo-cancel-logout");

  // استفاده از Event Delegation برای گرفتن کلیک روی دکمه خروج در سایدبار
  document.body.addEventListener("click", function (e) {
    // پیدا کردن عنصری که کلاس neo-logout-trigger دارد (حتی اگر روی آیکون داخل آن کلیک شود)
    const logoutTrigger = e.target.closest(".neo-logout-trigger");

    if (logoutTrigger) {
      e.preventDefault(); // جلوگیری از رفتار پیش‌فرض لینک

      if (logoutModal) {
        // نمایش پاپ آپ با افزودن کلاس active
        logoutModal.classList.add("active");
      }
    }
  });

  // بستن پاپ‌آپ با کلیک روی دکمه "انصراف"
  if (cancelLogoutBtn) {
    cancelLogoutBtn.addEventListener("click", function () {
      logoutModal.classList.remove("active");
    });
  }

  // بستن پاپ‌آپ در صورت کلیک روی فضای تاریک بیرون از باکس مودال
  if (logoutModal) {
    logoutModal.addEventListener("click", function (e) {
      // اگر دقیقاً روی خود overlay کلیک شد (نه فرزندان آن یعنی باکس سفید)
      if (e.target === logoutModal) {
        logoutModal.classList.remove("active");
      }
    });
  }

  // =========================================
  // Smart Back Button Logic
  // =========================================
  document.addEventListener("DOMContentLoaded", function () {
    const smartBackBtn = document.getElementById("neo-smart-back");

    if (smartBackBtn) {
      smartBackBtn.addEventListener("click", function (e) {
        // بررسی می‌کنیم که آیا تاریخچه مرورگر وجود دارد و آیا کاربر از داخل خود سایت ما آمده است؟
        if (
          window.history.length > 1 &&
          document.referrer.indexOf(window.location.host) !== -1
        ) {
          e.preventDefault(); // جلوگیری از رفتار پیش‌فرض لینک (رفتن به صفحه اصلی)
          window.history.back(); // بازگشت دقیق به صفحه قبلی (مثلا صفحه محصولی که در آن بوده)
        }
      });
    }
  });

  // ==========================================
  // کدهای مربوط به دکمه همبرگری و سایدبار موبایل
  // ==========================================
  (function () {
    const menuToggleBtn = document.getElementById("neo-menu-toggle");
    const sidebar = document.querySelector(".neo-sidebar");
    const overlay = document.getElementById("neo-sidebar-overlay");

    if (!menuToggleBtn || !sidebar) return;

    function closeSidebar() {
      sidebar.classList.remove("is-open");
      document.body.classList.remove("neo-sidebar-open");
    }

    function toggleSidebar() {
      sidebar.classList.toggle("is-open");
      document.body.classList.toggle("neo-sidebar-open");
    }

    // دکمه همبرگری
    menuToggleBtn.addEventListener("click", toggleSidebar);

    // بستن با ضربه روی فضای بیرون سایدبار (overlay)
    if (overlay) {
      overlay.addEventListener("click", closeSidebar);
    }

    // بستن بعد از انتخاب آیتم منو (فقط موبایل)
    document.querySelectorAll(".neo-menu-item").forEach(function (item) {
      item.addEventListener("click", function () {
        if (window.innerWidth <= 992) {
          closeSidebar();
        }
      });
    });
  })();
});
document.addEventListener("DOMContentLoaded", function () {
  // --- منطق پاپ‌آپ (Dropdown) پروفایل ---
  const profileIcon = document.getElementById("neo-profile-icon-toggle");
  const dropdownMenu = document.getElementById("neo-dropdown-menu");

  if (profileIcon && dropdownMenu) {
    // باز و بسته کردن منو با کلیک روی آیکون پروفایل
    profileIcon.addEventListener("click", function (e) {
      e.stopPropagation(); // جلوگیری از بسته شدن فوری بخاطر رویداد کلیک داکیومنت
      dropdownMenu.classList.toggle("active");
    });

    // بسته شدن منو وقتی روی فضایی بیرون از آن کلیک می‌شود
    document.addEventListener("click", function (e) {
      if (!dropdownMenu.contains(e.target) && !profileIcon.contains(e.target)) {
        dropdownMenu.classList.remove("active");
      }
    });
  }
});
