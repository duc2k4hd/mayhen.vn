/**
 * MAYHEN.VN — NATIVE CRAFTING PRESENTER V8 (EDITORIAL ATELIER EDITION)
 * Trình diễn dệt thiệp AI chậm rãi, sâu lắng, chuẩn xác từng phần tử (40s - 1 phút)
 * Từng thẻ, từng hàng, từng phần tử từ trên xuống dưới, từ trái sang phải.
 *
 * ĐẶC TÍNH NGHỆ THUẬT V8:
 * 1. DELIBERATE CRAFTING CADENCE: Chu kỳ dệt thiệp kéo dài ~45-50 giây (ít nhất 40s - 1 phút),
 *    mỗi phần tử xuất hiện chậm rãi ~1.6s - 2.2s, tạo cảm giác một nghệ nhân / AI cao cấp đang tỉ mỉ từng chi tiết.
 * 2. SYNCHRONIZED ROYAL LIGHT SWEEP: Vệt sáng vàng kim hoàng gia 1.1s quét đến đâu, clip-path mở lộ phần tử đến đó.
 * 3. ATELIER SAVOR PAUSE: Sau khi quét xong mỗi phần tử, dừng nghỉ nhẹ nhàng 500-800ms để người xem chiêm ngưỡng.
 * 4. FINE-GRAINED ATOMIC UNITS: Phân giải chi tiết 25-35 phần tử độc lập trên toàn bộ 4 mẫu gốc.
 * 5. BULLETPROOF FAIL-SAFE & ZERO FOUC: 100% phần tử hiển thị toàn vẹn, chống crash DOM void element, có nút Bỏ qua và Xem lại.
 *
 * 100% Pure Native Vanilla JS, Zero External Libraries.
 */
