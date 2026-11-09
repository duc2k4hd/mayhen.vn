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
    /**
     * Trình bày thiệp mượt mà theo từng chi tiết hạt nhân (Fine-Grained Progressive Crafting)
     * Tiết lộ từng dòng chữ, từng khung ảnh từ trên xuống dưới, trái sang phải (~18-22s).
     * Auto-scroll thông minh, dừng ngay khi người dùng chạm, kết thúc dừng tại chỗ.
     */
    function initLiveCraftingScroll() {
        const root = document.getElementById('mayhenCuratedRoot');
        if (!root || !root.classList.contains('is-crafting-live')) return;

        // Đáp ứng CRAFT-11: Nếu người dùng bật giảm chuyển động, hiển thị ngay thành phẩm
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            root.classList.remove('is-crafting-live');
            document.querySelectorAll('.mayhen_curated_craft_node').forEach(function (node) {
                node.classList.add('is-crafting-revealed');
            });
            document.querySelectorAll('.mayhen_curated_craft_unit').forEach(function (unit) {
                unit.classList.add('is-unit-revealed');
            });
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
            }
            return;
        }

        // Quét toàn bộ các đơn vị hạt nhân (units) theo thứ tự DOM tự nhiên
        const units = Array.from(document.querySelectorAll('.mayhen_curated_craft_unit'));
        const fallbackNodes = Array.from(document.querySelectorAll('.mayhen_curated_craft_node'));
        const revealTargets = units.length > 0 ? units : fallbackNodes;

        if (revealTargets.length === 0) return;

        let currentIndex = 0;
        let activeTimer = null;
        let isUserInteracting = false;

        // Phát hiện người dùng chủ động tương tác để tạm dừng auto-scroll
        const markInteracted = function () {
            isUserInteracting = true;
        };
        window.addEventListener('wheel', markInteracted, { passive: true });
        window.addEventListener('touchmove', markInteracted, { passive: true });
        window.addEventListener('pointerdown', markInteracted, { passive: true });
        window.addEventListener('keydown', function (e) {
            if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Space'].indexOf(e.code) !== -1) {
                isUserInteracting = true;
            }
        });

        function revealNextUnit() {
            if (currentIndex >= revealTargets.length) {
                finishCrafting();
                return;
            }

            const el = revealTargets[currentIndex];
            if (el) {
                // Tiết lộ section cha nếu chưa được mở
                const parentSection = el.closest('.mayhen_curated_craft_node');
                if (parentSection && !parentSection.classList.contains('is-crafting-revealed')) {
                    parentSection.classList.add('is-crafting-revealed');
                }

                // Tiết lộ unit hiện tại
                el.classList.add('is-unit-revealed');
                el.classList.add('is-unit-drawing');

                setTimeout(function () {
                    el.classList.remove('is-unit-drawing');
                }, 500);

                // Auto-scroll nhẹ nhàng và thông minh: chỉ cuộn khi unit nằm sâu sát đáy vùng nhìn
                if (!isUserInteracting) {
                    const rect = el.getBoundingClientRect();
                    const viewHeight = window.innerHeight;
                    if (rect.bottom > viewHeight * 0.78 || rect.top < 40) {
                        const targetY = window.scrollY + rect.top - Math.round(viewHeight * 0.32);
                        window.scrollTo({
                            top: Math.max(0, targetY),
                            behavior: 'smooth'
                        });
                    }
                }

                // Gửi cập nhật tiến trình chính xác cho Studio Preview bên ngoài
                if (window.parent && window.parent !== window) {
                    const secKey = parentSection ? parentSection.getAttribute('data-curated-section') : 'curated';
                    window.parent.postMessage({
                        type: 'MAYHEN_CRAFTING_STEP',
                        step: currentIndex + 1,
                        total: revealTargets.length,
                        section: secKey,
                    }, '*');
                }
            }

            currentIndex++;

            // Tính toán nhịp thời gian tự nhiên theo loại phần tử: ảnh/card cần thời gian lâu hơn dòng text ngắn
            let stepDelay = 420;
            if (el && (el.querySelector('img') || el.classList.contains('mayhen_invitation_gallery_item') || el.classList.contains('mayhen_invitation_ticket_card'))) {
                stepDelay = 580;
            } else if (el && el.tagName === 'H2') {
                stepDelay = 480;
            }

            activeTimer = setTimeout(revealNextUnit, stepDelay);
        }

        function finishCrafting() {
            if (activeTimer) {
                clearTimeout(activeTimer);
                activeTimer = null;
            }

            // Hiện toàn bộ section và units
            document.querySelectorAll('.mayhen_curated_craft_node').forEach(function (node) {
                node.classList.add('is-crafting-revealed');
                node.classList.remove('is-crafting-active-scan');
            });
            document.querySelectorAll('.mayhen_curated_craft_unit').forEach(function (unit) {
                unit.classList.add('is-unit-revealed');
                unit.classList.remove('is-unit-drawing');
            });

            root.classList.remove('is-crafting-live');

            // Giữ nguyên vị trí đang xem, tuyệt đối KHÔNG tự động nhảy về đầu trang!
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'MAYHEN_CRAFTING_COMPLETE'
                }, '*');
            }
        }

        // Bắt đầu trình diễn sau 300ms ổn định DOM
        activeTimer = setTimeout(revealNextUnit, 300);

        // Lắng nghe lệnh Skip hoặc Replay từ cửa sổ cha Studio
        window.addEventListener('message', function (e) {
            if (!e.data || typeof e.data !== 'object') return;

            if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
                finishCrafting();
            } else if (e.data.type === 'MAYHEN_REPLAY_CRAFTING') {
                if (activeTimer) clearTimeout(activeTimer);
                isUserInteracting = false;
                currentIndex = 0;
                window.scrollTo({ top: 0, behavior: 'instant' });

                document.querySelectorAll('.mayhen_curated_craft_node').forEach(function (node) {
                    node.classList.remove('is-crafting-revealed');
                    node.classList.remove('is-crafting-active-scan');
                });
                document.querySelectorAll('.mayhen_curated_craft_unit').forEach(function (unit) {
                    unit.classList.remove('is-unit-revealed');
                    unit.classList.remove('is-unit-drawing');
                });

                root.classList.add('is-crafting-live');
                activeTimer = setTimeout(revealNextUnit, 300);
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

