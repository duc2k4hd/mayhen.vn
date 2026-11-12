@extends('client.pages.account.layout')

@section('account_title', 'Studio Thẻ Kẹp Thiệp Giấy & QR Để Bàn | Mây Hẹn')

@section('account_content')
<div class="mayhen_companion_container">
    {{-- Top Header Ribbon --}}
    <div class="mayhen_companion_header">
        <div>
            <div class="mayhen_companion_kicker">QUIET LUXURY PRINTABLE COMPANION • IDEA-A06</div>
            <h1 class="mayhen_companion_title">Thẻ Kẹp Kèm Thiệp Giấy &amp; QR Để Bàn</h1>
            <p class="mayhen_companion_desc">
                Thiết kế thẻ nhỏ kẹp phong bì thiệp giấy truyền thống hoặc thẻ để bàn tiệc cưới, tích hợp mã QR dẫn thẳng vào thiệp online của đôi uyên ương.
            </p>
        </div>
        <div class="mayhen_companion_actions">
            <a href="{{ route('account.invitations') }}" class="mayhen_companion_btn mayhen_companion_btn--outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Quay Lại Danh Sách</span>
            </a>
            <a href="{{ route('account.companion_card.print', $invitation->id) }}" target="_blank" class="mayhen_companion_btn mayhen_companion_btn--gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>In Khổ A4 Nhà In</span>
            </a>
        </div>
    </div>

    {{-- Grid 2 Cột: Bảng Điều Khiển & Khung Xem Trước Trực Quan --}}
    <div class="mayhen_companion_grid">
        {{-- Cột Trái: Bảng Điều Khiển Tùy Biến --}}
        <div class="mayhen_companion_panel">
            <form action="{{ route('account.companion_card.update', $invitation->id) }}" method="POST" id="companionCardForm">
                @csrf

                {{-- Khối 1: Kích Thước In Tiêu Chuẩn --}}
                <div class="mayhen_companion_card">
                    <div class="mayhen_companion_card_head">
                        <span class="mayhen_companion_card_icon">📏</span>
                        <h3 class="mayhen_companion_card_title">1. Chọn Khổ In Chuẩn Nhà In</h3>
                    </div>
                    <div class="mayhen_companion_options_grid">
                        @foreach($sizes as $sKey => $sMeta)
                            <label class="mayhen_companion_size_option {{ ($config['size'] ?? 'namecard') === $sKey ? 'active' : '' }}">
                                <input type="radio" name="size" value="{{ $sKey }}" {{ ($config['size'] ?? 'namecard') === $sKey ? 'checked' : '' }} class="sr-only">
                                <div class="mayhen_companion_size_name">{{ $sMeta['short_name'] }}</div>
                                <div class="mayhen_companion_size_desc">{{ $sMeta['desc'] }}</div>
                                <div class="mayhen_companion_size_badge">{{ $sMeta['items_per_a4'] }} thẻ / tờ A4</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Khối 2: Tông Màu Quiet Luxury --}}
                <div class="mayhen_companion_card">
                    <div class="mayhen_companion_card_head">
                        <span class="mayhen_companion_card_icon">🎨</span>
                        <h3 class="mayhen_companion_card_title">2. Bảng Màu Hoàng Gia Tuyển Chọn</h3>
                    </div>
                    <div class="mayhen_companion_palette_grid">
                        @foreach($palettes as $pKey => $pMeta)
                            <label class="mayhen_companion_palette_item {{ ($config['palette'] ?? 'gold') === $pKey ? 'active' : '' }}">
                                <input type="radio" name="palette" value="{{ $pKey }}" {{ ($config['palette'] ?? 'gold') === $pKey ? 'checked' : '' }} class="sr-only">
                                <div class="mayhen_companion_color_swatch" style="background-color: {{ $pMeta['bg_color'] }}; border: 2px solid {{ $pMeta['border_color'] }};">
                                    <div class="mayhen_companion_color_dot" style="background-color: {{ $pMeta['accent_color'] }};"></div>
                                </div>
                                <span class="mayhen_companion_palette_name">{{ $pMeta['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Khối 3: Phong Cách Họa Tiết Viền --}}
                <div class="mayhen_companion_card">
                    <div class="mayhen_companion_card_head">
                        <span class="mayhen_companion_card_icon">⚜️</span>
                        <h3 class="mayhen_companion_card_title">3. Phong Cách Họa Tiết Khung Viền</h3>
                    </div>
                    <div class="mayhen_companion_motif_list">
                        @foreach($motifs as $mKey => $mName)
                            <label class="mayhen_companion_motif_item {{ ($config['motif'] ?? 'royal_crest') === $mKey ? 'active' : '' }}">
                                <input type="radio" name="motif" value="{{ $mKey }}" {{ ($config['motif'] ?? 'royal_crest') === $mKey ? 'checked' : '' }} class="sr-only">
                                <span class="mayhen_companion_radio_bullet"></span>
                                <span class="mayhen_companion_motif_label">{{ $mName }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Khối 4: Nội Dung Thông Điệp Lời Mời --}}
                <div class="mayhen_companion_card">
                    <div class="mayhen_companion_card_head">
                        <span class="mayhen_companion_card_icon">✍️</span>
                        <h3 class="mayhen_companion_card_title">4. Nội Dung &amp; Thông Điệp</h3>
                    </div>

                    <div class="mayhen_companion_field">
                        <label class="mayhen_companion_label">Tiêu Đề Phụ (Kicker)</label>
                        <input type="text" name="kicker" id="inpKicker" class="mayhen_companion_input" value="{{ old('kicker', $config['kicker']) }}" maxlength="100">
                    </div>

                    <div class="mayhen_companion_field">
                        <label class="mayhen_companion_label">Tên Cặp Đôi Hiển Thị <span class="required">*</span></label>
                        <input type="text" name="couple_title" id="inpCouple" class="mayhen_companion_input" value="{{ old('couple_title', $config['couple_title']) }}" required maxlength="150">
                    </div>

                    <div class="mayhen_companion_field">
                        <label class="mayhen_companion_label">Lời Hướng Dẫn Quét QR</label>
                        <textarea name="instructions" id="inpInstructions" class="mayhen_companion_textarea" rows="2" maxlength="300">{{ old('instructions', $config['instructions']) }}</textarea>
                    </div>

                    <div class="mayhen_companion_field">
                        <label class="mayhen_companion_label">Nhãn Dưới Mã QR</label>
                        <input type="text" name="qr_label" id="inpQrLabel" class="mayhen_companion_input" value="{{ old('qr_label', $config['qr_label']) }}" required maxlength="80">
                    </div>

                    <div class="mayhen_companion_toggles">
                        <label class="mayhen_companion_checkbox">
                            <input type="checkbox" name="show_date" value="1" id="chkDate" {{ !empty($config['show_date']) ? 'checked' : '' }}>
                            <span>Hiển thị Ngày cưới</span>
                        </label>
                        <label class="mayhen_companion_checkbox">
                            <input type="checkbox" name="show_venue" value="1" id="chkVenue" {{ !empty($config['show_venue']) ? 'checked' : '' }}>
                            <span>Hiển thị Sảnh tiệc / Địa điểm</span>
                        </label>
                    </div>
                </div>

                {{-- Nút Lưu Cấu Hình --}}
                <div class="mayhen_companion_submit_row">
                    <button type="submit" class="mayhen_companion_btn mayhen_companion_btn--primary" id="btnSaveConfig">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Lưu Cấu Hình Mặc Định</span>
                    </button>
                    <a href="{{ route('account.companion_card.download_svg', $invitation->id) }}" class="mayhen_companion_btn mayhen_companion_btn--outline" id="btnDownloadSvg">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span>Tải File Vector SVG</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- Cột Phải: Khung Xem Trước Trực Quan Thời Gian Thực (Live Preview) --}}
        <div class="mayhen_companion_preview_pane">
            <div class="mayhen_companion_preview_header">
                <div>
                    <h4 class="mayhen_companion_preview_title">Bản Xem Trước Trực Quan</h4>
                    <span class="mayhen_companion_preview_subtitle" id="previewSizeLabel">Khổ: Namecard 90 × 54 mm</span>
                </div>
                <div class="mayhen_companion_badge">Tỷ Lệ 1:1 Chuẩn In</div>
            </div>

            {{-- Vùng hiển thị Thẻ Kẹp Thật --}}
            <div class="mayhen_companion_stage" id="stageWrap">
                <div class="mayhen_companion_card_render size-namecard palette-gold motif-royal_crest" id="cardRender">
                    <div class="mayhen_card_border_outer"></div>
                    <div class="mayhen_card_border_inner"></div>

                    <div class="mayhen_card_content">
                        <div class="mayhen_card_left">
                            <div class="mayhen_card_kicker" id="txtKicker">{{ $config['kicker'] }}</div>
                            <h2 class="mayhen_card_couple" id="txtCouple">{{ $config['couple_title'] }}</h2>
                            <div class="mayhen_card_line"></div>
                            
                            <div class="mayhen_card_meta" id="txtMeta">
                                @if(!empty($config['show_date']) && $wedding && $wedding->wedding_date)
                                    <span id="txtDate">{{ date('d.m.Y', strtotime($wedding->wedding_date)) }}</span>
                                @endif
                                @if(!empty($config['show_venue']) && $wedding && $wedding->venue_name)
                                    <span id="txtSep">•</span>
                                    <span id="txtVenue">{{ $wedding->venue_name }}</span>
                                @endif
                            </div>

                            <p class="mayhen_card_instructions" id="txtInstructions">{{ $config['instructions'] }}</p>
                            <div class="mayhen_card_url">🌐 {{ url("/thiep/{$invitation->slug}") }}</div>
                        </div>

                        <div class="mayhen_card_right">
                            <div class="mayhen_card_qr_frame">
                                <div class="mayhen_card_qr_svg" id="qrContainer">
                                    {!! $qrSvg !!}
                                </div>
                            </div>
                            <div class="mayhen_card_qr_label" id="txtQrLabel">{{ $config['qr_label'] }}</div>
                        </div>
                    </div>

                    <div class="mayhen_card_footer_brand">MÂY HẸN • MAYHEN.VN</div>
                </div>
            </div>

            {{-- Khối In Hàng Loạt Cho Danh Sách Khách --}}
            <div class="mayhen_companion_batch_box">
                <div class="mayhen_companion_batch_head">
                    <span class="mayhen_companion_batch_icon">👥</span>
                    <div>
                        <strong>In Hàng Loạt Có Tên Khách Cá Nhân</strong>
                        <p>Tự động sinh mã QR riêng cho từng khách trong danh sách kèm tên xưng hô trang trọng.</p>
                    </div>
                </div>
                <div class="mayhen_companion_batch_actions">
                    <a href="{{ route('account.companion_card.print', ['id' => $invitation->id, 'mode' => 'guest_batch']) }}" target="_blank" class="mayhen_companion_btn mayhen_companion_btn--gold mayhen_companion_btn--sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        <span>In Khổ A4 Kèm Tên ({{ count($guests) }} Khách)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pure Vanilla JS Xử Lý Đồng Bộ Xem Trước Thời Gian Thực --}}
