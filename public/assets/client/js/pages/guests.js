/**
 * MAYHEN.VN — GUEST MANAGEMENT & ZALO SHARING CONTROLLER
 * Vanilla ES Module (Zero External Library)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Chuyển đổi Tabs thêm khách (Single vs Batch)
    const tabButtons = document.querySelectorAll('.mayhen_guest_add_tab_btn');
    const singlePane = document.getElementById('tab-single');
    const batchPane = document.getElementById('tab-batch');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab-target');

            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (target === 'single') {
                if (singlePane) singlePane.style.display = 'block';
                if (batchPane) batchPane.style.display = 'none';
            } else if (target === 'batch') {
                if (singlePane) singlePane.style.display = 'none';
                if (batchPane) batchPane.style.display = 'block';
            }
        });
    });

    // 2. Sao chép lời mời Zalo kèm link cá nhân hóa
    const copyZaloBtns = document.querySelectorAll('[data-mayhen-action="copy-zalo"]');
    copyZaloBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const guestName = btn.getAttribute('data-guest-name') || '';
            const salutation = btn.getAttribute('data-guest-salutation') || 'Kính mời';
            const guestLink = btn.getAttribute('data-guest-link') || '';
            const coupleNames = btn.getAttribute('data-couple-names') || 'Cô Dâu & Chú Rể';
            const weddingDate = btn.getAttribute('data-wedding-date') || '';

            const dateStr = weddingDate ? ` vào ngày ${weddingDate}` : '';
            const message = `${salutation} ${guestName},\n\nTrân trọng kính mời quý khách tới tham dự lễ thành hôn của ${coupleNames}${dateStr}.\n\nXem chi tiết thiệp cưới và gửi lời chúc phúc tại:\n${guestLink}\n\nRất hân hạnh được đón tiếp!`;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(message).then(() => {
                    const originalHtml = btn.innerHTML;
                    btn.innerText = 'Đã chép lời mời!';
                    btn.style.backgroundColor = '#1F1D1A';
                    btn.style.color = '#FAF8F5';
                    btn.style.borderColor = '#1F1D1A';

                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.style.backgroundColor = '';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                    }, 2000);
                });
            }
        });
    });

    // 3. Sao chép trực tiếp đường link thiệp của khách (không dùng alert)
    const copyLinkBtns = document.querySelectorAll('[data-mayhen-action="copy-link"]');
    copyLinkBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const link = btn.getAttribute('data-guest-link');
            if (navigator.clipboard && link) {
                navigator.clipboard.writeText(link).then(() => {
                    const originalText = btn.innerText;
                    btn.innerText = 'Đã chép link!';
                    btn.style.backgroundColor = '#FAF8F5';
                    btn.style.borderColor = '#C5A880';
                    btn.style.color = '#9C7A4A';

                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.style.backgroundColor = '';
                        btn.style.borderColor = '';
                        btn.style.color = '';
                    }, 2000);
                });
            }
        });
    });
});
