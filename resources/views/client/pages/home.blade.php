@extends('client.layouts.master')

@section('title', 'Mây Hẹn — Nền Tảng Tạo Thiệp Cưới Online Cao Cấp Số 1 Việt Nam')
@section('meta_description', 'Tự tạo thiệp cưới online phong cách Quiet Luxury, hiệu ứng mở phong thư hoàng gia, tối ưu điện thoại và quản lý khách mời chu đáo.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@section('content')
    <!-- 1. Hero Section -->
    <section class="mayhen_home_hero">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_hero_grid">
                <!-- Left Content -->
                <div class="mayhen_home_hero_content">
                    <span class="mayhen_home_hero_badge">Thiệp Cưới Điện Tử Thế Hệ Mới</span>
                    
                    <h1 class="mayhen_home_hero_title">
                        Gửi trao hạnh phúc trong <span class="mayhen_home_hero_title_highlight">từng nhịp chạm</span>
                    </h1>

                    <p class="mayhen_home_hero_desc">
                        Nền tảng thiệp cưới online chuẩn phong cách Quiet Luxury. Hiệu ứng mở phong thư sống động, thiết kế tối giản sang trọng, và bộ công cụ mời cưới chu đáo nhất dành cho ngày trọng đại của bạn.
                    </p>

                    <div class="mayhen_home_hero_actions">
                        <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                            Tạo Thiệp Miễn Phí Ngay
                        </a>
                        <a href="{{ route('templates.preview', 'envelope-reveal-luxury') }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg">
                            Xem Mẫu Trực Tiếp
                        </a>
                    </div>

                    <!-- Trust Stats -->
                    <div class="mayhen_home_hero_trust">
                        <div class="mayhen_home_hero_trust_item">
                            <span class="mayhen_home_hero_trust_number">100%</span>
                            <span class="mayhen_home_hero_trust_label">Chuẩn Điện Thoại</span>
                        </div>
                        <div class="mayhen_home_hero_trust_item">
                            <span class="mayhen_home_hero_trust_number">3 Phút</span>
                            <span class="mayhen_home_hero_trust_label">Tạo & Xem Thử</span>
                        </div>
                        <div class="mayhen_home_hero_trust_item">
                            <span class="mayhen_home_hero_trust_number">0đ</span>
                            <span class="mayhen_home_hero_trust_label">Dùng Thử Miễn Phí</span>
                        </div>
                    </div>
                </div>

                <!-- Right Visual: Mobile Mockup Interactive -->
                <div class="mayhen_home_hero_visual">
                    <div class="mayhen_home_phone_frame">
                        <div class="mayhen_home_phone_notch"></div>
                        <div class="mayhen_home_phone_screen">
                            <div class="mayhen_home_card_preview">
                                <div class="mayhen_home_card_envelope_stamp" title="Nhấp để thử hiệu ứng">
                                    M&A
                                </div>
                                <h2 class="mayhen_home_card_title">Minh Quân & Ái Như</h2>
                                <div class="mayhen_home_card_date">Chủ Nhật &bull; 24.10.2026</div>
                                <img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80" alt="Ảnh cưới cặp đôi" class="mayhen_home_card_image">
                                <span class="mayhen_home_card_badge">Chạm để mở phong thư &bull; Chúc mừng</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Curated Aesthetics Showcase -->
    <section class="mayhen_client_layout_section mayhen_client_layout_section_soft">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_section_header">
                <span class="mayhen_home_section_subtitle">Bộ Sưu Tập Nghệ Thuật</span>
                <h2 class="mayhen_home_section_title">Mẫu Thiệp Được Thiết Kế Có Chủ Đích</h2>
                <p class="mayhen_home_section_desc">
                    Mỗi mẫu thiệp tại Mây Hẹn là một câu chuyện riêng biệt với bố cục, typography và nhịp điệu chuyển động được trau chuốt tỉ mỉ.
                </p>
            </div>

            <div class="mayhen_home_templates_grid">
                @foreach ($featuredTemplates as $tpl)
                    <article class="mayhen_home_template_card">
                        <div class="mayhen_home_template_card_img_wrap">
                            <img src="{{ $tpl['image'] }}" alt="{{ $tpl['name'] }}" class="mayhen_home_template_card_img" loading="lazy">
                            <span class="mayhen_home_template_card_badge">{{ $tpl['badge'] }}</span>
                        </div>
                        <div class="mayhen_home_template_card_body">
                            <span class="mayhen_home_template_card_category">{{ $tpl['category_label'] }}</span>
                            <h3 class="mayhen_home_template_card_name">{{ $tpl['name'] }}</h3>
                            <p class="mayhen_home_template_card_desc">{{ $tpl['description'] }}</p>
                            
                            <div class="mayhen_home_template_card_footer">
                                <a href="{{ route('templates.preview', $tpl['slug']) }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_sm">
                                    Xem Thử Live
                                </a>
                                <a href="{{ route('templates.show', $tpl['slug']) }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_sm">
                                    Chọn Mẫu Này
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 3. How It Works (3 Steps) -->
    <section class="mayhen_client_layout_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_section_header">
                <span class="mayhen_home_section_subtitle">Trải Nghiệm Dễ Dàng</span>
                <h2 class="mayhen_home_section_title">Tạo Thiệp Cưới Hoàn Hảo Trong 3 Bước</h2>
                <p class="mayhen_home_section_desc">
                    Không đòi hỏi kỹ năng thiết kế, không giao diện phức tạp. Mây Hẹn giúp bạn chuẩn bị thiệp cưới trong nháy mắt.
                </p>
            </div>

            <div class="mayhen_home_steps_grid">
                <div class="mayhen_home_step_item">
                    <div class="mayhen_home_step_number">01</div>
                    <h3 class="mayhen_home_step_title">Chọn Mẫu & Nhập Thông Tin</h3>
                    <p class="mayhen_home_step_desc">
                        Lựa chọn mẫu thiệp yêu thích, điền tên cô dâu chú rể, ngày cưới, địa điểm tiệc và tải lên những bức ảnh kỷ niệm đẹp nhất.
                    </p>
                </div>

                <div class="mayhen_home_step_item">
                    <div class="mayhen_home_step_number">02</div>
                    <h3 class="mayhen_home_step_title">Xem Thử Trực Tiếp Trên Điện Thoại</h3>
                    <p class="mayhen_home_step_desc">
                        Bản thiệp tự động kết xuất với animation mở phong thư mượt mà, đồng hồ đếm ngược, nhạc nền và bản đồ dẫn đường chính xác.
                    </p>
                </div>

                <div class="mayhen_home_step_item">
                    <div class="mayhen_home_step_number">03</div>
                    <h3 class="mayhen_home_step_title">Xuất Bản & Gửi Tới Khách Mời</h3>
                    <p class="mayhen_home_step_desc">
                        Nhận đường link thiệp riêng, tạo lời mời cá nhân hóa gửi qua Zalo/Facebook và dễ dàng theo dõi xác nhận tham dự (RSVP).
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Final CTA Banner -->
    <section class="mayhen_client_layout_section" style="padding-top: 0;">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_cta_banner">
                <h2 class="mayhen_home_cta_title">Sẵn Sàng Cho Tấm Thiệp Cưới Trong Mơ?</h2>
                <p class="mayhen_home_cta_desc">
                    Trải nghiệm tạo thiệp cưới miễn phí ngay hôm nay. Chỉ thanh toán khi bạn đã hoàn toàn ưng ý với tác phẩm của mình.
                </p>
                <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                    Bắt Đầu Tạo Thiệp Miễn Phí
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/home.js') }}"></script>
@endpush
