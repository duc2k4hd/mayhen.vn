import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
    assignDeterministicEditIds,
    cleanSyncKey,
    FONT_STACKS,
    resolveCanonicalSectionId,
} from '../../public/assets/client/js/common/visual-editor-renderer.js';

function createMockElement(tag, attrs = {}, children = []) {
    const attrMap = new Map(Object.entries(attrs));
    const styleProps = new Map();
    const classes = new Set((attrs.class || '').split(/\s+/).filter(Boolean));
    const el = {
        tagName: tag.toUpperCase(),
        parentElement: null,
        isConnected: true,
        textContent: attrs.textContent || '',
        innerHTML: '',
        offsetWidth: 100,
        children: [],
        classList: {
            add: (...tokens) => tokens.forEach((t) => classes.add(t)),
            remove: (...tokens) => tokens.forEach((t) => classes.delete(t)),
            contains: (t) => classes.has(t),
            toggle: (t, force) => {
                const next = force !== undefined ? Boolean(force) : !classes.has(t);
                if (next) classes.add(t);
                else classes.delete(t);
                return next;
            },
        },
        style: {
            overflow: '',
            color: '',
            background: '',
            border: '',
            borderRadius: '',
            setProperty: (k, v, p = '') => styleProps.set(k, { value: String(v), priority: p }),
            getPropertyValue: (k) => styleProps.get(k)?.value || '',
            getPropertyPriority: (k) => styleProps.get(k)?.priority || '',
            removeProperty: (k) => styleProps.delete(k),
        },
        getAttribute: (k) => (attrMap.has(k) ? attrMap.get(k) : null),
        setAttribute: (k, v) => attrMap.set(k, String(v)),
        removeAttribute: (k) => attrMap.delete(k),
        hasAttribute: (k) => attrMap.has(k),
        appendChild(child) {
            child.parentElement = el;
            el.children.push(child);
            return child;
        },
        remove() {
            if (!el.parentElement) return;
            el.parentElement.children = el.parentElement.children.filter((c) => c !== el);
            el.parentElement = null;
        },
        closest(selector) {
            let cur = el;
            while (cur) {
                if (matchesSelector(cur, selector)) return cur;
                cur = cur.parentElement;
            }
            return null;
        },
        querySelectorAll(selector) {
            const out = [];
            const walk = (node) => {
                node.children.forEach((c) => {
                    if (matchesSelector(c, selector)) out.push(c);
                    walk(c);
                });
            };
            walk(el);
            return out;
        },
        querySelector(selector) {
            return el.querySelectorAll(selector)[0] || null;
        },
    };
    children.forEach((c) => el.appendChild(c));
    return el;
}

function matchesSelector(node, selectorGroup) {
    return selectorGroup.split(',').map((s) => s.trim()).some((sel) => {
        if (sel === '[data-sync-target]') return node.hasAttribute('data-sync-target');
        if (sel === '[data-mayhen-section]') return node.hasAttribute('data-mayhen-section');
        if (sel === '[data-curated-section]') return node.hasAttribute('data-curated-section');
        if (sel.startsWith('.')) return node.classList.contains(sel.slice(1));
        const attrEq = sel.match(/^\[([a-zA-Z0-9_-]+)="([^"]+)"\]$/);
        if (attrEq) return node.getAttribute(attrEq[1]) === attrEq[2];
        return false;
    });
}

test('assignDeterministicEditIds stamps unique repeatable edit IDs per canonical section', () => {
    const heroGroom = createMockElement('h1', { 'data-sync-target': 'groom_name' });
    const heroSection = createMockElement('section', { 'data-mayhen-section': 'hero,couple' }, [heroGroom]);

    const parentsGroom1 = createMockElement('h3', { 'data-sync-target': 'groom_name' });
    const parentsGroom2 = createMockElement('p', { 'data-sync-target': 'wedding.groom_name' });
    const parentsSection = createMockElement('section', { 'data-mayhen-section': 'groom_family,bride_family' }, [parentsGroom1, parentsGroom2]);

    const audioEl = createMockElement('audio', { 'data-sync-target': 'music_url' });
    const docRoot = createMockElement('body', {}, [heroSection, parentsSection, audioEl]);

    assignDeterministicEditIds(docRoot);

    assert.equal(heroGroom.getAttribute('data-edit-id'), 'hero__groom_name__0');
    assert.equal(parentsGroom1.getAttribute('data-edit-id'), 'parents__groom_name__0');
    assert.equal(parentsGroom2.getAttribute('data-edit-id'), 'parents__groom_name__1');
    assert.equal(audioEl.getAttribute('data-edit-id'), null);
});

test('cleanSyncKey and resolveCanonicalSectionId normalize legacy and curated keys accurately', () => {
    assert.equal(cleanSyncKey('wedding.extra_data.gallery.4'), 'gallery.4');
    assert.equal(cleanSyncKey('wedding.groom_name'), 'groom_name');

    const curatedEvents = createMockElement('section', {
        'data-curated-section': 'events',
        'data-mayhen-section': 'wedding_event',
    });
    assert.equal(resolveCanonicalSectionId(curatedEvents), 'events');
    assert.ok(FONT_STACKS['editorial-serif'].includes('MayhenV3EditorialSerif'));
    assert.ok(FONT_STACKS['indulge-script'].includes('MayhenV3Indulge'));
});
