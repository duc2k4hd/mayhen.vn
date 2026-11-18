/**
 * MÂY HẸN — STUDIO VISUAL EDITOR V3 IFRAME CANVAS AGENT
 * Chỉ nạp bên trong Iframe Preview khi ở chế độ Studio Editor (`window.__MAYHEN_IS_STUDIO_EDITOR === true`).
 * Hỗ trợ:
 *  - Click chọn trực tiếp chữ, ảnh, widget dẫn xuất & lớp trang trí (`overlay_*`)
 *  - Sửa chữ inline an toàn tuyệt đối với bộ gõ Tiếng Việt IME (Unikey / EVKey) & dán văn bản thuần
 *  - Kéo, phóng to/thu nhỏ góc, xoay có bắt góc, đường gióng căn giữa & cảnh báo vùng an toàn
 *  - Chuyển đổi tức thì chế độ Chỉnh sửa (`editor`) <-> Xem như khách (`guest_preview`)
 */

import {
    assignDeterministicEditIds,
    cleanSyncKey,
    resolveCanonicalSectionId,
} from '../../common/visual-editor-renderer.js';

const SINGLE_LINE_KEYS = new Set([
    'groom_name',
    'bride_name',
    'groom_father',
    'groom_mother',
    'bride_father',
    'bride_mother',
    'ceremony_type',
    'story_title',
    'story_subtitle',
    'party_time',
    'event_party_time',
    'bride_party_time',
    'ceremony_time',
    'ceremony_groom_time',
    'engagement_time',
    'party_venue',
    'bride_party_venue',
    'ceremony_venue',
    'engagement_venue',
    'wedding_lunar_date',
    'groom_bank_name',
    'groom_bank_account',
    'groom_account_name',
    'groom_bank_holder',
    'groom_bank_owner',
    'bride_bank_name',
    'bride_bank_account',
    'bride_account_name',
    'bride_bank_holder',
    'bride_bank_owner',
    'thank_you_title',
]);

const DERIVED_DATE_KEYS = new Set([
    'wedding_date',
    'wedding_date_digits',
    'wedding_date_full',
    'wedding_day',
    'wedding_month',
    'wedding_year',
    'wedding_weekday',
    'calendar_month_year',
    'wedding_calendar',
    'countdown_day',
    'countdown_hour',
    'countdown_min',
    'countdown_sec',
]);

const DERIVED_COUPLE_KEYS = new Set(['couple_names']);

const SAFE_ZONE_SELECTOR = [
    '.mayhen_invitation_rsvp_form',
    '.mayhen_invitation_rsvp button',
    '.mayhen_invitation_rsvp input',
    '.mayhen_invitation_wish_form',
    '.mayhen_invitation_wishes button',
    '.mayhen_invitation_signature canvas',
    '#mayhenSignatureCanvas',
    '[data-sync-target*="qr_"]',
    '[data-sync-target$="_qr_url"]',
    '.mayhen_invitation_map_btn',
    '.mayhen_invitation_nut_chi_duong',
    '.mayhen_invitation_map_direct_btn',
].join(', ');

function injectEditorCanvasStyles() {
    if (document.getElementById('mayhenV3CanvasAgentStyles')) return;
    const style = document.createElement('style');
    style.id = 'mayhenV3CanvasAgentStyles';
    style.textContent = `
        body.mayhen_studio_v3_editor:not(.mayhen_v3_guest_mode) [data-edit-id],
        body.mayhen_studio_v3_editor:not(.mayhen_v3_guest_mode) .mayhen_invitation_overlay_item {
            cursor: pointer !important;
            pointer-events: auto !important;
        }
        body.mayhen_studio_v3_editor:not(.mayhen_v3_guest_mode) [data-edit-id]:hover {
            outline: 1.5px dashed rgba(197, 160, 89, 0.78) !important;
            outline-offset: 3px !important;
        }
        body.mayhen_studio_v3_editor:not(.mayhen_v3_guest_mode) [data-v3-inline-editing="1"] {
            outline: 2px solid #9E7738 !important;
            outline-offset: 4px !important;
            background: rgba(255, 253, 248, 0.22) !important;
            border-radius: 4px;
            cursor: text !important;
            min-width: 24px;
            min-height: 1em;
        }
        .mayhen_v3_ui_layer {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 999990;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        body.mayhen_v3_guest_mode .mayhen_v3_ui_layer {
            display: none !important;
        }
        .mayhen_v3_selection_box {
            position: fixed;
            border: 1.5px solid #B68B40;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.75), 0 6px 18px rgba(34, 26, 18, 0.14);
            border-radius: 4px;
            pointer-events: none;
            display: none;
            box-sizing: border-box;
        }
        .mayhen_v3_selection_box.is-safe-warning {
            border-color: #C95B2B;
            box-shadow: 0 0 0 2px rgba(201, 91, 43, 0.28);
        }
        .mayhen_v3_handle {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #FFFDF9;
            border: 2px solid #9E7738;
            border-radius: 50%;
            pointer-events: auto;
            box-shadow: 0 2px 6px rgba(0,0,0,0.24);
            box-sizing: border-box;
        }
        .mayhen_v3_handle::after {
            content: '';
            position: absolute;
            inset: -8px;
        }
        .mayhen_v3_handle--nw { top: -7px; left: -7px; cursor: nwse-resize; }
        .mayhen_v3_handle--ne { top: -7px; right: -7px; cursor: nesw-resize; }
        .mayhen_v3_handle--sw { bottom: -7px; left: -7px; cursor: nesw-resize; }
        .mayhen_v3_handle--se { bottom: -7px; right: -7px; cursor: nwse-resize; }
        .mayhen_v3_handle--rot {
            top: -32px;
            left: calc(50% - 10px);
            width: 20px;
            height: 20px;
            cursor: grab;
            background: #B68B40;
            border: 2px solid #FFFDF9;
            color: #FFFDF9;
            font-size: 11px;
            line-height: 16px;
            text-align: center;
            font-weight: 700;
        }
        .mayhen_v3_handle--rot::before {
            content: '↻';
        }
        .mayhen_v3_rot_stem {
            position: absolute;
            top: -14px;
            left: calc(50% - 0.75px);
            width: 1.5px;
            height: 14px;
            background: #B68B40;
            pointer-events: none;
        }
        .mayhen_v3_quick_bar {
            position: fixed;
            display: none;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 3px;
            padding: 5px 8px;
            background: rgba(29, 25, 22, 0.96);
            color: #FAF6F0;
            border: 1px solid rgba(197, 160, 89, 0.5);
            border-radius: 14px;
            box-shadow: 0 10px 26px rgba(18, 14, 10, 0.32);
            pointer-events: auto;
            z-index: 999995;
            max-width: min(356px, calc(100vw - 16px));
            overflow: visible;
            scrollbar-width: none;
            box-sizing: border-box;
        }
        .mayhen_v3_quick_bar::-webkit-scrollbar {
            display: none;
        }
        .mayhen_v3_quick_badge {
            font-size: 11px;
            font-weight: 600;
            color: #E5C687;
            padding: 2px 7px;
            border-right: 1px solid rgba(255,255,255,0.16);
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 440px) {
            .mayhen_v3_quick_badge {
                display: none;
            }
        }
        .mayhen_v3_quick_btn {
            appearance: none;
            border: none;
            background: rgba(255, 255, 255, 0.06);
            color: #FAF6F0;
            font-size: 11.5px;
            font-weight: 600;
            padding: 5px 9px;
            border-radius: 999px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.16s ease, color 0.16s ease;
        }
        .mayhen_v3_quick_btn--accent {
            background: #B68B40;
            color: #FFFDF9;
        }
        .mayhen_v3_quick_btn--accent:hover {
            background: #9E7738 !important;
            color: #FFFDF9 !important;
        }
        .mayhen_v3_quick_btn:hover {
            background: rgba(197, 160, 89, 0.24);
            color: #FFE7B3;
        }
        .mayhen_v3_quick_btn--danger:hover {
            background: rgba(209, 73, 73, 0.3);
            color: #FFC7C7;
        }
        .mayhen_v3_snap_guide {
            position: fixed;
            background: rgba(197, 160, 89, 0.88);
            pointer-events: none;
            display: none;
            z-index: 999989;
        }
        .mayhen_v3_snap_guide--x { width: 1px; top: 0; bottom: 0; }
        .mayhen_v3_snap_guide--y { height: 1px; left: 0; right: 0; }
    `;
    document.head.appendChild(style);
}

