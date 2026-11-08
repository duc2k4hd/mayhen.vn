@extends('client.layouts.master')

@section('title', 'Bảng Radar Sẵn Sàng Tuần Lễ Cưới | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/wedding-week-readiness.css') }}">
@endpush

@section('content')
<div class="mayhen_week_readiness_page">
    <div class="mayhen_week_readiness_container">
        {{-- Breadcrumb & Back Link --}}
        <div>
            <a href="{{ route('account.invitations.settings', $invitation->id) }}" class="mayhen_week_readiness_back_link">
                &larr; Quay lại Cài đặt thiệp cưới
            </a>
        </div>

        {{-- Header --}}
        <header class="mayhen_week_readiness_header">
            @if($radar['is_wedding_week'])
                <span class="mayhen_week_readiness_badge mayhen_week_readiness_badge--urgent">
                    🔥 TUẦN LỄ CƯỚI TRỌNG ĐẠI
                </span>
            @else
                <span class="mayhen_week_readiness_badge">
                    WEDDING WEEK SUPPORT RADAR
                </span>
            @endif
            <h1 class="mayhen_week_readiness_title">Bảng Kiểm Tra Sẵn Sàng Ngày Cưới</h1>
            <p class="mayhen_week_readiness_subtitle">
                Đám cưới: <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong> &bull;
                Rà soát 5 trụ cột quan trọng nhất và cam kết đồng hành kỹ thuật trực ban 15 phút.
            </p>
        </header>

        {{-- 1. Countdown Card --}}
        <div class="mayhen_week_readiness_countdown_card">
            <div class="mayhen_week_readiness_countdown_info">
                <div class="mayhen_week_readiness_countdown_label">Thời Gian Hôn Lễ</div>
                <div class="mayhen_week_readiness_countdown_date">
                    {{ $radar['wedding_date'] ? 'Ngày ' . $radar['wedding_date'] : 'Chưa thiết lập ngày cưới chính thức' }}
                </div>
                <p class="mayhen_week_readiness_countdown_desc">
                    @if($radar['days_until_wedding'] !== null)
                        @if($radar['days_until_wedding'] > 7)
                            Còn {{ $radar['days_until_wedding'] }} ngày nữa tới hôn lễ. Đây là thời gian lý tưởng để hoàn tất danh sách khách và rà soát sơ đồ bàn tiệc.
                        @elseif($radar['days_until_wedding'] >= 0)
                            Chỉ còn {{ $radar['days_until_wedding'] }} ngày! Toàn bộ đội ngũ hỗ trợ kỹ thuật Mây Hẹn đã kích hoạt chế độ ưu tiên cao nhất cho đám cưới của bạn.
                        @else
                            Hôn lễ đã diễn ra thành công cách đây {{ abs($radar['days_until_wedding']) }} ngày. Bạn có thể xuất kỷ yếu và album ảnh làm kỷ niệm.
                        @endif
                    @else
                        Hãy cập nhật ngày cưới trong phần Cài đặt để hệ thống tự động đếm ngược và nhắc việc chính xác.
                    @endif
                </p>
            </div>
            <div class="mayhen_week_readiness_countdown_clock">
                <div class="mayhen_week_readiness_clock_number">
                    {{ $radar['days_until_wedding'] !== null ? max(0, $radar['days_until_wedding']) : '--' }}
                </div>
                <div class="mayhen_week_readiness_clock_unit">Ngày Đếm Ngược</div>
            </div>
        </div>

        {{-- 2. Radar Score Overview Card --}}
        <div class="mayhen_week_readiness_score_card">
            <div class="mayhen_week_readiness_score_flex">
                <div class="mayhen_week_readiness_score_meta">
                    <h2 class="mayhen_week_readiness_score_headline">Chỉ Số Sẵn Sàng Tổng Thể</h2>
                    <span class="mayhen_week_readiness_score_status mayhen_week_readiness_score_status--{{ $radar['status'] }}">
                        {{ $radar['status_label'] }}
                    </span>
                </div>
                <div class="mayhen_week_readiness_gauge_wrapper">
                    <span class="mayhen_week_readiness_gauge_val">{{ $radar['readiness_score'] }}</span>
                    <span class="mayhen_week_readiness_gauge_total">/ 100 Điểm</span>
                </div>
            </div>
            <div class="mayhen_week_readiness_progress_bar_bg">
                <div class="mayhen_week_readiness_progress_bar_fill" style="width: {{ $radar['readiness_score'] }}%;"></div>
            </div>
        </div>

        {{-- 3. The 5 Core Pillars --}}
        <div>
            <h3 class="mayhen_week_readiness_section_title">5 Trụ Cột Vận Hành Sẵn Sàng</h3>
            <div class="mayhen_week_readiness_pillars_grid">
                @foreach($radar['pillars'] as $pillar)
                    <div class="mayhen_week_readiness_pillar_card">
                        <div class="mayhen_week_readiness_pillar_body">
                            <div class="mayhen_week_readiness_pillar_header">
                                <span class="mayhen_week_readiness_pillar_icon">
                                    @if($pillar['status'] === 'ready') ✅ @else ⚠️ @endif
                                </span>
                                <h4 class="mayhen_week_readiness_pillar_name">{{ $pillar['title'] }}</h4>
                                <span class="mayhen_week_readiness_pillar_badge mayhen_week_readiness_pillar_badge--{{ $pillar['status'] }}">
                                    {{ $pillar['score'] }}/20 Điểm
                                </span>
                            </div>
                            <p class="mayhen_week_readiness_pillar_desc">{{ $pillar['description'] }}</p>
                        </div>
                        <div>
                            <a href="{{ $pillar['action_url'] }}" class="mayhen_week_readiness_pillar_action">
                                {{ $pillar['action_text'] }} &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 4. Emergency Support Card --}}
        <div class="mayhen_week_readiness_emergency_card">
            <div class="mayhen_week_readiness_emergency_content">
                <span class="mayhen_week_readiness_emergency_badge">CAM KẾT ĐỒNG HÀNH NGÀY CƯỚI</span>
                <h3 class="mayhen_week_readiness_emergency_title">Trung Tâm Phản Ứng Nhanh Mây Hẹn</h3>
                <p class="mayhen_week_readiness_emergency_text">
                    {{ $radar['emergency_support']['commitment'] }}
                </p>
            </div>
            <div class="mayhen_week_readiness_emergency_channels">
                <a href="tel:19008800" class="mayhen_week_readiness_support_btn mayhen_week_readiness_support_btn--hotline">
                    📞 {{ $radar['emergency_support']['hotline'] }}
                </a>
                <a href="{{ $radar['emergency_support']['zalo'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_week_readiness_support_btn mayhen_week_readiness_support_btn--zalo">
                    💬 Kênh Zalo Trực Ban Ngày Cưới
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

