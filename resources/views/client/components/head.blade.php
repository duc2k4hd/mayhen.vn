@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="application-name" content="{{ $brandName }}">
<meta name="referrer" content="strict-origin-when-cross-origin">

<!-- SEO Basic -->
<title>@hasSection('title')@yield('title')@elseif(View::hasSection('page_title'))@yield('page_title')@elseif(View::hasSection('account_title'))@yield('account_title')@else{{ 'Thiệp Cưới Online — ' . $brandName }}@endif</title>
<meta name="description" content="@yield('meta_description', $brandName . ' — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury, hiệu ứng mở phong bì hoàng gia, tối ưu điện thoại và quản lý khách mời chu đáo.')">
@hasSection('meta_keywords')
    <meta name="keywords" content="@yield('meta_keywords')">
@endif
@hasSection('meta_author')
    <meta name="author" content="@yield('meta_author')">
@endif

{{-- Favicon --}}
@include('client.components.favicon')

{{-- Google Tag Manager (GTM) --}}
@include('client.components.gtm')

@php
    // Kiểm tra URL có chứa tham số query (? hoặc &)
    $hasQueryParams = count(request()->query()) > 0 || !empty(request()->getQueryString());
    // Trang xem thử, tạo thiệp, thiệp cưới, admin, auth, checkout, tài khoản và đối tác mặc định là noindex, nofollow
    $isNoFollowRoute = request()->is(
        'xem-thu*',
        'admin*',
        'tao-thiep*',
        'thiep/*',
        'loi-moi/*',
        'thiep-su-kien/xem-thu*',
        'thiep-su-kien/tao-thu*',
        'dang-nhap*',
        'dang-ky*',
        'quen-mat-khau*',
        'doi-mat-khau*',
        'doi-tac*',
        'trinh-chieu*',
        'checkin*',
        'thanh-toan*',
        'tai-khoan*',
        'api*'
    );
    // Các trang có query params tìm kiếm lọc nội bộ cũng không index để tránh duplicate content
    $isNonMainPage = false;
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
<meta property="og:site_name" content="{{ $brandName }}">
<meta property="og:locale" content="vi_VN">
<meta property="og:title" content="@yield('title', 'Thiệp Cưới Online — ' . $brandName)">
<meta property="og:description" content="@yield('meta_description', $brandName . ' — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury.')">
<meta property="og:image" content="@yield('og_image', asset('storage/images/client/banners/banner-home.webp'))">
@hasSection('og_image_alt')
    <meta property="og:image:alt" content="@yield('og_image_alt')">
@endif
@hasSection('og_image_width')
    <meta property="og:image:width" content="@yield('og_image_width')">
@endif
@hasSection('og_image_height')
    <meta property="og:image:height" content="@yield('og_image_height')">
@endif

<!-- Twitter Cards -->
<meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
<meta name="twitter:title" content="@yield('title', 'Thiệp Cưới Online — ' . $brandName)">
<meta name="twitter:description" content="@yield('meta_description', $brandName . ' — Nền tảng tạo thiệp cưới online cao cấp.')">
<meta name="twitter:image" content="@yield('og_image', asset('storage/images/client/banners/banner-home.webp'))">
@hasSection('og_image_alt')
    <meta name="twitter:image:alt" content="@yield('og_image_alt')">
@endif

<!-- Additional Page-Level Meta Tags -->
@stack('meta')

<!-- Google Fonts: Playfair Display & Inter (Non-blocking để không chặn FCP) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet"></noscript>

<!-- May Hen Core Vanilla Styles (Tự động gắn ?v=filemtime chống cache trình duyệt) -->
<link rel="stylesheet" href="{{ asset_v('assets/client/css/common/tokens.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/common/base.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/common/layout.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/buttons.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/forms.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/header.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/footer.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/bottom-nav.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/atelier-loader.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/chatbot.css') }}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{{ asset_v('assets/client/css/components/chatbot.css') }}"></noscript>
@unless(View::hasSection('hide_chrome') || request()->is('diem-danh*') || request()->is('dieu-phoi-ban-tiec*') || request()->is('trinh-chieu*'))
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/seasonal-fall.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset_v('assets/client/css/components/seasonal-fall.css') }}"></noscript>
@endunless

<!-- Page Specific Styles & Schema -->
@stack('styles')
@stack('schema')
