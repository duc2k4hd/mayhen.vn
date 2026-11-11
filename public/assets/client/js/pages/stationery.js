/**
 * MAYHEN.VN — SYNCHRONIZED STATIONERY KIT STUDIO (MỤC 2.7 DOCX)
 * Strict Pure Vanilla ES Modules • Native HTML5 Canvas 2D Rendering
 * Zero external libraries • High-DPI 2x Retina Export
 */

export function initStationeryStudio() {
    const appEl = document.getElementById('mayhen_stationery_app');
    if (!appEl) return;

    // 1. Phân tích dữ liệu từ Data Attributes
    let weddingData = {};
    let presets = {};
    let palettes = {};
    const logUrl = appEl.getAttribute('data-log-url');
    const csrfToken = appEl.getAttribute('data-csrf');

    try {
        weddingData = JSON.parse(appEl.getAttribute('data-wedding') || '{}');
        presets = JSON.parse(appEl.getAttribute('data-presets') || '{}');
        palettes = JSON.parse(appEl.getAttribute('data-palettes') || '{}');
    } catch (e) {
        console.error('Error parsing stationery data:', e);
    }

    // 2. Trạng thái hiện tại
    let currentPresetId = 'story';
    let currentPaletteId = 'velvet_crimson';

    const canvas = document.getElementById('mayhen_stationery_canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const titleInput = document.getElementById('stationery_custom_title');
    const quoteInput = document.getElementById('stationery_custom_quote');
    const showQrCheckbox = document.getElementById('stationery_show_qr');
    const presetLabelEl = document.getElementById('mayhen_preview_preset_label');
    const toastEl = document.getElementById('mayhen_stationery_toast');

    // Nạp ảnh QR Code SVG
    let qrImage = new Image();
    let isQrLoaded = false;
    if (weddingData.qr_code_data_uri) {
        qrImage.src = weddingData.qr_code_data_uri;
        qrImage.onload = () => {
            isQrLoaded = true;
            renderCanvas();
        };
    }

    // Nạp ảnh Cover Photo của Cặp Đôi (nếu có)
    let coverImage = new Image();
    let isCoverLoaded = false;
    if (weddingData.cover_photo) {
        coverImage.crossOrigin = 'anonymous';
        coverImage.src = weddingData.cover_photo;
        coverImage.onload = () => {
            isCoverLoaded = true;
            renderCanvas();
        };
    }

    // 3. Hàm hiển thị Toast thông báo
    function showToast(message, type = 'success') {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.style.display = 'block';
        toastEl.style.background = type === 'success' ? '#E8F5E9' : '#FDEDEC';
        toastEl.style.color = type === 'success' ? '#1B5E20' : '#922B21';
        toastEl.style.borderColor = type === 'success' ? '#A5D6A7' : '#F5B7B1';

        setTimeout(() => {
            toastEl.style.display = 'none';
        }, 3500);
    }

    // 4. Hàm vẽ chính của Canvas Studio
    function renderCanvas() {
        const preset = presets[currentPresetId] || presets['story'];
        const palette = palettes[currentPaletteId] || palettes['velvet_crimson'];

        const width = preset.width || 1080;
        const height = preset.height || 1920;

        // Cập nhật kích thước vật lý của canvas
        canvas.width = width;
        canvas.height = height;

        // Xóa sạch canvas
        ctx.clearRect(0, 0, width, height);

        // A. Nền tổng thể
        ctx.fillStyle = palette.card_bg;
        ctx.fillRect(0, 0, width, height);

        // B. Vẽ khung viền kép Hoàng Gia (Double Ornamental Border)
        const margin = 48;
        ctx.strokeStyle = palette.border;
        ctx.lineWidth = 3;
        ctx.strokeRect(margin, margin, width - margin * 2, height - margin * 2);

        ctx.strokeStyle = palette.accent;
        ctx.lineWidth = 1;
        ctx.strokeRect(margin + 12, margin + 12, width - (margin + 12) * 2, height - (margin + 12) * 2);

        // C. Vẽ 4 góc hoa văn Art Deco (Corner Flourishes)
        drawCornerFlourish(margin + 12, margin + 12, 1, 1, palette.accent);
        drawCornerFlourish(width - margin - 12, margin + 12, -1, 1, palette.accent);
        drawCornerFlourish(margin + 12, height - margin - 12, 1, -1, palette.accent);
        drawCornerFlourish(width - margin - 12, height - margin - 12, -1, -1, palette.accent);

        // D. Vẽ nội dung tùy thuộc theo Preset
        switch (currentPresetId) {
            case 'story':
                renderStoryPreset(width, height, palette);
                break;
            case 'save_the_date':
                renderSaveTheDatePreset(width, height, palette);
                break;
            case 'vertical_card':
                renderVerticalCardPreset(width, height, palette);
                break;
            case 'table_qr':
                renderTableQrPreset(width, height, palette);
                break;
            case 'timeline':
                renderTimelinePreset(width, height, palette);
                break;
            case 'bao_hy':
                renderBaoHyPreset(width, height, palette);
                break;
            case 'thank_you':
                renderThankYouPreset(width, height, palette);
                break;
            default:
                renderStoryPreset(width, height, palette);
        }
    }

    // Helper: Vẽ góc hoa văn Art Deco
    function drawCornerFlourish(x, y, dirX, dirY, color) {
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(x, y + 24 * dirY);
        ctx.lineTo(x + 24 * dirX, y);
        ctx.stroke();

        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(x + 8 * dirX, y + 8 * dirY, 3, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    // Helper: Vẽ Monogram Chữ Lồng Cặp Đôi
    function drawMonogram(centerX, centerY, size, palette) {
        ctx.save();
        ctx.strokeStyle = palette.accent;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, size, 0, Math.PI * 2);
        ctx.stroke();

        ctx.strokeStyle = palette.border;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.arc(centerX, centerY, size - 6, 0, Math.PI * 2);
        ctx.stroke();

        ctx.fillStyle = palette.primary;
        ctx.font = `italic 500 ${Math.round(size * 0.9)}px 'Cormorant Garamond', serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const monogramText = `${weddingData.groom_initial} & ${weddingData.bride_initial}`;
        ctx.fillText(monogramText, centerX, centerY + 2);
        ctx.restore();
    }

    // 1. Preset: STORY 9:16
    function renderStoryPreset(width, height, palette) {
        drawMonogram(width / 2, 220, 75, palette);

        // Tiêu đề phụ
        ctx.fillStyle = palette.accent;
        ctx.font = "600 24px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '6px';
        const customTitle = titleInput ? titleInput.value.toUpperCase() : 'TRÂN TRỌNG BÁO HỶ';
        ctx.fillText(customTitle, width / 2, 380);

        // Tên Cặp Đôi
        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 84px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.groom_name, width / 2, 500);

        ctx.fillStyle = palette.accent;
        ctx.font = "italic 400 48px 'Cormorant Garamond', serif";
        ctx.fillText('&', width / 2, 580);

        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 84px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.bride_name, width / 2, 680);

        // Đường kẻ phân cách
        drawDivider(width / 2, 740, 240, palette.border);

        // Trích dẫn yêu thương
        if (quoteInput && quoteInput.value) {
            ctx.fillStyle = palette.muted;
            ctx.font = "italic 400 26px 'Cormorant Garamond', serif";
            wrapText(ctx, `“${quoteInput.value}”`, width / 2, 800, width - 260, 38);
        }

        // Ngày cưới
        ctx.fillStyle = palette.accent;
        ctx.font = "600 32px 'Inter', sans-serif";
        ctx.fillText(weddingData.wedding_date_short, width / 2, 980);

        ctx.fillStyle = palette.text;
        ctx.font = "400 24px 'Inter', sans-serif";
        ctx.fillText(weddingData.wedding_date_formatted, width / 2, 1030);
        ctx.fillText(`(Nhằm ${weddingData.lunar_date})`, width / 2, 1070);

        // Địa điểm & Giờ tiệc
        ctx.fillStyle = palette.accent;
        ctx.font = "600 28px 'Inter', sans-serif";
        ctx.fillText(`KHAI TIỆC: ${weddingData.party_time}`, width / 2, 1170);

        ctx.fillStyle = palette.primary;
        ctx.font = "600 36px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.venue_name, width / 2, 1230);

        ctx.fillStyle = palette.muted;
        ctx.font = "400 22px 'Inter', sans-serif";
        wrapText(ctx, weddingData.venue_address, width / 2, 1280, width - 280, 32);

        // QR Code Thiệp Online
        if (showQrCheckbox && showQrCheckbox.checked && isQrLoaded) {
            const qrSize = 200;
            const qrX = (width - qrSize) / 2;
            const qrY = height - 420;
            drawQrWithFrame(qrX, qrY, qrSize, palette);

            ctx.fillStyle = palette.muted;
            ctx.font = "500 20px 'Inter', sans-serif";
            ctx.fillText('QUÉT MÃ QR ĐỂ XEM THIỆP CƯỚI TRỰC TUYẾN', width / 2, height - 170);
        }
    }

    // 2. Preset: SAVE THE DATE
    function renderSaveTheDatePreset(width, height, palette) {
        drawMonogram(width / 2, 240, 80, palette);

        ctx.fillStyle = palette.accent;
        ctx.font = "600 36px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '10px';
        ctx.fillText('SAVE THE DATE', width / 2, 430);

        ctx.fillStyle = palette.muted;
        ctx.font = "italic 400 26px 'Cormorant Garamond', serif";
        ctx.fillText('Hãy giữ lịch hẹn cùng chúng tôi trong ngày vui trọng đại', width / 2, 490);

        // Tên Đôi Uyên Ương
        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 96px 'Cormorant Garamond', serif";
        ctx.fillText(`${weddingData.groom_name} & ${weddingData.bride_name}`, width / 2, 640);

        drawDivider(width / 2, 730, 300, palette.border);

        // Ngày lớn nổi bật ở giữa
        ctx.fillStyle = palette.accent;
        ctx.font = "700 120px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.wedding_date_short, width / 2, 920);

        ctx.fillStyle = palette.text;
        ctx.font = "500 30px 'Inter', sans-serif";
        ctx.fillText(weddingData.wedding_date_formatted, width / 2, 1000);

        ctx.fillStyle = palette.muted;
        ctx.font = "400 24px 'Inter', sans-serif";
        ctx.fillText(`(Tức ${weddingData.lunar_date})`, width / 2, 1050);

        // Địa điểm
        ctx.fillStyle = palette.primary;
        ctx.font = "600 40px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.venue_name, width / 2, 1190);

        ctx.fillStyle = palette.muted;
        ctx.font = "400 24px 'Inter', sans-serif";
        wrapText(ctx, weddingData.venue_address, width / 2, 1250, width - 260, 36);

        // QR Code
        if (showQrCheckbox && showQrCheckbox.checked && isQrLoaded) {
            const qrSize = 190;
            const qrX = (width - qrSize) / 2;
            const qrY = height - 420;
            drawQrWithFrame(qrX, qrY, qrSize, palette);

            ctx.fillStyle = palette.muted;
            ctx.font = "500 20px 'Inter', sans-serif";
            ctx.fillText('QUÉT MÃ ĐỂ LƯU LỊCH & XÁC NHẬN SỚM', width / 2, height - 180);
        }
    }

    // 3. Preset: VERTICAL CARD
    function renderVerticalCardPreset(width, height, palette) {
        ctx.fillStyle = palette.accent;
        ctx.font = "600 24px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '6px';
        ctx.fillText('THIỆP MỜI THÀNH HÔN', width / 2, 180);

        // Song thân hai bên
        ctx.font = "600 20px 'Inter', sans-serif";
        ctx.fillStyle = palette.accent;
        ctx.fillText('NHÀ TRAI', width * 0.28, 260);
        ctx.fillText('NHÀ GÁI', width * 0.72, 260);

        ctx.font = "400 22px 'Inter', sans-serif";
        ctx.fillStyle = palette.text;
        ctx.fillText(`Ông: ${weddingData.groom_father}`, width * 0.28, 305);
        ctx.fillText(`Bà: ${weddingData.groom_mother}`, width * 0.28, 345);

        ctx.fillText(`Ông: ${weddingData.bride_father}`, width * 0.72, 305);
        ctx.fillText(`Bà: ${weddingData.bride_mother}`, width * 0.72, 345);

        drawDivider(width / 2, 400, 240, palette.border);

        ctx.fillStyle = palette.muted;
        ctx.font = "italic 400 24px 'Cormorant Garamond', serif";
        ctx.fillText('Trân trọng kính mời Quý khách đến dự tiệc mừng hạnh phúc cùng chúng tôi', width / 2, 460);

        // Tên Cô Dâu Chú Rể
        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 78px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.groom_name, width / 2, 570);

        ctx.fillStyle = palette.accent;
        ctx.font = "italic 400 42px 'Cormorant Garamond', serif";
        ctx.fillText('&', width / 2, 640);

        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 78px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.bride_name, width / 2, 730);

        drawDivider(width / 2, 800, 240, palette.border);

        // Thời gian & Địa điểm
        ctx.fillStyle = palette.accent;
        ctx.font = "600 28px 'Inter', sans-serif";
        ctx.fillText(`TIỆC CƯỚI TỔ CHỨC VÀO LÚC ${weddingData.party_time}`, width / 2, 880);

        ctx.fillStyle = palette.primary;
        ctx.font = "700 36px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.wedding_date_formatted, width / 2, 940);

        ctx.fillStyle = palette.muted;
        ctx.font = "400 24px 'Inter', sans-serif";
        ctx.fillText(`(Nhằm ${weddingData.lunar_date})`, width / 2, 990);

        ctx.fillStyle = palette.accent;
        ctx.font = "600 32px 'Cormorant Garamond', serif";
        ctx.fillText(`TẠI: ${weddingData.venue_name}`, width / 2, 1090);

        ctx.fillStyle = palette.text;
        ctx.font = "400 22px 'Inter', sans-serif";
        wrapText(ctx, weddingData.venue_address, width / 2, 1140, width - 260, 34);

        if (showQrCheckbox && showQrCheckbox.checked && isQrLoaded) {
            const qrSize = 190;
            const qrX = (width - qrSize) / 2;
            const qrY = height - 420;
            drawQrWithFrame(qrX, qrY, qrSize, palette);

            ctx.fillStyle = palette.muted;
            ctx.font = "500 20px 'Inter', sans-serif";
            ctx.fillText('QUÉT MÃ ĐỂ XEM HƯỚNG DẪN ĐƯỜNG ĐI & XÁC NHẬN THAM DỰ', width / 2, height - 180);
        }
    }

    // 4. Preset: TABLE QR STANDEE
    function renderTableQrPreset(width, height, palette) {
        drawMonogram(width / 2, 220, 80, palette);

        ctx.fillStyle = palette.accent;
        ctx.font = "600 32px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '8px';
        ctx.fillText('CHÀO MỪNG QUÝ QUAN KHÁCH', width / 2, 380);

        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 84px 'Cormorant Garamond', serif";
        ctx.fillText(`${weddingData.groom_name} & ${weddingData.bride_name}`, width / 2, 500);

        ctx.fillStyle = palette.muted;
        ctx.font = "400 24px 'Inter', sans-serif";
        ctx.fillText(weddingData.wedding_date_formatted, width / 2, 570);

        drawDivider(width / 2, 640, 320, palette.border);

        // Khung QR siêu to ở trung tâm standee để khách ngồi bàn quét dễ dàng
        const qrSize = 340;
        const qrX = (width - qrSize) / 2;
        const qrY = 720;
        drawQrWithFrame(qrX, qrY, qrSize, palette);

        ctx.fillStyle = palette.primary;
        ctx.font = "700 36px 'Cormorant Garamond', serif";
        ctx.fillText('QUÉT MÃ QR TẠI BÀN TIỆC', width / 2, 1160);

        ctx.fillStyle = palette.text;
        ctx.font = "400 24px 'Inter', sans-serif";
        ctx.fillText('✦ Xem Album Ảnh Cưới & Thực Đơn Bàn Tiệc', width / 2, 1220);
        ctx.fillText('✦ Gửi Lời Chúc Phúc Trực Tiếp Lên Màn Hình Tiệc', width / 2, 1270);
        ctx.fillText('✦ Mừng Cưới Chúc Phúc Đôi Uyên Ương', width / 2, 1320);

        ctx.fillStyle = palette.accent;
        ctx.font = "italic 500 28px 'Cormorant Garamond', serif";
        ctx.fillText('Trân trọng cảm ơn sự hiện diện quý báu của Quý khách!', width / 2, 1450);
    }

    // 5. Preset: TIMELINE LỊCH TRÌNH
    function renderTimelinePreset(width, height, palette) {
        drawMonogram(width / 2, 180, 65, palette);

        ctx.fillStyle = palette.accent;
        ctx.font = "600 26px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '6px';
        ctx.fillText('LỊCH TRÌNH HÔN LỄ', width / 2, 310);

        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 68px 'Cormorant Garamond', serif";
        ctx.fillText(`${weddingData.groom_name} & ${weddingData.bride_name}`, width / 2, 400);

        drawDivider(width / 2, 460, 260, palette.border);

        // Vẽ danh sách mốc Timeline với đường nối thẳng
        const items = weddingData.timeline && weddingData.timeline.length ? weddingData.timeline : [
            { time: '10:30', title: 'Đón Tiếp Quan Khách', desc: 'Chụp ảnh lưu niệm tại Backdrop' },
            { time: '11:15', title: 'Nghi Thức Lễ Thành Hôn', desc: 'Trao nhẫn cưới & Cắt bánh hạnh phúc' },
            { time: '11:30', title: 'Khai Tiệc Mừng Hạnh Phúc', desc: 'Nâng ly chúc mừng đôi uyên ương' },
            { time: '13:00', title: 'Lời Tri Ân & Tiễn Khách', desc: 'Trao quà cảm ơn & lưu giữ kỷ niệm' }
        ];

        const startY = 560;
        const itemSpacing = 160;
        const lineX = width * 0.28;

        // Vẽ đường trục dọc nối các mốc
        ctx.strokeStyle = palette.border;
        ctx.lineWidth = 2;
        ctx.setLineDash([6, 6]);
        ctx.beginPath();
        ctx.moveTo(lineX, startY);
        ctx.lineTo(lineX, startY + (items.length - 1) * itemSpacing);
        ctx.stroke();
        ctx.setLineDash([]);

        items.forEach((item, index) => {
            const y = startY + index * itemSpacing;

            // Nút tròn mốc giờ
            ctx.fillStyle = palette.accent;
            ctx.beginPath();
            ctx.arc(lineX, y, 9, 0, Math.PI * 2);
            ctx.fill();

            ctx.strokeStyle = palette.card_bg;
            ctx.lineWidth = 3;
            ctx.stroke();

            // Giờ bên trái
            ctx.fillStyle = palette.accent;
            ctx.font = "700 32px 'Cormorant Garamond', serif";
            ctx.textAlign = 'right';
            ctx.fillText(item.time, lineX - 30, y + 8);

            // Tiêu đề & mô tả bên phải
            ctx.textAlign = 'left';
            ctx.fillStyle = palette.primary;
            ctx.font = "600 28px 'Cormorant Garamond', serif";
            ctx.fillText(item.title, lineX + 36, y);

            ctx.fillStyle = palette.muted;
            ctx.font = "400 20px 'Inter', sans-serif";
            ctx.fillText(item.desc || '', lineX + 36, y + 32);
        });

        // Chân trang
        ctx.textAlign = 'center';
        ctx.fillStyle = palette.accent;
        ctx.font = "500 22px 'Inter', sans-serif";
        ctx.fillText(`Địa Điểm: ${weddingData.venue_name}`, width / 2, height - 280);

        ctx.fillStyle = palette.muted;
        ctx.font = "400 20px 'Inter', sans-serif";
        wrapText(ctx, weddingData.venue_address, width / 2, height - 240, width - 260, 30);
    }

    // 6. Preset: THIỆP BÁO HỶ
    function renderBaoHyPreset(width, height, palette) {
        drawMonogram(width / 2, 220, 75, palette);

        ctx.fillStyle = palette.accent;
        ctx.font = "600 26px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '8px';
        ctx.fillText('THIỆP BÁO HỶ', width / 2, 380);

        ctx.fillStyle = palette.muted;
        ctx.font = "italic 400 24px 'Cormorant Garamond', serif";
        ctx.fillText('Gia đình chúng tôi trân trọng báo tin lễ thành hôn của hai con', width / 2, 440);

        // Tên Đôi Uyên Ương
        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 84px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.groom_name, width / 2, 570);

        ctx.fillStyle = palette.accent;
        ctx.font = "italic 400 44px 'Cormorant Garamond', serif";
        ctx.fillText('&', width / 2, 645);

        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 84px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.bride_name, width / 2, 740);

        drawDivider(width / 2, 810, 240, palette.border);

        ctx.fillStyle = palette.muted;
        ctx.font = "italic 400 24px 'Cormorant Garamond', serif";
        wrapText(ctx, 'Do khoảng cách địa lý và điều kiện không thể trực tiếp đón tiếp, kính mong Quý người thân, bạn bè lượng thứ và gửi lời chúc phúc cho hạnh phúc lứa đôi.', width / 2, 880, width - 280, 36);

        ctx.fillStyle = palette.accent;
        ctx.font = "700 32px 'Cormorant Garamond', serif";
        ctx.fillText(`LỄ THÀNH HÔN ĐÃ DIỄN RA VÀO NGÀY`, width / 2, 1060);

        ctx.fillStyle = palette.primary;
        ctx.font = "600 36px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.wedding_date_formatted, width / 2, 1115);

        if (showQrCheckbox && showQrCheckbox.checked && isQrLoaded) {
            const qrSize = 190;
            const qrX = (width - qrSize) / 2;
            const qrY = height - 420;
            drawQrWithFrame(qrX, qrY, qrSize, palette);

            ctx.fillStyle = palette.muted;
            ctx.font = "500 20px 'Inter', sans-serif";
            ctx.fillText('QUÉT MÃ ĐỂ XEM HÌNH ẢNH HÔN LỄ & GỬI LỜI CHÚC', width / 2, height - 180);
        }
    }

    // 7. Preset: THIỆP CẢM ƠN (THANK YOU)
    function renderThankYouPreset(width, height, palette) {
        drawMonogram(width / 2, 240, 80, palette);

        ctx.fillStyle = palette.accent;
        ctx.font = "600 32px 'Inter', sans-serif";
        ctx.textAlign = 'center';
        ctx.letterSpacing = '8px';
        ctx.fillText('LỜI CẢM ƠN CHÂN THÀNH', width / 2, 420);

        ctx.fillStyle = palette.primary;
        ctx.font = "italic 600 88px 'Cormorant Garamond', serif";
        ctx.fillText(`${weddingData.groom_name} & ${weddingData.bride_name}`, width / 2, 550);

        drawDivider(width / 2, 630, 280, palette.border);

        ctx.fillStyle = palette.text;
        ctx.font = "italic 400 26px 'Cormorant Garamond', serif";
        wrapText(ctx, '“Sự hiện diện, tình cảm và những lời chúc phúc ấm áp của Quý khách là món quà vô giá nhất đối với ngày chung đôi của chúng tôi. Chúng tôi xin gửi lời tri ân sâu sắc nhất đến toàn thể gia đình, người thân và bạn bè!”', width / 2, 730, width - 260, 42);

        if (showQrCheckbox && showQrCheckbox.checked && isQrLoaded) {
            const qrSize = 210;
            const qrX = (width - qrSize) / 2;
            const qrY = 1050;
            drawQrWithFrame(qrX, qrY, qrSize, palette);

            ctx.fillStyle = palette.accent;
            ctx.font = "600 22px 'Inter', sans-serif";
            ctx.fillText('QUÉT MÃ ĐỂ XEM LẠI PHÓNG SỰ ẢNH & KỶ NIỆM NGÀY CƯỚI', width / 2, 1330);
        }

        ctx.fillStyle = palette.muted;
        ctx.font = "italic 400 24px 'Cormorant Garamond', serif";
        ctx.fillText(weddingData.wedding_date_formatted, width / 2, height - 160);
    }

    // Helper: Vẽ đường phân cách trang nhã
    function drawDivider(centerX, y, length, color) {
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(centerX - length / 2, y);
        ctx.lineTo(centerX + length / 2, y);
        ctx.stroke();

        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(centerX, y, 4, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    // Helper: Vẽ QR Code có khung viền bo góc sang trọng
    function drawQrWithFrame(x, y, size, palette) {
        ctx.save();
        // Viền bóng
        ctx.shadowColor = 'rgba(0,0,0,0.2)';
        ctx.shadowBlur = 16;
        ctx.shadowOffsetY = 6;

        ctx.fillStyle = '#FFFFFF';
        ctx.beginPath();
        ctx.roundRect(x - 12, y - 12, size + 24, size + 24, 16);
        ctx.fill();
        ctx.restore();

        // Viền gold
        ctx.strokeStyle = palette.accent;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.roundRect(x - 12, y - 12, size + 24, size + 24, 16);
        ctx.stroke();

        ctx.drawImage(qrImage, x, y, size, size);
    }

    // Helper: Tự động ngắt dòng văn bản dài
    function wrapText(context, text, x, y, maxWidth, lineHeight) {
        const words = (text || '').split(' ');
        let line = '';
        let currentY = y;

        for (let n = 0; n < words.length; n++) {
            const testLine = line + words[n] + ' ';
            const metrics = context.measureText(testLine);
            const testWidth = metrics.width;
            if (testWidth > maxWidth && n > 0) {
                context.fillText(line.trim(), x, currentY);
                line = words[n] + ' ';
                currentY += lineHeight;
            } else {
                line = testLine;
            }
        }
        context.fillText(line.trim(), x, currentY);
    }

    // 5. Đăng ký sự kiện Tương tác (Event Listeners)

    // A. Chuyển đổi Preset Định dạng
    const presetBtns = document.querySelectorAll('.mayhen_stationery_preset_btn');
    presetBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            presetBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentPresetId = btn.getAttribute('data-preset-id') || 'story';

            const preset = presets[currentPresetId];
            if (preset && presetLabelEl) {
                presetLabelEl.textContent = preset.name;
            }

            // Cập nhật tiêu đề gợi ý phù hợp preset
            if (titleInput) {
                if (currentPresetId === 'save_the_date') titleInput.value = 'SAVE THE DATE';
                else if (currentPresetId === 'bao_hy') titleInput.value = 'THIỆP BÁO HỶ';
                else if (currentPresetId === 'thank_you') titleInput.value = 'LỜI CẢM ƠN CHÂN THÀNH';
                else if (currentPresetId === 'table_qr') titleInput.value = 'CHÀO MỪNG QUÝ QUAN KHÁCH';
                else if (currentPresetId === 'timeline') titleInput.value = 'LỊCH TRÌNH HÔN LỄ';
                else titleInput.value = 'LỜI MỜI TRÂN QUÝ';
            }

            renderCanvas();
        });
    });

    // B. Chuyển đổi Palette Màu Sắc
    const paletteBtns = document.querySelectorAll('.mayhen_stationery_palette_item');
    paletteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paletteBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentPaletteId = btn.getAttribute('data-palette-id') || 'velvet_crimson';
            renderCanvas();
        });
    });

    // C. Gõ nội dung thay đổi tức thời (Real-time debounced)
    let debounceTimer = null;
    const triggerDebounceRender = () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            renderCanvas();
        }, 80);
    };

    if (titleInput) titleInput.addEventListener('input', triggerDebounceRender);
    if (quoteInput) quoteInput.addEventListener('input', triggerDebounceRender);
    if (showQrCheckbox) showQrCheckbox.addEventListener('change', renderCanvas);

    // D. Tải Ảnh PNG Độ Nét Cao
    const downloadBtn = document.getElementById('mayhen_stationery_download_btn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
            const fileName = `mayhen_${currentPresetId}_${weddingData.groom_name}_${weddingData.bride_name}.png`.toLowerCase().replace(/\s+/g, '_');
            
            canvas.toBlob(blob => {
                if (!blob) {
                    showToast('Không thể tạo tệp ảnh. Vui lòng thử lại.', 'error');
                    return;
                }

                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                showToast('Đã tải ảnh chất lượng cao 2K thành công!');

                // Ghi nhận log phía server ngầm
                if (logUrl) {
                    fetch(logUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                        body: JSON.stringify({
                            preset: currentPresetId,
                            palette: currentPaletteId,
                            format: 'png',
                        }),
                    }).catch(() => {});
                }
            }, 'image/png');
        });
    }

    // E. Sao chép ảnh vào Clipboard (Hỗ trợ dán ngay vào Zalo / Messenger)
    const copyBtn = document.getElementById('mayhen_stationery_copy_btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            if (!navigator.clipboard || !window.ClipboardItem) {
                showToast('Trình duyệt của bạn không hỗ trợ sao chép ảnh trực tiếp. Vui lòng chọn "Tải Ảnh".', 'error');
                return;
            }

            try {
                canvas.toBlob(async blob => {
                    if (!blob) return;
                    await navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]);
                    showToast('Đã sao chép ảnh vào bộ nhớ tạm! Bạn có thể dán ngay vào Zalo hoặc Messenger.');
                }, 'image/png');
            } catch (err) {
                console.error('Clipboard copy failed:', err);
                showToast('Không thể sao chép ảnh. Vui lòng dùng nút "Tải Ảnh".', 'error');
            }
        });
    }

    // F. In ấn khổ giấy chuẩn
    const printBtn = document.getElementById('mayhen_stationery_print_btn');
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }

    // Khởi tạo render lần đầu
    renderCanvas();
}

// Tự động khởi chạy khi DOM sẵn sàng
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStationeryStudio);
} else {
    initStationeryStudio();
}

