@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('policy.payment');
@endphp

@section('title', 'Chính Sách Thanh Toán & Bảo Mật Giao Dịch | ' . $brandName)
@section('meta_description', 'Chính sách thanh toán chính thức tại ' . $brandName . ' (mayhen.vn): chuyển khoản ngân hàng qua mã VietQR tự động (SePay), thanh toán dịch vụ in thiệp giấy, đối soát đơn hàng và cam kết an toàn thanh toán.')
@section('canonical', $canonicalUrl)
@section('robots', 'index, follow, max-image-preview:large, max-snippet:-1')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/legal.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/legal.js') }}"></script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonicalUrl . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Chính sách thanh toán', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => 'Chính Sách Về Thanh Toán — ' . $brandName,
            'inLanguage' => 'vi-VN',
            'datePublished' => '2026-01-01T00:00:00+07:00',
            'dateModified' => '2026-11-22T00:00:00+07:00',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_legal_wrapper">
    <header class="mayhen_legal_hero">
        <div class="mayhen_legal_hero_inner">
            <div class="mayhen_legal_kicker_wrap">
                <span class="mayhen_legal_kicker">Công Bố Bắt Buộc TMĐT • Mục 04</span>
                <span class="mayhen_legal_badge">Thanh Toán An Toàn &amp; Tự Động</span>
            </div>
            <h1 class="mayhen_legal_title">Chính Sách Về Thanh Toán &amp; An Toàn Giao Dịch</h1>
            <p class="mayhen_legal_lead">
                Hướng dẫn các phương thức thanh toán được chấp nhận trên {{ $brandName }} (mayhen.vn), cơ chế xác nhận đơn hàng tự động và quy định bảo mật thông tin giao dịch của khách hàng.
            </p>
            <div class="mayhen_legal_meta_bar">
                <span class="mayhen_legal_meta_item">Hiệu lực: 01/01/2026</span>
                <span class="mayhen_legal_meta_dot">&bull;</span>
                <span class="mayhen_legal_meta_item">Cập nhật: Tháng 11/2026</span>
            </div>
        </div>
    </header>

    <div class="mayhen_legal_body_section">
        <div class="mayhen_legal_container">
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục chính sách thanh toán">
                <div class="mayhen_legal_toc_title">Mục Lục Chính Sách</div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#muc-1" class="mayhen_legal_toc_link is-active">1. Phương Thức Thanh Toán</a></li>
                    <li><a href="#muc-2" class="mayhen_legal_toc_link">2. Thanh Toán Gói Thiệp Online</a></li>
                    <li><a href="#muc-3" class="mayhen_legal_toc_link">3. Thanh Toán In Thiệp Giấy</a></li>
                    <li><a href="#muc-4" class="mayhen_legal_toc_link">4. Xử Lý Sai Sót Giao Dịch</a></li>
                    <li><a href="#muc-5" class="mayhen_legal_toc_link">5. Bảo Mật Thanh Toán</a></li>
                </ul>

                @include('client.components.legal-switcher')

                <button type="button" id="mayhen_legal_print_btn" class="mayhen_legal_print_btn">
                    In / Lưu PDF Văn Bản Này
                </button>
            </aside>

            <main class="mayhen_legal_article">
                <section id="muc-1" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">1</span>
                        <span>Các Phương Thức Thanh Toán Được Chấp Nhận</span>
                    </h2>
                    <p>
                        Nhằm đảm bảo tốc độ xử lý nhanh chóng, chính xác và không thu thêm phụ phí cổng thanh toán của khách hàng, <strong>{{ $brandName }}</strong> hiện áp dụng các phương thức thanh toán sau:
                    </p>
                    <ul>
                        <li><strong>Chuyển khoản ngân hàng nhanh 24/7 qua mã VietQR (NAPAS):</strong> Hỗ trợ quét mã trực tiếp bằng ứng dụng của toàn bộ các ngân hàng thương mại và ví điện tử hỗ trợ chuẩn VietQR tại Việt Nam.</li>
                        <li><strong>Thanh toán trực tiếp / COD (chỉ áp dụng cho phần còn lại của đơn hàng In Thiệp Giấy):</strong> Sau khi đặt cọc sản xuất và duyệt bản mẫu, khách hàng có thể thanh toán phần giá trị còn lại khi kiểm tra và nhận thiệp giấy.</li>
                    </ul>
                </section>

                <section id="muc-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Quy Trình Thanh Toán Đối Với Gói Thiệp Online (Plus &amp; Pro)</span>
                    </h2>
                    <ul>
                        <li><strong>Bước 1:</strong> Khách hàng chọn gói dịch vụ mong muốn tại trang <a href="{{ route('pricing') }}">Bảng Giá</a> và chuyển tới trang Thanh Toán.</li>
                        <li><strong>Bước 2:</strong> Hệ thống tự động sinh <strong>mã VietQR định danh riêng theo mã đơn hàng</strong> (hiển thị chính xác số tài khoản thụ hưởng, chủ tài khoản, số tiền và nội dung chuyển khoản).</li>
                        <li><strong>Bước 3:</strong> Khách hàng mở ứng dụng ngân hàng quét mã và xác nhận chuyển khoản đúng số tiền, đúng nội dung mã đơn hàng.</li>
                        <li><strong>Bước 4:</strong> Hệ thống đối soát tự động qua webhook ngân hàng xác nhận thanh toán và <strong>kích hoạt quyền lợi gói ngay lập tức trong 05 – 15 giây</strong>, đồng thời gửi biên nhận điện tử về tài khoản.</li>
                    </ul>
                </section>

                <section id="muc-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Quy Trình Thanh Toán Đối Với Dịch Vụ In Thiệp Cưới Giấy</span>
                    </h2>
                    <ul>
                        <li><strong>Đợt 1 (Đặt cọc sản xuất):</strong> Khách hàng thanh toán 50% tổng giá trị đơn hàng ngay sau khi chốt quy cách giấy, số lượng và duyệt bản thiết kế (maket) cuối cùng.</li>
                        <li><strong>Đợt 2 (Tất toán khi nhận hàng):</strong> Khách hàng thanh toán 50% giá trị còn lại bằng chuyển khoản hoặc tiền mặt (COD) sau khi nhận và kiểm tra chất lượng thiệp in.</li>
                    </ul>
                </section>

                <section id="muc-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Xử Lý Trường Hợp Chuyển Khoản Nhầm Nội Dung Hoặc Chuyển Dư</span>
                    </h2>
                    <p>
                        Trường hợp khách hàng chuyển khoản thiếu mã đơn hàng, sai cú pháp nội dung hoặc chuyển dư số tiền, hệ thống sẽ chưa kích hoạt tự động. Khách hàng chỉ cần gửi ảnh chụp biên lai giao dịch thành công tới email <a href="mailto:lienhe@mayhen.vn">lienhe@mayhen.vn</a> hoặc trang <a href="{{ route('contact') }}">Liên Hệ</a>. Bộ phận vận hành sẽ đối soát thủ công, kích hoạt gói và hoàn trả phần tiền chuyển dư (nếu có) về đúng tài khoản gốc trong vòng <strong>01 – 02 ngày làm việc</strong>.
                    </p>
                </section>

                <section id="muc-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Cam Kết Bảo Mật Thông Tin Thanh Toán</span>
                    </h2>
                    <p>
                        {{ $brandName }} <strong>không lưu trữ</strong> số thẻ tín dụng, mã bảo mật CVV, mật khẩu ngân hàng hay mã OTP của khách hàng trên máy chủ. Toàn bộ kết nối thanh toán và xác thực đơn hàng đều được mã hóa bằng giao thức <strong>HTTPS / TLS 1.3</strong> và kiểm tra chữ ký số đối soát chống giả mạo giao dịch.
                    </p>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
