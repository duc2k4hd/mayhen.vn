@extends('client.layouts.master')

@section('title', 'Liên Hệ In Thiệp Cưới Cao Cấp ' . date('Y') . ' — Tư Vấn & Đặt Hẹn Xem Mẫu | ' . config('app.name', 'Mây Hẹn'))
@section('meta_description', 'Kênh liên hệ in thiệp cưới chính thức tại ' . config('app.name', 'Mây Hẹn') . '. Đặt hẹn xem mẫu giấy mỹ thuật trực tiếp tại Studio, nhận tư vấn ép kim, dập nổi và báo giá in thiệp cưới theo yêu cầu nhanh trong 15 phút.')
@section('meta_keywords', 'liên hệ in thiệp cưới, tư vấn in thiệp cưới, đặt in thiệp cưới, địa chỉ in thiệp cưới, in thiệp cưới mây hẹn, liên hệ mây hẹn, showroom thiệp cưới, báo giá in thiệp cưới')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/contact.css') }}">
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/lien-he') . '/#breadcrumb',
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
                    'name' => 'Liên hệ',
                    'item' => url('/lien-he'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => 'Liên hệ in thiệp cưới tại ' . config('app.name', 'Mây Hẹn'),
                    'item' => url('/lien-he'),
                ],
            ],
        ],
        [
            '@type' => 'ContactPage',
            '@id' => url('/lien-he') . '/#webpage',
            'url' => url('/lien-he'),
            'name' => 'Liên Hệ In Thiệp Cưới Cao Cấp — ' . config('app.name', 'Mây Hẹn'),
            'description' => 'Trang thông tin liên hệ, đặt lịch hẹn xem mẫu giấy thiệp cưới trực tiếp và yêu cầu báo giá in thiệp cưới tại ' . config('app.name', 'Mây Hẹn') . '.',
            'inLanguage' => 'vi',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => url('/') . '/#website',
                'name' => config('app.name', 'Mây Hẹn'),
                'url' => url('/'),
            ],
        ],
        [
            '@type' => 'LocalBusiness',
            '@id' => url('/') . '/#localbusiness',
            'name' => config('app.name', 'Mây Hẹn') . ' — Xưởng In & Thiết Kế Thiệp Cưới Cao Cấp',
            'url' => url('/'),
            'telephone' => '+84988000000',
            'email' => 'hotro@mayhen.vn',
            'priceRange' => '₫₫',
            'image' => asset('assets/client/images/og-share.jpg'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Tầng 3, Tòa nhà Mây Hẹn, Quận Đống Đa',
                'addressLocality' => 'Hà Nội',
                'addressRegion' => 'Hà Nội',
                'postalCode' => '100000',
                'addressCountry' => 'VN',
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'opens' => '08:30',
                    'closes' => '21:30',
                ],
            ],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => '+84988000000',
                    'contactType' => 'customer support',
                    'availableLanguage' => ['Vietnamese', 'English'],
                    'areaServed' => 'VN',
                ],
                [
                    '@type' => 'ContactPoint',
                    'email' => 'doitac@mayhen.vn',
                    'contactType' => 'partner relations',
                    'availableLanguage' => ['Vietnamese', 'English'],
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/lien-he') . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Tôi nên liên hệ đặt in thiệp cưới trước ngày cưới bao lâu là tốt nhất?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Thời điểm lý tưởng nhất để liên hệ đặt in thiệp cưới là từ 4 đến 6 tuần trước ngày cưới. Khoảng thời gian này giúp cô dâu chú rể thoải mái chọn mẫu giấy, xem bản in mẫu (proof) thực tế và gửi thiệp mời đến tay người thân, bạn bè trước ngày cưới ít nhất 2 đến 3 tuần.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Tôi ở các tỉnh thành xa có đặt in thiệp cưới tại Mây Hẹn được không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Hoàn toàn được. Mây Hẹn nhận tư vấn online 1:1 qua Zalo, gửi ảnh/video thực tế từng chất liệu giấy và có dịch vụ gửi bộ kẹp mẫu giấy (sample kit) tận nhà qua bưu điện. Sau khi in xong, thiệp cưới được bọc chống sốc kỹ lưỡng và giao hàng bảo đảm toàn quốc.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Mây Hẹn có nhận in thiệp cưới số lượng ít dưới 100 thiệp không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Có. Nhờ công nghệ in kỹ thuật số hiện đại, Mây Hẹn sẵn sàng phục vụ các đơn đặt in thiệp cưới số lượng tinh gọn từ 30 đến 50 thiệp dành riêng cho tiệc cưới thân mật (Intimate Wedding) với chất lượng sắc nét tương đương đơn hàng lớn.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Nếu tôi đã tự thiết kế file thiệp cưới thì xưởng có nhận in gia công không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Có. Đội ngũ kỹ thuật của Mây Hẹn sẽ hỗ trợ bạn kiểm tra độ phân giải, hệ màu in (CMYK), căn lề xén thành phẩm và tư vấn loại giấy mỹ thuật phù hợp nhất với bản thiết kế của bạn trước khi tiến hành in ấn.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Khi đặt in thiệp cưới tại Mây Hẹn, tôi có được tặng kèm thiệp cưới online không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Có. Mọi gói in thiệp cưới tại Mây Hẹn đều được hỗ trợ tạo website thiệp cưới online đồng bộ thiết kế, mã QR xác nhận tham dự (RSVP) và bản đồ chỉ đường thông minh để dâu rể tiện gửi qua Zalo cho bạn bè ở xa.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Mây Hẹn có hỗ trợ in tên khách mời trực tiếp lên phong bì không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Có. Chúng tôi hỗ trợ in biến đổi tên khách mời (variable data printing) trực tiếp lên phong bì với phông chữ nghệ thuật đồng bộ, giúp cô dâu chú rể tiết kiệm thời gian viết tay và giữ cho tấm thiệp luôn trang trọng, hoàn mỹ.',
                    ],
                ],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div class="mayhen_contact_page">

    {{-- Breadcrumb Navigation --}}
    <div class="mayhen_contact_breadcrumb_wrapper">
        <div class="mayhen_client_layout_container">
            <ul class="mayhen_contact_breadcrumbs">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="mayhen_contact_breadcrumb_separator">/</li>
                <li><a href="{{ route('contact') }}">Liên hệ</a></li>
                <li class="mayhen_contact_breadcrumb_separator">/</li>
                <li class="mayhen_contact_breadcrumb_active">Liên hệ in thiệp cưới tại {{ config('app.name', 'Mây Hẹn') }}</li>
            </ul>
        </div>
    </div>

    {{-- Hero Section --}}
    <section class="mayhen_contact_hero">
        <div class="mayhen_client_layout_container">
            <span class="mayhen_contact_hero_badge">
                Tư Vấn 1:1 & Đặt Hẹn Xem Mẫu Trực Tiếp
            </span>
            <h1 class="mayhen_contact_hero_title">
                Liên Hệ In Thiệp Cưới & Tư Vấn Thiết Kế Tại {{ config('app.name', 'Mây Hẹn') }}
            </h1>
            <p class="mayhen_contact_hero_lead">
                Dù bạn cần đặt in thiệp cưới cao cấp trên phôi giấy mỹ thuật dệt gân, ép kim vàng cát thủ công hay kết hợp đồng bộ cùng website thiệp cưới online hiện đại — Đội ngũ {{ config('app.name', 'Mây Hẹn') }} luôn sẵn sàng lắng nghe và báo giá chi tiết trong 15 phút.
            </p>
            <div class="mayhen_contact_hero_meta">
                <div class="mayhen_contact_hero_meta_item">
                    <span class="mayhen_contact_hero_dot"></span>
                    <span>Phản hồi Zalo / Hotline trong 15 phút</span>
                </div>
                <div class="mayhen_contact_hero_meta_item">
                    <span class="mayhen_contact_hero_dot"></span>
                    <span>In Proof mẫu thật duyệt tay trước khi chạy máy</span>
                </div>
                <div class="mayhen_contact_hero_meta_item">
                    <span class="mayhen_contact_hero_dot"></span>
                    <span>Nhận in số lượng ít từ 30 thiệp tiệc thân mật</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Fast Channels Grid --}}
    <section class="mayhen_contact_channels_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_contact_section_head">
                <span class="mayhen_contact_section_badge">Kênh Kết Nối Nhanh</span>
                <h2 class="mayhen_contact_section_title">Chọn Kênh Liên Hệ Thuận Tiện Nhất Cho Bạn</h2>
                <p class="mayhen_contact_section_desc">
                    Chúng tôi bố trí chuyên viên tư vấn riêng biệt theo từng nhu cầu để hỗ trợ bạn nhanh chóng và chuẩn xác nhất.
                </p>
            </div>

            <div class="mayhen_contact_channels_grid">
                {{-- Channel 1 --}}
                <div class="mayhen_contact_channel_card">
                    <div>
                        <div class="mayhen_contact_channel_icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <h3 class="mayhen_contact_channel_name">Tư Vấn In Ấn & Báo Giá</h3>
                        <p class="mayhen_contact_channel_desc">
                            Trao đổi trực tiếp về mẫu thiệp, chất liệu giấy, kỹ thuật ép kim và nhận bảng dự toán chi phí chi tiết theo số lượng thiệp bạn cần.
                        </p>
                    </div>
                    <a href="tel:0988000000" class="mayhen_contact_channel_action">
                        <span>Gọi Hotline: 0988.xxx.xxx</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                {{-- Channel 2 --}}
                <div class="mayhen_contact_channel_card">
                    <div>
                        <div class="mayhen_contact_channel_icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                        </div>
                        <h3 class="mayhen_contact_channel_name">Nhắn Tin Zalo Chính Thức</h3>
                        <p class="mayhen_contact_channel_desc">
                            Gửi hình ảnh mẫu bạn ưng ý, nhận video quay cận cảnh thớ giấy và được chuyên viên phản hồi liên tục từ 8:30 đến 21:30 hàng ngày.
                        </p>
                    </div>
                    <a href="https://zalo.me" target="_blank" rel="noopener noreferrer" class="mayhen_contact_channel_action">
                        <span>Chat Zalo {{ config('app.name', 'Mây Hẹn') }}</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                {{-- Channel 3 --}}
                <div class="mayhen_contact_channel_card">
                    <div>
                        <div class="mayhen_contact_channel_icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <h3 class="mayhen_contact_channel_name">Gửi File & Kiểm Tra Kỹ Thuật</h3>
                        <p class="mayhen_contact_channel_desc">
                            Nếu bạn đã có sẵn file thiết kế (AI, PDF, Canva), hãy gửi email để bộ phận chế bản kiểm tra chuẩn in offset và canh lề an toàn miễn phí.
                        </p>
                    </div>
                    <a href="mailto:hotro@mayhen.vn" class="mayhen_contact_channel_action">
                        <span>hotro@mayhen.vn</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                {{-- Channel 4 --}}
                <div class="mayhen_contact_channel_card">
                    <div>
                        <div class="mayhen_contact_channel_icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M16 12l-4-4-4 4M12 16V9"></path>
                            </svg>
                        </div>
                        <h3 class="mayhen_contact_channel_name">Đối Tác & Wedding Planner</h3>
                        <p class="mayhen_contact_channel_desc">
                            Chính sách chiết khấu đại lý hấp dẫn dành riêng cho Studio chụp ảnh cưới, chuyên viên tổ chức sự kiện tiệc cưới và nhà thiết kế tự do.
                        </p>
                    </div>
                    <a href="mailto:doitac@mayhen.vn" class="mayhen_contact_channel_action">
                        <span>doitac@mayhen.vn</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Contact Form & Commitments --}}
    <section class="mayhen_contact_main_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_contact_layout_grid">

                {{-- Left: Form --}}
                <div class="mayhen_contact_form_box">
                    <div class="mayhen_contact_form_header">
                        <h2 class="mayhen_contact_form_title">Đặt Lịch Hẹn & Yêu Cầu Báo Giá In Thiệp Cưới</h2>
                        <p class="mayhen_contact_form_lead">
                            Vui lòng điền thông tin dự kiến dưới đây. Chuyên viên của {{ config('app.name', 'Mây Hẹn') }} sẽ liên hệ lại qua số điện thoại hoặc Zalo để tư vấn và gửi báo giá chi tiết nhất.
                        </p>
                    </div>

                    <div id="mayhen_contact_alert" class="mayhen_contact_alert_success">
                        <strong>Cảm ơn bạn đã gửi thông tin!</strong> Chuyên viên tư vấn in ấn của {{ config('app.name', 'Mây Hẹn') }} đã tiếp nhận và sẽ chủ động liên hệ hỗ trợ bạn qua Zalo/Điện thoại trong vòng 15 phút.
                    </div>

                    <form id="mayhen_contact_form" onsubmit="event.preventDefault(); document.getElementById('mayhen_contact_alert').style.display='block'; this.reset();">
                        <div class="mayhen_contact_row_2col">
                            <div class="mayhen_contact_field">
                                <label class="mayhen_contact_label" for="contact_name">
                                    Họ và tên của bạn <span class="mayhen_contact_label_required">*</span>
                                </label>
                                <input type="text" id="contact_name" class="mayhen_contact_input" required placeholder="Ví dụ: Hoàng Yến & Minh Quân">
                            </div>

                            <div class="mayhen_contact_field">
                                <label class="mayhen_contact_label" for="contact_phone">
                                    Số điện thoại / Zalo <span class="mayhen_contact_label_required">*</span>
                                </label>
                                <input type="tel" id="contact_phone" class="mayhen_contact_input" required placeholder="09xxxxxxxx (Nhận báo giá qua Zalo)">
                            </div>
                        </div>

                        <div class="mayhen_contact_row_2col">
                            <div class="mayhen_contact_field">
                                <label class="mayhen_contact_label" for="contact_date">
                                    Ngày cưới dự kiến
                                </label>
                                <input type="date" id="contact_date" class="mayhen_contact_input">
                                <span class="mayhen_contact_help_text">Giúp chúng tôi cân đối lịch in để giao thiệp đúng hẹn.</span>
                            </div>

                            <div class="mayhen_contact_field">
                                <label class="mayhen_contact_label" for="contact_quantity">
                                    Số lượng thiệp dự kiến
                                </label>
                                <select id="contact_quantity" class="mayhen_contact_select">
                                    <option value="30-100">Dưới 100 thiệp (Tiệc thân mật / in số lượng ít)</option>
                                    <option value="100-250" selected>Từ 100 đến 250 thiệp (Quy mô phổ biến)</option>
                                    <option value="250-400">Từ 250 đến 400 thiệp</option>
                                    <option value="400+">Trên 400 thiệp (Ưu đãi chiết khấu xưởng)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mayhen_contact_field">
                            <label class="mayhen_contact_label" for="contact_need">
                                Nhu cầu tư vấn chính <span class="mayhen_contact_label_required">*</span>
                            </label>
                            <select id="contact_need" class="mayhen_contact_select" required>
                                <option value="print-high-end">Tư vấn in thiệp cưới cao cấp (Ép kim, giấy dệt gân, tem sáp)</option>
                                <option value="print-minimal">Tư vấn in thiệp cưới phong cách Tối giản (Minimalist / Thanh lịch)</option>
                                <option value="print-fast">Cần in thiệp cưới gấp trong 3 - 5 ngày làm việc</option>
                                <option value="visit-studio">Đặt hẹn đến xem mẫu giấy trực tiếp tại Studio</option>
                                <option value="combo-online">Tư vấn gói đồng bộ Thiệp in truyền thống + Thiệp cưới online</option>
                                <option value="partner">Hợp tác Studio / Wedding Planner</option>
                            </select>
                        </div>

                        <div class="mayhen_contact_field">
                            <label class="mayhen_contact_label" for="contact_message">
                                Ghi chú thêm về ý tưởng của bạn
                            </label>
                            <textarea id="contact_message" class="mayhen_contact_textarea" placeholder="Ví dụ: Bạn mong muốn tone màu be nhạt, phong bì dập nổi họ tên cô dâu chú rể, hoặc cần in mẫu thử trước khi in hàng loạt..."></textarea>
                        </div>

                        <button type="submit" class="mayhen_contact_submit_btn">
                            <span>Gửi Yêu Cầu Báo Giá & Đặt Hẹn</span>
                            <span>&rarr;</span>
                        </button>

                        <p class="mayhen_contact_form_guarantee">
                            🔒 Thông tin của bạn được bảo mật tuyệt đối và chỉ dùng để gửi báo giá theo yêu cầu.
                        </p>
                    </form>
                </div>

                {{-- Right: Perks & Direct Assistance --}}
                <div class="mayhen_contact_side_wrapper">
                    <div class="mayhen_contact_perks_card">
                        <h3 class="mayhen_contact_perks_title">
                            Đặc Quyền Khi Đặt In Thiệp Cưới Tại {{ config('app.name', 'Mây Hẹn') }}
                        </h3>
                        <ul class="mayhen_contact_perks_list">
                            <li class="mayhen_contact_perk_item">
                                <span class="mayhen_contact_perk_icon">&#10003;</span>
                                <div class="mayhen_contact_perk_text">
                                    <strong>In Proof Duyệt Mẫu Thực Tế Miễn Phí</strong>
                                    <p>Bạn được tận mắt ngắm nhìn và chạm tay vào bản in thật trước khi xưởng tiến hành in toàn bộ số lượng.</p>
                                </div>
                            </li>
                            <li class="mayhen_contact_perk_item">
                                <span class="mayhen_contact_perk_icon">&#10003;</span>
                                <div class="mayhen_contact_perk_text">
                                    <strong>Cam Kết Chuẩn Màu & Đền Bù 100%</strong>
                                    <p>Nếu sản phẩm xuất hiện lỗi sai lệch màu sắc hoặc lỗi gia công do xưởng, chúng tôi cam kết in lại ngay lập tức.</p>
                                </div>
                            </li>
                            <li class="mayhen_contact_perk_item">
                                <span class="mayhen_contact_perk_icon">&#10003;</span>
                                <div class="mayhen_contact_perk_text">
                                    <strong>Tặng Kèm Website Thiệp Cưới Online</strong>
                                    <p>Đồng bộ phong cách thiệp in với trang thiệp online tích hợp mã QR chuyển khoản và sơ đồ chỉ đường GPS.</p>
                                </div>
                            </li>
                            <li class="mayhen_contact_perk_item">
                                <span class="mayhen_contact_perk_icon">&#10003;</span>
                                <div class="mayhen_contact_perk_text">
                                    <strong>Đóng Gói Chống Nước & Giao Hàng Toàn Quốc</strong>
                                    <p>Từng hộp thiệp được đóng gói tỉ mỉ, lót bọt khí chuyên dụng giúp giữ nguyên nếp thiệp phẳng phiu khi đến tay.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mayhen_contact_direct_call_box">
                        <span class="mayhen_contact_direct_tag">Cần Tư Vấn Khẩn Cấp?</span>
                        <h4 class="mayhen_contact_direct_title">Trao Đổi Trực Tiếp Cùng Quản Lý Xưởng</h4>
                        <p class="mayhen_contact_direct_desc">
                            Nếu ngày cưới đã cận kề và bạn cần xử lý in thiệp cưới lấy nhanh hoặc cần báo giá hợp đồng số lượng lớn, hãy gọi ngay cho chúng tôi:
                        </p>
                        <a href="tel:0988000000" class="mayhen_contact_direct_btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span>Hotline: 0988.xxx.xxx (8:30 - 21:30)</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 4-Step Process Section --}}
    <section class="mayhen_contact_process_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_contact_section_head">
                <span class="mayhen_contact_section_badge">Quy Trình Chuyên Nghiệp</span>
                <h2 class="mayhen_contact_section_title">4 Bước Từ Tiếp Nhận Đến Trao Tay Tấm Thiệp Hoàn Mỹ</h2>
                <p class="mayhen_contact_section_desc">
                    Chúng tôi tinh giản tối đa các bước đặt hàng để bạn luôn chủ động nắm bắt tiến độ mà không mất nhiều thời gian đi lại.
                </p>
            </div>

            <div class="mayhen_contact_process_grid">
                <div class="mayhen_contact_process_card">
                    <span class="mayhen_contact_process_num">01</span>
                    <h3 class="mayhen_contact_process_heading">Tiếp Nhận & Báo Giá</h3>
                    <p class="mayhen_contact_process_content">
                        Lắng nghe ý tưởng, ngân sách và thời gian tổ chức tiệc. Chuyên viên gửi bảng dự toán chi phí chi tiết từng hạng mục chỉ trong 15 phút.
                    </p>
                </div>

                <div class="mayhen_contact_process_card">
                    <span class="mayhen_contact_process_num">02</span>
                    <h3 class="mayhen_contact_process_heading">Tư Vấn & Chọn Mẫu Giấy</h3>
                    <p class="mayhen_contact_process_content">
                        Gợi ý các dòng giấy mỹ thuật phù hợp (Linen, Cotton, Kraft Ý) và tư vấn kỹ thuật gia công ép nhũ, dập nổi hoặc bế bo góc tinh tế.
                    </p>
                </div>

                <div class="mayhen_contact_process_card">
                    <span class="mayhen_contact_process_num">03</span>
                    <h3 class="mayhen_contact_process_heading">Lên Layout & In Proof</h3>
                    <p class="mayhen_contact_process_content">
                        Lên market thiết kế chính xác từng chữ, dấu câu và tiến hành in thử 1 bộ mẫu thật để bạn duyệt độ sắc nét trước khi in hàng loạt.
                    </p>
                </div>

                <div class="mayhen_contact_process_card">
                    <span class="mayhen_contact_process_num">04</span>
                    <h3 class="mayhen_contact_process_heading">In Ấn & Đóng Gói Trao Tay</h3>
                    <p class="mayhen_contact_process_content">
                        Chạy máy in công nghệ cao, gia công hoàn thiện, kiểm tra KCS từng tấm thiệp và đóng hộp sang trọng chuyển đến tận tay bạn.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Studio & Location Section --}}
    <section class="mayhen_contact_studio_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_contact_studio_box">
                <div class="mayhen_contact_studio_info">
                    <span class="mayhen_contact_section_badge">Không Gian Trải Nghiệm</span>
                    <h2 class="mayhen_contact_studio_title">Ghé Thăm Studio Của {{ config('app.name', 'Mây Hẹn') }}</h2>
                    <p class="mayhen_contact_studio_desc">
                        Không có gì tuyệt vời hơn việc được ngồi lại cùng người bạn đời, thưởng thức một tách trà thơm và tự tay chạm vào từng vân giấy mỹ thuật để chọn ra tấm thiệp mang đậm dấu ấn riêng của hai bạn.
                    </p>

                    <ul class="mayhen_contact_studio_list">
                        <li class="mayhen_contact_studio_item">
                            <span class="mayhen_contact_studio_icon">📍</span>
                            <div>
                                <strong>Địa chỉ Studio:</strong>
                                <div>Tầng 3, Tòa nhà {{ config('app.name', 'Mây Hẹn') }}, Quận Đống Đa, TP. Hà Nội</div>
                            </div>
                        </li>
                        <li class="mayhen_contact_studio_item">
                            <span class="mayhen_contact_studio_icon">🕒</span>
                            <div>
                                <strong>Giờ mở cửa đón tiếp:</strong>
                                <div>08:30 – 21:00 (Mở cửa tất cả các ngày trong tuần, kể cả Thứ 7 & Chủ Nhật)</div>
                            </div>
                        </li>
                        <li class="mayhen_contact_studio_item">
                            <span class="mayhen_contact_studio_icon">🚗</span>
                            <div>
                                <strong>Chỉ dẫn tiện ích:</strong>
                                <div>Có chỗ để xe máy và bãi đỗ ô tô rộng rãi, an toàn, có bảo vệ hỗ trợ hướng dẫn.</div>
                            </div>
                        </li>
                    </ul>

                    <div>
                        <a href="#mayhen_contact_form" class="mayhen_contact_submit_btn" style="display: inline-flex; width: auto; text-decoration: none;">
                            <span>Đặt Lịch Hẹn Tư Vấn 1:1 Tại Studio</span>
                        </a>
                    </div>
                </div>

                <div class="mayhen_contact_studio_map">
                    <div class="mayhen_contact_map_placeholder">
                        <span class="mayhen_contact_map_badge">Vị Trí Trung Tâm Thuận Tiện</span>
                        <h4 style="font-family: var(--mayhen-font-serif); font-size: 1.25rem; margin-bottom: 0.5rem;">Không Gian Trưng Bày Hơn 200+ Mẫu Thiệp Thật</h4>
                        <p style="font-size: var(--mayhen-font-size-small); color: var(--mayhen-color-text-muted); line-height: 1.6; margin-bottom: 1rem;">
                            Studio của chúng tôi luôn sẵn sàng chào đón bạn ghé thăm để trực tiếp chiêm ngưỡng các bộ sưu tập thiệp cưới mới nhất năm {{ date('Y') }}.
                        </p>
                        <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="mayhen_contact_channel_action" style="border: none; padding: 0;">
                            <span>Mở chỉ đường trên Google Maps &rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="mayhen_contact_faq_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_contact_section_head">
                <span class="mayhen_contact_section_badge">Giải Đáp Thắc Mắc</span>
                <h2 class="mayhen_contact_section_title">Câu Hỏi Thường Gặp Khi Đặt In Thiệp Cưới</h2>
                <p class="mayhen_contact_section_desc">
                    Tổng hợp những câu hỏi dâu rể thường quan tâm nhất trước khi quyết định đặt in thiệp cưới tại {{ config('app.name', 'Mây Hẹn') }}.
                </p>
            </div>

            <div class="mayhen_contact_faq_list">
                <div class="mayhen_contact_faq_item">
                    <h3 class="mayhen_contact_faq_question">1. Tôi nên liên hệ đặt in thiệp cưới trước ngày cưới bao lâu là tốt nhất?</h3>
                    <p class="mayhen_contact_faq_answer">
                        Thời điểm lý tưởng nhất để liên hệ đặt in thiệp cưới là từ <strong>4 đến 6 tuần</strong> trước ngày cưới. Khoảng thời gian này giúp cô dâu chú rể thoải mái chọn mẫu giấy, duyệt bản in mẫu (proof) thực tế và gửi thiệp mời đến tay người thân, bạn bè trước ngày cưới ít nhất 2 đến 3 tuần.
                    </p>
                </div>

                <div class="mayhen_contact_faq_item">
                    <h3 class="mayhen_contact_faq_question">2. Tôi ở các tỉnh thành xa có đặt in thiệp cưới tại Mây Hẹn được không?</h3>
                    <p class="mayhen_contact_faq_answer">
                        Hoàn toàn được. {{ config('app.name', 'Mây Hẹn') }} nhận tư vấn online 1:1 qua Zalo, gửi video quay cận cảnh từng loại phôi giấy và có dịch vụ <strong>gửi bộ kẹp mẫu giấy (sample kit) tận nhà</strong>. Sau khi hoàn thiện, toàn bộ thiệp cưới được bọc chống sốc kỹ lưỡng và chuyển phát nhanh bảo đảm trên toàn quốc.
                    </p>
                </div>

                <div class="mayhen_contact_faq_item">
                    <h3 class="mayhen_contact_faq_question">3. Mây Hẹn có nhận in thiệp cưới số lượng ít dưới 100 thiệp không?</h3>
                    <p class="mayhen_contact_faq_answer">
                        Có. Với hệ thống máy in kỹ thuật số tiên tiến, chúng tôi phục vụ linh hoạt các đơn đặt in số lượng tinh gọn từ <strong>30 đến 50 thiệp</strong> dành riêng cho các buổi tiệc cưới thân mật (Intimate Wedding) với độ nét và màu sắc chuẩn xác như các đơn hàng lớn.
                    </p>
                </div>

                <div class="mayhen_contact_faq_item">
                    <h3 class="mayhen_contact_faq_question">4. Nếu tôi đã có sẵn file tự thiết kế thì xưởng có nhận in gia công không?</h3>
                    <p class="mayhen_contact_faq_answer">
                        Có. Bộ phận chế bản đồ họa của {{ config('app.name', 'Mây Hẹn') }} sẽ hỗ trợ bạn kiểm tra độ phân giải, hệ màu in ấn (CMYK) và căn chỉnh lề an toàn hoàn toàn miễn phí, đồng thời gợi ý loại giấy mỹ thuật phù hợp nhất để tác phẩm của bạn hiển thị đẹp nhất khi in ra.
                    </p>
                </div>

                <div class="mayhen_contact_faq_item">
                    <h3 class="mayhen_contact_faq_question">5. Khi đặt in thiệp cưới thì có được tặng kèm thiệp cưới online không?</h3>
                    <p class="mayhen_contact_faq_answer">
                        Có. Mọi gói in thiệp cưới tại {{ config('app.name', 'Mây Hẹn') }} đều được tặng kèm giải pháp thiệp cưới online tương thích: cùng phong cách thiết kế, tích hợp mã QR chuyển khoản mừng cưới thông minh và bản đồ chỉ đường GPS để bạn gửi cho bạn bè ở xa.
                    </p>
                </div>

                <div class="mayhen_contact_faq_item">
                    <h3 class="mayhen_contact_faq_question">6. Mây Hẹn có hỗ trợ in tên khách mời trực tiếp lên phong bì không?</h3>
                    <p class="mayhen_contact_faq_answer">
                        Có. Chúng tôi cung cấp dịch vụ in biến đổi tên khách mời (variable printing) lên từng phong bì thiệp với font chữ nghệ thuật đồng điệu, giúp bạn tiết kiệm hàng giờ viết tay nắn nót và hạn chế tối đa sai sót khi viết thiệp.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA Banner --}}
    <section class="mayhen_contact_cta_section">
        <div class="mayhen_client_layout_container">
            <div class="mayhen_contact_cta_box">
                <h2 class="mayhen_contact_cta_title">Cùng {{ config('app.name', 'Mây Hẹn') }} Khởi Tạo Tấm Thiệp Cưới Đáng Nhớ</h2>
                <p class="mayhen_contact_cta_lead">
                    Hãy để tấm thiệp cưới thay bạn gửi gắm câu chuyện tình yêu ngọt ngào và sự trân quý sâu sắc nhất đến những người thân yêu.
                </p>
                <div class="mayhen_contact_cta_btns">
                    <a href="#mayhen_contact_form" class="mayhen_contact_btn_primary">
                        <span>Nhận Báo Giá In Thiệp Cưới Ngay</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('templates.index') }}" class="mayhen_contact_btn_outline">
                        <span>Khám Phá Các Mẫu Thiệp Mới</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

