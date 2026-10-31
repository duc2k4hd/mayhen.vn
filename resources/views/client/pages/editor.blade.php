@extends('client.layouts.master')

@section('title', 'Đang chọn mẫu: "' . ($selectedTemplate ? $selectedTemplate->name : 'Thiệp Mời') . '" | ' . config('app.name'))
@section('meta_description', 'Tự tạo thiệp cưới online phong cách Quiet Luxury dễ dàng với chế độ xem thử realtime chính xác theo từng mẫu thiệp.')
@section('robots', 'noindex, nofollow')

@section('hide_chrome', 'true')
@section('body_class', 'mayhen_editor_body_fullscreen')
@section('main_class', 'mayhen_editor_main_fullscreen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/editor-v2.css') }}?v={{ file_exists(public_path('assets/client/css/pages/editor-v2.css')) ? filemtime(public_path('assets/client/css/pages/editor-v2.css')) : time() }}">
@endpush

@php
    $sections = $schema['sections'] ?? [];
    if (empty($sections)) {
        $sections = [
            [
                'id' => 'basic',
                'title' => 'Thông Tin Hôn Lễ',
                'description' => 'Nhập thông tin cơ bản của cô dâu và chú rể',
                'fields' => [
                    ['key' => 'wedding.groom_name', 'label' => 'Họ tên Chú Rể', 'type' => 'text', 'required' => true, 'default' => 'Tràng Đức'],
                    ['key' => 'wedding.bride_name', 'label' => 'Họ tên Cô Dâu', 'type' => 'text', 'required' => true, 'default' => 'Ngọc Phượng'],
                    ['key' => 'wedding.wedding_date', 'label' => 'Ngày cử hành hôn lễ', 'type' => 'date', 'required' => true, 'default' => '2027-03-07'],
                ],
            ],
        ];
    }

    $formatInputName = function (string $key): string {
        $parts = explode('.', $key);
        $first = array_shift($parts);

        return empty($parts) ? $first : $first . '[' . implode('][', $parts) . ']';
    };

    $formatOldKey = function (string $key): string {
        return str_replace(['[', ']'], ['.', ''], $key);
    };
@endphp

