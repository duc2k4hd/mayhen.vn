<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin Dashboard') — Mây Hẹn (mayhen.vn)</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin Isolated Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard.css') }}">
</head>
<body>
    <div class="mayhen_admin_layout">
        <!-- Sidebar -->
        <aside class="mayhen_admin_sidebar">
            <div class="mayhen_admin_sidebar_brand">
                <span style="color: #C5A880; font-family: serif; font-size: 1.5rem;">M</span>
                <span>Mây Hẹn Admin</span>
            </div>

            <nav class="mayhen_admin_nav_menu" aria-label="Menu quản trị">
                <a href="{{ route('admin.dashboard') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.dashboard') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span>Tổng Quan (KPI)</span>
                </a>
                <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.templates.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span>Mẫu Thiệp Cưới</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.orders.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span>Đơn Hàng & Thanh Toán</span>
                </a>
                <a href="{{ route('home') }}" class="mayhen_admin_nav_link" target="_blank" style="margin-top: auto;">
                    <span>&larr; Xem Trang Khách</span>
                </a>
            </nav>
        </aside>

        <!-- Main Workspace Area -->
        <div class="mayhen_admin_main">
            <header class="mayhen_admin_topbar">
                <div class="mayhen_admin_topbar_title">@yield('page_title', 'Bảng Điều Khiển')</div>
                <div style="font-size: 0.875rem; color: #6C757D;">
                    Hệ Thống Mây Hẹn (mayhen.vn) &bull; Phiên Bản 1.0
                </div>
            </header>

            <main class="mayhen_admin_content">
                @if (session('success'))
                    <div style="background-color: #D1E7DD; color: #0F5132; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
