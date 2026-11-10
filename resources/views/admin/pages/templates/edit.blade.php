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
            <a href="{{ route('admin.templates.release_lab', $template->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="color: var(--mh-admin-gold); font-weight: 600;">
                Release Lab 🧪
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
            <!-- Template Visual DNA Inspector (Innovation #10) -->
            @if(isset($visualDna))
                <div class="mayhen_admin_card" style="margin-bottom: 20px; background: #FFFDF9; border-left: 4px solid var(--mh-admin-gold); padding: 18px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 1.25rem;">🧬</span>
                                <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                    Template Visual DNA (Art Direction Audit)
                                </h3>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                Dấu vết thiết kế nguyên bản & độ hòa hợp nhận diện thương hiệu Quiet Luxury
                            </div>
                        </div>
                        <span class="mayhen_admin_badge {{ $visualDna['brand_harmony'] >= 80 ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}">
                            {{ $visualDna['brand_harmony'] }}/100 Harmony
                        </span>
                    </div>

                    <!-- DNA Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div style="background: rgba(255,255,255,0.7); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--mh-admin-border-subtle);">
                            <div style="font-size: 0.6875rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Font Tiêu Đề (Editorial Serif)</div>
                            <div style="font-size: 0.9375rem; font-weight: 700; color: var(--mh-admin-charcoal); font-family: 'Playfair Display', serif; margin-top: 2px;">
                                {{ $visualDna['heading_font'] }}
                            </div>
                        </div>

                        <div style="background: rgba(255,255,255,0.7); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--mh-admin-border-subtle);">
                            <div style="font-size: 0.6875rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Font Nội Dung (Modern Sans)</div>
                            <div style="font-size: 0.9375rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-top: 2px;">
                                {{ $visualDna['body_font'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Color Swatches Palette -->
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Bảng Màu Tông Sắc Nhận Diện: <span style="font-weight: 400; color: var(--mh-admin-muted);">{{ $visualDna['theme'] }}</span>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            @foreach($visualDna['palette'] as $hex)
                                <div style="display: flex; align-items: center; gap: 6px; background: #fff; padding: 3px 8px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                                    <span style="width: 14px; height: 14px; border-radius: 3px; background-color: {{ $hex }}; border: 1px solid rgba(0,0,0,0.1); display: inline-block;"></span>
                                    <code style="font-size: 0.6875rem;">{{ $hex }}</code>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); display: flex; gap: 14px; flex-wrap: wrap; border-top: 1px dashed var(--mh-admin-border); padding-top: 10px;">
                        <span>Viền & Khung: <strong>{{ $visualDna['has_border'] ? 'Có đường viền nghệ thuật' : 'Không viền' }}</strong></span>
                        <span>Texture Giấy: <strong>{{ $visualDna['has_paper_texture'] ? 'Có chất liệu mỹ thuật' : 'Phẳng tối giản' }}</strong></span>
                    </div>
                </div>
            @endif

            <!-- Status Banner -->
            <div class="mayhen_admin_schema_status_banner {{ $isCanonicalValid ? 'valid' : 'invalid' }}">
                @if ($isCanonicalValid)
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>
                        <strong style="font-size: 0.875rem;">Đạt Chuẩn 10/10 Section Tiêu Chuẩn (Điểm QA: {{ $audit['score'] ?? 100 }}%)</strong><br>
                        <span style="font-size: 0.75rem;">Mẫu thiệp cưới tương thích hoàn hảo với Studio Tạo Thiệp & Admin.</span>
                    </div>
                @else
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong style="font-size: 0.875rem;">Chưa Đủ 10 Section Tiêu Chuẩn (Điểm QA: {{ $audit['score'] ?? 0 }}%)</strong><br>
                        <span style="font-size: 0.75rem;">Cần bổ sung các section còn thiếu để hiển thị trơn tru.</span>
                    </div>
                @endif
            </div>

            <!-- Architectural Governance Badges (Wave 04) -->
            <div style="padding: 14px 18px; border-bottom: 1px solid var(--mh-admin-border-subtle); display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.75rem;">
                <div style="padding: 8px 10px; background: #FAF8F5; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Live Sync Contract</div>
                    <strong style="color: #1B5E20;">{{ $audit['sync_count'] ?? 0 }} targets</strong> bound
                    <span style="color: #7A756E;">({{ ($audit['has_essential_sync'] ?? false) ? 'Đủ tên đôi lứa' : 'Thiếu tên' }})</span>
                </div>

                <div style="padding: 8px 10px; background: #FAF8F5; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Local Assets Defense</div>
                    @if ($audit['is_local_assets_only'] ?? true)
                        <strong style="color: #1B5E20;">100% Cục Bộ</strong>
                        <span style="color: #7A756E;">({{ $audit['local_asset_count'] ?? 0 }} tệp)</span>
                    @else
                        <strong style="color: #C62828;">Phát hiện link ngoài</strong>
                    @endif
                </div>

                <div style="padding: 8px 10px; background: #FAF8F5; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Layout Inheritance</div>
                    @if ($audit['extends_invitation'] ?? false)
                        <strong style="color: #1B5E20;">Layout Chung (✓)</strong>
                    @else
                        <strong style="color: #C62828;">Chưa kế thừa layout</strong>
                    @endif
                </div>

                <div style="padding: 8px 10px; background: #FAF8F5; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Curated Parity</div>
                    @if ($audit['curated_compatible'] ?? false)
                        <strong style="color: #1B5E20;">7 Phần Native (✓)</strong>
                    @else
                        <strong style="color: #F57F17;">Chưa đủ 7 phần</strong>
                    @endif
                </div>
            </div>

            <!-- Schema Details -->
            <div style="padding: 12px 18px; border-bottom: 1px solid var(--mh-admin-border-subtle); font-size: 0.75rem; color: #7A756E; line-height: 1.5;">
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

