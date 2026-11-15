<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#fffdfb">
  <title>{{ $pageTitle ?? 'Mây Hẹn Docs' }}</title>
  <meta name="description" content="{{ $metaDescription ?? 'Trung tâm Hướng dẫn sử dụng nền tảng thiệp cưới Mây Hẹn' }}">

  {{-- SEO & Security --}}
  @if(($space ?? 'client') === 'admin')
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  @else
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $pageTitle ?? 'Mây Hẹn Docs' }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Mây Hẹn (mayhen.vn)">
  @endif

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

  {{-- Global Theme Controller Script (Self-contained, failsafe, direct handler) --}}
  <script>
    (function() {
      function getActiveTheme() {
        try {
          return localStorage.getItem('mayhen_docs_theme') || 'light';
        } catch (e) {
          return 'light';
        }
      }

      function updateAllIcons(theme) {
        var sunSvg = '<svg class="mayhen_docs_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
        var moonSvg = '<svg class="mayhen_docs_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><path d="M20.3 15.7A8.5 8.5 0 0 1 8.3 3.7 8.5 8.5 0 1 0 20.3 15.7Z"/></svg>';

        var indexToggleBtn = document.getElementById('theme-toggle-btn');
        if (indexToggleBtn) {
          indexToggleBtn.innerHTML = theme === 'dark' ? sunSvg : moonSvg;
          indexToggleBtn.setAttribute('title', theme === 'dark' ? 'Chuyển sang giao diện Sáng' : 'Chuyển sang giao diện Tối');
          indexToggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Chuyển sang giao diện Sáng' : 'Chuyển sang giao diện Tối');
        }

        var detailToggleBtn = document.getElementById('mayhen_docs_detail_theme_quick');
        if (detailToggleBtn) {
          detailToggleBtn.innerHTML = theme === 'dark' ? sunSvg : moonSvg;
          detailToggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Chuyển sang giao diện Sáng' : 'Chuyển sang giao diện Tối');
        }
      }

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
          toast.textContent = theme === 'dark' ? 'Đã chuyển sang giao diện tối.' : 'Đã chuyển sang giao diện sáng.';
          toast.classList.add('mayhen_docs_toast_show', 'mayhen_docs_detail_visible');
          setTimeout(function() {
            toast.classList.remove('mayhen_docs_toast_show', 'mayhen_docs_detail_visible');
          }, 2500);
        }
      };

      window.toggleDocsTheme = function() {
        var current = document.documentElement.getAttribute('data-theme') || getActiveTheme();
        var next = current === 'dark' ? 'light' : 'dark';
        window.setDocsTheme(next);
      };

      document.addEventListener('DOMContentLoaded', function() {
        window.setDocsTheme(getActiveTheme());

        document.addEventListener('click', function(e) {
          var btn = e.target.closest('#theme-toggle-btn, #mayhen_docs_detail_theme_quick, [data-action="toggle-theme"], .mayhen_docs_theme_toggle');
          if (btn) {
            e.preventDefault();
            e.stopPropagation();
            window.toggleDocsTheme();
          }
        });
      });
    })();
  </script>

  {{-- Unified Pure JS with Cache Buster --}}
  <script src="{{ asset('assets/client/docs/js/docs-main.js') }}?v={{ $jsVer }}" defer></script>
</body>
</html>
