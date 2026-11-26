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

        <!-- Desktop Navigation (Tinh gọn trên 1 dòng: Mẫu Thiệp, Thiệp Sự Kiện, Gieo Quẻ, Tử Vi, Thần Số Học, Công Cụ, Bảng Giá) -->
        @php
            $isToolsMenuActive = request()->routeIs('family_tree.*', 'tarot.*', 'calendar.*', 'bazi.*', 'marriage.*', 'wedding.*');
        @endphp
        <nav class="mayhen_client_header_nav" aria-label="Điều hướng chính">
            <a href="{{ route('templates.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('templates.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Mẫu Thiệp Cưới</a>
            <a href="{{ route('event_invitations.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('event_invitations.*') ? 'mayhen_client_header_nav_link_active' : '' }}">
                <span>Thiệp Sự Kiện</span>
                <span class="mayhen_client_header_pro_tag">PRO</span>
            </a>
            <a href="{{ route('iching.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('iching.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Gieo Quẻ</a>
            <a href="{{ route('tuvi.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('tuvi.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Tử Vi</a>
            <a href="{{ route('numerology.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('numerology.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Thần Số Học</a>
            <div class="mayhen_client_header_nav_dropdown" data-mayhen-component="tools-menu">
                <button type="button" class="mayhen_client_header_nav_link mayhen_client_header_nav_dropdown_trigger {{ $isToolsMenuActive ? 'mayhen_client_header_nav_link_active' : '' }}" data-mayhen-action="toggle-tools-menu" aria-expanded="false" aria-haspopup="true">
                    <span>Công Cụ</span>
                    <svg class="mayhen_client_header_nav_chevron" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="mayhen_client_header_nav_dropdown_menu" role="menu" aria-label="Danh sách công cụ">
                    <a href="{{ route('family_tree.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('family_tree.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Dựng Gia Phả</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Cây gia phả dòng họ trực quan miễn phí</span>
                    </a>
                    <a href="{{ route('tarot.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('tarot.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Xem Tarot</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Trải bài 78 lá Rider–Waite–Smith 3D</span>
                    </a>
                    <a href="{{ route('calendar.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('calendar.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Xem Lịch Âm</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Vạn Niên Lịch UTC+7 &amp; ngày Hoàng Đạo</span>
                    </a>
                    <a href="{{ route('bazi.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('bazi.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Tra Cứu Bát Tự</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Lập lá số Tứ Trụ &amp; Dụng Thần chuẩn Tử Bình</span>
                    </a>
                    <a href="{{ route('marriage.caoly.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('marriage.caoly.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Cao Ly Đầu Hình</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Vòng Can Chi 120 tổ hợp &amp; Lục Cảnh</span>
                    </a>
                    <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('marriage.compatibility.*', 'marriage.book.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Xem Tuổi Vợ Chồng</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Hai Vòng Kết Duyên, Nạp Âm &amp; Bát Trạch</span>
                    </a>
                    <a href="{{ route('wedding.dates.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('wedding.dates.*') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Xem Ngày Cưới</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Chọn ngày lành, giờ đón dâu &amp; so sánh 3+ ngày</span>
                    </a>
                    <a href="{{ route('wedding.planner.index') }}" class="mayhen_client_header_nav_dropdown_item {{ request()->routeIs('wedding.planner.*', 'wedding.workspace.*', 'wedding.ceremonies') ? 'is-active' : '' }}" role="menuitem">
                        <span class="mayhen_client_header_nav_dropdown_title">Tổ Chức Đám Cưới</span>
                        <span class="mayhen_client_header_nav_dropdown_sub">Checklist, Ngân sách VND, Sơ đồ bàn 2D &amp; Kịch bản</span>
                    </a>
                </div>
            </div>
            <a href="{{ route('pricing') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('pricing') ? 'mayhen_client_header_nav_link_active' : '' }}">Bảng Giá</a>
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
                        <a href="{{ route('wedding.workspace.index') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 4.5h10M3 8h10M3 11.5h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                            <span>Hồ Sơ Tổ Chức Đám Cưới</span>
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
                        <a href="{{ route('tuvi.profiles') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <polygon points="8,1.5 9.8,6.2 14.5,8 9.8,9.8 8,14.5 6.2,9.8 1.5,8 6.2,6.2" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
                            </svg>
                            <span>Hồ Sơ Tử Vi (Thiên Mệnh)</span>
                        </a>
                        <a href="{{ route('numerology.profiles') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <polygon points="8,2 14,13 2,13" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
                                <circle cx="8" cy="9.2" r="1.4" stroke="currentColor" stroke-width="1.2"/>
                            </svg>
                            <span>Hồ Sơ Thần Số Học</span>
                        </a>
                        <a href="{{ route('tarot.journal.index') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3.5" y="1.8" width="9" height="12.4" rx="1.2" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M8 5.2L9.1 8L8 10.8L6.9 8L8 5.2Z" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/>
                            </svg>
                            <span>Sổ Bài Tarot</span>
                        </a>
                        <a href="{{ route('calendar.my_events') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="3" width="12" height="11" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M5 1.8V4.2M11 1.8V4.2M2 6.5H14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                            <span>Lịch Gia Đình (Âm Lịch)</span>
                        </a>
                        <a href="{{ route('bazi.profiles') }}" class="mayhen_client_header_user_item" role="menuitem">
                            <svg class="mayhen_client_header_user_item_icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="2.5" width="2.4" height="11" rx="0.6" stroke="currentColor" stroke-width="1.15"/>
                                <rect x="5.4" y="2.5" width="2.4" height="11" rx="0.6" stroke="currentColor" stroke-width="1.15"/>
                                <rect x="8.8" y="2.5" width="2.4" height="11" rx="0.6" stroke="currentColor" stroke-width="1.15"/>
                                <rect x="12.2" y="2.5" width="2.4" height="11" rx="0.6" stroke="currentColor" stroke-width="1.15"/>
                            </svg>
                            <span>Hồ Sơ Bát Tự (Tứ Trụ)</span>
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

    <!-- Mobile Drawer Menu (Toàn màn hình, tối giản đồng đều màu sắc, đầy đủ Công cụ & Hướng dẫn) -->
    <div class="mayhen_client_header_drawer" data-mayhen-component="mobile-drawer" role="navigation" aria-label="Menu di động">
        @php
            $drawerHotline = \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'hotline', '0988 888 888');
            $drawerHotlineClean = preg_replace('/[^0-9]/', '', $drawerHotline);
            $drawerZalo = \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'contact_zalo', 'https://zalo.me');
        @endphp

        {{-- 1. Tài Khoản / Đăng Nhập (gọn 1 khối phẳng đồng màu) --}}
        @auth
            <div class="mayhen_client_drawer_auth_card">
                <a href="{{ route('account.profile') }}" class="mayhen_client_drawer_auth_user" title="Xem hồ sơ tài khoản">
                    <span class="mayhen_client_drawer_avatar">{{ $headerUserInitial }}</span>
                    <div class="mayhen_client_drawer_user_details">
                        <div class="mayhen_client_drawer_user_name_row">
                            <span class="mayhen_client_drawer_user_fullname">{{ $headerUserName }}</span>
                            <span class="mayhen_client_drawer_user_badge">{{ auth()->user()->isAdmin() ? 'Admin' : 'Thành viên' }}</span>
                        </div>
                        <span class="mayhen_client_drawer_user_sub">{{ auth()->user()->email ?? auth()->user()->phone }}</span>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mayhen_client_header_user_logout_form">
                    @csrf
                    <button type="submit" class="mayhen_client_drawer_logout_btn" title="Đăng xuất" aria-label="Đăng xuất">
                        <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="14" height="14">
                            <path d="M6 14H3.33333C2.97971 14 2.64057 13.8595 2.39052 13.6095C2.14048 13.3594 2 13.0203 2 12.6667V3.33333C2 2.97971 2.14048 2.64057 2.39052 2.39052C2.64057 2.14048 2.97971 2 3.33333 2H6" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                            <path d="M10.6667 11.3333L14 8L10.6667 4.66667" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 8H6" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <div class="mayhen_client_drawer_guest_card">
                <span class="mayhen_client_drawer_guest_title">Chào mừng đến với {{ $headerBrandName }}</span>
                <div class="mayhen_client_drawer_guest_buttons">
                    <a href="{{ request()->is('dang-nhap*', 'dang-ky*') ? route('login') : route('login', ['redirect' => request()->fullUrl()]) }}" class="mayhen_client_drawer_guest_btn mayhen_client_drawer_guest_btn_login">Đăng Nhập</a>
                    <a href="{{ request()->is('dang-nhap*', 'dang-ky*') ? route('register') : route('register', ['redirect' => request()->fullUrl()]) }}" class="mayhen_client_drawer_guest_btn mayhen_client_drawer_guest_btn_register">Đăng Ký Miễn Phí</a>
                </div>
            </div>
        @endauth

        {{-- 2. Hành Động Chính (2 nút đồng bộ, phẳng, không màu chói) --}}
        <div class="mayhen_client_drawer_quick_actions">
            <a href="{{ route('templates.index') }}" class="mayhen_client_drawer_btn_primary">Tạo Thiệp Miễn Phí</a>
            <a href="{{ route('curated.create') }}" class="mayhen_client_drawer_btn_secondary">Tạo Thiệp Tự Động</a>
        </div>

        {{-- 3. Thiệp & Dịch Vụ --}}
        <div class="mayhen_client_drawer_section">
            <span class="mayhen_client_drawer_section_title">Thiệp &amp; Dịch Vụ</span>
            <div class="mayhen_client_drawer_grid_2">
                <a href="{{ route('templates.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('templates.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Mẫu Thiệp Cưới</span>
                    <span class="mayhen_client_drawer_cell_sub">50+ mẫu thiết kế độc bản</span>
                </a>
                <a href="{{ route('event_invitations.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('event_invitations.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Thiệp Sự Kiện</span>
                    <span class="mayhen_client_drawer_cell_sub">Đầy tháng, Tân gia, Lễ hội</span>
                </a>
                <a href="{{ route('services') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('services') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Dịch Vụ &amp; Tiện Ích</span>
                    <span class="mayhen_client_drawer_cell_sub">Sổ ký tên, QR, Nhạc nền</span>
                </a>
                <a href="{{ route('pricing') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('pricing') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Bảng Giá Dịch Vụ</span>
                    <span class="mayhen_client_drawer_cell_sub">Gói Miễn phí &amp; Trọn đời</span>
                </a>
            </div>
        </div>

        {{-- 4. Công Cụ Cưới & Văn Hóa (Đầy đủ toàn bộ công cụ) --}}
        <div class="mayhen_client_drawer_section">
            <span class="mayhen_client_drawer_section_title">Công Cụ Cưới &amp; Văn Hóa</span>
            <div class="mayhen_client_drawer_grid_2">
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('wedding.planner.*', 'wedding.workspace.*', 'wedding.ceremonies') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Tổ Chức Đám Cưới</span>
                    <span class="mayhen_client_drawer_cell_sub">Checklist, ngân sách, bàn tiệc</span>
                </a>
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('wedding.dates.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Xem Ngày Cưới</span>
                    <span class="mayhen_client_drawer_cell_sub">Chọn ngày lành, giờ đón dâu</span>
                </a>
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('marriage.compatibility.*', 'marriage.book.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Xem Tuổi Vợ Chồng</span>
                    <span class="mayhen_client_drawer_cell_sub">Nạp Âm &amp; Bát Trạch</span>
                </a>
                <a href="{{ route('marriage.caoly.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('marriage.caoly.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Cao Ly Đầu Hình</span>
                    <span class="mayhen_client_drawer_cell_sub">120 tổ hợp Can Chi</span>
                </a>
                <a href="{{ route('family_tree.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('family_tree.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Dựng Gia Phả</span>
                    <span class="mayhen_client_drawer_cell_sub">Cây gia phả dòng họ online</span>
                </a>
                <a href="{{ route('calendar.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('calendar.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Xem Lịch Âm</span>
                    <span class="mayhen_client_drawer_cell_sub">Vạn Niên Lịch &amp; Hoàng Đạo</span>
                </a>
                <a href="{{ route('iching.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('iching.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Gieo Quẻ Kinh Dịch</span>
                    <span class="mayhen_client_drawer_cell_sub">64 Quẻ &amp; Dịch Tâm</span>
                </a>
                <a href="{{ route('tuvi.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('tuvi.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Tử Vi Đẩu Số</span>
                    <span class="mayhen_client_drawer_cell_sub">Lá số 12 cung Nam Phái</span>
                </a>
                <a href="{{ route('numerology.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('numerology.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Thần Số Học</span>
                    <span class="mayhen_client_drawer_cell_sub">Đường Đời &amp; Tình Duyên</span>
                </a>
                <a href="{{ route('tarot.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('tarot.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Xem Tarot</span>
                    <span class="mayhen_client_drawer_cell_sub">Trải bài 78 lá 3D</span>
                </a>
                <a href="{{ route('bazi.index') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('bazi.*') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Tra Cứu Bát Tự</span>
                    <span class="mayhen_client_drawer_cell_sub">Tứ Trụ &amp; Dụng Thần</span>
                </a>
                <a href="{{ route('pricing.advisor') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('pricing.advisor') ? 'is-active' : '' }}">
                    <span class="mayhen_client_drawer_cell_title">Tư Vấn Chọn Gói</span>
                    <span class="mayhen_client_drawer_cell_sub">Đề xuất gói phù hợp</span>
                </a>
            </div>
        </div>

        {{-- 5. Không Gian Cá Nhân (hiển thị khi đã đăng nhập) --}}
        @auth
            <div class="mayhen_client_drawer_section">
                <span class="mayhen_client_drawer_section_title">Không Gian Của Tôi</span>
                <div class="mayhen_client_drawer_grid_2">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="mayhen_client_drawer_cell mayhen_client_drawer_cell_full">
                            <span class="mayhen_client_drawer_cell_title">Trang Quản Trị Hệ Thống</span>
                            <span class="mayhen_client_drawer_cell_sub">Admin Control Panel</span>
                        </a>
                    @endif
                    <a href="{{ route('account.invitations') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('account.invitations*') ? 'is-active' : '' }}">
                        <span class="mayhen_client_drawer_cell_title">Thiệp Của Tôi</span>
                        <span class="mayhen_client_drawer_cell_sub">Chỉnh sửa &amp; chia sẻ</span>
                    </a>
                    <a href="{{ route('account.dashboard') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('account.dashboard') ? 'is-active' : '' }}">
                        <span class="mayhen_client_drawer_cell_title">Khách Mời &amp; RSVP</span>
                        <span class="mayhen_client_drawer_cell_sub">Điểm danh &amp; lời chúc</span>
                    </a>
                    <a href="{{ route('wedding.workspace.index') }}" class="mayhen_client_drawer_cell">
                        <span class="mayhen_client_drawer_cell_title">Hồ Sơ Đám Cưới</span>
                        <span class="mayhen_client_drawer_cell_sub">Tiến độ &amp; ngân sách</span>
                    </a>
                    <a href="{{ route('account.orders') }}" class="mayhen_client_drawer_cell {{ request()->routeIs('account.orders*') ? 'is-active' : '' }}">
                        <span class="mayhen_client_drawer_cell_title">Đơn Hàng &amp; Gói</span>
                        <span class="mayhen_client_drawer_cell_sub">Lịch sử thanh toán</span>
                    </a>
                </div>
            </div>
        @endauth

        {{-- 6. Hướng Dẫn, Cẩm Nang & Điều Hướng --}}
        <div class="mayhen_client_drawer_section">
            <span class="mayhen_client_drawer_section_title">Hướng Dẫn &amp; Thông Tin</span>
            <div class="mayhen_client_drawer_list">
                <a href="{{ route('docs.index') }}" class="mayhen_client_drawer_list_item {{ request()->routeIs('docs.*') ? 'is-active' : '' }}">
                    <div class="mayhen_client_drawer_list_text">
                        <span class="mayhen_client_drawer_list_title">Trung Tâm Hướng Dẫn (Docs)</span>
                        <span class="mayhen_client_drawer_list_sub">Quy trình tự tạo thiệp chi tiết từng bước</span>
                    </div>
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <a href="{{ route('blog.index') }}" class="mayhen_client_drawer_list_item {{ request()->routeIs('blog.*') ? 'is-active' : '' }}">
                    <div class="mayhen_client_drawer_list_text">
                        <span class="mayhen_client_drawer_list_title">Cẩm Nang &amp; Ý Tưởng Cưới</span>
                        <span class="mayhen_client_drawer_list_sub">Kinh nghiệm chuẩn bị lễ cưới tinh tế</span>
                    </div>
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <a href="{{ route('faq') }}" class="mayhen_client_drawer_list_item {{ request()->routeIs('faq') ? 'is-active' : '' }}">
                    <div class="mayhen_client_drawer_list_text">
                        <span class="mayhen_client_drawer_list_title">Câu Hỏi Thường Gặp (FAQ)</span>
                        <span class="mayhen_client_drawer_list_sub">Giải đáp về gói cước, gửi thiệp, bảo mật</span>
                    </div>
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <a href="{{ route('news.index') }}" class="mayhen_client_drawer_list_item {{ request()->routeIs('news.*') ? 'is-active' : '' }}">
                    <div class="mayhen_client_drawer_list_text">
                        <span class="mayhen_client_drawer_list_title">Tin Tức &amp; Cập Nhật</span>
                        <span class="mayhen_client_drawer_list_sub">Mẫu thiệp mới &amp; tính năng vừa ra mắt</span>
                    </div>
                    <svg class="mayhen_client_drawer_card_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <div class="mayhen_client_drawer_inline_links">
                    <a href="{{ route('about') }}" class="mayhen_client_drawer_inline_link">Về {{ $headerBrandName }}</a>
                    <a href="{{ route('contact') }}" class="mayhen_client_drawer_inline_link">Liên Hệ Tư Vấn</a>
                </div>
            </div>
        </div>

        {{-- 7. Hỗ Trợ Nhanh (tối giản đồng màu) --}}
        <div class="mayhen_client_drawer_concierge">
            <div class="mayhen_client_drawer_concierge_head">
                <span class="mayhen_client_drawer_concierge_label">Hỗ trợ trực tiếp</span>
                <span class="mayhen_client_drawer_concierge_time">08:00 – 22:00</span>
            </div>
            <div class="mayhen_client_drawer_concierge_buttons">
                <a href="tel:{{ $drawerHotlineClean }}" class="mayhen_client_drawer_concierge_btn">Gọi Hotline</a>
                <a href="{{ $drawerZalo }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_drawer_concierge_btn">Chat Zalo</a>
            </div>
        </div>

        <div class="mayhen_client_drawer_footer">
            <span class="mayhen_client_drawer_footer_text">{{ $headerBrandName }} &bull; Thiệp cưới &amp; tiện ích văn hóa Việt</span>
        </div>
    </div>
</header>
