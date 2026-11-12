<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $pageTitle ?? 'Mây Hẹn Docs' }}</title>
  <meta name="description" content="{{ $metaDescription ?? 'Trung tâm Hướng dẫn sử dụng nền tảng thiệp cưới Mây Hẹn' }}">

  {{-- SEO & Security --}}
  @if($space === 'admin')
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  @else
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $pageTitle ?? 'Mây Hẹn Docs' }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Mây Hẹn (mayhen.vn)">
  @endif

  {{-- Google Fonts Open Sans (Offline fallback Segoe UI) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

  {{-- Unified Pure CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/client/docs/css/docs-main.css') }}">
</head>
<body class="mayhen_docs_body">
  <div class="mayhen_docs_app" data-space="{{ $space ?? 'client' }}" data-theme="light">
    {{-- Skip to content link for A11y --}}
    <a href="#main-content" class="mayhen_docs_skip">Nhảy tới nội dung chính</a>

    {{-- Header --}}
    @include('docs.partials.header')

    {{-- Main Body Layout --}}
    @yield('content')

    {{-- Footer --}}
    @include('docs.partials.footer')

    {{-- Search Modal Dialog --}}
    @include('docs.partials.search-modal')
  </div>

  {{-- Unified Pure JS --}}
  <script src="{{ asset('assets/client/docs/js/docs-main.js') }}" type="module"></script>
</body>
</html>