<script>
(function() {
    'use strict';

    const cardRender = document.getElementById('cardRender');
    const sizeRadios = document.querySelectorAll('input[name="size"]');
    const paletteRadios = document.querySelectorAll('input[name="palette"]');
    const motifRadios = document.querySelectorAll('input[name="motif"]');
    const previewSizeLabel = document.getElementById('previewSizeLabel');

    const inpKicker = document.getElementById('inpKicker');
    const inpCouple = document.getElementById('inpCouple');
    const inpInstructions = document.getElementById('inpInstructions');
    const inpQrLabel = document.getElementById('inpQrLabel');
    const chkDate = document.getElementById('chkDate');
    const chkVenue = document.getElementById('chkVenue');

    const txtKicker = document.getElementById('txtKicker');
    const txtCouple = document.getElementById('txtCouple');
    const txtInstructions = document.getElementById('txtInstructions');
    const txtQrLabel = document.getElementById('txtQrLabel');
    const txtDate = document.getElementById('txtDate');
    const txtVenue = document.getElementById('txtVenue');
    const txtSep = document.getElementById('txtSep');

    // Cập nhật khổ in
    sizeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.mayhen_companion_size_option').forEach(el => el.classList.remove('active'));
            this.closest('.mayhen_companion_size_option').classList.add('active');
            
            cardRender.classList.remove('size-namecard', 'size-postcard', 'size-square');
            cardRender.classList.add('size-' + this.value);

            const labels = {
                'namecard': 'Khổ: Namecard 90 × 54 mm',
                'postcard': 'Khổ: Bưu thiếp Postcard 100 × 150 mm',
                'square': 'Khổ: Thẻ vuông 100 × 100 mm'
            };
            if (previewSizeLabel) previewSizeLabel.textContent = labels[this.value] || '';
        });
    });

    // Cập nhật tông màu
    paletteRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.mayhen_companion_palette_item').forEach(el => el.classList.remove('active'));
            this.closest('.mayhen_companion_palette_item').classList.add('active');

            cardRender.classList.remove('palette-gold', 'palette-charcoal', 'palette-sage', 'palette-ruby', 'palette-dusty_rose');
            cardRender.classList.add('palette-' + this.value);
        });
    });

    // Cập nhật hoa văn viền
    motifRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.mayhen_companion_motif_item').forEach(el => el.classList.remove('active'));
            this.closest('.mayhen_companion_motif_item').classList.add('active');

            cardRender.classList.remove('motif-royal_crest', 'motif-minimal_editorial', 'motif-floral_botanical');
            cardRender.classList.add('motif-' + this.value);
        });
    });

    // Cập nhật văn bản thời gian thực
    if (inpKicker && txtKicker) {
        inpKicker.addEventListener('input', () => txtKicker.textContent = inpKicker.value);
    }
    if (inpCouple && txtCouple) {
        inpCouple.addEventListener('input', () => txtCouple.textContent = inpCouple.value);
    }
    if (inpInstructions && txtInstructions) {
        inpInstructions.addEventListener('input', () => txtInstructions.textContent = inpInstructions.value);
    }
    if (inpQrLabel && txtQrLabel) {
        inpQrLabel.addEventListener('input', () => txtQrLabel.textContent = inpQrLabel.value);
    }
    if (chkDate) {
        chkDate.addEventListener('change', () => {
            if (txtDate) txtDate.style.display = chkDate.checked ? 'inline' : 'none';
            if (txtSep) txtSep.style.display = (chkDate.checked && chkVenue && chkVenue.checked) ? 'inline' : 'none';
        });
    }
    if (chkVenue) {
        chkVenue.addEventListener('change', () => {
            if (txtVenue) txtVenue.style.display = chkVenue.checked ? 'inline' : 'none';
            if (txtSep) txtSep.style.display = (chkDate && chkDate.checked && chkVenue.checked) ? 'inline' : 'none';
        });
    }
})();
</script>

