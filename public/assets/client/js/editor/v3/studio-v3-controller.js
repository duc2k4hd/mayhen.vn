/**
 * MÂY HẸN — STUDIO VISUAL EDITOR V3 PARENT CONTROLLER
 * Điều phối toàn bộ tương tác Direct Click-to-Edit giữa Iframe Preview,
 * Right Contextual Inspector, Kho Trang Trí (Text/Sticker/Shape/Frame/Image),
 * Font Tiếng Việt Cục Bộ, Cắt Ảnh Đa Tỷ Lệ, Motion Registry, EditorStore, Undo/Redo & Autosaver.
 */

import { ActionTypes } from '../actions.js';
import { FONT_STACKS, resolveCanonicalSectionId } from '../../common/visual-editor-renderer.js';
import { compressImageFileToWebpDataUrl } from '../../common/image-webp-compressor.js?v=202611270935';
import { createStudioPreviewBridge, resolveFormKeyForSyncTarget } from '../studio-preview-bridge.js';

const PRESET_SWATCHES = [
    '#1F1D1A',
    '#5A1822',
    '#8B1E2D',
    '#9E7738',
    '#C5A059',
    '#4A614D',
    '#2C3E50',
    '#7A6A58',
    '#FFFDF9',
];

function cloneJson(val, fallback = {}) {
    try {
        return JSON.parse(JSON.stringify(val ?? fallback));
    } catch (e) {
        return fallback;
    }
}

function generateOverlayId() {
    const rand = Math.random().toString(36).slice(2, 10);
    return `ov_${Date.now().toString(36).slice(-4)}${rand}`;
}

