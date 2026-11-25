@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('tuvi.index');
    $currentYear = now('Asia/Ho_Chi_Minh')->year;

    $schemaGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Xem Tử Vi Online, Lập Và Luận Lá Số Tử Vi Miễn Phí — ' . $brandName,
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'Web',
                'url' => $canonicalUrl,
                'description' => 'Lập lá số Tử Vi Đẩu Số chuẩn Nam Phái Việt Nam, tương tác Thập Nhị Thiên Mệnh Bài 3D mở đúng dữ liệu 12 cung thật, Nhân Duyên Song Mệnh cho cặp đôi và luận giải AI 6 tầng minh bạch căn cứ.',
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
                        'name' => 'Xem Tử Vi Trọn Đời & Lập Lá Số Tử Vi',
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Lập lá số Tử Vi trên Mây Hẹn – Thiên Mệnh có miễn phí không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hoàn toàn miễn phí. Bạn có thể trải nghiệm Thập Nhị Thiên Mệnh Bài 3D mẫu và tra cứu Bách Khoa Tử Vi công khai, hoặc đăng nhập tài khoản Mây Hẹn (tự động nhận gói Thành Viên Premium 0đ) để lập lá số cá nhân, nhận luận giải AI 6 tầng và lưu Hồ Sơ Thiên Mệnh riêng tư.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Thập Nhị Thiên Mệnh Bài 3D có phải là rút bài Tarot ngẫu nhiên không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Không. Mây Hẹn phân biệt tuyệt đối Tử Vi Đẩu Số với rút bài ngẫu nhiên. Mỗi lá Thiên Mệnh Bài đại diện cho 1 trong 12 Cung đã được an sao tất định từ ngày tháng năm giờ sinh thật của bạn. Lật thẻ là một cách khám phá lá số sống động, không bao giờ thay đổi sao hay sinh kết quả ngẫu nhiên.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Không nhớ rõ giờ sinh thì có xem được Tử Vi không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Có, và hệ thống xử lý hoàn toàn minh bạch: nếu bạn nhớ khoảng giờ (như Sáng sớm, Trưa, Chiều), hệ thống lập các lá số ứng viên để bạn đối chiếu; nếu hoàn toàn không biết giờ sinh, hệ thống chỉ phân tích Bản Mệnh Ngũ Hành & Năm Sinh và không bao giờ tự gán giờ giả 12:00.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Thông tin ngày giờ sinh và lá số Tử Vi của tôi có được bảo mật không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Mọi lá số cá nhân và báo cáo Nhân Duyên Song Mệnh mặc định ở chế độ Riêng tư (trả lỗi 403 với người lạ và chặn công cụ tìm kiếm noindex). Dữ liệu gửi sang AI luôn được ẩn danh, không kèm tên thật, email hay số điện thoại.',
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', 'Xem Tử Vi Online, Lập Và Luận Lá Số Tử Vi Miễn Phí | ' . $brandName)
@section('meta_description', 'Lập lá số Tử Vi Đẩu Số chuẩn Nam Phái Việt Nam miễn phí tại Mây Hẹn – Thiên Mệnh. Khám phá 12 Thiên Mệnh Bài 3D, Thiên Bàn tương tác 4x4, Nhân Duyên Song Mệnh cho cặp đôi và luận giải AI 6 tầng sâu sắc, nhân văn.')
@section('meta_keywords', 'xem tử vi trọn đời, lập lá số tử vi miễn phí, tử vi đẩu số nam phái, xem tử vi nhân duyên, 12 cung tử vi, cách cục tử vi')
@section('canonical', $canonicalUrl)
@section('og_type', 'website')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_tuvi_page"
     id="mayhenTuViApp"
     data-generate-url="{{ route('tuvi.generate') }}"
     data-preview-url="{{ route('tuvi.preview_calendar') }}"
     data-can-use="{{ $canUseFree ? '1' : '0' }}">

    {{-- 1. KHÔNG GIAN THIÊN VĂN PHƯƠNG ĐÔNG & WIZARD 4 BƯỚC --}}
    <section class="mayhen_tuvi_celestial_hero">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_hero_head">
                <span class="mayhen_tuvi_kicker">✦ Mây Hẹn – Thiên Mệnh • Chuẩn Nam Phái Việt Nam ({{ $manifest['rule_version'] ?? 'vn_nam_phai_v1.0.0' }})</span>
                <h1 class="mayhen_tuvi_hero_h1">
                    <span>Hiểu mình, hiểu người</span>, vun đắp nhân duyên
                </h1>
                <p class="mayhen_tuvi_hero_lead">
                    Lập lá số Tử Vi Đẩu Số tất định chuẩn quy tắc Nam Phái, khám phá 12 Thiên Mệnh Bài 3D gắn chặt dữ liệu thật từng cung và nhận bản luận giải AI 6 tầng nhân văn, minh bạch căn cứ.
                </p>

                <div class="mayhen_tuvi_status_bar">
                    @if($canUseFree)
                        <span>Gói hiện tại: <strong>{{ $currentPlanLabel }}</strong></span>
                        <span>•</span>
                        <span>Còn <strong>{{ $castQuota['daily_remaining'] }}/{{ $castQuota['daily_limit'] }}</strong> lượt lập lá số AI hôm nay ({{ $castQuota['cooldown_label'] }})</span>
                        <span>•</span>
                        <a href="{{ route('tuvi.profiles') }}" class="mayhen_tuvi_status_link">Hồ Sơ Thiên Mệnh &rarr;</a>
                    @else
                        <span>Miễn phí lập lá số &amp; luận giải AI 6 tầng khi đăng nhập gói <strong>Thành Viên Premium 0đ</strong></span>
                        <span>•</span>
                        <a href="{{ route('login', ['redirect' => route('tuvi.index')]) }}" class="mayhen_tuvi_status_link">Đăng nhập miễn phí &rarr;</a>
                    @endif
                </div>
            </div>

            {{-- FORM LẬP LÁ SỐ 1 MÀN HÌNH — NHÌN LÀ BIẾT DÙNG NGAY (1 LUỒNG DUY NHẤT) --}}
            <div class="mayhen_tuvi_wizard_card">
                <div class="mayhen_tuvi_flow_ribbon">
                    <div class="mayhen_tuvi_flow_steps_inline">
                        <span class="mayhen_tuvi_flow_chip"><span>1</span> Nhập ngày giờ sinh</span>
                        <span class="mayhen_tuvi_flow_arrow">&rarr;</span>
                        <span class="mayhen_tuvi_flow_chip"><span>2</span> Khai mở 12 Thiên Mệnh Bài xòe quạt</span>
                        <span class="mayhen_tuvi_flow_arrow">&rarr;</span>
                        <span class="mayhen_tuvi_flow_chip"><span>3</span> Đọc luận giải 6 tầng &amp; Thiên Bàn</span>
                    </div>
                    <span style="font-size: 11.5px; color: rgba(244, 226, 188, 0.8);">🔒 Lưu riêng tư mặc định</span>
                </div>

                <div class="mayhen_tuvi_wizard_body">
                    {{-- PHẦN A: THÔNG TIN CƠ BẢN & NGÀY SINH --}}
                    <div class="mayhen_tuvi_form_section_label">1. Thông tin bản mệnh &amp; ngày sinh</div>
                    <div class="mayhen_tuvi_form_grid_3">
                        <div class="mayhen_tuvi_field">
                            <label for="tuviNickname">Tên hiển thị / Biệt danh (tùy chọn)</label>
                            <input id="tuviNickname" type="text" class="mayhen_tuvi_input" maxlength="80" placeholder="Ví dụ: Minh Anh">
                        </div>
                        <div class="mayhen_tuvi_field">
                            <label for="tuviGender">Giới tính Âm Dương *</label>
                            <select id="tuviGender" class="mayhen_tuvi_select">
                                <option value="female" selected>Nữ mệnh</option>
                                <option value="male">Nam mệnh</option>
                            </select>
                        </div>
                        <div class="mayhen_tuvi_field">
                            <label for="tuviCalendarType">Lịch ngày sinh *</label>
                            <select id="tuviCalendarType" class="mayhen_tuvi_select">
                                <option value="solar" selected>Dương lịch (tự đổi sang Âm)</option>
                                <option value="lunar">Âm lịch (tự đổi sang Dương)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mayhen_tuvi_form_grid_3">
                        <div class="mayhen_tuvi_field">
                            <label for="tuviBirthDay">Ngày sinh *</label>
                            <input id="tuviBirthDay" type="number" class="mayhen_tuvi_input" min="1" max="31" value="15">
                        </div>
                        <div class="mayhen_tuvi_field">
                            <label for="tuviBirthMonth">Tháng sinh *</label>
                            <input id="tuviBirthMonth" type="number" class="mayhen_tuvi_input" min="1" max="12" value="8">
                        </div>
                        <div class="mayhen_tuvi_field">
                            <label for="tuviBirthYear">Năm sinh (1900–2100) *</label>
                            <input id="tuviBirthYear" type="number" class="mayhen_tuvi_input" min="1900" max="2100" value="1995">
                        </div>
                    </div>

                    <div id="tuviLeapMonthWrap" class="mayhen_tuvi_field mayhen_tuvi_is_hidden">
                        <label>
                            <input id="tuviIsLeapMonth" type="checkbox" value="1"> Sinh vào tháng nhuận Âm lịch
                        </label>
                    </div>

                    <div class="mayhen_tuvi_form_divider"></div>

                    {{-- PHẦN B: GIỜ SINH (4 NÚT 1 CHẠM) --}}
                    <div class="mayhen_tuvi_form_section_label">2. Giờ sinh của bạn (chọn đúng mức bạn nhớ)</div>
                    <input type="hidden" id="tuviBirthTimeMode" value="exact_time">
                    <div class="mayhen_tuvi_segmented" role="group" aria-label="Mức độ chắc chắn về giờ sinh">
                        <button type="button" class="mayhen_tuvi_seg_btn is-active" data-time-mode-btn="exact_time">Biết rõ giờ &amp; phút</button>
                        <button type="button" class="mayhen_tuvi_seg_btn" data-time-mode-btn="earthly_branch">Biết khung 2 tiếng</button>
                        <button type="button" class="mayhen_tuvi_seg_btn" data-time-mode-btn="time_window">Nhớ khoảng giờ</button>
                        <button type="button" class="mayhen_tuvi_seg_btn" data-time-mode-btn="unknown">Không biết giờ</button>
                    </div>

                    <div class="mayhen_tuvi_field" data-time-field="exact_time">
                        <div class="mayhen_tuvi_form_grid">
                            <div>
                                <label for="tuviBirthHour">Giờ sinh (0 – 23h)</label>
                                <input id="tuviBirthHour" type="number" class="mayhen_tuvi_input" min="0" max="23" value="6">
                            </div>
                            <div>
                                <label for="tuviBirthMinute">Phút sinh (0 – 59p)</label>
                                <input id="tuviBirthMinute" type="number" class="mayhen_tuvi_input" min="0" max="59" value="30">
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_tuvi_field mayhen_tuvi_is_hidden" data-time-field="earthly_branch">
                        <label for="tuviBirthBranch">Chọn Giờ Địa Chi</label>
                        <select id="tuviBirthBranch" class="mayhen_tuvi_select">
                            @foreach($hourBranches as $idx => $hb)
                                <option value="{{ $idx }}" @selected($idx === 3)>Giờ {{ $hb['name'] }} ({{ $hb['range'] }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mayhen_tuvi_field mayhen_tuvi_is_hidden" data-time-field="time_window">
                        <label for="tuviBirthWindow">Chọn khoảng giờ nhớ mang máng (hệ thống sẽ lập tất cả giờ ứng viên để đối chiếu)</label>
                        <select id="tuviBirthWindow" class="mayhen_tuvi_select">
                            @foreach($timeWindows as $wKey => $wMeta)
                                <option value="{{ $wKey }}">{{ $wMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mayhen_tuvi_field mayhen_tuvi_is_hidden" data-time-field="unknown">
                        <div class="mayhen_tuvi_alert_info">
                            <strong>Minh bạch dữ liệu:</strong> Mây Hẹn không bao giờ gán giờ giả 12:00 — hệ thống sẽ phân tích trung thực Bản Mệnh Ngũ Hành &amp; Năm Sinh cho bạn.
                        </div>
                    </div>

                    {{-- ĐỐI CHIẾU LỊCH ÂM DƯƠNG TỨC THỜI --}}
                    <div id="tuviCalendarPreviewBox" class="mayhen_tuvi_preview_banner">
                        Đang đối chiếu lịch Âm – Dương Việt Nam...
                    </div>

                    <div class="mayhen_tuvi_form_divider"></div>

                    {{-- PHẦN C: CHỌN CHỦ ĐỀ CHIÊM NGHIỆM --}}
                    <div class="mayhen_tuvi_form_section_label">3. Chủ đề bạn muốn luận giải sâu nhất</div>
                    <div class="mayhen_tuvi_pill_group">
                        @foreach($focusTopics as $tKey => $tLabel)
                            <button type="button"
                                    class="mayhen_tuvi_choice_pill {{ $tKey === 'toan_bo' ? 'is-active' : '' }}"
                                    data-tuvi-topic="{{ $tKey }}">
                                {{ $tLabel }}
                            </button>
                        @endforeach
                    </div>

                    {{-- TÙY CHỌN CHUYÊN SÂU (THU GỌN MẶC ĐỊNH ĐỂ NGƯỜI MỚI KHÔNG RỐI) --}}
                    <details class="mayhen_tuvi_advanced_box">
                        <summary>⚙ Tùy chọn lịch pháp chuyên sâu (mặc định chuẩn Nam Phái VN • Năm xem {{ $currentYear }})</summary>
                        <div class="mayhen_tuvi_form_grid">
                            <div class="mayhen_tuvi_field">
                                <label for="tuviTimezone">Múi giờ nơi sinh</label>
                                <select id="tuviTimezone" class="mayhen_tuvi_select">
                                    <option value="Asia/Ho_Chi_Minh" selected>Việt Nam (UTC+7)</option>
                                    <option value="Asia/Tokyo">Nhật Bản / Hàn Quốc (UTC+9)</option>
                                    <option value="Australia/Sydney">Úc — Sydney</option>
                                    <option value="Europe/Paris">Châu Âu — Paris</option>
                                    <option value="America/Los_Angeles">Hoa Kỳ — California</option>
                                    <option value="America/New_York">Hoa Kỳ — New York</option>
                                </select>
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviViewedYear">Năm xem Lưu Niên / Tiểu Hạn</label>
                                <input id="tuviViewedYear" type="number" class="mayhen_tuvi_input" min="1900" max="2100" value="{{ $currentYear }}">
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviLeapRule">Quy ước tháng nhuận</label>
                                <select id="tuviLeapRule" class="mayhen_tuvi_select">
                                    <option value="split_15" selected>Nam Phái chuẩn (Tách mốc ngày 15)</option>
                                    <option value="keep_month">Giữ nguyên tháng nhuận</option>
                                </select>
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviLateRatRule">Quy ước giờ Tý muộn (23:00–23:59)</label>
                                <select id="tuviLateRatRule" class="mayhen_tuvi_select">
                                    <option value="next_lunar_day" selected>Tính sang ngày Âm lịch hôm sau</option>
                                    <option value="keep_day">Giữ nguyên ngày Âm lịch hiện tại</option>
                                </select>
                            </div>
                        </div>
                    </details>

                    <div id="tuviWizardErrorBox" class="mayhen_tuvi_alert_error" role="alert"></div>

                    <div class="mayhen_tuvi_submit_bar">
                        <span style="font-size: 12.5px; color: #5C5044;">
                            Trường phái <strong>Nam Phái Việt Nam</strong> • Tên &amp; SĐT không gửi sang AI
                        </span>
                        <button type="button" id="tuviSubmitGenerateBtn" class="mayhen_tuvi_btn_primary">
                            ✦ Lập Lá Số &amp; Khai Mở 12 Thiên Mệnh Bài (Miễn Phí)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. XEM TRƯỚC THẬP NHỊ THIÊN MỆNH BÀI 3D — XÒE QUẠT TRÊN TAY (100% TRÊN 1 MÀN, KHÔNG THANH CUỘN) --}}
    <section class="mayhen_tuvi_deck_section">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_deck_head">
                <div>
                    <span class="mayhen_tuvi_kicker">✦ Không phải Tarot ngẫu nhiên • 12 Cung Thật Xòe Quạt Trên Tay</span>
                    <h2>Xem Trước Thập Nhị Thiên Mệnh Bài 3D</h2>
                    <p>
                        Toàn bộ 12 lá bài xòe trọn trên một khung hình — chạm vào bất kỳ lá nào để xem cách Chính tinh, Tứ Hóa và Đại Hạn của bạn hiện ra.
                        Đã mở thử: <strong data-tuvi-explored-count>0/12</strong> lá.
                    </p>
                </div>
                <div class="mayhen_tuvi_btn_row">
                    <div class="mayhen_tuvi_quality_bar" role="group" aria-label="Chất lượng hiệu ứng">
                        <button type="button" class="mayhen_tuvi_quality_btn" data-tuvi-quality="high">Đầy đủ 3D</button>
                        <button type="button" class="mayhen_tuvi_quality_btn" data-tuvi-quality="balanced">Cân bằng</button>
                        <button type="button" class="mayhen_tuvi_quality_btn" data-tuvi-quality="lightweight">Nhẹ máy</button>
                    </div>
                    <button type="button" class="mayhen_tuvi_btn_gold mayhen_tuvi_btn_sm" data-tuvi-deck-reveal-all>Lật mở 12 lá</button>
                    <button type="button" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm" data-tuvi-deck-reset>Úp gọn quạt</button>
                </div>
            </div>

            <div class="mayhen_tuvi_fan_stage" data-tuvi-fan-stage>
                <div class="mayhen_tuvi_fan_hand" data-tuvi-deck-carousel></div>
            </div>
            <p class="mayhen_tuvi_fan_hint">✦ Rê chuột để tách nhẹ từng lá bài trên quạt • Bấm vào bất kỳ lá nào để lật 3D mở giải mã cung ✦</p>
            <div class="mayhen_tuvi_card_drawer" data-tuvi-card-drawer aria-live="polite"></div>
        </div>
    </section>

    {{-- 3. LÁ SỐ GẦN ĐÂY CỦA THÀNH VIÊN (NẾU ĐÃ ĐĂNG NHẬP) --}}
    @if($recentCharts->isNotEmpty())
        <section class="mayhen_tuvi_section_ivory">
            <div class="mayhen_tuvi_container">
                <div class="mayhen_tuvi_section_head">
                    <h2>Hồ Sơ Thiên Mệnh Gần Đây Của Bạn</h2>
                    <p>Toàn bộ lá số được lưu ở chế độ riêng tư tuyệt đối, kèm lịch sử phiên bản khi bạn cập nhật giờ sinh.</p>
                </div>
                <div class="mayhen_tuvi_wiki_grid">
                    @foreach($recentCharts as $rc)
                        <div class="mayhen_tuvi_card">
                            <span class="mayhen_tuvi_badge">v{{ $rc->version_number }} • Lưu niên {{ $rc->viewed_year }}</span>
                            <h3>{{ $rc->profile?->nickname ?: 'Hồ Sơ Thiên Mệnh' }}</h3>
                            <p style="margin-bottom: 12px;">
                                {{ $rc->interpretation_json['headline'] ?? 'Lá số Tử Vi chuẩn Nam Phái Việt Nam.' }}
                            </p>
                            <a href="{{ route('tuvi.show', $rc->slug) }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Mở Lá Số &amp; 12 Thẻ 3D &rarr;</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. NHÂN DUYÊN SONG MỆNH & BÁCH KHOA TỬ VI CÔNG KHAI --}}
    <section class="mayhen_tuvi_section_light">
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_synastry_stage">
                <span class="mayhen_tuvi_kicker">✦ Dành Riêng Cho Cặp Đôi &amp; Hôn Nhân Việt Nam</span>
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: clamp(24px, 3.2vw, 34px); margin: 4px 0 8px; color: #FFF8EC;">
                    Nhân Duyên Song Mệnh — Nhật · Nguyệt · Duyên
                </h2>
                <p style="max-width: 680px; margin: 0 auto 18px; font-size: 14.5px; color: rgba(250, 245, 235, 0.82); line-height: 1.65;">
                    Đối chiếu hai lá số độc lập trên 10 chủ đề thực tế của tình yêu, giao tiếp, tài chính chung và hòa hợp hai bên gia đình.
                    <strong>Tuyệt đối không chấm điểm % hợp tuổi hay phán định chia ly.</strong>
                </p>

                <div class="mayhen_tuvi_synastry_duet">
                    <div class="mayhen_tuvi_duet_card mayhen_tuvi_duet_card--sun">
                        <span style="font-size: 11px; letter-spacing: 0.2em; color: #EBC984;">NHẬT MỆNH</span>
                        <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 54px; color: #F4DEAE; line-height: 1;">日</div>
                        <div style="font-size: 13px; color: #EADBC0;">Lá số người thứ nhất</div>
                    </div>
                    <div class="mayhen_tuvi_duet_medallion" aria-hidden="true">緣</div>
                    <div class="mayhen_tuvi_duet_card mayhen_tuvi_duet_card--moon">
                        <span style="font-size: 11px; letter-spacing: 0.2em; color: #EBC984;">NGUYỆT MỆNH</span>
                        <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 54px; color: #F4DEAE; line-height: 1;">月</div>
                        <div style="font-size: 13px; color: #EADBC0;">Lá số người đồng hành</div>
                    </div>
                </div>

                <a href="{{ route('tuvi.synastry.index') }}" class="mayhen_tuvi_btn_gold">
                    ✦ Mở Không Gian Nhân Duyên Song Mệnh &rarr;
                </a>
            </div>

            {{-- BÁCH KHOA TỬ VI --}}
            <div class="mayhen_tuvi_section_head">
                <h2>Bách Khoa Tử Vi Đẩu Số Chuẩn Nam Phái</h2>
                <p>Kho tri thức công khai, phân tách rõ cổ học nguyên bản với góc nhìn ứng dụng hiện đại để bạn tự tra cứu và kiểm chứng.</p>
            </div>

            <div class="mayhen_tuvi_wiki_grid">
                <a href="{{ route('tuvi.palaces') }}" class="mayhen_tuvi_card" style="text-decoration: none;">
                    <span class="mayhen_tuvi_badge">12 Cung Chức Năng</span>
                    <h3>Ý Nghĩa 12 Cung Tử Vi</h3>
                    <p>Từ Mệnh, Phu Thê, Quan Lộc, Tài Bạch tới Điền Trạch, Phúc Đức — kèm cách đọc Vô Chính Diệu và đính chính ngộ nhận.</p>
                </a>
                <a href="{{ route('tuvi.stars') }}" class="mayhen_tuvi_card" style="text-decoration: none;">
                    <span class="mayhen_tuvi_badge">14 Chính Tinh &amp; Phụ Tinh</span>
                    <h3>Từ Điển Tinh Diệu &amp; Tứ Hóa</h3>
                    <p>Tra cứu Tử Vi, Thiên Phủ, Thái Dương, Thái Âm, Thiên Lương... cùng quy ước Tứ Hóa Nam Phái minh bạch.</p>
                </a>
                <a href="{{ route('tuvi.patterns') }}" class="mayhen_tuvi_card" style="text-decoration: none;">
                    <span class="mayhen_tuvi_badge">Cách Cục Kiểm Chứng</span>
                    <h3>Hệ Thống Cách Cục Tử Vi</h3>
                    <p>Tử Phủ Vũ Tướng, Sát Phá Tham, Cơ Nguyệt Đồng Lương, Nhật Nguyệt Tịnh Minh... kèm điều kiện định lượng rõ ràng.</p>
                </a>
                <a href="{{ route('tuvi.zodiacs') }}" class="mayhen_tuvi_card" style="text-decoration: none;">
                    <span class="mayhen_tuvi_badge">12 Con Giáp</span>
                    <h3>Tổng Quan 12 Địa Chi</h3>
                    <p>Phân biệt rõ Tử Vi 12 Con Giáp theo năm sinh với Tử Vi Đẩu Số 12 cung đầy đủ theo ngày giờ sinh.</p>
                </a>
                <a href="{{ route('tuvi.yearly') }}" class="mayhen_tuvi_card" style="text-decoration: none;">
                    <span class="mayhen_tuvi_badge">Đại Hạn • Tiểu Hạn • Lưu Niên</span>
                    <h3>Cách Đọc Vận Trình Theo Năm</h3>
                    <p>Hiểu đúng sự phối hợp giữa Đại Hạn 10 năm, Tiểu Hạn và Lưu Niên năm {{ $currentYear }} mà không lo sợ hạn năm.</p>
                </a>
                <a href="{{ route('iching.index') }}" class="mayhen_tuvi_card" style="text-decoration: none;">
                    <span class="mayhen_tuvi_badge">Hệ Sinh Thái Mây Hẹn</span>
                    <h3>Mây Hẹn Dịch Tâm (Kinh Dịch)</h3>
                    <p>Tử Vi soi chiều dài bản mệnh; Kinh Dịch giúp quán chiếu một tình huống cụ thể ngay lúc bạn cần cân nhắc.</p>
                </a>
            </div>
        </div>
    </section>

    {{-- 5. CÂU HỎI THƯỜNG GẶP (SEO FAQ) --}}
    <section class="mayhen_tuvi_section_ivory">
        <div class="mayhen_tuvi_container" style="max-width: 860px;">
            <div class="mayhen_tuvi_section_head">
                <h2>Câu Hỏi Thường Gặp Về Mây Hẹn – Thiên Mệnh</h2>
                <p>Minh bạch trường phái, tách bạch Tử Vi với bói toán mê tín và bảo vệ tuyệt đối dữ liệu cá nhân.</p>
            </div>

            <details class="mayhen_tuvi_faq_item" open>
                <summary>Thập Nhị Thiên Mệnh Bài 3D khác gì với rút bài Tarot?</summary>
                <p>Tử Vi Đẩu Số lập lá số hoàn toàn tất định dựa trên ngày tháng năm giờ sinh và giới tính Âm Dương theo Nam Phái Việt Nam. 12 lá Thiên Mệnh Bài chỉ là hình thức trình bày trực quan giúp bạn lần lượt mở từng cung thật trên lá số của mình, tuyệt đối không xáo trộn ngẫu nhiên.</p>
            </details>
            <details class="mayhen_tuvi_faq_item">
                <summary>Mây Hẹn áp dụng trường phái an sao Tử Vi nào?</summary>
                <p>Mặc định hệ thống áp dụng <strong>Nam Phái Việt Nam ({{ $manifest['rule_version'] ?? 'vn_nam_phai_v1.0.0' }})</strong> tham chiếu <em>Tử Vi Đẩu Số Tân Biên</em>: quy ước Tứ Hóa can Canh (Thái Dương Lộc, Vũ Khúc Quyền, Thái Âm Khoa, Thiên Đồng Kỵ), can Nhâm (Thiên Lương Lộc, Tử Vi Quyền, Tả Phù Khoa, Vũ Khúc Kỵ), tách tháng nhuận theo mốc ngày 15 và tính giờ Tý muộn 23:00–23:59 sang ngày Âm lịch tiếp theo (có thể tùy chọn quy ước khác ở Bước 2).</p>
            </details>
            <details class="mayhen_tuvi_faq_item">
                <summary>Lập lá số và luận giải AI có giới hạn lượt không?</summary>
                <p>Thành viên đăng nhập miễn phí (gói <strong>Premium 0đ</strong>) có 5 lượt lập lá số &amp; luận giải AI mỗi ngày (giãn cách 5 phút/lần); gói <strong>Plus</strong> có 10 lượt/ngày (1 phút/lần); gói <strong>Pro</strong> luận liên tục tới 30 lượt/ngày. Mọi lá số đã lưu đều có thể mở xem lại không giới hạn trong Hồ Sơ Thiên Mệnh.</p>
            </details>
        </div>
    </section>

    {{-- MÀN CHUYỂN CẢNH NGHI THỨC KHAI MỞ THIÊN BÀN (1 LUỒNG LIỀN MẠCH) --}}
    <div id="mayhenTuViRitualOverlay" class="mayhen_tuvi_ritual_overlay" role="status" aria-live="polite">
        <div class="mayhen_tuvi_ritual_inner">
            <div class="mayhen_tuvi_ritual_ring" aria-hidden="true">
                <div class="mayhen_tuvi_ritual_fan_mini">
                    @for($ci = 0; $ci < 12; $ci++)
                        <span class="mayhen_tuvi_ritual_mini_card" style="--i: {{ $ci }};"></span>
                    @endfor
                </div>
            </div>
            <span class="mayhen_tuvi_kicker">✦ Mây Hẹn – Thiên Mệnh Đang Khai Mở Lá Số</span>
            <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: clamp(22px, 3vw, 30px); margin: 4px 0 8px; color: #FFF8EC;">
                Đang An 14 Chính Tinh &amp; Xòe Quạt 12 Thiên Mệnh Bài
            </h2>
            <div class="mayhen_tuvi_ritual_steps">
                <div class="mayhen_tuvi_ritual_step is-active" data-ritual-step="1">1. Quy đổi lịch Âm – Dương Việt Nam &amp; Ngũ Hành Cục</div>
                <div class="mayhen_tuvi_ritual_step" data-ritual-step="2">2. An 12 Cung, 14 Chính Tinh, Phụ Tinh &amp; Tứ Hóa Nam Phái</div>
                <div class="mayhen_tuvi_ritual_step" data-ritual-step="3">3. Kiểm chứng Tam Phương Tứ Chính, Cách Cục &amp; Đại Hạn 120 năm</div>
                <div class="mayhen_tuvi_ritual_step" data-ritual-step="4">4. Hoàn thiện bản luận giải AI 6 tầng sâu sắc, nhân văn...</div>
            </div>
        </div>
    </div>

    {{-- MODAL YÊU CẦU ĐĂNG NHẬP MIỄN PHÍ PREMIUM 0Đ --}}
    <div id="mayhenTuViLoginModal" class="mayhen_tuvi_modal_backdrop" role="dialog" aria-modal="true" aria-labelledby="mayhenTuViLoginTitle">
        <div class="mayhen_tuvi_modal_card">
            <span class="mayhen_tuvi_badge">Miễn Phí Thành Viên Mây Hẹn</span>
            <h3 id="mayhenTuViLoginTitle" style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 24px; margin: 0 0 10px; color: #85182A;">
                Đăng nhập để lưu Hồ Sơ Thiên Mệnh riêng tư
            </h3>
            <p style="font-size: 14px; line-height: 1.65; color: #4A4038; margin: 0 0 18px;">
                Tính năng Lập Lá Số Tử Vi &amp; Luận Giải AI 6 Tầng hoàn toàn <strong>miễn phí</strong> cho thành viên Mây Hẹn (tự động kích hoạt gói Thành Viên Premium 0đ) để bảo vệ riêng tư ngày giờ sinh và lưu lịch sử phiên bản lá số của riêng bạn.
            </p>
            <div class="mayhen_tuvi_btn_row">
                <a href="{{ route('login', ['redirect' => route('tuvi.index')]) }}" class="mayhen_tuvi_btn_primary">Đăng nhập ngay</a>
                <a href="{{ route('register') }}" class="mayhen_tuvi_btn_outline">Tạo tài khoản 0đ</a>
                <button type="button" id="mayhenTuViLoginClose" class="mayhen_tuvi_btn_outline">Để sau</button>
            </div>
        </div>
    </div>

    {{-- JSON DATA ISLANDS CHO BỘ THẺ DEMO 3D --}}
    <script id="mayhenTuViDemoChartJson" type="application/json">@json($demoChart, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViDemoRuleJson" type="application/json">@json($demoRuleAnalysis, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViPalacesJson" type="application/json">@json($palacesMeta, JSON_UNESCAPED_UNICODE)</script>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
