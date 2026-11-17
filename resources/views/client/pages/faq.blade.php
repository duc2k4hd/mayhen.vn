@extends('client.layouts.master')

@section('title', 'Câu Hỏi Thường Gặp (FAQ) Về Thiệp Cưới Online | Mây Hẹn (mayhen.vn)')
@section('meta_description', 'Tổng hợp và giải đáp 100% câu hỏi thường gặp về tạo thiệp cưới online Mây Hẹn: cách tạo thử miễn phí, tích hợp mã VietQR mừng cưới, tính năng RSVP xác nhận tham dự, bảo mật thông tin và gửi qua Zalo.')
@section('meta_keywords', 'câu hỏi thường gặp mây hẹn, faq thiệp cưới online, tạo thiệp cưới miễn phí, mừng cưới vietqr, rsvp đám cưới, gửi thiệp cưới zalo, bảo mật thiệp cưới online, mayhen.vn')
@section('canonical', route('faq'))
@section('og_image', asset('storage/images/client/banners/banner-home.webp'))
@section('og_image_alt', 'Mây Hẹn — Giải Đáp Thắc Mắc & Câu Hỏi Thường Gặp Thiệp Cưới Online')
@section('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/faq.css') }}?v={{ time() }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/faq.js') }}?v={{ time() }}"></script>
@endpush

@php
    $faqItems = [
        // Nhóm 1: Khởi tạo & Trải nghiệm mẫu thiệp
        [
            'id' => 'faq-tao-thu-mien-phi',
            'category' => 'create',
            'category_name' => 'Khởi Tạo & Đổi Mẫu',
            'question' => 'Tạo thử thiệp cưới trên Mây Hẹn có thực sự miễn phí 100% không?',
            'answer' => 'Hoàn toàn miễn phí 100%! Bạn có thể tự do chọn mẫu, nhập tên cô dâu chú rể, ngày cưới, địa điểm, tải ảnh cưới và nghe thử âm nhạc trực tiếp trên điện thoại. Bạn chỉ thanh toán khi đã hoàn toàn hài lòng và sẵn sàng xuất bản đường link chính thức để gửi tới quan khách.',
        ],
        [
            'id' => 'faq-thoi-gian-tao-thiep',
            'category' => 'create',
            'category_name' => 'Khởi Tạo & Đổi Mẫu',
            'question' => 'Thời gian để hoàn thành một tấm thiệp cưới online là bao lâu?',
            'answer' => 'Chỉ mất khoảng 3 đến 5 phút với Studio Tạo Thiệp V2 trực quan. Giao diện được tối ưu với 10 bước rõ ràng: từ Trang bìa, Lời ngỏ, Chuyện tình yêu, Sự kiện & Bản đồ đến Album ảnh và Mừng cưới. Mọi thao tác đều có gợi ý sẵn để bạn hoàn thiện nhanh nhất.',
        ],
        [
            'id' => 'faq-doi-mau-thiep',
            'category' => 'create',
            'category_name' => 'Khởi Tạo & Đổi Mẫu',
            'question' => 'Sau khi nhập thông tin, tôi có thể đổi sang mẫu thiệp khác được không?',
            'answer' => 'Hoàn toàn được! Mây Hẹn tích hợp công nghệ đồng bộ Live Sync thông minh. Khi bạn chọn đổi mẫu thiệp, toàn bộ nội dung ngày giờ, thông tin gia đình, album ảnh và câu chuyện tình yêu sẽ tự động chuyển sang mẫu mới nguyên vẹn 100% mà không cần phải nhập lại từ đầu.',
        ],
        [
            'id' => 'faq-phien-ban-nha-trai-nha-gai',
            'category' => 'create',
            'category_name' => 'Khởi Tạo & Đổi Mẫu',
            'question' => 'Tôi có thể tạo hai phiên bản riêng cho Nhà Trai và Nhà Gái không?',
            'answer' => 'Có! Mây Hẹn hỗ trợ tính năng nhân bản thiệp linh hoạt. Bạn có thể dùng chung bộ ảnh cưới và câu chuyện nhưng tùy chỉnh riêng địa chỉ tiệc, ngày giờ hôn lễ, tên đại diện song thân và số tài khoản mừng cưới cho từng bên gia đình.',
        ],

        // Nhóm 2: Tương tác khách mời & RSVP
        [
            'id' => 'faq-rsvp-hoat-dong-ra-sao',
            'category' => 'rsvp',
            'category_name' => 'RSVP & Đón Khách',
            'question' => 'Tính năng RSVP xác nhận tham dự hoạt động như thế nào?',
            'answer' => 'Khi khách mở link thiệp cưới, họ có thể chọn xác nhận "Sẽ tham dự" hoặc "Rất tiếc không thể đến", chọn số người đi cùng và gửi lời chúc mừng. Toàn bộ phản hồi được cập nhật tức thì vào trang Quản lý Khách mời của bạn, giúp việc chốt cỗ nhà hàng chính xác và tiết kiệm tối đa.',
        ],
        [
            'id' => 'faq-gioi-han-so-suat-khach',
            'category' => 'rsvp',
            'category_name' => 'RSVP & Đón Khách',
            'question' => 'Tôi có thể giới hạn số suất đi kèm của từng khách mời không (Headcount Policy)?',
            'answer' => 'Được! Mây Hẹn cho phép bạn cài đặt số suất tối đa cho từng thiệp (ví dụ: thiệp chỉ dành riêng cho 1 người hoặc tối đa 2 người lớn kèm 1 trẻ em). Khi khách chọn quá số suất quy định, hệ thống sẽ nhắc nhở lịch sự để bạn luôn kiểm soát được quy mô tiệc.',
        ],
        [
            'id' => 'faq-khao-sat-khau-vi-xe-dua-don',
            'category' => 'rsvp',
            'category_name' => 'RSVP & Đón Khách',
            'question' => 'Mây Hẹn có hỗ trợ khảo sát khẩu vị ăn uống và xe buýt đưa đón không?',
            'answer' => 'Có! Bạn có thể kích hoạt form khảo sát thực đơn (Ăn chay, kiêng hải sản, dị ứng gluten...) và đăng ký điểm đón xe buýt từ trung tâm. Số liệu được phân loại trực quan theo danh sách giúp bạn làm việc với bên cung cấp dịch vụ ẩm thực và vận chuyển cực kỳ nhàn nhã.',
        ],
        [
            'id' => 'faq-diem-danh-qr-ban-tiec',
            'category' => 'rsvp',
            'category_name' => 'RSVP & Đón Khách',
            'question' => 'Tính năng điểm danh QR Code và sắp xếp sơ đồ bàn tiệc hoạt động ra sao?',
            'answer' => 'Mỗi khách mời có thể được gửi một mã QR check-in riêng. Ngày cưới, ban lễ tân dùng điện thoại quét mã QR để biết ngay khách ngồi bàn số mấy (ví dụ: Bàn VIP 01) và tự động ghi nhận sĩ số tham dự thực tế theo thời gian thực.',
        ],

        // Nhóm 3: Mừng cưới VietQR
        [
            'id' => 'faq-vietqr-hoa-hong',
            'category' => 'vietqr',
            'category_name' => 'Mừng Cưới VietQR',
            'question' => 'Mã QR mừng cưới có bị thu phí hoa hồng hay chiết khấu không?',
            'answer' => 'Tuyệt đối không! Mây Hẹn tạo mã VietQR theo chuẩn Napas 24/7 kết nối thẳng vào tài khoản ngân hàng chính chủ của cô dâu chú rể. Tiền mừng từ khách chuyển 100% về tài khoản của bạn tức thì. Mây Hẹn không đóng vai trò trung gian và không thu bất kỳ đồng phí nào.',
        ],
        [
            'id' => 'faq-khach-mung-cuoi-nhu-the-nao',
            'category' => 'vietqr',
            'category_name' => 'Mừng Cưới VietQR',
            'question' => 'Khách mời mừng cưới qua mã QR như thế nào? Có cần nhớ số tài khoản không?',
            'answer' => 'Rất đơn giản! Khách chỉ cần mở thiệp, chọn "Gửi Mừng Cưới" là mã QR tự động hiển thị cùng số tài khoản và cú pháp lời chúc. Khách mở ứng dụng ngân hàng bất kỳ (Vietcombank, MB Bank, Techcombank, VPBank, Momo...) quét mã là hoàn tất trong 5 giây mà không cần nhớ hay nhập số tài khoản.',
        ],
        [
            'id' => 'faq-hai-tai-khoan-ngan-hang',
            'category' => 'vietqr',
            'category_name' => 'Mừng Cưới VietQR',
            'question' => 'Tôi có thể cài đặt cả 2 tài khoản của Chú Rể và Cô Dâu trên cùng một thiệp không?',
            'answer' => 'Có! Mây Hẹn hỗ trợ hiển thị song song hộp mừng cưới của Chú Rể và Cô Dâu kèm tab chuyển đổi tiện lợi, giúp bạn bè của từng người chọn đúng tài khoản để gửi trao tình cảm thuận tiện nhất.',
        ],

        // Nhóm 4: Gửi thiệp & Tương thích
        [
            'id' => 'faq-gui-thiep-qua-zalo',
            'category' => 'sharing',
            'category_name' => 'Gửi Thiệp & Zalo',
            'question' => 'Gửi link thiệp qua Zalo, Messenger hoặc SMS có hiển thị ảnh bìa to đẹp không?',
            'answer' => 'Rất đẹp và chỉn chu! Mọi mẫu thiệp Mây Hẹn đều được cấu hình chuẩn OpenGraph và thẻ xem trước mạng xã hội (Rich Social Preview). Khi gửi qua Zalo hay Facebook Messenger, đường link sẽ hiện ảnh đại diện sắc nét, tên cô dâu chú rể và lời mời trang trọng.',
        ],
        [
            'id' => 'faq-khach-co-can-tai-app-khong',
            'category' => 'sharing',
            'category_name' => 'Gửi Thiệp & Zalo',
            'question' => 'Khách mời có cần tải ứng dụng hay đăng ký tài khoản để xem thiệp không?',
            'answer' => 'Hoàn toàn không! Khách mời chỉ cần bấm vào đường link thiệp là có thể mở xem trực tiếp trên mọi trình duyệt điện thoại (Safari, Chrome, Cốc Cốc, Zalo In-App Browser) với tốc độ mượt mà, không yêu cầu cài đặt bất kỳ phần mềm nào.',
        ],
        [
            'id' => 'faq-nhac-nen-tu-dong-phat',
            'category' => 'sharing',
            'category_name' => 'Gửi Thiệp & Zalo',
            'question' => 'Nhạc nền thiệp cưới có tự động phát trên iPhone và Android không?',
            'answer' => 'Theo chính sách bảo mật âm thanh của Apple (iOS Safari) và Google (Android Chrome), trình duyệt chặn nhạc phát tự động khi chưa có tương tác chạm. Ngay khi khách mở phong bì thiệp hoặc chạm vào màn hình, giai điệu tình ca du dương sẽ tự động vang lên êm ái.',
        ],

        // Nhóm 5: Gói cước & Chi phí
        [
            'id' => 'faq-chi-phi-cac-goi-thiep',
            'category' => 'pricing',
            'category_name' => 'Gói Cước & Chi Phí',
            'question' => 'Chi phí các gói thiệp cưới tại Mây Hẹn là bao nhiêu?',
            'answer' => 'Mây Hẹn cung cấp 3 gói dịch vụ minh bạch: Gói Miễn Phí (Setup nhanh 2 phút, đầy đủ tính năng cốt lõi), Gói Plus (Hoa lá & Album 10 ảnh nghệ thuật) và Gói Pro VIP (Phong cách Tem sáp hoàng gia, Tạp chí Editorial, Quản lý khách mời toàn diện). Bạn có thể xem chi tiết tại trang Bảng Giá.',
        ],
        [
            'id' => 'faq-thoi-gian-luu-tru-thiep',
            'category' => 'pricing',
            'category_name' => 'Gói Cước & Chi Phí',
            'question' => 'Sau ngày cưới, trang thiệp cưới của tôi sẽ tồn tại trong bao lâu?',
            'answer' => 'Các gói Plus và Pro VIP lưu giữ thiệp cưới lâu dài sau ngày cưới để hai bạn luôn có thể mở lại ngắm nhìn như một kỷ vật tình yêu số trọn đời. Bạn cũng có thể tải về toàn bộ danh sách lời chúc và sổ ký tên bất cứ lúc nào.',
        ],
        [
            'id' => 'faq-thanh-toan-va-kich-hoat',
            'category' => 'pricing',
            'category_name' => 'Gói Cước & Chi Phí',
            'question' => 'Kích hoạt gói dịch vụ qua hình thức nào và mất bao lâu?',
            'answer' => 'Hệ thống tích hợp cổng thanh toán SePay VietQR tự động. Bạn chỉ cần quét mã QR bằng ứng dụng ngân hàng, hệ thống sẽ xác nhận giao dịch và kích hoạt thiệp ngay lập tức trong 30 giây (hoạt động 24/7 kể cả ban đêm và ngày nghỉ lễ).',
        ],

        // Nhóm 6: Bảo mật & Riêng tư
        [
            'id' => 'faq-bao-mat-thong-tin-ca-nhan',
            'category' => 'privacy',
            'category_name' => 'Bảo Mật & Riêng Tư',
            'question' => 'Thông tin cá nhân và hình ảnh cưới của chúng tôi có được bảo mật không?',
            'answer' => 'Cam kết bảo mật tuyệt đối! Mây Hẹn tuân thủ nghiêm ngặt Luật Bảo vệ Dữ liệu Cá nhân số 91/2025/QH15. Toàn bộ hình ảnh, số điện thoại và danh sách khách mời của bạn được mã hóa và lưu trữ tại cụm máy chủ an toàn đặt tại Việt Nam.',
        ],
        [
            'id' => 'faq-dat-mat-khau-xem-thiep',
            'category' => 'privacy',
            'category_name' => 'Bảo Mật & Riêng Tư',
            'question' => 'Tôi có thể cài đặt mật khẩu để chỉ người có mã mới xem được thiệp không?',
            'answer' => 'Có! Tính năng Private Passcode cho phép bạn đặt mật khẩu truy cập thiệp. Khách mở thiệp cần nhập đúng mã bảo vệ do bạn cung cấp, giúp không gian ngày vui của bạn luôn riêng tư và ấm cúng.',
        ],
        [
            'id' => 'faq-an-hoac-xoa-thiep',
            'category' => 'privacy',
            'category_name' => 'Bảo Mật & Riêng Tư',
            'question' => 'Tôi có thể ẩn hoặc xóa thiệp sau khi hôn lễ kết thúc không?',
            'answer' => 'Bất kỳ lúc nào! Bạn có toàn quyền bật/tắt chế độ xuất bản, chặn các công cụ tìm kiếm index nội dung hoặc xóa vĩnh viễn dữ liệu thiệp cưới ngay trong trang Quản lý tài khoản cá nhân.',
        ],
    ];

    // Tạo danh sách Question Schema cho JSON-LD
    $schemaQuestions = [];
    foreach ($faqItems as $item) {
        $schemaQuestions[] = [
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['answer'],
            ],
        ];
    }
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/cau-hoi-thuong-gap') . '/#breadcrumb',
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
                    'name' => 'Câu hỏi thường gặp',
                    'item' => url('/cau-hoi-thuong-gap'),
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/cau-hoi-thuong-gap') . '/#faqpage',
            'url' => url('/cau-hoi-thuong-gap'),
            'name' => 'Câu Hỏi Thường Gặp (FAQ) Về Thiệp Cưới Online — Mây Hẹn (mayhen.vn)',
            'description' => 'Giải đáp toàn diện các thắc mắc về tạo thiệp cưới online miễn phí, tích hợp mã VietQR mừng cưới, tính năng RSVP xác nhận tham dự và bảo mật thông tin tại Mây Hẹn.',
            'inLanguage' => 'vi-VN',
            'mainEntity' => $schemaQuestions,
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_faq_wrapper">
    <!-- 1. Hero Section & Ô Tìm Kiếm Realtime -->
    <section class="mayhen_faq_hero">
        <div class="mayhen_faq_hero_container">
            <span class="mayhen_faq_kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                Trợ Giúp &amp; Giải Đáp Thắc Mắc
            </span>
            <h1 class="mayhen_faq_title">Câu Hỏi Thường Gặp Về Thiệp Cưới Online</h1>
            <p class="mayhen_faq_lead">
                Tổng hợp đầy đủ và chi tiết mọi điều hai bạn cần biết để tự tin tạo nên tấm thiệp cưới online thanh lịch, trang trọng và trọn vẹn cảm xúc cùng Mây Hẹn.
            </p>

            <!-- Ô tìm kiếm câu hỏi tức thì -->
            <div class="mayhen_faq_search_box">
                <div class="mayhen_faq_search_input_wrap">
                    <svg class="mayhen_faq_search_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="mayhen_faq_search_input"
                        class="mayhen_faq_search_input"
                        placeholder="Tìm câu hỏi (ví dụ: tạo thử, vietqr, rsvp, đổi mẫu, zalo, giá...)"
                        autocomplete="off"
                        aria-label="Tìm kiếm câu hỏi thường gặp"
                    >
                    <button type="button" id="mayhen_faq_search_clear" class="mayhen_faq_search_clear" aria-label="Xóa từ khóa tìm kiếm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Điểm Nhấn Tin Cậy (Highlights Strip) -->
    <section class="mayhen_faq_highlights">
        <div class="mayhen_faq_highlights_grid">
            <div class="mayhen_faq_highlight_card">
                <div class="mayhen_faq_highlight_icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div>
                    <div class="mayhen_faq_highlight_title">Miễn Phí Tạo Thử 100%</div>
                    <div class="mayhen_faq_highlight_desc">Trải nghiệm không giới hạn trước khi xuất bản</div>
                </div>
            </div>

            <div class="mayhen_faq_highlight_card">
                <div class="mayhen_faq_highlight_icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="mayhen_faq_highlight_title">3 Phút Hoàn Thành</div>
                    <div class="mayhen_faq_highlight_desc">Studio V2 trực quan, dễ dàng thao tác</div>
                </div>
            </div>

            <div class="mayhen_faq_highlight_card">
                <div class="mayhen_faq_highlight_icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h.01M17 7h.01M7 17h.01M17 17h.01"/></svg>
                </div>
                <div>
                    <div class="mayhen_faq_highlight_title">0đ Phí Mừng Cưới</div>
                    <div class="mayhen_faq_highlight_desc">VietQR Napas chuyển thẳng tài khoản 24/7</div>
                </div>
            </div>

            <div class="mayhen_faq_highlight_card">
                <div class="mayhen_faq_highlight_icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <div class="mayhen_faq_highlight_title">Bảo Mật Chuẩn Luật 91</div>
                    <div class="mayhen_faq_highlight_desc">Mã hóa an toàn dữ liệu khách mời &amp; ảnh</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Bộ Lọc Chuyên Mục (Category Tabs) -->
    <section class="mayhen_faq_tabs_section">
        <nav class="mayhen_faq_tabs_nav" aria-label="Phân loại câu hỏi thường gặp">
            <button type="button" class="mayhen_faq_tab_btn is-active" data-tab="all">
                <span>Tất Cả Câu Hỏi</span>
                <span class="mayhen_faq_tab_count">{{ count($faqItems) }}</span>
            </button>
            <button type="button" class="mayhen_faq_tab_btn" data-tab="create">
                <span>Khởi Tạo &amp; Đổi Mẫu</span>
                <span class="mayhen_faq_tab_count">4</span>
            </button>
            <button type="button" class="mayhen_faq_tab_btn" data-tab="rsvp">
                <span>RSVP &amp; Đón Khách</span>
                <span class="mayhen_faq_tab_count">4</span>
            </button>
            <button type="button" class="mayhen_faq_tab_btn" data-tab="vietqr">
                <span>Mừng Cưới VietQR</span>
                <span class="mayhen_faq_tab_count">3</span>
            </button>
            <button type="button" class="mayhen_faq_tab_btn" data-tab="sharing">
                <span>Gửi Thiệp &amp; Zalo</span>
                <span class="mayhen_faq_tab_count">3</span>
            </button>
            <button type="button" class="mayhen_faq_tab_btn" data-tab="pricing">
                <span>Gói Cước &amp; Chi Phí</span>
                <span class="mayhen_faq_tab_count">3</span>
            </button>
            <button type="button" class="mayhen_faq_tab_btn" data-tab="privacy">
                <span>Bảo Mật &amp; Riêng Tư</span>
                <span class="mayhen_faq_tab_count">3</span>
            </button>
        </nav>
    </section>

    <!-- 4. Danh Sách Accordion FAQ Theo Nhóm -->
    <section class="mayhen_faq_content_section">
        <div class="mayhen_faq_container">
            @php
                $groups = [
                    'create' => [
                        'title' => '1. Khởi Tạo & Trải Nghiệm Mẫu Thiệp',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>',
                    ],
                    'rsvp' => [
                        'title' => '2. Tương Tác Khách Mời, RSVP & Đón Khách',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                    ],
                    'vietqr' => [
                        'title' => '3. Hộp Mừng Cưới & Mã VietQR 24/7',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
                    ],
                    'sharing' => [
                        'title' => '4. Gửi Thiệp, Tương Thích & Hiển Thị Mạng Xã Hội',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>',
                    ],
                    'pricing' => [
                        'title' => '5. Gói Cước, Thanh Toán & Thời Hạn Lưu Trữ',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="1" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                    ],
                    'privacy' => [
                        'title' => '6. Bảo Mật, Quyền Riêng Tư & An Toàn Dữ Liệu',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
                    ],
                ];

                $orderCounter = 1;
            @endphp

            @foreach ($groups as $groupKey => $groupInfo)
                @php
                    $groupItems = array_filter($faqItems, fn($item) => $item['category'] === $groupKey);
                @endphp
                <div class="mayhen_faq_group" data-group-category="{{ $groupKey }}">
                    <div class="mayhen_faq_group_header">
                        <div class="mayhen_faq_group_icon">
                            {!! $groupInfo['icon'] !!}
                        </div>
                        <h2 class="mayhen_faq_group_title">{{ $groupInfo['title'] }}</h2>
                    </div>

                    <div class="mayhen_faq_items_wrap">
                        @foreach ($groupItems as $item)
                            <article
                                class="mayhen_faq_card"
                                id="{{ $item['id'] }}"
                                data-category="{{ $item['category'] }}"
                                data-question="{{ $item['question'] }}"
                                data-answer="{{ strip_tags($item['answer']) }}"
                            >
                                <button
                                    type="button"
                                    class="mayhen_faq_question_btn"
                                    aria-expanded="false"
                                    aria-controls="{{ $item['id'] }}-answer"
                                >
                                    <div class="mayhen_faq_question_title_wrap">
                                        <span class="mayhen_faq_q_number">{{ $orderCounter++ }}</span>
                                        <span class="mayhen_faq_question_text">{{ $item['question'] }}</span>
                                    </div>
                                    <span class="mayhen_faq_toggle_icon" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </span>
                                </button>

                                <div id="{{ $item['id'] }}-answer" class="mayhen_faq_answer_wrap" role="region">
                                    <div class="mayhen_faq_answer_body">
                                        <p>{{ $item['answer'] }}</p>

                                        @if ($item['id'] === 'faq-tao-thu-mien-phi')
                                            <div class="mayhen_faq_callout">
                                                ✦ <strong>Mẹo nhỏ:</strong> Bạn có thể bắt đầu tạo thử ngay mà không cần nhập thẻ ngân hàng tại <a href="{{ route('templates.index') }}">Kho Mẫu Thiệp Cưới Mây Hẹn</a>.
                                            </div>
                                        @elseif ($item['id'] === 'faq-chi-phi-cac-goi-thiep')
                                            <div class="mayhen_faq_callout">
                                                ✦ Xem so sánh chi tiết tính năng của từng gói tại <a href="{{ route('pricing') }}">Bảng Giá Dịch Vụ Mây Hẹn</a>.
                                            </div>
                                        @elseif ($item['id'] === 'faq-rsvp-hoat-dong-ra-sao')
                                            <div class="mayhen_faq_callout">
                                                ✦ Tham khảo thêm sổ tay hướng dẫn quản lý khách tại <a href="{{ route('docs.index') }}">Trung Tâm Hướng Dẫn Mây Hẹn</a>.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Empty State khi tìm kiếm không có kết quả -->
            <div id="mayhen_faq_empty_state" class="mayhen_faq_empty_state">
                <svg class="mayhen_faq_empty_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
                <h3 class="mayhen_faq_empty_title">Không tìm thấy câu hỏi phù hợp</h3>
                <p class="mayhen_faq_empty_desc">
                    Rất tiếc, chúng tôi không tìm thấy câu hỏi nào chứa từ khóa <span id="mayhen_faq_empty_keyword" style="font-weight: 600; color: var(--mh-faq-primary);"></span>.
                </p>
                <button type="button" id="mayhen_faq_reset_search_btn" class="mayhen_faq_btn_secondary" style="margin: 0 auto;">
                    Xem Lại Tất Cả Câu Hỏi
                </button>
            </div>
        </div>
    </section>

    <!-- 5. Khung Hỗ Trợ Trực Tiếp (Support Call To Action) -->
    <section class="mayhen_faq_support_section">
        <div class="mayhen_faq_support_card">
            <span class="mayhen_faq_support_kicker">Hỗ Trợ Tận Tâm 24/7</span>
            <h2 class="mayhen_faq_support_title">Hai Bạn Vẫn Còn Thắc Mắc Khác?</h2>
            <p class="mayhen_faq_support_desc">
                Đội ngũ chuyên viên tư vấn của Mây Hẹn luôn sẵn sàng đồng hành, giải đáp chi tiết và hỗ trợ hai bạn tùy chỉnh tấm thiệp cưới hoàn hảo nhất cho ngày trọng đại.
            </p>
            <div class="mayhen_faq_support_actions">
                <a href="https://zalo.me/0968988636" target="_blank" rel="noopener noreferrer" class="mayhen_faq_btn_primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Tư Vấn Nhanh Qua Zalo</span>
                </a>
                <a href="{{ route('docs.index') }}" class="mayhen_faq_btn_secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>Xem Trung Tâm Hướng Dẫn</span>
                </a>
                <a href="{{ route('contact') }}" class="mayhen_faq_btn_secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span>Gửi Thư Cho Chúng Tôi</span>
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
