/**
 * MAYHEN.VN — CLIENT LEGAL & POLICY PAGES SCRIPT
 * Quy chuẩn: 100% Vanilla JavaScript Native ES Modules
 * Tính năng: ScrollSpy mục lục Table of Contents, Smooth Scroll điều hướng, In tài liệu pháp lý.
 */

document.addEventListener('DOMContentLoaded', () => {
    const tocLinks = document.querySelectorAll('.mayhen_legal_toc_link');
    const sections = document.querySelectorAll('.mayhen_legal_section');
    const printBtn = document.getElementById('mayhen_legal_print_btn');

    // 1. Phím in tài liệu
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }

    // 2. Smooth Scroll khi bấm vào mục lục
    tocLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, '', targetId);
                }
            }
        });
    });

    // 3. ScrollSpy theo dõi phần tử đang đọc
    if ('IntersectionObserver' in window && sections.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -70% 0px',
            threshold: 0,
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    tocLinks.forEach((link) => {
                        const href = link.getAttribute('href');
                        if (href === `#${id}`) {
                            link.classList.add('is-active');
                        } else {
                            link.classList.remove('is-active');
                        }
                    });
                }
            });
        }, observerOptions);

        sections.forEach((section) => observer.observe(section));
    }
});
