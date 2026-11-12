@extends('client.pages.account.layout')

@php
    $pageTitle = 'Tra Cứu Bàn Tiệc & Hướng Dẫn Chỗ Ngồi — Mây Hẹn';
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_content')
<div class="mayhen_finder_studio">
    {{-- Thanh điều hướng quay lại --}}
    <div class="mayhen_finder_topbar">
        <a href="{{ route('account.invitations') }}" class="mayhen_finder_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Thiệp Cưới</span>
        </a>

        <div class="mayhen_finder_quick_actions">
            <a href="{{ route('seating.index', $invitation->id) }}" class="mayhen_finder_btn mayhen_finder_btn_outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                <span>Mở Sơ Đồ Bàn Tiệc Kéo Thả</span>
            </a>
            <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="mayhen_finder_btn mayhen_finder_btn_primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Xem Thiệp Trực Tiếp</span>
            </a>
        </div>
    </div>

    {{-- Thông báo session --}}
    @if(session('success'))
        <div class="mayhen_finder_alert mayhen_finder_alert_success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header giới thiệu --}}
    <header class="mayhen_finder_header">
        <div class="mayhen_finder_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="12 8 8 12 12 16 16 12 12 8"></polygon>
            </svg>
            <span>Guest Table Finder & Seating Concierge Studio (IDEA-C04)</span>
        </div>
        <h1 class="mayhen_finder_title">Tra Cứu Bàn Tiệc & Hướng Dẫn Chỗ Ngồi Cho Quan Khách</h1>
        <p class="mayhen_finder_subtitle">
            Tự động hiển thị vị trí bàn tiệc mạ vàng cho khách có link riêng và cung cấp thanh tra cứu nhanh 1-chạm tại sảnh cưới, giúp khách mời an tọa chu đáo, không còn cảnh chen lấn lúng túng.
        </p>
    </header>

    {{-- 4 Card KPI Sắp Bàn Tiệc --}}
    <div class="mayhen_finder_kpis_grid">
        <div class="mayhen_finder_kpi_card">
            <div class="mayhen_finder_kpi_val">{{ $overview['total_tables'] }}</div>
            <div class="mayhen_finder_kpi_lbl">Tổng Số Bàn Tiệc</div>
            <div class="mayhen_finder_kpi_hint">Sức chứa tối đa: {{ $overview['total_capacity'] }} ghế ngồi</div>
        </div>

        <div class="mayhen_finder_kpi_card">
            <div class="mayhen_finder_kpi_val mayhen_finder_kpi_val_seated">{{ $overview['seated_guests_count'] }}</div>
            <div class="mayhen_finder_kpi_lbl">Khách Đã Gán Bàn</div>
            <div class="mayhen_finder_kpi_hint">Đã phân vị trí ngồi rõ ràng</div>
        </div>

        <div class="mayhen_finder_kpi_card">
            <div class="mayhen_finder_kpi_val mayhen_finder_kpi_val_unseated">{{ $overview['unseated_guests_count'] }}</div>
            <div class="mayhen_finder_kpi_lbl">Khách Chưa Gán Bàn</div>
            <div class="mayhen_finder_kpi_hint">Cần xếp bàn trước ngày cưới</div>
        </div>

        <div class="mayhen_finder_kpi_card">
            <div class="mayhen_finder_kpi_val mayhen_finder_kpi_val_rate">{{ $overview['seating_rate'] }}%</div>
            <div class="mayhen_finder_kpi_lbl">Tiến Độ Sắp Bàn</div>
            <div class="mayhen_finder_kpi_hint">{{ $overview['assigned_tables_count'] }}/{{ $overview['total_tables'] }} bàn đã có khách</div>
        </div>
    </div>

    <div class="mayhen_finder_main_layout">
        {{-- Cột Form Cấu Hình --}}
        <div class="mayhen_finder_form_col">
            <form action="{{ route('account.table_finder.update', $invitation->id) }}" method="POST" class="mayhen_finder_card">
                @csrf

                <div class="mayhen_finder_card_header">
                    <h2 class="mayhen_finder_card_title">Cấu Hình Hiển Thị & Trải Nghiệm Khách Mời</h2>
                    <p class="mayhen_finder_card_desc">Tùy chỉnh nội dung hiển thị trong thiệp cưới và thanh tra cứu bàn tiệc</p>
                </div>

                {{-- Bật / Tắt tính năng --}}
                <div class="mayhen_finder_field_group">
                    <label class="mayhen_finder_toggle_label">
                        <input type="checkbox" name="is_enabled" value="1" {{ $config['is_enabled'] ? 'checked' : '' }} class="mayhen_finder_toggle_input">
                        <span class="mayhen_finder_toggle_custom"></span>
                        <div class="mayhen_finder_toggle_text">
                            <strong>Kích hoạt Tra Cứu Bàn Tiệc Trong Thiệp Cưới</strong>
                            <span>Cho phép quan khách xem vị trí bàn tiệc và tra cứu nhanh khi đến sảnh tiệc</span>
                        </div>
                    </label>
                </div>

                {{-- Tiêu đề & Lời ngỏ phụ --}}
                <div class="mayhen_finder_field_group">
                    <label class="mayhen_finder_label" for="title">Tiêu Đề Section</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $config['title']) }}" required class="mayhen_finder_input">
                </div>

                <div class="mayhen_finder_field_group">
                    <label class="mayhen_finder_label" for="subtitle">Lời Giới Thiệu Phụ</label>
                    <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle', $config['subtitle']) }}" class="mayhen_finder_input">
                </div>

                {{-- Các tùy chọn hiển thị --}}
                <div class="mayhen_finder_field_group">
                    <label class="mayhen_finder_label">Quyền Riêng Tư & Mức Độ Chi Tiết</label>
                    <div class="mayhen_finder_checkboxes">
                        <label class="mayhen_finder_checkbox_item">
                            <input type="checkbox" name="show_table_name" value="1" {{ $config['show_table_name'] ? 'checked' : '' }}>
                            <span>Hiển thị Tên Bàn Tiệc (VD: Bàn Số 08 - Bạn Cấp 3)</span>
                        </label>
                        <label class="mayhen_finder_checkbox_item">
                            <input type="checkbox" name="show_table_zone" value="1" {{ $config['show_table_zone'] ? 'checked' : '' }}>
                            <span>Hiển thị Khu Vực Bàn (VD: Sảnh Chính, Gần Sân Khấu, Cánh Phải)</span>
                        </label>
                        <label class="mayhen_finder_checkbox_item">
                            <input type="checkbox" name="show_table_mates" value="1" {{ $config['show_table_mates'] ? 'checked' : '' }}>
                            <span>Hiển thị Bạn Cùng Bàn (Tên những người quen ngồi cùng bàn)</span>
                        </label>
                        <label class="mayhen_finder_checkbox_item">
                            <input type="checkbox" name="allow_search_by_phone" value="1" {{ $config['allow_search_by_phone'] ? 'checked' : '' }}>
                            <span>Cho phép tra cứu bằng 4 số cuối Điện Thoại (Thuận tiện khi trùng tên)</span>
                        </label>
                    </div>
                </div>

                {{-- Lời chào đón an tọa --}}
                <div class="mayhen_finder_field_group">
                    <label class="mayhen_finder_label" for="welcome_message">Thông Điệp Chào Mừng An Tọa</label>
                    <textarea id="welcome_message" name="welcome_message" rows="3" class="mayhen_finder_textarea">{{ old('welcome_message', $config['welcome_message']) }}</textarea>
                </div>

                {{-- Lời nhắc khi không tìm thấy --}}
                <div class="mayhen_finder_field_group">
                    <label class="mayhen_finder_label" for="empty_result_message">Thông Báo Khi Chưa Tìm Thấy Kết Quả</label>
                    <textarea id="empty_result_message" name="empty_result_message" rows="2" class="mayhen_finder_textarea">{{ old('empty_result_message', $config['empty_result_message']) }}</textarea>
                </div>

                <div class="mayhen_finder_form_actions">
                    <button type="submit" class="mayhen_finder_btn mayhen_finder_btn_primary">
                        <span>Lưu Cấu Hình Tra Cứu</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Cột Xem Trước (Live Preview) & Danh Sách Bàn --}}
        <div class="mayhen_finder_preview_col">
            {{-- Thẻ Preview mô phỏng --}}
            <div class="mayhen_finder_card">
                <div class="mayhen_finder_card_header">
                    <h2 class="mayhen_finder_card_title">Mô Phỏng Trải Nghiệm Khách Mời</h2>
                    <p class="mayhen_finder_card_desc">Thẻ thông báo số bàn xuất hiện trên điện thoại của khách</p>
                </div>

                <div class="mayhen_finder_preview_box">
                    <div class="mayhen_finder_preview_card">
                        <div class="mayhen_finder_preview_guest">
                            Kính gửi <strong>Anh Nguyễn Hoàng Nam</strong>
                        </div>
                        <div class="mayhen_finder_preview_table">
                            <div class="lbl">Vị trí an tọa của Quý Khách</div>
                            <div class="val">Bàn Số 08</div>
                            <div class="zone">📍 Khu Vực Bạn Cô Dâu (Cánh Phải Sân Khấu)</div>
                        </div>
                        <div class="mayhen_finder_preview_mates">
                            <div class="mates_lbl">👥 Những người bạn cùng bàn:</div>
                            <div class="mates_tags">
                                <span>Trần Thu Trang</span>
                                <span>Lê Minh Khang</span>
                                <span>Đỗ Bảo Ngọc</span>
                            </div>
                        </div>
                        <div class="mayhen_finder_preview_msg">
                            "Kính mời Quý khách an tọa tại vị trí bàn tiệc đã chuẩn bị riêng, cùng nâng ly chung vui và chia sẻ khoảnh khắc ngọt ngào với gia đình!"
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách bàn tiệc hiện có --}}
            <div class="mayhen_finder_card">
                <div class="mayhen_finder_card_header">
                    <h2 class="mayhen_finder_card_title">Danh Sách Bàn Tiệc Sảnh Cưới</h2>
                    <p class="mayhen_finder_card_desc">{{ count($overview['tables']) }} bàn tiệc đã được thiết lập</p>
                </div>

                @if(count($overview['tables']) > 0)
                    <div class="mayhen_finder_table_list">
                        @foreach($overview['tables'] as $tbl)
                            <div class="mayhen_finder_table_item {{ $tbl['is_over_capacity'] ? 'mayhen_finder_table_item_alert' : '' }}">
                                <div class="mayhen_finder_table_item_info">
                                    <strong>{{ $tbl['name'] }}</strong>
                                    <span>Khu vực: {{ $tbl['zone'] }}</span>
                                    @if(count($tbl['guests_preview']) > 0)
                                        <div class="mayhen_finder_table_item_preview">
                                            Khách: {{ implode(', ', $tbl['guests_preview']) }}
                                            @if($tbl['assigned_count'] > count($tbl['guests_preview']))
                                                ... (+{{ $tbl['assigned_count'] - count($tbl['guests_preview']) }})
                                            @endif
                                        </div>
                                    @else
                                        <div class="mayhen_finder_table_item_preview" style="color: #A39B92;">
                                            Chưa có khách nào được gán vào bàn này
                                        </div>
                                    @endif
                                </div>
                                <div class="mayhen_finder_table_item_cap">
                                    <span class="badge {{ $tbl['is_over_capacity'] ? 'badge_over' : 'badge_normal' }}">
                                        {{ $tbl['assigned_count'] }}/{{ $tbl['capacity'] }} chỗ
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mayhen_finder_empty_tables">
                        <p>Chưa có bàn tiệc nào được tạo trong hồ sơ đám cưới.</p>
                        <a href="{{ route('seating.index', $invitation->id) }}" class="mayhen_finder_btn mayhen_finder_btn_outline">
                            Tạo Bàn Tiệc Trong Sơ Đồ Bàn
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   MAYHEN QUIET LUXURY GUEST TABLE FINDER STUDIO STYLES (PURE VANILLA CSS)
   ========================================================================== */
