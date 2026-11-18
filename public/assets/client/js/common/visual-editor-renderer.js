/**
 * MÂY HẸN — STUDIO VISUAL EDITOR V3 SHARED RENDERER
 * Dùng chung 100% giữa Preview Iframe (Studio) và Thiệp Công Khai (/thiep/{slug}).
 * Không chứa bất kỳ khung chọn hay tay nắm chỉnh sửa nào để bản công khai nhẹ & sạch tuyệt đối.
 */

export const FONT_STACKS = {
    'editorial-serif': "'MayhenV3EditorialSerif', Georgia, 'Times New Roman', serif",
    'royal-serif': "'MayhenV3RoyalSerif', Georgia, 'Times New Roman', serif",
    'classic-display': "'MayhenV3ClassicDisplay', Georgia, 'Times New Roman', serif",
    'philosopher-serif': "'MayhenV3Philosopher', Georgia, serif",
    'modern-sans': "'MayhenV3ModernSans', system-ui, -apple-system, sans-serif",
    'humanist-sans': "'MayhenV3HumanistSans', system-ui, -apple-system, sans-serif",
    'jura-geometric': "'MayhenV3Jura', system-ui, sans-serif",
    'romantic-calligraphy': "'MayhenV3Calligraphy', cursive",
    'wedding-signature': "'MayhenV3Signature', cursive",
    'indulge-script': "'MayhenV3Indulge', cursive",
    'pattaya-script': "'MayhenV3Pattaya', cursive",
    'editorial-condensed': "'MayhenV3Condensed', Georgia, serif",
};

export const SECTION_CANONICAL_MAP = {
    opening: 'opening',
    hero: 'hero',
    couple: 'hero',
    story: 'story',
    quote: 'story',
    parents: 'parents',
    groom_family: 'parents',
    bride_family: 'parents',
    countdown_calendar: 'countdown_calendar',
    countdown: 'countdown_calendar',
    calendar: 'countdown_calendar',
    events: 'events',
    wedding_event: 'events',
    timeline: 'timeline',
    dresscode: 'dresscode',
    album: 'album',
    gallery: 'album',
    gift: 'gift',
    signature: 'ending',
    rsvp: 'ending',
    wishes: 'ending',
    interactive: 'ending',
    ending: 'ending',
};

