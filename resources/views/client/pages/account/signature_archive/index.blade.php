@extends('client.pages.account.layout')

@php
    $pageTitle = 'Kho Lưu Trữ Bút Tích Chữ Ký Tay Quan Khách — Mây Hẹn';
    $coupleName = ($wedding->groom_name ?? 'Chú Rể') . ' & ' . ($wedding->bride_name ?? 'Cô Dâu');
@endphp

@section('account_content')
<div class="mayhen_sig_studio">
    {{-- Thanh điều hướng quay lại --}}
    <div class="mayhen_sig_topbar">
        <a href="{{ route('account.invitations') }}" class="mayhen_sig_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Thiệp Cưới</span>
        </a>

        <div class="mayhen_sig_top_actions">
            <a href="{{ route('signature_archive.mosaic', $invitation->id) }}" target="_blank" class="mayhen_sig_btn mayhen_sig_btn_gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                <span>Mở Tranh Ký Tên Toàn Màn Hình</span>
            </a>
        </div>
    </div>

    {{-- Header giới thiệu --}}
    <header class="mayhen_sig_header">
        <div class="mayhen_sig_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                <path d="M2 2l7.586 7.586"></path>
                <circle cx="11" cy="11" r="2"></circle>
            </svg>
            <span>Handwritten Signature Canvas Archive & Keepsake Mosaic (IDEA-D09)</span>
        </div>
        <h1 class="mayhen_sig_title">Lưu Giữ Bút Tích Chữ Ký Tay Quan Khách</h1>
        <p class="mayhen_sig_subtitle">
            Bảo tồn trọn đời từng nét bút ký tay thiêng liêng của bạn bè và người thân khi mở thiệp cưới. Tổng hợp thành tác phẩm "Tranh Ký Tên Vàng" nghệ thuật trang trọng để trình chiếu tại sảnh cưới và lưu giữ hạnh phúc gia đình.
        </p>
    </header>

    {{-- 4 Thẻ KPI --}}
    <div class="mayhen_sig_kpi_grid">
        <div class="mayhen_sig_kpi_card">
            <div class="mayhen_sig_kpi_val" id="kpiTotal">{{ $kpis['total_signatures'] }}</div>
            <div class="mayhen_sig_kpi_lbl">Chữ Ký Đã Thu Thập</div>
            <div class="mayhen_sig_kpi_hint">{{ $kpis['with_message_count'] }} chữ ký kèm lời chúc</div>
        </div>

        <div class="mayhen_sig_kpi_card">
            <div class="mayhen_sig_kpi_val mayhen_sig_kpi_val_pin" id="kpiPinned">{{ $kpis['pinned_signatures'] }}</div>
            <div class="mayhen_sig_kpi_lbl">Bút Tích Trang Trọng (Ghim)</div>
            <div class="mayhen_sig_kpi_hint">Ưu tiên vị trí trung tâm sảnh</div>
        </div>

        <div class="mayhen_sig_kpi_card">
            <div class="mayhen_sig_kpi_val mayhen_sig_kpi_val_rate" id="kpiRate">{{ $kpis['guest_signature_rate'] }}%</div>
            <div class="mayhen_sig_kpi_lbl">Tỷ Lệ Quan Khách Đã Ký</div>
            <div class="mayhen_sig_kpi_hint">Trên tổng số khách mời đám cưới</div>
        </div>

        <div class="mayhen_sig_kpi_card">
            <div class="mayhen_sig_kpi_val mayhen_sig_kpi_val_recent" id="kpiRecent">
                {{ $kpis['recent_signature'] ? $kpis['recent_signature']['name'] : 'Chưa có' }}
            </div>
            <div class="mayhen_sig_kpi_lbl">Vừa Ký Gần Nhất</div>
            <div class="mayhen_sig_kpi_hint" id="kpiRecentTime">
                {{ $kpis['recent_signature'] ? $kpis['recent_signature']['time'] : 'Đang chờ khách gửi lời chúc' }}
            </div>
        </div>
    </div>

    {{-- Khu vực Ký Tên Cặp Đôi (Dâu & Rể) --}}
    <section class="mayhen_sig_couple_section">
        <div class="mayhen_sig_couple_head">
            <div>
                <h2>Bút Tích Cặp Đôi (Cô Dâu & Chú Rể)</h2>
                <p>Ký tên tay trực tiếp lên khung cảm ứng để đặt chữ ký trang trọng tại vị trí mở đầu bức tranh kỷ niệm</p>
            </div>
        </div>

        <form id="formCoupleSignatures" class="mayhen_sig_couple_grid">
            @csrf
            {{-- Khung ký Chú Rể --}}
            <div class="mayhen_sig_canvas_box">
                <div class="mayhen_sig_canvas_label">
                    <span class="mayhen_sig_canvas_icon">🤵</span>
                    <span>Chữ Ký Chú Rể ({{ $wedding->groom_name ?? 'Chú Rể' }})</span>
                </div>
                <div class="mayhen_sig_canvas_wrapper">
                    <canvas id="groomCanvas" class="mayhen_sig_canvas" width="400" height="180"></canvas>
                    @if(!empty($config['groom_signature_url']))
                        <div class="mayhen_sig_current_preview" id="groomPreview">
                            <img src="{{ $config['groom_signature_url'] }}" alt="Chữ ký chú rể">
                            <span class="mayhen_sig_current_tag">Đã lưu</span>
                        </div>
                    @endif
                </div>
                <div class="mayhen_sig_canvas_tools">
                    <button type="button" class="mayhen_sig_btn_text" id="btnClearGroom">Xóa vẽ lại</button>
                    <span class="mayhen_sig_canvas_hint">Dùng ngón tay hoặc chuột để ký</span>
                </div>
            </div>

            {{-- Khung ký Cô Dâu --}}
            <div class="mayhen_sig_canvas_box">
                <div class="mayhen_sig_canvas_label">
                    <span class="mayhen_sig_canvas_icon">👰</span>
                    <span>Chữ Ký Cô Dâu ({{ $wedding->bride_name ?? 'Cô Dâu' }})</span>
                </div>
                <div class="mayhen_sig_canvas_wrapper">
                    <canvas id="brideCanvas" class="mayhen_sig_canvas" width="400" height="180"></canvas>
                    @if(!empty($config['bride_signature_url']))
                        <div class="mayhen_sig_current_preview" id="bridePreview">
                            <img src="{{ $config['bride_signature_url'] }}" alt="Chữ ký cô dâu">
                            <span class="mayhen_sig_current_tag">Đã lưu</span>
                        </div>
                    @endif
                </div>
                <div class="mayhen_sig_canvas_tools">
                    <button type="button" class="mayhen_sig_btn_text" id="btnClearBride">Xóa vẽ lại</button>
                    <span class="mayhen_sig_canvas_hint">Dùng ngón tay hoặc chuột để ký</span>
                </div>
            </div>

            {{-- Cấu hình hiển thị Bức Tranh --}}
            <div class="mayhen_sig_settings_box">
                <div class="mayhen_sig_form_group">
                    <label for="mosaicTitle">Tiêu Đề Bức Tranh Ký Tên</label>
                    <input type="text" id="mosaicTitle" name="mosaic_title" value="{{ $config['mosaic_title'] }}" class="mayhen_sig_input" placeholder="Ví dụ: Bức Tranh Ký Tên Vàng — Tri Ân Quan Khách">
                </div>

                <div class="mayhen_sig_form_group">
                    <label for="mosaicQuote">Lời Đề Tựa Đám Cưới</label>
                    <textarea id="mosaicQuote" name="mosaic_quote" rows="2" class="mayhen_sig_textarea" placeholder="Lời cảm ơn hoặc thông điệp trang trọng gửi gắm tới quan khách">{{ $config['mosaic_quote'] }}</textarea>
                </div>

                <div class="mayhen_sig_form_group">
                    <label for="mosaicTheme">Chủ Đề Ánh Sắc (Theme Canvas)</label>
                    <select id="mosaicTheme" name="mosaic_theme" class="mayhen_sig_select">
                        <option value="cream_gold" {{ $config['mosaic_theme'] === 'cream_gold' ? 'selected' : '' }}>Kem Ấm &amp; Vàng Kim Hoàng Gia (Cream &amp; Gold)</option>
                        <option value="ivory_charcoal" {{ $config['mosaic_theme'] === 'ivory_charcoal' ? 'selected' : '' }}>Giấy Mỹ Thuật Ngà &amp; Mực Than (Ivory &amp; Charcoal)</option>
                        <option value="obsidian_night" {{ $config['mosaic_theme'] === 'obsidian_night' ? 'selected' : '' }}>Đêm Huyền Bí Ánh Bạc (Obsidian Night)</option>
                        <option value="dusty_rose" {{ $config['mosaic_theme'] === 'dusty_rose' ? 'selected' : '' }}>Hồng Tro &amp; Rượu Vang Lãng Mạn (Dusty Rose)</option>
                    </select>
                </div>

                <div class="mayhen_sig_save_row">
                    <button type="submit" id="btnSaveCouple" class="mayhen_sig_btn mayhen_sig_btn_primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Lưu Bút Tích &amp; Cài Đặt Tranh</span>
                    </button>
                    <span id="saveStatus" class="mayhen_sig_status_msg"></span>
                </div>
            </div>
        </form>
    </section>

    {{-- Danh Sách Chữ Ký Quan Khách --}}
    <section class="mayhen_sig_gallery_section">
        <div class="mayhen_sig_gallery_head">
            <div>
                <h2>Bộ Sưu Tập Bút Tích Quan Khách ({{ count($signatures) }})</h2>
                <p>Từng chữ ký được lưu độc bản dưới dạng vector và ảnh nét cao</p>
            </div>

            <div class="mayhen_sig_filter_tabs">
                <button type="button" class="mayhen_sig_tab active" data-filter="all">Tất cả ({{ count($signatures) }})</button>
                <button type="button" class="mayhen_sig_tab" data-filter="pinned">Đã ghim ({{ $kpis['pinned_signatures'] }})</button>
                <button type="button" class="mayhen_sig_tab" data-filter="with_msg">Có lời chúc ({{ $kpis['with_message_count'] }})</button>
            </div>
        </div>

        @if(count($signatures) > 0)
            <div class="mayhen_sig_cards_grid" id="signaturesGrid">
                @foreach($signatures as $sig)
                    <div class="mayhen_sig_card {{ $sig['is_pinned'] ? 'is_pinned' : '' }}" 
                         data-id="{{ $sig['id'] }}"
                         data-has-msg="{{ !empty($sig['message']) ? '1' : '0' }}"
                         data-is-pinned="{{ $sig['is_pinned'] ? '1' : '0' }}">
                        <div class="mayhen_sig_card_top">
                            <span class="mayhen_sig_guest_badge">{{ $sig['guest_group'] }}</span>
                            <div class="mayhen_sig_card_actions">
                                <button type="button" class="mayhen_sig_action_btn btn-toggle-pin {{ $sig['is_pinned'] ? 'active' : '' }}" 
                                        title="{{ $sig['is_pinned'] ? 'Bỏ ghim trang trọng' : 'Ghim chữ ký trang trọng' }}"
                                        data-url="{{ route('account.signature_archive.toggle_pin', ['id' => $invitation->id, 'wishId' => $sig['id']]) }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $sig['is_pinned'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                </button>
                                <button type="button" class="mayhen_sig_action_btn btn-remove-sig" 
                                        title="Gỡ bỏ chữ ký này"
                                        data-url="{{ route('account.signature_archive.destroy', ['id' => $invitation->id, 'wishId' => $sig['id']]) }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Ảnh chữ ký --}}
                        <div class="mayhen_sig_card_canvas">
                            <img src="{{ $sig['signature_url'] }}" alt="Chữ ký của {{ $sig['name'] }}" loading="lazy">
                        </div>

                        {{-- Thông tin người ký --}}
                        <div class="mayhen_sig_card_footer">
                            <div class="mayhen_sig_guest_name">{{ $sig['name'] }}</div>
                            <div class="mayhen_sig_guest_meta">{{ $sig['time_ago'] }} &bull; {{ $sig['created_at_formatted'] }}</div>
                            @if(!empty($sig['message']))
                                <div class="mayhen_sig_guest_msg">"{{ $sig['message'] }}"</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mayhen_sig_empty">
                <div class="mayhen_sig_empty_icon">✍️</div>
                <h3>Chưa Có Khách Mời Nào Ký Tên</h3>
                <p>Khi quan khách mở thiệp cưới điện tử hoặc quét mã tại tiệc cưới, họ có thể ký tên vào sổ lưu bút trực tuyến. Chữ ký tay sẽ hiển thị tức thì tại đây!</p>
            </div>
        @endif
    </section>
