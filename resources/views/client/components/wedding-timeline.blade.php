@props([
    'invitation',
    'config' => null,
])

@php
    $timeline = $config ?? \App\Services\WeddingTimelineService::resolveTimelineConfig($invitation);
    if (!($timeline['enabled'] ?? true) || empty($timeline['items'])) {
        return;
    }
    $calendarLinks = \App\Services\WeddingTimelineService::generateCalendarLinks($invitation);
@endphp

<section class="mayhen_invitation_section mayhen_invitation_timeline" id="mayhen-timeline-section">
    <div class="mayhen_invitation_timeline__container">
        {{-- Section Header --}}
        <div class="mayhen_invitation_timeline__header">
            <span class="mayhen_invitation_timeline__badge">Wedding Program &amp; Schedule</span>
            <h2 class="mayhen_invitation_timeline__title" data-sync-target="timeline_title">
                {{ $timeline['title'] ?? 'Lịch Trình Hôn Lễ' }}
            </h2>
            <div class="mayhen_invitation_timeline__divider">
                <span class="mayhen_invitation_timeline__divider_line"></span>
                <span class="mayhen_invitation_timeline__divider_gem">❖</span>
                <span class="mayhen_invitation_timeline__divider_line"></span>
            </div>
            <p class="mayhen_invitation_timeline__subtitle" data-sync-target="timeline_subtitle">
                {{ $timeline['subtitle'] ?? 'Thời gian biểu chi tiết các nghi thức và hoạt động trong ngày trọng đại' }}
            </p>
        </div>

        {{-- Trục Dọc Thời Gian (Golden Spine Timeline) --}}
        <div class="mayhen_invitation_timeline__track">
            @foreach($timeline['items'] as $index => $item)
                <div class="mayhen_invitation_timeline__node {{ $index % 2 === 0 ? 'is-left' : 'is-right' }}">
                    {{-- Điểm Mốc Tròn Trên Trục --}}
                    <div class="mayhen_invitation_timeline__bullet">
                        <span class="mayhen_invitation_timeline__bullet_icon">{{ $item['icon_symbol'] ?? '💍' }}</span>
                    </div>

                    {{-- Card Nội Dung Hoạt Động --}}
                    <div class="mayhen_invitation_timeline__card">
                        <div class="mayhen_invitation_timeline__time_badge">
                            <span class="mayhen_invitation_timeline__clock_icon">⏰</span>
                            <span class="mayhen_invitation_timeline__time_text">{{ $item['time'] }}</span>
                        </div>

                        <h3 class="mayhen_invitation_timeline__item_title">{{ $item['title'] }}</h3>

                        @if(!empty($item['location']))
                            <div class="mayhen_invitation_timeline__location">
                                <span class="mayhen_invitation_timeline__loc_pin">📍</span>
                                <span>{{ $item['location'] }}</span>
                            </div>
                        @endif

                        @if(!empty($item['description']))
                            <p class="mayhen_invitation_timeline__item_desc">{{ $item['description'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Khối Hành Động: Thêm Vào Lịch Một Chạm (Add to Calendar) --}}
        <div class="mayhen_invitation_timeline__calendar_action">
            <div class="mayhen_invitation_timeline__cal_dropdown_wrapper">
                <button type="button" class="mayhen_invitation_timeline__cal_btn" onclick="toggleCalendarDropdown(this)">
                    <span class="mayhen_invitation_timeline__cal_icon">📅</span>
                    <span>Thêm Hôn Lễ Vào Lịch (Add to Calendar)</span>
                    <span class="mayhen_invitation_timeline__cal_arrow">▼</span>
                </button>

                <div class="mayhen_invitation_timeline__cal_menu" style="display: none;">
                    <a href="{{ $calendarLinks['google'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_timeline__cal_item">
                        <span class="mayhen_cal_vendor_icon">G</span>
                        <span>Google Calendar</span>
                    </a>
                    <a href="{{ $calendarLinks['ical'] }}" class="mayhen_invitation_timeline__cal_item">
                        <span class="mayhen_cal_vendor_icon">🍎</span>
                        <span>Apple Calendar (Tải file .ics)</span>
                    </a>
                    <a href="{{ $calendarLinks['outlook'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_timeline__cal_item">
                        <span class="mayhen_cal_vendor_icon">O</span>
                        <span>Outlook Calendar</span>
                    </a>
                    <a href="{{ $calendarLinks['yahoo'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_timeline__cal_item">
                        <span class="mayhen_cal_vendor_icon">Y</span>
                        <span>Yahoo Calendar</span>
                    </a>
                </div>
            </div>
            <p class="mayhen_invitation_timeline__cal_hint">
                Tự động nhắc nhở bạn trước 1 ngày và trước 2 giờ để bạn thong thả chuẩn bị trang phục.
            </p>
        </div>
    </div>
</section>

<style>
/* CSS BEM mayhen_invitation_timeline — Quiet Luxury Day Timeline */
.mayhen_invitation_timeline {
    padding: 60px 20px;
    background: transparent;
    font-family: inherit;
    color: #2C2824;
}
.mayhen_invitation_timeline__container {
    max-width: 760px;
    margin: 0 auto;
}
.mayhen_invitation_timeline__header {
    text-align: center;
    margin-bottom: 40px;
}
.mayhen_invitation_timeline__badge {
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
.mayhen_invitation_timeline__title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_invitation_timeline__divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 8px auto 14px auto;
    max-width: 180px;
}
.mayhen_invitation_timeline__divider_line {
    flex: 1;
    height: 1px;
    background: #C5A880;
    opacity: 0.5;
}
.mayhen_invitation_timeline__divider_gem {
    color: #9C7A4A;
    font-size: 11px;
}
.mayhen_invitation_timeline__subtitle {
    font-size: 14px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0 auto;
    max-width: 580px;
}

/* Trục dọc nối thời gian */
.mayhen_invitation_timeline__track {
    position: relative;
    padding: 20px 0;
}
.mayhen_invitation_timeline__track:before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 2px;
    background: linear-gradient(180deg, rgba(197,168,128,0.2) 0%, rgba(197,168,128,0.8) 15%, rgba(197,168,128,0.8) 85%, rgba(197,168,128,0.2) 100%);
    transform: translateX(-50%);
}

