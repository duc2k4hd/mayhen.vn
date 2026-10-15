@extends('client.layouts.master')

@section('title', 'Tạo Thiệp Cưới Online — ' . ($selectedTemplate ? $selectedTemplate->name : 'Thiệp Mời') . ' | ' . config('app.name'))
@section('meta_description', 'Tự tạo thiệp cưới online phong cách Quiet Luxury dễ dàng chỉ trong 3 phút với chế độ xem thử realtime.')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/preview.css') }}">
@endpush

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

        <!-- 2. Mobile Mini Live Preview Dock (Cố định đầu màn hình Mobile — Loại bỏ 100% vuốt mỏi tay) -->
        <div class="mayhen_editor_mobile_live_dock">
            <div class="mayhen_editor_dock_content">
                <img src="{{ $selectedTemplate->thumbnail_url }}" alt="Preview" class="mayhen_editor_dock_avatar" id="mayhen_dock_thumb">
                <div class="mayhen_editor_dock_info">
                    <div class="mayhen_editor_dock_live_badge">
                        <span class="mayhen_editor_dock_live_dot"></span>
                        <span>Xem thử trực tiếp</span>
                    </div>
                    <div class="mayhen_editor_dock_names" data-mayhen-preview="names">
                        Minh Quân &bull; Ái Như
                    </div>
                    <div class="mayhen_editor_dock_sub">
                        <span data-mayhen-preview="date">24.10.2026</span> • <span data-mayhen-preview="venue">The White Palace</span>
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
            <!-- Left: Form Nhập Liệu Chia Bước Gọn Gàng -->
            <div class="mayhen_editor_form_panel">
                <!-- Stepper Navigation Pills -->
                <nav class="mayhen_editor_stepper_nav" aria-label="Các bước nhập liệu">
                    <button type="button" class="mayhen_editor_stepper_tab mayhen_editor_stepper_tab_active" data-mayhen-step="1">
                        <span class="mayhen_editor_stepper_tab_num">1</span>
                        <span>Đôi Uyên Ương</span>
                    </button>
                    <button type="button" class="mayhen_editor_stepper_tab" data-mayhen-step="2">
                        <span class="mayhen_editor_stepper_tab_num">2</span>
                        <span>Ngày & Sảnh Tiệc</span>
                    </button>
                    <button type="button" class="mayhen_editor_stepper_tab" data-mayhen-step="3">
                        <span class="mayhen_editor_stepper_tab_num">3</span>
                        <span>Ảnh & Lời Ngỏ</span>
                    </button>
                    <button type="button" class="mayhen_editor_stepper_tab" data-mayhen-step="4">
                        <span class="mayhen_editor_stepper_tab_num">4</span>
                        <span>Mừng Cưới & Giao Diện</span>
                    </button>
                </nav>

                <form id="mayhen_editor_form" action="{{ route('invitation.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template_id" id="template_id" value="{{ $selectedTemplate->id }}">

                    <!-- BƯỚC 1: THÔNG TIN CÔ DÂU & CHÚ RỂ -->
                    <div class="mayhen_editor_step_content mayhen_editor_step_active" data-step-panel="1">
                        <div class="mayhen_editor_step_header">
                            <h2 class="mayhen_editor_step_heading">
                                <span>1. Thông Tin Cô Dâu & Chú Rể</span>
                            </h2>
                            <p class="mayhen_editor_step_subtext">Nhập tên của đôi bạn để hiển thị trang trọng trên trang bìa thiệp cưới.</p>
                        </div>

                        <div class="mayhen_editor_field_grid_2">
                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="groom_name">
                                    Họ tên Chú Rể <span style="color: var(--mayhen-color-danger);">*</span>
                                </label>
                                <input type="text" id="groom_name" name="wedding[groom_name]" class="mayhen_client_form_input" data-sync-key="wedding.groom_name" value="{{ old('wedding.groom_name', 'Đặng Minh Quân') }}" placeholder="Ví dụ: Minh Quân" required>
                                <div class="mayhen_editor_input_help">Tên chú rể hiển thị trang trọng</div>
                            </div>

                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="bride_name">
                                    Họ tên Cô Dâu <span style="color: var(--mayhen-color-danger);">*</span>
                                </label>
                                <input type="text" id="bride_name" name="wedding[bride_name]" class="mayhen_client_form_input" data-sync-key="wedding.bride_name" value="{{ old('wedding.bride_name', 'Lê Ái Như') }}" placeholder="Ví dụ: Ái Như" required>
                                <div class="mayhen_editor_input_help">Tên cô dâu hiển thị thanh tao</div>
                            </div>
                        </div>

                        <div class="mayhen_editor_field_grid_2" style="margin-top: 10px;">
                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="groom_parents">Phụ mẫu Nhà Trai (Tùy chọn)</label>
                                <input type="text" id="groom_parents" name="wedding[groom_parents]" class="mayhen_client_form_input" data-sync-key="wedding.groom_parents" value="{{ old('wedding.groom_parents', 'Ông: Nguyễn Văn A & Bà: Lê Thị B') }}" placeholder="Ông: ... & Bà: ...">
                            </div>

                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="bride_parents">Phụ mẫu Nhà Gái (Tùy chọn)</label>
                                <input type="text" id="bride_parents" name="wedding[bride_parents]" class="mayhen_client_form_input" data-sync-key="wedding.bride_parents" value="{{ old('wedding.bride_parents', 'Ông: Trần Văn C & Bà: Phạm Thị D') }}" placeholder="Ông: ... & Bà: ...">
                            </div>
                        </div>
                    </div>

                    <!-- BƯỚC 2: THỜI GIAN & SẢNH TIỆC CƯỚI -->
                    <div class="mayhen_editor_step_content" data-step-panel="2">
                        <div class="mayhen_editor_step_header">
                            <h2 class="mayhen_editor_step_heading">
                                <span>2. Thời Gian & Địa Điểm Tổ Chức Tiệc</span>
                            </h2>
                            <p class="mayhen_editor_step_subtext">Cung cấp ngày giờ cử hành hôn lễ để khách mời kịp thời sắp xếp chung vui.</p>
                        </div>

                        <div class="mayhen_editor_field_grid_2">
                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="wedding_date">
                                    Ngày cử hành hôn lễ <span style="color: var(--mayhen-color-danger);">*</span>
                                </label>
                                <input type="date" id="wedding_date" name="wedding[wedding_date]" class="mayhen_client_form_input" data-sync-key="wedding.wedding_date" value="{{ old('wedding.wedding_date', '2026-10-24') }}" required>
                            </div>

                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="venue_name">
                                    Tên Sảnh Tiệc / Trung Tâm Hội Nghị <span style="color: var(--mayhen-color-danger);">*</span>
                                </label>
                                <input type="text" id="venue_name" name="wedding[venue_name]" class="mayhen_client_form_input" data-sync-key="wedding.venue_name" value="{{ old('wedding.venue_name', 'The White Palace Ballroom') }}" placeholder="Ví dụ: Trung Tâm Hội Nghị White Palace" required>
                            </div>
                        </div>

                        <div class="mayhen_client_form_group" style="margin-top: 10px;">
                            <label class="mayhen_client_form_label" for="venue_address">
                                Địa chỉ cụ thể sảnh tiệc <span style="color: var(--mayhen-color-danger);">*</span>
                            </label>
                            <textarea id="venue_address" name="wedding[venue_address]" class="mayhen_client_form_textarea" rows="2" data-sync-key="wedding.venue_address" placeholder="Ví dụ: 194 Hoàng Văn Thụ, Phường 9, Quận Phú Nhuận, TP. Hồ Chí Minh" required>{{ old('wedding.venue_address', '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP. Hồ Chí Minh') }}</textarea>
                            <div class="mayhen_editor_input_help">Khách có thể bấm trực tiếp để mở Google Maps chỉ đường</div>
                        </div>
                    </div>

                    <!-- BƯỚC 3: HÌNH ẢNH & LỜI NGỎ YÊU THƯƠNG -->
                    <div class="mayhen_editor_step_content" data-step-panel="3">
                        <div class="mayhen_editor_step_header">
                            <h2 class="mayhen_editor_step_heading">
                                <span>3. Ảnh Cưới & Câu Chuyện Tình Yêu</span>
                            </h2>
                            <p class="mayhen_editor_step_subtext">Gợi nhắc những khoảnh khắc ngọt ngào nhất của hai bạn gửi tới quan khách.</p>
                        </div>

                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label" for="cover_photo_url">Đường dẫn ảnh cưới chính (Ảnh bìa)</label>
                            <input type="url" id="cover_photo_url" name="wedding[cover_photo_url]" class="mayhen_client_form_input" data-sync-key="wedding.cover_photo_url" value="{{ old('wedding.cover_photo_url', $selectedTemplate->thumbnail_url) }}" placeholder="https://... dán link ảnh cưới chất lượng cao">
                            <div class="mayhen_editor_input_help">Ảnh bìa đại diện hiển thị nổi bật trên đầu thiệp cưới</div>
                        </div>

                        <div class="mayhen_client_form_group" style="margin-top: 10px;">
                            <label class="mayhen_client_form_label" for="story">Lời ngỏ tâm tình / Chuyện tình yêu (Love Story)</label>
                            <textarea id="story" name="wedding[story]" class="mayhen_client_form_textarea" rows="3" data-sync-key="wedding.story" placeholder="Chia sẻ khoảnh khắc cầu hôn hoặc lời cảm ơn chân thành đến người thân...">{{ old('wedding.story', 'Yêu là khi nhìn thấy cả thế giới bình yên trong mắt một người. Cảm ơn bạn đã luôn đồng hành cùng chúng mình trên hành trình hạnh phúc này.') }}</textarea>
                        </div>

                        <div class="mayhen_client_form_group" style="margin-top: 10px;">
                            <label class="mayhen_client_form_label" for="music_url">Bài hát nhạc nền hôn lễ (Link file MP3)</label>
                            <input type="url" id="music_url" name="wedding[music_url]" class="mayhen_client_form_input" data-sync-key="wedding.music_url" value="{{ old('wedding.music_url', 'https://assets.mixkit.co/music/preview/mixkit-romantic-wedding-love-643.mp3') }}" placeholder="https://... link mp3 nhạc lãng mạn">
                            <div class="mayhen_editor_input_help">Tự động phát nhạc êm dịu khi khách mở thiệp cưới</div>
                        </div>
                    </div>

                    <!-- BƯỚC 4: MỪNG CƯỚI VIETQR & TÙY BIẾN GIAO DIỆN -->
                    <div class="mayhen_editor_step_content" data-step-panel="4">
                        <div class="mayhen_editor_step_header">
                            <h2 class="mayhen_editor_step_heading">
                                <span>4. Quà Mừng Cưới & Tùy Chỉnh Màu Sắc</span>
                            </h2>
                            <p class="mayhen_editor_step_subtext">Tích hợp mã QR mừng cưới từ xa và điều chỉnh màu sắc trang nhã hợp sở thích.</p>
                        </div>

                        <div class="mayhen_editor_field_grid_2">
                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="bank_name">Tên Ngân Hàng Thụ Hưởng</label>
                                <input type="text" id="bank_name" name="wedding[bank_name]" class="mayhen_client_form_input" data-sync-key="wedding.bank_name" value="{{ old('wedding.bank_name', 'Vietcombank') }}" placeholder="Ví dụ: Vietcombank, Techcombank, MB">
                            </div>

                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="bank_account_number">Số Tài Khoản</label>
                                <input type="text" id="bank_account_number" name="wedding[bank_account_number]" class="mayhen_client_form_input" data-sync-key="wedding.bank_account_number" value="{{ old('wedding.bank_account_number', '1023456789') }}" placeholder="Nhập chính xác số tài khoản">
                            </div>
                        </div>

                        <div class="mayhen_editor_field_grid_2" style="margin-top: 10px;">
                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label" for="bank_account_name">Tên Chủ Tài Khoản</label>
                                <input type="text" id="bank_account_name" name="wedding[bank_account_name]" class="mayhen_client_form_input" data-sync-key="wedding.bank_account_name" value="{{ old('wedding.bank_account_name', 'DANG MINH QUAN') }}" placeholder="DANG MINH QUAN">
                            </div>

                            <div class="mayhen_client_form_group">
                                <label class="mayhen_client_form_label">Màu Sắc Nhấn Chủ Đạo</label>
                                <div class="mayhen_editor_color_picker_wrap">
                                    <input type="color" id="accent_color" name="invitation[custom_config][accent_color]" class="mayhen_editor_color_input" data-sync-key="invitation.custom_config.accent_color" value="{{ old('invitation.custom_config.accent_color', '#C5A880') }}">
                                    <span style="font-size: 0.8125rem; color: var(--mayhen-color-text-main); font-weight: 500;">Bấm để đổi tông màu thiệp</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Column: Live Sticky Phone Preview (Trên PC) -->
            <div class="mayhen_editor_preview_panel">
                <div class="mayhen_editor_phone_wrap">
                    <div class="mayhen_editor_phone_notch"></div>
                    <div class="mayhen_editor_phone_screen">
                        <article class="mayhen_invitation_card" style="box-shadow: none; border: none; width: 100%; padding: 12px 10px;">
                            <!-- Header -->
                            <div class="mayhen_invitation_card_header" style="padding-top: 6px;">
                                <span class="mayhen_invitation_subhead" style="font-size: 0.75rem; letter-spacing: 0.15em;">Save Our Date</span>
                                <h1 class="mayhen_invitation_names" data-mayhen-preview="names" style="font-size: 1.625rem; margin: 6px 0;">
                                    Đặng Minh Quân <span style="font-family: var(--mayhen-font-script); font-style: italic; color: var(--mayhen-color-primary);">&</span> Lê Ái Như
                                </h1>
                                <p style="font-size: 0.78125rem; color: var(--mayhen-color-text-muted); font-style: italic;" data-mayhen-preview="story">
                                    "Yêu là khi nhìn thấy cả thế giới bình yên trong mắt một người."
                                </p>
                            </div>

                            <!-- Cover Image -->
                            <div class="mayhen_invitation_photo_wrap" style="padding: 0 4px; margin-bottom: 14px;">
                                <img src="{{ $selectedTemplate->thumbnail_url }}" alt="Ảnh cưới preview" class="mayhen_invitation_photo" data-mayhen-preview="photo" style="aspect-ratio: 4/5; border-radius: 8px;">
                            </div>

                            <!-- Countdown Card -->
                            <div class="mayhen_invitation_countdown_box" style="margin: 0 4px 14px; padding: 10px;">
                                <div class="mayhen_invitation_countdown_title" data-mayhen-preview="date" style="font-size: 0.75rem; margin-bottom: 0;">
                                    Ngày 24.10.2026
                                </div>
                            </div>

                            <!-- Event Details -->
                            <section class="mayhen_invitation_event_section" style="padding: 12px 8px; margin: 0 4px 14px;">
                                <div class="mayhen_invitation_event_time" style="font-size: 0.8125rem;">18:00 &bull; Tiệc Mừng Hôn Lễ</div>
                                <div class="mayhen_invitation_event_venue" data-mayhen-preview="venue" style="font-size: 0.875rem; font-weight: 600; margin: 4px 0;">
                                    The White Palace Ballroom
                                </div>
                                <div class="mayhen_invitation_event_addr" data-mayhen-preview="address" style="font-size: 0.75rem; color: var(--mayhen-color-text-muted);">
                                    194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP. Hồ Chí Minh
                                </div>
                            </section>

                            <!-- VietQR Section Preview -->
                            <div style="background: #FFFFFF; border: 1px dashed var(--mayhen-color-border); border-radius: 8px; padding: 10px; text-align: center; margin: 0 4px;">
                                <div style="font-size: 0.75rem; font-weight: 600; color: var(--mayhen-color-primary-hover);">Hộp Mừng Cưới VietQR</div>
                                <div style="font-size: 0.6875rem; color: var(--mayhen-color-text-muted);" data-mayhen-preview="banking">
                                    Vietcombank • 1023456789 • DANG MINH QUAN
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="mayhen_editor_preview_hint">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Mọi thông tin bạn nhập đều tự động cập nhật ngay lập tức</span>
                </div>
            </div>
        </div>

        <!-- 4. Sticky Bottom Action Bar (Cố định ở đáy màn hình cả PC & Mobile) -->
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

        <!-- 5. Modal Fullscreen Preview trên Mobile (Bật lên chỉ 1 chạm, đóng lại tiếp tục gõ) -->
        <div class="mayhen_editor_modal_preview" id="mayhen_mobile_preview_modal" role="dialog" aria-modal="true" aria-label="Xem trước thiệp cưới toàn màn hình">
            <div class="mayhen_editor_modal_sheet">
                <div class="mayhen_editor_modal_header">
                    <div style="font-weight: 600; font-size: 0.9375rem; color: var(--mayhen-color-text-main);">
                        Bản Xem Thử Trực Tiếp
                    </div>
                    <button type="button" class="mayhen_editor_modal_close_btn" data-mayhen-action="close-preview-modal" aria-label="Đóng bản xem trước">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="mayhen_editor_modal_body" id="mayhen_mobile_modal_body">
                    <!-- Cloned realtime content injects here via JS -->
                </div>
            </div>
        </div>

        <!-- 6. Modal Đổi Mẫu Thiệp (Template Switcher Modal) -->
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
