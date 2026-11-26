/**
 * MAYHEN.VN — CLIENT TEMPLATES SCRIPT
 * Prefix: mayhen_templates_*
 * Aesthetic: Quiet Luxury, Smooth Transitions
 * Functionality: Auto-scroll tier bar, GPU Hover-Pan Auto Scroll, Live Preview Modal Drawer
 */

import { showAtelierLoader, hideAtelierLoader } from '../components/atelier-loader.js';

document.addEventListener('DOMContentLoaded', () => {
    initTierBarAutoScroll();
    initTemplateCardHoverScroll();
    initLivePreviewModal();
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

/**
 * Tính toán độ cuộn của ảnh mẫu thiệp tự động (hover-pan bằng GPU Transform)
 * Tự động đo độ dài thực tế của ảnh dài full page so với khung thẻ và áp dụng tốc độ trôi êm ái
 */
function initTemplateCardHoverScroll() {
    const cards = document.querySelectorAll('.mayhen_home_template_card');
    if (!cards.length) return;

    function calculatePanMetrics() {
        cards.forEach(card => {
            const wrap = card.querySelector('[data-mayhen-pan-wrap]') || card.querySelector('.mayhen_home_showcase_pan_wrap');
            const img = card.querySelector('.mayhen_home_showcase_pan_img') || card.querySelector('.mayhen_home_template_card_img');
            if (!wrap || !img) return;

            const wrapWidth = wrap.clientWidth || 280;
            const wrapHeight = wrap.clientHeight || 450;

            let imgHeight = img.clientHeight;
            if ((!imgHeight || imgHeight <= wrapHeight + 10) && img.naturalWidth > 0 && img.naturalHeight > 0) {
                imgHeight = wrapWidth * (img.naturalHeight / img.naturalWidth);
            }

            const slug = card.getAttribute('data-slug') || '';

            // Đo khoảng cách trôi thực tế
            const scrollDistance = Math.max(0, imgHeight - wrapHeight);
            if (scrollDistance > 25) {
                const panY = `-${Math.round(scrollDistance)}px`;
                wrap.style.setProperty('--pan-y', panY);
                img.style.setProperty('--pan-y', panY);

                // Tốc độ trôi tự nhiên mượt mà (khoảng 180px/giây)
                let dur = Math.max(4, Math.min(65, scrollDistance / 180));
                if (slug === 'luu-but-ngay-chung-doi') {
                    dur = Math.max(50, scrollDistance / 175);
                }
                const durStr = `${dur.toFixed(1)}s`;
                wrap.style.setProperty('--pan-dur', durStr);
                img.style.setProperty('--pan-dur', durStr);
            } else {
                wrap.style.setProperty('--pan-y', '0px');
                img.style.setProperty('--pan-y', '0px');
            }
        });
    }

    // Chạy tính toán ngay và ở các nhịp trễ đảm bảo tính toán chuẩn
    calculatePanMetrics();
    setTimeout(calculatePanMetrics, 200);
    setTimeout(calculatePanMetrics, 600);
    setTimeout(calculatePanMetrics, 1400);

    // Lắng nghe khi từng ảnh tải xong
    cards.forEach(card => {
        const img = card.querySelector('.mayhen_home_showcase_pan_img');
        if (img) {
            if (img.complete) {
                calculatePanMetrics();
            } else {
                img.addEventListener('load', calculatePanMetrics, { once: true });
            }
        }
    });

    // Lắng nghe resize cửa sổ với debounce nhẹ
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(calculatePanMetrics, 120);
    });
}

/**
 * Khởi tạo Modal Xem Mẫu Trực Tiếp (Luxury Live Preview Modal Iframe)
 */
