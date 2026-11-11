@extends('client.pages.account.layout')

@php
    $pageTitle = 'Thông Báo Khẩn Cấp Ngày Cưới — Mây Hẹn';
@endphp

@section('account_breadcrumb', 'Thông Báo Khẩn Cấp')

@section('account_content')
<div class="mayhen_broadcast_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_broadcast_admin_header">
        <div class="mayhen_broadcast_admin_headline">
            <div class="mayhen_broadcast_admin_badge">Wedding Live Update &amp; Urgent Broadcast</div>
            <h1 class="mayhen_broadcast_admin_title">Băng Tin Khẩn Cấp &amp; Điều Phối Ngày Cưới</h1>
            <p class="mayhen_broadcast_admin_subtitle">
                Trong ngày hôn lễ diễn ra, nếu có phát sinh đột xuất (thời tiết mưa lớn cần dời sảnh, lùi giờ đón khách 30 phút vì kẹt xe, hoặc đổi bãi gửi xe), bạn có thể ghim một thông báo khẩn cấp trang trọng lên đầu thiệp cưới của <strong>{{ $invitation->wedding->groom_name ?? $invitation->groom_name }} &amp; {{ $invitation->wedding->bride_name ?? $invitation->bride_name }}</strong> để toàn bộ khách mời nắm bắt tức thời.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_broadcast_alert mayhen_broadcast_alert--success">
            <span class="mayhen_broadcast_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_broadcast_alert mayhen_broadcast_alert--danger">
            <span class="mayhen_broadcast_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Thẻ Trạng Thái Phát Sóng Hiện Tại --}}
    <div class="mayhen_broadcast_status_card {{ ($broadcastConfig['is_active'] ?? false) ? 'is-broadcasting' : 'is-idle' }}">
        <div class="mayhen_broadcast_status_left">
            <div class="mayhen_status_indicator">
                <span class="mayhen_status_dot"></span>
            </div>
            <div>
                <div class="mayhen_status_label">Trạng thái phát tin:</div>
                <h3 class="mayhen_status_title">
                    @if($broadcastConfig['is_active'] ?? false)
                        ĐANG PHÁT SÓNG TRÊN THIỆP CƯỚI
                    @else
                        Đang Tắt (Không có thông báo nào trên thiệp)
                    @endif
                </h3>
                @if(($broadcastConfig['is_active'] ?? false) && !empty($broadcastConfig['formatted_time']))
                    <p class="mayhen_status_meta">Cập nhật lần cuối: {{ $broadcastConfig['formatted_time'] }}</p>
                @endif
            </div>
        </div>

        @if($broadcastConfig['is_active'] ?? false)
            <form action="{{ route('account.broadcast.deactivate', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn tắt thông báo khẩn cấp này trên thiệp cưới?');">
                @csrf
                <button type="submit" class="mayhen_btn_deactivate">Tắt Thông Báo Ngay</button>
            </form>
        @endif
    </div>

    {{-- Grid 2 cột: Cột trái Form cấu hình, Cột phải Live Preview --}}
    <div class="mayhen_broadcast_grid">
        {{-- Cột Trái: Form Cấu Hình Phát Sóng --}}
        <div class="mayhen_broadcast_col_form">
            <form action="{{ route('account.broadcast.update', $invitation->id) }}" method="POST" class="mayhen_broadcast_card">
                @csrf
                <h3 class="mayhen_broadcast_card_heading">Thiết Lập Nội Dung Phát Sóng</h3>

                {{-- Công tắc Bật/Tắt --}}
                <div class="mayhen_form_group">
                    <label class="mayhen_switch_label">
                        <input type="checkbox" name="is_active" value="1" {{ ($broadcastConfig['is_active'] ?? false) ? 'checked' : '' }} class="mayhen_checkbox" id="broadcastActiveSwitch">
                        <span class="mayhen_switch_custom"></span>
                        <div class="mayhen_switch_text">
                            <strong>Kích hoạt phát sóng ngay lên thiệp</strong>
                            <p>Khách mời khi truy cập thiệp sẽ nhìn thấy thông báo này tức thời.</p>
                        </div>
                    </label>
                </div>

                {{-- Chọn Mẫu Khẩn Cấp Dựng Sẵn --}}
                <div class="mayhen_form_group">
                    <label class="mayhen_label" for="presetSelector">Chọn mẫu tình huống khẩn cấp (Điền tự động):</label>
                    <select id="presetSelector" name="template_key" class="mayhen_select">
                        <option value="custom" {{ ($broadcastConfig['template_key'] ?? '') === 'custom' ? 'selected' : '' }}>-- Soạn thông báo tự do --</option>
                        @foreach($presets as $key => $preset)
                            <option value="{{ $key }}" {{ ($broadcastConfig['template_key'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $preset['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Mức Độ Khẩn Cấp --}}
                <div class="mayhen_form_group">
                    <label class="mayhen_label">Mức độ khẩn cấp &amp; Màu sắc hiển thị:</label>
                    <div class="mayhen_radio_group">
                        <label class="mayhen_radio_card">
                            <input type="radio" name="urgency_level" value="critical" {{ ($broadcastConfig['urgency_level'] ?? '') === 'critical' ? 'checked' : '' }}>
                            <span class="mayhen_radio_content">
                                <strong style="color: #8C2E24;">⚠️ Khẩn cấp cao (Đỏ Bordeaux)</strong>
                                <small>Đổi sảnh tiệc, dời ngày, sự cố khẩn cấp</small>
                            </span>
                        </label>
                        <label class="mayhen_radio_card">
                            <input type="radio" name="urgency_level" value="warning" {{ ($broadcastConfig['urgency_level'] ?? 'warning') === 'warning' ? 'checked' : '' }}>
                            <span class="mayhen_radio_content">
                                <strong style="color: #9C7A4A;">⚜️ Quan trọng (Hổ phách cổ điển)</strong>
                                <small>Lùi giờ, đổi bãi xe, lưu ý thời tiết</small>
                            </span>
                        </label>
                        <label class="mayhen_radio_card">
                            <input type="radio" name="urgency_level" value="info" {{ ($broadcastConfig['urgency_level'] ?? '') === 'info' ? 'checked' : '' }}>
                            <span class="mayhen_radio_content">
                                <strong style="color: #4A4641;">ℹ️ Nhắc nhở (Trắng ngà / Sage)</strong>
                                <small>Hotline hỗ trợ, lưu ý trang phục đón tiếp</small>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Kiểu Hiển Thị --}}
                <div class="mayhen_form_group">
                    <label class="mayhen_label">Hình thức hiển thị trên thiệp:</label>
                    <div class="mayhen_radio_group">
                        <label class="mayhen_radio_card">
                            <input type="radio" name="display_type" value="both" {{ ($broadcastConfig['display_type'] ?? 'both') === 'both' ? 'checked' : '' }}>
                            <span class="mayhen_radio_content">
                                <strong>Cả hai (Khuyên Dùng)</strong>
                                <small>Hiện Popup khi mở thiệp + Ghim Banner đầu trang</small>
                            </span>
                        </label>
                        <label class="mayhen_radio_card">
                            <input type="radio" name="display_type" value="sticky_banner" {{ ($broadcastConfig['display_type'] ?? '') === 'sticky_banner' ? 'checked' : '' }}>
                            <span class="mayhen_radio_content">
                                <strong>Chỉ Banner ghim đầu trang</strong>
                                <small>Thanh thông báo nổi nhẹ nhàng, có thể thu gọn</small>
                            </span>
                        </label>
                        <label class="mayhen_radio_card">
                            <input type="radio" name="display_type" value="modal_popup" {{ ($broadcastConfig['display_type'] ?? '') === 'modal_popup' ? 'checked' : '' }}>
                            <span class="mayhen_radio_content">
                                <strong>Chỉ Hộp thoại Popup</strong>
                                <small>Bắt buộc khách xác nhận "Đã hiểu" mới vào thiệp</small>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Tiêu Đề Thông Báo --}}
                <div class="mayhen_form_group">
                    <label class="mayhen_label" for="inputTitle">Tiêu đề thông báo ngắn gọn:</label>
                    <input type="text" name="title" id="inputTitle" value="{{ old('title', $broadcastConfig['title'] ?? '') }}" required placeholder="Ví dụ: Cập Nhật Khẩn: Thay Đổi Vị Trí Sảnh Tiệc" class="mayhen_input">
                </div>

                {{-- Nội Dung Thông Báo --}}
                <div class="mayhen_form_group">
                    <label class="mayhen_label" for="inputMessage">Nội dung chi tiết lời nhắn:</label>
                    <textarea name="message" id="inputMessage" rows="4" required placeholder="Ghi rõ lý do thay đổi và lời dặn dò chu đáo dành cho khách mời..." class="mayhen_textarea">{{ old('message', $broadcastConfig['message'] ?? '') }}</textarea>
                </div>

                {{-- Nút Hành Động Nhanh (Optional) --}}
                <div class="mayhen_row_2col">
                    <div class="mayhen_form_group">
                        <label class="mayhen_label" for="inputActionText">Tên nút hành động (Tùy chọn):</label>
                        <input type="text" name="action_text" id="inputActionText" value="{{ old('action_text', $broadcastConfig['action_text'] ?? '') }}" placeholder="Ví dụ: Gọi Lễ Tân / Xem Bản Đồ" class="mayhen_input">
                    </div>
                    <div class="mayhen_form_group">
                        <label class="mayhen_label" for="inputActionUrl">Đường link hoặc SĐT (Tùy chọn):</label>
                        <input type="text" name="action_url" id="inputActionUrl" value="{{ old('action_url', $broadcastConfig['action_url'] ?? '') }}" placeholder="Ví dụ: tel:0909123456 hoặc https://..." class="mayhen_input">
                    </div>
                </div>

                <div class="mayhen_form_actions">
                    <button type="submit" class="mayhen_btn_submit">Lưu &amp; Phát Thông Báo Ngay</button>
                </div>
            </form>
        </div>

        {{-- Cột Phải: Xem Trước Trực Quan (Live Preview) --}}
        <div class="mayhen_broadcast_col_preview">
            <div class="mayhen_broadcast_card">
                <h3 class="mayhen_broadcast_card_heading">Mô Phỏng Thực Tế Trên Thiệp</h3>
                <p class="mayhen_preview_desc">Hình ảnh thông báo hiển thị cho khách mời trên điện thoại di động:</p>

                {{-- Mockup Điện Thoại --}}
                <div class="mayhen_phone_mockup">
                    <div class="mayhen_phone_screen">
                        {{-- Mockup Banner --}}
                        <div id="previewBanner" class="mayhen_preview_banner mayhen_broadcast--{{ $broadcastConfig['urgency_level'] ?? 'warning' }}">
                            <div class="mayhen_preview_banner_row">
                                <span class="mayhen_preview_dot"></span>
                                <strong id="previewTitle" class="mayhen_preview_title">
                                    {{ !empty($broadcastConfig['title']) ? $broadcastConfig['title'] : 'Tiêu đề thông báo khẩn cấp' }}
                                </strong>
                            </div>
                            <p id="previewMessage" class="mayhen_preview_message">
                                {{ !empty($broadcastConfig['message']) ? $broadcastConfig['message'] : 'Nội dung thông báo chi tiết sẽ hiển thị tại đây khi bạn nhập vào form...' }}
                            </p>
                            <div id="previewActionRow" style="margin-top: 6px; display: {{ !empty($broadcastConfig['action_text']) ? 'block' : 'none' }};">
                                <span id="previewActionBtn" class="mayhen_preview_action_btn">
                                    {{ $broadcastConfig['action_text'] ?? 'Xem Chi Tiết' }}
                                </span>
                            </div>
                        </div>

                        {{-- Thân thiệp cưới giả lập bên dưới --}}
                        <div class="mayhen_preview_dummy_invitation">
                            <div class="mayhen_dummy_curtain">
                                <span class="mayhen_dummy_crest">⚜️</span>
                                <div class="mayhen_dummy_names">{{ $invitation->wedding->groom_name ?? 'Chú Rể' }} &amp; {{ $invitation->wedding->bride_name ?? 'Cô Dâu' }}</div>
                                <div class="mayhen_dummy_date">Thiệp Mời Cưới Trực Tuyến</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dữ liệu Mẫu Khẩn Cấp để JavaScript tự động nạp --}}
