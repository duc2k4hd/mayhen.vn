import { DatabaseSync } from 'node:sqlite';
import { randomUUID } from 'node:crypto';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const source = path.join(root, 'docs/docs-center/11_DOCS_WORK_QUEUE.csv');
const matrix = path.join(root, 'docs/docs-center/02_DOCS_EDITORIAL_COVERAGE_MATRIX.csv');
const database = process.env.MAYHEN_DOCS_QUEUE_DB || path.join(root, 'storage/app/mayhen-docs-queue.sqlite');
const db = new DatabaseSync(database);
db.exec(`PRAGMA busy_timeout=5000; PRAGMA journal_mode=WAL;
CREATE TABLE IF NOT EXISTS jobs (
  id TEXT PRIMARY KEY, status TEXT NOT NULL, notes TEXT NOT NULL DEFAULT '',
  attempts INTEGER NOT NULL DEFAULT 0, token TEXT, started_at INTEGER,
  deadline_at INTEGER, ended_at INTEGER, evidence TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS one_running_job ON jobs(status) WHERE status='RUNNING';
CREATE TABLE IF NOT EXISTS events (
  id INTEGER PRIMARY KEY, at INTEGER NOT NULL, job_id TEXT, event TEXT NOT NULL, details TEXT
);
CREATE TABLE IF NOT EXISTS worker_checkpoints (
  job_id TEXT PRIMARY KEY REFERENCES jobs(id), token TEXT NOT NULL,
  session_key TEXT NOT NULL, updated_at INTEGER NOT NULL, checkpoint TEXT NOT NULL
);`);

function transaction(fn) {
    db.exec('BEGIN IMMEDIATE');
    try { const result = fn(); db.exec('COMMIT'); return result; }
    catch (error) { db.exec('ROLLBACK'); throw error; }
}

function log(id, event, details = '') {
    db.prepare('INSERT INTO events(at,job_id,event,details) VALUES(?,?,?,?)').run(Date.now(), id, event, details);
}

function initialize() {
    const lines = readFileSync(source, 'utf8').replace(/^\uFEFF/, '').trim().split(/\r?\n/);
    if (lines.shift() !== 'Article_ID,Status,Notes') throw new Error('Unexpected queue CSV header');
    const entries = lines.map((line) => {
        const match = /^(CLI|ADM)-[A-Z]+-\d{3},(TODO|IMPLEMENTED),([^"\r\n]*)$/.exec(line);
        if (!match) throw new Error(`Unsupported queue row: ${line}`);
        const [id, status, notes] = line.split(',');
        return { id, status, notes };
    });
    if (entries.length !== 108 || new Set(entries.map((entry) => entry.id)).size !== 108) throw new Error('Expected 108 unique article IDs');
    const matrixLines = readFileSync(matrix, 'utf8').replace(/^\uFEFF/, '').trim().split(/\r?\n/);
    if (!matrixLines.shift().startsWith('Article_ID,')) throw new Error('Unexpected coverage matrix header');
    const matrixIds = matrixLines.map((line) => line.split(',', 1)[0]);
    const queueIds = new Set(entries.map((entry) => entry.id));
    if (matrixIds.length !== 108 || new Set(matrixIds).size !== 108 || matrixIds.some((id) => !queueIds.has(id))) throw new Error('Queue IDs differ from the coverage matrix');
    transaction(() => {
        const insert = db.prepare('INSERT OR IGNORE INTO jobs(id,status,notes) VALUES(?,?,?)');
        for (const entry of entries) insert.run(entry.id, entry.status, entry.notes);
        log(null, 'import', `${entries.length} source IDs; existing records preserved`);
    });
    return { imported: entries.length };
}

function claim(requestedId) {
    return transaction(() => {
        const running = db.prepare("SELECT id,deadline_at FROM jobs WHERE status='RUNNING'").get();
        if (running) return { claimed: false, running };
        const job = requestedId
            ? db.prepare("SELECT id FROM jobs WHERE id=? AND status='TODO' AND attempts < 3").get(requestedId)
            : db.prepare("SELECT id FROM jobs WHERE status='TODO' AND attempts < 3 ORDER BY id LIMIT 1").get();
        if (!job) return { claimed: false, reason: 'no eligible jobs' };
        const token = randomUUID();
        const now = Date.now();
        db.prepare("UPDATE jobs SET status='RUNNING',token=?,started_at=?,deadline_at=?,ended_at=NULL,attempts=attempts+1 WHERE id=?").run(token, now, now + 30 * 60 * 1000, job.id);
        log(job.id, 'claimed', '30 minute deadline; expired workers require explicit stop verification');
        return { claimed: true, id: job.id, token, deadline_at: now + 30 * 60 * 1000 };
    });
}

function finish(id, token, status, evidence) {
    if (!['IMPLEMENTED', 'PENDING_SCREENSHOT', 'FAILED', 'BLOCKED'].includes(status) || !evidence) throw new Error('A permitted status and evidence are required');
    return transaction(() => {
        const job = db.prepare('SELECT * FROM jobs WHERE id=?').get(id);
        if (!job || job.status !== 'RUNNING' || job.token !== token || job.deadline_at <= Date.now()) throw new Error('Claim is invalid or expired');
        db.prepare('UPDATE jobs SET status=?,ended_at=?,evidence=?,token=NULL WHERE id=?').run(status, Date.now(), evidence, id);
        log(id, status, evidence);
        return { id, status };
    });
}

function heartbeat(id, token, sessionKey, checkpoint) {
    if (!/^agent:[a-zA-Z0-9_-]+:acp:[a-zA-Z0-9-]+$/.test(sessionKey || '') || !checkpoint || checkpoint.length > 4096) {
        throw new Error('A valid ACP session key and checkpoint are required');
    }
    return transaction(() => {
        const now = Date.now();
        const job = db.prepare('SELECT status,token,deadline_at FROM jobs WHERE id=?').get(id);
        if (!job || job.status !== 'RUNNING' || job.token !== token || job.deadline_at <= now) throw new Error('Claim is invalid or expired');
        const previous = db.prepare('SELECT token,session_key FROM worker_checkpoints WHERE job_id=?').get(id);
        if (previous && previous.token === token && previous.session_key !== sessionKey) throw new Error('A claim cannot change ACP session');
        const deadline = now + 30 * 60 * 1000;
        db.prepare('UPDATE jobs SET deadline_at=? WHERE id=?').run(deadline, id);
        db.prepare(`INSERT INTO worker_checkpoints(job_id,token,session_key,updated_at,checkpoint) VALUES(?,?,?,?,?)
          ON CONFLICT(job_id) DO UPDATE SET token=excluded.token,session_key=excluded.session_key,updated_at=excluded.updated_at,checkpoint=excluded.checkpoint`).run(id, token, sessionKey, now, checkpoint);
        log(id, 'heartbeat', sessionKey);
        return { id, session_key: sessionKey, deadline_at: deadline };
    });
}

function status() {
    const running = db.prepare("SELECT jobs.id,attempts,started_at,deadline_at,worker_checkpoints.session_key,worker_checkpoints.updated_at AS heartbeat_at,worker_checkpoints.checkpoint FROM jobs LEFT JOIN worker_checkpoints ON jobs.id=worker_checkpoints.job_id AND jobs.token=worker_checkpoints.token WHERE status='RUNNING'").get() || null;
    if (running) running.lease_state = running.deadline_at <= Date.now() ? 'EXPIRED_REQUIRES_INSPECTION' : 'ACTIVE';
    return { database, counts: db.prepare('SELECT status,COUNT(*) AS count FROM jobs GROUP BY status ORDER BY status').all(), running };
}

try {
    const [command, ...args] = process.argv.slice(2);
    const result = command === 'init' ? initialize() : command === 'status' ? status() : command === 'claim' ? claim(...args) : command === 'finish' ? finish(...args) : command === 'heartbeat' ? heartbeat(...args) : null;
    if (!result) throw new Error('Usage: node docs/docs-center/docs-queue.mjs init|status|claim|finish|heartbeat');
    console.log(JSON.stringify(result, null, 2));
} catch (error) {
    console.error(error.message);
    process.exitCode = 1;
} finally { db.close(); }
