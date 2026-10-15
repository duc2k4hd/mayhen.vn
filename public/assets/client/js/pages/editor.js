/**
 * MAYHEN.VN — INVITATION LIVE EDITOR CONTROLLER
 * Schema-Driven Dynamic Stepper & Realtime Iframe Template Sync
 * Strict: Pure Vanilla JavaScript (ES Modules), Zero external dependencies
 */
import { $, $$, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const mainForm = $('#mayhen_editor_form');
    const previewIframe = $('#mayhen_editor_preview_iframe');
    const mobileIframe = $('#mayhen_editor_mobile_preview_iframe');
    const dockNames = $('[data-mayhen-preview="names"]');

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

        // Ẩn hiện nút Quay lại / Tiếp theo
        if (btnPrev) {
            btnPrev.style.display = currentStep > 1 ? 'inline-flex' : 'none';
        }

        if (btnNext) {
            if (currentStep === totalSteps) {
                btnNext.innerHTML = `<span>Xem Lại & Tạo Thiệp</span> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>`;
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
                if (mainForm) mainForm.submit();
            }
        });
    }

    // Trigger submit từ các nút Hoàn Tất
    const submitTriggers = $$('[data-mayhen-action="trigger-submit"]');
    submitTriggers.forEach(btn => {
        on(btn, 'click', () => {
            if (mainForm) mainForm.submit();
        });
    });

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

            const val = target.value;

            // Xem trước ảnh nhỏ nếu là trường image
            if (target.id) {
                const thumb = document.getElementById('preview_thumb_' + target.id);
                if (thumb && val) {
                    thumb.src = val;
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

    // Khi Iframe hoàn tất tải, gửi toàn bộ giá trị form hiện thời sang iframe
    const syncAllFieldsToIframe = (iframeEl) => {
        if (!iframeEl || !iframeEl.contentWindow) return;
        const inputs = $$('[data-sync-key]');
        inputs.forEach(inp => {
            const key = inp.getAttribute('data-sync-key');
            const val = inp.value;
            if (key && val) {
                iframeEl.contentWindow.postMessage({
                    type: 'MAYHEN_SYNC_INPUT',
                    key: key,
                    value: val,
                }, '*');
            }
        });
    };

    if (previewIframe) {
        previewIframe.addEventListener('load', () => {
            setTimeout(() => syncAllFieldsToIframe(previewIframe), 300);
        });
    }

    if (mobileIframe) {
        mobileIframe.addEventListener('load', () => {
            setTimeout(() => syncAllFieldsToIframe(mobileIframe), 300);
        });
    }

    // ----------------------------------------------------
    // 3. Asset Image Uploader (Upload trực tiếp vào assets của template)
    // ----------------------------------------------------
    const fileUploaders = $$('.mayhen_editor_file_uploader');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                      document.querySelector('input[name="_token"]')?.value || '';

    fileUploaders.forEach(uploader => {
        on(uploader, 'change', async (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            const targetInputId = uploader.getAttribute('data-target-input');
            const targetThumbId = uploader.getAttribute('data-target-thumb');
            const templateSlug = uploader.getAttribute('data-template-slug');
            const targetInput = document.getElementById(targetInputId);
            const targetThumb = document.getElementById(targetThumbId);
            const statusEl = document.getElementById('status_' + targetInputId);

            // 1. Xem trước ảnh tức thì trên trình duyệt
            const localPreviewUrl = URL.createObjectURL(file);
            if (targetThumb) targetThumb.src = localPreviewUrl;

            // Gửi ngay sang iframe để xem trước thời gian thực
            if (targetInput) {
                const key = targetInput.getAttribute('data-sync-key');
                if (key) {
                    sendSyncMessage(key, localPreviewUrl);
                }
            }

            if (statusEl) {
                statusEl.textContent = 'Đang tải lên...';
                statusEl.style.color = 'var(--mayhen-color-primary)';
            }

            // 2. Upload file lên server lưu vào assets của template
            const formData = new FormData();
            formData.append('file', file);
            formData.append('template_slug', templateSlug);
            formData.append('_token', csrfToken);

            try {
                const res = await fetch('/tao-thiep/upload-asset', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                const data = await res.json();
                if (data.success && data.file_url) {
                    if (targetInput) {
                        targetInput.value = data.file_url;
                        const key = targetInput.getAttribute('data-sync-key');
                        if (key) {
                            sendSyncMessage(key, data.file_url);
                        }
                    }
                    if (targetThumb) targetThumb.src = data.file_url;
                    if (statusEl) {
                        statusEl.textContent = '✓ Đã lưu vào assets!';
                        statusEl.style.color = '#10B981';
                        setTimeout(() => { statusEl.textContent = ''; }, 3000);
                    }
                } else {
                    throw new Error(data.message || 'Lỗi khi tải ảnh lên');
                }
            } catch (err) {
                if (statusEl) {
                    statusEl.textContent = 'Lỗi tải ảnh';
                    statusEl.style.color = 'var(--mayhen-color-danger)';
                }
            }
        });
    });

    // ----------------------------------------------------
    // 4. Mobile Fullscreen Preview Modal
    // ----------------------------------------------------
    const previewModal = $('#mayhen_mobile_preview_modal');
    const openPreviewBtn = $('[data-mayhen-action="open-preview-modal"]');
    const closePreviewBtn = $('[data-mayhen-action="close-preview-modal"]');

    if (openPreviewBtn && previewModal) {
        on(openPreviewBtn, 'click', () => {
            previewModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
            if (mobileIframe) {
                syncAllFieldsToIframe(mobileIframe);
            }
        });
    }

    if (closePreviewBtn && previewModal) {
        on(closePreviewBtn, 'click', () => {
            previewModal.classList.remove('mayhen_editor_modal_open');
            document.body.style.overflow = '';
        });
    }

    if (previewModal) {
        on(previewModal, 'click', (e) => {
            if (e.target === previewModal) {
                previewModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    }

    // ----------------------------------------------------
    // 4. Template Switcher Modal (Đổi Mẫu Nhanh)
    // ----------------------------------------------------
    const templateModal = $('#mayhen_template_switcher_modal');
    const openTemplateBtn = $('[data-mayhen-action="open-template-modal"]');
    const closeTemplateBtn = $('[data-mayhen-action="close-template-modal"]');

    if (openTemplateBtn && templateModal) {
        on(openTemplateBtn, 'click', () => {
            templateModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeTemplateBtn && templateModal) {
        on(closeTemplateBtn, 'click', () => {
            templateModal.classList.remove('mayhen_editor_modal_open');
            document.body.style.overflow = '';
        });
    }

    if (templateModal) {
        on(templateModal, 'click', (e) => {
            if (e.target === templateModal) {
                templateModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    }

    // Khởi chạy dock
    updateDockNames();
});
