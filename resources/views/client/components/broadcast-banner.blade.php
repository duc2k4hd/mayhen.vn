@php
    $invitationModel = $invitation ?? null;
    $broadcast = \App\Services\WeddingBroadcastService::resolveBroadcastConfig($invitationModel);
@endphp

@if(($broadcast['is_active'] ?? false) && !($isEmbed ?? false) && (!empty($broadcast['title']) || !empty($broadcast['message'])))
<div id="mayhenBroadcastRoot" class="mayhen_invitation_broadcast_root" data-urgency="{{ $broadcast['urgency_level'] }}">
    {{-- 1. STICKY BANNER Ở ĐẦU MÀN HÌNH THIỆP --}}
    @if(in_array($broadcast['display_type'], ['sticky_banner', 'both'], true))
        <aside id="mayhenBroadcastBanner" class="mayhen_invitation_broadcast_banner mayhen_broadcast--{{ $broadcast['urgency_level'] }}" role="alert" aria-live="assertive">
            <div class="mayhen_broadcast_container">
                <div class="mayhen_broadcast_left">
                    <span class="mayhen_broadcast_pulse_dot" aria-hidden="true"></span>
                    <div class="mayhen_broadcast_content">
                        <div class="mayhen_broadcast_title_row">
                            <span class="mayhen_broadcast_badge">
                                @if($broadcast['urgency_level'] === 'critical')
                                    Khẩn Cấp
                                @elseif($broadcast['urgency_level'] === 'warning')
                                    Lưu Ý Quan Trọng
                                @else
                                    Thông Báo
                                @endif
                            </span>
                            <strong class="mayhen_broadcast_title">{{ $broadcast['title'] }}</strong>
                            @if(!empty($broadcast['formatted_time']))
                                <span class="mayhen_broadcast_time">({{ $broadcast['formatted_time'] }})</span>
                            @endif
                        </div>
                        <p class="mayhen_broadcast_message" id="mayhenBroadcastMessage">{{ $broadcast['message'] }}</p>
                    </div>
                </div>

                <div class="mayhen_broadcast_right">
                    @if(!empty($broadcast['action_text']) && !empty($broadcast['action_url']))
                        <a href="{{ $broadcast['action_url'] }}" class="mayhen_broadcast_btn_action" target="{{ str_starts_with($broadcast['action_url'], 'http') ? '_blank' : '_self' }}" rel="noopener">
                            {{ $broadcast['action_text'] }}
                        </a>
                    @endif
                    <button type="button" id="mayhenBroadcastToggle" class="mayhen_broadcast_btn_toggle" aria-label="Thu gọn hoặc mở rộng thông báo" title="Thu gọn thông báo">
                        <span class="mayhen_toggle_icon">▲</span>
                    </button>
                </div>
            </div>
        </aside>
    @endif

    {{-- 2. MODAL POPUP TRANG TRỌNG XUẤT HIỆN KHI MỞ TRANG --}}
    @if(in_array($broadcast['display_type'], ['modal_popup', 'both'], true))
        <div id="mayhenBroadcastModal" class="mayhen_invitation_broadcast_modal" role="dialog" aria-modal="true" aria-labelledby="broadcastModalTitle">
            <div class="mayhen_broadcast_modal_overlay" id="mayhenBroadcastModalOverlay"></div>
            <div class="mayhen_broadcast_modal_card mayhen_broadcast--{{ $broadcast['urgency_level'] }}">
                <div class="mayhen_broadcast_modal_header">
                    <div class="mayhen_broadcast_crest" aria-hidden="true">
                        @if($broadcast['urgency_level'] === 'critical')
                            ⚠️
                        @else
                            ⚜️
                        @endif
                    </div>
                    <span class="mayhen_modal_badge">
                        @if($broadcast['urgency_level'] === 'critical')
                            Thông Báo Khẩn Cấp Ngày Cưới
                        @else
                            Cập Nhật Quan Trọng Từ Gia Đình
                        @endif
                    </span>
                    <h3 id="broadcastModalTitle" class="mayhen_modal_title">{{ $broadcast['title'] }}</h3>
                    @if(!empty($broadcast['formatted_time']))
                        <div class="mayhen_modal_time">Thời gian cập nhật: {{ $broadcast['formatted_time'] }}</div>
                    @endif
                </div>

                <div class="mayhen_broadcast_modal_body">
                    <p class="mayhen_modal_text">{{ $broadcast['message'] }}</p>
                </div>

                <div class="mayhen_broadcast_modal_footer">
                    @if(!empty($broadcast['action_text']) && !empty($broadcast['action_url']))
                        <a href="{{ $broadcast['action_url'] }}" class="mayhen_broadcast_btn_modal_action" target="{{ str_starts_with($broadcast['action_url'], 'http') ? '_blank' : '_self' }}" rel="noopener">
                            {{ $broadcast['action_text'] }}
                        </a>
                    @endif
                    <button type="button" id="mayhenBroadcastModalDismiss" class="mayhen_broadcast_btn_modal_dismiss">
                        Tôi Đã Hiểu &amp; Tiếp Tục Xem Thiệp
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* ==========================================================================
   QUIET LUXURY WEDDING BROADCAST BANNER & MODAL (ĐỀ ÁN 130 — MÂY HẸN)
   100% Pure Vanilla CSS — BEM Prefix mayhen_broadcast_*
   ========================================================================== */
