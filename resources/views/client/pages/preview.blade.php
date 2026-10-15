@php
    $isEmbed = request()->boolean('embed');
    $hasEnvelope = in_array($template->style_layout, ['envelope-wax-seal']) || in_array($template->category, ['luxury']);
    $showEnvelope = $hasEnvelope && !$isEmbed;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Xem Thử: {{ $template['name'] }} — {{ config('app.name') }}</title>
    <meta name="description" content="Trải nghiệm thực tế thiệp cưới online phong cách {{ $template['category_label'] }} trên {{ config('app.name') }}.">
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,400;1,600&family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Core Styles -->
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/components/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/components/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/preview.css') }}">
</head>
<body class="mayhen_invitation_wrapper {{ $isEmbed ? 'mayhen_invitation_is_embed' : '' }}">
    @if (!$isEmbed)
        <!-- Top Demo Navigation Bar (Redesigned Quiet Luxury Header) -->
        <header class="mayhen_invitation_demo_banner">
            <div class="mayhen_invitation_banner_info">
                <span class="mayhen_invitation_banner_badge">
                    <span class="mayhen_invitation_banner_badge_dot"></span>
                    <span>{{ $template->tier_label }} &bull; {{ $template->complexity_label }}</span>
                </span>
                <h1 class="mayhen_invitation_banner_title" title="{{ $template['name'] }}">
                    {{ $template['name'] }}
                </h1>
            </div>

            <div class="mayhen_invitation_banner_actions">
                <!-- Music Control -->
                <button type="button" class="mayhen_invitation_music_btn" data-mayhen-action="toggle-music" aria-label="Bật hoặc tắt nhạc nền" title="Bật nhạc nền">
                    <svg class="mayhen_icon_music_static" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18V5l12-2v13" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                    <span class="mayhen_equalizer_mini" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                </button>

                <!-- Dùng Mẫu CTA -->
                @php
                    $isLocked = $template->tier !== 'free' && !auth()->check();
                    $targetCta = $isLocked
                        ? route('login', ['redirect' => route('invitation.create', ['template' => $template->slug])])
                        : route('invitation.create', ['template' => $template->slug]);
                @endphp
                <a href="{{ $targetCta }}" class="mayhen_invitation_banner_cta_btn" title="{{ $isLocked ? 'Đăng nhập để sử dụng mẫu này' : 'Sử dụng mẫu thiệp này để bắt đầu tạo thiệp cưới' }}">
                    <span>{{ $isLocked ? 'Đăng Nhập Để Dùng' : 'Tạo Thiệp Với Mẫu Này' }}</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </header>
    @endif

    <div class="mayhen_invitation_envelope_stage">
        @if ($showEnvelope)
            <!-- 1. The Royal Wax Seal Envelope -->
            <div class="mayhen_invitation_envelope" data-mayhen-component="envelope">
                <button type="button" class="mayhen_invitation_envelope_seal" data-mayhen-action="unseal-envelope" aria-label="Chạm để mở thiệp">
                    M&A
                </button>
                <h2 class="mayhen_invitation_envelope_to">Kính Mời: Bạn & Người Thương</h2>
                <p class="mayhen_invitation_envelope_hint">Chạm vào con dấu sáp để mở thiệp cưới</p>
            </div>
        @endif

        <!-- 2. The Main Invitation Card -->
        <article class="mayhen_invitation_card" data-mayhen-component="invitation-card" style="{{ $showEnvelope ? 'display: none; opacity: 0;' : 'display: block; opacity: 1;' }}">
            <!-- Header -->
            <div class="mayhen_invitation_card_header">
                <span class="mayhen_invitation_subhead">Save Our Date &bull; Lời Mời Cưới</span>
                <h1 class="mayhen_invitation_names">
                    Minh Quân <span class="mayhen_invitation_names_amp">&</span> Ái Như
                </h1>
                <p style="font-size: 0.875rem; color: var(--mayhen-color-text-muted); font-style: italic;">
                    "Yêu là khi nhìn thấy cả thế giới trong mắt một người."
                </p>
            </div>

            <!-- Photo -->
            <div class="mayhen_invitation_photo_wrap">
                <img src="{{ $template['image'] }}" alt="Ảnh cưới cô dâu chú rể" class="mayhen_invitation_photo">
            </div>

            <!-- Live Countdown Box -->
            <div class="mayhen_invitation_countdown_box">
                <div class="mayhen_invitation_countdown_title">Đếm Ngược Ngày Hạnh Phúc</div>
                <div class="mayhen_invitation_countdown_grid">
                    <div class="mayhen_invitation_countdown_item">
                        <span class="mayhen_invitation_countdown_num" data-mayhen-countdown="days">12</span>
                        <span class="mayhen_invitation_countdown_unit">Ngày</span>
                    </div>
                    <div class="mayhen_invitation_countdown_item">
                        <span class="mayhen_invitation_countdown_num" data-mayhen-countdown="hours">08</span>
                        <span class="mayhen_invitation_countdown_unit">Giờ</span>
                    </div>
                    <div class="mayhen_invitation_countdown_item">
                        <span class="mayhen_invitation_countdown_num" data-mayhen-countdown="minutes">45</span>
                        <span class="mayhen_invitation_countdown_unit">Phút</span>
                    </div>
                    <div class="mayhen_invitation_countdown_item">
                        <span class="mayhen_invitation_countdown_num" data-mayhen-countdown="seconds">30</span>
                        <span class="mayhen_invitation_countdown_unit">Giây</span>
                    </div>
                </div>
            </div>

            <!-- Timeline & Location -->
            <section class="mayhen_invitation_event_section">
                <h2 class="mayhen_invitation_event_title">Tiệc Cưới Thân Mật</h2>
                <div class="mayhen_invitation_event_time">18:00 &bull; Thứ Bảy, Ngày 24 Tháng 10 Năm 2026</div>
                <div class="mayhen_invitation_event_venue">
                    Trung Tâm Tiệc Cưới The White Palace<br>
                    Sảnh Grand Ballroom &bull; 194 Hoàng Văn Thụ, TP. Hồ Chí Minh
                </div>
                <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm">
                    Xem Bản Đồ & Chỉ Đường
                </a>
            </section>

            <!-- RSVP Form Section -->
            <section class="mayhen_invitation_rsvp_section" id="rsvp">
                <h2 class="mayhen_invitation_rsvp_title">Xác Nhận Tham Dự (RSVP)</h2>
                <p class="mayhen_invitation_rsvp_desc">
                    Sự hiện diện của quý khách là niềm vinh hạnh to lớn cho gia đình chúng tôi. Xin vui lòng phản hồi trước ngày 15.10.2026.
                </p>

                <form data-mayhen-component="rsvp-form">
                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label">Họ và tên của bạn</label>
                        <input type="text" class="mayhen_client_form_input" required placeholder="Ví dụ: Hoàng Long & Người thương">
                    </div>

                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label">Bạn có tham dự được không?</label>
                        <select class="mayhen_client_form_select" required>
                            <option value="attending">Chắc chắn sẽ tham dự</option>
                            <option value="declined">Rất tiếc không thể tham dự</option>
                            <option value="uncertain">Chưa chắc chắn</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label">Số người lớn</label>
                            <input type="number" class="mayhen_client_form_input" min="1" max="10" value="1">
                        </div>
                        <div class="mayhen_client_form_group">
                            <label class="mayhen_client_form_label">Số trẻ em</label>
                            <input type="number" class="mayhen_client_form_input" min="0" max="10" value="0">
                        </div>
                    </div>

                    <div class="mayhen_client_form_group">
                        <label class="mayhen_client_form_label">Lời chúc gửi tới cô dâu chú rể</label>
                        <textarea class="mayhen_client_form_textarea" rows="3" placeholder="Gửi gắm những lời chúc phúc ấm áp nhất..."></textarea>
                    </div>

                    <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary" style="width: 100%;">
                        Gửi Phản Hồi Xác Nhận
                    </button>
                </form>

                <div class="mayhen_invitation_alert_success" data-mayhen-component="rsvp-success">
                    Cảm ơn bạn rất nhiều! Phản hồi của bạn đã được gửi thành công đến cô dâu chú rể.
                </div>
            </section>

            <!-- Thank you note -->
            <footer style="text-align: center; padding: 24px; color: var(--mayhen-color-text-muted); font-size: 0.8125rem;">
                Trân trọng cảm ơn sự yêu thương của mọi người! &bull; Minh Quân & Ái Như
            </footer>
        </article>
    </div>

    <!-- Scripts -->
    <script type="module" src="{{ asset('assets/client/js/pages/preview.js') }}"></script>
</body>
</html>
