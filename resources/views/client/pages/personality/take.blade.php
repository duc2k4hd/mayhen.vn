@extends('client.layouts.master')

@section('title', 'Đang Làm Bài Trắc Nghiệm — '.$method['name'])
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/personality.js') }}"></script>
@endpush

@section('content')
<main class="mayhen_personality_page" style="padding-bottom: 120px;">
    <div id="mayhen_personality_test_app"
         class="mayhen_personality_test_shell mayhen_personality_test_shell--focus"
         data-save-url="{{ route('personality.session.save', ['sessionToken' => $session->session_token]) }}"
         data-submit-url="{{ route('personality.session.submit', ['sessionToken' => $session->session_token]) }}"
         data-total="{{ count($questions) }}"
         data-scale-max="{{ $method['scale_max'] }}"
         data-page-size="1"
         data-auto-advance="1"
         data-elapsed="{{ (int) $session->duration_seconds }}"
         data-saved-responses='@json((object) $savedResponses)'>

        {{-- Header tiến độ gọn gàng, không che nội dung --}}
        <div class="mayhen_personality_test_header_card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <div>
                    <span class="mayhen_personality_kicker" style="margin-bottom:2px;">Mây Hẹn • Bản Sắc Tâm Hồn</span>
                    <div style="font-weight:700; font-size:0.98rem; color:var(--mayhen-personality-ink);">{{ $method['name'] }}</div>
                </div>
                <div style="text-align:right;">
                    <div id="mayhen_personality_progress_text" style="font-size:0.86rem; font-weight:700; color:var(--mayhen-personality-jade);">
                        Câu 1 / {{ count($questions) }}
                    </div>
                    <div id="mayhen_personality_save_status" style="font-size:0.78rem; color:var(--mayhen-personality-muted);">
                        Tự động lưu • Phím 1–{{ $method['scale_max'] }}
                    </div>
                </div>
            </div>
            <div class="mayhen_personality_test_progress_track" style="margin-top:12px;">
                <div id="mayhen_personality_progress_fill" class="mayhen_personality_test_progress_fill"></div>
            </div>
        </div>

        <div id="mayhen_personality_missing_warning" class="mayhen_personality_notice" style="margin-bottom:18px; border-left-color:var(--mayhen-personality-crimson);" hidden></div>

        {{-- Bố cục 2 cột tinh tế: Sân khấu 3D đồng hành + Thẻ câu hỏi tập trung 1 câu/màn hình --}}
        <div class="mayhen_personality_focus_grid">
            <div class="mayhen_personality_focus_stage">
                @include('client.pages.personality.partials.sculpture-3d', [
                    'sculptureKey' => 'prism_crystal',
                    'primaryColor' => '#1E4D3B',
                    'accentColor' => '#CFA860',
                    'labelCode' => 'Điêu khắc bản sắc 3D',
                    'canvasHeight' => 230,
                    'compact' => true,
                ])
                <p style="font-size:0.8rem; color:var(--mayhen-personality-muted); text-align:center; margin:10px 0 0; line-height:1.55;">
                    Trả lời theo con người thật đời thường của bạn.<br>Chọn đáp án để tự động chuyển sang câu kế tiếp.
                </p>
            </div>

            <div class="mayhen_personality_focus_card_wrap">
                @foreach($questions as $idx => $q)
                    <article class="mayhen_personality_test_qcard mayhen_personality_test_qcard--single" data-qid="{{ $q['id'] }}" data-index="{{ $idx }}">
                        <div class="mayhen_personality_q_badge">
                            <span>Câu {{ $idx + 1 }} / {{ count($questions) }}</span>
                            <span style="color:var(--mayhen-personality-muted); font-weight:500;">Chọn 1 mức phù hợp nhất</span>
                        </div>

                        <h1 class="mayhen_personality_test_qtext mayhen_personality_test_qtext--single">{{ $q['text'] }}</h1>

                        <div class="mayhen_personality_likert_row {{ (int) $method['scale_max'] === 5 ? 'mayhen_personality_likert_row--5' : '' }}" role="radiogroup" aria-label="Câu {{ $idx + 1 }}">
                            @foreach($method['scale_labels'] as $val => $label)
                                @php
                                    $mid = ((int) $method['scale_max'] + 1) / 2;
                                    $dist = (int) abs($val - $mid);
                                    $tone = $val < $mid ? 'neg' : ($val > $mid ? 'pos' : 'mid');
                                @endphp
                                <label class="mayhen_personality_test_opt mayhen_personality_likert_btn mayhen_personality_likert_btn--{{ $tone }} mayhen_personality_likert_btn--d{{ $dist }}" data-val="{{ $val }}" title="{{ $label }}">
                                    <input type="radio" name="q_{{ $q['id'] }}" value="{{ $val }}">
                                    <span class="mayhen_personality_likert_circle">{{ $val }}</span>
                                    <span class="mayhen_personality_likert_caption">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mayhen_personality_likert_ends">
                            <span>← Hoàn toàn không đồng ý</span>
                            <span>Trung lập</span>
                            <span>Hoàn toàn đồng ý →</span>
                        </div>

                        @if(! empty($method['allow_unsure']))
                            <div class="mayhen_personality_test_unsure">
                                <button type="button" class="mayhen_personality_test_unsure_btn">
                                    Không chắc / Không áp dụng
                                </button>
                            </div>
                        @endif
                    </article>
                @endforeach

                <div class="mayhen_personality_test_nav">
                    <button type="button" id="mayhen_personality_prev_btn" class="mayhen_personality_btn mayhen_personality_btn--outline">
                        ← Câu trước
                    </button>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <button type="button" id="mayhen_personality_next_btn" class="mayhen_personality_btn mayhen_personality_btn--primary">
                            Câu tiếp theo →
                        </button>
                        <button type="button" id="mayhen_personality_submit_btn" class="mayhen_personality_btn mayhen_personality_btn--crimson" hidden>
                            Hoàn tất & Mở kết quả 3D
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
