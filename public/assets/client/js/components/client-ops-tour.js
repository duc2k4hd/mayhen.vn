/**
 * MÂY HẸN — CLIENT OPERATIONS INTERACTIVE PRODUCT TOUR
 * Scope:
 *   - Tour A (account_overview): /tai-khoan
 *   - Tour B (guest_crm): /tai-khoan/thiep/{id}/khach-moi
 *
 * Kiến trúc: 100% Pure Vanilla JavaScript (ES Modules), Zero external libraries.
 * Tách biệt hoàn toàn khỏi tour Studio (/tao-thiep): namespace riêng, CSS prefix
 * riêng (.mayhen_client_tour_*), nút mở riêng.
 * Cam kết an toàn dữ liệu tuyệt đối: Tour chỉ đọc vị trí phần tử và điều khiển
 * spotlight hướng dẫn; tuyệt đối không gửi form, không thêm/sửa/xóa khách,
 * không đổi trạng thái RSVP, không tự sao chép hay mở Zalo.
 */

export const CLIENT_OPS_TOUR_NAMESPACE = 'mayhen:client-ops-tour';
export const CLIENT_OPS_TOUR_VERSION = 1;

const BLOCKING_MODAL_SELECTOR = [
    '.mayhen_account_modal.is-open',
    '.mayhen_guest_modal.is-open',
    '#mayhen_qr_modal.is-open',
    '#mayhen_readiness_modal.is-open',
    '#mayhen_renewal_modal[style*="flex"]',
    '#mayhen_ceremony_fork_modal[style*="flex"]',
].join(', ');

export function normalizeTourId(rawId) {
    const clean = String(rawId || '').trim().toLowerCase().replace(/-/g, '_');
    if (clean === 'account_overview') return 'account_overview';
    if (clean === 'guest_crm') return 'guest_crm';
    return null;
}

export function resolveClientOpsTourKey(tourId, userId = null) {
    const normalizedTour = normalizeTourId(tourId) || 'account_overview';
    const cleanUser = userId !== null && userId !== undefined && String(userId).trim() !== '' && String(userId) !== 'null'
        ? `u_${String(userId).replace(/[^a-zA-Z0-9_-]/g, '')}`
        : 'u_guest';
    return `${CLIENT_OPS_TOUR_NAMESPACE}:v${CLIENT_OPS_TOUR_VERSION}:${normalizedTour}:${cleanUser}`;
}

export class ClientOpsTourPersistence {
    constructor({
        tourId,
        userId = null,
        localStore = globalThis.localStorage,
        sessionStore = globalThis.sessionStorage,
    } = {}) {
        this.tourId = normalizeTourId(tourId) || 'account_overview';
        this.key = resolveClientOpsTourKey(this.tourId, userId);
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
            if (!parsed || typeof parsed !== 'object' || parsed.version !== CLIENT_OPS_TOUR_VERSION) {
                return null;
            }
            if (!['in_progress', 'skipped', 'completed'].includes(parsed.status)) {
                return null;
            }
            return {
                version: CLIENT_OPS_TOUR_VERSION,
                status: parsed.status,
                last_step_id: typeof parsed.last_step_id === 'string' ? parsed.last_step_id : null,
                updated_at: typeof parsed.updated_at === 'string' ? parsed.updated_at : null,
            };
        } catch {
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
            version: CLIENT_OPS_TOUR_VERSION,
            status: 'in_progress',
            last_step_id: stepId ? String(stepId) : null,
            updated_at: new Date().toISOString(),
        });
    }

    markSkipped(stepId = null) {
        this.#write({
            version: CLIENT_OPS_TOUR_VERSION,
            status: 'skipped',
            last_step_id: stepId ? String(stepId) : null,
            updated_at: new Date().toISOString(),
        });
    }

    markCompleted() {
        this.#write({
            version: CLIENT_OPS_TOUR_VERSION,
            status: 'completed',
            last_step_id: null,
            updated_at: new Date().toISOString(),
        });
    }

    #safeGet(storage) {
        if (!storage || typeof storage.getItem !== 'function') return null;
        try {
            return storage.getItem(this.key);
        } catch {
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
            } catch {
                savedLocal = false;
            }
        }
        if (!savedLocal && this.sessionStore && typeof this.sessionStore.setItem === 'function') {
            try {
                this.sessionStore.setItem(this.key, serialized);
            } catch {
                // Memory fallback remains active
            }
        }
    }
}

