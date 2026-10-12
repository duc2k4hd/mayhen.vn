/**
 * MAYHEN.VN — HOME PAGE INTERACTIVE CONTROLLER
 * Vanilla ES Module
 */
import { $, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const previewStamp = $('.mayhen_home_card_envelope_stamp');
    if (previewStamp) {
        on(previewStamp, 'click', () => {
            previewStamp.style.transform = 'scale(1.2) rotate(10deg)';
            setTimeout(() => {
                previewStamp.style.transform = 'scale(1) rotate(0deg)';
            }, 300);
        });
    }

    // Micro-interactions for trio mockup seals & wax seals
    const seals = document.querySelectorAll('.mayhen_home_mockup_monogram_seal, .mayhen_home_mockup_hysymbol, .mayhen_home_mockup_wax_seal');
    seals.forEach(seal => {
        on(seal, 'click', () => {
            seal.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
            seal.style.transform = 'scale(1.25) rotate(12deg)';
            setTimeout(() => {
                seal.style.transform = 'scale(1) rotate(0deg)';
            }, 350);
        });
    });

    // RSVP Button Interactive Click Demo
    const rsvpBtn = $('.mayhen_home_mockup_rsvp_cta');
    if (rsvpBtn) {
        on(rsvpBtn, 'click', (e) => {
            e.stopPropagation();
            const originalText = rsvpBtn.innerHTML;
            rsvpBtn.innerHTML = '<span>✓ Đã Nhận Phản Hồi! ✨</span>';
            rsvpBtn.style.background = '#4A7C59';
            setTimeout(() => {
                rsvpBtn.innerHTML = originalText;
                rsvpBtn.style.background = '';
            }, 2500);
        });
    }

    // Envelope CTA Button Click Demo
    const envCta = $('.mayhen_home_mockup_envelope_cta');
    if (envCta) {
        on(envCta, 'click', (e) => {
            e.stopPropagation();
            envCta.style.transform = 'scale(0.95)';
            setTimeout(() => {
                envCta.style.transform = '';
            }, 200);
        });
    }
});

