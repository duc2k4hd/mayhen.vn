<!DOCTYPE html>
<html lang="vi">
<head>
    @include('client.components.head')
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
