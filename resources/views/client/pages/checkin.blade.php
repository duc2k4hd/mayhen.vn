@extends('client.layouts.master')

@section('title', 'Cổng Tiếp Tân Điểm Danh: ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkin.css') }}">
@endpush

@section('content')
<div class="mayhen_checkin_app" id="mayhen_checkin_app" 
    data-slug="{{ $invitation->slug }}" 
    data-scan-url="{{ route('checkin.scan', $invitation->slug) }}" 
    data-search-url="{{ route('checkin.quickSearch', $invitation->slug) }}" 
    data-undo-url="{{ url('api/diem-danh-tiec/' . $invitation->slug . '/huy') }}" 
    data-update-table-url="{{ url('api/diem-danh-tiec/' . $invitation->slug . '/ban-tiec') }}" 
    data-walkin-url="{{ route('checkin.exception.walk_in', $invitation->slug) }}" 
    data-adjust-url="{{ url('api/diem-danh-tiec/' . $invitation->slug . '/ngoai-le/dieu-chinh-suat') }}" 
    data-convert-url="{{ url('api/diem-danh-tiec/' . $invitation->slug . '/ngoai-le/chuyen-tham-du') }}"
    data-handover-summary-url="{{ route('checkin.shift.summary', $invitation->slug) }}"
    data-handover-submit-url="{{ route('checkin.shift.handover', $invitation->slug) }}"
