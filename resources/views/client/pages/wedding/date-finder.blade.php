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
        : 'Chọn ngày dạm ngõ, ăn hỏi, đón dâu, thành hôn và đăng ký kết hôn chuẩn Lịch Vạn Niên Việt Nam UTC+7 kết hợp Ràng buộc Thực tế (cuối tuần, ngày bận gia đình, so sánh 2–5 ngày ứng viên) cùng Không gian Lịch Hỷ Sự 3D.';
    $canonicalUrl = $focus ? route('wedding.dates.focus', ['focus' => $focus]) : route('wedding.dates.index');

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => $pageTitle . ' — Mây Hẹn',
                'url' => $canonicalUrl,
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
                'inLanguage' => 'vi-VN',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => array_values(array_filter([
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Ngày Cưới Hỏi', 'item' => route('wedding.dates.index')],
                    $focus ? ['@type' => 'ListItem', 'position' => 3, 'name' => $pageTitle, 'item' => $canonicalUrl] : null,
                ])),
            ],
            [
                '@type' => 'Article',
                'headline' => $pageTitle,
                'description' => $pageLead,
                'inLanguage' => 'vi-VN',
                'datePublished' => '2026-11-26T08:00:00+07:00',
                'dateModified' => '2026-11-26T14:00:00+07:00',
                'author' => ['@type' => 'Organization', 'name' => 'Ban Nghiên Cứu Văn Hóa & Lịch Pháp Mây Hẹn', 'url' => route('home')],
                'publisher' => ['@type' => 'Organization', 'name' => 'Mây Hẹn', 'url' => route('home')],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
            ],
            [
                '@type' => 'HowTo',
                'name' => 'Cách xem ngày cưới hỏi chuẩn Lịch Vạn Niên Việt Nam và lịch thực tế trong 5 bước',
                'step' => [
                    ['@type' => 'HowToStep', 'name' => 'Bước 1: Chọn đúng hồ sơ nghi lễ', 'text' => 'Chọn riêng từng nghi lễ (Dạm ngõ, Ăn hỏi, Lễ gia tiên, Đón dâu, Thành hôn, Tiệc cưới, Đăng ký kết hôn, Lại mặt) vì mỗi lễ áp dụng nhóm Trực khác nhau.'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 2: Đặt ràng buộc thực tế trước', 'text' => 'Bật lọc cuối tuần (Thứ 7/Chủ nhật), nhập số ngày chuẩn bị tối thiểu và các ngày bận hoặc ngày giỗ của hai gia đình.'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 3: Đối chiếu tuổi cô dâu chú rể tùy chọn', 'text' => 'Nhập năm sinh hai bên để xem ngày Lục Xung, kiểm tra Kim Lâu năm cưới và Hành Giá Đại Lợi Nguyệt (nếu gia đình theo truyền thống).'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 4: Chọn khung giờ Hoàng Đạo ban ngày khả thi', 'text' => 'Ưu tiên các khung giờ 07:00–17:00 có kèm 30 phút đệm di chuyển giữa nhà gái, nhà trai và nhà hàng.'],
                    ['@type' => 'HowToStep', 'name' => 'Bước 5: So sánh song song 2–5 ngày ứng viên', 'text' => 'Đặt các ngày tiềm năng cạnh nhau để hai bên gia đình thống nhất trước khi đặt cọc sảnh tiệc.'],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => 'Xem ngày cưới trên Mây Hẹn có mất phí hay bắt buộc đăng nhập không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hoàn toàn miễn phí và không cần đăng nhập cho toàn bộ công cụ xem ngày cưới, lọc cuối tuần, xem giờ Hoàng Đạo đón dâu và so sánh tối đa 5 ngày ứng viên.']],
                    ['@type' => 'Question', 'name' => 'Mây Hẹn có chấm điểm phần trăm đại cát giả cho ngày cưới không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Không. Mây Hẹn tách bạch minh bạch giữa ràng buộc thực tế và từng quy tắc lịch pháp truyền thống (Hoàng Đạo, 12 Trực, Tam Nương, xung tuổi) để hai gia đình tự quyết định sáng suốt.']],
                    ['@type' => 'Question', 'name' => 'Tuổi Kim Lâu có dùng để tính độ tuổi đăng ký kết hôn hợp pháp không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Tuyệt đối không. Tuổi Kim Lâu là tập tục văn hóa tính theo tuổi mụ. Độ tuổi đăng ký kết hôn hợp pháp theo Luật Hôn nhân và Gia đình 2014 tính theo ngày tháng năm sinh tròn Dương lịch (Nam từ đủ 20 tuổi, Nữ từ đủ 18 tuổi).']],
                    ['@type' => 'Question', 'name' => 'Nên chọn ngày cưới theo tuổi cô dâu hay chú rể?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Theo tập tục phổ biến “Lấy vợ xem tuổi đàn bà”, nhiều gia đình xét Kim Lâu và Đại Lợi Nguyệt theo tuổi cô dâu, đồng thời kiểm tra ngày cưới tránh Lục Xung với Địa Chi của cả hai người để hài hòa nhất.']],
                    ['@type' => 'Question', 'name' => 'Ngày Hoàng Đạo nhưng trùng Tam Nương hoặc Trực Phá thì xử lý thế nào?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Đây là xung đột phổ biến giữa các hệ lịch cổ. Nếu lịch gia đình linh hoạt, bạn nên chọn ngày khác không vướng lưu ý; nếu ngày đó là cuối tuần duy nhất thuận tiện, có thể chọn khung giờ Hoàng Đạo đẹp trong ngày và ưu tiên sự đồng thuận hai họ.']],
                    ['@type' => 'Question', 'name' => 'Xem ngày đăng ký kết hôn có nên bật lọc cuối tuần không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Không nên. UBND cấp xã/phường làm việc hành chính từ Thứ 2 đến Thứ 6 (một số nơi làm sáng Thứ 7). Mây Hẹn tự động ưu tiên ngày làm việc hành chính khi bạn chọn hồ sơ Đăng ký kết hôn.']],
                ],
            ],
        ],
    ];
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageLead)
@section('canonical_url', $canonicalUrl)

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
        <nav class="mayhen_wedding_seo_breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('wedding.dates.index') }}">Xem Ngày Cưới Hỏi</a>
            @if ($focus)
                <span>/</span>
                <span>{{ $pageTitle }}</span>
            @endif
        </nav>

        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Mây Hẹn – The Wedding Journey · Chuẩn Lịch Việt Nam UTC+7</span>
            <h1 class="mayhen_wedding_planner_h1">{{ $pageTitle }}</h1>
            <p class="mayhen_wedding_planner_lead">{{ $pageLead }}</p>
            <nav class="mayhen_wedding_planner_subnav" aria-label="Điều hướng Hành Trình Hôn Lễ">
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill {{ ! $focus ? 'is-active' : '' }}">Xem Ngày Cưới</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'ngay-an-hoi']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'ngay-an-hoi' ? 'is-active' : '' }}">Ngày Ăn Hỏi</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'ngay-dam-ngo']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'ngay-dam-ngo' ? 'is-active' : '' }}">Ngày Dạm Ngõ</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'gio-don-dau']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'gio-don-dau' ? 'is-active' : '' }}">Giờ Đón Dâu</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'thang-dai-loi']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'thang-dai-loi' ? 'is-active' : '' }}">Tháng Đại Lợi</a>
                <a href="{{ route('wedding.dates.focus', ['focus' => 'kim-lau']) }}" class="mayhen_wedding_planner_pill {{ $focus === 'kim-lau' ? 'is-active' : '' }}">Tuổi Kim Lâu</a>
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_pill mayhen_wedding_planner_pill--crimson">Tổ Chức Đám Cưới →</a>
            </nav>
        </section>

        {{-- KHÔNG GIAN LỊCH HỶ SỰ 3D TƯƠNG TÁC --}}
        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Không Gian Lịch Hỷ Sự &amp; La Bàn 12 Giờ Hoàng Đạo 3D</h2>
            <p class="mayhen_wedding_planner_card_sub">Kéo chuột/chạm hoặc chọn góc nhìn 3D bên dưới — toàn bộ dữ liệu trên khối 3D tự động đồng bộ tức thì theo ngày bạn chọn trên lịch.</p>

            <div class="mayhen_wedding_3d_toolbar">
                <div class="mayhen_wedding_3d_presets">
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-3d-preset-target="almanac3DScene" data-3d-preset="front" data-rx="0" data-ry="0">Mặt Trước</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn is-active" data-3d-preset-target="almanac3DScene" data-3d-preset="editorial" data-rx="10" data-ry="-14">Góc Nghiêng Editorial</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-3d-preset-target="almanac3DScene" data-3d-preset="orbit" data-rx="18" data-ry="18">Góc Nhìn Lịch Pháp</button>
                    <button type="button" class="mayhen_wedding_3d_preset_btn" data-3d-preset-target="almanac3DScene" data-3d-preset="auto">Tự Động Xoay 3D</button>
                </div>
                <div class="mayhen_wedding_3d_sliders">
                    <label>Nghiêng X <input type="range" min="-10" max="32" value="10" data-3d-rx="almanac3DScene"></label>
                    <label>Xoay Y <input type="range" min="-38" max="38" value="-14" data-3d-ry="almanac3DScene"></label>
                </div>
            </div>

            <div id="almanac3DStage" class="mayhen_wedding_3d_stage">
                <div id="almanac3DScene" class="mayhen_wedding_3d_almanac_scene">
                    <div id="almanac3DCardFace" class="mayhen_wedding_3d_card_face"></div>
                    <div id="almanac3DCompassPanel" class="mayhen_wedding_3d_compass_panel"></div>
                </div>
            </div>
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

            {{-- Khu vực hiển thị Lịch Tháng, Chi Tiết Ngày & Bảng So Sánh 2–5 Ngày --}}
            <div>
                <div id="weddingCoupleMeta"></div>

                <section class="mayhen_wedding_planner_card">
                    <h2 class="mayhen_wedding_planner_card_title">Lịch Tháng Âm Dương &amp; Đánh Giá Minh Bạch</h2>
                    <p class="mayhen_wedding_planner_card_sub">Chạm vào từng ngày để cập nhật khối Lịch Hỷ Sự 3D, xem 12 giờ Hoàng Đạo, hướng Hỷ Thần và đưa vào bảng so sánh.</p>

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

        {{-- PILLAR SEO CONTENT CHUẨN GOOGLE E-E-A-T --}}
        <article class="mayhen_wedding_planner_seo">
            <div class="mayhen_wedding_seo_meta_bar">
                <span><strong>Biên tập:</strong> Ban Nghiên Cứu Văn Hóa &amp; Lịch Pháp Mây Hẹn</span>
                <span><strong>Chuẩn lịch:</strong> Âm lịch Việt Nam UTC+7 (Hồ Ngọc Đức)</span>
                <span><strong>Cập nhật:</strong> Tháng 11/2026</span>
            </div>

            <nav class="mayhen_wedding_seo_toc" aria-label="Mục lục bài viết">
                <p class="mayhen_wedding_seo_toc_title">Mục Lục Cẩm Nang Xem Ngày Cưới Hỏi Chuẩn Lịch Pháp</p>
                <ol class="mayhen_wedding_seo_toc_list">
                    <li><a href="#quy-trinh-5-buoc">1. Quy trình 5 bước chọn ngày cưới kết hợp thực tế</a></li>
                    <li><a href="#bang-12-truc">2. Bảng giải mã 12 Trực Kiến Trừ cho lễ cưới hỏi</a></li>
                    <li><a href="#gio-don-dau">3. Cách chọn giờ đón dâu Hoàng Đạo &amp; đệm di chuyển</a></li>
                    <li><a href="#kim-lau-va-dai-loi">4. Giải mã tuổi Kim Lâu &amp; Hành Giá Đại Lợi Nguyệt</a></li>
                    <li><a href="#xu-ly-xung-dot">5. Cách xử lý khi ngày đẹp bị xung đột quy tắc</a></li>
                    <li><a href="#faq-ngay-cuoi">6. Câu hỏi thường gặp khi xem ngày cưới hỏi</a></li>
                </ol>
            </nav>

            <h2 id="quy-trinh-5-buoc">1. Quy Trình 5 Bước Xem Ngày Cưới Hỏi Chuẩn Lịch Pháp Việt Nam &amp; Thực Tế</h2>
            <p>Khác với các công cụ chỉ gắn nhãn cảm tính hoặc tự chấm điểm <em>"98% đại cát"</em> thiếu căn cứ, hệ thống <strong>Xem Ngày Cưới Hỏi Online Miễn Phí</strong> của Mây Hẹn vận hành trên bộ máy xác định <strong>VietnameseCalendarEngine (UTC+7 Việt Nam)</strong>, tách bạch rõ ràng giữa <strong>ràng buộc đời sống thực tế</strong> và <strong>tri thức lịch pháp cổ truyền</strong>:</p>
            <ol>
                <li><strong>Xác định đúng hồ sơ nghi lễ:</strong> Mỗi lễ (Dạm ngõ, Ăn hỏi, Lễ gia tiên, Đón dâu, Thành hôn, Tiệc cưới, Đăng ký kết hôn, Lại mặt) có tiêu chí chọn Trực và giờ khác nhau. Đặc biệt, <a href="{{ route('wedding.ceremonies') }}">Đăng ký kết hôn tại UBND</a> phải rơi vào ngày làm việc hành chính.</li>
                <li><strong>Khóa ràng buộc thực tế (Hard Constraints) trước:</strong> Bật lọc cuối tuần (Thứ 7/Chủ nhật) để khách mời tham dự đông đủ, loại trừ trước ngày giỗ hoặc ngày bận của hai họ, và đặt số ngày chuẩn bị tối thiểu để không rơi vào cảnh gấp gáp.</li>
                <li><strong>Đối chiếu lịch Vạn Niên truyền thống:</strong> Kiểm tra đồng thời 12 Thần Hoàng Đạo / Hắc Đạo (Ngọc Hạp Thông Thư), Thập Nhị Kiến Trừ (12 Trực tính theo tháng Tiết Khí), Nhị Thập Bát Tú, Lục Diệu và ngày Tam Nương, Nguyệt Kỵ.</li>
                <li><strong>Kiểm tra tương tác tuổi cô dâu – chú rể (tùy chọn):</strong> Đối chiếu Lục Xung Địa Chi ngày với tuổi hai bên, tra cứu tuổi Kim Lâu theo năm cưới và bảng Hành Giá Đại Lợi Nguyệt (khi hai gia đình mong muốn tham khảo thêm).</li>
                <li><strong>So sánh song song 2–5 ngày ứng viên:</strong> Đặt các ngày đẹp lên cùng một bảng để kiểm tra tình trạng trống sảnh tiệc và thống nhất nhanh giữa hai gia đình.</li>
            </ol>

            <h2 id="bang-12-truc">2. Bảng Giải Mã 12 Trực (Thập Nhị Kiến Trừ) Trong Chọn Ngày Cưới Hỏi</h2>
            <p>Trong lịch pháp cổ truyền, <strong>Thập Nhị Kiến Trừ</strong> (tính từ tháng Tiết Khí Lập Xuân = tháng Dần) là lớp quy tắc quan trọng bậc nhất khi chọn ngày hỷ sự. Bảng dưới đây tổng hợp ý nghĩa từng Trực áp dụng trực tiếp trên công cụ Mây Hẹn:</p>
            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead>
                        <tr><th>Tên Trực</th><th>Mức độ với Cưới Hỏi</th><th>Nghi lễ phù hợp nhất</th><th>Giải nghĩa truyền thống</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Trực Thành</strong></td><td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--favorable">Rất thuận</span></td><td>Thành hôn, Đón dâu, Ăn hỏi</td><td>Thành tựu vẹn toàn, thuận hầu hết các đại sự khởi đầu cuộc sống mới.</td></tr>
                        <tr><td><strong>Trực Định</strong></td><td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--favorable">Rất thuận</span></td><td>Ăn hỏi, Đăng ký kết hôn, Dạm ngõ</td><td>An định bền chặt, đặc biệt hợp ký kết hôn thú và lễ đính hôn.</td></tr>
                        <tr><td><strong>Trực Mãn</strong></td><td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--favorable">Thuận lợi</span></td><td>Tiệc cưới, Ăn hỏi, Gia tiên</td><td>Đủ đầy viên mãn, tượng trưng cho phúc lộc và sự sung túc.</td></tr>
                        <tr><td><strong>Trực Khai</strong></td><td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--favorable">Thuận lợi</span></td><td>Lễ thành hôn, Đón dâu, Xuất hành</td><td>Khai mở hanh thông, thuận lợi cho khởi sự và đón khách.</td></tr>
                        <tr><td><strong>Trực Bình / Thu / Kiến / Chấp / Trừ</strong></td><td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--balanced">Trung hòa</span></td><td>Dạm ngõ, Lại mặt, Tiệc báo hỷ</td><td>Bình hòa ổn định; hoàn toàn dùng tốt khi kết hợp ngày Hoàng Đạo và giờ đẹp.</td></tr>
                        <tr><td><strong>Trực Phá / Nguy / Bế</strong></td><td><span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--caution">Cân nhắc tránh</span></td><td>Không ưu tiên cho đại lễ</td><td>Thiên về tháo dỡ cũ hoặc thu liễm, dân gian thường tránh chọn cho lễ cưới chính.</td></tr>
                    </tbody>
                </table>
            </div>

            <h2 id="gio-don-dau">3. Cách Chọn Giờ Đón Dâu Hoàng Đạo &amp; Tính Khoảng Đệm Di Chuyển Thực Tế</h2>
            <p>Sai lầm phổ biến nhất của nhiều cặp đôi là chỉ chọn giờ Hoàng Đạo trên giấy (ví dụ 09:00–11:00 giờ Tỵ) nhưng quên trừ thời gian di chuyển, kẹt xe và thời gian làm lễ gia tiên tại nhà gái. Công cụ <a href="{{ route('wedding.dates.focus', ['focus' => 'gio-don-dau']) }}">Xem Giờ Đón Dâu Mây Hẹn</a> luôn ưu tiên các <strong>khung giờ Hoàng Đạo ban ngày (07:00–17:00)</strong> và tự động tính sẵn mốc khởi hành dự phòng trước 30 phút:</p>
            <ul>
                <li><strong>Giờ Thìn (07:00 – 09:00):</strong> Lý tưởng cho đoàn nhà trai xuất phát ở nội thành khi thời tiết mát mẻ, ít kẹt xe.</li>
                <li><strong>Giờ Tỵ (09:00 – 11:00):</strong> Khung giờ vàng phổ biến nhất cho Lễ Gia Tiên tại nhà gái và nghi thức rước dâu về nhà trai trước giờ trưa.</li>
                <li><strong>Giờ Ngọ (11:00 – 13:00) &amp; Giờ Mùi (13:00 – 15:00):</strong> Phù hợp khi hai nhà cách xa nhau liên tỉnh hoặc tổ chức tiệc cưới buổi tối.</li>
            </ul>

            <h2 id="kim-lau-va-dai-loi">4. Giải Mã Tuổi Kim Lâu &amp; Bảng Hành Giá Đại Lợi Nguyệt (12 Con Giáp Cô Dâu)</h2>
            <h3>4.1. Cách tính tuổi Kim Lâu năm cưới chuẩn dân gian</h3>
            <p>Tuổi Kim Lâu được tính dựa trên <strong>tuổi mụ của cô dâu</strong> trong năm tổ chức cưới (`Tuổi mụ = Năm cưới Âm lịch − Năm sinh Âm lịch + 1`). Lấy tuổi mụ chia cho 9: nếu số dư thuộc <strong>{1, 3, 6, 8}</strong> thì gọi là năm Kim Lâu (dư 1: <em>Kim Lâu Thân</em>, dư 3: <em>Kim Lâu Thê</em>, dư 6: <em>Kim Lâu Tử</em>, dư 8: <em>Kim Lâu Lục Súc</em>).</p>
            <div class="mayhen_wedding_seo_callout">
                <strong>Phân biệt rạch ròi Văn hóa &amp; Pháp lý:</strong> Tuổi Kim Lâu là tập tục tham khảo dân gian (nhiều gia đình hiện đại vẫn cưới bình thường khi cặp đôi đã sẵn sàng, hoặc tổ chức sau tiết Đông Chí). <strong>Tuyệt đối không dùng tuổi mụ</strong> để xác định điều kiện đăng ký kết hôn hợp pháp — Luật Hôn nhân và Gia đình 2014 quy định Nam từ đủ 20 tuổi, Nữ từ đủ 18 tuổi tính theo ngày tháng năm sinh tròn Dương lịch.
            </div>

            <h3>4.2. Bảng Hành Giá Đại Lợi Nguyệt theo Địa Chi tuổi cô dâu</h3>
            <p>Quy tắc Hành Giá Đại Lợi Nguyệt trong dân gian (Ngọc Hạp Thông Thư) gợi ý các tháng Âm lịch thuận lợi theo con giáp cô dâu. Lưu ý rằng giữa các bản sách cổ có tồn tại dị bản nhỏ theo vùng miền, nên Mây Hẹn để dưới dạng <strong>công tắc tùy chọn phụ</strong>:</p>
            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead><tr><th>Tuổi cô dâu (Địa Chi)</th><th>Tháng Đại Lợi (Âm lịch)</th><th>Tháng Tiểu Lợi (Âm lịch)</th></tr></thead>
                    <tbody>
                        <tr><td>Tý · Ngọ</td><td>Tháng 6, Tháng 12</td><td>Tháng 1, Tháng 7</td></tr>
                        <tr><td>Sửu · Mùi</td><td>Tháng 5, Tháng 11</td><td>Tháng 4, Tháng 10</td></tr>
                        <tr><td>Dần · Thân</td><td>Tháng 2, Tháng 8</td><td>Tháng 3, Tháng 9</td></tr>
                        <tr><td>Mão · Dậu</td><td>Tháng 1, Tháng 7</td><td>Tháng 6, Tháng 12</td></tr>
                        <tr><td>Thìn · Tuất</td><td>Tháng 4, Tháng 10</td><td>Tháng 5, Tháng 11</td></tr>
                        <tr><td>Tỵ · Hợi</td><td>Tháng 3, Tháng 9</td><td>Tháng 2, Tháng 8</td></tr>
                    </tbody>
                </table>
            </div>

            <h2 id="xu-ly-xung-dot">5. Xử Lý Thế Nào Khi Ngày Đẹp Bị Xung Đột Giữa Các Hệ Quy Tắc?</h2>
            <p>Thực tế, rất hiếm ngày đạt đồng thời 100% mọi hệ lịch cổ (vừa Hoàng Đạo, vừa Trực Thành/Định, vừa cuối tuần, vừa không Tam Nương, vừa Đại Lợi Nguyệt, vừa không xung tuổi). Khi gặp xung đột, thứ tự ưu tiên văn minh được các chuyên gia tổ chức hôn lễ khuyến nghị là: <strong>(1) Sự đồng thuận và sức khỏe của hai gia đình</strong> → <strong>(2) Lịch rảnh cuối tuần &amp; tình trạng sảnh tiệc</strong> → <strong>(3) Tránh Trực Phá/Nguy &amp; Lục Xung trực tiếp</strong> → <strong>(4) Chọn khung giờ Hoàng Đạo ban ngày thuận tiện di chuyển</strong>.</p>

            <h2 id="faq-ngay-cuoi">6. Câu Hỏi Thường Gặp Khi Xem Ngày Cưới Hỏi</h2>
            <details class="mayhen_wedding_planner_faq_item" open>
                <summary>1. Xem ngày cưới tại Mây Hẹn có dùng AI để tự bịa ngày tốt không?</summary>
                <p>Không bao giờ. Toàn bộ lịch Âm Dương, Can Chi, 12 Thần Hoàng Đạo, 12 Trực và 28 Tú đều tính bằng thuật toán thiên văn – lịch pháp xác định (Deterministic Engine). AI chỉ hỗ trợ biên soạn kịch bản MC, lời phát biểu và tối ưu tiến độ kế hoạch cưới.</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>2. Nên xem ngày cưới trước ngày tổ chức bao lâu là hợp lý nhất?</summary>
                <p>Lý tưởng nhất là trước từ <strong>6 đến 12 tháng</strong> để hai gia đình có đủ thời gian khảo sát và đặt cọc nhà hàng đẹp vào mùa cưới cao điểm (tháng 9–12 và tháng 2–4 hằng năm).</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>3. Ăn hỏi và đón dâu tổ chức cùng một ngày có được không?</summary>
                <p>Hoàn toàn được và ngày càng phổ biến với các cặp đôi ở xa nhau. Khi gộp lễ trong cùng một ngày, bạn chỉ cần chọn 1 ngày thuận lợi chung và phân bổ rõ 2 khung giờ Hoàng Đạo buổi sáng có khoảng đệm 30 phút nghỉ ngơi.</p>
            </details>
            <details class="mayhen_wedding_planner_faq_item">
                <summary>4. Sau khi chọn được ngày cưới đẹp trên Mây Hẹn, tôi nên làm gì tiếp theo?</summary>
                <p>Bạn có thể mở ngay <a href="{{ route('wedding.planner.index') }}"><strong>Workspace Tổ Chức Đám Cưới Miễn Phí</strong></a> để tự động tạo Checklist co giãn theo ngày cưới, lập bảng Ngân sách VND, xếp Sơ đồ bàn tiệc 2D/3D và thiết kế <a href="{{ route('templates.index') }}"><strong>Thiệp Cưới Online Mây Hẹn</strong></a>.</p>
            </details>

            <div class="mayhen_wedding_seo_links_grid">
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Tổ Chức Đám Cưới Online →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Checklist 3 miền, Ngân sách VND &amp; Sảnh 3D</div>
                </a>
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Xem Tuổi Vợ Chồng &amp; Cao Ly →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">120 tổ hợp Cao Ly, Nạp Âm &amp; Bát Trạch</div>
                </a>
                <a href="{{ route('calendar.index') }}" class="mayhen_wedding_seo_link_card">
                    <strong>Vạn Niên Lịch Âm Dương →</strong>
                    <div style="font-size:12.5px;color:#5F5750;margin-top:4px;">Đổi ngày Âm Dương 1900–2100 &amp; Tiết Khí</div>
                </a>
            </div>
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
