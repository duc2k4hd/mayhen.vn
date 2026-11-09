/**
 * MAYHEN.VN — NATIVE CRAFTING PRESENTER V3
 * Trình diễn hiệu ứng xuất hiện tiến trình (Progressive Live Crafting)
 * Từng thẻ, từng hàng, từng phần tử từ trên xuống dưới, từ trái sang phải.
 * Scroll chính xác theo điểm ngọt thị giác, hiệu ứng ánh sáng hoàng gia lộng lẫy.
 * 100% Pure Native Vanilla JS, Zero External Libraries.
 *
 * QUAN TRỌNG — KIẾN TRÚC CHỐNG FLASH-OF-CONTENT:
 *   • Layout CSS trong invitation.blade.php ẩn toàn bộ section ngay từ <head> khi ?crafting=1
 *   • Màn rèm Atelier (#mayhenCraftingCurtain) phủ toàn màn hình cho đến khi DOM đã sẵn sàng
 *   • Presenter này bóc rèm, rồi mới bắt đầu dệt từng phần tử theo thứ tự thị giác
 */
(() => {
    // Chỉ kích hoạt khi có tham số ?crafting=1 trên URL
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('crafting')) {
        return;
    }

    // Hàm hạ rèm atelier (khi kết thúc hoặc bỏ qua)
    const liftCurtain = () => {
        const curtain = document.getElementById('mayhenCraftingCurtain');
        if (curtain) {
            curtain.classList.add('is-hidden');
            // Xoá hoàn toàn sau animation để không che nội dung
            setTimeout(() => curtain.remove(), 800);
        }
        // Khôi phục visibility cho tất cả section — chúng vẫn còn opacity:0
        // vì presenter sẽ kiểm soát từng phần tử theo thứ tự
        document.querySelectorAll(
            'body.mayhen_is_crafting .mayhen_invitation_section, ' +
            'body.mayhen_is_crafting section, ' +
            'body.mayhen_is_crafting footer, ' +
            'body.mayhen_is_crafting [data-mayhen-section]'
        ).forEach(el => {
            el.style.removeProperty('opacity');
            el.style.removeProperty('visibility');
            el.style.removeProperty('pointer-events');
        });
    };

    // Tôn trọng thiết lập giảm chuyển động của hệ điều hành
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        liftCurtain();
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

    // Tiêm style hiệu ứng chuyển động và vệt sáng hoàng gia lộng lẫy
    const styleEl = document.createElement('style');
    styleEl.id = 'mayhen-native-crafting-styles';
    styleEl.textContent = `
        .mayhen_crafting_unit {
            position: relative;
            transition: opacity 0.62s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.62s cubic-bezier(0.16, 1, 0.3, 1),
                        filter 0.48s ease;
            will-change: opacity, transform, filter;
        }
        .mayhen_crafting_unit.mayhen_is_hidden {
            opacity: 0 !important;
            transform: translateY(20px) scale(0.985) !important;
            filter: blur(2px) !important;
            pointer-events: none !important;
        }
        .mayhen_crafting_unit.mayhen_is_revealed {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
            filter: blur(0) !important;
            pointer-events: auto !important;
        }
        .mayhen_crafting_unit.mayhen_crafting_glow {
            box-shadow: 0 0 22px rgba(207, 168, 96, 0.42) !important;
            transition: box-shadow 0.75s ease;
        }
        .mayhen_crafting_shimmer_beam {
            position: absolute;
            top: 0;
            left: -120%;
            width: 80%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), rgba(207, 168, 96, 0.65), transparent);
            transform: skewX(-22deg);
            pointer-events: none;
            z-index: 99;
            animation: mayhenBeamSweep 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes mayhenBeamSweep {
            0% { left: -120%; opacity: 0; }
            40% { opacity: 0.9; }
            100% { left: 160%; opacity: 0; }
        }
    `;
    document.head.appendChild(styleEl);

    // Bản đồ cấu hình bóc tách chi tiết từng phần tử theo 7 Section chuẩn mực
    const SECTION_RULES = [
        {
            key: 'hero',
            title: 'Trang Bìa Chính',
            container: '[data-mayhen-section*="hero"], .mayhen_invitation_hero, section.mayhen_invitation_sr_dau',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_kicker, .mayhen_invitation_hero_kicker, [data-sync-target="ceremony_type"]', label: '✦ Đang tạo biểu trưng & loại hình hôn lễ' },
                { sel: '.mayhen_invitation_sr_quy_dao, .mayhen_invitation_hero_media, .mayhen_invitation_img_dau', label: '✦ Đang mở khung ảnh chân dung uyên ương' },
                { sel: '.mayhen_invitation_t1, .mayhen_invitation_sr_ten_cua.mayhen_invitation_t1, [data-sync-target="groom_name"]', label: '✦ Đang dệt tên Chú Rể' },
                { sel: '.mayhen_invitation_sr_va, .mayhen_invitation_va, .mayhen_invitation_sr_va_cua', label: '✦ Đang kết nối tơ duyên song hỷ' },
                { sel: '.mayhen_invitation_t2, .mayhen_invitation_sr_ten_cua.mayhen_invitation_t2, [data-sync-target="bride_name"]', label: '✦ Đang dệt tên Cô Dâu' },
                { sel: '.mayhen_invitation_sr_ngay_chinh, [data-sync-target="wedding_date_digits"], [data-sync-target="wedding_day"]', label: '✦ Đang khắc ghi ngày cưới dương lịch' },
                { sel: '.mayhen_invitation_sr_am_chinh, [data-sync-target="wedding_lunar_date"]', label: '✦ Đang kính báo ngày lành tháng tốt âm lịch' }
            ]
        },
        {
            key: 'story',
            title: 'Lời Ngỏ & Chuyện Tình',
            container: '[data-mayhen-section*="story"], .mayhen_invitation_story',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_dau_muc, .mayhen_invitation_section_header', label: '✦ Đang mở chương Chuyện Tình Yêu' },
                { sel: '.mayhen_invitation_sr_the.mayhen_invitation_kieu_vom, .mayhen_invitation_quote_box, [data-sync-target="couple_quote"]', label: '✦ Đang biên soạn trích dẫn tâm tình' },
                { sel: '.mayhen_invitation_sr_cau_chuyen, [data-sync-target="story_content"]', label: '✦ Đang dệt câu chuyện tình yêu lắng đọng' }
            ]
        },
        {
            key: 'parents',
            title: 'Song Thân & Đôi Uyên Ương',
            container: '[data-mayhen-section*="parents"], .mayhen_invitation_parents, .mayhen_invitation_family',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_dau_muc, .mayhen_invitation_section_header', label: '✦ Đang kính báo Song Thân Phụ Mẫu' },
                { sel: '.mayhen_invitation_ben[data-ben="trai"], [data-mayhen-section="groom_family"]', label: '✦ Đang trang trọng đại diện Nhà Trai' },
                { sel: '.mayhen_invitation_ben[data-ben="phai"], [data-mayhen-section="bride_family"]', label: '✦ Đang trang trọng đại diện Nhà Gái' },
                { sel: '.mayhen_invitation_sr_the_cap, .mayhen_invitation_couple_box', label: '✦ Đang giới thiệu đôi uyên ương' }
            ]
        },
        {
            key: 'countdown',
            title: 'Đếm Ngược & Lịch Tháng',
            container: '[data-mayhen-section*="countdown"], .mayhen_invitation_countdown_calendar, .mayhen_invitation_calendar',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_dau_muc, .mayhen_invitation_section_header', label: '✦ Đang đếm ngược từng khoảnh khắc' },
                { sel: '.mayhen_invitation_tmx_o:nth-child(1), .mayhen_invitation_countdown_item:nth-child(1)', label: '✦ Khởi tạo ngày đếm ngược' },
                { sel: '.mayhen_invitation_tmx_o:nth-child(2), .mayhen_invitation_countdown_item:nth-child(2)', label: '✦ Khởi tạo giờ đếm ngược' },
                { sel: '.mayhen_invitation_tmx_o:nth-child(3), .mayhen_invitation_countdown_item:nth-child(3)', label: '✦ Khởi tạo phút đếm ngược' },
                { sel: '.mayhen_invitation_tmx_o:nth-child(4), .mayhen_invitation_countdown_item:nth-child(4)', label: '✦ Khởi tạo giây đếm ngược' },
                { sel: '.mayhen_invitation_sr_lich, .mayhen_invitation_calendar_grid', label: '✦ Đang đính kết lịch cưới Save The Date' }
            ]
        },
        {
            key: 'events',
            title: 'Thông Tin Sự Kiện & Bản Đồ',
            container: '[data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"], .mayhen_invitation_events',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_dau_muc, .mayhen_invitation_section_header', label: '✦ Đang sắp đặt không gian hôn lễ' },
                { sel: '.mayhen_invitation_sr_the_sk:first-of-type, .mayhen_invitation_event_card:first-of-type', label: '✦ Đang chuẩn bị nghi thức Lễ Vu Quy' },
                { sel: '.mayhen_invitation_sr_the_sk:nth-of-type(2), .mayhen_invitation_event_card:nth-of-type(2)', label: '✦ Đang chuẩn bị Tiệc Cưới chính' },
                { sel: '.mayhen_invitation_sr_the_bando, .mayhen_invitation_map_card', label: '✦ Đang kết nối bản đồ định vị chỉ đường' }
            ]
        },
        {
            key: 'timeline',
            title: 'Lịch Trình Hôn Lễ',
            container: '[data-mayhen-section*="timeline"], .mayhen_invitation_timeline',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_dau_muc, .mayhen_invitation_section_header', label: '✦ Đang dàn trang lịch trình hôn lễ' },
                { sel: '.mayhen_invitation_sr_moc:nth-child(1), .mayhen_invitation_timeline_item:nth-child(1)', label: '✦ Mốc 1: Đón tiếp quan khách' },
                { sel: '.mayhen_invitation_sr_moc:nth-child(2), .mayhen_invitation_timeline_item:nth-child(2)', label: '✦ Mốc 2: Cử hành hôn lễ' },
                { sel: '.mayhen_invitation_sr_moc:nth-child(3), .mayhen_invitation_timeline_item:nth-child(3)', label: '✦ Mốc 3: Khai tiệc mừng' },
                { sel: '.mayhen_invitation_sr_moc:nth-child(4), .mayhen_invitation_timeline_item:nth-child(4)', label: '✦ Mốc 4: Chụp hình kỷ niệm & Tiễn khách' }
            ]
        },
        {
            key: 'album',
            title: 'Khoảnh Khắc Hạnh Phúc (Album 10 Ảnh)',
            container: '[data-mayhen-section*="album"], [data-mayhen-section*="gallery"], .mayhen_invitation_gallery',
            unitSelectors: [
                { sel: '.mayhen_invitation_sr_dau_muc, .mayhen_invitation_section_header', label: '✦ Đang tuyển chọn album 10 khoảnh khắc hạnh phúc' },
                { sel: '.mayhen_invitation_tmx_album_cho, .mayhen_invitation_gallery_wrap, .mayhen_invitation_wb_cf', label: '✦ Dàn trang thước phim kỷ niệm tình yêu' },
                { sel: '.mayhen_invitation_curated_footer', label: '✦ Hoàn thiện tuyệt tác thiệp cưới độc bản!' }
            ]
        }
    ];

    let isCompleted = false;
    let userInterrupted = false;

    // Ngừng auto-scroll nếu người dùng chủ động chạm/cuộn màn hình
    const onUserScrollOrTouch = () => {
        userInterrupted = true;
    };
    window.addEventListener('wheel', onUserScrollOrTouch, { passive: true, once: true });
    window.addEventListener('touchstart', onUserScrollOrTouch, { passive: true, once: true });
    window.addEventListener('keydown', (e) => {
        if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Space'].includes(e.code)) {
            userInterrupted = true;
        }
    }, { passive: true, once: true });

    // Thu thập các phần tử chi tiết từ DOM
    const collectedUnits = [];

    SECTION_RULES.forEach((rule) => {
        const containerEl = document.querySelector(rule.container);
        if (!containerEl) return;

        let sectionHasSpecificUnits = false;

        rule.unitSelectors.forEach((unitSel) => {
            const els = containerEl.querySelectorAll(unitSel.sel);
            els.forEach((el) => {
                if (el && !collectedUnits.some(u => u.element === el || el.contains(u.element))) {
                    collectedUnits.push({
                        sectionKey: rule.key,
                        sectionTitle: rule.title,
                        label: unitSel.label,
                        element: el,
                    });
                    sectionHasSpecificUnits = true;
                }
            });
        });

        // Nếu section không tìm thấy selector con chi tiết, lấy chính section làm 1 đơn vị
        if (!sectionHasSpecificUnits && !collectedUnits.some(u => u.element === containerEl)) {
            collectedUnits.push({
                sectionKey: rule.key,
                sectionTitle: rule.title,
                label: `✦ Đang hoàn thiện ${rule.title}`,
                element: containerEl,
            });
        }
    });

    if (collectedUnits.length === 0) {
        liftCurtain();
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

    // Sắp xếp các phần tử theo đúng quy luật thị giác:
    // Từ TRÊN XUỐNG DƯỚI (top), và nếu cùng hàng (top chênh lệch <= 24px) thì từ TRÁI SANG PHẢI (left)
    collectedUnits.forEach((u) => {
        const rect = u.element.getBoundingClientRect();
        const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        const scrollX = window.pageXOffset || document.documentElement.scrollLeft || 0;
        u.top = rect.top + scrollY;
        u.left = rect.left + scrollX;
    });

    collectedUnits.sort((a, b) => {
        const diffTop = a.top - b.top;
        if (Math.abs(diffTop) > 24) {
            return diffTop;
        }
        return a.left - b.left;
    });

    // Ẩn tất cả các phần tử ban đầu
    collectedUnits.forEach((u) => {
        u.element.classList.add('mayhen_crafting_unit', 'mayhen_is_hidden');
    });

    // Bóc rèm Atelier — presenter đã sẵn sàng, section đã bị ẩn từng phần tử
    // → hạ rèm phủ màn hình → trả về nền thiệp sạch → bắt đầu dệt tuần tự
    liftCurtain();

    // Hàm tiết lộ tất cả ngay lập tức (khi Bỏ qua hoặc Kết thúc)
    const revealAllInstantly = () => {
        if (isCompleted) return;
        isCompleted = true;

        collectedUnits.forEach((u) => {
            u.element.classList.remove('mayhen_is_hidden', 'mayhen_crafting_glow');
            u.element.classList.add('mayhen_is_revealed');
            const beam = u.element.querySelector('.mayhen_crafting_shimmer_beam');
            if (beam) beam.remove();
        });

        window.parent?.postMessage({
            type: 'MAYHEN_CRAFTING_COMPLETE',
            total: collectedUnits.length
        }, '*');

        // Dọn dẹp các class và style tạm
        setTimeout(() => {
            collectedUnits.forEach((u) => {
                u.element.classList.remove('mayhen_crafting_unit', 'mayhen_is_revealed', 'mayhen_is_hidden');
            });
            styleEl.remove();
        }, 800);
    };

    // Lắng nghe lệnh Skip từ topbar trang cha
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
            revealAllInstantly();
        }
        // Xử lý Replay — reset toàn bộ về trạng thái ban đầu và dệt lại từ đầu
        if (e.data.type === 'MAYHEN_REPLAY_CRAFTING') {
            if (!isCompleted) return; // đang dệt dở thì bỏ qua
            isCompleted = false;
            currentIndex = 0;
            userInterrupted = false;
            window.scrollTo({ top: 0, behavior: 'auto' });
            collectedUnits.forEach((u) => {
                u.element.classList.remove('mayhen_is_revealed');
                u.element.classList.add('mayhen_is_hidden');
            });
            setTimeout(revealNextUnit, 500);
        }
    });

    // Cuộn màn hình CHÍNH XÁC theo điểm ngọt thị giác
    const performPreciseScroll = (targetEl) => {
        if (userInterrupted) return;

        const rect = targetEl.getBoundingClientRect();
        const viewportH = window.innerHeight || 667;

        // Điểm an toàn thoải mái trong tầm mắt (từ 12% đến 76% chiều cao màn hình)
        const inComfortZone = rect.top >= (viewportH * 0.12) && rect.bottom <= (viewportH * 0.78);

        // Nếu phần tử đã nằm trong vùng nhìn thấy (ví dụ 4 ô countdown nằm liền nhau trên 1 hàng)
        // -> KHÔNG cuộn để người xem chiêm ngưỡng hiệu ứng từ trái sang phải mượt mà!
        if (inComfortZone) {
            return;
        }

        // Đưa đỉnh phần tử về vị trí 26% - 30% viewport (sweet spot khi đọc thiệp trên điện thoại)
        const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        const targetScrollY = currentScrollY + rect.top - (viewportH * 0.26);

        window.scrollTo({
            top: Math.max(0, targetScrollY),
            behavior: 'smooth'
        });
    };

    // Chuỗi hoạt họa xuất hiện tuần tự
    let currentIndex = 0;
    const baseDuration = 480; // Nhịp độ êm ái, chuyên nghiệp (~480ms mỗi phần tử)

    const revealNextUnit = () => {
        if (isCompleted) return;

        if (currentIndex >= collectedUnits.length) {
            revealAllInstantly();
            return;
        }

        const unit = collectedUnits[currentIndex];
        const el = unit.element;

        // 1. Kích hoạt xuất hiện
        el.classList.remove('mayhen_is_hidden');
        el.classList.add('mayhen_is_revealed', 'mayhen_crafting_glow');

        // 2. Thêm vệt sáng hoàng gia lướt nhẹ từ trái sang phải
        const beam = document.createElement('div');
        beam.className = 'mayhen_crafting_shimmer_beam';
        el.appendChild(beam);
        setTimeout(() => beam.remove(), 800);

        // 3. Gỡ bỏ hiệu ứng phát sáng sau 750ms để trả về vẻ đẹp thuần khiết
        setTimeout(() => {
            el.classList.remove('mayhen_crafting_glow');
        }, 750);

        // 4. Cuộn màn hình chính xác
        performPreciseScroll(el);

        // 5. Gửi thông điệp tiến trình chi tiết cho topbar Preview Studio
        const percent = Math.min(100, Math.round(((currentIndex + 1) / collectedUnits.length) * 100));
        window.parent?.postMessage({
            type: 'MAYHEN_CRAFTING_STEP',
            section: unit.sectionKey,
            sectionTitle: unit.sectionTitle,
            detailTitle: unit.label,
            step: currentIndex + 1,
            total: collectedUnits.length,
            percent: percent,
        }, '*');

        currentIndex++;

        // Tính toán độ trễ nhịp điệu: các ô nhỏ liền kề lướt nhanh hơn (~360ms), các card lớn chậm hơn (~580ms)
        const isSmallElement = el.tagName === 'SPAN' || el.tagName === 'H1' || el.tagName === 'H2' || el.tagName === 'H3' || el.classList.contains('mayhen_invitation_tmx_o');
        const nextDelay = isSmallElement ? 380 : 540;

        setTimeout(revealNextUnit, nextDelay);
    };

    // Khởi chạy trình diễn mượt mà sau khi tài nguyên sẵn sàng
    // Delay 650ms để rèm Atelier kịp fade-out trước khi phần tử đầu tiên xuất hiện
    if (document.readyState === 'complete') {
        setTimeout(revealNextUnit, 650);
    } else {
        window.addEventListener('load', () => setTimeout(revealNextUnit, 650));
    }
})();
