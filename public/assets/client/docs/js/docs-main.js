/**
 * MÂY HẸN DOCUMENTATION CENTER - UNIFIED JAVASCRIPT
 * Pure Vanilla ES6+, Zero External Library
 */
(function() {
  'use strict';

  // 1. Reading Progress Bar
  function initProgressBar() {
    const bar = document.querySelector('.mayhen_docs_progress');
    if (!bar) return;
    window.addEventListener('scroll', function() {
      const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
      bar.style.width = scrolled + '%';
    }, { passive: true });
  }

  // 2. Theme Switcher (Light / Dark)
  function initTheme() {
    const app = document.querySelector('.mayhen_docs_app');
    const toggleBtns = document.querySelectorAll('[data-action="toggle-theme"], .mayhen_docs_theme_toggle');
    if (!app) return;

    const savedTheme = localStorage.getItem('mayhen_docs_theme') || 'light';
    app.setAttribute('data-theme', savedTheme);

    toggleBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const current = app.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        app.setAttribute('data-theme', next);
        localStorage.setItem('mayhen_docs_theme', next);
      });
    });
  }

  // 3. Search Modal & Native Keyboard Shortcuts (Ctrl+K, /, Esc)
  function initSearchModal() {
    const modal = document.getElementById('mayhen_docs_search_modal');
    const openBtns = document.querySelectorAll('[data-action="open-search"], .mayhen_docs_search_top, .mayhen_docs_mobile_search_button');
    const closeBtns = document.querySelectorAll('[data-action="close-search"], .mayhen_docs_search_close');
    const searchInput = document.getElementById('mayhen_docs_search_input');
    const resultsContainer = document.getElementById('mayhen_docs_search_results');

    if (!modal) return;

    function openModal() {
      modal.showModal();
      if (searchInput) {
        setTimeout(() => searchInput.focus(), 50);
      }
    }

    function closeModal() {
      modal.close();
    }

    openBtns.forEach(btn => btn.addEventListener('click', openModal));
    closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

    // Shortcut Ctrl+K, Cmd+K, /
    document.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
        e.preventDefault();
        if (modal.open) closeModal(); else openModal();
      } else if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
        e.preventDefault();
        openModal();
      } else if (e.key === 'Escape' && modal.open) {
        closeModal();
      }
    });

    // Live search query via API
    let debounceTimer;
    if (searchInput && resultsContainer) {
      searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        if (query.length < 2) {
          resultsContainer.innerHTML = '<div class="mayhen_docs_search_empty">Nhập ít nhất 2 ký tự để tìm kiếm...</div>';
          return;
        }

        debounceTimer = setTimeout(() => {
          const space = document.querySelector('.mayhen_docs_app')?.getAttribute('data-space') || 'client';
          const searchUrl = space === 'admin' ? '/admin/docs/search' : '/docs/search';

          fetch(searchUrl + '?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
              if (!data.data || data.data.length === 0) {
                resultsContainer.innerHTML = '<div class="mayhen_docs_search_empty">Không tìm thấy kết quả nào phù hợp.</div>';
                return;
              }
              let html = '<ul class="mayhen_docs_search_list">';
              data.data.forEach(item => {
                html += `<li><a href="${item.url}" class="mayhen_docs_search_item"><span class="mayhen_docs_search_cat">${item.category}</span><strong>${item.title}</strong><small>${item.summary || ''}</small></a></li>`;
              });
              html += '</ul>';
              resultsContainer.innerHTML = html;
            })
            .catch(() => {
              resultsContainer.innerHTML = '<div class="mayhen_docs_search_empty">Lỗi nạp tìm kiếm. Vui lòng thử lại.</div>';
            });
        }, 150);
      });
    }
  }

  // 4. Sidebar Collapsible Accordion & Mobile Drawer
  function initSidebar() {
    const groupToggles = document.querySelectorAll('.mayhen_docs_side_group');
    groupToggles.forEach(btn => {
      btn.addEventListener('click', function() {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
        const list = this.nextElementSibling;
        if (list) {
          list.hidden = isExpanded;
        }
      });
    });

    // Mobile Hamburger Drawer
    const mobileMenuBtn = document.querySelector('.mayhen_docs_mobile_menu_button');
    const sidebar = document.querySelector('.mayhen_docs_sidebar');
    const closeSidebarBtn = document.querySelector('.mayhen_docs_sidebar_close');

    if (mobileMenuBtn && sidebar) {
      mobileMenuBtn.addEventListener('click', () => {
        sidebar.classList.add('is-open');
      });
    }
    if (closeSidebarBtn && sidebar) {
      closeSidebarBtn.addEventListener('click', () => {
        sidebar.classList.remove('is-open');
      });
    }
  }

  // 5. Sticky Table of Contents (TOC) Scrollspy
  function initTocScrollspy() {
    const tocLinks = document.querySelectorAll('.mayhen_docs_toc_link');
    const headings = document.querySelectorAll('.mayhen_docs_article h2, .mayhen_docs_article h3');
    if (!tocLinks.length || !headings.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          tocLinks.forEach(link => {
            if (link.getAttribute('href') === '#' + id) {
              link.classList.add('is-active');
            } else {
              link.classList.remove('is-active');
            }
          });
        }
      });
    }, { rootMargin: '0px 0px -70% 0px' });

    headings.forEach(h => observer.observe(h));
  }

  // 6. Back to Top Button
  function initBackToTop() {
    const backBtn = document.querySelector('.mayhen_docs_back_to_top');
    if (!backBtn) return;
    window.addEventListener('scroll', () => {
      if (window.scrollY > 400) {
        backBtn.classList.add('is-visible');
      } else {
        backBtn.classList.remove('is-visible');
      }
    }, { passive: true });
    backBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // DOMContentLoaded Bootstrapper
  document.addEventListener('DOMContentLoaded', function() {
    initProgressBar();
    initTheme();
    initSearchModal();
    initSidebar();
    initTocScrollspy();
    initBackToTop();
  });
})();
