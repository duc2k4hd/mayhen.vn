@extends('client.layouts.master')

@section('title', 'Cổng Tiếp Tân Điểm Danh | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkin.css') }}">
@endpush

@section('content')
<div class="mayhen_checkin_pin_wrapper">
    <div class="mayhen_checkin_pin_card">
        <div class="mayhen_checkin_pin_emblem">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>

        <span class="mayhen_checkin_badge">TIẾP TÂN & ĐIỀU PHỐI</span>
        <h1 class="mayhen_checkin_pin_title">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
        <p class="mayhen_checkin_pin_subtitle">Vui lòng nhập mã PIN tiếp tân 6 số để mở Cổng Quét Mã Điểm Danh & Hướng Dẫn Bàn Tiệc.</p>

        @if(session('error'))
            <div class="mayhen_checkin_alert mayhen_checkin_alert--error">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('checkin.verifyPin', $invitation->slug) }}" method="POST" class="mayhen_checkin_pin_form">
            @csrf
            <div class="mayhen_checkin_pin_input_wrap">
                <input 
                    type="password" 
                    name="pin" 
                    id="mayhen_pin_input" 
                    inputmode="numeric" 
                    pattern="[0-9]*" 
                    maxlength="10" 
                    placeholder="Nhập mã PIN" 
                    required 
                    autofocus 
                    class="mayhen_checkin_pin_input"
                >
            </div>

            <button type="submit" class="mayhen_checkin_btn mayhen_checkin_btn--primary">
                Mở Cổng Tiếp Tân
            </button>
        </form>

        <div class="mayhen_checkin_pin_footer">
            <p>Mã PIN được cung cấp bởi Cô dâu & Chú rể.</p>
            @if(auth()->check() && (int) auth()->id() === (int) $wedding->user_id)
                <a href="{{ route('coordination.index', $invitation->id) }}" class="mayhen_checkin_pin_link">
                    → Đi tới Bảng Điều Khiển Ngày Cưới
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

