{{-- Section 4: Countdown & Calendar - Variant A: Tờ Lịch Cổ Điển (Classic Tear-off) --}}
<section class="mayhen_invitation_countdown_calendar mayhen_invitation_countdown_calendar--classic mayhen_curated_craft_node" data-curated-section="countdown" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Tờ Lịch Cổ Điển (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="countdown" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_countdown_inner">
        <div class="mayhen_invitation_countdown_kicker">SAVE THE DATE</div>
        <h2 class="mayhen_invitation_countdown_title">Đếm Ngược Ngày Chung Đôi</h2>
        
        {{-- Tờ Lịch Tháng Cổ Điển --}}
        <div class="mayhen_invitation_calendar_tear_box">
            <div class="mayhen_invitation_calendar_tear_header">
                <span class="mayhen_invitation_calendar_month_year">Tháng {{ $weddingMonth }} · {{ $weddingYear }}</span>
                <span class="mayhen_invitation_calendar_day_name">{{ $weddingDayOfWeek }}</span>
            </div>
            <div class="mayhen_invitation_calendar_big_day" data-sync-target="wedding_date_digits">
                {{ $weddingDay }}
            </div>
            <div class="mayhen_invitation_calendar_tear_footer">
                <span data-sync-target="wedding_lunar_date">{{ $weddingLunar }}</span>
            </div>
        </div>

        {{-- Đồng Hồ 4 Số --}}
        <div class="mayhen_invitation_countdown" data-countdown="{{ $weddingDate->toIso8601String() }}">
            <div class="mayhen_invitation_cd_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_day">00</span>
                <span class="mayhen_invitation_cd_lbl">Ngày</span>
            </div>
            <div class="mayhen_invitation_cd_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_hour">00</span>
                <span class="mayhen_invitation_cd_lbl">Giờ</span>
            </div>
            <div class="mayhen_invitation_cd_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_min">00</span>
                <span class="mayhen_invitation_cd_lbl">Phút</span>
            </div>
            <div class="mayhen_invitation_cd_item">
                <span class="mayhen_invitation_cd_val mayhen_invitation_cd_sec">00</span>
                <span class="mayhen_invitation_cd_lbl">Giây</span>
            </div>
        </div>
    </div>
</section>

