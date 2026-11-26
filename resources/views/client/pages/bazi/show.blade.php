@extends('client.layouts.master')

@section('title', 'Hồ Sơ Bát Tự — ' . $record->profile_name)
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ HỒ SƠ BÁT TỰ RIÊNG TƯ · {{ $record->engine_version }}</div>
            <h1 class="mayhen_bazi_h1">{{ $record->profile_name }}</h1>
            <p class="mayhen_bazi_lead">{{ $record->four_pillars_summary }} · Method: {{ $record->method_id }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <a href="{{ route('bazi.profiles') }}" class="mayhen_bazi_btn mayhen_bazi_btn--outline mayhen_bazi_btn--sm">&larr; Danh Sách Hồ Sơ</a>
                <button type="button" class="mayhen_bazi_btn mayhen_bazi_btn--sm" data-bazi-export="4:5">Xuất PNG 4:5 (Ẩn ngày sinh)</button>
            </div>
        </div>
    </section>

    <div class="mayhen_bazi_container">
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_stage_card">
                <div id="mayhenBaziViewport3D" class="mayhen_bazi_pillars_viewport">
                    <div id="mayhenBaziPillars3D" class="mayhen_bazi_pillars_3d"></div>
                </div>
            </div>
        </section>

        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Luận Giải 10 Tầng</h2>
                <div id="mayhenBaziReportBox" class="mayhen_bazi_report_grid"></div>
            </div>
        </section>

        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Trợ Lý Bát Tự AI (Hỏi Đáp Theo Lá Số Này)</h2>
                <p class="mayhen_bazi_card_sub">Đặt câu hỏi cụ thể về Nhật Chủ, Thập Thần, Đại Vận hoặc Dụng Thần của lá số này.</p>
                <form id="mayhenBaziFollowupForm" data-endpoint="{{ route('bazi.followup', ['slug' => $record->slug]) }}" style="display:flex;gap:10px;margin-bottom:14px;">
                    <input type="text" name="question" class="mayhen_bazi_input" placeholder="Ví dụ: Vì sao hai phương pháp Phù Ức và Điều Hậu đề xuất khác nhau?" required>
                    <button type="submit" class="mayhen_bazi_btn">Hỏi AI</button>
                </form>
                <div id="mayhenBaziFollowupError" style="color:#85182A;font-size:13px;margin-bottom:10px;"></div>
                <div id="mayhenBaziFollowupList">
                    @foreach ($record->followups as $fu)
                        <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;margin-bottom:10px;font-size:13.5px;">
                            <div style="font-weight:600;color:#1E4D3B;margin-bottom:4px;">Hỏi: {{ $fu->question }}</div>
                            <div>{{ $fu->answer }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>

<script id="mayhenBaziInitialData" type="application/json">{!! json_encode(['chart' => $chart, 'report' => $report], JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/bazi.js') }}?v={{ filemtime(public_path('assets/client/js/pages/bazi.js')) }}" defer></script>
@endpush
