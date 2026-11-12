@extends('client.pages.account.layout')

@php
    $pageTitle = 'Studio Soi Đa Thiết Bị — ' . $invitation->title;
@endphp

@section('account_breadcrumb', 'Soi Đa Thiết Bị')

@section('account_content')
<div class="mayhen_device_studio_root">
    {{-- Header Khái Quát --}}
    <div class="mayhen_device_header">
        <div>
            <div class="mayhen_device_header_kicker">
                <span>✦</span>
                <span>IDEA-E10 • TRUE MULTI-DEVICE VIEWPORT SIMULATOR</span>
            </div>
            <h1 class="mayhen_device_title">Phòng Kiểm Định & Soi Thiệp Đa Thiết Bị</h1>
            <p class="mayhen_device_subtitle">
                Kiểm tra trực quan từng điểm chạm của thiệp cưới trên các màn hình smartphone, tablet và máy tính phổ biến nhất. Đảm bảo 100% khách mời xem thiệp đều cảm nhận sự chỉn chu, mượt mà và không bị lệch bố cục.
            </p>
        </div>

        <div class="mayhen_device_header_actions">
            <a href="{{ route('invitation.show', ['slug' => $invitation->slug, 'rehearsal' => 1]) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                <span>Mở Thiệp Mới</span>
            </a>
            <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <span>Về Danh Sách Thiệp</span>
            </a>
        </div>
    </div>

    {{-- Bảng Điều Khiển Soi Thiết Bị (Top Control Bar) --}}
    <div class="mayhen_device_control_bar">
        {{-- 1. Thiết Bị Mục Tiêu --}}
        <div class="mayhen_device_control_group">
            <span class="mayhen_device_control_label">Thiết Bị:</span>
            <div class="mayhen_device_pills">
                @foreach($devices as $key => $dev)
                    <button 
                        type="button" 
                        class="mayhen_device_pill {{ $activeDevice['key'] === $key ? 'is-active' : '' }}"
                        data-device-key="{{ $key }}"
                        data-width="{{ $dev['width'] }}"
                        data-height="{{ $dev['height'] }}"
                        data-frame="{{ $dev['frame_type'] }}"
                        data-radius="{{ $dev['border_radius'] }}"
                        data-bezel="{{ $dev['bezel_width'] }}"
                        title="{{ $dev['description'] }}"
                    >
                        @if($dev['category'] === 'tablet')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        @elseif($dev['category'] === 'desktop')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12.02" y2="21"/></svg>
                        @else
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        @endif
                        <span>{{ $dev['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- 2. Thao Tác Phụ: Xoay Màn Hình, Tự Cuộn & Zoom --}}
        <div class="mayhen_device_control_tools">
            {{-- Nhóm khách mời --}}
            <div class="mayhen_device_tool_item">
                <label for="mayhen_device_group_select" class="mayhen_device_tool_label">Góc Nhìn Nhóm:</label>
                <select id="mayhen_device_group_select" class="mayhen_device_select">
                    @foreach($availableGroups as $grp)
                        <option value="{{ $grp }}" {{ $activeGroup === $grp ? 'selected' : '' }}>
                            {{ $grp }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Ngôn ngữ --}}
            @if(count($supportedLocales) > 1)
            <div class="mayhen_device_tool_item">
                <label for="mayhen_device_lang_select" class="mayhen_device_tool_label">Ngôn Ngữ:</label>
                <select id="mayhen_device_lang_select" class="mayhen_device_select">
                    @foreach($supportedLocales as $code => $meta)
                        <option value="{{ $code }}" {{ $activeLang === $code ? 'selected' : '' }}>
                            {{ $meta['flag'] }} {{ $meta['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Nút Xoay Ngang / Dọc --}}
            <button type="button" class="mayhen_device_btn_icon" id="mayhen_device_btn_rotate" title="Xoay ngang / dọc màn hình">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Xoay Ngang</span>
            </button>

            {{-- Nút Tự Động Cuộn Diễn Tập --}}
            <button type="button" class="mayhen_device_btn_icon" id="mayhen_device_btn_autoscroll" title="Tự động cuộn thiệp cưới mượt mà (Walkthrough)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="mayhen_autoscroll_icon"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                <span id="mayhen_autoscroll_text">Cuộn Tự Động</span>
            </button>

            {{-- Nút Thu Phóng (Zoom) --}}
            <div class="mayhen_device_zoom_selector">
                <button type="button" class="mayhen_device_zoom_btn" data-zoom="0.65">65%</button>
                <button type="button" class="mayhen_device_zoom_btn" data-zoom="0.8">80%</button>
                <button type="button" class="mayhen_device_zoom_btn is-active" data-zoom="1.0">100%</button>
            </div>
        </div>
    </div>

    {{-- Không Gian Sân Khấu Trưng Bày Mockup Vật Lý --}}
    <div class="mayhen_device_stage" id="mayhen_device_stage">
        {{-- Khung Vỏ Máy Vật Lý --}}
        <div 
            class="mayhen_device_frame frame--{{ $activeDevice['frame_type'] }}" 
            id="mayhen_device_frame"
            style="width: {{ $activeDevice['width'] }}px; height: {{ $activeDevice['height'] }}px; border-radius: {{ $activeDevice['border_radius'] }}px; border-width: {{ $activeDevice['bezel_width'] }}px;"
        >
            {{-- Đỉnh máy: Dynamic Island hoặc Camera Notch --}}
            <div class="mayhen_device_notch_wrap" aria-hidden="true">
                <div class="mayhen_device_dynamic_island" id="mayhen_device_island">
                    <span class="mayhen_device_island_camera"></span>
                    <span class="mayhen_device_island_sensor"></span>
                </div>
            </div>

            {{-- Màn Hình Iframe Thực Tế --}}
            <div class="mayhen_device_screen">
                <iframe 
                    id="mayhen_device_iframe"
                    src="{{ $embedUrl }}"
                    title="Mô phỏng thiệp cưới {{ $invitation->title }}"
                    frameborder="0"
                    loading="lazy"
                ></iframe>
            </div>

            {{-- Đáy máy: Home Indicator Bar --}}
            <div class="mayhen_device_home_indicator" aria-hidden="true">
                <div class="mayhen_device_home_bar"></div>
            </div>
        </div>

        {{-- Thanh Thống Số Viewport Dưới Đáy Khung --}}
        <div class="mayhen_device_spec_badge" id="mayhen_device_spec">
            <span id="spec_device_name">{{ $activeDevice['name'] }}</span>
            <span>&bull;</span>
            <span id="spec_resolution">{{ $activeDevice['width'] }} × {{ $activeDevice['height'] }} px</span>
            <span>&bull;</span>
            <span id="spec_ratio">{{ $activeDevice['screen_ratio'] }}</span>
            <span>&bull;</span>
            <span id="spec_orientation">Thẳng Đứng (Portrait)</span>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   MAYHEN.VN — MULTI-DEVICE SIMULATOR STUDIO STYLESHEET (QUIET LUXURY)
   BEM Prefix: mayhen_device_*
   ========================================================================== */

.mayhen_device_studio_root {
    display: flex;
    flex-direction: column;
    gap: 18px;
    width: 100%;
}

.mayhen_device_header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    background: #FFFFFF;
    border: 1px solid #EAE3D9;
    border-radius: 14px;
    padding: 22px 26px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.mayhen_device_header_kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9C7A4A;
    margin-bottom: 6px;
}

.mayhen_device_title {
    font-family: 'Playfair Display', serif;
    font-size: 1.55rem;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 6px 0;
    line-height: 1.25;
}

.mayhen_device_subtitle {
    font-size: 13px;
    color: #7A726A;
    margin: 0;
    line-height: 1.55;
    max-width: 820px;
}

.mayhen_device_header_actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Thanh Điều Khiển (Control Bar) */
.mayhen_device_control_bar {
    background: #FFFFFF;
    border: 1px solid #EAE3D9;
    border-radius: 12px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.mayhen_device_control_group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.mayhen_device_control_label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #4A433C;
}

.mayhen_device_pills {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.mayhen_device_pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 13px;
    background: #FAF8F5;
    border: 1px solid #E2D9CC;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
    color: #4A453E;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
}

.mayhen_device_pill:hover {
    border-color: #9C7A4A;
    color: #1F1D1A;
    background: #F4EFEB;
}

.mayhen_device_pill.is-active {
    background: #1F1D1A;
    border-color: #1F1D1A;
    color: #FAF8F5;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(31, 29, 26, 0.15);
}

.mayhen_device_control_tools {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.mayhen_device_tool_item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.mayhen_device_tool_label {
    font-size: 11px;
    font-weight: 600;
    color: #7A726A;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mayhen_device_select {
    padding: 6px 10px;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    font-size: 12px;
    font-family: inherit;
    background: #FAF8F5;
    color: #1F1D1A;
    cursor: pointer;
}

.mayhen_device_btn_icon {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #FAF8F5;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    color: #3B3733;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s ease;
}

.mayhen_device_btn_icon:hover {
    border-color: #9C7A4A;
    color: #9C7A4A;
    background: #FFFFFF;
}

.mayhen_device_btn_icon.is-active {
    background: #9C7A4A;
    border-color: #9C7A4A;
    color: #FFFFFF;
}

.mayhen_device_zoom_selector {
    display: inline-flex;
    align-items: center;
    background: #FAF8F5;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    padding: 2px;
}

.mayhen_device_zoom_btn {
    padding: 4px 8px;
    border: none;
    background: transparent;
    font-size: 11px;
    font-weight: 600;
    color: #7A726A;
    cursor: pointer;
    border-radius: 6px;
    font-family: inherit;
    transition: all 0.15s ease;
}

.mayhen_device_zoom_btn.is-active {
    background: #FFFFFF;
    color: #1F1D1A;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* Sân Khấu Mô Phỏng (Stage) */
.mayhen_device_stage {
    min-height: 820px;
    background: radial-gradient(circle at 50% 30%, #F5EFE9 0%, #E8DFD5 100%);
    border: 1px solid #DDD3C6;
    border-radius: 16px;
    padding: 40px 20px 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 24px;
    position: relative;
    overflow-x: auto;
    overflow-y: hidden;
}

/* Vỏ Khung Máy Vật Lý (Hardware Frame) */
.mayhen_device_frame {
    background: #000000;
    border-style: solid;
    border-color: #242220;
    box-shadow: 
        0 25px 60px -15px rgba(31, 29, 26, 0.45),
        0 0 0 1px rgba(255, 255, 255, 0.15) inset,
        0 10px 30px rgba(0, 0, 0, 0.25);
    position: relative;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: top center;
    flex-shrink: 0;
}

/* Màn hình hiển thị nội dung */
.mayhen_device_screen {
    width: 100%;
    height: 100%;
    background: #FAF8F5;
    border-radius: inherit;
    overflow: hidden;
    position: relative;
}

.mayhen_device_screen iframe {
    width: 100%;
    height: 100%;
    display: block;
    background: #FAF8F5;
    border: none;
}

/* Đỉnh máy: Dynamic Island */
.mayhen_device_notch_wrap {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100;
    pointer-events: none;
}

.mayhen_device_dynamic_island {
    width: 110px;
    height: 30px;
    background: #000000;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.mayhen_device_island_camera {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #0a0e1c;
    border: 1px solid #1a2236;
}

.mayhen_device_island_sensor {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #090c14;
}

/* Khung Hole Punch (Galaxy) */
.frame--hole_punch .mayhen_device_dynamic_island {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    padding: 0;
    background: #000000;
}
.frame--hole_punch .mayhen_device_island_sensor { display: none; }

/* Khung Tablet / Desktop: Ẩn notch */
.frame--tablet_bezel .mayhen_device_notch_wrap,
.frame--desktop .mayhen_device_notch_wrap,
.frame--laptop_bezel .mayhen_device_notch_wrap {
    display: none;
}

/* Home Indicator Bar ở đáy */
.mayhen_device_home_indicator {
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    width: 140px;
    height: 5px;
    z-index: 100;
    pointer-events: none;
}

.mayhen_device_home_bar {
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.45);
    border-radius: 999px;
    backdrop-filter: blur(4px);
}

.frame--desktop .mayhen_device_home_indicator,
.frame--laptop_bezel .mayhen_device_home_indicator {
    display: none;
}

/* Huy Hiệu Thông Số Kỹ Thuật (Spec Badge) */
.mayhen_device_spec_badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(31, 29, 26, 0.85);
    color: #FAF8F5;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 12px;
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    margin-top: 10px;
    letter-spacing: 0.3px;
}
</style>

<script>
(function() {
    'use strict';

    var frame = document.getElementById('mayhen_device_frame');
    var iframe = document.getElementById('mayhen_device_iframe');
    var specName = document.getElementById('spec_device_name');
    var specRes = document.getElementById('spec_resolution');
    var specRatio = document.getElementById('spec_ratio');
    var specOrientation = document.getElementById('spec_orientation');
    var btnRotate = document.getElementById('mayhen_device_btn_rotate');
    var btnAutoScroll = document.getElementById('mayhen_device_btn_autoscroll');
    var autoScrollText = document.getElementById('mayhen_autoscroll_text');
    var groupSelect = document.getElementById('mayhen_device_group_select');
    var langSelect = document.getElementById('mayhen_device_lang_select');

    var isLandscape = false;
    var currentWidth = {{ $activeDevice['width'] }};
    var currentHeight = {{ $activeDevice['height'] }};
    var currentRadius = {{ $activeDevice['border_radius'] }};
    var currentBezel = {{ $activeDevice['bezel_width'] }};
    var currentFrameType = '{{ $activeDevice['frame_type'] }}';
    var isAutoScrolling = false;

    // 1. Chuyển đổi Thiết bị
    document.querySelectorAll('.mayhen_device_pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.mayhen_device_pill').forEach(function(p) { p.classList.remove('is-active'); });
            pill.classList.add('is-active');

            currentWidth = parseInt(pill.dataset.width, 10);
            currentHeight = parseInt(pill.dataset.height, 10);
            currentRadius = parseInt(pill.dataset.radius, 10);
            currentBezel = parseInt(pill.dataset.bezel, 10);
            currentFrameType = pill.dataset.frame;

            specName.textContent = pill.querySelector('span').textContent;
            specRatio.textContent = pill.getAttribute('title') ? pill.getAttribute('title').split(',')[0] : '';

            applyDimensions();
        });
    });

    // 2. Áp dụng kích thước & khung viền
    function applyDimensions() {
        var w = isLandscape ? currentHeight : currentWidth;
        var h = isLandscape ? currentWidth : currentHeight;

        frame.style.width = w + 'px';
        frame.style.height = h + 'px';
        frame.style.borderRadius = currentRadius + 'px';
        frame.style.borderWidth = currentBezel + 'px';

        frame.className = 'mayhen_device_frame frame--' + currentFrameType;

        specRes.textContent = w + ' × ' + h + ' px';
        specOrientation.textContent = isLandscape ? 'Xoay Ngang (Landscape)' : 'Thẳng Đứng (Portrait)';
    }

    // 3. Xoay màn hình (Rotate)
    if (btnRotate) {
        btnRotate.addEventListener('click', function() {
            isLandscape = !isLandscape;
            btnRotate.classList.toggle('is-active', isLandscape);
            btnRotate.querySelector('span').textContent = isLandscape ? 'Xoay Dọc' : 'Xoay Ngang';
            applyDimensions();
        });
    }

    // 4. Thu phóng (Zoom)
    document.querySelectorAll('.mayhen_device_zoom_btn').forEach(function(zBtn) {
        zBtn.addEventListener('click', function() {
            document.querySelectorAll('.mayhen_device_zoom_btn').forEach(function(b) { b.classList.remove('is-active'); });
            zBtn.classList.add('is-active');
            var z = parseFloat(zBtn.dataset.zoom) || 1.0;
            frame.style.transform = 'scale(' + z + ')';
        });
    });

    // 5. Thay đổi Góc nhìn nhóm & Ngôn ngữ (Live Iframe Update)
    function reloadIframe() {
        var baseSlug = '{{ $invitation->slug }}';
        var group = groupSelect ? groupSelect.value : '';
        var lang = langSelect ? langSelect.value : 'vi';

        var url = '/thiep/' + encodeURIComponent(baseSlug) + '?embed=1&rehearsal=1';
        if (group && group !== 'Tất cả khách') {
            url += '&group=' + encodeURIComponent(group) + '&rehearsal_group=' + encodeURIComponent(group);
        }
        if (lang && lang !== 'vi') {
            url += '&lang=' + encodeURIComponent(lang);
        }

        iframe.src = url;
    }

    if (groupSelect) groupSelect.addEventListener('change', reloadIframe);
    if (langSelect) langSelect.addEventListener('change', reloadIframe);

    // 6. Tự động cuộn diễn tập (Auto-Scroll Walkthrough)
    if (btnAutoScroll) {
        btnAutoScroll.addEventListener('click', function() {
            isAutoScrolling = !isAutoScrolling;
            btnAutoScroll.classList.toggle('is-active', isAutoScrolling);

            if (isAutoScrolling) {
                autoScrollText.textContent = 'Dừng Cuộn';
                try {
                    iframe.contentWindow.postMessage({ type: 'MAYHEN_START_AUTO_SCROLL' }, '*');
                    if (iframe.contentWindow.__mayhenStartSmoothScroll) {
                        iframe.contentWindow.__mayhenStartSmoothScroll();
                    }
                } catch(e) {}
            } else {
                autoScrollText.textContent = 'Cuộn Tự Động';
                try {
                    iframe.contentWindow.postMessage({ type: 'MAYHEN_STOP_AUTO_SCROLL' }, '*');
                    if (iframe.contentWindow.__mayhenStopSmoothScroll) {
                        iframe.contentWindow.__mayhenStopSmoothScroll();
                    }
                } catch(e) {}
            }
        });
    }
})();
</script>
@endsection

