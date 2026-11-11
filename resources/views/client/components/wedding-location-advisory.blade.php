@props([
    'invitation',
    'config' => null,
])

@php
    $advisory = $config ?? \App\Services\WeddingLocationAdvisoryService::resolveAdvisoryConfig($invitation);
    if (!($advisory['enabled'] ?? true)) {
        return;
    }
    $navLinks = \App\Services\WeddingLocationAdvisoryService::generateNavigationLinks($invitation);
@endphp

<section class="mayhen_invitation_section mayhen_invitation_location_advisory" id="mayhen-location-advisory-section">
    <div class="mayhen_invitation_locadv__container">
        {{-- Section Header --}}
        <div class="mayhen_invitation_locadv__header">
            <span class="mayhen_invitation_locadv__badge">Venue, Navigation &amp; Weather</span>
            <h2 class="mayhen_invitation_locadv__title" data-sync-target="location_title">
                {{ $advisory['title'] ?? 'Chỉ Dẫn Địa Điểm &amp; Bãi Đỗ Xe' }}
            </h2>
            <div class="mayhen_invitation_locadv__divider">
                <span class="mayhen_invitation_locadv__divider_line"></span>
                <span class="mayhen_invitation_locadv__divider_gem">❖</span>
                <span class="mayhen_invitation_locadv__divider_line"></span>
            </div>
            <p class="mayhen_invitation_locadv__subtitle" data-sync-target="location_subtitle">
                {{ $advisory['subtitle'] ?? 'Thông tin đường đi, bãi đỗ xe và lưu ý thời tiết để quý khách thuận tiện di chuyển' }}
            </p>
        </div>

        {{-- Khối Địa Chỉ & Trung Tâm Tiệc Cưới --}}
        <div class="mayhen_invitation_locadv__venue_card">
            <div class="mayhen_invitation_locadv__venue_icon">🏛️</div>
            <h3 class="mayhen_invitation_locadv__venue_name" data-sync-target="venue_name">
                {{ $advisory['venue_name'] }}
            </h3>
            <p class="mayhen_invitation_locadv__venue_address" data-sync-target="venue_address">
                <span class="mayhen_invitation_locadv__pin">📍</span>
                <span>{{ $advisory['venue_address'] }}</span>
            </p>

            {{-- 4 Nút Điều Hướng Bản Đồ Đa Nền Tảng --}}
            <div class="mayhen_invitation_locadv__nav_buttons">
                <a href="{{ $navLinks['google_maps'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_locadv__nav_btn mayhen_invitation_locadv__nav_btn--google">
                    <span class="mayhen_nav_btn_icon">🗺️</span>
                    <span>Google Maps</span>
                </a>

                <a href="{{ $navLinks['apple_maps'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_locadv__nav_btn mayhen_invitation_locadv__nav_btn--apple">
                    <span class="mayhen_nav_btn_icon">🍎</span>
                    <span>Apple Maps</span>
                </a>

                <a href="{{ $navLinks['grab'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_locadv__nav_btn mayhen_invitation_locadv__nav_btn--grab">
                    <span class="mayhen_nav_btn_icon">🚗</span>
                    <span>Đặt Xe Grab</span>
                </a>

                <a href="{{ $navLinks['taxi_mailinh'] }}" class="mayhen_invitation_locadv__nav_btn mayhen_invitation_locadv__nav_btn--taxi">
                    <span class="mayhen_nav_btn_icon">🚕</span>
                    <span>Gọi Taxi (1055)</span>
                </a>
            </div>
        </div>

        {{-- Khối Widget Thời Tiết & Gợi Ý Trang Phục (Weather Advisory) --}}
        @if(!empty($advisory['weather']['enabled']))
            <div class="mayhen_invitation_locadv__weather_card">
                <div class="mayhen_invitation_locadv__weather_top">
                    <div class="mayhen_invitation_locadv__weather_left">
                        <span class="mayhen_invitation_locadv__weather_icon">{{ $advisory['weather']['icon'] ?? '☀️' }}</span>
                        <div>
                            <div class="mayhen_invitation_locadv__weather_label">Dự Báo Thời Tiết Hôn Lễ:</div>
                            <h4 class="mayhen_invitation_locadv__weather_condition">{{ $advisory['weather']['condition_name'] ?? 'Nắng Ấm' }}</h4>
                        </div>
                    </div>
                    <div class="mayhen_invitation_locadv__weather_temp">
                        <span class="mayhen_temp_num">{{ $advisory['weather']['temperature_celsius'] ?? 28 }}</span>
                        <span class="mayhen_temp_unit">°C</span>
                    </div>
                </div>
                @if(!empty($advisory['weather']['advisory_note']))
                    <p class="mayhen_invitation_locadv__weather_note" data-sync-target="weather_note">
                        {{ $advisory['weather']['advisory_note'] }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Khối Hướng Dẫn Bãi Đỗ Xe & Tiếp Đón --}}
        <div class="mayhen_invitation_locadv__parking_grid">
            {{-- Xe Ô Tô --}}
            <div class="mayhen_invitation_locadv__parking_item">
                <div class="mayhen_parking_icon">🚙</div>
                <div class="mayhen_parking_content">
                    <h4 class="mayhen_parking_title">Bãi Đỗ Xe Ô Tô</h4>
                    <p class="mayhen_parking_text">{{ $advisory['parking']['car_parking'] }}</p>
                </div>
            </div>

            {{-- Xe Máy --}}
            <div class="mayhen_invitation_locadv__parking_item">
                <div class="mayhen_parking_icon">🛵</div>
                <div class="mayhen_parking_content">
                    <h4 class="mayhen_parking_title">Bãi Đỗ Xe Máy</h4>
                    <p class="mayhen_parking_text">{{ $advisory['parking']['bike_parking'] }}</p>
                </div>
            </div>

            {{-- Lối Đi Riêng & Thang Máy --}}
            <div class="mayhen_invitation_locadv__parking_item">
                <div class="mayhen_parking_icon">♿</div>
                <div class="mayhen_parking_content">
                    <h4 class="mayhen_parking_title">Lối Đi Thang Máy &amp; Hỗ Trợ Di Chuyển</h4>
                    <p class="mayhen_parking_text">{{ $advisory['parking']['accessibility_note'] }}</p>
                </div>
            </div>

            {{-- Hotline Lễ Tân --}}
            @if(!empty($advisory['reception_phone']))
                <div class="mayhen_invitation_locadv__parking_item mayhen_invitation_locadv__parking_item--hotline">
                    <div class="mayhen_parking_icon">📞</div>
                    <div class="mayhen_parking_content">
                        <h4 class="mayhen_parking_title">{{ $advisory['reception_coordinator'] ?? 'Ban Lễ Tân Đón Tiếp' }}</h4>
                        <p class="mayhen_parking_text">
                            Hỗ trợ chỉ đường &amp; đón tiếp: 
                            <a href="tel:{{ $advisory['reception_phone'] }}" class="mayhen_hotline_link"><strong>{{ $advisory['reception_phone'] }}</strong></a>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<style>
