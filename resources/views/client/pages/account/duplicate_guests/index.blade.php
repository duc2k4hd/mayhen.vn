@extends('client.pages.account.layout')

@php
    $pageTitle = 'Lọc Trùng Khách Mời & Gộp Bản Ghi — Mây Hẹn';
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_content')
<div class="mayhen_dup_studio">
    {{-- Thanh điều hướng quay lại --}}
    <div class="mayhen_dup_topbar">
        <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_dup_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Khách Mời</span>
        </a>

        <div class="mayhen_dup_quick_actions">
            <a href="{{ route('account.duplicate_guests.export_csv', $invitation->id) }}" class="mayhen_dup_btn mayhen_dup_btn_outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất Báo Cáo CSV</span>
            </a>

            <button type="button" id="btnRescan" class="mayhen_dup_btn mayhen_dup_btn_primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="spin-icon">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                <span>Quét Lại Tức Thì</span>
            </button>
        </div>
    </div>

    {{-- Header giới thiệu --}}
    <header class="mayhen_dup_header">
        <div class="mayhen_dup_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Duplicate Guest Detector & One-Click Merge Studio (IDEA-B09)</span>
        </div>
        <h1 class="mayhen_dup_title">Lọc Trùng Khách Mời & Gộp Bản Ghi Thông Minh</h1>
        <p class="mayhen_dup_subtitle">
            Tự động đối soát số điện thoại, họ tên không dấu và danh xưng để phát hiện các dòng khách mời bị trùng khi nhập từ nhiều nguồn (Excel bố mẹ, Zalo, danh bạ), hỗ trợ gộp 1-chạm bảo toàn lịch sử RSVP và bàn tiệc.
        </p>
    </header>

    {{-- 4 Card KPI --}}
    <div class="mayhen_dup_kpis_grid">
        <div class="mayhen_dup_kpi_card">
            <div class="mayhen_dup_kpi_val" id="kpiTotal">{{ $kpis['total_guests'] }}</div>
            <div class="mayhen_dup_kpi_lbl">Tổng Số Khách Mời</div>
            <div class="mayhen_dup_kpi_hint">Đã có trong hồ sơ hôn lễ</div>
        </div>

        <div class="mayhen_dup_kpi_card">
            <div class="mayhen_dup_kpi_val mayhen_dup_kpi_val_dup" id="kpiDupCount">{{ $kpis['duplicate_pairs_count'] }}</div>
            <div class="mayhen_dup_kpi_lbl">Cặp Nghi Vấn Trùng</div>
            <div class="mayhen_dup_kpi_hint">{{ $kpis['critical_count'] }} cặp trùng số điện thoại</div>
        </div>

        <div class="mayhen_dup_kpi_card">
            <div class="mayhen_dup_kpi_val mayhen_dup_kpi_val_save" id="kpiSaved">{{ $kpis['potential_saved_invitations'] }}</div>
            <div class="mayhen_dup_kpi_lbl">Suất Mời Tiết Kiệm</div>
            <div class="mayhen_dup_kpi_hint">Tránh in & phát thừa thiệp cưới</div>
        </div>

        <div class="mayhen_dup_kpi_card">
            <div class="mayhen_dup_kpi_val mayhen_dup_kpi_val_clean" id="kpiClean">{{ $kpis['clean_rate'] }}%</div>
            <div class="mayhen_dup_kpi_lbl">Độ Sạch Dữ Liệu</div>
            <div class="mayhen_dup_kpi_hint">Tỷ lệ khách độc bản không trùng</div>
        </div>
    </div>

    {{-- Danh Sách Các Cặp Nghi Vấn Trùng Lặp --}}
    <div class="mayhen_dup_list_wrap" id="pairsContainer">
        @if(count($pairs) > 0)
            <div class="mayhen_dup_list_header">
                <h2>Phát Hiện {{ count($pairs) }} Cặp Khách Mời Có Nguy Cơ Trùng Lặp</h2>
                <p>Xem xét kỹ thông tin hai bên trước khi thực hiện gộp hoặc bỏ qua</p>
            </div>

            <div class="mayhen_dup_pairs_list">
                @foreach($pairs as $p)
                    <div class="mayhen_dup_pair_card" id="{{ $p['pair_id'] }}">
                        <div class="mayhen_dup_pair_top">
                            <div class="mayhen_dup_pair_badge {{ $p['severity'] === 'critical' ? 'badge_critical' : 'badge_warning' }}">
                                @if($p['severity'] === 'critical')
                                    🔴 Trùng 100% Số Điện Thoại
                                @else
                                    🟡 Tên Tương Đồng ({{ $p['confidence'] }}%)
                                @endif
                            </div>
                            <div class="mayhen_dup_pair_reasons">
                                {{ implode(' • ', $p['reasons']) }}
                            </div>
                        </div>

                        <div class="mayhen_dup_compare_grid">
                            {{-- Cột Khách A --}}
                            <div class="mayhen_dup_guest_col">
                                <div class="mayhen_dup_col_tag">Bản ghi A (ID #{{ $p['guest_a']['id'] }})</div>
                                <div class="mayhen_dup_guest_name">
                                    <span class="salutation">{{ $p['guest_a']['salutation'] ?: 'Chưa có xưng hô' }}</span>
                                    <strong>{{ $p['guest_a']['name'] }}</strong>
                                </div>
                                <div class="mayhen_dup_guest_meta">
                                    <div>📞 SĐT: <strong>{{ $p['guest_a']['phone'] ?: 'Chưa có' }}</strong></div>
                                    <div>👥 Nhóm: <strong>{{ $p['guest_a']['group_name'] ?: 'Chưa phân nhóm' }}</strong></div>
                                    <div>🍽️ Bàn tiệc: <strong>{{ $p['guest_a']['table_name'] ?: 'Chưa xếp bàn' }}</strong></div>
                                    <div>🎟️ Suất mời: <strong>{{ $p['guest_a']['expected_attendees'] }} người</strong></div>
                                    <div>✉️ Trạng thái: <strong>{{ $p['guest_a']['is_sent'] ? 'Đã gửi thiệp' : 'Chưa gửi' }}</strong></div>
                                </div>
                            </div>

                            <div class="mayhen_dup_vs_icon">
                                <span>VS</span>
                            </div>

                            {{-- Cột Khách B --}}
                            <div class="mayhen_dup_guest_col">
                                <div class="mayhen_dup_col_tag">Bản ghi B (ID #{{ $p['guest_b']['id'] }})</div>
                                <div class="mayhen_dup_guest_name">
                                    <span class="salutation">{{ $p['guest_b']['salutation'] ?: 'Chưa có xưng hô' }}</span>
                                    <strong>{{ $p['guest_b']['name'] }}</strong>
                                </div>
                                <div class="mayhen_dup_guest_meta">
                                    <div>📞 SĐT: <strong>{{ $p['guest_b']['phone'] ?: 'Chưa có' }}</strong></div>
                                    <div>👥 Nhóm: <strong>{{ $p['guest_b']['group_name'] ?: 'Chưa phân nhóm' }}</strong></div>
                                    <div>🍽️ Bàn tiệc: <strong>{{ $p['guest_b']['table_name'] ?: 'Chưa xếp bàn' }}</strong></div>
                                    <div>🎟️ Suất mời: <strong>{{ $p['guest_b']['expected_attendees'] }} người</strong></div>
                                    <div>✉️ Trạng thái: <strong>{{ $p['guest_b']['is_sent'] ? 'Đã gửi thiệp' : 'Chưa gửi' }}</strong></div>
                                </div>
                            </div>
                        </div>

                        {{-- Thao tác xử lý --}}
                        <div class="mayhen_dup_pair_actions">
                            <button type="button" class="mayhen_dup_btn mayhen_dup_btn_outline btn-dismiss" data-pair-id="{{ $p['pair_id'] }}">
                                <span>Không Phải Trùng / Bỏ Qua</span>
                            </button>
                            <button type="button" class="mayhen_dup_btn mayhen_dup_btn_primary btn-open-merge" 
                                    data-pair-id="{{ $p['pair_id'] }}"
                                    data-guest-a="{{ json_encode($p['guest_a']) }}"
                                    data-guest-b="{{ json_encode($p['guest_b']) }}">
                                <span>Gộp 2 Khách Này (1-Chạm)</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Trạng thái dữ liệu sạch hoàn hảo --}}
            <div class="mayhen_dup_clean_state">
                <div class="mayhen_dup_clean_icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h2>Tuyệt Vời! Danh Sách Khách Mời 100% Sạch &amp; Độc Bản</h2>
                <p>Không phát hiện dấu hiệu trùng lặp số điện thoại hay họ tên nào trong hồ sơ hôn lễ.</p>
                <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_dup_btn mayhen_dup_btn_primary">
                    Xem Toàn Bộ Danh Sách Khách Mời
                </a>
            </div>
        @endif
    </div>
