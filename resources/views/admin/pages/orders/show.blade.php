@extends('admin.layouts.master')

@section('title', 'Chi Tiết Đơn Hàng #' . $order->order_code . ' — Mây Hẹn')
@section('page_title', 'Chi Tiết Đơn Hàng: #' . $order->order_code)

@section('breadcrumb_parent')
    <a href="{{ route('admin.orders.index') }}">Đơn Hàng</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/orders.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_page_title">
                <span>Đơn Hàng #{{ $order->order_code }}</span>
                @if ($order->isPaid())
                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Thanh Toán</span>
                @elseif ($order->status === 'cancelled')
                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Đã Hủy</span>
                @else
                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ Chuyển Khoản</span>
                @endif
            </div>
            <div class="mayhen_admin_page_subtitle">
                Tạo lúc: <strong>{{ $order->created_at->format('d/m/Y H:i:s') }}</strong> &bull; Cổng thanh toán: <span class="mayhen_admin_method_badge">{{ strtoupper($order->payment_method) }}</span>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Quay Lại Danh Sách
            </a>
            @if ($invitation)
                <a href="{{ route('admin.invitations.show', $invitation->id) }}" class="mayhen_admin_btn mayhen_admin_btn_primary">
                    Xem Thiệp Cưới Kèm Theo &nearr;
                </a>
            @endif
        </div>
    </div>

    <!-- 2 Columns Layout: Left Order & Customer | Right Plan & Actions -->
    <div class="mayhen_admin_order_detail_grid">
        <!-- Left: Order & Customer Information -->
        <div>
            <!-- Hero Amount Box -->
            <div class="mayhen_admin_order_amount_hero">
                <div>
                    <div style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--mh-admin-gold);">
                        Tổng Số Tiền Thanh Toán
                    </div>
                    <div class="mayhen_admin_order_amount_hero_val">
                        {{ number_format($order->amount, 0, ',', '.') }}đ
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; color: #BDB7AF; margin-bottom: 4px;">Mã Giao Dịch</div>
                    <code style="font-size: 1.15rem; color: #FFFFFF; background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;">
                        {{ $order->order_code }}
                    </code>
                </div>
            </div>

            <!-- Transaction Details Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        <span>Chi Tiết Giao Dịch & Đối Soát</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div style="background-color: var(--mh-admin-bg); padding: 12px 16px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                            <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 4px;">
                                Phương Thức Thanh Toán
                            </div>
                            <div style="font-size: 0.9375rem; font-weight: 600;">
                                {{ strtoupper($order->payment_method) }} (VietQR / SePay Auto Check)
                            </div>
                        </div>

                        <div style="background-color: var(--mh-admin-bg); padding: 12px 16px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                            <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 4px;">
                                Trạng Thái Đơn Hàng
                            </div>
                            <div style="font-size: 0.9375rem; font-weight: 600;">
                                @if ($order->isPaid())
                                    <span style="color: #1B5E20;">Đã Thanh Toán Thành Công</span>
                                @elseif ($order->status === 'cancelled')
                                    <span style="color: #C62828;">Đã Hủy Giao Dịch</span>
                                @else
                                    <span style="color: #B78103;">Đang Chờ Khách Chuyển Khoản</span>
                                @endif
                            </div>
                        </div>

                        <div style="background-color: var(--mh-admin-bg); padding: 12px 16px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                            <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 4px;">
                                Thời Gian Khởi Tạo Đơn
                            </div>
                            <div style="font-size: 0.9375rem; font-weight: 600;">
                                {{ $order->created_at->format('d/m/Y H:i:s') }}
                            </div>
                        </div>

                        <div style="background-color: var(--mh-admin-bg); padding: 12px 16px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                            <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 4px;">
                                Thời Gian Nhận Tiền
                            </div>
                            <div style="font-size: 0.9375rem; font-weight: 600;">
                                {{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i:s') : 'Chưa nhận được thanh toán' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Thông Tin Khách Hàng Đặt Mua</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-bottom: 4px;">Họ và Tên</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--mh-admin-charcoal);">
                                {{ $user->name ?? 'Khách vãng lai' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-bottom: 4px;">Email Liên Hệ</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--mh-admin-charcoal);">
                                {{ $user->email ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-bottom: 4px;">Số Điện Thoại</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--mh-admin-charcoal);">
                                {{ $user->phone ?? 'Chưa cập nhật' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-bottom: 4px;">Vai Trò Tài Khoản</div>
                            <div>
                                <span class="mayhen_admin_badge mayhen_admin_badge_info">
                                    {{ strtoupper($user->role ?? 'CUSTOMER') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Plan, Associated Invitation & Admin Actions -->
        <div>
            <!-- Plan Details Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Gói Dịch Vụ Đã Mua</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    <div style="margin-bottom: 12px;">
                        <strong style="font-size: 1.15rem; color: var(--mh-admin-charcoal);">{{ $plan->name ?? 'Gói Bản Quyền' }}</strong><br>
                        <span style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                            Giá niêm yết: {{ number_format($plan->price ?? 0, 0, ',', '.') }}đ &bull; Thời hạn: <strong>{{ $plan->duration_days ?? 365 }} ngày</strong>
                        </span>
                    </div>

                    @if (!empty($plan->features))
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-top: 16px; margin-bottom: 8px;">
                            Quyền Lợi Gói:
                        </div>
                        <ul style="padding-left: 20px; font-size: 0.8125rem; color: #4A453E; line-height: 1.6;">
                            @foreach ($plan->features as $feat)
                                <li>{{ $feat }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Associated Invitation Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span>Thiệp Cưới Kích Hoạt</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    @if ($invitation)
                        @php $wedding = $invitation->wedding; @endphp
                        <div style="margin-bottom: 12px;">
                            <strong style="font-size: 1rem; color: var(--mh-admin-charcoal);">
                                {{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }}
                            </strong><br>
                            <span style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                Đường dẫn: <code>/thiep/{{ $invitation->slug }}</code>
                            </span>
                        </div>
                        <div style="font-size: 0.8125rem; margin-bottom: 16px;">
                            Hạn lưu trữ: <strong>{{ $invitation->expires_at ? $invitation->expires_at->format('d/m/Y') : 'Chưa định ngày' }}</strong>
                        </div>
                        <a href="{{ route('admin.invitations.show', $invitation->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" style="width: 100%;">
                            Xem Quản Lý Thiệp Này &nearr;
                        </a>
                    @else
                        <div style="text-align: center; color: var(--mh-admin-muted); font-size: 0.8125rem; padding: 12px 0;">
                            Đơn hàng thanh toán trước, chưa liên kết với thiệp cưới cụ thể nào.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Admin Actions Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <span>Hành Động Quản Trị</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body" style="display: flex; flex-direction: column; gap: 10px;">
                    @if ($order->status === 'pending')
                        <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST" style="background: #FAF8F5; border: 1px solid var(--mh-admin-border-subtle); padding: 14px; border-radius: 8px;">
                            @csrf
                            <div style="font-size: 0.8125rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 8px;">
                                Đối Soát & Kích Hoạt Thủ Công
                            </div>
                            <div style="margin-bottom: 8px;">
                                <label style="font-size: 0.75rem; color: #64748B; font-weight: 600; display: block; margin-bottom: 4px;">Mã GD Ngân Hàng / Tham Chiếu <span style="color: #DC2626;">*</span></label>
                                <input type="text" name="reference_code" required placeholder="VD: FT26312984920..." class="mayhen_admin_input" style="width: 100%; font-size: 0.8125rem; padding: 6px 10px;">
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 0.75rem; color: #64748B; font-weight: 600; display: block; margin-bottom: 4px;">Ghi Chú Đối Soát</label>
                                <input type="text" name="reconciliation_note" placeholder="VD: Đã kiểm tra sao kê VietinBank khớp số tiền..." class="mayhen_admin_input" style="width: 100%; font-size: 0.8125rem; padding: 6px 10px;">
                            </div>
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="width: 100%;" onclick="return confirm('Bạn có chắc chắn đã nhận được tiền vào tài khoản ngân hàng và muốn kích hoạt gói cho đơn hàng #{{ $order->order_code }} không?');">
                                ✓ Duyệt & Kích Hoạt Gói
                            </button>
                        </form>

                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_danger" style="width: 100%;" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                ✕ Hủy Đơn Hàng Này
                            </button>
                        </form>
                    @elseif ($order->isPaid())
                        <div style="padding: 12px; background-color: var(--mh-admin-success-bg); color: var(--mh-admin-success-text); border-radius: 6px; font-size: 0.8125rem; text-align: center; font-weight: 600;">
                            Đơn hàng đã được thanh toán và kích hoạt thành công.
                        </div>

                        <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" style="margin-top: 8px;">
                            @csrf
                            <input type="hidden" name="reason" value="Hoàn tiền theo yêu cầu khách hàng">
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="width: 100%; color: var(--mh-admin-danger); border-color: var(--mh-admin-danger);" onclick="return confirm('Bạn có chắc chắn muốn hoàn tiền đơn hàng #{{ $order->order_code }}? Thiệp cưới sẽ được chuyển về trạng thái hết hạn lưu trữ trực tuyến, dữ liệu kỷ niệm của khách vẫn được bảo tồn.');">
                                ↺ Hoàn Tiền Đơn Hàng (Refund)
                            </button>
                        </form>
                    @elseif ($order->status === 'refunded')
                        <div style="padding: 12px; background-color: #FEF3C7; color: #92400E; border-radius: 6px; font-size: 0.8125rem; text-align: center; font-weight: 600;">
                            Đơn hàng này đã được hoàn tiền (Refunded).
                        </div>
                    @else
                        <div style="padding: 12px; background-color: var(--mh-admin-danger-bg); color: var(--mh-admin-danger-text); border-radius: 6px; font-size: 0.8125rem; text-align: center; font-weight: 600;">
                            Đơn hàng đã ở trạng thái hủy.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