/* CSS BEM mayhen_invitation_location_advisory — Quiet Luxury Navigation & Weather */
.mayhen_invitation_location_advisory {
    padding: 60px 20px;
    background: transparent;
    font-family: inherit;
    color: #2C2824;
}
.mayhen_invitation_locadv__container {
    max-width: 760px;
    margin: 0 auto;
}
.mayhen_invitation_locadv__header {
    text-align: center;
    margin-bottom: 32px;
}
.mayhen_invitation_locadv__badge {
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
.mayhen_invitation_locadv__title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_invitation_locadv__divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 8px auto 14px auto;
    max-width: 180px;
}
.mayhen_invitation_locadv__divider_line {
    flex: 1;
    height: 1px;
    background: #C5A880;
    opacity: 0.5;
}
.mayhen_invitation_locadv__divider_gem {
    color: #9C7A4A;
    font-size: 11px;
}
.mayhen_invitation_locadv__subtitle {
    font-size: 14px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0 auto;
    max-width: 580px;
}

/* Card Venue */
.mayhen_invitation_locadv__venue_card {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 16px;
    padding: 28px 24px;
    text-align: center;
    box-shadow: 0 4px 18px rgba(0,0,0,0.03);
    margin-bottom: 24px;
}
.mayhen_invitation_locadv__venue_icon {
    font-size: 32px;
    line-height: 1;
    margin-bottom: 8px;
}
.mayhen_invitation_locadv__venue_name {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 24px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_invitation_locadv__venue_address {
    font-size: 14.5px;
    color: #555047;
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}

/* Navigation buttons */
.mayhen_invitation_locadv__nav_buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_invitation_locadv__nav_btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 25px;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.mayhen_invitation_locadv__nav_btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.1);
}
.mayhen_invitation_locadv__nav_btn--google {
    background: #FFFFFF;
    color: #1A73E8;
    border: 1px solid #DADCE0;
}
.mayhen_invitation_locadv__nav_btn--apple {
    background: #1C1C1E;
    color: #FFFFFF;
}
.mayhen_invitation_locadv__nav_btn--grab {
    background: #00B14F;
    color: #FFFFFF;
}
.mayhen_invitation_locadv__nav_btn--taxi {
    background: #FAF8F5;
    color: #9C7A4A;
    border: 1px solid #C5A880;
}

