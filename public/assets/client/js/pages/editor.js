/**
 * MAYHEN.VN — INVITATION LIVE EDITOR CONTROLLER V2
 * Single State Store, Autosave Engine, Undo/Redo & Zero-Loss Template Switcher
 * Strict: Pure Vanilla JavaScript (ES Modules), Zero external dependencies
 */
import { $, $$, on } from '../common/dom.js';
import { EditorStore } from '../editor/store.js';
import { ActionTypes } from '../editor/actions.js';
import { TemplateSwitcher } from '../editor/template-switcher.js';
import { SectionDnDManager } from '../editor/dnd-section.js';
import { ThemeController } from '../editor/theme-controller.js';
import { applyWeddingDraft, mirrorWeddingInput } from '../editor/draft-recovery.js';

const initEditor = () => {
    const mainForm = $('#mayhen_editor_form');
    const previewIframe = $('#mayhen_editor_preview_iframe');
    const mobileIframe = $('#mayhen_editor_mobile_preview_iframe');
    const dockNames = $('[data-mayhen-preview="names"]');

    // UI Topbar Elements
    const savePill = $('#mayhen_save_pill');
    const saveText = savePill?.querySelector('.mayhen_editor_save_text');
    const btnUndo = $('#mayhen_btn_undo');
    const btnRedo = $('#mayhen_btn_redo');
    const btnDeviceMobile = $('#mayhen_btn_device_mobile');
    const btnDeviceDesktop = $('#mayhen_btn_device_desktop');
    const centerStage = $('#mayhen_editor_center_stage') || $('.mayhen_editor_center_stage');

    // ----------------------------------------------------
    // 0. Initialize Single State Store & Subsystems
    // ----------------------------------------------------
    const invitationId = window.__MAYHEN_INVITATION_ID || null;
    const templateId = window.__MAYHEN_TEMPLATE_ID || null;
    const templateSlug = window.__MAYHEN_TEMPLATE_SLUG || '';
    const initialRevision = window.__MAYHEN_REVISION_ID || 0;
    const customConfigInput = document.getElementById('invitation_custom_config');

    // Đọc các giá trị input hiện có trong DOM để khởi tạo State
    const initialWedding = {};
    const inputs = $$('[data-sync-key]');
    inputs.forEach(inp => {
        const key = inp.getAttribute('data-sync-key');
        if (key && key.startsWith('wedding.')) {
            const prop = key.replace('wedding.', '');
            initialWedding[prop] = inp.value;
        }
    });

    const store = new EditorStore({
        invitation_id: invitationId,
        template_id: templateId,
        template_slug: templateSlug,
        revision_id: initialRevision,
        wedding: initialWedding,
        custom_config: EditorStore.parseCustomConfig(customConfigInput?.value),
    });

    const hasRevisionConflict = mainForm?.dataset.revisionConflict === 'true';
    if (hasRevisionConflict) {
        store.autosaver.hasConflict = true;
        store.state.ui.isDirty = true;
        store.setSaveStatus('conflict', { message: 'Thiệp đã thay đổi ở phiên khác. Hãy giữ bản nháp và kiểm tra trước khi tiếp tục.' });
    }

    const hasProtectedLocalDraft = !hasRevisionConflict && store.autosaver.protectStaleLocalDraft(initialRevision);
    const hasProtectedTransferDraft = !hasRevisionConflict && !hasProtectedLocalDraft
        && store.autosaver.protectStaleTransferDraft(initialRevision, templateSlug);
    const hasProtectedDraft = hasProtectedLocalDraft || hasProtectedTransferDraft;
    if (hasProtectedDraft) {
        store.state.ui.isDirty = true;
        store.setSaveStatus('conflict', { message: 'Bản nháp trên máy không khớp với thiệp hiện tại.' });
        if (savePill) savePill.setAttribute('data-status', 'conflict');
        if (saveText) saveText.textContent = 'Xung đột phiên làm việc!';
        if (mainForm) mainForm.inert = true;

        const recovery = document.getElementById('mayhen_editor_recovery');
        const recoveryDescription = document.getElementById('mayhen_editor_recovery_description');
        const recoveryMessage = document.getElementById('mayhen_editor_recovery_message');
        const downloadButton = document.getElementById('mayhen_editor_recovery_download');
        const continueButton = document.getElementById('mayhen_editor_recovery_continue');
        if (recovery) recovery.hidden = false;
        if (hasProtectedTransferDraft && recoveryDescription) {
            recoveryDescription.textContent = 'Bản đang chuyển mẫu có thể chứa thay đổi chưa lưu. Hãy tải xuống nếu cần; khi tiếp tục, bản này chỉ được giữ trong tab hiện tại cho đến khi đóng tab.';
        }
        if (!invitationId && continueButton) continueButton.textContent = 'Bắt đầu lại';
        downloadButton?.addEventListener('click', () => {
            try {
                const raw = store.autosaver.protectedLocalDraft?.raw ?? store.autosaver.protectedTransferDraft?.raw;
                if (typeof raw !== 'string') throw new Error('Missing draft');
                const url = URL.createObjectURL(new Blob([raw], { type: 'application/json;charset=utf-8' }));
                const link = document.createElement('a');
                link.href = url;
                link.download = `mayhen-ban-nhap-${invitationId || 'moi'}.json`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                if (recoveryMessage) recoveryMessage.textContent = 'Đã yêu cầu tải bản nháp xuống thiết bị.';
            } catch (error) {
                if (recoveryMessage) recoveryMessage.textContent = 'Không thể tải bản nháp. Bản gốc vẫn được giữ trên trình duyệt.';
            }
        });
        continueButton?.addEventListener('click', () => {
            if (hasProtectedLocalDraft) {
                try {
                    if (sessionStorage.getItem('mayhen_transferred_draft') !== null) {
                        sessionStorage.setItem('mayhen_skip_transferred_draft', '1');
                        if (sessionStorage.getItem('mayhen_skip_transferred_draft') !== '1') throw new Error('Cannot preserve transfer');
                    }
                } catch (error) {
                    if (recoveryMessage) recoveryMessage.textContent = 'Không thể kiểm tra bản nháp đang chuyển mẫu. Hãy tải bản nháp xuống và thử lại.';
                    return;
                }
            }
            const preserved = hasProtectedLocalDraft
                ? store.autosaver.archiveProtectedLocalDraft()
                : store.autosaver.suppressProtectedTransferDraft();
            if (!preserved) {
                if (recoveryMessage) recoveryMessage.textContent = 'Không thể giữ bản nháp hoặc bản nháp đã thay đổi. Hãy tải xuống hoặc kiểm tra lại trước khi tiếp tục.';
                return;
            }
            store.state.ui.isDirty = false;
            window.location.reload();
        });
    }

    // Khởi tạo các phân hệ
    const templateSwitcher = new TemplateSwitcher(store, { previewIframe, mobileIframe });
    const dndManager = new SectionDnDManager(store, { previewIframe, mobileIframe });
    const themeController = new ThemeController(store, { previewIframe, mobileIframe });

    // Thuật toán chuyển đổi ngày Dương Lịch sang Âm Lịch Việt Nam chuẩn thiên văn (Hồ Ngọc Đức - GMT+7)
    const calcVietnameseLunar = (solarDateStr) => {
        if (!solarDateStr) return '';
        const parts = solarDateStr.toString().trim().split('-');
        if (parts.length !== 3) return '';
        const yy = parseInt(parts[0], 10);
        const mm = parseInt(parts[1], 10);
        const dd = parseInt(parts[2], 10);
        if (isNaN(yy) || isNaN(mm) || isNaN(dd)) return '';

        function INT(d) { return Math.floor(d); }
        function jdFromDate(d, m, y) {
            const a = INT((14 - m) / 12);
            const y1 = y + 4800 - a;
            const m1 = m + 12 * a - 3;
            return d + INT((153 * m1 + 2) / 5) + 365 * y1 + INT(y1 / 4) - INT(y1 / 100) + INT(y1 / 400) - 32045;
        }
        function getNewMoonDay(k, tz) {
            const T = k / 1236.85;
            const T2 = T * T;
            const T3 = T2 * T;
            const dr = Math.PI / 180;
            const Jd1 = 2415020.75933 + 29.53058868 * k + 0.0001178 * T2 - 0.000000155 * T3;
            const M = 359.2242 + 29.10535608 * k - 0.0000333 * T2 - 0.00000347 * T3;
            const Mprime = 306.0253 + 385.81691806 * k + 0.0107306 * T2 + 0.00001236 * T3;
            const F = 21.2964 + 390.67050646 * k - 0.0016528 * T2 - 0.00000239 * T3;
            const C1 = (0.1734 - 0.000393 * T) * Math.sin(M * dr) + 0.0021 * Math.sin(2 * dr * M)
                   - 0.4068 * Math.sin(Mprime * dr) + 0.0161 * Math.sin(2 * dr * Mprime)
                   - 0.0004 * Math.sin(3 * dr * Mprime) + 0.0104 * Math.sin(2 * dr * F)
                   - 0.0051 * Math.sin(dr * (M + Mprime)) - 0.0074 * Math.sin(dr * (M - Mprime))
                   + 0.0004 * Math.sin(dr * (2 * F + M)) - 0.0004 * Math.sin(dr * (2 * F - M))
                   - 0.0006 * Math.sin(dr * (2 * F + Mprime)) + 0.001 * Math.sin(dr * (2 * F - Mprime))
                   + 0.0005 * Math.sin(dr * (M + 2 * Mprime));
            return INT(Jd1 + C1 + 0.5 + tz / 24);
        }
        function getSunLongitude(jdn, tz) {
            const T = (jdn - 2451545.0 + 0.5 - tz / 24) / 36525;
            const dr = Math.PI / 180;
            const M = 357.5291 + 35999.0503 * T - 0.0001559 * T * T - 0.00000048 * T * T * T;
            const L0 = 280.46645 + 36000.76983 * T + 0.0003032 * T * T;
            const DL = (1.9146 - 0.004817 * T - 0.000014 * T * T) * Math.sin(dr * M)
                   + (0.019993 - 0.000101 * T) * Math.sin(2 * dr * M) + 0.00029 * Math.sin(3 * dr * M);
            const L = (L0 + DL) * dr;
            return INT((L - Math.PI * 2 * INT(L / (Math.PI * 2))) / (Math.PI / 6));
        }
        function getLunarMonth11(y, tz) {
            const off = jdFromDate(31, 12, y) - 2415021;
            const k = INT(off / 29.530588853);
            const nm = getNewMoonDay(k, tz);
            return getSunLongitude(nm, tz) >= 9 ? getNewMoonDay(k - 1, tz) : nm;
        }
        function getLeapOffset(a11, tz) {
            const k = INT((a11 - 2415021.076998695) / 29.530588853 + 0.5);
            let last = 0, i = 1, arc = getSunLongitude(getNewMoonDay(k + i, tz), tz);
            do {
                last = arc;
                i++;
                arc = getSunLongitude(getNewMoonDay(k + i, tz), tz);
            } while (arc !== last && i < 14);
            return i - 1;
        }

        const tz = 7;
        const dayNumber = jdFromDate(dd, mm, yy);
        const k = INT((dayNumber - 2415021.076998695) / 29.530588853);
        let monthStart = getNewMoonDay(k + 1, tz);
        if (monthStart > dayNumber) {
            monthStart = getNewMoonDay(k, tz);
        }
        let a11 = getLunarMonth11(yy, tz);
        let b11 = a11;
        let lunarYear = yy;
        if (a11 >= monthStart) {
            lunarYear = yy - 1;
            a11 = getLunarMonth11(yy - 1, tz);
        } else {
            b11 = getLunarMonth11(yy + 1, tz);
        }
        const lunarDay = dayNumber - monthStart + 1;
        const diff = INT((monthStart - a11) / 29);
        let lunarLeap = 0;
        let lunarMonth = diff + 11;
        if (b11 - a11 > 365) {
            const leapDiff = getLeapOffset(a11, tz);
            if (diff >= leapDiff) {
                lunarMonth = diff + 10;
                if (diff === leapDiff) lunarLeap = 1;
            }
        }
        if (lunarMonth > 12) lunarMonth -= 12;
        if (lunarMonth >= 11 && diff < 4) lunarYear -= 1;

        const canList = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];
        const chiList = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

        const canChiYearNum = (lunarMonth >= 11) ? (lunarYear + 1) : lunarYear;
        const canYear = canList[(canChiYearNum + 6) % 10];
        const chiYear = chiList[(canChiYearNum + 8) % 12];
        const leapText = lunarLeap === 1 ? ' nhuận' : '';

        const dStr = lunarDay < 10 ? '0' + lunarDay : '' + lunarDay;
        const mStr = lunarMonth < 10 ? '0' + lunarMonth : '' + lunarMonth;

        return `Nhằm ngày ${dStr} tháng ${mStr}${leapText} năm ${canYear} ${chiYear}`;
    };

    // Hàm kiểm tra xem một key có phải trường ảnh/album không
    const isImageKey = (k) => {
        const lower = (k || '').toLowerCase();
        return lower.includes('photo') || lower.includes('cover') || lower.includes('image') || lower.includes('gallery') || lower.includes('album');
    };

    // Hàm kiểm tra và loại bỏ các URL ảnh rác từ bên ngoài (motdoi, ladicdn, 27biggroup...)
    const isBannedExternalUrl = (urlStr) => {
        if (typeof urlStr !== 'string') return false;
        const lower = urlStr.toLowerCase();
        return lower.includes('motdoi.com') ||
               lower.includes('ladicdn.com') ||
               lower.includes('27biggroup.com') ||
               lower.includes('unsplash.com') ||
               lower.includes('iili.io');
    };

    // Kiểm tra và phục hồi bản nháp (Ưu tiên bản nháp vừa chuyển mẫu transferred_draft)
    let draftWeddingData = null;
    let draftCustomConfig = null;
    let restoringTransfer = false;
    let restoredFieldKeys = new Set();
    let recoveryRaw = null;
    try {
        const transferredDraftRaw = sessionStorage.getItem('mayhen_transferred_draft');
        const skipTransferredDraft = sessionStorage.getItem('mayhen_skip_transferred_draft') === '1';
        if (!hasRevisionConflict && !hasProtectedDraft && !skipTransferredDraft && transferredDraftRaw
            && transferredDraftRaw === store.autosaver.validatedTransferDraft?.raw) {
            const parsed = JSON.parse(transferredDraftRaw);
            if (parsed && parsed.wedding) {
                draftWeddingData = parsed.wedding;
                draftCustomConfig = parsed.custom_config;
                recoveryRaw = transferredDraftRaw;
                restoringTransfer = true;
                store.autosaver.isRestoringTransfer = true;
            }
        }
    } catch (e) {}

    if (!hasRevisionConflict && !hasProtectedDraft && !draftWeddingData) {
        const localDraft = store.autosaver.getLocalDraft(invitationId, templateSlug);
        if (localDraft && localDraft.wedding) {
            // Chỉ nạp localDraft nếu đúng mẫu thiệp hiện tại hoặc đang chỉnh sửa thiệp đã lưu
            const isSameTemplate = localDraft.template_slug === templateSlug;
            const hasData = Object.keys(EditorStore.flattenWeddingFields(localDraft.wedding)).length > 0;
            if (hasData && (isSameTemplate || invitationId)) {
                draftWeddingData = localDraft.wedding;
                draftCustomConfig = localDraft.custom_config;
                recoveryRaw = JSON.stringify(localDraft);
                store.autosaver.isRestoringTransfer = true;
            }
        }
    }

    let transferRecoveryFailed = false;
    try {
        if (draftWeddingData) {
            const restored = applyWeddingDraft(mainForm, draftWeddingData, store.getState().wedding);
            restoredFieldKeys = restored.restoredKeys;
            if (draftCustomConfig && typeof draftCustomConfig === 'object' && !Array.isArray(draftCustomConfig)) {
                store.dispatch({ type: ActionTypes.UPDATE_CUSTOM_CONFIG, payload: draftCustomConfig });
                if (customConfigInput) customConfigInput.value = JSON.stringify(store.getState().custom_config);
            }
            store.dispatch({ type: ActionTypes.UPDATE_WEDDING, payload: restored.wedding });
            if (restoringTransfer) {
                transferRecoveryFailed = !store.autosaver.finishTransferRecovery();
            } else {
                store.autosaver.isRestoringTransfer = false;
                if (!store.autosaver.saveToLocalStorage(store.getState(), true)) throw new Error('Cannot preserve restored local draft');
                store.autosaver.schedule();
            }
        }
    } catch (error) {
        store.autosaver.failTransferRecovery();
        if (!restoringTransfer) store.autosaver.protectedLocalDraft = { key: store.autosaver.getDraftKey(store.getState()), raw: recoveryRaw };
        transferRecoveryFailed = true;
        console.warn('[Editor] Transfer recovery failed:', error);
    }

    if (transferRecoveryFailed) {
        store.state.ui.isDirty = true;
        store.setSaveStatus('conflict', { message: 'Không thể lưu bản nháp đã khôi phục trên thiết bị.' });
        if (savePill) savePill.setAttribute('data-status', 'conflict');
        if (saveText) saveText.textContent = 'Không thể khôi phục bản nháp!';
        if (mainForm) mainForm.inert = true;
        const recovery = document.getElementById('mayhen_editor_recovery');
        const description = document.getElementById('mayhen_editor_recovery_description');
        const message = document.getElementById('mayhen_editor_recovery_message');
        const download = document.getElementById('mayhen_editor_recovery_download');
        const retry = document.getElementById('mayhen_editor_recovery_continue');
        if (recovery) recovery.hidden = false;
        if (description) description.textContent = 'Không thể lưu bản nháp đã khôi phục trên thiết bị. Hãy tải bản nháp đã đọc xuống khi trang còn mở trước khi thử lại.';
        if (retry) {
            retry.textContent = 'Thử lại';
            retry.addEventListener('click', () => window.location.reload());
        }
        download?.addEventListener('click', () => {
            try {
                const url = URL.createObjectURL(new Blob([recoveryRaw], { type: 'application/json;charset=utf-8' }));
                const link = document.createElement('a');
                link.href = url;
                link.download = `mayhen-ban-nhap-${invitationId || 'moi'}.json`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                if (message) message.textContent = 'Đã yêu cầu tải bản nháp xuống thiết bị.';
            } catch (error) {
                if (message) message.textContent = 'Không thể tải bản nháp. Hãy giữ trang hiện tại và thử lại.';
            }
        });
    }

    // Subscribe Store để cập nhật UI Topbar (Autosave pill, Undo/Redo buttons)
    store.subscribe((state, action) => {
        // Cập nhật trạng thái nút Undo/Redo
        if (btnUndo) btnUndo.disabled = !state.ui.canUndo;
        if (btnRedo) btnRedo.disabled = !state.ui.canRedo;

        // Cập nhật Save Status Pill
        if (savePill && saveText) {
            const status = state.ui.saveStatus;
            savePill.setAttribute('data-status', status);

            switch (status) {
                case 'idle':
                    saveText.textContent = 'Đã sẵn sàng';
                    break;
                case 'pending':
                    saveText.textContent = 'Có thay đổi...';
                    break;
                case 'saving':
                    saveText.textContent = 'Đang lưu nháp...';
                    break;
                case 'saved':
                    saveText.textContent = 'Đã lưu nháp';
                    break;
                case 'local_only':
                    saveText.textContent = 'Đã lưu trình duyệt';
                    break;
                case 'conflict':
                    saveText.textContent = 'Xung đột phiên làm việc!';
                    break;
                case 'offline':
                    saveText.textContent = 'Đang lưu ngoại tuyến';
                    break;
                case 'storage_error':
                    saveText.textContent = 'Chưa lưu được trên thiết bị';
                    break;
                default:
                    saveText.textContent = 'Đã sẵn sàng';
            }
        }

        // Cập nhật chế độ thiết bị xem trước (Mobile / Desktop)
        if (state.ui && state.ui.previewDevice) {
            const isDesktop = state.ui.previewDevice === 'desktop';
            btnDeviceDesktop?.classList.toggle('active', isDesktop);
            btnDeviceMobile?.classList.toggle('active', !isDesktop);
            centerStage?.classList.toggle('is-desktop-mode', isDesktop);
        }

        // Luôn đồng bộ custom_config (bố cục, bảng màu, tem sáp) vào input hidden của form
        const customConfigInput = document.getElementById('invitation_custom_config');
        if (customConfigInput && state.custom_config) {
            customConfigInput.value = JSON.stringify(state.custom_config);
        }

        // Nếu là action Undo hoặc Redo, cập nhật ngược lại các input trên DOM
        if (action.type === ActionTypes.UNDO || action.type === ActionTypes.REDO) {
            const restored = applyWeddingDraft(mainForm, state.wedding, {}, document, { authoritative: true, templateSlug });
            restoredFieldKeys = restored.restoredKeys;
            syncAllFieldsToIframe(previewIframe, true);
            syncAllFieldsToIframe(mobileIframe, true);
            updateDockNames();
        }
    });

    // Bắt sự kiện Undo / Redo qua nút bấm Topbar
    if (btnUndo) {
        on(btnUndo, 'click', () => store.dispatch({ type: ActionTypes.UNDO }));
    }
    if (btnRedo) {
        on(btnRedo, 'click', () => store.dispatch({ type: ActionTypes.REDO }));
    }

    // Phím tắt bàn phím Ctrl+Z, Ctrl+Y
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
            if (e.shiftKey) {
                e.preventDefault();
                store.dispatch({ type: ActionTypes.REDO });
            } else {
                e.preventDefault();
                store.dispatch({ type: ActionTypes.UNDO });
            }
        } else if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
            e.preventDefault();
            store.dispatch({ type: ActionTypes.REDO });
        }
    });

    // Chuyển đổi chế độ xem Thiết bị Preview (Mobile / Desktop)
    if (btnDeviceMobile && btnDeviceDesktop) {
        on(btnDeviceMobile, 'click', () => {
            btnDeviceMobile.classList.add('active');
            btnDeviceDesktop.classList.remove('active');
            centerStage?.classList.remove('is-desktop-mode');
            store.dispatch({ type: ActionTypes.SET_PREVIEW_DEVICE, payload: { device: 'mobile' } });
        });

        on(btnDeviceDesktop, 'click', () => {
            btnDeviceDesktop.classList.add('active');
            btnDeviceMobile.classList.remove('active');
            centerStage?.classList.add('is-desktop-mode');
            store.dispatch({ type: ActionTypes.SET_PREVIEW_DEVICE, payload: { device: 'desktop' } });
        });
    }

    // ----------------------------------------------------
    // Chuyển đổi các Tab trên Left Tool Rail (Nội Dung, Bố Cục, Màu Sắc, Âm Nhạc)
    // ----------------------------------------------------
    const railItems = $$('.mayhen_editor_rail_item');
    const subpanels = $$('.mayhen_editor_subpanel');

    railItems.forEach(item => {
        on(item, 'click', () => {
            const tabName = item.getAttribute('data-rail-tab');
            if (!tabName || tabName === 'templates') return; // templates mở modal riêng

            railItems.forEach(ri => ri.classList.remove('active'));
            item.classList.add('active');

            subpanels.forEach(p => {
                if (p.getAttribute('data-panel-name') === tabName) {
                    p.style.display = tabName === 'content' ? 'flex' : 'block';
                } else {
                    p.style.display = 'none';
                }
            });

            store.dispatch({
                type: ActionTypes.SET_ACTIVE_TAB,
                payload: { tab: tabName }
            });
        });
    });

    // Mở kho nhạc từ Tab Âm Nhạc
    const openMusicModalBtn = $('#mayhen_open_music_modal_btn');
    if (openMusicModalBtn) {
        on(openMusicModalBtn, 'click', () => {
            const mModal = $('#mayhen_music_picker_modal');
            if (mModal) {
                mModal.classList.add('mayhen_editor_modal_open');
                document.body.style.overflow = 'hidden';
                const searchInp = $('#mayhen_music_search_input');
                if (searchInp) searchInp.dispatchEvent(new Event('input'));
            }
        });
    }

    // ----------------------------------------------------
    // 1. Realtime Iframe Communication Engine (Dual Channel: Direct Call + PostMessage)
    // ----------------------------------------------------
    let currentStep = 1;
    let lastScrolledKey = null;
    let scrollDebounceTimer = null;

    const getActiveIframes = () => {
        return [
            document.getElementById('mayhen_editor_preview_iframe'),
            document.getElementById('mayhen_editor_mobile_preview_iframe')
        ].filter(Boolean);
    };

    const dispatchToIframes = (directFnName, directArgs, postPayload) => {
        getActiveIframes().forEach(iframe => {
            if (!iframe || !iframe.contentWindow) return;

            // Kênh 1: Gọi hàm trực tiếp trên cùng Origin (Tức thời 0ms, không trễ event loop)
            try {
                if (directFnName && typeof iframe.contentWindow[directFnName] === 'function') {
                    iframe.contentWindow[directFnName].apply(iframe.contentWindow, directArgs);
                }
            } catch (e) {
                // Safe cross-origin fallback
            }

            // Kênh 2: Gửi postMessage dự phòng chuẩn HTML5
            try {
                if (postPayload) {
                    iframe.contentWindow.postMessage(postPayload, '*');
                }
            } catch (e) {
                // Safe cross-origin fallback
            }
        });
    };

    const sendSyncMessage = (key, value) => {
        dispatchToIframes(
            '__mayhenSyncInput',
            [key, value],
            { type: 'MAYHEN_SYNC_INPUT', key, value }
        );
    };

    // Cuộn tới Section theo ID hoặc Step (1..10)
    const sendScrollToSection = (sectionId, step) => {
        if (!sectionId && !step) return;
        dispatchToIframes(
            '__mayhenScrollToSection',
            [sectionId, step],
            { type: 'MAYHEN_SCROLL_TO_SECTION', section_id: sectionId, step }
        );
    };

    // Smart Scroll-on-Action: Gửi thông điệp cuộn tới phần tử đích và Highlight vàng kim trong Preview
    const sendScrollToTarget = (key, action = 'focus') => {
        if (!key) return;
        dispatchToIframes(
            '__mayhenScrollToTarget',
            [key, action, currentStep],
            { type: 'MAYHEN_SCROLL_TO_TARGET', target: key, action, step: currentStep }
        );
    };

    // ----------------------------------------------------
    // 2. Dynamic Stepper Navigation
    // ----------------------------------------------------
    const stepTabs = $$('[data-mayhen-step]');
    const stepPanels = $$('[data-step-panel]');
    const totalSteps = stepPanels.length;
    const btnPrev = $('#mayhen_btn_prev');
    const btnNext = $('#mayhen_btn_next');

    const goToStep = (step, triggerScroll = true) => {
        if (step < 1 || step > totalSteps) return;
        currentStep = step;

        // Cập nhật tab navigation pills
        stepTabs.forEach(tab => {
            const s = parseInt(tab.getAttribute('data-mayhen-step'), 10);
            if (s === currentStep) {
                tab.classList.add('mayhen_editor_stepper_tab_active');
                tab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                tab.classList.remove('mayhen_editor_stepper_tab_active');
            }
        });

        // Cập nhật step panels
        stepPanels.forEach(panel => {
            const p = parseInt(panel.getAttribute('data-step-panel'), 10);
            if (p === currentStep) {
                panel.classList.add('mayhen_editor_step_active');
            } else {
                panel.classList.remove('mayhen_editor_step_active');
            }
        });

        // Cập nhật trạng thái nút Quay lại
        if (btnPrev) {
            btnPrev.disabled = currentStep === 1;
        }

        // Cập nhật chỉ số bước hiện tại
        const stepCurrentEl = document.querySelector('.mayhen_step_current');
        if (stepCurrentEl) {
            stepCurrentEl.textContent = `Bước ${currentStep}`;
        }

        // Tự động cuộn Iframe preview đến đúng Section tương ứng (Cơ chế KÉP: Gọi Trực Tiếp + PostMessage)
        if (triggerScroll) {
            const activeTab = document.querySelector(`[data-mayhen-step="${currentStep}"]`);
            const sectionId = activeTab ? activeTab.getAttribute('data-section-id') : null;
            if (sectionId) {
                sendScrollToSection(sectionId, currentStep);
            }
        }

        // Cuộn mượt form về đầu khi chuyển bước
        const formEl = $('#mayhen_editor_form');
        if (formEl) {
            formEl.scrollTo({ top: 0, behavior: 'smooth' });
        }
        const drawerPanel = $('#mayhen_drawer_panel');
        if (drawerPanel) {
            drawerPanel.scrollTo({ top: 0, behavior: 'smooth' });
        }

        if (btnNext) {
            if (currentStep === totalSteps) {
                btnNext.innerHTML = `<span>Xuất Bản Thiệp</span> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>`;
            } else {
                btnNext.innerHTML = `<span>Tiếp Tục</span> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>`;
            }
        }
    };

    stepTabs.forEach(tab => {
        on(tab, 'click', () => {
            const s = parseInt(tab.getAttribute('data-mayhen-step'), 10);
            goToStep(s, true);
        });
    });

    if (btnPrev) {
        on(btnPrev, 'click', () => {
            goToStep(currentStep - 1, true);
        });
    }

    if (btnNext) {
        on(btnNext, 'click', () => {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1, true);
            } else {
                submitFormWithPendingUploads();
            }
        });
    }

    // Khởi tạo trạng thái ban đầu cho Stepper (không ép scroll iframe trước khi handshake)
    goToStep(1, false);

    const updateDockNames = () => {
        if (!dockNames) return;
        const groomInp = document.querySelector('[data-sync-key="wedding.groom_name"]');
        const brideInp = document.querySelector('[data-sync-key="wedding.bride_name"]');
        const g = groomInp ? groomInp.value.trim() : 'Tràng Đức';
        const b = brideInp ? brideInp.value.trim() : 'Ngọc Phượng';
        dockNames.textContent = `${g || 'Chú Rể'} • ${b || 'Cô Dâu'}`;
    };

    // Tự động tính Âm lịch Việt Nam từ ô ngày Dương lịch
    const updateAutoLunarFromDateInput = (dateInput) => {
        if (!dateInput) return;
        const solarVal = dateInput.value;
        const lunarTargetId = dateInput.getAttribute('data-lunar-target');
        if (!lunarTargetId) return;

        const lunarInput = document.getElementById(lunarTargetId);
        const lunarTextEl = document.getElementById('text_' + lunarTargetId);

        if (!solarVal) return;
        const lunarCalculated = calcVietnameseLunar(solarVal);
        if (!lunarCalculated) return;

        if (lunarTextEl) {
            lunarTextEl.textContent = lunarCalculated;
        }
        if (lunarInput) {
            lunarInput.value = lunarCalculated;
            const lKey = lunarInput.getAttribute('data-sync-key');
            if (lKey) {
                store.dispatch({
                    type: ActionTypes.UPDATE_FIELD,
                    payload: { key: lKey, value: lunarCalculated }
                });
                sendSyncMessage(lKey, lunarCalculated);
                sendSyncMessage('wedding_lunar_date', lunarCalculated);
            }
        }
    };

    if (mainForm) {
        // Smart Scroll-on-Action: Khi người dùng focus hoặc click vào bất kỳ ô nhập liệu nào (Toàn cục, Capture Phase)
        const handleFieldInteraction = (e) => {
            const target = e.target.closest('input, textarea, select');
            if (!target) return;
            // Bỏ qua input ẩn hoặc nút bấm
            if (target.type === 'file' || target.type === 'submit' || target.type === 'button') return;
            const key = target.getAttribute('data-sync-key') || target.getAttribute('name') || target.id;
            if (key) {
                lastScrolledKey = key;
                sendScrollToTarget(key, e.type);
            }
        };

        document.addEventListener('focusin', handleFieldInteraction, true);
        document.addEventListener('click', (e) => {
            // Nếu click trực tiếp vào ô input/textarea/select
            if (e.target.matches('input:not([type="file"]):not([type="hidden"]), textarea, select')) {
                handleFieldInteraction(e);
            }
        }, true);

        const handleInputEvent = (e) => {
            const target = e.target;
            const key = target.getAttribute('data-sync-key');
            if (!key) return;

            // TUYỆT ĐỐI KHÔNG can thiệp hoặc gán đè target.value trong sự kiện input
            // để bảo vệ trọn vẹn bộ gõ tiếng Việt (Unikey/EVKey/Windows IME) không bị nuốt chữ hay nhảy con trỏ.
            const val = target.value;
            mirrorWeddingInput(mainForm, target);

            // Dispatch vào State Store
            store.dispatch({
                type: ActionTypes.UPDATE_FIELD,
                payload: { key, value: val }
            });

            // Xem trước ảnh nhỏ nếu là trường image
            if (target.id) {
                const thumb = document.getElementById('preview_thumb_' + target.id);
                if (thumb && val) {
                    if (val.startsWith('http://') || val.startsWith('https://') || val.startsWith('/') || val.startsWith('blob:') || val.startsWith('data:')) {
                        thumb.src = val;
                    } else if (templateSlug) {
                        thumb.src = `/mau-thiep/${templateSlug}/assets/${val}`;
                    }
                }
            }

            // Cập nhật tên đôi uyên ương trên mobile dock
            if (key === 'wedding.groom_name' || key === 'wedding.bride_name') {
                updateDockNames();
            }

            // Tự động tính toán ngày Âm lịch nếu là ô ngày dương lịch
            if (target && target.classList && target.classList.contains('mayhen_date_with_auto_lunar')) {
                updateAutoLunarFromDateInput(target);
            }

            // Smart Scroll-on-Action: Khi người dùng đang nhập họ tên hoặc dữ liệu, tự động cuộn
            lastScrolledKey = key;
            if (e.type === 'change' || (target && target.type === 'date')) {
                sendScrollToTarget(key, 'change');
            } else {
                clearTimeout(scrollDebounceTimer);
                scrollDebounceTimer = setTimeout(() => {
                    sendScrollToTarget(key, 'type');
                }, 180);
            }

            // Đồng bộ sang Iframe
            sendSyncMessage(key, val);
        };

        // Chuẩn hóa và làm sạch họ tên chỉ khi người dùng đã gõ xong và rời khỏi ô nhập liệu (blur)
        // Không can thiệp lúc đang gõ để tránh xung đột với bộ gõ tiếng Việt Unikey/EVKey
        const handleBlurSanitize = (e) => {
            const target = e.target;
            if (!target || !target.classList) return;

            if (target.classList.contains('mayhen_validate_human_name')) {
                let cleaned = target.value.replace(/[^\p{L}\s]/gu, ''); // Chỉ giữ chữ cái tiếng Việt/Latin và khoảng trắng
                cleaned = cleaned.replace(/\s{2,}/g, ' ').trim(); // Khử khoảng trắng kép và khoảng trắng thừa đầu cuối
                if (cleaned.length > 30) {
                    cleaned = cleaned.slice(0, 30);
                }
                const words = cleaned.split(/\s+/).filter(Boolean);
                if (words.length > 4) {
                    cleaned = words.slice(0, 4).join(' ');
                }
                if (target.value !== cleaned) {
                    target.value = cleaned;
                    const key = target.getAttribute('data-sync-key');
                    if (key) {
                        store.dispatch({
                            type: ActionTypes.UPDATE_FIELD,
                            payload: { key, value: cleaned }
                        });
                        sendSyncMessage(key, cleaned);
                        if (key === 'wedding.groom_name' || key === 'wedding.bride_name') {
                            updateDockNames();
                        }
                    }
                }
            }
        };

        on(mainForm, 'input', handleInputEvent);
        on(mainForm, 'change', handleInputEvent);
        mainForm.addEventListener('blur', handleBlurSanitize, true);

        // Smart Scroll-on-Action: Khi click vào nút chọn/tải ảnh cưới bất kỳ hoặc click vào thumbnail xem trước ảnh
        document.addEventListener('click', (e) => {
            const uploadTrigger = e.target.closest('label[for^="upload_"], .mayhen_editor_upload_btn, .mayhen_editor_file_uploader_wrapper, [data-upload-trigger]');
            if (uploadTrigger) {
                const forAttr = uploadTrigger.getAttribute('for');
                let targetInput = null;
                if (forAttr) {
                    const fileInp = document.getElementById(forAttr);
                    if (fileInp) {
                        const targetInputId = fileInp.getAttribute('data-target-input');
                        if (targetInputId) targetInput = document.getElementById(targetInputId);
                    }
                }
                if (!targetInput) {
                    const fieldBox = uploadTrigger.closest('.mayhen_editor_field_group, .mayhen_editor_field, .mayhen_editor_photo_slot, .mayhen_editor_gallery_item_box');
                    const uploaderInBox = fieldBox?.querySelector('.mayhen_editor_file_uploader');
                    if (uploaderInBox) {
                        const targetInputId = uploaderInBox.getAttribute('data-target-input');
                        if (targetInputId) targetInput = document.getElementById(targetInputId);
                    }
                }
                if (targetInput) {
                    const key = targetInput.getAttribute('data-sync-key') || targetInput.getAttribute('name') || targetInput.id;
                    if (key) {
                        lastScrolledKey = key;
                        sendScrollToTarget(key, 'upload');
                    }
                }
            }

            // Click trực tiếp vào ảnh preview thumbnail bên cột nhập liệu
            const thumbEl = e.target.closest('.mayhen_editor_image_field_preview, [id^="preview_thumb_"]');
            if (thumbEl && thumbEl.id && thumbEl.id.startsWith('preview_thumb_')) {
                const targetId = thumbEl.id.replace('preview_thumb_', '');
                const targetInput = document.getElementById(targetId);
                if (targetInput) {
                    const key = targetInput.getAttribute('data-sync-key') || targetInput.getAttribute('name') || targetInput.id;
                    if (key) {
                        lastScrolledKey = key;
                        sendScrollToTarget(key, 'thumb_click');
                    }
                }
            }
        });
    }

    // Khi Iframe hoàn tất tải, gửi toàn bộ giá trị form, bảng màu và thứ tự bố cục sang iframe
    const syncAllFieldsToIframe = (iframeEl, authoritative = false) => {
        if (!iframeEl || !iframeEl.contentWindow) return;
        const inputs = $$('[data-sync-key]');
        inputs.forEach(inp => {
            const key = inp.getAttribute('data-sync-key');
            const val = inp.value;
            // Giữ nội dung mẫu mặc định, trừ trường rỗng đã được khôi phục có chủ đích từ bản nháp.
            if (key && val !== undefined && val !== null && (String(val).trim() !== '' || restoredFieldKeys.has(key))) {
                // Bảo vệ trường ảnh: CHỈ gửi sang iframe khi là ảnh do người dùng tải lên (data:image/... hoặc link upload cá nhân)
                // Các ảnh tĩnh mẫu thì iframe đã tự render sẵn ảnh mẫu đặc trưng của chính nó, không gửi đè tránh vỡ ảnh
                if (isImageKey(key) && typeof val === 'string') {
                    const isUserUpload = val.startsWith('data:image') || val.includes('/storage/') || val.includes('/uploads/');
                    if (!isUserUpload && !authoritative) {
                        return;
                    }
                }
                sendSyncMessage(key, val);
            }
        });

        const state = store.getState();
        const customConfig = state.custom_config || {};

        // Đồng bộ Bảng màu (Theme Palette)
        if ((customConfig.theme && customConfig.theme.palette_id) || authoritative) {
            dispatchToIframes(
                null,
                [],
                {
                    type: 'MAYHEN_CHANGE_THEME',
                    palette_id: customConfig.theme?.palette_id || '',
                    palette: customConfig.theme || {},
                }
            );
        }

        // Đồng bộ Con dấu sáp (Wax Seal)
        if (customConfig.wax_seal) {
            dispatchToIframes(
                null,
                [],
                {
                    type: 'MAYHEN_UPDATE_WAX_SEAL',
                    wax_seal: customConfig.wax_seal,
                }
            );
        }

        // Đồng bộ Thứ tự Bố cục (Section Order & Hidden)
        if (customConfig.sections_order || customConfig.sections_hidden) {
            dispatchToIframes(
                null,
                [],
                {
                    type: 'MAYHEN_REORDER_SECTIONS',
                    order: customConfig.sections_order || [],
                    sections_hidden: customConfig.sections_hidden || [],
                }
            );
        }

        // Cuộn đến section ban đầu tương ứng với bước đang mở
        const initialTab = document.querySelector(`[data-mayhen-step="${currentStep}"]`);
        const initialSectionId = initialTab ? initialTab.getAttribute('data-section-id') : null;
        if (initialSectionId) {
            sendScrollToSection(initialSectionId, currentStep);
        }
    };

    // Bắt tay 2 chiều: Khi Template Iframe gửi tín hiệu MAYHEN_BRIDGE_READY, lập tức đồng bộ toàn bộ dữ liệu
    window.addEventListener('message', (event) => {
        if (!event.data || typeof event.data !== 'object') return;
        if (event.data.type === 'MAYHEN_BRIDGE_READY') {
            syncAllFieldsToIframe(previewIframe);
            syncAllFieldsToIframe(mobileIframe);
        }
    });

    if (previewIframe) {
        previewIframe.addEventListener('load', () => {
            syncAllFieldsToIframe(previewIframe);
        });
        // Nếu iframe đã hoàn tất tải trước khi JS khởi chạy
        try {
            if (previewIframe.contentDocument && previewIframe.contentDocument.readyState === 'complete') {
                syncAllFieldsToIframe(previewIframe);
            }
        } catch (e) {}
    }

    if (mobileIframe) {
        mobileIframe.addEventListener('load', () => {
            syncAllFieldsToIframe(mobileIframe);
        });
        try {
            if (mobileIframe.contentDocument && mobileIframe.contentDocument.readyState === 'complete') {
                syncAllFieldsToIframe(mobileIframe);
            }
        } catch (e) {}
    }

    // ----------------------------------------------------
    // 3. Asset Image Uploader (Upload trực tiếp vào assets của template)
    // ----------------------------------------------------
    const fileUploaders = $$('.mayhen_editor_file_uploader');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                      document.querySelector('input[name="_token"]')?.value || '';

    // ----------------------------------------------------
    // 3. Quản lý Ảnh: Tự động chuyển đổi 100% data:image, Kiểm tra tỷ lệ 3/4 & Cropper Canvas
    // Không lưu file vào server, không tải file vật lý, tốc độ siêu nhanh
    // ----------------------------------------------------
    let activeCropperTarget = null; // { input, thumb, status, file, img, scale, rotate, offsetX, offsetY }

    /**
     * Tự động nén và chuyển đổi file ảnh thành chuỗi data:image/jpeg;base64 tối ưu
     * Đảm bảo hiển thị sắc nét trên điện thoại, dung lượng gọn nhẹ (< 200KB), không lưu file vào server
     */
    const compressImageToDataUrl = (file, maxWidth = 1200, maxHeight = 1600, quality = 0.85) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    try {
                        let w = img.naturalWidth;
                        let h = img.naturalHeight;

                        // Giới hạn kích thước tối đa giữ nguyên tỷ lệ
                        if (w > maxWidth || h > maxHeight) {
                            const ratio = Math.min(maxWidth / w, maxHeight / h);
                            w = Math.round(w * ratio);
                            h = Math.round(h * ratio);
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);

                        const dataUrl = canvas.toDataURL('image/jpeg', quality);
                        resolve(dataUrl);
                    } catch (error) {
                        reject(error);
                    }
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    };

    const cropperModal = $('#mayhen_cropper_modal');
    const cropperCanvas = $('#mayhen_cropper_canvas');
    const cropperStage = $('#mayhen_cropper_stage');
    const cropperOverlay = $('#mayhen_cropper_overlay');
    const cropperCloseBtn = $('#mayhen_cropper_close_btn');
    const cropperCancelBtn = $('#mayhen_cropper_btn_cancel');
    const cropperConfirmBtn = $('#mayhen_cropper_btn_confirm');
    const cropperZoomIn = $('#mayhen_cropper_zoom_in');
    const cropperZoomOut = $('#mayhen_cropper_zoom_out');
    const cropperRotateLeft = $('#mayhen_cropper_rotate_left');
    const cropperRotateRight = $('#mayhen_cropper_rotate_right');
    const cropperReset = $('#mayhen_cropper_reset');

    let cropperCtx = cropperCanvas ? cropperCanvas.getContext('2d') : null;
    let isDraggingStage = false;
    let startDragX = 0;
    let startDragY = 0;

    const getCropperDimensions = () => {
        const stageW = (cropperStage && cropperStage.clientWidth) ? cropperStage.clientWidth : 580;
        const stageH = (cropperStage && cropperStage.clientHeight) ? cropperStage.clientHeight : 380;
        const cropBoxH = Math.min(stageH - 40, 310);
        const cropBoxW = Math.round((cropBoxH * 3) / 4);
        const cropBoxX = Math.round((stageW - cropBoxW) / 2);
        const cropBoxY = Math.round((stageH - cropBoxH) / 2);

        return { stageW, stageH, cropBoxW, cropBoxH, cropBoxX, cropBoxY };
    };

    const renderCropper = () => {
        if (!activeCropperTarget || !activeCropperTarget.img || !cropperCtx || !cropperCanvas) return;
        const img = activeCropperTarget.img;
        const { stageW, stageH, cropBoxW, cropBoxH, cropBoxX, cropBoxY } = getCropperDimensions();

        if (cropperCanvas.width !== stageW || cropperCanvas.height !== stageH) {
            cropperCanvas.width = stageW;
            cropperCanvas.height = stageH;
        }

        cropperCtx.clearRect(0, 0, stageW, stageH);
        cropperCtx.save();
        cropperCtx.translate(stageW / 2 + activeCropperTarget.offsetX, stageH / 2 + activeCropperTarget.offsetY);
        cropperCtx.rotate((activeCropperTarget.rotate * Math.PI) / 180);
        cropperCtx.scale(activeCropperTarget.scale, activeCropperTarget.scale);

        cropperCtx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2);
        cropperCtx.restore();

        // Kích thước khung 3:4 ở chính giữa
        if (cropperOverlay) {
            cropperOverlay.style.width = cropBoxW + 'px';
            cropperOverlay.style.height = cropBoxH + 'px';
            cropperOverlay.style.left = cropBoxX + 'px';
            cropperOverlay.style.top = cropBoxY + 'px';
        }
    };

    const openCropperModal = (imageSource, targetObj) => {
        const img = new Image();
        img.onload = () => {
            if (!store.isCurrentMedia(targetObj.mediaKey, targetObj.mediaToken)) return;
            activeCropperTarget = {
                ...targetObj,
                img: img,
                scale: 1,
                rotate: 0,
                offsetX: 0,
                offsetY: 0,
                confirmed: false,
            };

            if (cropperModal) {
                cropperModal.classList.add('mayhen_editor_modal_open');
                document.body.style.overflow = 'hidden';
            }

            requestAnimationFrame(() => {
                const { cropBoxW, cropBoxH } = getCropperDimensions();
                const minScaleW = cropBoxW / img.naturalWidth;
                const minScaleH = cropBoxH / img.naturalHeight;
                activeCropperTarget.scale = Math.max(minScaleW, minScaleH, 0.35);
                renderCropper();
            });
        };
        img.onerror = () => {
            store.finishMedia(targetObj.mediaKey, targetObj.mediaToken);
            URL.revokeObjectURL(imageSource);
        };
        img.src = imageSource;
    };

    const closeCropperModal = () => {
        if (cropperModal) cropperModal.classList.remove('mayhen_editor_modal_open');
        document.body.style.overflow = '';

        // Nếu người dùng đóng mà chưa xác nhận cắt ảnh, không cho phép lưu ảnh chưa chuẩn 3/4
        if (activeCropperTarget && !activeCropperTarget.confirmed) {
            if (activeCropperTarget.uploader) {
                activeCropperTarget.uploader.value = '';
            }
            if (activeCropperTarget.status) {
                activeCropperTarget.status.textContent = 'Đã hủy chọn ảnh';
                activeCropperTarget.status.style.color = 'var(--mayhen-color-text-muted)';
            }
        }
        if (activeCropperTarget) {
            store.finishMedia(activeCropperTarget.mediaKey, activeCropperTarget.mediaToken);
            URL.revokeObjectURL(activeCropperTarget.objectUrl);
        }
        activeCropperTarget = null;
    };

    if (cropperCloseBtn) on(cropperCloseBtn, 'click', closeCropperModal);
    if (cropperCancelBtn) on(cropperCancelBtn, 'click', closeCropperModal);

    if (cropperZoomIn) on(cropperZoomIn, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.scale = Math.min(4, activeCropperTarget.scale * 1.15);
        renderCropper();
    });

    if (cropperZoomOut) on(cropperZoomOut, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.scale = Math.max(0.1, activeCropperTarget.scale / 1.15);
        renderCropper();
    });

    if (cropperRotateLeft) on(cropperRotateLeft, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.rotate = (activeCropperTarget.rotate - 90) % 360;
        renderCropper();
    });

    if (cropperRotateRight) on(cropperRotateRight, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.rotate = (activeCropperTarget.rotate + 90) % 360;
        renderCropper();
    });

    if (cropperReset) on(cropperReset, 'click', () => {
        if (!activeCropperTarget || !activeCropperTarget.img) return;
        const { cropBoxW, cropBoxH } = getCropperDimensions();
        activeCropperTarget.scale = Math.max(cropBoxW / activeCropperTarget.img.naturalWidth, cropBoxH / activeCropperTarget.img.naturalHeight, 0.35);
        activeCropperTarget.rotate = 0;
        activeCropperTarget.offsetX = 0;
        activeCropperTarget.offsetY = 0;
        renderCropper();
    });

    // Kéo di chuyển ảnh trong khung cắt (hỗ trợ cả chuột và cảm ứng điện thoại, lăn chuột zoom)
    if (cropperStage) {
        const handleDragStart = (x, y) => {
            isDraggingStage = true;
            startDragX = x;
            startDragY = y;
        };

        const handleDragMove = (x, y) => {
            if (!isDraggingStage || !activeCropperTarget) return;
            const dx = x - startDragX;
            const dy = y - startDragY;
            startDragX = x;
            startDragY = y;

            activeCropperTarget.offsetX += dx;
            activeCropperTarget.offsetY += dy;
            renderCropper();
        };

        const handleDragEnd = () => {
            isDraggingStage = false;
        };

        // Chuột
        on(cropperStage, 'mousedown', (e) => handleDragStart(e.clientX, e.clientY));
        window.addEventListener('mousemove', (e) => handleDragMove(e.clientX, e.clientY));
        window.addEventListener('mouseup', handleDragEnd);

        // Cảm ứng điện thoại
        cropperStage.addEventListener('touchstart', (e) => {
            if (e.touches && e.touches.length === 1) {
                handleDragStart(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });

        window.addEventListener('touchmove', (e) => {
            if (e.touches && e.touches.length === 1) {
                handleDragMove(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });

        window.addEventListener('touchend', handleDragEnd);

        // Lăn chuột để phóng to / thu nhỏ
        cropperStage.addEventListener('wheel', (e) => {
            e.preventDefault();
            if (!activeCropperTarget) return;
            const delta = e.deltaY < 0 ? 1.08 : 0.92;
            activeCropperTarget.scale = Math.max(0.1, Math.min(4, activeCropperTarget.scale * delta));
            renderCropper();
        }, { passive: false });
    }

    // Xác nhận cắt ảnh và xuất ra đúng tỷ lệ 3/4 dạng data:image chất lượng cao
    if (cropperConfirmBtn) {
        on(cropperConfirmBtn, 'click', () => {
            if (!activeCropperTarget || !activeCropperTarget.img) return;
            if (!store.isCurrentMedia(activeCropperTarget.mediaKey, activeCropperTarget.mediaToken)) return;
            try {

                const { cropBoxW } = getCropperDimensions();

                // Xuất ra canvas chất lượng cao độ phân giải chuẩn 3/4: 900x1200
                const outW = 900;
                const outH = 1200;
                const outCanvas = document.createElement('canvas');
                outCanvas.width = outW;
                outCanvas.height = outH;
                const outCtx = outCanvas.getContext('2d');

                const scaleFactor = outW / cropBoxW; // Phóng to theo tỷ lệ khung cắt thực tế

                outCtx.save();
                outCtx.translate(outW / 2 + activeCropperTarget.offsetX * scaleFactor, outH / 2 + activeCropperTarget.offsetY * scaleFactor);
                outCtx.rotate((activeCropperTarget.rotate * Math.PI) / 180);
                outCtx.scale(activeCropperTarget.scale * scaleFactor, activeCropperTarget.scale * scaleFactor);
                outCtx.drawImage(activeCropperTarget.img, -activeCropperTarget.img.naturalWidth / 2, -activeCropperTarget.img.naturalHeight / 2);
                outCtx.restore();

                // Lấy chuỗi data:image/jpeg trực tiếp, không lưu file vào server
                const dataUrl = outCanvas.toDataURL('image/jpeg', 0.88);
                const targetInput = activeCropperTarget.input;
                const targetThumb = activeCropperTarget.thumb;
                const statusEl = activeCropperTarget.status;

                if (targetThumb) targetThumb.src = dataUrl;
                if (targetInput) {
                    targetInput.value = dataUrl;
                    const syncKey = targetInput.getAttribute('data-sync-key');
                    if (syncKey) {
                        mirrorWeddingInput(mainForm, targetInput);
                        store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: syncKey, value: dataUrl } });
                        sendSyncMessage(syncKey, dataUrl);
                        sendScrollToTarget(syncKey, 'uploaded');
                    }
                }

                if (statusEl) {
                    statusEl.textContent = '✓ Tải ảnh thành công';
                    statusEl.style.color = '#10B981';
                }

                activeCropperTarget.confirmed = true;
                closeCropperModal();
            } catch (error) {
                console.error('Không thể cắt ảnh:', error);
                closeCropperModal();
            }
        });
    }

    // Sự kiện khi người dùng chọn file ảnh từ máy
    fileUploaders.forEach(uploader => {
        on(uploader, 'change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            const targetInputId = uploader.getAttribute('data-target-input');
            const targetThumbId = uploader.getAttribute('data-target-thumb');
            const isMainPhoto = uploader.getAttribute('data-is-main-photo') === '1';
            const targetInput = document.getElementById(targetInputId);
            const targetThumb = document.getElementById(targetThumbId);
            const statusEl = document.getElementById('status_' + targetInputId);
            const mediaKey = targetInputId;
            const mediaToken = store.beginMedia(mediaKey);
            const isCurrent = () => store.isCurrentMedia(mediaKey, mediaToken);
            const finish = () => {
                store.finishMedia(mediaKey, mediaToken);
                URL.revokeObjectURL(objectUrl);
            };

            // Tự động cuộn Iframe xem trước đến đúng vị trí ảnh ngay khi người dùng chọn file
            if (targetInput) {
                const syncKey = targetInput.getAttribute('data-sync-key');
                if (syncKey) {
                    sendScrollToTarget(syncKey, 'upload');
                }
            }

            if (statusEl) {
                statusEl.textContent = 'Đang tải ảnh...';
                statusEl.style.color = 'var(--mayhen-color-text-muted)';
            }

            const objectUrl = URL.createObjectURL(file);

            // Kiểm tra kích thước và tỷ lệ ảnh
            const tempImg = new Image();
            tempImg.onload = async () => {
                if (!isCurrent()) { finish(); return; }
                const ratio = tempImg.naturalWidth / tempImg.naturalHeight;
                // Chuẩn 3/4 = 0.75. Ngưỡng dung sai cực nhỏ 0.02
                const isExactThreeFour = Math.abs(ratio - 0.75) <= 0.02;

                // Quy tắc: Không tin tưởng người dùng.
                // Nếu là ảnh bìa chính (bắt buộc 3/4) mà tỷ lệ chưa chuẩn 3/4 -> BẮT BUỘC BẬT POPUP CẮT ẢNH NGAY
                if (isMainPhoto && !isExactThreeFour) {
                    if (statusEl) {
                        statusEl.textContent = 'Đang mở căn chỉnh...';
                        statusEl.style.color = '#9C7A4A';
                    }
                    openCropperModal(objectUrl, {
                        input: targetInput,
                        thumb: targetThumb,
                        status: statusEl,
                        file: file,
                        uploader: uploader,
                        mediaKey, mediaToken, objectUrl,
                    });
                } else {
                    // Ảnh đã chuẩn 3/4 hoặc là ảnh gallery không bắt buộc -> Tự động nén sang data:image trực tiếp
                    try {
                        if (statusEl) statusEl.textContent = 'Đang tải ảnh...';
                        const dataUrl = await compressImageToDataUrl(file);
                        if (!isCurrent()) return;
                        if (targetThumb) targetThumb.src = dataUrl;
                        if (targetInput) {
                            targetInput.value = dataUrl;
                            const syncKey = targetInput.getAttribute('data-sync-key');
                            if (syncKey) {
                                mirrorWeddingInput(mainForm, targetInput);
                                store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: syncKey, value: dataUrl } });
                                sendSyncMessage(syncKey, dataUrl);
                                sendScrollToTarget(syncKey, 'uploaded');
                            }
                        }
                        if (statusEl) {
                            statusEl.textContent = '✓ Tải ảnh thành công';
                            statusEl.style.color = '#10B981';
                        }
                    } catch (err) {
                        if (!isCurrent()) return;
                        console.error('Lỗi chuyển ảnh sang data:image:', err);
                        if (statusEl) {
                            statusEl.textContent = 'Không thể tải ảnh này';
                            statusEl.style.color = '#EF4444';
                        }
                    } finally {
                        finish();
                    }
                }
            };
            tempImg.onerror = () => {
                finish();
                if (!isCurrent()) return;
                if (statusEl) {
                    statusEl.textContent = 'Không thể đọc tệp ảnh';
                    statusEl.style.color = '#EF4444';
                }
            };
            tempImg.src = objectUrl;
        });
    });

    // ----------------------------------------------------
    // 3.1 Tự động tạo mã VietQR Ngân Hàng An Toàn Tuyệt Đối
    // ----------------------------------------------------
    const qrRequestTokens = { groom_qr: 0, bride_qr: 0 };
    const qrUpdateTimers = new Map();
    const nameNormalizeTimers = new Map();

    /**
     * Chuẩn hóa họ tên tiếng Việt: chuyển thành chữ HOA KHÔNG DẤU, loại bỏ ký tự lạ.
     */
    const normalizeVietnameseName = (str) => {
        if (!str || typeof str !== 'string') return '';
        let res = str;

        res = res.replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a');
        res = res.replace(/[èéẹẻẽêềếệểễ]/g, 'e');
        res = res.replace(/[ìíịỉĩ]/g, 'i');
        res = res.replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o');
        res = res.replace(/[ùúụủũưừứựửữ]/g, 'u');
        res = res.replace(/[ỳýỵỷỹ]/g, 'y');
        res = res.replace(/đ/g, 'd');
        res = res.replace(/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/g, 'A');
        res = res.replace(/[ÈÉẸẺẼÊỀẾỆỂỄ]/g, 'E');
        res = res.replace(/[ÌÍỊỈĨ]/g, 'I');
        res = res.replace(/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/g, 'O');
        res = res.replace(/[ÙÚỤỦŨƯỪỨỰỬỮ]/g, 'U');
        res = res.replace(/[ỲÝỴỶỸ]/g, 'Y');
        res = res.replace(/Đ/g, 'D');

        res = res.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        res = res.replace(/[^a-zA-Z0-9\s]/g, ' ');
        res = res.replace(/\s+/g, ' ').trim();

        return res.toUpperCase();
    };

    /**
     * Chuyển mã nguồn SVG từ API VietQR thành Data URL an toàn trên mọi trình duyệt.
     * Khắc phục triệt để lỗi createImageBitmap không hỗ trợ SVG blob.
     */
    const renderSvgToDataUrl = (svgText) => {
        return new Promise((resolve) => {
            let fallbackDataUrl = '';
            try {
                fallbackDataUrl = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgText)));
            } catch (e) {
                fallbackDataUrl = 'data:image/svg+xml;utf8,' + encodeURIComponent(svgText);
            }

            const svgBlob = new Blob([svgText], { type: 'image/svg+xml;charset=utf-8' });
            const blobUrl = URL.createObjectURL(svgBlob);
            const img = new Image();

            img.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth || 600;
                    canvas.height = img.naturalHeight || 600;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    const pngData = canvas.toDataURL('image/png');
                    URL.revokeObjectURL(blobUrl);
                    resolve(pngData);
                } catch (err) {
                    URL.revokeObjectURL(blobUrl);
                    resolve(fallbackDataUrl);
                }
            };

            img.onerror = () => {
                URL.revokeObjectURL(blobUrl);
                resolve(fallbackDataUrl);
            };

            img.src = blobUrl;
        });
    };

    const updateVietQr = async (side) => {
        const bankSelect = $(`.mayhen_vietqr_bank_select[data-qr-side="${side}"]`);
        const accInput = $(`.mayhen_vietqr_acc_input[data-qr-side="${side}"]`);
        const nameInput = $(`.mayhen_vietqr_name_input[data-qr-side="${side}"]`);

        if (!bankSelect || !accInput) return;

        const bankCode = bankSelect.value.trim();
        const accountNo = accInput.value.trim();
        const rawName = nameInput ? nameInput.value.trim() : '';
        const accountName = normalizeVietnameseName(rawName);

        const targetInputId = bankSelect.getAttribute('data-target-input');
        const targetInput = document.getElementById(targetInputId);
        const targetThumb = document.getElementById('preview_thumb_' + targetInputId);
        const statusEl = document.getElementById('status_' + targetInputId);
        const requestToken = ++qrRequestTokens[side];

        if (!bankCode || !accountNo) {
            if (statusEl) {
                statusEl.textContent = 'Vui lòng chọn ngân hàng và số tài khoản';
                statusEl.style.color = '#F59E0B';
            }
            return;
        }

        if (statusEl) {
            statusEl.textContent = 'Đang tạo mã VietQR...';
            statusEl.style.color = '#3B82F6';
        }

        let qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNo}-compact2.svg`;
        if (accountName) {
            qrUrl += `?accountName=${encodeURIComponent(accountName)}`;
        }

        try {
            const response = await fetch(qrUrl, { mode: 'cors' });
            if (!response.ok) throw new Error('Không thể tải mã VietQR');
            if (requestToken !== qrRequestTokens[side]) return;

            const svgText = await response.text();
            if (!svgText || !svgText.includes('<svg')) {
                throw new Error('Định dạng ảnh VietQR không hợp lệ');
            }

            const localQrData = await renderSvgToDataUrl(svgText);
            if (requestToken !== qrRequestTokens[side]) return;

            if (statusEl) {
                statusEl.textContent = '✓ Đã tạo mã VietQR thành công';
                statusEl.style.color = '#10B981';
            }

            if (targetThumb) targetThumb.src = localQrData;
            if (targetInput) {
                targetInput.value = localQrData;
                const syncKey = targetInput.getAttribute('data-sync-key');
                if (syncKey) {
                    store.dispatch({
                        type: ActionTypes.UPDATE_FIELD,
                        payload: { key: syncKey, value: localQrData }
                    });
                    sendSyncMessage(syncKey, localQrData);
                    sendScrollToTarget(syncKey, 'bank');
                }
            }
        } catch (error) {
            if (requestToken !== qrRequestTokens[side]) return;
            console.error('Không thể tạo ảnh QR cục bộ:', error);
            if (statusEl) {
                statusEl.textContent = 'Không tạo được QR. Kiểm tra ngân hàng và số tài khoản.';
                statusEl.style.color = '#EF4444';
            }
        }
    };

    const syncVietQrHiddenField = (el) => {
        const targetBankId = el.getAttribute('data-target-bank');
        const targetAccId = el.getAttribute('data-target-acc');
        const targetNameId = el.getAttribute('data-target-name');

        if (targetBankId && el.tagName === 'SELECT') {
            const targetEl = document.getElementById(targetBankId);
            if (targetEl) {
                const selVal = el.selectedIndex > 0 ? (el.options[el.selectedIndex].text.split(' - ')[0] || el.value) : '';
                targetEl.value = selVal;
                const syncKey = targetEl.getAttribute('data-sync-key');
                if (syncKey) {
                    store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: syncKey, value: selVal } });
                    sendSyncMessage(syncKey, selVal);
                }
            }
        } else if (targetAccId) {
            const targetEl = document.getElementById(targetAccId);
            if (targetEl) {
                targetEl.value = el.value.trim();
                const syncKey = targetEl.getAttribute('data-sync-key');
                if (syncKey) {
                    store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: syncKey, value: el.value.trim() } });
                    sendSyncMessage(syncKey, el.value.trim());
                }
            }
        } else if (targetNameId) {
            const targetEl = document.getElementById(targetNameId);
            if (targetEl) {
                const valNorm = normalizeVietnameseName(el.value);
                targetEl.value = valNorm;
                const syncKey = targetEl.getAttribute('data-sync-key');
                if (syncKey) {
                    store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: syncKey, value: valNorm } });
                    sendSyncMessage(syncKey, valNorm);
                }
            }
        }
    };

    // Lắng nghe sự kiện cho Ngân hàng và Số tài khoản
    $$('.mayhen_vietqr_bank_select, .mayhen_vietqr_acc_input').forEach(el => {
        const updateQrAfterPause = () => {
            syncVietQrHiddenField(el);
            const side = el.getAttribute('data-qr-side');
            if (!side) return;
            window.clearTimeout(qrUpdateTimers.get(side));
            qrUpdateTimers.set(side, window.setTimeout(() => updateVietQr(side), 500));
        };
        if (el.tagName === 'SELECT') {
            on(el, 'change', updateQrAfterPause);
        } else {
            on(el, 'input', updateQrAfterPause);
            on(el, 'change', updateQrAfterPause);
        }
    });

    // Lắng nghe sự kiện cho Tên chủ tài khoản: chuẩn hóa tự động sau 5s nếu chưa không dấu viết hoa
    $$('.mayhen_vietqr_name_input').forEach(nameInput => {
        const side = nameInput.getAttribute('data-qr-side');

        const applyNameNormalization = (forceUpdateQr = true) => {
            const curVal = nameInput.value;
            if (!curVal) return;
            const normalized = normalizeVietnameseName(curVal);
            // Nếu chưa viết hoa không dấu thì mới tự động chuyển đổi
            if (curVal !== normalized) {
                nameInput.value = normalized;
                syncVietQrHiddenField(nameInput);
                if (forceUpdateQr && side) {
                    window.clearTimeout(qrUpdateTimers.get(side));
                    updateVietQr(side);
                }
            }
        };

        on(nameInput, 'input', () => {
            syncVietQrHiddenField(nameInput);
            if (!side) return;

            if (nameNormalizeTimers.has(side)) {
                window.clearTimeout(nameNormalizeTimers.get(side));
            }

            const curVal = nameInput.value;
            const normalized = normalizeVietnameseName(curVal);

            // Nếu đã không dấu và viết hoa rồi thì thôi, tạo VietQR sau 500ms
            if (curVal === normalized) {
                window.clearTimeout(qrUpdateTimers.get(side));
                qrUpdateTimers.set(side, window.setTimeout(() => updateVietQr(side), 500));
            } else {
                // Nếu chưa chuẩn hóa: tự chuẩn hóa khi nhập xong sau đúng 5s
                const timer = window.setTimeout(() => {
                    applyNameNormalization(true);
                }, 5000);
                nameNormalizeTimers.set(side, timer);
            }
        });

        // Khi blur khỏi ô tên: chuẩn hóa ngay lập tức
        on(nameInput, 'blur', () => {
            if (nameNormalizeTimers.has(side)) {
                window.clearTimeout(nameNormalizeTimers.get(side));
            }
            applyNameNormalization(true);
        });
    });

    $$('.mayhen_btn_gen_vietqr').forEach(btn => {
        on(btn, 'click', () => {
            const side = btn.getAttribute('data-qr-side');
            if (side) {
                const bankSelect = $(`.mayhen_vietqr_bank_select[data-qr-side="${side}"]`);
                const accInput = $(`.mayhen_vietqr_acc_input[data-qr-side="${side}"]`);
                const nameInput = $(`.mayhen_vietqr_name_input[data-qr-side="${side}"]`);

                if (nameInput) {
                    const curVal = nameInput.value;
                    const normalized = normalizeVietnameseName(curVal);
                    if (curVal !== normalized) {
                        nameInput.value = normalized;
                    }
                    syncVietQrHiddenField(nameInput);
                }
                if (bankSelect) syncVietQrHiddenField(bankSelect);
                if (accInput) syncVietQrHiddenField(accInput);

                window.clearTimeout(qrUpdateTimers.get(side));
                updateVietQr(side);
            }
        });
    });

    // Xử lý tải ảnh QR cá nhân có sẵn từ thiết bị
    $$('.mayhen_vietqr_file_input').forEach(fileInput => {
        on(fileInput, 'change', () => {
            const file = fileInput.files && fileInput.files[0];
            if (!file) return;

            const targetInputId = fileInput.getAttribute('data-target-input');
            const targetInput = document.getElementById(targetInputId);
            const targetThumb = document.getElementById('preview_thumb_' + targetInputId);
            const statusEl = document.getElementById('status_' + targetInputId);

            if (file.size > 10 * 1024 * 1024) {
                if (statusEl) {
                    statusEl.textContent = 'Tệp quá lớn, vui lòng chọn ảnh dưới 10MB';
                    statusEl.style.color = '#EF4444';
                }
                return;
            }

            const mediaToken = store.beginMedia(targetInputId);
            const isCurrent = () => store.isCurrentMedia(targetInputId, mediaToken);
            const finish = () => store.finishMedia(targetInputId, mediaToken);
            const fail = () => {
                if (isCurrent() && statusEl) statusEl.textContent = 'Không thể đọc tệp ảnh QR';
                finish();
            };
            const reader = new FileReader();
            reader.onerror = fail;
            reader.onabort = fail;
            reader.onload = (e) => {
                if (!isCurrent()) return;
                const img = new Image();
                img.onerror = fail;
                img.onload = () => {
                    if (!isCurrent()) return;
                    try {
                        const canvas = document.createElement('canvas');
                        const maxDim = 800;
                        let w = img.width;
                        let h = img.height;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) {
                                h = Math.round((h * maxDim) / w);
                                w = maxDim;
                            } else {
                                w = Math.round((w * maxDim) / h);
                                h = maxDim;
                            }
                        }
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        const base64Data = canvas.toDataURL('image/png', 0.92);

                        if (targetThumb) targetThumb.src = base64Data;
                        if (targetInput) {
                            targetInput.value = base64Data;
                            const syncKey = targetInput.getAttribute('data-sync-key');
                            if (syncKey) {
                                store.dispatch({
                                    type: ActionTypes.UPDATE_FIELD,
                                    payload: { key: syncKey, value: base64Data }
                                });
                                sendSyncMessage(syncKey, base64Data);
                            }
                        }
                        if (statusEl) {
                            statusEl.textContent = '✓ Đã tải ảnh QR từ thiết bị';
                            statusEl.style.color = '#10B981';
                        }
                    } catch (error) {
                        fail();
                    } finally {
                        finish();
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    });

    // ----------------------------------------------------
    // 3.2 Modal Chọn Nhạc Cưới Lãng Mạn Bản Quyền
    // ----------------------------------------------------
    const musicModal = $('#mayhen_music_picker_modal');
    const musicCloseBtn = $('#mayhen_music_close_btn');
    const musicSearchInput = $('#mayhen_music_search_input');
    const musicListContainer = $('#mayhen_music_list');
    const musicPreviewAudio = $('#mayhen_music_preview_audio');
    let musicSearchDebounce = null;
    let currentPlayingBtn = null;

    const openMusicModal = () => {
        if (musicModal) {
            musicModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
            fetchMusics('');
        }
    };

    const closeMusicModal = () => {
        if (musicModal) musicModal.classList.remove('mayhen_editor_modal_open');
        document.body.style.overflow = '';
        if (musicPreviewAudio) {
            musicPreviewAudio.pause();
            if (currentPlayingBtn) currentPlayingBtn.classList.remove('mayhen_playing');
        }
    };

    const fetchMusics = async (query = '') => {
        if (!musicListContainer) return;
        musicListContainer.innerHTML = '<p style="text-align:center;padding:24px;color:var(--mayhen-color-text-muted);font-style:italic;">Đang tìm kiếm giai điệu...</p>';

        try {
            const res = await fetch(`/api/wedding-musics?q=${encodeURIComponent(query)}`);
            const json = await res.json();
            const list = json.data || [];

            if (list.length === 0) {
                musicListContainer.innerHTML = '<p style="text-align:center;padding:24px;color:var(--mayhen-color-text-muted);">Không tìm thấy bài hát nào phù hợp.</p>';
                return;
            }

            musicListContainer.innerHTML = '';
            list.forEach(song => {
                const item = document.createElement('div');
                item.className = 'mayhen_music_item';
                item.innerHTML = `
                    <div class="mayhen_music_info">
                        <button type="button" class="mayhen_music_play_btn" data-audio-url="${song.url}" title="Nghe thử">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                        <div class="mayhen_music_meta">
                            <div class="mayhen_music_title">${song.title}</div>
                            <div class="mayhen_music_artist">Ca sĩ / Tác giả: <strong>${song.artist}</strong></div>
                        </div>
                    </div>
                    <button type="button" class="mayhen_music_select_btn" data-audio-url="${song.url}" data-audio-title="${song.title} - ${song.artist}">
                        Chọn bài này
                    </button>
                `;
                musicListContainer.appendChild(item);
            });

            // Gắn sự kiện nghe thử và chọn bài
            musicListContainer.querySelectorAll('.mayhen_music_play_btn').forEach(btn => {
                on(btn, 'click', () => {
                    const audioUrl = btn.getAttribute('data-audio-url');
                    if (musicPreviewAudio.src === audioUrl && !musicPreviewAudio.paused) {
                        musicPreviewAudio.pause();
                        btn.classList.remove('mayhen_playing');
                    } else {
                        if (currentPlayingBtn) currentPlayingBtn.classList.remove('mayhen_playing');
                        musicPreviewAudio.src = audioUrl;
                        musicPreviewAudio.play();
                        btn.classList.add('mayhen_playing');
                        currentPlayingBtn = btn;
                    }
                });
            });

            musicListContainer.querySelectorAll('.mayhen_music_select_btn').forEach(btn => {
                on(btn, 'click', () => {
                    const audioUrl = btn.getAttribute('data-audio-url');
                    const audioTitle = btn.getAttribute('data-audio-title') || btn.closest('.mayhen_music_item')?.querySelector('.mayhen_music_title')?.textContent || 'Bài hát cưới đã chọn';
                    let musicInput = document.getElementById('wedding_music_url') || $('[data-sync-key="wedding.music_url"]');
                    if (!musicInput) {
                        const form = document.getElementById('mayhen_editor_form');
                        if (form) {
                            musicInput = document.createElement('input');
                            musicInput.type = 'hidden';
                            musicInput.name = 'wedding[music_url]';
                            musicInput.id = 'wedding_music_url';
                            musicInput.setAttribute('data-sync-key', 'wedding.music_url');
                            form.appendChild(musicInput);
                        }
                    }
                    if (musicInput) {
                        musicInput.value = audioUrl;
                        mirrorWeddingInput(mainForm, musicInput);
                        store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: 'wedding.music_url', value: audioUrl } });
                    }
                    sendSyncMessage('wedding.music_url', audioUrl);
                    sendSyncMessage('music_url', audioUrl);
                    const activeTitle = $('#mayhen_active_music_title');
                    if (activeTitle) {
                        activeTitle.textContent = audioTitle;
                    }
                    closeMusicModal();
                });
            });
        } catch {
            musicListContainer.innerHTML = '<p style="text-align:center;padding:24px;color:var(--mayhen-color-danger);">Không thể kết nối đến kho nhạc.</p>';
        }
    };

    if (musicCloseBtn) on(musicCloseBtn, 'click', closeMusicModal);
    if (musicSearchInput) {
        on(musicSearchInput, 'input', (e) => {
            clearTimeout(musicSearchDebounce);
            musicSearchDebounce = setTimeout(() => {
                fetchMusics(e.target.value.trim());
            }, 250);
        });
    }

    $$('[data-mayhen-action="open-music-modal"]').forEach(btn => {
        on(btn, 'click', openMusicModal);
    });

    // ----------------------------------------------------
    // 3.3 Submit Form: Toàn bộ ảnh đã được lưu trực tiếp dạng data:image, submit tức thì
    // ----------------------------------------------------
    const submitFormWithPendingUploads = async () => {
        if (store.pendingMedia.size) {
            window.alert('Ảnh đang được xử lý. Vui lòng đợi ảnh hoàn tất trước khi xuất bản.');
            return;
        }
        if (store.autosaver.hasConflict) {
            window.alert('Thiệp đang có xung đột phiên làm việc. Hãy giữ lại bản nháp và kiểm tra trước khi xuất bản.');
            return;
        }
        if (store.autosaver.isSaving) {
            window.alert('Thiệp đang được lưu. Vui lòng đợi lưu xong rồi thử lại.');
            return;
        }
        const submitBtns = $$('[data-mayhen-action="trigger-submit"]');
        submitBtns.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<span>Đang xuất bản thiệp cưới...</span>';
        });

        if (mainForm) {
            // Cập nhật custom_config (bố cục, bảng màu, tem sáp) mới nhất vào input form
            const customConfigInput = document.getElementById('invitation_custom_config');
            if (customConfigInput) {
                customConfigInput.value = JSON.stringify(store.getState().custom_config || {});
            }

            // Đảm bảo template_id gửi lên khớp với mẫu hiện tại trong store
            const currentTemplateId = store.getState().template_id;
            const templateInput = document.getElementById('template_id');
            if (templateInput && currentTemplateId) {
                templateInput.value = currentTemplateId;
            }

            const revisionInput = document.getElementById('invitation_revision_id');
            if (revisionInput) {
                revisionInput.value = String(store.getState().revision_id);
            }

            clearTimeout(store.autosaver.timer);
            store.autosaver.timer = null;
            store.autosaver.isNavigating = true;
            store.autosaver.saveToLocalStorage(store.getState());
            mainForm.submit();
        }
    };

    const submitTriggers = $$('[data-mayhen-action="trigger-submit"]');
    submitTriggers.forEach(btn => {
        on(btn, 'click', (e) => {
            e.preventDefault();
            submitFormWithPendingUploads();
        });
    });

    // ----------------------------------------------------
    // 4. Mobile Fullscreen Preview Modal
    // ----------------------------------------------------
    const previewModal = $('#mayhen_mobile_preview_modal');
    const openPreviewBtns = $$('[data-mayhen-action="open-preview-modal"]');
    const closePreviewBtns = $$('[data-mayhen-action="close-preview-modal"]');

    openPreviewBtns.forEach(btn => {
        on(btn, 'click', () => {
            if (previewModal) {
                previewModal.classList.add('mayhen_editor_modal_open');
                document.body.style.overflow = 'hidden';
                if (mobileIframe) {
                    syncAllFieldsToIframe(mobileIframe);
                }
            }
        });
    });

    closePreviewBtns.forEach(btn => {
        on(btn, 'click', () => {
            if (previewModal) {
                previewModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    });

    if (previewModal) {
        on(previewModal, 'click', (e) => {
            if (e.target === previewModal) {
                previewModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    }

    // ----------------------------------------------------
    // 5. Template Switcher Modal (Đổi Mẫu Nhanh)
    // ----------------------------------------------------
    const templateModal = $('#mayhen_template_switcher_modal');
    const openTemplateBtns = $$('[data-mayhen-action="open-template-modal"]');
    const closeTemplateBtns = $$('[data-mayhen-action="close-template-modal"]');

    openTemplateBtns.forEach(btn => {
        on(btn, 'click', () => {
            if (templateModal) {
                templateModal.classList.add('mayhen_editor_modal_open');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    closeTemplateBtns.forEach(btn => {
        on(btn, 'click', () => {
            if (templateModal) {
                templateModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    });

    if (templateModal) {
        on(templateModal, 'click', (e) => {
            if (e.target === templateModal) {
                templateModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    }

    // ----------------------------------------------------
    // 6. Mobile Bottom Navigation Drawer Handler
    // ----------------------------------------------------
    const mobileBottomBtns = $$('.mayhen_editor_mobile_bottom_btn');
    const drawerPanel = $('#mayhen_drawer_panel');
    const mobileCloseDrawerBtn = $('#mayhen_mobile_close_drawer_btn');

    mobileBottomBtns.forEach(btn => {
        on(btn, 'click', () => {
            const tabName = btn.getAttribute('data-mobile-tab');
            if (tabName === 'preview') {
                if (previewModal) {
                    previewModal.classList.add('mayhen_editor_modal_open');
                    document.body.style.overflow = 'hidden';
                    if (mobileIframe) syncAllFieldsToIframe(mobileIframe);
                }
                return;
            }

            mobileBottomBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (drawerPanel) {
                drawerPanel.classList.add('is-open');
            }

            subpanels.forEach(p => {
                if (p.getAttribute('data-panel-name') === tabName) {
                    p.style.display = tabName === 'content' ? 'flex' : 'block';
                } else {
                    p.style.display = 'none';
                }
            });

            // Đồng bộ tab tương ứng trên rail
            railItems.forEach(ri => {
                if (ri.getAttribute('data-rail-tab') === tabName) {
                    ri.classList.add('active');
                } else {
                    ri.classList.remove('active');
                }
            });
        });
    });

    if (mobileCloseDrawerBtn && drawerPanel) {
        on(mobileCloseDrawerBtn, 'click', () => {
            drawerPanel.classList.remove('is-open');
            mobileBottomBtns.forEach(b => b.classList.remove('active'));
        });
    }

    // ----------------------------------------------------
    // 7. Mobile Segmented Mode Switcher (Nhập Liệu ⇄ Xem Thiệp)
    // ----------------------------------------------------
    const modeBtns = $$('[data-editor-mode]');
    const setMobileMode = (mode) => {
        if (mode === 'preview') {
            document.body.classList.add('is-mobile-preview-active');
            syncAllFieldsToIframe(previewIframe);
            syncAllFieldsToIframe(mobileIframe);
        } else {
            document.body.classList.remove('is-mobile-preview-active');
        }

        modeBtns.forEach(btn => {
            if (btn.getAttribute('data-editor-mode') === mode) {
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            }
        });
    };

    modeBtns.forEach(btn => {
        on(btn, 'click', () => {
            const mode = btn.getAttribute('data-editor-mode');
            if (mode) setMobileMode(mode);
        });
    });

    // Khởi chạy dock
    updateDockNames();

    // Khởi tạo Âm lịch tự động cho toàn bộ ô ngày dương lịch ngay khi mở trang
    const autoDateInputs = $$('.mayhen_date_with_auto_lunar');
    autoDateInputs.forEach(dInp => {
        updateAutoLunarFromDateInput(dInp);
        on(dInp, 'input', () => updateAutoLunarFromDateInput(dInp));
        on(dInp, 'change', () => updateAutoLunarFromDateInput(dInp));
    });

    // Bắt buộc chọn Ngày tháng năm và Thời gian 24h, cấm nhập tay (hỗ trợ cả các trường sinh động)
    document.addEventListener('keydown', (e) => {
        if (e.target && e.target.matches && e.target.matches('input[type="date"], input[type="time"], .mayhen_picker_must_select')) {
            if (e.key !== 'Tab' && e.key !== 'Enter' && e.key !== 'Escape') {
                e.preventDefault();
                return false;
            }
        }
    }, true);

    document.addEventListener('paste', (e) => {
        if (e.target && e.target.matches && e.target.matches('input[type="date"], input[type="time"], .mayhen_picker_must_select')) {
            e.preventDefault();
            return false;
        }
    }, true);

    document.addEventListener('click', (e) => {
        if (e.target && e.target.matches && e.target.matches('input[type="date"], input[type="time"], .mayhen_picker_must_select')) {
            try {
                if (typeof e.target.showPicker === 'function') {
                    e.target.showPicker();
                }
            } catch (err) {}
        }
    }, true);

    // ----------------------------------------------------
    // AI STORY WRITER (TASK-V1-04)
    // ----------------------------------------------------
    const btnOpenAiStory = $('#btn-open-ai-story');
    const aiStoryModal = $('#mayhen_ai_story_modal');
    const aiStoryCloseBtn = $('#mayhen_ai_story_close_btn');
    const aiGroomInp = $('#ai-story-groom');
    const aiBrideInp = $('#ai-story-bride');
    const aiKeywordsInp = $('#ai-story-keywords');
    const aiToneBtns = $$('.mayhen_ai_tone_btn');
    const btnGenAiStory = $('#btn-generate-ai-story');
    const aiStoryResults = $('#ai-story-results');

    let selectedTone = 'romantic';

    if (btnOpenAiStory && aiStoryModal) {
        on(btnOpenAiStory, 'click', () => {
            const groomInp = $('[data-sync-key="wedding.groom_name"]') || $('input[name="wedding[groom_name]"]');
            const brideInp = $('[data-sync-key="wedding.bride_name"]') || $('input[name="wedding[bride_name]"]');
            if (aiGroomInp && groomInp && groomInp.value) aiGroomInp.value = groomInp.value;
            if (aiBrideInp && brideInp && brideInp.value) aiBrideInp.value = brideInp.value;

            aiStoryModal.classList.add('mayhen_editor_modal_open');
        });

        const closeAiModal = () => {
            aiStoryModal.classList.remove('mayhen_editor_modal_open');
        };

        if (aiStoryCloseBtn) on(aiStoryCloseBtn, 'click', closeAiModal);
        on(aiStoryModal, 'click', (e) => {
            if (e.target === aiStoryModal) closeAiModal();
        });

        aiToneBtns.forEach(btn => {
            on(btn, 'click', () => {
                selectedTone = btn.getAttribute('data-tone') || 'romantic';
                aiToneBtns.forEach(b => {
                    const active = b === btn;
                    b.classList.toggle('is-active', active);
                    b.style.borderColor = active ? '#C5A880' : '#E5DCCD';
                    b.style.background = active ? '#FAF8F5' : '#FFF';
                    b.style.fontWeight = active ? '600' : 'normal';
                });
            });
        });

        if (btnGenAiStory) {
            on(btnGenAiStory, 'click', async () => {
                const groom = aiGroomInp?.value || 'Chú Rể';
                const bride = aiBrideInp?.value || 'Cô Dâu';
                const keywords = aiKeywordsInp?.value || '';
                const originalBtnHtml = btnGenAiStory.innerHTML;

                btnGenAiStory.disabled = true;
                btnGenAiStory.innerHTML = '<span>⏳ Đang dệt nên câu chuyện...</span>';
                if (aiStoryResults) {
                    aiStoryResults.innerHTML = '<div style="text-align: center; padding: 24px; color: #7A726A; font-style: italic;">Đang phân tích kỷ niệm và tạo 3 phương án Quiet Luxury...</div>';
                }

                try {
                    const aiUrl = window.__MAYHEN_AI_STORY_URL || '/api/editor/ai-story';
                    const res = await fetch(aiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.__MAYHEN_CSRF_TOKEN || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            groom_name: groom,
                            bride_name: bride,
                            keywords: keywords,
                            tone: selectedTone
                        })
                    });

                    const data = await res.json();
                    if (data && data.options && aiStoryResults) {
                        aiStoryResults.innerHTML = '';
                        data.options.forEach((opt) => {
                            const card = document.createElement('div');
                            card.style.border = '1px solid #EAE3D9';
                            card.style.borderRadius = '12px';
                            card.style.padding = '16px';
                            card.style.background = '#FCFAF7';
                            card.style.display = 'flex';
                            card.style.flexDirection = 'column';
                            card.style.gap = '8px';

                            card.innerHTML = `
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="background: #EFE8DD; color: #6B5E4B; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px;">${opt.style_name}</span>
                                        <strong style="font-size: 14px; color: #1F1D1A;">${opt.title}</strong>
                                    </div>
                                    <button type="button" class="btn-apply-story mayhen_client_button_base mayhen_client_button_sm" style="background: #1F1D1A; color: #FFF; border: none; font-size: 12px; padding: 6px 12px; border-radius: 6px; cursor: pointer;">
                                        Áp Dụng Vào Thiệp
                                    </button>
                                </div>
                                <div style="font-size: 13px; color: #9C7A4A; font-style: italic; font-weight: 500;">"${opt.subtitle}"</div>
                                <p style="font-size: 13px; color: #4A443E; line-height: 1.5; margin: 0;">${opt.content}</p>
                            `;

                            const applyBtn = card.querySelector('.btn-apply-story');
                            if (applyBtn) {
                                on(applyBtn, 'click', () => {
                                    const titleInp = $('[data-sync-key*="story_title"]') || $('input[name*="story_title"]');
                                    const subInp = $('[data-sync-key*="story_subtitle"]') || $('input[name*="story_subtitle"]');
                                    const contentInp = $('[data-sync-key*="story_content"]') || $('textarea[name*="story_content"]');

                                    if (titleInp) {
                                        titleInp.value = opt.title;
                                        titleInp.dispatchEvent(new Event('input', { bubbles: true }));
                                        titleInp.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                    if (subInp) {
                                        subInp.value = opt.subtitle;
                                        subInp.dispatchEvent(new Event('input', { bubbles: true }));
                                        subInp.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                    if (contentInp) {
                                        contentInp.value = opt.content;
                                        contentInp.dispatchEvent(new Event('input', { bubbles: true }));
                                        contentInp.dispatchEvent(new Event('change', { bubbles: true }));
                                    }

                                    applyBtn.innerText = '✓ Đã Áp Dụng!';
                                    applyBtn.style.background = '#2E7D32';

                                    setTimeout(() => {
                                        closeAiModal();
                                    }, 800);
                                });
                            }

                            aiStoryResults.appendChild(card);
                        });
                    }
                } catch (err) {
                    if (aiStoryResults) {
                        aiStoryResults.innerHTML = '<div style="color: #C62828; font-size: 13px; padding: 12px;">Đã xảy ra lỗi khi tạo gợi ý. Vui lòng thử lại.</div>';
                    }
                } finally {
                    btnGenAiStory.disabled = false;
                    btnGenAiStory.innerHTML = originalBtnHtml;
                }
            });
        }
    }

    // ----------------------------------------------------
    // VISUAL QUALITY DOCTOR (TASK-V1-06)
    // ----------------------------------------------------
    const btnQualityDoctor = $('#mayhen_btn_quality_doctor');
    const doctorScoreLabel = $('#mayhen_doctor_score_label');
    const qualityDoctorModal = $('#mayhen_quality_doctor_modal');
    const doctorCloseBtn = $('#mayhen_doctor_close_btn');
    const doctorCloseBottomBtn = $('#doctor-close-bottom-btn');
    const doctorModalScore = $('#doctor-modal-score');
    const doctorModalSummary = $('#doctor-modal-summary');
    const doctorModalBadge = $('#doctor-modal-badge');
    const doctorCheckList = $('#doctor-check-list');

    function runQualityDiagnosis() {
        const checks = [];
        let score = 100;

        // 1. Tên Cô dâu & Chú rể
        const groomVal = ($('[data-sync-key="wedding.groom_name"]')?.value || '').trim();
        const brideVal = ($('[data-sync-key="wedding.bride_name"]')?.value || '').trim();
        if (!groomVal || !brideVal || groomVal === 'Chú Rể' || brideVal === 'Cô Dâu') {
            score -= 20;
            checks.push({
                passed: false,
                step: 1,
                title: 'Tên Cô Dâu & Chú Rể',
                desc: 'Chưa điền tên cô dâu chú rể thật của hai bạn.'
            });
        } else {
            checks.push({
                passed: true,
                step: 1,
                title: 'Tên Cô Dâu & Chú Rể',
                desc: `Đã điền: ${groomVal} & ${brideVal}.`
            });
        }

        // 2. Ngày cử hành hôn lễ
        const dateVal = ($('[data-sync-key="wedding.wedding_date"]')?.value || '').trim();
        if (!dateVal) {
            score -= 15;
            checks.push({
                passed: false,
                step: 5,
                title: 'Ngày Cử Hành Hôn Lễ',
                desc: 'Chưa chọn ngày diễn ra đám cưới.'
            });
        } else {
            checks.push({
                passed: true,
                step: 5,
                title: 'Ngày Cử Hành Hôn Lễ',
                desc: `Ngày cưới: ${dateVal} (đã tự động tính âm lịch).`
            });
        }

        // 3. Địa chỉ tiệc cưới
        const addressVal = ($('[data-sync-key*="address"]')?.value || '').trim();
        if (!addressVal) {
            score -= 15;
            checks.push({
                passed: false,
                step: 6,
                title: 'Địa Chỉ Tổ Chức Tiệc',
                desc: 'Chưa điền địa chỉ nơi tiếp đón quan khách.'
            });
        } else {
            checks.push({
                passed: true,
                step: 6,
                title: 'Địa Chỉ Tổ Chức Tiệc',
                desc: 'Đã có địa chỉ sảnh tiệc chi tiết.'
            });
        }

        // 4. Đường dẫn Google Maps
        const mapVal = ($('[data-sync-key*="map"]')?.value || '').trim();
        if (!mapVal || mapVal === '#' || mapVal.startsWith('#map')) {
            score -= 10;
            checks.push({
                passed: false,
                step: 6,
                title: 'Bản Đồ Chỉ Đường Google Maps',
                desc: 'Chưa dán liên kết Google Maps để khách tìm đường 1-chạm.'
            });
        } else {
            checks.push({
                passed: true,
                step: 6,
                title: 'Bản Đồ Chỉ Đường Google Maps',
                desc: 'Đã tích hợp link bản đồ Google Maps.'
            });
        }

        // 5. Lời ngỏ & Câu chuyện tình yêu
        const storyVal = ($('[data-sync-key*="story_content"]')?.value || '').trim();
        if (!storyVal) {
            score -= 10;
            checks.push({
                passed: false,
                step: 3,
                title: 'Lời Ngỏ & Tình Yêu',
                desc: 'Chưa viết lời ngỏ tâm sự gửi tới khách mời.'
            });
        } else {
            checks.push({
                passed: true,
                step: 3,
                title: 'Lời Ngỏ & Tình Yêu',
                desc: 'Đã có lời ngỏ tình cảm dành cho quan khách.'
            });
        }

        // 6. Tài khoản mừng cưới VietQR
        const bankAccountVal = ($('[data-sync-key*="bank_account"]')?.value || '').trim();
        if (!bankAccountVal) {
            score -= 10;
            checks.push({
                passed: false,
                step: 10,
                title: 'Tài Khoản Mừng Cưới VietQR',
                desc: 'Chưa điền số tài khoản ngân hàng để nhận mừng cưới online.'
            });
        } else {
            checks.push({
                passed: true,
                step: 10,
                title: 'Tài Khoản Mừng Cưới VietQR',
                desc: 'Đã cấu hình tài khoản ngân hàng mừng cưới.'
            });
        }

        score = Math.max(score, 0);

        // Cập nhật nhãn điểm trên topbar
        if (doctorScoreLabel) {
            doctorScoreLabel.innerText = `${score}đ`;
            if (score >= 90) {
                doctorScoreLabel.style.color = '#2E7D32';
            } else if (score >= 70) {
                doctorScoreLabel.style.color = '#F57C00';
            } else {
                doctorScoreLabel.style.color = '#C62828';
            }
        }

        return { score, checks };
    }

    function renderQualityDoctorModal() {
        const { score, checks } = runQualityDiagnosis();

        if (doctorModalScore) doctorModalScore.innerText = score;

        if (doctorModalBadge && doctorModalSummary) {
            if (score >= 90) {
                doctorModalBadge.innerText = '✓ Sẵn sàng xuất bản';
                doctorModalBadge.style.background = '#E8F5E9';
                doctorModalBadge.style.color = '#2E7D32';
                doctorModalSummary.innerText = 'Thiệp cưới của hai bạn đã rất hoàn thiện và trang trọng!';
            } else if (score >= 70) {
                doctorModalBadge.innerText = '⚠️ Nên hoàn thiện thêm';
                doctorModalBadge.style.background = '#FFF3E0';
                doctorModalBadge.style.color = '#E65100';
                doctorModalSummary.innerText = 'Thiệp khá tốt, nhưng nên hoàn thiện các mục màu vàng để chu đáo hơn.';
            } else {
                doctorModalBadge.innerText = '❌ Cần điền thêm thông tin';
                doctorModalBadge.style.background = '#FFEBEE';
                doctorModalBadge.style.color = '#C62828';
                doctorModalSummary.innerText = 'Thiệp còn thiếu các thông tin cơ bản trước khi có thể xuất bản.';
            }
        }

        if (doctorCheckList) {
            doctorCheckList.innerHTML = '';
            checks.forEach(item => {
                const el = document.createElement('div');
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.justifyContent = 'space-between';
                el.style.padding = '12px 14px';
                el.style.borderRadius = '10px';
                el.style.background = item.passed ? '#FAFAFA' : '#FFF9F4';
                el.style.border = item.passed ? '1px solid #EFEFEF' : '1px solid #FFE0B2';
                el.style.gap = '10px';

                el.innerHTML = `
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 16px; line-height: 1.2;">${item.passed ? '✅' : '⚠️'}</span>
                        <div>
                            <strong style="font-size: 13px; color: #1F1D1A; display: block;">${item.title}</strong>
                            <span style="font-size: 12px; color: ${item.passed ? '#686058' : '#C62828'};">${item.desc}</span>
                        </div>
                    </div>
                    ${!item.passed ? `
                        <button type="button" class="btn-jump-step" data-jump-step="${item.step}" style="background: #FAF8F5; border: 1px solid #C5A880; color: #9C7A4A; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; white-space: nowrap;">
                            Đi tới sửa →
                        </button>
                    ` : ''}
                `;

                const jumpBtn = el.querySelector('.btn-jump-step');
                if (jumpBtn) {
                    on(jumpBtn, 'click', () => {
                        const targetStep = parseInt(jumpBtn.getAttribute('data-jump-step'), 10);
                        closeDoctorModal();
                        const stepPill = $(`[data-step="${targetStep}"]`);
                        if (stepPill) stepPill.click();
                    });
                }

                doctorCheckList.appendChild(el);
            });
        }
    }

    const closeDoctorModal = () => {
        if (qualityDoctorModal) qualityDoctorModal.classList.remove('mayhen_editor_modal_open');
    };

    if (btnQualityDoctor && qualityDoctorModal) {
        on(btnQualityDoctor, 'click', () => {
            renderQualityDoctorModal();
            qualityDoctorModal.classList.add('mayhen_editor_modal_open');
        });

        if (doctorCloseBtn) on(doctorCloseBtn, 'click', closeDoctorModal);
        if (doctorCloseBottomBtn) on(doctorCloseBottomBtn, 'click', closeDoctorModal);
        on(qualityDoctorModal, 'click', (e) => {
            if (e.target === qualityDoctorModal) closeDoctorModal();
        });

        setTimeout(runQualityDiagnosis, 300);
    }

    // Đồng bộ ngay lập tức toàn bộ dữ liệu form sang iframes
    setTimeout(() => {
        syncAllFieldsToIframe(previewIframe);
        syncAllFieldsToIframe(mobileIframe);
    }, 150);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditor);
} else {
    initEditor();
}
