@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('event_invitations.category', $category['slug']);
    $layoutVariant = $seoProfile['layout_variant'] ?? 'festive-imperial';
    $sectionOrder = $seoProfile['section_order'] ?? ['pillars', 'special', 'wording', 'editorial', 'steps', 'faq'];
    $faqs = ! empty($seoProfile['faqs']) ? $seoProfile['faqs'] : ($category['faqs'] ?? []);
    $hasActiveFilter = ! empty($filters['q']) || ! empty($filters['mode']) || ! empty($filters['envelope_color']);

    $itemListElements = [];
    $pos = 1;
    foreach ($templates as $tplItem) {
        $itemListElements[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $tplItem['name'],
            'url' => route('event_invitations.preview', $tplItem['slug']),
        ];
    }
@endphp

@section('title', $category['seo_title'])
@section('meta_description', $category['seo_description'])
@section('meta_keywords', $category['seo_keywords'])
@section('canonical', $canonicalUrl)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/event-invitations.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/event-invitations.js') }}"></script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            '@id' => $canonicalUrl . '#collection',
            'url' => $canonicalUrl,
            'name' => $category['seo_title'],
            'description' => $category['seo_description'],
            'inLanguage' => 'vi-VN',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $brandName,
                'url' => route('home'),
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'name' => 'Bộ sưu tập ' . $category['name'],
                'numberOfItems' => count($itemListElements),
                'itemListElement' => $itemListElements,
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonicalUrl . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Thiệp Sự Kiện & Lễ Hội',
                    'item' => route('event_invitations.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $category['name'],
                    'item' => $canonicalUrl,
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $canonicalUrl . '#faq',
            'mainEntity' => array_map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ], $faqs),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_event_hub_wrapper mayhen_event_cat_page mayhen_event_cat_layout--{{ $layoutVariant }}">
    <div class="mayhen_client_layout_container">

        {{-- 1. Breadcrumb chuẩn SEO trùng khớp BreadcrumbList JSON-LD --}}
        <nav class="mayhen_event_cat_breadcrumb" aria-label="Đường dẫn trang">
            <ol class="mayhen_event_cat_breadcrumb_list">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li aria-hidden="true" class="mayhen_event_cat_breadcrumb_sep">/</li>
                <li><a href="{{ route('event_invitations.index') }}">Thiệp Sự Kiện &amp; Lễ Hội</a></li>
                <li aria-hidden="true" class="mayhen_event_cat_breadcrumb_sep">/</li>
                <li aria-current="page">{{ $category['short_name'] }}</li>
            </ol>
        </nav>

        {{-- 2. Hero riêng biệt theo từng danh mục --}}
        <header class="mayhen_event_cat_hero mayhen_event_cat_hero--{{ $layoutVariant }}">
            <div class="mayhen_event_cat_hero_top">
                <span class="mayhen_event_cat_hero_badge">
                    <span aria-hidden="true">{{ $category['icon_symbol'] }}</span>
                    {{ $seoProfile['hero_kicker'] ?? $category['season_badge'] }}
                </span>
                <span class="mayhen_event_cat_hero_count">
                    {{ $totalCategoryTemplatesCount }} mẫu thuần Việt
                </span>
            </div>

            <h1 class="mayhen_event_cat_hero_title">{{ $category['name'] }}</h1>
            <p class="mayhen_event_cat_hero_desc">{{ $category['hero_subtitle'] }}</p>

            @if(!empty($seoProfile['quick_highlights']))
                <ul class="mayhen_event_cat_highlights">
                    @foreach($seoProfile['quick_highlights'] as $highlight)
                        <li class="mayhen_event_cat_highlight_item">
                            <span class="mayhen_event_cat_highlight_dot" aria-hidden="true">✦</span>
                            <span>{{ $highlight }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <nav class="mayhen_event_cat_jump_nav" aria-label="Mục lục nhanh trang danh mục">
                <a href="#kho-mau" class="mayhen_event_cat_jump_pill is-primary">Xem {{ count($templates) }} mẫu thiệp</a>
                <a href="#loi-van-mau" class="mayhen_event_cat_jump_pill">Mẫu lời mời hay</a>
                <a href="#cam-nang-seo" class="mayhen_event_cat_jump_pill">Kinh nghiệm &amp; Bố cục</a>
                <a href="#cau-hoi-thuong-gap" class="mayhen_event_cat_jump_pill">Câu hỏi thường gặp</a>
            </nav>
        </header>

        {{-- 3. Thanh chuyển nhanh 10 danh mục (Internal Linking mạnh mẽ) --}}
        <nav class="mayhen_event_cat_switcher" aria-label="Chuyển nhanh danh mục thiệp sự kiện">
            @foreach($categories as $cat)
                <a href="{{ route('event_invitations.category', $cat['slug']) }}"
                   class="mayhen_event_cat_switch_chip {{ $category['slug'] === $cat['slug'] ? 'is-active' : '' }}">
                    <span class="mayhen_event_cat_switch_icon" aria-hidden="true">{{ $cat['icon_symbol'] }}</span>
                    <span class="mayhen_event_cat_switch_label">{{ $cat['short_name'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- 4. Bộ lọc nhanh dễ sử dụng ngay trong danh mục --}}
        <section id="kho-mau" class="mayhen_event_cat_catalog_section" aria-labelledby="mayhenCatCatalogHeading">
            <div class="mayhen_event_cat_catalog_head">
                <div>
                    <h2 id="mayhenCatCatalogHeading" class="mayhen_event_cat_section_title">
                        Bộ sưu tập {{ $category['short_name'] }} mới nhất
                    </h2>
                    <p class="mayhen_event_cat_section_sub">
                        Hiển thị {{ count($templates) }} mẫu thiết kế một trang kèm phong bì mở nắp 3D &mdash; thử đổi màu và sửa lời mời miễn phí 100%.
                    </p>
                </div>

                <form method="GET" action="{{ route('event_invitations.category', $category['slug']) }}" class="mayhen_event_cat_filter_bar" role="search">
                    <div class="mayhen_event_cat_filter_search">
                        <input
                            type="search"
                            name="q"
                            value="{{ $filters['q'] ?? '' }}"
                            placeholder="Tìm tên mẫu {{ mb_strtolower($category['short_name']) }}..."
                            aria-label="Tìm kiếm mẫu thiệp trong danh mục"
                            class="mayhen_event_cat_filter_input"
                        >
                    </div>

                    <select name="mode" class="mayhen_event_cat_filter_select" aria-label="Lọc theo chế độ thiệp" onchange="this.form.submit()">
                        <option value="">Tất cả kiểu thiệp</option>
                        <option value="invitation" {{ ($filters['mode'] ?? '') === 'invitation' ? 'selected' : '' }}>Thiệp Mời &amp; RSVP</option>
                        <option value="greeting" {{ ($filters['mode'] ?? '') === 'greeting' ? 'selected' : '' }}>Thiệp Chúc &amp; Lì Xì</option>
                    </select>

                    <select name="envelope_color" class="mayhen_event_cat_filter_select" aria-label="Lọc theo màu phong bì" onchange="this.form.submit()">
                        <option value="">Tất cả màu phong bì</option>
                        @foreach($envelopeColors as $envColor)
                            <option value="{{ $envColor['id'] }}" {{ ($filters['envelope_color'] ?? '') === $envColor['id'] ? 'selected' : '' }}>
                                {{ $envColor['name'] }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="mayhen_event_cat_filter_btn">Lọc mẫu</button>

                    @if($hasActiveFilter)
                        <a href="{{ route('event_invitations.category', $category['slug']) }}" class="mayhen_event_cat_filter_reset">Xóa lọc</a>
                    @endif
                </form>
            </div>

            @if(empty($templates))
                <div class="mayhen_event_cat_empty_state">
                    <p>Chưa tìm thấy mẫu thiệp nào khớp với bộ lọc hiện tại trong danh mục <strong>{{ $category['short_name'] }}</strong>.</p>
                    <a href="{{ route('event_invitations.category', $category['slug']) }}" class="mayhen_event_btn_primary">Xem lại toàn bộ {{ $totalCategoryTemplatesCount }} mẫu</a>
                </div>
            @else
                <div class="mayhen_event_tpl_grid">
                    @foreach($templates as $tpl)
                        @php $defaults = $tpl['defaults']; @endphp
                        <article class="mayhen_event_tpl_card mayhen_event_palette_{{ $tpl['default_palette'] }} mayhen_event_env_{{ $tpl['envelope_color'] }}">
                            <div class="mayhen_event_pp_showcase">
                                <span class="mayhen_event_tpl_pro_tag">✦ PRO VIP</span>
                                <span class="mayhen_event_tpl_mode_tag">
                                    {{ $tpl['card_mode'] === 'greeting' ? 'Thiệp Chúc & Lì Xì' : 'Thiệp Mời & RSVP' }}
                                </span>

                                <div class="mayhen_event_pp_env_back {{ !empty($tpl['envelope_back_img']) ? 'has-real-envelope-img' : '' }}" aria-hidden="true">
                                    @if(!empty($tpl['envelope_back_img']))
                                        <img
                                            src="{{ asset($tpl['envelope_back_img']) }}"
                                            alt=""
                                            width="320"
                                            height="280"
                                            class="mayhen_event_pp_real_env_thumb"
                                            loading="{{ $loop->index < 3 ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                        >
                                    @else
                                        <div class="mayhen_event_pp_env_flap"></div>
                                        <div class="mayhen_event_pp_env_liner mayhen_event_liner_{{ $tpl['envelope_liner'] }}"></div>
                                        <div class="mayhen_event_pp_env_pocket"></div>
                                    @endif
                                </div>

                                <div class="mayhen_event_pp_front_card mayhen_event_border_{{ $tpl['card_border'] }} mayhen_event_cat_card_surface">
                                    <div class="mayhen_event_card_scene_layer" aria-hidden="true">
                                        {!! \App\Services\EventInvitationService::renderCardSceneSvg($tpl['artwork_motif']) !!}
                                    </div>

                                    <div class="mayhen_event_cat_card_inner">
                                        <span class="mayhen_event_cat_card_badge">
                                            {{ \Illuminate\Support\Str::limit($defaults['event_badge'], 34) }}
                                        </span>

                                        <div class="mayhen_event_cat_card_host">
                                            {{ \Illuminate\Support\Str::limit($defaults['host_name'], 28) }}
                                        </div>

                                        <div class="mayhen_event_cat_card_sub">
                                            {{ \Illuminate\Support\Str::limit($defaults['sub_host_name'], 42) }}
                                        </div>

                                        @if(!empty($defaults['show_datetime_venue']))
                                            <div class="mayhen_event_cat_card_date">
                                                {{ \Illuminate\Support\Carbon::parse($defaults['event_date'])->format('d . m . Y') }} &bull; {{ $defaults['party_time'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mayhen_event_tpl_body">
                                <div>
                                    <h3 class="mayhen_event_tpl_name">{{ $tpl['name'] }}</h3>
                                    <p class="mayhen_event_tpl_tagline">{{ $tpl['tagline'] }}</p>
                                </div>

                                <div class="mayhen_event_tpl_actions">
                                    <a href="{{ route('event_invitations.preview', $tpl['slug']) }}" class="mayhen_event_btn_outline">
                                        Xem Thử Mẫu
                                    </a>
                                    <a href="{{ route('event_invitations.studio', $tpl['slug']) }}" class="mayhen_event_btn_primary">
                                        ✦ Tạo Thử &amp; Sửa Ngay
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- 5. Các khối nội dung & bố cục SEO riêng biệt theo thứ tự tối ưu của từng danh mục --}}
        <div id="cam-nang-seo" class="mayhen_event_cat_seo_stack">
            @foreach($sectionOrder as $sectionKey)
                @if($sectionKey === 'pillars' && !empty($seoProfile['pillars']))
                    <section class="mayhen_event_cat_block mayhen_event_cat_pillars_block">
                        <span class="mayhen_event_cat_kicker">Điểm Nổi Bật Riêng Của {{ $category['short_name'] }}</span>
                        <h2 class="mayhen_event_cat_block_title">{{ $seoProfile['pillars_title'] }}</h2>
                        <div class="mayhen_event_cat_pillars_grid">
                            @foreach($seoProfile['pillars'] as $pillar)
                                <article class="mayhen_event_cat_pillar_card">
                                    <span class="mayhen_event_cat_pillar_icon" aria-hidden="true">{{ $pillar['icon'] }}</span>
                                    <h3 class="mayhen_event_cat_pillar_title">{{ $pillar['title'] }}</h3>
                                    <p class="mayhen_event_cat_pillar_desc">{{ $pillar['desc'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($sectionKey === 'special' && !empty($seoProfile['special_block']))
                    @php $special = $seoProfile['special_block']; @endphp
                    <section class="mayhen_event_cat_block mayhen_event_cat_special_block mayhen_event_cat_special--{{ $special['type'] }}">
                        <span class="mayhen_event_cat_kicker">{{ $special['kicker'] }}</span>
                        <h2 class="mayhen_event_cat_block_title">{{ $special['title'] }}</h2>

                        @if($special['type'] === 'comparison')
                            <div class="mayhen_event_cat_table_wrap">
                                <table class="mayhen_event_cat_compare_table">
                                    <thead>
                                        <tr>
                                            @foreach($special['columns'] as $col)
                                                <th scope="col">{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($special['rows'] as $row)
                                            <tr>
                                                <th scope="row">{{ $row[0] }}</th>
                                                <td>{{ $row[1] }}</td>
                                                <td>{{ $row[2] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($special['type'] === 'checklist')
                            <div class="mayhen_event_cat_checklist_grid">
                                @foreach($special['items'] as $idx => $item)
                                    <div class="mayhen_event_cat_checklist_card">
                                        <span class="mayhen_event_cat_checklist_num">0{{ $idx + 1 }}</span>
                                        <div>
                                            <h3 class="mayhen_event_cat_checklist_title">{{ $item['title'] }}</h3>
                                            <p class="mayhen_event_cat_checklist_desc">{{ $item['desc'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($special['type'] === 'timeline')
                            <ol class="mayhen_event_cat_timeline_list">
                                @foreach($special['items'] as $item)
                                    <li class="mayhen_event_cat_timeline_item">
                                        <span class="mayhen_event_cat_timeline_time">{{ $item['time'] }}</span>
                                        <div class="mayhen_event_cat_timeline_content">
                                            <h3 class="mayhen_event_cat_timeline_title">{{ $item['title'] }}</h3>
                                            <p class="mayhen_event_cat_timeline_desc">{{ $item['desc'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </section>
                @endif

                @if($sectionKey === 'wording' && !empty($category['wording_presets']))
                    <section id="loi-van-mau" class="mayhen_event_cat_block mayhen_event_cat_wording_block">
                        <span class="mayhen_event_cat_kicker">Kho Lời Văn Có Sẵn Trong Studio</span>
                        <h2 class="mayhen_event_cat_block_title">Mẫu lời mời &amp; lời chúc {{ $category['short_name'] }} hay, sao chép nhanh</h2>
                        <div class="mayhen_event_cat_wording_grid">
                            @foreach($category['wording_presets'] as $preset)
                                <article class="mayhen_event_cat_wording_card">
                                    <div class="mayhen_event_cat_wording_head">
                                        <h3 class="mayhen_event_cat_wording_label">✦ {{ $preset['label'] }}</h3>
                                        <button
                                            type="button"
                                            class="mayhen_event_cat_copy_btn"
                                            data-copy-wording="{{ $preset['msg'] }}"
                                        >
                                            Sao chép
                                        </button>
                                    </div>
                                    <p class="mayhen_event_cat_wording_sub">{{ $preset['sub'] }}</p>
                                    <blockquote class="mayhen_event_cat_wording_quote">“{{ $preset['msg'] }}”</blockquote>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($sectionKey === 'editorial' && !empty($seoProfile['editorial_sections']))
                    <section class="mayhen_event_cat_block mayhen_event_cat_editorial_block">
                        <span class="mayhen_event_cat_kicker">Cẩm Nang Văn Hóa &amp; Thiết Kế {{ $brandName }}</span>
                        <h2 class="mayhen_event_cat_block_title">Thiết kế {{ $category['name'] }} chuẩn đẹp trên {{ $brandName }}</h2>
                        <p class="mayhen_event_cat_editorial_intro">{{ $category['cultural_intro'] }}</p>

                        <div class="mayhen_event_cat_editorial_cols">
                            @foreach($seoProfile['editorial_sections'] as $article)
                                <article class="mayhen_event_cat_editorial_card">
                                    <h3 class="mayhen_event_cat_editorial_h3">{{ $article['heading'] }}</h3>
                                    <p class="mayhen_event_cat_editorial_p">{{ $article['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($sectionKey === 'steps' && !empty($seoProfile['steps']))
                    <section class="mayhen_event_cat_block mayhen_event_cat_steps_block">
                        <span class="mayhen_event_cat_kicker">Dễ Sử Dụng Trên Cả Điện Thoại &amp; Máy Tính</span>
                        <h2 class="mayhen_event_cat_block_title">{{ $seoProfile['steps_title'] }}</h2>
                        <ol class="mayhen_event_cat_steps_grid">
                            @foreach($seoProfile['steps'] as $stepIndex => $step)
                                <li class="mayhen_event_cat_step_card">
                                    <span class="mayhen_event_cat_step_num">Bước {{ $stepIndex + 1 }}</span>
                                    <h3 class="mayhen_event_cat_step_title">{{ $step['title'] }}</h3>
                                    <p class="mayhen_event_cat_step_desc">{{ $step['desc'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                @if($sectionKey === 'faq' && !empty($faqs))
                    <section id="cau-hoi-thuong-gap" class="mayhen_event_cat_block mayhen_event_cat_faq_block">
                        <span class="mayhen_event_cat_kicker">Giải Đáp Chi Tiết</span>
                        <h2 class="mayhen_event_cat_block_title">Câu hỏi thường gặp về {{ $category['short_name'] }}</h2>
                        <div class="mayhen_event_cat_faq_list">
                            @foreach($faqs as $faq)
                                <details class="mayhen_event_cat_faq_item" {{ $loop->first ? 'open' : '' }}>
                                    <summary class="mayhen_event_cat_faq_q">{{ $faq['q'] }}</summary>
                                    <p class="mayhen_event_cat_faq_a">{{ $faq['a'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>

        {{-- 6. Liên kết nội bộ theo chủ đề liên quan (Topical Authority & Crawl Depth <= 2) --}}
        @if(!empty($relatedCategories))
            <section class="mayhen_event_cat_related_section" aria-labelledby="mayhenRelatedCatHeading">
                <div class="mayhen_event_cat_related_head">
                    <h2 id="mayhenRelatedCatHeading" class="mayhen_event_cat_section_title">Khám phá thêm dịp lễ &amp; sự kiện liên quan</h2>
                    <a href="{{ route('event_invitations.index') }}" class="mayhen_event_cat_related_all">Xem tất cả 10 danh mục &rarr;</a>
                </div>
                <div class="mayhen_event_cat_related_grid">
                    @foreach($relatedCategories as $rel)
                        <a href="{{ route('event_invitations.category', $rel['slug']) }}" class="mayhen_event_cat_related_card">
                            <span class="mayhen_event_cat_related_icon" aria-hidden="true">{{ $rel['icon_symbol'] }}</span>
                            <div>
                                <strong class="mayhen_event_cat_related_name">{{ $rel['name'] }}</strong>
                                <p class="mayhen_event_cat_related_desc">{{ \Illuminate\Support\Str::limit($rel['hero_subtitle'], 92) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 7. CTA nâng cấp & liên kết tới dịch vụ chính Thiệp Cưới --}}
        <aside class="mayhen_event_pro_banner">
            <div class="mayhen_event_pro_banner_copy">
                <h2 class="mayhen_event_pro_banner_title">Đặc quyền trọn bộ {{ $brandName }} Gói Pro VIP</h2>
                <p class="mayhen_event_pro_banner_desc">
                    Dùng thử và thiết kế toàn bộ mẫu {{ mb_strtolower($category['short_name']) }} hoàn toàn miễn phí. Sở hữu Gói Pro để xuất bản đường dẫn riêng, cá nhân hóa tên khách mời hàng loạt và mở khóa cả kho <a href="{{ route('templates.index') }}" class="mayhen_event_cat_inline_link">Mẫu Thiệp Cưới Online</a>.
                </p>
            </div>
            <a href="{{ route('pricing') }}" class="mayhen_event_pro_banner_cta">Xem Bảng Giá Gói Pro &rarr;</a>
        </aside>

    </div>
</div>
@endsection
