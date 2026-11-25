@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('iching.index');
    $schemaGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Gieo Quẻ Kinh Dịch & Gieo Quẻ Đầu Năm Miễn phí — ' . $brandName,
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'Web',
                'url' => $canonicalUrl,
                'description' => 'Gieo quẻ Kinh Dịch 3 đồng xu cổ, Mai Hoa Dịch Số và gieo quẻ đầu năm miễn phí chuẩn 64 quẻ Chu Dịch. Phân tích Quẻ Chủ, Quẻ Hỗ, Quẻ Biến, Thể–Dụng Ngũ Hành và luận giải AI 4 tầng cá nhân hóa.',
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
                        'name' => 'Gieo Quẻ Kinh Dịch & Đầu Năm',
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Gieo quẻ Kinh Dịch & gieo quẻ đầu năm trên Mây Hẹn có miễn phí không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hoàn toàn miễn phí. Bạn có thể tra cứu trọn bộ 64 quẻ Kinh Dịch công khai, và chỉ cần đăng nhập tài khoản Mây Hẹn (tự động nhận miễn phí gói Thành Viên Premium 0đ) để gieo quẻ, nhận luận giải AI 4 tầng cá nhân hóa và lưu Sổ Quẻ riêng tư.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Hệ thống gieo quẻ của Mây Hẹn khác biệt gì so với các trang web khác?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Mây Hẹn tách biệt hoàn toàn thuật toán lập quẻ chuẩn 64 quẻ Chu Dịch (3 đồng xu Lục Hào, Mai Hoa Dịch Số, Quẻ Hỗ, Quẻ Biến, Thể–Dụng Ngũ Hành sinh khắc, phép Chu Hy đọc hào động) với tầng AI luận giải 4 tầng cá nhân hóa theo đúng câu hỏi thực tế của bạn — đảm bảo tuyệt đối không bịa sai tên quẻ hay hào động.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Nên gieo quẻ Kinh Dịch với tâm thế nào để có góc nhìn sâu sắc nhất?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hãy tĩnh tâm 10 giây, tập trung vào một câu hỏi cụ thể nhất và không gieo lặp lại nhiều lần cho cùng một việc trong ngày. Kinh Dịch là công cụ tự quán chiếu giúp bạn nhận diện thời thế, điểm mù và hành động tỉnh táo hơn.',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $demoPayload = [
        'analysis' => $demoAnalysis,
        'interpretation' => $demoInterpretation,
        'question' => 'Vận trình năm mới và công việc, gia đạo sắp tới ra sao?',
        'topic_label' => 'Tổng Quan & Vận Trình',
        'result_url' => null,
    ];
@endphp

