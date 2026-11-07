<!DOCTYPE html>
<html lang="vi" data-theme="{{ $wallConfig['theme'] ?? 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wedding->groom_name }} & {{ $wedding->bride_name }} — Màn Chiếu Lễ Cưới Trực Tiếp | {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/live-wall.css') }}?v={{ file_exists(public_path('assets/client/css/pages/live-wall.css')) ? filemtime(public_path('assets/client/css/pages/live-wall.css')) : '1.0' }}">
</head>
<body class="mayhen_wall_body">

    <div class="mayhen_wall_stage" 
        id="mayhen-wall-root"
        data-slug="{{ $invitation->slug }}"
        data-feed-url="{{ route('live_wall.feed', $invitation->slug) }}"
        data-speed="{{ $wallConfig['speed'] ?? 6 }}"
        data-mode="{{ $wallConfig['display_mode'] ?? 'mixed' }}"
        data-theme="{{ $wallConfig['theme'] ?? 'dark' }}"
        data-initial-photos='@json($photos)'
        data-initial-wishes='@json($formattedWishes)'>

        <!-- Header Ribbon -->
        <header class="mayhen_wall_header">
            <div>
                <div class="mayhen_wall_brand_kicker">LỄ THÀNH HÔN & CHÚC PHÚC TRỰC TIẾP</div>
                <h1 class="mayhen_wall_couple_title">
                    {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
                </h1>
            </div>

            <div class="mayhen_wall_header_meta">
                <div class="mayhen_wall_live_clock" id="mayhen-wall-clock">--:--:--</div>
                <div class="mayhen_wall_wedding_date">
                    @if($wedding->wedding_date)
                        {{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d.m.Y') }}
                    @endif
                    @if($wedding->venue_name)
                        &bull; {{ $wedding->venue_name }}
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Display Canvas -->
        <main class="mayhen_wall_canvas" data-mode="{{ $wallConfig['display_mode'] ?? 'mixed' }}">
            <!-- Left Pane: Photo Slideshow -->
            <section class="mayhen_wall_slideshow_pane">
                <div id="mayhen-wall-slideshow-container" style="position: absolute; inset: 0;"></div>
                
                @if(empty($photos))
                    <div class="mayhen_wall_empty">
                        <svg class="mayhen_wall_empty_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <p style="font-size: 16px;">Đang tải khoảnh khắc kỷ niệm...</p>
                    </div>
                @endif

                <div class="mayhen_wall_photo_overlay">
                    <div class="mayhen_wall_photo_author" id="mayhen-wall-photo-author">
                        {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
                    </div>
                    <div class="mayhen_wall_photo_badge" id="mayhen-wall-photo-badge">
                        Ảnh Cưới Cặp Đôi
                    </div>
                </div>
            </section>

            <!-- Right Pane: Live Wishes Stream -->
            <section class="mayhen_wall_wishes_pane">
                <div class="mayhen_wall_wishes_header">
                    <h2 class="mayhen_wall_wishes_title">
                        <span class="mayhen_wall_wishes_pulse"></span>
                        <span>Lời Chúc Trực Tiếp</span>
                    </h2>
                </div>

                <div class="mayhen_wall_cards_stream" id="mayhen-wall-wishes-stream">
                    <!-- Cards populated dynamically via JS -->
                </div>
            </section>
        </main>

        <!-- Corner QR Widget (Cho phép quan khách quét gửi lời chúc & ảnh) -->
        @if(!isset($wallConfig['show_qr']) || $wallConfig['show_qr'])
            <div class="mayhen_wall_corner_qr">
                <div class="mayhen_wall_qr_box">
                    {!! $qrCodeSvg !!}
                </div>
                <div class="mayhen_wall_qr_label">
                    <strong>Gửi Lời Chúc & Ảnh</strong>
                    <span>Quét mã QR để hiện lên màn chiếu sân khấu!</span>
                </div>
            </div>
        @endif

        <!-- Floating Control Toolbar (Tự động ẩn sau 3.5s) -->
        <aside class="mayhen_wall_toolbar" id="mayhen-wall-toolbar" aria-label="Bảng điều khiển màn chiếu">
            <button type="button" class="mayhen_wall_tool_btn" id="mayhen-wall-btn-fullscreen">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                <span>Toàn Màn Hình</span>
            </button>

            <button type="button" class="mayhen_wall_tool_btn" id="mayhen-wall-btn-mode">
                Chế Độ: Kết Hợp
            </button>

            <button type="button" class="mayhen_wall_tool_btn" id="mayhen-wall-btn-theme">
                {{ ($wallConfig['theme'] ?? 'dark') === 'dark' ? 'Nền Tối' : 'Nền Sáng' }}
            </button>

            @if($isOwner)
                <a href="{{ route('live_wall.settings', $invitation->id) }}" class="mayhen_wall_tool_btn" style="text-decoration: none;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    <span>Cài Đặt</span>
                </a>
            @endif
        </aside>
    </div>

    <script type="module" src="{{ asset('assets/client/js/pages/live-wall.js') }}?v={{ file_exists(public_path('assets/client/js/pages/live-wall.js')) ? filemtime(public_path('assets/client/js/pages/live-wall.js')) : '1.0' }}"></script>
</body>
</html>
