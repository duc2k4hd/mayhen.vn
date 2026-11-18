/**
 * MÂY HẸN — STUDIO INTERACTIVE PRODUCT TOUR (/tao-thiep ONLY)
 * Kiến trúc: 100% Pure Vanilla JavaScript (ES Modules), Zero external libraries.
 * Bảo toàn tuyệt đối dữ liệu thiệp cưới: chỉ điều khiển giao diện xem hướng dẫn,
 * không bao giờ sửa dữ liệu cưới, không gọi autosave, không phát nhạc tự động,
 * không lưu bất kỳ thông tin cá nhân nào vào LocalStorage.
 */

export const TOUR_STORAGE_NAMESPACE = 'mayhen:editor:product-tour';
export const TOUR_STORAGE_VERSION = 1;

const BLOCKING_DIALOG_SELECTOR = [
    '#mayhen_editor_recovery:not([hidden])',
    '.mayhen_editor_revision_error',
    '.mayhen_cropper_modal.mayhen_editor_modal_open',
    '.mayhen_editor_template_modal.mayhen_editor_modal_open',
    '.mayhen_music_picker_modal.mayhen_editor_modal_open',
    '.mayhen_editor_ai_story_modal.mayhen_editor_modal_open',
    '.mayhen_editor_quality_doctor_modal.mayhen_editor_modal_open',
    '.mayhen_editor_modal_preview.mayhen_editor_modal_open',
].join(', ');

export function resolveTourStorageKey(userId = null) {
    const cleanScope = userId !== null && userId !== undefined && String(userId).trim() !== '' && String(userId) !== 'null'
        ? `u_${String(userId).replace(/[^a-zA-Z0-9_-]/g, '')}`
        : 'guest';
    return `${TOUR_STORAGE_NAMESPACE}:v${TOUR_STORAGE_VERSION}:${cleanScope}`;
}

/**
 * Quản lý lưu trữ trạng thái đã xem / bỏ qua / đang xem dở của Hướng dẫn.
 * Tuyệt đối chỉ lưu trạng thái tour (version, status, last_step_id, updated_at),
 * an toàn tuyệt đối khi trình duyệt chặn LocalStorage / SessionStorage (chế độ ẩn danh nghiêm ngặt).
 */
export class TourPersistence {
    constructor({ userId = null, localStore = globalThis.localStorage, sessionStore = globalThis.sessionStorage } = {}) {
        this.key = resolveTourStorageKey(userId);
        this.localStore = localStore;
        this.sessionStore = sessionStore;
        this.memoryRecord = null;
    }

    read() {
        const raw = this.#safeGet(this.localStore) ?? this.#safeGet(this.sessionStore);
        if (!raw) {
            return this.memoryRecord;
        }
        try {
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object' || parsed.version !== TOUR_STORAGE_VERSION) {
                return null;
            }
            if (!['completed', 'skipped', 'in_progress'].includes(parsed.status)) {
                return null;
            }
            return {
                version: TOUR_STORAGE_VERSION,
                status: parsed.status,
                last_step_id: typeof parsed.last_step_id === 'string' ? parsed.last_step_id : null,
                updated_at: typeof parsed.updated_at === 'string' ? parsed.updated_at : null,
            };
        } catch (err) {
            return this.memoryRecord;
        }
    }

    shouldAutoStart() {
        const state = this.read();
        if (!state) return true;
        return state.status !== 'completed' && state.status !== 'skipped';
    }

    markInProgress(stepId) {
        this.#write({
            version: TOUR_STORAGE_VERSION,
            status: 'in_progress',
            last_step_id: String(stepId || ''),
            updated_at: new Date().toISOString(),
        });
    }

    markSkipped(stepId = null) {
        this.#write({
            version: TOUR_STORAGE_VERSION,
            status: 'skipped',
            last_step_id: stepId ? String(stepId) : null,
            updated_at: new Date().toISOString(),
        });
    }

    markCompleted() {
        this.#write({
            version: TOUR_STORAGE_VERSION,
            status: 'completed',
            last_step_id: null,
            updated_at: new Date().toISOString(),
        });
    }

    #safeGet(storage) {
        if (!storage || typeof storage.getItem !== 'function') return null;
        try {
            return storage.getItem(this.key);
        } catch (err) {
            return null;
        }
    }

    #write(record) {
        this.memoryRecord = record;
        const serialized = JSON.stringify(record);
        let savedLocal = false;
        if (this.localStore && typeof this.localStore.setItem === 'function') {
            try {
                this.localStore.setItem(this.key, serialized);
                savedLocal = true;
            } catch (err) {
                savedLocal = false;
            }
        }
        if (!savedLocal && this.sessionStore && typeof this.sessionStore.setItem === 'function') {
            try {
                this.sessionStore.setItem(this.key, serialized);
            } catch (err) {}
        }
    }
}

/**
 * Thuật toán tính toán tọa độ Spotlight và tự lật vị trí Popover chống tràn màn hình 100%.
 */
