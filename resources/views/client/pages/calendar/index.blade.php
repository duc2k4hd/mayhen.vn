@extends('client.layouts.master')

@section('title', $pageMeta['title'] ?? 'Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay')
@section('meta_description', $pageMeta['meta_description'] ?? 'Xem Lịch Âm hôm nay và Lịch Vạn Niên online miễn phí chuẩn Việt Nam UTC+7 tại Mây Hẹn.')
@section('canonical', $pageMeta['canonical'] ?? route('calendar.index'))
@if (empty($isIndexable))
    @section('robots', 'noindex, nofollow')
@endif

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/calendar.css') }}?v={{ filemtime(public_path('assets/client/css/pages/calendar.css')) }}">
@endpush

@push('structured_data')
    @php
        $calSchemas = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebApplication',
                'name' => 'Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay — Mây Hẹn',
                'url' => $pageMeta['canonical'] ?? route('calendar.index'),
                'applicationCategory' => 'UtilitiesApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Lịch Âm Hôm Nay', 'item' => route('calendar.index')],
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Lịch âm Việt Nam tại Mây Hẹn tính theo múi giờ và quy tắc nào?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Mây Hẹn áp dụng chuẩn Lịch Việt Nam hiện hành theo múi giờ UTC+7 (Asia/Ho_Chi_Minh), tính trực tiếp thời điểm Sóc (mùng 1) và Trung Khí (tháng nhuận) theo thuật toán thiên văn Hồ Ngọc Đức / Jean Meeus trong phạm vi 1900–2100.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Vì sao lịch âm Việt Nam đôi khi khác lịch Trung Quốc 1 ngày hoặc khác tháng nhuận?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Lịch Việt Nam quy chiếu theo múi giờ UTC+7 (Hà Nội) còn lịch Trung Quốc quy chiếu theo UTC+8 (Bắc Kinh). Khi thời điểm Sóc hoặc Trung Khí rơi vào khoảng 23:00–23:59 giờ Việt Nam (00:00–00:59 giờ Bắc Kinh), ngày mùng 1 hoặc tháng nhuận của hai nước có thể lệch nhau.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Ngày 15 Âm lịch có luôn trùng chính xác với thời điểm Trăng Tròn (Full Moon) không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Không nhất thiết. Do quỹ đạo Mặt Trăng hình elip, thời điểm Trăng Tròn thiên văn (góc pha 180°) thường rơi vào ngày 15, 16 hoặc đôi khi 14, 17 Âm lịch. Mây Hẹn hiển thị tách biệt cả ngày Âm lịch lẫn thời điểm Trăng Tròn thiên văn.',
                        ],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($calSchemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
@php
    $d = $dayBundle['day'];
    $alm = $dayBundle['almanac'];
    $moon = $dayBundle['moon_phase'];
    $sun = $dayBundle['sun_times'];
    $term = $dayBundle['solar_term'];
    $hols = $dayBundle['holidays'] ?? [];
    $activeToolTab = match ($focusSection ?? null) {
        'anniversary' => 'anniversary',
        default => 'converter',
    };
@endphp

<div class="mayhen_calendar_page">
    <!-- 1. Hero: Tờ Lịch Bloc 3D + Mặt Trăng 3D & Mặt Trời -->
    <section class="mayhen_calendar_hero">
        <div class="mayhen_calendar_container">
            <div class="mayhen_calendar_hero_top">
                <div>
                    <div class="mayhen_calendar_eyebrow">✦ MÂY HẸN – VẠN NIÊN LỊCH · CHUẨN VIỆT NAM UTC+7</div>
                    <h1 class="mayhen_calendar_h1">{{ $pageMeta['h1'] ?? 'Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay' }}</h1>
                </div>
                <div class="mayhen_calendar_quick_links">
                    <a href="{{ route('calendar.converter') }}" class="mayhen_calendar_pill_link">⇄ Đổi Ngày Âm Dương</a>
                    <a href="{{ route('calendar.year', ['year' => $d['solar']['year']]) }}" class="mayhen_calendar_pill_link">📅 Lịch Năm {{ $d['solar']['year'] }}</a>
                    <a href="{{ route('calendar.solar_terms') }}" class="mayhen_calendar_pill_link">☀ 24 Tiết Khí</a>
                    <a href="{{ route('calendar.anniversary') }}" class="mayhen_calendar_pill_link">🕯 Tính Ngày Giỗ</a>
                    <a href="{{ route('calendar.my_events') }}" class="mayhen_calendar_pill_link">🏡 Lịch Gia Đình</a>
                </div>
            </div>

            <div class="mayhen_calendar_hero_grid">
                <!-- Left: 3D Bloc Tear-Off Card -->
                <div class="mayhen_calendar_bloc_stage">
                    <div class="mayhen_calendar_bloc_card" id="calBlocCard">
                        <div class="mayhen_calendar_bloc_header">
                            <span class="mayhen_calendar_bloc_month_label" id="calBlocMonthLabel">
                                THÁNG {{ str_pad((string) $d['solar']['month'], 2, '0', STR_PAD_LEFT) }} · NĂM {{ $d['solar']['year'] }}
                            </span>
                            <div class="mayhen_calendar_bloc_nav">
                                <button type="button" id="calPrevDayBtn" class="mayhen_calendar_bloc_btn" aria-label="Ngày trước">‹ Hôm qua</button>
                                <button type="button" id="calTodayBtn" class="mayhen_calendar_bloc_btn">Hôm nay</button>
                                <button type="button" id="calNextDayBtn" class="mayhen_calendar_bloc_btn" aria-label="Ngày sau">Ngày mai ›</button>
                            </div>
                        </div>

                        <div class="mayhen_calendar_bloc_body">
                            <div class="mayhen_calendar_solar_focus">
                                <div class="mayhen_calendar_weekday" id="calWeekday">{{ $d['solar']['weekday_name'] }}</div>
                                <div class="mayhen_calendar_big_day" id="calBigDay">{{ $d['solar']['day'] }}</div>
                                <div class="mayhen_calendar_solar_full" id="calSolarFull">Dương lịch: {{ $d['solar']['label'] }}</div>
                            </div>

                            <div class="mayhen_calendar_lunar_panel">
                                <div class="mayhen_calendar_lunar_badge_row">
                                    <span id="calQualityTag" class="mayhen_calendar_tag mayhen_calendar_tag--{{ $alm['day_quality']['type'] }}">
                                        ✦ {{ $alm['day_quality']['label'] }} ({{ $alm['day_quality']['god_name'] }})
                                    </span>
                                    @if ($d['lunar']['is_leap_month'])
                                        <span class="mayhen_calendar_tag mayhen_calendar_tag--leap">Tháng Nhuận</span>
                                    @endif
                                </div>

                                <div class="mayhen_calendar_lunar_big" id="calLunarBig">
                                    {{ str_pad((string) $d['lunar']['day'], 2, '0', STR_PAD_LEFT) }}/{{ str_pad((string) $d['lunar']['month'], 2, '0', STR_PAD_LEFT) }}{{ $d['lunar']['is_leap_month'] ? ' (Nhuận)' : '' }} Âm Lịch
                                </div>
                                <div style="font-size: 13px; color: #57534E;" id="calLunarSub">
                                    Năm {{ $d['can_chi']['year']['label'] }} ({{ $d['can_chi']['year']['zodiac'] }}) · {{ $d['lunar']['month_size_label'] }}
                                </div>

                                <div class="mayhen_calendar_canchi_grid">
                                    <div class="mayhen_calendar_canchi_item">
                                        <span>Can Chi Ngày</span>
                                        <strong id="calCanChiDay">{{ $d['can_chi']['day']['label'] }}</strong>
                                    </div>
                                    <div class="mayhen_calendar_canchi_item">
                                        <span>Nạp Âm Ngày</span>
                                        <strong id="calNapAmDay">{{ $d['can_chi']['day']['nap_am']['name'] }} ({{ $d['can_chi']['day']['nap_am']['element'] }})</strong>
                                    </div>
                                    <div class="mayhen_calendar_canchi_item">
                                        <span>Can Chi Tháng</span>
                                        <strong id="calCanChiMonth">{{ $d['can_chi']['month']['label'] }}</strong>
                                    </div>
                                    <div class="mayhen_calendar_canchi_item">
                                        <span>Tiết Khí Hiện Hành</span>
                                        <strong id="calSolarTermName">{{ $term['current']['name'] }} ({{ $term['sun_longitude_deg'] }}°)</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_calendar_bloc_footer">
                            <span id="calBlocFooterTerm">Tiết {{ $term['current']['name'] }} · Còn {{ $term['days_until_next'] }} ngày tới {{ $term['next']['name'] }}</span>
                            @if (! empty($hols))
                                <strong style="color: #85182A;">🎉 {{ collect($hols)->pluck('name')->implode(' · ') }}</strong>
                            @else
                                <span style="color: #57534E;">Chuẩn lịch Việt Nam · Múi giờ UTC+7</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: 3D Moon Phase & Solar Elevation Card -->
                <div class="mayhen_calendar_night_card">
                    <div class="mayhen_calendar_moon_row">
                        <canvas id="calMoonCanvas" class="mayhen_calendar_moon_canvas" width="272" height="272" aria-label="Mô hình pha Mặt Trăng thiên văn"></canvas>
                        <div>
                            <div class="mayhen_calendar_night_kicker">✦ PHA MẶT TRĂNG THIÊN VĂN</div>
                            <h2 class="mayhen_calendar_moon_name" id="calMoonName">{{ $moon['phase_name'] }}</h2>
                            <p class="mayhen_calendar_night_meta" id="calMoonMeta">
                                Chiếu sáng {{ $moon['illumination_pct'] }}% · Tuổi Trăng {{ $moon['age_days'] }} ngày · {{ $moon['trend_label'] }}
                            </p>
                            <p class="mayhen_calendar_night_meta" style="margin-top: 6px; font-size: 12px; color: #E9D2A0;" id="calMoonNext">
                                Trăng tròn kế tiếp: {{ $moon['next_full_moon_vn'] }} · Trăng non: {{ $moon['next_new_moon_vn'] }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span class="mayhen_calendar_night_kicker" style="margin: 0;">MẶT TRỜI MỌC &amp; LẶN THEO THÀNH PHỐ</span>
                            <select id="calCitySelect" class="mayhen_calendar_city_select" aria-label="Chọn thành phố Việt Nam">
                                @foreach ($cities as $cKey => $cMeta)
                                    <option value="{{ $cKey }}" @selected($sun['city_key'] === $cKey)>{{ $cMeta['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mayhen_calendar_sun_bar">
                            <div class="mayhen_calendar_sun_cell">
                                <span>Mặt Trời mọc</span>
                                <strong id="calSunRise">{{ $sun['sunrise'] }}</strong>
                            </div>
                            <div class="mayhen_calendar_sun_cell">
                                <span>Chính Ngọ</span>
                                <strong id="calSunNoon">{{ $sun['solar_noon'] }}</strong>
                            </div>
                            <div class="mayhen_calendar_sun_cell">
                                <span>Mặt Trời lặn</span>
                                <strong id="calSunSet">{{ $sun['sunset'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mayhen_calendar_container">
        <!-- 2. Lịch Tháng Tương Tác & Đồng Hồ 12 Thời Thần -->
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_two_col">
                <div class="mayhen_calendar_card">
                    <div class="mayhen_calendar_month_head">
                        <h2 id="calMonthLabel" style="margin: 0;">{{ $monthGrid['label'] }}</h2>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button type="button" id="calPrevMonthBtn" class="mayhen_calendar_btn_outline">‹ Tháng trước</button>
                            <button type="button" id="calNextMonthBtn" class="mayhen_calendar_btn_outline">Tháng sau ›</button>
                            <a href="{{ route('calendar.year', ['year' => $d['solar']['year']]) }}" class="mayhen_calendar_btn_outline">Xem cả năm</a>
                        </div>
                    </div>

                    <div class="mayhen_calendar_month_grid_head">
                        <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div style="color:#85182A;">T7</div><div style="color:#85182A;">CN</div>
                    </div>
                    <div class="mayhen_calendar_month_grid" id="calMonthGrid">
                        @foreach ($monthGrid['cells'] as $cell)
                            @if ($cell === null)
                                <div class="mayhen_calendar_cell is-empty"></div>
                            @else
                                <button type="button"
                                        class="mayhen_calendar_cell {{ $cell['is_weekend'] ? 'is-weekend' : '' }} {{ $cell['iso'] === $todayIso ? 'is-today' : '' }} {{ $cell['iso'] === $d['solar']['iso'] ? 'is-selected' : '' }}"
                                        data-iso="{{ $cell['iso'] }}">
                                    @if ($cell['is_hoang_dao'])
                                        <span class="mayhen_calendar_cell_dot" title="Ngày Hoàng Đạo"></span>
                                    @endif
                                    <span class="mayhen_calendar_cell_solar">{{ $cell['solar_day'] }}</span>
                                    <span class="mayhen_calendar_cell_lunar">{{ $cell['lunar_short'] }}</span>
                                    @if (! empty($cell['holiday_name']))
                                        <span class="mayhen_calendar_cell_hol">{{ $cell['holiday_name'] }}</span>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                    </div>
                    <div style="margin-top: 12px; font-size: 12px; color: #57534E; display: flex; gap: 16px; flex-wrap: wrap;">
                        <span><strong style="color:#CFA860;">●</strong> Ngày Hoàng Đạo (Ngọc Hạp Thông Thư)</span>
                        <span>Chạm vào bất kỳ ngày nào để lật tờ lịch 3D</span>
                    </div>
                </div>

                <!-- Đồng Hồ 12 Thời Thần & Giờ Hoàng Đạo -->
                <div class="mayhen_calendar_card">
                    <div class="mayhen_calendar_eyebrow">ĐỒNG HỒ 12 THỜI THẦN</div>
                    <h2>Giờ Hoàng Đạo &amp; Hắc Đạo</h2>
                    <div class="mayhen_calendar_clock_wrap">
                        <div id="calClockWrap"></div>
                        <div class="mayhen_calendar_hours_pills" id="calHoursPills">
                            @foreach ($alm['hours'] as $h)
                                <div class="mayhen_calendar_hour_pill {{ $h['is_hoang_dao'] ? 'is-hoang-dao' : '' }}">
                                    <strong>{{ $h['branch'] }}</strong> ({{ $h['range'] }})<br>
                                    <span style="font-size: 10px; color: #57534E;">{{ $h['god_name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Lịch Vạn Niên Dân Gian (12 Trực, 28 Tú, Lục Diệu, Hướng Xuất Hành & Giải Thích) -->
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_card">
                <div class="mayhen_calendar_eyebrow">THÔNG TIN LỊCH VẠN NIÊN TRUYỀN THỐNG (CÓ NGUỒN MINH BẠCH)</div>
                <h2>Thập Nhị Kiến Trừ, Nhị Thập Bát Tú &amp; Xuất Hành</h2>

                <div class="mayhen_calendar_almanac_grid">
                    <div class="mayhen_calendar_almanac_box">
                        <span>Thập Nhị Kiến Trừ</span>
                        <strong id="calAlmTruc">Trực {{ $alm['truc']['name'] }}</strong>
                        <p id="calAlmTrucDesc">{{ $alm['truc']['desc'] }}</p>
                    </div>
                    <div class="mayhen_calendar_almanac_box">
                        <span>Nhị Thập Bát Tú</span>
                        <strong id="calAlmMansion">Tú {{ $alm['mansion']['name'] }} ({{ $alm['mansion']['element'] }})</strong>
                        <p id="calAlmMansionDesc">{{ $alm['mansion']['nature'] }}</p>
                    </div>
                    <div class="mayhen_calendar_almanac_box">
                        <span>Lục Diệu Dân Gian</span>
                        <strong id="calAlmLucDieu">{{ $alm['luc_dieu']['name'] }}</strong>
                        <p id="calAlmLucDieuDesc">{{ $alm['luc_dieu']['desc'] }}</p>
                    </div>
                    <div class="mayhen_calendar_almanac_box">
                        <span>Xuất Hành &amp; Tuổi Xung</span>
                        <strong id="calAlmDirections" style="font-size: 15px;">Hỷ Thần: {{ $alm['directions']['hy_than'] }} · Tài Thần: {{ $alm['directions']['tai_than'] }}</strong>
                        <p id="calAlmClash">Tuổi xung theo Chi ngày: {{ $alm['clash_branch'] }}</p>
                    </div>
                </div>

                <div class="mayhen_calendar_result_box" style="margin-top: 16px;">
                    <div style="font-weight: 700; color: #1E4D3B; margin-bottom: 4px;">Tóm tắt ngày từ Engine Lịch Pháp Mây Hẹn</div>
                    <div id="calExplainSummary">{{ $dailyExplain['summary'] ?? '' }}</div>
                    <div id="calExplainTraditional" style="font-size: 13.5px; color: #57534E; margin-top: 6px;">{{ $dailyExplain['traditional_note'] ?? '' }}</div>
                </div>
            </div>
        </section>

        <!-- 4. Bộ Công Cụ 4-trong-1: Đổi Ngày, Tìm Ngày Cưới/Khai Trương, Tính Ngày Giỗ, Hỏi AI -->
        <section class="mayhen_calendar_section" id="calToolsSection">
            <div class="mayhen_calendar_card">
                <div class="mayhen_calendar_eyebrow">TIỆN ÍCH VẠN NIÊN LỊCH MIỄN PHÍ</div>
                <h2>Công Cụ Tra Cứu &amp; Lập Kế Hoạch Gia Đình</h2>

                <div class="mayhen_calendar_tabs" role="tablist">
                    <button type="button" class="mayhen_calendar_tab_btn {{ $activeToolTab === 'converter' ? 'is-active' : '' }}" data-cal-tab="converter">⇄ Đổi Ngày Âm ↔ Dương</button>
                    <button type="button" class="mayhen_calendar_tab_btn" data-cal-tab="finder">💍 Tìm Ngày Cưới / Khai Trương</button>
                    <button type="button" class="mayhen_calendar_tab_btn {{ $activeToolTab === 'anniversary' ? 'is-active' : '' }}" data-cal-tab="anniversary">🕯 Tính Ngày Giỗ Âm Lịch</button>
                    <button type="button" class="mayhen_calendar_tab_btn" data-cal-tab="ai">✦ Hỏi Trợ Lý Vạn Niên Lịch</button>
                </div>

                <!-- Tab 1: Converter -->
                <div class="mayhen_calendar_tab_panel {{ $activeToolTab === 'converter' ? 'is-active' : '' }}" data-cal-panel="converter">
                    <form id="calConvertForm">
                        <div class="mayhen_calendar_form_row">
                            <div class="mayhen_calendar_field">
                                <label>Chiều chuyển đổi</label>
                                <select name="direction" class="mayhen_calendar_select">
                                    <option value="solar_to_lunar">Dương lịch → Âm lịch</option>
                                    <option value="lunar_to_solar">Âm lịch → Dương lịch</option>
                                </select>
                            </div>
                            <div class="mayhen_calendar_field" style="max-width: 100px;">
                                <label>Ngày</label>
                                <input type="number" name="day" min="1" max="31" value="{{ $d['solar']['day'] }}" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field" style="max-width: 100px;">
                                <label>Tháng</label>
                                <input type="number" name="month" min="1" max="12" value="{{ $d['solar']['month'] }}" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field" style="max-width: 120px;">
                                <label>Năm (1900–2100)</label>
                                <input type="number" name="year" min="1900" max="2100" value="{{ $d['solar']['year'] }}" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Tháng nhuận (khi đổi Âm→Dương)</label>
                                <select name="is_leap_month" class="mayhen_calendar_select">
                                    <option value="0">Tháng thường</option>
                                    <option value="1">Tháng nhuận</option>
                                </select>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Chế độ lịch</label>
                                <select name="mode" class="mayhen_calendar_select">
                                    <option value="vn_modern_utc7">Lịch VN hiện hành (UTC+7)</option>
                                    <option value="vn_historical">Lịch VN lịch sử (1960–1975)</option>
                                </select>
                            </div>
                            <button type="submit" class="mayhen_calendar_btn_primary">Quy Đổi Ngay</button>
                        </div>
                    </form>
                    <div id="calConvertResult" class="mayhen_calendar_result_box" style="display: none;"></div>
                </div>

                <!-- Tab 2: Activity Date Finder -->
                <div class="mayhen_calendar_tab_panel" data-cal-panel="finder">
                    <form id="calFindDatesForm">
                        <div class="mayhen_calendar_form_row">
                            <div class="mayhen_calendar_field">
                                <label>Mục đích xem ngày</label>
                                <select name="activity" class="mayhen_calendar_select">
                                    @foreach ($activities as $aKey => $aMeta)
                                        <option value="{{ $aKey }}">{{ $aMeta['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Từ ngày</label>
                                <input type="date" name="from_date" value="{{ $todayIso }}" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Đến ngày (tối đa 3 tháng)</label>
                                <input type="date" name="to_date" value="{{ \Carbon\Carbon::parse($todayIso)->addDays(45)->format('Y-m-d') }}" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Ưu tiên thực tế</label>
                                <select name="weekends_only" class="mayhen_calendar_select">
                                    <option value="0">Tất cả các ngày trong tuần</option>
                                    <option value="1">Chỉ Thứ 7 &amp; Chủ Nhật (Cuối tuần)</option>
                                </select>
                            </div>
                            <button type="submit" class="mayhen_calendar_btn_primary">Tìm Ngày Phù Hợp</button>
                        </div>
                    </form>
                    <div id="calFindDatesResult" class="mayhen_calendar_result_box" style="display: none;"></div>
                </div>

                <!-- Tab 3: Lunar Anniversary / Ngày Giỗ -->
                <div class="mayhen_calendar_tab_panel {{ $activeToolTab === 'anniversary' ? 'is-active' : '' }}" data-cal-panel="anniversary">
                    <form id="calAnniversaryForm">
                        <div class="mayhen_calendar_form_row">
                            <div class="mayhen_calendar_field" style="max-width: 110px;">
                                <label>Ngày Âm lịch</label>
                                <input type="number" name="lunar_day" min="1" max="30" value="15" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field" style="max-width: 110px;">
                                <label>Tháng Âm lịch</label>
                                <input type="number" name="lunar_month" min="1" max="12" value="7" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Nếu gặp tháng thiếu (29 ngày)</label>
                                <select name="short_month_rule" class="mayhen_calendar_select">
                                    <option value="last_day_of_month">Chuyển về ngày cuối tháng (29 Âm)</option>
                                    <option value="skip">Bỏ qua năm tháng thiếu</option>
                                </select>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Nếu gặp năm tháng nhuận</label>
                                <select name="leap_month_rule" class="mayhen_calendar_select">
                                    <option value="regular_only">Chỉ tính tháng thường</option>
                                    <option value="leap_if_exists">Tính vào tháng nhuận nếu có</option>
                                    <option value="both">Hiển thị cả 2 tháng</option>
                                </select>
                            </div>
                            <button type="submit" class="mayhen_calendar_btn_primary">Tính 6 Năm Tới</button>
                        </div>
                    </form>
                    <div id="calAnniversaryResult" class="mayhen_calendar_result_box" style="display: none;"></div>
                </div>

                <!-- Tab 4: Grounded AI Q&A -->
                <div class="mayhen_calendar_tab_panel" data-cal-panel="ai">
                    <form id="calAiForm">
                        <div class="mayhen_calendar_form_row">
                            <div class="mayhen_calendar_field">
                                <label>Đặt câu hỏi về ngày đang xem (AI căn cứ 100% dữ liệu Engine)</label>
                                <input type="text" name="question" maxlength="360" class="mayhen_calendar_input" placeholder="Ví dụ: Ngày này có phù hợp tổ chức ăn hỏi không và nên chọn khung giờ nào?" required>
                            </div>
                            <button type="submit" class="mayhen_calendar_btn_primary">Hỏi Trợ Lý</button>
                        </div>
                    </form>
                    <div id="calAiResult" class="mayhen_calendar_result_box" style="display: none;"></div>
                </div>
            </div>
        </section>

        <!-- 5. Đếm Ngược Ngày Lễ Sắp Tới -->
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_eyebrow">ĐẾM NGƯỢC SỰ KIỆN TRUYỀN THỐNG</div>
            <h2 style="font-family: var(--mayhen-calendar-serif); margin: 0 0 16px;">Ngày lễ Âm lịch sắp tới</h2>
            <div class="mayhen_calendar_countdown_grid">
                @foreach ($countdowns as $cd)
                    <div class="mayhen_calendar_countdown_card">
                        <div style="font-size: 12px; color: #57534E; margin-bottom: 4px;">{{ $cd['target_lunar'] }}</div>
                        <div class="mayhen_calendar_countdown_days">{{ $cd['days_remaining'] }}</div>
                        <div style="font-size: 12px; color: #85182A; font-weight: 600; margin-bottom: 6px;">ngày lịch nữa</div>
                        <strong style="font-size: 14px; display: block;">{{ $cd['name'] }}</strong>
                        <span style="font-size: 12px; color: #57534E;">Dương lịch: {{ $cd['target_solar'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 6. Vòng 24 Tiết Khí Trong Năm -->
        <section class="mayhen_calendar_section" id="calSolarTermsSection">
            <div class="mayhen_calendar_card">
                <div class="mayhen_calendar_eyebrow">THIÊN VĂN HỌC · 24 TIẾT KHÍ NĂM {{ $d['solar']['year'] }}</div>
                <h2>Thời điểm giao 24 Tiết Khí chính xác theo giờ Việt Nam (UTC+7)</h2>
                <p style="font-size: 14px; color: #57534E; margin: 0 0 16px;">
                    24 tiết khí chia vòng hoàng đạo 360° thành 24 mốc cách nhau đúng 15°. Thời điểm giao tiết thay đổi từng năm theo chuyển động quỹ đạo Trái Đất (không cố định 00:00 hay ngày cố định).
                </p>
                <div class="mayhen_calendar_terms_grid">
                    @foreach ($yearTerms as $yt)
                        <div class="mayhen_calendar_term_item {{ $yt['index'] === $term['current']['index'] ? 'is-current' : '' }}">
                            <div style="font-size: 10.5px; color: #85182A; font-weight: 700;">{{ $yt['season'] }} · {{ $yt['deg'] }}°</div>
                            <strong style="display: block; font-size: 13.5px; margin: 2px 0;">{{ $yt['name'] }}</strong>
                            <span style="color: #57534E;">{{ $yt['datetime_vn'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- 7. Cầu Nối Hệ Sinh Thái Mây Hẹn -->
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_card" style="background: #F3EBE0; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                <div>
                    <div class="mayhen_calendar_eyebrow">MÂY HẸN – TỪ NHÂN DUYÊN ĐẾN TRĂM NĂM</div>
                    <h2 style="margin-bottom: 6px;">Đã chọn được ngày lành cho cưới hỏi hoặc sự kiện gia đình?</h2>
                    <p style="font-size: 14px; color: #57534E; margin: 0;">
                        Lưu ngay vào Lịch Gia Đình âm lịch, đối chiếu lá số Tử Vi hoặc khởi tạo Thiệp Cưới Online miễn phí tại Mây Hẹn.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('calendar.my_events') }}" class="mayhen_calendar_btn_primary">🏡 Mở Lịch Gia Đình</a>
                    <a href="{{ route('templates.index') }}" class="mayhen_calendar_btn_outline">💌 Tạo Thiệp Cưới Online</a>
                    <a href="{{ route('tuvi.index') }}" class="mayhen_calendar_btn_outline">✦ Lập Lá Số Tử Vi</a>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="application/json" id="mayhenCalendarConfig">{!! json_encode([
    'todayIso' => $todayIso,
    'initialBundle' => $dayBundle,
    'monthGrid' => $monthGrid,
    'endpoints' => [
        'apiDay' => route('calendar.api.day'),
        'apiMonth' => route('calendar.api.month'),
        'apiConvert' => route('calendar.api.convert'),
        'apiFindDates' => route('calendar.api.find_dates'),
        'apiAnniversary' => route('calendar.api.anniversary'),
        'apiAskAi' => route('calendar.api.ask_ai'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/calendar.js') }}"></script>
@endpush
