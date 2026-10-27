<!-- May Hen Bottom Navigation Dock (SVG Icon kèm Nhãn Text, Nút Tạo Thiệp Nổi Bật) -->
<nav class="mayhen_client_bottom_nav_wrapper" aria-label="Điều hướng nhanh chân trang">
    <div class="mayhen_client_bottom_nav_container">
        <!-- 1. Trang Chủ -->
        <a href="{{ route('home') }}" 
           class="mayhen_client_bottom_nav_item {{ request()->routeIs('home') ? 'mayhen_client_bottom_nav_item_active' : '' }}" 
           title="Trang chủ" 
           aria-label="Trang chủ">
            <span class="mayhen_client_bottom_nav_icon">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 10.5L12 3l9 7.5V20a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 20v-9.5z"/>
                    <path d="M9 21v-7a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v7"/>
                </svg>
            </span>
            <span class="mayhen_client_bottom_nav_label">Trang chủ</span>
        </a>

        <!-- 2. Mẫu Thiệp -->
        <a href="{{ route('templates.index') }}" 
           class="mayhen_client_bottom_nav_item {{ request()->routeIs('templates.*') ? 'mayhen_client_bottom_nav_item_active' : '' }}" 
           title="Mẫu thiệp cưới" 
           aria-label="Mẫu thiệp">
            <span class="mayhen_client_bottom_nav_icon">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                    <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                </svg>
            </span>
            <span class="mayhen_client_bottom_nav_label">Mẫu thiệp</span>
        </a>

        <!-- 3. Tạo Thiệp (Nổi bật ở giữa với nút tròn và nhãn text) -->
        <a href="{{ (isset($template) && !empty($template->slug)) ? route('invitation.create', ['template' => $template->slug]) : route('invitation.create') }}" 
           class="mayhen_client_bottom_nav_item mayhen_client_bottom_nav_item_center mayhen_client_bottom_nav_item_create {{ request()->routeIs('invitation.create') ? 'mayhen_client_bottom_nav_item_active' : '' }}" 
           title="Tạo thiệp cưới ngay" 
           aria-label="Tạo thiệp">
            <span class="mayhen_client_bottom_nav_create_btn">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m18 2 4 4-14 14H4v-4L18 2z"/>
                    <path d="m14.5 5.5 4 4"/>
                    <path d="M8.5 2.5 10 4"/>
                    <path d="M2.5 8.5 4 10"/>
                </svg>
            </span>
            <span class="mayhen_client_bottom_nav_label mayhen_client_bottom_nav_label_create">Tạo thiệp</span>
        </a>

        <!-- 4. Yêu Thích -->
        <a href="{{ route('templates.index') }}#yeu-thich" 
           class="mayhen_client_bottom_nav_item" 
           title="Mẫu thiệp yêu thích" 
           aria-label="Yêu thích">
            <span class="mayhen_client_bottom_nav_icon">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </span>
            <span class="mayhen_client_bottom_nav_label">Yêu thích</span>
        </a>

        <!-- 5. Trợ Lý AI -->
        <a href="{{ route('contact') }}#tro-ly-ai" 
           class="mayhen_client_bottom_nav_item {{ request()->routeIs('contact') ? 'mayhen_client_bottom_nav_item_active' : '' }}" 
           title="Trợ lý AI Mây Hẹn" 
           aria-label="Trợ lý AI">
            <span class="mayhen_client_bottom_nav_icon">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="10" rx="4"/>
                    <circle cx="12" cy="5" r="2"/>
                    <path d="M12 7v4"/>
                    <line x1="8" y1="15" x2="8.01" y2="15" stroke-width="2.5"/>
                    <line x1="16" y1="15" x2="16.01" y2="15" stroke-width="2.5"/>
                    <path d="M9 18h6"/>
                    <path d="M2 15h1"/>
                    <path d="M21 15h1"/>
                </svg>
            </span>
            <span class="mayhen_client_bottom_nav_label">Trợ lý AI</span>
        </a>
    </div>
</nav>
