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
</head>
<body class="@yield('body_class') {{ $isEmbed ? 'mayhen_embed_mode mayhen_template_auto_opened' : '' }}" @yield('body_attributes')>
    {{-- Nội dung nghệ thuật chính của mẫu thiệp cưới --}}
    @yield('content')

    {{-- Modals / Lightbox / Popups nếu có --}}
    @yield('modals')

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
</body>
</html>