@section('title', 'Gieo Quẻ Kinh Dịch & Gieo Quẻ Đầu Năm Miễn phí | ' . $brandName)
@section('meta_description', 'Gieo quẻ Kinh Dịch 3 đồng xu cổ, Mai Hoa Dịch Số và gieo quẻ đầu năm miễn phí chuẩn 64 quẻ Chu Dịch. Lập Quẻ Chủ – Quẻ Hỗ – Quẻ Biến, xét Ngũ Hành Thể–Dụng và luận giải 4 tầng chuyên sâu theo câu hỏi riêng của bạn.')
@section('meta_keywords', 'gieo quẻ kinh dịch, gieo quẻ đầu năm, gieo quẻ online miễn phí, 64 quẻ kinh dịch, mai hoa dịch số, xin quẻ đầu năm')
@section('canonical', $canonicalUrl)
@section('og_type', 'website')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/iching.css') }}">
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_iching_page"
     id="mayhenIchingApp"
     data-cast-url="{{ route('iching.cast') }}"
     data-can-cast="{{ $canCastFree ? '1' : '0' }}">

    {{-- 1. HERO & BÀN GIEO QUẺ KINH DỊCH / ĐẦU NĂM --}}
    <section class="mayhen_iching_hero">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_hero_head">
                <span class="mayhen_iching_kicker">☯ Chu Dịch Chính Tông • Luận Giải 4 Tầng AI Cá Nhân Hóa</span>
                <h1 class="mayhen_iching_hero_h1">
                    <span>Gieo Quẻ Kinh Dịch</span> &amp; Gieo Quẻ Đầu Năm Miễn Phí
                </h1>
                <p class="mayhen_iching_hero_lead">
                    Chuẩn xác tuyệt đối thuật toán <strong>64 Quẻ Chu Dịch</strong> (Quẻ Chủ – Quẻ Hỗ – Quẻ Biến – Ngũ Hành Thể Dụng – Phép Chu Hy)
                    kết hợp trí tuệ nhân tạo luận giải 4 tầng sát với từng câu hỏi của bạn, giúp tự quán chiếu sáng suốt và định hướng hành động an lành.
                </p>

                <div class="mayhen_iching_status_bar">
                    @if($canCastFree)
                        <span style="color:#1E4D3B; font-weight:700;">✓ Đã mở khóa miễn phí ({{ $currentPlanLabel }})</span>
                        <span>•</span>
                        <a href="{{ route('iching.history') }}" style="color:#85182A; font-weight:700; text-decoration:none;">
                            Xem Sổ Quẻ Của Tôi ({{ $recentReadings->count() }}) &rarr;
                        </a>
                    @else
                        <span>Miễn phí 100% khi đăng nhập (tự động cấp gói <strong>Thành Viên Premium 0đ</strong>)</span>
                        <a href="{{ route('login', ['redirect' => route('iching.index')]) }}" style="color:#85182A; font-weight:700; text-decoration:none;">
                            Đăng Nhập Ngay &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- BÀN GIEO QUẺ TƯƠNG TÁC --}}
            <div class="mayhen_iching_altar_card">
                <div class="mayhen_iching_method_tabs" role="tablist">
                    <button type="button" class="mayhen_iching_method_tab is-active" data-method="coins_3">
                        <strong>🪙 Gieo 3 Đồng Xu (Lục Hào)</strong>
                        <small>Tung 6 lần chuẩn Chu Dịch cổ điển</small>
                    </button>
                    <button type="button" class="mayhen_iching_method_tab" data-method="mai_hoa">
                        <strong>🌸 Mai Hoa Dịch Số</strong>
                        <small>Lập quẻ theo Giờ Động Tâm / 2 Số</small>
                    </button>
                    <button type="button" class="mayhen_iching_method_tab" data-method="new_year">
                        <strong>🏮 Gieo Quẻ Đầu Năm</strong>
                        <small>Xin 1 quẻ Xuân khai vận cả năm</small>
                    </button>
                </div>

                <div class="mayhen_iching_altar_grid">
                    {{-- CỘT TRÁI: CHỌN CHỦ ĐỀ & CÂU HỎI KHỞI TÂM --}}
                    <div class="mayhen_iching_altar_left">
                        <div class="mayhen_iching_field">
                            <label>1. Chọn lĩnh vực bạn muốn quán chiếu</label>
                            <div class="mayhen_iching_topic_grid">
                                @foreach($topics as $tKey => $topicItem)
                                    <button type="button"
                                            class="mayhen_iching_topic_chip {{ $tKey === 'tong_quan' ? 'is-active' : '' }}"
                                            data-topic="{{ $tKey }}">
                                        {{ $topicItem['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mayhen_iching_field">
                            <label for="mayhenIchingQuestion">2. Tĩnh tâm và viết rõ điều bạn đang băn khoăn *</label>
                            <textarea id="mayhenIchingQuestion"
                                      class="mayhen_iching_textarea"
                                      rows="3"
                                      maxlength="360"
                                      placeholder="Ví dụ: Vận trình năm mới của gia đình tôi có thuận lợi không? Hoặc: Tôi có nên chuyển sang công việc mới vào quý tới?"></textarea>
                            <div class="mayhen_iching_quick_prompts">
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Xin quẻ đầu năm cho vận trình tổng quan, sức khỏe và tài lộc năm nay">Quẻ Đầu Năm tổng quan</button>
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Tình duyên và kế hoạch cưới hỏi của hai chúng tôi trong năm nay ra sao?">Tình duyên &amp; Cưới hỏi</button>
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Hướng đi công việc và cơ hội thăng tiến của tôi trong 6 tháng tới thế nào?">Công danh 6 tháng tới</button>
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Kế hoạch kinh doanh và hợp tác sắp tới cần lưu ý điều gì để thuận lợi?">Kinh doanh &amp; Hợp tác</button>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px;">
                            <button type="button" class="mayhen_iching_btn_primary" id="mayhenIchingSubmitCastBtn" style="flex:1;">
                                ✦ Lập Quẻ &amp; Luận Giải 4 Tầng (Miễn Phí)
                            </button>
                            @if(! $canCastFree)
                                <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingShowDemoBtn">
                                    Xem Quẻ Mẫu
                                </button>
                            @endif
                        </div>

                        <p style="font-size:12px; color:#75695D; margin:12px 0 0; line-height:1.55;">
                            * Nguyên tắc Kinh Dịch: <em>“Bất thành bất chiếm, bất nghi bất chiếm”</em> — Hãy tập trung vào một việc rõ ràng để nhận góc nhìn mạch lạc, khách quan nhất.
                        </p>
                    </div>

                    {{-- CỘT PHẢI: SÂN KHẤU ĐỒNG XU 3D / MAI HOA / ỐNG QUẺ ĐẦU NĂM --}}
                    <div class="mayhen_iching_altar_right">
                        {{-- PANEL 1: 3 ĐỒNG XU 3D --}}
                        <div class="mayhen_iching_stage_panel is-active" data-stage-panel="coins_3">
                            <p style="font-size:12.5px; font-weight:700; color:#85182A; margin:0 0 6px; text-transform:uppercase; letter-spacing:0.05em;">
                                Tung 3 Đồng Xu Cổ (Từ Hào 1 Lên Hào 6)
                            </p>
                            <div class="mayhen_iching_coins_arena">
                                <div class="mayhen_iching_coin" id="mayhenIchingCoin1">
                                    <span class="mayhen_iching_coin_hole">D</span>
                                    <span class="mayhen_iching_coin_face_label">Ngửa (3)</span>
                                </div>
                                <div class="mayhen_iching_coin" id="mayhenIchingCoin2">
                                    <span class="mayhen_iching_coin_hole">Â</span>
                                    <span class="mayhen_iching_coin_face_label">Sấp (2)</span>
                                </div>
                                <div class="mayhen_iching_coin" id="mayhenIchingCoin3">
                                    <span class="mayhen_iching_coin_hole">D</span>
                                    <span class="mayhen_iching_coin_face_label">Ngửa (3)</span>
                                </div>
                            </div>

                            <div class="mayhen_iching_lines_stack" id="mayhenIchingLinesStack"></div>

                            <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                                <button type="button" class="mayhen_iching_btn_gold" id="mayhenIchingTossOneBtn" style="padding:9px 14px; font-size:12.5px;">
                                    🪙 Tung Từng Hào
                                </button>
                                <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingTossAllBtn" style="padding:9px 14px; font-size:12.5px;">
                                    ⚡ Gieo Nhanh 6 Hào
                                </button>
                                <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingResetCoinsBtn" style="padding:9px 12px; font-size:12.5px;">
                                    ↺ Làm lại
                                </button>
                            </div>
                        </div>

                        {{-- PANEL 2: MAI HOA DỊCH SỐ --}}
                        <div class="mayhen_iching_stage_panel" data-stage-panel="mai_hoa">
                            <p style="font-size:13px; font-weight:700; color:#85182A; margin:0 0 10px; text-transform:uppercase;">
                                Phép Lập Quẻ Mai Hoa (Thiệu Khang Tiết)
                            </p>
                            <p style="font-size:13px; color:#5C5349; line-height:1.6; max-width:360px; margin:0 auto 16px;">
                                Dùng Tiên Thiên Bát Quái (1 Càn – 8 Khôn) tính Thượng Quái, Hạ Quái và Hào Động đúng thời điểm tâm khởi niệm hoặc từ 2 con số hiện lên trong đầu bạn.
                            </p>
                            <div style="max-width:340px; margin:0 auto; text-align:left;">
                                <div class="mayhen_iching_field">
                                    <label for="mayhenIchingMaiHoaMode">Chọn cách khởi quẻ Mai Hoa</label>
                                    <select id="mayhenIchingMaiHoaMode" class="mayhen_iching_select">
                                        <option value="mai_hoa_time">Theo Giờ Động Tâm hiện tại ({{ now('Asia/Ho_Chi_Minh')->format('H:i • d/m/Y') }})</option>
                                        <option value="mai_hoa_numbers">Theo 2 con số bất kỳ tôi vừa nghĩ ra</option>
                                    </select>
                                </div>
                                <div id="mayhenIchingMaiHoaNums" style="display:none; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="mayhen_iching_field">
                                        <label for="mayhenIchingNumUpper">Số thứ 1 (Thượng Quái)</label>
                                        <input type="number" id="mayhenIchingNumUpper" class="mayhen_iching_input" min="1" max="9999" value="18">
                                    </div>
                                    <div class="mayhen_iching_field">
                                        <label for="mayhenIchingNumLower">Số thứ 2 (Hạ Quái)</label>
                                        <input type="number" id="mayhenIchingNumLower" class="mayhen_iching_input" min="1" max="9999" value="26">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PANEL 3: GIEO QUẺ ĐẦU NĂM --}}
                        <div class="mayhen_iching_stage_panel" data-stage-panel="new_year">
                            <p style="font-size:13px; font-weight:700; color:#85182A; margin:0 0 8px; text-transform:uppercase;">
                                Xin Quẻ Đầu Năm • Khai Xuân An Lành
                            </p>
                            <div class="mayhen_iching_tube_box" id="mayhenIchingTubeBox" aria-hidden="true">
                                <div class="mayhen_iching_tube_sticks">
                                    <span></span><span></span><span></span><span></span><span></span>
                                </div>
                                <div class="mayhen_iching_tube_body">XUÂN</div>
                            </div>
                            <p style="font-size:13px; color:#5C5349; line-height:1.6; max-width:340px; margin:0 auto;">
                                Giữ tâm an hòa, nghĩ về mong ước năm mới rồi nhấn nút <strong>“Lập Quẻ &amp; Luận Giải 4 Tầng”</strong> bên trái để lắc ống quẻ khai xuân.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. KHU VỰC HIỂN THỊ KẾT QUẢ BẢN ĐỒ 3 QUẺ & LUẬN GIẢI 4 TẦNG --}}
    <section class="mayhen_iching_result_section" id="mayhenIchingResultSection" style="display:none;">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_result_shell" id="mayhenIchingResultShell"></div>
        </div>
    </section>

    {{-- 3. KHO TRA CỨU TRỰC QUAN 64 QUẺ KINH DỊCH --}}
    <section class="mayhen_iching_section" id="tra-cuu-64-que">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_section_head">
                <h2>Kho Tra Cứu Trọn Bộ 64 Quẻ Kinh Dịch (Chu Dịch)</h2>
                <p>Bấm vào bất kỳ quẻ nào để xem Quái Từ, Đại Tượng, Ngũ Hành Thượng–Hạ Quái cùng lời khuyên cho Tình duyên, Công danh, Tài lộc và Gia đạo.</p>
            </div>

            <div class="mayhen_iching_catalog_controls">
                <div class="mayhen_iching_filter_pills">
                    <button type="button" class="mayhen_iching_filter_btn is-active" data-level-filter="all">Tất cả (64)</button>
                    <button type="button" class="mayhen_iching_filter_btn" data-level-filter="Đại Cát">Đại Cát</button>
                    <button type="button" class="mayhen_iching_filter_btn" data-level-filter="Cát">Cát</button>
                    <button type="button" class="mayhen_iching_filter_btn" data-level-filter="Bình">Bình</button>
                    <button type="button" class="mayhen_iching_filter_btn" data-level-filter="Thận Trọng">Thận Trọng</button>
                </div>
                <input type="search"
                       id="mayhenIching64Search"
                       class="mayhen_iching_input"
                       style="max-width:260px; padding:8px 13px; border-radius:999px;"
                       placeholder="Tìm quẻ (VD: Thái, Càn, Hàm...)">
            </div>

            <div class="mayhen_iching_64_grid">
                @foreach($hexagrams as $hex)
                    <button type="button"
                            class="mayhen_iching_64_card"
                            data-hex-no="{{ $hex['no'] }}"
                            data-level="{{ $hex['level'] }}"
                            data-search="{{ $hex['no'] }} {{ $hex['name'] }} {{ $hex['hanzi'] }} {{ $hex['meaning_short'] }}">
                        <div class="mayhen_iching_64_top">
                            <span class="mayhen_iching_64_no">Quẻ {{ $hex['no'] }} • {{ $hex['level'] }}</span>
                            <span class="mayhen_iching_64_hanzi">{{ $hex['hanzi'] }}</span>
                        </div>
                        <h3>{{ $hex['name'] }}</h3>
                        <p>{{ $hex['meaning_short'] }}</p>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. GIẢI THÍCH PHƯƠNG PHÁP LUẬN 4 TẦNG & FAQ SEO --}}
    <section class="mayhen_iching_section mayhen_iching_section_soft">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_section_head">
                <h2>Phương Pháp Luận Giải Kinh Dịch 4 Tầng Chuẩn Xác</h2>
                <p>Khác với các trang bói toán chung chung, mọi quẻ trên {{ $brandName }} đều trải qua quy trình lập quẻ toán học chính xác rồi mới luận giải 4 tầng sâu sắc.</p>
            </div>

            <div class="mayhen_iching_faq_list">
                <div class="mayhen_iching_faq_item">
                    <h3>Gieo 3 đồng xu cổ tính Âm Dương và Hào Động như thế nào?</h3>
                    <p>Mỗi lần tung 3 đồng xu tương ứng với 1 hào (từ Hào Sơ dưới cùng lên Hào Thượng trên cùng): Mặt Ngửa tính 3 (Dương), Mặt Sấp tính 2 (Âm). Tổng 9 (3 Ngửa) là Lão Dương động, tổng 6 (3 Sấp) là Lão Âm động, tổng 7 là Thiếu Dương tĩnh, tổng 8 là Thiếu Âm tĩnh.</p>
                </div>
                <div class="mayhen_iching_faq_item">
                    <h3>Quẻ Chủ, Quẻ Hỗ và Quẻ Biến nói lên điều gì?</h3>
                    <p><strong>Quẻ Chủ</strong> phản ánh bản chất hiện tại; <strong>Quẻ Hỗ</strong> (lập từ hào 2-3-4 và 3-4-5) soi rọi diễn biến ngầm bên trong; <strong>Quẻ Biến</strong> (đảo Âm ↔ Dương tại các Hào Động) chỉ ra xu hướng kết cục tương lai.</p>
                </div>
                <div class="mayhen_iching_faq_item">
                    <h3>Tại sao cần đăng nhập (miễn phí gói Premium) để gieo quẻ AI?</h3>
                    <p>Đăng nhập hoàn toàn miễn phí giúp hệ thống bảo vệ tài nguyên AI luận giải 4 tầng chất lượng cao khỏi spam, đồng thời tự động lưu lại Sổ Quẻ cá nhân để bạn đối chiếu Ứng Kỳ theo thời gian.</p>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- MODAL CHI TIẾT 1 QUẺ TRONG KHO 64 QUẺ --}}
