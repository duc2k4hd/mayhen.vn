import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { Autosaver } from '../../public/assets/client/js/editor/autosaver.js';
import { TemplateSwitcher } from '../../public/assets/client/js/editor/template-switcher.js';
import { EditorStore } from '../../public/assets/client/js/editor/store.js';
import { ActionTypes } from '../../public/assets/client/js/editor/actions.js';

let savedGlobals;
let storage;

beforeEach(() => {
    savedGlobals = {
        window: globalThis.window,
        document: globalThis.document,
        localStorage: globalThis.localStorage,
        sessionStorage: globalThis.sessionStorage,
        fetch: globalThis.fetch,
    };
    storage = new Map();
    globalThis.window = { addEventListener() {}, location: { href: 'https://example.test/tao-thiep' } };
    globalThis.document = { querySelector: () => null, querySelectorAll: () => [] };
    globalThis.localStorage = {
        setItem: (key, value) => storage.set(key, value),
        getItem: (key) => storage.get(key) ?? null,
        removeItem: (key) => storage.delete(key),
    };
    globalThis.sessionStorage = {
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

test('saved editor configuration is loaded without replacing it with defaults', () => {
    const savedConfig = { theme: { palette_id: 'sage-botanic' }, sections_hidden: ['story'] };
    const store = new EditorStore({ custom_config: EditorStore.parseCustomConfig(JSON.stringify(savedConfig)) });

    assert.deepEqual(store.getState().custom_config.theme, savedConfig.theme);
    assert.deepEqual(store.getState().custom_config.sections_hidden, ['story']);
    assert.deepEqual(EditorStore.parseCustomConfig('[]'), {});
    assert.deepEqual(EditorStore.parseCustomConfig('{invalid'), {});
});

test('autosave retains the saved configuration when a wedding field changes', async () => {
    const savedConfig = { theme: { palette_id: 'sage-botanic' }, sections_hidden: ['story'] };
    const store = new EditorStore({ invitation_id: 42, revision_id: 1, custom_config: EditorStore.parseCustomConfig(JSON.stringify(savedConfig)) });
    store.autosaver.debounceMs = 60000;
    let request;
    globalThis.fetch = async (url, options) => {
        request = JSON.parse(options.body);
        return makeResponse(200, { success: true, new_revision_id: 2 });
    };

    store.dispatch({ type: ActionTypes.UPDATE_FIELD, payload: { key: 'wedding.groom_name', value: 'Minh' } });
    await store.autosaver.syncToServer();

    assert.deepEqual(request.draft_config.theme, savedConfig.theme);
    assert.deepEqual(request.draft_config.sections_hidden, ['story']);
});

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

test('keeps changes and pauses when a successful response has no valid next revision', async () => {
    for (const nextRevision of [undefined, 1, 3, '2']) {
        const store = makeStore();
        let requests = 0;
        globalThis.fetch = async () => {
            requests += 1;
            return makeResponse(200, { success: true, new_revision_id: nextRevision });
        };
        const autosaver = new Autosaver(store, { debounceMs: 60000 });

        autosaver.schedule();
        await autosaver.syncToServer();
        assert.equal(store.state.ui.saveStatus, 'conflict');
        assert.equal(store.state.ui.isDirty, true);
        assert.equal(store.state.revision_id, 1);
        assert.equal(JSON.parse(storage.get('mayhen_draft_42')).wedding.groom_name, 'Minh');
        await autosaver.syncToServer();
        assert.equal(requests, 1);
    }
});

test('switching templates stops when the browser cannot store the transfer draft', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    const messages = [];
    switcher.showToast = (message) => messages.push(message);
    switcher.closeModal = () => assert.fail('the modal must remain open');
    globalThis.sessionStorage.setItem = () => { throw new Error('Storage full'); };

    switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });

    assert.equal(window.location.href, 'https://example.test/tao-thiep');
    assert.match(messages[0], /Không thể lưu dữ liệu/);
});

test('switching templates stops for an unresolved conflict or a temporary image', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    const messages = [];
    switcher.showToast = (message) => messages.push(message);
    switcher.closeModal = () => assert.fail('the modal must remain open');
    switcher.store.autosaver = { hasConflict: true };
    switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });
    assert.match(messages[0], /xung đột/);

    switcher.store.autosaver.hasConflict = false;
    document.querySelectorAll = () => [{ getAttribute: () => 'wedding.cover_photo_url', value: 'blob:pending' }];
    switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });
    assert.match(messages[1], /Ảnh đang được xử lý/);
    assert.equal(storage.has('mayhen_transferred_draft'), false);
});

