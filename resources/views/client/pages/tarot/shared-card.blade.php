@extends('client.layouts.master')

@section('title', 'Thẻ Chia Sẻ Trải Bài Tarot Ẩn Danh — Mây Hẹn')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@section('content')
<div class="mayhen_tarot_page">
    <section class="mayhen_tarot_result_hero">
        <div class="mayhen_tarot_container" style="text-align: center;">
            <div class="mayhen_tarot_eyebrow">✦ MÂY HẸN – THE ART OF TAROT · THẺ CHIA SẺ ẨN DANH</div>
            <h1 class="mayhen_tarot_title">{{ $spread['name'] ?? 'Trải Bài Tarot' }}</h1>
            <p class="mayhen_tarot_subtitle" style="margin: 0 auto;">
                Câu hỏi cá nhân và ghi chú nhật ký đã được ẩn hoàn toàn để bảo vệ quyền riêng tư.
            </p>
        </div>
    </section>

    <div class="mayhen_tarot_container">
        <div class="mayhen_tarot_board_wrap">
            <div class="mayhen_tarot_board_row">
                @foreach ($cards as $c)
                    <div class="mayhen_tarot_board_card" style="cursor: default;">
                        <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($c)'></div>
                        <div class="mayhen_tarot_slot_caption">
                            <strong>{{ $c['position_index'] ?? $loop->iteration }}. {{ $c['position_label'] ?? '' }}</strong><br>
                            <span style="color: #E7D2A3;">{{ $c['name_en'] ?? '' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <article class="mayhen_tarot_book_card" style="max-width: 780px; margin: 0 auto; text-align: center;">
            <div class="mayhen_tarot_kicker">THÔNG ĐIỆP CHIA SẺ</div>
            <h2>{{ $interpretation['headline'] ?? 'Điểm chạm chiêm nghiệm' }}</h2>
            <p style="font-size: 15.5px; line-height: 1.78; color: #292524;">
                {{ $interpretation['summary'] ?? ($interpretation['core_answer'] ?? '') }}
            </p>
            <div style="margin-top: 22px;">
                <a href="{{ route('tarot.index') }}" class="mayhen_tarot_btn_editorial">✦ Tự tay trải bài Tarot miễn phí tại Mây Hẹn</a>
            </div>
        </article>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tarot.js') }}"></script>
@endpush
