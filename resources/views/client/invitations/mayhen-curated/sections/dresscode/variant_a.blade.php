{{-- Section 7: Dresscode - Variant A: Vòng Tròn Sắc Màu (Palette Circles) --}}
<section class="mayhen_invitation_dresscode mayhen_invitation_dresscode--circles mayhen_curated_craft_node" data-curated-section="dresscode" data-mayhen-section="dresscode">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Vòng Tròn Sắc Màu (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="dresscode" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_dresscode_inner">
        <div class="mayhen_invitation_dresscode_kicker">DRESSCODE</div>
        <h2 class="mayhen_invitation_dresscode_title">Gợi Ý Trang Phục</h2>
        
        <div class="mayhen_invitation_dresscode_swatches">
            @php
                $swatches = [
                    ['color' => '#FAF7F2', 'name' => 'Trắng Kem'],
                    ['color' => '#E5DCCD', 'name' => 'Be Nhạt'],
                    ['color' => '#CFA860', 'name' => 'Vàng Champagne'],
                    ['color' => '#85182A', 'name' => 'Đỏ Rượu'],
                    ['color' => '#1F1D1A', 'name' => 'Đen Lịch Lãm'],
                ];
            @endphp
            @foreach($swatches as $s)
                <div class="mayhen_invitation_dresscode_item">
                    <div class="mayhen_invitation_dresscode_circle" style="background-color: {{ $s['color'] }};"></div>
                    <span class="mayhen_invitation_dresscode_label">{{ $s['name'] }}</span>
                </div>
            @endforeach
        </div>

        <p class="mayhen_invitation_dresscode_note" data-sync-target="dresscode_note">
            {{ $extraData['dresscode_note'] ?? 'Trân trọng kính mời Quý khách diện trang phục trang nhã để buổi tiệc thêm phần trọn vẹn và ấm cúng.' }}
        </p>
    </div>
</section>