function isImageElement(el, syncKey) {
    if (!el) return false;
    if (el.tagName === 'IMG') return true;
    const k = (syncKey || '').toLowerCase();
    if (k.includes('photo') || k.includes('image') || k.includes('cover') || k.includes('gallery') || k.includes('qr') || k.includes('triptych')) {
        return true;
    }
    const bg = window.getComputedStyle(el).backgroundImage;
    return Boolean(bg && bg !== 'none' && bg.includes('url('));
}

function extractCurrentImageUrl(el) {
    if (!el) return '';
    if (el.tagName === 'IMG') return el.getAttribute('src') || '';
    const bg = el.style.backgroundImage || window.getComputedStyle(el).backgroundImage || '';
    const m = bg.match(/url\(["']?([^"')]+)["']?\)/i);
    return m ? m[1] : '';
}

function classifyCanonicalElement(el) {
    const editId = el.getAttribute('data-edit-id') || '';
    const rawSync = el.getAttribute('data-sync-target') || '';
    const syncKey = cleanSyncKey(rawSync);
    const sectionEl = el.closest('[data-mayhen-section], [data-curated-section], .mayhen_invitation_opening, .mayhen_invitation_sr_cua');
    const sectionId = resolveCanonicalSectionId(sectionEl);

    if (DERIVED_DATE_KEYS.has(syncKey)) {
        return {
            kind: 'derived_date',
            editId,
            syncKey,
            ownerKey: 'wedding.wedding_date',
            sectionId,
            label: 'Ngày cưới (Tự động đồng bộ)',
            readOnlyReason: 'derived_from_wedding_date',
        };
    }

    if (DERIVED_COUPLE_KEYS.has(syncKey) || el.querySelector('[data-sync-target="groom_name"], [data-sync-target="bride_name"]')) {
        return {
            kind: 'derived_couple',
            editId,
            syncKey: syncKey || 'couple_names',
            ownerKey: 'wedding.groom_name',
            sectionId,
            label: 'Tên Cô Dâu & Chú Rể',
            readOnlyReason: 'derived_couple_pair',
        };
    }

    if (syncKey === 'dresscode_colors' || el.classList.contains('mayhen_invitation_dresscode_swatches')) {
        return {
            kind: 'swatch_widget',
            editId: editId || `${sectionId}__dresscode_colors__0`,
            syncKey: 'dresscode_colors',
            ownerKey: 'wedding.extra_data.dresscode_colors',
            sectionId,
            label: 'Bảng màu Trang phục',
            readOnlyReason: 'swatch_widget',
        };
    }

    if (el.tagName === 'A' && (syncKey.includes('map') || el.className.includes('map') || el.className.includes('chi_duong'))) {
        return {
            kind: 'business_map',
            editId: editId || `${sectionId}__map_link__0`,
            syncKey: syncKey || 'event_party_map_link',
            ownerKey: `wedding.extra_data.${syncKey || 'event_party_map_link'}`,
            sectionId,
            label: 'Chỉ đường Google Maps',
            href: el.getAttribute('href') || '',
            readOnlyReason: 'interactive_map_link',
        };
    }

    if (isImageElement(el, syncKey)) {
        const rect = el.getBoundingClientRect();
        const aspect = rect.width > 10 && rect.height > 10 ? Number((rect.width / rect.height).toFixed(3)) : 0.75;
        const isQr = syncKey.includes('qr');
        return {
            kind: isQr ? 'business_qr' : 'canonical_image',
            editId,
            syncKey,
            sectionId,
            label: isQr ? 'Mã QR Mừng Cưới' : humanizeSyncKey(syncKey),
            imageUrl: extractCurrentImageUrl(el),
            slotWidth: Math.round(rect.width),
            slotHeight: Math.round(rect.height),
            slotAspect: aspect,
        };
    }

    const isSingleLine = SINGLE_LINE_KEYS.has(syncKey) || syncKey.endsWith('.time') || syncKey.endsWith('.title');
    const cs = window.getComputedStyle(el);

    return {
        kind: 'canonical_text',
        editId,
        syncKey,
        sectionId,
        label: humanizeSyncKey(syncKey),
        text: (el.textContent || '').trim(),
        multiline: !isSingleLine,
        computedStyle: {
            fontSizePx: Math.round(parseFloat(cs.fontSize) || 16),
            fontWeight: parseInt(cs.fontWeight, 10) || 400,
            fontStyle: cs.fontStyle || 'normal',
            lineHeight: cs.lineHeight === 'normal' ? 1.4 : Number(((parseFloat(cs.lineHeight) || 22) / (parseFloat(cs.fontSize) || 16)).toFixed(2)),
            textAlign: cs.textAlign || 'center',
            color: rgbToHex(cs.color),
        },
    };
}

