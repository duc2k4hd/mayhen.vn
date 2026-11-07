@if(isset($guest) && $guest && !($isEmbed ?? false))
<div class="mayhen_guest_badge_widget" id="mayhen_guest_badge_widget">
    {{-- Floating Action Pill --}}
    <button type="button" class="mayhen_guest_badge_trigger" id="mayhen_btn_guest_pass" aria-label="Xem vị trí bàn tiệc và mã điểm danh">
        <span class="badge_icon">⚜️</span>
        <span class="badge_text">
            @if(!empty($guest->table_name))
                Bàn Tiệc: <strong>{{ $guest->table_name }}</strong>
            @else
                Mã Check-in & Bàn Tiệc
            @endif
        </span>
    </button>

    {{-- VIP Bottom Sheet / Modal --}}
    <div class="mayhen_guest_pass_modal" id="mayhen_guest_pass_modal" style="display: none;" role="dialog" aria-modal="true">
        <div class="pass_modal_backdrop" id="mayhen_pass_backdrop"></div>
        <div class="pass_modal_dialog">
            <button type="button" class="pass_modal_close" id="mayhen_pass_close" aria-label="Đóng">&times;</button>
            
            <div class="pass_modal_emblem">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.5">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                </svg>
            </div>

            <span class="pass_modal_title_sub">THẺ ĐIỂM DANH & VỊ TRÍ BÀN TIỆC</span>
            <h3 class="pass_modal_guest_name">{{ $guest->salutation ? $guest->salutation . ' ' : '' }}{{ $guest->name }}</h3>
            
            <div class="pass_modal_table_wrap">
                <span class="table_lead">VỊ TRÍ BÀN TIỆC</span>
                @if(!empty($guest->table_name))
                    <div class="table_name_highlight">{{ $guest->table_name }}</div>
                @else
                    <div class="table_name_pending">Lễ tân sẽ hướng dẫn vị trí bàn khi quý khách tới sảnh</div>
                @endif
            </div>

            <div class="pass_modal_qr_wrap">
                <div class="pass_qr_code">
                    {!! \App\Services\QrCodeService::svg(route('checkin.direct', ['token' => $guest->guest_token]), 180, '#1F1D1A', '#FAF8F5') !!}
                </div>
                <p class="pass_qr_text">
                    Xuất trình mã này tại bàn đón tiếp để nhận vị trí bàn tiệc nhanh nhất
                </p>
            </div>

            <div class="pass_modal_footer">
                <a href="{{ route('checkin.direct', ['token' => $guest->guest_token]) }}" target="_blank" class="pass_btn_link">
                    Mở thẻ VIP đầy đủ →
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Pure CSS for Guest Table Badge & Pass Modal */
.mayhen_guest_badge_widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.mayhen_guest_badge_trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #1F1D1A;
    color: #FAF8F5;
    border: 1px solid #C5A880;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.mayhen_guest_badge_trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.3);
}
.mayhen_guest_badge_trigger strong {
    color: #E8D3B9;
}
.mayhen_guest_pass_modal {
    position: fixed;
    inset: 0;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.pass_modal_backdrop {
    position: absolute;
    inset: 0;
    background: rgba(18, 16, 14, 0.65);
    backdrop-filter: blur(4px);
}
.pass_modal_dialog {
    position: relative;
    width: 100%;
    max-width: 380px;
    background: #FAF8F5;
    border: 1px solid #E5DCCD;
    border-radius: 20px;
    padding: 28px 24px;
    text-align: center;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
    animation: mayhenPassPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes mayhenPassPop {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
.pass_modal_close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 32px;
    height: 32px;
    border: none;
    background: #EFE8DD;
    color: #4A453E;
    border-radius: 50%;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
}
.pass_modal_emblem {
    margin-bottom: 8px;
}
.pass_modal_title_sub {
    font-size: 10px;
    letter-spacing: 2px;
    font-weight: 700;
    color: #9C7A4A;
    text-transform: uppercase;
}
.pass_modal_guest_name {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 22px;
    color: #1F1D1A;
    margin: 6px 0 16px;
    font-weight: 600;
}
.pass_modal_table_wrap {
    background: #FFFDF9;
    border: 1px solid #D8CEBD;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.table_lead {
    display: block;
    font-size: 11px;
    letter-spacing: 1px;
    color: #7A726A;
    margin-bottom: 4px;
}
.table_name_highlight {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 26px;
    font-weight: 700;
    color: #85182A;
    letter-spacing: 0.5px;
}
.table_name_pending {
    font-size: 13px;
    color: #7A726A;
    font-style: italic;
}
.pass_modal_qr_wrap {
    margin-bottom: 16px;
}
.pass_qr_code {
    display: inline-block;
    padding: 8px;
    background: #FFFDF9;
    border-radius: 12px;
    border: 1px solid #EFE8DD;
}
.pass_qr_text {
    font-size: 12px;
    color: #6B635B;
    margin-top: 8px;
    line-height: 1.4;
}
.pass_btn_link {
    display: inline-block;
    font-size: 13px;
    color: #9C7A4A;
    text-decoration: none;
    font-weight: 500;
}
@media (max-width: 640px) {
    .mayhen_guest_badge_widget {
        bottom: 16px;
        right: 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('mayhen_btn_guest_pass');
    const modal = document.getElementById('mayhen_guest_pass_modal');
    const closeBtn = document.getElementById('mayhen_pass_close');
    const backdrop = document.getElementById('mayhen_pass_backdrop');

    if (!trigger || !modal) return;

    const openPass = () => { modal.style.display = 'flex'; };
    const closePass = () => { modal.style.display = 'none'; };

    trigger.addEventListener('click', openPass);
    if (closeBtn) closeBtn.addEventListener('click', closePass);
    if (backdrop) backdrop.addEventListener('click', closePass);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display !== 'none') {
            closePass();
        }
    });
});
</script>
@endif