</div>

<style>
/* CSS Định dạng BEM mayhen_sig_* Chuẩn Mây Hẹn Quiet Luxury */
.mayhen_sig_studio {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #2c2824;
}

.mayhen_sig_topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_sig_back_link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #8c7355;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.2s ease;
}

.mayhen_sig_back_link:hover {
    color: #5c4b37;
}

.mayhen_sig_header {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.mayhen_sig_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fbf9f5;
    border: 1px solid #e2d7c5;
    color: #9c7a4a;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 16px;
}

.mayhen_sig_title {
    font-size: 28px;
    font-weight: 700;
    color: #1f1d1a;
    margin: 0 0 10px;
    letter-spacing: -0.01em;
}

.mayhen_sig_subtitle {
    font-size: 15px;
    line-height: 1.6;
    color: #6a645a;
    margin: 0;
    max-width: 800px;
}

/* 4 KPI Cards */
.mayhen_sig_kpi_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.mayhen_sig_kpi_card {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.mayhen_sig_kpi_val {
    font-size: 32px;
    font-weight: 700;
    color: #1f1d1a;
    line-height: 1.1;
    margin-bottom: 6px;
    font-variant-numeric: tabular-nums;
}

.mayhen_sig_kpi_val_pin { color: #b8935d; }
.mayhen_sig_kpi_val_rate { color: #2e7d32; }
.mayhen_sig_kpi_val_recent { font-size: 20px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.mayhen_sig_kpi_lbl {
    font-size: 13.5px;
    font-weight: 600;
    color: #4a453e;
    margin-bottom: 4px;
}

.mayhen_sig_kpi_hint {
    font-size: 12px;
    color: #8c8376;
}

/* Section Ký Tên Cặp Đôi */
.mayhen_sig_couple_section {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 32px;
}

.mayhen_sig_couple_head h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1f1d1a;
    margin: 0 0 6px;
}

.mayhen_sig_couple_head p {
    font-size: 14px;
    color: #6a645a;
    margin: 0 0 24px;
}

.mayhen_sig_couple_grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}

.mayhen_sig_canvas_box {
    background: #faf8f5;
    border: 1px solid #e8decb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
}

.mayhen_sig_canvas_label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #2c2824;
    margin-bottom: 12px;
}

.mayhen_sig_canvas_wrapper {
    position: relative;
    background: #ffffff;
    border: 1px dashed #d8cebd;
    border-radius: 8px;
    overflow: hidden;
    height: 160px;
    touch-action: none;
}

.mayhen_sig_canvas {
    width: 100%;
    height: 100%;
    cursor: crosshair;
    display: block;
}

.mayhen_sig_current_preview {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.mayhen_sig_current_preview img {
    max-width: 90%;
    max-height: 80%;
    object-fit: contain;
}

.mayhen_sig_current_tag {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: #eef7ee;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
}

.mayhen_sig_canvas_tools {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.mayhen_sig_btn_text {
    background: none;
    border: none;
    color: #b8935d;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.mayhen_sig_btn_text:hover { text-decoration: underline; }

.mayhen_sig_canvas_hint {
    font-size: 11.5px;
    color: #9c9488;
}

/* Cài đặt Form */
.mayhen_sig_settings_box {
    background: #faf8f5;
    border: 1px solid #e8decb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.mayhen_sig_form_group label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #4a453e;
    margin-bottom: 6px;
}

.mayhen_sig_input, .mayhen_sig_textarea, .mayhen_sig_select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d8cebd;
    border-radius: 6px;
    font-size: 13.5px;
    background: #ffffff;
    color: #2c2824;
    box-sizing: border-box;
}

.mayhen_sig_input:focus, .mayhen_sig_textarea:focus, .mayhen_sig_select:focus {
    outline: none;
    border-color: #b8935d;
    box-shadow: 0 0 0 3px rgba(184, 147, 93, 0.15);
}

.mayhen_sig_save_row {
    margin-top: auto;
    display: flex;
    align-items: center;
    gap: 12px;
}

.mayhen_sig_status_msg {
    font-size: 12px;
    font-weight: 500;
}

/* Gallery Danh Sách Chữ Ký */
.mayhen_sig_gallery_section {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 28px;
}

.mayhen_sig_gallery_head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_sig_gallery_head h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1f1d1a;
    margin: 0 0 6px;
}

.mayhen_sig_gallery_head p {
    font-size: 14px;
    color: #6a645a;
    margin: 0;
}

.mayhen_sig_filter_tabs {
    display: flex;
    gap: 8px;
    background: #fbf9f5;
    padding: 4px;
    border-radius: 8px;
    border: 1px solid #efe8dd;
}

.mayhen_sig_tab {
    background: none;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #6a645a;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mayhen_sig_tab.active {
    background: #ffffff;
    color: #1f1d1a;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* Grid Cards Chữ Ký */
.mayhen_sig_cards_grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
}

.mayhen_sig_card {
    background: #faf8f5;
    border: 1px solid #efe8dd;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    transition: all 0.2s ease;
    position: relative;
}

.mayhen_sig_card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
    border-color: #d8cebd;
}

.mayhen_sig_card.is_pinned {
    border-color: #b8935d;
    background: #fcfbf8;
    box-shadow: 0 4px 16px rgba(184, 147, 93, 0.12);
}

.mayhen_sig_card_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.mayhen_sig_guest_badge {
    background: #efe8dd;
    color: #5c5244;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
}

.mayhen_sig_card_actions {
    display: flex;
    gap: 4px;
}

.mayhen_sig_action_btn {
    background: #ffffff;
    border: 1px solid #d8cebd;
    border-radius: 6px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6a645a;
    transition: all 0.15s ease;
}

.mayhen_sig_action_btn:hover {
    color: #1f1d1a;
    border-color: #b8935d;
}

.mayhen_sig_action_btn.btn-toggle-pin.active {
    color: #b8935d;
    background: #fff8eb;
    border-color: #e5c285;
}

.mayhen_sig_card_canvas {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 8px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    margin-bottom: 14px;
}

.mayhen_sig_card_canvas img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
}

