import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { Autosaver } from '../../public/assets/client/js/editor/autosaver.js';

let savedGlobals;
let storage;

beforeEach(() => {
    savedGlobals = {
        window: globalThis.window,
        document: globalThis.document,
        localStorage: globalThis.localStorage,
        fetch: globalThis.fetch,
    };
    storage = new Map();
    globalThis.window = { addEventListener() {} };
    globalThis.document = { querySelector: () => null };
    globalThis.localStorage = {
        setItem: (key, value) => storage.set(key, value),
        getItem: (key) => storage.get(key) ?? null,
        removeItem: (key) => storage.delete(key),
    };
});

afterEach(() => {
    Object.assign(globalThis, savedGlobals);
});

function makeStore() {
    const state = {
        invitation_id: 42,
        template_id: 1,
        template_slug: 'test-template',
        revision_id: 1,
        wedding: { groom_name: 'Minh' },
        custom_config: {},
        ui: { isDirty: true, saveStatus: 'idle' },
    };

    return {
        state,
        statuses: [],
        getState() { return this.state; },
        setSaveStatus(status) {
            this.statuses.push(status);
            this.state.ui.saveStatus = status;
            if (status === 'saved') this.state.ui.isDirty = false;
        },
        updateRevision(revision) { this.state.revision_id = revision; },
    };
}

function makeResponse(status, data) {
    return { status, ok: status >= 200 && status < 300, json: async () => data };
}

test('saves changes made during a pending request with the new revision', async () => {
    const store = makeStore();
    const requests = [];
    globalThis.fetch = (url, options) => new Promise((resolve) => {
        requests.push({ body: JSON.parse(options.body), resolve });
    });
    const autosaver = new Autosaver(store, { debounceMs: 60000 });

    autosaver.schedule();
    const firstSave = autosaver.syncToServer();
    store.state.wedding.groom_name = 'Tuấn';
    autosaver.schedule();
    await autosaver.syncToServer();
    assert.equal(requests.length, 1);

    requests[0].resolve(makeResponse(200, { success: true, new_revision_id: 2 }));
    await firstSave;

    assert.equal(requests.length, 2);
    assert.equal(store.state.ui.saveStatus, 'saving');
    assert.equal(store.state.ui.isDirty, true);
    assert.equal(requests[0].body.wedding_delta.groom_name, 'Minh');
    assert.equal(requests[1].body.wedding_delta.groom_name, 'Tuấn');
    assert.equal(requests[1].body.revision_id, 2);
    assert.equal(store.statuses.includes('saved'), false);

    requests[1].resolve(makeResponse(200, { success: true, new_revision_id: 3 }));
    await new Promise(setImmediate);

    assert.equal(store.state.ui.saveStatus, 'saved');
    assert.equal(store.state.ui.isDirty, false);
    assert.equal(JSON.parse(storage.get('mayhen_draft_42')).revision_id, 3);
});

test('preserves a conflict and does not replay later edits automatically', async () => {
    const store = makeStore();
    const requests = [];
    globalThis.fetch = (url, options) => new Promise((resolve) => {
        requests.push({ body: JSON.parse(options.body), resolve });
    });
    const autosaver = new Autosaver(store, { debounceMs: 60000 });

    autosaver.schedule();
    const firstSave = autosaver.syncToServer();
    store.state.wedding.groom_name = 'Tuấn';
    autosaver.schedule();
    requests[0].resolve(makeResponse(409, { success: false, current_revision: 2 }));
    await firstSave;

    store.state.wedding.groom_name = 'Quân';
    autosaver.schedule();
    await autosaver.syncToServer();

    assert.equal(requests.length, 1);
    assert.equal(store.state.ui.saveStatus, 'conflict');
    assert.equal(store.state.ui.isDirty, true);
    assert.equal(JSON.parse(storage.get('mayhen_draft_42')).wedding.groom_name, 'Quân');
});

test('keeps a failed save dirty with a local draft', async () => {
    const store = makeStore();
    globalThis.fetch = async () => makeResponse(500, { success: false });
    const autosaver = new Autosaver(store, { debounceMs: 60000 });

    autosaver.schedule();
    await autosaver.syncToServer();

    assert.equal(store.state.ui.saveStatus, 'offline');
    assert.equal(store.state.ui.isDirty, true);
    assert.equal(JSON.parse(storage.get('mayhen_draft_42')).wedding.groom_name, 'Minh');
});
