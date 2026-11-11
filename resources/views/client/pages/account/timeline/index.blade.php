@extends('client.pages.account.layout')

@php
    $pageTitle = 'Lịch Trình Hôn Lễ (Timeline) & Thêm Vào Lịch — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Lịch Trình Hôn Lễ')

@section('account_content')
<div class="mayhen_timeline_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_timeline_admin_header">
        <div class="mayhen_timeline_admin_headline">
            <div class="mayhen_timeline_admin_badge">Interactive Day Timeline &amp; Calendar Suite</div>
            <h1 class="mayhen_timeline_admin_title">Lịch Trình Hôn Lễ &amp; Thêm Vào Lịch Một Chạm</h1>
            <p class="mayhen_timeline_admin_subtitle">
                Thiết lập thời gian biểu chi tiết các nghi thức và hoạt động trong ngày trọng đại của <strong>{{ $coupleName }}</strong>, đồng thời cung cấp tính năng thêm vào lịch (Google Calendar, Apple Calendar, Outlook) tự động nhắc nhở khách mời chu đáo.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_timeline_alert mayhen_timeline_alert--success">
            <span class="mayhen_timeline_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_timeline_alert mayhen_timeline_alert--danger">
            <span class="mayhen_timeline_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('account.timeline.update', $invitation->id) }}" method="POST" id="timelineForm" class="mayhen_timeline_form">
        @csrf

        {{-- Section 1: Chọn Kịch Bản Mẫu Sẵn --}}
        <div class="mayhen_timeline_card">
            <div class="mayhen_timeline_card_header">
                <div class="mayhen_timeline_card_icon">📋</div>
                <div class="mayhen_timeline_card_title_row">
                    <div>
                        <h2 class="mayhen_timeline_card_title">1. Chọn Kịch Bản Mẫu Ngày Cưới (Preset Scenarios)</h2>
                        <p class="mayhen_timeline_card_desc">Nhấp chọn kịch bản phù hợp nhất với kế hoạch của bạn để tự động điền các mốc sự kiện chuẩn mực.</p>
                    </div>
                    <label class="mayhen_timeline_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($timelineConfig['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_timeline_slider"></span>
                        <span class="mayhen_timeline_switch_text">Hiển thị trên thiệp</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_timeline_presets_grid">
                @foreach($presets as $key => $preset)
                    <div class="mayhen_scenario_card {{ ($timelineConfig['scenario_key'] ?? '') === $key ? 'is-selected' : '' }}"
                         onclick="selectScenario('{{ $key }}', {{ json_encode($preset['items']) }})">
                        <div class="mayhen_scenario_card_top">
                            <input type="radio" name="scenario_key" value="{{ $key }}" id="sc_{{ $key }}"
                                   {{ ($timelineConfig['scenario_key'] ?? '') === $key ? 'checked' : '' }}>
                            <label for="sc_{{ $key }}" class="mayhen_scenario_name">{{ $preset['name'] }}</label>
                        </div>
                        <p class="mayhen_scenario_desc">{{ $preset['description'] }}</p>
                        <div class="mayhen_scenario_pills">
                            @foreach($preset['items'] as $item)
                                <span class="mayhen_scenario_pill">{{ $item['time'] }} — {{ Str::limit($item['title'], 20) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 2: Quản Lý Các Mốc Thời Gian Trong Ngày --}}
        <div class="mayhen_timeline_card">
            <div class="mayhen_timeline_card_header">
                <div class="mayhen_timeline_card_icon">⏱️</div>
                <div>
                    <h2 class="mayhen_timeline_card_title">2. Danh Sách Các Mốc Thời Gian &amp; Hoạt Động Chi Tiết</h2>
                    <p class="mayhen_timeline_card_desc">Tùy biến giờ giấc, chọn biểu tượng thời khắc, địa điểm sảnh và ghi chú cho từng sự kiện.</p>
                </div>
            </div>

            <div class="mayhen_timeline_toolbar">
                <span class="mayhen_timeline_count">Hiện có <strong id="timelineItemCount">{{ count($timelineConfig['items']) }}</strong> mốc sự kiện</span>
                <button type="button" class="mayhen_btn_add_item" id="addTimelineItemBtn">+ Thêm mốc thời gian mới</button>
            </div>

            <div id="timelineItemsContainer" class="mayhen_timeline_items_list">
                @foreach($timelineConfig['items'] as $idx => $item)
                    <div class="mayhen_timeline_item_row" data-index="{{ $idx }}">
                        <div class="mayhen_item_top">
                            <span class="mayhen_item_order_tag">Mốc sự kiện #{{ $idx + 1 }}</span>
                            <button type="button" class="mayhen_btn_del_item" onclick="removeTimelineItem(this)">✕ Xóa mốc này</button>
                        </div>

                        <div class="mayhen_item_grid_top">
                            <div class="mayhen_form_field mayhen_form_field--time">
                                <label class="mayhen_label">Thời gian *</label>
                                <input type="text" name="items[{{ $idx }}][time]" class="mayhen_input"
                                       placeholder="Ví dụ: 17:30" value="{{ $item['time'] }}" required>
                            </div>

                            <div class="mayhen_form_field mayhen_form_field--icon">
                                <label class="mayhen_label">Biểu tượng</label>
                                <select name="items[{{ $idx }}][icon]" class="mayhen_select">
                                    @foreach($availableIcons as $iKey => $icon)
                                        <option value="{{ $iKey }}" {{ ($item['icon'] ?? '') === $iKey ? 'selected' : '' }}>
                                            {{ $icon['icon'] }} {{ $icon['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mayhen_form_field mayhen_form_field--title">
                                <label class="mayhen_label">Tiêu đề hoạt động *</label>
                                <input type="text" name="items[{{ $idx }}][title]" class="mayhen_input"
                                       placeholder="Ví dụ: Đón Tiếp Quan Khách & Chụp Ảnh" value="{{ $item['title'] }}" required>
                            </div>
                        </div>

                        <div class="mayhen_item_grid_bottom">
                            <div class="mayhen_form_field">
                                <label class="mayhen_label">Sảnh / Vị trí tổ chức</label>
                                <input type="text" name="items[{{ $idx }}][location]" class="mayhen_input"
                                       placeholder="Ví dụ: Sảnh Grand Ballroom (Tầng 2)" value="{{ $item['location'] ?? '' }}">
                            </div>

                            <div class="mayhen_form_field">
                                <label class="mayhen_label">Mô tả &amp; Lưu ý ngắn</label>
                                <input type="text" name="items[{{ $idx }}][description]" class="mayhen_input"
                                       placeholder="Ví dụ: Thưởng thức cocktail và ký tên lưu bút" value="{{ $item['description'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 3: Tiêu Đề & Lời Nhắc Lịch (Calendar Reminder) --}}
        <div class="mayhen_timeline_card">
            <div class="mayhen_timeline_card_header">
                <div class="mayhen_timeline_card_icon">🔔</div>
                <div>
                    <h2 class="mayhen_timeline_card_title">3. Tùy Chọn Lời Nhắc Lịch &amp; Tiêu Đề Hiển Thị</h2>
                    <p class="mayhen_timeline_card_desc">Thiết lập thời gian thông báo trước giờ cử hành hôn lễ trên ứng dụng Lịch của khách mời.</p>
                </div>
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="title">Tiêu đề mục Lịch Trình trên thiệp</label>
                    <input type="text" id="title" name="title" class="mayhen_input"
                           value="{{ old('title', $timelineConfig['title']) }}" required>
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="calendar_reminder_hours">Báo thức nhắc nhở trước giờ khai tiệc</label>
                    <select name="calendar_reminder_hours" id="calendar_reminder_hours" class="mayhen_select">
                        <option value="1" {{ ($timelineConfig['calendar_reminder_hours'] ?? 2) === 1 ? 'selected' : '' }}>Nhắc trước 1 giờ</option>
                        <option value="2" {{ ($timelineConfig['calendar_reminder_hours'] ?? 2) === 2 ? 'selected' : '' }}>Nhắc trước 2 giờ (Khuyến nghị)</option>
                        <option value="3" {{ ($timelineConfig['calendar_reminder_hours'] ?? 2) === 3 ? 'selected' : '' }}>Nhắc trước 3 giờ</option>
                        <option value="5" {{ ($timelineConfig['calendar_reminder_hours'] ?? 2) === 5 ? 'selected' : '' }}>Nhắc trước 5 giờ</option>
                    </select>
                </div>
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label" for="subtitle">Phụ đề truyền cảm hứng</label>
                <input type="text" id="subtitle" name="subtitle" class="mayhen_input"
                       value="{{ old('subtitle', $timelineConfig['subtitle']) }}">
            </div>
        </div>

        {{-- Thanh Nút Thao Tác Lưu Cài Đặt --}}
        <div class="mayhen_timeline_actions">
            <a href="{{ route('account.invitations.settings', $invitation->id) }}" class="mayhen_timeline_btn_back">← Quay lại cài đặt thiệp</a>
            <div class="mayhen_actions_right">
                <a href="{{ route('account.timeline.download_ics', $invitation->id) }}" class="mayhen_btn_test_ics" title="Tải thử tệp .ics để thêm vào Apple Calendar / Outlook">
                    📥 Tải thử file lịch (.ics)
                </a>
                <button type="submit" class="mayhen_timeline_btn_primary">Lưu Lịch Trình Ngày Cưới</button>
            </div>
        </div>
    </form>

    {{-- Live Preview Section 7 --}}
    <div class="mayhen_timeline_preview_section">
        <div class="mayhen_timeline_preview_header">
            <h3 class="mayhen_preview_title">Xem Thử Trực Quan Section Lịch Trình Trên Thiệp Cưới</h3>
            <span class="mayhen_preview_sub">Mô phỏng hiển thị trục dọc thời gian và nút Thêm Vào Lịch một chạm</span>
        </div>
        <div class="mayhen_timeline_preview_canvas">
            @include('client.components.wedding-timeline', ['invitation' => $invitation])
        </div>
    </div>
</div>

<style>
/* CSS Độc Bản Phân Hệ Wedding Timeline Studio — Quiet Luxury */
.mayhen_timeline_admin_container {
    max-width: 1040px;
    margin: 0 auto;
    padding-bottom: 60px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #2C2824;
}
.mayhen_timeline_admin_header {
    margin-bottom: 24px;
}
.mayhen_timeline_admin_badge {
    display: inline-block;
    padding: 4px 12px;
    background: #EFE8DD;
    color: #9C7A4A;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.mayhen_timeline_admin_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_timeline_admin_subtitle {
    font-size: 14.5px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0;
}
.mayhen_timeline_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}
.mayhen_timeline_alert--success {
    background: #EBF7EE;
    color: #1E6B38;
    border: 1px solid #C3E6CB;
}
.mayhen_timeline_alert--danger {
    background: #FDF2F2;
    color: #9E1C1C;
    border: 1px solid #F8D7DA;
}

/* Card */
.mayhen_timeline_card {
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #E5DCCD;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.mayhen_timeline_card_header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}
.mayhen_timeline_card_icon {
    font-size: 26px;
    line-height: 1;
}
.mayhen_timeline_card_title_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_timeline_card_title {
    font-size: 18px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_timeline_card_desc {
    font-size: 13.5px;
    color: #6C665C;
    margin: 0;
}

/* Switch */
.mayhen_timeline_switch {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.mayhen_timeline_switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.mayhen_timeline_slider {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background-color: #D8CEBD;
    border-radius: 24px;
    transition: 0.3s;
}
.mayhen_timeline_slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}
.mayhen_timeline_switch input:checked + .mayhen_timeline_slider {
    background-color: #9C7A4A;
}
.mayhen_timeline_switch input:checked + .mayhen_timeline_slider:before {
    transform: translateX(20px);
}
.mayhen_timeline_switch_text {
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
}

/* Presets Grid */
.mayhen_timeline_presets_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 16px;
    margin-top: 14px;
}
.mayhen_scenario_card {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_scenario_card:hover {
    border-color: #C5A880;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.mayhen_scenario_card.is-selected {
    border-color: #9C7A4A;
    background: #FFFFFF;
    box-shadow: 0 0 0 2px #9C7A4A;
}
.mayhen_scenario_card_top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.mayhen_scenario_card_top input[type="radio"] {
    accent-color: #9C7A4A;
    cursor: pointer;
}
.mayhen_scenario_name {
    font-size: 14px;
    font-weight: 700;
    color: #1F1D1A;
    cursor: pointer;
}
.mayhen_scenario_desc {
    font-size: 12.5px;
    color: #6C665C;
    margin: 0 0 12px 0;
    line-height: 1.4;
}
.mayhen_scenario_pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.mayhen_scenario_pill {
    font-size: 11px;
    background: #EFE8DD;
    color: #7A7265;
    padding: 2px 8px;
    border-radius: 6px;
}

/* Toolbar & Items */
.mayhen_timeline_toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.mayhen_timeline_count {
    font-size: 13.5px;
    color: #6C665C;
}
.mayhen_btn_add_item {
    background: transparent;
    border: 1px solid #9C7A4A;
    color: #9C7A4A;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.mayhen_btn_add_item:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}

.mayhen_timeline_items_list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.mayhen_timeline_item_row {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    padding: 16px 18px;
    border-radius: 10px;
    transition: border-color 0.2s;
}
.mayhen_timeline_item_row:hover {
    border-color: #D8CEBD;
}
.mayhen_item_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.mayhen_item_order_tag {
    font-size: 12px;
    font-weight: 700;
    color: #9C7A4A;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.mayhen_btn_del_item {
    background: transparent;
    border: none;
    color: #DC3545;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}
.mayhen_btn_del_item:hover {
    text-decoration: underline;
}

.mayhen_item_grid_top {
    display: grid;
    grid-template-columns: 120px 220px 1fr;
    gap: 12px;
    margin-bottom: 10px;
}
.mayhen_item_grid_bottom {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 12px;
}

/* Form inputs */
.mayhen_form_grid_2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.mayhen_form_field {
    margin-bottom: 12px;
}
.mayhen_label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #38342E;
    margin-bottom: 5px;
}
.mayhen_input, .mayhen_select {
    width: 100%;
    box-sizing: border-box;
    padding: 9px 12px;
    font-size: 13.5px;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    background: #FFFFFF;
    color: #1F1D1A;
    transition: border-color 0.2s;
}
.mayhen_input:focus, .mayhen_select:focus {
    outline: none;
    border-color: #9C7A4A;
}

/* Actions */
.mayhen_timeline_actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    gap: 16px;
    flex-wrap: wrap;
}
.mayhen_timeline_btn_back {
    color: #6C665C;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}
.mayhen_actions_right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.mayhen_btn_test_ics {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FAF8F5;
    border: 1px solid #C5A880;
    color: #9C7A4A;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.mayhen_btn_test_ics:hover {
    background: #EFE8DD;
}
.mayhen_timeline_btn_primary {
    background: #9C7A4A;
    color: #FFFFFF;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(156,122,74,0.25);
    transition: background 0.2s;
}
.mayhen_timeline_btn_primary:hover {
    background: #846538;
}

/* Preview Canvas */
.mayhen_timeline_preview_section {
    margin-top: 48px;
    padding-top: 36px;
    border-top: 1px solid #E5DCCD;
}
.mayhen_timeline_preview_header {
    text-align: center;
    margin-bottom: 24px;
}
.mayhen_preview_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 22px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_preview_sub {
    font-size: 13.5px;
    color: #6C665C;
}
.mayhen_timeline_preview_canvas {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #E5DCCD;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    overflow: hidden;
}

@media (max-width: 768px) {
    .mayhen_item_grid_top, .mayhen_item_grid_bottom, .mayhen_form_grid_2 {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
const availableIconsMap = @json($availableIcons);

function selectScenario(scenarioKey, items) {
    const radio = document.getElementById('sc_' + scenarioKey);
    if (radio) radio.checked = true;

    document.querySelectorAll('.mayhen_scenario_card').forEach(el => el.classList.remove('is-selected'));
    const clicked = document.querySelector(`[onclick*="${scenarioKey}"]`);
    if (clicked) clicked.classList.add('is-selected');

    const container = document.getElementById('timelineItemsContainer');
    if (!container) return;
    container.innerHTML = '';

    items.forEach((item, idx) => {
        addTimelineItemRow(item.time, item.title, item.location, item.description, item.icon, idx);
    });

    updateTimelineCount();
}

function addTimelineItemRow(time = '17:30', title = 'Hoạt động mới', location = '', desc = '', icon = 'rings', idx = null) {
    const container = document.getElementById('timelineItemsContainer');
    if (!container) return;

    const nextIndex = idx !== null ? idx : container.querySelectorAll('.mayhen_timeline_item_row').length;

    let iconOptionsHtml = '';
    for (const [key, ic] of Object.entries(availableIconsMap)) {
        const isSelected = key === icon ? 'selected' : '';
        iconOptionsHtml += `<option value="${key}" ${isSelected}>${ic.icon} ${ic.name}</option>`;
    }

    const row = document.createElement('div');
    row.className = 'mayhen_timeline_item_row';
    row.setAttribute('data-index', nextIndex);
    row.innerHTML = `
        <div class="mayhen_item_top">
            <span class="mayhen_item_order_tag">Mốc sự kiện #${nextIndex + 1}</span>
            <button type="button" class="mayhen_btn_del_item" onclick="removeTimelineItem(this)">✕ Xóa mốc này</button>
        </div>

        <div class="mayhen_item_grid_top">
            <div class="mayhen_form_field mayhen_form_field--time">
                <label class="mayhen_label">Thời gian *</label>
                <input type="text" name="items[${nextIndex}][time]" class="mayhen_input"
                       placeholder="Ví dụ: 17:30" value="${time}" required>
            </div>

            <div class="mayhen_form_field mayhen_form_field--icon">
                <label class="mayhen_label">Biểu tượng</label>
                <select name="items[${nextIndex}][icon]" class="mayhen_select">
                    ${iconOptionsHtml}
                </select>
            </div>

            <div class="mayhen_form_field mayhen_form_field--title">
                <label class="mayhen_label">Tiêu đề hoạt động *</label>
                <input type="text" name="items[${nextIndex}][title]" class="mayhen_input"
                       placeholder="Ví dụ: Đón Tiếp Quan Khách" value="${title}" required>
            </div>
        </div>

        <div class="mayhen_item_grid_bottom">
            <div class="mayhen_form_field">
                <label class="mayhen_label">Sảnh / Vị trí tổ chức</label>
                <input type="text" name="items[${nextIndex}][location]" class="mayhen_input"
                       placeholder="Sảnh, phòng tiệc..." value="${location}">
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label">Mô tả &amp; Lưu ý ngắn</label>
                <input type="text" name="items[${nextIndex}][description]" class="mayhen_input"
                       placeholder="Ghi chú thêm..." value="${desc}">
            </div>
        </div>
    `;

    container.appendChild(row);
    updateTimelineCount();
}

function removeTimelineItem(btn) {
    const container = document.getElementById('timelineItemsContainer');
    if (!container) return;
    if (container.querySelectorAll('.mayhen_timeline_item_row').length <= 1) {
        alert('Lịch trình cần có ít nhất 1 mốc hoạt động.');
        return;
    }
    btn.closest('.mayhen_timeline_item_row').remove();
    updateTimelineCount();

    // Cập nhật lại số thứ tự
    const rows = container.querySelectorAll('.mayhen_timeline_item_row');
    rows.forEach((r, i) => {
        const tag = r.querySelector('.mayhen_item_order_tag');
        if (tag) tag.textContent = 'Mốc sự kiện #' + (i + 1);
    });
}

function updateTimelineCount() {
    const container = document.getElementById('timelineItemsContainer');
    const txt = document.getElementById('timelineItemCount');
    if (container && txt) {
        txt.textContent = container.querySelectorAll('.mayhen_timeline_item_row').length;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('addTimelineItemBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addTimelineItemRow('18:00', 'Mốc hoạt động mới', '', '', 'rings');
        });
    }
});
</script>
@endsection
