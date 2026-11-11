@extends('client.pages.account.layout')

@php
    $pageTitle = 'Chỉ Dẫn Địa Điểm, Bãi Đỗ Xe & Thời Tiết — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Bản Đồ & Thời Tiết')

@section('account_content')
<div class="mayhen_locadv_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_locadv_admin_header">
        <div class="mayhen_locadv_admin_headline">
            <div class="mayhen_locadv_admin_badge">Multi-App Navigation &amp; Weather Advisory Suite</div>
            <h1 class="mayhen_locadv_admin_title">Chỉ Dẫn Địa Điểm, Bãi Đỗ Xe &amp; Thời Tiết Ngày Cưới</h1>
            <p class="mayhen_locadv_admin_subtitle">
                Thiết lập thông tin dẫn đường đa ứng dụng (Google Maps, Apple Maps, Grab), vị trí bãi đỗ xe ô tô / xe máy và lưu ý thời tiết dự kiến cho ngày trọng đại của <strong>{{ $coupleName }}</strong> để quan khách an tâm di chuyển thong thả.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_locadv_alert mayhen_locadv_alert--success">
            <span class="mayhen_locadv_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_locadv_alert mayhen_locadv_alert--danger">
            <span class="mayhen_locadv_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('account.location_advisory.update', $invitation->id) }}" method="POST" id="locadvForm" class="mayhen_locadv_form">
        @csrf

        {{-- Section 1: Thông Tin Địa Điểm & Bản Đồ --}}
        <div class="mayhen_locadv_card">
            <div class="mayhen_locadv_card_header">
                <div class="mayhen_locadv_card_icon">📍</div>
                <div class="mayhen_locadv_card_title_row">
                    <div>
                        <h2 class="mayhen_locadv_card_title">1. Trung Tâm Tiệc Cưới &amp; Bản Đồ Dẫn Đường</h2>
                        <p class="mayhen_locadv_card_desc">Khách mời có thể chạm 1 chạm để mở Google Maps, Apple Maps hoặc gọi xe Grab đến thẳng sảnh tiệc.</p>
                    </div>
                    <label class="mayhen_locadv_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($advisoryConfig['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_locadv_slider"></span>
                        <span class="mayhen_locadv_switch_text">Hiển thị trên thiệp</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="venue_name">Tên sảnh / Trung tâm tiệc cưới *</label>
                    <input type="text" id="venue_name" name="venue_name" class="mayhen_input"
                           placeholder="Ví dụ: Trung Tâm Hội Nghị Grand Palace"
                           value="{{ old('venue_name', $advisoryConfig['venue_name']) }}" required>
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="map_url">Đường dẫn Google Maps (URL)</label>
                    <input type="url" id="map_url" name="map_url" class="mayhen_input"
                           placeholder="https://maps.app.goo.gl/..."
                           value="{{ old('map_url', $advisoryConfig['map_url']) }}">
                </div>
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label" for="venue_address">Địa chỉ sảnh tiệc chi tiết *</label>
                <input type="text" id="venue_address" name="venue_address" class="mayhen_input"
                       placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành..."
                       value="{{ old('venue_address', $advisoryConfig['venue_address']) }}" required>
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="latitude">Vĩ độ GPS (Latitude — Tùy chọn)</label>
                    <input type="number" step="any" id="latitude" name="latitude" class="mayhen_input"
                           placeholder="Ví dụ: 10.7828" value="{{ old('latitude', $advisoryConfig['latitude']) }}">
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="longitude">Kinh độ GPS (Longitude — Tùy chọn)</label>
                    <input type="number" step="any" id="longitude" name="longitude" class="mayhen_input"
                           placeholder="Ví dụ: 106.6958" value="{{ old('longitude', $advisoryConfig['longitude']) }}">
                </div>
            </div>
        </div>

        {{-- Section 2: Dự Báo Thời Tiết & Gợi Ý Trang Phục (IDEA-A02) --}}
        <div class="mayhen_locadv_card">
            <div class="mayhen_locadv_card_header">
                <div class="mayhen_locadv_card_icon">🌤️</div>
                <div class="mayhen_locadv_card_title_row">
                    <div>
                        <h2 class="mayhen_locadv_card_title">2. Dự Báo &amp; Gợi Ý Thời Tiết Hôn Lễ (Weather Advisory)</h2>
                        <p class="mayhen_locadv_card_desc">Hiển thị widget thời tiết Quiet Luxury và lời dặn dò trang phục ấm áp từ đôi uyên ương.</p>
                    </div>
                    <label class="mayhen_locadv_switch">
                        <input type="checkbox" name="weather_enabled" id="weatherToggle" value="1" {{ !empty($advisoryConfig['weather']['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_locadv_slider"></span>
                        <span class="mayhen_locadv_switch_text">Bật widget thời tiết</span>
                    </label>
                </div>
            </div>

            <div id="weatherContent" class="mayhen_locadv_collapsible {{ !empty($advisoryConfig['weather']['enabled']) ? 'is-visible' : '' }}">
                <div class="mayhen_weather_presets_grid">
                    @foreach($weatherPresets as $wKey => $wPreset)
                        <div class="mayhen_weather_card {{ ($advisoryConfig['weather']['condition_key'] ?? '') === $wKey ? 'is-selected' : '' }}"
                             onclick="selectWeather('{{ $wKey }}', {{ $wPreset['temp_default'] }}, '{{ addslashes($wPreset['description']) }}')">
                            <input type="radio" name="condition_key" value="{{ $wKey }}" id="w_{{ $wKey }}"
                                   {{ ($advisoryConfig['weather']['condition_key'] ?? '') === $wKey ? 'checked' : '' }} style="display: none;">
                            <div class="mayhen_weather_card_top">
                                <span class="mayhen_weather_big_icon">{{ $wPreset['icon'] }}</span>
                                <span class="mayhen_weather_temp_badge">{{ $wPreset['temp_default'] }}°C</span>
                            </div>
                            <h4 class="mayhen_weather_card_name">{{ $wPreset['name'] }}</h4>
                            <p class="mayhen_weather_card_desc">{{ $wPreset['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mayhen_form_grid_2" style="margin-top: 20px;">
                    <div class="mayhen_form_field">
                        <label class="mayhen_label" for="temperature_celsius">Nhiệt độ dự kiến (°C)</label>
                        <input type="number" id="temperature_celsius" name="temperature_celsius" class="mayhen_input"
                               min="-10" max="50"
                               value="{{ old('temperature_celsius', $advisoryConfig['weather']['temperature_celsius'] ?? 28) }}">
                    </div>

                    <div class="mayhen_form_field">
                        <label class="mayhen_label" for="advisory_note">Lời nhắn &amp; dặn dò trang phục cho khách</label>
                        <input type="text" id="advisory_note" name="advisory_note" class="mayhen_input"
                               placeholder="Ví dụ: Thời tiết se lạnh nhẹ về chiều tối, quý khách nên mang theo áo khoác mỏng."
                               value="{{ old('advisory_note', $advisoryConfig['weather']['advisory_note'] ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Hướng Dẫn Bãi Đỗ Xe & Ban Tiếp Đón --}}
        <div class="mayhen_locadv_card">
            <div class="mayhen_locadv_card_header">
                <div class="mayhen_locadv_card_icon">🚙</div>
                <div>
                    <h2 class="mayhen_locadv_card_title">3. Hướng Dẫn Bãi Đỗ Xe &amp; Hotline Tiếp Đón</h2>
                    <p class="mayhen_locadv_card_desc">Giúp quan khách không bỡ ngỡ tìm chỗ gửi ô tô, xe máy và có hotline hỗ trợ khi tới nơi.</p>
                </div>
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label" for="car_parking">Chỉ dẫn bãi đỗ xe Ô Tô</label>
                <input type="text" id="car_parking" name="car_parking" class="mayhen_input"
                       placeholder="Ví dụ: Bãi xe ô tô tầng hầm B1-B2 (Miễn phí vé gửi xe cho khách dự tiệc)"
                       value="{{ old('car_parking', $advisoryConfig['parking']['car_parking'] ?? '') }}">
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label" for="bike_parking">Chỉ dẫn bãi đỗ xe Máy</label>
                <input type="text" id="bike_parking" name="bike_parking" class="mayhen_input"
                       placeholder="Ví dụ: Bãi xe máy bên hông sảnh chính, có bảo vệ trông giữ an toàn"
                       value="{{ old('bike_parking', $advisoryConfig['parking']['bike_parking'] ?? '') }}">
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label" for="accessibility_note">Lối đi riêng cho người lớn tuổi / xe lăn</label>
                <input type="text" id="accessibility_note" name="accessibility_note" class="mayhen_input"
                       placeholder="Ví dụ: Có thang máy và dốc di chuyển riêng cho xe lăn và người cao tuổi tại cửa số 1"
                       value="{{ old('accessibility_note', $advisoryConfig['parking']['accessibility_note'] ?? '') }}">
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="reception_coordinator">Tên bộ phận tiếp đón</label>
                    <input type="text" id="reception_coordinator" name="reception_coordinator" class="mayhen_input"
                           placeholder="Ví dụ: Ban Lễ Tân Đón Tiếp"
                           value="{{ old('reception_coordinator', $advisoryConfig['reception_coordinator']) }}">
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="reception_phone">Hotline hỗ trợ chỉ đường khi khách đến</label>
                    <input type="text" id="reception_phone" name="reception_phone" class="mayhen_input"
                           placeholder="Ví dụ: 0901234567"
                           value="{{ old('reception_phone', $advisoryConfig['reception_phone']) }}">
                </div>
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="title">Tiêu đề Section trên thiệp</label>
                    <input type="text" id="title" name="title" class="mayhen_input"
                           value="{{ old('title', $advisoryConfig['title']) }}" required>
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="subtitle">Phụ đề truyền cảm hứng</label>
                    <input type="text" id="subtitle" name="subtitle" class="mayhen_input"
                           value="{{ old('subtitle', $advisoryConfig['subtitle']) }}">
                </div>
            </div>
        </div>

        {{-- Thanh Nút Thao Tác Lưu Cài Đặt --}}
        <div class="mayhen_locadv_actions">
            <a href="{{ route('account.invitations.settings', $invitation->id) }}" class="mayhen_locadv_btn_back">← Quay lại cài đặt thiệp</a>
            <button type="submit" class="mayhen_locadv_btn_primary">Lưu Chỉ Dẫn Địa Điểm &amp; Thời Tiết</button>
        </div>
    </form>

    {{-- Live Preview Section 6 --}}
    <div class="mayhen_locadv_preview_section">
        <div class="mayhen_locadv_preview_header">
            <h3 class="mayhen_preview_title">Xem Thử Trực Quan Section Địa Điểm &amp; Thời Tiết Trên Thiệp Cưới</h3>
            <span class="mayhen_preview_sub">Mô phỏng hiển thị 4 nút điều hướng bản đồ và widget thời tiết</span>
        </div>
        <div class="mayhen_locadv_preview_canvas">
            @include('client.components.wedding-location-advisory', ['invitation' => $invitation])
        </div>
    </div>
</div>

<style>
/* CSS Độc Bản Phân Hệ Chỉ Dẫn Địa Điểm & Thời Tiết Studio — Quiet Luxury */
.mayhen_locadv_admin_container {
    max-width: 1040px;
    margin: 0 auto;
    padding-bottom: 60px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #2C2824;
}
.mayhen_locadv_admin_header {
    margin-bottom: 24px;
}
.mayhen_locadv_admin_badge {
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
.mayhen_locadv_admin_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_locadv_admin_subtitle {
    font-size: 14.5px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0;
}
.mayhen_locadv_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}
.mayhen_locadv_alert--success {
    background: #EBF7EE;
    color: #1E6B38;
    border: 1px solid #C3E6CB;
}
.mayhen_locadv_alert--danger {
    background: #FDF2F2;
    color: #9E1C1C;
    border: 1px solid #F8D7DA;
}

/* Card */
.mayhen_locadv_card {
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #E5DCCD;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.mayhen_locadv_card_header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}
.mayhen_locadv_card_icon {
    font-size: 26px;
    line-height: 1;
}
.mayhen_locadv_card_title_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_locadv_card_title {
    font-size: 18px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_locadv_card_desc {
    font-size: 13.5px;
    color: #6C665C;
    margin: 0;
}

/* Switch */
.mayhen_locadv_switch {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.mayhen_locadv_switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.mayhen_locadv_slider {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background-color: #D8CEBD;
    border-radius: 24px;
    transition: 0.3s;
}
.mayhen_locadv_slider:before {
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
.mayhen_locadv_switch input:checked + .mayhen_locadv_slider {
    background-color: #9C7A4A;
}
.mayhen_locadv_switch input:checked + .mayhen_locadv_slider:before {
    transform: translateX(20px);
}
.mayhen_locadv_switch_text {
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
}

/* Collapsible */
.mayhen_locadv_collapsible {
    display: none;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed #E5DCCD;
}
.mayhen_locadv_collapsible.is-visible {
    display: block;
}

/* Weather Presets Grid */
.mayhen_weather_presets_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
}
.mayhen_weather_card {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_weather_card:hover {
    border-color: #C5A880;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.mayhen_weather_card.is-selected {
    border-color: #9C7A4A;
    background: #FFFFFF;
    box-shadow: 0 0 0 2px #9C7A4A;
}
.mayhen_weather_card_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.mayhen_weather_big_icon {
    font-size: 28px;
    line-height: 1;
}
.mayhen_weather_temp_badge {
    background: #EFE8DD;
    color: #9C7A4A;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12.5px;
}
.mayhen_weather_card_name {
    font-size: 14px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_weather_card_desc {
    font-size: 12px;
    color: #6C665C;
    margin: 0;
    line-height: 1.4;
}

/* Form inputs & grids */
.mayhen_form_grid_2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.mayhen_form_field {
    margin-bottom: 14px;
}
.mayhen_label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
    margin-bottom: 6px;
}
.mayhen_input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 14px;
    font-size: 14px;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    background: #FAF8F5;
    color: #1F1D1A;
    transition: border-color 0.2s, background 0.2s;
}
.mayhen_input:focus {
    outline: none;
    border-color: #9C7A4A;
    background: #FFFFFF;
}

/* Actions */
.mayhen_locadv_actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    gap: 16px;
}
.mayhen_locadv_btn_back {
    color: #6C665C;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}
.mayhen_locadv_btn_primary {
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
.mayhen_locadv_btn_primary:hover {
    background: #846538;
}

/* Preview Canvas */
.mayhen_locadv_preview_section {
    margin-top: 48px;
    padding-top: 36px;
    border-top: 1px solid #E5DCCD;
}
.mayhen_locadv_preview_header {
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
.mayhen_locadv_preview_canvas {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #E5DCCD;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    overflow: hidden;
}

@media (max-width: 768px) {
    .mayhen_form_grid_2 {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function selectWeather(key, temp, desc) {
    const radio = document.getElementById('w_' + key);
    if (radio) radio.checked = true;

    document.querySelectorAll('.mayhen_weather_card').forEach(el => el.classList.remove('is-selected'));
    const clicked = document.querySelector(`[onclick*="${key}"]`);
    if (clicked) clicked.classList.add('is-selected');

    const tempInput = document.getElementById('temperature_celsius');
    if (tempInput) tempInput.value = temp;

    const noteInput = document.getElementById('advisory_note');
    if (noteInput && !noteInput.value.trim()) {
        noteInput.value = desc;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('weatherToggle');
    const content = document.getElementById('weatherContent');
    if (toggle && content) {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                content.classList.add('is-visible');
            } else {
                content.classList.remove('is-visible');
            }
        });
    }
});
</script>
@endsection

