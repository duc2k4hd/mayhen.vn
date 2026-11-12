@extends('client.pages.account.layout')

@php
    $pageTitle = 'Bác Sĩ Chất Lượng Thiệp & Diễn Tập — Mây Hẹn';
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_content')
<div class="mayhen_doctor_container">
    {{-- Thanh điều hướng quay lại --}}
    <div class="mayhen_doctor_topbar">
        <a href="{{ route('account.invitations') }}" class="mayhen_doctor_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Thiệp Cưới</span>
        </a>

        <div class="mayhen_doctor_quick_actions">
            <button type="button" id="btnRescan" class="mayhen_doctor_btn mayhen_doctor_btn_outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="spin-icon">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                <span>Quét Lại Tức Thì</span>
            </button>
            <a href="{{ route('account.invitations.settings', $invitation->id) }}" class="mayhen_doctor_btn mayhen_doctor_btn_primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Vào Studio Chỉnh Sửa</span>
            </a>
        </div>
    </div>

    {{-- Header giới thiệu --}}
    <header class="mayhen_doctor_header">
        <div class="mayhen_doctor_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            <span>Quality Doctor & Rehearsal Cockpit (IDEA-E01 & Section 23/24)</span>
        </div>
        <h1 class="mayhen_doctor_title">Bác Sĩ Chất Lượng Thiệp & Diễn Tập Nghiệm Thu</h1>
        <p class="mayhen_doctor_subtitle">
            Rà soát 10 Section tiêu chuẩn, kiểm tra xung đột lịch trình, tính toàn vẹn VietQR và giả lập trải nghiệm mở thiệp thực tế của quan khách trước khi phát thiệp chính thức.
        </p>
    </header>

    {{-- Bảng Điều Khiển Điểm Sức Khỏe Tổng Quan (Score Cockpit) --}}
    <div class="mayhen_doctor_cockpit_card">
        <div class="mayhen_doctor_score_column">
            <div class="mayhen_doctor_score_ring mayhen_doctor_score_ring_{{ $kpis['badge_color'] }}">
                <span class="mayhen_doctor_score_number" id="doctorScore">{{ $kpis['health_score'] }}</span>
                <span class="mayhen_doctor_score_max">/100</span>
            </div>
            <div class="mayhen_doctor_grade_label mayhen_doctor_grade_{{ $kpis['badge_color'] }}" id="doctorGrade">
                {{ $kpis['grade'] }}
            </div>
            <div class="mayhen_doctor_score_desc">
                Điểm Sẵn Sàng Xuất Bản & Gửi Thiệp
            </div>
        </div>

        <div class="mayhen_doctor_kpis_column">
            <div class="mayhen_doctor_kpi_card mayhen_doctor_kpi_critical" data-filter="critical">
                <div class="mayhen_doctor_kpi_val" id="countCritical">{{ $kpis['critical_count'] }}</div>
                <div class="mayhen_doctor_kpi_lbl">🔴 Lỗi Nghiêm Trọng</div>
                <div class="mayhen_doctor_kpi_hint">Cần khắc phục ngay trước khi gửi</div>
            </div>

            <div class="mayhen_doctor_kpi_card mayhen_doctor_kpi_warning" data-filter="warning">
                <div class="mayhen_doctor_kpi_val" id="countWarning">{{ $kpis['warning_count'] }}</div>
                <div class="mayhen_doctor_kpi_lbl">🟡 Cảnh Báo Cần Lưu Ý</div>
                <div class="mayhen_doctor_kpi_hint">Nên bổ sung để thiệp trọn vẹn</div>
            </div>

            <div class="mayhen_doctor_kpi_card mayhen_doctor_kpi_suggestion" data-filter="suggestion">
                <div class="mayhen_doctor_kpi_val" id="countSuggestion">{{ $kpis['suggestion_count'] }}</div>
                <div class="mayhen_doctor_kpi_lbl">💡 Gợi Ý Tối Ưu</div>
                <div class="mayhen_doctor_kpi_hint">Nâng tầm trải nghiệm cảm xúc</div>
            </div>

            <div class="mayhen_doctor_kpi_card mayhen_doctor_kpi_passed" data-filter="pass">
                <div class="mayhen_doctor_kpi_val" id="countPassed">{{ $kpis['passed_count'] }}</div>
                <div class="mayhen_doctor_kpi_lbl">🟢 Đã Đạt Chuẩn</div>
                <div class="mayhen_doctor_kpi_hint">Hoàn hảo trên mọi thiết bị</div>
            </div>
        </div>
    </div>

    {{-- Khối Diễn Tập Trải Nghiệm (Simulation Rehearsal Box) --}}
    <div class="mayhen_doctor_rehearsal_card">
        <div class="mayhen_doctor_rehearsal_content">
            <div class="mayhen_doctor_rehearsal_icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                </svg>
            </div>
            <div>
                <h2 class="mayhen_doctor_rehearsal_heading">Chế Độ Diễn Tập Nghiệm Thu (VIP Rehearsal Simulation)</h2>
                <p class="mayhen_doctor_rehearsal_text">
                    Tự trải nghiệm thiệp cưới với tư cách một quan khách VIP: mở nắp phong bì, nghe nhạc nền, quét thử VietQR mừng cưới và phản hồi thử RSVP trước khi gửi hàng loạt cho gia đình, bạn bè.
                </p>
                <div class="mayhen_doctor_rehearsal_url_box">
                    <input type="text" id="rehearsalUrlInput" class="mayhen_doctor_rehearsal_input" readonly value="{{ $rehearsalUrl }}">
                    <button type="button" id="btnCopyRehearsal" class="mayhen_doctor_btn mayhen_doctor_btn_outline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>Sao Chép Link Diễn Tập</span>
                    </button>
                    <a href="{{ $rehearsalUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_doctor_btn mayhen_doctor_btn_primary" id="btnOpenRehearsal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        <span>Mở Trải Nghiệm Thử</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="mayhen_doctor_rehearsal_meta">
            <button type="button" id="btnCreateTestGuest" class="mayhen_doctor_rehearsal_subbtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                <span>Tạo Khách Diễn Tập Cá Nhân Hóa (VIP Test Code)</span>
            </button>
        </div>
    </div>

    {{-- Thanh Tab Bộ Lọc Danh Sách Kiểm Tra --}}
    <div class="mayhen_doctor_filter_bar">
        <div class="mayhen_doctor_filter_tabs">
            <button type="button" class="mayhen_doctor_tab active" data-tab="all">
                Tất Cả Tiêu Chuẩn (<span id="tabCountAll">{{ count($checks) }}</span>)
            </button>
            <button type="button" class="mayhen_doctor_tab" data-tab="critical">
                🔴 Lỗi Nghiêm Trọng (<span id="tabCountCritical">{{ $kpis['critical_count'] }}</span>)
            </button>
            <button type="button" class="mayhen_doctor_tab" data-tab="warning">
                🟡 Cảnh Báo (<span id="tabCountWarning">{{ $kpis['warning_count'] }}</span>)
            </button>
            <button type="button" class="mayhen_doctor_tab" data-tab="suggestion">
                💡 Gợi Ý Lễ Nghi (<span id="tabCountSuggestion">{{ $kpis['suggestion_count'] }}</span>)
            </button>
            <button type="button" class="mayhen_doctor_tab" data-tab="pass">
                🟢 Đạt Chuẩn (<span id="tabCountPass">{{ $kpis['passed_count'] }}</span>)
            </button>
        </div>
    </div>

    {{-- Danh sách các Hạng Mục Rà Soát (Checks List) --}}
    <div class="mayhen_doctor_checklist" id="checklistContainer">
        @foreach($checks as $chk)
            <div class="mayhen_doctor_item mayhen_doctor_item_{{ $chk['status'] }}" data-status="{{ $chk['status'] }}">
                <div class="mayhen_doctor_item_status_icon">
                    @if($chk['status'] === 'critical')
                        <span class="icon-badge icon-badge-critical" title="Lỗi nghiêm trọng">✕</span>
                    @elseif($chk['status'] === 'warning')
                        <span class="icon-badge icon-badge-warning" title="Cần lưu ý">!</span>
                    @elseif($chk['status'] === 'suggestion')
                        <span class="icon-badge icon-badge-suggestion" title="Gợi ý tối ưu">★</span>
                    @else
                        <span class="icon-badge icon-badge-pass" title="Đạt chuẩn">✓</span>
                    @endif
                </div>

                <div class="mayhen_doctor_item_body">
                    <div class="mayhen_doctor_item_top">
                        <span class="mayhen_doctor_section_tag">{{ $chk['section_name'] }}</span>
                        @if($chk['impact_score'] > 0)
                            <span class="mayhen_doctor_penalty_tag">-{{ $chk['impact_score'] }} điểm</span>
                        @else
                            <span class="mayhen_doctor_success_tag">Trọn vẹn 100%</span>
                        @endif
                    </div>
                    <h3 class="mayhen_doctor_item_title">{{ $chk['title'] }}</h3>
                    <p class="mayhen_doctor_item_msg">{{ $chk['message'] }}</p>
                    <div class="mayhen_doctor_item_suggestion">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>{{ $chk['suggestion'] }}</span>
                    </div>
                </div>

                <div class="mayhen_doctor_item_action">
                    @if($chk['status'] !== 'pass')
                        <a href="{{ $chk['action_url'] }}" class="mayhen_doctor_btn mayhen_doctor_btn_action">
                            <span>{{ $chk['action_label'] }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    @else
                        <a href="{{ $chk['action_url'] }}" class="mayhen_doctor_btn mayhen_doctor_btn_view">
                            <span>{{ $chk['action_label'] }}</span>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Toast thông báo Pure JS --}}