test('switching templates preserves uploaded local image paths', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    switcher.showToast = () => {};
    switcher.closeModal = () => {};
    document.querySelectorAll = () => [
        { getAttribute: () => 'wedding.cover_photo_url', value: '/storage/photos/a.webp' },
        { getAttribute: () => 'wedding.extra_data.gallery.0', value: '/uploads/b.webp' },
    ];
    const originalSetTimeout = globalThis.setTimeout;
    let navigate;
    globalThis.setTimeout = (callback) => { navigate = callback; };
    try {
        switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });
        const draft = JSON.parse(storage.get('mayhen_transferred_draft'));
        assert.equal(draft.invitation_id, 42);
        assert.equal(draft.revision_id, 1);
        assert.equal(draft.destination_template_slug, 'new-template');
        assert.equal(draft.wedding['wedding.cover_photo_url'], '/storage/photos/a.webp');
        assert.equal(draft.wedding['wedding.extra_data.gallery.0'], '/uploads/b.webp');
        navigate();
        assert.equal(window.location.href, 'https://example.test/tao-thiep?template=new-template');
    } finally {
        globalThis.setTimeout = originalSetTimeout;
    }
});

test('switching templates keeps cleared fields and their aliases empty', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    switcher.store.state.wedding.groom_name = 'Tên cũ';
    switcher.showToast = () => {};
    switcher.closeModal = () => {};
    document.querySelectorAll = () => [
        { getAttribute: () => 'wedding.groom_name', value: '' },
        { getAttribute: () => 'wedding.extra_data.groom_bank_account', value: '' },
        { getAttribute: () => 'wedding.extra_data.groom_account_number', value: 'Giá trị cũ' },
        { getAttribute: () => 'wedding.extra_data.bride_bank_account', value: '' },
    ];
    const originalSetTimeout = globalThis.setTimeout;
    globalThis.setTimeout = () => {};
    try {
        switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });
        const draft = JSON.parse(storage.get('mayhen_transferred_draft'));
        assert.equal(draft.wedding['wedding.groom_name'], '');
        assert.equal(draft.wedding['wedding.extra_data.groom_bank_account'], '');
        assert.equal(draft.wedding['wedding.extra_data.groom_account_number'], 'Giá trị cũ');
        assert.equal(draft.wedding['wedding.extra_data.bride_account_number'], '');
    } finally {
        globalThis.setTimeout = originalSetTimeout;
    }
});

test('switching an existing invitation waits for its pending autosave', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    switcher.store.autosaver = { isSaving: true };
    const messages = [];
    switcher.showToast = (message) => messages.push(message);
    switcher.closeModal = () => assert.fail('the modal must remain open');

    switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });
    assert.match(messages[0], /đang được lưu/);
    assert.equal(storage.has('mayhen_transferred_draft'), false);
});

test('switching templates backs up an existing transfer before replacing it', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    switcher.showToast = () => {};
    switcher.closeModal = () => {};
    const original = '{"wedding":{"groom_name":"Bản cũ"}}';
    storage.set('mayhen_transferred_draft', original);
    storage.set('mayhen_skip_transferred_draft', '1');
    const originalSetTimeout = globalThis.setTimeout;
    globalThis.setTimeout = () => {};
    try {
        switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });
        const backup = [...storage.entries()].find(([key]) => key.startsWith('mayhen_transferred_draft_recovery_'));
        assert.equal(backup?.[1], original);
        assert.equal(JSON.parse(storage.get('mayhen_transferred_draft')).destination_template_slug, 'new-template');
        assert.equal(storage.has('mayhen_skip_transferred_draft'), false);
    } finally {
        globalThis.setTimeout = originalSetTimeout;
    }
});

test('switching templates preserves the existing transfer when its backup fails', () => {
    const switcher = Object.create(TemplateSwitcher.prototype);
    switcher.store = makeStore();
    const messages = [];
    switcher.showToast = (message) => messages.push(message);
    switcher.closeModal = () => assert.fail('the modal must remain open');
    const original = '{"wedding":{"groom_name":"Bản cũ"}}';
    storage.set('mayhen_transferred_draft', original);
    globalThis.sessionStorage.setItem = () => { throw new Error('Storage full'); };

    switcher.switchTemplate({ id: 2, slug: 'new-template', name: 'Mẫu mới' });

    assert.equal(storage.get('mayhen_transferred_draft'), original);
    assert.match(messages[0], /Không thể lưu dữ liệu/);
    assert.equal(window.location.href, 'https://example.test/tao-thiep');
});

test('a matching transfer draft is accepted for the correct invitation and target', () => {
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: { groom_name: 'Minh' } });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(makeStore());

    assert.equal(autosaver.protectStaleTransferDraft(1, 'new-template'), false);
    assert.equal(autosaver.validatedTransferDraft.raw, raw);
    assert.equal(autosaver.hasConflict, false);
});