/* Weather Card */
.mayhen_invitation_locadv__weather_card {
    background: #FFFFFF;
    border: 1px solid #E5DCCD;
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.02);
}
.mayhen_invitation_locadv__weather_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mayhen_invitation_locadv__weather_left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mayhen_invitation_locadv__weather_icon {
    font-size: 36px;
    line-height: 1;
}
.mayhen_invitation_locadv__weather_label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #8C8275;
    font-weight: 700;
}
.mayhen_invitation_locadv__weather_condition {
    font-size: 17px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 2px 0 0 0;
}
.mayhen_invitation_locadv__weather_temp {
    display: flex;
    align-items: baseline;
    color: #9C7A4A;
}
.mayhen_temp_num {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
}
.mayhen_temp_unit {
    font-size: 16px;
    font-weight: 600;
    margin-left: 2px;
}
.mayhen_invitation_locadv__weather_note {
    font-size: 13.5px;
    line-height: 1.6;
    color: #4A453E;
    margin: 12px 0 0 0;
    padding-top: 12px;
    border-top: 1px dashed #EFE8DD;
    font-style: italic;
}

/* Parking Grid */
.mayhen_invitation_locadv__parking_grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.mayhen_invitation_locadv__parking_item {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.mayhen_invitation_locadv__parking_item--hotline {
    background: #FFFDF9;
    border-color: #C5A880;
}
.mayhen_parking_icon {
    font-size: 24px;
    line-height: 1;
}
.mayhen_parking_title {
    font-size: 13.5px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_parking_text {
    font-size: 12.5px;
    line-height: 1.5;
    color: #555047;
    margin: 0;
}
.mayhen_hotline_link {
    color: #9C7A4A;
    text-decoration: none;
}
.mayhen_hotline_link:hover {
    text-decoration: underline;
}

@media (max-width: 650px) {
    .mayhen_invitation_locadv__parking_grid {
        grid-template-columns: 1fr;
    }
    .mayhen_invitation_locadv__nav_buttons {
        flex-direction: column;
        width: 100%;
    }
    .mayhen_invitation_locadv__nav_btn {
        width: 100%;
        justify-content: center;
        box-sizing: border-box;
    }
}
</style>

