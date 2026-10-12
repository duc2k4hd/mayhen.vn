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
            <h1 class="mayhen_templates_title">Kho Mẫu Thiệp Nghệ Thuật</h1>
            <p class="mayhen_templates_desc">
                Chọn phong cách phù hợp với câu chuyện tình yêu của hai bạn. Tất cả mẫu đều tối ưu hoàn hảo trên điện thoại di động.
            </p>
        </header>

        <!-- Filter Bar -->
        <nav class="mayhen_templates_filter_bar" aria-label="Bộ lọc mẫu thiệp">
            <a href="{{ route('templates.index') }}" class="mayhen_templates_filter_pill {{ empty($currentCategory) ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tất Cả Mẫu
            </a>
            <a href="{{ route('templates.index', ['category' => 'luxury']) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'luxury' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Quiet Luxury & Phong Thư
            </a>
            <a href="{{ route('templates.index', ['category' => 'editorial']) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'editorial' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tạp Chí Editorial
            </a>
            <a href="{{ route('templates.index', ['category' => 'minimal']) }}" class="mayhen_templates_filter_pill {{ $currentCategory === 'minimal' ? 'mayhen_templates_filter_pill_active' : '' }}">
                Tối Giản Tinh Tế
            </a>
        </nav>

        <!-- Templates Grid -->
        <div class="mayhen_templates_grid">
            @forelse ($templates as $tpl)
                <article class="mayhen_home_template_card">
                    <div class="mayhen_home_template_card_img_wrap">
                        <img src="{{ $tpl['image'] }}" alt="{{ $tpl['name'] }}" class="mayhen_home_template_card_img" loading="lazy">
                        <span class="mayhen_home_template_card_badge">{{ $tpl['badge'] }}</span>
                    </div>
                    <div class="mayhen_home_template_card_body">
                        <span class="mayhen_home_template_card_category">{{ $tpl['category_label'] }}</span>
                        <h2 class="mayhen_home_template_card_name">{{ $tpl['name'] }}</h2>
                        <p class="mayhen_home_template_card_desc">{{ $tpl['description'] }}</p>

                        <div class="mayhen_home_template_card_footer">
                            <a href="{{ route('templates.preview', $tpl['slug']) }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_sm">
                                Xem Live Demo
                            </a>
                            <a href="{{ route('templates.show', $tpl['slug']) }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_sm">
                                Chi Tiết & Tạo
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem;">
                    <p style="color: var(--mayhen-color-text-muted);">Không tìm thấy mẫu thiệp phù hợp trong danh mục này.</p>
                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_secondary">Xem tất cả mẫu</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
