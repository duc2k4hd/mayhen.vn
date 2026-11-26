@extends('client.layouts.master')

@php
    $canonicalUrl = route('wedding.ceremonies');
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tổ Chức Đám Cưới', 'item' => route('wedding.planner.index')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'Nghi Lễ 3 Miền & Pháp Lý', 'item' => $canonicalUrl],
                ],
            ],
            [
                '@type' => 'Article',
                'headline' => 'Nghi Lễ Cưới Hỏi 3 Miền Việt Nam & Thủ Tục Đăng Ký Kết Hôn Pháp Lý',
                'description' => 'Cẩm nang nghi lễ cưới hỏi truyền thống Bắc – Trung – Nam cùng mô hình mâm tráp 3D và hướng dẫn đăng ký kết hôn chuẩn Luật Hôn nhân và Gia đình 2014 qua dichvucong.gov.vn.',
                'inLanguage' => 'vi-VN',
                'datePublished' => '2026-11-26T08:00:00+07:00',
                'dateModified' => '2026-11-26T14:00:00+07:00',
                'author' => ['@type' => 'Organization', 'name' => 'Ban Nghiên Cứu Văn Hóa & Pháp Lý Mây Hẹn', 'url' => route('home')],
                'publisher' => ['@type' => 'Organization', 'name' => 'Mây Hẹn', 'url' => route('home')],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
            ],
            [
                '@type' => 'HowTo',
                'name' => 'Hướng dẫn 4 bước đăng ký kết hôn trực tuyến qua Cổng Dịch vụ công Quốc gia (dichvucong.gov.vn)',
                'step' => [
                    ['@type' => 'HowToStep', 'name' => 'Bước 1: Đăng nhập VNeID', 'text' => 'Truy cập dichvucong.gov.vn và đăng nhập bằng tài khoản định danh điện tử VNeID.'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 2: Chọn thủ tục Đăng ký kết hôn', 'text' => 'Tìm thủ tục Đăng ký kết hôn và chọn UBND cấp xã/phường nơi cư trú của một trong hai bên.'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 3: Kê khai & đính kèm hồ sơ', 'text' => 'Điền tờ khai điện tử, đính kèm CCCD/Hộ chiếu và giấy xác nhận tình trạng hôn nhân (nếu cần).'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 4: Ký Sổ đăng ký kết hôn tại UBND', 'text' => 'Cả hai bên nam và nữ cùng đến trực tiếp UBND theo lịch hẹn để ký sổ và nhận Giấy chứng nhận kết hôn.'],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => 'Độ tuổi đăng ký kết hôn hợp pháp tại Việt Nam tính theo tuổi mụ hay tuổi tròn?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Theo Luật Hôn nhân và Gia đình 2014, Nam phải từ đủ 20 tuổi trở lên và Nữ từ đủ 18 tuổi trở lên, tính chính xác theo ngày tháng năm sinh tròn Dương lịch. Tuyệt đối không dùng tuổi mụ.']],
                    ['@type' => 'Question', 'name' => 'Khác biệt lớn nhất giữa mâm tráp ăn hỏi Miền Bắc và Miền Nam là gì?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Miền Bắc chuộng số tráp lẻ (5, 7, 9 tráp) với lễ vật bên trong chia chẵn; Miền Nam chuộng số mâm quả chẵn (6 hoặc 8 mâm) kèm cặp đèn cầy long phụng thắp trên bàn thờ gia tiên.']],
                ],
            ],
        ],
    ];
@endphp

