<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Thiệp mời cưới: "{{ $wedding->groom_name }} & {{ $wedding->bride_name }}" | {{ config('app.name') }}</title>
    <meta name="description" content="Trân trọng kính mời quý khách tới chung vui cùng {{ $wedding->groom_name }} & {{ $wedding->bride_name }}.">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="noindex, follow">

    <!-- Open Graph for Zalo / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="Thiệp mời cưới: &quot;{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}&quot; | {{ config('app.name') }}">
    <meta property="og:description" content="Hôn lễ được tổ chức vào ngày {{ $wedding->wedding_date->format('d.m.Y') }} tại {{ $wedding->venue_name }}. Kính mời quý khách tới tham dự.">
    <meta property="og:image" content="{{ $wedding->cover_photo_url }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,400;1,600&family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/components/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/components/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/preview.css') }}">
</head>
<body class="mayhen_invitation_wrapper">
    @if (isset($isOwner) && $isOwner && isset($invitation))
        {{-- Floating Top Bar dành riêng cho Chủ Nhân Thiệp --}}
        <header class="mayhen_invitation_demo_banner" style="background: rgba(28, 25, 23, 0.95); z-index: 9999;">
            <div class="mayhen_invitation_banner_info">
                <span class="mayhen_invitation_banner_badge" style="background: rgba(16, 185, 129, 0.2); color: #34D399;">
                    <span class="mayhen_invitation_banner_badge_dot" style="background: #10B981;"></span>
                    <span>Chủ Nhân Thiệp</span>
                </span>
                <span style="font-size: 0.8125rem; color: #FFFFFF; font-weight: 500;">
                    {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
                </span>
            </div>

            <div class="mayhen_invitation_banner_actions">
                <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_invitation_banner_cta_btn" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFFFFF;" title="Chỉnh sửa lại nội dung thiệp">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span>Sửa Lại Thiệp</span>
                </a>
            </div>
        </header>
    @endif

    <!-- Floating Audio Control -->
    <button type="button" class="mayhen_invitation_music_btn" data-mayhen-action="toggle-music" aria-label="Bật/Tắt nhạc nền" title="Bật nhạc nền">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M9 18V5l12-2v13" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="6" cy="18" r="3"/>
            <circle cx="18" cy="16" r="3"/>
        </svg>
    </button>

    <div class="mayhen_invitation_envelope_stage">
        <!-- 1. The Royal Wax Seal Envelope -->
        <div class="mayhen_invitation_envelope" data-mayhen-component="envelope">
            <button type="button" class="mayhen_invitation_envelope_seal" data-mayhen-action="unseal-envelope" aria-label="Chạm để mở thiệp">
                {{ mb_substr($wedding->groom_name, 0, 1) }}&{{ mb_substr($wedding->bride_name, 0, 1) }}
            </button>
            <h2 class="mayhen_invitation_envelope_to">Trân Trọng Kính Mời Quý Khách</h2>
            <p class="mayhen_invitation_envelope_hint">Chạm vào con dấu sáp để mở thiệp cưới</p>
        </div>

        <!-- 2. The Main Invitation Card -->
        <article class="mayhen_invitation_card" data-mayhen-component="invitation-card" style="display: none; opacity: 0;">
            <!-- Header -->
            <div class="mayhen_invitation_card_header">
                <span class="mayhen_invitation_subhead">Save Our Date &bull; Lời Mời Cưới</span>
                <h1 class="mayhen_invitation_names">
                    {{ $wedding->groom_name }} <span class="mayhen_invitation_names_amp">&</span> {{ $wedding->bride_name }}
                </h1>
                <p style="font-size: 0.875rem; color: var(--mayhen-color-text-muted); font-style: italic;">
                    "{{ $wedding->story ?? 'Tình yêu là hành trình cùng nhau chia sẻ những điều tuyệt vời nhất.' }}"
                </p>
            </div>

            <!-- Couple Photo -->
            <div class="mayhen_invitation_photo_wrap">
                <img src="{{ $wedding->cover_photo_url }}" alt="Ảnh cưới cô dâu chú rể" class="mayhen_invitation_photo">
            </div>

            <!-- Live Countdown Box -->
            <div class="mayhen_invitation_countdown_box">
                <div class="mayhen_invitation_countdown_title">
                    Ngày Hạnh Phúc: {{ $wedding->wedding_date->format('d.m.Y') }}
                </div>
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

            <!-- Venue & Timeline -->
            <section class="mayhen_invitation_event_section">
                <h2 class="mayhen_invitation_event_title">{{ $wedding->venue_name }}</h2>
                <div class="mayhen_invitation_event_time">{{ $wedding->wedding_date->format('d/m/Y') }}</div>
                <div class="mayhen_invitation_event_venue">
                    {{ $wedding->venue_address }}
                </div>
                <a href="https://maps.google.com/?q={{ urlencode($wedding->venue_address ?? $wedding->venue_name) }}" target="_blank" rel="noopener noreferrer" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm">
                    Xem Bản Đồ & Chỉ Đường
                </a>
            </section>

            <!-- RSVP Form Section -->
            <section class="mayhen_invitation_rsvp_section" id="rsvp">
                <h2 class="mayhen_invitation_rsvp_title">Xác Nhận Tham Dự (RSVP)</h2>
                <p class="mayhen_invitation_rsvp_desc">
                    Sự hiện diện của quý khách là niềm vinh hạnh to lớn cho gia đình chúng tôi.
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
                        <label class="mayhen_client_form_label">Lời chúc gửi tới hai bạn</label>
                        <textarea class="mayhen_client_form_textarea" rows="3" placeholder="Gửi gắm những lời chúc phúc ấm áp..."></textarea>
                    </div>

                    <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary" style="width: 100%;">
                        Gửi Phản Hồi Xác Nhận
                    </button>
                </form>

                <div class="mayhen_invitation_alert_success" data-mayhen-component="rsvp-success">
                    Cảm ơn bạn rất nhiều! Phản hồi của bạn đã được gửi thành công đến cô dâu chú rể.
                </div>
            </section>

            <!-- Footer -->
            <footer style="text-align: center; padding: 24px; color: var(--mayhen-color-text-muted); font-size: 0.8125rem;">
                Trân trọng cảm ơn sự yêu thương của mọi người! &bull; {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
            </footer>
        </article>
    </div>

    <!-- Scripts -->
    <script type="module" src="{{ asset('assets/client/js/pages/preview.js') }}"></script>
</body>
</html>
