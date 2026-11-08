@extends('client.layouts.master')

@section('title', 'Kích Hoạt Gói Dịch Vụ Thành Công | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkout-first-success.css') }}">
@endpush

@section('content')
<div class="mayhen_success_wrap" id="mayhen_success_app" data-order-code="{{ $order->order_code }}" data-is-paid="{{ $guide['order']['is_paid'] ? '1' : '0' }}" data-status-url="{{ route('checkout.status', $order->order_code) }}">
    <div class="mayhen_success_container">
        {{-- Hero Status Card --}}
        <div class="mayhen_success_hero">
            @if($guide['order']['is_paid'])
                <div class="success_icon_ring">
                    ✓
                </div>
                <h1 class="success_title">Kích Hoạt Gói Dịch Vụ Thành Công!</h1>
                <p class="success_subtitle">
                    Chúc mừng hai bạn! Gói <strong>{{ $guide['plan']['name'] }}</strong> đã được kích hoạt thành công cho hôn lễ của hai bạn. Dưới đây là 3 bước quan trọng để bắt đầu gửi thiệp và chuẩn bị cho ngày cưới trọn vẹn.
                </p>
            @else
                <div class="success_icon_ring success_icon_ring--pending">
                    ⏳
                </div>
                <h1 class="success_title">Đang Xác Nhận Giao Dịch Ngân Hàng</h1>
                <p class="success_subtitle">
                    Hệ thống đang kiểm tra tự động giao dịch chuyển khoản từ cổng SePay / VietQR cho đơn hàng <strong>#{{ $order->order_code }}</strong>. Trang sẽ tự động kích hoạt ngay khi nhận được tiền.
                </p>
            @endif

            <div class="success_order_meta">
                <span>Mã đơn: <strong>#{{ $order->order_code }}</strong></span>
                <span>Gói dịch vụ: <strong>{{ $guide['plan']['name'] }}</strong></span>
                <span>Số tiền: <strong style="color: #9C7A4A;">{{ number_format($guide['order']['amount']) }}đ</strong></span>
                <span>Thời hạn: <strong>{{ $guide['plan']['duration_days'] }} ngày</strong></span>
            </div>
        </div>

        {{-- 3-Step Onboarding Quickstart --}}
        <div class="mayhen_success_onboarding_card">
            <div class="onboarding_head">
                <h2 class="onboarding_title">3 Bước Khởi Đầu Hoàn Hảo Cho Ngày Trọng Đại</h2>
                <p class="onboarding_desc">Các bước tối ưu để gửi thiệp cưới chỉn chu và chuẩn bị đón tiếp khách mời chu đáo</p>
            </div>

            <div class="onboarding_steps_list">
                @foreach($guide['onboarding_steps'] as $st)
                    <div class="onboarding_step_item">
                        <div class="step_num_circle">
                            0{{ $st['step'] }}
                        </div>
                        <div>
                            <div class="step_content_title">
                                {{ $st['title'] }}
                                <span class="step_badge">{{ $st['badge'] }}</span>
                            </div>
                            <p class="step_content_desc">{{ $st['description'] }}</p>
                        </div>
                        <div class="onboarding_step_action">
                            <a href="{{ $st['action_url'] }}" class="step_action_btn">
                                {{ $st['action_text'] }} &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Support Contact Strip --}}
        <div class="mayhen_success_support_box">
            <div>
                <div class="support_box_title">Cần Trợ Giúp Vận Hành Thiệp Cưới?</div>
                <p class="support_box_desc">Đội ngũ kỹ thuật Mây Hẹn sẵn sàng đồng hành cùng hai bạn suốt quá trình chuẩn bị cưới.</p>
            </div>
            <div class="support_box_links">
                <a href="{{ $guide['support_contact']['zalo_link'] }}" target="_blank" class="support_link">
                    💬 Chat Zalo Hỗ Trợ 24/7
                </a>
                <span>&bull;</span>
                <a href="{{ route('account.invitations') }}" class="support_link">
                    Về Trang Quản Lý Thiệp &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('mayhen_success_app');
    if (!appEl) return;

    const isPaid = appEl.getAttribute('data-is-paid') === '1';
    const statusUrl = appEl.getAttribute('data-status-url');

    if (!isPaid && statusUrl) {
        // Tự động kiểm tra trạng thái mỗi 3 giây nếu đơn hàng đang pending
        const pollInterval = setInterval(async () => {
            try {
                const res = await fetch(statusUrl);
                const data = await res.json();
                if (data.is_paid) {
                    clearInterval(pollInterval);
                    window.location.reload();
                }
            } catch (e) {
                // silent
            }
        }, 3000);
    }
});
</script>
@endpush

