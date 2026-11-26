@php
    $footerBrandName = \App\Models\PlatformSetting::getBrandName();
    $footerBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $footerBrandLogo = \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'brand_logo',
        '/storage/images/client/business/mayhen-logo.png'
    );
    $footerTagline = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'tagline',
        'Nền tảng thiệp cưới trực tuyến cao cấp'
    ));
    $footerHotline = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'hotline',
        ''
    ));
    $footerHotlineDigits = preg_replace('/[^0-9+]/', '', $footerHotline) ?: '';
    $footerHasCallableHotline = $footerHotline !== '' && ! str_contains(strtolower($footerHotline), 'x') && strlen($footerHotlineDigits) >= 8;

    $footerSupportEmail = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'support_email',
        'support@mayhen.vn'
    )) ?: 'support@mayhen.vn';

    $footerCompanyName = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'company_name',
        $footerBrandName . ' (mayhen.vn)'
    )) ?: ($footerBrandName . ' (mayhen.vn)');

    $footerBusinessLicense = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'business_license',
        ''
    ));

    $footerBusinessAddress = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'business_address',
        'Hà Nội, Việt Nam'
    )) ?: 'Hà Nội, Việt Nam';

    $footerWorkingHours = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'working_hours',
        '08:00 - 22:00 (Thứ 2 - Chủ Nhật)'
    )) ?: '08:00 - 22:00 (Thứ 2 - Chủ Nhật)';

    $footerBankName = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_PAYMENT,
        'bank_name',
        'Ngân hàng TMCP Quân Đội (MBBank)'
    ));

    $footerSocials = array_filter([
        'facebook' => trim((string) \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'facebook_url', '')),
        'instagram' => trim((string) \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'instagram_url', '')),
        'tiktok' => trim((string) \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'tiktok_url', '')),
        'zalo' => trim((string) \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'zalo_url', '')),
    ], fn (string $url): bool => $url !== '' && $url !== '#');
