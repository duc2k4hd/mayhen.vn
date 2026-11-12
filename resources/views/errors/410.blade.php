@extends('client.layouts.master')

@section('title', '410 — Thiệp Đã Khép Lại Thời Gian Lưu Trữ | ' . config('app.name', 'Mây Hẹn'))
@section('meta_description', 'Thiệp cưới trực tuyến này đã hoàn thành sứ mệnh và khép lại thời gian lưu trữ trực tuyến.')

@section('content')
<div class="mayhen_client_layout_container" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1.5rem; text-align: center;">
    <div style="max-width: 540px; margin: 0 auto;">
        <div style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: clamp(4.5rem, 12vw, 7.5rem); font-weight: 300; line-height: 1; color: var(--mayhen-color-accent, #C5A880); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
            410
        </div>
        <div style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.25em; color: var(--mayhen-color-text-muted, #7A756D); margin-bottom: 1.5rem;">
            Đã Khép Lại Lưu Trữ
        </div>
        <h1 style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: clamp(1.75rem, 4vw, 2.25rem); font-weight: 400; color: var(--mayhen-color-text-main, #1F1D1A); margin-bottom: 1rem; line-height: 1.3;">
            Khoảnh khắc trọn vẹn yêu thương
        </h1>
        <p style="font-size: 0.9375rem; color: var(--mayhen-color-text-muted, #7A756D); line-height: 1.6; margin-bottom: 2.5rem;">
            {{ $exception->getMessage() ?: 'Thiệp cưới trực tuyến này đã hoàn thành trọn vẹn sứ mệnh kết nối và hiện đã hết hạn lưu trữ trực tuyến.' }}
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('home') }}" class="mayhen_client_button_base mayhen_client_button_primary" style="padding: 12px 28px; text-decoration: none;">
                <span>Về Trang Chủ Mây Hẹn</span>
            </a>
            <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_secondary" style="padding: 12px 28px; text-decoration: none;">
                <span>Khám Phá Mẫu Thiệp Mới</span>
            </a>
        </div>
    </div>
</div>
@endsection