function humanizeSyncKey(key) {
    const labels = {
        groom_name: 'Tên Chú Rể',
        bride_name: 'Tên Cô Dâu',
        ceremony_type: 'Tiêu đề Lễ Cưới',
        slogan: 'Câu trích dẫn mở đầu',
        story_title: 'Tiêu đề Chuyện Tình',
        story_subtitle: 'Phụ đề Chuyện Tình',
        couple_quote: 'Lời Ngỏ Tình Yêu',
        story_content: 'Nội dung Chuyện Tình',
        groom_father: 'Ông (Nhà Trai)',
        groom_mother: 'Bà (Nhà Trai)',
        groom_parents_address: 'Địa chỉ Nhà Trai',
        bride_father: 'Ông (Nhà Gái)',
        bride_mother: 'Bà (Nhà Gái)',
        bride_parents_address: 'Địa chỉ Nhà Gái',
        groom_bio: 'Đôi nét về Chú Rể',
        bride_bio: 'Đôi nét về Cô Dâu',
        party_time: 'Giờ Tiệc Cưới',
        party_venue: 'Tên Địa Điểm Tiệc',
        event_party_address: 'Địa chỉ Sảnh Tiệc',
        wedding_lunar_date: 'Ngày Âm Lịch',
        ceremony_time: 'Giờ Lễ Nhà Gái',
        ceremony_address: 'Địa chỉ Lễ Nhà Gái',
        ceremony_groom_time: 'Giờ Lễ Nhà Trai',
        ceremony_groom_address: 'Địa chỉ Lễ Nhà Trai',
        dresscode_note: 'Ghi chú Trang Phục',
        thank_you_title: 'Tiêu đề Cảm Ơn',
        thank_you_quote: 'Lời Cảm Ơn Kết Thiệp',
        cover_photo_url: 'Ảnh Bìa Chính (Hero)',
        hero_photo_url: 'Ảnh Bìa Chính (Hero)',
        save_date_photo_url: 'Ảnh Kỷ Niệm / Save The Date',
        groom_photo_url: 'Ảnh Chân Dung Chú Rể',
        bride_photo_url: 'Ảnh Chân Dung Cô Dâu',
        footer_photo_url: 'Ảnh Kết Thiệp',
        ceremony_photo_url: 'Ảnh Lễ Thành Hôn',
        vinyl_photo_url: 'Ảnh Đĩa Nhạc Kỷ Niệm',
    };
    if (labels[key]) return labels[key];
    if (key.startsWith('gallery.')) {
        const idx = Number(key.split('.')[1] || 0) + 1;
        return `Ảnh Album #${idx}`;
    }
    if (key.startsWith('timeline.')) {
        const parts = key.split('.');
        const idx = Number(parts[1] || 0) + 1;
        return parts[2] === 'time' ? `Mốc giờ #${idx}` : `Nội dung mốc #${idx}`;
    }
    return key.replace(/_/g, ' ');
}

function rgbToHex(rgb) {
    if (!rgb) return '#2B2523';
    if (rgb.startsWith('#')) return rgb;
    const m = rgb.match(/\d+/g);
    if (!m || m.length < 3) return '#2B2523';
    return '#' + m.slice(0, 3).map((n) => Number(n).toString(16).padStart(2, '0')).join('').toUpperCase();
}

function postToStudio(type, payload = {}) {
    if (!window.parent || window.parent === window) return;
    window.parent.postMessage({
        protocol: 'MAYHEN_STUDIO_V3',
        version: 3,
        type,
        ...payload,
    }, window.location.origin);
}

