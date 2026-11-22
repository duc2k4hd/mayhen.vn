/**
 * MAYHEN.VN — NATIVE CRAFTING PRESENTER V10 (ATELIER STEP-BY-STEP BLUEPRINT EDITION)
 * Trình diễn quá trình AI dựng thiệp chậm rãi, tinh tế và chuyên nghiệp:
 * 1. Mọi section và mọi phần tử con đều bị ẩn 100% trước khi đến lượt (TUYỆT ĐỐI KHÔNG HIỂN THỊ TRƯỚC).
 * 2. Với từng Section: Tạo lớp nền giao diện (Canvas & Khung nền) trước -> Dựng lần lượt từng phần tử con từ trên xuống dưới kèm tia sáng vàng kim & khung viền blueprint.
 * 3. Khi hoàn tất hoặc bấm Bỏ qua, dọn sạch 100% class/style tạm để DOM trở về nguyên bản.
 * 100% Pure Native Vanilla JS, Zero External Libraries.
 */
(() => {
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

    // Nhịp trình diễn chậm rãi, đĩnh đạc, cảm nhận rõ từng lớp nền & từng phần tử được dựng (~22s - 32s toàn thiệp)
    const SECTION_BG_STAGE_MS = 680;
    const SECTION_BG_PAUSE_MS = 240;
    const SWEEP_ANIMATION_MS = 760;
    const STEP_PAUSE_MS = 280;
    const WATCHDOG_MS = 95000;

    const liftCurtain = () => {
        const curtain = document.getElementById('mayhenCraftingCurtain');
        if (curtain) {
            curtain.style.transition = 'opacity 0.42s ease';
            curtain.style.opacity = '0';
            setTimeout(() => {
                if (curtain.parentNode) curtain.remove();
            }, 440);
        }
    };

    const cleanupCraftingStylesAndClasses = () => {
        document.documentElement.classList.remove('mayhen_is_crafting', 'mayhen_crafting_preparing');
        document.body.classList.remove('mayhen_is_crafting');

        const initialCurtainStyle = document.getElementById('mayhenCraftingInitialCurtainStyles');
        if (initialCurtainStyle && initialCurtainStyle.parentNode) {
            initialCurtainStyle.remove();
        }
        const injectedStyle = document.getElementById('mayhen-native-crafting-styles-v9');
        if (injectedStyle && injectedStyle.parentNode) {
            injectedStyle.remove();
        }

        document.querySelectorAll(
            '.mayhen_crafting_section_waiting, .mayhen_crafting_section_active, .mayhen_crafting_section_canvas_forming, .mayhen_crafting_section_canvas_ready'
        ).forEach((sec) => {
            sec.classList.remove(
                'mayhen_crafting_section_waiting',
                'mayhen_crafting_section_active',
                'mayhen_crafting_section_canvas_forming',
                'mayhen_crafting_section_canvas_ready'
            );
            delete sec.dataset.mayhenCanvasLabel;
        });

        document.querySelectorAll('.mayhen_crafting_unit, .mayhen_is_hidden, .mayhen_is_sweeping, .mayhen_is_revealed').forEach((el) => {
            el.classList.remove('mayhen_crafting_unit', 'mayhen_is_hidden', 'mayhen_is_sweeping', 'mayhen_is_revealed');
            el.classList.add('mayhen_invitation_visible', 'is-visible');
            delete el.dataset.mayhenUnitLabel;
        });

        document.querySelectorAll('.mayhen_crafting_shimmer_beam, .mayhen_crafting_canvas_badge, #mayhenCraftingFloatingHud').forEach((b) => b.remove());
    };

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        liftCurtain();
        cleanupCraftingStylesAndClasses();
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

    const VOID_TAGS = new Set(['IMG', 'INPUT', 'HR', 'BR', 'SOURCE', 'TRACK', 'WBR', 'AREA', 'EMBED', 'IFRAME']);
    const attachBeamSafely = (targetEl, beamEl) => {
        if (!targetEl) return null;
        let host = targetEl;
        if (VOID_TAGS.has(targetEl.tagName.toUpperCase())) {
            host = targetEl.parentElement || targetEl;
        }
        if (host && !VOID_TAGS.has(host.tagName.toUpperCase())) {
            const comp = window.getComputedStyle(host).position;
            if (comp === 'static') {
                host.dataset.mayhenPrevPos = 'static';
                host.style.position = 'relative';
            }
            host.appendChild(beamEl);
            return host;
        }
        return null;
    };

    const restoreHostPos = (host) => {
        if (host && host.dataset.mayhenPrevPos === 'static') {
            host.style.removeProperty('position');
            delete host.dataset.mayhenPrevPos;
        }
    };

    const styleEl = document.createElement('style');
    styleEl.id = 'mayhen-native-crafting-styles-v9';
    styleEl.textContent = `
        /* 1. Khóa hoàn toàn mọi section chưa đến lượt — không bao giờ hiển thị trước */
        html.mayhen_is_crafting .mayhen_crafting_section_waiting,
        body.mayhen_is_crafting .mayhen_crafting_section_waiting {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* 2. Giai đoạn dựng Giao diện nền (Background Canvas Formation) của từng section */
        .mayhen_crafting_section_canvas_forming {
            position: relative;
            animation: mayhenSectionCanvasReveal 0.68s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            outline: 1.5px dashed rgba(197, 168, 128, 0.72);
            outline-offset: -8px;
            box-shadow: inset 0 0 0 2000px rgba(250, 246, 239, 0.28), 0 0 32px rgba(197, 168, 128, 0.18);
        }
        .mayhen_crafting_section_canvas_ready {
            outline: 1px solid rgba(197, 168, 128, 0.24);
            outline-offset: -6px;
            transition: outline-color 0.5s ease;
        }
        @keyframes mayhenSectionCanvasReveal {
            0% {
                opacity: 0;
                clip-path: inset(0 0 92% 0);
                filter: brightness(1.06);
            }
            45% {
                opacity: 1;
            }
            100% {
                opacity: 1;
                clip-path: inset(0 0 0% 0);
                filter: brightness(1);
            }
        }

        /* Nhãn chỉ báo đang dựng nền giao diện */
        .mayhen_crafting_canvas_badge {
            position: absolute;
            top: 14px;
            right: 16px;
            z-index: 9998;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(28, 25, 23, 0.86);
            color: #F4E2B8;
            border: 1px solid rgba(207, 168, 96, 0.65);
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.6px;
            pointer-events: none;
            box-shadow: 0 6px 18px rgba(28, 25, 23, 0.18);
            animation: mayhenBadgeFadeIn 0.25s ease forwards;
        }
        .mayhen_crafting_canvas_badge::before {
            content: '✦';
            color: #CFA860;
            font-size: 11px;
        }
        @keyframes mayhenBadgeFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* 3. Khóa 100% mọi phần tử con chưa đến lượt tạo */
        .mayhen_crafting_unit {
            will-change: clip-path, opacity, filter;
        }
        .mayhen_crafting_unit.mayhen_is_hidden,
        html.mayhen_is_crafting .mayhen_crafting_unit.mayhen_is_hidden,
        body.mayhen_is_crafting .mayhen_crafting_unit.mayhen_is_hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            clip-path: inset(0 100% 0 0) !important;
        }

        /* 4. Hiệu ứng dựng từng phần tử (Blueprint Frame + Silk Sweep) */
        .mayhen_crafting_unit.mayhen_is_sweeping {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            outline: 1.5px dashed rgba(197, 168, 128, 0.85);
            outline-offset: 4px;
            box-shadow: 0 0 0 4px rgba(207, 168, 96, 0.12), 0 10px 26px rgba(140, 109, 63, 0.14);
            border-radius: 6px;
            animation: mayhenSweepRevealV10 0.76s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes mayhenSweepRevealV10 {
            0% {
                clip-path: inset(0 100% 0 0);
                opacity: 0.08;
                filter: blur(2px) brightness(1.06);
            }
            28% {
                opacity: 1;
                filter: blur(0.5px) brightness(1.03);
            }
            100% {
                clip-path: inset(0 0% 0 0);
                opacity: 1;
                filter: blur(0) brightness(1);
            }
        }
        .mayhen_crafting_unit.mayhen_is_revealed {
            opacity: 1 !important;
            visibility: visible !important;
            clip-path: none !important;
            outline: none;
            pointer-events: auto !important;
            transition: box-shadow 0.35s ease;
        }

        /* 5. Tia sáng kim tuyến dệt qua phần tử */
        .mayhen_crafting_shimmer_beam {
            position: absolute;
            top: -10%;
            left: -44px;
            width: 82px;
            height: 120%;
            background: linear-gradient(90deg,
                transparent 0%,
                rgba(207, 168, 96, 0.16) 18%,
                rgba(255, 236, 182, 0.88) 48%,
                #FFFFFF 54%,
                rgba(255, 224, 142, 0.85) 64%,
                rgba(207, 168, 96, 0.22) 85%,
                transparent 100%
            );
            box-shadow: 0 0 22px rgba(255, 232, 168, 0.85);
            transform: skewX(-16deg);
            pointer-events: none;
            z-index: 999;
            animation: mayhenBeamTrackV10 0.76s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes mayhenBeamTrackV10 {
            0% { left: -44px; opacity: 0; }
            15% { opacity: 0.96; }
            85% { opacity: 0.96; }
            100% { left: calc(100% - 8px); opacity: 0; }
        }

        /* 6. Thanh HUD nổi dưới đáy khung thiệp hiển thị trực tiếp phần tử đang được AI dựng */
        #mayhenCraftingFloatingHud {
            position: fixed;
            left: 50%;
            bottom: 18px;
            transform: translateX(-50%);
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            border-radius: 999px;
            background: rgba(28, 25, 23, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(207, 168, 96, 0.65);
            box-shadow: 0 12px 32px rgba(28, 25, 23, 0.28);
            color: #FAF7F2;
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 12px;
            max-width: calc(100vw - 28px);
            pointer-events: none;
        }
        #mayhenCraftingFloatingHud .hud_dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #CFA860;
            box-shadow: 0 0 10px #CFA860;
            flex-shrink: 0;
            animation: mayhenHudPulse 1.1s infinite ease-in-out;
        }
        @keyframes mayhenHudPulse {
            0%, 100% { transform: scale(0.85); opacity: 0.6; }
            50% { transform: scale(1.25); opacity: 1; }
        }
        #mayhenCraftingFloatingHud .hud_text {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #F4E2B8;
        }
        #mayhenCraftingFloatingHud .hud_step {
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(207, 168, 96, 0.2);
            color: #FFFDF9;
            flex-shrink: 0;
        }
    `;
    document.head.appendChild(styleEl);

    const SECTION_RULES = [
        {
            key: 'hero',
            title: 'Trang Bìa Chính',
            bgLabel: '✦ Đang dựng giao diện nền & khung vòm Trang Bìa Chính...',
            container: '[data-mayhen-section*="hero"], .mayhen_invitation_hero, section.mayhen_invitation_sr_dau',
            unitSelectors: [
                { sel: '.mayhen_invitation_hero_badge, .mayhen_invitation_sr_kicker, .mayhen_invitation_hero__badge, .mayhen_invitation_hero_kicker, [data-sync-target="ceremony_type"]', label: '✦ Khắc họa tiêu đề nghi lễ thành hôn' },
                { sel: '[data-sync-target="groom_name"], [data-sync-target="bride_name"], .mayhen_invitation_hero_couple, .mayhen_invitation_hero__names, .mayhen_invitation_sr_ten_cap, [data-sync-target="couple_names"]', label: '✦ Chế tác nghệ thuật chữ tên Đôi Uyên Ương' },
                { sel: '.mayhen_invitation_hero_frame, .mayhen_invitation_hero_portrait, .mayhen_invitation_sr_quy_dao, .mayhen_invitation_hero_media, .mayhen_invitation_hero__frame, .mayhen_invitation_hero__photo_box, .mayhen_invitation_img_dau', label: '✦ Lồng ghép khung ảnh bìa & viền vàng kim' },
                { sel: '[data-sync-target="wedding_date_digits"], [data-sync-target="wedding_date"], .mayhen_invitation_sr_ngay_dau, .mayhen_invitation_hero_date_pill, .mayhen_invitation_hero_date', label: '✦ Đính kết dấu ấn ngày chung đôi' },
                { sel: '[data-sync-target="hero_venue"], [data-sync-target="venue_name"], .mayhen_invitation_hero_venue, .mayhen_invitation_hero_location', label: '✦ Trình bày thông tin sảnh tiệc trang bìa' }
            ]
        },
        {
            key: 'story',
            title: 'Lời Ngỏ & Chuyện Tình',
            bgLabel: '✦ Đang tạo lớp nền giấy mỹ thuật mục Chuyện Tình Yêu...',
            container: '[data-mayhen-section*="story"], .mayhen_invitation_story',
            unitSelectors: [
                { sel: '.mayhen_invitation_eyebrow, [data-sync-target="story_title"], .mayhen_invitation_story_title, .mayhen_invitation_story__title, .mayhen_invitation_story .mayhen_invitation_section_header', label: '✦ Dựng tiêu đề chương Chuyện Tình Yêu' },
                { sel: '[data-sync-target="couple_quote"], .mayhen_invitation_quote_box, .mayhen_invitation_story__quote_box, .mayhen_invitation_intro_quote, blockquote', label: '✦ AI chắp bút trích dẫn tâm tình riêng cho hai bạn' },
                { sel: '[data-sync-target="story_content"], .mayhen_invitation_story_content, .mayhen_invitation_story_item, .mayhen_invitation_story_card', label: '✦ Dàn trang từng dòng tự sự & cột mốc kỷ niệm' }
            ]
        },
        {
            key: 'parents',
            title: 'Song Thân & Đôi Uyên Ương',
            bgLabel: '✦ Đang dựng bố cục nền đối xứng Gia Đình Hai Họ...',
            container: '[data-mayhen-section*="parents"], [data-mayhen-section*="family"], .mayhen_invitation_parents, .mayhen_invitation_family',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_family_header', label: '✦ Mở đầu mục Trân Trọng Báo Tin' },
                { sel: '.mayhen_invitation_family_side, .mayhen_invitation_family_col, .mayhen_invitation_family_card, .mayhen_invitation_ben, .mayhen_invitation_family_grid', label: '✦ Trang trọng khắc tên Song Thân Nhà Trai & Nhà Gái' },
                { sel: '.mayhen_invitation_couple_card, .mayhen_invitation_couple_item, .mayhen_invitation_couple_grid, .mayhen_invitation_announce_box, .mayhen_invitation_couple_box', label: '✦ Hoàn thiện thẻ giới thiệu Cô Dâu & Chú Rể' }
            ]
        },
        {
            key: 'countdown',
            title: 'Đếm Ngược & Lịch Tháng',
            bgLabel: '✦ Đang tạo giao diện nền Lịch Cưới Save The Date...',
            container: '[data-mayhen-section*="countdown"], .mayhen_invitation_countdown_calendar, .mayhen_invitation_calendar',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header, .mayhen_invitation_calendar_header', label: '✦ Tạo tiêu đề Lịch Tháng & Thời Khắc Hẹn Ước' },
                { sel: '.mayhen_invitation_calendar_card, .mayhen_invitation_sr_lich, .mayhen_invitation_calendar_grid, .mayhen_invitation_calendar__wrap, table', label: '✦ Đánh dấu trái tim ngày cưới trên tờ lịch tháng' },
                { sel: '.mayhen_invitation_countdown_item, .mayhen_invitation_countdown_box, .mayhen_invitation_countdown_grid, .mayhen_invitation_countdown', label: '✦ Kích hoạt từng ô đồng hồ đếm ngược Ngày - Giờ - Phút - Giây' }
            ]
        },
        {
            key: 'events',
            title: 'Thông Tin Sự Kiện & Bản Đồ',
            bgLabel: '✦ Đang dựng giao diện nền Thiệp Mời Tiệc & Bản Đồ...',
            container: '[data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"], .mayhen_invitation_events',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header', label: '✦ Khởi tạo tiêu đề Nghi Thức & Tiệc Mừng' },
                { sel: '.mayhen_invitation_time_card, .mayhen_invitation_party_card, .mayhen_invitation_sr_the_sk, .mayhen_invitation_event_card, .mayhen_invitation_ticket_card', label: '✦ Dựng từng thẻ sự kiện, giờ đón khách & địa chỉ sảnh tiệc' },
                { sel: '.mayhen_invitation_map_frame, .mayhen_invitation_sr_the_bando, .mayhen_invitation_map_card, .mayhen_invitation_map_box, .mayhen_invitation_map_btn', label: '✦ Kết nối khung bản đồ chỉ đường thông minh' }
            ]
        },
        {
            key: 'timeline',
            title: 'Lịch Trình Hôn Lễ',
            bgLabel: '✦ Đang tạo trục nền Timeline Kịch Bản Ngày Cưới...',
            container: '[data-mayhen-section*="timeline"], .mayhen_invitation_timeline',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header', label: '✦ Dựng tiêu đề Kịch Bản Đón Khách' },
                { sel: '.mayhen_invitation_timeline_item, .mayhen_invitation_tl_item, .mayhen_invitation_tl_card, .mayhen_invitation_timeline_list, .mayhen_invitation_sr_truc', label: '✦ Gắn từng mốc giờ Đón Khách - Làm Lễ - Khai Tiệc' }
            ]
        },
        {
            key: 'dresscode',
            title: 'Quy Định Trang Phục',
            bgLabel: '✦ Đang dựng bảng nền Dresscode & Bảng Màu Khách Mời...',
            container: '[data-mayhen-section*="dresscode"], .mayhen_invitation_dresscode',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header', label: '✦ Khởi tạo tiêu đề Gợi Ý Trang Phục' },
                { sel: '.mayhen_invitation_dresscode_swatches, .mayhen_invitation_dresscode_item, .mayhen_invitation_dresscode_card', label: '✦ Phối bảng chấm màu Dresscode thanh lịch' }
            ]
        },
        {
            key: 'album',
            title: 'Khoảnh Khắc Hạnh Phúc',
            bgLabel: '✦ Đang tạo khung nền phòng triển lãm Album Ảnh Cưới...',
            container: '[data-mayhen-section*="album"], [data-mayhen-section*="gallery"], .mayhen_invitation_gallery',
            unitSelectors: [
                { sel: '.mayhen_invitation_section_header', label: '✦ Dựng tiêu đề Album Khoảnh Khắc Tình Yêu' },
                { sel: '.mayhen_invitation_gallery_item, .mayhen_invitation_album_item, .mayhen_invitation_album_grid figure, .mayhen_invitation_gallery_grid figure, .mayhen_invitation_album_grid, .mayhen_invitation_gallery_grid', label: '✦ Tráng rửa & sắp đặt từng khung hình kỷ niệm' }
            ]
        },
        {
            key: 'ending',
            title: 'Sổ Lưu Bút, RSVP & Mừng Cưới',
            bgLabel: '✦ Đang dựng giao diện nền Tương Tác Khách Mời & Lời Cảm Ơn...',
            container: '[data-mayhen-section*="ending"], [data-mayhen-section*="rsvp"], [data-mayhen-section*="gift"], .mayhen_invitation_ending, .mayhen_invitation_rsvp',
            unitSelectors: [
                { sel: '.mayhen_invitation_rsvp_card, .mayhen_invitation_rsvp_form, form', label: '✦ Dựng biểu mẫu Xác Nhận Tham Dự (RSVP)' },
                { sel: '.mayhen_invitation_signature_card, .mayhen_invitation_guestbook_card, .mayhen_invitation_wish_box', label: '✦ Khởi tạo Sổ Ký Tên & Lưu Bút Chúc Phúc' },
                { sel: '.mayhen_invitation_gift_card, .mayhen_invitation_qr_card, .mayhen_invitation_thankyou', label: '✦ Hoàn thiện Hộp Mừng Cưới VietQR & Lời Cảm Ơn' }
            ]
        }
    ];

    let isCompleted = false;
    let userInterrupted = false;
    let currentStepIdx = 0;

    // Danh sách kịch bản tuần tự gồm cả bước dựng nền (type: 'section_bg') và bước dựng từng phần tử (type: 'unit')
    const timelineSteps = [];

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

    const ensureFloatingHud = () => {
        let hud = document.getElementById('mayhenCraftingFloatingHud');
        if (!hud) {
            hud = document.createElement('div');
            hud.id = 'mayhenCraftingFloatingHud';
            hud.innerHTML = `
                <span class="hud_dot"></span>
                <span class="hud_text" id="mayhenHudLabel">✦ Đang khởi tạo giao diện nền...</span>
                <span class="hud_step" id="mayhenHudStep">1/1</span>
            `;
            document.body.appendChild(hud);
        }
        return hud;
    };

    const updateFloatingHud = (label, step, total) => {
        ensureFloatingHud();
        const textEl = document.getElementById('mayhenHudLabel');
        const stepEl = document.getElementById('mayhenHudStep');
        if (textEl) textEl.textContent = label;
        if (stepEl) stepEl.textContent = `${step}/${total}`;
    };

    const scrollToElementBeforeCrafting = (targetEl) => {
        return new Promise((resolve) => {
            if (userInterrupted || !targetEl) {
                setTimeout(resolve, 90);
                return;
            }
            const rect = targetEl.getBoundingClientRect();
            const viewportH = window.innerHeight || 667;
            if (rect.top >= viewportH * 0.12 && rect.bottom <= viewportH * 0.82) {
                setTimeout(resolve, 140);
                return;
            }
            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
            const targetScrollY = Math.max(0, currentScrollY + rect.top - (viewportH * 0.2));
            window.scrollTo({ top: targetScrollY, behavior: 'smooth' });
            const scrollDistance = Math.abs(targetScrollY - currentScrollY);
            const scrollDelay = Math.min(460, Math.max(200, Math.round(scrollDistance * 0.22)));
            setTimeout(resolve, scrollDelay);
        });
    };

    const revealAllInstantly = () => {
        if (isCompleted) return;
        isCompleted = true;

        liftCurtain();
        cleanupCraftingStylesAndClasses();

        window.parent?.postMessage({
            type: 'MAYHEN_CRAFTING_COMPLETE',
            total: timelineSteps.length
        }, '*');

        setTimeout(() => {
            if (!userInterrupted) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }, 350);
    };

    /**
     * Thu thập toàn bộ phần tử con độc lập bên trong một section để đảm bảo
     * KHÔNG CÓ BẤT KỲ PHẦN TỬ NÀO hiển thị ra trước khi đến lượt dựng.
     */
    const collectGranularUnitsForSection = (containerEl, rule) => {
        const units = [];
        const addedSet = new Set();

        const pushUnit = (el, label) => {
            if (!el || addedSet.has(el)) return;
            if (['SCRIPT', 'STYLE', 'LINK', 'META'].includes(el.tagName)) return;
            // Tránh thêm cha nếu đã thêm con hoặc ngược lại
            for (const existing of addedSet) {
                if (existing.contains(el) || el.contains(existing)) {
                    return;
                }
            }
            addedSet.add(el);
            units.push({
                element: el,
                label: label,
            });
        };

        // 1. Ưu tiên quét theo các selector ngữ nghĩa của từng section
        rule.unitSelectors.forEach((unitSel) => {
            const matched = containerEl.querySelectorAll(unitSel.sel);
            matched.forEach((el) => {
                // Nếu phần tử là một lưới chứa nhiều thẻ con (như gallery grid, countdown grid, timeline list), tách từng thẻ con tối đa 6 mục để hiệu ứng dựng từng phần tử rõ nét
                const childCards = el.querySelectorAll(':scope > figure, :scope > .mayhen_invitation_gallery_item, :scope > .mayhen_invitation_countdown_item, :scope > .mayhen_invitation_countdown_box, :scope > .mayhen_invitation_timeline_item, :scope > .mayhen_invitation_event_card, :scope > .mayhen_invitation_party_card, :scope > .mayhen_invitation_family_side');
                if (childCards.length > 1 && childCards.length <= 12) {
                    childCards.forEach((card, idx) => {
                        pushUnit(card, `${unitSel.label} (${idx + 1}/${childCards.length})`);
                    });
                } else {
                    pushUnit(el, unitSel.label);
                }
            });
        });

        // 2. Quét vét mọi phần tử có [data-sync-target] hoặc các khối con trực tiếp chưa nằm trong danh sách
        // để đảm bảo 100% phần tử trong section đều được ẩn lúc đầu và được dựng tuần tự
        const fallbackCandidates = containerEl.querySelectorAll(
            '[data-sync-target], h1, h2, h3, p, blockquote, figure, img, table, form, .mayhen_invitation_card, [class*="_card"], [class*="_box"], [class*="_btn"]'
        );
        fallbackCandidates.forEach((cand) => {
            if (cand === containerEl) return;
            let covered = false;
            for (const existing of addedSet) {
                if (existing === cand || existing.contains(cand) || cand.contains(existing)) {
                    covered = true;
                    break;
                }
            }
            if (!covered) {
                addedSet.add(cand);
                units.push({
                    element: cand,
                    label: `✦ Dựng chi tiết ${rule.title}`,
                });
            }
        });

        // Nếu một section quá đặc biệt không khớp selector con nào, lấy các con trực tiếp của nó
        if (units.length === 0) {
            Array.from(containerEl.children).forEach((child, idx) => {
                if (!['SCRIPT', 'STYLE'].includes(child.tagName)) {
                    pushUnit(child, `✦ Dựng thành phần ${rule.title} (#${idx + 1})`);
                }
            });
        }

        // Sắp xếp các phần tử theo đúng thứ tự xuất hiện trong DOM từ trên xuống dưới, trái sang phải
        units.sort((a, b) => {
            if (a.element === b.element) return 0;
            const pos = a.element.compareDocumentPosition(b.element);
            return (pos & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1;
        });

        return units;
    };

    const runNextTimelineStep = async () => {
        if (isCompleted) return;
        if (currentStepIdx >= timelineSteps.length) {
            revealAllInstantly();
            return;
        }

        const stepObj = timelineSteps[currentStepIdx];
        const percent = Math.min(100, Math.round(((currentStepIdx + 1) / timelineSteps.length) * 100));

        try {
            window.parent?.postMessage({
                type: 'MAYHEN_CRAFTING_STEP',
                section: stepObj.sectionKey,
                sectionTitle: stepObj.sectionTitle,
                detailTitle: stepObj.label,
                step: currentStepIdx + 1,
                total: timelineSteps.length,
                percent: percent,
            }, '*');

            updateFloatingHud(stepObj.label, currentStepIdx + 1, timelineSteps.length);

            if (stepObj.type === 'section_bg') {
                // BƯỚC A: DỰNG GIAO DIỆN NỀN CỦA SECTION (Trong khi toàn bộ phần tử con bên trong vẫn ẩn 100%)
                const secEl = stepObj.containerEl;
                secEl.classList.remove('mayhen_crafting_section_waiting');
                secEl.classList.add('mayhen_crafting_section_active', 'mayhen_crafting_section_canvas_forming');

                const badge = document.createElement('div');
                badge.className = 'mayhen_crafting_canvas_badge';
                badge.textContent = `Giao diện nền: ${stepObj.sectionTitle}`;
                const host = attachBeamSafely(secEl, badge);

                await scrollToElementBeforeCrafting(secEl);
                if (isCompleted) {
                    if (badge.parentNode) badge.remove();
                    restoreHostPos(host);
                    return;
                }

                await new Promise((res) => setTimeout(res, SECTION_BG_STAGE_MS));
                if (isCompleted) {
                    if (badge.parentNode) badge.remove();
                    restoreHostPos(host);
                    return;
                }

                secEl.classList.remove('mayhen_crafting_section_canvas_forming');
                secEl.classList.add('mayhen_crafting_section_canvas_ready');
                if (badge.parentNode) badge.remove();
                restoreHostPos(host);

                await new Promise((res) => setTimeout(res, SECTION_BG_PAUSE_MS));
            } else {
                // BƯỚC B: DỰNG TỪNG PHẦN TỬ CON BÊN TRONG GIAO DIỆN NỀN ĐÃ CÓ
                const el = stepObj.element;
                await scrollToElementBeforeCrafting(el);
                if (isCompleted) return;

                el.classList.remove('mayhen_is_hidden');
                el.classList.add('mayhen_is_sweeping');

                const beam = document.createElement('div');
                beam.className = 'mayhen_crafting_shimmer_beam';
                const host = attachBeamSafely(el, beam);

                await new Promise((res) => setTimeout(res, SWEEP_ANIMATION_MS));
                if (isCompleted) {
                    if (beam.parentNode) beam.remove();
                    restoreHostPos(host);
                    return;
                }

                el.classList.remove('mayhen_is_sweeping');
                el.classList.add('mayhen_is_revealed', 'mayhen_invitation_visible', 'is-visible');
                if (beam.parentNode) beam.remove();
                restoreHostPos(host);

                await new Promise((res) => setTimeout(res, STEP_PAUSE_MS));
            }

            if (isCompleted) return;
            currentStepIdx++;
            runNextTimelineStep();
        } catch (err) {
            console.warn('[MayHen Crafting] Fallback:', err);
            revealAllInstantly();
        }
    };

    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_SCROLL_TO_SECTION') {
            const sec = e.data.section;
            const rule = SECTION_RULES.find((r) => r.key === sec);
            const targetEl = document.querySelector(`[data-curated-section="${sec}"]`)
                || document.querySelector(`[data-mayhen-section*="${sec}"]`)
                || (rule ? document.querySelector(rule.container) : null);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });

    const runSectionRecraft = async (sectionKey) => {
        liftCurtain();
        document.documentElement.classList.remove('mayhen_crafting_preparing');

        const rule = SECTION_RULES.find((r) => r.key === sectionKey);
        const targetSectionEl = document.querySelector(`[data-curated-section="${sectionKey}"]`)
            || document.querySelector(`[data-mayhen-section*="${sectionKey}"]`)
            || (rule ? document.querySelector(rule.container) : null);

        if (!targetSectionEl) {
            cleanupCraftingStylesAndClasses();
            window.parent?.postMessage({ type: 'MAYHEN_SECTION_CRAFT_COMPLETE', section: sectionKey }, '*');
            return;
        }

        await scrollToElementBeforeCrafting(targetSectionEl);
        targetSectionEl.classList.add('mayhen_crafting_unit', 'mayhen_is_sweeping');
        const beam = document.createElement('div');
        beam.className = 'mayhen_crafting_shimmer_beam';
        const host = attachBeamSafely(targetSectionEl, beam);

        await new Promise((res) => setTimeout(res, SWEEP_ANIMATION_MS + 150));

        if (beam.parentNode) beam.remove();
        restoreHostPos(host);
        cleanupCraftingStylesAndClasses();

        window.parent?.postMessage({
            type: 'MAYHEN_SECTION_CRAFT_COMPLETE',
            section: sectionKey,
        }, '*');
    };

    const runPresenter = () => {
        if (recraftSectionKey) {
            runSectionRecraft(recraftSectionKey);
            return;
        }

        const matchedContainers = new Set();

        SECTION_RULES.forEach((rule) => {
            const containerEl = document.querySelector(`[data-curated-section="${rule.key}"]`)
                || document.querySelector(rule.container);
            if (!containerEl || matchedContainers.has(containerEl)) return;

            matchedContainers.add(containerEl);

            // Mọi section (kể cả section đầu tiên) đều bắt đầu ở trạng thái waiting để không bao giờ hiển thị trước!
            containerEl.classList.add('mayhen_crafting_section_waiting');

            // Bước 1 của Section: Dựng giao diện nền (Background Canvas)
            timelineSteps.push({
                type: 'section_bg',
                sectionKey: rule.key,
                sectionTitle: rule.title,
                label: rule.bgLabel || `✦ Đang dựng giao diện nền ${rule.title}...`,
                containerEl: containerEl,
            });

            // Bước 2..N của Section: Dựng lần lượt từng phần tử con bên trong
            const sectionUnits = collectGranularUnitsForSection(containerEl, rule);
            sectionUnits.forEach((u) => {
                u.element.classList.add('mayhen_crafting_unit', 'mayhen_is_hidden');
                timelineSteps.push({
                    type: 'unit',
                    sectionKey: rule.key,
                    sectionTitle: rule.title,
                    label: u.label,
                    element: u.element,
                    containerEl: containerEl,
                });
            });
        });

        // Quét thêm bất kỳ <section> nào khác trong thiệp chưa nằm trong SECTION_RULES để đảm bảo không có section nào lọt lưới hiện ra trước
        document.querySelectorAll('section, .mayhen_invitation_section, [data-mayhen-section]').forEach((extraSec, idx) => {
            if (matchedContainers.has(extraSec)) return;
            if (extraSec.classList.contains('mayhen_invitation_opening') || extraSec.classList.contains('mayhen_invitation_sr_cua')) return;
            // Nếu extraSec nằm bên trong một container đã xử lý thì bỏ qua
            for (const m of matchedContainers) {
                if (m.contains(extraSec) || extraSec.contains(m)) return;
            }
            matchedContainers.add(extraSec);
            extraSec.classList.add('mayhen_crafting_section_waiting');

            const fallbackRule = {
                key: `extra_${idx}`,
                title: 'Chi Tiết Thiệp Cưới',
                unitSelectors: [],
            };
            timelineSteps.push({
                type: 'section_bg',
                sectionKey: fallbackRule.key,
                sectionTitle: fallbackRule.title,
                label: '✦ Đang dựng giao diện nền phần tiếp theo...',
                containerEl: extraSec,
            });
            const extraUnits = collectGranularUnitsForSection(extraSec, fallbackRule);
            extraUnits.forEach((u) => {
                u.element.classList.add('mayhen_crafting_unit', 'mayhen_is_hidden');
                timelineSteps.push({
                    type: 'unit',
                    sectionKey: fallbackRule.key,
                    sectionTitle: fallbackRule.title,
                    label: u.label,
                    element: u.element,
                    containerEl: extraSec,
                });
            });
        });

        if (timelineSteps.length === 0) {
            revealAllInstantly();
            return;
        }

        // Gỡ lớp preparing và mở rèm lụa khi toàn bộ section & phần tử đã được khóa ẩn 100%
        document.documentElement.classList.remove('mayhen_crafting_preparing');
        ensureFloatingHud();
        liftCurtain();

        setTimeout(() => {
            if (!isCompleted) {
                revealAllInstantly();
            }
        }, WATCHDOG_MS);

        setTimeout(runNextTimelineStep, 480);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPresenter);
    } else {
        runPresenter();
    }
})();
