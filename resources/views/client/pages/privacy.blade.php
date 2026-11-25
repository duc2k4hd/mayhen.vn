@extends('client.layouts.master')

@php
    $privacyBrandName = \App\Models\PlatformSetting::getBrandName();
    $privacyBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $privacyBrandNameLower = mb_strtolower($privacyBrandName, 'UTF-8');
@endphp

@section('title', 'Chính Sách Bảo Mật Dữ Liệu Cá Nhân — Luật 91/2025/QH15')
@section('meta_description', 'Chính sách bảo vệ dữ liệu cá nhân của ' . $privacyBrandName . ' (mayhen.vn) tuân thủ Luật 91/2025/QH15 và Nghị định 13/2023/NĐ-CP: bảo mật danh sách khách mời, mã hóa TLS 1.3 và quyền DSAR.')
@section('meta_keywords', 'chính sách bảo mật ' . $privacyBrandNameLower . ', bảo vệ dữ liệu cá nhân luật 91, quyền riêng tư thiệp cưới, nghị định 13 bảo vệ dữ liệu cá nhân, dsar ' . $privacyBrandNameLower . ', bảo mật mayhen.vn')
@section('canonical', route('privacy'))
@section('og_image', asset('storage/images/client/banners/banner-home.webp'))
@section('og_image_alt', $privacyBrandName . ' — Chính Sách Bảo Mật & Quyền Riêng Tư Dữ Liệu')
@section('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/legal.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/legal.js') }}"></script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/chinh-sach-bao-mat') . '/#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Chính sách bảo mật',
                    'item' => url('/chinh-sach-bao-mat'),
                ],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => url('/chinh-sach-bao-mat') . '/#webpage',
            'url' => url('/chinh-sach-bao-mat'),
            'name' => 'Chính Sách Bảo Mật Dữ Liệu Cá Nhân — ' . $privacyBrandName . ' (mayhen.vn)',
            'description' => 'Văn bản cam kết và quy chuẩn bảo vệ dữ liệu cá nhân tuân thủ Luật 91/2025/QH15 và Nghị định 13/2023/NĐ-CP tại nền tảng thiệp cưới ' . $privacyBrandName . '.',
            'inLanguage' => 'vi-VN',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => url('/') . '/#website',
                'name' => $privacyBrandName,
                'url' => url('/'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $privacyBrandName,
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('storage/images/client/business/mayhen-favicon.png'),
                ],
            ],
            'datePublished' => '2026-01-01T00:00:00+07:00',
            'dateModified' => '2026-11-17T00:00:00+07:00',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_legal_wrapper">
    <!-- 1. Hero Header -->
    <header class="mayhen_legal_hero">
        <div class="mayhen_legal_hero_inner">
            <div class="mayhen_legal_kicker_wrap">
                <span class="mayhen_legal_kicker">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Bảo Vệ Dữ Liệu Cá Nhân
                </span>
                <span class="mayhen_legal_badge">Tuân thủ Luật 91/2025/QH15</span>
            </div>
            <h1 class="mayhen_legal_title">Chính Sách Bảo Mật Dữ Liệu Cá Nhân</h1>
            <p class="mayhen_legal_lead">
                Cam kết tối cao của {{ $privacyBrandName }} về việc tôn trọng quyền riêng tư, bảo mật toàn vẹn hình ảnh cưới, thông tin khách mời và nhật ký tương tác theo quy định pháp luật Việt Nam.
            </p>
            <div class="mayhen_legal_meta_bar">
                <span class="mayhen_legal_meta_item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Căn cứ: Nghị định 13/2023/NĐ-CP &amp; Luật 91/2025/QH15
                </span>
                <span class="mayhen_legal_meta_dot">•</span>
                <span class="mayhen_legal_meta_item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Tiêu chuẩn: Mã hóa TLS 1.3 / AES-256
                </span>
                <span class="mayhen_legal_meta_dot">•</span>
                <span class="mayhen_legal_meta_item">Máy chủ: Việt Nam</span>
            </div>
        </div>
    </header>

    <!-- 2. Thân Trang (Sidebar + Nội Dung) -->
    <div class="mayhen_legal_body_section">
        <div class="mayhen_legal_container">
            <!-- Sidebar Mục Lục Cố Định -->
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục chính sách bảo mật">
                <div class="mayhen_legal_toc_title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    Mục Lục Chính Sách
                </div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#dieu-1" class="mayhen_legal_toc_link is-active">1. Cơ Sở Pháp Lý</a></li>
                    <li><a href="#dieu-2" class="mayhen_legal_toc_link">2. Dữ Liệu Chúng Tôi Thu Thập</a></li>
                    <li><a href="#dieu-3" class="mayhen_legal_toc_link">3. Mục Đích &amp; Nguyên Tắc</a></li>
                    <li><a href="#dieu-4" class="mayhen_legal_toc_link">4. Bảo Vệ Riêng Tư Đa Tầng</a></li>
                    <li><a href="#dieu-5" class="mayhen_legal_toc_link">5. Quyền Chủ Thể (DSAR)</a></li>
                    <li><a href="#dieu-6" class="mayhen_legal_toc_link">6. Chuẩn Mã Hóa &amp; Lưu Trữ</a></li>
                    <li><a href="#dieu-7" class="mayhen_legal_toc_link">7. Nhật Ký Kiểm Toán (Audit)</a></li>
                    <li><a href="#dieu-8" class="mayhen_legal_toc_link">8. Quản Lý Cookie &amp; Phiên</a></li>
                    <li><a href="#dieu-9" class="mayhen_legal_toc_link">9. Đầu Mối Liên Hệ DPO</a></li>
                </ul>

                @include('client.components.legal-switcher')

                <button type="button" id="mayhen_legal_print_btn" class="mayhen_legal_print_btn" aria-label="In văn bản này">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    In Văn Bản Này
                </button>
            </aside>

            <!-- Khung Nội Dung Pháp Lý Chi Tiết -->
            <main class="mayhen_legal_article">
                <!-- Điều 1 -->
                <section id="dieu-1" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">1</span>
                        <span>Cơ Sở Pháp Lý &amp; Tôn Chỉ Bảo Vệ Dữ Liệu</span>
                    </h2>
                    <p>
                        {{ $privacyBrandName }} (vận hành tại tên miền chính thức <a href="{{ route('home') }}">mayhen.vn</a>) hiểu rằng thiệp cưới chứa đựng những khoảnh khắc riêng tư, hình ảnh kỷ niệm vô giá và thông tin nhạy cảm của hai bên gia đình. Chúng tôi thiết lập Chính sách bảo mật này dựa trên sự tuân thủ nghiêm ngặt các quy định pháp luật hiện hành của Nước Cộng hòa Xã hội Chủ nghĩa Việt Nam:
                    </p>
                    <ul>
                        <li><strong>Luật Bảo vệ Dữ liệu Cá nhân</strong> (Luật số 91/2025/QH15 do Quốc hội ban hành).</li>
                        <li><strong>Nghị định số 13/2023/NĐ-CP</strong> của Chính phủ về Bảo vệ dữ liệu cá nhân.</li>
                        <li><strong>Luật An toàn thông tin mạng</strong> (Luật số 86/2015/QH13) và <strong>Luật Giao dịch điện tử</strong> (Luật số 20/2023/QH15).</li>
                    </ul>
                    <div class="mayhen_legal_callout">
                        <div class="mayhen_legal_callout_title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Tôn chỉ cốt lõi của {{ $privacyBrandName }}:
                        </div>
                        "Dữ liệu của bạn thuộc về bạn. Chúng tôi chỉ là người bảo hộ công nghệ đáng tin cậy. Tuyệt đối không khai thác thương mại, không bán hoặc chia sẻ số điện thoại, danh tính của bạn và người thân cho bất kỳ công ty quảng cáo hoặc bên thứ ba nào."
                    </div>
                </section>

                <!-- Điều 2 -->
                <section id="dieu-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Các Loại Dữ Liệu Cá Nhân Chúng Tôi Thu Thập</span>
                    </h2>
                    <p>
                        Chúng tôi chỉ thu thập các dữ liệu tối thiểu cần thiết để vận hành và cá nhân hóa tấm thiệp cưới của hai bạn, được phân loại thành 3 nhóm rõ ràng:
                    </p>
                    <div class="mayhen_legal_table_wrap">
                        <table class="mayhen_legal_table">
                            <thead>
                                <tr>
                                    <th>Nhóm Dữ Liệu</th>
                                    <th>Thông Tin Cụ Thể Thu Thập</th>
                                    <th>Mục Đích Sử Dụng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1. Dữ liệu Chủ thiệp (Cặp đôi)</strong></td>
                                    <td>Họ tên cô dâu chú rể, số điện thoại, email đăng ký, ngày cưới, địa điểm tổ chức tiệc, tên bố mẹ hai bên, số tài khoản ngân hàng thụ hưởng (để tạo VietQR mừng cưới).</td>
                                    <td>Khởi tạo thiệp cưới, gửi liên kết quản trị, thông báo khi có khách gửi lời chúc và hỗ trợ kích hoạt gói dịch vụ.</td>
                                </tr>
                                <tr>
                                    <td><strong>2. Dữ liệu Khách mời</strong></td>
                                    <td>Tên thiệp mời (ví dụ: Anh Nguyễn Văn An), số điện thoại (nếu chủ thiệp nhập), trạng thái phản hồi RSVP (tham dự/không tham dự), số suất đi cùng, yêu cầu khẩu vị ăn uống, điểm đón xe buýt và nội dung lời chúc mừng.</td>
                                    <td>Hiển thị lời chào cá nhân hóa trên thiệp, tổng hợp sĩ số khách mời cho nhà hàng và ghi vào Sổ ký tên lưu niệm.</td>
                                </tr>
                                <tr>
                                    <td><strong>3. Dữ liệu Kỹ thuật Tối thiểu</strong></td>
                                    <td>Địa chỉ IP truy cập (ẩn danh một phần), loại thiết bị và trình duyệt (User-Agent), thời điểm mở thiệp.</td>
                                    <td>Bảo vệ hệ thống chống tấn công DDoS, ngăn chặn spam lời chúc tự động và đo lường Uptime máy chủ.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Điều 3 -->
                <section id="dieu-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Mục Đích &amp; Nguyên Tắc Xử Lý Dữ Liệu (Data Minimization)</span>
                    </h2>
                    <p>
                        {{ $privacyBrandName }} tuân thủ triệt để nguyên tắc <strong>Tối thiểu hóa dữ liệu (Data Minimization)</strong>. Chúng tôi chỉ xử lý dữ liệu đúng với mục đích đã được người dùng đồng thuận:
                    </p>
                    <ul>
                        <li><strong>Chỉ phục vụ ngày cưới:</strong> Mọi dữ liệu thu thập chỉ nhằm phục vụ việc hiển thị thiệp mời, điểm danh quan khách tại sảnh tiệc và lưu giữ kỷ niệm cho cô dâu chú rể.</li>
                        <li><strong>Không bán dữ liệu (No-Sale Policy):</strong> Chúng tôi cam kết không kinh doanh, cho thuê, trao đổi hoặc tiết lộ thông tin khách mời cho các đơn vị bảo hiểm, bất động sản hay tiếp thị cuộc gọi rác.</li>
                        <li><strong>Bảo đảm quyền riêng tư giữa các khách:</strong> Khách mời khi mở thiệp chỉ nhìn thấy lời chào dành riêng cho mình. Hệ thống tuyệt đối không công khai danh sách toàn bộ khách mời và không để khách này xem phản hồi riêng tư của khách khác.</li>
                    </ul>
                </section>

                <!-- Điều 4 -->
                <section id="dieu-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Cơ Chế Bảo Vệ Quyền Riêng Tư Đa Tầng</span>
                    </h2>
                    <p>
                        Để đảm bảo ngày vui của hai bạn diễn ra ấm cúng và kín đáo, {{ $privacyBrandName }} trang bị sẵn bộ công cụ bảo vệ riêng tư chuyên sâu:
                    </p>
                    <ul>
                        <li><strong>Mật khẩu bảo vệ thiệp (Private Passcode):</strong> Cho phép bạn đặt mật mã truy cập. Khách mời mở thiệp bắt buộc phải nhập đúng mật mã mới có thể xem hình ảnh và nội dung thiệp cưới.</li>
                        <li><strong>Chặn công cụ tìm kiếm cào dữ liệu:</strong> Toàn bộ trang thiệp cưới khách mời và trang xem thử đều được gắn thẻ <code>meta name="robots" content="noindex, nofollow"</code> nhằm ngăn chặn Google, Bing thu thập số điện thoại hoặc ảnh gia đình của bạn lên mạng tìm kiếm công cộng.</li>
                        <li><strong>Phân quyền độc lập Nhà Trai &amp; Nhà Gái:</strong> Giúp gia đình hai bên chủ động quản lý số lượng bàn tiệc, danh sách khách và số tài khoản mừng cưới tách bạch, tránh nhầm lẫn.</li>
                    </ul>
                </section>

                <!-- Điều 5 -->
                <section id="dieu-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Quyền Của Chủ Thể Dữ Liệu (DSAR Protocols)</span>
                    </h2>
                    <p>
                        Theo quy định tại Điều 9 Luật Bảo vệ Dữ liệu Cá nhân số 91/2025/QH15, người dùng và khách mời có đầy đủ các quyền hợp pháp sau:
                    </p>
                    <ul>
                        <li><strong>Quyền được biết:</strong> Được thông báo minh bạch về loại dữ liệu đang được thu thập và mục đích xử lý.</li>
                        <li><strong>Quyền truy cập &amp; trích xuất:</strong> Bạn có quyền tải về toàn bộ dữ liệu thiệp cưới, ảnh kỷ niệm và danh sách khách mời bất cứ lúc nào dưới dạng tệp tin tiêu chuẩn.</li>
                        <li><strong>Quyền chỉnh sửa:</strong> Bạn có quyền sửa đổi thông tin ngày giờ tiệc cưới, thông tin gia đình hoặc danh sách khách mời trong suốt quá trình chuẩn bị hôn lễ.</li>
                        <li><strong>Quyền được lãng quên (Right to be Forgotten):</strong> Sau khi hôn lễ hoàn tất, bạn có toàn quyền yêu cầu xóa vĩnh viễn toàn bộ dữ liệu thiệp cưới, sổ lưu bút và ảnh cưới khỏi hệ thống máy chủ {{ $privacyBrandName }}.</li>
                    </ul>
                    <div class="mayhen_legal_callout mayhen_legal_callout--gold">
                        <div class="mayhen_legal_callout_title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                            Trung tâm Quyền riêng tư tự phục vụ:
                        </div>
                        Khách hàng có thể truy cập trực tiếp vào mục "Quản Lý Quyền Riêng Tư" trong Dashboard tài khoản để bật/tắt chế độ hiển thị, xuất file kiểm toán bảo mật hoặc gửi yêu cầu xóa dữ liệu hoàn toàn tự động chỉ với 1 cú click.
                    </div>
                </section>

                <!-- Điều 6 -->
                <section id="dieu-6" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">6</span>
                        <span>Biện Pháp Kỹ Thuật &amp; Chuẩn Mã Hóa Dữ Liệu</span>
                    </h2>
                    <p>
                        Hạ tầng kỹ thuật của {{ $privacyBrandName }} được xây dựng dựa trên các tiêu chuẩn an toàn an ninh mạng khắt khe nhất:
                    </p>
                    <ul>
                        <li><strong>Mã hóa đường truyền TLS 1.3:</strong> 100% kết nối giữa điện thoại khách mời và máy chủ {{ $privacyBrandName }} được mã hóa đường truyền bằng chứng chỉ SSL/TLS 256-bit cao cấp, triệt tiêu nguy cơ bị đánh cắp dữ liệu trên mạng Wi-Fi công cộng.</li>
                        <li><strong>Bảo vệ cơ sở dữ liệu (At-Rest Encryption):</strong> Dữ liệu nhạy cảm (mật khẩu người dùng, số điện thoại) được băm (hash) bằng thuật toán Bcrypt an toàn và lưu trữ trong hệ quản trị cơ sở dữ liệu được phân vùng bảo vệ nghiêm ngặt.</li>
                        <li><strong>Lưu trữ máy chủ tại Việt Nam:</strong> Tuân thủ Luật An ninh mạng, 100% dữ liệu của người dùng Việt Nam được lưu trữ tại trung tâm dữ liệu đạt chuẩn Tier 3 quốc tế đặt tại Hà Nội và TP. Hồ Chí Minh.</li>
                    </ul>
                </section>

                <!-- Điều 7 -->
                <section id="dieu-7" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">7</span>
                        <span>Nhật Ký Kiểm Toán Bảo Mật (Immutable Audit Logs)</span>
                    </h2>
                    <p>
                        Nhằm minh bạch hóa và phòng ngừa mọi sự can thiệp trái phép, hệ thống {{ $privacyBrandName }} duy trì cơ chế <strong>Nhật ký kiểm toán bất biến (Audit Logs)</strong>:
                    </p>
                    <ul>
                        <li>Ghi nhận thời điểm và địa chỉ IP của mọi thao tác quan trọng: Đăng nhập tài khoản, cập nhật thiệp cưới, xuất danh sách khách mời, đổi số tài khoản mừng cưới.</li>
                        <li>Nhật ký kiểm toán chỉ được phép ghi (Append-only) và không thể bị sửa đổi hoặc xóa bởi bất kỳ tài khoản người dùng nào, phục vụ công tác đối soát an toàn thông tin khi có yêu cầu.</li>
                    </ul>
                </section>

                <!-- Điều 8 -->
                <section id="dieu-8" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">8</span>
                        <span>Chính Sách Cookie &amp; Quản Lý Phiên Làm Việc</span>
                    </h2>
                    <p>
                        {{ $privacyBrandName }} chỉ sử dụng các loại Cookie thiết yếu (Essential Cookies) phục vụ cho việc vận hành kỹ thuật của trang web:
                    </p>
                    <ul>
                        <li><strong>Cookie xác thực phiên (Session Cookie):</strong> Giúp bạn duy trì trạng thái đăng nhập khi quản lý thiệp mà không cần đăng nhập lại ở mỗi thao tác.</li>
                        <li><strong>Mã phòng vệ CSRF Token:</strong> Bảo vệ tài khoản của bạn khỏi các cuộc tấn công giả mạo yêu cầu chéo trang (Cross-Site Request Forgery).</li>
                        <li><strong>Tuyệt đối không sử dụng Tracking Cookie bên thứ ba:</strong> Chúng tôi không cài đặt các đoạn mã theo dõi hành vi xuyên trang của các mạng lưới quảng cáo rác.</li>
                    </ul>
                </section>

                <!-- Điều 9 -->
                <section id="dieu-9" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">9</span>
                        <span>Thông Tin Liên Hệ Bộ Phận Bảo Vệ Dữ Liệu (DPO)</span>
                    </h2>
                    <p>
                        Nếu bạn có bất kỳ thắc mắc, phản ánh hoặc yêu cầu thực thi quyền riêng tư đối với dữ liệu cá nhân của mình, xin vui lòng liên hệ trực tiếp với Cán bộ Bảo vệ Dữ liệu (DPO) của {{ $privacyBrandName }}:
                    </p>
                    <ul>
                        <li><strong>Đơn vị chủ quản:</strong> Công ty TNHH Công Nghệ &amp; Sự Kiện {{ $privacyBrandName }} (mayhen.vn)</li>
                        <li><strong>Bộ phận chuyên trách:</strong> Ban An Toàn Thông Tin &amp; Bảo Vệ Dữ Liệu Cá Nhân</li>
                        <li><strong>Địa chỉ tiếp nhận:</strong> Tòa nhà {{ $privacyBrandName }}, Quận Tây Hồ, Thành phố Hà Nội</li>
                        <li><strong>Email chuyên trách bảo mật:</strong> <a href="mailto:privacy@mayhen.vn">privacy@mayhen.vn</a> hoặc <a href="mailto:support@mayhen.vn">support@mayhen.vn</a></li>
                        <li><strong>Hotline / Zalo tư vấn 24/7:</strong> 0968 988 636</li>
                    </ul>

                    <!-- Khung Chữ Ký & Cam Kết Pháp Lý -->
                    <div class="mayhen_legal_signature_box">
                        <div class="mayhen_legal_sig_info">
                            <div class="mayhen_legal_sig_title">BAN AN TOÀN THÔNG TIN {{ $privacyBrandNameUpper }}</div>
                            <div class="mayhen_legal_sig_desc">
                                Cam kết phụng sự với tinh thần trách nhiệm cao nhất • Giữ trọn sự riêng tư cho ngày hạnh phúc lứa đôi.
                            </div>
                        </div>
                        <a href="{{ route('contact') }}" class="mayhen_legal_switch_link" style="background: var(--mh-legal-primary); color: #FFF; border-color: var(--mh-legal-primary); padding: 10px 20px;">
                            <span>Gửi Yêu Cầu Bảo Mật</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
