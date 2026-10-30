@extends('client.layouts.master')

@section('title', 'Tạo Thiệp Cưới Online Miễn Phí, Đẹp & Sang Trọng | ' . config('app.name'))
@section('meta_description', 'Tự tạo thiệp cưới online đẹp, sang trọng và miễn phí trên ' . config('app.name') . ' (mayhen.vn). Hiệu ứng mở phong thư hoàng gia, nhạc nền lãng mạn, gửi link Zalo, báo hỷ RSVP và mừng cưới QR.')
@section('meta_keywords', 'thiệp cưới online, tạo thiệp cưới online, mẫu thiệp cưới online, thiệp cưới online đẹp, thiệp cưới điện tử, thiệp mời cưới online, tự làm thiệp cưới online, thiệp cưới online miễn phí, thiệp cưới gửi zalo, ' . mb_strtolower(config('app.name')) . ', mayhen.vn')

@push('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@graph": [
        {
            "@type": "WebSite",
            "@id": "{{ url('/') }}/#website",
            "url": "{{ url('/') }}",
            "name": "Mây Hẹn",
            "alternateName": ["MayHen", "MayHen.vn", "Thiệp Cưới Online Mây Hẹn"],
            "description": "Nền tảng tạo thiệp cưới online cao cấp, phong cách Quiet Luxury, hiệu ứng mở phong bì hoàng gia và quản lý RSVP chuyên nghiệp.",
            "inLanguage": "vi-VN",
            "potentialAction": {
                "@type": "SearchAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "{{ route('templates.index') }}?q={search_term_string}"
                },
                "query-input": "required name=search_term_string"
            }
        },
        {
            "@type": "Organization",
            "@id": "{{ url('/') }}/#organization",
            "name": "Mây Hẹn",
            "alternateName": "MayHen.vn",
            "url": "{{ url('/') }}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('storage/images/client/business/mayhen-favicon.png') }}",
                "caption": "Mây Hẹn — Thiệp Cưới Online Cao Cấp"
            },
            "description": "Thương hiệu thiệp cưới online và thiệp cưới điện tử cao cấp dẫn đầu xu hướng thẩm mỹ tinh tế tại Việt Nam.",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Hà Nội",
                "addressCountry": "VN"
            },
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": "+84-969-888-999",
                "contactType": "customer service",
                "availableLanguage": ["vi", "en"]
            }
        },
        {
            "@type": "WebApplication",
            "@id": "{{ url('/') }}/#application",
            "name": "Mây Hẹn — Ứng Dụng Tạo Thiệp Cưới Online",
            "url": "{{ url('/') }}",
            "applicationCategory": "LifestyleApplication",
            "operatingSystem": "All (iOS, Android, Windows, macOS)",
            "browserRequirements": "Requires HTML5 and modern JavaScript",
            "offers": {
                "@type": "Offer",
                "price": "0",
                "priceCurrency": "VND",
                "availability": "https://schema.org/InStock",
                "description": "Trải nghiệm tạo và xem thử thiệp cưới online hoàn toàn miễn phí"
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "4.9",
                "bestRating": "5",
                "ratingCount": "1280"
            }
        },
        {
            "@type": "FAQPage",
            "@id": "{{ url('/') }}/#faq",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "Thiệp mời online là gì và khác gì so với thiệp cưới giấy truyền thống?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Thiệp mời online (hay thiệp cưới điện tử) là một trang web thiệp cưới riêng biệt được thiết kế tối ưu cho màn hình điện thoại. Khác với thiệp giấy thông thường hay ảnh chụp màn hình chỉ xem được chữ tĩnh, thiệp mời online tích hợp trọn vẹn nhạc nền, album ảnh cưới, bản đồ chỉ đường Google Maps tới tận sảnh tiệc, đồng hồ đếm ngược ngày cưới và nút báo có tham dự hay không. Bạn có thể gửi link thiệp mời online qua Zalo, Messenger hoặc SMS cho bạn bè, đồng nghiệp ở xa rất nhanh chóng và chỉn chu, đồng thời dùng song song với thiệp giấy để việc mời cưới trọn vẹn nhất."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Tôi không rành công nghệ và không biết thiết kế thì có tự tạo thiệp mời online được không?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Hoàn toàn được. Toàn bộ mẫu thiệp cưới đẹp trên Mây Hẹn đã được dàn trang, phối màu và căn chỉnh tỷ lệ chuẩn xác. Bạn chỉ cần chọn mẫu mình thích, gõ tên cô dâu chú rể, ngày giờ tổ chức, địa chỉ sảnh cưới và tải ảnh kỷ niệm lên. Giao diện trực quan cho phép bạn thao tác dễ dàng trên cả điện thoại lẫn máy tính và hoàn thành tấm thiệp trong khoảng 5 đến 10 phút mà không cần bất kỳ kiến thức đồ họa nào."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Tôi có thể xem thử thiệp cưới trên điện thoại trước khi thanh toán không?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Có. Bạn được tự do chọn mẫu, nhập đầy đủ thông tin, tải ảnh và trải nghiệm xem thử thực tế trên điện thoại hoàn toàn miễn phí. Bạn có thể kiểm tra hiệu ứng mở phong thư, nghe thử nhạc nền, kiểm tra bản đồ chỉ đường và duyệt lại từng chi tiết. Khi nào bạn thực sự ưng ý với tác phẩm của mình và muốn nhận đường link chính thức để gửi cho khách mời thì mới tiến hành thanh toán kích hoạt."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Khách mời nhận link qua Zalo có cần tải app hay tạo tài khoản để xem thiệp không?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Không. Khi bạn gửi đường link thiệp qua Zalo, Messenger hay tin nhắn thông thường, khách mời chỉ cần chạm vào link là thiệp sẽ mở tức thì trên trình duyệt mặc định của máy (tương thích mượt mà với cả iPhone, Samsung, Xiaomi hay máy tính). Khách không cần cài thêm bất kỳ ứng dụng nào và không phải đăng ký tài khoản."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Tính năng xác nhận tham dự (RSVP) trên thiệp hoạt động như thế nào?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Cuối mỗi tấm thiệp mời online có sẵn một biểu mẫu phản hồi ngắn gọn. Khách mời có thể nhanh chóng chọn: chắc chắn tham dự, rất tiếc không thể đến hoặc chưa chắc chắn, kèm theo số lượng người đi cùng và lời chúc gửi tới cô dâu chú rể. Tất cả câu trả lời sẽ được tổng hợp tự động vào danh sách quản lý của bạn theo thời gian thực, giúp bạn nắm rõ số lượng khách để chốt số bàn tiệc chính xác, hạn chế tình trạng thừa hoặc thiếu cỗ."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Sau khi đã gửi link cho khách mời, tôi có thể chỉnh sửa lại thông tin trên thiệp không?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Có. Nếu cần thay đổi ảnh cưới, cập nhật lại giờ đón khách hay đính kèm thêm ghi chú về trang phục (dress code), bạn chỉ cần đăng nhập và cập nhật nội dung trong trang quản trị. Thông tin mới sẽ hiển thị ngay lập tức trên đường link mà không làm thay đổi địa chỉ link bạn đã gửi trước đó cho khách."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Mã QR mừng cưới trên thiệp có bị thu phí hoa hồng hay qua trung gian không?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Hoàn toàn không. Mã QR mừng cưới trên thiệp cưới Mây Hẹn là mã VietQR cá nhân dẫn thẳng về tài khoản ngân hàng chính chủ của cô dâu hoặc chú rể. Tiền mừng cưới từ khách mời sẽ được chuyển trực tiếp 100% vào tài khoản của bạn tức thì, Mây Hẹn không giữ tiền và không thu bất kỳ khoản phí hoa hồng nào."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Thiệp cưới online của tôi sẽ duy trì hoạt động trong bao lâu sau ngày cưới?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Sau khi kích hoạt, thiệp của bạn sẽ duy trì hoạt động liên tục trong suốt thời gian chuẩn bị đám cưới và tiếp tục lưu trữ tối thiểu 6 tháng sau ngày cưới. Bạn và bạn bè, người thân vẫn có thể mở lại đường link bất cứ lúc nào để xem lại những khung hình kỷ niệm và những lời chúc thân thương được gửi gắm trong ngày cưới."
                    }
                }
            ]
        }
    ]
}
</script>
@endpush

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
                        <a href="{{ route('templates.preview', $featuredTemplates->first()?->slug ?? 'thuoc-phim-tinh-yeu') }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg">
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
                <h2 class="mayhen_home_showcase_title">Mẫu Thiệp Cưới Online Cao Cấp &bull; Trải Nghiệm Thực Tế</h2>
                <p class="mayhen_home_showcase_desc">
                    Khám phá và xem thử trực tiếp các mẫu thiệp cưới online đẹp chuẩn Quiet Luxury với hiệu ứng mở phong thư hoàng gia, âm nhạc du dương và tương tác 3D sống động trên điện thoại.
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

                                    <!-- Card Media: Ultra HD WebP Artwork Poster -->
                                    <div class="mayhen_home_showcase_card_media">
                                        @if (!empty($tpl['image']))
                                            <img src="{{ $tpl['image'] }}"
                                                 alt="{{ $tpl['name'] }}"
                                                 class="mayhen_home_showcase_card_img"
                                                 loading="{{ $index < 2 ? 'eager' : 'lazy' }}"
                                                 decoding="async">
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
             LUXURY LIVE DEMO PREVIEW MODAL
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

            <!-- Floating Top Toolbar (Global Preview Topbar) -->
            <div class="mayhen_home_showcase_modal_top_bar">
                <div class="mayhen_home_showcase_modal_badge_info">
                    <span class="mayhen_home_showcase_modal_badge_dot"></span>
                    <span class="mayhen_home_showcase_modal_badge_label">Xem Thử</span>
                    <strong class="mayhen_home_showcase_modal_header_title" id="mayhen_home_showcase_modal_header_title">Mẫu Thiệp Cưới</strong>
                </div>

                <div class="mayhen_home_showcase_modal_top_bar_divider" aria-hidden="true"></div>

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

                <div class="mayhen_home_showcase_modal_top_bar_divider" aria-hidden="true"></div>

                <div class="mayhen_home_showcase_modal_top_actions">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="mayhen_home_showcase_modal_newtab_btn" id="mayhen_home_showcase_modal_newtab_link" title="Mở trang thiệp này trong tab mới">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        <span>Mở tab mới</span>
                    </a>

                    <button type="button" class="mayhen_home_showcase_modal_tool_action_btn" id="mayhen_home_showcase_modal_reload_btn" title="Tải lại trang thiệp để xem lại hiệu ứng mở thiệp">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                        <span>Tải lại</span>
                    </button>

                    <button type="button" class="mayhen_home_showcase_modal_tool_action_btn" id="mayhen_home_showcase_modal_copy_btn" title="Sao chép link chia sẻ mẫu thiệp">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                        <span>Sao chép</span>
                    </button>
                </div>
            </div>

            <!-- Toast Notification -->
            <div class="mayhen_home_showcase_modal_toast" id="mayhen_home_showcase_modal_toast" aria-live="polite">
                <span>✓ Đã sao chép liên kết mẫu thiệp!</span>
            </div>

            <!-- Central Preview Container -->
            <div class="mayhen_home_showcase_modal_container" id="mayhen_home_showcase_modal_container">
                <!-- Smartphone Outer Titanium Frame -->
                <div class="mayhen_home_showcase_modal_phone_bezel" id="mayhen_home_showcase_modal_phone_bezel">
                    <!-- Screen Container with Perfect Hardware Corner Masking -->
                    <div class="mayhen_home_showcase_modal_phone_screen" id="mayhen_home_showcase_modal_phone_screen">
                        <!-- Dynamic Island Camera Sensor -->
                        <div class="mayhen_home_showcase_modal_phone_island" id="mayhen_home_showcase_modal_phone_island">
                            <span class="mayhen_home_showcase_modal_phone_lens"></span>
                            <span class="mayhen_home_showcase_modal_phone_speaker"></span>
                        </div>

                        <!-- Live Scrollable Iframe -->
                        <iframe class="mayhen_home_showcase_modal_iframe" id="mayhen_home_showcase_modal_iframe" title="Trải nghiệm thực tế thiệp cưới" src="about:blank" loading="lazy"></iframe>
                    </div>
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

    <!-- 3. Highlight Features Section (Tính Năng Nổi Bật — Vì Sao Nên Chọn Mây Hẹn?) -->
    <section class="mayhen_home_features_section" id="tinh-nang" aria-labelledby="mayhen-features-heading">
        <div class="mayhen_client_layout_container">
            <!-- Section Header -->
            <div class="mayhen_home_features_header">
                <span class="mayhen_home_features_badge">Giá Trị Khác Biệt &bull; Vì Sao Chọn Mây Hẹn</span>
                <h2 class="mayhen_home_features_title" id="mayhen-features-heading">
                    Những Tiện Ích Đột Phá Cho Tấm <span class="mayhen_home_features_title_serif">Thiệp Cưới Online</span>
                </h2>
                <p class="mayhen_home_features_desc">
                    Không chỉ đơn thuần là trang thiệp mời online thông báo ngày cưới, Mây Hẹn kết hợp nghệ thuật thẩm mỹ Quiet Luxury cùng các tiện ích thiết thực giúp bạn gửi trao trọn vẹn sự trang trọng tới từng vị khách quý qua tấm thiệp cưới online chỉn chu.
                </p>
            </div>

            <!-- Asymmetric Bento Grid -->
            <div class="mayhen_home_features_bento">
                <!-- Feature 01: Hero Bento Card (7 cols on desktop) -->
                <article class="mayhen_home_features_card mayhen_home_features_card_hero" tabindex="0" aria-label="Tính năng: Mở nắp phong bì dấu sáp hoàng gia và nhạc nền lãng mạn">
                    <div class="mayhen_home_features_card_inner">
                        <div class="mayhen_home_features_card_body">
                            <span class="mayhen_home_features_card_tag">Trải Nghiệm Cảm Xúc</span>
                            <h3 class="mayhen_home_features_card_title">Mở Nắp Phong Bì Dấu Sáp &amp; Nhạc Nền Du Dương</h3>
                            <p class="mayhen_home_features_card_desc">
                                Khách mời chạm nhẹ để bóc con dấu sáp dập nổi chữ Hỷ 囍, mở ra lá thư cưới trang nhã kèm giai điệu tình ca lãng mạn và đồng hồ đếm ngược thời gian thực tới ngày hạnh phúc.
                            </p>
                            <div class="mayhen_home_features_card_perk">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                <span>Tối ưu mở mượt trên mọi điện thoại &bull; Nhạc nền tùy chỉnh</span>
                            </div>
                        </div>

                        <!-- Mini Visual Mockup 01: Envelope + Wax Seal + Music Equalizer + Countdown -->
                        <div class="mayhen_home_features_visual mayhen_home_features_visual_hero" aria-hidden="true">
                            <div class="mayhen_home_features_envelope_mockup">
                                <div class="mayhen_home_features_envelope_back">
                                    <div class="mayhen_home_features_envelope_card_peek">
                                        <span class="mayhen_home_features_peek_label">THIỆP MỜI TRỌNG THỂ</span>
                                        <span class="mayhen_home_features_peek_names">Minh Quân &bull; Ái Như</span>
                                        <!-- Mini Countdown in peek card -->
                                        <div class="mayhen_home_features_peek_countdown">
                                            <div class="mayhen_home_features_countdown_item">
                                                <strong>12</strong><span>Ngày</span>
                                            </div>
                                            <div class="mayhen_home_features_countdown_item">
                                                <strong>08</strong><span>Giờ</span>
                                            </div>
                                            <div class="mayhen_home_features_countdown_item">
                                                <strong>45</strong><span>Phút</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mayhen_home_features_envelope_flap"></div>
                                    <div class="mayhen_home_features_envelope_seal" title="Chạm nhẹ để kiểm tra con dấu">囍</div>
                                </div>
                                <!-- Floating Audio Pill -->
                                <div class="mayhen_home_features_music_pill">
                                    <span class="mayhen_home_features_music_disc">🎵</span>
                                    <span class="mayhen_home_features_music_title">A Thousand Years</span>
                                    <div class="mayhen_home_features_equalizer">
                                        <span></span><span></span><span></span><span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Feature 02: Card (5 cols on desktop) -->
                <article class="mayhen_home_features_card mayhen_home_features_card_zalo" tabindex="0" aria-label="Tính năng: Cá nhân hóa từng khách mời và 1-chạm gửi Zalo">
                    <div class="mayhen_home_features_card_body">
                        <span class="mayhen_home_features_card_tag">Chỉn Chu Từng Khách</span>
                        <h3 class="mayhen_home_features_card_title">Cá Nhân Hóa Lời Mời &amp; 1-Chạm Gửi Zalo</h3>
                        <p class="mayhen_home_features_card_desc">
                            Hệ thống tạo sẵn link riêng theo đúng danh xưng (Kính mời, Thân mời, Thương mời). Nút 1-chạm sao chép lời mời Zalo kèm ảnh đại diện cưới chuẩn Open Graph hiển thị trang trọng.
                        </p>
                    </div>

                    <!-- Mini Visual Mockup 02: Zalo Message Bubble & Guest Card -->
                    <div class="mayhen_home_features_visual mayhen_home_features_visual_zalo" aria-hidden="true">
                        <div class="mayhen_home_features_zalo_card">
                            <div class="mayhen_home_features_salutation_chip">
                                <span class="mayhen_home_features_salutation_dot"></span>
                                <span>Danh xưng: <strong>Kính mời</strong></span>
                            </div>
                            <div class="mayhen_home_features_zalo_bubble">
                                <div class="mayhen_home_features_zalo_sender">
                                    <span class="mayhen_home_features_zalo_badge">Zalo Message</span>
                                    <span class="mayhen_home_features_zalo_time">Vừa xong</span>
                                </div>
                                <p class="mayhen_home_features_zalo_text">
                                    Kính mời <strong>Anh Hoàng &amp; Bạn gái</strong> tới chung vui cùng Minh Quân &amp; Ái Như vào ngày 24.10 tại The White Palace 💌
                                </p>
                                <div class="mayhen_home_features_zalo_link_preview">
                                    <div class="mayhen_home_features_zalo_link_thumb">🕊️</div>
                                    <div class="mayhen_home_features_zalo_link_meta">
                                        <span class="mayhen_home_features_zalo_link_title">Thiệp Cưới Minh Quân &amp; Ái Như</span>
                                        <span class="mayhen_home_features_zalo_link_url">mayhen.vn/thiep/quan-nhu</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_features_card_perk">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Khách mở không cần app &bull; Sao chép tin nhắn trong 1 giây</span>
                    </div>
                </article>

                <!-- Feature 03: Card (4 cols on desktop) -->
                <article class="mayhen_home_features_card mayhen_home_features_card_rsvp" tabindex="0" aria-label="Tính năng: Báo hỷ RSVP và quản lý bàn tiệc tự động">
                    <div class="mayhen_home_features_card_body">
                        <span class="mayhen_home_features_card_tag">Chốt Cỗ Chu Đáo</span>
                        <h3 class="mayhen_home_features_card_title">Báo Hỷ RSVP &amp; Quản Lý Bàn Tiệc</h3>
                        <p class="mayhen_home_features_card_desc">
                            Khách mời dễ dàng báo trước có tham dự hay không, số người đi cùng và gửi lời chúc phúc. Dữ liệu cập nhật về bảng quản lý giúp bạn xếp chỗ chuẩn xác, tránh lãng phí cỗ cưới.
                        </p>
                    </div>

                    <!-- Mini Visual Mockup 03: RSVP Interactive Pill & Live Stats -->
                    <div class="mayhen_home_features_visual mayhen_home_features_visual_rsvp" aria-hidden="true">
                        <div class="mayhen_home_features_rsvp_mockup">
                            <div class="mayhen_home_features_rsvp_toggle">
                                <span class="mayhen_home_features_rsvp_option is-selected">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span>Chắc chắn dự (2 người)</span>
                                </span>
                            </div>
                            <div class="mayhen_home_features_rsvp_stats_row">
                                <div class="mayhen_home_features_rsvp_stat_box">
                                    <span class="mayhen_home_features_rsvp_stat_val">128</span>
                                    <span class="mayhen_home_features_rsvp_stat_lbl">Khách tham dự</span>
                                </div>
                                <div class="mayhen_home_features_rsvp_stat_box">
                                    <span class="mayhen_home_features_rsvp_stat_val">24</span>
                                    <span class="mayhen_home_features_rsvp_stat_lbl">Bàn tiệc đã chốt</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_features_card_perk">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Cập nhật thời gian thực &bull; Đính kèm lời chúc phúc</span>
                    </div>
                </article>

                <!-- Feature 04: Card (4 cols on desktop) -->
                <article class="mayhen_home_features_card mayhen_home_features_card_map" tabindex="0" aria-label="Tính năng: Tích hợp Google Maps dẫn đường 1-chạm tới sảnh cưới">
                    <div class="mayhen_home_features_card_body">
                        <span class="mayhen_home_features_card_tag">Tránh Lạc Đường</span>
                        <h3 class="mayhen_home_features_card_title">Bản Đồ Google Maps Dẫn Đường 1-Chạm</h3>
                        <p class="mayhen_home_features_card_desc">
                            Gắn vị trí sảnh cưới chính xác trên bản đồ. Khách bấm nút dẫn đường là Google Maps tự động mở lộ trình di chuyển, giúp người thân ở xa hay người lớn tuổi tới đúng giờ lành.
                        </p>
                    </div>

                    <!-- Mini Visual Mockup 04: Map Pin & Directions Card -->
                    <div class="mayhen_home_features_visual mayhen_home_features_visual_map" aria-hidden="true">
                        <div class="mayhen_home_features_map_mockup">
                            <div class="mayhen_home_features_map_pin_badge">
                                <span class="mayhen_home_features_map_pin_icon">📍</span>
                                <div class="mayhen_home_features_map_pin_text">
                                    <strong>The White Palace</strong>
                                    <span>Sảnh Grand Ballroom</span>
                                </div>
                            </div>
                            <div class="mayhen_home_features_map_cta_pill">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                                <span>Chỉ Đường Bằng Google Maps</span>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_features_card_perk">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Định vị chính xác sảnh tiệc &bull; Mở nhanh trên điện thoại</span>
                    </div>
                </article>

                <!-- Feature 05: Card (4 cols on desktop) -->
                <article class="mayhen_home_features_card mayhen_home_features_card_qr" tabindex="0" aria-label="Tính năng: Hộp mừng cưới VietQR chính chủ 0% chiết khấu">
                    <div class="mayhen_home_features_card_body">
                        <span class="mayhen_home_features_card_tag">Minh Bạch Tuyệt Đối</span>
                        <h3 class="mayhen_home_features_card_title">Hộp Mừng Cưới VietQR Chính Chủ</h3>
                        <p class="mayhen_home_features_card_desc">
                            Tích hợp mã VietQR ngân hàng chính chủ của cô dâu chú rể. Khách ở xa gửi tiền mừng trực tiếp vào tài khoản của bạn, tuyệt đối không qua trung gian và không chiết khấu.
                        </p>
                    </div>

                    <!-- Mini Visual Mockup 05: Bank VietQR Card -->
                    <div class="mayhen_home_features_visual mayhen_home_features_visual_qr" aria-hidden="true">
                        <div class="mayhen_home_features_qr_mockup">
                            <div class="mayhen_home_features_qr_head">
                                <span class="mayhen_home_features_qr_bank">VietinBank &bull; MB Bank</span>
                                <span class="mayhen_home_features_qr_direct">100% Trực Tiếp</span>
                            </div>
                            <div class="mayhen_home_features_qr_frame">
                                <div class="mayhen_home_features_qr_pixels">
                                    <span></span><span></span><span></span>
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="mayhen_home_features_qr_center">囍</div>
                            </div>
                            <div class="mayhen_home_features_qr_owner">
                                <span>Chủ TK: NGUYEN MINH QUAN</span>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_features_card_perk">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Chuẩn VietQR toàn quốc &bull; 0% phí hoa hồng</span>
                    </div>
                </article>
            </div>

            <!-- Bottom Action & Reassurance Bar -->
            <div class="mayhen_home_features_bottom_bar">
                <div class="mayhen_home_features_bottom_content">
                    <span class="mayhen_home_features_bottom_icon" aria-hidden="true">✨</span>
                    <p class="mayhen_home_features_bottom_text">
                        Mọi tính năng đều được tối ưu tải trang dưới 1 giây trên điện thoại, không chèn quảng cáo và cho phép bạn xem thử miễn phí trước khi quyết định.
                    </p>
                </div>
                <div class="mayhen_home_features_bottom_actions">
                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_md">
                        <span>Tạo Thử Thiệp Cưới Của Bạn</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('services') }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_md">
                        <span>Khám Phá Dịch Vụ</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Wedding Invitation Gallery Section (Bộ Sưu Tập Hình Ảnh Mẫu Thiệp Cưới) -->
    <section class="mayhen_home_gallery_section" id="bo-suu-tap" aria-labelledby="mayhen-gallery-heading">
        <div class="mayhen_client_layout_container">
            <!-- Section Header -->
            <div class="mayhen_home_gallery_header">
                <span class="mayhen_home_gallery_badge">Phòng Trưng Bày Nghệ Thuật &bull; Gallery Thiệp Cưới</span>
                <h2 class="mayhen_home_gallery_title" id="mayhen-gallery-heading">
                    Bộ Sưu Tập Hình Ảnh <span class="mayhen_home_gallery_title_serif">Mẫu Thiệp Cưới Online</span> Đẹp
                </h2>
                <p class="mayhen_home_gallery_desc">
                    Khám phá kho ảnh đại diện thực tế từ bộ sưu tập mẫu thiệp cưới online đẹp của Mây Hẹn. Chạm vào bất kỳ mẫu nào để mở trình xem Lightbox toàn màn hình, phóng to chi tiết và trải nghiệm xem thử trực tiếp trên điện thoại.
                </p>
            </div>

            <!-- Filter & Search Toolbar Suite -->
            <div class="mayhen_home_gallery_toolbar_wrapper">
                <!-- Category Filter Tabs -->
                <nav class="mayhen_home_gallery_filter_tabs" role="tablist" aria-label="Lọc mẫu thiệp theo phong cách">
                    <button type="button" class="mayhen_home_gallery_filter_btn is-active" data-filter="all" role="tab" aria-selected="true">
                        <span>Tất Cả ({{ count($galleryTemplates) }})</span>
                    </button>
                    <button type="button" class="mayhen_home_gallery_filter_btn" data-filter="luxury" role="tab" aria-selected="false">
                        <span>Hoàng Gia &amp; Sáp</span>
                    </button>
                    <button type="button" class="mayhen_home_gallery_filter_btn" data-filter="editorial" role="tab" aria-selected="false">
                        <span>Tạp Chí Vogue</span>
                    </button>
                    <button type="button" class="mayhen_home_gallery_filter_btn" data-filter="minimal" role="tab" aria-selected="false">
                        <span>Tối Giản Tinh Tế</span>
                    </button>
                    <button type="button" class="mayhen_home_gallery_filter_btn" data-filter="floral" role="tab" aria-selected="false">
                        <span>Hoa Cỏ Thơ Mộng</span>
                    </button>
                    <button type="button" class="mayhen_home_gallery_filter_btn" data-filter="romantic" role="tab" aria-selected="false">
                        <span>Hồng Khói Lãng Mạn</span>
                    </button>
                    <button type="button" class="mayhen_home_gallery_filter_btn" data-filter="modern" role="tab" aria-selected="false">
                        <span>Điện Ảnh Ký Ức</span>
                    </button>
                </nav>

                <!-- Search Input & View Mode Toggles -->
                <div class="mayhen_home_gallery_actions_row">
                    <div class="mayhen_home_gallery_search_box">
                        <svg class="mayhen_home_gallery_search_icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text"
                               class="mayhen_home_gallery_search_input"
                               id="mayhen_home_gallery_search"
                               placeholder="Tìm tên mẫu thiệp cưới..."
                               aria-label="Tìm kiếm mẫu thiệp trong bộ sưu tập">
                        <button type="button" class="mayhen_home_gallery_search_clear" id="mayhen_home_gallery_search_clear" aria-label="Xóa từ khóa tìm kiếm" style="display:none;">
                            &times;
                        </button>
                    </div>

                    <div class="mayhen_home_gallery_view_controls">
                        <span class="mayhen_home_gallery_counter_badge" id="mayhen_home_gallery_count">
                            {{ count($galleryTemplates) }} mẫu thiệp
                        </span>
                        <div class="mayhen_home_gallery_mode_toggles">
                            <button type="button" class="mayhen_home_gallery_mode_btn is-active" data-gallery-mode="masonry" title="Bố cục Masonry nghệ thuật" aria-label="Chế độ xem Masonry">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="8" height="12" x="3" y="3" rx="1"/><rect width="8" height="6" x="13" y="3" rx="1"/><rect width="8" height="6" x="3" y="17" rx="1"/><rect width="8" height="12" x="13" y="11" rx="1"/></svg>
                            </button>
                            <button type="button" class="mayhen_home_gallery_mode_btn" data-gallery-mode="grid" title="Lưới ảnh đều đặn" aria-label="Chế độ xem lưới đều">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Grid Container -->
            <div class="mayhen_home_gallery_grid mayhen_mode_masonry" id="mayhen_home_gallery_grid">
                @forelse ($galleryTemplates as $index => $tpl)
                    <article class="mayhen_home_gallery_item {{ $tpl->is_featured ? 'is-featured' : '' }}"
                             data-gallery-index="{{ $index }}"
                             data-id="{{ $tpl->id }}"
                             data-name="{{ $tpl->name }}"
                             data-slug="{{ $tpl->slug }}"
                             data-category="{{ $tpl->category }}"
                             data-category-label="{{ $tpl->category_label }}"
                             data-image="{{ $tpl->thumbnail_url }}"
                             data-detail-url="{{ route('templates.show', $tpl->slug) }}"
                             data-preview-url="{{ route('templates.preview', $tpl->slug) }}"
                             data-create-url="{{ route('invitation.create', ['template' => $tpl->slug]) }}"
                             tabindex="0"
                             role="button"
                             aria-haspopup="dialog"
                             aria-label="Xem ảnh lớn mẫu thiệp cưới {{ $tpl->name }}">
                        <div class="mayhen_home_gallery_card">
                            <!-- Image Wrap with natural aspect ratio -->
                            <div class="mayhen_home_gallery_thumb_wrap">
                                <img src="{{ $tpl->thumbnail_url }}"
                                     alt="Mẫu thiệp cưới online {{ $tpl->name }} phong cách {{ $tpl->category_label }} — Mây Hẹn"
                                     class="mayhen_home_gallery_img"
                                     loading="{{ $index < 6 ? 'eager' : 'lazy' }}"
                                     decoding="async"
                                     width="600"
                                     height="800">

                                <!-- Hover Overlay (Desktop) -->
                                <div class="mayhen_home_gallery_overlay" aria-hidden="true">
                                    <div class="mayhen_home_gallery_overlay_top">
                                        <span class="mayhen_home_gallery_cat_pill">{{ $tpl->category_label }}</span>
                                        <span class="mayhen_home_gallery_zoom_hint" title="Bấm để mở Lightbox phóng to">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                        </span>
                                    </div>
                                    <div class="mayhen_home_gallery_overlay_bottom">
                                        <h3 class="mayhen_home_gallery_item_title">{{ $tpl->name }}</h3>
                                        <div class="mayhen_home_gallery_overlay_actions">
                                            <span class="mayhen_home_gallery_btn_lightbox">
                                                <span>Xem Ảnh Lớn</span>
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                                            </span>
                                            <a href="{{ route('templates.show', $tpl->slug) }}" class="mayhen_home_gallery_btn_detail" onclick="event.stopPropagation();" title="Xem chi tiết mẫu thiệp này">
                                                <span>Chi Tiết Mẫu</span>
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Card Info (Mobile friendly, always visible) -->
                            <div class="mayhen_home_gallery_card_footer">
                                <div class="mayhen_home_gallery_card_meta">
                                    <span class="mayhen_home_gallery_card_cat">{{ $tpl->category_label }}</span>
                                    <h3 class="mayhen_home_gallery_card_name">{{ $tpl->name }}</h3>
                                </div>
                                <button type="button" class="mayhen_home_gallery_card_btn_open" aria-label="Xem ảnh lớn mẫu {{ $tpl->name }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="mayhen_home_gallery_empty">
                        <p>Hiện chưa có mẫu thiệp trong danh mục này.</p>
                    </div>
                @endforelse
            </div>

            <!-- No results state for search/filter -->
            <div class="mayhen_home_gallery_no_results" id="mayhen_home_gallery_no_results" style="display:none;">
                <span class="mayhen_home_gallery_no_results_icon">🔍</span>
                <p class="mayhen_home_gallery_no_results_title">Không tìm thấy mẫu thiệp cưới phù hợp</p>
                <p class="mayhen_home_gallery_no_results_desc">Vui lòng thử tìm kiếm với từ khóa khác hoặc chọn danh mục "Tất Cả".</p>
                <button type="button" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm" id="mayhen_home_gallery_reset_filter_btn">
                    Xem Lại Tất Cả Mẫu Thiệp
                </button>
            </div>

            <!-- Bottom Expand / Explore More Bar -->
            <div class="mayhen_home_gallery_bottom_bar">
                <div class="mayhen_home_gallery_bottom_note">
                    <span class="mayhen_home_gallery_bottom_dot"></span>
                    <span>Toàn bộ mẫu thiệp đều sẵn sàng tạo thử miễn phí và hỗ trợ thanh toán VietQR tự động.</span>
                </div>
                <div class="mayhen_home_gallery_bottom_actions">
                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_md">
                        <span>Khám Phá Toàn Bộ Thư Viện Thiệp</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- ========================================================
             FULLSCREEN LIGHTBOX SUITE (Quiet Luxury Image Viewer)
             ======================================================== -->
        <div class="mayhen_home_gallery_lightbox"
             id="mayhen_home_gallery_lightbox"
             role="dialog"
             aria-modal="true"
             aria-label="Trình xem ảnh mẫu thiệp cưới toàn màn hình"
             aria-hidden="true">
            <!-- Frosted Glass Backdrop -->
            <div class="mayhen_home_gallery_lightbox_backdrop" data-lightbox-action="close"></div>

            <!-- Top Header Suite -->
            <header class="mayhen_home_gallery_lightbox_header">
                <!-- Counter & Category Badge -->
                <div class="mayhen_home_gallery_lightbox_counter_wrap">
                    <span class="mayhen_home_gallery_lightbox_counter_box">
                        <strong data-lightbox-text="current">1</strong>
                        <span>/</span>
                        <span data-lightbox-text="total">{{ count($galleryTemplates) }}</span>
                    </span>
                    <span class="mayhen_home_gallery_lightbox_cat_badge" data-lightbox-text="category">Hoàng Gia</span>
                </div>

                <!-- Center Title -->
                <div class="mayhen_home_gallery_lightbox_title_wrap">
                    <h3 class="mayhen_home_gallery_lightbox_title" data-lightbox-text="title">Tên Mẫu Thiệp</h3>
                </div>

                <!-- Toolbar Actions Suite -->
                <div class="mayhen_home_gallery_lightbox_toolbar">
                    <!-- Zoom Controls -->
                    <div class="mayhen_home_gallery_lightbox_tool_group" role="group" aria-label="Điều khiển phóng to thu nhỏ">
                        <button type="button" class="mayhen_home_gallery_lightbox_tool_btn" data-lightbox-action="zoom-out" title="Thu nhỏ (-)" aria-label="Thu nhỏ">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </button>
                        <span class="mayhen_home_gallery_lightbox_zoom_indicator" data-lightbox-text="zoom">100%</span>
                        <button type="button" class="mayhen_home_gallery_lightbox_tool_btn" data-lightbox-action="zoom-in" title="Phóng to (+)" aria-label="Phóng to">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </button>
                        <button type="button" class="mayhen_home_gallery_lightbox_tool_btn" data-lightbox-action="zoom-reset" title="Đặt lại kích thước gốc" aria-label="Kích thước gốc">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                        </button>
                    </div>

                    <!-- Fullscreen Toggle -->
                    <button type="button" class="mayhen_home_gallery_lightbox_tool_btn" data-lightbox-action="fullscreen" title="Chế độ toàn màn hình" aria-label="Toàn màn hình">
                        <svg class="mayhen_icon_fullscreen_enter" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                        <svg class="mayhen_icon_fullscreen_exit" style="display:none;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/></svg>
                    </button>

                    <!-- Share / Copy Link -->
                    <button type="button" class="mayhen_home_gallery_lightbox_tool_btn" data-lightbox-action="share" title="Chia sẻ mẫu thiệp này" aria-label="Chia sẻ">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                    </button>

                    <!-- Download Photo Direct -->
                    <button type="button" class="mayhen_home_gallery_lightbox_tool_btn" data-lightbox-action="download" title="Tải ảnh mẫu thiệp này xuống máy" aria-label="Tải ảnh xuống máy">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </button>

                    <!-- Toggle Info Panel -->
                    <button type="button" class="mayhen_home_gallery_lightbox_tool_btn is-active" data-lightbox-action="toggle-info" title="Ẩn/Hiện thông tin mẫu" aria-label="Ẩn hoặc hiện thông tin">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </button>

                    <!-- Close Button -->
                    <button type="button" class="mayhen_home_gallery_lightbox_close_btn" data-lightbox-action="close" title="Đóng cửa sổ xem ảnh (Phím Esc)" aria-label="Đóng cửa sổ">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </header>

            <!-- Main Stage Viewport -->
            <div class="mayhen_home_gallery_lightbox_stage">
                <!-- Navigation Prev -->
                <button type="button" class="mayhen_home_gallery_lightbox_nav_btn is-prev" data-lightbox-action="prev" title="Ảnh trước (Mũi tên trái)" aria-label="Xem ảnh trước">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
                </button>

                <!-- Viewport & Image Canvas -->
                <div class="mayhen_home_gallery_lightbox_viewport" data-lightbox-element="viewport">
                    <div class="mayhen_home_gallery_lightbox_canvas" data-lightbox-element="canvas">
                        <img src="" alt="" class="mayhen_home_gallery_lightbox_img" data-lightbox-element="img">
                    </div>

                    <!-- Loader Spinner -->
                    <div class="mayhen_home_gallery_lightbox_loader" data-lightbox-element="loader">
                        <div class="mayhen_home_gallery_lightbox_spinner"></div>
                        <span>Đang tải ảnh nghệ thuật...</span>
                    </div>

                    <!-- Error state -->
                    <div class="mayhen_home_gallery_lightbox_error" data-lightbox-element="error" style="display:none;">
                        <p>Không thể tải ảnh. Vui lòng thử lại.</p>
                        <button type="button" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm" data-lightbox-action="retry">
                            Tải Lại Ảnh
                        </button>
                    </div>
                </div>

                <!-- Navigation Next -->
                <button type="button" class="mayhen_home_gallery_lightbox_nav_btn is-next" data-lightbox-action="next" title="Ảnh tiếp theo (Mũi tên phải)" aria-label="Xem ảnh tiếp theo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

            <!-- Bottom Floating Info Bar -->
            <div class="mayhen_home_gallery_lightbox_info_bar" data-lightbox-element="info-bar">
                <div class="mayhen_home_gallery_lightbox_info_meta">
                    <span class="mayhen_home_gallery_lightbox_info_cat" data-lightbox-text="info-cat">PHONG CÁCH</span>
                    <h4 class="mayhen_home_gallery_lightbox_info_name" data-lightbox-text="info-name">Tên Mẫu Thiệp</h4>
                    <p class="mayhen_home_gallery_lightbox_info_hint">Click đúp để phóng to &bull; Kéo để di chuyển chi tiết &bull; Vuốt ngón tay để chuyển ảnh</p>
                </div>
                <div class="mayhen_home_gallery_lightbox_info_actions">
                    <a href="#" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_sm" data-lightbox-link="preview" target="_blank" rel="noopener noreferrer">
                        <span>Xem Thử Trực Tiếp</span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a href="#" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm" data-lightbox-link="detail">
                        <span>Chi Tiết Mẫu</span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="#" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_sm" data-lightbox-link="create">
                        <span>Tạo Thiệp Mẫu Này</span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Horizontal Thumbnail Strip Slider -->
            <div class="mayhen_home_gallery_lightbox_strip" data-lightbox-element="strip">
                <div class="mayhen_home_gallery_lightbox_strip_track" data-lightbox-element="strip-track">
                    <!-- Populated by JS for quick thumbnail jumps -->
                </div>
            </div>

            <!-- Toast Notification -->
            <div class="mayhen_home_gallery_lightbox_toast" data-lightbox-element="toast" aria-live="polite">
                <span>✓ Đã sao chép liên kết mẫu thiệp!</span>
            </div>
        </div>
    </section>

    <!-- 5. Real Workflow Section (Quy Trình Tạo & Sử Dụng Thiệp Cưới Online Thực Tế) -->
    <section class="mayhen_home_steps_section" id="quy-trinh" aria-labelledby="mayhen-steps-heading">
        <div class="mayhen_client_layout_container">
            <!-- Section Header -->
            <div class="mayhen_home_steps_header">
                <span class="mayhen_home_steps_badge">Hành Trình Trực Quan</span>
                <h2 class="mayhen_home_steps_title" id="mayhen-steps-heading">
                    Quy Trình Tạo & Sử Dụng <span class="mayhen_home_steps_title_serif">Thiệp Cưới Online</span> Trong 4 Bước
                </h2>
                <p class="mayhen_home_steps_desc">
                    Trải nghiệm liền mạch từ lúc chọn mẫu thiệp cưới online đẹp, tự tay điền thông tin và tải ảnh cưới, xem thử miễn phí trên điện thoại đến khi gửi link qua Zalo và quản lý khách mời chu đáo.
                </p>
            </div>

            <!-- 4-Stage Continuous Journey Grid -->
            <div class="mayhen_home_steps_grid">
                <!-- Step 01: Khám Phá & Xem Thử Trực Tiếp -->
                <article class="mayhen_home_steps_card" data-mayhen-step="1" tabindex="0" aria-label="Bước 1: Chọn mẫu thiệp và xem thử trên điện thoại">
                    <div class="mayhen_home_steps_card_top">
                        <span class="mayhen_home_steps_card_num" aria-hidden="true">01</span>
                        <span class="mayhen_home_steps_card_phase">Khám Phá Miễn Phí</span>
                    </div>

                    <h3 class="mayhen_home_steps_card_title">Chọn Mẫu Thiệp & Xem Thử Trên Điện Thoại</h3>
                    <p class="mayhen_home_steps_card_desc">
                        Lựa chọn phong cách yêu thích từ bộ sưu tập Quiet Luxury, Editorial hay Phong thư hoàng gia. Bạn có thể mở xem thử trực tiếp trên điện thoại để trải nghiệm trọn vẹn hiệu ứng mở nắp sáp, nhạc nền và đếm ngược trước khi tạo.
                    </p>

                    <!-- Mini Visual Mockup 01: Phong bì & Xem Thử -->
                    <div class="mayhen_home_steps_visual" aria-hidden="true">
                        <div class="mayhen_home_steps_visual_phone_mini">
                            <div class="mayhen_home_steps_visual_phone_bar">
                                <span class="mayhen_home_steps_visual_dot"></span>
                                <span>mayhen.vn/xem-thu</span>
                            </div>
                            <div class="mayhen_home_steps_visual_envelope">
                                <div class="mayhen_home_steps_visual_flap"></div>
                                <div class="mayhen_home_steps_visual_seal">囍</div>
                                <span class="mayhen_home_steps_visual_env_title">THIỆP MỜI TRỌNG THỂ</span>
                                <span class="mayhen_home_steps_visual_env_names">Minh Quân &bull; Ái Như</span>
                                <div class="mayhen_home_steps_visual_music_pill">
                                    <span class="mayhen_home_steps_visual_music_note">🎵</span>
                                    <span>A Thousand Years</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_steps_card_chip">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Trải nghiệm xem thử miễn phí 100%</span>
                    </div>
                </article>

                <!-- Step 02: Studio Điền Thông Tin Realtime -->
                <article class="mayhen_home_steps_card" data-mayhen-step="2" tabindex="0" aria-label="Bước 2: Nhập thông tin cưới và xem trước realtime">
                    <div class="mayhen_home_steps_card_top">
                        <span class="mayhen_home_steps_card_num" aria-hidden="true">02</span>
                        <span class="mayhen_home_steps_card_phase">Studio Trực Quan</span>
                    </div>

                    <h3 class="mayhen_home_steps_card_title">Nhập Thông Tin Cưới & Xem Trước Realtime</h3>
                    <p class="mayhen_home_steps_card_desc">
                        Tại Studio tạo thiệp, bạn chỉ cần gõ tên cô dâu chú rể, ngày cưới, sảnh tiệc và tải lên bộ ảnh kỷ niệm. Giao diện chia đôi thông minh tự động căn chỉnh và hiển thị bản xem trước song song theo thời gian thực trong 5 phút mà không cần biết thiết kế.
                    </p>

                    <!-- Mini Visual Mockup 02: Studio Form & Live Preview -->
                    <div class="mayhen_home_steps_visual" aria-hidden="true">
                        <div class="mayhen_home_steps_visual_studio_mini">
                            <div class="mayhen_home_steps_visual_studio_fields">
                                <div class="mayhen_home_steps_visual_field_line">
                                    <span class="mayhen_home_steps_visual_field_lbl">Dâu Rể:</span>
                                    <span class="mayhen_home_steps_visual_field_val">Minh Quân & Ái Như</span>
                                </div>
                                <div class="mayhen_home_steps_visual_field_line">
                                    <span class="mayhen_home_steps_visual_field_lbl">Ngày Cưới:</span>
                                    <span class="mayhen_home_steps_visual_field_val">24.10.2026</span>
                                </div>
                                <div class="mayhen_home_steps_visual_field_line">
                                    <span class="mayhen_home_steps_visual_field_lbl">Sảnh Tiệc:</span>
                                    <span class="mayhen_home_steps_visual_field_val">The White Palace</span>
                                </div>
                            </div>
                            <div class="mayhen_home_steps_visual_studio_sync">
                                <span class="mayhen_home_steps_visual_sync_badge">✓ Cập nhật realtime</span>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_steps_card_chip">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Tự động căn chỉnh • Hoàn thành trong 5 phút</span>
                    </div>
                </article>

                <!-- Step 03: Kích Hoạt VietQR Minh Bạch -->
                <article class="mayhen_home_steps_card" data-mayhen-step="3" tabindex="0" aria-label="Bước 3: Nhận link riêng và kích hoạt VietQR tự động">
                    <div class="mayhen_home_steps_card_top">
                        <span class="mayhen_home_steps_card_num" aria-hidden="true">03</span>
                        <span class="mayhen_home_steps_card_phase">Kích Hoạt Minh Bạch</span>
                    </div>

                    <h3 class="mayhen_home_steps_card_title">Nhận Link Riêng & Thanh Toán VietQR Tự Động</h3>
                    <p class="mayhen_home_steps_card_desc">
                        Khi đã ưng ý hoàn toàn với bản thiệp của mình, bạn kích hoạt gói lưu trữ (Gói Premium hoặc Pro) bằng cách quét mã VietQR tự động qua app ngân hàng. Kích hoạt trong vài giây, thanh toán 1 lần trọn gói, tuyệt đối không phí ẩn và không giữ tiền mừng cưới.
                    </p>

                    <!-- Mini Visual Mockup 03: VietQR Payment -->
                    <div class="mayhen_home_steps_visual" aria-hidden="true">
                        <div class="mayhen_home_steps_visual_qr_mini">
                            <div class="mayhen_home_steps_visual_qr_header">
                                <span class="mayhen_home_steps_visual_qr_brand">VietQR</span>
                                <span class="mayhen_home_steps_visual_qr_order">MH-8899</span>
                            </div>
                            <div class="mayhen_home_steps_visual_qr_box">
                                <div class="mayhen_home_steps_visual_qr_pattern">
                                    <span></span><span></span><span></span>
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="mayhen_home_steps_visual_qr_scan_line"></div>
                            </div>
                            <div class="mayhen_home_steps_visual_qr_footer">
                                <span class="mayhen_home_steps_visual_qr_price">149.000đ</span>
                                <span class="mayhen_home_steps_visual_qr_status">✓ Tự động kích hoạt</span>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_steps_card_chip">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Thanh toán 1 lần • Không giữ tiền mừng</span>
                    </div>
                </article>

                <!-- Step 04: Gửi Link Zalo & Quản Lý RSVP -->
                <article class="mayhen_home_steps_card" data-mayhen-step="4" tabindex="0" aria-label="Bước 4: Gửi thiệp qua Zalo và theo dõi khách báo hỷ">
                    <div class="mayhen_home_steps_card_top">
                        <span class="mayhen_home_steps_card_num" aria-hidden="true">04</span>
                        <span class="mayhen_home_steps_card_phase">Gửi Trao & Quản Lý</span>
                    </div>

                    <h3 class="mayhen_home_steps_card_title">Gửi Thiệp Qua Zalo & Theo Dõi Khách Báo Hỷ</h3>
                    <p class="mayhen_home_steps_card_desc">
                        Nhận đường link thiệp riêng biệt với ảnh đại diện Zalo/Facebook trang trọng. Khách nhận link chạm mở ngay trên điện thoại không cần tải app. Toàn bộ phản hồi xác nhận tham dự (đi bao nhiêu người, gửi lời chúc) được tổng hợp tự động để bạn chốt số bàn tiệc chu đáo.
                    </p>

                    <!-- Mini Visual Mockup 04: Zalo Message & RSVP Summary -->
                    <div class="mayhen_home_steps_visual" aria-hidden="true">
                        <div class="mayhen_home_steps_visual_rsvp_mini">
                            <div class="mayhen_home_steps_visual_zalo_bubble">
                                <span class="mayhen_home_steps_visual_zalo_badge">Tin Nhắn Zalo</span>
                                <p class="mayhen_home_steps_visual_zalo_text">Kính mời Anh Long & Người thương dự lễ cưới Quân & Như 💌</p>
                            </div>
                            <div class="mayhen_home_steps_visual_rsvp_stats">
                                <div class="mayhen_home_steps_visual_rsvp_stat_item">
                                    <span class="mayhen_home_steps_visual_rsvp_num">128</span>
                                    <span class="mayhen_home_steps_visual_rsvp_lbl">Khách tham dự</span>
                                </div>
                                <div class="mayhen_home_steps_visual_rsvp_stat_item">
                                    <span class="mayhen_home_steps_visual_rsvp_num">24</span>
                                    <span class="mayhen_home_steps_visual_rsvp_lbl">Bàn tiệc chốt</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_home_steps_card_chip">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Khách mở không cần app • Chốt bàn tiệc dễ dàng</span>
                    </div>
                </article>
            </div>

            <!-- Bottom Action Recommendation Bar -->
            <div class="mayhen_home_steps_bottom_bar">
                <div class="mayhen_home_steps_bottom_content">
                    <span class="mayhen_home_steps_bottom_icon" aria-hidden="true">✨</span>
                    <p class="mayhen_home_steps_bottom_text">
                        Bạn có thể bắt đầu tạo thử thiệp cưới của mình ngay bây giờ — Trải nghiệm miễn phí và chỉ kích hoạt khi đã hoàn toàn ưng ý.
                    </p>
                </div>
                <div class="mayhen_home_steps_bottom_actions">
                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_md">
                        <span>Tạo Thử Thiệp Miễn Phí</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('how-it-works') }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_md">
                        <span>Xem Chi Tiết Quy Trình</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. FAQ Section (Câu Hỏi Thường Gặp — Thiệp Mời Online) -->
    <section class="mayhen_home_faq_section" id="faq" aria-labelledby="mayhen-faq-heading">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_faq_grid">
                <!-- Left Editorial Column: Header & Consultation Help Note -->
                <aside class="mayhen_home_faq_sidebar">
                    <div class="mayhen_home_faq_sidebar_sticky">
                        <span class="mayhen_home_faq_badge">Hỏi & Đáp Thường Gặp</span>

                        <h2 class="mayhen_home_faq_title" id="mayhen-faq-heading">
                            Giải đáp băn khoăn về <span class="mayhen_home_faq_title_serif">thiệp cưới online</span>
                        </h2>

                        <p class="mayhen_home_faq_desc">
                            Tổng hợp những câu hỏi thường gặp nhất khi tự tạo thiệp cưới online, gửi link qua Zalo và quản lý xác nhận tham dự (RSVP) bàn tiệc.
                        </p>

                        <!-- Atelier Consultation Box -->
                        <div class="mayhen_home_faq_help_card">
                            <div class="mayhen_home_faq_help_icon_wrap" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    <circle cx="9" cy="10" r="1" fill="currentColor"/>
                                    <circle cx="12" cy="10" r="1" fill="currentColor"/>
                                    <circle cx="15" cy="10" r="1" fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="mayhen_home_faq_help_body">
                                <h3 class="mayhen_home_faq_help_title">Bạn cần tư vấn thêm?</h3>
                                <p class="mayhen_home_faq_help_text">
                                    Đội ngũ Mây Hẹn luôn sẵn lòng đồng hành và giải đáp mọi yêu cầu riêng cho thiệp cưới của bạn.
                                </p>
                                <a href="{{ route('contact') }}" class="mayhen_home_faq_help_link">
                                    <span>Trò chuyện cùng Mây Hẹn</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Romantic Atelier Wedding Postcard Widget (Đáng yêu & Lấp đầy hoàn hảo khoảng trống) -->
                        <div class="mayhen_home_faq_postcard" data-mayhen-faq-postcard>
                            <!-- Vintage Postage Stamp -->
                            <div class="mayhen_home_faq_postcard_stamp" aria-hidden="true">
                                <span class="mayhen_home_faq_postcard_stamp_icon">🕊️</span>
                                <span class="mayhen_home_faq_postcard_stamp_title">MÂY HẸN</span>
                                <span class="mayhen_home_faq_postcard_stamp_year">2026</span>
                                <div class="mayhen_home_faq_postcard_stamp_postmark">LOVE POST</div>
                            </div>

                            <!-- Postcard Header -->
                            <div class="mayhen_home_faq_postcard_head">
                                <span class="mayhen_home_faq_postcard_tag">Lời Nhắn Hạnh Phúc</span>
                                <h4 class="mayhen_home_faq_postcard_title">Gửi Người Thương Quý</h4>
                            </div>

                            <!-- Romantic Quote -->
                            <p class="mayhen_home_faq_postcard_quote">
                                "Tấm thiệp gửi đi mang theo trọn vẹn sự trân trọng. Mây Hẹn nâng niu từng chi tiết để ngày vui của hai bạn thật vẹn tròn."
                            </p>

                            <!-- Interactive Wax Seal Monogram -->
                            <div class="mayhen_home_faq_postcard_seal_row">
                                <button type="button" 
                                        class="mayhen_home_faq_postcard_seal_btn" 
                                        id="mayhen_home_faq_seal_btn"
                                        title="Chạm vào con dấu sáp để nhận lời chúc phúc"
                                        aria-label="Chạm vào con dấu sáp Song Hỷ để nhận lời chúc phúc">
                                    <span class="mayhen_home_faq_postcard_seal_char">囍</span>
                                    <span class="mayhen_home_faq_postcard_seal_halo"></span>
                                </button>
                                <div class="mayhen_home_faq_postcard_seal_meta">
                                    <span class="mayhen_home_faq_postcard_seal_text" id="mayhen_home_faq_seal_text">Chạm con dấu sáp 💌</span>
                                    <span class="mayhen_home_faq_postcard_seal_hint">Gửi lời chúc phúc trăm năm</span>
                                </div>
                            </div>

                            <!-- 3 Mini Trust Chips -->
                            <div class="mayhen_home_faq_postcard_chips">
                                <span class="mayhen_home_faq_postcard_chip">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span>Không quảng cáo</span>
                                </span>
                                <span class="mayhen_home_faq_postcard_chip">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span>Xem thử miễn phí</span>
                                </span>
                                <span class="mayhen_home_faq_postcard_chip">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span>Mở mượt trên Zalo</span>
                                </span>
                            </div>

                            <!-- Action CTA Button -->
                            <a href="{{ route('templates.index') }}" class="mayhen_home_faq_postcard_cta">
                                <span>Thử làm thiệp của bạn ngay</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>

                            <!-- Floating hearts and petals target container -->
                            <div class="mayhen_home_faq_postcard_bursts" id="mayhen_home_faq_bursts" aria-hidden="true"></div>
                        </div>
                    </div>
                </aside>

                <!-- Right Accordion Column -->
                <div class="mayhen_home_faq_accordion_wrap" data-mayhen-component="faq-accordion" role="region" aria-label="Danh sách câu hỏi thường gặp">
                    <!-- Item 01 -->
                    <div class="mayhen_home_faq_item is-open" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="true"
                                aria-controls="mayhen-faq-answer-1"
                                id="mayhen-faq-trigger-1">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">01</span>
                            <span class="mayhen_home_faq_question">Thiệp mời online là gì và khác gì so với thiệp cưới giấy truyền thống?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-1"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-1">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Thiệp mời online (hay thiệp cưới điện tử) là một trang web thiệp cưới riêng biệt được thiết kế tối ưu cho màn hình điện thoại. Khác với thiệp giấy thông thường hay ảnh chụp màn hình chỉ xem được chữ tĩnh, thiệp mời online tích hợp trọn vẹn nhạc nền, album ảnh cưới, bản đồ chỉ đường Google Maps tới tận sảnh tiệc, đồng hồ đếm ngược ngày cưới và nút báo có tham dự hay không. Bạn có thể gửi link thiệp mời online qua Zalo, Messenger hoặc SMS cho bạn bè, đồng nghiệp ở xa rất nhanh chóng và chỉn chu, đồng thời dùng song song với thiệp giấy để việc mời cưới trọn vẹn nhất.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 02 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-2"
                                id="mayhen-faq-trigger-2">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">02</span>
                            <span class="mayhen_home_faq_question">Tôi không rành công nghệ và không biết thiết kế thì có tự tạo thiệp mời online được không?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-2"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-2">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Hoàn toàn được. Toàn bộ mẫu thiệp cưới đẹp trên Mây Hẹn đã được dàn trang, phối màu và căn chỉnh tỷ lệ chuẩn xác. Bạn chỉ cần chọn mẫu mình thích, gõ tên cô dâu chú rể, ngày giờ tổ chức, địa chỉ sảnh cưới và tải ảnh kỷ niệm lên. Giao diện trực quan cho phép bạn thao tác dễ dàng trên cả điện thoại lẫn máy tính và hoàn thành tấm thiệp trong khoảng 5 đến 10 phút mà không cần bất kỳ kiến thức đồ họa nào.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 03 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-3"
                                id="mayhen-faq-trigger-3">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">03</span>
                            <span class="mayhen_home_faq_question">Tôi có thể xem thử thiệp cưới trên điện thoại trước khi thanh toán không?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-3"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-3">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Có. Bạn được tự do chọn mẫu, nhập đầy đủ thông tin, tải ảnh và trải nghiệm xem thử thực tế trên điện thoại hoàn toàn miễn phí. Bạn có thể kiểm tra hiệu ứng mở phong thư, nghe thử nhạc nền, kiểm tra bản đồ chỉ đường và duyệt lại từng chi tiết. Khi nào bạn thực sự ưng ý với tác phẩm của mình và muốn nhận đường link chính thức để gửi cho khách mời thì mới tiến hành thanh toán kích hoạt.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 04 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-4"
                                id="mayhen-faq-trigger-4">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">04</span>
                            <span class="mayhen_home_faq_question">Khách mời nhận link qua Zalo có cần tải app hay tạo tài khoản để xem thiệp không?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-4"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-4">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Không. Khi bạn gửi đường link thiệp qua Zalo, Messenger hay tin nhắn thông thường, khách mời chỉ cần chạm vào link là thiệp sẽ mở tức thì trên trình duyệt mặc định của máy (tương thích mượt mà với cả iPhone, Samsung, Xiaomi hay máy tính). Khách không cần cài thêm bất kỳ ứng dụng nào và không phải đăng ký tài khoản.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 05 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-5"
                                id="mayhen-faq-trigger-5">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">05</span>
                            <span class="mayhen_home_faq_question">Tính năng xác nhận tham dự (RSVP) trên thiệp hoạt động như thế nào?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-5"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-5">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Cuối mỗi tấm thiệp mời online có sẵn một biểu mẫu phản hồi ngắn gọn. Khách mời có thể nhanh chóng chọn: chắc chắn tham dự, rất tiếc không thể đến hoặc chưa chắc chắn, kèm theo số lượng người đi cùng và lời chúc gửi tới cô dâu chú rể. Tất cả câu trả lời sẽ được tổng hợp tự động vào danh sách quản lý của bạn theo thời gian thực, giúp bạn nắm rõ số lượng khách để chốt số bàn tiệc chính xác, hạn chế tình trạng thừa hoặc thiếu cỗ.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 06 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-6"
                                id="mayhen-faq-trigger-6">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">06</span>
                            <span class="mayhen_home_faq_question">Sau khi đã gửi link cho khách mời, tôi có thể chỉnh sửa lại thông tin trên thiệp không?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-6"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-6">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Có. Nếu cần thay đổi ảnh cưới, cập nhật lại giờ đón khách hay đính kèm thêm ghi chú về trang phục (dress code), bạn chỉ cần đăng nhập và cập nhật nội dung trong trang quản trị. Thông tin mới sẽ hiển thị ngay lập tức trên đường link mà không làm thay đổi địa chỉ link bạn đã gửi trước đó cho khách.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 07 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-7"
                                id="mayhen-faq-trigger-7">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">07</span>
                            <span class="mayhen_home_faq_question">Mã QR mừng cưới trên thiệp có bị thu phí hoa hồng hay qua trung gian không?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-7"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-7">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Tuyệt đối không. Mã QR hiển thị trên thiệp cưới là mã chuyển khoản ngân hàng chính chủ của cô dâu chú rể theo tiêu chuẩn VietQR. Khi khách quét mã, tiền mừng cưới sẽ được chuyển trực tiếp vào tài khoản ngân hàng của bạn ngay lập tức. Mây Hẹn không đóng vai trò giữ tiền trung gian và không thu bất kỳ khoản chiết khấu nào từ tiền mừng của bạn.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 08 -->
                    <div class="mayhen_home_faq_item" data-mayhen-faq-item>
                        <button type="button"
                                class="mayhen_home_faq_trigger"
                                aria-expanded="false"
                                aria-controls="mayhen-faq-answer-8"
                                id="mayhen-faq-trigger-8">
                            <span class="mayhen_home_faq_item_num" aria-hidden="true">08</span>
                            <span class="mayhen_home_faq_question">Thiệp cưới online của tôi sẽ duy trì hoạt động trong bao lâu sau ngày cưới?</span>
                            <span class="mayhen_home_faq_icon" aria-hidden="true">
                                <svg class="mayhen_home_faq_icon_svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" class="mayhen_home_faq_icon_vert"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="mayhen_home_faq_answer_region"
                             id="mayhen-faq-answer-8"
                             role="region"
                             aria-labelledby="mayhen-faq-trigger-8">
                            <div class="mayhen_home_faq_answer_inner">
                                <p class="mayhen_home_faq_answer_text">
                                    Sau khi kích hoạt, thiệp của bạn sẽ duy trì hoạt động liên tục trong suốt thời gian chuẩn bị đám cưới và tiếp tục lưu trữ tối thiểu 6 tháng sau ngày cưới. Bạn và bạn bè, người thân vẫn có thể mở lại đường link bất cứ lúc nào để xem lại những khung hình kỷ niệm và những lời chúc thân thương được gửi gắm trong ngày cưới.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Semantic SEO Guide / Knowledge Hub (Xu Hướng & Cẩm Nang Thiệp Cưới Online) -->
    <section class="mayhen_home_guide_section" id="cam-nang-thiep-cuoi" aria-labelledby="mayhen-guide-heading">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_guide_header">
                <span class="mayhen_home_guide_badge">Cẩm Nang Hôn Lễ &bull; Xu Hướng Thiệp Cưới</span>
                <h2 class="mayhen_home_guide_title" id="mayhen-guide-heading">
                    Cẩm Nang Tạo & Gửi <span class="mayhen_home_guide_title_serif">Thiệp Cưới Online</span> Tinh Tế
                </h2>
                <p class="mayhen_home_guide_desc">
                    Tất cả những điều dâu rể cần biết để tự tạo tấm thiệp cưới online chỉn chu, hiện đại và gửi trao trọn vẹn sự trân trọng tới từng vị khách quý.
                </p>
            </div>

            <div class="mayhen_home_guide_grid">
                <!-- Guide Card 1 -->
                <article class="mayhen_home_guide_card">
                    <div class="mayhen_home_guide_card_body">
                        <span class="mayhen_home_guide_tag">Kiến Thức &bull; Xu Hướng 2026</span>
                        <h3 class="mayhen_home_guide_card_title">
                            Thiệp cưới online là gì? 5 lý do thiệp cưới điện tử được các cặp đôi hiện đại yêu thích
                        </h3>
                        <p class="mayhen_home_guide_card_text">
                            Thiệp cưới online (thiệp cưới điện tử) là website thiệp cưới cá nhân hóa cho từng cặp đôi. Khác với thiệp giấy truyền thống chỉ truyền tải nội dung tĩnh, thiệp cưới online mang đến trải nghiệm đa giác quan với âm nhạc du dương, album ảnh cưới sắc nét, bản đồ Google Maps dẫn đường chính xác tới sảnh tiệc và biểu mẫu báo hỷ RSVP giúp chốt số lượng khách chu đáo.
                        </p>
                        <div class="mayhen_home_guide_footer">
                            <span class="mayhen_home_guide_author">Bởi Mây Hẹn Atelier</span>
                            <span class="mayhen_home_guide_readtime">4 phút đọc</span>
                        </div>
                    </div>
                </article>

                <!-- Guide Card 2 -->
                <article class="mayhen_home_guide_card">
                    <div class="mayhen_home_guide_card_body">
                        <span class="mayhen_home_guide_tag">Kinh Nghiệm Thực Tế</span>
                        <h3 class="mayhen_home_guide_card_title">
                            Nghệ thuật gửi thiệp cưới online qua Zalo lịch sự, tinh tế và không gây cảm giác đường đột
                        </h3>
                        <p class="mayhen_home_guide_card_text">
                            Để việc gửi link thiệp mời cưới qua Zalo hay Messenger trọn vẹn sự trang trọng, cô dâu chú rể nên soạn kèm một đoạn lời ngỏ chân thành xưng hô theo từng mối quan hệ. Thời điểm lý tưởng nhất để gửi link là trước ngày cưới 2 đến 3 tuần, giúp khách mời kịp xem album ảnh, nắm rõ thời gian địa điểm và xác nhận tham dự thuận tiện.
                        </p>
                        <div class="mayhen_home_guide_footer">
                            <span class="mayhen_home_guide_author">Kinh nghiệm cưới</span>
                            <span class="mayhen_home_guide_readtime">3 phút đọc</span>
                        </div>
                    </div>
                </article>

                <!-- Guide Card 3 -->
                <article class="mayhen_home_guide_card">
                    <div class="mayhen_home_guide_card_body">
                        <span class="mayhen_home_guide_tag">Tiêu Chuẩn Quiet Luxury</span>
                        <h3 class="mayhen_home_guide_card_title">
                            Vì sao thiệp cưới online Mây Hẹn luôn mang lại cảm xúc trang nhã khác biệt?
                        </h3>
                        <p class="mayhen_home_guide_card_text">
                            Mây Hẹn kiên định theo đuổi triết lý Quiet Luxury: tuyệt đối không chèn quảng cáo phiền toái, chăm chút chuyển động mở nắp phong thư dấu sáp hoàng gia mượt mà và tối ưu tốc độ tải trang dưới 1 giây trên mọi smartphone. Tấm thiệp cưới của bạn trở thành một tác phẩm nghệ thuật lưu giữ trọn vẹn kỷ niệm ngày hạnh phúc trăm năm.
                        </p>
                        <div class="mayhen_home_guide_footer">
                            <span class="mayhen_home_guide_author">Triết lý thiết kế</span>
                            <span class="mayhen_home_guide_readtime">3 phút đọc</span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- 8. Final CTA Banner -->
    <section class="mayhen_client_layout_section" style="padding-top: 0;">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_home_cta_banner">
                <h2 class="mayhen_home_cta_title">Tự Tạo Thiệp Cưới Online Sang Trọng & Miễn Phí Ngay Hôm Nay</h2>
                <p class="mayhen_home_cta_desc">
                    Trải nghiệm tạo và xem thử mẫu thiệp cưới online thực tế trên điện thoại hoàn toàn miễn phí. Chỉ thanh toán kích hoạt khi bạn đã hoàn toàn ưng ý với tác phẩm của mình.
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
