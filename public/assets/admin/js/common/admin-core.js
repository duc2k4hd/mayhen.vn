/**
 * MAYHEN.VN — ADMIN PANEL CORE JAVASCRIPT
 * Native Vanilla ES Modules (Zero Frameworks, Zero Dependencies)
 * Prefix: mayhen_admin_*
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initUserDropdown();
    initCopyHelpers();
    initCommandPalette();
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
 * 4. Command Palette (Ctrl+K / Quick Navigation)
 */
function initCommandPalette() {
    const trigger = document.getElementById('mayhen_admin_command_trigger');
    const palette = document.getElementById('mayhen_admin_command_palette');
    const input = document.getElementById('mayhen_admin_command_input');
    const list = document.getElementById('mayhen_admin_command_list');

    if (!palette || !input || !list) return;

    const openPalette = () => {
        palette.classList.add('is-open');
        input.value = '';
        filterItems('');
        setTimeout(() => input.focus(), 50);
    };

    const closePalette = () => {
        palette.classList.remove('is-open');
    };

    if (trigger) {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openPalette();
        });
    }

    palette.addEventListener('click', (e) => {
        if (e.target === palette) {
            closePalette();
        }
    });

    document.addEventListener('keydown', (e) => {
        // Ctrl+K or Cmd+K
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (palette.classList.contains('is-open')) {
                closePalette();
            } else {
                openPalette();
            }
        }

        // Press / when not inside input or textarea
        if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            openPalette();
        }

        // Escape closes palette
        if (e.key === 'Escape' && palette.classList.contains('is-open')) {
            e.preventDefault();
            closePalette();
        }
    });

    function filterItems(query) {
        const q = query.trim().toLowerCase();
        const items = list.querySelectorAll('.mayhen_admin_cmd_item');
        let hasVisible = false;

        items.forEach((item, index) => {
            const text = (item.getAttribute('data-search') || item.innerText).toLowerCase();
            if (!q || text.includes(q)) {
                item.style.display = 'flex';
                hasVisible = true;
                if (!list.querySelector('.mayhen_admin_cmd_item.is-selected')) {
                    item.classList.add('is-selected');
                }
            } else {
                item.style.display = 'none';
                item.classList.remove('is-selected');
            }
        });

        // Ensure first visible item is selected
        const visibleItems = Array.from(items).filter((it) => it.style.display !== 'none');
        visibleItems.forEach((it, idx) => {
            it.classList.toggle('is-selected', idx === 0);
        });
    }

    input.addEventListener('input', () => {
        filterItems(input.value);
    });

    input.addEventListener('keydown', (e) => {
        const visibleItems = Array.from(list.querySelectorAll('.mayhen_admin_cmd_item')).filter(
            (it) => it.style.display !== 'none'
        );

        if (!visibleItems.length) return;

        let currentIndex = visibleItems.findIndex((it) => it.classList.contains('is-selected'));

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentIndex < visibleItems.length - 1) {
                visibleItems[currentIndex]?.classList.remove('is-selected');
                visibleItems[currentIndex + 1]?.classList.add('is-selected');
                visibleItems[currentIndex + 1]?.scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentIndex > 0) {
                visibleItems[currentIndex]?.classList.remove('is-selected');
                visibleItems[currentIndex - 1]?.classList.add('is-selected');
                visibleItems[currentIndex - 1]?.scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const selected = visibleItems[currentIndex] || visibleItems[0];
            if (selected) {
                window.location.href = selected.href;
            }
        }
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

