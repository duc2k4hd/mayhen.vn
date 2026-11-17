@extends('client.layouts.master')

@section('title', 'Về Mây Hẹn (mayhen.vn) — Nơi Những Lời Hẹn Trở Thành Ngày Chung Đôi')
@section('meta_description', 'Khám phá câu chuyện thương hiệu, ý nghĩa biểu tượng áng mây - trái tim - ngôi sao và triết lý Quiet Luxury của Mây Hẹn. Nơi nâng niu lời hẹn ước ngày chung đôi bằng nghệ thuật thiệp cưới online tinh tế.')
@section('meta_keywords', 'về mây hẹn, câu chuyện thương hiệu mây hẹn, ý nghĩa biểu tượng mây hẹn, thiệp cưới online mây hẹn, thiệp cưới quiet luxury, hẹn trời hẹn đất hẹn mây, mayhen.vn')
@section('canonical', route('about'))
@section('og_image', asset('storage/images/client/business/mayhen-favicon.png'))
@section('og_image_alt', 'Mây Hẹn — Nơi Những Lời Hẹn Trở Thành Ngày Chung Đôi')
@section('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/about.css') }}?v={{ time() }}">
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/ve-may-hen') . '/#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Về Mây Hẹn',
                    'item' => url('/ve-may-hen'),
                ],
            ],
        ],
        [
            '@type' => 'AboutPage',
            '@id' => url('/ve-may-hen') . '/#webpage',
            'url' => url('/ve-may-hen'),
            'name' => 'Về Mây Hẹn (mayhen.vn) — Nơi Những Lời Hẹn Trở Thành Ngày Chung Đôi',
            'description' => 'Câu chuyện thương hiệu, ý nghĩa biểu tượng và triết lý nghệ thuật Quiet Luxury của Mây Hẹn trong việc nâng tầm trải nghiệm thiệp cưới online tại Việt Nam.',
            'inLanguage' => 'vi-VN',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => url('/') . '/#website',
                'name' => 'Mây Hẹn',
                'url' => url('/'),
            ],
            'about' => [
                '@type' => 'Organization',
                '@id' => url('/') . '/#organization',
                'name' => 'Mây Hẹn',
                'alternateName' => ['MayHen', 'MayHen.vn', 'Thiệp Cưới Online Mây Hẹn'],
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('storage/images/client/business/mayhen-favicon.png'),
                    'caption' => 'Mây Hẹn — Nơi Những Lời Hẹn Trở Thành Ngày Chung Đôi',
                ],
                'slogan' => 'Nơi những lời hẹn trở thành ngày chung đôi',
                'description' => 'Nền tảng sáng tạo thiệp cưới online và giải pháp cưới kỹ thuật số cao cấp hàng đầu Việt Nam theo phong cách Quiet Luxury.',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Hà Nội',
                    'addressCountry' => 'VN'
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => '+84-988-888-888',
                    'contactType' => 'customer service',
                    'availableLanguage' => ['vi', 'en']
                ]
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_about_wrapper">
    {{-- 1. HERO SECTION: LỜI MỞ ĐẦU THƯƠNG HIỆU (ATELIER HAUTE COUTURE) --}}
    <section class="mayhen_about_hero">
        <div class="mayhen_about_hero_container">
            {{-- Huy hiệu Monogram Hoàng Gia Mây Hẹn --}}
            <div class="mayhen_about_hero_crest">
                <div class="mayhen_about_hero_crest_box">
                    <img src="{{ asset('storage/images/client/business/mayhen-favicon.png') }}" 
                         alt="Biểu Tượng Mây Hẹn" 
                         class="mayhen_about_hero_crest_img" 
                         width="72" 
                         height="72" 
                         loading="eager">
                </div>
                <span class="mayhen_about_hero_kicker">Câu Chuyện Thương Hiệu</span>
            </div>

            {{-- Tiêu đề Typography Editorial --}}
            <span class="mayhen_about_hero_brand_lead">MÂY HẸN ATELIER</span>
            <h1 class="mayhen_about_hero_title">
                Nơi Những Lời Hẹn
                <span class="mayhen_about_hero_title_highlight">Trở Thành Ngày Chung Đôi</span>
            </h1>

            {{-- Bức Thư Lụa Đầu Tiên (The Atelier Silk Letter Parchment) --}}
            <div class="mayhen_about_hero_letter_card">
                <span class="mayhen_about_hero_letter_corner mayhen_about_hero_letter_corner--tl" aria-hidden="true"></span>
                <span class="mayhen_about_hero_letter_corner mayhen_about_hero_letter_corner--tr" aria-hidden="true"></span>
                <span class="mayhen_about_hero_letter_corner mayhen_about_hero_letter_corner--bl" aria-hidden="true"></span>
                <span class="mayhen_about_hero_letter_corner mayhen_about_hero_letter_corner--br" aria-hidden="true"></span>

                <p class="mayhen_about_hero_letter_p1">
                    Có những lời hẹn bắt đầu thật giản dị: một cuộc gặp gỡ, một cái nắm tay, một lời đồng ý. Rồi đến một ngày, hai người muốn sẻ chia niềm vui thiêng liêng ấy với gia đình, bạn bè và những người thương quý.
                </p>

                <div class="mayhen_about_hero_letter_divider" aria-hidden="true">
                    <span class="mayhen_about_hero_letter_divider_heart">❦</span>
                </div>

                <p class="mayhen_about_hero_letter_p2">
                    <strong>Mây Hẹn</strong> đồng hành cùng khoảnh khắc ấy. Không chỉ để tạo nên một tấm thiệp cưới đẹp, mà để mỗi lời mời được gửi đi bằng sự trân trọng và mang trọn vẹn dấu ấn riêng của hai người.
                </p>

                <div class="mayhen_about_hero_letter_sign">
                    ✦ Lời ngỏ từ Mây Hẹn Atelier ✦
                </div>
            </div>
        </div>
    </section>

    {{-- 2. POETIC HERITAGE: HAI CÂU THƠ, MỘT LỜI HẸN --}}
    <section class="mayhen_about_poem_section" aria-label="Hai câu thơ, một lời hẹn của Mây Hẹn">
        <div class="mayhen_about_poem_card">
            <span class="mayhen_about_poem_ornament">❦</span>
            <blockquote class="mayhen_about_poem_verse">
                “Hẹn trời, hẹn đất, hẹn Mây<br>
                Hẹn ngày chung bước, đong đầy yêu thương.”
            </blockquote>
            <span class="mayhen_about_poem_subtitle">Hai câu thơ &bull; Một lời hẹn thề non nước</span>

            <div class="mayhen_about_poem_analysis_grid">
                <div class="mayhen_about_poem_analysis_col">
                    <h3><span>✦</span> “Hẹn trời, hẹn đất, hẹn Mây”</h3>
                    <p>
                        Lời hẹn gửi vào những điều rộng lớn và thân thuộc. Trời đất như chứng kiến tình yêu, còn Mây là nơi lời hẹn ấy được nâng niu, chở che và lưu giữ trọn vẹn giữa bao la tình cảm gia đình, bè bạn.
                    </p>
                </div>
                <div class="mayhen_about_poem_analysis_col">
                    <h3><span>✦</span> “Hẹn ngày chung bước, đong đầy yêu thương”</h3>
                    <p>
                        Đưa lời hẹn trở về điều quan trọng nhất: ngày hai người quyết định cùng đi chung một con đường. Ngày cưới là một dấu mốc đẹp, nhưng tình yêu không dừng lại ở đó — tình yêu còn tiếp tục trong những ngày bình thường, khi cả hai cùng chăm chút cho một mái ấm tương lai.
                    </p>
                </div>
            </div>

            <div class="mayhen_about_poem_footnote">
                Hai câu thơ được viết theo thể lục bát truyền thống, với nhịp điệu gần gũi và vần <strong>“Mây – đầy”</strong>, để mỗi lần đọc lên đều gợi cảm giác nhẹ nhàng, thân thương và ấm áp.
            </div>
        </div>
    </section>

    <div class="mayhen_about_container">
        {{-- 3. ICONOGRAPHY: GIẢI MÃ Ý NGHĨA BIỂU TƯỢNG MÂY HẸN --}}
        <section class="mayhen_about_iconography_section" aria-labelledby="iconographyHeading">
            <div class="mayhen_about_section_header">
                <span class="mayhen_about_kicker">Ngôn Ngữ Thiết Kế</span>
                <h2 class="mayhen_about_section_title" id="iconographyHeading">Ý Nghĩa Đằng Sau Biểu Tượng Mây Hẹn</h2>
                <p class="mayhen_about_section_subtitle">
                    Biểu tượng Mây Hẹn được tạo nên từ những đường cong mềm mại của áng mây, một trái tim ở vị trí trung tâm và ngôi sao nhỏ phía trên. Tất cả hòa quyện trong sắc vàng champagne ấm áp — mỗi chi tiết mang một ý nghĩa, cùng kể về tình yêu và những lời hẹn ước.
                </p>
            </div>

            <div class="mayhen_about_iconography_grid">
                {{-- 1. Áng Mây --}}
                <div class="mayhen_about_iconography_card">
                    <div class="mayhen_about_iconography_card_top">
                        <div class="mayhen_about_iconography_badge_icon">
                            ☁️
                        </div>
                        <div>
                            <span class="mayhen_about_iconography_sub">Đường Nét Mềm Mại</span>
                            <h3 class="mayhen_about_iconography_title">Áng Mây — Sự dịu dàng của một lời hẹn</h3>
                        </div>
                    </div>
                    <p class="mayhen_about_iconography_desc">
                        Áng mây gợi cảm giác nhẹ nhàng, bình yên và tự do. Những đường cong nối tiếp nhau cũng thể hiện tinh thần của Mây Hẹn: một nơi nâng niu, lưu giữ những lời mời chân thành và những khoảnh khắc đáng nhớ nhất trong hành trình tình yêu lứa đôi.
                    </p>
                </div>

                {{-- 2. Trái Tim --}}
                <div class="mayhen_about_iconography_card">
                    <div class="mayhen_about_iconography_card_top">
                        <div class="mayhen_about_iconography_badge_icon">
                            🤍
                        </div>
                        <div>
                            <span class="mayhen_about_iconography_sub">Vị Trí Trung Tâm</span>
                            <h3 class="mayhen_about_iconography_title">Trái Tim — Hai người, một hành trình</h3>
                        </div>
                    </div>
                    <p class="mayhen_about_iconography_desc">
                        Nằm giữa những đường cong của áng mây là hình trái tim, biểu tượng của tình yêu và sự gắn bó sắt son. Đó là khoảnh khắc hai người không chỉ yêu nhau, mà còn lựa chọn cùng nhau bước tiếp, sẻ chia niềm vui, những thử thách và cả những điều bình dị của cuộc sống.
                    </p>
                </div>

                {{-- 3. Ngôi Sao --}}
                <div class="mayhen_about_iconography_card">
                    <div class="mayhen_about_iconography_card_top">
                        <div class="mayhen_about_iconography_badge_icon">
                            ⭐
                        </div>
                        <div>
                            <span class="mayhen_about_iconography_sub">Dấu Mốc Tỏa Sáng</span>
                            <h3 class="mayhen_about_iconography_title">Ngôi Sao — Khoảnh khắc đáng nhớ</h3>
                        </div>
                    </div>
                    <p class="mayhen_about_iconography_desc">
                        Ngôi sao nhỏ phía trên trái tim tượng trưng cho một dấu mốc đặc biệt của cuộc đời. Giữa rất nhiều ngày trong dòng chảy thời gian, có những ngày chúng ta muốn nhớ mãi. Ngày cưới chính là một trong những khoảnh khắc lấp lánh và thiêng liêng ấy.
                    </p>
                </div>

                {{-- 4. Sắc Vàng Champagne --}}
                <div class="mayhen_about_iconography_card">
                    <div class="mayhen_about_iconography_card_top">
                        <div class="mayhen_about_iconography_badge_icon">
                            ✨
                        </div>
                        <div>
                            <span class="mayhen_about_iconography_sub">Gam Màu Chủ Đạo</span>
                            <h3 class="mayhen_about_iconography_title">Sắc Vàng Champagne — Vẻ đẹp của sự trân trọng</h3>
                        </div>
                    </div>
                    <p class="mayhen_about_iconography_desc">
                        Không quá rực rỡ hay chói lóa, sắc vàng champagne mang đến cảm giác ấm áp, thanh lịch và trang trọng. Màu sắc ấy thể hiện trọn vẹn tinh thần mà Mây Hẹn theo đuổi: tinh tế mà gần gũi, lãng mạn nhưng không phô trương — chuẩn mực của phong cách Quiet Luxury.
                    </p>
                </div>
            </div>
        </section>

        {{-- 4. MANIFESTO: TỪ MỘT LỜI MỜI ĐẾN MỘT KỶ NIỆM --}}
        <section class="mayhen_about_manifesto_box" aria-labelledby="manifestoHeading">
            <div style="text-align: center; max-width: 720px; margin: 0 auto;">
                <span class="mayhen_about_kicker">Triết Lý Tác Phẩm</span>
                <h2 class="mayhen_about_section_title" id="manifestoHeading">Từ Một Lời Mời Đến Một Kỷ Niệm Vĩnh Cửu</h2>
                <p style="font-size: 16px; color: var(--mh-about-text-muted); line-height: 1.85; margin: 0;">
                    Mây Hẹn mong muốn việc chuẩn bị và gửi thiệp cưới trở nên nhẹ nhàng hơn bao giờ hết. Mỗi cặp đôi đều có thể tự do lựa chọn phong cách mình yêu thích, kể câu chuyện tình yêu theo cách riêng và gửi lời mời đến những người quan trọng trong đời.
                </p>
                <div style="margin-top: 14px; font-size: 17px; font-weight: 600; color: var(--mh-about-primary); font-family: 'Playfair Display', Georgia, serif; font-style: italic;">
                    “Bởi một tấm thiệp đẹp không chỉ nằm ở màu sắc, hình ảnh hay hiệu ứng. Nó còn đẹp khi người nhận cảm thấy mình thật sự được chờ đón.”
                </div>
            </div>

            <div class="mayhen_about_manifesto_grid">
                <div class="mayhen_about_manifesto_item">
                    <div class="mayhen_about_manifesto_item_icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <h3 class="mayhen_about_manifesto_item_title">Thiết Kế Độc Bản</h3>
                    <p class="mayhen_about_manifesto_item_desc">
                        Từ nét hoàng gia với tem sáp phong thư, phong cách tối giản Hàn Quốc đến nét truyền thống Á Đông — bạn tự do chắp bút cho câu chuyện của riêng mình.
                    </p>
                </div>

                <div class="mayhen_about_manifesto_item">
                    <div class="mayhen_about_manifesto_item_icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </div>
                    <h3 class="mayhen_about_manifesto_item_title">Chạm Đến Trái Tim</h3>
                    <p class="mayhen_about_manifesto_item_desc">
                        Khách mời mở thiệp là được đắm chìm trong giai điệu tình ca, ngắm nhìn ảnh cưới lung linh và gửi lại những lời chúc phúc ấm lòng tức thì.
                    </p>
                </div>

                <div class="mayhen_about_manifesto_item">
                    <div class="mayhen_about_manifesto_item_icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <h3 class="mayhen_about_manifesto_item_title">Lưu Giữ Trọn Đời</h3>
                    <p class="mayhen_about_manifesto_item_desc">
                        Không bao giờ bị thất lạc hay phai mờ theo thời gian. Mọi kỷ vật thiệp cưới, lưu bút và lời chúc của khách quý đều được lưu giữ vẹn nguyên cùng năm tháng.
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. STATS SECTION (CON SỐ ẤN TƯỢNG) --}}
        <section class="mayhen_about_stats_wrapper" aria-label="Những con số ấn tượng về Mây Hẹn" style="margin-bottom: 80px;">
            <div class="mayhen_about_stats_grid">
                <div class="mayhen_about_stat_item">
                    <div class="mayhen_about_stat_number">50.000+</div>
                    <div class="mayhen_about_stat_label">Cặp đôi tin tưởng trao gửi ngày chung đôi</div>
                </div>
                <div class="mayhen_about_stat_item">
                    <div class="mayhen_about_stat_number">99.8%</div>
                    <div class="mayhen_about_stat_label">Phản hồi xúc động và yêu thích từ khách mời</div>
                </div>
                <div class="mayhen_about_stat_item">
                    <div class="mayhen_about_stat_number">1.000.000+</div>
                    <div class="mayhen_about_stat_label">Lượt thiệp gửi qua Zalo, Messenger &amp; Web</div>
                </div>
                <div class="mayhen_about_stat_item">
                    <div class="mayhen_about_stat_number">90%</div>
                    <div class="mayhen_about_stat_label">Tiết kiệm thời gian chuẩn bị &amp; chi phí in ấn</div>
                </div>
            </div>
        </section>

        {{-- 6. CORE VALUES: BỐN TRỤ CỘT GIÁ TRỊ --}}
        <section style="margin-bottom: 80px;" aria-labelledby="valuesHeading">
            <div class="mayhen_about_section_header">
                <span class="mayhen_about_kicker">Cam Kết Danh Dự</span>
                <h2 class="mayhen_about_section_title" id="valuesHeading">Bốn Nguyên Tắc Định Hình Trải Nghiệm Mây Hẹn</h2>
                <p class="mayhen_about_section_subtitle">
                    Mỗi tính năng và dòng mã tại Mây Hẹn đều được xây dựng dựa trên 4 cam kết bất biến với các cặp đôi và khách mời.
                </p>
            </div>

            <div class="mayhen_about_values_grid">
                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">1. Cảm Xúc Là Trọng Tâm (Heartfelt First)</h3>
                    <p class="mayhen_about_value_desc">
                        Tấm thiệp cưới không đơn thuần là lời thông báo ngày giờ, mà là kỷ vật lưu dấu tình yêu. Mây Hẹn trao cho bạn không gian kể câu chuyện hẹn hò, album 10 khoảnh khắc hạnh phúc và sổ lưu bút để người thân gửi gắm những lời chúc phúc ấm lòng.
                    </p>
                </div>

                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">2. Hoàn Hảo Tuyệt Đối Trên Di Động (Mobile-First)</h3>
                    <p class="mayhen_about_value_desc">
                        Hơn 95% khách mời mở thiệp qua điện thoại (Zalo, Messenger, SMS). Mọi mẫu thiệp Mây Hẹn đều được tối ưu tải trang dưới 1 giây, cuộn lướt êm ái 60fps, tự động căn chỉnh hoàn hảo trên mọi dòng máy iPhone và Android mà không bị tràn màn hình.
                    </p>
                </div>

                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">3. Tôn Trọng Riêng Tư (Luật 91/2025/QH15)</h3>
                    <p class="mayhen_about_value_desc">
                        Chúng tôi hiểu danh sách khách mời và số tài khoản mừng cưới là thông tin tuyệt mật. Mây Hẹn tuân thủ nghiêm ngặt Luật Bảo vệ Dữ liệu Cá nhân: mã hóa đường link cá nhân hóa, ngăn chặn công cụ tìm kiếm cào dữ liệu riêng tư và không bao giờ thương mại hóa dữ liệu khách hàng.
                    </p>
                </div>

                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">4. Hôn Lễ Xanh &amp; Bền Vững (Green Wedding)</h3>
                    <p class="mayhen_about_value_desc">
                        Mỗi tấm thiệp cưới online gửi đi là một hành động thiết thực bảo vệ môi trường, giảm thiểu lượng lớn giấy phế thải sau lễ cưới. Cùng với việc tiết kiệm đến 90% chi phí in ấn, Mây Hẹn tự hào đồng hành cùng các cặp đôi xây dựng phong cách cưới văn minh và hiện đại.
                    </p>
                </div>
            </div>
        </section>

        {{-- 7. FOUNDERS' PROMISE & CLOSING OATH --}}
        <section class="mayhen_about_promise_box" aria-label="Lời hẹn ước cùng Mây Hẹn">
            <div style="font-size: 28px; color: var(--mh-about-gold); margin-bottom: 12px;">“</div>
            <p class="mayhen_about_promise_text" style="font-size: 20px; font-weight: 600; color: var(--mh-about-primary); line-height: 1.7; margin-bottom: 16px;">
                Hẹn một ngày chung bước.<br>
                Hẹn những người thương cùng chứng kiến.<br>
                Và hẹn lưu giữ những điều đẹp đẽ ấy với Mây Hẹn.
            </p>
            <p style="font-size: 15px; color: var(--mh-about-text-muted); max-width: 680px; margin: 0 auto 20px; line-height: 1.8;">
                Ngày cưới là một dấu mốc đẹp đẽ nhất của hai bạn. Chúng tôi ở đây để đảm bảo rằng từng lời mời gửi đi, từng lời chúc nhận về đều diễn ra trơn tru, trang trọng và ngập tràn hạnh phúc.
            </p>
            <div class="mayhen_about_founder_sign">Đội Ngũ Sáng Lập &amp; Vận Hành Mây Hẹn (mayhen.vn)</div>
        </section>

        {{-- 8. CALL TO ACTION --}}
        <section class="mayhen_about_cta_card" aria-labelledby="ctaHeading">
            <h2 class="mayhen_about_cta_title" id="ctaHeading">Bắt Đầu Câu Chuyện Tình Yêu Của Bạn Ngay Hôm Nay</h2>
            <p class="mayhen_about_cta_desc">
                Chỉ mất 2 phút để chọn mẫu và ngắm nhìn tấm thiệp cưới trong mơ của hai bạn trên điện thoại. Hoàn toàn miễn phí trải nghiệm và xem trước bản hoàn chỉnh.
            </p>
            <div class="mayhen_about_cta_btn_group">
                <a href="{{ route('templates.index') }}" class="mayhen_about_btn_primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m3 9 9 6 9-6"/></svg>
                    <span>Khám Phá Toàn Bộ Mẫu Thiệp</span>
                </a>
                <a href="{{ route('invitation.create') }}" class="mayhen_about_btn_secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 2 4 4-14 14H4v-4L18 2z"/></svg>
                    <span>Tự Tạo Thiệp Ngay</span>
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
