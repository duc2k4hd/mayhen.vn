<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mosaicData['config']['mosaic_title'] ?? 'Bức Tranh Ký Tên Vàng' }} — {{ $mosaicData['wedding_title'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-base: #faf8f5;
            --bg-card: #ffffff;
            --border-card: #efe8dd;
            --text-title: #1f1d1a;
            --text-body: #4a453e;
            --text-meta: #8c8376;
            --accent-gold: #b8935d;
            --accent-gold-soft: rgba(184, 147, 93, 0.15);
        }

        /* Các chủ đề ánh sắc */
        body.theme_cream_gold {
            --bg-base: #faf8f5;
            --bg-card: #ffffff;
            --border-card: #efe8dd;
            --text-title: #1f1d1a;
            --text-body: #4a453e;
            --text-meta: #8c8376;
            --accent-gold: #b8935d;
        }

        body.theme_ivory_charcoal {
            --bg-base: #f4f1ea;
            --bg-card: #ffffff;
            --border-card: #ded7c8;
            --text-title: #161514;
            --text-body: #3c3833;
            --text-meta: #787268;
            --accent-gold: #9c7a4a;
        }

        body.theme_obsidian_night {
            --bg-base: #141312;
            --bg-card: #1f1d1b;
            --border-card: #2e2b27;
            --text-title: #f5f2eb;
            --text-body: #d4cdc3;
            --text-meta: #8c857b;
            --accent-gold: #d4af37;
        }

        body.theme_dusty_rose {
            --bg-base: #fdfaf9;
            --bg-card: #ffffff;
            --border-card: #eedfdc;
            --text-title: #2a1f21;
            --text-body: #544345;
            --text-meta: #9e8588;
            --accent-gold: #c2888d;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-body);
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Nền ánh sao vàng nhẹ */
        .mayhen_mosaic_stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background: radial-gradient(circle at 50% 20%, rgba(184, 147, 93, 0.08) 0%, transparent 60%);
            z-index: 1;
        }

        /* Khung nội dung */
        .mayhen_mosaic_container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 24px 60px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header Tranh Ký Tên */
        .mayhen_mosaic_header {
            text-align: center;
            margin-bottom: 40px;
        }

        .mayhen_mosaic_monogram {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 28px;
            font-style: italic;
            color: var(--accent-gold);
            letter-spacing: 0.1em;
            margin-bottom: 8px;
            display: inline-block;
        }

        .mayhen_mosaic_title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 42px;
            font-weight: 700;
            color: var(--text-title);
            margin-bottom: 12px;
            letter-spacing: -0.01em;
        }

        .mayhen_mosaic_quote {
            font-size: 16px;
            color: var(--text-meta);
            max-width: 720px;
            margin: 0 auto 16px;
            line-height: 1.6;
            font-style: italic;
        }

        .mayhen_mosaic_kpi_bar {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 8px 20px;
            border-radius: 999px;
            font-size: 13px;
            color: var(--text-meta);
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        }

        .mayhen_mosaic_kpi_num {
            font-weight: 700;
            color: var(--accent-gold);
        }

        /* Chữ Ký Cô Dâu & Chú Rể Trang Trọng */
        .mayhen_mosaic_couple_row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .mayhen_mosaic_couple_card {
            background: var(--bg-card);
            border: 1px solid var(--accent-gold);
            border-radius: 16px;
            padding: 20px 30px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(184, 147, 93, 0.15);
            min-width: 260px;
            max-width: 340px;
        }

        .mayhen_mosaic_couple_role {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 8px;
        }

        .mayhen_mosaic_couple_img {
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .mayhen_mosaic_couple_img img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .mayhen_mosaic_couple_name {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-title);
        }

        .mayhen_mosaic_couple_heart {
            font-size: 24px;
            color: var(--accent-gold);
        }

        /* Lưới Chữ Ký Quan Khách */
        .mayhen_mosaic_grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .mayhen_mosaic_item {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .mayhen_mosaic_item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(184, 147, 93, 0.15);
            border-color: var(--accent-gold);
        }

        .mayhen_mosaic_item.pinned {
            border-color: var(--accent-gold);
            background: linear-gradient(180deg, var(--bg-card) 0%, rgba(184, 147, 93, 0.04) 100%);
        }

        .mayhen_mosaic_pin_badge {
            position: absolute;
            top: 10px;
            right: 10px;
            color: var(--accent-gold);
            font-size: 14px;
        }

        .mayhen_mosaic_item_canvas {
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .mayhen_mosaic_item_canvas img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .mayhen_mosaic_item_footer {
            border-top: 1px solid var(--border-card);
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .mayhen_mosaic_item_name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-title);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }

        .mayhen_mosaic_item_time {
            font-size: 11px;
            color: var(--text-meta);
        }

        /* Nút Điều Khiển Floating */
        .mayhen_mosaic_controls {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 100;
            display: flex;
            gap: 12px;
        }

        .mayhen_mosaic_ctrl_btn {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            color: var(--text-title);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .mayhen_mosaic_ctrl_btn:hover {
            color: var(--accent-gold);
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .mayhen_mosaic_title { font-size: 28px; }
            .mayhen_mosaic_couple_row { gap: 16px; }
            .mayhen_mosaic_grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        }
    </style>
</head>
<body class="theme_{{ $mosaicData['config']['mosaic_theme'] ?? 'cream_gold' }}">
    <div class="mayhen_mosaic_stars"></div>

    <div class="mayhen_mosaic_container">
        {{-- Header --}}
        <header class="mayhen_mosaic_header">
            <div class="mayhen_mosaic_monogram">May Hen &bull; Pure Keepsake</div>
            <h1 class="mayhen_mosaic_title">{{ $mosaicData['config']['mosaic_title'] }}</h1>
            <p class="mayhen_mosaic_quote">"{{ $mosaicData['config']['mosaic_quote'] }}"</p>
            <div class="mayhen_mosaic_kpi_bar">
                <span>Quan Khách Chung Đôi: <strong>{{ $mosaicData['wedding_title'] }}</strong></span>
                &bull;
                <span>Tổng Chữ Ký: <span class="mayhen_mosaic_kpi_num" id="liveSigCount">{{ $mosaicData['kpis']['total_signatures'] }}</span></span>
                &bull;
                <span id="liveStatusText">Cập nhật thời gian thực</span>
            </div>
        </header>

        {{-- Bút Tích Cô Dâu & Chú Rể Trang Trọng --}}
        @if(!empty($mosaicData['config']['groom_signature_url']) || !empty($mosaicData['config']['bride_signature_url']))
            <div class="mayhen_mosaic_couple_row">
                @if(!empty($mosaicData['config']['groom_signature_url']))
                    <div class="mayhen_mosaic_couple_card">
                        <div class="mayhen_mosaic_couple_role">Chú Rể</div>
                        <div class="mayhen_mosaic_couple_img">
                            <img src="{{ $mosaicData['config']['groom_signature_url'] }}" alt="Bút tích chú rể">
                        </div>
                        <div class="mayhen_mosaic_couple_name">{{ $wedding->groom_name ?? 'Chú Rể' }}</div>
                    </div>
                @endif

                <div class="mayhen_mosaic_couple_heart">❦</div>

                @if(!empty($mosaicData['config']['bride_signature_url']))
                    <div class="mayhen_mosaic_couple_card">
                        <div class="mayhen_mosaic_couple_role">Cô Dâu</div>
                        <div class="mayhen_mosaic_couple_img">
                            <img src="{{ $mosaicData['config']['bride_signature_url'] }}" alt="Bút tích cô dâu">
                        </div>
                        <div class="mayhen_mosaic_couple_name">{{ $wedding->bride_name ?? 'Cô Dâu' }}</div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Lưới Bút Tích Quan Khách --}}
        <div class="mayhen_mosaic_grid" id="mosaicGrid">
            @forelse($mosaicData['signatures'] as $sig)
                <div class="mayhen_mosaic_item {{ $sig['is_pinned'] ? 'pinned' : '' }}" id="sig-item-{{ $sig['id'] }}">
                    @if($sig['is_pinned'])
                        <div class="mayhen_mosaic_pin_badge">★</div>
                    @endif
                    <div class="mayhen_mosaic_item_canvas">
                        <img src="{{ $sig['signature_url'] }}" alt="Chữ ký của {{ $sig['name'] }}" loading="lazy">
                    </div>
                    <div class="mayhen_mosaic_item_footer">
                        <div class="mayhen_mosaic_item_name">{{ $sig['name'] }}</div>
                        <div class="mayhen_mosaic_item_time">{{ $sig['time_ago'] }}</div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-meta);">
                    Chưa có chữ ký nào. Khi quan khách ký tên trên thiệp cưới, từng nét bút sẽ tự động xuất hiện tại đây!
                </div>
            @endforelse
        </div>
    </div>

    {{-- Nút điều khiển --}}
    <div class="mayhen_mosaic_controls">
        <button type="button" class="mayhen_mosaic_ctrl_btn" id="btnFullscreen" title="Toàn màn hình (F)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
        </button>
    </div>

    <script>
        // 1. Phím tắt Toàn màn hình F
        const btnFs = document.getElementById('btnFullscreen');
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }
        if (btnFs) btnFs.addEventListener('click', toggleFullscreen);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'f' || e.key === 'F') {
                toggleFullscreen();
            }
        });

        // 2. Polling API cập nhật chữ ký mới thời gian thực (Mỗi 3.5 giây)
        const feedUrl = "{{ route('signature_archive.feed', $invitation->id) }}";
        const grid = document.getElementById('mosaicGrid');
        const countBadge = document.getElementById('liveSigCount');
        let currentSigIds = new Set(@json(array_column($mosaicData['signatures'], 'id')));

        async function pollSignatures() {
            try {
                const res = await fetch(feedUrl);
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success || !data.signatures) return;

                if (countBadge) {
                    countBadge.textContent = data.kpis.total_signatures;
                }

                // Kiểm tra có chữ ký mới chưa có trong lưới
                data.signatures.forEach(sig => {
                    if (!currentSigIds.has(sig.id)) {
                        currentSigIds.add(sig.id);

                        const card = document.createElement('div');
                        card.className = 'mayhen_mosaic_item' + (sig.is_pinned ? ' pinned' : '');
                        card.id = 'sig-item-' + sig.id;
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';

                        card.innerHTML = `
                            ${sig.is_pinned ? '<div class="mayhen_mosaic_pin_badge">★</div>' : ''}
                            <div class="mayhen_mosaic_item_canvas">
                                <img src="${sig.signature_url}" alt="Chữ ký của ${sig.name}" loading="lazy">
                            </div>
                            <div class="mayhen_mosaic_item_footer">
                                <div class="mayhen_mosaic_item_name">${sig.name}</div>
                                <div class="mayhen_mosaic_item_time">Vừa xong</div>
                            </div>
                        `;

                        grid.prepend(card);
                        setTimeout(() => {
                            card.style.transition = 'all 0.5s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }, 50);
                    }
                });
            } catch (err) {
                // Tiếp tục thử lần sau
            }
        }

        setInterval(pollSignatures, 3500);
    </script>
</body>
</html>

