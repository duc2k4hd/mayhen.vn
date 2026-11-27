@extends('client.layouts.master')

@section('title', 'Thẻ Bản Sắc Tính Cách — '.($session->effectiveType() ?? 'Mây Hẹn'))
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_narrow" style="text-align:center;">
            <span class="mayhen_personality_kicker">Mây Hẹn • The Art of Personality</span>
            <div class="mayhen_personality_card" style="max-width:440px; margin:20px auto; padding:32px;">
                {!! $artworkSvg !!}
                <div class="mayhen_personality_type_code" style="font-size:2.2rem; margin-top:16px;">{{ $session->effectiveType() }}</div>
                @if($typeInfo)
                    <div style="font-size:1.2rem; font-weight:700; color:var(--mayhen-personality-crimson);">{{ $typeInfo['art_title'] }}</div>
                    <div style="font-size:0.95rem; color:var(--mayhen-personality-muted); margin-top:4px;">{{ $typeInfo['vietnamese_name'] }}</div>
                    <p style="font-size:0.9rem; line-height:1.65; margin:16px 0 0;">{{ $typeInfo['theory_summary'] }}</p>
                @endif
            </div>
            <a href="{{ route('personality.index') }}" class="mayhen_personality_btn mayhen_personality_btn--primary">
                Khám phá Bản Sắc Tâm Hồn của riêng bạn
            </a>
        </div>
    </section>
</main>
@endsection