export function computePopoverPlacement({
    targetRect,
    popoverWidth,
    popoverHeight,
    viewportWidth,
    viewportHeight,
    preferredSide = 'right',
}) {
    const pad = 12;
    const gap = 14;
    const vw = Math.max(280, Number(viewportWidth) || 1280);
    const vh = Math.max(360, Number(viewportHeight) || 800);
    const pw = Math.min(Math.max(220, Number(popoverWidth) || 340), vw - pad * 2);
    const ph = Math.min(Math.max(120, Number(popoverHeight) || 210), vh - pad * 2);

    if (!targetRect || targetRect.width <= 0 || targetRect.height <= 0) {
        return {
            side: 'center',
            left: Math.round((vw - pw) / 2),
            top: Math.round(Math.max(pad, (vh - ph) / 2)),
        };
    }

    // Mobile layout (< 768px): ghim gọn ở nửa đối diện để không bao giờ che phần tử đang spotlight
    if (vw < 768) {
        const targetMidY = targetRect.top + targetRect.height / 2;
        const placeAtTop = targetMidY > vh * 0.54;
        const top = placeAtTop
            ? Math.max(58, Math.min(vh - ph - pad, pad + 48))
            : Math.max(pad, vh - ph - pad);
        return {
            side: placeAtTop ? 'mobile-top' : 'mobile-bottom',
            left: pad,
            top: Math.round(top),
        };
    }

    const orderMap = {
        right: ['right', 'left', 'bottom', 'top'],
        left: ['left', 'right', 'bottom', 'top'],
        bottom: ['bottom', 'top', 'right', 'left'],
        top: ['top', 'bottom', 'right', 'left'],
    };
    const sides = orderMap[preferredSide] || orderMap.right;

    const clampX = (x) => Math.round(Math.max(pad, Math.min(vw - pw - pad, x)));
    const clampY = (y) => Math.round(Math.max(pad, Math.min(vh - ph - pad, y)));

    for (const side of sides) {
        if (side === 'right' && vw - targetRect.right >= pw + gap + pad) {
            return {
                side: 'right',
                left: Math.round(targetRect.right + gap),
                top: clampY(targetRect.top + (targetRect.height - ph) / 2),
            };
        }
        if (side === 'left' && targetRect.left >= pw + gap + pad) {
            return {
                side: 'left',
                left: Math.round(targetRect.left - pw - gap),
                top: clampY(targetRect.top + (targetRect.height - ph) / 2),
            };
        }
        if (side === 'bottom' && vh - targetRect.bottom >= ph + gap + pad) {
            return {
                side: 'bottom',
                left: clampX(targetRect.left + (targetRect.width - pw) / 2),
                top: Math.round(targetRect.bottom + gap),
            };
        }
        if (side === 'top' && targetRect.top >= ph + gap + pad) {
            return {
                side: 'top',
                left: clampX(targetRect.left + (targetRect.width - pw) / 2),
                top: Math.round(targetRect.top - ph - gap),
            };
        }
    }

    // Fallback thông minh khi mục tiêu chiếm diện tích rất lớn (ví dụ màn tablet hẹp): neo góc thoáng nhất
    const spaceRight = vw - targetRect.right;
    const spaceLeft = targetRect.left;
    const fallbackLeft = spaceRight >= spaceLeft ? (vw - pw - pad) : pad;
    return {
        side: 'floating-corner',
        left: clampX(fallbackLeft),
        top: clampY(vh - ph - pad),
    };
}

function isMeasurableVisible(el, win = window) {
    if (!el || typeof el.getBoundingClientRect !== 'function') return false;
    const cs = win.getComputedStyle ? win.getComputedStyle(el) : null;
    if (cs && (cs.display === 'none' || cs.visibility === 'hidden' || Number(cs.opacity) === 0)) {
        return false;
    }
    const r = el.getBoundingClientRect();
    return r.width > 6 && r.height > 6;
}

function findStepTabBySectionIds(doc, sectionIds = []) {
    const tabs = Array.from(doc.querySelectorAll('[data-mayhen-step][data-section-id]'));
    for (const id of sectionIds) {
        const match = tabs.find((t) => t.getAttribute('data-section-id') === id);
        if (match) return match;
    }
    return null;
}

function findStepPanelTarget(doc, win, sectionIds = []) {
    for (const id of sectionIds) {
        const panel = doc.querySelector(`[data-step-panel][data-section-id="${id}"]`);
        if (!panel) continue;
        // Trên điện thoại (< 768px), khoanh gọn phần tiêu đề + cụm trường đầu tiên của bước để không xung đột với thẻ hướng dẫn phía dưới
        if ((win.innerWidth || 1280) < 768) {
            const header = panel.querySelector('.mayhen_editor_step_header');
            const firstBannerOrField = panel.querySelector('.mayhen_editor_ai_story_banner, .mayhen_client_form_group');
            if (header && firstBannerOrField) {
                return { compositeNodes: [header, firstBannerOrField], primaryNode: panel };
            }
        }
        return panel;
    }
    return null;
}

/**
 * Xây dựng danh sách bước hướng dẫn thực tế dựa trên DOM thật của mẫu thiệp & kích thước màn hình.
 */
