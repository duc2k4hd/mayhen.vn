@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $currentYear = now('Asia/Ho_Chi_Minh')->year;

    $sectionMeta = [
        '12-cung' => [
            'title' => 'Ý Nghĩa 12 Cung Tử Vi Đẩu Số Chuẩn Nam Phái | ' . $brandName,
            'h1' => '12 Cung Tử Vi Đẩu Số',
            'desc' => 'Tra cứu ý nghĩa cổ học và ứng dụng hiện đại của 12 Cung trên lá số Tử Vi: Mệnh, Huynh Đệ, Phu Thê, Tử Tức, Tài Bạch, Tật Ách, Thiên Di, Nô Bộc, Quan Lộc, Điền Trạch, Phúc Đức và Phụ Mẫu.',
            'route' => route('tuvi.palaces'),
        ],
        'tinh-dieu' => [
            'title' => '14 Chính Tinh & Phụ Tinh Tử Vi Đẩu Số Nam Phái | ' . $brandName,
            'h1' => '14 Chính Tinh, Lục Cát, Lục Sát & Tứ Hóa',
            'desc' => 'Từ điển tra cứu 14 Chính Tinh, hệ thống Phụ Tinh và quy ước Tứ Hóa chuẩn Nam Phái Việt Nam (Tử Vi Đẩu Số Tân Biên) — không gán nhãn tốt/xấu tuyệt đối.',
            'route' => route('tuvi.stars'),
        ],
        'cach-cuc' => [
            'title' => 'Hệ Thống Cách Cục Tử Vi Đẩu Số Kiểm Chứng | ' . $brandName,
            'h1' => 'Hệ Thống Cách Cục Tử Vi Chuẩn Nam Phái',
            'desc' => 'Tổng hợp các cách cục kinh điển như Tử Phủ Vũ Tướng, Sát Phá Tham, Cơ Nguyệt Đồng Lương, Cự Nhật, Song Lộc Giao Trì, Mệnh Vô Chính Diệu kèm điều kiện định lượng rõ ràng.',
            'route' => route('tuvi.patterns'),
        ],
        '12-con-giap' => [
            'title' => 'Tử Vi 12 Con Giáp & Khác Biệt Với Tử Vi Đẩu Số | ' . $brandName,
            'h1' => 'Tổng Quan 12 Con Giáp (12 Địa Chi)',
            'desc' => 'Khám phá đặc điểm văn hóa, tình cảm và lời khuyên ứng xử cho 12 Con Giáp — phân tách minh bạch giữa Tử Vi theo năm sinh với Tử Vi Đẩu Số 12 cung đầy đủ.',
            'route' => route('tuvi.zodiacs'),
        ],
        'nam' => [
            'title' => 'Xem Tử Vi Năm ' . $currentYear . ' — Đại Hạn, Tiểu Hạn & Lưu Niên | ' . $brandName,
            'h1' => 'Cách Đọc Vận Trình Năm ' . $currentYear . ' (Đại Hạn • Tiểu Hạn • Lưu Niên)',
            'desc' => 'Hướng dẫn hiểu đúng cách phối hợp Đại Hạn 10 năm, Tiểu Hạn và Lưu Niên năm ' . $currentYear . ' theo Nam Phái Việt Nam một cách bình tĩnh, chủ động và không mê tín.',
            'route' => route('tuvi.yearly'),
        ],
    ];
    $activeMeta = $sectionMeta[$section] ?? $sectionMeta['12-cung'];

    $schemaGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'CollectionPage',
                'name' => $activeMeta['h1'],
                'url' => $activeMeta['route'],
                'description' => $activeMeta['desc'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tử Vi – Thiên Mệnh', 'item' => route('tuvi.index')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $activeMeta['h1'], 'item' => $activeMeta['route']],
                ],
            ],
        ],
    ];
@endphp

