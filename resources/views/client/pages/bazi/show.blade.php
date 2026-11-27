@extends('client.layouts.master')

@section('title', 'Bài Luận Bát Tự — ' . $record->profile_name)
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ HỒ SƠ BÁT TỰ RIÊNG TƯ · {{ $record->engine_version }} · {{ $record->method_id }}</div>
            <h1 class="mayhen_bazi_h1">{{ $record->profile_name }}</h1>
            <p class="mayhen_bazi_lead">
                Nhật Chủ <strong>{{ $record->day_master_label }}</strong> · Tứ Trụ: <strong>{{ $record->four_pillars_summary }}</strong>
                @if (! empty($report['ai_powered']))
                    · <span class="mayhen_bazi_badge">Luận giải AI 10 tầng</span>
                @endif
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                <a href="{{ route('bazi.profiles') }}" class="mayhen_bazi_btn mayhen_bazi_btn--outline mayhen_bazi_btn--sm">&larr; Danh Sách Hồ Sơ</a>
                <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn mayhen_bazi_btn--sm">+ Lập Lá Số Mới</a>
                <button type="button" class="mayhen_bazi_btn mayhen_bazi_btn--outline mayhen_bazi_btn--sm" data-bazi-export="1:1">PNG 1:1</button>
                <button type="button" class="mayhen_bazi_btn mayhen_bazi_btn--outline mayhen_bazi_btn--sm" data-bazi-export="4:5">PNG 4:5 (Ẩn ngày sinh)</button>
                <button type="button" id="mayhenBaziToggleShareBtn" data-endpoint="{{ route('bazi.toggle_share', ['slug' => $record->slug]) }}" class="mayhen_bazi_btn mayhen_bazi_btn--outline mayhen_bazi_btn--sm">
                    {{ $record->is_shared ? 'Tắt Link Chia Sẻ Ẩn Danh' : 'Tạo Link Chia Sẻ Ẩn Danh' }}
                </button>
            </div>
            <div id="mayhenBaziShareBox" style="margin-top:10px;font-size:13px;color:#1E4D3B;{{ $record->is_shared ? '' : 'display:none;' }}">
                @if ($record->is_shared && $record->share_token)
                    Link chia sẻ ẩn danh (đã ẩn ngày giờ sinh): <a href="{{ route('bazi.shared', ['shareToken' => $record->share_token]) }}" target="_blank" style="font-weight:600;color:#1E4D3B;">{{ route('bazi.shared', ['shareToken' => $record->share_token]) }}</a>
                @endif
            </div>
        </div>
    </section>

    <div class="mayhen_bazi_container">
        <div id="mayhenBaziWarnings" class="mayhen_bazi_warning_box" style="display:none;margin-top:20px;"></div>

        {{-- Tứ Trụ 3D + Bảng 2D --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_stage_card">
                <div class="mayhen_bazi_stage_top">
                    <div>
                        <div style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:#CFA860;">KHÔNG GIAN TỨ TRỤ 3D</div>
                        <div id="mayhenBaziStageMeta" style="font-size:13px;color:rgba(250,245,235,0.82);margin-top:2px;">{{ $record->four_pillars_summary }}</div>
                    </div>
                    <div class="mayhen_bazi_stage_controls">
                        <button type="button" class="mayhen_bazi_stage_btn is-active" data-bazi-view="3d">Tứ Trụ 3D</button>
                        <button type="button" class="mayhen_bazi_stage_btn" data-bazi-view="2d">Bảng 2D</button>
                        <label style="font-size:11.5px;color:#F4DCAA;display:inline-flex;align-items:center;gap:6px;">
                            Xoay 3D
                            <input id="mayhenBaziTiltRange" type="range" min="-18" max="18" value="0" style="width:74px;">
                        </label>
                    </div>
                </div>
                <div id="mayhenBaziViewport3D" class="mayhen_bazi_pillars_viewport">
                    <div id="mayhenBaziPillars3D" class="mayhen_bazi_pillars_3d"></div>
                </div>
                <div id="mayhenBaziTable2D" class="mayhen_bazi_table_wrap" style="display:none;color:#292524;"></div>
            </div>
        </section>

        {{-- Báo Cáo Luận Giải 10 Tầng (Đưa lên ngay sau Tứ Trụ để đọc bài luận liền mạch) --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Bài Luận Bát Tự 10 Tầng Có Căn Cứ</h2>
                <div id="mayhenBaziReportBox" class="mayhen_bazi_report_grid"></div>
            </div>
        </section>

        {{-- Ngũ Hành 3 Chế Độ & Thập Thần Orbit --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_two_col">
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Phân Tích Ngũ Hành (3 Chế Độ Độc Lập)</h2>
                    <div class="mayhen_bazi_tabs">
                        <button type="button" class="mayhen_bazi_tab_btn mayhen_bazi_el_mode_btn is-active" data-el-mode="eight_chars">8 Chữ</button>
                        <button type="button" class="mayhen_bazi_tab_btn mayhen_bazi_el_mode_btn" data-el-mode="with_hidden">Mở Rộng Tàng Can</button>
                        <button type="button" class="mayhen_bazi_tab_btn mayhen_bazi_el_mode_btn" data-el-mode="seasonal_qi">Khí Thế Nguyệt Lệnh</button>
                    </div>
                    <div id="mayhenBaziElementsSvg" class="mayhen_bazi_svg_box"></div>
                    <div id="mayhenBaziElementsBars"></div>
                </div>
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Thập Thần Orbit (Xoay Quanh Nhật Chủ)</h2>
                    <div id="mayhenBaziTenGodsSvg" class="mayhen_bazi_svg_box"></div>
                    <div id="mayhenBaziTenGodsDetail"></div>
                </div>
            </div>
        </section>

        {{-- Cách Cục, Dụng Thần & Thần Sát --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_two_col">
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Cách Cục, Quan Hệ Can Chi &amp; Dụng Thần</h2>
                    <div id="mayhenBaziStructureBox"></div>
                </div>
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Thần Sát Bát Tự &amp; Thai Nguyên / Mệnh Cung</h2>
                    <div id="mayhenBaziStarsBox"></div>
                </div>
            </div>
        </section>

        {{-- Timeline Đại Vận & Time Travel --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Timeline Đại Vận 10 Năm &amp; Time Travel (Lưu Niên · Lưu Nguyệt)</h2>
                <div id="mayhenBaziLuckBox" style="margin-bottom:20px;"></div>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding-top:16px;border-top:1px solid var(--mayhen-bazi-border);margin-bottom:14px;">
                    <label for="mayhenBaziAnnualYear" style="font-weight:600;font-size:13.5px;">Chọn năm Time Travel:</label>
                    <input id="mayhenBaziAnnualYear" type="number" min="1900" max="2100" value="{{ now('Asia/Ho_Chi_Minh')->year }}" data-endpoint="{{ route('bazi.annual_flow') }}" class="mayhen_bazi_input" style="width:120px;">
                </div>
                <div id="mayhenBaziAnnualBox"></div>
            </div>
        </section>

        {{-- Cây Lập Luận & Căn Cứ Thời Gian --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Cây Lập Luận &amp; Căn Cứ Thời Gian</h2>
                <div id="mayhenBaziTimeDetail" style="margin-bottom:16px;"></div>
                <div id="mayhenBaziEvidenceBox" class="mayhen_bazi_evidence_list"></div>
            </div>
        </section>

        {{-- Trợ Lý Hỏi Đáp Bát Tự AI (Theo Hạn Mức Gói) --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Trợ Lý Bát Tự AI (Hỏi Đáp Theo Lá Số Này)</h2>
                <p class="mayhen_bazi_card_sub">
                    Gói <strong>{{ $baziQuota['plan_label'] }}</strong> hỗ trợ tối đa <strong>{{ $baziQuota['max_followups'] }} câu hỏi sâu/hồ sơ</strong>
                    (đã dùng {{ $record->followups->count() }}/{{ $baziQuota['max_followups'] }}).
                </p>
                <form id="mayhenBaziFollowupForm" data-endpoint="{{ route('bazi.followup', ['slug' => $record->slug]) }}" style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                    <input type="text" name="question" class="mayhen_bazi_input" style="flex:1;min-width:240px;" placeholder="Ví dụ: Vì sao hai phương pháp Phù Ức và Điều Hậu đề xuất khác nhau?" required>
                    <button type="submit" class="mayhen_bazi_btn">Hỏi AI</button>
                </form>
                <div id="mayhenBaziFollowupError" style="color:#85182A;font-size:13px;margin-bottom:10px;"></div>
                <div id="mayhenBaziFollowupList">
                    @foreach ($record->followups as $fu)
                        <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.16);border-radius:10px;margin-bottom:10px;font-size:13.5px;">
                            <div style="font-weight:600;color:#1E4D3B;margin-bottom:4px;">Hỏi: {{ $fu->question }}</div>
                            <div class="mayhen_md_prose">{!! safe_ai_markdown_html($fu->answer) !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>

<script id="mayhenBaziInitialData" type="application/json">@json(['chart' => $chart, 'report' => $report], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/bazi.js') }}"></script>
@endpush
