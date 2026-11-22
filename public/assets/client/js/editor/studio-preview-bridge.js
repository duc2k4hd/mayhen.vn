/**
 * Deep StudioPreviewBridge Module
 * Single seam for Studio V2 & V3 iframe postMessage transport and
 * canonical sync-target to form-key resolution.
 */

const WEDDING_ROOT_FIELDS = new Set([
    'groom_name',
    'bride_name',
    'groom_parents',
    'bride_parents',
    'wedding_date',
    'venue_name',
    'venue_address',
    'story',
    'cover_photo_url',
    'music_url',
    'bank_name',
    'bank_account_number',
    'bank_account_name',
]);

const SYNC_TARGET_ALIASES = {
    hero_photo_url: 'wedding.cover_photo_url',
    groom_qr_url: 'wedding.extra_data.qr_groom_image',
    bride_qr_url: 'wedding.extra_data.qr_bride_image',
    couple_photo_url: 'wedding.extra_data.save_date_photo_url',
};

/**
 * Resolve a preview `data-sync-target` key into its canonical Studio form `data-sync-key`.
 */
export function resolveFormKeyForSyncTarget(syncKey) {
    const clean = String(syncKey || '').trim();
    if (!clean) return '';
    if (SYNC_TARGET_ALIASES[clean]) return SYNC_TARGET_ALIASES[clean];
    if (WEDDING_ROOT_FIELDS.has(clean)) return `wedding.${clean}`;
    if (clean.startsWith('wedding.')) return clean;
    if (clean.startsWith('extra_data.')) return `wedding.${clean}`;
    return `wedding.extra_data.${clean}`;
}

/**
 * Build ordered candidate keys when locating the matching left-panel input for a selected preview node.
 */
export function buildFieldLookupCandidates(syncKey = '', ownerKey = '') {
    const candidates = [];
    const pushUnique = (k) => {
        if (k && !candidates.includes(k)) candidates.push(k);
    };

    pushUnique(ownerKey);
    if (syncKey) {
        pushUnique(resolveFormKeyForSyncTarget(syncKey));
        pushUnique(syncKey);
        pushUnique(`wedding.${syncKey}`);
        pushUnique(`wedding.extra_data.${syncKey}`);
        if (/^gallery\.\d+$/i.test(syncKey)) {
            pushUnique('wedding.extra_data.gallery');
            pushUnique('gallery');
        }
    }

    return candidates;
}

/**
 * Escape a string for safe inclusion in a CSS attribute selector.
 */
export function escapeCssAttr(val) {
    return String(val || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
}

/**
 * Create a single-channel same-origin iframe bridge shared by Studio V2 and V3 controllers.
 */
export function createStudioPreviewBridge({ getIframes, origin } = {}) {
    const resolveIframes = () => (typeof getIframes === 'function' ? getIframes() : []).filter(Boolean);
    const resolveOrigin = () => origin || (typeof window !== 'undefined' ? window.location.origin : '');

    function dispatch(directFnName, directArgs, postPayload) {
        const targetOrigin = resolveOrigin();
        resolveIframes().forEach((iframe) => {
            if (!iframe || !iframe.contentWindow) return;
            try {
                if (postPayload) {
                    iframe.contentWindow.postMessage(postPayload, targetOrigin);
                    return;
                }
                if (directFnName && typeof iframe.contentWindow[directFnName] === 'function') {
                    iframe.contentWindow[directFnName].apply(iframe.contentWindow, directArgs);
                }
            } catch {
                // Safe origin guard
            }
        });
    }

    function postV3(payload) {
        dispatch(null, null, {
            protocol: 'MAYHEN_STUDIO_V3',
            version: 3,
            ...payload,
        });
    }

    return {
        dispatch,
        postV3,
        syncInput(key, value) {
            dispatch('__mayhenSyncInput', [key, value], { type: 'MAYHEN_SYNC_INPUT', key, value });
        },
        scrollToSection(sectionId, step) {
            if (!sectionId && !step) return;
            dispatch('__mayhenScrollToSection', [sectionId, step], {
                type: 'MAYHEN_SCROLL_TO_SECTION',
                section_id: sectionId,
                step,
            });
        },
        scrollToTarget(target, action = 'focus', step = 1) {
            if (!target) return;
            dispatch('__mayhenScrollToTarget', [target, action, step], {
                type: 'MAYHEN_SCROLL_TO_TARGET',
                target,
                action,
                step,
            });
        },
    };
}
