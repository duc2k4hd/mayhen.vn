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

  {{-- Google Fonts: Open Sans --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- Unified Pure CSS (index + detail combined) --}}
  <link rel="stylesheet" href="{{ asset('assets/client/docs/css/docs-main.css') }}">
</head>
<body>
  @yield('body')

  {{-- Unified Pure JS --}}
  <script src="{{ asset('assets/client/docs/js/docs-main.js') }}" defer></script>
</body>
</html>
