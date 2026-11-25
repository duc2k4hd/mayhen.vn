@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('policy.delivery_refund');
@endphp

@section('title', 'Chính Sách Giao Hàng, Cung Cấp Dịch Vụ, Đổi Trả & Hoàn Tiền')
@section('meta_description', 'Chính sách phương thức cung cấp dịch vụ số, chấm dứt dịch vụ, giao nhận thiệp in giấy, kiểm hàng, đổi trả và hoàn tiền minh bạch tại ' . $brandName . ' (mayhen.vn).')
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Chính sách giao hàng & hoàn tiền', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => 'Chính Sách Giao Hàng, Cung Cấp Dịch Vụ, Đổi Trả & Hoàn Tiền — ' . $brandName,
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
                <span class="mayhen_legal_kicker">Công Bố Bắt Buộc TMĐT • Mục 06</span>
                <span class="mayhen_legal_badge">Áp Dụng Cả Dịch Vụ Số &amp; In Thiệp Giấy</span>
            </div>
            <h1 class="mayhen_legal_title">Chính Sách Cung Cấp Dịch Vụ, Giao Hàng, Đổi Trả &amp; Hoàn Tiền</h1>
            <p class="mayhen_legal_lead">
                Quy định đầy đủ về phương thức bàn giao dịch vụ trực tuyến, thời gian sản xuất và vận chuyển thiệp giấy, chính sách đồng kiểm, chấm dứt dịch vụ và hoàn tiền tại {{ $brandName }} (mayhen.vn).
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
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục chính sách giao hàng và hoàn tiền">
                <div class="mayhen_legal_toc_title">Mục Lục Chính Sách</div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#muc-1" class="mayhen_legal_toc_link is-active">1. Phương Thức Cung Cấp Dịch Vụ Online</a></li>
                    <li><a href="#muc-2" class="mayhen_legal_toc_link">2. Chấm Dứt &amp; Hoàn Tiền Dịch Vụ Online</a></li>
                    <li><a href="#muc-3" class="mayhen_legal_toc_link">3. Giao Hàng &amp; Kiểm Hàng In Thiệp Giấy</a></li>
                    <li><a href="#muc-4" class="mayhen_legal_toc_link">4. Đổi Trả &amp; Hoàn Tiền In Thiệp Giấy</a></li>
                    <li><a href="#muc-5" class="mayhen_legal_toc_link">5. Thời Gian &amp; Phương Thức Hoàn Tiền</a></li>
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
                        <span>Phương Thức Cung Cấp Dịch Vụ Số (Thiệp Cưới &amp; Thiệp Sự Kiện Online)</span>
                    </h2>
                    <ul>
                        <li><strong>Cung cấp tự động 24/7:</strong> Ngay sau khi giao dịch thanh toán VietQR được xác nhận (thường từ <strong>05 – 15 giây</strong>), hệ thống tự động nâng cấp tài khoản của khách hàng lên gói Plus hoặc Pro và mở khóa ngay toàn bộ tính năng tương ứng.</li>
                        <li><strong>Trải nghiệm trước khi thanh toán:</strong> Khách hàng luôn được tạo thử, phối màu, tải ảnh và xem trước hoàn toàn miễn phí trước khi quyết định thanh toán nâng cấp gói.</li>
                        <li><strong>Bàn giao sản phẩm số:</strong> Khách hàng nhận được đường dẫn công khai riêng biệt (ví dụ <code>mayhen.vn/thiep/...</code> hoặc <code>mayhen.vn/loi-moi/...</code>) cùng quyền truy cập Bảng điều khiển quản lý khách mời, RSVP và lời chúc trong suốt thời hạn gói.</li>
                    </ul>
                </section>

                <section id="muc-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Chính Sách Chấm Dứt Dịch Vụ &amp; Hoàn Tiền Đối Với Gói Online</span>
                    </h2>
                    <p><strong>Các trường hợp được hoàn tiền 100%:</strong></p>
                    <ul>
                        <li>Khách hàng bị trừ tiền nhiều lần (thanh toán trùng lặp) cho cùng một đơn hàng do lỗi hệ thống hoặc lỗi đường truyền ngân hàng.</li>
                        <li>Hệ thống xảy ra sự cố kỹ thuật nghiêm trọng kéo dài khiến khách hàng không thể xuất bản hoặc sử dụng thiệp cưới đúng ngày tổ chức mà {{ $brandName }} không thể khắc phục trong vòng 24 giờ.</li>
                        <li>Khách hàng yêu cầu hủy đơn trong vòng <strong>24 giờ</strong> kể từ lúc thanh toán và <strong>chưa xuất bản thiệp công khai</strong>, chưa sử dụng quyền lợi trả phí của gói.</li>
                    </ul>
                    <p><strong>Chấm dứt dịch vụ theo yêu cầu:</strong> Khách hàng có quyền tự ẩn/xóa thiệp hoặc yêu cầu chấm dứt tài khoản, xóa toàn bộ dữ liệu cá nhân bất kỳ lúc nào trong trang Cài đặt tài khoản theo Luật 91/2025/QH15. Đối với gói dịch vụ số đã được kích hoạt và xuất bản sử dụng thành công sau 24 giờ, {{ $brandName }} không áp dụng hoàn tiền trừ trường hợp lỗi kỹ thuật từ phía nền tảng.</p>
                </section>

                <section id="muc-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Chính Sách Giao Hàng &amp; Đồng Kiểm Đối Với Dịch Vụ In Thiệp Giấy</span>
                    </h2>
                    <ul>
                        <li><strong>Thời gian sản xuất &amp; gia công:</strong> Từ <strong>03 – 05 ngày làm việc</strong> (dòng Minimalist) và <strong>05 – 07 ngày làm việc</strong> (dòng Ép Kim / Phong thư Tem Sáp thủ công) kể từ thời điểm khách hàng duyệt bản maket cuối cùng.</li>
                        <li><strong>Thời gian vận chuyển toàn quốc:</strong> Nội thành Hà Nội &amp; TP. Hồ Chí Minh từ <strong>01 – 02 ngày</strong>; các tỉnh thành khác từ <strong>02 – 04 ngày làm việc</strong> qua đối tác vận chuyển uy tín (Viettel Post, GHTK, GrabExpress/Ahamove).</li>
                        <li><strong>Chính sách đồng kiểm:</strong> Khách hàng có quyền mở kiện hàng kiểm tra số lượng, chất lượng giấy và nội dung in ấn trước khi xác nhận nhận hàng và thanh toán phần còn lại.</li>
                        <li><strong>Trách nhiệm khi phát sinh chậm trễ:</strong> Nếu tiến độ sản xuất hoặc vận chuyển bị chậm so với cam kết, {{ $brandName }} có trách nhiệm thông báo sớm cho khách hàng; trường hợp chậm trễ làm ảnh hưởng đến lịch phát thiệp, khách hàng có quyền hủy đơn và nhận hoàn 100% tiền cọc.</li>
                    </ul>
                </section>

                <section id="muc-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Chính Sách Đổi Trả &amp; In Bù Đối Với Thiệp Giấy</span>
                    </h2>
                    <ul>
                        <li><strong>Đổi mới / In bù miễn phí 100%:</strong> Áp dụng trong vòng <strong>07 ngày</strong> kể từ ngày nhận hàng nếu sản phẩm in sai nội dung so với bản maket đã duyệt, lem mực, rách giấy, bong ép kim, thiếu số lượng hoặc hư hại trong quá trình vận chuyển. {{ $brandName }} chịu 100% chi phí vận chuyển hai chiều.</li>
                        <li><strong>Trường hợp không áp dụng đổi trả miễn phí:</strong> Sản phẩm in đúng 100% theo bản maket khách hàng đã ký/xác nhận duyệt nhưng khách hàng muốn thay đổi lại thông tin ngày giờ, địa điểm hoặc đổi mẫu giấy sau khi đã hoàn tất sản xuất. (Trường hợp này {{ $brandName }} sẽ hỗ trợ in lại với mức giá ưu đãi chỉ tính chi phí vật tư).</li>
                    </ul>
                </section>

                <section id="muc-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Thời Gian &amp; Phương Thức Hoàn Tiền</span>
                    </h2>
                    <p>
                        Mọi khoản hoàn tiền hợp lệ sẽ được {{ $brandName }} chuyển khoản trực tiếp về đúng số tài khoản ngân hàng của khách hàng trong vòng <strong>01 – 03 ngày làm việc</strong> kể từ thời điểm hai bên xác nhận phương án hoàn tiền. Khách hàng không phải chịu bất kỳ phí chuyển khoản hoàn tiền nào.
                    </p>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
