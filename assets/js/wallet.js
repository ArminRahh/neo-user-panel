// assets/js/wallet.js

document.addEventListener('submit', function (e) {
    // بررسی می‌کنیم که آیا فرم سابمیت شده، همان فرم شارژ کیف پول است یا خیر
    if (e.target && e.target.id === 'neo-recharge-form') {
        e.preventDefault(); // جلوگیری قطعی از رفرش صفحه و تغییر URL

        const rechargeForm = e.target;
        const submitBtn = rechargeForm.querySelector('button[type="submit"]');
        const btnText = submitBtn ? submitBtn.querySelector('.neo-btn-text') : null;
        const btnLoader = submitBtn ? submitBtn.querySelector('.neo-btn-loader') : null;
        const walletMessage = document.getElementById('neo-wallet-message');
        
        const presetAmountInput = document.getElementById('neo-preset-amount');
        const customAmountInput = document.getElementById('neo-custom-amount');
        const gatewayInput = document.querySelector('input[name="gateway"]:checked');

        // محاسبه مبلغ نهایی
        let finalAmount = '';
        if (customAmountInput && customAmountInput.value) {
            finalAmount = customAmountInput.value;
        } else if (presetAmountInput && presetAmountInput.value) {
            finalAmount = presetAmountInput.value;
        }

        // اعتبارسنجی مبلغ
        if (!finalAmount || parseInt(finalAmount) < 1000) {
            if (walletMessage) {
                walletMessage.style.display = 'block';
                walletMessage.style.backgroundColor = '#fee2e2';
                walletMessage.style.color = '#991b1b';
                walletMessage.innerHTML = '⚠️ لطفاً مبلغ شارژ را (حداقل 1000 تومان) مشخص کنید.';
            }
            return;
        }

        // اعتبارسنجی درگاه
        if (!gatewayInput) {
            if (walletMessage) {
                walletMessage.style.display = 'block';
                walletMessage.style.backgroundColor = '#fee2e2';
                walletMessage.style.color = '#991b1b';
                walletMessage.innerHTML = '⚠️ لطفاً یک درگاه پرداخت انتخاب کنید.';
            }
            return;
        }

        // تغییر ظاهر دکمه به حالت لودینگ
        if (submitBtn) submitBtn.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (btnLoader) btnLoader.style.display = 'inline-block';

        // آماده‌سازی داده‌ها برای ارسال به بک‌اند
        const formData = new FormData(rechargeForm);
        formData.append('amount', finalAmount);
        formData.append('gateway', gatewayInput.value);
        formData.append('action', 'neo_process_recharge');
        formData.append('security', neo_ajax_obj.nonce);

        // ارسال درخواست ایجکس
        fetch(neo_ajax_obj.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (walletMessage) {
                    walletMessage.style.display = 'block';
                    walletMessage.style.backgroundColor = '#d1fae5';
                    walletMessage.style.color = '#065f46';
                    walletMessage.innerHTML = '✅ ' + data.data.message;
                }
                
                // انتقال به درگاه بانکی
                if (data.data.redirect_url) {
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 1000);
                } else {
                    if (walletMessage) {
                        walletMessage.innerHTML += '<br>⚠️ لینک درگاه از سمت سرور دریافت نشد!';
                    }
                    if (submitBtn) submitBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline-block';
                    if (btnLoader) btnLoader.style.display = 'none';
                }
            } else {
                if (walletMessage) {
                    walletMessage.style.display = 'block';
                    walletMessage.style.backgroundColor = '#fee2e2';
                    walletMessage.style.color = '#991b1b';
                    walletMessage.innerHTML = '❌ ' + (data.data.message || 'خطایی رخ داد.');
                }
                if (submitBtn) submitBtn.disabled = false;
                if (btnText) btnText.style.display = 'inline-block';
                if (btnLoader) btnLoader.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (walletMessage) {
                walletMessage.style.display = 'block';
                walletMessage.style.backgroundColor = '#fee2e2';
                walletMessage.style.color = '#991b1b';
                walletMessage.innerHTML = '❌ خطای ارتباط با سرور.';
            }
            if (submitBtn) submitBtn.disabled = false;
            if (btnText) btnText.style.display = 'inline-block';
            if (btnLoader) btnLoader.style.display = 'none';
        });
    }
});


// تابع بررسی پارامتر بازگشت از بانک در URL
function checkWalletRechargeStatus() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('recharge') === 'success') {
        
        // باز کردن خودکار تب کیف پول
        const walletTab = document.querySelector('[data-tab="wallet"]');
        if (walletTab) {
            walletTab.click();
        }

        // نمایش پیام موفقیت پس از مکث کوتاه
        setTimeout(() => {
            const successMsg = document.createElement('div');
            successMsg.style.cssText = 'background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #34d399; font-weight: bold;';
            successMsg.innerHTML = '🎉 پرداخت شما با موفقیت انجام شد و کیف پولتان شارژ گردید!';
            
            const contentArea = document.getElementById('neo-tab-content') || document.querySelector('.neo-panel-content');
            if (contentArea) {
                contentArea.prepend(successMsg);
            }

            // حذف پیام پس از ۶ ثانیه
            setTimeout(() => {
                successMsg.style.transition = 'opacity 0.5s ease';
                successMsg.style.opacity = '0';
                setTimeout(() => successMsg.remove(), 500);
            }, 6000);
        }, 500);

        // پاک کردن پارامتر recharge=success از URL بدون رفرش شدن صفحه
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: newUrl}, '', newUrl);
    }
}
