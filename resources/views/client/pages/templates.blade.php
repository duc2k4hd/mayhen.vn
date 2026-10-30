@extends('client.layouts.master')

@section('title', '99+ Mẫu Thiệp Cưới Đẹp, Mẫu Thiệp Cưới Online Xu Hướng ' . date('Y') . ' | ' . config('app.name', 'Mây Hẹn'))
@section('meta_description', 'Khám phá 99+ mẫu thiệp cưới đẹp, mẫu thiệp cưới online mới nhất ' . date('Y') . ' tại ' . config('app.name', 'Mây Hẹn') . '. Thiết kế đa dạng: tối giản Minimalist, sang trọng tem sáp, hoa lá lãng mạn, tạp chí Editorial. Tự tạo & xem thử miễn phí trên điện thoại trong 2 phút.')
@section('meta_keywords', 'mẫu thiệp cưới, mẫu thiệp cưới đẹp, mẫu thiệp cưới online, mẫu thiệp cưới điện tử, kho mẫu thiệp cưới, thiết kế mẫu thiệp cưới, mẫu thiệp cưới sang trọng, mẫu thiệp cưới đơn giản, ' . mb_strtolower(config('app.name', 'Mây Hẹn')))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/templates.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@push('schema')
@php
    $itemListElements = [];
    foreach ($templates->take(12) as $index => $item) {
        $itemListElements[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item->name,
            'url' => route('templates.show', $item->slug),
            'image' => $item->image,
        ];
    }
