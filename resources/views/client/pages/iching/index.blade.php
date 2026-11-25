@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('iching.index');
    $libraryThemes = $topicsTaxonomy['library_themes'] ?? [];
    $schemaGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Gieo Quẻ Kinh Dịch & Gieo Quẻ Đầu Năm Miễn phí — ' . $brandName,
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'Web',
                'url' => $canonicalUrl,
                'description' => 'Mây Hẹn Dịch Tâm: Gieo quẻ Kinh Dịch 3 đồng xu chuẩn Chu Dịch, nhập kết quả tự gieo xu thật, Mai Hoa Dịch Số theo Âm lịch Việt Nam và Gieo Quẻ Đầu Năm miễn phí. Minh bạch thuật toán Chu Hy & Ngũ Hành 25 tổ hợp.',
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
                            'text' => 'Hoàn toàn miễn phí. Bạn có thể tra cứu trọn bộ 64 quẻ Kinh Dịch công khai, và đăng nhập tài khoản Mây Hẹn (tự động nhận miễn phí gói Thành Viên Premium 0đ) để gieo quẻ, nhận luận giải cá nhân hóa và lưu Sổ Quẻ riêng tư.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Mây Hẹn Dịch Tâm khác biệt gì so với các trang gieo quẻ khác?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hệ thống tách bạch trường phái Chu Dịch (quy tắc Chu Hy đầy đủ 20 trường hợp 3 hào động & Dụng Cửu/Dụng Lục) với Mai Hoa Dịch Số (chuyển đổi Âm lịch Việt Nam UTC+7 và ma trận 25 tổ hợp Ngũ Hành Thể–Dụng), công khai minh bạch từng phép tính và không trộn nội dung AI với nguyên văn cổ thư.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Lá quẻ tôi gieo có được bảo mật riêng tư không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Mọi lá quẻ đều ở chế độ riêng tư mặc định — chỉ tài khoản chủ sở hữu mới xem được. Bạn cũng có thể chọn ẩn nội dung câu hỏi nhạy cảm khi lưu hoặc tạo Thẻ Chia Sẻ ẩn danh có thể thu hồi bất cứ lúc nào.',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $demoPayload = [
        'analysis' => $demoAnalysis,
        'interpretation' => $demoInterpretation,
        'question' => 'Tôi nên chuẩn bị điều gì để năm mới và gia đạo thêm hài hòa, vững vàng?',
        'topic_label' => 'Tổng Quan & Vận Trình',
        'sub_topic' => 'Định hướng năm mới',
        'result_url' => null,
        'endpoints' => null,
    ];
@endphp

