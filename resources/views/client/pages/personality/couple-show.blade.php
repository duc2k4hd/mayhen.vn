@extends('client.layouts.master')

@section('title', 'Đối Chiếu Tính Cách Cặp Đôi — '.($report->type_a ?? '?').' & '.($report->type_b ?? '...'))
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/personality.js') }}"></script>
@endpush

@section('content')
@php $comp = (array) ($report->comparison_json ?? []); @endphp
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            @if(session('success'))
                <div class="mayhen_personality_notice" style="margin-bottom:20px; border-left-color:var(--mayhen-personality-jade);">{{ session('success') }}</div>
            @endif
            <span class="mayhen_personality_kicker">Couple Portrait • Đối Chiếu Hai Hồ Sơ Độc Lập</span>
            <h1 class="mayhen_personality_h1">{{ $report->partner_a_name ?? 'Bạn A' }} & {{ $report->partner_b_name ?? 'Đang chờ người thương' }}</h1>

            <div class="mayhen_personality_couple_stage" style="margin-top:24px;">
                <div class="mayhen_personality_sculpture_card" data-mode="3d">
                    @include('client.pages.personality.partials.sculpture-3d', [
                        'typeInfo' => $typeInfoA,
                        'canvasHeight' => 200,
                        'fallbackSvg' => $artworkA,
                    ])
                    <div class="mayhen_personality_type_code" style="margin-top:10px;">{{ $report->type_a }}</div>
                    <div style="font-weight:700; color:var(--mayhen-personality-crimson);">{{ $typeInfoA['art_title'] ?? '' }}</div>
                </div>
                <div style="text-align:center; font-family:'Playfair Display', serif; font-size:1.8rem; color:var(--mayhen-personality-gold);">×</div>
                <div class="mayhen_personality_sculpture_card" data-mode="3d">
                    @if($artworkB)
                        @include('client.pages.personality.partials.sculpture-3d', [
                            'typeInfo' => $typeInfoB,
                            'canvasHeight' => 200,
                            'fallbackSvg' => $artworkB,
                        ])
                        <div class="mayhen_personality_type_code" style="margin-top:10px;">{{ $report->type_b }}</div>
                        <div style="font-weight:700; color:var(--mayhen-personality-crimson);">{{ $typeInfoB['art_title'] ?? '' }}</div>
                    @else
                        <div style="padding:48px 16px;">
                            <div style="font-weight:700; font-size:1.1rem;">Mã mời kết nối: <code>{{ $report->invite_code }}</code></div>
                            <p style="font-size:0.88rem; color:var(--mayhen-personality-muted); margin-top:8px;">
                                Gửi mã này cho người thương để họ làm bài độc lập và nhập mã tại trang Tính Cách Cặp Đôi.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if(! empty($comp))
        <section class="mayhen_personality_section">
            <div class="mayhen_personality_container">
                <div class="mayhen_personality_notice" style="margin-bottom:24px;">
                    <strong>{{ $comp['factual_headline'] ?? '' }}</strong><br>
                    {{ $comp['ethical_disclaimer'] ?? '' }}
                </div>

                <h2 class="mayhen_personality_h2">So sánh 4 chiều xu hướng</h2>
                <div class="mayhen_personality_grid_2" style="margin-bottom:28px;">
                    @foreach((array) ($comp['dimensions'] ?? []) as $d)
                        <div class="mayhen_personality_card">
                            <div class="mayhen_personality_type_code">{{ $d['dimension'] }}: {{ $d['tendency_a'] }} vs {{ $d['tendency_b'] }}</div>
                            <h3 class="mayhen_personality_h3">{{ $d['name'] }}</h3>
                            <p style="font-size:0.9rem; line-height:1.6;">{{ $d['summary'] }}</p>
                            <p style="font-size:0.84rem; color:var(--mayhen-personality-jade); margin:8px 0 0;">
                                <strong>Câu hỏi thảo luận:</strong> {{ $d['dialogue_prompt'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if(! empty($comp['reflections']))
                    <h2 class="mayhen_personality_h2">Đối chiếu câu trả lời thấu hiểu thực tế</h2>
                    <div class="mayhen_personality_grid_2" style="margin-bottom:28px;">
                        @foreach($comp['reflections'] as $ref)
                            <div class="mayhen_personality_card">
                                <div style="font-weight:700; margin-bottom:8px;">{{ $ref['question'] }}</div>
                                <div style="font-size:0.88rem;"><strong>{{ $report->partner_a_name }}:</strong> {{ $ref['answer_a'] }}</div>
                                <div style="font-size:0.88rem; margin-top:4px;"><strong>{{ $report->partner_b_name }}:</strong> {{ $ref['answer_b'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <h2 class="mayhen_personality_h2">12 Chủ Đề Tình Yêu & Đồng Hành Hôn Nhân</h2>
                <div class="mayhen_personality_grid_3">
                    @foreach((array) ($comp['twelve_love_topics'] ?? []) as $topic)
                        <div class="mayhen_personality_card">
                            <div style="font-size:0.78rem; color:var(--mayhen-personality-gold); font-weight:700;">Chủ đề {{ $topic['index'] }}</div>
                            <h3 class="mayhen_personality_h3">{{ $topic['title'] }}</h3>
                            <p style="font-size:0.88rem; line-height:1.6; color:var(--mayhen-personality-muted); margin:0;">{{ $topic['guidance'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mayhen_personality_card" style="margin-top:28px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
                    <div>
                        <h3 class="mayhen_personality_h3">Sẵn sàng bước sang hành trình chuẩn bị hôn nhân?</h3>
                        <p style="margin:0; font-size:0.9rem; color:var(--mayhen-personality-muted);">Tiếp nối tự nhiên sang Wedding Workspace & Kho Thiệp Cưới Online của Mây Hẹn.</p>
                    </div>
                    <div class="mayhen_personality_actions">
                        <a href="{{ url('/to-chuc-dam-cuoi') }}" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm">Tổ Chức Đám Cưới</a>
                        <a href="{{ url('/mau-thiep') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Xem Mẫu Thiệp</a>
                    </div>
                </div>
            </div>
        </section>
    @endif
</main>
@endsection
