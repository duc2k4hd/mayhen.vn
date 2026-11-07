@php
    $isEmbedMode = ($isEmbed ?? false) 
        || request()->boolean('embed') 
        || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
@endphp
@if(!$isEmbedMode)
<div class="mayhen_senior_widget" id="mayhen_senior_widget">
    {{-- Nút Chuyển Đổi Chế Độ Dễ Đọc --}}
    <button type="button" class="mayhen_senior_toggle_btn" id="mayhen_senior_toggle" aria-label="Bật chế độ chữ lớn dễ đọc cho người lớn tuổi" aria-pressed="false" title="Chế độ chữ lớn & tương phản cao cho người lớn tuổi">
        <span class="mayhen_senior_icon" aria-hidden="true">👓</span>
        <span class="mayhen_senior_label" id="mayhen_senior_btn_text">Chữ Lớn</span>
    </button>

    {{-- Thanh Hỗ Trợ Nhanh Người Lớn Tuổi (chỉ hiện khi đang bật Senior Mode) --}}
    <div class="mayhen_senior_quick_bar" id="mayhen_senior_quick_bar" style="display: none;">
        <span class="mayhen_senior_bar_title">Hỗ Trợ Nhanh:</span>
        @php
            $weddingData = isset($wedding) && $wedding ? $wedding : (isset($invitation) && $invitation->wedding ? $invitation->wedding : null);
            $extra = $weddingData && is_array($weddingData->extra_data) ? $weddingData->extra_data : (json_decode($weddingData->extra_data ?? '[]', true) ?? []);
            $groomPhone = $extra['groom_phone'] ?? ($extra['event_party_phone'] ?? null);
            $bridePhone = $extra['bride_phone'] ?? null;
            $mapUrl = $extra['event_party_map_url'] ?? ($extra['google_map_url'] ?? null);
        @endphp

        @if(!empty($groomPhone))
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $groomPhone) }}" class="mayhen_senior_call_btn" title="Gọi điện cho chú rể">
                📞 Gọi Chú Rể
            </a>
        @endif

        @if(!empty($bridePhone))
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $bridePhone) }}" class="mayhen_senior_call_btn" title="Gọi điện cho cô dâu">
                📞 Gọi Cô Dâu
            </a>
        @endif

        @if(!empty($mapUrl))
            <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_senior_map_btn" title="Xem chỉ đường Google Maps">
                📍 Chỉ Đường
            </a>
        @endif
    </div>
</div>

<style>
/* CSS thuần cho Chế Độ Tiếp Cận Người Lớn Tuổi (Senior-Friendly Mode) */
.mayhen_senior_widget {
    position: fixed;
    top: 18px;
    left: 18px;
    z-index: 9998;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    pointer-events: auto;
}

.mayhen_senior_toggle_btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(31, 29, 26, 0.88);
    color: #FAF8F5;
    border: 1px solid rgba(197, 168, 128, 0.6);
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: all 0.25s ease;
}

.mayhen_senior_toggle_btn:hover {
    background: #1F1D1A;
    border-color: #C5A880;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.28);
}

.mayhen_senior_toggle_btn[aria-pressed="true"] {
    background: #C5A880;
    color: #1F1D1A;
    border-color: #9C7A4A;
    font-weight: 600;
}

.mayhen_senior_icon {
    font-size: 14px;
    line-height: 1;
}

.mayhen_senior_quick_bar {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9997;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(31, 29, 26, 0.95);
    border: 1px solid #C5A880;
    padding: 8px 16px;
    border-radius: 999px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
    max-width: 90vw;
    white-space: nowrap;
    overflow-x: auto;
}

.mayhen_senior_bar_title {
    font-size: 12px;
    color: #E5DCCD;
    font-weight: 500;
}

.mayhen_senior_call_btn,
.mayhen_senior_map_btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    background: #FAF8F5;
    color: #1F1D1A;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: opacity 0.2s ease;
}

.mayhen_senior_call_btn:hover,
.mayhen_senior_map_btn:hover {
    opacity: 0.9;
}

/* Lớp áp dụng khi BẬT Senior Mode */
.mayhen_senior_mode {
    font-size: 118% !important;
}

.mayhen_senior_mode body,
.mayhen_senior_mode p,
.mayhen_senior_mode span:not(.mayhen_senior_icon),
.mayhen_senior_mode a:not(.mayhen_senior_call_btn):not(.mayhen_senior_map_btn),
.mayhen_senior_mode li {
    line-height: 1.75 !important;
    letter-spacing: 0.02em !important;
}

.mayhen_senior_mode h1,
.mayhen_senior_mode h2,
.mayhen_senior_mode h3 {
    line-height: 1.4 !important;
}

/* Tăng độ tương phản chữ */
.mayhen_senior_mode p,
.mayhen_senior_mode .mayhen_invitation_text {
    color: #12100E !important;
    font-weight: 500 !important;
}

/* Dừng hiệu ứng hoa rơi hoặc chuyển động lắc lư */
.mayhen_senior_mode .mayhen_petal,
.mayhen_senior_mode .petal,
.mayhen_senior_mode [class*="petal"],
.mayhen_senior_mode [class*="sparkle"] {
    display: none !important;
}
</style>

<script>
(function() {
    var btn = document.getElementById('mayhen_senior_toggle');
    var label = document.getElementById('mayhen_senior_btn_text');
    var bar = document.getElementById('mayhen_senior_quick_bar');
    if (!btn) return;

    var KEY = 'mayhen_senior_mode_active';

    function setSeniorMode(active) {
        if (active) {
            document.documentElement.classList.add('mayhen_senior_mode');
            btn.setAttribute('aria-pressed', 'true');
            if (label) label.textContent = 'Cỡ Chuẩn';
            if (bar && bar.children.length > 1) bar.style.display = 'flex';
            try { sessionStorage.setItem(KEY, '1'); } catch(e) {}
        } else {
            document.documentElement.classList.remove('mayhen_senior_mode');
            btn.setAttribute('aria-pressed', 'false');
            if (label) label.textContent = 'Chữ Lớn';
            if (bar) bar.style.display = 'none';
            try { sessionStorage.removeItem(KEY); } catch(e) {}
        }
    }

    // Khởi tạo trạng thái từ sessionStorage nếu đã chọn trước đó
    try {
        if (sessionStorage.getItem(KEY) === '1') {
            setSeniorMode(true);
        }
    } catch(e) {}

    btn.addEventListener('click', function() {
        var isCurrentlyActive = document.documentElement.classList.contains('mayhen_senior_mode');
        setSeniorMode(!isCurrentlyActive);
    });
})();
</script>
@endif
