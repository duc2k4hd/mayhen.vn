@extends('client.pages.account.layout')

@php
    $pageTitle = 'Studio Tem Niêm Phong Sáp Hoàng Gia — Mây Hẹn';
@endphp

@section('account_breadcrumb', 'Con Dấu Sáp Hoàng Gia')

@section('account_content')
<div class="mayhen_wax_studio_container">
    {{-- Header & Headline --}}
    <div class="mayhen_wax_studio_header">
        <div class="mayhen_wax_studio_badge">Royal Wax Seal &amp; Monogram Studio</div>
        <h1 class="mayhen_wax_studio_title">Tem Niêm Phong Sáp Hoàng Gia</h1>
        <p class="mayhen_wax_studio_subtitle">
            Khắc họa dấu ấn độc bản của <strong>{{ $invitation->wedding->groom_name ?? $invitation->groom_name }} &amp; {{ $invitation->wedding->bride_name ?? $invitation->bride_name }}</strong> với con dấu sáp 3D dập nổi tinh xảo, phong bì lụa và âm thanh bóc thư êm dịu đánh thức mọi giác quan của khách mời.
        </p>
    </div>

    @if(session('success'))
        <div class="mayhen_wax_alert mayhen_wax_alert--success">
            <span class="mayhen_wax_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_wax_alert mayhen_wax_alert--danger">
            <span class="mayhen_wax_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Layout 2 cột: Cột trái Form Studio, Cột phải Live Preview phong bì --}}
    <div class="mayhen_wax_grid">
        {{-- Cột Trái: Bộ Công Cụ Tùy Biến --}}
        <div class="mayhen_wax_col_form">
            <form action="{{ route('account.wax_seal.update', $invitation->id) }}" method="POST" class="mayhen_wax_card" id="waxSealForm">
                @csrf

                {{-- 1. Bảng Màu Sáp Hoàng Gia --}}
                <div class="mayhen_wax_field_group">
                    <label class="mayhen_wax_label">1. Sắc Màu Sáp Niêm Phong (6 Bảng Màu Hoàng Gia):</label>
                    <div class="mayhen_wax_palette_grid">
                        @foreach($palettes as $pKey => $p)
                            <label class="mayhen_palette_swatch_card">
                                <input type="radio" name="color_palette" value="{{ $pKey }}" {{ ($sealConfig['color_palette'] ?? 'burgundy') === $pKey ? 'checked' : '' }} class="mayhen_palette_input">
                                <div class="mayhen_palette_circle" style="background: {{ $p['primary_color'] }};">
                                    <span class="mayhen_palette_check">✓</span>
                                </div>
                                <span class="mayhen_palette_name">{{ $p['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Biểu Tượng Dập Nổi Trung Tâm --}}
                <div class="mayhen_wax_field_group">
                    <label class="mayhen_wax_label">2. Họa Tiết Dập Nổi Trung Tâm:</label>
                    <div class="mayhen_wax_emblem_grid">
                        @foreach($emblems as $eKey => $e)
                            <label class="mayhen_emblem_card">
                                <input type="radio" name="emblem" value="{{ $eKey }}" {{ ($sealConfig['emblem'] ?? 'monogram') === $eKey ? 'checked' : '' }} class="mayhen_emblem_input">
                                <div class="mayhen_emblem_box">
                                    <strong class="mayhen_emblem_title">{{ $e['name'] }}</strong>
                                    <p class="mayhen_emblem_desc">{{ $e['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Khắc Chữ Viết Tắt Monogram --}}
                <div class="mayhen_wax_field_group" id="groupMonogramText">
                    <label class="mayhen_wax_label" for="inputMonogram">3. Chữ Viết Tắt Monogram (Tối đa 7 ký tự):</label>
                    <input type="text" name="monogram_text" id="inputMonogram" value="{{ old('monogram_text', $sealConfig['monogram_text'] ?? '') }}" maxlength="7" placeholder="Ví dụ: T &amp; N" class="mayhen_wax_input">
                    <p class="mayhen_wax_help">Hiển thị khi bạn chọn kiểu biểu tượng "Monogram 2 Chữ Cái Đầu".</p>
                </div>

                {{-- 4. Câu Khẩu Hiệu Dập Chìm --}}
                <div class="mayhen_wax_field_group">
                    <label class="mayhen_wax_label" for="inputMotto">4. Dải Chữ Khẩu Hiệu Dập Chìm Viền Sáp (Motto):</label>
                    <input type="text" name="motto_text" id="inputMotto" value="{{ old('motto_text', $sealConfig['motto_text'] ?? 'MAYHEN ATELIER') }}" maxlength="20" placeholder="Ví dụ: FOREVER LOVE / MAYHEN ATELIER" class="mayhen_wax_input">
                </div>

                {{-- 5. Kiểu Viền Sáp Chảy --}}
                <div class="mayhen_wax_field_group">
                    <label class="mayhen_wax_label">5. Kiểu Dáng Viền Sáp:</label>
                    <div class="mayhen_wax_rim_grid">
                        @foreach($rimStyles as $rKey => $r)
                            <label class="mayhen_rim_card">
                                <input type="radio" name="rim_style" value="{{ $rKey }}" {{ ($sealConfig['rim_style'] ?? 'vintage_deckled') === $rKey ? 'checked' : '' }} class="mayhen_rim_input">
                                <div class="mayhen_rim_box">
                                    <strong>{{ $r['name'] }}</strong>
                                    <small>{{ $r['description'] }}</small>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 6. Tùy Chọn Tương Tác --}}
                <div class="mayhen_wax_field_group">
                    <label class="mayhen_wax_label">6. Hiệu Ứng Giác Quan:</label>
                    <div class="mayhen_wax_switches">
                        <label class="mayhen_wax_switch_item">
                            <input type="checkbox" name="crack_sound" value="1" {{ ($sealConfig['crack_sound'] ?? true) ? 'checked' : '' }}>
                            <span>Phát âm thanh nứt sáp chân thực (Wax Crack Sound) khi khách bấm mở thiệp</span>
                        </label>
                        <label class="mayhen_wax_switch_item">
                            <input type="checkbox" name="shimmer_effect" value="1" {{ ($sealConfig['shimmer_effect'] ?? true) ? 'checked' : '' }}>
                            <span>Hiệu ứng lấp lánh ánh kim hoàng gia (Gold Foil Shimmer)</span>
                        </label>
                    </div>
                </div>

                <div class="mayhen_wax_actions">
                    <button type="submit" class="mayhen_wax_btn_submit">Lưu &amp; Áp Dụng Dấu Sáp Lên Thiệp</button>
                </div>
            </form>
        </div>

        {{-- Cột Phải: Live Preview Mô Phỏng Phong Bì Hoàng Gia --}}
        <div class="mayhen_wax_col_preview">
            <div class="mayhen_wax_card mayhen_preview_card">
                <h3 class="mayhen_wax_card_heading">Mô Phỏng Phong Bì &amp; Con Dấu Sáp</h3>
                <p class="mayhen_preview_sub">Khách mời sẽ nhìn thấy con dấu sáp này ở màn hình mở đầu bì thư thiệp cưới:</p>

                {{-- Khối Phong Bì Mỹ Thuật --}}
                <div class="mayhen_wax_envelope_preview">
                    <div class="mayhen_envelope_body">
                        <div class="mayhen_envelope_top_flap"></div>
                        <div class="mayhen_envelope_paper_texture"></div>

                        {{-- Con Dấu Sáp Live Preview --}}
                        <div class="mayhen_envelope_seal_anchor" id="liveSealWrapper">
                            @include('client.components.wax-seal-badge', ['invitation' => $invitation, 'size' => 'large', 'interactive' => true])
                        </div>

                        <div class="mayhen_envelope_caption">
                            <span>Chạm vào con dấu sáp để nghe thử tiếng nứt sáp 🎵</span>
                        </div>
                    </div>
                </div>

                <div class="mayhen_wax_test_action">
                    <button type="button" class="mayhen_btn_test_sound" onclick="window.mayhenPlayWaxCrackSound()">
                        🔊 Nghe Thử Âm Thanh Nứt Sáp (Crack Sound)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   WAX SEAL STUDIO STYLES (ĐỀ ÁN 131)
   100% Pure Vanilla CSS — BEM Prefix mayhen_wax_*
   ========================================================================== */
.mayhen_wax_studio_container {
    max-width: 1200px;
    margin: 0 auto;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #1F1D1A;
}

.mayhen_wax_studio_badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(140, 29, 36, 0.12);
    color: #8C1D24;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}

.mayhen_wax_studio_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: #1F1D1A;
}

.mayhen_wax_studio_subtitle {
    font-size: 0.95rem;
    color: #635E56;
    line-height: 1.6;
    max-width: 820px;
    margin: 0 0 2rem;
}

.mayhen_wax_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.mayhen_wax_alert--success {
    background: #F4F7ED;
    border: 1px solid #C4D7AC;
    color: #375323;
}
.mayhen_wax_alert--danger {
    background: #FDF3F2;
    border: 1px solid #F5C2BE;
    color: #8C2E24;
}
.mayhen_wax_alert_icon {
    font-weight: bold;
    font-size: 1.1rem;
}

/* Grid Layout */
.mayhen_wax_grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 2rem;
    align-items: start;
}
@media (max-width: 992px) {
    .mayhen_wax_grid {
        grid-template-columns: 1fr;
    }
}

.mayhen_wax_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.25);
    border-radius: 12px;
    padding: 1.75rem;
    box-shadow: 0 2px 12px rgba(31, 29, 26, 0.03);
}

