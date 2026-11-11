@props([
    'invitation',
    'config' => null,
])

@php
    $gallery = $config ?? \App\Services\WeddingGalleryService::resolveGalleryConfig($invitation);
    if (!($gallery['enabled'] ?? true)) {
        return;
    }
    $photos = $gallery['photos'] ?? [];
    $layoutStyle = $gallery['layout_style'] ?? 'editorial_masonry';
    $lightboxEnabled = $gallery['lightbox_enabled'] ?? true;
@endphp

<section 
    class="mayhen_invitation_section mayhen_invitation_gallery mayhen_invitation_gallery--{{ $layoutStyle }}" 
    id="mayhen-gallery-section"
    data-mayhen-section="album"
    data-layout="{{ $layoutStyle }}"
>
    <div class="mayhen_invitation_gallery__container">
        {{-- Section Header --}}
        <div class="mayhen_invitation_gallery__header">
            <span class="mayhen_invitation_gallery_kicker">Wedding Gallery ✦ Moments</span>
            <h2 class="mayhen_invitation_gallery_title" data-sync-target="gallery_title">
                {{ $gallery['title'] ?? 'Khoảnh Khắc Hạnh Phúc' }}
            </h2>
            <div class="mayhen_invitation_gallery__divider">
                <span class="mayhen_gallery_divider_line"></span>
                <span class="mayhen_gallery_divider_gem">✦</span>
                <span class="mayhen_gallery_divider_line"></span>
            </div>
            <p class="mayhen_invitation_gallery__subtitle" data-sync-target="gallery_subtitle">
                {{ $gallery['subtitle'] ?? 'Những thước ảnh ghi dấu tình yêu và lời hẹn ước trăm năm của chúng mình' }}
            </p>
        </div>

        {{-- Khung Trình Diễn Album 10 Ảnh Cưới --}}
        <div class="mayhen_invitation_gallery_wrap mayhen_invitation_gallery_wrap--{{ $layoutStyle }}" id="mayhenGalleryWrap">
            @foreach($photos as $index => $photo)
                <div 
                    class="mayhen_invitation_gallery_item mayhen_invitation_gallery_item--{{ $index }}" 
                    data-index="{{ $index }}"
                    data-caption="{{ $photo['caption'] }}"
                    data-url="{{ $photo['url'] }}"
                >
                    <div class="mayhen_invitation_gallery_card">
                        <div class="mayhen_invitation_gallery_img_box">
                            <img 
                                src="{{ $photo['url'] }}" 
                                alt="{{ $photo['alt'] }}" 
                                class="mayhen_invitation_gallery_img"
                                data-sync-target="gallery.{{ $index }}"
                                loading="lazy"
                            />
                            <div class="mayhen_invitation_gallery_overlay">
                                <span class="mayhen_gallery_zoom_icon">⤢</span>
                                @if(!empty($photo['caption']))
                                    <span class="mayhen_gallery_caption_text">{{ $photo['caption'] }}</span>
                                @endif
                            </div>
                        </div>
                        @if(!empty($photo['caption']))
                            <div class="mayhen_invitation_gallery_caption">
                                <span class="mayhen_caption_num">0{{ $index + 1 }}</span>
                                <span class="mayhen_caption_line">—</span>
                                <span class="mayhen_caption_title">{{ $photo['caption'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Điều Hướng Cho Chế Độ Coverflow Carousel --}}
        @if($layoutStyle === 'carousel_coverflow')
            <div class="mayhen_invitation_gallery__nav">
                <button type="button" class="mayhen_invitation_gallery_prev_btn" id="mayhenGalleryPrev" aria-label="Xem ảnh trước">
                    ‹
                </button>
                <div class="mayhen_invitation_gallery_dots" id="mayhenGalleryDots">
                    @foreach($photos as $index => $photo)
                        <span class="mayhen_gallery_dot {{ $index === 0 ? 'active' : '' }}" data-dot-index="{{ $index }}"></span>
                    @endforeach
                </div>
                <button type="button" class="mayhen_invitation_gallery_next_btn" id="mayhenGalleryNext" aria-label="Xem ảnh kế tiếp">
                    ›
                </button>
            </div>
        @endif
    </div>

    {{-- Cửa Sổ Lightbox Toàn Màn Hình Tương Tác --}}
    @if($lightboxEnabled)
        <div class="mayhen_invitation_lightbox" id="mayhenGalleryLightbox" aria-hidden="true" style="display: none;">
            <div class="mayhen_lightbox_backdrop" id="mayhenLightboxBackdrop"></div>
            <div class="mayhen_lightbox_dialog">
                <button type="button" class="mayhen_lightbox_close_btn" id="mayhenLightboxClose" aria-label="Đóng cửa sổ ảnh">✕</button>
                
                <div class="mayhen_lightbox_body">
                    <button type="button" class="mayhen_lightbox_nav_btn prev" id="mayhenLightboxPrev" aria-label="Ảnh trước">‹</button>
                    <div class="mayhen_lightbox_view">
                        <img src="" alt="Ảnh cưới phóng to" id="mayhenLightboxImg" class="mayhen_lightbox_img" />
                        <div class="mayhen_lightbox_footer">
                            <span class="mayhen_lightbox_counter" id="mayhenLightboxCounter">1 / 10</span>
                            <span class="mayhen_lightbox_caption" id="mayhenLightboxCaption"></span>
                        </div>
                    </div>
                    <button type="button" class="mayhen_lightbox_nav_btn next" id="mayhenLightboxNext" aria-label="Ảnh sau">›</button>
                </div>
            </div>
        </div>
    @endif
</section>

{{-- Pure Vanilla CSS cho Gallery Suite --}}
<style>
.mayhen_invitation_gallery {
    padding: 70px 20px;
    background: #FAF8F5;
    color: #2C2824;
    position: relative;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.mayhen_invitation_gallery__container {
    max-width: 1040px;
    margin: 0 auto;
}
.mayhen_invitation_gallery__header {
    text-align: center;
    margin-bottom: 40px;
}
.mayhen_invitation_gallery_kicker {
    display: inline-block;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #9C7A4A;
    font-weight: 600;
    margin-bottom: 8px;
}
.mayhen_invitation_gallery_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 32px;
    color: #1F1D1A;
    margin: 0 0 12px;
    font-weight: 500;
    letter-spacing: -0.5px;
}
.mayhen_invitation_gallery__divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 14px;
}
.mayhen_gallery_divider_line {
    width: 40px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #C5A880, transparent);
}
.mayhen_gallery_divider_gem {
    color: #C5A880;
    font-size: 10px;
}
.mayhen_invitation_gallery__subtitle {
    font-size: 14px;
    color: #6B635B;
    max-width: 540px;
    margin: 0 auto;
    line-height: 1.6;
    font-style: italic;
}