<script>
const PRESET_DATA = @json($presets);

document.addEventListener('DOMContentLoaded', function() {
    const presetSelect = document.getElementById('presetSelector');
    const inputTitle = document.getElementById('inputTitle');
    const inputMessage = document.getElementById('inputMessage');
    const inputActionText = document.getElementById('inputActionText');
    const inputActionUrl = document.getElementById('inputActionUrl');

    const previewTitle = document.getElementById('previewTitle');
    const previewMessage = document.getElementById('previewMessage');
    const previewBanner = document.getElementById('previewBanner');
    const previewActionRow = document.getElementById('previewActionRow');
    const previewActionBtn = document.getElementById('previewActionBtn');

    // Tự động điền dữ liệu khi chọn mẫu khẩn cấp
    presetSelect.addEventListener('change', function() {
        const key = this.value;
        if (key && PRESET_DATA[key]) {
            const preset = PRESET_DATA[key];
            inputTitle.value = preset.title;
            inputMessage.value = preset.message;
            inputActionText.value = preset.action_text || '';
            if (preset.action_type === 'tel') {
                inputActionUrl.value = 'tel:0909000111';
            } else if (preset.action_type === 'url') {
                inputActionUrl.value = 'https://maps.google.com';
            } else {
                inputActionUrl.value = '';
            }

            // Chọn radio urgency tương ứng
            const urgencyRadio = document.querySelector(`input[name="urgency_level"][value="${preset.urgency}"]`);
            if (urgencyRadio) {
                urgencyRadio.checked = true;
                updatePreviewUrgency(preset.urgency);
            }

            updateLivePreview();
        }
    });

    // Cập nhật live preview khi gõ text
    function updateLivePreview() {
        previewTitle.textContent = inputTitle.value.trim() || 'Tiêu đề thông báo khẩn cấp';
        previewMessage.textContent = inputMessage.value.trim() || 'Nội dung thông báo chi tiết...';
        if (inputActionText.value.trim()) {
            previewActionRow.style.display = 'block';
            previewActionBtn.textContent = inputActionText.value.trim();
        } else {
            previewActionRow.style.display = 'none';
        }
    }

    inputTitle.addEventListener('input', updateLivePreview);
    inputMessage.addEventListener('input', updateLivePreview);
    inputActionText.addEventListener('input', updateLivePreview);

    // Cập nhật màu sắc preview khi đổi radio urgency
    function updatePreviewUrgency(urgency) {
        previewBanner.className = `mayhen_preview_banner mayhen_broadcast--${urgency}`;
    }

    document.querySelectorAll('input[name="urgency_level"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) updatePreviewUrgency(this.value);
        });
    });
});
</script>

