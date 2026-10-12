@extends('client.layouts.master')

@section('title', 'Thanh Toán Gói ' . $plan->name . ' — Mây Hẹn')
@section('meta_description', 'Thanh toán trực tuyến quét mã VietQR tự động kích hoạt gói dịch vụ thiệp cưới online Mây Hẹn.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkout.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_checkout_wrapper">
        <div class="mayhen_checkout_grid">
            <!-- Left: Checkout Form or QR Screen -->
            <div class="mayhen_checkout_card">
                <!-- 1. Form Section -->
                <div id="checkout-form-section">
                    <h1 class="mayhen_checkout_title">Xác Nhận & Thanh Toán</h1>
                    <p class="mayhen_checkout_desc">
                        Vui lòng điền thông tin để nhận mã kích hoạt và hướng dẫn thanh toán VietQR.
                    </p>

                    <form id="checkout-form">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="payment_method" value="vietqr">

                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label" for="customer_name">Họ và tên của bạn *</label>
                            <input type="text" id="customer_name" name="customer_name" class="mayhen_client_form_input" required value="{{ auth()->user()?->name ?? '' }}" placeholder="Nguyễn Văn A">
                        </div>

                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label" for="customer_email">Email nhận thông báo *</label>
                            <input type="email" id="customer_email" name="customer_email" class="mayhen_client_form_input" required value="{{ auth()->user()?->email ?? '' }}" placeholder="email@example.com">
                        </div>

                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label" for="customer_phone">Số điện thoại / Zalo *</label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="mayhen_client_form_input" required value="{{ auth()->user()?->phone ?? '' }}" placeholder="09xxxxxxxx">
                        </div>

                        <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg" style="width: 100%; margin-top: 1rem;">
                            Tạo Mã VietQR Thanh Toán
                        </button>
                    </form>
                </div>

                <!-- 2. VietQR Display Section (Hidden until submitted) -->
                <div id="checkout-qr-section" class="mayhen_checkout_qr_section">
                    <h2 class="mayhen_checkout_title">Quét Mã VietQR Để Thanh Toán</h2>
                    <p class="mayhen_checkout_desc">
                        Mở ứng dụng ngân hàng bất kỳ (MBBank, Vietcombank, Techcombank...) và quét mã bên dưới. Hệ thống sẽ tự động kích hoạt ngay khi nhận được tiền.
                    </p>

                    <img id="vietqr-image" src="" alt="Mã VietQR thanh toán" class="mayhen_checkout_qr_image">

                    <div id="bank-details" class="mayhen_checkout_bank_info"></div>

                    <div style="font-size: 0.8125rem; color: var(--mayhen-color-text-muted); display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: var(--mayhen-color-primary); animation: mayhen_pulse 1.5s infinite;"></span>
                        Đang chờ hệ thống ngân hàng xác nhận giao dịch...
                    </div>
                </div>

                <!-- 3. Success Feedback Section -->
                <div id="checkout-success-banner" class="mayhen_checkout_success_banner">
                    <h2 style="font-family: var(--mayhen-font-serif); font-size: 1.5rem; margin-bottom: 0.5rem;">Thanh Toán Thành Công!</h2>
                    <p style="font-size: 0.9375rem; margin-bottom: 1.5rem;">
                        Gói dịch vụ <strong>{{ $plan->name }}</strong> của bạn đã được kích hoạt thành công. Bạn có thể tiếp tục chỉnh sửa và xuất bản thiệp cưới.
                    </p>
                    <a href="{{ route('invitation.create') }}" class="mayhen_client_button_base mayhen_client_button_primary">
                        Đi Tới Trình Tạo Thiệp
                    </a>
                </div>
            </div>

            <!-- Right: Order Summary Sidebar -->
            <div class="mayhen_checkout_card">
                <h2 style="font-family: var(--mayhen-font-serif); font-size: 1.35rem; margin-bottom: 1rem;">Tóm Tắt Đơn Hàng</h2>

                <div class="mayhen_checkout_summary_box">
                    <div class="mayhen_checkout_summary_row">
                        <span>Gói dịch vụ:</span>
                        <strong>{{ $plan->name }}</strong>
                    </div>
                    <div class="mayhen_checkout_summary_row">
                        <span>Thời hạn sử dụng:</span>
                        <span>{{ $plan->duration_days }} ngày</span>
                    </div>
                    <div class="mayhen_checkout_summary_row">
                        <span>Số lượng thiệp tối đa:</span>
                        <span>{{ $plan->max_invitations }} thiệp</span>
                    </div>
                    <div class="mayhen_checkout_summary_total">
                        <span>Tổng thanh toán:</span>
                        <span style="color: var(--mayhen-color-primary);">{{ $plan->formatted_price }}</span>
                    </div>
                </div>

                <div style="font-size: 0.8125rem; color: var(--mayhen-color-text-muted); line-height: 1.7;">
                    &bull; Thanh toán an toàn qua cổng liên ngân hàng Napas 247.<br>
                    &bull; Hỗ trợ kích hoạt tự động 24/7 kể cả ngày lễ và cuối tuần.<br>
                    &bull; Hotline giải đáp thắc mắc: 0988.xxx.xxx
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/checkout.js') }}"></script>
@endpush
