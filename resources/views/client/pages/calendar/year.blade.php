@extends('client.layouts.master')

@section('title', 'Lịch Âm Năm ' . $year . ' (' . $tetDay['can_chi']['year']['label'] . ') — Lịch Vạn Niên 12 Tháng')
@section('meta_description', 'Tra cứu trọn bộ Lịch Âm năm ' . $year . ' (' . $tetDay['can_chi']['year']['label'] . '): Mùng 1 Tết Dương lịch ' . $tetDay['solar']['label'] . ', danh sách ' . count($lunarMonths) . ' tháng Âm lịch' . ($leapMonth ? ' (có tháng ' . $leapMonth . ' nhuận)' : '') . ' và 24 tiết khí.')
@section('canonical', route('calendar.year', ['year' => $year]))
@if (empty($isIndexable))
    @section('robots', 'noindex, nofollow')
@endif

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/calendar.css') }}?v={{ filemtime(public_path('assets/client/css/pages/calendar.css')) }}">
@endpush

@section('content')
<div class="mayhen_calendar_page">
    <section class="mayhen_calendar_hero">
        <div class="mayhen_calendar_container">
            <div class="mayhen_calendar_hero_top">
                <div>
                    <div class="mayhen_calendar_eyebrow">✦ LỊCH VẠN NIÊN THEO NĂM · CHUẨN VIỆT NAM UTC+7</div>
                    <h1 class="mayhen_calendar_h1">Lịch Âm Năm {{ $year }} — Năm {{ $tetDay['can_chi']['year']['label'] }} ({{ $tetDay['can_chi']['year']['zodiac'] }})</h1>
                    <p style="font-size: 14.5px; color: #57534E; margin: 8px 0 0;">
                        Mùng 1 Tết Nguyên Đán {{ $year }} rơi vào <strong>{{ $tetDay['solar']['weekday_name'] }}, {{ $tetDay['solar']['label'] }}</strong> ·
                        Năm Âm lịch có <strong>{{ count($lunarMonths) }} tháng</strong>{{ $leapMonth ? ' (Nhuận tháng ' . $leapMonth . ')' : ' (Không có tháng nhuận)' }}.
                    </p>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @if ($year > 1900)
                        <a href="{{ route('calendar.year', ['year' => $year - 1]) }}" class="mayhen_calendar_btn_outline">← Năm {{ $year - 1 }}</a>
                    @endif
                    <a href="{{ route('calendar.index') }}" class="mayhen_calendar_btn_primary">Lịch Hôm Nay</a>
                    @if ($year < 2100)
                        <a href="{{ route('calendar.year', ['year' => $year + 1]) }}" class="mayhen_calendar_btn_outline">Năm {{ $year + 1 }} →</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="mayhen_calendar_container">
        <!-- Bảng 12 hoặc 13 tháng Âm lịch (rõ tháng nhuận & độ dài 29/30) -->
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_card">
                <div class="mayhen_calendar_eyebrow">CẤU TRÚC NĂM ÂM LỊCH {{ $year }}</div>
                <h2>Danh sách {{ count($lunarMonths) }} tháng Âm lịch năm {{ $tetDay['can_chi']['year']['label'] }}</h2>
                <div class="mayhen_calendar_almanac_grid">
                    @foreach ($lunarMonths as $lm)
                        <div class="mayhen_calendar_almanac_box" style="{{ $lm['is_leap'] ? 'border-color:#85182A;background:rgba(133,24,42,0.06);' : '' }}">
                            <span>{{ $lm['is_leap'] ? '✦ THÁNG NHUẬN' : 'THÁNG ÂM LỊCH' }}</span>
                            <strong>{{ $lm['label'] }} ({{ $lm['days'] }} ngày)</strong>
                            <p>{{ $lm['solar_start'] }} → {{ $lm['solar_end'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Lưới 12 Tháng Dương lịch -->
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_year_grid">
                @foreach ($monthsGrid as $mNum => $mg)
                    <div class="mayhen_calendar_card" style="padding: 16px;">
                        <h3 style="margin-bottom: 10px; font-size: 18px;">{{ $mg['label'] }}</h3>
                        <div class="mayhen_calendar_month_grid_head" style="font-size: 11px;">
                            <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div>
                        </div>
                        <div class="mayhen_calendar_month_grid" style="gap: 4px;">
                            @foreach ($mg['cells'] as $cell)
                                @if ($cell === null)
                                    <div class="mayhen_calendar_cell is-empty" style="min-height: 40px;"></div>
                                @else
                                    <a href="{{ route('calendar.day', ['date' => $cell['iso']]) }}"
                                       class="mayhen_calendar_cell {{ $cell['is_weekend'] ? 'is-weekend' : '' }}"
                                       style="min-height: 40px; padding: 4px 2px; text-decoration: none;">
                                        <span class="mayhen_calendar_cell_solar" style="font-size: 13px;">{{ $cell['solar_day'] }}</span>
                                        <span class="mayhen_calendar_cell_lunar" style="font-size: 9.5px;">{{ $cell['lunar_short'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