</div>

{{-- MODAL GỘP KHÁCH MỜI THÔNG MINH --}}
<div id="modalMerge" class="mayhen_dup_modal" style="display: none;">
    <div class="mayhen_dup_modal_backdrop" id="modalMergeBackdrop"></div>
    <div class="mayhen_dup_modal_dialog">
        <div class="mayhen_dup_modal_header">
            <h3>Xác Nhận Gộp Bản Ghi Khách Mời</h3>
            <button type="button" class="mayhen_dup_modal_close" id="btnCloseModal">✕</button>
        </div>

        <form id="formMerge" onsubmit="return false;">
            <input type="hidden" id="mergePairId" value="">
            <input type="hidden" id="primaryGuestId" value="">
            <input type="hidden" id="secondaryGuestId" value="">

            <div class="mayhen_dup_modal_body">
                <p class="mayhen_dup_modal_desc">
                    Hệ thống sẽ hợp nhất thông tin và chuyển toàn bộ lịch sử phản hồi RSVP, bàn tiệc sang khách chính. Khách phụ sẽ được xóa an toàn khỏi danh sách.
                </p>

                <div class="mayhen_dup_modal_field">
                    <label>Chọn Họ và Tên Giữ Lại:</label>
                    <div class="mayhen_dup_radio_group" id="radioGroupName"></div>
                </div>

                <div class="mayhen_dup_modal_field">
                    <label>Chọn Số Điện Thoại:</label>
                    <div class="mayhen_dup_radio_group" id="radioGroupPhone"></div>
                </div>

                <div class="mayhen_dup_modal_field">
                    <label>Chọn Phân Nhóm Khách:</label>
                    <div class="mayhen_dup_radio_group" id="radioGroupGroup"></div>
                </div>

                <div class="mayhen_dup_modal_field">
                    <label class="mayhen_dup_checkbox_label">
                        <input type="checkbox" id="checkCombineAttendees" checked>
                        <span>Cộng dồn số suất mời dự kiến của 2 người</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_dup_modal_footer">
                <button type="button" class="mayhen_dup_btn mayhen_dup_btn_outline" id="btnCancelMerge">Hủy Bỏ</button>
                <button type="button" class="mayhen_dup_btn mayhen_dup_btn_primary" id="btnSubmitMerge">Xác Nhận Gộp Bản Ghi</button>
            </div>
        </form>
    </div>