.mayhen_finder_studio {
    max-width: 1120px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: inherit;
    color: #2D2A26;
}

.mayhen_finder_topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_finder_back_link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    color: #7D756D;
    text-decoration: none;
    transition: color 0.2s ease;
}

.mayhen_finder_back_link:hover {
    color: #9E7A52;
}

.mayhen_finder_quick_actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mayhen_finder_btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    line-height: 1.4;
}

.mayhen_finder_btn_primary {
    background: #9E7A52;
    color: #FFFFFF;
}

.mayhen_finder_btn_primary:hover {
    background: #846541;
}

.mayhen_finder_btn_outline {
    background: #FFFFFF;
    color: #4A443E;
    border: 1px solid #E6E1DA;
}

.mayhen_finder_btn_outline:hover {
    border-color: #9E7A52;
    color: #9E7A52;
}

.mayhen_finder_alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 24px;
}

.mayhen_finder_alert_success {
    background: #E8F5E9;
    color: #2E7D32;
    border: 1px solid #C8E6C9;
}

.mayhen_finder_header {
    margin-bottom: 28px;
}

.mayhen_finder_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: #F4EFEA;
    color: #9E7A52;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.mayhen_finder_title {
    font-size: 1.85rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 8px 0;
    letter-spacing: -0.01em;
}

