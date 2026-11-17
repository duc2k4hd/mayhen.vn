@extends('client.layouts.master')

@section('title', 'Điều Khoản Dịch Vụ — Quy Định Sử Dụng Nền Tảng Mây Hẹn (mayhen.vn)')
@section('meta_description', 'Quy định và điều khoản sử dụng nền tảng tạo thiệp cưới online cao cấp Mây Hẹn (mayhen.vn): quyền sở hữu trí tuệ, cơ chế mừng cưới VietQR, chính sách thanh toán và cam kết chất lượng.')
@section('meta_keywords', 'điều khoản dịch vụ mây hẹn, quy định sử dụng mayhen.vn, chính sách thiệp cưới online, điều khoản tạo thiệp cưới, bản quyền thiệp cưới mây hẹn')
@section('canonical', route('terms'))
@section('og_image', asset('storage/images/client/banners/banner-home.webp'))
@section('og_image_alt', 'Mây Hẹn — Điều Khoản Dịch Vụ & Quy Định Sử Dụng')
@section('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/legal.css') }}?v={{ time() }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/legal.js') }}?v={{ time() }}"></script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/dieu-khoan-dich-vu') . '/#breadcrumb',
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
                    'name' => 'Điều khoản dịch vụ',
                    'item' => url('/dieu-khoan-dich-vu'),
                ],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => url('/dieu-khoan-dich-vu') . '/#webpage',
            'url' => url('/dieu-khoan-dich-vu'),
            'name' => 'Điều Khoản Dịch Vụ — Mây Hẹn (mayhen.vn)',
            'description' => 'Văn bản quy định và điều khoản sử dụng nền tảng số hóa thiệp cưới trực tuyến cao cấp Mây Hẹn.',
            'inLanguage' => 'vi-VN',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => url('/') . '/#website',
                'name' => 'Mây Hẹn',
                'url' => url('/'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mây Hẹn',
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
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Văn Bản Pháp Lý
                </span>
                <span class="mayhen_legal_badge">Phiên bản 2.4</span>
            </div>
            <h1 class="mayhen_legal_title">Điều Khoản Dịch Vụ Mây Hẹn</h1>
            <p class="mayhen_legal_lead">
                Quy định sử dụng nền tảng tạo và điều phối thiệp cưới trực tuyến {{ config('app.name', 'Mây Hẹn') }} (mayhen.vn), nhằm đảm bảo quyền lợi tối cao và trải nghiệm ngày cưới an tâm nhất cho các cặp đôi.
            </p>
            <div class="mayhen_legal_meta_bar">
                <span class="mayhen_legal_meta_item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Hiệu lực: 01/01/2026
                </span>
                <span class="mayhen_legal_meta_dot">•</span>
                <span class="mayhen_legal_meta_item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Cập nhật mới nhất: Tháng 11/2026
                </span>
                <span class="mayhen_legal_meta_dot">•</span>
                <span class="mayhen_legal_meta_item">Áp dụng: Toàn quốc</span>
            </div>
        </div>
    </header>

    <!-- 2. Thân Trang (Sidebar + Nội Dung) -->
    <div class="mayhen_legal_body_section">
        <div class="mayhen_legal_container">
            <!-- Sidebar Mục Lục Cố Định -->
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục điều khoản dịch vụ">
                <div class="mayhen_legal_toc_title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    Mục Lục Điều Khoản
                </div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#dieu-1" class="mayhen_legal_toc_link is-active">1. Chấp Thuận Điều Khoản</a></li>
                    <li><a href="#dieu-2" class="mayhen_legal_toc_link">2. Tài Khoản &amp; Trách Nhiệm</a></li>
                    <li><a href="#dieu-3" class="mayhen_legal_toc_link">3. Sở Hữu Trí Tuệ &amp; Bản Quyền</a></li>
                    <li><a href="#dieu-4" class="mayhen_legal_toc_link">4. Mừng Cưới VietQR</a></li>
                    <li><a href="#dieu-5" class="mayhen_legal_toc_link">5. Gói Dịch Vụ &amp; Hoàn Tiền</a></li>
                    <li><a href="#dieu-6" class="mayhen_legal_toc_link">6. Trách Nhiệm Nội Dung</a></li>
                    <li><a href="#dieu-7" class="mayhen_legal_toc_link">7. Cam Kết Uptime (SLA)</a></li>
                    <li><a href="#dieu-8" class="mayhen_legal_toc_link">8. Lưu Trữ Dữ Liệu Thiệp</a></li>
                    <li><a href="#dieu-9" class="mayhen_legal_toc_link">9. Luật Áp Dụng &amp; Tranh Chấp</a></li>
                </ul>

                <div class="mayhen_legal_switcher">
                    <div class="mayhen_legal_toc_title">Văn Bản Liên Quan</div>
                    <a href="{{ route('terms') }}" class="mayhen_legal_switch_link is-current">
                        <span>Điều Khoản Dịch Vụ</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <a href="{{ route('privacy') }}" class="mayhen_legal_switch_link">
                        <span>Chính Sách Bảo Mật</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <a href="{{ route('faq') }}" class="mayhen_legal_switch_link">
                        <span>Câu Hỏi Thường Gặp (FAQ)</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

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
                        <span>Chấp Thuận Các Điều Khoản &amp; Phạm Vi Áp Dụng</span>
                    </h2>
                    <p>
                        Chào mừng bạn đến với <strong>Mây Hẹn</strong> (trang web chính thức tại địa chỉ <a href="{{ route('home') }}">mayhen.vn</a>). Bản Điều khoản dịch vụ này (gọi tắt là "Điều khoản") cấu thành một hợp đồng điện tử có giá trị pháp lý ràng buộc giữa người dùng (bao gồm cô dâu, chú rể, khách mời tham dự, đối tác studio cưới) và đơn vị vận hành nền tảng Mây Hẹn.
                    </p>
                    <p>
                        Bằng việc đăng ký tài khoản, truy cập, tạo thử thiệp, thanh toán nâng cấp gói hoặc sử dụng bất kỳ tính năng nào trên hệ thống, bạn xác nhận rằng bạn đã đủ năng lực hành vi dân sự theo quy định pháp luật Việt Nam, đã đọc kỹ, hiểu rõ và đồng ý tuân thủ toàn bộ các điều khoản được quy định tại đây. Nếu bạn không đồng ý với bất kỳ phần nào, xin vui lòng dừng sử dụng dịch vụ.
                    </p>
                    <div class="mayhen_legal_callout">
                        <div class="mayhen_legal_callout_title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            Lưu ý quan trọng:
                        </div>
                        Mây Hẹn giữ quyền sửa đổi hoặc bổ sung các điều khoản này theo thời gian nhằm đáp ứng quy định pháp luật hoặc nâng cấp dịch vụ. Mọi thay đổi sẽ được công bố công khai kèm ngày cập nhật mới nhất. Việc bạn tiếp tục sử dụng dịch vụ sau khi điều khoản sửa đổi có hiệu lực đồng nghĩa với việc bạn chấp thuận các thay đổi đó.
                    </div>
                </section>

                <!-- Điều 2 -->
                <section id="dieu-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Tài Khoản Người Dùng &amp; Trách Nhiệm Bảo Mật</span>
                    </h2>
                    <p>
                        Để quản lý thiệp cưới, xem danh sách khách mời và theo dõi lời chúc, người dùng cần tạo một tài khoản Mây Hẹn thông qua địa chỉ Email hoặc Đăng nhập một chạm với Google. Bạn cam kết:
                    </p>
                    <ul>
                        <li>Cung cấp thông tin chính xác, trung thực và cập nhật khi đăng ký tài khoản.</li>
                        <li>Chịu trách nhiệm hoàn toàn về việc bảo mật mật khẩu, liên kết đặt lại mật khẩu và mã xác thực hai lớp (nếu có).</li>
                        <li>Không chia sẻ thông tin đăng nhập hoặc cho phép bên thứ ba truy cập trái phép vào trang quản trị thiệp của bạn.</li>
                        <li>Thông báo ngay lập tức cho Mây Hẹn qua Hotline hoặc Email hỗ trợ nếu phát hiện bất kỳ dấu hiệu truy cập trái phép hoặc lỗ hổng bảo mật nào.</li>
                    </ul>
                    <p>
                        Mây Hẹn không chịu trách nhiệm đối với bất kỳ tổn thất, mất mát dữ liệu hoặc rò rỉ thông tin nào phát sinh do sự bất cẩn trong việc bảo quản thông tin tài khoản từ phía người dùng.
                    </p>
                </section>

                <!-- Điều 3 -->
                <section id="dieu-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Quyền Sở Hữu Trí Tuệ &amp; Bản Quyền Mẫu Thiệp</span>
                    </h2>
                    <p>
                        Mây Hẹn là chủ sở hữu hợp pháp duy nhất của mọi quyền sở hữu trí tuệ liên quan đến nền tảng, bao gồm nhưng không giới hạn ở:
                    </p>
                    <ul>
                        <li>Các mẫu thiết kế thiệp cưới độc quyền, bản vẽ đồ họa, bố cục editorial, font chữ thư pháp chọn lọc.</li>
                        <li>Hiệu ứng mở phong bì nhung lụa, tem sáp niêm phong (wax seal), hoạt ảnh stardust, hoa rơi canvas.</li>
                        <li>Toàn bộ mã nguồn phần mềm (HTML, CSS, JavaScript thuần, PHP Laravel backend) và cơ sở dữ liệu.</li>
                        <li>Thương hiệu, logo, slogan và nhận diện thương hiệu "Mây Hẹn — mayhen.vn".</li>
                    </ul>
                    <div class="mayhen_legal_callout mayhen_legal_callout--gold">
                        <div class="mayhen_legal_callout_title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Quyền sử dụng được cấp phép:
                        </div>
                        Khi kích hoạt gói dịch vụ, bạn được cấp một giấy phép cá nhân, không thể chuyển nhượng, không độc quyền để sử dụng mẫu thiệp cho mục đích ngày cưới của chính mình. Nghiêm cấm mọi hành vi sao chép, trích xuất mã nguồn (clone), phân phối lại hoặc sử dụng mẫu thiệp Mây Hẹn cho mục đích thương mại khi chưa có sự đồng ý bằng văn bản của chúng tôi.
                    </div>
                </section>

                <!-- Điều 4 -->
                <section id="dieu-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Tính Năng Mừng Cưới VietQR &amp; Miễn Trừ Trách Nhiệm Tài Chính</span>
                    </h2>
                    <p>
                        Mây Hẹn cung cấp tiện ích tạo mã VietQR theo chuẩn Napas 24/7 nhằm hỗ trợ quan khách gửi tiền mừng cưới một cách nhanh chóng và trang trọng:
                    </p>
                    <ul>
                        <li><strong>Chuyển khoản trực tiếp:</strong> Tiền mừng cưới từ khách mời được chuyển 100% thẳng vào tài khoản ngân hàng chính chủ của cô dâu hoặc chú rể.</li>
                        <li><strong>0đ phí hoa hồng:</strong> Mây Hẹn <em>tuyệt đối không đóng vai trò cổng thanh toán trung gian, không giữ tiền và không thu bất kỳ đồng phí hoa hồng nào</em> từ tiền mừng của hai bạn.</li>
                        <li><strong>Trách nhiệm số tài khoản:</strong> Bạn có nghĩa vụ tự kiểm tra kỹ lưỡng số tài khoản, tên ngân hàng và tên chủ tài khoản thụ hưởng khi thiết lập hộp mừng cưới. Mây Hẹn không chịu trách nhiệm đối với các sai sót chuyển tiền do bạn nhập sai thông tin ngân hàng.</li>
                    </ul>
                </section>

                <!-- Điều 5 -->
                <section id="dieu-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Các Gói Dịch Vụ, Kích Hoạt Tự Động &amp; Chính Sách Hoàn Tiền</span>
                    </h2>
                    <p>
                        Mây Hẹn áp dụng chính sách <strong>"Trải Nghiệm Trước — Hài Lòng Mới Thanh Toán"</strong>. Bạn được quyền tạo thử miễn phí, xem trước bản thiệp hoàn chỉnh trên điện thoại mà không bị ràng buộc bất kỳ khoản phí nào:
                    </p>
                    <div class="mayhen_legal_table_wrap">
                        <table class="mayhen_legal_table">
                            <thead>
                                <tr>
                                    <th>Gói Dịch Vụ</th>
                                    <th>Hình Thức Kích Hoạt</th>
                                    <th>Thời Gian Kích Hoạt</th>
                                    <th>Chính Sách Hoàn Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Gói Miễn Phí</strong></td>
                                    <td>Khởi tạo tức thì</td>
                                    <td>0 giây</td>
                                    <td>Miễn phí trọn đời</td>
                                </tr>
                                <tr>
                                    <td><strong>Gói Plus</strong></td>
                                    <td>Chuyển khoản SePay VietQR</td>
                                    <td>Tự động dưới 30 giây</td>
                                    <td>Hoàn tiền 100% nếu phát sinh lỗi kỹ thuật hệ thống trong 48h</td>
                                </tr>
                                <tr>
                                    <td><strong>Gói Pro VIP</strong></td>
                                    <td>Chuyển khoản SePay VietQR</td>
                                    <td>Tự động dưới 30 giây</td>
                                    <td>Hoàn tiền 100% nếu phát sinh lỗi kỹ thuật hệ thống trong 48h</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p>
                        <strong>Điều kiện hoàn tiền:</strong> Do đặc thù của sản phẩm số, Mây Hẹn xem xét hoàn tiền 100% khi và chỉ khi: Hệ thống gặp lỗi kỹ thuật nghiêm trọng từ phía máy chủ Mây Hẹn khiến thiệp cưới không thể truy cập được hoặc bị lỗi hiển thị cốt lõi mà đội ngũ kỹ thuật không thể khắc phục trong vòng 24 giờ kể từ khi nhận được phản ánh.
                    </p>
                </section>

                <!-- Điều 6 -->
                <section id="dieu-6" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">6</span>
                        <span>Trách Nhiệm Về Nội Dung &amp; Quy Trình Xử Lý Báo Cáo Vi Phạm</span>
                    </h2>
                    <p>
                        Người dùng chịu trách nhiệm pháp lý độc lập đối với toàn bộ hình ảnh, âm nhạc, video, lời ngỏ và thông tin cá nhân tải lên thiệp cưới của mình. Nghiêm cấm các hành vi:
                    </p>
                    <ul>
                        <li>Tải lên hình ảnh, nội dung khiêu dâm, đồi trụy, bạo lực hoặc vi phạm thuần phong mỹ tục dân tộc.</li>
                        <li>Đăng tải thông tin mạo danh cá nhân, tổ chức, hoặc thông tin vu khống, xúc phạm danh dự người khác.</li>
                        <li>Sử dụng thiệp cưới cho các hoạt động lừa đảo, huy động vốn trái phép hoặc phát tán mã độc hại.</li>
                    </ul>
                    <p>
                        Mây Hẹn có cơ chế tiếp nhận báo cáo vi phạm nội dung (Content Report). Khi nhận được khiếu nại có căn cứ về hành vi xâm phạm quyền riêng tư hoặc bản quyền, chúng tôi bảo lưu quyền tạm khóa thiệp cưới và yêu cầu chủ tài khoản giải trình theo quy định.
                    </p>
                </section>

                <!-- Điều 7 -->
                <section id="dieu-7" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">7</span>
                        <span>Cam Kết Thời Gian Hoạt Động (SLA Uptime 99.9%)</span>
                    </h2>
                    <p>
                        Mây Hẹn thấu hiểu ngày cưới là khoảnh khắc thiêng liêng duy nhất của cuộc đời. Chúng tôi cam kết vận hành hệ thống với độ ổn định máy chủ đạt tối thiểu <strong>99.9% Uptime</strong>:
                    </p>
                    <ul>
                        <li>Hệ thống máy chủ đám mây tốc độ cao đặt tại các trung tâm dữ liệu tiêu chuẩn quốc tế tại Việt Nam (Viettel IDC, FPT Telecom).</li>
                        <li>Mạng phân phối nội dung (CDN) đa điểm cùng công nghệ Cache tĩnh 304, đảm bảo thiệp cưới mở mượt mà ngay cả khi hàng chục nghìn khách mời truy cập cùng lúc trong giờ đón khách.</li>
                        <li>Bảo trì định kỳ: Chỉ thực hiện vào khung giờ khuya (02:00 – 04:00 sáng) và thông báo trước tối thiểu 24 giờ.</li>
                    </ul>
                </section>

                <!-- Điều 8 -->
                <section id="dieu-8" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">8</span>
                        <span>Lưu Trữ Dữ Liệu Thiệp &amp; Xuất Kỷ Niệm</span>
                    </h2>
                    <p>
                        Sau khi hôn lễ kết thúc, Mây Hẹn duy trì lưu giữ trang thiệp cưới và sổ lời chúc cho hai bạn theo thời hạn cam kết của từng gói dịch vụ. Bạn có toàn quyền:
                    </p>
                    <ul>
                        <li>Tải về toàn bộ danh sách khách mời, số tiền mừng cưới và lời chúc dưới dạng tệp Excel/PDF bất cứ lúc nào.</li>
                        <li>Bật chế độ khóa riêng tư để chỉ hai bạn mở xem lại kỷ niệm.</li>
                        <li>Yêu cầu xóa vĩnh viễn toàn bộ dữ liệu thiệp cưới khỏi hệ thống theo chính sách Quyền được lãng quên (Right to be Forgotten).</li>
                    </ul>
                </section>

                <!-- Điều 9 -->
                <section id="dieu-9" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">9</span>
                        <span>Luật Áp Dụng &amp; Giải Quyết Tranh Chấp</span>
                    </h2>
                    <p>
                        Bản Điều khoản dịch vụ này được điều chỉnh và giải thích theo các quy định của pháp luật nước Cộng hòa Xã hội Chủ nghĩa Việt Nam. Mọi bất đồng hoặc tranh chấp phát sinh liên quan đến việc sử dụng dịch vụ sẽ được các bên ưu tiên giải quyết thông qua thương lượng hòa giải thiện chí.
                    </p>
                    <p>
                        Trường hợp thương lượng không đạt được kết quả trong vòng 30 ngày kể từ ngày phát sinh tranh chấp, một trong các bên có quyền đưa vụ việc ra Tòa án nhân dân có thẩm quyền tại Thành phố Hà Nội để giải quyết theo luật định.
                    </p>

                    <!-- Khung Chữ Ký & Cam Kết Pháp Lý -->
                    <div class="mayhen_legal_signature_box">
                        <div class="mayhen_legal_sig_info">
                            <div class="mayhen_legal_sig_title">CÔNG TY TNHH CÔNG NGHỆ &amp; SỰ KIỆN MÂY HẸN</div>
                            <div class="mayhen_legal_sig_desc">
                                Đại diện pháp lý nền tảng thiệp cưới trực tuyến mayhen.vn • Tôn vinh nét đẹp văn hóa cưới số văn minh &amp; trang trọng.
                            </div>
                        </div>
                        <a href="{{ route('contact') }}" class="mayhen_legal_switch_link" style="background: var(--mh-legal-primary); color: #FFF; border-color: var(--mh-legal-primary); padding: 10px 20px;">
                            <span>Liên Hệ Ban Pháp Chế</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
