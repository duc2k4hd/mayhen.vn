@php
    $isEmbed = ($isEmbed ?? false) 
        || request()->boolean('embed') 
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
@endphp
<!DOCTYPE html>
<html lang="vi" class="@yield('html_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }}">
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
<body class="@yield('body_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }}" data-mayhen-invitation-config="{{ json_encode($invitation->custom_config ?? new stdClass()) }}" @yield('body_attributes')>
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
</body>
</html>
