/**
 * MAYHEN.VN — INVITATION EDITOR CONTROLLER
 * Realtime 2-way input preview sync
 */
import { $, $$, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Inputs Sync với hỗ trợ Schema Dynamic Fields
    const findInput = (keyPart, fallbackId) => {
        return $(`[data-sync-key*="${keyPart}"]`) || $(`#${fallbackId}`);
    };

    const groomInput = findInput('groom_name', 'groom_name');
    const brideInput = findInput('bride_name', 'bride_name');
    const dateInput = findInput('wedding_date', 'wedding_date');
    const venueInput = findInput('venue_name', 'venue_name');
    const colorInput = findInput('accent_color', 'accent_color');

    const previewNames = $('[data-mayhen-preview="names"]');
    const previewDate = $('[data-mayhen-preview="date"]');
    const previewVenue = $('[data-mayhen-preview="venue"]');
    const previewCard = $('.mayhen_invitation_card');

    const updatePreview = () => {
        const groom = groomInput?.value.trim() || 'Minh Quân';
        const bride = brideInput?.value.trim() || 'Ái Như';
        if (previewNames) {
            previewNames.innerHTML = `${groom} <span style="font-family: var(--mayhen-font-script); font-style: italic; color: var(--mayhen-color-primary);">&</span> ${bride}`;
        }

        if (dateInput?.value && previewDate) {
            const d = new Date(dateInput.value);
            if (!isNaN(d.getTime())) {
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                previewDate.textContent = `Ngày ${day}.${month}.${year}`;
            }
        }

        if (venueInput && previewVenue) {
            previewVenue.textContent = venueInput.value.trim() || 'The White Palace • TP. HCM';
        }

        if (colorInput && previewCard) {
            previewCard.style.setProperty('--mayhen-color-primary', colorInput.value);
        }
    };

    if (groomInput) on(groomInput, 'input', updatePreview);
    if (brideInput) on(brideInput, 'input', updatePreview);
    if (dateInput) on(dateInput, 'change', updatePreview);
    if (venueInput) on(venueInput, 'input', updatePreview);
    if (colorInput) on(colorInput, 'input', updatePreview);

    // Lắng nghe tất cả các input động
    const allSyncInputs = $$('[data-sync-key]');
    allSyncInputs.forEach(input => {
        on(input, 'input', updatePreview);
    });

    // 2. Template Picker Selection
    const templateCards = $$('[data-mayhen-template-card]');
    const hiddenTemplateId = $('#template_id');

    templateCards.forEach(card => {
        on(card, 'click', () => {
            templateCards.forEach(c => c.classList.remove('mayhen_editor_template_radio_selected'));
            card.classList.add('mayhen_editor_template_radio_selected');
            if (hiddenTemplateId) {
                hiddenTemplateId.value = card.getAttribute('data-mayhen-template-id');
            }
        });
    });

    // 3. Mobile View Toggle (Chỉnh Sửa / Xem Thử)
    const toggleBtns = $$('[data-mayhen-action="switch-editor-view"]');
    const formPanel = $('.mayhen_editor_form_panel');
    const previewPanel = $('.mayhen_editor_preview_panel');

    toggleBtns.forEach(btn => {
        on(btn, 'click', () => {
            const target = btn.getAttribute('data-view-target');
            toggleBtns.forEach(b => b.classList.remove('mayhen_editor_toggle_btn_active'));
            btn.classList.add('mayhen_editor_toggle_btn_active');

            if (target === 'preview') {
                formPanel.style.display = 'none';
                previewPanel.classList.add('mayhen_editor_preview_mobile_show');
                previewPanel.style.display = 'flex';
            } else {
                formPanel.style.display = 'block';
                previewPanel.classList.remove('mayhen_editor_preview_mobile_show');
                previewPanel.style.display = 'none';
            }
        });
    });
});
