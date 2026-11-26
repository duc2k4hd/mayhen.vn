@extends('client.layouts.master')

@php
    $focusMeta = [
        'ngay-an-hoi' => ['title' => 'Xem Ngày Ăn Hỏi (Đính Hôn) Đẹp Online Miễn Phí', 'lead' => 'Lọc ngày ăn hỏi đẹp theo Trực Định/Thành/Mãn, giờ Hoàng Đạo buổi sáng và lịch cuối tuần thuận tiện hai họ.'],
        'ngay-dam-ngo' => ['title' => 'Xem Ngày Dạm Ngõ Đẹp Online Miễn Phí', 'lead' => 'Chọn ngày dạm ngõ ấm cúng, thuận lịch hai gia đình kết hợp quy tắc Lịch Vạn Niên minh bạch.'],
        'gio-don-dau' => ['title' => 'Xem Giờ Đón Dâu (Rước Dâu) Hoàng Đạo & Lịch Di Chuyển', 'lead' => 'Tra cứu 6 khung giờ Hoàng Đạo ban ngày kèm khoảng đệm di chuyển 30 phút thực tế cho đoàn rước dâu.'],
        'thang-dai-loi' => ['title' => 'Tra Cứu Tháng Đại Lợi Nguyệt (Hành Giá) Theo Tuổi Cô Dâu', 'lead' => 'Tra cứu bảng Hành Giá Đại Lợi Nguyệt dân gian minh bạch nguồn & dị bản, kết hợp lịch thực tế.'],
        'kim-lau' => ['title' => 'Xem Tuổi Kim Lâu Kết Hôn Online Miễn Phí', 'lead' => 'Tính tuổi Kim Lâu theo tuổi mụ cô dâu (chia 9 dư 1, 3, 6, 8) có công tắc tùy chọn và phân biệt rõ với tuổi pháp lý.'],
    ];
    $pageTitle = $focus && isset($focusMeta[$focus])
        ? $focusMeta[$focus]['title']
        : 'Xem Ngày Cưới Hỏi & Tổ Chức Hôn Lễ Online Miễn Phí';
    $pageLead = $focus && isset($focusMeta[$focus])
        ? $focusMeta[$focus]['lead']
        : 'Chọn ngày dạm ngõ, ăn hỏi, đón dâu, thành hôn và đăng ký kết hôn chuẩn Lịch Vạn Niên Việt Nam UTC+7 kết hợp Ràng buộc Thực tế (cuối tuần, ngày bận gia đình, so sánh 3–5 ngày ứng viên).';

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Xem Ngày Cưới Hỏi & Tổ Chức Hôn Lễ Online Miễn Phí — Mây Hẹn',
                'url' => route('wedding.dates.index'),
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
                'inLanguage' => 'vi-VN',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Ngày Cưới Hỏi', 'item' => route('wedding.dates.index')],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Xem ngày cưới trên Mây Hẹn có mất phí hay bắt buộc đăng nhập không?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hoàn toàn miễn phí và không cần đăng nhập cho toàn bộ tính năng xem ngày cưới, lọc cuối tuần, xem giờ Hoàng Đạo đón dâu và so sánh tối đa 5 ngày ứng viên.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Mây Hẹn có chấm điểm phần trăm đại cát giả cho ngày cưới không?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Không. Mây Hẹn tách bạch minh bạch giữa ràng buộc thực tế (lịch rảnh, cuối tuần, ngày bận) và từng quy tắc lịch pháp truyền thống (Hoàng Đạo, 12 Trực, Tam Nương, xung tuổi) để hai gia đình tự quyết định sáng suốt.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Tuổi Kim Lâu có dùng để tính độ tuổi đăng ký kết hôn hợp pháp không?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Tuyệt đối không. Tuổi Kim Lâu là khái niệm văn hóa dân gian tính theo tuổi mụ. Độ tuổi đăng ký kết hôn hợp pháp tại Việt Nam theo Luật Hôn nhân và Gia đình 2014 tính theo ngày tháng năm sinh tròn Dương lịch (Nam từ đủ 20 tuổi, Nữ từ đủ 18 tuổi).'],
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageLead)
@section('canonical_url', $focus ? route('wedding.dates.focus', ['focus' => $focus]) : route('wedding.dates.index'))

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_wedding_date_page"
     data-wedding-page="date-finder"
     data-find-url="{{ route('wedding.dates.find') }}"
     data-compare-url="{{ route('wedding.dates.compare') }}"
     data-initial="{{ json_encode($initialResult, JSON_UNESCAPED_UNICODE) }}">
    <div class="mayhen_wedding_planner_container">
        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Mây Hẹn – The Wedding Journey · Hành Trình Hôn Lễ</span>
            <h1 class="mayhen_wedding_planner_h1">{{ $pageTitle }}</h1>
            <p class="mayhen_wedding_planner_lead">{{ $pageLead }}</p>
            <nav class="mayhen_wedding_planner_subnav" aria-label="Điều hướng Hành Trình Hôn Lễ">
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill {{ ! $focus ? 'is-active' : '' }}">Xem Ngày Cưới</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'ngay-an-hoi']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'ngay-an-hoi' ? 'is-active' : '' }}">Ngày Ăn Hỏi</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'gio-don-dau']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'gio-don-dau' ? 'is-active' : '' }}">Giờ Đón Dâu</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'kim-lau']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'kim-lau' ? 'is-active' : '' }}">Tuổi Kim Lâu</a>
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_pill mayhen_wedding_planner_pill--crimson">Tổ Chức Đám Cưới →</a>
                <a href="{{ route('wedding.ceremonies') }}" class="mayhen_wedding_planner_pill">Nghi Lễ 3 Miền &amp; Pháp Lý</a>
            </nav>
        </section>

        <div class="mayhen_wedding_planner_grid_2">
            {{-- Bộ lọc Ràng buộc Thực tế & Quy tắc Truyền thống --}}
            <aside class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Bộ Lọc Chọn Ngày Thông Minh</h2>
                <p class="mayhen_wedding_planner_card_sub">Kết hợp ràng buộc thực tế (bắt buộc) với tùy chọn lịch pháp truyền thống minh bạch.</p>

                <form id="weddingDateFilterForm">
                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label" for="wdActivity">Nghi lễ cần chọn ngày</label>
                        <select id="wdActivity" name="activity" class="mayhen_wedding_planner_select">
                            @foreach ($activityProfiles as $k => $prof)
                                <option value="{{ $k }}" {{ ($initialResult['activity']['key'] ?? '') === $k ? 'selected' : '' }}>{{ $prof['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mayhen_wedding_planner_row_2">
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label" for="wdMonth">Tháng Dương lịch</label>
                            <select id="wdMonth" name="month" class="mayhen_wedding_planner_select">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) ($initialResult['month'] ?? 1) === $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label" for="wdYear">Năm Dương lịch</label>
                            <select id="wdYear" name="year" class="mayhen_wedding_planner_select">
                                @for ($y = 2025; $y <= 2032; $y++)
                                    <option value="{{ $y }}" {{ (int) ($initialResult['year'] ?? 2026) === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mayhen_wedding_planner_row_2">
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label" for="wdGroomYear">Năm sinh chú rể (tùy chọn)</label>
                            <input id="wdGroomYear" name="groom_year" type="number" min="1950" max="2015" placeholder="VD: 1996" class="mayhen_wedding_planner_input">
                        </div>
                        <div class="mayhen_wedding_planner_field">
                            <label class="mayhen_wedding_planner_label" for="wdBrideYear">Năm sinh cô dâu (tùy chọn)</label>
                            <input id="wdBrideYear" name="bride_year" type="number" min="1950" max="2015" placeholder="VD: 1998" class="mayhen_wedding_planner_input">
                        </div>
                    </div>

                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label" for="wdPrepDays">Số ngày chuẩn bị tối thiểu từ hôm nay</label>
                        <input id="wdPrepDays" name="min_prep_days" type="number" min="0" max="730" value="0" class="mayhen_wedding_planner_input">
                    </div>

                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label" for="wdExcluded">Ngày gia đình bận / cần tránh (YYYY-MM-DD, cách nhau bằng dấu phẩy)</label>
                        <input id="wdExcluded" name="excluded_dates_text" type="text" placeholder="VD: 2026-12-05, 2026-12-12" class="mayhen_wedding_planner_input">
                    </div>

                    <div class="mayhen_wedding_planner_field" style="padding-top:6px;border-top:1px solid #E5DAC8;">
                        <label class="mayhen_wedding_planner_check"><input type="checkbox" name="weekends_only" value="1"> <span>Chỉ hiển thị cuối tuần (Thứ 7 / Chủ nhật)</span></label>
                        <label class="mayhen_wedding_planner_check"><input type="checkbox" name="avoid_clash_couple" value="1" checked> <span>Lưu ý ngày Lục Xung tuổi cô dâu / chú rể</span></label>
                        <label class="mayhen_wedding_planner_check"><input type="checkbox" name="check_kim_lau" value="1" checked> <span>Kiểm tra tuổi Kim Lâu năm cưới (tùy chọn)</span></label>
                        <label class="mayhen_wedding_planner_check"><input type="checkbox" name="check_dai_loi" value="1" {{ $focus === 'thang-dai-loi' ? 'checked' : '' }}> <span>Áp dụng Hành Giá Đại Lợi Nguyệt (quy tắc dân gian phụ)</span></label>
                    </div>

                    <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary" style="width:100%;">
                        Tra Cứu &amp; Lọc Ngày Cưới Ngay
                    </button>
                </form>

                <div style="margin-top:18px;padding-top:16px;border-top:1px solid #E5DAC8;font-size:13px;color:#5F5750;">
                    Chưa xem tuổi hợp hôn trước khi chọn ngày?
                    <a href="{{ route('marriage.compatibility.index') }}" style="color:#85182A;font-weight:700;">Xem Tuổi Vợ Chồng &amp; Cao Ly Đầu Hình →</a>
                </div>
            </aside>

            {{-- Khu vực hiển thị Lịch Tháng, Chi Tiết Ngày & Bảng So Sánh 3+ Ngày --}}
            <div>
                <div id="weddingCoupleMeta"></div>

                <section class="mayhen_wedding_planner_card">
                    <h2 class="mayhen_wedding_planner_card_title">Lịch Tháng Âm Dương &amp; Đánh Giá Minh Bạch</h2>
                    <p class="mayhen_wedding_planner_card_sub">Chạm vào từng ngày để xem đầy đủ 12 giờ Hoàng Đạo, hướng Hỷ Thần, Trực Kiến Trừ và thêm vào bảng so sánh song song.</p>

                    <div class="mayhen_wedding_date_weekdays">
                        <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div style="color:#85182A;">T7</div><div style="color:#85182A;">CN</div>
                    </div>
                    <div id="weddingDateCalendar" class="mayhen_wedding_date_calendar"></div>
                </section>

                <section class="mayhen_wedding_planner_card">
                    <h2 class="mayhen_wedding_planner_card_title">Phân Tích Chi Tiết Ngày Đang Chọn</h2>
                    <div id="weddingDateDetail"></div>
                </section>

                <section class="mayhen_wedding_planner_card">
                    <h2 class="mayhen_wedding_planner_card_title">So Sánh Song Song 2–5 Ngày Ứng Viên</h2>
                    <p class="mayhen_wedding_planner_card_sub">Đặt tối đa 5 ngày cưới tiềm năng cạnh nhau để hai gia đình cùng thống nhất nhanh chóng.</p>
                    <div id="weddingDateCompareBox"></div>
                </section>

                <section class="mayhen_wedding_planner_card">
                    <h2 class="mayhen_wedding_planner_card_title">Gợi Ý Ngày Nổi Bật Đạt Đủ Ràng Buộc Thực Tế</h2>
                    <div id="weddingDateRecommended"></div>
                </section>
            </div>
        </div>

        {{-- Pillar SEO Content & FAQ --}}
        <article class="mayhen_wedding_planner_seo">
            <h2>Cách Xem Ngày Cưới Hỏi Chuẩn Lịch Pháp Việt Nam Kết Hợp Thực Tế</h2>
            <p>Khác với những công cụ chỉ gắn nhãn chung chung hay chấm tỷ lệ % may mắn cảm tính, hệ thống <strong>Xem Ngày Cưới Hỏi Online Miễn Phí</strong> của Mây Hẹn vận hành trên nền tảng <strong>VietnameseCalendarEngine (chuẩn múi giờ UTC+7 Việt Nam)</strong>, tách bạch rõ ràng 3 lớp dữ liệu:</p>
            <ul>
                <li><strong>Lớp 1 — Ràng buộc thực tế (Hard Constraints):</strong> Lịch cuối tuần (Thứ 7, Chủ nhật), ngày làm việc hành chính của UBND khi đăng ký kết hôn, danh sách ngày bận riêng của gia đình và số ngày chuẩn bị tối thiểu.</li>
                <li><strong>Lớp 2 — Lịch Vạn Niên truyền thống:</strong> Ngày Hoàng Đạo / Hắc Đạo (Ngọc Hạp Thông Thư), Thập Nhị Kiến Trừ (ưu tiên Trực Thành, Khai, Định, Mãn cho hỷ sự), Nhị Thập Bát Tú, Lục Diệu và ghi chú minh bạch ngày Tam Nương, Nguyệt Kỵ.</li>
                <li><strong>Lớp 3 — Tương tác tuổi hai bên &amp; Khung giờ đón dâu khả thi:</strong> Đối chiếu Lục Xung Địa Chi, tuổi Kim Lâu (tùy chọn), Hành Giá Đại Lợi Nguyệt (tùy chọn) và đặc biệt là các khung giờ Hoàng Đạo ban ngày (07:00–17:00) kèm 30 phút đệm di chuyển thực tế.</li>
            </ul>

            <h2>Sau Khi Chốt Ngày Cưới, Bước Tiếp Theo Là Gì?</h2>
            <p>Khi hai gia đình đã thống nhất được 1–3 ngày ứng viên, bạn có thể chuyển ngay sang <a href="{{ route('wedding.planner.index') }}"><strong>Không Gian Tổ Chức Đám Cưới Miễn Phí</strong></a> để lập Checklist co giãn theo ngày cưới, theo dõi Ngân sách VND chống tính trùng tiền cọc, sắp xếp Sơ đồ bàn tiệc 2D và tạo <a href="{{ route('templates.index') }}"><strong>Thiệp Cưới Online Mây Hẹn</strong></a> đồng bộ hoàn chỉnh.</p>

            <h2>Câu Hỏi Thường Gặp Khi Xem Ngày Cưới</h2>
            <details class="mayhen_wedding_planner_faq_item" open>
                <summary>1. Xem ngày cưới tại Mây Hẹn có dùng AI để tự bịa ngày tốt không?</summary>
                <p>Không bao giờ. Toàn bộ lịch Âm Dương, Can Chi, 12 Thần Hoàng Đạo, 12 Trực và 28 Tú đều được tính toán bằng thuật toán thiên văn – lịch pháp xác định (Deterministic Engine). AI chỉ hỗ trợ biên soạn kịch bản MC, lời phát biểu và gợi ý kế hoạch tổ chức.</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>2. Ngày cưới trùng ngày có cả yếu tố thuận lợi và yếu tố lưu tâm thì xử lý thế nào?</summary>
                <p>Trong lịch pháp cổ truyền, rất hiếm ngày đạt 100% mọi hệ quy tắc đồng thời. Mây Hẹn hiển thị minh bạch cả hai cột Thuận lợi và Lưu tâm để hai gia đình ưu tiên sự thuận tiện của khách mời, sức khỏe cặp đôi và chọn khung giờ Hoàng Đạo đẹp trong ngày.</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>3. Tuổi Kim Lâu có ảnh hưởng tới độ tuổi đăng ký kết hôn hợp pháp không?</summary>
                <p>Không. Tuổi Kim Lâu là tập tục tham khảo tính theo tuổi mụ. Còn thủ tục đăng ký kết hôn tại UBND theo Luật Hôn nhân và Gia đình 2014 bắt buộc tính theo ngày tháng năm sinh tròn Dương lịch (Nam từ đủ 20 tuổi, Nữ từ đủ 18 tuổi).</p>
            </details>
        </article>
    </div>
</div>

<div id="mayhenWeddingOverlay" class="mayhen_wedding_planner_overlay" role="status" aria-live="polite">
    <div class="mayhen_wedding_planner_overlay_box">
        <div class="mayhen_wedding_planner_spinner"></div>
        <h3 data-overlay-title style="margin:0 0 8px;font-size:20px;">Đang xử lý...</h3>
        <p data-overlay-desc style="margin:0;font-size:13.5px;color:#5F5750;">Vui lòng đợi trong giây lát.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset_v('assets/client/js/pages/wedding-journey.js') }}" defer></script>
@endpush
