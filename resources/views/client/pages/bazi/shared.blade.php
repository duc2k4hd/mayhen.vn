@extends('client.layouts.master')

@section('title', 'Thẻ Bát Tự Chia Sẻ Ẩn Danh')
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ THẺ BÁT TỰ CHIA SẺ ẨN DANH (ĐÃ ẨN NGÀY GIỜ SINH)</div>
            <h1 class="mayhen_bazi_h1">Nhật Chủ {{ $chart['day_master']['name'] }} {{ $chart['day_master']['element'] }} ({{ $chart['day_master']['hanzi'] }})</h1>
            <p class="mayhen_bazi_lead">{{ $chart['structure']['name'] }} · {{ $record->four_pillars_summary }}</p>
            <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn">Lập Lá Số Tứ Trụ Miễn Phí Của Bạn &rarr;</a>
        </div>
    </section>
</div>
@endsection
