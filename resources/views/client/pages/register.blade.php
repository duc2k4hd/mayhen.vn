@extends('client.layouts.master')

@section('title', 'Đăng Ký Tài Khoản | ' . config('app.name'))
@section('meta_description', 'Tạo tài khoản Mây Hẹn để bắt đầu thiết kế thiệp cưới online phong cách Quiet Luxury.')
@section('robots', 'noindex, follow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/auth.css') }}">
@endpush

@section('content')
<div class="mayhen_auth_wrapper">
    <div class="mayhen_auth_container">
        <div class="mayhen_auth_card mayhen_auth_card_register">
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

                <h2 class="mayhen_auth_hero_title">Hello, Friend!</h2>
                <p class="mayhen_auth_hero_desc">
                    Tạo tài khoản để bắt đầu trải nghiệm thiết kế thiệp cưới online phong cách Quiet Luxury dành riêng cho bạn
                </p>

                <a href="{{ route('login', ['redirect' => $redirect]) }}" class="mayhen_auth_pill_btn_outline">
                    Đăng Nhập
                </a>

                <div class="mayhen_auth_hero_footer">
                    Mây Hẹn &bull; Quiet Luxury Wedding
                </div>
            </div>

            <!-- Cột Phải: Form Đăng Ký -->
            <div class="mayhen_auth_form_panel">
                <!-- Điểm nhấn cong góc trên bên phải -->
                <div class="mayhen_auth_corner_accent"></div>
                <div class="mayhen_auth_corner_accent_inner"></div>

                <div class="mayhen_auth_form_header">
                    <h1 class="mayhen_auth_form_title">Đăng ký ngay để sử dụng ngay</h1>
                    <p class="mayhen_auth_form_subtitle">Khởi tạo tài khoản để trải nghiệm ngay</p>
                </div>

                @if ($errors->any())
                    <div class="mayhen_auth_alert_error">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('register.submit') }}" method="POST" class="mayhen_auth_form">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">

                    <div class="mayhen_auth_field_group">
                        <label for="name" class="mayhen_auth_sr_only">Họ và tên</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="mayhen_auth_pill_input" 
                            value="{{ old('name') }}" 
                            placeholder="Họ và tên........" 
                            required 
                            autofocus
                        >
                    </div>

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
                        >
                    </div>

                    <div class="mayhen_auth_field_group">
                        <label for="phone" class="mayhen_auth_sr_only">Số điện thoại</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="mayhen_auth_pill_input" 
                            value="{{ old('phone') }}" 
                            placeholder="Số điện thoại........"
                        >
                    </div>

                    <div class="mayhen_auth_field_group">
                        <label for="password" class="mayhen_auth_sr_only">Mật khẩu</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="mayhen_auth_pill_input" 
                            placeholder="Mật khẩu (tối thiểu 6 ký tự)........" 
                            required
                        >
                    </div>

                    <div class="mayhen_auth_field_group">
                        <label for="password_confirmation" class="mayhen_auth_sr_only">Xác nhận mật khẩu</label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="mayhen_auth_pill_input" 
                            placeholder="Xác nhận mật khẩu........" 
                            required
                        >
                    </div>

                    <div class="mayhen_auth_btn_container">
                        <button type="submit" class="mayhen_auth_pill_btn_submit">
                            ĐĂNG KÝ
                        </button>
                    </div>

                    <div class="mayhen_auth_switch_row">
                        <span>Đã có tài khoản?</span>
                        <a href="{{ route('login', ['redirect' => $redirect]) }}" class="mayhen_auth_switch_link">Đăng nhập ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