<style>
/* ==========================================================================
   BROADCAST ADMIN SUITE STYLES (ĐỀ ÁN 130)
   100% Pure Vanilla CSS — Prefix mayhen_broadcast_admin_*
   ========================================================================== */
.mayhen_broadcast_admin_container {
    max-width: 1200px;
    margin: 0 auto;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #1F1D1A;
}

.mayhen_broadcast_admin_badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(140, 46, 36, 0.12);
    color: #8C2E24;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}

.mayhen_broadcast_admin_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: #1F1D1A;
}

.mayhen_broadcast_admin_subtitle {
    font-size: 0.95rem;
    color: #635E56;
    line-height: 1.6;
    max-width: 820px;
    margin: 0 0 2rem;
}

.mayhen_broadcast_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.mayhen_broadcast_alert--success {
    background: #F4F7ED;
    border: 1px solid #C4D7AC;
    color: #375323;
}
.mayhen_broadcast_alert--danger {
    background: #FDF3F2;
    border: 1px solid #F5C2BE;
    color: #8C2E24;
}
.mayhen_broadcast_alert_icon {
    font-weight: bold;
    font-size: 1.1rem;
}

/* Status Card */
.mayhen_broadcast_status_card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    border: 1.5px solid;
    transition: all 0.3s ease;
}
.mayhen_broadcast_status_card.is-broadcasting {
    background: #FFF9F8;
    border-color: #8C2E24;
}
.mayhen_broadcast_status_card.is-idle {
    background: #FAF7F2;
    border-color: #D9D5CF;
}

