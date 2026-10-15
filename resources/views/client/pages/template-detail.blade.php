@extends('client.layouts.master')

@php
    $seoTitle = 'Mẫu Thiệp Cưới ' . $template->name . ' Online | ' . config('app.name');
    $seoDesc = 'Khám phá mẫu thiệp cưới online ' . $template->name . ' (' . $template->category_label . ') tại Mây Hẹn. Tích hợp RSVP xác nhận khách mời, album ảnh cưới, mã QR mừng cưới VietQR và nhạc nền. Tạo thiệp cưới đẹp chỉ trong ' . $template->setup_time . '.';
    $canonicalUrl = route('templates.show', $template->slug);
    $createUrl = route('invitation.create', ['template' => $template->slug]);
    $previewUrl = route('templates.preview', $template->slug);

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Trang Chủ',
                        'item' => route('home'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Mẫu Thiệp Cưới',
                        'item' => route('templates.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $template->name,
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            [
                '@type' => 'Product',
                'name' => $template->name,
                'image' => $template->image,
                'description' => $seoDesc,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => config('app.name'),
                ],
                'category' => $template->category_label,
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $canonicalUrl,
                    'priceCurrency' => 'VND',
                    'price' => $template->tier === 'free' ? '0' : '199000',
                    'availability' => 'https://schema.org/InStock',
                    'seller' => [
                        '@type' => 'Organization',
                        'name' => config('app.name'),
                    ],
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => '5.0',
                    'reviewCount' => max((int) $template->usage_count, 120),
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Mẫu thiệp ' . $template->name . ' có chèn được bài hát đám cưới và album ảnh không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Có. Mẫu thiệp hỗ trợ đầy đủ album ảnh cưới chất lượng cao, bài hát nhạc nền lãng mạn tự động phát và đếm ngược ngày cưới.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Khách mời có thể xác nhận tham dự (RSVP) trực tiếp trên thiệp không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Có. Mẫu thiệp cưới Mây Hẹn tích hợp sẵn tính năng RSVP giúp khách phản hồi đi một mình hay cùng người thương, ăn chay hay ăn mặn, và gửi lời chúc trực tiếp.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Tôi có thể chỉnh sửa lại nội dung thiệp sau khi đã gửi đi không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hoàn toàn được. Mọi cập nhật về giờ đón khách, sảnh tiệc hay ảnh cưới đều được đồng bộ tức thì trên cùng một đường link duy nhất mà không cần gửi lại link mới.',
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDesc)
@section('robots', 'noindex, follow')
@section('canonical', $canonicalUrl)
@section('og_type', 'product')
@section('og_image', $template->image)

@push('schema')
    <!-- Schema.org JSON-LD Structured Data (Product + BreadcrumbList + FAQPage) -->
    <script type="application/ld+json">
    {!! json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/template-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_template_detail_wrapper">
        <!-- 1. Breadcrumb Navigation -->
        <nav class="mayhen_template_detail_breadcrumb" aria-label="Điều hướng liên kết">
            <a href="{{ route('home') }}" class="mayhen_template_detail_breadcrumb_link">Trang Chủ</a>
            <span class="mayhen_template_detail_breadcrumb_sep" aria-hidden="true">/</span>
            <a href="{{ route('templates.index') }}" class="mayhen_template_detail_breadcrumb_link">Mẫu Thiệp Cưới</a>
            <span class="mayhen_template_detail_breadcrumb_sep" aria-hidden="true">/</span>
            <span class="mayhen_template_detail_breadcrumb_current">{{ $template->name }}</span>
        </nav>

        <!-- 2. Main Two-Column Compact Editorial Layout -->
        <div class="mayhen_template_detail_grid">
            <!-- Left Column: Visual Mockup Preview Sticky & Gallery Showcase -->
            <div class="mayhen_template_detail_preview_sticky">
                <div class="mayhen_template_detail_preview_box">
                    <img src="{{ $template->image }}" alt="Mẫu thiệp cưới online {{ $template->name }}" class="mayhen_template_detail_preview_img" id="mayhen_main_preview_img" width="490" height="612" loading="eager">
                    
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="mayhen_template_detail_preview_overlay_btn" title="Mở trang xem thử toàn màn hình">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span>Mở Trải Nghiệm Xem Thử Live</span>
                    </a>
                </div>

                <!-- Showcase Thư Viện Ảnh Chi Tiết Các Góc Mẫu Thiệp -->
                <div class="mayhen_template_detail_gallery_strip">
                    <div class="mayhen_template_detail_gallery_thumb active" onclick="document.getElementById('mayhen_main_preview_img').src='{{ $template->image }}'">
                        <img src="{{ $template->image }}" alt="Mặt trước thiệp cưới">
                        <span class="mayhen_template_detail_gallery_thumb_label">Toàn cảnh</span>
                    </div>
                    <div class="mayhen_template_detail_gallery_thumb" onclick="window.open('{{ $previewUrl }}', '_blank')">
                        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=400&q=80" alt="Khoảnh khắc cô dâu chú rể">
                        <span class="mayhen_template_detail_gallery_thumb_label">Ảnh kỷ niệm</span>
                    </div>
                    <div class="mayhen_template_detail_gallery_thumb" onclick="window.open('{{ $previewUrl }}', '_blank')">
                        <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=400&q=80" alt="Sảnh tiệc hoàng gia">
                        <span class="mayhen_template_detail_gallery_thumb_label">Sảnh tiệc</span>
                    </div>
                    <div class="mayhen_template_detail_gallery_thumb" onclick="window.open('{{ $previewUrl }}', '_blank')">
                        <img src="https://images.unsplash.com/photo-1532712938310-34cb3982ef74?auto=format&fit=crop&w=400&q=80" alt="Tem sáp niêm phong">
                        <span class="mayhen_template_detail_gallery_thumb_label">Tem sáp 3D</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Editorial Information & Actions -->
            <div class="mayhen_template_detail_info">
                <!-- Badges Row - Nổi bật cực đỉnh chữ Gói Pro VIP -->
                <div class="mayhen_template_detail_badges_row">
                    @if ($template->tier === 'pro')
                        <span class="mayhen_template_detail_vip_pro_badge">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                            <span>GÓI PRO VIP</span>
                        </span>
                    @elseif ($template->tier === 'plus')
                        <span class="mayhen_template_detail_plus_badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="6 3 18 3 22 9 12 22 2 9"/></svg>
                            <span>GÓI PLUS</span>
                        </span>
                    @else
                        <span class="mayhen_template_detail_free_badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <span>GÓI MIỄN PHÍ 100%</span>
                        </span>
                    @endif

                    <span class="mayhen_template_detail_badge_cat">
                        {{ $template->category_label }}
                    </span>
                    <span class="mayhen_template_detail_badge_time">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Cài đặt {{ $template->setup_time }}</span>
                    </span>
                </div>

                <!-- Main Title H1 -->
                <h1 class="mayhen_template_detail_title">{{ $template->name }}</h1>

                <!-- Trust Meta & Social Proof -->
                <div class="mayhen_template_detail_meta_bar">
                    <div class="mayhen_template_detail_rating">
                        <span>★★★★★</span>
                        <span>5.0</span>
                    </div>
                    <span>&bull;</span>
                    <div class="mayhen_template_detail_pairs">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span><strong>{{ number_format($template->usage_count) }}+</strong> cặp đôi đã tạo mẫu thiệp này</span>
                    </div>
                </div>

                <!-- Emotional Description -->
                <p class="mayhen_template_detail_desc">
                    {{ $template->description }}
                </p>

                <!-- High-Converting Action Buttons (Nút chính luôn để rõ Tạo Thiệp Với Mẫu Này) -->
                <div class="mayhen_template_detail_actions">
                    <a href="{{ $createUrl }}" class="mayhen_template_detail_btn_primary" title="Bắt đầu tạo thiệp cưới với mẫu {{ $template->name }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <span>Tạo Thiệp Với Mẫu Này</span>
                    </a>
                    
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="mayhen_template_detail_btn_secondary" title="Mở trang xem thử mẫu thiệp {{ $template->name }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Mở Trải Nghiệm Xem Thử Live</span>
                    </a>
                </div>

                <!-- Feature Highlights Box -->
                <div class="mayhen_template_detail_features_wrap">
                    <div class="mayhen_template_detail_features_heading">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>Đặc Điểm & Tính Năng Nổi Bật:</span>
                    </div>
                    <div class="mayhen_template_detail_features_grid">
                        @foreach ($template->features as $feat)
                            <div class="mayhen_template_detail_feature_item">
                                <svg class="mayhen_template_detail_feature_icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $feat }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quality Guarantees Strip -->
                <div class="mayhen_template_detail_guarantees">
                    <div class="mayhen_template_detail_guarantee_item">
                        <svg class="mayhen_template_detail_guarantee_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <div class="mayhen_template_detail_guarantee_label">Không giới hạn khách</div>
                    </div>
                    <div class="mayhen_template_detail_guarantee_item">
                        <svg class="mayhen_template_detail_guarantee_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        <div class="mayhen_template_detail_guarantee_label">Tải trang dưới 0.8s</div>
                    </div>
                    <div class="mayhen_template_detail_guarantee_item">
                        <svg class="mayhen_template_detail_guarantee_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <div class="mayhen_template_detail_guarantee_label">Bảo mật riêng tư</div>
                    </div>
                    <div class="mayhen_template_detail_guarantee_item">
                        <svg class="mayhen_template_detail_guarantee_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        <div class="mayhen_template_detail_guarantee_label">Cập nhật tức thì 24/7</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Section Chuyên Sâu: "Vì Sao Mẫu Thiệp Này Xứng Đáng Để Bạn Xuống Tiền?" -->
        <section class="mayhen_template_detail_selling_section" aria-labelledby="selling-heading">
            <div class="mayhen_template_detail_selling_header">
                <span class="mayhen_template_detail_selling_badge">Trải Nghiệm Đẳng Cấp & Tiết Kiệm</span>
                <h2 id="selling-heading" class="mayhen_template_detail_selling_title">Vì Sao Mẫu Thiệp Này Xứng Đáng Để Bạn Xuống Tiền?</h2>
                <p style="font-size: 0.875rem; color: var(--mayhen-color-text-muted); margin: 0;">
                    Không chỉ là một tấm thiệp báo tin mừng, đây là lời mời cưới trang trọng chạm đến cảm xúc của từng vị khách quý
                </p>
            </div>

            <div class="mayhen_template_detail_selling_grid">
                <div class="mayhen_template_detail_selling_card">
                    <div class="mayhen_template_detail_selling_icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    </div>
                    <h3 class="mayhen_template_detail_selling_card_title">Hiệu Ứng Hoàng Gia Độc Bản</h3>
                    <p class="mayhen_template_detail_selling_card_desc">
                        Nắp phong bì mở ra mượt mà, con dấu sáp niêm phong 3D tinh xảo dập chữ cái tên hai bạn (Monogram), kết hợp âm thanh nhạc cưới du dương khiến khách mời trầm trồ ngay giây đầu tiên mở thiệp.
                    </p>
                </div>

                <div class="mayhen_template_detail_selling_card">
                    <div class="mayhen_template_detail_selling_icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <h3 class="mayhen_template_detail_selling_card_title">Tiết Kiệm 80% Chi Phí In Ấn</h3>
                    <p class="mayhen_template_detail_selling_card_desc">
                        In 300 - 500 thiệp giấy cao cấp có thể tốn từ 3 đến 8 triệu đồng. Với Mây Hẹn, bạn chỉ trả một lần duy nhất để gửi không giới hạn khách mời, có thể sửa đổi thông tin giờ tiệc tức thì mà không lo hủy in hỏng thiệp.
                    </p>
                </div>

                <div class="mayhen_template_detail_selling_card">
                    <div class="mayhen_template_detail_selling_icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                    </div>
                    <h3 class="mayhen_template_detail_selling_card_title">Quản Lý Khách & VietQR Tiện Lợi</h3>
                    <p class="mayhen_template_detail_selling_card_desc">
                        Tích hợp sẵn bảng RSVP để khách phản hồi đi một mình hay cùng gia đình, ăn chay hay ăn mặn. Mã VietQR tự động điền số tài khoản giúp bạn bè phương xa gửi lời chúc và mừng cưới chỉ với 1 chạm.
                    </p>
                </div>
            </div>
        </section>

        <!-- 4. Section: 3-Step Creation Process -->
        <section class="mayhen_template_detail_extra_section" aria-labelledby="process-heading">
            <h2 id="process-heading" class="mayhen_template_detail_section_title">Tạo Thiệp Cưới Trong 3 Bước Đơn Giản</h2>
            <p class="mayhen_template_detail_section_subtitle">Không cần biết thiết kế, bất kỳ cặp đôi nào cũng có thể tự tạo thiệp cưới online chuẩn thẩm mỹ Quiet Luxury</p>

            <div class="mayhen_template_detail_steps_grid">
                <div class="mayhen_template_detail_step_card">
                    <div class="mayhen_template_detail_step_num">1</div>
                    <h3 class="mayhen_template_detail_step_title">Nhập Thông Tin Đôi Bạn</h3>
                    <p class="mayhen_template_detail_step_desc">Điền tên cô dâu chú rể, ngày giờ hôn lễ, sảnh tiệc đón khách và tải lên những bức ảnh cưới ưng ý nhất.</p>
                </div>
                <div class="mayhen_template_detail_step_card">
                    <div class="mayhen_template_detail_step_num">2</div>
                    <h3 class="mayhen_template_detail_step_title">Xem Trực Tiếp & Cá Nhân Hóa</h3>
                    <p class="mayhen_template_detail_step_desc">Mọi thay đổi đều hiển thị trực quan theo thời gian thực. Tùy chọn bài hát nhạc nền và màu nhấn thiệp.</p>
                </div>
                <div class="mayhen_template_detail_step_card">
                    <div class="mayhen_template_detail_step_num">3</div>
                    <h3 class="mayhen_template_detail_step_title">Nhận Link & Gửi Quan Khách</h3>
                    <p class="mayhen_template_detail_step_desc">Nhận đường link thiệp cưới cá nhân hóa riêng để gửi tới người thân, bạn bè qua Zalo, Messenger hoặc SMS.</p>
                </div>
            </div>
        </section>

        <!-- 5. Section: Frequently Asked Questions (FAQ) -->
        <section class="mayhen_template_detail_extra_section" aria-labelledby="faq-heading">
            <h2 id="faq-heading" class="mayhen_template_detail_section_title">Câu Hỏi Thường Gặp Về Mẫu Thiệp Này</h2>
            <p class="mayhen_template_detail_section_subtitle">Giải đáp nhanh những thắc mắc thường gặp của các cặp đôi khi chuẩn bị thiệp cưới online</p>

            <div class="mayhen_template_detail_faq_list">
                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">Mẫu thiệp này có hỗ trợ quét mã QR mừng cưới và chỉ đường không?</h3>
                    <p class="mayhen_template_detail_faq_a">Có. Thiệp cưới tích hợp sẵn mã VietQR tự động điền số tài khoản ngân hàng và tên người nhận, kèm nút bấm mở bản đồ Google Maps chỉ dẫn đường đến tận sảnh tiệc.</p>
                </div>
                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">Tôi có thể gửi thiệp có tên riêng của từng vị khách không?</h3>
                    <p class="mayhen_template_detail_faq_a">Hoàn toàn có thể. Tính năng quản lý khách mời của Mây Hẹn cho phép bạn tạo danh sách khách và sinh ra các link thiệp cá nhân hóa kèm tên khách mời (Ví dụ: Kính mời Anh Hoàng & Người Thương).</p>
                </div>
                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">Sau khi tạo thiệp xong, tôi có được hỗ trợ chỉnh sửa không?</h3>
                    <p class="mayhen_template_detail_faq_a">Bạn có thể chỉnh sửa lại bất kỳ thông tin nào bất cứ lúc nào trong trang quản trị. Đội ngũ kỹ thuật của Mây Hẹn luôn sẵn sàng hỗ trợ bạn qua Zalo 24/7.</p>
                </div>
            </div>
        </section>

        <!-- 6. Section: Similar Templates (Internal Links) -->
        @if ($similarTemplates->isNotEmpty())
            <section class="mayhen_template_detail_extra_section" aria-labelledby="similar-heading">
                <h2 id="similar-heading" class="mayhen_template_detail_section_title">Mẫu Thiệp Cưới Cùng Phong Cách</h2>
                <p class="mayhen_template_detail_section_subtitle">Khám phá thêm các thiết kế thiệp cưới được yêu thích khác trong cùng bộ sưu tập</p>

                <div class="mayhen_template_detail_similar_grid">
                    @foreach ($similarTemplates as $item)
                        <article class="mayhen_home_template_card" data-tier="{{ $item->tier }}">
                            <div class="mayhen_home_template_card_img_wrap">
                                <img src="{{ $item->image }}" alt="Mẫu thiệp {{ $item->name }}" class="mayhen_home_template_card_img" loading="lazy" width="380" height="475">
                                <span class="mayhen_home_template_card_badge">{{ $item->badge }}</span>
                                <span class="mayhen_home_template_card_tier_badge {{ $item->tier_badge_class }}">{{ $item->tier_label }}</span>
                            </div>
                            <div class="mayhen_home_template_card_body">
                                <div class="mayhen_home_template_card_meta">
                                    <span class="mayhen_home_template_card_category">{{ $item->category_label }}</span>
                                    <span class="mayhen_home_template_card_setup_badge">{{ $item->setup_time }}</span>
                                </div>
                                <h3 class="mayhen_home_template_card_title">{{ $item->name }}</h3>
                                <p class="mayhen_home_template_card_desc">{{ $item->description }}</p>
                                <div class="mayhen_home_template_card_footer">
                                    <span class="mayhen_home_template_card_users">{{ number_format($item->usage_count) }} lượt tạo</span>
                                    <div class="mayhen_home_template_card_actions">
                                        <a href="{{ route('templates.preview', $item->slug) }}" class="mayhen_home_template_card_btn_preview" target="_blank" rel="noopener">Xem thử</a>
                                        <a href="{{ route('templates.show', $item->slug) }}" class="mayhen_home_template_card_btn_select">Chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
