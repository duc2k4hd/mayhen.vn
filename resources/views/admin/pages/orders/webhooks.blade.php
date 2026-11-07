@extends('admin.layouts.master')

@section('title', 'Nhật Ký Webhook Thanh Toán — Mây Hẹn')
@section('page_title', 'Nhật Ký Đối Soát Webhook Thanh Toán')

@section('breadcrumb_parent')
    <a href="{{ route('admin.orders.index') }}">Đơn Hàng</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/orders.css') }}">
@endpush

@section('content')
    <!-- Navigation Tabs: Orders vs Webhooks -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 8px;">
        <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>Đơn Hàng & Doanh Thu</span>
        </a>
        <a href="{{ route('admin.orders.webhooks') }}" class="mayhen_admin_btn" style="background-color: var(--mh-admin-charcoal); color: #FFF; border-color: var(--mh-admin-charcoal);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            <span>Nhật Ký Webhook Thanh Toán ({{ count($logs) }})</span>
        </a>
    </div>

    <!-- Webhook Logs Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="mayhen_admin_card_title">Lịch Sử Nhận Yêu Cầu Webhook Từ SePay / VietQR</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Hiển thị 100 sự kiện gần nhất</div>
        </div>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Thời Gian</th>
                        <th style="width: 110px; text-align: center;">Trạng Thái</th>
                        <th style="width: 150px;">Mã Đơn Hàng</th>
                        <th style="width: 130px; text-align: right;">Số Tiền</th>
                        <th>Thông Điệp & Nội Dung Giao Dịch</th>
                        <th style="width: 130px; text-align: right;">IP Gửi Đến</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $status = $log['status'] ?? 'info';
                            $time = isset($log['timestamp']) ? \Carbon\Carbon::parse($log['timestamp'])->format('d/m/Y H:i:s') : '—';
                        @endphp
                        <tr>
                            <!-- Timestamp -->
                            <td style="font-size: 0.8125rem; color: var(--mh-admin-muted); font-family: monospace;">
                                {{ $time }}
                            </td>

                            <!-- Status Badge -->
                            <td style="text-align: center;">
                                @if($status === 'success')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Thành công</span>
                                @elseif($status === 'warning')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Cảnh báo</span>
                                @elseif($status === 'info')
                                    <span class="mayhen_admin_badge" style="background-color: #E0F2FE; color: #0369A1;">Đã xử lý</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Lỗi</span>
                                @endif
                            </td>

                            <!-- Order Code -->
                            <td>
                                @if(!empty($log['order_code']))
                                    <span class="mayhen_admin_order_code_pill">
                                        {{ $log['order_code'] }}
                                    </span>
                                @else
                                    <span style="color: var(--mh-admin-muted); font-size: 0.8125rem;">— Không có mã —</span>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                @if(!empty($log['amount']))
                                    <span style="color: #1B5E20;">{{ number_format((int) $log['amount'], 0, ',', '.') }}đ</span>
                                @else
                                    <span style="color: var(--mh-admin-muted);">—</span>
                                @endif
                            </td>

                            <!-- Message & Content -->
                            <td>
                                <div style="font-weight: 600; font-size: 0.84375rem; color: var(--mh-admin-charcoal); margin-bottom: 2px;">
                                    {{ $log['message'] ?? 'Webhook request received' }}
                                </div>
                                @if(!empty($log['content']))
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); font-family: monospace;">
                                        Nội dung CK: "{{ $log['content'] }}"
                                    </div>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td style="text-align: right; font-size: 0.75rem; color: var(--mh-admin-muted); font-family: monospace;">
                                {{ $log['ip'] ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: var(--mh-admin-muted);">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; opacity: 0.5;">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                <div>Chưa có bản ghi nhật ký webhook nào được lưu lại.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

