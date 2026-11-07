@extends('client.pages.account.layout')

@php
    $pageTitle = 'Hồ Sơ & Bảo Mật';
@endphp

@section('account_breadcrumb', 'Hồ Sơ & Bảo Mật')

@section('account_content')
    <div class="mayhen_account_profile_grid">
        <!-- Form Thông Tin Hồ Sơ -->
        <div class="mayhen_account_form_card">
            <h2 class="mayhen_account_form_title">Thông Tin Cá Nhân</h2>

            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf

                <div class="mayhen_account_field">
                    <label for="name" class="mayhen_account_label">Họ và tên của bạn</label>
                    <input type="text" id="name" name="name" class="mayhen_account_input" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="mayhen_account_field_error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mayhen_account_field">
                    <label for="email" class="mayhen_account_label">Địa chỉ Email</label>
                    <input type="email" id="email" name="email" class="mayhen_account_input" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="mayhen_account_field_error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mayhen_account_field">
                    <label for="phone" class="mayhen_account_label">Số điện thoại liên hệ</label>
                    <input type="tel" id="phone" name="phone" class="mayhen_account_input" value="{{ old('phone', $user->phone) }}" placeholder="Ví dụ: 0912345678">
                    @error('phone')
                        <span class="mayhen_account_field_error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mayhen_account_field">
                    <label class="mayhen_account_label">Vai trò tài khoản (Phân quyền)</label>
                    <input type="text" class="mayhen_account_input" value="{{ $user->role_label }} — Nền tảng Mây Hẹn" disabled style="background: #FAF8F5; cursor: not-allowed; color: #7A726A; font-weight: 500;">
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                        <span>Lưu Thay Đổi Hồ Sơ</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Đổi Mật Khẩu -->
        <div class="mayhen_account_form_card">
            <h2 class="mayhen_account_form_title">Bảo Mật & Đổi Mật Khẩu</h2>

            <form action="{{ route('account.password.update') }}" method="POST">
                @csrf

                <div class="mayhen_account_field">
                    <label for="current_password" class="mayhen_account_label">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" class="mayhen_account_input" required autocomplete="current-password">
                    @error('current_password')
                        <span class="mayhen_account_field_error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mayhen_account_field">
                    <label for="password" class="mayhen_account_label">Mật khẩu mới (Tối thiểu 8 ký tự)</label>
                    <input type="password" id="password" name="password" class="mayhen_account_input" required autocomplete="new-password">
                    @error('password')
                        <span class="mayhen_account_field_error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mayhen_account_field">
                    <label for="password_confirmation" class="mayhen_account_label">Xác nhận lại mật khẩu mới</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="mayhen_account_input" required autocomplete="new-password">
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline">
                        <span>Cập Nhật Mật Khẩu</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

