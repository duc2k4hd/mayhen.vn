@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quản Lý Album 10 Ảnh Cưới Nghệ Thuật — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
    $photos = $galleryConfig['photos'] ?? [];
@endphp

@section('account_breadcrumb', 'Album 10 Ảnh Cưới')

@section('account_content')
<div class="mayhen_gallery_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_gallery_admin_header">
        <div class="mayhen_gallery_admin_headline">
            <div class="mayhen_gallery_admin_badge">Curated 10-Photo Editorial Gallery Suite</div>
            <h1 class="mayhen_gallery_admin_title">Album 10 Ảnh Cưới Nghệ Thuật &amp; Bố Cục Tạp Chí</h1>
            <p class="mayhen_gallery_admin_subtitle">
                Tùy chỉnh 10 khoảnh khắc hạnh phúc nhất của <strong>{{ $coupleName }}</strong>, lựa chọn phong cách bố cục tạp chí và kích hoạt chế độ xem toàn màn hình (Lightbox) tương tác mượt mà cho quan khách.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_gallery_alert mayhen_gallery_alert--success">
            <span class="mayhen_gallery_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_gallery_alert mayhen_gallery_alert--danger">
            <span class="mayhen_gallery_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('account.gallery.update', $invitation->id) }}" method="POST" id="galleryForm" class="mayhen_gallery_form">
        @csrf

        {{-- Section 1: Cấu Hình Hiển Thị & Bố Cục Tạp Chí --}}
        <div class="mayhen_gallery_card">
            <div class="mayhen_gallery_card_header">
                <div class="mayhen_gallery_card_icon">🖼️</div>
                <div class="mayhen_gallery_card_title_row">
                    <div>
                        <h2 class="mayhen_gallery_card_title">1. Phong Cách Bố Cục &amp; Cài Đặt Chung</h2>
                        <p class="mayhen_gallery_card_desc">Chọn 1 trong 3 phong cách trình diễn ảnh cưới Quiet Luxury tiêu chuẩn</p>
                    </div>
                    <label class="mayhen_gallery_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($galleryConfig['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_gallery_slider"></span>
                        <span class="mayhen_gallery_switch_text">Hiển thị Album</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_gallery_card_body">
                <div class="mayhen_gallery_form_row" style="margin-bottom: 24px;">
                    <div class="mayhen_gallery_field">
                        <label class="mayhen_gallery_label">Tiêu Đề Album</label>
                        <input type="text" name="title" class="mayhen_gallery_input" value="{{ old('title', $galleryConfig['title']) }}" required maxlength="150" placeholder="Khoảnh Khắc Hạnh Phúc">
                    </div>
                    <div class="mayhen_gallery_field">
                        <label class="mayhen_gallery_label">Lời Dẫn Nhập (Subtitle)</label>
                        <input type="text" name="subtitle" class="mayhen_gallery_input" value="{{ old('subtitle', $galleryConfig['subtitle']) }}" maxlength="255" placeholder="Những thước ảnh ghi dấu tình yêu và lời hẹn ước trăm năm của chúng mình">
                    </div>
                </div>

                <div class="mayhen_gallery_field" style="margin-bottom: 24px;">
                    <label class="mayhen_gallery_label">Phong Cách Bố Cục Album Ảnh</label>
                    <div class="mayhen_gallery_layouts_grid">
                        @foreach($presetLayouts as $key => $layout)
                            <label class="mayhen_gallery_layout_card {{ ($galleryConfig['layout_style'] ?? 'editorial_masonry') === $key ? 'selected' : '' }}">
                                <input type="radio" name="layout_style" value="{{ $key }}" {{ ($galleryConfig['layout_style'] ?? 'editorial_masonry') === $key ? 'checked' : '' }} class="mayhen_gallery_layout_radio">
                                <div class="mayhen_layout_icon">{{ $layout['icon'] }}</div>
                                <div class="mayhen_layout_name">{{ $layout['name'] }}</div>
                                <div class="mayhen_layout_desc">{{ $layout['description'] }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_gallery_checkbox_field">
                    <label class="mayhen_gallery_checkbox_label">
                        <input type="checkbox" name="lightbox_enabled" value="1" {{ !empty($galleryConfig['lightbox_enabled']) ? 'checked' : '' }}>
                        <span>Cho phép quan khách chạm vào ảnh để phóng to toàn màn hình (Chế độ Lightbox đen tuyền sang trọng)</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 2: Quản Lý 10 Slot Ảnh Cưới Nghệ Thuật --}}
        <div class="mayhen_gallery_card">
            <div class="mayhen_gallery_card_header">
                <div class="mayhen_gallery_card_icon">📸</div>
                <div class="mayhen_gallery_card_title_row">
                    <div>
                        <h2 class="mayhen_gallery_card_title">2. Danh Sách 10 Bức Ảnh Cưới &amp; Chú Thích Kỷ Niệm</h2>
                        <p class="mayhen_gallery_card_desc">Thay đổi ảnh, nhập lời nhắn gửi cho từng khoảnh khắc và điều chỉnh thứ tự xuất hiện</p>
                    </div>
                </div>
            </div>

            <div class="mayhen_gallery_card_body">
                <div class="mayhen_gallery_slots_grid" id="gallerySlotsGrid">
                    @for($i = 0; $i < 10; $i++)
                        @php
                            $photo = $photos[$i] ?? ['url' => '', 'caption' => '', 'alt' => ''];
                        @endphp
                        <div class="mayhen_gallery_slot_item" data-slot-index="{{ $i }}" id="slot-item-{{ $i }}">
                            <div class="mayhen_slot_badge">Slot {{ sprintf('%02d', $i + 1) }}</div>
                            
                            {{-- Preview Thumbnail --}}
                            <div class="mayhen_slot_thumb_box">
                                <img src="{{ $photo['url'] }}" alt="Ảnh cưới slot {{ $i + 1 }}" class="mayhen_slot_thumb" id="thumb-{{ $i }}">
                                <div class="mayhen_slot_thumb_overlay">
                                    <label class="mayhen_slot_upload_btn" title="Tải ảnh mới từ máy tính">
                                        <input type="file" class="mayhen_file_input" accept="image/*" data-target-index="{{ $i }}">
                                        <span>📷 Đổi Ảnh</span>
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="photos[{{ $i }}][url]" value="{{ $photo['url'] }}" id="url-input-{{ $i }}">

                            <div class="mayhen_slot_inputs">
                                <div class="mayhen_slot_caption_field">
                                    <label class="mayhen_slot_caption_label">Chú thích ảnh:</label>
                                    <input type="text" name="photos[{{ $i }}][caption]" value="{{ $photo['caption'] }}" class="mayhen_slot_caption_input" placeholder="Ví dụ: Lễ cầu hôn tại Đà Lạt..." maxlength="255">
                                </div>

                                <div class="mayhen_slot_actions">
                                    <button type="button" class="mayhen_slot_action_btn btn-move-up" data-index="{{ $i }}" title="Di chuyển lên trên" {{ $i === 0 ? 'disabled' : '' }}>
                                        ▲ Lên
                                    </button>
                                    <button type="button" class="mayhen_slot_action_btn btn-move-down" data-index="{{ $i }}" title="Di chuyển xuống dưới" {{ $i === 9 ? 'disabled' : '' }}>
                                        ▼ Xuống
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- Thanh Nút Lưu Cố Định --}}
        <div class="mayhen_gallery_actions_bar">
            <a href="{{ route('account.invitations') }}" class="mayhen_gallery_btn mayhen_gallery_btn--secondary">
                ← Quay lại danh sách
            </a>
            <button type="submit" class="mayhen_gallery_btn mayhen_gallery_btn--primary" id="saveGalleryBtn">
                ✓ Lưu Cấu Hình Album Ảnh
            </button>
        </div>
    </form>
