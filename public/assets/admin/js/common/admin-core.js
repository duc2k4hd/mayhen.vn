/**
 * MAYHEN.VN — ADMIN PANEL CORE JAVASCRIPT
 * Native Vanilla ES Modules (Zero Frameworks, Zero Dependencies)
 * Prefix: mayhen_admin_*
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initUserDropdown();
    initCopyHelpers();
});

/**
 * 1. Sidebar Collapse & Mobile Drawer
 */
function initSidebar() {
    const layout = document.querySelector('.mayhen_admin_layout');
    const toggleBtn = document.querySelector('.mayhen_admin_sidebar_toggle');
    const backdrop = document.querySelector('.mayhen_admin_backdrop');

    if (!layout || !toggleBtn) return;

    // Restore desktop collapsed state
    const isDesktop = window.innerWidth >= 992;
    if (isDesktop && localStorage.getItem('mayhen_admin_sidebar_collapsed') === 'true') {
        layout.classList.add('sidebar-collapsed');
    }

    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (window.innerWidth >= 992) {
            layout.classList.toggle('sidebar-collapsed');
            const isCollapsed = layout.classList.contains('sidebar-collapsed');
            localStorage.setItem('mayhen_admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        } else {
            layout.classList.toggle('sidebar-mobile-open');
        }
    });

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            layout.classList.remove('sidebar-mobile-open');
        });
    }

    // Auto close drawer when resizing to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            layout.classList.remove('sidebar-mobile-open');
        }
    });
}

/**
 * 2. User Profile Dropdown
 */
function initUserDropdown() {
    const userPill = document.querySelector('.mayhen_admin_user_pill');
    const userDropdown = document.querySelector('.mayhen_admin_user_dropdown');

    if (!userPill || !userDropdown) return;

    userPill.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!userDropdown.contains(e.target) && !userPill.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            userDropdown.classList.remove('show');
        }
    });
}

/**
 * 3. Quick Copy to Clipboard Helpers
 */
function initCopyHelpers() {
    document.querySelectorAll('[data-copy-target]').forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const textToCopy = btn.getAttribute('data-copy-target') || btn.innerText;
            if (!textToCopy) return;

            try {
                await navigator.clipboard.writeText(textToCopy);
                showToast('Đã sao chép vào bộ nhớ tạm: ' + textToCopy);
            } catch (err) {
                // Fallback
                const tempInput = document.createElement('input');
                tempInput.value = textToCopy;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showToast('Đã sao chép: ' + textToCopy);
            }
        });
    });
}

/**
 * Global Admin Toast Notification
 */
export function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('mayhen_admin_toast_container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'mayhen_admin_toast_container';
        toastContainer.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        `;
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: #1B1815;
        color: #FFFFFF;
        padding: 10px 18px;
        border-radius: 8px;
        border-left: 3px solid #C5A880;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        font-size: 0.8125rem;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: auto;
    `;
    toast.textContent = message;
    toastContainer.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 250);
    }, 2500);
}

// Make accessible globally
window.mayhenAdminToast = showToast;

