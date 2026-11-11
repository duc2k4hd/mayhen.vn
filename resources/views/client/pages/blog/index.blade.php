@extends('client.layouts.master')

@section('title', 'Cẩm Nang Cưới — Kinh Nghiệm & Ý Tưởng Cưới Tinh Tế | ' . config('app.name'))
@section('meta_description', 'Khám phá cẩm nang cưới phong cách Quiet Luxury từ ' . config('app.name') . ': ý tưởng thiết kế thiệp, nghi thức lễ cưới, quản lý bàn tiệc và kinh nghiệm tổ chức hôn lễ trọn vẹn.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/blog.css') }}">
@endpush

@section('content')
<div class="mayhen_blog_wrapper">
    {{-- Hero Section --}}
    <section class="mayhen_blog_hero">
        <div class="mayhen_client_layout_container">
            <span class="mayhen_blog_hero_badge">Ấn Phẩm &amp; Cẩm Nang Hôn Lễ</span>
            <h1 class="mayhen_blog_hero_title">Chuyên Đề &amp; Cảm Hứng Ngày Chung Đôi</h1>
            <p class="mayhen_blog_hero_desc">
                Nơi sẻ chia những góc nhìn tinh tế về nghệ thuật cưới Quiet Luxury, nghi lễ truyền thống, và những giải pháp số giúp ngày trọng đại thêm vẹn tròn.
            </p>
        </div>
    </section>

    <div class="mayhen_client_layout_container">
        {{-- Category Filters --}}
        <div class="mayhen_blog_filter_bar">
            <a href="{{ route('blog.index') }}" class="mayhen_blog_filter_link {{ empty(request('category')) ? 'mayhen_blog_filter_link_active' : '' }}">
                Tất Cả Chuyên Đề
            </a>
            @foreach($categories as $category)
                <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="mayhen_blog_filter_link {{ request('category') === $category->slug ? 'mayhen_blog_filter_link_active' : '' }}">
                    {{ $category->name }} ({{ $category->published_posts_count }})
                </a>
            @endforeach
        </div>

        {{-- Featured Article (Nếu ở trang chủ cẩm nang và có bài nổi bật) --}}
        @if($featuredPosts->isNotEmpty() && empty(request('category')) && empty(request('q')))
            @php $featured = $featuredPosts->first(); @endphp
            <article class="mayhen_blog_featured_card">
                <div class="mayhen_blog_featured_image_wrap">
                    <img src="{{ $featured->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $featured->title }}" class="mayhen_blog_featured_image" loading="lazy">
                </div>
                <div class="mayhen_blog_featured_content">
                    @if($featured->category)
                        <span class="mayhen_blog_category_pill">{{ $featured->category->name }}</span>
                    @endif
                    <h2 class="mayhen_blog_featured_title">
                        <a href="{{ route('blog.show', $featured->slug) }}">{{ $featured->title }}</a>
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

        {{-- Danh Sách Bài Viết --}}
        <div class="mayhen_blog_grid">
            @forelse($posts as $post)
                <article class="mayhen_blog_card">
                    <a href="{{ route('blog.show', $post->slug) }}" class="mayhen_blog_card_thumb_wrap">
                        <img src="{{ $post->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $post->title }}" class="mayhen_blog_card_thumb" loading="lazy">
                    </a>
                    <div class="mayhen_blog_card_body">
                        @if($post->category)
                            <span class="mayhen_blog_category_pill" style="font-size: 0.75rem;">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="mayhen_blog_card_title">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="mayhen_blog_card_summary">
                            {{ Str::limit($post->summary, 120) }}
                        </p>
                        <div class="mayhen_blog_meta_row">
                            <span>{{ $post->formatted_published_date }}</span>
                            <span>&bull;</span>
                            <span>{{ $post->reading_time_minutes }} phút đọc</span>
                        </div>
                    </div>
                </article>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; border: 1px solid var(--mh-blog-border);">
                    <p style="color: var(--mh-blog-muted); font-size: 1.0625rem; margin-bottom: 16px;">Chưa tìm thấy bài viết nào trong chuyên mục này.</p>
                    <a href="{{ route('blog.index') }}" class="mayhen_blog_filter_link mayhen_blog_filter_link_active">Xem toàn bộ cẩm nang</a>
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

