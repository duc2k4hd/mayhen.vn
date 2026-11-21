@extends('client.layouts.master')

@php
    $authBrandName = \App\Models\PlatformSetting::getBrandName();
    $isGoogleMode = ($mode ?? 'email') === 'google';
@endphp

@section('title', 'Hoàn Tất Thông Tin Tài Khoản | ' . $authBrandName)
@section('meta_description', 'Nhập thông tin cơ bản và thiết lập mật khẩu đăng nhập cho tài khoản ' . $authBrandName . '.')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/auth.css') }}">
@endpush

@section('content')
<div class="mayhen_auth_wrapper">
    <div class="mayhen_auth_container">
        <div class="mayhen_auth_card mayhen_auth_card_register">
            <!-- Cột Trái: Welcome Hero Panel -->
            <div class="mayhen_auth_hero">
                <div class="mayhen_auth_hero_wave_1"></div>
                <div class="mayhen_auth_hero_wave_2"></div>
                <div class="mayhen_auth_hero_wave_3"></div>
                <div class="mayhen_auth_hero_wave_4"></div>

                <div class="mayhen_auth_hero_logo_circle">
                    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M16 3C16 3 13 8 9 10C5 12 3 16 3 18C3 24 9 28 16 29C23 28 29 24 29 18C29 16 27 12 23 10C19 8 16 3 16 3Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 11V23M11 16H21" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="mayhen_auth_hero_brand">{{ $authBrandName }}</div>

                <h2 class="mayhen_auth_hero_title">Bước Cuối Cùng!</h2>
                <p class="mayhen_auth_hero_desc">
                    {{ $isGoogleMode
                        ? 'Tài khoản Google của bạn đã được xác thực. Hãy hoàn tất họ tên và mật khẩu để lần sau đăng nhập linh hoạt bằng Google hoặc Email & mật khẩu.'
                        : 'Email của bạn đã được xác thực an toàn. Hãy nhập thông tin cơ bản và mật khẩu để hoàn tất khởi tạo tài khoản.' }}
                </p>

                <div class="mayhen_auth_hero_footer">
                    {{ $authBrandName }} &bull; Quiet Luxury Wedding
                </div>
            </div>

            <!-- Cột Phải: Form Hoàn Tất Thông Tin & Mật Khẩu -->
            <div class="mayhen_auth_form_panel">
                <div class="mayhen_auth_corner_accent"></div>
                <div class="mayhen_auth_corner_accent_inner"></div>

                <div class="mayhen_auth_form_header">
                    <h1 class="mayhen_auth_form_title">Hoàn tất hồ sơ</h1>
                    <p class="mayhen_auth_form_subtitle">Nhập thông tin cơ bản và mật khẩu đăng nhập cho lần sau</p>
                </div>

                <div class="mayhen_auth_verified_email_box">
                    <span class="mayhen_auth_verified_email_badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ $isGoogleMode ? 'Email Google đã xác thực' : 'Email đã xác thực' }}
                    </span>
                    <div class="mayhen_auth_verified_email_value">{{ $verifiedEmail }}</div>
                </div>

                @if ($errors->any())
                    <div class="mayhen_auth_alert_error">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ $formAction }}" method="POST" class="mayhen_auth_form">
                    @csrf

                    <div class="mayhen_auth_field_group">
                        <label for="name" class="mayhen_auth_sr_only">Họ và tên</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="mayhen_auth_pill_input"
                            value="{{ old('name', $defaultName ?? '') }}"
                            placeholder="Họ và tên của bạn........"
                            required
                            autofocus
                        >
                    </div>

                    <div class="mayhen_auth_field_group">
                        <label for="phone" class="mayhen_auth_sr_only">Số điện thoại (không bắt buộc)</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="mayhen_auth_pill_input"
                            value="{{ old('phone') }}"
                            placeholder="Số điện thoại (tùy chọn)........"
                        >
                    </div>

                    <div class="mayhen_auth_field_group">
                        <label for="password" class="mayhen_auth_sr_only">Mật khẩu đăng nhập</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="mayhen_auth_pill_input"
                            placeholder="Mật khẩu (tối thiểu 8 ký tự, gồm chữ & số)........"
                            autocomplete="new-password"
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
                            placeholder="Nhập lại mật khẩu........"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="mayhen_auth_security_note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Sau khi hoàn tất, lần sau bạn có thể đăng nhập bằng <strong>Google</strong> hoặc <strong>Email &amp; Mật khẩu</strong> đều vào chung một tài khoản.</span>
                    </div>

                    <div class="mayhen_auth_btn_container">
                        <button type="submit" class="mayhen_auth_pill_btn_submit">
                            HOÀN TẤT &amp; VÀO TÀI KHOẢN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