<style>
/* Pure CSS BEM cho Phân Hệ Studio Thẻ Kẹp Thiệp Giấy (IDEA-A06) */
.mayhen_companion_container {
    padding: 1.5rem 0 3rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1F1D1A;
}

.mayhen_companion_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(197, 168, 128, 0.25);
}

.mayhen_companion_kicker {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #9C7A4A;
    margin-bottom: 0.4rem;
}

.mayhen_companion_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    color: #1F1D1A;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.mayhen_companion_desc {
    font-size: 0.95rem;
    color: #666;
    max-width: 650px;
    line-height: 1.5;
}

.mayhen_companion_actions {
    display: flex;
    gap: 0.75rem;
    flex-shrink: 0;
}

.mayhen_companion_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.mayhen_companion_btn--primary {
    background: #1F1D1A;
    color: #FAF8F5;
}

.mayhen_companion_btn--primary:hover {
    background: #333;
}

.mayhen_companion_btn--gold {
    background: linear-gradient(135deg, #C5A880 0%, #A8875A 100%);
    color: #FFF;
    border: none;
}

.mayhen_companion_btn--gold:hover {
    filter: brightness(1.08);
}

.mayhen_companion_btn--outline {
    background: #FFF;
    color: #444;
    border-color: #DCD6CD;
}

.mayhen_companion_btn--outline:hover {
    border-color: #C5A880;
    color: #1F1D1A;
}

.mayhen_companion_btn--sm {
    padding: 0.45rem 0.9rem;
    font-size: 0.8rem;
}

/* Grid Layout 2 Cột */
.mayhen_companion_grid {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 2rem;
    align-items: flex-start;
}

@media (max-width: 1024px) {
    .mayhen_companion_grid {
        grid-template-columns: 1fr;
    }
}

.mayhen_companion_panel {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.mayhen_companion_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
}

.mayhen_companion_card_head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px dashed rgba(197, 168, 128, 0.25);
}