.mayhen_wax_card_heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.35rem;
    font-weight: 600;
    margin: 0 0 1.25rem;
    border-bottom: 1px solid rgba(197, 168, 128, 0.2);
    padding-bottom: 0.5rem;
}

.mayhen_wax_field_group {
    margin-bottom: 1.5rem;
}
.mayhen_wax_label {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    margin-bottom: 0.6rem;
    color: #1F1D1A;
}
.mayhen_wax_help {
    font-size: 0.78rem;
    color: #7D7871;
    margin: 4px 0 0;
}
.mayhen_wax_input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #D9D5CF;
    border-radius: 6px;
    font-size: 0.9rem;
    box-sizing: border-box;
    font-family: inherit;
}
.mayhen_wax_input:focus {
    outline: none;
    border-color: #9C7A4A;
}

/* Color Palettes */
.mayhen_wax_palette_grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
@media (max-width: 640px) {
    .mayhen_wax_palette_grid {
        grid-template-columns: 1fr 1fr;
    }
}
.mayhen_palette_swatch_card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid #D9D5CF;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_palette_swatch_card:hover {
    border-color: #9C7A4A;
    background: #FAF7F2;
}
.mayhen_palette_input {
    display: none;
}
.mayhen_palette_circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}
.mayhen_palette_check {
    display: none;
    color: #FFFFFF;
    font-size: 11px;
    font-weight: bold;
}
.mayhen_palette_input:checked + .mayhen_palette_circle .mayhen_palette_check {
    display: block;
}
.mayhen_palette_input:checked ~ .mayhen_palette_name {
    font-weight: 700;
    color: #1F1D1A;
}
.mayhen_palette_name {
    font-size: 0.78rem;
    color: #4A4641;
}