<div class="mayhen_iching_modal_backdrop" id="mayhenIchingHexModal">
    <div class="mayhen_iching_modal">
        <div style="display:flex; justify-content:flex-end; margin-bottom:6px;">
            <button type="button" class="mayhen_iching_btn_outline" style="padding:5px 11px; font-size:12px;" id="mayhenIchingHexModalClose">Đóng ✕</button>
        </div>
        <div id="mayhenIchingHexModalBody"></div>
    </div>
</div>

{{-- MODAL YÊU CẦU ĐĂNG NHẬP MIỄN PHÍ GÓI PREMIUM --}}
<div class="mayhen_iching_modal_backdrop" id="mayhenIchingLoginModal">
    <div class="mayhen_iching_modal" style="text-align:center;">
        <span class="mayhen_iching_kicker">Miễn Phí 100% Cho Thành Viên Premium (0đ)</span>
        <h2 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; margin:8px 0 10px;">
            Đăng Nhập Để Gieo Quẻ &amp; Luận Giải AI 4 Tầng
        </h2>
        <p style="font-size:14px; color:#544A42; line-height:1.65; margin:0 0 20px;">
            Chỉ cần đăng nhập tài khoản {{ $brandName }} (tự động cấp miễn phí gói <strong>Thành Viên Premium 0đ</strong>), bạn sẽ mở khóa không giới hạn trải nghiệm Gieo Quẻ Kinh Dịch, Gieo Quẻ Đầu Năm, luận giải AI cá nhân hóa theo câu hỏi và lưu Sổ Quẻ riêng tư.
        </p>
        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('login', ['redirect' => route('iching.index')]) }}" class="mayhen_iching_btn_primary">
                Đăng Nhập Miễn Phí &rarr;
            </a>
            <a href="{{ route('register') }}" class="mayhen_iching_btn_gold">
                Tạo Tài Khoản Mới (0đ)
            </a>
            <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingLoginModalClose">
                Để Sau
            </button>
        </div>
    </div>
</div>

<script id="mayhenIchingCatalogJson" type="application/json">@json($hexagrams, JSON_UNESCAPED_UNICODE)</script>
<script id="mayhenIchingDemoJson" type="application/json">@json($demoPayload, JSON_UNESCAPED_UNICODE)</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/iching.js') }}"></script>
@endpush
