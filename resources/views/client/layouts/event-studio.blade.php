@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ $brandName }}">
    <title>@yield('title', 'Studio Thiết Kế Thiệp Sự Kiện & Phong Bì 3D | ' . $brandName)</title>
    <meta name="robots" content="@yield('robots', 'noindex, follow')">
    @include('client.components.favicon')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/event-invitations.css') }}">
    @stack('styles')
</head>
<body class="mayhen_event_studio_app_body">
    @yield('content')
    @stack('scripts')
</body>
</html>
