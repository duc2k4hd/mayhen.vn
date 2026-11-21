/**
 * MAYHEN.VN — NATIVE CRAFTING PRESENTER V9 (ZERO-DISTORTION ATELIER EDITION)
 * Trình diễn dựng thiệp mượt mà từ trên xuống dưới, tuyệt đối KHÔNG ghi đè display/transform/overflow gốc.
 * Khi hoàn tất hoặc bấm Bỏ qua, DOM trở về nguyên bản 100% y hệt lúc tải lại trang.
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

    // Nhịp trình diễn tinh tế, gọn gàng (~6s - 9s toàn bộ thiệp)
    const SWEEP_ANIMATION_MS = 460;
    const STEP_PAUSE_MS = 110;
    const WATCHDOG_MS = 14000;

    const liftCurtain = () => {
        const curtain = document.getElementById('mayhenCraftingCurtain');
        if (curtain) {
            curtain.style.transition = 'opacity 0.22s ease';
            curtain.style.opacity = '0';
            setTimeout(() => {
                if (curtain.parentNode) curtain.remove();
            }, 240);
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

        document.querySelectorAll('.mayhen_crafting_section_waiting, .mayhen_crafting_section_active').forEach((sec) => {
            sec.classList.remove('mayhen_crafting_section_waiting', 'mayhen_crafting_section_active');
        });

        document.querySelectorAll('.mayhen_crafting_unit, .mayhen_is_hidden, .mayhen_is_sweeping, .mayhen_is_revealed').forEach((el) => {
            el.classList.remove('mayhen_crafting_unit', 'mayhen_is_hidden', 'mayhen_is_sweeping', 'mayhen_is_revealed');
            el.classList.add('mayhen_invitation_visible', 'is-visible');
        });

        document.querySelectorAll('.mayhen_crafting_shimmer_beam').forEach((b) => b.remove());
    };

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        liftCurtain();
        cleanupCraftingStylesAndClasses();
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

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

    // CHỈ sử dụng opacity + clip-path, TUYỆT ĐỐI KHÔNG ghi đè display/overflow/transform của mẫu gốc!
    const styleEl = document.createElement('style');
    styleEl.id = 'mayhen-native-crafting-styles-v9';
    styleEl.textContent = `
        html.mayhen_is_crafting .mayhen_crafting_section_waiting,
        body.mayhen_is_crafting .mayhen_crafting_section_waiting {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        .mayhen_crafting_unit {
            will-change: clip-path, opacity;
        }
        .mayhen_crafting_unit.mayhen_is_hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            clip-path: inset(0 100% 0 0);
        }
        .mayhen_crafting_unit.mayhen_is_sweeping {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            animation: mayhenSweepRevealV9 0.46s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes mayhenSweepRevealV9 {
            0% {
                clip-path: inset(0 100% 0 0);
                opacity: 0.2;
            }
            20% {
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
            clip-path: none;
            pointer-events: auto !important;
        }
        .mayhen_crafting_shimmer_beam {
            position: absolute;
            top: -12%;
            left: -36px;
            width: 68px;
            height: 124%;
            background: linear-gradient(90deg,
                transparent 0%,
                rgba(207, 168, 96, 0.18) 18%,
                rgba(255, 235, 175, 0.9) 48%,
                #FFFFFF 55%,
                rgba(255, 225, 140, 0.85) 65%,
                rgba(207, 168, 96, 0.25) 85%,
                transparent 100%
            );
            box-shadow: 0 0 18px rgba(255, 232, 168, 0.8);
            transform: skewX(-16deg);
            pointer-events: none;
            z-index: 999;
            animation: mayhenBeamTrackV9 0.46s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes mayhenBeamTrackV9 {
            0% { left: -36px; opacity: 0; }
            18% { opacity: 0.95; }
            82% { opacity: 0.95; }
            100% { left: calc(100% - 12px); opacity: 0; }
        }
    `;
    document.head.appendChild(styleEl);

    const SECTION_RULES = [
        {
            key: 'hero',
            title: 'Trang Bìa Chính',
            container: '[data-mayhen-section*="hero"], .mayhen_invitation_hero, section.mayhen_invitation_sr_dau',
            unitSelectors: [
                { sel: '.mayhen_invitation_hero_badge, .mayhen_invitation_sr_kicker, .mayhen_invitation_hero__badge, .mayhen_invitation_hero_kicker, [data-sync-target="ceremony_type"]', label: '✦ Dệt biểu trưng hôn lễ' },
                { sel: '.mayhen_invitation_hero_couple, .mayhen_invitation_hero__names, .mayhen_invitation_sr_ten_cap, [data-sync-target="couple_names"]', label: '✦ Nắn nót tên Đôi Uyên Ương' },
                { sel: '.mayhen_invitation_hero_frame, .mayhen_invitation_hero_portrait, .mayhen_invitation_sr_quy_dao, .mayhen_invitation_hero_media, .mayhen_invitation_hero__frame, .mayhen_invitation_hero__photo_box, .mayhen_invitation_img_dau', label: '✦ Mở khung ảnh bìa hạnh phúc' },
                { sel: '[data-sync-target="wedding_date_digits"], [data-sync-target="wedding_date"], .mayhen_invitation_sr_ngay_dau, .mayhen_invitation_hero_date_pill, .mayhen_invitation_hero_date', label: '✦ Khắc ghi ngày trọng đại' }
            ]
        },
        {
            key: 'story',
            title: 'Lời Ngỏ & Chuyện Tình',
            container: '[data-mayhen-section*="story"], .mayhen_invitation_story',
            unitSelectors: [
                { sel: '[data-sync-target="story_title"], .mayhen_invitation_story_title, .mayhen_invitation_story__title, .mayhen_invitation_story .mayhen_invitation_section_header', label: '✦ Khởi mở Lời Ngỏ & Chuyện Tình' },
                { sel: '[data-sync-target="couple_quote"], .mayhen_invitation_quote_box, .mayhen_invitation_story__quote_box, .mayhen_invitation_intro_quote', label: '✦ AI chắp bút trích dẫn tâm tình' },
                { sel: '[data-sync-target="story_content"], .mayhen_invitation_story_content, .mayhen_invitation_story_list', label: '✦ Hoàn thiện câu chuyện tình yêu' }
            ]
        },
        {
            key: 'parents',
            title: 'Song Thân & Đôi Uyên Ương',
            container: '[data-mayhen-section*="parents"], [data-mayhen-section*="family"], .mayhen_invitation_parents, .mayhen_invitation_family',
            unitSelectors: [
                { sel: '.mayhen_invitation_family_grid, .mayhen_invitation_family_card, .mayhen_invitation_ben[data-ben="trai"]', label: '✦ Trang trọng giới thiệu Song Thân hai họ' },
                { sel: '.mayhen_invitation_couple_grid, .mayhen_invitation_announce_box, .mayhen_invitation_couple_box', label: '✦ Trân trọng báo tin lễ thành hôn' }
            ]
        },
        {
            key: 'countdown',
            title: 'Đếm Ngược & Lịch Tháng',
            container: '[data-mayhen-section*="countdown"], .mayhen_invitation_countdown_calendar, .mayhen_invitation_calendar',
            unitSelectors: [
                { sel: '.mayhen_invitation_calendar_card, .mayhen_invitation_sr_lich, .mayhen_invitation_calendar_grid, .mayhen_invitation_calendar__wrap', label: '✦ Đính kết lịch cưới Save The Date' },
                { sel: '.mayhen_invitation_countdown_grid, .mayhen_invitation_countdown, .mayhen_invitation_countdown_pending', label: '✦ Khởi tạo đồng hồ đếm ngược' }
            ]
        },
        {
            key: 'events',
            title: 'Thông Tin Sự Kiện & Bản Đồ',
            container: '[data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"], .mayhen_invitation_events',
            unitSelectors: [
                { sel: '.mayhen_invitation_time_card, .mayhen_invitation_party_card, .mayhen_invitation_sr_the_sk, .mayhen_invitation_event_card, .mayhen_invitation_ticket_card', label: '✦ Dàn trang nghi thức & sảnh tiệc' },
                { sel: '.mayhen_invitation_map_frame, .mayhen_invitation_sr_the_bando, .mayhen_invitation_map_card, .mayhen_invitation_map_box', label: '✦ Định vị bản đồ chỉ đường Google Maps' }
            ]
        },
        {
            key: 'timeline',
            title: 'Lịch Trình Hôn Lễ',
            container: '[data-mayhen-section*="timeline"], .mayhen_invitation_timeline',
            unitSelectors: [
                { sel: '.mayhen_invitation_tl_card, .mayhen_invitation_timeline_list, .mayhen_invitation_sr_truc', label: '✦ Phác họa lịch trình ngày cưới' }
            ]
        },
        {
            key: 'album',
            title: 'Khoảnh Khắc Hạnh Phúc',
            container: '[data-mayhen-section*="album"], [data-mayhen-section*="gallery"], .mayhen_invitation_gallery',
            unitSelectors: [
                { sel: '.mayhen_invitation_album_grid, .mayhen_invitation_gallery_grid, .mayhen_invitation_gallery__grid, .mayhen_invitation_tmx_album_cho', label: '✦ Sắp đặt Album 10 khoảnh khắc hạnh phúc' }
            ]
        }
    ];

    let isCompleted = false;
    let userInterrupted = false;
    let currentIndex = 0;
    const collectedUnits = [];
    const sectionContainers = [];

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

    const scrollToElementBeforeCrafting = (targetEl) => {
        return new Promise((resolve) => {
            if (userInterrupted || !targetEl) {
                setTimeout(resolve, 60);
                return;
            }
            const rect = targetEl.getBoundingClientRect();
            const viewportH = window.innerHeight || 667;
            if (rect.top >= viewportH * 0.12 && rect.bottom <= viewportH * 0.78) {
                setTimeout(resolve, 100);
                return;
            }
            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
            const targetScrollY = Math.max(0, currentScrollY + rect.top - (viewportH * 0.22));
            window.scrollTo({ top: targetScrollY, behavior: 'smooth' });
            const scrollDistance = Math.abs(targetScrollY - currentScrollY);
            const scrollDelay = Math.min(300, Math.max(120, Math.round(scrollDistance * 0.14)));
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
            total: collectedUnits.length
        }, '*');

        setTimeout(() => {
            if (!userInterrupted) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }, 280);
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
            if (unit.containerEl && unit.containerEl.classList.contains('mayhen_crafting_section_waiting')) {
                unit.containerEl.classList.remove('mayhen_crafting_section_waiting');
                unit.containerEl.classList.add('mayhen_crafting_section_active');
            }

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
            if (isCompleted) return;

            currentIndex++;
            revealNextUnit();
        } catch (err) {
            console.warn('[MayHen Crafting] Fallback:', err);
            revealAllInstantly();
        }
    };

    // Lắng nghe lệnh từ màn hình Curated Preview Studio
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
            revealAllInstantly();
        }
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

        await new Promise((res) => setTimeout(res, SWEEP_ANIMATION_MS + 100));

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

        SECTION_RULES.forEach((rule, rIdx) => {
            const containerEl = document.querySelector(`[data-curated-section="${rule.key}"]`)
                || document.querySelector(rule.container);
            if (!containerEl) return;

            if (!sectionContainers.includes(containerEl)) {
                sectionContainers.push(containerEl);
            }

            if (rIdx > 0) {
                containerEl.classList.add('mayhen_crafting_section_waiting');
            } else {
                containerEl.classList.add('mayhen_crafting_section_active');
            }

            let sectionHasSpecificUnits = false;
            rule.unitSelectors.forEach((unitSel) => {
                const els = containerEl.querySelectorAll(unitSel.sel);
                els.forEach((el) => {
                    if (el && !collectedUnits.some((u) => u.element === el || el.contains(u.element) || u.element.contains(el))) {
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

            if (!sectionHasSpecificUnits && !collectedUnits.some((u) => u.element === containerEl)) {
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
            revealAllInstantly();
            return;
        }

        collectedUnits.forEach((u) => {
            u.element.classList.add('mayhen_crafting_unit', 'mayhen_is_hidden');
        });

        document.documentElement.classList.remove('mayhen_crafting_preparing');
        liftCurtain();

        setTimeout(() => {
            if (!isCompleted) {
                revealAllInstantly();
            }
        }, WATCHDOG_MS);

        setTimeout(revealNextUnit, 220);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPresenter);
    } else {
        runPresenter();
    }
})();
