@extends('client.layouts.master')

@section('title', 'Đăng Nhập Tài Khoản | ' . config('app.name'))
@section('meta_description', 'Đăng nhập vào tài khoản Mây Hẹn để tạo và quản lý thiệp cưới online cao cấp.')
@section('robots', 'noindex, follow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/auth.css') }}">
@endpush

@section('content')
    <div class="mayhen_auth_wrapper">
        <div class="mayhen_auth_card">
            <div class="mayhen_auth_header">
                <span class="mayhen_auth_badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Trải Nghiệm Mẫu Thiệp Cao Cấp</span>
                </span>
                <h1 class="mayhen_auth_title">Đăng Nhập</h1>
                <p class="mayhen_auth_desc">Chào mừng bạn quay trở lại với Mây Hẹn</p>
            </div>

            @if ($redirect && str_contains($redirect, 'tao-thiep'))
                <div class="mayhen_auth_alert_info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Mẫu thiệp này thuộc gói <strong>Plus / Pro VIP</strong>. Vui lòng đăng nhập để bắt đầu chỉnh sửa và tạo thiệp cưới.</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mayhen_auth_alert_error">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ $redirect }}">

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label" for="email">Địa chỉ Email</label>
                    <input type="email" id="email" name="email" class="mayhen_client_form_input" value="{{ old('email') }}" placeholder="nhapemail@gmail.com" required autofocus>
                </div>

                <div class="mayhen_client_form_group">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                        <label class="mayhen_client_form_label" for="password" style="margin-bottom: 0;">Mật khẩu</label>
                    </div>
                    <input type="password" id="password" name="password" class="mayhen_client_form_input" placeholder="••••••••" required>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--mayhen-color-text-main); cursor: pointer;">
                        <input type="checkbox" name="remember" value="1" style="accent-color: var(--mayhen-color-primary);">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary" style="width: 100%; height: 48px; font-size: 0.9375rem;">
                    Đăng Nhập
                </button>
            </form>

            <div class="mayhen_auth_footer_links">
                <span>Chưa có tài khoản?</span>
                <a href="{{ route('register', ['redirect' => $redirect]) }}" class="mayhen_auth_link">Đăng ký tài khoản mới</a>
            </div>
        </div>
    </div>
@endsection
