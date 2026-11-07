<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cổng Đối Tác Studio & Wedding Planner | ' . config('app.name'))</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts: Cormorant Garamond & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Partner Styles -->
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/partner.css') }}">
    @stack('styles')
</head>
<body class="mayhen_partner_body">

    <!-- Top Navigation Bar -->
    <nav class="mayhen_partner_navbar" aria-label="Partner Navigation">
        <div class="mayhen_partner_nav_container">
            <a href="{{ route('partner.dashboard') }}" class="mayhen_partner_brand_wrap">
                <span class="mayhen_partner_brand_title">MÂY HẸN</span>
                <span class="mayhen_partner_portal_pill">STUDIO PARTNER</span>
            </a>

            @if(auth()->check() && (auth()->user()->isPartner() || auth()->user()->isAdmin()))
                <div class="mayhen_partner_nav_links">
                    <a href="{{ route('partner.dashboard') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.dashboard') ? 'is-active' : '' }}">
                        📊 Tổng Quan
                    </a>
                    <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.weddings.*') ? 'is-active' : '' }}">
                        💌 Thiệp Khách Hàng
                    </a>
                    <a href="{{ route('partner.commissions.index') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.commissions.*') ? 'is-active' : '' }}">
                        💰 Hoa Hồng & Chiết Khấu
                    </a>
                    <a href="{{ route('partner.profile') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.profile') ? 'is-active' : '' }}">
                        ⚜️ Hồ Sơ Thương Hiệu
                    </a>
                </div>
            @endif

            <div class="mayhen_partner_nav_actions">
                @if(auth()->check())
                    <a href="{{ route('partner.weddings.create') }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--gold">
                        + Tạo Thiệp Cho Khách
                    </a>
                    <a href="{{ route('account.invitations') }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline">
                        Về Cá Nhân
                    </a>
                @else
                    <a href="{{ route('login') }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline">
                        Đăng Nhập
                    </a>
                @endif
            </div>
        </div>

        <!-- Mobile Subnav Bar -->
        @if(auth()->check() && (auth()->user()->isPartner() || auth()->user()->isAdmin()))
            <div class="mayhen_partner_mobile_subnav">
                <a href="{{ route('partner.dashboard') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.dashboard') ? 'is-active' : '' }}">
                    Tổng Quan
                </a>
                <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.weddings.*') ? 'is-active' : '' }}">
                    Thiệp Khách Hàng
                </a>
                <a href="{{ route('partner.commissions.index') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.commissions.*') ? 'is-active' : '' }}">
                    Hoa Hồng
                </a>
                <a href="{{ route('partner.profile') }}" class="mayhen_partner_nav_item {{ request()->routeIs('partner.profile') ? 'is-active' : '' }}">
                    Hồ Sơ
                </a>
            </div>
        @endif
    </nav>

    <!-- Main Content Area -->
    <main class="mayhen_partner_main">
        @yield('content')
    </main>

    <!-- Partner Scripts -->
    <script type="module" src="{{ asset('assets/client/js/pages/partner.js') }}"></script>
    @stack('scripts')
</body>
</html>

