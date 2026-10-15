/**
 * MAYHEN.VN — CLIENT TEMPLATE DETAIL SCRIPT
 * Feature: Showcase Reel (Poster Image on load -> Autoplays after 3s without hover -> Restarts from beginning when hovered)
 * Strict: Native ES Module, Zero external libraries
 */

let isStageInitialized = false;

function safeInitStageScroller() {
    if (isStageInitialized) return;
    isStageInitialized = true;
    initTemplateDetailStageScroller();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', safeInitStageScroller);
} else {
    safeInitStageScroller();
}
window.addEventListener('load', safeInitStageScroller);

function initTemplateDetailStageScroller() {
    const media = document.getElementById('mayhen_stage_media');
    const reel = document.getElementById('mayhen_stage_reel');
    const poster = document.getElementById('mayhen_stage_poster');
    const iframe = document.getElementById('mayhen_stage_iframe');
    const btnAutoScroll = document.getElementById('mayhen_btn_autoscroll');

    if (!media || !reel || !poster || !iframe) {
        return;
    }

    let currentReelOffset = 0;
    let animFrameId = null;
    let pauseTimer = null;
    let autoPlayTimer = null;
    let isAutoRunning = false;
    let isHovering = false;

    // Vận tốc chuyển động
    const REEL_SPEED = 5.4; // Tốc độ trượt ảnh poster lên (px / frame)
    const IFRAME_SCROLL_SPEED = 3.2; // Tốc độ cuộn trang thiệp trong iframe sau khi poster trượt hết

    // Helper cập nhật trạng thái nút bấm trên Mobile
    function setButtonState(running) {
        isAutoRunning = running;
        if (!btnAutoScroll) return;

        const playIcon = btnAutoScroll.querySelector('.mayhen_btn_autoscroll_icon_play');
        const pauseIcon = btnAutoScroll.querySelector('.mayhen_btn_autoscroll_icon_pause');
        const textSpan = btnAutoScroll.querySelector('.mayhen_btn_autoscroll_text');

        if (running) {
            btnAutoScroll.classList.add('mayhen_btn_active');
            if (playIcon) playIcon.style.display = 'none';
            if (pauseIcon) pauseIcon.style.display = 'inline-block';
            if (textSpan) textSpan.textContent = 'Dừng Lại';
        } else {
            btnAutoScroll.classList.remove('mayhen_btn_active');
            if (playIcon) playIcon.style.display = 'inline-block';
            if (pauseIcon) pauseIcon.style.display = 'none';
            if (textSpan) textSpan.textContent = 'Chạy Thử';
        }
    }

    // Lấy context cuộn bên trong iframe
    function getScrollContext() {
        try {
            const win = iframe.contentWindow;
            const doc = iframe.contentDocument || win?.document;
            if (!win || !doc) return null;

            const scroller = doc.scrollingElement || doc.documentElement || doc.body;
            if (!scroller) return null;

            const maxScroll = Math.max(0, scroller.scrollHeight - scroller.clientHeight);
            return { win, doc, scroller, maxScroll };
        } catch (e) {
            return null;
        }
    }

    // Hủy mọi hoạt ảnh và timer đang chạy
    function cancelScrollAnimation() {
        if (animFrameId) {
            cancelAnimationFrame(animFrameId);
            animFrameId = null;
        }
        if (pauseTimer) {
            clearTimeout(pauseTimer);
            pauseTimer = null;
        }
    }

    // Vòng lặp chuyển động: Đẩy poster lên -> Iframe bám đít theo sau -> Cuộn trang thiệp
    function startAnimationLoop(onComplete) {
        cancelScrollAnimation();
        reel.style.transition = 'none';

        function step() {
            const stageHeight = media.offsetHeight || media.clientHeight || 540;

            // GIAI ĐOẠN 1: Đẩy ảnh đại diện lên trên, iframe bám ngay đít cuộn lên theo
            if (currentReelOffset < stageHeight) {
                currentReelOffset = Math.min(stageHeight, currentReelOffset + REEL_SPEED);
                reel.style.transform = `translateY(-${currentReelOffset}px)`;
                animFrameId = requestAnimationFrame(step);
                return;
            }

            // GIAI ĐOẠN 2: Khi poster đã lên hết (iframe chiếm trọn khung), cuộn nội dung trong iframe
            reel.style.transform = `translateY(-${stageHeight}px)`;

            const ctx = getScrollContext();
            if (ctx && ctx.maxScroll > 5) {
                const { win, scroller, maxScroll } = ctx;
                if (scroller.scrollTop < maxScroll - 2) {
                    win.scrollBy(0, IFRAME_SCROLL_SPEED);
                    animFrameId = requestAnimationFrame(step);
                } else {
                    animFrameId = null;
                    if (onComplete) onComplete();
                }
            } else {
                animFrameId = null;
                if (onComplete) onComplete();
            }
        }

        animFrameId = requestAnimationFrame(step);
    }

    // Trả lại trạng thái ban đầu: Cuộn ngược về ảnh poster hiển thị trọn vẹn
    function resetToPoster(smooth = true) {
        cancelScrollAnimation();

        // Đưa nội dung iframe về đỉnh
        const ctx = getScrollContext();
        if (ctx) {
            try {
                ctx.win.scrollTo({ top: 0, behavior: 'instant' });
            } catch (e) {}
        }

        if (smooth) {
            reel.style.transition = 'transform 0.55s cubic-bezier(0.25, 1, 0.5, 1)';
            reel.style.transform = 'translateY(0px)';
            currentReelOffset = 0;

            setTimeout(() => {
                reel.style.transition = 'none';
            }, 600);
        } else {
            reel.style.transition = 'none';
            reel.style.transform = 'translateY(0px)';
            currentReelOffset = 0;
        }
    }

    // 1. Tự động chạy trong vòng 3 giây sau khi tải trang (không cần hover)
    function scheduleAutoPlay() {
        if (autoPlayTimer) {
            clearTimeout(autoPlayTimer);
        }

        autoPlayTimer = setTimeout(() => {
            if (!isHovering) {
                setButtonState(true);

                startAnimationLoop(() => {
                    // Khi chạm đáy thiệp: dừng lại 2 giây rồi thu hồi về ảnh poster ban đầu
                    pauseTimer = setTimeout(() => {
                        resetToPoster(true);
                        setButtonState(false);
                    }, 2000);
                });
            }
        }, 3000);
    }

    // 2. Xử lý Hover trên Desktop: Khi hover vào SẼ CHẠY LẠI TỪ ĐẦU
    media.addEventListener('mouseenter', () => {
        isHovering = true;
        media.classList.add('mayhen_is_hovered');

        // Hủy hẹn giờ tự chạy nếu còn
        if (autoPlayTimer) {
            clearTimeout(autoPlayTimer);
            autoPlayTimer = null;
        }

        // Chạy lại từ đầu: Reset ngay lập tức về poster ban đầu và chạy lại
        resetToPoster(false);
        startAnimationLoop();
    });

    media.addEventListener('mouseleave', () => {
        isHovering = false;
        media.classList.remove('mayhen_is_hovered');

        // Thu hồi êm ái về ảnh poster ban đầu
        resetToPoster(true);
    });

    // 3. Xử lý nút "Chạy Thử" trên Mobile
    if (btnAutoScroll) {
        function stopMobileDemo() {
            setButtonState(false);
            resetToPoster(true);
        }

        function startMobileDemo() {
            setButtonState(true);
            resetToPoster(false);

            startAnimationLoop(() => {
                pauseTimer = setTimeout(() => {
                    resetToPoster(true);
                    pauseTimer = setTimeout(() => {
                        setButtonState(false);
                    }, 600);
                }, 1600);
            });
        }

        btnAutoScroll.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (autoPlayTimer) {
                clearTimeout(autoPlayTimer);
                autoPlayTimer = null;
            }

            if (isAutoRunning) {
                stopMobileDemo();
            } else {
                startMobileDemo();
            }
        });
    }

    // Ban đầu hiển thị ảnh poster 100%
    resetToPoster(false);

    // Kích hoạt bộ đếm tự chạy trong vòng 3 giây
    scheduleAutoPlay();
}
