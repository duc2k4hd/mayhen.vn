/**
 * Mây Hẹn (mayhen.vn) - Wedding QR Code & Print Card Generator Modal
 * 100% Pure Vanilla JavaScript (Native ES Modules), Zero External Libraries.
 */

import { renderQrCode } from '../common/qrcode.js';

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('mayhen-qr-modal');
    if (!modal) return;

    const modalBackdrop = modal.querySelector('.mayhen_qr_modal_backdrop');
    const closeBtn = modal.querySelector('.mayhen_qr_modal_close');
    const previewCanvas = document.getElementById('mayhen-qr-preview-canvas');
    const coupleNameEl = document.getElementById('mayhen-qr-couple-name');
    const weddingDateEl = document.getElementById('mayhen-qr-wedding-date');
    const invitationLinkEl = document.getElementById('mayhen-qr-invitation-link');
    const copyLinkBtn = document.getElementById('mayhen-qr-copy-btn');
    const downloadCardBtn = document.getElementById('mayhen-qr-download-card-btn');
    const downloadRawBtn = document.getElementById('mayhen-qr-download-raw-btn');

    let currentData = {
        url: '',
        coupleName: '',
        weddingDate: '',
        templateName: '',
        slug: ''
    };

    // Mở Modal khi click vào nút kích hoạt bất kỳ
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.mayhen_account_qr_trigger');
        if (!trigger) return;

        e.preventDefault();

        currentData = {
            url: trigger.getAttribute('data-invitation-url') || window.location.origin,
            coupleName: trigger.getAttribute('data-couple-name') || 'Cô Dâu & Chú Rể',
            weddingDate: trigger.getAttribute('data-wedding-date') || '',
            templateName: trigger.getAttribute('data-template-name') || 'Mẫu Thiệp Mây Hẹn',
            slug: trigger.getAttribute('data-invitation-slug') || 'thiep-cuoi'
        };

        openQrModal(currentData);
    });

    function openQrModal(data) {
        if (coupleNameEl) coupleNameEl.textContent = data.coupleName;
        if (weddingDateEl) {
            weddingDateEl.textContent = data.weddingDate ? `Ngày hôn lễ: ${data.weddingDate}` : 'Ngày hôn lễ thiêng liêng';
        }
        if (invitationLinkEl) {
            invitationLinkEl.textContent = data.url;
            invitationLinkEl.href = data.url;
        }

        // Render QR Code lên canvas preview (kích thước 220x220)
        if (previewCanvas) {
            renderQrCode(previewCanvas, data.url, {
                size: 220,
                margin: 10,
                darkColor: '#1F1D1A',
                lightColor: '#FFFFFF',
                errorCorrectionLevel: 'M'
            });
        }

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeQrModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    if (modalBackdrop) modalBackdrop.addEventListener('click', closeQrModal);
    if (closeBtn) closeBtn.addEventListener('click', closeQrModal);

    // Đóng bằng phím ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeQrModal();
        }
    });

    // Sao chép liên kết thiệp
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', () => {
            if (!currentData.url) return;
            navigator.clipboard.writeText(currentData.url).then(() => {
                const originalHtml = copyLinkBtn.innerHTML;
                copyLinkBtn.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Đã chép!</span>
                `;
                setTimeout(() => {
                    copyLinkBtn.innerHTML = originalHtml;
                }, 2200);
            }).catch(() => {
                // Fallback nếu không có clipboard API
                const input = document.createElement('input');
                input.value = currentData.url;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            });
        });
    }

    // Tải mã QR vuông độc lập (600x600 px)
    if (downloadRawBtn) {
        downloadRawBtn.addEventListener('click', () => {
            const rawCanvas = document.createElement('canvas');
            renderQrCode(rawCanvas, currentData.url, {
                size: 600,
                margin: 24,
                darkColor: '#1F1D1A',
                lightColor: '#FFFFFF',
                errorCorrectionLevel: 'H'
            });

            const link = document.createElement('a');
            link.download = `mayhen-qr-${currentData.slug}.png`;
            link.href = rawCanvas.toDataURL('image/png');
            link.click();
        });
    }

    // Tải thẻ thiệp in sang trọng Quiet Luxury (1000 x 1400 px, tỷ lệ 5:7 chuẩn in ấn thiệp & standee)
    if (downloadCardBtn) {
        downloadCardBtn.addEventListener('click', () => {
            generateAndDownloadPrintCard(currentData);
        });
    }

    function generateAndDownloadPrintCard(data) {
        const W = 1000;
        const H = 1400;
        const canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');

        // 1. Nền giấy mỹ thuật ấm (Warm Ivory #FAF8F5)
        ctx.fillStyle = '#FAF8F5';
        ctx.fillRect(0, 0, W, H);

        // Hiệu ứng vân giấy nhẹ
        const gradient = ctx.createRadialGradient(W / 2, H / 2, 100, W / 2, H / 2, 700);
        gradient.addColorStop(0, '#FFFFFF');
        gradient.addColorStop(1, '#F3EDE4');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, W, H);

        // 2. Khung viền kép hoàng gia (Double Border Quiet Luxury)
        // Viền ngoài (Gold)
        ctx.strokeStyle = '#C5A880';
        ctx.lineWidth = 2.5;
        ctx.strokeRect(50, 50, W - 100, H - 100);

        // Viền trong (Muted Sand)
        ctx.strokeStyle = '#E5DCCD';
        ctx.lineWidth = 1;
        ctx.strokeRect(62, 62, W - 124, H - 124);

        // Họa tiết góc cổ điển (Corner Accents)
        drawCornerAccent(ctx, 62, 62, 0);
        drawCornerAccent(ctx, W - 62, 62, Math.PI / 2);
        drawCornerAccent(ctx, W - 62, H - 62, Math.PI);
        drawCornerAccent(ctx, 62, H - 62, -Math.PI / 2);

        // 3. Tiêu đề phía trên
        ctx.textAlign = 'center';

        // Lời chào trang trọng
        ctx.font = '600 16px "Inter", -apple-system, BlinkMacSystemFont, sans-serif';
        ctx.fillStyle = '#9C7A4A';
        ctx.letterSpacing = '6px';
        ctx.fillText('TRÂN TRỌNG KÍNH MỜI', W / 2, 160);

        ctx.font = 'italic 400 15px "Cormorant Garamond", Georgia, serif';
        ctx.fillStyle = '#7A726A';
        ctx.letterSpacing = '3px';
        ctx.fillText('QUÉT MÃ ĐỂ XEM THIỆP CƯỚI TRỰC TUYẾN', W / 2, 195);

        // Dải phân cách hoa văn nhẹ
        ctx.strokeStyle = '#D8CEBD';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(W / 2 - 80, 220);
        ctx.lineTo(W / 2 + 80, 220);
        ctx.stroke();

        ctx.fillStyle = '#C5A880';
        ctx.beginPath();
        ctx.arc(W / 2, 220, 3, 0, Math.PI * 2);
        ctx.fill();

        // Tên Cô Dâu & Chú Rể
        ctx.font = '600 52px "Cormorant Garamond", Georgia, serif';
        ctx.fillStyle = '#1F1D1A';
        ctx.letterSpacing = '1px';
        ctx.fillText(data.coupleName, W / 2, 300);

        // Ngày cưới
        if (data.weddingDate) {
            ctx.font = '500 20px "Inter", -apple-system, BlinkMacSystemFont, sans-serif';
            ctx.fillStyle = '#5A534B';
            ctx.letterSpacing = '2px';
            ctx.fillText(data.weddingDate, W / 2, 350);
        }

        // 4. Khung chứa Mã QR ở trung tâm
        const qrBoxSize = 540;
        const qrBoxX = (W - qrBoxSize) / 2;
        const qrBoxY = 400;

        // Nền trắng tinh tế bo góc cho QR
        ctx.fillStyle = '#FFFFFF';
        roundRect(ctx, qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 20);
        ctx.fill();

        // Viền thẻ QR
        ctx.strokeStyle = '#EAE3D9';
        ctx.lineWidth = 1.5;
        roundRect(ctx, qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 20);
        ctx.stroke();

        // Render QR Code vào thẻ tạm rồi vẽ vào canvas chính
        const tempQrCanvas = document.createElement('canvas');
        renderQrCode(tempQrCanvas, data.url, {
            size: 460,
            margin: 16,
            darkColor: '#1F1D1A',
            lightColor: '#FFFFFF',
            errorCorrectionLevel: 'H'
        });

        ctx.drawImage(tempQrCanvas, qrBoxX + 40, qrBoxY + 40, 460, 460);

        // 5. Hướng dẫn & Lời chúc dưới mã
        ctx.font = '400 17px "Inter", -apple-system, BlinkMacSystemFont, sans-serif';
        ctx.fillStyle = '#6E665E';
        ctx.letterSpacing = '0.5px';
        ctx.fillText('Sự hiện diện của Quý khách là niềm vinh hạnh cho chúng tôi', W / 2, 1010);

        // 6. Đường link rút gọn
        const shortUrl = data.url.replace(/^https?:\/\//i, '');
        ctx.fillStyle = '#FAF4EB';
        roundRect(ctx, W / 2 - 240, 1050, 480, 46, 23);
        ctx.fill();
        ctx.strokeStyle = '#E2D7C8';
        ctx.lineWidth = 1;
        roundRect(ctx, W / 2 - 240, 1050, 480, 46, 23);
        ctx.stroke();

        ctx.font = '500 15px "Courier New", monospace';
        ctx.fillStyle = '#9C7A4A';
        ctx.letterSpacing = '0.5px';
        ctx.fillText(shortUrl, W / 2, 1079);

        // 7. Chân trang thương hiệu Quiet Luxury
        ctx.strokeStyle = '#E5DCCD';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(W / 2 - 100, 1240);
        ctx.lineTo(W / 2 + 100, 1240);
        ctx.stroke();

        ctx.font = '600 14px "Cormorant Garamond", Georgia, serif';
        ctx.fillStyle = '#B8935D';
        ctx.letterSpacing = '4px';
        ctx.fillText('M Â Y   H Ẹ N', W / 2, 1275);

        ctx.font = '400 12px "Inter", -apple-system, BlinkMacSystemFont, sans-serif';
        ctx.fillStyle = '#9E948A';
        ctx.letterSpacing = '1px';
        ctx.fillText('mayhen.vn • Tinh hoa thiệp cưới số', W / 2, 1300);

        // Xuất file PNG
        const link = document.createElement('a');
        link.download = `mayhen-the-in-qr-${data.slug}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function drawCornerAccent(ctx, x, y, rotation) {
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(rotation);
        ctx.strokeStyle = '#C5A880';
        ctx.lineWidth = 1.5;

        // Góc vuông bo nhẹ
        ctx.beginPath();
        ctx.moveTo(-16, 0);
        ctx.lineTo(0, 0);
        ctx.lineTo(0, -16);
        ctx.stroke();

        // Chấm tròn nhỏ góc
        ctx.fillStyle = '#C5A880';
        ctx.beginPath();
        ctx.arc(6, 6, 2, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    function roundRect(ctx, x, y, width, height, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
    }
});