</div>

{{-- Toast thông báo Pure JS --}}
<div id="dupToast" class="mayhen_dup_toast" role="alert" aria-live="polite"></div>

<style>
/* ==========================================================================
   MAYHEN QUIET LUXURY DUPLICATE GUEST DETECTOR STYLES (PURE VANILLA CSS)
   ========================================================================== */
.mayhen_dup_studio {
    max-width: 1120px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: inherit;
    color: #2D2A26;
}

.mayhen_dup_topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_dup_back_link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    color: #7D756D;
    text-decoration: none;
    transition: color 0.2s ease;
}

.mayhen_dup_back_link:hover {
    color: #9E7A52;
}

.mayhen_dup_quick_actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mayhen_dup_btn {
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

.mayhen_dup_btn_primary {
    background: #9E7A52;
    color: #FFFFFF;
}

.mayhen_dup_btn_primary:hover {
    background: #846541;
}

.mayhen_dup_btn_outline {
    background: #FFFFFF;
    color: #4A443E;
    border: 1px solid #E6E1DA;
}

.mayhen_dup_btn_outline:hover {
    border-color: #9E7A52;
    color: #9E7A52;
}

.spin-icon.spinning {
    animation: dupSpin 1s linear infinite;
}

@keyframes dupSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.mayhen_dup_header {
    margin-bottom: 28px;
}

.mayhen_dup_badge {
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

.mayhen_dup_title {
    font-size: 1.85rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 8px 0;
    letter-spacing: -0.01em;
}

.mayhen_dup_subtitle {
    font-size: 0.95rem;
    color: #7D756D;
    line-height: 1.6;
    margin: 0;
    max-width: 820px;
}

/* KPIs Grid */
.mayhen_dup_kpis_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.mayhen_dup_kpi_card {
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.mayhen_dup_kpi_val {
    font-size: 2rem;
    font-weight: 700;
    color: #2D2A26;
    line-height: 1;
    margin-bottom: 6px;
}

.mayhen_dup_kpi_val_dup { color: #D32F2F; }
.mayhen_dup_kpi_val_save { color: #9E7A52; }
.mayhen_dup_kpi_val_clean { color: #2E7D32; }

.mayhen_dup_kpi_lbl {
    font-size: 0.88rem;
    font-weight: 600;
    color: #4A443E;
    margin-bottom: 4px;
}

.mayhen_dup_kpi_hint {
    font-size: 0.75rem;
    color: #8C827A;
}

/* Danh sách cặp trùng lặp */
.mayhen_dup_list_wrap {
    margin-top: 24px;
}

.mayhen_dup_list_header {
    margin-bottom: 20px;
}

.mayhen_dup_list_header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 4px 0;
}

.mayhen_dup_list_header p {
    font-size: 0.88rem;
    color: #7D756D;
    margin: 0;
}

.mayhen_dup_pairs_list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.mayhen_dup_pair_card {
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    transition: all 0.3s ease;
}

.mayhen_dup_pair_top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.mayhen_dup_pair_badge {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 700;
}

.badge_critical { background: #FFEBEE; color: #D32F2F; }
.badge_warning { background: #FFF3E0; color: #F57C00; }

.mayhen_dup_pair_reasons {
    font-size: 0.85rem;
    color: #6A625A;
}

.mayhen_dup_compare_grid {
    display: grid;
    grid-template-columns: 1fr 40px 1fr;
    gap: 16px;
    align-items: center;
    background: #FAF8F5;
    border: 1px solid #ECE7E1;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 20px;
}

.mayhen_dup_guest_col {
    background: #FFFFFF;
    border: 1px solid #EAE3D9;
    border-radius: 10px;
    padding: 16px;
}

.mayhen_dup_col_tag {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #8C827A;
    margin-bottom: 8px;
}

.mayhen_dup_guest_name {
    margin-bottom: 12px;
}

.mayhen_dup_guest_name .salutation {
    font-size: 0.85rem;
    color: #8C827A;
    display: block;
}

.mayhen_dup_guest_name strong {
    font-size: 1.15rem;
    color: #2D2A26;
}

.mayhen_dup_guest_meta {
    font-size: 0.85rem;
    color: #6A625A;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mayhen_dup_vs_icon {
    text-align: center;
    font-weight: 700;
    color: #9E7A52;
    font-size: 0.9rem;
}

.mayhen_dup_pair_actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-top: 1px solid #F0ECE6;
    padding-top: 16px;
}

/* Trạng thái dữ liệu sạch */
.mayhen_dup_clean_state {
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 16px;
    padding: 48px 24px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.mayhen_dup_clean_icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #E8F5E9;
    color: #2E7D32;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px auto;
}

.mayhen_dup_clean_state h2 {
    font-size: 1.45rem;
    color: #2D2A26;
    margin: 0 0 8px 0;
}

.mayhen_dup_clean_state p {
    font-size: 0.95rem;
    color: #7D756D;
    margin: 0 0 24px 0;
}

/* Modal Gộp */
.mayhen_dup_modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.mayhen_dup_modal_backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.45);
}

.mayhen_dup_modal_dialog {
    position: relative;
    background: #FFFFFF;
    border-radius: 16px;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    z-index: 10;
    overflow: hidden;
}

.mayhen_dup_modal_header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid #ECE7E1;
}

.mayhen_dup_modal_header h3 {
    font-size: 1.15rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0;
}

.mayhen_dup_modal_close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #8C827A;
}

.mayhen_dup_modal_body {
    padding: 20px 24px;
    max-height: 60vh;
    overflow-y: auto;
}

.mayhen_dup_modal_desc {
    font-size: 0.88rem;
    color: #6A625A;
    line-height: 1.5;
    margin: 0 0 16px 0;
    background: #FAF7F2;
    padding: 10px 14px;
    border-radius: 8px;
}

.mayhen_dup_modal_field {
    margin-bottom: 16px;
}

.mayhen_dup_modal_field label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #4A443E;
    margin-bottom: 8px;
}

.mayhen_dup_radio_group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mayhen_dup_radio_item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #FAF8F5;
    border: 1px solid #ECE7E1;
    border-radius: 8px;
    padding: 10px 14px;
    cursor: pointer;
    font-size: 0.88rem;
    color: #2D2A26;
}

