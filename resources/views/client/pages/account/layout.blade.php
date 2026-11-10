@extends('client.layouts.master')

@section('page_title', ($pageTitle ?? 'Tài Khoản') . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}?v={{ file_exists(public_path('assets/client/css/pages/account.css')) ? filemtime(public_path('assets/client/css/pages/account.css')) : '1.0' }}">
@endpush

@section('content')
<div class="mayhen_account_container">
    <!-- Breadcrumbs -->
    <div class="mayhen_account_breadcrumbs">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <a href="{{ route('account.dashboard') }}">Tài khoản</a>
        @hasSection('account_breadcrumb')
            <span class="mayhen_account_breadcrumbs_sep">/</span>
            <span class="mayhen_account_breadcrumbs_current">@yield('account_breadcrumb')</span>
        @endif
    </div>

    <!-- User Header Card -->
    <div class="mayhen_account_header_card">
        <div class="mayhen_account_user_profile">
            @php
                $userName = auth()->user()->name ?? 'Cặp đôi Mây Hẹn';
                $nameParts = preg_split('/\s+/', trim($userName));
                $lastName = end($nameParts) ?: $userName;
                $userInitial = mb_strtoupper(mb_substr($lastName, 0, 1, 'UTF-8'), 'UTF-8');
            @endphp
            <div class="mayhen_account_avatar" aria-hidden="true">{{ $userInitial }}</div>
            <div class="mayhen_account_user_details">
                <span class="mayhen_account_greeting">Không Gian Cặp Đôi</span>
                <h1 class="mayhen_account_username">{{ $userName }}</h1>
                <div class="mayhen_account_meta_info">
                    <span>{{ auth()->user()->email ?? auth()->user()->phone }}</span>
                    @if(auth()->user()->isAdmin())
                        <span class="mayhen_account_badge">Quản trị viên</span>
                    @elseif(auth()->user()->isPartner())
                        <span class="mayhen_account_badge">Đối tác Studio</span>
                    @else
                        <span class="mayhen_account_badge">Cặp đôi Mây Hẹn</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="mayhen_account_header_actions">
            <a href="{{ route('invitation.create') }}" class="mayhen_account_btn mayhen_account_btn_gold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tạo Thiệp Cưới Mới</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <nav class="mayhen_account_nav_tabs" aria-label="Điều hướng tài khoản">
        <a href="{{ route('account.dashboard') }}" class="mayhen_account_tab_link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}">
            <svg class="mayhen_account_tab_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="9" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="5" rx="1.5"></rect>
                <rect x="14" y="12" width="7" height="9" rx="1.5"></rect>
                <rect x="3" y="16" width="7" height="5" rx="1.5"></rect>
            </svg>
            <span>Tổng Quan</span>
        </a>
        <a href="{{ route('account.invitations') }}" class="mayhen_account_tab_link {{ request()->routeIs('account.invitations*') ? 'active' : '' }}">
            <svg class="mayhen_account_tab_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <span>Thiệp Cưới Của Tôi</span>
        </a>
        <a href="{{ route('account.orders') }}" class="mayhen_account_tab_link {{ request()->routeIs('account.orders*') ? 'active' : '' }}">
            <svg class="mayhen_account_tab_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            <span>Đơn Hàng & Gói Cước</span>
        </a>
        <a href="{{ route('account.profile') }}" class="mayhen_account_tab_link {{ request()->routeIs('account.profile*') ? 'active' : '' }}">
            <svg class="mayhen_account_tab_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Hồ Sơ & Bảo Mật</span>
        </a>
        <a href="{{ route('account.support.index') }}" class="mayhen_account_tab_link {{ request()->routeIs('account.support*') ? 'active' : '' }}">
            <svg class="mayhen_account_tab_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <span>Hỗ Trợ & Yêu Cầu</span>
        </a>
    </nav>

    <!-- Session Feedback Alerts -->
    @if(session('success'))
        <div class="mayhen_account_alert mayhen_account_alert_success">
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mayhen_account_alert mayhen_account_alert_danger">
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_account_alert mayhen_account_alert_danger">
            <div>
                <strong>Có lỗi xảy ra, vui lòng kiểm tra lại:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Account Tab Content Area -->
    @yield('account_content')
</div>

{{-- QR Code & Print Card Modal --}}
@include('client.components.qr-modal')

{{-- Wedding Readiness Checklist Modal (TASK-VAL-12) --}}
@include('client.components.readiness-modal')
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/account-qr.js') }}"></script>
    <script type="module" src="{{ asset('assets/client/js/pages/account-readiness.js') }}"></script>
@endpush


