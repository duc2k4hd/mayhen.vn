@extends('client.layouts.master')

@php
    $canonicalUrl = route('wedding.planner.index');
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Tổ Chức Đám Cưới & Lập Kế Hoạch Hôn Lễ Online Miễn Phí — Mây Hẹn',
                'url' => $canonicalUrl,
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
                'inLanguage' => 'vi-VN',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tổ Chức Đám Cưới', 'item' => $canonicalUrl],
                ],
            ],
            [
                '@type' => 'Article',
                'headline' => 'Tổ Chức Đám Cưới & Lập Kế Hoạch Hôn Lễ Online Miễn Phí',
                'description' => 'Cẩm nang và nền tảng lập kế hoạch tổ chức đám cưới toàn diện: Checklist 3 miền, Ngân sách VND chuẩn kế toán, Sảnh tiệc 3D Isometric, Lịch trình ngày cưới & Thiệp cưới đồng bộ.',
                'inLanguage' => 'vi-VN',
                'datePublished' => '2026-11-26T08:00:00+07:00',
                'dateModified' => '2026-11-26T14:00:00+07:00',
                'author' => ['@type' => 'Organization', 'name' => 'Đội Ngũ Wedding Planner Mây Hẹn', 'url' => route('home')],
                'publisher' => ['@type' => 'Organization', 'name' => 'Mây Hẹn', 'url' => route('home')],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
            ],
            [
                '@type' => 'HowTo',
                'name' => 'Lộ trình tổ chức đám cưới Việt Nam chuẩn chỉ trong 6 giai đoạn',
                'step' => [
                    ['@type' => 'HowToStep', 'name' => 'Giai đoạn 1 (6–12 tháng): Ngân sách & Ngày cưới', 'text' => 'Thống nhất ngân sách tổng, đóng góp hai họ và chọn 2–3 ngày cưới ứng viên thuận tiện.'],
                    ['@type' => 'HowToStep', 'name' => 'Giai đoạn 2 (4–6 tháng): Khóa sảnh tiệc & Ekip', 'text' => 'Đặt cọc nhà hàng, quay phim chụp ảnh phóng sự cưới và trang phục lễ cưới.'],
                    ['@type' => 'HowToStep', 'name' => 'Giai đoạn 3 (2–3 tháng): Thủ tục pháp lý & Lễ ăn hỏi', 'text' => 'Nộp hồ sơ đăng ký kết hôn trực tuyến tại dichvucong.gov.vn và chuẩn bị tráp lễ theo vùng miền.'],
                    ['@type' => 'HowToStep', 'name' => 'Giai đoạn 4 (1–2 tháng): Thiệp cưới & RSVP', 'text' => 'Tạo thiệp cưới online Mây Hẹn, gửi link Zalo cá nhân hóa và tổng hợp khách xác nhận tham dự.'],
                    ['@type' => 'HowToStep', 'name' => 'Giai đoạn 5 (2 tuần cuối): Sơ đồ bàn 2D/3D & Kịch bản', 'text' => 'Chốt số bàn chính thức, xếp sơ đồ bàn không trùng khách và duyệt kịch bản MC.'],
                    ['@type' => 'HowToStep', 'name' => 'Giai đoạn 6 (Ngày cưới): Vận hành & Dự phòng', 'text' => 'Chạy đúng lịch trình có đệm di chuyển 15–30 phút và kích hoạt phương án dự phòng khi cần.'],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => 'Bảng ngân sách đám cưới trên Mây Hẹn tính tiền cọc như thế nào?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Mây Hẹn sử dụng toán học số nguyên VND chính xác và tách bạch Hợp đồng đã chốt, Tiền cọc, Đã thanh toán và Còn phải trả — đảm bảo tiền cọc không bao giờ bị cộng trùng vào tổng giá trị hợp đồng.']],
                    ['@type' => 'Question', 'name' => 'Tôi có thể xem sảnh tiệc cưới ở góc nhìn 3D và kiểm tra trùng khách không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Có. Studio Sơ đồ bàn tiệc hỗ trợ cả chế độ kéo thả 2D lẫn góc nhìn Sảnh Tiệc 3D Isometric xoay 360 độ, tự động cảnh báo khi bàn bị chồng lấn, vượt sức chứa ghế hoặc xếp trùng một khách vào 2 bàn, và xuất ảnh PNG gửi nhà hàng.']],
                    ['@type' => 'Question', 'name' => 'Cần chuẩn bị ngân sách bao nhiêu để tổ chức đám cưới tại Việt Nam?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Tùy quy mô khách mời và thành phố: đám cưới tối giản 100–120 khách thường dao động 80–120 triệu VND; đám cưới tiêu chuẩn 150–250 khách khoảng 150–280 triệu VND; đám cưới cao cấp tại khách sạn 5 sao từ 350 triệu VND trở lên.']],
                    ['@type' => 'Question', 'name' => 'Nên đặt dự phòng bao nhiêu bàn tiệc cưới so với danh sách RSVP?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Nên đặt dự phòng khoảng 10% tổng số bàn (tối thiểu 1–2 bàn dự phòng) và thỏa thuận trước với nhà hàng về chính sách chỉ tính phí khi mở bàn dự phòng.']],
                ],
            ],
        ],
    ];