.mayhen_sig_guest_name {
    font-size: 15px;
    font-weight: 700;
    color: #1f1d1a;
    margin-bottom: 4px;
}

.mayhen_sig_guest_meta {
    font-size: 12px;
    color: #8c8376;
    margin-bottom: 8px;
}

.mayhen_sig_guest_msg {
    font-size: 13px;
    font-style: italic;
    color: #4a453e;
    line-height: 1.4;
    background: #ffffff;
    border-left: 2px solid #b8935d;
    padding: 6px 10px;
    border-radius: 0 4px 4px 0;
}

/* Empty State */
.mayhen_sig_empty {
    text-align: center;
    padding: 60px 20px;
}

.mayhen_sig_empty_icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.mayhen_sig_empty h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f1d1a;
    margin: 0 0 8px;
}

.mayhen_sig_empty p {
    font-size: 14px;
    color: #6a645a;
    max-width: 500px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Nút bấm chung */
.mayhen_sig_btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}

.mayhen_sig_btn_gold {
    background: #b8935d;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(184, 147, 93, 0.25);
}

.mayhen_sig_btn_gold:hover {
    background: #a37f4a;
    color: #ffffff;
}

.mayhen_sig_btn_primary {
    background: #1f1d1a;
    color: #ffffff;
}

