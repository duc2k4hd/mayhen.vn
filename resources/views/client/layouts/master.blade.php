<!DOCTYPE html>
<html lang="vi">
<head>
    @include('client.components.head')
</head>
<body class="@yield('body_class')">
    @unless(View::hasSection('hide_chrome'))
        @include('client.components.header')
    @endunless

    <main class="mayhen_client_layout_main @yield('main_class')">
        @yield('content')
    </main>

    @unless(View::hasSection('hide_chrome'))
        @include('client.components.footer')
        @include('client.components.bottom-nav')
    @endunless

    <!-- May Hen Core Scripts -->
    <script type="module" src="{{ asset('assets/client/js/components/header.js') }}"></script>
    @stack('scripts')
</body>
</html>
