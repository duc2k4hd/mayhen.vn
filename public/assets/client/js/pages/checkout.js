/**
 * MAYHEN.VN — CHECKOUT & VIETQR / SEPAY ACTIVE VERIFICATION CONTROLLER
 * Pure Vanilla ES Module — Zero External Libraries
 */
import { $, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const appEl = $('#mayhen-checkout-app');
    if (!appEl) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const planId = appEl.dataset.planId || '';
    const basePlanPrice = parseInt(appEl.dataset.planPrice || '0', 10);
    const processUrl = appEl.dataset.processUrl || '/api/checkout/process';
    const couponUrl = appEl.dataset.couponUrl || '/api/checkout/apply-coupon';

    const checkoutForm = $('#checkout-form');
    const formSection = $('#checkout-form-section');
    const formAlert = $('#checkout-form-alert');
    const submitBtn = $('#checkout-submit-btn');

    const couponInput = $('#coupon_input');
    const applyCouponBtn = $('#checkout-apply-coupon-btn');
    const removeCouponBtn = $('#checkout-remove-coupon-btn');
    const appliedCouponHidden = $('#checkout-applied-coupon');
    const couponFeedback = $('#checkout-coupon-feedback');

    const summaryDiscountRow = $('#checkout-summary-discount-row');
    const summaryCouponCode = $('#checkout-summary-coupon-code');
    const summaryDiscountVal = $('#checkout-summary-discount-val');
    const summaryTotal = $('#checkout-summary-total');

    const qrSection = $('#checkout-qr-section');
    const qrImg = $('#vietqr-image');
    const orderCodeLabel = $('#checkout-order-code-label');
    const bankDetails = $('#bank-details');
    const countdownEl = $('#checkout-countdown');
    const pollingText = $('#checkout-polling-text');
    const verifyFeedback = $('#checkout-verify-feedback');
    const manualVerifyBtn = $('#checkout-manual-verify-btn');
    const copyShareLinkBtn = $('#checkout-copy-share-link-btn');
    const cancelSessionBtn = $('#checkout-cancel-session-btn');
    const isSharedPayer = appEl.dataset.isSharedPayer === '1';

    const terminatedBanner = $('#checkout-terminated-banner');
    const terminatedTitleEl = $('#checkout-terminated-title');
    const terminatedDescEl = $('#checkout-terminated-desc');
    const successBanner = $('#checkout-success-banner');
    const successRedirectBtn = $('#checkout-success-redirect-btn');

    let pollingTimer = null;
    let countdownTimer = null;
    let verifyCooldownTimer = null;
    let activeOrder = null;

    const formatVnd = (amount) => `${Number(amount || 0).toLocaleString('vi-VN')}đ`;

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const setAlert = (el, type, message) => {
        if (!el) return;
        if (!message) {
            el.hidden = true;
            el.textContent = '';
            el.className = 'mayhen_checkout_alert';
            return;
        }
        el.hidden = false;
        el.className = `mayhen_checkout_alert is-${type}`;
        el.textContent = message;
    };

    const stopAllTimers = () => {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    };

    const startCountdown = (expiresAtIso, fallbackSeconds = 1800) => {
        if (!countdownEl) return;
        if (countdownTimer) clearInterval(countdownTimer);

        const targetMs = expiresAtIso ? Date.parse(expiresAtIso) : (Date.now() + fallbackSeconds * 1000);

        const tick = () => {
            const diffSec = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
            const mins = String(Math.floor(diffSec / 60)).padStart(2, '0');
            const secs = String(diffSec % 60).padStart(2, '0');
            countdownEl.textContent = `${mins}:${secs}`;

            if (diffSec <= 0) {
                countdownEl.classList.add('is-expired');
                clearInterval(countdownTimer);
                countdownTimer = null;
                pollOrderStatus();
            } else {
                countdownEl.classList.remove('is-expired');
            }
        };

        tick();
        countdownTimer = setInterval(tick, 1000);
    };

    const startVerifyCooldown = (seconds) => {
        if (!manualVerifyBtn) return;
        if (verifyCooldownTimer) clearInterval(verifyCooldownTimer);

        let left = Math.max(1, parseInt(seconds, 10) || 10);
        manualVerifyBtn.disabled = true;
        manualVerifyBtn.textContent = `Đang chờ kiểm tra lại (${left}s)...`;

        verifyCooldownTimer = setInterval(() => {
            left -= 1;
            if (left <= 0) {
                clearInterval(verifyCooldownTimer);
                verifyCooldownTimer = null;
                manualVerifyBtn.disabled = false;
                manualVerifyBtn.textContent = 'Tôi đã chuyển khoản — Kiểm tra với SePay ngay';
            } else {
                manualVerifyBtn.textContent = `Đang chờ kiểm tra lại (${left}s)...`;
            }
        }, 1000);
    };

    const showSessionTerminated = (title, message) => {
        stopAllTimers();
        if (qrSection) qrSection.hidden = true;
        if (formSection) formSection.hidden = true;
        if (successBanner) successBanner.hidden = true;
        if (qrImg) qrImg.removeAttribute('src');
        if (bankDetails) bankDetails.innerHTML = '';
        if (terminatedTitleEl && title) {
            terminatedTitleEl.textContent = title;
        }
        if (terminatedDescEl && message) {
            terminatedDescEl.textContent = message;
        }
        if (terminatedBanner) {
            terminatedBanner.hidden = false;
        }
    };

    const showPaymentSuccess = (successUrl) => {
        stopAllTimers();
        if (qrSection) qrSection.hidden = true;
        if (formSection) formSection.hidden = true;
        if (terminatedBanner) terminatedBanner.hidden = true;
        if (successBanner) successBanner.hidden = false;
        if (successRedirectBtn && successUrl) {
            successRedirectBtn.href = successUrl;
        }

        if (successUrl) {
            setTimeout(() => {
                window.location.href = successUrl;
            }, 1500);
        }
    };

    const pollOrderStatus = async () => {
        if (!activeOrder) return;
        const statusUrl = activeOrder.status_url || `/api/checkout/status/${encodeURIComponent(activeOrder.order_code)}`;

        try {
            const statusRes = await fetch(statusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Checkout-Token': activeOrder.checkout_token || '',
                },
                credentials: 'same-origin',
            });

            if (statusRes.status === 401 || statusRes.status === 403 || statusRes.status === 404) {
                showSessionTerminated(
                    'Liên Kết Thanh Toán Không Còn Hiệu Lực',
                    'Phiên thanh toán đã bị hủy hoặc đường dẫn không hợp lệ. Vui lòng KHÔNG chuyển khoản cho mã đơn này nữa.'
                );
                return;
            }

            const statusData = await statusRes.json();
            if (statusData.is_paid) {
                const redirectTarget = isSharedPayer ? null : (statusData.success_url || activeOrder.success_url);
                showPaymentSuccess(redirectTarget);
                return;
            }

            if (
                statusData.is_terminated
                || statusData.is_cancelled
                || statusData.is_expired
                || ['cancelled', 'failed', 'refunded'].includes(statusData.status)
            ) {
                showSessionTerminated(
                    statusData.terminated_title || 'Phiên Thanh Toán Đã Bị Hủy',
                    statusData.terminated_message || 'Phiên thanh toán này đã bị chủ tài khoản hủy hoặc đã hết hạn. Vui lòng KHÔNG chuyển khoản cho mã đơn này nữa.'
                );
            }
        } catch (err) {
            console.error('Polling status error', err);
        }
    };

    const renderBankDetails = (data) => {
        if (!bankDetails) return;
        const info = data.bank_info || {};
        const rows = [
            { label: 'Ngân hàng', value: info.bank_name || '', copy: null, mono: false, highlight: false },
            { label: 'Chủ tài khoản', value: info.account_name || '', copy: null, mono: false, highlight: false },
            { label: 'Số tài khoản', value: info.account_number || '', copy: info.account_number || '', mono: true, highlight: false },
            { label: 'Số tiền chính xác', value: data.formatted_amount || formatVnd(data.amount), copy: String(data.amount || ''), mono: false, highlight: true },
            { label: 'Nội dung chuyển khoản', value: info.content || data.order_code || '', copy: info.content || data.order_code || '', mono: true, highlight: true },
        ];

        bankDetails.innerHTML = rows.map((row) => {
            const valClasses = [
                'mayhen_checkout_bank_val',
                row.mono ? 'mayhen_checkout_bank_val--mono' : '',
                row.highlight ? 'mayhen_checkout_bank_val--highlight' : '',
            ].filter(Boolean).join(' ');

            const copyBtn = row.copy
                ? `<button type="button" class="mayhen_checkout_copy_btn" data-copy-value="${escapeHtml(row.copy)}">Sao chép</button>`
                : '';

            return `
                <div class="mayhen_checkout_bank_row">
                    <span class="mayhen_checkout_bank_label">${escapeHtml(row.label)}</span>
                    <span class="mayhen_checkout_bank_val_wrap">
                        <span class="${valClasses}">${escapeHtml(row.value)}</span>
                        ${copyBtn}
                    </span>
                </div>
            `;
        }).join('');
    };

    const copyTextSafe = async (text) => {
        try {
            await navigator.clipboard.writeText(text);
        } catch {
            const temp = document.createElement('input');
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        }
    };

    if (bankDetails) {
        on(bankDetails, 'click', async (e) => {
            const btn = e.target.closest('.mayhen_checkout_copy_btn');
            if (!btn) return;
            const copyVal = btn.getAttribute('data-copy-value') || '';
            if (!copyVal) return;

            await copyTextSafe(copyVal);

            const original = btn.textContent;
            btn.textContent = 'Đã chép ✓';
            btn.classList.add('is-copied');
            setTimeout(() => {
                btn.textContent = original;
                btn.classList.remove('is-copied');
            }, 1800);
        });
    }

    if (copyShareLinkBtn) {
        on(copyShareLinkBtn, 'click', async () => {
            const shareUrl = (activeOrder && activeOrder.session_url) || window.location.href;
            await copyTextSafe(shareUrl);
            const original = copyShareLinkBtn.textContent;
            copyShareLinkBtn.textContent = 'Đã chép link ✓';
            copyShareLinkBtn.classList.add('is-copied');
            setTimeout(() => {
                copyShareLinkBtn.textContent = original;
                copyShareLinkBtn.classList.remove('is-copied');
            }, 2200);
        });
    }

    const enterQrStage = (data, pushSessionUrl = false) => {
        activeOrder = data;

        // Cập nhật URL sang link phiên bền vững (/thanh-toan/phien/{orderCode}/{token}) để F5 không mất màn hình QR
        if (pushSessionUrl && data.session_url && window.history && typeof window.history.replaceState === 'function') {
            try {
                const nextUrl = new URL(data.session_url, window.location.origin);
                if (nextUrl.origin === window.location.origin && window.location.pathname !== nextUrl.pathname) {
                    window.history.replaceState({ order_code: data.order_code }, '', nextUrl.pathname + nextUrl.search);
                }
            } catch {
                // fallback silent
            }
        }

        if (formSection) formSection.hidden = true;
        if (qrSection) qrSection.hidden = false;
        if (orderCodeLabel) orderCodeLabel.textContent = `#${data.order_code}`;

        if (qrImg) {
            qrImg.onerror = () => {
                if (data.sepay_qr_url && qrImg.src !== data.sepay_qr_url) {
                    qrImg.src = data.sepay_qr_url;
                }
            };
            qrImg.src = data.vietqr_url || data.sepay_qr_url || '';
        }

        renderBankDetails(data);
        startCountdown(data.expires_at, data.expires_in_seconds || 1800);

        if (data.discount_amount > 0 && summaryDiscountRow) {
            summaryDiscountRow.hidden = false;
            if (summaryCouponCode) {
                summaryCouponCode.textContent = data.coupon_code ? ` (${data.coupon_code})` : '';
            }
            if (summaryDiscountVal) summaryDiscountVal.textContent = `-${formatVnd(data.discount_amount)}`;
        }
        if (summaryTotal && data.formatted_amount) {
            summaryTotal.textContent = data.formatted_amount;
        }

        if (manualVerifyBtn) {
            manualVerifyBtn.hidden = false;
            manualVerifyBtn.disabled = false;
        }

        if (pollingTimer) clearInterval(pollingTimer);
        pollingTimer = setInterval(pollOrderStatus, 3000);
    };

    // Coupon Apply / Remove
    const resetCouponUi = () => {
        if (appliedCouponHidden) appliedCouponHidden.value = '';
        if (couponInput) {
            couponInput.value = '';
            couponInput.disabled = false;
        }
        if (applyCouponBtn) applyCouponBtn.hidden = false;
        if (removeCouponBtn) removeCouponBtn.hidden = true;
        if (summaryDiscountRow) summaryDiscountRow.hidden = true;
        if (summaryTotal) summaryTotal.textContent = formatVnd(basePlanPrice);
        if (couponFeedback) {
            couponFeedback.hidden = true;
            couponFeedback.textContent = '';
        }
    };

    if (applyCouponBtn && couponInput) {
        on(applyCouponBtn, 'click', async () => {
            const code = couponInput.value.trim().toUpperCase();
            if (!code) {
                couponFeedback.hidden = false;
                couponFeedback.className = 'mayhen_checkout_coupon_feedback is-error';
                couponFeedback.textContent = 'Vui lòng nhập mã ưu đãi trước khi áp dụng.';
                return;
            }

            applyCouponBtn.disabled = true;
            applyCouponBtn.textContent = 'Đang kiểm tra...';

            try {
                const res = await fetch(couponUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ code, plan_id: planId }),
                });

                const data = await res.json();
                if (res.ok && data.success && data.coupon) {
                    if (appliedCouponHidden) appliedCouponHidden.value = data.coupon.code;
                    couponInput.value = data.coupon.code;
                    couponInput.disabled = true;
                    applyCouponBtn.hidden = true;
                    if (removeCouponBtn) removeCouponBtn.hidden = false;

                    if (summaryDiscountRow) summaryDiscountRow.hidden = false;
                    if (summaryCouponCode) summaryCouponCode.textContent = ` (${data.coupon.code})`;
                    if (summaryDiscountVal) summaryDiscountVal.textContent = `-${data.coupon.formatted_discount || formatVnd(data.coupon.discount_amount)}`;
                    if (summaryTotal) summaryTotal.textContent = data.coupon.formatted_final_amount || formatVnd(data.coupon.final_amount);

                    couponFeedback.hidden = false;
                    couponFeedback.className = 'mayhen_checkout_coupon_feedback is-success';
                    couponFeedback.textContent = data.message || 'Áp dụng mã ưu đãi thành công.';
                } else {
                    couponFeedback.hidden = false;
                    couponFeedback.className = 'mayhen_checkout_coupon_feedback is-error';
                    couponFeedback.textContent = data.message || 'Mã ưu đãi không hợp lệ.';
                }
            } catch {
                couponFeedback.hidden = false;
                couponFeedback.className = 'mayhen_checkout_coupon_feedback is-error';
                couponFeedback.textContent = 'Không thể kiểm tra mã ưu đãi lúc này.';
            } finally {
                applyCouponBtn.disabled = false;
                applyCouponBtn.textContent = 'Áp Dụng';
            }
        });
    }

    if (removeCouponBtn) {
        on(removeCouponBtn, 'click', resetCouponUi);
    }

    // Active Verify with SePay Button (Secured by X-Checkout-Token)
    if (manualVerifyBtn) {
        on(manualVerifyBtn, 'click', async () => {
            if (!activeOrder) return;
            const verifyUrl = activeOrder.verify_url || `/api/checkout/verify/${encodeURIComponent(activeOrder.order_code)}`;

            manualVerifyBtn.disabled = true;
            manualVerifyBtn.textContent = 'Đang đối soát trực tiếp với SePay...';
            setAlert(verifyFeedback, 'info', 'Đang kết nối SePay kiểm tra giao dịch tiền vào cho đơn hàng của bạn...');

            try {
                const res = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Checkout-Token': activeOrder.checkout_token || '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ checkout_token: activeOrder.checkout_token || '' }),
                });

                const data = await res.json();

                if (data.is_paid) {
                    setAlert(verifyFeedback, 'success', data.message || 'Đã xác nhận thanh toán thành công!');
                    const redirectTarget = isSharedPayer ? null : (data.success_url || activeOrder.success_url);
                    showPaymentSuccess(redirectTarget);
                    return;
                }

                if (data.is_terminated) {
                    showSessionTerminated(
                        data.terminated_title || 'Phiên Thanh Toán Đã Bị Hủy',
                        data.terminated_message || data.message || 'Phiên thanh toán này không còn hiệu lực. Vui lòng KHÔNG chuyển khoản cho mã đơn này nữa.'
                    );
                    return;
                }

                if (res.status === 429) {
                    setAlert(verifyFeedback, 'warning', data.message || 'Vui lòng chờ vài giây trước khi kiểm tra lại.');
                    startVerifyCooldown(data.retry_after || 10);
                    return;
                }

                const alertType = data.status === 'amount_mismatch' || res.status >= 400 ? 'error' : 'warning';
                setAlert(verifyFeedback, alertType, data.message || 'Chưa ghi nhận giao dịch mới trên SePay.');
                startVerifyCooldown(data.cooldown_seconds || 10);
            } catch (err) {
                console.error('Manual verify error', err);
                setAlert(verifyFeedback, 'error', 'Không thể kết nối máy chủ đối soát. Vui lòng thử lại sau vài giây.');
                manualVerifyBtn.disabled = false;
                manualVerifyBtn.textContent = 'Tôi đã chuyển khoản — Kiểm tra với SePay ngay';
            }
        });
    }

    if (cancelSessionBtn) {
        on(cancelSessionBtn, 'click', async () => {
            if (!activeOrder || !activeOrder.order_code) return;
            const confirmed = window.confirm(
                `Chỉ hủy nếu bạn CHƯA chuyển khoản cho mã đơn #${activeOrder.order_code}.\nNếu đã chuyển tiền, hãy bấm "Kiểm tra với SePay ngay".\n\nBạn có chắc chắn muốn hủy phiên thanh toán này?`
            );
            if (!confirmed) return;

            cancelSessionBtn.disabled = true;
            cancelSessionBtn.textContent = 'Đang hủy phiên...';

            try {
                const res = await fetch(`/api/checkout/cancel/${encodeURIComponent(activeOrder.order_code)}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    stopAllTimers();
                    window.location.href = '/bang-gia';
                    return;
                }
                setAlert(verifyFeedback, 'error', data.message || 'Không thể hủy phiên thanh toán lúc này.');
            } catch {
                setAlert(verifyFeedback, 'error', 'Lỗi kết nối khi hủy phiên thanh toán.');
            } finally {
                cancelSessionBtn.disabled = false;
                cancelSessionBtn.textContent = 'Hủy Phiên Này';
            }
        });
    }

    // Form Submit -> Create Authenticated Order + Private Checkout Token
    if (checkoutForm) {
        on(checkoutForm, 'submit', async (e) => {
            e.preventDefault();
            setAlert(formAlert, null, '');

            const phoneEl = $('#customer_phone');
            if (phoneEl && phoneEl.value.trim().length < 8) {
                setAlert(formAlert, 'error', 'Vui lòng nhập số điện thoại / Zalo hợp lệ (tối thiểu 8 chữ số).');
                phoneEl.focus();
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang khởi tạo phiên thanh toán an toàn...';
            }

            const formData = new FormData(checkoutForm);

            try {
                const response = await fetch(processUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: formData,
                });

                if (response.status === 401) {
                    window.location.href = `/dang-nhap?redirect=${encodeURIComponent(window.location.href)}`;
                    return;
                }

                const data = await response.json();

                if (response.ok && data.success) {
                    enterQrStage(data, true);
                } else {
                    if (data.code === 'PENDING_ORDER_EXISTS' && data.pending_session_url) {
                        setAlert(formAlert, 'warning', data.message || 'Bạn đang có một phiên thanh toán chưa hoàn tất.');
                        setTimeout(() => {
                            window.location.href = data.pending_session_url;
                        }, 1200);
                        return;
                    }
                    const firstFieldError = data.errors
                        ? Object.values(data.errors).flat()[0]
                        : null;
                    setAlert(formAlert, 'error', firstFieldError || data.message || 'Không thể tạo đơn hàng. Vui lòng thử lại.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Tạo Mã VietQR Thanh Toán';
                    }
                }
            } catch (err) {
                console.error(err);
                setAlert(formAlert, 'error', 'Không thể kết nối đến máy chủ thanh toán. Vui lòng kiểm tra mạng và thử lại.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Tạo Mã VietQR Thanh Toán';
                }
            }
        });
    }

    // Resume existing pending order if navigated from /tai-khoan/don-hang
    const resumeScript = $('#mayhen-checkout-resume-data');
    if (resumeScript && resumeScript.textContent) {
        try {
            const resumedData = JSON.parse(resumeScript.textContent);
            if (resumedData && resumedData.order_code && resumedData.checkout_token) {
                enterQrStage(resumedData);
            }
        } catch (err) {
            console.error('Resume checkout parse error', err);
        }
    }

    window.addEventListener('beforeunload', stopAllTimers);
});
