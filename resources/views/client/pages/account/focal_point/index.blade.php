@extends('client.pages.account.layout')

@php
    $pageTitle = 'Trợ Lý Tối Ưu Bố Cục Ảnh Cưới & Tiêu Cự Vàng — Mây Hẹn';
    $firstKey = !empty($images) ? array_key_first($images) : null;
@endphp

@section('account_content')
<div class="mayhen_focal_studio">
    {{-- Thanh điều hướng --}}
    <div class="mayhen_focal_topbar">
        <a href="{{ route('account.invitations') }}" class="mayhen_focal_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Thiệp Cưới</span>
        </a>

        <div class="mayhen_focal_top_actions">
            <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="mayhen_focal_btn mayhen_focal_btn_outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Xem Thiệp Thực Tế</span>
            </a>
        </div>
    </div>

    {{-- Header giới thiệu --}}
    <header class="mayhen_focal_header">
        <div class="mayhen_focal_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="3"></circle>
                <line x1="12" y1="2" x2="12" y2="6"></line>
                <line x1="12" y1="18" x2="12" y2="22"></line>
                <line x1="2" y1="12" x2="6" y2="12"></line>
                <line x1="18" y1="12" x2="22" y2="12"></line>
            </svg>
            <span>Smart Focal Point Photo Optimizer (IDEA-F06 & IDEA-E04)</span>
        </div>
        <h1 class="mayhen_focal_title">Căn Chỉnh Tiêu Cự &amp; Bố Cục Ảnh Cưới Đa Màn Hình</h1>
        <p class="mayhen_focal_subtitle">
            Chấm chọn điểm tiêu cự vàng trên ảnh để khuôn mặt cô dâu chú rể hoặc chi tiết váy cưới không bao giờ bị cắt xén lệch khi hiển thị trên các màn hình điện thoại (9:16), máy tính bảng (4:3) hay máy tính để bàn (16:9).
        </p>
    </header>

    @if(!empty($images))
        {{-- Khung làm việc chính --}}
        <div class="mayhen_focal_workspace">
            {{-- Cột 1: Danh sách ảnh cần căn chỉnh --}}
            <aside class="mayhen_focal_sidebar">
                <div class="mayhen_focal_sidebar_title">Chọn Ảnh Cần Căn Chỉnh</div>
                <div class="mayhen_focal_thumb_list">
                    @foreach($images as $key => $img)
                        <div class="mayhen_focal_thumb_item {{ $loop->first ? 'active' : '' }}" 
                             data-key="{{ $key }}"
                             data-title="{{ $img['title'] }}"
                             data-section="{{ $img['section'] }}"
                             data-url="{{ $img['url'] }}"
                             data-x="{{ $img['focal_point']['x'] }}"
                             data-y="{{ $img['focal_point']['y'] }}">
                            <div class="mayhen_focal_thumb_preview">
                                <img src="{{ $img['url'] }}" alt="{{ $img['title'] }}">
                                <div class="mayhen_focal_thumb_coord" id="badge-{{ $key }}">
                                    {{ $img['focal_point']['x'] }}%, {{ $img['focal_point']['y'] }}%
                                </div>
                            </div>
                            <div class="mayhen_focal_thumb_info">
                                <div class="mayhen_focal_thumb_name">{{ $img['title'] }}</div>
                                <div class="mayhen_focal_thumb_sec">{{ $img['section'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>

            {{-- Cột 2: Bộ căn chỉnh trực quan & Live Preview --}}
            <main class="mayhen_focal_main">
                <div class="mayhen_focal_main_card">
                    <div class="mayhen_focal_main_header">
                        <div>
                            <h2 id="currentImageTitle">{{ $images[$firstKey]['title'] }}</h2>
                            <p id="currentImageSection">{{ $images[$firstKey]['section'] }}</p>
                        </div>

                        <div class="mayhen_focal_actions">
                            <button type="button" id="btnResetFocal" class="mayhen_focal_btn mayhen_focal_btn_text">
                                Đặt lại (50%, 50%)
                            </button>
                            <button type="button" id="btnSaveFocal" class="mayhen_focal_btn mayhen_focal_btn_primary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Lưu Tiêu Điểm</span>
                            </button>
                        </div>
                    </div>

                    {{-- Khung hình tương tác chấm tiêu điểm --}}
                    <div class="mayhen_focal_canvas_container">
                        <div class="mayhen_focal_canvas_wrapper" id="focalWrapper">
                            <img src="{{ $images[$firstKey]['url'] }}" id="targetImage" alt="Ảnh đang chọn" draggable="false">
                            
                            {{-- Tâm ngắm vàng Reticle --}}
                            <div class="mayhen_focal_reticle" id="reticle" style="left: {{ $images[$firstKey]['focal_point']['x'] }}%; top: {{ $images[$firstKey]['focal_point']['y'] }}%;">
                                <div class="reticle_ring"></div>
                                <div class="reticle_dot"></div>
                                <div class="reticle_coords" id="reticleCoords">{{ $images[$firstKey]['focal_point']['x'] }}%, {{ $images[$firstKey]['focal_point']['y'] }}%</div>
                            </div>
                        </div>
                        <div class="mayhen_focal_canvas_hint">
                            <span>💡 <strong>Hướng dẫn:</strong> Nhấp hoặc kéo thả tâm ngắm trực tiếp lên vị trí gương mặt cô dâu / chú rể để ảnh tự động khóa tiêu cự.</span>
                        </div>
                    </div>

                    {{-- 9 Nút Neo Tiêu Cự Vàng Nhanh --}}
                    <div class="mayhen_focal_anchors_box">
                        <div class="mayhen_focal_anchors_title">Vị Trí Tiêu Cự Gợi Ý Sẵn:</div>
                        <div class="mayhen_focal_anchors_grid">
                            @foreach($presets as $pKey => $p)
                                <button type="button" class="mayhen_focal_anchor_btn" data-x="{{ $p['x'] }}" data-y="{{ $p['y'] }}">
                                    <span>{{ $p['label'] }}</span>
                                    <small>({{ $p['x'] }}%, {{ $p['y'] }}%)</small>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 4 Khung Xem Trước Tức Thì (Realtime Multi-Ratio Live Preview) --}}
                <div class="mayhen_focal_previews_card">
                    <h3 class="mayhen_focal_previews_title">Mô Phỏng Hiển Thị Trên 4 Khung Hình Vàng Thực Tế</h3>
                    <div class="mayhen_focal_previews_grid">
                        {{-- 9:16 Dọc Mobile Story --}}
                        <div class="mayhen_focal_preview_box">
                            <div class="mayhen_focal_preview_label">Di Động Dọc (9:16)</div>
                            <div class="mayhen_focal_ratio_wrap ratio_9_16">
                                <img src="{{ $images[$firstKey]['url'] }}" class="preview_img" alt="9:16" style="object-position: {{ $images[$firstKey]['focal_point']['x'] }}% {{ $images[$firstKey]['focal_point']['y'] }}%;">
                            </div>
                            <div class="mayhen_focal_ratio_hint">Bìa thiệp điện thoại</div>
                        </div>

                        {{-- 1:1 Vuông Avatar --}}
                        <div class="mayhen_focal_preview_box">
                            <div class="mayhen_focal_preview_label">Khung Vuông (1:1)</div>
                            <div class="mayhen_focal_ratio_wrap ratio_1_1">
                                <img src="{{ $images[$firstKey]['url'] }}" class="preview_img" alt="1:1" style="object-position: {{ $images[$firstKey]['focal_point']['x'] }}% {{ $images[$firstKey]['focal_point']['y'] }}%;">
                            </div>
                            <div class="mayhen_focal_ratio_hint">Chân dung dâu rể</div>
                        </div>

                        {{-- 4:3 Cổ Điển --}}
                        <div class="mayhen_focal_preview_box">
                            <div class="mayhen_focal_preview_label">Cổ Điển Sảnh Tiệc (4:3)</div>
                            <div class="mayhen_focal_ratio_wrap ratio_4_3">
                                <img src="{{ $images[$firstKey]['url'] }}" class="preview_img" alt="4:3" style="object-position: {{ $images[$firstKey]['focal_point']['x'] }}% {{ $images[$firstKey]['focal_point']['y'] }}%;">
                            </div>
                            <div class="mayhen_focal_ratio_hint">Máy tính bảng &amp; iPad</div>
                        </div>

                        {{-- 16:9 Banner Màn Hình Rộng --}}
                        <div class="mayhen_focal_preview_box">
                            <div class="mayhen_focal_preview_label">Màn Rộng (16:9)</div>
                            <div class="mayhen_focal_ratio_wrap ratio_16_9">
                                <img src="{{ $images[$firstKey]['url'] }}" class="preview_img" alt="16:9" style="object-position: {{ $images[$firstKey]['focal_point']['x'] }}% {{ $images[$firstKey]['focal_point']['y'] }}%;">
                            </div>
                            <div class="mayhen_focal_ratio_hint">Màn LED &amp; Máy tính</div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    @else
        <div class="mayhen_focal_empty">
            <div class="mayhen_focal_empty_icon">🖼️</div>
            <h3>Chưa Có Ảnh Nào Trong Hồ Sơ Thiệp</h3>
            <p>Vui lòng tải ảnh cưới hoặc ảnh cổng vào phần thông tin thiệp cưới trước để tiến hành căn chỉnh tiêu cự.</p>
        </div>
    @endif
</div>

<style>
/* CSS Chuẩn BEM mayhen_focal_* Mây Hẹn Quiet Luxury */
.mayhen_focal_studio {
    max-width: 1240px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #2c2824;
}

.mayhen_focal_topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.mayhen_focal_back_link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #8c7355;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.2s ease;
}

