@extends('client.layouts.master')

@section('title', 'Điều Khoản Dịch Vụ — ' . config('app.name'))
@section('meta_description', 'Quy định và điều khoản sử dụng nền tảng thiệp cưới online ' . config('app.name') . '.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container_narrow mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Quy Định Sử Dụng</span>
            <h1 class="mayhen_info_title">Điều Khoản Dịch Vụ</h1>
            <p class="mayhen_info_lead">
                Áp dụng cho mọi khách hàng sử dụng dịch vụ tại mayhen.vn.
            </p>
        </header>

        <article class="mayhen_info_content">
            <h2>1. Chấp Thuận Điều Khoản</h2>
            <p>
                Khi truy cập và sử dụng {{ config('app.name') }} (mayhen.vn), bạn đồng ý tuân thủ các điều khoản và quy định được nêu tại trang này. Nếu không đồng ý, xin vui lòng ngừng sử dụng dịch vụ.
            </p>

            <h2>2. Quyền Sở Hữu Nội Dung & Hình Ảnh</h2>
            <p>
                Khách hàng chịu trách nhiệm hoàn toàn về tính hợp pháp của các hình ảnh, nội dung và thông tin tải lên nền tảng. Khách hàng cam đoan sở hữu hoặc có quyền sử dụng hợp pháp đối với các hình ảnh cưới được đăng tải.
            </p>

            <h2>3. Chính Sách Thanh Toán & Hoàn Tiền</h2>
            <p>
                Khách hàng được quyền xem thử và kiểm tra đầy đủ mọi tính năng trước khi quyết định nâng cấp gói dịch vụ. Khi giao dịch thanh toán thành công, dịch vụ xuất bản thiệp sẽ được kích hoạt tự động. Các yêu cầu hoàn tiền sẽ được xem xét nếu có lỗi kỹ thuật từ phía hệ thống {{ config('app.name') }} khiến thiệp không thể hoạt động.
            </p>

            <h2>4. Giới Hạn Trách Nhiệm</h2>
            <p>
                {{ config('app.name') }} không can thiệp và không chịu trách nhiệm đối với các giao dịch tài chính mừng cưới giữa khách mời và chủ hôn thông qua mã QR được cung cấp trong thiệp.
            </p>
        </article>
    </div>
@endsection