export function initStudioV3Controller({
    store,
    mainForm,
    sendSyncMessage,
    sendScrollToSection,
    getActiveIframes,
    openDynamicCropper,
    selectAndProcessImage,
    mirrorWeddingInput,
}) {
    const catalog = window.__MAYHEN_V3_CATALOG || {
        fonts: [],
        stickers: [],
        shapes: [],
        frames: [],
        motions: [],
    };

    const inspectorEl = document.getElementById('mayhen_v3_inspector');
    const inspectorTitleEl = document.getElementById('mayhen_v3_inspector_title');
    const inspectorSubEl = document.getElementById('mayhen_v3_inspector_sub');
    const inspectorTabsEl = document.getElementById('mayhen_v3_inspector_tabs');
    const inspectorBodyEl = document.getElementById('mayhen_v3_inspector_body');
    const inspectorCloseBtn = document.getElementById('mayhen_v3_inspector_close');
    const decorDrawerContainer = document.getElementById('mayhen_v3_decor_drawer_body');
    const guestModeBtn = document.getElementById('mayhen_v3_guest_mode_btn');
    const centerStage = document.getElementById('mayhen_editor_center_stage');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

    // Hidden file input dedicated to Studio V3 Direct Image Replace / Frame Upload
    const v3FileInput = document.createElement('input');
    v3FileInput.type = 'file';
    v3FileInput.accept = 'image/jpeg,image/png,image/webp,image/gif';
    v3FileInput.style.display = 'none';
    document.body.appendChild(v3FileInput);

    let pendingFileCallback = null;
    v3FileInput.addEventListener('change', async () => {
        const file = v3FileInput.files?.[0];
        v3FileInput.value = '';
        if (!file || typeof pendingFileCallback !== 'function') return;
        const cb = pendingFileCallback;
        pendingFileCallback = null;
        await cb(file);
    });

    let selectedTarget = null;
    let activeInspectorTab = 'style'; // 'style' | 'motion' | 'layers'
    let isGuestPreview = false;

    function getVisualEditorState() {
        const cfg = store.getState().custom_config || {};
        const ve = cfg.visual_editor && typeof cfg.visual_editor === 'object' ? cfg.visual_editor : {};
        return {
            version: 1,
            global_style: { ...(ve.global_style || {}) },
            instance_styles: { ...(ve.instance_styles || {}) },
            image_overrides: { ...(ve.image_overrides || {}) },
            instance_animations: { ...(ve.instance_animations || {}) },
            overlays: Array.isArray(ve.overlays) ? cloneJson(ve.overlays, []) : [],
        };
    }

    const previewBridge = createStudioPreviewBridge({ getIframes: getActiveIframes });

    function postToAllIframes(payload) {
        previewBridge.postV3(payload);
    }

    function syncHiddenCustomConfigInput() {
        const hiddenInput = document.getElementById('invitation_custom_config');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(store.getState().custom_config || {});
        }
    }

    function commitVisualEditor(nextVe) {
        const cleanVe = cloneJson(nextVe, {});
        cleanVe.version = 1;
        store.dispatch({
            type: ActionTypes.UPDATE_CUSTOM_CONFIG,
            payload: { visual_editor: cleanVe },
        });
        syncHiddenCustomConfigInput();
        postToAllIframes({
            type: 'MAYHEN_V3_SYNC_VISUAL',
            visual_editor: cleanVe,
        });
        renderDecorationDrawer();
    }

    function updateCanonicalFieldValue(syncKey, value, pushToIframe = true) {
        const formKey = resolveFormKeyForSyncTarget(syncKey);
        const inputs = Array.from(document.querySelectorAll(`[data-sync-key="${formKey}"]`));
        inputs.forEach((inp) => {
            if (inp.value !== value) {
                inp.value = value;
                if (typeof mirrorWeddingInput === 'function' && mainForm) {
                    mirrorWeddingInput(mainForm, inp);
                }
            }
            const card = inp.closest('.mayhen_editor_upload_box, .mayhen_editor_gallery_slot');
            const thumb = card?.querySelector('img');
            if (thumb && (value.startsWith('/storage/') || value.startsWith('http') || value.startsWith('data:image') || value.startsWith('/mau-thiep/'))) {
                thumb.src = value;
            }
        });

        store.dispatch({
            type: ActionTypes.UPDATE_FIELD,
            payload: { key: formKey, value },
        });

        if (pushToIframe) {
            sendSyncMessage(syncKey, value);
        }
    }

    async function convertImageToLocalWebpDataUrl(fileOrBlob) {
        return compressImageFileToWebpDataUrl(fileOrBlob, { maxWidth: 1200, maxHeight: 1600 });
    }

    function showV3Toast(msg) {
        let toast = document.getElementById('mayhenV3Toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'mayhenV3Toast';
            toast.style.cssText = 'position:fixed;bottom:22px;left:50%;transform:translateX(-50%);background:#2B2523;color:#FAF6F0;padding:9px 16px;border-radius:999px;font-size:12.5px;font-weight:600;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,0.22);transition:opacity 0.2s ease;';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.style.opacity = '1';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
        }, 2600);
    }

    // ---- ADD NEW OVERLAY DECORATION ITEMS AT EXACT VISIBLE VIEWPORT CENTER (ZERO SCROLL) ----
    function getPrimaryVisibleIframe() {
        const iframes = getActiveIframes();
        const visible = iframes.find((f) => {
            if (!f || !f.contentWindow || !f.contentDocument) return false;
            const r = f.getBoundingClientRect();
            return r.width > 20 && r.height > 20 && window.getComputedStyle(f).display !== 'none';
        });
        return visible || iframes[0] || null;
    }

    function resolveViewportCenterAnchor() {
        const iframe = getPrimaryVisibleIframe();
        if (iframe?.contentWindow && iframe?.contentDocument) {
            try {
                const win = iframe.contentWindow;
                const doc = iframe.contentDocument;
                const vpW = win.innerWidth || 390;
                const vpH = win.innerHeight || 720;
                const vpCenterX = vpW / 2;
                const vpCenterY = vpH / 2;

                const candidates = Array.from(doc.querySelectorAll('[data-mayhen-section], [data-curated-section]'))
                    .filter((sec) => {
                        const secId = resolveCanonicalSectionId(sec);
                        if (!secId || secId === 'opening') return false;
                        const cs = win.getComputedStyle(sec);
                        if (cs.display === 'none' || cs.visibility === 'hidden') return false;
                        const r = sec.getBoundingClientRect();
                        return r.width > 40 && r.height > 30;
                    });

                if (candidates.length > 0) {
                    // 1. Prefer the section directly under the center of the current preview screen
                    let bestSec = candidates.find((sec) => {
                        const r = sec.getBoundingClientRect();
                        return r.top <= vpCenterY && r.bottom >= vpCenterY;
                    });

                    // 2. Fallback: nearest visible section to viewport center (e.g., if scrolled onto a margin gap)
                    if (!bestSec) {
                        let minDistance = Infinity;
                        candidates.forEach((sec) => {
                            const r = sec.getBoundingClientRect();
                            const dist = vpCenterY < r.top
                                ? (r.top - vpCenterY)
                                : vpCenterY > r.bottom
                                    ? (vpCenterY - r.bottom)
                                    : 0;
                            if (dist < minDistance) {
                                minDistance = dist;
                                bestSec = sec;
                            }
                        });
                    }

                    if (bestSec) {
                        const rect = bestSec.getBoundingClientRect();
                        const rawX = (vpCenterX - rect.left) / Math.max(1, rect.width);
                        const rawY = (vpCenterY - rect.top) / Math.max(1, rect.height);
                        return {
                            sectionId: resolveCanonicalSectionId(bestSec),
                            x: Number(Math.max(0.12, Math.min(0.88, rawX)).toFixed(4)),
                            y: Number(Math.max(0.06, Math.min(0.94, rawY)).toFixed(4)),
                            isDesktop: vpW >= 900,
                        };
                    }
                }
            } catch (err) {}
        }

        const activeStepBtn = document.querySelector('.mayhen_editor_stepper_tab_active, .mayhen_editor_step_pill.active, [data-mayhen-step].active');
        const stepSec = activeStepBtn?.getAttribute('data-section-id');
        return {
            sectionId: (selectedTarget?.sectionId && selectedTarget.sectionId !== 'opening')
                ? selectedTarget.sectionId
                : (stepSec || 'hero'),
            x: 0.5,
            y: 0.5,
            isDesktop: false,
        };
    }

    function readFileImageAspect(file) {
        return new Promise((resolve) => {
            if (!file) {
                resolve(0.8);
                return;
            }
            const tempUrl = URL.createObjectURL(file);
            const img = new Image();
            const cleanup = (ratio) => {
                URL.revokeObjectURL(tempUrl);
                resolve(Math.max(0.45, Math.min(2.4, Number(ratio.toFixed(4)) || 0.8)));
            };
            img.onload = () => cleanup((img.naturalWidth || 4) / Math.max(1, img.naturalHeight || 5));
            img.onerror = () => cleanup(0.8);
            img.src = tempUrl;
        });
    }

    function renderFramePreviewIcon(frameId, strokeColor = '#C5A059') {
        const c = strokeColor || '#C5A059';
        const common = `display:inline-block;box-sizing:border-box;background:linear-gradient(160deg,#FFFDF9,#EFE4D2);`;
        switch (frameId) {
            case 'frame-arch':
                return `<span style="${common}width:24px;height:30px;border:2px solid ${c};border-radius:999px 999px 4px 4px;"></span>`;
            case 'frame-oval':
                return `<span style="${common}width:24px;height:30px;border:2px solid ${c};border-radius:50%;"></span>`;
            case 'frame-circle':
                return `<span style="${common}width:28px;height:28px;border:2px solid ${c};border-radius:50%;"></span>`;
            case 'frame-polaroid':
                return `<span style="${common}width:25px;height:30px;background:#FFFDF9;border:1px solid #DFD5C5;padding:3px 3px 7px;box-shadow:0 2px 5px rgba(0,0,0,0.12);"><span style="display:block;width:100%;height:100%;background:#E6D8C3;"></span></span>`;
            case 'frame-royal-gold':
                return `<span style="${common}width:25px;height:30px;border:3px double ${c};border-radius:5px;"></span>`;
            case 'frame-scallop':
                return `<span style="${common}width:25px;height:30px;border:2px dashed ${c};border-radius:8px;"></span>`;
            case 'frame-film-strip':
                return `<span style="${common}width:26px;height:30px;background:#231F1D;border-top:2.5px dotted #FFF;border-bottom:2.5px dotted #FFF;border-radius:2px;"></span>`;
            default:
                return `<span style="${common}width:26px;height:30px;border:1.5px solid ${c};border-radius:6px;"></span>`;
        }
    }

    function addOverlayItem(partial) {
        const ve = getVisualEditorState();
        if (ve.overlays.length >= 80) {
            showV3Toast('Đã đạt giới hạn tối đa 80 phần tử trang trí trên một thiệp.');
            return;
        }
        const anchor = resolveViewportCenterAnchor();
        const sectionId = partial.section_id || anchor.sectionId;
        const inSection = ve.overlays.filter((o) => o.section_id === sectionId).length;
        if (inSection >= 25) {
            showV3Toast('Mục này đã đạt giới hạn 25 phần tử trang trí.');
            return;
        }

        const wVal = Number((partial.w ?? 0.26).toFixed(4));
        const hVal = Number((partial.h ?? 0.26).toFixed(4));
        const aspectVal = Number((partial.aspect || (wVal / Math.max(0.03, hVal))).toFixed(4));
        const posX = partial.x ?? anchor.x;
        const posY = partial.y ?? anchor.y;

        const maxZ = ve.overlays.reduce((m, o) => Math.max(m, Number(o.z_index) || 12), 11);
        const newItem = {
            id: generateOverlayId(),
            type: partial.type || 'overlay_sticker',
            section_id: sectionId,
            asset_id: partial.asset_id || '',
            text: partial.text || '',
            image_url: partial.image_url || '',
            x: posX,
            y: posY,
            w: wVal,
            h: hVal,
            aspect: aspectVal,
            desktop_override: partial.desktop_override ?? null,
            rotation_deg: partial.rotation_deg ?? 0,
            z_index: Math.min(60, maxZ + 1),
            opacity: partial.opacity ?? 100,
            locked: false,
            hidden: false,
            style: partial.style || { color: '#C5A059' },
            crop: partial.crop ?? null,
            motion: partial.motion || {
                preset: 'fade-in',
                duration_ms: 750,
                delay_ms: 0,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                trigger: 'viewport',
                repeat: 'once',
            },
        };

        ve.overlays.push(newItem);
        commitVisualEditor(ve);
        // Không gọi sendScrollToSection hay scrollIntoView để màn preview đứng yên tuyệt đối tại vị trí đang xem
        postToAllIframes({
            type: 'MAYHEN_V3_SELECT_BY_ID',
            id: newItem.id,
            is_overlay: true,
            scroll: false,
            notify: true,
            start_inline: newItem.type === 'overlay_text',
        });
    }

    function mutateOverlayById(overlayId, mutator) {
        const ve = getVisualEditorState();
        const idx = ve.overlays.findIndex((o) => o.id === overlayId);
        if (idx === -1) return;
        const updated = mutator(cloneJson(ve.overlays[idx]), idx, ve.overlays);
        if (updated === null) {
            ve.overlays.splice(idx, 1);
            if (selectedTarget?.overlayId === overlayId) {
                selectedTarget = null;
            }
        } else {
            ve.overlays[idx] = updated;
        }
        commitVisualEditor(ve);
        renderInspector();
    }

    // ---- RENDER LEFT DECORATION DRAWER (`[data-panel-name="decor"]`) ----
    function renderDecorationDrawer() {
        if (!decorDrawerContainer) return;
        const ve = getVisualEditorState();

        decorDrawerContainer.innerHTML = '';

        // Card 1: Add Text & Upload Free Image right at viewport center
        const quickCard = document.createElement('div');
        quickCard.className = 'mayhen_editor_v3_card';
        quickCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title">
                <span>Thêm Ngay Giữa Màn Hình Đang Xem</span>
            </div>
            <div class="mayhen_editor_v3_row">
                <button type="button" class="mayhen_editor_v3_btn mayhen_editor_v3_btn--primary" data-v3-add="text">
                    <span>＋ Khối Chữ Mới</span>
                </button>
                <button type="button" class="mayhen_editor_v3_btn" data-v3-add="free-image">
                    <span>＋ Ảnh Trang Trí</span>
                </button>
            </div>
        `;
        decorDrawerContainer.appendChild(quickCard);

        // Card 2: Stickers SVG
        const stickerCard = document.createElement('div');
        stickerCard.className = 'mayhen_editor_v3_card';
        stickerCard.innerHTML = `<div class="mayhen_editor_v3_card_title"><span>Dấu Sáp & Sticker Nghệ Thuật</span></div>`;
        const stickerGrid = document.createElement('div');
        stickerGrid.className = 'mayhen_editor_v3_catalog_grid';
        (catalog.stickers || []).forEach((st) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mayhen_editor_v3_catalog_tile';
            btn.innerHTML = `
                <span class="mayhen_editor_v3_catalog_icon" style="color:${st.default_color || '#C5A059'}">${st.svg}</span>
                <span>${st.label}</span>
            `;
            btn.addEventListener('click', () => {
                const ratio = Number(st.aspect) || 1;
                const w = ratio > 2 ? 0.52 : 0.24;
                const h = Number((w / ratio).toFixed(3));
                addOverlayItem({
                    type: st.category === 'divider' ? 'overlay_divider' : 'overlay_sticker',
                    asset_id: st.id,
                    w,
                    h,
                    aspect: ratio,
                    style: { color: st.default_color || '#C5A059' },
                });
            });
            stickerGrid.appendChild(btn);
        });
        stickerCard.appendChild(stickerGrid);
        decorDrawerContainer.appendChild(stickerCard);

        // Card 3: Photo Frames (8 frames with real shape preview icons)
        const frameCard = document.createElement('div');
        frameCard.className = 'mayhen_editor_v3_card';
        frameCard.innerHTML = `<div class="mayhen_editor_v3_card_title"><span>Khung Ảnh Nghệ Thuật (${(catalog.frames || []).length})</span></div>`;
        const frameGrid = document.createElement('div');
        frameGrid.className = 'mayhen_editor_v3_catalog_grid';
        (catalog.frames || []).forEach((fr) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mayhen_editor_v3_catalog_tile';
            btn.innerHTML = `
                ${renderFramePreviewIcon(fr.id, fr.default_stroke || '#C5A059')}
                <span>${fr.label}</span>
            `;
            btn.addEventListener('click', () => {
                const frameAspect = fr.id === 'frame-circle' ? 1 : (Number(fr.aspect) || 0.75);
                const w = fr.id === 'frame-circle' ? 0.44 : 0.46;
                addOverlayItem({
                    type: 'overlay_frame',
                    asset_id: fr.id,
                    w,
                    h: Number((w / frameAspect).toFixed(3)),
                    aspect: frameAspect,
                    style: { stroke: fr.default_stroke || '#C5A059' },
                });
            });
            frameGrid.appendChild(btn);
        });
        frameCard.appendChild(frameGrid);
        decorDrawerContainer.appendChild(frameCard);

        // Card 4: Shapes
        const shapeCard = document.createElement('div');
        shapeCard.className = 'mayhen_editor_v3_card';
        shapeCard.innerHTML = `<div class="mayhen_editor_v3_card_title"><span>Hình Khối & Nhãn Nền</span></div>`;
        const shapeGrid = document.createElement('div');
        shapeGrid.className = 'mayhen_editor_v3_catalog_grid';
        (catalog.shapes || []).forEach((sh) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mayhen_editor_v3_catalog_tile';
            btn.innerHTML = `
                <span style="width:28px;height:18px;display:inline-block;background:${sh.fill};border:1.5px solid ${sh.stroke};border-radius:${sh.radius};"></span>
                <span>${sh.label}</span>
            `;
            btn.addEventListener('click', () => {
                const isLine = sh.id === 'line-gold';
                const isCircle = sh.id === 'circle-medallion';
                const w = isLine ? 0.54 : (isCircle ? 0.32 : 0.42);
                const aspect = isLine ? 18 : (isCircle ? 1 : (sh.id === 'arch-dome' ? 0.8 : 2.2));
                addOverlayItem({
                    type: 'overlay_shape',
                    asset_id: sh.id,
                    w,
                    h: Number((w / aspect).toFixed(3)),
                    aspect,
                    style: { fill: sh.fill, stroke: sh.stroke },
                });
            });
            shapeGrid.appendChild(btn);
        });
        shapeCard.appendChild(shapeGrid);
        decorDrawerContainer.appendChild(shapeCard);

        // Bind quick add buttons
        quickCard.querySelector('[data-v3-add="text"]')?.addEventListener('click', () => {
            addOverlayItem({
                type: 'overlay_text',
                text: 'Trăm Năm Hạnh Phúc',
                w: 0.68,
                h: 0.12,
                aspect: 5.5,
                style: {
                    font_id: 'romantic-calligraphy',
                    font_size_px: 26,
                    color: '#5A1822',
                    text_align: 'center',
                },
            });
        });

        quickCard.querySelector('[data-v3-add="free-image"]')?.addEventListener('click', () => {
            // Capture exact screen position AT CLICK TIME before file dialog opens
            const capturedAnchor = resolveViewportCenterAnchor();
            pendingFileCallback = async (file) => {
                try {
                    showV3Toast('Đang tối ưu ảnh WebP...');
                    const [naturalAspect, uploadedUrl] = await Promise.all([
                        readFileImageAspect(file),
                        convertImageToLocalWebpDataUrl(file),
                    ]);
                    const w = naturalAspect >= 1.15 ? 0.56 : 0.46;
                    addOverlayItem({
                        type: 'overlay_image',
                        section_id: capturedAnchor.sectionId,
                        x: capturedAnchor.x,
                        y: capturedAnchor.y,
                        image_url: uploadedUrl,
                        w,
                        h: Number((w / naturalAspect).toFixed(3)),
                        aspect: naturalAspect,
                    });
                    showV3Toast('Đã đặt ảnh ngay giữa màn hình đang xem!');
                } catch (err) {
                    showV3Toast(err.message || 'Tải ảnh thất bại.');
                }
            };
            v3FileInput.click();
        });

        // Card 5: Layers & Unplaced Overlays Manager
        if (ve.overlays.length > 0) {
            const availableSecIds = new Set(
                Array.from(document.querySelectorAll('[data-mayhen-step][data-section-id]'))
                    .map((el) => el.getAttribute('data-section-id'))
                    .filter(Boolean)
            );
            const layersCard = document.createElement('div');
            layersCard.className = 'mayhen_editor_v3_card';
            layersCard.innerHTML = `<div class="mayhen_editor_v3_card_title"><span>Quản Lý Lớp Trang Trí (${ve.overlays.length})</span></div>`;
            ve.overlays.forEach((ov) => {
                const isUnplaced = availableSecIds.size > 0 && !availableSecIds.has(ov.section_id);
                const row = document.createElement('div');
                row.className = 'mayhen_editor_v3_layer_item' + (selectedTarget?.overlayId === ov.id ? ' is-selected' : '');
                const titleText = ov.type === 'overlay_text'
                    ? `Chữ: "${(ov.text || '').slice(0, 18)}"`
                    : `${ov.type.replace('overlay_', '').toUpperCase()} (${ov.section_id})`;
                row.innerHTML = `
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${ov.locked ? '🔒 ' : ''}${ov.hidden ? '👁️‍🗨️ ' : ''}${titleText}${isUnplaced ? ' ⚠️ Ẩn ở mẫu này' : ''}
                    </span>
                    <span style="display:inline-flex;gap:4px;">
                        ${isUnplaced ? '<button type="button" class="mayhen_editor_v3_btn" data-move-hero="1" style="padding:2px 6px;font-size:11px;">Chuyển về Bìa</button>' : ''}
                        <button type="button" class="mayhen_editor_v3_btn mayhen_editor_v3_btn--danger" data-del-ov="1" style="padding:2px 6px;font-size:11px;">✕</button>
                    </span>
                `;
                row.addEventListener('click', (e) => {
                    if (e.target.closest('[data-del-ov]')) {
                        mutateOverlayById(ov.id, () => null);
                        return;
                    }
                    if (e.target.closest('[data-move-hero]')) {
                        mutateOverlayById(ov.id, (item) => ({ ...item, section_id: 'hero', x: 0.5, y: 0.5 }));
                        return;
                    }
                    sendScrollToSection(ov.section_id, 0);
                    postToAllIframes({
                        type: 'MAYHEN_V3_SELECT_BY_ID',
                        id: ov.id,
                        is_overlay: true,
                        notify: true,
                    });
                });
                layersCard.appendChild(row);
            });
            decorDrawerContainer.appendChild(layersCard);
        }
    }

    // ---- RENDER RIGHT CONTEXTUAL INSPECTOR ----
    function buildMotionControls(currentMotion, onMotionChange, onPreviewClick) {
        const wrap = document.createElement('div');
        wrap.className = 'mayhen_editor_v3_card';
        const activePreset = currentMotion?.preset || 'none';
        const dur = currentMotion?.duration_ms ?? 800;
        const delay = currentMotion?.delay_ms ?? 0;
        const trigger = currentMotion?.trigger || 'viewport';
        const repeat = currentMotion?.repeat || 'once';

        wrap.innerHTML = `
            <div class="mayhen_editor_v3_card_title">
                <span>Thư Viện Chuyển Động</span>
                <button type="button" class="mayhen_editor_v3_btn" data-v3-replay-motion="1" style="padding:3px 8px;font-size:11px;">▶ Xem thử</button>
            </div>
            <div class="mayhen_editor_v3_field">
                <label class="mayhen_editor_v3_label">Kiểu hiệu ứng</label>
                <select class="mayhen_editor_v3_select" data-mot-field="preset"></select>
            </div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Thời lượng <span>${dur}ms</span></label>
                    <input type="range" min="200" max="3000" step="50" value="${dur}" data-mot-field="duration_ms">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Độ trễ <span>${delay}ms</span></label>
                    <input type="range" min="0" max="1500" step="50" value="${delay}" data-mot-field="delay_ms">
                </div>
            </div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Kích hoạt</label>
                    <select class="mayhen_editor_v3_select" data-mot-field="trigger">
                        <option value="viewport" ${trigger === 'viewport' ? 'selected' : ''}>Khi cuộn tới</option>
                        <option value="on_load" ${trigger === 'on_load' ? 'selected' : ''}>Ngay khi mở</option>
                        <option value="after_open" ${trigger === 'after_open' ? 'selected' : ''}>Sau mở phong bì</option>
                    </select>
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Lặp lại</label>
                    <select class="mayhen_editor_v3_select" data-mot-field="repeat">
                        <option value="once" ${repeat === 'once' ? 'selected' : ''}>Một lần</option>
                        <option value="infinite" ${repeat === 'infinite' ? 'selected' : ''}>Liên tục nhẹ</option>
                    </select>
                </div>
            </div>
        `;

        const presetSelect = wrap.querySelector('[data-mot-field="preset"]');
        (catalog.motions || []).forEach((m) => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.label;
            if (m.id === activePreset) opt.selected = true;
            presetSelect.appendChild(opt);
        });

        const collectMotion = () => {
            const p = presetSelect.value;
            if (!p || p === 'none') return null;
            return {
                preset: p,
                duration_ms: Number(wrap.querySelector('[data-mot-field="duration_ms"]').value) || 800,
                delay_ms: Number(wrap.querySelector('[data-mot-field="delay_ms"]').value) || 0,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                trigger: wrap.querySelector('[data-mot-field="trigger"]').value || 'viewport',
                repeat: wrap.querySelector('[data-mot-field="repeat"]').value || 'once',
            };
        };

        wrap.querySelectorAll('[data-mot-field]').forEach((inp) => {
            inp.addEventListener('change', () => {
                const next = collectMotion();
                onMotionChange(next);
                if (next) onPreviewClick(next);
            });
        });

        wrap.querySelector('[data-v3-replay-motion]')?.addEventListener('click', () => {
            const next = collectMotion() || { preset: 'soft-rise', duration_ms: 800, delay_ms: 0, repeat: 'once' };
            onPreviewClick(next);
        });

        return wrap;
    }

    function buildFontPickerList(selectedFontId, onSelectFont) {
        const list = document.createElement('div');
        list.className = 'mayhen_editor_v3_font_list';

        const defaultBtn = document.createElement('button');
        defaultBtn.type = 'button';
        defaultBtn.className = 'mayhen_editor_v3_font_item' + (!selectedFontId ? ' is-selected' : '');
        defaultBtn.innerHTML = `
            <div class="mayhen_editor_v3_font_meta"><span>Mặc định của mẫu thiệp</span><span>Chuẩn gốc</span></div>
            <div class="mayhen_editor_v3_font_sample">Giữ nguyên font nghệ thuật gốc</div>
        `;
        defaultBtn.addEventListener('click', () => onSelectFont(null));
        list.appendChild(defaultBtn);

        (catalog.fonts || []).forEach((f) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mayhen_editor_v3_font_item' + (selectedFontId === f.id ? ' is-selected' : '');
            const metaDiv = document.createElement('div');
            metaDiv.className = 'mayhen_editor_v3_font_meta';
            metaDiv.innerHTML = `<span>${f.label}</span><span>Tiếng Việt ✓</span>`;
            const sampleDiv = document.createElement('div');
            sampleDiv.className = 'mayhen_editor_v3_font_sample';
            sampleDiv.style.fontFamily = FONT_STACKS[f.id] || f.stack;
            sampleDiv.textContent = f.sample || 'Trăm năm hạnh phúc — Huyền Trang & Thành Đạt';
            btn.appendChild(metaDiv);
            btn.appendChild(sampleDiv);
            btn.addEventListener('click', () => onSelectFont(f.id));
            list.appendChild(btn);
        });

        return list;
    }

    function renderGlobalDefaultInspector() {
        const ve = getVisualEditorState();
        const gs = ve.global_style || {};
        inspectorTitleEl.textContent = 'Trình Chỉnh Sửa Trực Quan V3';
        inspectorSubEl.textContent = 'Chạm trực tiếp vào chữ hoặc ảnh trên thiệp để sửa ngay';
        inspectorTabsEl.style.display = 'none';
        inspectorBodyEl.innerHTML = '';

        const tipCard = document.createElement('div');
        tipCard.className = 'mayhen_editor_v3_card';
        tipCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title"><span>Mẹo Thao Tác Nhanh</span></div>
            <div style="font-size:0.76rem;color:#57534E;line-height:1.5;">
                • <strong>1 chạm</strong> vào chữ/ảnh trên thiệp để chọn.<br>
                • <strong>Chạm đúp</strong> (hoặc nhấn <code>Enter</code>) để gõ sửa chữ trực tiếp.<br>
                • Dùng mục <strong>Trang Trí</strong> bên trái để thêm Dấu sáp, Sticker, Khung ảnh.
            </div>
        `;
        inspectorBodyEl.appendChild(tipCard);

        const globalFontCard = document.createElement('div');
        globalFontCard.className = 'mayhen_editor_v3_card';
        globalFontCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title">
                <span>Bộ Font Toàn Thiệp (100% Tiếng Việt)</span>
                <button type="button" class="mayhen_editor_v3_btn" data-reset-global-fonts="1" style="padding:2px 7px;font-size:11px;">Đặt lại</button>
            </div>
        `;

        const roles = [
            { key: 'heading_font_id', label: 'Font Tiêu Đề Chính' },
            { key: 'body_font_id', label: 'Font Nội Dung Đọc' },
            { key: 'accent_font_id', label: 'Font Chữ Ký Nghệ Thuật' },
        ];

        roles.forEach((r) => {
            const field = document.createElement('div');
            field.className = 'mayhen_editor_v3_field';
            const lbl = document.createElement('label');
            lbl.className = 'mayhen_editor_v3_label';
            lbl.textContent = r.label;
            const sel = document.createElement('select');
            sel.className = 'mayhen_editor_v3_select';

            const defOpt = document.createElement('option');
            defOpt.value = '';
            defOpt.textContent = '— Mặc định của mẫu thiệp —';
            sel.appendChild(defOpt);

            (catalog.fonts || []).forEach((f) => {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.textContent = `${f.label}`;
                if (gs[r.key] === f.id) opt.selected = true;
                sel.appendChild(opt);
            });

            sel.addEventListener('change', () => {
                const nextVe = getVisualEditorState();
                if (!sel.value) {
                    delete nextVe.global_style[r.key];
                } else {
                    nextVe.global_style[r.key] = sel.value;
                }
                commitVisualEditor(nextVe);
            });

            field.appendChild(lbl);
            field.appendChild(sel);
            globalFontCard.appendChild(field);
        });

        globalFontCard.querySelector('[data-reset-global-fonts]')?.addEventListener('click', () => {
            const nextVe = getVisualEditorState();
            nextVe.global_style = {};
            commitVisualEditor(nextVe);
            renderInspector();
            showV3Toast('Đã khôi phục bộ font gốc của mẫu thiệp.');
        });

        inspectorBodyEl.appendChild(globalFontCard);
    }

    function renderCanonicalTextInspector(target) {
        const ve = getVisualEditorState();
        const syncRuleKey = `sync_key:${target.syncKey}`;
        const hasSyncScope = Boolean(ve.instance_styles[syncRuleKey]);
        let scopeMode = hasSyncScope ? 'sync_key' : 'instance';
        const activeStyleKey = () => (scopeMode === 'sync_key' ? syncRuleKey : target.editId);
        const currentRule = ve.instance_styles[activeStyleKey()] || {};
        const currentMotion = ve.instance_animations[target.editId] || null;

        inspectorTitleEl.textContent = target.label || 'Chỉnh sửa Văn bản';
        inspectorSubEl.textContent = `Mục: ${target.sectionId} · Chạm đúp trên thiệp để gõ trực tiếp`;
        inspectorTabsEl.style.display = 'flex';
        inspectorBodyEl.innerHTML = '';

        if (activeInspectorTab === 'motion') {
            inspectorBodyEl.appendChild(
                buildMotionControls(
                    currentMotion,
                    (nextMotion) => {
                        const nextVe = getVisualEditorState();
                        if (!nextMotion) delete nextVe.instance_animations[target.editId];
                        else nextVe.instance_animations[target.editId] = nextMotion;
                        commitVisualEditor(nextVe);
                    },
                    (previewMotion) => {
                        postToAllIframes({
                            type: 'MAYHEN_V3_PREVIEW_MOTION',
                            target_id: target.editId,
                            is_overlay: false,
                            motion: previewMotion,
                        });
                    }
                )
            );
            return;
        }

        // Content Card (2-way synced with Wedding Store & Iframe)
        const contentCard = document.createElement('div');
        contentCard.className = 'mayhen_editor_v3_card';
        const formKey = resolveFormKeyForSyncTarget(target.syncKey);
        const existingFormInput = document.querySelector(`[data-sync-key="${formKey}"]`);
        const currentText = existingFormInput ? existingFormInput.value : (target.text || '');

        contentCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title"><span>Nội Dung Văn Bản</span></div>
            <div class="mayhen_editor_v3_field">
                ${target.multiline
                    ? '<textarea class="mayhen_editor_v3_textarea" data-v3-live-text="1"></textarea>'
                    : '<input type="text" class="mayhen_editor_v3_input" data-v3-live-text="1">'}
            </div>
        `;
        const liveInput = contentCard.querySelector('[data-v3-live-text]');
        liveInput.value = currentText;
        let composingInspector = false;
        liveInput.addEventListener('compositionstart', () => { composingInspector = true; });
        liveInput.addEventListener('compositionend', () => {
            composingInspector = false;
            updateCanonicalFieldValue(target.syncKey, liveInput.value, true);
        });
        liveInput.addEventListener('input', (e) => {
            if (composingInspector || e.isComposing) return;
            updateCanonicalFieldValue(target.syncKey, liveInput.value, true);
        });
        inspectorBodyEl.appendChild(contentCard);

        // Typography Style Card
        const styleCard = document.createElement('div');
        styleCard.className = 'mayhen_editor_v3_card';
        const fontSizeVal = currentRule.font_size_px || target.computedStyle?.fontSizePx || 20;
        const lineHeightVal = currentRule.line_height || target.computedStyle?.lineHeight || 1.4;
        const letterSpacingVal = currentRule.letter_spacing_em ?? 0;
        const alignVal = currentRule.text_align || target.computedStyle?.textAlign || 'center';
        const colorVal = currentRule.color || target.computedStyle?.color || '#1F1D1A';

        styleCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title">
                <span>Kiểu Chữ & Định Dạng</span>
                <button type="button" class="mayhen_editor_v3_btn" data-v3-reset-text="1" style="padding:2px 7px;font-size:11px;">Đặt lại</button>
            </div>
            <div class="mayhen_editor_v3_field">
                <label class="mayhen_editor_v3_label">Phạm vi áp dụng</label>
                <div class="mayhen_editor_v3_seg">
                    <button type="button" class="mayhen_editor_v3_seg_btn ${scopeMode === 'instance' ? 'is-active' : ''}" data-scope-btn="instance">Chỉ vị trí này</button>
                    <button type="button" class="mayhen_editor_v3_seg_btn ${scopeMode === 'sync_key' ? 'is-active' : ''}" data-scope-btn="sync_key">Mọi nơi cùng trường</button>
                </div>
            </div>
            <div class="mayhen_editor_v3_field" data-font-picker-slot="1">
                <label class="mayhen_editor_v3_label">Phông chữ Tiếng Việt</label>
            </div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Cỡ chữ <span data-lbl-size>${fontSizeVal}px</span></label>
                    <input type="range" min="11" max="84" step="1" value="${fontSizeVal}" data-style-prop="font_size_px">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Giãn dòng <span data-lbl-lh>${lineHeightVal}</span></label>
                    <input type="range" min="0.95" max="2.2" step="0.05" value="${lineHeightVal}" data-style-prop="line_height">
                </div>
            </div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Giãn chữ (em)</label>
                    <input type="number" min="-0.05" max="0.4" step="0.01" value="${letterSpacingVal}" class="mayhen_editor_v3_input" data-style-prop="letter_spacing_em">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Đậm / Nghiêng</label>
                    <div class="mayhen_editor_v3_seg">
                        <button type="button" class="mayhen_editor_v3_seg_btn ${(currentRule.font_weight || 400) >= 600 ? 'is-active' : ''}" data-toggle-bold="1"><strong>B</strong></button>
                        <button type="button" class="mayhen_editor_v3_seg_btn ${currentRule.font_style === 'italic' ? 'is-active' : ''}" data-toggle-italic="1"><em>I</em></button>
                    </div>
                </div>
            </div>
            <div class="mayhen_editor_v3_field">
                <label class="mayhen_editor_v3_label">Căn lề</label>
                <div class="mayhen_editor_v3_seg">
                    <button type="button" class="mayhen_editor_v3_seg_btn ${alignVal === 'left' ? 'is-active' : ''}" data-align-val="left">Trái</button>
                    <button type="button" class="mayhen_editor_v3_seg_btn ${alignVal === 'center' ? 'is-active' : ''}" data-align-val="center">Giữa</button>
                    <button type="button" class="mayhen_editor_v3_seg_btn ${alignVal === 'right' ? 'is-active' : ''}" data-align-val="right">Phải</button>
                </div>
            </div>
            <div class="mayhen_editor_v3_field">
                <label class="mayhen_editor_v3_label">Màu chữ</label>
                <div class="mayhen_editor_v3_swatches" data-swatch-slot="1">
                    <input type="color" value="${colorVal.startsWith('#') && colorVal.length === 7 ? colorVal : '#1F1D1A'}" data-color-picker="1" style="width:28px;height:28px;border:none;background:none;cursor:pointer;">
                </div>
            </div>
        `;

        const applyStylePatch = (patch) => {
            const nextVe = getVisualEditorState();
            const key = activeStyleKey();
            const merged = { ...(nextVe.instance_styles[key] || {}), scope: scopeMode, ...patch };
            Object.keys(merged).forEach((k) => {
                if (merged[k] === null || merged[k] === undefined || merged[k] === '') delete merged[k];
            });
            nextVe.instance_styles[key] = merged;
            commitVisualEditor(nextVe);
        };

        styleCard.querySelector('[data-font-picker-slot]').appendChild(
            buildFontPickerList(currentRule.font_id || null, (fontId) => {
                applyStylePatch({ font_id: fontId });
                renderInspector();
            })
        );

        const swatchSlot = styleCard.querySelector('[data-swatch-slot]');
        PRESET_SWATCHES.forEach((hex) => {
            const sw = document.createElement('button');
            sw.type = 'button';
            sw.className = 'mayhen_editor_v3_swatch' + (colorVal.toUpperCase() === hex ? ' is-active' : '');
            sw.style.backgroundColor = hex;
            sw.addEventListener('click', () => {
                applyStylePatch({ color: hex });
                renderInspector();
            });
            swatchSlot.appendChild(sw);
        });

        styleCard.querySelector('[data-color-picker]')?.addEventListener('input', (e) => {
            applyStylePatch({ color: e.target.value });
        });

        styleCard.querySelectorAll('[data-scope-btn]').forEach((btn) => {
            btn.addEventListener('click', () => {
                scopeMode = btn.getAttribute('data-scope-btn');
                styleCard.querySelectorAll('[data-scope-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
            });
        });

        styleCard.querySelector('[data-style-prop="font_size_px"]')?.addEventListener('input', (e) => {
            const v = Number(e.target.value);
            styleCard.querySelector('[data-lbl-size]').textContent = `${v}px`;
            applyStylePatch({ font_size_px: v });
        });

        styleCard.querySelector('[data-style-prop="line_height"]')?.addEventListener('input', (e) => {
            const v = Number(e.target.value);
            styleCard.querySelector('[data-lbl-lh]').textContent = String(v);
            applyStylePatch({ line_height: v });
        });

        styleCard.querySelector('[data-style-prop="letter_spacing_em"]')?.addEventListener('change', (e) => {
            applyStylePatch({ letter_spacing_em: Number(e.target.value) || 0 });
        });

        styleCard.querySelector('[data-toggle-bold]')?.addEventListener('click', () => {
            const cur = getVisualEditorState().instance_styles[activeStyleKey()]?.font_weight || 400;
            applyStylePatch({ font_weight: cur >= 600 ? 400 : 700 });
            renderInspector();
        });

        styleCard.querySelector('[data-toggle-italic]')?.addEventListener('click', () => {
            const cur = getVisualEditorState().instance_styles[activeStyleKey()]?.font_style || 'normal';
            applyStylePatch({ font_style: cur === 'italic' ? 'normal' : 'italic' });
            renderInspector();
        });

        styleCard.querySelectorAll('[data-align-val]').forEach((btn) => {
            btn.addEventListener('click', () => {
                applyStylePatch({ text_align: btn.getAttribute('data-align-val') });
                renderInspector();
            });
        });

        styleCard.querySelector('[data-v3-reset-text]')?.addEventListener('click', () => {
            const nextVe = getVisualEditorState();
            delete nextVe.instance_styles[target.editId];
            delete nextVe.instance_styles[syncRuleKey];
            commitVisualEditor(nextVe);
            renderInspector();
            showV3Toast('Đã khôi phục kiểu chữ gốc của mẫu thiệp.');
        });

        inspectorBodyEl.appendChild(styleCard);
    }

    function renderCanonicalImageInspector(target) {
        const ve = getVisualEditorState();
        const currentOverride = ve.image_overrides[target.editId] || {};
        const currentMotion = ve.instance_animations[target.editId] || null;
        const fx = currentOverride.focal_x ?? 50;
        const fy = currentOverride.focal_y ?? 50;
        const zoom = currentOverride.zoom ?? 1;
        const opacity = currentOverride.opacity ?? 100;

        inspectorTitleEl.textContent = target.label || 'Chỉnh sửa Hình ảnh';
        inspectorSubEl.textContent = `Mục: ${target.sectionId} · Khung ảnh chuẩn không phá bố cục`;
        inspectorTabsEl.style.display = 'flex';
        inspectorBodyEl.innerHTML = '';

        if (activeInspectorTab === 'motion') {
            inspectorBodyEl.appendChild(
                buildMotionControls(
                    currentMotion,
                    (nextMotion) => {
                        const nextVe = getVisualEditorState();
                        if (!nextMotion) delete nextVe.instance_animations[target.editId];
                        else nextVe.instance_animations[target.editId] = nextMotion;
                        commitVisualEditor(nextVe);
                    },
                    (previewMotion) => {
                        postToAllIframes({
                            type: 'MAYHEN_V3_PREVIEW_MOTION',
                            target_id: target.editId,
                            is_overlay: false,
                            motion: previewMotion,
                        });
                    }
                )
            );
            return;
        }

        const imgCard = document.createElement('div');
        imgCard.className = 'mayhen_editor_v3_card';
        imgCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title">
                <span>Ảnh Đang Hiển Thị</span>
                <button type="button" class="mayhen_editor_v3_btn" data-v3-reset-img="1" style="padding:2px 7px;font-size:11px;">Đặt lại</button>
            </div>
            <div style="width:100%;height:150px;border-radius:9px;overflow:hidden;background:#EFECE6;display:flex;align-items:center;justify-content:center;">
                <img src="${target.imageUrl || ''}" alt="" data-v3-img-preview="1" style="max-width:100%;max-height:100%;object-fit:contain;">
            </div>
            <div class="mayhen_editor_v3_row">
                <button type="button" class="mayhen_editor_v3_btn mayhen_editor_v3_btn--primary" data-v3-replace-img="1">Thay ảnh mới</button>
                <button type="button" class="mayhen_editor_v3_btn" data-v3-crop-img="1">Cắt theo khung</button>
            </div>
        `;

        const triggerUploadAndAssign = (file, forceCrop = false) => {
            if (typeof selectAndProcessImage === 'function') {
                selectAndProcessImage(file, {
                    syncKey: target.syncKey,
                    aspectRatio: target.slotAspect || 0.75,
                    forceCrop,
                    onSuccess: (url) => {
                        target.imageUrl = url;
                        renderInspector();
                        showV3Toast(forceCrop ? 'Đã cắt và cập nhật ảnh!' : 'Đã cập nhật ảnh thành công!');
                    },
                    onCancel: () => {
                        showV3Toast('Đã hủy chọn ảnh.');
                    },
                    onError: (err) => {
                        showV3Toast(err?.message || 'Không thể xử lý ảnh này.');
                    },
                });
                return;
            }

            convertImageToLocalWebpDataUrl(file).then((url) => {
                target.imageUrl = url;
                updateCanonicalFieldValue(target.syncKey, url, true);
                renderInspector();
                showV3Toast('Đã thay ảnh thành công!');
            }).catch((err) => {
                showV3Toast(err.message || 'Không thể xử lý ảnh này.');
            });
        };

        imgCard.querySelector('[data-v3-replace-img]')?.addEventListener('click', () => {
            pendingFileCallback = (file) => triggerUploadAndAssign(file, false);
            v3FileInput.click();
        });

        imgCard.querySelector('[data-v3-crop-img]')?.addEventListener('click', () => {
            if (target.imageUrl && typeof openDynamicCropper === 'function') {
                openDynamicCropper(target.imageUrl, {
                    aspectRatio: target.slotAspect || 0.75,
                    syncKey: target.syncKey,
                    onCroppedBlob: async (blob, webpDataUrl) => {
                        try {
                            const url = webpDataUrl || await convertImageToLocalWebpDataUrl(blob);
                            target.imageUrl = url;
                            updateCanonicalFieldValue(target.syncKey, url, true);
                            renderInspector();
                            showV3Toast('Đã cắt và cập nhật ảnh!');
                        } catch (err) {
                            showV3Toast(err.message || 'Lỗi khi lưu ảnh cắt.');
                        }
                    },
                });
            } else {
                pendingFileCallback = (file) => triggerUploadAndAssign(file, true);
                v3FileInput.click();
            }
        });

        inspectorBodyEl.appendChild(imgCard);

        // Non-destructive Focal Point & Zoom Card
        const focalCard = document.createElement('div');
        focalCard.className = 'mayhen_editor_v3_card';
        focalCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title"><span>Điểm Tiêu Cự & Thu Phóng (Không Phá Ảnh)</span></div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Ngang (X) <span data-fx-lbl>${fx}%</span></label>
                    <input type="range" min="0" max="100" step="1" value="${fx}" data-img-prop="focal_x">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Dọc (Y) <span data-fy-lbl>${fy}%</span></label>
                    <input type="range" min="0" max="100" step="1" value="${fy}" data-img-prop="focal_y">
                </div>
            </div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Phóng (Zoom) <span data-zm-lbl>${zoom}x</span></label>
                    <input type="range" min="1" max="2.5" step="0.05" value="${zoom}" data-img-prop="zoom">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Độ rõ <span data-op-lbl>${opacity}%</span></label>
                    <input type="range" min="20" max="100" step="5" value="${opacity}" data-img-prop="opacity">
                </div>
            </div>
        `;

        const patchImgOverride = (patch) => {
            const nextVe = getVisualEditorState();
            nextVe.image_overrides[target.editId] = {
                ...(nextVe.image_overrides[target.editId] || {}),
                ...patch,
            };
            commitVisualEditor(nextVe);
        };

        focalCard.querySelector('[data-img-prop="focal_x"]')?.addEventListener('input', (e) => {
            const v = Number(e.target.value);
            focalCard.querySelector('[data-fx-lbl]').textContent = `${v}%`;
            patchImgOverride({ focal_x: v });
        });
        focalCard.querySelector('[data-img-prop="focal_y"]')?.addEventListener('input', (e) => {
            const v = Number(e.target.value);
            focalCard.querySelector('[data-fy-lbl]').textContent = `${v}%`;
            patchImgOverride({ focal_y: v });
        });
        focalCard.querySelector('[data-img-prop="zoom"]')?.addEventListener('input', (e) => {
            const v = Number(e.target.value);
            focalCard.querySelector('[data-zm-lbl]').textContent = `${v}x`;
            patchImgOverride({ zoom: v });
        });
        focalCard.querySelector('[data-img-prop="opacity"]')?.addEventListener('input', (e) => {
            const v = Number(e.target.value);
            focalCard.querySelector('[data-op-lbl]').textContent = `${v}%`;
            patchImgOverride({ opacity: v });
        });

        imgCard.querySelector('[data-v3-reset-img]')?.addEventListener('click', () => {
            const nextVe = getVisualEditorState();
            delete nextVe.image_overrides[target.editId];
            commitVisualEditor(nextVe);
            renderInspector();
            showV3Toast('Đã đặt lại tiêu cự và tỷ lệ thu phóng gốc.');
        });

        inspectorBodyEl.appendChild(focalCard);

        // If business QR slot, also provide quick jump to Banking Section
        if (target.kind === 'business_qr') {
            const bankTip = document.createElement('div');
            bankTip.className = 'mayhen_editor_v3_card';
            bankTip.innerHTML = `
                <div class="mayhen_editor_v3_card_title"><span>Tự Động Tạo Mã VietQR Chuẩn</span></div>
                <div style="font-size:0.75rem;color:#57534E;">Bạn có thể nhập Số tài khoản & Ngân hàng ở mục Mừng Cưới để hệ thống tự sinh mã VietQR chính xác.</div>
                <button type="button" class="mayhen_editor_v3_btn" data-jump-gift="1">Mở biểu mẫu Ngân hàng & VietQR</button>
            `;
            bankTip.querySelector('[data-jump-gift]')?.addEventListener('click', () => {
                document.querySelector('[data-rail-tab="content"]')?.click();
                document.querySelector('[data-mayhen-step][data-section-id="gift"]')?.click();
            });
            inspectorBodyEl.appendChild(bankTip);
        }
    }

    function renderOverlayInspector(target) {
        const ve = getVisualEditorState();
        const ov = ve.overlays.find((o) => o.id === target.overlayId);
        if (!ov) {
            selectedTarget = null;
            renderGlobalDefaultInspector();
            return;
        }

        inspectorTitleEl.textContent = target.label || 'Phần tử Trang trí';
        inspectorSubEl.textContent = `Neo tại mục: ${ov.section_id} · Kéo / Xoay trực tiếp trên thiệp`;
        inspectorTabsEl.style.display = 'flex';
        inspectorBodyEl.innerHTML = '';

        if (target.safeZoneWarning) {
            const warn = document.createElement('div');
            warn.className = 'mayhen_editor_v3_warning';
            warn.innerHTML = `
                <strong>Lưu ý vùng an toàn:</strong> Phần tử đang đè lên nút tương tác của khách mời (RSVP / Lời chúc / QR / Bản đồ).
                <div style="margin-top:6px;">
                    <button type="button" class="mayhen_editor_v3_btn" data-safe-rescue="1" style="padding:4px 9px;font-size:11px;">Đưa về vị trí an toàn</button>
                </div>
            `;
            warn.querySelector('[data-safe-rescue]')?.addEventListener('click', () => {
                target.safeZoneWarning = false;
                mutateOverlayById(ov.id, (item) => ({ ...item, x: 0.5, y: 0.14 }));
            });
            inspectorBodyEl.appendChild(warn);
        }

        if (activeInspectorTab === 'motion') {
            inspectorBodyEl.appendChild(
                buildMotionControls(
                    ov.motion,
                    (nextMotion) => mutateOverlayById(ov.id, (item) => ({ ...item, motion: nextMotion })),
                    (previewMotion) => {
                        postToAllIframes({
                            type: 'MAYHEN_V3_PREVIEW_MOTION',
                            target_id: ov.id,
                            is_overlay: true,
                            motion: previewMotion,
                        });
                    }
                )
            );
            return;
        }

        // Type-specific content/color/photo controls
        const propCard = document.createElement('div');
        propCard.className = 'mayhen_editor_v3_card';
        propCard.innerHTML = `<div class="mayhen_editor_v3_card_title"><span>Thuộc Tính Phần Tử</span></div>`;

        if (ov.type === 'overlay_text') {
            const tf = document.createElement('div');
            tf.className = 'mayhen_editor_v3_field';
            tf.innerHTML = `<label class="mayhen_editor_v3_label">Nội dung chữ</label><textarea class="mayhen_editor_v3_textarea">${ov.text || ''}</textarea>`;
            const ta = tf.querySelector('textarea');
            let comp = false;
            ta.addEventListener('compositionstart', () => { comp = true; });
            ta.addEventListener('compositionend', () => {
                comp = false;
                mutateOverlayById(ov.id, (item) => ({ ...item, text: ta.value }));
            });
            ta.addEventListener('input', (e) => {
                if (comp || e.isComposing) return;
                mutateOverlayById(ov.id, (item) => ({ ...item, text: ta.value }));
            });
            propCard.appendChild(tf);

            propCard.appendChild(
                buildFontPickerList(ov.style?.font_id || null, (fontId) => {
                    mutateOverlayById(ov.id, (item) => ({
                        ...item,
                        style: { ...(item.style || {}), font_id: fontId },
                    }));
                })
            );

            const sizeRow = document.createElement('div');
            sizeRow.className = 'mayhen_editor_v3_field';
            const fsz = ov.style?.font_size_px || 24;
            sizeRow.innerHTML = `<label class="mayhen_editor_v3_label">Cỡ chữ <span>${fsz}px</span></label><input type="range" min="12" max="72" value="${fsz}">`;
            sizeRow.querySelector('input').addEventListener('input', (e) => {
                const v = Number(e.target.value);
                sizeRow.querySelector('span').textContent = `${v}px`;
                mutateOverlayById(ov.id, (item) => ({
                    ...item,
                    style: { ...(item.style || {}), font_size_px: v },
                }));
            });
            propCard.appendChild(sizeRow);
        } else if (ov.type === 'overlay_frame' || ov.type === 'overlay_image') {
            const hasPhoto = Boolean(ov.image_url);
            const previewWrap = document.createElement('div');
            previewWrap.style.cssText = 'width:100%;min-height:132px;border-radius:10px;overflow:hidden;background:#F3ECE0;border:1.5px dashed #C5A059;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8px;box-sizing:border-box;cursor:pointer;';
            if (hasPhoto) {
                previewWrap.style.borderStyle = 'solid';
                previewWrap.innerHTML = `<img src="${ov.image_url}" alt="" style="max-width:100%;max-height:140px;object-fit:contain;border-radius:6px;">`;
            } else {
                previewWrap.innerHTML = `
                    <span style="font-size:12px;font-weight:700;color:#5A4632;">Chưa có ảnh trong khung</span>
                    <span style="font-size:11px;color:#8C6226;margin-top:3px;">Bấm vào đây để chọn ảnh từ máy</span>
                `;
            }

            const triggerOverlayUpload = () => {
                pendingFileCallback = async (file) => {
                    try {
                        showV3Toast('Đang tối ưu ảnh WebP...');
                        const [naturalAspect, url] = await Promise.all([
                            readFileImageAspect(file),
                            convertImageToLocalWebpDataUrl(file),
                        ]);
                        mutateOverlayById(ov.id, (item) => {
                            const next = { ...item, image_url: url };
                            if (item.type === 'overlay_image') {
                                next.aspect = naturalAspect;
                                next.h = Number(((next.w || 0.46) / naturalAspect).toFixed(4));
                            }
                            return next;
                        });
                        showV3Toast('Đã cập nhật ảnh thành công!');
                    } catch (err) {
                        showV3Toast(err.message || 'Xử lý ảnh thất bại.');
                    }
                };
                v3FileInput.click();
            };

            previewWrap.addEventListener('click', triggerOverlayUpload);
            propCard.appendChild(previewWrap);

            const mediaRow = document.createElement('div');
            mediaRow.className = 'mayhen_editor_v3_row';
            mediaRow.innerHTML = `
                <button type="button" class="mayhen_editor_v3_btn mayhen_editor_v3_btn--primary" data-ov-upload="1">${hasPhoto ? 'Đổi ảnh khác' : '＋ Chọn ảnh tải lên'}</button>
                <button type="button" class="mayhen_editor_v3_btn" data-ov-crop="1">Cắt vừa khung</button>
            `;
            mediaRow.querySelector('[data-ov-upload]')?.addEventListener('click', triggerOverlayUpload);
            mediaRow.querySelector('[data-ov-crop]')?.addEventListener('click', () => {
                if (!ov.image_url || typeof openDynamicCropper !== 'function') {
                    triggerOverlayUpload();
                    return;
                }
                const frameAspect = Number(ov.aspect) > 0.1 ? Number(ov.aspect) : (target.slotAspect || 0.75);
                openDynamicCropper(ov.image_url, {
                    aspectRatio: frameAspect,
                    onCroppedBlob: async (blob, webpDataUrl) => {
                        const url = webpDataUrl || await convertImageToLocalWebpDataUrl(blob);
                        mutateOverlayById(ov.id, (item) => ({
                            ...item,
                            image_url: url,
                            crop: { focal_x: 50, focal_y: 50, zoom: 1 },
                        }));
                        showV3Toast('Đã cắt ảnh vừa khung!');
                    },
                });
            });
            propCard.appendChild(mediaRow);

            // Switch Frame Style anytime for `overlay_frame`
            if (ov.type === 'overlay_frame' && Array.isArray(catalog.frames) && catalog.frames.length > 0) {
                const frameSelField = document.createElement('div');
                frameSelField.className = 'mayhen_editor_v3_field';
                frameSelField.innerHTML = `<label class="mayhen_editor_v3_label">Kiểu dáng khung ảnh</label><select class="mayhen_editor_v3_select" data-ov-frame-select="1"></select>`;
                const sel = frameSelField.querySelector('select');
                catalog.frames.forEach((fr) => {
                    const opt = document.createElement('option');
                    opt.value = fr.id;
                    opt.textContent = fr.label;
                    if (fr.id === ov.asset_id) opt.selected = true;
                    sel.appendChild(opt);
                });
                sel.addEventListener('change', () => {
                    const chosen = catalog.frames.find((f) => f.id === sel.value);
                    const nextAspect = sel.value === 'frame-circle' ? 1 : (Number(chosen?.aspect) || 0.75);
                    mutateOverlayById(ov.id, (item) => ({
                        ...item,
                        asset_id: sel.value,
                        aspect: nextAspect,
                        h: Number(((item.w || 0.46) / nextAspect).toFixed(4)),
                    }));
                });
                propCard.appendChild(frameSelField);
            }

            // Focal Point & Zoom inside Frame when photo exists
            if (hasPhoto) {
                const cFx = ov.crop?.focal_x ?? 50;
                const cFy = ov.crop?.focal_y ?? 50;
                const cZoom = ov.crop?.zoom ?? 1;
                const cropControls = document.createElement('div');
                cropControls.className = 'mayhen_editor_v3_field';
                cropControls.innerHTML = `
                    <div class="mayhen_editor_v3_row">
                        <div class="mayhen_editor_v3_field">
                            <label class="mayhen_editor_v3_label">Dịch ngang <span data-ov-fx-lbl>${cFx}%</span></label>
                            <input type="range" min="0" max="100" step="1" value="${cFx}" data-ov-crop-fx="1">
                        </div>
                        <div class="mayhen_editor_v3_field">
                            <label class="mayhen_editor_v3_label">Dịch dọc <span data-ov-fy-lbl>${cFy}%</span></label>
                            <input type="range" min="0" max="100" step="1" value="${cFy}" data-ov-crop-fy="1">
                        </div>
                    </div>
                    <div class="mayhen_editor_v3_field">
                        <label class="mayhen_editor_v3_label">Phóng ảnh trong khung <span data-ov-zm-lbl>${cZoom}x</span></label>
                        <input type="range" min="1" max="2.5" step="0.05" value="${cZoom}" data-ov-crop-zm="1">
                    </div>
                `;
                const updateOvCrop = (patch) => {
                    mutateOverlayById(ov.id, (item) => ({
                        ...item,
                        crop: {
                            focal_x: item.crop?.focal_x ?? 50,
                            focal_y: item.crop?.focal_y ?? 50,
                            zoom: item.crop?.zoom ?? 1,
                            ...patch,
                        },
                    }));
                };
                cropControls.querySelector('[data-ov-crop-fx]')?.addEventListener('input', (e) => {
                    const v = Number(e.target.value);
                    cropControls.querySelector('[data-ov-fx-lbl]').textContent = `${v}%`;
                    updateOvCrop({ focal_x: v });
                });
                cropControls.querySelector('[data-ov-crop-fy]')?.addEventListener('input', (e) => {
                    const v = Number(e.target.value);
                    cropControls.querySelector('[data-ov-fy-lbl]').textContent = `${v}%`;
                    updateOvCrop({ focal_y: v });
                });
                cropControls.querySelector('[data-ov-crop-zm]')?.addEventListener('input', (e) => {
                    const v = Number(e.target.value);
                    cropControls.querySelector('[data-ov-zm-lbl]').textContent = `${v}x`;
                    updateOvCrop({ zoom: v });
                });
                propCard.appendChild(cropControls);
            }
        }

        // Color Swatches for Sticker / Text / Shape / Frame Border
        const colorField = document.createElement('div');
        colorField.className = 'mayhen_editor_v3_field';
        const curCol = ov.style?.color || ov.style?.stroke || '#C5A059';
        colorField.innerHTML = `<label class="mayhen_editor_v3_label">Màu sắc chủ đạo</label><div class="mayhen_editor_v3_swatches"></div>`;
        const swWrap = colorField.querySelector('.mayhen_editor_v3_swatches');
        PRESET_SWATCHES.forEach((hex) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'mayhen_editor_v3_swatch' + (curCol.toUpperCase() === hex ? ' is-active' : '');
            b.style.backgroundColor = hex;
            b.addEventListener('click', () => {
                mutateOverlayById(ov.id, (item) => ({
                    ...item,
                    style: { ...(item.style || {}), color: hex, stroke: hex },
                }));
            });
            swWrap.appendChild(b);
        });
        propCard.appendChild(colorField);
        inspectorBodyEl.appendChild(propCard);

        // Transform, Size, Alignment & Layer Actions Card
        const layoutCard = document.createElement('div');
        layoutCard.className = 'mayhen_editor_v3_card';
        const curWidthPct = Math.round((Number(ov.w) || 0.36) * 100);
        layoutCard.innerHTML = `
            <div class="mayhen_editor_v3_card_title"><span>Kích Thước, Xoay & Thứ Tự Lớp</span></div>
            <div class="mayhen_editor_v3_field">
                <label class="mayhen_editor_v3_label">Kích thước (Độ rộng) <span data-ov-w-lbl>${curWidthPct}%</span></label>
                <input type="range" min="12" max="90" step="1" value="${curWidthPct}" data-ov-w="1">
            </div>
            <div class="mayhen_editor_v3_row">
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Góc xoay <span>${ov.rotation_deg || 0}°</span></label>
                    <input type="range" min="-180" max="180" step="1" value="${ov.rotation_deg || 0}" data-ov-rot="1">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Độ mờ <span>${ov.opacity ?? 100}%</span></label>
                    <input type="range" min="15" max="100" step="5" value="${ov.opacity ?? 100}" data-ov-op="1">
                </div>
            </div>
            <div class="mayhen_editor_v3_row">
                <button type="button" class="mayhen_editor_v3_btn" data-ov-act="center-x">Căn giữa ngang</button>
                <button type="button" class="mayhen_editor_v3_btn" data-ov-act="center-screen">Đưa về giữa màn</button>
            </div>
            <div class="mayhen_editor_v3_row">
                <button type="button" class="mayhen_editor_v3_btn" data-ov-act="up">↑ Lên một lớp</button>
                <button type="button" class="mayhen_editor_v3_btn" data-ov-act="down">↓ Xuống một lớp</button>
            </div>
            <div class="mayhen_editor_v3_row">
                <button type="button" class="mayhen_editor_v3_btn" data-ov-act="dup">Nhân bản</button>
                <button type="button" class="mayhen_editor_v3_btn" data-ov-act="lock">${ov.locked ? '🔓 Mở khóa' : '🔒 Khóa vị trí'}</button>
            </div>
            <button type="button" class="mayhen_editor_v3_btn mayhen_editor_v3_btn--danger" data-ov-act="del">Xóa phần tử này</button>
        `;

        layoutCard.querySelector('[data-ov-w]')?.addEventListener('input', (e) => {
            const pct = Number(e.target.value);
            layoutCard.querySelector('[data-ov-w-lbl]').textContent = `${pct}%`;
            const nw = Number((pct / 100).toFixed(4));
            mutateOverlayById(ov.id, (item) => {
                const asp = Number(item.aspect) > 0.1 ? Number(item.aspect) : ((item.w || 0.36) / Math.max(0.03, item.h || 0.36));
                return {
                    ...item,
                    w: nw,
                    h: Number((nw / asp).toFixed(4)),
                    aspect: Number(asp.toFixed(4)),
                };
            });
        });
        layoutCard.querySelector('[data-ov-rot]')?.addEventListener('input', (e) => {
            const deg = Number(e.target.value);
            layoutCard.querySelector('[data-ov-rot]').previousElementSibling.querySelector('span').textContent = `${deg}°`;
            mutateOverlayById(ov.id, (item) => ({ ...item, rotation_deg: deg }));
        });
        layoutCard.querySelector('[data-ov-op]')?.addEventListener('input', (e) => {
            const op = Number(e.target.value);
            layoutCard.querySelector('[data-ov-op]').previousElementSibling.querySelector('span').textContent = `${op}%`;
            mutateOverlayById(ov.id, (item) => ({ ...item, opacity: op }));
        });

        layoutCard.querySelectorAll('[data-ov-act]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const act = btn.getAttribute('data-ov-act');
                if (act === 'center-x') mutateOverlayById(ov.id, (item) => ({ ...item, x: 0.5 }));
                else if (act === 'center-screen') {
                    const anc = resolveViewportCenterAnchor();
                    mutateOverlayById(ov.id, (item) => ({ ...item, section_id: anc.sectionId, x: 0.5, y: anc.y }));
                }
                else if (act === 'up') mutateOverlayById(ov.id, (item) => ({ ...item, z_index: Math.min(60, (item.z_index || 12) + 1) }));
                else if (act === 'down') mutateOverlayById(ov.id, (item) => ({ ...item, z_index: Math.max(1, (item.z_index || 12) - 1) }));
                else if (act === 'lock') mutateOverlayById(ov.id, (item) => ({ ...item, locked: !item.locked }));
                else if (act === 'del') mutateOverlayById(ov.id, () => null);
                else if (act === 'dup') {
                    addOverlayItem({
                        ...cloneJson(ov),
                        x: Math.min(0.9, (ov.x || 0.5) + 0.04),
                        y: Math.min(0.92, (ov.y || 0.5) + 0.03),
                    });
                }
            });
        });

        inspectorBodyEl.appendChild(layoutCard);
    }

    function renderDerivedOrSystemInspector(target) {
        inspectorTitleEl.textContent = target.label || 'Thành phần Tự động';
        inspectorSubEl.textContent = 'Được đồng bộ tự động từ dữ liệu cưới chính';
        inspectorTabsEl.style.display = 'none';
        inspectorBodyEl.innerHTML = '';

        const card = document.createElement('div');
        card.className = 'mayhen_editor_v3_card';

        if (target.kind === 'derived_date') {
            const dateInput = document.querySelector('[data-sync-key="wedding.wedding_date"]');
            const curDate = dateInput?.value || '';
            card.innerHTML = `
                <div class="mayhen_editor_v3_card_title"><span>Đổi Ngày Cử Hành Hôn Lễ</span></div>
                <div style="font-size:0.75rem;color:#57534E;">Lịch tháng, Thứ và Đồng hồ đếm ngược sẽ tự động tính toán chính xác khi bạn chọn ngày cưới:</div>
                <input type="date" class="mayhen_editor_v3_input" value="${curDate}" data-v3-direct-date="1">
            `;
            card.querySelector('[data-v3-direct-date]')?.addEventListener('change', (e) => {
                updateCanonicalFieldValue('wedding_date', e.target.value, true);
                showV3Toast('Đã cập nhật Ngày cưới, Lịch & Đếm ngược!');
            });
        } else if (target.kind === 'derived_couple') {
            const gVal = document.querySelector('[data-sync-key="wedding.groom_name"]')?.value || '';
            const bVal = document.querySelector('[data-sync-key="wedding.bride_name"]')?.value || '';
            card.innerHTML = `
                <div class="mayhen_editor_v3_card_title"><span>Tên Đôi Uyên Ương</span></div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Họ tên Chú Rể</label>
                    <input type="text" class="mayhen_editor_v3_input" value="${gVal}" data-couple-inp="groom_name">
                </div>
                <div class="mayhen_editor_v3_field">
                    <label class="mayhen_editor_v3_label">Họ tên Cô Dâu</label>
                    <input type="text" class="mayhen_editor_v3_input" value="${bVal}" data-couple-inp="bride_name">
                </div>
            `;
            card.querySelectorAll('[data-couple-inp]').forEach((inp) => {
                let comp = false;
                inp.addEventListener('compositionstart', () => { comp = true; });
                inp.addEventListener('compositionend', () => {
                    comp = false;
                    updateCanonicalFieldValue(inp.getAttribute('data-couple-inp'), inp.value, true);
                });
                inp.addEventListener('input', (e) => {
                    if (comp || e.isComposing) return;
                    updateCanonicalFieldValue(inp.getAttribute('data-couple-inp'), inp.value, true);
                });
            });
        } else if (target.kind === 'business_map') {
            const formKey = resolveFormKeyForSyncTarget(target.syncKey || 'event_party_map_link');
            const curLink = document.querySelector(`[data-sync-key="${formKey}"]`)?.value || target.href || '';
            card.innerHTML = `
                <div class="mayhen_editor_v3_card_title"><span>Liên Kết Chỉ Đường Google Maps</span></div>
                <input type="url" class="mayhen_editor_v3_input" placeholder="https://maps.google.com/..." value="${curLink}" data-v3-map-inp="1">
            `;
            card.querySelector('[data-v3-map-inp]')?.addEventListener('change', (e) => {
                updateCanonicalFieldValue(target.syncKey || 'event_party_map_link', e.target.value.trim(), true);
                showV3Toast('Đã cập nhật liên kết Google Maps!');
            });
        } else {
            card.innerHTML = `
                <div class="mayhen_editor_v3_card_title"><span>Khu Tương Tác Dành Cho Khách Mời</span></div>
                <div style="font-size:0.76rem;color:#57534E;line-height:1.5;">
                    Trong lúc chỉnh sửa thiệp, các biểu mẫu Xác nhận tham dự (RSVP), Sổ lưu bút và Ký tên được khóa an toàn để tránh tạo dữ liệu thử nhầm lẫn.<br>
                    Bấm nút <strong>"Xem như khách"</strong> trên thanh công cụ để trải nghiệm thực tế.
                </div>
            `;
        }

        inspectorBodyEl.appendChild(card);
    }

    function renderInspector() {
        if (!inspectorEl || !inspectorBodyEl) return;

        inspectorTabsEl?.querySelectorAll('[data-v3-tab]').forEach((btn) => {
            btn.classList.toggle('is-active', btn.getAttribute('data-v3-tab') === activeInspectorTab);
        });

        if (!selectedTarget) {
            inspectorEl.classList.remove('is-mobile-open');
            renderGlobalDefaultInspector();
            return;
        }

        inspectorEl.classList.add('is-mobile-open');

        if (selectedTarget.isOverlay) {
            renderOverlayInspector(selectedTarget);
        } else if (selectedTarget.kind === 'canonical_text') {
            renderCanonicalTextInspector(selectedTarget);
        } else if (selectedTarget.kind === 'canonical_image' || selectedTarget.kind === 'business_qr') {
            renderCanonicalImageInspector(selectedTarget);
        } else {
            renderDerivedOrSystemInspector(selectedTarget);
        }
    }

    // ---- BIND INSPECTOR TABS, CLOSE, GUEST PREVIEW & ZOOM ----
    inspectorTabsEl?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-v3-tab]');
        if (!btn) return;
        activeInspectorTab = btn.getAttribute('data-v3-tab') || 'style';
        renderInspector();
    });

    inspectorCloseBtn?.addEventListener('click', () => {
        selectedTarget = null;
        inspectorEl?.classList.remove('is-mobile-open');
        postToAllIframes({ type: 'MAYHEN_V3_CLEAR_SELECTION' });
        renderInspector();
    });

    guestModeBtn?.addEventListener('click', () => {
        isGuestPreview = !isGuestPreview;
        guestModeBtn.classList.toggle('is-active', isGuestPreview);
        const span = guestModeBtn.querySelector('span');
        if (span) span.textContent = isGuestPreview ? 'Đang xem như khách' : 'Xem như khách';
        if (isGuestPreview) {
            selectedTarget = null;
            renderInspector();
        }
        postToAllIframes({
            type: 'MAYHEN_V3_SET_MODE',
            mode: isGuestPreview ? 'guest_preview' : 'editor',
        });
    });

    document.querySelectorAll('[data-v3-zoom-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const z = btn.getAttribute('data-v3-zoom-btn') || '100';
            if (centerStage) centerStage.setAttribute('data-v3-zoom', z);
            document.querySelectorAll('[data-v3-zoom-btn]').forEach((b) => b.classList.toggle('is-active', b === btn));
        });
    });

    // ---- LISTEN TO STORE UPDATES (UNDO / REDO / DRAFT RECOVERY) ----
    store.subscribe((state, action) => {
        if ([ActionTypes.UNDO, ActionTypes.REDO, ActionTypes.RESTORE_DRAFT].includes(action?.type)) {
            syncHiddenCustomConfigInput();
            postToAllIframes({
                type: 'MAYHEN_V3_SYNC_VISUAL',
                visual_editor: getVisualEditorState(),
            });
            renderDecorationDrawer();
            renderInspector();
        }
    });

    // ---- LISTEN TO IFRAME V3 POSTMESSAGE EVENTS (STRICT ORIGIN + SOURCE CHECK) ----
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return;
        const allowedWindows = getActiveIframes().map((f) => f.contentWindow).filter(Boolean);
        if (!allowedWindows.includes(event.source)) return;
        const data = event.data;
        if (!data || data.protocol !== 'MAYHEN_STUDIO_V3') return;

        switch (data.type) {
            case 'MAYHEN_V3_AGENT_READY': {
                postToAllIframes({
                    type: 'MAYHEN_V3_SYNC_VISUAL',
                    visual_editor: getVisualEditorState(),
                });
                if (isGuestPreview) {
                    postToAllIframes({ type: 'MAYHEN_V3_SET_MODE', mode: 'guest_preview' });
                }
                break;
            }

            case 'MAYHEN_V3_TARGET_SELECTED': {
                selectedTarget = data.target || null;
                activeInspectorTab = 'style';
                renderDecorationDrawer();
                renderInspector();
                if (selectedTarget && typeof window.__MAYHEN_SYNC_LEFT_PANEL_TO_SELECTION === 'function') {
                    window.__MAYHEN_SYNC_LEFT_PANEL_TO_SELECTION({
                        syncKey: selectedTarget.syncKey || '',
                        ownerKey: selectedTarget.ownerKey || '',
                        sectionId: selectedTarget.sectionId || '',
                        isOverlay: Boolean(selectedTarget.isOverlay),
                    });
                }
                break;
            }

            case 'MAYHEN_V3_TARGET_CLEARED': {
                selectedTarget = null;
                renderDecorationDrawer();
                renderInspector();
                break;
            }

            case 'MAYHEN_V3_INLINE_TEXT_INPUT':
            case 'MAYHEN_V3_INLINE_TEXT_COMMIT': {
                if (data.sync_key !== undefined) {
                    // Push to Store & Form WITHOUT echoing back over the active inline DOM node
                    updateCanonicalFieldValue(data.sync_key, data.value ?? '', true);
                    if (selectedTarget && selectedTarget.syncKey === data.sync_key) {
                        selectedTarget.text = data.value ?? '';
                        const liveInp = inspectorBodyEl?.querySelector('[data-v3-live-text]');
                        if (liveInp && document.activeElement !== liveInp) {
                            liveInp.value = data.value ?? '';
                        }
                    }
                }
                break;
            }

            case 'MAYHEN_V3_OVERLAY_TEXT_INPUT':
            case 'MAYHEN_V3_OVERLAY_TEXT_COMMIT': {
                if (data.overlay_id) {
                    const ve = getVisualEditorState();
                    const idx = ve.overlays.findIndex((o) => o.id === data.overlay_id);
                    if (idx !== -1) {
                        ve.overlays[idx].text = data.text ?? '';
                        store.dispatch({
                            type: ActionTypes.UPDATE_CUSTOM_CONFIG,
                            payload: { visual_editor: ve },
                        });
                        syncHiddenCustomConfigInput();
                        if (data.type === 'MAYHEN_V3_OVERLAY_TEXT_COMMIT') {
                            renderDecorationDrawer();
                            renderInspector();
                        }
                    }
                }
                break;
            }

            case 'MAYHEN_V3_OVERLAY_TRANSFORM_COMMIT': {
                if (data.overlay_id && data.patch) {
                    mutateOverlayById(data.overlay_id, (item) => {
                        const updated = { ...item, ...data.patch };
                        if (data.is_desktop_viewport && item.desktop_override) {
                            updated.desktop_override = {
                                ...item.desktop_override,
                                x: data.patch.x ?? item.desktop_override.x,
                                y: data.patch.y ?? item.desktop_override.y,
                                w: data.patch.w ?? item.desktop_override.w,
                                h: data.patch.h ?? item.desktop_override.h,
                            };
                        }
                        return updated;
                    });
                    if (selectedTarget && selectedTarget.overlayId === data.overlay_id) {
                        selectedTarget.safeZoneWarning = Boolean(data.safe_zone_warning);
                        renderInspector();
                    }
                }
                break;
            }

            case 'MAYHEN_V3_QUICK_ACTION': {
                const act = data.action;
                const tgt = data.target || selectedTarget;
                if (!tgt) break;
                selectedTarget = tgt;

                if (act === 'focus-typography' || act === 'focus-focal' || act === 'focus-inspector') {
                    activeInspectorTab = 'style';
                    renderInspector();
                } else if (act === 'focus-motion') {
                    activeInspectorTab = 'motion';
                    renderInspector();
                } else if (act === 'reset-instance') {
                    const nextVe = getVisualEditorState();
                    delete nextVe.instance_styles[tgt.editId];
                    delete nextVe.instance_styles[`sync_key:${tgt.syncKey}`];
                    delete nextVe.instance_animations[tgt.editId];
                    commitVisualEditor(nextVe);
                    renderInspector();
                    showV3Toast('Đã đặt lại phần tử về mặc định mẫu.');
                } else if (act === 'replace-image') {
                    activeInspectorTab = 'style';
                    renderInspector();
                    inspectorBodyEl?.querySelector('[data-v3-replace-img]')?.click();
                } else if (act === 'crop-image') {
                    activeInspectorTab = 'style';
                    renderInspector();
                    inspectorBodyEl?.querySelector('[data-v3-crop-img]')?.click();
                } else if (act === 'replace-overlay-image' && tgt.overlayId) {
                    activeInspectorTab = 'style';
                    renderInspector();
                    inspectorBodyEl?.querySelector('[data-ov-upload]')?.click();
                } else if (act === 'crop-overlay-image' && tgt.overlayId) {
                    activeInspectorTab = 'style';
                    renderInspector();
                    inspectorBodyEl?.querySelector('[data-ov-crop]')?.click();
                } else if (act === 'duplicate-overlay' && tgt.overlayId) {
                    const ov = getVisualEditorState().overlays.find((o) => o.id === tgt.overlayId);
                    if (ov) {
                        addOverlayItem({
                            ...cloneJson(ov),
                            x: Math.min(0.9, (ov.x || 0.5) + 0.04),
                            y: Math.min(0.92, (ov.y || 0.5) + 0.03),
                        });
                    }
                } else if (act === 'layer-up' && tgt.overlayId) {
                    mutateOverlayById(tgt.overlayId, (item) => ({ ...item, z_index: Math.min(60, (item.z_index || 12) + 1) }));
                } else if (act === 'center-overlay' && tgt.overlayId) {
                    mutateOverlayById(tgt.overlayId, (item) => ({ ...item, x: 0.5 }));
                } else if (act === 'delete-overlay' && tgt.overlayId) {
                    mutateOverlayById(tgt.overlayId, () => null);
                    showV3Toast('Đã xóa phần tử trang trí (Ctrl+Z để hoàn tác).');
                } else if (act === 'AttemptDeleteCanonical') {
                    showV3Toast('Đây là phần tử gốc của mẫu thiệp — bạn có thể sửa chữ hoặc ẩn cả mục ở Bố Cục.');
                }
                break;
            }

            case 'MAYHEN_V3_SHORTCUT': {
                if (data.command === 'undo') {
                    document.getElementById('mayhen_btn_undo')?.click();
                } else if (data.command === 'redo') {
                    document.getElementById('mayhen_btn_redo')?.click();
                }
                break;
            }

            default:
                break;
        }
    });

    renderDecorationDrawer();
    renderInspector();

    return {
        refresh: () => {
            renderDecorationDrawer();
            renderInspector();
        },
    };
}