.mayhen_companion_card_icon {
    font-size: 1.1rem;
}

.mayhen_companion_card_title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0;
}

/* Size Options */
.mayhen_companion_options_grid {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.mayhen_companion_size_option {
    display: block;
    padding: 0.75rem 1rem;
    background: #FAF8F5;
    border: 1px solid #E5DFD7;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mayhen_companion_size_option:hover {
    background: #FFF;
    border-color: #C5A880;
}

.mayhen_companion_size_option.active {
    background: #FFFDF9;
    border-color: #C5A880;
    box-shadow: 0 0 0 2px rgba(197, 168, 128, 0.25);
}

.mayhen_companion_size_name {
    font-weight: 600;
    font-size: 0.88rem;
    color: #1F1D1A;
    margin-bottom: 0.2rem;
}

.mayhen_companion_size_desc {
    font-size: 0.78rem;
    color: #777;
    margin-bottom: 0.35rem;
}

.mayhen_companion_size_badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    color: #9C7A4A;
    background: rgba(197, 168, 128, 0.15);
    padding: 2px 6px;
    border-radius: 4px;
}

/* Palette Swatches */
.mayhen_companion_palette_grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.mayhen_companion_palette_item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #EFEAE2;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mayhen_companion_palette_item:hover,
.mayhen_companion_palette_item.active {
    background: #FFFDF9;
    border-color: #C5A880;
}