.mayhen_focal_back_link:hover { color: #5c4b37; }

.mayhen_focal_header {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.mayhen_focal_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fbf9f5;
    border: 1px solid #e2d7c5;
    color: #9c7a4a;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 16px;
}

.mayhen_focal_title {
    font-size: 28px;
    font-weight: 700;
    color: #1f1d1a;
    margin: 0 0 10px;
}

.mayhen_focal_subtitle {
    font-size: 15px;
    line-height: 1.6;
    color: #6a645a;
    margin: 0;
    max-width: 850px;
}

/* Workspace 2 Cột */
.mayhen_focal_workspace {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
}

/* Sidebar Danh Sách Ảnh */
.mayhen_focal_sidebar {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 14px;
    padding: 20px;
    position: sticky;
    top: 24px;
}

.mayhen_focal_sidebar_title {
    font-size: 14px;
    font-weight: 700;
    color: #1f1d1a;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mayhen_focal_thumb_list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
}

.mayhen_focal_thumb_item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #efe8dd;
    background: #faf8f5;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mayhen_focal_thumb_item:hover {
    border-color: #d8cebd;
    background: #f5efe6;
}

.mayhen_focal_thumb_item.active {
    border-color: #b8935d;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(184, 147, 93, 0.15);
}

.mayhen_focal_thumb_preview {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    background: #e8decb;
    flex-shrink: 0;
}

