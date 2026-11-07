@extends('client.pages.account.layout')

@php
    $pageTitle = 'Đơn Hàng & Gói Cước';
@endphp

@section('account_breadcrumb', 'Đơn Hàng & Gói Cước')

@section('account_content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Lịch Sử Đơn Hàng & Gói Cước ({{ $orders->total() }})
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Theo dõi các giao dịch mua gói dịch vụ thiệp cưới và nâng cấp tính năng.
            </p>
        </div>
        <a href="{{ route('pricing') }}" class="mayhen_account_btn mayhen_account_btn_gold mayhen_account_btn_sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>Nâng Cấp Gói Mới</span>
        </a>
    </div>

    @if($orders->isNotEmpty())
        <div class="mayhen_account_table_wrap">
            <table class="mayhen_account_table">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Gói Dịch Vụ</th>
                        <th>Số Tiền</th>
                        <th>Phương Thức</th>
                        <th>Thời Gian</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $planName = $order->plan->name ?? 'Gói Dịch Vụ';
                            $status = $order->status;
                        @endphp
                        <tr>
                            <td>
                                <strong style="font-family: monospace; color: #1F1D1A;">{{ $order->order_code }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #1F1D1A;">{{ $planName }}</strong>
                                    @if($order->invitation && $order->invitation->wedding)
                                        <div style="font-size: 12px; color: #8C827A;">
                                            Thiệp: {{ $order->invitation->wedding->groom_name }} & {{ $order->invitation->wedding->bride_name }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #1F1D1A;">{{ number_format($order->amount) }} đ</span>
                            </td>
                            <td>
                                <span style="font-size: 13px; color: #6B635B;">
                                    {{ strtoupper($order->payment_method ?? 'VietQR') }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 13px; color: #6B635B;">
                                    {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '' }}
                                </span>
                            </td>
                            <td>
                                @if($status === 'paid' || $status === 'completed')
                                    <span class="mayhen_account_status_badge published">
                                        <span class="mayhen_account_status_dot"></span>
                                        Đã Thanh Toán
                                    </span>
                                @elseif($status === 'pending')
                                    <span class="mayhen_account_status_badge draft">
                                        <span class="mayhen_account_status_dot"></span>
                                        Chờ Thanh Toán
                                    </span>
                                @else
                                    <span class="mayhen_account_status_badge locked">
                                        <span class="mayhen_account_status_dot"></span>
                                        {{ ucfirst($status) }}
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($status === 'pending' && $order->plan)
                                    <a href="{{ route('checkout.show', $order->plan->slug) }}" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                                        Thanh Toán Ngay
                                    </a>
                                @else
                                    <span style="font-size: 12px; color: #8C827A;">Hoàn tất</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div style="margin-top: 24px;">
                {{ $orders->links() }}
            </div>
        @endif
    @else
        <div class="mayhen_account_section mayhen_account_empty_state">
            <svg class="mayhen_account_empty_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            <h2 class="mayhen_account_empty_title">Chưa Có Giao Dịch Nào</h2>
            <p class="mayhen_account_empty_desc">
                Bạn chưa thực hiện đơn hàng thanh toán nào. Các gói nâng cấp thiệp cưới Mây Hẹn sẽ giúp bạn có trọn vẹn album ảnh, nhạc nền độc quyền và tên miền thiệp riêng.
            </p>
            <a href="{{ route('pricing') }}" class="mayhen_account_btn mayhen_account_btn_gold" style="margin-top: 8px;">
                <span>Tham Khảo Bảng Giá Dịch Vụ</span>
            </a>
        </div>
    @endif
@endsection

