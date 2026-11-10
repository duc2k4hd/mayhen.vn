/**
 * MAYHEN.VN — ONBOARDING WIZARD CONTROLLER
 * Vanilla ES Module (Zero External Library)
 */

document.addEventListener('DOMContentLoaded', () => {
    const config = window.MAYHEN_ONBOARDING_CONFIG || {};
    let currentStep = config.initialStep || 1;

    // Elements
    const panes = {
        1: document.getElementById('onboarding-step-1'),
        2: document.getElementById('onboarding-step-2'),
        3: document.getElementById('onboarding-step-3'),
        4: document.getElementById('onboarding-step-4')
    };

    const nodes = document.querySelectorAll('.mayhen_onboarding_node');
    const progressFill = document.getElementById('onboarding-progress-fill');

    // Step 1: Intent Cards
    const intentCards = document.querySelectorAll('.mayhen_onboarding_intent_card');
    intentCards.forEach(card => {
        card.addEventListener('click', () => {
            intentCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                const finalRole = document.getElementById('final-role-intent');
                if (finalRole) finalRole.value = radio.value;
            }
        });
    });

    // Step 2: Template Cards
    const templateCards = document.querySelectorAll('.mayhen_onboarding_template_card');
    templateCards.forEach(card => {
        const selectBtn = card.querySelector('.mayhen_onboarding_btn_select');
        const handleSelect = () => {
            templateCards.forEach(c => {
                c.classList.remove('selected');
                const btn = c.querySelector('.mayhen_onboarding_btn_select');
                if (btn) {
                    btn.classList.remove('active');
                    btn.textContent = 'Chọn Mẫu Này';
                }
            });

            card.classList.add('selected');
            if (selectBtn) {
                selectBtn.classList.add('active');
                selectBtn.textContent = '✓ Đã Chọn';
            }

            const tmplId = card.getAttribute('data-template-id');
            const finalTmpl = document.getElementById('final-template-id');
            if (finalTmpl) finalTmpl.value = tmplId;
        };

        if (selectBtn) {
            selectBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                handleSelect();
            });
        }

        card.addEventListener('click', handleSelect);
    });

    // Navigation functions
    function goToStep(step) {
        if (!panes[step]) return;

        currentStep = step;

        // Hide all panes
        Object.keys(panes).forEach(k => {
            if (panes[k]) panes[k].style.display = (parseInt(k, 10) === step) ? 'block' : 'none';
        });

        // Update progress bar
        const percentages = { 1: 25, 2: 50, 3: 75, 4: 100 };
        if (progressFill) {
            progressFill.style.width = (percentages[step] || 25) + '%';
        }

        // Update nodes
        nodes.forEach(node => {
            const nodeStep = parseInt(node.getAttribute('data-step-node'), 10);
            node.classList.remove('active', 'completed');
            if (nodeStep === step) {
                node.classList.add('active');
            } else if (nodeStep < step) {
                node.classList.add('completed');
            }
        });

        // Window scroll to top of wizard
        window.scrollTo({ top: 80, behavior: 'smooth' });

        // If step 4, update summary
        if (step === 4) {
            updateStep4Summary();
        }
    }

    function updateStep4Summary() {
        const groomInput = document.getElementById('wizard-groom-name');
        const brideInput = document.getElementById('wizard-bride-name');
        const dateInput = document.getElementById('wizard-wedding-date');
        const venueInput = document.getElementById('wizard-venue-name');
        const addressInput = document.getElementById('wizard-venue-address');

        const selectedTemplateCard = document.querySelector('.mayhen_onboarding_template_card.selected');
        const templateName = selectedTemplateCard ? selectedTemplateCard.getAttribute('data-template-name') : 'Chưa chọn';

        const groom = groomInput ? groomInput.value.trim() : '';
        const bride = brideInput ? brideInput.value.trim() : '';
        const date = dateInput ? dateInput.value : '';
        const venue = venueInput ? venueInput.value.trim() : '';
        const address = addressInput ? addressInput.value.trim() : '';

        // Update hidden inputs for final submission
        const finalGroom = document.getElementById('final-groom-name');
        const finalBride = document.getElementById('final-bride-name');
        const finalDate = document.getElementById('final-wedding-date');
        const finalVenue = document.getElementById('final-venue-name');
        const finalAddress = document.getElementById('final-venue-address');

        if (finalGroom) finalGroom.value = groom;
        if (finalBride) finalBride.value = bride;
        if (finalDate) finalDate.value = date;
        if (finalVenue) finalVenue.value = venue;
        if (finalAddress) finalAddress.value = address;

        // Render summary text
        const summaryTmpl = document.getElementById('summary-template-name');
        const summaryCouple = document.getElementById('summary-couple-names');
        const summaryDate = document.getElementById('summary-wedding-date');
        const summaryVenue = document.getElementById('summary-venue-details');

        if (summaryTmpl) summaryTmpl.textContent = templateName;
        if (summaryCouple) summaryCouple.textContent = `${groom || 'Chú Rể'} & ${bride || 'Cô Dâu'}`;
        
        if (summaryDate) {
            if (date) {
                const parts = date.split('-');
                summaryDate.textContent = `${parts[2]}/${parts[1]}/${parts[0]}`;
            } else {
                summaryDate.textContent = 'Chưa xác định';
            }
        }

        if (summaryVenue) {
            summaryVenue.textContent = venue ? `${venue} (${address || 'TP.HCM'})` : (address || 'TP. Hồ Chí Minh');
        }
    }

    async function saveStepAsync(stepNumber, payload) {
        if (!config.saveStepUrl) return;

        try {
            await fetch(config.saveStepUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    step: stepNumber,
                    ...payload
                })
            });
        } catch (e) {
            console.warn('Onboarding autosave silent notification:', e);
        }
    }

    // Step 1 Button
    const btnNext1 = document.getElementById('btn-next-step-1');
    if (btnNext1) {
        btnNext1.addEventListener('click', async () => {
            const checkedRadio = document.querySelector('input[name="wizard_role_intent"]:checked');
            const intentVal = checkedRadio ? checkedRadio.value : 'couple';
            await saveStepAsync(1, { role_intent: intentVal });
            goToStep(2);
        });
    }

    // Step 2 Buttons
    const btnBack2 = document.getElementById('btn-back-step-2');
    if (btnBack2) {
        btnBack2.addEventListener('click', () => goToStep(1));
    }

    const btnNext2 = document.getElementById('btn-next-step-2');
    if (btnNext2) {
        btnNext2.addEventListener('click', async () => {
            const selectedTmpl = document.querySelector('.mayhen_onboarding_template_card.selected');
            if (!selectedTmpl) {
                alert('Vui lòng chọn một mẫu thiệp cưới để tiếp tục.');
                return;
            }
            const tmplId = selectedTmpl.getAttribute('data-template-id');
            await saveStepAsync(2, { template_id: tmplId });
            goToStep(3);
        });
    }

    // Step 3 Buttons
    const btnBack3 = document.getElementById('btn-back-step-3');
    if (btnBack3) {
        btnBack3.addEventListener('click', () => goToStep(2));
    }

    const btnNext3 = document.getElementById('btn-next-step-3');
    if (btnNext3) {
        btnNext3.addEventListener('click', async () => {
            const groomInput = document.getElementById('wizard-groom-name');
            const brideInput = document.getElementById('wizard-bride-name');
            const dateInput = document.getElementById('wizard-wedding-date');
            const venueInput = document.getElementById('wizard-venue-name');
            const addressInput = document.getElementById('wizard-venue-address');

            if (!groomInput || !groomInput.value.trim()) {
                alert('Vui lòng nhập tên Chú Rể.');
                if (groomInput) groomInput.focus();
                return;
            }

            if (!brideInput || !brideInput.value.trim()) {
                alert('Vui lòng nhập tên Cô Dâu.');
                if (brideInput) brideInput.focus();
                return;
            }

            if (!dateInput || !dateInput.value) {
                alert('Vui lòng chọn ngày tổ chức hôn lễ.');
                if (dateInput) dateInput.focus();
                return;
            }

            const payload = {
                groom_name: groomInput.value.trim(),
                bride_name: brideInput.value.trim(),
                wedding_date: dateInput.value,
                venue_name: venueInput ? venueInput.value.trim() : '',
                venue_address: addressInput ? addressInput.value.trim() : ''
            };

            await saveStepAsync(3, payload);
            goToStep(4);
        });
    }

    // Step 4 Buttons
    const btnBack4 = document.getElementById('btn-back-step-4');
    if (btnBack4) {
        btnBack4.addEventListener('click', () => goToStep(3));
    }

    // Initialize initial step from draft
    if (currentStep > 1 && currentStep <= 4) {
        goToStep(currentStep);
    }
});

