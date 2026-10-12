/**
 * MAYHEN.VN — CHECKOUT & VIETQR POLLING CONTROLLER
 * Vanilla ES Module
 */
import { $, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = $('#checkout-form');
    const formSection = $('#checkout-form-section');
    const qrSection = $('#checkout-qr-section');
    const qrImg = $('#vietqr-image');
    const bankDetails = $('#bank-details');
    const successBanner = $('#checkout-success-banner');

    let pollingTimer = null;

    if (checkoutForm) {
        on(checkoutForm, 'submit', async (e) => {
            e.preventDefault();

            const submitBtn = checkoutForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang khởi tạo thanh toán...';

            const formData = new FormData(checkoutForm);

            try {
                const response = await fetch('/api/checkout/process', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    formSection.style.display = 'none';
                    qrSection.style.display = 'block';

                    qrImg.src = data.vietqr_url;
                    bankDetails.innerHTML = `
                        <strong>Ngân hàng:</strong> ${data.bank_info.bank_name}<br>
                        <strong>Số tài khoản:</strong> <span style="font-family: monospace; font-size: 1rem; color: #1C1917;">${data.bank_info.account_number}</span><br>
                        <strong>Chủ tài khoản:</strong> ${data.bank_info.account_name}<br>
                        <strong>Số tiền:</strong> <span style="font-weight: 600; color: #C5A880;">${data.formatted_amount}</span><br>
                        <strong>Nội dung chuyển khoản:</strong> <span style="font-family: monospace; font-size: 1rem; font-weight: 600; color: #C5A880;">${data.bank_info.content}</span>
                    `;

                    // Start Polling for payment status
                    pollingTimer = setInterval(async () => {
                        try {
                            const statusRes = await fetch(`/api/checkout/status/${data.order_code}`);
                            const statusData = await statusRes.json();

                            if (statusData.is_paid) {
                                clearInterval(pollingTimer);
                                qrSection.style.display = 'none';
                                successBanner.style.display = 'block';
                            }
                        } catch (err) {
                            console.error('Polling status error', err);
                        }
                    }, 3000);
                } else {
                    alert('Lỗi tạo đơn hàng: ' + (data.message || 'Xin vui lòng thử lại sau.'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Tiếp Tục Thanh Toán';
                }
            } catch (err) {
                console.error(err);
                alert('Không thể kết nối đến máy chủ thanh toán.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Tiếp Tục Thanh Toán';
            }
        });
    }
});
