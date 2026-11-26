@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $rawPageTitle = trim((string) (
        $__env->yieldContent('title')
        ?: ($__env->yieldContent('page_title')
        ?: $__env->yieldContent('account_title'))
    ));
    $decodedRawTitle = html_entity_decode($rawPageTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($decodedRawTitle === '') {
        $resolvedSeoTitle = 'Thiệp Cưới Online | ' . $brandName;
    } elseif ($brandName !== '' && mb_stripos($decodedRawTitle, $brandName, 0, 'UTF-8') !== false) {
        $resolvedSeoTitle = $decodedRawTitle;
    } else {
        $resolvedSeoTitle = $decodedRawTitle . ' | ' . $brandName;
    }
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="application-name" content="{{ $brandName }}">
<meta name="referrer" content="strict-origin-when-cross-origin">

<!-- SEO Basic -->
<title>{{ $resolvedSeoTitle }}</title>
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
        'gia-pha/*',
        'gieo-que/*',
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
<meta property="og:title" content="{{ $resolvedSeoTitle }}">
<meta property="og:description" content="@yield('meta_description', $brandName . ' — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury.')">
@php
    $toolSocialBanner = \App\Services\SocialBannerService::resolveForPath(request()->path());
    $resolvedOgImage = View::hasSection('og_image')
        ? trim(View::yieldContent('og_image'))
        : ($toolSocialBanner['url'] ?? asset('storage/images/client/banners/banner-home.webp'));
    $resolvedOgImageWidth = View::hasSection('og_image_width')
        ? trim(View::yieldContent('og_image_width'))
        : ($toolSocialBanner ? (string) $toolSocialBanner['width'] : '1200');
    $resolvedOgImageHeight = View::hasSection('og_image_height')
        ? trim(View::yieldContent('og_image_height'))
        : ($toolSocialBanner ? (string) $toolSocialBanner['height'] : '600');
    $resolvedOgImageAlt = View::hasSection('og_image_alt')
        ? trim(View::yieldContent('og_image_alt'))
        : ($toolSocialBanner['alt'] ?? null);
@endphp
<meta property="og:image" content="{{ $resolvedOgImage }}">
@if ($resolvedOgImageAlt)
    <meta property="og:image:alt" content="{{ $resolvedOgImageAlt }}">
@endif
@if ($resolvedOgImageWidth)
    <meta property="og:image:width" content="{{ $resolvedOgImageWidth }}">
@endif
@if ($resolvedOgImageHeight)
    <meta property="og:image:height" content="{{ $resolvedOgImageHeight }}">
@endif

<!-- Twitter Cards -->
<meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
<meta name="twitter:title" content="{{ $resolvedSeoTitle }}">
<meta name="twitter:description" content="@yield('meta_description', $brandName . ' — Nền tảng tạo thiệp cưới online cao cấp.')">
<meta name="twitter:image" content="{{ $resolvedOgImage }}">
@if ($resolvedOgImageAlt)
    <meta name="twitter:image:alt" content="{{ $resolvedOgImageAlt }}">
@endif

<!-- Additional Page-Level Meta Tags -->
@stack('meta')

<!-- Google Fonts: Playfair Display & Inter (Non-blocking để không chặn FCP) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet"></noscript>

<!-- May Hen Core Vanilla Styles (Inline Critical Core CSS — 0 render-blocking HTTP requests) -->
<style id="mayhen-core-css">{!! inline_core_css([
    'assets/client/css/common/tokens.css',
    'assets/client/css/common/base.css',
    'assets/client/css/common/layout.css',
    'assets/client/css/components/buttons.css',
    'assets/client/css/components/header.css',
]) !!}</style>
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/forms.css') }}" media="print" onload="this.media='all'">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/footer.css') }}" media="print" onload="this.media='all'">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/bottom-nav.css') }}" media="print" onload="this.media='all'">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/atelier-loader.css') }}" media="print" onload="this.media='all'">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/chatbot.css') }}" media="print" onload="this.media='all'">
<link rel="stylesheet" href="{{ asset_v('assets/client/css/components/cookie-consent.css') }}" media="print" onload="this.media='all'">
<noscript>
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/forms.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/footer.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/bottom-nav.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/atelier-loader.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/chatbot.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/cookie-consent.css') }}">
</noscript>
@unless(View::hasSection('hide_chrome') || request()->is('diem-danh*') || request()->is('dieu-phoi-ban-tiec*') || request()->is('trinh-chieu*'))
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/components/seasonal-fall.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset_v('assets/client/css/components/seasonal-fall.css') }}"></noscript>
@endunless

<!-- Page Specific Styles & Schema -->
@stack('styles')
@stack('schema')
