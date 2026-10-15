@extends('client.layouts.master')

@section('title', 'Đăng Nhập Tài Khoản | ' . config('app.name'))
@section('meta_description', 'Đăng nhập vào tài khoản Mây Hẹn để thiết kế thiệp cưới online phong cách Quiet Luxury và quản lý khách mời.')
@section('robots', 'noindex, follow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/auth.css') }}">
@endpush

@section('content')
<div class="mayhen_auth_wrapper">
    <div class="mayhen_auth_container">
        <div class="mayhen_auth_card">
            <!-- Cột Trái: Welcome Hero Panel (Organic Curved Wave) -->
            <div class="mayhen_auth_hero">
                <!-- Các lớp sóng cong hữu cơ đan xen -->
                <div class="mayhen_auth_hero_wave_1"></div>
                <div class="mayhen_auth_hero_wave_2"></div>
                <div class="mayhen_auth_hero_wave_3"></div>
                <div class="mayhen_auth_hero_wave_4"></div>

                <!-- Logo Circle Badge -->
                <div class="mayhen_auth_hero_logo_circle">
                    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M16 3C16 3 13 8 9 10C5 12 3 16 3 18C3 24 9 28 16 29C23 28 29 24 29 18C29 16 27 12 23 10C19 8 16 3 16 3Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 11V23M11 16H21" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="mayhen_auth_hero_brand">{{ config('app.name') }}</div>

                <h2 class="mayhen_auth_hero_title">Welcome Back!</h2>
                <p class="mayhen_auth_hero_desc">
                    Để tiếp tục đồng hành cùng Mây Hẹn, vui lòng đăng nhập bằng thông tin của bạn
                </p>

                <a href="{{ route('register', ['redirect' => $redirect]) }}" class="mayhen_auth_pill_btn_outline">
                    Đăng Ký
                </a>

                <div class="mayhen_auth_hero_footer">
                    Mây Hẹn &bull; Quiet Luxury Wedding
                </div>
            </div>

            <!-- Cột Phải: Form Đăng Nhập -->
            <div class="mayhen_auth_form_panel">
                <!-- Điểm nhấn cong góc trên bên phải -->
                <div class="mayhen_auth_corner_accent"></div>
                <div class="mayhen_auth_corner_accent_inner"></div>

                <div class="mayhen_auth_form_header">
                    <h1 class="mayhen_auth_form_title">welcome</h1>
                    <p class="mayhen_auth_form_subtitle">Đăng nhập vào tài khoản để tiếp tục</p>
                </div>

                @if ($redirect && str_contains($redirect, 'tao-thiep'))
                    <div class="mayhen_auth_alert_info">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span>Mẫu thiệp này thuộc gói <strong>Plus / Pro VIP</strong>. Đăng nhập để bắt đầu chỉnh sửa thiệp.</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mayhen_auth_alert_error">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" class="mayhen_auth_form">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">

                    <div class="mayhen_auth_field_group">
                        <label for="email" class="mayhen_auth_sr_only">Địa chỉ Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="mayhen_auth_pill_input" 
                            value="{{ old('email') }}" 
                            placeholder="Email........" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="mayhen_auth_field_group">
                        <label for="password" class="mayhen_auth_sr_only">Mật khẩu</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="mayhen_auth_pill_input" 
                            placeholder="Mật khẩu........" 
                            required
                        >
                    </div>

                    <div class="mayhen_auth_forgot_row">
                        <label class="mayhen_auth_remember_label">
                            <input type="checkbox" name="remember" value="1" class="mayhen_auth_remember_checkbox">
                            <span>Ghi nhớ</span>
                        </label>
                        <a href="{{ route('contact') }}" class="mayhen_auth_forgot_link">
                            Quên mật khẩu?
                        </a>
                    </div>

                    <div class="mayhen_auth_btn_container">
                        <button type="submit" class="mayhen_auth_pill_btn_submit">
                            ĐĂNG NHẬP
                        </button>
                    </div>

                    <div class="mayhen_auth_switch_row">
                        <span>Chưa có tài khoản?</span>
                        <a href="{{ route('register', ['redirect' => $redirect]) }}" class="mayhen_auth_switch_link">Đăng ký ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