.mayhen_companion_color_swatch {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mayhen_companion_color_dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.mayhen_companion_palette_name {
    font-size: 0.82rem;
    font-weight: 500;
    color: #333;
}

/* Motifs */
.mayhen_companion_motif_list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.mayhen_companion_motif_item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #EFEAE2;
    cursor: pointer;
    font-size: 0.82rem;
    color: #444;
}

.mayhen_companion_motif_item.active {
    border-color: #C5A880;
    background: #FFFDF9;
    font-weight: 600;
    color: #1F1D1A;
}

.mayhen_companion_radio_bullet {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 1px solid #C5A880;
    display: inline-block;
    position: relative;
}

.mayhen_companion_motif_item.active .mayhen_companion_radio_bullet::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #C5A880;
}

/* Fields */
.mayhen_companion_field {
    margin-bottom: 0.85rem;
}

.mayhen_companion_label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.35rem;
}

.mayhen_companion_input,
.mayhen_companion_textarea {
    width: 100%;
    padding: 0.6rem 0.85rem;
    border: 1px solid #DCD6CD;
    border-radius: 6px;
    font-size: 0.85rem;
    background: #FFF;
    color: #1F1D1A;
    box-sizing: border-box;
}

.mayhen_companion_input:focus,
.mayhen_companion_textarea:focus {
    border-color: #C5A880;
    outline: none;
    box-shadow: 0 0 0 2px rgba(197, 168, 128, 0.2);
}

