/**
 * MAYHEN.VN — AI CURATED INVITATION WIZARD JAVASCRIPT
 * Pure Native JS, Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('curatedWizardForm');
    const submitBtn = document.getElementById('submitCuratedBtn');

    if (!form || !submitBtn) return;

    const groomInput = document.getElementById('groom_name');
    const brideInput = document.getElementById('bride_name');
    const summaryCouple = document.getElementById('summaryCoupleText');
    const summaryStyle = document.getElementById('summaryStyleText');
    const summaryPalette = document.getElementById('summaryPaletteText');

    const styleNames = {
        romantic: 'Lãng Mạn (Romantic Poetry)',
        minimal: 'Tối Giản (Modern Minimalist)',
        editorial: 'Tạp Chí (Editorial Vogue)',
        traditional: 'Gia Phong (Traditional Grace)',
    };

    const paletteNames = {
        'ruby': 'Đỏ Rượu Vang & Vàng Antique',
        'dusty-rose': 'Hồng Tro & Phấn Lụa Champagne',
        'sage-green': 'Xanh Xô Thơm & Rừng Thu Olive',
        'royal-navy': 'Xanh Hoàng Gia & Bạc Ánh Trăng',
        'warm-amber': 'Hổ Phách & Hoàng Hôn Ấm',
    };

    function updateSummary() {
        const groom = groomInput ? groomInput.value.trim() : '';
        const bride = brideInput ? brideInput.value.trim() : '';

        if (summaryCouple) {
            summaryCouple.textContent = (groom && bride) ? `${groom} & ${bride}` : 'Minh Tuấn & Thu Hà';
        }

        const selectedStyle = form.querySelector('input[name="style"]:checked');
        if (summaryStyle && selectedStyle && styleNames[selectedStyle.value]) {
            summaryStyle.textContent = styleNames[selectedStyle.value];
        }

        const selectedPalette = form.querySelector('input[name="palette"]:checked');
        if (summaryPalette && selectedPalette && paletteNames[selectedPalette.value]) {
            summaryPalette.textContent = paletteNames[selectedPalette.value];
        }
    }

    // Lắng nghe thay đổi để cập nhật tóm tắt thiết kế
    if (groomInput) groomInput.addEventListener('input', updateSummary);
    if (brideInput) brideInput.addEventListener('input', updateSummary);

    form.querySelectorAll('input[name="style"]').forEach((r) => {
        r.addEventListener('change', updateSummary);
    });

    form.querySelectorAll('input[name="palette"]').forEach((r) => {
        r.addEventListener('change', updateSummary);
    });

    // Khởi tạo hiển thị ban đầu
    updateSummary();

    // Xử lý gửi biểu mẫu & chống click đúp
    let isSubmitting = false;

    form.addEventListener('submit', (e) => {
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        const groomName = groomInput ? groomInput.value.trim() : '';
        const brideName = brideInput ? brideInput.value.trim() : '';

        if (!groomName || !brideName) {
            e.preventDefault();
            alert('Vui lòng nhập đầy đủ họ tên Chú rể và Cô dâu.');
            if (!groomName && groomInput) groomInput.focus();
            else if (brideInput) brideInput.focus();
            return;
        }

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

        isSubmitting = true;
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

