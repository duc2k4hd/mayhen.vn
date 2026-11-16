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
    initSearchableSelects();
});

/**
 * 1. Sidebar Collapse (Mini Icon-Only Mode) & Mobile Drawer
 */
function initSidebar() {
    const layout = document.querySelector('.mayhen_admin_layout');
    const toggleBtn = document.querySelector('.mayhen_admin_sidebar_toggle');
    const backdrop = document.querySelector('.mayhen_admin_backdrop');

    if (!layout || !toggleBtn) return;

    function updateToggleTitle() {
        const isCollapsed = layout.classList.contains('sidebar-collapsed');
        if (isCollapsed) {
            toggleBtn.setAttribute('title', 'Mở rộng menu đầy đủ');
            toggleBtn.setAttribute('aria-label', 'Mở rộng menu đầy đủ');
            toggleBtn.classList.add('is-collapsed');
        } else {
            toggleBtn.setAttribute('title', 'Thu gọn menu (chỉ hiển thị icon)');
            toggleBtn.setAttribute('aria-label', 'Thu gọn menu (chỉ hiển thị icon)');
            toggleBtn.classList.remove('is-collapsed');
        }
    }

    // Restore desktop collapsed state
    const isDesktop = window.innerWidth >= 992;
    if (isDesktop && localStorage.getItem('mayhen_admin_sidebar_collapsed') === 'true') {
        layout.classList.add('sidebar-collapsed');
    }
    updateToggleTitle();

    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (window.innerWidth >= 992) {
            layout.classList.toggle('sidebar-collapsed');
            const isCollapsed = layout.classList.contains('sidebar-collapsed');
            localStorage.setItem('mayhen_admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            updateToggleTitle();
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
        updateToggleTitle();
    });

    initSidebarTooltip();
}

/**
 * Global Floating Tooltip for Collapsed Sidebar Items (Zero Clipping)
 */
