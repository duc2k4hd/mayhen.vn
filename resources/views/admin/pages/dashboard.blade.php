@extends('admin.layouts.master')

@section('title', 'Admin Dashboard — Mây Hẹn')
@section('page_title', 'Tổng Quan Vận Hành & Doanh Thu')

@section('content')
    <!-- 4 Key KPI Metrics -->
    <div class="mayhen_admin_stats_grid">
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_num" style="color: #0F5132;">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
            <div class="mayhen_admin_stat_label">Doanh Thu Đã Thu</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_num">{{ $paidOrdersCount }}</div>
            <div class="mayhen_admin_stat_label">Đơn Hàng Thành Công</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_num">{{ $publishedInvitations }} / {{ $totalInvitations }}</div>
            <div class="mayhen_admin_stat_label">Thiệp Xuất Bản / Tổng Thiệp</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_num">{{ $totalUsers }}</div>
            <div class="mayhen_admin_stat_label">Tổng Người Dùng</div>
        </div>
    </div>

    <!-- Templates Popularity Grid -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_title">
            <span>Hiệu Suất Mẫu Thiệp Cưới</span>
            <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn_sm">Quản lý kho mẫu &rarr;</a>
        </div>

        <table class="mayhen_admin_table">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên Mẫu</th>
                    <th>Phong Cách</th>
                    <th>Số Thiệp Đã Tạo</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($templates as $tpl)
                    <tr>
                        <td>
                            <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                        </td>
                        <td><strong>{{ $tpl->name }}</strong></td>
                        <td>{{ $tpl->category_label }}</td>
                        <td><strong>{{ $tpl->invitations_count }}</strong> thiệp</td>
                        <td>
                            @if ($tpl->is_active)
                                <span class="mayhen_admin_badge_paid">Đang Hoạt Động</span>
                            @else
                                <span class="mayhen_admin_badge_pending">Tạm Ẩn</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Recent Orders -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_title">
            <span>Đơn Hàng Gần Đây</span>
            <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn_sm">Xem tất cả &rarr;</a>
        </div>

        <table class="mayhen_admin_table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Gói Dịch Vụ</th>
                    <th>Số Tiền</th>
                    <th>Thời Gian</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td><code>{{ $order->order_code }}</code></td>
                        <td>{{ $order->user->name }} ({{ $order->user->email }})</td>
                        <td>{{ $order->plan->name }}</td>
                        <td><strong>{{ number_format($order->amount, 0, ',', '.') }}đ</strong></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($order->isPaid())
                                <span class="mayhen_admin_badge_paid">Đã Thanh Toán</span>
                            @else
                                <span class="mayhen_admin_badge_pending">Chờ Thanh Toán</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6C757D; padding: 24px;">
                            Chưa có đơn hàng nào phát sinh.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