.mayhen_dup_radio_item input {
    accent-color: #9E7A52;
}

.mayhen_dup_checkbox_label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.88rem;
    color: #2D2A26;
    cursor: pointer;
}

.mayhen_dup_checkbox_label input {
    accent-color: #9E7A52;
}

.mayhen_dup_modal_footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid #ECE7E1;
    background: #FAF8F5;
}

/* Toast */
.mayhen_dup_toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #2D2A26;
    color: #FFFFFF;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 0.88rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.25s ease;
    pointer-events: none;
}

.mayhen_dup_toast.show {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .mayhen_dup_kpis_grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .mayhen_dup_compare_grid {
        grid-template-columns: 1fr;
    }
    .mayhen_dup_vs_icon {
        padding: 4px 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnRescan = document.getElementById('btnRescan');
    const modalMerge = document.getElementById('modalMerge');
    const modalBackdrop = document.getElementById('modalMergeBackdrop');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancelMerge = document.getElementById('btnCancelMerge');
    const btnSubmitMerge = document.getElementById('btnSubmitMerge');
    const toast = document.getElementById('dupToast');

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2800);
    }

    function closeModal() {
        if (modalMerge) modalMerge.style.display = 'none';
    }

    if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
    if (btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
    if (btnCancelMerge) btnCancelMerge.addEventListener('click', closeModal);

    // 1. Quét lại tức thì (Rescan AJAX)
    if (btnRescan) {
        btnRescan.addEventListener('click', async function () {
            const icon = btnRescan.querySelector('.spin-icon');
            if (icon) icon.classList.add('spinning');
            btnRescan.disabled = true;

            try {
                const res = await fetch("{{ route('account.duplicate_guests.scan', $invitation->id) }}", {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data && data.success) {
                    showToast('✓ ' + data.message);
                    window.location.reload();
                } else {
                    showToast('Quét thất bại.');
                }
            } catch (err) {
                showToast('Lỗi mạng khi quét lại.');
            } finally {
                if (icon) icon.classList.remove('spinning');
                btnRescan.disabled = false;
            }
        });
    }

    // 2. Bỏ qua cặp khách mời (Dismiss False Alarm)
    document.querySelectorAll('.btn-dismiss').forEach(btn => {
        btn.addEventListener('click', function () {
            const pairId = this.dataset.pairId;
            const card = document.getElementById(pairId);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 250);
                showToast('Đã bỏ qua cặp khách mời này.');
            }
        });
    });

    // 3. Mở Modal Gộp Khách Mời
    document.querySelectorAll('.btn-open-merge').forEach(btn => {
        btn.addEventListener('click', function () {
            const pairId = this.dataset.pairId;
            const guestA = JSON.parse(this.dataset.guestA);
            const guestB = JSON.parse(this.dataset.guestB);

            document.getElementById('mergePairId').value = pairId;
            document.getElementById('primaryGuestId').value = guestA.id;
            document.getElementById('secondaryGuestId').value = guestB.id;

            // Render Radio Chọn Tên
            const radioName = document.getElementById('radioGroupName');
            radioName.innerHTML = `
                <label class="mayhen_dup_radio_item">
                    <input type="radio" name="selected_name" value="${guestA.name}" checked>
                    <span>${guestA.name} (Bản ghi A)</span>
                </label>
                <label class="mayhen_dup_radio_item">
                    <input type="radio" name="selected_name" value="${guestB.name}">
                    <span>${guestB.name} (Bản ghi B)</span>
                </label>
            `;

            // Render Radio Chọn SĐT
            const radioPhone = document.getElementById('radioGroupPhone');
            radioPhone.innerHTML = `
                <label class="mayhen_dup_radio_item">
                    <input type="radio" name="selected_phone" value="${guestA.phone || ''}" checked>
                    <span>${guestA.phone || 'Chưa có SĐT'} (Bản ghi A)</span>
                </label>
                <label class="mayhen_dup_radio_item">
                    <input type="radio" name="selected_phone" value="${guestB.phone || ''}">
                    <span>${guestB.phone || 'Chưa có SĐT'} (Bản ghi B)</span>
                </label>
            `;

            // Render Radio Chọn Nhóm
            const radioGroup = document.getElementById('radioGroupGroup');
            radioGroup.innerHTML = `
                <label class="mayhen_dup_radio_item">
                    <input type="radio" name="selected_group" value="${guestA.group_name || ''}" checked>
                    <span>${guestA.group_name || 'Chưa phân nhóm'} (Bản ghi A)</span>
                </label>
                <label class="mayhen_dup_radio_item">
                    <input type="radio" name="selected_group" value="${guestB.group_name || ''}">
                    <span>${guestB.group_name || 'Chưa phân nhóm'} (Bản ghi B)</span>
                </label>
            `;

            if (modalMerge) modalMerge.style.display = 'flex';
        });
    });

    // 4. Gửi yêu cầu gộp bản ghi qua AJAX
    if (btnSubmitMerge) {
        btnSubmitMerge.addEventListener('click', async function () {
            const pairId = document.getElementById('mergePairId').value;
            const primaryId = document.getElementById('primaryGuestId').value;
            const secondaryId = document.getElementById('secondaryGuestId').value;

            const selectedName = document.querySelector('input[name="selected_name"]:checked')?.value || '';
            const selectedPhone = document.querySelector('input[name="selected_phone"]:checked')?.value || '';
            const selectedGroup = document.querySelector('input[name="selected_group"]:checked')?.value || '';
            const combineAttendees = document.getElementById('checkCombineAttendees')?.checked ? 1 : 0;

            btnSubmitMerge.disabled = true;
            btnSubmitMerge.textContent = 'Đang gộp...';

            try {
                const res = await fetch("{{ route('account.duplicate_guests.merge', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        primary_guest_id: primaryId,
                        secondary_guest_id: secondaryId,
                        name: selectedName,
                        phone: selectedPhone,
                        group_name: selectedGroup,
                        combine_attendees: combineAttendees
                    })
                });

                const data = await res.json();
                if (data && data.success) {
                    closeModal();
                    showToast('✓ ' + data.message);

                    // Xóa thẻ cặp đó khỏi danh sách
                    const card = document.getElementById(pairId);
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => card.remove(), 250);
                    }

                    // Cập nhật lại số liệu KPI nhanh
                    const kpiDup = document.getElementById('kpiDupCount');
                    if (kpiDup) {
                        let cur = parseInt(kpiDup.textContent) || 0;
                        kpiDup.textContent = Math.max(0, cur - 1);
                    }
                } else {
                    showToast(data.message || 'Gộp khách mời thất bại.');
                }
            } catch (err) {
                showToast('Lỗi mạng khi gộp bản ghi.');
            } finally {
                btnSubmitMerge.disabled = false;
                btnSubmitMerge.textContent = 'Xác Nhận Gộp Bản Ghi';
            }
        });
    }
});
</script>
@endsection