@endphp

@section('title', 'Tổ Chức Đám Cưới & Lập Kế Hoạch Hôn Lễ Online Miễn Phí')
@section('meta_description', 'Lập kế hoạch tổ chức đám cưới toàn diện miễn phí: Checklist 3 miền co giãn theo ngày cưới, Ngân sách VND chống tính trùng cọc, Sảnh tiệc 3D Isometric, Kiểm tra xung đột lịch trình và Kịch bản MC.')
@section('canonical_url', $canonicalUrl)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_wedding_planner_page"
     data-wedding-page="planner-landing"
     data-quick-budget-url="{{ route('wedding.api.quick_budget') }}"
     data-script-url="{{ route('wedding.api.generate_script') }}">
    <div class="mayhen_wedding_planner_container">
        <nav class="mayhen_wedding_seo_breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <span>Tổ Chức Đám Cưới</span>
        </nav>

        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Mây Hẹn – The Wedding Journey · Trọn vẹn từng nghi lễ, chỉn chu từng khoảnh khắc</span>
            <h1 class="mayhen_wedding_planner_h1">Tổ Chức Đám Cưới &amp; Lập Kế Hoạch Hôn Lễ Online Miễn Phí</h1>
            <p class="mayhen_wedding_planner_lead">Nền tảng quản trị toàn bộ hành trình hôn lễ Việt Nam: từ Xem ngày cưới, Checklist 3 miền, Ngân sách VND chuẩn kế toán, Studio Sảnh Tiệc 3D Isometric, Kiểm tra xung đột lịch trình ngày cưới đến Thiệp Cưới Online đồng bộ.</p>
            <nav class="mayhen_wedding_planner_subnav">
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_pill is-active">Lập Kế Hoạch Cưới</a>
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill">Xem Ngày Cưới</a>
                <a href="{{ route('wedding.ceremonies') }}" class="mayhen_wedding_planner_pill">Nghi Lễ 3 Miền &amp; Pháp Lý</a>
                @auth
                    <a href="{{ route('wedding.workspace.index') }}" class="mayhen_wedding_planner_pill mayhen_wedding_planner_pill--crimson">Hồ Sơ Đám Cưới Của Tôi ({{ $userProjects->count() }})</a>
                @else
                    <a href="{{ route('login', ['redirect' => route('wedding.workspace.index')]) }}" class="mayhen_wedding_planner_pill mayhen_wedding_planner_pill--crimson">Đăng Nhập Mở Workspace Riêng</a>
                @endauth
            </nav>
        </section>

        {{-- KHÔNG GIAN SẢNH TIỆC CƯỚI 3D ISOMETRIC TƯƠNG TÁC --}}
        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Mô Phỏng Sảnh Tiệc Cưới 3D Isometric Trực Quan</h2>
            <p class="mayhen_wedding_planner_card_sub">Xoay góc nhìn 360°, chỉnh độ nghiêng mặt bằng và thử các quy mô khách mời (100 / 150 / 220 khách) để hình dung ngay bố cục sân khấu, lễ đường và bàn VIP hai họ.</p>

            <div class="mayhen_wedding_3d_toolbar">
                <div class="mayhen_wedding_3d_presets">
                    <button type="button" class="mayhen_wedding_3d_preset_btn is-active" data-demo-hall-guests="120">120 khách (12 bàn)</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-demo-hall-guests="160">160 khách (16 bàn)</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-demo-hall-guests="80">80 khách (Ấm cúng)</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-3d-preset-target="publicHall3DFloor" data-3d-preset="top" data-rx="0" data-ry="0" data-rz="0">Nhìn Từ Trên 2D</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn is-active" data-3d-preset-target="publicHall3DFloor" data-3d-preset="iso" data-rx="54" data-ry="0" data-rz="-18">Góc 3D Isometric</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-3d-preset-target="publicHall3DFloor" data-3d-preset="auto">Tự Xoay Sảnh 3D</button>
                </div>
                <div class="mayhen_wedding_3d_sliders">
                    <label>Góc nghiêng <input type="range" min="0" max="68" value="54" data-3d-rx="publicHall3DFloor"></label>
                    <label>Xoay 360° <input type="range" min="-180" max="180" value="-18" data-3d-ry="publicHall3DFloor"></label>
                </div>
            </div>

            <div id="publicHall3DStage" class="mayhen_wedding_3d_hall_stage">
                <div id="publicHall3DFloor" class="mayhen_wedding_3d_hall_floor"></div>
            </div>
        </section>

        {{-- Khối Khởi tạo Hồ Sơ Tổ Chức Đám Cưới Cá Nhân --}}
        <section class="mayhen_wedding_planner_card">
            <div class="mayhen_wedding_planner_grid_equal">
                <div>
                    <h2 class="mayhen_wedding_planner_card_title">Khởi Tạo Hồ Sơ Tổ Chức Đám Cưới Riêng</h2>
                    <p class="mayhen_wedding_planner_card_sub">Tự động sinh Checklist 13+ hạng mục co giãn theo ngày cưới thực tế, bảng Ngân sách VND 8 hạng mục, Sơ đồ bàn tiệc 2D/3D và Lịch trình ngày cưới mẫu.</p>
                    <ul style="padding-left:18px;font-size:14px;line-height:1.7;color:#5F5750;">
                        <li>Dữ liệu tách biệt riêng tư theo tài khoản (chỉ bạn xem và chỉnh sửa).</li>
                        <li>Kết nối trực tiếp với <a href="{{ route('templates.index') }}" style="color:#85182A;font-weight:600;">Thiệp Cưới Online</a>, Khách mời RSVP và Lịch Âm Mây Hẹn.</li>
                        <li>Gói hiện tại: <strong>{{ $planSummary['tier_label'] }}</strong> (tối đa {{ $planSummary['max_projects'] }} hồ sơ · {{ $planSummary['daily_ai'] }} lượt AI Concierge/ngày).</li>
                    </ul>
                    @if ($userProjects->isNotEmpty())
                        <div style="margin-top:14px;padding:12px 14px;background:#F6EFE2;border-radius:12px;">
                            <strong style="font-size:13.5px;">Hồ sơ đang có của bạn:</strong>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                                @foreach ($userProjects as $up)
                                    <a href="{{ route('wedding.workspace.show', ['slug' => $up->slug]) }}" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm">
                                        {{ $up->title }} →
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    @auth
                        <form id="createWeddingProjectForm" action="{{ route('wedding.workspace.store') }}" method="POST">
                            @csrf
                            <div class="mayhen_wedding_planner_row_2">
                                <div class="mayhen_wedding_planner_field">
                                    <label class="mayhen_wedding_planner_label">Tên chú rể *</label>
                                    <input name="groom_name" type="text" required placeholder="VD: Minh Quân" class="mayhen_wedding_planner_input">
                                </div>
                                <div class="mayhen_wedding_planner_field">
                                    <label class="mayhen_wedding_planner_label">Tên cô dâu *</label>
                                    <input name="bride_name" type="text" required placeholder="VD: Thảo Vy" class="mayhen_wedding_planner_input">
                                </div>
                            </div>
                            <div class="mayhen_wedding_planner_row_2">
                                <div class="mayhen_wedding_planner_field">
                                    <label class="mayhen_wedding_planner_label">Ngày cưới dự kiến</label>
                                    <input name="wedding_date" type="date" class="mayhen_wedding_planner_input">
                                </div>
                                <div class="mayhen_wedding_planner_field">
                                    <label class="mayhen_wedding_planner_label">Nghi lễ vùng miền</label>
                                    <select name="region_style" class="mayhen_wedding_planner_select">
                                        <option value="bac">Miền Bắc (Tráp số lẻ 5/7/9)</option>
                                        <option value="trung">Miền Trung (Trầu cau, nến tơ hồng)</option>
                                        <option value="nam">Miền Nam (Mâm quả số chẵn 6/8)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mayhen_wedding_planner_row_2">
                                <div class="mayhen_wedding_planner_field">
                                    <label class="mayhen_wedding_planner_label">Ngân sách dự kiến (VND)</label>
                                    <input name="budget_total_vnd" type="number" min="10000000" step="1000000" value="150000000" class="mayhen_wedding_planner_input">
                                </div>
                                <div class="mayhen_wedding_planner_field">
                                    <label class="mayhen_wedding_planner_label">Số khách mời dự kiến</label>
                                    <input name="guest_target" type="number" min="10" max="5000" value="150" class="mayhen_wedding_planner_input">
                                </div>
                            </div>
                            <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--crimson" style="width:100%;">
                                Khởi Tạo Workspace Tổ Chức Đám Cưới →
                            </button>
                        </form>
                    @else
                        <div style="background:#F6EFE2;padding:24px;border-radius:14px;text-align:center;">
                            <h3 style="margin:0 0 8px;">Lưu trữ kế hoạch cưới riêng tư trọn hành trình</h3>
                            <p style="font-size:13.5px;color:#5F5750;margin:0 0 16px;">Đăng nhập miễn phí để mở bảng điều khiển Checklist, Ngân sách VND, Sơ đồ bàn 2D/3D và Lịch trình ngày cưới lưu trên đám mây.</p>
                            <a href="{{ route('login', ['redirect' => route('wedding.planner.index')]) }}" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--crimson">
                                Đăng Nhập / Đăng Ký Miễn Phí
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </section>

        {{-- Công cụ miễn phí 1: Tính nhanh Ngân sách VND & Số bàn tiệc --}}
        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Công Cụ Tính Nhanh Ngân Sách Cưới (VND) &amp; Số Bàn Tiệc</h2>
            <p class="mayhen_wedding_planner_card_sub">Dùng ngay không cần đăng nhập — tính toán bằng số nguyên VND chính xác 100%, không sai số làm tròn.</p>
            <form id="quickBudgetForm" class="mayhen_wedding_planner_grid_4" style="align-items:end;margin-bottom:18px;">
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Tổng ngân sách (VND)</label>
                    <input name="total_budget_vnd" type="number" min="10000000" step="5000000" value="150000000" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Số khách mời dự kiến</label>
                    <input name="guest_count" type="number" min="10" max="5000" value="150" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Số khách / bàn</label>
                    <select name="seats_per_table" class="mayhen_wedding_planner_select">
                        <option value="10" selected>10 khách / bàn (Phổ biến)</option>
                        <option value="8">8 khách / bàn</option>
                        <option value="6">6 khách / bàn (Bàn VIP)</option>
                    </select>
                </div>
                <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary">Tính Ngân Sách &amp; Số Bàn</button>
            </form>
            <div id="quickBudgetResult">
                <div class="mayhen_wedding_date_compare_table_wrap">
                    <table class="mayhen_wedding_date_compare_table">
                        <thead><tr><th>Hạng mục</th><th>Tỷ trọng tham khảo</th><th>Phân bổ đề xuất (150 triệu VND)</th></tr></thead>
                        <tbody>
                            @foreach ($sampleBudget as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['ratio'] }}%</td>
                                    <td><strong>{{ number_format($row['budgeted_vnd'], 0, ',', '.') }} ₫</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- Công cụ miễn phí 2: Biên soạn Kịch bản MC, Phát biểu hai họ, Vows & Cảm ơn --}}
        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Trợ Lý Soạn Kịch Bản MC, Lời Phát Biểu Hai Họ &amp; Thề Nguyện</h2>
            <p class="mayhen_wedding_planner_card_sub">Tạo nhanh Mẫu Biên Tập Chuẩn miễn phí không giới hạn, hoặc bật AI Wedding Concierge để cá nhân hóa sâu theo câu chuyện của hai bạn.</p>

            <div class="mayhen_wedding_planner_grid_equal">
                <form id="weddingScriptForm">
                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label">Loại nội dung cần soạn</label>
                        <select name="script_type" class="mayhen_wedding_planner_select">
                            @foreach ($scriptTypes as $key => $label)
                                @if ($key !== 'workspace_advice')
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mayhen_wedding_planner_row_2">
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label">Tên chú rể</label>
                            <input name="groom_name" type="text" placeholder="VD: Minh Quân" class="mayhen_wedding_planner_input">
                        </div>
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label">Tên cô dâu</label>
                            <input name="bride_name" type="text" placeholder="VD: Thảo Vy" class="mayhen_wedding_planner_input">
                        </div>
                    </div>
                    <div class="mayhen_wedding_planner_row_2">
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label">Ngày tổ chức</label>
                            <input name="wedding_date" type="text" placeholder="VD: 20/12/2026" class="mayhen_wedding_planner_input">
                        </div>
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label">Vùng miền</label>
                            <select name="region" class="mayhen_wedding_planner_select">
                                <option value="bac">Miền Bắc</option>
                                <option value="trung">Miền Trung</option>
                                <option value="nam">Miền Nam</option>
                            </select>
                        </div>
                    </div>
                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label">Ghi chú kỷ niệm / yêu cầu riêng (tùy chọn)</label>
                        <textarea name="custom_notes" rows="2" placeholder="VD: Yêu nhau 4 năm từ thời đại học, phong cách ấm áp ngắn gọn..." class="mayhen_wedding_planner_textarea"></textarea>
                    </div>
                    <label class="mayhen_wedding_planner_check">
                        <input type="checkbox" name="use_ai" value="1">
                        <span>Bật AI Wedding Concierge cá nhân hóa (yêu cầu đăng nhập · còn {{ $planSummary['remaining_ai_today'] }}/{{ $planSummary['daily_ai'] }} lượt hôm nay)</span>
                    </label>
                    <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary">Tạo Nội Dung Ngay</button>
                </form>

                <div id="weddingScriptOutput">
                    <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info">
                        Nhập tên chú rể, cô dâu và bấm <strong>Tạo Nội Dung Ngay</strong> để nhận kịch bản MC, bài phát biểu hai họ hoặc lời thề nguyện chuẩn mực.
                    </div>
                </div>
            </div>
        </section>

        {{-- PILLAR SEO CONTENT CHUẨN GOOGLE E-E-A-T --}}
        <article class="mayhen_wedding_planner_seo">
            <div class="mayhen_wedding_seo_meta_bar">
                <span><strong>Biên tập:</strong> Đội Ngũ Wedding Planner &amp; Kiến Trúc Sản Phẩm Mây Hẹn</span>
                <span><strong>Phạm vi:</strong> Đám cưới Việt Nam 3 miền</span>
                <span><strong>Cập nhật:</strong> Tháng 11/2026</span>
            </div>

            <nav class="mayhen_wedding_seo_toc" aria-label="Mục lục bài viết">
                <p class="mayhen_wedding_seo_toc_title">Mục Lục Cẩm Nang Tổ Chức Đám Cưới Từ A Đến Z</p>
                <ol class="mayhen_wedding_seo_toc_list">
                    <li><a href="#lo-trinh-6-giai-doan">1. Lộ trình tổ chức đám cưới 6 giai đoạn chuẩn xác</a></li>
                    <li><a href="#co-cau-ngan-sach">2. Bảng cơ cấu ngân sách cưới Việt Nam 3 phân khúc</a></li>
                    <li><a href="#ke-toan-tien-coc">3. Nguyên tắc quản lý tiền cọc &amp; chia sẻ chi phí hai họ</a></li>
                    <li><a href="#xep-ban-tiec">4. Nghệ thuật xếp sơ đồ bàn tiệc 2D/3D &amp; quản lý RSVP</a></li>
                    <li><a href="#rui-ro-du-phong">5. 8 rủi ro ngày cưới thường gặp &amp; phương án dự phòng</a></li>
                    <li><a href="#faq-to-chuc-cuoi">6. Câu hỏi thường gặp khi lập kế hoạch hôn lễ</a></li>
                </ol>
            </nav>

            <h2 id="lo-trinh-6-giai-doan">1. Lộ Trình Tổ Chức Đám Cưới Việt Nam Chuẩn Chỉ Trong 6 Giai Đoạn</h2>
            <p>Tổ chức đám cưới tại Việt Nam có độ phức tạp đặc thù vì phải dung hòa đồng thời yếu tố gia tộc hai họ, nghi thức 3 miền, thủ tục hành chính và trải nghiệm khách mời hiện đại. Dưới đây là lộ trình 6 giai đoạn tối ưu mà <strong>Mây Hẹn – The Wedding Journey</strong> tự động co giãn theo ngày cưới của bạn:</p>
            <ol>
                <li><strong>Giai đoạn 1 — Khởi động &amp; Thống nhất (6–12 tháng trước cưới):</strong> Hai gia đình gặp mặt thống nhất ngân sách trần, <a href="{{ route('marriage.compatibility.index') }}">xem tuổi vợ chồng</a>, <a href="{{ route('wedding.dates.index') }}">chốt 2–3 ngày cưới ứng viên</a> và ước lượng danh sách khách mời.</li>
                <li><strong>Giai đoạn 2 — Khóa các dịch vụ khan hiếm (4–6 tháng):</strong> Khảo sát ít nhất 3 trung tâm tiệc cưới, so sánh báo giá trọn gói, đặt cọc sảnh tiệc, ekip chụp ảnh phóng sự cưới, quay phim và trang phục cưới.</li>
                <li><strong>Giai đoạn 3 — Thủ tục pháp lý &amp; Lễ ăn hỏi (2–3 tháng):</strong> Nộp hồ sơ <a href="{{ route('wedding.ceremonies') }}">đăng ký kết hôn trực tuyến trên dichvucong.gov.vn</a>, đặt tráp ăn hỏi / mâm quả theo đúng phong tục Bắc – Trung – Nam.</li>
                <li><strong>Giai đoạn 4 — Thiệp cưới &amp; Quản lý RSVP (1–2 tháng):</strong> Thiết kế <a href="{{ route('templates.index') }}">Thiệp Cưới Online Mây Hẹn</a>, gửi link cá nhân hóa tên từng khách qua Zalo, thu thập xác nhận tham dự (RSVP) và ghi chú khách ăn chay/dị ứng.</li>
                <li><strong>Giai đoạn 5 — Chốt sơ đồ bàn 2D/3D &amp; Kịch bản (14 ngày cuối):</strong> Xếp sơ đồ chỗ ngồi bằng Studio 2D/3D chống trùng khách, duyệt kịch bản MC, lời phát biểu hai họ và tổng duyệt lịch trình ngày cưới.</li>
                <li><strong>Giai đoạn 6 — Vận hành ngày trọng đại (48 giờ cuối):</strong> Kiểm tra nhẫn cưới, phong bì lì xì đội bê tráp, thanh toán các đợt cọc đến hạn và gửi bảng lịch trình chi tiết cho trưởng đoàn hai họ + ekip.</li>
            </ol>

            <h2 id="co-cau-ngan-sach">2. Bảng Cơ Cấu Ngân Sách Đám Cưới Việt Nam Theo 3 Phân Khúc Thực Tế</h2>
            <p>Để tránh tình trạng đội vốn 25–40% vào sát ngày cưới, các cặp đôi nên phân bổ ngân sách ngay từ đầu theo tỷ trọng đã được kiểm chứng thực tế tại Việt Nam:</p>
            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead>
                        <tr><th>Hạng mục chi phí</th><th>Tỷ trọng</th><th>Gói Tối Giản (~100 triệu)</th><th>Gói Tiêu Chuẩn (~200 triệu)</th><th>Gói Cao Cấp (~400 triệu)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Nhà hàng &amp; Tiệc cưới (48%)</strong></td><td>48%</td><td>48.000.000 ₫</td><td>96.000.000 ₫</td><td>192.000.000 ₫</td></tr>
                        <tr><td><strong>Lễ ăn hỏi, tráp &amp; Gia tiên (10%)</strong></td><td>10%</td><td>10.000.000 ₫</td><td>20.000.000 ₫</td><td>40.000.000 ₫</td></tr>
                        <tr><td><strong>Chụp ảnh &amp; Quay phim (10%)</strong></td><td>10%</td><td>10.000.000 ₫</td><td>20.000.000 ₫</td><td>40.000.000 ₫</td></tr>
                        <tr><td><strong>Váy cưới, Vest &amp; Trang điểm (9%)</strong></td><td>9%</td><td>9.000.000 ₫</td><td>18.000.000 ₫</td><td>36.000.000 ₫</td></tr>
                        <tr><td><strong>Trang trí sảnh &amp; Hoa tươi (8%)</strong></td><td>8%</td><td>8.000.000 ₫</td><td>16.000.000 ₫</td><td>32.000.000 ₫</td></tr>
                        <tr><td><strong>Nhẫn cưới &amp; Trang sức (6%)</strong></td><td>6%</td><td>6.000.000 ₫</td><td>12.000.000 ₫</td><td>24.000.000 ₫</td></tr>
                        <tr><td><strong>Thiệp cưới, Xe hoa &amp; Di chuyển (4%)</strong></td><td>4%</td><td>4.000.000 ₫</td><td>8.000.000 ₫</td><td>16.000.000 ₫</td></tr>
                        <tr><td><strong>Quỹ dự phòng phát sinh (5%)</strong></td><td>5%</td><td>5.000.000 ₫</td><td>10.000.000 ₫</td><td>20.000.000 ₫</td></tr>
                    </tbody>
                </table>
            </div>

            <h2 id="ke-toan-tien-coc">3. Nguyên Tắc Kế Toán Chống Tính Trùng Tiền Cọc &amp; Chia Sẻ Chi Phí Hai Họ</h2>
            <p>Khi quản lý ngân sách cưới bằng file ghi chú thông thường, sai sót phổ biến nhất là cộng cả <strong>giá trị hợp đồng</strong> (ví dụ 90 triệu) và <strong>tiền cọc đợt 1</strong> (ví dụ 25 triệu) thành tổng chi 115 triệu. Hệ thống Ngân sách VND của Mây Hẹn áp dụng chặt chẽ 4 cột tách biệt: <em>Ngân sách dự kiến</em> → <em>Hợp đồng đã chốt (Committed = 90tr)</em> → <em>Đã thanh toán gồm cọc (Paid = 25tr)</em> → <em>Còn phải trả (Outstanding = 65tr)</em> — đảm bảo số liệu minh bạch 100% và xuất thẳng file Excel CSV đối soát với hai gia đình.</p>

            <h2 id="xep-ban-tiec">4. Nghệ Thuật Xếp Sơ Đồ Bàn Tiệc 2D/3D &amp; Quản Lý Khách Mời RSVP</h2>
            <ul>
                <li><strong>Khu vực VIP gần sân khấu:</strong> Luôn bố trí bàn cha mẹ, ông bà và trưởng tộc hai bên đối xứng hai bên thảm đỏ lễ đường để thuận tiện lên sân khấu làm lễ.</li>
                <li><strong>Tách cụm âm thanh hợp lý:</strong> Người lớn tuổi và gia đình có trẻ nhỏ nên ngồi cách loa chính ít nhất 6–8 mét; nhóm bạn đại học và đồng nghiệp trẻ đặt gần khu vực trung tâm để tạo không khí sôi nổi.</li>
                <li><strong>Quy tắc bàn dự phòng 10%:</strong> Luôn giữ tối thiểu 10% bàn dự phòng và kiểm tra tự động bằng Studio 2D/3D Mây Hẹn để không xảy ra tình trạng xếp trùng 1 khách vào 2 bàn hoặc vượt sức chứa ghế.</li>
            </ul>

            <h2 id="rui-ro-du-phong">5. 8 Rủi Ro Ngày Cưới Hay Gặp Nhất &amp; Phương Án Dự Phòng (Backup Plan)</h2>
            <ol>
                <li><strong>Kẹt xe giờ rước dâu:</strong> Luôn bố trí 20–30 phút đệm di chuyển trong Lịch trình ngày cưới; công cụ Timeline Conflict Detector của Mây Hẹn sẽ cảnh báo đỏ ngay nếu 2 địa điểm cách nhau dưới 15 phút.</li>
                <li><strong>Mưa bất ngờ với tiệc ngoài trời:</strong> Bắt buộc có điều khoản chuyển sảnh trong nhà hoặc dựng nhà bạt trong hợp đồng địa điểm.</li>
                <li><strong>Khách đến vượt số lượng RSVP:</strong> Thỏa thuận sẵn với nhà hàng quy trình mở bàn dự phòng trong 15 phút.</li>
                <li><strong>Lỗi file nhạc / micro / máy chiếu:</strong> Chuẩn bị sẵn USB dự phòng chứa toàn bộ nhạc lễ đường và video phóng sự cưới.</li>
            </ol>

            <h2 id="faq-to-chuc-cuoi">6. Câu Hỏi Thường Gặp Khi Tổ Chức Đám Cưới</h2>
            <details class="mayhen_wedding_planner_faq_item" open>
                <summary>1. Dùng công cụ Tổ Chức Đám Cưới trên Mây Hẹn có miễn phí không?</summary>
                <p>Có. Toàn bộ công cụ tính ngân sách nhanh, mô phỏng sảnh tiệc 3D, tạo Mẫu Biên Tập Chuẩn (kịch bản MC, phát biểu, lời thề nguyện) đều miễn phí không cần đăng nhập. Khi đăng nhập miễn phí, bạn được mở Workspace lưu trữ riêng tư trọn hành trình.</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>2. Hồ sơ tổ chức đám cưới cá nhân của tôi có bị người khác xem được không?</summary>
                <p>Tuyệt đối không. Mọi hồ sơ trong `/to-chuc-dam-cuoi/du-an/{slug}` đều yêu cầu đăng nhập, áp dụng kiểm soát quyền sở hữu `403` độc lập từng tài khoản và gắn thẻ `noindex, nofollow` để không xuất hiện trên công cụ tìm kiếm.</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>3. Tôi có thể liên kết kế hoạch đám cưới với Thiệp Cưới Online Mây Hẹn không?</summary>
                <p>Có. Sau khi chốt ngày cưới và thông tin tiệc trong Workspace, bạn có thể chuyển trực tiếp sang kho <a href="{{ route('templates.index') }}">Mẫu Thiệp Cưới Mây Hẹn</a> để tạo thiệp mời, gửi link Zalo và thu thập phản hồi RSVP đồng bộ.</p>
            </details>

            <div class="mayhen_wedding_seo_links_grid">
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Xem Ngày Cưới Hỏi 3D →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Lọc ngày cuối tuần, giờ đón dâu &amp; so sánh 5 ngày</div>
                </a>
                <a href="{{ route('wedding.ceremonies') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Nghi Lễ 3 Miền &amp; Pháp Lý →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Mâm tráp 3D Bắc–Trung–Nam &amp; dichvucong.gov.vn</div>
                </a>
                <a href="{{ route('templates.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Kho Mẫu Thiệp Cưới Online →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Thiết kế thiệp cưới Quiet Luxury &amp; RSVP 1 chạm</div>
                </a>
            </div>
        </article>
    </div>
</div>

<div id="mayhenWeddingOverlay" class="mayhen_wedding_planner_overlay" role="status" aria-live="polite">
    <div class="mayhen_wedding_planner_overlay_box">
        <div class="mayhen_wedding_planner_spinner"></div>
        <h3 data-overlay-title style="margin:0 0 8px;font-size:20px;">Đang khởi tạo...</h3>
        <p data-overlay-desc style="margin:0;font-size:13.5px;color:#5F5750;">Vui lòng đợi trong giây lát.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/wedding-journey.js') }}"></script>
@endpush
