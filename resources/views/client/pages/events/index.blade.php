@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
@endphp

@section('title', 'Thiệp Sự Kiện & Lễ Hội Việt Nam: Tết, Trung Thu, Giáng Sinh, Đầy Tháng, Sinh Nhật, Tân Gia | ' . $brandName)
@section('meta_description', 'Khám phá 30+ mẫu thiệp mời & thiệp chúc mừng chuẩn văn hóa Việt trên ' . $brandName . ': Thiệp Chúc Tết Nguyên Đán, Trung Thu, Giáng Sinh, Đầy Tháng, Thôi Nôi, Sinh Nhật Trẻ Em & Người Lớn, Tân Gia, Tất Niên Công Ty, Tốt Nghiệp, 8/3, 20/10, 20/11. Phong bì 3D lót hoa văn sang trọng.')
@section('meta_keywords', 'thiệp chúc tết online, thiệp trung thu online, thiệp mời tiệc giáng sinh, thiệp mời đầy tháng thôi nôi, thiệp mời sinh nhật cho bé, thiệp mời tân gia, thiệp mời tất niên công ty, thiệp mời lễ tốt nghiệp, ' . mb_strtolower($brandName, 'UTF-8'))
@section('canonical', route('event_invitations.index'))

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/event-invitations.css') }}">
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => route('event_invitations.index') . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ ' . $brandName,
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Thiệp Sự Kiện, Lễ Hội & Chúc Mừng',
                    'item' => route('event_invitations.index'),
                ],
            ],
        ],
        [
            '@type' => 'CollectionPage',
            '@id' => route('event_invitations.index') . '#collection',
            'url' => route('event_invitations.index'),
            'name' => 'Thiệp Sự Kiện Gia Đình, Mùa Lễ Hội & Thiệp Chúc Mừng — ' . $brandName,
            'description' => 'Bộ sưu tập 30+ mẫu thiệp phong bì 3D lót hoa văn dành cho Tết Nguyên Đán, Trung Thu, Giáng Sinh, Đầy Tháng, Thôi Nôi, Sinh Nhật, Tân Gia, Tất Niên, Khai Trương, Tốt Nghiệp, 8/3, 20/10, 20/11.',
            'inLanguage' => 'vi-VN',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_event_hub_wrapper">
    <div class="mayhen_client_layout_container">

        {{-- 1. HERO HEADER (TÁCH BIỆT RÕ VỚI THIỆP CƯỚI CHÍNH) --}}
        <header class="mayhen_event_hero">
            <span class="mayhen_event_pro_pill">✦ 30+ Thiết Kế Phong Bì 3D Lót Hoa Văn • Đặc Quyền Gói Pro VIP ✦</span>
            <h1 class="mayhen_event_hero_title">Thiệp Sự Kiện Gia Đình, Mùa Lễ Hội &amp; Thiệp Chúc Mừng</h1>
            <p class="mayhen_event_hero_desc">
                Trải nghiệm nghệ thuật thiệp phong bì 3D lót nhũ vàng &amp; hoa văn thủ công cho mọi dịp lễ và cột mốc đáng nhớ tại Việt Nam: <strong>Tết Nguyên Đán, Tết Trung Thu, Giáng Sinh, Đầy Tháng, Thôi Nôi, Sinh Nhật, Tân Gia, Tất Niên, Khai Trương, Tốt Nghiệp &amp; 8/3 - 20/10 - 20/11</strong>.
            </p>
            <div class="mayhen_event_wedding_notice">
                <span>💍 Bạn đang tìm kiếm <strong>Thiệp Cưới Online</strong> cho lễ Thành Hôn / Vu Quy?</span>
                <a href="{{ route('templates.index') }}">Vào Kho Mẫu Thiệp Cưới Chính Của {{ $brandName }} &rarr;</a>
            </div>
        </header>

        {{-- 2. KHÁM PHÁ THEO 10 DỊP LỄ & SỰ KIỆN VIỆT NAM (EXPLORE BY CATEGORY) --}}
        <section aria-label="Khám phá theo danh mục dịp lễ Việt Nam">
            <div class="mayhen_event_explore_strip">
                @foreach($categories as $cat)
                    <a href="{{ route('event_invitations.category', $cat['slug']) }}" class="mayhen_event_explore_card {{ $selectedCategorySlug === $cat['slug'] ? 'is-active' : '' }}">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                <span style="font-size: 24px;" aria-hidden="true">{{ $cat['icon_symbol'] }}</span>
                                <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #8C6D3F;">{{ $cat['season_badge'] }}</span>
                            </div>
                            <h2 style="font-family: var(--mayhen-font-serif, 'Playfair Display', Georgia, serif); font-size: 16.5px; margin: 0 0 4px; color: #1F1D1A;">
                                {{ $cat['short_name'] }}
                            </h2>
                        </div>
                        <span style="font-size: 11.5px; color: #85182A; font-weight: 600;">Xem 3 mẫu &rarr;</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- 3. BỐ CỤC 2 CỘT CHUẨN PAPERLESS POST: BỘ LỌC BÊN TRÁI + LƯỚI THIỆP & PHONG BÌ 3D BÊN PHẢI --}}
        <div class="mayhen_event_catalog_layout">

            {{-- CỘT TRÁI: BỘ LỌC CHUYÊN SÂU --}}
            <aside class="mayhen_event_filter_sidebar" aria-label="Bộ lọc mẫu thiệp sự kiện">
                <div class="mayhen_event_filter_group">
                    <h3 class="mayhen_event_filter_heading">Tìm kiếm nhanh</h3>
                    <form method="GET" action="{{ route('event_invitations.index') }}">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="mayhen_event_input" placeholder="Tìm Tết, Giáng sinh, Thôi nôi..." style="font-size: 12.5px; padding: 8px 11px;">
                    </form>
                </div>

                <div class="mayhen_event_filter_group">
                    <h3 class="mayhen_event_filter_heading">Loại Thiệp</h3>
                    <ul class="mayhen_event_filter_list">
                        <li>
                            <a href="{{ route('event_invitations.index') }}" class="mayhen_event_filter_link {{ empty($filters['mode']) && empty($selectedCategorySlug) ? 'is-active' : '' }}">
                                <span>Tất cả mẫu thiệp</span>
                                <span>30</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('event_invitations.index', ['mode' => 'invitation']) }}" class="mayhen_event_filter_link {{ ($filters['mode'] ?? '') === 'invitation' ? 'is-active' : '' }}">
                                <span>Thiệp Mời Tiệc (Có Bản Đồ &amp; RSVP)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('event_invitations.index', ['mode' => 'greeting']) }}" class="mayhen_event_filter_link {{ ($filters['mode'] ?? '') === 'greeting' ? 'is-active' : '' }}">
                                <span>Thiệp Chúc Mừng &amp; Lì Xì Từ Xa</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="mayhen_event_filter_group">
                    <h3 class="mayhen_event_filter_heading">Màu Phong Bì 3D</h3>
                    <div class="mayhen_event_color_dots">
                        @foreach($envelopeColors as $envKey => $envVal)
                            <a href="{{ route('event_invitations.index', array_filter(['category' => $selectedCategorySlug, 'envelope_color' => $envKey])) }}"
                               class="mayhen_event_color_dot_link {{ ($filters['envelope_color'] ?? '') === $envKey ? 'is-active' : '' }}"
                               style="background-color: {{ $envVal['hex'] }};"
                               title="{{ $envVal['name'] }}"></a>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_event_filter_group">
                    <h3 class="mayhen_event_filter_heading">Khung Viền Mặt Thiệp</h3>
                    <ul class="mayhen_event_filter_list">
                        @foreach($cardBorders as $bKey => $bVal)
                            <li>
                                <a href="{{ route('event_invitations.index', array_filter(['category' => $selectedCategorySlug, 'card_border' => $bKey])) }}"
                                   class="mayhen_event_filter_link {{ ($filters['card_border'] ?? '') === $bKey ? 'is-active' : '' }}">
                                    <span>{{ $bVal['name'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mayhen_event_filter_group">
                    <h3 class="mayhen_event_filter_heading">10 Dịp Lễ Việt Nam</h3>
                    <ul class="mayhen_event_filter_list">
                        @foreach($categories as $cat)
                            <li>
                                <a href="{{ route('event_invitations.category', $cat['slug']) }}" class="mayhen_event_filter_link {{ $selectedCategorySlug === $cat['slug'] ? 'is-active' : '' }}">
                                    <span>{{ $cat['icon_symbol'] }} {{ $cat['short_name'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            {{-- CỘT PHẢI: LƯỚI 30 MẪU THIỆP + PHONG BÌ 3D LÓT HOA VĂN --}}
            <div>
                <div class="mayhen_event_tpl_grid">
                    @forelse($templates as $tpl)
                        @php
                            $defaults = $tpl['defaults'];
                        @endphp
                        <article class="mayhen_event_tpl_card mayhen_event_palette_{{ $tpl['default_palette'] }} mayhen_event_env_{{ $tpl['envelope_color'] }}">
                            {{-- Sân khấu Thiệp Chính + Phong Bì Mở Nắp Lót Hoa Văn Phía Sau (Y Hệt Paperless Post) --}}
                            <div class="mayhen_event_pp_showcase">
                                <span class="mayhen_event_tpl_pro_tag">✦ PRO VIP</span>
                                <span class="mayhen_event_tpl_mode_tag">
                                    {{ $tpl['card_mode'] === 'greeting' ? 'Thiệp Chúc & Lì Xì' : 'Thiệp Mời & RSVP' }}
                                </span>

                                {{-- Phong bì 3D mở nắp nghiêng phía sau --}}
                                <div class="mayhen_event_pp_env_back" aria-hidden="true">
                                    <div class="mayhen_event_pp_env_flap"></div>
                                    <div class="mayhen_event_pp_env_liner mayhen_event_liner_{{ $tpl['envelope_liner'] }}"></div>
                                    <div class="mayhen_event_pp_env_pocket"></div>
                                </div>

                                {{-- Tờ thiệp 1 trang chính đặt phía trước có khung tranh sự kiện bao quanh 4 cạnh --}}
                                <div class="mayhen_event_pp_front_card mayhen_event_border_{{ $tpl['card_border'] }}" style="position: relative; overflow: hidden;">
                                    <div class="mayhen_event_card_scene_layer" aria-hidden="true">
                                        {!! \App\Services\EventInvitationService::renderCardSceneSvg($tpl['artwork_motif']) !!}
                                    </div>

                                    <div style="position: relative; z-index: 2; padding: 14px 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 6px;">
                                        <span style="font-size: 7.5px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #8C7A6B; line-height: 1.3;">
                                            {{ \Illuminate\Support\Str::limit($defaults['event_badge'], 34) }}
                                        </span>

                                        <div style="font-family: var(--mayhen-font-serif, 'Playfair Display', Georgia, serif); font-size: 13.5px; font-weight: 700; color: var(--mh-evt-primary); line-height: 1.25;">
                                            {{ \Illuminate\Support\Str::limit($defaults['host_name'], 28) }}
                                        </div>

                                        <div style="font-size: 8.5px; color: #6A635B; line-height: 1.35;">
                                            {{ \Illuminate\Support\Str::limit($defaults['sub_host_name'], 42) }}
                                        </div>

                                        @if(!empty($defaults['show_datetime_venue']))
                                            <div style="font-size: 7.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--mh-evt-primary); border-top: 1px solid rgba(207, 168, 96, 0.35); padding-top: 4px; margin-top: 2px;">
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
                                        Xem Thử
                                    </a>
                                    <a href="{{ route('event_invitations.studio', $tpl['slug']) }}" class="mayhen_event_btn_primary">
                                        ✦ Tạo &amp; Sửa Ngay
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; background: #FFFFFF; border-radius: 18px; border: 1px solid #E6DDD0;">
                            <p style="font-size: 15px; color: #6A635B; margin: 0 0 14px;">Không tìm thấy mẫu thiệp nào khớp bộ lọc hiện tại.</p>
                            <a href="{{ route('event_invitations.index') }}" class="mayhen_event_btn_primary">Xem Toàn Bộ 30 Mẫu Thiệp</a>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- 4. BANNER ĐẶC QUYỀN GÓI PRO --}}
        <section class="mayhen_event_pro_banner" aria-label="Đặc quyền Gói Pro VIP">
            <div class="mayhen_event_pro_banner_copy">
                <h2 class="mayhen_event_pro_banner_title">
                    @if($hasProPlan)
                        ✦ Tài khoản của bạn đã kích hoạt Đặc Quyền Gói Pro VIP!
                    @else
                        ✦ Tạo thử &amp; Phối phong bì 3D miễn phí — Xuất bản không giới hạn với Gói Pro VIP
                    @endif
                </h2>
                <p class="mayhen_event_pro_banner_desc">
                    Toàn bộ 30+ mẫu thiệp Chúc Tết, Trung Thu, Giáng Sinh, Đầy Tháng, Thôi Nôi, Sinh Nhật, Tân Gia, Tất Niên, Khai Trương &amp; Tốt Nghiệp đều mở sẵn để bạn tự tay phối màu phong bì, đổi lớp lót, tải ảnh và lưu nháp. Thành viên <strong>Gói Pro VIP (199.000đ)</strong> được xuất bản link <code>/loi-moi/slug</code> trọn đời mà không mất thêm bất kỳ chi phí nào.
                </p>
            </div>
            @if(!$hasProPlan)
                <a href="{{ route('checkout.show', 'pro') }}" class="mayhen_event_pro_banner_cta">
                    Mở Khóa Gói Pro VIP (199k) &rarr;
                </a>
            @endif
        </section>

    </div>
</div>
@endsection
