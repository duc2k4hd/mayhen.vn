import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
    TOUR_STORAGE_VERSION,
    TourPersistence,
    computePopoverPlacement,
    resolveTourStorageKey,
} from '../../public/assets/client/js/editor/product-tour.js';

function createMemoryStorage() {
    const map = new Map();
    return {
        map,
        getItem: (k) => (map.has(k) ? map.get(k) : null),
        setItem: (k, v) => map.set(k, String(v)),
        removeItem: (k) => map.delete(k),
    };
}

test('resolveTourStorageKey isolates guest and multiple authenticated users without PII', () => {
    assert.equal(resolveTourStorageKey(null), `mayhen:editor:product-tour:v${TOUR_STORAGE_VERSION}:guest`);
    assert.equal(resolveTourStorageKey(''), `mayhen:editor:product-tour:v${TOUR_STORAGE_VERSION}:guest`);
    assert.equal(resolveTourStorageKey(15), `mayhen:editor:product-tour:v${TOUR_STORAGE_VERSION}:u_15`);
    assert.equal(resolveTourStorageKey(88), `mayhen:editor:product-tour:v${TOUR_STORAGE_VERSION}:u_88`);
    assert.notEqual(resolveTourStorageKey(15), resolveTourStorageKey(88));
});

test('TourPersistence auto-starts first time, resumes in_progress on reload, and stops auto-start after skip or finish', () => {
    const localStore = createMemoryStorage();
    const sessionStore = createMemoryStorage();
    const p = new TourPersistence({ userId: 42, localStore, sessionStore });

    // First visit
    assert.equal(p.read(), null);
    assert.equal(p.shouldAutoStart(), true);

    // Mid-tour F5 reload resumes cleanly without marking completed
    p.markInProgress('step-story-ai');
    assert.equal(p.shouldAutoStart(), true);
    assert.equal(p.read()?.status, 'in_progress');
    assert.equal(p.read()?.last_step_id, 'step-story-ai');

    // Skip prevents future auto-start
    p.markSkipped('step-story-ai');
    assert.equal(p.shouldAutoStart(), false);
    assert.equal(p.read()?.status, 'skipped');

    // Another account on the same browser still gets first-time onboarding
    const otherAccount = new TourPersistence({ userId: 99, localStore, sessionStore });
    assert.equal(otherAccount.shouldAutoStart(), true);
    otherAccount.markCompleted();
    assert.equal(otherAccount.shouldAutoStart(), false);
    assert.equal(otherAccount.read()?.status, 'completed');

    // Verify stored payload contains zero wedding PII fields
    const raw = localStore.getItem(p.key);
    assert.equal(raw.includes('groom_name'), false);
    assert.equal(raw.includes('bride_name'), false);
});

test('TourPersistence degrades gracefully without throwing or looping when localStorage and sessionStorage are disabled', () => {
    const brokenStorage = {
        getItem() { throw new Error('SecurityError: Storage disabled'); },
        setItem() { throw new Error('QuotaExceededError'); },
    };
    const p = new TourPersistence({ userId: null, localStore: brokenStorage, sessionStore: brokenStorage });

    assert.equal(p.shouldAutoStart(), true);
    assert.doesNotThrow(() => p.markSkipped('studio-overview'));
    assert.equal(p.shouldAutoStart(), false);
    assert.equal(p.read()?.status, 'skipped');
});

test('computePopoverPlacement flips sides cleanly on desktop and docks safely inside mobile viewports without overflow', () => {
    // Desktop left drawer target -> places popover to the right
    const rightPlace = computePopoverPlacement({
        targetRect: { left: 72, top: 120, right: 500, bottom: 520, width: 428, height: 400 },
        popoverWidth: 360,
        popoverHeight: 210,
        viewportWidth: 1440,
        viewportHeight: 900,
        preferredSide: 'right',
    });
    assert.equal(rightPlace.side, 'right');
    assert.ok(rightPlace.left >= 510);
    assert.ok(rightPlace.left + 360 <= 1440 - 12);

    // Desktop right inspector target -> flips to the left
    const leftPlace = computePopoverPlacement({
        targetRect: { left: 1108, top: 64, right: 1440, bottom: 880, width: 332, height: 816 },
        popoverWidth: 360,
        popoverHeight: 210,
        viewportWidth: 1440,
        viewportHeight: 900,
        preferredSide: 'left',
    });
    assert.equal(leftPlace.side, 'left');
    assert.ok(leftPlace.left + 360 <= 1108 - 10);

    // Mobile 320px top target -> docks at bottom within viewport bounds
    const mobileBottom = computePopoverPlacement({
        targetRect: { left: 10, top: 68, right: 310, bottom: 210, width: 300, height: 142 },
        popoverWidth: 296,
        popoverHeight: 190,
        viewportWidth: 320,
        viewportHeight: 568,
        preferredSide: 'bottom',
    });
    assert.equal(mobileBottom.side, 'mobile-bottom');
    assert.equal(mobileBottom.left, 12);
    assert.ok(mobileBottom.top + 190 <= 568);

    // Mobile 390px bottom target -> flips to top so target is never covered
    const mobileTop = computePopoverPlacement({
        targetRect: { left: 16, top: 680, right: 374, bottom: 810, width: 358, height: 130 },
        popoverWidth: 366,
        popoverHeight: 195,
        viewportWidth: 390,
        viewportHeight: 844,
        preferredSide: 'top',
    });
    assert.equal(mobileTop.side, 'mobile-top');
    assert.ok(mobileTop.top < 120);
});
