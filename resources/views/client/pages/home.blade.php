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
                    <span class="mayhen_home_hero_badge">Thiệp cưới online gửi người thân quý</span>
                    
                    <h1 class="mayhen_home_hero_title">
                        Thiệp cưới online <span class="mayhen_home_hero_title_highlight">chỉn chu và trang nhã</span>
                    </h1>

                    <p class="mayhen_home_hero_desc">
                        Mây Hẹn giúp bạn tự tạo tấm thiệp cưới online tươm tất để gửi qua Zalo hay tin nhắn. Khách mở thiệp là xem được ảnh cưới, thời gian, bản đồ chỉ đường và có thể bấm báo tham dự để bạn tiện sắp xếp bàn tiệc.
                    </p>

                    <div class="mayhen_home_hero_actions">
                        <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                            Tạo thử thiệp của bạn
                        </a>
                        <a href="{{ route('templates.preview', 'envelope-reveal-luxury') }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg">
                            Xem mẫu trên điện thoại
                        </a>
                    </div>

                    <!-- Trust Stats / Key Benefits -->
                    <div class="mayhen_home_hero_trust">
                        <div class="mayhen_home_hero_trust_item">
                            <span class="mayhen_home_hero_trust_number">Dễ gửi Zalo</span>
                            <span class="mayhen_home_hero_trust_label">Khách mở xem mượt trên mọi máy</span>
                        </div>
                        <div class="mayhen_home_hero_trust_item">
                            <span class="mayhen_home_hero_trust_number">Có bản đồ</span>
                            <span class="mayhen_home_hero_trust_label">Dẫn đường thuận tiện tới sảnh tiệc</span>
                        </div>
                        <div class="mayhen_home_hero_trust_item">
                            <span class="mayhen_home_hero_trust_number">Báo tham dự</span>
                            <span class="mayhen_home_hero_trust_label">Biết trước số khách để tiện xếp bàn</span>
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

    <!-- 2. 3D Coverflow Invitation Showcase (Haute Couture Gallery) -->
    <section class="mayhen_home_showcase_section" id="mayhen_home_showcase_section" aria-label="Không gian triển lãm thiệp cưới 3D">
        <!-- Floating Romantic Petals Canvas -->
        <canvas class="mayhen_home_showcase_canvas_petals" id="mayhen_home_showcase_canvas_petals" aria-hidden="true"></canvas>

        <!-- Haute Couture Visual Vignettes (2 Bên Cánh Xếp Lớp Nghệ Thuật) -->
        <div class="mayhen_home_showcase_vignettes_wrap" aria-hidden="true">
            <!-- Left Vignette: Polaroid, Vintage Wax Envelope & Editorial Quote (Fills Box 1) -->
            <div class="mayhen_home_showcase_vignette is-left">
                <!-- Layer 1: Polaroid Frame -->
                <div class="mayhen_home_showcase_vignette_card is-polaroid">
                    <div class="mayhen_home_showcase_vignette_img_box">
                        <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=400&q=80" alt="Khoảnh khắc hoa cưới" loading="lazy">
                    </div>
                    <div class="mayhen_home_showcase_vignette_polaroid_footer">
                        <span>Forever & Always • 2026</span>
                    </div>
                    <!-- Vintage Gold Clip -->
                    <span class="mayhen_home_showcase_vignette_clip"></span>
                </div>

                <!-- Layer 2: Wax Seal Luxury Envelope -->
                <div class="mayhen_home_showcase_vignette_card is-envelope">
                    <div class="mayhen_home_showcase_vignette_envelope_inner">
                        <span class="mayhen_home_showcase_vignette_postage">MÂY HẸN • VIỆT NAM</span>
                        <div class="mayhen_home_showcase_vignette_seal">
                            <span>囍</span>
                        </div>
                    </div>
                </div>

                <!-- Layer 3: Floating Calligraphy Tag -->
                <div class="mayhen_home_showcase_vignette_tag is-left-tag">
                    <span>✦ Nghệ thuật trao lời hẹn</span>
                </div>

                <!-- Layer 4: Editorial Whisper Quote (Lấp đầy khoảng trống bên trái tiêu đề) -->
                <div class="mayhen_home_showcase_vignette_quote is-left-quote">
                    <div class="mayhen_home_showcase_quote_ornament">
                        <span class="mayhen_home_showcase_quote_leaf">❦</span>
                        <span class="mayhen_home_showcase_quote_line"></span>
                    </div>
                    <p class="mayhen_home_showcase_vignette_quote_text">
                        “Mỗi tấm thiệp là một nốt nhạc dịu êm, dệt nên khúc dạo đầu ngọt ngào cho ngày trăm năm.”
                    </p>
                    <span class="mayhen_home_showcase_vignette_quote_author">MAY HEN • CHUYỆN TRĂM NĂM</span>
                </div>
            </div>

            <!-- Right Vignette: French Arch Photo, Editorial Card & Aesthetic Motto (Fills Box 2) -->
            <div class="mayhen_home_showcase_vignette is-right">
                <!-- Layer 1: French Arch Frame -->
                <div class="mayhen_home_showcase_vignette_card is-arch">
                    <div class="mayhen_home_showcase_vignette_img_box">
                        <img src="https://images.unsplash.com/photo-1606800052052-a08af7148866?auto=format&fit=crop&w=400&q=80" alt="Nhẫn cưới và lời hẹn ước" loading="lazy">
                    </div>
                    <span class="mayhen_home_showcase_vignette_arch_bezel"></span>
                </div>

                <!-- Layer 2: Editorial Monogram Card -->
                <div class="mayhen_home_showcase_vignette_card is-stationery">
                    <div class="mayhen_home_showcase_vignette_stationery_body">
                        <span class="mayhen_home_showcase_vignette_stat_cat">EDITION 2026</span>
                        <span class="mayhen_home_showcase_vignette_stat_title">MayHen Wedding Studio</span>
                        <span class="mayhen_home_showcase_vignette_stat_seal">MH</span>
                    </div>
                </div>

                <!-- Layer 3: Floating Rotating Badge -->
                <div class="mayhen_home_showcase_vignette_badge">
                    <svg viewBox="0 0 100 100" class="mayhen_home_showcase_vignette_spin_text">
                        <path id="mayhen_text_path_circle" d="M 50, 50 m -36, 0 a 36,36 0 1,1 72,0 a 36,36 0 1,1 -72,0" fill="none"/>
                        <text font-size="9.5" fill="currentColor" letter-spacing="2.2">
                            <textPath href="#mayhen_text_path_circle">
                                • QUIET LUXURY • WEDDING STATIONERY
                            </textPath>
                        </text>
                    </svg>
                    <span class="mayhen_home_showcase_vignette_badge_icon">💍</span>
                </div>

                <!-- Layer 4: Couture Motto Plaque (Lấp đầy khoảng trống bên phải tiêu đề) -->
                <div class="mayhen_home_showcase_vignette_quote is-right-quote">
                    <div class="mayhen_home_showcase_quote_ornament">
                        <span class="mayhen_home_showcase_quote_line"></span>
                        <span class="mayhen_home_showcase_quote_leaf">❦</span>
                    </div>
                    <p class="mayhen_home_showcase_vignette_quote_text">
                        “Chuẩn mực thẩm mỹ Quiet Luxury — tối giản, thanh lịch và trường tồn cùng năm tháng.”
                    </p>
                    <span class="mayhen_home_showcase_vignette_quote_author">HAUTE COUTURE • 2026</span>
                </div>
            </div>

            <!-- Bottom Left Flank Panel: Quy Chuẩn Thiết Kế (Lấp đầy khoảng trống góc dưới bên trái) -->
            <div class="mayhen_home_showcase_flank is-bottom-left">
                <div class="mayhen_home_showcase_flank_inner">
                    <div class="mayhen_home_showcase_flank_badge">
                        <span class="mayhen_home_showcase_flank_sparkle">✦</span>
                        <span>QUY CHUẨN THIẾT KẾ</span>
                    </div>
                    <h3 class="mayhen_home_showcase_flank_title">Nghệ Thuật Thiệp Cưới</h3>
                    <div class="mayhen_home_showcase_flank_divider"></div>
                    <ul class="mayhen_home_showcase_flank_list">
                        <li class="mayhen_home_showcase_flank_item">
                            <span class="mayhen_home_showcase_flank_icon">💌</span>
                            <div class="mayhen_home_showcase_flank_text">
                                <strong>Nắp Gấp Tem Sáp 3D</strong>
                                <span>Tương tác mở phong thư như chạm vào chất liệu thật</span>
                            </div>
                        </li>
                        <li class="mayhen_home_showcase_flank_item">
                            <span class="mayhen_home_showcase_flank_icon">🎵</span>
                            <div class="mayhen_home_showcase_flank_text">
                                <strong>Âm Nhạc Du Dương</strong>
                                <span>Giai điệu lễ cưới tự động ngân vang khi mở thiệp</span>
                            </div>
                        </li>
                        <li class="mayhen_home_showcase_flank_item">
                            <span class="mayhen_home_showcase_flank_icon">📱</span>
                            <div class="mayhen_home_showcase_flank_text">
                                <strong>Tối Ưu Từng Màn Hình</strong>
                                <span>Mượt mà, sắc nét trên cả iPhone và Android</span>
                            </div>
                        </li>
                    </ul>
                    <div class="mayhen_home_showcase_flank_footer">
                        <span class="mayhen_home_showcase_flank_seal_text">MAYHEN BESPOKE</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Right Flank Panel: Tiện Ích Dành Cho Khách (Lấp đầy khoảng trống góc dưới bên phải) -->
            <div class="mayhen_home_showcase_flank is-bottom-right">
                <div class="mayhen_home_showcase_flank_inner">
                    <div class="mayhen_home_showcase_flank_badge">
                        <span class="mayhen_home_showcase_flank_sparkle">✦</span>
                        <span>TIỆN ÍCH DÀNH CHO KHÁCH</span>
                    </div>
                    <h3 class="mayhen_home_showcase_flank_title">Gửi Trao Chu Đáo</h3>
                    <div class="mayhen_home_showcase_flank_divider"></div>
                    <ul class="mayhen_home_showcase_flank_list">
                        <li class="mayhen_home_showcase_flank_item">
                            <span class="mayhen_home_showcase_flank_icon">💬</span>
                            <div class="mayhen_home_showcase_flank_text">
                                <strong>Gửi Nhanh Qua Zalo</strong>
                                <span>Một chạm là khách xem được ngay, không cần cài app</span>
                            </div>
                        </li>
                        <li class="mayhen_home_showcase_flank_item">
                            <span class="mayhen_home_showcase_flank_icon">✨</span>
                            <div class="mayhen_home_showcase_flank_text">
                                <strong>Báo Tham Dự (RSVP)</strong>
                                <span>Biết trước số lượng khách để xếp bàn tiệc chu toàn</span>
                            </div>
                        </li>
                        <li class="mayhen_home_showcase_flank_item">
                            <span class="mayhen_home_showcase_flank_icon">📍</span>
                            <div class="mayhen_home_showcase_flank_text">
                                <strong>Bản Đồ Chỉ Đường</strong>
                                <span>Tích hợp Google Maps dẫn đường chính xác tới sảnh tiệc</span>
                            </div>
                        </li>
                    </ul>
                    <div class="mayhen_home_showcase_flank_footer">
                        <span class="mayhen_home_showcase_flank_seal_text">TRẢI NGHIỆM LIỀN MẠCH</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mayhen_client_layout_container">
            <!-- Section Header -->
            <div class="mayhen_home_showcase_header">
                <div class="mayhen_home_showcase_badge_wrap">
                    <span class="mayhen_home_showcase_badge">HAUTE COUTURE COLLECTION</span>
                    <span class="mayhen_home_showcase_badge_glow"></span>
                </div>
                <h2 class="mayhen_home_showcase_title">Không Gian Triển Lãm Thiệp Cưới Nghệ Thuật</h2>
                <p class="mayhen_home_showcase_desc">
                    Trải nghiệm tương tác sống động với phong thư hoàng gia, âm nhạc du dương và thị giác 3D đa chiều được thiết kế riêng cho ngày trọng đại.
                </p>

                <!-- Interactive Category Filter Pills -->
                <div class="mayhen_home_showcase_filter_bar" role="tablist" aria-label="Bộ lọc phong cách thiệp cưới">
                    <button type="button" class="mayhen_home_showcase_filter_pill is-active" data-filter="all" role="tab" aria-selected="true">
                        <span>Tất Cả Mẫu</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_filter_pill" data-filter="luxury" role="tab" aria-selected="false">
                        <span>👑 Hoàng Gia Tem Sáp</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_filter_pill" data-filter="editorial" role="tab" aria-selected="false">
                        <span>📖 Tạp Chí Editorial</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_filter_pill" data-filter="minimal" role="tab" aria-selected="false">
                        <span>✨ Tối Giản Sang Trọng</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_filter_pill" data-filter="floral" role="tab" aria-selected="false">
                        <span>🌿 Vườn Thơ & Hoa Cỏ</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_filter_pill" data-filter="romantic" role="tab" aria-selected="false">
                        <span>🌸 Hồng Khói Tình Thơ</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_filter_pill" data-filter="modern" role="tab" aria-selected="false">
                        <span>🎞️ Cinema Ký Ức</span>
                    </button>
                </div>
            </div>

            <!-- Top Showcase Toolbar (Interactive Audio & Petals Toggle) -->
            <div class="mayhen_home_showcase_toolbar">
                <button type="button" class="mayhen_home_showcase_tool_btn mayhen_home_showcase_audio_toggle" id="mayhen_home_showcase_audio_btn" title="Nghe thử giai điệu lãng mạn">
                    <div class="mayhen_home_showcase_equalizer_bars" aria-hidden="true">
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <span class="mayhen_home_showcase_audio_text">Nhạc Lễ Cưới: A Thousand Years</span>
                </button>

                <button type="button" class="mayhen_home_showcase_tool_btn mayhen_home_showcase_petals_toggle is-active" id="mayhen_home_showcase_petals_btn" title="Bật/Tắt hiệu ứng cánh hoa bay">
                    <span class="mayhen_home_showcase_petals_icon">🌸</span>
                    <span class="mayhen_home_showcase_petals_text">Cánh Hoa Bay: Đang Bật</span>
                </button>
            </div>

            <!-- 3D Coverflow Stage -->
            <div class="mayhen_home_showcase_stage" data-mayhen-showcase="true">
                <!-- Navigation Buttons -->
                <button type="button" class="mayhen_home_showcase_nav_btn mayhen_home_showcase_nav_prev" aria-label="Mẫu thiệp trước đó" data-mayhen-showcase-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <button type="button" class="mayhen_home_showcase_nav_btn mayhen_home_showcase_nav_next" aria-label="Mẫu thiệp tiếp theo" data-mayhen-showcase-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>

                <!-- Viewport & 3D Track -->
                <div class="mayhen_home_showcase_viewport" role="region" aria-roledescription="carousel" aria-label="Bộ sưu tập thiệp cưới 3D">
                    <div class="mayhen_home_showcase_track" data-mayhen-showcase-track>
                        @foreach ($featuredTemplates as $index => $tpl)
                            <article class="mayhen_home_showcase_card {{ $index === 0 ? 'is-active' : '' }}"
                                data-index="{{ $index }}"
                                data-category-slug="{{ $tpl['category'] }}"
                                data-slug="{{ $tpl['slug'] }}"
                                data-name="{{ $tpl['name'] }}"
                                data-category="{{ $tpl['category_label'] }}"
                                data-badge="{{ $tpl['badge'] }}"
                                data-desc="{{ $tpl['description'] }}"
                                data-accent-color="{{ $tpl['accent_color'] }}"
                                data-font-serif="{{ $tpl['font_serif'] }}"
                                data-features="{{ json_encode($tpl['features']) }}"
                                data-preview-url="{{ route('templates.preview', $tpl['slug']) }}"
                                data-create-url="{{ route('templates.show', $tpl['slug']) }}"
                                role="group"
                                aria-roledescription="slide"
                                aria-label="{{ $tpl['name'] }}"
                                tabindex="0">
                                <div class="mayhen_home_showcase_card_inner">
                                    <!-- Metallic Foil Rim -->
                                    <div class="mayhen_home_showcase_card_bezel" aria-hidden="true"></div>

                                    <!-- Top Left Badge -->
                                    <div class="mayhen_home_showcase_card_badge_pin">
                                        <span class="mayhen_home_showcase_card_badge">{{ $tpl['badge'] }}</span>
                                    </div>

                                    <!-- Card Media: Mẫu thiệp live preview + Ảnh đại diện (nếu có) -->
                                    <div class="mayhen_home_showcase_card_media">
                                        <!-- Mẫu thiệp cưới (được nhúng live, cuộn chậm rãi khi hover) -->
                                        <div class="mayhen_home_showcase_card_frame_wrap">
                                            <iframe src="{{ route('templates.preview', $tpl['slug']) }}?embed=1"
                                                    class="mayhen_home_showcase_card_iframe"
                                                    tabindex="-1"
                                                    aria-hidden="true"
                                                    loading="{{ $index < 4 ? 'eager' : 'lazy' }}"></iframe>
                                            <div class="mayhen_home_showcase_card_iframe_shield" aria-hidden="true"></div>
                                        </div>

                                        <!-- Ảnh đại diện: Mặc định hiển thị che lên trên; khi hover sẽ ẩn để lộ mẫu thiệp đang cuộn -->
                                        @if (!empty($tpl['image']))
                                            <img src="{{ $tpl['image'] }}"
                                                 alt="{{ $tpl['name'] }}"
                                                 class="mayhen_home_showcase_card_img"
                                                 loading="{{ $index < 6 ? 'eager' : 'lazy' }}">
                                        @endif
                                    </div>

                                    <!-- Persistent Bottom Name Label Bar -->
                                    <div class="mayhen_home_showcase_card_label_bar">
                                        <h3 class="mayhen_home_showcase_card_label_title">{{ $tpl['name'] }}</h3>
                                    </div>

                                    <!-- Interactive 3D Wax Seal Monogram -->
                                    <div class="mayhen_home_showcase_card_wax_wrap" title="Chạm để kiểm tra niêm phong hoàng gia">
                                        <div class="mayhen_home_showcase_card_wax_stamp">
                                            <span>囍</span>
                                        </div>
                                    </div>

                                    <!-- Rich Slide-Up Hover Overlay -->
                                    <div class="mayhen_home_showcase_card_hover_overlay">
                                        <div class="mayhen_home_showcase_card_hover_body">
                                            <span class="mayhen_home_showcase_card_hover_cat">{{ $tpl['category_label'] }}</span>
                                            <h3 class="mayhen_home_showcase_card_hover_title">{{ $tpl['name'] }}</h3>
                                            <p class="mayhen_home_showcase_card_hover_desc">{{ $tpl['description'] }}</p>

                                            <!-- Tag Badges -->
                                            <div class="mayhen_home_showcase_card_hover_tags">
                                                <span class="mayhen_home_showcase_card_tag">Nhạc Tự Động</span>
                                                <span class="mayhen_home_showcase_card_tag">RSVP Online</span>
                                                <span class="mayhen_home_showcase_card_tag">Bản Đồ</span>
                                            </div>

                                            <!-- Action CTA in Card: Xem Mẫu (mở popup live) & Chọn Mẫu (link tạo thiệp) -->
                                            <div class="mayhen_home_showcase_card_hover_actions">
                                                <button type="button" class="mayhen_home_showcase_btn_peek" data-mayhen-showcase-peek-btn title="Xem trải nghiệm thực tế mẫu thiệp này">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <span>Xem Mẫu</span>
                                                </button>
                                                <a href="{{ route('templates.show', $tpl['slug']) }}" class="mayhen_home_showcase_btn_pick" title="Chọn và bắt đầu tạo thiệp">
                                                    <span>Chọn Mẫu</span>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mayhen_home_showcase_card_reflection" aria-hidden="true"></div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <!-- Stage Floor Shadow & Ambient Light -->
                <div class="mayhen_home_showcase_floor" aria-hidden="true"></div>
            </div>

            <!-- Dynamic Active Info & Curator Control Suite -->
            @if(count($featuredTemplates) > 0)
                <div class="mayhen_home_showcase_info_box" aria-live="polite">
                    <div class="mayhen_home_showcase_curator_card">
                        <!-- Left Details: Category, Title, Description, Font Pairing -->
                        <div class="mayhen_home_showcase_curator_info">
                            <div class="mayhen_home_showcase_curator_head">
                                <span class="mayhen_home_showcase_info_cat" data-mayhen-showcase-info-cat>{{ $featuredTemplates[0]['category_label'] }}</span>
                                <span class="mayhen_home_showcase_font_badge" data-mayhen-showcase-font-badge>
                                    Font: {{ $featuredTemplates[0]['font_serif'] }} × Inter
                                </span>
                            </div>

                            <h3 class="mayhen_home_showcase_info_title" data-mayhen-showcase-info-name>{{ $featuredTemplates[0]['name'] }}</h3>
                            <p class="mayhen_home_showcase_info_desc" data-mayhen-showcase-info-desc>{{ $featuredTemplates[0]['description'] }}</p>

                            <!-- Live Color Palette Switcher Swatches -->
                            <div class="mayhen_home_showcase_palette_row">
                                <span class="mayhen_home_showcase_palette_label">Bảng màu gợi ý:</span>
                                <div class="mayhen_home_showcase_palette_swatches" data-mayhen-showcase-palettes>
                                    <button type="button" class="mayhen_home_showcase_swatch is-active" data-color="#C5A880" style="--swatch-color: #C5A880;" title="Vàng Champagne Hoàng Gia"></button>
                                    <button type="button" class="mayhen_home_showcase_swatch" data-color="#C48B8B" style="--swatch-color: #C48B8B;" title="Hồng Khói Lãng Mạn"></button>
                                    <button type="button" class="mayhen_home_showcase_swatch" data-color="#879883" style="--swatch-color: #879883;" title="Xanh Sage Botanical"></button>
                                    <button type="button" class="mayhen_home_showcase_swatch" data-color="#B89B72" style="--swatch-color: #B89B72;" title="Cát Ấm Minimal"></button>
                                    <button type="button" class="mayhen_home_showcase_swatch" data-color="#1C1917" style="--swatch-color: #1C1917;" title="Đen Editorial Sang Trọng"></button>
                                </div>
                            </div>

                            <!-- 4 Signature Feature Chips -->
                            <div class="mayhen_home_showcase_feature_chips" data-mayhen-showcase-features>
                                @foreach ($featuredTemplates[0]['features'] as $ft)
                                    <span class="mayhen_home_showcase_feature_chip">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span>{{ $ft }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Right Actions: CTAs & Instant Preview Trigger -->
                        <div class="mayhen_home_showcase_curator_actions">
                            <a href="{{ route('templates.show', $featuredTemplates[0]['slug']) }}"
                               class="mayhen_client_button_base mayhen_client_button_primary mayhen_home_showcase_action_create"
                               data-mayhen-showcase-create-btn>
                                <span>Bắt Đầu Tạo Thiệp Miễn Phí</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>

                            <button type="button"
                                    class="mayhen_client_button_base mayhen_client_button_ghost mayhen_home_showcase_action_preview"
                                    data-mayhen-showcase-preview-btn>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span>Xem Mẫu Trực Tiếp</span>
                            </button>

                            <span class="mayhen_home_showcase_curator_footnote">
                                ✨ Không cần thẻ tín dụng &bull; Chỉ thanh toán khi ưng ý
                            </span>
                        </div>
                    </div>

                    <!-- Pagination Dots -->
                    <div class="mayhen_home_showcase_pagination" data-mayhen-showcase-pagination>
                        @foreach ($featuredTemplates as $index => $tpl)
                            <button type="button"
                                    class="mayhen_home_showcase_dot {{ $index === 0 ? 'is-active' : '' }}"
                                    data-index="{{ $index }}"
                                    aria-label="Chuyển đến mẫu {{ $tpl['name'] }}">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Footer Explore More -->
            <div class="mayhen_home_showcase_footer_row">
                <a href="{{ route('templates.index') }}" class="mayhen_home_showcase_view_all_link">
                    <span>Khám phá toàn bộ bộ sưu tập thiệp cưới nghệ thuật Mây Hẹn</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- ========================================================
             LUXURY LIVE DEMO PREVIEW MODAL (ChungDoi-inspired Phone Experience)
             ======================================================== -->
        <div class="mayhen_home_showcase_modal" id="mayhen_home_showcase_modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Xem mẫu thiệp cưới trực tiếp">
            <!-- Dark Frosted Glass Backdrop -->
            <div class="mayhen_home_showcase_modal_backdrop" data-mayhen-modal-close></div>

            <!-- Floating Close Button (Top Right) -->
            <button type="button" class="mayhen_home_showcase_modal_close_floating" data-mayhen-modal-close aria-label="Đóng cửa sổ xem mẫu" title="Đóng (Phím Esc)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>

            <!-- Floating Top Toolbar (Mobile, Desktop, New Tab, Copy Link) -->
            <div class="mayhen_home_showcase_modal_top_bar">
                <div class="mayhen_home_showcase_modal_device_toggles">
                    <button type="button" class="mayhen_home_showcase_modal_device_btn is-active" data-mayhen-device-mode="mobile" title="Chế độ xem trên Điện Thoại">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                        <span>Mobile</span>
                    </button>
                    <button type="button" class="mayhen_home_showcase_modal_device_btn" data-mayhen-device-mode="desktop" title="Chế độ xem trên Máy Tính / Toàn Màn Hình">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="3" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
                        <span>Desktop</span>
                    </button>
                </div>

                <a href="#" target="_blank" rel="noopener noreferrer" class="mayhen_home_showcase_modal_newtab_btn" id="mayhen_home_showcase_modal_newtab_link" title="Mở trang thiệp này trong tab mới">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    <span>Mở tab mới</span>
                </a>

                <button type="button" class="mayhen_home_showcase_modal_tool_action_btn" id="mayhen_home_showcase_modal_copy_btn" title="Sao chép link chia sẻ mẫu thiệp">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                    <span>Sao chép link</span>
                </button>

                <button type="button" class="mayhen_home_showcase_modal_tool_action_btn" id="mayhen_home_showcase_modal_reload_btn" title="Tải lại trang thiệp để xem lại hiệu ứng">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                    <span>Tải lại</span>
                </button>
            </div>

            <!-- Toast Notification -->
            <div class="mayhen_home_showcase_modal_toast" id="mayhen_home_showcase_modal_toast" aria-live="polite">
                <span>✓ Đã sao chép liên kết mẫu thiệp!</span>
            </div>

            <!-- Central Preview Container -->
            <div class="mayhen_home_showcase_modal_container" id="mayhen_home_showcase_modal_container">
                <!-- Smartphone Outer Titanium Frame -->
                <div class="mayhen_home_showcase_modal_phone_bezel" id="mayhen_home_showcase_modal_phone_bezel">
                    <!-- Dynamic Island Camera Sensor -->
                    <div class="mayhen_home_showcase_modal_phone_island" id="mayhen_home_showcase_modal_phone_island">
                        <span class="mayhen_home_showcase_modal_phone_lens"></span>
                        <span class="mayhen_home_showcase_modal_phone_speaker"></span>
                    </div>

                    <!-- Live Scrollable Iframe -->
                    <iframe class="mayhen_home_showcase_modal_iframe" id="mayhen_home_showcase_modal_iframe" title="Trải nghiệm thực tế thiệp cưới" src="about:blank" loading="lazy"></iframe>
                </div>

                <!-- Bottom Sticky Action Bar in Modal -->
                <div class="mayhen_home_showcase_modal_bottom_action">
                    <div class="mayhen_home_showcase_modal_bottom_info">
                        <span class="mayhen_home_showcase_modal_bottom_cat" id="mayhen_home_showcase_modal_bottom_cat">MẪU THIỆP CƯỚI</span>
                        <h4 class="mayhen_home_showcase_modal_bottom_title" id="mayhen_home_showcase_modal_title">Mẫu Thiệp Cưới</h4>
                    </div>
                    <a href="#" class="mayhen_home_showcase_modal_cta_btn" id="mayhen_home_showcase_modal_create_link">
                        <span>Dùng Mẫu Này Ngay</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
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
