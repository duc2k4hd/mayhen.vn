@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $inv = $demoInvitation;
    $custom = is_array($inv->custom_data) ? $inv->custom_data : [];
    $envColor = $custom['envelope_color'] ?? ($template['envelope_color'] ?? 'crimson-red');
    $envLiner = $custom['envelope_liner'] ?? ($template['envelope_liner'] ?? 'gold-foil');
    $cardBorder = $custom['card_border'] ?? ($template['card_border'] ?? 'gold-double');
    $artworkMotif = $custom['artwork_motif'] ?? ($template['artwork_motif'] ?? 'tet-blossom');
    $backdrop = $custom['backdrop'] ?? ($template['backdrop'] ?? 'silk-cream');
    $fontStyle = $custom['font_style'] ?? 'serif';
    $showDatetimeVenue = array_key_exists('show_datetime_venue', $custom) ? (bool) $custom['show_datetime_venue'] : true;
    $rsvpQuestion = $custom['rsvp_question'] ?? '';
    $customPhotoData = $custom['custom_photo_data'] ?? '';

    $eventDateDisplay = $inv->event_date ? \Illuminate\Support\Carbon::parse($inv->event_date)->format('d . m . Y') : now()->addDays(15)->format('d . m . Y');
    $giftHeading = $category['field_labels']['gift_title'] ?? 'Phong Bao Lì Xì & Mừng Quà (VietQR)';
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $inv->title }} | {{ $brandName }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($inv->invitation_message, 155) }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $inv->title }} — Trân Trọng Gửi Tới {{ $guestName }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($inv->invitation_message, 155) }}">
    @if(!empty($isDemoPreview))
        <meta name="robots" content="noindex, follow">
    @else
        <meta name="robots" content="noindex, nofollow">
    @endif
    @include('client.components.favicon')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/event-invitations.css') }}">
</head>
<body style="margin: 0; padding: 0;">