>
    <div class="mayhen_checkin_container">
        {{-- Top Header --}}
        <header class="mayhen_checkin_header">
            <div class="mayhen_checkin_header_info">
                <span class="mayhen_checkin_pill">
                    <span class="mayhen_checkin_live_dot"></span> TRỰC TIẾP ĐÓN KHÁCH
                </span>
                <h1 class="mayhen_checkin_title">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
                <p class="mayhen_checkin_subtitle">Cổng Quét Mã QR & Điều Phối Vị Trí Bàn Tiệc</p>
            </div>

            <div class="mayhen_checkin_header_actions">
                @if(!empty($currentShift))
                    <span style="background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        🛡️ {{ $currentShift['name'] }} ({{ $currentShift['staff_name'] }})
                    </span>
                @endif
                <button type="button" id="btn_open_handover_modal" class="mayhen_checkin_btn" style="background: #FFFFFF; border: 1px solid #C5A880; color: #8C6D3F; font-weight: 700;">
                    📋 Bàn Giao Ca Trực
                </button>
                <button type="button" id="btn_open_exception_modal" class="mayhen_checkin_btn" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFF; font-weight: 700; border: none; box-shadow: 0 4px 14px rgba(156, 122, 74, 0.35);">
                    ⚡ Bàn Xử Lý Ngoại Lệ
                </button>
                @if(auth()->check() && (int) auth()->id() === (int) $wedding->user_id)
                    <a href="{{ route('coordination.index', $invitation->id) }}" class="mayhen_checkin_btn mayhen_checkin_btn--outline">
                        📊 Bảng Điều Khiển Realtime
                    </a>
                @endif
                <a href="{{ route('docs.show', ['category' => 'checkin-livewall', 'slug' => 'CLI-CKIN-001']) }}" target="_blank" rel="noopener noreferrer" class="mayhen_checkin_btn mayhen_checkin_btn--outline" title="Xem hướng dẫn sử dụng cổng điểm danh lễ tân và quét mã QR">
                    📖 Hướng Dẫn Điểm Danh
                </a>
                <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_checkin_btn mayhen_checkin_btn--text">
                    ← Danh sách khách
                </a>
            </div>
        </header>

        {{-- Banner Chế Độ Diễn Tập Lễ Tân (TASK-VAL-20) --}}
        @if(!empty($isDryRun))
            <div style="background: #F3E5F5; border: 2px dashed #AB47BC; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 26px;">🧪</span>
                    <div>
                        <strong style="font-size: 15px; color: #6A1B9A; display: block;">CHẾ ĐỘ TỔNG DIỄN TẬP LỄ TÂN (DRY RUN SIMULATOR)</strong>
                        <span style="font-size: 12.5px; color: #4A148C;">Dữ liệu thử nghiệm hoàn toàn cách ly trong phiên làm việc. Mọi lượt quét QR thử nghiệm sẽ KHÔNG làm thay đổi số liệu đón tiếp thật.</span>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <form action="{{ route('checkin.dry_run.reset', $invitation->slug) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_checkin_btn" style="background: #FFFFFF; color: #6A1B9A; border: 1px solid #AB47BC; padding: 6px 14px; font-size: 12px; border-radius: 20px;">
                            🔄 Làm Mới Thử Nghiệm
                        </button>
                    </form>
                    <form action="{{ route('checkin.dry_run.disable', $invitation->slug) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_checkin_btn" style="background: #6A1B9A; color: #FFFFFF; border: none; padding: 6px 14px; font-size: 12px; border-radius: 20px; font-weight: 700;">
                            ✕ Thoát Diễn Tập
                        </button>
                    </form>
                </div>
            </div>

            {{-- 5 Kịch Bản Thử Nghiệm Nhanh 1 Chạm Cho Lễ Tân --}}
            <div style="background: #FFFFFF; border: 1px solid #E5DCCD; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <strong style="font-size: 13.5px; color: #1F1D1A; text-transform: uppercase; letter-spacing: 0.5px;">🎯 5 Kịch Bản Thử Nghiệm Nhanh (Bấm để quét thử lập tức):</strong>
                    <span style="font-size: 11px; color: #7A726A;">Kiểm tra camera &amp; máy quét QR</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">
                    @foreach($dryRunGuests ?? [] as $dg)
                        <div style="background: #FAF8F5; border: 1px solid #E5DCCD; border-radius: 10px; padding: 10px 12px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <strong style="font-size: 13px; color: #1F1D1A;">{{ $dg['name'] }}</strong>
                                    @if(!empty($dg['checked_in_at']))
                                        <span style="background: #FFF3E0; color: #E65100; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px;">Đã Đón</span>
                                    @else
                                        <span style="background: #E8F5E9; color: #2E7D32; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px;">Chưa Đón</span>
                                    @endif
                                </div>
                                <div style="font-size: 11px; color: #7A726A;">
                                    {{ $dg['table_name'] ?: '⚠️ Chưa xếp bàn' }} • {{ $dg['expected_attendees'] }} suất
                                    @if(!empty($dg['dietary']))
                                        <span style="color: #E65100; display: block;">🥗 {{ $dg['dietary'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <button type="button" class="btn-dry-run-scan" data-token="{{ $dg['guest_token'] }}" style="margin-top: 8px; width: 100%; background: #C5A880; color: #fff; border: none; border-radius: 6px; padding: 5px 8px; font-size: 11px; font-weight: 700; cursor: pointer;">
                                📷 Quét Thử Mã Này
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Nút Kích Hoạt Chế Độ Diễn Tập Cho Lễ Tân --}}
            <div style="display: flex; justify-content: flex-end; margin-bottom: 14px;">
                <form action="{{ route('checkin.dry_run.enable', $invitation->slug) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="mayhen_checkin_btn" style="background: #FAF8F5; border: 1px dashed #AB47BC; color: #6A1B9A; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 20px;">
                        🧪 Bật Chế Độ Diễn Tập Lễ Tân (Dry Run)
                    </button>
                </form>
            </div>
        @endif

        {{-- Live Headcount Counter Cards --}}
        <div class="mayhen_checkin_counter_grid">
            <div class="mayhen_checkin_counter_card mayhen_checkin_counter_card--gold">
                <span class="mayhen_checkin_counter_label">ĐÃ ĐÓN VÀO BÀN</span>
                <div class="mayhen_checkin_counter_value" id="checked_in_counter">
                    {{ $checkedInCount }} <span class="mayhen_checkin_counter_total">/ {{ $totalExpected }} khách</span>
                </div>
                <div class="mayhen_checkin_progress_bar">
                    @php
                        $percentage = $totalExpected > 0 ? min(100, round(($checkedInCount / $totalExpected) * 100)) : 0;
                    @endphp
                    <div class="mayhen_checkin_progress_fill" id="checked_in_progress" style="width: {{ $percentage }}%;"></div>
                </div>
            </div>

            <div class="mayhen_checkin_counter_card">
                <span class="mayhen_checkin_counter_label">SỐ LƯỢT KHÁCH MỜI</span>
                <div class="mayhen_checkin_counter_value">
                    <span id="checked_in_guests_counter">{{ $checkedInGuestsCount }}</span> <span class="mayhen_checkin_counter_total">/ {{ $totalGuestsCount }} lượt</span>
                </div>
                <span class="mayhen_checkin_counter_hint">Tính theo từng thiệp mời</span>
            </div>
        </div>

        {{-- Navigation Tabs: Camera Scan vs Manual Quick Search --}}
        <div class="mayhen_checkin_tabs">
            <button type="button" class="mayhen_checkin_tab is-active" data-tab="scanner">
                📷 Quét Mã QR Khách Mời
            </button>
            <button type="button" class="mayhen_checkin_tab" data-tab="search">
                🔍 Tìm Tên / SĐT Điểm Danh
            </button>
        </div>

        {{-- Tab Content: Scanner --}}
        <section class="mayhen_checkin_panel is-active" id="panel_scanner">
            <div class="mayhen_checkin_scanner_box">
                <div class="mayhen_checkin_video_wrapper" id="scanner_wrapper">
                    <video id="checkin_video" playsinline></video>
                    <div class="mayhen_checkin_scanner_overlay">
                        <div class="mayhen_checkin_scanner_reticle">
                            <span class="reticle_corner reticle_tl"></span>
                            <span class="reticle_corner reticle_tr"></span>
                            <span class="reticle_corner reticle_bl"></span>
                            <span class="reticle_corner reticle_br"></span>
                            <div class="reticle_laser"></div>
                        </div>
                        <p class="reticle_hint">Hướng camera vào mã QR trên thiệp cưới của khách</p>
                    </div>
                </div>

                <div class="mayhen_checkin_scanner_controls">
                    <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--secondary" id="btn_toggle_camera">
                        🔄 Bật / Đổi Camera
                    </button>
                    <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--text" id="btn_manual_token_toggle">
                        Nhập mã thủ công ⌨️
                    </button>
                </div>

                {{-- Fallback input if camera unavailable or manual --}}
                <div class="mayhen_checkin_manual_input_wrap" id="manual_token_form" style="display: none;">
                    <form id="form_token_manual" class="mayhen_checkin_form_inline">
                        <input type="text" id="input_guest_token" placeholder="Dán mã QR token hoặc URL thiệp..." class="mayhen_checkin_input">
                        <button type="submit" class="mayhen_checkin_btn mayhen_checkin_btn--primary">Điểm danh</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- Tab Content: Search & Fast Check-in --}}
        <section class="mayhen_checkin_panel" id="panel_search">
            <div class="mayhen_checkin_search_box">
                <div class="mayhen_checkin_search_input_wrap">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="input_quick_search" placeholder="Gõ tên khách, số điện thoại hoặc bàn tiệc..." class="mayhen_checkin_search_input" autocomplete="off">
                </div>

                <div class="mayhen_checkin_search_results" id="search_results_container">
                    <div class="mayhen_checkin_empty_state">
                        Nhập tên khách mời để tra cứu nhanh vị trí bàn tiệc và điểm danh.
                    </div>
                </div>
            </div>
        </section>

        {{-- Recent Check-in Activity Feed --}}
        <section class="mayhen_checkin_recent_section">
            <h2 class="mayhen_checkin_section_title">Khách Mới Điểm Danh Gần Đây</h2>
            <div class="mayhen_checkin_recent_list" id="recent_checkin_list">
                @forelse($recentCheckIns as $item)
                    @php
                        $itemId = data_get($item, 'id');
                        $itemName = data_get($item, 'name');
                        $itemSalutation = data_get($item, 'salutation');
                        $itemTable = data_get($item, 'table_name');
                        $itemAttendees = data_get($item, 'checked_in_attendees') ?: data_get($item, 'expected_attendees');
                        $checkInRaw = data_get($item, 'checked_in_at');
                        $itemTime = is_string($checkInRaw) ? $checkInRaw : ($checkInRaw instanceof \Carbon\Carbon ? $checkInRaw->format('H:i') : '');
                    @endphp
                    <div class="mayhen_checkin_recent_item" id="recent_guest_{{ $itemId }}">
                        <div class="mayhen_checkin_recent_main">
                            <span class="mayhen_checkin_recent_name">
                                {{ $itemSalutation ? $itemSalutation . ' ' : '' }}{{ $itemName }}
                            </span>
                            <span class="mayhen_checkin_recent_meta">
                                Bàn: <strong>{{ $itemTable ?: 'Chưa xếp' }}</strong> • {{ $itemAttendees }} người
                            </span>
                        </div>
                        <div class="mayhen_checkin_recent_action">
                            <span class="mayhen_checkin_recent_time">{{ $itemTime }}</span>
                            <button type="button" class="mayhen_checkin_btn_undo" data-guest-id="{{ $itemId }}" title="Hủy điểm danh nếu nhầm">
                                Hủy ✕
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="mayhen_checkin_empty_recent" id="empty_recent_notice">
                        Chưa có khách nào điểm danh. Hãy bắt đầu quét mã QR khi đón khách!
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- SUCCESS CELEBRATION MODAL (POPUP TO LỄ TÂN) --}}
    <div class="mayhen_checkin_modal_backdrop" id="checkin_modal" style="display: none;">
        <div class="mayhen_checkin_modal_card">
            <div class="mayhen_checkin_modal_header">
                <span class="mayhen_checkin_modal_tag" id="modal_status_tag">✓ ĐÃ ĐÓN KHÁCH</span>
                <button type="button" class="mayhen_checkin_modal_close" id="btn_modal_close">&times;</button>
            </div>

            <div class="mayhen_checkin_modal_body">
                <span class="mayhen_checkin_modal_salutation" id="modal_guest_salutation">Trân trọng kính đón</span>
                <h2 class="mayhen_checkin_modal_name" id="modal_guest_name">Nguyễn Văn A</h2>
                <div class="mayhen_checkin_modal_group" id="modal_guest_group">Nhóm: Bạn Bè</div>

                {{-- Prominent Table Announcement --}}
                <div class="mayhen_checkin_table_badge">
                    <span class="table_badge_label">VỊ TRÍ BÀN TIỆC</span>
                    <h3 class="table_badge_val" id="modal_table_name">BÀN 08</h3>
                    <div class="table_badge_edit_link">
                        <button type="button" id="btn_edit_table" class="btn_link_text">Đổi bàn khác ✎</button>
                    </div>
                </div>

                {{-- Quick Table Edit Drawer inside Modal --}}
                <div class="mayhen_checkin_quick_table_box" id="quick_table_box" style="display: none;">
                    <div class="quick_table_input_row">
                        <input type="text" id="input_modal_new_table" placeholder="Nhập tên bàn mới..." class="mayhen_checkin_input">
                        <button type="button" id="btn_save_modal_table" class="mayhen_checkin_btn mayhen_checkin_btn--primary">Lưu bàn</button>
                    </div>
                </div>

                <div class="mayhen_checkin_modal_details">
                    <div class="detail_item">
                        <span class="detail_label">Số người:</span>
                        <strong class="detail_val" id="modal_guest_count">2 khách</strong>
                    </div>
                    <div class="detail_item" id="modal_dietary_wrap" style="display: none;">
                        <span class="detail_label">Ăn uống:</span>
                        <strong class="detail_val" id="modal_dietary">Ăn chay</strong>
                    </div>
                </div>

                <div class="mayhen_checkin_warning_box" id="modal_already_checked_notice" style="display: none;">
                    ⚠️ Khách này đã điểm danh lúc <span id="modal_prev_time"></span>!
                </div>
            </div>

            <div class="mayhen_checkin_modal_footer">
                <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--primary mayhen_checkin_btn--full" id="btn_modal_next">
                    Tiếp tục đón khách (Xong)
                </button>
            </div>
        </div>
    </div>

    {{-- EXCEPTION DESK MODAL (BÀN XỬ LÝ NGOẠI LỆ NGÀY CƯỚI - TASK-VAL-21) --}}
    <div class="mayhen_checkin_modal_backdrop" id="modal_exception_desk" style="display: none;">
        <div class="mayhen_checkin_modal_card" style="max-width: 520px; width: 92%;">
            <div class="mayhen_checkin_modal_header">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #9C7A4A; font-weight: 700;">RSVP EXCEPTION DESK</span>
                    <h3 style="margin: 2px 0 0 0; font-size: 18px; color: #1F1D1A;">Bàn Xử Lý Ngoại Lệ Đón Khách</h3>
                </div>
                <button type="button" class="mayhen_checkin_modal_close" id="btn_close_exception_modal">&times;</button>
            </div>

            <div class="mayhen_checkin_modal_body" style="padding: 16px 20px;">
                <p style="font-size: 13px; color: #7A726A; margin-bottom: 16px;">
                    Dành cho khách phát sinh chưa có trong danh sách (Walk-in), khách đi kèm vượt suất hoặc khách báo bận nay đến dự tiệc.
                </p>

                <form id="form_walkin_guest" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Họ và Tên Khách Phát Sinh: *</label>
                        <input type="text" id="walkin_name" required placeholder="Ví dụ: Anh Nguyễn Văn Bình" class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Danh xưng:</label>
                            <input type="text" id="walkin_salutation" placeholder="Anh / Chị / Bạn / Bác..." class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Số suất (Người đi cùng): *</label>
                            <input type="number" id="walkin_attendees" min="1" max="10" value="1" required class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Xếp vào Bàn Tiệc:</label>
                            <input type="text" id="walkin_table" placeholder="Nhập tên bàn (ví dụ Bàn 12)..." list="exception_tables_list" class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                            <datalist id="exception_tables_list">
                                @foreach($tables as $tbl)
                                    <option value="{{ $tbl }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Phân nhóm khách:</label>
                            <input type="text" id="walkin_group" placeholder="Bạn Bè / Đối Tác..." class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                        </div>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Ghi chú đặc biệt (nếu có):</label>
                        <input type="text" id="walkin_note" placeholder="Ví dụ: Bạn học cấp 3 của chú rể, ăn chay..." class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                    </div>

                    <div id="walkin_msg_box" style="display: none; padding: 8px 12px; border-radius: 6px; font-size: 12.5px;"></div>

                    <button type="submit" class="mayhen_checkin_btn mayhen_checkin_btn--primary mayhen_checkin_btn--full" id="btn_submit_walkin" style="margin-top: 6px;">
                        ⚡ Đón Tiếp &amp; Điểm Danh Vào Bàn Ngay
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- TASK-VAL-23: Modal Bàn Giao Ca Trực Lễ Tân (Staff Shift Handover) --}}
    <div class="mayhen_checkin_modal" id="modal_shift_handover" style="display: none;">
        <div class="mayhen_checkin_modal_backdrop" id="modal_handover_backdrop"></div>
        <div class="mayhen_checkin_modal_content" style="max-width: 520px;">
            <div class="mayhen_checkin_modal_header">
                <div>
                    <h3 class="mayhen_checkin_modal_title">📋 Bàn Giao Ca Trực Lễ Tân</h3>
                    <span style="font-size: 11.5px; color: #8C8479;">Tổng kết sĩ số đón khách & bàn giao cho nhân sự ca tiếp theo</span>
                </div>
                <button type="button" class="mayhen_checkin_modal_close" id="btn_close_handover_modal">✕</button>
            </div>

            <div class="mayhen_checkin_modal_body" style="padding: 16px 20px;">
                <div id="handover_summary_loading" style="text-align: center; padding: 20px; color: #8C8479;">
                    Đang tính toán số liệu ca trực...
                </div>

                <div id="handover_summary_content" style="display: none;">
                    {{-- 4 chỉ số ca trực --}}
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px;">
                        <div style="background: #FAF8F5; border: 1px solid #EFEAE3; border-radius: 8px; padding: 10px 12px;">
                            <span style="font-size: 11px; text-transform: uppercase; color: #8C8479; font-weight: 700; display: block;">Lượt Khách Đã Đón</span>
                            <strong style="font-size: 18px; color: #1F1D1A;" id="ho_stat_checked_in">0</strong>
                            <span style="font-size: 11px; color: #6E685F; display: block;">lượt thiệp mời</span>
                        </div>

                        <div style="background: #E8F5E9; border: 1px solid #C8E6C9; border-radius: 8px; padding: 10px 12px;">
                            <span style="font-size: 11px; text-transform: uppercase; color: #2E7D32; font-weight: 700; display: block;">Tổng Khách Vào Bàn</span>
                            <strong style="font-size: 18px; color: #1B5E20;" id="ho_stat_attendees">0</strong>
                            <span style="font-size: 11px; color: #2E7D32; display: block;">người thực tế</span>
                        </div>

                        <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 8px; padding: 10px 12px;">
                            <span style="font-size: 11px; text-transform: uppercase; color: #F57F17; font-weight: 700; display: block;">Khách Phát Sinh (Walk-in)</span>
                            <strong style="font-size: 18px; color: #E65100;" id="ho_stat_walkin">0</strong>
                            <span style="font-size: 11px; color: #F57F17; display: block;">ngoài danh sách</span>
                        </div>

                        <div style="background: #EDE7F6; border: 1px solid #D1C4E9; border-radius: 8px; padding: 10px 12px;">
                            <span style="font-size: 11px; text-transform: uppercase; color: #512DA8; font-weight: 700; display: block;">Chưa Đến Check-in</span>
                            <strong style="font-size: 18px; color: #311B92;" id="ho_stat_uncheckin">0</strong>
                            <span style="font-size: 11px; color: #512DA8; display: block;">khách còn lại</span>
                        </div>
                    </div>

                    <form id="form_submit_handover" style="display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Người Bàn Giao:</label>
                            <input type="text" id="ho_from_staff" readonly class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box; background: #FAF8F5; color: #6E685F;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Bàn Giao Cho Nhân Sự Tiếp Theo: *</label>
                            <input type="text" id="ho_to_staff" required placeholder="Ví dụ: Bạn Nguyễn Minh Tuấn..." class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #4A453E; display: block; margin-bottom: 4px;">Ghi chú bàn giao quan trọng:</label>
                            <textarea id="ho_notes" rows="2" placeholder="Ví dụ: Bàn 08 còn thiếu 2 ghế, bác Tuấn có nhờ gửi quà mừng riêng..." class="mayhen_checkin_input" style="width: 100%; box-sizing: border-box; resize: vertical;"></textarea>
                        </div>

                        <div id="ho_msg_box" style="display: none; padding: 8px 12px; border-radius: 6px; font-size: 12.5px;"></div>

                        <button type="submit" class="mayhen_checkin_btn mayhen_checkin_btn--primary mayhen_checkin_btn--full" id="btn_submit_handover" style="margin-top: 6px;">
                            ✓ Xác Nhận &amp; Lưu Biên Bản Bàn Giao Ca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/checkin.js') }}"></script>
@endpush

