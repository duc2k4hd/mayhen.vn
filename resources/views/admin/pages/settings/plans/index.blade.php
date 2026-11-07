@extends('admin.layouts.master')

@section('title', 'Quản Lý Gói Dịch Vụ & Bảng Giá — Mây Hẹn')
@section('page_title', 'Gói Dịch Vụ & Bảng Giá Nền Tảng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    <!-- Intro Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal);">
                    Bảng Giá & Các Gói Đăng Ký Mây Hẹn
                </h3>
                <p style="color: var(--mh-admin-muted); font-size: 0.84375rem; margin: 0;">
                    Quản lý giá bán, thời hạn lưu trữ và quyền lợi tính năng của các gói thiệp cưới công khai trên toàn hệ thống.
                </p>
            </div>
            <a href="{{ route('pricing') }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                </svg>
                <span>Xem Trang Bảng Giá Khách &nearr;</span>
            </a>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="mayhen_admin_plans_grid">
        @foreach($plans as $plan)
            <div class="mayhen_admin_plan_card {{ $plan->slug === 'plus' ? 'featured' : '' }}">
                @if($plan->slug === 'plus')
                    <span class="mayhen_admin_plan_badge">Gói Phổ Biến Nhất</span>
                @endif

                <div>
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span class="mayhen_admin_badge {{ $plan->is_active ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}" style="margin-bottom: 8px;">
                                {{ $plan->is_active ? 'Đang Mở Bán' : 'Tạm Dừng' }}
                            </span>
                            <h4 style="font-size: 1.2rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                {{ $plan->name }}
                            </h4>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); font-family: monospace;">
                                slug: {{ $plan->slug }}
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="mayhen_admin_plan_price">
                        @if($plan->price == 0)
                            Miễn Phí
                        @else
                            {{ number_format($plan->price, 0, ',', '.') }}<span style="font-size: 1rem; font-family: 'Inter', sans-serif; font-weight: 500;">đ</span>
                        @endif
                    </div>

                    <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 16px;">
                        Thời hạn lưu trữ: <strong>{{ $plan->duration_days }} ngày</strong> ({{ round($plan->duration_days / 30) }} tháng)
                    </div>

                    <!-- Revenue & Orders KPI -->
                    <div style="display: flex; gap: 12px; background-color: #FAF8F5; padding: 10px 14px; border-radius: var(--mh-admin-radius-sm); border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 16px;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); text-transform: uppercase;">Số Đơn Hàng</div>
                            <div style="font-weight: 700; color: var(--mh-admin-charcoal);">{{ number_format($plan->orders_count) }}</div>
                        </div>
                        <div style="flex: 1; border-left: 1px solid var(--mh-admin-border-subtle); padding-left: 12px;">
                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); text-transform: uppercase;">Doanh Thu Thu Được</div>
                            <div style="font-weight: 700; color: #1B5E20;">{{ number_format($plan->paid_revenue ?? 0, 0, ',', '.') }}đ</div>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div style="font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-muted); margin-bottom: 8px;">Quyền lợi tính năng:</div>
                    <ul class="mayhen_admin_plan_features">
                        @php
                            $features = is_array($plan->features) ? $plan->features : (json_decode($plan->features, true) ?? []);
                        @endphp
                        @forelse($features as $feat)
                            <li class="mayhen_admin_plan_feature_item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>{{ $feat }}</span>
                            </li>
                        @empty
                            <li style="color: var(--mh-admin-muted); font-size: 0.8125rem; font-style: italic;">Chưa cấu hình tính năng</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 8px; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--mh-admin-border-subtle);">
                    <a href="{{ route('admin.settings.plans.edit', $plan->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="flex: 1; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Chỉnh Sửa</span>
                    </a>

                    <form action="{{ route('admin.settings.plans.toggle_active', $plan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn {{ $plan->is_active ? 'mayhen_admin_btn_outline' : 'mayhen_admin_btn_primary' }}" title="{{ $plan->is_active ? 'Tạm dừng gói này' : 'Mở bán gói này' }}">
                            @if($plan->is_active)
                                <span>Tắt</span>
                            @else
                                <span>Bật</span>
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection

