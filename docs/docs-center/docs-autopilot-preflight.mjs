import { DatabaseSync } from 'node:sqlite';
import { execFile } from 'node:child_process';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { promisify } from 'node:util';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const execute = promisify(execFile);

export function assess({ jobs, queue, router, workers }) {
    const supervisors = Array.isArray(jobs) ? jobs.filter((job) => /mayhen docs supervisor/i.test(job.name || '')) : null;
    const blockers = [];
    if (!supervisors) blockers.push('automation_status_unknown');
    else if (supervisors.some((job) => job.enabled)) blockers.push('supervisor_enabled_before_acceptance');
    if (!queue) blockers.push('queue_unavailable');
    else if (queue.running) blockers.push('worker_claim_requires_inspection');
    if (!workers) blockers.push('acp_session_status_unknown');
    else if (workers.length) blockers.push('acp_session_requires_inspection');
    if (!router) blockers.push('router_unavailable');
    // These gates must remain closed until the actual worker path and OS boundary are verified.
    blockers.push('restricted_write_boundary_unverified', 'acp_queue_adapter_unverified', 'consecutive_worker_runs_unverified', 'maintenance_cycles_unverified');
    return { ready: false, supervisors, queue, active_acp_sessions: workers, router_reachable: router, blockers };
}

async function main() {
    let jobs = null;
    let queue = null;
    let router = false;
    let workers = null;
    const openclaw = path.join(process.env.APPDATA || '', 'npm/node_modules/openclaw/openclaw.mjs');
    try {
        const result = await execute(process.execPath, [openclaw, 'cron', 'list', '--all', '--json'], { timeout: 12000, maxBuffer: 1024 * 1024 });
        const parsed = JSON.parse(result.stdout);
        jobs = parsed.hasMore ? null : parsed.jobs.map(({ id, name, enabled }) => ({ id, name, enabled }));
    } catch { /* An unavailable gateway must never open the gate. */ }
    try {
        const result = await execute(process.execPath, [openclaw, 'sessions', '--agent', 'main', '--active', '1440', '--json'], { timeout: 12000, maxBuffer: 4 * 1024 * 1024 });
        const parsed = JSON.parse(result.stdout);
        if (!parsed.hasMore && Array.isArray(parsed.sessions)) {
            const terminal = new Set(['done', 'failed', 'timeout', 'interrupted', 'cancelled']);
            workers = parsed.sessions.filter((session) => session.acpRuntime && !terminal.has(session.status)).map(({ key, status }) => ({ key, status: status || 'unknown' }));
        }
    } catch { /* Unknown sessions remain a blocker. */ }
    const database = process.env.MAYHEN_DOCS_QUEUE_DB || path.join(root, 'storage/app/mayhen-docs-queue.sqlite');
    if (existsSync(database)) {
        let db;
        try {
            db = new DatabaseSync(database, { readOnly: true });
            queue = {
                counts: db.prepare('SELECT status,COUNT(*) AS count FROM jobs GROUP BY status ORDER BY status').all(),
                running: db.prepare("SELECT id,started_at,deadline_at FROM jobs WHERE status='RUNNING'").get() || null,
            };
        } catch { queue = null; } finally { db?.close(); }
    }
    try { router = (await fetch('http://127.0.0.1:20128/', { signal: AbortSignal.timeout(3000) })).ok; } catch { /* Report unavailable. */ }
    console.log(JSON.stringify(assess({ jobs, queue, router, workers }), null, 2));
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) await main();