.mayhen_sig_btn_primary:hover {
    background: #332f2b;
}

@media (max-width: 900px) {
    .mayhen_sig_kpi_grid { grid-template-columns: repeat(2, 1fr); }
    .mayhen_sig_couple_grid { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
    .mayhen_sig_kpi_grid { grid-template-columns: 1fr; }
    .mayhen_sig_studio { padding: 16px 12px 60px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Quản lý Canvas Ký Tên Dâu & Rể
    function setupCanvas(canvasId, clearBtnId, previewId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let hasDrawn = false;

        // Cấu hình nét mực ký
        ctx.strokeStyle = '#1f1d1a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            // Ẩn preview cũ nếu có
            const prev = document.getElementById(previewId);
            if (prev) prev.style.display = 'none';
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDrawing);

        const clearBtn = document.getElementById(clearBtnId);
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
                const prev = document.getElementById(previewId);
                if (prev) prev.style.display = 'none';
            });
        }

        return {
            hasDrawn: () => hasDrawn,
            getDataURL: () => hasDrawn ? canvas.toDataURL('image/png') : null
        };
    }

    const groomTool = setupCanvas('groomCanvas', 'btnClearGroom', 'groomPreview');
    const brideTool = setupCanvas('brideCanvas', 'btnClearBride', 'bridePreview');

    // 2. Lưu chữ ký Cặp đôi & Cài đặt
    const coupleForm = document.getElementById('formCoupleSignatures');
    if (coupleForm) {
        coupleForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btnSave = document.getElementById('btnSaveCouple');
            const statusMsg = document.getElementById('saveStatus');
            btnSave.disabled = true;
            statusMsg.textContent = 'Đang lưu bút tích...';
            statusMsg.style.color = '#8c7355';

            const payload = {
                mosaic_title: document.getElementById('mosaicTitle').value,
                mosaic_quote: document.getElementById('mosaicQuote').value,
                mosaic_theme: document.getElementById('mosaicTheme').value,
            };

            if (groomTool && groomTool.hasDrawn()) {
                payload.groom_signature_data = groomTool.getDataURL();
            }
            if (brideTool && brideTool.hasDrawn()) {
                payload.bride_signature_data = brideTool.getDataURL();
            }

            try {
                const res = await fetch("{{ route('account.signature_archive.update_couple', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    statusMsg.textContent = '✓ ' + data.message;
                    statusMsg.style.color = '#2e7d32';
                    setTimeout(() => { statusMsg.textContent = ''; }, 4000);
                } else {
                    statusMsg.textContent = 'Lỗi: ' + (data.message || 'Không thể lưu');
                    statusMsg.style.color = '#c62828';
                }
            } catch (err) {
                statusMsg.textContent = 'Lỗi kết nối máy chủ';
                statusMsg.style.color = '#c62828';
            } finally {
                btnSave.disabled = false;
            }
        });
    }

    // 3. Ghim chữ ký & Gỡ chữ ký
    document.addEventListener('click', async function (e) {
        // Ghim chữ ký
        const pinBtn = e.target.closest('.btn-toggle-pin');
        if (pinBtn) {
            const url = pinBtn.getAttribute('data-url');
            pinBtn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const card = pinBtn.closest('.mayhen_sig_card');
                    if (data.is_pinned) {
                        pinBtn.classList.add('active');
                        card.classList.add('is_pinned');
                        card.setAttribute('data-is-pinned', '1');
                    } else {
                        pinBtn.classList.remove('active');
                        card.classList.remove('is_pinned');
                        card.setAttribute('data-is-pinned', '0');
                    }
                }
            } catch (err) {
                alert('Không thể cập nhật trạng thái ghim');
            } finally {
                pinBtn.disabled = false;
            }
            return;
        }

        // Gỡ bỏ chữ ký
        const removeBtn = e.target.closest('.btn-remove-sig');
        if (removeBtn) {
            if (!confirm('Bạn có chắc chắn muốn gỡ bỏ chữ ký này khỏi kho lưu trữ? (Lời chúc của khách vẫn được giữ lại nếu có)')) {
                return;
            }
            const url = removeBtn.getAttribute('data-url');
            removeBtn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const card = removeBtn.closest('.mayhen_sig_card');
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 250);
                }
            } catch (err) {
                alert('Không thể gỡ bỏ chữ ký');
                removeBtn.disabled = false;
            }
        }
    });

    // 4. Lọc Thẻ Gallery
    const filterTabs = document.querySelectorAll('.mayhen_sig_tab');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            const cards = document.querySelectorAll('.mayhen_sig_card');
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'flex';
                } else if (filter === 'pinned') {
                    card.style.display = card.getAttribute('data-is-pinned') === '1' ? 'flex' : 'none';
                } else if (filter === 'with_msg') {
                    card.style.display = card.getAttribute('data-has-msg') === '1' ? 'flex' : 'none';
                }
            });
        });
    });
});
</script>
@endsection

