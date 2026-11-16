@extends('client.layouts.master')

@section('title', ($post->meta_title ?: $post->title) . ' | ' . config('app.name'))
@section('meta_description', $post->meta_description ?: ($post->summary ?: Str::limit(strip_tags($post->content), 160)))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/blog.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/blog.js') }}"></script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => route('blog.show', $post->slug) . '#breadcrumb',
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
                    'name' => 'Cẩm nang cưới',
                    'item' => route('blog.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $post->category?->name ?? 'Chuyên đề',
                    'item' => $post->category ? route('blog.index', ['category' => $post->category->slug]) : route('blog.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 4,
                    'name' => $post->title,
                    'item' => route('blog.show', $post->slug),
                ],
            ],
        ],
        [
            '@type' => 'BlogPosting',
            '@id' => route('blog.show', $post->slug) . '#article',
            'isPartOf' => [
                '@type' => 'Blog',
                '@id' => route('blog.index') . '#blog',
                'name' => 'Cẩm Nang Cưới Mây Hẹn',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                    'url' => route('home'),
                ],
            ],
            'headline' => $post->title,
            'description' => $post->summary ?: Str::limit(strip_tags($post->content), 160),
            'image' => $post->cover_image_url ? [$post->cover_image_url] : [asset('assets/client/images/home/hero-wedding-card.webp')],
            'datePublished' => $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String(),
            'dateModified' => $post->updated_at ? $post->updated_at->toIso8601String() : ($post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String()),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author_name ?: 'Ban Biên Tập Mây Hẹn',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => route('home'),
            ],
            'mainEntityOfPage' => route('blog.show', $post->slug),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
{{-- 1. THANH TIẾN ĐỘ ĐỌC BÀI VIẾT BÁM ĐỈNH (READING PROGRESS) --}}
<div class="mayhen_blog_reading_progress" id="mayhen_blog_reading_progress" aria-hidden="true"></div>