.mayhen_finder_subtitle {
    font-size: 0.95rem;
    color: #7D756D;
    line-height: 1.6;
    margin: 0;
    max-width: 820px;
}

/* KPIs Grid */
.mayhen_finder_kpis_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.mayhen_finder_kpi_card {
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.mayhen_finder_kpi_val {
    font-size: 2rem;
    font-weight: 700;
    color: #2D2A26;
    line-height: 1;
    margin-bottom: 6px;
}

.mayhen_finder_kpi_val_seated { color: #2E7D32; }
.mayhen_finder_kpi_val_unseated { color: #F57C00; }
.mayhen_finder_kpi_val_rate { color: #9E7A52; }

.mayhen_finder_kpi_lbl {
    font-size: 0.88rem;
    font-weight: 600;
    color: #4A443E;
    margin-bottom: 4px;
}

.mayhen_finder_kpi_hint {
    font-size: 0.75rem;
    color: #8C827A;
}

/* Main Layout */
.mayhen_finder_main_layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
}

.mayhen_finder_card {
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 16px;
    padding: 26px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.mayhen_finder_card_header {
    margin-bottom: 22px;
    border-bottom: 1px solid #F0ECE6;
    padding-bottom: 14px;
}

.mayhen_finder_card_title {
    font-size: 1.12rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 4px 0;
}

.mayhen_finder_card_desc {
    font-size: 0.85rem;
    color: #7D756D;
    margin: 0;
}

.mayhen_finder_field_group {
    margin-bottom: 20px;
}

.mayhen_finder_label {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    color: #4A443E;
    margin-bottom: 8px;
}

.mayhen_finder_input, .mayhen_finder_textarea {
    width: 100%;
    padding: 10px 14px;
    background: #FAF8F5;
    border: 1px solid #E6E1DA;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #2D2A26;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
    font-family: inherit;
}

.mayhen_finder_input:focus, .mayhen_finder_textarea:focus {
    border-color: #9E7A52;
    background: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(158, 122, 82, 0.1);
}

.mayhen_finder_toggle_label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    background: #FAF7F2;
    border: 1px solid #E6DBCF;
    border-radius: 10px;
    padding: 14px 16px;
}

.mayhen_finder_toggle_input {
    margin-top: 3px;
    accent-color: #9E7A52;
    width: 18px;
    height: 18px;
}

.mayhen_finder_toggle_text strong {
    display: block;
    font-size: 0.92rem;
    color: #2D2A26;
    margin-bottom: 2px;
}

.mayhen_finder_toggle_text span {
    font-size: 0.82rem;
    color: #7D756D;
}

.mayhen_finder_checkboxes {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #FAF8F5;
    border: 1px solid #ECE7E1;
    border-radius: 10px;
    padding: 14px 16px;
}

.mayhen_finder_checkbox_item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.88rem;
    color: #4A443E;
    cursor: pointer;
}

.mayhen_finder_checkbox_item input {
    accent-color: #9E7A52;
}

.mayhen_finder_form_actions {
    margin-top: 26px;
    padding-top: 18px;
    border-top: 1px solid #F0ECE6;
    text-align: right;
}

/* Preview Box */
.mayhen_finder_preview_box {
    background: linear-gradient(135deg, #FAF7F2 0%, #F5EFE6 100%);
    border: 1px solid #E6DBCF;
    border-radius: 14px;
    padding: 24px;
}

.mayhen_finder_preview_card {
    background: #FFFFFF;
    border: 1px solid #EAE2D8;
    border-radius: 14px;
    padding: 24px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.mayhen_finder_preview_guest {
    font-size: 0.95rem;
    color: #6A625A;
    margin-bottom: 14px;
}

.mayhen_finder_preview_guest strong {
    color: #2D2A26;
}

.mayhen_finder_preview_table {
    background: #FAF7F2;
    border: 1px dashed #D9CFB8;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 14px;
}

.mayhen_finder_preview_table .lbl {
    font-size: 0.72rem;
    text-transform: uppercase;
    color: #8C827A;
    margin-bottom: 4px;
}

.mayhen_finder_preview_table .val {
    font-size: 1.5rem;
    font-weight: 700;
    color: #9E7A52;
    margin-bottom: 4px;
}

.mayhen_finder_preview_table .zone {
    font-size: 0.82rem;
    color: #4A443E;
    font-weight: 500;
}

.mayhen_finder_preview_mates {
    text-align: left;
    margin-bottom: 14px;
    border-top: 1px solid #F0ECE6;
    padding-top: 12px;
}

.mayhen_finder_preview_mates .mates_lbl {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6A625A;
    margin-bottom: 6px;
}

.mayhen_finder_preview_mates .mates_tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.mayhen_finder_preview_mates .mates_tags span {
    background: #F4EFEA;
    font-size: 0.75rem;
    padding: 3px 10px;
    border-radius: 999px;
    color: #4A443E;
}

.mayhen_finder_preview_msg {
    font-size: 0.82rem;
    color: #8C827A;
    font-style: italic;
    line-height: 1.4;
}

/* Danh sách bàn tiệc */
.mayhen_finder_table_list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 420px;
    overflow-y: auto;
    padding-right: 4px;
}

.mayhen_finder_table_item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    background: #FAF8F5;
    border: 1px solid #ECE7E1;
    border-radius: 10px;
    padding: 12px 16px;
}

.mayhen_finder_table_item_alert {
    border-color: #FFCDD2;
    background: #FFF9F9;
}

.mayhen_finder_table_item_info strong {
    display: block;
    font-size: 0.92rem;
    color: #2D2A26;
}

.mayhen_finder_table_item_info span {
    font-size: 0.78rem;
    color: #7D756D;
}

.mayhen_finder_table_item_preview {
    font-size: 0.75rem;
    color: #6A625A;
    margin-top: 4px;
}

.mayhen_finder_table_item_cap .badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge_normal { background: #E8F5E9; color: #2E7D32; }
.badge_over { background: #FFEBEE; color: #D32F2F; }

.mayhen_finder_empty_tables {
    text-align: center;
    padding: 32px 16px;
    color: #8C827A;
}

.mayhen_finder_empty_tables p {
    margin-bottom: 14px;
}

@media (max-width: 900px) {
    .mayhen_finder_kpis_grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .mayhen_finder_main_layout {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

