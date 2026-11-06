@extends('admin.layouts.master')

@section('title', 'Quản Lý Đơn Hàng — Mây Hẹn')
@section('page_title', 'Danh Sách Đơn Hàng & Thanh Toán')

@section('content')
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_title">
            <span>Tất Cả Đơn Hàng ({{ $orders->total() }} đơn)</span>
        </div>

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
                            <span style="font-size: 0.75rem; color: #6C757D;">{{ $order->user->email }}</span>
                        </td>
                        <td>{{ $order->plan->name }}</td>
                        <td><strong>{{ number_format($order->amount, 0, ',', '.') }}đ</strong></td>
                        <td><span style="text-transform: uppercase; font-size: 0.75rem;">{{ $order->payment_method }}</span></td>
                        <td>
                            @if ($order->isPaid())
                                <span class="mayhen_admin_badge_paid">Đã Thanh Toán</span>
                            @else
                                <span class="mayhen_admin_badge_pending">Chờ Thanh Toán</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6C757D; padding: 24px;">
                            Chưa có đơn hàng nào trong hệ thống.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $orders->links() }}
        </div>
    </div>
@endsection
