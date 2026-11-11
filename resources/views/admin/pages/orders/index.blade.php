@extends('admin.layouts.master')

@section('title', 'Quản Lý Đơn Hàng')
@section('page_title', 'Đơn Hàng & Đối Soát Doanh Thu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/orders.css') }}">
@endpush

@section('content')
    <!-- KPI Overview Cards -->
    <div class="mayhen_admin_orders_kpi_grid">
        <div class="mayhen_admin_orders_kpi_card">
            <div class="mayhen_admin_orders_kpi_icon" style="color: #1B5E20; background-color: #E8F5E9; border-color: #C8E6C9;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_orders_kpi_val" style="color: #1B5E20;">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                <div class="mayhen_admin_orders_kpi_lbl">Doanh Thu Đã Thu</div>
            </div>
        </div>

        <div class="mayhen_admin_orders_kpi_card">
            <div class="mayhen_admin_orders_kpi_icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_orders_kpi_val">{{ $paidCount }}</div>
                <div class="mayhen_admin_orders_kpi_lbl">Đơn Đã Thanh Toán</div>
            </div>
        </div>

        <div class="mayhen_admin_orders_kpi_card">
            <div class="mayhen_admin_orders_kpi_icon" style="color: #B78103; background-color: #FFF8E1; border-color: #FFE082;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_orders_kpi_val" style="color: #B78103;">{{ $pendingCount }}</div>
                <div class="mayhen_admin_orders_kpi_lbl">Chờ Chuyển Khoản</div>
            </div>
        </div>

        <div class="mayhen_admin_orders_kpi_card">
            <div class="mayhen_admin_orders_kpi_icon" style="color: #C62828; background-color: #FFEBEE; border-color: #FFCDD2;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_orders_kpi_val" style="color: #C62828;">{{ $cancelledCount }}</div>
                <div class="mayhen_admin_orders_kpi_lbl">Đã Hủy / Hết Hạn</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs: Orders vs Webhooks -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 8px;">
        <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn" style="background-color: var(--mh-admin-charcoal); color: #FFF; border-color: var(--mh-admin-charcoal);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>Đơn Hàng & Doanh Thu</span>
        </a>
        <a href="{{ route('admin.orders.webhooks') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            <span>Nhật Ký Webhook Thanh Toán</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('admin.orders.index') }}" method="GET" class="mayhen_admin_filter_bar">
        <!-- Search query -->
        <div class="mayhen_admin_search_box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #7A756E;">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm mã đơn (MH...), tên khách, email, SĐT..." 
                class="mayhen_admin_search_input"
            >
        </div>

        <!-- Filter by status -->
        <select name="status" class="mayhen_admin_select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="paid" {{ $currentStatus === 'paid' ? 'selected' : '' }}>Đã thanh toán (Paid)</option>
            <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Chờ thanh toán (Pending)</option>
            <option value="refunded" {{ $currentStatus === 'refunded' ? 'selected' : '' }}>Đã hoàn tiền (Refunded)</option>
            <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Đã hủy (Cancelled)</option>
        </select>

        <!-- Filter by plan -->
        <select name="plan_id" class="mayhen_admin_select">
            <option value="">-- Tất cả gói cước --</option>
            @foreach ($plans as $p)
                <option value="{{ $p->id }}" {{ (string)$currentPlanId === (string)$p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
            @endforeach
        </select>

        <!-- Filter by payment method -->
        <select name="payment_method" class="mayhen_admin_select">
            <option value="">-- Tất cả cổng thanh toán --</option>
            <option value="vietqr" {{ $currentPaymentMethod === 'vietqr' ? 'selected' : '' }}>VietQR Quét Mã</option>
            <option value="sepay" {{ $currentPaymentMethod === 'sepay' ? 'selected' : '' }}>SePay Bank Transfer</option>
            <option value="bank_transfer" {{ $currentPaymentMethod === 'bank_transfer' ? 'selected' : '' }}>Chuyển Khoản Thường</option>
        </select>

        <!-- Filter by Date Range -->
        <select name="date_range" class="mayhen_admin_select">
            <option value="">-- Toàn bộ thời gian --</option>
            <option value="today" {{ $currentDateRange === 'today' ? 'selected' : '' }}>Hôm nay</option>
            <option value="this_week" {{ $currentDateRange === 'this_week' ? 'selected' : '' }}>Tuần này</option>
            <option value="this_month" {{ $currentDateRange === 'this_month' ? 'selected' : '' }}>Tháng này</option>
        </select>

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <span>Tìm Kiếm & Lọc</span>
        </button>

        @if ($currentSearch || $currentStatus || $currentPlanId || $currentPaymentMethod || $currentDateRange)
            <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt Lại</span>
            </a>
        @endif
    </form>

    <!-- Orders Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Danh Sách Đơn Hàng ({{ $orders->total() }} đơn)</span>
            </div>

            <a href="{{ route('admin.orders.export', request()->query()) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất Báo Cáo Doanh Thu (CSV)</span>
            </a>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Gói Dịch Vụ</th>
                        <th>Thiệp Kèm Theo</th>
                        <th>Số Tiền</th>
                        <th>Phương Thức</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $inv = $order->invitation;
                            $wedding = $inv?->wedding;
                            $methodClass = match($order->payment_method) {
                                'vietqr' => 'mayhen_admin_method_vietqr',
                                'sepay' => 'mayhen_admin_method_sepay',
                                default => '',
                            };
                        @endphp
                        <tr>
                            <!-- Order Code -->
                            <td>
                                <div class="mayhen_admin_order_code_wrap">
                                    <span class="mayhen_admin_order_code">{{ $order->order_code }}</span>
                                    <button 
                                        type="button" 
                                        class="mayhen_admin_copy_btn" 
                                        data-copy-target="{{ $order->order_code }}" 
                                        title="Sao chép mã đơn"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- Customer -->
                            <td>
                                <strong>{{ $order->user->name ?? 'Khách hàng' }}</strong><br>
                                <span style="font-size: 0.75rem; color: #7A756E;">{{ $order->user->email ?? '—' }}</span>
                                @if ($order->user && $order->user->phone)
                                    <br><span style="font-size: 0.75rem; color: #7A756E;">{{ $order->user->phone }}</span>
                                @endif
                            </td>

                            <!-- Plan -->
                            <td>
                                <strong>{{ $order->plan->name ?? 'Gói Bản Quyền' }}</strong><br>
                                <span style="font-size: 0.6875rem; color: #7A756E;">Thời hạn: {{ $order->plan->duration_days ?? 365 }} ngày</span>
                            </td>

                            <!-- Associated Invitation -->
                            <td>
                                @if ($inv)
                                    <a href="{{ route('admin.invitations.show', $inv->id) }}" style="color: var(--mh-admin-gold-hover); font-weight: 600; font-size: 0.8125rem;">
                                        {{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }} &nearr;
                                    </a><br>
                                    <span style="font-size: 0.6875rem; color: #7A756E;">/thiep/{{ $inv->slug }}</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_muted">Chưa gắn thiệp</span>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td>
                                <strong style="font-size: 0.9375rem; color: #1B5E20;">
                                    {{ number_format($order->amount, 0, ',', '.') }}đ
                                </strong>
                            </td>

                            <!-- Payment Method -->
                            <td>
                                <span class="mayhen_admin_method_badge {{ $methodClass }}">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                @if ($order->isPaid())
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Thanh Toán</span>
                                @elseif ($order->status === 'refunded')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_muted" style="background-color: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">Đã Hoàn Tiền</span>
                                @elseif ($order->status === 'cancelled')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Đã Hủy</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ Chuyển Khoản</span>
                                @endif
                            </td>

                            <!-- Created At -->
                            <td>
                                <span style="font-size: 0.8125rem;">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                @if ($order->paid_at)
                                    <br><span style="font-size: 0.6875rem; color: #1B5E20;">Trả: {{ $order->paid_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" title="Xem chi tiết đơn hàng">
                                        Chi Tiết &nearr;
                                    </a>

                                    @if ($order->status === 'pending')
                                        <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="reference_code" value="">
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary mayhen_admin_btn_sm" onclick="let ref = prompt('Vui lòng nhập mã tham chiếu giao dịch ngân hàng / sao kê đối soát cho đơn #{{ $order->order_code }}:'); if (!ref || ref.trim() === '') return false; this.form.reference_code.value = ref.trim();" title="Duyệt đối soát thanh toán thủ công">
                                                Duyệt
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: #7A756E; padding: 40px;">
                                Không tìm thấy đơn hàng nào phù hợp với bộ lọc hiện tại.
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