export function buildStudioTourSteps(doc = document, win = window) {
    const isDesktopRailAvailable = () => {
        const rail = doc.querySelector('aside.mayhen_editor_left_rail');
        return (win.innerWidth || 1280) >= 768 && isMeasurableVisible(rail, win);
    };

    const isRightInspectorDocked = () => {
        const insp = doc.getElementById('mayhen_v3_inspector');
        return (win.innerWidth || 1280) >= 1101 && isMeasurableVisible(insp, win);
    };

    const ensureMobileMode = (mode) => {
        if ((win.innerWidth || 1280) >= 768) return;
        const isPreviewNow = doc.body.classList.contains('is-mobile-preview-active');
        if (mode === 'preview' && !isPreviewNow) {
            doc.querySelector('.mayhen_editor_mode_btn[data-editor-mode="preview"]')?.click();
        } else if (mode === 'form' && isPreviewNow) {
            doc.querySelector('.mayhen_editor_mode_btn[data-editor-mode="form"]')?.click();
        }
    };

    const ensureRailTab = (tabName) => {
        ensureMobileMode('form');
        const railBtn = doc.querySelector(`.mayhen_editor_rail_item[data-rail-tab="${tabName}"]`);
        if (railBtn && !railBtn.classList.contains('active')) {
            railBtn.click();
        }
    };

    const activateSectionStep = (sectionIds) => {
        ensureRailTab('content');
        const tab = findStepTabBySectionIds(doc, sectionIds);
        if (tab && !tab.classList.contains('mayhen_editor_stepper_tab_active')) {
            tab.click();
        }
        const formEl = doc.getElementById('mayhen_editor_form');
        if (formEl && typeof formEl.scrollTo === 'function') {
            formEl.scrollTo({ top: 0, behavior: 'auto' });
        }
    };

    const countRealSteps = () => doc.querySelectorAll('[data-mayhen-step]').length || 10;
    const hasSection = (sectionIds) => Boolean(findStepTabBySectionIds(doc, sectionIds));

    return [
        {
            id: 'studio-overview',
            group: 'Làm quen nhanh',
            title: 'Chào mừng bạn đến với Studio Mây Hẹn',
            getDescription: () => (win.innerWidth || 1280) < 768
                ? 'Bạn điền thông tin cưới ở tab Nhập Liệu và bấm Xem Thiệp ngay trên đầu trang để kiểm tra thiệp thật bất cứ lúc nào.'
                : 'Cột bên trái là nơi nhập thông tin theo từng mục, chính giữa là thiệp sống động cập nhật tức thì, bên phải là bảng chỉnh kiểu chữ và hiệu ứng.',
            preferredSide: 'bottom',
            isAvailable: () => Boolean(doc.querySelector('.mayhen_editor_v2_container')),
            prepare: () => {
                ensureMobileMode('form');
            },
            resolveTarget: () => {
                if ((win.innerWidth || 1280) < 768) {
                    return doc.querySelector('[data-mayhen-tour="mobile-switcher"], .mayhen_editor_mode_switcher')
                        || doc.querySelector('.mayhen_editor_topbar');
                }
                return doc.querySelector('.mayhen_editor_topbar');
            },
        },
        {
            id: 'direct-canvas-edit',
            group: 'Chỉnh sửa trực quan',
            title: 'Chạm trực tiếp lên thiệp để sửa ngay',
            getDescription: () => 'Bấm 1 lần vào chữ hoặc ảnh bất kỳ trên thiệp để đổi font, màu sắc hay hiệu ứng. Nhấp đúp để gõ tiếng Việt hoặc thay ảnh ngay tại chỗ.',
            preferredSide: 'left',
            isAvailable: () => Boolean(doc.getElementById('mayhen_editor_center_stage')),
            prepare: () => {
                ensureMobileMode('preview');
            },
            resolveTarget: () => doc.querySelector('.mayhen_editor_mockup_phone') || doc.getElementById('mayhen_editor_center_stage'),
        },
        {
            id: 'visual-inspector',
            group: 'Kiểu chữ & Hiệu ứng',
            title: 'Bộ phông chữ Tiếng Việt & chuyển động',
            getDescription: () => 'Chọn 12 phông chữ Tiếng Việt cho toàn bộ thiệp hoặc riêng từng dòng chữ, căn điểm tiêu cự ảnh không làm méo hình và chọn 10 hiệu ứng xuất hiện tinh tế.',
            preferredSide: 'left',
            isAvailable: () => isRightInspectorDocked(),
            prepare: () => {},
            resolveTarget: () => doc.getElementById('mayhen_v3_inspector'),
        },
        {
            id: 'stepper-sections',
            group: 'Các phần của thiệp',
            title: 'Điều hướng lần lượt từng phần thiệp',
            getDescription: () => {
                const total = countRealSteps();
                return `Mẫu thiệp đang mở gồm ${total} phần rõ ràng. Bạn bấm vào tên phần nào, biểu mẫu và bản xem thử sẽ tự cuộn đến đúng phần đó.`;
            },
            preferredSide: 'bottom',
            isAvailable: () => countRealSteps() >= 2 && Boolean(doc.querySelector('nav.mayhen_editor_stepper_nav')),
            prepare: () => {
                ensureRailTab('content');
            },
            resolveTarget: () => doc.querySelector('nav.mayhen_editor_stepper_nav'),
        },
        {
            id: 'step-hero-cover',
            group: 'Nội dung thiệp',
            title: 'Trang bìa, tên đôi uyên ương & ảnh chính',
            getDescription: () => 'Điền tên Chú Rể, Cô Dâu và tải ảnh bìa mở đầu. Khi chọn ảnh mới, công cụ cắt khung thông minh sẽ mở ra để bạn căn góc đẹp nhất.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['hero', 'basic']),
            prepare: () => activateSectionStep(['hero', 'basic']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['hero', 'basic']),
        },
        {
            id: 'step-story-ai',
            group: 'Nội dung thiệp',
            title: 'Lời ngỏ tình yêu & Trợ lý AI gợi ý',
            getDescription: () => 'Tải ảnh kỷ niệm và kể câu chuyện của hai bạn. Nếu cần gợi ý câu chữ, bấm nút Gợi Ý Lời Ngỏ để AI biên soạn sẵn 3 phương án trang nhã.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['story', 'story_and_timeline']),
            prepare: () => activateSectionStep(['story', 'story_and_timeline']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['story', 'story_and_timeline']),
        },
        {
            id: 'step-family',
            group: 'Nội dung thiệp',
            title: 'Thông tin hai bên gia đình & chân dung',
            getDescription: () => 'Nhập họ tên cha mẹ Nhà Trai, Nhà Gái, địa chỉ tư gia cùng ảnh chân dung riêng của Cô Dâu và Chú Rể.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['parents', 'groom_family', 'couple']),
            prepare: () => activateSectionStep(['parents', 'groom_family', 'couple']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['parents', 'groom_family', 'couple']),
        },
        {
            id: 'step-date-lunar',
            group: 'Nội dung thiệp',
            title: 'Ngày cưới tự tính Âm lịch & đếm ngược',
            getDescription: () => 'Chỉ cần chọn ngày Dương lịch, hệ thống sẽ tự động tính ngày Âm lịch tương ứng, dựng tờ lịch cưới và đồng bộ bộ đếm ngược.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['countdown_calendar']),
            prepare: () => activateSectionStep(['countdown_calendar']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['countdown_calendar']),
        },
        {
            id: 'step-events-timeline',
            group: 'Nội dung thiệp',
            title: 'Giờ tiệc, bản đồ chỉ đường & lịch trình',
            getDescription: () => 'Điền giờ đón khách, địa điểm sảnh tiệc và link Google Maps. Ở bước Lịch Trình kế tiếp, bạn chỉnh chi tiết từng mốc giờ làm lễ, khai tiệc.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['events', 'groom_events', 'timeline']),
            prepare: () => activateSectionStep(['events', 'groom_events', 'timeline']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['events', 'groom_events', 'timeline']),
        },
        {
            id: 'step-album-dresscode',
            group: 'Hình ảnh & Trang phục',
            title: 'Album ảnh cưới & màu trang phục khách mời',
            getDescription: () => hasSection(['dresscode'])
                ? 'Tải bộ 10 ảnh kỷ niệm vào các ô Album. Ngay bên cạnh là mục Trang Phục giúp bạn gợi ý tông màu dresscode đồng điệu cho khách dự tiệc.'
                : 'Tải từng bức ảnh kỷ niệm từ điện thoại hoặc máy tính vào các ô Album để hiển thị trọn vẹn bộ ảnh cưới trên thiệp.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['album', 'gallery', 'gallery_and_photos']),
            prepare: () => activateSectionStep(['album', 'gallery', 'gallery_and_photos']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['album', 'gallery', 'gallery_and_photos']),
        },
        {
            id: 'step-gift-ending',
            group: 'Mừng cưới & Lưu bút',
            title: 'Mã VietQR mừng cưới & Lời cảm ơn',
            getDescription: () => hasSection(['gift', 'banking', 'gift_and_music'])
                ? 'Chọn ngân hàng và nhập số tài khoản để tự tạo mã VietQR chuẩn xác (hãy kiểm tra kỹ STK trước khi xuất bản). Phần xác nhận tham dự & lời chúc cuối thiệp sẽ do khách mời tự điền.'
                : 'Chỉnh sửa lời tri ân cuối thiệp gửi tới quan khách. Biểu mẫu xác nhận tham dự (RSVP) và sổ lưu bút sẽ tự hoạt động khi khách mở thiệp đã xuất bản.',
            preferredSide: 'right',
            isAvailable: () => hasSection(['gift', 'banking', 'gift_and_music', 'ending']),
            prepare: () => activateSectionStep(['gift', 'banking', 'gift_and_music', 'ending']),
            resolveTarget: () => findStepPanelTarget(doc, win, ['gift', 'banking', 'gift_and_music', 'ending']),
        },
        {
            id: 'rail-layout-theme',
            group: 'Bố cục & Màu sắc',
            title: 'Sắp xếp thứ tự mục, bảng màu & dấu sáp',
            getDescription: () => 'Kéo thả đổi vị trí hoặc bấm biểu tượng con mắt để ẩn mục chưa cần. Sang thẻ Màu Sắc bên trái để đổi 6 tông màu sang trọng và khắc chữ lên dấu sáp.',
            preferredSide: 'right',
            isAvailable: () => isDesktopRailAvailable() && Boolean(doc.querySelector('.mayhen_editor_subpanel[data-panel-name="sections"]')),
            prepare: () => ensureRailTab('sections'),
            resolveTarget: () => doc.querySelector('.mayhen_editor_subpanel[data-panel-name="sections"]'),
        },
        {
            id: 'rail-decor-music',
            group: 'Trang trí & Âm nhạc',
            title: 'Kho sticker, khung ảnh & nhạc nền cưới',
            getDescription: () => 'Thêm chữ nghệ thuật, dấu sáp, sticker SVG hoặc 8 khung ảnh ngay giữa màn hình đang xem. Ở thẻ Âm Nhạc kế bên, bạn chọn giai điệu phát khi khách mở thiệp.',
            preferredSide: 'right',
            isAvailable: () => isDesktopRailAvailable() && Boolean(doc.querySelector('.mayhen_editor_subpanel[data-panel-name="decor"]')),
            prepare: () => ensureRailTab('decor'),
            resolveTarget: () => doc.querySelector('.mayhen_editor_subpanel[data-panel-name="decor"]'),
        },
        {
            id: 'topbar-publish-finish',
            group: 'Kiểm tra & Xuất bản',
            title: 'Tự lưu nháp, chấm điểm & Xuất bản thiệp',
            getDescription: () => (win.innerWidth || 1280) >= 1024
                ? 'Mọi thao tác đều tự lưu nháp, hỗ trợ Hoàn tác (Ctrl+Z), Đổi mẫu giữ nguyên chữ và chấm điểm hoàn thiện (🩺). Khi ưng ý, bấm Xuất Bản Thiệp để lấy link gửi khách mời!'
                : 'Bạn có thể Đổi mẫu bất cứ lúc nào mà không mất nội dung đã nhập. Khi kiểm tra ưng ý, bấm Xuất Bản Thiệp để nhận đường link gửi tới khách mời!',
            finaleMessage: 'Bạn đã sẵn sàng tạo thiệp của riêng mình! Cần xem lại lúc nào, hãy bấm nút "Hướng dẫn" trên thanh đầu trang.',
            preferredSide: 'bottom',
            isAvailable: () => Boolean(doc.querySelector('.mayhen_editor_topbar [data-mayhen-action="trigger-submit"]')),
            prepare: () => {
                ensureMobileMode('form');
                if (isDesktopRailAvailable()) {
                    ensureRailTab('content');
                }
                activateSectionStep(['hero', 'basic']);
            },
            resolveTarget: () => doc.querySelector('.mayhen_editor_topbar [data-mayhen-action="trigger-submit"]'),
        },
    ];
}

