{{-- Section 3: Family & Couple - Variant A: Đối Xứng Hai Họ (Symmetric Heritage) --}}
<section class="mayhen_invitation_parents mayhen_invitation_parents--symmetric mayhen_curated_craft_node" data-curated-section="parents" data-mayhen-section="groom_family,bride_family">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Đối Xứng Hai Họ (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="parents" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_parents_inner">
        <div class="mayhen_invitation_family_kicker">HAI BÊN GIA ĐÌNH</div>
        <h2 class="mayhen_invitation_family_title">Song Thân Phụ Mẫu</h2>
        <p class="mayhen_invitation_family_notice">Trân trọng kính báo tin Lễ Thành Hôn của hai con chúng tôi</p>
        
        <div class="mayhen_invitation_family_grid">
            {{-- Nhà Trai --}}
            <div class="mayhen_invitation_family_card mayhen_invitation_family_card--groom">
                <h4 class="mayhen_invitation_family_groom_title">NHÀ TRAI</h4>
                <div class="mayhen_invitation_family_parents_names">
                    <p>Ông: <span class="mayhen_invitation_family_groom_father" data-sync-target="groom_father">{{ $groomFather }}</span></p>
                    <p>Bà: <span class="mayhen_invitation_family_groom_mother" data-sync-target="groom_mother">{{ $groomMother }}</span></p>
                </div>
                <p class="mayhen_invitation_family_groom_address" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
            </div>

            <div class="mayhen_invitation_family_crest_middle">
                <img src="{{ $assetBase }}/wax-seal.svg" alt="Hỷ" class="mayhen_invitation_crest_small">
            </div>

            {{-- Nhà Gái --}}
            <div class="mayhen_invitation_family_card mayhen_invitation_family_card--bride">
                <h4 class="mayhen_invitation_family_bride_title">NHÀ GÁI</h4>
                <div class="mayhen_invitation_family_parents_names">
                    <p>Ông: <span class="mayhen_invitation_family_bride_father" data-sync-target="bride_father">{{ $brideFather }}</span></p>
                    <p>Bà: <span class="mayhen_invitation_family_bride_mother" data-sync-target="bride_mother">{{ $brideMother }}</span></p>
                </div>
                <p class="mayhen_invitation_family_bride_address" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
            </div>
        </div>

        {{-- Chân dung & Bio Uyên Ương --}}
        <div class="mayhen_invitation_couple_portraits_wrap">
            <div class="mayhen_invitation_couple_col">
                <div class="mayhen_invitation_groom_photo_box">
                    <img src="{{ $groomPhotoUrl }}" alt="{{ $groomName }}" class="mayhen_invitation_groom_photo" data-sync-target="groom_photo_url">
                </div>
                <div class="mayhen_invitation_groom_role">CHÚ RỂ</div>
                <h3 class="mayhen_invitation_groom_name" data-sync-target="groom_name">{{ $groomName }}</h3>
                <p class="mayhen_invitation_groom_bio" data-sync-target="groom_bio">{{ $groomBio }}</p>
            </div>
            <div class="mayhen_invitation_couple_col">
                <div class="mayhen_invitation_bride_photo_box">
                    <img src="{{ $bridePhotoUrl }}" alt="{{ $brideName }}" class="mayhen_invitation_bride_photo" data-sync-target="bride_photo_url">
                </div>
                <div class="mayhen_invitation_bride_role">CÔ DÂU</div>
                <h3 class="mayhen_invitation_bride_name" data-sync-target="bride_name">{{ $brideName }}</h3>
                <p class="mayhen_invitation_bride_bio" data-sync-target="bride_bio">{{ $brideBio }}</p>
            </div>
        </div>
    </div>
</section>

