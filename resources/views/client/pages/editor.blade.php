@extends('client.layouts.master')

@section('title', 'Tạo Thiệp Cưới Online — ' . ($selectedTemplate ? $selectedTemplate->name : 'Thiệp Mời') . ' | ' . config('app.name'))
@section('meta_description', 'Tự tạo thiệp cưới online phong cách Quiet Luxury dễ dàng với chế độ xem thử realtime chính xác theo từng mẫu thiệp.')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/preview.css') }}">
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
    <div class="mayhen_editor_wrapper">
        <!-- 1. Studio Topbar -->
        <header class="mayhen_editor_topbar">
            <div class="mayhen_editor_topbar_left">
                <div class="mayhen_editor_topbar_title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mayhen-color-primary);">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    <span>Studio Tạo Thiệp Cưới</span>
                </div>

                <!-- Template Active Chip & Quick Switcher -->
                <button type="button" class="mayhen_editor_template_chip" data-mayhen-action="open-template-modal" title="Bấm để đổi mẫu thiệp khác">
                    <img src="{{ $selectedTemplate->thumbnail_url }}" alt="{{ $selectedTemplate->name }}" class="mayhen_editor_template_chip_thumb">
                    <span class="mayhen_editor_template_chip_name">{{ $selectedTemplate->name }}</span>
                    <span style="font-size: 0.6875rem; color: var(--mayhen-color-primary); font-weight: 700;">Đổi mẫu</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>

            <div>
                <button type="button" class="mayhen_editor_btn_submit" data-mayhen-action="trigger-submit">
                    <span>Tạo Link Thiệp</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </div>
        </header>

        <!-- 2. Mobile Mini Live Preview Dock -->
        <div class="mayhen_editor_mobile_live_dock">
            <div class="mayhen_editor_dock_content">
                <img src="{{ $selectedTemplate->thumbnail_url }}" alt="Preview" class="mayhen_editor_dock_avatar" id="mayhen_dock_thumb">
                <div class="mayhen_editor_dock_info">
                    <div class="mayhen_editor_dock_live_badge">
                        <span class="mayhen_editor_dock_live_dot"></span>
                        <span>Đang sửa mẫu: {{ $selectedTemplate->name }}</span>
                    </div>
                    <div class="mayhen_editor_dock_names" data-mayhen-preview="names">
                        Tràng Đức &bull; Ngọc Phượng
                    </div>
                </div>
            </div>

            <button type="button" class="mayhen_editor_dock_action_btn" data-mayhen-action="open-preview-modal" title="Xem thiệp cưới to toàn màn hình">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Xem Toàn Thiệp</span>
            </button>
        </div>

        <!-- 3. Main Split Studio Grid -->
        <div class="mayhen_editor_studio_grid">
            <!-- Left: Dynamic Schema-Driven Form Panel -->
            <div class="mayhen_editor_form_panel">
                <!-- Stepper Navigation Pills -->
                <nav class="mayhen_editor_stepper_nav" aria-label="Các bước nhập liệu">
                    @foreach ($sections as $index => $section)
                        @php
                            $shortTitle = $section['short_title'] ?? match ($section['id'] ?? '') {
                                'couple' => 'Đôi Uyên Ương',
                                'bride_events' => 'Nhà Gái',
                                'groom_events' => 'Nhà Trai',
                                'gallery_and_photos' => 'Ảnh Cưới',
                                'story_and_timeline' => 'Lịch Trình',
                                'gift_and_music' => 'Mừng Cưới',
                                default => \Illuminate\Support\Str::limit($section['title'], 18)
                            };
                        @endphp
                        <button type="button" 
                                class="mayhen_editor_stepper_tab {{ $index === 0 ? 'mayhen_editor_stepper_tab_active' : '' }}" 
                                data-mayhen-step="{{ $index + 1 }}"
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

                    @foreach ($sections as $index => $section)
                        <div class="mayhen_editor_step_content {{ $index === 0 ? 'mayhen_editor_step_active' : '' }}" data-step-panel="{{ $index + 1 }}">
                            <div class="mayhen_editor_step_header">
                                <h2 class="mayhen_editor_step_heading">
                                    <span>{{ $index + 1 }}. {{ $section['title'] }}</span>
                                </h2>
                                @if (!empty($section['description']))
                                    <p class="mayhen_editor_step_subtext">{{ $section['description'] }}</p>
                                @endif
                            </div>

                            <div class="mayhen_editor_fields_container" style="display: flex; flex-direction: column; gap: 14px;">
                                @php
                                    $fields = $section['fields'] ?? [];
                                @endphp

                                @foreach ($fields as $field)
                                    @php
                                        $fieldKey = $field['key'];
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
                                            } else {
                                                $editingVal = $editingInvitation->wedding->{$cleanKey} ?? null;
                                            }
                                        }

                                        $defaultVal = $editingVal !== null ? $editingVal : ($field['default'] ?? '');
                                        $val = old($oldKey, $defaultVal);
                                        $type = $field['type'] ?? 'text';
                                        $label = $field['label'] ?? $fieldKey;
                                        $isRequired = !empty($field['required']);
                                    @endphp

                                    @if ($type === 'repeater')
                                        {{-- Trường dạng danh sách repeater (Ví dụ Timeline) --}}
                                        <div class="mayhen_client_form_group">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_editor_repeater_list">
                                                @php
                                                    $items = is_array($val) ? $val : ($field['default'] ?? []);
                                                @endphp
                                                @foreach ($items as $rIdx => $rItem)
                                                    <div class="mayhen_editor_repeater_item">
                                                        <input type="text" 
                                                               name="{{ $inputName }}[{{ $rIdx }}][time]" 
                                                               class="mayhen_client_form_input" 
                                                               value="{{ $rItem['time'] ?? '' }}" 
                                                               placeholder="07:00"
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
                                        <div class="mayhen_client_form_group">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_editor_gallery_grid">
                                                @for ($gIdx = 0; $gIdx < ($field['max_items'] ?? 10); $gIdx++)
                                                    @php
                                                        $gVal = is_array($val) && isset($val[$gIdx]) ? $val[$gIdx] : '';
                                                        $gId = "gallery_photo_{$gIdx}";
                                                    @endphp
                                                    <div class="mayhen_editor_gallery_item_box">
                                                        <div class="mayhen_editor_image_field_row">
                                                            <img src="{{ is_string($gVal) && (str_starts_with($gVal, 'http') || str_starts_with($gVal, '/')) ? $gVal : ($gVal ? url('/mau-thiep/' . $selectedTemplate->slug . '/assets/' . $gVal) : $selectedTemplate->thumbnail_url) }}" 
                                                                 alt="Ảnh {{ $gIdx + 1 }}" 
                                                                 class="mayhen_editor_image_field_preview"
                                                                 id="preview_thumb_{{ $gId }}"
                                                                 onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'">
                                                            <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                                                                <input type="text" 
                                                                       id="{{ $gId }}"
                                                                       name="{{ $inputName }}[{{ $gIdx }}]" 
                                                                       class="mayhen_client_form_input" 
                                                                       value="{{ $gVal }}" 
                                                                       placeholder="Ảnh {{ $gIdx + 1 }}..."
                                                                       data-sync-key="{{ $fieldKey }}.{{ $gIdx }}">
                                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                                    <label for="upload_{{ $gId }}" class="mayhen_editor_upload_btn mayhen_editor_upload_btn_sm" title="Tải ảnh lên">
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
                                            <div class="mayhen_editor_input_help">Tải trực tiếp ảnh từ thiết bị để lưu vào assets của mẫu</div>
                                        </div>

                                    @elseif ($fieldKey === 'wedding.music_url')
                                        {{-- Trường Nhạc nền: Chọn bài hát thông minh từ thư viện --}}
                                        <div class="mayhen_client_form_group">
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
                                        <div class="mayhen_client_form_group">
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
                                                    <span style="font-size: 0.75rem; color: #10B981; font-weight: 500;">✓ Tự động tạo mã VietQR NAPAS bảo mật chuẩn 100%</span>
                                                    <button type="button" class="mayhen_cropper_btn_tool mayhen_btn_gen_vietqr" data-qr-side="{{ $prefixQr }}" data-target-input="{{ $fieldId }}" style="padding: 4px 12px; font-size: 11px;">
                                                        ⚡ Cập nhật mã VietQR
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif ($type === 'image')
                                        {{-- Trường Ảnh đơn kèm kiểm tra tỷ lệ 3/4 và popup cắt ảnh --}}
                                        <div class="mayhen_client_form_group">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <div class="mayhen_editor_image_field_row">
                                                <img src="{{ is_string($val) && (str_starts_with($val, 'http') || str_starts_with($val, '/')) ? $val : url('/mau-thiep/' . $selectedTemplate->slug . '/assets/' . $val) }}" 
                                                     alt="Xem trước ảnh" 
                                                     class="mayhen_editor_image_field_preview"
                                                     id="preview_thumb_{{ $fieldId }}"
                                                     onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'">
                                                <div style="flex: 1; display: flex; flex-direction: column; gap: 6px;">
                                                    <input type="text" 
                                                           id="{{ $fieldId }}" 
                                                           name="{{ $inputName }}" 
                                                           class="mayhen_client_form_input" 
                                                           data-sync-key="{{ $fieldKey }}" 
                                                           value="{{ is_string($val) ? $val : '' }}" 
                                                           placeholder="{{ $field['placeholder'] ?? 'Dán link hoặc bấm nút tải ảnh...' }}"
                                                           {{ $isRequired ? 'required' : '' }}>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
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
                                                               data-is-main-photo="{{ str_contains($fieldKey, 'cover_photo') || str_contains($fieldKey, 'hero_photo') ? '1' : '0' }}"
                                                               accept="image/*" 
                                                               style="display: none;">
                                                        <span class="mayhen_editor_upload_status" id="status_{{ $fieldId }}" style="font-size: 0.75rem; color: var(--mayhen-color-text-muted);"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (str_contains($fieldKey, 'cover_photo') || str_contains($fieldKey, 'hero_photo'))
                                                <div class="mayhen_editor_input_help">Ảnh chính mở thiệp bắt buộc tỉ lệ đứng 3/4. Hệ thống sẽ tự động hỗ trợ căn chỉnh và cắt ảnh cho bạn.</div>
                                            @endif
                                        </div>

                                    @elseif ($type === 'textarea')
                                        {{-- Trường Textarea --}}
                                        <div class="mayhen_client_form_group">
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
                                        <div class="mayhen_client_form_group">
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

                                    @else
                                        {{-- Trường Text / Date / Time thông thường --}}
                                        <div class="mayhen_client_form_group">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <input type="{{ $type === 'date' ? 'date' : 'text' }}" 
                                                   id="{{ $fieldId }}" 
                                                   name="{{ $inputName }}" 
                                                   class="mayhen_client_form_input" 
                                                   data-sync-key="{{ $fieldKey }}" 
                                                   value="{{ is_string($val) ? $val : '' }}" 
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                   {{ $isRequired ? 'required' : '' }}>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>

            <!-- Right Column: Live Iframe Phone Preview (Hiển thị CHÍNH XÁC MẪU THIỆP ĐƯỢC CHỌN) -->
            <div class="mayhen_editor_preview_panel">
                <div class="mayhen_editor_phone_wrap">
                    <div class="mayhen_editor_phone_notch"></div>
                    <div class="mayhen_editor_phone_screen mayhen_editor_phone_screen_iframe">
                        <iframe 
                            id="mayhen_editor_preview_iframe"
                            src="{{ route('templates.preview', $selectedTemplate->slug) }}?embed=1&mode=editor"
                            class="mayhen_editor_preview_iframe"
                            title="Bản xem trước trực tiếp mẫu thiệp cưới {{ $selectedTemplate->name }}"
                            loading="eager"
                        ></iframe>
                    </div>
                </div>

                <div class="mayhen_editor_preview_hint">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Mọi thông tin bạn sửa bên trái đều phản chiếu thời gian thực trên mẫu thiệp</span>
                </div>
            </div>
        </div>

        <!-- 4. Sticky Bottom Action Bar -->
        <div class="mayhen_editor_bottom_bar">
            <div class="mayhen_editor_bottom_bar_inner">
                <div class="mayhen_editor_bottom_nav_btns">
                    <button type="button" class="mayhen_editor_btn_nav" data-mayhen-action="prev-step" id="mayhen_btn_prev" style="display: none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                        <span>Quay Lại</span>
                    </button>
                    <button type="button" class="mayhen_editor_btn_nav" data-mayhen-action="next-step" id="mayhen_btn_next">
                        <span>Tiếp Tục</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>

                <div>
                    <button type="button" class="mayhen_editor_btn_submit" data-mayhen-action="trigger-submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <span>Hoàn Tất & Tạo Thiệp Ngay</span>
                    </button>
                </div>
            </div>
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
                        src="{{ route('templates.preview', $selectedTemplate->slug) }}?embed=1&mode=editor"
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
                        <span>Cắt ảnh bìa chính (Tỉ lệ chuẩn 3:4)</span>
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
    </div>
@endsection

@push('scripts')
    <script>
        window.__MAYHEN_TEMPLATE_SLUG = "{{ $selectedTemplate->slug }}";
        window.__MAYHEN_CSRF_TOKEN = "{{ csrf_token() }}";
        window.__MAYHEN_UPLOAD_URL = "{{ route('invitation.uploadAsset') }}";
    </script>
    <script type="module" src="{{ asset('assets/client/js/pages/editor.js') }}"></script>
@endpush
