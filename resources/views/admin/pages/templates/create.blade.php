@extends('admin.layouts.master')

@section('title', 'Thêm Mẫu Thiệp Mới')
@section('page_title', 'Khởi Tạo Mẫu Thiệp Mới (Template Engine)')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/admin/css/pages/templates.css') }}">
@endpush

@section('content')
<div class="mayhen_admin_page" style="max-width: 1040px; margin: 0 auto;">
    {{-- Header & Navigation --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / 
                <a href="{{ route('admin.templates.index') }}" style="color: inherit; text-decoration: none;">Kho Mẫu Thiệp</a> / 
                <span>Thêm Mẫu Mới</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Thêm Mẫu Thiệp Cưới Mới
            </h1>
        </div>
        <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            &larr; Quay lại danh sách
        </a>
    </div>

    @if ($errors->any())
        <div style="padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; margin-bottom: 20px; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.templates.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mayhen_admin_card" style="padding: 28px; background: #FFFFFF; border: 1px solid var(--mh-admin-border); border-radius: var(--mh-admin-radius); box-shadow: var(--mh-admin-shadow-sm);">
            
            <div class="mayhen_admin_group_heading">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                <span>1. Thông Tin Định Danh & Phân Hạng</span>
            </div>

            <div class="mayhen_admin_form_grid_2" style="margin-top: 16px;">
                {{-- Template Name --}}
                <div class="mayhen_admin_form_group">
                    <label class="mayhen_admin_form_label">
                        Tên Mẫu Thiệp Cưới <span style="color: #EF4444;">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="template_name_input"
                        value="{{ old('name') }}" 
                        class="mayhen_admin_form_input" 
                        placeholder="Ví dụ: Hoàng Gia Tem Sáp Ánh Kim" 
                        required
                        oninput="autoGenerateSlug(this.value)"
                    >
                </div>

                {{-- Template Slug --}}
                <div class="mayhen_admin_form_group">
                    <label class="mayhen_admin_form_label">
                        Mã Định Danh (Slug URL)
                    </label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="template_slug_input"
                        value="{{ old('slug') }}" 
                        class="mayhen_admin_form_input" 
                        placeholder="tu-dong-sinh-neu-de-trong"
                    >
                    <small style="color: #64748B; font-size: 0.75rem; display: block; margin-top: 4px;">Tên thư mục view & URL xem thử (/xem-thu/slug).</small>
                </div>
            </div>

            <div class="mayhen_admin_form_grid_2" style="margin-top: 14px;">
                {{-- Category --}}
                <div class="mayhen_admin_form_group">
                    <label class="mayhen_admin_form_label">
                        Phong Cách Thiết Kế <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="category" class="mayhen_admin_form_select" required>
                        @foreach ($availableCategories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ old('category') === $catKey ? 'selected' : '' }}>
                                {{ $catLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tier --}}
                <div class="mayhen_admin_form_group">
                    <label class="mayhen_admin_form_label">
                        Phân Hạng Gói Dịch Vụ <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="tier" class="mayhen_admin_form_select" required>
                        @foreach ($availableTiers as $tierKey => $tierLabel)
                            <option value="{{ $tierKey }}" {{ old('tier', 'plus') === $tierKey ? 'selected' : '' }}>
                                {{ $tierLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Description --}}
            <div class="mayhen_admin_form_group" style="margin-top: 14px;">
                <label class="mayhen_admin_form_label">Mô Tả Giới Thiệu Ngắn</label>
                <textarea 
                    name="description" 
                    rows="2" 
                    class="mayhen_admin_form_input" 
                    placeholder="Mẫu thiệp phong cách Quiet Luxury dành riêng cho những hôn lễ trang nhã..."
                >{{ old('description') }}</textarea>
            </div>

            {{-- 2. Assets & Previews --}}
            <div class="mayhen_admin_group_heading" style="margin-top: 32px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span>2. Hình Ảnh Đại Diện & Ảnh Cuộn Toàn Trang</span>
            </div>

            {{-- Thumbnail Cover --}}
            <div class="mayhen_admin_form_group" style="margin-top: 16px;">
                <label class="mayhen_admin_form_label">
                    Ảnh Đại Diện Mẫu (Thumbnail Cover)
                </label>
                <div class="mayhen_admin_asset_field_box">
                    <img src="/storage/images/client/business/mayhen-favicon.png" alt="Thumbnail" class="mayhen_admin_asset_thumb" id="create_preview_thumb_img">
                    <div class="mayhen_admin_asset_controls">
                        <input 
                            type="text" 
                            name="thumbnail_url" 
                            id="create_thumbnail_url_input" 
                            value="{{ old('thumbnail_url') }}" 
                            class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                            placeholder="Đường dẫn ảnh hoặc tải tệp lên bên dưới"
                            onchange="updateThumbFromUrl(this.value)"
                        >
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <label class="mayhen_admin_file_upload_btn">
                                <input type="file" name="thumbnail_file" accept="image/*" style="display: none;" onchange="previewCreateThumb(this)">
                                <span>📁 Tải Ảnh Mới</span>
                            </label>
                            <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Full Page Scroll Preview --}}
            <div class="mayhen_admin_form_group" style="margin-top: 20px;">
                <label class="mayhen_admin_form_label" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                    <span>Ảnh Chụp Toàn Mẫu Để Cuộn (Full Page Scroll Preview)</span>
                    <span style="font-size: 0.75rem; color: #7A756E; font-weight: normal;">Dùng cho hiệu ứng cuộn tự động trên Trang Chủ & Kho Mẫu</span>
                </label>

                <div class="mayhen_admin_full_scroll_preview_box">
                    <div class="mayhen_admin_phone_mockup_scroll" id="create_phone_mockup" title="Rê chuột hoặc bấm 'Cuộn Thử' để xem hiệu ứng cuộn mượt mà">
                        <div class="mayhen_admin_phone_mockup_speaker"></div>
                        <div class="mayhen_admin_phone_mockup_inner" id="create_phone_inner">
                            <img src="/storage/images/client/business/mayhen-favicon.png" alt="Full Page Scroll Preview" class="mayhen_admin_phone_mockup_img" id="create_preview_full_page_img">
                        </div>
                        <div class="mayhen_admin_phone_mockup_badge">Scroll Preview</div>
                    </div>

                    <div class="mayhen_admin_asset_controls" style="flex: 1;">
                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                            <input 
                                type="text" 
                                name="full_page_preview_url" 
                                id="create_full_preview_url" 
                                value="{{ old('full_page_preview_url') }}" 
                                class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                                placeholder="Đường dẫn ảnh dài toàn trang (/storage/... hoặc URL) hoặc tải tệp bên dưới"
                                onchange="updateCreateFullPreview(this.value)"
                            >
                            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="toggleCreateScrollSim()" style="padding: 6px 12px; font-size: 0.75rem; white-space: nowrap;">
                                <span id="create_scroll_sim_btn_text">▶ Cuộn Thử</span>
                            </button>
                        </div>

                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <label class="mayhen_admin_file_upload_btn">
                                <input type="file" name="full_page_preview_file" accept="image/*" style="display: none;" onchange="previewCreateFullPage(this)">
                                <span>📁 Tải Ảnh Cuộn Toàn Trang (Tối đa 25MB)</span>
                            </label>
                            <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                        </div>

                        <div style="font-size: 0.75rem; color: #64748B; line-height: 1.5; margin-top: 6px;">
                            💡 <strong>Mẹo:</strong> Ảnh cuộn toàn trang (Full Page Scroll) là ảnh chụp toàn bộ các section từ đầu đến cuối của mẫu thiệp (khuyến nghị định dạng WebP, chiều rộng khoảng 480px - 900px, chiều dài từ 2.000px đến 10.000px). Khi khách xem trên Trang Chủ hoặc Kho Mẫu, ảnh sẽ tự động trôi mượt mà mô phỏng màn hình điện thoại thật.
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Status --}}
            <div style="display: flex; gap: 24px; margin-top: 24px; padding: 14px 16px; background-color: #F8FAFC; border-radius: var(--mh-admin-radius-sm); border: 1px solid var(--mh-admin-border);">
                <label class="mayhen_admin_checkbox_label">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', '1') ? 'checked' : '' }}
                    >
                    <span>Kích hoạt hiển thị cho khách tạo thiệp ngay</span>
                </label>

                <label class="mayhen_admin_checkbox_label">
                    <input 
                        type="checkbox" 
                        name="is_featured" 
                        value="1" 
                        {{ old('is_featured') ? 'checked' : '' }}
                    >
                    <span>Ghim mẫu nổi bật lên Trang Chủ</span>
                </label>
            </div>

            {{-- Action Buttons --}}
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--mh-admin-border);">
                <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                    Hủy Bỏ
                </a>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Tạo Mẫu & Chuyển Đến Chỉnh Sửa Chi Tiết</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function autoGenerateSlug(name) {
        const slugInput = document.getElementById('template_slug_input');
        if (!slugInput || slugInput.dataset.manualEdited === 'true') return;
        
        let slug = name.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[đĐ]/g, 'd')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
            
        slugInput.value = slug;
    }

    document.getElementById('template_slug_input')?.addEventListener('input', function() {
        this.dataset.manualEdited = this.value.trim() !== '' ? 'true' : 'false';
    });

    function previewCreateThumb(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('create_preview_thumb_img');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateThumbFromUrl(url) {
        const img = document.getElementById('create_preview_thumb_img');
        if (img && url.trim() !== '') img.src = url.trim();
    }

    function previewCreateFullPage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('create_preview_full_page_img');
                if (img) {
                    img.src = e.target.result;
                    img.onload = function() {
                        setupPhoneMockupScrollSpeed(this);
                    };
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCreateFullPreview(url) {
        const img = document.getElementById('create_preview_full_page_img');
        if (img && url.trim() !== '') {
            img.src = url.trim();
            img.onload = function() {
                setupPhoneMockupScrollSpeed(this);
            };
        }
    }

    let isCreateMockupScrolling = false;
    function toggleCreateScrollSim() {
        const mockup = document.getElementById('create_phone_mockup');
        const btnText = document.getElementById('create_scroll_sim_btn_text');
        if (!mockup) return;

        isCreateMockupScrolling = !isCreateMockupScrolling;
        if (isCreateMockupScrolling) {
            mockup.classList.add('is-animating');
            if (btnText) btnText.textContent = '⏸ Dừng';
        } else {
            mockup.classList.remove('is-animating');
            if (btnText) btnText.textContent = '▶ Cuộn Thử';
        }
    }

    function setupPhoneMockupScrollSpeed(img) {
        const mockupInner = document.getElementById('create_phone_inner');
        if (!img || !mockupInner) return;
        const naturalH = img.naturalHeight || 0;
        const naturalW = img.naturalWidth || 1;
        const innerW = mockupInner.clientWidth || 110;
        const computedH = innerW * (naturalH / naturalW);
        const innerH = mockupInner.clientHeight || 190;
        const delta = Math.max(0, computedH - innerH);
        
        const duration = Math.max(8, Math.min(60, delta / 120));
        img.style.setProperty('--mockup-scroll-y', `-${delta}px`);
        img.style.setProperty('--mockup-scroll-dur', `${duration.toFixed(1)}s`);
    }
</script>
@endsection