@section('title', $activeMeta['title'])
@section('meta_description', $activeMeta['desc'])
@section('canonical', $activeMeta['route'])

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_tuvi_page">
    <section class="mayhen_tuvi_celestial_hero" style="padding: 40px 0 48px;">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_hero_head">
                <span class="mayhen_tuvi_kicker">✦ Bách Khoa Tử Vi Mây Hẹn • {{ $manifest['school_name'] ?? 'Nam Phái Việt Nam' }}</span>
                <h1 class="mayhen_tuvi_hero_h1" style="font-size: clamp(26px, 3.5vw, 40px);">{{ $activeMeta['h1'] }}</h1>
                <p class="mayhen_tuvi_hero_lead">{{ $activeMeta['desc'] }}</p>

                <div class="mayhen_tuvi_btn_row" style="justify-content: center;">
                    <a href="{{ route('tuvi.palaces') }}" class="{{ $section === '12-cung' ? 'mayhen_tuvi_btn_gold' : 'mayhen_tuvi_btn_outline' }} mayhen_tuvi_btn_sm">12 Cung</a>
                    <a href="{{ route('tuvi.stars') }}" class="{{ $section === 'tinh-dieu' ? 'mayhen_tuvi_btn_gold' : 'mayhen_tuvi_btn_outline' }} mayhen_tuvi_btn_sm">14 Chính Tinh &amp; Phụ Tinh</a>
                    <a href="{{ route('tuvi.patterns') }}" class="{{ $section === 'cach-cuc' ? 'mayhen_tuvi_btn_gold' : 'mayhen_tuvi_btn_outline' }} mayhen_tuvi_btn_sm">Cách Cục</a>
                    <a href="{{ route('tuvi.zodiacs') }}" class="{{ $section === '12-con-giap' ? 'mayhen_tuvi_btn_gold' : 'mayhen_tuvi_btn_outline' }} mayhen_tuvi_btn_sm">12 Con Giáp</a>
                    <a href="{{ route('tuvi.yearly') }}" class="{{ $section === 'nam' ? 'mayhen_tuvi_btn_gold' : 'mayhen_tuvi_btn_outline' }} mayhen_tuvi_btn_sm">Tử Vi Năm {{ $currentYear }}</a>
                    <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm">✦ Lập Lá Số Cá Nhân</a>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_tuvi_section_light">
        <div class="mayhen_tuvi_container">
            @if($section !== 'nam')
                <div style="max-width: 520px; margin: 0 auto 26px;">
                    <input id="mayhenTuViWikiSearch"
                           type="search"
                           class="mayhen_tuvi_input"
                           placeholder="Tìm nhanh không dấu (ví dụ: phu the, tu vi, hoa loc, sat pha tham...)"
                           aria-label="Tìm kiếm trong Bách Khoa Tử Vi">
                </div>
            @endif

            @if($section === '12-cung')
                <div class="mayhen_tuvi_wiki_grid">
                    @foreach($palacesMeta as $code => $p)
                        <article class="mayhen_tuvi_card" data-tuvi-wiki-item="{{ ($p['name'] ?? '') . ' ' . ($p['hanzi'] ?? '') . ' ' . ($p['card_title'] ?? '') . ' ' . ($p['modern_meaning'] ?? '') }}">
                            <span class="mayhen_tuvi_badge">{{ $p['hanzi'] ?? '' }} • {{ $p['card_title'] ?? '' }}</span>
                            <h3>{{ $p['name'] ?? $code }}</h3>
                            <p style="margin-bottom: 8px;"><strong>Cổ học:</strong> {{ $p['classical_meaning'] ?? '' }}</p>
                            <p style="margin-bottom: 8px;"><strong>Ứng dụng hiện đại:</strong> {{ $p['modern_meaning'] ?? '' }}</p>
                            @if(! empty($p['vcd_note']))
                                <p style="font-size: 13px; color: #5C5044; margin-bottom: 6px;"><strong>Khi Vô Chính Diệu:</strong> {{ $p['vcd_note'] }}</p>
                            @endif
                            @if(! empty($p['common_misconception']))
                                <p style="font-size: 12.5px; color: #85182A;"><strong>Đính chính ngộ nhận:</strong> {{ $p['common_misconception'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @elseif($section === 'tinh-dieu')
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 16px;">14 Chính Tinh (Hệ Tử Vi &amp; Hệ Thiên Phủ)</h2>
                <div class="mayhen_tuvi_wiki_grid" style="margin-bottom: 34px;">
                    @foreach($majorStarsMeta as $code => $s)
                        <article class="mayhen_tuvi_card" data-tuvi-wiki-item="{{ ($s['name'] ?? '') . ' ' . ($s['hanzi'] ?? '') . ' ' . ($s['archetype'] ?? '') . ' ' . ($s['core_meaning'] ?? '') }}">
                            <span class="mayhen_tuvi_badge">{{ $s['hanzi'] ?? '' }} • Hành {{ $s['element'] ?? '' }} • {{ $s['yin_yang'] ?? '' }}</span>
                            <h3>{{ $s['name'] ?? $code }}</h3>
                            <p style="font-size: 13px; color: #85182A; font-weight: 700; margin-bottom: 6px;">{{ $s['archetype'] ?? '' }}</p>
                            <p style="margin-bottom: 6px;">{{ $s['core_meaning'] ?? '' }}</p>
                            <p style="font-size: 13px; margin-bottom: 4px;"><strong>Phát huy:</strong> {{ $s['strengths'] ?? '' }}</p>
                            <p style="font-size: 13px;"><strong>Lưu tâm:</strong> {{ $s['watch_outs'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>

                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 16px;">Phụ Tinh, Lục Cát, Lục Sát, Tứ Hóa &amp; Tuần Triệt</h2>
                <div class="mayhen_tuvi_wiki_grid">
                    @foreach($auxStarsMeta as $code => $a)
                        <article class="mayhen_tuvi_card" data-tuvi-wiki-item="{{ ($a['name'] ?? '') . ' ' . ($a['hanzi'] ?? '') . ' ' . ($a['group'] ?? '') . ' ' . ($a['meaning'] ?? '') }}">
                            <span class="mayhen_tuvi_badge">{{ $a['hanzi'] ?? '' }} • Hành {{ $a['element'] ?? '' }}</span>
                            <h3>{{ $a['name'] ?? $code }}</h3>
                            <p>{{ $a['meaning'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @elseif($section === 'cach-cuc')
                <div class="mayhen_tuvi_layers_grid">
                    @foreach($patternsCatalog as $pat)
                        <article class="mayhen_tuvi_card" data-tuvi-wiki-item="{{ ($pat['name'] ?? '') . ' ' . ($pat['hanzi'] ?? '') . ' ' . ($pat['plain_explanation'] ?? '') }}">
                            <span class="mayhen_tuvi_badge">{{ $pat['hanzi'] ?? '' }} • {{ $pat['source'] ?? 'Nam Phái' }}</span>
                            <h3>{{ $pat['name'] ?? '' }}</h3>
                            <p style="margin-bottom: 8px;">{{ $pat['plain_explanation'] ?? '' }}</p>
                            <p style="font-size: 13px; color: #5C5044;"><strong>Góc nhìn chuyên sâu:</strong> {{ $pat['expert_explanation'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @elseif($section === '12-con-giap')
                <div class="mayhen_tuvi_alert_info" style="margin-bottom: 22px;">
                    <strong>Phân biệt minh bạch:</strong> Tử Vi 12 Con Giáp chỉ xét Địa Chi năm sinh mang tính khái quát văn hóa. Để có lá số cá nhân hóa chính xác theo ngày tháng năm giờ sinh, hãy sử dụng bộ lập lá số Tử Vi Đẩu Số 12 cung tại <a href="{{ route('tuvi.index') }}">/tu-vi</a>.
                </div>
                <div class="mayhen_tuvi_wiki_grid">
                    @foreach($zodiacsMeta as $z)
                        <article class="mayhen_tuvi_card" data-tuvi-wiki-item="{{ ($z['animal'] ?? '') . ' ' . ($z['branch'] ?? '') . ' ' . ($z['traits'] ?? '') }}">
                            <span class="mayhen_tuvi_badge">{{ $z['hanzi'] ?? '' }} • Hành {{ $z['element'] ?? '' }}</span>
                            <h3>{{ $z['animal'] ?? '' }}</h3>
                            <p style="margin-bottom: 6px;"><strong>Tính cách:</strong> {{ $z['traits'] ?? '' }}</p>
                            <p style="margin-bottom: 6px;"><strong>Tình duyên:</strong> {{ $z['love'] ?? '' }}</p>
                            <p style="font-size: 13px; color: #1E4D3B;"><strong>Gợi ý:</strong> {{ $z['advice'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mayhen_tuvi_layers_grid">
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Lớp 1 • Chu Kỳ 10 Năm</span>
                        <h3>Đại Hạn — Khí Hậu Của Cả Một Giai Đoạn</h3>
                        <p>Đại Hạn khởi từ số Ngũ Hành Cục (2, 3, 4, 5 hoặc 6 tuổi) và luân chuyển thuận hoặc nghịch tùy Âm Dương Nam Nữ. Đại Hạn giống như khí hậu của cả thập kỷ: định hình chủ đề lớn về sự nghiệp, hôn nhân và trưởng thành.</p>
                    </div>
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Lớp 2 • Chu Kỳ Từng Năm</span>
                        <h3>Tiểu Hạn &amp; Lưu Niên Năm {{ $currentYear }}</h3>
                        <p>Tiểu Hạn và Lưu Niên (vòng Lưu Thái Tuế, Lưu Lộc Tồn, Lưu Thiên Mã, Lưu Kình Đà...) giống như thời tiết từng năm trong khung khí hậu Đại Hạn. Một năm có nhiều sao động không có nghĩa là năm xấu, mà là năm cần chuẩn bị kỹ và chủ động nhịp tiến – lùi.</p>
                    </div>
                </div>
                <div class="mayhen_tuvi_card" style="text-align: center; background: #FAF5EB; border-color: #CFA860;">
                    <h3>Xem Đại Hạn, Tiểu Hạn &amp; Lưu Niên Năm {{ $currentYear }} Cho Riêng Bạn</h3>
                    <p style="margin-bottom: 16px;">Lập lá số Tử Vi Đẩu Số cá nhân để xem đúng cung Đại Hạn hiện tại, các sao lưu niên kích hoạt và kế hoạch hành động 30/90 ngày.</p>
                    <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_primary">✦ Lập Lá Số Năm {{ $currentYear }} Miễn Phí</a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
