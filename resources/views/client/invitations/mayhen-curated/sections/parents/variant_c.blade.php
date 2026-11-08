{{-- Section 3: Family & Couple - Variant C: Gia Phong Chuẩn Mực (Formal Honor) --}}
<section class="mayhen_invitation_parents mayhen_invitation_parents--formal mayhen_curated_craft_node" data-curated-section="parents" data-mayhen-section="groom_family,bride_family">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Gia Phong Chuẩn Mực (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="parents" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_parents_inner">
        <div class="mayhen_invitation_formal_border_box">
            <img src="{{ $assetBase }}/corner-ornament.svg" alt="Corner" class="mayhen_invitation_corner_tl">
            <img src="{{ $assetBase }}/corner-ornament.svg" alt="Corner" class="mayhen_invitation_corner_tr">
            
            <div class="mayhen_invitation_family_kicker">HIẾU NGHĨA SONG THÂN</div>
            <h2 class="mayhen_invitation_family_title">Kính Báo Hôn Lễ</h2>
            
            <div class="mayhen_invitation_formal_ancestor_wrap">
                <div class="mayhen_invitation_formal_side">
                    <h4>NHÀ TRAI</h4>
                    <p class="mayhen_invitation_family_groom_father" data-sync-target="groom_father">Trưởng nam: <strong>{{ $groomName }}</strong></p>
                    <p>Con ông: <strong data-sync-target="groom_father">{{ $groomFather }}</strong></p>
                    <p>Và bà: <strong data-sync-target="groom_mother">{{ $groomMother }}</strong></p>
                    <p class="mayhen_invitation_address_sub" data-sync-target="groom_parents_address">{{ $groomParentsAddress }}</p>
                </div>

                <div class="mayhen_invitation_formal_divider_line"></div>

                <div class="mayhen_invitation_formal_side">
                    <h4>NHÀ GÁI</h4>
                    <p class="mayhen_invitation_family_bride_father" data-sync-target="bride_father">Ái nữ: <strong>{{ $brideName }}</strong></p>
                    <p>Con ông: <strong data-sync-target="bride_father">{{ $brideFather }}</strong></p>
                    <p>Và bà: <strong data-sync-target="bride_mother">{{ $brideMother }}</strong></p>
                    <p class="mayhen_invitation_address_sub" data-sync-target="bride_parents_address">{{ $brideParentsAddress }}</p>
                </div>
            </div>
            <img src="{{ $assetBase }}/corner-ornament.svg" alt="Corner" class="mayhen_invitation_corner_bl">
            <img src="{{ $assetBase }}/corner-ornament.svg" alt="Corner" class="mayhen_invitation_corner_br">
        </div>
    </div>
</section>

