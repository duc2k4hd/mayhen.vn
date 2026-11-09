{{-- Section 3: Family & Couple - Variant B: Thẻ Giấy Mỹ Thuật (Parchment Cards) --}}
<section class="mayhen_invitation_parents mayhen_invitation_parents--cards mayhen_curated_craft_node" data-curated-section="parents" data-mayhen-section="groom_family,bride_family">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Thẻ Giấy Mỹ Thuật (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="parents" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_parents_inner">
        <div class="mayhen_curated_craft_unit" data-craft-unit="parents_header">
            <div class="mayhen_invitation_family_kicker">GIA ĐÌNH &amp; UYÊN ƯƠNG</div>
            <h2 class="mayhen_invitation_family_title">Hai Bên Gia Đình</h2>
        </div>
        
        <div class="mayhen_invitation_family_parchment_list mayhen_curated_craft_unit" data-craft-unit="parents_family">
            <div class="mayhen_invitation_family_parchment_item">
                <div class="mayhen_invitation_parchment_badge">ĐẠI DIỆN NHÀ TRAI</div>
                <div class="mayhen_invitation_parchment_names">
                    <span>Thân phụ: <strong data-sync-target="groom_father">{{ $groomFather }}</strong></span>
                    <span>Thân mẫu: <strong data-sync-target="groom_mother">{{ $groomMother }}</strong></span>
                </div>
                <p class="mayhen_invitation_family_groom_address" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
            </div>

            <div class="mayhen_invitation_family_parchment_item">
                <div class="mayhen_invitation_parchment_badge">ĐẠI DIỆN NHÀ GÁI</div>
                <div class="mayhen_invitation_parchment_names">
                    <span>Thân phụ: <strong data-sync-target="bride_father">{{ $brideFather }}</strong></span>
                    <span>Thân mẫu: <strong data-sync-target="bride_mother">{{ $brideMother }}</strong></span>
                </div>
                <p class="mayhen_invitation_family_bride_address" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
            </div>
        </div>

        {{-- Uyên Ương Card --}}
        <div class="mayhen_invitation_couple_cards_grid mayhen_curated_craft_unit" data-craft-unit="parents_couple">
            <div class="mayhen_invitation_couple_card">
                <img src="{{ $groomPhotoUrl }}" alt="{{ $groomName }}" class="mayhen_invitation_groom_photo" data-sync-target="groom_photo_url">
                <div class="mayhen_invitation_couple_card_info">
                    <span class="mayhen_invitation_groom_role">CHÚ RỂ</span>
                    <h3 class="mayhen_invitation_groom_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                    <p class="mayhen_invitation_groom_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
                </div>
            </div>
            <div class="mayhen_invitation_couple_card">
                <img src="{{ $bridePhotoUrl }}" alt="{{ $brideName }}" class="mayhen_invitation_bride_photo" data-sync-target="bride_photo_url">
                <div class="mayhen_invitation_couple_card_info">
                    <span class="mayhen_invitation_bride_role">CÔ DÂU</span>
                    <h3 class="mayhen_invitation_bride_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                    <p class="mayhen_invitation_bride_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

