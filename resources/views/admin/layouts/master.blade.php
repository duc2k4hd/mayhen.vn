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

                <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.wishes.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </span>
                    <span>Lời Chúc & RSVP</span>
                    @if (($pendingWishesBadge ?? 0) > 0)
                        <span class="mayhen_admin_nav_badge mayhen_admin_nav_badge_gold" title="{{ $pendingWishesBadge }} lời chúc chờ duyệt">{{ $pendingWishesBadge }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.guests.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                    </span>
                    <span>Khách Mời & Check-in</span>
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
                    @if (($pendingOrdersBadge ?? 0) > 0)
                        <span class="mayhen_admin_nav_badge mayhen_admin_nav_badge_gold" title="{{ $pendingOrdersBadge }} đơn hàng chờ đối soát">{{ $pendingOrdersBadge }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.plans.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.plans.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </span>
                    <span>Gói Cước & Đăng Ký</span>
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.coupons.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                    </span>
                    <span>Mã Giảm Giá & Ưu Đãi</span>
                </a>

                <!-- Section 4: Người Dùng & Hỗ Trợ -->
                <div class="mayhen_admin_menu_section">Người Dùng & Hỗ Trợ</div>
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

                <a href="{{ route('admin.support.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.support.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                    <span class="mayhen_admin_nav_icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </span>
                    <span>Trung Tâm Hỗ Trợ</span>
                    @if (($openTicketsBadge ?? 0) > 0)
                        <span class="mayhen_admin_nav_badge mayhen_admin_nav_badge_gold" title="{{ $openTicketsBadge }} ticket đang chờ xử lý">{{ $openTicketsBadge }}</span>
                    @endif
                </a>

                <!-- Section 5: Hệ Thống & Cài Đặt (Chỉ Quản trị viên cấp cao) -->
                @if (Auth::user()?->isSuperAdmin())
                    <div class="mayhen_admin_menu_section">Hệ Thống & Cài Đặt</div>
                    <a href="{{ route('admin.settings.plans') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.settings.plans*') ? 'mayhen_admin_nav_link_active' : '' }}">
                        <span class="mayhen_admin_nav_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        </span>
                        <span>Gói Dịch Vụ & Bảng Giá</span>
                    </a>

                    <a href="{{ route('admin.settings.banking') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.settings.banking') ? 'mayhen_admin_nav_link_active' : '' }}">
                        <span class="mayhen_admin_nav_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </span>
                        <span>Ngân Hàng & VietQR</span>
                    </a>

                    <a href="{{ route('admin.settings.storage') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.settings.storage') ? 'mayhen_admin_nav_link_active' : '' }}">
                        <span class="mayhen_admin_nav_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                            </svg>
                        </span>
                        <span>Thanh Tra Bộ Nhớ Lưu Trữ</span>
                    </a>

                    <a href="{{ route('admin.settings.seo') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.settings.seo*') ? 'mayhen_admin_nav_link_active' : '' }}">
                        <span class="mayhen_admin_nav_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                        </span>
                        <span>Thanh Tra SEO & Sitemap</span>
                    </a>

                    <a href="{{ route('admin.settings.audit_logs') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.settings.audit_logs') ? 'mayhen_admin_nav_link_active' : '' }}">
                        <span class="mayhen_admin_nav_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </span>
                        <span>Nhật Ký Quản Trị (Audit)</span>
                    </a>

                    <a href="{{ route('admin.jobs.index') }}" class="mayhen_admin_nav_link {{ request()->routeIs('admin.jobs.*') ? 'mayhen_admin_nav_link_active' : '' }}">
                        <span class="mayhen_admin_nav_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                        </span>
                        <span>Giám Sát Hàng Đợi (Queue)</span>
                        @if (($failedJobsBadge ?? 0) > 0)
                            <span class="mayhen_admin_nav_badge mayhen_admin_nav_badge_gold" style="background: #C0392B; color: #fff;" title="{{ $failedJobsBadge }} tác vụ lỗi cần xử lý">{{ $failedJobsBadge }}</span>
                        @endif
                    </a>
                @endif
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
                        @hasSection('breadcrumb_parent')
                            <span class="mayhen_admin_breadcrumb_sep">/</span>
                            <div class="mayhen_admin_breadcrumb_item">
                                @yield('breadcrumb_parent')
                            </div>
                        @endif
                        <span class="mayhen_admin_breadcrumb_sep">/</span>
                        <div class="mayhen_admin_breadcrumb_item active">
                            <span>@yield('page_title', 'Bảng Điều Khiển')</span>
                        </div>
                    </nav>
                </div>

                <!-- Topbar Actions & User Dropdown -->
                <div class="mayhen_admin_topbar_right">
                    <!-- Quick Command Search Trigger -->
                    <button type="button" class="mayhen_admin_quick_search_btn" id="mayhen_admin_command_trigger" title="Tìm nhanh menu hoặc thao tác (Phím tắt: Ctrl+K hoặc /)" aria-label="Tìm kiếm nhanh">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span class="mayhen_admin_quick_search_text">Tìm trang hoặc thao tác...</span>
                        <kbd class="mayhen_admin_kbd">Ctrl K</kbd>
                    </button>

                    <div class="mayhen_admin_user_dropdown_wrap">
                        <button type="button" class="mayhen_admin_user_pill" aria-label="Tài khoản Quản trị">
                            <div class="mayhen_admin_user_avatar">
                                {{ strtoupper(mb_substr(Auth::user()->name ?? 'Q', 0, 1, 'UTF-8')) }}
                            </div>
                            <div class="mayhen_admin_user_meta">
                                <span class="mayhen_admin_user_name">{{ Auth::user()->name ?? 'Quản Trị Viên' }}</span>
                                <span class="mayhen_admin_user_role">{{ Auth::user()->role_label ?? 'Quản Trị Viên' }}</span>
                            </div>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #8C847A;">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="mayhen_admin_user_dropdown" role="menu">
                            <div style="padding: 10px 14px; font-size: 0.75rem; color: #7A756E; border-bottom: 1px solid var(--mh-admin-border-subtle); background: #FAF8F5; border-radius: 8px 8px 0 0;">
                                Đăng nhập với tư cách:<br>
                                <strong style="color: var(--mh-admin-charcoal); font-size: 0.8125rem;">{{ Auth::user()->name ?? 'Quản Trị Viên' }}</strong>
                                <div style="font-size: 0.71875rem; color: #8C827A; margin-top: 2px;">{{ Auth::user()->email ?? 'admin@mayhen.vn' }} &bull; <span style="font-weight: 600; color: var(--mh-admin-gold);">{{ Auth::user()->role_label ?? 'Quản Trị Viên' }}</span></div>
                            </div>

                            @if (Auth::user())
                                <a href="{{ route('admin.users.show', Auth::id()) }}" class="mayhen_admin_user_dropdown_item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span>Hồ Sơ & Đổi Mật Khẩu</span>
                                </a>
                            @endif

                            @if (Auth::user()?->isSuperAdmin())
                                <a href="{{ route('admin.settings.audit_logs') }}" class="mayhen_admin_user_dropdown_item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                    <span>Nhật Ký Quản Trị (Audit)</span>
                                </a>
                            @endif

                            <a href="{{ route('home') }}" class="mayhen_admin_user_dropdown_item" target="_blank" rel="noopener noreferrer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                                <span>Xem Trang Khách &nearr;</span>
                            </a>

                            <div class="mayhen_admin_user_dropdown_divider"></div>
                            <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="mayhen_admin_user_dropdown_item danger" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi không gian Quản trị?');">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    <span>Đăng Xuất An Toàn</span>
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

    <!-- Command Palette (Ctrl+K Quick Navigation) -->
    <div class="mayhen_admin_cmd_palette" id="mayhen_admin_command_palette" role="dialog" aria-modal="true" aria-label="Tìm kiếm trang nhanh">
        <div class="mayhen_admin_cmd_dialog">
            <div class="mayhen_admin_cmd_header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" class="mayhen_admin_cmd_input" id="mayhen_admin_command_input" placeholder="Gõ tên trang, phân hệ hoặc thao tác cần tìm..." autocomplete="off">
                <kbd class="mayhen_admin_kbd">ESC để đóng</kbd>
            </div>
            <div class="mayhen_admin_cmd_list" id="mayhen_admin_command_list">
                <a href="{{ route('admin.dashboard') }}" class="mayhen_admin_cmd_item" data-search="kpi dashboard tổng quan báo cáo số liệu">
                    <span>📊 Bảng Điều Khiển (KPI Dashboard)</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/dashboard</span>
                </a>
                <a href="{{ route('admin.invitations.index') }}" class="mayhen_admin_cmd_item" data-search="thiệp cưới khách hàng dự án weddings invitations">
                    <span>💌 Thiệp Cưới Khách Hàng</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/invitations</span>
                </a>
                <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_cmd_item" data-search="kho mẫu thiệp cưới templates giao diện">
                    <span>🎨 Kho Mẫu Thiệp Cưới</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/templates</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_cmd_item" data-search="đơn hàng doanh thu thanh toán sepay vietqr orders">
                    <span>💰 Đơn Hàng & Doanh Thu</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/orders</span>
                </a>
                <a href="{{ route('admin.plans.index') }}" class="mayhen_admin_cmd_item" data-search="gói cước bảng giá plans subscription pricing đăng ký gói">
                    <span>📦 Gói Cước & Đăng Ký</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/plans</span>
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="mayhen_admin_cmd_item" data-search="mã giảm giá ưu đãi coupons khuyến mãi promotion voucher discount">
                    <span>🎟️ Mã Giảm Giá & Khuyến Mãi</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/coupons</span>
                </a>
                <a href="{{ route('admin.orders.webhooks') }}" class="mayhen_admin_cmd_item" data-search="webhook sepay vietqr đối soát thanh toán giao dịch">
                    <span>⚡ Nhật Ký Webhook VietQR / SePay</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/orders/webhooks/audit</span>
                </a>
                <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_cmd_item" data-search="lời chúc sổ lưu bút guestbook kiểm duyệt">
                    <span>✍️ Kiểm Duyệt Lời Chúc Mừng</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/wishes</span>
                </a>
                <a href="{{ route('admin.wishes.rsvps') }}" class="mayhen_admin_cmd_item" data-search="rsvp khách mời xác nhận tham dự attendance">
                    <span>📋 Giám Sát Phản Hồi RSVP</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/wishes/rsvps</span>
                </a>
                <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_cmd_item" data-search="khách mời điểm danh check-in ngày cưới seating bàn tiệc guests">
                    <span>🎟️ Khách Mời & Điểm Danh Ngày Cưới</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/guests</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="mayhen_admin_cmd_item" data-search="người dùng tài khoản phân quyền rbac users staff manager">
                    <span>👥 Quản Lý Người Dùng & Phân Quyền</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/users</span>
                </a>
                <a href="{{ route('admin.support.index') }}" class="mayhen_admin_cmd_item" data-search="trung tâm hỗ trợ support tickets khiếu nại báo lỗi trợ giúp khách hàng">
                    <span>🎧 Trung Tâm Hỗ Trợ Khách Hàng (Tickets)</span>
                    <span class="mayhen_admin_cmd_item_meta">/admin/support</span>
                </a>
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('admin.settings.plans') }}" class="mayhen_admin_cmd_item" data-search="gói cước dịch vụ bảng giá plans pricing">
                        <span>⭐ Gói Dịch Vụ & Bảng Giá</span>
                        <span class="mayhen_admin_cmd_item_meta">/admin/settings/plans</span>
                    </a>
                    <a href="{{ route('admin.settings.banking') }}" class="mayhen_admin_cmd_item" data-search="ngân hàng vietqr sepay tài khoản thụ hưởng banking">
                        <span>🏦 Ngân Hàng & VietQR</span>
                        <span class="mayhen_admin_cmd_item_meta">/admin/settings/banking</span>
                    </a>
                    <a href="{{ route('admin.settings.storage') }}" class="mayhen_admin_cmd_item" data-search="thanh tra bộ nhớ lưu trữ storage media inspector">
                        <span>💾 Thanh Tra Bộ Nhớ Lưu Trữ</span>
                        <span class="mayhen_admin_cmd_item_meta">/admin/settings/storage</span>
                    </a>
                    <a href="{{ route('admin.settings.seo') }}" class="mayhen_admin_cmd_item" data-search="thanh tra seo sitemap xml google robots meta search index">
                        <span>🌐 Thanh Tra SEO & Sitemap XML</span>
                        <span class="mayhen_admin_cmd_item_meta">/admin/settings/seo</span>
                    </a>
                    <a href="{{ route('admin.settings.audit_logs') }}" class="mayhen_admin_cmd_item" data-search="nhật ký kiểm toán audit logs trail lịch sử thao tác">
                        <span>📜 Nhật Ký Quản Trị (Audit Logs)</span>
                        <span class="mayhen_admin_cmd_item_meta">/admin/settings/audit-logs</span>
                    </a>
                    <a href="{{ route('admin.jobs.index') }}" class="mayhen_admin_cmd_item" data-search="hàng đợi failed jobs queue worker tác vụ nền lỗi retry">
                        <span>⚙️ Giám Sát Hàng Đợi (Queue & Jobs)</span>
                        <span class="mayhen_admin_cmd_item_meta">/admin/system/jobs</span>
                    </a>
                @endif
                <a href="{{ route('home') }}" class="mayhen_admin_cmd_item" target="_blank" data-search="trang chủ website khách hàng landing home">
                    <span>🌐 Xem Trang Khách Hàng (Website) &nearr;</span>
                    <span class="mayhen_admin_cmd_item_meta">mayhen.vn</span>
                </a>
            </div>
            <div class="mayhen_admin_cmd_footer">
                <span>Dùng phím <kbd class="mayhen_admin_kbd">&uarr;</kbd> <kbd class="mayhen_admin_kbd">&darr;</kbd> để chọn &bull; <kbd class="mayhen_admin_kbd">Enter</kbd> để mở</span>
                <span>ESC để đóng</span>
            </div>
        </div>
    </div>

    <!-- Core Vanilla Native JS -->
    <script type="module" src="{{ asset('assets/admin/js/common/admin-core.js') }}"></script>
    @stack('scripts')
</body>
</html>
