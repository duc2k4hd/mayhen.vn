@extends('client.layouts.master')

@php
    $newsBrandName = \App\Models\PlatformSetting::getBrandName();
@endphp

@section('title', 'Tin Tức & Bản Cập Nhật Mới Nhất | ' . $newsBrandName)
@section('meta_description', 'Theo dõi các tin tức, tính năng mới, bộ sưu tập mẫu thiệp cưới mới ra mắt và thông báo chính thức từ nền tảng thiệp cưới online ' . $newsBrandName . '.')
@section('canonical', route('news.index'))

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/blog.css') }}">
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => route('news.index') . '#breadcrumb',
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
            ],
        ],
        [
            '@type' => 'CollectionPage',
            '@id' => route('news.index') . '#collection',
            'url' => route('news.index'),
            'name' => 'Tin Tức & Cập Nhật Dự Án — ' . $newsBrandName,
            'description' => 'Những điểm mới, tính năng mới, mẫu thiệp mới và thông báo chính thức từ ' . $newsBrandName . '.',
            'inLanguage' => 'vi-VN',
            'publisher' => [
                '@type' => 'Organization',
                '@id' => route('home') . '#organization',
                'name' => $newsBrandName,
                'url' => route('home'),
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_blog_wrapper">
    {{-- Hero Section --}}
    <section class="mayhen_blog_hero">
        <div class="mayhen_client_layout_container">
            <span class="mayhen_blog_hero_badge">Bản Tin &amp; Nhật Ký Phát Triển</span>
            <h1 class="mayhen_blog_hero_title">Tin Tức &amp; Cập Nhật Từ {{ $newsBrandName }}</h1>
            <p class="mayhen_blog_hero_desc">
                Nơi cập nhật sớm nhất những tính năng mới, bộ sưu tập mẫu thiệp cưới vừa ra mắt và các chương trình ưu đãi dành riêng cho cộng đồng cặp đôi {{ $newsBrandName }}.
            </p>
        </div>
    </section>

    <div class="mayhen_client_layout_container">
        {{-- Category Filters --}}
        <div class="mayhen_blog_filter_bar">
            <a href="{{ route('news.index') }}" class="mayhen_blog_filter_link {{ empty($activeCategorySlug) ? 'mayhen_blog_filter_link_active' : '' }}">
                Tất Cả Bản Tin
            </a>
            @foreach($categoryCounts as $label => $count)
                @php $catSlug = \App\Models\NewsPost::categorySlug($label); @endphp
                <a href="{{ route('news.index', ['category' => $catSlug]) }}" class="mayhen_blog_filter_link {{ ($activeCategorySlug ?? null) === $catSlug ? 'mayhen_blog_filter_link_active' : '' }}">
                    {{ $label }} ({{ $count }})
                </a>
            @endforeach
        </div>

        {{-- Featured News --}}
        @if($featuredNews->isNotEmpty() && empty(request('category')) && empty(request('q')))
            @php $featured = $featuredNews->first(); @endphp
            <article class="mayhen_blog_featured_card">
                <div class="mayhen_blog_featured_image_wrap">
                    <img src="{{ $featured->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $featured->title }}" class="mayhen_blog_featured_image" loading="lazy">
                </div>
                <div class="mayhen_blog_featured_content">
                    <span class="mayhen_blog_category_pill">{{ $featured->category_label ?: 'Cập Nhật Hệ Thống' }}</span>
                    <h2 class="mayhen_blog_featured_title">
                        <a href="{{ route('news.show', $featured->slug) }}">{{ $featured->title }}</a>
                    </h2>
                    <p class="mayhen_blog_featured_summary">
                        {{ $featured->summary }}
                    </p>
                    <div class="mayhen_blog_meta_row">
                        <span>{{ $featured->author_name }}</span>
                        <span>&bull;</span>
                        <span>{{ $featured->formatted_published_date }}</span>
                        <span>&bull;</span>
                        <span>{{ $featured->reading_time_minutes }} phút đọc</span>
                    </div>
                </div>
            </article>
        @endif

        {{-- Danh Sách Tin Tức --}}
        <div class="mayhen_blog_grid">
            @forelse($posts as $post)
                <article class="mayhen_blog_card">
                    <a href="{{ route('news.show', $post->slug) }}" class="mayhen_blog_card_thumb_wrap">
                        <img src="{{ $post->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $post->title }}" class="mayhen_blog_card_thumb" loading="lazy">
                    </a>
                    <div class="mayhen_blog_card_body">
                        <span class="mayhen_blog_category_pill">{{ $post->category_label ?: 'Cập Nhật Hệ Thống' }}</span>
                        <h3 class="mayhen_blog_card_title">
                            <a href="{{ route('news.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="mayhen_blog_card_summary">
                            {{ Str::limit($post->summary, 130) }}
                        </p>
                        <div class="mayhen_blog_meta_row">
                            <span>{{ $post->formatted_published_date }}</span>
                            <span>&bull;</span>
                            <span>{{ $post->reading_time_minutes }} phút đọc</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="mayhen_blog_empty_state">
                    <p class="mayhen_blog_empty_text">Hiện chưa có bản tin nào trong mục này.</p>
                    <a href="{{ route('news.index') }}" class="mayhen_blog_filter_link mayhen_blog_filter_link_active">Xem toàn bộ tin tức</a>
                </div>
            @endforelse
        </div>

        {{-- Phân Trang --}}
        @if($posts->hasPages())
            <div class="mayhen_blog_pagination">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