@section('content')
    <div class="mayhen_editor_wrapper mayhen_editor_v2_container">
        <!-- 1. Studio Topbar V2 -->
        <header class="mayhen_editor_topbar">
            <div class="mayhen_editor_topbar_left">
                <a href="{{ route('templates.index') }}" class="mayhen_editor_topbar_back_btn" title="Trở về danh sách mẫu thiệp">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <div class="mayhen_editor_topbar_title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mayhen-color-primary);">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    <span>Studio Tạo Thiệp</span>
                </div>

                <!-- Template Active Chip & Quick Switcher -->
                <button type="button" class="mayhen_editor_template_chip" data-mayhen-action="open-template-modal" title="Bấm để đổi sang mẫu thiệp khác">
                    <img src="{{ $selectedTemplate->thumbnail_url }}" alt="{{ $selectedTemplate->name }}" class="mayhen_editor_template_chip_thumb">
                    <span class="mayhen_editor_template_chip_name">{{ $selectedTemplate->name }}</span>
                    <span style="font-size: 0.6875rem; color: var(--mayhen-color-primary); font-weight: 700;">Đổi mẫu</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <!-- Autosave Live Pill -->
                <div class="mayhen_editor_save_pill" id="mayhen_save_pill" data-status="idle" title="Trạng thái lưu trữ nháp">
                    <span class="mayhen_editor_save_dot"></span>
                    <span class="mayhen_editor_save_text">Đã sẵn sàng</span>
                </div>
            </div>

            <!-- Segmented Switcher cho Mobile: Nhập Liệu ⇄ Xem Thiệp -->
            <div class="mayhen_editor_mode_switcher" role="tablist" aria-label="Chuyển chế độ màn hình">
                <button type="button" class="mayhen_editor_mode_btn active" data-editor-mode="form" role="tab" aria-selected="true">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Nhập Liệu</span>
                </button>
                <button type="button" class="mayhen_editor_mode_btn" data-editor-mode="preview" role="tab" aria-selected="false">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Xem Thiệp</span>
                </button>
            </div>

            <div class="mayhen_editor_topbar_actions">
                <!-- Undo / Redo -->
                <div class="mayhen_editor_history_group">
                    <button type="button" class="mayhen_editor_btn_history" id="mayhen_btn_undo" title="Hoàn tác (Ctrl+Z)" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                    </button>
                    <button type="button" class="mayhen_editor_btn_history" id="mayhen_btn_redo" title="Làm lại (Ctrl+Y)" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7"/></svg>
                    </button>
                </div>

                <!-- Device Preview Switcher -->
                <div class="mayhen_editor_device_group">
                    <button type="button" class="mayhen_editor_btn_device active" id="mayhen_btn_device_mobile" title="Xem trên màn hình điện thoại">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </button>
                    <button type="button" class="mayhen_editor_btn_device" id="mayhen_btn_device_desktop" title="Xem trên màn hình máy tính">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </button>
                </div>

                <!-- Submit / Publish Button -->
                <button type="button" class="mayhen_editor_btn_submit" data-mayhen-action="trigger-submit">
                    <span>Xuất Bản Thiệp</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </div>
        </header>

        <!-- 2. Main Split Studio Grid V2 -->
        <div class="mayhen_editor_v2_body">
            <!-- Zone 1: Left Tool Rail (Thanh Công Cụ Dọc Trực Quan) -->
            <aside class="mayhen_editor_left_rail">
                <button type="button" class="mayhen_editor_rail_item active" data-rail-tab="content" title="Nhập thông tin đám cưới">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Nội Dung</span>
                </button>
                <button type="button" class="mayhen_editor_rail_item" data-rail-tab="sections" title="Kéo thả sắp xếp thứ tự các phần">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>Bố Cục</span>
                </button>
                <button type="button" class="mayhen_editor_rail_item" data-rail-tab="theme" title="Chọn bảng màu Quiet Luxury & tem sáp">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                    <span>Màu Sắc</span>
                </button>
                <button type="button" class="mayhen_editor_rail_item" data-rail-tab="music" title="Kho nhạc cưới lãng mạn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                    <span>Âm Nhạc</span>
                </button>
                <button type="button" class="mayhen_editor_rail_item" data-mayhen-action="open-template-modal" title="Đổi sang mẫu thiệp khác">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    <span>Đổi Mẫu</span>
                </button>
            </aside>

            <!-- Zone 2: Contextual Drawer Subpanel (520px) -->
            <div class="mayhen_editor_drawer_panel" id="mayhen_drawer_panel">
                <!-- Subpanel 1: Form Nhập Liệu Nội Dung -->
                <div class="mayhen_editor_subpanel" data-panel-name="content" style="display: flex;">
                    <!-- Stepper Navigation Pills -->
                    <nav class="mayhen_editor_stepper_nav" aria-label="Các bước nhập liệu">
                    @foreach ($sections as $index => $section)
                        @php
                            $shortTitle = $section['short_title'] ?? match ($section['id'] ?? '') {
                                'couple' => 'Đôi Uyên Ương',
                                'groom_family', 'groom_events' => 'Nhà Trai',
                                'bride_family', 'bride_events' => 'Nhà Gái',
                                'album', 'gallery', 'gallery_and_photos' => 'Ảnh Cưới',
                                'timeline', 'story_and_timeline' => 'Lịch Trình',
                                'gift', 'banking', 'gift_and_music' => 'Mừng Cưới',
                                default => \Illuminate\Support\Str::limit($section['title'], 18)
                            };
                        @endphp
                        <button type="button" 
                                class="mayhen_editor_stepper_tab {{ $index === 0 ? 'mayhen_editor_stepper_tab_active' : '' }}" 
                                data-mayhen-step="{{ $index + 1 }}"
                                data-section-id="{{ $section['id'] }}"
                                title="{{ $section['title'] }}">
                            <span class="mayhen_editor_stepper_tab_num">{{ $index + 1 }}</span>
                            <span>{{ $shortTitle }}</span>
                        </button>
                    @endforeach
                </nav>

                <form id="mayhen_editor_form" action="{{ route('invitation.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template_id" id="template_id" value="{{ $selectedTemplate->id }}">
                    @if (isset($editingInvitation) && $editingInvitation)
                        <input type="hidden" name="invitation_id" id="invitation_id" value="{{ $editingInvitation->id }}">
                    @endif
                    <input type="hidden" name="invitation[custom_config]" id="invitation_custom_config" value="{{ json_encode($editingInvitation->custom_config ?? []) }}">
                    @php
                        $currentMusicUrl = old('wedding.music_url', $editingInvitation->wedding->music_url ?? ($selectedTemplate->default_config['music_url'] ?? ''));
                        if (empty($currentMusicUrl)) {
                            $currentMusicUrl = url('/mau-thiep/' . $selectedTemplate->slug . '/assets/wedding-song.mp3');
                        }
                    @endphp
                    <input type="hidden" name="wedding[music_url]" id="wedding_music_url" data-sync-key="wedding.music_url" value="{{ $currentMusicUrl }}">

                    @foreach ($sections as $index => $section)
                        <div class="mayhen_editor_step_content {{ $index === 0 ? 'mayhen_editor_step_active' : '' }}" data-step-panel="{{ $index + 1 }}" data-section-id="{{ $section['id'] }}">
                            <div class="mayhen_editor_step_header">
                                <h2 class="mayhen_editor_step_heading">
                                    <span>{{ $index + 1 }}. {{ $section['title'] }}</span>
                                </h2>
                                @if (!empty($section['description']))
                                    <p class="mayhen_editor_step_subtext">{{ $section['description'] }}</p>
                                @endif
                            </div>

                            <div class="mayhen_editor_fields_container mayhen_editor_fields_grid">
                                @php
                                    $fields = $section['fields'] ?? [];
                                @endphp

                                @foreach ($fields as $field)
                                    @php
                                        $fieldKey = $field['key'] ?? $field['name'] ?? null;
                                        if (empty($fieldKey)) {
                                            continue;
                                        }
                                        if (!str_contains($fieldKey, '.')) {
                                            if (in_array($fieldKey, ['groom_name', 'bride_name', 'wedding_date', 'groom_parents', 'bride_parents', 'cover_photo_url', 'music_url'])) {
                                                $fieldKey = 'wedding.' . $fieldKey;
                                            } else {
                                                $fieldKey = 'wedding.extra_data.' . $fieldKey;
                                            }
                                        }
                                        $inputName = $formatInputName($fieldKey);
                                        $oldKey = $formatOldKey($inputName);
                                        $fieldId = str_replace('.', '_', $fieldKey);

                                        // Lấy giá trị khởi tạo: ưu tiên old input -> giá trị thiệp cũ đang sửa -> default trong schema
                                        $editingVal = null;
                                        if (isset($editingInvitation) && $editingInvitation->wedding) {
                                            $cleanKey = str_replace(['wedding.', 'invitation.'], '', $fieldKey);
                                            if (str_starts_with($cleanKey, 'extra_data.')) {
                                                $extraSub = str_replace('extra_data.', '', $cleanKey);
                                                $editingVal = $editingInvitation->wedding->extra_data[$extraSub] ?? null;

                                                // Fallback tu chuoi cu neu chua co extra_data
                                                if ($editingVal === null) {
                                                    $gParents = (string) ($editingInvitation->wedding->groom_parents ?? "");
                                                    $bParents = (string) ($editingInvitation->wedding->bride_parents ?? "");
                                                    if ($extraSub === "groom_father" && preg_match("/ÔNG:\s*([^&]+?)(?=\s*(&|BÀ:|$))/ui", $gParents, $m)) {
                                                        $editingVal = trim($m[1]);
                                                    } elseif ($extraSub === "groom_mother" && preg_match("/BÀ:\s*(.+)$/ui", $gParents, $m)) {
                                                        $editingVal = trim($m[1]);
                                                    } elseif ($extraSub === "bride_father" && preg_match("/ÔNG:\s*([^&]+?)(?=\s*(&|BÀ:|$))/ui", $bParents, $m)) {
                                                        $editingVal = trim($m[1]);
                                                    } elseif ($extraSub === "bride_mother" && preg_match("/BÀ:\s*(.+)$/ui", $bParents, $m)) {
                                                        $editingVal = trim($m[1]);
                                                    } elseif ($extraSub === "groom_bank_holder") {
                                                        $editingVal = $editingInvitation->wedding->extra_data['groom_account_name'] ?? ($editingInvitation->wedding->bank_account_name ?? null);
                                                    } elseif ($extraSub === "bride_bank_holder") {
                                                        $editingVal = $editingInvitation->wedding->extra_data['bride_account_name'] ?? null;
                                                    } elseif ($extraSub === "groom_bank_account") {
                                                        $editingVal = $editingInvitation->wedding->extra_data['groom_account_number'] ?? ($editingInvitation->wedding->bank_account_number ?? null);
                                                    } elseif ($extraSub === "bride_bank_account") {
                                                        $editingVal = $editingInvitation->wedding->extra_data['bride_account_number'] ?? null;
                                                    } elseif ($extraSub === "groom_party_address") {
                                                        $editingVal = $editingInvitation->wedding->extra_data['event_party_address'] ?? ($editingInvitation->wedding->venue_address ?? null);
                                                    }
                                                }
                                            } else {
                                                $editingVal = $editingInvitation->wedding->{$cleanKey} ?? null;
                                            }
                                        }

                                        $defaultVal = $editingVal !== null ? $editingVal : ($field['default'] ?? '');
                                        $val = old($oldKey, $defaultVal);
                                        $type = $field['type'] ?? 'text';
                                        $label = $field['label'] ?? $fieldKey;
                                        $isRequired = !empty($field['required']);
                                        $isFullWidth = in_array($type, ['repeater', 'gallery', 'textarea', 'color'])
                                            || ($type === 'image' && !str_contains($fieldKey, 'groom_photo') && !str_contains($fieldKey, 'bride_photo'))
                                            || str_contains($fieldKey, 'extra_data.qr_')
                                            || str_contains($fieldKey, 'music_url')
                                            || str_contains($fieldKey, 'map_')
                                            || str_contains($fieldKey, 'address')
                                            || $fieldKey === 'wedding.wedding_date';
                                        $groupClass = 'mayhen_client_form_group' . ($isFullWidth ? ' is-full-width' : '');
                                    @endphp

                                    @if (str_contains($fieldKey, 'lunar'))
                                        {{-- Trường Ngày Âm lịch: Tự động tính toán theo Dương lịch 100%, ẩn ô nhập liệu để người dùng không phải chọn thủ công --}}
                                        <input type="hidden" 
                                               id="{{ $fieldId }}" 
                                               name="{{ $inputName }}" 
                                               data-sync-key="{{ $fieldKey }}" 
                                               value="{{ is_string($val) ? $val : '' }}"
                                               class="mayhen_auto_lunar_input">

                                    @elseif ($type === 'repeater')
                                        {{-- Trường dạng danh sách repeater (Ví dụ Timeline) --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_editor_repeater_list">
                                                @php
                                                    $items = is_array($val) ? $val : ($field['default'] ?? []);
                                                @endphp
                                                @foreach ($items as $rIdx => $rItem)
                                                    <div class="mayhen_editor_repeater_item">
                                                        <input type="time" 
                                                               name="{{ $inputName }}[{{ $rIdx }}][time]" 
                                                               class="mayhen_client_form_input mayhen_time_picker_input mayhen_picker_must_select" 
                                                               value="{{ $rItem['time'] ?? '08:00' }}" 
                                                               onkeydown="if(event.key!=='Tab'&&event.key!=='Enter'&&event.key!=='Escape')return false;"
                                                               onpaste="return false;"
                                                               data-sync-key="{{ $fieldKey }}.{{ $rIdx }}.time">
                                                        <input type="text" 
                                                               name="{{ $inputName }}[{{ $rIdx }}][title]" 
                                                               class="mayhen_client_form_input" 
                                                               value="{{ $rItem['title'] ?? '' }}" 
                                                               placeholder="Nội dung sự kiện..."
                                                               data-sync-key="{{ $fieldKey }}.{{ $rIdx }}.title">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mayhen_editor_input_help">Chỉnh sửa giờ và các mốc trình tự cử hành hôn lễ</div>
                                        </div>

                                    @elseif ($type === 'gallery')
                                        {{-- Trường Album ảnh kèm nút tải ảnh trực tiếp --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_editor_gallery_grid">
                                                @php
                                                    $defaultGalleryList = !empty($field['default']) && is_array($field['default'])
                                                        ? $field['default']
                                                        : ($selectedTemplate->default_gallery ?? []);
                                                @endphp
                                                @for ($gIdx = 0; $gIdx < ($field['max_items'] ?? 10); $gIdx++)
                                                    @php
                                                        $defaultImg = $defaultGalleryList[$gIdx] ?? '';
                                                        $gVal = is_array($val) && isset($val[$gIdx]) && !empty($val[$gIdx])
                                                            ? $val[$gIdx]
                                                            : $defaultImg;
                                                        $gId = "gallery_photo_{$gIdx}";
                                                        $galleryThumbSrc = $selectedTemplate->thumbnail_url;
                                                        if (!empty($gVal) && is_string($gVal)) {
                                                            if (str_starts_with($gVal, 'http') || str_starts_with($gVal, '/') || str_starts_with($gVal, 'data:')) {
                                                                $galleryThumbSrc = $gVal;
                                                            } else {
                                                                $galleryThumbSrc = url('/mau-thiep/' . $selectedTemplate->slug . '/assets/' . ltrim($gVal, '/'));
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="mayhen_editor_gallery_item_box">
                                                        <div class="mayhen_editor_image_field_row">
                                                            <img src="{{ $galleryThumbSrc }}" 
                                                                 alt="Ảnh {{ $gIdx + 1 }}" 
                                                                 class="mayhen_editor_image_field_preview"
                                                                 id="preview_thumb_{{ $gId }}"
                                                                 onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'">
                                                            <div style="flex: 1; display: flex; flex-direction: column; gap: 6px;">
                                                                <input type="hidden" 
                                                                       id="{{ $gId }}"
                                                                       name="{{ $inputName }}[{{ $gIdx }}]" 
                                                                       value="{{ $gVal }}" 
                                                                       data-sync-key="{{ $fieldKey }}.{{ $gIdx }}">
                                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                                    <label for="upload_{{ $gId }}" class="mayhen_editor_upload_btn mayhen_editor_upload_btn_sm" title="Tải ảnh từ máy tính hoặc điện thoại">
                                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                                                        </svg>
                                                                        <span>Tải ảnh {{ $gIdx + 1 }}</span>
                                                                    </label>
                                                                    <input type="file" 
                                                                           id="upload_{{ $gId }}" 
                                                                           class="mayhen_editor_file_uploader" 
                                                                           data-target-input="{{ $gId }}"
                                                                           data-target-thumb="preview_thumb_{{ $gId }}"
                                                                           data-template-slug="{{ $selectedTemplate->slug }}"
                                                                           accept="image/*" 
                                                                           style="display: none;">
                                                                    <span class="mayhen_editor_upload_status" id="status_{{ $gId }}" style="font-size: 0.7rem; color: var(--mayhen-color-text-muted);"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                            <div class="mayhen_editor_input_help">Tải ảnh kỷ niệm cưới từ máy tính hoặc điện thoại của bạn</div>
                                        </div>

                                    @elseif ($fieldKey === 'wedding.music_url')
                                        {{-- Trường Nhạc nền: Chọn bài hát thông minh từ thư viện --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="text" 
                                                       id="{{ $fieldId }}" 
                                                       name="{{ $inputName }}" 
                                                       class="mayhen_client_form_input" 
                                                       data-sync-key="{{ $fieldKey }}" 
                                                       value="{{ is_string($val) ? $val : '' }}" 
                                                       placeholder="Chọn bài hát từ danh sách..."
                                                       readonly
                                                       style="background: var(--mayhen-color-bg-soft); cursor: pointer;"
                                                       data-mayhen-action="open-music-modal">
                                                <button type="button" 
                                                        class="mayhen_editor_upload_btn" 
                                                        data-mayhen-action="open-music-modal"
                                                        title="Chọn bài hát từ kho nhạc cưới">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                                                    <span>Chọn bài hát</span>
                                                </button>
                                            </div>
                                            <div class="mayhen_editor_input_help">Kho nhạc lãng mạn bản quyền chất lượng cao, tìm kiếm theo tên bài hát hoặc ca sĩ</div>
                                        </div>

                                    @elseif ($fieldKey === 'wedding.extra_data.qr_groom_image' || $fieldKey === 'wedding.extra_data.qr_bride_image')
                                        {{-- Trường QR Mừng Cưới: Tạo VietQR ngân hàng an toàn tuyệt đối --}}
                                        @php
                                            $isGroomQr = str_contains($fieldKey, 'groom');
                                            $prefixQr = $isGroomQr ? 'groom_qr' : 'bride_qr';
                                            $sideLabel = $isGroomQr ? 'Nhà Trai' : 'Nhà Gái';
                                        @endphp
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_vietqr_box">
                                                <div class="mayhen_vietqr_preview_wrap">
                                                    <img src="{{ is_string($val) && (str_starts_with($val, 'http') || str_starts_with($val, '/')) ? $val : url('/mau-thiep/' . $selectedTemplate->slug . '/assets/' . $val) }}" 
                                                         alt="Mã QR {{ $sideLabel }}" 
                                                         class="mayhen_vietqr_img" 
                                                         id="preview_thumb_{{ $fieldId }}"
                                                         onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'">
                                                    <div class="mayhen_vietqr_fields">
                                                        <select class="mayhen_client_form_input mayhen_vietqr_bank_select" data-qr-side="{{ $prefixQr }}" data-target-input="{{ $fieldId }}">
                                                            <option value="">-- Chọn ngân hàng thụ hưởng --</option>
                                                            @foreach ($banks as $b)
                                                                <option value="{{ $b['code'] }}">{{ $b['short_name'] }} - {{ $b['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="text" 
                                                               class="mayhen_client_form_input mayhen_vietqr_acc_input" 
                                                               data-qr-side="{{ $prefixQr }}" 
                                                               data-target-input="{{ $fieldId }}"
                                                               placeholder="Số tài khoản ngân hàng...">
                                                        <input type="text" 
                                                               class="mayhen_client_form_input mayhen_vietqr_name_input" 
                                                               data-qr-side="{{ $prefixQr }}" 
                                                               data-target-input="{{ $fieldId }}"
                                                               placeholder="Tên chủ tài khoản (không dấu)...">
                                                    </div>
                                                </div>
                                                <input type="hidden" 
                                                       id="{{ $fieldId }}" 
                                                       name="{{ $inputName }}" 
                                                       data-sync-key="{{ $fieldKey }}" 
                                                       value="{{ is_string($val) ? $val : '' }}">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="font-size: 0.75rem; color: #10B981; font-weight: 500;">✓ Tự động tạo mã VietQR</span>
                                                    <button type="button" class="mayhen_cropper_btn_tool mayhen_btn_gen_vietqr" data-qr-side="{{ $prefixQr }}" data-target-input="{{ $fieldId }}" style="padding: 4px 12px; font-size: 11px;">
                                                        ⚡ Cập nhật mã VietQR
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif ($type === 'image')
                                        {{-- Trường Ảnh đơn kèm kiểm tra tỷ lệ 3/4 và popup cắt ảnh --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <div class="mayhen_editor_image_field_row">
                                                @php
                                                    $imagePreviewSrc = $selectedTemplate->thumbnail_url;
                                                    if (!empty($val) && is_string($val)) {
                                                        if (str_starts_with($val, 'http') || str_starts_with($val, '/') || str_starts_with($val, 'data:')) {
                                                            $imagePreviewSrc = $val;
                                                        } else {
                                                            $imagePreviewSrc = url('/mau-thiep/' . $selectedTemplate->slug . '/assets/' . ltrim($val, '/'));
                                                        }
                                                    }
                                                @endphp
                                                <img src="{{ $imagePreviewSrc }}" 
                                                     alt="Xem trước ảnh" 
                                                     class="mayhen_editor_image_field_preview"
                                                     id="preview_thumb_{{ $fieldId }}"
                                                     onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'">
                                                <div style="flex: 1; display: flex; flex-direction: column; gap: 8px;">
                                                    <input type="hidden" 
                                                           id="{{ $fieldId }}" 
                                                           name="{{ $inputName }}" 
                                                           data-sync-key="{{ $fieldKey }}" 
                                                           value="{{ is_string($val) ? $val : '' }}">
                                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                        <label for="upload_{{ $fieldId }}" class="mayhen_editor_upload_btn" title="Chọn ảnh từ máy tính hoặc điện thoại">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                                            </svg>
                                                            <span>Chọn ảnh</span>
                                                        </label>
                                                        <input type="file" 
                                                               id="upload_{{ $fieldId }}" 
                                                               class="mayhen_editor_file_uploader" 
                                                               data-target-input="{{ $fieldId }}"
                                                               data-target-thumb="preview_thumb_{{ $fieldId }}"
                                                               data-template-slug="{{ $selectedTemplate->slug }}"
                                                               data-is-main-photo="{{ str_contains($fieldKey, 'cover_photo') || str_contains($fieldKey, 'hero_photo') || str_contains($fieldKey, 'groom_photo') || str_contains($fieldKey, 'bride_photo') ? '1' : '0' }}"
                                                               accept="image/*" 
                                                               style="display: none;">
                                                        <span class="mayhen_editor_upload_status" id="status_{{ $fieldId }}" style="font-size: 0.75rem; color: var(--mayhen-color-text-muted);"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (str_contains($fieldKey, 'cover_photo') || str_contains($fieldKey, 'hero_photo') || str_contains($fieldKey, 'groom_photo') || str_contains($fieldKey, 'bride_photo'))
                                                <div class="mayhen_editor_input_help">Ảnh chân dung / đại diện sẽ được tự động căn chỉnh khung hình dọc tỉ lệ chuẩn 3:4 đẹp nhất</div>
                                            @endif
                                        </div>

                                    @elseif ($type === 'textarea')
                                        {{-- Trường Textarea --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <textarea id="{{ $fieldId }}" 
                                                      name="{{ $inputName }}" 
                                                      class="mayhen_client_form_textarea" 
                                                      rows="2" 
                                                      data-sync-key="{{ $fieldKey }}" 
                                                      placeholder="{{ $field['placeholder'] ?? '' }}"
                                                      {{ $isRequired ? 'required' : '' }}>{{ is_string($val) ? $val : '' }}</textarea>
                                        </div>

                                    @elseif ($type === 'color')
                                        {{-- Trường Color Accent --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_editor_color_picker_wrap">
                                                <input type="color" 
                                                       id="{{ $fieldId }}" 
                                                       name="{{ $inputName }}" 
                                                       class="mayhen_editor_color_input" 
                                                       data-sync-key="{{ $fieldKey }}" 
                                                       value="{{ $val ?: '#151515' }}">
                                                <span style="font-size: 0.8125rem; color: var(--mayhen-color-text-main); font-weight: 500;">Bấm để đổi tông màu thiệp</span>
                                            </div>
                                        </div>

                                    @elseif ($type === 'date')
                                        {{-- Trường Date: Tự động tính toán Âm lịch và gắn thẻ thông tin tự động dưới ô chọn ngày --}}
                                        @php
                                            $lunarFieldId = match($fieldKey) {
                                                'wedding.wedding_date', 'wedding_date' => 'wedding_extra_data_wedding_lunar_date',
                                                'wedding.extra_data.groom_party_date', 'groom_party_date' => 'wedding_extra_data_groom_party_lunar',
                                                'wedding.extra_data.bride_party_date', 'bride_party_date' => 'wedding_extra_data_bride_party_lunar',
                                                default => 'wedding_extra_data_wedding_lunar_date',
                                            };
                                            $lunarInitVal = '';
                                            if (isset($editingInvitation) && $editingInvitation->wedding) {
                                                $lunarSub = str_replace(['wedding_extra_data_', 'wedding_'], '', $lunarFieldId);
                                                $lunarInitVal = $editingInvitation->wedding->extra_data[$lunarSub] ?? '';
                                            }
                                            if (empty($lunarInitVal)) {
                                                $lunarInitVal = 'Tự động tính theo ngày cử hành';
                                            }
                                        @endphp
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <input type="date" 
                                                   id="{{ $fieldId }}" 
                                                   name="{{ $inputName }}" 
                                                   class="mayhen_client_form_input mayhen_date_with_auto_lunar mayhen_picker_must_select" 
                                                   data-sync-key="{{ $fieldKey }}" 
                                                   data-lunar-target="{{ $lunarFieldId }}"
                                                   value="{{ is_string($val) ? $val : '' }}" 
                                                   onkeydown="if(event.key!=='Tab'&&event.key!=='Enter'&&event.key!=='Escape')return false;"
                                                   onpaste="return false;"
                                                   {{ $isRequired ? 'required' : '' }}>
                                            <div class="mayhen_editor_lunar_badge" id="badge_{{ $lunarFieldId }}">
                                                <span class="mayhen_editor_lunar_icon">🌙</span>
                                                <span>Âm lịch:</span>
                                                <span class="mayhen_editor_lunar_val" id="text_{{ $lunarFieldId }}">{{ $lunarInitVal }}</span>
                                                <span class="mayhen_editor_lunar_tag">Tự động</span>
                                            </div>
                                            @if ($fieldKey === 'wedding.wedding_date')
                                                <div class="mayhen_editor_input_help">Ngày cử hành hôn lễ chính thức (Dương lịch & tự động đối soát Âm lịch)</div>
                                            @endif
                                        </div>

                                    @elseif ($type === 'time' || str_ends_with($fieldKey, '_time') || str_contains($fieldKey, 'party_time'))
                                        {{-- Trường Time 24h: Bắt buộc chọn từ bộ chọn thời gian 24h, cấm nhập tay --}}
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            @php
                                                $timeVal = '18:00';
                                                if (!empty($val) && is_string($val)) {
                                                    $ts = strtotime(trim($val));
                                                    $timeVal = ($ts !== false) ? date('H:i', $ts) : trim($val);
                                                } elseif (!empty($field['default']) && is_string($field['default'])) {
                                                    $ts = strtotime(trim($field['default']));
                                                    $timeVal = ($ts !== false) ? date('H:i', $ts) : trim($field['default']);
                                                }
                                            @endphp
                                            <input type="time" 
                                                   id="{{ $fieldId }}" 
                                                   name="{{ $inputName }}" 
                                                   class="mayhen_client_form_input mayhen_time_picker_input mayhen_picker_must_select" 
                                                   data-sync-key="{{ $fieldKey }}" 
                                                   value="{{ $timeVal }}" 
                                                   onkeydown="if(event.key!=='Tab'&&event.key!=='Enter'&&event.key!=='Escape')return false;"
                                                   onpaste="return false;"
                                                   {{ $isRequired ? 'required' : '' }}>
                                            <div class="mayhen_editor_input_help">Định dạng 24h, bấm để chọn giờ và phút (không cho phép nhập tay)</div>
                                        </div>

                                    @else
                                        {{-- Trường Text thông thường --}}
                                        @php
                                            $isNameField = in_array($fieldKey, [
                                                'wedding.groom_name',
                                                'wedding.bride_name',
                                                'wedding.extra_data.groom_father',
                                                'wedding.extra_data.groom_mother',
                                                'wedding.extra_data.bride_father',
                                                'wedding.extra_data.bride_mother',
                                            ]);
                                        @endphp
                                        <div class="{{ $groupClass }}">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <input type="text" 
                                                   id="{{ $fieldId }}" 
                                                   name="{{ $inputName }}" 
                                                   class="mayhen_client_form_input {{ $isNameField ? 'mayhen_validate_human_name' : '' }}" 
                                                   data-sync-key="{{ $fieldKey }}" 
                                                   value="{{ is_string($val) ? $val : '' }}" 
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                   @if ($isNameField)
                                                       maxlength="30"
                                                       data-max-words="4"
                                                       autocomplete="off"
                                                   @endif
                                                   {{ $isRequired ? 'required' : '' }}>
                                            @if ($isNameField)
                                                <div class="mayhen_editor_input_help" style="font-size: 0.72rem; color: #a8a29e;">
                                                    Tối đa 4 chữ, không quá 30 ký tự, không chứa số hoặc ký tự đặc biệt.
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </form>

                <!-- Sticky Stepper Footer Action Buttons (Ghim cố định ở chân Drawer) -->
                <div class="mayhen_editor_drawer_footer">
                    <button type="button" class="mayhen_editor_btn_nav mayhen_editor_btn_nav_prev" data-mayhen-action="prev-step" id="mayhen_btn_prev" disabled>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                        <span>Quay Lại</span>
                    </button>
                    <div class="mayhen_editor_drawer_step_indicator" id="mayhen_step_indicator">
                        <span class="mayhen_step_current">Bước 1</span> / <span class="mayhen_step_total">{{ count($sections) }}</span>
                    </div>
                    <button type="button" class="mayhen_editor_btn_nav mayhen_editor_btn_nav_next" data-mayhen-action="next-step" id="mayhen_btn_next">
                        <span>Tiếp Tục</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
                </div>

                <!-- Subpanel 2: Bố Cục Section Kéo Thả (Direct Drag-and-Drop) -->
                <div class="mayhen_editor_subpanel" data-panel-name="sections" style="display: none; padding: 20px;">
                    <div style="margin-bottom: 16px;">
                        <h3 style="font-size: 1.05rem; font-weight: 600; color: var(--mayhen-color-text-main); margin-bottom: 4px;">Sắp Xếp Bố Cục Thiệp</h3>
                        <p style="font-size: 0.75rem; color: var(--mayhen-color-text-muted);">Nhấn giữ biểu tượng ⠿ để kéo thả đổi vị trí các phần thiệp, hoặc bấm mắt để ẩn bớt phần không cần thiết.</p>
                    </div>

                    <ul class="mayhen_editor_sections_list">
                        @foreach ($sections as $sec)
                            <li class="mayhen_editor_section_card" data-section-id="{{ $sec['id'] }}">
                                <span class="mayhen_editor_drag_handle" title="Nhấn giữ và kéo để đổi thứ tự">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                </span>
                                <div class="mayhen_editor_section_info">
                                    <div class="mayhen_editor_section_name">{{ $sec['title'] }}</div>
                                    <div class="mayhen_editor_section_sub">{{ $sec['description'] ?? 'Khối nội dung thiệp cưới' }}</div>
                                </div>
                                <div class="mayhen_editor_section_actions">
                                    <button type="button" class="mayhen_editor_btn_icon" data-section-action="move-up" title="Di chuyển lên">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                    </button>
                                    <button type="button" class="mayhen_editor_btn_icon" data-section-action="move-down" title="Di chuyển xuống">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                    <button type="button" class="mayhen_editor_btn_icon" data-section-action="toggle-visibility" title="Ẩn/Hiện khối này">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Subpanel 3: 6 Bảng Màu Thượng Lưu & Tem Sáp -->
                <div class="mayhen_editor_subpanel" data-panel-name="theme" style="display: none; padding: 20px;">
                    <div style="margin-bottom: 16px;">
                        <h3 style="font-size: 1.05rem; font-weight: 600; color: var(--mayhen-color-text-main); margin-bottom: 4px;">6 Bảng Màu Quiet Luxury</h3>
                        <p style="font-size: 0.75rem; color: var(--mayhen-color-text-muted);">Đổi tông màu toàn bộ thiệp cưới chỉ với 1 chạm tinh tế.</p>
                    </div>

                    <div class="mayhen_editor_palette_grid">
                        <div class="mayhen_editor_palette_card active" data-palette-id="quiet-luxury">
                            <div class="mayhen_editor_swatch_row">
                                <span class="mayhen_editor_swatch_circle" style="background: #FAF8F5;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #B45309;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #1C1917;"></span>
                            </div>
                            <div class="mayhen_editor_palette_name">Quiet Luxury</div>
                            <div class="mayhen_editor_palette_desc">Vàng cát ấm áp trên nền giấy ngà</div>
                        </div>
                        <div class="mayhen_editor_palette_card" data-palette-id="sage-botanic">
                            <div class="mayhen_editor_swatch_row">
                                <span class="mayhen_editor_swatch_circle" style="background: #F5F7F5;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #3A4D39;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #739072;"></span>
                            </div>
                            <div class="mayhen_editor_palette_name">Sage Botanic</div>
                            <div class="mayhen_editor_palette_desc">Thảo mộc thanh tao & xanh rêu ô-liu</div>
                        </div>
                        <div class="mayhen_editor_palette_card" data-palette-id="warm-terracotta">
                            <div class="mayhen_editor_swatch_row">
                                <span class="mayhen_editor_swatch_circle" style="background: #FAF4EF;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #C85A32;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #E07A5F;"></span>
                            </div>
                            <div class="mayhen_editor_palette_name">Warm Terracotta</div>
                            <div class="mayhen_editor_palette_desc">Đất nung Địa Trung Hải nồng nàn</div>
                        </div>
                        <div class="mayhen_editor_palette_card" data-palette-id="rose-vintage">
                            <div class="mayhen_editor_swatch_row">
                                <span class="mayhen_editor_swatch_circle" style="background: #FCF7F7;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #9D5C63;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #D8A47F;"></span>
                            </div>
                            <div class="mayhen_editor_palette_name">Rose Vintage</div>
                            <div class="mayhen_editor_palette_desc">Hồng khói Pháp dịu dàng lãng mạn</div>
                        </div>
                        <div class="mayhen_editor_palette_card" data-palette-id="champagne-glam">
                            <div class="mayhen_editor_swatch_row">
                                <span class="mayhen_editor_swatch_circle" style="background: #FAF6F0;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #A67C37;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #C5A059;"></span>
                            </div>
                            <div class="mayhen_editor_palette_name">Champagne Glam</div>
                            <div class="mayhen_editor_palette_desc">Vàng rượu sâm panh hoàng gia</div>
                        </div>
                        <div class="mayhen_editor_palette_card" data-palette-id="noir-chic">
                            <div class="mayhen_editor_swatch_row">
                                <span class="mayhen_editor_swatch_circle" style="background: #F5F5F5;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #1F1F1F;"></span>
                                <span class="mayhen_editor_swatch_circle" style="background: #BFA46F;"></span>
                            </div>
                            <div class="mayhen_editor_palette_name">Noir Chic</div>
                            <div class="mayhen_editor_palette_desc">Tương phản đen tuyền & vàng ánh kim</div>
                        </div>
                    </div>

                    <!-- Tùy Biến Con Dấu Sáp Mở Thiệp -->
                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--mayhen-color-border, #e7e5e4);">
                        <label class="mayhen_client_form_label" style="font-weight: 600;">Màu Con Dấu Sáp Hoàng Gia</label>
                        <div class="mayhen_editor_seal_swatches">
                            <button type="button" class="mayhen_editor_seal_btn active" data-seal-color="#8A151B" style="background: #8A151B;" title="Đỏ Bordeaux"></button>
                            <button type="button" class="mayhen_editor_seal_btn" data-seal-color="#C5A059" style="background: #C5A059;" title="Vàng Hoàng Kim"></button>
                            <button type="button" class="mayhen_editor_seal_btn" data-seal-color="#9D5C63" style="background: #9D5C63;" title="Hồng Khói"></button>
                            <button type="button" class="mayhen_editor_seal_btn" data-seal-color="#3A4D39" style="background: #3A4D39;" title="Xanh Rêu"></button>
                            <button type="button" class="mayhen_editor_seal_btn" data-seal-color="#1C1917" style="background: #1C1917;" title="Đen Huyền Bí"></button>
                        </div>
                        <div class="mayhen_client_form_group" style="margin-top: 14px;">
                            <label class="mayhen_client_form_label" for="mayhen_seal_monogram_input">Ký Tự Dập Nổi Monogram (Tối đa 4 chữ cái)</label>
                            <input type="text" id="mayhen_seal_monogram_input" class="mayhen_client_form_input" maxlength="4" placeholder="Ví dụ: MH, ĐP, 2027" value="MH" style="text-transform: uppercase; font-family: var(--mayhen-font-serif); letter-spacing: 2px;">
                        </div>
                    </div>
                </div>

                <!-- Subpanel 4: Nhạc Nền Đám Cưới -->
                <div class="mayhen_editor_subpanel" data-panel-name="music" style="display: none; padding: 20px;">
                    <div style="margin-bottom: 16px;">
                        <h3 style="font-size: 1.05rem; font-weight: 600; color: var(--mayhen-color-text-main); margin-bottom: 4px;">Nhạc Nền Hôn Lễ</h3>
                        <p style="font-size: 0.75rem; color: var(--mayhen-color-text-muted);">Giai điệu tình yêu sẽ tự động ngân vang khi khách chạm mở phong thư.</p>
                    </div>

                    <div style="background-color: var(--mayhen-color-bg-soft, #f7f5f2); border: 1px solid var(--mayhen-color-border, #e7e5e4); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--mayhen-color-primary-soft, #fef3c7); color: var(--mayhen-color-primary, #b45309); display: flex; align-items: center; justify-content: center;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div id="mayhen_active_music_title" style="font-size: 0.875rem; font-weight: 600; color: var(--mayhen-color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Ánh Nắng Của Anh (Đức Phúc)</div>
                                <div style="font-size: 0.6875rem; color: var(--mayhen-color-text-muted);">Nhạc cưới bản quyền chất lượng cao</div>
                            </div>
                        </div>

                        <button type="button" class="mayhen_editor_btn_nav" id="mayhen_open_music_modal_btn" style="width: 100%; justify-content: center; padding: 10px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Duyệt Kho 20+ Bài Hát Đám Cưới</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Zone 3: Center Stage Live Mockup Canvas -->
            <main class="mayhen_editor_center_stage" id="mayhen_editor_center_stage">
                <div class="mayhen_editor_mockup_phone">
                    <div class="mayhen_editor_phone_island"></div>
                    <iframe 
                        id="mayhen_editor_preview_iframe"
                        src="{{ route('templates.preview', $selectedTemplate->slug) }}?embed=1&mode=editor{{ isset($editingInvitation) && $editingInvitation ? '&edit=' . $editingInvitation->slug : '' }}"
                        class="mayhen_editor_iframe_viewport"
                        title="Bản xem trước trực tiếp mẫu thiệp cưới {{ $selectedTemplate->name }}"
                        loading="eager"
                    ></iframe>
                </div>

                <div class="mayhen_editor_preview_hint" style="margin-top: 14px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Mọi thao tác chỉnh sửa nội dung, thứ tự khối, bảng màu đều phản chiếu thời gian thực trên màn hình</span>
                </div>
            </main>
        </div>

        <!-- 5. Modal Fullscreen Preview trên Mobile -->
        <div class="mayhen_editor_modal_preview" id="mayhen_mobile_preview_modal" role="dialog" aria-modal="true" aria-label="Xem trước thiệp cưới toàn màn hình">
            <div class="mayhen_editor_modal_sheet">
                <div class="mayhen_editor_modal_header">
                    <div style="font-weight: 600; font-size: 0.9375rem; color: var(--mayhen-color-text-main);">
                        Bản Xem Thử Trực Tiếp ({{ $selectedTemplate->name }})
                    </div>
                    <button type="button" class="mayhen_editor_modal_close_btn" data-mayhen-action="close-preview-modal" aria-label="Đóng bản xem trước">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="mayhen_editor_modal_body" id="mayhen_mobile_modal_body" style="padding: 0; height: calc(100vh - 120px); overflow: hidden;">
                    <iframe 
                        id="mayhen_editor_mobile_preview_iframe"
                        src="{{ route('templates.preview', $selectedTemplate->slug) }}?embed=1&mode=editor{{ isset($editingInvitation) && $editingInvitation ? '&edit=' . $editingInvitation->slug : '' }}"
                        style="width: 100%; height: 100%; border: none; display: block;"
                        title="Bản xem trước di động"
                    ></iframe>
                </div>
            </div>
        </div>

        <!-- 6. Modal Đổi Mẫu Thiệp -->
        <div class="mayhen_editor_template_modal" id="mayhen_template_switcher_modal" role="dialog" aria-modal="true" aria-label="Chọn mẫu thiệp khác">
            <div class="mayhen_editor_template_modal_box">
                <div class="mayhen_editor_modal_header">
                    <div style="font-weight: 600; font-size: 1.05rem; color: var(--mayhen-color-text-main);">
                        Chọn Phong Cách Thiệp Cưới Khác
                    </div>
                    <button type="button" class="mayhen_editor_modal_close_btn" data-mayhen-action="close-template-modal" aria-label="Đóng cửa sổ">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="mayhen_editor_template_grid">
                    @foreach ($allTemplates as $tpl)
                        @php
                            $isLocked = $tpl->tier !== 'free' && !auth()->check();
                            $targetUrl = $isLocked
                                ? route('login', ['redirect' => route('invitation.create', ['template' => $tpl->slug])])
                                : route('invitation.create', ['template' => $tpl->slug]);
                        @endphp
                        <a href="{{ $targetUrl }}" 
                           class="mayhen_editor_template_radio_card {{ $tpl->id === $selectedTemplate->id ? 'mayhen_editor_template_radio_selected' : '' }}"
                           data-template-switch="true"
                           data-template-id="{{ $tpl->id }}"
                           data-template-slug="{{ $tpl->slug }}"
                           data-template-name="{{ $tpl->name }}"
                           data-template-thumb="{{ $tpl->thumbnail_url }}"
                           data-template-tier="{{ $tpl->tier }}"
                           data-template-locked="{{ $isLocked ? 'true' : 'false' }}"
                           style="text-decoration: none; color: inherit; position: relative;">
                            <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" class="mayhen_editor_template_radio_thumb">
                            <div class="mayhen_editor_template_radio_name">{{ $tpl->name }}</div>
                            @if ($isLocked)
                                <div style="font-size: 0.6875rem; color: #D97706; margin-top: 2px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <span>Đăng nhập</span>
                                </div>
                            @else
                                <div style="font-size: 0.6875rem; color: var(--mayhen-color-primary); margin-top: 2px;">
                                    {{ strtoupper($tpl->tier ?? 'FREE') }}
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 7. Modal Cắt Ảnh 3/4 Thông Minh (Pure Canvas Cropper) -->
        <div class="mayhen_cropper_modal" id="mayhen_cropper_modal" role="dialog" aria-modal="true" aria-label="Cắt ảnh theo tỷ lệ 3/4">
            <div class="mayhen_cropper_box">
                <div class="mayhen_cropper_header">
                    <div class="mayhen_cropper_header_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2v14a2 2 0 0 0 2 2h14"/><path d="M18 22V8a2 2 0 0 0-2-2H2"/></svg>
                        <span>Căn chỉnh ảnh bìa thiệp cưới</span>
                    </div>
                    <button type="button" class="mayhen_editor_modal_close_btn" id="mayhen_cropper_close_btn" aria-label="Hủy cắt ảnh">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="mayhen_cropper_body">
                    <div class="mayhen_cropper_stage" id="mayhen_cropper_stage">
                        <canvas id="mayhen_cropper_canvas" class="mayhen_cropper_canvas"></canvas>
                        <div class="mayhen_cropper_overlay" id="mayhen_cropper_overlay"></div>
                    </div>
                    <div class="mayhen_cropper_controls">
                        <button type="button" class="mayhen_cropper_btn_tool" id="mayhen_cropper_zoom_in">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                            <span>Phóng to</span>
                        </button>
                        <button type="button" class="mayhen_cropper_btn_tool" id="mayhen_cropper_zoom_out">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                            <span>Thu nhỏ</span>
                        </button>
                        <button type="button" class="mayhen_cropper_btn_tool" id="mayhen_cropper_rotate_left">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            <span>Xoay trái</span>
                        </button>
                        <button type="button" class="mayhen_cropper_btn_tool" id="mayhen_cropper_rotate_right">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                            <span>Xoay phải</span>
                        </button>
                        <button type="button" class="mayhen_cropper_btn_tool" id="mayhen_cropper_reset">
                            <span>Đặt lại</span>
                        </button>
                    </div>
                </div>
                <div class="mayhen_cropper_footer">
                    <button type="button" class="mayhen_cropper_btn_cancel" id="mayhen_cropper_btn_cancel">Hủy bỏ</button>
                    <button type="button" class="mayhen_cropper_btn_confirm" id="mayhen_cropper_btn_confirm">
                        <span>✓ Cắt & Sử Dụng Ảnh Này</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 8. Modal Chọn Nhạc Cưới Thông Minh -->
        <div class="mayhen_music_picker_modal" id="mayhen_music_picker_modal" role="dialog" aria-modal="true" aria-label="Kho nhạc đám cưới">
            <div class="mayhen_music_picker_box">
                <div class="mayhen_cropper_header">
                    <div class="mayhen_cropper_header_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                        <span>Kho Nhạc Cưới Lãng Mạn Bản Quyền</span>
                    </div>
                    <button type="button" class="mayhen_editor_modal_close_btn" id="mayhen_music_close_btn" aria-label="Đóng kho nhạc">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="mayhen_music_search_bar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mayhen-color-text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="mayhen_music_search_input" id="mayhen_music_search_input" placeholder="Tìm theo tên bài hát hoặc ca sĩ (ví dụ: Kai Đinh, Váy Cưới...)...">
                </div>
                <div class="mayhen_music_list" id="mayhen_music_list">
                    <p style="text-align: center; color: var(--mayhen-color-text-muted); padding: 24px; font-style: italic;">Đang tải danh sách bài hát...</p>
                </div>
            </div>
        </div>
        <audio id="mayhen_music_preview_audio" preload="none"></audio>



        <!-- Mobile Floating Action Bar (Nổi khi xem trước trên mobile) -->
        <div class="mayhen_editor_mobile_floating_bar" role="toolbar" aria-label="Thao tác nhanh trên điện thoại">
            <button type="button" class="mayhen_editor_mobile_floating_btn" data-editor-mode="form">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Sửa Thiệp</span>
            </button>
            <button type="button" class="mayhen_editor_mobile_floating_btn" data-mayhen-action="open-template-modal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                <span>Đổi Mẫu</span>
            </button>
            <button type="button" class="mayhen_editor_mobile_floating_btn btn-primary" data-mayhen-action="trigger-submit">
                <span>Xuất Bản</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__MAYHEN_TEMPLATE_SLUG = "{{ $selectedTemplate->slug }}";
        window.__MAYHEN_TEMPLATE_ID = {{ $selectedTemplate->id }};
        window.__MAYHEN_INVITATION_ID = {{ isset($editingInvitation) && $editingInvitation ? $editingInvitation->id : 'null' }};
        window.__MAYHEN_REVISION_ID = {{ isset($editingInvitation) && $editingInvitation ? ($editingInvitation->revision_id ?? 0) : 0 }};
        window.__MAYHEN_CSRF_TOKEN = "{{ csrf_token() }}";
        window.__MAYHEN_UPLOAD_URL = "{{ route('invitation.uploadAsset') }}";
        window.__MAYHEN_AUTOSAVE_URL = "{{ route('invitation.autosave') }}";
    </script>
    <script type="module" src="{{ asset('assets/client/js/pages/editor.js') }}?v={{ file_exists(public_path('assets/client/js/pages/editor.js')) ? filemtime(public_path('assets/client/js/pages/editor.js')) : time() }}"></script>
@endpush
