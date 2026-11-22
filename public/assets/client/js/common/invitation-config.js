import { CURATED_PALETTES } from '../editor/theme-controller.js';
import { createVisualEditorRenderer, playElementMotion } from './visual-editor-renderer.js';

export function createInvitationConfig(doc = document, css = CSS) {
    let config = {};
    const v3Renderer = createVisualEditorRenderer(doc);
    const sections = Array.from(doc.querySelectorAll('[data-mayhen-section]'));
    const originals = new Map(sections.map((section) => [section, {
        parent: section.parentNode,
        display: section.style.getPropertyValue('display'),
        priority: section.style.getPropertyPriority('display'),
    }]));
    const aliases = (section) => {
        const ids = (section.getAttribute('data-mayhen-section') || '').split(',').map((id) => id.trim());
        const canonical = { groom_family: 'parents', bride_family: 'parents', wedding_event: 'events',
            gallery: 'album', interactive: 'ending', signature: 'ending', rsvp: 'ending', wishes: 'ending' };
        return [...new Set([...ids, ...ids.map((id) => canonical[id]).filter(Boolean)])];
    };
    const validColor = (value) => typeof value === 'string' && css.supports('color', value);
    const originalVariables = new Map();
    const waxElements = Array.from(doc.querySelectorAll('[data-mayhen-wax-seal], .mayhen_invitation_wax_seal'));
    const monogramElements = Array.from(doc.querySelectorAll('[data-mayhen-wax-monogram], .mayhen_invitation_wax_initials'));
    const waxOriginals = new Map(waxElements.map((element) => [element, {
        background: element.style.getPropertyValue('background-color'),
        backgroundPriority: element.style.getPropertyPriority('background-color'),
        color: element.style.getPropertyValue('color'),
        colorPriority: element.style.getPropertyPriority('color'),
    }]));
    const monogramOriginals = new Map(monogramElements.map((element) => [element, Array.from(element.childNodes)]));

    function render() {
        const palette = { ...(CURATED_PALETTES[config.theme?.palette_id] || {}), ...(config.theme || {}) };
        const variables = {
            bg: ['--paper', '--paper-dim', '--mayhen_bg_warm', '--dem', '--kinh', '--mayhen-bg-cream', '--mayhen-bg-ivory', '--mayhen-invitation-bg', '--mayhen-invitation-bg-page', '--mayhen-invitation-bg-outer'],
            text: ['--ink', '--mayhen_ink', '--muc', '--mayhen-text-main', '--mayhen-invitation-text', '--mayhen-invitation-text-main'],
            accent: ['--border', '--mayhen_gold', '--mayhen_gold_soft', '--vang-kim', '--mayhen-gold-antique', '--mayhen-invitation-accent', '--mayhen-invitation-gold'],
            primary: ['--primary', '--primary-dark', '--mayhen-sage-main', '--mayhen-sage-dark', '--mayhen-invitation-green', '--mayhen-invitation-red'],
        };
        Object.entries(variables).forEach(([key, names]) => {
            names.forEach((name) => {
                const style = doc.documentElement.style;
                if (!originalVariables.has(name)) originalVariables.set(name, {
                    value: style.getPropertyValue(name), priority: style.getPropertyPriority(name),
                });
                const original = originalVariables.get(name);
                if (validColor(palette[key])) style.setProperty(name, palette[key], 'important');
                else if (original.value) style.setProperty(name, original.value, original.priority);
                else style.removeProperty(name);
            });
        });
        const seal = config.wax_seal || {};
        waxElements.forEach((element) => {
            if (validColor(seal.color)) {
                element.style.setProperty('background-color', seal.color);
                element.style.setProperty('color', seal.color);
            } else {
                const original = waxOriginals.get(element);
                [['background-color', original.background, original.backgroundPriority], ['color', original.color, original.colorPriority]]
                    .forEach(([name, value, priority]) => value ? element.style.setProperty(name, value, priority) : element.style.removeProperty(name));
            }
        });
        monogramElements.forEach((element) => {
            if (typeof seal.monogram === 'string') {
                element.textContent = seal.monogram.slice(0, 4);
            } else element.replaceChildren(...monogramOriginals.get(element));
        });

        const hidden = new Set(Array.isArray(config.sections_hidden) ? config.sections_hidden : []);
        sections.forEach((section) => {
            const original = originals.get(section);
            if (aliases(section).some((id) => hidden.has(id))) section.style.setProperty('display', 'none', 'important');
            else if (original.display) section.style.setProperty('display', original.display, original.priority);
            else section.style.removeProperty('display');
        });
        const order = Array.isArray(config.sections_order) ? config.sections_order : [];
        const groups = new Map();
        sections.forEach((section) => {
            const parent = originals.get(section).parent;
            if (!groups.has(parent)) groups.set(parent, []);
            groups.get(parent).push(section);
        });
        groups.forEach((siblings, parent) => {
            const rank = (section) => {
                const positions = aliases(section).map((id) => order.indexOf(id)).filter((index) => index >= 0);
                return positions.length ? Math.min(...positions) : order.length + sections.indexOf(section);
            };
            const sorted = [...siblings].sort((a, b) => rank(a) - rank(b));
            const current = Array.from(parent.children).filter((element) => siblings.includes(element));
            if (current.every((element, index) => element === sorted[index])) return;
            const anchors = current.map((element) => {
                const anchor = doc.createComment('mayhen-section');
                parent.insertBefore(anchor, element);
                return anchor;
            });
            sorted.forEach((element, index) => parent.insertBefore(element, anchors[index]));
            anchors.forEach((anchor) => anchor.remove());
        });

        const catalog = (typeof window !== 'undefined' && window.__MAYHEN_V3_CATALOG) ? window.__MAYHEN_V3_CATALOG : {};
        const isStudioMode = typeof window !== 'undefined' && Boolean(window.__MAYHEN_IS_STUDIO_EDITOR);
        v3Renderer.apply(config.visual_editor || {}, catalog, { isStudioMode });
    }

    return {
        apply(next) {
            config = next && typeof next === 'object' && !Array.isArray(next) ? next : {};
            render();
        },
        receive(data) {
            if (data.type === 'MAYHEN_CHANGE_THEME') config.theme = { ...(data.palette || {}), palette_id: data.palette_id };
            else if (data.type === 'MAYHEN_UPDATE_WAX_SEAL') config.wax_seal = data.wax_seal || { color: data.color, monogram: data.monogram };
            else if (data.type === 'MAYHEN_REORDER_SECTIONS') {
                if (Array.isArray(data.order)) config.sections_order = data.order;
                if (Array.isArray(data.sections_hidden || data.hidden)) config.sections_hidden = data.sections_hidden || data.hidden;
            } else if (data.type === 'MAYHEN_TOGGLE_SECTION') {
                const hidden = new Set(config.sections_hidden || []);
                if (data.is_hidden) hidden.add(data.section_id);
                else hidden.delete(data.section_id);
                config.sections_hidden = [...hidden];
            } else if (data.type === 'MAYHEN_V3_SYNC_VISUAL') {
                config.visual_editor = data.visual_editor && typeof data.visual_editor === 'object' ? data.visual_editor : {};
            } else if (data.type === 'MAYHEN_V3_PREVIEW_MOTION') {
                const target = data.is_overlay
                    ? doc.querySelector(`[data-overlay-id="${data.target_id}"] .mayhen_invitation_overlay_inner`)
                    : doc.querySelector(`[data-edit-id="${data.target_id}"]`);
                if (target) playElementMotion(target, data.motion);
                return;
            } else return;
            render();
        },
        v3Renderer,
    };
}