<div class="mayhen_event_card_page mayhen_event_single_screen_mode mayhen_event_palette_{{ $inv->color_palette ?: 'ruby' }} mayhen_event_env_{{ $envColor }} mayhen_event_backdrop_{{ $backdrop }} mayhen_event_font_{{ $fontStyle }}" id="mayhenEventPreviewRoot">

    {{-- Thanh điều hướng gọn trên cùng khi khách đang xem mẫu Demo --}}
    @if(!empty($isDemoPreview) && empty($isEmbed))
        <div class="mayhen_event_card_topbar">
            <a href="{{ route('event_invitations.category', $template['category_slug']) }}" class="mayhen_event_btn_outline" style="padding: 7px 14px;">
                &larr; Quay lại danh mục
            </a>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="mayhen_event_btn_outline" data-event-toggle-envelope style="padding: 7px 13px;">
                    &#9655; Xem Lại Hiệu Ứng Rút Thiệp
                </button>
                <a href="{{ route('event_invitations.studio', $template['slug']) }}" class="mayhen_event_btn_primary" style="padding: 8px 18px;">
                    ✦ Vào Tạo Thử &amp; Tùy Chỉnh Mẫu Này &rarr;
                </a>
            </div>
        </div>
    @endif

    <div class="mayhen_event_single_stage_viewport">

        @if(session('success'))
            <div class="mayhen_event_toast_banner">
                {{ session('success') }}
            </div>
        @endif

        {{-- SÂN KHẤU 1 TRANG DUY NHẤT (CHUẨN VIDEO PAPERLESS POST: PHONG BÌ MỞ NẮP + RÚT THIỆP + NÚT REPLAY BÊN TRÁI) --}}
        <div class="mayhen_event_3d_stage is-extracted" id="mayhenEvent3dStage">

            {{-- Nút tròn Play/Replay bên trái tấm thiệp giống hệt 00:06 trong video Paperless Post --}}
            <button type="button"
                    class="mayhen_event_replay_circle_btn"
                    data-event-toggle-envelope
                    title="Xem lại hiệu ứng mở phong bì & rút thiệp"
                    aria-label="Xem lại hiệu ứng rút thiệp">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polygon points="6 4 20 12 6 20 6 4"></polygon>
                </svg>
            </button>

            {{-- TỜ THIỆP 1 TRANG DUY NHẤT (GỒM KHUNG TRANH SỰ KIỆN BAO QUANH + LỜI MỜI + NGÀY GIỜ + ĐỊA ĐIỂM TRÊN CÙNG 1 MẶT THIỆP) --}}
            <article class="mayhen_event_stationery_sheet mayhen_event_border_{{ $cardBorder }}" id="mayhenEventStationerySheet">
                {{-- Lớp tranh minh họa bao quanh 4 cạnh mặt thiệp theo đúng từng sự kiện --}}
                <div class="mayhen_event_card_scene_layer" aria-hidden="true">
                    {!! \App\Services\EventInvitationService::renderCardSceneSvg($artworkMotif) !!}
                </div>

                <div class="mayhen_event_card_inner_content">
                    <span class="mayhen_event_guest_greeting">
                        Kính gửi: <strong data-event-sync="guest_preview">{{ $guestName }}</strong>
                    </span>

                    <div class="mayhen_event_badge_kicker" data-event-sync="event_badge">{{ $inv->event_badge }}</div>

                    {{-- Ảnh riêng của khách tải lên (nếu có) --}}
                    <div class="mayhen_event_custom_photo_wrap" id="evtCustomPhotoPreviewWrap" style="{{ empty($customPhotoData) ? 'display: none;' : '' }}">
                        <img src="{{ $customPhotoData }}" alt="{{ $inv->host_name }}" id="evtCustomPhotoPreviewImg">
                    </div>

                    <h1 class="mayhen_event_host_heading" data-event-sync="host_name">{{ $inv->host_name }}</h1>
                    <p class="mayhen_event_subhost" data-event-sync="sub_host_name">{{ $inv->sub_host_name }}</p>

                    <p class="mayhen_event_message_body" data-event-sync="invitation_message">{{ $inv->invitation_message }}</p>

                    {{-- THỜI GIAN & ĐỊA ĐIỂM IN TRỰC TIẾP TRÊN MẶT THIỆP 1 TRANG (Y HỆT VIDEO PAPERLESS POST) --}}
                    <div class="mayhen_event_oncard_details" id="evtPreviewDatetimeSection" style="{{ !$showDatetimeVenue ? 'display: none;' : '' }}">
                        <div class="mayhen_event_oncard_divider" aria-hidden="true">✦</div>
                        <div class="mayhen_event_oncard_datetime">
                            <span data-event-sync="event_date">{{ $eventDateDisplay }}</span>
                            <span>&bull;</span>
                            <span>LÚC <strong data-event-sync="party_time">{{ $inv->party_time ?: '18:00' }}</strong></span>
                        </div>
                        <div class="mayhen_event_oncard_lunar" data-event-sync="lunar_note">{{ $inv->lunar_note }}</div>
                        <div class="mayhen_event_oncard_venue" data-event-sync="venue_name">{{ $inv->venue_name }}</div>
                        <div class="mayhen_event_oncard_address" data-event-sync="venue_address">{{ $inv->venue_address }}</div>
                    </div>
                </div>
            </article>

            {{-- PHONG BÌ 3D MỞ NẮP LÓT HOA VĂN TRƯỢT XUỐNG DƯỚI ĐÁY TỜ THIỆP (Y HỆT VIDEO 00:01 -> 00:06) --}}
            <div class="mayhen_event_3d_envelope_back" aria-hidden="true">
                <div class="mayhen_event_3d_flap_top"></div>
                <div class="mayhen_event_3d_liner_inner mayhen_event_liner_{{ $envLiner }}" id="mayhenEventPreviewLiner"></div>
                <div class="mayhen_event_3d_pocket_front"></div>
            </div>
        </div>

        {{-- THANH NÚT TIỆN ÍCH NỔI GỌN GÀNG DƯỚI CHÂN SÂN KHẤU (MỞ POPUP KHI BẤM, TUYỆT ĐỐI KHÔNG KÉO DÀI TRANG) --}}
        <nav class="mayhen_event_floating_dock" aria-label="Tác vụ nhanh của khách mời">
            <a href="{{ $inv->map_url ?: '#' }}"
               target="_blank"
               rel="noopener noreferrer"
               class="mayhen_event_dock_pill"
               id="evtPreviewVenueSection"
               data-event-sync="map_url"
               style="{{ !$showDatetimeVenue ? 'display: none;' : '' }}">
                📍 Chỉ Đường Bản Đồ
            </a>

            <button type="button"
                    class="mayhen_event_dock_pill mayhen_event_dock_pill_primary"
                    id="evtPreviewRsvpBox"
                    data-open-event-popup="rsvp"
                    style="{{ !$inv->enable_rsvp ? 'display: none;' : '' }}">
                ✉ Xác Nhận Tham Dự &amp; Lời Chúc
                @if(isset($responses) && $responses->isNotEmpty())
                    <span>({{ $responses->count() }})</span>
                @endif
            </button>

            <button type="button"
                    class="mayhen_event_dock_pill"
                    id="evtPreviewGiftQrBox"
                    data-open-event-popup="gift"
                    style="{{ !$inv->enable_gift_qr ? 'display: none;' : '' }}">
                🧧 {{ $giftHeading }}
            </button>
        </nav>

    </div>

    {{-- POPUP 1: XÁC NHẬN THAM DỰ (RSVP) & SỔ LỜI CHÚC (KHÔNG LÀM DÀI TRANG THIỆP) --}}
    <div class="mayhen_event_modal_overlay" id="mayhenEventRsvpPopup" role="dialog" aria-modal="true" aria-label="Xác nhận tham dự và gửi lời chúc">
        <div class="mayhen_event_modal_dialog" style="max-width: 520px; text-align: left;">
            <button type="button" class="mayhen_event_modal_close" data-close-event-popup>&times;</button>
            <span class="mayhen_event_badge_kicker">Phản Hồi &amp; Lời Chúc Mừng</span>
            <h2 class="mayhen_event_venue_title" style="margin-bottom: 14px;">Xác Nhận Tham Dự &amp; Gửi Lời Chúc</h2>

            @if(!empty($isDemoPreview))
                <div style="padding: 14px; border-radius: 12px; background: #FFF8EC; border: 1px solid #E5C896; font-size: 13px; color: #7A5823; margin-bottom: 12px;">
                    ✦ Đây là bản xem trước. Khi xuất bản thiệp chính thức (Gói Pro), khách mời có thể gửi xác nhận tham dự và viết lời chúc trực tiếp tại popup này.
                </div>
            @endif

            <form method="POST" action="{{ empty($isDemoPreview) ? route('event_invitations.rsvp', $inv->slug) : '#' }}" class="mayhen_event_rsvp_form" @if(!empty($isDemoPreview)) onsubmit="return false;" @endif>
                @csrf
                <div>
                    <input type="text" name="guest_name" class="mayhen_event_input" placeholder="Tên của bạn / Gia đình *" required maxlength="120">
                </div>
                <div class="mayhen_event_rsvp_grid_2">
                    <select name="attendance_status" class="mayhen_event_select">
                        <option value="attending">Sẽ tham dự chung vui</option>
                        <option value="maybe">Đang sắp xếp thời gian</option>
                        <option value="declined">Gửi lời chúc từ xa</option>
                    </select>
                    <select name="guests_count" class="mayhen_event_select">
                        <option value="1">Đi 1 người</option>
                        <option value="2">Đi 2 người</option>
                        <option value="3">Đi 3 người</option>
                        <option value="4">Đi cả gia đình (4 người)</option>
                    </select>
                </div>
                @if(!empty($rsvpQuestion))
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #5C5349; margin-bottom: 4px;" data-event-sync="rsvp_question">{{ $rsvpQuestion }}</label>
                        <input type="text" name="custom_answer" class="mayhen_event_input" placeholder="Câu trả lời của bạn (tùy chọn)">
                    </div>
                @endif
                <div>
                    <textarea name="wish_message" rows="3" class="mayhen_event_textarea" placeholder="Nhập lời chúc tốt đẹp gửi tới chủ tiệc..."></textarea>
                </div>
                @if(empty($isDemoPreview))
                    <button type="submit" class="mayhen_event_btn_primary" style="width: 100%; padding: 13px;">
                        ✦ Gửi Phản Hồi &amp; Lời Chúc
                    </button>
                @endif
            </form>

            @if(isset($responses) && $responses->isNotEmpty())
                <div style="margin-top: 18px; border-top: 1px solid #EAE2D6; padding-top: 14px; max-height: 220px; overflow-y: auto;">
                    <h3 style="font-size: 14px; margin: 0 0 10px; color: var(--mh-evt-primary);">Lời chúc từ người thân &amp; bạn bè ({{ $responses->count() }})</h3>
                    @foreach($responses as $resp)
                        @if(!empty($resp->wish_message))
                            <div style="padding: 9px 12px; border-radius: 10px; background: var(--mh-evt-bg); margin-bottom: 8px;">
                                <strong style="font-size: 12.5px; color: #1F1D1A;">{{ $resp->guest_name }}</strong>
                                <p style="margin: 3px 0 0; font-size: 13px; color: #4A433C;">{{ $resp->wish_message }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- POPUP 2: PHONG BAO LÌ XÌ / MỪNG QUÀ VIETQR --}}
    <div class="mayhen_event_modal_overlay" id="mayhenEventGiftPopup" role="dialog" aria-modal="true" aria-label="Phong bao lì xì và mừng quà">
        <div class="mayhen_event_modal_dialog">
            <button type="button" class="mayhen_event_modal_close" data-close-event-popup>&times;</button>
            <span class="mayhen_event_badge_kicker">Phong Bao May Mắn &amp; Quà Mừng</span>
            <h2 class="mayhen_event_venue_title">{{ $giftHeading }}</h2>
            <p style="font-size: 13.5px; color: #6A635B; margin: 6px 0 14px;" data-event-sync="gift_note">
                {{ $inv->gift_note ?: 'Cảm ơn tình cảm và lời chúc tốt đẹp của Quý vị dành cho gia đình.' }}
            </p>
            <div class="mayhen_event_qr_box">
                <div style="font-size: 13px; color: #5C5349; margin-bottom: 4px;">
                    Ngân hàng: <strong data-event-sync="bank_name">{{ $inv->bank_name ?: 'Vietcombank' }}</strong>
                </div>
                <div style="font-size: 18px; font-weight: 700; color: var(--mh-evt-primary); letter-spacing: 0.05em; margin-bottom: 4px;" data-event-sync="bank_account_number">
                    {{ $inv->bank_account_number ?: '0123456789' }}
                </div>
                <div style="font-size: 13px; font-weight: 600; color: #1F1D1A;" data-event-sync="bank_account_holder">
                    {{ $inv->bank_account_holder ?: 'CHU TAI KHOAN' }}
                </div>
            </div>
        </div>
    </div>

</div>

<script src="{{ asset_v('assets/client/js/pages/event-invitations.js') }}"></script>
</body>
</html>
