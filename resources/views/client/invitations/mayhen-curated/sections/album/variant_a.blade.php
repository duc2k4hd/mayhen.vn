{{-- Section 7: Album 10 Ảnh - Variant A: Editorial Gallery Grid (Lưới Tạp Chí Bất Đối Xứng) --}}
<section class="mayhen_invitation_gallery mayhen_invitation_gallery--editorial mayhen_curated_craft_node" data-curated-section="album" data-mayhen-section="album">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Album Tạp Chí (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="album" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif

    <div class="mayhen_invitation_gallery_inner">
        <div class="mayhen_invitation_gallery_header mayhen_curated_craft_unit" data-craft-unit="album_header">
            <span class="mayhen_invitation_gallery_kicker">SWEET MOMENTS</span>
            <h2 class="mayhen_invitation_gallery_title">Khoảnh Khắc Hạnh Phúc</h2>
            <div class="mayhen_invitation_gallery_crest_line">
                <span>✦</span>
            </div>
        </div>

        {{-- Khung trình diễn 10 ảnh --}}
        <div class="mayhen_invitation_gallery_editorial_wrap">
            {{-- Ảnh tiêu điểm lớn (Photo 0) --}}
            <div class="mayhen_invitation_gallery_item gallery_item--feature mayhen_curated_craft_unit" data-craft-unit="album_feature" data-index="0">
                <div class="gallery_item_inner">
                    <img src="{{ $gallery[0] }}" alt="Khoảnh khắc 1" class="mayhen_invitation_gallery_img" data-sync-target="gallery.0" loading="lazy">
                    <span class="gallery_badge">Tiêu Điểm &bull; #01</span>
                </div>
            </div>

            {{-- Lưới 9 ảnh tiếp nối (Photos 1 đến 9) --}}
            <div class="mayhen_invitation_gallery_grid_9">
                @for($i = 1; $i < 10; $i++)
                    <div class="mayhen_invitation_gallery_item gallery_item--sub mayhen_curated_craft_unit" data-craft-unit="album_sub_{{ $i }}" data-index="{{ $i }}">
                        <div class="gallery_item_inner">
                            <img src="{{ $gallery[$i] }}" alt="Khoảnh khắc {{ $i + 1 }}" class="mayhen_invitation_gallery_img" data-sync-target="gallery.{{ $i }}" loading="lazy">
                            <span class="gallery_index_pill">#0{{ $i + 1 }}</span>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <p class="mayhen_invitation_gallery_footer_note">
            &ldquo;Từng bức hình là một dấu chân tình yêu chúng mình đã cùng nhau bước qua.&rdquo;
        </p>
    </div>
</section>

