@extends('client.layouts.master')

@section('title', 'Dịch Vụ & Tiện Ích Thiệp Cưới Điện Tử — ' . config('app.name'))
@section('meta_description', 'Khám phá trọn bộ dịch vụ thiệp cưới online cao cấp: thiệp chuyển động phong thư, xác nhận tham dự RSVP gia đình, công cụ gửi Zalo, QR mừng cưới.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Giải Pháp Toàn Diện</span>
            <h1 class="mayhen_info_title">Dịch Vụ Thiệp Cưới Cao Cấp {{ config('app.name') }}</h1>
            <p class="mayhen_info_lead">
                Chúng tôi kết hợp nghệ thuật thẩm mỹ Quiet Luxury cùng công nghệ hiện đại để mang đến trải nghiệm mời cưới trọn vẹn nhất cho bạn và quan khách.
            </p>
        </header>

        <div class="mayhen_home_steps_grid">
            <div class="mayhen_home_step_item">
                <h2 class="mayhen_home_step_title">1. Thiệp Cưới Điện Tử Nghệ Thuật</h2>
                <p class="mayhen_home_step_desc">
                    Tùy biến hình ảnh, câu chuyện tình yêu, lịch trình tiệc cưới và hiệu ứng mở phong thư hoàng gia với chất lượng hiển thị sắc nét trên mọi dòng smartphone.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <h2 class="mayhen_home_step_title">2. Quản Lý Khách & RSVP Gia Đình</h2>
                <p class="mayhen_home_step_desc">
                    Xác nhận số lượng người lớn, trẻ em, chế độ ăn kiêng hoặc ăn chay. Tự động tổng hợp số lượng khách mời để cô dâu chú rể đặt bàn tiệc chính xác.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <h2 class="mayhen_home_step_title">3. Bộ Công Cụ Gửi Mời Qua Zalo</h2>
                <p class="mayhen_home_step_desc">
                    Tạo đường dẫn cá nhân hóa theo từng cách xưng hô (Kính mời, Thân mời) với ảnh xem trước chuẩn tỉ lệ, giúp lời mời luôn trang trọng và chân thành.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <h2 class="mayhen_home_step_title">4. QR Mừng Cưới Kín Đáo & Bảo Mật</h2>
                <p class="mayhen_home_step_desc">
                    Hiển thị mã QR ngân hàng chính chủ của cô dâu chú rể, tạo điều kiện thuận lợi cho những người bạn ở xa muốn gửi lời chúc mừng ấm áp.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <h2 class="mayhen_home_step_title">5. Bản Đồ Dẫn Đường Thông Minh</h2>
                <p class="mayhen_home_step_desc">
                    Tích hợp chỉ đường Google Maps một chạm, giúp khách mời dễ dàng tìm kiếm địa điểm tổ chức tiệc cưới mà không lo lạc đường.
                </p>
            </div>

            <div class="mayhen_home_step_item">
                <h2 class="mayhen_home_step_title">6. Lưu Giữ Kỷ Niệm & Sổ Lời Chúc</h2>
                <p class="mayhen_home_step_desc">
                    Lưu trữ tất cả lời chúc phúc ý nghĩa từ bạn bè, người thân cùng album ảnh cưới online để bạn có thể xem lại trong suốt nhiều năm sau.
                </p>
            </div>
        </div>
    </div>
@endsection