.mayhen_invitation_timeline__node {
    position: relative;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
}
.mayhen_invitation_timeline__node:last-child {
    margin-bottom: 0;
}
.mayhen_invitation_timeline__bullet {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 2px solid #C5A880;
    box-shadow: 0 4px 14px rgba(197, 168, 128, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}
.mayhen_invitation_timeline__bullet_icon {
    font-size: 18px;
    line-height: 1;
}

.mayhen_invitation_timeline__card {
    width: 44%;
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    box-sizing: border-box;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.mayhen_invitation_timeline__card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(156, 122, 74, 0.12);
}
.mayhen_invitation_timeline__node.is-left .mayhen_invitation_timeline__card {
    margin-right: auto;
    text-align: right;
}
.mayhen_invitation_timeline__node.is-right .mayhen_invitation_timeline__card {
    margin-left: auto;
    text-align: left;
}

.mayhen_invitation_timeline__time_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #EFE8DD;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    color: #9C7A4A;
    margin-bottom: 8px;
}
.mayhen_invitation_timeline__clock_icon {
    font-size: 13px;
}
.mayhen_invitation_timeline__item_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 20px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 6px 0;
}
.mayhen_invitation_timeline__location {
    font-size: 12.5px;
    color: #7A7265;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.mayhen_invitation_timeline__node.is-left .mayhen_invitation_timeline__location {
    justify-content: flex-end;
}
.mayhen_invitation_timeline__loc_pin {
    font-size: 12px;
}
.mayhen_invitation_timeline__item_desc {
    font-size: 13px;
    line-height: 1.5;
    color: #555047;
    margin: 0;
}

/* Thêm vào Lịch (Add to Calendar) */
.mayhen_invitation_timeline__calendar_action {
    margin-top: 48px;
    text-align: center;
}
.mayhen_invitation_timeline__cal_dropdown_wrapper {
    position: relative;
    display: inline-block;
}
.mayhen_invitation_timeline__cal_btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #9C7A4A;
    color: #FFFFFF;
    border: none;
    padding: 12px 24px;
    border-radius: 30px;
    font-size: 14.5px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(156, 122, 74, 0.3);
    transition: background 0.2s, transform 0.2s;
}
.mayhen_invitation_timeline__cal_btn:hover {
    background: #846538;
    transform: translateY(-2px);
}
.mayhen_invitation_timeline__cal_arrow {
    font-size: 10px;
    margin-left: 4px;
}
.mayhen_invitation_timeline__cal_menu {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-bottom: 10px;
    background: #FFFFFF;
    border: 1px solid #E5DCCD;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    min-width: 240px;
    overflow: hidden;
    z-index: 10;
    text-align: left;
}
.mayhen_invitation_timeline__cal_item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    color: #2C2824;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 500;
    border-bottom: 1px solid #FAF8F5;
    transition: background 0.2s;
}
.mayhen_invitation_timeline__cal_item:last-child {
    border-bottom: none;
}
.mayhen_invitation_timeline__cal_item:hover {
    background: #FAF8F5;
    color: #9C7A4A;
}
.mayhen_cal_vendor_icon {
    font-weight: bold;
    font-size: 15px;
    width: 20px;
    text-align: center;
}
.mayhen_invitation_timeline__cal_hint {
    font-size: 12.5px;
    color: #7A7265;
    margin-top: 10px;
    font-style: italic;
}

@media (max-width: 650px) {
    .mayhen_invitation_timeline__track:before {
        left: 20px;
    }
    .mayhen_invitation_timeline__bullet {
        left: 20px;
        width: 34px;
        height: 34px;
    }
    .mayhen_invitation_timeline__card {
        width: calc(100% - 50px);
        margin-left: 50px !important;
        margin-right: 0 !important;
        text-align: left !important;
    }
    .mayhen_invitation_timeline__node.is-left .mayhen_invitation_timeline__location {
        justify-content: flex-start;
    }
}
</style>

<script>
function toggleCalendarDropdown(btn) {
    const wrapper = btn.closest('.mayhen_invitation_timeline__cal_dropdown_wrapper');
    if (!wrapper) return;
    const menu = wrapper.querySelector('.mayhen_invitation_timeline__cal_menu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.mayhen_invitation_timeline__cal_dropdown_wrapper')) {
        document.querySelectorAll('.mayhen_invitation_timeline__cal_menu').forEach(m => m.style.display = 'none');
    }
});
</script>

