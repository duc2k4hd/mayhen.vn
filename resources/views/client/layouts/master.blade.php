<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Thiệp Cưới Online — ' . config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', config('app.name') . ' — Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury, hiệu ứng mở phong bì hoàng gia, tối ưu điện thoại và quản lý khách mời chu đáo.')">
    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    
    <!-- Open Graph / Facebook / Zalo -->
    <meta property="og:type" content="website">
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

    @stack('styles')
    @stack('schema')
</head>
<body>
    @include('client.components.header')

    <main class="mayhen_client_layout_main">
        @yield('content')
    </main>

    @include('client.components.footer')

    <!-- May Hen Core Scripts -->
    <script type="module" src="{{ asset('assets/client/js/components/header.js') }}"></script>
    @stack('scripts')
</body>
</html>
