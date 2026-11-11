@props([
    'invitation',
    'config' => null,
])

@php
    $dresscode = $config ?? \App\Services\WeddingDresscodeService::resolveDresscodeConfig($invitation);
    if (!($dresscode['enabled'] ?? true)) {
        return;
    }
@endphp

<section class="mayhen_invitation_section mayhen_invitation_dresscode" id="mayhen-dresscode-section">
    <div class="mayhen_invitation_dresscode__container">
        {{-- Section Header --}}
        <div class="mayhen_invitation_dresscode__header">
            <span class="mayhen_invitation_dresscode__badge">Attire &amp; Dress Code Guide</span>
            <h2 class="mayhen_invitation_dresscode__title" data-sync-target="dresscode_title">
                {{ $dresscode['title'] ?? 'Quy Chuẩn Trang Phục' }}
            </h2>
            <div class="mayhen_invitation_dresscode__divider">
                <span class="mayhen_invitation_dresscode__divider_line"></span>
                <span class="mayhen_invitation_dresscode__divider_gem">❖</span>
                <span class="mayhen_invitation_dresscode__divider_line"></span>
            </div>
            <p class="mayhen_invitation_dresscode__subtitle" data-sync-target="dresscode_subtitle">
                {{ $dresscode['subtitle'] ?? 'Bảng màu gợi ý để những khoảnh khắc kỷ niệm thêm phần hòa hợp & đồng điệu' }}
            </p>
        </div>

        {{-- Phong cách chủ đạo --}}
        @if(!empty($dresscode['style_name']))
            <div class="mayhen_invitation_dresscode__style_badge">
                <span class="mayhen_invitation_dresscode__style_icon">✨</span>
                <span>Phong cách: <strong>{{ $dresscode['style_name'] }}</strong></span>
            </div>
        @endif

        {{-- Bảng Swatch Màu Sắc Hôn Lễ --}}
        @if(!empty($dresscode['colors']))
            <div class="mayhen_invitation_dresscode__palette">
                <div class="mayhen_invitation_dresscode__swatches">
                    @foreach($dresscode['colors'] as $c)
                        <div class="mayhen_invitation_dresscode__swatch_card" data-hex="{{ $c['hex'] }}">
                            <div class="mayhen_invitation_dresscode__circle" style="background-color: {{ $c['hex'] }};">
                                <span class="mayhen_invitation_dresscode__check">✓</span>
                            </div>
                            <div class="mayhen_invitation_dresscode__color_name">{{ $c['name'] }}</div>
                            <div class="mayhen_invitation_dresscode__color_hex">{{ $c['hex'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Khung Hướng Dẫn Phối Đồ Cho Nam & Nữ --}}
        <div class="mayhen_invitation_dresscode__guides">
            {{-- Quý Nam --}}
            <div class="mayhen_invitation_dresscode__guide_box">
                <div class="mayhen_invitation_dresscode__guide_icon">🤵‍♂️</div>
                <div class="mayhen_invitation_dresscode__guide_content">
                    <h4 class="mayhen_invitation_dresscode__guide_heading">Dành Cho Quý Nam (Gentlemen)</h4>
                    <p class="mayhen_invitation_dresscode__guide_text" data-sync-target="gentlemen_guide">
                        {{ $dresscode['gentlemen_guide'] }}
                    </p>
                </div>
            </div>

            {{-- Quý Nữ --}}
            <div class="mayhen_invitation_dresscode__guide_box">
                <div class="mayhen_invitation_dresscode__guide_icon">👰‍♀️</div>
                <div class="mayhen_invitation_dresscode__guide_content">
                    <h4 class="mayhen_invitation_dresscode__guide_heading">Dành Cho Quý Nữ (Ladies)</h4>
                    <p class="mayhen_invitation_dresscode__guide_text" data-sync-target="ladies_guide">
                        {{ $dresscode['ladies_guide'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Lời Nhắn & Lưu Ý Màu Sắc Cần Tránh --}}
        @if(!empty($dresscode['note']) || !empty($dresscode['avoid_colors_note']))
            <div class="mayhen_invitation_dresscode__notes">
                @if(!empty($dresscode['note']))
                    <p class="mayhen_invitation_dresscode__note_item" data-sync-target="dresscode_note">
                        {{ $dresscode['note'] }}
                    </p>
                @endif
                @if(!empty($dresscode['avoid_colors_note']))
                    <p class="mayhen_invitation_dresscode__avoid_item">
                        <span class="mayhen_invitation_dresscode__avoid_icon">⚠️</span>
                        <span>{{ $dresscode['avoid_colors_note'] }}</span>
                    </p>
                @endif
            </div>
        @endif

        {{-- Ảnh Moodboard Gợi Ý Trang Phục (Nếu Có) --}}
        @if(!empty($dresscode['moodboard_image_url']))
            <div class="mayhen_invitation_dresscode__moodboard">
                <div class="mayhen_invitation_dresscode__moodboard_frame">
                    <img src="{{ $dresscode['moodboard_image_url'] }}" alt="Outfit Moodboard" loading="lazy" class="mayhen_invitation_dresscode__moodboard_img">
                    <span class="mayhen_invitation_dresscode__moodboard_caption">Gợi ý phối đồ tiệc cưới (Outfit Moodboard)</span>
                </div>
            </div>
        @endif
    </div>
</section>

<style>
/* CSS BEM mayhen_invitation_dresscode — Quiet Luxury Palette Guide */
.mayhen_invitation_dresscode {
    padding: 60px 20px;
    background: transparent;
    font-family: inherit;
    text-align: center;
}
.mayhen_invitation_dresscode__container {
    max-width: 720px;
    margin: 0 auto;
}
.mayhen_invitation_dresscode__badge {
    display: inline-block;
    padding: 3px 12px;
    background: rgba(197, 168, 128, 0.15);
    color: #9C7A4A;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
}
.mayhen_invitation_dresscode__title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 30px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_invitation_dresscode__divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 8px auto 14px auto;
    max-width: 180px;
}
.mayhen_invitation_dresscode__divider_line {
    flex: 1;
    height: 1px;
    background: #C5A880;
    opacity: 0.5;
}
.mayhen_invitation_dresscode__divider_gem {
    color: #9C7A4A;
    font-size: 11px;
}
.mayhen_invitation_dresscode__subtitle {
    font-size: 14px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0 auto 24px auto;
    max-width: 580px;
}

/* Style badge */
.mayhen_invitation_dresscode__style_badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    color: #38342E;
    margin-bottom: 28px;
}
.mayhen_invitation_dresscode__style_icon {
    font-size: 14px;
}

