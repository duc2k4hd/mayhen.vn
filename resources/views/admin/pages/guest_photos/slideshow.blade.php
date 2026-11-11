<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wedding ? ($wedding->groom_name . ' & ' . $wedding->bride_name . ' - Khoảnh Khắc Tiệc Cưới') : 'Live Wedding Guest Photo Stream' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        :root {
            --mh-admin-font: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body, html {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #050507;
            color: #ffffff;
            font-family: var(--mh-admin-font);
            user-select: none;
        }
        .slideshow-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .slide-item {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.8s ease;
        }
        .slide-item.active {
            opacity: 1;
            visibility: visible;
            z-index: 5;
        }
        .slide-bg-blur {
            position: absolute;
            inset: -20px;
            background-size: cover;
            background-position: center;
            filter: blur(40px) brightness(0.25);
            transform: scale(1.1);
            z-index: 1;
        }
        .slide-img-wrapper {
            position: relative;
            z-index: 2;
            max-width: 90vw;
            max-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            overflow: hidden;
        }
        .slide-img-wrapper img {
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
            display: block;
        }
        .slide-caption-box {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 14px 28px;
            border-radius: 50px;
            text-align: center;
            max-width: 80vw;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            animation: captionFade 0.6s ease;
        }
        .slide-uploader {
            font-size: 16px;
            font-weight: 700;
            color: #fbcfe8;
            margin-bottom: 2px;
        }
        .slide-text {
            font-size: 14px;
            color: #f1f5f9;
            font-style: italic;
        }
        .top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 20;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0) 100%);
            transition: opacity 0.4s ease;
        }
        .top-bar.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .wedding-badge {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .wedding-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #ffffff;
        }
        .wedding-sub {
            font-size: 12px;
            color: #94a3b8;
        }
        .controls-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-ctrl {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-ctrl:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            max-width: 500px;
            z-index: 10;
        }
        .empty-state h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #f1f5f9;
        }
        .empty-state p {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        @keyframes captionFade {
            from { opacity: 0; transform: translate(-50%, 10px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
    </style>
</head>
<body>

    <div class="slideshow-container" id="slideshowApp">
        <!-- Top Control Bar -->
        <div class="top-bar" id="topBar">
            <div class="wedding-badge">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <div>
                    <div class="wedding-title">
                        {{ $wedding ? ($wedding->groom_name . ' & ' . $wedding->bride_name) : 'Khoảnh Khắc Tiệc Cưới Quan Khách' }}
                    </div>
                    <div class="wedding-sub">
                        Mây Hẹn Live Photo Stream • <span id="slideIndexLabel">1/{{ count($photos) }}</span>
                    </div>
                </div>
            </div>

            <div class="controls-group">
                <button type="button" class="btn-ctrl" id="btnPrev" title="Ảnh trước">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
                <button type="button" class="btn-ctrl" id="btnPlayPause">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="iconPause"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                    <span id="labelPlayPause">Tạm dừng</span>
                </button>
                <button type="button" class="btn-ctrl" id="btnNext" title="Ảnh kế tiếp">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
                <button type="button" class="btn-ctrl" id="btnFullscreen">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                    Toàn màn hình
                </button>
                <a href="{{ route('admin.guest_photos.index', $wedding ? ['wedding_id' => $wedding->id] : []) }}" class="btn-ctrl">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Thoát
                </a>
            </div>
        </div>

        @if($photos->isEmpty())
            <div class="empty-state">
                <svg style="margin: 0 auto 16px; color: #64748b;" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <h2>Chưa có khoảnh khắc được duyệt</h2>
                <p>Màn hình sẽ tự động trình chiếu ngay khi Quản trị viên duyệt các bức ảnh mà khách dự tiệc gửi lên qua thiệp cưới.</p>
                <a href="{{ route('admin.guest_photos.index') }}" class="btn-ctrl">Về trang kiểm duyệt ảnh</a>
            </div>
        @else
            @foreach($photos as $index => $photo)
                <div class="slide-item {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                    <div class="slide-bg-blur" style="background-image: url('{{ $photo->photo_url }}');"></div>
                    <div class="slide-img-wrapper">
                        <img src="{{ $photo->photo_url }}" alt="Khoảnh khắc quan khách">
                    </div>
                    <div class="slide-caption-box">
                        <div class="slide-uploader">{{ $photo->uploader_name }}</div>
                        @if(!empty($photo->caption))
                            <div class="slide-text">"{{ $photo->caption }}"</div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @if(!$photos->isEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slide-item');
            const total = slides.length;
            let current = 0;
            let timer = null;
            let isPlaying = true;
            const intervalTime = 5000;

            const labelIndex = document.getElementById('slideIndexLabel');
            const btnPlayPause = document.getElementById('btnPlayPause');
            const labelPlayPause = document.getElementById('labelPlayPause');
            const iconPause = document.getElementById('iconPause');
            const btnPrev = document.getElementById('btnPrev');
            const btnNext = document.getElementById('btnNext');
            const btnFullscreen = document.getElementById('btnFullscreen');
            const topBar = document.getElementById('topBar');

            function showSlide(index) {
                slides.forEach(s => s.classList.remove('active'));
                current = (index + total) % total;
                slides[current].classList.add('active');
                if (labelIndex) {
                    labelIndex.textContent = (current + 1) + '/' + total;
                }
            }

            function nextSlide() {
                showSlide(current + 1);
            }

            function prevSlide() {
                showSlide(current - 1);
            }

            function startAutoplay() {
                if (timer) clearInterval(timer);
                timer = setInterval(nextSlide, intervalTime);
                isPlaying = true;
                if (labelPlayPause) labelPlayPause.textContent = 'Tạm dừng';
                if (iconPause) iconPause.innerHTML = '<rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>';
            }

            function stopAutoplay() {
                if (timer) clearInterval(timer);
                isPlaying = false;
                if (labelPlayPause) labelPlayPause.textContent = 'Tiếp tục';
                if (iconPause) iconPause.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
            }

            btnPlayPause.addEventListener('click', function() {
                if (isPlaying) {
                    stopAutoplay();
                } else {
                    startAutoplay();
                }
            });

            btnNext.addEventListener('click', function() {
                nextSlide();
                if (isPlaying) startAutoplay();
            });

            btnPrev.addEventListener('click', function() {
                prevSlide();
                if (isPlaying) startAutoplay();
            });

            // Fullscreen toggle
            btnFullscreen.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.error('Fullscreen error:', err);
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowRight' || e.key === ' ') {
                    nextSlide();
                    if (isPlaying) startAutoplay();
                } else if (e.key === 'ArrowLeft') {
                    prevSlide();
                    if (isPlaying) startAutoplay();
                } else if (e.key === 'f' || e.key === 'F') {
                    btnFullscreen.click();
                }
            });

            // Hide top bar after 3 seconds of inactivity
            let hideTimeout;
            function resetHideTimer() {
                topBar.classList.remove('hidden');
                clearTimeout(hideTimeout);
                hideTimeout = setTimeout(() => {
                    topBar.classList.add('hidden');
                }, 3500);
            }

            document.addEventListener('mousemove', resetHideTimer);
            resetHideTimer();

            // Start slideshow
            startAutoplay();

            // Auto-refresh every 60 seconds to pull newly approved photos
            setTimeout(function() {
                window.location.reload();
            }, 60000);
        });
    </script>
    @endif
</body>
</html>

