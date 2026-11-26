@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ CAO LY ĐẦU HÌNH · BẢN GHI {{ $record['order'] }}/120 · {{ $record['id'] }}</div>
            <h1 class="mayhen_marriage_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_marriage_lead">
                Lưu ý: Tổ hợp <strong>{{ $record['combination_name'] }}</strong> là phép tra có hướng gồm <strong>Thiên Can {{ $record['husband_stem']['name'] }}</strong> của người chồng phối với <strong>Địa Chi {{ $record['wife_branch']['name'] }} ({{ $record['wife_branch']['zodiac'] }})</strong> của người vợ (không nhất thiết là năm sinh {{ $record['combination_name'] }}).
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <a href="{{ route('marriage.caoly.index') }}" class="mayhen_marriage_btn mayhen_marriage_btn--outline mayhen_marriage_btn--sm">&larr; Quay Lại Vòng Tra 120 Tổ Hợp</a>
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_btn mayhen_marriage_btn--sm">Xem Tuổi Vợ Chồng Đầy Đủ &rarr;</a>
            </div>
        </div>
    </section>

    <div class="mayhen_marriage_container">
        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_card">
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                    <span class="mayhen_marriage_badge" style="background:{{ $record['scene']['color'] }}22;color:{{ $record['scene']['color'] }};border-color:{{ $record['scene']['color'] }}55;">Lục Cảnh: {{ $record['scene']['name'] }} ({{ $record['scene']['hanzi'] }})</span>
                    <span class="mayhen_marriage_badge mayhen_marriage_badge--crimson">{{ $record['element_relation'] }}</span>
                    <span class="mayhen_marriage_badge">Phiên bản: {{ $record['version'] }}</span>
                </div>

                <h2 class="mayhen_marriage_card_title">Ý Nghĩa Truyền Thống &amp; Diễn Giải Hiện Đại</h2>
                <p style="font-size:14.5px;line-height:1.7;margin:0 0 12px;">{{ $record['tradition_summary'] }}</p>
                <p style="font-size:14.5px;line-height:1.7;margin:0 0 12px;color:#1E4D3B;"><strong>Diễn giải hiện đại Mây Hẹn:</strong> {{ $record['modern_guidance'] }}</p>
                <p style="font-size:14px;line-height:1.65;margin:0 0 14px;"><strong>Gợi ý vun đắp cho cặp đôi:</strong> {{ $record['couple_tip'] }}</p>
                <div style="padding:12px 14px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:10px;font-size:12.5px;color:#6E6259;">
                    <strong>Nguồn tham chiếu:</strong> {{ $record['source_label'] }}. {{ $record['variant_note'] }}
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
