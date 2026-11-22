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
     * 2. CRAFTING PROGRESS & ANTI-DEVTOOLS BYPASS LOCK (NO BLOCKING F12)
     * - Khi AI đang tạo thiệp: Khóa 100% nút bấm, link, form, tab, phím tắt.
     * - Không chặn mở F12/DevTools, nhưng nếu người dùng dùng F12 xóa disabled/class
     *   hoặc bắn postMessage giả trong Console:
     *   1) Closure private `isCraftingLocked` chặn mọi click/submit/keydown ở capture phase.
     *   2) `MutationObserver` tự động khôi phục class `is_crafting_locked` & `disabled` ngay lập tức.
     *   3) Xác thực chữ ký `e.source === iframe.contentWindow` + token phiên + số bước thực tế.
     *   4) Đồng bộ mở khóa với Server (`/api/curated/complete-crafting/{id}`) có kiểm tra thời gian tối thiểu.
     * ===================================================================== */
    let isCraftingLocked = appEl.dataset.shouldAnimate === '1';
    let craftingNonce = '';
    let lastVerifiedStep = 0;
    let totalExpectedSteps = 0;
    let craftingStartedAtMs = 0;
    const MIN_CLIENT_CRAFTING_MS = 12000;

    function generateNonce() {
        const arr = new Uint32Array(4);
        if (window.crypto && window.crypto.getRandomValues) {
            window.crypto.getRandomValues(arr);
        } else {
            for (let i = 0; i < 4; i++) arr[i] = Math.floor(Math.random() * 0xffffffff);
        }
        return Array.from(arr, (n) => n.toString(16).padStart(8, '0')).join('');
    }

    const INTERACTIVE_LOCK_SELECTOR = [
        '.mayhen_curated_btn_back',
        '.mayhen_curated_topbar_right button',
        '.mayhen_curated_topbar_right a',
        '.mayhen_curated_viewport_switcher button',
        '.mayhen_curated_variants_sidebar button',
        '.mayhen_curated_variants_sidebar input',
        '.mayhen_curated_variants_sidebar select',
        '.mayhen_curated_variants_sidebar textarea',
        '.mayhen_curated_variants_sidebar a',
        '.mayhen_curated_mobile_dock button',
        '.mayhen_curated_mobile_dock a',
    ].join(', ');

    function applyInteractiveDomLock(locked) {
        appEl.classList.toggle('is_crafting_locked', locked);
        if (topbar) topbar.classList.toggle('is_crafting', locked);

        document.querySelectorAll(INTERACTIVE_LOCK_SELECTOR).forEach((el) => {
            if (locked) {
                el.setAttribute('aria-disabled', 'true');
                el.setAttribute('data-crafting-locked', '1');
                if ('disabled' in el) {
                    el.disabled = true;
                }
                if (el.tagName === 'A' && el.hasAttribute('href')) {
                    el.dataset.lockedHref = el.getAttribute('href') || '';
                    el.removeAttribute('href');
                }
            } else {
                el.removeAttribute('aria-disabled');
                el.removeAttribute('data-crafting-locked');
                if ('disabled' in el) {
                    el.disabled = false;
                }
                if (el.tagName === 'A' && el.dataset.lockedHref) {
                    el.setAttribute('href', el.dataset.lockedHref);
                    delete el.dataset.lockedHref;
                }
            }
        });
    }

    // Chặn tuyệt đối ở Capture Phase: kể cả khi người dùng mở F12 xóa attribute disabled/class trong Elements
    const blockEventWhileCrafting = (e) => {
        if (!isCraftingLocked) return;
        const target = e.target;
        if (!target || !(target instanceof Element)) return;

        // Chỉ cho phép iframe tự chạy bên trong khung
        if (target === iframe) return;

        const isControl = target.closest(
            '.mayhen_curated_topbar, .mayhen_curated_variants_sidebar, .mayhen_curated_mobile_dock, .mayhen_curated_modal, button, a, input, select, textarea, summary, [role="tab"]'
        );
        if (isControl) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showToast('✦ AI đang tạo từng phần tử thiệp cưới, vui lòng đợi hoàn tất 100%...');
        }
    };

    ['click', 'dblclick', 'submit', 'change', 'input'].forEach((evtName) => {
        document.addEventListener(evtName, blockEventWhileCrafting, true);
    });

    document.addEventListener(
        'keydown',
        (e) => {
            if (!isCraftingLocked) return;
            // Không bao giờ chặn F12 hoặc tổ hợp mở DevTools (Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C)
            if (
                e.key === 'F12' ||
                ((e.ctrlKey || e.metaKey) && e.shiftKey && ['I', 'i', 'J', 'j', 'C', 'c'].includes(e.key))
            ) {
                return;
            }
            const target = e.target;
            if (target instanceof Element && target.closest('button, a, input, select, textarea, .mayhen_curated_variants_sidebar')) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        },
        true
    );

    // Tự động phục hồi trạng thái khóa nếu người dùng dùng F12 sửa DOM (xóa class hoặc gỡ disabled)
    let isMutatingLock = false;
    const lockIntegrityObserver = new MutationObserver(() => {
        if (!isCraftingLocked || isMutatingLock) return;
        isMutatingLock = true;
        if (!appEl.classList.contains('is_crafting_locked')) {
            appEl.classList.add('is_crafting_locked');
        }
        if (topbar && !topbar.classList.contains('is_crafting')) {
            topbar.classList.add('is_crafting');
        }
        if (craftingPill && (!craftingPill.classList.contains('is-active') || craftingPill.style.display === 'none')) {
            craftingPill.classList.add('is-active');
            craftingPill.style.display = 'inline-flex';
        }
        document.querySelectorAll('[data-crafting-locked="1"]').forEach((el) => {
            if ('disabled' in el && !el.disabled) {
                el.disabled = true;
            }
            if (el.tagName === 'A' && el.hasAttribute('href')) {
                el.dataset.lockedHref = el.getAttribute('href') || el.dataset.lockedHref || '';
                el.removeAttribute('href');
            }
        });
        isMutatingLock = false;
    });

    lockIntegrityObserver.observe(appEl, {
        attributes: true,
        subtree: true,
        attributeFilter: ['class', 'disabled', 'style', 'href'],
    });

    function startCraftingMode() {
        isCraftingLocked = true;
        craftingNonce = generateNonce();
        lastVerifiedStep = 0;
        totalExpectedSteps = 0;
        craftingStartedAtMs = Date.now();

        applyInteractiveDomLock(true);

        if (craftingPill && craftingText) {
            craftingPill.classList.add('is-active');
            craftingPill.style.display = 'inline-flex';
            craftingText.textContent = '✦ AI đang dựng tác phẩm...';
            if (progressBar) progressBar.style.width = '5%';
            if (progressPct) progressPct.textContent = '5%';
        }
    }

    async function unlockCraftingAfterServerCheck() {
        try {
            const res = await fetch(`/api/curated/complete-crafting/${invitationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            if (res.status === 423) {
                const data = await res.json();
                const waitMs = Math.max(1000, ((data?.remaining_seconds || 2) * 1000));
                setTimeout(unlockCraftingAfterServerCheck, waitMs);
                return;
            }
        } catch (_e) {}

        isCraftingLocked = false;
        applyInteractiveDomLock(false);

        if (craftingPill && craftingText) {
            craftingText.textContent = '✦ Thiệp cưới của bạn đã sẵn sàng!';
            if (progressBar) progressBar.style.width = '100%';
            if (progressPct) progressPct.textContent = '100%';
            setTimeout(() => {
                if (!isCraftingLocked) {
                    craftingPill.classList.remove('is-active');
                    craftingPill.style.display = 'none';
                }
            }, 1800);
        }
        try {
            history.replaceState({}, '', window.location.pathname);
        } catch (_e) {}
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

    // Khi iframe tải xong, truyền craftingNonce riêng vào iframe để xác thực nguồn tin nhắn
    if (iframe) {
        iframe.addEventListener('load', () => {
            if (isCraftingLocked && craftingNonce) {
                try {
                    iframe.contentWindow?.postMessage(
                        { type: 'MAYHEN_CRAFTING_BIND_NONCE', nonce: craftingNonce },
                        window.location.origin
                    );
                } catch (_e) {}
            }
        });
    }

    window.addEventListener('message', (e) => {
        if (!e.data || typeof e.data !== 'object') return;
        // Chống giả mạo từ Console: Tin nhắn bắt buộc phải đến từ đúng cửa sổ iframe preview và cùng origin
        if (!iframe || e.source !== iframe.contentWindow) return;
        if (e.origin && e.origin !== window.location.origin) return;

        if (e.data.type === 'MAYHEN_CRAFTING_READY_HANDSHAKE' && isCraftingLocked) {
            try {
                iframe.contentWindow?.postMessage(
                    { type: 'MAYHEN_CRAFTING_BIND_NONCE', nonce: craftingNonce },
                    window.location.origin
                );
            } catch (_e) {}
            return;
        }

        if (e.data.type === 'MAYHEN_CRAFTING_STEP') {
            const stepNum = Number(e.data.step || 0);
            const totalNum = Number(e.data.total || 0);
            if (totalNum > 0) totalExpectedSteps = totalNum;
            if (stepNum >= lastVerifiedStep) {
                lastVerifiedStep = stepNum;
            }

            if (craftingPill && craftingText) {
                craftingPill.classList.add('is-active');
                craftingPill.style.display = 'inline-flex';
                craftingText.textContent = e.data.detailTitle || `✦ Đang hoàn thiện: ${e.data.sectionTitle || e.data.section}`;
            }
            if (progressBar) progressBar.style.width = `${e.data.percent || 0}%`;
            if (progressPct) progressPct.textContent = `${e.data.percent || 0}%`;
        }

        if (e.data.type === 'MAYHEN_CRAFTING_COMPLETE') {
            // Chống dùng F12 trong iframe để bắn MAYHEN_CRAFTING_COMPLETE sớm:
            // Kiểm tra đã đi qua các bước dựng và đủ thời gian tối thiểu
            const elapsed = Date.now() - craftingStartedAtMs;
            if (totalExpectedSteps > 3 && lastVerifiedStep < totalExpectedSteps - 1 && elapsed < MIN_CLIENT_CRAFTING_MS) {
                return;
            }
            unlockCraftingAfterServerCheck();
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
        if (isCraftingLocked) {
            showToast('✦ AI đang tạo từng phần tử thiệp cưới, vui lòng đợi hoàn tất 100%...');
            return null;
        }
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
        if (isCraftingLocked) {
            showToast('✦ AI đang tạo từng phần tử thiệp cưới, vui lòng đợi hoàn tất 100%...');
            return;
        }
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
            if (res.ok && data.success) {
                currentTemplateSlug = data.template_slug || currentTemplateSlug;
                const nameEl = document.getElementById('templateSourceName');
                if (nameEl && data.template_name) nameEl.textContent = data.template_name;
                document.querySelectorAll('[data-pick-template]').forEach((b) => {
                    b.classList.toggle('is-active', b.dataset.pickTemplate === currentTemplateSlug);
                });
                if (window.innerWidth <= 900) closeDrawer();
                startCraftingMode();
                reloadIframeClean(true);
                showToast(`✦ Đang tạo lại với mẫu: ${data.template_name || 'Mới'}`);
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
        if (isCraftingLocked) return;
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
            if (isCraftingLocked) {
                showToast('✦ AI đang tạo từng phần tử thiệp cưới, vui lòng đợi hoàn tất 100%...');
                return;
            }
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
        if (isCraftingLocked) {
            showToast('✦ AI đang tạo từng phần tử thiệp cưới, vui lòng đợi hoàn tất 100%...');
            return;
        }
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
