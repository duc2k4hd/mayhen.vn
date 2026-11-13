/**
 * MÂY HẸN (MAYHEN.VN) — DOCUMENTATION CENTER SCRIPT
 * Pure Vanilla JavaScript (Zero external framework, zero dependencies)
 * Handles both Catalog Index (#mayhen_docs_app) and Article Detail (#mayhen_docs_detail_app)
 * Features:
 * - Dynamic full-text Vietnamese unaccent search
 * - Keyboard navigation (Ctrl+K, Up/Down, Enter, Esc)
 * - Reading progress indicator & Back-to-top
 * - Table of Contents scrollspy & smooth scrolling
 * - Light / Dark / System theme switcher & font scaling
 * - Mobile sidebar drawers & accordions
 * - Native <dialog> modal controller
 * - Code copy blocks & social sharing
 */

(() => {
  'use strict';

  /* ============================================================
     SHARED UTILITIES
  ============================================================ */
  const SafeStorage = {
    get(key, fallback = null) {
      try {
        const val = localStorage.getItem(key);
        return val === null ? fallback : val;
      } catch (e) {
        return fallback;
      }
    },
    set(key, val) {
      try {
        localStorage.setItem(key, String(val));
        return true;
      } catch (e) {
        return false;
      }
    }
  };

  /**
   * Bỏ dấu tiếng Việt và chuẩn hóa tìm kiếm
   */
  function removeVietnameseTones(str) {
    if (!str) return '';
    return str
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[đĐ]/g, 'd')
      .replace(/[^a-z0-9\s]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  /**
   * Sao chép nội dung vào Clipboard
   */
  async function copyToClipboard(text) {
    if (!text) return false;
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
      }
    } catch (e) {}

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '-9999px';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    let success = false;
    try {
      success = document.execCommand('copy');
    } catch (e) {}
    textarea.remove();
    return success;
  }

  /* ============================================================
     MODULE 1: INDEX CATALOG CONTROLLER (#mayhen_docs_app)
  ============================================================ */
  function initDocsIndex(root) {
    const space = root.dataset.space || 'client';
    const isDark = SafeStorage.get('mayhen_docs_theme') === 'dark';
    if (isDark) root.dataset.theme = 'dark';

    // Parse Search Data from Embedded JSON
    let searchData = [];
    const searchDataScript = document.getElementById('mayhen_docs_search_data');
    if (searchDataScript) {
      try {
        searchData = JSON.parse(searchDataScript.textContent || '[]');
      } catch (e) {
        searchData = [];
      }
    }

    // Elements
    const searchDialog = document.getElementById('mayhen_docs_dialog_search');
    const searchInput = document.getElementById('mayhen_docs_search_input');
    const searchResults = document.getElementById('mayhen_docs_search_results');
    const progressBar = document.getElementById('mayhen_docs_progress');
    const toTopBtn = document.getElementById('mayhen_docs_to_top');
    const toastEl = document.getElementById('mayhen_docs_toast');

    let toastTimer = null;
    function showToast(msg) {
      if (!toastEl) return;
      window.clearTimeout(toastTimer);
      toastEl.textContent = msg;
      toastEl.classList.add('mayhen_docs_toast_show');
      toastTimer = window.setTimeout(() => toastEl.classList.remove('mayhen_docs_toast_show'), 3000);
    }

    // Modal Control
    function openSearchModal(initialQuery = '') {
      if (!searchDialog || typeof searchDialog.showModal !== 'function') return;
      if (!searchDialog.open) {
        searchDialog.showModal();
        document.body.style.overflow = 'hidden';
      }
      if (searchInput) {
        searchInput.value = initialQuery;
        renderSearchResults(initialQuery);
        requestAnimationFrame(() => searchInput.focus());
      }
    }

    function closeSearchModal() {
      if (searchDialog && searchDialog.open) {
        searchDialog.close();
        document.body.style.overflow = '';
      }
    }

    // Triggers
    const searchTrigger = document.getElementById('mayhen_docs_search_trigger');
    if (searchTrigger) searchTrigger.addEventListener('click', () => openSearchModal());

    const mobileSearchTrigger = document.getElementById('mayhen_docs_mobile_search');
    if (mobileSearchTrigger) mobileSearchTrigger.addEventListener('click', () => openSearchModal());

    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', () => {
        const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = next;
        SafeStorage.set('mayhen_docs_theme', next);
        showToast(next === 'dark' ? 'Đã chuyển sang chế độ tối.' : 'Đã chuyển sang chế độ sáng.');
      });
    }

    const closeBtn = searchDialog?.querySelector('[data-close]');
    if (closeBtn) closeBtn.addEventListener('click', closeSearchModal);

    if (searchDialog) {
      searchDialog.addEventListener('click', (e) => {
        if (e.target === searchDialog) closeSearchModal();
      });
      searchDialog.addEventListener('close', () => {
        document.body.style.overflow = '';
      });
    }

    // Search Execution
    let activeResultIndex = -1;
    let currentResults = [];

    function renderSearchResults(query) {
      if (!searchResults) return;
      searchResults.replaceChildren();
      activeResultIndex = -1;

      const cleanQuery = removeVietnameseTones(query);
      const tokens = cleanQuery.split(/\s+/).filter(Boolean);

      if (tokens.length === 0) {
        currentResults = searchData.slice(0, 8);
      } else {
        currentResults = searchData.filter(item => {
          const titleClean = removeVietnameseTones(item.title || '');
          const catClean = removeVietnameseTones(item.category || '');
          return tokens.every(token => titleClean.includes(token) || catClean.includes(token));
        }).slice(0, 15);
      }

      if (currentResults.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'mayhen_docs_no_results';
        empty.style.padding = '24px 16px';
        empty.style.textAlign = 'center';
        empty.style.color = 'var(--mayhen-docs-muted, #7A726A)';
        empty.style.fontSize = '0.875rem';
        empty.textContent = 'Chưa tìm thấy bài viết phù hợp với từ khóa "' + query + '". Hãy thử từ khóa ngắn hơn.';
        searchResults.appendChild(empty);
        return;
      }

      currentResults.forEach((item, index) => {
        const link = document.createElement('a');
        link.className = 'mayhen_docs_search_result_item';
        link.href = item.url;
        link.setAttribute('role', 'option');
        link.setAttribute('aria-selected', 'false');
        link.style.display = 'block';
        link.style.padding = '10px 14px';
        link.style.borderRadius = '8px';
        link.style.textDecoration = 'none';
        link.style.color = 'inherit';
        link.style.borderBottom = '1px solid var(--mayhen-docs-border, #EFE8DD)';
        link.style.transition = 'background 0.15s ease';

        link.innerHTML = `
          <div style="font-weight: 600; font-size: 0.9375rem; color: var(--mayhen-docs-charcoal, #1F1D1A);">${item.title}</div>
          <div style="font-size: 0.75rem; color: var(--mayhen-docs-gold, #C5A880); margin-top: 2px;">${item.category || 'Mục tài liệu'}</div>
        `;

        link.addEventListener('mouseenter', () => highlightResult(index));
        searchResults.appendChild(link);
      });
    }

    function highlightResult(index) {
      if (!searchResults) return;
      const items = searchResults.querySelectorAll('.mayhen_docs_search_result_item');
      items.forEach((item, idx) => {
        const isActive = idx === index;
        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
        item.style.background = isActive ? 'var(--mayhen-docs-rose-soft, rgba(197, 168, 128, 0.15))' : '';
      });
      activeResultIndex = index;
    }

    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        renderSearchResults(e.target.value);
      });

      searchInput.addEventListener('keydown', (e) => {
        const items = searchResults ? searchResults.querySelectorAll('.mayhen_docs_search_result_item') : [];
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const nextIndex = activeResultIndex < items.length - 1 ? activeResultIndex + 1 : 0;
          highlightResult(nextIndex);
          items[nextIndex]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prevIndex = activeResultIndex > 0 ? activeResultIndex - 1 : items.length - 1;
          highlightResult(prevIndex);
          items[prevIndex]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
          if (activeResultIndex >= 0 && items[activeResultIndex]) {
            e.preventDefault();
            items[activeResultIndex].click();
          }
        }
      });
    }

    // Trending Tags (Chips)
    document.querySelectorAll('[data-search-term]').forEach(btn => {
      btn.addEventListener('click', () => {
        const term = btn.dataset.searchTerm || '';
        openSearchModal(term);
      });
    });

    // Mobile Sidebar Drawer
    const sidebar = document.getElementById('mayhen_docs_sidebar');
    const menuBtn = document.getElementById('mayhen_docs_menu_button');
    const closeSidebarBtn = document.getElementById('mayhen_docs_sidebar_close');
    const scrim = document.getElementById('mayhen_docs_scrim');

    function toggleMobileSidebar(open) {
      if (!sidebar) return;
      if (open) {
        sidebar.classList.add('mayhen_docs_sidebar_open');
        if (scrim) scrim.hidden = false;
        document.body.style.overflow = 'hidden';
      } else {
        sidebar.classList.remove('mayhen_docs_sidebar_open');
        if (scrim) scrim.hidden = true;
        document.body.style.overflow = '';
      }
    }

    if (menuBtn) menuBtn.addEventListener('click', () => toggleMobileSidebar(true));
    if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', () => toggleMobileSidebar(false));
    if (scrim) scrim.addEventListener('click', () => toggleMobileSidebar(false));

    // Sidebar Category Accordions
    document.querySelectorAll('.mayhen_docs_side_group').forEach(btn => {
      btn.addEventListener('click', () => {
        const groupKey = btn.dataset.group;
        const targetList = document.getElementById('mayhen_docs_group_' + groupKey);
        if (targetList) {
          const isClosed = targetList.classList.contains('mayhen_docs_group_closed');
          if (isClosed) {
            targetList.classList.remove('mayhen_docs_group_closed');
            btn.setAttribute('aria-expanded', 'true');
          } else {
            targetList.classList.add('mayhen_docs_group_closed');
            btn.setAttribute('aria-expanded', 'false');
          }
        }
      });
    });

    // Scroll Progress & Back-to-Top
    function updateScrollState() {
      const scrollY = window.scrollY || document.documentElement.scrollTop;
      const totalH = document.documentElement.scrollHeight - window.innerHeight;

      if (progressBar) {
        const pct = totalH > 0 ? Math.min(100, Math.max(0, (scrollY / totalH) * 100)) : 0;
        progressBar.style.width = pct + '%';
      }

      if (toTopBtn) {
        toTopBtn.classList.toggle('mayhen_docs_to_top_visible', scrollY > 350);
      }
    }

    window.addEventListener('scroll', updateScrollState, { passive: true });
    window.addEventListener('resize', updateScrollState, { passive: true });
    updateScrollState();

    if (toTopBtn) {
      toTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    const footerTop = document.getElementById('mayhen_docs_footer_top');
    if (footerTop) {
      footerTop.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    // Global Hotkeys
    document.addEventListener('keydown', (e) => {
      const isInput = e.target.matches && e.target.matches('input, textarea, [contenteditable="true"]');
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        openSearchModal();
        return;
      }
      if (!isInput && e.key === '/') {
        e.preventDefault();
        openSearchModal();
        return;
      }
      if (e.key === 'Escape') {
        closeSearchModal();
        toggleMobileSidebar(false);
      }
    });
  }

  /* ============================================================
     MODULE 2: DETAIL ARTICLE CONTROLLER (#mayhen_docs_detail_app)
  ============================================================ */
  function initDocsDetail(app) {
    const PREFIX = 'mayhen_docs_detail_';
    const byId = (suffix) => document.getElementById(PREFIX + suffix);

    // Initial Preferences
    let currentTheme = SafeStorage.get('mayhen_docs_theme', 'light');
    let currentWidth = SafeStorage.get('mayhen_docs_width', 'comfortable');
    let currentFont = Number(SafeStorage.get('mayhen_docs_font', '16')) || 16;
    let isFocus = SafeStorage.get('mayhen_docs_focus') === 'true';

    function applyTheme(theme) {
      currentTheme = theme;
      if (theme === 'system') {
        const isSysDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        app.dataset.theme = isSysDark ? 'dark' : 'light';
      } else {
        app.dataset.theme = theme;
      }
      SafeStorage.set('mayhen_docs_theme', theme);
      document.querySelectorAll('[data-theme-option]').forEach(btn => {
        btn.setAttribute('aria-pressed', String(btn.dataset.themeOption === theme));
      });
    }

    function applyWidth(width) {
      currentWidth = width;
      app.dataset.width = width;
      SafeStorage.set('mayhen_docs_width', width);
      document.querySelectorAll('[data-width-option]').forEach(btn => {
        btn.setAttribute('aria-pressed', String(btn.dataset.widthOption === width));
      });
    }

    function applyFont(size) {
      currentFont = Math.max(14, Math.min(22, size));
      app.style.setProperty('--mayhen-docs-detail-font-size', currentFont + 'px');
      SafeStorage.set('mayhen_docs_font', currentFont);
      const fontValEl = byId('settings_font_value');
      if (fontValEl) fontValEl.textContent = currentFont + 'px';
    }

    function applyFocus(on) {
      isFocus = Boolean(on);
      app.classList.toggle(PREFIX + 'is_focus', isFocus);
      SafeStorage.set('mayhen_docs_focus', String(isFocus));
      const focusToggle = byId('focus_toggle');
      if (focusToggle) focusToggle.setAttribute('aria-pressed', String(isFocus));
    }

    applyTheme(currentTheme);
    applyWidth(currentWidth);
    applyFont(currentFont);
    applyFocus(isFocus);

    // Toast
    const toastEl = byId('toast');
    let toastTimer = null;
    function showToast(msg) {
      if (!toastEl) return;
      window.clearTimeout(toastTimer);
      toastEl.textContent = msg;
      toastEl.classList.add(PREFIX + 'visible');
      toastTimer = window.setTimeout(() => toastEl.classList.remove(PREFIX + 'visible'), 3000);
    }

    // Parse Search Data
    let searchData = [];
    const searchScript = document.getElementById('mayhen_docs_detail_search_data');
    if (searchScript) {
      try {
        searchData = JSON.parse(searchScript.textContent || '[]');
      } catch (e) {
        searchData = [];
      }
    }

    // Modal Dialog Manager
    const dialogs = Array.from(app.querySelectorAll('dialog'));
    function openDialog(id) {
      const dlg = document.getElementById(id);
      if (!dlg || typeof dlg.showModal !== 'function') return;
      dialogs.forEach(d => { if (d.open && d !== dlg) d.close(); });
      dlg.showModal();
      document.body.style.overflow = 'hidden';

      if (id === PREFIX + 'search_dialog') {
        const input = byId('search_input');
        if (input) {
          input.value = '';
          renderDetailSearch('');
          requestAnimationFrame(() => input.focus());
        }
      }
      if (id === PREFIX + 'share_dialog') {
        const shareInput = byId('share_input');
        if (shareInput) shareInput.value = window.location.href;
      }
    }

    function closeDialog(dlg) {
      if (dlg && dlg.open) {
        dlg.close();
        document.body.style.overflow = '';
      }
    }

    document.querySelectorAll('[data-modal]').forEach(btn => {
      btn.addEventListener('click', () => openDialog(btn.dataset.modal));
    });

    document.querySelectorAll('[data-close-dialog]').forEach(btn => {
      btn.addEventListener('click', () => closeDialog(btn.closest('dialog')));
    });

    dialogs.forEach(dlg => {
      dlg.addEventListener('click', (e) => {
        if (e.target === dlg) closeDialog(dlg);
      });
      dlg.addEventListener('close', () => {
        if (!dialogs.some(d => d.open)) document.body.style.overflow = '';
      });
    });

    // Theme Switchers
    const themeQuickBtn = byId('theme_quick');
    if (themeQuickBtn) {
      themeQuickBtn.addEventListener('click', () => {
        const next = app.dataset.theme === 'light' ? 'dark' : 'light';
        applyTheme(next);
        showToast(next === 'dark' ? 'Đã chuyển sang chế độ tối.' : 'Đã chuyển sang chế độ sáng.');
      });
    }

    document.querySelectorAll('[data-theme-option]').forEach(btn => {
      btn.addEventListener('click', () => {
        applyTheme(btn.dataset.themeOption);
      });
    });

    document.querySelectorAll('[data-width-option]').forEach(btn => {
      btn.addEventListener('click', () => {
        applyWidth(btn.dataset.widthOption);
      });
    });

    // Font Controls
    const fontSmaller = byId('settings_smaller');
    if (fontSmaller) fontSmaller.addEventListener('click', () => applyFont(currentFont - 1));

    const fontBigger = byId('settings_bigger');
    if (fontBigger) fontBigger.addEventListener('click', () => applyFont(currentFont + 1));

    const fontDec = byId('font_decrease');
    if (fontDec) fontDec.addEventListener('click', () => applyFont(currentFont - 1));

    const fontInc = byId('font_increase');
    if (fontInc) fontInc.addEventListener('click', () => applyFont(currentFont + 1));

    // Focus Toggle
    const focusBtn = byId('focus_toggle');
    if (focusBtn) {
      focusBtn.addEventListener('click', () => applyFocus(!isFocus));
    }

    // Bookmark Toggle
    const bookmarkBtn = byId('bookmark');
    if (bookmarkBtn) {
      const currentUrl = window.location.pathname;
      let bookmarks = [];
      try {
        bookmarks = JSON.parse(SafeStorage.get('mayhen_docs_bookmarks', '[]'));
      } catch (e) {
        bookmarks = [];
      }
      let isBookmarked = bookmarks.includes(currentUrl);
      bookmarkBtn.setAttribute('aria-pressed', String(isBookmarked));

      bookmarkBtn.addEventListener('click', () => {
        isBookmarked = !isBookmarked;
        if (isBookmarked) {
          if (!bookmarks.includes(currentUrl)) bookmarks.push(currentUrl);
          showToast('Đã lưu bài viết vào danh sách quan tâm.');
        } else {
          bookmarks = bookmarks.filter(u => u !== currentUrl);
          showToast('Đã bỏ lưu bài viết.');
        }
        SafeStorage.set('mayhen_docs_bookmarks', JSON.stringify(bookmarks));
        bookmarkBtn.setAttribute('aria-pressed', String(isBookmarked));
      });
    }

    // Mobile Drawer
    function toggleDrawer(open) {
      app.classList.toggle(PREFIX + 'mobile_open', open);
      const openBtn = byId('menu_open');
      if (openBtn) openBtn.setAttribute('aria-expanded', String(open));
      document.body.style.overflow = open ? 'hidden' : '';
    }

    const menuOpenBtn = byId('menu_open');
    if (menuOpenBtn) menuOpenBtn.addEventListener('click', () => toggleDrawer(true));

    const menuCloseBtn = byId('menu_close');
    if (menuCloseBtn) menuCloseBtn.addEventListener('click', () => toggleDrawer(false));

    const mobileScrim = byId('mobile_scrim');
    if (mobileScrim) mobileScrim.addEventListener('click', () => toggleDrawer(false));

    // Desktop Sidebar Collapse
    const sidebarToggle = byId('sidebar_toggle');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        const collapsed = app.classList.toggle(PREFIX + 'is_sidebar_collapsed');
        sidebarToggle.setAttribute('aria-pressed', String(collapsed));
      });
    }

    // Sidebar Category Accordions
    document.querySelectorAll('.mayhen_docs_detail_nav_group_toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const expanded = btn.getAttribute('aria-expanded') !== 'true';
        btn.setAttribute('aria-expanded', String(expanded));
        const controlledId = btn.getAttribute('aria-controls');
        if (controlledId) {
          const list = document.getElementById(controlledId);
          if (list) list.hidden = !expanded;
        }
      });
    });

    // Detail Search Execution
    let activeResultIdx = -1;
    let detailResults = [];

    function renderDetailSearch(query) {
      const resultsContainer = byId('search_results');
      const countEl = byId('search_count');
      if (!resultsContainer) return;
      resultsContainer.replaceChildren();
      activeResultIdx = -1;

      const clean = removeVietnameseTones(query);
      const tokens = clean.split(/\s+/).filter(Boolean);

      if (tokens.length === 0) {
        detailResults = searchData.slice(0, 8);
      } else {
        detailResults = searchData.filter(item => {
          const tClean = removeVietnameseTones(item.title || '');
          const cClean = removeVietnameseTones(item.category || '');
          return tokens.every(tok => tClean.includes(tok) || cClean.includes(tok));
        }).slice(0, 15);
      }

      if (countEl) {
        countEl.textContent = tokens.length === 0
          ? 'Các mục hướng dẫn gợi ý'
          : `${detailResults.length} kết quả phù hợp`;
      }

      if (detailResults.length === 0) {
        const empty = document.createElement('div');
        empty.className = PREFIX + 'search_empty';
        empty.style.padding = '20px';
        empty.style.textAlign = 'center';
        empty.style.color = 'var(--mayhen-docs-detail-muted, #7A726A)';
        empty.textContent = 'Chưa tìm thấy bài viết phù hợp. Hãy thử từ khóa khác.';
        resultsContainer.appendChild(empty);
        return;
      }

      detailResults.forEach((res, idx) => {
        const a = document.createElement('a');
        a.className = PREFIX + 'search_result';
        a.href = res.url;
        a.style.display = 'flex';
        a.style.alignItems = 'center';
        a.style.gap = '10px';
        a.style.padding = '10px 14px';
        a.style.textDecoration = 'none';
        a.style.color = 'inherit';
        a.style.borderRadius = '8px';
        a.style.borderBottom = '1px solid var(--mayhen-docs-detail-border, #EFE8DD)';

        a.innerHTML = `
          <span style="color: var(--mayhen-docs-detail-gold, #C5A880);">✧</span>
          <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 0.875rem;">${res.title}</div>
            <div style="font-size: 0.75rem; color: var(--mayhen-docs-detail-muted, #8C847A);">${res.category || 'Tài liệu'}</div>
          </div>
          <span style="color: var(--mayhen-docs-detail-muted, #8C847A);">↗</span>
        `;

        a.addEventListener('mouseenter', () => highlightDetailResult(idx));
        resultsContainer.appendChild(a);
      });
    }

    function highlightDetailResult(idx) {
      const resultsContainer = byId('search_results');
      if (!resultsContainer) return;
      const links = resultsContainer.querySelectorAll('.' + PREFIX + 'search_result');
      links.forEach((link, i) => {
        const isActive = i === idx;
        link.style.background = isActive ? 'var(--mayhen-docs-detail-rose-soft, rgba(197, 168, 128, 0.15))' : '';
      });
      activeResultIdx = idx;
    }

    const searchInput = byId('search_input');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => renderDetailSearch(e.target.value));
      searchInput.addEventListener('keydown', (e) => {
        const resultsContainer = byId('search_results');
        const links = resultsContainer ? resultsContainer.querySelectorAll('.' + PREFIX + 'search_result') : [];
        if (!links.length) return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = activeResultIdx < links.length - 1 ? activeResultIdx + 1 : 0;
          highlightDetailResult(next);
          links[next]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = activeResultIdx > 0 ? activeResultIdx - 1 : links.length - 1;
          highlightDetailResult(prev);
          links[prev]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
          if (activeResultIdx >= 0 && links[activeResultIdx]) {
            e.preventDefault();
            links[activeResultIdx].click();
          }
        }
      });
    }

    const detailSearchTrigger = byId('search_trigger');
    if (detailSearchTrigger) {
      detailSearchTrigger.addEventListener('click', () => openDialog(PREFIX + 'search_dialog'));
    }

    // Scrollspy & Reading Progress
    const progressBar = byId('reading_progress');
    const backTopBtn = byId('back_top');
    const tocLinks = Array.from(document.querySelectorAll('#mayhen_docs_detail_toc_links a[href^="#"]'));
    const articleHeadings = Array.from(document.querySelectorAll('.mayhen_docs_detail_article_body h2[id], .mayhen_docs_detail_article_body h3[id]'));

    function updateScrollAndTOC() {
      const scrollY = window.scrollY || document.documentElement.scrollTop;
      const totalH = document.documentElement.scrollHeight - window.innerHeight;

      if (progressBar) {
        const pct = totalH > 0 ? Math.min(100, Math.max(0, (scrollY / totalH) * 100)) : 0;
        progressBar.style.width = pct + '%';
      }

      const tocProgressBar = byId('toc_progress_bar');
      if (tocProgressBar) {
        const pct = totalH > 0 ? Math.min(100, Math.max(0, (scrollY / totalH) * 100)) : 0;
        tocProgressBar.style.width = pct + '%';
      }

      const readPercent = byId('read_percent');
      if (readPercent) {
        const pct = totalH > 0 ? Math.min(100, Math.max(0, (scrollY / totalH) * 100)) : 0;
        readPercent.textContent = Math.round(pct) + '%';
      }

      if (backTopBtn) {
        backTopBtn.classList.toggle(PREFIX + 'visible', scrollY > 400);
      }

      // Scrollspy
      if (articleHeadings.length && tocLinks.length) {
        let activeHeadingId = '';
        for (let i = 0; i < articleHeadings.length; i++) {
          const heading = articleHeadings[i];
          const rect = heading.getBoundingClientRect();
          if (rect.top <= 120) {
            activeHeadingId = heading.id;
          } else {
            break;
          }
        }

        tocLinks.forEach(link => {
          const href = link.getAttribute('href');
          const isCurrent = href === '#' + activeHeadingId;
          if (isCurrent) {
            link.setAttribute('aria-current', 'location');
            link.style.fontWeight = '700';
            link.style.color = 'var(--mayhen-docs-detail-gold, #C5A880)';
          } else {
            link.removeAttribute('aria-current');
            link.style.fontWeight = '';
            link.style.color = '';
          }
        });
      }
    }

    window.addEventListener('scroll', updateScrollAndTOC, { passive: true });
    window.addEventListener('resize', updateScrollAndTOC, { passive: true });
    updateScrollAndTOC();

    // Smooth Back-to-Top
    if (backTopBtn) {
      backTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    const footerTopBtn = byId('footer_top');
    if (footerTopBtn) {
      footerTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    // Share Actions
    const copyMdBtn = byId('copy_markdown');
    if (copyMdBtn) {
      copyMdBtn.addEventListener('click', async () => {
        const body = document.querySelector('.mayhen_docs_detail_article_body');
        const text = body ? body.innerText : '';
        const success = await copyToClipboard(text);
        showToast(success ? 'Đã sao chép nội dung bài viết.' : 'Không thể sao chép.');
      });
    }

    const copyShareBtn = byId('copy_share');
    if (copyShareBtn) {
      copyShareBtn.addEventListener('click', async () => {
        const success = await copyToClipboard(window.location.href);
        showToast(success ? 'Đã sao chép liên kết vào bộ nhớ tạm.' : 'Không thể sao chép liên kết.');
      });
    }

    const nativeShareBtn = byId('native_share');
    if (nativeShareBtn) {
      nativeShareBtn.addEventListener('click', async () => {
        if (navigator.share) {
          try {
            await navigator.share({
              title: document.title,
              url: window.location.href
            });
          } catch (e) {}
        } else {
          const success = await copyToClipboard(window.location.href);
          showToast(success ? 'Trình duyệt chưa hỗ trợ chia sẻ trực tiếp. Đã sao chép liên kết!' : 'Đã sao chép liên kết.');
        }
      });
    }

    const printBtn = byId('print');
    if (printBtn) {
      printBtn.addEventListener('click', () => {
        window.print();
      });
    }

    // Feedback Actions
    const copyFeedbackBtn = byId('copy_feedback');
    if (copyFeedbackBtn) {
      copyFeedbackBtn.addEventListener('click', async () => {
        const msgEl = byId('feedback_message');
        const text = msgEl ? msgEl.value.trim() : '';
        if (!text) {
          showToast('Vui lòng nhập nội dung góp ý.');
          return;
        }
        const success = await copyToClipboard(text);
        showToast(success ? 'Đã sao chép góp ý của bạn!' : 'Không thể sao chép góp ý.');
      });
    }

    // Code Block Copy Enhancement
    document.querySelectorAll('.mayhen_docs_detail_article_body pre').forEach(pre => {
      pre.style.position = 'relative';
      const copyBtn = document.createElement('button');
      copyBtn.type = 'button';
      copyBtn.textContent = 'Sao chép';
      copyBtn.style.position = 'absolute';
      copyBtn.style.top = '8px';
      copyBtn.style.right = '8px';
      copyBtn.style.padding = '4px 8px';
      copyBtn.style.fontSize = '11px';
      copyBtn.style.borderRadius = '4px';
      copyBtn.style.border = '1px solid var(--mayhen-docs-detail-border, #E5DCCD)';
      copyBtn.style.background = 'var(--mayhen-docs-detail-surface, #FAF7F2)';
      copyBtn.style.color = 'inherit';
      copyBtn.style.cursor = 'pointer';
      copyBtn.style.opacity = '0.8';

      copyBtn.addEventListener('click', async () => {
        const code = pre.querySelector('code');
        const textToCopy = code ? code.innerText : pre.innerText;
        const success = await copyToClipboard(textToCopy);
        if (success) {
          copyBtn.textContent = 'Đã sao chép ✓';
          setTimeout(() => { copyBtn.textContent = 'Sao chép'; }, 2000);
        }
      });

      pre.appendChild(copyBtn);
    });

    // Global Hotkeys
    document.addEventListener('keydown', (e) => {
      const isInput = e.target.matches && e.target.matches('input, textarea, [contenteditable="true"]');
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        openDialog(PREFIX + 'search_dialog');
        return;
      }
      if (!isInput && e.key === '/') {
        e.preventDefault();
        openDialog(PREFIX + 'search_dialog');
        return;
      }
      if (e.key === 'Escape') {
        dialogs.forEach(d => closeDialog(d));
        toggleDrawer(false);
      }
    });
  }

  /* ============================================================
     ENTRY POINT EXECUTION
  ============================================================ */
  document.addEventListener('DOMContentLoaded', () => {
    const indexRoot = document.getElementById('mayhen_docs_app');
    if (indexRoot) {
      initDocsIndex(indexRoot);
    }

    const detailRoot = document.getElementById('mayhen_docs_detail_app');
    if (detailRoot) {
      initDocsDetail(detailRoot);
    }
  });

  // Fallback if script executes after DOMContentLoaded
  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    const indexRoot = document.getElementById('mayhen_docs_app');
    if (indexRoot && !indexRoot.dataset.jsInitialized) {
      indexRoot.dataset.jsInitialized = 'true';
      initDocsIndex(indexRoot);
    }
    const detailRoot = document.getElementById('mayhen_docs_detail_app');
    if (detailRoot && !detailRoot.dataset.jsInitialized) {
      detailRoot.dataset.jsInitialized = 'true';
      initDocsDetail(detailRoot);
    }
  }
})();
