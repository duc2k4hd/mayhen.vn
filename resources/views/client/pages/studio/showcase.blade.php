@extends('client.layouts.master')

@section('title', $partner->business_name . ' — Bộ Sưu Tập Thiệp Cưới Đặc Quyền | ' . config('app.name'))
@section('meta_description', 'Khám phá bộ sưu tập thiệp cưới trực tuyến Quiet Luxury tuyển chọn riêng bởi ' . $partner->business_name . ' và Mây Hẹn Atelier.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/studio.css') }}?v={{ file_exists(public_path('assets/client/css/pages/studio.css')) ? filemtime(public_path('assets/client/css/pages/studio.css')) : time() }}">
@endpush

@section('content')
<div class="mayhen_studio_wrapper">
    {{-- 1. Co-branded Hero Header --}}
    <header class="mayhen_studio_header">
        <div class="mayhen_studio_container">
            <div class="mayhen_studio_badge_co">
                <span>ĐỐI TÁC CHỨNG NHẬN</span>
                <span class="mayhen_studio_badge_dot"></span>
                <span>MÂY HẸN ATELIER</span>
            </div>

            <div class="mayhen_studio_brand_row">
                @if($partner->logo_url)
                    <div class="mayhen_studio_logo_wrap">
                        <img src="{{ $partner->logo_url }}" alt="Logo {{ $partner->business_name }}" class="mayhen_studio_logo">
                    </div>
                    <div class="mayhen_studio_cross">×</div>
                @endif
                <div class="mayhen_studio_atelier_mark">
                    <span class="mayhen_studio_atelier_title">MÂY HẸN</span>
                    <span class="mayhen_studio_atelier_sub">ATELIER WEDDING</span>
                </div>
            </div>

            <h1 class="mayhen_studio_title">{{ $partner->business_name }}</h1>
            
            <p class="mayhen_studio_bio">
                {{ $showcaseConfig['bio'] ?? 'Không gian nghệ thuật cưới tinh tế, nơi mỗi câu chuyện tình yêu được ghi dấu bằng những tác phẩm thiệp cưới cao cấp mang đậm dấu ấn cá nhân.' }}
            </p>

            {{-- Thông tin liên hệ Studio --}}
            <div class="mayhen_studio_meta_bar">
                @if($partner->phone)
                    <a href="tel:{{ $partner->phone }}" class="mayhen_studio_meta_item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ $partner->phone }}</span>
                    </a>
                @endif

                @if($partner->address)
                    <div class="mayhen_studio_meta_item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ $partner->address }}</span>
                    </div>
                @endif

                @if($partner->website)
                    <a href="{{ $partner->website }}" target="_blank" rel="noopener noreferrer" class="mayhen_studio_meta_item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        <span>Website Studio</span>
                    </a>
                @endif

                <button type="button" class="mayhen_studio_share_btn" id="btn-copy-studio-link" data-url="{{ url()->current() }}" title="Sao chép liên kết giới thiệu">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                    <span>Chia Sẻ Bộ Sưu Tập</span>
                </button>
            </div>
        </div>
    </header>

    {{-- 2. Exclusive Client Perks Banner --}}
    <section class="mayhen_studio_perks_section">
        <div class="mayhen_studio_container">
            <div class="mayhen_studio_perks_card">
                <div class="mayhen_studio_perks_icon">💎</div>
                <div class="mayhen_studio_perks_body">
                    <h3 class="mayhen_studio_perks_title">Đặc Quyền Dành Riêng Cho Khách Hàng Của {{ $partner->business_name }}</h3>
                    <p class="mayhen_studio_perks_desc">
                        {{ $showcaseConfig['exclusive_perk'] ?? 'Tặng trọn bộ thiệp cưới điện tử cao cấp, bảo chứng xác nhận tham dự thông minh và lưu niệm kỷ niệm trăm năm khi bạn khởi tạo thiệp qua liên kết đối tác chính thức.' }}
                    </p>
                </div>
                <div class="mayhen_studio_perks_code">
                    <span class="mayhen_studio_perks_code_label">MÃ ĐỐI TÁC:</span>
                    <strong class="mayhen_studio_perks_code_val">{{ $partner->partner_code }}</strong>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. Curated Templates Grid --}}
    <section class="mayhen_studio_gallery_section">
        <div class="mayhen_studio_container">
            <div class="mayhen_studio_section_header">
                <span class="mayhen_studio_section_sub">BỘ SƯU TẬP TUYỂN CHỌN</span>
                <h2 class="mayhen_studio_section_title">Các Mẫu Thiệp Khuyên Dùng Cho Mùa Cưới</h2>
                <div class="mayhen_studio_divider"></div>
            </div>

            <div class="mayhen_studio_grid">
                @forelse($templates as $template)
                    <article class="mayhen_studio_card">
                        <div class="mayhen_studio_card_media">
                            <img src="{{ $template->thumbnail_url ?: asset('assets/client/images/sample-wedding-cover.jpg') }}" alt="{{ $template->name }}" loading="lazy" class="mayhen_studio_card_img">
                            @if($template->is_featured)
                                <span class="mayhen_studio_card_tag">Tuyển Chọn</span>
                            @endif
                        </div>
                        <div class="mayhen_studio_card_info">
                            <span class="mayhen_studio_card_cat">{{ $template->category_label ?? $template->category }}</span>
                            <h3 class="mayhen_studio_card_name">{{ $template->name }}</h3>
                            <p class="mayhen_studio_card_desc">
                                {{ Str::limit($template->description ?: 'Thiết kế tinh xảo theo phong cách Quiet Luxury với hiệu ứng mở thiệp mượt mà và chuyển động lụa cao cấp.', 90) }}
                            </p>
                            <div class="mayhen_studio_card_actions">
                                <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="mayhen_studio_btn_preview">
                                    Xem Thử
                                </a>
                                <a href="{{ route('invitation.create', ['template' => $template->slug, 'ref' => $partner->partner_code]) }}" class="mayhen_studio_btn_create">
                                    <span>Tạo Thiệp Ngay</span>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="mayhen_studio_empty">
                        <p>Hiện chưa có mẫu thiệp nào trong bộ sưu tập. Vui lòng quay lại sau.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- 4. Studio Consultation & Booking Footer --}}
    <section class="mayhen_studio_contact_section">
        <div class="mayhen_studio_container">
            <div class="mayhen_studio_contact_box">
                <h3 class="mayhen_studio_contact_title">Cần Tư Vấn Thiết Kế Hoặc Đặt Lịch Chụp Cưới?</h3>
                <p class="mayhen_studio_contact_desc">
                    Đội ngũ của {{ $partner->business_name }} luôn sẵn sàng lắng nghe và đồng hành cùng hai bạn để chuẩn bị cho ngày trọng đại một cách chu đáo nhất.
                </p>
                <div class="mayhen_studio_contact_btns">
                    @if($partner->phone)
                        <a href="tel:{{ $partner->phone }}" class="mayhen_studio_contact_primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span>Gọi Hotline: {{ $partner->phone }}</span>
                        </a>
                    @endif
                    <a href="{{ route('templates.index') }}" class="mayhen_studio_contact_secondary">
                        <span>Khám Phá Tất Cả Mẫu Thiệp Mây Hẹn</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/studio.js') }}?v={{ file_exists(public_path('assets/client/js/pages/studio.js')) ? filemtime(public_path('assets/client/js/pages/studio.js')) : time() }}"></script>
@endpush

