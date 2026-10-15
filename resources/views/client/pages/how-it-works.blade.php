@extends('client.layouts.master')

@section('title', 'Cách Làm Thiệp Cưới Online Từ A-Z ' . date('Y') . ' — Hướng Dẫn Chi Tiết | ' . config('app.name'))
@section('meta_description', 'Hướng dẫn cách làm thiệp cưới online trên điện thoại đơn giản trong 5 phút. Quy tắc điền thông tin 2 họ chuẩn nghi lễ, cài mã QR mừng cưới và cách gửi thiệp qua Zalo tinh tế.')
@section('meta_keywords', 'cách làm thiệp cưới online, hướng dẫn sử dụng thiệp cưới online, hướng dẫn tạo thiệp cưới online, tự làm thiệp cưới trên điện thoại, cách gửi thiệp cưới qua zalo, cách tạo mã qr mừng cưới, cách viết thiệp cưới online, ' . mb_strtolower(config('app.name')))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/huong-dan') . '/#breadcrumb',
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
                    'name' => 'Hướng dẫn làm thiệp cưới',
                    'item' => url('/huong-dan'),
                ],
            ],
        ],
        [
            '@type' => 'HowTo',
            '@id' => url('/huong-dan') . '/#howto',
            'name' => 'Cách Làm Thiệp Cưới Online Trên Điện Thoại Đơn Giản Trong 5 Phút',
            'description' => 'Hướng dẫn từng bước tự tạo thiệp cưới online sang trọng, chuẩn nghi thức Việt Nam, cài đặt mã VietQR mừng cưới và gửi lời mời qua Zalo.',
            'totalTime' => 'PT5M',
            'step' => [
                [
                    '@type' => 'HowToStep',
                    'position' => 1,
                    'name' => 'Chọn Mẫu Thiệp Cưới',
                    'text' => 'Truy cập kho mẫu thiệp cưới tại Mây Hẹn, lựa chọn phong cách yêu thích: Quiet Luxury, hoa lá lãng mạn, phong thư hoàng gia tem sáp hoặc tối giản tinh tế.',
                    'url' => route('templates.index'),
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 2,
                    'name' => 'Nhập Thông Tin Sự Kiện Cưới',
                    'text' => 'Điền thông tin cô dâu chú rể, tên phụ mẫu hai bên gia đình, thời gian tổ chức hôn lễ và địa chỉ sảnh cưới kèm định vị Google Maps.',
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 3,
                    'name' => 'Tải Ảnh Cưới & Cài Mã QR Mừng Cưới',
                    'text' => 'Tải lên các bức ảnh cưới đẹp nhất, chọn bài hát nhạc nền lãng mạn và nhập số tài khoản ngân hàng chính chủ để tạo mã VietQR mừng cưới tiện lợi.',
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 4,
                    'name' => 'Xem Thử Miễn Phí & Xuất Bản Gửi Khách',
                    'text' => 'Bấm xem trước trực tiếp trên điện thoại để kiểm tra toàn bộ thiệp. Khi hoàn toàn ưng ý, kích hoạt gói và sao chép đường link gửi cho khách mời qua Zalo hoặc Messenger.',
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/huong-dan') . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Tôi có thể tự tạo thiệp cưới trên điện thoại mà không cần máy tính được không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Hoàn toàn được. Toàn bộ giao diện của Mây Hẹn được tối ưu 100% cho điện thoại thông minh (iPhone, Android). Bạn chỉ cần mở trình duyệt Safari hoặc Chrome là có thể chọn mẫu, gõ chữ, tải ảnh cưới và xem trước trực tiếp mà không cần cài đặt ứng dụng.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Nhà Trai và Nhà Gái ở 2 tỉnh khác nhau thì nên điền thông tin thiệp cưới thế nào?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Với hệ thống nhân bản thông minh tại Mây Hẹn, bạn chỉ cần nhập thông tin 1 lần, hệ thống sẽ tự động tạo ra 2 đường link riêng biệt: 1 link Lễ Thành Hôn cho Nhà Trai và 1 link Lễ Vu Quy cho Nhà Gái với ngày giờ và sảnh tiệc riêng, giúp quan khách hai bên không bị nhầm lẫn.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Sau khi gửi thiệp cưới online cho khách, tôi có sửa lại ngày giờ hoặc địa điểm được không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Được và hoàn toàn miễn phí. Bất cứ khi nào bạn cập nhật thông tin trong trang quản lý, đường link thiệp cưới đã gửi cho khách sẽ tự động cập nhật ngay lập tức mà không cần phải gửi lại link mới.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Khách mời lớn tuổi có gặp khó khăn khi bấm vào xem thiệp cưới online không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Thiệp cưới online Mây Hẹn được thiết kế tối giản, phông chữ to rõ ràng, hình ảnh hiển thị ngay không bị che khuất bởi quảng cáo. Khách mời chỉ cần bấm vào đường link nhận qua tin nhắn Zalo là thiệp tự động mở ra ngay trên trình duyệt mà không cần đăng ký tài khoản.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Làm sao để biết khách mời đã nhận được thiệp và có tham dự tiệc hay không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Trên mỗi mẫu thiệp có tích hợp sẵn nút Xác Nhận Tham Dự (RSVP). Khách mời chỉ cần tích chọn Có tham dự, số người đi kèm và lời chúc phúc. Hệ thống sẽ tự động tổng hợp danh sách vào bảng quản lý để cô dâu chú rể nắm rõ số lượng bàn tiệc.',
                    ],
                ],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/how-it-works.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_guide_wrapper">
        <!-- ==========================================
             1. HERO HEADER
             ========================================== -->
        <header class="mayhen_guide_header">
            <span class="mayhen_guide_kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                Cẩm Nang Hướng Dẫn {{ date('Y') }}
            </span>
            <h1 class="mayhen_guide_title">
                Hướng Dẫn Cách Làm & Sử Dụng <br>
                <span class="mayhen_guide_title_accent">Thiệp Cưới Online Chi Tiết</span>
            </h1>
            <p class="mayhen_guide_desc">
                Cẩm nang toàn diện từ khâu chọn mẫu, cách điền thông tin chuẩn phong tục nghi lễ hai họ, cài đặt mã VietQR mừng cưới đến nghệ thuật gửi thiệp qua Zalo tinh tế và quản lý khách mời chu đáo.
            </p>

            <div class="mayhen_guide_trust_strip">
                <div class="mayhen_guide_trust_item">
                    <svg class="mayhen_guide_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Làm trên điện thoại trong 5 phút</span>
                </div>
                <div class="mayhen_guide_trust_item">
                    <svg class="mayhen_guide_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Điền thông tin chuẩn lễ nghi 2 họ</span>
                </div>
                <div class="mayhen_guide_trust_item">
                    <svg class="mayhen_guide_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Tạo VietQR nhận mừng cưới 0% phí</span>
                </div>
                <div class="mayhen_guide_trust_item">
                    <svg class="mayhen_guide_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Mẫu tin nhắn gửi Zalo lịch sự</span>
                </div>
            </div>
        </header>

        <!-- ==========================================
             2. QUY TRÌNH 4 BƯỚC TẠO THIỆP CƯỚI ONLINE
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="cac-buoc-tao-heading">
            <div class="mayhen_guide_section_header">
                <span class="mayhen_guide_section_kicker">Thao Tác Đơn Giản</span>
                <h2 id="cac-buoc-tao-heading" class="mayhen_guide_section_title">
                    4 Bước Tự Làm Thiệp Cưới Online Trên Điện Thoại
                </h2>
                <p class="mayhen_guide_section_desc">
                    Không cần biết đồ họa hay lập trình, bất kỳ ai cũng có thể hoàn thiện tấm thiệp cưới tuyệt đẹp chỉ qua 4 bước chạm.
                </p>
            </div>

            <div class="mayhen_guide_steps_list">
                <!-- Bước 1 -->
                <article class="mayhen_guide_step_item">
                    <div class="mayhen_guide_step_badge">01</div>
                    <div class="mayhen_guide_step_content">
                        <h3 class="mayhen_guide_step_heading">Khám Phá & Chọn Mẫu Thiệp Cưới Phù Hợp</h3>
                        <p class="mayhen_guide_step_text">
                            Truy cập kho 50 mẫu thiệp cưới thật tại Mây Hẹn. Bạn có thể lọc theo phong cách: phong thư hoàng gia tem sáp 3D mở nắp trang trọng, phong cách tối giản thanh lịch (Quiet Luxury), hoa lá nhẹ nhàng hoặc tạp chí Editorial hiện đại.
                        </p>
                        <div class="mayhen_guide_step_tips">
                            <strong>Mẹo nhỏ:</strong> Nếu tiệc cưới có nhiều người lớn tuổi, hãy ưu tiên các mẫu có phông chữ to, nét chữ rõ ràng và màu nền kem cát hoặc đỏ rượu truyền thống.
                        </div>
                    </div>
                </article>

                <!-- Bước 2 -->
                <article class="mayhen_guide_step_item">
                    <div class="mayhen_guide_step_badge">02</div>
                    <div class="mayhen_guide_step_content">
                        <h3 class="mayhen_guide_step_heading">Nhập Thông Tin Sự Kiện & Lễ Tiệc Cưới</h3>
                        <p class="mayhen_guide_step_text">
                            Điền họ tên cô dâu chú rể, tên phụ mẫu hai bên gia đình, ngày giờ tổ chức lễ cưới (hệ thống tự động đồng bộ cả ngày dương lịch và âm lịch) cùng địa chỉ sảnh cưới. Bạn chỉ cần dán link Google Maps, hệ thống sẽ tích hợp nút chỉ đường một chạm cho khách mời.
                        </p>
                        <div class="mayhen_guide_step_tips">
                            <strong>Lưu ý:</strong> Kiểm tra kỹ địa chỉ sảnh cưới và số tầng/sảnh tiệc cụ thể để khách mời tới nơi không bị bỡ ngỡ.
                        </div>
                    </div>
                </article>

                <!-- Bước 3 -->
                <article class="mayhen_guide_step_item">
                    <div class="mayhen_guide_step_badge">03</div>
                    <div class="mayhen_guide_step_content">
                        <h3 class="mayhen_guide_step_heading">Tải Ảnh Kỷ Niệm, Chọn Nhạc Nền & Cài Mã VietQR</h3>
                        <p class="mayhen_guide_step_text">
                            Tải lên từ 3 đến 10 bức ảnh cưới đẹp nhất để tạo album trượt mượt mà. Lựa chọn bài hát tình ca bạn yêu thích để phát tự động khi khách mở thiệp. Đồng thời nhập số tài khoản ngân hàng để hệ thống tạo mã VietQR mừng cưới chính chủ.
                        </p>
                        <div class="mayhen_guide_step_tips">
                            <strong>Mẹo chọn ảnh:</strong> Chọn các bức ảnh có bố cục dọc để hiển thị tràn viền sắc nét trên màn hình smartphone.
                        </div>
                    </div>
                </article>

                <!-- Bước 4 -->
                <article class="mayhen_guide_step_item">
                    <div class="mayhen_guide_step_badge">04</div>
                    <div class="mayhen_guide_step_content">
                        <h3 class="mayhen_guide_step_heading">Xem Trước Miễn Phí (Live Demo) & Xuất Bản Thiệp</h3>
                        <p class="mayhen_guide_step_text">
                            Bấm nút "Xem Thử" để trải nghiệm toàn bộ hiệu ứng mở thiệp, nhạc nền và bản đồ dẫn đường y như khách mời sẽ thấy. Sau khi kiểm tra mọi chi tiết đều hoàn hảo, bạn tiến hành xuất bản để nhận đường dẫn chính thức và mã QR riêng gửi cho quan khách.
                        </p>
                        <div class="mayhen_guide_step_tips">
                            <strong>Yên tâm tuyệt đối:</strong> Sau khi xuất bản, bạn vẫn có thể chỉnh sửa lại thông tin bất kỳ lúc nào hoàn toàn miễn phí.
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- ==========================================
             3. QUY TẮC ĐIỀN THÔNG TIN THIỆP CƯỚI CHUẨN LỄ NGHI
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="nghi-le-heading">
            <div class="mayhen_guide_section_header">
                <span class="mayhen_guide_section_kicker">Phong Tục Cưới Việt Nam</span>
                <h2 id="nghi-le-heading" class="mayhen_guide_section_title">
                    Cách Viết Thông Tin Thiệp Cưới Chuẩn Phong Tục Hai Họ
                </h2>
                <p class="mayhen_guide_section_desc">
                    Tránh các hiểu lầm và sơ suất trong cách dùng từ ngữ lễ nghi khi gửi thiệp tới người lớn tuổi trong gia đình.
                </p>
            </div>

            <div class="mayhen_guide_protocol_grid">
                <!-- Nhà Gái: Lễ Vu Quy -->
                <article class="mayhen_guide_protocol_card">
                    <span class="mayhen_guide_protocol_tag">Tiệc Nhà Gái</span>
                    <h3 class="mayhen_guide_protocol_title">Dùng Từ "Lễ Vu Quy"</h3>
                    <p class="mayhen_guide_protocol_desc">
                        "Vu Quy" là nghi thức nhà gái tiễn con gái về nhà chồng. Cụm từ này chỉ xuất hiện trên thiệp cưới của gia đình nhà gái khi tổ chức tiệc mời họ hàng, làng xóm và bạn bè của cô dâu.
                    </p>
                    <div class="mayhen_guide_protocol_example">
                        <strong>Cách xưng danh con cái:</strong><br>
                        • Con gái đầu: <em>Trưởng Nữ</em><br>
                        • Con gái thứ: <em>Thứ Nữ</em><br>
                        • Con gái út: <em>Út Nữ</em> hoặc <em>Quý Nữ</em>
                    </div>
                </article>

                <!-- Nhà Trai: Lễ Thành Hôn -->
                <article class="mayhen_guide_protocol_card">
                    <span class="mayhen_guide_protocol_tag">Tiệc Nhà Trai</span>
                    <h3 class="mayhen_guide_protocol_title">Dùng Từ "Lễ Thành Hôn"</h3>
                    <p class="mayhen_guide_protocol_desc">
                        "Thành Hôn" là lễ đón dâu và làm lễ gia tiên tại nhà trai, chính thức nhận cô dâu làm dâu con trong nhà. Đây là tiêu đề chuẩn trên thiệp mời của nhà trai gửi họ hàng và quan khách bên chú rể.
                    </p>
                    <div class="mayhen_guide_protocol_example">
                        <strong>Cách xưng danh con cái:</strong><br>
                        • Con trai đầu: <em>Trưởng Nam</em><br>
                        • Con trai thứ: <em>Thứ Nam</em><br>
                        • Con trai út: <em>Út Nam</em> hoặc <em>Quý Nam</em>
                    </div>
                </article>

                <!-- Tiệc Chung / Bạn Bè: Lễ Báo Hỷ -->
                <article class="mayhen_guide_protocol_card">
                    <span class="mayhen_guide_protocol_tag">Tiệc Đãi Bạn Bè</span>
                    <h3 class="mayhen_guide_protocol_title">Dùng Từ "Lễ Báo Hỷ" / Tiệc Mừng</h3>
                    <p class="mayhen_guide_protocol_desc">
                        Áp dụng khi cô dâu chú rể đã tổ chức hôn lễ tại quê nhà và tổ chức một buổi tiệc ngọt hoặc tiệc mặn thân mật tại nơi làm việc (Hà Nội, TP.HCM...) để chung vui cùng đồng nghiệp và bạn bè.
                    </p>
                    <div class="mayhen_guide_protocol_example">
                        <strong>Lưu ý tên phụ mẫu:</strong><br>
                        Nếu phụ mẫu đã qua đời, có thể ghi thêm từ <em>"Cố phụ"</em> (Cha đã mất) hoặc <em>"Cố mẫu"</em> (Mẹ đã mất) để thể hiện sự tưởng nhớ trang kính.
                    </div>
                </article>
            </div>
        </section>

        <!-- ==========================================
             4. CÀI ĐẶT MÃ VIETQR MỪNG CƯỚI
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="huong-dan-qr-heading">
            <div class="mayhen_guide_qr_box">
                <div class="mayhen_guide_qr_grid">
                    <div>
                        <span class="mayhen_guide_kicker" style="background: rgba(197, 168, 128, 0.2);">Tiện Ích 0% Phí</span>
                        <h2 id="huong-dan-qr-heading" class="mayhen_guide_title" style="font-size: var(--mayhen-font-size-h2); text-align: left; margin-bottom: var(--mayhen-space-sm);">
                            Cách Tạo Mã QR Mừng Cưới Chuyển Thẳng Ngân Hàng
                        </h2>
                        <p class="mayhen_guide_desc" style="text-align: left; margin: 0 0 var(--mayhen-space-md) 0;">
                            Nhiều bạn bè ở xa hoặc có việc bận không thể đến dự tiệc trực tiếp thường có nhu cầu mừng cưới qua tài khoản. Tính năng VietQR trên Mây Hẹn giúp quan khách mừng cưới nhanh chóng, tế nhị mà không cần hỏi xin số tài khoản riêng.
                        </p>

                        <div class="mayhen_guide_qr_points">
                            <div class="mayhen_guide_qr_point_item">
                                <span class="mayhen_guide_qr_point_num">1</span>
                                <p class="mayhen_guide_qr_point_text">
                                    <strong>Chọn ngân hàng & nhập số tài khoản:</strong> Chọn ngân hàng của bạn trong danh sách hơn 50 ngân hàng Việt Nam và nhập chính xác số tài khoản chính chủ.
                                </p>
                            </div>
                            <div class="mayhen_guide_qr_point_item">
                                <span class="mayhen_guide_qr_point_num">2</span>
                                <p class="mayhen_guide_qr_point_text">
                                    <strong>Tự động sinh mã VietQR chuẩn:</strong> Hệ thống tự động tạo mã QR có sẵn tên chủ tài khoản và nội dung chuyển khoản chúc phúc. Khách quét là tiền chuyển thẳng vào tài khoản của bạn, Mây Hẹn không giữ tiền và không thu phí giao dịch.
                                </p>
                            </div>
                            <div class="mayhen_guide_qr_point_item">
                                <span class="mayhen_guide_qr_point_num">3</span>
                                <p class="mayhen_guide_qr_point_text">
                                    <strong>Thiết lập 2 tài khoản riêng (Gói Pro):</strong> Bạn có thể cài 1 mã QR tài khoản chú rể trên link Nhà Trai và 1 mã QR tài khoản cô dâu trên link Nhà Gái để tiền mừng về đúng người.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_guide_qr_card_preview">
                        <div class="mayhen_guide_qr_dummy_icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="8" height="8" x="2" y="2" rx="1"/><rect width="8" height="8" x="14" y="2" rx="1"/><rect width="8" height="8" x="2" y="14" rx="1"/><rect width="4" height="4" x="14" y="14"/><rect width="2" height="2" x="20" y="14"/><rect width="2" height="2" x="14" y="20"/><rect width="2" height="2" x="18" y="18"/></svg>
                        </div>
                        <h4 style="font-family: var(--mayhen-font-serif); margin: 0 0 6px 0; font-size: 1.15rem;">Mã VietQR Tự Động</h4>
                        <p style="font-size: var(--mayhen-font-size-small); color: var(--mayhen-color-text-muted); margin: 0 0 12px 0;">
                            Quét 1 chạm qua ứng dụng bất kỳ ngân hàng nào tại Việt Nam.
                        </p>
                        <span style="display: inline-block; font-size: 0.75rem; background: var(--mayhen-color-primary-soft); color: var(--mayhen-color-primary); padding: 4px 12px; border-radius: var(--mayhen-radius-pill); font-weight: 600;">
                            100% Chuyển Thẳng Ngân Hàng
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             5. CÁCH GỬI THIỆP QUA ZALO & TIN NHẮN MẪU
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="gui-zalo-heading">
            <div class="mayhen_guide_section_header">
                <span class="mayhen_guide_section_kicker">Nghệ Thuật Mời Cưới</span>
                <h2 id="gui-zalo-heading" class="mayhen_guide_section_title">
                    Cách Gửi Thiệp Cưới Online Qua Zalo Lịch Sự & Tinh Tế
                </h2>
                <p class="mayhen_guide_section_desc">
                    Tuyệt đối không gửi một đường link cộc lốc vào group chat. Hãy gửi kèm lời nhắn riêng chân thành để khách mời cảm nhận được sự trân trọng.
                </p>
            </div>

            <div class="mayhen_guide_messages_grid">
                <!-- Mẫu 1: Bạn bè thân thiết -->
                <article class="mayhen_guide_msg_card">
                    <span class="mayhen_guide_msg_role">Mẫu 1</span>
                    <h3 class="mayhen_guide_msg_title">Gửi Bạn Bè & Đồng Nghiệp</h3>
                    <div class="mayhen_guide_msg_bubble">"Chào [Tên bạn], vào ngày [Ngày cưới] tới đây, mình và [Tên vợ/chồng] sẽ chính thức về chung một nhà.

Vì khoảng cách địa lý / để tiện theo dõi thông tin, mình xin phép gửi tấm thiệp cưới online nhỏ này tới bạn:
[Đường link thiệp cưới]

Sự hiện diện và lời chúc phúc của bạn là niềm hạnh phúc lớn của tụi mình. Hẹn gặp bạn trong ngày vui nhé!"</div>
                </article>

                <!-- Mẫu 2: Người lớn tuổi, họ hàng ở xa -->
                <article class="mayhen_guide_msg_card">
                    <span class="mayhen_guide_msg_role">Mẫu 2</span>
                    <h3 class="mayhen_guide_msg_title">Gửi Họ Hàng & Người Lớn Tuổi</h3>
                    <div class="mayhen_guide_msg_bubble">"Dạ cháu chào [Cô/Chú/Bác], cháu là [Tên], con của bố mẹ [Tên bố mẹ].

Ngày [Ngày cưới] sắp tới, gia đình cháu tổ chức lễ cưới cho hai cháu. Do đường sá xa xôi cháu chưa thể đến kính chuyển thiệp giấy tận tay, cháu xin phép được gửi thiệp cưới online kính mời [Cô/Chú/Bác]:
[Đường link thiệp cưới]

Gia đình cháu rất mong được đón tiếp [Cô/Chú/Bác] ạ!"</div>
                </article>

                <!-- Mẫu 3: Bạn cũ lâu ngày -->
                <article class="mayhen_guide_msg_card">
                    <span class="mayhen_guide_msg_role">Mẫu 3</span>
                    <h3 class="mayhen_guide_msg_title">Gửi Bạn Cũ Lâu Ngày Không Gặp</h3>
                    <div class="mayhen_guide_msg_bubble">"Chào [Tên], lâu rồi tụi mình chưa có dịp ngồi trò chuyện cùng nhau.

Tháng này mình có một cột mốc đặc biệt của cuộc đời, ngày [Ngày cưới] mình chính thức lập gia đình. Mình gửi tấm thiệp cưới online để bạn xem thông tin và chung vui cùng tụi mình nhé:
[Đường link thiệp cưới]

Nếu sắp xếp được thời gian, rất mong được gặp lại bạn hôm ấy!"</div>
                </article>
            </div>
        </section>

        <!-- ==========================================
             6. THEO DÕI KHÁCH MỜI RSVP
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="rsvp-guide-heading">
            <div class="mayhen_guide_section_header">
                <span class="mayhen_guide_section_kicker">Quản Lý Chu Đáo</span>
                <h2 id="rsvp-guide-heading" class="mayhen_guide_section_title">
                    Cách Theo Dõi Khách Xác Nhận Tham Dự (RSVP)
                </h2>
                <p class="mayhen_guide_section_desc">
                    Nỗi lo đặt thừa hoặc thiếu bàn tiệc cưới được giải quyết triệt để nhờ tính năng RSVP tự động trên thiệp.
                </p>
            </div>

            <div class="mayhen_guide_protocol_grid">
                <div class="mayhen_guide_protocol_card">
                    <span class="mayhen_guide_protocol_tag">Khách Thao Tác</span>
                    <h3 class="mayhen_guide_protocol_title">Khách Bấm Xác Nhận 1 Chạm</h3>
                    <p class="mayhen_guide_protocol_desc">
                        Ngay trên giao diện thiệp, khách bấm nút "Xác Nhận Tham Dự", chọn: Sẽ tham dự (đi 1 mình hoặc kèm người thân/trẻ em), hoặc Không thể đến dự (kèm gửi lời chúc phúc).
                    </p>
                </div>

                <div class="mayhen_guide_protocol_card">
                    <span class="mayhen_guide_protocol_tag">Hệ Thống Thống Kê</span>
                    <h3 class="mayhen_guide_protocol_title">Tự Động Báo Về Bảng Quản Trị</h3>
                    <p class="mayhen_guide_protocol_desc">
                        Cô dâu chú rể mở trang quản trị là thấy ngay biểu đồ: Bao nhiêu khách chắc chắn đến, bao nhiêu suất ăn người lớn, bao nhiêu trẻ nhỏ và các ghi chú ăn chay đặc biệt.
                    </p>
                </div>

                <div class="mayhen_guide_protocol_card">
                    <span class="mayhen_guide_protocol_tag">Xuất File Báo Cáo</span>
                    <h3 class="mayhen_guide_protocol_title">Xuất File Excel Gửi Nhà Hàng</h3>
                    <p class="mayhen_guide_protocol_desc">
                        Chỉ với 1 cú click chuột, bạn có thể tải toàn bộ danh sách khách mời kèm số điện thoại và lời chúc về máy tính dưới dạng file Excel để gửi thẳng cho bên đặt tiệc.
                    </p>
                </div>
            </div>
        </section>

        <!-- ==========================================
             7. 5 SAI LẦM THƯỜNG GẶP KHI TỰ LÀM THIỆP ONLINE
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="sai-lam-heading">
            <div class="mayhen_guide_section_header">
                <span class="mayhen_guide_section_kicker">Lưu Ý Quan Trọng</span>
                <h2 id="sai-lam-heading" class="mayhen_guide_section_title">
                    5 Sai Lầm Thường Gặp Khi Làm Thiệp Cưới Online & Cách Khắc Phục
                </h2>
                <p class="mayhen_guide_section_desc">
                    Tránh những sự cố đáng tiếc trước khi bấm gửi đường dẫn thiệp mời tới hàng trăm khách quý.
                </p>
            </div>

            <div class="mayhen_guide_mistakes_grid">
                <div class="mayhen_guide_mistake_card">
                    <div class="mayhen_guide_mistake_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_guide_mistake_title">1. Tải ảnh dung lượng quá nặng (>10MB/ảnh)</h3>
                        <p class="mayhen_guide_mistake_text">
                            Ảnh gốc máy cơ nặng hàng chục MB sẽ khiến thiệp mở chậm khi khách dùng 4G. <em>Cách khắc phục:</em> Hệ thống Mây Hẹn có tích hợp sẵn bộ nén ảnh tự động chuẩn WebP, giữ nguyên độ nét mà dung lượng cực nhẹ.
                        </p>
                    </div>
                </div>

                <div class="mayhen_guide_mistake_card">
                    <div class="mayhen_guide_mistake_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_guide_mistake_title">2. Gõ nhầm số tài khoản hoặc sai tên ngân hàng</h3>
                        <p class="mayhen_guide_mistake_text">
                            Khách quét mã mừng cưới chuyển nhầm sang tài khoản người khác. <em>Cách khắc phục:</em> Sau khi tạo thiệp, hãy dùng điện thoại của người thân quét thử mã QR để kiểm tra đúng họ tên chủ thẻ trước khi chia sẻ.
                        </p>
                    </div>
                </div>

                <div class="mayhen_guide_mistake_card">
                    <div class="mayhen_guide_mistake_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_guide_mistake_title">3. Quên kiểm tra định vị Google Maps</h3>
                        <p class="mayhen_guide_mistake_text">
                            Nhiều nhà hàng tiệc cưới có tên giống nhau ở các chi nhánh khác nhau. <em>Cách khắc phục:</em> Mở ứng dụng Google Maps, tìm đúng chi nhánh sảnh tiệc của bạn, bấm nút "Chia sẻ" và dán đúng đường link đó vào thiệp.
                        </p>
                    </div>
                </div>

                <div class="mayhen_guide_mistake_card">
                    <div class="mayhen_guide_mistake_icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_guide_mistake_title">4. Gửi thiệp mời quá sát ngày cưới (dưới 5 ngày)</h3>
                        <p class="mayhen_guide_mistake_text">
                            Gửi thiệp quá sát ngày khiến bạn bè bị động trong việc sắp xếp lịch trình. <em>Cách khắc phục:</em> Nên gửi thiệp cưới online trước ngày cưới từ 2 đến 3 tuần là khoảng thời gian lý tưởng nhất.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             8. CÂU HỎI THƯỜNG GẶP (FAQ CHUẨN SEO)
             ========================================== -->
        <section class="mayhen_guide_section" aria-labelledby="faq-huong-dan-heading">
            <div class="mayhen_guide_section_header">
                <span class="mayhen_guide_section_kicker">Hỏi & Đáp</span>
                <h2 id="faq-huong-dan-heading" class="mayhen_guide_section_title">
                    Câu Hỏi Thường Gặp Khi Tự Làm Thiệp Cưới Online
                </h2>
                <p class="mayhen_guide_section_desc">
                    Giải đáp tất cả thắc mắc kỹ thuật và thao tác thực tế cho các cặp đôi.
                </p>
            </div>

            <div class="mayhen_guide_faq_list">
                <article class="mayhen_guide_faq_item">
                    <h3 class="mayhen_guide_faq_q">
                        <span class="mayhen_guide_faq_q_badge">Q.</span>
                        Tôi có thể tự tạo thiệp cưới trên điện thoại mà không cần máy tính được không?
                    </h3>
                    <p class="mayhen_guide_faq_a">
                        Hoàn toàn được. Toàn bộ giao diện của Mây Hẹn được tối ưu 100% cho điện thoại thông minh (iPhone, Android). Bạn chỉ cần mở trình duyệt Safari hoặc Chrome là có thể chọn mẫu, gõ chữ, tải ảnh cưới và xem trước trực tiếp mà không cần cài đặt ứng dụng.
                    </p>
                </article>

                <article class="mayhen_guide_faq_item">
                    <h3 class="mayhen_guide_faq_q">
                        <span class="mayhen_guide_faq_q_badge">Q.</span>
                        Nhà Trai và Nhà Gái ở 2 tỉnh khác nhau thì nên điền thông tin thiệp cưới thế nào?
                    </h3>
                    <p class="mayhen_guide_faq_a">
                        Với hệ thống nhân bản thông minh tại Mây Hẹn (Gói Pro), bạn chỉ cần nhập thông tin 1 lần, hệ thống sẽ tự động tạo ra 2 đường link riêng biệt: 1 link Lễ Thành Hôn cho Nhà Trai và 1 link Lễ Vu Quy cho Nhà Gái với ngày giờ và sảnh tiệc riêng, giúp quan khách hai bên không bị nhầm lẫn.
                    </p>
                </article>

                <article class="mayhen_guide_faq_item">
                    <h3 class="mayhen_guide_faq_q">
                        <span class="mayhen_guide_faq_q_badge">Q.</span>
                        Sau khi gửi thiệp cưới online cho khách, tôi có sửa lại ngày giờ hoặc địa điểm được không?
                    </h3>
                    <p class="mayhen_guide_faq_a">
                        Được và hoàn toàn miễn phí. Bất cứ khi nào bạn cập nhật thông tin trong trang quản lý, đường link thiệp cưới đã gửi cho khách sẽ tự động cập nhật ngay lập tức mà không cần phải gửi lại link mới.
                    </p>
                </article>

                <article class="mayhen_guide_faq_item">
                    <h3 class="mayhen_guide_faq_q">
                        <span class="mayhen_guide_faq_q_badge">Q.</span>
                        Khách mời lớn tuổi có gặp khó khăn khi bấm vào xem thiệp cưới online không?
                    </h3>
                    <p class="mayhen_guide_faq_a">
                        Thiệp cưới online Mây Hẹn được thiết kế tối giản, phông chữ to rõ ràng, hình ảnh hiển thị ngay không bị che khuất bởi quảng cáo. Khách mời chỉ cần bấm vào đường link nhận qua tin nhắn Zalo là thiệp tự động mở ra ngay trên trình duyệt mà không cần đăng ký tài khoản.
                    </p>
                </article>

                <article class="mayhen_guide_faq_item">
                    <h3 class="mayhen_guide_faq_q">
                        <span class="mayhen_guide_faq_q_badge">Q.</span>
                        Làm sao để biết khách mời đã nhận được thiệp và có tham dự tiệc hay không?
                    </h3>
                    <p class="mayhen_guide_faq_a">
                        Trên mỗi mẫu thiệp có tích hợp sẵn nút Xác Nhận Tham Dự (RSVP). Khách mời chỉ cần tích chọn Có tham dự, số người đi kèm và lời chúc phúc. Hệ thống sẽ tự động tổng hợp danh sách vào bảng quản lý để cô dâu chú rể nắm rõ số lượng bàn tiệc.
                    </p>
                </article>
            </div>
        </section>

        <!-- ==========================================
             9. BOTTOM ACTION BOX
             ========================================== -->
        <section class="mayhen_guide_cta_box">
            <span class="mayhen_guide_cta_kicker">Bắt Đầu Thật Nhanh</span>
            <h2 class="mayhen_guide_cta_title">Tự Tạo Thiệp Cưới Online Của Bạn Ngay Hôm Nay</h2>
            <p class="mayhen_guide_cta_desc">
                Chọn mẫu yêu thích trong kho 50 mẫu thiệp cưới thật, nhập thông tin và xem thử trực tiếp trên điện thoại hoàn toàn miễn phí.
            </p>
            <div class="mayhen_guide_cta_actions">
                <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                    Khám Phá Mẫu Thiệp Đẹp
                </a>
                <a href="{{ route('templates.index', ['tier' => 'free']) }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.3);">
                    Tạo Thử Miễn Phí (0đ)
                </a>
            </div>
        </section>
    </div>
@endsection