.mayhen_invitation_broadcast_root {
    font-family: 'Inter', -apple-system, sans-serif;
    position: relative;
    z-index: 1000;
}

/* 1. STICKY BANNER */
.mayhen_invitation_broadcast_banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 10px 16px;
    box-shadow: 0 4px 20px rgba(31, 29, 26, 0.12);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), max-height 0.3s ease;
}

/* Urgency Color Variants */
.mayhen_broadcast--critical {
    background: rgba(140, 46, 36, 0.96);
    color: #FFFFFF;
    border-bottom: 2px solid #D4AF37;
}
.mayhen_broadcast--warning {
    background: rgba(44, 40, 37, 0.95);
    color: #FAF7F2;
    border-bottom: 2px solid #C5A880;
}
.mayhen_broadcast--info {
    background: rgba(250, 247, 242, 0.96);
    color: #1F1D1A;
    border-bottom: 1.5px solid #C5A880;
}

.mayhen_broadcast_container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.mayhen_broadcast_left {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex: 1;
}

.mayhen_broadcast_pulse_dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #D4AF37;
    margin-top: 6px;
    flex-shrink: 0;
    box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.7);
    animation: mayhenBroadcastPulse 1.8s infinite;
}

@keyframes mayhenBroadcastPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(212, 175, 55, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
}

.mayhen_broadcast_title_row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 2px;
}

.mayhen_broadcast_badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.2);
}
.mayhen_broadcast--info .mayhen_broadcast_badge {
    background: rgba(197, 168, 128, 0.2);
    color: #9C7A4A;
}

.mayhen_broadcast_title {
    font-size: 0.88rem;
    font-weight: 600;
}

.mayhen_broadcast_time {
    font-size: 0.75rem;
    opacity: 0.8;
}

.mayhen_broadcast_message {
    font-size: 0.82rem;
    line-height: 1.45;
    margin: 0;
    opacity: 0.95;
    transition: all 0.25s ease;
}

.mayhen_broadcast_right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.mayhen_broadcast_btn_action {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    background: #D4AF37;
    color: #1F1D1A;
    transition: all 0.2s ease;
}
.mayhen_broadcast_btn_action:hover {
    background: #FFFFFF;
    color: #1F1D1A;
}

.mayhen_broadcast_btn_toggle {
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}
.mayhen_broadcast_btn_toggle:hover {
    opacity: 1;
}

