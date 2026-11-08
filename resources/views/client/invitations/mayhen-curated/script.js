/**
 * MÂY HẸN (MAYHEN.VN) — CURATED INVITATION CLIENT SCRIPT
 * 100% Native Vanilla JS • Không thư viện ngoài
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initEnvelopeOpening();
        initMusicPlayer();
        initCountdown();
        initVariantSwitchers();
        initLiveCraftingScroll();
    });

    /**
     * Trình bày thiệp mượt mà theo từng hàng/từng mục (Progressive Fine-Grained Crafting)
     * Thời lượng ~18-20 giây, không nhảy cóc, không giật màn hình về đầu khi xong.
     */
    function initLiveCraftingScroll() {
        const root = document.getElementById('mayhenCuratedRoot');
        if (!root || !root.classList.contains('is-crafting-live')) return;

        const sections = [
            'hero',
            'story',
            'parents',
            'countdown',
            'events',
            'timeline',
            'album'
        ];

        // Nhịp thời gian trình bày từng section (~2.6s mỗi section, tổng ~18-20s)
        const stepInterval = 2600;
        let activeTimers = [];
        let isUserInteracting = false;

        // Phát hiện người dùng chủ động cuộn để tạm dừng auto-scroll tự động
        window.addEventListener('wheel', function () {
            isUserInteracting = true;
        }, { passive: true });
        window.addEventListener('touchmove', function () {
            isUserInteracting = true;
        }, { passive: true });
        window.addEventListener('keydown', function (e) {
            if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Space'].indexOf(e.code) !== -1) {
                isUserInteracting = true;
            }
        });

        function revealStep(index) {
            if (index >= sections.length) {
                finishCrafting();
                return;
            }

            const secKey = sections[index];
            const secEl = document.querySelector('[data-curated-section="' + secKey + '"]');

            if (secEl) {
                secEl.classList.add('is-crafting-revealed');
                secEl.classList.add('is-crafting-active-scan');

                setTimeout(function () {
                    secEl.classList.remove('is-crafting-active-scan');
                }, 1200);

                // Cuộn nhẹ nhàng đến mục đang trình diễn nếu người dùng chưa can thiệp
                if (!isUserInteracting) {
                    const rect = secEl.getBoundingClientRect();
                    const targetY = window.scrollY + rect.top - 60;
                    window.scrollTo({
                        top: Math.max(0, targetY),
                        behavior: 'smooth'
                    });
                }

                // Gửi thông điệp cập nhật tiến trình cho Studio Preview bên ngoài
                if (window.parent && window.parent !== window) {
                    window.parent.postMessage({
                        type: 'MAYHEN_CRAFTING_STEP',
                        step: index + 1,
                        total: sections.length,
                        section: secKey,
                    }, '*');
                }
            }

            const timer = setTimeout(function () {
                revealStep(index + 1);
            }, stepInterval);
            activeTimers.push(timer);
        }

        function finishCrafting() {
            clearAllTimers();
            document.querySelectorAll('.mayhen_curated_craft_node').forEach(function (node) {
                node.classList.add('is-crafting-revealed');
                node.classList.remove('is-crafting-active-scan');
            });
            root.classList.remove('is-crafting-live');

            // Giữ nguyên vị trí đang xem, tuyệt đối KHÔNG tự động nhảy về đầu trang!
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'MAYHEN_CRAFTING_COMPLETE'
                }, '*');
            }
        }

        function clearAllTimers() {
            activeTimers.forEach(function (t) { clearTimeout(t); });
            activeTimers = [];
        }

        // Bắt đầu trình diễn từ mục 1 (Hero) sau 300ms ổn định DOM
        const startTimer = setTimeout(function () {
            revealStep(0);
        }, 300);
        activeTimers.push(startTimer);

        // Lắng nghe lệnh Skip hoặc Replay từ cửa sổ cha Studio
        window.addEventListener('message', function (e) {
            if (!e.data || typeof e.data !== 'object') return;

            if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
                finishCrafting();
            } else if (e.data.type === 'MAYHEN_REPLAY_CRAFTING') {
                clearAllTimers();
                isUserInteracting = false;
                window.scrollTo({ top: 0, behavior: 'instant' });
                document.querySelectorAll('.mayhen_curated_craft_node').forEach(function (node) {
                    node.classList.remove('is-crafting-revealed');
                    node.classList.remove('is-crafting-active-scan');
                });
                root.classList.add('is-crafting-live');
                revealStep(0);
            }
        });
    }

    /**
     * Mở phong bì thiệp cưới
     */
    function initEnvelopeOpening() {
        const openBtn = document.getElementById('openInvitationBtn');
        const opening = document.getElementById('invitationOpening');
        const audio = document.getElementById('bgAudio');

        if (openBtn && opening) {
            openBtn.addEventListener('click', function () {
                opening.classList.add('is-opened');
                // Tự động phát nhạc khi người dùng tương tác mở thiệp
                if (audio) {
                    audio.play().catch(function () {
                        console.log('Autoplay audio blocked by browser policy');
                    });
                }
            });
        }
    }

    /**
     * Nút đĩa than bật/tắt nhạc
     */
    function initMusicPlayer() {
        const musicDock = document.getElementById('musicDock');
        const musicBtn = document.getElementById('musicBtn');
        const audio = document.getElementById('bgAudio');

        if (musicBtn && audio && musicDock) {
            musicBtn.addEventListener('click', function () {
                if (audio.paused) {
                    audio.play();
                    musicDock.classList.remove('is-paused');
                } else {
                    audio.pause();
                    musicDock.classList.add('is-paused');
                }
            });

            audio.addEventListener('play', function () {
                musicDock.classList.remove('is-paused');
            });
            audio.addEventListener('pause', function () {
                musicDock.classList.add('is-paused');
            });
        }
    }

    /**
     * Đồng hồ đếm ngược thời gian thực
     */
    function initCountdown() {
        const countdownBoxes = document.querySelectorAll('.mayhen_invitation_countdown');

        countdownBoxes.forEach(function (box) {
            const targetIso = box.getAttribute('data-countdown');
            if (!targetIso) return;

            const targetDate = new Date(targetIso).getTime();
            if (isNaN(targetDate)) return;

            const dayEl = box.querySelector('.mayhen_invitation_cd_day');
            const hourEl = box.querySelector('.mayhen_invitation_cd_hour');
            const minEl = box.querySelector('.mayhen_invitation_cd_min');
            const secEl = box.querySelector('.mayhen_invitation_cd_sec');

            function update() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    if (dayEl) dayEl.textContent = '00';
                    if (hourEl) hourEl.textContent = '00';
                    if (minEl) minEl.textContent = '00';
                    if (secEl) secEl.textContent = '00';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (dayEl) dayEl.textContent = days < 10 ? '0' + days : days;
                if (hourEl) hourEl.textContent = hours < 10 ? '0' + hours : hours;
                if (minEl) minEl.textContent = minutes < 10 ? '0' + minutes : minutes;
                if (secEl) secEl.textContent = seconds < 10 ? '0' + seconds : seconds;
            }

            update();
            setInterval(update, 1000);
        });
    }

    /**
     * Nút đổi biến thể section tương tác
     */
    function initVariantSwitchers() {
        const switchBtns = document.querySelectorAll('[data-switch-section]');
        switchBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const sectionKey = this.getAttribute('data-switch-section');
                if (window.parent && window.parent !== window) {
                    // Nếu đang nhúng trong iframe Studio/Crafting Preview, gửi postMessage
                    window.parent.postMessage({
                        type: 'MAYHEN_CURATED_SWITCH_SECTION',
                        section: sectionKey,
                    }, '*');
                } else {
                    // Nếu đang mở trực tiếp có token/session, gọi API đổi variant
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch('/api/curated/switch-variant', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ section: sectionKey })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(err => console.error(err));
                }
            });
        });
    }
})();

