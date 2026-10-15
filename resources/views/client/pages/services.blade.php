@extends('client.layouts.master')

@section('title', 'Dịch Vụ In Thiệp Cưới Cao Cấp ' . date('Y') . ' — In Thiệp Cưới Đẹp, Tinh Tế | ' . config('app.name'))
@section('meta_description', 'Dịch vụ in thiệp cưới cao cấp, tinh tế tại ' . config('app.name') . '. Đa dạng mẫu in thiệp cưới: ép kim, dập nổi, phôi giấy mỹ thuật nhập khẩu. Nhận in số lượng linh hoạt, hỗ trợ tạo thiệp cưới online đồng bộ.')
@section('meta_keywords', 'in thiệp cưới, dịch vụ in thiệp cưới, in thiệp cưới đẹp, giá in thiệp cưới, in thiệp cưới cao cấp, in thiệp cưới số lượng ít, in thiệp cưới theo yêu cầu, ' . mb_strtolower(config('app.name')))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/dich-vu') . '/#breadcrumb',
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
                    'name' => 'Dịch vụ',
                    'item' => url('/dich-vu'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => 'Dịch vụ in thiệp cưới',
                    'item' => url('/dich-vu'),
                ],
            ],
        ],
        [
            '@type' => 'Service',
            '@id' => url('/dich-vu') . '/#service',
            'name' => 'Dịch Vụ In Thiệp Cưới Cao Cấp ' . config('app.name'),
            'serviceType' => 'In thiệp cưới & Thiết kế thiệp cưới',
            'provider' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => url('/'),
            ],
            'description' => 'Dịch vụ in thiệp cưới cao cấp, phôi giấy mỹ thuật dệt gân, ép kim vàng cát, phong thư tem sáp thủ công kết hợp giải pháp thiệp cưới điện tử hiện đại.',
            'areaServed' => 'VN',
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Các gói dịch vụ in thiệp cưới',
                'itemListElement' => [
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'In thiệp cưới tối giản Minimalist',
                        ],
                    ],
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'In thiệp cưới ép kim Foil Stamping',
                        ],
                    ],
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'In thiệp cưới phong thư hoàng gia Wax Seal',
                        ],
                    ],
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/dich-vu') . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Nên đặt in thiệp cưới trước ngày cưới bao lâu là hợp lý nhất?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Thời điểm lý tưởng nhất để đặt in thiệp cưới là từ 1 đến 2 tháng trước ngày cưới. Khoảng thời gian này giúp bạn thảnh thơi duyệt ma-két thiết kế, in thử bản mẫu thực tế, gia công ép kim thủ công và gửi thiệp mời tận tay họ hàng, bạn bè trước ngày cưới ít nhất 2 đến 3 tuần.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Dịch vụ in thiệp cưới có nhận in số lượng ít (dưới 100 thiệp) không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Có. Chúng tôi nhận in thiệp cưới linh hoạt từ 50 thiệp trở lên cho các đám cưới ấm cúng hoặc tiệc báo hỷ nhỏ. Các phôi thiệp đều được căn chỉnh kỹ lưỡng từng bản in dù số lượng ít.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Giá in thiệp cưới đã bao gồm phong bì và in tên khách mời chưa?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Tất cả các gói dịch vụ in thiệp cưới đều đã bao gồm trọn bộ: thiệp chính, phong bì nắp nhọn cao cấp và công in ấn hoàn thiện. Dịch vụ in trực tiếp tên khách mời lên từng phong bì cũng được hỗ trợ đồng bộ theo danh sách của bạn.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Tôi có được xem và duyệt bản in mẫu thực tế trước khi in toàn bộ không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Chắc chắn có. Trước khi tiến hành in ấn và gia công hàng loạt, bạn sẽ được duyệt bản maket đồ họa chi tiết và nhận bản in mẫu thực tế để kiểm tra chất giấy, độ nét của nét chữ và màu sắc ép kim.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Nếu cần in thêm thiệp cưới phát sinh thì thời gian xử lý mất bao lâu?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Do khuôn in và file ma-két của bạn đã được lưu sẵn trên hệ thống, các đơn hàng in thêm thiệp cưới phát sinh sẽ được ưu tiên sản xuất và bàn giao chỉ trong vòng 2 đến 3 ngày làm việc.',
                    ],
                ],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/services.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_service_wrapper">
        <!-- ==========================================
             1. HERO HEADER
             ========================================== -->
        <header class="mayhen_service_header">
            <span class="mayhen_service_kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                Dịch Vụ In Thiệp Cưới Cao Cấp {{ date('Y') }}
            </span>
            <h1 class="mayhen_service_title">
                Dịch Vụ In Thiệp Cưới Tinh Tế & Sang Trọng <br>
                <span class="mayhen_service_title_accent">Tại {{ config('app.name') }}</span>
            </h1>
            <p class="mayhen_service_desc">
                Chúng tôi cung cấp giải pháp in thiệp cưới trọn gói với chất liệu giấy mỹ thuật cao cấp, kỹ thuật ép kim và dập nổi thủ công tỉ mỉ, kết hợp đồng bộ cùng công nghệ thiệp cưới online thời thượng.
            </p>

            <div class="mayhen_service_trust_strip">
                <div class="mayhen_service_trust_item">
                    <svg class="mayhen_service_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Giấy mỹ thuật 300 - 450gsm</span>
                </div>
                <div class="mayhen_service_trust_item">
                    <svg class="mayhen_service_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Ép kim & dập nổi sắc nét</span>
                </div>
                <div class="mayhen_service_trust_item">
                    <svg class="mayhen_service_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Nhận in từ 50 thiệp</span>
                </div>
                <div class="mayhen_service_trust_item">
                    <svg class="mayhen_service_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Tặng kèm thiệp cưới online</span>
                </div>
            </div>
        </header>

        <!-- ==========================================
             2. CÁC GÓI IN THIỆP CƯỚI ĐẶC TRƯNG
             ========================================== -->
        <section class="mayhen_service_section" aria-labelledby="cac-goi-in-heading">
            <div class="mayhen_service_section_header">
                <span class="mayhen_service_section_kicker">Phong Cách Đa Dạng</span>
                <h2 id="cac-goi-in-heading" class="mayhen_service_section_title">
                    Các Gói Dịch Vụ In Thiệp Cưới Phổ Biến
                </h2>
                <p class="mayhen_service_section_desc">
                    Lựa chọn phong cách in thiệp cưới phản ánh đúng cá tính và câu chuyện tình yêu của hai bạn.
                </p>
            </div>

            <div class="mayhen_service_packages_grid">
                <!-- Gói 1: Tối Giản -->
                <article class="mayhen_service_package_card">
                    <span class="mayhen_service_package_kicker">Phong Cách Tinh Gọn</span>
                    <h3 class="mayhen_service_package_title">In Thiệp Cưới Tối Giản</h3>
                    <p class="mayhen_service_package_desc">
                        Tập trung vào vẻ đẹp thuần khiết của kiểu chữ (typography), khoảng trắng trang nhã và phôi giấy mỹ thuật có vân gân tự nhiên.
                    </p>

                    <div class="mayhen_service_package_price_wrap">
                        <div class="mayhen_service_package_price_label">Giá in thiệp cưới từ</div>
                        <div class="mayhen_service_package_price">3.500đ <span style="font-size: 1rem; font-weight: normal; color: var(--mayhen-color-text-muted);">/ thiệp</span></div>
                    </div>

                    <div class="mayhen_service_package_features">
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Giấy mỹ thuật dệt gân nhập khẩu 300gsm</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>In kỹ thuật số độ phân giải cao sắc nét</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Bao gồm phong bì nắp nhọn trang nhã</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Tặng thiệp cưới online đồng bộ giao diện</span>
                        </div>
                    </div>

                    <a href="{{ route('contact') }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg" style="width: 100%; justify-content: center;">
                        Tư Vấn Mẫu Tối Giản
                    </a>
                </article>

                <!-- Gói 2: Ép Kim Sang Trọng (Featured) -->
                <article class="mayhen_service_package_card mayhen_service_package_featured">
                    <div class="mayhen_service_package_badge">Được Yêu Thích Nhất</div>
                    <span class="mayhen_service_package_kicker">Chuẩn Quiet Luxury</span>
                    <h3 class="mayhen_service_package_title">In Thiệp Cưới Ép Kim</h3>
                    <p class="mayhen_service_package_desc">
                        Điểm nhấn ép kim nhiệt vàng cát, bạc ánh trăng hoặc đồng cổ sang trọng trên nền giấy cotton dày dặn mang lại vẻ đẹp quyền quý.
                    </p>

                    <div class="mayhen_service_package_price_wrap">
                        <div class="mayhen_service_package_price_label">Giá in thiệp cưới từ</div>
                        <div class="mayhen_service_package_price">5.200đ <span style="font-size: 1rem; font-weight: normal; color: var(--mayhen-color-text-muted);">/ thiệp</span></div>
                    </div>

                    <div class="mayhen_service_package_features">
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Giấy mỹ thuật Cotton dệt nổi 350 - 400gsm</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Ép kim nhiệt tên cô dâu chú rể & họa tiết viền</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Phong bì ép kim nắp kèm lót hoa văn tinh tế</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Tặng thiệp cưới online gói Pro VIP (299.000đ)</span>
                        </div>
                    </div>

                    <a href="{{ route('contact') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg" style="width: 100%; justify-content: center;">
                        Đặt In Gói Ép Kim
                    </a>
                </article>

                <!-- Gói 3: Phong Thư Hoàng Gia -->
                <article class="mayhen_service_package_card">
                    <span class="mayhen_service_package_kicker">Đẳng Cấp Thủ Công</span>
                    <h3 class="mayhen_service_package_title">In Thiệp Hoàng Gia Tem Sáp</h3>
                    <p class="mayhen_service_package_desc">
                        Bộ thiệp cao cấp đa lớp với kỹ thuật dập nổi viền xơ, nơ lụa tơ tằm và con dấu sáp niêm phong phong thư mang hơi thở quý tộc châu Âu.
                    </p>

                    <div class="mayhen_service_package_price_wrap">
                        <div class="mayhen_service_package_price_label">Giá in thiệp cưới từ</div>
                        <div class="mayhen_service_package_price">8.500đ <span style="font-size: 1rem; font-weight: normal; color: var(--mayhen-color-text-muted);">/ thiệp</span></div>
                    </div>

                    <div class="mayhen_service_package_features">
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Giấy mỹ thuật cao cấp 450gsm cạnh viền xơ mộc</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Kèm dấu sáp đồng khắc tên lồng monogram</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Thẻ can mờ trong suốt lót lịch trình & dress code</span>
                        </div>
                        <div class="mayhen_service_package_feature_item">
                            <svg class="mayhen_service_package_check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>Bộ thiệp cưới online cao cấp có hiệu ứng mở nắp sáp</span>
                        </div>
                    </div>

                    <a href="{{ route('contact') }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg" style="width: 100%; justify-content: center;">
                        Tư Vấn Mẫu Hoàng Gia
                    </a>
                </article>
            </div>
        </section>

        <!-- ==========================================
             3. TIÊU CHUẨN CHẤT LIỆU & GIA CÔNG
             ========================================== -->
        <section class="mayhen_service_section" aria-labelledby="chat-lieu-heading">
            <div class="mayhen_service_section_header">
                <span class="mayhen_service_section_kicker">Chất Lượng Vượt Trội</span>
                <h2 id="chat-lieu-heading" class="mayhen_service_section_title">
                    Chất Liệu Giấy & Kỹ Thuật Gia Công In Thiệp Cưới
                </h2>
                <p class="mayhen_service_section_desc">
                    Từng tấm thiệp cầm trên tay đều mang lại cảm giác dày dặn, đầm chắc và tinh tế đến từng chi tiết nhỏ.
                </p>
            </div>

            <div class="mayhen_service_specs_grid">
                <div class="mayhen_service_spec_card">
                    <div class="mayhen_service_spec_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <h3 class="mayhen_service_spec_title">Phôi Giấy Mỹ Thuật Ý</h3>
                    <p class="mayhen_service_spec_desc">
                        Giấy nhập khẩu chất lượng cao với định lượng từ 300gsm đến 450gsm, bề mặt dệt vân sần tự nhiên, chống ố vàng và giữ màu mực sắc nét qua nhiều năm.
                    </p>
                </div>

                <div class="mayhen_service_spec_card">
                    <div class="mayhen_service_spec_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </div>
                    <h3 class="mayhen_service_spec_title">Ép Kim Nhũ Nhiệt Sắc Nét</h3>
                    <p class="mayhen_service_spec_desc">
                        Kỹ thuật ép nhũ nhiệt cao tần giúp ánh kim bám chắc, không bong tróc. Đa dạng tông màu nhũ thời thượng: nhũ vàng cát, nhũ đồng cổ và nhũ bạc ánh kim.
                    </p>
                </div>

                <div class="mayhen_service_spec_card">
                    <div class="mayhen_service_spec_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <h3 class="mayhen_service_spec_title">Dập Nổi & Dập Chìm Thủ Công</h3>
                    <p class="mayhen_service_spec_desc">
                        Tạo chiều sâu 3D trên bề mặt thiệp không cần dùng màu mực, làm nổi bật tên lồng lãng mạn hoặc họa tiết hoa lá với độ hoàn thiện hoàn mỹ.
                    </p>
                </div>

                <div class="mayhen_service_spec_card">
                    <div class="mayhen_service_spec_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h3 class="mayhen_service_spec_title">Dấu Sáp & Nơ Tơ Tằm</h3>
                    <p class="mayhen_service_spec_desc">
                        Từng con tem sáp được đúc thủ công bằng tay với phôi đồng sắc sảo, kết hợp cùng dải ruy băng voan lụa nhẹ nhàng tạo nên vẻ đẹp vượt thời gian.
                    </p>
                </div>
            </div>
        </section>

        <!-- ==========================================
             4. BẢNG BÁO GIÁ THAM KHẢO THEO SỐ LƯỢNG
             ========================================== -->
        <section class="mayhen_service_section" aria-labelledby="bao-gia-in-heading">
            <div class="mayhen_service_section_header">
                <span class="mayhen_service_section_kicker">Báo Giá Rõ Ràng</span>
                <h2 id="bao-gia-in-heading" class="mayhen_service_section_title">
                    Bảng Báo Giá Dịch Vụ In Thiệp Cưới Tham Khảo
                </h2>
                <p class="mayhen_service_section_desc">
                    Mức giá in thiệp cưới tối ưu theo số lượng in thực tế của tiệc cưới. Số lượng in càng nhiều, đơn giá trên mỗi thiệp càng tiết kiệm.
                </p>
            </div>

            <div class="mayhen_service_table_wrapper">
                <table class="mayhen_service_table">
                    <thead>
                        <tr>
                            <th>Số Lượng Thiệp In</th>
                            <th>Gói In Tối Giản</th>
                            <th>Gói In Ép Kim Nổi</th>
                            <th>Gói Hoàng Gia Tem Sáp</th>
                            <th>Thời Gian Bàn Giao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>50 – 150 thiệp</strong> (Số lượng ít)</td>
                            <td class="mayhen_service_table_highlight">4.500đ – 5.500đ</td>
                            <td class="mayhen_service_table_highlight">6.800đ – 7.800đ</td>
                            <td class="mayhen_service_table_highlight">9.500đ – 11.500đ</td>
                            <td>3 – 5 ngày làm việc</td>
                        </tr>
                        <tr>
                            <td><strong>200 – 350 thiệp</strong> (Tiêu chuẩn)</td>
                            <td class="mayhen_service_table_highlight">3.800đ – 4.500đ</td>
                            <td class="mayhen_service_table_highlight">5.500đ – 6.500đ</td>
                            <td class="mayhen_service_table_highlight">8.500đ – 9.800đ</td>
                            <td>4 – 7 ngày làm việc</td>
                        </tr>
                        <tr>
                            <td><strong>400 – 600 thiệp</strong> (Hai họ)</td>
                            <td class="mayhen_service_table_highlight">3.200đ – 3.800đ</td>
                            <td class="mayhen_service_table_highlight">4.800đ – 5.500đ</td>
                            <td class="mayhen_service_table_highlight">7.500đ – 8.500đ</td>
                            <td>5 – 8 ngày làm việc</td>
                        </tr>
                        <tr>
                            <td><strong>700 – 1.000+ thiệp</strong> (Quy mô lớn)</td>
                            <td class="mayhen_service_table_highlight">2.800đ – 3.200đ</td>
                            <td class="mayhen_service_table_highlight">4.200đ – 4.800đ</td>
                            <td class="mayhen_service_table_highlight">6.800đ – 7.500đ</td>
                            <td>7 – 10 ngày làm việc</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="text-align: center; font-size: var(--mayhen-font-size-small); color: var(--mayhen-color-text-light); margin-top: var(--mayhen-space-md);">
                * Đơn giá đã bao gồm: Thiết kế ma-két, phôi giấy mỹ thuật, in ruột thiệp, phong bì cao cấp và in tên khách mời theo danh sách.
            </p>
        </section>

        <!-- ==========================================
             5. XU HƯỚNG MỚI: KẾT HỢP IN THIỆP GIẤY & THIỆP ONLINE
             ========================================== -->
        <section class="mayhen_service_combo_box" aria-labelledby="giai-phap-kep-heading">
            <div class="mayhen_service_combo_header">
                <span class="mayhen_service_combo_badge">Xu Hướng Cưới {{ date('Y') }}</span>
                <h2 id="giai-phap-kep-heading" class="mayhen_service_combo_title">
                    Giải Pháp Kép: In Thiệp Cưới Truyền Thống + Thiệp Cưới Online
                </h2>
                <p class="mayhen_service_combo_desc">
                    Sự kết hợp hoàn hảo giữa nét văn hóa mời thiệp giấy trang trọng và sự tiện lợi vượt trội của công nghệ hiện đại.
                </p>
            </div>

            <div class="mayhen_service_combo_grid">
                <div class="mayhen_service_combo_card">
                    <div class="mayhen_service_combo_card_tag">Thiệp Cưới In Giấy</div>
                    <h3 class="mayhen_service_combo_card_title">Gửi Tặng Người Lớn Tuổi & Họ Hàng</h3>
                    <p class="mayhen_service_combo_card_text">
                        Những tấm thiệp giấy ép kim cao cấp trao tay thể hiện trọn vẹn sự hiếu kính, lễ nghĩa với gia tiên, ông bà, cô bác lớn tuổi và các đối tác quan trọng của phụ mẫu hai bên gia đình.
                    </p>
                </div>

                <div class="mayhen_service_combo_card">
                    <div class="mayhen_service_combo_card_tag">Thiệp Cưới Online {{ config('app.name') }}</div>
                    <h3 class="mayhen_service_combo_card_title">Gửi Nhanh Cho Bạn Bè & Đồng Nghiệp</h3>
                    <p class="mayhen_service_combo_card_text">
                        Đường dẫn thiệp cưới trực tuyến gửi qua Zalo hay Messenger có kèm âm nhạc, album ảnh cưới, bản đồ Google Maps và nút xác nhận tham dự (RSVP), giúp bạn nắm rõ số khách để đặt cỗ bàn chuẩn xác.
                    </p>
                </div>
            </div>
        </section>

        <!-- ==========================================
             6. QUY TRÌNH ĐẶT DỊCH VỤ IN THIỆP CƯỚI
             ========================================== -->
        <section class="mayhen_service_section" aria-labelledby="quy-trinh-in-heading">
            <div class="mayhen_service_section_header">
                <span class="mayhen_service_section_kicker">Chuyên Nghiệp & Đúng Hẹn</span>
                <h2 id="quy-trinh-in-heading" class="mayhen_service_section_title">
                    Quy Trình 5 Bước Đặt In Thiệp Cưới Tại {{ config('app.name') }}
                </h2>
                <p class="mayhen_service_section_desc">
                    Từng công đoạn được giám sát tỉ mỉ để đảm bảo chất lượng thành phẩm hoàn hảo khi giao tới tay bạn.
                </p>
            </div>

            <div class="mayhen_service_steps_grid">
                <div class="mayhen_service_step_card">
                    <div class="mayhen_service_step_num">01</div>
                    <h3 class="mayhen_service_step_title">Chọn Mẫu & Chất Liệu</h3>
                    <p class="mayhen_service_step_desc">
                        Lựa chọn phong cách thiết kế, định lượng phôi giấy và màu sắc nhũ ép kim ưng ý theo chủ đề đám cưới.
                    </p>
                </div>

                <div class="mayhen_service_step_card">
                    <div class="mayhen_service_step_num">02</div>
                    <h3 class="mayhen_service_step_title">Lên Ma-két & Soát Lỗi</h3>
                    <p class="mayhen_service_step_desc">
                        Đội ngũ thiết kế dàn trang thông tin ngày giờ, địa điểm và gửi bạn duyệt bản vẽ ma-két chi tiết trước.
                    </p>
                </div>

                <div class="mayhen_service_step_card">
                    <div class="mayhen_service_step_num">03</div>
                    <h3 class="mayhen_service_step_title">Duyệt Bản In Mẫu</h3>
                    <p class="mayhen_service_step_desc">
                        Xem bản in mẫu thực tế để kiểm tra chất lượng giấy, độ sắc nét và màu mực trước khi chạy hàng loạt.
                    </p>
                </div>

                <div class="mayhen_service_step_card">
                    <div class="mayhen_service_step_num">04</div>
                    <h3 class="mayhen_service_step_title">In Ấn & Gia Công</h3>
                    <p class="mayhen_service_step_desc">
                        Tiến hành in kỹ thuật số, ép kim nhiệt, dập nổi khuôn và gắn phụ kiện phong bì tem sáp thủ công.
                    </p>
                </div>

                <div class="mayhen_service_step_card">
                    <div class="mayhen_service_step_num">05</div>
                    <h3 class="mayhen_service_step_title">Kiểm Tra & Giao Hàng</h3>
                    <p class="mayhen_service_step_desc">
                        Kiểm đếm từng tấm thiệp, đóng hộp carton chống sốc cẩn thận và giao tận nơi đúng hẹn trên toàn quốc.
                    </p>
                </div>
            </div>
        </section>

        <!-- ==========================================
             7. CAM KẾT CHẤT LƯỢNG DỊCH VỤ
             ========================================== -->
        <section class="mayhen_service_section" aria-labelledby="cam-ket-in-heading">
            <div class="mayhen_service_section_header">
                <span class="mayhen_service_section_kicker">Yên Tâm Tuyệt Đối</span>
                <h2 id="cam-ket-in-heading" class="mayhen_service_section_title">
                    Cam Kết Vàng Khi In Thiệp Cưới Tại {{ config('app.name') }}
                </h2>
                <p class="mayhen_service_section_desc">
                    Uy tín tạo nên từ sự tỉ mỉ trong từng đường gấp và lòng chân thành đối với ngày vui trọng đại của bạn.
                </p>
            </div>

            <div class="mayhen_service_diff_grid">
                <div class="mayhen_service_diff_card">
                    <div class="mayhen_service_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_service_diff_title">1. Chuẩn Màu Bản Thiết Kế Đến 98%</h3>
                        <p class="mayhen_service_diff_text">
                            Hệ thống máy in chuyên dụng cho giấy mỹ thuật cùng quy trình cân chỉnh màu chuyên nghiệp đảm bảo màu sắc in ra chân thực, không bị lệch màu hay mờ nhòe.
                        </p>
                    </div>
                </div>

                <div class="mayhen_service_diff_card">
                    <div class="mayhen_service_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_service_diff_title">2. Giao Hàng Đúng Tiến Độ Cam Kết</h3>
                        <p class="mayhen_service_diff_text">
                            Chúng tôi hiểu rõ áp lực về thời gian trong mùa cưới. Toàn bộ đơn hàng in thiệp cưới đều được bàn giao đúng ngày đã thỏa thuận để cô dâu chú rể kịp gửi thiệp mời.
                        </p>
                    </div>
                </div>

                <div class="mayhen_service_diff_card">
                    <div class="mayhen_service_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_service_diff_title">3. In Lại Miễn Phí Nếu Lỗi Do Nhà In</h3>
                        <p class="mayhen_service_diff_text">
                            Nếu thành phẩm có bất kỳ lỗi in ấn, sai chính tả so với ma-két bạn đã duyệt, {{ config('app.name') }} cam kết in lại toàn bộ lô hàng hoàn toàn miễn phí ngay lập tức.
                        </p>
                    </div>
                </div>

                <div class="mayhen_service_diff_card">
                    <div class="mayhen_service_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_service_diff_title">4. Giá Cả Minh Bạch — Không Phát Sinh</h3>
                        <p class="mayhen_service_diff_text">
                            Báo giá in thiệp cưới trọn gói bao gồm thiết kế, in ruột, phong bì và đóng gói. Tuyệt đối không thu thêm bất kỳ phụ phí ẩn nào sau khi đã ký hợp đồng.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             8. CÂU HỎI THƯỜNG GẶP (FAQ CHUẨN SEO)
             ========================================== -->
        <section class="mayhen_service_section" aria-labelledby="faq-in-heading">
            <div class="mayhen_service_section_header">
                <span class="mayhen_service_section_kicker">Giải Đáp Thắc Mắc</span>
                <h2 id="faq-in-heading" class="mayhen_service_section_title">
                    Câu Hỏi Thường Gặp Về Dịch Vụ In Thiệp Cưới
                </h2>
                <p class="mayhen_service_section_desc">
                    Những băn khoăn phổ biến nhất của cô dâu chú rể khi tìm kiếm dịch vụ in thiệp cưới đẹp và uy tín.
                </p>
            </div>

            <div class="mayhen_service_faq_list">
                <article class="mayhen_service_faq_item">
                    <h3 class="mayhen_service_faq_q">
                        <span class="mayhen_service_faq_q_badge">Q.</span>
                        Nên đặt in thiệp cưới trước ngày cưới bao lâu là hợp lý nhất?
                    </h3>
                    <p class="mayhen_service_faq_a">
                        Thời điểm lý tưởng nhất để đặt in thiệp cưới là từ <strong>1 đến 2 tháng trước ngày cưới</strong>. Khoảng thời gian này giúp bạn thảnh thơi duyệt ma-két thiết kế, in thử bản mẫu thực tế, gia công ép kim thủ công và gửi thiệp mời tận tay họ hàng, bạn bè trước ngày cưới ít nhất 2 đến 3 tuần.
                    </p>
                </article>

                <article class="mayhen_service_faq_item">
                    <h3 class="mayhen_service_faq_q">
                        <span class="mayhen_service_faq_q_badge">Q.</span>
                        Dịch vụ in thiệp cưới có nhận in số lượng ít (dưới 100 thiệp) không?
                    </h3>
                    <p class="mayhen_service_faq_a">
                        Có. Chúng tôi nhận in thiệp cưới linh hoạt từ <strong>50 thiệp trở lên</strong> cho các đám cưới ấm cúng hoặc tiệc báo hỷ nhỏ. Các phôi thiệp đều được căn chỉnh kỹ lưỡng từng bản in dù số lượng ít.
                    </p>
                </article>

                <article class="mayhen_service_faq_item">
                    <h3 class="mayhen_service_faq_q">
                        <span class="mayhen_service_faq_q_badge">Q.</span>
                        Giá in thiệp cưới đã bao gồm phong bì và in tên khách mời chưa?
                    </h3>
                    <p class="mayhen_service_faq_a">
                        Tất cả các gói dịch vụ in thiệp cưới đều đã bao gồm trọn bộ: thiệp chính, phong bì nắp nhọn cao cấp và công in ấn hoàn thiện. Dịch vụ in trực tiếp tên khách mời lên từng phong bì cũng được hỗ trợ đồng bộ theo danh sách của bạn.
                    </p>
                </article>

                <article class="mayhen_service_faq_item">
                    <h3 class="mayhen_service_faq_q">
                        <span class="mayhen_service_faq_q_badge">Q.</span>
                        Tôi có được xem và duyệt bản in mẫu thực tế trước khi in toàn bộ không?
                    </h3>
                    <p class="mayhen_service_faq_a">
                        Chắc chắn có. Trước khi tiến hành in ấn và gia công hàng loạt, bạn sẽ được duyệt bản maket đồ họa chi tiết và nhận bản in mẫu thực tế để kiểm tra chất giấy, độ nét của nét chữ và màu sắc ép kim.
                    </p>
                </article>

                <article class="mayhen_service_faq_item">
                    <h3 class="mayhen_service_faq_q">
                        <span class="mayhen_service_faq_q_badge">Q.</span>
                        Nếu cần in thêm thiệp cưới phát sinh thì thời gian xử lý mất bao lâu?
                    </h3>
                    <p class="mayhen_service_faq_a">
                        Do khuôn in và file ma-két của bạn đã được lưu sẵn trên hệ thống, các đơn hàng in thêm thiệp cưới phát sinh sẽ được ưu tiên sản xuất và bàn giao chỉ trong vòng <strong>2 đến 3 ngày làm việc</strong>.
                    </p>
                </article>
            </div>
        </section>

        <!-- ==========================================
             9. BOTTOM ACTION BOX
             ========================================== -->
        <section class="mayhen_service_cta_box">
            <span class="mayhen_service_cta_kicker">Đồng Hành Cùng Bạn</span>
            <h2 class="mayhen_service_cta_title">Nhận Tư Vấn Mẫu & Báo Giá In Thiệp Cưới</h2>
            <p class="mayhen_service_cta_desc">
                Liên hệ ngay với đội ngũ {{ config('app.name') }} để nhận mẫu phôi giấy thực tế và bảng dự toán chi phí in thiệp cưới tối ưu nhất cho ngày trọng đại của bạn.
            </p>
            <div class="mayhen_service_cta_actions">
                <a href="{{ route('contact') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                    Liên Hệ Nhận Mẫu Thiệp In
                </a>
                <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.3);">
                    Xem Kho Mẫu Thiệp Cưới
                </a>
            </div>
        </section>
    </div>
@endsection

