/**
 * May Hen — Studio Showcase Interactions (Pure Vanilla ES Modules)
 * Class Prefix: mayhen_studio_*
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnCopy = document.getElementById('btn-copy-studio-link');

    if (btnCopy) {
        btnCopy.addEventListener('click', async () => {
            const url = btnCopy.getAttribute('data-url') || window.location.href;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(url);
                } else {
                    // Fallback input
                    const tempInput = document.createElement('input');
                    tempInput.value = url;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                }

                const span = btnCopy.querySelector('span');
                const origText = span ? span.innerText : 'Chia Sẻ Bộ Sưu Tập';
                
                if (span) span.innerText = '✓ Đã Sao Chép Liên Kết!';
                btnCopy.style.background = '#E8F5E9';
                btnCopy.style.borderColor = '#A5D6A7';
                btnCopy.style.color = '#2E7D32';

                setTimeout(() => {
                    if (span) span.innerText = origText;
                    btnCopy.style.background = '';
                    btnCopy.style.borderColor = '';
                    btnCopy.style.color = '';
                }, 2000);
            } catch (err) {
                console.error('Không thể sao chép liên kết:', err);
            }
        });
    }
});

