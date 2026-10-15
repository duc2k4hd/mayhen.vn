@extends('client.layouts.master')

@section('title', 'Bộ Sưu Tập Mẫu Thiệp Cưới Online — Mây Hẹn')
@section('meta_description', 'Khám phá các mẫu thiệp cưới online phong cách Quiet Luxury, Editorial thời trang, tối giản tinh tế với hiệu ứng chuyển động mượt mà.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/templates.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container">
        <!-- Header -->
        <header class="mayhen_templates_header">
            <span class="mayhen_templates_kicker">Bộ Sưu Tập Thiệp Cưới Điện Tử 2026</span>
            <h1 class="mayhen_templates_title">Mẫu Thiệp Cưới Nghệ Thuật</h1>
            <p class="mayhen_templates_desc">
                Đa dạng phong cách từ tinh gọn setup siêu tốc 2 phút đến phong thư hoàng gia tem sáp, tạp chí thời trang và passport. Tự tạo & xem thử miễn phí trực tiếp trên di động.
            </p>
        </header>

        <!-- Category Filter Bar -->
        <nav class="mayhen_templates_filter_bar" aria-label="Bộ lọc mẫu thiệp theo phong cách">
            <a href="{{ route('templates.index', array_filter(['tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ empty($currentCategory) || $currentCategory === 'all' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tất Cả Phong Cách
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'minimal', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'minimal' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tối Giản
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'floral', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'floral' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Hoa Lá
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'traditional', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'traditional' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Truyền Thống
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'luxury', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'luxury' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Sang Trọng Tem Sáp
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'editorial', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'editorial' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tạp Chí Editorial
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'modern', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'modern' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Hiện Đại
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'creative', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'creative' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Độc Đáo (Passport/Lá Thư)
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => 'cute', 'tier' => $currentTier, 'sort' => $currentSort])) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'cute' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Dễ Thương (Chibi)
            </a>
        </nav>

        <!-- Tier Filter Bar (Phân loại gói dịch vụ) -->
        <div class="mayhen_templates_tier_bar" aria-label="Lọc theo gói dịch vụ">
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'sort' => $currentSort])) }}" class="mayhen_templates_tier_pill {{ empty($currentTier) ? 'mayhen_templates_tier_pill_active' : '' }}">
                Tất cả gói
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => 'free', 'sort' => $currentSort])) }}" class="mayhen_templates_tier_pill {{ $currentTier === 'free' ? 'mayhen_templates_tier_pill_active' : '' }}">
                Gói Miễn Phí (Setup 2 phút)
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => 'plus', 'sort' => $currentSort])) }}" class="mayhen_templates_tier_pill {{ $currentTier === 'plus' ? 'mayhen_templates_tier_pill_active' : '' }}">
                Gói Plus (Hoa lá & Album ảnh)
            </a>
            <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => 'pro', 'sort' => $currentSort])) }}" class="mayhen_templates_tier_pill {{ $currentTier === 'pro' ? 'mayhen_templates_tier_pill_active' : '' }}">
                Gói Pro VIP (Tem sáp & Tạp chí)
            </a>
        </div>

        <!-- Meta Bar -->
        <div class="mayhen_templates_meta_bar">
            <span>Hiển thị <strong>{{ $templates->count() }}</strong> / {{ $totalCount }} mẫu thiệp cưới</span>
            <div class="mayhen_templates_sort_group">
                <span>Sắp xếp:</span>
                <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => $currentTier, 'sort' => 'featured'])) }}" class="mayhen_templates_sort_link {{ empty($currentSort) || $currentSort === 'featured' ? 'mayhen_templates_sort_link_active' : '' }}">
                    Nổi bật
                </a>
                <span>&bull;</span>
                <a href="{{ route('templates.index', array_filter(['category' => $currentCategory, 'tier' => $currentTier, 'sort' => 'popular'])) }}" class="mayhen_templates_sort_link {{ $currentSort === 'popular' ? 'mayhen_templates_sort_link_active' : '' }}">
                    Chuộng nhất
                </a>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="mayhen_templates_grid">
            @forelse ($templates as $tpl)
                <article class="mayhen_home_template_card">
                    <div class="mayhen_home_template_card_img_wrap">
                        <img src="{{ $tpl->image }}" alt="{{ $tpl->name }}" class="mayhen_home_template_card_img" loading="lazy">
                        <div class="mayhen_home_template_card_badge_group">
                            <span class="mayhen_home_template_card_tier_badge {{ $tpl->tier_badge_class }}">
                                {{ $tpl->tier_label }}
                            </span>
                            <span class="mayhen_home_template_card_setup_badge">
                                {{ $tpl->complexity_label }}
                            </span>
                        </div>
                    </div>
                    <div class="mayhen_home_template_card_body">
                        <span class="mayhen_home_template_card_category">{{ $tpl->category_label }}</span>
                        <h2 class="mayhen_home_template_card_name">{{ $tpl->name }}</h2>
                        <p class="mayhen_home_template_card_desc">{{ $tpl->description }}</p>

                        <div class="mayhen_home_template_card_stats">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>{{ number_format($tpl->usage_count) }} cặp đôi đã dùng</span>
                        </div>

                        <div class="mayhen_home_template_card_footer">
                            <a href="{{ route('templates.preview', $tpl->slug) }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_sm">
                                Xem Live
                            </a>
                            <a href="{{ route('templates.show', $tpl->slug) }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_sm">
                                Dùng Mẫu
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem;">
                    <p style="color: var(--mayhen-color-text-muted); margin-bottom: 1rem;">Không tìm thấy mẫu thiệp phù hợp trong tiêu chí lọc này.</p>
                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_secondary">Xem tất cả 50 mẫu</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
