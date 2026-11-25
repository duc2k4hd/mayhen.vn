@extends('client.layouts.master')

@php
    $newsBrandName = \App\Models\PlatformSetting::getBrandName();
    $seoTitle = ($post->meta_title ?: $post->title) . ' | Tin Tức';
    $rawDescription = $post->meta_description ?: ($post->summary ?: Str::limit(strip_tags($post->content), 160));
    $seoDescription = trim(preg_replace('/\s+/', ' ', $rawDescription));

    $canonicalUrl = route('news.show', $post->slug);

    $resolvedCover = $post->cover_image_url;
    if (!empty($resolvedCover)) {
        $seoCoverImage = Str::startsWith($resolvedCover, ['http://', 'https://'])
            ? $resolvedCover
            : asset(ltrim($resolvedCover, '/'));
    } else {
        $seoCoverImage = asset('storage/images/client/banners/banner-home.webp');
    }

    $authorName = $post->author_name ?: ($post->user?->name ?: ('Ban Phát Triển ' . $newsBrandName));
    $publishedIso = $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String();
    $modifiedIso = $post->updated_at ? $post->updated_at->toIso8601String() : $publishedIso;

    if (!empty($post->meta_keywords)) {
        $seoKeywords = $post->meta_keywords;
    } elseif (!empty($post->tags) && is_array($post->tags)) {
        $seoKeywords = implode(', ', $post->tags);
    } else {
        $seoKeywords = $post->title . ', tin tức ' . $newsBrandName . ', cập nhật tính năng, thiệp cưới online';
    }

    $wordCount = str_word_count(strip_tags($post->content));
    $readingTimeMinutes = $post->reading_time_minutes ?: max(1, (int) ceil($wordCount / 200));
    $timeRequiredIso = 'PT' . $readingTimeMinutes . 'M';
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', $seoKeywords)
@section('meta_author', $authorName)
@section('canonical', $canonicalUrl)
@section('og_type', 'article')
@section('og_image', $seoCoverImage)
@section('og_image_alt', $post->title)
@section('og_image_width', '1200')
@section('og_image_height', '630')

@if(!$post->isPublished())
    @section('robots', 'noindex, nofollow')
@else
    @section('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')
@endif

