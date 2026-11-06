import { CURATED_PALETTES } from '../editor/theme-controller.js';

export function createInvitationConfig(doc = document, css = CSS) {
    let config = {};
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
            } else return;
            render();
        },
    };
}

if (typeof document !== 'undefined' && typeof window !== 'undefined') {
    const renderer = createInvitationConfig();
    let initial = {};
    try { initial = JSON.parse(document.body.dataset.mayhenInvitationConfig || '{}'); } catch (error) {}
    renderer.apply(initial);
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || ![window.parent, window].includes(event.source)
            || !event.data || typeof event.data !== 'object') return;
        renderer.receive(event.data);
    });
}
