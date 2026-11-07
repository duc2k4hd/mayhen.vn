/**
 * MAYHEN.VN — LIVE WEDDING PROJECTION WALL JAVASCRIPT
 * Strictly Native ES Modules (Zero External Libraries, 60fps Animation)
 */

document.addEventListener('DOMContentLoaded', () => {
    const wallRoot = document.getElementById('mayhen-wall-root');
    if (!wallRoot) return;

    const slug = wallRoot.dataset.slug;
    const feedUrl = wallRoot.dataset.feedUrl;
    let speedMs = parseInt(wallRoot.dataset.speed || '6', 10) * 1000;
    let currentMode = wallRoot.dataset.mode || 'mixed';
    let currentTheme = wallRoot.dataset.theme || 'dark';

    // 1. Đồng Hồ Thời Gian Thực (Live Clock)
    const clockEl = document.getElementById('mayhen-wall-clock');
    function updateClock() {
        if (!clockEl) return;
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        clockEl.textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // 2. Tự Động Ẩn Toolbar Sau 3s Không Di Chuột
    const toolbar = document.getElementById('mayhen-wall-toolbar');
    let hideTimer = null;

    function showToolbar() {
        if (!toolbar) return;
        toolbar.classList.add('is-visible');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => {
            toolbar.classList.remove('is-visible');
        }, 3500);
    }

    window.addEventListener('mousemove', showToolbar, { passive: true });
    window.addEventListener('click', showToolbar, { passive: true });
    window.addEventListener('touchstart', showToolbar, { passive: true });
    showToolbar(); // Hiển thị ban đầu rồi tự ẩn

    // 3. Toàn Màn Hình (Fullscreen API)
    const fullscreenBtn = document.getElementById('mayhen-wall-btn-fullscreen');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
                fullscreenBtn.innerHTML = `
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/></svg>
                    <span>Thu Nhỏ</span>
                `;
            } else {
                document.exitFullscreen().catch(() => {});
                fullscreenBtn.innerHTML = `
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                    <span>Toàn Màn Hình</span>
                `;
            }
        });
    }

    // 4. Quản Lý Slideshow Ảnh Luân Chuyển
    const slideshowContainer = document.getElementById('mayhen-wall-slideshow-container');
    const photoAuthorEl = document.getElementById('mayhen-wall-photo-author');
    const photoBadgeEl = document.getElementById('mayhen-wall-photo-badge');

    let photos = [];
    try {
        const rawPhotos = wallRoot.dataset.initialPhotos;
        photos = rawPhotos ? JSON.parse(rawPhotos) : [];
    } catch (e) {
        photos = [];
    }

    let currentPhotoIdx = 0;
    let slideshowInterval = null;

    function renderPhoto(photo) {
        if (!slideshowContainer || !photo) return;

        // Xóa slide cũ (giữ tối đa 1 slide active để chuyển cảnh crossfade)
        const existingSlides = slideshowContainer.querySelectorAll('.mayhen_wall_photo_slide');
        const img = document.createElement('img');
        img.className = 'mayhen_wall_photo_slide';
        img.src = photo.url;
        img.alt = photo.caption || 'Khoảnh khắc ngày cưới';

        img.onload = () => {
            slideshowContainer.appendChild(img);
            // Trigger reflow
            void img.offsetWidth;
            img.classList.add('active');

            // Cập nhật chú thích & người chụp
            if (photoAuthorEl) {
                photoAuthorEl.textContent = photo.author || 'Mây Hẹn';
            }
            if (photoBadgeEl) {
                photoBadgeEl.textContent = photo.type === 'guest_photo' ? 'Ảnh Khách Chụp' : 'Ảnh Cưới Cặp Đôi';
            }

            // Loại bỏ slide cũ sau khi crossfade xong
            setTimeout(() => {
                existingSlides.forEach(el => el.remove());
            }, 1400);
        };
    }

    function nextPhoto() {
        if (!photos.length) return;
        currentPhotoIdx = (currentPhotoIdx + 1) % photos.length;
        renderPhoto(photos[currentPhotoIdx]);
    }

    function startSlideshow() {
        clearInterval(slideshowInterval);
        if (photos.length > 0) {
            renderPhoto(photos[currentPhotoIdx]);
            if (photos.length > 1) {
                slideshowInterval = setInterval(nextPhoto, speedMs);
            }
        }
    }
    startSlideshow();

    // 5. Quản Lý Dòng Lời Chúc (Live Wishes Stream)
    const wishesContainer = document.getElementById('mayhen-wall-wishes-stream');
    let wishes = [];
    try {
        const rawWishes = wallRoot.dataset.initialWishes;
        wishes = rawWishes ? JSON.parse(rawWishes) : [];
    } catch (e) {
        wishes = [];
    }

    function createWishCard(wish) {
        const card = document.createElement('div');
        card.className = 'mayhen_wall_wish_card';

        const header = document.createElement('div');
        header.className = 'mayhen_wall_wish_header';

        const nameSpan = document.createElement('div');
        nameSpan.className = 'mayhen_wall_wish_name';
        nameSpan.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        `;
        const nameText = document.createTextNode(wish.name || 'Quan Khách');
        nameSpan.appendChild(nameText);

        const timeSpan = document.createElement('div');
        timeSpan.className = 'mayhen_wall_wish_time';
        timeSpan.textContent = wish.time_ago || 'Vừa xong';

        header.appendChild(nameSpan);
        header.appendChild(timeSpan);

        const textDiv = document.createElement('div');
        textDiv.className = 'mayhen_wall_wish_text';
        textDiv.textContent = wish.message || '';

        card.appendChild(header);
        card.appendChild(textDiv);
        return card;
    }

    function renderWishesStream() {
        if (!wishesContainer) return;
        wishesContainer.innerHTML = '';

        if (!wishes.length) {
            wishesContainer.innerHTML = `
                <div class="mayhen_wall_empty">
                    <p>Chưa có lời chúc mừng nào. Hãy là người đầu tiên gửi lời chúc!</p>
                </div>
            `;
            return;
        }

        // Hiển thị tối đa 4 lời chúc gần nhất
        const displayWishes = wishes.slice(0, 4);
        displayWishes.forEach(w => {
            wishesContainer.appendChild(createWishCard(w));
        });
    }
    renderWishesStream();

    // 6. Polling Cập Nhật Dữ Liệu Thời Gian Thực Mỗi 8 Giây
    async function pollLiveFeed() {
        if (!feedUrl) return;
        try {
            const res = await fetch(feedUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.success) {
                // Kiểm tra ảnh mới
                if (data.photos && data.photos.length > 0) {
                    const oldLength = photos.length;
                    photos = data.photos;
                    if (oldLength === 0 && photos.length > 0) {
                        startSlideshow();
                    }
                }

                // Kiểm tra lời chúc mới
                if (data.wishes && Array.isArray(data.wishes)) {
                    const latestNewId = data.wishes[0]?.id;
                    const latestCurrentId = wishes[0]?.id;
                    if (latestNewId !== latestCurrentId) {
                        wishes = data.wishes;
                        renderWishesStream();
                    }
                }
            }
        } catch (err) {
            // Im lặng nếu mạng chập chờn để không làm gián đoạn màn chiếu
        }
    }
    setInterval(pollLiveFeed, 8500);

    // 7. Chuyển Đổi Giao Diện Sáng / Tối (Theme Toggle)
    const themeBtn = document.getElementById('mayhen-wall-btn-theme');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', currentTheme);
            themeBtn.textContent = currentTheme === 'dark' ? 'Nền Tối' : 'Nền Sáng';
        });
    }

    // 8. Chuyển Đổi Chế Độ Hiển Thị (Mode Toggle)
    const modeBtn = document.getElementById('mayhen-wall-btn-mode');
    const canvasEl = document.querySelector('.mayhen_wall_canvas');
    if (modeBtn && canvasEl) {
        const modes = ['mixed', 'photos_only', 'wishes_only'];
        const modeLabels = {
            'mixed': 'Chế Độ: Kết Hợp',
            'photos_only': 'Chế Độ: Chỉ Ảnh',
            'wishes_only': 'Chế Độ: Chỉ Lời Chúc'
        };

        modeBtn.addEventListener('click', () => {
            const nextIdx = (modes.indexOf(currentMode) + 1) % modes.length;
            currentMode = modes[nextIdx];
            canvasEl.setAttribute('data-mode', currentMode);
            modeBtn.textContent = modeLabels[currentMode];
        });
    }
});

