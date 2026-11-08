{{-- Section 2: Love Story & Quote - Variant B: Tự Sự Lắng Đọng (Intimate Story) --}}
<section class="mayhen_invitation_story mayhen_invitation_story--intimate mayhen_curated_craft_node" data-curated-section="story" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Tự Sự Lắng Đọng (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="story" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_story_inner">
        <div class="mayhen_invitation_story_seal">
            <img src="{{ $assetBase }}/wax-seal.svg" alt="Seal" class="mayhen_invitation_seal_icon">
        </div>
        <h2 class="mayhen_invitation_ladi_headline_story" data-sync-target="story_title">{{ $storyTitle }}</h2>
        <h3 class="mayhen_invitation_story_subtitle" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>
        <div class="mayhen_invitation_story_letter_card">
            <blockquote class="mayhen_invitation_quote_text" data-sync-target="couple_quote">
                &ldquo;{{ $coupleQuote }}&rdquo;
            </blockquote>
            <p class="mayhen_invitation_story_content" data-sync-target="story_content">{{ $storyContent }}</p>
            <div class="mayhen_invitation_story_signature_sign">
                <span>{{ $groomName }}</span> &amp; <span>{{ $brideName }}</span>
            </div>
        </div>
    </div>
</section>

