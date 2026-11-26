<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, interactive-widget=resizes-content">
  <meta name="theme-color" content="#fffdfb">
  @php
    $docsBrandName = \App\Models\PlatformSetting::getBrandName();
  @endphp
  <meta name="application-name" content="{{ $docsBrandName }}">
  <title>{{ $pageTitle ?? ($docsBrandName . ' Docs') }}</title>
  <meta name="description" content="{{ $metaDescription ?? ('Trung tâm Hướng dẫn sử dụng nền tảng thiệp cưới ' . $docsBrandName) }}">

  {{-- SEO & Security --}}
  @if(($space ?? 'client') === 'admin')
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  @else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $pageTitle ?? ($docsBrandName . ' Docs') }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $docsBrandName }} (mayhen.vn)">
  @endif

  @include('client.components.favicon')

  {{-- Google Fonts: Open Sans & Playfair Display (Full Vietnamese Subsets) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

  {{-- Unified Pure CSS (index + detail combined) with Cache Buster --}}
  @php
    $cssPath = public_path('assets/client/docs/css/docs-main.css');
    $jsPath  = public_path('assets/client/docs/js/docs-main.js');
    $cssVer  = file_exists($cssPath) ? filemtime($cssPath) : '1.0';
    $jsVer   = file_exists($jsPath) ? filemtime($jsPath) : '1.0';
  @endphp
  <link rel="stylesheet" href="{{ asset('assets/client/docs/css/docs-main.css') }}?v={{ $cssVer }}">
  @if(($space ?? 'client') === 'client')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/seasonal-fall.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset_v('assets/client/css/components/seasonal-fall.css') }}"></noscript>
  @endif

  {{-- Early Theme Activation (Eliminates FOUC) --}}
  <script>
    (function() {
      try {
        var savedTheme = localStorage.getItem('mayhen_docs_theme');
        var theme = savedTheme ? savedTheme : 'light';
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.style.colorScheme = theme;
      } catch (e) {}
    })();
  </script>
