@php
    $isEmbed = ($isEmbed ?? false) 
        || request()->boolean('embed') 
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
@endphp
<!DOCTYPE html>
<html lang="vi" class="@yield('html_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }} {{ request()->boolean('crafting') ? 'mayhen_is_crafting' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $pageTitle ?? config('app.name'))</title>
    <meta name="robots" content="noindex, nofollow">
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @elseif(!empty($metaDescription))
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    {{-- Favicon chuẩn Mây Hẹn --}}
    @include('client.components.favicon')

    {{-- Typography Fonts của từng mẫu thiệp --}}
    @stack('fonts')

    {{-- Stylesheets của từng mẫu thiệp --}}
    @stack('styles')

    {{-- PHÒNG VỆ HIỂN THỊ CHẾ ĐỘ DỆT THIỆP AI (ZERO FLASH OF CONTENT - CRITICAL HEAD CSS) --}}
    @if(request()->boolean('crafting'))
        <style id="mayhenCraftingInitialCurtainStyles">
            /* 1. Mở sẵn cánh cửa thiệp 3D & ẩn các dock âm thanh không cần thiết khi dệt */
            .mayhen_invitation_opening,
            .mayhen_invitation_sr_cua,
            .mayhen_invitation_music_dock,
            .mayhen_client_bottom_nav_wrapper {
                display: none !important;
            }
            html.mayhen_is_crafting,
            body.mayhen_is_crafting {
                overflow-x: hidden !important;
                background-color: #FAF8F5 !important;
            }
            /* 2. ẨN TOÀN BỘ NỘI DUNG THIỆP NGAY TỪ ĐẦU (TUYỆT ĐỐI KHÔNG SHOW TRƯỚC KHI DỆT) */
            body.mayhen_is_crafting .mayhen_invitation_section,
            body.mayhen_is_crafting section,
            body.mayhen_is_crafting footer,
            body.mayhen_is_crafting [data-mayhen-section] {
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }
            /* 3. RÈM LỤA ATELIER CANVAS BAN ĐẦU (SILK CURTAIN STATE) */
            #mayhenCraftingCurtain {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: radial-gradient(circle at 50% 40%, #FFFDF9 0%, #F5EFEB 100%);
                z-index: 999999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 16px;
                transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.75s ease;
                pointer-events: none;
            }
            #mayhenCraftingCurtain.is-hidden {
                opacity: 0 !important;
                visibility: hidden !important;
            }
            .mayhen_curtain_crest {
                font-size: 26px;
                color: #9C7A4A;
                line-height: 1;
                animation: mayhenCrestPulse 2s infinite ease-in-out;
            }
            .mayhen_curtain_brand {
                font-family: 'Cormorant Garamond', Georgia, serif;
                font-size: 20px;
                font-weight: 600;
                letter-spacing: 4px;
                color: #1F1D1A;
                text-transform: uppercase;
            }
            .mayhen_curtain_line {
                width: 48px;
                height: 1px;
                background: linear-gradient(90deg, transparent, #C5A880, transparent);
            }
            .mayhen_curtain_status {
                font-family: 'Inter', -apple-system, sans-serif;
                font-size: 11px;
                font-weight: 500;
                letter-spacing: 1.5px;
                color: #7A726A;
                text-transform: uppercase;
                animation: mayhenTextBreathe 1.8s infinite ease-in-out;
            }
            @keyframes mayhenCrestPulse {
                0%, 100% { transform: scale(0.92); opacity: 0.65; }
                50% { transform: scale(1.18); opacity: 1; filter: drop-shadow(0 0 10px rgba(197, 168, 128, 0.75)); }
            }
            @keyframes mayhenTextBreathe {
                0%, 100% { opacity: 0.45; }
                50% { opacity: 0.95; }
            }
        </style>
    @endif

    @php
        $hideBottomNav = ($isEmbed ?? false) || request()->boolean('embed') || request()->has('hide_nav') || request()->has('in_modal') || request()->has('popup');
    @endphp

    {{-- Thanh điều hướng chân trang CSS (tự động nạp khi xem thử ở tab độc lập) --}}
    @if (($isPreview ?? false) && !$hideBottomNav)
        <link rel="stylesheet" href="{{ asset('assets/client/css/components/bottom-nav.css') }}">
    @endif

    {{-- Extra Styles / Code nhúng trong thẻ head --}}
    @yield('head_extra')

    @if(isset($noNavBottom) && $noNavBottom == true) 
        <style>
            .mayhen_client_bottom_nav_wrapper {
                display: none !important;
            }
        </style>
    @endif

    {{-- Favicon --}}
    @include('client.components.favicon')
</head>
<body class="@yield('body_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }} {{ request()->boolean('crafting') ? 'mayhen_is_crafting' : '' }}" data-mayhen-invitation-config="{{ json_encode($invitation->custom_config ?? new stdClass()) }}" @yield('body_attributes')>
    @if(request()->boolean('crafting'))
        {{-- RÈM LỤA KHỞI TẠO DỆT THIỆP (INITIAL ATELIER CURTAIN) --}}
        <div id="mayhenCraftingCurtain" aria-hidden="true">
            <span class="mayhen_curtain_crest">✦</span>
            <div class="mayhen_curtain_brand">Mây Hẹn Atelier</div>
            <div class="mayhen_curtain_line"></div>
            <div class="mayhen_curtain_status">Đang dệt tác phẩm độc bản...</div>
        </div>
    @endif

    {{-- Thanh Điều Khiển Chế Độ Tổng Duyệt Trước Xuất Bản (Pre-Publish Rehearsal - TASK-VAL-08) --}}
    @include('client.components.rehearsal-bar')

    {{-- Nội dung nghệ thuật chính của mẫu thiệp cưới --}}
    @yield('content')

    {{-- Modals / Lightbox / Popups nếu có --}}
    @yield('modals')

    {{-- Thẻ Tham Dự & Vị Trí Bàn Tiệc Khách Mời (Guest Table Finder & QR Pass) --}}
    @include('client.components.guest-table-badge')

    {{-- Chế Độ Tiếp Cận Dành Riêng Cho Người Lớn Tuổi (Senior-Friendly Guest Mode - TASK-VAL-01) --}}
    @include('client.components.senior-mode-widget')

    {{-- Chỉ Dẫn Bãi Đỗ Xe & Lối Vào Sảnh Tiệc (Interactive Parking Concierge - TASK-VAL-05) --}}
    @include('client.components.parking-concierge-modal')

    {{-- Huy hiệu Co-branding của Studio Đối Tác (TASK-V3-01) --}}
    @if(isset($wedding) && $wedding && $wedding->partner && $wedding->partner->isActive())
        @php
            $pConfig = is_array($wedding->partner->custom_config) && isset($wedding->partner->custom_config['branding'])
                ? $wedding->partner->custom_config['branding']
                : [];
            $showBadge = $pConfig['show_badge'] ?? true;
            $footerText = $pConfig['footer_text'] ?? ('Đồng hành cùng ' . $wedding->partner->business_name);
        @endphp
        @if($showBadge && !($isEmbed ?? false))
            <div class="mayhen_invitation_partner_badge" style="text-align: center; padding: 1.5rem 1rem 3rem; font-size: 0.78rem; letter-spacing: 0.08em; color: rgba(31, 29, 26, 0.5); text-transform: uppercase;">
                @if($wedding->partner->logo_url)
                    <img src="{{ $wedding->partner->logo_url }}" alt="{{ $wedding->partner->business_name }}" style="max-height: 24px; width: auto; opacity: 0.6; display: block; margin: 0 auto 0.4rem;" loading="lazy">
                @endif
                <span>{{ $footerText }}</span>
            </div>
        @endif
    @endif

    {{-- Thanh điều hướng chân trang (chỉ nạp khi xem thử ở tab độc lập ngoài trình duyệt) --}}
    @if (($isPreview ?? false) && !$hideBottomNav)
        @include('client.components.bottom-nav')
    @endif

    {{-- Bộ kết nối chuẩn hóa giữa Live Studio Editor và Template --}}
    @include('client.invitations.partials.editor-bridge')

    {{-- Cấu hình JavaScript toàn cục chuẩn hóa --}}
    <script>
        window.__MAYHEN_IS_EMBED = {{ ($isEmbed ?? false) ? 'true' : 'false' }};
        window.__MAYHEN_IS_PREVIEW = {{ ($isPreview ?? false) ? 'true' : 'false' }};
        window.__MAYHEN_CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
    @stack('scripts')
    <script type="module" src="{{ asset('assets/client/js/common/invitation-config.js') }}"></script>
    @if(request()->boolean('crafting'))
        <script src="{{ asset('assets/client/js/pages/native-crafting-presenter.js') }}?v={{ time() }}"></script>
    @endif
</body>
</html>
