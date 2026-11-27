@extends('client.layouts.master')

@section('title', 'Trắc Nghiệm Big Five (OCEAN) Miễn Phí — IPIP 50 Câu')
@section('meta_description', 'Trắc nghiệm tính cách Big Five (OCEAN: Openness, Conscientiousness, Extraversion, Agreeableness, Neuroticism) độc lập dựa trên kho câu hỏi IPIP Public Domain.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_narrow">
            <span class="mayhen_personality_kicker">Mô Hình 5 Yếu Tố Độc Lập • IPIP Public Domain</span>
            <h1 class="mayhen_personality_h1">Trắc Nghiệm Big Five (OCEAN)</h1>
            <p class="mayhen_personality_lead">
                Mô hình tâm lý học đo 5 đặc điểm tính cách trên thang liên tục (O, C, E, A, N) dựa trên bộ câu hỏi <strong>IPIP 50-Item Big-Five Factor Markers</strong> (Goldberg et al., Public Domain — <code>ipip.ori.org</code>), triển khai hoàn toàn độc lập với hệ 16 nhóm.
            </p>
            <form method="POST" action="{{ route('personality.session.create') }}" class="mayhen_personality_actions">
                @csrf
                <input type="hidden" name="method_id" value="mayhen_bigfive_ipip50_v1">
                <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary">
                    Bắt đầu bài test Big Five (50 câu • 8–12 phút)
                </button>
                <a href="{{ route('personality.index') }}" class="mayhen_personality_btn mayhen_personality_btn--outline">
                    Quay lại Trắc nghiệm 16 nhóm
                </a>
            </form>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_3">
                @foreach($dimensions as $code => $dim)
                    <div class="mayhen_personality_card">
                        <div class="mayhen_personality_type_code">{{ $code }}</div>
                        <h2 class="mayhen_personality_h3">{{ $dim['name'] }}</h2>
                        <p style="font-size:0.9rem; line-height:1.65; color:var(--mayhen-personality-muted);">{{ $dim['summary'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mayhen_personality_notice" style="margin-top:24px;">
                <strong>Minh bạch khoa học:</strong> {{ $method['validation_note'] }}
            </div>
        </div>
    </section>

    @include('client.pages.personality.partials.seo-pillar', ['seoPillar' => $seoPillar ?? null])
</main>
@endsection
