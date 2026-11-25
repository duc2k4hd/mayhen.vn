@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('numerology.index');
    $currentYear = now('Asia/Ho_Chi_Minh')->year;

    $schemaGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Tra Cứu Thần Số Học Online & Thần Số Học Tình Duyên Miễn Phí',
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'Web',
                'url' => $canonicalUrl,
                'description' => 'Tra cứu Thần Số Học Online chuẩn Pythagorean (Hans Decoz) & David A. Phillips, xử lý chuẩn họ tên tiếng Việt và chữ Y, Biểu đồ ngày sinh 3x3, 4 Đỉnh cao, Chu kỳ năm cá nhân và Thần Số Học Tình Duyên 10 chủ đề miễn phí.',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tra Cứu Thần Số Học Online & Thần Số Học Tình Duyên Miễn Phí', 'item' => $canonicalUrl],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Tra cứu Thần Số Học trên Mây Hẹn có thực sự miễn phí không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hoàn toàn miễn phí. Ngay cả khách chưa đăng nhập vẫn xem được tức thời toàn bộ bộ chỉ số cốt lõi, bảng Cách tính của tôi từng ký tự, Biểu đồ ngày sinh 3×3 và nội dung biên tập chuẩn hóa. Khi đăng nhập (tự động cấp gói Thành Viên Premium 0đ), bạn được lưu hồ sơ riêng tư, nhận luận giải AI 6 tầng, Hỏi Sâu AI, Nhật ký và Xuất thẻ nghệ thuật PNG.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Vì sao số chủ đạo ở Mây Hẹn có thể khác một số trang web khác?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Thần số học có nhiều phương pháp: mặc định Mây Hẹn áp dụng Pythagorean hiện đại tham chiếu Hans Decoz (rút gọn riêng Ngày, Tháng, Năm giữ Master 11, 22, 33) và đồng thời hỗ trợ phương pháp David A. Phillips (cộng thẳng toàn bộ chữ số ngày sinh, giữ 10, 11, 22/4). Ví dụ ngày 11/11/2009 cho 33/6 theo Decoz và số nền 6 theo phép cộng thẳng — hệ thống luôn hiển thị rõ sự đối chiếu này.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Họ tên tiếng Việt có dấu và chữ Đ, chữ Y được tính như thế nào?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hệ thống chuẩn hóa Unicode NFC/NFD đồng nhất, bỏ dấu thanh/mũ, ánh xạ Đ thành D theo quy ước bản địa hóa minh bạch, phân nhóm Họ – Tên đệm – Tên chính và mặc định phân loại Y là nguyên âm trong tên tiếng Việt (Yến, Vy, Mỹ, Duy, Nguyễn, Quỳnh). Bạn có thể tự kiểm tra và đảo vai trò từng chữ Y trong mục Cách tính của tôi.',
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', 'Tra Cứu Thần Số Học Online & Thần Số Học Tình Duyên Miễn Phí')
@section('meta_description', 'Tra cứu Thần Số Học Online chuẩn Pythagorean (Hans Decoz) & David A. Phillips hoàn toàn miễn phí tại Mây Hẹn. Minh bạch từng bước tính họ tên tiếng Việt & chữ Y, Biểu đồ ngày sinh 3x3, 4 Đỉnh cao và Thần Số Học Tình Duyên 10 chủ đề không chấm % hợp tuổi.')
@section('meta_keywords', 'tra cứu thần số học, xem thần số học online, thần số học miễn phí, thần số học tình duyên, tính số chủ đạo, biểu đồ ngày sinh, năm cá nhân')
@section('canonical', $canonicalUrl)
@section('og_type', 'website')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_numerology_page"
     id="mayhenNumerologyApp"
     data-preview-url="{{ route('numerology.calculate_preview') }}"
     data-store-url="{{ route('numerology.store') }}"
     data-can-ai="{{ $canUseAi ? '1' : '0' }}">

    {{-- 1. HERO EDITORIAL & KHUNG TRA CỨU 3 BƯỚC TUẦN TỰ --}}
    <section class="mayhen_numerology_hero">
        <div class="mayhen_numerology_container">
            <div class="mayhen_numerology_hero_grid">
                <div>
                    <span class="mayhen_numerology_eyebrow">Mây Hẹn · Dấu Ấn Con Số — Pythagorean &amp; Biểu Đồ Ngày Sinh</span>
                    <h1 class="mayhen_numerology_h1">
                        Tra Cứu <em>Thần Số Học Online</em> &amp; Thần Số Học Tình Duyên Miễn Phí
                    </h1>
                    <p class="mayhen_numerology_lead">
                        Khám phá những con số gắn với ngày sinh và họ tên của bạn. Hiểu thêm về bản thân, tình yêu và những điều hai người có thể cùng vun đắp — minh bạch 100% từng phép tính, không bói toán mê tín.
                    </p>

                    <div class="mayhen_numerology_mode_switch" role="navigation" aria-label="Hai chế độ tra cứu chính">
                        <a href="{{ route('numerology.index') }}" class="mayhen_numerology_mode_btn is-active">Thần Số Học Cá Nhân</a>
                        <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_mode_btn">Thần Số Học Tình Duyên</a>
                    </div>

                    <div class="mayhen_numerology_status_line">
                        @if($canUseAi)
                            <span>Gói hiện tại: <strong>{{ $currentPlanLabel }}</strong></span>
                            <span>Còn <strong>{{ $quota['daily_remaining'] }}/{{ $quota['daily_limit'] }}</strong> lượt AI hôm nay ({{ $quota['cooldown_label'] }})</span>
                            <a href="{{ route('numerology.profiles') }}">Hồ Sơ Đã Lưu &rarr;</a>
                        @else
                            <span>Khách xem miễn phí tức thời toàn bộ chỉ số &amp; cách tính.</span>
                            <a href="{{ route('login', ['redirect' => route('numerology.index')]) }}">Đăng nhập 0đ để lưu hồ sơ &amp; mở AI 6 tầng &rarr;</a>
                        @endif
                    </div>
                </div>

                {{-- KHUNG TRA CỨU 3 TAB TUẦN TỰ CÓ KHÓA --}}
                <div class="mayhen_numerology_sheet">
                    <div class="mayhen_numerology_stepper" role="tablist" aria-label="3 bước tra cứu Thần Số Học">
                        <button type="button" class="mayhen_numerology_step_btn is-active" data-num-tab="1">
                            <span class="mayhen_numerology_step_num" data-num-tab-badge>1</span>
                            <span>Ngày Sinh</span>
                        </button>
                        <button type="button" class="mayhen_numerology_step_btn is-locked" data-num-tab="2">
                            <span class="mayhen_numerology_step_num" data-num-tab-badge>2</span>
                            <span>Họ Tên &amp; Chữ Y</span>
                        </button>
                        <button type="button" class="mayhen_numerology_step_btn is-locked" data-num-tab="3">
                            <span class="mayhen_numerology_step_num" data-num-tab-badge>3</span>
                            <span>Chủ Đề &amp; Kết Quả</span>
                        </button>
                    </div>

                    <div class="mayhen_numerology_sheet_body">
                        {{-- BƯỚC 1: NGÀY SINH & PHƯƠNG PHÁP --}}
                        <div class="mayhen_numerology_step_panel is-active" data-num-panel="1">
                            <div class="mayhen_numerology_grid_3">
                                <div class="mayhen_numerology_field">
                                    <label for="numBirthDay">Ngày sinh (1–31) *</label>
                                    <input id="numBirthDay" type="number" class="mayhen_numerology_input" min="1" max="31" placeholder="VD: 15" required>
                                </div>
                                <div class="mayhen_numerology_field">
                                    <label for="numBirthMonth">Tháng sinh (1–12) *</label>
                                    <input id="numBirthMonth" type="number" class="mayhen_numerology_input" min="1" max="12" placeholder="VD: 5" required>
                                </div>
                                <div class="mayhen_numerology_field">
                                    <label for="numBirthYear">Năm sinh dương lịch *</label>
                                    <input id="numBirthYear" type="number" class="mayhen_numerology_input" min="1900" max="2100" placeholder="VD: 1995" required>
                                </div>
                            </div>

                            <div class="mayhen_numerology_grid_2">
                                <div class="mayhen_numerology_field">
                                    <label for="numMethodId">Phương pháp tính</label>
                                    <select id="numMethodId" class="mayhen_numerology_select">
                                        <option value="pythagorean_decoz_v1" selected>Pythagorean Hiện Đại — Hans Decoz (Mặc định)</option>
                                        <option value="phillips_vn_v1">Nhân Số Học — David A. Phillips (Giữ 10, 11, 22/4)</option>
                                    </select>
                                </div>
                                <div class="mayhen_numerology_field">
                                    <label for="numViewedYear">Năm xem chu kỳ cá nhân</label>
                                    <input id="numViewedYear" type="number" class="mayhen_numerology_input" min="1900" max="2100" value="{{ $currentYear }}">
                                </div>
                            </div>

                            <div id="numStep1Err" class="mayhen_numerology_alert_error" role="alert"></div>

                            <div class="mayhen_numerology_actions">
                                <span style="font-size: 12px; color: #6B5F52;">Xử lý thuần ngày lịch dd/mm/yyyy</span>
                                <button type="button" class="mayhen_numerology_btn_primary" data-num-next="2">Tiếp tục: Nhập Họ Tên &rarr;</button>
                            </div>
                        </div>

                        {{-- BƯỚC 2: HỌ TÊN TIẾNG VIỆT & QUY ƯỚC CHỮ Y --}}
                        <div class="mayhen_numerology_step_panel" data-num-panel="2">
                            <div class="mayhen_numerology_field">
                                <label for="numFullBirthName">Họ tên khai sinh đầy đủ (tùy chọn — để tính Sứ Mệnh, Linh Hồn, Nhân Cách)</label>
                                <input id="numFullBirthName" type="text" class="mayhen_numerology_input" maxlength="160" placeholder="Ví dụ: Nguyễn Thị Minh Anh hoặc Đỗ An">
                            </div>

                            <div class="mayhen_numerology_grid_2">
                                <div class="mayhen_numerology_field">
                                    <label for="numEverydayName">Tên thường gọi hiện tại (tách riêng, tùy chọn)</label>
                                    <input id="numEverydayName" type="text" class="mayhen_numerology_input" maxlength="100" placeholder="Ví dụ: Minh An">
                                </div>
                                <div class="mayhen_numerology_field">
                                    <label for="numYMode">Quy ước ngữ âm chữ Y</label>
                                    <select id="numYMode" class="mayhen_numerology_select">
                                        <option value="vn_phonetic_y_v1" selected>Tiếng Việt (Y là nguyên âm: Yến, Vy, Mỹ, Duy, Nguyễn)</option>
                                        <option value="en_phonetic_y_v1">Tiếng Anh / Quốc tế (Y đầu âm tiết trước NA là phụ âm)</option>
                                    </select>
                                </div>
                            </div>

                            <details class="mayhen_numerology_note_box" style="margin-top: 4px;">
                                <summary style="cursor: pointer; font-weight: 700;">Tùy chỉnh phân nhóm Họ ghép / Tên ghép (không bắt buộc)</summary>
                                <div class="mayhen_numerology_grid_3" style="margin-top: 10px;">
                                    <input id="numGroupLast" type="text" class="mayhen_numerology_input" placeholder="Họ (VD: Nguyễn Trần)" aria-label="Họ">
                                    <input id="numGroupMiddle" type="text" class="mayhen_numerology_input" placeholder="Tên đệm" aria-label="Tên đệm">
                                    <input id="numGroupFirst" type="text" class="mayhen_numerology_input" placeholder="Tên chính (VD: Minh Anh)" aria-label="Tên chính">
                                </div>
                            </details>

                            <div id="numStep2Err" class="mayhen_numerology_alert_error" role="alert"></div>

                            <div class="mayhen_numerology_actions">
                                <button type="button" class="mayhen_numerology_btn_outline" data-num-prev="1">&larr; Quay lại</button>
                                <button type="button" class="mayhen_numerology_btn_primary" data-num-next="3">Tiếp tục: Chọn Chủ Đề &rarr;</button>
                            </div>
                        </div>

                        {{-- BƯỚC 3: CHỦ ĐỀ & XEM KẾT QUẢ --}}
                        <div class="mayhen_numerology_step_panel" data-num-panel="3">
                            <div class="mayhen_numerology_field">
                                <label>Chủ đề bạn muốn ưu tiên chiêm nghiệm</label>
                                <div class="mayhen_numerology_topic_grid">
                                    @foreach($focusTopics as $tKey => $tLabel)
                                        <button type="button"
                                                class="mayhen_numerology_topic_btn {{ $tKey === 'toan_bo' ? 'is-active' : '' }}"
                                                data-num-topic="{{ $tKey }}">
                                            {{ $tLabel }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mayhen_numerology_field">
                                <label for="numContextNote">Hoàn cảnh hoặc câu hỏi thực tế (tùy chọn — giúp AI luận sát việc hơn)</label>
                                <textarea id="numContextNote" rows="2" class="mayhen_numerology_textarea" maxlength="500" placeholder="Ví dụ: Mình đang chuẩn bị chuyển công việc và lập kế hoạch cưới vào cuối năm..."></textarea>
                            </div>

                            <div id="numSubmitErr" class="mayhen_numerology_alert_error" role="alert"></div>

                            <div class="mayhen_numerology_actions">
                                <button type="button" class="mayhen_numerology_btn_outline" data-num-prev="2">&larr; Quay lại</button>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <button type="button" id="numFreePreviewBtn" class="mayhen_numerology_btn_primary">Tra Cứu Miễn Phí Ngay</button>
                                    <button type="button" id="numSaveAiBtn" class="mayhen_numerology_btn_crimson">Lưu Hồ Sơ &amp; Luận Giải AI 6 Tầng (0đ)</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KHU VỰC HIỂN THỊ KẾT QUẢ TRA CỨU NHANH MIỄN PHÍ NGAY TRÊN TRANG --}}
            <div id="mayhenNumInstantResult" style="display: none; margin-top: 34px;"></div>
        </div>
    </section>

    {{-- 2. BỘ 12 THẺ NGHỆ THUẬT CON SỐ (1..9, 11, 22, 33 — XÒE QUẠT TRỌN 1 MÀN, ZERO SCROLLBAR) --}}
    <section class="mayhen_numerology_section mayhen_numerology_section--paper">
        <div class="mayhen_numerology_container">
            <div class="mayhen_numerology_section_head">
                <div>
                    <span class="mayhen_numerology_eyebrow">Nghệ Thuật Hình Học &amp; Con Số</span>
                    <h2>Bộ 12 Thẻ Con Số Cơ Bản &amp; Master Number</h2>
                    <p>Toàn bộ 12 thẻ (1–9 và 11/2, 22/4, 33/6) xòe trọn trên một khung hình không thanh cuộn. Chạm thẻ bất kỳ hoặc chọn số bên dưới để lật đọc ý nghĩa cốt lõi và góc nhìn tình yêu.</p>
                </div>
                <div class="mayhen_numerology_fan_controls">
                    <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-fan-random>Rút ngẫu nhiên 1 thẻ</button>
                    <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-fan-reset>Úp gọn bộ thẻ</button>
                </div>
            </div>

            <div class="mayhen_numerology_fan_shell">
                <div class="mayhen_numerology_fan_stage" data-num-fan-stage>
                    <div class="mayhen_numerology_fan_hand" data-num-fan-hand></div>
                </div>
                <div class="mayhen_numerology_fan_pills" data-num-fan-pills role="tablist" aria-label="Chọn nhanh con số"></div>
            </div>
            <div class="mayhen_numerology_drawer" data-num-card-drawer aria-live="polite"></div>
        </div>
    </section>

    {{-- 3. THẦN SỐ HỌC TÌNH DUYÊN — ĐIỂM KHÁC BIỆT CỦA MÂY HẸN --}}
    <section class="mayhen_numerology_section mayhen_numerology_section--ivory">
        <div class="mayhen_numerology_container">
            <div class="mayhen_numerology_love_banner">
                <span class="mayhen_numerology_eyebrow">Thần Số Học Tình Duyên &amp; Câu Chuyện Hai Chúng Ta</span>
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: clamp(24px, 3vw, 34px); margin: 4px 0 10px;">
                    Đối Chiếu Hai Hồ Sơ Độc Lập — Không Chấm % Hợp Tuổi
                </h2>
                <p style="max-width: 680px; margin: 0 auto 12px; font-size: 15px; line-height: 1.7; color: #4E463F;">
                    Phân tích đa tầng Đường đời (ma trận 45 cặp đối xứng), Sứ mệnh, Linh hồn, Nhân cách và Chu kỳ năm trên 10 chủ đề thực tế của tình yêu, hôn nhân, gia đình hai bên — kèm không gian tương tác <strong>Lời hẹn 30 ngày</strong> dành riêng cho cặp đôi.
                </p>

                <div class="mayhen_numerology_love_duet">
                    <div class="mayhen_numerology_love_emblem">
                        <div style="font-size: 11px; letter-spacing: 0.12em; color: #1E4D3B; font-weight: 700;">HỒ SƠ NGƯỜI THỨ NHẤT</div>
                        <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 46px; color: #85182A; line-height: 1.1;">6</div>
                        <div style="font-size: 12px; color: #5C5248;">Chăm Sóc &amp; Trách Nhiệm</div>
                    </div>
                    <div class="mayhen_numerology_love_bridge" aria-hidden="true"></div>
                    <div class="mayhen_numerology_love_emblem">
                        <div style="font-size: 11px; letter-spacing: 0.12em; color: #1E4D3B; font-weight: 700;">HỒ SƠ NGƯỜI ĐỒNG HÀNH</div>
                        <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 46px; color: #1E4D3B; line-height: 1.1;">4</div>
                        <div style="font-size: 12px; color: #5C5248;">Nền Tảng &amp; Kỷ Luật</div>
                    </div>
                </div>

                <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_btn_crimson">
                    Mở Không Gian Thần Số Học Tình Duyên &rarr;
                </a>
            </div>
        </div>
    </section>

    {{-- 4. BẢNG QUY ĐỔI MINH BẠCH & BÁCH KHOA THẦN SỐ HỌC --}}
    <section class="mayhen_numerology_section mayhen_numerology_section--paper">
        <div class="mayhen_numerology_container">
            <div class="mayhen_numerology_section_head">
                <div>
                    <span class="mayhen_numerology_eyebrow">Minh Bạch Công Thức &amp; Thư Viện Tri Thức</span>
                    <h2>Bảng Quy Đổi Pythagorean &amp; Bách Khoa Thần Số Học</h2>
                    <p>Mọi công thức, quy ước tiếng Việt và nguồn tham chiếu đều được công bố minh bạch để bạn tự kiểm chứng.</p>
                </div>
                <a href="{{ route('numerology.library.index') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Xem Toàn Bộ Thư Viện &rarr;</a>
            </div>

            <table class="mayhen_numerology_table" style="margin-bottom: 24px;">
                <thead>
                    <tr>
                        <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A · J · S</td><td>B · K · T</td><td>C · L · U</td><td>D · M · V</td><td>E · N · W</td><td>F · O · X</td><td>G · P · Y</td><td>H · Q · Z</td><td>I · R</td>
                    </tr>
                </tbody>
            </table>

            <div class="mayhen_numerology_grid_3">
                <a href="{{ route('numerology.library.life_path') }}" class="mayhen_numerology_note_box" style="text-decoration: none; background: #FAF5EB;">
                    <strong style="color: #85182A;">Số Chủ Đạo &amp; Đường Đời (1–9, 11, 22, 33)</strong><br>
                    So sánh công thức Hans Decoz vs David A. Phillips và giải thích Master Number.
                </a>
                <a href="{{ route('numerology.library.indicators') }}" class="mayhen_numerology_note_box" style="text-decoration: none; background: #FAF5EB;">
                    <strong style="color: #1E4D3B;">Hệ Thống Chỉ Số Cốt Lõi &amp; Mở Rộng</strong><br>
                    Sứ Mệnh, Linh Hồn, Nhân Cách, Trưởng Thành, Cân Bằng, Đỉnh Cao &amp; Thử Thách.
                </a>
                <a href="{{ route('numerology.library.birth_chart') }}" class="mayhen_numerology_note_box" style="text-decoration: none; background: #FAF5EB;">
                    <strong style="color: #292524;">Biểu Đồ Ngày Sinh 3×3 &amp; Năm Cá Nhân {{ $currentYear }}</strong><br>
                    Lưới 3×3 cùng 8 đường mũi tên theo David A. Phillips và chu kỳ 9 năm.
                </a>
            </div>
        </div>
    </section>

    {{-- 5. HỒ SƠ GẦN ĐÂY (NẾU ĐÃ ĐĂNG NHẬP) --}}
    @if($recentReadings->isNotEmpty())
        <section class="mayhen_numerology_section mayhen_numerology_section--ivory">
            <div class="mayhen_numerology_container">
                <div class="mayhen_numerology_section_head">
                    <div>
                        <span class="mayhen_numerology_eyebrow">Hồ Sơ Riêng Tư Của Bạn</span>
                        <h2>Các Hồ Sơ Thần Số Học Gần Đây</h2>
                    </div>
                    <a href="{{ route('numerology.profiles') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Xem Tất Cả &rarr;</a>
                </div>
                <div class="mayhen_numerology_grid_2">
                    @foreach($recentReadings as $rr)
                        <div class="mayhen_numerology_note_box" style="background: #FCFBF9; display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                            <div>
                                <div style="font-size: 11px; color: #1E4D3B; font-weight: 700;">v{{ $rr->version_number }} • Đường đời {{ $rr->calculation_payload_json['core_numbers']['life_path']['display'] ?? '—' }}</div>
                                <strong>{{ $rr->profile?->display_name ?: 'Hồ Sơ Con Số' }}</strong>
                            </div>
                            <a href="{{ route('numerology.show', $rr->slug) }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Mở Hồ Sơ &rarr;</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- MÀN CHUYỂN CẢNH TÍNH TOÁN ÊM ÁI (ZERO LAYOUT SHIFT, CÓ NÚT BỎ QUA) --}}
    <div id="mayhenNumCalcOverlay" class="mayhen_numerology_calc_overlay" role="status" aria-live="polite" aria-hidden="true">
        <div class="mayhen_numerology_calc_box">
            <span class="mayhen_numerology_eyebrow">Mây Hẹn · Dấu Ấn Con Số</span>

            <div class="mayhen_numerology_calc_orbit" aria-hidden="true">
                <svg viewBox="0 0 100 100" width="84" height="84">
                    <circle class="mayhen_numerology_orbit_outer" cx="50" cy="50" r="44" fill="none" stroke="#CFA860" stroke-width="1.2" stroke-dasharray="3 4"/>
                    <g class="mayhen_numerology_orbit_inner">
                        <rect x="27" y="27" width="46" height="46" fill="none" stroke="#85182A" stroke-width="1.1" opacity="0.55"/>
                        <polygon points="50,18 82,50 50,82 18,50" fill="none" stroke="#1E4D3B" stroke-width="1.1" opacity="0.6"/>
                    </g>
                    <circle class="mayhen_numerology_orbit_core" cx="50" cy="50" r="5" fill="#85182A"/>
                </svg>
            </div>

            <div class="mayhen_numerology_calc_stage">
                <div id="mayhenNumCalcDigits" class="mayhen_numerology_calc_digits">05 · 05 · 2004</div>
                <p id="mayhenNumCalcLabel" class="mayhen_numerology_calc_label">Chuẩn hóa ngày sinh dương lịch &amp; quy ước hệ tính</p>
            </div>

            <div class="mayhen_numerology_calc_progress" aria-hidden="true">
                <div id="mayhenNumCalcBar" class="mayhen_numerology_calc_bar"></div>
            </div>

            <div class="mayhen_numerology_calc_dots" id="mayhenNumCalcDots" aria-hidden="true">
                <span class="mayhen_numerology_calc_dot is-active"></span>
                <span class="mayhen_numerology_calc_dot"></span>
                <span class="mayhen_numerology_calc_dot"></span>
                <span class="mayhen_numerology_calc_dot"></span>
            </div>

            <button type="button" id="mayhenNumSkipAnim" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Bỏ qua hiệu ứng</button>
        </div>
    </div>

    {{-- MODAL ĐĂNG NHẬP MIỄN PHÍ 0Đ --}}
    <div id="mayhenNumLoginModal" class="mayhen_numerology_modal" role="dialog" aria-modal="true" aria-labelledby="mayhenNumLoginTitle">
        <div class="mayhen_numerology_modal_card">
            <span class="mayhen_numerology_eyebrow">Miễn Phí Thành Viên Mây Hẹn (0đ)</span>
            <h3 id="mayhenNumLoginTitle" style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 24px; margin: 0 0 10px; color: #85182A;">
                Đăng nhập để lưu hồ sơ &amp; mở Luận Giải AI 6 Tầng
            </h3>
            <p style="font-size: 14px; line-height: 1.65; color: #4E463F; margin: 0 0 18px;">
                Bạn vẫn có thể bấm <strong>“Tra Cứu Miễn Phí Ngay”</strong> mà không cần đăng nhập. Khi đăng nhập (tự động kích hoạt gói Thành Viên Premium 0đ), bạn được lưu hồ sơ riêng tư, nhận Luận giải AI 6 tầng, Hỏi Sâu AI, Nhật ký và Xuất thẻ nghệ thuật PNG.
            </p>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('login', ['redirect' => route('numerology.index')]) }}" class="mayhen_numerology_btn_primary">Đăng nhập ngay</a>
                <a href="{{ route('register') }}" class="mayhen_numerology_btn_outline">Tạo tài khoản 0đ</a>
                <button type="button" id="mayhenNumLoginClose" class="mayhen_numerology_btn_outline">Đóng</button>
            </div>
        </div>
    </div>

    <script id="mayhenNumArchetypesJson" type="application/json">@json($archetypes, JSON_UNESCAPED_UNICODE)</script>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/numerology.js') }}"></script>
@endpush
