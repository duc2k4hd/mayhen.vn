@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $core = $calc['core_numbers'] ?? [];
    $lp = $core['life_path'] ?? ['display' => '—'];
@endphp

@section('title', 'Thẻ Dấu Ấn Con Số Ẩn Danh')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@section('content')
<div class="mayhen_numerology_page">
    <section class="mayhen_numerology_hero" style="padding: 56px 0;">
        <div class="mayhen_numerology_container" style="max-width: 680px;">
            <div class="mayhen_numerology_love_banner">
                <span class="mayhen_numerology_eyebrow">Mây Hẹn · Thẻ Dấu Ấn Con Số (Đã Ẩn Ngày Sinh Đầy Đủ)</span>
                <div class="mayhen_numerology_giant_number" style="margin: 14px 0 8px;">{{ $lp['display'] }}</div>
                <h1 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 26px; margin: 0 0 12px;">
                    {{ $interpretation['headline'] ?? 'Chỉ Số Đường Đời ' . $lp['display'] }}
                </h1>
                <div class="mayhen_md_prose" style="font-size: 15px; line-height: 1.72; color: #4E463F; margin: 0 0 18px;">
                    {!! safe_ai_markdown_html($interpretation['summary'] ?? '') !!}
                </div>
                @if(! empty($interpretation['reflection_question']))
                    <p style="font-style: italic; color: #1E4D3B; font-size: 14px; margin: 0 0 22px;">
                        “{{ $interpretation['reflection_question'] }}”
                    </p>
                @endif
                <a href="{{ route('numerology.index') }}" class="mayhen_numerology_btn_primary">Tra Cứu Thần Số Học Của Riêng Bạn (Miễn Phí)</a>
            </div>
        </div>
    </section>
</div>
@endsection
