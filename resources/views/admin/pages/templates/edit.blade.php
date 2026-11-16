@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Mẫu: ' . $template->name)
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
                    <span class="mayhen_admin_badge mayhen_admin_badge_warning" style="background-color: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">★ Nổi Bật</span>
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
            <a href="{{ route('admin.templates.release_lab', $template->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="color: var(--mh-admin-primary); font-weight: 600;">
                Release Lab 🧪
            </a>
            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_primary">
                Xem Thử Giao Diện &nearr;
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; color: #065F46;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @php
        $cfg = $template->default_config ?? [];
    @endphp

    <!-- 2 Columns Layout: Form Left (Tabs) | Schema Inspector Right -->
    <div class="mayhen_admin_tpl_edit_layout">
        <!-- Left Column: Template Edit & Customization Suite -->
        <div class="mayhen_admin_card">
            <div class="mayhen_admin_card_header">
                <div class="mayhen_admin_card_title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-primary);">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Cấu Hình Thông Tin Mẫu Thiệp</span>
                </div>
            </div>

            <div class="mayhen_admin_card_body">
                <!-- Navigation Tabs -->
                <div class="mayhen_admin_tabs" role="tablist">
                    <button type="button" class="mayhen_admin_tab_btn active" data-tab="tab-general">
                        <span>📋</span> <span>Thông Tin Mẫu</span>
                    </button>
                    <button type="button" class="mayhen_admin_tab_btn" data-tab="tab-music-typography">
                        <span>🎵</span> <span>Nhạc & Phông Chữ</span>
                    </button>
                    <button type="button" class="mayhen_admin_tab_btn" data-tab="tab-couple-event">
                        <span>💍</span> <span>Đôi Lứa & Sự Kiện</span>
                    </button>
                    <button type="button" class="mayhen_admin_tab_btn" data-tab="tab-photos-gallery">
                        <span>🖼️</span> <span>Ảnh & Album 10 Ảnh</span>
                    </button>
                    <button type="button" class="mayhen_admin_tab_btn" data-tab="tab-story-gift">
                        <span>📜</span> <span>Lời Ngỏ & Mừng Cưới</span>
                    </button>
                </div>

                <form action="{{ route('admin.templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- ========================================== -->
                    <!-- TAB 1: THÔNG TIN MẪU CƠ BẢN               -->
                    <!-- ========================================== -->
                    <div class="mayhen_admin_tab_pane active" id="tab-general">
                        <div class="mayhen_admin_storage_clean_notice">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 1px;">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <div>
                                <strong>Cơ chế dọn dẹp Storage tự động:</strong> Mọi file ảnh hoặc file nhạc do quản trị viên tải lên mới khi sửa mẫu sẽ tự động thay thế và xóa bỏ file cũ tương ứng trong thư mục lưu trữ (Storage), ngăn ngừa tích tụ file rác và tiết kiệm tối đa dung lượng máy chủ.
                            </div>
                        </div>

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
                            <div class="mayhen_admin_form_help">Tên gọi trang trọng xuất hiện trên trang chủ, kho thư viện và Studio tạo thiệp.</div>
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
                            >{{ old('description', $template->description ?? ($cfg['description'] ?? '')) }}</textarea>
                            <div class="mayhen_admin_form_help">Đoạn văn ngắn làm nổi bật nét độc bản và cảm xúc tinh tế của mẫu thiệp này.</div>
                        </div>

                        <!-- Category & Tier Grid -->
                        <div class="mayhen_admin_form_grid_2">
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
                                    <option value="curated" {{ old('category', $template->category) === 'curated' ? 'selected' : '' }}>Thiết Kế Tự Phối (Curated)</option>
                                </select>
                            </div>

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

                        <!-- Thumbnail Image -->
                        <div class="mayhen_admin_form_group">
                            <label class="mayhen_admin_form_label">
                                Ảnh Đại Diện Mẫu (Thumbnail Cover)
                            </label>
                            <div class="mayhen_admin_asset_field_box">
                                <img src="{{ $template->thumbnail_url }}" alt="Thumbnail" class="mayhen_admin_asset_thumb" id="preview_thumb_img">
                                <div class="mayhen_admin_asset_controls">
                                    <input 
                                        type="text" 
                                        name="thumbnail_url" 
                                        id="thumbnail_url_input" 
                                        value="{{ old('thumbnail_url', $template->thumbnail_url) }}" 
                                        class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                                        placeholder="Đường dẫn ảnh hoặc tải tệp lên bên dưới"
                                    >
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <label class="mayhen_admin_file_upload_btn">
                                            <input type="file" name="thumbnail_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_thumb_img')">
                                            <span>📁 Tải Ảnh Mới Thay Thế</span>
                                        </label>
                                        <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Checkboxes -->
                        <div style="display: flex; gap: 24px; margin-top: 24px; padding: 14px 16px; background-color: #F8FAFC; border-radius: var(--mh-admin-radius-sm); border: 1px solid var(--mh-admin-border);">
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
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 2: NHẠC NỀN & PHÔNG CHỮ TRƯNG BÀY      -->
                    <!-- ========================================== -->
                    <div class="mayhen_admin_tab_pane" id="tab-music-typography">
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                            </svg>
                            <span>Nhạc Nền Trưng Bày Mẫu Thiệp (Showcase Audio)</span>
                        </div>

                        <!-- Music selection -->
                        <div class="mayhen_admin_form_group">
                            <label class="mayhen_admin_form_label">
                                Chọn Bài Hát Từ Thư Viện Nhạc Cưới Mây Hẹn
                            </label>
                            <select id="preset_music_select" class="mayhen_admin_form_select" onchange="applyPresetMusic(this)">
                                <option value="">-- Chọn bài hát có sẵn trong thư viện --</option>
                                @if(isset($musicCatalog) && is_array($musicCatalog))
                                    @foreach($musicCatalog as $song)
                                        <option value="{{ $song['url'] }}" {{ ($cfg['music_url'] ?? '') === $song['url'] ? 'selected' : '' }}>
                                            {{ $song['title'] }} - {{ $song['artist'] }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="/mau-thiep/{{ $template->slug }}/assets/wedding-song.mp3">
                                    [Gốc] Nhạc mặc định theo mẫu (wedding-song.mp3)
                                </option>
                            </select>
                            <div class="mayhen_admin_form_help">Chọn nhanh bài hát lãng mạn đã được kiểm duyệt chất lượng âm thanh 320kbps.</div>
                        </div>

                        <!-- Custom URL or File Upload -->
                        <div class="mayhen_admin_form_group">
                            <label class="mayhen_admin_form_label">Đường Dẫn Hoặc Tải Tệp Nhạc MP3 Mới Lên</label>
                            <div class="mayhen_admin_audio_box">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input 
                                        type="text" 
                                        id="custom_music_url_input" 
                                        name="music_url" 
                                        value="{{ old('music_url', $cfg['music_url'] ?? '') }}" 
                                        class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                                        placeholder="Ví dụ: /storage/templates/... hoặc đường dẫn URL bài hát"
                                        oninput="updateAudioPlayerSrc(this.value)"
                                    >
                                    <label class="mayhen_admin_file_upload_btn" style="white-space: nowrap;">
                                        <input type="file" name="music_file" accept="audio/mpeg,audio/mp3,audio/wav,audio/ogg" style="display: none;" onchange="previewAudioFileInput(this)">
                                        <span>🎵 Tải Lên MP3 Mới</span>
                                    </label>
                                </div>
                                <div class="mayhen_admin_form_help" style="margin-top: 8px;">
                                    Nghe thử trực tiếp bài hát mẫu sẽ phát khi khách mở thiệp cưới:
                                </div>
                                <audio controls id="admin_showcase_audio_player" class="mayhen_admin_audio_player" src="{{ $cfg['music_url'] ?? '/mau-thiep/' . $template->slug . '/assets/wedding-song.mp3' }}">
                                    Trình duyệt không hỗ trợ thẻ audio.
                                </audio>
                            </div>
                        </div>

                        <!-- Typography Fonts -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="4 7 4 4 20 4 20 7"></polyline>
                                <line x1="9" y1="20" x2="15" y2="20"></line>
                                <line x1="12" y1="4" x2="12" y2="20"></line>
                            </svg>
                            <span>Hệ Thống Phông Chữ Trưng Bày (Showcase Typography)</span>
                        </div>

                        <div class="mayhen_admin_form_grid_3">
                            <div class="mayhen_admin_form_group">
                                <label for="heading_font" class="mayhen_admin_form_label">
                                    Phông Tiêu Đề Chính (Editorial Serif)
                                </label>
                                @php $selectedHeadingFont = old('heading_font', $cfg['heading_font'] ?? ($visualDna['heading_font'] ?? 'Playfair Display')); @endphp
                                <select id="heading_font" name="heading_font" class="mayhen_admin_form_select" onchange="updateLiveFontPreview()">
                                    @foreach($availableHeadingFonts as $f)
                                        <option value="{{ $f }}" {{ $selectedHeadingFont === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="script_font" class="mayhen_admin_form_label">
                                    Phông Nghệ Thuật / Điểm Nhấn (Script)
                                </label>
                                @php $selectedScriptFont = old('script_font', $cfg['script_font'] ?? 'Dancing Script'); @endphp
                                <select id="script_font" name="script_font" class="mayhen_admin_form_select" onchange="updateLiveFontPreview()">
                                    @foreach($availableScriptFonts as $f)
                                        <option value="{{ $f }}" {{ $selectedScriptFont === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="body_font" class="mayhen_admin_form_label">
                                    Phông Nội Dung (Modern Sans)
                                </label>
                                @php $selectedBodyFont = old('body_font', $cfg['body_font'] ?? ($visualDna['body_font'] ?? 'Montserrat')); @endphp
                                <select id="body_font" name="body_font" class="mayhen_admin_form_select" onchange="updateLiveFontPreview()">
                                    @foreach($availableBodyFonts as $f)
                                        <option value="{{ $f }}" {{ $selectedBodyFont === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Live Typography Preview Box -->
                        <div class="mayhen_admin_font_preview_card">
                            <div class="mayhen_admin_font_preview_script" id="preview_font_script_box" style="font-family: '{{ $selectedScriptFont }}', cursive;">
                                Save The Date &bull; Chúng Mình Cưới Nhau
                            </div>
                            <div class="mayhen_admin_font_preview_heading" id="preview_font_heading_box" style="font-family: '{{ $selectedHeadingFont }}', Georgia, serif;">
                                {{ $cfg['groom_name'] ?? 'Hà Kiên' }} & {{ $cfg['bride_name'] ?? 'Phạm Xôn' }}
                            </div>
                            <div class="mayhen_admin_font_preview_body" id="preview_font_body_box" style="font-family: '{{ $selectedBodyFont }}', sans-serif;">
                                Trân trọng kính mời quý khách đến chung vui cùng gia đình chúng mình trong ngày trọng đại và thiêng liêng nhất cuộc đời.
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 3: ĐÔI LỨA & SỰ KIỆN CƯỚI TRƯNG BÀY     -->
                    <!-- ========================================== -->
                    <div class="mayhen_admin_tab_pane" id="tab-couple-event">
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <span>Thông Tin Đôi Uyên Ương Trưng Bày</span>
                        </div>

                        <div class="mayhen_admin_form_grid_3">
                            <div class="mayhen_admin_form_group">
                                <label for="ceremony_type" class="mayhen_admin_form_label">Loại Hình Hôn Lễ</label>
                                <input type="text" id="ceremony_type" name="ceremony_type" value="{{ old('ceremony_type', $cfg['ceremony_type'] ?? 'Lễ Thành Hôn') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Lễ Thành Hôn, Lễ Vu Quy, Lễ Báo Hỷ">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="groom_name" class="mayhen_admin_form_label">Họ Tên Chú Rể Trưng Bày</label>
                                <input type="text" id="groom_name" name="groom_name" value="{{ old('groom_name', $cfg['groom_name'] ?? 'Hà Kiên') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Hà Kiên">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="bride_name" class="mayhen_admin_form_label">Họ Tên Cô Dâu Trưng Bày</label>
                                <input type="text" id="bride_name" name="bride_name" value="{{ old('bride_name', $cfg['bride_name'] ?? 'Phạm Xôn') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Phạm Xôn">
                            </div>
                        </div>

                        <!-- Parents Grid -->
                        <div class="mayhen_admin_form_grid_2">
                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Song Thân Nhà Trai:</strong>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Bố Chú Rể:</label>
                                    <input type="text" name="groom_father" value="{{ old('groom_father', $cfg['groom_father'] ?? 'Lê Quang Minh') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Mẹ Chú Rể:</label>
                                    <input type="text" name="groom_mother" value="{{ old('groom_mother', $cfg['groom_mother'] ?? 'Trần Thị Thu Trang') }}" class="mayhen_admin_form_input">
                                </div>
                            </div>

                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Song Thân Nhà Gái:</strong>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Bố Cô Dâu:</label>
                                    <input type="text" name="bride_father" value="{{ old('bride_father', $cfg['bride_father'] ?? 'Phạm Văn Hùng') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Mẹ Cô Dâu:</label>
                                    <input type="text" name="bride_mother" value="{{ old('bride_mother', $cfg['bride_mother'] ?? 'Nguyễn Thị Tuyết') }}" class="mayhen_admin_form_input">
                                </div>
                            </div>
                        </div>

                        <!-- Date & Location -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>Thời Gian & Địa Điểm Cử Hành Trưng Bày</span>
                        </div>

                        <div class="mayhen_admin_form_grid_3">
                            <div class="mayhen_admin_form_group">
                                <label for="wedding_date" class="mayhen_admin_form_label">Ngày Cưới Dương Lịch</label>
                                <input type="date" id="wedding_date" name="wedding_date" value="{{ old('wedding_date', $cfg['wedding_date'] ?? '2026-12-20') }}" class="mayhen_admin_form_input">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="party_time" class="mayhen_admin_form_label">Giờ Cử Hành Tiệc</label>
                                <input type="text" id="party_time" name="party_time" value="{{ old('party_time', $cfg['party_time'] ?? '10:00') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: 10:00 hoặc 18:30">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="wedding_lunar_date" class="mayhen_admin_form_label">Ngày Âm Lịch Trưng Bày</label>
                                <input type="text" id="wedding_lunar_date" name="wedding_lunar_date" value="{{ old('wedding_lunar_date', $cfg['wedding_lunar_date'] ?? 'Nhằm ngày 16 tháng 08 năm Ất Tỵ') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Nhằm ngày 16 tháng 08 năm Ất Tỵ">
                            </div>
                        </div>

                        <div class="mayhen_admin_form_grid_2">
                            <div class="mayhen_admin_form_group">
                                <label for="party_venue" class="mayhen_admin_form_label">Tên Địa Điểm / Sảnh Tiệc</label>
                                <input type="text" id="party_venue" name="party_venue" value="{{ old('party_venue', $cfg['party_venue'] ?? ($cfg['venue_name'] ?? 'TẠI TƯ GIA NHÀ TRAI')) }}" class="mayhen_admin_form_input" placeholder="Ví dụ: TƯ GIA NHÀ TRAI hoặc WHITE PALACE">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="party_map_url" class="mayhen_admin_form_label">Link Bản Đồ Google Maps</label>
                                <input type="text" id="party_map_url" name="party_map_url" value="{{ old('party_map_url', $cfg['party_map_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="https://maps.google.com/...">
                            </div>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label for="event_party_address" class="mayhen_admin_form_label">Địa Chỉ Cử Hành Tiệc</label>
                            <input type="text" id="event_party_address" name="event_party_address" value="{{ old('event_party_address', $cfg['event_party_address'] ?? ($cfg['venue_address'] ?? '423 An Dương Vương - Thượng Thuỵ, Phú Thượng, Tây Hồ, Hà Nội')) }}" class="mayhen_admin_form_input" placeholder="Địa chỉ chi tiết nơi diễn ra hôn lễ...">
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 4: ẢNH TRƯNG BÀY & ALBUM 10 ẢNH        -->
                    <!-- ========================================== -->
                    <div class="mayhen_admin_tab_pane" id="tab-photos-gallery">
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>Bộ Ảnh Chân Dung & Bìa Chính Trưng Bày</span>
                        </div>

                        <div class="mayhen_admin_form_grid_2">
                            <!-- Hero Cover Photo -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Bìa Chính (Hero Cover Photo)</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['cover_photo_url'] ?? ($cfg['hero_photo_url'] ?? $template->thumbnail_url) }}" alt="Hero Cover" class="mayhen_admin_asset_thumb mayhen_admin_asset_thumb_tall" id="preview_cover_img">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="cover_photo_url" value="{{ old('cover_photo_url', $cfg['cover_photo_url'] ?? ($cfg['hero_photo_url'] ?? '')) }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp assets hoặc URL ảnh">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="cover_photo_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_cover_img')">
                                                <span>📁 Tải Ảnh Mới</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Date Photo -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Khung Vòm / Save The Date</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['save_date_photo_url'] ?? $template->thumbnail_url }}" alt="Save Date" class="mayhen_admin_asset_thumb mayhen_admin_asset_thumb_tall" id="preview_savedate_img">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="save_date_photo_url" value="{{ old('save_date_photo_url', $cfg['save_date_photo_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp assets hoặc URL ảnh">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="save_date_photo_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_savedate_img')">
                                                <span>📁 Tải Ảnh Mới</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Groom Photo -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Chân Dung Chú Rể</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['groom_photo_url'] ?? $template->thumbnail_url }}" alt="Groom" class="mayhen_admin_asset_thumb" id="preview_groom_img">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="groom_photo_url" value="{{ old('groom_photo_url', $cfg['groom_photo_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp assets hoặc URL ảnh">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="groom_photo_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_groom_img')">
                                                <span>📁 Tải Ảnh Mới</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bride Photo -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Chân Dung Cô Dâu</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['bride_photo_url'] ?? $template->thumbnail_url }}" alt="Bride" class="mayhen_admin_asset_thumb" id="preview_bride_img">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="bride_photo_url" value="{{ old('bride_photo_url', $cfg['bride_photo_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp assets hoặc URL ảnh">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="bride_photo_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_bride_img')">
                                                <span>📁 Tải Ảnh Mới</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 10 Photos Album Gallery -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>Showcase Album 10 Ảnh Cưới Tiêu Chuẩn (Đạt Chuẩn 10 Section)</span>
                        </div>

                        <div class="mayhen_admin_gallery_showcase_grid">
                            @php
                                $galleryList = $cfg['gallery'] ?? [];
                            @endphp
                            @for ($gIdx = 0; $gIdx < 10; $gIdx++)
                                @php
                                    $photoVal = $galleryList[$gIdx] ?? '';
                                    $photoPreview = $photoVal;
                                    if (!empty($photoVal) && !str_starts_with($photoVal, 'http') && !str_starts_with($photoVal, '/')) {
                                        $photoPreview = '/mau-thiep/' . $template->slug . '/assets/' . ltrim($photoVal, '/');
                                    } elseif (empty($photoPreview)) {
                                        $photoPreview = $template->thumbnail_url;
                                    }
                                @endphp
                                <div class="mayhen_admin_gallery_card">
                                    <div class="mayhen_admin_gallery_card_header">
                                        <span class="mayhen_admin_gallery_badge">Ảnh {{ sprintf('%02d', $gIdx + 1) }}</span>
                                        <span class="mayhen_admin_storage_pill" style="font-size: 0.625rem;">Tự dọn file cũ</span>
                                    </div>
                                    <img src="{{ $photoPreview }}" alt="Gallery {{ $gIdx + 1 }}" class="mayhen_admin_gallery_thumb" id="preview_gallery_img_{{ $gIdx }}" onerror="this.onerror=null; this.src='{{ $template->thumbnail_url }}'">
                                    <input 
                                        type="text" 
                                        name="gallery_urls[{{ $gIdx }}]" 
                                        value="{{ $photoVal }}" 
                                        class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                                        placeholder="Tên tệp assets hoặc URL"
                                    >
                                    <label class="mayhen_admin_file_upload_btn" style="justify-content: center; width: 100%;">
                                        <input type="file" name="gallery_files[{{ $gIdx }}]" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_gallery_img_{{ $gIdx }}')">
                                        <span>📁 Đổi Ảnh Này</span>
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 5: LỜI NGỎ, LỊCH & MỪNG CƯỚI           -->
                    <!-- ========================================== -->
                    <div class="mayhen_admin_tab_pane" id="tab-story-gift">
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <span>Câu Chuyện Tình Yêu Trưng Bày (3 Mốc Kỷ Niệm)</span>
                        </div>

                        <!-- Story 3 Stages -->
                        <div style="background: #F8FAFC; padding: 16px; border-radius: 6px; border: 1px solid var(--mh-admin-border); margin-bottom: 20px;">
                            <!-- Stage 1 -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Mốc 1: Tiêu Đề</label>
                                <input type="text" name="story_stage_1_title" value="{{ old('story_stage_1_title', $cfg['story_stage_1_title'] ?? 'Lần Đầu Gặp Gỡ') }}" class="mayhen_admin_form_input">
                            </div>
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Mốc 1: Nội Dung Kỷ Niệm</label>
                                <textarea name="story_stage_1_desc" class="mayhen_admin_form_textarea" style="min-height: 60px;">{{ old('story_stage_1_desc', $cfg['story_stage_1_desc'] ?? 'Có ai tin vào tình yêu từ cái nhìn đầu tiên không? Một cô bé khá xinh xắn mà tôi tình cờ gặp trong lần đi chơi Noel cùng đám bạn...') }}</textarea>
                            </div>

                            <hr style="border: none; border-top: 1px dashed var(--mh-admin-border); margin: 16px 0;">

                            <!-- Stage 2 -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Mốc 2: Tiêu Đề</label>
                                <input type="text" name="story_stage_2_title" value="{{ old('story_stage_2_title', $cfg['story_stage_2_title'] ?? 'Ngày Em Nhận Lời Yêu') }}" class="mayhen_admin_form_input">
                            </div>
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Mốc 2: Nội Dung Kỷ Niệm</label>
                                <textarea name="story_stage_2_desc" class="mayhen_admin_form_textarea" style="min-height: 60px;">{{ old('story_stage_2_desc', $cfg['story_stage_2_desc'] ?? 'Không cần đắn đo suy nghĩ gì thêm, tôi quyết định ngỏ lời trong một buổi chiều hoàng hôn bên bờ hồ...') }}</textarea>
                            </div>

                            <hr style="border: none; border-top: 1px dashed var(--mh-admin-border); margin: 16px 0;">

                            <!-- Stage 3 -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Mốc 3: Tiêu Đề</label>
                                <input type="text" name="story_stage_3_title" value="{{ old('story_stage_3_title', $cfg['story_stage_3_title'] ?? 'Mãi Bên Nhau Nhé!') }}" class="mayhen_admin_form_input">
                            </div>
                            <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                <label class="mayhen_admin_form_label">Mốc 3: Nội Dung Kỷ Niệm</label>
                                <textarea name="story_stage_3_desc" class="mayhen_admin_form_textarea" style="min-height: 60px;">{{ old('story_stage_3_desc', $cfg['story_stage_3_desc'] ?? 'Suy cho cùng, mấy ai yêu mà chẳng mong được cùng nhau tiến tới ngưỡng cửa hôn nhân. Tình yêu chín muồi thì mình cưới nhau thôi.') }}</textarea>
                            </div>
                        </div>

                        <!-- Thank you and Dresscode -->
                        <div class="mayhen_admin_form_grid_2">
                            <div class="mayhen_admin_form_group">
                                <label for="dresscode_colors" class="mayhen_admin_form_label">Bảng Màu Trang Phục (Dresscode)</label>
                                <input type="text" id="dresscode_colors" name="dresscode_colors" value="{{ old('dresscode_colors', $cfg['dresscode_colors'] ?? '#1F1D1A, #85182A, #CFA860, #FAF7F2') }}" class="mayhen_admin_form_input" placeholder="#1F1D1A, #85182A, #CFA860, #FAF7F2">
                                <div class="mayhen_admin_form_help">Các mã màu HEX phân cách bằng dấu phẩy.</div>
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="dresscode_note" class="mayhen_admin_form_label">Ghi Chú Trang Phục</label>
                                <input type="text" id="dresscode_note" name="dresscode_note" value="{{ old('dresscode_note', $cfg['dresscode_note'] ?? 'Khuyến khích trang phục lịch thiệp mang sắc màu đồng điệu với không gian tiệc.') }}" class="mayhen_admin_form_input">
                            </div>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label for="footer_thank_you_text" class="mayhen_admin_form_label">Lời Cảm Ơn Cuối Thiệp (Thank You Note)</label>
                            <textarea id="footer_thank_you_text" name="footer_thank_you_text" class="mayhen_admin_form_textarea" style="min-height: 60px;">{{ old('footer_thank_you_text', $cfg['footer_thank_you_text'] ?? 'Sự hiện diện và lời chúc phúc của quý khách là niềm vinh hạnh to lớn và là món quà ý nghĩa nhất đối với gia đình chúng mình!') }}</textarea>
                        </div>

                        <!-- Gift & Bank QR -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                            <span>Hộp Mừng Cưới & Mã QR Trưng Bày</span>
                        </div>

                        <div class="mayhen_admin_form_grid_2">
                            <!-- Groom Banking -->
                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Ngân Hàng Chú Rể:</strong>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 8px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Tên Ngân Hàng:</label>
                                    <input type="text" name="groom_bank_name" value="{{ old('groom_bank_name', $cfg['groom_bank_name'] ?? 'Vietcombank') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_grid_2" style="margin-bottom: 8px;">
                                    <div>
                                        <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Số Tài Khoản:</label>
                                        <input type="text" name="groom_bank_account" value="{{ old('groom_bank_account', $cfg['groom_bank_account'] ?? '999999999999999') }}" class="mayhen_admin_form_input">
                                    </div>
                                    <div>
                                        <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Chủ Tài Khoản:</label>
                                        <input type="text" name="groom_bank_owner" value="{{ old('groom_bank_owner', $cfg['groom_bank_owner'] ?? 'HÀ VĂN KIÊN') }}" class="mayhen_admin_form_input">
                                    </div>
                                </div>
                                <div class="mayhen_admin_asset_field_box" style="margin-top: 8px;">
                                    <img src="{{ $cfg['groom_qr_url'] ?? $template->thumbnail_url }}" alt="Groom QR" class="mayhen_admin_asset_thumb" id="preview_groom_qr">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="groom_qr_url" value="{{ old('groom_qr_url', $cfg['groom_qr_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp QR hoặc URL">
                                        <label class="mayhen_admin_file_upload_btn">
                                            <input type="file" name="groom_qr_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_groom_qr')">
                                            <span>📁 Đổi Mã QR</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bride Banking -->
                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Ngân Hàng Cô Dâu:</strong>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 8px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Tên Ngân Hàng:</label>
                                    <input type="text" name="bride_bank_name" value="{{ old('bride_bank_name', $cfg['bride_bank_name'] ?? 'Techcombank') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_grid_2" style="margin-bottom: 8px;">
                                    <div>
                                        <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Số Tài Khoản:</label>
                                        <input type="text" name="bride_bank_account" value="{{ old('bride_bank_account', $cfg['bride_bank_account'] ?? '1234567890') }}" class="mayhen_admin_form_input">
                                    </div>
                                    <div>
                                        <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Chủ Tài Khoản:</label>
                                        <input type="text" name="bride_bank_owner" value="{{ old('bride_bank_owner', $cfg['bride_bank_owner'] ?? 'PHẠM THỊ XÔN') }}" class="mayhen_admin_form_input">
                                    </div>
                                </div>
                                <div class="mayhen_admin_asset_field_box" style="margin-top: 8px;">
                                    <img src="{{ $cfg['bride_qr_url'] ?? $template->thumbnail_url }}" alt="Bride QR" class="mayhen_admin_asset_thumb" id="preview_bride_qr">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="bride_qr_url" value="{{ old('bride_qr_url', $cfg['bride_qr_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp QR hoặc URL">
                                        <label class="mayhen_admin_file_upload_btn">
                                            <input type="file" name="bride_qr_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_bride_qr')">
                                            <span>📁 Đổi Mã QR</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Submit Buttons -->
                    <div style="display: flex; gap: 10px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--mh-admin-border); flex-wrap: wrap;">
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 10px 24px; font-size: 0.875rem;">
                            <span>💾 Lưu Cập Nhật</span>
                        </button>
                        <button type="submit" name="save_and_continue" value="1" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 10px 20px; font-size: 0.875rem;">
                            <span>💾 Lưu & Tiếp Tục Sửa</span>
                        </button>
                        <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_ghost" style="padding: 10px 20px;">
                            <span>Hủy Bỏ</span>
                        </a>
                        <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline" style="margin-left: auto;">
                            <span>Xem Thử Thiệp &nearr;</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Schema Validator Inspector Card -->
        <div class="mayhen_admin_schema_card">
            <!-- Template Visual DNA Inspector (Innovation #10) -->
            @if(isset($visualDna))
                <div class="mayhen_admin_card" style="margin-bottom: 20px; background: #FFFFFF; border-left: 4px solid var(--mh-admin-primary); padding: 18px 20px; border: 1px solid var(--mh-admin-border);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 1.25rem;">🧬</span>
                                <h3 style="font-size: 0.9375rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                    Template Visual DNA (Art Direction Audit)
                                </h3>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                Dấu vết thiết kế nguyên bản & độ hòa hợp nhận diện thương hiệu
                            </div>
                        </div>
                        <span class="mayhen_admin_badge {{ $visualDna['brand_harmony'] >= 80 ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}">
                            {{ $visualDna['brand_harmony'] }}/100 Harmony
                        </span>
                    </div>

                    <!-- DNA Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                        <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                            <div style="font-size: 0.6875rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Font Tiêu Đề Gốc</div>
                            <div style="font-size: 0.875rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-top: 2px;">
                                {{ $visualDna['heading_font'] }}
                            </div>
                        </div>

                        <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                            <div style="font-size: 0.6875rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Font Nội Dung Gốc</div>
                            <div style="font-size: 0.875rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-top: 2px;">
                                {{ $visualDna['body_font'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Color Swatches Palette -->
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Bảng Màu Tông Sắc Nhận Diện: <span style="font-weight: 400; color: var(--mh-admin-muted);">{{ $visualDna['theme'] }}</span>
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            @foreach($visualDna['palette'] as $hex)
                                <div style="display: flex; align-items: center; gap: 5px; background: #F8FAFC; padding: 2px 7px; border-radius: 4px; border: 1px solid var(--mh-admin-border);">
                                    <span style="width: 12px; height: 12px; border-radius: 2px; background-color: {{ $hex }}; border: 1px solid rgba(0,0,0,0.1); display: inline-block;"></span>
                                    <code style="font-size: 0.6875rem; color: #334155;">{{ $hex }}</code>
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
                <div style="padding: 8px 10px; background: #F8FAFC; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Live Sync Contract</div>
                    <strong style="color: #065F46;">{{ $audit['sync_count'] ?? 0 }} targets</strong> bound
                    <span style="color: var(--mh-admin-muted);">({{ ($audit['has_essential_sync'] ?? false) ? 'Đủ tên đôi lứa' : 'Thiếu tên' }})</span>
                </div>

                <div style="padding: 8px 10px; background: #F8FAFC; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Local Assets Defense</div>
                    @if ($audit['is_local_assets_only'] ?? true)
                        <strong style="color: #065F46;">100% Cục Bộ</strong>
                        <span style="color: var(--mh-admin-muted);">({{ $audit['local_asset_count'] ?? 0 }} tệp)</span>
                    @else
                        <strong style="color: #991B1B;">Phát hiện link ngoài</strong>
                    @endif
                </div>

                <div style="padding: 8px 10px; background: #F8FAFC; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Layout Inheritance</div>
                    @if ($audit['extends_invitation'] ?? false)
                        <strong style="color: #065F46;">Layout Chung (✓)</strong>
                    @else
                        <strong style="color: #991B1B;">Chưa kế thừa layout</strong>
                    @endif
                </div>

                <div style="padding: 8px 10px; background: #F8FAFC; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                    <div style="font-weight: 700; color: var(--mh-admin-muted); margin-bottom: 2px;">Curated Parity</div>
                    @if ($audit['curated_compatible'] ?? false)
                        <strong style="color: #065F46;">7 Phần Native (✓)</strong>
                    @else
                        <strong style="color: #92400E;">Chưa đủ 7 phần</strong>
                    @endif
                </div>
            </div>

            <!-- Schema Details -->
            <div style="padding: 12px 18px; border-bottom: 1px solid var(--mh-admin-border-subtle); font-size: 0.75rem; color: var(--mh-admin-muted); line-height: 1.5;">
                Tệp cấu hình: <code>resources/views/client/invitations/{{ $template->slug }}/schema.json</code><br>
                Trạng thái tệp: <strong>{{ $schemaExists ? 'Đã tồn tại' : 'Chưa có file JSON' }}</strong> &bull; Album 10 ảnh: <strong>{{ $galleryValid ? 'Đã chuẩn hóa' : 'Chưa có field gallery' }}</strong>
            </div>

            <!-- Checklist of 10 Canonical Sections -->
            <div class="mayhen_admin_section_list">
                @foreach ($sectionsReport as $sec)
                    <div class="mayhen_admin_section_item {{ $sec['passed'] ? 'passed' : 'missing' }}">
                        <span class="mayhen_admin_section_title">{{ $sec['title'] }}</span>
                        @if ($sec['passed'])
                            <span class="mayhen_admin_section_badge" style="background-color: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0;">✓ Đạt chuẩn</span>
                        @else
                            <span class="mayhen_admin_section_badge" style="background-color: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;">✗ Còn thiếu</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Tab Switching Logic
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtns = document.querySelectorAll('.mayhen_admin_tab_btn');
        const tabPanes = document.querySelectorAll('.mayhen_admin_tab_pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetTabId = this.getAttribute('data-tab');

                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                const targetPane = document.getElementById(targetTabId);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    });

    // Image Upload Live Preview
    function previewImageInput(input, imgElementId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const targetImg = document.getElementById(imgElementId);
                if (targetImg) {
                    targetImg.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Audio Preset Selector & Live Player
    function applyPresetMusic(select) {
        const val = select.value;
        const input = document.getElementById('custom_music_url_input');
        if (input && val) {
            input.value = val;
            updateAudioPlayerSrc(val);
        }
    }

    function updateAudioPlayerSrc(url) {
        const audio = document.getElementById('admin_showcase_audio_player');
        if (audio && url) {
            audio.src = url;
            audio.load();
        }
    }

    function previewAudioFileInput(input) {
        if (input.files && input.files[0]) {
            const fileUrl = URL.createObjectURL(input.files[0]);
            updateAudioPlayerSrc(fileUrl);
            const inputTxt = document.getElementById('custom_music_url_input');
            if (inputTxt) {
                inputTxt.value = '[Tệp mới tải lên: ' + input.files[0].name + ']';
            }
        }
    }

    // Dynamic Typography Preview Update
    function updateLiveFontPreview() {
        const headingFont = document.getElementById('heading_font') ? document.getElementById('heading_font').value : 'Playfair Display';
        const scriptFont = document.getElementById('script_font') ? document.getElementById('script_font').value : 'Dancing Script';
        const bodyFont = document.getElementById('body_font') ? document.getElementById('body_font').value : 'Montserrat';

        const headBox = document.getElementById('preview_font_heading_box');
        const scriptBox = document.getElementById('preview_font_script_box');
        const bodyBox = document.getElementById('preview_font_body_box');

        if (headBox) headBox.style.fontFamily = `"${headingFont}", Georgia, serif`;
        if (scriptBox) scriptBox.style.fontFamily = `"${scriptFont}", cursive`;
        if (bodyBox) bodyBox.style.fontFamily = `"${bodyFont}", sans-serif`;
    }
</script>
@endpush
