@extends('client.layouts.master')

@php
    $authBrandName = \App\Models\PlatformSetting::getBrandName();
@endphp

@section('title', 'Đăng Nhập Tài Khoản')
@section('meta_description', 'Đăng nhập vào tài khoản ' . $authBrandName . ' để thiết kế thiệp cưới online phong cách Quiet Luxury và quản lý khách mời.')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/auth.css') }}">
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

                <div class="mayhen_auth_hero_brand">{{ $authBrandName }}</div>

                <h2 class="mayhen_auth_hero_title">Welcome Back!</h2>
                <p class="mayhen_auth_hero_desc">
                    Để tiếp tục đồng hành cùng {{ $authBrandName }}, vui lòng đăng nhập bằng thông tin của bạn
                </p>

                <a href="{{ route('register', array_filter(['redirect' => $redirect])) }}" class="mayhen_auth_pill_btn_outline">
                    Đăng Ký
                </a>

                <div class="mayhen_auth_hero_footer">
                    {{ $authBrandName }} &bull; Quiet Luxury Wedding
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
                        <span>Mẫu thiệp này thuộc gói <strong>Premium / Plus / Pro VIP</strong>. Đăng nhập để bắt đầu tạo và chỉnh sửa thiệp.</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mayhen_auth_alert_error">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="mayhen_auth_social_wrap">
                    <a href="{{ route('auth.google.redirect', array_filter(['redirect' => $redirect])) }}" class="mayhen_auth_google_btn">
                        <svg class="mayhen_auth_google_icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.2-2.27H12v4.3h6.45c-.28 1.48-1.12 2.74-2.39 3.58v2.98h3.87c2.26-2.08 3.56-5.15 3.56-8.59z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.91l-3.87-2.98c-1.07.72-2.45 1.14-4.06 1.14-3.12 0-5.77-2.11-6.72-4.95H1.28v3.08C3.25 21.3 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.3A7.22 7.22 0 0 1 4.9 12c0-.8.14-1.57.38-2.3V6.62H1.28A11.99 11.99 0 0 0 0 12c0 1.94.46 3.77 1.28 5.38l4-3.08z"/>
                            <path fill="#EA4335" d="M12 4.75c1.76 0 3.34.61 4.59 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.28 6.62l4 3.08c.95-2.84 3.6-4.95 6.72-4.95z"/>
                        </svg>
                        <span>Tiếp tục bằng Google</span>
                    </a>
                </div>

                <div class="mayhen_auth_divider">
                    <span>hoặc đăng nhập bằng email</span>
                </div>

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
                        <a href="{{ route('password.request') }}" class="mayhen_auth_forgot_link">
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
                        <a href="{{ route('register', array_filter(['redirect' => $redirect])) }}" class="mayhen_auth_switch_link">Đăng ký ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