export function initInteractionGuard(doc = document, win = window) {
    if (!doc || !win) return;
    if (win.__MAYHEN_IS_EMBED) return;

    const isPreview = Boolean(win.__MAYHEN_IS_PREVIEW);
    const isOwner = Boolean(win.__MAYHEN_IS_OWNER);
    const createUrl = typeof win.__MAYHEN_CREATE_URL === 'string' && win.__MAYHEN_CREATE_URL
        ? win.__MAYHEN_CREATE_URL
        : '/tao-thiep';

    if (isPreview) {
        const lockSelectors = [
            '#mayhen_section_signature',
            '[data-mayhen-section="signature"]',
            '.mayhen_invitation_signature_block',
            '.mayhen_invitation_sig_pad',
            '#mayhen_section_rsvp',
            '[data-mayhen-section="rsvp"]',
            '.mayhen_invitation_rsvp_wrap',
            '#mayhenRsvpForm',
            'form[id*="rsvp" i]',
            'form[class*="rsvp" i]',
            '#mayhen_section_wishes',
            '[data-mayhen-section="wishes"]',
            '.mayhen_invitation_wishes_wrap',
            '#mayhenWishForm',
            'form[id*="wish" i]',
            'form[class*="wish" i]',
        ];

        const rawTargets = Array.from(doc.querySelectorAll(lockSelectors.join(',')));
        const topTargets = rawTargets.filter((el) => !rawTargets.some((other) => other !== el && other.contains(el)));

        const blockEvent = (evt) => {
            if (evt.target && evt.target.closest && evt.target.closest('.mayhen_invitation_preview_lock_banner')) {
                return;
            }
            evt.preventDefault();
            evt.stopImmediatePropagation();
        };

        const createLockBanner = (label) => {
            const banner = doc.createElement('div');
            banner.className = 'mayhen_invitation_preview_lock_banner';
            banner.setAttribute('role', 'note');

            const text = doc.createElement('span');
            text.className = 'mayhen_invitation_preview_lock_text';
            text.textContent = `Chế độ xem thử — ${label} chỉ hoạt động trên thiệp cưới đã được tạo.`;

            const cta = doc.createElement('a');
            cta.className = 'mayhen_invitation_preview_lock_cta';
            cta.href = createUrl;
            cta.textContent = 'Tạo thiệp ngay →';

            banner.appendChild(text);
            banner.appendChild(cta);
            return banner;
        };

        const resolveLabel = (el) => {
            const hay = `${el.id || ''} ${el.className || ''} ${el.getAttribute('data-mayhen-section') || ''}`.toLowerCase();
            if (hay.includes('sig')) return 'Sổ ký tên lưu bút';
            if (hay.includes('rsvp')) return 'Xác nhận tham dự';
            if (hay.includes('wish')) return 'Sổ lời chúc phúc';
            return 'Tính năng tương tác';
        };

        topTargets.forEach((container) => {
            if (container.dataset.mayhenPreviewLocked === '1') return;
            container.dataset.mayhenPreviewLocked = '1';
            container.classList.add('mayhen_invitation_preview_locked');

            container.querySelectorAll('input, textarea, select, button').forEach((ctrl) => {
                ctrl.disabled = true;
                ctrl.setAttribute('aria-disabled', 'true');
                ctrl.setAttribute('tabindex', '-1');
            });

            container.querySelectorAll('canvas').forEach((canvas) => {
                canvas.style.pointerEvents = 'none';
                canvas.setAttribute('aria-disabled', 'true');
            });

            ['submit', 'pointerdown', 'mousedown', 'touchstart', 'click'].forEach((evtName) => {
                container.addEventListener(evtName, blockEvent, true);
            });

            container.prepend(createLockBanner(resolveLabel(container)));
        });

        return;
    }

    if (isOwner) {
        const ownerNoticeTargets = Array.from(doc.querySelectorAll(
            '#mayhenRsvpForm, form[id*="rsvp" i], form[class*="rsvp" i], #mayhenWishForm, form[id*="wish" i], form[class*="wish" i]'
        ));

        ownerNoticeTargets.forEach((form) => {
            if (form.dataset.mayhenOwnerNoticed === '1') return;
            form.dataset.mayhenOwnerNoticed = '1';

            const notice = doc.createElement('div');
            notice.className = 'mayhen_invitation_owner_notice';
            notice.setAttribute('role', 'note');
            notice.textContent = 'Bạn đang xem với tư cách chủ thiệp (không thể tự gửi RSVP/Lời chúc). Hãy mở link thiệp ở tab ẩn danh hoặc gửi cho khách mời để thử nghiệm như khách thật.';
            form.prepend(notice);
        });
    }
}

if (typeof document !== 'undefined' && typeof window !== 'undefined') {
    const renderer = createInvitationConfig();
    window.__MAYHEN_INVITATION_RENDERER = renderer;
    let initial = {};
    try { initial = JSON.parse(document.body.dataset.mayhenInvitationConfig || '{}'); } catch (error) {}
    renderer.apply(initial);
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || ![window.parent, window].includes(event.source)
            || !event.data || typeof event.data !== 'object') return;
        renderer.receive(event.data);
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initInteractionGuard(document, window), { once: true });
    } else {
        initInteractionGuard(document, window);
    }
}