@section('title', 'Nghi Lễ Cưới Hỏi 3 Miền Việt Nam & Thủ Tục Đăng Ký Kết Hôn Pháp Lý')
@section('meta_description', 'Cẩm nang nghi lễ cưới hỏi truyền thống Bắc – Trung – Nam kèm mô hình mâm tráp 3D và hướng dẫn thủ tục đăng ký kết hôn chuẩn Luật Hôn nhân và Gia đình 2014 qua dichvucong.gov.vn.')
@section('canonical_url', $canonicalUrl)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_wedding_planner_page">
    <div class="mayhen_wedding_planner_container">
        <nav class="mayhen_wedding_seo_breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('wedding.planner.index') }}">Tổ Chức Đám Cưới</a>
            <span>/</span>
            <span>Nghi Lễ 3 Miền &amp; Pháp Lý</span>
        </nav>

        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Thư Viện Văn Hóa &amp; Pháp Lý Hôn Nhân Việt Nam</span>
            <h1 class="mayhen_wedding_planner_h1">Nghi Lễ Cưới Hỏi 3 Miền &amp; Hướng Dẫn Đăng Ký Kết Hôn</h1>
            <p class="mayhen_wedding_planner_lead">Phân biệt rõ phong tục cưới hỏi Miền Bắc – Miền Trung – Miền Nam với mô hình Mâm Tráp 3D trực quan cùng hướng dẫn thủ tục đăng ký kết hôn chính thức tại Cổng Dịch vụ công Quốc gia (dichvucong.gov.vn).</p>
            <nav class="mayhen_wedding_planner_subnav">
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill">Xem Ngày Cưới</a>
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_pill">Tổ Chức Đám Cưới</a>
                <a href="{{ route('wedding.ceremonies') }}" class="mayhen_wedding_planner_pill is-active">Nghi Lễ &amp; Pháp Lý</a>
            </nav>
        </section>

        {{-- MÔ HÌNH MÂM TRÁP LỄ VẬT 3 MIỀN 3D TƯƠNG TÁC --}}
        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Mô Hình Mâm Tráp Ăn Hỏi 3 Miền 3D Tương Tác</h2>
            <p class="mayhen_wedding_planner_card_sub">Chuyển đổi nhanh giữa Miền Bắc (7 tráp lẻ), Miền Trung (5 mâm nến tơ hồng) và Miền Nam (6 mâm quả chẵn) rồi xoay khối 3D để xem ý nghĩa từng lễ vật.</p>

            <div class="mayhen_wedding_3d_toolbar">
                <div class="mayhen_wedding_3d_presets">
                    <button type="button" class="mayhen_wedding_3d_preset_btn is-active" data-tray-region="bac">Miền Bắc (7 Tráp Lẻ)</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-tray-region="trung">Miền Trung (5 Mâm Lễ)</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-tray-region="nam">Miền Nam (6 Mâm Chẵn)</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-3d-preset-target="ceremonyTrays3DFloor" data-3d-preset="auto">Tự Xoay 3D</button>
                </div>
                <div class="mayhen_wedding_3d_sliders">
                    <label>Nghiêng X <input type="range" min="0" max="42" value="24" data-3d-rx="ceremonyTrays3DFloor"></label>
                    <label>Xoay Y <input type="range" min="-35" max="35" value="-14" data-3d-ry="ceremonyTrays3DFloor"></label>
                </div>
            </div>

            <div id="ceremonyTrays3DStage" class="mayhen_wedding_3d_stage">
                <div id="ceremonyTrays3DFloor" class="mayhen_wedding_3d_trays_floor"></div>
            </div>
        </section>

        <div class="mayhen_wedding_planner_grid_3">
            <section class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Phong Tục Miền Bắc</h2>
                <p class="mayhen_wedding_planner_card_sub">Trang trọng, chuẩn mực lễ nghi truyền thống.</p>
                <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                    <li><strong>Số tráp ăn hỏi:</strong> Số lẻ (5, 7, 9 hoặc 11 tráp) nhưng lễ vật bên trong thường chia chẵn tượng trưng có đôi có cặp.</li>
                    <li><strong>Lễ vật tiêu biểu:</strong> Trầu cau, chè, hạt sen, bánh cốm, bánh phu thê, rượu thuốc, lợn sữa quay hoặc xôi gấc.</li>
                    <li><strong>Nghi thức lại quả:</strong> Nhà gái chia lại một phần lễ vật (thường ngửa nắp tráp) gửi nhà trai mang về.</li>
                </ul>
            </section>

            <section class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Phong Tục Miền Trung</h2>
                <p class="mayhen_wedding_planner_card_sub">Mộc mạc, chu đáo và giàu tình nghĩa gia tộc.</p>
                <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                    <li><strong>Số mâm lễ:</strong> Linh hoạt theo thỏa thuận hai nhà (thường 4, 6 hoặc 5, 7 mâm), không quá nặng hình thức.</li>
                    <li><strong>Lễ vật đặc trưng:</strong> Trầu cau, bánh phu thê (bánh xu xê), trà rượu và cặp nến tơ hồng thắp trên bàn thờ gia tiên.</li>
                    <li><strong>Lễ rước dâu:</strong> Chú trọng giờ xuất hành và sự có mặt đông đủ của họ hàng hai bên trong nghi lễ gia tiên.</li>
                </ul>
            </section>

            <section class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Phong Tục Miền Nam</h2>
                <p class="mayhen_wedding_planner_card_sub">Phóng khoáng, rộn ràng và đề cao sự sung túc.</p>
                <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                    <li><strong>Số mâm quả:</strong> Số chẵn (thường 6 hoặc 8 mâm quả), tượng trưng cho sự song hỷ viên mãn.</li>
                    <li><strong>Lễ vật đặc trưng:</strong> Cặp đèn cầy long phụng cỡ lớn, trà rượu, bánh kem/bánh pía, mâm ngũ quả, heo quay và khay trầu rượu dẫn đầu.</li>
                    <li><strong>Nghi thức lên đèn:</strong> Chú rể và cô dâu cùng thắp cặp đèn cầy long phụng trên bàn thờ gia tiên nhà gái.</li>
                </ul>
            </section>
        </div>

        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Hướng Dẫn Đăng Ký Kết Hôn Hợp Pháp Tại Việt Nam</h2>
            <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">
                <strong>Lưu ý pháp lý bắt buộc:</strong> Theo Điểm a Khoản 1 Điều 8 <strong>Luật Hôn nhân và Gia đình năm 2014</strong>, độ tuổi kết hôn hợp pháp là <strong>Nam từ đủ 20 tuổi trở lên, Nữ từ đủ 18 tuổi trở lên</strong> — tính chính xác theo <strong>ngày, tháng, năm sinh tròn Dương lịch</strong>. <strong>Tuyệt đối không sử dụng tuổi mụ</strong> để tính điều kiện kết hôn pháp lý.
            </div>
            <div class="mayhen_wedding_planner_grid_equal">
                <div>
                    <h3 style="margin-top:0;">1. Hồ sơ cần chuẩn bị</h3>
                    <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                        <li>Tờ khai đăng ký kết hôn theo mẫu (hoặc biểu mẫu điện tử khi nộp online).</li>
                        <li>CCCD / Hộ chiếu còn thời hạn của cả hai bên nam và nữ.</li>
                        <li>Giấy xác nhận tình trạng hôn nhân (nếu không thường trú tại nơi đăng ký và chưa đồng bộ VNeID).</li>
                        <li>Bản án / Quyết định ly hôn có hiệu lực pháp luật (nếu một trong hai bên đã từng kết hôn).</li>
                    </ul>
                </div>
                <div>
                    <h3 style="margin-top:0;">2. Quy trình nộp trực tuyến qua Cổng Dịch vụ công Quốc gia</h3>
                    <ol style="padding-left:18px;font-size:14px;line-height:1.7;">
                        <li>Truy cập <strong>https://dichvucong.gov.vn</strong> và đăng nhập bằng tài khoản định danh điện tử <strong>VNeID</strong>.</li>
                        <li>Tìm thủ tục <em>“Đăng ký kết hôn”</em> và chọn UBND cấp xã/phường nơi cư trú của một trong hai bên.</li>
                        <li>Kê khai tờ khai điện tử, đính kèm ảnh chụp giấy tờ và nhận lịch hẹn.</li>
                        <li>Đến ngày hẹn, <strong>cả hai bên nam và nữ cùng có mặt trực tiếp tại UBND</strong> để ký Sổ đăng ký kết hôn và nhận Giấy chứng nhận kết hôn.</li>
                    </ol>
                </div>
            </div>
        </section>

        {{-- PILLAR SEO CONTENT SO SÁNH 3 MIỀN & 6 NGHI LỄ --}}
        <article class="mayhen_wedding_planner_seo">
            <h2>Bảng So Sánh Toàn Diện Phong Tục Cưới Hỏi 3 Miền Bắc – Trung – Nam</h2>
            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead>
                        <tr><th>Tiêu chí</th><th>Miền Bắc</th><th>Miền Trung</th><th>Miền Nam</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Số lượng tráp / mâm quả</strong></td><td>Số lẻ (5, 7, 9 tráp), đồ bên trong chia chẵn</td><td>Linh hoạt (4–6 hoặc 5–7 mâm)</td><td>Số chẵn (6 hoặc 8 mâm quả)</td></tr>
                        <tr><td><strong>Lễ vật không thể thiếu</strong></td><td>Trầu cau, bánh cốm, bánh phu thê, chè hạt sen</td><td>Trầu cau, bánh phu thê, cặp nến tơ hồng</td><td>Khay trầu rượu, cặp đèn cầy long phụng, heo quay</td></tr>
                        <tr><td><strong>Nghi thức gia tiên</strong></td><td>Thắp hương gia tiên hai họ, trao lễ đen</td><td>Thắp nến tơ hồng, trình diện họ hàng</td><td>Lên đèn long phụng, trao nữ trang cưới trên sân khấu gia tiên</td></tr>
                        <tr><td><strong>Nghi thức lại quả</strong></td><td>Chia lại lễ, ngửa nắp tráp, không dùng dao kéo</td><td>Chia lại một phần lễ tượng trưng</td><td>Trả lại một phần mâm quả theo số chẵn</td></tr>
                        <tr><td><strong>Lễ Lại Mặt (Nhị Hỷ)</strong></td><td>Sau cưới 1–3 ngày, đôi trẻ về thăm nhà gái</td><td>Sau cưới 1–3 ngày, thường gọn nhẹ ấm cúng</td><td>Phản bái sau cưới 1–3 ngày kèm quà biếu cha mẹ vợ</td></tr>
                    </tbody>
                </table>
            </div>

            <h2>6 Nghi Lễ Truyền Thống Trong Hành Trình Cưới Hỏi Việt Nam</h2>
            <ol>
                <li><strong>Lễ Dạm Ngõ (Xem Mặt):</strong> Nhà trai mang lễ vật giản dị (trầu cau, chè, rượu, bánh) sang nhà gái chính thức thưa chuyện đặt vấn đề hôn nhân và thống nhất ngày ăn hỏi, ngày cưới.</li>
                <li><strong>Lễ Ăn Hỏi (Đính Hôn):</strong> Nghi thức quan trọng xác lập hôn ước giữa hai họ với đội ngũ bê tráp, lễ vật theo vùng miền và lễ lại quả.</li>
                <li><strong>Lễ Xin Dâu:</strong> Trước giờ đón dâu chính thức, đại diện nhà trai mang khay trầu rượu sang báo trước để nhà gái chuẩn bị sẵn sàng đón đoàn.</li>
                <li><strong>Lễ Gia Tiên &amp; Đón Dâu (Rước Dâu):</strong> Hai họ cử hành nghi thức bái lễ tổ tiên tại nhà gái, trao nữ trang, sau đó rước cô dâu về nhà trai làm lễ nhập gia.</li>
                <li><strong>Lễ Thành Hôn &amp; Tiệc Cưới:</strong> Công bố chính thức đôi tân lang – tân nương trước quan khách, thực hiện nghi thức trao nhẫn, rót rượu champagne và cắt bánh cưới.</li>
                <li><strong>Lễ Lại Mặt (Nhị Hỷ):</strong> Sau lễ cưới 1–3 ngày, hai vợ chồng mang lễ nhỏ về thăm nhà gái bày tỏ lòng hiếu kính.</li>
            </ol>

            <div class="mayhen_wedding_seo_links_grid">
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Xem Ngày Cưới &amp; Ăn Hỏi →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Chọn ngày đẹp cho đủ 8 nghi lễ cưới hỏi</div>
                </a>
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Lập Kế Hoạch Tổ Chức Cưới →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Checklist 3 miền tự động &amp; Ngân sách VND</div>
                </a>
                <a href="{{ route('templates.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Thiết Kế Thiệp Cưới Online →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Tạo thiệp mời đồng bộ lịch trình hai họ</div>
                </a>
            </div>
        </article>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset_v('assets/client/js/pages/wedding-journey.js') }}" defer></script>
@endpush
