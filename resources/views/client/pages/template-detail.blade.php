@extends('client.layouts.master')

@php
    $tierLabel = match ($template->tier ?? 'free') {
        'pro' => 'Gói Pro VIP',
        'plus' => 'Gói Plus Cao Cấp',
        default => 'Mẫu Miễn Phí',
    };
    $tierClass = match ($template->tier ?? 'free') {
        'pro' => 'mayhen_template_detail_tier_pro',
        'plus' => 'mayhen_template_detail_tier_plus',
        default => 'mayhen_template_detail_tier_free',
    };
    $categoryLabel = $template->category_label ?? 'Thiệp cưới tinh tế';
    $primaryColor = $template->accent_color ?? '#B76E79';
    $fontFamily = $template->font_serif ?? 'Playfair Display / Cinzel';
    $features = $template->features ?? [];
    $usageCount = $template->usage_count ?? 120;
@endphp

@section('title', ('Mẫu thiệp "'. $template->name ?? '') . '" Sang Trọng & Qúy Phái | ' . config('app.name'))
@section('meta_description', 'Khám phá mẫu thiệp cưới ' . ($template->name ?? '') . ' chuẩn Quiet Luxury tại ' . config('app.name') . '. Bố cục thanh lịch, hiệu ứng chạm mở phong bì hoàng gia, album ảnh, RSVP và mừng cưới VietQR tiện lợi.')
@section('robots', 'noindex, follow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/template-detail.css') }}?v={{ time() }}">
@endpush

@push('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ $template->name }} — Mẫu Thiệp Cưới Online",
    "image": "{{ $template->thumbnail_url ?: asset('assets/client/images/og-share.jpg') }}",
    "description": "{{ $template->description }}",
    "brand": {
        "@type": "Brand",
        "name": "{{ config('app.name') }}"
    },
    "offers": {
        "@type": "Offer",
        "price": "{{ ($template->tier ?? 'free') === 'free' ? '0' : (($template->tier ?? 'free') === 'plus' ? '199000' : '399000') }}",
        "priceCurrency": "VND",
        "availability": "https://schema.org/InStock",
        "url": "{{ url()->current() }}"
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "{{ $usageCount }}"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "Mẫu thiệp cưới {{ $template->name }} có hiển thị đẹp trên điện thoại không?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Có. Mọi mẫu thiệp cưới tại Mây Hẹn đều được tối ưu hóa 100% hiển thị chuẩn tỉ lệ trên mọi dòng smartphone (iPhone, Samsung, Xiaomi) từ hiệu ứng mở phong thư, phông chữ đến ảnh cưới full HD."
            }
        },
        {
            "@type": "Question",
            "name": "Tôi có thể chỉnh sửa những thông tin gì trên mẫu này?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Bạn có thể tự do thay đổi tên cô dâu chú rể, ngày giờ cử hành hôn lễ, địa chỉ 2 nhà, bản đồ chỉ đường Google Maps, album ảnh cưới, nhạc nền lãng mạn và tài khoản ngân hàng mừng cưới VietQR."
            }
        },
        {
            "@type": "Question",
            "name": "Mẫu thiệp này có hỗ trợ gửi thiệp riêng theo tên từng khách mời không?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Có. Hệ thống quản lý khách mời thông minh của Mây Hẹn hỗ trợ bạn tạo link gửi đích danh từng vị khách (Ví dụ: Kính mời Anh Hoàng & Người Thương) để thể hiện sự trân trọng tối đa."
            }
        }
    ]
}
</script>
@endpush

