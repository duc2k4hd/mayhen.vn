{{-- Section 7: Dresscode - Variant C: Ký Họa Thời Trang (Vogue Palette) --}}
<section class="mayhen_invitation_dresscode mayhen_invitation_dresscode--vogue mayhen_curated_craft_node" data-curated-section="dresscode" data-mayhen-section="dresscode">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Ký Họa Thời Trang (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="dresscode" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_dresscode_inner">
        <div class="mayhen_invitation_dresscode_kicker">ATTIRE GUIDELINE</div>
        <h2 class="mayhen_invitation_dresscode_title">Bảng Màu Khách Quý</h2>
        
        <div class="mayhen_invitation_vogue_wrap">
            <div class="mayhen_invitation_vogue_sketch_icons">
                <span class="mayhen_invitation_sketch_tag">FORMAL &bull; COCKTAIL</span>
            </div>
            
            <div class="mayhen_invitation_vogue_palette_bar">
                <div class="mayhen_invitation_vogue_bar_item" style="background: #FAF7F2;" title="Kem Nhạt"></div>
                <div class="mayhen_invitation_vogue_bar_item" style="background: #D9B4A7;" title="Hồng Tro"></div>
                <div class="mayhen_invitation_vogue_bar_item" style="background: #CFA860;" title="Vàng Champagne"></div>
                <div class="mayhen_invitation_vogue_bar_item" style="background: #4A635D;" title="Xanh Xô Thơm"></div>
                <div class="mayhen_invitation_vogue_bar_item" style="background: #1F1D1A;" title="Đen Sang Trọng"></div>
            </div>

            <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">
                {{ $extraData['dresscode_note'] ?? 'Trang phục khuyến nghị: Lịch sự, trang nhã. Ưu tiên tông màu thanh khiết, tránh diện trang phục trùng màu trắng với cô dâu.' }}
            </p>
        </div>
    </div>
</section>

