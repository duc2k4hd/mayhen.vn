{{-- Section 1: Hero Cover - Variant A: Cổng Vòm Cổ Điển (Classic Arch) --}}
<section class="mayhen_invitation_hero mayhen_invitation_hero--arch mayhen_curated_craft_node" data-curated-section="hero" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Bìa Vòm Cổ Điển (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="hero" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_hero_inner">
        <div class="mayhen_invitation_monogram mayhen_curated_craft_unit" data-craft-unit="hero_monogram">
            <img src="{{ $assetBase }}/monogram-crest.svg" alt="Monogram" class="mayhen_invitation_monogram_icon">
        </div>
        <div class="mayhen_invitation_ladi_headline_ceremony_type mayhen_curated_craft_unit" data-craft-unit="hero_ceremony" data-sync-target="ceremony_type">
            {{ $ceremonyType ?? 'Lễ Thành Hôn' }}
        </div>
        <div class="mayhen_invitation_hero_names mayhen_curated_craft_unit" data-craft-unit="hero_names">
            <h1 class="mayhen_invitation_ladi_headline_name_groom" data-sync-target="groom_name">{{ $groomName }}</h1>
            <span class="mayhen_invitation_hero_amp">&amp;</span>
            <h1 class="mayhen_invitation_ladi_headline_name_bride" data-sync-target="bride_name">{{ $brideName }}</h1>
        </div>
        <div class="mayhen_invitation_hero_arch_wrap mayhen_curated_craft_unit" data-craft-unit="hero_photo">
            <div class="mayhen_invitation_hero_arch_frame">
                <img src="{{ $heroPhotoUrl }}" alt="{{ $groomName }} & {{ $brideName }}" class="mayhen_invitation_hero_img" data-sync-target="hero_photo_url">
            </div>
        </div>
        <div class="mayhen_invitation_hero_date_pill mayhen_curated_craft_unit" data-craft-unit="hero_date">
            <div class="mayhen_invitation_ladi_headline_wedding_day" data-sync-target="wedding_date_digits">
                {{ $weddingDateDigits }}
            </div>
            <div class="mayhen_invitation_ladi_paragraph_lunar_calendar" data-sync-target="wedding_lunar_date">
                {{ $weddingLunar }}
            </div>
        </div>
    </div>
</section>