<div class="mayhen_blog_detail_page">
    <div class="mayhen_client_layout_container mayhen_blog_detail_container">
        
        {{-- 2. BREADCRUMB SEMANTIC NHỎ GỌN --}}
        <nav class="mayhen_blog_breadcrumb" aria-label="Breadcrumb">
            <ol class="mayhen_blog_breadcrumb_list">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="mayhen_blog_breadcrumb_separator">/</li>
                <li><a href="{{ route('blog.index') }}">Cẩm nang cưới</a></li>
                @if($post->category)
                    <li class="mayhen_blog_breadcrumb_separator">/</li>
                    <li><a href="{{ route('blog.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a></li>
                @endif
                <li class="mayhen_blog_breadcrumb_separator">/</li>
                <li class="mayhen_blog_breadcrumb_current" aria-current="page">{{ Str::limit($post->title, 40) }}</li>
            </ol>
        </nav>

        {{-- 3. HEADER BÀI VIẾT (EDITORIAL MAGAZINE HEADER) --}}
        <header class="mayhen_blog_article_header">
            @if($post->category)
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="mayhen_blog_badge_pill">
                    {{ $post->category->name }}
                </a>
            @endif

            <h1 class="mayhen_blog_article_title">{{ $post->title }}</h1>

            @if($post->summary)
                <div class="mayhen_blog_article_lead">
                    <p>{{ $post->summary }}</p>
                </div>
            @endif

            {{-- Thông tin tác giả & Meta --}}
            <div class="mayhen_blog_meta_bar">
                <div class="mayhen_blog_author_box">
                    <div class="mayhen_blog_author_avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($post->author_name ?: 'MH', 0, 1)) }}
                    </div>
                    <div class="mayhen_blog_author_info">
                        <span class="mayhen_blog_author_name">{{ $post->author_name ?: 'Ban Biên Tập' }}</span>
                        <span class="mayhen_blog_author_role">Chuyên gia cẩm nang cưới Mây Hẹn</span>
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
                        <span>{{ $post->reading_time_minutes }} phút đọc</span>
                    </span>
                    <span class="mayhen_blog_dot">&bull;</span>
                    <span class="mayhen_blog_stat_item" title="Lượt xem bài viết">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <span>{{ number_format($post->view_count) }} lượt xem</span>
                    </span>
                </div>
            </div>

            {{-- 4. THANH TIỆN ÍCH ĐỌC BÀI (READER CONTROLS TOOLBAR) --}}
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
                    <button type="button" class="mayhen_blog_action_btn" id="mayhen_blog_bookmark_btn" title="Lưu bài viết vào cẩm nang yêu thích">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mayhen_blog_bookmark_icon"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                        <span class="mayhen_blog_bookmark_text">Lưu bài</span>
                    </button>

                    <button type="button" class="mayhen_blog_action_btn" id="mayhen_blog_copy_link_btn" title="Sao chép liên kết bài viết">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <span>Sao chép link</span>
                    </button>

                    <button type="button" class="mayhen_blog_action_btn" id="mayhen_blog_print_btn" title="In bài viết ra giấy hoặc lưu PDF">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        <span>In bài</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- 5. ẢNH BÌA CHÍNH (EDITORIAL COVER IMAGE) --}}
        @if($post->cover_image_url)
            <div class="mayhen_blog_cover_wrapper">
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="mayhen_blog_cover_img" loading="eager">
                <figcaption class="mayhen_blog_cover_caption">Hình ảnh minh họa chuyên đề cưới &bull; Mây Hẹn Editorial</figcaption>
            </div>
        @endif

        {{-- 6. BỐ CỤC 2 CỘT: MỤC LỤC STICKY (TOC) & THÂN BÀI VIẾT NỀN TRẮNG --}}
        <div class="mayhen_blog_layout_grid">
            
            {{-- CỘT BÊN: MỤC LỤC BÀI VIẾT (TOC STICKY SIDEBAR) --}}
            <aside class="mayhen_blog_sidebar">
                <div class="mayhen_blog_toc_card" id="mayhen_blog_toc_widget">
                    <div class="mayhen_blog_toc_header">
                        <div class="mayhen_blog_toc_title_wrap">
                            <span class="mayhen_blog_toc_sparkle">✦</span>
                            <h2 class="mayhen_blog_toc_title">Mục Lục Bài Viết</h2>
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
                        <p class="mayhen_blog_toc_empty">Đọc tiếp toàn bộ nội dung chuyên đề bên dưới.</p>
                    @endif

                    {{-- Khối widget mẫu thiệp cưới nhỏ gọn --}}
                    <div class="mayhen_blog_sidebar_promo">
                        <span class="mayhen_blog_promo_tag">Dành Cho Cặp Đôi</span>
                        <h3 class="mayhen_blog_promo_title">Tạo Thiệp Cưới Online Phong Cách Quiet Luxury</h3>
                        <p class="mayhen_blog_promo_desc">Miễn phí trải nghiệm hơn 99+ mẫu thiệp lãng mạn, tinh tế.</p>
                        <a href="{{ route('templates.index') }}" class="mayhen_blog_promo_btn">
                            Xem Mẫu Thiệp Ngay
                        </a>
                    </div>
                </div>
            </aside>

            {{-- CỘT CHÍNH: THÂN BÀI VIẾT VỚI HƠN 100 THẺ ĐƯỢC CHĂM CHÚT TỈ MỈ --}}
            <main class="mayhen_blog_main_content">
                <article class="mayhen_blog_article_body" id="mayhen_blog_article_body">
                    {!! $processedContent !!}
                </article>

                {{-- Tags Chuyên Đề (Nếu Có) --}}
                @if(!empty($post->tags) && is_array($post->tags))
                    <div class="mayhen_blog_tags_section">
                        <span class="mayhen_blog_tags_label">Chủ đề bài viết:</span>
                        <div class="mayhen_blog_tags_list">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('blog.index', ['q' => $tag]) }}" class="mayhen_blog_tag_pill">
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Hộp Chia Sẻ & Nút Tương Tác Cuối Bài --}}
                <div class="mayhen_blog_footer_share">
                    <div class="mayhen_blog_share_info">
                        <span class="mayhen_blog_share_label">Bạn thấy bài viết hữu ích? Chia sẻ cùng người thương &amp; bạn bè:</span>
                    </div>
                    <div class="mayhen_blog_share_btns">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener noreferrer" class="mayhen_blog_share_social_btn mayhen_blog_share_fb" title="Chia sẻ lên Facebook">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </a>

                        <a href="https://zalo.me/share?url={{ urlencode(request()->url()) }}" target="_blank" rel="noopener noreferrer" class="mayhen_blog_share_social_btn mayhen_blog_share_zalo" title="Chia sẻ qua Zalo">
                            <span class="mayhen_blog_zalo_badge">Zalo</span>
                            <span>Chia sẻ</span>
                        </a>

                        <button type="button" class="mayhen_blog_share_social_btn mayhen_blog_share_copy" data-action="copy-article-link" title="Sao chép liên kết">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                            <span>Sao chép</span>
                        </button>
                    </div>
                </div>

                {{-- 7. KHỐI KÊU GỌI HÀNH ĐỘNG (QUIET LUXURY INVITATION CTA) --}}
                <div class="mayhen_blog_cta_card">
                    <div class="mayhen_blog_cta_inner">
                        <span class="mayhen_blog_cta_sparkle">✦ ✦ ✦</span>
                        <h3 class="mayhen_blog_cta_title">Cùng Mây Hẹn Khởi Tạo Cánh Thiệp Cưới Độc Bản</h3>
                        <p class="mayhen_blog_cta_desc">
                            Trải nghiệm tạo thiệp cưới online phong cách Quiet Luxury, đồng bộ nhạc nền lãng mạn, hiệu ứng mở phong bì hoàng gia và quản lý xác nhận tham dự (RSVP) chu đáo.
                        </p>
                        <div class="mayhen_blog_cta_actions">
                            <a href="{{ route('templates.index') }}" class="mayhen_blog_cta_btn_primary">
                                Khám Phá Kho Mẫu Thiệp Ngay &rarr;
                            </a>
                            <a href="{{ route('invitation.create') }}" class="mayhen_blog_cta_btn_secondary">
                                Tự Tạo Thiệp Miễn Phí
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 8. ĐIỀU HƯỚNG BÀI TRƯỚC / BÀI KẾ TIẾP --}}
                @if(isset($prevPost) || isset($nextPost))
                    <nav class="mayhen_blog_post_nav" aria-label="Điều hướng bài viết">
                        <div class="mayhen_blog_post_nav_col">
                            @if(isset($prevPost))
                                <a href="{{ route('blog.show', $prevPost->slug) }}" class="mayhen_blog_post_nav_link mayhen_blog_post_nav_prev">
                                    <span class="mayhen_blog_post_nav_label">&larr; Bài trước</span>
                                    <span class="mayhen_blog_post_nav_title">{{ $prevPost->title }}</span>
                                </a>
                            @endif
                        </div>
                        <div class="mayhen_blog_post_nav_col">
                            @if(isset($nextPost))
                                <a href="{{ route('blog.show', $nextPost->slug) }}" class="mayhen_blog_post_nav_link mayhen_blog_post_nav_next">
                                    <span class="mayhen_blog_post_nav_label">Bài kế tiếp &rarr;</span>
                                    <span class="mayhen_blog_post_nav_title">{{ $nextPost->title }}</span>
                                </a>
                            @endif
                        </div>
                    </nav>
                @endif

                {{-- 9. BÀI VIẾT CÙNG CHUYÊN MỤC / LIÊN QUAN --}}
                @if($relatedPosts->isNotEmpty())
                    <section class="mayhen_blog_related_section">
                        <div class="mayhen_blog_related_header">
                            <span class="mayhen_blog_related_kicker">✦ Cảm Hứng Tiếp Theo</span>
                            <h3 class="mayhen_blog_related_title">Chuyên Đề Cùng Chủ Đề</h3>
                        </div>
                        <div class="mayhen_blog_related_grid">
                            @foreach($relatedPosts as $related)
                                <article class="mayhen_blog_related_card">
                                    <a href="{{ route('blog.show', $related->slug) }}" class="mayhen_blog_related_thumb_link">
                                        <img src="{{ $related->cover_image_url ?? asset('assets/client/images/home/hero-wedding-card.webp') }}" alt="{{ $related->title }}" class="mayhen_blog_related_thumb" loading="lazy">
                                    </a>
                                    <div class="mayhen_blog_related_body">
                                        <h4 class="mayhen_blog_related_card_title">
                                            <a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}</a>
                                        </h4>
                                        <div class="mayhen_blog_meta_row" style="font-size: 0.78125rem;">
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

{{-- 10. NÚT CUỘN LÊN ĐẦU TRANG (BACK TO TOP) --}}
<button type="button" class="mayhen_blog_back_to_top" id="mayhen_blog_back_to_top" aria-label="Cuộn lên đầu trang" title="Cuộn lên đầu trang">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
</button>

{{-- 11. HỘP THÔNG BÁO TOAST --}}
<div class="mayhen_blog_toast" id="mayhen_blog_toast" role="status" aria-live="polite"></div>
@endsection
