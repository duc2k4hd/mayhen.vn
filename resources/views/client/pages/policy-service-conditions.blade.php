@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('policy.service_conditions');
@endphp

@section('title', 'Các Điều Kiện & Hạn Chế Cung Cấp Hàng Hóa, Dịch Vụ | ' . $brandName)
@section('meta_description', 'Quy định chi tiết các điều kiện, giới hạn phạm vi lãnh thổ, thời hạn lưu trữ gói, số lượng đặt in tối thiểu và nội dung bị hạn chế khi sử dụng nền tảng ' . $brandName . ' (mayhen.vn).')
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Điều kiện & hạn chế cung cấp dịch vụ', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => 'Các Điều Kiện Hoặc Hạn Chế Trong Việc Cung Cấp Hàng Hóa, Dịch Vụ — ' . $brandName,
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
                <span class="mayhen_legal_kicker">Công Bố Bắt Buộc TMĐT • Mục 05</span>
                <span class="mayhen_legal_badge">Luật TMĐT 122/2025/QH15</span>
            </div>
            <h1 class="mayhen_legal_title">Các Điều Kiện &amp; Hạn Chế Trong Việc Cung Cấp Hàng Hóa, Dịch Vụ</h1>
            <p class="mayhen_legal_lead">
                Công bố minh bạch các điều kiện sử dụng, giới hạn kỹ thuật, phạm vi địa lý, số lượng tối thiểu và chuẩn mực nội dung khi sử dụng dịch vụ Thiệp Online &amp; In Thiệp Giấy tại {{ $brandName }} (mayhen.vn).
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
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục điều kiện cung cấp dịch vụ">
                <div class="mayhen_legal_toc_title">Mục Lục Chính Sách</div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#muc-1" class="mayhen_legal_toc_link is-active">1. Điều Kiện Đối Với Khách Hàng</a></li>
                    <li><a href="#muc-2" class="mayhen_legal_toc_link">2. Giới Hạn Theo Từng Gói Online</a></li>
                    <li><a href="#muc-3" class="mayhen_legal_toc_link">3. Điều Kiện &amp; Hạn Chế In Thiệp Giấy</a></li>
                    <li><a href="#muc-4" class="mayhen_legal_toc_link">4. Nội Dung Bị Cấm &amp; Hạn Chế</a></li>
                    <li><a href="#muc-5" class="mayhen_legal_toc_link">5. Phạm Vi Lãnh Thổ &amp; Thời Gian</a></li>
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
                        <span>Điều Kiện Đối Với Người Sử Dụng Dịch Vụ</span>
                    </h2>
                    <ul>
                        <li>Khách hàng là cá nhân từ đủ 18 tuổi trở lên có đầy đủ năng lực hành vi dân sự, hoặc tổ chức/doanh nghiệp thành lập hợp pháp theo quy định pháp luật Việt Nam.</li>
                        <li>Khách hàng cần cung cấp địa chỉ Email hợp lệ và thông tin liên hệ chính xác khi đăng ký tài khoản hoặc đặt dịch vụ để nhận xác nhận đơn hàng, hóa đơn và thông báo kỹ thuật.</li>
                    </ul>
                </section>

                <section id="muc-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Hạn Mức Kỹ Thuật &amp; Thời Hạn Lưu Trữ Theo Từng Gói Online</span>
                    </h2>
                    <ul>
                        <li><strong>Gói Trải Nghiệm (Free — 0đ):</strong> Giới hạn 01 thiệp, lưu trữ 07 ngày, sử dụng nhóm mẫu Cơ bản.</li>
                        <li><strong>Gói Thành Viên (Premium — 0đ):</strong> Yêu cầu đăng nhập tài khoản, giới hạn 01 thiệp, lưu trữ 30 ngày.</li>
                        <li><strong>Gói Hạnh Phúc (Plus — 99.000đ):</strong> Giới hạn 01 thiệp, lưu trữ 180 ngày kể từ ngày kích hoạt; không bao gồm quyền tách 2 đường dẫn Nhà Trai / Nhà Gái và không bao gồm quyền xuất bản Thiệp Sự Kiện Pro.</li>
                        <li><strong>Gói Trọn Vẹn (Wedding Pro VIP — 199.000đ):</strong> Giới hạn tối đa 03 thiệp, lưu trữ 730 ngày (02 năm), hỗ trợ tách riêng Lễ Thành Hôn &amp; Lễ Vu Quy và xuất bản Thiệp Sự Kiện &amp; Lễ Hội.</li>
                        <li><strong>Dung lượng tải ảnh:</strong> Ảnh tải lên hệ thống có định dạng JPG, PNG hoặc WebP, dung lượng tối đa 5MB/ảnh để đảm bảo tốc độ mở thiệp nhanh cho khách mời.</li>
                    </ul>
                </section>

                <section id="muc-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Điều Kiện &amp; Hạn Chế Đối Với Dịch Vụ In Thiệp Giấy</span>
                    </h2>
                    <ul>
                        <li><strong>Số lượng đặt in tối thiểu:</strong> Nhận in từ <strong>50 bộ thiệp trở lên</strong> cho mỗi đơn hàng (không áp dụng in lẻ dưới 50 bộ đối với dòng gia công khuôn ép kim hoặc dấu sáp thủ công).</li>
                        <li><strong>Yêu cầu duyệt bản maket:</strong> Mọi đơn in chỉ được đưa vào sản xuất sau khi khách hàng xác nhận duyệt bản thiết kế cuối cùng (kiểm tra chính tả tên hai họ, ngày âm – dương lịch, địa chỉ nhà hàng).</li>
                        <li><strong>Độ sai lệch màu sắc cho phép:</strong> Màu sắc in ấn thực tế trên giấy mỹ thuật có thể chênh lệch nhẹ (±5–8%) so với màn hình điện thoại/máy tính do khác biệt hệ màu RGB và CMYK.</li>
                    </ul>
                </section>

                <section id="muc-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Các Hành Vi &amp; Nội Dung Bị Cấm Trên Nền Tảng</span>
                    </h2>
                    <p>Để tuân thủ pháp luật Việt Nam và bảo vệ cộng đồng, {{ $brandName }} có quyền từ chối cung cấp dịch vụ hoặc tạm khóa đường dẫn thiệp nếu phát hiện nội dung vi phạm:</p>
                    <ul>
                        <li>Nội dung vi phạm pháp luật Việt Nam, thuần phong mỹ tục, kích động bạo lực, lừa đảo hoặc giả mạo danh tính cá nhân/tổ chức để trục lợi qua mã QR.</li>
                        <li>Hình ảnh, văn bản, tệp âm thanh vi phạm bản quyền sở hữu trí tuệ hoặc xâm phạm quyền riêng tư của bên thứ ba khi chưa có sự đồng ý.</li>
                        <li>Hành vi tấn công kỹ thuật, phát tán mã độc, thu thập dữ liệu trái phép hoặc lợi dụng nền tảng để gửi tin nhắn rác (spam).</li>
                    </ul>
                </section>

                <section id="muc-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Phạm Vi Địa Lý &amp; Thời Gian Cung Cấp</span>
                    </h2>
                    <ul>
                        <li><strong>Dịch vụ Thiệp Online:</strong> Cung cấp trực tuyến 24/7 trên phạm vi toàn cầu đối với khách hàng và khách mời có kết nối Internet.</li>
                        <li><strong>Dịch vụ In Thiệp Giấy:</strong> Phục vụ giao hàng trên toàn lãnh thổ Việt Nam (63 tỉnh, thành phố). Chưa hỗ trợ giao thiệp giấy trực tiếp ra nước ngoài trừ trường hợp có thỏa thuận riêng về chi phí chuyển phát quốc tế.</li>
                    </ul>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