export function initIframeCanvasAgent() {
    if (window.__MAYHEN_V3_AGENT_ACTIVE) return;
    window.__MAYHEN_V3_AGENT_ACTIVE = true;

    injectEditorCanvasStyles();
    document.body.classList.add('mayhen_studio_v3_editor');
    assignDeterministicEditIds(document);

    let guestPreviewMode = false;
    let selectedEl = null;
    let selectedDescriptor = null;
    let activeInlineEl = null;
    let isComposingIME = false;
    let pointerSession = null;

    // Build fixed UI chrome overlay inside iframe (selection ring, handles, floating toolbar, snap guides)
    const uiLayer = document.createElement('div');
    uiLayer.className = 'mayhen_v3_ui_layer';
    uiLayer.innerHTML = `
        <div class="mayhen_v3_snap_guide mayhen_v3_snap_guide--x" id="mayhenV3SnapX"></div>
        <div class="mayhen_v3_snap_guide mayhen_v3_snap_guide--y" id="mayhenV3SnapY"></div>
        <div class="mayhen_v3_selection_box" id="mayhenV3SelectionBox">
            <div class="mayhen_v3_rot_stem" data-v3-overlay-only="1"></div>
            <div class="mayhen_v3_handle mayhen_v3_handle--rot" data-v3-handle="rot" data-v3-overlay-only="1" title="Xoay phần tử"></div>
            <div class="mayhen_v3_handle mayhen_v3_handle--nw" data-v3-handle="nw" data-v3-overlay-only="1"></div>
            <div class="mayhen_v3_handle mayhen_v3_handle--ne" data-v3-handle="ne" data-v3-overlay-only="1"></div>
            <div class="mayhen_v3_handle mayhen_v3_handle--sw" data-v3-handle="sw" data-v3-overlay-only="1"></div>
            <div class="mayhen_v3_handle mayhen_v3_handle--se" data-v3-handle="se" data-v3-overlay-only="1"></div>
        </div>
        <div class="mayhen_v3_quick_bar" id="mayhenV3QuickBar" role="toolbar" aria-label="Thanh công cụ nhanh"></div>
    `;
    document.body.appendChild(uiLayer);

    const selectionBox = uiLayer.querySelector('#mayhenV3SelectionBox');
    const quickBar = uiLayer.querySelector('#mayhenV3QuickBar');
    const snapX = uiLayer.querySelector('#mayhenV3SnapX');
    const snapY = uiLayer.querySelector('#mayhenV3SnapY');

    function setOverlayHandlesVisible(visible) {
        selectionBox.querySelectorAll('[data-v3-overlay-only="1"]').forEach((node) => {
            node.style.display = visible ? 'block' : 'none';
        });
    }

    function checkSafeZoneOverlap(el) {
        if (!el || !el.hasAttribute('data-overlay-id')) return false;
        const r1 = el.getBoundingClientRect();
        if (r1.width <= 0 || r1.height <= 0) return false;
        const protectedNodes = document.querySelectorAll(SAFE_ZONE_SELECTOR);
        for (const node of protectedNodes) {
            const r2 = node.getBoundingClientRect();
            if (r2.width <= 0 || r2.height <= 0) continue;
            const overlapX = Math.max(0, Math.min(r1.right, r2.right) - Math.max(r1.left, r2.left));
            const overlapY = Math.max(0, Math.min(r1.bottom, r2.bottom) - Math.max(r1.top, r2.top));
            if (overlapX > 6 && overlapY > 6) {
                return true;
            }
        }
        return false;
    }

    function updateSelectionGeometry() {
        if (selectedEl && !selectedEl.isConnected && selectedDescriptor) {
            selectedEl = selectedDescriptor.isOverlay && selectedDescriptor.overlayId
                ? document.querySelector(`[data-overlay-id="${selectedDescriptor.overlayId}"]`)
                : (selectedDescriptor.editId ? document.querySelector(`[data-edit-id="${selectedDescriptor.editId}"]`) : null);
        }

        if (!selectedEl || !selectedEl.isConnected || guestPreviewMode) {
            selectionBox.style.display = 'none';
            quickBar.style.display = 'none';
            return;
        }

        const rect = selectedEl.getBoundingClientRect();
        if (rect.width < 2 && rect.height < 2) {
            selectionBox.style.display = 'none';
            quickBar.style.display = 'none';
            return;
        }

        const isOverlay = Boolean(selectedDescriptor?.isOverlay);
        const isLocked = selectedEl.getAttribute('data-ov-locked') === '1';
        const safeWarning = isOverlay && checkSafeZoneOverlap(selectedEl);

        selectionBox.style.display = 'block';
        selectionBox.style.left = `${Math.round(rect.left - 3)}px`;
        selectionBox.style.top = `${Math.round(rect.top - 3)}px`;
        selectionBox.style.width = `${Math.round(rect.width + 6)}px`;
        selectionBox.style.height = `${Math.round(rect.height + 6)}px`;
        selectionBox.classList.toggle('is-safe-warning', safeWarning);
        setOverlayHandlesVisible(isOverlay && !isLocked && !activeInlineEl);

        if (activeInlineEl) {
            quickBar.style.display = 'none';
            return;
        }

        quickBar.style.display = 'flex';
        quickBar.style.left = '8px';
        const qbRect = quickBar.getBoundingClientRect();
        const vw = window.innerWidth || 390;
        const vh = window.innerHeight || 720;
        const desiredLeft = rect.left + (rect.width / 2) - (qbRect.width / 2);
        const clampedLeft = Math.max(8, Math.min(vw - qbRect.width - 8, desiredLeft));
        const topClearance = isOverlay ? 42 : 12;
        const topAbove = rect.top - qbRect.height - topClearance;
        const bottomBelow = rect.bottom + 12;

        let finalTop;
        if (topAbove >= 8) {
            finalTop = topAbove;
        } else if (bottomBelow + qbRect.height <= vh - 8) {
            finalTop = bottomBelow;
        } else {
            // Element spans most of the viewport: dock cleanly inside visible area without touching rotation knob
            const minSafeTop = isOverlay && rect.top > -28 ? Math.max(12, Math.round(rect.top + 14)) : 12;
            finalTop = Math.max(12, Math.min(vh - qbRect.height - 12, minSafeTop));
        }

        quickBar.style.left = `${Math.round(clampedLeft)}px`;
        quickBar.style.top = `${Math.round(finalTop)}px`;
    }

    function renderQuickBarActions(desc) {
        if (!desc) {
            quickBar.innerHTML = '';
            return;
        }

        const buttons = [];
        if (desc.isOverlay) {
            if (desc.overlayType === 'overlay_text') {
                buttons.push({ action: 'inline-edit', label: '✎ Sửa chữ', accent: true });
            } else if (desc.overlayType === 'overlay_frame' || desc.overlayType === 'overlay_image') {
                buttons.push(
                    { action: 'replace-overlay-image', label: desc.hasOverlayImage ? 'Đổi ảnh' : '＋ Chọn ảnh', accent: true },
                    ...(desc.hasOverlayImage ? [{ action: 'crop-overlay-image', label: 'Cắt khung' }] : [])
                );
            }
            buttons.push(
                { action: 'center-overlay', label: 'Căn giữa' },
                { action: 'duplicate-overlay', label: 'Nhân bản' },
                { action: 'focus-motion', label: 'Hiệu ứng' },
                { action: 'delete-overlay', label: 'Xóa', danger: true }
            );
        } else if (desc.kind === 'canonical_text') {
            buttons.push(
                { action: 'inline-edit', label: '✎ Sửa chữ', accent: true },
                { action: 'focus-typography', label: 'Kiểu chữ' },
                { action: 'focus-motion', label: 'Hiệu ứng' },
                { action: 'reset-instance', label: 'Đặt lại' }
            );
        } else if (desc.kind === 'canonical_image' || desc.kind === 'business_qr') {
            buttons.push(
                { action: 'replace-image', label: 'Thay ảnh', accent: true },
                { action: 'crop-image', label: 'Cắt khung' },
                { action: 'focus-focal', label: 'Tiêu cự' },
                { action: 'focus-motion', label: 'Hiệu ứng' }
            );
        } else {
            buttons.push({ action: 'focus-inspector', label: 'Mở thiết lập gốc' });
        }

        quickBar.innerHTML = '';
        const badge = document.createElement('span');
        badge.className = 'mayhen_v3_quick_badge';
        badge.textContent = desc.label || 'Phần tử';
        quickBar.appendChild(badge);

        buttons.forEach((b) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mayhen_v3_quick_btn'
                + (b.accent ? ' mayhen_v3_quick_btn--accent' : '')
                + (b.danger ? ' mayhen_v3_quick_btn--danger' : '');
            btn.setAttribute('data-v3-quick-action', b.action);
            btn.textContent = b.label;
            quickBar.appendChild(btn);
        });
    }

    function selectElement(el, notifyParent = true) {
        if (activeInlineEl && activeInlineEl !== el) {
            commitInlineEditing();
        }

        if (!el) {
            selectedEl = null;
            selectedDescriptor = null;
            updateSelectionGeometry();
            if (notifyParent) postToStudio('MAYHEN_V3_TARGET_CLEARED');
            return;
        }

        assignDeterministicEditIds(document);
        selectedEl = el;

        if (el.hasAttribute('data-overlay-id')) {
            const ovId = el.getAttribute('data-overlay-id');
            const ovType = el.getAttribute('data-overlay-type') || 'overlay_sticker';
            const secId = el.getAttribute('data-overlay-section') || 'hero';
            const safeWarning = checkSafeZoneOverlap(el);
            const rect = el.getBoundingClientRect();
            const hasOverlayImage = Boolean(el.querySelector('.mayhen_invitation_overlay_frame_box img'));
            selectedDescriptor = {
                kind: ovType,
                isOverlay: true,
                overlayId: ovId,
                overlayType: ovType,
                sectionId: secId,
                hasOverlayImage,
                label: ovType === 'overlay_text' ? 'Chữ trang trí'
                    : ovType === 'overlay_frame' ? 'Khung ảnh nghệ thuật'
                    : ovType === 'overlay_shape' ? 'Hình khối trang trí'
                    : ovType === 'overlay_image' ? 'Ảnh trang trí'
                    : 'Sticker trang trí',
                safeZoneWarning: safeWarning,
                slotAspect: rect.width > 10 && rect.height > 10 ? Number((rect.width / rect.height).toFixed(3)) : 0.75,
            };
        } else {
            selectedDescriptor = classifyCanonicalElement(el);
        }

        renderQuickBarActions(selectedDescriptor);
        updateSelectionGeometry();

        if (notifyParent) {
            postToStudio('MAYHEN_V3_TARGET_SELECTED', {
                target: selectedDescriptor,
            });
        }
    }

    // ---- SAFE VIETNAMESE IME INLINE TEXT EDITING ----
    function startInlineEditing(targetEl) {
        if (!targetEl || guestPreviewMode) return;
        let editableNode = targetEl;
        let isOverlayText = false;
        let overlayId = null;

        if (targetEl.hasAttribute('data-overlay-id')) {
            if (targetEl.getAttribute('data-overlay-type') !== 'overlay_text') return;
            if (targetEl.getAttribute('data-ov-locked') === '1') return;
            editableNode = targetEl.querySelector('.mayhen_invitation_overlay_text_box') || targetEl;
            isOverlayText = true;
            overlayId = targetEl.getAttribute('data-overlay-id');
        } else if (selectedDescriptor?.kind !== 'canonical_text') {
            return;
        }

        if (activeInlineEl === editableNode) return;
        if (activeInlineEl) commitInlineEditing();

        activeInlineEl = editableNode;
        activeInlineEl._v3Meta = {
            isOverlayText,
            overlayId,
            syncKey: selectedDescriptor?.syncKey || '',
            editId: selectedDescriptor?.editId || '',
            multiline: isOverlayText ? true : Boolean(selectedDescriptor?.multiline),
            initialText: editableNode.textContent || '',
        };

        editableNode.setAttribute('contenteditable', 'true');
        editableNode.setAttribute('spellcheck', 'false');
        editableNode.setAttribute('data-v3-inline-editing', '1');
        window.__MAYHEN_ACTIVE_INLINE_EL = editableNode;
        updateSelectionGeometry();

        editableNode.focus({ preventScroll: true });
        try {
            const sel = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(editableNode);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
        } catch (e) {}
    }

    function sanitizeInlinePlainText(rawText, multiline) {
        const normalized = String(rawText || '').replace(/\r\n|\r/g, '\n').replace(/\u00A0/g, ' ');
        if (!multiline) {
            return normalized.replace(/\n+/g, ' ').replace(/\s{2,}/g, ' ');
        }
        return normalized;
    }

    function emitInlineTextUpdate(isFinalCommit = false) {
        if (!activeInlineEl || !activeInlineEl._v3Meta) return;
        const meta = activeInlineEl._v3Meta;
        const raw = activeInlineEl.innerText !== undefined ? activeInlineEl.innerText : (activeInlineEl.textContent || '');
        const clean = sanitizeInlinePlainText(raw, meta.multiline);

        if (meta.isOverlayText) {
            postToStudio(isFinalCommit ? 'MAYHEN_V3_OVERLAY_TEXT_COMMIT' : 'MAYHEN_V3_OVERLAY_TEXT_INPUT', {
                overlay_id: meta.overlayId,
                text: isFinalCommit ? clean.trim() : clean,
            });
        } else if (meta.syncKey) {
            // Update sibling occurrences of the same syncKey WITHOUT touching activeInlineEl
            document.querySelectorAll(`[data-sync-target="${meta.syncKey}"]`).forEach((peer) => {
                if (peer !== activeInlineEl && !peer.querySelector('[data-sync-target]')) {
                    peer.textContent = isFinalCommit ? clean.trim() : clean;
                }
            });
            postToStudio(isFinalCommit ? 'MAYHEN_V3_INLINE_TEXT_COMMIT' : 'MAYHEN_V3_INLINE_TEXT_INPUT', {
                sync_key: meta.syncKey,
                edit_id: meta.editId,
                value: isFinalCommit ? clean.trim() : clean,
            });
        }
    }

    function commitInlineEditing(cancelChanges = false) {
        if (!activeInlineEl) return;
        const node = activeInlineEl;
        const meta = node._v3Meta;

        if (cancelChanges && meta) {
            node.textContent = meta.initialText;
        } else {
            emitInlineTextUpdate(true);
        }

        node.removeAttribute('contenteditable');
        node.removeAttribute('data-v3-inline-editing');
        delete node._v3Meta;
        activeInlineEl = null;
        isComposingIME = false;
        window.__MAYHEN_ACTIVE_INLINE_EL = null;
        updateSelectionGeometry();
    }

    document.addEventListener('compositionstart', (e) => {
        if (activeInlineEl && activeInlineEl.contains(e.target)) {
            isComposingIME = true;
        }
    }, true);

    document.addEventListener('compositionend', (e) => {
        if (activeInlineEl && activeInlineEl.contains(e.target)) {
            isComposingIME = false;
            emitInlineTextUpdate(false);
        }
    }, true);

    document.addEventListener('input', (e) => {
        if (!activeInlineEl || !activeInlineEl.contains(e.target)) return;
        if (isComposingIME || e.isComposing) return;
        emitInlineTextUpdate(false);
    }, true);

    document.addEventListener('paste', (e) => {
        if (!activeInlineEl || !activeInlineEl.contains(e.target)) return;
        e.preventDefault();
        const clip = (e.clipboardData || window.clipboardData)?.getData('text/plain') || '';
        const meta = activeInlineEl._v3Meta || { multiline: true };
        const clean = sanitizeInlinePlainText(clip, meta.multiline);

        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            const textNode = document.createTextNode(clean);
            range.insertNode(textNode);
            range.setStartAfter(textNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        } else {
            activeInlineEl.textContent = (activeInlineEl.textContent || '') + clean;
        }
        emitInlineTextUpdate(false);
    }, true);

    document.addEventListener('focusout', (e) => {
        if (activeInlineEl && e.target === activeInlineEl) {
            setTimeout(() => {
                if (activeInlineEl && document.activeElement !== activeInlineEl) {
                    commitInlineEditing(false);
                }
            }, 60);
        }
    }, true);

    // ---- POINTER EVENTS DRAG / RESIZE / ROTATE FOR OVERLAYS ----
    function beginOverlayTransform(mode, overlayEl, e) {
        if (!overlayEl || overlayEl.getAttribute('data-ov-locked') === '1') return;
        const host = overlayEl.closest('[data-mayhen-section], [data-curated-section]');
        if (!host) return;
        const hostRect = host.getBoundingClientRect();
        if (hostRect.width < 20 || hostRect.height < 20) return;

        const isDesktopOverrideActive = window.innerWidth >= 900 && overlayEl.getAttribute('data-ov-desktop') === '1';
        const readVar = (desktopProp, baseProp, fallback) => {
            const raw = overlayEl.style.getPropertyValue(isDesktopOverrideActive ? desktopProp : baseProp)
                || overlayEl.style.getPropertyValue(baseProp);
            const num = parseFloat(raw);
            return Number.isFinite(num) ? num : fallback;
        };

        const startX = readVar('--mh-ov-dx', '--mh-ov-x', 0.5);
        const startY = readVar('--mh-ov-dy', '--mh-ov-y', 0.5);
        const startW = readVar('--mh-ov-dw', '--mh-ov-w', 0.28);
        const startH = readVar('--mh-ov-dh', '--mh-ov-h', 0.18);
        const startAspect = readVar('--mh-ov-daspect', '--mh-ov-aspect', startW / Math.max(0.03, startH));
        const startRot = parseFloat(overlayEl.style.getPropertyValue('--mh-ov-rot')) || 0;
        const elRect = overlayEl.getBoundingClientRect();
        const startPxW = Math.max(28, elRect.width || (hostRect.width * startW));
        const startPxH = Math.max(22, elRect.height || (startPxW / Math.max(0.15, startAspect)));
        const centerX = elRect.left + elRect.width / 2;
        const centerY = elRect.top + elRect.height / 2;

        pointerSession = {
            mode,
            pointerId: e.pointerId,
            overlayEl,
            overlayId: overlayEl.getAttribute('data-overlay-id'),
            overlayType: overlayEl.getAttribute('data-overlay-type'),
            hostRect,
            startClientX: e.clientX,
            startClientY: e.clientY,
            startX,
            startY,
            startW,
            startH,
            startAspect: startPxW / Math.max(12, startPxH),
            startPxW,
            startPxH,
            startRot,
            centerX,
            centerY,
            moved: false,
            current: { x: startX, y: startY, w: startW, h: startH, aspect: Number((startPxW / Math.max(12, startPxH)).toFixed(4)), rotation_deg: startRot },
        };

        try {
            e.target.setPointerCapture?.(e.pointerId);
        } catch (err) {}
    }

    window.addEventListener('pointermove', (e) => {
        if (!pointerSession || e.pointerId !== pointerSession.pointerId) return;
        const s = pointerSession;
        const dx = e.clientX - s.startClientX;
        const dy = e.clientY - s.startClientY;
        if (!s.moved && Math.hypot(dx, dy) < 3) return;
        s.moved = true;

        if (s.mode === 'drag') {
            let nx = Math.max(0.03, Math.min(0.97, s.startX + (dx / s.hostRect.width)));
            let ny = Math.max(0.03, Math.min(0.97, s.startY + (dy / s.hostRect.height)));

            if (Math.abs(nx - 0.5) < 0.018) {
                nx = 0.5;
                snapX.style.display = 'block';
                snapX.style.left = `${Math.round(s.hostRect.left + s.hostRect.width * 0.5)}px`;
            } else {
                snapX.style.display = 'none';
            }

            if (Math.abs(ny - 0.5) < 0.018) {
                ny = 0.5;
                snapY.style.display = 'block';
                snapY.style.top = `${Math.round(s.hostRect.top + s.hostRect.height * 0.5)}px`;
            } else {
                snapY.style.display = 'none';
            }

            s.current.x = Number(nx.toFixed(4));
            s.current.y = Number(ny.toFixed(4));
            s.overlayEl.style.setProperty('--mh-ov-x', String(s.current.x));
            s.overlayEl.style.setProperty('--mh-ov-y', String(s.current.y));
            if (window.innerWidth >= 900 && s.overlayEl.getAttribute('data-ov-desktop') === '1') {
                s.overlayEl.style.setProperty('--mh-ov-dx', String(s.current.x));
                s.overlayEl.style.setProperty('--mh-ov-dy', String(s.current.y));
            }
        } else if (s.mode === 'rot') {
            const angleRad = Math.atan2(e.clientY - s.centerY, e.clientX - s.centerX);
            let deg = Math.round((angleRad * 180 / Math.PI) + 90);
            if (deg > 180) deg -= 360;
            const snapAngles = [-180, -135, -90, -45, -15, 0, 15, 45, 90, 135, 180];
            for (const sa of snapAngles) {
                if (Math.abs(deg - sa) <= 4) {
                    deg = sa;
                    break;
                }
            }
            s.current.rotation_deg = deg;
            s.overlayEl.style.setProperty('--mh-ov-rot', `${deg}deg`);
        } else {
            // Corner resize (`nw`, `ne`, `sw`, `se`) using true pixel geometry so tall sections never distort aspect ratio
            const signX = s.mode.includes('e') ? 1 : -1;
            const signY = s.mode.includes('s') ? 1 : -1;
            const deltaW = signX * dx * 2;
            const deltaH = signY * dy * 2;

            let nextPxW;
            let nextAspect = s.startAspect;

            if (s.overlayType === 'overlay_text') {
                nextPxW = Math.max(72, Math.min(s.hostRect.width * 0.95, s.startPxW + deltaW));
            } else if (e.altKey) {
                nextPxW = Math.max(40, Math.min(s.hostRect.width * 0.95, s.startPxW + deltaW));
                const nextPxH = Math.max(24, Math.min(s.hostRect.height * 0.90, s.startPxH + deltaH));
                nextAspect = Math.max(0.2, Math.min(10, nextPxW / nextPxH));
            } else {
                const scaleX = (s.startPxW + deltaW) / s.startPxW;
                const scaleY = (s.startPxH + deltaH) / s.startPxH;
                const scale = Math.max(0.18, (scaleX + scaleY) / 2);
                nextPxW = Math.max(40, Math.min(s.hostRect.width * 0.95, s.startPxW * scale));
            }

            const nw = Math.max(0.08, Math.min(0.95, nextPxW / s.hostRect.width));
            const nh = Math.max(0.03, Math.min(0.95, nw / Math.max(0.15, nextAspect)));

            s.current.w = Number(nw.toFixed(4));
            s.current.h = Number(nh.toFixed(4));
            s.current.aspect = Number(nextAspect.toFixed(4));
            s.overlayEl.style.setProperty('--mh-ov-w', String(s.current.w));
            s.overlayEl.style.setProperty('--mh-ov-h', String(s.current.h));
            s.overlayEl.style.setProperty('--mh-ov-aspect', String(s.current.aspect));
            if (window.innerWidth >= 900 && s.overlayEl.getAttribute('data-ov-desktop') === '1') {
                s.overlayEl.style.setProperty('--mh-ov-dw', String(s.current.w));
                s.overlayEl.style.setProperty('--mh-ov-dh', String(s.current.h));
                s.overlayEl.style.setProperty('--mh-ov-daspect', String(s.current.aspect));
            }
        }

        updateSelectionGeometry();
    });

    function finishPointerSession() {
        if (!pointerSession) return;
        const s = pointerSession;
        pointerSession = null;
        snapX.style.display = 'none';
        snapY.style.display = 'none';

        if (s.moved) {
            const safeWarning = checkSafeZoneOverlap(s.overlayEl);
            postToStudio('MAYHEN_V3_OVERLAY_TRANSFORM_COMMIT', {
                overlay_id: s.overlayId,
                patch: {
                    x: s.current.x,
                    y: s.current.y,
                    w: s.current.w,
                    h: s.current.h,
                    aspect: s.current.aspect,
                    rotation_deg: s.current.rotation_deg,
                },
                is_desktop_viewport: window.innerWidth >= 900,
                safe_zone_warning: safeWarning,
            });
            updateSelectionGeometry();
        }
    }

    window.addEventListener('pointerup', finishPointerSession);
    window.addEventListener('pointercancel', finishPointerSession);

    // Handle quickbar button clicks & resize/rotate handle pointerdowns
    quickBar.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-v3-quick-action]');
        if (!btn || !selectedEl) return;
        e.preventDefault();
        e.stopPropagation();
        const act = btn.getAttribute('data-v3-quick-action');
        if (act === 'inline-edit') {
            startInlineEditing(selectedEl);
            return;
        }
        postToStudio('MAYHEN_V3_QUICK_ACTION', {
            action: act,
            target: selectedDescriptor,
        });
    });

    selectionBox.addEventListener('pointerdown', (e) => {
        const handle = e.target.closest('[data-v3-handle]');
        if (!handle || !selectedEl || !selectedDescriptor?.isOverlay) return;
        e.preventDefault();
        e.stopPropagation();
        beginOverlayTransform(handle.getAttribute('data-v3-handle'), selectedEl, e);
    });

    // ---- PRIMARY CLICK, DOUBLE-CLICK & KEYBOARD INTERCEPTION IN EDITOR MODE ----
    document.addEventListener('pointerdown', (e) => {
        if (guestPreviewMode) return;
        if (uiLayer.contains(e.target)) return;
        if (e.target.closest('[data-v3-pick-overlay-image]')) return;
        const ovEl = e.target.closest('.mayhen_invitation_overlay_item');
        if (ovEl && !activeInlineEl) {
            selectElement(ovEl, true);
            beginOverlayTransform('drag', ovEl, e);
        }
    }, true);

    document.addEventListener('click', (e) => {
        if (guestPreviewMode) return;
        if (uiLayer.contains(e.target)) return;
        if (activeInlineEl && activeInlineEl.contains(e.target)) return;

        // 0. Direct 1-click "＋ Chọn ảnh" button inside an empty Photo Frame / Overlay Image
        const emptyPickBtn = e.target.closest('[data-v3-pick-overlay-image]');
        if (emptyPickBtn) {
            const ovEl = emptyPickBtn.closest('.mayhen_invitation_overlay_item');
            if (ovEl) {
                e.preventDefault();
                e.stopPropagation();
                selectElement(ovEl, true);
                postToStudio('MAYHEN_V3_QUICK_ACTION', {
                    action: 'replace-overlay-image',
                    target: selectedDescriptor,
                });
                return;
            }
        }

        // 1. Overlay target
        const ovEl = e.target.closest('.mayhen_invitation_overlay_item');
        if (ovEl) {
            e.preventDefault();
            e.stopPropagation();
            selectElement(ovEl, true);
            return;
        }

        // 2. Canonical [data-edit-id] target (prefer deepest leaf so spans inside wrappers win!)
        const editTarget = e.target.closest('[data-edit-id]');
        if (editTarget) {
            e.preventDefault();
            e.stopPropagation();
            selectElement(editTarget, true);
            return;
        }

        // 3. Countdown / Calendar / Map / Guest interactive forms protection
        const countdownEl = e.target.closest('.mayhen_invitation_countdown, [data-countdown]');
        if (countdownEl) {
            e.preventDefault();
            e.stopPropagation();
            selectedEl = countdownEl;
            selectedDescriptor = {
                kind: 'derived_date',
                editId: 'countdown_calendar__countdown__0',
                syncKey: 'wedding_date',
                ownerKey: 'wedding.wedding_date',
                sectionId: 'countdown_calendar',
                label: 'Đồng hồ đếm ngược (Từ Ngày cưới)',
                readOnlyReason: 'derived_from_wedding_date',
            };
            renderQuickBarActions(selectedDescriptor);
            updateSelectionGeometry();
            postToStudio('MAYHEN_V3_TARGET_SELECTED', { target: selectedDescriptor });
            return;
        }

        const safeInteractive = e.target.closest('.mayhen_invitation_rsvp, .mayhen_invitation_wishes, .mayhen_invitation_signature');
        if (safeInteractive) {
            if (e.target.closest('button, input, textarea, select, canvas, a')) {
                e.preventDefault();
                e.stopPropagation();
            }
            selectedEl = safeInteractive;
            selectedDescriptor = {
                kind: 'interactive_safe_zone',
                editId: 'ending__interactive__0',
                syncKey: 'interactive',
                sectionId: 'ending',
                label: 'Khu tương tác Khách mời (Bật "Xem như khách" để thử)',
                readOnlyReason: 'guest_interactive_form',
            };
            renderQuickBarActions(selectedDescriptor);
            updateSelectionGeometry();
            postToStudio('MAYHEN_V3_TARGET_SELECTED', { target: selectedDescriptor });
            return;
        }

        const anyLink = e.target.closest('a[href]');
        if (anyLink) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Click on empty background clears selection
        selectElement(null, true);
    }, true);

    document.addEventListener('submit', (e) => {
        if (!guestPreviewMode) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    document.addEventListener('dblclick', (e) => {
        if (guestPreviewMode) return;
        if (uiLayer.contains(e.target)) return;
        const ovEl = e.target.closest('.mayhen_invitation_overlay_item');
        if (ovEl) {
            const ovType = ovEl.getAttribute('data-overlay-type');
            if (ovType === 'overlay_text') {
                e.preventDefault();
                e.stopPropagation();
                selectElement(ovEl, true);
                startInlineEditing(ovEl);
                return;
            }
            if (ovType === 'overlay_frame' || ovType === 'overlay_image') {
                e.preventDefault();
                e.stopPropagation();
                selectElement(ovEl, true);
                postToStudio('MAYHEN_V3_QUICK_ACTION', {
                    action: 'replace-overlay-image',
                    target: selectedDescriptor,
                });
                return;
            }
        }
        const editTarget = e.target.closest('[data-edit-id]');
        if (editTarget) {
            selectElement(editTarget, true);
            if (selectedDescriptor?.kind === 'canonical_text') {
                e.preventDefault();
                e.stopPropagation();
                startInlineEditing(editTarget);
            } else if (selectedDescriptor?.kind === 'canonical_image' || selectedDescriptor?.kind === 'business_qr') {
                e.preventDefault();
                e.stopPropagation();
                postToStudio('MAYHEN_V3_QUICK_ACTION', {
                    action: 'replace-image',
                    target: selectedDescriptor,
                });
            }
        }
    }, true);

    document.addEventListener('keydown', (e) => {
        if (guestPreviewMode) return;

        if (activeInlineEl) {
            const meta = activeInlineEl._v3Meta || { multiline: true };
            if (e.key === 'Escape') {
                e.preventDefault();
                commitInlineEditing(false);
            } else if (e.key === 'Enter' && !meta.multiline && !isComposingIME && !e.isComposing) {
                e.preventDefault();
                commitInlineEditing(false);
            }
            return;
        }

        // Undo / Redo shortcuts inside iframe forward cleanly to Parent Studio
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
            e.preventDefault();
            postToStudio('MAYHEN_V3_SHORTCUT', { command: e.shiftKey ? 'redo' : 'undo' });
            return;
        }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
            e.preventDefault();
            postToStudio('MAYHEN_V3_SHORTCUT', { command: 'redo' });
            return;
        }

        if (!selectedEl || !selectedDescriptor) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            selectElement(null, true);
            return;
        }

        if (e.key === 'Enter' && (selectedDescriptor.kind === 'canonical_text' || selectedDescriptor.overlayType === 'overlay_text')) {
            e.preventDefault();
            startInlineEditing(selectedEl);
            return;
        }

        if (e.key === 'Delete' || e.key === 'Backspace') {
            e.preventDefault();
            postToStudio('MAYHEN_V3_QUICK_ACTION', {
                action: selectedDescriptor.isOverlay ? 'delete-overlay' : 'AttemptDeleteCanonical',
                target: selectedDescriptor,
            });
            return;
        }

        if (selectedDescriptor.isOverlay && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            if (selectedEl.getAttribute('data-ov-locked') === '1') return;
            e.preventDefault();
            const host = selectedEl.closest('[data-mayhen-section], [data-curated-section]');
            const hr = host?.getBoundingClientRect();
            if (!hr || hr.width < 20 || hr.height < 20) return;
            const stepPx = e.shiftKey ? 10 : 1;
            const curX = parseFloat(selectedEl.style.getPropertyValue('--mh-ov-x')) || 0.5;
            const curY = parseFloat(selectedEl.style.getPropertyValue('--mh-ov-y')) || 0.5;
            const nx = Math.max(0.02, Math.min(0.98, curX + ((e.key === 'ArrowRight' ? stepPx : e.key === 'ArrowLeft' ? -stepPx : 0) / hr.width)));
            const ny = Math.max(0.02, Math.min(0.98, curY + ((e.key === 'ArrowDown' ? stepPx : e.key === 'ArrowUp' ? -stepPx : 0) / hr.height)));
            selectedEl.style.setProperty('--mh-ov-x', String(Number(nx.toFixed(4))));
            selectedEl.style.setProperty('--mh-ov-y', String(Number(ny.toFixed(4))));
            updateSelectionGeometry();
            postToStudio('MAYHEN_V3_OVERLAY_TRANSFORM_COMMIT', {
                overlay_id: selectedDescriptor.overlayId,
                patch: { x: Number(nx.toFixed(4)), y: Number(ny.toFixed(4)) },
                is_desktop_viewport: window.innerWidth >= 900,
                safe_zone_warning: checkSafeZoneOverlap(selectedEl),
            });
        }
    });

    window.addEventListener('scroll', updateSelectionGeometry, { passive: true });
    window.addEventListener('resize', updateSelectionGeometry);

    // Listen to commands from Parent Studio V3 Controller
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== window.parent) return;
        const data = event.data;
        if (!data || typeof data !== 'object') return;

        if (data.type === 'MAYHEN_V3_SET_MODE') {
            guestPreviewMode = data.mode === 'guest_preview';
            if (guestPreviewMode && activeInlineEl) commitInlineEditing(false);
            document.body.classList.toggle('mayhen_v3_guest_mode', guestPreviewMode);
            updateSelectionGeometry();
        } else if (data.type === 'MAYHEN_V3_SELECT_BY_ID') {
            setTimeout(() => {
                assignDeterministicEditIds(document);
                const el = data.is_overlay
                    ? document.querySelector(`[data-overlay-id="${data.id}"]`)
                    : document.querySelector(`[data-edit-id="${data.id}"]`);
                if (el) {
                    if (data.scroll !== false) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    selectElement(el, Boolean(data.notify));
                    if (data.start_inline) startInlineEditing(el);
                }
            }, 40);
        } else if (data.type === 'MAYHEN_V3_CLEAR_SELECTION') {
            selectElement(null, false);
        } else if (data.type === 'MAYHEN_V3_SYNC_VISUAL') {
            // Re-bind selection after overlay DOM re-render
            setTimeout(() => {
                if (selectedDescriptor?.isOverlay && selectedDescriptor.overlayId) {
                    selectedEl = document.querySelector(`[data-overlay-id="${selectedDescriptor.overlayId}"]`);
                } else if (selectedDescriptor?.editId) {
                    selectedEl = document.querySelector(`[data-edit-id="${selectedDescriptor.editId}"]`);
                }
                updateSelectionGeometry();
            }, 20);
        }
    });

    postToStudio('MAYHEN_V3_AGENT_READY', {
        template_slug: document.body.getAttribute('data-template-slug') || '',
    });
}

if (typeof window !== 'undefined' && window.__MAYHEN_IS_STUDIO_EDITOR) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIframeCanvasAgent);
    } else {
        initIframeCanvasAgent();
    }
}
