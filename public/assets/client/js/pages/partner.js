/**
 * Mây Hẹn (mayhen.vn) — Partner & Studio B2B Native JavaScript
 * ES Modules & Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sao chép mã bàn giao hoặc mã giới thiệu 1-chạm
    const copyBtns = document.querySelectorAll('[data-copy-target]');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const targetSelector = btn.getAttribute('data-copy-target');
            const targetEl = document.querySelector(targetSelector);
            if (!targetEl) return;

            const textToCopy = targetEl.value || targetEl.textContent.trim();
            if (!textToCopy) return;

            try {
                await navigator.clipboard.writeText(textToCopy);
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '✓ Đã Sao Chép!';
                btn.classList.add('is-copied');

                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('is-copied');
                }, 2000);
            } catch (err) {
                console.error('Không thể sao chép vào bộ nhớ đệm:', err);
            }
        });
    });

    // 2. Tương tác chọn Template Card trong form tạo thiệp
    const templateRadios = document.querySelectorAll('.template_radio_input');
    templateRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.template_radio_card').forEach(card => {
                card.classList.remove('is-selected');
            });
            if (radio.checked) {
                const parentCard = radio.closest('.template_radio_card');
                if (parentCard) {
                    parentCard.classList.add('is-selected');
                }
            }
        });

        // Đánh dấu thẻ checked mặc định lúc load
        if (radio.checked) {
            const parentCard = radio.closest('.template_radio_card');
            if (parentCard) {
                parentCard.classList.add('is-selected');
            }
        }
    });

    // 3. Tự động viết hoa ô nhập mã bàn giao
    const handoverInput = document.getElementById('handover_code_input');
    if (handoverInput) {
        handoverInput.addEventListener('input', () => {
            handoverInput.value = handoverInput.value.toUpperCase();
        });
    }
});

