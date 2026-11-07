@extends('client.layouts.master')

@section('title', 'Thẻ Tham Dự & Vị Trí Bàn Tiệc | ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkin.css') }}">
@endpush

@section('content')
<div class="mayhen_guest_pass_wrapper">
    <div class="mayhen_guest_pass_card">
        {{-- Card Header --}}
        <div class="mayhen_guest_pass_emblem">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
            </svg>
        </div>

        <span class="mayhen_guest_pass_badge">MÂY HẸN WEDDING VIP PASS</span>
        <h1 class="mayhen_guest_pass_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
        <p class="mayhen_guest_pass_date">
            {{ $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d \t\h\á\n\g m, Y') : 'Hôn Lễ Trọng Đại' }}
        </p>

        <div class="mayhen_guest_pass_divider"></div>

        {{-- Guest Personal Info --}}
        <div class="mayhen_guest_pass_guest">
            <span class="mayhen_guest_pass_salutation">{{ $guest->salutation ?: 'Trân trọng kính mời' }}</span>
            <h2 class="mayhen_guest_pass_name">{{ $guest->name }}</h2>
            <div class="mayhen_guest_pass_group">
                {{ $guest->group_name ?: 'Khách Quý' }} • {{ $guest->expected_attendees }} khách
            </div>
        </div>

        {{-- Prominent Seating Table Badge --}}
        <div class="mayhen_guest_pass_table_box">
            <span class="table_box_label">VỊ TRÍ BÀN TIỆC DÀNH CHO BẠN</span>
            @if(!empty($guest->table_name))
                <div class="table_box_number">{{ $guest->table_name }}</div>
            @else
                <div class="table_box_pending">Lễ tân sẽ hướng dẫn vị trí bàn khi quý khách tới sảnh tiệc</div>
            @endif
        </div>

        {{-- QR Code Section --}}
        <div class="mayhen_guest_pass_qr_box">
            <div class="mayhen_guest_pass_qr_code">
                {!! \App\Services\QrCodeService::svg(route('checkin.direct', ['token' => $guest->guest_token]), 200, '#1F1D1A', '#FAF8F5') !!}
            </div>
            <p class="mayhen_guest_pass_qr_hint">
                Vui lòng xuất trình mã QR này tại bàn đón tiếp để lễ tân hướng dẫn vị trí bàn tiệc nhanh chóng nhất.
            </p>
        </div>

        {{-- Status badge --}}
        @if($guest->checked_in_at)
            <div class="mayhen_guest_pass_status mayhen_guest_pass_status--checked">
                ✓ Đã đón tiếp lúc {{ $guest->checked_in_at->format('H:i d/m/Y') }}
            </div>
        @endif

        {{-- Action buttons --}}
        <div class="mayhen_guest_pass_actions">
            <a href="{{ route('invitation.show', ['slug' => $invitation->slug, 'token' => $guest->guest_token]) }}" class="mayhen_checkin_btn mayhen_checkin_btn--primary mayhen_checkin_btn--full">
                💌 Mở Thiệp Cưới Điện Tử
            </a>
            <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--text" onclick="window.print()">
                🖨️ Lưu hoặc In Thẻ VIP
            </button>
        </div>
    </div>
</div>
@endsection
