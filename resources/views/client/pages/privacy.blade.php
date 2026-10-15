@extends('client.layouts.master')

@section('title', 'Chính Sách Bảo Mật Dữ Liệu Cá Nhân — ' . config('app.name'))
@section('meta_description', 'Chính sách bảo vệ dữ liệu cá nhân theo quy định pháp luật Việt Nam tại ' . config('app.name') . ' (mayhen.vn).')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container_narrow mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Bảo Vệ Dữ Liệu</span>
            <h1 class="mayhen_info_title">Chính Sách Bảo Mật & Quyền Riêng Tư</h1>
            <p class="mayhen_info_lead">
                Cam kết tuân thủ nghiêm ngặt Luật Bảo vệ Dữ liệu Cá nhân Việt Nam (Luật số 91/2025/QH15).
            </p>
        </header>

        <article class="mayhen_info_content">
            <h2>1. Mục Đích Thu Thập Dữ Liệu</h2>
            <p>
                {{ config('app.name') }} chỉ thu thập những dữ liệu cần thiết phục vụ cho việc tạo, hiển thị và quản lý thiệp cưới, bao gồm: tên cô dâu chú rể, thông tin sự kiện cưới, hình ảnh kỷ niệm, và danh sách khách mời do chính bạn nhập vào hệ thống.
            </p>

            <h2>2. Bảo Vệ Danh Sách Khách Mời & Lời Chúc</h2>
            <p>
                Danh sách khách mời và các phản hồi RSVP thuộc quyền sở hữu riêng tư của chủ hôn. Hệ thống không công khai danh sách khách mời và không cho phép khách mời này xem dữ liệu phản hồi của khách mời khác.
            </p>

            <h2>3. An Toàn Thông Tin & Lưu Trữ</h2>
            <p>
                Toàn bộ dữ liệu được truyền tải qua giao thức mã hóa HTTPS an toàn. {{ config('app.name') }} áp dụng các biện pháp kiểm soát an ninh nghiêm ngặt nhằm chống rò rỉ dữ liệu, truy cập trái phép hoặc phát tán hình ảnh không có sự chấp thuận.
            </p>

            <h2>4. Quyền Yêu Cầu Chỉnh Sửa & Xóa Dữ Liệu</h2>
            <p>
                Bạn có toàn quyền yêu cầu tải về toàn bộ dữ liệu hoặc xóa vĩnh viễn hồ sơ thiệp cưới cùng mọi dữ liệu liên quan khỏi hệ thống {{ config('app.name') }} sau khi đám cưới kết thúc.
            </p>
        </article>
    </div>
@endsection
