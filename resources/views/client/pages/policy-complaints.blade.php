@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('policy.complaints');
@endphp

@section('title', 'Phương Thức Tiếp Nhận & Giải Quyết Phản Ánh, Khiếu Nại')
@section('meta_description', 'Quy trình tiếp nhận, xác minh và giải quyết phản ánh, yêu cầu, khiếu nại của khách hàng tại nền tảng ' . $brandName . ' (mayhen.vn) theo Luật Thương mại điện tử 122/2025/QH15 và Luật Bảo vệ quyền lợi người tiêu dùng 19/2023/QH15.')
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Giải quyết phản ánh & khiếu nại', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => 'Phương Thức Tiếp Nhận & Giải Quyết Phản Ánh, Yêu Cầu, Khiếu Nại — ' . $brandName,
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
                <span class="mayhen_legal_kicker">Công Bố Bắt Buộc TMĐT • Mục 02</span>
                <span class="mayhen_legal_badge">Luật 122/2025/QH15 &amp; Luật 19/2023/QH15</span>
            </div>
            <h1 class="mayhen_legal_title">Phương Thức Tiếp Nhận &amp; Giải Quyết Phản Ánh, Yêu Cầu, Khiếu Nại</h1>
            <p class="mayhen_legal_lead">
                Quy định rõ ràng về các kênh tiếp nhận, thời hạn xử lý và trình tự giải quyết mọi phản ánh, yêu cầu hỗ trợ hoặc khiếu nại phát sinh trong quá trình sử dụng dịch vụ thiệp cưới online và in thiệp giấy tại {{ $brandName }} (mayhen.vn).
            </p>
            <div class="mayhen_legal_meta_bar">
                <span class="mayhen_legal_meta_item">Hiệu lực: 01/01/2026</span>
                <span class="mayhen_legal_meta_dot">&bull;</span>
                <span class="mayhen_legal_meta_item">Cập nhật: Tháng 11/2026</span>
                <span class="mayhen_legal_meta_dot">&bull;</span>
                <span class="mayhen_legal_meta_item">Áp dụng: Toàn quốc</span>
            </div>
        </div>
    </header>

    <div class="mayhen_legal_body_section">
        <div class="mayhen_legal_container">
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục chính sách giải quyết khiếu nại">
                <div class="mayhen_legal_toc_title">Mục Lục Chính Sách</div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#muc-1" class="mayhen_legal_toc_link is-active">1. Phạm Vi &amp; Nguyên Tắc</a></li>
                    <li><a href="#muc-2" class="mayhen_legal_toc_link">2. Các Kênh Tiếp Nhận Chính Thức</a></li>
                    <li><a href="#muc-3" class="mayhen_legal_toc_link">3. Quy Trình 4 Bước Xử Lý</a></li>
                    <li><a href="#muc-4" class="mayhen_legal_toc_link">4. Thời Hạn Giải Quyết</a></li>
                    <li><a href="#muc-5" class="mayhen_legal_toc_link">5. Giải Quyết Tranh Chấp</a></li>
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
                        <span>Phạm Vi Áp Dụng &amp; Nguyên Tắc Giải Quyết</span>
                    </h2>
                    <p>
                        Chính sách này áp dụng đối với toàn bộ khách hàng, người tiêu dùng và đối tác sử dụng dịch vụ trên nền tảng <strong>{{ $brandName }} (mayhen.vn)</strong>, bao gồm:
                    </p>
                    <ul>
                        <li>Dịch vụ khởi tạo, lưu trữ và xuất bản Thiệp Cưới Online &amp; Thiệp Sự Kiện (gói Free, Premium, Plus, Pro).</li>
                        <li>Dịch vụ In Thiệp Cưới Giấy Mỹ Thuật Cao Cấp và giao nhận thiệp vật lý.</li>
                        <li>Các vấn đề liên quan đến thanh toán đơn hàng, quyền truy cập tính năng, lỗi hiển thị kỹ thuật hoặc yêu cầu bảo vệ dữ liệu cá nhân theo Luật 91/2025/QH15.</li>
                    </ul>
                    <p>
                        <strong>Nguyên tắc xử lý:</strong> Mọi phản ánh, yêu cầu và khiếu nại đều được tiếp nhận công khai, minh bạch, giải quyết trên tinh thần thiện chí, ưu tiên bảo vệ quyền và lợi ích hợp pháp của người tiêu dùng theo đúng quy định của Luật Bảo vệ quyền lợi người tiêu dùng số 19/2023/QH15 và Luật Thương mại điện tử số 122/2025/QH15.
                    </p>
                </section>

                <section id="muc-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Các Kênh Tiếp Nhận Phản Ánh, Yêu Cầu &amp; Khiếu Nại Chính Thức</span>
                    </h2>
                    <p>Khách hàng có thể gửi phản ánh hoặc khiếu nại bất kỳ lúc nào qua một trong 4 kênh trực tiếp sau:</p>
                    <ul>
                        <li><strong>Cổng phiếu hỗ trợ trực tuyến (Ticket):</strong> Gửi trực tiếp tại trang <a href="{{ route('contact') }}">Liên Hệ &amp; Hỗ Trợ (mayhen.vn/lien-he)</a> hoặc từ Bảng điều khiển tài khoản khách hàng sau khi đăng nhập.</li>
                        <li><strong>Email bộ phận Chăm sóc Khách hàng &amp; Pháp chế:</strong> <a href="mailto:lienhe@mayhen.vn">lienhe@mayhen.vn</a> (tiêu đề ghi rõ mã đơn hàng hoặc đường dẫn thiệp cần xử lý).</li>
                        <li><strong>Đường dây nóng (Hotline) &amp; Zalo hỗ trợ:</strong> Hoạt động từ 08:00 đến 22:00 tất cả các ngày trong tuần (bao gồm Thứ 7, Chủ Nhật và ngày lễ).</li>
                        <li><strong>Văn bản trực tiếp / Bưu điện:</strong> Gửi về địa chỉ trụ sở đăng ký kinh doanh của đơn vị vận hành {{ $brandName }} được công bố công khai ở chân trang website.</li>
                    </ul>
                </section>

                <section id="muc-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Quy Trình 4 Bước Tiếp Nhận &amp; Xử Lý Khiếu Nại</span>
                    </h2>
                    <ul>
                        <li><strong>Bước 1 — Tiếp nhận &amp; Cấp mã hồ sơ:</strong> Bộ phận Chăm sóc Khách hàng ghi nhận yêu cầu, cấp mã phiếu theo dõi và gửi thông báo xác nhận đã tiếp nhận tới khách hàng trong vòng tối đa <strong>04 giờ làm việc</strong> (tối đa 24 giờ đối với ngày nghỉ lễ).</li>
                        <li><strong>Bước 2 — Xác minh thông tin &amp; Đối soát hệ thống:</strong> Đội ngũ kỹ thuật / vận hành kiểm tra lịch sử đơn hàng, nhật ký đối soát thanh toán VietQR, bản in mẫu (đối với thiệp giấy) hoặc nhật ký hệ thống liên quan.</li>
                        <li><strong>Bước 3 — Đề xuất phương án khắc phục:</strong> {{ $brandName }} thông báo kết quả xác minh kèm phương án giải quyết cụ thể (khắc phục kỹ thuật ngay lập tức, kích hoạt bù thời hạn gói, in bù miễn phí đối với lỗi in ấn hoặc hoàn tiền theo <a href="{{ route('policy.delivery_refund') }}">Chính sách hoàn tiền</a>).</li>
                        <li><strong>Bước 4 — Hoàn tất &amp; Lưu trữ hồ sơ:</strong> Sau khi khách hàng xác nhận hài lòng với phương án xử lý, phiếu khiếu nại được đóng lại và lưu trữ nhật ký kiểm toán phục vụ công tác thanh tra, hậu kiểm theo quy định.</li>
                    </ul>
                </section>

                <section id="muc-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Thời Hạn Giải Quyết Cam Kết</span>
                    </h2>
                    <ul>
                        <li><strong>Sự cố thanh toán / chưa tự động kích hoạt gói Online:</strong> Xử lý và kích hoạt trong vòng <strong>15 phút – 02 giờ</strong> sau khi nhận được ảnh biên lai chuyển khoản hợp lệ.</li>
                        <li><strong>Lỗi kỹ thuật hiển thị thiệp, lỗi tải ảnh, hỗ trợ dữ liệu cá nhân (DSAR):</strong> Phản hồi trong <strong>24 giờ</strong> và xử lý dứt điểm tối đa <strong>02 ngày làm việc</strong>.</li>
                        <li><strong>Khiếu nại chất lượng in thiệp giấy / giao thiếu số lượng:</strong> Xác minh trong <strong>24 giờ</strong> kể từ khi nhận ảnh/video đồng kiểm và hoàn tất in bù/đổi mới trong <strong>03 – 05 ngày làm việc</strong>.</li>
                        <li><strong>Các khiếu nại phức tạp cần phối hợp bên thứ ba (ngân hàng, đơn vị vận chuyển):</strong> Thời hạn xử lý tối đa không quá <strong>07 ngày làm việc</strong> kể từ ngày tiếp nhận đủ thông tin.</li>
                    </ul>
                </section>

                <section id="muc-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Cơ Chế Giải Quyết Tranh Chấp Ngoài Thương Lượng</span>
                    </h2>
                    <p>
                        Trong trường hợp hai bên không đạt được thỏa thuận thống nhất thông qua thương lượng và hòa giải trực tiếp, khách hàng có quyền gửi yêu cầu bảo vệ quyền lợi người tiêu dùng tới Hội Bảo vệ quyền lợi người tiêu dùng, Sở Công Thương nơi cư trú hoặc khởi kiện tại Tòa án nhân dân có thẩm quyền tại Việt Nam để giải quyết theo đúng quy định pháp luật hiện hành.
                    </p>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
