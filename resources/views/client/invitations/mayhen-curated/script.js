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
     * Tự động cuộn mượt theo tiến trình dựng thiệp trực tiếp (Live Crafting)
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
            'dresscode'
        ];

        // Timing tương ứng với delay của từng section:
        // hero: 200ms, story: 900ms, parents: 1700ms, countdown: 2500ms, events: 3300ms, timeline: 4100ms, dresscode: 4900ms
        const timings = [200, 900, 1700, 2500, 3300, 4100, 4900];

        timings.forEach(function (time, index) {
            setTimeout(function () {
                const secEl = document.querySelector('[data-curated-section="' + sections[index] + '"]');
                if (secEl) {
                    secEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (window.parent && window.parent !== window) {
                        window.parent.postMessage({
                            type: 'MAYHEN_CRAFTING_STEP',
                            step: index + 1,
                            total: 7,
                            section: sections[index],
                        }, '*');
                    }
                }
            }, time);
        });

        // Khi hoàn thành (5.8s), lướt êm dịu trở lại đầu thiệp
        setTimeout(function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'MAYHEN_CRAFTING_COMPLETE'
                }, '*');
            }
        }, 5800);
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