/* Swatches */
.mayhen_invitation_dresscode__palette {
    margin-bottom: 32px;
}
.mayhen_invitation_dresscode__swatches {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 18px;
}
.mayhen_invitation_dresscode__swatch_card {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 90px;
    cursor: pointer;
    transition: transform 0.25s ease;
}
.mayhen_invitation_dresscode__swatch_card:hover {
    transform: translateY(-4px);
}
.mayhen_invitation_dresscode__circle {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    border: 3px solid #FFFFFF;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    position: relative;
    transition: box-shadow 0.2s;
}
.mayhen_invitation_dresscode__circle:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18), 0 0 0 2px #C5A880;
}
.mayhen_invitation_dresscode__check {
    display: none;
    color: #FFFFFF;
    font-weight: bold;
    font-size: 14px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.4);
}
.mayhen_invitation_dresscode__color_name {
    font-size: 12px;
    font-weight: 600;
    color: #2C2824;
    line-height: 1.3;
    text-align: center;
    min-height: 32px;
}
.mayhen_invitation_dresscode__color_hex {
    font-size: 11px;
    color: #8C8275;
    font-family: monospace;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

/* Guides boxes */
.mayhen_invitation_dresscode__guides {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
    text-align: left;
}
.mayhen_invitation_dresscode__guide_box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.mayhen_invitation_dresscode__guide_icon {
    font-size: 26px;
    line-height: 1;
}
.mayhen_invitation_dresscode__guide_content {
    flex: 1;
}
.mayhen_invitation_dresscode__guide_heading {
    font-size: 14px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_invitation_dresscode__guide_text {
    font-size: 13px;
    line-height: 1.5;
    color: #555047;
    margin: 0;
}

/* Notes */
.mayhen_invitation_dresscode__notes {
    background: rgba(239, 232, 221, 0.4);
    border-left: 3px solid #C5A880;
    padding: 14px 18px;
    border-radius: 0 8px 8px 0;
    margin-bottom: 24px;
    text-align: left;
}
.mayhen_invitation_dresscode__note_item {
    font-size: 13.5px;
    line-height: 1.6;
    color: #4A453E;
    margin: 0 0 6px 0;
    font-style: italic;
}
.mayhen_invitation_dresscode__avoid_item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: #9E1C1C;
    margin: 0;
    font-weight: 600;
}

/* Moodboard image */
.mayhen_invitation_dresscode__moodboard {
    margin-top: 28px;
}
.mayhen_invitation_dresscode__moodboard_frame {
    display: inline-block;
    background: #FFFFFF;
    padding: 10px;
    border-radius: 12px;
    border: 1px solid #E5DCCD;
    box-shadow: 0 6px 20px rgba(0,0,0,0.05);
    max-width: 100%;
}
.mayhen_invitation_dresscode__moodboard_img {
    max-width: 100%;
    height: auto;
    max-height: 380px;
    border-radius: 8px;
    display: block;
    object-fit: cover;
}
.mayhen_invitation_dresscode__moodboard_caption {
    display: block;
    font-size: 12px;
    color: #7A7265;
    margin-top: 8px;
    font-style: italic;
}

@media (max-width: 600px) {
    .mayhen_invitation_dresscode__guides {
        grid-template-columns: 1fr;
    }
    .mayhen_invitation_dresscode__swatch_card {
        width: 75px;
    }
    .mayhen_invitation_dresscode__circle {
        width: 46px;
        height: 46px;
    }
}
</style>

