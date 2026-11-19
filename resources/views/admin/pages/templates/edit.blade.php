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

                        <!-- Full Page Scroll Preview Image -->
                        <div class="mayhen_admin_form_group" style="margin-top: 18px;">
                            <label class="mayhen_admin_form_label" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                                <span>Ảnh Chụp Toàn Mẫu Để Cuộn (Full Page Scroll Preview)</span>
                                <span style="font-size: 0.75rem; color: #7A756E; font-weight: normal;">Dùng cho hiệu ứng cuộn tự động trên Trang Chủ & Kho Mẫu</span>
                            </label>

                            @php
                                $currentFullPreview = old('full_page_preview_url', $defaultConfig['full_page_preview'] ?? $template->full_page_preview);
                            @endphp

                            <div class="mayhen_admin_full_scroll_preview_box">
                                <div class="mayhen_admin_phone_mockup_scroll" id="phone_mockup_container" title="Rê chuột hoặc bấm 'Cuộn Thử' để xem hiệu ứng cuộn mượt mà">
                                    <div class="mayhen_admin_phone_mockup_speaker"></div>
                                    <div class="mayhen_admin_phone_mockup_inner" id="phone_mockup_inner">
                                        <img src="{{ $currentFullPreview }}" alt="Full Page Scroll Preview" class="mayhen_admin_phone_mockup_img" id="preview_full_page_img" onerror="this.onerror=null; this.src='{{ $template->thumbnail_url }}';">
                                    </div>
                                    <div class="mayhen_admin_phone_mockup_badge">Scroll Preview</div>
                                </div>

                                <div class="mayhen_admin_asset_controls" style="flex: 1;">
                                    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                                        <input 
                                            type="text" 
                                            name="full_page_preview_url" 
                                            id="full_page_preview_url_input" 
                                            value="{{ old('full_page_preview_url', $defaultConfig['full_page_preview'] ?? '') }}" 
                                            class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                                            placeholder="Đường dẫn ảnh dài toàn trang (/mau-thiep/.../assets/... hoặc /storage/...) hoặc tải tệp bên dưới"
                                            onchange="updateFullPreviewFromUrl(this.value)"
                                        >
                                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="toggleScrollSimulation()" style="padding: 6px 12px; font-size: 0.75rem; white-space: nowrap;">
                                            <span id="scroll_simulation_btn_text">▶ Cuộn Thử</span>
                                        </button>
                                    </div>

                                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        <label class="mayhen_admin_file_upload_btn">
                                            <input type="file" name="full_page_preview_file" id="full_page_preview_file_input" accept="image/*" style="display: none;" onchange="previewFullPageImageInput(this)">
                                            <span>📁 Tải Ảnh Cuộn Mới (Tối đa 25MB)</span>
                                        </label>

                                        @if (!empty($detectedLocalFullPreview))
                                            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="selectDetectedLocalAsset('{{ $detectedLocalFullPreview }}')" style="font-size: 0.75rem; padding: 5px 10px; color: #8A6D3B; border-color: #D6C29A; background-color: #FCF8F2;" title="Gán đường dẫn ảnh phát hiện có sẵn trong thư mục assets">
                                                <span>⚡ Chọn ảnh có sẵn trong assets ({{ basename($detectedLocalFullPreview) }})</span>
                                            </button>
                                        @endif

                                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="resetFullPreviewField()" style="font-size: 0.75rem; padding: 5px 10px; color: #991B1B;">
                                            <span>Xóa / Đặt lại</span>
                                        </button>
                                        <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
                                    </div>

                                    <div style="font-size: 0.75rem; color: #64748B; line-height: 1.5; margin-top: 6px;">
                                        💡 <strong>Mẹo:</strong> Ảnh cuộn toàn trang (Full Page Scroll) là ảnh chụp toàn bộ các section từ đầu đến cuối của mẫu thiệp (khuyến nghị định dạng WebP, chiều rộng khoảng 480px - 900px, chiều dài từ 2.000px đến 10.000px). Khi khách xem trên Trang Chủ hoặc Kho Mẫu, ảnh sẽ tự động trôi mượt mà mô phỏng màn hình điện thoại thật.
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

                        @php
                            $currentMusicUrl = old('music_url', $cfg['music_url'] ?? ('/mau-thiep/' . $template->slug . '/assets/wedding-song.mp3'));
                            $matchedSong = null;
                            if (isset($musicCatalog)) {
                                foreach ($musicCatalog as $song) {
                                    if (($song['url'] ?? '') === $currentMusicUrl) {
                                        $matchedSong = $song;
                                        break;
                                    }
                                }
                            }
                            $templateEditorBrandName = \App\Models\PlatformSetting::getBrandName();
                            $currentTitle = $matchedSong['title'] ?? (str_starts_with($currentMusicUrl, '/storage/') ? 'Tệp Nhạc Tải Lên Riêng' : 'Giai Điệu Mặc Định Của Mẫu');
                            $currentArtist = $matchedSong['artist'] ?? (str_starts_with($currentMusicUrl, '/storage/') ? 'Quản Trị Viên Tải Lên' : $templateEditorBrandName);
                            $currentGenre = $matchedSong['genre'] ?? (str_starts_with($currentMusicUrl, '/storage/') ? 'File Tùy Chỉnh' : 'Theo Mẫu Thiệp');
                        @endphp

                        <!-- Showcase Music Selection Card (Dedicated Popup Modal with Artist & Title Separation) -->
                        <div class="mayhen_admin_form_group">
                            <label class="mayhen_admin_form_label">
                                Nhạc Nền Phát Khi Khách Mở Thiệp Cưới
                            </label>
                            
                            <div class="mayhen_admin_music_showcase_card">
                                <!-- Active Song Banner -->
                                <div class="mayhen_admin_music_active_display">
                                    <div class="mayhen_admin_music_disc_icon" id="admin_music_disc_icon">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 18V5l12-2v13"></path>
                                            <circle cx="6" cy="18" r="3"></circle>
                                            <circle cx="18" cy="16" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div class="mayhen_admin_music_active_info">
                                        <div class="mayhen_admin_music_active_title">
                                            <span id="active_music_title">{{ $currentTitle }}</span>
                                            <span id="active_music_genre_badge" class="mayhen_admin_music_badge">{{ $currentGenre }}</span>
                                        </div>
                                        <div class="mayhen_admin_music_active_artist">
                                            <span>🎙️ Tác Giả / Nghệ Sĩ:</span>
                                            <strong id="active_music_artist">{{ $currentArtist }}</strong>
                                        </div>
                                        <div class="mayhen_admin_music_active_url" id="active_music_url_preview">
                                            {{ $currentMusicUrl }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons & Hidden/Manual Input -->
                                <div class="mayhen_admin_music_actions_row">
                                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="openMusicPickerModal()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 18V5l12-2v13"></path>
                                            <circle cx="6" cy="18" r="3"></circle>
                                            <circle cx="18" cy="16" r="3"></circle>
                                        </svg>
                                        <span>🎶 Mở Thư Viện Chọn Nhạc (Popup)</span>
                                    </button>

                                    <label class="mayhen_admin_file_upload_btn">
                                        <input type="file" name="music_file" accept="audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/m4a" style="display: none;" onchange="previewAudioFileInput(this)">
                                        <span>📁 Tải Lên MP3 Riêng</span>
                                    </label>

                                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="resetToTemplateDefaultMusic('/mau-thiep/{{ $template->slug }}/assets/wedding-song.mp3', 'Giai Điệu Gốc ({{ e($template->name) }})', '{{ e($templateEditorBrandName) }} Studio')">
                                        <span>↺ Đặt Lại Nhạc Gốc Mẫu</span>
                                    </button>

                                    <span class="mayhen_admin_storage_pill" style="margin-left: auto;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Tự động dọn file cũ trong Storage
                                    </span>
                                </div>

                                <!-- URL Input (Editable if direct URL needed) -->
                                <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                    <span style="font-size: 0.75rem; color: #64748B; white-space: nowrap;">Đường dẫn file:</span>
                                    <input 
                                        type="text" 
                                        id="custom_music_url_input" 
                                        name="music_url" 
                                        value="{{ old('music_url', $currentMusicUrl) }}" 
                                        class="mayhen_admin_form_input mayhen_admin_asset_url_input" 
                                        placeholder="Đường dẫn bài hát hoặc URL..."
                                        oninput="onManualMusicUrlChange(this.value)"
                                    >
                                </div>

                                <!-- Audio Player -->
                                <div>
                                    <div class="mayhen_admin_form_help" style="margin-bottom: 6px;">
                                        Nghe thử trực tiếp bài hát mẫu sẽ phát khi khách mở thiệp cưới:
                                    </div>
                                    <audio controls id="admin_showcase_audio_player" class="mayhen_admin_audio_player" src="{{ $currentMusicUrl }}">
                                        Trình duyệt không hỗ trợ thẻ audio.
                                    </audio>
                                </div>
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
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Mẹ Chú Rể:</label>
                                    <input type="text" name="groom_mother" value="{{ old('groom_mother', $cfg['groom_mother'] ?? 'Trần Thị Thu Trang') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Địa Chỉ Tư Gia Nhà Trai:</label>
                                    <input type="text" name="groom_parents_address" value="{{ old('groom_parents_address', $cfg['groom_parents_address'] ?? 'Thôn Hưng Đạo, Xã An Đạo, H. Phù Ninh, Phú Thọ') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Thôn Hưng Đạo, Xã An Đạo, H. Phù Ninh, Phú Thọ">
                                </div>
                            </div>

                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Song Thân Nhà Gái:</strong>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Bố Cô Dâu:</label>
                                    <input type="text" name="bride_father" value="{{ old('bride_father', $cfg['bride_father'] ?? 'Phạm Văn Hùng') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Họ Tên Mẹ Cô Dâu:</label>
                                    <input type="text" name="bride_mother" value="{{ old('bride_mother', $cfg['bride_mother'] ?? 'Nguyễn Thị Tuyết') }}" class="mayhen_admin_form_input">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Địa Chỉ Tư Gia Nhà Gái:</label>
                                    <input type="text" name="bride_parents_address" value="{{ old('bride_parents_address', $cfg['bride_parents_address'] ?? 'Phường Vĩnh Phúc, Quận Ba Đình, TP. Hà Nội') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Phường Vĩnh Phúc, Quận Ba Đình, TP. Hà Nội">
                                </div>
                            </div>
                        </div>

                        <!-- Bio Chú Rể & Cô Dâu -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <polyline points="17 11 19 13 23 9"></polyline>
                            </svg>
                            <span>Tâm Sự Chân Dung Đôi Uyên Ương (Profile Bio)</span>
                        </div>

                        <div class="mayhen_admin_form_grid_2">
                            <div class="mayhen_admin_form_group">
                                <label for="groom_bio" class="mayhen_admin_form_label">Tâm Sự Chú Rể</label>
                                <textarea id="groom_bio" name="groom_bio" class="mayhen_admin_form_textarea" style="min-height: 80px;" placeholder="Lời tâm sự ngắn hoặc châm ngôn sống của Chú Rể...">{{ old('groom_bio', $cfg['groom_bio'] ?? 'Chàng trai luôn tin rằng hạnh phúc giản đơn là được cùng em đi qua những tháng ngày bình yên.') }}</textarea>
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="bride_bio" class="mayhen_admin_form_label">Tâm Sự Cô Dâu</label>
                                <textarea id="bride_bio" name="bride_bio" class="mayhen_admin_form_textarea" style="min-height: 80px;" placeholder="Lời tâm sự ngắn hoặc châm ngôn sống của Cô Dâu...">{{ old('bride_bio', $cfg['bride_bio'] ?? 'Cô gái nhỏ luôn mỉm cười vì biết rằng con đường phía trước luôn có anh che chở và sẻ chia.') }}</textarea>
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
                            <span>Thời Gian & Địa Điểm Tiệc Chính (Nhà Trai) Trưng Bày</span>
                        </div>

                        <div class="mayhen_admin_form_grid_3">
                            <div class="mayhen_admin_form_group">
                                <label for="wedding_date" class="mayhen_admin_form_label">Ngày Cưới Dương Lịch</label>
                                <input type="date" id="wedding_date" name="wedding_date" value="{{ old('wedding_date', $cfg['wedding_date'] ?? '2026-12-20') }}" class="mayhen_admin_form_input">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="party_time" class="mayhen_admin_form_label">Giờ Cử Hành Tiệc</label>
                                <input type="text" id="party_time" name="party_time" value="{{ old('party_time', $cfg['party_time'] ?? '17:30') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: 10:00 hoặc 17:30">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="wedding_lunar_date" class="mayhen_admin_form_label">Ngày Âm Lịch Trưng Bày</label>
                                <input type="text" id="wedding_lunar_date" name="wedding_lunar_date" value="{{ old('wedding_lunar_date', $cfg['wedding_lunar_date'] ?? 'Tức ngày 13 tháng 10 năm Bính Ngọ') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Tức ngày 13 tháng 10 năm Bính Ngọ">
                            </div>
                        </div>

                        <div class="mayhen_admin_form_grid_2">
                            <div class="mayhen_admin_form_group">
                                <label for="party_venue" class="mayhen_admin_form_label">Tên Địa Điểm / Sảnh Tiệc Nhà Trai</label>
                                <input type="text" id="party_venue" name="party_venue" value="{{ old('party_venue', $cfg['party_venue'] ?? ($cfg['venue_name'] ?? 'NHÀ VĂN HÓA TỔ 6 NHÂN ĐẠO')) }}" class="mayhen_admin_form_input" placeholder="Ví dụ: NHÀ VĂN HÓA TỔ 6 NHÂN ĐẠO hoặc WHITE PALACE">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="party_map_url" class="mayhen_admin_form_label">Link Bản Đồ Google Maps</label>
                                <input type="text" id="party_map_url" name="party_map_url" value="{{ old('party_map_url', $cfg['party_map_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="https://maps.google.com/...">
                            </div>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label for="event_party_address" class="mayhen_admin_form_label">Địa Chỉ Cử Hành Tiệc Nhà Trai</label>
                            <input type="text" id="event_party_address" name="event_party_address" value="{{ old('event_party_address', $cfg['event_party_address'] ?? ($cfg['venue_address'] ?? 'TDP Nhân Sơn, P. Yên Nghĩa, TP. Hà Nội')) }}" class="mayhen_admin_form_input" placeholder="Địa chỉ chi tiết nơi diễn ra hôn lễ...">
                        </div>

                        <!-- Các Buổi Lễ Chi Tiết (Lễ Ăn Hỏi, Lễ Thành Hôn Tư Gia, Tiệc Cưới Nhà Gái) -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Các Buổi Lễ Chi Tiết (Lễ Ăn Hỏi, Thành Hôn Tư Gia & Tiệc Nhà Gái)</span>
                        </div>

                        <!-- Lễ Ăn Hỏi -->
                        <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border); margin-bottom: 14px;">
                            <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Lễ Ăn Hỏi (Nhà Gái):</strong>
                            <div class="mayhen_admin_form_grid_3" style="margin-bottom: 10px;">
                                <div>
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Giờ Cử Hành:</label>
                                    <input type="text" name="engagement_time" value="{{ old('engagement_time', $cfg['engagement_time'] ?? '08:00') }}" class="mayhen_admin_form_input" placeholder="08:00">
                                </div>
                                <div>
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Địa Điểm:</label>
                                    <input type="text" name="engagement_venue" value="{{ old('engagement_venue', $cfg['engagement_venue'] ?? 'TƯ GIA NHÀ GÁI') }}" class="mayhen_admin_form_input" placeholder="TƯ GIA NHÀ GÁI">
                                </div>
                                <div>
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Ngày Âm Lịch:</label>
                                    <input type="text" name="engagement_lunar_date" value="{{ old('engagement_lunar_date', $cfg['engagement_lunar_date'] ?? '(Tức ngày 13 tháng 10 năm Bính Ngọ)') }}" class="mayhen_admin_form_input" placeholder="(Tức ngày 13 tháng 10 năm Bính Ngọ)">
                                </div>
                            </div>
                            <div class="mayhen_admin_form_grid_3">
                                <div>
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Thứ trong tuần:</label>
                                    <input type="text" name="engagement_weekday" value="{{ old('engagement_weekday', $cfg['engagement_weekday'] ?? 'CHỦ NHẬT') }}" class="mayhen_admin_form_input" placeholder="CHỦ NHẬT">
                                </div>
                                <div>
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Ngày (Số):</label>
                                    <input type="text" name="engagement_day" value="{{ old('engagement_day', $cfg['engagement_day'] ?? '21') }}" class="mayhen_admin_form_input" placeholder="21">
                                </div>
                                <div>
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Tháng - Năm:</label>
                                    <input type="text" name="engagement_month_year" value="{{ old('engagement_month_year', $cfg['engagement_month_year'] ?? '11 - 2026') }}" class="mayhen_admin_form_input" placeholder="11 - 2026">
                                </div>
                            </div>
                        </div>

                        <!-- Lễ Thành Hôn Tư Gia & Tiệc Nhà Gái Grid -->
                        <div class="mayhen_admin_form_grid_2">
                            <!-- Lễ Thành Hôn Tư Gia Nhà Trai -->
                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Lễ Thành Hôn Tư Gia (Nhà Trai):</strong>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Giờ Cử Hành:</label>
                                    <input type="text" name="ceremony_time" value="{{ old('ceremony_time', $cfg['ceremony_time'] ?? '13:00') }}" class="mayhen_admin_form_input" placeholder="13:00">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Địa Điểm:</label>
                                    <input type="text" name="ceremony_venue" value="{{ old('ceremony_venue', $cfg['ceremony_venue'] ?? 'TƯ GIA NHÀ TRAI') }}" class="mayhen_admin_form_input" placeholder="TƯ GIA NHÀ TRAI">
                                </div>
                            </div>

                            <!-- Tiệc Cưới Nhà Gái -->
                            <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); display: block; margin-bottom: 10px;">Tiệc Cưới Nhà Gái:</strong>
                                <div class="mayhen_admin_form_grid_2" style="margin-bottom: 10px;">
                                    <div>
                                        <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Giờ Khai Tiệc:</label>
                                        <input type="text" name="bride_party_time" value="{{ old('bride_party_time', $cfg['bride_party_time'] ?? '16:30') }}" class="mayhen_admin_form_input" placeholder="16:30">
                                    </div>
                                    <div>
                                        <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Tên Địa Điểm:</label>
                                        <input type="text" name="bride_party_venue" value="{{ old('bride_party_venue', $cfg['bride_party_venue'] ?? 'NHÀ VĂN HÓA TỔ 5 QUANG LÃM') }}" class="mayhen_admin_form_input" placeholder="NHÀ VĂN HÓA...">
                                    </div>
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 10px;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Địa Chỉ Tiệc Nhà Gái:</label>
                                    <input type="text" name="bride_party_address" value="{{ old('bride_party_address', $cfg['bride_party_address'] ?? 'Phường Phú Lương, TP. Hà Nội') }}" class="mayhen_admin_form_input" placeholder="Địa chỉ tiệc nhà gái...">
                                </div>
                                <div class="mayhen_admin_form_group" style="margin-bottom: 0;">
                                    <label class="mayhen_admin_form_label" style="font-size: 0.75rem;">Link Bản Đồ Nhà Gái:</label>
                                    <input type="text" name="bride_party_map_url" value="{{ old('bride_party_map_url', $cfg['bride_party_map_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="https://maps.google.com/...">
                                </div>
                            </div>
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

                        <!-- Bộ Ba Ảnh Nghệ Thuật (Triptych 3 Khung Vòm) -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                                <line x1="15" y1="3" x2="15" y2="21"></line>
                            </svg>
                            <span>Bộ Ba Ảnh Nghệ Thuật (Triptych 3 Khung Vòm)</span>
                        </div>

                        <div class="mayhen_admin_form_grid_3">
                            <!-- Triptych Left -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Vòm Trái</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['triptych_left_url'] ?? $template->thumbnail_url }}" alt="Triptych Left" class="mayhen_admin_asset_thumb mayhen_admin_asset_thumb_tall" id="preview_triptych_left">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="triptych_left_url" value="{{ old('triptych_left_url', $cfg['triptych_left_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tệp assets hoặc URL">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="triptych_left_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_triptych_left')">
                                                <span>📁 Đổi Ảnh</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn file cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Triptych Center -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Vòm Giữa (Trọng Tâm)</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['triptych_center_url'] ?? $template->thumbnail_url }}" alt="Triptych Center" class="mayhen_admin_asset_thumb mayhen_admin_asset_thumb_tall" id="preview_triptych_center">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="triptych_center_url" value="{{ old('triptych_center_url', $cfg['triptych_center_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tệp assets hoặc URL">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="triptych_center_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_triptych_center')">
                                                <span>📁 Đổi Ảnh</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn file cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Triptych Right -->
                            <div class="mayhen_admin_form_group">
                                <label class="mayhen_admin_form_label">Ảnh Vòm Phải</label>
                                <div class="mayhen_admin_asset_field_box">
                                    <img src="{{ $cfg['triptych_right_url'] ?? $template->thumbnail_url }}" alt="Triptych Right" class="mayhen_admin_asset_thumb mayhen_admin_asset_thumb_tall" id="preview_triptych_right">
                                    <div class="mayhen_admin_asset_controls">
                                        <input type="text" name="triptych_right_url" value="{{ old('triptych_right_url', $cfg['triptych_right_url'] ?? '') }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tệp assets hoặc URL">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label class="mayhen_admin_file_upload_btn">
                                                <input type="file" name="triptych_right_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_triptych_right')">
                                                <span>📁 Đổi Ảnh</span>
                                            </label>
                                            <span class="mayhen_admin_storage_pill">Tự dọn file cũ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ảnh Tròn Chân Trang (Footer Couple Circle) -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <span>Ảnh Tròn Chân Trang Thiệp (Footer Couple Circle)</span>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label class="mayhen_admin_form_label">Ảnh Cặp Đôi Vòng Tròn Khép Thiệp</label>
                            <div class="mayhen_admin_asset_field_box" style="max-width: 420px;">
                                <img src="{{ $cfg['footer_photo_url'] ?? ($cfg['couple_photo_url'] ?? $template->thumbnail_url) }}" alt="Footer Circle" class="mayhen_admin_asset_thumb" style="border-radius: 50%;" id="preview_footer_img">
                                <div class="mayhen_admin_asset_controls">
                                    <input type="text" name="footer_photo_url" value="{{ old('footer_photo_url', $cfg['footer_photo_url'] ?? ($cfg['couple_photo_url'] ?? '')) }}" class="mayhen_admin_form_input mayhen_admin_asset_url_input" placeholder="Tên tệp assets hoặc URL ảnh">
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <label class="mayhen_admin_file_upload_btn">
                                            <input type="file" name="footer_photo_file" accept="image/*" style="display: none;" onchange="previewImageInput(this, 'preview_footer_img')">
                                            <span>📁 Tải Ảnh Tròn Mới</span>
                                        </label>
                                        <span class="mayhen_admin_storage_pill">Tự dọn ảnh cũ</span>
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
                        <!-- Slogan & Lời Ngỏ Cặp Đôi -->
                        <div class="mayhen_admin_group_heading">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span>Slogan Trang Bìa, Lời Ngỏ & Trích Dẫn Tình Yêu</span>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label for="slogan" class="mayhen_admin_form_label">Slogan Lãng Mạn Trang Bìa</label>
                            <input type="text" id="slogan" name="slogan" value="{{ old('slogan', $cfg['slogan'] ?? 'Tình yêu nở hoa khi hai trái tim chung một nhịp đập.') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Tình yêu nở hoa khi hai trái tim chung một nhịp đập.">
                        </div>

                        <div class="mayhen_admin_form_grid_2">
                            <div class="mayhen_admin_form_group">
                                <label for="story_title" class="mayhen_admin_form_label">Tiêu Đề Lời Ngỏ</label>
                                <input type="text" id="story_title" name="story_title" value="{{ old('story_title', $cfg['story_title'] ?? 'Đám Cưới Của Chúng Mình') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: Đám Cưới Của Chúng Mình">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="story_subtitle" class="mayhen_admin_form_label">Phụ Đề Lời Ngỏ (Kicker / Subtitle)</label>
                                <input type="text" id="story_subtitle" name="story_subtitle" value="{{ old('story_subtitle', $cfg['story_subtitle'] ?? 'NGÀY TÌNH YÊU ĐƠM HOA') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: NGÀY TÌNH YÊU ĐƠM HOA">
                            </div>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label for="couple_quote" class="mayhen_admin_form_label">Câu Châm Ngôn / Trích Dẫn Tình Yêu (Quote)</label>
                            <textarea id="couple_quote" name="couple_quote" class="mayhen_admin_form_textarea" style="min-height: 70px;" placeholder="Câu thơ hoặc trích dẫn tình yêu thi vị...">{{ old('couple_quote', $cfg['couple_quote'] ?? "Cảm ơn đời mỗi sớm mai thức dậy\nCho ta thêm ngày nữa để yêu thương.") }}</textarea>
                        </div>

                        <div class="mayhen_admin_form_group">
                            <label for="story_content" class="mayhen_admin_form_label">Đoạn Văn Lời Ngỏ & Tâm Sự Tình Yêu</label>
                            <textarea id="story_content" name="story_content" class="mayhen_admin_form_textarea" style="min-height: 80px;" placeholder="Đoạn văn tâm sự mở đầu thiệp cưới...">{{ old('story_content', $cfg['story_content'] ?? 'Chúng mình tin rằng tình yêu chân thành không phải là tìm được một người hoàn hảo, mà là cùng nhau hoàn thiện qua từng ngày.') }}</textarea>
                        </div>

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

                        <div class="mayhen_admin_form_grid_2">
                            <div class="mayhen_admin_form_group">
                                <label for="ending_title" class="mayhen_admin_form_label">Tiêu Đề Lời Cảm Ơn Cuối Thiệp</label>
                                <input type="text" id="ending_title" name="ending_title" value="{{ old('ending_title', $cfg['ending_title'] ?? 'THANK YOU') }}" class="mayhen_admin_form_input" placeholder="Ví dụ: THANK YOU hoặc CẢM ƠN">
                            </div>

                            <div class="mayhen_admin_form_group">
                                <label for="ending_text" class="mayhen_admin_form_label">Lời Cảm Ơn Cuối Thiệp (Thank You Note)</label>
                                <textarea id="ending_text" name="ending_text" class="mayhen_admin_form_textarea" style="min-height: 60px;">{{ old('ending_text', $cfg['ending_text'] ?? ($cfg['footer_thank_you_text'] ?? 'Sự hiện diện và lời chúc phúc của quý khách là niềm vinh hạnh to lớn và là món quà ý nghĩa nhất đối với gia đình chúng mình!')) }}</textarea>
                                <input type="hidden" name="footer_thank_you_text" id="footer_thank_you_text" value="{{ old('ending_text', $cfg['ending_text'] ?? ($cfg['footer_thank_you_text'] ?? '')) }}">
                            </div>
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

    <!-- ========================================================================== -->
    <!-- POPUP MODAL: THƯ VIỆN BÀI HÁT THIỆP CƯỚI (PHÂN TÁCH RÕ TÁC GIẢ & BÀI NHẠC) -->
    <!-- ========================================================================== -->
    <div id="mayhen_admin_music_modal" class="mayhen_admin_modal_overlay" role="dialog" aria-modal="true" aria-labelledby="music_modal_title">
        <div class="mayhen_admin_music_modal_dialog">
            <!-- Modal Header -->
            <div class="mayhen_admin_modal_header">
                <div class="mayhen_admin_modal_title_group">
                    <h3 id="music_modal_title">
                        <span>🎶</span>
                        <span>Thư Viện Bài Hát Thiệp Cưới {{ \App\Models\PlatformSetting::getBrandName() }}</span>
                    </h3>
                    <p>Khám phá bộ sưu tập giai điệu lãng mạn — Phân tách rõ Tác Giả, Bài Nhạc & Nghe thử trực tiếp</p>
                </div>
                <button type="button" class="mayhen_admin_modal_close_btn" onclick="closeMusicPickerModal()" aria-label="Đóng popup">&times;</button>
            </div>

            <!-- Modal Search & Category Filter Toolbar -->
            <div class="mayhen_admin_music_toolbar">
                <div class="mayhen_admin_music_search_box">
                    <svg class="mayhen_admin_music_search_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="music_modal_search_input" placeholder="🔍 Tìm kiếm nhanh theo Tên bài hát, Tác giả / Ca sĩ, Thể loại (ví dụ: Canon, Kai Đinh, Yiruma, Acoustic, Piano)..." autocomplete="off" oninput="filterMusicList()">
                </div>
                <div class="mayhen_admin_music_filter_chips">
                    <span class="mayhen_admin_music_chip is-active" data-filter="all" onclick="setMusicCategoryFilter(this, 'all')">Tất Cả ({{ count($musicCatalog) }})</span>
                    <span class="mayhen_admin_music_chip" data-filter="Tuyển Chọn" onclick="setMusicCategoryFilter(this, 'Tuyển Chọn')">✨ Tuyển Chọn</span>
                    <span class="mayhen_admin_music_chip" data-filter="Classical" onclick="setMusicCategoryFilter(this, 'Classical')">🎻 Cổ Điển</span>
                    <span class="mayhen_admin_music_chip" data-filter="Piano" onclick="setMusicCategoryFilter(this, 'Piano')">🎹 Piano Solo</span>
                    <span class="mayhen_admin_music_chip" data-filter="Acoustic" onclick="setMusicCategoryFilter(this, 'Acoustic')">🎸 Acoustic</span>
                    <span class="mayhen_admin_music_chip" data-filter="V-Pop" onclick="setMusicCategoryFilter(this, 'V-Pop')">🇻🇳 Nhạc Việt</span>
                    <span class="mayhen_admin_music_chip" data-filter="Theo Mẫu" onclick="setMusicCategoryFilter(this, 'Theo Mẫu')">💿 Theo Mẫu</span>
                </div>
            </div>

            <!-- Modal Table Container -->
            <div class="mayhen_admin_music_table_wrap">
                <table class="mayhen_admin_music_table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">Nghe</th>
                            <th style="width: 32%;">Tên Bài Nhạc</th>
                            <th style="width: 28%;">Tác Giả / Nghệ Sĩ</th>
                            <th style="width: 20%;">Thể Loại / Cảm Xúc</th>
                            <th style="width: 60px; text-align: center;">Thời Lượng</th>
                            <th style="width: 120px; text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="music_modal_table_body">
                        @foreach($musicCatalog as $song)
                            <tr class="music-row {{ ($currentMusicUrl === $song['url']) ? 'is-current' : '' }}" 
                                data-title="{{ $song['title'] }}" 
                                data-artist="{{ $song['artist'] }}" 
                                data-genre="{{ $song['genre'] ?? '' }}" 
                                data-tag="{{ $song['tag'] ?? '' }}"
                                data-url="{{ $song['url'] }}">
                                <td style="text-align: center;">
                                    <button type="button" class="mayhen_admin_modal_play_btn" onclick="toggleModalAudioPreview(this, '{{ $song['url'] }}')" title="Nghe thử bài hát này">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                        </svg>
                                    </button>
                                </td>
                                <td>
                                    <div class="mayhen_admin_music_row_title">{{ $song['title'] }}</div>
                                    @if(!empty($song['description']))
                                        <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px;">{{ $song['description'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="mayhen_admin_music_row_artist">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mh-admin-primary);">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span>{{ $song['artist'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="mayhen_admin_music_row_genre">{{ $song['genre'] ?? 'Lãng Mạn' }}</span>
                                </td>
                                <td style="text-align: center; font-size: 0.8125rem; color: #64748B;">
                                    {{ $song['duration'] ?? '--:--' }}
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="mayhen_admin_modal_select_btn {{ ($currentMusicUrl === $song['url']) ? 'is-selected' : '' }}" 
                                        onclick="selectMusicTrack('{{ addslashes($song['url']) }}', '{{ addslashes($song['title']) }}', '{{ addslashes($song['artist']) }}', '{{ addslashes($song['genre'] ?? 'Nhạc Trưng Bày') }}')">
                                        {{ ($currentMusicUrl === $song['url']) ? '✓ Đang Chọn' : 'Chọn Bài Này' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div id="music_modal_empty" style="display: none; padding: 40px 20px; text-align: center; color: #94A3B8;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <p style="font-size: 0.875rem; margin: 0;">Không tìm thấy bài hát nào khớp với từ khóa tìm kiếm.</p>
                </div>
            </div>

            <!-- Hidden Audio Element for Modal Auditioning -->
            <audio id="modal_audition_player" style="display: none;"></audio>

            <!-- Modal Footer -->
            <div class="mayhen_admin_modal_footer">
                <div class="mayhen_admin_modal_status">
                    <span id="music_modal_counter">Hiển thị {{ count($musicCatalog) }} / {{ count($musicCatalog) }} bài hát</span>
                    <span id="music_modal_now_playing" class="mayhen_admin_modal_now_playing" style="display: none;">
                        <span>▶ Đang nghe thử:</span>
                        <strong id="music_modal_now_playing_name"></strong>
                    </span>
                </div>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="closeMusicPickerModal()">
                    <span>Đóng (ESC)</span>
                </button>
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

    // Full Page Scroll Preview Controls
    function previewFullPageImageInput(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                const targetImg = document.getElementById('preview_full_page_img');
                if (targetImg) {
                    targetImg.src = e.target.result;
                    targetImg.onload = function() {
                        setupPhoneMockupScrollSpeed(this);
                    };
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function updateFullPreviewFromUrl(url) {
        const targetImg = document.getElementById('preview_full_page_img');
        if (targetImg && url && url.trim() !== '') {
            targetImg.src = url.trim();
            targetImg.onload = function() {
                setupPhoneMockupScrollSpeed(this);
            };
        }
    }

    function selectDetectedLocalAsset(url) {
        const urlInput = document.getElementById('full_page_preview_url_input');
        if (urlInput) {
            urlInput.value = url;
            updateFullPreviewFromUrl(url);
        }
    }

    function resetFullPreviewField() {
        const urlInput = document.getElementById('full_page_preview_url_input');
        const fileInput = document.getElementById('full_page_preview_file_input');
        if (urlInput) urlInput.value = '';
        if (fileInput) fileInput.value = '';
        const targetImg = document.getElementById('preview_full_page_img');
        if (targetImg) {
            targetImg.src = '{{ $template->thumbnail_url }}';
        }
    }

    let isMockupScrolling = false;
    function toggleScrollSimulation() {
        const mockup = document.getElementById('phone_mockup_container');
        const btnText = document.getElementById('scroll_simulation_btn_text');
        if (!mockup) return;

        isMockupScrolling = !isMockupScrolling;
        if (isMockupScrolling) {
            mockup.classList.add('is-animating');
            if (btnText) btnText.textContent = '⏸ Dừng';
        } else {
            mockup.classList.remove('is-animating');
            if (btnText) btnText.textContent = '▶ Cuộn Thử';
        }
    }

    function setupPhoneMockupScrollSpeed(img) {
        const mockupInner = document.getElementById('phone_mockup_inner');
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

    window.addEventListener('load', function() {
        const fullImg = document.getElementById('preview_full_page_img');
        if (fullImg) {
            if (fullImg.complete) {
                setupPhoneMockupScrollSpeed(fullImg);
            } else {
                fullImg.addEventListener('load', function() {
                    setupPhoneMockupScrollSpeed(this);
                });
            }
        }
    });

    // =========================================================================
    // MUSIC PICKER POPUP MODAL & SHOWCASE AUDIO CONTROLS
    // =========================================================================
    let currentAuditionBtn = null;
    let activeMusicCategory = 'all';
    const auditionAudio = document.getElementById('modal_audition_player');
    const mainShowcaseAudio = document.getElementById('admin_showcase_audio_player');
    const discIcon = document.getElementById('admin_music_disc_icon');

    // Spin disc when main audio plays
    if (mainShowcaseAudio && discIcon) {
        mainShowcaseAudio.addEventListener('play', () => discIcon.classList.add('is-spinning'));
        mainShowcaseAudio.addEventListener('pause', () => discIcon.classList.remove('is-spinning'));
        mainShowcaseAudio.addEventListener('ended', () => discIcon.classList.remove('is-spinning'));
    }

    function openMusicPickerModal() {
        const modal = document.getElementById('mayhen_admin_music_modal');
        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            const searchInput = document.getElementById('music_modal_search_input');
            if (searchInput) {
                searchInput.value = '';
                filterMusicList();
                setTimeout(() => searchInput.focus(), 60);
            }
        }
    }

    function closeMusicPickerModal() {
        const modal = document.getElementById('mayhen_admin_music_modal');
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            stopModalAudition();
        }
    }

    // Close on outside click or ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMusicPickerModal();
        }
    });

    const musicModalOverlay = document.getElementById('mayhen_admin_music_modal');
    if (musicModalOverlay) {
        musicModalOverlay.addEventListener('click', (e) => {
            if (e.target === musicModalOverlay) {
                closeMusicPickerModal();
            }
        });
    }

    // Filter by category chip
    function setMusicCategoryFilter(chip, category) {
        activeMusicCategory = category;
        document.querySelectorAll('.mayhen_admin_music_chip').forEach(c => c.classList.remove('is-active'));
        chip.classList.add('is-active');
        filterMusicList();
    }

    // Filter tracks by keyword and active category
    function filterMusicList() {
        const queryInput = document.getElementById('music_modal_search_input');
        const query = queryInput ? queryInput.value.trim().toLowerCase() : '';
        const normalizedQuery = typeof removeVietnameseAccents === 'function' ? removeVietnameseAccents(query) : query;

        const rows = document.querySelectorAll('#music_modal_table_body .music-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const title = (row.getAttribute('data-title') || '').toLowerCase();
            const artist = (row.getAttribute('data-artist') || '').toLowerCase();
            const genre = (row.getAttribute('data-genre') || '').toLowerCase();
            const tag = (row.getAttribute('data-tag') || '').toLowerCase();

            const normalizedFullText = typeof removeVietnameseAccents === 'function' 
                ? removeVietnameseAccents(`${title} ${artist} ${genre} ${tag}`)
                : `${title} ${artist} ${genre} ${tag}`;

            // Check category filter
            let matchCategory = true;
            if (activeMusicCategory !== 'all') {
                const normCat = (activeMusicCategory || '').toLowerCase();
                matchCategory = genre.includes(normCat) || tag.includes(normCat) || title.includes(normCat);
            }

            // Check keyword
            const matchKeyword = !normalizedQuery || normalizedFullText.includes(normalizedQuery);

            if (matchCategory && matchKeyword) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Counter & empty state
        const counter = document.getElementById('music_modal_counter');
        if (counter) {
            counter.textContent = `Hiển thị ${visibleCount} / ${rows.length} bài hát`;
        }

        const emptyBox = document.getElementById('music_modal_empty');
        if (emptyBox) {
            emptyBox.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // Inline audio auditioning in modal
    function toggleModalAudioPreview(btn, url) {
        if (!auditionAudio) return;

        if (currentAuditionBtn === btn && !auditionAudio.paused) {
            stopModalAudition();
            return;
        }

        stopModalAudition();

        currentAuditionBtn = btn;
        btn.classList.add('is-playing');
        btn.title = 'Tạm dừng nghe thử';
        btn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>`;

        const row = btn.closest('.music-row');
        const title = row ? row.getAttribute('data-title') : '';
        const nowPlayingWrap = document.getElementById('music_modal_now_playing');
        const nowPlayingName = document.getElementById('music_modal_now_playing_name');
        if (nowPlayingWrap && nowPlayingName) {
            nowPlayingName.textContent = title;
            nowPlayingWrap.style.display = 'inline-flex';
        }

        auditionAudio.src = url;
        auditionAudio.play().catch(e => console.warn('Audio play request interrupted:', e));

        auditionAudio.onended = () => {
            stopModalAudition();
        };
    }

    function stopModalAudition() {
        if (auditionAudio) {
            auditionAudio.pause();
            auditionAudio.currentTime = 0;
        }
        if (currentAuditionBtn) {
            currentAuditionBtn.classList.remove('is-playing');
            currentAuditionBtn.title = 'Nghe thử bài hát này';
            currentAuditionBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>`;
            currentAuditionBtn = null;
        }
        const nowPlayingWrap = document.getElementById('music_modal_now_playing');
        if (nowPlayingWrap) {
            nowPlayingWrap.style.display = 'none';
        }
    }

    // Select track from modal
    function selectMusicTrack(url, title, artist, genre) {
        // 1. Update text input
        const input = document.getElementById('custom_music_url_input');
        if (input) {
            input.value = url;
        }

        // 2. Update active song display banner
        const titleEl = document.getElementById('active_music_title');
        const artistEl = document.getElementById('active_music_artist');
        const genreEl = document.getElementById('active_music_genre_badge');
        const urlEl = document.getElementById('active_music_url_preview');

        if (titleEl) titleEl.textContent = title;
        if (artistEl) artistEl.textContent = artist;
        if (genreEl) genreEl.textContent = genre;
        if (urlEl) urlEl.textContent = url;

        // 3. Update main showcase audio player
        updateAudioPlayerSrc(url);

        // 4. Update row active states
        document.querySelectorAll('#music_modal_table_body .music-row').forEach(row => {
            const rowUrl = row.getAttribute('data-url');
            const selectBtn = row.querySelector('.mayhen_admin_modal_select_btn');
            if (rowUrl === url) {
                row.classList.add('is-current');
                if (selectBtn) {
                    selectBtn.classList.add('is-selected');
                    selectBtn.textContent = '✓ Đang Chọn';
                }
            } else {
                row.classList.remove('is-current');
                if (selectBtn) {
                    selectBtn.classList.remove('is-selected');
                    selectBtn.textContent = 'Chọn Bài Này';
                }
            }
        });

        closeMusicPickerModal();
        if (window.mayhenAdminToast) {
            window.mayhenAdminToast(`Đã chọn nhạc: ${title} - ${artist}`);
        }
    }

    // Reset to template default music
    function resetToTemplateDefaultMusic(url, title, artist) {
        selectMusicTrack(url, title, artist, 'Theo Mẫu Thiệp');
    }

    // Handle manual URL text input edit
    function onManualMusicUrlChange(url) {
        updateAudioPlayerSrc(url);
        const titleEl = document.getElementById('active_music_title');
        const artistEl = document.getElementById('active_music_artist');
        const genreEl = document.getElementById('active_music_genre_badge');
        const urlEl = document.getElementById('active_music_url_preview');

        if (urlEl) urlEl.textContent = url || 'Chưa thiết lập URL';
        if (titleEl) titleEl.textContent = url ? 'Giai Điệu Tùy Chỉnh' : 'Chưa Chọn Bài Hát';
        if (artistEl) artistEl.textContent = 'Tệp / URL Bên Ngoài';
        if (genreEl) genreEl.textContent = 'Tùy Chỉnh';
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
            const fileName = input.files[0].name;
            const fileUrl = URL.createObjectURL(input.files[0]);
            updateAudioPlayerSrc(fileUrl);

            const inputTxt = document.getElementById('custom_music_url_input');
            if (inputTxt) {
                inputTxt.value = '[Tệp mới tải lên: ' + fileName + ']';
            }

            const titleEl = document.getElementById('active_music_title');
            const artistEl = document.getElementById('active_music_artist');
            const genreEl = document.getElementById('active_music_genre_badge');
            const urlEl = document.getElementById('active_music_url_preview');

            if (titleEl) titleEl.textContent = fileName.replace(/\.[^/.]+$/, "");
            if (artistEl) artistEl.textContent = 'Tệp Tải Lên Mới (Sẽ lưu vào Storage)';
            if (genreEl) genreEl.textContent = 'Tệp Riêng';
            if (urlEl) urlEl.textContent = '[Tệp thiết bị] ' + fileName;

            if (window.mayhenAdminToast) {
                window.mayhenAdminToast(`Đã chọn tệp tải lên: ${fileName}`);
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
