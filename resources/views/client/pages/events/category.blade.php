@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('event_invitations.category', $category['slug']);
@endphp

@section('title', $category['seo_title'] . ' | ' . $brandName)
@section('meta_description', $category['seo_description'])
@section('meta_keywords', $category['seo_keywords'])
@section('canonical', $canonicalUrl)

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
            ], !empty($category['faqs']) ? $category['faqs'] : [
                [
                    'q' => 'Tôi có thể tự đổi màu phong bì, lớp lót hoa văn và tải ảnh riêng lên ' . $category['short_name'] . ' không?',
                    'a' => 'Có 100%. Studio 5 Tab của ' . $brandName . ' cho phép bạn tự chọn 8 màu phong bì, 8 lớp lót hoa văn, 8 khung viền thiệp và tải ảnh thật lên mặt thiệp hoàn toàn miễn phí trước khi xuất bản.',
                ],
            ]),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_event_hub_wrapper">
    <div class="mayhen_client_layout_container">

        {{-- Hero --}}
        <header class="mayhen_event_hero">
            <span class="mayhen_event_pro_pill">{{ $category['icon_symbol'] }} {{ $category['season_badge'] }} &bull; Phong Bì 3D Lót Hoa Văn</span>
            <h1 class="mayhen_event_hero_title">{{ $category['name'] }}</h1>
            <p class="mayhen_event_hero_desc">{{ $category['hero_subtitle'] }}</p>
            <div class="mayhen_event_wedding_notice">
                <span>Tự tay phối màu phong bì, đổi lớp lót &amp; nhập thử lời mời miễn phí 100% &bull;</span>
                <a href="{{ route('event_invitations.index') }}">&larr; Xem tất cả 10 danh mục lễ hội &amp; sự kiện</a>
            </div>
        </header>

        {{-- Thanh chuyển nhanh giữa 10 dịp lễ Việt Nam --}}
        <div class="mayhen_event_explore_strip" style="margin-bottom: 30px;">
            @foreach($categories as $cat)
                <a href="{{ route('event_invitations.category', $cat['slug']) }}" class="mayhen_event_explore_card {{ $category['slug'] === $cat['slug'] ? 'is-active' : '' }}" style="padding: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">{{ $cat['icon_symbol'] }}</span>
                        <strong style="font-size: 13.5px;">{{ $cat['short_name'] }}</strong>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Danh sách mẫu của chuyên mục (Hiển thị Thiệp + Phong Bì 3D Mở Nắp Lót Hoa Văn) --}}
        <div class="mayhen_event_tpl_grid">
            @foreach($templates as $tpl)
                @php $defaults = $tpl['defaults']; @endphp
                <article class="mayhen_event_tpl_card mayhen_event_palette_{{ $tpl['default_palette'] }} mayhen_event_env_{{ $tpl['envelope_color'] }}">
                    <div class="mayhen_event_pp_showcase">
                        <span class="mayhen_event_tpl_pro_tag">✦ PRO VIP</span>
                        <span class="mayhen_event_tpl_mode_tag">
                            {{ $tpl['card_mode'] === 'greeting' ? 'Thiệp Chúc & Lì Xì' : 'Thiệp Mời & RSVP' }}
                        </span>

                        <div class="mayhen_event_pp_env_back" aria-hidden="true">
                            <div class="mayhen_event_pp_env_flap"></div>
                            <div class="mayhen_event_pp_env_liner mayhen_event_liner_{{ $tpl['envelope_liner'] }}"></div>
                            <div class="mayhen_event_pp_env_pocket"></div>
                        </div>

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
                            <h2 class="mayhen_event_tpl_name">{{ $tpl['name'] }}</h2>
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

        {{-- Bài viết giới thiệu văn hóa & Gợi ý lời mời chuẩn SEO --}}
        <section class="mayhen_event_section_card" style="text-align: left; max-width: 880px; margin: 0 auto 36px;">
            <span class="mayhen_event_badge_kicker">Văn Hóa Lời Mời &amp; Lời Chúc Việt Nam</span>
            <h2 class="mayhen_event_venue_title" style="font-size: 24px; margin-bottom: 12px;">Thiết kế {{ $category['name'] }} trên {{ $brandName }} có gì đặc biệt?</h2>
            <p class="mayhen_event_message_body" style="margin-bottom: 20px;">{{ $category['cultural_intro'] }}</p>

            @if(!empty($category['wording_presets']))
                <div style="border-top: 1px solid #EAE2D6; padding-top: 18px; margin-bottom: 18px;">
                    <h3 class="mayhen_event_venue_title" style="font-size: 18px; margin-bottom: 12px;">Mẫu lời văn gợi ý có sẵn trong Studio</h3>
                    @foreach($category['wording_presets'] as $preset)
                        <div style="padding: 12px 16px; border-radius: 12px; background: #FAF7F2; border: 1px solid #E8DFD2; margin-bottom: 10px;">
                            <strong style="display: block; font-size: 13.5px; color: #85182A; margin-bottom: 4px;">✦ {{ $preset['label'] }}</strong>
                            <p style="margin: 0; font-size: 13.5px; color: #4A433C; line-height: 1.65;">"{{ $preset['msg'] }}"</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
