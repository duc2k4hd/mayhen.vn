/**
 * MAYHEN.VN — AI CURATED INVITATION WIZARD JAVASCRIPT (V2)
 * Live Mini-Card Preview + Smart Validation (Zero alert())
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('curatedWizardForm');
    const submitBtn = document.getElementById('submitCuratedBtn');
    const inlineError = document.getElementById('wizardInlineError');

    if (!form || !submitBtn) return;

    const groomInput = document.getElementById('groom_name');
    const brideInput = document.getElementById('bride_name');
    const dateInput = document.getElementById('wedding_date');
    const timeInput = document.getElementById('party_time');
    const venueInput = document.getElementById('venue_name');

    const summaryCouple = document.getElementById('summaryCoupleText');
    const summaryStyle = document.getElementById('summaryStyleText');
    const summaryPalette = document.getElementById('summaryPaletteText');
    const summaryLayout = document.getElementById('summaryLayoutText');

    const miniCard = document.getElementById('wizardMiniCard');
    const miniGroom = document.getElementById('miniCardGroom');
    const miniBride = document.getElementById('miniCardBride');
    const miniCeremony = document.getElementById('miniCardCeremony');
    const miniDate = document.getElementById('miniCardDate');
    const miniVenue = document.getElementById('miniCardVenue');

    const styleNames = {
        romantic: 'Lãng Mạn & Bay Bổng',
        minimal: 'Tối Giản & Hiện Đại',
        editorial: 'Tạp Chí & Thời Thượng',
        traditional: 'Truyền Thống & Chuẩn Mực',
    };

    function formatDisplayDate(iso) {
        const m = String(iso || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return '20 · 12 · 2026';
        return `${m[3]} · ${m[2]} · ${m[1]}`;
    }

    function showInlineError(msg) {
        if (!inlineError) return;
        inlineError.textContent = msg;
        inlineError.style.display = 'block';
        inlineError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearInlineError() {
        if (inlineError) inlineError.style.display = 'none';
    }

    function updateLivePreview() {
        const groom = (groomInput?.value || '').trim() || 'Minh Tuấn';
        const bride = (brideInput?.value || '').trim() || 'Thu Hà';
        const venue = (venueInput?.value || '').trim() || 'Trung Tâm Tiệc Cưới Ngân Hà';

        if (summaryCouple) summaryCouple.textContent = `${groom} & ${bride}`;
        if (miniGroom) miniGroom.textContent = groom;
        if (miniBride) miniBride.textContent = bride;
        if (miniVenue) miniVenue.textContent = venue;

        const selectedCeremony = form.querySelector('input[name="ceremony_type"]:checked');
        if (miniCeremony && selectedCeremony) {
            miniCeremony.textContent = selectedCeremony.value;
        }

        if (miniDate && dateInput) {
            miniDate.textContent = formatDisplayDate(dateInput.value);
        }

        const selectedStyle = form.querySelector('input[name="style"]:checked');
        if (summaryStyle && selectedStyle && styleNames[selectedStyle.value]) {
            summaryStyle.textContent = styleNames[selectedStyle.value];
        }

        const selectedPalette = form.querySelector('input[name="palette"]:checked');
        if (selectedPalette) {
            if (summaryPalette) {
                summaryPalette.textContent = selectedPalette.dataset.name || selectedPalette.value;
            }
            if (miniCard) {
                miniCard.style.setProperty('--mc-primary', selectedPalette.dataset.primary || '#85182A');
                miniCard.style.setProperty('--mc-accent', selectedPalette.dataset.accent || '#CFA860');
                miniCard.style.setProperty('--mc-bg', selectedPalette.dataset.bg || '#FAF7F2');
            }
        }

        const selectedMode = form.querySelector('input[name="layout_mode"]:checked');
        if (summaryLayout && selectedMode) {
            summaryLayout.textContent = selectedMode.value === 'full_10'
                ? '10 Section Đầy Đủ (Có RSVP & QR)'
                : '7 Section Tinh Gọn (Có Album 10 Ảnh)';
        }
    }

    // Event bindings
    [groomInput, brideInput, dateInput, venueInput].forEach((el) => {
        if (el) el.addEventListener('input', () => { clearInlineError(); updateLivePreview(); });
    });

    form.querySelectorAll('input[type="radio"]').forEach((r) => {
        r.addEventListener('change', () => { clearInlineError(); updateLivePreview(); });
    });

    // Quick time buttons
    document.querySelectorAll('[data-set-time]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (timeInput) timeInput.value = btn.getAttribute('data-set-time') || '18:00';
        });
    });

    // Step pill active state on click
    const stepPills = document.querySelectorAll('.step_pill');
    stepPills.forEach((pill) => {
        pill.addEventListener('click', () => {
            stepPills.forEach((p) => p.classList.remove('is-active'));
            pill.classList.add('is-active');
        });
    });

    updateLivePreview();

    // Form submit guard + Staged AI Crafting Preparation Overlay
    let isSubmitting = false;
    form.addEventListener('submit', (e) => {
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        const groomName = (groomInput?.value || '').trim();
        const brideName = (brideInput?.value || '').trim();

        if (!groomName || !brideName) {
            e.preventDefault();
            showInlineError('Vui lòng nhập đầy đủ tên Chú rể và Cô dâu để khởi tạo thiệp cưới.');
            if (!groomName && groomInput) groomInput.focus();
            else if (brideInput) brideInput.focus();
            return;
        }

        e.preventDefault();
        isSubmitting = true;
        submitBtn.disabled = true;

        const brandName = document.querySelector('meta[name="application-name"]')?.content?.trim() || 'Mây Hẹn';
        const selectedStyleEl = form.querySelector('input[name="style"]:checked');
        const selectedPaletteEl = form.querySelector('input[name="palette"]:checked');
        const styleLabel = (selectedStyleEl && styleNames[selectedStyleEl.value]) ? styleNames[selectedStyleEl.value] : 'Quiet Luxury';
        const paletteLabel = selectedPaletteEl?.dataset?.name || 'Bảng màu cao cấp';

        const overlay = document.createElement('div');
        overlay.className = 'mayhen_curated_ai_stage_overlay';
        overlay.innerHTML = `
            <div class="ai_stage_dialog" role="status" aria-live="polite">
                <div class="ai_stage_crest">✦ ${brandName.toUpperCase()} &bull; AI ATELIER ✦</div>
                <h2 class="ai_stage_title">Đang Khởi Tạo Thiệp Cưới Độc Bản</h2>
                <p class="ai_stage_couple">${groomName} <span>&amp;</span> ${brideName}</p>
                <div class="ai_stage_progress_track">
                    <div class="ai_stage_progress_fill" id="aiStageProgressFill" style="width: 12%;"></div>
                </div>
                <ul class="ai_stage_checklist" id="aiStageChecklist">
                    <li class="is-active" data-stage-idx="0"><span class="stage_icon">✦</span><span>Phân tích phong cách <strong>${styleLabel}</strong> &amp; hòa sắc <strong>${paletteLabel}</strong>...</span></li>
                    <li data-stage-idx="1"><span class="stage_icon">○</span><span>Khởi tạo các lớp giao diện nền (Canvas) &amp; khung mỹ thuật...</span></li>
                    <li data-stage-idx="2"><span class="stage_icon">○</span><span>AI chắp bút lời ngỏ, trích dẫn tình yêu &amp; cấu trúc từng phần tử...</span></li>
                    <li data-stage-idx="3"><span class="stage_icon">○</span><span>Chuẩn bị sân khấu dựng thiệp trực tiếp từng phần tử...</span></li>
                </ul>
            </div>
        `;
        document.body.appendChild(overlay);

        const fillEl = document.getElementById('aiStageProgressFill');
        const items = overlay.querySelectorAll('#aiStageChecklist li');
        const setStage = (idx, pct) => {
            if (fillEl) fillEl.style.width = `${pct}%`;
            items.forEach((li, i) => {
                const icon = li.querySelector('.stage_icon');
                if (i < idx) {
                    li.classList.remove('is-active');
                    li.classList.add('is-done');
                    if (icon) icon.textContent = '✓';
                } else if (i === idx) {
                    li.classList.add('is-active');
                    if (icon) icon.textContent = '✦';
                }
            });
        };

        setTimeout(() => setStage(1, 38), 750);
        setTimeout(() => setStage(2, 68), 1600);
        setTimeout(() => setStage(3, 92), 2450);
        setTimeout(() => {
            if (fillEl) fillEl.style.width = '100%';
            HTMLFormElement.prototype.submit.call(form);
        }, 3150);
    });
});
