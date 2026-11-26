@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ CÔNG CỤ NGHIÊN CỨU NÂNG CAO</div>
            <h1 class="mayhen_bazi_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_bazi_lead">Nhập 4 trụ Can Chi (Năm, Tháng, Ngày, Giờ) để tìm tất cả các mốc ngày giờ sinh Dương lịch khớp hợp lệ theo Ngũ Hổ Độn, Ngũ Thử Độn và 12 mốc Tiết Khí.</p>

            <div class="mayhen_bazi_card">
                <form id="mayhenBaziReverseForm" data-endpoint="{{ route('bazi.reverse.search') }}">
                    <div class="mayhen_bazi_two_col">
                        @foreach ([
                            'year' => ['Trụ Năm', 1, 11],
                            'month' => ['Trụ Tháng', 7, 5],
                            'day' => ['Trụ Ngày', 7, 1],
                            'hour' => ['Trụ Giờ', 9, 5],
                        ] as $k => $info)
                            <div class="mayhen_bazi_form_grid mayhen_bazi_form_grid--2">
                                <div class="mayhen_bazi_field">
                                    <label>Thiên Can {{ $info[0] }}</label>
                                    <select name="{{ $k }}_stem" class="mayhen_bazi_select">
                                        @foreach ($stems as $idx => $s)
                                            <option value="{{ $idx }}" @selected($idx === $info[1])>{{ $s['hanzi'] }} {{ $s['name'] }} ({{ $s['element'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mayhen_bazi_field">
                                    <label>Địa Chi {{ $info[0] }}</label>
                                    <select name="{{ $k }}_branch" class="mayhen_bazi_select">
                                        @foreach ($branches as $idx => $b)
                                            <option value="{{ $idx }}" @selected($idx === $info[2])>{{ $b['hanzi'] }} {{ $b['name'] }} ({{ $b['zodiac'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mayhen_bazi_form_grid mayhen_bazi_form_grid--2" style="max-width:420px;margin-top:8px;">
                        <div class="mayhen_bazi_field">
                            <label>Từ năm</label>
                            <input name="from_year" type="number" min="1900" max="2100" value="1940" class="mayhen_bazi_input">
                        </div>
                        <div class="mayhen_bazi_field">
                            <label>Đến năm</label>
                            <input name="to_year" type="number" min="1900" max="2100" value="2040" class="mayhen_bazi_input">
                        </div>
                    </div>

                    <button type="submit" class="mayhen_bazi_btn">Tìm Các Ngày Giờ Sinh Khớp Tứ Trụ</button>
                    <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn mayhen_bazi_btn--outline" style="margin-left:8px;">&larr; Quay Lại /bat-tu</a>
                </form>

                <div id="mayhenBaziReverseResult"></div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/bazi.js') }}?v={{ filemtime(public_path('assets/client/js/pages/bazi.js')) }}" defer></script>
@endpush
