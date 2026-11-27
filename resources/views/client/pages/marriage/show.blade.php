@extends('client.layouts.master')

@section('title', 'Sổ Nhân Duyên — ' . $record->title)
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ SỔ NHÂN DUYÊN RIÊNG TƯ · {{ $record->engine_version }}</div>
            <h1 class="mayhen_marriage_h1">{{ $record->title }}</h1>
            <p class="mayhen_marriage_lead">Cao Ly: <strong>{{ $analysis['cao_ly']['combination_name'] }} ({{ $analysis['cao_ly']['scene']['name'] }})</strong> · Bát Trạch: <strong>{{ $analysis['du_nien']['name'] }}</strong></p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <a href="{{ route('marriage.book.index') }}" class="mayhen_marriage_btn mayhen_marriage_btn--outline mayhen_marriage_btn--sm">&larr; Sổ Nhân Duyên</a>
                <button type="button" id="mayhenMarriageToggleShareBtn" data-endpoint="{{ route('marriage.book.toggle_share', ['slug' => $record->slug]) }}" class="mayhen_marriage_btn mayhen_marriage_btn--outline mayhen_marriage_btn--sm">
                    {{ $record->is_shared ? 'Tắt Link Chia Sẻ Ẩn Danh' : 'Tạo Link Chia Sẻ Ẩn Danh' }}
                </button>
                <a href="{{ route('templates.index') }}" class="mayhen_marriage_btn mayhen_marriage_btn--jade mayhen_marriage_btn--sm">Tạo Thiệp Cưới Online &rarr;</a>
            </div>
            <div id="mayhenMarriageShareBox" style="margin-top:10px;font-size:13px;color:#1E4D3B;{{ $record->is_shared ? '' : 'display:none;' }}">
                @if ($record->is_shared && $record->share_token)
                    Link chia sẻ ẩn danh: <a href="{{ route('marriage.shared', ['shareToken' => $record->share_token]) }}" target="_blank" style="font-weight:600;color:#1E4D3B;">{{ route('marriage.shared', ['shareToken' => $record->share_token]) }}</a>
                @endif
            </div>
        </div>
    </section>

    <div class="mayhen_marriage_container">
        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_two_col">
                <div class="mayhen_marriage_card mayhen_marriage_rings_wrap">
                    <h2 class="mayhen_marriage_card_title" style="color:#85182A;">Hai Vòng Kết Duyên</h2>
                    <div id="mayhenMarriageRingsWrap" style="width:100%;"></div>
                </div>
                <div class="mayhen_marriage_card mayhen_cao_ly_wheel_wrap">
                    <h2 class="mayhen_marriage_card_title" style="color:#1E4D3B;">Phối Cung Bát Quái Du Niên</h2>
                    <div id="mayhenBaguaSvgWrap"></div>
                    <div id="mayhenBaguaInfo" style="margin-top:10px;width:100%;padding:12px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:10px;text-align:left;"></div>
                </div>
            </div>
        </section>

        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Bài Luận Nhân Duyên Đa Phương Pháp</h2>
                <div id="mayhenMarriageAiBox" class="mayhen_marriage_two_col"></div>
            </div>
        </section>

        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Bộ 6 Câu Hỏi Trước Hôn Nhân Dành Cho Hai Bạn</h2>
                <ul style="margin:8px 0 0 18px;font-size:14px;line-height:1.7;">
                    @foreach ($analysis['pre_marriage_questions'] as $q)
                        <li>{{ $q }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Trợ Lý Duyên Hợp AI &amp; Trao Đổi Với Gia Đình</h2>
                <p class="mayhen_marriage_card_sub">Gói <strong>{{ $marriageQuota['plan_label'] }}</strong> hỗ trợ tối đa <strong>{{ $marriageQuota['max_followups'] }} câu hỏi</strong>/sổ (đã dùng {{ $record->followups->count() }}/{{ $marriageQuota['max_followups'] }}).</p>
                <form id="mayhenMarriageFollowupForm" data-endpoint="{{ route('marriage.book.followup', ['slug' => $record->slug]) }}" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
                    <input type="text" name="question" class="mayhen_marriage_input" style="flex:1;min-width:240px;" placeholder="Ví dụ: Làm thế nào để giải thích kết quả đa phương pháp cho bố mẹ yên tâm?" required>
                    <button type="submit" class="mayhen_marriage_btn">Hỏi Trợ Lý AI</button>
                </form>
                <div id="mayhenMarriageFollowupError" style="color:#85182A;font-size:13px;margin-bottom:10px;"></div>
                <div id="mayhenMarriageFollowupList">
                    @foreach ($record->followups as $fu)
                        <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(178,146,94,0.3);border-radius:10px;margin-bottom:10px;font-size:13.5px;">
                            <div style="font-weight:600;color:#85182A;margin-bottom:4px;">Hỏi: {{ $fu->question }}</div>
                            <div class="mayhen_md_prose">{!! safe_ai_markdown_html($fu->answer) !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>

<script id="mayhenMarriageInitialData" type="application/json">@json(['analysis' => $analysis, 'aiReport' => $aiReport], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
<script id="mayhenBaguaDataset" type="application/json">@json(['trigrams' => $trigrams, 'matrix' => $duNienMatrix, 'du_nien_meta' => $duNienMeta], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/marriage.js') }}"></script>
@endpush
