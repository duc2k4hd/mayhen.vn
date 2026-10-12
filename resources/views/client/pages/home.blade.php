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

                <!-- Right Visual: Trio Mobile Mockups Perspective Stage -->
                <div class="mayhen_home_hero_visual">
                    <div class="mayhen_home_trio_phones_stage">
                        
                        <!-- Floating Luxury Badge: Background Wedding Music -->
                        <div class="mayhen_home_float_badge mayhen_home_float_badge_music" title="Nhạc nền tự động khi khách mở thiệp">
                            <div class="mayhen_home_equalizer">
                                <span class="mayhen_home_equalizer_bar"></span>
                                <span class="mayhen_home_equalizer_bar"></span>
                                <span class="mayhen_home_equalizer_bar"></span>
                                <span class="mayhen_home_equalizer_bar"></span>
                            </div>
                            <div class="mayhen_home_float_badge_text">
                                <span class="mayhen_home_float_badge_title">Nhạc Nền Cưới</span>
                                <span class="mayhen_home_float_badge_subtitle">A Thousand Years 🎵</span>
                            </div>
                        </div>

                        <!-- 1. LEFT PHONE (Phong Thư Hoàng Gia & Nắp Gập Tem Sáp) -->
                        <div class="mayhen_home_phone_device mayhen_home_phone_device_left" title="iPhone 18 Pro Max Sunset Orange Titanium - Phong Thư Mở">
                            <!-- Earpiece Speaker & Antenna Inlays -->
                            <span class="mayhen_home_phone_speaker_slit"></span>
                            <span class="mayhen_home_phone_antenna_top_l"></span>
                            <span class="mayhen_home_phone_antenna_bot_l"></span>
                            
                            <!-- Flat Titanium Hardware Buttons -->
                            <span class="mayhen_home_phone_btn_action"></span>
                            <span class="mayhen_home_phone_btn_vol_up"></span>
                            <span class="mayhen_home_phone_btn_vol_down"></span>

                            <div class="mayhen_home_phone_screen_wrap">
                                <div class="mayhen_home_phone_island">
                                    <span class="mayhen_home_phone_island_camera"></span>
                                </div>

                                <div class="mayhen_home_phone_screen_content mayhen_home_mockup_envelope_screen">
                                    <div class="mayhen_home_mockup_envelope_box">
                                        <div class="mayhen_home_mockup_envelope_flap"></div>
                                        <div class="mayhen_home_mockup_wax_seal" title="Chạm để kiểm tra niêm phong">M&A</div>

                                        <span class="mayhen_home_mockup_envelope_invite_tag">THIỆP MỜI TRỌNG THỂ</span>
                                        <div class="mayhen_home_mockup_envelope_names">Minh Quân &bull; Ái Như</div>
                                        <div class="mayhen_home_mockup_envelope_date">Chủ Nhật &bull; 24.10.2026</div>

                                        <div class="mayhen_home_mockup_envelope_cta">
                                            <span>Chạm Mở Phong Thư</span>
                                            <span>💌</span>
                                        </div>
                                    </div>
                                    
                                    <span class="mayhen_home_card_badge" style="font-size: 0.5rem; margin-top: 8px;">
                                        Hiệu ứng mở thư độc quyền
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. CENTER PHONE (Lễ Thành Hôn & Song Thân Phụ Mẫu - Điểm Nhấn Trung Tâm) -->
                        <div class="mayhen_home_phone_device mayhen_home_phone_device_center" title="iPhone 18 Pro Max Sunset Orange Titanium - Lễ Thành Hôn">
                            <!-- Earpiece Speaker & Antenna Inlays -->
                            <span class="mayhen_home_phone_speaker_slit"></span>
                            <span class="mayhen_home_phone_antenna_top_l"></span>
                            <span class="mayhen_home_phone_antenna_bot_l"></span>
                            <span class="mayhen_home_phone_antenna_top_r"></span>
                            <span class="mayhen_home_phone_antenna_bot_r"></span>

                            <!-- Full Pro Max Side Controls -->
                            <span class="mayhen_home_phone_btn_action"></span>
                            <span class="mayhen_home_phone_btn_vol_up"></span>
                            <span class="mayhen_home_phone_btn_vol_down"></span>
                            <span class="mayhen_home_phone_btn_power"></span>
                            <span class="mayhen_home_phone_btn_camera"></span>

                            <div class="mayhen_home_phone_screen_wrap">
                                <div class="mayhen_home_phone_island">
                                    <span class="mayhen_home_phone_island_camera"></span>
                                </div>

                                <div class="mayhen_home_phone_screen_content">
                                    <span class="mayhen_home_mockup_top_badge">WELCOME TO OUR WEDDING</span>
                                    
                                    <div class="mayhen_home_mockup_monogram_row">
                                        <div class="mayhen_home_mockup_monogram_name">
                                            <span class="mayhen_home_mockup_monogram_role">ÚT NAM</span>
                                            MINH QUÂN
                                        </div>
                                        <div class="mayhen_home_mockup_monogram_seal">囍</div>
                                        <div class="mayhen_home_mockup_monogram_name">
                                            <span class="mayhen_home_mockup_monogram_role">ÚT NỮ</span>
                                            ÁI NHƯ
                                        </div>
                                    </div>

                                    <div class="mayhen_home_mockup_curved_quote">&bull; LOVE NEVER FAILS &bull; 2026 &bull;</div>

                                    <!-- Arch Photo Frame -->
                                    <div class="mayhen_home_mockup_arch_frame">
                                        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80" alt="Ảnh cưới Lễ Thành Hôn" class="mayhen_home_mockup_arch_img" loading="lazy">
                                    </div>

                                    <!-- Ribbon Title -->
                                    <div class="mayhen_home_mockup_ribbon">
                                        THÔNG TIN HÔN LỄ
                                    </div>

                                    <!-- Parents Information Grid -->
                                    <div class="mayhen_home_mockup_parents_grid">
                                        <div class="mayhen_home_mockup_parents_col">
                                            <span class="mayhen_home_mockup_parents_tag">NHÀ TRAI</span>
                                            <span class="mayhen_home_mockup_parents_name">Trần Văn Long</span>
                                            <span class="mayhen_home_mockup_parents_name">Phạm Thị Hoa</span>
                                            <span class="mayhen_home_mockup_parents_addr">12 Nguyễn Trãi, Thanh Xuân, Hà Nội</span>
                                        </div>
                                        <div class="mayhen_home_mockup_parents_col">
                                            <span class="mayhen_home_mockup_parents_tag">NHÀ GÁI</span>
                                            <span class="mayhen_home_mockup_parents_name">Phan Văn Hoàng</span>
                                            <span class="mayhen_home_mockup_parents_name">Lê Thị Mai</span>
                                            <span class="mayhen_home_mockup_parents_addr">36 Nguyễn Thái Sơn, Gò Vấp, TP.HCM</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. RIGHT PHONE (Tiệc Cưới Thân Mật & Xác Nhận Tham Dự RSVP) -->
                        <div class="mayhen_home_phone_device mayhen_home_phone_device_right" title="iPhone 18 Pro Max Sunset Orange Titanium - Tiệc Cưới & RSVP">
                            <!-- Earpiece Speaker & Antenna Inlays -->
                            <span class="mayhen_home_phone_speaker_slit"></span>
                            <span class="mayhen_home_phone_antenna_top_r"></span>
                            <span class="mayhen_home_phone_antenna_bot_r"></span>

                            <!-- Right Controls: Power & Camera Control Button -->
                            <span class="mayhen_home_phone_btn_power"></span>
                            <span class="mayhen_home_phone_btn_camera"></span>

                            <div class="mayhen_home_phone_screen_wrap">
                                <div class="mayhen_home_phone_island">
                                    <span class="mayhen_home_phone_island_camera"></span>
                                </div>

                                <div class="mayhen_home_phone_screen_content">
                                    <div class="mayhen_home_mockup_header_birds">
                                        <!-- Left Bird SVG Icon -->
                                        <svg class="mayhen_home_mockup_bird_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 7h.01M3.4 18H12a8 8 0 0 0 8-8V7a4 4 0 0 0-7.3-2.3L11 6"/>
                                            <path d="m3 13 4 5"/>
                                            <path d="m12 18-3-5"/>
                                        </svg>

                                        <div class="mayhen_home_mockup_couple_badge">
                                            <span class="mayhen_home_mockup_couple_name">MINH QUÂN</span>
                                            <span class="mayhen_home_mockup_hysymbol">囍</span>
                                            <span class="mayhen_home_mockup_couple_name">ÁI NHƯ</span>
                                        </div>

                                        <!-- Right Bird SVG Icon (mirrored) -->
                                        <svg class="mayhen_home_mockup_bird_icon" style="transform: scaleX(-1);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 7h.01M3.4 18H12a8 8 0 0 0 8-8V7a4 4 0 0 0-7.3-2.3L11 6"/>
                                            <path d="m3 13 4 5"/>
                                            <path d="m12 18-3-5"/>
                                        </svg>
                                    </div>

                                    <!-- Scalloped Photo Frame -->
                                    <div class="mayhen_home_mockup_scallop_frame">
                                        <img src="https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=600&q=80" alt="Ảnh cưới Tiệc Cưới Thân Mật" class="mayhen_home_mockup_scallop_img" loading="lazy">
                                    </div>

                                    <!-- Double-Bordered Info Box -->
                                    <div class="mayhen_home_mockup_info_box">
                                        <div class="mayhen_home_mockup_info_title">THÔNG TIN TIỆC CƯỚI</div>
                                        <div class="mayhen_home_mockup_info_datetime">18:00 &bull; CHỦ NHẬT &bull; 24.10.2026</div>
                                        <div class="mayhen_home_mockup_info_venue">TRUNG TÂM HỘI NGHỊ THE WHITE PALACE</div>
                                        <div class="mayhen_home_mockup_info_address">194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM</div>
                                    </div>

                                    <!-- RSVP Action Button -->
                                    <div class="mayhen_home_mockup_rsvp_cta">
                                        <span>✓ Xác Nhận Tham Dự &bull; RSVP</span>
                                    </div>

                                    <!-- Signature Typography -->
                                    <div class="mayhen_home_mockup_signature">
                                        Minh Quân & Ái Như
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Luxury Badge: Live RSVP Count -->
                        <div class="mayhen_home_float_badge mayhen_home_float_badge_rsvp" title="Tính năng quản lý RSVP tự động">
                            <div class="mayhen_home_float_badge_icon">💌</div>
                            <div class="mayhen_home_float_badge_text">
                                <span class="mayhen_home_float_badge_title">186 Khách Mời</span>
                                <span class="mayhen_home_float_badge_subtitle">Đã gửi lời chúc & RSVP ✨</span>
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
