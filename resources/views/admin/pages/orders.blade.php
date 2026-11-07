@extends('admin.layouts.master')

@section('title', 'Quản Lý Đơn Hàng — Mây Hẹn')
@section('page_title', 'Danh Sách Đơn Hàng & Thanh Toán')

@section('content')
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Tất Cả Đơn Hàng ({{ $orders->total() }} đơn)</span>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Gói Dịch Vụ</th>
                        <th>Số Tiền</th>
                        <th>Phương Thức</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><code>{{ $order->order_code }}</code></td>
                            <td>
                                <strong>{{ $order->user->name }}</strong><br>
                                <span style="font-size: 0.75rem; color: #7A756E;">{{ $order->user->email }}</span>
                            </td>
                            <td>{{ $order->plan->name }}</td>
                            <td><strong style="color: #1B5E20;">{{ number_format($order->amount, 0, ',', '.') }}đ</strong></td>
                            <td><span class="mayhen_admin_badge mayhen_admin_badge_muted">{{ strtoupper($order->payment_method) }}</span></td>
                            <td>
                                @if ($order->isPaid())
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Thanh Toán</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ Thanh Toán</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #7A756E; padding: 24px;">
                                Chưa có đơn hàng nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