const BUILTIN_STICKERS = {
    'wax-seal-champagne': '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="52" fill="currentColor" opacity="0.92"/><circle cx="60" cy="60" r="42" stroke="#F8EFE2" stroke-width="2" stroke-dasharray="4 3"/><path d="M60 38C53 30 39 34 39 46C39 59 60 72 60 80C60 72 81 59 81 46C81 34 67 30 60 38Z" fill="#F8EFE2" opacity="0.9"/></svg>',
    'wedding-rings-gold': '<svg viewBox="0 0 140 104" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="52" cy="58" r="28" stroke="currentColor" stroke-width="4.5"/><circle cx="88" cy="58" r="28" stroke="currentColor" stroke-width="4.5"/><path d="M88 22L95 30L88 38L81 30L88 22Z" fill="currentColor"/></svg>',
    'botanical-olive': '<svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 78C56 64 98 44 142 20" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><ellipse cx="48" cy="52" rx="14" ry="6" transform="rotate(-32 48 52)" fill="currentColor" opacity="0.82"/><ellipse cx="66" cy="66" rx="14" ry="6" transform="rotate(18 66 66)" fill="currentColor" opacity="0.82"/><ellipse cx="86" cy="36" rx="14" ry="6" transform="rotate(-28 86 36)" fill="currentColor" opacity="0.82"/><ellipse cx="104" cy="48" rx="14" ry="6" transform="rotate(20 104 48)" fill="currentColor" opacity="0.82"/><ellipse cx="124" cy="22" rx="12" ry="5" transform="rotate(-24 124 22)" fill="currentColor" opacity="0.82"/></svg>',
    'double-happiness-royal': '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="54" stroke="currentColor" stroke-width="3"/><path d="M26 36H54M40 24V56M28 48H52M30 62H50V74H30V62ZM24 82H96M30 90H50V102H30V90ZM66 36H94M80 24V56M68 48H92M70 62H90V74H70V62ZM70 90H90V102H70V90Z" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'celestial-starburst': '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M60 10L68 48L106 60L68 72L60 110L52 72L14 60L52 48L60 10Z" fill="currentColor"/><circle cx="28" cy="28" r="3.5" fill="currentColor"/><circle cx="92" cy="32" r="2.8" fill="currentColor"/><circle cx="90" cy="92" r="3.2" fill="currentColor"/></svg>',
    'silk-ribbon-bow': '<svg viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M70 46C52 22 20 20 20 42C20 62 50 58 70 46ZM70 46C88 22 120 20 120 42C120 62 90 58 70 46ZM70 46L44 88M70 46L96 88" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="70" cy="46" r="7" fill="currentColor"/></svg>',
    'love-heart-minimal': '<svg viewBox="0 0 120 110" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M60 94C60 94 18 66 18 38C18 23 30 14 43 14C51 14 57 19 60 25C63 19 69 14 77 14C90 14 102 23 102 38C102 66 60 94 60 94Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/></svg>',
    'vintage-corner-flourish': '<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 104V28C16 21.3726 21.3726 16 28 16H104" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M28 84V34C28 30.6863 30.6863 28 34 28H84" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" opacity="0.75"/><circle cx="28" cy="28" r="5" fill="currentColor"/></svg>',
    'dove-pair': '<svg viewBox="0 0 150 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M58 58C44 58 30 48 22 30C36 32 46 38 52 44C50 32 54 22 64 18C64 30 68 42 68 50C68 56 64 58 58 58Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="M92 58C106 58 120 48 128 30C114 32 104 38 98 44C100 32 96 22 86 18C86 30 82 42 82 50C82 56 86 58 92 58Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/></svg>',
    'divider-floral-ornate': '<svg viewBox="0 0 240 56" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 28H92M148 28H228" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M120 12L130 28L120 44L110 28L120 12Z" fill="currentColor"/><circle cx="100" cy="28" r="3" fill="currentColor"/><circle cx="140" cy="28" r="3" fill="currentColor"/></svg>',
};

const BUILTIN_SHAPES = {
    'pill-badge': { fill: 'rgba(255,253,249,0.88)', stroke: '#C5A059', radius: '999px' },
    'arch-dome': { fill: 'rgba(248,242,232,0.82)', stroke: '#C5A059', radius: '999px 999px 12px 12px' },
    'circle-medallion': { fill: 'rgba(255,253,249,0.85)', stroke: '#C5A059', radius: '50%' },
    'rect-editorial': { fill: 'rgba(255,253,249,0.78)', stroke: '#8A6F4D', radius: '12px' },
    'line-gold': { fill: '#C5A059', stroke: '#C5A059', radius: '999px' },
};

const BUILTIN_FRAMES = {
    'frame-arch': 'arch',
    'frame-oval': 'oval',
    'frame-circle': 'circle',
    'frame-polaroid': 'polaroid',
    'frame-royal-gold': 'royal-gold',
    'frame-scallop': 'scallop',
    'frame-film-strip': 'film-strip',
    'frame-soft-rect': 'soft-rect',
};

export function resolveCanonicalSectionId(sectionEl) {
    if (!sectionEl) return 'hero';
    const curated = sectionEl.getAttribute('data-curated-section');
    if (curated && SECTION_CANONICAL_MAP[curated]) {
        return SECTION_CANONICAL_MAP[curated];
    }
    const raw = (sectionEl.getAttribute('data-mayhen-section') || '').split(',').map((s) => s.trim()).filter(Boolean);
    for (const token of raw) {
        if (SECTION_CANONICAL_MAP[token]) {
            return SECTION_CANONICAL_MAP[token];
        }
    }
    if (sectionEl.classList?.contains('mayhen_invitation_opening') || sectionEl.classList?.contains('mayhen_invitation_sr_cua')) {
        return 'opening';
    }
    return raw[0] || 'hero';
}

export function cleanSyncKey(rawKey) {
    return String(rawKey || '')
        .trim()
        .replace(/^wedding\.extra_data\./, '')
        .replace(/^wedding\./, '')
        .replace(/^extra_data\./, '')
        .replace(/[^a-zA-Z0-9_.\-]/g, '_');
}

/**
 * Gán `data-edit-id` tất định cho toàn bộ phần tử `[data-sync-target]` trên thiệp.
 */
