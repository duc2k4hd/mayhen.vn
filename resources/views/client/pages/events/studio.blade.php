@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $labels = $category['field_labels'] ?? [];
    $presets = $category['wording_presets'] ?? [];
@endphp

@section('title', 'Studio Tùy Chỉnh Thiệp 1 Trang & Phong Bì 3D: ' . $template['name'] . ' | ' . $brandName)
@section('robots', 'noindex, follow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/event-invitations.css') }}">
@endpush

@section('content')
<div class="mayhen_event_studio_layout">

    {{-- CỘT TRÁI: STUDIO 5 TAB CHUYÊN SÂU CHO THIỆP 1 TRANG --}}
    <aside class="mayhen_event_studio_sidebar" aria-label="Bảng chỉnh sửa thiệp sự kiện 1 trang và phong bì 3D">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <a href="{{ route('event_invitations.category', $template['category_slug']) }}" style="font-size: 12.5px; color: #6A635B; text-decoration: none; font-weight: 600;">
                &larr; Đổi mẫu khác
            </a>
            <span class="mayhen_event_pro_pill" style="margin: 0; font-size: 10px; padding: 4px 10px;">
                {{ $hasProPlan ? '✦ Tài Khoản Gói Pro VIP' : '✦ Studio Dùng Thử Miễn Phí' }}
            </span>
        </div>

        <h1 style="font-family: var(--mayhen-font-serif, 'Playfair Display', Georgia, serif); font-size: 19px; margin: 0 0 12px; color: #1F1D1A;">
            {{ $template['name'] }}
        </h1>

        <div id="mayhenEventStudioToast" style="display: none; padding: 11px 14px; border-radius: 10px; background: #1F1D1A; color: #F4E2B8; font-size: 12.5px; font-weight: 600; margin-bottom: 12px;"></div>

        {{-- Thanh 5 Tab Chức Năng --}}
        <div class="mayhen_event_studio_tabs" role="tablist">
            <button type="button" class="mayhen_event_studio_tab_btn is-active" data-event-tab-btn="card">1. Mặt Thiệp</button>
            <button type="button" class="mayhen_event_studio_tab_btn" data-event-tab-btn="envelope">2. Phong Bì</button>
            <button type="button" class="mayhen_event_studio_tab_btn" data-event-tab-btn="content">3. Lời Văn</button>
            <button type="button" class="mayhen_event_studio_tab_btn" data-event-tab-btn="venue">4. Bản Đồ</button>
            <button type="button" class="mayhen_event_studio_tab_btn" data-event-tab-btn="rsvp">5. RSVP &amp; QR</button>
        </div>

        <form id="mayhenEventStudioForm" onsubmit="return false;">
            <input type="hidden" id="evtDraftIdInput" name="draft_id" value="{{ $state['id'] ?? '' }}">
            <input type="hidden" name="template_slug" value="{{ $template['slug'] }}">
            <input type="hidden" id="evtColorPaletteInput" name="color_palette" value="{{ $state['color_palette'] }}">
            <input type="hidden" id="evtEnvelopeColorInput" name="envelope_color" value="{{ $state['envelope_color'] }}">
            <input type="hidden" id="evtEnvelopeLinerInput" name="envelope_liner" value="{{ $state['envelope_liner'] }}">
            <input type="hidden" id="evtCardBorderInput" name="card_border" value="{{ $state['card_border'] }}">
            <input type="hidden" id="evtBackdropInput" name="backdrop" value="{{ $state['backdrop'] }}">
            <input type="hidden" id="evtFontStyleInput" name="font_style" value="{{ $state['font_style'] }}">
            <input type="hidden" id="evtCustomPhotoDataInput" name="custom_photo_data" value="{{ $state['custom_photo_data'] }}">

            {{-- ================= TAB 1: HÌNH DÁNG CẮT KHUÔN MẶT THIỆP, MÀU CHỮ, PHÔNG CHỮ & ẢNH ================= --}}
            <div class="mayhen_event_studio_panel is-active" data-event-tab-panel="card">
                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Khuôn Cắt &amp; Viền Mặt Thiệp (Oval Sóng, Vòm, Sọc...)</h2>
                    <div class="mayhen_event_swatch_grid">
                        @foreach($cardBorders as $bKey => $bVal)
                            <button type="button" class="mayhen_event_swatch_btn {{ $state['card_border'] === $bKey ? 'is-active' : '' }}" data-event-border-btn="{{ $bKey }}">
                                <span>{{ $bVal['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Tông Màu Chữ &amp; Điểm Nhấn</h2>
                    <div class="mayhen_event_swatch_grid">
                        @foreach($palettes as $pKey => $pVal)
                            <button type="button" class="mayhen_event_swatch_btn {{ $state['color_palette'] === $pKey ? 'is-active' : '' }}" data-event-palette-btn="{{ $pKey }}">
                                <span style="width: 14px; height: 14px; border-radius: 50%; background: {{ $pVal['primary'] }}; flex-shrink: 0;"></span>
                                <span>{{ $pVal['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Kiểu Chữ Tiêu Đề</h2>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        <button type="button" class="mayhen_event_swatch_btn {{ $state['font_style'] === 'serif' ? 'is-active' : '' }}" data-event-font-btn="serif">Cổ Điển Serif</button>
                        <button type="button" class="mayhen_event_swatch_btn {{ $state['font_style'] === 'sans' ? 'is-active' : '' }}" data-event-font-btn="sans">Hiện Đại Sans</button>
                        <button type="button" class="mayhen_event_swatch_btn {{ $state['font_style'] === 'script' ? 'is-active' : '' }}" data-event-font-btn="script">Nghiêng Nghệ Thuật</button>
                    </div>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Tải Ảnh Nhỏ Lồng Vào Thiệp (Tùy chọn)</h2>
                    <input type="file" id="evtCustomPhotoFile" accept="image/*" class="mayhen_event_input" style="font-size: 12px;">
                    <button type="button" id="btnClearEventCustomPhoto" class="mayhen_event_btn_outline" style="margin-top: 8px; padding: 6px 12px; font-size: 11.5px; {{ empty($state['custom_photo_data']) ? 'display: none;' : '' }}">
                        &times; Gỡ ảnh đã tải lên
                    </button>
                </div>
            </div>

            {{-- ================= TAB 2: PHONG BÌ 3D, LỚP LÓT HOA VĂN & MẶT BÀN PHÔNG NỀN ================= --}}
            <div class="mayhen_event_studio_panel" data-event-tab-panel="envelope">
                <div class="mayhen_event_studio_block">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <h2 class="mayhen_event_studio_block_title" style="margin: 0;">Màu Vỏ Phong Bì 3D (8 Màu)</h2>
                        <button type="button" class="mayhen_event_btn_outline" data-event-toggle-envelope style="padding: 4px 10px; font-size: 11px;">
                            &#9655; Thử Rút Thiệp
                        </button>
                    </div>
                    <div class="mayhen_event_swatch_grid">
                        @foreach($envelopeColors as $eKey => $eVal)
                            <button type="button" class="mayhen_event_swatch_btn {{ $state['envelope_color'] === $eKey ? 'is-active' : '' }}" data-event-env-color-btn="{{ $eKey }}">
                                <span style="width: 16px; height: 16px; border-radius: 50%; background: {{ $eVal['hex'] }}; border: 1px solid rgba(0,0,0,0.15); flex-shrink: 0;"></span>
                                <span>{{ $eVal['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Hoa Văn Lớp Lót Trong Phong Bì (Envelope Liner)</h2>
                    <div class="mayhen_event_swatch_grid">
                        @foreach($envelopeLiners as $lKey => $lVal)
                            <button type="button" class="mayhen_event_swatch_btn {{ $state['envelope_liner'] === $lKey ? 'is-active' : '' }}" data-event-liner-btn="{{ $lKey }}">
                                <span class="mayhen_event_liner_{{ $lKey }}" style="width: 18px; height: 18px; border-radius: 4px; border: 1px solid #D8CFC2; flex-shrink: 0;"></span>
                                <span>{{ $lVal['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Mặt Bàn &amp; Phông Nền Sân Khấu (Backdrop)</h2>
                    <div class="mayhen_event_swatch_grid">
                        @foreach($backdrops as $bgKey => $bgVal)
                            <button type="button" class="mayhen_event_swatch_btn {{ $state['backdrop'] === $bgKey ? 'is-active' : '' }}" data-event-backdrop-btn="{{ $bgKey }}">
                                <span>{{ $bgVal['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= TAB 3: NỘI DUNG NGẮN GỌN IN TRỰC TIẾP TRÊN 1 MẶT THIỆP ================= --}}
            <div class="mayhen_event_studio_panel" data-event-tab-panel="content">
                @if(!empty($presets))
                    <div class="mayhen_event_studio_block">
                        <h2 class="mayhen_event_studio_block_title">✦ Chọn Nhanh Lời Văn Mẫu Ngắn Gọn (1 Chạm)</h2>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            @foreach($presets as $preset)
                                <button type="button"
                                        class="mayhen_event_swatch_btn"
                                        data-wording-preset
                                        data-preset-badge="{{ $preset['badge'] }}"
                                        data-preset-sub="{{ $preset['sub'] }}"
                                        data-preset-msg="{{ $preset['msg'] }}">
                                    <span>✦ {{ $preset['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Chữ Hiển Thị Trên Tờ Thiệp 1 Trang</h2>
                    <div class="mayhen_event_studio_field">
                        <label>{{ $labels['event_badge'] ?? 'Dòng mở đầu phía trên' }}</label>
                        <input type="text" name="event_badge" class="mayhen_event_input" value="{{ $state['event_badge'] }}" data-event-input="event_badge">
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>{{ $labels['host_name'] ?? 'Tên Gia Chủ / Nhân Vật Chính *' }}</label>
                        <input type="text" name="host_name" class="mayhen_event_input" value="{{ $state['host_name'] }}" data-event-input="host_name" required>
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>{{ $labels['sub_host_name'] ?? 'Dòng phụ đề / Cột mốc' }}</label>
                        <input type="text" name="sub_host_name" class="mayhen_event_input" value="{{ $state['sub_host_name'] }}" data-event-input="sub_host_name">
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>Lời nhắn ngắn trên mặt thiệp (1–2 câu súc tích)</label>
                        <textarea name="invitation_message" rows="3" class="mayhen_event_textarea" data-event-input="invitation_message">{{ $state['invitation_message'] }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ================= TAB 4: NGÀY GIỜ & ĐỊA ĐIỂM IN NGAY TRÊN MẶT THIỆP ================= --}}
            <div class="mayhen_event_studio_panel" data-event-tab-panel="venue">
                <div class="mayhen_event_studio_block">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #85182A; cursor: pointer;">
                        <input type="checkbox" id="evtShowDatetimeVenue" name="show_datetime_venue" value="1" {{ $state['show_datetime_venue'] ? 'checked' : '' }}>
                        <span>In Ngày Giờ &amp; Địa Điểm lên mặt thiệp (Tắt nếu là Thiệp Chúc Tết / Trung Thu / 8-3 từ xa)</span>
                    </label>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Thời Gian &amp; Địa Điểm Trên Mặt Thiệp</h2>
                    <div class="mayhen_event_rsvp_grid_2">
                        <div class="mayhen_event_studio_field">
                            <label>Ngày tổ chức</label>
                            <input type="date" name="event_date" class="mayhen_event_input" value="{{ $state['event_date'] }}" data-event-input="event_date">
                        </div>
                        <div class="mayhen_event_studio_field">
                            <label>Giờ khai tiệc</label>
                            <input type="text" name="party_time" class="mayhen_event_input" value="{{ $state['party_time'] }}" data-event-input="party_time">
                        </div>
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>Ghi chú ngày Âm lịch / Dresscode ngắn</label>
                        <input type="text" name="lunar_note" class="mayhen_event_input" value="{{ $state['lunar_note'] }}" data-event-input="lunar_note">
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>Tên Nhà hàng / Tư gia</label>
                        <input type="text" name="venue_name" class="mayhen_event_input" value="{{ $state['venue_name'] }}" data-event-input="venue_name">
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>Địa chỉ hiển thị trên mặt thiệp</label>
                        <input type="text" name="venue_address" class="mayhen_event_input" value="{{ $state['venue_address'] }}" data-event-input="venue_address">
                    </div>
                    <div class="mayhen_event_studio_field">
                        <label>Link Google Maps (Mở khi khách bấm nút Chỉ Đường)</label>
                        <input type="url" name="map_url" class="mayhen_event_input" value="{{ $state['map_url'] }}" data-event-input="map_url">
                    </div>
                </div>
            </div>

            {{-- ================= TAB 5: POPUP RSVP & PHONG BAO LÌ XÌ VIETQR ================= --}}
            <div class="mayhen_event_studio_panel" data-event-tab-panel="rsvp">
                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Nút Popup Xác Nhận Tham Dự (RSVP)</h2>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; margin-bottom: 12px; cursor: pointer;">
                        <input type="checkbox" id="evtEnableRsvp" name="enable_rsvp" value="1" {{ $state['enable_rsvp'] ? 'checked' : '' }}>
                        <span>Hiển thị nút mở Popup RSVP &amp; Gửi lời chúc dưới chân thiệp</span>
                    </label>
                    <div class="mayhen_event_studio_field">
                        <label>Câu hỏi thêm dành cho khách mời</label>
                        <input type="text" name="rsvp_question" class="mayhen_event_input" value="{{ $state['rsvp_question'] }}" data-event-input="rsvp_question" placeholder="Ví dụ: Bạn dùng tiệc chay hay mặn?">
                    </div>
                </div>

                <div class="mayhen_event_studio_block">
                    <h2 class="mayhen_event_studio_block_title">Nút Popup Phong Bao Lì Xì / Mừng Quà (VietQR)</h2>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; margin-bottom: 10px; cursor: pointer;">
                        <input type="checkbox" id="evtEnableGiftQr" name="enable_gift_qr" value="1" {{ $state['enable_gift_qr'] ? 'checked' : '' }}>
                        <span>Hiển thị nút mở Popup Lì xì / Mừng quà (VietQR)</span>
                    </label>

                    <div id="evtGiftQrConfigFields" style="{{ !$state['enable_gift_qr'] ? 'display: none;' : '' }}; padding-top: 8px; border-top: 1px dashed #E5DCD0;">
                        <div class="mayhen_event_studio_field">
                            <label>Tên Ngân hàng</label>
                            <input type="text" name="bank_name" class="mayhen_event_input" value="{{ $state['bank_name'] }}" data-event-input="bank_name">
                        </div>
                        <div class="mayhen_event_studio_field">
                            <label>Số tài khoản</label>
                            <input type="text" name="bank_account_number" class="mayhen_event_input" value="{{ $state['bank_account_number'] }}" data-event-input="bank_account_number">
                        </div>
                        <div class="mayhen_event_studio_field">
                            <label>Chủ tài khoản</label>
                            <input type="text" name="bank_account_holder" class="mayhen_event_input" value="{{ $state['bank_account_holder'] }}" data-event-input="bank_account_holder">
                        </div>
                        <div class="mayhen_event_studio_field">
                            <label>Lời cảm ơn trong popup lì xì</label>
                            <input type="text" name="gift_note" class="mayhen_event_input" value="{{ $state['gift_note'] }}" data-event-input="gift_note">
                        </div>
                    </div>
                </div>
            </div>

            {{-- THANH HÀNH ĐỘNG CỐ ĐỊNH DƯỚI ĐÁY SIDEBAR --}}
            <div style="display: grid; grid-template-columns: 1fr 1.45fr; gap: 10px; position: sticky; bottom: 0; background: #FFFFFF; padding: 12px 0; border-top: 1px solid #EAE2D6;">
                <button type="button" id="btnEventSaveDraft" class="mayhen_event_btn_outline">
                    Lưu Bản Nháp
                </button>
                <button type="button" id="btnEventPublishPro" class="mayhen_event_btn_primary">
                    ✦ Xuất Bản Thiệp (PRO)
                </button>
            </div>
        </form>
    </aside>

    {{-- CỘT PHẢI: SÂN KHẤU XEM TRƯỚC TRỰC TIẾP 1 TRANG DUY NHẤT (PHONG BÌ MỞ NẮP + RÚT THIỆP + NÚT PLAY BÊN TRÁI) --}}
    <section class="mayhen_event_studio_stage" aria-label="Sân khấu xem trước thiệp 1 trang và phong bì 3D">
        <div class="mayhen_event_card_page mayhen_event_single_screen_mode mayhen_event_palette_{{ $state['color_palette'] }} mayhen_event_env_{{ $state['envelope_color'] }} mayhen_event_backdrop_{{ $state['backdrop'] }} mayhen_event_font_{{ $state['font_style'] }}" id="mayhenEventPreviewRoot" style="border-radius: 26px; min-height: 760px; box-shadow: 0 22px 60px rgba(28, 25, 23, 0.16);">
            <div class="mayhen_event_single_stage_viewport">

                <div class="mayhen_event_3d_stage is-extracted" id="mayhenEvent3dStage">
                    <button type="button"
                            class="mayhen_event_replay_circle_btn"
                            data-event-toggle-envelope
                            title="Xem lại hiệu ứng mở phong bì & rút thiệp"
                            aria-label="Xem lại hiệu ứng rút thiệp">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polygon points="6 4 20 12 6 20 6 4"></polygon>
                        </svg>
                    </button>

                    <article class="mayhen_event_stationery_sheet mayhen_event_border_{{ $state['card_border'] }}" id="mayhenEventStationerySheet">
                        <div class="mayhen_event_card_scene_layer" aria-hidden="true">
                            {!! \App\Services\EventInvitationService::renderCardSceneSvg($state['artwork_motif']) !!}
                        </div>

                        <div class="mayhen_event_card_inner_content">
                            <span class="mayhen_event_guest_greeting">
                                Kính gửi: <strong>Quý khách &amp; Gia đình</strong>
                            </span>

                            <div class="mayhen_event_badge_kicker" data-event-sync="event_badge">{{ $state['event_badge'] }}</div>

                            <div class="mayhen_event_custom_photo_wrap" id="evtCustomPhotoPreviewWrap" style="{{ empty($state['custom_photo_data']) ? 'display: none;' : '' }}">
                                <img src="{{ $state['custom_photo_data'] }}" alt="{{ $state['host_name'] }}" id="evtCustomPhotoPreviewImg">
                            </div>

                            <h2 class="mayhen_event_host_heading" data-event-sync="host_name">{{ $state['host_name'] }}</h2>
                            <p class="mayhen_event_subhost" data-event-sync="sub_host_name">{{ $state['sub_host_name'] }}</p>
                            <p class="mayhen_event_message_body" data-event-sync="invitation_message">{{ $state['invitation_message'] }}</p>

                            <div class="mayhen_event_oncard_details" id="evtPreviewDatetimeSection" style="{{ !$state['show_datetime_venue'] ? 'display: none;' : '' }}">
                                <div class="mayhen_event_oncard_divider" aria-hidden="true">✦</div>
                                <div class="mayhen_event_oncard_datetime">
                                    <span data-event-sync="event_date">{{ \Illuminate\Support\Carbon::parse($state['event_date'])->format('d . m . Y') }}</span>
                                    <span>&bull;</span>
                                    <span>LÚC <strong data-event-sync="party_time">{{ $state['party_time'] }}</strong></span>
                                </div>
                                <div class="mayhen_event_oncard_lunar" data-event-sync="lunar_note">{{ $state['lunar_note'] }}</div>
                                <div class="mayhen_event_oncard_venue" data-event-sync="venue_name">{{ $state['venue_name'] }}</div>
                                <div class="mayhen_event_oncard_address" data-event-sync="venue_address">{{ $state['venue_address'] }}</div>
                            </div>
                        </div>
                    </article>

                    <div class="mayhen_event_3d_envelope_back" aria-hidden="true">
                        <div class="mayhen_event_3d_flap_top"></div>
                        <div class="mayhen_event_3d_liner_inner mayhen_event_liner_{{ $state['envelope_liner'] }}" id="mayhenEventPreviewLiner"></div>
                        <div class="mayhen_event_3d_pocket_front"></div>
                    </div>
                </div>

                {{-- Thanh nút tiện ích nổi dưới chân thiệp 1 trang --}}
                <nav class="mayhen_event_floating_dock" aria-label="Nút tiện ích dưới chân thiệp">
                    <a href="{{ $state['map_url'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_event_dock_pill" id="evtPreviewVenueSection" data-event-sync="map_url" style="{{ !$state['show_datetime_venue'] ? 'display: none;' : '' }}">
                        📍 Chỉ Đường Bản Đồ
                    </a>
                    <span class="mayhen_event_dock_pill mayhen_event_dock_pill_primary" id="evtPreviewRsvpBox" style="{{ !$state['enable_rsvp'] ? 'display: none;' : '' }}">
                        ✉ Xác Nhận Tham Dự &amp; Lời Chúc
                    </span>
                    <span class="mayhen_event_dock_pill" id="evtPreviewGiftQrBox" style="{{ !$state['enable_gift_qr'] ? 'display: none;' : '' }}">
                        🧧 {{ $labels['gift_title'] ?? 'Phong Bao Lì Xì (VietQR)' }}
                    </span>
                </nav>

            </div>
        </div>
    </section>

</div>

{{-- MODAL 1: CỔNG KHÓA ĐẶC QUYỀN GÓI PRO KHI BẤM XUẤT BẢN --}}
<div class="mayhen_event_modal_overlay" id="mayhenEventProModal" role="dialog" aria-modal="true">
    <div class="mayhen_event_modal_dialog">
        <button type="button" class="mayhen_event_modal_close" data-close-event-modal>&times;</button>
        <span class="mayhen_event_pro_pill">✦ ĐẶC QUYỀN THÀNH VIÊN GÓI PRO VIP ✦</span>
        <h2 style="font-family: var(--mayhen-font-serif, 'Playfair Display', Georgia, serif); font-size: 24px; margin: 4px 0 10px; color: #1F1D1A;">
            Bản Nháp Đã Được Lưu An Toàn!
        </h2>
        <p id="mayhenEventProModalMessage" style="font-size: 14px; line-height: 1.65; color: #5C5349; margin: 0 0 20px;">
            Đặc quyền Xuất bản &amp; Lấy link gửi Zalo cho toàn bộ 30+ mẫu Thiệp Tết, Trung Thu, Giáng Sinh, Đầy Tháng, Thôi Nôi, Sinh Nhật, Tân Gia, Tất Niên &amp; Khai Trương thuộc về Thành Viên Gói Pro VIP (199.000đ).
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('checkout.show', 'pro') }}" id="mayhenEventProModalPrimaryBtn" class="mayhen_event_btn_primary" style="padding: 14px 20px;">
                Nâng Cấp Gói Pro VIP (199.000đ) Để Xuất Bản Ngay &rarr;
            </a>
            <button type="button" class="mayhen_event_btn_outline" data-close-event-modal>
                Tiếp tục chỉnh sửa thử trong Studio
            </button>
        </div>
    </div>
</div>

{{-- MODAL 2: XUẤT BẢN THÀNH CÔNG (DÀNH CHO THÀNH VIÊN GÓI PRO) --}}
<div class="mayhen_event_modal_overlay" id="mayhenEventPublishedModal" role="dialog" aria-modal="true">
    <div class="mayhen_event_modal_dialog">
        <button type="button" class="mayhen_event_modal_close" data-close-event-modal>&times;</button>
        <span class="mayhen_event_pro_pill">✦ ĐÃ XUẤT BẢN CHÍNH THỨC ✦</span>
        <h2 style="font-family: var(--mayhen-font-serif, 'Playfair Display', Georgia, serif); font-size: 24px; margin: 4px 0 10px; color: #1F1D1A;">
            Thiệp Phong Bì 3D Đã Sẵn Sàng!
        </h2>
        <div style="text-align: left; margin-bottom: 14px;">
            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px;">Cá nhân hóa tên người nhận / khách mời (tùy chọn):</label>
            <input type="text" id="mayhenEventGuestNameInput" class="mayhen_event_input" placeholder="Ví dụ: Vợ chồng Anh Minh / Quý Đối Tác (để trống nếu gửi chung)">
        </div>
        <div style="text-align: left; margin-bottom: 18px;">
            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px;">Đường dẫn gửi Zalo / Messenger:</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="mayhenEventPublishedUrl" class="mayhen_event_input" readonly>
                <button type="button" id="btnCopyEventPublishedUrl" class="mayhen_event_btn_primary" style="white-space: nowrap;">
                    Sao Chép Link
                </button>
            </div>
        </div>
        <a href="#" id="mayhenEventOpenPublicLink" target="_blank" rel="noopener noreferrer" class="mayhen_event_btn_outline" style="width: 100%;">
            Mở Xem Thiệp Chính Thức &nearr;
        </a>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset_v('assets/client/js/pages/event-invitations.js') }}"></script>
@endpush
