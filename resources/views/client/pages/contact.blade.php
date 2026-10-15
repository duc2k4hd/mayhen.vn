@extends('client.layouts.master')

@section('title', 'Liên Hệ & Hỗ Trợ Khách Hàng — ' . config('app.name'))
@section('meta_description', 'Kênh hỗ trợ và liên hệ chính thức của ' . config('app.name') . ' (mayhen.vn) dành cho cô dâu chú rể và đối tác studio.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container_narrow mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Chăm Sóc & Đồng Hành</span>
            <h1 class="mayhen_info_title">Liên Hệ Với {{ config('app.name') }}</h1>
            <p class="mayhen_info_lead">
                Đội ngũ {{ config('app.name') }} luôn sẵn sàng hỗ trợ bạn hoàn thiện tấm thiệp cưới hoàn hảo nhất.
            </p>
        </header>

        <div class="mayhen_info_content">
            <div style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-bottom: 2.5rem;">
                <div>
                    <h3 style="margin-top: 0;">Thông Tin Chính Thức</h3>
                    <p style="margin-bottom: 0.5rem;"><strong>Website:</strong> https://mayhen.vn</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Email Hỗ Trợ:</strong> hotro@mayhen.vn</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Hotline / Zalo:</strong> 0988.xxx.xxx (8:30 - 21:00 hàng ngày)</p>
                    <p style="margin-bottom: 0;"><strong>Hợp tác Studio / Wedding Planner:</strong> doitac@mayhen.vn</p>
                </div>
            </div>

            <h2>Gửi Lời Nhắn Hỗ Trợ</h2>
            <form onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã liên hệ! Đội ngũ Mây Hẹn sẽ phản hồi trong vòng 24 giờ.');">
                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label">Họ và tên của bạn</label>
                    <input type="text" class="mayhen_client_form_input" required placeholder="Nguyễn Văn A">
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label">Số điện thoại hoặc Zalo</label>
                    <input type="tel" class="mayhen_client_form_input" required placeholder="09xxxxxxxx">
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label">Email</label>
                    <input type="email" class="mayhen_client_form_input" required placeholder="email@example.com">
                </div>

                <div class="mayhen_client_form_group">
                    <label class="mayhen_client_form_label">Nội dung cần hỗ trợ</label>
                    <textarea class="mayhen_client_form_textarea" rows="4" required placeholder="Mô tả yêu cầu hoặc câu hỏi của bạn..."></textarea>
                </div>

                <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg" style="width: 100%;">
                    Gửi Yêu Cầu Hỗ Trợ
                </button>
            </form>
        </div>
    </div>
@endsection
