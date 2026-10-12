@extends('client.layouts.master')

@section('title', 'Về Mây Hẹn — Nền Tảng Thiệp Cưới Online Đẳng Cấp')
@section('meta_description', 'Câu chuyện thương hiệu và sứ mệnh của Mây Hẹn (mayhen.vn) trong việc nâng tầm trải nghiệm thiệp cưới online tại Việt Nam.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container_narrow mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Thương Hiệu & Tầm Nhìn</span>
            <h1 class="mayhen_info_title">Về Mây Hẹn (mayhen.vn)</h1>
            <p class="mayhen_info_lead">
                Mây Hẹn ra đời từ mong muốn mang lại một tấm thiệp cưới online thực sự tinh tế, sang trọng và giàu cảm xúc cho các cặp đôi Việt Nam.
            </p>
        </header>

        <article class="mayhen_info_content">
            <h2>Triết Lý Quiet Luxury Trong Thiệp Cưới</h2>
            <p>
                Chúng tôi tin rằng thiệp cưới không chỉ là một thông báo ngày giờ đơn thuần, mà là lời chào trang trọng đầu tiên gửi đến những người thân quý nhất. Một tấm thiệp đẹp không cần sự màu mè phô trương, mà chinh phục người xem bởi sự chuẩn xác của typography, sự nhã nhặn của bảng màu kem ấm và nhịp chuyển động êm ái khi mở nắp phong thư.
            </p>

            <h2>Tối Ưu Tuyệt Đối Cho Điện Thoại Di Động</h2>
            <p>
                Hơn 95% khách mời tại Việt Nam nhận và mở thiệp cưới qua các ứng dụng di động như Zalo hoặc Facebook Messenger. Do đó, toàn bộ kiến trúc của Mây Hẹn được xây dựng theo tiêu chuẩn Mobile-First: tải trang dưới 2 giây, không bị giật lag, dễ dàng chạm mở và tương thích với mọi kích thước màn hình.
            </p>

            <h2>Cam Kết Bảo Mật Thông Tin & Quyền Riêng Tư</h2>
            <p>
                Mây Hẹn coi trọng quyền riêng tư của cô dâu, chú rể và danh sách khách mời. Chúng tôi tuân thủ nghiêm ngặt các quy định pháp luật Việt Nam về bảo vệ dữ liệu cá nhân (bao gồm Luật số 91/2025/QH15). Đường link thiệp cá nhân hóa được mã hóa bằng token an toàn, không thể dò đoán và không bao giờ rò rỉ danh sách khách mời ra bên ngoài.
            </p>
        </article>
    </div>
@endsection