test('transfer recovery saves a complete local snapshot before allowing autosave', () => {
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: {} });
    storage.set('mayhen_transferred_draft', raw);
    const store = makeStore();
    const autosaver = new Autosaver(store, { debounceMs: 60000 });
    autosaver.protectStaleTransferDraft(1, 'new-template');
    autosaver.isRestoringTransfer = true;
    store.state.wedding.groom_name = 'Bản đang khôi phục';
    autosaver.schedule();
    assert.equal(storage.has('mayhen_draft_42'), false);
    assert.equal(autosaver.timer, null);

    assert.equal(autosaver.finishTransferRecovery(), true);
    assert.equal(JSON.parse(storage.get('mayhen_draft_42')).wedding.groom_name, 'Bản đang khôi phục');
    assert.equal(storage.get('mayhen_transferred_draft'), raw);
    assert.equal(storage.get('mayhen_skip_transferred_draft'), '1');
    clearTimeout(autosaver.timer);
});

test('transfer recovery pauses and keeps the original if local storage fails', () => {
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: {} });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(makeStore());
    autosaver.protectStaleTransferDraft(1, 'new-template');
    autosaver.isRestoringTransfer = true;
    globalThis.localStorage.setItem = () => { throw new Error('Storage full'); };

    assert.equal(autosaver.finishTransferRecovery(), false);
    assert.equal(autosaver.hasConflict, true);
    assert.equal(autosaver.timer, null);
    assert.equal(autosaver.protectedTransferDraft.raw, raw);
    assert.equal(storage.get('mayhen_transferred_draft'), raw);
    assert.equal(storage.has('mayhen_skip_transferred_draft'), false);
});

test('transfer recovery refuses to overwrite or skip a changed transfer', () => {
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: {} });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(makeStore());
    autosaver.protectStaleTransferDraft(1, 'new-template');
    autosaver.isRestoringTransfer = true;
    storage.set('mayhen_transferred_draft', 'new transfer');

    assert.equal(autosaver.finishTransferRecovery(), false);
    assert.equal(storage.get('mayhen_transferred_draft'), 'new transfer');
    assert.equal(storage.has('mayhen_skip_transferred_draft'), false);
    assert.equal(autosaver.hasConflict, true);
});

test('transfer recovery pauses if the session cannot mark the draft as restored', () => {
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: {} });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(makeStore());
    autosaver.protectStaleTransferDraft(1, 'new-template');
    autosaver.isRestoringTransfer = true;
    globalThis.sessionStorage.setItem = () => { throw new Error('Session unavailable'); };

    assert.equal(autosaver.finishTransferRecovery(), false);
    assert.equal(autosaver.hasConflict, true);
    assert.equal(storage.get('mayhen_transferred_draft'), raw);
    assert.equal(storage.has('mayhen_skip_transferred_draft'), false);
    assert.equal(autosaver.timer, null);
});

test('an interrupted transfer recovery stops autosave and keeps the original draft', () => {
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: {} });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(makeStore());
    autosaver.protectStaleTransferDraft(1, 'new-template');
    autosaver.isRestoringTransfer = true;
    autosaver.schedule();

    autosaver.failTransferRecovery();
    autosaver.schedule();
    assert.equal(autosaver.hasConflict, true);
    assert.equal(autosaver.timer, null);
    assert.equal(autosaver.protectedTransferDraft.raw, raw);
    assert.equal(storage.get('mayhen_transferred_draft'), raw);
    assert.equal(storage.has('mayhen_draft_42'), false);
});

test('a new invitation can restore its matching transfer draft', () => {
    const store = makeStore();
    store.state.invitation_id = null;
    store.state.revision_id = 0;
    const raw = JSON.stringify({ invitation_id: null, revision_id: 0, destination_template_slug: 'new-template', wedding: {} });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(store);

    assert.equal(autosaver.protectStaleTransferDraft(0, 'new-template'), false);
    assert.equal(autosaver.validatedTransferDraft.raw, raw);
});

test('old or invalid transfer drafts are retained and stop autosave', async () => {
    for (const raw of [
        '{invalid json',
        JSON.stringify({ wedding: { groom_name: 'Mẫu cũ' } }),
        JSON.stringify({ invitation_id: 99, revision_id: 1, destination_template_slug: 'new-template', wedding: {} }),
        JSON.stringify({ invitation_id: 42, revision_id: 0, destination_template_slug: 'new-template', wedding: {} }),
        JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'other', wedding: {} }),
        JSON.stringify({ invitation_id: 42, revision_id: 1, destination_template_slug: 'new-template', wedding: [] }),
    ]) {
        storage.set('mayhen_transferred_draft', raw);
        const autosaver = new Autosaver(makeStore());
        assert.equal(autosaver.protectStaleTransferDraft(1, 'new-template'), true);
        autosaver.schedule();
        await autosaver.syncToServer();
        assert.equal(autosaver.hasConflict, true);
        assert.equal(storage.get('mayhen_transferred_draft'), raw);
        assert.equal(autosaver.protectedTransferDraft.raw, raw);
    }
});