export class StudioProductTour {
    constructor({
        doc = document,
        win = window,
        userId = null,
        docsUrl = '/docs/studio-editor/CLI-EDIT-001',
        persistence = null,
    } = {}) {
        this.doc = doc;
        this.win = win;
        this.docsUrl = docsUrl;
        this.persistence = persistence || new TourPersistence({ userId });

        this.state = 'idle'; // 'idle' | 'preparing' | 'active' | 'paused' | 'completed' | 'skipped'
        this.activeSteps = [];
        this.currentIndex = 0;
        this.isTransitioning = false;
        this.glideUntilTs = 0;
        this.rafId = null;
        this.savedUiSnapshot = null;
        this.blockingObserver = null;
        this.resizeObserver = null;
        this.boundReposition = null;
        this.boundKeydown = null;

        this.layerEl = null;
        this.backdropEl = null;
        this.spotlightEl = null;
        this.popoverEl = null;
        this.contentStageEl = null;
        this.stepBadgeEl = null;
        this.groupTagEl = null;
        this.progressBarEl = null;
        this.titleEl = null;
        this.descEl = null;
        this.finaleBoxEl = null;
        this.skipBtn = null;
        this.prevBtn = null;
        this.nextBtn = null;
        this.closeBtn = null;
    }

    hasBlockingDialog() {
        const nodes = Array.from(this.doc.querySelectorAll(BLOCKING_DIALOG_SELECTOR));
        return nodes.some((node) => isMeasurableVisible(node, this.win));
    }

