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
                    <h2 class="mayhen_editor_section_title">1. Chọn Phong Cách Thiệp</h2>
                    <div class="mayhen_editor_template_picker">
                        @foreach ($allTemplates as $tpl)
                            <div class="mayhen_editor_template_radio_card {{ $tpl->id === $selectedTemplate->id ? 'mayhen_editor_template_radio_selected' : '' }}" data-mayhen-template-card data-mayhen-template-id="{{ $tpl->id }}">
                                <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" class="mayhen_editor_template_radio_thumb">
                                <div class="mayhen_editor_template_radio_name">{{ $tpl->name }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Step 2: Couple Info -->
                    <h2 class="mayhen_editor_section_title">2. Thông Tin Cặp Đôi</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label" for="groom_name">Tên Chú Rể *</label>
                            <input type="text" id="groom_name" name="groom_name" class="mayhen_client_form_input" required value="Đặng Minh Quân" placeholder="Ví dụ: Minh Quân">
                        </div>
                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label" for="bride_name">Tên Cô Dâu *</label>
                            <input type="text" id="bride_name" name="bride_name" class="mayhen_client_form_input" required value="Lê Ái Như" placeholder="Ví dụ: Ái Như">
                        </div>
                    </div>

                    <!-- Step 3: Date & Venue -->
                    <h2 class="mayhen_editor_section_title">3. Ngày Cưới & Địa Điểm</h2>
                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label" for="wedding_date">Ngày Tổ Chức Hôn Lễ *</label>
                        <input type="date" id="wedding_date" name="wedding_date" class="mayhen_client_form_input" required value="2026-10-24">
                    </div>

                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label" for="venue_name">Tên Trung Tâm Tiệc Cưới</label>
                        <input type="text" id="venue_name" name="venue_name" class="mayhen_client_form_input" value="The White Palace" placeholder="Ví dụ: The White Palace">
                    </div>

                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label" for="venue_address">Địa Chỉ Sảnh Tiệc</label>
                        <input type="text" id="venue_address" name="venue_address" class="mayhen_client_form_input" value="Sảnh Grand Ballroom &bull; 194 Hoàng Văn Thụ, TP. HCM">
                    </div>

                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label" for="story">Lời Nhắn / Câu Chuyện Tình Yêu</label>
                        <textarea id="story" name="story" class="mayhen_client_form_textarea" rows="2" placeholder="Gửi gắm thông điệp lãng mạn tới khách mời...">Yêu là khi nhìn thấy cả thế giới trong mắt một người.</textarea>
                    </div>

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
