{{-- Section 7: Album 10 Ảnh - Variant B: Filmstrip Storyboard (Thước Phim Điện Ảnh Lãng Mạn) --}}
<section class="mayhen_invitation_gallery mayhen_invitation_gallery--filmstrip mayhen_curated_craft_node" data-curated-section="album" data-mayhen-section="album">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Thước Phim Tình Yêu (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="album" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif

    <div class="mayhen_invitation_gallery_inner">
        <div class="mayhen_invitation_gallery_header mayhen_curated_craft_unit" data-craft-unit="album_header">
            <span class="mayhen_invitation_gallery_kicker">THE LOVE FILM &bull; 10 SCENES</span>
            <h2 class="mayhen_invitation_gallery_title">Thước Phim Hạnh Phúc</h2>
            <p class="mayhen_invitation_gallery_desc">Mỗi bức ảnh là một thước phim đẹp đẽ khắc ghi chặng đường bên nhau</p>
        </div>

        {{-- Dải phim Filmstrip 10 khung hình --}}
        <div class="mayhen_invitation_filmstrip_container mayhen_curated_craft_unit" data-craft-unit="album_filmstrip">
            <div class="mayhen_invitation_filmstrip_perforations top_perf">
                @for($p = 0; $p < 16; $p++)<span></span>@endfor
            </div>

            <div class="mayhen_invitation_filmstrip_track">
                @for($i = 0; $i < 10; $i++)
                    <div class="mayhen_invitation_gallery_item film_frame" data-index="{{ $i }}">
                        <div class="film_frame_inner">
                            <div class="film_meta_top">
                                <span class="film_roll_code">MAYHEN-35MM</span>
                                <span class="film_frame_num">#{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="film_photo_wrap">
                                <img src="{{ $gallery[$i] }}" alt="Khung hình {{ $i + 1 }}" class="mayhen_invitation_gallery_img" data-sync-target="gallery.{{ $i }}" loading="lazy">
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="mayhen_invitation_filmstrip_perforations bottom_perf">
                @for($p = 0; $p < 16; $p++)<span></span>@endfor
            </div>
        </div>

        <div class="mayhen_invitation_filmstrip_indicator">
            <span>✦ Kéo sang ngang để xem trọn vẹn 10 khoảnh khắc ✦</span>
        </div>
    </div>
</section>

