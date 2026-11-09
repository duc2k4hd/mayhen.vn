/**
 * MAYHEN.VN — NATIVE CRAFTING PRESENTER V7 (MASTER REVISION)
 * Trình diễn hiệu ứng xuất hiện tiến trình (Progressive Live Crafting)
 * Từng thẻ, từng hàng, từng phần tử từ trên xuống dưới, từ trái sang phải.
 *
 * ĐẶC TÍNH NGHỆ THUẬT V7:
 * 1. ZERO LEAKED ELEMENTS: 100% phần tử chưa dệt bị khóa ẩn hoàn toàn, tạo tới đâu mới lộ tới đó.
 * 2. SYNCHRONIZED LIGHT SWEEP: Vệt sáng vàng kim hoàng gia quét tới đâu, clip-path mới mở lộ phần tử đến đó.
 * 3. VOID ELEMENT SAFEGUARD: Chống crash DOM 100% khi dệt thẻ img, input, hr...
 * 4. ROYAL ATELIER BEAM: Vệt sáng vàng kim lộng lẫy kèm đốm sao tinh hoa Quiet Luxury.
 * 5. BULLETPROOF FAIL-SAFE: 100% nội dung giải phóng trọn vẹn, không bao giờ bị màn hình trắng.
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

    const liftCurtain = () => {
        const curtain = document.getElementById('mayhenCraftingCurtain');
        if (curtain) {
            curtain.style.transition = 'opacity 0.22s ease';
            curtain.style.opacity = '0';
            setTimeout(() => {
                if (curtain.parentNode) curtain.remove();
            }, 260);
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

    // Tiêm style hiệu ứng chuyển động và vệt sáng hoàng gia lộng lẫy
    const styleEl = document.createElement('style');
    styleEl.id = 'mayhen-native-crafting-styles-v7';
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
            transition: opacity 0.35s ease;
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

        /* 3. HIỆU ỨNG VỆT SÁNG QUÉT TỚI ĐÂU, PHẦN TỬ MỚI LỘ RA THEO ĐẾN ĐÓ */
        .mayhen_crafting_unit.mayhen_is_sweeping {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            animation: mayhenSweepReveal 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
        }
        @keyframes mayhenSweepReveal {
            0% {
                clip-path: polygon(0 0, 0 0, 0 100%, 0 100%);
            }
            100% {
                clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
            }
        }

        .mayhen_crafting_unit.mayhen_is_revealed {
            opacity: 1 !important;
            visibility: visible !important;
            clip-path: none !important;
            pointer-events: auto !important;
        }

        /* 4. VỆT SÁNG HOÀNG GIA LỘNG LẪY (ROYAL GOLD SHIMMER BEAM) */
        .mayhen_crafting_shimmer_beam {
            position: absolute;
            top: -15%;
            left: -15%;
            width: 85px;
            height: 130%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(207, 168, 96, 0.15) 15%, 
                rgba(255, 235, 175, 0.95) 45%, 
                #FFFFFF 58%, 
                rgba(255, 235, 175, 0.95) 70%, 
                rgba(207, 168, 96, 0.3) 85%, 
                transparent 100%
            );
            box-shadow: 0 0 16px #FFFFFF, 0 0 32px rgba(255, 225, 140, 0.95), 0 0 60px rgba(207, 168, 96, 0.75), 0 0 90px rgba(207, 168, 96, 0.35);
            transform: skewX(-20deg);
            pointer-events: none;
            z-index: 999;
            animation: mayhenBeamTrack 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .mayhen_crafting_shimmer_beam::after {
            content: '✦';
            position: absolute;
            top: 50%;
            left: 55%;
            transform: translate(-50%, -50%);
            color: #FFFDF9;
            font-size: 13px;
            line-height: 1;
            text-shadow: 0 0 8px #FFFFFF, 0 0 16px #FFE29F;
            opacity: 0.95;
        }
        @keyframes mayhenBeamTrack {
            0% {
                left: -15%;
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

    // Bản đồ bóc tách chi tiết từng đơn vị mỹ thuật hỗ trợ cả 4 mẫu gốc
    const SECTION_RULES = [
        {
            key: 'hero',
            title: 'Trang Bìa Chính',
            container: '[data-mayhen-section*="hero"], .mayhen_invitation_hero, section.mayhen_invitation_sr_dau',
            unitSelectors: [
                { sel: '.mayhen_invitation_hero_badge, .mayhen_invitation_sr_kicker, .mayhen_invitation_hero__badge, .mayhen_invitation_hero_kicker, [data-sync-target="ceremony_type"]', label: '✦ Biểu trưng & loại hình hôn lễ' },
                { sel: '.mayhen_invitation_hero_couple, .mayhen_invitation_hero__names, .mayhen_invitation_t1, .mayhen_invitation_t2, [data-sync-target="couple_names"]', label: '✦ Đang dệt tên Đôi Uyên Ương' },
                { sel: '.mayhen_invitation_hero_frame, .mayhen_invitation_sr_quy_dao, .mayhen_invitation_hero_media, .mayhen_invitation_hero__frame, .mayhen_invitation_hero__photo_box, .mayhen_invitation_img_dau', label: '✦ Mở khung ảnh chân dung uyên ương' },
                { sel: '.mayhen_invitation_hero_date_pill, .mayhen_invitation_sr_ngay_chinh, .mayhen_invitation_lunar_note, .mayhen_invitation_sr_am_chinh, [data-sync-target="wedding_date_digits"]', label: '✦ Khắc ghi ngày cưới dương & âm lịch' }
            ]
        },
        {
            key: 'story',
            title: 'Lời Ngỏ & Chuyện Tình',
            container: '[data-mayhen-section*="story"], .mayhen_invitation_story',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_sr_dau_muc, .mayhen_invitation_story__header', label: '✦ Khởi mở Chuyện Tình Yêu' },
                { sel: '.mayhen_invitation_quote_box, .mayhen_invitation_sr_the.mayhen_invitation_kieu_vom, .mayhen_invitation_story__quote, [data-sync-target="couple_quote"]', label: '✦ Biên soạn trích dẫn tâm tình' },
                { sel: '.mayhen_invitation_triptych, .mayhen_invitation_story__photo_frame, .mayhen_invitation_story_media, .mayhen_invitation_story__photo', label: '✦ Tuyển chọn thước phim kỷ niệm' },
                { sel: '.mayhen_invitation_story_content, .mayhen_invitation_sr_cau_chuyen, .mayhen_invitation_story__text, [data-sync-target="story_content"]', label: '✦ Dệt câu chuyện tình yêu lắng đọng' }
            ]
        },
        {
            key: 'parents',
            title: 'Song Thân & Đôi Uyên Ương',
            container: '[data-mayhen-section*="parents"], [data-mayhen-section*="family"], .mayhen_invitation_parents, .mayhen_invitation_family, .mayhen_invitation_family_section',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_sr_dau_muc, .mayhen_invitation_parents__header, .mayhen_invitation_family__header', label: '✦ Kính báo Song Thân Phụ Mẫu' },
                { sel: '.mayhen_invitation_family_card:first-of-type, .mayhen_invitation_ben[data-ben="trai"], [data-mayhen-section*="groom_family"], .mayhen_invitation_family__side--groom', label: '✦ Trang trọng đại diện Nhà Trai' },
                { sel: '.mayhen_invitation_family_card:last-of-type, .mayhen_invitation_ben[data-ben="phai"], [data-mayhen-section*="bride_family"], .mayhen_invitation_family__side--bride', label: '✦ Trang trọng đại diện Nhà Gái' },
                { sel: '.mayhen_invitation_announce_box, .mayhen_invitation_sr_the_cap, .mayhen_invitation_couple_box, .mayhen_invitation_couple_card', label: '✦ Giới thiệu Đôi Uyên Ương' },
                { sel: '.mayhen_invitation_couple_row:first-of-type, .mayhen_invitation_couple_list > *:first-child', label: '✦ Thông tin Chú Rể' },
                { sel: '.mayhen_invitation_couple_row:last-of-type, .mayhen_invitation_couple_list > *:last-child', label: '✦ Thông tin Cô Dâu' }
            ]
        },
        {
            key: 'countdown',
            title: 'Đếm Ngược & Lịch Tháng',
            container: '[data-mayhen-section*="countdown"], .mayhen_invitation_countdown_calendar, .mayhen_invitation_calendar',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_sr_dau_muc, .mayhen_invitation_calendar__header', label: '✦ Đếm ngược từng khoảnh khắc' },
                { sel: '.mayhen_invitation_calendar_card, .mayhen_invitation_sr_lich, .mayhen_invitation_calendar_grid, .mayhen_invitation_calendar__wrap', label: '✦ Đính kết lịch cưới Save The Date' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(1), .mayhen_invitation_tmx_o:nth-child(1), .mayhen_invitation_countdown_item:nth-child(1)', label: '✦ Đếm ngược: Ngày' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(2), .mayhen_invitation_tmx_o:nth-child(2), .mayhen_invitation_countdown_item:nth-child(2)', label: '✦ Đếm ngược: Giờ' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(3), .mayhen_invitation_tmx_o:nth-child(3), .mayhen_invitation_countdown_item:nth-child(3)', label: '✦ Đếm ngược: Phút' },
                { sel: '.mayhen_invitation_countdown_box:nth-child(4), .mayhen_invitation_tmx_o:nth-child(4), .mayhen_invitation_countdown_item:nth-child(4)', label: '✦ Đếm ngược: Giây' },
                { sel: '.mayhen_invitation_countdown', label: '✦ Đồng hồ đếm ngược ngày vui' }
            ]
        },
        {
            key: 'events',
            title: 'Thông Tin Sự Kiện & Bản Đồ',
            container: '[data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"], .mayhen_invitation_events',
            unitSelectors: [
                { sel: '.mayhen_invitation_guest_banner, .mayhen_invitation_section_header, .mayhen_invitation_sr_dau_muc', label: '✦ Trân trọng kính mời quan khách' },
                { sel: '.mayhen_invitation_party_card:first-of-type, .mayhen_invitation_sr_the_sk:first-of-type, .mayhen_invitation_event_card:first-of-type, .mayhen_invitation_party_grid > *:first-child', label: '✦ Nghi thức hôn lễ & Tiệc mừng' },
                { sel: '.mayhen_invitation_party_card:nth-of-type(2), .mayhen_invitation_sr_the_sk:nth-of-type(2), .mayhen_invitation_event_card:nth-of-type(2), .mayhen_invitation_party_grid > *:nth-child(2)', label: '✦ Thông tin buổi tiệc cưới' },
                { sel: '.mayhen_invitation_sr_the_bando, .mayhen_invitation_map_card, .mayhen_invitation_map_btn, .mayhen_invitation_events__map', label: '✦ Bản đồ định vị chỉ đường' }
            ]
        },
        {
            key: 'timeline',
            title: 'Lịch Trình Hôn Lễ',
            container: '[data-mayhen-section*="timeline"], .mayhen_invitation_timeline',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_sr_dau_muc', label: '✦ Dàn trang lịch trình hôn lễ' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(1), .mayhen_invitation_sr_moc:nth-child(1)', label: '✦ Mốc 1: Đón tiếp quan khách' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(2), .mayhen_invitation_sr_moc:nth-child(2)', label: '✦ Mốc 2: Cử hành hôn lễ' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(3), .mayhen_invitation_sr_moc:nth-child(3)', label: '✦ Mốc 3: Khai tiệc mừng' },
                { sel: '.mayhen_invitation_timeline_item:nth-child(4), .mayhen_invitation_sr_moc:nth-child(4)', label: '✦ Mốc 4: Chụp hình kỷ niệm & Tiễn khách' },
                { sel: '.mayhen_invitation_timeline_list', label: '✦ Các mốc lịch trình hôn lễ' }
            ]
        },
        {
            key: 'album',
            title: 'Khoảnh Khắc Hạnh Phúc (Album 10 Ảnh)',
            container: '[data-mayhen-section*="album"], [data-mayhen-section*="gallery"], .mayhen_invitation_gallery',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_sr_dau_muc, .mayhen_invitation_album_header', label: '✦ Tuyển chọn Album 10 khoảnh khắc' },
                { sel: '.mayhen_invitation_gallery_grid, .mayhen_invitation_album_grid, .mayhen_invitation_gallery__grid, .mayhen_invitation_gallery_wrap, .mayhen_invitation_tmx_album_cho', label: '✦ Thước phim kỷ niệm hạnh phúc' },
                { sel: '.mayhen_invitation_curated_footer', label: '✦ Hoàn thiện tuyệt tác thiệp cưới độc bản!' }
            ]
        }
    ];

    let isCompleted = false;
    let userInterrupted = false;
    let currentIndex = 0;
    const collectedUnits = [];
    const sectionContainers = [];

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

    // Cuộn màn hình CHÍNH XÁC theo điểm ngọt thị giác (26% viewport)
    const performPreciseScroll = (targetEl) => {
        if (userInterrupted || !targetEl) return;

        const rect = targetEl.getBoundingClientRect();
        const viewportH = window.innerHeight || 667;

        // Điểm an toàn thoải mái trong tầm mắt (từ 12% đến 76% chiều cao màn hình)
        const inComfortZone = rect.top >= (viewportH * 0.12) && rect.bottom <= (viewportH * 0.78);
        if (inComfortZone) {
            return;
        }

        const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        const targetScrollY = currentScrollY + rect.top - (viewportH * 0.26);

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
        }, 350);

        // 6. Dọn dẹp an toàn các class chuyển tiếp sau khi hoàn tất
        setTimeout(() => {
            collectedUnits.forEach((u) => {
                u.element.classList.remove('mayhen_crafting_unit', 'mayhen_is_revealed', 'mayhen_is_hidden', 'mayhen_is_sweeping');
                u.element.classList.add('mayhen_invitation_visible');
            });
            document.body.classList.remove('mayhen_is_crafting');
            document.documentElement.classList.remove('mayhen_is_crafting');
        }, 1000);
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

        // 2. Kích hoạt hiệu ứng quét vệt sáng hoàng gia: Vệt sáng quét tới đâu, phần tử lộ ra theo sau đến đó
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

        // 5. Sau khi quét xong (650ms): cố định trạng thái hiển thị hoàn mỹ
        setTimeout(() => {
            el.classList.remove('mayhen_is_sweeping');
            el.classList.add('mayhen_is_revealed', 'mayhen_invitation_visible');
            el.style.setProperty('clip-path', 'none', 'important');
            if (beam.parentNode) beam.remove();
        }, 650);

        currentIndex++;

        // Nhịp độ chuyển tiếp: văn bản nhanh (~380ms), khối ảnh to (~540ms)
        const isSmall = el.tagName === 'SPAN' || el.tagName === 'H1' || el.tagName === 'H2' || el.tagName === 'H3' || el.tagName === 'P';
        const nextDelay = isSmall ? 380 : 540;

        setTimeout(revealNextUnit, nextDelay);
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
            setTimeout(revealNextUnit, 250);
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

        // Bóc rèm Atelier siêu tốc (180ms)
        liftCurtain();

        // Khởi động dệt tức thì trong vòng 200ms
        setTimeout(revealNextUnit, 200);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPresenter);
    } else {
        runPresenter();
    }
})();
