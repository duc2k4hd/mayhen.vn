/**
 * MAYHEN.VN — HEADER COMPONENT CONTROLLER
 * Mobile drawer toggle, scroll elevation
 */
import { $, on, toggleClass } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = $('[data-mayhen-action="toggle-mobile-menu"]');
    const drawer = $('[data-mayhen-component="mobile-drawer"]');
    const header = $('.mayhen_client_header_wrapper');

    if (toggleBtn && drawer) {
        on(toggleBtn, 'click', () => {
            const isOpen = drawer.classList.contains('mayhen_client_header_drawer_open');
            toggleClass(drawer, 'mayhen_client_header_drawer_open', !isOpen);
            toggleBtn.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
        });
    }

    // Elevation on scroll
    let lastScrollY = window.scrollY;
    on(window, 'scroll', () => {
        if (!header) return;
        if (window.scrollY > 20) {
            header.style.boxShadow = '0 4px 20px -2px rgba(28, 25, 23, 0.05)';
        } else {
            header.style.boxShadow = 'none';
        }
    }, { passive: true });
});
