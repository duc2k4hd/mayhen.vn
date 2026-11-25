@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $summary = $chart['summary'] ?? [];
    $menhStars = $summary['menh_major_stars'] ?? [];
    $menhStarsText = ! empty($menhStars)
        ? implode(' • ', array_map(fn ($s) => ($s['name'] ?? '').' ('.($s['brightness'] ?? '').')', $menhStars))
        : 'Vô Chính Diệu';
@endphp

@section('title', 'Thẻ Thiên Mệnh Ẩn Danh')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@section('content')
<div class="mayhen_tuvi_page">
    <section class="mayhen_tuvi_celestial_hero" style="padding: 54px 0 68px;">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container" style="max-width: 720px;">
            <div class="mayhen_tuvi_card" style="background: rgba(252, 251, 249, 0.98); border: 2px solid #CFA860; padding: 32px 28px; text-align: center;">
                <span class="mayhen_tuvi_badge">✦ Thẻ Thiên Mệnh Ẩn Danh • Đã Ẩn Tên &amp; Ngày Giờ Sinh</span>
                <h1 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: clamp(24px, 3vw, 32px); color: #85182A; margin: 8px 0 12px;">
                    {{ $interpretation['headline'] ?? 'Lá Số Thiên Mệnh Mây Hẹn' }}
                </h1>
                <p style="font-size: 15px; line-height: 1.7; color: #3D352E; margin-bottom: 18px;">
                    {{ $interpretation['core_message'] ?? '' }}
                </p>

                <div class="mayhen_tuvi_center_meta" style="margin-bottom: 18px;">
                    <div><strong>Bản Mệnh:</strong> {{ $summary['nap_am']['name'] ?? ($chart['birth']['nap_am']['name'] ?? '—') }}</div>
                    <div><strong>Ngũ Hành Cục:</strong> {{ $summary['cuc']['name'] ?? '—' }}</div>
                    <div><strong>Chính tinh Mệnh:</strong> {{ $menhStarsText }}</div>
                    <div><strong>Thân cư:</strong> {{ $summary['than_palace_name'] ?? '—' }}</div>
                </div>

                @if(! empty($interpretation['reflection_question']))
                    <p style="font-style: italic; color: #85182A; font-size: 14px; margin-bottom: 20px;">
                        “{{ $interpretation['reflection_question'] }}”
                    </p>
                @endif

                <div class="mayhen_tuvi_btn_row" style="justify-content: center;">
                    <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_primary">✦ Lập Lá Số Thiên Mệnh Của Riêng Bạn (Miễn Phí)</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
