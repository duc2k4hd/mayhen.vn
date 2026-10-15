/**
 * MAYHEN.VN — CLIENT TEMPLATES SCRIPT
 * Prefix: mayhen_templates_*
 * Aesthetic: Quiet Luxury, Smooth Transitions
 * Functionality: Auto-scroll tier bar to top (75px) on filter selection
 */

document.addEventListener('DOMContentLoaded', () => {
    initTierBarAutoScroll();
});

/**
 * Tự động cuộn trang sao cho mayhen_templates_tier_bar cách mép trên 75px
 * áp dụng cho cả Desktop và Mobile khi người dùng chọn lọc theo gói
 */
function initTierBarAutoScroll() {
    const tierBar = document.getElementById('mayhen_templates_tier_bar');
    if (!tierBar) return;

    const urlParams = new URLSearchParams(window.location.search);
    const hasTierParam = urlParams.has('tier');
    const hasTierHash = window.location.hash === '#mayhen_templates_tier_bar';

    // Nếu người dùng vừa chọn gói lọc (có ?tier=... hoặc hash #mayhen_templates_tier_bar)
    if (hasTierParam || hasTierHash) {
        scrollToTierBar(tierBar);
    }

    // Bắt sự kiện click trên các nút lọc gói để cuộn mượt mà ngay cả khi cùng trang
    const tierPills = tierBar.querySelectorAll('.mayhen_templates_tier_pill');
    tierPills.forEach(pill => {
        pill.addEventListener('click', () => {
            // Lưu cờ vào sessionStorage để chắc chắn cuộn sau khi tải trang mới
            sessionStorage.setItem('mayhen_scroll_to_tier', 'true');
        });
    });

    // Kiểm tra cờ cuộn từ sessionStorage (nếu trình duyệt không tự cuộn theo hash)
    if (sessionStorage.getItem('mayhen_scroll_to_tier') === 'true') {
        sessionStorage.removeItem('mayhen_scroll_to_tier');
        scrollToTierBar(tierBar);
    }
}

/**
 * Thực hiện cuộn mượt mà đến vị trí top 75px
 */
function scrollToTierBar(element) {
    // Độ lệch 75px trên cả Desktop và Mobile (ngay dưới chân Header)
    const TARGET_OFFSET_TOP = 75;

    // Chờ một khoảng tick nhỏ để layout rendering hoàn tất
    setTimeout(() => {
        const rect = element.getBoundingClientRect();
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        const targetScrollPosition = rect.top + currentScroll - TARGET_OFFSET_TOP;

        window.scrollTo({
            top: Math.max(0, targetScrollPosition),
            behavior: 'smooth'
        });
    }, 60);
}
