{{-- Section 2: Love Story & Quote - Variant C: Tạp Chí Đối Xứng (Magazine Split) --}}
<section class="mayhen_invitation_story mayhen_invitation_story--magazine mayhen_curated_craft_node" data-curated-section="story" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Tạp Chí Đối Xứng (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="story" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_story_inner">
        <div class="mayhen_invitation_story_magazine_wrap">
            <div class="mayhen_invitation_story_kicker mayhen_curated_craft_unit" data-craft-unit="story_kicker">CHAPTER ONE &bull; THE BEGINNING</div>
            <h2 class="mayhen_invitation_ladi_headline_story mayhen_curated_craft_unit" data-craft-unit="story_title" data-sync-target="story_title">{{ $storyTitle }}</h2>
            
            <blockquote class="mayhen_invitation_quote_text mayhen_curated_craft_unit" data-craft-unit="story_quote" data-sync-target="couple_quote">
                &ldquo;{{ $coupleQuote }}&rdquo;
            </blockquote>

            <div class="mayhen_invitation_story_polaroid_box mayhen_curated_craft_unit" data-craft-unit="story_photo">
                <div class="mayhen_invitation_story_polaroid">
                    <img src="{{ $saveDatePhotoUrl }}" alt="Kỷ niệm" class="mayhen_invitation_story_img" data-sync-target="save_date_photo_url">
                    <div class="mayhen_invitation_story_polaroid_caption">{{ $groomName }} &bull; {{ $brideName }}</div>
                </div>
            </div>

            <div class="mayhen_invitation_story_body_box mayhen_curated_craft_unit" data-craft-unit="story_content">
                <h3 class="mayhen_invitation_story_subtitle" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>
                <p class="mayhen_invitation_story_content" data-sync-target="story_content">{{ $storyContent }}</p>
            </div>
        </div>
    </div>
</section>

