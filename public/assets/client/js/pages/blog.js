/**
 * MÂY HẸN (MAYHEN.VN) — EDITORIAL BLOG JAVASCRIPT
 * Pure Native ES Modules / Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Thanh Tiến Độ Đọc (Scroll Reading Progress)
    const progressBar = document.querySelector('.mayhen_blog_reading_progress');
    if (progressBar) {
        window.addEventListener('scroll', () => {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            progressBar.style.width = scrolled + '%';
        }, { passive: true });
    }

    // 2. Neo Cuộn Mục Lục Mượt Mà (Smooth TOC Scroll)
    const tocLinks = document.querySelectorAll('.mayhen_blog_toc_link');
    tocLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const targetElem = document.querySelector(targetId);
                if (targetElem) {
                    e.preventDefault();
                    targetElem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    history.pushState(null, null, targetId);
                }
            }
        });
    });

    // 3. Sao Chép Liên Kết Bài Viết (Copy Link)
    const copyBtns = document.querySelectorAll('[data-action="copy-article-link"]');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span>Đã sao chép link!</span>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            } catch (err) {
                console.warn('Copy failed:', err);
            }
        });
    });
});

