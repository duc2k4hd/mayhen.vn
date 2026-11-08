{{-- Section 7: Dresscode - Variant B: Mẫu Vải Nghệ Thuật (Fabric Swatches) --}}
<section class="mayhen_invitation_dresscode mayhen_invitation_dresscode--swatches mayhen_curated_craft_node" data-curated-section="dresscode" data-mayhen-section="dresscode">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Bảng Mẫu Vải (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="dresscode" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_dresscode_inner">
        <div class="mayhen_invitation_dresscode_kicker">GỢI Ý TÔNG MÀU</div>
        <h2 class="mayhen_invitation_dresscode_title">Quy Định Trang Phục</h2>
        
        <div class="mayhen_invitation_fabric_swatches_grid">
            <div class="mayhen_invitation_fabric_card">
                <div class="mayhen_invitation_fabric_swatch" style="background: #FAF7F2; border: 1px solid #E5DCCD;"></div>
                <div class="mayhen_invitation_fabric_name">IVORY</div>
            </div>
            <div class="mayhen_invitation_fabric_card">
                <div class="mayhen_invitation_fabric_swatch" style="background: #E5DCCD;"></div>
                <div class="mayhen_invitation_fabric_name">BEIGE</div>
            </div>
            <div class="mayhen_invitation_fabric_card">
                <div class="mayhen_invitation_fabric_swatch" style="background: #CFA860;"></div>
                <div class="mayhen_invitation_fabric_name">ANTIQUE GOLD</div>
            </div>
            <div class="mayhen_invitation_fabric_card">
                <div class="mayhen_invitation_fabric_swatch" style="background: #85182A;"></div>
                <div class="mayhen_invitation_fabric_name">BURGUNDY</div>
            </div>
        </div>

        <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">
            {{ $extraData['dresscode_note'] ?? 'Quý khách vui lòng chọn trang phục theo dải màu gợi ý để những khung hình kỷ niệm cùng cô dâu chú rể thêm phần hài hòa.' }}
        </p>
    </div>
</section>

