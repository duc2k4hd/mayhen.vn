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
    });

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
    const transferredDraftRaw = sessionStorage.getItem('mayhen_transferred_draft');
    if (transferredDraftRaw) {
        try {
            const parsed = JSON.parse(transferredDraftRaw);
            if (parsed && parsed.wedding) {
                draftWeddingData = parsed.wedding;
            }
            sessionStorage.removeItem('mayhen_transferred_draft');
        } catch (e) {}
    }

    if (!draftWeddingData) {
        const localDraft = store.autosaver.getLocalDraft(invitationId, templateSlug);
        if (localDraft && localDraft.wedding) {
            const isDifferentTemplate = localDraft.template_slug && templateSlug && localDraft.template_slug !== templateSlug;
            const hasData = Object.values(localDraft.wedding).some(v => typeof v === 'string' && v.trim().length > 0);
            if (hasData && !invitationId) {
                if (isDifferentTemplate) {
                    // Nếu là nháp của mẫu khác, chỉ giữ lại text (tên, ngày cưới, phụ mẫu...), loại bỏ ảnh asset mặc định của mẫu cũ
                    const filtered = {};
                    Object.keys(localDraft.wedding).forEach(k => {
                        const val = localDraft.wedding[k];
                        if (!isImageKey(k)) {
                            filtered[k] = val;
                        } else if (typeof val === 'string' && val.startsWith('data:image') && !isBannedExternalUrl(val)) {
                            filtered[k] = val;
                        }
                    });
                    draftWeddingData = filtered;
                } else {
                    draftWeddingData = localDraft.wedding;
                }
            }
        }
    }

    if (draftWeddingData) {
        Object.keys(draftWeddingData).forEach(key => {
            const val = draftWeddingData[key];
            if (val === undefined || val === null || val === '') return;

            const inputEl = document.querySelector(`[data-sync-key="${key}"]`) ||
                            document.querySelector(`[data-sync-key="wedding.${key}"]`) ||
                            document.querySelector(`[data-sync-key="wedding.extra_data.${key}"]`);
            if (inputEl) {
                // Nếu là trường ảnh, loại bỏ asset ảo hoặc link rác bên ngoài (motdoi, ladicdn...)
                if (isImageKey(key) && typeof val === 'string') {
                    if (isBannedExternalUrl(val) || (val.startsWith('assets/') && !val.startsWith('data:image'))) {
                        return;
                    }
                }

                inputEl.value = val;

                // Đồng bộ thẻ xem trước ảnh bên trái (preview thumb)
                if (inputEl.id) {
                    const thumb = document.getElementById('preview_thumb_' + inputEl.id);
                    if (thumb && val && !isBannedExternalUrl(val)) {
                        if (val.startsWith('http://') || val.startsWith('https://') || val.startsWith('/') || val.startsWith('data:')) {
                            thumb.src = val;
                        } else if (templateSlug) {
                            thumb.src = `/mau-thiep/${templateSlug}/assets/${val}`;
                        }
                    }
                }
            }

            store.dispatch({
                type: ActionTypes.UPDATE_FIELD,
                payload: { key: key.startsWith('wedding.') ? key : `wedding.${key}`, value: val }
            });
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
            const wedding = state.wedding;
            Object.keys(wedding).forEach(prop => {
                const val = wedding[prop];
                const inputEl = document.querySelector(`[data-sync-key="wedding.${prop}"]`);
                if (inputEl && inputEl.value !== val) {
                    inputEl.value = val !== undefined && val !== null ? val : '';
                }
            });
            syncAllFieldsToIframe(previewIframe);
            syncAllFieldsToIframe(mobileIframe);
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
    // 1. Dynamic Stepper Navigation
    // ----------------------------------------------------
    let currentStep = 1;
    const stepTabs = $$('[data-mayhen-step]');
    const stepPanels = $$('[data-step-panel]');
    const totalSteps = stepPanels.length;
    const btnPrev = $('#mayhen_btn_prev');
    const btnNext = $('#mayhen_btn_next');

    const goToStep = (step) => {
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

        // Tự động cuộn Iframe preview đến đúng Section tương ứng
        const activeTab = document.querySelector(`[data-mayhen-step="${currentStep}"]`);
        const sectionId = activeTab ? activeTab.getAttribute('data-section-id') : null;
        if (sectionId) {
            const scrollPayload = {
                type: 'MAYHEN_SCROLL_TO_SECTION',
                section_id: sectionId,
                step: currentStep,
            };
            if (previewIframe && previewIframe.contentWindow) {
                previewIframe.contentWindow.postMessage(scrollPayload, '*');
            }
            if (mobileIframe && mobileIframe.contentWindow) {
                mobileIframe.contentWindow.postMessage(scrollPayload, '*');
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
            goToStep(s);
        });
    });

    if (btnPrev) {
        on(btnPrev, 'click', () => {
            goToStep(currentStep - 1);
        });
    }

    if (btnNext) {
        on(btnNext, 'click', () => {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            } else {
                submitFormWithPendingUploads();
            }
        });
    }

    // Khởi tạo trạng thái ban đầu cho Stepper
    goToStep(1);

    // ----------------------------------------------------
    // 2. Realtime Iframe Communication Engine (PostMessage Sync)
    // ----------------------------------------------------
    const sendSyncMessage = (key, value) => {
        const payload = {
            type: 'MAYHEN_SYNC_INPUT',
            key: key,
            value: value,
        };

        try {
            if (previewIframe && previewIframe.contentWindow) {
                previewIframe.contentWindow.postMessage(payload, '*');
            }
            if (mobileIframe && mobileIframe.contentWindow) {
                mobileIframe.contentWindow.postMessage(payload, '*');
            }
        } catch (e) {
            // Safe cross-origin fallback
        }
    };

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
        const handleInputEvent = (e) => {
            const target = e.target;
            const key = target.getAttribute('data-sync-key');
            if (!key) return;

            // TUYỆT ĐỐI KHÔNG can thiệp hoặc gán đè target.value trong sự kiện input
            // để bảo vệ trọn vẹn bộ gõ tiếng Việt (Unikey/EVKey/Windows IME) không bị nuốt chữ hay nhảy con trỏ.
            const val = target.value;

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
    }

    // Khi Iframe hoàn tất tải, gửi toàn bộ giá trị form, bảng màu và thứ tự bố cục sang iframe
    const syncAllFieldsToIframe = (iframeEl) => {
        if (!iframeEl || !iframeEl.contentWindow) return;
        const inputs = $$('[data-sync-key]');
        inputs.forEach(inp => {
            const key = inp.getAttribute('data-sync-key');
            const val = inp.value;
            if (key) {
                iframeEl.contentWindow.postMessage({
                    type: 'MAYHEN_SYNC_INPUT',
                    key: key,
                    value: val !== undefined ? val : '',
                }, '*');
            }
        });

        const state = store.getState();
        const customConfig = state.custom_config || {};

        // Đồng bộ Bảng màu (Theme Palette)
        if (customConfig.theme && customConfig.theme.palette_id) {
            iframeEl.contentWindow.postMessage({
                type: 'MAYHEN_CHANGE_THEME',
                palette_id: customConfig.theme.palette_id,
                palette: customConfig.theme,
            }, '*');
        }

        // Đồng bộ Con dấu sáp (Wax Seal)
        if (customConfig.wax_seal) {
            iframeEl.contentWindow.postMessage({
                type: 'MAYHEN_UPDATE_WAX_SEAL',
                wax_seal: customConfig.wax_seal,
            }, '*');
        }

        // Đồng bộ Thứ tự Bố cục (Section Order & Hidden)
        if (customConfig.sections_order || customConfig.sections_hidden) {
            iframeEl.contentWindow.postMessage({
                type: 'MAYHEN_REORDER_SECTIONS',
                order: customConfig.sections_order || [],
                sections_hidden: customConfig.sections_hidden || [],
            }, '*');
        }

        // Cuộn đến section ban đầu tương ứng với bước đang mở
        const initialTab = document.querySelector(`[data-mayhen-step="${currentStep}"]`);
        const initialSectionId = initialTab ? initialTab.getAttribute('data-section-id') : null;
        if (initialSectionId) {
            iframeEl.contentWindow.postMessage({
                type: 'MAYHEN_SCROLL_TO_SECTION',
                section_id: initialSectionId,
                step: currentStep,
            }, '*');
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
                if (syncKey) sendSyncMessage(syncKey, dataUrl);
            }

            if (statusEl) {
                statusEl.textContent = '✓ Tải ảnh thành công';
                statusEl.style.color = '#10B981';
            }

            activeCropperTarget.confirmed = true;
            closeCropperModal();
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

            if (statusEl) {
                statusEl.textContent = 'Đang tải ảnh...';
                statusEl.style.color = 'var(--mayhen-color-text-muted)';
            }

            const objectUrl = URL.createObjectURL(file);

            // Kiểm tra kích thước và tỷ lệ ảnh
            const tempImg = new Image();
            tempImg.onload = async () => {
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
                    });
                } else {
                    // Ảnh đã chuẩn 3/4 hoặc là ảnh gallery không bắt buộc -> Tự động nén sang data:image trực tiếp
                    try {
                        if (statusEl) statusEl.textContent = 'Đang tải ảnh...';
                        const dataUrl = await compressImageToDataUrl(file);
                        if (targetThumb) targetThumb.src = dataUrl;
                        if (targetInput) {
                            targetInput.value = dataUrl;
                            const syncKey = targetInput.getAttribute('data-sync-key');
                            if (syncKey) sendSyncMessage(syncKey, dataUrl);
                        }
                        if (statusEl) {
                            statusEl.textContent = '✓ Tải ảnh thành công';
                            statusEl.style.color = '#10B981';
                        }
                    } catch (err) {
                        console.error('Lỗi chuyển ảnh sang data:image:', err);
                        if (statusEl) {
                            statusEl.textContent = 'Không thể tải ảnh này';
                            statusEl.style.color = '#EF4444';
                        }
                    }
                }
            };
            tempImg.onerror = () => {
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

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
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
