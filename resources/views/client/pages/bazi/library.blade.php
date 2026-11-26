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
            <div class="mayhen_bazi_eyebrow">✦ BÁCH KHOA BÁT TỰ · CHUẨN HÓA NGUỒN CỔ ĐIỂN</div>
            <h1 class="mayhen_bazi_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_bazi_lead">Tra cứu trọn bộ 10 Thiên Can, 12 Địa Chi (bảng Tàng Can chuẩn), 10 Thập Thần, 60 Hoa Giáp – 30 Nạp Âm và các Method Profile của Mây Hẹn – Tứ Trụ Nhân Sinh.</p>
            <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn mayhen_bazi_btn--sm">&larr; Lập Lá Số Tứ Trụ Miễn Phí</a>
        </div>
    </section>

    <div class="mayhen_bazi_container">
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">1. Thập Thiên Can (10 Nhật Chủ)</h2>
                <div class="mayhen_bazi_table_wrap">
                    <table class="mayhen_bazi_table">
                        <thead><tr><th>Thiên Can</th><th>Hán tự / Pinyin</th><th>Ngũ Hành · Âm Dương</th><th>Biểu tượng &amp; Khí chất</th></tr></thead>
                        <tbody>
                            @foreach ($stems as $s)
                                <tr>
                                    <td><strong>{{ $s['name'] }}</strong></td>
                                    <td>{{ $s['hanzi'] }} · {{ $s['pinyin'] }}</td>
                                    <td>{{ $s['yin_yang'] }} {{ $s['element'] }}</td>
                                    <td><strong>{{ $s['symbol'] }}</strong> — {{ $s['nature'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">2. Thập Nhị Địa Chi &amp; Bảng Tàng Can</h2>
                <div class="mayhen_bazi_table_wrap">
                    <table class="mayhen_bazi_table">
                        <thead><tr><th>Địa Chi</th><th>Con giáp VN</th><th>Ngũ Hành · Mùa</th><th>Khung giờ</th><th>Tàng Can (Bản / Trung / Dư Khí)</th></tr></thead>
                        <tbody>
                            @foreach ($branches as $idx => $b)
                                <tr>
                                    <td><strong>{{ $b['hanzi'] }} {{ $b['name'] }}</strong></td>
                                    <td>{{ $b['zodiac'] }}</td>
                                    <td>{{ $b['element'] }} · {{ $b['season'] }}</td>
                                    <td>{{ $b['hours'] }}</td>
                                    <td>
                                        @foreach ($hiddenStems[$idx] as $hs)
                                            {{ $stems[$hs['stem_index']]['name'] }} ({{ $hs['role_label'] }})@if (! $loop->last), @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">3. Thập Thần (Ten Gods)</h2>
                <div class="mayhen_bazi_two_col">
                    @foreach ($tenGods as $tg)
                        <div style="padding:12px 14px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.14);border-radius:10px;">
                            <strong style="color:#1E4D3B;">{{ $tg['name'] }} ({{ $tg['hanzi'] }})</strong>
                            <span style="font-size:12px;color:#6E6259;"> · {{ $tg['group'] }}</span>
                            <p style="margin:5px 0 0;font-size:13px;line-height:1.55;">{{ $tg['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">4. Lục Thập Hoa Giáp, 30 Nạp Âm &amp; Tuần Không Vong</h2>
                <div class="mayhen_bazi_three_col">
                    @foreach ($sixtyPillars as $p)
                        <div style="padding:8px 10px;background:#FAF5EB;border:1px solid rgba(78,52,46,0.12);border-radius:8px;font-size:12.5px;">
                            <strong>{{ $p['index'] + 1 }}. {{ $p['hanzi'] }} {{ $p['label'] }}</strong>
                            <div style="color:#1E4D3B;">{{ $p['nap_am']['name'] }} ({{ $p['nap_am']['element'] }})</div>
                            <div style="color:#6E6259;font-size:11px;">Không Vong: {{ $p['kong_wang'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
