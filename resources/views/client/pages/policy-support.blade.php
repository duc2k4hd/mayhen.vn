@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('policy.support');
@endphp

@section('title', 'Hình Thức Hỗ Trợ Trực Tuyến & Chăm Sóc Khách Hàng | ' . $brandName)
@section('meta_description', 'Tổng hợp các kênh hỗ trợ trực tuyến chính thức tại ' . $brandName . ' (mayhen.vn): Trung tâm Hướng dẫn 24/7, Trợ lý AI, Phiếu hỗ trợ Ticket, Email, Hotline và Zalo hỗ trợ tạo thiệp.')
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Hình thức hỗ trợ trực tuyến', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => 'Hình Thức Hỗ Trợ Trực Tuyến — ' . $brandName,
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
                <span class="mayhen_legal_kicker">Công Bố Nền Tảng TMĐT • Mục 07</span>
                <span class="mayhen_legal_badge">Hỗ Trợ Đa Kênh 08:00 – 22:00</span>
            </div>
            <h1 class="mayhen_legal_title">Hình Thức Hỗ Trợ Trực Tuyến Tại {{ $brandName }}</h1>
            <p class="mayhen_legal_lead">
                Danh sách đầy đủ các kênh hỗ trợ trực tuyến chính thức giúp khách hàng được giải đáp nhanh chóng từ khâu chọn mẫu thiệp, tùy chỉnh trong Studio, thanh toán cho đến gửi thiệp ngày cưới.
            </p>
            <div class="mayhen_legal_meta_bar">
                <span class="mayhen_legal_meta_item">Hỗ trợ trực tiếp: 08:00 – 22:00 hàng ngày</span>
                <span class="mayhen_legal_meta_dot">&bull;</span>
                <span class="mayhen_legal_meta_item">Tài liệu &amp; Trợ lý AI: 24/7</span>
            </div>
        </div>
    </header>

    <div class="mayhen_legal_body_section">
        <div class="mayhen_legal_container">
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục hình thức hỗ trợ trực tuyến">
                <div class="mayhen_legal_toc_title">Mục Lục Hỗ Trợ</div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#muc-1" class="mayhen_legal_toc_link is-active">1. Kênh Hỗ Trợ Tự Động 24/7</a></li>
                    <li><a href="#muc-2" class="mayhen_legal_toc_link">2. Kênh Hỗ Trợ Trực Tiếp Với Chuyên Viên</a></li>
                    <li><a href="#muc-3" class="mayhen_legal_toc_link">3. Cam Kết Thời Gian Phản Hồi</a></li>
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
                        <span>Kênh Hỗ Trợ Tự Động 24/7 Ngay Trên Website</span>
                    </h2>
                    <ul>
                        <li><strong>Trung Tâm Hướng Dẫn Sử Dụng (<a href="{{ route('docs.index') }}">/docs</a>):</strong> Hơn 50 bài hướng dẫn chi tiết từng thao tác từ tạo thiệp, đổi ảnh Smart Focal Point, quản lý khách mời RSVP đến cấu hình VietQR.</li>
                        <li><strong>Trợ Lý AI Trực Tuyến:</strong> Tích hợp trực tiếp trên website giúp giải đáp tức thì các câu hỏi về bảng giá, chọn mẫu thiệp, quy trình in ấn và hướng dẫn sử dụng 24/7.</li>
                        <li><strong>Hệ thống Câu Hỏi Thường Gặp (<a href="{{ route('faq') }}">/cau-hoi-thuong-gap</a>):</strong> Tổng hợp nhanh các tình huống phổ biến nhất của cô dâu chú rể.</li>
                    </ul>
                </section>

                <section id="muc-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Kênh Hỗ Trợ Trực Tiếp Với Đội Ngũ Chuyên Viên</span>
                    </h2>
                    <ul>
                        <li><strong>Cổng Liên Hệ &amp; Gửi Phiếu Hỗ Trợ:</strong> Gửi yêu cầu trực tuyến tại <a href="{{ route('contact') }}">{{ url('/lien-he') }}</a> (có mã theo dõi tiến độ xử lý).</li>
                        <li><strong>Email Hỗ Trợ Chính Thức:</strong> <a href="mailto:lienhe@mayhen.vn">lienhe@mayhen.vn</a> — tiếp nhận hỗ trợ kỹ thuật, đối soát đơn hàng, tư vấn in thiệp giấy và yêu cầu bảo mật dữ liệu.</li>
                        <li><strong>Hotline &amp; Zalo Official Account:</strong> Tư vấn trực tiếp, hỗ trợ chỉnh ảnh, duyệt bản maket in thiệp giấy từ 08:00 đến 22:00 tất cả các ngày trong tuần.</li>
                    </ul>
                </section>

                <section id="muc-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Cam Kết Thời Gian Phản Hồi (SLA)</span>
                    </h2>
                    <ul>
                        <li><strong>Hỗ trợ khẩn cấp ngày cưới / sự cố thanh toán:</strong> Phản hồi trong vòng <strong>15 – 30 phút</strong> trong khung giờ 08:00 – 22:00.</li>
                        <li><strong>Tư vấn mẫu thiệp &amp; báo giá in thiệp giấy:</strong> Phản hồi trong vòng <strong>01 – 02 giờ làm việc</strong>.</li>
                        <li><strong>Yêu cầu qua Email / Phiếu hỗ trợ ngoài giờ hành chính:</strong> Phản hồi chậm nhất vào sáng ngày làm việc kế tiếp (không quá 12 giờ).</li>
                    </ul>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
