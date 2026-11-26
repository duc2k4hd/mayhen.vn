@extends('client.layouts.master')

@section('title', 'Sổ Nhân Duyên Của Tôi')
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ KHÔNG GIAN RIÊNG TƯ CẶP ĐÔI</div>
            <h1 class="mayhen_marriage_h1">Sổ Nhân Duyên Của Tôi</h1>
            <p class="mayhen_marriage_lead">Lưu giữ các báo cáo Cao Ly Đầu Hình, Xem Tuổi Vợ Chồng và câu hỏi trao đổi riêng tư.</p>
            <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_btn">+ Xem Tuổi Cặp Đôi Mới</a>
        </div>
    </section>

    <div class="mayhen_marriage_container" style="margin-top:24px;">
        @if ($reports->isEmpty())
            <div class="mayhen_marriage_card" style="text-align:center;">
                <p style="margin:0 0 12px;color:#6E6259;">Bạn chưa lưu Sổ Nhân Duyên nào.</p>
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_btn">Bắt Đầu Xem Tuổi Vợ Chồng</a>
            </div>
        @else
            <div class="mayhen_marriage_three_col">
                @foreach ($reports as $r)
                    <div class="mayhen_marriage_card">
                        <span class="mayhen_marriage_badge mayhen_marriage_badge--crimson">{{ $r->cao_ly_id }}</span>
                        <h3 class="mayhen_marriage_card_title" style="font-size:20px;margin-top:8px;">{{ $r->title }}</h3>
                        <p style="font-size:13px;color:#6E6259;margin:0 0 12px;">{{ $r->husband_can_chi }} &amp; {{ $r->wife_can_chi }} · {{ $r->created_at?->format('d/m/Y') }}</p>
                        <a href="{{ route('marriage.book.show', ['slug' => $r->slug]) }}" class="mayhen_marriage_btn mayhen_marriage_btn--sm">Mở Sổ Nhân Duyên &rarr;</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
