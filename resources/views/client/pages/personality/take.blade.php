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
<main class="mayhen_personality_page">
    <div id="mayhen_personality_test_app"
         class="mayhen_personality_test_shell"
         data-save-url="{{ route('personality.session.save', ['sessionToken' => $session->session_token]) }}"
         data-submit-url="{{ route('personality.session.submit', ['sessionToken' => $session->session_token]) }}"
         data-total="{{ count($questions) }}"
         data-scale-max="{{ $method['scale_max'] }}"
         data-page-size="4"
         data-elapsed="{{ (int) $session->duration_seconds }}"
         data-saved-responses="{{ json_encode($savedResponses) }}">

        <div class="mayhen_personality_test_topbar">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <div>
                    <strong style="font-size:0.95rem;">{{ $method['name'] }}</strong>
                    <div id="mayhen_personality_progress_text" style="font-size:0.84rem; color:var(--mayhen-personality-muted);">
                        0 / {{ count($questions) }} câu
                    </div>
                </div>
                <div style="font-size:0.82rem; color:var(--mayhen-personality-jade);" id="mayhen_personality_save_status">
                    Tự động lưu đang bật • Phím 1–{{ $method['scale_max'] }}
                </div>
            </div>
            <div class="mayhen_personality_test_progress_track">
                <div id="mayhen_personality_progress_fill" class="mayhen_personality_test_progress_fill"></div>
            </div>
        </div>

        <div class="mayhen_personality_notice" style="margin-bottom: 20px;">
            Hãy trả lời dựa trên cách bạn <strong>thường suy nghĩ và hành động</strong>, không có đáp án đúng hay sai. Mọi câu trả lời được tự động lưu khi bạn làm bài.
        </div>

        <div id="mayhen_personality_missing_warning" class="mayhen_personality_notice" style="margin-bottom:20px; border-left-color:var(--mayhen-personality-crimson);" hidden></div>

        @foreach($questions as $idx => $q)
            <article class="mayhen_personality_test_qcard" data-qid="{{ $q['id'] }}">
                <div style="font-size:0.78rem; color:var(--mayhen-personality-muted); margin-bottom:6px;">
                    Câu {{ $idx + 1 }} / {{ count($questions) }}
                </div>
                <p class="mayhen_personality_test_qtext">{{ $q['text'] }}</p>

                <div class="mayhen_personality_test_scale {{ (int) $method['scale_max'] === 5 ? 'mayhen_personality_test_scale--5' : '' }}" role="radiogroup" aria-label="Câu {{ $idx + 1 }}">
                    @foreach($method['scale_labels'] as $val => $label)
                        <label class="mayhen_personality_test_opt" data-val="{{ $val }}">
                            <input type="radio" name="q_{{ $q['id'] }}" value="{{ $val }}">
                            <span class="mayhen_personality_test_opt_num">{{ $val }}</span>
                            <span class="mayhen_personality_test_opt_label">{{ $label }}</span>
                        </label>
                    @endforeach
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

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:24px;">
            <button type="button" id="mayhen_personality_prev_btn" class="mayhen_personality_btn mayhen_personality_btn--outline">
                ← Trang trước
            </button>
            <div style="display:flex; gap:10px;">
                <button type="button" id="mayhen_personality_next_btn" class="mayhen_personality_btn mayhen_personality_btn--primary">
                    Tiếp theo →
                </button>
                <button type="button" id="mayhen_personality_submit_btn" class="mayhen_personality_btn mayhen_personality_btn--crimson" hidden>
                    Hoàn tất & Xem kết quả
                </button>
            </div>
        </div>
    </div>
</main>
@endsection