@section('title', 'Gieo Quẻ Kinh Dịch & Gieo Quẻ Đầu Năm Miễn phí | ' . $brandName)
@section('meta_description', 'Gieo quẻ Kinh Dịch 3 đồng xu chuẩn Chu Dịch, nhập kết quả tự gieo xu thật, Mai Hoa Dịch Số theo Âm lịch Việt Nam và gieo quẻ đầu năm miễn phí. Minh bạch thuật toán 64 quẻ, luận giải tinh tế và bảo mật riêng tư.')
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
     data-refine-url="{{ route('iching.refine_question') }}"
     data-can-cast="{{ $canCastFree ? '1' : '0' }}">

    {{-- 1. HERO TINH GỌN & BÀN GIEO QUẺ NGAY MÀN HÌNH ĐẦU --}}
    <section class="mayhen_iching_hero">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_hero_head">
                <span class="mayhen_iching_kicker">☯ Mây Hẹn Dịch Tâm • Chuẩn Cổ Học &amp; Minh Bạch Thuật Toán</span>
                <h1 class="mayhen_iching_hero_h1">
                    <span>Gieo một quẻ Dịch</span>, hiểu thêm một hướng đi
                </h1>
                <p class="mayhen_iching_hero_quote">
                    “Một quẻ Dịch không quyết định tương lai của bạn, nhưng có thể giúp bạn nhìn lại hiện tại và cân nhắc những lựa chọn phía trước.”
                </p>

                <div class="mayhen_iching_status_bar">
                    @if($canCastFree)
                        <span class="mayhen_iching_status_ok">✓ Đã mở khóa miễn phí ({{ $currentPlanLabel }})</span>
                        <span>•</span>
                        <a href="{{ route('iching.history') }}" class="mayhen_iching_status_link">
                            Sổ Quẻ Riêng Tư ({{ $recentReadings->count() }}) &rarr;
                        </a>
                    @else
                        <span>Tra cứu 64 quẻ công khai • Gieo quẻ &amp; luận giải miễn phí 100% khi đăng nhập (<strong>Thành Viên Premium 0đ</strong>)</span>
                        <a href="{{ route('login', ['redirect' => route('iching.index')]) }}" class="mayhen_iching_status_link">
                            Đăng Nhập Ngay &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- BÀN GIEO QUẺ ĐA PHƯƠNG PHÁP V2 --}}
            <div class="mayhen_iching_altar_card">
                <div class="mayhen_iching_method_tabs" role="tablist">
                    <button type="button" class="mayhen_iching_method_tab is-active" data-method="coins_3">
                        <strong>🪙 3 Đồng Xu Chu Dịch</strong>
                        <small>Tung 6 lần chuẩn xác suất 1/8 : 3/8</small>
                    </button>
                    <button type="button" class="mayhen_iching_method_tab" data-method="coins_manual">
                        <strong>✋ Tự Gieo Xu Thật</strong>
                        <small>Nhập 6 hào từ đồng xu thật của bạn</small>
                    </button>
                    <button type="button" class="mayhen_iching_method_tab" data-method="mai_hoa">
                        <strong>🌸 Mai Hoa Dịch Số</strong>
                        <small>Âm lịch VN (UTC+7) hoặc 2 số</small>
                    </button>
                    <button type="button" class="mayhen_iching_method_tab" data-method="spring_blessing">
                        <strong>🏮 Quẻ Đầu Năm</strong>
                        <small>Khai xuân an lành (lõi 3 đồng xu)</small>
                    </button>
                </div>

                <div class="mayhen_iching_altar_grid">
                    {{-- CỘT TRÁI: CHỦ ĐỀ, TÌNH HUỐNG CON, CÂU HỎI & RIÊNG TƯ --}}
                    <div class="mayhen_iching_altar_left">
                        <div class="mayhen_iching_field">
                            <label>1. Chọn chủ đề &amp; tình huống gần nhất với bạn</label>
                            <div class="mayhen_iching_topic_grid">
                                @foreach($topics as $tKey => $topicItem)
                                    <button type="button"
                                            class="mayhen_iching_topic_chip {{ $tKey === 'tong_quan' ? 'is-active' : '' }}"
                                            data-topic="{{ $tKey }}">
                                        {{ $topicItem['label'] }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="mayhen_iching_subtopic_wrap" id="mayhenIchingSubtopics"></div>
                        </div>

                        <div class="mayhen_iching_field">
                            <label for="mayhenIchingQuestion">2. Viết rõ câu hỏi bạn muốn tự quán chiếu *</label>
                            <textarea id="mayhenIchingQuestion"
                                      class="mayhen_iching_textarea"
                                      rows="3"
                                      maxlength="360"
                                      placeholder="Ví dụ: Chúng tôi cần chuẩn bị điều gì để buổi gặp mặt hai gia đình diễn ra ấm áp và thuận hòa?"></textarea>

                            <div class="mayhen_iching_quick_prompts">
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Tôi nên ưu tiên điều gì để năm mới vững vàng về gia đạo và công việc?">Định hướng năm mới</button>
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Chúng tôi cần chuẩn bị và vun đắp điều gì để tiến tới hôn nhân bền vững?">Chuẩn bị kết hôn</button>
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Tôi nên cân nhắc yếu tố nào trước khi chuyển sang hướng công việc mới?">Định hướng sự nghiệp</button>
                                <button type="button" class="mayhen_iching_quick_chip" data-sample-q="Tôi có thể làm gì để giữ hòa khí và sự tin tưởng giữa hai bên gia đình?">Hòa khí hai bên gia đình</button>
                            </div>

                            <div class="mayhen_iching_refine_box" id="mayhenIchingRefineBox" aria-live="polite"></div>
                            <div class="mayhen_iching_crisis_box" id="mayhenIchingCrisisBox" role="alert"></div>
                        </div>

                        <details class="mayhen_iching_optional_details">
                            <summary>Bổ sung hoàn cảnh thực tế (tự nguyện, giúp bài luận sát việc hơn)</summary>
                            <div class="mayhen_iching_optional_body">
                                <textarea id="mayhenIchingContext"
                                          class="mayhen_iching_textarea"
                                          rows="2"
                                          maxlength="600"
                                          placeholder="Ví dụ: Dự định tổ chức lễ cưới vào tháng 10 âm lịch, hai gia đình sống khác tỉnh..."></textarea>
                            </div>
                        </details>

                        <div class="mayhen_iching_options_row">
                            <label class="mayhen_iching_checkbox_label">
                                <input type="checkbox" id="mayhenIchingHideQuestion">
                                <span>Không lưu nội dung câu hỏi nhạy cảm vào Sổ Quẻ</span>
                            </label>
                            <div class="mayhen_iching_mindfulness_bar">
                                <button type="button" class="mayhen_iching_pill_btn" id="mayhenIchingMindfulBtn">🌿 Tĩnh tâm 10 giây</button>
                                <button type="button" class="mayhen_iching_pill_btn" id="mayhenIchingSoundBtn">🔕 Âm thanh: Tắt</button>
                            </div>
                        </div>

                        <div class="mayhen_iching_btn_row">
                            <button type="button" class="mayhen_iching_btn_primary" id="mayhenIchingSubmitCastBtn" style="flex:1;">
                                ✦ Lập Quẻ &amp; Luận Giải (Miễn Phí)
                            </button>
                            @if(! $canCastFree)
                                <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingShowDemoBtn">
                                    Xem Quẻ Mẫu
                                </button>
                            @endif
                        </div>

                        <p style="font-size:11.5px; color:#75695D; margin:10px 0 0; line-height:1.55;">
                            Lá quẻ của bạn được lưu <strong>riêng tư mặc định</strong> — chỉ mình bạn xem được. Nên tập trung vào 1 câu hỏi rõ ràng và tránh gieo lặp lại nhiều lần cho cùng một việc.
                        </p>
                    </div>

                    {{-- CỘT PHẢI: SÂN KHẤU 4 PHƯƠNG PHÁP --}}
                    <div class="mayhen_iching_altar_right">
                        {{-- PANEL 1: 3 ĐỒNG XU 3D --}}
                        <div class="mayhen_iching_stage_panel is-active" data-stage-panel="coins_3">
                            <p class="mayhen_iching_stage_title">Tung 3 Đồng Xu Cổ (Từ Hào 1 Lên Hào 6)</p>
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

                            <div class="mayhen_iching_btn_row mayhen_iching_btn_row_center">
                                <button type="button" class="mayhen_iching_btn_gold mayhen_iching_btn_sm" id="mayhenIchingTossOneBtn">🪙 Tung Hào 1/6</button>
                                <button type="button" class="mayhen_iching_btn_outline mayhen_iching_btn_sm" id="mayhenIchingTossAllBtn">⚡ Gieo Đủ 6 Hào</button>
                                <button type="button" class="mayhen_iching_btn_outline mayhen_iching_btn_sm" id="mayhenIchingResetCoinsBtn">↺ Làm lại</button>
                                <button type="button" class="mayhen_iching_pill_btn" id="mayhenIchingSkipAnimBtn">⚡ Bỏ qua hiệu ứng</button>
                            </div>
                        </div>

                        {{-- PANEL 2: NHẬP 6 HÀO THỦ CÔNG TỪ XU THẬT --}}
                        <div class="mayhen_iching_stage_panel" data-stage-panel="coins_manual">
                            <p class="mayhen_iching_stage_title">Nhập 6 Hào Tự Gieo Đồng Xu Thật</p>
                            <p class="mayhen_iching_stage_desc">
                                Bạn tung 3 đồng xu thật ở nhà 6 lần từ dưới lên (Ngửa = 3, Sấp = 2) rồi chọn đúng tổng từng hào bên dưới:
                            </p>
                            <div class="mayhen_iching_manual_stack">
                                @for($pos = 1; $pos <= 6; $pos++)
                                    <div class="mayhen_iching_manual_row">
                                        <span>Hào {{ $pos }} {{ $pos === 1 ? '(Sơ)' : ($pos === 6 ? '(Thượng)' : '') }}</span>
                                        <select id="mayhenIchingManualLine{{ $pos }}" class="mayhen_iching_select">
                                            <option value="7">7 — Thiếu Dương (2 Sấp 1 Ngửa • Tĩnh)</option>
                                            <option value="8">8 — Thiếu Âm (1 Sấp 2 Ngửa • Tĩnh)</option>
                                            <option value="9">9 — Lão Dương (3 Ngửa • Động ○)</option>
                                            <option value="6">6 — Lão Âm (3 Sấp • Động ×)</option>
                                        </select>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- PANEL 3: MAI HOA DỊCH SỐ --}}
                        <div class="mayhen_iching_stage_panel" data-stage-panel="mai_hoa">
                            <p class="mayhen_iching_stage_title">Mai Hoa Dịch Số (Âm Lịch VN &amp; Thể – Dụng)</p>
                            <p class="mayhen_iching_stage_desc">
                                Tính theo <strong>Âm lịch Việt Nam chuẩn UTC+7</strong> (đổi Chi năm tại Mùng 1 Tết, Giờ Tý 23h–23h59 tính sang ngày kế tiếp) và xét 25 tổ hợp Ngũ Hành Thể–Dụng.
                            </p>
                            <div style="max-width:350px; margin:0 auto; text-align:left;">
                                <div class="mayhen_iching_field">
                                    <label for="mayhenIchingMaiHoaMode">Chọn phép khởi quẻ Mai Hoa</label>
                                    <select id="mayhenIchingMaiHoaMode" class="mayhen_iching_select">
                                        <option value="mai_hoa_time">Theo Thời Khắc Âm Lịch Hiện Tại ({{ now('Asia/Ho_Chi_Minh')->format('H:i • d/m/Y') }})</option>
                                        <option value="mai_hoa_numbers">Theo 2 Số Khởi Tâm (Quy ước Mây Hẹn)</option>
                                    </select>
                                </div>
                                <div id="mayhenIchingMaiHoaNums" style="display:none; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="mayhen_iching_field">
                                        <label for="mayhenIchingNumUpper">Số 1 (Thượng Quái)</label>
                                        <input type="number" id="mayhenIchingNumUpper" class="mayhen_iching_input" min="1" max="99999" value="18">
                                    </div>
                                    <div class="mayhen_iching_field">
                                        <label for="mayhenIchingNumLower">Số 2 (Hạ Quái)</label>
                                        <input type="number" id="mayhenIchingNumLower" class="mayhen_iching_input" min="1" max="99999" value="26">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PANEL 4: GIEO QUẺ ĐẦU NĂM --}}
                        <div class="mayhen_iching_stage_panel" data-stage-panel="spring_blessing">
                            <p class="mayhen_iching_stage_title">Gieo Quẻ Đầu Năm • Khai Xuân An Lành</p>
                            <div class="mayhen_iching_tube_box" id="mayhenIchingTubeBox" aria-hidden="true">
                                <div class="mayhen_iching_tube_sticks">
                                    <span></span><span></span><span></span><span></span><span></span>
                                </div>
                                <div class="mayhen_iching_tube_body">XUÂN</div>
                            </div>
                            <p class="mayhen_iching_stage_desc">
                                Sử dụng minh bạch lõi thuật toán <strong>3 đồng xu Chu Dịch chuẩn</strong> kết hợp khung chiêm nghiệm định hướng năm mới. Bấm nút <strong>“Lập Quẻ &amp; Luận Giải”</strong> bên trái để bắt đầu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. KHU VỰC KẾT QUẢ 10 THÀNH PHẦN & 3 MỨC ĐỘ ĐỌC --}}
    <section class="mayhen_iching_result_section" id="mayhenIchingResultSection" style="display:none;">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_result_shell" id="mayhenIchingResultShell"></div>
        </div>
    </section>

    {{-- 3. THƯ VIỆN TRA CỨU 64 QUẺ KINH DỊCH THEO 10 CHỦ ĐỀ ĐỜI SỐNG --}}
    <section class="mayhen_iching_section" id="tra-cuu-64-que">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_section_head">
                <h2>Thư Viện 64 Quẻ Kinh Dịch (Chu Dịch)</h2>
                <p>Không có quẻ nào là tốt hay xấu tuyệt đối — mỗi quẻ phản ánh một quy luật vận động của cuộc sống. Tìm không dấu theo tên, số thứ tự hoặc lọc theo 10 chủ đề đời sống:</p>
            </div>

            <div class="mayhen_iching_catalog_controls">
                <div class="mayhen_iching_filter_pills">
                    <button type="button" class="mayhen_iching_filter_btn is-active" data-theme-filter="all">Tất cả (64)</button>
                    @foreach($libraryThemes as $theme)
                        <button type="button" class="mayhen_iching_filter_btn" data-theme-filter="{{ $theme['key'] }}">
                            {{ $theme['label'] }}
                        </button>
                    @endforeach
                </div>
                <input type="search"
                       id="mayhenIching64Search"
                       class="mayhen_iching_input mayhen_iching_search_input"
                       placeholder="Tìm quẻ (VD: Thai, Gia Nhan, #31...)">
            </div>

            <div class="mayhen_iching_64_grid">
                @foreach($hexagrams as $hex)
                    <button type="button"
                            class="mayhen_iching_64_card"
                            data-hex-no="{{ $hex['no'] }}"
                            data-themes="{{ implode(',', $hex['themes'] ?? []) }}"
                            data-search="#{{ $hex['no'] }} {{ $hex['no'] }} {{ $hex['name'] }} {{ $hex['hanzi'] }} {{ $hex['trigram_pair_label'] }} {{ $hex['meaning_short'] }}">
                        <div class="mayhen_iching_64_top">
                            <span class="mayhen_iching_64_no">Quẻ #{{ $hex['no'] }} • {{ $hex['guidance_state_ref'] }}</span>
                            <span class="mayhen_iching_64_hanzi">{{ $hex['unicode'] }} {{ $hex['hanzi'] }}</span>
                        </div>
                        <h3>{{ $hex['name'] }}</h3>
                        <p>{{ $hex['meaning_short'] }}</p>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. MINH BẠCH PHƯƠNG PHÁP & FAQ CHUẨN SEO --}}
    <section class="mayhen_iching_section mayhen_iching_section_soft">
        <div class="mayhen_iching_container">
            <div class="mayhen_iching_section_head">
                <h2>Minh Bạch Thuật Toán &amp; Kho Tri Thức Mây Hẹn Dịch Tâm</h2>
                <p>Phiên bản kho tri thức <strong>v{{ $manifest['kb_version'] ?? '2.0.0' }}</strong> — tách bạch nguồn cổ thư, quy tắc tính toán tất định và phần AI diễn giải ứng dụng.</p>
            </div>

            <div class="mayhen_iching_faq_list">
                <div class="mayhen_iching_faq_item">
                    <h3>Phép gieo 3 đồng xu Chu Dịch và quy tắc Chu Hy được tính như thế nào?</h3>
                    <p>Mỗi hào gieo 3 đồng xu độc lập (Ngửa = 3, Sấp = 2) với phân phối chuẩn 1/8 Lão Âm (6), 3/8 Thiếu Dương (7), 3/8 Thiếu Âm (8), 1/8 Lão Dương (9). Hệ thống áp dụng đầy đủ quy tắc chọn Quái Từ / Hào Từ của Chu Hy (<em>Dịch Học Khải Mông</em>), bao gồm cả 20 trường hợp 3 hào động (Tiền thập quái / Hậu thập quái) và Dụng Cửu / Dụng Lục của Thuần Càn, Thuần Khôn.</p>
                </div>
                <div class="mayhen_iching_faq_item">
                    <h3>Mai Hoa Dịch Số sử dụng lịch nào và khác gì phép gieo đồng xu?</h3>
                    <p>Mai Hoa Dịch Số trên Mây Hẹn chuyển đổi chính xác sang <strong>Âm lịch Việt Nam múi giờ UTC+7</strong> (đổi Địa Chi năm tại Mùng 1 Tết Nguyên Đán, Giờ Tý đêm 23h–23h59 tính sang ngày Âm lịch kế tiếp), áp dụng quy tắc <em>“Càn Khôn vô hỗ, hỗ kỳ biến quái”</em> và ma trận 25 tổ hợp Ngũ Hành Thể–Dụng. Hệ thống không trộn Thể–Dụng sang phép gieo 3 đồng xu.</p>
                </div>
                <div class="mayhen_iching_faq_item">
                    <h3>Quyền riêng tư và cam kết nhân văn của Mây Hẹn Dịch Tâm?</h3>
                    <p>Mọi lá quẻ lưu trong Sổ Quẻ đều riêng tư mặc định — không công khai qua đường dẫn. Bạn có thể chọn ẩn câu hỏi nhạy cảm khi lưu, tạo Thẻ Chia Sẻ ẩn danh có thể thu hồi, và tuyệt đối không gặp những lời hù dọa mê tín hay phán quyết cực đoan.</p>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- MODAL CHI TIẾT 1 QUẺ TRONG THƯ VIỆN 64 QUẺ --}}
