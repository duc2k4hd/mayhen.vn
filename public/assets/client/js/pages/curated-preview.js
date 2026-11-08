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
        if (craftingPill && craftingText) {
            craftingPill.classList.add('is-active');
            craftingText.textContent = 'Đang trình bày thiệp...';
            if (btnSkip) btnSkip.style.display = 'inline-block';
        }
    }

    function endCraftingMode() {
        if (craftingPill && craftingText) {
            craftingText.textContent = '✦ Thiệp của bạn đã sẵn sàng!';
            if (btnSkip) btnSkip.style.display = 'none';
            setTimeout(() => {
                craftingPill.classList.remove('is-active');
                document.querySelectorAll('.section_ctrl_card').forEach(c => c.style.borderColor = '');
            }, 3000);
        }
        // Gỡ token/query khỏi URL để F5 hoặc back/forward không replay
        try {
            history.replaceState({}, '', window.location.pathname);
        } catch (e) {}
    }

    // Kích hoạt ngay lúc vào trang nếu có query param crafting=1 hoặc pt
    const urlParams = new URLSearchParams(window.location.search);
    const hasCraftingParam = urlParams.get('crafting') === '1' || urlParams.has('pt') || (iframe && iframe.src.indexOf('crafting=1') !== -1);
    if (hasCraftingParam) {
        startCraftingMode();
    }

    // Lắng nghe postMessage trực tiếp từ iframe khi mỗi section được dựng
    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;

        if (e.data.type === 'MAYHEN_CRAFTING_STEP') {
            if (craftingPill && craftingText) {
                craftingPill.classList.add('is-active');
                const title = sectionTitles[e.data.section] || e.data.section;
                craftingText.textContent = `✦ Đang phác thảo: Mục ${e.data.step}/${e.data.total} (${title})...`;
            }

            // Highlight nhẹ thẻ section ở sidebar tương ứng
            document.querySelectorAll('.section_ctrl_card').forEach(c => c.style.borderColor = '');
            const activeCard = document.querySelector(`.section_ctrl_card[data-section="${e.data.section}"]`);
            if (activeCard) {
                activeCard.style.borderColor = 'var(--curated-gold)';
            }
        }

        if (e.data.type === 'MAYHEN_CRAFTING_COMPLETE') {
            endCraftingMode();
        }

        if (e.data.type === 'MAYHEN_CURATED_SWITCH_SECTION') {
            const sec = e.data.section;
            const nextBtn = document.querySelector(`.btn_variant_nav.btn_next[data-section="${sec}"]`);
            if (nextBtn) nextBtn.click();
        }
    });

    // 1. Nút Xem lại quá trình dựng thiệp (Replay Crafting) — 100% Client-Side, không tốn tài nguyên server
    const btnReplay = document.getElementById('btnReplayCrafting');
    if (btnReplay && iframe) {
        btnReplay.addEventListener('click', () => {
            startCraftingMode();
            try {
                if (iframe.contentWindow) {
                    iframe.contentWindow.postMessage({ type: 'MAYHEN_REPLAY_CRAFTING' }, '*');
                    return;
                }
            } catch (e) {}
            const baseUrl = iframe.src.split('?')[0];
            iframe.src = `${baseUrl}?crafting=1&replay=${Date.now()}`;
        });
    }

    // 2. Nút Phối lại toàn bộ bố cục (Remix All) — BẢO TOÀN MÀU SẮC BẤT BIẾN
    const btnRemix = document.getElementById('btnRemixAll');
    if (btnRemix && iframe) {
        btnRemix.addEventListener('click', async () => {
            btnRemix.disabled = true;
            const originalText = btnRemix.innerHTML;
            btnRemix.innerHTML = '<span>Đang phối... ⏳</span>';

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
                if (data.success && data.layout) {
                    // Cập nhật nhãn biến thể trong sidebar
                    const sections = data.layout.sections || {};
                    Object.keys(sections).forEach((secKey) => {
                        const variantKey = sections[secKey];
                        const labelEl = document.getElementById(`label-${secKey}`);
                        if (labelEl && variantNames[secKey] && variantNames[secKey][variantKey]) {
                            labelEl.textContent = variantNames[secKey][variantKey];
                        }
                    });

                    // Tải lại iframe để hiển thị bố cục mới với hiệu ứng dựng thiệp
                    startCraftingMode();
                    const baseUrl = iframe.src.split('?')[0];
                    iframe.src = `${baseUrl}?crafting=1&t=${Date.now()}`;
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi phối lại bố cục.');
                }
            } catch (err) {
                console.error('Lỗi phối lại bố cục:', err);
                alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
            } finally {
                btnRemix.disabled = false;
                btnRemix.innerHTML = originalText;
            }
        });
    }

    // 3. Đổi riêng biến thể từng section
    document.querySelectorAll('.btn_variant_nav[data-action="switch"]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const section = btn.dataset.section;
            const direction = btn.dataset.direction || 'next';
            btn.disabled = true;

            try {
                const response = await fetch('/api/curated/switch-variant', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        invitation_id: invitationId,
                        section: section,
                        direction: direction,
                    }),
                });

                const data = await response.json();
                if (data.success && data.layout) {
                    const newVariant = data.layout.sections[section];
                    const labelEl = document.getElementById(`label-${section}`);
                    if (labelEl && variantNames[section] && variantNames[section][newVariant]) {
                        labelEl.textContent = variantNames[section][newVariant];
                    }

                    // Tải lại iframe ở đúng vị trí (không chạy full crafting dài)
                    if (iframe) {
                        const baseUrl = iframe.src.split('?')[0];
                        iframe.src = `${baseUrl}?crafting=0&t=${Date.now()}`;
                    }
                } else {
                    alert(data.message || 'Không thể đổi biến thể mục này.');
                }
            } catch (err) {
                console.error('Lỗi đổi biến thể:', err);
                alert('Có lỗi xảy ra khi đổi biến thể.');
            } finally {
                btn.disabled = false;
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
});