@section('content')
<div class="mayhen_template_detail_wrapper">
    <div class="mayhen_template_detail_container">
        
        {{-- Breadcrumb thanh lịch tối giản --}}
        <nav class="mayhen_template_detail_breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span class="mayhen_template_detail_breadcrumb_sep">/</span>
            <a href="{{ route('templates.index') }}">Bộ sưu tập thiệp cưới</a>
            <span class="mayhen_template_detail_breadcrumb_sep">/</span>
            <span class="mayhen_template_detail_breadcrumb_current">{{ $template->name }}</span>
        </nav>

        {{-- Bố cục chính 2 cột chặt chẽ, tỷ lệ vàng 480px - 1fr --}}
        <section class="mayhen_template_detail_hero_grid">
            
            {{-- Cột trái: Sân khấu trưng bày thiệp nghệ thuật (Visual Stage) --}}
            <div class="mayhen_template_detail_stage_col">
                <div class="mayhen_template_detail_stage_card">
                    <div class="mayhen_template_detail_stage_media">
                        <img 
                            src="{{ $template->thumbnail_url ?: asset('assets/client/images/template-fallback.svg') }}" 
                            alt="Mẫu thiệp cưới online {{ $template->name }} phong cách {{ $categoryLabel }}" 
                            class="mayhen_template_detail_stage_img"
                            loading="eager"
                        >
                        <div class="mayhen_template_detail_stage_overlay"></div>
                        
                        {{-- Nút xem demo trực tiếp dạng Glassmorphism --}}
                        <div class="mayhen_template_detail_stage_floating_action">
                            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" rel="noopener noreferrer" class="mayhen_template_detail_btn_demo">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                                <span>Xem Trước Live</span>
                            </a>
                        </div>
                    </div>

                    {{-- Dải thông số kỹ thuật bên dưới ảnh --}}
                    <div class="mayhen_template_detail_stage_footer">
                        <div class="mayhen_template_detail_stage_spec">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                            <span>Chuẩn màn hình điện thoại 100%</span>
                        </div>
                        <div class="mayhen_template_detail_stage_spec">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span>Độc bản Quiet Luxury</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột phải: Thông tin tác phẩm, Hồ sơ thiết kế & Kêu gọi hành động --}}
            <div class="mayhen_template_detail_info_col">
                <div class="mayhen_template_detail_info_card">
                    
                    {{-- Khối Kicker: Danh mục, Số cặp đôi tin dùng & Huy hiệu Gói Pro VIP nổi bật --}}
                    <div class="mayhen_template_detail_kicker_row">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span class="mayhen_template_detail_category_badge">{{ $categoryLabel }}</span>
                            <span style="font-size: 0.75rem; color: var(--mayhen-color-text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <strong>{{ number_format($usageCount) }}+</strong> cặp đôi đã chọn
                            </span>
                        </div>
                        
                        <div class="mayhen_template_detail_tier_badge {{ $tierClass }}">
                            @if(($template->tier ?? 'free') === 'pro')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z"></path>
                                </svg>
                            @elseif(($template->tier ?? 'free') === 'plus')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            @endif
                            <span>{{ $tierLabel }}</span>
                        </div>
                    </div>

                    {{-- Tiêu đề mẫu thiệp chuẩn tạp chí --}}
                    <h1 class="mayhen_template_detail_title">{{ $template->name }}</h1>

                    {{-- Dòng mô tả cảm xúc tinh tế (SEO Lead) --}}
                    <p class="mayhen_template_detail_lead">
                        {{ $template->description ?: 'Bản hòa ca giữa vẻ đẹp tối giản và phong vị lãng mạn đương đại. Được chắt lọc từng đường nét typography, phối màu trang nhã và chuyển động chạm mở mượt mà để tôn vinh câu chuyện tình yêu của bạn.' }}
                    </p>

                    {{-- Bảng Hồ Sơ Thiết Kế (Design Atelier Sheet) --}}
                    <div class="mayhen_template_detail_atelier_sheet">
                        <div class="mayhen_template_detail_atelier_title">
                            <span>Hồ Sơ Thiết Kế Độc Bản</span>
                        </div>
                        <div class="mayhen_template_detail_atelier_grid">
                            <div class="mayhen_template_detail_atelier_item">
                                <span class="mayhen_template_detail_atelier_label">Tone sắc chủ đạo:</span>
                                <div class="mayhen_template_detail_atelier_val">
                                    <span class="mayhen_template_detail_color_dot" style="background-color: {{ $primaryColor }};"></span>
                                    <span>{{ $primaryColor }}</span>
                                </div>
                            </div>
                            <div class="mayhen_template_detail_atelier_item">
                                <span class="mayhen_template_detail_atelier_label">Kiểu chữ:</span>
                                <span class="mayhen_template_detail_atelier_val">{{ $fontFamily }}</span>
                            </div>
                            <div class="mayhen_template_detail_atelier_item">
                                <span class="mayhen_template_detail_atelier_label">Hiệu ứng mở:</span>
                                <span class="mayhen_template_detail_atelier_val">Phong thư lụa & Tem sáp 3D</span>
                            </div>
                            <div class="mayhen_template_detail_atelier_item">
                                <span class="mayhen_template_detail_atelier_label">Tương tác:</span>
                                <span class="mayhen_template_detail_atelier_val">RSVP, Nhạc nền & VietQR mừng cưới</span>
                            </div>
                        </div>
                    </div>

                    {{-- Danh sách tính năng cao cấp tích hợp trong mẫu --}}
                    @if(!empty($features))
                        <div class="mayhen_template_detail_features_list">
                            <span class="mayhen_template_detail_features_title">Đặc quyền có sẵn trong mẫu thiệp:</span>
                            <ul class="mayhen_template_detail_features_ul">
                                @foreach($features as $feat)
                                    <li>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span>{{ $feat }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Nút bấm kêu gọi hành động chính --}}
                    <div class="mayhen_template_detail_actions_group">
                        <a href="{{ route('invitation.create', ['template' => $template->slug]) }}" class="mayhen_template_detail_btn_primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                            <span>Tạo Thiệp Với Mẫu Này</span>
                        </a>

                        <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" rel="noopener noreferrer" class="mayhen_template_detail_btn_secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="10 8 16 12 10 16 10 8"></polygon>
                            </svg>
                            <span>Xem Thử Trực Tiếp</span>
                        </a>
                    </div>

                    {{-- Cam kết chất lượng dịch vụ --}}
                    <div class="mayhen_template_detail_trust_row">
                        <div class="mayhen_template_detail_trust_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Chỉnh sửa ảnh, nhạc và thông tin không giới hạn</span>
                        </div>
                        <div class="mayhen_template_detail_trust_item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Xem trước thời gian thực (Live Preview)</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Section: Trải nghiệm khách mời (The Guest Experience) - Điểm then chốt thúc đẩy chuyển đổi --}}
        <section class="mayhen_template_detail_experience_section">
            <div class="mayhen_template_detail_section_header">
                <span class="mayhen_template_detail_section_kicker">Cảm xúc đắt giá</span>
                <h2 class="mayhen_template_detail_section_title">Trải Nghiệm Khách Mời Của Bạn Sẽ Nhận Được</h2>
                <p class="mayhen_template_detail_section_subtitle">Không chỉ là một tấm thiệp điện tử vô hồn, đây là cánh cửa đón tiếp trang trọng gửi gắm trọn vẹn tình cảm của hai bạn.</p>
            </div>

            <div class="mayhen_template_detail_experience_grid">
                <div class="mayhen_template_detail_experience_card">
                    <div class="mayhen_template_detail_experience_num">01</div>
                    <div class="mayhen_template_detail_experience_icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <h3 class="mayhen_template_detail_experience_name">Chạm Mở Phong Thư Sang Trọng</h3>
                    <p class="mayhen_template_detail_experience_text">Khách mời nhẹ nhàng chạm vào tem sáp dập nổi để mở phong thư, âm nhạc vang lên du dương mang lại cảm giác được trân quý đặc biệt.</p>
                </div>

                <div class="mayhen_template_detail_experience_card">
                    <div class="mayhen_template_detail_experience_num">02</div>
                    <div class="mayhen_template_detail_experience_icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                    <h3 class="mayhen_template_detail_experience_name">Chiêm Ngưỡng Chuyện Tình Của Hai Bạn</h3>
                    <p class="mayhen_template_detail_experience_text">Album ảnh cưới độ nét cao hiển thị chuẩn màu sắc, timeline lịch trình ngày cưới và bản đồ Google Maps chỉ đường chỉ với 1 chạm.</p>
                </div>

                <div class="mayhen_template_detail_experience_card">
                    <div class="mayhen_template_detail_experience_num">03</div>
                    <div class="mayhen_template_detail_experience_icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </div>
                    <h3 class="mayhen_template_detail_experience_name">Xác Nhận Tham Dự & Mừng Cưới Tinh Tế</h3>
                    <p class="mayhen_template_detail_experience_text">Hệ thống form RSVP gửi thẳng lời chúc về tài khoản của bạn, kèm mã VietQR tự động điền số tài khoản để khách gửi gắm yêu thương dễ dàng.</p>
                </div>
            </div>
        </section>

        {{-- Section SEO Narrative: Câu chuyện thiết kế & Lợi ích độc bản (SEO Content Article) --}}
        <section class="mayhen_template_detail_seo_story_section">
            <div class="mayhen_template_detail_seo_story_container">
                <div class="mayhen_template_detail_seo_story_header">
                    <span class="mayhen_template_detail_section_kicker">Góc nhìn thiết kế</span>
                    <h2 class="mayhen_template_detail_seo_story_title">Tại Sao Mẫu Thiệp Cưới {{ $template->name }} Lại Được Yêu Thích?</h2>
                </div>
                
                <div class="mayhen_template_detail_seo_story_content">
                    <p>
                        Ngày cưới là cột mốc thiêng liêng nhất của đời người, và chiếc thiệp mời chính là lời chào trang trọng đầu tiên gửi tới gia đình, người thân và bạn bè hai bên. Khác với thiệp giấy truyền thống dễ thất lạc hoặc thiệp mời thông thường đơn điệu, <strong>mẫu thiệp cưới online {{ $template->name }}</strong> tại <strong>{{ config('app.name') }}</strong> được định hình theo trường phái <em>Quiet Luxury</em> — vẻ đẹp sang trọng đến từ sự tinh giản, trật tự thị giác và cảm xúc thăng hoa.
                    </p>
                    
                    <h3>1. Tối ưu hoàn hảo cho thời đại số & Thiết bị di động</h3>
                    <p>
                        100% khách mời hiện nay tiếp nhận lời mời cưới qua điện thoại thông minh (Zalo, Facebook, Tin nhắn). Mẫu <strong>{{ $template->name }}</strong> được kiến tạo với công nghệ hiển thị responsive vượt trội, tải trang thần tốc dưới 0.8 giây, đảm bảo màu sắc ảnh cưới luôn rực rỡ và nắp phong bì chuyển động mượt mà mà không làm tiêu tốn dung lượng của khách mời.
                    </p>

                    <h3>2. Tiết kiệm 80% chi phí in ấn và gửi thiệp xa</h3>
                    <p>
                        Thay vì phải in hàng trăm bộ thiệp đắt đỏ và lo lắng việc chuyển phát xa bị trễ lịch trình, bạn chỉ cần tạo thiệp một lần, lấy liên kết và gửi ngay tới bạn bè ở xa, ở nước ngoài trong vòng một nốt nhạc. Mọi lời chúc và phản hồi dự tiệc (RSVP) đều được lưu trữ trực quan trong tài khoản quản trị của bạn.
                    </p>

                    <h3>3. Hộp mừng cưới thông minh & Bảo mật</h3>
                    <p>
                        Tính năng quét mã VietQR tự động hiển thị số tài khoản ngân hàng và cú pháp chúc mừng giúp khách mời dễ dàng gửi quà mừng cưới tinh tế, văn minh, phù hợp với xu hướng cưới hiện đại mà vẫn giữ trọn sự trang trọng.
                    </p>
                </div>
            </div>
        </section>

        {{-- Section: Câu hỏi thường gặp (FAQ Accordion Schema) --}}
        <section class="mayhen_template_detail_faq_section">
            <div class="mayhen_template_detail_section_header">
                <span class="mayhen_template_detail_section_kicker">Giải đáp thắc mắc</span>
                <h2 class="mayhen_template_detail_section_title">Câu Hỏi Thường Gặp Về Mẫu Thiệp {{ $template->name }}</h2>
                <p class="mayhen_template_detail_section_subtitle">Tất cả những thông tin bạn cần biết trước khi bắt đầu tạo thiệp cưới</p>
            </div>

            <div class="mayhen_template_detail_faq_list">
                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">1. Mẫu thiệp cưới {{ $template->name }} có hiển thị đẹp trên điện thoại không?</h3>
                    <p class="mayhen_template_detail_faq_a">Hoàn toàn có. Mọi chi tiết từ bìa thiệp, phong bì gập mở đến album ảnh và bản đồ đều được tối ưu hóa chuẩn xác cho cả iPhone và tất cả dòng máy Android, đảm bảo trải nghiệm mở thiệp sắc nét tuyệt đối.</p>
                </div>

                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">2. Tôi có thể thay đổi ảnh cưới, bài hát và thông tin sau khi tạo không?</h3>
                    <p class="mayhen_template_detail_faq_a">Có. Bạn có thể tự do thay đổi ảnh cưới, ngày giờ, địa chỉ tiệc cưới, nhạc nền và tài khoản ngân hàng mừng cưới bất cứ lúc nào trong tài khoản mà không làm thay đổi đường link gửi thiệp.</p>
                </div>

                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">3. Tôi có thể gửi thiệp có tên riêng của từng vị khách không?</h3>
                    <p class="mayhen_template_detail_faq_a">Hoàn toàn được. Hệ thống quản lý khách mời của Mây Hẹn hỗ trợ tạo danh sách và sinh ra các đường link thiệp riêng biệt đính kèm tên từng khách mời (ví dụ: Kính mời Anh Hoàng & Người Thương) để thể hiện sự chu đáo tối đa.</p>
                </div>

                <div class="mayhen_template_detail_faq_item">
                    <h3 class="mayhen_template_detail_faq_q">4. Thời gian tạo và hoàn thiện một bộ thiệp cưới mất bao lâu?</h3>
                    <p class="mayhen_template_detail_faq_a">Chỉ mất từ 3 đến 5 phút thao tác với giao diện biên tập trực quan. Bạn có thể xem trước kết quả thời gian thực (Live Preview) trên màn hình trước khi gửi đến bạn bè.</p>
                </div>
            </div>
        </section>

        {{-- Section: Các mẫu cùng phong cách (Related Templates - Internal Linking) --}}
        @if(isset($similarTemplates) && $similarTemplates->count() > 0)
            <section class="mayhen_template_detail_related_section">
                <div class="mayhen_template_detail_section_header">
                    <span class="mayhen_template_detail_section_kicker">Gợi ý dành riêng cho bạn</span>
                    <h2 class="mayhen_template_detail_section_title">Các Mẫu Thiệp Cùng Phong Cách</h2>
                    <p class="mayhen_template_detail_section_subtitle">Khám phá thêm các thiết kế thiệp cưới được yêu thích khác trong cùng bộ sưu tập</p>
                </div>

                <div class="mayhen_template_detail_related_grid">
                    @foreach($similarTemplates as $related)
                        <div class="mayhen_template_detail_related_card">
                            <a href="{{ route('templates.show', $related->slug) }}" class="mayhen_template_detail_related_thumb">
                                <img src="{{ $related->thumbnail_url ?: asset('assets/client/images/template-fallback.svg') }}" alt="Mẫu thiệp cưới {{ $related->name }}" loading="lazy">
                                <div class="mayhen_template_detail_related_badge">
                                    {{ match ($related->tier ?? 'free') { 'pro' => 'VIP Pro', 'plus' => 'Plus', default => 'Free' } }}
                                </div>
                            </a>
                            <div class="mayhen_template_detail_related_body">
                                <h3 class="mayhen_template_detail_related_name">
                                    <a href="{{ route('templates.show', $related->slug) }}">{{ $related->name }}</a>
                                </h3>
                                <div class="mayhen_template_detail_related_footer">
                                    <span class="mayhen_template_detail_related_category">{{ $related->category_label ?? 'Thiệp cưới' }}</span>
                                    <a href="{{ route('templates.show', $related->slug) }}" class="mayhen_template_detail_related_link">
                                        Chi tiết
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Thanh CTA neo ở cuối trang (Bottom Conversion Banner) --}}
        <section class="mayhen_template_detail_bottom_banner">
            <div class="mayhen_template_detail_bottom_banner_content">
                <div class="mayhen_template_detail_bottom_banner_txt">
                    <h3 class="mayhen_template_detail_bottom_banner_title">Sẵn sàng khởi tạo thiệp cưới độc bản của hai bạn?</h3>
                    <p class="mayhen_template_detail_bottom_banner_desc">Thao tác chỉnh sửa trực quan chỉ mất 3 phút, lưu lại ngay trong tài khoản và chia sẻ bất cứ lúc nào.</p>
                </div>
                <div class="mayhen_template_detail_bottom_banner_btn">
                    <a href="{{ route('invitation.create', ['template' => $template->slug]) }}" class="mayhen_template_detail_btn_primary">
                        <span>Tạo Thiệp Với Mẫu Này</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
