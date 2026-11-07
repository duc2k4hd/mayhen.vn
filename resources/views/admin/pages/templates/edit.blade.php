@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Mẫu: ' . $template->name . ' — Mây Hẹn')
@section('page_title', 'Chỉnh Sửa Mẫu & Soát Schema: ' . $template->name)

@section('breadcrumb_parent')
    <a href="{{ route('admin.templates.index') }}">Kho Mẫu Thiệp</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/templates.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_page_title">
                <span>{{ $template->name }}</span>
                @if ($template->is_active)
                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đang Hoạt Động</span>
                @else
                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Tạm Ẩn</span>
                @endif
                @if ($template->is_featured)
                    <span class="mayhen_admin_badge mayhen_admin_badge_warning" style="background-color: #FEF3C7; color: #92400E;">★ Nổi Bật</span>
                @endif
            </div>
            <div class="mayhen_admin_page_subtitle">
                Mã định danh kỹ thuật: <code>{{ $template->slug }}</code> &bull; Số thiệp đang sử dụng: <strong>{{ $template->invitations_count }}</strong> thiệp
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Quay Lại Danh Sách
            </a>
            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_primary">
                Xem Thử Giao Diện &nearr;
            </a>
        </div>
    </div>

    <!-- 2 Columns Layout: Form Left | Schema Inspector Right -->
    <div class="mayhen_admin_tpl_edit_layout">
        <!-- Left: Template Edit Form -->
        <div class="mayhen_admin_card">
            <div class="mayhen_admin_card_header">
                <div class="mayhen_admin_card_title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Cấu Hình Thông Tin Mẫu Thiệp</span>
                </div>
            </div>

            <div class="mayhen_admin_card_body">
                <form action="{{ route('admin.templates.update', $template->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Template Name -->
                    <div class="mayhen_admin_form_group">
                        <label for="name" class="mayhen_admin_form_label">
                            Tên Mẫu Thiệp Cưới Tiếng Việt <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', $template->name) }}" 
                            class="mayhen_admin_form_input" 
                            required
                        >
                        <div class="mayhen_admin_form_help">Tên gọi trang trọng xuất hiện trên trang chủ và kho thư viện mẫu.</div>
                    </div>

                    <!-- Description / Slogan -->
                    <div class="mayhen_admin_form_group">
                        <label for="description" class="mayhen_admin_form_label">
                            Mô Tả Phong Cách / Slogan Thiệp
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="mayhen_admin_form_textarea"
                        >{{ old('description', $template->description) }}</textarea>
                        <div class="mayhen_admin_form_help">Đoạn văn ngắn làm nổi bật nét độc bản của mẫu thiệp cưới này.</div>
                    </div>

                    <!-- Category & Tier Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Category -->
                        <div class="mayhen_admin_form_group">
                            <label for="category" class="mayhen_admin_form_label">
                                Danh Mục Phong Cách <span class="required">*</span>
                            </label>
                            <select id="category" name="category" class="mayhen_admin_form_select" required>
                                <option value="luxury" {{ old('category', $template->category) === 'luxury' ? 'selected' : '' }}>Hoàng Gia Tem Sáp (Luxury)</option>
                                <option value="modern" {{ old('category', $template->category) === 'modern' ? 'selected' : '' }}>Hiện Đại Sắc Nét (Modern)</option>
                                <option value="traditional" {{ old('category', $template->category) === 'traditional' ? 'selected' : '' }}>Truyền Thống Á Đông (Traditional)</option>
                                <option value="floral" {{ old('category', $template->category) === 'floral' ? 'selected' : '' }}>Hoa Lá Thiên Nhiên (Floral)</option>
                                <option value="minimal" {{ old('category', $template->category) === 'minimal' ? 'selected' : '' }}>Tối Giản Tinh Tế (Minimal)</option>
                                <option value="editorial" {{ old('category', $template->category) === 'editorial' ? 'selected' : '' }}>Tạp Chí Editorial (Editorial)</option>
                                <option value="romantic" {{ old('category', $template->category) === 'romantic' ? 'selected' : '' }}>Lãng Mạn Pastel (Romantic)</option>
                            </select>
                        </div>

                        <!-- Tier -->
                        <div class="mayhen_admin_form_group">
                            <label for="tier" class="mayhen_admin_form_label">
                                Hạng Gói Bản Quyền <span class="required">*</span>
                            </label>
                            @php $currentTier = old('tier', $template->tier); @endphp
                            <select id="tier" name="tier" class="mayhen_admin_form_select" required>
                                <option value="free" {{ $currentTier === 'free' ? 'selected' : '' }}>Gói Miễn Phí (Free)</option>
                                <option value="plus" {{ $currentTier === 'plus' ? 'selected' : '' }}>Gói Hạnh Phúc (Plus - Mặc định)</option>
                                <option value="pro" {{ $currentTier === 'pro' ? 'selected' : '' }}>Gói Cao Cấp VIP (Pro)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Thumbnail URL -->
                    <div class="mayhen_admin_form_group">
                        <label for="thumbnail_url" class="mayhen_admin_form_label">
                            Đường Dẫn Ảnh Đại Diện (Thumbnail)
                        </label>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <img src="{{ $template->thumbnail_url }}" alt="Thumbnail preview" style="width: 48px; height: 48px; border-radius: 6px; object-fit: cover; border: 1px solid var(--mh-admin-border);">
                            <input 
                                type="text" 
                                id="thumbnail_url" 
                                name="thumbnail_url" 
                                value="{{ old('thumbnail_url', $template->thumbnail_url) }}" 
                                class="mayhen_admin_form_input" 
                                style="flex: 1;"
                            >
                        </div>
                        <div class="mayhen_admin_form_help">Đường dẫn nội bộ trỏ vào file ảnh đại diện WebP trong thư mục assets của mẫu.</div>
                    </div>

                    <!-- Checkboxes: Active & Featured -->
                    <div style="display: flex; gap: 24px; margin-bottom: 24px; padding: 14px 16px; background-color: var(--mh-admin-bg); border-radius: var(--mh-admin-radius-sm); border: 1px solid var(--mh-admin-border-subtle);">
                        <label class="mayhen_admin_checkbox_label">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                            >
                            <span>Kích hoạt hiển thị cho khách tạo thiệp</span>
                        </label>

                        <label class="mayhen_admin_checkbox_label">
                            <input 
                                type="checkbox" 
                                name="is_featured" 
                                value="1" 
                                {{ old('is_featured', $template->is_featured) ? 'checked' : '' }}
                            >
                            <span>Ghim mẫu nổi bật lên Trang Chủ</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                            <span>Lưu Cập Nhật</span>
                        </button>
                        <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                            <span>Hủy Bỏ</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Schema Validator Inspector Card -->
        <div class="mayhen_admin_schema_card">
            <!-- Status Banner -->
            <div class="mayhen_admin_schema_status_banner {{ $isCanonicalValid ? 'valid' : 'invalid' }}">
                @if ($isCanonicalValid)
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>
                        <strong style="font-size: 0.875rem;">Đạt Chuển 10/10 Section Tiêu Chuẩn</strong><br>
                        <span style="font-size: 0.75rem;">Schema hợp lệ tuyệt đối theo chuẩn Studio Mây Hẹn.</span>
                    </div>
                @else
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong style="font-size: 0.875rem;">Chưa Đủ 10 Section Tiêu Chuẩn</strong><br>
                        <span style="font-size: 0.75rem;">Cần bổ sung các section còn thiếu để hiển thị trơn tru.</span>
                    </div>
                @endif
            </div>

            <!-- Schema Details -->
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border-subtle); font-size: 0.75rem; color: #7A756E;">
                Tệp cấu hình: <code>resources/views/client/invitations/{{ $template->slug }}/schema.json</code><br>
                Trạng thái tệp: <strong>{{ $schemaExists ? 'Đã tồn tại' : 'Chưa có file JSON' }}</strong> &bull; Album 10 ảnh: <strong>{{ $galleryValid ? 'Đã chuẩn hóa' : 'Chưa có field gallery' }}</strong>
            </div>

            <!-- Checklist of 10 Canonical Sections -->
            <div class="mayhen_admin_section_list">
                @foreach ($sectionsReport as $sec)
                    <div class="mayhen_admin_section_item {{ $sec['passed'] ? 'passed' : 'missing' }}">
                        <span class="mayhen_admin_section_title">{{ $sec['title'] }}</span>
                        @if ($sec['passed'])
                            <span class="mayhen_admin_section_badge" style="background-color: #E8F5E9; color: #1B5E20;">✓ Đạt chuẩn</span>
                        @else
                            <span class="mayhen_admin_section_badge" style="background-color: #FFEBEE; color: #C62828;">✗ Còn thiếu</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

