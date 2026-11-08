{{-- Section 2: Love Story & Quote - Variant A: Trích Dẫn & Khung Vòm (Poetic Arch) --}}
<section class="mayhen_invitation_story mayhen_invitation_story--arch mayhen_curated_craft_node" data-curated-section="story" data-mayhen-section="couple">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Lời Ngỏ &amp; Vòm Thơ (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="story" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_story_inner">
        <div class="mayhen_invitation_story_kicker">LOVE STORY</div>
        <h2 class="mayhen_invitation_ladi_headline_story" data-sync-target="story_title">{{ $storyTitle }}</h2>
        <div class="mayhen_invitation_story_arch_box">
            <img src="{{ $saveDatePhotoUrl }}" alt="Kỷ niệm" class="mayhen_invitation_story_img" data-sync-target="save_date_photo_url">
        </div>
        <blockquote class="mayhen_invitation_quote_text" data-sync-target="couple_quote">
            &ldquo;{{ $coupleQuote }}&rdquo;
        </blockquote>
        <h3 class="mayhen_invitation_story_subtitle" data-sync-target="story_subtitle">{{ $storySubtitle }}</h3>
        <p class="mayhen_invitation_story_content" data-sync-target="story_content">{{ $storyContent }}</p>
    </div>
</section>