.mayhen_focal_thumb_preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mayhen_focal_thumb_coord {
    position: absolute;
    bottom: 2px;
    right: 2px;
    background: rgba(31, 29, 26, 0.75);
    color: #ffffff;
    font-size: 9px;
    font-weight: 600;
    padding: 1px 4px;
    border-radius: 3px;
}

.mayhen_focal_thumb_info {
    flex: 1;
    min-width: 0;
}

.mayhen_focal_thumb_name {
    font-size: 13.5px;
    font-weight: 600;
    color: #1f1d1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.mayhen_focal_thumb_sec {
    font-size: 11.5px;
    color: #8c8376;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Cột Phải: Main Canvas & Previews */
.mayhen_focal_main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.mayhen_focal_main_card, .mayhen_focal_previews_card {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 24px;
}

.mayhen_focal_main_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.mayhen_focal_main_header h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1f1d1a;
    margin: 0 0 4px;
}

.mayhen_focal_main_header p {
    font-size: 13.5px;
    color: #8c8376;
    margin: 0;
}

.mayhen_focal_actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Khung Chấm Tiêu Điểm Canvas */
.mayhen_focal_canvas_container {
    margin-bottom: 20px;
}

.mayhen_focal_canvas_wrapper {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: #1f1d1a;
    max-height: 460px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: crosshair;
    user-select: none;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.mayhen_focal_canvas_wrapper img {
    max-width: 100%;
    max-height: 460px;
    display: block;
    object-fit: contain;
    pointer-events: none;
}

/* Tâm ngắm Reticle */
.mayhen_focal_reticle {
    position: absolute;
    width: 36px;
    height: 36px;
    transform: translate(-50%, -50%);
    pointer-events: none;
    transition: transform 0.1s ease;
}

.reticle_ring {
    width: 100%;
    height: 100%;
    border: 2px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 0 1.5px #b8935d, 0 4px 12px rgba(0, 0, 0, 0.4);
    animation: reticlePulse 2s infinite ease-in-out;
}

.reticle_dot {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    background: #b8935d;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    box-shadow: 0 0 0 1.5px #ffffff;
}

.reticle_coords {
    position: absolute;
    top: 40px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(31, 29, 26, 0.85);
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    white-space: nowrap;
    backdrop-filter: blur(4px);
}

@keyframes reticlePulse {
    0%, 100% { transform: scale(1); opacity: 0.9; }
    50% { transform: scale(1.1); opacity: 1; }
}

.mayhen_focal_canvas_hint {
    margin-top: 10px;
    font-size: 12.5px;
    color: #6a645a;
    line-height: 1.5;
}

/* 9 Nút Neo Tiêu Cự */
.mayhen_focal_anchors_box {
    background: #faf8f5;
    border: 1px solid #efe8dd;
    border-radius: 12px;
    padding: 16px;
}

.mayhen_focal_anchors_title {
    font-size: 13px;
    font-weight: 600;
    color: #4a453e;
    margin-bottom: 10px;
}

.mayhen_focal_anchors_grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.mayhen_focal_anchor_btn {
    background: #ffffff;
    border: 1px solid #d8cebd;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    color: #2c2824;
    transition: all 0.15s ease;
}

.mayhen_focal_anchor_btn:hover {
    border-color: #b8935d;
    background: #fffdf9;
    color: #9c7a4a;
}

.mayhen_focal_anchor_btn small {
    display: block;
    font-size: 10.5px;
    color: #8c8376;
    margin-top: 2px;
}

/* 4 Khung Xem Trước Tức Thì */
.mayhen_focal_previews_title {
    font-size: 16px;
    font-weight: 700;
    color: #1f1d1a;
    margin: 0 0 16px;
}

.mayhen_focal_previews_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.mayhen_focal_preview_box {
    text-align: center;
}

.mayhen_focal_preview_label {
    font-size: 12px;
    font-weight: 600;
    color: #4a453e;
    margin-bottom: 8px;
}

.mayhen_focal_ratio_wrap {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    background: #e8decb;
    border: 1px solid #d8cebd;
    margin: 0 auto 6px;
}

.mayhen_focal_ratio_wrap.ratio_9_16 { aspect-ratio: 9 / 16; max-height: 220px; }
.mayhen_focal_ratio_wrap.ratio_1_1 { aspect-ratio: 1 / 1; }
.mayhen_focal_ratio_wrap.ratio_4_3 { aspect-ratio: 4 / 3; }
.mayhen_focal_ratio_wrap.ratio_16_9 { aspect-ratio: 16 / 9; }

.mayhen_focal_ratio_wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: object-position 0.15s ease;
}

