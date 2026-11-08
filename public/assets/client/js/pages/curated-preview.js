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
    const craftingBanner = document.getElementById('craftingBanner');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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
        dresscode: {
            variant_a: 'Vòng Tròn Sắc Màu',
            variant_b: 'Bảng Mẫu Vải',
            variant_c: 'Ký Họa Thời Trang',
        },
    };

    // Helper: Hiển thị Crafting Banner tạm thời
    function triggerCraftingAnimation() {
        if (!craftingBanner) return;
        craftingBanner.classList.add('is-active');
        setTimeout(() => {
            craftingBanner.classList.remove('is-active');
        }, 5000);
    }

    // Kích hoạt ngay lúc vào trang nếu có query param crafting=1
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('crafting') === '1') {
        triggerCraftingAnimation();
    }

    // 1. Nút Xem lại quá trình dựng thiệp (Replay Crafting)
    const btnReplay = document.getElementById('btnReplayCrafting');
    if (btnReplay && iframe) {
        btnReplay.addEventListener('click', () => {
            triggerCraftingAnimation();
            const baseUrl = iframe.src.split('?')[0];
            iframe.src = `${baseUrl}?crafting=1&replay=${Date.now()}`;
        });
    }

    // 2. Nút Phối lại toàn bộ bố cục (Remix All) — BẢO TOÀN MÀU SẮC
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

                    // Tải lại iframe để hiển thị bố cục mới với hiệu ứng dựng
                    triggerCraftingAnimation();
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

                    // Tải lại iframe
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

