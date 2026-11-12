<header class="mayhen_client_header_wrapper">
    <div class="mayhen_client_layout_container mayhen_client_header_container">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="mayhen_client_header_brand" aria-label="{{ config('app.name') }} - Thiệp Cưới Online">
            <svg class="mayhen_client_header_logo_icon" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 3C16 3 13 8 9 10C5 12 3 16 3 18C3 24 9 28 16 29C23 28 29 24 29 18C29 16 27 12 23 10C19 8 16 3 16 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 11V23M11 16H21" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
            <span class="mayhen_client_header_brand_name">{{ config('app.name') }}</span>
            <span class="mayhen_client_header_brand_tagline">Thiệp Cưới Online</span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="mayhen_client_header_nav" aria-label="Điều hướng chính">
            <a href="{{ route('templates.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('templates.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Mẫu Thiệp</a>
            <a href="{{ route('services') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('services') ? 'mayhen_client_header_nav_link_active' : '' }}">Dịch Vụ</a>
            <a href="{{ route('pricing') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('pricing') ? 'mayhen_client_header_nav_link_active' : '' }}">Bảng Giá</a>
            <a href="{{ route('blog.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('blog.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Cẩm Nang</a>
            <a href="{{ route('docs.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('docs.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Hướng Dẫn</a>
            <a href="{{ route('contact') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('contact') ? 'mayhen_client_header_nav_link_active' : '' }}">Liên Hệ</a>
        </nav>

        <!-- Header Actions -->
        <div class="mayhen_client_header_actions">
            @auth
                @php
                    $headerUserName = auth()->user()->name ?? 'Tài khoản';
                    $headerNameParts = preg_split('/\s+/', trim($headerUserName));
                    $headerLastName = end($headerNameParts) ?: $headerUserName;
                    $headerUserInitial = mb_strtoupper(mb_substr($headerLastName, 0, 1, 'UTF-8'), 'UTF-8');
                    $headerShortName = Str::limit($headerLastName, 9, '');
                @endphp
                <div class="mayhen_client_header_user_menu" data-mayhen-component="user-menu">
                    <button type="button" class="mayhen_client_header_user_trigger" data-mayhen-action="toggle-user-menu" aria-expanded="false" aria-haspopup="true" aria-label="Menu tài khoản">
                        <span class="mayhen_client_header_user_avatar" aria-hidden="true">{{ $headerUserInitial }}</span>
                        <span class="mayhen_client_header_user_name">{{ $headerShortName }}</span>
                        <svg class="mayhen_client_header_user_chevron" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="mayhen_client_header_user_dropdown" data-mayhen-component="user-dropdown" role="menu">
                        <div class="mayhen_client_header_user_info">
                            <span class="mayhen_client_header_user_fullname">{{ $headerUserName }}</span>
                            <span class="mayhen_client_header_user_meta">{{ auth()->user()->email ?? auth()->user()->phone }}</span>
                            @if (auth()->user()->isAdmin())
                                <span class="mayhen_client_header_user_badge">Quản trị viên</span>
                            @endif
                        </div>
                        <div class="mayhen_client_header_user_divider"></div>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="mayhen_client_header_user_item" role="menuitem">
                                <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                    <rect x="9" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                    <rect x="2" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                    <rect x="9" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                </svg>
                                <span>Trang Quản Trị</span>
                            </a>
                        @endif
                        <a href="{{ route('account.dashboard') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                <rect x="9" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                <rect x="2" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                                <rect x="9" y="9" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                            <span>Bảng Điều Khiển</span>
                        </a>
                        <a href="{{ route('account.invitations') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 3.5h11c.55 0 1 .45 1 1v8c0 .55-.45 1-1 1h-11c-.55 0-1-.45-1-1v-8c0-.55.45-1 1-1z" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M14.5 4.5l-6.5 4.5-6.5-4.5" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                            <span>Thiệp Cưới Của Tôi</span>
                        </a>
                        <a href="{{ route('account.orders') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M2 6.5h12" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                            <span>Đơn Hàng & Gói Cước</span>
                        </a>
                        <a href="{{ route('account.profile') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.5 13.5v-1a2.5 2.5 0 0 0-2.5-2.5h-6A2.5 2.5 0 0 0 2.5 12.5v1" stroke="currentColor" stroke-width="1.3"/>
                                <circle cx="8" cy="5" r="2.5" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                            <span>Hồ Sơ & Mật Khẩu</span>
                        </a>
                        <a href="{{ route('docs.index') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 13.5A1.5 1.5 0 0 1 4 12h10" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M4 2.5h10v11H4a1.5 1.5 0 0 1-1.5-1.5v-8A1.5 1.5 0 0 1 4 2.5z" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                            <span>Trung Tâm Hướng Dẫn</span>
                        </a>
                        <div class="mayhen_client_header_user_divider"></div>
                        <a href="{{ route('invitation.create') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                            <span>Tạo Mẫu Thiệp Mới</span>
                        </a>
                        <div class="mayhen_client_header_user_divider"></div>
                        <form action="{{ route('logout') }}" method="POST" class="mayhen_client_header_user_logout_form">
                            @csrf
                            <button type="submit" class="mayhen_client_header_user_item mayhen_client_header_user_logout_btn" role="menuitem">
                                <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 14H3.33333C2.97971 14 2.64057 13.8595 2.39052 13.6095C2.14048 13.3594 2 13.0203 2 12.6667V3.33333C2 2.97971 2.14048 2.64057 2.39052 2.39052C2.64057 2.14048 2.97971 2 3.33333 2H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    <path d="M10.6667 11.3333L14 8L10.6667 4.66667" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                </svg>
                                <span>Đăng Xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="mayhen_client_header_login_btn">
                    Đăng Nhập
                </a>
            @endauth
            <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_sm mayhen_client_header_cta">
                Tạo Thiệp Ngay
            </a>
            
            <!-- Mobile Menu Toggle Button -->
            <button type="button" class="mayhen_client_header_toggle" data-mayhen-action="toggle-mobile-menu" aria-label="Mở menu di động" aria-expanded="false">
                <span class="mayhen_client_header_toggle_bar"></span>
                <span class="mayhen_client_header_toggle_bar"></span>
                <span class="mayhen_client_header_toggle_bar"></span>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Menu (Toàn màn hình, nền đục Quiet Luxury) -->
    <div class="mayhen_client_header_drawer" data-mayhen-component="mobile-drawer" role="navigation" aria-label="Menu di động">
        <a href="{{ route('templates.index') }}" class="mayhen_client_header_drawer_link">
            <span>Bộ Sưu Tập Mẫu Thiệp</span>
            <span style="font-size: 0.875rem; color: var(--mayhen-color-primary);">&rarr;</span>
        </a>
        <a href="{{ route('services') }}" class="mayhen_client_header_drawer_link">
            <span>Dịch Vụ & Tiện Ích</span>
            <span style="font-size: 0.875rem; color: var(--mayhen-color-primary);">&rarr;</span>
        </a>
        <a href="{{ route('pricing') }}" class="mayhen_client_header_drawer_link">
            <span>Bảng Giá Gói Dịch Vụ</span>
            <span style="font-size: 0.875rem; color: var(--mayhen-color-primary);">&rarr;</span>
        </a>
        <a href="{{ route('blog.index') }}" class="mayhen_client_header_drawer_link">
            <span>Cẩm Nang &amp; Ý Tưởng Cưới</span>
            <span style="font-size: 0.875rem; color: var(--mayhen-color-primary);">&rarr;</span>
        </a>
        <a href="{{ route('how-it-works') }}" class="mayhen_client_header_drawer_link">
            <span>Hướng Dẫn Tạo Thiệp</span>
            <span style="font-size: 0.875rem; color: var(--mayhen-color-primary);">&rarr;</span>
        </a>
        <a href="{{ route('contact') }}" class="mayhen_client_header_drawer_link">
            <span>Liên Hệ & Hỗ Trợ</span>
            <span style="font-size: 0.875rem; color: var(--mayhen-color-primary);">&rarr;</span>
        </a>

        @auth
            <a href="{{ route('account.dashboard') }}" class="mayhen_client_header_drawer_link" style="background: rgba(197, 168, 128, 0.12); font-weight: 600; color: #1F1D1A;">
                <span>Bảng Điều Khiển Cặp Đôi</span>
                <span style="font-size: 0.875rem; color: #9C7A4A;">&rarr;</span>
            </a>
            <div class="mayhen_client_header_drawer_user_card">
                <div class="mayhen_client_header_drawer_user_info">
                    <span class="mayhen_client_header_user_avatar">{{ $headerUserInitial }}</span>
                    <div class="mayhen_client_header_drawer_user_meta">
                        <span class="mayhen_client_header_drawer_user_name">{{ $headerUserName }}</span>
                        <span class="mayhen_client_header_drawer_user_sub">{{ auth()->user()->email ?? auth()->user()->phone }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mayhen_client_header_drawer_user_form">
                    @csrf
                    <button type="submit" class="mayhen_client_header_drawer_user_logout_btn" aria-label="Đăng xuất">
                        <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="14" height="14">
                            <path d="M6 14H3.33333C2.97971 14 2.64057 13.8595 2.39052 13.6095C2.14048 13.3594 2 13.0203 2 12.6667V3.33333C2 2.97971 2.14048 2.64057 2.39052 2.39052C2.64057 2.14048 2.97971 2 3.33333 2H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            <path d="M10.6667 11.3333L14 8L10.6667 4.66667" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                        </svg>
                        <span>Thoát</span>
                    </button>
                </form>
            </div>
        @else
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                <a href="{{ route('login') }}" class="mayhen_client_button_base mayhen_client_button_secondary" style="width: 100%; justify-content: center; height: 44px; font-size: 0.875rem;">
                    Đăng Nhập
                </a>
                <a href="{{ route('register') }}" class="mayhen_client_button_base mayhen_client_button_outline" style="width: 100%; justify-content: center; height: 44px; font-size: 0.875rem;">
                    Đăng Ký
                </a>
            </div>
        @endauth

        <div style="margin-top: 12px;">
            <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary" style="width: 100%; justify-content: center; height: 48px; font-size: 0.9375rem; border-radius: var(--mayhen-radius-pill);">
                Tạo Thiệp Cưới Miễn Phí
            </a>
        </div>
    </div>
</header>
