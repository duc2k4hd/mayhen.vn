@extends('client.layouts.master')

@section('title', 'Cổng Tiếp Tân Điểm Danh: ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkin.css') }}">
@endpush

@section('content')
<div class="mayhen_checkin_app" id="mayhen_checkin_app" data-slug="{{ $invitation->slug }}" data-scan-url="{{ route('checkin.scan', $invitation->slug) }}" data-search-url="{{ route('checkin.quickSearch', $invitation->slug) }}" data-undo-url="{{ url('api/diem-danh-tiec/' . $invitation->slug . '/huy') }}" data-update-table-url="{{ url('api/diem-danh-tiec/' . $invitation->slug . '/ban-tiec') }}">
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
                @if(auth()->check() && (int) auth()->id() === (int) $wedding->user_id)
                    <a href="{{ route('coordination.index', $invitation->id) }}" class="mayhen_checkin_btn mayhen_checkin_btn--outline">
                        📊 Bảng Điều Khiển Realtime
                    </a>
                @endif
                <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_checkin_btn mayhen_checkin_btn--text">
                    ← Danh sách khách
                </a>
            </div>
        </header>

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
                    <div class="mayhen_checkin_recent_item" id="recent_guest_{{ $item->id }}">
                        <div class="mayhen_checkin_recent_main">
                            <span class="mayhen_checkin_recent_name">
                                {{ $item->salutation ? $item->salutation . ' ' : '' }}{{ $item->name }}
                            </span>
                            <span class="mayhen_checkin_recent_meta">
                                Bàn: <strong>{{ $item->table_name ?: 'Chưa xếp' }}</strong> • {{ $item->checked_in_attendees ?: $item->expected_attendees }} người
                            </span>
                        </div>
                        <div class="mayhen_checkin_recent_action">
                            <span class="mayhen_checkin_recent_time">{{ $item->checked_in_at ? $item->checked_in_at->format('H:i') : '' }}</span>
                            <button type="button" class="mayhen_checkin_btn_undo" data-guest-id="{{ $item->id }}" title="Hủy điểm danh nếu nhầm">
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
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/checkin.js') }}"></script>
@endpush

