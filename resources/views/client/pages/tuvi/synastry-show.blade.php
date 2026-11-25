@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $basis = is_array($report->comparison_payload_json) ? $report->comparison_payload_json : [];
    $interp = is_array($report->interpretation_json) ? $report->interpretation_json : [];
    $pA = $basis['person_a'] ?? ['nickname' => 'Người 1', 'nap_am' => '', 'element' => '', 'menh_stars' => '', 'phu_the_stars' => ''];
    $pB = $basis['person_b'] ?? ['nickname' => 'Người 2', 'nap_am' => '', 'element' => '', 'menh_stars' => '', 'phu_the_stars' => ''];
    $dyn = $basis['element_dynamic'] ?? ['label' => 'Tương trợ', 'summary' => ''];
    $topics = is_array($interp['topics'] ?? null) ? $interp['topics'] : [];
@endphp

@section('title', 'Nhân Duyên Song Mệnh: ' . ($pA['nickname'] ?? '') . ' & ' . ($pB['nickname'] ?? ''))
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@section('content')
<div class="mayhen_tuvi_page">
    <section class="mayhen_tuvi_celestial_hero">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_btn_row" style="justify-content: space-between; margin-bottom: 18px;">
                <a href="{{ route('tuvi.synastry.index') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">&larr; Tạo Đối Chiếu Khác</a>
                <a href="{{ route('tuvi.profiles') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Hồ Sơ Thiên Mệnh</a>
            </div>

            <div class="mayhen_tuvi_hero_head">
                <span class="mayhen_tuvi_kicker">🔒 Báo Cáo Riêng Tư • Nhật · Nguyệt · Duyên</span>
                <h1 class="mayhen_tuvi_hero_h1" style="font-size: clamp(25px, 3.3vw, 38px);">
                    {{ $interp['headline'] ?? ('Nhân Duyên Song Mệnh: ' . $pA['nickname'] . ' & ' . $pB['nickname']) }}
                </h1>
                <p class="mayhen_tuvi_hero_lead">{{ $basis['disclaimer'] ?? '' }}</p>
            </div>

            <div class="mayhen_tuvi_synastry_duet">
                <div class="mayhen_tuvi_duet_card mayhen_tuvi_duet_card--sun">
                    <span style="font-size: 11px; letter-spacing: 0.16em; color: #EBC984;">NHẬT MỆNH</span>
                    <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 46px; color: #F4DEAE; line-height: 1;">日</div>
                    <div style="font-size: 14px; font-weight: 700; color: #FFF8EC;">{{ $pA['nickname'] }}</div>
                    <div style="font-size: 12px; color: #EADBC0;">{{ $pA['nap_am'] }} ({{ $pA['element'] }})<br>Mệnh: {{ $pA['menh_stars'] }}</div>
                </div>
                <div class="mayhen_tuvi_duet_medallion" aria-hidden="true">緣</div>
                <div class="mayhen_tuvi_duet_card mayhen_tuvi_duet_card--moon">
                    <span style="font-size: 11px; letter-spacing: 0.16em; color: #EBC984;">NGUYỆT MỆNH</span>
                    <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 46px; color: #F4DEAE; line-height: 1;">月</div>
                    <div style="font-size: 14px; font-weight: 700; color: #FFF8EC;">{{ $pB['nickname'] }}</div>
                    <div style="font-size: 12px; color: #EADBC0;">{{ $pB['nap_am'] }} ({{ $pB['element'] }})<br>Mệnh: {{ $pB['menh_stars'] }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_tuvi_section_light">
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_card" style="margin-bottom: 20px; border-color: #CFA860; background: #FAF5EB;">
                <span class="mayhen_tuvi_badge">Động Lực Ngũ Hành Bản Mệnh: {{ $dyn['label'] }}</span>
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 8px; color: #85182A;">Tổng Quan Song Mệnh</h2>
                <p style="font-size: 15px;">{{ $interp['overview'] ?? ($dyn['summary'] ?? '') }}</p>
            </div>

            <div class="mayhen_tuvi_layers_grid">
                <div class="mayhen_tuvi_card">
                    <span class="mayhen_tuvi_badge">Điểm Bổ Trợ Tự Nhiên</span>
                    <h3>Thế Mạnh Khi Hai Bạn Đồng Hành</h3>
                    <ul class="mayhen_tuvi_list">
                        @foreach($interp['shared_strengths'] ?? [] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="mayhen_tuvi_card">
                    <span class="mayhen_tuvi_badge">Khác Biệt Cần Thấu Hiểu</span>
                    <h3>Điều Nên Chủ Động Trò Chuyện Sớm</h3>
                    <ul class="mayhen_tuvi_list">
                        @foreach($interp['differences_to_discuss'] ?? [] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mayhen_tuvi_card" style="margin-bottom: 26px;">
                <span class="mayhen_tuvi_badge">Hành Trang Tiền Hôn Nhân</span>
                <h3>Những Câu Hỏi Nên Cùng Ngồi Xuống Trao Đổi Trước Khi Cưới</h3>
                <ul class="mayhen_tuvi_list">
                    @foreach($interp['pre_marriage_questions'] ?? [] as $q)
                        <li>{{ $q }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="mayhen_tuvi_section_head">
                <h2>Chi Tiết 10 Chủ Đề Song Mệnh</h2>
                <p>Gợi mở chiều sâu cho từng lát cắt đời sống đôi lứa — luôn đặt sự chân thành và lắng nghe lên trên mọi ký hiệu sao.</p>
            </div>
            <div class="mayhen_tuvi_layers_grid">
                @foreach($topics as $idx => $topic)
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">{{ $topic['label'] ?? $idx }}</span>
                        <p>{{ $topic['insight'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>

            {{-- GỢI Ý HỆ SINH THÁI MÂY HẸN NHẸ NHÀNG --}}
            <div class="mayhen_tuvi_card" style="margin-top: 24px; background: #FAF5EB; border-color: #DFCFAF;">
                <span class="mayhen_tuvi_badge">Hệ Sinh Thái Chuẩn Bị Ngày Chung Đôi — Mây Hẹn</span>
                <h3>Đồng hành cùng hai bạn từ lúc thấu hiểu đến ngày trọng đại</h3>
                <p style="margin-bottom: 14px;">Nếu hai bạn đang bước vào hành trình chuẩn bị hôn lễ, Mây Hẹn có sẵn các công cụ tinh tế đồng hành cùng cặp đôi và hai bên gia đình:</p>
                <div class="mayhen_tuvi_btn_row">
                    <a href="{{ route('templates.index') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Khám Phá Thiệp Cưới Online</a>
                    <a href="{{ route('blog.index') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Cẩm Nang Cưới Hỏi</a>
                    <a href="{{ route('iching.index') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Quán Chiếu Kinh Dịch</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