(() => {
    // Chỉ kích hoạt khi có tham số ?crafting=1 trên URL
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('crafting')) {
        return;
    }

    if (window.__MAYHEN_CRAFTING_BOOTED) {
        return;
    }
    window.__MAYHEN_CRAFTING_BOOTED = true;

    // CẤU HÌNH NHỊP ĐỘ DỆT THIỆP CHUẨN MỰC (40S - 1 PHÚT)
    const TARGET_TOTAL_DURATION_MS = 48000; // Mục tiêu ~48 giây toàn bộ bản thiệp
    const MIN_STEP_DELAY_MS = 1450;        // Nhịp bước tối thiểu 1.45s
    const MAX_STEP_DELAY_MS = 2500;        // Nhịp bước tối đa 2.5s
    const SWEEP_ANIMATION_MS = 1100;       // Thời gian vệt sáng hoàng gia lướt qua phần tử (1.1s)

    const liftCurtain = () => {
        const curtain = document.getElementById('mayhenCraftingCurtain');
        if (curtain) {
            curtain.style.transition = 'opacity 0.25s ease';
            curtain.style.opacity = '0';
            setTimeout(() => {
                if (curtain.parentNode) curtain.remove();
            }, 280);
        }
    };

    // Tôn trọng thiết lập giảm chuyển động của hệ điều hành
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        liftCurtain();
        document.documentElement.classList.remove('mayhen_crafting_preparing');
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

    // Danh sách thẻ void không thể append child trực tiếp
    const VOID_TAGS = new Set(['IMG', 'INPUT', 'HR', 'BR', 'SOURCE', 'TRACK', 'WBR', 'AREA', 'EMBED']);
    const attachBeamSafely = (targetEl, beamEl) => {
        if (!targetEl) return null;
        let host = targetEl;
        if (VOID_TAGS.has(targetEl.tagName.toUpperCase())) {
            host = targetEl.parentElement || targetEl;
        }
        if (host && !VOID_TAGS.has(host.tagName.toUpperCase())) {
            const comp = window.getComputedStyle(host).position;
            if (comp === 'static') {
                host.style.position = 'relative';
            }
            host.appendChild(beamEl);
            return beamEl;
        }
        return null;
    };

    // Tiêm style hiệu ứng chuyển động và vệt sáng hoàng gia lộng lẫy V8
    const styleEl = document.createElement('style');
    styleEl.id = 'mayhen-native-crafting-styles-v8';
    styleEl.textContent = `
        /* 1. KHÓA TRIỆT ĐỂ CÁC SECTION CHƯA TẠO: TUYỆT ĐỐI KHÔNG HIỂN THỊ TRƯỚC */
        html.mayhen_is_crafting .mayhen_crafting_section_waiting,
        body.mayhen_is_crafting .mayhen_crafting_section_waiting {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            max-height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            transition: opacity 0.5s ease;
        }
        html.mayhen_is_crafting .mayhen_crafting_section_active,
        body.mayhen_is_crafting .mayhen_crafting_section_active {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        /* 2. KHÓA ẨN CÁC PHẦN TỬ CON CHƯA TỚI LƯỢT DỆT */
        .mayhen_crafting_unit {
            position: relative;
            will-change: clip-path, opacity, transform;
        }
        .mayhen_crafting_unit.mayhen_is_hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            clip-path: polygon(0 0, 0 0, 0 100%, 0 100%) !important;
        }

        /* 3. HIỆU ỨNG VỆT SÁNG QUÉT TỚI ĐÂU, PHẦN TỬ LỘ RA CHẬM RÃI VÀ DỊU ÊM ĐẾN ĐÓ (1.1s) */
        .mayhen_crafting_unit.mayhen_is_sweeping {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            animation: mayhenSweepReveal 1.1s cubic-bezier(0.22, 1, 0.36, 1) forwards !important;
        }
        @keyframes mayhenSweepReveal {
            0% {
                clip-path: polygon(0 0, 0 0, 0 100%, 0 100%);
                transform: translateY(6px);
                opacity: 0.15;
            }
            100% {
                clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
                transform: translateY(0);
                opacity: 1;
            }
        }

        .mayhen_crafting_unit.mayhen_is_revealed {
            opacity: 1 !important;
            visibility: visible !important;
            clip-path: none !important;
            pointer-events: auto !important;
            transform: none !important;
            transition: opacity 0.35s ease, transform 0.35s ease;
        }

        /* 4. VỆT SÁNG HOÀNG GIA LỘNG LẪY (ROYAL GOLD SHIMMER BEAM 1.1s) */
        .mayhen_crafting_shimmer_beam {
            position: absolute;
            top: -20%;
            left: -20%;
            width: 95px;
            height: 140%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(207, 168, 96, 0.15) 15%, 
                rgba(255, 235, 175, 0.95) 45%, 
                #FFFFFF 58%, 
                rgba(255, 235, 175, 0.95) 70%, 
                rgba(207, 168, 96, 0.3) 85%, 
                transparent 100%
            );
            box-shadow: 0 0 18px #FFFFFF, 0 0 36px rgba(255, 225, 140, 0.95), 0 0 65px rgba(207, 168, 96, 0.75), 0 0 95px rgba(207, 168, 96, 0.35);
            transform: skewX(-20deg);
            pointer-events: none;
            z-index: 999;
            animation: mayhenBeamTrack 1.1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .mayhen_crafting_shimmer_beam::after {
            content: '✦';
            position: absolute;
            top: 50%;
            left: 55%;
            transform: translate(-50%, -50%);
            color: #FFFDF9;
            font-size: 14px;
            line-height: 1;
            text-shadow: 0 0 8px #FFFFFF, 0 0 18px #FFE29F;
            opacity: 0.95;
        }
        @keyframes mayhenBeamTrack {
            0% {
                left: -20%;
                opacity: 0;
            }
            15% {
                opacity: 1;
            }
            85% {
                opacity: 1;
            }
            100% {
                left: 108%;
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(styleEl);

    // Bản đồ bóc tách chi tiết từng đơn vị mỹ thuật nguyên tử hỗ trợ cả 4 mẫu gốc
    const SECTION_RULES = [
        {
            key: 'hero',
            title: 'Trang Bìa Chính',
            container: '[data-mayhen-section*="hero"], .mayhen_invitation_hero, section.mayhen_invitation_sr_dau',
            unitSelectors: [
                { sel: '.mayhen_invitation_hero_badge, .mayhen_invitation_sr_kicker, .mayhen_invitation_hero__badge, .mayhen_invitation_hero_kicker, [data-sync-target="ceremony_type"]', label: '✦ Phân tích phong cách & dệt biểu trưng hôn lễ' },
                { sel: '[data-sync-target="groom_name"], .mayhen_invitation_ladi_headline_name_groom, .mayhen_invitation_sr_ten_cap .mayhen_invitation_t1, .mayhen_invitation_hero_couple_groom', label: '✦ Nắn nót typography tên Chú Rể' },
                { sel: '.mayhen_invitation_sr_va, .mayhen_invitation_hero_amp, .mayhen_invitation_crest, .mayhen_invitation_hero__ornament, .mayhen_invitation_sr_va_cua', label: '✦ Dệt huy hiệu gắn kết đôi uyên ương' },
                { sel: '[data-sync-target="bride_name"], .mayhen_invitation_ladi_headline_name_bride, .mayhen_invitation_sr_ten_cap .mayhen_invitation_t2, .mayhen_invitation_hero_couple_bride', label: '✦ Hoạ nét typography tên Cô Dâu dịu dàng' },
                { sel: '.mayhen_invitation_hero_couple, .mayhen_invitation_hero__names, [data-sync-target="couple_names"]', label: '✦ Định vị bố cục tên Đôi Uyên Ương' },
                { sel: '.mayhen_invitation_hero_frame, .mayhen_invitation_sr_quy_dao, .mayhen_invitation_hero_media, .mayhen_invitation_hero__frame, .mayhen_invitation_hero__photo_box, .mayhen_invitation_img_dau', label: '✦ Mở cổng vòm chân dung hạnh phúc' },
                { sel: '[data-sync-target="wedding_date_digits"], .mayhen_invitation_sr_ngay_dau, .mayhen_invitation_hero_date_pill, .mayhen_invitation_sr_ngay_chinh', label: '✦ Khắc ghi thời khắc hoàng đạo dương lịch' },
                { sel: '[data-sync-target="wedding_lunar_date"], .mayhen_invitation_sr_am_dau, .mayhen_invitation_lunar_note, .mayhen_invitation_sr_am_chinh', label: '✦ Định ngày lành cát tường âm lịch' },
                { sel: '.mayhen_invitation_sr_cau_dau, .mayhen_invitation_hero_slogan, .mayhen_invitation_ladi_paragraph_slogan', label: '✦ Đề tựa khúc ca tình yêu đôi lứa' }
            ]
        },
        {
            key: 'story',
            title: 'Lời Ngỏ & Chuyện Tình',
            container: '[data-mayhen-section*="story"], .mayhen_invitation_story',
            unitSelectors: [
                { sel: '.mayhen_invitation_story .mayhen_invitation_section_header, .mayhen_invitation_story .mayhen_invitation_sr_dau_muc, .mayhen_invitation_story__header, .mayhen_invitation_story_kicker', label: '✦ Khởi mở Lời Ngỏ & Chuyện Tình Yêu' },
                { sel: '[data-sync-target="story_title"], .mayhen_invitation_story_title, .mayhen_invitation_story__title, .mayhen_invitation_story .mayhen_invitation_sr_tieu_de', label: '✦ Tựa đề: Chuyện Tình Yêu Lắng Đọng' },
                { sel: '.mayhen_invitation_quote_box, .mayhen_invitation_story .mayhen_invitation_sr_the, .mayhen_invitation_story__quote_box, .mayhen_invitation_story__quote', label: '✦ Dựng khung vòm mỹ thuật trích dẫn' },
                { sel: '[data-sync-target="couple_quote"], .mayhen_invitation_quote_text, .mayhen_invitation_story_quote_p', label: '✦ AI chắt lọc câu trích dẫn tâm tình' },
                { sel: '.mayhen_invitation_triptych, .mayhen_invitation_story__photo_frame, .mayhen_invitation_story_media, .mayhen_invitation_story__photo', label: '✦ Tuyển chọn thước phim kỷ niệm thanh xuân' },
                { sel: '[data-sync-target="story_content"], .mayhen_invitation_story_content, .mayhen_invitation_sr_cau_chuyen, .mayhen_invitation_story__text', label: '✦ Chắp bút câu chuyện tình yêu lắng đọng' }
            ]
        },
        {
            key: 'parents',
            title: 'Song Thân & Đôi Uyên Ương',
            container: '[data-mayhen-section*="parents"], [data-mayhen-section*="family"], .mayhen_invitation_parents, .mayhen_invitation_family, .mayhen_invitation_family_section',
            unitSelectors: [
                { sel: '.mayhen_invitation_parents .mayhen_invitation_section_header, .mayhen_invitation_family .mayhen_invitation_sr_dau_muc, .mayhen_invitation_parents__header, .mayhen_invitation_family__header', label: '✦ Kính báo Song Thân Phụ Mẫu hai bên' },
                { sel: '.mayhen_invitation_family_card:first-of-type, .mayhen_invitation_ben[data-ben="trai"], [data-mayhen-section*="groom_family"], .mayhen_invitation_family__side--groom, .mayhen_invitation_parents_card:first-of-type', label: '✦ Trang trọng đại diện Nhà Trai' },
                { sel: '.mayhen_invitation_family_card:last-of-type, .mayhen_invitation_ben[data-ben="phai"], [data-mayhen-section*="bride_family"], .mayhen_invitation_family__side--bride, .mayhen_invitation_parents_card:last-of-type', label: '✦ Kính cẩn giới thiệu Nhà Gái' },
                { sel: '.mayhen_invitation_announce_box, .mayhen_invitation_sr_the_cap, .mayhen_invitation_couple_box, .mayhen_invitation_couple_card', label: '✦ Giới thiệu Đôi Bạn Trẻ' },
                { sel: '.mayhen_invitation_couple_row:first-of-type, .mayhen_invitation_couple_list > *:first-child, .mayhen_invitation_groom_profile', label: '✦ Tự bạch Chú Rể đĩnh đạc' },
                { sel: '.mayhen_invitation_couple_row:last-of-type, .mayhen_invitation_couple_list > *:last-child, .mayhen_invitation_bride_profile', label: '✦ Tự bạch Cô Dâu dịu dàng' }
            ]
        },
        {
            key: 'countdown',
            title: 'Đếm Ngược & Lịch Tháng',
            container: '[data-mayhen-section*="countdown"], .mayhen_invitation_countdown_calendar, .mayhen_invitation_calendar',
            unitSelectors: [
                { sel: '.mayhen_invitation_countdown_calendar .mayhen_invitation_section_header, .mayhen_invitation_calendar .mayhen_invitation_sr_dau_muc, .mayhen_invitation_calendar__header', label: '✦ Đếm ngược từng khoảnh khắc chung đôi' },
                { sel: '.mayhen_invitation_calendar_card, .mayhen_invitation_sr_lich, .mayhen_invitation_calendar_grid, .mayhen_invitation_calendar__wrap, .mayhen_invitation_calendar_tear_box', label: '✦ Đính kết lịch cưới Save The Date' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(1), .mayhen_invitation_tmx_o:nth-child(1), .mayhen_invitation_cd_item:nth-child(1), .mayhen_invitation_cd_pill_box:nth-child(1), .mayhen_invitation_countdown_item:nth-child(1)', label: '✦ Khởi tạo nhịp đếm: Ngày' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(2), .mayhen_invitation_tmx_o:nth-child(2), .mayhen_invitation_cd_item:nth-child(2), .mayhen_invitation_cd_pill_box:nth-of-type(2), .mayhen_invitation_countdown_item:nth-child(2)', label: '✦ Khởi tạo nhịp đếm: Giờ' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(3), .mayhen_invitation_tmx_o:nth-child(3), .mayhen_invitation_cd_item:nth-child(3), .mayhen_invitation_cd_pill_box:nth-of-type(3), .mayhen_invitation_countdown_item:nth-child(3)', label: '✦ Khởi tạo nhịp đếm: Phút' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(4), .mayhen_invitation_tmx_o:nth-child(4), .mayhen_invitation_cd_item:nth-child(4), .mayhen_invitation_cd_pill_box:nth-of-type(4), .mayhen_invitation_countdown_item:nth-child(4)', label: '✦ Khởi tạo nhịp đếm: Giây' },
                { sel: '.mayhen_invitation_countdown, .mayhen_invitation_countdown_pending', label: '✦ Đồng hồ đếm ngược thời gian thực' }
            ]
        },
        {
            key: 'events',
            title: 'Thông Tin Sự Kiện & Bản Đồ',
            container: '[data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"], .mayhen_invitation_events',
            unitSelectors: [
                { sel: '.mayhen_invitation_guest_banner, .mayhen_invitation_events .mayhen_invitation_section_header, .mayhen_invitation_events .mayhen_invitation_sr_dau_muc', label: '✦ Trân trọng kính mời quan khách' },
                { sel: '.mayhen_invitation_party_card:first-of-type, .mayhen_invitation_sr_the_sk:first-of-type, .mayhen_invitation_event_card:first-of-type, .mayhen_invitation_party_grid > *:first-child, .mayhen_invitation_ticket_card', label: '✦ Nghi thức hôn lễ & Lễ Gia Tiên' },
                { sel: '.mayhen_invitation_party_card:nth-of-type(2), .mayhen_invitation_sr_the_sk:nth-of-type(2), .mayhen_invitation_event_card:nth-of-type(2), .mayhen_invitation_party_grid > *:nth-child(2)', label: '✦ Thông tin buổi tiệc cưới thân mật' },
                { sel: '.mayhen_invitation_sr_the_bando, .mayhen_invitation_map_card, .mayhen_invitation_map_box, .mayhen_invitation_events__map, .mayhen_invitation_map_placeholder', label: '✦ Định vị toạ độ sảnh tiệc Google Maps' },
                { sel: '.mayhen_invitation_map_btn, .mayhen_invitation_map_open_link, .mayhen_invitation_map_footer_bar', label: '✦ Tích hợp chỉ đường thông minh' }
            ]
        },
        {
            key: 'timeline',
            title: 'Lịch Trình Hôn Lễ',
            container: '[data-mayhen-section*="timeline"], .mayhen_invitation_timeline',
            unitSelectors: [
                { sel: '.mayhen_invitation_timeline .mayhen_invitation_section_header, .mayhen_invitation_timeline .mayhen_invitation_sr_dau_muc, .mayhen_invitation_timeline_kicker', label: '✦ Dàn trang lịch trình hôn lễ' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(1), .mayhen_invitation_sr_moc:nth-child(1)', label: '✦ Mốc 1: Đón tiếp quan khách' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(2), .mayhen_invitation_sr_moc:nth-child(2)', label: '✦ Mốc 2: Cử hành hôn lễ thiêng liêng' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(3), .mayhen_invitation_sr_moc:nth-child(3)', label: '✦ Mốc 3: Khai tiệc mừng tân hôn' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(4), .mayhen_invitation_sr_moc:nth-child(4)', label: '✦ Mốc 4: Chụp hình kỷ niệm & tri ân' },
                { sel: '.mayhen_invitation_timeline_list, .mayhen_invitation_sr_truc', label: '✦ Trục chỉ vàng thời gian hôn lễ' }
            ]
        },
        {
            key: 'album',
            title: 'Khoảnh Khắc Hạnh Phúc (Album 10 Ảnh)',
            container: '[data-mayhen-section*="album"], [data-mayhen-section*="gallery"], .mayhen_invitation_gallery',
            unitSelectors: [
                { sel: '.mayhen_invitation_gallery .mayhen_invitation_section_header, .mayhen_invitation_gallery .mayhen_invitation_sr_dau_muc, .mayhen_invitation_album_header', label: '✦ Tuyển chọn Album 10 khoảnh khắc' },
                { sel: '.mayhen_invitation_gallery_grid > *:nth-child(1), .mayhen_invitation_album_grid > *:nth-child(1), .mayhen_invitation_tmx_album_cho', label: '✦ Khung ảnh tiêu điểm kỷ niệm' },
                { sel: '.mayhen_invitation_gallery_grid, .mayhen_invitation_album_grid, .mayhen_invitation_gallery__grid, .mayhen_invitation_gallery_wrap', label: '✦ Phối kết dải ảnh cưới mỹ thuật' },
                { sel: '.mayhen_invitation_curated_footer, .mayhen_invitation_sr_chan, .mayhen_invitation_footer', label: '✦ Hoàn thiện tuyệt tác thiệp cưới độc bản!' }
            ]
        }
    ];

    let isCompleted = false;
    let userInterrupted = false;
    let currentIndex = 0;
    const collectedUnits = [];
    const sectionContainers = [];
    let calculatedStepDelay = 1800;

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

    // Cuộn màn hình CHÍNH XÁC theo điểm ngọt thị giác (24% viewport)
    const performPreciseScroll = (targetEl) => {
        if (userInterrupted || !targetEl) return;

        const rect = targetEl.getBoundingClientRect();
        const viewportH = window.innerHeight || 667;

        // Điểm an toàn thoải mái trong tầm mắt (từ 14% đến 72% chiều cao màn hình)
        const inComfortZone = rect.top >= (viewportH * 0.14) && rect.bottom <= (viewportH * 0.72);
        if (inComfortZone) {
            return;
        }

        const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        const targetScrollY = currentScrollY + rect.top - (viewportH * 0.24);

        window.scrollTo({
            top: Math.max(0, targetScrollY),
            behavior: 'smooth'
        });
    };

    // Hàm tiết lộ tất cả ngay lập tức (khi Bỏ qua hoặc Kết thúc)
    const revealAllInstantly = () => {
        if (isCompleted) return;
        isCompleted = true;

        document.documentElement.classList.remove('mayhen_crafting_preparing');
        liftCurtain();

        // 1. Mở tất cả các section và cưỡng chế hiển thị
        sectionContainers.forEach((sec) => {
            sec.classList.remove('mayhen_crafting_section_waiting');
            sec.classList.add('mayhen_crafting_section_active');
            sec.style.setProperty('opacity', '1', 'important');
            sec.style.setProperty('visibility', 'visible', 'important');
            sec.style.setProperty('max-height', 'none', 'important');
            sec.style.setProperty('overflow', 'visible', 'important');
        });

        // 2. Kích hoạt hiển thị toàn bộ các thẻ đã thu thập
        collectedUnits.forEach((u) => {
            u.element.classList.remove('mayhen_is_hidden', 'mayhen_is_sweeping');
            u.element.classList.add('mayhen_is_revealed', 'mayhen_invitation_visible');
            u.element.style.setProperty('opacity', '1', 'important');
            u.element.style.setProperty('visibility', 'visible', 'important');
            u.element.style.setProperty('clip-path', 'none', 'important');
            u.element.style.setProperty('transform', 'none', 'important');
            const beam = u.element.querySelector('.mayhen_crafting_shimmer_beam');
            if (beam) beam.remove();
        });

        // 3. Bảo vệ toàn diện: cưỡng chế hiển thị 100% tất cả thẻ reveal trong trang
        document.querySelectorAll('.mayhen_invitation_reveal, [class*="mayhen_invitation_reveal"]').forEach((el) => {
            el.classList.add('mayhen_invitation_visible');
            el.style.setProperty('opacity', '1', 'important');
            el.style.setProperty('visibility', 'visible', 'important');
            el.style.setProperty('transform', 'none', 'important');
        });

        // 4. Thông báo cho Studio hoàn tất 100%
        window.parent?.postMessage({
            type: 'MAYHEN_CRAFTING_COMPLETE',
            total: collectedUnits.length
        }, '*');

        // 5. Cuộn mượt mà trở lại đỉnh thiệp để người dùng chiêm ngưỡng trọn vẹn tác phẩm từ đầu
        setTimeout(() => {
            if (!userInterrupted) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }, 500);

        // 6. Dọn dẹp an toàn các class chuyển tiếp sau khi hoàn tất
        setTimeout(() => {
            collectedUnits.forEach((u) => {
                u.element.classList.remove('mayhen_crafting_unit', 'mayhen_is_revealed', 'mayhen_is_hidden', 'mayhen_is_sweeping');
                u.element.classList.add('mayhen_invitation_visible');
            });
            document.body.classList.remove('mayhen_is_crafting');
            document.documentElement.classList.remove('mayhen_is_crafting');
        }, 1200);
    };

    const revealNextUnit = () => {
        if (isCompleted) return;

        if (currentIndex >= collectedUnits.length) {
            revealAllInstantly();
            return;
        }

        const unit = collectedUnits[currentIndex];
        const el = unit.element;

        // 1. Mở section cha nếu chưa mở (Section Staging: tạo tới đâu mới mở tới đó)
        if (unit.containerEl && unit.containerEl.classList.contains('mayhen_crafting_section_waiting')) {
            unit.containerEl.classList.remove('mayhen_crafting_section_waiting');
            unit.containerEl.classList.add('mayhen_crafting_section_active');
            unit.containerEl.style.setProperty('opacity', '1', 'important');
            unit.containerEl.style.setProperty('visibility', 'visible', 'important');
            unit.containerEl.style.setProperty('max-height', 'none', 'important');
        }

        // 2. Kích hoạt hiệu ứng quét vệt sáng hoàng gia (1.1s): Vệt sáng quét tới đâu, phần tử lộ ra theo sau đến đó
        el.classList.remove('mayhen_is_hidden');
        el.classList.add('mayhen_is_sweeping');

        // Tạo vệt sáng hoàng gia (Beam) lướt đồng bộ cùng clip-path (Có phòng vệ void element)
        const beam = document.createElement('div');
        beam.className = 'mayhen_crafting_shimmer_beam';
        attachBeamSafely(el, beam);

        // 3. Cuộn màn hình chính xác theo điểm ngọt thị giác
        performPreciseScroll(el);

        // 4. Gửi thông điệp tiến trình chi tiết cho topbar Preview Studio
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

        // 5. Sau khi quét xong (SWEEP_ANIMATION_MS = 1100ms): cố định trạng thái hiển thị hoàn mỹ
        setTimeout(() => {
            el.classList.remove('mayhen_is_sweeping');
            el.classList.add('mayhen_is_revealed', 'mayhen_invitation_visible');
            el.style.setProperty('clip-path', 'none', 'important');
            el.style.setProperty('transform', 'none', 'important');
            if (beam.parentNode) beam.remove();
        }, SWEEP_ANIMATION_MS);

        currentIndex++;

        // 6. Nhịp độ chuyển tiếp chậm rãi, sâu lắng (khoảng 1.5s - 2.2s mỗi phần tử, tổng thời gian ~45-50 giây)
        setTimeout(revealNextUnit, calculatedStepDelay);
    };

    // Lắng nghe lệnh Skip và Replay từ Preview Studio
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
            revealAllInstantly();
        }
        if (e.data.type === 'MAYHEN_REPLAY_CRAFTING') {
            isCompleted = false;
            currentIndex = 0;
            userInterrupted = false;
            window.scrollTo({ top: 0, behavior: 'auto' });

            // Khóa lại các section phía dưới
            sectionContainers.forEach((sec, idx) => {
                if (idx > 0) {
                    sec.classList.remove('mayhen_crafting_section_active');
                    sec.classList.add('mayhen_crafting_section_waiting');
                }
            });

            collectedUnits.forEach((u) => {
                u.element.classList.remove('mayhen_is_revealed', 'mayhen_is_sweeping');
                u.element.classList.add('mayhen_is_hidden');
            });

            setTimeout(revealNextUnit, 350);
        }
    });

    const runPresenter = () => {
        // Thu thập toàn bộ section containers
        SECTION_RULES.forEach((rule, rIdx) => {
            const containerEl = document.querySelector(rule.container);
            if (!containerEl) return;

            if (!sectionContainers.includes(containerEl)) {
                sectionContainers.push(containerEl);
            }

            // SECTION STAGING: Section đầu tiên mở sẵn, toàn bộ section bên dưới KHÓA ẨN 100%
            if (rIdx > 0) {
                containerEl.classList.add('mayhen_crafting_section_waiting');
            } else {
                containerEl.classList.add('mayhen_crafting_section_active');
            }

            let sectionHasSpecificUnits = false;

            rule.unitSelectors.forEach((unitSel) => {
                const els = containerEl.querySelectorAll(unitSel.sel);
                els.forEach((el) => {
                    if (el && !collectedUnits.some(u => u.element === el || el.contains(u.element) || u.element.contains(el))) {
                        collectedUnits.push({
                            sectionKey: rule.key,
                            sectionTitle: rule.title,
                            label: unitSel.label,
                            element: el,
                            containerEl: containerEl,
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
                    containerEl: containerEl,
                });
            }
        });

        if (collectedUnits.length === 0) {
            liftCurtain();
            document.documentElement.classList.remove('mayhen_crafting_preparing');
            window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
            return;
        }

        // TÍNH TOÁN NHỊP BƯỚC THÔNG MINH CHO TỔNG THỜI GIAN 40S - 1 PHÚT:
        // Đảm bảo dù bất kỳ mẫu thiệp nào, tổng thời gian dệt thiệp luôn rơi vào khoảng ~45-50 giây
        calculatedStepDelay = Math.max(
            MIN_STEP_DELAY_MS,
            Math.min(MAX_STEP_DELAY_MS, Math.round(TARGET_TOTAL_DURATION_MS / collectedUnits.length))
        );

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

        // Giải phóng trạng thái chuẩn bị FOUC: Khung nền trở lại bình thường, các phần tử con đã an toàn dưới class mayhen_is_hidden
        document.documentElement.classList.remove('mayhen_crafting_preparing');

        // Bóc rèm Atelier nhẹ nhàng (220ms)
        liftCurtain();

        // Khởi động dệt với nhịp nghỉ tao nhã ban đầu (450ms)
        setTimeout(revealNextUnit, 450);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPresenter);
    } else {
        runPresenter();
    }
})();