function initSidebarTooltip() {
    let tooltip = document.getElementById('mayhen_admin_sidebar_floating_tooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'mayhen_admin_sidebar_floating_tooltip';
        tooltip.className = 'mayhen_admin_sidebar_tooltip';
        tooltip.style.cssText = `
            position: fixed;
            z-index: 10050;
            background: #0F172A;
            color: #FFFFFF;
            font-family: var(--mh-admin-font, 'Open Sans', sans-serif);
            font-size: 0.8125rem;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity 0.15s ease, transform 0.15s ease;
            display: none;
            line-height: 1.4;
        `;
        document.body.appendChild(tooltip);
    }

    const links = document.querySelectorAll('.mayhen_admin_nav_link, .mayhen_admin_btn_view_site');
    links.forEach(link => {
        // Tự động gán data-tooltip nếu chưa có
        if (!link.getAttribute('data-tooltip')) {
            const textSpan = link.querySelector('span:not(.mayhen_admin_nav_icon)');
            if (textSpan) {
                link.setAttribute('data-tooltip', textSpan.textContent.trim());
            }
        }

        link.addEventListener('mouseenter', () => {
            const layout = document.querySelector('.mayhen_admin_layout');
            // Chỉ hiển thị tooltip khi sidebar đang thu gọn trên desktop
            if (!layout || !layout.classList.contains('sidebar-collapsed') || window.innerWidth < 992) {
                return;
            }

            const title = link.getAttribute('data-tooltip');
            if (!title) return;

            tooltip.textContent = title;
            tooltip.style.display = 'block';

            const rect = link.getBoundingClientRect();
            const tooltipHeight = tooltip.offsetHeight || 30;
            const top = rect.top + (rect.height / 2) - (tooltipHeight / 2);
            const left = rect.right + 12;

            tooltip.style.top = `${Math.max(10, top)}px`;
            tooltip.style.left = `${left}px`;

            requestAnimationFrame(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateX(0)';
            });
        });

        link.addEventListener('mouseleave', () => {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateX(-4px)';
            setTimeout(() => {
                if (tooltip.style.opacity === '0') {
                    tooltip.style.display = 'none';
                }
            }, 150);
        });
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
        background: #0F172A;
        color: #FFFFFF;
        padding: 10px 18px;
        border-radius: 8px;
        border-left: 3px solid #2563EB;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        font-size: 0.8125rem;
        font-family: var(--mh-admin-font, 'Open Sans', sans-serif);
        font-weight: 500;
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

/**
 * 5. Chuẩn Hóa Chuỗi Tìm Kiếm Tiếng Việt (Bỏ dấu, lower case)
 */
export function removeVietnameseAccents(str) {
    if (!str) return '';
    return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D');
}
window.removeVietnameseAccents = removeVietnameseAccents;

/**
 * 6. Khởi Tạo Tích Hợp Tìm Kiếm Nhanh Cho Toàn Bộ Thẻ Select (Searchable Selects)
 * Native Vanilla ES Modules - Zero External Libs
 */
export function initSearchableSelects(root = document) {
    const selects = root.querySelectorAll('select.mayhen_admin_form_select:not([data-no-search]), select.mayhen_admin_select:not([data-no-search]), select[data-searchable="true"]');

    selects.forEach(select => {
        // Tránh khởi tạo trùng lặp nếu select đã được bọc
        if (select.closest('.mayhen_admin_select_wrapper')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'mayhen_admin_select_wrapper';
        if (select.id) wrapper.setAttribute('data-select-id', select.id);

        // Kế thừa độ rộng tối đa và chiều rộng nếu có từ thẻ select gốc
        if (select.style.maxWidth) wrapper.style.maxWidth = select.style.maxWidth;
        if (select.style.width && select.style.width !== '100%') wrapper.style.width = select.style.width;

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.classList.add('mayhen_admin_native_select');

        // Trigger Button
        const trigger = document.createElement('div');
        trigger.className = 'mayhen_admin_select_trigger';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-expanded', 'false');

        const label = document.createElement('span');
        label.className = 'mayhen_admin_select_trigger_label';

        const arrow = document.createElement('div');
        arrow.className = 'mayhen_admin_select_trigger_arrow';
        arrow.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>`;

        trigger.appendChild(label);
        trigger.appendChild(arrow);
        wrapper.appendChild(trigger);

        // Dropdown Menu
        const dropdown = document.createElement('div');
        dropdown.className = 'mayhen_admin_select_dropdown';

        // Search Box
        const searchBox = document.createElement('div');
        searchBox.className = 'mayhen_admin_select_search_box';
        searchBox.innerHTML = `
            <svg class="mayhen_admin_select_search_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" class="mayhen_admin_select_search_input" placeholder="🔍 Tìm kiếm nhanh..." autocomplete="off">
        `;
        dropdown.appendChild(searchBox);

        const searchInput = searchBox.querySelector('input');

        // Options List
        const optionsList = document.createElement('ul');
        optionsList.className = 'mayhen_admin_select_options';
        dropdown.appendChild(optionsList);

        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'mayhen_admin_select_empty';
        emptyMsg.textContent = 'Không tìm thấy kết quả phù hợp';
        emptyMsg.style.display = 'none';
        dropdown.appendChild(emptyMsg);

        wrapper.appendChild(dropdown);

        // Render options from native select
        function renderOptions() {
            optionsList.innerHTML = '';
            const selectedOpt = select.options[select.selectedIndex];
            label.textContent = selectedOpt ? selectedOpt.text : 'Chọn một mục...';

            Array.from(select.options).forEach((opt, idx) => {
                const li = document.createElement('li');
                li.className = 'mayhen_admin_select_option' + (opt.selected ? ' is-selected' : '');
                li.textContent = opt.text;
                li.setAttribute('data-value', opt.value);
                li.setAttribute('data-index', idx);

                if (opt.disabled) {
                    li.classList.add('is-disabled');
                    li.style.opacity = '0.5';
                    li.style.pointerEvents = 'none';
                    li.style.cursor = 'not-allowed';
                }

                li.addEventListener('click', (e) => {
                    if (opt.disabled) return;
                    e.stopPropagation();
                    select.selectedIndex = idx;
                    select.value = opt.value;
                    label.textContent = opt.text;

                    // Trigger native change and input events
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    select.dispatchEvent(new Event('input', { bubbles: true }));

                    closeDropdown();
                    trigger.focus();
                });

                optionsList.appendChild(li);
            });
        }

        function filterOptions(query) {
            const normalized = removeVietnameseAccents(query.trim().toLowerCase());
            let visibleCount = 0;
            const items = optionsList.querySelectorAll('.mayhen_admin_select_option');

            items.forEach(item => {
                const text = removeVietnameseAccents(item.textContent.toLowerCase());
                if (!query || text.includes(normalized)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        function openDropdown() {
            // Close other open selects
            document.querySelectorAll('.mayhen_admin_select_wrapper.is-open').forEach(w => {
                if (w !== wrapper) w.classList.remove('is-open');
            });

            wrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            searchInput.value = '';
            filterOptions('');
            setTimeout(() => searchInput.focus(), 50);
        }

        function closeDropdown() {
            wrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (wrapper.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                openDropdown();
            }
        });

        searchInput.addEventListener('input', (e) => {
            filterOptions(e.target.value);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const firstVisible = optionsList.querySelector('.mayhen_admin_select_option:not([style*="display: none"])');
                if (firstVisible) {
                    firstVisible.click();
                }
            }
        });

        // Click outside closes dropdown
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                closeDropdown();
            }
        });

        // Sync if select value changes programmatically
        select.addEventListener('change', () => {
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt) {
                label.textContent = selectedOpt.text;
                optionsList.querySelectorAll('.mayhen_admin_select_option').forEach(li => {
                    li.classList.toggle('is-selected', li.getAttribute('data-value') === select.value);
                });
            }
        });

        renderOptions();
    });
}
window.initSearchableSelects = initSearchableSelects;

