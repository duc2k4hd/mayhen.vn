{{-- Section 1: Hero Cover - Variant C: Trang Bìa Tạp Chí (Editorial Vogue) --}}
<section class="mayhen_invitation_hero mayhen_invitation_hero--editorial mayhen_curated_craft_node" data-curated-section="hero" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Bìa Tạp Chí Vogue (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="hero" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_hero_inner">
        <div class="mayhen_invitation_editorial_header">
            <span class="mayhen_invitation_editorial_vol">SPECIAL EDITION &bull; MAYHEN</span>
            <span class="mayhen_invitation_ladi_headline_ceremony_type" data-sync-target="ceremony_type">{{ $ceremonyType ?? 'Lễ Thành Hôn' }}</span>
        </div>
        <div class="mayhen_invitation_editorial_title_box">
            <h1 class="mayhen_invitation_ladi_headline_name_groom" data-sync-target="groom_name">{{ $groomName }}</h1>
            <div class="mayhen_invitation_editorial_overlap_wrap">
                <img src="{{ $heroPhotoUrl }}" alt="{{ $groomName }} & {{ $brideName }}" class="mayhen_invitation_hero_img" data-sync-target="hero_photo_url">
                <div class="mayhen_invitation_editorial_amp_stamp">&amp;</div>
            </div>
            <h1 class="mayhen_invitation_ladi_headline_name_bride" data-sync-target="bride_name">{{ $brideName }}</h1>
        </div>
        <div class="mayhen_invitation_editorial_footer">
            <div class="mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</div>
            <div class="mayhen_invitation_ladi_paragraph_lunar_calendar" data-sync-target="wedding_lunar_date">{{ $weddingLunar }}</div>
        </div>
    </div>
</section>