<div id="doctorToast" class="mayhen_doctor_toast" role="alert" aria-live="polite"></div>

<style>
/* ==========================================================================
   MAYHEN QUIET LUXURY QUALITY DOCTOR & REHEARSAL STYLES (PURE VANILLA CSS)
   ========================================================================== */
.mayhen_doctor_container {
    max-width: 1120px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: inherit;
    color: #2D2A26;
}

.mayhen_doctor_topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_doctor_back_link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    color: #7D756D;
    text-decoration: none;
    transition: color 0.2s ease;
}

.mayhen_doctor_back_link:hover {
    color: #9E7A52;
}

.mayhen_doctor_quick_actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mayhen_doctor_btn {
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

.mayhen_doctor_btn_primary {
    background: #9E7A52;
    color: #FFFFFF;
}

.mayhen_doctor_btn_primary:hover {
    background: #846541;
}

.mayhen_doctor_btn_outline {
    background: #FFFFFF;
    color: #4A443E;
    border: 1px solid #E6E1DA;
}

.mayhen_doctor_btn_outline:hover {
    border-color: #9E7A52;
    color: #9E7A52;
}

.spin-icon.spinning {
    animation: doctorSpin 1s linear infinite;
}

@keyframes doctorSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.mayhen_doctor_header {
    margin-bottom: 28px;
}

.mayhen_doctor_badge {
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

.mayhen_doctor_title {
    font-size: 1.85rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 8px 0;
    letter-spacing: -0.01em;
}

.mayhen_doctor_subtitle {
    font-size: 0.95rem;
    color: #7D756D;
    line-height: 1.6;
    margin: 0;
    max-width: 820px;
}

/* Bảng điểm tổng quan (Cockpit Card) */
.mayhen_doctor_cockpit_card {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.mayhen_doctor_score_column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-right: 1px solid #F0ECE6;
    padding-right: 24px;
}

.mayhen_doctor_score_ring {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: baseline;
    justify-content: center;
    margin-bottom: 14px;
    border: 5px solid #E6E1DA;
    padding-top: 36px;
    box-sizing: border-box;
}

.mayhen_doctor_score_ring_green {
    border-color: #2E7D32;
    background: #F1F8E9;
    color: #2E7D32;
}

.mayhen_doctor_score_ring_blue {
    border-color: #1976D2;
    background: #E3F2FD;
    color: #1976D2;
}

.mayhen_doctor_score_ring_amber {
    border-color: #F57C00;
    background: #FFF3E0;
    color: #F57C00;
}

.mayhen_doctor_score_ring_red {
    border-color: #D32F2F;
    background: #FFEBEE;
    color: #D32F2F;
}

.mayhen_doctor_score_number {
    font-size: 2.8rem;
    font-weight: 700;
    line-height: 1;
}

.mayhen_doctor_score_max {
    font-size: 0.9rem;
    font-weight: 500;
    opacity: 0.7;
    margin-left: 2px;
}

.mayhen_doctor_grade_label {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 4px;
    text-align: center;
}

.mayhen_doctor_grade_green { color: #2E7D32; }
.mayhen_doctor_grade_blue { color: #1976D2; }
.mayhen_doctor_grade_amber { color: #F57C00; }
.mayhen_doctor_grade_red { color: #D32F2F; }

.mayhen_doctor_score_desc {
    font-size: 0.8rem;
    color: #8C827A;
    text-align: center;
}

.mayhen_doctor_kpis_column {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    align-content: center;
}

.mayhen_doctor_kpi_card {
    background: #FAF8F5;
    border: 1px solid #ECE7E1;
    border-radius: 12px;
    padding: 16px 18px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mayhen_doctor_kpi_card:hover {
    border-color: #9E7A52;
    transform: translateY(-2px);
}

.mayhen_doctor_kpi_val {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 4px;
}

.mayhen_doctor_kpi_critical .mayhen_doctor_kpi_val { color: #D32F2F; }
.mayhen_doctor_kpi_warning .mayhen_doctor_kpi_val { color: #F57C00; }
.mayhen_doctor_kpi_suggestion .mayhen_doctor_kpi_val { color: #0288D1; }
.mayhen_doctor_kpi_passed .mayhen_doctor_kpi_val { color: #2E7D32; }

.mayhen_doctor_kpi_lbl {
    font-size: 0.85rem;
    font-weight: 600;
    color: #4A443E;
    margin-bottom: 4px;
}

.mayhen_doctor_kpi_hint {
    font-size: 0.75rem;
    color: #8C827A;
}

/* Khối Diễn Tập Rehearsal */
.mayhen_doctor_rehearsal_card {
    background: linear-gradient(135deg, #FAF7F2 0%, #F5EFE6 100%);
    border: 1px solid #E6DBCF;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 28px;
}

.mayhen_doctor_rehearsal_content {
    display: flex;
    gap: 18px;
    align-items: flex-start;
    margin-bottom: 16px;
}

.mayhen_doctor_rehearsal_icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #9E7A52;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.mayhen_doctor_rehearsal_heading {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 6px 0;
}

.mayhen_doctor_rehearsal_text {
    font-size: 0.88rem;
    color: #6A625A;
    line-height: 1.5;
    margin: 0 0 14px 0;
}

.mayhen_doctor_rehearsal_url_box {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.mayhen_doctor_rehearsal_input {
    flex: 1;
    min-width: 240px;
    background: #FFFFFF;
    border: 1px solid #D9CFB8;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 0.85rem;
    color: #4A443E;
}

.mayhen_doctor_rehearsal_meta {
    border-top: 1px dashed #DCCDBE;
    padding-top: 14px;
    display: flex;
    justify-content: flex-end;
}

.mayhen_doctor_rehearsal_subbtn {
    background: none;
    border: none;
    color: #9E7A52;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background 0.2s;
}

.mayhen_doctor_rehearsal_subbtn:hover {
    background: rgba(158, 122, 82, 0.08);
}

/* Tabs */
.mayhen_doctor_filter_bar {
    margin-bottom: 20px;
    border-bottom: 1px solid #ECE7E1;
}

.mayhen_doctor_filter_tabs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 10px;
}

.mayhen_doctor_tab {
    background: #F4EFEA;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #6A625A;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.mayhen_doctor_tab.active {
    background: #2D2A26;
    color: #FFFFFF;
}

/* Checklist Items */
.mayhen_doctor_checklist {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.mayhen_doctor_item {
    display: grid;
    grid-template-columns: 48px 1fr 160px;
    gap: 18px;
    align-items: center;
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 12px;
    padding: 18px 20px;
    transition: all 0.2s ease;
}

.mayhen_doctor_item:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.mayhen_doctor_item_critical {
    border-left: 4px solid #D32F2F;
}

.mayhen_doctor_item_warning {
    border-left: 4px solid #F57C00;
}

.mayhen_doctor_item_suggestion {
    border-left: 4px solid #0288D1;
}

.mayhen_doctor_item_pass {
    border-left: 4px solid #2E7D32;
    opacity: 0.9;
}

.icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
}

.icon-badge-critical { background: #FFEBEE; color: #D32F2F; }
.icon-badge-warning { background: #FFF3E0; color: #F57C00; }
.icon-badge-suggestion { background: #E1F5FE; color: #0288D1; }
.icon-badge-pass { background: #E8F5E9; color: #2E7D32; }

.mayhen_doctor_item_top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}

.mayhen_doctor_section_tag {
    font-size: 0.75rem;
    font-weight: 600;
    color: #8C827A;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.mayhen_doctor_penalty_tag {
    font-size: 0.72rem;
    font-weight: 600;
    background: #FFEBEE;
    color: #D32F2F;
    padding: 2px 8px;
    border-radius: 4px;
}

.mayhen_doctor_success_tag {
    font-size: 0.72rem;
    font-weight: 600;
    background: #E8F5E9;
    color: #2E7D32;
    padding: 2px 8px;
    border-radius: 4px;
}

.mayhen_doctor_item_title {
    font-size: 0.98rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 4px 0;
}

.mayhen_doctor_item_msg {
    font-size: 0.88rem;
    color: #6A625A;
    line-height: 1.45;
    margin: 0 0 6px 0;
}

.mayhen_doctor_item_suggestion {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    color: #9E7A52;
}

.mayhen_doctor_btn_action {
    background: #2D2A26;
    color: #FFFFFF;
    justify-content: center;
    width: 100%;
}

.mayhen_doctor_btn_action:hover {
    background: #9E7A52;
}

.mayhen_doctor_btn_view {
    background: #FAF8F5;
    border: 1px solid #E6E1DA;
    color: #6A625A;
    justify-content: center;
    width: 100%;
}

.mayhen_doctor_btn_view:hover {
    background: #F0ECE6;
    color: #2D2A26;
}

/* Toast Message */
.mayhen_doctor_toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #2D2A26;
    color: #FFFFFF;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 0.88rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.25s ease;
    pointer-events: none;
}

.mayhen_doctor_toast.show {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .mayhen_doctor_cockpit_card {
        grid-template-columns: 1fr;
    }
    .mayhen_doctor_score_column {
        border-right: none;
        border-bottom: 1px solid #F0ECE6;
        padding-right: 0;
        padding-bottom: 20px;
    }
    .mayhen_doctor_item {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .mayhen_doctor_item_action {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnRescan = document.getElementById('btnRescan');
    const btnCopyRehearsal = document.getElementById('btnCopyRehearsal');
    const btnCreateTestGuest = document.getElementById('btnCreateTestGuest');
    const rehearsalUrlInput = document.getElementById('rehearsalUrlInput');
    const btnOpenRehearsal = document.getElementById('btnOpenRehearsal');
    const tabs = document.querySelectorAll('.mayhen_doctor_tab');
    const kpiCards = document.querySelectorAll('.mayhen_doctor_kpi_card');
    const items = document.querySelectorAll('.mayhen_doctor_item');
    const toast = document.getElementById('doctorToast');

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2800);
    }

    // 1. Sao chép liên kết diễn tập
    if (btnCopyRehearsal && rehearsalUrlInput) {
        btnCopyRehearsal.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(rehearsalUrlInput.value);
                showToast('✓ Đã sao chép liên kết diễn tập vào bộ nhớ tạm!');
            } catch (err) {
                rehearsalUrlInput.select();
                document.execCommand('copy');
                showToast('✓ Đã sao chép liên kết diễn tập!');
            }
        });
    }

    // 2. Tạo khách mời diễn tập mẫu cá nhân hóa
    if (btnCreateTestGuest) {
        btnCreateTestGuest.addEventListener('click', async function () {
            btnCreateTestGuest.disabled = true;
            btnCreateTestGuest.innerHTML = '<span>Đang khởi tạo mã VIP Test...</span>';

            try {
                const res = await fetch("{{ route('account.quality_doctor.simulate', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data && data.success) {
                    if (rehearsalUrlInput) rehearsalUrlInput.value = data.rehearsal_url;
                    if (btnOpenRehearsal) btnOpenRehearsal.href = data.rehearsal_url;
                    showToast('✓ Đã tạo khách VIP Test: ' + (data.guest.name || 'Thành công'));
                } else {
                    showToast(data.message || 'Không thể tạo khách diễn tập.');
                }
            } catch (err) {
                showToast('Lỗi kết nối máy chủ.');
            } finally {
                btnCreateTestGuest.disabled = false;
                btnCreateTestGuest.innerHTML = '<span>Tạo Khách Diễn Tập Cá Nhân Hóa (VIP Test Code)</span>';
            }
        });
    }

    // 3. Quét lại tức thì (Rescan AJAX)
    if (btnRescan) {
        btnRescan.addEventListener('click', async function () {
            const icon = btnRescan.querySelector('.spin-icon');
            if (icon) icon.classList.add('spinning');
            btnRescan.disabled = true;

            try {
                const res = await fetch("{{ route('account.quality_doctor.rescan', $invitation->id) }}", {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data && data.success) {
                    showToast('✓ ' + data.message);
                    // Reload nhẹ để đồng bộ đầy đủ DOM hoặc cập nhật số liệu
                    window.location.reload();
                } else {
                    showToast('Quét thất bại.');
                }
            } catch (e) {
                showToast('Lỗi mạng khi quét lại.');
            } finally {
                if (icon) icon.classList.remove('spinning');
                btnRescan.disabled = false;
            }
        });
    }

    // 4. Lọc Tab
    function applyFilter(filter) {
        tabs.forEach(t => {
            if (t.dataset.tab === filter) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });

        items.forEach(it => {
            if (filter === 'all' || it.dataset.status === filter) {
                it.style.display = 'grid';
            } else {
                it.style.display = 'none';
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            applyFilter(this.dataset.tab);
        });
    });

    kpiCards.forEach(card => {
        card.addEventListener('click', function () {
            applyFilter(this.dataset.filter);
        });
    });
});
</script>
@endsection
