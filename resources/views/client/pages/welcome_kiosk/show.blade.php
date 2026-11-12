<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $initData['config']['kiosk_title'] }} — {{ $initData['groom_name'] }} &amp; {{ $initData['bride_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            user-select: none;
            -webkit-user-select: none;
        }
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #141210;
            font-family: 'Inter', sans-serif;
            color: #FFFFFF;
        }

        /* Nền Slideshow Ảnh Cưới Mờ Ken Burns */
        .kiosk_bg_slideshow {
            position: absolute;
            inset: 0;
            z-index: 1;
            background-size: cover;
            background-position: center;
            filter: brightness(0.4) saturate(1.1);
            transition: background-image 1.5s ease-in-out;
            transform: scale(1.05);
            animation: slowPan 30s infinite alternate ease-in-out;
        }
        @keyframes slowPan {
            0% { transform: scale(1.05) translate(0, 0); }
            100% { transform: scale(1.12) translate(-1%, -1%); }
        }

        .kiosk_overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            background: radial-gradient(circle at center, rgba(20, 18, 16, 0.4) 0%, rgba(20, 18, 16, 0.88) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
        }

        /* Chế độ chờ (Ambient Standby) */
        .ambient_container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            transition: opacity 0.5s ease;
        }
        .monogram_seal {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #C5A880;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            color: #C5A880;
            margin-bottom: 20px;
            box-shadow: 0 0 25px rgba(197, 168, 128, 0.3);
            background: rgba(31, 29, 26, 0.6);
        }
        .ambient_kicker {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #C5A880;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .ambient_couple_title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 58px;
            font-weight: 700;
            color: #FFFFFF;
            margin: 0 0 10px 0;
            letter-spacing: 1px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.6);
        }
        .ambient_venue_meta {
            font-size: 18px;
            color: #EFE8DD;
            margin-bottom: 24px;
            font-weight: 400;
        }

        /* Đồng hồ đếm ngược */
        .countdown_box {
            display: inline-flex;
            gap: 16px;
            background: rgba(31, 29, 26, 0.7);
            border: 1px solid rgba(197, 168, 128, 0.4);
            padding: 12px 24px;
            border-radius: 40px;
            backdrop-filter: blur(8px);
        }
        .countdown_item {
            text-align: center;
        }
        .countdown_num {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 700;
            color: #C5A880;
            line-height: 1;
        }
        .countdown_label {
            font-size: 10px;
            text-transform: uppercase;
            color: #A39A91;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* Popup Chào Mừng Kích Hoạt (Active Welcome Card) */
        .welcome_overlay {
            display: none;
            position: absolute;
            inset: 0;
            z-index: 10;
            background: rgba(15, 13, 11, 0.94);
            backdrop-filter: blur(12px);
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeInModal 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeInModal {
            0% { opacity: 0; transform: scale(0.92); }
            100% { opacity: 1; transform: scale(1); }
        }

        .welcome_card {
            background: #1F1D1A;
            border: 2px solid #C5A880;
            border-radius: 28px;
            padding: 44px 36px;
            max-width: 680px;
            width: 100%;
            text-align: center;
            box-shadow: 0 0 80px rgba(197, 168, 128, 0.35);
            position: relative;
            overflow: hidden;
        }

        .welcome_flourish {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #C5A880;
            font-weight: 700;
            margin-bottom: 14px;
            display: inline-block;
        }
        .welcome_guest_name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 50px;
            font-weight: 700;
            color: #FFFFFF;
            margin: 0 0 16px 0;
            line-height: 1.15;
            text-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .welcome_table_badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #9C7A4A 0%, #C5A880 100%);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 20px;
            padding: 10px 28px;
            border-radius: 40px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 20px rgba(156, 122, 74, 0.4);
        }
        .welcome_quote_text {
            font-size: 16px;
            color: #E5DCCD;
            line-height: 1.5;
            margin: 0 0 24px 0;
            font-style: italic;
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Thanh tiến trình đếm ngược */
        .welcome_progress_bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 5px;
            background: #C5A880;
            width: 100%;
            transform-origin: left;
        }

        /* Nút toàn màn hình kín đáo */
        .fullscreen_btn {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 20;
            background: rgba(31, 29, 26, 0.5);
            border: 1px solid rgba(197, 168, 128, 0.3);
            color: #C5A880;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            backdrop-filter: blur(4px);
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .fullscreen_btn:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    {{-- Nền Slideshow Ảnh Ken Burns --}}
    <div id="kiosk_bg" class="kiosk_bg_slideshow" style="background-image: url('{{ $initData['config']['photos'][0] ?? '' }}');"></div>

    {{-- Lớp phủ trung tâm Kiosk --}}
    <div class="kiosk_overlay">
        {{-- Nút toàn màn hình & Audio toggle --}}
        <div style="position: absolute; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 25;">
            <button type="button" class="fullscreen_btn" id="audio_toggle_btn" onclick="toggleAudio()" style="position: static;">
                🔔 Âm thanh: Bật
            </button>
            <button type="button" class="fullscreen_btn" onclick="toggleFullscreen()" style="position: static;">
                ⛶ Toàn màn hình
            </button>
        </div>

        {{-- Monogram --}}
        <div class="monogram_seal">
            {{ mb_substr($initData['groom_name'], 0, 1) }}&amp;{{ mb_substr($initData['bride_name'], 0, 1) }}
        </div>

        {{-- Nội Dung Chế Độ Chờ --}}
        <div id="ambient_box" class="ambient_container">
            <div class="ambient_kicker">{{ $initData['config']['kiosk_title'] }}</div>
            <h1 class="ambient_couple_title">
                {{ $initData['groom_name'] }} &amp; {{ $initData['bride_name'] }}
            </h1>
            <div class="ambient_venue_meta">
                📍 {{ $initData['venue_name'] }} • Ngày {{ $initData['wedding_date_formatted'] }}
            </div>

            @if(!empty($initData['config']['show_countdown']))
                <div class="countdown_box" id="countdown_element">
                    <div class="countdown_item">
                        <div class="countdown_num" id="cnt_hours">00</div>
                        <div class="countdown_label">Giờ</div>
                    </div>
                    <div style="color: #C5A880; font-size: 20px; font-weight: bold;">:</div>
                    <div class="countdown_item">
                        <div class="countdown_num" id="cnt_mins">00</div>
                        <div class="countdown_label">Phút</div>
                    </div>
                    <div style="color: #C5A880; font-size: 20px; font-weight: bold;">:</div>
                    <div class="countdown_item">
                        <div class="countdown_num" id="cnt_secs">00</div>
                        <div class="countdown_label">Giây</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Lời Nhắn --}}
        <div style="font-size: 13.5px; color: #C5A880; letter-spacing: 0.5px; text-align: center; opacity: 0.85;">
            ✨ Kính chúc Quý Quan Khách một buổi tiệc trọn vẹn niềm vui và hạnh phúc ✨
        </div>
    </div>

    {{-- Khung Popup Chào Mừng VIP Kích Hoạt --}}
    <div id="welcome_modal" class="welcome_overlay">
        <div class="welcome_card">
            <span class="welcome_flourish">✨ Nhiệt Liệt Chào Đón ✨</span>
            <h2 id="guest_welcome_name" class="welcome_guest_name">
                Bác Nguyễn Văn Nam
            </h2>

            @if(!empty($initData['config']['show_table_name']))
                <div id="guest_welcome_table" class="welcome_table_badge">
                    ✨ BÀN SỐ 08 (VIP)
                </div>
            @endif

            <p class="welcome_quote_text">
                "{{ $initData['config']['welcome_quote'] }}"
            </p>

            <div style="font-size: 13px; color: #A39A91;">
                Cảm ơn Quý Khách đã tới chung vui cùng gia đình chúng em!
            </div>

            {{-- Progress bar đếm ngược ẩn modal --}}
            <div id="welcome_timer_bar" class="welcome_progress_bar"></div>
        </div>
    </div>

    <script>
        // Cấu hình Kiosk từ backend
        const kioskConfig = @json($initData['config']);
        const photos = kioskConfig.photos && kioskConfig.photos.length > 0 ? kioskConfig.photos : [];
        let currentPhotoIndex = 0;

        let soundEnabled = kioskConfig.sound_enabled;
        let lastTimestampMs = {{ $initData['server_timestamp_ms'] }};
        const durationSeconds = kioskConfig.welcome_duration_seconds || 8;
        const feedUrl = "{{ route('welcome_kiosk.feed', $invitation->slug) }}";

        let popupTimer = null;
        let progressInterval = null;

        // 1. Slideshow Ảnh Nền Chuyển Mượt Sau Mỗi 7 Giây
        if (photos.length > 1) {
            setInterval(() => {
                currentPhotoIndex = (currentPhotoIndex + 1) % photos.length;
                document.getElementById('kiosk_bg').style.backgroundImage = `url('${photos[currentPhotoIndex]}')`;
            }, 7000);
        }

        // 2. Đồng hồ đếm ngược giờ tiệc
        const weddingDateStr = "{{ $initData['wedding_date'] }} {{ $initData['party_time'] }}:00";
        function updateCountdown() {
            if (!document.getElementById('cnt_hours')) return;
            const target = new Date(weddingDateStr).getTime();
            const now = new Date().getTime();
            let diff = Math.max(0, target - now);

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('cnt_hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('cnt_mins').textContent = String(mins).padStart(2, '0');
            document.getElementById('cnt_secs').textContent = String(secs).padStart(2, '0');
        }
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // 3. Chuông Pha Lê Hoàng Gia (Pure Web Audio API - Zero External Audio File)
        function playCrystalChime() {
            if (!soundEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const now = ctx.currentTime;
                // Chuỗi hợp âm pha lê C5 - E5 - G5 - C6
                const freqs = [523.25, 659.25, 783.99, 1046.50];
                freqs.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.12);

                    gain.gain.setValueAtTime(0.001, now + idx * 0.12);
                    gain.gain.exponentialRampToValueAtTime(0.25, now + idx * 0.12 + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.12 + 2.0);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + idx * 0.12);
                    osc.stop(now + idx * 0.12 + 2.2);
                });
            } catch (e) {
                console.log('Audio Context error or autoplay locked:', e);
            }
        }

        // 4. Kích hoạt Popup Chào Mừng Khi Có Khách Vừa Check-in
        function triggerWelcomePopup(checkin) {
            const modal = document.getElementById('welcome_modal');
            const nameEl = document.getElementById('guest_welcome_name');
            const tableEl = document.getElementById('guest_welcome_table');
            const progressBar = document.getElementById('welcome_timer_bar');

            nameEl.textContent = (checkin.salutation ? checkin.salutation + ' ' : '') + checkin.name;
            if (tableEl) {
                tableEl.textContent = '✨ VỊ TRÍ AN TỌA: ' + (checkin.table_name || 'Bàn Tiệc').toUpperCase();
            }

            // Mở modal
            modal.style.display = 'flex';
            playCrystalChime();

            // Khởi tạo thanh progress bar đếm ngược
            if (popupTimer) clearTimeout(popupTimer);
            if (progressInterval) clearInterval(progressInterval);

            progressBar.style.transform = 'scaleX(1)';
            const startTime = Date.now();
            const totalMs = durationSeconds * 1000;

            progressInterval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const remainingRatio = Math.max(0, 1 - (elapsed / totalMs));
                progressBar.style.transform = `scaleX(${remainingRatio})`;
                if (remainingRatio <= 0) {
                    clearInterval(progressInterval);
                }
            }, 50);

            popupTimer = setTimeout(() => {
                modal.style.display = 'none';
                clearInterval(progressInterval);
            }, totalMs);
        }

        // 5. Polling Realtime Check-in Feed (1.5 giây / lần)
        let isPolling = false;
        setInterval(() => {
            if (isPolling) return;
            isPolling = true;

            fetch(`${feedUrl}?since=${lastTimestampMs}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                isPolling = false;
                if (data.success && data.checkin) {
                    const checkin = data.checkin;
                    if (checkin.timestamp_ms > lastTimestampMs) {
                        lastTimestampMs = checkin.timestamp_ms;
                        triggerWelcomePopup(checkin);
                    }
                }
            })
            .catch(() => {
                isPolling = false;
            });
        }, 1500);

        // 6. Toàn màn hình & Âm thanh
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }

        function toggleAudio() {
            soundEnabled = !soundEnabled;
            document.getElementById('audio_toggle_btn').textContent = soundEnabled ? '🔔 Âm thanh: Bật' : '🔕 Âm thanh: Tắt';
        }

        // Bật Audio Context khi người dùng chạm màn hình lần đầu
        window.addEventListener('click', () => {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (ctx.state === 'suspended') ctx.resume();
            } catch(e) {}
        }, { once: true });
    </script>
</body>
</html>

