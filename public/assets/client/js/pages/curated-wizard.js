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

    // Form submit guard
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

        isSubmitting = true;
        submitBtn.disabled = true;
        const textSpan = submitBtn.querySelector('.btn_text');
        const subSpan = submitBtn.querySelector('.btn_sub');

        if (textSpan) textSpan.textContent = '✦ Đang Dựng Bản Thiệp Độc Bản... ✦';
        if (subSpan) {
            const brandName = document.querySelector('meta[name="application-name"]')?.content?.trim() || 'Mây Hẹn';
            subSpan.textContent = `${brandName} đang phối bố cục & AI đang chắp bút lời ngỏ`;
        }
    });
});
