@extends('client.layouts.master')

@section('title', 'Trình Tạo Thiệp Cưới Trực Quan — Mây Hẹn')
@section('meta_description', 'Tự tạo thiệp cưới online phong cách Quiet Luxury dễ dàng trong 3 phút với chế độ xem thử realtime.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/preview.css') }}">
@endpush

@section('content')
    <div class="mayhen_editor_wrapper">
        <!-- Editor Topbar -->
        <div class="mayhen_editor_topbar">
            <div class="mayhen_editor_topbar_title">
                <span>Studio Tạo Thiệp Cưới</span>
            </div>

            <!-- Mobile View Switcher -->
            <div class="mayhen_editor_view_toggle">
                <button type="button" class="mayhen_editor_toggle_btn mayhen_editor_toggle_btn_active" data-mayhen-action="switch-editor-view" data-view-target="form">
                    Chỉnh Sửa
                </button>
                <button type="button" class="mayhen_editor_toggle_btn" data-mayhen-action="switch-editor-view" data-view-target="preview">
                    Xem Thử Live
                </button>
            </div>
        </div>

        <!-- Split Grid Studio -->
        <div class="mayhen_editor_studio_grid">
            <!-- Left: Inputs Form -->
            <div class="mayhen_editor_form_panel">
                <form action="{{ route('invitation.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template_id" id="template_id" value="{{ $selectedTemplate->id }}">

                    <!-- Step 1: Template Selection -->
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                        <h2 class="mayhen_editor_section_title" style="margin-bottom: 0;">1. Phong Cách Thiệp Đang Chọn</h2>
                        <span style="font-size: 0.8125rem; color: var(--mayhen-color-primary); font-weight: 600;">
                            {{ $selectedTemplate->name }} ({{ strtoupper($selectedTemplate->tier ?? 'FREE') }})
                        </span>
                    </div>
                    <p style="font-size: 0.8125rem; color: var(--mayhen-color-text-muted); margin-bottom: 1rem;">
                        Bấm vào mẫu bên dưới để đổi giao diện. Form nhập liệu sẽ tự động điều chỉnh theo đúng tính năng của mẫu được chọn.
                    </p>

                    <div class="mayhen_editor_template_picker">
                        @foreach ($allTemplates as $tpl)
                            <a href="{{ route('invitation.create', ['template' => $tpl->slug]) }}" 
                               class="mayhen_editor_template_radio_card {{ $tpl->id === $selectedTemplate->id ? 'mayhen_editor_template_radio_selected' : '' }}" 
                               data-mayhen-template-card 
                               data-mayhen-template-id="{{ $tpl->id }}"
                               style="text-decoration: none; color: inherit;">
                                <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" class="mayhen_editor_template_radio_thumb">
                                <div class="mayhen_editor_template_radio_name">{{ $tpl->name }}</div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Dynamic Schema Sections (Các mục chỉnh sửa sinh tự động từ Schema của mẫu) -->
                    @if (!empty($schema['sections']))
                        @foreach ($schema['sections'] as $sIndex => $sec)
                            <div class="mayhen_editor_schema_section" style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px dashed var(--mayhen-color-border);">
                                <div style="margin-bottom: 0.75rem;">
                                    <h3 class="mayhen_editor_section_title" style="font-size: 1.05rem; margin-bottom: 2px;">
                                        {{ $sIndex + 2 }}. {{ $sec['title'] }}
                                    </h3>
                                    @if (!empty($sec['description']))
                                        <p style="font-size: 0.78125rem; color: var(--mayhen-color-text-muted); margin: 0;">
                                            {{ $sec['description'] }}
                                        </p>
                                    @endif
                                </div>

                                @foreach ($sec['fields'] as $field)
                                    @php
                                        $fieldId = str_replace(['.', '[', ']', ' '], '_', $field['key']);
                                        $defaultVal = $field['default'] ?? '';
                                        
                                        // Demo mock values nếu chưa có dữ liệu
                                        if (empty($defaultVal)) {
                                            if (str_contains($field['key'], 'groom_name')) $defaultVal = 'Đặng Minh Quân';
                                            if (str_contains($field['key'], 'bride_name')) $defaultVal = 'Lê Ái Như';
                                            if (str_contains($field['key'], 'wedding_date')) $defaultVal = '2026-10-24';
                                            if (str_contains($field['key'], 'venue_name')) $defaultVal = 'The White Palace';
                                            if (str_contains($field['key'], 'venue_address')) $defaultVal = 'Sảnh Grand Ballroom • 194 Hoàng Văn Thụ, TP. HCM';
                                            if (str_contains($field['key'], 'story')) $defaultVal = 'Yêu là khi nhìn thấy cả thế giới trong mắt một người.';
                                            if (str_contains($field['key'], 'seal_monogram')) $defaultVal = 'M & N';
                                        }
                                    @endphp

                                    <div class="mayhen_client_form_group" style="margin-bottom: 12px;">
                                        <label class="mayhen_client_form_label" for="{{ $fieldId }}" style="font-size: 0.8125rem;">
                                            {{ $field['label'] }}
                                            @if (!empty($field['required']))
                                                <span style="color: var(--mayhen-color-danger);">*</span>
                                            @endif
                                        </label>

                                        @if ($field['type'] === 'textarea')
                                            <textarea id="{{ $fieldId }}" 
                                                      name="{{ $field['key'] }}" 
                                                      class="mayhen_client_form_textarea" 
                                                      rows="2" 
                                                      data-sync-key="{{ $field['key'] }}"
                                                      placeholder="{{ $field['placeholder'] ?? '' }}"
                                                      {{ !empty($field['required']) ? 'required' : '' }}>{{ old($field['key'], $defaultVal) }}</textarea>
                                        @elseif ($field['type'] === 'date')
                                            <input type="date" 
                                                   id="{{ $fieldId }}" 
                                                   name="{{ $field['key'] }}" 
                                                   class="mayhen_client_form_input" 
                                                   data-sync-key="{{ $field['key'] }}"
                                                   value="{{ old($field['key'], $defaultVal) }}" 
                                                   {{ !empty($field['required']) ? 'required' : '' }}>
                                        @elseif ($field['type'] === 'color')
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <input type="color" 
                                                       id="{{ $fieldId }}" 
                                                       name="{{ $field['key'] }}" 
                                                       data-sync-key="{{ $field['key'] }}"
                                                       value="{{ old($field['key'], $defaultVal ?: '#C5A880') }}"
                                                       style="width: 44px; height: 36px; padding: 2px; border: 1px solid var(--mayhen-color-border); border-radius: 6px; cursor: pointer;">
                                                <span style="font-size: 0.8125rem; color: var(--mayhen-color-text-muted);">Bấm để chọn màu sắc chủ đạo</span>
                                            </div>
                                        @elseif ($field['type'] === 'gallery')
                                            <div style="background-color: var(--mayhen-color-bg-base); border: 1px dashed var(--mayhen-color-border); padding: 12px; border-radius: var(--mayhen-radius-md);">
                                                <input type="text" 
                                                       id="{{ $fieldId }}" 
                                                       name="{{ $field['key'] }}[]" 
                                                       class="mayhen_client_form_input" 
                                                       placeholder="Dán đường dẫn ảnh cưới hoặc album online"
                                                       value="{{ $selectedTemplate->thumbnail_url }}">
                                                <p style="font-size: 0.75rem; color: var(--mayhen-color-text-muted); margin: 6px 0 0;">
                                                    Mẫu VIP hỗ trợ hiển thị album ảnh cưới đa góc nhìn với hiệu ứng chuyển động mượt mà.
                                                </p>
                                            </div>
                                        @elseif ($field['type'] === 'repeater')
                                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 8px;">
                                                <input type="text" name="extra_data[timeline][0][time]" class="mayhen_client_form_input" placeholder="17:30" value="17:30">
                                                <input type="text" name="extra_data[timeline][0][title]" class="mayhen_client_form_input" placeholder="Đón khách & Chụp ảnh lưu niệm" value="Đón khách & Chụp ảnh">
                                            </div>
                                        @else
                                            <input type="text" 
                                                   id="{{ $fieldId }}" 
                                                   name="{{ $field['key'] }}" 
                                                   class="mayhen_client_form_input" 
                                                   data-sync-key="{{ $field['key'] }}"
                                                   value="{{ old($field['key'], $defaultVal) }}" 
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                   {{ !empty($field['required']) ? 'required' : '' }}>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg" style="width: 100%;">
                            Xuất Bản & Tạo Link Thiệp
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Realtime Live Preview Panel -->
            <div class="mayhen_editor_preview_panel">
                <div class="mayhen_home_phone_frame">
                    <div class="mayhen_home_phone_notch"></div>
                    <div class="mayhen_home_phone_screen">
                        <article class="mayhen_invitation_card" style="box-shadow: none; border: none; width: 100%;">
                            <div class="mayhen_invitation_card_header" style="padding-top: 10px;">
                                <span class="mayhen_invitation_subhead">Save Our Date</span>
                                <h1 class="mayhen_invitation_names" data-mayhen-preview="names" style="font-size: 1.65rem;">
                                    Đặng Minh Quân <span style="font-family: var(--mayhen-font-script); font-style: italic; color: var(--mayhen-color-primary);">&</span> Lê Ái Như
                                </h1>
                            </div>

                            <div class="mayhen_invitation_photo_wrap" style="padding: 0 10px; margin-bottom: 16px;">
                                <img src="{{ $selectedTemplate->thumbnail_url }}" alt="Ảnh cưới preview" class="mayhen_invitation_photo" style="aspect-ratio: 4/5;">
                            </div>

                            <div class="mayhen_invitation_countdown_box" style="margin: 0 10px 16px 10px; padding: 12px 10px;">
                                <div class="mayhen_invitation_countdown_title" data-mayhen-preview="date" style="font-size: 0.75rem; margin-bottom: 0;">
                                    Ngày 24.10.2026
                                </div>
                            </div>

                            <section class="mayhen_invitation_event_section" style="padding: 14px 10px;">
                                <div class="mayhen_invitation_event_time">18:00 &bull; Tiệc Mừng</div>
                                <div class="mayhen_invitation_event_venue" data-mayhen-preview="venue" style="font-size: 0.8125rem;">
                                    The White Palace &bull; TP. HCM
                                </div>
                            </section>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/editor.js') }}"></script>
@endpush