@endphp
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/mau-thiep') . '/#breadcrumb',
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
                    'name' => 'Mẫu thiệp cưới',
                    'item' => url('/mau-thiep'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => '99+ Mẫu thiệp cưới đẹp & Mẫu thiệp cưới online',
                    'item' => url('/mau-thiep'),
                ],
            ],
        ],
        [
            '@type' => 'CollectionPage',
            '@id' => url('/mau-thiep') . '/#collection',
            'url' => url('/mau-thiep'),
            'name' => '99+ Mẫu Thiệp Cưới Đẹp, Mẫu Thiệp Cưới Online — ' . config('app.name', 'Mây Hẹn'),
            'description' => 'Kho sưu tập các mẫu thiệp cưới đẹp, mẫu thiệp cưới online đa phong cách từ tối giản đến hoàng gia sang trọng. Tích hợp mã QR mừng cưới và sơ đồ chỉ đường.',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => url('/') . '/#website',
                'name' => config('app.name', 'Mây Hẹn'),
                'url' => url('/'),
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $templates->count(),
                'itemListElement' => $itemListElements,
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/mau-thiep') . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Tôi có thể đổi ảnh cưới, đổi font chữ và màu sắc trên các mẫu thiệp cưới được không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Hoàn toàn được. Trình chỉnh sửa trực quan của Mây Hẹn cho phép bạn thay đổi toàn bộ ảnh đại diện, ảnh album cưới, điều chỉnh nội dung câu chữ, ngày giờ, địa điểm tổ chức và chọn phông chữ theo sở thích riêng một cách linh hoạt.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Một mẫu thiệp cưới online có thể dùng chung cho cả Nhà Trai và Nhà Gái không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Được. Hệ thống của Mây Hẹn hỗ trợ tính năng nhân bản thông minh: Bạn chỉ cần thiết lập thông tin một lần, sau đó có thể tạo ra 2 đường link riêng biệt (1 link Thành Hôn cho Nhà Trai và 1 link Vu Quy cho Nhà Gái) với ngày giờ và sảnh tiệc riêng để gửi đúng cho khách mời từng bên.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Mẫu thiệp cưới online hiển thị trên điện thoại iPhone và Android có mượt không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => '100% mẫu thiệp tại Mây Hẹn được thiết kế theo triết lý Mobile-First, tối ưu hóa dung lượng hình ảnh và code siêu nhẹ giúp trang tải dưới 1 giây trên mọi dòng điện thoại (iPhone, Samsung, Xiaomi...) mà không lo bị tràn viền hay lỗi font.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Mẫu thiệp cưới miễn phí (Gói Free) khác gì so với mẫu Plus và Pro?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Mẫu Free cho phép bạn tạo thiệp nhanh trong 2 phút với đầy đủ tính năng cốt lõi (ảnh cưới, thời gian, bản đồ GPS, mã QR mừng cưới). Các mẫu Plus và Pro bổ sung thêm hiệu ứng mở phong thư tem sáp hoàng gia, layout tạp chí Editorial, album ảnh không giới hạn và nhạc nền lãng mạn.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Khách mời có cần tải ứng dụng nào để mở xem mẫu thiệp cưới không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Không cần. Khách mời chỉ cần bấm vào đường link thiệp cưới bạn gửi qua Zalo, Messenger, SMS là có thể mở xem trực tiếp trên trình duyệt web mặc định của điện thoại mà không cần cài đặt bất kỳ ứng dụng nào.',
                    ],
                ],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
    <div class="mayhen_client_layout_container">

        {{-- Breadcrumb Navigation --}}
        <div class="mayhen_templates_breadcrumb_wrap">
            <ul class="mayhen_templates_breadcrumbs">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="mayhen_templates_breadcrumb_sep">/</li>
                <li><a href="{{ route('templates.index') }}">Mẫu thiệp cưới</a></li>
                @if (!empty($currentCategory) && $currentCategory !== 'all')
                    <li class="mayhen_templates_breadcrumb_sep">/</li>
                    <li class="mayhen_templates_breadcrumb_active">Phong cách: {{ ucfirst($currentCategory) }}</li>
                @else
                    <li class="mayhen_templates_breadcrumb_sep">/</li>
                    <li class="mayhen_templates_breadcrumb_active">Tất cả mẫu thiệp cưới</li>
                @endif
            </ul>
        </div>

        {{-- Header Hero (Quiet Luxury Split Layout & Clean SEO) --}}
        <header class="mayhen_templates_header">
            <div class="mayhen_templates_hero_split">
                {{-- Cột Trái: Tiêu đề, SEO Description & Feature Badges --}}
                <div class="mayhen_templates_hero_content">
                    <div class="mayhen_templates_kicker_wrap">
                        <span class="mayhen_templates_kicker_badge">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true">
                                <path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/>
                            </svg>
                            Bộ Sưu Tập Mẫu Thiệp Cưới Đẹp {{ date('Y') }}
                        </span>
                        <span class="mayhen_templates_kicker_sub">MÂY HẸN STUDIO</span>
                    </div>

                    <h1 class="mayhen_templates_title">Mẫu Thiệp Cưới Đẹp &amp; Thiệp Mời Online Cao Cấp</h1>

                    <p class="mayhen_templates_desc">
                        Tuyển chọn những <strong>mẫu thiệp cưới đẹp</strong> và <strong>thiệp cưới online</strong> cao cấp theo phong cách Quiet Luxury, Minimalist và Hoàng gia mở phong thư tem sáp. Trải nghiệm xem thử tương tác chân thực, dễ dàng tự tạo thiệp miễn phí và chia sẻ tức thì qua Zalo.
                    </p>

                    {{-- 4 Feature Highlights dạng Clean Badges có Icon --}}
                    <div class="mayhen_templates_features_grid" role="list" aria-label="Đặc quyền thiệp cưới Mây Hẹn">
                        <div class="mayhen_templates_feature_item" role="listitem">
                            <span class="mayhen_templates_feature_icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none" width="16" height="16" stroke="currentColor" stroke-width="1.5">
                                    <rect x="5" y="2" width="10" height="16" rx="2"/>
                                    <path d="M9 15h2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div class="mayhen_templates_feature_text">
                                <strong>Tự tạo miễn phí</strong>
                                <span>Setup 2 phút trên điện thoại</span>
                            </div>
                        </div>

                        <div class="mayhen_templates_feature_item" role="listitem">
                            <span class="mayhen_templates_feature_icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none" width="16" height="16" stroke="currentColor" stroke-width="1.5">
                                    <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4H15.5A1.5 1.5 0 0 1 17 5.5V14.5A1.5 1.5 0 0 1 15.5 16H4.5A1.5 1.5 0 0 1 3 14.5V5.5Z"/>
                                    <path d="M3.5 5.5L10 10.5L16.5 5.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div class="mayhen_templates_feature_text">
                                <strong>Mở phong thư tem sáp</strong>
                                <span>Tương tác chạm mở độc bản</span>
                            </div>
                        </div>

                        <div class="mayhen_templates_feature_item" role="listitem">
                            <span class="mayhen_templates_feature_icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none" width="16" height="16" stroke="currentColor" stroke-width="1.5">
                                    <path d="M10 10.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                    <path d="M10 2.5c-3.04 0-5.5 2.46-5.5 5.5 0 4.125 5.5 9.5 5.5 9.5s5.5-5.375 5.5-9.5c0-3.04-2.46-5.5-5.5-5.5Z"/>
                                </svg>
                            </span>
                            <div class="mayhen_templates_feature_text">
                                <strong>RSVP &amp; Bản đồ GPS</strong>
                                <span>Mã QR mừng cưới tiện lợi</span>
                            </div>
                        </div>

                        <div class="mayhen_templates_feature_item" role="listitem">
                            <span class="mayhen_templates_feature_icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none" width="16" height="16" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9 14.5V5.5l7-2v9"/>
                                    <circle cx="6.5" cy="14.5" r="2.5"/>
                                    <circle cx="16" cy="12.5" r="2.5"/>
                                </svg>
                            </span>
                            <div class="mayhen_templates_feature_text">
                                <strong>Tự phát nhạc cưới</strong>
                                <span>20+ giai điệu lãng mạn nhẹ nhàng</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột Phải: Visual Showcase Card & Stickers & Mockup Nghệ Thuật --}}
                <div class="mayhen_templates_hero_visual" aria-hidden="true">
                    <div class="mayhen_templates_showcase_stage">
                        {{-- Ánh sáng lan tỏa nhẹ phía sau --}}
                        <div class="mayhen_templates_showcase_glow"></div>

                        {{-- Thẻ thiệp mẫu chính (Invitation Art Card) --}}
                        <div class="mayhen_templates_art_card">
                            <div class="mayhen_templates_art_card_inner">
                                <img src="{{ route('templates.asset', ['slug' => 'thuoc-phim-tinh-yeu', 'path' => 'feecd763-f272-424b-9985-b4f3d68d7fb1.jpg']) }}" 
                                     alt="Mẫu thiệp cưới đẹp Mây Hẹn" 
                                     class="mayhen_templates_art_photo" 
                                     loading="eager"
                                     fetchpriority="high"
                                     decoding="async">
                                <div class="mayhen_templates_art_overlay">
                                    <span class="mayhen_templates_art_tagline">WEDDING INVITATION</span>
                                    <span class="mayhen_templates_art_couple">Minh Triết &amp; Thảo Linh</span>
                                    <div class="mayhen_templates_art_divider"></div>
                                    <span class="mayhen_templates_art_date">Save The Date · {{ date('Y') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- STICKER 1: Con dấu sáp hoàng gia 3D nổi (Wax Seal Badge) --}}
                        <div class="mayhen_templates_sticker_wax">
                            <div class="mayhen_templates_wax_seal">
                                <div class="mayhen_templates_wax_rim"></div>
                                <div class="mayhen_templates_wax_inner">
                                    <span class="mayhen_templates_wax_monogram">MH</span>
                                    <span class="mayhen_templates_wax_sub">SEAL</span>
                                </div>
                            </div>
                        </div>

                        {{-- STICKER 2: Floating Pill "✨ Chạm Mở Tương Tác" --}}
                        <div class="mayhen_templates_floating_pill mayhen_templates_pill_touch">
                            <span class="mayhen_templates_pill_sparkle">✨</span>
                            <span class="mayhen_templates_pill_label">Chạm mở tương tác</span>
                        </div>

                        {{-- STICKER 3: Floating Pill "🎵 Tự động phát nhạc" --}}
                        <div class="mayhen_templates_floating_pill mayhen_templates_pill_music">
                            <span class="mayhen_templates_audio_eq">
                                <span class="mayhen_templates_eq_bar"></span>
                                <span class="mayhen_templates_eq_bar"></span>
                                <span class="mayhen_templates_eq_bar"></span>
                            </span>
                            <span class="mayhen_templates_pill_label">Tự động phát nhạc</span>
                        </div>

                        {{-- STICKER 4: Sticker góc nhãn Quiet Luxury --}}
                        <div class="mayhen_templates_ribbon_badge">
                            <span>Quiet Luxury</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Category Filter Bar --}}
        <nav class="mayhen_templates_filter_bar" aria-label="Bộ lọc mẫu thiệp theo phong cách">
            <a href="{{ route('templates.index', array_filter(['tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ empty($currentCategory) || $currentCategory === 'all' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tất Cả Phong Cách
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'minimal', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'minimal' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tối Giản
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'floral', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'floral' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Hoa Lá
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'traditional', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'traditional' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Truyền Thống
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'luxury', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'luxury' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Sang Trọng Tem Sáp
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'editorial', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'editorial' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tạp Chí Editorial
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'modern', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'modern' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Hiện Đại
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'creative', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'creative' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Độc Đáo (Passport/Lá Thư)
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'cute', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'cute' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Dễ Thương (Chibi)
            </a>
        </nav>

        {{-- Tier Filter Bar --}}
        <div class="mayhen_templates_tier_bar" id="mayhen_templates_tier_bar" aria-label="Lọc theo gói dịch vụ">
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'sort' => $currentSort])) }}#mayhen_templates_tier_bar" class="mayhen_templates_tier_pill {{ empty($currentTier) ? 'mayhen_templates_tier_pill_active' : '' }}">
                Tất cả gói
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => 'free', 'sort' => $currentSort])) }}#mayhen_templates_tier_bar" class="mayhen_templates_tier_pill {{ $currentTier === 'free' ? 'mayhen_templates_tier_pill_active' : '' }}">
                Gói Miễn Phí (Setup 2 phút)
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => 'plus', 'sort' => $currentSort])) }}#mayhen_templates_tier_bar" class="mayhen_templates_tier_pill {{ $currentTier === 'plus' ? 'mayhen_templates_tier_pill_active' : '' }}">
                Gói Plus (Hoa lá & Album ảnh)
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => 'pro', 'sort' => $currentSort])) }}#mayhen_templates_tier_bar" class="mayhen_templates_tier_pill {{ $currentTier === 'pro' ? 'mayhen_templates_tier_pill_active' : '' }}">
                Gói Pro VIP (Tem sáp & Tạp chí)
            </a>
        </div>

        {{-- Meta Bar --}}
        <div class="mayhen_templates_meta_bar">
            <span class="mayhen_templates_meta_count">
                <span class="mayhen_templates_meta_count_desktop">Hiển thị <strong>{{ $templates->count() }}</strong> / {{ $totalCount }} mẫu thiệp cưới đang sẵn sàng</span>
                <span class="mayhen_templates_meta_count_mobile"><strong>{{ $templates->count() }}</strong> / {{ $totalCount }} mẫu thiệp</span>
            </span>
            <div class="mayhen_templates_sort_group">
                <span class="mayhen_templates_sort_label">Sắp xếp:</span>
                <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => $currentTier, 'sort' => 'featured'])) }}" class="mayhen_templates_sort_link {{ empty($currentSort) || $currentSort === 'featured' ? 'mayhen_templates_sort_link_active' : '' }}">
                    Nổi bật
                </a>
                <span class="mayhen_templates_sort_dot">&bull;</span>
                <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => $currentTier, 'sort' => 'popular'])) }}" class="mayhen_templates_sort_link {{ $currentSort === 'popular' ? 'mayhen_templates_sort_link_active' : '' }}">
                    Chuộng nhất
                </a>
            </div>
        </div>

        {{-- Templates Grid --}}
        <div class="mayhen_templates_grid">
            @forelse ($templates as $tpl)
                @php
                    $fullPageImg = $tpl->full_page_preview ?? $tpl->image;
                    $initialPanY = ($tpl->slug === 'luu-but-ngay-chung-doi') ? '-9370px' : '-500px';
                    $initialPanDur = ($tpl->slug === 'luu-but-ngay-chung-doi') ? '55s' : '18s';
                @endphp
                <article class="mayhen_home_template_card"
                         data-slug="{{ $tpl->slug }}"
                         data-name="{{ $tpl->name }}"
                         data-category="{{ $tpl->category_label }}"
                         data-preview-url="{{ route('templates.preview', $tpl->slug) }}"
                         data-create-url="{{ route('templates.show', $tpl->slug) }}">
                    <div class="mayhen_home_template_card_img_wrap" data-mayhen-showcase-peek-btn style="cursor: pointer;" title="Bấm để xem thử trực tiếp mẫu thiệp {{ $tpl->name }}">
                        <div class="mayhen_home_showcase_pan_wrap hover-pan" data-mayhen-pan-wrap style="--pan-y: {{ $initialPanY }}; --pan-dur: {{ $initialPanDur }};">
                            <img src="{{ $fullPageImg }}"
                                 alt="Mẫu thiệp cưới đẹp {{ $tpl->name }}"
                                 class="mayhen_home_showcase_pan_img"
                                 loading="lazy"
                                 decoding="async">
                        </div>
                        <div class="mayhen_home_template_card_badge_group">
                            <span class="mayhen_home_template_card_tier_badge {{ $tpl->tier_badge_class }}">
                                {{ $tpl->tier_label }}
                            </span>
                            <span class="mayhen_home_template_card_setup_badge">
                                {{ $tpl->complexity_label }}
                            </span>
                        </div>
                    </div>
                    <div class="mayhen_home_template_card_body">
                        <span class="mayhen_home_template_card_category">{{ $tpl->category_label }}</span>
                        <h2 class="mayhen_home_template_card_name">{{ $tpl->name }}</h2>
                        <p class="mayhen_home_template_card_desc">{{ $tpl->description }}</p>

                        <div class="mayhen_home_template_card_stats">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>{{ number_format($tpl->usage_count) }} cặp đôi</span>
                        </div>

                        <div class="mayhen_home_template_card_footer">
                            <button type="button"
                                    class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_sm"
                                    data-mayhen-showcase-peek-btn
                                    title="Xem trải nghiệm thực tế mẫu thiệp cưới {{ $tpl->name }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Xem Mẫu</span>
                            </button>
                            <a href="{{ route('templates.show', $tpl->slug) }}"
                               class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_sm"
                               title="Sử dụng mẫu thiệp cưới {{ $tpl->name }}">
                                <span>Dùng Mẫu</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem;">
                    <p style="color: var(--mayhen-color-text-muted); margin-bottom: 1rem;">Không tìm thấy mẫu thiệp phù hợp trong tiêu chí lọc này.</p>
                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_secondary">Xem tất cả các mẫu</a>
                </div>
            @endforelse
        </div>

        {{-- SEO Content Hub --}}
        <section class="mayhen_templates_seo_section">

            {{-- Block 1: 6 Phong cách mẫu thiệp cưới đẹp --}}
            <div class="mayhen_templates_seo_block">
                <div class="mayhen_templates_seo_header">
                    <span class="mayhen_templates_seo_kicker">Xu Hướng Thiết Kế</span>
                    <h2 class="mayhen_templates_seo_title">Bảng Tổng Hợp 6 Phong Cách Mẫu Thiệp Cưới Đẹp Thịnh Hành Nhất Năm {{ date('Y') }}</h2>
                    <p class="mayhen_templates_seo_subtitle">
                        Mỗi mẫu thiệp cưới mang một ngôn ngữ cảm xúc riêng biệt. Hãy chọn phong cách đại diện trọn vẹn nhất cho câu chuyện tình yêu của hai bạn.
                    </p>
                </div>

                <div class="mayhen_templates_styles_grid">
                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Phong Cách 01</span>
                        <h3 class="mayhen_templates_style_name">Mẫu Thiệp Cưới Tối Giản (Minimalist)</h3>
                        <p class="mayhen_templates_style_desc">
                            Tôn vinh vẻ đẹp nguyên bản với nghệ thuật khoảng trắng, phông chữ serif thanh tao và gam màu kem ngà, be nhạt. Phù hợp tuyệt đối cho những cặp đôi yêu chuộng sự thanh lịch, tinh tế.
                        </p>
                        <ul class="mayhen_templates_style_features">
                            <li>Thiết kế thoáng đãng, tập trung vào thông điệp</li>
                            <li>Tốc độ tải trang siêu nhanh dưới 1 giây</li>
                            <li>Thời gian nhập liệu chỉ mất 2 phút</li>
                        </ul>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Phong Cách 02</span>
                        <h3 class="mayhen_templates_style_name">Mẫu Thiệp Cưới Hoàng Gia Tem Sáp (Wax Seal)</h3>
                        <p class="mayhen_templates_style_desc">
                            Tái hiện sống động cảm giác mở phong bì thiệp giấy truyền thống. Khi khách chạm tay vào màn hình, con dấu sáp vàng ánh kim tan nhẹ để hé lộ thiệp mời bên trong đầy trang trọng.
                        </p>
                        <ul class="mayhen_templates_style_features">
                            <li>Hiệu ứng mở phong thư tương tác độc quyền</li>
                            <li>Dấu tem sáp dập chữ lồng Monogram riêng</li>
                            <li>Mang lại cảm giác nhận thiệp tận tay cao cấp</li>
                        </ul>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Phong Cách 03</span>
                        <h3 class="mayhen_templates_style_name">Mẫu Thiệp Cưới Tạp Chí Editorial (Vogue)</h3>
                        <p class="mayhen_templates_style_desc">
                            Lấy cảm hứng từ các trang bìa tạp chí thời trang quốc tế, bố cục hình khối phá cách giúp biến bộ ảnh cưới ngoại cảnh của hai bạn thành một tác phẩm nghệ thuật nhiếp ảnh cuốn hút.
                        </p>
                        <ul class="mayhen_templates_style_features">
                            <li>Typography lớn ấn tượng, hiện đại</li>
                            <li>Trình diễn trọn vẹn câu chuyện tình yêu (Love Story)</li>
                            <li>Album ảnh cưới chuyển động đa tầng điện ảnh</li>
                        </ul>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Phong Cách 04</span>
                        <h3 class="mayhen_templates_style_name">Mẫu Thiệp Cưới Hoa Lá Lãng Mạn (Floral)</h3>
                        <p class="mayhen_templates_style_desc">
                            Họa tiết cành lá sage xanh mướt, hoa mẫu đơn và hoa hồng khô vẽ màu nước mềm mại. Lựa chọn hoàn hảo cho tiệc cưới ngoài trời, tiệc bãi biển hoặc hôn lễ trong không gian sân vườn.
                        </p>
                        <ul class="mayhen_templates_style_features">
                            <li>Gam màu pastel tươi mát, ngập tràn sức sống</li>
                            <li>Họa tiết hoa cọ vẽ tay nghệ thuật độc bản</li>
                            <li>Hòa hợp với không gian tiệc Rustic & Garden</li>
                        </ul>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Phong Cách 05</span>
                        <h3 class="mayhen_templates_style_name">Mẫu Thiệp Cưới Á Đông Truyền Thống</h3>
                        <p class="mayhen_templates_style_desc">
                            Sự giao thoa hoàn mỹ giữa nét văn hóa cưới hỏi Việt Nam và hơi thở đương đại. Sắc đỏ rượu vang quý phái, họa tiết song hỷ mạ vàng kết hợp cùng lời chúc phúc thiêng liêng.
                        </p>
                        <ul class="mayhen_templates_style_features">
                            <li>Phù hợp hoàn hảo cho Lễ Vu Quy & Thành Hôn</li>
                            <li>Được người lớn tuổi và họ hàng hai bên đánh giá cao</li>
                            <li>Giữ trọn đạo lý và tôn ti lễ giáo truyền thống</li>
                        </ul>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Phong Cách 06</span>
                        <h3 class="mayhen_templates_style_name">Mẫu Thiệp Cưới Độc Đáo Passport & Ticket</h3>
                        <p class="mayhen_templates_style_desc">
                            Thiết kế mô phỏng cuốn Hộ chiếu tình yêu (Wedding Passport) hoặc Tấm vé máy bay đưa lối (Boarding Pass). Ý tưởng sáng tạo đầy hứng khởi cho các đám cưới phong cách du lịch.
                        </p>
                        <ul class="mayhen_templates_style_features">
                            <li>Thiết kế độc lạ, tạo ấn tượng khó phai với khách</li>
                            <li>Tích hợp mã vạch, dấu thị thực tình yêu độc quyền</li>
                            <li>Tuyệt vời cho tiệc cưới tại Đà Lạt, Phú Quốc, Nha Trang</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Block 2: So sánh Mẫu thiệp cưới online vs Thiệp giấy --}}
            <div class="mayhen_templates_seo_block">
                <div class="mayhen_templates_seo_header">
                    <span class="mayhen_templates_seo_kicker">Phân Tích Giá Trị</span>
                    <h2 class="mayhen_templates_seo_title">So Sánh Ưu Điểm: Mẫu Thiệp Cưới Online So Với Thiệp Giấy Truyền Thống</h2>
                    <p class="mayhen_templates_seo_subtitle">
                        Hiểu rõ những lợi ích vượt trội giúp bạn tối ưu chi phí và nâng tầm trải nghiệm của khách mời trong ngày trọng đại.
                    </p>
                </div>

                <div class="mayhen_templates_compare_table_wrap">
                    <table class="mayhen_templates_compare_table">
                        <thead>
                            <tr>
                                <th>Tiêu Chí So Sánh</th>
                                <th>Mẫu Thiệp Cưới Online ({{ config('app.name', 'Mây Hẹn') }})</th>
                                <th>Thiệp Cưới Giấy Truyền Thống</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Chi phí thực hiện</strong></td>
                                <td>
                                    <span class="mayhen_templates_compare_badge_best">Tiết kiệm 85%</span><br>
                                    Chỉ từ 0đ đến vài trăm nghìn đồng cho số lượng khách không giới hạn.
                                </td>
                                <td>Tốn từ 5.000đ – 45.000đ/thiệp. Tổng chi phí từ 3 – 15 triệu đồng tùy loại giấy.</td>
                            </tr>
                            <tr>
                                <td><strong>Thời gian chuẩn bị & gửi</strong></td>
                                <td>
                                    <span class="mayhen_templates_compare_badge_best">Siêu tốc 2 phút</span><br>
                                    Tạo thiệp trên điện thoại, gửi ngay qua Zalo/Facebook chỉ bằng 1 chạm.
                                </td>
                                <td>Mất từ 10 – 20 ngày in ấn, đóng gói và đi phát thiệp từng nhà.</td>
                            </tr>
                            <tr>
                                <td><strong>Khả năng sửa thông tin</strong></td>
                                <td>
                                    <span class="mayhen_templates_compare_badge_best">Cập nhật tức thì</span><br>
                                    Đổi giờ giấc, sảnh tiệc trong trang quản lý là link thiệp tự cập nhật ngay.
                                </td>
                                <td>Nếu in sai ngày giờ hoặc địa điểm thì phải bỏ toàn bộ và in lại từ đầu.</td>
                            </tr>
                            <tr>
                                <td><strong>Tính năng tương tác thông minh</strong></td>
                                <td>
                                    <span class="mayhen_templates_compare_badge_best">Đa tính năng</span><br>
                                    Bản đồ GPS chỉ đường, quét mã QR mừng cưới VietQR, form xác nhận RSVP.
                                </td>
                                <td>Khách mời chỉ đọc được thông tin chữ in tĩnh, dễ lạc đường khi tìm sảnh tiệc.</td>
                            </tr>
                            <tr>
                                <td><strong>Khách mời ở xa & bạn bè</strong></td>
                                <td>
                                    <span class="mayhen_templates_compare_badge_best">Tiếp cận 100%</span><br>
                                    Gửi thiệp cho bạn bè ở nước ngoài, khác tỉnh thành ngay lập tức không tốn cước.
                                </td>
                                <td>Gửi bưu điện mất nhiều ngày, dễ thất lạc phong bì hoặc nhăn nheo khi vận chuyển.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Block 3: Kinh nghiệm chọn mẫu thiệp cưới chuẩn đẹp --}}
            <div class="mayhen_templates_seo_block">
                <div class="mayhen_templates_seo_header">
                    <span class="mayhen_templates_seo_kicker">Lời Khuyên Chuyên Gia</span>
                    <h2 class="mayhen_templates_seo_title">Kinh Nghiệm Chọn Mẫu Thiệp Cưới Chuẩn Đẹp Phù Hợp Từng Không Gian Tiệc</h2>
                    <p class="mayhen_templates_seo_subtitle">
                        Tấm thiệp cưới là lời mở đầu cho ngày vui. Chọn đúng tông màu và phong cách sẽ tạo nên sự đồng điệu tuyệt hảo với không gian sảnh tiệc.
                    </p>
                </div>

                <div class="mayhen_templates_styles_grid">
                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Không Gian 01</span>
                        <h3 class="mayhen_templates_style_name">Tiệc Khách Sạn 5 Sao & Trung Tâm Hội Nghị</h3>
                        <p class="mayhen_templates_style_desc">
                            Với không gian lộng lẫy dưới ánh đèn chùm pha lê, bạn nên ưu tiên các <strong>mẫu thiệp cưới sang trọng</strong> có phong thư mở tem sáp hoàng gia, đường viền ép kim vàng cát hoặc thiết kế Monogram lồng chữ tinh xảo.
                        </p>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Không Gian 02</span>
                        <h3 class="mayhen_templates_style_name">Tiệc Cưới Ngoài Trời, Sân Vườn & Bãi Biển</h3>
                        <p class="mayhen_templates_style_desc">
                            Hôn lễ hòa mình vào thiên nhiên sẽ cực kỳ phù hợp với <strong>mẫu thiệp cưới hoa lá (Floral)</strong>, phong cách Rustic mộc mạc hoặc thiết kế Passport du lịch, tạo cảm giác thư thái, thân mật cho khách mời.
                        </p>
                    </div>

                    <div class="mayhen_templates_style_card">
                        <span class="mayhen_templates_style_tag">Không Gian 03</span>
                        <h3 class="mayhen_templates_style_name">Tiệc Cưới Thân Mật (Intimate Wedding)</h3>
                        <p class="mayhen_templates_style_desc">
                            Nếu quy mô tiệc chỉ từ 30 – 80 khách thân thiết nhất, các <strong>mẫu thiệp cưới tối giản Minimalist</strong> hoặc phong cách tạp chí Editorial sẽ giúp truyền tải chân thành từng câu chuyện tình yêu mà không hề cầu kỳ.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Block 4: 3 bước tự tạo thiệp cưới online --}}
            <div class="mayhen_templates_seo_block">
                <div class="mayhen_templates_seo_header">
                    <span class="mayhen_templates_seo_kicker">Dễ Dàng Thực Hiện</span>
                    <h2 class="mayhen_templates_seo_title">3 Bước Đơn Giản Tự Tạo & Sử Dụng Mẫu Thiệp Cưới Trên Điện Thoại</h2>
                    <p class="mayhen_templates_seo_subtitle">
                        Không cần biết thiết kế hay cài đặt phần mềm phức tạp, bạn hoàn toàn có thể tự tay tạo tấm thiệp cưới như ý chỉ trong 2 phút.
                    </p>
                </div>

                <div class="mayhen_templates_steps_grid">
                    <div class="mayhen_templates_step_card">
                        <div class="mayhen_templates_step_num">01</div>
                        <h3 class="mayhen_templates_step_title">Chọn Mẫu Thiệp Ưng Ý</h3>
                        <p class="mayhen_templates_step_desc">
                            Lướt bộ sưu tập hơn 99+ mẫu thiệp cưới bên trên, bấm <em>"Xem Live"</em> để xem trước hiệu ứng thực tế trên điện thoại rồi nhấn <em>"Dùng Mẫu"</em>.
                        </p>
                    </div>

                    <div class="mayhen_templates_step_card">
                        <div class="mayhen_templates_step_num">02</div>
                        <h3 class="mayhen_templates_step_title">Nhập Thông Tin & Tải Ảnh Cưới</h3>
                        <p class="mayhen_templates_step_desc">
                            Điền họ tên cô dâu chú rể, ngày giờ tổ chức, ghim bản đồ Google Maps sảnh tiệc và nhúng mã QR tài khoản ngân hàng mừng cưới VietQR an toàn.
                        </p>
                    </div>

                    <div class="mayhen_templates_step_card">
                        <div class="mayhen_templates_step_num">03</div>
                        <h3 class="mayhen_templates_step_title">Nhận Link & Gửi Cho Khách</h3>
                        <p class="mayhen_templates_step_desc">
                            Bấm Lưu thiệp để nhận ngay đường link thiệp cưới điện tử riêng biệt mang tên hai bạn, sẵn sàng gửi qua Zalo, Messenger kèm thiệp mời ảnh đại diện đẹp mắt.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Block 5: FAQ thường gặp về mẫu thiệp cưới --}}
            <div class="mayhen_templates_seo_block">
                <div class="mayhen_templates_seo_header">
                    <span class="mayhen_templates_seo_kicker">Giải Đáp Nhanh</span>
                    <h2 class="mayhen_templates_seo_title">Câu Hỏi Thường Gặp Khi Chọn & Sử Dụng Mẫu Thiệp Cưới</h2>
                    <p class="mayhen_templates_seo_subtitle">
                        Những băn khoăn phổ biến nhất của các cặp đôi khi trải nghiệm kho mẫu thiệp cưới tại {{ config('app.name', 'Mây Hẹn') }}.
                    </p>
                </div>

                <div class="mayhen_templates_faq_list">
                    <div class="mayhen_templates_faq_card">
                        <h3 class="mayhen_templates_faq_q">1. Tôi có thể đổi ảnh cưới, đổi font chữ và màu sắc trên các mẫu thiệp cưới được không?</h3>
                        <p class="mayhen_templates_faq_a">
                            Hoàn toàn được. Trình chỉnh sửa trực quan của {{ config('app.name', 'Mây Hẹn') }} cho phép bạn thay đổi toàn bộ ảnh đại diện, ảnh album cưới, điều chỉnh nội dung câu chữ, ngày giờ, địa điểm tổ chức và chọn phông chữ theo sở thích riêng một cách linh hoạt.
                        </p>
                    </div>

                    <div class="mayhen_templates_faq_card">
                        <h3 class="mayhen_templates_faq_q">2. Một mẫu thiệp cưới online có thể dùng chung cho cả Nhà Trai và Nhà Gái không?</h3>
                        <p class="mayhen_templates_faq_a">
                            Được. Hệ thống của chúng tôi hỗ trợ tính năng nhân bản thông minh: Bạn chỉ cần thiết lập thông tin một lần, sau đó có thể tạo ra 2 đường link riêng biệt (1 link Thành Hôn cho Nhà Trai và 1 link Vu Quy cho Nhà Gái) với ngày giờ và sảnh tiệc riêng để gửi đúng cho khách mời từng bên mà không bị nhầm lẫn.
                        </p>
                    </div>

                    <div class="mayhen_templates_faq_card">
                        <h3 class="mayhen_templates_faq_q">3. Mẫu thiệp cưới online hiển thị trên điện thoại iPhone và Android có mượt không?</h3>
                        <p class="mayhen_templates_faq_a">
                            100% mẫu thiệp tại {{ config('app.name', 'Mây Hẹn') }} được thiết kế theo triết lý Mobile-First, tối ưu hóa dung lượng hình ảnh và code siêu nhẹ giúp trang tải dưới 1 giây trên mọi dòng điện thoại (iPhone, Samsung, Xiaomi...) mà không lo bị tràn viền hay lỗi font.
                        </p>
                    </div>

                    <div class="mayhen_templates_faq_card">
                        <h3 class="mayhen_templates_faq_q">4. Mẫu thiệp cưới miễn phí (Gói Free) khác gì so với mẫu Plus và Pro?</h3>
                        <p class="mayhen_templates_faq_a">
                            Mẫu Free cho phép bạn tạo thiệp nhanh trong 2 phút với đầy đủ tính năng cốt lõi (ảnh cưới, thời gian, bản đồ GPS, mã QR mừng cưới). Các mẫu Plus và Pro bổ sung thêm hiệu ứng mở phong thư tem sáp hoàng gia, layout tạp chí Editorial, album ảnh không giới hạn và nhạc nền lãng mạn.
                        </p>
                    </div>

                    <div class="mayhen_templates_faq_card">
                        <h3 class="mayhen_templates_faq_q">5. Khách mời có cần tải ứng dụng nào để mở xem mẫu thiệp cưới không?</h3>
                        <p class="mayhen_templates_faq_a">
                            Không cần. Khách mời chỉ cần bấm vào đường link thiệp cưới bạn gửi qua Zalo, Messenger, SMS là có thể mở xem trực tiếp trên trình duyệt web mặc định của điện thoại mà không cần cài đặt bất kỳ ứng dụng nào.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Block 6: Bottom CTA --}}
            <div class="mayhen_templates_cta_box">
                <div class="mayhen_templates_cta_glow" aria-hidden="true"></div>
                <div class="mayhen_templates_cta_inner">
                    <div class="mayhen_templates_cta_badge_wrap">
                        <span class="mayhen_templates_cta_badge">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true">
                                <path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/>
                            </svg>
                            Khởi Tạo Tác Phẩm Độc Bản
                        </span>
                        <span class="mayhen_templates_cta_sub">MÂY HẸN • QUIET LUXURY</span>
                    </div>

                    <h2 class="mayhen_templates_cta_title">Chọn Mẫu Thiệp Cưới Yêu Thích &amp; Tự Tạo Trong 2 Phút</h2>
                    <p class="mayhen_templates_cta_desc">
                        Trải nghiệm tự thiết kế thiệp cưới online cao cấp hoàn toàn miễn phí. Dễ dàng đổi ảnh, thay đổi nội dung câu chữ và xem trước sống động ngay trên điện thoại của hai bạn.
                    </p>

                    {{-- 3 Cam kết Quiet Luxury --}}
                    <div class="mayhen_templates_cta_perks" role="list">
                        <div class="mayhen_templates_cta_perk_item" role="listitem">
                            <span class="mayhen_templates_cta_perk_icon">⚡</span>
                            <span class="mayhen_templates_cta_perk_label">Setup siêu nhanh 2 phút</span>
                        </div>
                        <div class="mayhen_templates_cta_perk_item" role="listitem">
                            <span class="mayhen_templates_cta_perk_icon">📱</span>
                            <span class="mayhen_templates_cta_perk_label">Xem live trên điện thoại</span>
                        </div>
                        <div class="mayhen_templates_cta_perk_item" role="listitem">
                            <span class="mayhen_templates_cta_perk_icon">✨</span>
                            <span class="mayhen_templates_cta_perk_label">Miễn phí tạo &amp; xem thử</span>
                        </div>
                    </div>

                    <div class="mayhen_templates_cta_actions">
                        <a href="{{ route('invitation.create') }}" class="mayhen_templates_cta_btn_primary">
                            <span>Bắt Đầu Tạo Thiệp Miễn Phí</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('pricing') }}" class="mayhen_templates_cta_btn_secondary">
                            <span>Xem Bảng Giá Gói Dịch Vụ</span>
                        </a>
                    </div>

                    <p class="mayhen_templates_cta_note">
                        ✨ Không yêu cầu thẻ tín dụng &bull; Chỉ thanh toán khi hai bạn hoàn toàn ưng ý
                    </p>
                </div>
            </div>

        </section>

        {{-- Luxury Live Preview Modal in Smartphone Frame --}}
        @include('client.components.showcase-preview-modal')

    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/templates.js') }}"></script>
@endpush

