<footer class="mayhen_client_footer_wrapper">
    <!-- 1. Thanh Cam Kết Giá Trị (Value Proposition Bar) -->
    <div class="mayhen_client_footer_value_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_value_grid">
                <div class="mayhen_client_footer_value_item">
                    <div class="mayhen_client_footer_value_icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3h12l4 6-10 12L2 9l4-6z"/>
                            <path d="M2 9h20"/>
                            <path d="m10 3 2 6 2-6"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_footer_value_content">
                        <h4 class="mayhen_client_footer_value_title">Chuẩn Quiet Luxury</h4>
                        <p class="mayhen_client_footer_value_desc">Thiết kế tối giản, tinh tế và trân quý cảm xúc</p>
                    </div>
                </div>

                <div class="mayhen_client_footer_value_item">
                    <div class="mayhen_client_footer_value_icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_footer_value_content">
                        <h4 class="mayhen_client_footer_value_title">Khởi Tạo Trong 5 Phút</h4>
                        <p class="mayhen_client_footer_value_desc">Tự chỉnh sửa, xem trước tức thì trên di động</p>
                    </div>
                </div>

                <div class="mayhen_client_footer_value_item">
                    <div class="mayhen_client_footer_value_icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_footer_value_content">
                        <h4 class="mayhen_client_footer_value_title">Cá Nhân Hóa Khách Mời</h4>
                        <p class="mayhen_client_footer_value_desc">Tên riêng từng khách, gửi link Zalo 1 chạm</p>
                    </div>
                </div>

                <div class="mayhen_client_footer_value_item">
                    <div class="mayhen_client_footer_value_icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <div class="mayhen_client_footer_value_content">
                        <h4 class="mayhen_client_footer_value_title">Bảo Mật &amp; Riêng Tư</h4>
                        <p class="mayhen_client_footer_value_desc">Mã hóa an toàn, tùy chọn đặt mật khẩu thiệp</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Khối Đăng Ký Nhận Bản Tin & Cổng Thanh Toán -->
    <div class="mayhen_client_footer_newsletter_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_newsletter_grid">
                <div class="mayhen_client_footer_newsletter_left">
                    <div class="mayhen_client_footer_newsletter_badge">ƯU ĐÃI NGÀY CƯỚI</div>
                    <h3 class="mayhen_client_footer_newsletter_title">Đăng Ký Nhận Cẩm Nang &amp; Voucher 10%</h3>
                    <p class="mayhen_client_footer_newsletter_sub">Nhận gợi ý ý tưởng thiệp cưới độc quyền và ưu đãi sớm nhất từ {{ config('app.name') }}.</p>
                    <form class="mayhen_client_footer_newsletter_form" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký! {{ config('app.name') }} sẽ gửi cẩm nang đến email của bạn.');">
                        <div class="mayhen_client_footer_input_wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <input type="email" placeholder="Nhập địa chỉ email của bạn..." required class="mayhen_client_footer_input">
                        </div>
                        <button type="submit" class="mayhen_client_footer_submit_btn">
                            <span>Đăng Ký Ngay</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </form>
                </div>

                <div class="mayhen_client_footer_payments_right">
                    <span class="mayhen_client_footer_payments_label">Phương thức thanh toán an toàn</span>
                    <div class="mayhen_client_footer_payments_badges">
                        <span class="mayhen_client_footer_pay_pill" title="Chuyển khoản VietQR nhanh">VietQR</span>
                        <span class="mayhen_client_footer_pay_pill" title="Cổng thanh toán VNPAY">VNPAY-QR</span>
                        <span class="mayhen_client_footer_pay_pill" title="Ví MoMo">MoMo</span>
                        <span class="mayhen_client_footer_pay_pill" title="Ví ZaloPay">ZaloPay</span>
                        <span class="mayhen_client_footer_pay_pill" title="Apple Pay">Apple Pay</span>
                        <span class="mayhen_client_footer_pay_pill" title="Visa Card">VISA</span>
                        <span class="mayhen_client_footer_pay_pill" title="Mastercard">Mastercard</span>
                        <span class="mayhen_client_footer_pay_pill" title="Thẻ ATM Napas">Napas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Lưới Thông Tin & Điều Hướng 5 Cột Chính -->
    <div class="mayhen_client_footer_main_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_grid_5col">
                <!-- Cột 1: Thông tin thương hiệu & Hotline -->
                <div class="mayhen_client_footer_col mayhen_client_footer_col_brand">
                    <a href="{{ route('home') }}" class="mayhen_client_footer_brand_logo" aria-label="Trang chủ {{ config('app.name') }}">
                        <span class="mayhen_client_footer_brand_monogram">M</span>
                        <span class="mayhen_client_footer_brand_text">{{ config('app.name') }}</span>
                    </a>
                    <p class="mayhen_client_footer_brand_desc">
                        Nền tảng thiệp cưới online cao cấp hàng đầu Việt Nam. Nơi từng khuôn hình, nét chữ và giai điệu hòa quyện thành bản giao hưởng tình yêu bất tận.
                    </p>

                    <div class="mayhen_client_footer_contact_box">
                        <div class="mayhen_client_footer_hotline_card">
                            <div class="mayhen_client_footer_hotline_icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <span class="mayhen_client_footer_hotline_sub">Tư vấn tạo thiệp (8:00 - 22:00)</span>
                                <a href="tel:0988000000" class="mayhen_client_footer_hotline_num">0988.xxx.xxx</a>
                            </div>
                        </div>

                        <div class="mayhen_client_footer_contact_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <span>Email: <a href="mailto:lienhe@mayhen.vn">lienhe@mayhen.vn</a></span>
                        </div>

                        <div class="mayhen_client_footer_contact_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>Atelier: Hà Nội &bull; TP. Hồ Chí Minh</span>
                        </div>
                    </div>

                    <!-- Mạng xã hội -->
                    <div class="mayhen_client_footer_socials">
                        <span class="mayhen_client_footer_socials_title">Kết nối cùng Mây Hẹn:</span>
                        <div class="mayhen_client_footer_social_icons">
                            <a href="#" class="mayhen_client_footer_social_btn" aria-label="Facebook Mây Hẹn" title="Facebook">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="#" class="mayhen_client_footer_social_btn" aria-label="Instagram Mây Hẹn" title="Instagram">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a href="#" class="mayhen_client_footer_social_btn" aria-label="TikTok Mây Hẹn" title="TikTok">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.97v7.54c-.01 1.95-.73 3.91-2.07 5.34-1.76 1.9-4.4 2.85-6.95 2.5-2.74-.36-5.07-2.31-5.91-4.94-.97-3.04.14-6.52 2.75-8.28 1.58-1.07 3.53-1.48 5.41-1.19v4.11c-.93-.31-1.99-.24-2.87.23-.97.51-1.62 1.54-1.67 2.64-.06 1.43.91 2.75 2.3 3.09 1.34.34 2.82-.26 3.47-1.48.24-.46.36-.98.35-1.51V.02h2.95z"/></svg>
                            </a>
                            <a href="#" class="mayhen_client_footer_social_btn" aria-label="Zalo Official Account" title="Zalo OA">
                                <span style="font-weight: 700; font-size: 11px;">Zalo</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cột 2: Bộ sưu tập Mẫu thiệp -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Kho Mẫu Thiệp</h3>
                    <ul class="mayhen_client_footer_nav_list">
                        <li><a href="{{ route('templates.index', ['category' => 'luxury']) }}" class="mayhen_client_footer_nav_link">Quiet Luxury Hoàng Gia</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'editorial']) }}" class="mayhen_client_footer_nav_link">Tạp Chí Editorial Hiện Đại</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'envelope']) }}" class="mayhen_client_footer_nav_link">Phong Bì Tem Sáp 3D</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'minimal']) }}" class="mayhen_client_footer_nav_link">Tối Giản Tinh Tế (Minimalist)</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'romantic']) }}" class="mayhen_client_footer_nav_link">Hoa Cỏ Lãng Mạn (Flora)</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'tradition']) }}" class="mayhen_client_footer_nav_link">Lễ Vu Quy &amp; Thành Hôn</a></li>
                        <li><a href="{{ route('templates.index') }}" class="mayhen_client_footer_nav_link mayhen_client_footer_highlight_link">Xem Toàn Bộ 50+ Mẫu &rarr;</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Tính năng & Trải nghiệm -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Tính Năng Cưới</h3>
                    <ul class="mayhen_client_footer_nav_list">
                        <li><a href="{{ route('services') }}#mo-thiep" class="mayhen_client_footer_nav_link">Mở Phong Bì Tem Sáp</a></li>
                        <li><a href="{{ route('services') }}#nhac-nen" class="mayhen_client_footer_nav_link">Nhạc Nền Cưới Tự Động</a></li>
                        <li><a href="{{ route('services') }}#rsvp" class="mayhen_client_footer_nav_link">Báo Hỷ RSVP Chốt Bàn</a></li>
                        <li><a href="{{ route('services') }}#vietqr" class="mayhen_client_footer_nav_link">Mừng Cưới Mã VietQR</a></li>
                        <li><a href="{{ route('services') }}#ban-do" class="mayhen_client_footer_nav_link">Bản Đồ Chỉ Đường 1 Chạm</a></li>
                        <li><a href="{{ route('services') }}#album" class="mayhen_client_footer_nav_link">Album Phóng Lớn Lightbox</a></li>
                        <li><a href="{{ route('services') }}#so-luu-but" class="mayhen_client_footer_nav_link">Sổ Lưu Bút Gửi Lời Chúc</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Thông tin & Chính sách -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Về Mây Hẹn</h3>
                    <ul class="mayhen_client_footer_nav_list">
                        <li><a href="{{ route('about') }}" class="mayhen_client_footer_nav_link">Câu Chuyện Thương Hiệu</a></li>
                        <li><a href="{{ route('pricing') }}" class="mayhen_client_footer_nav_link">Bảng Giá Các Gói Cưới</a></li>
                        <li><a href="{{ route('how-it-works') }}" class="mayhen_client_footer_nav_link">Quy Trình Tạo &amp; Gửi Thiệp</a></li>
                        <li><a href="{{ route('contact') }}" class="mayhen_client_footer_nav_link">Hợp Tác Studio / Nhiếp Ảnh</a></li>
                        <li><a href="{{ route('faq') }}" class="mayhen_client_footer_nav_link">Câu Hỏi Thường Gặp (FAQ)</a></li>
                        <li><a href="{{ route('privacy') }}" class="mayhen_client_footer_nav_link">Chính Sách Bảo Mật Dữ Liệu</a></li>
                        <li><a href="{{ route('terms') }}" class="mayhen_client_footer_nav_link">Điều Khoản Dịch Vụ</a></li>
                    </ul>
                </div>

                <!-- Cột 5: Trải nghiệm di động & Chứng nhận -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Trải Nghiệm Thử</h3>
                    <div class="mayhen_client_footer_qr_box">
                        <div class="mayhen_client_footer_qr_code" aria-label="Mã QR xem demo thiệp">
                            <!-- SVG QR Code Mockup tinh tế -->
                            <svg viewBox="0 0 100 100" width="76" height="76" fill="#1C1917">
                                <rect width="100" height="100" fill="#FFFFFF" rx="8"/>
                                <path d="M12 12h24v24H12zM16 16h16v16H16zM20 20h8v8h-8zM64 12h24v24H64zM68 16h16v16H68zM72 20h8v8h-8zM12 64h24v24H12zM16 68h16v16H16zM20 72h8v8h-8zM44 14h6v6h-6zM54 20h6v6h-6zM44 26h6v6h-6zM44 44h12v12H44zM60 44h6v6h-6zM68 44h8v8h-8zM44 60h8v6h-8zM60 54h6v8h-6zM54 68h6v6h-6zM68 60h6v6h-6zM44 80h8v8h-8zM60 74h8v6h-8zM74 72h12v6h-12zM80 84h8v4h-8z"/>
                            </svg>
                        </div>
                        <div class="mayhen_client_footer_qr_info">
                            <span class="mayhen_client_footer_qr_label">Quét mã mở thiệp</span>
                            <span class="mayhen_client_footer_qr_desc">Trải nghiệm thực tế trên điện thoại của bạn</span>
                        </div>
                    </div>

                    <div class="mayhen_client_footer_badges_group">
                        <span class="mayhen_client_footer_cert_label">Chứng nhận &amp; Tiêu chuẩn</span>
                        <div class="mayhen_client_footer_cert_row">
                            <span class="mayhen_client_footer_cert_badge" title="Đã đăng ký chứng nhận thương mại điện tử">
                                <span class="mayhen_client_footer_cert_dot" style="background: #10B981;"></span>
                                <span>Bộ Công Thương</span>
                            </span>
                            <span class="mayhen_client_footer_cert_badge" title="Bảo vệ bản quyền DMCA">
                                <span class="mayhen_client_footer_cert_dot" style="background: #3B82F6;"></span>
                                <span>DMCA Protected</span>
                            </span>
                            <span class="mayhen_client_footer_cert_badge" title="Mã hóa SSL 256-bit chuẩn ngân hàng">
                                <span class="mayhen_client_footer_cert_dot" style="background: #F59E0B;"></span>
                                <span>SSL 256-Bit</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Thanh Hệ Sinh Thái & Đối Tác Đồng Hành (Ecosystem & Partners) -->
    <div class="mayhen_client_footer_ecosystem_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_ecosystem_inner">
                <span class="mayhen_client_footer_ecosystem_label">Hệ sinh thái &amp; Đối tác:</span>
                <div class="mayhen_client_footer_ecosystem_tags">
                    <span class="mayhen_client_footer_eco_pill">MayHen Atelier</span>
                    <span class="mayhen_client_footer_eco_pill">MayHen RSVP Pro</span>
                    <span class="mayhen_client_footer_eco_pill">VietQR NAPAS</span>
                    <span class="mayhen_client_footer_eco_pill">Wedding Studio Partners</span>
                    <span class="mayhen_client_footer_eco_pill">Nhà In Mỹ Thuật Cưới</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Khối Từ Khóa Tìm Kiếm Nổi Bật (SEO Keywords Cloud) -->
    <div class="mayhen_client_footer_seo_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_seo_inner">
                <span class="mayhen_client_footer_seo_title">Từ khóa thịnh hành:</span>
                <div class="mayhen_client_footer_seo_links">
                    <a href="{{ route('templates.index') }}">Thiệp cưới online</a>
                    <span>|</span>
                    <a href="{{ route('templates.index') }}">Mẫu thiệp cưới đẹp</a>
                    <span>|</span>
                    <a href="{{ route('templates.index') }}">Thiệp cưới điện tử</a>
                    <span>|</span>
                    <a href="{{ route('templates.index') }}">Thiệp cưới sang trọng</a>
                    <span>|</span>
                    <a href="{{ route('services') }}">Tạo thiệp cưới online miễn phí</a>
                    <span>|</span>
                    <a href="{{ route('services') }}">Báo hỷ online</a>
                    <span>|</span>
                    <a href="{{ route('services') }}">RSVP xác nhận tham dự đám cưới</a>
                    <span>|</span>
                    <a href="{{ route('services') }}">Mừng cưới VietQR</a>
                    <span>|</span>
                    <a href="{{ route('templates.index') }}">Thiệp cưới phong bì sáp</a>
                    <span>|</span>
                    <a href="{{ route('how-it-works') }}">Quy trình gửi thiệp cưới Zalo</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Chân Trang Pháp Lý & Bản Quyền (Legal Bottom Bar) -->
    <div class="mayhen_client_footer_bottom">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_bottom_inner">
                <div class="mayhen_client_footer_legal_info">
                    <p class="mayhen_client_footer_company_name">CÔNG TY TNHH CÔNG NGHỆ &amp; SỰ KIỆN MÂY HẸN (MAYHEN.VN)</p>
                    <p class="mayhen_client_footer_company_meta">
                        GPĐKKD: 0110xxxxxx cấp bởi Sở Kế hoạch và Đầu tư TP. Hà Nội &bull; Hotline: 0988.xxx.xxx &bull; Trụ sở: Tòa nhà MayHen, Quận Cầu Giấy, Hà Nội.
                    </p>
                    <p class="mayhen_client_footer_copyright">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. Bản quyền thuộc về Mây Hẹn. Thiết kế vì tình yêu và sự tinh tế.
                    </p>
                </div>

                <div class="mayhen_client_footer_legal_links">
                    <a href="{{ route('privacy') }}" class="mayhen_client_footer_legal_link">Chính Sách Bảo Mật</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('terms') }}" class="mayhen_client_footer_legal_link">Quy Định Sử Dụng</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('faq') }}" class="mayhen_client_footer_legal_link">Trợ Giúp</a>
                </div>
            </div>
        </div>
    </div>
</footer>
