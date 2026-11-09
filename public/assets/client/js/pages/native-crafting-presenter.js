/**
 * MAYHEN.VN — NATIVE CRAFTING PRESENTER
 * Trình diễn hiệu ứng xuất hiện tiến trình (Progressive Live Crafting)
 * Trên chính giao diện của mẫu gốc đã render, 100% Native Vanilla JS.
 * Zero external libraries, non-destructive, progressive enhancement.
 */
(() => {
    // Chỉ kích hoạt khi có tham số ?crafting=1 trên URL
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('crafting')) {
        return;
    }

    // Tôn trọng thiết lập giảm chuyển động của hệ điều hành
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

    // Danh sách 7 section tiêu chuẩn theo thứ tự visual từ trên xuống dưới
    const SECTION_TARGETS = [
        { key: 'hero', title: 'Trang Bìa Chính', selector: '[data-mayhen-section*="hero"], .mayhen_invitation_hero, section.mayhen_invitation_sr_dau' },
        { key: 'story', title: 'Lời Ngỏ & Chuyện Tình', selector: '[data-mayhen-section*="story"], .mayhen_invitation_story' },
        { key: 'parents', title: 'Song Thân & Đôi Uyên Ương', selector: '[data-mayhen-section*="parents"], .mayhen_invitation_parents, .mayhen_invitation_family' },
        { key: 'countdown', title: 'Đếm Ngược & Lịch Tháng', selector: '[data-mayhen-section*="countdown"], .mayhen_invitation_countdown_calendar, .mayhen_invitation_calendar' },
        { key: 'events', title: 'Thông Tin Sự Kiện & Bản Đồ', selector: '[data-mayhen-section*="events"], [data-mayhen-section*="wedding_event"], .mayhen_invitation_events' },
        { key: 'timeline', title: 'Lịch Trình Hôn Lễ', selector: '[data-mayhen-section*="timeline"], .mayhen_invitation_timeline' },
        { key: 'album', title: 'Khoảnh Khắc Hạnh Phúc (Album 10 Ảnh)', selector: '[data-mayhen-section*="album"], [data-mayhen-section*="gallery"], .mayhen_invitation_gallery' },
    ];

    let isCompleted = false;
    let userInterrupted = false;

    // Ngừng auto-scroll nếu người dùng chủ động chạm/cuộn màn hình
    const onUserScrollOrTouch = () => {
        userInterrupted = true;
    };
    window.addEventListener('wheel', onUserScrollOrTouch, { passive: true, once: true });
    window.addEventListener('touchstart', onUserScrollOrTouch, { passive: true, once: true });

    // Tiêm style hiệu ứng chuyển động giấy mỹ thuật mượt mà
    const styleEl = document.createElement('style');
    styleEl.id = 'mayhen-native-crafting-styles';
    styleEl.textContent = `
        .mayhen_crafting_unit {
            transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .mayhen_crafting_unit.mayhen_is_hidden {
            opacity: 0 !important;
            transform: translateY(24px) !important;
            pointer-events: none !important;
        }
        .mayhen_crafting_unit.mayhen_is_revealed {
            opacity: 1 !important;
            transform: translateY(0) !important;
            pointer-events: auto !important;
        }
    `;
    document.head.appendChild(styleEl);

    // Thu thập các phần tử thực tế trên trang
    const units = [];
    SECTION_TARGETS.forEach((target) => {
        const el = document.querySelector(target.selector);
        if (el) {
            el.classList.add('mayhen_crafting_unit', 'mayhen_is_hidden');
            units.push({
                key: target.key,
                title: target.title,
                element: el,
            });
        }
    });

    if (units.length === 0) {
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        return;
    }

    const revealAllInstantly = () => {
        if (isCompleted) return;
        isCompleted = true;
        units.forEach((u) => {
            u.element.classList.remove('mayhen_is_hidden');
            u.element.classList.add('mayhen_is_revealed');
        });
        window.parent?.postMessage({ type: 'MAYHEN_CRAFTING_COMPLETE' }, '*');
        // Dọn dẹp style tạm sau khi hoàn tất
        setTimeout(() => {
            units.forEach((u) => {
                u.element.classList.remove('mayhen_crafting_unit', 'mayhen_is_revealed', 'mayhen_is_hidden');
            });
            styleEl.remove();
        }, 1200);
    };

    // Lắng nghe lệnh Skip hoặc Replay từ topbar của trang cha
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_CRAFTING_SKIP') {
            revealAllInstantly();
        }
    });

    // Thực hiện chuỗi animation xuất hiện từng khối
    let currentIndex = 0;
    const stepDuration = 1400; // 1.4 giây mỗi section (khoảng 10s cho toàn bộ 7 section)

    const revealNextUnit = () => {
        if (isCompleted) return;

        if (currentIndex >= units.length) {
            revealAllInstantly();
            return;
        }

        const unit = units[currentIndex];
        unit.element.classList.remove('mayhen_is_hidden');
        unit.element.classList.add('mayhen_is_revealed');

        // Gửi thông điệp cập nhật tiến trình cho topbar ở màn hình cha
        window.parent?.postMessage({
            type: 'MAYHEN_CRAFTING_STEP',
            section: unit.key,
            title: unit.title,
            step: currentIndex + 1,
            total: units.length,
        }, '*');

        // Cuộn nhẹ nhàng đến khối mới nếu người dùng chưa can thiệp cuộn
        if (!userInterrupted && currentIndex > 0) {
            const rect = unit.element.getBoundingClientRect();
            const targetY = window.pageYOffset + rect.top - 80;
            window.scrollTo({
                top: Math.max(0, targetY),
                behavior: 'smooth',
            });
        }

        currentIndex++;
        setTimeout(revealNextUnit, stepDuration);
    };

    // Bắt đầu chuỗi animation sau khi font chữ và hình ảnh cơ bản đã nạp
    if (document.readyState === 'complete') {
        setTimeout(revealNextUnit, 350);
    } else {
        window.addEventListener('load', () => setTimeout(revealNextUnit, 350));
    }
})();

