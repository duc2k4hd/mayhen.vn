@extends('client.layouts.master')

@php
    $authBrandName = \App\Models\PlatformSetting::getBrandName();
@endphp

@section('title', 'Đăng Ký Tài Khoản | ' . $authBrandName)
@section('meta_description', 'Tạo tài khoản ' . $authBrandName . ' để bắt đầu thiết kế thiệp cưới online phong cách Quiet Luxury.')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/auth.css') }}">
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

                <div class="mayhen_auth_hero_brand">{{ $authBrandName }}</div>

                <h2 class="mayhen_auth_hero_title">Hello, Friend!</h2>
                <p class="mayhen_auth_hero_desc">
                    Tạo tài khoản để bắt đầu trải nghiệm thiết kế thiệp cưới online phong cách Quiet Luxury dành riêng cho bạn
                </p>

                <a href="{{ route('login', array_filter(['redirect' => $redirect])) }}" class="mayhen_auth_pill_btn_outline">
                    Đăng Nhập
                </a>

                <div class="mayhen_auth_hero_footer">
                    {{ $authBrandName }} &bull; Quiet Luxury Wedding
                </div>
            </div>

            <!-- Cột Phải: Form Đăng Ký -->
            <div class="mayhen_auth_form_panel">
                <!-- Điểm nhấn cong góc trên bên phải -->
                <div class="mayhen_auth_corner_accent"></div>
                <div class="mayhen_auth_corner_accent_inner"></div>

                <div class="mayhen_auth_form_header">
                    <h1 class="mayhen_auth_form_title">Đăng ký tài khoản</h1>
                    <p class="mayhen_auth_form_subtitle">Xác thực nhanh qua Google hoặc liên kết email bảo mật</p>
                </div>

                @if (!empty($isBanned) && $isBanned)
                    <div class="mayhen_auth_alert_error" style="background: #FDF4F4; border: 1px solid #E2A0A0; color: #8F2D35; border-radius: 12px; padding: 16px; margin-bottom: 24px; font-size: 0.875rem; line-height: 1.6;">
                        <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Giới hạn đăng ký trên thiết bị
                        </div>
                        <p style="margin: 0 0 10px 0;">
                            Thiết bị này đã vượt quá số lượng tài khoản cho phép (tối đa 5 tài khoản). Tạm thời không thể đăng ký mới trong vòng 7 ngày
                            @if (!empty($banExpiresAt))
                                (đến <strong>{{ $banExpiresAt->format('H:i d/m/Y') }}</strong>)
                            @endif.
                        </p>
                        <p style="margin: 0; font-size: 0.8125rem;">
                            Nếu bạn đã có tài khoản, vui lòng chuyển sang đăng nhập bên dưới. Nếu cần hỗ trợ thêm, vui lòng liên hệ đội ngũ chăm sóc khách hàng.
                        </p>
                        <div style="margin-top: 14px;">
                            <a href="{{ route('login', array_filter(['redirect' => $redirect])) }}" class="mayhen_auth_pill_btn_submit" style="display: inline-block; text-align: center; text-decoration: none; padding: 10px 24px; font-size: 0.8125rem;">
                                ĐĂNG NHẬP NGAY
                            </a>
                        </div>
                    </div>
                @else
                    @if (session('registration_link_sent'))
                        <div class="mayhen_auth_alert_success" role="status">
                            <div class="mayhen_auth_alert_success_title">Đã tiếp nhận yêu cầu xác thực</div>
                            <p>{{ session('status') }}</p>
                            @if (session('sent_email'))
                                <p class="mayhen_auth_alert_success_hint">Vui lòng kiểm tra hộp thư (kể cả mục Quảng cáo / Spam) của <strong>{{ session('sent_email') }}</strong>.</p>
                            @endif
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
                            <span>Đăng ký nhanh bằng Google</span>
                        </a>
                    </div>

                    <div class="mayhen_auth_divider">
                        <span>hoặc đăng ký qua email xác thực</span>
                    </div>

                    <form action="{{ route('register.submit') }}" method="POST" class="mayhen_auth_form">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                        <input type="hidden" name="_device_token" id="mayhen_device_token" value="{{ $machineId ?? '' }}">

                        <div class="mayhen_auth_field_group">
                            <label for="email" class="mayhen_auth_sr_only">Địa chỉ Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="mayhen_auth_pill_input"
                                value="{{ old('email') }}"
                                placeholder="Nhập địa chỉ email của bạn........"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mayhen_auth_security_note">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Hệ thống gửi <strong>liên kết xác thực dùng 1 lần</strong> (hiệu lực 24 giờ) để chống email rác. Sau khi nhấp liên kết, bạn mới nhập thông tin cơ bản và mật khẩu.</span>
                        </div>

                        <div class="mayhen_auth_btn_container">
                            <button type="submit" class="mayhen_auth_pill_btn_submit">
                                GỬI LIÊN KẾT XÁC THỰC
                            </button>
                        </div>

                        <div class="mayhen_auth_switch_row">
                            <span>Đã có tài khoản?</span>
                            <a href="{{ route('login', array_filter(['redirect' => $redirect])) }}" class="mayhen_auth_switch_link">Đăng nhập ngay</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        try {
            var currentMid = '{{ $machineId ?? '' }}';
            var storedMid = localStorage.getItem('mh_mid');
            if (!storedMid && currentMid) {
                localStorage.setItem('mh_mid', currentMid);
            } else if (storedMid && !currentMid) {
                var input = document.getElementById('mayhen_device_token');
                if (input) input.value = storedMid;
            }
        } catch (e) {}
    })();
</script>
@endpush
