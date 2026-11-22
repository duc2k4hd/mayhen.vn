/**
 * MAYHEN.VN — PAPERLESS POST VIETNAMESE EDITION STUDIO & 3D ENVELOPE JS
 * 100% Pure Vanilla JS • Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    /* =====================================================================
     * 1. PAPERLESS POST 3-PHASE ENVELOPE EXTRACTION ANIMATION & POPUPS
     * ===================================================================== */
    const stage3d = document.getElementById('mayhenEvent3dStage');
    let extractionTimer1 = null;
    let extractionTimer2 = null;

    function playEnvelopeExtractionAnimation() {
        if (!stage3d) return;
        if (extractionTimer1) clearTimeout(extractionTimer1);
        if (extractionTimer2) clearTimeout(extractionTimer2);

        // Pha 1 (00:00 - 00:01): Thiệp nằm gọn trong túi phong bì ở giữa màn hình
        stage3d.classList.remove('is-extracted', 'is-turning-out');
        stage3d.classList.add('is-in-pocket');

        // Pha 2 (00:01 - 00:02): Thiệp xoay nghiêng và rút dần ra khỏi miệng phong bì lót hoa văn
        extractionTimer1 = setTimeout(() => {
            stage3d.classList.remove('is-in-pocket');
            stage3d.classList.add('is-turning-out');
        }, 720);

        // Pha 3 (00:02 - 00:03): Phong bì trượt xuống đáy, tấm thiệp 1 trang đứng thẳng ở chính giữa sân khấu
        extractionTimer2 = setTimeout(() => {
            stage3d.classList.remove('is-turning-out');
            stage3d.classList.add('is-extracted');
        }, 1580);
    }

    // Tự động chạy hiệu ứng rút thiệp khi mở trang xem thử hoặc trang thiệp chính thức
    if (stage3d && !document.getElementById('mayhenEventStudioForm')) {
        playEnvelopeExtractionAnimation();
    }

    document.querySelectorAll('[data-event-toggle-envelope]').forEach((btn) => {
        btn.addEventListener('click', () => {
            playEnvelopeExtractionAnimation();
        });
    });

    // Mở / đóng Popup RSVP & Popup Lì xì VietQR trên sân khấu 1 trang
    const rsvpPopup = document.getElementById('mayhenEventRsvpPopup');
    const giftPopup = document.getElementById('mayhenEventGiftPopup');

    document.querySelectorAll('[data-open-event-popup]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-open-event-popup');
            if (type === 'rsvp' && rsvpPopup) rsvpPopup.classList.add('is-open');
            if (type === 'gift' && giftPopup) giftPopup.classList.add('is-open');
        });
    });

    document.querySelectorAll('[data-close-event-popup]').forEach((btn) => {
        btn.addEventListener('click', () => {
            rsvpPopup?.classList.remove('is-open');
            giftPopup?.classList.remove('is-open');
        });
    });

    /* =====================================================================
     * 2. 5-TAB ADVANCED STUDIO & REALTIME STATIONERY SYNC
     * ===================================================================== */
    const studioForm = document.getElementById('mayhenEventStudioForm');
    const previewRoot = document.getElementById('mayhenEventPreviewRoot');
    if (!studioForm || !previewRoot) return;

    // Chuyển đổi 5 Tab trong Studio
    const tabBtns = document.querySelectorAll('[data-event-tab-btn]');
    const tabPanels = document.querySelectorAll('[data-event-tab-panel]');
    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-event-tab-btn');
            tabBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
            tabPanels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-event-tab-panel') === targetTab));
        });
    });

    function formatVietnameseDateDots(isoDate) {
        const m = String(isoDate || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return isoDate || '';
        return `${m[3]} . ${m[2]} . ${m[1]}`;
    }

    // Đồng bộ trực tiếp từng ô input sang khung thiệp bên phải
    function syncTextTarget(key, val) {
        previewRoot.querySelectorAll(`[data-event-sync="${key}"]`).forEach((target) => {
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
    }

    studioForm.querySelectorAll('[data-event-input]').forEach((inputEl) => {
        inputEl.addEventListener('input', () => {
            const key = inputEl.getAttribute('data-event-input');
            if (key) syncTextTarget(key, inputEl.value);
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

    // Helper thay class theo prefix
    function replacePrefixClass(el, prefix, newVal) {
        if (!el) return;
        const regex = new RegExp(`\\b${prefix}[a-z0-9-]+`, 'g');
        el.className = el.className.replace(regex, '').trim() + ` ${prefix}${newVal}`;
    }

    // 1. Đổi Bảng Màu Chủ Đạo
    const paletteInput = document.getElementById('evtColorPaletteInput');
    document.querySelectorAll('[data-event-palette-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-palette-btn');
            if (!val) return;
            if (paletteInput) paletteInput.value = val;
            document.querySelectorAll('[data-event-palette-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(previewRoot, 'mayhen_event_palette_', val);
        });
    });

    // 2. Đổi Màu Vỏ Phong Bì 3D (Envelope Color)
    const envColorInput = document.getElementById('evtEnvelopeColorInput');
    document.querySelectorAll('[data-event-env-color-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-env-color-btn');
            if (!val) return;
            if (envColorInput) envColorInput.value = val;
            document.querySelectorAll('[data-event-env-color-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(previewRoot, 'mayhen_event_env_', val);
        });
    });

    // 3. Đổi Hoa Văn Lớp Lót Phong Bì (Envelope Liner)
    const envLinerInput = document.getElementById('evtEnvelopeLinerInput');
    const linerEl = document.getElementById('mayhenEventPreviewLiner');
    document.querySelectorAll('[data-event-liner-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-liner-btn');
            if (!val) return;
            if (envLinerInput) envLinerInput.value = val;
            document.querySelectorAll('[data-event-liner-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(linerEl, 'mayhen_event_liner_', val);
        });
    });

    // 4. Đổi Khung Viền Mặt Thiệp (Card Border)
    const borderInput = document.getElementById('evtCardBorderInput');
    const sheetEl = document.getElementById('mayhenEventStationerySheet');
    document.querySelectorAll('[data-event-border-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-border-btn');
            if (!val) return;
            if (borderInput) borderInput.value = val;
            document.querySelectorAll('[data-event-border-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(sheetEl, 'mayhen_event_border_', val);
        });
    });

    // 5. Đổi Phông Nền Sân Khấu (Backdrop)
    const backdropInput = document.getElementById('evtBackdropInput');
    document.querySelectorAll('[data-event-backdrop-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-backdrop-btn');
            if (!val) return;
            if (backdropInput) backdropInput.value = val;
            document.querySelectorAll('[data-event-backdrop-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(previewRoot, 'mayhen_event_backdrop_', val);
        });
    });

    // 6. Đổi Phông Chữ Mặt Thiệp (Font Style)
    const fontInput = document.getElementById('evtFontStyleInput');
    document.querySelectorAll('[data-event-font-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-event-font-btn');
            if (!val) return;
            if (fontInput) fontInput.value = val;
            document.querySelectorAll('[data-event-font-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            replacePrefixClass(previewRoot, 'mayhen_event_font_', val);
        });
    });

    // 7. Tải ảnh riêng lên mặt thiệp (Custom Photo Upload)
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
        });
    }

    // 8. Công tắc Bật/Tắt Thời gian & Địa điểm tiệc (Chuyển giữa Thiệp Mời Tiệc vs Thiệp Chúc Mừng Từ Xa)
    const showVenueCheckbox = document.getElementById('evtShowDatetimeVenue');
    const datetimeSection = document.getElementById('evtPreviewDatetimeSection');
    const venueSection = document.getElementById('evtPreviewVenueSection');
    if (showVenueCheckbox) {
        showVenueCheckbox.addEventListener('change', () => {
            const show = showVenueCheckbox.checked;
            if (datetimeSection) datetimeSection.style.display = show ? 'block' : 'none';
            if (venueSection) venueSection.style.display = show ? 'block' : 'none';
        });
    }

    // 9. Công tắc Bật/Tắt Hộp Mừng Quà / Lì Xì VietQR & RSVP
    const qrCheckbox = document.getElementById('evtEnableGiftQr');
    const qrConfigFields = document.getElementById('evtGiftQrConfigFields');
    const qrPreviewBox = document.getElementById('evtPreviewGiftQrBox');
    if (qrCheckbox) {
        qrCheckbox.addEventListener('change', () => {
            const enabled = qrCheckbox.checked;
            if (qrConfigFields) qrConfigFields.style.display = enabled ? 'block' : 'none';
            if (qrPreviewBox) qrPreviewBox.style.display = enabled ? 'block' : 'none';
        });
    }

    const rsvpCheckbox = document.getElementById('evtEnableRsvp');
    const rsvpPreviewBox = document.getElementById('evtPreviewRsvpBox');
    if (rsvpCheckbox) {
        rsvpCheckbox.addEventListener('change', () => {
            if (rsvpPreviewBox) rsvpPreviewBox.style.display = rsvpCheckbox.checked ? 'block' : 'none';
        });
    }

    /* =====================================================================
     * 3. SAVE DRAFT & PRO-GATED PUBLISH
     * ===================================================================== */
    const draftIdInput = document.getElementById('evtDraftIdInput');
    const btnSaveDraft = document.getElementById('btnEventSaveDraft');
    const btnPublishPro = document.getElementById('btnEventPublishPro');
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
            schedule_items: [
                { time: fd.get('schedule_time_0'), title: fd.get('schedule_title_0') },
                { time: fd.get('schedule_time_1'), title: fd.get('schedule_title_1') },
                { time: fd.get('schedule_time_2'), title: fd.get('schedule_title_2') },
            ],
        };
    }

    if (btnSaveDraft) {
        btnSaveDraft.addEventListener('click', async () => {
            btnSaveDraft.disabled = true;
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
                    showStudioMessage('✓ Đã lưu bản nháp thiết kế thiệp của bạn!');
                } else {
                    showStudioMessage(data.message || 'Vui lòng kiểm tra lại thông tin.');
                }
            } catch (_e) {
                showStudioMessage('Không thể kết nối máy chủ, vui lòng thử lại.');
            } finally {
                btnSaveDraft.disabled = false;
            }
        });
    }

    if (btnPublishPro) {
        btnPublishPro.addEventListener('click', async () => {
            btnPublishPro.disabled = true;
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
                btnPublishPro.disabled = false;
            }
        });
    }

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