    mountTriggerButton() {
        const triggers = this.doc.querySelectorAll('[data-mayhen-tour="tour-trigger"], #mayhen_editor_tour_trigger_btn');
        triggers.forEach((btn) => {
            if (btn._mayhenTourBound) return;
            btn._mayhenTourBound = true;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.start({ manual: true });
            });
        });
    }

    scheduleAutoStartWhenReady() {
        if (!this.persistence.shouldAutoStart()) {
            return;
        }
        const searchParams = new URLSearchParams(this.win.location?.search || '');
        if (searchParams.get('tour') === '0') {
            return;
        }
        if (this.win.location?.hash && this.win.location.hash.length > 1) {
            return;
        }

        const attemptAutoStart = () => {
            if (this.state !== 'idle' || !this.persistence.shouldAutoStart()) return;
            // Nếu người dùng đã chủ động bấm gõ vào một ô nhập liệu trước đó thì tôn trọng không chen ngang
            const activeTag = this.doc.activeElement?.tagName;
            if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT') {
                return;
            }
            if (this.hasBlockingDialog()) {
                this.#watchUntilBlockingDialogCloses(attemptAutoStart);
                return;
            }
            const saved = this.persistence.read();
            this.start({
                manual: false,
                resumeStepId: saved?.status === 'in_progress' ? saved.last_step_id : null,
            });
        };

        // Khởi chạy êm ngay sau khi Studio Store, Rail & Toolbar đã mount hoàn tất trên khung vẽ đầu tiên
        const reqRaf = this.win.requestAnimationFrame?.bind(this.win) || ((cb) => globalThis.setTimeout(cb, 16));
        reqRaf(() => {
            globalThis.setTimeout(attemptAutoStart, 160);
        });
    }

    start({ manual = false, resumeStepId = null } = {}) {
        if (this.isTransitioning) return false;
        if (!manual && !this.persistence.shouldAutoStart()) return false;
        if (!manual && this.hasBlockingDialog()) return false;

        const candidates = buildStudioTourSteps(this.doc, this.win);
        const available = candidates.filter((step) => {
            try {
                return Boolean(step.isAvailable());
            } catch (err) {
                return false;
            }
        });

        if (available.length === 0) {
            return false;
        }

        if (this.state !== 'active' && this.state !== 'paused') {
            this.#capturePreTourUiSnapshot();
        }

        this.activeSteps = available;
        let startIdx = 0;
        if (!manual && resumeStepId) {
            const foundIdx = available.findIndex((s) => s.id === resumeStepId);
            if (foundIdx >= 0) startIdx = foundIdx;
        }

        this.#ensureDomLayer();
        this.#bindGlobalListeners();
        this.state = 'active';
        this.layerEl.hidden = false;
        this.layerEl.classList.remove('is-paused', 'is-closing');
        this.layerEl.classList.add('is-entering');
        this.popoverEl.classList.remove('is-opening');
        void this.popoverEl.offsetWidth;
        this.popoverEl.classList.add('is-opening');

        this.#goToIndex(startIdx, { focusPopover: true, direction: 0, isInitialOpen: true });

        const reqRaf = this.win.requestAnimationFrame?.bind(this.win) || ((cb) => globalThis.setTimeout(cb, 16));
        reqRaf(() => {
            this.layerEl?.classList.remove('is-entering');
        });
        return true;
    }

    next() {
        if (this.state !== 'active' || this.isTransitioning) return;
        if (this.currentIndex >= this.activeSteps.length - 1) {
            this.finish();
            return;
        }
        this.#goToIndex(this.currentIndex + 1, { focusPopover: false, direction: 1, isInitialOpen: false });
    }

    previous() {
        if (this.state !== 'active' || this.isTransitioning) return;
        if (this.currentIndex <= 0) return;
        this.#goToIndex(this.currentIndex - 1, { focusPopover: false, direction: -1, isInitialOpen: false });
    }

    skip() {
        if (this.state !== 'active' && this.state !== 'paused') return;
        const currentStep = this.activeSteps[this.currentIndex];
        this.persistence.markSkipped(currentStep?.id || null);
        this.state = 'skipped';
        this.#teardownSession({ restoreUi: true });
    }

    finish() {
        if (this.state !== 'active' && this.state !== 'paused') return;
        this.persistence.markCompleted();
        this.state = 'completed';
        this.#teardownSession({ restoreUi: true });
    }

    destroy() {
        this.#teardownSession({ restoreUi: false });
        if (this.blockingObserver) {
            this.blockingObserver.disconnect();
            this.blockingObserver = null;
        }
        if (this.layerEl && this.layerEl.parentNode) {
            this.layerEl.parentNode.removeChild(this.layerEl);
        }
        this.layerEl = null;
        this.state = 'idle';
    }

    #capturePreTourUiSnapshot() {
        const activeRail = this.doc.querySelector('.mayhen_editor_rail_item.active')?.getAttribute('data-rail-tab') || 'content';
        const activeStep = Number(this.doc.querySelector('.mayhen_editor_stepper_tab_active')?.getAttribute('data-mayhen-step')) || 1;
        const isMobilePreview = this.doc.body.classList.contains('is-mobile-preview-active');
        const formEl = this.doc.getElementById('mayhen_editor_form');
        const drawerEl = this.doc.getElementById('mayhen_drawer_panel');

        this.savedUiSnapshot = {
            activeRail,
            activeStep,
            isMobilePreview,
            formScrollTop: formEl ? formEl.scrollTop : 0,
            drawerScrollTop: drawerEl ? drawerEl.scrollTop : 0,
            previousFocus: this.doc.activeElement,
        };
    }

    #restorePreTourUiSnapshot() {
        const snap = this.savedUiSnapshot;
        this.savedUiSnapshot = null;
        if (!snap) return;

        try {
            if ((this.win.innerWidth || 1280) < 768) {
                const targetMode = snap.isMobilePreview ? 'preview' : 'form';
                this.doc.querySelector(`.mayhen_editor_mode_btn[data-editor-mode="${targetMode}"]`)?.click();
            } else if (snap.activeRail) {
                const railBtn = this.doc.querySelector(`.mayhen_editor_rail_item[data-rail-tab="${snap.activeRail}"]`);
                if (railBtn && !railBtn.classList.contains('active')) {
                    railBtn.click();
                }
            }

            const stepBtn = this.doc.querySelector(`[data-mayhen-step="${snap.activeStep}"]`);
            if (stepBtn && !stepBtn.classList.contains('mayhen_editor_stepper_tab_active')) {
                stepBtn.click();
            }

            const formEl = this.doc.getElementById('mayhen_editor_form');
            if (formEl) formEl.scrollTop = snap.formScrollTop || 0;
            const drawerEl = this.doc.getElementById('mayhen_drawer_panel');
            if (drawerEl) drawerEl.scrollTop = snap.drawerScrollTop || 0;

            const focusCandidate = (snap.previousFocus && snap.previousFocus.isConnected && typeof snap.previousFocus.focus === 'function')
                ? snap.previousFocus
                : this.doc.getElementById('mayhen_editor_tour_trigger_btn');
            focusCandidate?.focus?.({ preventScroll: true });
        } catch (err) {}
    }

    #ensureDomLayer() {
        if (this.layerEl && this.layerEl.isConnected) return;

        const layer = this.doc.createElement('div');
        layer.className = 'mayhen_editor_tour_layer';
        layer.id = 'mayhen_editor_tour_layer';
        layer.hidden = true;

        const backdrop = this.doc.createElement('div');
        backdrop.className = 'mayhen_editor_tour_backdrop';
        backdrop.setAttribute('aria-hidden', 'true');

        const spotlight = this.doc.createElement('div');
        spotlight.className = 'mayhen_editor_tour_spotlight';
        spotlight.setAttribute('aria-hidden', 'true');

        const popover = this.doc.createElement('section');
        popover.className = 'mayhen_editor_tour_popover';
        popover.id = 'mayhen_editor_tour_popover';
        popover.setAttribute('role', 'dialog');
        popover.setAttribute('aria-modal', 'false');
        popover.setAttribute('aria-labelledby', 'mayhen_editor_tour_title');
        popover.setAttribute('aria-describedby', 'mayhen_editor_tour_desc');
        popover.tabIndex = -1;

        // Header
        const head = this.doc.createElement('div');
        head.className = 'mayhen_editor_tour_head';

        const meta = this.doc.createElement('div');
        meta.className = 'mayhen_editor_tour_meta';

        const stepBadge = this.doc.createElement('span');
        stepBadge.className = 'mayhen_editor_tour_step_badge';
        stepBadge.setAttribute('aria-live', 'polite');

        const groupTag = this.doc.createElement('span');
        groupTag.className = 'mayhen_editor_tour_group_tag';

        meta.appendChild(stepBadge);
        meta.appendChild(groupTag);

        const closeBtn = this.doc.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'mayhen_editor_tour_close_btn';
        closeBtn.setAttribute('aria-label', 'Bỏ qua hướng dẫn');
        closeBtn.title = 'Bỏ qua hướng dẫn (Esc)';
        closeBtn.textContent = '✕';
        closeBtn.addEventListener('click', () => this.skip());

        head.appendChild(meta);
        head.appendChild(closeBtn);

        // Progress Track
        const progressTrack = this.doc.createElement('div');
        progressTrack.className = 'mayhen_editor_tour_progress_track';
        progressTrack.setAttribute('aria-hidden', 'true');

        const progressBar = this.doc.createElement('div');
        progressBar.className = 'mayhen_editor_tour_progress_bar';
        progressTrack.appendChild(progressBar);

        // Content
        const title = this.doc.createElement('h2');
        title.className = 'mayhen_editor_tour_title';
        title.id = 'mayhen_editor_tour_title';

        const desc = this.doc.createElement('p');
        desc.className = 'mayhen_editor_tour_desc';
        desc.id = 'mayhen_editor_tour_desc';

        const finaleBox = this.doc.createElement('div');
        finaleBox.className = 'mayhen_editor_tour_finale_box';
        finaleBox.hidden = true;

        // Footer
        const footer = this.doc.createElement('div');
        footer.className = 'mayhen_editor_tour_footer';

        const skipBtn = this.doc.createElement('button');
        skipBtn.type = 'button';
        skipBtn.className = 'mayhen_editor_tour_btn mayhen_editor_tour_btn_skip';
        skipBtn.setAttribute('data-tour-action', 'skip');
        skipBtn.textContent = 'Bỏ qua';
        skipBtn.addEventListener('click', () => this.skip());

        const navGroup = this.doc.createElement('div');
        navGroup.className = 'mayhen_editor_tour_nav_group';

        const prevBtn = this.doc.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'mayhen_editor_tour_btn mayhen_editor_tour_btn_prev';
        prevBtn.setAttribute('data-tour-action', 'prev');
        prevBtn.textContent = '← Quay lại';
        prevBtn.addEventListener('click', () => this.previous());

        const nextBtn = this.doc.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'mayhen_editor_tour_btn mayhen_editor_tour_btn_next';
        nextBtn.setAttribute('data-tour-action', 'next');
        nextBtn.textContent = 'Tiếp theo →';
        nextBtn.addEventListener('click', () => this.next());

        navGroup.appendChild(prevBtn);
        navGroup.appendChild(nextBtn);
        footer.appendChild(skipBtn);
        footer.appendChild(navGroup);

        const contentStage = this.doc.createElement('div');
        contentStage.className = 'mayhen_editor_tour_content_stage';
        contentStage.appendChild(title);
        contentStage.appendChild(desc);
        contentStage.appendChild(finaleBox);

        popover.appendChild(head);
        popover.appendChild(progressTrack);
        popover.appendChild(contentStage);
        popover.appendChild(footer);

        layer.appendChild(backdrop);
        layer.appendChild(spotlight);
        layer.appendChild(popover);
        this.doc.body.appendChild(layer);

        this.layerEl = layer;
        this.backdropEl = backdrop;
        this.spotlightEl = spotlight;
        this.popoverEl = popover;
        this.contentStageEl = contentStage;
        this.stepBadgeEl = stepBadge;
        this.groupTagEl = groupTag;
        this.progressBarEl = progressBar;
        this.titleEl = title;
        this.descEl = desc;
        this.finaleBoxEl = finaleBox;
        this.skipBtn = skipBtn;
        this.prevBtn = prevBtn;
        this.nextBtn = nextBtn;
        this.closeBtn = closeBtn;
    }

    #goToIndex(nextIndex, { focusPopover = false, direction = 1, isInitialOpen = false } = {}) {
        if (nextIndex < 0 || nextIndex >= this.activeSteps.length) return;
        this.isTransitioning = true;
        this.currentIndex = nextIndex;

        const step = this.activeSteps[nextIndex];
        try {
            step.prepare?.();
        } catch (err) {}

        // Ghi nhận bước đang xem dở (chỉ khi chưa hoàn thành/bỏ qua trước đó)
        if (this.persistence.shouldAutoStart()) {
            this.persistence.markInProgress(step.id);
        }

        const total = this.activeSteps.length;
        const isFirst = nextIndex === 0;
        const isLast = nextIndex === total - 1;
        const pct = Math.round(((nextIndex + 1) / total) * 100);

        this.stepBadgeEl.textContent = `Bước ${nextIndex + 1} / ${total}`;
        this.groupTagEl.textContent = step.group || '';
        this.progressBarEl.style.width = `${pct}%`;
        this.titleEl.textContent = step.title;
        this.descEl.textContent = typeof step.getDescription === 'function' ? step.getDescription() : (step.description || '');

        // Finale box on final step
        this.finaleBoxEl.textContent = '';
        if (isLast && step.finaleMessage) {
            this.finaleBoxEl.hidden = false;
            const noteText = this.doc.createElement('div');
            noteText.textContent = step.finaleMessage;
            this.finaleBoxEl.appendChild(noteText);

            if (this.docsUrl) {
                const docsLink = this.doc.createElement('a');
                docsLink.href = this.docsUrl;
                docsLink.target = '_blank';
                docsLink.rel = 'noopener noreferrer';
                docsLink.className = 'mayhen_editor_tour_docs_link';
                docsLink.textContent = 'Mở Cẩm nang Studio chi tiết ↗';
                this.finaleBoxEl.appendChild(docsLink);
            }
        } else {
            this.finaleBoxEl.hidden = true;
        }

        this.prevBtn.hidden = isFirst;
        this.prevBtn.disabled = isFirst;
        this.skipBtn.hidden = isLast;
        this.nextBtn.textContent = isLast ? 'Bắt đầu tạo thiệp ✓' : 'Tiếp theo →';
        this.nextBtn.classList.toggle('is-finale', isLast);

        const resolvedTarget = this.#resolveStepTargetObject(step);
        this.#scrollTargetSafely(resolvedTarget?.primaryNode || resolvedTarget?.element || null);

        if (isInitialOpen) {
            // Bước mở đầu: đặt ngay vào đúng tọa độ trước rồi fade-in êm (tránh bay từ góc 0,0)
            this.glideUntilTs = 0;
            this.spotlightEl.classList.add('is-tracking');
            this.popoverEl.classList.add('is-tracking');
            this.updatePosition(true);
            void this.spotlightEl.offsetWidth;
            this.spotlightEl.classList.remove('is-tracking');
            this.popoverEl.classList.remove('is-tracking');
        } else {
            // Chuyển giữa các bước: kích hoạt lướt mượt Spotlight + Popover + hiệu ứng chữ theo hướng Tiến/Lùi
            this.glideUntilTs = Date.now() + 460;
            this.popoverEl.classList.remove('is-opening', 'is-tracking');
            this.spotlightEl.classList.remove('is-tracking', 'is-step-arriving');
            if (this.contentStageEl) {
                this.contentStageEl.classList.remove('is-anim-next', 'is-anim-prev');
                void this.contentStageEl.offsetWidth;
                this.contentStageEl.classList.add(direction < 0 ? 'is-anim-prev' : 'is-anim-next');
            }
            void this.spotlightEl.offsetWidth;
            this.spotlightEl.classList.add('is-step-arriving');
            this.updatePosition(false);
        }

        globalThis.setTimeout(() => {
            this.updatePosition(false);
            this.isTransitioning = false;
            if (focusPopover) {
                this.nextBtn?.focus?.({ preventScroll: true });
            }
        }, 70);
    }

    #resolveStepTargetObject(step) {
        if (!step || typeof step.resolveTarget !== 'function') return null;
        try {
            const raw = step.resolveTarget();
            if (!raw) return null;
            if (raw.compositeNodes && Array.isArray(raw.compositeNodes)) {
                const validNodes = raw.compositeNodes.filter((n) => isMeasurableVisible(n, this.win));
                if (validNodes.length > 0) {
                    return { compositeNodes: validNodes, primaryNode: raw.primaryNode || validNodes[0] };
                }
            }
            if (isMeasurableVisible(raw, this.win)) {
                return { element: raw, primaryNode: raw };
            }
        } catch (err) {}
        return null;
    }

    #measureTargetBounds(targetObj) {
        if (!targetObj) return null;
        const vw = this.win.visualViewport?.width || this.win.innerWidth || 1280;
        const vh = this.win.visualViewport?.height || this.win.innerHeight || 800;

        let rawRect;
        let sampleNode = targetObj.primaryNode || targetObj.element;

        if (targetObj.compositeNodes) {
            const rects = targetObj.compositeNodes.map((n) => n.getBoundingClientRect());
            const left = Math.min(...rects.map((r) => r.left));
            const top = Math.min(...rects.map((r) => r.top));
            const right = Math.max(...rects.map((r) => r.right));
            const bottom = Math.max(...rects.map((r) => r.bottom));
            rawRect = { left, top, right, bottom, width: right - left, height: bottom - top };
            sampleNode = targetObj.compositeNodes[0];
        } else if (targetObj.element) {
            rawRect = targetObj.element.getBoundingClientRect();
        } else {
            return null;
        }

        const pad = 5;
        const left = Math.max(4, rawRect.left - pad);
        const top = Math.max(4, rawRect.top - pad);
        const right = Math.min(vw - 4, rawRect.right + pad);
        const bottom = Math.min(vh - 4, rawRect.bottom + pad);
        const width = Math.max(0, right - left);
        const height = Math.max(0, bottom - top);

        if (width < 8 || height < 8) return null;

        let radius = 12;
        if (sampleNode && this.win.getComputedStyle) {
            const parsedRadius = parseFloat(this.win.getComputedStyle(sampleNode).borderRadius);
            if (Number.isFinite(parsedRadius)) {
                radius = Math.max(8, Math.min(24, Math.round(parsedRadius + 3)));
            }
        }

        return { left, top, right, bottom, width, height, radius };
    }

    #scrollTargetSafely(el) {
        if (!el || typeof el.getBoundingClientRect !== 'function') return;
        try {
            // Nếu là thanh stepper ngang, đảm bảo bước đang chọn nằm gọn trong tầm mắt
        if (el.matches?.('nav.mayhen_editor_stepper_nav')) {
                const activePill = el.querySelector('.mayhen_editor_stepper_tab_active');
                activePill?.scrollIntoView?.({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                return;
            }

            const scroller = el.closest?.('#mayhen_editor_form, .mayhen_editor_subpanel, .mayhen_editor_drawer_panel');
            if (scroller && scroller !== el) {
                const sRect = scroller.getBoundingClientRect();
                const eRect = el.getBoundingClientRect();
                if (eRect.top < sRect.top + 8 || eRect.top > sRect.bottom - 60) {
                    const targetTop = Math.max(0, scroller.scrollTop + (eRect.top - sRect.top - 14));
                    if (typeof scroller.scrollTo === 'function') {
                        scroller.scrollTo({ top: targetTop, behavior: 'smooth' });
                    } else {
                        scroller.scrollTop = targetTop;
                    }
                }
            }
        } catch (err) {}
    }

    updatePosition(isLiveTracking = true) {
        if (!this.layerEl || this.state !== 'active') return;

        // Tạm ẩn lớp tour nếu hộp thoại hệ thống (Cắt ảnh, Kho nhạc, Đổi mẫu, Khôi phục nháp) mở lên
        if (this.hasBlockingDialog()) {
            this.layerEl.classList.add('is-paused');
            return;
        }
        this.layerEl.classList.remove('is-paused');

        const step = this.activeSteps[this.currentIndex];
        if (!step) return;

        const targetObj = this.#resolveStepTargetObject(step);
        const bounds = this.#measureTargetBounds(targetObj);

        const isGlidingBetweenSteps = Date.now() < this.glideUntilTs;
        if (isLiveTracking && !isGlidingBetweenSteps) {
            this.spotlightEl.classList.add('is-tracking');
            this.popoverEl.classList.add('is-tracking');
        } else {
            this.spotlightEl.classList.remove('is-tracking');
            this.popoverEl.classList.remove('is-tracking');
        }

        if (bounds) {
            this.spotlightEl.classList.remove('is-fallback-backdrop');
            this.spotlightEl.style.left = `${Math.round(bounds.left)}px`;
            this.spotlightEl.style.top = `${Math.round(bounds.top)}px`;
            this.spotlightEl.style.width = `${Math.round(bounds.width)}px`;
            this.spotlightEl.style.height = `${Math.round(bounds.height)}px`;
            this.spotlightEl.style.setProperty('--mh-tour-radius', `${bounds.radius}px`);
        } else {
            this.spotlightEl.classList.add('is-fallback-backdrop');
        }

        const vw = this.win.visualViewport?.width || this.win.innerWidth || 1280;
        const vh = this.win.visualViewport?.height || this.win.innerHeight || 800;
        const popRect = this.popoverEl.getBoundingClientRect();

        const placement = computePopoverPlacement({
            targetRect: bounds,
            popoverWidth: popRect.width || 356,
            popoverHeight: popRect.height || 210,
            viewportWidth: vw,
            viewportHeight: vh,
            preferredSide: step.preferredSide || 'right',
        });

        this.popoverEl.style.left = `${placement.left}px`;
        this.popoverEl.style.top = `${placement.top}px`;
        this.popoverEl.setAttribute('data-placement', placement.side);
    }

    #scheduleReposition() {
        if (this.rafId !== null) return;
        const reqRaf = this.win.requestAnimationFrame?.bind(this.win) || ((cb) => setTimeout(cb, 16));
        this.rafId = reqRaf(() => {
            this.rafId = null;
            this.updatePosition(true);
        });
    }

    #bindGlobalListeners() {
        if (this.boundReposition) return;

        this.boundReposition = () => this.#scheduleReposition();
        this.boundKeydown = (e) => {
            if (this.state !== 'active' || this.layerEl?.classList.contains('is-paused')) return;
            const activeTag = this.doc.activeElement?.tagName;
            const isTypingField = activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT' || this.doc.activeElement?.isContentEditable;

            if (e.key === 'Escape') {
                if (isTypingField) return;
                e.preventDefault();
                e.stopPropagation();
                this.skip();
                return;
            }

            if (e.key === 'Tab' && this.popoverEl) {
                const focusables = Array.from(
                    this.popoverEl.querySelectorAll('button:not([hidden]):not(:disabled), a[href]:not([hidden])')
                );
                if (focusables.length > 0) {
                    const first = focusables[0];
                    const last = focusables[focusables.length - 1];
                    if (e.shiftKey && (this.doc.activeElement === first || !this.popoverEl.contains(this.doc.activeElement))) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && (this.doc.activeElement === last || !this.popoverEl.contains(this.doc.activeElement))) {
                        e.preventDefault();
                        first.focus();
                    }
                }
                return;
            }

            if (!isTypingField && e.key === 'ArrowRight') {
                e.preventDefault();
                this.next();
            } else if (!isTypingField && e.key === 'ArrowLeft') {
                e.preventDefault();
                this.previous();
            }
        };

        this.win.addEventListener('resize', this.boundReposition, { passive: true });
        this.doc.addEventListener('scroll', this.boundReposition, { capture: true, passive: true });
        this.doc.addEventListener('keydown', this.boundKeydown, true);
        if (this.win.visualViewport) {
            this.win.visualViewport.addEventListener('resize', this.boundReposition, { passive: true });
            this.win.visualViewport.addEventListener('scroll', this.boundReposition, { passive: true });
        }

        if (typeof this.win.MutationObserver === 'function') {
            this.resizeObserver = new this.win.MutationObserver(() => this.#scheduleReposition());
            this.resizeObserver.observe(this.doc.body, {
                attributes: true,
                subtree: true,
                attributeFilter: ['class', 'hidden', 'style'],
            });
        }
    }

    #unbindGlobalListeners() {
        if (this.rafId !== null) {
            const cancelRaf = this.win.cancelAnimationFrame?.bind(this.win) || clearTimeout;
            cancelRaf(this.rafId);
            this.rafId = null;
        }
        if (this.boundReposition) {
            this.win.removeEventListener('resize', this.boundReposition);
            this.doc.removeEventListener('scroll', this.boundReposition, { capture: true });
            if (this.win.visualViewport) {
                this.win.visualViewport.removeEventListener('resize', this.boundReposition);
                this.win.visualViewport.removeEventListener('scroll', this.boundReposition);
            }
            this.boundReposition = null;
        }
        if (this.boundKeydown) {
            this.doc.removeEventListener('keydown', this.boundKeydown, true);
            this.boundKeydown = null;
        }
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
            this.resizeObserver = null;
        }
    }

    #watchUntilBlockingDialogCloses(onUnblocked) {
        if (this.blockingObserver || typeof this.win.MutationObserver !== 'function') return;
        this.blockingObserver = new this.win.MutationObserver(() => {
            if (!this.hasBlockingDialog()) {
                this.blockingObserver?.disconnect();
                this.blockingObserver = null;
                globalThis.setTimeout(onUnblocked, 350);
            }
        });
        this.blockingObserver.observe(this.doc.body, {
            attributes: true,
            subtree: true,
            attributeFilter: ['class', 'hidden', 'style'],
        });
    }

    #teardownSession({ restoreUi = false } = {}) {
        this.#unbindGlobalListeners();
        this.isTransitioning = false;
        if (this.layerEl) {
            this.layerEl.hidden = true;
            this.layerEl.classList.remove('is-paused');
        }
        if (restoreUi) {
            this.#restorePreTourUiSnapshot();
        }
    }
}

export function initStudioProductTour(options = {}) {
    const doc = options.doc || document;
    const win = options.win || window;

    // Phạm vi tuyệt đối: CHỈ khởi tạo trên giao diện Studio `/tao-thiep`
    if (!doc.querySelector('.mayhen_editor_v2_container')) {
        return null;
    }

    const tour = new StudioProductTour({
        doc,
        win,
        userId: options.userId ?? win.__MAYHEN_AUTH_USER_ID ?? null,
        docsUrl: options.docsUrl ?? win.__MAYHEN_STUDIO_DOCS_URL ?? '/docs/studio-editor/CLI-EDIT-001',
    });

    tour.mountTriggerButton();
    if (options.autoStart !== false) {
        tour.scheduleAutoStartWhenReady();
    }

    win.__MAYHEN_STUDIO_TOUR = tour;
    return tour;
}