export const TOUR_DEFINITIONS = {
    account_overview: {
        id: 'account_overview',
        label: 'Hướng dẫn Tài khoản',
        triggerSelector: '#mayhen_account_tour_trigger_btn, [data-ops-tour-trigger="account_overview"]',
        steps: [
            {
                id: 'account-header',
                selector: '[data-ops-tour-anchor="account-header"]',
                title: 'Không gian quản lý của hai bạn',
                description: 'Đây là trung tâm quản lý toàn bộ lễ cưới trên Mây Hẹn. Khi cần tách riêng thiệp Nhà Trai và Nhà Gái, bạn có thể bấm nút "Tạo Thiệp Cưới Mới" ngay tại đây.',
                tip: 'Mỗi thiệp có thể dùng chung hoặc tách riêng danh sách khách mời tùy nhu cầu hai bên gia đình.',
            },
            {
                id: 'account-tabs',
                selector: '[data-ops-tour-anchor="account-tabs"]',
                title: '5 phân khu quản lý rõ ràng',
                description: 'Thanh điều hướng giúp bạn chuyển nhanh giữa Tổng Quan, danh sách Thiệp Cưới Của Tôi, lịch sử Đơn Hàng & Gói Cước, Hồ Sơ & Bảo Mật và Hỗ Trợ.',
                tip: 'Mọi công cụ sâu hơn của từng đám cưới đều nằm gọn trong mục "Thiệp Cưới Của Tôi".',
            },
            {
                id: 'account-kpis',
                selector: '[data-ops-tour-anchor="account-kpis"]',
                title: '4 chỉ số cốt lõi minh bạch',
                description: 'Mây Hẹn tách rõ "Suất Dự Kiến" (tổng số chỗ dự định mời kèm số lời mời đã tạo) và "Người Xác Nhận Đến" (tổng người lớn + trẻ em đã báo tham dự) để hai bạn đặt bàn chính xác.',
                tip: 'Số liệu tự động đồng bộ từ các thiệp đang hoạt động trong tài khoản của bạn.',
            },
            {
                id: 'account-active-wedding',
                selector: '[data-ops-tour-anchor="account-active-wedding"]',
                title: 'Thiệp cưới đang chuẩn bị',
                description: 'Tại khung này, bạn có thể sao chép đường link thiệp chung, vào Studio chỉnh sửa thiết kế, xem thiệp trực tiếp, tạo mã QR in thiệp giấy hoặc kiểm tra trạng thái xuất bản.',
                tip: 'Nếu bạn chưa có thiệp nào, hãy bấm "Khám Phá Kho Mẫu Thiệp" để khởi tạo mẫu đầu tiên.',
            },
            {
                id: 'account-quick-actions',
                selector: '[data-ops-tour-anchor="account-quick-actions"]',
                title: 'Lối tắt vận hành trước, trong và sau lễ cưới',
                description: 'Truy cập ngay 6 tác vụ quan trọng nhất: Quản lý Khách Mời & RSVP, Sơ Đồ Xếp Bàn Tiệc, Màn Chiếu LED & Điều Phối sảnh cưới, Két Kỷ Niệm Số và nâng cấp gói thiệp.',
                tip: 'Quy trình thuận tiện nhất: Hoàn thiện thiệp → Lập khách mời & gửi link riêng → Xếp bàn → Đón khách QR.',
            },
            {
                id: 'account-wishes',
                selector: '[data-ops-tour-anchor="account-wishes"]',
                title: 'Sổ lưu bút & lời chúc mới nhất',
                description: 'Những lời chúc phúc bạn bè và người thân gửi qua thiệp sẽ hiển thị tại đây. Bạn có thể bấm "Quản Lý Sổ Lưu Bút" để duyệt hiển thị hoặc đọc riêng các lời chúc tâm sự kín.',
                tip: 'Các lời chúc khách đánh dấu riêng tư sẽ chỉ hiển thị cho cô dâu chú rể, không bao giờ lộ công khai.',
            },
            {
                id: 'account-replay',
                selector: '#mayhen_account_tour_trigger_btn',
                title: 'Xem lại hướng dẫn bất cứ khi nào cần',
                description: 'Sau khi hoàn tất hoặc bỏ qua, hướng dẫn sẽ không tự hiện lại để tránh làm phiền. Bạn luôn có thể nhấn nút "Hướng dẫn" này để xem lại từ đầu.',
                tip: 'Chúc hai bạn có hành trình chuẩn bị hôn lễ thật nhẹ nhàng và trọn vẹn!',
            },
        ],
    },
    guest_crm: {
        id: 'guest_crm',
        label: 'Hướng dẫn Khách mời & RSVP',
        triggerSelector: '#mayhen_guest_tour_trigger_btn, [data-ops-tour-trigger="guest_crm"]',
        steps: [
            {
                id: 'guest-header',
                selector: '[data-ops-tour-anchor="guest-header"]',
                title: 'Sổ Quản lý Khách mời & Phản hồi RSVP',
                description: 'Đây là nơi hai bạn lập danh sách khách mời, tạo đường link thiệp mang tên riêng từng khách và theo dõi chính xác số người tham dự cho đám cưới.',
                tip: 'Mọi thay đổi tại đây đều đồng bộ trực tiếp với Sơ đồ bàn tiệc, Báo bếp và Cổng đón khách QR.',
            },
            {
                id: 'guest-kpis',
                selector: '[data-ops-tour-anchor="guest-kpi-grid"]',
                title: 'Phân biệt rõ 6 con số quan trọng',
                description: 'Mây Hẹn không gộp chung gây nhầm lẫn: "Số Lời Mời" là số hộ/nhóm khách; "Suất Dự Kiến" là số ghế dự trù ban đầu; "Người Xác Nhận Đến" tính đúng tổng người lớn + trẻ em báo tham dự; và "Đã Điểm Danh" là số khách thực tế đã tới sảnh.',
                tip: 'Khi đặt bàn nhà hàng, hãy đối chiếu giữa "Suất Dự Kiến" và "Người Xác Nhận Đến".',
            },
            {
                id: 'guest-add-box',
                selector: '[data-ops-tour-anchor="guest-add-box"]',
                title: '3 cách thêm khách mời linh hoạt',
                description: 'Bạn có thể thêm lẻ từng khách kèm cách xưng hô ("Kính mời", "Thân mời"), chuyển sang tab "Nhập Nhanh Nhiều Khách" để dán nhiều dòng cùng lúc, hoặc tải tệp CSV mẫu chuẩn tiếng Việt để nhập từ Excel.',
                tip: 'Cột "Suất tối đa" giúp bạn giới hạn lịch sự số người đi kèm trên từng link mời cá nhân.',
            },
            {
                id: 'guest-filter-toolbar',
                selector: '[data-ops-tour-anchor="guest-filter-toolbar"]',
                title: 'Tìm kiếm & lọc khách theo tình trạng thực tế',
                description: 'Dùng thanh lọc để xem riêng khách Họ Nhà Trai / Nhà Gái, lọc theo nhóm, theo bàn tiệc, hoặc lọc nhanh những khách "Chưa mở thiệp" và "Chưa gửi phản hồi" để nhắc hẹn kịp thời.',
                tip: 'Các nút lọc nhanh phía trên giúp chuyển danh sách chỉ với một chạm trên điện thoại.',
            },
            {
                id: 'guest-table',
                selector: '[data-ops-tour-anchor="guest-table"]',
                title: 'Link mời cá nhân hóa & cập nhật RSVP hộ khách',
                description: 'Trên từng dòng khách, bạn có thể chép lời mời kèm link riêng để dán gửi qua Zalo/Messenger, bấm đánh dấu "Đã gửi" để không gửi sót, hoặc nhập RSVP thủ công hộ các cô chú lớn tuổi gọi điện báo.',
                tip: 'Lưu ý: Mây Hẹn hỗ trợ soạn sẵn nội dung & mở Zalo nhanh; bạn chủ động nhấn gửi trong Zalo để lời mời giữ trọn sự trân trọng.',
            },
            {
                id: 'guest-primary-actions',
                selector: '[data-ops-tour-anchor="guest-primary-actions"]',
                title: 'Soát lỗi, Xếp bàn tiệc & Đón khách QR',
                description: 'Trước khi gửi thiệp, hãy bấm "Soát Lỗi Trước Khi Gửi" để kiểm tra khách thiếu xưng hô. Khi khách đã phản hồi, chuyển sang "Sơ Đồ Bàn Tiệc" để xếp chỗ và dùng "Cổng Quét QR Tiếp Tân" trong ngày cưới.',
                tip: 'Bạn cũng có thể Xuất CSV (Excel) hoặc In danh sách chuẩn A4 cho bàn lễ tân bất cứ lúc nào.',
            },
            {
                id: 'guest-more-tools',
                selector: '#mayhen_guest_more_tools',
                title: 'Ngăn công cụ mở rộng theo từng giai đoạn',
                description: 'Nhấn mở ngăn này khi bạn cần dùng các trợ lý chuyên sâu: Soạn tin nhắc hẹn tế nhị, Lời cảm ơn sau cưới, Lọc & gộp khách trùng, Cổng phụ huynh hai họ hoặc Bảng kê suất ăn cho Bếp trưởng.',
                tip: 'Các công cụ phụ được xếp gọn tại đây để màn hình làm việc hàng ngày luôn thoáng đãng.',
            },
            {
                id: 'guest-replay',
                selector: '#mayhen_guest_tour_trigger_btn',
                title: 'Luôn sẵn sàng khi bạn muốn xem lại',
                description: 'Hệ thống sẽ ghi nhớ để không tự bật lại lần sau. Bất cứ khi nào cần ôn lại thao tác, bạn chỉ cần nhấn nút "Hướng dẫn" ở góc trên bên phải.',
                tip: 'Bạn có thể nhấn "Hoàn tất" để bắt đầu quản lý danh sách khách mời ngay bây giờ!',
            },
        ],
    },
};