.mayhen_broadcast_status_left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mayhen_status_indicator {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mayhen_status_dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
.is-broadcasting .mayhen_status_dot {
    background: #8C2E24;
    box-shadow: 0 0 0 0 rgba(140, 46, 36, 0.7);
    animation: mayhenBroadcastPulse 1.8s infinite;
}
.is-idle .mayhen_status_dot {
    background: #9E9992;
}

.mayhen_status_label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #7D7871;
    font-weight: 600;
}
.mayhen_status_title {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 2px 0;
}
.is-broadcasting .mayhen_status_title {
    color: #8C2E24;
}
.is-idle .mayhen_status_title {
    color: #635E56;
}
.mayhen_status_meta {
    font-size: 0.8rem;
    color: #7D7871;
    margin: 0;
}

.mayhen_btn_deactivate {
    padding: 8px 16px;
    background: #FFFFFF;
    border: 1px solid #8C2E24;
    color: #8C2E24;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_btn_deactivate:hover {
    background: #8C2E24;
    color: #FFFFFF;
}

/* Grid Layout */
.mayhen_broadcast_grid {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 2rem;
    align-items: start;
}
@media (max-width: 992px) {
    .mayhen_broadcast_grid {
        grid-template-columns: 1fr;
    }
}

.mayhen_broadcast_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.25);
    border-radius: 12px;
    padding: 1.75rem;
    box-shadow: 0 2px 12px rgba(31, 29, 26, 0.03);
}

