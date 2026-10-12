@extends('client.layouts.master')

@section('title', 'Câu Hỏi Thường Gặp — Mây Hẹn')
@section('meta_description', 'Giải đáp các thắc mắc về tạo thiệp cưới online, thanh toán, hạn lưu trữ và cách gửi thiệp qua Zalo tại Mây Hẹn.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container_narrow mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Giải Đáp Thắc Mắc</span>
            <h1 class="mayhen_info_title">Câu Hỏi Thường Gặp (FAQ)</h1>
            <p class="mayhen_info_lead">
                Những thông tin quan trọng giúp bạn an tâm tạo và sử dụng thiệp cưới online Mây Hẹn.
            </p>
        </header>

        <div class="mayhen_info_faq_group">
            <div class="mayhen_info_faq_item">
                <h2 class="mayhen_info_faq_question">1. Tạo thiệp cưới trên Mây Hẹn có thực sự miễn phí không?</h2>
                <p class="mayhen_info_faq_answer">
                    Hoàn toàn miễn phí! Bạn có thể tự do chọn mẫu, điền thông tin, tải ảnh và xem trước bản thiệp hoàn chỉnh trên điện thoại. Bạn chỉ thanh toán khi hài lòng và muốn xuất bản đường link chính thức để gửi tới khách mời.
                </p>
            </div>

            <div class="mayhen_info_faq_item">
                <h2 class="mayhen_info_faq_question">2. Thiệp cưới online sau khi xuất bản sẽ tồn tại trong bao lâu?</h2>
                <p class="mayhen_info_faq_answer">
                    Gói Premium lưu giữ thiệp 6 tháng sau ngày cưới, còn gói Wedding Pro lưu giữ thiệp lên đến 2 năm để làm kỷ niệm. Bạn cũng có thể tải về toàn bộ ảnh và sổ lời chúc bất cứ lúc nào.
                </p>
            </div>

            <div class="mayhen_info_faq_item">
                <h2 class="mayhen_info_faq_question">3. Tôi có thể tạo hai phiên bản riêng cho Nhà Trai và Nhà Gái không?</h2>
                <p class="mayhen_info_faq_answer">
                    Có! Mây Hẹn hỗ trợ tính năng nhân bản thiệp thông minh. Bạn có thể dùng chung hình ảnh nhưng thay đổi địa chỉ tiệc, ngày giờ và lời mời riêng cho từng bên gia đình.
                </p>
            </div>

            <div class="mayhen_info_faq_item">
                <h2 class="mayhen_info_faq_question">4. Mã QR mừng cưới có bị thu phí hoa hồng không?</h2>
                <p class="mayhen_info_faq_answer">
                    Tuyệt đối không! Tiền mừng cưới từ khách mời được chuyển trực tiếp vào tài khoản ngân hàng chính chủ của bạn theo chuẩn VietQR. Mây Hẹn không đóng vai trò trung gian và không thu bất kỳ khoản phí nào từ tiền mừng.
                </p>
            </div>

            <div class="mayhen_info_faq_item">
                <h2 class="mayhen_info_faq_question">5. Khách mời có cần đăng ký tài khoản để xem thiệp và gửi lời chúc không?</h2>
                <p class="mayhen_info_faq_answer">
                    Không cần. Khách mời chỉ cần bấm vào đường link thiệp do bạn gửi qua Zalo hoặc Messenger là có thể xem thiệp, thưởng thức âm nhạc và gửi xác nhận tham dự (RSVP) ngay lập tức.
                </p>
            </div>
        </div>
    </div>
@endsection