export function assignDeterministicEditIds(doc = document) {
    const counters = new Map();
    const targets = Array.from(doc.querySelectorAll('[data-sync-target]'));

    targets.forEach((el) => {
        if (el.tagName === 'AUDIO' || el.tagName === 'SOURCE') return;
        const sectionEl = el.closest('[data-mayhen-section], [data-curated-section], .mayhen_invitation_opening, .mayhen_invitation_sr_cua');
        const sectionId = resolveCanonicalSectionId(sectionEl);
        const key = cleanSyncKey(el.getAttribute('data-sync-target'));
        if (!key) return;
        const prefix = `${sectionId}__${key}`;
        const idx = counters.get(prefix) || 0;
        counters.set(prefix, idx + 1);
        const editId = `${prefix}__${idx}`;
        el.setAttribute('data-edit-id', editId);
        el.setAttribute('data-edit-section', sectionId);
    });

    return targets;
}

export function findSectionHost(doc, sectionId) {
    if (!sectionId) return doc.querySelector('[data-mayhen-section]');
    const normalized = SECTION_CANONICAL_MAP[sectionId] || sectionId;
    const allSections = Array.from(doc.querySelectorAll('[data-mayhen-section], [data-curated-section]'));
    const exact = allSections.find((sec) => {
        const tokens = (sec.getAttribute('data-mayhen-section') || '').split(',').map((s) => s.trim());
        const cur = sec.getAttribute('data-curated-section');
        return tokens.includes(sectionId) || cur === sectionId;
    });
    if (exact) return exact;
    return allSections.find((sec) => resolveCanonicalSectionId(sec) === normalized) || allSections[0] || null;
}

function isSafeColor(val) {
    if (typeof val !== 'string') return false;
    const t = val.trim();
    return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(t)
        || /^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/.test(t);
}

function isSafeAssetUrl(val) {
    if (typeof val !== 'string' || !val.trim()) return false;
    const t = val.trim();
    const low = t.toLowerCase();
    if (low.startsWith('javascript:') || low.startsWith('vbscript:')) return false;
    if (t.startsWith('/storage/') || t.startsWith('/assets/') || t.startsWith('/mau-thiep/')) return true;
    if (low.startsWith('http://') || low.startsWith('https://')) return true;
    return false;
}

export function playElementMotion(el, motion) {
    if (!el || !motion || !motion.preset || motion.preset === 'none') {
        if (el) {
            el.removeAttribute('data-v3-motion');
            el.removeAttribute('data-v3-motion-ready');
            el.classList.remove('mayhen_v3_motion_active');
        }
        return;
    }

    el.style.removeProperty('transform');
    el.style.removeProperty('opacity');
    el.setAttribute('data-v3-motion', motion.preset);
    el.style.setProperty('--mh-mot-dur', `${Number(motion.duration_ms) || 800}ms`);
    el.style.setProperty('--mh-mot-delay', `${Number(motion.delay_ms) || 0}ms`);
    el.style.setProperty('--mh-mot-ease', motion.easing || 'cubic-bezier(0.22, 1, 0.36, 1)');
    el.style.setProperty('--mh-mot-iter', motion.repeat === 'infinite' ? 'infinite' : '1');

    el.classList.remove('mayhen_v3_motion_active');
    void el.offsetWidth;
    el.classList.add('mayhen_v3_motion_active');
}

