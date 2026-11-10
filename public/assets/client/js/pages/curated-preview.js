/**
 * MAYHEN.VN — AI CURATED INVITATION PREVIEW JAVASCRIPT
 * Pure Native JS, Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('curatedPreviewApp');
    if (!appEl) return;

    const invitationId = appEl.dataset.invitationId;
    const invitationSlug = appEl.dataset.invitationSlug;
    const currentPalette = appEl.dataset.palette;

    const iframe = document.getElementById('curatedPreviewIframe');
    const phoneWrapper = document.getElementById('phoneWrapper');
    const craftingPill = document.getElementById('craftingStatusPill');
    const craftingText = document.getElementById('craftingStatusText');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Bản đồ tên tiếng Việt của 7 Section
    const sectionTitles = {
        hero: 'Trang Bìa Chính',
        story: 'Lời Ngỏ & Chuyện Tình',
        parents: 'Song Thân Phụ Mẫu',
        countdown: 'Thời Khắc Đếm Ngược',
        events: 'Sảnh Tiệc & Sự Kiện',
        timeline: 'Lịch Trình Hôn Lễ',
        album: 'Khoảnh Khắc Hạnh Phúc (Album 10 Ảnh)',
    };

    // Bản đồ tên biến thể cho từng section
    const variantNames = {
        hero: {
            variant_a: 'Cổng Vòm Cổ Điển',
            variant_b: 'Tối Giản Hiện Đại',
            variant_c: 'Trang Bìa Tạp Chí',
        },
        story: {
            variant_a: 'Trích Dẫn & Khung Vòm',
            variant_b: 'Tự Sự Lắng Đọng',
            variant_c: 'Tạp Chí Đối Xứng',
        },
        parents: {
            variant_a: 'Đối Xứng Hai Họ',
            variant_b: 'Thẻ Giấy Mỹ Thuật',
            variant_c: 'Gia Phong Chuẩn Mực',
        },
        countdown: {
            variant_a: 'Tờ Lịch Cổ Điển',
            variant_b: 'Hộp Số Tối Giản',
            variant_c: 'Vòng Tròn Thời Gian',
        },
        events: {
            variant_a: 'Vé Mời Boarding Pass',
            variant_b: 'Thư Mời Niêm Phong Sáp',
            variant_c: 'Cột Lễ & Tiệc Đôi',
        },
        timeline: {
            variant_a: 'Trục Chỉ Vàng',
            variant_b: 'Thẻ Mốc So Le',
            variant_c: 'Tối Giản Tinh Tế',
        },
        album: {
            variant_a: 'Lưới Tạp Chí (Editorial Grid)',
            variant_b: 'Thước Phim Điện Ảnh (Filmstrip)',
            variant_c: 'Photobook Mỹ Thuật (Fine-Art Collage)',
        },
    };

    const btnSkip = document.getElementById('btnSkipCrafting');
    const progressBar = document.getElementById('craftingProgressBar');
    const progressPct = document.getElementById('craftingProgressPct');
    const topbar = document.querySelector('.mayhen_curated_topbar');

    if (btnSkip && iframe) {
        btnSkip.addEventListener('click', () => {
            try {
                iframe.contentWindow?.postMessage({ type: 'MAYHEN_CRAFTING_SKIP' }, '*');
            } catch (err) {}
            endCraftingMode();
        });
    }

    // Helper: Bật trạng thái Live Crafting
    function startCraftingMode() {
        if (topbar) topbar.classList.add('is_crafting');
        if (craftingPill && craftingText) {
            craftingPill.classList.add('is-active');
            craftingPill.style.display = 'inline-flex';
            craftingText.textContent = '✦ Khởi tạo bản thiết kế độc bản...';
            if (progressBar) progressBar.style.width = '0%';
            if (progressPct) progressPct.textContent = '0%';
            if (btnSkip) btnSkip.style.display = 'inline-block';
        }
    }

    function endCraftingMode() {
        if (craftingPill && craftingText) {
            craftingText.textContent = '✦ Tuyệt tác thiệp cưới của bạn đã sẵn sàng!';
            if (btnSkip) btnSkip.style.display = 'none';
            if (progressBar) progressBar.style.width = '100%';
            if (progressPct) progressPct.textContent = '100%';
            setTimeout(() => {
                craftingPill.classList.remove('is-active');
                craftingPill.style.display = 'none';
                if (topbar) topbar.classList.remove('is_crafting');
                document.querySelectorAll('.section_ctrl_card').forEach(c => c.style.borderColor = '');
            }, 3200);
        } else {
            if (topbar) topbar.classList.remove('is_crafting');
        }
        // Gỡ token/query khỏi URL để F5 hoặc back/forward không replay
        try {
            history.replaceState({}, '', window.location.pathname);
        } catch (e) {}
    }

    // Kích hoạt animation CHỈ KHI server cấp quyền trình diễn lần đầu tiên (First-Visit-Only)
    const shouldAnimate = appEl.dataset.shouldAnimate === '1';
    if (shouldAnimate) {
        startCraftingMode();
        // Xóa ngay token khỏi URL để F5, Back/Forward không bao giờ bị phát lại
        try {
            history.replaceState({}, '', window.location.pathname);
        } catch (e) {}
    } else {
        if (craftingPill) {
            craftingPill.classList.remove('is-active');
            craftingPill.style.display = 'none';
        }
    }

    // Lắng nghe postMessage trực tiếp từ iframe khi từng chi tiết được dựng
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;

        if (e.data.type === 'MAYHEN_CRAFTING_STEP') {
            if (craftingPill && craftingText) {
                craftingPill.classList.add('is-active');
                craftingPill.style.display = 'inline-flex';
                craftingText.textContent = e.data.detailTitle || `✦ Đang hoàn thiện: ${e.data.sectionTitle || e.data.section}...`;
            }
            if (progressBar) {
                progressBar.style.width = `${e.data.percent || 0}%`;
            }
            if (progressPct) {
                progressPct.textContent = `${e.data.percent || 0}%`;
            }

            // Highlight nhẹ thẻ section ở sidebar tương ứng
            document.querySelectorAll('.section_ctrl_card').forEach(c => c.style.borderColor = '');
            const activeCard = document.querySelector(`.section_ctrl_card[data-section="${e.data.section}"]`);
            if (activeCard) {
                activeCard.style.borderColor = 'var(--curated-gold)';
            }
        }

        if (e.data.type === 'MAYHEN_SECTION_CRAFT_COMPLETE') {
            clearTimeout(window.__variantLockTimer);
            setVariantControlsLocked(false);

            if (craftingPill && craftingText) {
                craftingText.textContent = '✦ Đã cập nhật xong bố cục!';
                if (progressBar) progressBar.style.width = '100%';
                if (progressPct) progressPct.textContent = '100%';
                setTimeout(() => {
                    craftingPill.classList.remove('is-active');
                    craftingPill.style.display = 'none';
                }, 1800);
            }

            // Highlight nhẹ thẻ section ở sidebar vừa dệt xong
            document.querySelectorAll('.section_ctrl_card').forEach(c => c.style.borderColor = '');
            const card = document.querySelector(`.section_ctrl_card[data-section="${e.data.section}"]`);
            if (card) {
                card.style.borderColor = 'var(--curated-gold)';
                setTimeout(() => { card.style.borderColor = ''; }, 2000);
            }
        }

        if (e.data.type === 'MAYHEN_CRAFTING_COMPLETE') {
            clearTimeout(window.__variantLockTimer);
            setVariantControlsLocked(false);
            if (progressBar) progressBar.style.width = '100%';
            if (progressPct) progressPct.textContent = '100%';
            endCraftingMode();
        }

        if (e.data.type === 'MAYHEN_CURATED_SWITCH_SECTION') {
            const sec = e.data.section;
            const nextBtn = document.querySelector(`.btn_variant_nav.btn_next[data-section="${sec}"]`);
            if (nextBtn && !nextBtn.disabled) nextBtn.click();
        }
    });

    // Helper khóa/mở các nút điều khiển biến thể khi đang dệt (tránh bấm liên tục)
    function setVariantControlsLocked(locked) {
        document.querySelectorAll('.btn_variant_nav, #btnRemixAll, #btnReplayCrafting').forEach(btn => {
            btn.disabled = locked;
            if (locked) {
                btn.classList.add('is_locked_crafting');
                btn.style.opacity = '0.45';
                btn.style.cursor = 'not-allowed';
            } else {
                btn.classList.remove('is_locked_crafting');
                btn.style.opacity = '';
                btn.style.cursor = '';
            }
        });
    }

    // 1. Nút Xem lại quá trình dựng thiệp (Replay Crafting)
    const btnReplay = document.getElementById('btnReplayCrafting');
    if (btnReplay && iframe) {
        btnReplay.addEventListener('click', () => {
            if (btnReplay.disabled) return;
            setVariantControlsLocked(true);
            startCraftingMode();
            const baseUrl = iframe.src.split('?')[0];
            iframe.src = `${baseUrl}?crafting=1&embed=1&palette=${currentPalette}&replay=${Date.now()}`;
        });
    }

    // 2. Nút Đổi mẫu khác cùng gu (Remix Mẫu Nguồn) — BẢO TOÀN MÀU SẮC BẤT BIẾN
    const handleRemix = async () => {
        const btnRemix = document.getElementById('btnRemixAll');
        const btnRemixSidebar = document.getElementById('btnRemixSidebar');
        if (btnRemix?.disabled || btnRemixSidebar?.disabled) return;

        setVariantControlsLocked(true);
        const originalText = btnRemix ? btnRemix.innerHTML : '';
        if (btnRemix) btnRemix.innerHTML = '<span>Đang đổi...</span>';
        if (btnRemixSidebar) btnRemixSidebar.innerHTML = '<span>Đang tuyển chọn...</span>';

        try {
            const response = await fetch(`/api/curated/remix/${invitationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (data.success) {
                // Cập nhật tên mẫu nguồn trên sidebar
                const nameEl = document.getElementById('templateSourceName');
                if (nameEl && data.template_name) {
                    nameEl.textContent = data.template_name;
                }

                // Tải lại iframe để hiển thị mẫu mới với hiệu ứng dựng thiệp
                startCraftingMode();
                const baseUrl = iframe.src.split('?')[0];
                iframe.src = `${baseUrl}?crafting=1&embed=1&palette=${currentPalette}&t=${Date.now()}`;
            } else {
                alert(data.message || 'Có lỗi xảy ra khi đổi mẫu.');
                setVariantControlsLocked(false);
            }
        } catch (err) {
            console.error('Lỗi đổi mẫu tuyển chọn:', err);
            alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
            setVariantControlsLocked(false);
        } finally {
            if (btnRemix) btnRemix.innerHTML = originalText;
            if (btnRemixSidebar) btnRemixSidebar.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg><span>Đổi Sang Mẫu Khác Cùng Gu</span>';
        }
    };

    const btnRemixAll = document.getElementById('btnRemixAll');
    if (btnRemixAll) btnRemixAll.addEventListener('click', handleRemix);

    const btnRemixSidebar = document.getElementById('btnRemixSidebar');
    if (btnRemixSidebar) btnRemixSidebar.addEventListener('click', handleRemix);

    // 3. Chạm vào thẻ section ở sidebar: cuộn camera trong iframe đến section đó
    document.querySelectorAll('.section_ctrl_card').forEach((card) => {
        card.addEventListener('click', () => {
            const sec = card.dataset.section;
            if (sec && iframe) {
                document.querySelectorAll('.section_ctrl_card').forEach(c => c.style.borderColor = '');
                card.style.borderColor = 'var(--curated-gold)';
                try {
                    iframe.contentWindow?.postMessage({
                        type: 'MAYHEN_SCROLL_TO_SECTION',
                        section: sec
                    }, '*');
                } catch (e) {}
            }
        });
    });

    // 4. Viewport Switcher (Mobile / Tablet / Desktop)
    const viewportBtns = document.querySelectorAll('.viewport_btn');
    viewportBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            viewportBtns.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            const vp = btn.dataset.viewport;
            phoneWrapper.classList.remove('viewport-tablet', 'viewport-desktop');

            if (vp === 'tablet') {
                phoneWrapper.classList.add('viewport-tablet');
            } else if (vp === 'desktop') {
                phoneWrapper.classList.add('viewport-desktop');
            }
        });
    });

    // 5. Modal Xuất Bản Thiệp Cưới
    const publishModal = document.getElementById('publishModal');
    const btnOpenPublish = document.getElementById('btnOpenPublishModal');
    const btnClosePublish = document.getElementById('btnClosePublishModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const btnCopyUrl = document.getElementById('btnCopyShareUrl');
    const shareInput = document.getElementById('publicShareUrl');
    const copyBtnText = document.getElementById('copyBtnText');

    function openModal() {
        if (publishModal) publishModal.classList.add('is-open');
    }

    function closeModal() {
        if (publishModal) publishModal.classList.remove('is-open');
    }

    if (btnOpenPublish) btnOpenPublish.addEventListener('click', openModal);
    if (btnClosePublish) btnClosePublish.addEventListener('click', closeModal);
    if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);

    if (btnCopyUrl && shareInput) {
        btnCopyUrl.addEventListener('click', () => {
            shareInput.select();
            shareInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(shareInput.value).then(() => {
                if (copyBtnText) copyBtnText.textContent = 'Đã chép! ✓';
                setTimeout(() => {
                    if (copyBtnText) copyBtnText.textContent = 'Sao Chép';
                }, 2500);
            }).catch(() => {
                alert('Không thể tự động sao chép. Vui lòng copy thủ công từ ô nhập.');
            });
        });
    }

    // 6. Mobile Drawer Toggle cho Bảng Tùy Biến Bố Cục
    const btnToggleDrawer = document.getElementById('btnToggleSidebarDrawer');
    const btnCloseDrawer = document.getElementById('btnCloseSidebarDrawer');
    const sidebar = document.getElementById('variantsSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function openDrawer() {
        if (sidebar) sidebar.classList.add('is-drawer-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('is-open');
    }

    function closeDrawer() {
        if (sidebar) sidebar.classList.remove('is-drawer-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('is-open');
    }

    if (btnToggleDrawer) btnToggleDrawer.addEventListener('click', openDrawer);
    if (btnCloseDrawer) btnCloseDrawer.addEventListener('click', closeDrawer);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeDrawer);
});
