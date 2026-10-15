@extends('client.layouts.master')

@section('title', 'Đăng Ký Tài Khoản | ' . config('app.name'))
@section('meta_description', 'Tạo tài khoản Mây Hẹn để bắt đầu thiết kế thiệp cưới online phong cách Quiet Luxury.')
@section('robots', 'noindex, follow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/auth.css') }}">
@endpush

@section('content')
    <div class="mayhen_auth_wrapper">
        <div class="mayhen_auth_card">
            <div class="mayhen_auth_header">
                <span class="mayhen_auth_badge">
                    <span>Đồng Hành Cùng Hạnh Phúc</span>
                </span>
                <h1 class="mayhen_auth_title">Đăng Ký Tài Khoản</h1>
                <p class="mayhen_auth_desc">Tạo thiệp cưới online trang nhã và lưu giữ kỷ niệm</p>
            </div>

            @if ($errors->any())
                <div class="mayhen_auth_alert_error">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ $redirect }}">

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label" for="name">Họ và tên *</label>
                    <input type="text" id="name" name="name" class="mayhen_client_form_input" value="{{ old('name') }}" placeholder="Ví dụ: Minh Quân" required autofocus>
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label" for="email">Địa chỉ Email *</label>
                    <input type="email" id="email" name="email" class="mayhen_client_form_input" value="{{ old('email') }}" placeholder="nhapemail@gmail.com" required>
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label" for="phone">Số điện thoại (Nhận thông báo RSVP)</label>
                    <input type="tel" id="phone" name="phone" class="mayhen_client_form_input" value="{{ old('phone') }}" placeholder="0912 345 678">
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label" for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" class="mayhen_client_form_input" placeholder="Tối thiểu 6 ký tự" required>
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label" for="password_confirmation">Xác nhận mật khẩu *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="mayhen_client_form_input" placeholder="Nhập lại mật khẩu" required>
                </div>

                <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary" style="width: 100%; height: 48px; font-size: 0.9375rem; margin-top: 10px;">
                    Đăng Ký & Bắt Đầu
                </button>
            </form>

            <div class="mayhen_auth_footer_links">
                <span>Đã có tài khoản?</span>
                <a href="{{ route('login', ['redirect' => $redirect]) }}" class="mayhen_auth_link">Đăng nhập ngay</a>
            </div>
        </div>
    </div>
@endsection
