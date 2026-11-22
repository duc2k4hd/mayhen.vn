@extends('client.layouts.event-studio')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $labels = $category['field_labels'] ?? [];
    $presets = $category['wording_presets'] ?? [];
    $giftHeading = $labels['gift_title'] ?? 'Phong Bao Lì Xì & Mừng Quà (VietQR)';
    $envelopeBackImg = $template['envelope_back_img'] ?? null;
    $backdropImg = $template['backdrop_img'] ?? null;
@endphp

@section('title', 'Studio Thiết Kế Thiệp 1 Trang & Phong Bì 3D: ' . $template['name'] . ' | ' . $brandName)
@section('robots', 'noindex, follow')

@section('content')
<div class="mayhen_event_studio_app" id="mayhenEventStudioApp" data-mobile-mode="split">

    {{-- =========================================================================
         1. THANH ĐIỀU HÀNH STUDIO RIÊNG BIỆT (STANDALONE STUDIO TOPBAR)
         ========================================================================= --}}
    <header class="mayhen_event_studio_topbar" aria-label="Thanh công cụ điều hành Studio Thiệp Sự Kiện">
        {{-- Khu vực Trái: Quay lại + Đổi nhanh 30 mẫu thiệp + Trạng thái lưu --}}
        <div class="mayhen_event_studio_topbar_left">
            <a href="{{ route('event_invitations.category', $template['category_slug']) }}"
               class="mayhen_event_studio_back_btn"
               title="Quay lại danh mục {{ $category['short_name'] ?? '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kho Mẫu</span>
            </a>

            <div class="mayhen_event_studio_topbar_divider" aria-hidden="true"></div>

            <button type="button"
                    class="mayhen_event_studio_tpl_trigger"
                    id="btnOpenTemplateSwitcherModal"
                    title="Bấm để chuyển nhanh sang mẫu thiệp sự kiện khác">
                <span class="mayhen_event_studio_tpl_icon" aria-hidden="true">{{ $category['icon_symbol'] ?? '✦' }}</span>
                <span class="mayhen_event_studio_tpl_meta">
                    <small>{{ $category['short_name'] ?? 'Thiệp Sự Kiện' }}</small>
                    <strong>{{ $template['name'] }}</strong>
                </span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <span class="mayhen_event_studio_save_status" id="mayhenEventStudioSaveBadge">
                <span class="mayhen_event_studio_save_dot"></span>
                <span id="mayhenEventStudioSaveText">Sẵn sàng thiết kế</span>
            </span>
        </div>

        {{-- Khu vực Giữa: Chuyển thiết bị Desktop/Mobile + Zoom + Xem Phong Bì + Phối Ngẫu Nhiên --}}
        <div class="mayhen_event_studio_topbar_center" role="toolbar" aria-label="Công cụ sân khấu">
            <div class="mayhen_event_studio_tool_group">
                <button type="button" class="mayhen_event_studio_tool_btn is-active" data-studio-viewport="desktop" title="Xem khung Máy tính">
                    🖥 <span>Desktop</span>
                </button>
                <button type="button" class="mayhen_event_studio_tool_btn" data-studio-viewport="mobile" title="Xem khung Điện thoại">
                    📱 <span>Mobile</span>
                </button>
            </div>

            <div class="mayhen_event_studio_tool_group">
                <button type="button" class="mayhen_event_studio_tool_btn" data-studio-zoom="0.88" title="Thu nhỏ 88%">88%</button>
                <button type="button" class="mayhen_event_studio_tool_btn is-active" data-studio-zoom="1" title="Kích thước chuẩn 100%">100%</button>
                <button type="button" class="mayhen_event_studio_tool_btn" data-studio-zoom="1.08" title="Phóng to 108%">108%</button>
            </div>

            <button type="button" class="mayhen_event_studio_tool_btn" data-event-toggle-envelope title="Chạy hiệu ứng mở phong bì & rút thiệp 3D">
                &#9655; <span>Rút Thiệp</span>
            </button>

            <button type="button" class="mayhen_event_studio_tool_btn" id="btnStudioToggleEnvelopeVisibility" title="Ẩn/Hiện phong bì phía dưới để tập trung sửa chữ">
                ✉ <span>Ẩn/Hiện Phong Bì</span>
            </button>

            <button type="button" class="mayhen_event_studio_tool_btn" id="btnStudioMagicShuffle" title="Phối ngẫu nhiên bộ Phong bì + Lớp lót + Khung viền + Nền đẹp mắt">
                🎲 <span>Phối Màu Đẹp</span>
            </button>
        </div>

        {{-- Khu vực Phải: Huy hiệu gói + Lưu nháp + Xuất bản PRO --}}
        <div class="mayhen_event_studio_topbar_right">
            <span class="mayhen_event_studio_plan_chip {{ $hasProPlan ? 'is-pro' : '' }}">
                {{ $hasProPlan ? '✦ GÓI PRO VIP' : '✦ DÙNG THỬ MIỄN PHÍ' }}
            </span>

            <button type="button" id="btnEventSaveDraftTop" data-trigger-save-draft class="mayhen_event_btn_outline mayhen_event_studio_top_action">
                Lưu Nháp
            </button>

            <button type="button" id="btnEventPublishProTop" data-trigger-publish-pro class="mayhen_event_btn_primary mayhen_event_studio_top_action">
                ✦ Xuất Bản (PRO)
            </button>
        </div>
    </header>

    {{-- Thanh chuyển đổi nhanh Sân Khấu / Bảng Chỉnh Sửa dành riêng cho Mobile & Tablet --}}
    <div class="mayhen_event_studio_mobile_modebar" aria-label="Chuyển chế độ trên điện thoại">
        <button type="button" class="mayhen_event_studio_mobile_mode_btn is-active" data-mobile-view-btn="edit">
            ✎ Bảng Chỉnh Sửa
        </button>
        <button type="button" class="mayhen_event_studio_mobile_mode_btn" data-mobile-view-btn="stage">
            👁 Xem Thiệp &amp; Phong Bì 3D
        </button>
        <button type="button" class="mayhen_event_studio_mobile_mode_btn" data-event-toggle-envelope>
            &#9655; Rút Thiệp
        </button>
    </div>

    {{-- =========================================================================
         2. KHÔNG GIAN LÀM VIỆC CHÍNH (RAIL 6 CÔNG CỤ + INSPECTOR + SÂN KHẤU 3D)
         ========================================================================= --}}
    <div class="mayhen_event_studio_workspace">

        {{-- CỘT 1: THANH ĐIỀU HƯỚNG 6 TAB CHỨC NĂNG (STUDIO ICON RAIL) --}}
        <nav class="mayhen_event_studio_rail" role="tablist" aria-label="6 nhóm công cụ thiết kế thiệp sự kiện">
            <button type="button" class="mayhen_event_studio_rail_btn is-active" data-event-tab-btn="card">
                <span class="mayhen_event_studio_rail_icon" aria-hidden="true">🎨</span>
                <span class="mayhen_event_studio_rail_label">1. Mặt Thiệp</span>
            </button>
            <button type="button" class="mayhen_event_studio_rail_btn" data-event-tab-btn="envelope">
                <span class="mayhen_event_studio_rail_icon" aria-hidden="true">✉️</span>
                <span class="mayhen_event_studio_rail_label">2. Phong Bì</span>
            </button>
            <button type="button" class="mayhen_event_studio_rail_btn" data-event-tab-btn="content">
                <span class="mayhen_event_studio_rail_icon" aria-hidden="true">✍️</span>
                <span class="mayhen_event_studio_rail_label">3. Lời Văn</span>
            </button>
            <button type="button" class="mayhen_event_studio_rail_btn" data-event-tab-btn="venue">
                <span class="mayhen_event_studio_rail_icon" aria-hidden="true">📍</span>
                <span class="mayhen_event_studio_rail_label">4. Bản Đồ</span>
            </button>
            <button type="button" class="mayhen_event_studio_rail_btn" data-event-tab-btn="rsvp">
                <span class="mayhen_event_studio_rail_icon" aria-hidden="true">🧧</span>
                <span class="mayhen_event_studio_rail_label">5. RSVP &amp; QR</span>
            </button>
            <button type="button" class="mayhen_event_studio_rail_btn" data-event-tab-btn="guests">
                <span class="mayhen_event_studio_rail_icon" aria-hidden="true">👥</span>
                <span class="mayhen_event_studio_rail_label">6. Khách Mời</span>
            </button>
        </nav>

        {{-- CỘT 2: BẢNG INSPECTOR CHỈNH SỬA CHI TIẾT (CÓ HEADER + VÙNG CUỘN ĐỘC LẬP + FOOTER CỐ ĐỊNH KHÔNG ĐÈ NỘI DUNG) --}}
        <aside class="mayhen_event_studio_inspector" aria-label="Bảng tùy chỉnh chi tiết">
            <div id="mayhenEventStudioToast" class="mayhen_event_studio_toast" style="display: none;"></div>

            <form id="mayhenEventStudioForm" class="mayhen_event_studio_form_shell" onsubmit="return false;">
                <input type="hidden" id="evtDraftIdInput" name="draft_id" value="{{ $state['id'] ?? '' }}">
                <input type="hidden" name="template_slug" value="{{ $template['slug'] }}">
                <input type="hidden" id="evtColorPaletteInput" name="color_palette" value="{{ $state['color_palette'] }}">
                <input type="hidden" id="evtEnvelopeColorInput" name="envelope_color" value="{{ $state['envelope_color'] }}">
                <input type="hidden" id="evtEnvelopeLinerInput" name="envelope_liner" value="{{ $state['envelope_liner'] }}">
                <input type="hidden" id="evtCardBorderInput" name="card_border" value="{{ $state['card_border'] }}">
                <input type="hidden" id="evtArtworkMotifInput" name="artwork_motif" value="{{ $state['artwork_motif'] }}">
                <input type="hidden" id="evtBackdropInput" name="backdrop" value="{{ $state['backdrop'] }}">
                <input type="hidden" id="evtFontStyleInput" name="font_style" value="{{ $state['font_style'] }}">
                <input type="hidden" id="evtCustomPhotoDataInput" name="custom_photo_data" value="{{ $state['custom_photo_data'] }}">

                {{-- VÙNG CUỘN ĐỘC LẬP CỦA INSPECTOR --}}
                <div class="mayhen_event_studio_inspector_scroll">

                    {{-- ================= TAB 1: MẶT THIỆP, KHUÔN CẮT, KHUNG TRANH MINH HỌA, MÀU & ẢNH ================= --}}
                    <div class="mayhen_event_studio_panel is-active" data-event-tab-panel="card">
                        <div class="mayhen_event_studio_panel_intro">
                            <h2>Khuôn Thiệp &amp; Tranh Minh Họa</h2>
                            <p>Tùy chỉnh hình dáng khuôn cắt, đổi chủ đề tranh vẽ bao quanh 4 mép thiệp và tải ảnh kỷ niệm.</p>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <h3 class="mayhen_event_studio_block_title">1. Khuôn Cắt &amp; Viền Mặt Thiệp (8 Kiểu)</h3>
                            <div class="mayhen_event_swatch_grid">
                                @foreach($cardBorders as $bKey => $bVal)
                                    <button type="button" class="mayhen_event_swatch_btn {{ $state['card_border'] === $bKey ? 'is-active' : '' }}" data-event-border-btn="{{ $bKey }}">
                                        <span>{{ $bVal['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <h3 class="mayhen_event_studio_block_title">2. Khung Tranh Minh Họa Theo Sự Kiện (15 Chủ Đề)</h3>
                            <div class="mayhen_event_swatch_grid">
                                @foreach($artworkMotifs as $mKey => $mVal)
                                    <button type="button" class="mayhen_event_swatch_btn {{ $state['artwork_motif'] === $mKey ? 'is-active' : '' }}" data-event-motif-btn="{{ $mKey }}">
                                        <span>{{ $mVal['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <h3 class="mayhen_event_studio_block_title">3. Tông Màu Chữ &amp; Điểm Nhấn</h3>
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
                            <h3 class="mayhen_event_studio_block_title">4. Kiểu Phông Chữ Tiêu Đề</h3>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                <button type="button" class="mayhen_event_swatch_btn {{ $state['font_style'] === 'serif' ? 'is-active' : '' }}" data-event-font-btn="serif">Cổ Điển Serif</button>
                                <button type="button" class="mayhen_event_swatch_btn {{ $state['font_style'] === 'sans' ? 'is-active' : '' }}" data-event-font-btn="sans">Hiện Đại Sans</button>
                                <button type="button" class="mayhen_event_swatch_btn {{ $state['font_style'] === 'script' ? 'is-active' : '' }}" data-event-font-btn="script">Nghiêng Nghệ Thuật</button>
                            </div>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <h3 class="mayhen_event_studio_block_title">5. Ảnh Chân Dung / Kỷ Niệm Lồng Trên Mặt Thiệp</h3>
                            <p style="font-size: 12px; color: #6A635B; margin: 0 0 8px;">
                                Tải ảnh của bé, gia đình hoặc ngôi nhà mới để hiển thị trong khung tròn viền vàng trên mặt thiệp:
                            </p>
                            <input type="file" id="evtCustomPhotoFile" accept="image/*" class="mayhen_event_input" style="font-size: 12px;">
                            <button type="button" id="btnClearEventCustomPhoto" class="mayhen_event_btn_outline" style="margin-top: 8px; padding: 6px 12px; font-size: 11.5px; {{ empty($state['custom_photo_data']) ? 'display: none;' : '' }}">
                                &times; Gỡ ảnh đã tải lên
                            </button>
                        </div>
                    </div>

                    {{-- ================= TAB 2: PHONG BÌ 3D, LỚP LÓT HOA VĂN & MẶT BÀN PHÔNG NỀN ================= --}}
                    <div class="mayhen_event_studio_panel" data-event-tab-panel="envelope">
                        <div class="mayhen_event_studio_panel_intro">
                            <h2>Phong Bì 3D &amp; Phông Nền</h2>
                            <p>Phối màu vỏ phong bì, lớp giấy lót hoa văn bên trong nắp phong bì và mặt bàn trưng bày.</p>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <h3 class="mayhen_event_studio_block_title" style="margin: 0;">1. Màu Vỏ Phong Bì 3D (8 Màu)</h3>
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
                            <h3 class="mayhen_event_studio_block_title">2. Hoa Văn Lớp Lót Trong Phong Bì (Envelope Liner)</h3>
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
                            <h3 class="mayhen_event_studio_block_title">3. Mặt Bàn &amp; Phông Nền Sân Khấu (Backdrop)</h3>
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
                        <div class="mayhen_event_studio_panel_intro">
                            <h2>Lời Mời Trên Mặt Thiệp 1 Trang</h2>
                            <p>Bạn có thể chạm trực tiếp vào bất kỳ dòng chữ nào trên tấm thiệp bên phải để nhảy tới đúng ô sửa.</p>
                        </div>

                        @if(!empty($presets))
                            <div class="mayhen_event_studio_block">
                                <h3 class="mayhen_event_studio_block_title">✦ Chọn Nhanh Lời Văn Mẫu Chuẩn Dịp Lễ (1 Chạm)</h3>
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
                            <h3 class="mayhen_event_studio_block_title">Nội Dung Chính Trên Thiệp</h3>
                            <div class="mayhen_event_studio_field">
                                <label>Xem thử tên khách mời trên thiệp</label>
                                <input type="text" class="mayhen_event_input" value="Quý khách & Gia đình" data-event-input="guest_preview" placeholder="Ví dụ: Vợ chồng Anh Minh">
                            </div>
                            <div class="mayhen_event_studio_field">
                                <label>{{ $labels['event_badge'] ?? 'Dòng mở đầu phía trên' }}</label>
                                <input type="text" name="event_badge" class="mayhen_event_input" value="{{ $state['event_badge'] }}" data-event-input="event_badge">
                            </div>
                            <div class="mayhen_event_studio_field">
                                <label>{{ $labels['host_name'] ?? 'Tên Chủ Tiệc / Nhân Vật Chính *' }}</label>
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
                        <div class="mayhen_event_studio_panel_intro">
                            <h2>Thời Gian &amp; Địa Điểm</h2>
                            <p>Hiển thị tinh gọn ngay ở nửa dưới mặt thiệp hoặc tắt đi nếu bạn chỉ gửi Thiệp Chúc Tết / 8-3 / 20-11 từ xa.</p>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #85182A; cursor: pointer;">
                                <input type="checkbox" id="evtShowDatetimeVenue" name="show_datetime_venue" value="1" {{ $state['show_datetime_venue'] ? 'checked' : '' }}>
                                <span>In Ngày Giờ &amp; Địa Điểm lên mặt thiệp</span>
                            </label>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <h3 class="mayhen_event_studio_block_title">Thông Tin Lịch &amp; Địa Chỉ</h3>
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
                        <div class="mayhen_event_studio_panel_intro">
                            <h2>Popup RSVP &amp; Phong Bao VietQR</h2>
                            <p>Giữ cho mặt thiệp luôn gọn 1 trang bằng cách mở biểu mẫu RSVP và hộp Lì xì dạng Popup sang trọng.</p>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <h3 class="mayhen_event_studio_block_title" style="margin: 0;">1. Nút Popup Xác Nhận (RSVP)</h3>
                                <button type="button" class="mayhen_event_btn_outline" data-open-event-popup="rsvp" style="padding: 4px 10px; font-size: 11px;">
                                    👁 Xem Thử Popup
                                </button>
                            </div>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; margin-bottom: 12px; cursor: pointer;">
                                <input type="checkbox" id="evtEnableRsvp" name="enable_rsvp" value="1" {{ $state['enable_rsvp'] ? 'checked' : '' }}>
                                <span>Hiển thị nút mở Popup RSVP &amp; Gửi lời chúc</span>
                            </label>
                            <div class="mayhen_event_studio_field">
                                <label>Câu hỏi thêm dành cho khách mời</label>
                                <input type="text" name="rsvp_question" class="mayhen_event_input" value="{{ $state['rsvp_question'] }}" data-event-input="rsvp_question" placeholder="Ví dụ: Bạn dùng tiệc chay hay mặn?">
                            </div>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <h3 class="mayhen_event_studio_block_title" style="margin: 0;">2. Nút Popup Lì Xì / Mừng Quà QR</h3>
                                <button type="button" class="mayhen_event_btn_outline" data-open-event-popup="gift" style="padding: 4px 10px; font-size: 11px;">
                                    👁 Xem Thử Popup
                                </button>
                            </div>
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

                    {{-- ================= TAB 6: TẠO LINK CÁ NHÂN HÓA HÀNG LOẠT CHO KHÁCH MỜI ================= --}}
                    <div class="mayhen_event_studio_panel" data-event-tab-panel="guests">
                        <div class="mayhen_event_studio_panel_intro">
                            <h2>Cá Nhân Hóa Tên Khách Mời</h2>
                            <p>Nhập danh sách tên khách mời (mỗi dòng 1 người/gia đình) để tạo sẵn link thiệp có in đúng tên từng vị khách và bấm xem thử ngay trên mặt thiệp.</p>
                        </div>

                        <div class="mayhen_event_studio_block">
                            <h3 class="mayhen_event_studio_block_title">Danh Sách Khách Mời (Mỗi dòng 1 tên)</h3>
                            <textarea id="evtBatchGuestNamesInput" rows="5" class="mayhen_event_textarea" placeholder="Vợ chồng Anh Minh & Chị Lan&#10;Gia đình Chú Hùng Cô Thảo&#10;Quý Đối Tác Công Ty ABC&#10;Ông Bà Nội Ngoại">Vợ chồng Anh Minh & Chị Lan
Gia đình Chú Hùng Cô Thảo
Quý Đối Tác & Khách Hàng VIP</textarea>
                            <button type="button" id="btnGenerateBatchGuestLinks" class="mayhen_event_btn_primary" style="width: 100%; margin-top: 10px;">
                                ✦ Tạo Danh Sách Thiệp Cá Nhân Hóa
                            </button>
                        </div>

                        <div class="mayhen_event_studio_block" id="evtBatchGuestListResult">
                            <h3 class="mayhen_event_studio_block_title">Danh Sách Xem Thử &amp; Gửi Zalo</h3>
                            <div id="evtBatchGuestItemsContainer" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER CỐ ĐỊNH Ở ĐÁY CỘT INSPECTOR (TÁCH BIỆT 100% VỚI VÙNG CUỘN, KHÔNG BAO GIỜ ĐÈ LÊN FORM) --}}
                <div class="mayhen_event_studio_inspector_foot">
                    <button type="button" id="btnEventSaveDraft" data-trigger-save-draft class="mayhen_event_btn_outline">
                        Lưu Bản Nháp
                    </button>
                    <button type="button" id="btnEventPublishPro" data-trigger-publish-pro class="mayhen_event_btn_primary">
                        ✦ Xuất Bản Thiệp (PRO)
                    </button>
                </div>
            </form>
        </aside>

        {{-- CỘT 3: SÂN KHẤU TRỰC QUAN 1 TRANG DUY NHẤT (LIVE INTERACTIVE CANVAS STAGE) --}}
        <main class="mayhen_event_studio_canvas_area" id="mayhenEventStudioCanvasArea" aria-label="Sân khấu thiết kế thiệp 1 trang trực tiếp">
            <div class="mayhen_event_studio_stage_Hint">
                <span>💡 Mẹo: Chạm vào bất kỳ dòng chữ nào trên mặt thiệp để sửa nhanh &bull; Bấm <strong>&#9655;</strong> để xem hiệu ứng rút thiệp</span>
            </div>

            <div class="mayhen_event_studio_viewport_frame" id="mayhenEventStudioViewportFrame" data-viewport="desktop">
                <div class="mayhen_event_card_page mayhen_event_single_screen_mode mayhen_event_palette_{{ $state['color_palette'] }} mayhen_event_env_{{ $state['envelope_color'] }} mayhen_event_backdrop_{{ $state['backdrop'] }} mayhen_event_font_{{ $state['font_style'] }}" id="mayhenEventPreviewRoot" @if(!empty($backdropImg)) style="background-image: url('{{ asset($backdropImg) }}'); background-size: cover; background-position: center;" @endif>
                    <div class="mayhen_event_single_stage_viewport">

                        <div class="mayhen_event_3d_stage is-extracted" id="mayhenEvent3dStage">
                            {{-- Nút tròn Replay bên trái tấm thiệp giống hệt 00:06 trong video Paperless Post --}}
                            <button type="button"
                                    class="mayhen_event_replay_circle_btn"
                                    data-event-toggle-envelope
                                    title="Xem lại hiệu ứng mở phong bì & rút thiệp"
                                    aria-label="Xem lại hiệu ứng rút thiệp">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="6 4 20 12 6 20 6 4"></polygon>
                                </svg>
                            </button>

                            {{-- TỜ THIỆP 1 TRANG DUY NHẤT --}}
                            <article class="mayhen_event_stationery_sheet mayhen_event_border_{{ $state['card_border'] }}" id="mayhenEventStationerySheet">
                                <div class="mayhen_event_card_scene_layer" id="mayhenEventCardSceneLayer" aria-hidden="true">
                                    {!! \App\Services\EventInvitationService::renderCardSceneSvg($state['artwork_motif']) !!}
                                </div>

                                <div class="mayhen_event_card_inner_content">
                                    <span class="mayhen_event_guest_greeting" data-studio-click-edit="guest_preview" title="Bấm để sửa tên khách mời">
                                        Kính gửi: <strong data-event-sync="guest_preview">Quý khách &amp; Gia đình</strong>
                                    </span>

                                    <div class="mayhen_event_badge_kicker" data-event-sync="event_badge" data-studio-click-edit="event_badge" title="Bấm để sửa tiêu đề mở đầu">{{ $state['event_badge'] }}</div>

                                    <div class="mayhen_event_custom_photo_wrap" id="evtCustomPhotoPreviewWrap" style="{{ empty($state['custom_photo_data']) ? 'display: none;' : '' }}">
                                        <img src="{{ $state['custom_photo_data'] }}" alt="{{ $state['host_name'] }}" id="evtCustomPhotoPreviewImg">
                                    </div>

                                    <h2 class="mayhen_event_host_heading" data-event-sync="host_name" data-studio-click-edit="host_name" title="Bấm để sửa tên chủ tiệc / nhân vật chính">{{ $state['host_name'] }}</h2>
                                    <p class="mayhen_event_subhost" data-event-sync="sub_host_name" data-studio-click-edit="sub_host_name" title="Bấm để sửa dòng phụ đề">{{ $state['sub_host_name'] }}</p>
                                    <p class="mayhen_event_message_body" data-event-sync="invitation_message" data-studio-click-edit="invitation_message" title="Bấm để sửa lời nhắn">{{ $state['invitation_message'] }}</p>

                                    <div class="mayhen_event_oncard_details" id="evtPreviewDatetimeSection" style="{{ !$state['show_datetime_venue'] ? 'display: none;' : '' }}">
                                        <div class="mayhen_event_oncard_divider" aria-hidden="true">✦</div>
                                        <div class="mayhen_event_oncard_datetime" data-studio-click-edit="event_date" title="Bấm để sửa ngày giờ">
                                            <span data-event-sync="event_date">{{ \Illuminate\Support\Carbon::parse($state['event_date'])->format('d . m . Y') }}</span>
                                            <span>&bull;</span>
                                            <span>LÚC <strong data-event-sync="party_time">{{ $state['party_time'] }}</strong></span>
                                        </div>
                                        <div class="mayhen_event_oncard_lunar" data-event-sync="lunar_note" data-studio-click-edit="lunar_note">{{ $state['lunar_note'] }}</div>
                                        <div class="mayhen_event_oncard_venue" data-event-sync="venue_name" data-studio-click-edit="venue_name" title="Bấm để sửa địa điểm">{{ $state['venue_name'] }}</div>
                                        <div class="mayhen_event_oncard_address" data-event-sync="venue_address" data-studio-click-edit="venue_address">{{ $state['venue_address'] }}</div>
                                    </div>
                                </div>
                            </article>

                            {{-- PHONG BÌ 3D MỞ NẮP LÓT HOA VĂN PHÍA DƯỚI --}}
                            <div class="mayhen_event_3d_envelope_back {{ !empty($envelopeBackImg) ? 'has-real-envelope-img' : '' }}" id="mayhenEventStudioEnvelopeBack" aria-hidden="true">
                                @if(!empty($envelopeBackImg))
                                    <img src="{{ asset($envelopeBackImg) }}" alt="" class="mayhen_event_real_env_img">
                                @endif
                                <div class="mayhen_event_3d_flap_top"></div>
                                <div class="mayhen_event_3d_liner_inner mayhen_event_liner_{{ $state['envelope_liner'] }}" id="mayhenEventPreviewLiner"></div>
                                <div class="mayhen_event_3d_pocket_front"></div>
                            </div>
                        </div>

                        {{-- Thanh nút tiện ích nổi dưới chân thiệp 1 trang (Bấm được ngay trong Studio để test Popup!) --}}
                        <nav class="mayhen_event_floating_dock" aria-label="Nút tiện ích dưới chân thiệp">
                            <a href="{{ $state['map_url'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_event_dock_pill" id="evtPreviewVenueSection" data-event-sync="map_url" style="{{ !$state['show_datetime_venue'] ? 'display: none;' : '' }}">
                                📍 Chỉ Đường Bản Đồ
                            </a>
                            <button type="button" class="mayhen_event_dock_pill mayhen_event_dock_pill_primary" id="evtPreviewRsvpBox" data-open-event-popup="rsvp" style="{{ !$state['enable_rsvp'] ? 'display: none;' : '' }}">
                                ✉ Xác Nhận Tham Dự &amp; Lời Chúc
                            </button>
                            <button type="button" class="mayhen_event_dock_pill" id="evtPreviewGiftQrBox" data-open-event-popup="gift" style="{{ !$state['enable_gift_qr'] ? 'display: none;' : '' }}">
                                🧧 {{ $giftHeading }}
                            </button>
                        </nav>

                    </div>
                </div>
            </div>
        </main>

    </div>
</div>

{{-- =========================================================================
     3. CÁC HỘP THOẠI POPUP TRONG STUDIO (ĐỔI MẪU NHANH, XEM THỬ RSVP, XEM THỬ VIETQR, GÓI PRO)
     ========================================================================= --}}

{{-- POPUP A: BỘ ĐỔI MẪU NHANH 30 MẪU THIỆP SỰ KIỆN NGAY TRONG STUDIO --}}
<div class="mayhen_event_modal_overlay" id="mayhenEventTemplateSwitcherModal" role="dialog" aria-modal="true" aria-label="Đổi mẫu thiệp sự kiện nhanh">
    <div class="mayhen_event_modal_dialog" style="max-width: 780px; text-align: left; max-height: 86vh; overflow-y: auto;">
        <button type="button" class="mayhen_event_modal_close" data-close-event-popup>&times;</button>
        <span class="mayhen_event_badge_kicker">30+ Mẫu Thiệp Sự Kiện &amp; Lễ Hội Việt Nam</span>
        <h2 class="mayhen_event_venue_title" style="margin-bottom: 14px;">Chuyển Nhanh Sang Mẫu Thiệp Khác</h2>
        <div class="mayhen_event_studio_switcher_grid">
            @foreach($allTemplates as $tplItem)
                <a href="{{ route('event_invitations.studio', $tplItem['slug']) }}"
                   class="mayhen_event_studio_switcher_item {{ $tplItem['slug'] === $template['slug'] ? 'is-current' : '' }}">
                    <span class="mayhen_event_studio_switcher_icon">{{ $tplItem['seal_icon'] ?? '✦' }}</span>
                    <div>
                        <strong>{{ $tplItem['name'] }}</strong>
                        <small>{{ \Illuminate\Support\Str::limit($tplItem['tagline'], 65) }}</small>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- POPUP B: XEM THỬ BIỂU MẪU RSVP & LỜI CHÚC NGAY TRONG STUDIO --}}
<div class="mayhen_event_modal_overlay" id="mayhenEventRsvpPopup" role="dialog" aria-modal="true" aria-label="Xem thử Popup RSVP">
    <div class="mayhen_event_modal_dialog" style="max-width: 500px; text-align: left;">
        <button type="button" class="mayhen_event_modal_close" data-close-event-popup>&times;</button>
        <span class="mayhen_event_badge_kicker">Xem Thử Trải Nghiệm Khách Mời</span>
        <h2 class="mayhen_event_venue_title" style="margin-bottom: 12px;">Xác Nhận Tham Dự &amp; Gửi Lời Chúc</h2>
        <div class="mayhen_event_rsvp_form">
            <input type="text" class="mayhen_event_input" placeholder="Tên của bạn / Gia đình *">
            <div class="mayhen_event_rsvp_grid_2">
                <select class="mayhen_event_select">
                    <option>Sẽ tham dự chung vui</option>
                    <option>Đang sắp xếp thời gian</option>
                    <option>Gửi lời chúc từ xa</option>
                </select>
                <select class="mayhen_event_select">
                    <option>Đi 1 người</option>
                    <option>Đi 2 người</option>
                    <option>Đi cả gia đình</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #5C5349; margin-bottom: 4px;" data-event-sync="rsvp_question">{{ $state['rsvp_question'] }}</label>
                <input type="text" class="mayhen_event_input" placeholder="Câu trả lời của khách mời...">
            </div>
            <textarea rows="3" class="mayhen_event_textarea" placeholder="Nhập lời chúc tốt đẹp gửi tới chủ tiệc..."></textarea>
            <button type="button" class="mayhen_event_btn_primary" data-close-event-popup style="width: 100%; padding: 12px;">
                ✦ Đóng Xem Thử Popup RSVP
            </button>
        </div>
    </div>
</div>

{{-- POPUP C: XEM THỬ PHONG BAO LÌ XÌ / MỪNG QUÀ VIETQR NGAY TRONG STUDIO --}}
<div class="mayhen_event_modal_overlay" id="mayhenEventGiftPopup" role="dialog" aria-modal="true" aria-label="Xem thử Popup Phong bao lì xì">
    <div class="mayhen_event_modal_dialog">
        <button type="button" class="mayhen_event_modal_close" data-close-event-popup>&times;</button>
        <span class="mayhen_event_badge_kicker">Xem Thử Phong Bao May Mắn</span>
        <h2 class="mayhen_event_venue_title">{{ $giftHeading }}</h2>
        <p style="font-size: 13.5px; color: #6A635B; margin: 6px 0 14px;" data-event-sync="gift_note">{{ $state['gift_note'] }}</p>
        <div class="mayhen_event_qr_box">
            <div style="font-size: 13px; color: #5C5349; margin-bottom: 4px;">
                Ngân hàng: <strong data-event-sync="bank_name">{{ $state['bank_name'] }}</strong>
            </div>
            <div style="font-size: 18px; font-weight: 700; color: var(--mh-evt-primary); letter-spacing: 0.05em; margin-bottom: 4px;" data-event-sync="bank_account_number">
                {{ $state['bank_account_number'] }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: #1F1D1A;" data-event-sync="bank_account_holder">
                {{ $state['bank_account_holder'] }}
            </div>
        </div>
    </div>
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

{{-- Kho SVG Scene ẩn để JS đổi chủ đề tranh minh họa tức thì trên sân khấu mà không cần tải lại trang --}}
<div id="mayhenEventSceneTemplatesBank" style="display: none;" aria-hidden="true">
    @foreach($artworkMotifs as $mKey => $mVal)
        <template data-scene-template="{{ $mKey }}">
            {!! \App\Services\EventInvitationService::renderCardSceneSvg($mKey) !!}
        </template>
    @endforeach
</div>
@endsection

@push('scripts')
    <script src="{{ asset_v('assets/client/js/pages/event-invitations.js') }}"></script>
@endpush