/* 1. Bố cục Editorial Masonry (So le tạp chí) */
.mayhen_invitation_gallery_wrap--editorial_masonry {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 16px;
    align-items: center;
}
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--0 { grid-column: span 7; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--1 { grid-column: span 5; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--2 { grid-column: span 4; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--3 { grid-column: span 4; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--4 { grid-column: span 4; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--5 { grid-column: span 6; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--6 { grid-column: span 6; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--7 { grid-column: span 4; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--8 { grid-column: span 4; }
.mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--9 { grid-column: span 4; }

@media (max-width: 768px) {
    .mayhen_invitation_gallery_wrap--editorial_masonry {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--0 { grid-column: span 2; }
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--1 { grid-column: span 2; }
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--2,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--3,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--4,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--5,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--6,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--7,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--8,
    .mayhen_invitation_gallery_wrap--editorial_masonry .mayhen_invitation_gallery_item--9 {
        grid-column: span 1;
    }
}

/* 2. Bố cục Classic Grid (Lưới 2 cột sang trọng) */
.mayhen_invitation_gallery_wrap--classic_grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
@media (max-width: 640px) {
    .mayhen_invitation_gallery_wrap--classic_grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}

/* 3. Bố cục Coverflow Carousel (Cuộn ngang 3D) */
.mayhen_invitation_gallery_wrap--carousel_coverflow {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    padding: 20px 10px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.mayhen_invitation_gallery_wrap--carousel_coverflow::-webkit-scrollbar {
    display: none;
}
.mayhen_invitation_gallery_wrap--carousel_coverflow .mayhen_invitation_gallery_item {
    flex: 0 0 280px;
    scroll-snap-align: center;
    transition: transform 0.3s ease;
}
@media (min-width: 768px) {
    .mayhen_invitation_gallery_wrap--carousel_coverflow .mayhen_invitation_gallery_item {
        flex: 0 0 340px;
    }
}

/* Chung Thẻ Ảnh */
.mayhen_invitation_gallery_card {
    background: #FFF;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    border: 1px solid #EFE8DD;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}
.mayhen_invitation_gallery_card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.08);
}
.mayhen_invitation_gallery_img_box {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 5;
    background: #EFE8DD;
    overflow: hidden;
}
.mayhen_invitation_gallery_img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}
.mayhen_invitation_gallery_card:hover .mayhen_invitation_gallery_img {
    transform: scale(1.04);
}
.mayhen_invitation_gallery_overlay {
    position: absolute;
    inset: 0;
    background: rgba(31, 29, 26, 0.4);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 16px;
    text-align: center;
}
.mayhen_invitation_gallery_card:hover .mayhen_invitation_gallery_overlay {
    opacity: 1;
}
.mayhen_gallery_zoom_icon {
    font-size: 24px;
    color: #FFF;
    margin-bottom: 6px;
}
.mayhen_gallery_caption_text {
    color: #FFF;
    font-size: 13px;
    font-style: italic;
    font-family: "Playfair Display", Georgia, serif;
}
.mayhen_invitation_gallery_caption {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #4A453E;
    background: #FFF;
}
.mayhen_caption_num {
    font-weight: 600;
    color: #9C7A4A;
    font-size: 11px;
}
.mayhen_caption_line {
    color: #D8CEBD;
}
.mayhen_caption_title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Nav nút Carousel */
.mayhen_invitation_gallery__nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 24px;
}
.mayhen_invitation_gallery_prev_btn,
.mayhen_invitation_gallery_next_btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #FFF;
    border: 1px solid #D8CEBD;
    color: #4A453E;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.mayhen_invitation_gallery_prev_btn:hover,
.mayhen_invitation_gallery_next_btn:hover {
    background: #9C7A4A;
    color: #FFF;
    border-color: #9C7A4A;
}
.mayhen_invitation_gallery_dots {
    display: flex;
    gap: 6px;
}
.mayhen_gallery_dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #D8CEBD;
    transition: all 0.3s ease;
}
.mayhen_gallery_dot.active {
    width: 20px;
    border-radius: 3px;
    background: #9C7A4A;
}

/* Lightbox Modal */
.mayhen_invitation_lightbox {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mayhen_lightbox_backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 14, 12, 0.92);
    backdrop-filter: blur(8px);
}
.mayhen_lightbox_dialog {
    position: relative;
    z-index: 2;
    max-width: 90vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.mayhen_lightbox_close_btn {
    position: absolute;
    top: -46px;
    right: 0;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.25);
    color: #FFF;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s ease;
}
.mayhen_lightbox_close_btn:hover {
    background: rgba(255,255,255,0.25);
}
.mayhen_lightbox_body {
    display: flex;
    align-items: center;
    gap: 16px;
}
.mayhen_lightbox_nav_btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #FFF;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.mayhen_lightbox_nav_btn:hover {
    background: rgba(255,255,255,0.25);
}
.mayhen_lightbox_view {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.mayhen_lightbox_img {
    max-width: 82vw;
    max-height: 75vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.5);
    animation: mayhenFadeIn 0.25s ease;
}
.mayhen_lightbox_footer {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #EFE8DD;
    font-size: 13px;
    margin-top: 12px;
}
.mayhen_lightbox_counter {
    background: rgba(255,255,255,0.15);
    padding: 3px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
}
.mayhen_lightbox_caption {
    font-style: italic;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 14px;
}
@keyframes mayhenFadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
@media (max-width: 640px) {
    .mayhen_lightbox_nav_btn {
        display: none;
    }
}
</style>

{{-- Pure Vanilla JS cho Lightbox & Coverflow --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const section = document.getElementById('mayhen-gallery-section');
    if (!section) return;

    const items = section.querySelectorAll('.mayhen_invitation_gallery_item');
    const lightbox = document.getElementById('mayhenGalleryLightbox');
    const lightboxImg = document.getElementById('mayhenLightboxImg');
    const lightboxCounter = document.getElementById('mayhenLightboxCounter');
    const lightboxCaption = document.getElementById('mayhenLightboxCaption');
    const closeBtn = document.getElementById('mayhenLightboxClose');
    const backdrop = document.getElementById('mayhenLightboxBackdrop');
    const prevBtn = document.getElementById('mayhenLightboxPrev');
    const nextBtn = document.getElementById('mayhenLightboxNext');

    let currentIndex = 0;
    const photoData = [];

    items.forEach((item, idx) => {
        const url = item.getAttribute('data-url');
        const caption = item.getAttribute('data-caption') || '';
        photoData.push({ url, caption });

        item.addEventListener('click', function () {
            openLightbox(idx);
        });
    });

    function openLightbox(index) {
        if (!lightbox || photoData.length === 0) return;
        currentIndex = (index + photoData.length) % photoData.length;
        updateLightboxView();
        lightbox.style.display = 'flex';
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.style.display = 'none';
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function updateLightboxView() {
        const item = photoData[currentIndex];
        if (!item) return;
        lightboxImg.src = item.url;
        lightboxCounter.textContent = (currentIndex + 1) + ' / ' + photoData.length;
        lightboxCaption.textContent = item.caption;
    }

    function nextPhoto() {
        currentIndex = (currentIndex + 1) % photoData.length;
        updateLightboxView();
    }

    function prevPhoto() {
        currentIndex = (currentIndex - 1 + photoData.length) % photoData.length;
        updateLightboxView();
    }

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (backdrop) backdrop.addEventListener('click', closeLightbox);
    if (nextBtn) nextBtn.addEventListener('click', nextPhoto);
    if (prevBtn) prevBtn.addEventListener('click', prevPhoto);

    // Keyboard support: Escape, ArrowLeft, ArrowRight
    document.addEventListener('keydown', function (e) {
        if (lightbox && lightbox.style.display === 'flex') {
            if (e.key === 'Escape') closeLightbox();
            else if (e.key === 'ArrowRight') nextPhoto();
            else if (e.key === 'ArrowLeft') prevPhoto();
        }
    });

    // Coverflow buttons
    const gWrap = document.getElementById('mayhenGalleryWrap');
    const cPrev = document.getElementById('mayhenGalleryPrev');
    const cNext = document.getElementById('mayhenGalleryNext');
    if (gWrap && cPrev && cNext) {
        cPrev.addEventListener('click', () => {
            gWrap.scrollBy({ left: -300, behavior: 'smooth' });
        });
        cNext.addEventListener('click', () => {
            gWrap.scrollBy({ left: 300, behavior: 'smooth' });
        });
    }
});
</script>

