/**
 * MAYHEN.VN — INVITATION LIVE EDITOR CONTROLLER
 * Realtime 2-way input preview sync, step-by-step navigation & mobile dock
 * Strict: Pure Vanilla JavaScript, No external dependencies
 */
import { $, $$, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Inputs Realtime Live Sync Engine
    // ----------------------------------------------------
    const groomInput = $('#groom_name');
    const brideInput = $('#bride_name');
    const dateInput = $('#wedding_date');
    const venueInput = $('#venue_name');
    const addressInput = $('#venue_address');
    const storyInput = $('#story');
    const coverInput = $('#cover_photo_url');
    const colorInput = $('#accent_color');
    const bankNameInput = $('#bank_name');
    const bankAccInput = $('#bank_account_number');
    const bankOwnerInput = $('#bank_account_name');

    // Preview targets (hỗ trợ cả Phone screen PC và Mobile Dock)
    const nameTargets = $$('[data-mayhen-preview="names"]');
    const dateTargets = $$('[data-mayhen-preview="date"]');
    const venueTargets = $$('[data-mayhen-preview="venue"]');
    const addressTargets = $$('[data-mayhen-preview="address"]');
    const storyTargets = $$('[data-mayhen-preview="story"]');
    const photoTargets = $$('[data-mayhen-preview="photo"]');
    const bankingTargets = $$('[data-mayhen-preview="banking"]');
    const dockThumb = $('#mayhen_dock_thumb');
    const previewCards = $$('.mayhen_invitation_card');

    const updatePreview = () => {
        const groom = groomInput?.value.trim() || 'Minh Quân';
        const bride = brideInput?.value.trim() || 'Ái Như';
        
        nameTargets.forEach(el => {
            if (el.classList.contains('mayhen_editor_dock_names')) {
                el.textContent = `${groom} • ${bride}`;
            } else {
                el.innerHTML = `${groom} <span style="font-family: var(--mayhen-font-script); font-style: italic; color: var(--mayhen-color-primary);">&</span> ${bride}`;
            }
        });

        if (dateInput?.value) {
            const parts = dateInput.value.split('-');
            if (parts.length === 3) {
                const formattedDate = `Ngày ${parts[2]}.${parts[1]}.${parts[0]}`;
                dateTargets.forEach(el => el.textContent = formattedDate);
            }
        }

        if (venueInput) {
            const venue = venueInput.value.trim() || 'The White Palace Ballroom';
            venueTargets.forEach(el => el.textContent = venue);
        }

        if (addressInput) {
            const addr = addressInput.value.trim() || '194 Hoàng Văn Thụ, TP. HCM';
            addressTargets.forEach(el => el.textContent = addr);
        }

        if (storyInput) {
            const story = storyInput.value.trim() || 'Yêu là khi nhìn thấy cả thế giới bình yên trong mắt một người.';
            storyTargets.forEach(el => el.textContent = `"${story}"`);
        }

        if (coverInput?.value) {
            const url = coverInput.value.trim();
            photoTargets.forEach(img => img.src = url);
            if (dockThumb) dockThumb.src = url;
        }

        if (colorInput) {
            const color = colorInput.value;
            previewCards.forEach(card => {
                card.style.setProperty('--mayhen-color-primary', color);
            });
        }

        if (bankNameInput || bankAccInput || bankOwnerInput) {
            const bName = bankNameInput?.value.trim() || 'Vietcombank';
            const bAcc = bankAccInput?.value.trim() || '1023456789';
            const bOwner = bankOwnerInput?.value.trim() || 'DANG MINH QUAN';
            bankingTargets.forEach(el => el.textContent = `${bName} • ${bAcc} • ${bOwner}`);
        }
    };

    // Gán listener realtime cho tất cả các input
    const inputsToWatch = [groomInput, brideInput, dateInput, venueInput, addressInput, storyInput, coverInput, colorInput, bankNameInput, bankAccInput, bankOwnerInput];
    inputsToWatch.forEach(inp => {
        if (!inp) return;
        on(inp, 'input', updatePreview);
        on(inp, 'change', updatePreview);
    });

    // ----------------------------------------------------
    // 2. Stepper Tabs Navigation (Chuyển bước mượt mà)
    // ----------------------------------------------------
    let currentStep = 1;
    const totalSteps = 4;
    const stepTabs = $$('[data-mayhen-step]');
    const stepPanels = $$('[data-step-panel]');
    const btnPrev = $('#mayhen_btn_prev');
    const btnNext = $('#mayhen_btn_next');
    const mainForm = $('#mayhen_editor_form');

    const goToStep = (step) => {
        if (step < 1 || step > totalSteps) return;
        currentStep = step;

        // Cập nhật tab pills
        stepTabs.forEach(tab => {
            const s = parseInt(tab.getAttribute('data-mayhen-step'), 10);
            if (s === currentStep) {
                tab.classList.add('mayhen_editor_stepper_tab_active');
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
    // 3. Mobile Fullscreen Preview Modal
    // ----------------------------------------------------
    const previewModal = $('#mayhen_mobile_preview_modal');
    const modalBody = $('#mayhen_mobile_modal_body');
    const openPreviewBtn = $('[data-mayhen-action="open-preview-modal"]');
    const closePreviewBtn = $('[data-mayhen-action="close-preview-modal"]');

    if (openPreviewBtn && previewModal && modalBody) {
        on(openPreviewBtn, 'click', () => {
            // Clone phone screen content vào modal
            const phoneScreen = $('.mayhen_editor_phone_screen');
            if (phoneScreen) {
                modalBody.innerHTML = phoneScreen.innerHTML;
            }
            previewModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closePreviewBtn && previewModal) {
        on(closePreviewBtn, 'click', () => {
            previewModal.classList.remove('mayhen_editor_modal_open');
            document.body.style.overflow = '';
        });
    }

    // Đóng khi click ngoài backdrop
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

    // Khởi chạy đồng bộ lần đầu
    updatePreview();
});
