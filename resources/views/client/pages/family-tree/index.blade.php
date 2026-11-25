@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('family_tree.index');
    $schemaGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'SoftwareApplication',
                'name' => 'Công Cụ Tạo Gia Phả Online — ' . $brandName,
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'Web',
                'url' => $canonicalUrl,
                'description' => 'Nền tảng tạo gia phả online miễn phí, vẽ cây phả hệ nhiều đời chuẩn văn hóa Việt Nam, tra cứu xưng hô họ hàng, lịch giỗ âm lịch và xuất ảnh PNG/in khổ lớn.',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'VND',
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Trang chủ',
                        'item' => route('home'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Gia Phả Online',
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Tạo gia phả online trên Mây Hẹn có mất phí không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Bạn có thể tạo gia phả, dùng thử mọi mẫu gia phả, tra cứu xưng hô, xem lịch giỗ âm lịch và xuất file ảnh PNG/in ấn hoàn toàn miễn phí không cần đăng nhập. Khi muốn xuất bản link gia phả thật để chia sẻ cho dòng họ, bạn chỉ cần đăng nhập tài khoản Mây Hẹn (tự động kích hoạt gói Thành Viên Premium 0đ).',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Mẫu gia phả hỗ trợ bao nhiêu đời và có in treo nhà thờ họ được không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hệ thống hỗ trợ vẽ cây gia phả không giới hạn thế hệ, tự động sắp xếp cặp vợ chồng và các chi nhánh, cho phép xuất ảnh PNG độ phân giải cao (2x HiDPI) hoặc in khổ lớn treo tại phòng khách, nhà thờ họ.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Làm sao bảo mật thông tin người thân khi gửi link gia phả online?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Mọi link chia sẻ gia phả thật trên Mây Hẹn đều gắn thẻ noindex (không lộ trên Google tìm kiếm) và cho phép chủ gia phả đặt mật khẩu riêng chỉ con cháu trong họ mới xem được.',
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', 'Tạo Gia Phả Online & Xem Mẫu Gia Phả Online Đẹp')
@section('meta_description', 'Tạo gia phả online đẹp và dễ nhìn chỉ trong 5 phút. Kho mẫu gia phả truyền thống Việt Nam, tự động vẽ cây phả hệ nhiều đời, lịch giỗ âm lịch, tra cứu xưng hô họ hàng và xuất ảnh in khổ lớn miễn phí.')
@section('meta_keywords', 'gia phả online, tạo gia phả, mẫu gia phả, vẽ cây gia phả, gia phả dòng họ, phần mềm gia phả miễn phí')
@section('canonical', $canonicalUrl)
@section('og_type', 'website')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/family-tree.css') }}">
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_family_tree_seo_page">
    {{-- 1. HERO SECTION CHUẨN SEO --}}
    <section class="mayhen_family_tree_hero">
        <div class="mayhen_family_tree_container">
            <div class="mayhen_family_tree_hero_grid">
                <div>
                    <span class="mayhen_family_tree_kicker">✦ Dịch vụ cộng đồng miễn phí • Văn hóa Việt</span>
                    <h1 class="mayhen_family_tree_hero_h1">
                        <span>Gia Phả Online</span> Tinh Tế — Tạo Gia Phả &amp; Lưu Giữ Cội Nguồn Dòng Họ
                    </h1>
                    <p class="mayhen_family_tree_hero_lead">
                        Công cụ <strong>tạo gia phả</strong> trực quan, đẹp và dễ nhìn nhất dành cho gia đình Việt.
                        Tự động vẽ cây phả hệ đứng &amp; ngang nhiều thế hệ, lưu ngày giỗ Âm lịch, tra cứu xưng hô nội ngoại
                        và xuất ảnh PNG nét căng để in treo từ đường hoàn toàn miễn phí.
                    </p>
                    <div class="mayhen_family_tree_hero_actions">
                        <a href="{{ route('family_tree.studio') }}" class="mayhen_family_tree_btn_primary">
                            ✦ Tạo Gia Phả Ngay (Miễn Phí) &rarr;
                        </a>
                        <a href="#mau-gia-pha" class="mayhen_family_tree_btn_outline">
                            Xem 4 Mẫu Gia Phả Đẹp
                        </a>
                        @auth
                            <a href="{{ route('family_tree.my_trees') }}" class="mayhen_family_tree_btn_gold">
                                Gia Phả Của Tôi
                            </a>
                        @endauth
                    </div>
                    <div class="mayhen_family_tree_trust_row">
                        <span>✓ Tạo &amp; xuất ảnh 100% miễn phí</span>
                        <span>✓ Đăng nhập gói Premium để tạo link thật</span>
                        <span>✓ Bảo mật riêng tư (Noindex + Mật khẩu)</span>
                    </div>
                </div>

                <div class="mayhen_family_tree_hero_visual" aria-label="Minh họa sơ đồ cây gia phả online">
                    <div class="mayhen_family_tree_mini_diagram">
                        <p class="mayhen_family_tree_mini_title">Phả Đồ Dòng Họ Nguyễn Văn</p>
                        <div class="mayhen_family_tree_mini_row">
                            <div class="mayhen_family_tree_mini_node">
                                <strong>Cụ Nguyễn Văn Khang</strong>
                                <small>Thủy Tổ • Giỗ 15/08 ÂL</small>
                            </div>
                            <div class="mayhen_family_tree_mini_node">
                                <strong>Cụ Bà Đặng Thị Hạnh</strong>
                                <small>Đời 1 • Giỗ 23/02 ÂL</small>
                            </div>
                        </div>
                        <div class="mayhen_family_tree_mini_connector"></div>
                        <div class="mayhen_family_tree_mini_branch_bar"></div>
                        <div class="mayhen_family_tree_mini_row">
                            <div class="mayhen_family_tree_mini_node">
                                <strong>Nguyễn Văn Minh</strong>
                                <small>Trưởng Nam (Đời 2)</small>
                            </div>
                            <div class="mayhen_family_tree_mini_node">
                                <strong>Nguyễn Thị Mai</strong>
                                <small>Trưởng Nữ (Đời 2)</small>
                            </div>
                            <div class="mayhen_family_tree_mini_node">
                                <strong>Nguyễn Văn Hùng</strong>
                                <small>Thứ Nam (Đời 2)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. KHO 4 MẪU GIA PHẢ ĐẸP TƯƠNG TÁC --}}
    <section class="mayhen_family_tree_section" id="mau-gia-pha">
        <div class="mayhen_family_tree_container">
            <div class="mayhen_family_tree_section_head">
                <h2>Kho Mẫu Gia Phả Online Chuẩn Truyền Thống &amp; Hiện Đại</h2>
                <p>Mỗi mẫu gia phả đều có sẵn dữ liệu minh họa 4 thế hệ để bạn bấm xem thử, xoay phả đồ đứng/ngang, tra cứu xưng hô và tùy biến ngay thành cây gia đình của riêng mình.</p>
            </div>

            <div class="mayhen_family_tree_templates_grid">
                @foreach($templates as $tpl)
                    <article class="mayhen_family_tree_tpl_card">
                        <div class="mayhen_family_tree_tpl_preview_box"
                             style="background: {{ $tpl['bg_color'] }}; border-color: {{ $tpl['accent_color'] }};">
                            <span class="mayhen_family_tree_tpl_badge" style="color: {{ $tpl['primary_color'] }};">
                                {{ $tpl['badge'] }}
                            </span>
                            <h3 style="color: {{ $tpl['primary_color'] }};">{{ $tpl['name'] }}</h3>
                            <p style="font-size: 13px; color: #5C5349; margin: 0;">{{ $tpl['subtitle'] }}</p>
                        </div>
                        <p style="font-size: 14px; line-height: 1.6; color: #4A433C; margin: 0;">
                            {{ $tpl['description'] }}
                        </p>
                        <div class="mayhen_family_tree_tpl_actions">
                            <a href="{{ route('family_tree.template_preview', $tpl['slug']) }}" class="mayhen_family_tree_btn_outline">
                                Xem Thử Mẫu
                            </a>
                            <a href="{{ route('family_tree.studio', ['mau' => $tpl['slug']]) }}" class="mayhen_family_tree_btn_primary">
                                Dùng Mẫu Này &rarr;
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. TÍNH NĂNG VƯỢT TRỘI --}}
    <section class="mayhen_family_tree_section mayhen_family_tree_section_soft">
        <div class="mayhen_family_tree_container">
            <div class="mayhen_family_tree_section_head">
                <h2>Vì Sao Tạo Gia Phả Tại {{ $brandName }} Giá Trị Hơn Hẳn?</h2>
                <p>Kết hợp độ trực quan của phần mềm phả hệ hiện đại với chiều sâu văn hóa dòng họ Việt Nam, không quảng cáo rối mắt.</p>
            </div>

            <div class="mayhen_family_tree_features_grid">
                <div class="mayhen_family_tree_feature_card">
                    <div class="mayhen_family_tree_feature_icon" aria-hidden="true">🌳</div>
                    <h3>3 Chế Độ Phả Đồ &amp; Phả Ký</h3>
                    <p>Chuyển đổi tức thì giữa Phả đồ đứng truyền thống, Phả đồ ngang dễ nhìn trên màn hình rộng và Cuốn Phả Ký – Tộc Ước trang trọng.</p>
                </div>
                <div class="mayhen_family_tree_feature_card">
                    <div class="mayhen_family_tree_feature_icon" aria-hidden="true">🕯️</div>
                    <h3>Lịch Giỗ Âm Lịch Tự Động</h3>
                    <p>Tổng hợp toàn bộ ngày giỗ Âm lịch của các bậc tiền nhân theo thứ tự trong năm để con cháu ở xa luôn kịp nhớ và thu xếp về sum họp.</p>
                </div>
                <div class="mayhen_family_tree_feature_card">
                    <div class="mayhen_family_tree_feature_icon" aria-hidden="true">🧭</div>
                    <h3>Tra Cứu Xưng Hô Họ Hàng</h3>
                    <p>Chọn 2 thành viên bất kỳ trên cây gia phả, hệ thống tự động phân tích bậc thế hệ để gợi ý đúng vai vế (Bác, Chú, Cô, Dì, Cháu, Anh em họ).</p>
                </div>
                <div class="mayhen_family_tree_feature_card">
                    <div class="mayhen_family_tree_feature_icon" aria-hidden="true">🖼️</div>
                    <h3>Xuất Ảnh PNG Nét Căng &amp; In A0</h3>
                    <p>Tải xuống file PNG độ phân giải cao (2x HiDPI) kèm khung viền truyền thống để in treo nhà thờ họ hoặc lưu trữ vĩnh viễn.</p>
                </div>
                <div class="mayhen_family_tree_feature_card">
                    <div class="mayhen_family_tree_feature_icon" aria-hidden="true">🔒</div>
                    <h3>Bảo Mật Mật Khẩu &amp; Riêng Tư</h3>
                    <p>Link chia sẻ gia phả được ẩn khỏi công cụ tìm kiếm (noindex) và hỗ trợ khóa mật khẩu riêng cho nội bộ con cháu trong dòng họ.</p>
                </div>
                <div class="mayhen_family_tree_feature_card">
                    <div class="mayhen_family_tree_feature_icon" aria-hidden="true">🎁</div>
                    <h3>Miễn Phí Hoàn Toàn Với Gói Premium</h3>
                    <p>Vẽ và xuất ảnh ngay không cần tài khoản. Khi đăng nhập Mây Hẹn (tự động nhận gói Thành Viên Premium 0đ), bạn tạo ngay link thật chia sẻ cho cả họ.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. QUY TRÌNH 4 BƯỚC --}}
    <section class="mayhen_family_tree_section">
        <div class="mayhen_family_tree_container">
            <div class="mayhen_family_tree_section_head">
                <h2>4 Bước Tạo Gia Phả Online Chỉ Trong Vài Phút</h2>
                <p>Giao diện kéo thả trực quan, bấm trực tiếp vào từng ô thành viên để thêm cha mẹ, vợ chồng, con cháu.</p>
            </div>
            <div class="mayhen_family_tree_steps_grid">
                <div class="mayhen_family_tree_step_card">
                    <span class="mayhen_family_tree_step_num">01</span>
                    <h3>Chọn Mẫu Gia Phả</h3>
                    <p>Chọn 1 trong 4 mẫu phả đồ phù hợp với truyền thống dòng họ hoặc gia đình hiện đại.</p>
                </div>
                <div class="mayhen_family_tree_step_card">
                    <span class="mayhen_family_tree_step_num">02</span>
                    <h3>Thêm Thành Viên</h3>
                    <p>Chạm vào từng thành viên để thêm Con, Vợ/Chồng, Cha/Mẹ, ngày giỗ Âm lịch và ảnh chân dung.</p>
                </div>
                <div class="mayhen_family_tree_step_card">
                    <span class="mayhen_family_tree_step_num">03</span>
                    <h3>Viết Phả Ký &amp; Tộc Ước</h3>
                    <p>Ghi lại lời nói đầu, nguồn gốc dòng họ và gia huấn để lưu truyền cho các thế hệ sau.</p>
                </div>
                <div class="mayhen_family_tree_step_card">
                    <span class="mayhen_family_tree_step_num">04</span>
                    <h3>Xuất Ảnh Hoặc Tạo Link Thật</h3>
                    <p>Tải ảnh PNG miễn phí hoặc đăng nhập gói Premium (0đ) để nhận đường dẫn online gửi cho người thân.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. CÂU HỎI THƯỜNG GẶP (FAQ) --}}
    <section class="mayhen_family_tree_section mayhen_family_tree_section_soft">
        <div class="mayhen_family_tree_container">
            <div class="mayhen_family_tree_section_head">
                <h2>Câu Hỏi Thường Gặp Về Tạo Gia Phả Online</h2>
            </div>
            <div class="mayhen_family_tree_faq_list">
                <div class="mayhen_family_tree_faq_item">
                    <h3>Tôi chưa đăng nhập có vẽ và tải ảnh gia phả được không?</h3>
                    <p>Có. Bạn có thể vào Studio Tạo Gia Phả dùng thử đầy đủ tính năng, lưu nháp tự động trên trình duyệt và tải xuống ảnh PNG độ nét cao hoàn toàn miễn phí mà không cần đăng nhập.</p>
                </div>
                <div class="mayhen_family_tree_faq_item">
                    <h3>Làm sao để có đường link gia phả thật gửi cho họ hàng qua Zalo?</h3>
                    <p>Bạn chỉ cần đăng nhập hoặc đăng ký tài khoản Mây Hẹn (hệ thống tự động cấp miễn phí gói Thành Viên Premium). Sau đó bấm nút “Tạo Link Thật” trong Studio để nhận đường dẫn riêng tư gửi cho cả dòng họ cùng xem.</p>
                </div>
                <div class="mayhen_family_tree_faq_item">
                    <h3>Gói Premium lưu được bao nhiêu gia phả và tồn tại bao lâu?</h3>
                    <p>Theo chính sách chung của Mây Hẹn, gói Thành Viên (Premium 0đ) cho phép lưu tối đa 3 gia phả link thật với thời hạn 3 tháng (90 ngày), và bạn có thể nâng cấp gói Plus (6 tháng) hoặc Pro (1 năm) bất kỳ lúc nào.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
