/**
 * MAYHEN.VN — HOME PAGE INTERACTIVE CONTROLLER
 * Vanilla ES Module
 */
import { $, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const phoneScreen = $('.mayhen_home_phone_screen');
    const previewStamp = $('.mayhen_home_card_envelope_stamp');

    if (previewStamp && phoneScreen) {
        on(previewStamp, 'click', () => {
            previewStamp.style.transform = 'scale(1.2) rotate(10deg)';
            setTimeout(() => {
                previewStamp.style.transform = 'scale(1) rotate(0deg)';
            }, 300);
        });
    }
});
