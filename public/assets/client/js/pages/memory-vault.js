/**
 * BẢO TÀNG KỶ NIỆM NGÀY CƯỚI SỐ (DIGITAL MEMORY VAULT)
 * Mây Hẹn (mayhen.vn) — Pure Native ES Module
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnShare = document.getElementById('btn_share_vault');
    if (!btnShare) return;

    btnShare.addEventListener('click', async () => {
        const url = window.location.href;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }

            const originalText = btnShare.innerHTML;
            btnShare.innerHTML = '✓ Đã Sao Chép Link!';
            btnShare.style.background = '#E8F5E9';
            btnShare.style.borderColor = '#C8E6C9';
            btnShare.style.color = '#2E7D32';

            setTimeout(() => {
                btnShare.innerHTML = originalText;
                btnShare.style.background = '';
                btnShare.style.borderColor = '';
                btnShare.style.color = '';
            }, 2500);
        } catch (err) {
            alert('Không thể tự động sao chép link. Quý khách vui lòng copy link trên thanh địa chỉ trình duyệt.');
        }
    });
});

