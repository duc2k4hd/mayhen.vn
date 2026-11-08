/**
 * MAYHEN.VN — AI CURATED INVITATION WIZARD JAVASCRIPT
 * Pure Native JS, Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('curatedWizardForm');
    const submitBtn = document.getElementById('submitCuratedBtn');

    if (!form || !submitBtn) return;

    form.addEventListener('submit', (e) => {
        const groomInput = document.getElementById('groom_name');
        const brideInput = document.getElementById('bride_name');

        if (!groomInput || !brideInput) return;

        const groomName = groomInput.value.trim();
        const brideName = brideInput.value.trim();

        if (!groomName || !brideName) {
            e.preventDefault();
            alert('Vui lòng nhập đầy đủ họ tên Chú rể và Cô dâu.');
            if (!groomName) groomInput.focus();
            else brideInput.focus();
            return;
        }

        // Kiểm tra đã chọn style và palette
        const selectedStyle = form.querySelector('input[name="style"]:checked');
        const selectedPalette = form.querySelector('input[name="palette"]:checked');

        if (!selectedStyle) {
            e.preventDefault();
            alert('Vui lòng chọn 1 phong cách thẩm mỹ.');
            return;
        }

        if (!selectedPalette) {
            e.preventDefault();
            alert('Vui lòng chọn 1 bảng màu chủ đạo.');
            return;
        }

        // Chuyển nút sang trạng thái đang xử lý
        submitBtn.disabled = true;
        const textSpan = submitBtn.querySelector('.btn_text');
        const subSpan = submitBtn.querySelector('.btn_sub');

        if (textSpan) {
            textSpan.textContent = '✦ Đang Dựng Bản Thiệp Độc Bản... ✦';
        }
        if (subSpan) {
            subSpan.textContent = 'Mây Hẹn đang phối 7 mục & AI đang sáng tác lời chúc';
        }
    });
});

