@extends('client.layouts.master')

@section('title', 'Về Mây Hẹn (mayhen.vn) — Nền Tảng Thiệp Cưới Online Quiet Luxury Hàng Đầu')
@section('meta_description', 'Tìm hiểu câu chuyện thương hiệu, sứ mệnh và triết lý Quiet Luxury của Mây Hẹn (mayhen.vn). Giải pháp thiệp cưới online cao cấp, hiệu ứng phong bì hoàng gia, tối ưu di động và bảo mật thông tin.')
@section('meta_keywords', 'về mây hẹn, câu chuyện thương hiệu mây hẹn, thiệp cưới online mây hẹn, thiệp cưới quiet luxury, nền tảng thiệp cưới trực tuyến, mayhen.vn, thiệp cưới điện tử mây hẹn')
@section('canonical', route('about'))
@section('og_image', asset('storage/images/client/banners/banner-home.webp'))
@section('og_image_alt', 'Mây Hẹn — Thiệp Cưới Online Phong Cách Quiet Luxury')
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
            'name' => 'Về Mây Hẹn (mayhen.vn) — Nền Tảng Thiệp Cưới Online Quiet Luxury Hàng Đầu',
            'description' => 'Câu chuyện thương hiệu, sứ mệnh và triết lý nghệ thuật Quiet Luxury của Mây Hẹn trong việc nâng tầm trải nghiệm thiệp cưới online tại Việt Nam.',
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
                    'caption' => 'Mây Hẹn — Thiệp Cưới Online Cao Cấp',
                ],
                'slogan' => 'Nâng Niu Lời Hẹn Ước Ngày Chung Đôi',
                'description' => 'Nền tảng sáng tạo thiệp cưới online và giải pháp cưới kỹ thuật số cao cấp hàng đầu Việt Nam theo phong cách Quiet Luxury.',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Hà Nội',
                    'addressCountry' => 'VN'
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => '+84-969-888-999',
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
    {{-- 1. HERO SECTION --}}
    <section class="mayhen_about_hero">
        <div class="mayhen_about_hero_container">
            <span class="mayhen_about_kicker">Thương Hiệu &amp; Sứ Mệnh</span>
            <h1 class="mayhen_about_hero_title">Nâng Niu Từng Lời Hẹn Ước<br>Bằng Nghệ Thuật Thiệp Cưới Tinh Tế</h1>
            <p class="mayhen_about_hero_lead">
                Chào mừng bạn đến với <strong>Mây Hẹn (mayhen.vn)</strong> — nơi công nghệ hiện đại giao thoa cùng thẩm mỹ <em>Quiet Luxury</em>, biến từng tấm thiệp mời số thành tác phẩm nghệ thuật đong đầy cảm xúc cho ngày trọng đại nhất cuộc đời.
            </p>
            <div class="mayhen_about_hero_symbol" aria-hidden="true">✦ ✿ ✦</div>
        </div>
    </section>

    {{-- 2. STATS SECTION (CON SỐ ẤN TƯỢNG) --}}
    <section class="mayhen_about_stats_wrapper" aria-label="Những con số ấn tượng về Mây Hẹn">
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
                <div class="mayhen_about_stat_label">Tiết kiệm thời gian chuyển phát &amp; chi phí in ấn</div>
            </div>
        </div>
    </section>

    <div class="mayhen_about_container">
        {{-- 3. STORY SECTION (CÂU CHUYỆN THƯƠNG HIỆU) --}}
        <section class="mayhen_about_story_grid" aria-labelledby="storyHeading">
            <div class="mayhen_about_story_text">
                <span class="mayhen_about_kicker" style="margin-bottom: 12px;">Khởi Nguồn Ý Tưởng</span>
                <h2 id="storyHeading">Câu Chuyện Về Mây Hẹn: Khi Lời Hẹn Ước Được Nâng Niu Đúng Nghĩa</h2>
                <p>
                    Ý niệm về <strong>Mây Hẹn</strong> bắt đầu từ một câu hỏi giản dị: <em>"Tại sao trong thời đại số, những tấm thiệp mời cưới online gửi qua tin nhắn vẫn thường chỉ là những bức ảnh chụp tĩnh đơn điệu, hay những đường link thô sơ thiếu đi sự ấm áp?"</em>
                </p>
                <p>
                    Chúng tôi nhận thấy, một đám cưới trọn vẹn bắt đầu ngay từ giây phút khách mời nhận được tấm thiệp trên tay. Cảm giác bồi hồi khi lướt tay mở nắp phong thư, nhìn thấy con dấu sáp niêm phong trang trọng, ngắm nhìn nụ cười của đôi uyên ương trong nền nhạc tình ca du dương... chính là những rung cảm nguyên bản mà không công nghệ nào nên làm phai nhạt.
                </p>
                <p>
                    Từ trăn trở ấy, Mây Hẹn ra đời. Tên gọi <strong>Mây Hẹn</strong> gợi nhắc về một lời hẹn ước nhẹ nhàng, bồng bềnh và thanh khiết như mây trời, nhưng lại bền chặt và sắt son theo năm tháng.
                </p>
                <div class="mayhen_about_story_quote">
                    "Chúng tôi không chỉ số hóa một tấm thiệp cưới — chúng tôi kiến tạo không gian cảm xúc để tình yêu của hai bạn được sẻ chia trọn vẹn nhất."
                </div>
            </div>

            <div class="mayhen_about_story_card">
                <div class="mayhen_about_wax_seal_badge">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    <span>Chuẩn Thẩm Mỹ Quiet Luxury</span>
                </div>
                <h3 style="font-family: 'Playfair Display', Georgia, serif; font-size: 22px; color: var(--mh-about-text-heading); margin-bottom: 14px;">
                    Tinh Tế Từ Những Điều Nhỏ Nhất
                </h3>
                <p style="font-size: 15px; color: var(--mh-about-text-muted); margin-bottom: 18px;">
                    Tại Mây Hẹn, mỗi mẫu thiệp không chạy theo những trào lưu màu mè chớp nhoáng. Chúng tôi chắt lọc tinh hoa từ phong cách <strong>Editorial Minimalism</strong> (tạp chí tối giản) kết hợp cùng vẻ đẹp cổ điển phương Đông:
                </p>
                <ul style="padding-left: 18px; margin: 0 0 20px; font-size: 14.5px; color: var(--mh-about-text-body);">
                    <li style="margin-bottom: 8px;"><strong>Hiệu ứng mở phong bì hoàng gia:</strong> Cảm giác chạm vuốt chân thực như cầm trên tay bức thư lụa.</li>
                    <li style="margin-bottom: 8px;"><strong>Đồng bộ ngày Âm Lịch tự động:</strong> Soát chính xác ngày lành tháng tốt theo phong tục cổ truyền.</li>
                    <li style="margin-bottom: 8px;"><strong>Mã QR mừng cưới VietQR:</strong> Nhận quà mừng trực tiếp vào tài khoản cô dâu chú rể, minh bạch 100%.</li>
                    <li><strong>Xác nhận tham dự (RSVP) thông minh:</strong> Nắm bắt khẩu vị và số lượng khách tức thì.</li>
                </ul>
                <div style="font-size: 13.5px; color: var(--mh-about-gold-deep); font-weight: 600;">
                    ✦ Đồng hành cùng hơn 50.000 hôn lễ trên khắp mọi miền tổ quốc.
                </div>
            </div>
        </section>

        {{-- 4. CORE VALUES (4 TRỤ CỘT GIÁ TRỊ CỐT LÕI) --}}
        <section style="margin-bottom: 80px;" aria-labelledby="valuesHeading">
            <div class="mayhen_about_section_header">
                <span class="mayhen_about_kicker">Giá Trị Cốt Lõi</span>
                <h2 class="mayhen_about_section_title" id="valuesHeading">Bốn Nguyên Tắc Định Hình Trải Nghiệm Mây Hẹn</h2>
                <p class="mayhen_about_section_subtitle">
                    Mỗi tính năng và dòng mã tại Mây Hẹn đều được xây dựng dựa trên 4 cam kết bất biến với các cặp đôi và khách mời.
                </p>
            </div>

            <div class="mayhen_about_values_grid">
                {{-- Value 1 --}}
                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">1. Cảm Xúc Là Trọng Tâm (Heartfelt First)</h3>
                    <p class="mayhen_about_value_desc">
                        Tấm thiệp cưới không đơn thuần là lời thông báo ngày giờ, mà là kỷ vật lưu dấu tình yêu. Mây Hẹn trao cho bạn không gian kể câu chuyện hẹn hò, album 10 khoảnh khắc hạnh phúc và sổ lưu bút để người thân gửi gắm những lời chúc phúc ấm lòng.
                    </p>
                </div>

                {{-- Value 2 --}}
                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">2. Hoàn Hảo Tuyệt Đối Trên Di Động (Mobile-First)</h3>
                    <p class="mayhen_about_value_desc">
                        Hơn 95% khách mời mở thiệp qua điện thoại (Zalo, Messenger, SMS). Mọi mẫu thiệp Mây Hẹn đều được tối ưu tải trang dưới 1 giây, cuộn lướt êm ái 60fps, tự động căn chỉnh hoàn hảo trên mọi dòng máy iPhone và Android mà không bị tràn màn hình.
                    </p>
                </div>

                {{-- Value 3 --}}
                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">3. Tôn Trọng Riêng Tư (Luật 91/2025/QH15)</h3>
                    <p class="mayhen_about_value_desc">
                        Chúng tôi hiểu danh sách khách mời và số tài khoản mừng cưới là thông tin tuyệt mật. Mây Hẹn tuân thủ nghiêm ngặt Luật Bảo vệ Dữ liệu Cá nhân: mã hóa đường link cá nhân hóa, ngăn chặn công cụ tìm kiếm cào dữ liệu riêng tư và không bao giờ thương mại hóa dữ liệu khách hàng.
                    </p>
                </div>

                {{-- Value 4 --}}
                <div class="mayhen_about_value_card">
                    <div class="mayhen_about_value_icon_box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                    </div>
                    <h3 class="mayhen_about_value_title">4. Hôn Lễ Xanh &amp; Bền Vững (Green Wedding)</h3>
                    <p class="mayhen_about_value_desc">
                        Mỗi tấm thiệp cưới online gửi đi là một hành động thiết thực bảo vệ môi trường, giảm thiểu hàng triệu tấn giấy phế thải sau lễ cưới. Cùng với việc tiết kiệm đến 90% chi phí in ấn, Mây Hẹn tự hào đồng hành cùng các cặp đôi xây dựng phong cách cưới văn minh và hiện đại.
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. DESIGN PHILOSOPHY (TRIẾT LÝ QUIET LUXURY) --}}
        <section class="mayhen_about_philosophy_box" aria-labelledby="philosophyHeading">
            <div style="text-align: center; max-width: 680px; margin: 0 auto;">
                <span class="mayhen_about_kicker">Thẩm Mỹ Thiết Kế</span>
                <h2 class="mayhen_about_section_title" id="philosophyHeading">Định Nghĩa Lại Sự Sang Trọng Bằng "Quiet Luxury"</h2>
                <p style="font-size: 15.5px; color: var(--mh-about-text-muted); margin: 0; line-height: 1.8;">
                    "Quiet Luxury" không phải là sự giản đơn tẻ nhạt, mà là đẳng cấp ẩn sâu trong từng đường nét chắt lọc — nơi không cần phô trương vẫn toát lên vẻ quý phái và trang nhã.
                </p>
            </div>

            <div class="mayhen_about_philosophy_grid">
                <div class="mayhen_about_philosophy_item">
                    <h3 class="mayhen_about_philosophy_item_title">
                        <span>✿</span> Bảng Màu Kem Ấm &amp; Vàng Nhạt
                    </h3>
                    <p class="mayhen_about_philosophy_item_desc">
                        Sắc trắng ngà Ivory, kem ấm Warm Cream và ánh vàng đồng cổ điển Muted Gold tạo nên cảm giác dễ chịu, ấm cúng và gần gũi cho đôi mắt khách mời.
                    </p>
                </div>

                <div class="mayhen_about_philosophy_item">
                    <h3 class="mayhen_about_philosophy_item_title">
                        <span>✦</span> Typography Thư Pháp &amp; Serif Cổ Điển
                    </h3>
                    <p class="mayhen_about_philosophy_item_desc">
                        Kết hợp nét bút Playfair Display quý phái cùng cấu trúc Inter rõ nét, từng con chữ tên cô dâu chú rể đều được chăm chút trang nghiêm như một bức thiệp lụa tay.
                    </p>
                </div>

                <div class="mayhen_about_philosophy_item">
                    <h3 class="mayhen_about_philosophy_item_title">
                        <span>✧</span> Chuyển Động Nhung Lụa Tự Nhiên
                    </h3>
                    <p class="mayhen_about_philosophy_item_desc">
                        Không dùng hiệu ứng giật mắt, từng cánh thiệp mở ra nhẹ nhàng theo nhịp thở, đưa người xem vào một không gian lễ cưới đầy trang trọng và thiêng liêng.
                    </p>
                </div>
            </div>
        </section>

        {{-- 6. FOUNDERS' PROMISE (LỜI HỨA TỪ ĐỘI NGŨ) --}}
        <section class="mayhen_about_promise_box" aria-label="Lời cam kết từ đội ngũ Mây Hẹn">
            <div style="font-size: 26px; color: var(--mh-about-gold); margin-bottom: 12px;">“</div>
            <p class="mayhen_about_promise_text">
                Ngày cưới là cột mốc đẹp đẽ nhất của hai bạn. Chúng tôi ở đây để đảm bảo rằng từng lời mời gửi đi, từng lời chúc nhận về đều diễn ra trơn tru, trang trọng và ngập tràn hạnh phúc. Đó là lời hứa kiên định của toàn thể đội ngũ Mây Hẹn.
            </p>
            <div class="mayhen_about_founder_sign">Đội Ngũ Sáng Lập &amp; Vận Hành Mây Hẹn (mayhen.vn)</div>
        </section>

        {{-- 7. CALL TO ACTION (CTA CARD) --}}
        <section class="mayhen_about_cta_card" aria-labelledby="ctaHeading">
            <h2 class="mayhen_about_cta_title" id="ctaHeading">Bắt Đầu Câu Chuyện Tình Yêu Của Bạn Ngay Hôm Nay</h2>
            <p class="mayhen_about_cta_desc">
                Chỉ mất 2 phút để chọn mẫu và ngắm nhìn tấm thiệp cưới trong mơ của bạn trên điện thoại. Hoàn toàn miễn phí trải nghiệm và xem trước bản hoàn chỉnh.
            </p>
            <div class="mayhen_about_cta_btn_group">
                <a href="{{ route('templates.index') }}" class="mayhen_about_btn_primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m3 9 9 6 9-6"/></svg>
                    <span>Khám Phá 99+ Mẫu Thiệp Cưới</span>
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
