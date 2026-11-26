@extends('client.layouts.master')

@section('title', 'Hồ Sơ Bát Tự Cá Nhân')
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ KHÔNG GIAN RIÊNG TƯ</div>
            <h1 class="mayhen_bazi_h1">Hồ Sơ Bát Tự Của Tôi</h1>
            <p class="mayhen_bazi_lead">Toàn bộ lá số Bát Tự được lưu riêng tư theo tài khoản kèm phiên bản Engine &amp; Method Profile.</p>
            <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn">+ Lập Lá Số Mới</a>
        </div>
    </section>

    <div class="mayhen_bazi_container" style="margin-top:24px;">
        @if ($charts->isEmpty())
            <div class="mayhen_bazi_card" style="text-align:center;">
                <p style="margin:0 0 12px;color:#6E6259;">Bạn chưa lưu hồ sơ Bát Tự nào.</p>
                <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn">Lập Lá Số Đầu Tiên</a>
            </div>
        @else
            <div class="mayhen_bazi_three_col">
                @foreach ($charts as $c)
                    <div class="mayhen_bazi_card">
                        <div class="mayhen_bazi_badge">{{ $c->day_master_label }}</div>
                        <h3 class="mayhen_bazi_card_title" style="font-size:20px;margin-top:8px;">{{ $c->profile_name }}</h3>
                        <p style="font-size:13px;color:#4E342E;margin:0 0 8px;">{{ $c->four_pillars_summary }}</p>
                        <div style="font-size:11.5px;color:#6E6259;margin-bottom:12px;">Method: {{ $c->method_id }} · {{ $c->created_at?->format('d/m/Y') }}</div>
                        <a href="{{ route('bazi.show', ['slug' => $c->slug]) }}" class="mayhen_bazi_btn mayhen_bazi_btn--sm">Xem Chi Tiết &amp; Hỏi AI &rarr;</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