.mayhen_companion_toggles {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.mayhen_companion_checkbox {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: #555;
    cursor: pointer;
}

.mayhen_companion_submit_row {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

/* Cột Phải: Preview Stage */
.mayhen_companion_preview_pane {
    position: sticky;
    top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.mayhen_companion_preview_header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mayhen_companion_preview_title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 0.2rem;
}

.mayhen_companion_preview_subtitle {
    font-size: 0.8rem;
    color: #777;
}

.mayhen_companion_badge {
    background: #F2ECE3;
    color: #8C6D3F;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
}

.mayhen_companion_stage {
    background: #EAE6DF;
    border-radius: 12px;
    padding: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 380px;
}

/* Card Render Component */
.mayhen_companion_card_render {
    position: relative;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.12);
    border-radius: 4px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

/* Sizes */
.mayhen_companion_card_render.size-namecard {
    width: 420px;
    height: 252px;
    padding: 24px 28px;
}

.mayhen_companion_card_render.size-postcard {
    width: 320px;
    height: 480px;
    padding: 30px 24px;
}

.mayhen_companion_card_render.size-square {
    width: 350px;
    height: 350px;
    padding: 26px;
}

/* Palettes */
.palette-gold {
    background-color: #FFFDF9;
    color: #1F1D1A;
}
.palette-gold .mayhen_card_border_outer { border: 1.5px solid #C5A880; }
.palette-gold .mayhen_card_border_inner { border: 1px dashed rgba(197, 168, 128, 0.5); }
.palette-gold .mayhen_card_kicker,
.palette-gold .mayhen_card_qr_label { color: #9C7A4A; }
.palette-gold .mayhen_card_line { background-color: #C5A880; }

.palette-charcoal {
    background-color: #1F1D1A;
    color: #FAF8F5;
}
.palette-charcoal .mayhen_card_border_outer { border: 1.5px solid #D8B276; }
.palette-charcoal .mayhen_card_border_inner { border: 1px dashed rgba(216, 178, 118, 0.5); }
.palette-charcoal .mayhen_card_kicker,
.palette-charcoal .mayhen_card_qr_label { color: #D8B276; }
.palette-charcoal .mayhen_card_line { background-color: #D8B276; }
.palette-charcoal .mayhen_card_couple { color: #FAF8F5; }
.palette-charcoal .mayhen_card_instructions { color: rgba(250, 248, 245, 0.85); }

.palette-sage {
    background-color: #F4F7F4;
    color: #1F291F;
}
.palette-sage .mayhen_card_border_outer { border: 1.5px solid #7A917A; }
.palette-sage .mayhen_card_border_inner { border: 1px dashed rgba(122, 145, 122, 0.5); }
.palette-sage .mayhen_card_kicker,
.palette-sage .mayhen_card_qr_label { color: #486648; }
.palette-sage .mayhen_card_line { background-color: #7A917A; }

.palette-ruby {
    background-color: #5E0E1C;
    color: #FAF7F2;
}
.palette-ruby .mayhen_card_border_outer { border: 1.5px solid #E8C88A; }
.palette-ruby .mayhen_card_border_inner { border: 1px dashed rgba(232, 200, 138, 0.5); }
.palette-ruby .mayhen_card_kicker,
.palette-ruby .mayhen_card_qr_label { color: #E8C88A; }
.palette-ruby .mayhen_card_line { background-color: #E8C88A; }
.palette-ruby .mayhen_card_couple { color: #FAF7F2; }

.palette-dusty_rose {
    background-color: #FCF6F6;
    color: #3E2424;
}
.palette-dusty_rose .mayhen_card_border_outer { border: 1.5px solid #A26B6B; }
.palette-dusty_rose .mayhen_card_border_inner { border: 1px dashed rgba(162, 107, 107, 0.5); }
.palette-dusty_rose .mayhen_card_kicker,
.palette-dusty_rose .mayhen_card_qr_label { color: #7D4E4E; }
.palette-dusty_rose .mayhen_card_line { background-color: #A26B6B; }

/* Motifs */
.motif-minimal_editorial .mayhen_card_border_inner { border-style: solid; border-width: 0.8px; }

/* Borders & Positioning */
.mayhen_card_border_outer {
    position: absolute;
    inset: 8px;
    pointer-events: none;
}
.mayhen_card_border_inner {
    position: absolute;
    inset: 12px;
    pointer-events: none;
}

.mayhen_card_content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    justify-content: space-between;
}

.size-postcard .mayhen_card_content {
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.size-postcard .mayhen_card_left {
    align-items: center;
    text-align: center;
}

.size-postcard .mayhen_card_line {
    margin: 8px auto;
}

.mayhen_card_left {
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1;
    padding-right: 18px;
}

.mayhen_card_kicker {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.mayhen_card_couple {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}

.mayhen_card_line {
    width: 40px;
    height: 1px;
    margin: 6px 0 8px;
}

.mayhen_card_meta {
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
}

.mayhen_card_instructions {
    font-size: 0.72rem;
    line-height: 1.4;
    margin: 0 0 0.5rem;
    opacity: 0.85;
}

.mayhen_card_url {
    font-size: 0.68rem;
    font-weight: 600;
    opacity: 0.9;
}

.mayhen_card_right {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.mayhen_card_qr_frame {
    background: #FFF;
    padding: 6px;
    border-radius: 8px;
    border: 1px solid rgba(197, 168, 128, 0.4);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.mayhen_card_qr_svg svg {
    display: block;
    width: 100px;
    height: 100px;
}

.size-postcard .mayhen_card_qr_svg svg {
    width: 130px;
    height: 130px;
}

.mayhen_card_qr_label {
    font-size: 0.68rem;
    font-weight: 600;
    margin-top: 6px;
    text-align: center;
}

.mayhen_card_footer_brand {
    position: absolute;
    bottom: 12px;
    left: 20px;
    font-size: 0.58rem;
    letter-spacing: 0.15em;
    opacity: 0.6;
}

.size-postcard .mayhen_card_footer_brand {
    left: 50%;
    transform: translateX(-50%);
}

/* Batch Box */
.mayhen_companion_batch_box {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.mayhen_companion_batch_head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.mayhen_companion_batch_head strong {
    display: block;
    font-size: 0.92rem;
    color: #1F1D1A;
}

.mayhen_companion_batch_head p {
    font-size: 0.78rem;
    color: #666;
    margin: 0.2rem 0 0;
}

.mayhen_companion_batch_icon {
    font-size: 1.6rem;
}
</style>
@endsection

