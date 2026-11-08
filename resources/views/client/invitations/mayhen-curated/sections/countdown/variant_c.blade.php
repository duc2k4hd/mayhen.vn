{{-- Section 4: Countdown & Calendar - Variant C: Vòng Tròn Thời Gian (Golden Circle) --}}
<section class="mayhen_invitation_countdown_calendar mayhen_invitation_countdown_calendar--circle mayhen_curated_craft_node" data-curated-section="countdown" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Vòng Tròn Thời Gian (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="countdown" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_countdown_inner">
        <div class="mayhen_invitation_circle_date_wrap">
            <div class="mayhen_invitation_gold_ring">
                <span class="mayhen_invitation_ring_sub">{{ $weddingDayOfWeek }}</span>
                <span class="mayhen_invitation_ring_day" data-sync-target="wedding_date_digits">{{ $weddingDay }}</span>
                <span class="mayhen_invitation_ring_my">THÁNG {{ $weddingMonth }} · {{ $weddingYear }}</span>
            </div>
        </div>

        <div class="mayhen_invitation_countdown mayhen_invitation_countdown--circular" data-countdown="{{ $weddingDate->toIso8601String() }}">
            <div class="mayhen_invitation_cd_ring_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_day">00</span>
                <span class="mayhen_invitation_cd_lbl">NGÀY</span>
            </div>
            <div class="mayhen_invitation_cd_ring_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_hour">00</span>
                <span class="mayhen_invitation_cd_lbl">GIỜ</span>
            </div>
            <div class="mayhen_invitation_cd_ring_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_min">00</span>
                <span class="mayhen_invitation_cd_lbl">PHÚT</span>
            </div>
            <div class="mayhen_invitation_cd_ring_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_sec">00</span>
                <span class="mayhen_invitation_cd_lbl">GIÂY</span>
            </div>
        </div>
    </div>
</section>

