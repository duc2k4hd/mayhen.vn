import { test } from 'node:test';
import assert from 'node:assert/strict';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { spawn, spawnSync } from 'node:child_process';
import { DatabaseSync } from 'node:sqlite';
import { assess } from '../../docs/docs-center/docs-autopilot-preflight.mjs';

test('a selected TODO can be completed without changing unrelated progress', () => {
    const directory = mkdtempSync(path.join(tmpdir(), 'mayhen-queue-select-'));
    const env = { ...process.env, MAYHEN_DOCS_QUEUE_DB: path.join(directory, 'queue.sqlite') };
    const script = path.resolve('docs/docs-center/docs-queue.mjs');
    const run = (...args) => spawnSync(process.execPath, [script, ...args], { env, encoding: 'utf8' });
    try {
        assert.equal(run('init').status, 0);
        assert.equal(JSON.parse(run('claim', 'CLI-START-002').stdout).claimed, false);
        const claim = JSON.parse(run('claim', 'CLI-START-003').stdout);
        assert.equal(claim.id, 'CLI-START-003');
        assert.equal(run('finish', claim.id, claim.token, 'PENDING_SCREENSHOT', 'source review; screenshot pending').status, 0);
        const counts = Object.fromEntries(JSON.parse(run('status').stdout).counts.map((row) => [row.status, row.count]));
        assert.deepEqual(counts, { IMPLEMENTED: 6, PENDING_SCREENSHOT: 1, TODO: 101 });
    } finally { rmSync(directory, { recursive: true, force: true }); }
});

test('preflight fails closed for missing inputs and unverified worker safety', () => {
    const unavailable = assess({ jobs: null, queue: null, router: false });
    assert.equal(unavailable.ready, false);
    assert.ok(unavailable.blockers.includes('automation_status_unknown'));
    assert.ok(unavailable.blockers.includes('queue_unavailable'));
    const active = assess({ jobs: [{ id: 'a', name: 'MayHen Docs Supervisor', enabled: true }], queue: { running: { id: 'x' } }, router: true, workers: [{ key: 'worker' }] });
    assert.equal(active.ready, false);
    assert.ok(active.blockers.includes('supervisor_enabled_before_acceptance'));
    assert.ok(active.blockers.includes('worker_claim_requires_inspection'));
    assert.ok(active.blockers.includes('acp_session_requires_inspection'));
    const healthy = assess({ jobs: [], queue: { running: null }, router: true, workers: [] });
    assert.equal(healthy.ready, false);
    assert.ok(healthy.blockers.includes('restricted_write_boundary_unverified'));
});

test('queue preserves progress, fences claims and refuses expired acknowledgements', async () => {
    const directory = mkdtempSync(path.join(tmpdir(), 'mayhen-queue-test-'));
    const database = path.join(directory, 'queue.sqlite');
    const script = path.resolve('docs/docs-center/docs-queue.mjs');
    const env = { ...process.env, MAYHEN_DOCS_QUEUE_DB: database };
    const run = (...args) => spawnSync(process.execPath, [script, ...args], { env, encoding: 'utf8' });
    const parallel = () => new Promise((resolve, reject) => {
        const child = spawn(process.execPath, [script, 'claim'], { env });
        let output = '';
        child.stdout.on('data', (chunk) => { output += chunk; });
        child.on('error', reject);
        child.on('close', (code) => code === 0 ? resolve(JSON.parse(output)) : reject(new Error(`claim exited ${code}`)));
    });
    try {
        assert.equal(run('init').status, 0);
        const firstStatus = JSON.parse(run('status').stdout);
        assert.deepEqual(Object.fromEntries(firstStatus.counts.map((row) => [row.status, row.count])), { IMPLEMENTED: 6, TODO: 102 });
        const claims = await Promise.all([parallel(), parallel()]);
        assert.equal(claims.filter((item) => item.claimed).length, 1);
        const claim = claims.find((item) => item.claimed);
        const session = 'agent:main:acp:40ca6300-1d2f-42d2-a309-91ec726780af';
        assert.notEqual(run('heartbeat', claim.id, 'wrong-token', session, 'working').status, 0);
        assert.equal(run('heartbeat', claim.id, claim.token, session, 'working').status, 0);
        assert.notEqual(run('heartbeat', claim.id, claim.token, 'agent:main:acp:another-session', 'working').status, 0);
        assert.equal(JSON.parse(run('status').stdout).running.session_key, session);
        assert.notEqual(run('finish', claim.id, 'wrong-token', 'IMPLEMENTED', 'test evidence').status, 0);
        assert.notEqual(run('finish', claim.id, claim.token, 'VERIFIED', 'test evidence').status, 0);
        const db = new DatabaseSync(database);
        db.prepare('UPDATE jobs SET deadline_at=? WHERE id=?').run(Date.now() - 1, claim.id);
        db.close();
        assert.notEqual(run('finish', claim.id, claim.token, 'IMPLEMENTED', 'test evidence').status, 0);
        assert.notEqual(run('heartbeat', claim.id, claim.token, session, 'late').status, 0);
        assert.equal(JSON.parse(run('claim').stdout).claimed, false);
        assert.equal(run('init').status, 0);
        const resumed = JSON.parse(run('status').stdout).running;
        assert.equal(resumed.id, claim.id);
        assert.equal(resumed.session_key, session);
        assert.equal(resumed.checkpoint, 'working');
        assert.equal(resumed.lease_state, 'EXPIRED_REQUIRES_INSPECTION');
    } finally {
        rmSync(directory, { recursive: true, force: true });
    }
});
