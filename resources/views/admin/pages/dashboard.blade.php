@extends('admin.layouts.master')

@section('title', 'Admin Dashboard — Mây Hẹn')
@section('page_title', 'Tổng Quan Vận Hành & Doanh Thu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard.css') }}">
@endpush

@section('content')
    <!-- 5 Key KPI Metrics -->
    <div class="mayhen_admin_stats_grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_num" style="color: #1B5E20;">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
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
            <div class="mayhen_admin_stat_num">{{ $totalGuests }}</div>
            <div class="mayhen_admin_stat_label">Khách Mời & RSVP</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_num">{{ $totalUsers }}</div>
            <div class="mayhen_admin_stat_label">Tổng Người Dùng</div>
        </div>
    </div>

    <!-- Templates Popularity Grid -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"></path>
                </svg>
                <span>Hiệu Suất Mẫu Thiệp Cưới</span>
            </div>
            <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">Quản lý kho mẫu &rarr;</a>
        </div>

        <div class="mayhen_admin_table_responsive">
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
                                <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                            </td>
                            <td><strong>{{ $tpl->name }}</strong></td>
                            <td>{{ $tpl->category_label }}</td>
                            <td><strong>{{ $tpl->invitations_count }}</strong> thiệp</td>
                            <td>
                                @if ($tpl->is_active)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đang Hoạt Động</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Tạm Ẩn</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Đơn Hàng Gần Đây</span>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">Xem tất cả &rarr;</a>
        </div>

        <div class="mayhen_admin_table_responsive">
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
                            <td>
                                <strong>{{ $order->user->name }}</strong><br>
                                <span style="font-size: 0.75rem; color: #7A756E;">{{ $order->user->email }}</span>
                            </td>
                            <td>{{ $order->plan->name }}</td>
                            <td><strong style="color: #1B5E20;">{{ number_format($order->amount, 0, ',', '.') }}đ</strong></td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($order->isPaid())
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Thanh Toán</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ Thanh Toán</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #7A756E; padding: 24px;">
                                Chưa có đơn hàng nào phát sinh.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
