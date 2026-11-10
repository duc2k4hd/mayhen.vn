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
    // Kích hoạt khi có tham số ?crafting=1 hoặc ?recraft_section=... trên URL
    const urlParams = new URLSearchParams(window.location.search);
    const isFullCrafting = urlParams.has('crafting');
    const recraftSectionKey = urlParams.get('recraft_section');

    if (!isFullCrafting && !recraftSectionKey) {
        return;
    }

    if (window.__MAYHEN_CRAFTING_BOOTED) {
        return;
    }
    window.__MAYHEN_CRAFTING_BOOTED = true;

    // CẤU HÌNH NHỊP ĐỘ DỆT THIỆP CHUẨN MỰC (16S - 20S THEO MASTER PROMPT V3)
    const TARGET_TOTAL_DURATION_MS = 18000; // Tổng thời gian ~18 giây, gãy gọn, tinh tế
    const MIN_STEP_DELAY_MS = 850;         // Nhịp bước tối thiểu 0.85s
    const MAX_STEP_DELAY_MS = 1250;        // Nhịp bước tối đa 1.25s
    const SWEEP_ANIMATION_MS = 650;        // Thời gian vệt sáng hoàng gia lướt qua phần tử (0.65s)

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
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            max-height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        html.mayhen_is_crafting .mayhen_crafting_section_active,
        body.mayhen_is_crafting .mayhen_crafting_section_active {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        /* 2. KHÓA ẨN CÁC PHẦN TỬ CON CHƯA TỚI LƯỢT DỆT (DÙNG INSET CHÍNH XÁC 100%) */
        .mayhen_crafting_unit {
            position: relative;
            will-change: clip-path, opacity;
        }
        .mayhen_crafting_unit.mayhen_is_hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            clip-path: inset(0 100% 0 0) !important;
        }

        /* 3. HIỆU ỨNG VỆT SÁNG QUÉT TỚI ĐÂU, PHẦN TỬ LỘ RA TỚI ĐÓ (0.65s ĐỒNG BỘ 100%) */
        .mayhen_crafting_unit.mayhen_is_sweeping {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            animation: mayhenSweepReveal 0.65s cubic-bezier(0.25, 1, 0.5, 1) forwards !important;
        }
        @keyframes mayhenSweepReveal {
            0% {
                clip-path: inset(0 100% 0 0);
                opacity: 0.15;
            }
            15% {
                opacity: 1;
            }
            100% {
                clip-path: inset(0 0% 0 0);
                opacity: 1;
            }
        }

        .mayhen_crafting_unit.mayhen_is_revealed {
            opacity: 1 !important;
            visibility: visible !important;
            clip-path: none !important;
            pointer-events: auto !important;
            transform: none !important;
            transition: opacity 0.25s ease;
        }

        /* 4. VỆT SÁNG HOÀNG GIA LỘNG LẪY (ROYAL 24K GOLD SHIMMER BEAM 0.65s) */
        .mayhen_crafting_shimmer_beam {
            position: absolute;
            top: -25%;
            left: -40px;
            width: 80px;
            height: 150%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(207, 168, 96, 0.2) 15%, 
                rgba(255, 235, 175, 0.95) 45%, 
                #FFFFFF 55%, 
                rgba(255, 225, 140, 0.95) 65%, 
                rgba(207, 168, 96, 0.35) 85%, 
                transparent 100%
            );
            box-shadow: 0 0 16px #FFFFFF, 0 0 32px rgba(255, 224, 130, 0.95), 0 0 55px rgba(212, 175, 55, 0.75), 0 0 85px rgba(197, 160, 89, 0.35);
            transform: skewX(-18deg);
            pointer-events: none;
            z-index: 999;
            animation: mayhenBeamTrack 0.65s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        .mayhen_crafting_shimmer_beam::after {
            content: '✦';
            position: absolute;
            top: 50%;
            left: 55%;
            transform: translate(-50%, -50%);
            color: #FFFDF9;
            font-size: 15px;
            line-height: 1;
            text-shadow: 0 0 8px #FFFFFF, 0 0 18px #FFE082, 0 0 25px #FFD700;
            opacity: 1;
        }
        @keyframes mayhenBeamTrack {
            0% {
                left: -40px;
                opacity: 0;
            }
            15% {
                opacity: 1;
            }
            85% {
                opacity: 1;
            }
            100% {
                left: calc(100% - 10px);
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

    // Cuộn màn hình CHÍNH XÁC đến điểm ngọt thị giác (24% viewport) và CHỈ BẮT ĐẦU DỆT KHI ĐÃ ĐẾN ĐÍCH
    const scrollToElementBeforeCrafting = (targetEl) => {
        return new Promise((resolve) => {
            if (userInterrupted || !targetEl) {
                // Người dùng đang chủ động tương tác màn hình: giữ nguyên vị trí, chờ 120ms tạo nhịp
                setTimeout(resolve, 120);
                return;
            }

            const rect = targetEl.getBoundingClientRect();
            const viewportH = window.innerHeight || 667;

            // Nếu phần tử đã nằm êm ái trong tầm mắt (từ 15% đến 68% chiều cao màn hình)
            const inComfortZone = rect.top >= (viewportH * 0.15) && rect.bottom <= (viewportH * 0.68);
            if (inComfortZone) {
                // Đã đúng điểm nhìn: dừng nghỉ nhẹ nhàng 180ms tạo sự tập trung trước khi dệt
                setTimeout(resolve, 180);
                return;
            }

            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
            const targetScrollY = Math.max(0, currentScrollY + rect.top - (viewportH * 0.24));

            window.scrollTo({
                top: targetScrollY,
                behavior: 'smooth'
            });

            // Chờ chuyển động cuộn mượt hoàn tất và màn hình đứng yên hoàn toàn theo cự ly cuộn
            const scrollDistance = Math.abs(targetScrollY - currentScrollY);
            const scrollDelay = Math.min(380, Math.max(180, Math.round(scrollDistance * 0.18)));
            setTimeout(resolve, scrollDelay);
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
            sec.style.setProperty('display', 'block', 'important');
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
        document.querySelectorAll('.mayhen_invitation_reveal, [class*="mayhen_invitation_reveal"], .mayhen_invitation_section, [data-mayhen-section]').forEach((el) => {
            el.classList.add('mayhen_invitation_visible');
            el.style.setProperty('display', 'block', 'important');
            el.style.setProperty('opacity', '1', 'important');
            el.style.setProperty('visibility', 'visible', 'important');
            el.style.setProperty('clip-path', 'none', 'important');
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
        }, 800);
    };

    const revealNextUnit = async () => {
        if (isCompleted) return;

        if (currentIndex >= collectedUnits.length) {
            revealAllInstantly();
            return;
        }

        const unit = collectedUnits[currentIndex];
        const el = unit.element;

        try {
            // 1. Mở section cha nếu chưa mở (Section Staging: tạo tới đâu mới mở tới đó)
            if (unit.containerEl && (unit.containerEl.classList.contains('mayhen_crafting_section_waiting') || unit.containerEl.style.display === 'none')) {
                unit.containerEl.classList.remove('mayhen_crafting_section_waiting');
                unit.containerEl.classList.add('mayhen_crafting_section_active');
                unit.containerEl.style.setProperty('display', 'block', 'important');
                unit.containerEl.style.setProperty('opacity', '1', 'important');
                unit.containerEl.style.setProperty('visibility', 'visible', 'important');
                unit.containerEl.style.setProperty('max-height', 'none', 'important');
            }

            // 2. GIAI ĐOẠN 1: GỬI TIẾN TRÌNH CHO TOPBAR & CUỘN MÀN HÌNH ĐẾN ĐÚNG ĐIỂM TRƯỚC
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

            // Cuộn màn hình đến đúng vị trí và ĐỢI MÀN HÌNH DỪNG YÊN
            await scrollToElementBeforeCrafting(el);

            if (isCompleted) return;

            // 3. GIAI ĐOẠN 2: BẮT ĐẦU DỆT KHI MÀN HÌNH ĐÃ ĐỨNG YÊN HOÀN TOÀN
            el.classList.remove('mayhen_is_hidden');
            el.classList.add('mayhen_is_sweeping');

            // Tạo vệt sáng hoàng gia (Beam) lướt đồng bộ cùng clip-path (Có phòng vệ void element)
            const beam = document.createElement('div');
            beam.className = 'mayhen_crafting_shimmer_beam';
            attachBeamSafely(el, beam);

            // Đợi vệt sáng quét xong (SWEEP_ANIMATION_MS = 650ms)
            await new Promise((res) => setTimeout(res, SWEEP_ANIMATION_MS));

            if (isCompleted) return;

            // Cố định trạng thái hiển thị hoàn mỹ của phần tử vừa dệt
            el.classList.remove('mayhen_is_sweeping');
            el.classList.add('mayhen_is_revealed', 'mayhen_invitation_visible');
            el.style.setProperty('clip-path', 'none', 'important');
            el.style.setProperty('transform', 'none', 'important');
            if (beam.parentNode) beam.remove();

            // 4. GIAI ĐOẠN 3: NHỊP NGHỈ CHIÊM NGƯỠNG (ATELIER SAVOR PAUSE)
            await new Promise((res) => setTimeout(res, 200));

            if (isCompleted) return;

            currentIndex++;
            revealNextUnit();
        } catch (err) {
            console.warn('[MayHen Crafting] Error in step, triggering fail-safe:', err);
            revealAllInstantly();
        }
    };

    // Lắng nghe lệnh Skip, Replay và Scroll to Section từ Preview Studio
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
            revealAllInstantly();
        }
        if (e.data.type === 'MAYHEN_SCROLL_TO_SECTION') {
            const sec = e.data.section;
            const rule = SECTION_RULES.find((r) => r.key === sec);
            const targetEl = document.querySelector(`[data-curated-section="${sec}"]`)
                || (rule ? document.querySelector(rule.container) : null);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
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

    // Trình diễn dệt riêng một Section khi người dùng bấm đổi biến thể
    const runSectionRecraft = async (sectionKey) => {
        liftCurtain();
        document.documentElement.classList.remove('mayhen_crafting_preparing');

        // Mở tất cả các section bình thường để hiển thị trọn vẹn
        document.querySelectorAll('.mayhen_crafting_section_waiting').forEach((sec) => {
            sec.classList.remove('mayhen_crafting_section_waiting');
            sec.classList.add('mayhen_crafting_section_active');
            sec.style.setProperty('opacity', '1', 'important');
            sec.style.setProperty('visibility', 'visible', 'important');
            sec.style.setProperty('max-height', 'none', 'important');
            sec.style.setProperty('overflow', 'visible', 'important');
        });

        // Tìm section mục tiêu trong DOM
        const rule = SECTION_RULES.find((r) => r.key === sectionKey);
        const targetSectionEl = document.querySelector(`[data-curated-section="${sectionKey}"]`)
            || (rule ? document.querySelector(rule.container) : null);

        if (!targetSectionEl) {
            window.parent?.postMessage({ type: 'MAYHEN_SECTION_CRAFT_COMPLETE', section: sectionKey }, '*');
            return;
        }

        // Thu thập các phần tử con của section này
        const targetUnits = [];
        if (rule) {
            rule.unitSelectors.forEach((unitSel) => {
                const els = targetSectionEl.querySelectorAll(unitSel.sel);
                els.forEach((el) => {
                    if (el && !targetUnits.some((u) => u.element === el || el.contains(u.element) || u.element.contains(el))) {
                        targetUnits.push({
                            element: el,
                            label: unitSel.label,
                        });
                    }
                });
            });
        }

        if (targetUnits.length === 0) {
            const craftUnits = targetSectionEl.querySelectorAll('.mayhen_curated_craft_unit');
            if (craftUnits.length > 0) {
                craftUnits.forEach((el, cIdx) => {
                    targetUnits.push({
                        element: el,
                        label: `✦ Đang hoàn thiện chi tiết ${cIdx + 1}`,
                    });
                });
            } else {
                targetUnits.push({
                    element: targetSectionEl,
                    label: `✦ Đang hoàn thiện ${rule ? rule.title : 'bố cục'}`,
                });
            }
        }

        // Khóa ẩn các phần tử của section này trước khi dệt
        targetUnits.forEach((u) => {
            u.element.classList.add('mayhen_crafting_unit', 'mayhen_is_hidden');
        });

        // 1. CUỘN CAMERA ĐẾN ĐÚNG SECTION VỪA ĐỔI BIẾN THỂ VÀ CHỜ DỪNG ỔN ĐỊNH
        await scrollToElementBeforeCrafting(targetSectionEl);

        // 2. DỆT CHẬM RÃI TỪNG PHẦN TỬ CỦA SECTION VỚI VỆT SÁNG HOÀNG GIA
        for (let i = 0; i < targetUnits.length; i++) {
            const unit = targetUnits[i];
            const el = unit.element;

            await scrollToElementBeforeCrafting(el);

            el.classList.remove('mayhen_is_hidden');
            el.classList.add('mayhen_is_sweeping');

            const beam = document.createElement('div');
            beam.className = 'mayhen_crafting_shimmer_beam';
            attachBeamSafely(el, beam);

            await new Promise((res) => setTimeout(res, 950));

            el.classList.remove('mayhen_is_sweeping');
            el.classList.add('mayhen_is_revealed', 'mayhen_invitation_visible');
            el.style.setProperty('clip-path', 'none', 'important');
            el.style.setProperty('transform', 'none', 'important');
            if (beam.parentNode) beam.remove();

            await new Promise((res) => setTimeout(res, 220));
        }

        // 3. THÔNG BÁO HOÀN TẤT CHO PREVIEW STUDIO ĐỂ NHẢ KHÓA CÁC NÚT BIẾN THỂ
        window.parent?.postMessage({
            type: 'MAYHEN_SECTION_CRAFT_COMPLETE',
            section: sectionKey,
        }, '*');
    };

    const runPresenter = () => {
        // Nếu có tham số recraft_section: chỉ dệt lại đúng section đó
        if (recraftSectionKey) {
            runSectionRecraft(recraftSectionKey);
            return;
        }

        // Thu thập toàn bộ section containers
        SECTION_RULES.forEach((rule, rIdx) => {
            const containerEl = document.querySelector(`[data-curated-section="${rule.key}"]`) 
                || document.querySelector(rule.container);
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

        // TÍNH TOÁN NHỊP BƯỚC THÔNG MINH CHO TỔNG THỜI GIAN 16S - 20S (MASTER PROMPT V3)
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

        // Fail-safe Watchdog Timer: Sau tối đa 25s nếu chưa xong, tự động giải phóng 100% không bao giờ để thiệp bị trắng
        setTimeout(() => {
            if (!isCompleted) {
                console.info('[MayHen Crafting] Watchdog timer triggered - revealing full invitation.');
                revealAllInstantly();
            }
        }, 25000);

        // Khởi động dệt với nhịp nghỉ tao nhã ban đầu (350ms)
        setTimeout(revealNextUnit, 350);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPresenter);
    } else {
        runPresenter();
    }
})();