test('continuing with the server keeps the transfer in the current tab but skips restoring it', () => {
    const raw = JSON.stringify({ wedding: { groom_name: 'Bản cũ' } });
    storage.set('mayhen_transferred_draft', raw);
    const autosaver = new Autosaver(makeStore());
    autosaver.protectStaleTransferDraft(1, 'new-template');

    assert.equal(autosaver.suppressProtectedTransferDraft(), true);
    assert.equal(storage.get('mayhen_transferred_draft'), raw);
    assert.equal(storage.get('mayhen_skip_transferred_draft'), '1');
    assert.equal(new Autosaver(makeStore()).protectStaleTransferDraft(1, 'new-template'), false);
});

test('a changed transfer draft is not suppressed', () => {
    storage.set('mayhen_transferred_draft', '{"wedding":{}}');
    const autosaver = new Autosaver(makeStore());
    autosaver.protectStaleTransferDraft(1, 'new-template');
    storage.set('mayhen_transferred_draft', 'new draft');

    assert.equal(autosaver.suppressProtectedTransferDraft(), false);
    assert.equal(storage.get('mayhen_transferred_draft'), 'new draft');
    assert.equal(storage.has('mayhen_skip_transferred_draft'), false);
});

test('a stale local draft pauses autosave and cannot be overwritten while protected', async () => {
    const store = makeStore();
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 0, wedding: { groom_name: 'Bản cũ' } });
    storage.set('mayhen_draft_42', raw);
    let requests = 0;
    globalThis.fetch = async () => { requests += 1; };
    const autosaver = new Autosaver(store, { debounceMs: 60000 });

    assert.equal(autosaver.protectStaleLocalDraft(1), true);
    store.state.wedding.groom_name = 'Bản máy chủ';
    autosaver.schedule();
    await autosaver.syncToServer();

    assert.equal(storage.get('mayhen_draft_42'), raw);
    assert.equal(autosaver.hasConflict, true);
    assert.equal(requests, 0);
});

test('a matching local draft remains eligible for normal recovery', () => {
    const store = makeStore();
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 1, wedding: { groom_name: 'Bản nháp' } });
    storage.set('mayhen_draft_42', raw);
    const autosaver = new Autosaver(store);

    assert.equal(autosaver.protectStaleLocalDraft(1), false);
    assert.equal(autosaver.hasConflict, false);
    assert.equal(autosaver.getLocalDraft(42).wedding.groom_name, 'Bản nháp');
});

test('drafts with malformed data or an unknown revision are protected', () => {
    for (const raw of [
        '{invalid json',
        JSON.stringify({ invitation_id: 42, wedding: { groom_name: 'Bản cũ' } }),
        JSON.stringify({ invitation_id: 42, revision_id: 2 }),
        JSON.stringify({ invitation_id: 99, revision_id: 1 }),
    ]) {
        storage.set('mayhen_draft_42', raw);
        const autosaver = new Autosaver(makeStore());
        assert.equal(autosaver.protectStaleLocalDraft(1), true);
        assert.equal(autosaver.protectedLocalDraft.raw, raw);
        assert.equal(storage.get('mayhen_draft_42'), raw);
    }
});

test('continuing with server data first preserves the stale draft in a recovery copy', () => {
    const store = makeStore();
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 0, wedding: { groom_name: 'Bản cũ' } });
    storage.set('mayhen_draft_42', raw);
    const autosaver = new Autosaver(store);
    autosaver.protectStaleLocalDraft(1);

    assert.equal(autosaver.archiveProtectedLocalDraft(), true);
    assert.equal(storage.has('mayhen_draft_42'), false);
    const backups = [...storage.entries()].filter(([key]) => key.startsWith('mayhen_draft_42_recovery_'));
    assert.equal(backups.length, 1);
    assert.equal(backups[0][1], raw);
});

test('a stale draft is retained if backup storage fails or another tab changes it', () => {
    const store = makeStore();
    const raw = JSON.stringify({ invitation_id: 42, revision_id: 0, wedding: { groom_name: 'Bản cũ' } });
    storage.set('mayhen_draft_42', raw);
    const autosaver = new Autosaver(store);
    autosaver.protectStaleLocalDraft(1);
    const originalSetItem = localStorage.setItem;
    localStorage.setItem = () => { throw new Error('Storage full'); };
    assert.equal(autosaver.archiveProtectedLocalDraft(), false);
    assert.equal(storage.get('mayhen_draft_42'), raw);

    localStorage.setItem = originalSetItem;
    storage.set('mayhen_draft_42', 'newer draft');
    assert.equal(autosaver.archiveProtectedLocalDraft(), false);
    assert.equal(storage.get('mayhen_draft_42'), 'newer draft');
});
