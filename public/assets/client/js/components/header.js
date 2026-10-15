/**
 * MAYHEN.VN — HEADER COMPONENT CONTROLLER
 * Mobile drawer toggle, scroll lock & scroll elevation
 */
import { $, $$, on, toggleClass } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = $('[data-mayhen-action="toggle-mobile-menu"]');
    const drawer = $('[data-mayhen-component="mobile-drawer"]');
    const header = $('.mayhen_client_header_wrapper');

    if (toggleBtn && drawer && header) {
        const closeMenu = () => {
            drawer.classList.remove('mayhen_client_header_drawer_open');
            header.classList.remove('mayhen_client_header_open');
            toggleBtn.classList.remove('mayhen_client_header_toggle_active');
            toggleBtn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('mayhen_no_scroll');
        };

        const openMenu = () => {
            drawer.classList.add('mayhen_client_header_drawer_open');
            header.classList.add('mayhen_client_header_open');
            toggleBtn.classList.add('mayhen_client_header_toggle_active');
            toggleBtn.setAttribute('aria-expanded', 'true');
            document.body.classList.add('mayhen_no_scroll');
        };

        on(toggleBtn, 'click', (e) => {
            e.stopPropagation();
            const isOpen = drawer.classList.contains('mayhen_client_header_drawer_open');
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Đóng menu khi click vào bất kỳ link điều hướng nào trong drawer
        const drawerLinks = $$('.mayhen_client_header_drawer_link, .mayhen_client_header_drawer a', drawer);
        drawerLinks.forEach(link => {
            on(link, 'click', () => {
                closeMenu();
            });
        });

        // Đóng menu khi bấm phím Escape
        on(document, 'keydown', (e) => {
            if (e.key === 'Escape' && drawer.classList.contains('mayhen_client_header_drawer_open')) {
                closeMenu();
            }
        });

        // Đóng menu khi resize lên desktop (>= 860px)
        on(window, 'resize', () => {
            if (window.innerWidth >= 860 && drawer.classList.contains('mayhen_client_header_drawer_open')) {
                closeMenu();
            }
        });
    }

    // Hiệu ứng bóng đổ khi cuộn trang
    on(window, 'scroll', () => {
        if (!header) return;
        if (window.scrollY > 20) {
            header.style.boxShadow = '0 4px 20px -2px rgba(28, 25, 23, 0.06)';
        } else {
            header.style.boxShadow = 'none';
        }
    }, { passive: true });
});