</head>
<body>
  @yield('body')

  {{-- Global Theme & Navigation Controller Script (Self-contained, failsafe, direct handler) --}}
  <script>
    (function() {
      function getActiveTheme() {
        try {
          return localStorage.getItem('mayhen_docs_theme') || 'light';
        } catch (e) {
          return 'light';
        }
      }

      var sunSvg = '<svg viewBox="0 0 24 24" style="width:14px;height:14px;display:block;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
      var moonSvg = '<svg viewBox="0 0 24 24" style="width:14px;height:14px;display:block;" fill="currentColor" stroke="none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

      function updateAllIcons(theme) {
        var isDark = (theme === 'dark');

        // 1. Single pill theme switcher buttons (.mayhen_docs_theme_switcher)
        var switcherButtons = document.querySelectorAll('.mayhen_docs_theme_switcher, #theme-toggle-btn, #mayhen_docs_detail_theme_quick, .mayhen_docs_theme_toggle');
        switcherButtons.forEach(function(btn) {
          var iconEl = btn.querySelector('.mayhen_docs_theme_switcher_icon') || btn.querySelector('svg');
          var labelEl = btn.querySelector('.mayhen_docs_theme_switcher_label, .mayhen_docs_theme_text');

          if (iconEl) {
            iconEl.innerHTML = isDark ? sunSvg : moonSvg;
          }
          if (labelEl) {
            labelEl.textContent = isDark ? 'Giao diện Tối' : 'Giao diện Sáng';
          }
          btn.setAttribute('title', isDark ? 'Đang bật Giao diện Tối (nhấp để đổi sang Sáng)' : 'Đang bật Giao diện Sáng (nhấp để đổi sang Tối)');
          btn.setAttribute('aria-label', isDark ? 'Đang bật Giao diện Tối (nhấp để đổi sang Sáng)' : 'Đang bật Giao diện Sáng (nhấp để đổi sang Tối)');
          btn.setAttribute('aria-pressed', String(isDark));
        });

        // 2. Dual segmented theme buttons (if present)
        var lightBtns = document.querySelectorAll('.mayhen_docs_theme_btn_light');
        lightBtns.forEach(function(btn) {
          btn.setAttribute('aria-checked', String(theme === 'light'));
          btn.classList.toggle('mayhen_docs_theme_btn_active', theme === 'light');
        });

        var darkBtns = document.querySelectorAll('.mayhen_docs_theme_btn_dark');
        darkBtns.forEach(function(btn) {
          btn.setAttribute('aria-checked', String(theme === 'dark'));
          btn.classList.toggle('mayhen_docs_theme_btn_active', theme === 'dark');
        });

        // 3. Settings modal options
        document.querySelectorAll('[data-theme-option]').forEach(function(opt) {
          opt.setAttribute('aria-pressed', String(opt.dataset.themeOption === theme));
        });
      }

      var themeLock = false;
      window.setDocsTheme = function(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
        if (document.body) {
          document.body.setAttribute('data-theme', theme);
          document.body.dataset.theme = theme;
        }

        var app = document.getElementById('mayhen_docs_app') || document.getElementById('mayhen_docs_detail_app');
        if (app) {
          app.setAttribute('data-theme', theme);
          app.dataset.theme = theme;
        }

        try {
          localStorage.setItem('mayhen_docs_theme', theme);
        } catch (e) {}

        updateAllIcons(theme);

        var toast = document.getElementById('mayhen_docs_toast') || document.getElementById('mayhen_docs_detail_toast');
        if (toast) {
          toast.textContent = (theme === 'dark') ? 'Đã chuyển sang giao diện tối.' : 'Đã chuyển sang giao diện sáng.';
          toast.classList.add('mayhen_docs_toast_show', 'mayhen_docs_detail_visible');
          setTimeout(function() {
            toast.classList.remove('mayhen_docs_toast_show', 'mayhen_docs_detail_visible');
          }, 2500);
        }
      };

      window.toggleDocsTheme = function() {
        if (themeLock) return;
        themeLock = true;
        setTimeout(function() { themeLock = false; }, 300);

        var current = document.documentElement.getAttribute('data-theme') || getActiveTheme();
        var next = (current === 'dark') ? 'light' : 'dark';
        window.setDocsTheme(next);
      };

      // Direct, failsafe global event delegation (registered immediately on document)
      document.addEventListener('click', function(e) {
        // 1. Theme toggle button click (single pill or legacy toggle)
        var themeBtn = e.target.closest('.mayhen_docs_theme_switcher, #theme-toggle-btn, #mayhen_docs_detail_theme_quick, .mayhen_docs_theme_toggle, [data-action="toggle-theme"]');
        if (themeBtn && !themeBtn.matches('[data-theme-set]')) {
          e.preventDefault();
          e.stopPropagation();
          window.toggleDocsTheme();
          return;
        }

        // 2. Dual segmented theme button click (if present)
        var themeSetBtn = e.target.closest('[data-theme-set]');
        if (themeSetBtn) {
          e.preventDefault();
          e.stopPropagation();
          var targetTheme = themeSetBtn.getAttribute('data-theme-set');
          window.setDocsTheme(targetTheme);
          return;
        }

        // 3. Theme option in settings dialog
        var themeOpt = e.target.closest('[data-theme-option]');
        if (themeOpt) {
          e.preventDefault();
          var optTheme = themeOpt.dataset.themeOption;
          if (optTheme === 'system') {
            var isSysDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            optTheme = isSysDark ? 'dark' : 'light';
          }
          window.setDocsTheme(optTheme);
          return;
        }

        // 4. Detail sidebar category accordion
        var detailToggle = e.target.closest('.mayhen_docs_detail_nav_group_toggle');
        if (detailToggle) {
          e.preventDefault();
          e.stopPropagation();
          var controlledId = detailToggle.getAttribute('aria-controls');
          var isExpanded = detailToggle.getAttribute('aria-expanded') === 'true';
          var nextState = !isExpanded;
          detailToggle.setAttribute('aria-expanded', String(nextState));

          var targetList = controlledId ? document.getElementById(controlledId) : null;
          if (!targetList) {
            var group = detailToggle.closest('.mayhen_docs_detail_nav_group');
            if (group) {
              targetList = group.querySelector('.mayhen_docs_detail_nav_links');
            }
          }

          if (targetList) {
            if (nextState) {
              targetList.removeAttribute('hidden');
              targetList.style.removeProperty('display');
              targetList.style.display = 'flex';
            } else {
              targetList.setAttribute('hidden', '');
              targetList.style.display = 'none';
            }
          }
          return;
        }

        // 5. Index sidebar category accordion
        var indexToggle = e.target.closest('.mayhen_docs_side_group');
        if (indexToggle) {
          e.preventDefault();
          e.stopPropagation();
          var groupKey = indexToggle.dataset.group;
          var targetList = groupKey ? document.getElementById('mayhen_docs_group_' + groupKey) : null;
          if (!targetList) {
            var parentGroup = indexToggle.closest('.mayhen_docs_side_category') || indexToggle.parentElement;
            if (parentGroup) {
              targetList = parentGroup.querySelector('.mayhen_docs_side_children');
            }
          }
          var isExpanded = indexToggle.getAttribute('aria-expanded') === 'true';
          var nextState = !isExpanded;
          indexToggle.setAttribute('aria-expanded', String(nextState));
          if (targetList) {
            if (nextState) {
              targetList.classList.remove('mayhen_docs_group_closed');
              targetList.style.removeProperty('display');
              targetList.style.display = 'block';
            } else {
              targetList.classList.add('mayhen_docs_group_closed');
              targetList.style.display = 'none';
            }
          }
          return;
        }
      }, true);

      // Run theme initialization immediately and on DOM ready
      window.setDocsTheme(getActiveTheme());
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
          window.setDocsTheme(getActiveTheme());
        });
      }
    })();
  </script>

  {{-- Unified Pure JS with Cache Buster --}}
  <script src="{{ asset('assets/client/docs/js/docs-main.js') }}?v={{ $jsVer }}" defer></script>
  @if(($space ?? 'client') === 'client')
    <script type="module" src="{{ asset_v('assets/client/js/components/seasonal-fall.js') }}"></script>
  @endif
</body>
</html>