@endphp
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
                        <p class="mayhen_client_footer_value_title">Chuẩn Quiet Luxury</p>
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
                        <p class="mayhen_client_footer_value_title">Khởi Tạo Trong 5 Phút</p>
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
                        <p class="mayhen_client_footer_value_title">Cá Nhân Hóa Khách Mời</p>
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
                        <p class="mayhen_client_footer_value_title">Bảo Mật &amp; Riêng Tư</p>
                        <p class="mayhen_client_footer_value_desc">Tuân thủ Luật BVDLCN 91/2025/QH15, tùy chọn khóa mật khẩu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Khối Khởi Tạo Nhanh & Cổng Thanh Toán Thực Tế -->
    <div class="mayhen_client_footer_newsletter_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_newsletter_grid">
                <div class="mayhen_client_footer_newsletter_left">
                    <div class="mayhen_client_footer_newsletter_badge">TRẢI NGHIỆM MIỄN PHÍ 0Đ</div>
                    <h3 class="mayhen_client_footer_newsletter_title">Tự Thiết Kế Thiệp Cưới Online &amp; Đặt In Thiệp Giấy</h3>
                    <p class="mayhen_client_footer_newsletter_sub">
                        Khởi tạo bản nháp hoàn toàn miễn phí trong Studio {{ $footerBrandName }} hoặc liên hệ tư vấn trực tiếp về in thiệp giấy mỹ thuật cao cấp.
                    </p>
                    <div class="mayhen_client_footer_cta_actions">
                        <a href="{{ route('invitation.create') }}" class="mayhen_client_footer_submit_btn">
                            <span>Tạo Thiệp Ngay</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('contact') }}" class="mayhen_client_footer_secondary_btn">
                            <span>Tư Vấn In Thiệp Giấy</span>
                        </a>
                    </div>
                </div>

                <div class="mayhen_client_footer_payments_right">
                    <a href="{{ route('policy.payment') }}" class="mayhen_client_footer_payments_label">
                        Phương thức thanh toán minh bạch &rarr;
                    </a>
                    <div class="mayhen_client_footer_payments_badges">
                        <span class="mayhen_client_footer_pay_pill" title="Chuyển khoản nhanh NAPAS 24/7 qua mã VietQR">VietQR NAPAS 24/7</span>
                        <span class="mayhen_client_footer_pay_pill" title="Đối soát giao dịch tự động 5-15 giây qua SePay">SePay Tự Động</span>
                        <span class="mayhen_client_footer_pay_pill" title="Quét mã QR bằng hơn 40 ứng dụng ngân hàng Việt Nam">Mobile Banking QR</span>
                        <span class="mayhen_client_footer_pay_pill" title="Hỗ trợ chuyển khoản trực tiếp vào tài khoản thụ hưởng">Chuyển Khoản Ngân Hàng</span>
                        <span class="mayhen_client_footer_pay_pill" title="0% phí trung gian khi khách mừng cưới về tài khoản cặp đôi">0% Phí Mừng Cưới</span>
                    </div>
                    @if ($footerBankName !== '')
                        <p class="mayhen_client_footer_payment_note">
                            Đối soát tự động qua hệ thống {{ $footerBankName }} &bull; Xem chi tiết tại <a href="{{ route('policy.payment') }}">Chính sách thanh toán</a>.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Lưới Thông Tin & Điều Hướng 5 Cột Chính -->
    <div class="mayhen_client_footer_main_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_grid_5col">
                <!-- Cột 1: Logo Thật & Thông Tin Liên Hệ Thực Tế -->
                <div class="mayhen_client_footer_col mayhen_client_footer_col_brand">
                    <a href="{{ route('home') }}" class="mayhen_client_footer_brand_logo" aria-label="Trang chủ {{ $footerBrandName }}">
                        <span class="mayhen_client_footer_brand_logo_frame">
                            <img src="{{ asset($footerBrandLogo) }}"
                                 alt="{{ $footerBrandName }} - {{ $footerTagline }}"
                                 class="mayhen_client_footer_brand_logo_img"
                                 width="184"
                                 height="44"
                                 loading="lazy"
                                 decoding="async">
                        </span>
                    </a>
                    <p class="mayhen_client_footer_brand_desc">
                        {{ $footerTagline }}. Nơi từng khuôn hình, nét chữ và giai điệu hòa quyện thành tấm thiệp cưới điện tử và thiệp giấy mỹ thuật chuẩn Quiet Luxury.
                    </p>

                    <div class="mayhen_client_footer_contact_box">
                        <div class="mayhen_client_footer_hotline_card">
                            <div class="mayhen_client_footer_hotline_icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <span class="mayhen_client_footer_hotline_sub">Hỗ trợ khách hàng ({{ $footerWorkingHours }})</span>
                                @if ($footerHasCallableHotline)
                                    <a href="tel:{{ $footerHotlineDigits }}" class="mayhen_client_footer_hotline_num">{{ $footerHotline }}</a>
                                @else
                                    <a href="{{ route('contact') }}" class="mayhen_client_footer_hotline_num">Gửi Yêu Cầu Tư Vấn &rarr;</a>
                                @endif
                            </div>
                        </div>

                        <div class="mayhen_client_footer_contact_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <span>Email: <a href="mailto:{{ $footerSupportEmail }}">{{ $footerSupportEmail }}</a></span>
                        </div>

                        <div class="mayhen_client_footer_contact_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>Địa chỉ: {{ $footerBusinessAddress }}</span>
                        </div>

                        <div class="mayhen_client_footer_contact_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            <span>Giờ hỗ trợ: {{ $footerWorkingHours }}</span>
                        </div>
                    </div>

                    @if (! empty($footerSocials))
                        <!-- Mạng xã hội chính thức (chỉ hiển thị khi có đường dẫn thật trong Admin) -->
                        <div class="mayhen_client_footer_socials">
                            <span class="mayhen_client_footer_socials_title">Kênh chính thức {{ $footerBrandName }}:</span>
                            <div class="mayhen_client_footer_social_icons">
                                @if (! empty($footerSocials['facebook']))
                                    <a href="{{ $footerSocials['facebook'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_footer_social_btn" aria-label="Facebook {{ $footerBrandName }}" title="Facebook">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </a>
                                @endif
                                @if (! empty($footerSocials['instagram']))
                                    <a href="{{ $footerSocials['instagram'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_footer_social_btn" aria-label="Instagram {{ $footerBrandName }}" title="Instagram">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </a>
                                @endif
                                @if (! empty($footerSocials['tiktok']))
                                    <a href="{{ $footerSocials['tiktok'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_footer_social_btn" aria-label="TikTok {{ $footerBrandName }}" title="TikTok">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.97v7.54c-.01 1.95-.73 3.91-2.07 5.34-1.76 1.9-4.4 2.85-6.95 2.5-2.74-.36-5.07-2.31-5.91-4.94-.97-3.04.14-6.52 2.75-8.28 1.58-1.07 3.53-1.48 5.41-1.19v4.11c-.93-.31-1.99-.24-2.87.23-.97.51-1.62 1.54-1.67 2.64-.06 1.43.91 2.75 2.3 3.09 1.34.34 2.82-.26 3.47-1.48.24-.46.36-.98.35-1.51V.02h2.95z"/></svg>
                                    </a>
                                @endif
                                @if (! empty($footerSocials['zalo']))
                                    <a href="{{ $footerSocials['zalo'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_footer_social_btn" aria-label="Zalo {{ $footerBrandName }}" title="Zalo">
                                        <span class="mayhen_client_footer_social_zalo">Zalo</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Cột 2: Kho Mẫu Thiệp -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Kho Mẫu Thiệp</h3>
                    <ul class="mayhen_client_footer_nav_list">
                        <li><a href="{{ route('templates.index', ['category' => 'luxury']) }}" class="mayhen_client_footer_nav_link">Quiet Luxury Hoàng Gia</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'editorial']) }}" class="mayhen_client_footer_nav_link">Tạp Chí Editorial Hiện Đại</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'envelope']) }}" class="mayhen_client_footer_nav_link">Phong Bì Tem Sáp 3D</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'minimal']) }}" class="mayhen_client_footer_nav_link">Tối Giản Tinh Tế (Minimalist)</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'romantic']) }}" class="mayhen_client_footer_nav_link">Hoa Cỏ Lãng Mạn (Flora)</a></li>
                        <li><a href="{{ route('templates.index', ['category' => 'tradition']) }}" class="mayhen_client_footer_nav_link">Lễ Vu Quy &amp; Thành Hôn</a></li>
                        <li><a href="{{ route('event_invitations.index') }}" class="mayhen_client_footer_nav_link">Thiệp Sự Kiện &amp; Lễ Hội (PRO)</a></li>
                        <li><a href="{{ route('templates.index') }}" class="mayhen_client_footer_nav_link mayhen_client_footer_highlight_link">Xem Toàn Bộ Mẫu Thiệp &rarr;</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Dịch Vụ & Tài Nguyên (Cân bằng 9 mục chuẩn chiều cao với các cột bên cạnh) -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Khám Phá &amp; Hỗ Trợ</h3>
                    <ul class="mayhen_client_footer_nav_list">
                        <li><a href="{{ route('about') }}" class="mayhen_client_footer_nav_link">Về {{ $footerBrandName }}</a></li>
                        <li><a href="{{ route('services') }}" class="mayhen_client_footer_nav_link">Tính Năng &amp; Dịch Vụ Cưới</a></li>
                        <li><a href="{{ route('pricing') }}" class="mayhen_client_footer_nav_link">Bảng Giá Các Gói Cưới</a></li>
                        <li><a href="{{ route('pricing.advisor') }}" class="mayhen_client_footer_nav_link">Tư Vấn Chọn Gói Phù Hợp</a></li>
                        <li><a href="{{ route('blog.index') }}" class="mayhen_client_footer_nav_link">Cẩm Nang &amp; Ý Tưởng Cưới</a></li>
                        <li><a href="{{ route('news.index') }}" class="mayhen_client_footer_nav_link">Tin Tức &amp; Ra Mắt Mẫu Mới</a></li>
                        <li><a href="{{ route('docs.index') }}" class="mayhen_client_footer_nav_link">Trung Tâm Hướng Dẫn (Docs)</a></li>
                        <li><a href="{{ route('faq') }}" class="mayhen_client_footer_nav_link">Câu Hỏi Thường Gặp (FAQ)</a></li>
                        <li><a href="{{ route('partner.register') }}" class="mayhen_client_footer_nav_link">Đăng Ký Đối Tác Studio B2B</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Chính Sách & Quy Định (Đủ 7 mục Bộ Công Thương + Cookie + Điều Khoản) -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Chính Sách &amp; Quy Định</h3>
                    <ul class="mayhen_client_footer_nav_list">
                        <li><a href="{{ route('privacy') }}" class="mayhen_client_footer_nav_link">Chính Sách Bảo Mật</a></li>
                        <li><a href="{{ route('policy.cookies') }}" class="mayhen_client_footer_nav_link">Chính Sách Cookie &amp; Dữ Liệu</a></li>
                        <li><a href="{{ route('policy.complaints') }}" class="mayhen_client_footer_nav_link">Giải Quyết Phản Ánh &amp; Khiếu Nại</a></li>
                        <li><a href="{{ route('policy.pricing') }}" class="mayhen_client_footer_nav_link">Chính Sách Giá Dịch Vụ</a></li>
                        <li><a href="{{ route('policy.payment') }}" class="mayhen_client_footer_nav_link">Chính Sách Về Thanh Toán</a></li>
                        <li><a href="{{ route('policy.service_conditions') }}" class="mayhen_client_footer_nav_link">Điều Kiện Cung Cấp Dịch Vụ</a></li>
                        <li><a href="{{ route('policy.delivery_refund') }}" class="mayhen_client_footer_nav_link">Giao Hàng, Đổi Trả &amp; Hoàn Tiền</a></li>
                        <li><a href="{{ route('policy.support') }}" class="mayhen_client_footer_nav_link">Hình Thức Hỗ Trợ Trực Tuyến</a></li>
                        <li><a href="{{ route('terms') }}" class="mayhen_client_footer_nav_link">Điều Khoản Sử Dụng</a></li>
                    </ul>
                </div>

                <!-- Cột 5: Trải Nghiệm Thực Tế & Tiêu Chuẩn Pháp Lý -->
                <div class="mayhen_client_footer_col">
                    <h3 class="mayhen_client_footer_nav_title">Bắt Đầu &amp; Pháp Lý</h3>
                    <div class="mayhen_client_footer_action_card">
                        <span class="mayhen_client_footer_qr_label">Studio Tạo Thiệp V2</span>
                        <span class="mayhen_client_footer_qr_desc">Chọn mẫu, nhập thông tin và xem trước trực tiếp trên di động mà không cần trả phí trước.</span>
                        <a href="{{ route('invitation.create') }}" class="mayhen_client_footer_action_link">Mở Studio Miễn Phí &rarr;</a>
                    </div>

                    <div class="mayhen_client_footer_badges_group">
                        <span class="mayhen_client_footer_cert_label">Tiêu chuẩn vận hành</span>
                        <div class="mayhen_client_footer_cert_row">
                            <a href="{{ route('privacy') }}" class="mayhen_client_footer_cert_badge" title="Tuân thủ Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15">
                                <span class="mayhen_client_footer_cert_dot mayhen_client_footer_cert_dot--green"></span>
                                <span>Luật BVDLCN 91/2025/QH15</span>
                            </a>
                            <a href="{{ route('policy.complaints') }}" class="mayhen_client_footer_cert_badge" title="Tuân thủ Luật Thương mại điện tử 122/2025/QH15 và Bảo vệ quyền lợi người tiêu dùng">
                                <span class="mayhen_client_footer_cert_dot mayhen_client_footer_cert_dot--blue"></span>
                                <span>Luật TMĐT 122/2025/QH15</span>
                            </a>
                            <a href="{{ route('policy.payment') }}" class="mayhen_client_footer_cert_badge" title="Kết nối mã hóa HTTPS / TLS 1.3 bảo vệ toàn bộ giao dịch">
                                <span class="mayhen_client_footer_cert_dot mayhen_client_footer_cert_dot--gold"></span>
                                <span>Bảo Mật HTTPS / TLS 1.3</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3.5. Thanh Công Cụ Trực Tuyến Miễn Phí (Tách khỏi cột Khám Phá để cân đối 5 cột Footer) -->
    <div class="mayhen_client_footer_ecosystem_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_ecosystem_inner">
                <span class="mayhen_client_footer_ecosystem_label">Công cụ miễn phí:</span>
                <div class="mayhen_client_footer_ecosystem_tags">
                    <a href="{{ route('wedding.dates.index') }}" class="mayhen_client_footer_eco_pill">Xem Ngày Cưới</a>
                    <a href="{{ route('wedding.planner.index') }}" class="mayhen_client_footer_eco_pill">Tổ Chức Đám Cưới</a>
                    <a href="{{ route('wedding.ceremonies') }}" class="mayhen_client_footer_eco_pill">Nghi Lễ 3 Miền</a>
                    <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_client_footer_eco_pill">Xem Tuổi Vợ Chồng</a>
                    <a href="{{ route('marriage.caoly.index') }}" class="mayhen_client_footer_eco_pill">Cao Ly Đầu Hình</a>
                    <a href="{{ route('calendar.index') }}" class="mayhen_client_footer_eco_pill">Lịch Âm &amp; Vạn Niên</a>
                    <a href="{{ route('bazi.index') }}" class="mayhen_client_footer_eco_pill">Tra Cứu Bát Tự</a>
                    <a href="{{ route('tuvi.index') }}" class="mayhen_client_footer_eco_pill">Tử Vi Đẩu Số</a>
                    <a href="{{ route('numerology.index') }}" class="mayhen_client_footer_eco_pill">Thần Số Học</a>
                    <a href="{{ route('iching.index') }}" class="mayhen_client_footer_eco_pill">Gieo Quẻ Kinh Dịch</a>
                    <a href="{{ route('tarot.index') }}" class="mayhen_client_footer_eco_pill">Xem Tarot</a>
                    <a href="{{ route('family_tree.index') }}" class="mayhen_client_footer_eco_pill">Dựng Gia Phả</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Thanh Hệ Sinh Thái Nền Tảng -->
    <div class="mayhen_client_footer_ecosystem_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_ecosystem_inner">
                <span class="mayhen_client_footer_ecosystem_label">Hệ sinh thái {{ $footerBrandName }}:</span>
                <div class="mayhen_client_footer_ecosystem_tags">
                    <a href="{{ route('templates.index') }}" class="mayhen_client_footer_eco_pill">Thiệp Cưới Online</a>
                    <a href="{{ route('event_invitations.index') }}" class="mayhen_client_footer_eco_pill">Thiệp Sự Kiện PRO</a>
                    <a href="{{ route('contact') }}" class="mayhen_client_footer_eco_pill">In Thiệp Giấy Mỹ Thuật</a>
                    <a href="{{ route('services') }}#rsvp" class="mayhen_client_footer_eco_pill">Quản Lý RSVP &amp; Khách Mời</a>
                    <a href="{{ route('partner.register') }}" class="mayhen_client_footer_eco_pill">Đối Tác Studio B2B</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Khối Từ Khóa Tìm Kiếm Nổi Bật -->
    <div class="mayhen_client_footer_seo_strip">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_seo_inner">
                <span class="mayhen_client_footer_seo_title">Khám phá nhanh:</span>
                <div class="mayhen_client_footer_seo_links">
                    <a href="{{ route('templates.index') }}">Thiệp cưới online</a>
                    <span>|</span>
                    <a href="{{ route('templates.index') }}">Mẫu thiệp cưới đẹp</a>
                    <span>|</span>
                    <a href="{{ route('event_invitations.category', 'tet-nguyen-dan') }}">Thiệp chúc Tết</a>
                    <span>|</span>
                    <a href="{{ route('event_invitations.category', 'day-thang-thoi-noi') }}">Thiệp đầy tháng thôi nôi</a>
                    <span>|</span>
                    <a href="{{ route('event_invitations.category', 'tan-gia-nha-moi') }}">Thiệp mời tân gia</a>
                    <span>|</span>
                    <a href="{{ route('services') }}">Tạo thiệp cưới online miễn phí</a>
                    <span>|</span>
                    <a href="{{ route('services') }}#rsvp">RSVP xác nhận tham dự</a>
                    <span>|</span>
                    <a href="{{ route('services') }}#vietqr">Mừng cưới VietQR</a>
                    <span>|</span>
                    <a href="{{ route('docs.index') }}">Hướng dẫn gửi thiệp Zalo</a>
                    <span>|</span>
                    <a href="{{ route('blog.index') }}">Cẩm nang cưới</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Chân Trang Pháp Lý & Bản Quyền (Legal Bottom Bar) -->
    <div class="mayhen_client_footer_bottom">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_client_footer_bottom_inner">
                <div class="mayhen_client_footer_legal_info">
                    <p class="mayhen_client_footer_company_name">{{ mb_strtoupper($footerCompanyName, 'UTF-8') }}</p>
                    <p class="mayhen_client_footer_company_meta">
                        @if ($footerBusinessLicense !== '')
                            {{ $footerBusinessLicense }} &bull;
                        @endif
                        Địa chỉ: {{ $footerBusinessAddress }}
                        @if ($footerHasCallableHotline)
                            &bull; Hotline: {{ $footerHotline }}
                        @endif
                        &bull; Email: {{ $footerSupportEmail }}
                    </p>
                    <p class="mayhen_client_footer_copyright">
                        &copy; {{ date('Y') }} {{ $footerBrandName }} (mayhen.vn). Bảo lưu mọi quyền.
                    </p>
                </div>

                <div class="mayhen_client_footer_legal_links">
                    <a href="{{ route('privacy') }}" class="mayhen_client_footer_legal_link">Bảo Mật</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('policy.complaints') }}" class="mayhen_client_footer_legal_link">Khiếu Nại</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('policy.pricing') }}" class="mayhen_client_footer_legal_link">Chính Sách Giá</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('policy.payment') }}" class="mayhen_client_footer_legal_link">Thanh Toán</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('policy.delivery_refund') }}" class="mayhen_client_footer_legal_link">Hoàn Tiền</a>
                    <span class="mayhen_client_footer_legal_sep">&bull;</span>
                    <a href="{{ route('terms') }}" class="mayhen_client_footer_legal_link">Điều Khoản</a>
                </div>
            </div>
        </div>
    </div>
</footer>
