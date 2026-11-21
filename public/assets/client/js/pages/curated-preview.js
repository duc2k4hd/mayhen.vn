/**
 * MAYHEN.VN — AI CURATED INVITATION PREVIEW WORKBENCH JS (V2)
 * Zero alert() • 3-Tab Studio • Instant Template/Palette/Style/Mode Switch • AI Copy Rewriter • Personalized Guest Link
 */

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('curatedPreviewApp');
    if (!appEl) return;

    const invitationId = appEl.dataset.invitationId;
    const publicBaseUrl = appEl.dataset.publicUrl || '';
    let currentPalette = appEl.dataset.palette || 'ruby';
    let currentStyle = appEl.dataset.style || 'romantic';
    let currentTemplateSlug = appEl.dataset.templateSlug || '';
    let isCuratedSeven = appEl.dataset.isCuratedSeven === '1';

    const iframe = document.getElementById('curatedPreviewIframe');
    const phoneWrapper = document.getElementById('phoneWrapper');
    const craftingPill = document.getElementById('craftingStatusPill');
    const craftingText = document.getElementById('craftingStatusText');
    const progressBar = document.getElementById('craftingProgressBar');
    const progressPct = document.getElementById('craftingProgressPct');
    const btnSkip = document.getElementById('btnSkipCrafting');
    const topbar = document.querySelector('.mayhen_curated_topbar');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    /* =====================================================================
     * 1. TOAST HELPER (Zero window.alert)
     * ===================================================================== */
    let toastTimer = null;
    function showToast(msg) {
        const toast = document.getElementById('curatedToast');
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2800);
    }

    function reloadIframeClean(withCrafting = false) {
        if (!iframe) return;
        const baseUrl = iframe.src.split('?')[0];
        const params = new URLSearchParams({ embed: '1', palette: currentPalette, t: String(Date.now()) });
        if (withCrafting) params.set('crafting', '1');
        iframe.src = `${baseUrl}?${params.toString()}`;
    }

    /* =====================================================================
     * 2. CRAFTING PROGRESS & SKIP
     * ===================================================================== */
    function startCraftingMode() {
        if (topbar) topbar.classList.add('is_crafting');
        if (craftingPill && craftingText) {
            craftingPill.classList.add('is-active');
            craftingPill.style.display = 'inline-flex';
            craftingText.textContent = '✦ Đang dựng tác phẩm...';
            if (progressBar) progressBar.style.width = '5%';
            if (progressPct) progressPct.textContent = '5%';
            if (btnSkip) btnSkip.style.display = 'inline-block';
        }
    }

    function endCraftingMode() {
        if (craftingPill && craftingText) {
            craftingText.textContent = '✦ Thiệp cưới của bạn đã sẵn sàng!';
            if (btnSkip) btnSkip.style.display = 'none';
            if (progressBar) progressBar.style.width = '100%';
            if (progressPct) progressPct.textContent = '100%';
            setTimeout(() => {
                craftingPill.classList.remove('is-active');
                craftingPill.style.display = 'none';
                if (topbar) topbar.classList.remove('is_crafting');
            }, 1800);
        } else if (topbar) {
            topbar.classList.remove('is_crafting');
        }
        try {
            history.replaceState({}, '', window.location.pathname);
        } catch (_e) {}
    }

    if (btnSkip && iframe) {
        btnSkip.addEventListener('click', () => {
            try {
                iframe.contentWindow?.postMessage({ type: 'MAYHEN_CRAFTING_SKIP' }, '*');
            } catch (_e) {}
            endCraftingMode();
        });
    }

    if (appEl.dataset.shouldAnimate === '1') {
        startCraftingMode();
        try {
            history.replaceState({}, '', window.location.pathname);
        } catch (_e) {}
    } else if (craftingPill) {
        craftingPill.classList.remove('is-active');
        craftingPill.style.display = 'none';
    }

    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        if (e.data.type === 'MAYHEN_CRAFTING_STEP') {
            if (craftingPill && craftingText) {
                craftingPill.classList.add('is-active');
                craftingPill.style.display = 'inline-flex';
                craftingText.textContent = e.data.detailTitle || `✦ Đang hoàn thiện: ${e.data.sectionTitle || e.data.section}`;
            }
            if (progressBar) progressBar.style.width = `${e.data.percent || 0}%`;
            if (progressPct) progressPct.textContent = `${e.data.percent || 0}%`;
        }
        if (e.data.type === 'MAYHEN_CRAFTING_COMPLETE') {
            endCraftingMode();
        }
    });

    /* =====================================================================
     * 3. 3-TAB SIDEBAR & MOBILE BOTTOM SHEET
     * ===================================================================== */
    const tabs = document.querySelectorAll('.sidebar_tab');
    const panels = document.querySelectorAll('.sidebar_tab_panel');
    const sidebar = document.getElementById('variantsSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const btnCloseDrawer = document.getElementById('btnCloseSidebarDrawer');

    function activateTab(tabName) {
        tabs.forEach((t) => {
            const active = t.dataset.tab === tabName;
            t.classList.toggle('is-active', active);
            t.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panels.forEach((p) => {
            p.classList.toggle('is-active', p.dataset.tabPanel === tabName);
        });
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.tab));
    });

    function openDrawer(tabName) {
        if (tabName) activateTab(tabName);
        if (sidebar) sidebar.classList.add('is-drawer-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('is-open');
    }

    function closeDrawer() {
        if (sidebar) sidebar.classList.remove('is-drawer-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('is-open');
    }

    if (btnCloseDrawer) btnCloseDrawer.addEventListener('click', closeDrawer);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeDrawer);

    document.querySelectorAll('[data-open-sheet-tab]').forEach((btn) => {
        btn.addEventListener('click', () => openDrawer(btn.getAttribute('data-open-sheet-tab')));
    });

    /* =====================================================================
     * 4. UNIFIED CUSTOMIZE API HELPER
     * ===================================================================== */
    let isBusy = false;
    async function callCustomize(payload, successToast) {
        if (isBusy) return null;
        isBusy = true;
        try {
            const res = await fetch(`/api/curated/customize/${invitationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                const firstErr = data?.errors ? Object.values(data.errors).flat()[0] : (data?.message || 'Không thể cập nhật thiệp.');
                showToast(firstErr);
                return null;
            }

            // Sync local state & header badges
            if (data.palette) currentPalette = data.palette;
            if (data.style) currentStyle = data.style;
            if (data.template_slug) currentTemplateSlug = data.template_slug;
            if (typeof data.is_curated_seven === 'boolean') isCuratedSeven = data.is_curated_seven;

            const tplNameEl = document.getElementById('templateSourceName');
            if (tplNameEl && data.template_name) tplNameEl.textContent = data.template_name;

            const topCouple = document.getElementById('topbarCoupleNames');
            const mobCouple = document.getElementById('mobileCoupleTitle');
            const modalCouple = document.getElementById('modalCoupleName');
            if (data.groom_name && data.bride_name) {
                const pair = `${data.groom_name} & ${data.bride_name}`;
                if (topCouple) topCouple.textContent = pair;
                if (mobCouple) mobCouple.textContent = pair;
                if (modalCouple) modalCouple.textContent = pair;
            }

            const topStyle = document.getElementById('topbarStyleName');
            if (topStyle && data.style_name) topStyle.textContent = data.style_name;

            const topPalName = document.getElementById('topbarPaletteName');
            const topPalDot = document.getElementById('topbarPaletteDot');
            if (topPalName && data.palette_short_name) topPalName.textContent = data.palette_short_name;
            if (topPalDot && data.palette_primary) topPalDot.style.backgroundColor = data.palette_primary;

            const modePill = document.getElementById('activeModePill');
            if (modePill) modePill.textContent = isCuratedSeven ? '7 Mục Tinh Gọn' : '10 Mục Đầy Đủ';

            // Update active buttons
            document.querySelectorAll('[data-pick-palette]').forEach((b) => {
                b.classList.toggle('is-active', b.dataset.pickPalette === currentPalette);
            });
            document.querySelectorAll('[data-pick-style]').forEach((b) => {
                b.classList.toggle('is-active', b.dataset.pickStyle === currentStyle);
            });
            document.querySelectorAll('[data-pick-template]').forEach((b) => {
                b.classList.toggle('is-active', b.dataset.pickTemplate === currentTemplateSlug);
            });
            document.querySelectorAll('[data-curated-seven]').forEach((b) => {
                b.classList.toggle('is-active', (b.dataset.curatedSeven === '1') === isCuratedSeven);
            });

            reloadIframeClean(false);
            showToast(successToast || data.message || 'Đã cập nhật thiệp!');
            return data;
        } catch (_err) {
            showToast('Lỗi kết nối máy chủ, vui lòng thử lại.');
            return null;
        } finally {
            isBusy = false;
        }
    }

    // Chọn trực tiếp Bảng Màu
    document.querySelectorAll('[data-pick-palette]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const palette = btn.dataset.pickPalette;
            if (!palette || palette === currentPalette) return;
            callCustomize({ palette }, '✦ Đã đổi bảng màu chủ đạo!');
        });
    });

    // Chọn trực tiếp Phong Cách
    document.querySelectorAll('[data-pick-style]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const style = btn.dataset.pickStyle;
            if (!style || style === currentStyle) return;
            callCustomize({ style }, '✦ Đã chuyển phong cách thẩm mỹ!');
        });
    });

    // Chọn trực tiếp Mẫu Thiệp từ thư viện Thumbnail
    document.querySelectorAll('[data-pick-template]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const slug = btn.dataset.pickTemplate;
            if (!slug || slug === currentTemplateSlug) return;
            callCustomize({ template_slug: slug }, `✦ Đã áp dụng mẫu "${btn.getAttribute('title') || slug}"!`);
            if (window.innerWidth <= 900) closeDrawer();
        });
    });

    // Bật / Tắt chế độ 7 Mục Tinh Gọn ↔ 10 Mục Đầy Đủ
    document.querySelectorAll('[data-curated-seven]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const nextSeven = btn.dataset.curatedSeven === '1';
            if (nextSeven === isCuratedSeven) return;
            callCustomize(
                { is_curated_seven: nextSeven },
                nextSeven ? '✦ Đã chuyển sang chế độ 7 mục tinh gọn' : '✦ Đã mở khóa đầy đủ 10 mục (RSVP, VietQR, Dresscode)!'
            );
        });
    });

    /* =====================================================================
     * 5. REMIX (ĐỔI MẪU KHÁC CÙNG GU) & REPLAY
     * ===================================================================== */
    async function handleRemix() {
        if (isBusy) return;
        isBusy = true;
        showToast('✦ Đang chọn mẫu thiệp mới cùng gu...');
        try {
            const res = await fetch(`/api/curated/remix/${invitationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            const data = await res.json();
            if (data.success) {
                currentTemplateSlug = data.template_slug || currentTemplateSlug;
                const nameEl = document.getElementById('templateSourceName');
                if (nameEl && data.template_name) nameEl.textContent = data.template_name;
                document.querySelectorAll('[data-pick-template]').forEach((b) => {
                    b.classList.toggle('is-active', b.dataset.pickTemplate === currentTemplateSlug);
                });
                reloadIframeClean(false);
                showToast(`✦ Đã đổi sang mẫu: ${data.template_name || 'Mới'}`);
            } else {
                showToast(data.message || 'Không thể đổi mẫu lúc này.');
            }
        } catch (_err) {
            showToast('Lỗi kết nối máy chủ, vui lòng thử lại.');
        } finally {
            isBusy = false;
        }
    }

    document.getElementById('btnRemixAll')?.addEventListener('click', handleRemix);
    document.getElementById('btnRemixSidebar')?.addEventListener('click', handleRemix);
    document.getElementById('btnMobileQuickRemix')?.addEventListener('click', handleRemix);

    document.getElementById('btnReplayCrafting')?.addEventListener('click', () => {
        startCraftingMode();
        reloadIframeClean(true);
    });

    /* =====================================================================
     * 6. QUICK CONTENT EDIT FORM & AI COPY REWRITER
     * ===================================================================== */
    const quickForm = document.getElementById('curatedQuickEditForm');
    const saveBtn = document.getElementById('btnSaveQuickEdit');

    if (quickForm) {
        quickForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(quickForm);
            const payload = {};
            fd.forEach((val, key) => {
                payload[key] = typeof val === 'string' ? val.trim() : val;
            });

            if (!payload.groom_name || !payload.bride_name) {
                showToast('Vui lòng nhập đầy đủ tên Chú rể và Cô dâu.');
                return;
            }

            if (saveBtn) saveBtn.disabled = true;
            const res = await callCustomize(payload, '✦ Đã lưu và cập nhật nội dung thiệp!');
            if (saveBtn) saveBtn.disabled = false;
            if (res && window.innerWidth <= 900) closeDrawer();
        });
    }

    const aiRewriteBtn = document.getElementById('btnAiRegenerateCopy');
    if (aiRewriteBtn) {
        aiRewriteBtn.addEventListener('click', async () => {
            if (isBusy) return;
            isBusy = true;
            aiRewriteBtn.disabled = true;
            const oldText = aiRewriteBtn.textContent;
            aiRewriteBtn.textContent = 'Đang viết...';

            try {
                const res = await fetch(`/api/curated/regenerate-copy/${invitationId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ style: currentStyle }),
                });
                const data = await res.json();
                if (res.ok && data.success && data.copy) {
                    const titleIn = document.getElementById('qeStoryTitle');
                    const quoteIn = document.getElementById('qeCoupleQuote');
                    const contentIn = document.getElementById('qeStoryContent');
                    if (titleIn) titleIn.value = data.copy.story_title || '';
                    if (quoteIn) quoteIn.value = data.copy.couple_quote || '';
                    if (contentIn) contentIn.value = data.copy.story_content || '';
                    reloadIframeClean(false);
                    showToast('✦ AI đã biên soạn lời ngỏ mới!');
                } else {
                    showToast(data.message || 'Không thể viết lại lời ngỏ lúc này.');
                }
            } catch (_err) {
                showToast('Lỗi kết nối máy chủ.');
            } finally {
                isBusy = false;
                aiRewriteBtn.disabled = false;
                aiRewriteBtn.textContent = oldText;
            }
        });
    }

    /* =====================================================================
     * 7. SECTION NAVIGATOR & VIEWPORT SWITCHER
     * ===================================================================== */
    document.querySelectorAll('.section_ctrl_card').forEach((card) => {
        card.addEventListener('click', () => {
            const sec = card.dataset.section;
            if (!sec || !iframe) return;
            document.querySelectorAll('.section_ctrl_card').forEach((c) => c.classList.remove('is-active'));
            card.classList.add('is-active');
            try {
                iframe.contentWindow?.postMessage({ type: 'MAYHEN_SCROLL_TO_SECTION', section: sec }, '*');
            } catch (_e) {}
            if (window.innerWidth <= 900) closeDrawer();
        });
    });

    const viewportBtns = document.querySelectorAll('.viewport_btn');
    viewportBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            viewportBtns.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            const vp = btn.dataset.viewport;
            phoneWrapper.classList.remove('viewport-tablet', 'viewport-desktop');
            if (vp === 'tablet') phoneWrapper.classList.add('viewport-tablet');
            if (vp === 'desktop') phoneWrapper.classList.add('viewport-desktop');
        });
    });

    /* =====================================================================
     * 8. PUBLISH MODAL & PERSONALIZED GUEST LINK
     * ===================================================================== */
    const publishModal = document.getElementById('publishModal');
    const btnOpenPublish = document.getElementById('btnOpenPublishModal');
    const btnClosePublish = document.getElementById('btnClosePublishModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const guestInput = document.getElementById('guestPersonalizeInput');
    const shareInput = document.getElementById('publicShareUrl');
    const openGuestLink = document.getElementById('btnOpenGuestInvitation');
    const btnCopyUrl = document.getElementById('btnCopyShareUrl');
    const copyBtnText = document.getElementById('copyBtnText');
    const btnCopyMsg = document.getElementById('btnCopyInviteMessage');

    function buildShareUrl() {
        const guest = (guestInput?.value || '').trim();
        if (!guest) return publicBaseUrl;
        return `${publicBaseUrl}?guest=${encodeURIComponent(guest)}`;
    }

    function syncShareUrl() {
        const url = buildShareUrl();
        if (shareInput) shareInput.value = url;
        if (openGuestLink) openGuestLink.href = url;
    }

    if (guestInput) guestInput.addEventListener('input', syncShareUrl);

    function openModal() {
        syncShareUrl();
        if (publishModal) {
            publishModal.classList.add('is-open');
            publishModal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeModal() {
        if (publishModal) {
            publishModal.classList.remove('is-open');
            publishModal.setAttribute('aria-hidden', 'true');
        }
    }

    if (btnOpenPublish) btnOpenPublish.addEventListener('click', openModal);
    if (btnClosePublish) btnClosePublish.addEventListener('click', closeModal);
    if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && publishModal?.classList.contains('is-open')) closeModal();
    });

    if (btnCopyUrl && shareInput) {
        btnCopyUrl.addEventListener('click', () => {
            const url = shareInput.value;
            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    if (copyBtnText) copyBtnText.textContent = 'Đã chép ✓';
                    showToast('Đã sao chép đường dẫn thiệp cưới!');
                    setTimeout(() => { if (copyBtnText) copyBtnText.textContent = 'Sao Chép'; }, 2200);
                }).catch(() => showToast('Vui lòng chọn và sao chép đường dẫn ở ô trên.'));
            }
        });
    }

    if (btnCopyMsg) {
        btnCopyMsg.addEventListener('click', () => {
            const guest = (guestInput?.value || '').trim() || 'Quý khách';
            const couple = document.getElementById('modalCoupleName')?.textContent?.trim() || '';
            const url = buildShareUrl();
            const message = `Trân trọng kính mời ${guest} đến chung vui trong ngày trọng đại của ${couple}. Xem thiệp cưới trực tuyến tại: ${url}`;
            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(message).then(
                    () => showToast('✦ Đã sao chép lời mời kèm link! Dán ngay vào Zalo/Messenger.'),
                    () => showToast('Không thể tự động sao chép lúc này.')
                );
            }
        });
    }
});