</div>

{{-- Pure Vanilla CSS cho Gallery Studio --}}
<style>
.mayhen_gallery_admin_container {
    max-width: 1040px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #2C2824;
}
.mayhen_gallery_admin_header {
    margin-bottom: 28px;
}
.mayhen_gallery_admin_badge {
    display: inline-block;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #9C7A4A;
    font-weight: 600;
    margin-bottom: 6px;
}
.mayhen_gallery_admin_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 28px;
    color: #1F1D1A;
    margin: 0 0 8px;
    font-weight: 500;
}
.mayhen_gallery_admin_subtitle {
    font-size: 14px;
    color: #6B635B;
    line-height: 1.6;
    margin: 0;
}

/* Alerts */
.mayhen_gallery_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
}
.mayhen_gallery_alert--success {
    background: #E8F5E9;
    border: 1px solid #C8E6C9;
    color: #2E7D32;
}
.mayhen_gallery_alert--danger {
    background: #FFEBEE;
    border: 1px solid #FFCDD2;
    color: #C62828;
}
.mayhen_gallery_alert_icon {
    font-weight: bold;
    font-size: 16px;
}

/* Cards */
.mayhen_gallery_card {
    background: #FFFFFF;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}
.mayhen_gallery_card_header {
    padding: 20px 24px;
    background: #FAF8F5;
    border-bottom: 1px solid #EFE8DD;
    display: flex;
    align-items: center;
    gap: 16px;
}
.mayhen_gallery_card_icon {
    font-size: 26px;
}
.mayhen_gallery_card_title_row {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.mayhen_gallery_card_title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 4px;
    color: #1F1D1A;
}
.mayhen_gallery_card_desc {
    font-size: 13px;
    color: #8C827A;
    margin: 0;
}
.mayhen_gallery_card_body {
    padding: 24px;
}

/* Switch */
.mayhen_gallery_switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.mayhen_gallery_switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.mayhen_gallery_slider {
    position: relative;
    width: 44px;
    height: 24px;
    background-color: #D8CEBD;
    border-radius: 24px;
    transition: 0.3s;
}
.mayhen_gallery_slider:before {
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
.mayhen_gallery_switch input:checked + .mayhen_gallery_slider {
    background-color: #9C7A4A;
}
.mayhen_gallery_switch input:checked + .mayhen_gallery_slider:before {
    transform: translateX(20px);
}
.mayhen_gallery_switch_text {
    font-size: 13px;
    font-weight: 500;
    color: #4A453E;
}

/* Form inputs */
.mayhen_gallery_form_row {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 18px;
}
@media (max-width: 640px) {
    .mayhen_gallery_form_row {
        grid-template-columns: 1fr;
    }
}
.mayhen_gallery_field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.mayhen_gallery_label {
    font-size: 13px;
    font-weight: 600;
    color: #4A453E;
}
.mayhen_gallery_input {
    padding: 10px 14px;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    font-size: 14px;
    color: #1F1D1A;
    background: #FFF;
    outline: none;
    transition: border 0.2s;
}
.mayhen_gallery_input:focus {
    border-color: #9C7A4A;
}

/* Layout cards */
.mayhen_gallery_layouts_grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
@media (max-width: 768px) {
    .mayhen_gallery_layouts_grid {
        grid-template-columns: 1fr;
    }
}
.mayhen_gallery_layout_card {
    border: 2px solid #EFE8DD;
    background: #FAF8F5;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
}
.mayhen_gallery_layout_card.selected {
    border-color: #9C7A4A;
    background: #FFFDF9;
    box-shadow: 0 4px 12px rgba(156,122,74,0.12);
}
.mayhen_gallery_layout_radio {
    position: absolute;
    top: 14px;
    right: 14px;
    accent-color: #9C7A4A;
}
.mayhen_layout_icon {
    font-size: 22px;
}
.mayhen_layout_name {
    font-size: 14px;
    font-weight: 600;
    color: #1F1D1A;
}
.mayhen_layout_desc {
    font-size: 12px;
    color: #6B635B;
    line-height: 1.4;
}

/* Checkbox field */
.mayhen_gallery_checkbox_field {
    background: #FAF8F5;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px dashed #D8CEBD;
}
.mayhen_gallery_checkbox_label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #4A453E;
    cursor: pointer;
}
.mayhen_gallery_checkbox_label input {
    accent-color: #9C7A4A;
    width: 16px;
    height: 16px;
}

/* 10 Slots Grid */
.mayhen_gallery_slots_grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
@media (max-width: 768px) {
    .mayhen_gallery_slots_grid {
        grid-template-columns: 1fr;
    }
}
.mayhen_gallery_slot_item {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 14px;
    display: flex;
    gap: 14px;
    position: relative;
    transition: all 0.2s ease;
}
.mayhen_gallery_slot_item:hover {
    border-color: #C5A880;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}
.mayhen_slot_badge {
    position: absolute;
    top: -8px;
    left: 12px;
    background: #9C7A4A;
    color: #FFF;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    letter-spacing: 1px;
}
.mayhen_slot_thumb_box {
    position: relative;
    width: 100px;
    height: 125px;
    flex-shrink: 0;
    border-radius: 6px;
    overflow: hidden;
    background: #EFE8DD;
}
.mayhen_slot_thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.mayhen_slot_thumb_overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.mayhen_slot_thumb_box:hover .mayhen_slot_thumb_overlay {
    opacity: 1;
}
.mayhen_slot_upload_btn {
    color: #FFF;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    background: rgba(156,122,74,0.9);
    padding: 4px 8px;
    border-radius: 4px;
}
.mayhen_file_input {
    display: none;
}
.mayhen_slot_inputs {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.mayhen_slot_caption_field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.mayhen_slot_caption_label {
    font-size: 11px;
    font-weight: 600;
    color: #8C827A;
    text-transform: uppercase;
}
.mayhen_slot_caption_input {
    padding: 8px 10px;
    border: 1px solid #D8CEBD;
    border-radius: 6px;
    font-size: 13px;
    background: #FFF;
    outline: none;
    transition: border 0.2s;
}
.mayhen_slot_caption_input:focus {
    border-color: #9C7A4A;
}
.mayhen_slot_actions {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}
.mayhen_slot_action_btn {
    padding: 4px 10px;
    font-size: 11px;
    background: #FFF;
    border: 1px solid #D8CEBD;
    border-radius: 4px;
    color: #4A453E;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_slot_action_btn:hover:not(:disabled) {
    background: #9C7A4A;
    color: #FFF;
    border-color: #9C7A4A;
}
.mayhen_slot_action_btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Actions Bar */
.mayhen_gallery_actions_bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 0;
}
.mayhen_gallery_btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
}
.mayhen_gallery_btn--primary {
    background: #9C7A4A;
    color: #FFF;
}
.mayhen_gallery_btn--primary:hover {
    background: #85673E;
}
.mayhen_gallery_btn--secondary {
    background: #FFF;
    color: #4A453E;
    border: 1px solid #D8CEBD;
}
.mayhen_gallery_btn--secondary:hover {
    background: #FAF8F5;
}
</style>

{{-- Pure Vanilla JS cho Studio Tương Tác --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Radio Layout Card Active Highlight
    const layoutRadios = document.querySelectorAll('.mayhen_gallery_layout_radio');
    const layoutCards = document.querySelectorAll('.mayhen_gallery_layout_card');

    layoutRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            layoutCards.forEach(c => c.classList.remove('selected'));
            if (this.checked) {
                this.closest('.mayhen_gallery_layout_card').classList.add('selected');
            }
        });
    });

    // 2. Upload AJAX từng ảnh lẻ
    const uploadInputs = document.querySelectorAll('.mayhen_file_input');
    const uploadUrl = '{{ route('account.gallery.upload_photo', $invitation->id) }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    uploadInputs.forEach(input => {
        input.addEventListener('change', async function () {
            if (!this.files || !this.files[0]) return;
            const targetIndex = this.getAttribute('data-target-index');
            const file = this.files[0];

            const formData = new FormData();
            formData.append('photo', file);

            const thumb = document.getElementById('thumb-' + targetIndex);
            const urlInput = document.getElementById('url-input-' + targetIndex);

            const originalOpacity = thumb.style.opacity;
            thumb.style.opacity = '0.4';

            try {
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success && data.url) {
                    thumb.src = data.url;
                    urlInput.value = data.url;
                } else {
                    alert(data.message || 'Lỗi tải ảnh lên');
                }
            } catch (err) {
                alert('Có lỗi xảy ra khi kết nối máy chủ.');
            } finally {
                thumb.style.opacity = originalOpacity || '1';
                input.value = '';
            }
        });
    });

    // 3. Move Up / Move Down Reordering
    const upBtns = document.querySelectorAll('.btn-move-up');
    const downBtns = document.querySelectorAll('.btn-move-down');

    function swapSlots(idxA, idxB) {
        if (idxA < 0 || idxA > 9 || idxB < 0 || idxB > 9) return;

        const thumbA = document.getElementById('thumb-' + idxA);
        const thumbB = document.getElementById('thumb-' + idxB);
        const urlA = document.getElementById('url-input-' + idxA);
        const urlB = document.getElementById('url-input-' + idxB);

        const slotA = document.getElementById('slot-item-' + idxA);
        const slotB = document.getElementById('slot-item-' + idxB);

        const captionA = slotA.querySelector('.mayhen_slot_caption_input');
        const captionB = slotB.querySelector('.mayhen_slot_caption_input');

        // Swap values
        const tempThumb = thumbA.src;
        thumbA.src = thumbB.src;
        thumbB.src = tempThumb;

        const tempUrl = urlA.value;
        urlA.value = urlB.value;
        urlB.value = tempUrl;

        const tempCaption = captionA.value;
        captionA.value = captionB.value;
        captionB.value = tempCaption;
    }

    upBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-index'), 10);
            if (idx > 0) swapSlots(idx, idx - 1);
        });
    });

    downBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-index'), 10);
            if (idx < 9) swapSlots(idx, idx + 1);
        });
    });
});
</script>
@endsection
