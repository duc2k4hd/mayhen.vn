<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — Mây Hẹn (mayhen.vn)</title>
    @include('client.components.favicon')
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    <!-- Admin Isolated Styles (Zero External CDN / Zero CSS Frameworks) -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/common/layout.css') }}">
    @stack('styles')
</head>
<body>
    <div class="mayhen_admin_layout">
        <!-- Mobile Drawer Backdrop -->
        <div class="mayhen_admin_backdrop" aria-hidden="true"></div>

        <!-- Sidebar Navigation -->
        <aside class="mayhen_admin_sidebar">
            <!-- Sidebar Header Brand -->
            <a href="{{ route('admin.dashboard') }}" class="mayhen_admin_sidebar_brand">
                <div class="mayhen_admin_sidebar_logo_mark">M</div>
                <div class="mayhen_admin_sidebar_brand_text">
                    <span class="mayhen_admin_sidebar_brand_title">MÂY HẸN</span>
                    <span class="mayhen_admin_sidebar_brand_sub">Atelier Control</span>
                </div>
            </a>

            <!-- Nav Menu Sections -->
            <nav class="mayhen_admin_nav_menu" aria-label="Menu quản trị chính">
                <!-- Section 1: Tổng Quan -->
                <div class="mayhen_admin_menu_section">Tổng Quan</div>
                <a href="{{ route('admin.dashboard') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.dashboard') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="9"></rect>
                            <rect x="14" y="3" width="7" height="5"></rect>
                            <rect x="14" y="12" width="7" height="9"></rect>
                            <rect x="3" y="16" width="7" height="5"></rect>
                        </svg>
                    </span>
                    <span>Bảng Điều Khiển (KPI)</span>
                </a>

                <!-- Section 2: Vận Hành Thiệp Cưới -->
                <div class="mayhen_admin_menu_section">Vận Hành Thiệp Cưới</div>
                <a href="{{ route('admin.invitations.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.invitations.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <span>Thiệp Cưới Khách Hàng</span>
                </a>

                <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.templates.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"></path>
                        </svg>
                    </span>
                    <span>Kho Mẫu Thiệp</span>
                </a>

                <!-- Section 3: Kinh Doanh & Đơn Hàng -->
                <div class="mayhen_admin_menu_section">Kinh Doanh & Doanh Thu</div>
                <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.orders.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </span>
                    <span>Đơn Hàng & Doanh Thu</span>
                </a>

                <!-- Section 4: Người Dùng & Tài Khoản -->
                <div class="mayhen_admin_menu_section">Người Dùng & Tài Khoản</div>
                <a href="{{ route('admin.users.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.users.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span>Quản Lý Người Dùng</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="mayhen_admin_sidebar_footer">
                <a href="{{ route('home') }}" class="mayhen_admin_btn_view_site" target="_blank" rel="noopener noreferrer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span>Xem Trang Khách &nearr;</span>
                </a>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="mayhen_admin_main">
            <!-- Topbar Header -->
            <header class="mayhen_admin_topbar">
                <div class="mayhen_admin_topbar_left">
                    <button type="button" class="mayhen_admin_sidebar_toggle" title="Thu gọn / Mở rộng Menu" aria-label="Menu Toggle">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <!-- Breadcrumbs -->
                    <nav class="mayhen_admin_breadcrumbs" aria-label="Đường dẫn trang">
                        <div class="mayhen_admin_breadcrumb_item">
                            <a href="{{ route('admin.dashboard') }}">Admin</a>
                        </div>
                        <span class="mayhen_admin_breadcrumb_sep">/</span>
                        <div class="mayhen_admin_breadcrumb_item active">
                            <span>@yield('page_title', 'Bảng Điều Khiển')</span>
                        </div>
                    </nav>
                </div>

                <!-- Topbar Actions & User Dropdown -->
                <div class="mayhen_admin_topbar_right">
                    <div class="mayhen_admin_user_dropdown_wrap">
                        <button type="button" class="mayhen_admin_user_pill" aria-label="Tài khoản Quản trị">
                            <div class="mayhen_admin_user_avatar">
                                {{ strtoupper(mb_substr(Auth::user()->name ?? 'Q', 0, 1, 'UTF-8')) }}
                            </div>
                            <div class="mayhen_admin_user_meta">
                                <span class="mayhen_admin_user_name">{{ Auth::user()->name ?? 'Quản Trị Viên' }}</span>
                                <span class="mayhen_admin_user_role">Quản trị viên</span>
                            </div>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #8C847A;">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="mayhen_admin_user_dropdown" role="menu">
                            <div style="padding: 8px 12px; font-size: 0.75rem; color: #7A756E; border-bottom: 1px solid #F0ECE4;">
                                Đăng nhập với email:<br>
                                <strong style="color: #1F1D1A;">{{ Auth::user()->email ?? 'admin@mayhen.vn' }}</strong>
                            </div>
                            <a href="{{ route('home') }}" class="mayhen_admin_user_dropdown_item" target="_blank" rel="noopener noreferrer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                <span>Trang chủ Mây Hẹn</span>
                            </a>
                            <div class="mayhen_admin_user_dropdown_divider"></div>
                            <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="mayhen_admin_user_dropdown_item danger" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi trang quản trị?');">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    <span>Đăng Xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="mayhen_admin_content">
                @if (session('success'))
                    <div class="mayhen_admin_alert mayhen_admin_alert_success" role="alert">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mayhen_admin_alert mayhen_admin_alert_danger" role="alert">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Core Vanilla Native JS -->
    <script type="module" src="{{ asset('assets/admin/js/common/admin-core.js') }}"></script>
    @stack('scripts')
</body>
</html>
