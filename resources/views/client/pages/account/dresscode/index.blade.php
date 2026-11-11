@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quy Chuẩn Trang Phục (Dresscode) & Bảng Màu Hôn Lễ — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Bảng Màu Dresscode')

@section('account_content')
<div class="mayhen_dresscode_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_dresscode_admin_header">
        <div class="mayhen_dresscode_admin_headline">
            <div class="mayhen_dresscode_admin_badge">Attire &amp; Dress Code Palette Studio</div>
            <h1 class="mayhen_dresscode_admin_title">Bảng Màu Trang Phục &amp; Quy Chuẩn Dresscode</h1>
            <p class="mayhen_dresscode_admin_subtitle">
                Gợi ý bảng màu và hướng dẫn trang phục thanh lịch cho quan khách tham dự hôn lễ của <strong>{{ $coupleName }}</strong>, giúp toàn bộ khung hình kỷ niệm đạt được sự hòa quyện và đồng điệu tuyệt đối theo phong cách Quiet Luxury.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_dresscode_alert mayhen_dresscode_alert--success">
            <span class="mayhen_dresscode_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_dresscode_alert mayhen_dresscode_alert--danger">
            <span class="mayhen_dresscode_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('account.dresscode.update', $invitation->id) }}" method="POST" id="dresscodeForm" class="mayhen_dresscode_form">
        @csrf

        {{-- Section 1: Kích Hoạt & Chọn Bảng Màu Mẫu Sẵn --}}
        <div class="mayhen_dresscode_card">
            <div class="mayhen_dresscode_card_header">
                <div class="mayhen_dresscode_card_icon">🎨</div>
                <div class="mayhen_dresscode_card_title_row">
                    <div>
                        <h2 class="mayhen_dresscode_card_title">1. Chọn Bảng Màu Gợi Ý Sẵn (Preset Palettes)</h2>
                        <p class="mayhen_dresscode_card_desc">Chọn một trong 6 bảng màu kinh điển được Mây Hẹn thiết kế riêng cho các phong cách hôn lễ.</p>
                    </div>
                    <label class="mayhen_dresscode_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($dresscodeConfig['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_dresscode_slider"></span>
                        <span class="mayhen_dresscode_switch_text">Hiển thị trên thiệp</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_dresscode_presets_grid">
                @foreach($presets as $key => $preset)
                    <div class="mayhen_preset_card {{ ($dresscodeConfig['preset_key'] ?? '') === $key ? 'is-selected' : '' }}"
                         onclick="selectPreset('{{ $key }}', {{ json_encode($preset['colors']) }}, '{{ addslashes($preset['name']) }}')">
                        <div class="mayhen_preset_card_top">
                            <input type="radio" name="preset_key" value="{{ $key }}" id="preset_{{ $key }}"
                                   {{ ($dresscodeConfig['preset_key'] ?? '') === $key ? 'checked' : '' }}>
                            <label for="preset_{{ $key }}" class="mayhen_preset_name">{{ $preset['name'] }}</label>
                        </div>
                        <p class="mayhen_preset_desc">{{ $preset['description'] }}</p>
                        <div class="mayhen_preset_swatches">
                            @foreach($preset['colors'] as $c)
                                <span class="mayhen_preset_swatch" style="background-color: {{ $c['hex'] }};" title="{{ $c['name'] }} ({{ $c['hex'] }})"></span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 2: Tùy Biến Danh Sách Màu Sắc Hôn Lễ --}}
        <div class="mayhen_dresscode_card">
            <div class="mayhen_dresscode_card_header">
                <div class="mayhen_dresscode_card_icon">✨</div>
                <div>
                    <h2 class="mayhen_dresscode_card_title">2. Tùy Chỉnh Các Mẫu Màu Trang Phục (Custom Swatches)</h2>
                    <p class="mayhen_dresscode_card_desc">Bạn có thể click vào ô màu để đổi mã HEX, đặt tên màu và mô tả gợi ý cho khách.</p>
                </div>
            </div>

            <div class="mayhen_swatches_toolbar">
                <span class="mayhen_swatches_count">Hiện có <strong id="swatchCountText">{{ count($dresscodeConfig['colors']) }}</strong> màu sắc trong bảng</span>
                <button type="button" class="mayhen_btn_add_swatch" id="addSwatchBtn">+ Thêm tone màu tùy chỉnh</button>
            </div>

            <div id="swatchesContainer" class="mayhen_swatches_list">
                @foreach($dresscodeConfig['colors'] as $idx => $color)
                    <div class="mayhen_swatch_row" data-index="{{ $idx }}">
                        <div class="mayhen_swatch_color_picker_box">
                            <input type="color" class="mayhen_color_native" value="{{ $color['hex'] }}"
                                   onchange="syncColorInput(this, {{ $idx }})">
                            <input type="text" name="colors[{{ $idx }}][hex]" class="mayhen_color_hex_input"
                                   id="hexInput_{{ $idx }}" value="{{ $color['hex'] }}" required
                                   oninput="syncHexToPicker(this, {{ $idx }})">
                        </div>
                        <div class="mayhen_swatch_inputs">
                            <input type="text" name="colors[{{ $idx }}][name]" class="mayhen_input"
                                   placeholder="Tên tone màu (Ví dụ: Kem Be, Nâu Cát...)" value="{{ $color['name'] }}" required>
                            <input type="text" name="colors[{{ $idx }}][description]" class="mayhen_input mayhen_input--desc"
                                   placeholder="Gợi ý trang phục (Ví dụ: Đầm lụa, áo sơ mi...)" value="{{ $color['description'] ?? '' }}">
                        </div>
                        <button type="button" class="mayhen_btn_del_swatch" onclick="removeSwatch(this)" title="Xóa màu này">✕</button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 3: Phong Cách & Hướng Dẫn Nam / Nữ --}}
        <div class="mayhen_dresscode_card">
            <div class="mayhen_dresscode_card_header">
                <div class="mayhen_dresscode_card_icon">👔</div>
                <div>
                    <h2 class="mayhen_dresscode_card_title">3. Phong Cách Hôn Lễ &amp; Hướng Dẫn Phối Đồ</h2>
                    <p class="mayhen_dresscode_card_desc">Thiết lập quy chuẩn cụ thể cho Quý Nam và Quý Nữ để khách mời chuẩn bị trang phục tự tin nhất.</p>
                </div>
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="attire_style">Quy chuẩn phong cách trang phục</label>
                    <select name="attire_style" id="attire_style" class="mayhen_select" onchange="applyAttireStyle(this.value)">
                        @foreach($attireStyles as $sKey => $s)
                            <option value="{{ $sKey }}" {{ ($dresscodeConfig['attire_style'] ?? '') === $sKey ? 'selected' : '' }}>
                                {{ $s['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="title">Tiêu đề mục trên thiệp cưới</label>
                    <input type="text" id="title" name="title" class="mayhen_input"
                           value="{{ old('title', $dresscodeConfig['title']) }}" required>
                </div>
            </div>

            <div class="mayhen_form_field">
                <label class="mayhen_label" for="subtitle">Phụ đề truyền cảm hứng</label>
                <input type="text" id="subtitle" name="subtitle" class="mayhen_input"
                       value="{{ old('subtitle', $dresscodeConfig['subtitle']) }}">
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="gentlemen_guide">
                        🤵‍♂️ Hướng dẫn dành cho Quý Nam (Gentlemen)
                    </label>
                    <textarea id="gentlemen_guide" name="gentlemen_guide" rows="3" class="mayhen_textarea">{{ old('gentlemen_guide', $dresscodeConfig['gentlemen_guide']) }}</textarea>
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="ladies_guide">
                        👰‍♀️ Hướng dẫn dành cho Quý Nữ (Ladies)
                    </label>
                    <textarea id="ladies_guide" name="ladies_guide" rows="3" class="mayhen_textarea">{{ old('ladies_guide', $dresscodeConfig['ladies_guide']) }}</textarea>
                </div>
            </div>

            <div class="mayhen_form_grid_2">
                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="note">Lời nhắn gửi gắm từ cô dâu chú rể</label>
                    <textarea id="note" name="note" rows="2" class="mayhen_textarea">{{ old('note', $dresscodeConfig['note']) }}</textarea>
                </div>

                <div class="mayhen_form_field">
                    <label class="mayhen_label" for="avoid_colors_note">
                        ⚠️ Trang phục / Màu sắc cần hạn chế (nếu có)
                    </label>
                    <textarea id="avoid_colors_note" name="avoid_colors_note" rows="2" class="mayhen_textarea"
                              placeholder="Ví dụ: Xin quý khách hạn chế trang phục màu đỏ tươi, dạ quang hoặc áo thun, quần ngắn.">{{ old('avoid_colors_note', $dresscodeConfig['avoid_colors_note']) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 4: Ảnh Moodboard Gợi Ý Phối Đồ --}}
        <div class="mayhen_dresscode_card">
            <div class="mayhen_dresscode_card_header">
                <div class="mayhen_dresscode_card_icon">🖼️</div>
                <div>
                    <h2 class="mayhen_dresscode_card_title">4. Ảnh Moodboard Gợi Ý Trang Phục (Tùy Chọn)</h2>
                    <p class="mayhen_dresscode_card_desc">Tải lên một tấm ảnh ghép các set đồ mẫu nam &amp; nữ để khách mời dễ hình dung (Dưới 10MB, JPG/PNG/WEBP).</p>
                </div>
            </div>

            <input type="hidden" name="moodboard_image_url" id="moodboardInput" value="{{ $dresscodeConfig['moodboard_image_url'] ?? '' }}">

            <div class="mayhen_moodboard_upload_area">
                <div class="mayhen_moodboard_preview_box" id="moodboardPreviewBox" style="{{ !empty($dresscodeConfig['moodboard_image_url']) ? '' : 'display: none;' }}">
                    <img id="moodboardImgPreview" src="{{ $dresscodeConfig['moodboard_image_url'] ?? '' }}" alt="Moodboard Preview" class="mayhen_moodboard_img">
                    <button type="button" class="mayhen_btn_remove_moodboard" onclick="removeMoodboard()">✕ Xóa ảnh này</button>
                </div>

                <div class="mayhen_upload_dropzone" id="moodboardDropzone" style="{{ !empty($dresscodeConfig['moodboard_image_url']) ? 'display: none;' : '' }}">
                    <div class="mayhen_upload_icon">📁</div>
                    <div class="mayhen_upload_text">Kéo thả ảnh moodboard vào đây hoặc <strong>nhấp để chọn tệp</strong></div>
                    <div class="mayhen_upload_hint">Định dạng hỗ trợ: JPG, PNG, WEBP (Tối đa 10MB)</div>
                    <input type="file" id="moodboardFileInput" accept="image/*" style="display: none;">
                </div>
                <div id="uploadStatusText" class="mayhen_upload_status"></div>
            </div>
        </div>

        {{-- Thanh Nút Thao Tác Lưu Cài Đặt --}}
        <div class="mayhen_dresscode_actions">
            <a href="{{ route('account.invitations.settings', $invitation->id) }}" class="mayhen_dresscode_btn_back">← Quay lại cài đặt thiệp</a>
            <button type="submit" class="mayhen_dresscode_btn_primary">Lưu Quy Chuẩn &amp; Bảng Màu Dresscode</button>
        </div>
    </form>

    {{-- Live Preview Section 8 --}}
    <div class="mayhen_dresscode_preview_section">
        <div class="mayhen_dresscode_preview_header">
            <h3 class="mayhen_preview_title">Xem Thử Trực Quan Section Dresscode Trên Thiệp Cưới</h3>
            <span class="mayhen_preview_sub">Mô phỏng hiển thị thực tế khách mời sẽ chiêm ngưỡng</span>
        </div>
        <div class="mayhen_dresscode_preview_canvas">
            @include('client.components.dresscode-palette', ['invitation' => $invitation])
        </div>
    </div>
</div>

<style>
/* CSS Độc Bản Phân Hệ Dresscode Studio — Quiet Luxury */
.mayhen_dresscode_admin_container {
    max-width: 1040px;
    margin: 0 auto;
    padding-bottom: 60px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #2C2824;
}
.mayhen_dresscode_admin_header {
    margin-bottom: 24px;
}
.mayhen_dresscode_admin_badge {
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
.mayhen_dresscode_admin_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_dresscode_admin_subtitle {
    font-size: 14.5px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0;
}
.mayhen_dresscode_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}
.mayhen_dresscode_alert--success {
    background: #EBF7EE;
    color: #1E6B38;
    border: 1px solid #C3E6CB;
}
.mayhen_dresscode_alert--danger {
    background: #FDF2F2;
    color: #9E1C1C;
    border: 1px solid #F8D7DA;
}

/* Card */
.mayhen_dresscode_card {
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #E5DCCD;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.mayhen_dresscode_card_header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}
.mayhen_dresscode_card_icon {
    font-size: 26px;
    line-height: 1;
}
.mayhen_dresscode_card_title_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_dresscode_card_title {
    font-size: 18px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_dresscode_card_desc {
    font-size: 13.5px;
    color: #6C665C;
    margin: 0;
}

/* Switch */
.mayhen_dresscode_switch {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.mayhen_dresscode_switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.mayhen_dresscode_slider {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background-color: #D8CEBD;
    border-radius: 24px;
    transition: 0.3s;
}
.mayhen_dresscode_slider:before {
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
.mayhen_dresscode_switch input:checked + .mayhen_dresscode_slider {
    background-color: #9C7A4A;
}
.mayhen_dresscode_switch input:checked + .mayhen_dresscode_slider:before {
    transform: translateX(20px);
}
.mayhen_dresscode_switch_text {
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
}

/* Preset Cards Grid */
.mayhen_dresscode_presets_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
    margin-top: 14px;
}
.mayhen_preset_card {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_preset_card:hover {
    border-color: #C5A880;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.mayhen_preset_card.is-selected {
    border-color: #9C7A4A;
    background: #FFFFFF;
    box-shadow: 0 0 0 2px #9C7A4A;
}
.mayhen_preset_card_top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.mayhen_preset_card_top input[type="radio"] {
    accent-color: #9C7A4A;
    cursor: pointer;
}
.mayhen_preset_name {
    font-size: 14px;
    font-weight: 700;
    color: #1F1D1A;
    cursor: pointer;
}
.mayhen_preset_desc {
    font-size: 12.5px;
    color: #6C665C;
    margin: 0 0 12px 0;
    line-height: 1.4;
}
.mayhen_preset_swatches {
    display: flex;
    gap: 8px;
}
.mayhen_preset_swatch {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #FFFFFF;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

/* Custom Swatches list */
.mayhen_swatches_toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.mayhen_swatches_count {
    font-size: 13.5px;
    color: #6C665C;
}
.mayhen_btn_add_swatch {
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
.mayhen_btn_add_swatch:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}
.mayhen_swatches_list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.mayhen_swatch_row {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    padding: 12px 16px;
    border-radius: 10px;
}
.mayhen_swatch_color_picker_box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1px solid #D8CEBD;
    padding: 4px 8px;
    border-radius: 8px;
}
.mayhen_color_native {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    background: transparent;
}
.mayhen_color_hex_input {
    width: 75px;
    border: none;
    font-family: monospace;
    font-size: 13px;
    font-weight: 600;
    color: #1F1D1A;
    text-transform: uppercase;
}
.mayhen_color_hex_input:focus {
    outline: none;
}
.mayhen_swatch_inputs {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 12px;
}
.mayhen_btn_del_swatch {
    background: transparent;
    border: none;
    color: #DC3545;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    padding: 4px 8px;
}
.mayhen_btn_del_swatch:hover {
    color: #A71D2A;
}

/* Forms & Grids */
.mayhen_form_grid_2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.mayhen_form_field {
    margin-bottom: 16px;
}
.mayhen_label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
    margin-bottom: 6px;
}
.mayhen_input, .mayhen_textarea, .mayhen_select {
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
.mayhen_input:focus, .mayhen_textarea:focus, .mayhen_select:focus {
    outline: none;
    border-color: #9C7A4A;
    background: #FFFFFF;
}

/* Moodboard Upload */
.mayhen_moodboard_upload_area {
    margin-top: 10px;
}
.mayhen_upload_dropzone {
    border: 2px dashed #C5A880;
    border-radius: 12px;
    padding: 32px 20px;
    text-align: center;
    background: #FAF8F5;
    cursor: pointer;
    transition: background 0.2s;
}
.mayhen_upload_dropzone:hover {
    background: #F5EFE6;
}
.mayhen_upload_icon {
    font-size: 32px;
    margin-bottom: 8px;
}
.mayhen_upload_text {
    font-size: 14px;
    color: #38342E;
}
.mayhen_upload_hint {
    font-size: 12px;
    color: #8C8275;
    margin-top: 4px;
}
.mayhen_moodboard_preview_box {
    text-align: center;
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 16px;
}
.mayhen_moodboard_img {
    max-width: 100%;
    max-height: 280px;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    display: inline-block;
}
.mayhen_btn_remove_moodboard {
    display: block;
    margin: 12px auto 0 auto;
    background: transparent;
    border: 1px solid #DC3545;
    color: #DC3545;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12.5px;
    cursor: pointer;
}
.mayhen_upload_status {
    font-size: 13px;
    margin-top: 8px;
    font-weight: 600;
}

/* Actions */
.mayhen_dresscode_actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    gap: 16px;
}
.mayhen_dresscode_btn_back {
    color: #6C665C;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}
.mayhen_dresscode_btn_primary {
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
.mayhen_dresscode_btn_primary:hover {
    background: #846538;
}

/* Preview Canvas */
.mayhen_dresscode_preview_section {
    margin-top: 48px;
    padding-top: 36px;
    border-top: 1px solid #E5DCCD;
}
.mayhen_dresscode_preview_header {
    text-align: center;
    margin-bottom: 24px;
}
.mayhen_preview_title {
    font-size: 20px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_preview_sub {
    font-size: 13.5px;
    color: #6C665C;
}
.mayhen_dresscode_preview_canvas {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #E5DCCD;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    overflow: hidden;
}

@media (max-width: 768px) {
    .mayhen_form_grid_2, .mayhen_swatch_inputs {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
const attirePresets = @json($attireStyles);

function selectPreset(presetKey, colors, presetName) {
    // 1. Radio check
    const radio = document.getElementById('preset_' + presetKey);
    if (radio) radio.checked = true;

    // 2. Active class
    document.querySelectorAll('.mayhen_preset_card').forEach(el => el.classList.remove('is-selected'));
    const clickedCard = document.querySelector(`[onclick*="${presetKey}"]`);
    if (clickedCard) clickedCard.classList.add('is-selected');

    // 3. Render lại danh sách Swatches
    const container = document.getElementById('swatchesContainer');
    if (!container) return;
    container.innerHTML = '';

    colors.forEach((c, idx) => {
        addSwatchRow(c.hex, c.name, c.description, idx);
    });

    updateSwatchCount();
}

function syncColorInput(picker, index) {
    const hexInput = document.getElementById('hexInput_' + index);
    if (hexInput) {
        hexInput.value = picker.value.toUpperCase();
    }
}

function syncHexToPicker(input, index) {
    let val = input.value.trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        const picker = input.parentElement.querySelector('.mayhen_color_native');
        if (picker) picker.value = val;
    }
}

function addSwatchRow(hex = '#C5A880', name = 'Tone màu mới', desc = '', idx = null) {
    const container = document.getElementById('swatchesContainer');
    if (!container) return;

    const nextIndex = idx !== null ? idx : container.querySelectorAll('.mayhen_swatch_row').length;

    const row = document.createElement('div');
    row.className = 'mayhen_swatch_row';
    row.setAttribute('data-index', nextIndex);
    row.innerHTML = `
        <div class="mayhen_swatch_color_picker_box">
            <input type="color" class="mayhen_color_native" value="${hex}"
                   onchange="syncColorInput(this, ${nextIndex})">
            <input type="text" name="colors[${nextIndex}][hex]" class="mayhen_color_hex_input"
                   id="hexInput_${nextIndex}" value="${hex.toUpperCase()}" required
                   oninput="syncHexToPicker(this, ${nextIndex})">
        </div>
        <div class="mayhen_swatch_inputs">
            <input type="text" name="colors[${nextIndex}][name]" class="mayhen_input"
                   placeholder="Tên tone màu" value="${name}" required>
            <input type="text" name="colors[${nextIndex}][description]" class="mayhen_input mayhen_input--desc"
                   placeholder="Gợi ý trang phục" value="${desc}">
        </div>
        <button type="button" class="mayhen_btn_del_swatch" onclick="removeSwatch(this)" title="Xóa màu này">✕</button>
    `;
    container.appendChild(row);
    updateSwatchCount();
}

function removeSwatch(btn) {
    const container = document.getElementById('swatchesContainer');
    if (!container) return;
    if (container.querySelectorAll('.mayhen_swatch_row').length <= 1) {
        alert('Bảng màu cần có ít nhất 1 màu sắc.');
        return;
    }
    btn.closest('.mayhen_swatch_row').remove();
    updateSwatchCount();
}

function updateSwatchCount() {
    const container = document.getElementById('swatchesContainer');
    const txt = document.getElementById('swatchCountText');
    if (container && txt) {
        txt.textContent = container.querySelectorAll('.mayhen_swatch_row').length;
    }
}

function applyAttireStyle(styleKey) {
    const style = attirePresets[styleKey];
    if (style) {
        const gent = document.getElementById('gentlemen_guide');
        const lad = document.getElementById('ladies_guide');
        if (gent && !gent.value.trim()) gent.value = style.gentlemen;
        if (lad && !lad.value.trim()) lad.value = style.ladies;
    }
}

function removeMoodboard() {
    document.getElementById('moodboardInput').value = '';
    document.getElementById('moodboardPreviewBox').style.display = 'none';
    document.getElementById('moodboardDropzone').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    // Add swatch button
    const addBtn = document.getElementById('addSwatchBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addSwatchRow('#D8CEBD', 'Tone màu mới', 'Gợi ý trang phục');
        });
    }

    // Moodboard upload
    const dropzone = document.getElementById('moodboardDropzone');
    const fileInput = document.getElementById('moodboardFileInput');
    const statusText = document.getElementById('uploadStatusText');

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const formData = new FormData();
                formData.append('moodboard_file', file);
                formData.append('_token', '{{ csrf_token() }}');

                statusText.textContent = 'Đang tải ảnh lên...';
                statusText.style.color = '#9C7A4A';

                fetch('{{ route('account.dresscode.upload_moodboard', $invitation->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.url) {
                        document.getElementById('moodboardInput').value = data.url;
                        document.getElementById('moodboardImgPreview').src = data.url;
                        document.getElementById('moodboardPreviewBox').style.display = 'block';
                        dropzone.style.display = 'none';
                        statusText.textContent = 'Tải ảnh thành công!';
                        statusText.style.color = '#28A745';
                    } else {
                        statusText.textContent = data.message || 'Lỗi khi tải ảnh.';
                        statusText.style.color = '#DC3545';
                    }
                })
                .catch(() => {
                    statusText.textContent = 'Có lỗi xảy ra khi tải ảnh lên.';
                    statusText.style.color = '#DC3545';
                });
            }
        });
    }
});
</script>
@endsection
