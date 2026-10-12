/**
 * MAYHEN.VN — GUEST MANAGEMENT & ZALO SHARING CONTROLLER
 * Vanilla ES Module
 */
import { $$, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    // Copy Personalized Zalo Text
    const copyZaloBtns = $$('[data-mayhen-action="copy-zalo"]');

    copyZaloBtns.forEach(btn => {
        on(btn, 'click', () => {
            const guestName = btn.getAttribute('data-guest-name');
            const salutation = btn.getAttribute('data-guest-salutation') || 'Kính mời';
            const guestLink = btn.getAttribute('data-guest-link');
            const coupleNames = btn.getAttribute('data-couple-names') || 'Minh Quân & Ái Như';
            const weddingDate = btn.getAttribute('data-wedding-date') || '24.10.2026';

            const message = `${salutation} ${guestName},\n\nTrân trọng kính mời quý khách tới tham dự lễ thành hôn của ${coupleNames} vào ngày ${weddingDate}.\n\nXem chi tiết thiệp cưới và gửi lời chúc phúc tại:\n${guestLink}\n\nRất hân hạnh được đón tiếp!`;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(message).then(() => {
                    const originalText = btn.textContent;
                    btn.textContent = 'Đã chép!';
                    btn.style.backgroundColor = 'var(--mayhen-color-primary-soft)';
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.backgroundColor = '';
                    }, 2000);
                });
            }
        });
    });

    // Copy Raw Link
    const copyLinkBtns = $$('[data-mayhen-action="copy-link"]');
    copyLinkBtns.forEach(btn => {
        on(btn, 'click', () => {
            const link = btn.getAttribute('data-guest-link');
            if (navigator.clipboard && link) {
                navigator.clipboard.writeText(link).then(() => {
                    alert('Đã sao chép đường link thiệp cá nhân hóa!');
                });
            }
        });
    });
});
