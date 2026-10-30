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
        const localDraft = store.autosaver.getLocalDraft(invitationId);
        if (localDraft && localDraft.wedding) {
            const hasData = Object.values(localDraft.wedding).some(v => typeof v === 'string' && v.trim().length > 0);
            if (hasData && !invitationId) {
                draftWeddingData = localDraft.wedding;
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
                inputEl.value = val;
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
                    p.style.display = 'block';
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

    if (mainForm) {
        const handleInputEvent = (e) => {
            const target = e.target;
            const key = target.getAttribute('data-sync-key');
            if (!key) return;

            // Kiểm tra và khử ký tự đặc biệt cho các trường họ tên (chống hack, chống phá, tối đa 4 từ và 30 ký tự)
            if (target.classList && target.classList.contains('mayhen_validate_human_name')) {
                let cleaned = target.value.replace(/[^\p{L}\s]/gu, ''); // Chỉ giữ chữ cái tiếng Việt/Latin và khoảng trắng
                cleaned = cleaned.replace(/\s{2,}/g, ' '); // Khử khoảng trắng kép
                if (cleaned.length > 30) {
                    cleaned = cleaned.slice(0, 30);
                }
                const words = cleaned.trim().split(/\s+/).filter(Boolean);
                if (words.length > 4) {
                    cleaned = words.slice(0, 4).join(' ');
                }
                if (target.value !== cleaned) {
                    target.value = cleaned;
                }
            }

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

            // Đồng bộ sang Iframe
            sendSyncMessage(key, val);
        };

        on(mainForm, 'input', handleInputEvent);
        on(mainForm, 'change', handleInputEvent);
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
    const updateVietQr = (side) => {
        const bankSelect = $(`.mayhen_vietqr_bank_select[data-qr-side="${side}"]`);
        const accInput = $(`.mayhen_vietqr_acc_input[data-qr-side="${side}"]`);
        const nameInput = $(`.mayhen_vietqr_name_input[data-qr-side="${side}"]`);

        if (!bankSelect || !accInput) return;

        const bankCode = bankSelect.value.trim();
        const accountNo = accInput.value.trim();
        const accountName = nameInput ? nameInput.value.trim().toUpperCase() : '';
        const targetInputId = bankSelect.getAttribute('data-target-input');
        const targetInput = document.getElementById(targetInputId);
        const targetThumb = document.getElementById('preview_thumb_' + targetInputId);

        if (!bankCode || !accountNo) return;

        // Sinh link VietQR chuẩn NAPAS an toàn 100%
        let qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNo}-compact2.jpg`;
        if (accountName) {
            qrUrl += `?accountName=${encodeURIComponent(accountName)}`;
        }

        if (targetThumb) targetThumb.src = qrUrl;
        if (targetInput) {
            targetInput.value = qrUrl;
            const syncKey = targetInput.getAttribute('data-sync-key');
            if (syncKey) {
                store.dispatch({
                    type: ActionTypes.UPDATE_FIELD,
                    payload: { key: syncKey, value: qrUrl }
                });
                sendSyncMessage(syncKey, qrUrl);
            }
        }
    };

    $$('.mayhen_vietqr_bank_select, .mayhen_vietqr_acc_input, .mayhen_vietqr_name_input').forEach(el => {
        on(el, 'input', () => {
            const side = el.getAttribute('data-qr-side');
            if (side) updateVietQr(side);
        });
    });

    $$('.mayhen_btn_gen_vietqr').forEach(btn => {
        on(btn, 'click', () => {
            const side = btn.getAttribute('data-qr-side');
            if (side) updateVietQr(side);
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
                    const musicInput = document.getElementById('wedding_music_url') || $('[data-sync-key="wedding.music_url"]');
                    if (musicInput) {
                        musicInput.value = audioUrl;
                        sendSyncMessage('wedding.music_url', audioUrl);
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
                    p.style.display = 'block';
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
