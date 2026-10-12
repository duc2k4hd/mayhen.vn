@extends('client.layouts.master')

@section('title', 'Hướng Dẫn Tạo Thiệp Cưới Online — Mây Hẹn')
@section('meta_description', 'Các bước tạo thiệp cưới online đơn giản và nhanh chóng trên nền tảng Mây Hẹn.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Quy Trình 4 Bước</span>
            <h1 class="mayhen_info_title">Cách Tạo Thiệp Cưới Tại Mây Hẹn</h1>
            <p class="mayhen_info_lead">
                Dễ dàng hoàn thiện thiệp cưới online đẳng cấp trong vòng vài phút mà không cần bất kỳ kiến thức nào về đồ họa.
            </p>
        </header>

        <div class="mayhen_home_steps_grid">
            <div class="mayhen_home_step_item">
                <div class="mayhen_home_step_number">01</div>
                <h2 class="mayhen_home_step_title">Khám Phá & Chọn Mẫu</h2>
                <p class="mayhen_home_step_desc">
                    Truy cập kho mẫu thiệp Mây Hẹn và chọn phong cách phù hợp với chủ đề đám cưới của bạn: Quiet Luxury, Editorial hoặc Minimal.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <div class="mayhen_home_step_number">02</div>
                <h2 class="mayhen_home_step_title">Điền Thông Tin Cưới</h2>
                <p class="mayhen_home_step_desc">
                    Nhập tên cô dâu, chú rể, phụ mẫu hai bên, thời gian tổ chức hôn lễ, tiệc mừng, địa chỉ sảnh tiệc và tải lên bộ ảnh cưới yêu thích.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <div class="mayhen_home_step_number">03</div>
                <h2 class="mayhen_home_step_title">Xem Thử Trực Tiếp</h2>
                <p class="mayhen_home_step_desc">
                    Trải nghiệm thiệp cưới tương tác trên điện thoại của chính bạn: kiểm tra hiệu ứng mở phong thư, nghe thử nhạc nền và kiểm tra thông tin.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <div class="mayhen_home_step_number">04</div>
                <h2 class="mayhen_home_step_title">Kích Hoạt & Gửi Lời Mời</h2>
                <p class="mayhen_home_step_desc">
                    Xuất bản đường link thiệp chính thức, tạo thiệp riêng cho nhà trai hoặc nhà gái và chia sẻ nhanh chóng qua Zalo, Facebook, SMS.
                </p>
            </div>
        </div>
    </div>
@endsection
