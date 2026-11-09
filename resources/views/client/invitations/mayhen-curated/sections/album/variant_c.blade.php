{{-- Section 7: Album 10 Ảnh - Variant C: Masonry Fine-Art Collage (Ghép Ảnh Nghệ Thuật Lệch Tầng) --}}
<section class="mayhen_invitation_gallery mayhen_invitation_gallery--collage mayhen_curated_craft_node" data-curated-section="album" data-mayhen-section="album">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Photobook Mỹ Thuật (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="album" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif

    <div class="mayhen_invitation_gallery_inner">
        <div class="mayhen_invitation_gallery_header mayhen_curated_craft_unit" data-craft-unit="album_header">
            <span class="mayhen_invitation_gallery_kicker">OUR PHOTOBOOK</span>
            <h2 class="mayhen_invitation_gallery_title">Kỷ Niệm Gắn Kết</h2>
            <div class="mayhen_invitation_gallery_crest_line">
                <span>✦</span>
            </div>
        </div>

        {{-- Lưới so le 2 cột nhịp nhàng (Collage Masonry) --}}
        <div class="mayhen_invitation_gallery_collage_grid mayhen_curated_craft_unit" data-craft-unit="album_collage">
            <div class="collage_col collage_col--left">
                @foreach([0, 2, 4, 6, 8] as $i)
                    <div class="mayhen_invitation_gallery_item collage_card collage_card--{{ $i % 4 == 0 ? 'tall' : 'standard' }}" data-index="{{ $i }}">
                        <div class="collage_card_inner">
                            <img src="{{ $gallery[$i] }}" alt="Ảnh cưới {{ $i + 1 }}" class="mayhen_invitation_gallery_img" data-sync-target="gallery.{{ $i }}" loading="lazy">
                            <div class="collage_caption">#{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="collage_col collage_col--right">
                @foreach([1, 3, 5, 7, 9] as $i)
                    <div class="mayhen_invitation_gallery_item collage_card collage_card--{{ $i % 4 == 1 ? 'standard' : 'tall' }}" data-index="{{ $i }}">
                        <div class="collage_card_inner">
                            <img src="{{ $gallery[$i] }}" alt="Ảnh cưới {{ $i + 1 }}" class="mayhen_invitation_gallery_img" data-sync-target="gallery.{{ $i }}" loading="lazy">
                            <div class="collage_caption">#{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mayhen_invitation_gallery_footer_sign">
            <span>{{ $groomName }}</span> &amp; <span>{{ $brideName }}</span>
        </div>
    </div>
</section>

