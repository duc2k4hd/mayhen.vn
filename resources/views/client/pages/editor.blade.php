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
                                        $defaultVal = $field['default'] ?? '';
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
                                        {{-- Trường Album ảnh --}}
                                        <div class="mayhen_client_form_group">
                                            <label class="mayhen_client_form_label">{{ $label }}</label>
                                            <div class="mayhen_editor_gallery_grid">
                                                @for ($gIdx = 0; $gIdx < ($field['max_items'] ?? 10); $gIdx++)
                                                    @php
                                                        $gVal = is_array($val) && isset($val[$gIdx]) ? $val[$gIdx] : '';
                                                    @endphp
                                                    <input type="text" 
                                                           name="{{ $inputName }}[{{ $gIdx }}]" 
                                                           class="mayhen_client_form_input" 
                                                           value="{{ $gVal }}" 
                                                           placeholder="Link ảnh {{ $gIdx + 1 }}..."
                                                           data-sync-key="{{ $fieldKey }}.{{ $gIdx }}">
                                                @endfor
                                            </div>
                                            <div class="mayhen_editor_input_help">Nhập URL ảnh cưới trong album (để trống sẽ dùng ảnh mẫu có sẵn)</div>
                                        </div>

                                    @elseif ($type === 'image')
                                        {{-- Trường Ảnh đơn kèm xem trước ảnh nhỏ --}}
                                        <div class="mayhen_client_form_group">
                                            <label class="mayhen_client_form_label" for="{{ $fieldId }}">
                                                {{ $label }}
                                                @if ($isRequired)
                                                    <span style="color: var(--mayhen-color-danger);">*</span>
                                                @endif
                                            </label>
                                            <div class="mayhen_editor_image_field_row">
                                                <img src="{{ is_string($val) && (str_starts_with($val, 'http') || str_starts_with($val, '/')) ? $val : asset('assets/client/invitations/net-muc-tinh-yeu/' . $val) }}" 
                                                     alt="Xem trước ảnh" 
                                                     class="mayhen_editor_image_field_preview"
                                                     id="preview_thumb_{{ $fieldId }}"
                                                     onerror="this.src='{{ $selectedTemplate->thumbnail_url }}'">
                                                <input type="text" 
                                                       id="{{ $fieldId }}" 
                                                       name="{{ $inputName }}" 
                                                       class="mayhen_client_form_input" 
                                                       data-sync-key="{{ $fieldKey }}" 
                                                       value="{{ is_string($val) ? $val : '' }}" 
                                                       placeholder="{{ $field['placeholder'] ?? 'Dán đường dẫn ảnh...' }}"
                                                       {{ $isRequired ? 'required' : '' }}>
                                            </div>
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
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/editor.js') }}"></script>
@endpush
