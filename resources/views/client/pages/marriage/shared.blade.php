@extends('client.layouts.master')

@section('title', 'Thẻ Nhân Duyên Chia Sẻ Ẩn Danh')
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ THẺ NHÂN DUYÊN ẨN DANH (ĐÃ ẨN NGÀY SINH CHI TIẾT)</div>
            <h1 class="mayhen_marriage_h1">{{ $analysis['husband']['can_chi'] }} &amp; {{ $analysis['wife']['can_chi'] }}</h1>
            <p class="mayhen_marriage_lead">Cao Ly Đầu Hình: <strong>{{ $analysis['cao_ly']['combination_name'] }} ({{ $analysis['cao_ly']['scene']['name'] }})</strong> · Bát Trạch: <strong>{{ $analysis['du_nien']['name'] }}</strong></p>
            <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_btn">Xem Tuổi Vợ Chồng Miễn Phí Của Bạn &rarr;</a>
        </div>
    </section>
</div>
@endsection
