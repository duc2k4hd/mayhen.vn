<header class="mayhen_client_header_wrapper">
    <div class="mayhen_client_layout_container mayhen_client_header_container">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="mayhen_client_header_brand" aria-label="Mây Hẹn - Thiệp Cưới Online">
            <svg class="mayhen_client_header_logo_icon" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 3C16 3 13 8 9 10C5 12 3 16 3 18C3 24 9 28 16 29C23 28 29 24 29 18C29 16 27 12 23 10C19 8 16 3 16 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 11V23M11 16H21" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
            <span class="mayhen_client_header_brand_name">Mây Hẹn</span>
            <span class="mayhen_client_header_brand_tagline">Thiệp Cưới Online</span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="mayhen_client_header_nav" aria-label="Điều hướng chính">
            <a href="{{ route('templates.index') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('templates.*') ? 'mayhen_client_header_nav_link_active' : '' }}">Mẫu Thiệp</a>
            <a href="{{ route('services') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('services') ? 'mayhen_client_header_nav_link_active' : '' }}">Dịch Vụ</a>
            <a href="{{ route('pricing') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('pricing') ? 'mayhen_client_header_nav_link_active' : '' }}">Bảng Giá</a>
            <a href="{{ route('how-it-works') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('how-it-works') ? 'mayhen_client_header_nav_link_active' : '' }}">Hướng Dẫn</a>
            <a href="{{ route('contact') }}" class="mayhen_client_header_nav_link {{ request()->routeIs('contact') ? 'mayhen_client_header_nav_link_active' : '' }}">Liên Hệ</a>
        </nav>

        <!-- Header Actions -->
        <div class="mayhen_client_header_actions">
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

    <!-- Mobile Drawer Menu -->
    <div class="mayhen_client_header_drawer" data-mayhen-component="mobile-drawer" role="navigation" aria-label="Menu di động">
        <a href="{{ route('templates.index') }}" class="mayhen_client_header_drawer_link">Bộ Sưu Tập Mẫu Thiệp</a>
        <a href="{{ route('services') }}" class="mayhen_client_header_drawer_link">Dịch Vụ & Tiện Ích</a>
        <a href="{{ route('pricing') }}" class="mayhen_client_header_drawer_link">Bảng Giá Gói Dịch Vụ</a>
        <a href="{{ route('how-it-works') }}" class="mayhen_client_header_drawer_link">Hướng Dẫn Tạo Thiệp</a>
        <a href="{{ route('contact') }}" class="mayhen_client_header_drawer_link">Liên Hệ & Hỗ Trợ</a>
        <div style="margin-top: 1rem;">
            <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary" style="width: 100%;">
                Tạo Thiệp Cưới Miễn Phí
            </a>
        </div>
    </div>
</header>