export function createVisualEditorRenderer(doc = document) {
    const savedInlineStyles = new Map();
    const savedMotionTargets = new Set();
    let motionObserver = null;

    const TRACKED_PROPS = [
        'font-family',
        'font-size',
        'font-weight',
        'font-style',
        'line-height',
        'letter-spacing',
        'text-align',
        'text-transform',
        'color',
        'opacity',
        'object-position',
        'background-position',
        'transform',
        'transform-origin',
    ];

    function rememberOriginal(el) {
        if (savedInlineStyles.has(el)) return;
        const snapshot = {};
        TRACKED_PROPS.forEach((prop) => {
            snapshot[prop] = {
                value: el.style.getPropertyValue(prop),
                priority: el.style.getPropertyPriority(prop),
            };
        });
        savedInlineStyles.set(el, snapshot);
    }

    function restoreOriginal(el) {
        const snapshot = savedInlineStyles.get(el);
        if (!snapshot) return;
        TRACKED_PROPS.forEach((prop) => {
            const entry = snapshot[prop];
            if (entry && entry.value) {
                el.style.setProperty(prop, entry.value, entry.priority);
            } else {
                el.style.removeProperty(prop);
            }
        });
    }

    function applyGlobalTypography(globalStyle = {}) {
        let styleEl = typeof doc.getElementById === 'function' ? doc.getElementById('mayhenV3LiveGlobalTypography') : null;
        const headingStack = FONT_STACKS[globalStyle.heading_font_id] || '';
        const bodyStack = FONT_STACKS[globalStyle.body_font_id] || '';
        const accentStack = FONT_STACKS[globalStyle.accent_font_id] || '';

        if (!headingStack && !bodyStack && !accentStack) {
            if (styleEl) styleEl.textContent = '';
            return;
        }

        if (!styleEl && doc.head && typeof doc.createElement === 'function') {
            styleEl = doc.createElement('style');
            styleEl.id = 'mayhenV3LiveGlobalTypography';
            doc.head.appendChild(styleEl);
        }
        if (!styleEl) return;

        const rules = [];
        if (headingStack) {
            rules.push(
                `--font-heading: ${headingStack} !important;`,
                `--font-serif: ${headingStack} !important;`,
                `--f-than: ${headingStack} !important;`,
                `--mayhen-font-serif: ${headingStack} !important;`,
                `--mayhen-font-display: ${headingStack} !important;`,
                `--mayhen-invitation-heading-font: ${headingStack} !important;`,
                `--mayhen-invitation-serif-font: ${headingStack} !important;`,
                `--mh-f-phi: ${headingStack} !important;`,
                `--mh-f-lus: ${headingStack} !important;`
            );
        }
        if (bodyStack) {
            rules.push(
                `--font-body: ${bodyStack} !important;`,
                `--font-sans: ${bodyStack} !important;`,
                `--mayhen-font-sans: ${bodyStack} !important;`,
                `--mayhen-invitation-body-font: ${bodyStack} !important;`,
                `--mh-f-jura: ${bodyStack} !important;`
            );
        }
        if (accentStack) {
            rules.push(
                `--font-script: ${accentStack} !important;`,
                `--f-script: ${accentStack} !important;`,
                `--mayhen-font-script: ${accentStack} !important;`,
                `--mayhen-invitation-script-font: ${accentStack} !important;`,
                `--mh-f-script: ${accentStack} !important;`,
                `--mh-f-pattaya: ${accentStack} !important;`
            );
        }

        styleEl.textContent = `:root, html body, .mayhen_invitation_root, .mayhen_invitation_app, .mayhen_invitation_ladi_wraper { ${rules.join(' ')} }`;
    }

    function applyStyleRuleToElement(el, rule) {
        rememberOriginal(el);
        restoreOriginal(el);
        if (!rule || typeof rule !== 'object') return;

        if (rule.font_id && FONT_STACKS[rule.font_id]) {
            el.style.setProperty('font-family', FONT_STACKS[rule.font_id], 'important');
        }
        if (Number.isFinite(Number(rule.font_size_px))) {
            el.style.setProperty('font-size', `${Math.max(10, Math.min(96, Number(rule.font_size_px)))}px`, 'important');
            el.setAttribute('data-v3-custom-fontsize', '1');
        } else {
            el.removeAttribute('data-v3-custom-fontsize');
        }
        if (Number.isFinite(Number(rule.font_weight))) {
            el.style.setProperty('font-weight', String(rule.font_weight), 'important');
        }
        if (rule.font_style === 'italic' || rule.font_style === 'normal') {
            el.style.setProperty('font-style', rule.font_style, 'important');
        }
        if (Number.isFinite(Number(rule.line_height))) {
            el.style.setProperty('line-height', String(rule.line_height), 'important');
        }
        if (Number.isFinite(Number(rule.letter_spacing_em))) {
            el.style.setProperty('letter-spacing', `${Number(rule.letter_spacing_em)}em`, 'important');
        }
        if (['left', 'center', 'right', 'justify'].includes(rule.text_align)) {
            el.style.setProperty('text-align', rule.text_align, 'important');
        }
        if (['none', 'uppercase', 'capitalize'].includes(rule.text_transform)) {
            el.style.setProperty('text-transform', rule.text_transform, 'important');
        }
        if (isSafeColor(rule.color)) {
            el.style.setProperty('color', rule.color.trim(), 'important');
        }
        if (Number.isFinite(Number(rule.opacity))) {
            el.style.setProperty('opacity', String(Math.max(0.1, Math.min(1, Number(rule.opacity) / 100))), 'important');
        }
    }

    function applyImageOverrideToElement(el, override) {
        rememberOriginal(el);
        restoreOriginal(el);
        if (!override || typeof override !== 'object') return;

        const fx = Number.isFinite(Number(override.focal_x)) ? Math.max(0, Math.min(100, Number(override.focal_x))) : 50;
        const fy = Number.isFinite(Number(override.focal_y)) ? Math.max(0, Math.min(100, Number(override.focal_y))) : 50;
        const zoom = Number.isFinite(Number(override.zoom)) ? Math.max(1, Math.min(3, Number(override.zoom))) : 1;

        if (el.tagName === 'IMG') {
            el.style.setProperty('object-position', `${fx}% ${fy}%`, 'important');
            if (zoom > 1.001) {
                const clipParent = el.parentElement;
                if (clipParent && clipParent !== doc.body) {
                    clipParent.style.overflow = clipParent.style.overflow || 'hidden';
                }
                el.style.setProperty('transform-origin', `${fx}% ${fy}%`, 'important');
                el.style.setProperty('transform', `scale(${zoom})`, 'important');
            }
        } else {
            el.style.setProperty('background-position', `${fx}% ${fy}%`, 'important');
            if (zoom > 1.001) {
                el.style.setProperty('background-size', `${Math.round(zoom * 100)}%`, 'important');
            }
        }

        if (Number.isFinite(Number(override.opacity))) {
            el.style.setProperty('opacity', String(Math.max(0.1, Math.min(1, Number(override.opacity) / 100))), 'important');
        }
    }

    function renderOverlays(overlays = [], catalog = {}, isStudioMode = false) {
        doc.querySelectorAll('.mayhen_invitation_overlay_layer').forEach((layer) => layer.remove());
        doc.querySelectorAll('.mayhen_invitation_has_overlays').forEach((sec) => sec.classList.remove('mayhen_invitation_has_overlays'));

        if (!Array.isArray(overlays) || overlays.length === 0) return;

        const stickerLookup = new Map(Object.entries(BUILTIN_STICKERS));
        if (Array.isArray(catalog?.stickers)) {
            catalog.stickers.forEach((s) => {
                if (s?.id && s?.svg) stickerLookup.set(s.id, s.svg);
            });
        }

        const bySection = new Map();
        overlays.forEach((ov) => {
            if (!ov || typeof ov !== 'object') return;
            if (!isStudioMode && ov.hidden) return;
            const secId = ov.section_id || 'hero';
            if (!bySection.has(secId)) bySection.set(secId, []);
            bySection.get(secId).push(ov);
        });

        bySection.forEach((items, secId) => {
            const host = findSectionHost(doc, secId);
            if (!host) return;
            host.classList.add('mayhen_invitation_has_overlays');

            const layer = doc.createElement('div');
            layer.className = 'mayhen_invitation_overlay_layer';
            layer.setAttribute('data-overlay-section', secId);

            items.forEach((ov) => {
                const outer = doc.createElement('div');
                outer.className = 'mayhen_invitation_overlay_item';
                outer.setAttribute('data-overlay-id', ov.id);
                outer.setAttribute('data-overlay-type', ov.type);
                outer.setAttribute('data-overlay-section', secId);
                if (ov.locked) outer.setAttribute('data-ov-locked', '1');
                if (ov.hidden) outer.setAttribute('data-ov-hidden', '1');

                outer.style.setProperty('--mh-ov-x', String(ov.x ?? 0.5));
                outer.style.setProperty('--mh-ov-y', String(ov.y ?? 0.5));
                outer.style.setProperty('--mh-ov-w', String(ov.w ?? 0.28));
                outer.style.setProperty('--mh-ov-h', String(ov.h ?? 0.18));
                outer.style.setProperty('--mh-ov-rot', `${Number(ov.rotation_deg) || 0}deg`);
                outer.style.setProperty('--mh-ov-z', String(ov.z_index ?? 12));
                outer.style.setProperty('--mh-ov-opacity', String(Math.max(0.1, Math.min(1, (Number(ov.opacity ?? 100)) / 100))));

                if (ov.type !== 'overlay_text') {
                    outer.setAttribute('data-ov-has-height', '1');
                }

                if (ov.desktop_override && typeof ov.desktop_override === 'object') {
                    outer.setAttribute('data-ov-desktop', '1');
                    outer.style.setProperty('--mh-ov-dx', String(ov.desktop_override.x ?? ov.x ?? 0.5));
                    outer.style.setProperty('--mh-ov-dy', String(ov.desktop_override.y ?? ov.y ?? 0.5));
                    outer.style.setProperty('--mh-ov-dw', String(ov.desktop_override.w ?? ov.w ?? 0.28));
                    outer.style.setProperty('--mh-ov-dh', String(ov.desktop_override.h ?? ov.h ?? 0.18));
                }

                const inner = doc.createElement('div');
                inner.className = 'mayhen_invitation_overlay_inner';

                if (ov.type === 'overlay_text') {
                    const p = doc.createElement('div');
                    p.className = 'mayhen_invitation_overlay_text_box';
                    p.textContent = ov.text || 'Chạm đúp để nhập chữ';
                    applyStyleRuleToElement(p, ov.style || {});
                    inner.appendChild(p);
                } else if (ov.type === 'overlay_sticker' || ov.type === 'overlay_divider') {
                    const wrap = doc.createElement('div');
                    wrap.className = 'mayhen_invitation_overlay_svg';
                    const color = isSafeColor(ov.style?.color) ? ov.style.color : '#C5A059';
                    wrap.style.color = color;
                    wrap.innerHTML = stickerLookup.get(ov.asset_id) || BUILTIN_STICKERS['wax-seal-champagne'];
                    inner.appendChild(wrap);
                } else if (ov.type === 'overlay_shape') {
                    const preset = BUILTIN_SHAPES[ov.asset_id] || BUILTIN_SHAPES['pill-badge'];
                    const box = doc.createElement('div');
                    box.className = 'mayhen_invitation_overlay_shape_box';
                    box.style.background = isSafeColor(ov.style?.fill) ? ov.style.fill : preset.fill;
                    box.style.border = `1.5px solid ${isSafeColor(ov.style?.stroke || ov.style?.color) ? (ov.style.stroke || ov.style.color) : preset.stroke}`;
                    box.style.borderRadius = preset.radius;
                    inner.appendChild(box);
                } else if (ov.type === 'overlay_frame' || ov.type === 'overlay_image') {
                    const mod = ov.type === 'overlay_frame' ? (BUILTIN_FRAMES[ov.asset_id] || 'arch') : 'soft-rect';
                    const frameBox = doc.createElement('div');
                    frameBox.className = `mayhen_invitation_overlay_frame_box mayhen_invitation_overlay_frame_box--${mod}`;
                    if (isSafeColor(ov.style?.stroke || ov.style?.color)) {
                        frameBox.style.setProperty('--mh-frame-stroke', ov.style.stroke || ov.style.color);
                    }
                    if (isSafeAssetUrl(ov.image_url)) {
                        const img = doc.createElement('img');
                        img.src = ov.image_url;
                        img.alt = '';
                        img.loading = 'lazy';
                        if (ov.crop) {
                            frameBox.style.setProperty('--mh-crop-x', `${ov.crop.focal_x ?? 50}%`);
                            frameBox.style.setProperty('--mh-crop-y', `${ov.crop.focal_y ?? 50}%`);
                            frameBox.style.setProperty('--mh-crop-zoom', String(ov.crop.zoom ?? 1));
                        }
                        frameBox.appendChild(img);
                    } else if (isStudioMode) {
                        const placeholder = doc.createElement('span');
                        placeholder.style.cssText = 'font-size:11px;color:#7A6A58;text-align:center;padding:6px;';
                        placeholder.textContent = 'Chạm để chọn ảnh';
                        frameBox.appendChild(placeholder);
                    }
                    inner.appendChild(frameBox);
                }

                if (ov.motion && ov.motion.preset && ov.motion.preset !== 'none') {
                    bindMotionRule(inner, ov.motion, isStudioMode);
                }

                outer.appendChild(inner);
                layer.appendChild(outer);
            });

            host.appendChild(layer);
        });
    }

    function bindMotionRule(el, motion, isStudioMode) {
        if (!el || !motion || !motion.preset || motion.preset === 'none') return;
        savedMotionTargets.add(el);
        el.setAttribute('data-v3-motion', motion.preset);
        el.style.setProperty('--mh-mot-dur', `${Number(motion.duration_ms) || 800}ms`);
        el.style.setProperty('--mh-mot-delay', `${Number(motion.delay_ms) || 0}ms`);
        el.style.setProperty('--mh-mot-ease', motion.easing || 'cubic-bezier(0.22, 1, 0.36, 1)');
        el.style.setProperty('--mh-mot-iter', motion.repeat === 'infinite' ? 'infinite' : '1');

        if (isStudioMode || motion.trigger === 'on_load' || typeof IntersectionObserver === 'undefined') {
            el.classList.add('mayhen_v3_motion_active');
            return;
        }

        el.setAttribute('data-v3-motion-ready', '1');
        if (!motionObserver) {
            motionObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('mayhen_v3_motion_active');
                        if (entry.target.style.getPropertyValue('--mh-mot-iter') !== 'infinite') {
                            motionObserver?.unobserve(entry.target);
                        }
                    }
                });
            }, { threshold: 0.12 });
        }
        motionObserver.observe(el);
    }

    function apply(visualEditor = {}, catalog = {}, options = {}) {
        const isStudioMode = Boolean(options.isStudioMode);
        assignDeterministicEditIds(doc);

        // Reset previously mutated canonical elements before re-applying current visual_editor state
        savedInlineStyles.forEach((_, el) => {
            if (el.isConnected) restoreOriginal(el);
        });
        savedMotionTargets.forEach((el) => {
            if (el.isConnected) {
                el.removeAttribute('data-v3-motion');
                el.removeAttribute('data-v3-motion-ready');
                el.classList.remove('mayhen_v3_motion_active');
            }
        });
        savedMotionTargets.clear();
        if (motionObserver) {
            motionObserver.disconnect();
            motionObserver = null;
        }

        const ve = visualEditor && typeof visualEditor === 'object' ? visualEditor : {};

        // 1. Global typography
        applyGlobalTypography(ve.global_style || {});

        // 2. Instance styles (supports both exact `instance_id` and `sync_key:*` rules)
        const instanceStyles = ve.instance_styles && typeof ve.instance_styles === 'object' ? ve.instance_styles : {};
        Object.entries(instanceStyles).forEach(([targetKey, rule]) => {
            if (targetKey.startsWith('sync_key:')) {
                const syncKey = targetKey.slice(9);
                doc.querySelectorAll(`[data-sync-target="${syncKey}"]`).forEach((el) => applyStyleRuleToElement(el, rule));
            } else {
                const el = doc.querySelector(`[data-edit-id="${targetKey}"]`);
                if (el) applyStyleRuleToElement(el, rule);
            }
        });

        // 3. Non-destructive image overrides
        const imageOverrides = ve.image_overrides && typeof ve.image_overrides === 'object' ? ve.image_overrides : {};
        Object.entries(imageOverrides).forEach(([targetKey, override]) => {
            const el = doc.querySelector(`[data-edit-id="${targetKey}"]`) || doc.querySelector(`[data-sync-target="${targetKey}"]`);
            if (el) applyImageOverrideToElement(el, override);
        });

        // 4. Canonical element animations
        const instanceAnimations = ve.instance_animations && typeof ve.instance_animations === 'object' ? ve.instance_animations : {};
        Object.entries(instanceAnimations).forEach(([targetKey, motion]) => {
            const el = doc.querySelector(`[data-edit-id="${targetKey}"]`);
            if (el) bindMotionRule(el, motion, isStudioMode);
        });

        // 5. Free decoration overlays
        renderOverlays(ve.overlays || [], catalog, isStudioMode);
    }

    return {
        apply,
        playElementMotion,
        assignDeterministicEditIds: () => assignDeterministicEditIds(doc),
    };
}