.mayhen_focal_ratio_hint {
    font-size: 11px;
    color: #8c8376;
}

/* Nút Bấm Chung */
.mayhen_focal_btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}

.mayhen_focal_btn_primary { background: #1f1d1a; color: #ffffff; }
.mayhen_focal_btn_primary:hover { background: #332f2b; }
.mayhen_focal_btn_outline { background: #ffffff; border: 1px solid #d8cebd; color: #4a453e; }
.mayhen_focal_btn_outline:hover { border-color: #b8935d; color: #1f1d1a; }
.mayhen_focal_btn_text { background: none; color: #8c7355; border: none; padding: 0 8px; }
.mayhen_focal_btn_text:hover { color: #5c4b37; text-decoration: underline; }

/* Empty state */
.mayhen_focal_empty {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #efe8dd;
}

.mayhen_focal_empty_icon { font-size: 48px; margin-bottom: 16px; }

@media (max-width: 900px) {
    .mayhen_focal_workspace { grid-template-columns: 1fr; }
    .mayhen_focal_sidebar { position: static; }
    .mayhen_focal_previews_grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .mayhen_focal_anchors_grid { grid-template-columns: 1fr; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentKey = "{{ $firstKey }}";
    let currentX = {{ $images[$firstKey]['focal_point']['x'] ?? 50 }};
    let currentY = {{ $images[$firstKey]['focal_point']['y'] ?? 50 }};

    const wrapper = document.getElementById('focalWrapper');
    const targetImg = document.getElementById('targetImage');
    const reticle = document.getElementById('reticle');
    const reticleCoords = document.getElementById('reticleCoords');
    const previewImgs = document.querySelectorAll('.preview_img');

    // Cập nhật giao diện vị trí tiêu cự
    function setFocalPoint(x, y) {
        currentX = Math.max(0, Math.min(100, Math.round(x)));
        currentY = Math.max(0, Math.min(100, Math.round(y)));

        reticle.style.left = currentX + '%';
        reticle.style.top = currentY + '%';
        reticleCoords.textContent = currentX + '%, ' + currentY + '%';

        // Cập nhật cả 4 khung xem trước tức thời
        previewImgs.forEach(img => {
            img.style.objectPosition = currentX + '% ' + currentY + '%';
        });

        // Cập nhật badge ở sidebar
        const badge = document.getElementById('badge-' + currentKey);
        if (badge) badge.textContent = currentX + '%, ' + currentY + '%';
    }

    // Nhấp chuột hoặc chạm vào ảnh để chọn tâm ngắm
    if (wrapper) {
        function handlePointer(e) {
            const rect = targetImg.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            // Tính tỷ lệ phần trăm so với kích thước thực tế của ảnh hiển thị
            const relX = ((clientX - rect.left) / rect.width) * 100;
            const relY = ((clientY - rect.top) / rect.height) * 100;

            setFocalPoint(relX, relY);
        }

        wrapper.addEventListener('click', handlePointer);

        let isDragging = false;
        wrapper.addEventListener('mousedown', () => isDragging = true);
        window.addEventListener('mouseup', () => isDragging = false);
        wrapper.addEventListener('mousemove', (e) => {
            if (isDragging) handlePointer(e);
        });

        wrapper.addEventListener('touchmove', (e) => {
            e.preventDefault();
            handlePointer(e);
        }, { passive: false });
    }

    // 9 Nút Neo Tiêu Cự Vàng
    document.querySelectorAll('.mayhen_focal_anchor_btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const x = parseFloat(this.getAttribute('data-x'));
            const y = parseFloat(this.getAttribute('data-y'));
            setFocalPoint(x, y);
        });
    });

    // Đổi ảnh ở Sidebar
    document.querySelectorAll('.mayhen_focal_thumb_item').forEach(item => {
        item.addEventListener('click', function () {
            document.querySelectorAll('.mayhen_focal_thumb_item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            currentKey = this.getAttribute('data-key');
            const title = this.getAttribute('data-title');
            const section = this.getAttribute('data-section');
            const url = this.getAttribute('data-url');
            const x = parseFloat(this.getAttribute('data-x'));
            const y = parseFloat(this.getAttribute('data-y'));

            document.getElementById('currentImageTitle').textContent = title;
            document.getElementById('currentImageSection').textContent = section;

            targetImg.src = url;
            previewImgs.forEach(img => img.src = url);

            setFocalPoint(x, y);
        });
    });

    // Nút Đặt Lại (50%, 50%)
    const btnReset = document.getElementById('btnResetFocal');
    if (btnReset) {
        btnReset.addEventListener('click', async function () {
            setFocalPoint(50, 50);
            try {
                await fetch("{{ route('account.focal_point.reset', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ image_key: currentKey })
                });
            } catch (err) {}
        });
    }

    // Nút Lưu Tiêu Điểm
    const btnSave = document.getElementById('btnSaveFocal');
    if (btnSave) {
        btnSave.addEventListener('click', async function () {
            btnSave.disabled = true;
            btnSave.innerHTML = '<span>Đang lưu...</span>';

            try {
                const res = await fetch("{{ route('account.focal_point.update', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        image_key: currentKey,
                        x: currentX,
                        y: currentY
                    })
                });

                const data = await res.json();
                if (data.success) {
                    btnSave.innerHTML = '<span>✓ Đã lưu thành công</span>';
                    btnSave.style.background = '#2e7d32';

                    // Cập nhật lại thuộc tính của item ở sidebar
                    const activeThumb = document.querySelector('.mayhen_focal_thumb_item.active');
                    if (activeThumb) {
                        activeThumb.setAttribute('data-x', currentX);
                        activeThumb.setAttribute('data-y', currentY);
                    }

                    setTimeout(() => {
                        btnSave.disabled = false;
                        btnSave.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Lưu Tiêu Điểm</span>`;
                        btnSave.style.background = '#1f1d1a';
                    }, 2500);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu'));
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<span>Lưu Tiêu Điểm</span>';
                }
            } catch (err) {
                alert('Lỗi kết nối máy chủ');
                btnSave.disabled = false;
                btnSave.innerHTML = '<span>Lưu Tiêu Điểm</span>';
            }
        });
    }
});
</script>
@endsection