/* Emblems */
.mayhen_wax_emblem_grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
@media (max-width: 640px) {
    .mayhen_wax_emblem_grid {
        grid-template-columns: 1fr;
    }
}
.mayhen_emblem_card {
    display: flex;
    align-items: flex-start;
    padding: 10px 12px;
    border: 1px solid #D9D5CF;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_emblem_card:hover {
    border-color: #9C7A4A;
    background: #FAF7F2;
}
.mayhen_emblem_input {
    margin-top: 3px;
    margin-right: 8px;
    accent-color: #9C7A4A;
}
.mayhen_emblem_title {
    display: block;
    font-size: 0.85rem;
    color: #1F1D1A;
}
.mayhen_emblem_desc {
    font-size: 0.74rem;
    color: #7D7871;
    margin: 2px 0 0;
    line-height: 1.35;
}

/* Rim Styles */
.mayhen_wax_rim_grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.mayhen_rim_card {
    display: flex;
    align-items: flex-start;
    padding: 8px 12px;
    border: 1px solid #D9D5CF;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_rim_card:hover {
    border-color: #9C7A4A;
    background: #FAF7F2;
}
.mayhen_rim_input {
    margin-top: 3px;
    margin-right: 8px;
    accent-color: #9C7A4A;
}
.mayhen_rim_box strong {
    display: block;
    font-size: 0.85rem;
}
.mayhen_rim_box small {
    font-size: 0.74rem;
    color: #7D7871;
}

/* Switches */
.mayhen_wax_switches {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.mayhen_wax_switch_item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.84rem;
    color: #4A4641;
    cursor: pointer;
}
.mayhen_wax_switch_item input {
    accent-color: #9C7A4A;
}

.mayhen_wax_btn_submit {
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
.mayhen_wax_btn_submit:hover {
    background: #38342E;
}

/* Preview Column: Luxury Envelope */
.mayhen_preview_card {
    text-align: center;
}
.mayhen_preview_sub {
    font-size: 0.85rem;
    color: #7D7871;
    margin: -0.5rem 0 1.5rem;
}

.mayhen_wax_envelope_preview {
    background: #FAF7F2;
    padding: 30px 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mayhen_envelope_body {
    position: relative;
    width: 280px;
    height: 180px;
    background: #FDFBF8;
    border: 1px solid rgba(197, 168, 128, 0.4);
    box-shadow: 0 10px 30px rgba(31, 29, 26, 0.08);
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.mayhen_envelope_top_flap {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 90px;
    background: linear-gradient(180deg, #F9F5EC 0%, #F3EDE0 100%);
    clip-path: polygon(0 0, 100% 0, 50% 100%);
    border-bottom: 1px solid rgba(197, 168, 128, 0.3);
}

.mayhen_envelope_seal_anchor {
    position: relative;
    z-index: 5;
    margin-top: 10px;
}

.mayhen_envelope_caption {
    position: absolute;
    bottom: 8px;
    font-size: 0.72rem;
    color: #9E9992;
}

.mayhen_wax_test_action {
    margin-top: 1.5rem;
}
.mayhen_btn_test_sound {
    padding: 9px 18px;
    background: #FAF7F2;
    border: 1px solid #C5A880;
    color: #9C7A4A;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_btn_test_sound:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}
</style>
@endsection