export class ClientOpsProductTour {
    #boundResizeOrScroll;
    #boundKeyDown;

    constructor({ tourDef, persistence, doc = globalThis.document, win = globalThis.window } = {}) {
        this.tourDef = tourDef;
        this.persistence = persistence;
        this.doc = doc;
        this.win = win;
        this.activeSteps = [];
        this.currentIndex = -1;
        this.isOpen = false;
        this.previousFocusEl = null;

        this.layerEl = null;
        this.spotlightEl = null;
        this.cardEl = null;

        this.#boundResizeOrScroll = () => this.#onResizeOrScroll();
        this.#boundKeyDown = (e) => this.#onKeyDown(e);
    }

    hasBlockingDialog() {
        if (!this.doc) return false;
        return Boolean(this.doc.querySelector(BLOCKING_MODAL_SELECTOR));
    }

    resolveAvailableSteps() {
        if (!this.doc || !this.tourDef || !Array.isArray(this.tourDef.steps)) {
            return [];
        }
        return this.tourDef.steps.filter((step) => {
            const el = this.doc.querySelector(step.selector);
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            const style = this.win.getComputedStyle(el);
            return style.display !== 'none' && style.visibility !== 'hidden' && (rect.width > 0 || rect.height > 0);
        });
    }

    start({ fromBeginning = false, triggerEl = null } = {}) {
        if (this.hasBlockingDialog()) {
            return false;
        }

        this.activeSteps = this.resolveAvailableSteps();
        if (this.activeSteps.length === 0) {
            return false;
        }

        this.previousFocusEl = triggerEl || this.doc.activeElement || null;
        this.#mountDom();
        this.isOpen = true;

        let initialIdx = 0;
        if (!fromBeginning) {
            const saved = this.persistence.read();
            if (saved && saved.status === 'in_progress' && saved.last_step_id) {
                const foundIdx = this.activeSteps.findIndex((s) => s.id === saved.last_step_id);
                if (foundIdx >= 0) {
                    initialIdx = foundIdx;
                }
            }
        }

        this.win.addEventListener('resize', this.#boundResizeOrScroll, { passive: true });
        this.win.addEventListener('scroll', this.#boundResizeOrScroll, { passive: true });
        this.doc.addEventListener('keydown', this.#boundKeyDown);

        this.goToStep(initialIdx);
        return true;
    }

    goToStep(index) {
        if (!this.isOpen || index < 0 || index >= this.activeSteps.length) {
            return;
        }
        this.currentIndex = index;
        const step = this.activeSteps[index];
        this.persistence.markInProgress(step.id);

        const targetEl = this.doc.querySelector(step.selector);
        if (targetEl && typeof targetEl.scrollIntoView === 'function') {
            const rect = targetEl.getBoundingClientRect();
            const vh = this.win.innerHeight || 800;
            if (rect.top < 76 || rect.bottom > vh - 76) {
                try {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                } catch {
                    targetEl.scrollIntoView();
                }
            }
        }

        this.#renderCard(step, index, this.activeSteps.length);
        this.reposition();
        this.win.setTimeout(() => this.reposition(), 220);
        this.win.setTimeout(() => this.reposition(), 420);
    }

    next() {
        if (!this.isOpen) return;
        if (this.currentIndex >= this.activeSteps.length - 1) {
            this.complete();
            return;
        }
        this.goToStep(this.currentIndex + 1);
    }

    prev() {
        if (!this.isOpen || this.currentIndex <= 0) return;
        this.goToStep(this.currentIndex - 1);
    }

    skip() {
        if (!this.isOpen) return;
        const currentStep = this.activeSteps[this.currentIndex] || null;
        this.persistence.markSkipped(currentStep ? currentStep.id : null);
        this.#teardown();
    }

    complete() {
        if (!this.isOpen) return;
        this.persistence.markCompleted();
        this.#teardown();
    }

    reposition() {
        if (!this.isOpen || !this.spotlightEl || !this.cardEl) return;
        const step = this.activeSteps[this.currentIndex];
        if (!step) return;

        const targetEl = this.doc.querySelector(step.selector);
        const vw = this.win.innerWidth || 1280;
        const vh = this.win.innerHeight || 800;
        const isMobile = vw < 768;

        if (!targetEl) {
            this.spotlightEl.style.width = '0px';
            this.spotlightEl.style.height = '0px';
            this.spotlightEl.style.top = `${Math.round(vh / 2)}px`;
            this.spotlightEl.style.left = `${Math.round(vw / 2)}px`;
            return;
        }

        const rawRect = targetEl.getBoundingClientRect();
        const pad = isMobile ? 6 : 8;
        const spotTop = Math.max(8, Math.round(rawRect.top - pad));
        const spotLeft = Math.max(8, Math.round(rawRect.left - pad));
        const maxSpotWidth = Math.max(48, vw - spotLeft - 8);
        const maxSpotHeight = Math.max(36, vh - spotTop - 8);
        const spotWidth = Math.min(Math.round(rawRect.width + pad * 2), maxSpotWidth);
        const spotHeight = Math.min(Math.round(rawRect.height + pad * 2), maxSpotHeight);

        this.spotlightEl.style.top = `${spotTop}px`;
        this.spotlightEl.style.left = `${spotLeft}px`;
        this.spotlightEl.style.width = `${spotWidth}px`;
        this.spotlightEl.style.height = `${spotHeight}px`;

        const cardRect = this.cardEl.getBoundingClientRect();
        const cardW = cardRect.width || Math.min(392, vw - 24);
        const cardH = cardRect.height || 250;
        const margin = isMobile ? 10 : 16;
        const gap = isMobile ? 12 : 16;

        let cardLeft = Math.round(spotLeft + (spotWidth - cardW) / 2);
        cardLeft = Math.max(margin, Math.min(cardLeft, vw - cardW - margin));

        const spaceBelow = vh - (spotTop + spotHeight) - margin;
        const spaceAbove = spotTop - margin;
        let cardTop;

        if (spaceBelow >= cardH + gap) {
            cardTop = spotTop + spotHeight + gap;
        } else if (spaceAbove >= cardH + gap) {
            cardTop = spotTop - cardH - gap;
        } else if (!isMobile && vw - (spotLeft + spotWidth) >= cardW + gap + margin) {
            cardLeft = spotLeft + spotWidth + gap;
            cardTop = Math.max(margin, Math.min(spotTop, vh - cardH - margin));
        } else if (!isMobile && spotLeft >= cardW + gap + margin) {
            cardLeft = spotLeft - cardW - gap;
            cardTop = Math.max(margin, Math.min(spotTop, vh - cardH - margin));
        } else {
            // Mobile hoặc khối mục tiêu cao gần hết màn hình: đặt gọn ở mép dưới hoặc mép trên ít che mục tiêu nhất
            cardTop = spaceBelow >= spaceAbove
                ? Math.max(margin, vh - cardH - margin)
                : margin;
        }

        cardTop = Math.max(margin, Math.min(Math.round(cardTop), Math.max(margin, vh - cardH - margin)));

        this.cardEl.style.top = `${cardTop}px`;
        this.cardEl.style.left = `${cardLeft}px`;
    }

    #mountDom() {
        if (this.layerEl) return;

        const layer = this.doc.createElement('div');
        layer.className = 'mayhen_client_tour_layer';
        layer.setAttribute('data-ops-tour-active', this.tourDef.id);

        const catcher = this.doc.createElement('div');
        catcher.className = 'mayhen_client_tour_backdrop_catcher';
        catcher.addEventListener('click', () => {
            // Nhấn ngoài vùng hộp thoại không tự ý mất trạng thái mà chỉ giữ focus an toàn trong hộp hướng dẫn
            if (this.cardEl) {
                this.cardEl.focus({ preventScroll: true });
            }
        });

        const spotlight = this.doc.createElement('div');
        spotlight.className = 'mayhen_client_tour_spotlight';
        spotlight.setAttribute('aria-hidden', 'true');

        const card = this.doc.createElement('div');
        card.className = 'mayhen_client_tour_card';
        card.setAttribute('role', 'dialog');
        card.setAttribute('aria-modal', 'true');
        card.setAttribute('aria-labelledby', 'mayhen_client_tour_title');
        card.setAttribute('tabindex', '-1');

        layer.appendChild(catcher);
        layer.appendChild(spotlight);
        layer.appendChild(card);
        this.doc.body.appendChild(layer);

        this.layerEl = layer;
        this.spotlightEl = spotlight;
        this.cardEl = card;

        this.win.requestAnimationFrame(() => {
            if (this.layerEl) {
                this.layerEl.classList.add('mayhen_client_tour_active');
            }
        });
    }

    #renderCard(step, index, total) {
        if (!this.cardEl) return;

        const isFirst = index === 0;
        const isLast = index === total - 1;
        const progressPercent = Math.round(((index + 1) / total) * 100);

        this.cardEl.innerHTML = `
            <div class="mayhen_client_tour_card_body">
                <div class="mayhen_client_tour_top_row">
                    <span class="mayhen_client_tour_badge">
                        <span class="mayhen_client_tour_badge_dot" aria-hidden="true"></span>
                        <span>${this.#escape(this.tourDef.label)} • Bước ${index + 1}/${total}</span>
                    </span>
                    <button type="button" class="mayhen_client_tour_close_btn" data-client-tour-action="skip" aria-label="Đóng và bỏ qua hướng dẫn" title="Đóng hướng dẫn">&times;</button>
                </div>
                <h3 id="mayhen_client_tour_title" class="mayhen_client_tour_title">${this.#escape(step.title)}</h3>
                <p class="mayhen_client_tour_desc">${this.#escape(step.description)}</p>
                ${step.tip ? `<div class="mayhen_client_tour_tip">${this.#escape(step.tip)}</div>` : ''}
                <div class="mayhen_client_tour_progress_track" role="progressbar" aria-valuemin="1" aria-valuemax="${total}" aria-valuenow="${index + 1}">
                    <div class="mayhen_client_tour_progress_fill" style="width: ${progressPercent}%;"></div>
                </div>
                <div class="mayhen_client_tour_footer">
                    <button type="button" class="mayhen_client_tour_skip_btn" data-client-tour-action="skip">Bỏ qua hướng dẫn</button>
                    <div class="mayhen_client_tour_nav_group">
                        <button type="button" class="mayhen_client_tour_btn mayhen_client_tour_btn_prev" data-client-tour-action="prev" ${isFirst ? 'disabled' : ''}>Quay lại</button>
                        ${
                            isLast
                                ? '<button type="button" class="mayhen_client_tour_btn mayhen_client_tour_btn_finish" data-client-tour-action="finish">Hoàn tất</button>'
                                : '<button type="button" class="mayhen_client_tour_btn mayhen_client_tour_btn_next" data-client-tour-action="next">Tiếp theo →</button>'
                        }
                    </div>
                </div>
            </div>
        `;

        this.cardEl.querySelectorAll('[data-client-tour-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const action = btn.getAttribute('data-client-tour-action');
                if (action === 'next') this.next();
                else if (action === 'prev') this.prev();
                else if (action === 'skip') this.skip();
                else if (action === 'finish') this.complete();
            });
        });

        const primaryBtn = this.cardEl.querySelector('[data-client-tour-action="next"], [data-client-tour-action="finish"]');
        if (primaryBtn && typeof primaryBtn.focus === 'function') {
            primaryBtn.focus({ preventScroll: true });
        }
    }

    #onResizeOrScroll() {
        if (!this.isOpen) return;
        this.reposition();
    }

    #onKeyDown(e) {
        if (!this.isOpen) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            this.skip();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            this.next();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            this.prev();
        }
    }

    #teardown() {
        this.isOpen = false;
        this.win.removeEventListener('resize', this.#boundResizeOrScroll);
        this.win.removeEventListener('scroll', this.#boundResizeOrScroll);
        this.doc.removeEventListener('keydown', this.#boundKeyDown);

        if (this.layerEl) {
            const toRemove = this.layerEl;
            this.layerEl = null;
            this.spotlightEl = null;
            this.cardEl = null;
            toRemove.classList.remove('mayhen_client_tour_active');
            this.win.setTimeout(() => {
                if (toRemove.parentNode) {
                    toRemove.parentNode.removeChild(toRemove);
                }
            }, 180);
        }

        const restoreTarget = this.previousFocusEl
            || this.doc.querySelector(this.tourDef.triggerSelector);
        if (restoreTarget && typeof restoreTarget.focus === 'function') {
            try {
                restoreTarget.focus({ preventScroll: true });
            } catch {
                // Ignore focus error on detached nodes
            }
        }
    }

    #escape(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
}

export function bootClientOpsTour(doc = globalThis.document, win = globalThis.window) {
    if (!doc || !win) return null;

    const hostEl = doc.querySelector('[data-ops-tour]');
    if (!hostEl) return null;

    const tourId = normalizeTourId(hostEl.getAttribute('data-ops-tour'));
    if (!tourId || !TOUR_DEFINITIONS[tourId]) return null;

    const userId = hostEl.getAttribute('data-user-id') || 'guest';
    const tourDef = TOUR_DEFINITIONS[tourId];
    const persistence = new ClientOpsTourPersistence({ tourId, userId });
    const controller = new ClientOpsProductTour({ tourDef, persistence, doc, win });

    doc.querySelectorAll(tourDef.triggerSelector).forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            controller.start({ fromBeginning: true, triggerEl: btn });
        });
    });

    const params = new URLSearchParams(win.location ? win.location.search : '');
    const tourQuery = params.get('tour');

    if (tourQuery === '1') {
        win.setTimeout(() => controller.start({ fromBeginning: true }), 260);
    } else if (tourQuery !== '0' && persistence.shouldAutoStart()) {
        win.setTimeout(() => {
            if (!controller.hasBlockingDialog()) {
                controller.start({ fromBeginning: false });
            }
        }, 320);
    }

    return controller;
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => bootClientOpsTour());
    } else {
        bootClientOpsTour();
    }
}