/* Collapsed state */
.mayhen_invitation_broadcast_banner.is-collapsed .mayhen_broadcast_message {
    display: none;
}
.mayhen_invitation_broadcast_banner.is-collapsed .mayhen_toggle_icon {
    display: inline-block;
    transform: rotate(180deg);
}

/* 2. MODAL POPUP */
.mayhen_invitation_broadcast_modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.mayhen_broadcast_modal_overlay {
    position: absolute;
    inset: 0;
    background: rgba(31, 29, 26, 0.65);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.mayhen_broadcast_modal_card {
    position: relative;
    max-width: 480px;
    width: 100%;
    background: #FFFFFF;
    border-radius: 14px;
    padding: 28px 24px;
    box-shadow: 0 16px 48px rgba(31, 29, 26, 0.25);
    text-align: center;
    animation: mayhenModalEntrance 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes mayhenModalEntrance {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.mayhen_broadcast_crest {
    font-size: 2rem;
    line-height: 1;
    margin-bottom: 8px;
}

.mayhen_modal_badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9C7A4A;
    background: rgba(197, 168, 128, 0.15);
    padding: 3px 10px;
    border-radius: 999px;
    margin-bottom: 8px;
}

.mayhen_modal_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.45rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 4px;
}

.mayhen_modal_time {
    font-size: 0.78rem;
    color: #7D7871;
    margin-bottom: 16px;
}

.mayhen_modal_text {
    font-size: 0.92rem;
    line-height: 1.6;
    color: #4A4641;
    margin: 0 0 24px;
    text-align: left;
    background: #FAF7F2;
    padding: 14px 16px;
    border-radius: 8px;
    border-left: 3px solid #C5A880;
}

.mayhen_broadcast_modal_footer {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mayhen_broadcast_btn_modal_action {
    display: block;
    width: 100%;
    padding: 12px;
    background: #D4AF37;
    color: #1F1D1A;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border-radius: 6px;
    box-sizing: border-box;
}

.mayhen_broadcast_btn_modal_dismiss {
    width: 100%;
    padding: 10px;
    background: transparent;
    border: 1px solid rgba(197, 168, 128, 0.4);
    color: #4A4641;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_broadcast_btn_modal_dismiss:hover {
    background: #FAF7F2;
    color: #1F1D1A;
}

@media (max-width: 640px) {
    .mayhen_broadcast_container {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .mayhen_broadcast_right {
        width: 100%;
        justify-content: space-between;
    }
}
</style>

<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Logic Thu gọn / Mở rộng Sticky Banner
        const banner = document.getElementById('mayhenBroadcastBanner');
        const toggleBtn = document.getElementById('mayhenBroadcastToggle');

        if (banner && toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                banner.classList.toggle('is-collapsed');
            });
        }

        // 2. Logic Modal Dismiss (Lưu vào sessionStorage để không làm phiền khách lặp lại trong phiên)
        const modal = document.getElementById('mayhenBroadcastModal');
        const dismissBtn = document.getElementById('mayhenBroadcastModalDismiss');
        const overlay = document.getElementById('mayhenBroadcastModalOverlay');

        if (modal) {
            const broadcastKey = 'mayhen_broadcast_dismissed_{{ md5($broadcast['title'] . ($broadcast['updated_at'] ?? '')) }}';
            if (sessionStorage.getItem(broadcastKey) === 'true') {
                modal.style.display = 'none';
            }

            function closeModal() {
                modal.style.opacity = '0';
                modal.style.transition = 'opacity 0.25s ease';
                setTimeout(function() {
                    modal.style.display = 'none';
                }, 250);
                sessionStorage.setItem(broadcastKey, 'true');
            }

            if (dismissBtn) {
                dismissBtn.addEventListener('click', closeModal);
            }
            if (overlay) {
                overlay.addEventListener('click', closeModal);
            }
        }
    });
})();
</script>
@endif