function initLivePreviewModal() {
    const modal = document.getElementById('mayhen_home_showcase_modal');
    if (!modal) return;

    const PREVIEW_MODAL_LOADER_ID = 'mayhen_home_showcase_modal_loader';
    let previewSafetyTimer = null;
    const prefetchedUrls = new Set();

    function showPreviewLoader(templateName) {
        if (previewSafetyTimer) clearTimeout(previewSafetyTimer);
        showAtelierLoader(PREVIEW_MODAL_LOADER_ID, {
            title: templateName || 'Mẫu Thiệp Cưới',
            status: 'Đang mở phong thư cưới...',
            minDurationMs: 200,
        });
        // Safety cap tối đa 1.6s: không bao giờ bắt khách chờ toàn bộ ảnh cuối trang/audio tải xong
        previewSafetyTimer = setTimeout(() => {
            if (modal.classList.contains('is-open')) {
                hidePreviewLoader();
            }
        }, 1600);
    }

    function hidePreviewLoader(immediate = false) {
        if (previewSafetyTimer) {
            clearTimeout(previewSafetyTimer);
            previewSafetyTimer = null;
        }
        hideAtelierLoader(PREVIEW_MODAL_LOADER_ID, { immediate });
    }

    // Nhận tín hiệu DOMContentLoaded từ iframe để tắt Preloader tức thì (~300-600ms)
    window.addEventListener('message', (ev) => {
        if (ev.origin !== window.location.origin) return;
        if (ev.data && ev.data.type === 'MAYHEN_PREVIEW_DOM_READY' && modal.classList.contains('is-open')) {
            hidePreviewLoader();
        }
    });

    // Hover prefetch nhẹ nhàng để khách bấm là mở ngay
    document.addEventListener('pointerenter', (ev) => {
        const card = ev.target && ev.target.closest ? ev.target.closest('.mayhen_home_template_card[data-preview-url]') : null;
        if (!card) return;
        const url = card.getAttribute('data-preview-url');
        if (!url || prefetchedUrls.has(url)) return;
        prefetchedUrls.add(url);
        const sep = url.includes('?') ? '&' : '?';
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = `${url}${sep}hide_nav=1&in_modal=1&embed=1`;
        document.head.appendChild(link);
    }, true);

    // Hàm mở modal
    function openPreview(card) {
        if (!card) return;

        const previewUrl = card.getAttribute('data-preview-url') || '';
        const name = card.getAttribute('data-name') || 'Mẫu Thiệp Cưới';
        const cat = card.getAttribute('data-category') || 'MẪU THIỆP CƯỚI';
        const createUrl = card.getAttribute('data-create-url') || '#';

        const titleEl = document.getElementById('mayhen_home_showcase_modal_title');
        const headerTitleEl = document.getElementById('mayhen_home_showcase_modal_header_title');
        const catEl = document.getElementById('mayhen_home_showcase_modal_bottom_cat');
        const iframe = document.getElementById('mayhen_home_showcase_modal_iframe');
        const newtabLink = document.getElementById('mayhen_home_showcase_modal_newtab_link');
        const createLink = document.getElementById('mayhen_home_showcase_modal_create_link');

        if (titleEl) titleEl.textContent = name;
        if (headerTitleEl) headerTitleEl.textContent = name;
        if (catEl) catEl.textContent = cat;

        showPreviewLoader(name);

        if (iframe && previewUrl) {
            // Tự động thêm tham số hide_nav=1&in_modal=1&embed=1 để trang thiệp trong iframe không nạp thanh điều hướng bottom-nav và widget phụ
            const sep = previewUrl.includes('?') ? '&' : '?';
            iframe.src = `${previewUrl}${sep}hide_nav=1&in_modal=1&embed=1`;
        }

        if (newtabLink) newtabLink.href = previewUrl;
        if (createLink) createLink.href = createUrl;

        modal.setAttribute('data-current-preview-url', previewUrl);
        modal.inert = false;
        modal.removeAttribute('inert');
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        document.documentElement.classList.add('mayhen_preview_modal_active');
        document.body.classList.add('mayhen_preview_modal_active');
        if (window.innerWidth <= 768) {
            if (!modal.dataset.lockedScrollY) {
                const currentY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
                modal.dataset.lockedScrollY = String(currentY);
                document.body.style.top = `-${currentY}px`;
            }
        } else {
            document.body.style.overflow = 'hidden';
        }

        // Ẩn thanh bottom nav dock chân trang để không bị che vướng khung xem mẫu
        const bottomNav = document.querySelector('.mayhen_client_bottom_nav_wrapper');
        if (bottomNav) {
            bottomNav.style.display = 'none';
        }
    }

    // Hàm đóng modal
    function closePreview() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.inert = true;
        document.documentElement.classList.remove('mayhen_preview_modal_active');
        document.body.classList.remove('mayhen_preview_modal_active');
        document.body.style.overflow = '';
        if (modal.dataset.lockedScrollY !== undefined && modal.dataset.lockedScrollY !== '') {
            const restoreY = parseInt(modal.dataset.lockedScrollY, 10) || 0;
            delete modal.dataset.lockedScrollY;
            document.body.style.removeProperty('top');
            window.scrollTo({ top: restoreY, left: 0, behavior: 'instant' });
        }

        const iframe = document.getElementById('mayhen_home_showcase_modal_iframe');
        if (iframe) iframe.src = 'about:blank';
        hidePreviewLoader(true);

        // Khôi phục hiển thị thanh bottom nav dock
        const bottomNav = document.querySelector('.mayhen_client_bottom_nav_wrapper');
        if (bottomNav) {
            bottomNav.style.display = '';
        }
    }

    // 1. Bắt sự kiện click mở modal từ các nút Xem Mẫu hoặc chạm vào ảnh của card
    document.addEventListener('click', (e) => {
        const peekBtn = e.target.closest('[data-mayhen-showcase-peek-btn]');
        if (peekBtn) {
            e.preventDefault();
            const card = peekBtn.closest('.mayhen_home_template_card');
            if (card) {
                openPreview(card);
            }
        }
    });

    // 2. Bắt sự kiện click đóng modal (backdrop hoặc nút close)
    const closeTriggers = document.querySelectorAll('[data-mayhen-modal-close]');
    closeTriggers.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closePreview();
        });
    });

    // 3. Phím Escape đóng modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closePreview();
        }
    });

    // 4. Chuyển đổi chế độ xem Mobile / Desktop
    const deviceBtns = modal.querySelectorAll('[data-mayhen-device-mode]');
    deviceBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const mode = btn.getAttribute('data-mayhen-device-mode');
            deviceBtns.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            if (mode === 'desktop') {
                modal.classList.add('is-desktop-mode');
            } else {
                modal.classList.remove('is-desktop-mode');
            }
        });
    });

    // 5. Nút Sao chép liên kết (Copy Link kèm Toast)
    const copyBtn = document.getElementById('mayhen_home_showcase_modal_copy_btn');
    const toast = document.getElementById('mayhen_home_showcase_modal_toast');
    let toastTimer = null;
    if (copyBtn) {
        copyBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const relUrl = modal.getAttribute('data-current-preview-url') || '';
            const fullUrl = relUrl.startsWith('http') ? relUrl : (window.location.origin + relUrl);
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(fullUrl);
                } else {
                    const temp = document.createElement('input');
                    temp.value = fullUrl;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                }
                if (toast) {
                    toast.classList.add('show');
                    if (toastTimer) clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => {
                        toast.classList.remove('show');
                    }, 2400);
                }
            } catch (err) {
                // copy fallback
            }
        });
    }

    // 6. Ẩn hiệu ứng preloader mượt mà ngay khi iframe tải xong & Nút Tải lại iframe
    const modalIframe = document.getElementById('mayhen_home_showcase_modal_iframe');
    if (modalIframe) {
        modalIframe.addEventListener('load', () => {
            if (modal.classList.contains('is-open') && modalIframe.src && modalIframe.src !== 'about:blank') {
                hidePreviewLoader();
            }
        });
    }

    const reloadBtn = document.getElementById('mayhen_home_showcase_modal_reload_btn');
    if (reloadBtn) {
        reloadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const iframe = document.getElementById('mayhen_home_showcase_modal_iframe');
            if (iframe && iframe.src && iframe.src !== 'about:blank') {
                const currentSrc = iframe.src;
                const headerTitleEl = document.getElementById('mayhen_home_showcase_modal_header_title');
                showPreviewLoader(headerTitleEl ? headerTitleEl.textContent : 'Mẫu Thiệp Cưới');
                iframe.src = 'about:blank';
                setTimeout(() => {
                    iframe.src = currentSrc;
                }, 60);
            }
        });
    }
}

