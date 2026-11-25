<header class="mayhen_client_header_wrapper">
    <div class="mayhen_client_layout_container mayhen_client_header_container">
        @php
            $headerBrandName = \App\Models\PlatformSetting::getBrandName();
            $headerBrandLogo = \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'brand_logo', '/storage/images/client/business/mayhen-logo.png');
        @endphp
        <!-- Logo -->
        <a href="{{ route('home') }}" class="mayhen_client_header_brand" aria-label="{{ $headerBrandName }} - Thiệp Cưới Online">
            <img src="{{ asset($headerBrandLogo) }}" 
                 alt="{{ $headerBrandName }} - Thiệp Cưới Online" 
                 class="mayhen_client_header_brand_logo" 
                 width="230" 
                 height="55" 
                 loading="eager">
        </a>

        <!-- Desktop Navigation (Tinh gọn 6 mục cốt lõi, luôn nằm trên 1 dòng) -->
        <nav class="mayhen_client_header_nav" aria-label="Điều hướng chính">
            <a href="{{ route('templates.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('templates.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Mẫu Thiệp Cưới</a>
            <a href="{{ route('event_invitations.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('event_invitations.*') ? 'mayhen_client_header_nav_link_active' : '' }}">
                <span>Thiệp Sự Kiện</span>
                <span class="mayhen_client_header_pro_tag">PRO</span>
            </a>
            <a href="{{ route('family_tree.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('family_tree.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Gia Phả</a>
            <a href="{{ route('iching.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('iching.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Gieo Quẻ</a>
            <a href="{{ route('pricing') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('pricing') ? 'mayhen_client_header_nav_link_active' : '' }}">Bảng Giá</a>
            <a href="{{ route('blog.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('blog.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Cẩm Nang</a>
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
                        <a href="{{ route('family_tree.my_trees') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 2v4M8 6H4v4M8 6h4v4M4 10v4M12 10v4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                            <span>Gia Phả Của Tôi</span>
                        </a>
                        <a href="{{ route('iching.history') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M8 2.5a2.75 2.75 0 0 1 0 5.5 2.75 2.75 0 0 0 0 5.5" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                            <span>Sổ Quẻ Kinh Dịch</span>
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
                <a href="{{ request()->is('dang-nhap*', 'dang-ky*') ? route('login') : route('login', ['redirect' => request()->fullUrl()]) }}" class="mayhen_client_header_login_btn">
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
        @php
            $drawerHotline = \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'hotline', '0988 888 888');
            $drawerHotlineClean = preg_replace('/[^0-9]/', '', $drawerHotline);
            $drawerZalo = \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'contact_zalo', 'https://zalo.me');
        @endphp

        {{-- 1. Khối Tài Khoản / Chào Mừng --}}
        @auth
            <div class="mayhen_client_drawer_auth_card">
                <a href="{{ route('account.profile') }}" class="mayhen_client_drawer_auth_user" title="Xem hồ sơ tài khoản">
                    <span class="mayhen_client_drawer_avatar">{{ $headerUserInitial }}</span>
                    <div class="mayhen_client_drawer_user_details">
                        <div class="mayhen_client_drawer_user_name_row">
                            <span class="mayhen_client_drawer_user_fullname">{{ $headerUserName }}</span>
                            @if (auth()->user()->isAdmin())
                                <span class="mayhen_client_drawer_user_badge">Admin</span>
                            @else
                                <span class="mayhen_client_drawer_user_badge">Cặp Đôi</span>
                            @endif
                        </div>
                        <span class="mayhen_client_drawer_user_sub">{{ auth()->user()->email ?? auth()->user()->phone }}</span>
                    </div>
                </a>
                <div class="mayhen_client_drawer_auth_actions">
                    <form action="{{ route('logout') }}" method="POST" class="mayhen_client_header_user_logout_form">
                        @csrf
                        <button type="submit" class="mayhen_client_drawer_logout_btn" title="Đăng xuất khỏi tài khoản" aria-label="Đăng xuất">
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="15" height="15">
                                <path d="M6 14H3.33333C2.97971 14 2.64057 13.8595 2.39052 13.6095C2.14048 13.3594 2 13.0203 2 12.6667V3.33333C2 2.97971 2.14048 2.64057 2.39052 2.39052C2.64057 2.14048 2.97971 2 3.33333 2H6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                <path d="M10.6667 11.3333L14 8L10.6667 4.66667" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 8H6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="mayhen_client_drawer_guest_card">
                <div class="mayhen_client_drawer_guest_head">
                    <svg class="mayhen_client_drawer_guest_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    </svg>
                    <span class="mayhen_client_drawer_guest_title">Chào mừng bạn đến với {{ $headerBrandName }}</span>
                </div>
                <p class="mayhen_client_drawer_guest_desc">Đăng nhập để lưu giữ kỷ niệm, quản lý khách mời và chỉnh sửa thiệp cưới trọn đời.</p>
                <div class="mayhen_client_drawer_guest_buttons">
                    <a href="{{ request()->is('dang-nhap*', 'dang-ky*') ? route('login') : route('login', ['redirect' => request()->fullUrl()]) }}" class="mayhen_client_drawer_guest_btn mayhen_client_drawer_guest_btn_login">
                        Đăng Nhập
                    </a>
                    <a href="{{ request()->is('dang-nhap*', 'dang-ky*') ? route('register') : route('register', ['redirect' => request()->fullUrl()]) }}" class="mayhen_client_drawer_guest_btn mayhen_client_drawer_guest_btn_register">
                        Đăng Ký Miễn Phí
                    </a>
                </div>
            </div>
        @endauth

        {{-- 2. Nút Hành Động Trọng Tâm (Primary Hero CTA) --}}
        <a href="{{ route('templates.index') }}" class="mayhen_client_drawer_cta_hero">
            <div class="mayhen_client_drawer_cta_left">
                <div class="mayhen_client_drawer_cta_icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </div>
                <div class="mayhen_client_drawer_cta_text">
                    <span class="mayhen_client_drawer_cta_title">Tạo Thiệp Cưới Miễn Phí</span>
                    <span class="mayhen_client_drawer_cta_sub">✨ Không cần thẻ &bull; Trải nghiệm ngay</span>
                </div>
            </div>
            <div class="mayhen_client_drawer_cta_arrow">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </div>
        </a>

        {{-- 3. Nhóm: Khám Phá Bộ Sưu Tập Thiệp Cưới --}}
        <div class="mayhen_client_drawer_section">
            <span class="mayhen_client_drawer_section_title">Bộ Sưu Tập Mẫu Thiệp</span>
            <a href="{{ route('templates.index') }}" class="mayhen_client_drawer_card_item">
                <div class="mayhen_client_drawer_card_item_left">
                    <div class="mayhen_client_drawer_card_icon_wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_drawer_card_item_info">
                        <span class="mayhen_client_drawer_card_item_title">Khám Phá Toàn Bộ Mẫu Thiệp</span>
                        <span class="mayhen_client_drawer_card_item_sub">Hơn 50+ mẫu thiệp thiết kế độc bản</span>
                    </div>
                </div>
                <div class="mayhen_client_drawer_card_item_right">
                    <span class="mayhen_client_drawer_badge_hot">HOT</span>
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>
            </a>

            {{-- Thanh cuộn nhanh phong cách thịnh hành --}}
            <div class="mayhen_client_drawer_pills_scroll">
                <a href="{{ route('templates.index', ['category' => 'hoang-gia-tem-sap']) }}" class="mayhen_client_drawer_pill">
                    <span>👑 Hoàng Gia Tem Sáp</span>
                </a>
                <a href="{{ route('templates.index', ['category' => 'han-quoc-toi-gian']) }}" class="mayhen_client_drawer_pill">
                    <span>🌿 Hàn Quốc Tối Giản</span>
                </a>
                <a href="{{ route('templates.index', ['category' => 'hien-dai-tre-trung']) }}" class="mayhen_client_drawer_pill">
                    <span>🌸 Hiện Đại Trẻ Trung</span>
                </a>
                <a href="{{ route('templates.index', ['category' => 'truyen-thong-a-dong']) }}" class="mayhen_client_drawer_pill">
                    <span>🏮 Truyền Thống Á Đông</span>
                </a>
                <a href="{{ route('event_invitations.index') }}" class="mayhen_client_drawer_pill">
                    <span>🎁 Thiệp Đầy Tháng, Tân Gia &amp; Lễ Hội (PRO)</span>
                </a>
                <a href="{{ route('family_tree.index') }}" class="mayhen_client_drawer_pill">
                    <span>🌳 Gia Phả (Miễn Phí)</span>
                </a>
                <a href="{{ route('iching.index') }}" class="mayhen_client_drawer_pill">
                    <span>☯ Gieo Quẻ Kinh Dịch &amp; Đầu Năm</span>
                </a>
            </div>
        </div>

        {{-- 4. Nhóm: Tiện Ích Cặp Đôi (Studio & Quản Lý) --}}
        <div class="mayhen_client_drawer_section">
            <span class="mayhen_client_drawer_section_title">Tiện Ích Cho Cặp Đôi</span>
            <div class="mayhen_client_drawer_tools_grid">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="mayhen_client_drawer_admin_tile">
                            <div class="mayhen_client_drawer_admin_left">
                                <div class="mayhen_client_drawer_admin_icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z"/>
                                    </svg>
                                </div>
                                <div class="mayhen_client_drawer_admin_text">
                                    <span class="mayhen_client_drawer_admin_title">Trang Quản Trị Hệ Thống</span>
                                    <span class="mayhen_client_drawer_admin_sub">Admin Control Panel</span>
                                </div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @endif

                    <a href="{{ route('account.invitations') }}" class="mayhen_client_drawer_tool_tile">
                        <div class="mayhen_client_drawer_tool_head">
                            <div class="mayhen_client_drawer_tool_icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8v13H3V8"/>
                                    <path d="M1 3h22v5H1z"/>
                                    <path d="M10 12h4"/>
                                </svg>
                            </div>
                            <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                        <span class="mayhen_client_drawer_tool_title">Thiệp Của Tôi</span>
                        <span class="mayhen_client_drawer_tool_sub">Chỉnh sửa thiệp đã tạo</span>
                    </a>

                    <a href="{{ route('account.dashboard') }}" class="mayhen_client_drawer_tool_tile">
                        <div class="mayhen_client_drawer_tool_head">
                            <div class="mayhen_client_drawer_tool_icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                        <span class="mayhen_client_drawer_tool_title">Khách Mời & RSVP</span>
                        <span class="mayhen_client_drawer_tool_sub">Điểm danh & lời chúc</span>
                    </a>
                @endauth

                <a href="{{ route('services') }}" class="mayhen_client_drawer_tool_tile">
                    <div class="mayhen_client_drawer_tool_head">
                        <div class="mayhen_client_drawer_tool_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        </div>
                        <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </div>
                    <span class="mayhen_client_drawer_tool_title">Dịch Vụ & Tiện Ích</span>
                    <span class="mayhen_client_drawer_tool_sub">Sổ ký tên, QR, Nhạc</span>
                </a>

                <a href="{{ route('pricing') }}" class="mayhen_client_drawer_tool_tile">
                    <div class="mayhen_client_drawer_tool_head">
                        <div class="mayhen_client_drawer_tool_icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 3h12l4 6-10 12L2 9z"/>
                                <path d="M11 3 8 9l4 12 4-12-3-6"/>
                                <path d="M2 9h20"/>
                            </svg>
                        </div>
                        <span class="mayhen_client_drawer_badge_free">TIẾT KIỆM</span>
                    </div>
                    <span class="mayhen_client_drawer_tool_title">Bảng Giá Dịch Vụ</span>
                    <span class="mayhen_client_drawer_tool_sub">Gói Miễn Phí & Trọn Đời</span>
                </a>
            </div>
        </div>

        {{-- 5. Nhóm: Cẩm Nang & Hướng Dẫn --}}
        <div class="mayhen_client_drawer_section">
            <span class="mayhen_client_drawer_section_title">Cẩm Nang & Tài Liệu</span>
            <a href="{{ route('blog.index') }}" class="mayhen_client_drawer_card_item">
                <div class="mayhen_client_drawer_card_item_left">
                    <div class="mayhen_client_drawer_card_icon_wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                            <path d="M6 6h10"/>
                            <path d="M6 10h10"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_drawer_card_item_info">
                        <span class="mayhen_client_drawer_card_item_title">Cẩm Nang & Ý Tưởng Cưới</span>
                        <span class="mayhen_client_drawer_card_item_sub">Kinh nghiệm chuẩn bị & văn hóa hôn lễ</span>
                    </div>
                </div>
                <div class="mayhen_client_drawer_card_item_right">
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('docs.index') }}" class="mayhen_client_drawer_card_item">
                <div class="mayhen_client_drawer_card_item_left">
                    <div class="mayhen_client_drawer_card_icon_wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_drawer_card_item_info">
                        <span class="mayhen_client_drawer_card_item_title">Hướng Dẫn & Tài Liệu (SOP)</span>
                        <span class="mayhen_client_drawer_card_item_sub">Quy trình tự tạo thiệp trong 5 phút</span>
                    </div>
                </div>
                <div class="mayhen_client_drawer_card_item_right">
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>
            </a>
        </div>

        {{-- 6. Khối Trợ Giúp Cặp Đôi Nhanh (Direct Concierge Widget) --}}
        <div class="mayhen_client_drawer_concierge">
            <div class="mayhen_client_drawer_concierge_head">
                <span class="mayhen_client_drawer_concierge_dot"></span>
                <span class="mayhen_client_drawer_concierge_label">Hỗ Trợ Cặp Đôi Trực Tiếp</span>
                <span class="mayhen_client_drawer_concierge_time">8:00 - 22:00</span>
            </div>
            <div class="mayhen_client_drawer_concierge_buttons">
                <a href="tel:{{ $drawerHotlineClean }}" class="mayhen_client_drawer_concierge_btn mayhen_client_drawer_concierge_call">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Gọi Hotline</span>
                </a>
                <a href="{{ $drawerZalo }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_drawer_concierge_btn mayhen_client_drawer_concierge_zalo">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span>Chat Zalo</span>
                </a>
            </div>
        </div>

        {{-- 7. Footer Thương Hiệu --}}
        <div class="mayhen_client_drawer_footer">
            <span class="mayhen_client_drawer_footer_text">
                {{ $headerBrandName }} &bull; Nền tảng thiệp cưới trực tuyến Quiet Luxury
            </span>
        </div>
    </div>
</header>
