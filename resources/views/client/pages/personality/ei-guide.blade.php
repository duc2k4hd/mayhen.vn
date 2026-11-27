@extends('client.layouts.master')

@section('title', 'Hướng Nội và Hướng Ngoại (E / I) Trong Tâm Lý Học Tính Cách')
@section('meta_description', 'Hiểu đúng chiều Hướng nội (Introversion) và Hướng ngoại (Extraversion): hướng nội không phải nhút nhát hay lo âu xã hội, hướng ngoại không phải nói nhiều.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_narrow">
            <span class="mayhen_personality_kicker">Chiều Năng Lượng & Hướng Chú Ý (E / I)</span>
            <h1 class="mayhen_personality_h1">Hiểu Đúng Về Hướng Nội & Hướng Ngoại</h1>
            <p class="mayhen_personality_lead">{{ $dimension['neutral_summary'] }}</p>
            <div class="mayhen_personality_notice">{{ $dimension['clarification'] }}</div>
            <div class="mayhen_personality_actions" style="margin-top:20px;">
                <a href="{{ route('personality.start') }}" class="mayhen_personality_btn mayhen_personality_btn--primary">Đo xu hướng E / I của bạn</a>
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_2">
                <div class="mayhen_personality_card">
                    <h2 class="mayhen_personality_h3">8 Nhóm Thiên Hướng Ngoại (E)</h2>
                    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-top:12px;">
                        @foreach($eTypes as $code => $t)
                            <a href="{{ route('personality.type', ['typeCode' => strtolower($code)]) }}" class="mayhen_personality_type_tile">
                                <span class="mayhen_personality_type_code">{{ $code }}</span>
                                <span style="font-size:0.82rem; color:var(--mayhen-personality-muted);">{{ $t['art_title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="mayhen_personality_card">
                    <h2 class="mayhen_personality_h3">8 Nhóm Thiên Hướng Nội (I)</h2>
                    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-top:12px;">
                        @foreach($iTypes as $code => $t)
                            <a href="{{ route('personality.type', ['typeCode' => strtolower($code)]) }}" class="mayhen_personality_type_tile">
                                <span class="mayhen_personality_type_code">{{ $code }}</span>
                                <span style="font-size:0.82rem; color:var(--mayhen-personality-muted);">{{ $t['art_title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
