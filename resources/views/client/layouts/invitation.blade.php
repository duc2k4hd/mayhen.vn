@php
    $isEmbed = ($isEmbed ?? false) 
        || request()->boolean('embed') 
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);

    $invConfig = (isset($invitation) && is_array($invitation->custom_config)) 
        ? $invitation->custom_config 
        : (isset($invitation) ? json_decode((string) ($invitation->custom_config ?? '{}'), true) : []);
    $colorTheme = $invConfig['color_theme'] ?? ($invConfig['curated_layout']['palette'] ?? request()->query('palette', ''));
@endphp
<!DOCTYPE html>
<html lang="vi" class="@yield('html_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }} {{ request()->boolean('crafting') ? 'mayhen_is_crafting mayhen_crafting_preparing' : '' }} {{ !empty($colorTheme) ? 'mayhen_palette_' . $colorTheme : '' }}">
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

    {{-- Dynamic Open Graph & Rich Social Link Preview (Section 27 Master PRD V3) --}}
    <meta property="og:site_name" content="Mây Hẹn (mayhen.vn)">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $ogTitle ?? ($pageTitle ?? config('app.name')) }}">
    <meta property="og:description" content="{{ $ogDescription ?? ($metaDescription ?? 'Thiệp cưới online tinh tế và trang trọng từ Mây Hẹn.') }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}">

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle ?? ($pageTitle ?? config('app.name')) }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? ($metaDescription ?? 'Thiệp cưới online tinh tế và trang trọng từ Mây Hẹn.') }}">
    @if(!empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
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
            /* Chống FOUC: Khi đang nạp trang dệt, toàn bộ nội dung được ẩn an toàn */
            html.mayhen_crafting_preparing body .mayhen_invitation_section,
            html.mayhen_crafting_preparing body .mayhen_invitation_hero_card,
            html.mayhen_crafting_preparing body [data-mayhen-section],
            html.mayhen_crafting_preparing body .mayhen_invitation_reveal {
                opacity: 0 !important;
                visibility: hidden !important;
            }
            /* Khóa triệt để các section chưa tạo: TUYỆT ĐỐI KHÔNG HIỂN THỊ TRƯỚC */
            html.mayhen_is_crafting .mayhen_crafting_section_waiting,
            body.mayhen_is_crafting .mayhen_crafting_section_waiting {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
                max-height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            /* 2. RÈM LỤA ATELIER BAN ĐẦU (SILK CURTAIN CHỐNG FOUC NHẸ NHÀNG) */
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
                transition: opacity 0.25s ease, visibility 0.25s ease;
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

    {{-- BỘ CHUYỂN ĐỔI BẢNG MÀU CHUẨN QUIET LUXURY (PALETTE THEME OVERRIDE - NẰM CUỐI THẺ HEAD ĐẢM BẢO ƯU TIÊN TUYỆT ĐỐI) --}}
    @if(!empty($colorTheme))
        <style id="mayhenPaletteThemeStyles">
            @if($colorTheme === 'ruby')
                /* BẢNG MÀU ĐỎ RƯỢU VANG & VÀNG ANTIQUE (ROYAL VELVET RUBY) */
                :root {
                    --mayhen-primary: #85182A !important;
                    --mayhen-bg-cream: #FAF7F2 !important;
                    --mayhen-bg-ivory: #FFFDF9 !important;
                    --mayhen-bg-sage-soft: #F9ECEE !important;
                    --mayhen-sage-light: #C97A88 !important;
                    --mayhen-sage-main: #85182A !important;
                    --mayhen-sage-dark: #5E0E1C !important;
                    --mayhen-forest-deep: #5E0E1C !important;
                    --mayhen-forest-ink: #3D0812 !important;
                    --mayhen-gold-antique: #CFA860 !important;
                    --mayhen-gold-light: #F4E2B8 !important;
                    --mayhen-gold-deep: #967332 !important;
                    --mayhen-text-main: #1F1D1A !important;
                    --mayhen-border-gold: rgba(207, 168, 96, 0.45) !important;
                    --mayhen-border-sage: rgba(133, 24, 42, 0.28) !important;
                    --mayhen-invitation-accent: #85182A !important;
                    --mayhen-invitation-accent-bright: #9E1F34 !important;
                    --mayhen-invitation-accent-deep: #5E0E1C !important;
                    --mayhen-invitation-door-wine: #5E0E1C !important;
                    --mayhen-invitation-bg-crimson: #85182A !important;
                    --mayhen-invitation-bg-crimson-deep: #5E0E1C !important;
                }
                body.mayhen_invitation_body,
                html body {
                    background-color: #FAF7F2 !important;
                }
                .mayhen_invitation_hero_frame {
                    background: linear-gradient(145deg, #CFA860, #85182A) !important;
                }
                .mayhen_invitation_section--sage {
                    background: linear-gradient(180deg, #F9ECEE 0%, #FAF7F2 100%) !important;
                }
                .mayhen_invitation_hero_card {
                    background: rgba(255, 253, 249, 0.96) !important;
                    border-color: rgba(207, 168, 96, 0.55) !important;
                }
                .mayhen_invitation_hero_badge, .mayhen_invitation_eyebrow {
                    color: #85182A !important;
                }
                .mayhen_invitation_hero_date_pill {
                    background: #5E0E1C !important;
                    color: #F4E2B8 !important;
                    border-color: #CFA860 !important;
                }
                .mayhen_invitation_script_title, .mayhen_invitation_title {
                    color: #5E0E1C !important;
                }
                .mayhen_invitation_family_side {
                    color: #85182A !important;
                }
                .mayhen_invitation_calendar_day--active {
                    background: #85182A !important;
                    color: #FFFFFF !important;
                }
                .mayhen_invitation_calendar_day--active svg {
                    fill: #CFA860 !important;
                }
                .mayhen_invitation_countdown_item, .mayhen_invitation_countdown_box {
                    background: #5E0E1C !important;
                    color: #F4E2B8 !important;
                    border-color: rgba(207, 168, 96, 0.4) !important;
                }
                .mayhen_invitation_party_card {
                    border-color: rgba(207, 168, 96, 0.4) !important;
                }
                .mayhen_invitation_map_btn, .mayhen_invitation_submit_btn {
                    background: #85182A !important;
                    color: #FFFFFF !important;
                }
                .mayhen_invitation_door {
                    background: linear-gradient(165deg, #741525 0%, #4f0b17 55%, #3d0812 100%) !important;
                }
            @elseif($colorTheme === 'dusty-rose')
                /* BẢNG MÀU HỒNG TRO & CHAMPAGNE LỤA (DUSTY ROSE) */
                :root {
                    --mayhen-primary: #A26B6B !important;
                    --mayhen-bg-cream: #FFFDF9 !important;
                    --mayhen-bg-sage-soft: #FCF4F4 !important;
                    --mayhen-sage-light: #E0BCBC !important;
                    --mayhen-sage-main: #A26B6B !important;
                    --mayhen-sage-dark: #7D4E4E !important;
                    --mayhen-forest-deep: #7D4E4E !important;
                    --mayhen-forest-ink: #4E2E2E !important;
                    --mayhen-gold-antique: #C5A880 !important;
                    --mayhen-gold-light: #F0DFC8 !important;
                    --mayhen-border-sage: rgba(162, 107, 107, 0.28) !important;
                    --mayhen-invitation-accent: #A26B6B !important;
                    --mayhen-invitation-accent-deep: #7D4E4E !important;
                }
                body.mayhen_invitation_body,
                html body {
                    background-color: #FFFDF9 !important;
                }
                .mayhen_invitation_hero_frame {
                    background: linear-gradient(145deg, #C5A880, #A26B6B) !important;
                }
                .mayhen_invitation_hero_badge, .mayhen_invitation_eyebrow { color: #A26B6B !important; }
                .mayhen_invitation_hero_date_pill { background: #7D4E4E !important; color: #F0DFC8 !important; border-color: #C5A880 !important; }
                .mayhen_invitation_script_title, .mayhen_invitation_title { color: #7D4E4E !important; }
                .mayhen_invitation_calendar_day--active { background: #A26B6B !important; color: #FFFFFF !important; }
                .mayhen_invitation_countdown_item, .mayhen_invitation_countdown_box { background: #7D4E4E !important; color: #F0DFC8 !important; }
                .mayhen_invitation_door { background: linear-gradient(165deg, #A26B6B 0%, #7D4E4E 100%) !important; }
            @elseif($colorTheme === 'royal-navy')
                /* BẢNG MÀU XANH HOÀNG GIA & ÁNH BẠC (ROYAL NAVY) */
                :root {
                    --mayhen-primary: #1B2A4A !important;
                    --mayhen-bg-cream: #F8FAFC !important;
                    --mayhen-bg-sage-soft: #EFF4FA !important;
                    --mayhen-sage-light: #7E96BF !important;
                    --mayhen-sage-main: #2A406C !important;
                    --mayhen-sage-dark: #1B2A4A !important;
                    --mayhen-forest-deep: #0F1A30 !important;
                    --mayhen-forest-ink: #0A1222 !important;
                    --mayhen-gold-antique: #9FAEC2 !important;
                    --mayhen-gold-light: #E2E8F0 !important;
                    --mayhen-border-sage: rgba(27, 42, 74, 0.25) !important;
                    --mayhen-invitation-accent: #1B2A4A !important;
                    --mayhen-invitation-accent-deep: #0F1A30 !important;
                }
                body.mayhen_invitation_body,
                html body {
                    background-color: #F8FAFC !important;
                }
                .mayhen_invitation_hero_frame {
                    background: linear-gradient(145deg, #9FAEC2, #1B2A4A) !important;
                }
                .mayhen_invitation_hero_badge, .mayhen_invitation_eyebrow { color: #1B2A4A !important; }
                .mayhen_invitation_hero_date_pill { background: #0F1A30 !important; color: #E2E8F0 !important; border-color: #9FAEC2 !important; }
                .mayhen_invitation_script_title, .mayhen_invitation_title { color: #0F1A30 !important; }
                .mayhen_invitation_calendar_day--active { background: #1B2A4A !important; color: #FFFFFF !important; }
                .mayhen_invitation_countdown_item, .mayhen_invitation_countdown_box { background: #0F1A30 !important; color: #E2E8F0 !important; }
                .mayhen_invitation_door { background: linear-gradient(165deg, #2A406C 0%, #1B2A4A 100%) !important; }
            @elseif($colorTheme === 'warm-amber')
                /* BẢNG MÀU NÂU HỔ PHÁCH & HOÀNG HÔN ẤM (WARM AMBER) */
                :root {
                    --mayhen-primary: #8C4724 !important;
                    --mayhen-bg-cream: #FFFBF5 !important;
                    --mayhen-bg-sage-soft: #FDF4ED !important;
                    --mayhen-sage-light: #DCA180 !important;
                    --mayhen-sage-main: #8C4724 !important;
                    --mayhen-sage-dark: #6E3416 !important;
                    --mayhen-forest-deep: #5C2D15 !important;
                    --mayhen-forest-ink: #3B1B0A !important;
                    --mayhen-gold-antique: #D4A373 !important;
                    --mayhen-gold-light: #F7E5D2 !important;
                    --mayhen-border-sage: rgba(140, 71, 36, 0.28) !important;
                    --mayhen-invitation-accent: #8C4724 !important;
                    --mayhen-invitation-accent-deep: #5C2D15 !important;
                }
                body.mayhen_invitation_body,
                html body {
                    background-color: #FFFBF5 !important;
                }
                .mayhen_invitation_hero_frame {
                    background: linear-gradient(145deg, #D4A373, #8C4724) !important;
                }
                .mayhen_invitation_hero_badge, .mayhen_invitation_eyebrow { color: #8C4724 !important; }
                .mayhen_invitation_hero_date_pill { background: #5C2D15 !important; color: #F7E5D2 !important; border-color: #D4A373 !important; }
                .mayhen_invitation_script_title, .mayhen_invitation_title { color: #5C2D15 !important; }
                .mayhen_invitation_calendar_day--active { background: #8C4724 !important; color: #FFFFFF !important; }
                .mayhen_invitation_countdown_item, .mayhen_invitation_countdown_box { background: #5C2D15 !important; color: #F7E5D2 !important; }
                .mayhen_invitation_door { background: linear-gradient(165deg, #8C4724 0%, #5C2D15 100%) !important; }
            @endif
        </style>
    @endif

    {{-- Favicon --}}
    @include('client.components.favicon')
</head>
<body class="@yield('body_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }} {{ (request()->boolean('crafting') || request()->filled('recraft_section')) ? 'mayhen_is_crafting' : '' }} {{ !empty($colorTheme) ? 'mayhen_palette_' . $colorTheme : '' }}" data-mayhen-invitation-config="{{ json_encode($invitation->custom_config ?? new stdClass()) }}" data-palette="{{ $colorTheme }}" @yield('body_attributes')>
    @if(request()->boolean('crafting') && !request()->filled('recraft_section'))
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

    {{-- Banner Kỷ Niệm Hậu Sự Kiện & Lời Cảm Ơn (Post-Wedding Keepsake & Memorial Mode - Mục 2.8 DOCX) --}}
    @include('client.components.memorial-keepsake-banner')

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
    @if(request()->boolean('crafting') || request()->filled('recraft_section'))
        <script src="{{ asset('assets/client/js/pages/native-crafting-presenter.js') }}?v={{ time() }}"></script>
    @endif
</body>
</html>