@push('meta')
    <meta property="article:published_time" content="{{ $publishedIso }}">
    <meta property="article:modified_time" content="{{ $modifiedIso }}">
    <meta property="article:author" content="{{ $authorName }}">
    <meta property="article:section" content="{{ $post->category_label ?: 'Tin tức' }}">
    @if(!empty($post->tags) && is_array($post->tags))
        @foreach($post->tags as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/blog.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/blog.js') }}"></script>
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
                    'name' => 'Tin tức & Cập nhật',
                    'item' => route('news.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $post->title,
                    'item' => $canonicalUrl,
                ],
            ],
        ],
        [
            '@type' => 'NewsArticle',
            '@id' => $canonicalUrl . '#newsarticle',
            'headline' => Str::limit($post->title, 110),
            'name' => $post->title,
            'description' => $seoDescription,
            'image' => [
                $seoCoverImage,
            ],
            'datePublished' => $publishedIso,
            'dateModified' => $modifiedIso,
            'inLanguage' => 'vi-VN',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl,
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => $authorName,
                'url' => route('home'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $newsBrandName,
                'url' => route('home'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('storage/images/client/banners/banner-home.webp'),
                ],
            ],
            'articleSection' => $post->category_label ?: 'Cập Nhật Hệ Thống',
            'keywords' => $seoKeywords,
            'wordCount' => $wordCount,
            'timeRequired' => $timeRequiredIso,
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_blog_reading_progress" id="mayhen_blog_reading_progress" aria-hidden="true"></div>

<div class="mayhen_blog_detail_page">
    <div class="mayhen_client_layout_container mayhen_blog_detail_container">

        {{-- Breadcrumb --}}
        <nav class="mayhen_blog_breadcrumb" aria-label="Breadcrumb">
            <ol class="mayhen_blog_breadcrumb_list">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="mayhen_blog_breadcrumb_separator">/</li>
                <li><a href="{{ route('news.index') }}">Tin tức &amp; Cập nhật</a></li>
                @if($post->category_label)
                    <li class="mayhen_blog_breadcrumb_separator">/</li>
                    <li><a href="{{ route('news.index', ['category' => \App\Models\NewsPost::categorySlug($post->category_label)]) }}">{{ $post->category_label }}</a></li>
                @endif
                <li class="mayhen_blog_breadcrumb_separator">/</li>
                <li class="mayhen_blog_breadcrumb_current" aria-current="page">{{ Str::limit($post->title, 42) }}</li>
            </ol>
        </nav>

        {{-- Header --}}
        <header class="mayhen_blog_article_header">
            <a href="{{ route('news.index', ['category' => \App\Models\NewsPost::categorySlug($post->category_label ?: 'Cập Nhật Hệ Thống')]) }}" class="mayhen_blog_badge_pill">
                {{ $post->category_label ?: 'Cập Nhật Hệ Thống' }}
            </a>

            <h1 class="mayhen_blog_article_title">{{ $post->title }}</h1>

            @if($post->summary)
                <div class="mayhen_blog_article_lead">
                    <p>{{ $post->summary }}</p>
                </div>
            @endif

            <div class="mayhen_blog_meta_bar">
                <div class="mayhen_blog_author_box">
                    <div class="mayhen_blog_author_avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($post->author_name ?: 'MH', 0, 1)) }}
                    </div>
                    <div class="mayhen_blog_author_info">
                        <span class="mayhen_blog_author_name">{{ $post->author_name ?: ('Ban Phát Triển ' . $newsBrandName) }}</span>
                        <span class="mayhen_blog_author_role">Bản tin chính thức từ {{ $newsBrandName }}</span>
                    </div>
                </div>

                <div class="mayhen_blog_stats_row">
                    <span class="mayhen_blog_stat_item" title="Ngày xuất bản">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>{{ $post->formatted_published_date }}</span>
                    </span>
                    <span class="mayhen_blog_dot">&bull;</span>
                    <span class="mayhen_blog_stat_item" title="Thời gian đọc ước tính">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>{{ $readingTimeMinutes }} phút đọc</span>
                    </span>
                    <span class="mayhen_blog_dot">&bull;</span>
                    <span class="mayhen_blog_stat_item" title="Lượt xem bản tin">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <span>{{ number_format($post->view_count) }} lượt xem</span>
                    </span>
                </div>
            </div>

            {{-- Thanh tiện ích đọc bài --}}
            <div class="mayhen_blog_toolbar">
                <div class="mayhen_blog_toolbar_group">
                    <span class="mayhen_blog_toolbar_label">Cỡ chữ:</span>
                    <div class="mayhen_blog_font_stepper">
                        <button type="button" class="mayhen_blog_tool_btn" id="mayhen_blog_font_decrease" title="Giảm cỡ chữ (A−)">A−</button>
                        <span class="mayhen_blog_font_display" id="mayhen_blog_font_size_display">17px</span>
                        <button type="button" class="mayhen_blog_tool_btn" id="mayhen_blog_font_increase" title="Tăng cỡ chữ (A+)">A+</button>
                    </div>
                </div>

                <div class="mayhen_blog_toolbar_actions">
                    <button type="button" class="mayhen_blog_action_btn" id="mayhen_blog_copy_link_btn" title="Sao chép liên kết bản tin">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <span>Sao chép link</span>
                    </button>

                    <button type="button" class="mayhen_blog_action_btn" id="mayhen_blog_print_btn" title="In bài viết hoặc lưu PDF">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        <span>In bài</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- Ảnh bìa --}}
        @if($post->cover_image_url)
            <div class="mayhen_blog_cover_wrapper">
                <img src="{{ $seoCoverImage }}" alt="{{ $post->title }}" class="mayhen_blog_cover_img" loading="eager" fetchpriority="high">
                <figcaption class="mayhen_blog_cover_caption">Bản tin chính thức &bull; {{ $newsBrandName }}</figcaption>
            </div>
        @endif

        {{-- Bố cục 2 cột --}}
        <div class="mayhen_blog_layout_grid">
            <aside class="mayhen_blog_sidebar">
                <div class="mayhen_blog_toc_card" id="mayhen_blog_toc_widget">
                    <div class="mayhen_blog_toc_header">
                        <div class="mayhen_blog_toc_title_wrap">
                            <span class="mayhen_blog_toc_sparkle">✦</span>
                            <h2 class="mayhen_blog_toc_title">Nội Dung Chính</h2>
                        </div>
                        <button type="button" class="mayhen_blog_toc_toggle_mobile" id="mayhen_blog_toc_toggle_mobile" aria-expanded="false" aria-label="Mở hoặc thu gọn mục lục">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>

                    @if(!empty($toc))
                        <nav class="mayhen_blog_toc_nav" id="mayhen_blog_toc_nav">
                            <ul class="mayhen_blog_toc_list">
                                @foreach($toc as $item)
                                    <li class="mayhen_blog_toc_item {{ $item['level'] === 3 ? 'mayhen_blog_toc_item--h3' : 'mayhen_blog_toc_item--h2' }}">
                                        <a href="#{{ $item['id'] }}" class="mayhen_blog_toc_link" data-toc-id="{{ $item['id'] }}">
                                            <span class="mayhen_blog_toc_bullet">&rsaquo;</span>
                                            <span class="mayhen_blog_toc_text">{{ $item['text'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    @else
                        <p class="mayhen_blog_toc_empty">Xem chi tiết nội dung bản tin ở khung bên cạnh.</p>
                    @endif

                    <div class="mayhen_blog_sidebar_promo">
                        <span class="mayhen_blog_promo_tag">Trải Nghiệm Ngay</span>
                        <h3 class="mayhen_blog_promo_title">Khám Phá Kho Mẫu Thiệp Cưới {{ $newsBrandName }}</h3>
                        <p class="mayhen_blog_promo_desc">Thiết kế Quiet Luxury, tự tạo thiệp cưới chỉ trong 5 phút.</p>
                        <a href="{{ route('templates.index') }}" class="mayhen_blog_promo_btn">
                            Xem Kho Mẫu Thiệp
                        </a>
                    </div>
                </div>
            </aside>

            <main class="mayhen_blog_main_content">
                <article class="mayhen_blog_article_body" id="mayhen_blog_article_body">
                    {!! $processedContent !!}
                </article>

                @if(!empty($post->tags) && is_array($post->tags))
                    <div class="mayhen_blog_tags_section">
                        <span class="mayhen_blog_tags_label">Từ khóa bản tin:</span>
                        <div class="mayhen_blog_tags_list">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('news.index', ['q' => $tag]) }}" class="mayhen_blog_tag_pill">
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Chia sẻ --}}
                <div class="mayhen_blog_footer_share">
                    <div class="mayhen_blog_share_info">
                        <span class="mayhen_blog_share_label">Chia sẻ bản tin này tới bạn bè:</span>
                    </div>
                    <div class="mayhen_blog_share_btns">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" class="mayhen_blog_share_social_btn mayhen_blog_share_fb" title="Chia sẻ lên Facebook">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </a>

                        <a href="https://zalo.me/share?url={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" class="mayhen_blog_share_social_btn mayhen_blog_share_zalo" title="Chia sẻ qua Zalo">
                            <span class="mayhen_blog_zalo_badge">Zalo</span>
                            <span>Chia sẻ</span>
                        </a>

                        <button type="button" class="mayhen_blog_share_social_btn mayhen_blog_share_copy" data-action="copy-article-link" title="Sao chép liên kết">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                            <span>Sao chép</span>
                        </button>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="mayhen_blog_cta_card">
                    <div class="mayhen_blog_cta_inner">
                        <span class="mayhen_blog_cta_sparkle">✦ ✦ ✦</span>
                        <h3 class="mayhen_blog_cta_title">Trải Nghiệm Những Tính Năng Mới Nhất Trên {{ $newsBrandName }}</h3>
                        <p class="mayhen_blog_cta_desc">
                            Khởi tạo thiệp cưới online phong cách Quiet Luxury, cá nhân hóa tên khách mời và quản lý xác nhận tham dự (RSVP) ngay hôm nay.
                        </p>
                        <div class="mayhen_blog_cta_actions">
                            <a href="{{ route('templates.index') }}" class="mayhen_blog_cta_btn_primary">
                                Xem Bộ Sưu Tập Mẫu Thiệp &rarr;
                            </a>
                            <a href="{{ route('invitation.create') }}" class="mayhen_blog_cta_btn_secondary">
                                Tạo Thiệp Ngay
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Điều hướng bài trước / kế tiếp --}}
                @if(isset($prevPost) || isset($nextPost))
                    <nav class="mayhen_blog_post_nav" aria-label="Điều hướng bản tin">
                        <div class="mayhen_blog_post_nav_col">
                            @if(isset($prevPost))
                                <a href="{{ route('news.show', $prevPost->slug) }}" class="mayhen_blog_post_nav_link mayhen_blog_post_nav_prev">
                                    <span class="mayhen_blog_post_nav_label">&larr; Bản tin trước</span>
                                    <span class="mayhen_blog_post_nav_title">{{ $prevPost->title }}</span>
                                </a>
                            @endif
                        </div>
                        <div class="mayhen_blog_post_nav_col">
                            @if(isset($nextPost))
                                <a href="{{ route('news.show', $nextPost->slug) }}" class="mayhen_blog_post_nav_link mayhen_blog_post_nav_next">
                                    <span class="mayhen_blog_post_nav_label">Bản tin kế tiếp &rarr;</span>
                                    <span class="mayhen_blog_post_nav_title">{{ $nextPost->title }}</span>
                                </a>
                            @endif
                        </div>
                    </nav>
                @endif

                {{-- Các bản tin khác --}}
                @if($relatedPosts->isNotEmpty())
                    <section class="mayhen_blog_related_section">
                        <div class="mayhen_blog_related_header">
                            <span class="mayhen_blog_related_kicker">✦ Bản Tin Gần Đây</span>
                            <h3 class="mayhen_blog_related_title">Tin Tức &amp; Cập Nhật Khác</h3>
                        </div>
                        <div class="mayhen_blog_related_grid">
                            @foreach($relatedPosts as $related)
                                <article class="mayhen_blog_related_card">
                                    <a href="{{ route('news.show', $related->slug) }}" class="mayhen_blog_related_thumb_link">
                                        <img src="{{ $related->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $related->title }}" class="mayhen_blog_related_thumb" loading="lazy">
                                    </a>
                                    <div class="mayhen_blog_related_body">
                                        <h4 class="mayhen_blog_related_card_title">
                                            <a href="{{ route('news.show', $related->slug) }}">{{ $related->title }}</a>
                                        </h4>
                                        <div class="mayhen_blog_meta_row">
                                            <span>{{ $related->formatted_published_date }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $related->reading_time_minutes }} phút đọc</span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>
        </div>
    </div>
</div>

<button type="button" class="mayhen_blog_back_to_top" id="mayhen_blog_back_to_top" aria-label="Cuộn lên đầu trang" title="Cuộn lên đầu trang">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
</button>

<div class="mayhen_blog_toast" id="mayhen_blog_toast" role="status" aria-live="polite"></div>
@endsection
