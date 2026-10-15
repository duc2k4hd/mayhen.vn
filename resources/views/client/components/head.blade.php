<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- SEO Basic -->
<title>@yield('title', 'Thiệp Cưới Online — ' . config('app.name'))</title>
<meta name="description" content="@yield('meta_description', config('app.name') . ' — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury, hiệu ứng mở phong bì hoàng gia, tối ưu điện thoại và quản lý khách mời chu đáo.')">
@hasSection('meta_keywords')
    <meta name="keywords" content="@yield('meta_keywords')">
@endif


{{-- Favicon --}}
<link rel="apple-touch-icon" sizes="180x180"
    href="{{ asset('storage/images/client/business/mayhen-favicon.webp') }}">
<link rel="icon" type="image/png" sizes="32x32"
    href="{{ asset('storage/images/client/business/mayhen-favicon.webp') }}">
<link rel="icon" type="image/png" sizes="16x16"
    href="{{ asset('storage/images/client/business/mayhen-favicon.webp') }}">
<link rel="mask-icon"
    href="{{ asset('storage/images/client/business/mayhen-favicon.webp') }}"
    color="#5bbad5">
<link rel="icon"
    href="{{ asset('storage/images/client/business/mayhen-favicon.webp') }}"
    type="image/x-icon">
<meta name="theme-color" content="#C5A880">
@php
    // Kiểm tra URL có chứa tham số query (? hoặc &)
    $hasQueryParams = count(request()->query()) > 0 || !empty(request()->getQueryString());
    // Trang xem thử, tạo thiệp, thiệp cưới hoặc admin mặc định là noindex, nofollow
    $isNoFollowRoute = request()->is('xem-thu*', 'admin*', 'tao-thiep*', 'thiep*');
    // Các trang công cụ/chức năng khác không phải trang nội dung SEO chính là noindex, follow
    $isNonMainPage = request()->is('thanh-toan*', 'tai-khoan*');
@endphp
@if (trim($__env->yieldContent('robots')))
    <meta name="robots" content="@yield('robots')">
@elseif ($isNoFollowRoute)
    <meta name="robots" content="noindex, nofollow">
@elseif ($hasQueryParams || $isNonMainPage)
    <meta name="robots" content="noindex, follow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
@endif
<link rel="canonical" href="@yield('canonical', url()->current())">

<!-- Open Graph / Facebook / Zalo -->
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="@yield('canonical', url()->current())">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="vi_VN">
<meta property="og:title" content="@yield('title', 'Thiệp Cưới Online — ' . config('app.name'))">
<meta property="og:description" content="@yield('meta_description', config('app.name') . ' — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury.')">
<meta property="og:image" content="@yield('og_image', asset('assets/client/images/og-share.jpg'))">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title', 'Thiệp Cưới Online — ' . config('app.name'))">
<meta name="twitter:description" content="@yield('meta_description', config('app.name') . ' — Nền tảng tạo thiệp cưới online cao cấp.')">
<meta name="twitter:image" content="@yield('og_image', asset('assets/client/images/og-share.jpg'))">

<!-- Google Fonts: Playfair Display & Inter (Full Vietnamese Subsets) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

<!-- May Hen Core Vanilla Styles -->
<link rel="stylesheet" href="{{ asset('assets/client/css/common/tokens.css') }}">
<link rel="stylesheet" href="{{ asset('assets/client/css/common/base.css') }}">
<link rel="stylesheet" href="{{ asset('assets/client/css/common/layout.css') }}">
<link rel="stylesheet" href="{{ asset('assets/client/css/components/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/client/css/components/forms.css') }}">
<link rel="stylesheet" href="{{ asset('assets/client/css/components/header.css') }}">
<link rel="stylesheet" href="{{ asset('assets/client/css/components/footer.css') }}">

<!-- Page Specific Styles & Schema -->
@stack('styles')
@stack('schema')
