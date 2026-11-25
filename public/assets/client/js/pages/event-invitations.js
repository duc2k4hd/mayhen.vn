/**
 * MAYHEN.VN — DEDICATED STANDALONE EVENT INVITATION STUDIO & 3D ENVELOPE JS
 * 100% Pure Vanilla JS • Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    /* =====================================================================
     * 1. SILKY 60FPS 4-LAYER REALISTIC PAPER ENVELOPE EXTRACTION ANIMATION
     *    (CHUẨN VIDEO MẪU: ĐÓNG KÍN -> MỞ NẮP -> RÚT NGANG -> XOAY ĐỨNG)
     * ===================================================================== */
    const stage3d = document.getElementById('mayhenEvent3dStage');
    const sheetElMetric = document.getElementById('mayhenEventStationerySheet');
    let envelopeAnimEndTimer = null;
    let envelopeClosePhaseTimer = null;

    function syncEnvelopeStageMetrics() {
        if (!stage3d || !sheetElMetric) return;

        const isMobile = window.innerWidth <= 580
            || stage3d.closest('.mayhen_event_studio_viewport_frame')?.getAttribute('data-viewport') === 'mobile';

        const envW = isMobile ? 342 : 456;
        const envH = isMobile ? 238 : 316;
        const envClosedY = isMobile ? 155 : 190;
        const envOpenY = isMobile ? 185 : 232;
        const envPullY = isMobile ? 216 : 274;
        const restCardY = isMobile ? 12 : 16;
        const bottomPeek = isMobile ? 76 : 94;
        const minStageH = isMobile ? 520 : 635;

        const cardW = sheetElMetric.offsetWidth || (isMobile ? 296 : 386);
        const cardH = sheetElMetric.offsetHeight || (isMobile ? 432 : 525);

        const targetStageH = Math.max(minStageH, cardH + restCardY + bottomPeek);
        const targetEnvRestY = targetStageH + 3;

        const insideScale = Math.min(isMobile ? 0.71 : 0.73, (envW - 36) / Math.max(1, cardH));
        const pullScale = Math.min(insideScale + 0.04, (envW - 26) / Math.max(1, cardH));

        const cardInsideY = (envClosedY + envH / 2) - (cardH / 2);
        const cardOpenY = (envOpenY + envH / 2) - (cardH / 2);
        const cardPullCenterY = envPullY + 34 - (cardW * pullScale) / 2;
        const cardPullY = cardPullCenterY - (cardH / 2);

        stage3d.style.setProperty('--mh-stage-h', `${targetStageH}px`);
        stage3d.style.setProperty('--mh-env-y-rest', `${targetEnvRestY}px`);
        stage3d.style.setProperty('--mh-card-inside-scale', insideScale.toFixed(3));
        stage3d.style.setProperty('--mh-card-pull-scale', pullScale.toFixed(3));
        stage3d.style.setProperty('--mh-card-y-inside', `${cardInsideY.toFixed(1)}px`);
        stage3d.style.setProperty('--mh-card-y-open', `${cardOpenY.toFixed(1)}px`);
        stage3d.style.setProperty('--mh-card-y-pull', `${cardPullY.toFixed(1)}px`);
    }

    syncEnvelopeStageMetrics();
    window.addEventListener('resize', syncEnvelopeStageMetrics);

    if (typeof ResizeObserver !== 'undefined' && sheetElMetric) {
        const sheetObserver = new ResizeObserver(() => syncEnvelopeStageMetrics());
        sheetObserver.observe(sheetElMetric);
    }

    function clearEnvelopeTimers() {
        if (envelopeAnimEndTimer) {
            window.clearTimeout(envelopeAnimEndTimer);
            envelopeAnimEndTimer = null;
        }
        if (envelopeClosePhaseTimer) {
            window.clearTimeout(envelopeClosePhaseTimer);
            envelopeClosePhaseTimer = null;
        }
    }

    function startExtractionRun() {
        if (!stage3d) return;
        syncEnvelopeStageMetrics();
        stage3d.classList.remove('is-sealed', 'is-closing-transition', 'is-extracted', 'is-playing-extract', 'is-envelope-hidden');
        // Force reflow để CSS Animation chạy lại chuẩn xác từ frame 0%
        void stage3d.offsetWidth;
        stage3d.classList.add('is-playing-extract');

        envelopeAnimEndTimer = window.setTimeout(() => {
            if (!stage3d) return;
            stage3d.classList.remove('is-playing-extract');
            stage3d.classList.add('is-extracted');
            envelopeAnimEndTimer = null;
        }, 2560);
    }

    function playEnvelopeExtractionAnimation(needFoldFirst = true) {
        if (!stage3d) return;
        clearEnvelopeTimers();

        const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            stage3d.classList.remove('is-sealed', 'is-closing-transition', 'is-playing-extract', 'is-envelope-hidden');
            stage3d.classList.add('is-extracted');
            return;
        }

        // Nếu đang ở trạng thái mở (.is-extracted) và bấm Replay: gập thiệp cất vào phong bì trong 280ms rồi mới rút ra lại
        if (needFoldFirst && stage3d.classList.contains('is-extracted')) {
            stage3d.classList.remove('is-playing-extract', 'is-extracted', 'is-envelope-hidden');
            stage3d.classList.add('is-closing-transition', 'is-sealed');

            envelopeClosePhaseTimer = window.setTimeout(() => {
                envelopeClosePhaseTimer = null;
                startExtractionRun();
            }, 300);
            return;
        }

        startExtractionRun();
    }

    // Trang xem thiệp / link công khai: khởi đầu đang đóng kín (.is-sealed), tự động mở nắp & rút thiệp sau 140ms
    if (stage3d && !document.getElementById('mayhenEventStudioForm') && stage3d.dataset.initialState === 'is-sealed') {
        envelopeClosePhaseTimer = window.setTimeout(() => {
            envelopeClosePhaseTimer = null;
            playEnvelopeExtractionAnimation(false);
        }, 140);
    }

    document.querySelectorAll('[data-event-toggle-envelope]').forEach((btn) => {
        btn.addEventListener('click', () => {
            playEnvelopeExtractionAnimation(true);
        });
    });

    // Mở / đóng Popup RSVP, Popup Lì xì VietQR & Popup Đổi mẫu nhanh
    const rsvpPopup = document.getElementById('mayhenEventRsvpPopup');
    const giftPopup = document.getElementById('mayhenEventGiftPopup');
    const switcherModal = document.getElementById('mayhenEventTemplateSwitcherModal');

    document.querySelectorAll('[data-open-event-popup]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-open-event-popup');
            if (type === 'rsvp' && rsvpPopup) rsvpPopup.classList.add('is-open');
            if (type === 'gift' && giftPopup) giftPopup.classList.add('is-open');
        });
    });

    const btnOpenSwitcher = document.getElementById('btnOpenTemplateSwitcherModal');
    if (btnOpenSwitcher && switcherModal) {
        btnOpenSwitcher.addEventListener('click', () => {
            switcherModal.classList.add('is-open');
        });
    }

    document.querySelectorAll('[data-close-event-popup]').forEach((btn) => {
        btn.addEventListener('click', () => {
            rsvpPopup?.classList.remove('is-open');
            giftPopup?.classList.remove('is-open');
            switcherModal?.classList.remove('is-open');
        });
    });

    /* =====================================================================
     * 2. DEDICATED STANDALONE 6-TAB STUDIO & REALTIME STATIONERY SYNC
     * ===================================================================== */
    const studioApp = document.getElementById('mayhenEventStudioApp');
    const studioForm = document.getElementById('mayhenEventStudioForm');
    const previewRoot = document.getElementById('mayhenEventPreviewRoot');
    if (!studioForm || !previewRoot) return;

    const saveStatusText = document.getElementById('mayhenEventStudioSaveText');
    function markDirtyStatus(label = 'Đang chỉnh sửa trực tiếp...') {
        if (saveStatusText) saveStatusText.textContent = label;
    }

    // Chuyển đổi 6 Tab trong Studio
    const tabBtns = document.querySelectorAll('[data-event-tab-btn]');
    const tabPanels = document.querySelectorAll('[data-event-tab-panel]');

    function activateStudioTab(targetTab) {
        tabBtns.forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-tab-btn') === targetTab));
        tabPanels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-event-tab-panel') === targetTab));
    }

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-event-tab-btn');
            if (targetTab) activateStudioTab(targetTab);
            if (studioApp && window.innerWidth <= 980) {
                studioApp.setAttribute('data-mobile-mode', 'edit');
                document.querySelectorAll('[data-mobile-view-btn]').forEach((mb) => {
                    mb.classList.toggle('is-active', mb.getAttribute('data-mobile-view-btn') === 'edit');
                });
            }
        });
    });

    // Chuyển đổi chế độ trên Mobile & Tablet (Bảng Chỉnh Sửa vs Xem Thiệp 3D)
    document.querySelectorAll('[data-mobile-view-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const viewMode = btn.getAttribute('data-mobile-view-btn') || 'edit';
            if (studioApp) studioApp.setAttribute('data-mobile-mode', viewMode);
            document.querySelectorAll('[data-mobile-view-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
        });
    });

    // Công cụ Thanh Topbar: Chuyển khung Desktop / Mobile, Thu phóng (Zoom) & Ẩn/Hiện Phong Bì
    const viewportFrame = document.getElementById('mayhenEventStudioViewportFrame');
    document.querySelectorAll('[data-studio-viewport]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const vp = btn.getAttribute('data-studio-viewport') || 'desktop';
            if (viewportFrame) viewportFrame.setAttribute('data-viewport', vp);
            document.querySelectorAll('[data-studio-viewport]').forEach((b) => b.classList.toggle('is-active', b === btn));
            window.requestAnimationFrame(syncEnvelopeStageMetrics);
        });
    });

    document.querySelectorAll('[data-studio-zoom]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const scale = btn.getAttribute('data-studio-zoom') || '1';
            if (viewportFrame) viewportFrame.style.transform = `scale(${scale})`;
            document.querySelectorAll('[data-studio-zoom]').forEach((b) => b.classList.toggle('is-active', b === btn));
        });
    });

    const btnToggleEnvelopeVis = document.getElementById('btnStudioToggleEnvelopeVisibility');
    if (btnToggleEnvelopeVis && stage3d) {
        btnToggleEnvelopeVis.addEventListener('click', () => {
            stage3d.classList.toggle('is-envelope-hidden');
            btnToggleEnvelopeVis.classList.toggle('is-active', stage3d.classList.contains('is-envelope-hidden'));
        });
    }

    function formatVietnameseDateDots(isoDate) {
        const m = String(isoDate || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return isoDate || '';
        return `${m[3]} . ${m[2]} . ${m[1]}`;
    }

    // Đồng bộ trực tiếp từng ô input sang khung thiệp bên phải
    function syncTextTarget(key, val) {
        document.querySelectorAll(`[data-event-sync="${key}"]`).forEach((target) => {
            if (key === 'event_date') {
                target.textContent = formatVietnameseDateDots(val);
            } else if (key === 'map_url') {
                if (target.tagName === 'A') {
                    target.setAttribute('href', val || '#');
                }
            } else {
                target.textContent = val;
            }
        });
        markDirtyStatus();
    }

    studioForm.querySelectorAll('[data-event-input]').forEach((inputEl) => {
        inputEl.addEventListener('input', () => {
            const key = inputEl.getAttribute('data-event-input');
            if (key) syncTextTarget(key, inputEl.value);
        });
    });

    // CHẠM TRỰC TIẾP VÀO CHỮ TRÊN MẶT THIỆP ĐỂ NHẢY ĐẾN ĐÚNG TAB & Ô NHẬP (CLICK-TO-EDIT)
    const venueFieldKeys = new Set(['event_date', 'party_time', 'lunar_note', 'venue_name', 'venue_address', 'map_url']);
    previewRoot.querySelectorAll('[data-studio-click-edit]').forEach((clickableEl) => {
        clickableEl.addEventListener('click', (ev) => {
            ev.stopPropagation();
            const fieldKey = clickableEl.getAttribute('data-studio-click-edit');
            if (!fieldKey) return;

            const targetTab = venueFieldKeys.has(fieldKey) ? 'venue' : 'content';
            activateStudioTab(targetTab);

            const targetInput = studioForm.querySelector(`[data-event-input="${fieldKey}"]`);
            if (targetInput) {
                targetInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetInput.focus();
                targetInput.classList.add('mayhen_event_input_flash');
                setTimeout(() => {
                    targetInput.classList.remove('mayhen_event_input_flash');
                }, 1200);
            }
        });
    });

    // Kho Gợi Ý Lời Mời & Lời Chúc Chuẩn Văn Hóa Việt 1 Chạm
    document.querySelectorAll('[data-wording-preset]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const badge = btn.getAttribute('data-preset-badge') || '';
            const sub = btn.getAttribute('data-preset-sub') || '';
            const msg = btn.getAttribute('data-preset-msg') || '';

            const badgeIn = studioForm.querySelector('[name="event_badge"]');
            const subIn = studioForm.querySelector('[name="sub_host_name"]');
            const msgIn = studioForm.querySelector('[name="invitation_message"]');

            if (badgeIn && badge) { badgeIn.value = badge; syncTextTarget('event_badge', badge); }
            if (subIn && sub) { subIn.value = sub; syncTextTarget('sub_host_name', sub); }
            if (msgIn && msg) { msgIn.value = msg; syncTextTarget('invitation_message', msg); }
        });
    });

    function replacePrefixClass(el, prefix, newVal) {
        if (!el) return;
        const regex = new RegExp(`\\b${prefix}[a-z0-9-]+`, 'g');
        el.className = el.className.replace(regex, '').trim() + ` ${prefix}${newVal}`;
    }

    // 1. Đổi Bảng Màu Chủ Đạo
    const paletteInput = document.getElementById('evtColorPaletteInput');
    function applyPalette(val) {
        if (!val) return;
        if (paletteInput) paletteInput.value = val;
        document.querySelectorAll('[data-event-palette-btn]').forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-palette-btn') === val));
        replacePrefixClass(previewRoot, 'mayhen_event_palette_', val);
        markDirtyStatus();
    }
    document.querySelectorAll('[data-event-palette-btn]').forEach((btn) => {
        btn.addEventListener('click', () => applyPalette(btn.getAttribute('data-event-palette-btn')));
    });

    // 2. Đổi Màu Vỏ Phong Bì 3D (Envelope Color)
    const envColorInput = document.getElementById('evtEnvelopeColorInput');
    function applyEnvelopeColor(val) {
        if (!val) return;
        if (envColorInput) envColorInput.value = val;
        if (stage3d) stage3d.classList.remove('has-real-envelope-img');
        document.querySelectorAll('[data-event-env-color-btn]').forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-env-color-btn') === val));
        replacePrefixClass(previewRoot, 'mayhen_event_env_', val);
        markDirtyStatus();
    }
    document.querySelectorAll('[data-event-env-color-btn]').forEach((btn) => {
        btn.addEventListener('click', () => applyEnvelopeColor(btn.getAttribute('data-event-env-color-btn')));
    });

    // 3. Đổi Hoa Văn Lớp Lót Phong Bì (Envelope Liner)
    const envLinerInput = document.getElementById('evtEnvelopeLinerInput');
    function applyEnvelopeLiner(val) {
        if (!val) return;
        if (envLinerInput) envLinerInput.value = val;
        if (stage3d) stage3d.classList.remove('has-real-envelope-img');
        document.querySelectorAll('[data-event-liner-btn]').forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-liner-btn') === val));
        document.querySelectorAll('[data-liner-sync="true"]').forEach((targetEl) => {
            replacePrefixClass(targetEl, 'mayhen_event_liner_', val);
        });
        markDirtyStatus();
    }
    document.querySelectorAll('[data-event-liner-btn]').forEach((btn) => {
        btn.addEventListener('click', () => applyEnvelopeLiner(btn.getAttribute('data-event-liner-btn')));
    });

    // 4. Đổi Khuôn Cắt & Viền Mặt Thiệp (Card Border)
    const borderInput = document.getElementById('evtCardBorderInput');
    const sheetEl = document.getElementById('mayhenEventStationerySheet');
    function applyCardBorder(val) {
        if (!val) return;
        if (borderInput) borderInput.value = val;
        document.querySelectorAll('[data-event-border-btn]').forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-border-btn') === val));
        replacePrefixClass(sheetEl, 'mayhen_event_border_', val);
        markDirtyStatus();
    }
    document.querySelectorAll('[data-event-border-btn]').forEach((btn) => {
        btn.addEventListener('click', () => applyCardBorder(btn.getAttribute('data-event-border-btn')));
    });

    // 5. Đổi Khung Tranh Minh Họa Sự Kiện (Artwork Motif Scene)
    const motifInput = document.getElementById('evtArtworkMotifInput');
    const sceneLayer = document.getElementById('mayhenEventCardSceneLayer');
    function applyArtworkMotif(val) {
        if (!val) return;
        if (motifInput) motifInput.value = val;
        document.querySelectorAll('[data-event-motif-btn]').forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-motif-btn') === val));
        const tplNode = document.querySelector(`#mayhenEventSceneTemplatesBank template[data-scene-template="${val}"]`);
        if (tplNode && sceneLayer) {
            sceneLayer.innerHTML = tplNode.innerHTML;
        }
        markDirtyStatus();
    }
    document.querySelectorAll('[data-event-motif-btn]').forEach((btn) => {
        btn.addEventListener('click', () => applyArtworkMotif(btn.getAttribute('data-event-motif-btn')));
    });

    // 6. Đổi Phông Nền Sân Khấu (Backdrop)
    const backdropInput = document.getElementById('evtBackdropInput');
    function applyBackdrop(val) {
        if (!val) return;
        if (backdropInput) backdropInput.value = val;
        previewRoot.style.backgroundImage = '';
        document.querySelectorAll('[data-event-backdrop-btn]').forEach((b) => b.classList.toggle('is-active', b.getAttribute('data-event-backdrop-btn') === val));
        replacePrefixClass(previewRoot, 'mayhen_event_backdrop_', val);
        markDirtyStatus();
    }
    document.querySelectorAll('[data-event-backdrop-btn]').forEach((btn) => {
        btn.addEventListener('click', () => applyBackdrop(btn.getAttribute('data-event-backdrop-btn')));
    });

    // 7. Đổi Phông Chữ Mặt Thiệp (Font Style)
    const fontInput = document.getElementById('evtFontStyleInput');
    document.querySelectorAll('[data-event-font-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-font-btn');
            if (!val) return;
            if (fontInput) fontInput.value = val;
            document.querySelectorAll('[data-event-font-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(previewRoot, 'mayhen_event_font_', val);
            markDirtyStatus();
        });
    });

    // 8. Nút Phối Màu Đẹp Ngẫu Nhiên (Magic Shuffle) trên Topbar
    const curatedCombos = [
        { palette: 'dusty-rose', env: 'blush-pink', liner: 'berry-dots', border: 'scalloped-wave', backdrop: 'silk-cream' },
        { palette: 'ruby', env: 'crimson-red', liner: 'lunar-clouds', border: 'lunar-brocade', backdrop: 'festive-velvet' },
        { palette: 'sage-green', env: 'forest-green', liner: 'gold-foil', border: 'pine-garland', backdrop: 'silk-cream' },
        { palette: 'royal-navy', env: 'midnight-navy', liner: 'starry-night', border: 'lantern-arch', backdrop: 'moonlight-navy' },
        { palette: 'warm-amber', env: 'imperial-gold', liner: 'gold-foil', border: 'gold-double', backdrop: 'warm-walnut' },
        { palette: 'ruby', env: 'crimson-red', liner: 'candy-stripe', border: 'candy-stripe', backdrop: 'silk-cream' },
    ];
    let comboIndex = 0;
    const btnMagicShuffle = document.getElementById('btnStudioMagicShuffle');
    if (btnMagicShuffle) {
        btnMagicShuffle.addEventListener('click', () => {
            const combo = curatedCombos[comboIndex % curatedCombos.length];
            comboIndex += 1;
            applyPalette(combo.palette);
            applyEnvelopeColor(combo.env);
            applyEnvelopeLiner(combo.liner);
            applyCardBorder(combo.border);
            applyBackdrop(combo.backdrop);
            showStudioMessage('✦ Đã phối bộ Phong bì & Mặt thiệp hài hòa mới!');
        });
    }

    // 9. Tải ảnh riêng lên mặt thiệp (Custom Photo Upload)
    const photoFileInput = document.getElementById('evtCustomPhotoFile');
    const photoDataInput = document.getElementById('evtCustomPhotoDataInput');
    const photoPreviewWrap = document.getElementById('evtCustomPhotoPreviewWrap');
    const photoPreviewImg = document.getElementById('evtCustomPhotoPreviewImg');
    const btnClearPhoto = document.getElementById('btnClearEventCustomPhoto');

    if (photoFileInput) {
        photoFileInput.addEventListener('change', () => {
            const file = photoFileInput.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                const dataUrl = String(ev.target?.result || '');
                if (photoDataInput) photoDataInput.value = dataUrl;
                if (photoPreviewImg) photoPreviewImg.src = dataUrl;
                if (photoPreviewWrap) photoPreviewWrap.style.display = 'block';
                if (btnClearPhoto) btnClearPhoto.style.display = 'inline-flex';
                markDirtyStatus();
            };
            reader.readAsDataURL(file);
        });
    }

    if (btnClearPhoto) {
        btnClearPhoto.addEventListener('click', () => {
            if (photoDataInput) photoDataInput.value = '';
            if (photoFileInput) photoFileInput.value = '';
            if (photoPreviewWrap) photoPreviewWrap.style.display = 'none';
            btnClearPhoto.style.display = 'none';
            markDirtyStatus();
        });
    }

    // 10. Công tắc Bật/Tắt Thời gian & Địa điểm tiệc, RSVP & VietQR
    const showVenueCheckbox = document.getElementById('evtShowDatetimeVenue');
    const datetimeSection = document.getElementById('evtPreviewDatetimeSection');
    const venueSection = document.getElementById('evtPreviewVenueSection');
    if (showVenueCheckbox) {
        showVenueCheckbox.addEventListener('change', () => {
            const show = showVenueCheckbox.checked;
            if (datetimeSection) datetimeSection.style.display = show ? 'block' : 'none';
            if (venueSection) venueSection.style.display = show ? 'inline-flex' : 'none';
            markDirtyStatus();
        });
    }

    const qrCheckbox = document.getElementById('evtEnableGiftQr');
    const qrConfigFields = document.getElementById('evtGiftQrConfigFields');
    const qrPreviewBox = document.getElementById('evtPreviewGiftQrBox');
    if (qrCheckbox) {
        qrCheckbox.addEventListener('change', () => {
            const enabled = qrCheckbox.checked;
            if (qrConfigFields) qrConfigFields.style.display = enabled ? 'block' : 'none';
            if (qrPreviewBox) qrPreviewBox.style.display = enabled ? 'inline-flex' : 'none';
            markDirtyStatus();
        });
    }

    const rsvpCheckbox = document.getElementById('evtEnableRsvp');
    const rsvpPreviewBox = document.getElementById('evtPreviewRsvpBox');
    if (rsvpCheckbox) {
        rsvpCheckbox.addEventListener('change', () => {
            if (rsvpPreviewBox) rsvpPreviewBox.style.display = rsvpCheckbox.checked ? 'inline-flex' : 'none';
            markDirtyStatus();
        });
    }

    // 11. Tab 6: Tạo Danh Sách Link Cá Nhân Hóa Hàng Loạt Cho Khách Mời
    const batchGuestsTextarea = document.getElementById('evtBatchGuestNamesInput');
    const btnGenBatchGuests = document.getElementById('btnGenerateBatchGuestLinks');
    const batchGuestsContainer = document.getElementById('evtBatchGuestItemsContainer');

    function renderBatchGuestItems() {
        if (!batchGuestsTextarea || !batchGuestsContainer) return;
        const lines = batchGuestsTextarea.value
            .split(/\r?\n/)
            .map((s) => s.trim())
            .filter(Boolean);

        batchGuestsContainer.innerHTML = '';
        lines.forEach((guestName) => {
            const row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border-radius:10px;background:#FFFFFF;border:1px solid #E5DDD0;';

            const nameSpan = document.createElement('strong');
            nameSpan.style.cssText = 'font-size:12px;color:#1F1D1A;';
            nameSpan.textContent = guestName;

            const actions = document.createElement('div');
            actions.style.cssText = 'display:flex;gap:6px;flex-shrink:0;';

            const btnPreviewGuest = document.createElement('button');
            btnPreviewGuest.type = 'button';
            btnPreviewGuest.className = 'mayhen_event_btn_outline';
            btnPreviewGuest.style.cssText = 'padding:4px 8px;font-size:11px;';
            btnPreviewGuest.textContent = 'Xem trên thiệp';
            btnPreviewGuest.addEventListener('click', () => {
                syncTextTarget('guest_preview', guestName);
                const guestIn = studioForm.querySelector('[data-event-input="guest_preview"]');
                if (guestIn) guestIn.value = guestName;
            });

            actions.appendChild(btnPreviewGuest);
            row.appendChild(nameSpan);
            row.appendChild(actions);
            batchGuestsContainer.appendChild(row);
        });
    }

    if (btnGenBatchGuests) {
        btnGenBatchGuests.addEventListener('click', () => {
            renderBatchGuestItems();
            showStudioMessage('✓ Đã tạo danh sách khách mời cá nhân hóa!');
        });
        renderBatchGuestItems();
    }

    /* =====================================================================
     * 3. SAVE DRAFT & PRO-GATED PUBLISH
     * ===================================================================== */
    const draftIdInput = document.getElementById('evtDraftIdInput');
    const statusToast = document.getElementById('mayhenEventStudioToast');

    const proGateModal = document.getElementById('mayhenEventProModal');
    const proGateMsg = document.getElementById('mayhenEventProModalMessage');
    const proGatePrimaryBtn = document.getElementById('mayhenEventProModalPrimaryBtn');

    const publishedModal = document.getElementById('mayhenEventPublishedModal');
    const publishedUrlInput = document.getElementById('mayhenEventPublishedUrl');
    const publishedGuestInput = document.getElementById('mayhenEventGuestNameInput');
    const publishedOpenLink = document.getElementById('mayhenEventOpenPublicLink');
    const btnCopyPublishedLink = document.getElementById('btnCopyEventPublishedUrl');

    let basePublicUrl = '';

    function showStudioMessage(text) {
        if (!statusToast) return;
        statusToast.textContent = text;
        statusToast.style.display = 'block';
        setTimeout(() => {
            statusToast.style.display = 'none';
        }, 3600);
    }

    function collectFormPayload() {
        const fd = new FormData(studioForm);
        return {
            draft_id: draftIdInput?.value ? Number(draftIdInput.value) : null,
            template_slug: fd.get('template_slug'),
            title: fd.get('title'),
            event_badge: fd.get('event_badge'),
            host_name: fd.get('host_name'),
            sub_host_name: fd.get('sub_host_name'),
            invitation_message: fd.get('invitation_message'),
            event_date: fd.get('event_date'),
            welcome_time: fd.get('welcome_time'),
            party_time: fd.get('party_time'),
            lunar_note: fd.get('lunar_note'),
            venue_name: fd.get('venue_name'),
            venue_address: fd.get('venue_address'),
            map_url: fd.get('map_url'),
            dresscode_note: fd.get('dresscode_note'),
            color_palette: fd.get('color_palette'),
            envelope_color: fd.get('envelope_color'),
            envelope_liner: fd.get('envelope_liner'),
            card_border: fd.get('card_border'),
            artwork_motif: fd.get('artwork_motif'),
            backdrop: fd.get('backdrop'),
            font_style: fd.get('font_style'),
            custom_photo_data: fd.get('custom_photo_data'),
            rsvp_question: fd.get('rsvp_question'),
            show_datetime_venue: showVenueCheckbox ? showVenueCheckbox.checked : true,
            enable_rsvp: rsvpCheckbox ? rsvpCheckbox.checked : true,
            enable_gift_qr: qrCheckbox ? qrCheckbox.checked : false,
            bank_name: fd.get('bank_name'),
            bank_account_number: fd.get('bank_account_number'),
            bank_account_holder: fd.get('bank_account_holder'),
            gift_note: fd.get('gift_note'),
        };
    }

    async function handleSaveDraft() {
        const saveBtns = document.querySelectorAll('[data-trigger-save-draft]');
        saveBtns.forEach((b) => { b.disabled = true; });
        markDirtyStatus('Đang lưu bản nháp...');
        try {
            const res = await fetch('/thiep-su-kien/luu-nhap', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(collectFormPayload()),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                if (draftIdInput && data.draft_id) draftIdInput.value = String(data.draft_id);
                markDirtyStatus('Đã lưu nháp an toàn');
                showStudioMessage('✓ Đã lưu bản nháp thiết kế thiệp của bạn!');
            } else {
                markDirtyStatus('Chưa lưu được');
                showStudioMessage(data.message || 'Vui lòng kiểm tra lại thông tin.');
            }
        } catch (_e) {
            markDirtyStatus('Lỗi kết nối');
            showStudioMessage('Không thể kết nối máy chủ, vui lòng thử lại.');
        } finally {
            saveBtns.forEach((b) => { b.disabled = false; });
        }
    }

    async function handlePublishPro() {
        const pubBtns = document.querySelectorAll('[data-trigger-publish-pro]');
        pubBtns.forEach((b) => { b.disabled = true; });
        markDirtyStatus('Đang kiểm tra & xuất bản...');
        try {
            const res = await fetch('/thiep-su-kien/xuat-ban', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(collectFormPayload()),
            });
            const data = await res.json();

            if (data.draft_id && draftIdInput) {
                draftIdInput.value = String(data.draft_id);
            }

            if (res.status === 401 || res.status === 403) {
                markDirtyStatus('Đã lưu nháp • Cần Gói Pro để xuất bản');
                if (proGateMsg && data.message) proGateMsg.textContent = data.message;
                if (proGatePrimaryBtn) {
                    if (res.status === 401 && data.login_url) {
                        proGatePrimaryBtn.href = data.login_url;
                        proGatePrimaryBtn.textContent = 'Đăng Nhập Tài Khoản & Nâng Cấp Gói Pro →';
                    } else if (data.upgrade_url) {
                        proGatePrimaryBtn.href = data.upgrade_url;
                        proGatePrimaryBtn.textContent = 'Nâng Cấp Gói Pro VIP (199.000đ) Để Xuất Bản Ngay →';
                    }
                }
                if (proGateModal) proGateModal.classList.add('is-open');
                return;
            }

            if (res.ok && data.success && data.public_url) {
                markDirtyStatus('Đã xuất bản chính thức!');
                basePublicUrl = data.public_url;
                if (publishedUrlInput) publishedUrlInput.value = basePublicUrl;
                if (publishedOpenLink) publishedOpenLink.href = basePublicUrl;
                if (publishedModal) publishedModal.classList.add('is-open');
            } else {
                showStudioMessage(data.message || 'Không thể xuất bản thiệp lúc này.');
            }
        } catch (_e) {
            showStudioMessage('Lỗi kết nối máy chủ.');
        } finally {
            pubBtns.forEach((b) => { b.disabled = false; });
        }
    }

    document.querySelectorAll('[data-trigger-save-draft]').forEach((btn) => {
        btn.addEventListener('click', handleSaveDraft);
    });

    document.querySelectorAll('[data-trigger-publish-pro]').forEach((btn) => {
        btn.addEventListener('click', handlePublishPro);
    });

    if (publishedGuestInput) {
        publishedGuestInput.addEventListener('input', () => {
            const g = publishedGuestInput.value.trim();
            const fullUrl = g ? `${basePublicUrl}?guest=${encodeURIComponent(g)}` : basePublicUrl;
            if (publishedUrlInput) publishedUrlInput.value = fullUrl;
            if (publishedOpenLink) publishedOpenLink.href = fullUrl;
        });
    }

    if (btnCopyPublishedLink && publishedUrlInput) {
        btnCopyPublishedLink.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(publishedUrlInput.value);
                btnCopyPublishedLink.textContent = 'Đã Sao Chép!';
                setTimeout(() => {
                    btnCopyPublishedLink.textContent = 'Sao Chép Link';
                }, 2000);
            } catch (_e) {}
        });
    }

    document.querySelectorAll('[data-close-event-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            proGateModal?.classList.remove('is-open');
            publishedModal?.classList.remove('is-open');
        });
    });
});

document.addEventListener('click', async (event) => {
    const copyBtn = event.target.closest('[data-copy-wording]');
    if (!copyBtn) {
        return;
    }
    const text = copyBtn.getAttribute('data-copy-wording') || '';
    if (!text) {
        return;
    }
    try {
        await navigator.clipboard.writeText(text);
        const original = copyBtn.textContent;
        copyBtn.textContent = '✓ Đã sao chép';
        copyBtn.classList.add('is-copied');
        setTimeout(() => {
            copyBtn.textContent = original;
            copyBtn.classList.remove('is-copied');
        }, 1800);
    } catch (_e) {}
});
