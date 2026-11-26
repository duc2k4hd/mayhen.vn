<!DOCTYPE html>
<html lang="vi">
<head>
    @include('client.components.head')
</head>
<body class="@yield('body_class')">
    @include('client.components.impersonation-banner')
    @include('client.components.announcement-bar')

    @unless(View::hasSection('hide_chrome'))
        @include('client.components.header')
    @endunless

    <main class="mayhen_client_layout_main @yield('main_class')">
        @yield('content')
    </main>

    @unless(View::hasSection('hide_chrome'))
        @include('client.components.footer')
        @unless(request()->is('tai-khoan*') || request()->is('diem-danh*') || request()->is('dieu-phoi-ban-tiec*') || request()->is('khach-moi*'))
            @include('client.components.bottom-nav')
        @endunless
        @unless(request()->is('diem-danh*') || request()->is('dieu-phoi-ban-tiec*') || request()->is('trinh-chieu*'))
            @include('client.components.chatbot')
            @include('client.components.cookie-consent-banner')
        @endunless
    @endunless

    <!-- May Hen Core Scripts -->
    <script type="module" src="{{ asset_v('assets/client/js/components/header.js') }}"></script>
    @unless(View::hasSection('hide_chrome') || request()->is('diem-danh*') || request()->is('dieu-phoi-ban-tiec*') || request()->is('trinh-chieu*'))
        <script type="module" src="{{ asset_v('assets/client/js/components/chatbot.js') }}"></script>
        <script type="module" src="{{ asset_v('assets/client/js/components/seasonal-fall.js') }}"></script>
        <script type="module" src="{{ asset_v('assets/client/js/components/cookie-consent.js') }}"></script>
    @endunless
    @stack('scripts')
</body>
</html>
