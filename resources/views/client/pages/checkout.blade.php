@extends('client.layouts.master')

@php
    $checkoutBrandName = \App\Models\PlatformSetting::getBrandName();
    $userInitial = mb_strtoupper(
        mb_substr(trim((string) ($beneficiaryName ?: $beneficiaryEmail)), 0, 1, 'UTF-8'),
        'UTF-8',
    );
    $durationText =
        ($planRule['duration_months'] ?? 0) === 12
            ? '1 năm (365 ngày)'
            : (($planRule['duration_months'] ?? 0) === 6
                ? '6 tháng (180 ngày)'
                : ($planRule['duration_days'] ?? $plan->duration_days) . ' ngày');
    $isSessionPaid = $isSessionMode && $sessionOrder && $sessionOrder->isPaid();
    $displayAmount = $isSessionMode && $sessionOrder ? (int) $sessionOrder->amount : (int) $plan->price;
    $sessionDiscount = $isSessionMode && $sessionOrder ? max(0, (int) $plan->price - (int) $sessionOrder->amount) : 0;
@endphp

@section('title', 'Thanh Toán Gói ' . $plan->name)
@section('meta_description', 'Thanh toán trực tuyến quét mã VietQR tự động kích hoạt gói dịch vụ thiệp cưới online ' .
    $checkoutBrandName . '.')

    @push('styles')
        <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/checkout.css') }}">
    @endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_checkout_wrapper" id="mayhen-checkout-app"
        data-plan-id="{{ $plan->id }}" data-plan-price="{{ (int) $plan->price }}" data-plan-name="{{ $plan->name }}"
        data-process-url="{{ route('checkout.process') }}" data-coupon-url="{{ route('checkout.apply_coupon') }}"
        data-can-manual-verify="{{ $canManualVerify ? '1' : '0' }}"
        data-is-shared-payer="{{ $isSharedPayerView ? '1' : '0' }}">

        <div class="mayhen_checkout_grid">
            <!-- Left: Checkout Form or QR Screen -->
            <div class="mayhen_checkout_card">
                <!-- Account Ownership Banner (Always Visible) -->
                <div class="mayhen_checkout_account_card">
                    <div class="mayhen_checkout_account_avatar" aria-hidden="true">{{ $userInitial }}</div>
                    <div class="mayhen_checkout_account_meta">
                        <div class="mayhen_checkout_account_kicker">
                            {{ $isSharedPayerView ? 'Đang thanh toán hộ cho tài khoản' : 'Tài khoản được kích hoạt tự động' }}
                        </div>
                        <div class="mayhen_checkout_account_name">{{ $beneficiaryName }}</div>
                        <div class="mayhen_checkout_account_email">{{ $beneficiaryEmail }}</div>
                    </div>
                    <div class="mayhen_checkout_account_tier">
                        <span class="mayhen_checkout_tier_badge mayhen_checkout_tier_badge--{{ $accountPlanSlug }}">
                            {{ $accountPlanLabel }}
                        </span>
                        @if ($accountPlanExpiry)
                            <span class="mayhen_checkout_tier_expiry">Hạn: {{ $accountPlanExpiry->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>

                @if (!$isSessionMode)
                    @php
                        $canProceedCheckout = (bool) ($eligibility['allowed'] ?? true);
                        $blockCode = (string) ($eligibility['code'] ?? '');
                        $blockMessage = (string) ($eligibility['message'] ?? '');
                    @endphp

                    @if (!$canProceedCheckout)
                        <div class="mayhen_checkout_blocked_card mayhen_checkout_blocked_card--{{ strtolower($blockCode) }}">
                            <div class="mayhen_checkout_blocked_icon" aria-hidden="true">
                                @if ($blockCode === 'PENDING_ORDER_EXISTS')
                                    ⏳
                                @elseif ($blockCode === 'ADMIN_ALWAYS_PRO')
                                    ★
                                @else
                                    ✓
                                @endif
                            </div>

                            <h1 class="mayhen_checkout_blocked_title">
                                @if ($blockCode === 'ADMIN_ALWAYS_PRO')
                                    Tài Khoản Quản Trị Luôn Ở Gói Pro Vĩnh Viễn
                                @elseif ($blockCode === 'ALREADY_PRO_ACTIVE')
                                    Tài Khoản Đang Sử Dụng Gói Pro VIP
                                @elseif ($blockCode === 'ALREADY_PLUS_ACTIVE')
                                    Tài Khoản Đang Sử Dụng Gói Plus
                                @elseif ($blockCode === 'PENDING_ORDER_EXISTS')
                                    Bạn Đang Có Phiên Thanh Toán Chưa Hoàn Tất
                                @else
                                    Chưa Thể Tạo Đơn Thanh Toán Mới
                                @endif
                            </h1>

                            <p class="mayhen_checkout_blocked_desc">{{ $blockMessage }}</p>

                            <div class="mayhen_checkout_blocked_actions">
                                @if ($blockCode === 'PENDING_ORDER_EXISTS' && $activePendingOrder)
                                    <a href="{{ route('checkout.session', ['orderCode' => $activePendingOrder->order_code, 'token' => $activePendingOrder->checkout_token]) }}"
                                        class="mayhen_client_button_base mayhen_client_button_primary">
                                        Mở Lại Mã QR #{{ $activePendingOrder->order_code }} &rarr;
                                    </a>
                                    <form method="POST"
                                        action="{{ route('checkout.cancel', $activePendingOrder->order_code) }}">
                                        @csrf
                                        <input type="hidden" name="redirect_to"
                                            value="{{ route('checkout.show', $plan->slug) }}">
                                        <button type="submit"
                                            class="mayhen_client_button_base mayhen_client_button_ghost mayhen_checkout_cancel_btn">
                                            Hủy Phiên Cũ Để Chọn Gói Này
                                        </button>
                                    </form>
                                @elseif ($blockCode === 'ALREADY_PLUS_ACTIVE')
                                    <a href="{{ route('checkout.show', 'pro') }}"
                                        class="mayhen_client_button_base mayhen_client_button_primary">
                                        Nâng Cấp Lên Pro VIP &rarr;
                                    </a>
                                    <a href="{{ route('account.invitations') }}"
                                        class="mayhen_client_button_base mayhen_client_button_secondary">
                                        Về Danh Sách Thiệp Cưới
                                    </a>
                                @else
                                    <a href="{{ route('account.invitations') }}"
                                        class="mayhen_client_button_base mayhen_client_button_primary">
                                        Về Danh Sách Thiệp Cưới &rarr;
                                    </a>
                                    <a href="{{ route('pricing') }}"
                                        class="mayhen_client_button_base mayhen_client_button_secondary">
                                        Xem Bảng Giá Dịch Vụ
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- 1. Form Section -->
                        <div id="checkout-form-section">
                            <h1 class="mayhen_checkout_title">Xác Nhận &amp; Thanh Toán</h1>
                            <p class="mayhen_checkout_desc">
                                @if ($isUpgradeFromPlus ?? false)
                                    Đơn hàng này sẽ <strong>nâng cấp trực tiếp từ Gói Plus lên Gói Pro VIP
                                        ({{ $durationText }} / tối đa 30 thiệp)</strong> cho tài khoản
                                    <strong>{{ $beneficiaryEmail }}</strong> ngay sau khi xác nhận tiền vào.
                                @else
                                    Gói <strong>{{ $plan->name }}</strong> sẽ được tự động nâng cấp cho tài khoản
                                    <strong>{{ $beneficiaryEmail }}</strong> ngay sau khi xác nhận chuyển khoản
                                    thành công.
                                @endif
                            </p>

                            <div id="checkout-form-alert" class="mayhen_checkout_alert" role="alert" hidden></div>

                            <form id="checkout-form" novalidate>
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="payment_method" value="vietqr">
                                <input type="hidden" name="coupon_code" id="checkout-applied-coupon" value="">

                                @if ($invitation)
                                    <input type="hidden" name="invitation_id" value="{{ $invitation->id }}">
                                    <div class="mayhen_checkout_invitation_target">
                                        <div class="mayhen_checkout_invitation_kicker">Áp Dụng Kích Hoạt Cho Thiệp Cưới</div>
                                        <div class="mayhen_checkout_invitation_couple">
                                            {{ $invitation->wedding->groom_name ?? 'Chú Rể' }} &amp;
                                            {{ $invitation->wedding->bride_name ?? 'Cô Dâu' }}
                                        </div>
                                        <div class="mayhen_checkout_invitation_sub">
                                            Mẫu thiệp: {{ $invitation->template->name ?? $invitation->slug }}
                                        </div>
                                    </div>
                                @endif

                                <div class="mayhen_checkout_field_grid">
                                    <div class="mayhen_client_form_group">
                                        <label class="mayhen_client_form_label" for="customer_name">Họ và tên tài
                                            khoản</label>
                                        <input type="text" id="customer_name" name="customer_name"
                                            class="mayhen_client_form_input" value="{{ $user->name ?? '' }}"
                                            placeholder="Nguyễn Văn A">
                                    </div>

                                    <div class="mayhen_client_form_group">
                                        <label class="mayhen_client_form_label" for="customer_phone">Số điện thoại / Zalo
                                            liên hệ *</label>
                                        <input type="tel" id="customer_phone" name="customer_phone"
                                            class="mayhen_client_form_input" required value="{{ $user->phone ?? '' }}"
                                            placeholder="09xxxxxxxx">
                                    </div>
                                </div>

                                <div class="mayhen_client_form_group">
                                    <label class="mayhen_client_form_label" for="customer_email_display">Email tài khoản
                                        nhận gói (Cố định theo đăng nhập)</label>
                                    <input type="email" id="customer_email_display"
                                        class="mayhen_client_form_input mayhen_checkout_input_readonly"
                                        value="{{ $beneficiaryEmail }}" readonly tabindex="-1">
                                </div>

                                <!-- Coupon Section -->
                                <div class="mayhen_checkout_coupon_box">
                                    <label class="mayhen_client_form_label" for="coupon_input">Mã ưu đãi (nếu có)</label>
                                    <div class="mayhen_checkout_coupon_row">
                                        <input type="text" id="coupon_input" class="mayhen_client_form_input"
                                            placeholder="Nhập mã giảm giá (VD: MAYHEN20)" autocomplete="off">
                                        <button type="button" id="checkout-apply-coupon-btn"
                                            class="mayhen_client_button_base mayhen_client_button_secondary">
                                            Áp Dụng
                                        </button>
                                        <button type="button" id="checkout-remove-coupon-btn"
                                            class="mayhen_client_button_base mayhen_client_button_ghost" hidden>
                                            Gỡ Mã
                                        </button>
                                    </div>
                                    <div id="checkout-coupon-feedback" class="mayhen_checkout_coupon_feedback" hidden></div>
                                </div>

                                <button type="submit" id="checkout-submit-btn"
                                    class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg mayhen_checkout_submit_btn">
                                    Tạo Mã VietQR Thanh Toán
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

                <!-- 2. VietQR Display & Active SePay Verify Section -->
                <div id="checkout-qr-section" class="mayhen_checkout_qr_section"
                    @if (!($isSessionMode && ($isSessionActivePending ?? false))) hidden @endif>
                    <div class="mayhen_checkout_qr_head">
                        <div>
                            <span class="mayhen_checkout_secure_pill">
                                {{ $isSharedPayerView ? 'Bảo mật' : 'Bảo mật' }}
                            </span>
                            <h2 class="mayhen_checkout_title">Quét Mã VietQR Để Thanh Toán</h2>
                        </div>
                        <div class="mayhen_checkout_timer_box" aria-live="polite">
                            <span class="mayhen_checkout_timer_label">Hiệu lực phiên</span>
                            <strong id="checkout-countdown" class="mayhen_checkout_timer_value">30:00</strong>
                        </div>
                    </div>

                    <p class="mayhen_checkout_desc">
                        Mở ứng dụng ngân hàng bất kỳ (MBBank, Vietcombank, Techcombank, VietinBank...) và quét mã dưới đây.
                        Tải lại trang (F5) vẫn giữ nguyên phiên; bạn cũng có thể sao chép link này gửi cho người thân thanh
                        toán hộ.
                    </p>

                    <div class="mayhen_checkout_qr_stage">
                        <div class="mayhen_checkout_qr_frame">
                            <img id="vietqr-image" src="" alt="Mã VietQR thanh toán"
                                class="mayhen_checkout_qr_image">
                            <div class="mayhen_checkout_qr_caption">
                                Mã đơn: <strong
                                    id="checkout-order-code-label">{{ $sessionOrder ? '#' . $sessionOrder->order_code : '—' }}</strong>
                            </div>
                        </div>

                        <div class="mayhen_checkout_bank_panel">
                            <div id="bank-details" class="mayhen_checkout_bank_info"></div>

                            <div class="mayhen_checkout_transfer_notice">
                                <strong>Lưu ý quan trọng:</strong> Vui lòng giữ nguyên chính xác <strong>Nội dung chuyển
                                    khoản</strong> và <strong>Số tiền</strong> để hệ thống tự động nâng cấp cho tài khoản
                                <strong>{{ $beneficiaryEmail }}</strong>.
                            </div>
                        </div>
                    </div>

                    @if (!$isSharedPayerView)
                        <div class="mayhen_checkout_share_strip">
                            <div class="mayhen_checkout_share_text">
                                <strong>Nhờ người thân thanh toán hộ?</strong> Gửi đường dẫn phiên này qua Zalo — họ không cần
                                đăng nhập tài khoản của bạn vẫn quét mã được.
                            </div>
                            <button type="button" id="checkout-copy-share-link-btn"
                                class="mayhen_checkout_copy_btn mayhen_checkout_copy_btn--share">
                                Sao chép link thanh toán
                            </button>
                        </div>
                    @endif

                    <div class="mayhen_checkout_live_status">
                        <span class="mayhen_checkout_pulse_dot" aria-hidden="true"></span>
                        <span id="checkout-polling-text">Đang kiểm tra...</span>
                    </div>

                    <div id="checkout-verify-feedback" class="mayhen_checkout_alert" role="status" hidden></div>

                    <div class="mayhen_checkout_qr_actions">
                        <button type="button" id="checkout-manual-verify-btn"
                            class="mayhen_client_button_base mayhen_client_button_primary">
                            Tôi đã chuyển khoản — Kiểm tra ngay
                        </button>
                        @if (!$isSharedPayerView)
                            <a href="{{ route('account.orders') }}"
                                class="mayhen_client_button_base mayhen_client_button_secondary">
                                Xem Lịch Sử Đơn Hàng
                            </a>
                            <button type="button" id="checkout-cancel-session-btn"
                                class="mayhen_client_button_base mayhen_client_button_ghost mayhen_checkout_cancel_btn">
                                Hủy Phiên Này
                            </button>
                        @endif
                    </div>
                </div>

                <!-- 2b. Terminated / Cancelled / Expired Session Banner -->
                <div id="checkout-terminated-banner" class="mayhen_checkout_terminated_banner"
                    @if (!($isSessionMode && ($isSessionTerminated ?? false))) hidden @endif>
                    <div class="mayhen_checkout_terminated_icon" aria-hidden="true">✕</div>
                    <h2 id="checkout-terminated-title" class="mayhen_checkout_terminated_title">
                        {{ $terminatedTitle ?: 'Phiên Thanh Toán Đã Bị Hủy' }}
                    </h2>
                    <p id="checkout-terminated-desc" class="mayhen_checkout_terminated_desc">
                        {{ $terminatedMessage ?: 'Phiên thanh toán này không còn hiệu lực. Vui lòng KHÔNG chuyển khoản cho mã đơn này nữa.' }}
                    </p>
                    <div class="mayhen_checkout_terminated_actions">
                        @if (!$isSharedPayerView)
                            <a href="{{ route('pricing') }}"
                                class="mayhen_client_button_base mayhen_client_button_primary">
                                Chọn Lại Gói Dịch Vụ &rarr;
                            </a>
                            <a href="{{ route('account.orders') }}"
                                class="mayhen_client_button_base mayhen_client_button_secondary">
                                Xem Lịch Sử Đơn Hàng
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="mayhen_client_button_base mayhen_client_button_primary">
                                Về Trang Chủ {{ $checkoutBrandName }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 3. Success Feedback Section -->
                <div id="checkout-success-banner" class="mayhen_checkout_success_banner"
                    @if (!$isSessionPaid) hidden @endif>
                    <div class="mayhen_checkout_success_icon" aria-hidden="true">✓</div>
                    <h2 class="mayhen_checkout_success_title">Thanh Toán Thành Công!</h2>
                    <p class="mayhen_checkout_success_desc">
                        @if ($isSharedPayerView)
                            Cảm ơn bạn đã thanh toán hộ! Gói dịch vụ <strong>{{ $plan->name }}</strong> đã được kích hoạt
                            tự động cho tài khoản <strong>{{ $beneficiaryName }} ({{ $beneficiaryEmail }})</strong>.
                        @else
                            Gói dịch vụ <strong>{{ $plan->name }}</strong> đã được kích hoạt tự động cho tài khoản
                            <strong>{{ $beneficiaryEmail }}</strong>.
                        @endif
                    </p>
                    <div class="mayhen_checkout_success_actions">
                        @if (!$isSharedPayerView)
                            <a id="checkout-success-redirect-btn"
                                href="{{ $sessionOrder ? route('checkout.first_success', $sessionOrder->order_code) : route('account.invitations') }}"
                                class="mayhen_client_button_base mayhen_client_button_primary">
                                Xem Hướng Dẫn Khởi Đầu &rarr;
                            </a>
                            <a href="{{ route('account.invitations') }}"
                                class="mayhen_client_button_base mayhen_client_button_secondary">
                                Về Danh Sách Thiệp Cưới
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="mayhen_client_button_base mayhen_client_button_primary">
                                Về Trang Chủ {{ $checkoutBrandName }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary Sidebar -->
            <aside class="mayhen_checkout_card mayhen_checkout_sidebar">
                <h2 class="mayhen_checkout_sidebar_title">Tóm Tắt Đơn Hàng</h2>

                <div class="mayhen_checkout_summary_box">
                    <div class="mayhen_checkout_summary_row">
                        <span>Gói dịch vụ:</span>
                        <strong>{{ $plan->name }}</strong>
                    </div>
                    <div class="mayhen_checkout_summary_row">
                        <span>Hình thức:</span>
                        <strong>{{ ($isUpgradeFromPlus ?? false) ? 'Nâng cấp từ Plus lên Pro' : 'Nâng cấp gói tài khoản' }}</strong>
                    </div>
                    <div class="mayhen_checkout_summary_row">
                        <span>Thời hạn sử dụng:</span>
                        <span>{{ $durationText }}</span>
                    </div>
                    <div class="mayhen_checkout_summary_row">
                        <span>Số lượng thiệp tối đa:</span>
                        <span>{{ $planRule['max_invitations'] ?? $plan->max_invitations }} thiệp</span>
                    </div>
                    <div class="mayhen_checkout_summary_row">
                        <span>Tạm tính:</span>
                        <span id="checkout-summary-subtotal">{{ number_format((int) $plan->price, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="mayhen_checkout_summary_row mayhen_checkout_summary_discount"
                        id="checkout-summary-discount-row" @if ($sessionDiscount <= 0) hidden @endif>
                        <span>Mã ưu đãi<span id="checkout-summary-coupon-code"></span>:</span>
                        <strong
                            id="checkout-summary-discount-val">-{{ number_format($sessionDiscount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="mayhen_checkout_summary_total">
                        <span>Tổng thanh toán:</span>
                        <span id="checkout-summary-total"
                            class="mayhen_checkout_total_price">{{ number_format($displayAmount, 0, ',', '.') }}đ</span>
                    </div>
                </div>

                <ul class="mayhen_checkout_guarantees">
                    <li>Thanh toán trực tiếp qua hệ thống liên ngân hàng Napas 247.</li>
                    <li>Mỗi đơn hàng có đường dẫn phiên &amp; token riêng — tải lại trang không bị mất QR và có thể gửi nhờ
                        người thân thanh toán hộ.</li>
                    <li>Tự động kích hoạt 24/7 kể cả cuối tuần và ngày lễ.</li>
                    <li>
                        Hỗ trợ kỹ thuật: <strong>{{ $supportPhone }}</strong>
                        @if (!empty($supportZaloUrl))
                            &bull; <a href="{{ $supportZaloUrl }}" target="_blank" rel="noopener noreferrer"
                                class="mayhen_checkout_support_link">Nhắn Zalo hỗ trợ</a>
                        @endif
                    </li>
                </ul>
            </aside>
        </div>
    </div>

    @if ($resumedOrderPayload)
        <script type="application/json" id="mayhen-checkout-resume-data">{!! json_encode($resumedOrderPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/checkout.js') }}"></script>
@endpush
