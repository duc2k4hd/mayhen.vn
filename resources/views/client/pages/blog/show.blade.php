@extends('client.layouts.master')

@section('title', ($post->meta_title ?: $post->title) . ' | ' . config('app.name'))
@section('meta_description', $post->meta_description ?: $post->summary)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/blog.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/blog.js') }}"></script>
@endpush

@section('content')
{{-- Thanh Đo Tiến Độ Đọc (Top Scroll Progress Bar) --}}
<div class="mayhen_blog_reading_progress" aria-hidden="true"></div>

<div class="mayhen_blog_wrapper">
    <div class="mayhen_blog_detail_container">
        {{-- Breadcrumb --}}
        <nav style="font-size: 0.8125rem; color: var(--mh-blog-muted); margin-bottom: 24px;" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a> / 
            <a href="{{ route('blog.index') }}" style="color: inherit; text-decoration: none;">Cẩm nang cưới</a>
            @if($post->category)
                / <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" style="color: inherit; text-decoration: none;">{{ $post->category->name }}</a>
            @endif
        </nav>

        {{-- Tiêu Đề & Meta Header --}}
        <header class="mayhen_blog_detail_header">
            @if($post->category)
                <span class="mayhen_blog_category_pill">{{ $post->category->name }}</span>
            @endif
            <h1 class="mayhen_blog_detail_title">{{ $post->title }}</h1>
            @if($post->summary)
                <p class="mayhen_blog_detail_lead">{{ $post->summary }}</p>
            @endif
            <div class="mayhen_blog_meta_row" style="justify-content: center;">
                <span>Tác giả: <strong>{{ $post->author_name }}</strong></span>
                <span>&bull;</span>
                <span>{{ $post->formatted_published_date }}</span>
                <span>&bull;</span>
                <span>{{ $post->reading_time_minutes }} phút đọc</span>
                <span>&bull;</span>
                <span>{{ number_format($post->view_count) }} lượt xem</span>
            </div>
        </header>

        {{-- Ảnh Bìa Chính (Cover Image) --}}
        @if($post->cover_image_url)
            <div class="mayhen_blog_detail_cover_wrap">
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="mayhen_blog_detail_cover">
            </div>
        @endif

        {{-- Layout 2 Cột: Sườn Mục Lục (TOC) & Nội Dung Bài Viết --}}
        <div class="mayhen_blog_detail_layout">
            {{-- Sườn Mục Lục Cố Định --}}
            <aside class="mayhen_blog_toc_sticky">
                <div class="mayhen_blog_toc_title">Mục Lục Bài Viết</div>
                @if(!empty($toc))
                    <ul class="mayhen_blog_toc_list">
                        @foreach($toc as $item)
                            <li class="mayhen_blog_toc_item {{ $item['level'] === 3 ? 'mayhen_blog_toc_item_h3' : '' }}">
                                <a href="#{{ $item['id'] }}" class="mayhen_blog_toc_link">
                                    {{ $item['text'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p style="font-size: 0.8125rem; color: var(--mh-blog-muted); margin: 0;">Đọc bài viết dưới đây.</p>
                @endif

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--mh-blog-border);">
                    <button type="button" data-action="copy-article-link" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--mh-blog-border); background: #FAF8F5; color: var(--mh-blog-heading); font-size: 0.8125rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <span>Sao chép liên kết</span>
                    </button>
                </div>
            </aside>

            {{-- Nội Dung Trọng Tâm --}}
            <main>
                <div class="mayhen_blog_content">
                    {!! $processedContent !!}
                </div>

                {{-- Hộp Chia Sẻ & Tác Giả --}}
                <div class="mayhen_blog_share_box">
                    <div>
                        <span style="font-size: 0.8125rem; color: var(--mh-blog-muted);">Chuyên mục:</span>
                        <a href="{{ route('blog.index', ['category' => $post->category?->slug]) }}" style="font-size: 0.875rem; font-weight: 600; color: #9C7A4A; text-decoration: none; margin-left: 6px;">
                            {{ $post->category?->name ?? 'Cẩm Nang Cưới' }}
                        </a>
                    </div>
                    <div>
                        <button type="button" data-action="copy-article-link" style="padding: 8px 14px; border-radius: 20px; border: 1px solid var(--mh-blog-gold); background: #FFFDF9; color: #9C7A4A; font-size: 0.8125rem; font-weight: 600; cursor: pointer;">
                            Chia Sẻ Bài Viết
                        </button>
                    </div>
                </div>

                {{-- Kêu Gọi Hành Động (CTA Box Tạo Thiệp) --}}
                <div class="mayhen_blog_cta_card">
                    <h3 class="mayhen_blog_cta_title">Cùng Mây Hẹn Khởi Tạo Cánh Thiệp Cưới Độc Bản</h3>
                    <p style="color: var(--mh-blog-muted); max-width: 540px; margin: 0 auto; font-size: 0.9375rem; line-height: 1.6;">
                        Tận hưởng trải nghiệm tạo thiệp cưới online phong cách Quiet Luxury, gửi trao lời mời trang nhã tới quan khách chỉ trong 5 phút.
                    </p>
                    <a href="{{ route('templates.index') }}" class="mayhen_blog_cta_btn">
                        Khám Phá Kho Mẫu Thiệp Ngay &rarr;
                    </a>
                </div>

                {{-- Bài Viết Liên Quan --}}
                @if($relatedPosts->isNotEmpty())
                    <section style="margin-top: 60px;">
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.625rem; color: var(--mh-blog-heading); margin-bottom: 24px;">
                            Chuyên Đề Cùng Chủ Đề
                        </h3>
                        <div class="mayhen_blog_grid" style="margin-bottom: 0;">
                            @foreach($relatedPosts as $related)
                                <article class="mayhen_blog_card">
                                    <a href="{{ route('blog.show', $related->slug) }}" class="mayhen_blog_card_thumb_wrap">
                                        <img src="{{ $related->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $related->title }}" class="mayhen_blog_card_thumb" loading="lazy">
                                    </a>
                                    <div class="mayhen_blog_card_body">
                                        <h4 class="mayhen_blog_card_title" style="font-size: 1.1875rem;">
                                            <a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}</a>
                                        </h4>
                                        <div class="mayhen_blog_meta_row" style="font-size: 0.75rem;">
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
@endsection

