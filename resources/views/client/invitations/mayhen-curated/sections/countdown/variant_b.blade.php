{{-- Section 4: Countdown & Calendar - Variant B: Hộp Số Tối Giản (Minimalist Digits) --}}
<section class="mayhen_invitation_countdown_calendar mayhen_invitation_countdown_calendar--minimal mayhen_curated_craft_node" data-curated-section="countdown" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Hộp Số Tối Giản (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="countdown" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_countdown_inner">
        <div class="mayhen_invitation_countdown_kicker">COUNTDOWN</div>
        <h2 class="mayhen_invitation_countdown_title">Từng Nhịp Đập Tiến Gần</h2>
        <p class="mayhen_invitation_countdown_desc">Chỉ còn ít ngày nữa để cùng nhau viết nên khoảnh khắc đẹp nhất cuộc đời</p>
        
        <div class="mayhen_invitation_countdown mayhen_invitation_countdown--pill" data-countdown="{{ $weddingDate->toIso8601String() }}">
            <div class="mayhen_invitation_cd_pill_box">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_day">00</span>
                <span class="mayhen_invitation_cd_lbl">DAYS</span>
            </div>
            <span class="mayhen_invitation_cd_sep">:</span>
            <div class="mayhen_invitation_cd_pill_box">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_hour">00</span>
                <span class="mayhen_invitation_cd_lbl">HOURS</span>
            </div>
            <span class="mayhen_invitation_cd_sep">:</span>
            <div class="mayhen_invitation_cd_pill_box">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_min">00</span>
                <span class="mayhen_invitation_cd_lbl">MINS</span>
            </div>
            <span class="mayhen_invitation_cd_sep">:</span>
            <div class="mayhen_invitation_cd_pill_box">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_sec">00</span>
                <span class="mayhen_invitation_cd_lbl">SECS</span>
            </div>
        </div>

        <div class="mayhen_invitation_minimal_date_banner">
            <span class="mayhen_invitation_date_highlight">{{ $weddingDateDigits }}</span>
            <span class="mayhen_invitation_lunar_sub">{{ $weddingLunar }}</span>
        </div>
    </div>
</section>