<div class="mayhen_iching_modal_backdrop" id="mayhenIchingHexModal">
    <div class="mayhen_iching_modal">
        <div style="display:flex; justify-content:flex-end; margin-bottom:6px;">
            <button type="button" class="mayhen_iching_btn_outline mayhen_iching_btn_sm" id="mayhenIchingHexModalClose">Đóng ✕</button>
        </div>
        <div id="mayhenIchingHexModalBody"></div>
    </div>
</div>

{{-- MODAL YÊU CẦU ĐĂNG NHẬP MIỄN PHÍ GÓI PREMIUM --}}
<div class="mayhen_iching_modal_backdrop" id="mayhenIchingLoginModal">
    <div class="mayhen_iching_modal" style="text-align:center;">
        <span class="mayhen_iching_kicker">Miễn Phí 100% Cho Thành Viên Premium (0đ)</span>
        <h2 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; margin:8px 0 10px;">
            Đăng Nhập Để Gieo Quẻ &amp; Lưu Sổ Quẻ Riêng Tư
        </h2>
        <p style="font-size:14px; color:#544A42; line-height:1.65; margin:0 0 20px;">
            Chỉ cần đăng nhập tài khoản {{ $brandName }} (tự động cấp miễn phí gói <strong>Thành Viên Premium 0đ</strong>), bạn sẽ mở khóa trải nghiệm Gieo Quẻ Kinh Dịch, Hỏi Sâu Về Quẻ, Nhật Ký Nhìn Lại và Sổ Quẻ hoàn toàn riêng tư.
        </p>
        <div class="mayhen_iching_btn_row mayhen_iching_btn_row_center">
            <a href="{{ route('login', ['redirect' => route('iching.index')]) }}" class="mayhen_iching_btn_primary">Đăng Nhập Miễn Phí &rarr;</a>
            <a href="{{ route('register') }}" class="mayhen_iching_btn_gold">Tạo Tài Khoản Mới (0đ)</a>
            <button type="button" class="mayhen_iching_btn_outline" id="mayhenIchingLoginModalClose">Để Sau</button>
        </div>
    </div>
</div>

<script id="mayhenIchingCatalogJson" type="application/json">@json($hexagrams, JSON_UNESCAPED_UNICODE)</script>
<script id="mayhenIchingTaxonomyJson" type="application/json">@json($topicsTaxonomy, JSON_UNESCAPED_UNICODE)</script>
<script id="mayhenIchingDemoJson" type="application/json">@json($demoPayload, JSON_UNESCAPED_UNICODE)</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/iching.js') }}"></script>
@endpush
