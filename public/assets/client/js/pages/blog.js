/**
 * MÂY HẸN (MAYHEN.VN) — EDITORIAL BLOG JAVASCRIPT
 * Pure Native ES Modules / Zero External Libraries
 * Features: Reading Progress, Font Size Stepper, Bookmark, Copy Link, Scrollspy, Back To Top
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Toast Notification Helper
    const toast = document.getElementById('mayhen_blog_toast');
    let toastTimeout = null;

    const showToast = (message) => {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is_show');
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('is_show');
        }, 2200);
    };

    // 2. Thanh Tiến Độ Đọc (Top Scroll Progress Bar)
    const progressBar = document.getElementById('mayhen_blog_reading_progress');
    const updateProgress = () => {
        if (!progressBar) return;
        const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
        progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
    };
    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();

    // 3. Điều Chỉnh Cỡ Chữ (Font Size Stepper: A− / A+)
    const articleBody = document.getElementById('mayhen_blog_article_body');
    const fontDisplay = document.getElementById('mayhen_blog_font_size_display');
    const btnDecrease = document.getElementById('mayhen_blog_font_decrease');
    const btnIncrease = document.getElementById('mayhen_blog_font_increase');

    let currentFontSize = 17;
    try {
        const savedSize = localStorage.getItem('mayhen_blog_font_size');
        if (savedSize) {
            currentFontSize = parseInt(savedSize, 10);
            if (isNaN(currentFontSize) || currentFontSize < 14 || currentFontSize > 22) {
                currentFontSize = 17;
            }
        }
    } catch (e) {
        currentFontSize = 17;
    }

    const applyFontSize = (size, notify = false) => {
        if (articleBody) {
            articleBody.style.setProperty('--mayhen-blog-font-size', size + 'px');
        }
        if (fontDisplay) {
            fontDisplay.textContent = size + 'px';
        }
        try {
            localStorage.setItem('mayhen_blog_font_size', size);
        } catch (e) {}
        if (notify) {
            showToast(`Cỡ chữ nội dung: ${size}px`);
        }
    };
    applyFontSize(currentFontSize, false);

    if (btnDecrease) {
        btnDecrease.addEventListener('click', () => {
            if (currentFontSize > 14) {
                currentFontSize--;
                applyFontSize(currentFontSize, true);
            } else {
                showToast('Đã đạt cỡ chữ tối thiểu (14px)');
            }
        });
    }

    if (btnIncrease) {
        btnIncrease.addEventListener('click', () => {
            if (currentFontSize < 22) {
                currentFontSize++;
                applyFontSize(currentFontSize, true);
            } else {
                showToast('Đã đạt cỡ chữ tối đa (22px)');
            }
        });
    }

    // 4. Lưu Bài Viết (Bookmark System via localStorage)
    const bookmarkBtn = document.getElementById('mayhen_blog_bookmark_btn');
    if (bookmarkBtn) {
        const articleSlug = window.location.pathname.split('/').filter(Boolean).pop();
        const articleTitle = document.querySelector('.mayhen_blog_article_title')?.textContent?.trim() || document.title;

        const getSavedArticles = () => {
            try {
                return JSON.parse(localStorage.getItem('mayhen_saved_blog_articles') || '[]');
            } catch (e) {
                return [];
            }
        };

        const updateBookmarkState = () => {
            const saved = getSavedArticles();
            const isSaved = saved.some(item => item.slug === articleSlug || item.url === window.location.href);
            bookmarkBtn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
            const textSpan = bookmarkBtn.querySelector('.mayhen_blog_bookmark_text');
            if (textSpan) {
                textSpan.textContent = isSaved ? 'Đã lưu' : 'Lưu bài';
            }
        };
        updateBookmarkState();

        bookmarkBtn.addEventListener('click', () => {
            let saved = getSavedArticles();
            const existsIndex = saved.findIndex(item => item.slug === articleSlug || item.url === window.location.href);

            if (existsIndex >= 0) {
                saved.splice(existsIndex, 1);
                showToast('Đã bỏ lưu bài viết');
            } else {
                saved.unshift({
                    slug: articleSlug,
                    title: articleTitle,
                    url: window.location.href,
                    savedAt: new Date().toISOString()
                });
                showToast('Đã lưu bài viết vào cẩm nang yêu thích');
            }

            try {
                localStorage.setItem('mayhen_saved_blog_articles', JSON.stringify(saved));
            } catch (e) {}
            updateBookmarkState();
        });
    }

    // 5. Sao Chép Liên Kết Bài Viết (Copy Link)
    const setupCopyLink = (button) => {
        if (!button) return;
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                showToast('Đã sao chép liên kết bài viết');
            } catch (err) {
                const tempInput = document.createElement('input');
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showToast('Đã sao chép liên kết bài viết');
            }
        });
    };
    setupCopyLink(document.getElementById('mayhen_blog_copy_link_btn'));
    document.querySelectorAll('[data-action="copy-article-link"]').forEach(setupCopyLink);

    // 6. In Bài Viết (Print)
    const printBtn = document.getElementById('mayhen_blog_print_btn');
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }

    // 7. Neo Cuộn Mục Lục & Scrollspy
    const tocLinks = document.querySelectorAll('.mayhen_blog_toc_link');
    const headingElements = [];

    tocLinks.forEach(link => {
        const targetId = link.getAttribute('href');
        if (targetId && targetId.startsWith('#')) {
            const targetElem = document.querySelector(targetId);
            if (targetElem) {
                headingElements.push({ id: targetId.substring(1), elem: targetElem, link: link });
            }

            link.addEventListener('click', (e) => {
                e.preventDefault();
                if (targetElem) {
                    targetElem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    history.pushState(null, null, targetId);
                }
            });
        }
    });

    // Scrollspy highlight mục đang đọc
    if (headingElements.length > 0) {
        const onScrollHighlight = () => {
            const scrollPos = window.scrollY + 120;
            let activeId = null;

            for (let i = 0; i < headingElements.length; i++) {
                const item = headingElements[i];
                if (item.elem.offsetTop <= scrollPos) {
                    activeId = item.id;
                } else {
                    break;
                }
            }

            tocLinks.forEach(link => {
                const targetId = link.getAttribute('data-toc-id') || link.getAttribute('href')?.replace('#', '');
                if (targetId === activeId) {
                    link.classList.add('is_active');
                } else {
                    link.classList.remove('is_active');
                }
            });
        };
        window.addEventListener('scroll', onScrollHighlight, { passive: true });
        onScrollHighlight();
    }

    // 8. Mobile TOC Toggle
    const tocToggleMobile = document.getElementById('mayhen_blog_toc_toggle_mobile');
    const tocWidget = document.getElementById('mayhen_blog_toc_widget');
    if (tocToggleMobile && tocWidget) {
        tocToggleMobile.addEventListener('click', () => {
            const isOpen = tocWidget.classList.toggle('is_open');
            tocToggleMobile.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // 9. Nút Cuộn Lên Đầu Trang (Back To Top)
    const backToTopBtn = document.getElementById('mayhen_blog_back_to_top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 280) {
                backToTopBtn.classList.add('is_visible');
            } else {
                backToTopBtn.classList.remove('is_visible');
            }
        }, { passive: true });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
