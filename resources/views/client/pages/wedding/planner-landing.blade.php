@extends('client.layouts.master')

@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Tổ Chức Đám Cưới & Lập Kế Hoạch Hôn Lễ Online Miễn Phí — Mây Hẹn',
                'url' => route('wedding.planner.index'),
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
                'inLanguage' => 'vi-VN',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tổ Chức Đám Cưới', 'item' => route('wedding.planner.index')],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Bảng ngân sách đám cưới trên Mây Hẹn tính tiền cọc như thế nào?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Mây Hẹn sử dụng toán học số nguyên VND chính xác và tách bạch Hợp đồng đã chốt, Tiền cọc, Đã thanh toán và Còn phải trả — đảm bảo tiền cọc không bao giờ bị cộng trùng vào tổng giá trị hợp đồng.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Tôi có thể tự sắp xếp sơ đồ bàn tiệc 2D và kiểm tra trùng khách không?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Có. Studio Sơ đồ bàn tiệc 2D cho phép kéo thả vị trí bàn, gán danh sách khách từng bàn, tự động cảnh báo khi bàn bị chồng lấn, vượt sức chứa ghế hoặc xếp trùng một khách vào 2 bàn, và xuất ảnh PNG in gửi nhà hàng.'],
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', 'Tổ Chức Đám Cưới & Lập Kế Hoạch Hôn Lễ Online Miễn Phí')
@section('meta_description', 'Lập kế hoạch tổ chức đám cưới toàn diện miễn phí: Checklist 3 miền co giãn theo ngày cưới, Ngân sách VND chống tính trùng cọc, Sơ đồ bàn tiệc 2D, Kiểm tra xung đột lịch trình và Kịch bản MC.')
@section('canonical_url', route('wedding.planner.index'))

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
        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Mây Hẹn – The Wedding Journey · Trọn vẹn từng nghi lễ, chỉn chu từng khoảnh khắc</span>
            <h1 class="mayhen_wedding_planner_h1">Tổ Chức Đám Cưới &amp; Lập Kế Hoạch Hôn Lễ Online Miễn Phí</h1>
            <p class="mayhen_wedding_planner_lead">Nền tảng quản trị toàn bộ hành trình hôn lễ Việt Nam: từ Xem ngày cưới, Checklist 3 miền, Ngân sách VND chuẩn kế toán, Sơ đồ bàn tiệc 2D, Kiểm tra xung đột lịch trình ngày cưới đến Thiệp Cưới Online đồng bộ.</p>
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

        {{-- Khối Khởi tạo Hồ Sơ Tổ Chức Đám Cưới Cá Nhân --}}
        <section class="mayhen_wedding_planner_card">
            <div class="mayhen_wedding_planner_grid_equal">
                <div>
                    <h2 class="mayhen_wedding_planner_card_title">Khởi Tạo Hồ Sơ Tổ Chức Đám Cưới Riêng</h2>
                    <p class="mayhen_wedding_planner_card_sub">Tự động sinh Checklist 13+ hạng mục co giãn theo ngày cưới thực tế, bảng Ngân sách VND 8 hạng mục, Sơ đồ 8 bàn tiệc 2D và Lịch trình ngày cưới mẫu.</p>
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
                            <p style="font-size:13.5px;color:#5F5750;margin:0 0 16px;">Đăng nhập miễn phí để mở bảng điều khiển Checklist, Ngân sách VND, Sơ đồ bàn 2D và Lịch trình ngày cưới lưu trên đám mây.</p>
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

        {{-- Pillar SEO Content --}}
        <article class="mayhen_wedding_planner_seo">
            <h2>Lộ Trình Tổ Chức Đám Cưới Việt Nam Chuẩn Chỉ Từ A Đến Z</h2>
            <p>Tổ chức một hôn lễ trọn vẹn không chỉ dừng lại ở việc chọn được một ngày lành, mà là bài toán điều phối đồng thời hàng chục đầu việc giữa hai bên gia đình: ngân sách, địa điểm, lễ vật 3 miền, khách mời, thiệp cưới, bàn tiệc và lịch trình giờ giấc ngày trọng đại. <strong>Mây Hẹn – The Wedding Journey</strong> kết nối liền mạch toàn bộ chuỗi công việc này trên một nền tảng duy nhất:</p>
            <ol>
                <li><strong>Giai đoạn Khởi động (6–12 tháng trước cưới):</strong> Thống nhất ngân sách tổng, đóng góp hai họ, <a href="{{ route('marriage.compatibility.index') }}">xem tuổi vợ chồng</a> và <a href="{{ route('wedding.dates.index') }}">chọn 2–3 ngày cưới ứng viên</a>.</li>
                <li><strong>Giai đoạn Khóa dịch vụ &amp; Pháp lý (3–6 tháng):</strong> Đặt cọc sảnh tiệc, ekip quay chụp, trang phục và nộp hồ sơ <a href="{{ route('wedding.ceremonies') }}">đăng ký kết hôn trực tuyến trên dichvucong.gov.vn</a>.</li>
                <li><strong>Giai đoạn Thiệp cưới &amp; Khách mời (1–2 tháng):</strong> Thiết kế <a href="{{ route('templates.index') }}">Thiệp Cưới Online Mây Hẹn</a>, gửi link cá nhân hóa qua Zalo và theo dõi xác nhận tham dự (RSVP) theo thời gian thực.</li>
                <li><strong>Giai đoạn Vận hành ngày cưới (2 tuần cuối):</strong> Chốt sơ đồ bàn tiệc 2D không trùng khách, kiểm tra xung đột lịch trình từng khung giờ và gửi kịch bản MC cho ekip.</li>
            </ol>
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
    <script src="{{ asset_v('assets/client/js/pages/wedding-journey.js') }}" defer></script>
@endpush
