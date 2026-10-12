<footer class="mayhen_client_footer_wrapper">
    <div class="mayhen_client_layout_container">
        <div class="mayhen_client_footer_grid">
            <!-- Brand Column -->
            <div class="mayhen_client_footer_brand_col">
                <span class="mayhen_client_footer_brand_title">Mây Hẹn</span>
                <p class="mayhen_client_footer_brand_desc">
                    Nền tảng tạo thiệp cưới online cao cấp, mang phong cách Quiet Luxury và trải nghiệm cảm xúc trọn vẹn dành cho ngày trọng đại của bạn.
                </p>
                <div style="font-size: 0.875rem; color: #A8A29E;">
                    Hotline: 0988.xxx.xxx &bull; Email: lienhe@mayhen.vn
                </div>
            </div>

            <!-- Col 1: Mẫu Thiệp -->
            <div>
                <h3 class="mayhen_client_footer_nav_title">Mẫu Thiệp</h3>
                <ul class="mayhen_client_footer_nav_list">
                    <li><a href="{{ route('templates.index', ['category' => 'luxury']) }}" class="mayhen_client_footer_nav_link">Quiet Luxury</a></li>
                    <li><a href="{{ route('templates.index', ['category' => 'editorial']) }}" class="mayhen_client_footer_nav_link">Tạp Chí Editorial</a></li>
                    <li><a href="{{ route('templates.index', ['category' => 'envelope']) }}" class="mayhen_client_footer_nav_link">Phong Thư Hoàng Gia</a></li>
                    <li><a href="{{ route('templates.index', ['category' => 'minimal']) }}" class="mayhen_client_footer_nav_link">Tối Giản Tinh Tế</a></li>
                </ul>
            </div>

            <!-- Col 2: Dịch Vụ -->
            <div>
                <h3 class="mayhen_client_footer_nav_title">Sản Phẩm</h3>
                <ul class="mayhen_client_footer_nav_list">
                    <li><a href="{{ route('services') }}" class="mayhen_client_footer_nav_link">Thiệp Cưới Tự Tạo</a></li>
                    <li><a href="{{ route('pricing') }}" class="mayhen_client_footer_nav_link">Bảng Giá & Gói Cưới</a></li>
                    <li><a href="{{ route('how-it-works') }}" class="mayhen_client_footer_nav_link">Quy Trình Tạo Thiệp</a></li>
                    <li><a href="{{ route('faq') }}" class="mayhen_client_footer_nav_link">Câu Hỏi Thường Gặp</a></li>
                </ul>
            </div>

            <!-- Col 3: Hỗ Trợ -->
            <div>
                <h3 class="mayhen_client_footer_nav_title">Thông Tin</h3>
                <ul class="mayhen_client_footer_nav_list">
                    <li><a href="{{ route('about') }}" class="mayhen_client_footer_nav_link">Về Mây Hẹn</a></li>
                    <li><a href="{{ route('contact') }}" class="mayhen_client_footer_nav_link">Liên Hệ Studio</a></li>
                    <li><a href="{{ route('terms') }}" class="mayhen_client_footer_nav_link">Điều Khoản Dịch Vụ</a></li>
                    <li><a href="{{ route('privacy') }}" class="mayhen_client_footer_nav_link">Chính Sách Bảo Mật</a></li>
                </ul>
            </div>
        </div>

        <div class="mayhen_client_footer_bottom">
            <div>
                &copy; {{ date('Y') }} Mây Hẹn (mayhen.vn). Tất cả quyền được bảo lưu.
            </div>
            <div class="mayhen_client_footer_legal_links">
                <a href="{{ route('privacy') }}" class="mayhen_client_footer_nav_link">Bảo Vệ Dữ Liệu</a>
                <span>&bull;</span>
                <a href="{{ route('terms') }}" class="mayhen_client_footer_nav_link">Quy Định Sử Dụng</a>
            </div>
        </div>
    </div>
</footer>