.mayhen_broadcast_card_heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.35rem;
    font-weight: 600;
    margin: 0 0 1.25rem;
    border-bottom: 1px solid rgba(197, 168, 128, 0.2);
    padding-bottom: 0.5rem;
}

/* Form Styles */
.mayhen_form_group {
    margin-bottom: 1.25rem;
}
.mayhen_label {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #1F1D1A;
}
.mayhen_input, .mayhen_select, .mayhen_textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #D9D5CF;
    border-radius: 6px;
    font-size: 0.9rem;
    box-sizing: border-box;
    font-family: inherit;
    transition: border-color 0.2s ease;
}
.mayhen_input:focus, .mayhen_select:focus, .mayhen_textarea:focus {
    outline: none;
    border-color: #9C7A4A;
}

.mayhen_switch_label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
}
.mayhen_checkbox {
    display: none;
}
.mayhen_switch_custom {
    width: 40px;
    height: 22px;
    background: #D9D5CF;
    border-radius: 11px;
    position: relative;
    flex-shrink: 0;
    transition: background 0.25s ease;
    margin-top: 2px;
}
.mayhen_switch_custom::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    transition: transform 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}
.mayhen_checkbox:checked + .mayhen_switch_custom {
    background: #8C2E24;
}
.mayhen_checkbox:checked + .mayhen_switch_custom::after {
    transform: translateX(18px);
}
.mayhen_switch_text strong {
    display: block;
    font-size: 0.92rem;
    font-weight: 600;
    color: #1F1D1A;
}
.mayhen_switch_text p {
    font-size: 0.8rem;
    color: #7D7871;
    margin: 2px 0 0;
}

/* Radio Group Cards */
.mayhen_radio_group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.mayhen_radio_card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    border: 1px solid #D9D5CF;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_radio_card:hover {
    border-color: #9C7A4A;
    background: #FDFBF8;
}
.mayhen_radio_card input[type="radio"] {
    margin-top: 4px;
    accent-color: #9C7A4A;
}
.mayhen_radio_content strong {
    display: block;
    font-size: 0.88rem;
}
.mayhen_radio_content small {
    display: block;
    font-size: 0.78rem;
    color: #7D7871;
    margin-top: 2px;
}

.mayhen_row_2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
@media (max-width: 640px) {
    .mayhen_row_2col {
        grid-template-columns: 1fr;
    }
}

.mayhen_btn_submit {
    width: 100%;
    padding: 12px;
    background: #1F1D1A;
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.mayhen_btn_submit:hover {
    background: #38342E;
}

/* Mockup Phone & Preview */
.mayhen_preview_desc {
    font-size: 0.85rem;
    color: #7D7871;
    margin: -0.5rem 0 1rem;
}
.mayhen_phone_mockup {
    background: #1F1D1A;
    border-radius: 24px;
    padding: 12px;
    box-shadow: 0 16px 36px rgba(31, 29, 26, 0.15);
}
.mayhen_phone_screen {
    background: #FAF7F2;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    min-height: 400px;
    display: flex;
    flex-direction: column;
}
.mayhen_preview_banner {
    padding: 10px 12px;
    transition: all 0.25s ease;
}
.mayhen_preview_banner_row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}
.mayhen_preview_dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #D4AF37;
    animation: mayhenBroadcastPulse 1.8s infinite;
}
.mayhen_preview_title {
    font-size: 0.82rem;
    font-weight: 700;
}
.mayhen_preview_message {
    font-size: 0.75rem;
    line-height: 1.45;
    margin: 0;
    opacity: 0.95;
}
.mayhen_preview_action_btn {
    display: inline-block;
    padding: 3px 10px;
    background: #D4AF37;
    color: #1F1D1A;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 999px;
}

.mayhen_preview_dummy_invitation {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    text-align: center;
}
.mayhen_dummy_crest {
    font-size: 1.8rem;
    display: block;
    margin-bottom: 6px;
}
.mayhen_dummy_names {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1F1D1A;
}
.mayhen_dummy_date {
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9C7A4A;
    margin-top: 4px;
}
</style>
@endsection

