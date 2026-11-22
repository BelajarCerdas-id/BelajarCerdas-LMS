// Run: node --test tests/js/
// Runs the teacher proctor dashboard in a vm sandbox with a stub DOM, fetch and WebSocket.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const file = path.join(path.dirname(fileURLToPath(import.meta.url)), '../../public/assets/js/features/lms/teacher/proctor/proctor-dashboard.js');

function element(tag = 'div') {
    const el = {
        tagName: tag, className: '', dataset: {}, children: [], _text: '',
        classList: {
            add(c) { el.className = `${el.className} ${c}`.trim(); },
            toggle() {}, remove() {},
        },
        get firstElementChild() { return this.children[0] || null; },
        get textContent() {
            return this._text + this.children.map((c) => c.textContent).join('');
        },
        set textContent(v) { this._text = v; this.children = []; },
        append(...nodes) { this.children.push(...nodes); },
        replaceChildren(...nodes) { this.children = nodes; },
        addEventListener() {},
        setAttribute() {},
        querySelector(sel) { return find(this, sel)[0] || null; },
    };
    return el;
}

// supports [data-x="y"] and [data-role="x"] selectors used by the dashboard
function find(root, sel) {
    const out = [];
    const walk = (node) => {
        for (const c of node.children || []) {
            const m = sel.match(/^\[(data-[a-z-]+)="([^"]+)"\]$/);
            if (m) {
                const key = m[1].replace(/^data-/, '').replace(/-([a-z])/g, (_, ch) => ch.toUpperCase());
                if (c.dataset && String(c.dataset[key]) === m[2]) out.push(c);
            }
            walk(c);
        }
    };
    walk(root);
    return out;
}

function createSandbox(snapshotStudents, hash = '#ujian-9') {
    const elements = {
        'proctor-cards': element('section'),
        'proctor-status': element('div'),
    };
    const root = element('div');
    root.dataset = { snapshotUrl: '/snap', tokenUrl: '/tok', unlockUrl: '/att', resolveUrl: '/req', approversUrl: '/ap', approverToggleUrl: '/tg', canApprove: '0', csrf: 'x' };
    const sockets = [];
    const timers = [];
    const document = {
        getElementById: (id) => (id === 'proctor-root' ? root : elements[id] || null),
        createElement: (tag) => element(tag),
        querySelector: (sel) => find(elements['proctor-cards'], sel)[0] || null,
        querySelectorAll: () => [],
    };
    const responses = {
        '/snap': {
            assessments: [{ assessment_id: 9, assessment_title: 'Matematika', time: '08:00–10:00', state: 'running' }],
            students: snapshotStudents,
            canApprove: false,
        },
        '/tok': { url: '/proctor/ws', token: 'tok' },
    };
    const sandbox = {
        document,
        location: { protocol: 'https:', host: 'example.test', hash },
        addEventListener() {},
        fetch: async (url) => ({ ok: true, status: 200, json: async () => responses[url] || {} }),
        WebSocket: class { constructor(url) { this.url = url; sockets.push(this); } },
        setTimeout: (fn) => { timers.push(fn); return timers.length; }, clearTimeout: () => {}, setInterval: () => 0,
        encodeURIComponent, alert: () => {}, Date, Event: class {}, Option: class { constructor(label, value) { this.textContent = label; this.value = value; } },
        CSS: { escape: (s) => s }, console,
    };
    sandbox.window = sandbox;
    vm.createContext(sandbox);
    vm.runInContext(fs.readFileSync(file, 'utf8'), sandbox, { filename: 'proctor-dashboard.js' });
    const flush = async () => {
        for (let i = 0; i < 5 && timers.length; i++) {
            timers.splice(0).forEach((fn) => fn());
            await new Promise((r) => setImmediate(r));
        }
        await new Promise((r) => setImmediate(r));
    };
    return { elements, sockets, flush };
}

function text(el, role) { return find(el, `[data-role="${role}"]`)[0]?.textContent; }
function rowFor(s, key) { return find(s.elements['proctor-cards'], `[data-student-key="${key}"]`)[0]; }

// One row per student per exam (class roster + attempts + entry requests).
const ROW = {
    key: '9:1', assessment_id: 9, assessment_title: 'Matematika', user_id: 1, student_name: 'Budi',
    status: 'in_progress', attempt_id: 1, entry_request_id: null, unlock_request_id: null, unlock_expires_at: null,
    count: 1, limit: 5, last_heartbeat_at: '2026-11-21T10:00:00Z',
};

test('snapshot renders one row per student with the Bahasa notice', async () => {
    const s = createSandbox([ROW]);
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const row = rowFor(s, ROW.key);
    assert.ok(row, 'row rendered');
    assert.ok(row.textContent.includes('Budi'));
    assert.ok(row.textContent.includes('Pindah tab (1/5)'));
});

test('strike event updates the row and blinks it', async () => {
    const s = createSandbox([ROW]);
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'strike', attempt_id: 1, assessment_id: 9, count: 2, limit: 5, reason: 'window_blur' }) });
    await s.flush();
    const row = rowFor(s, ROW.key);
    assert.ok(row.textContent.includes('Pindah tab (2/5): keluar dari jendela ujian'));
    assert.ok(row.classList.blinked === true || row.className.includes('blink'), 'row blinks');
});

test('heartbeat event blinks the row', async () => {
    const s = createSandbox([ROW]);
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'heartbeat', attempt_id: 1, assessment_id: 9, at: new Date().toISOString() }) });
    await s.flush();
    const row = rowFor(s, ROW.key);
    assert.ok(row, 'row still rendered');
    assert.ok(row.className.includes('blink'), 'heartbeat blinks the row');
});

test('blocked event marks the row as locked', async () => {
    const s = createSandbox([ROW]);
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'blocked', attempt_id: 1, assessment_id: 9, count: 5, limit: 5 }) });
    await s.flush();
    const row = rowFor(s, ROW.key);
    assert.ok(row.textContent.includes('Terkunci'), 'notice flips immediately; the badge follows on the next snapshot');
});

test('event for an unknown student triggers a snapshot reload instead of a broken row', async () => {
    const s = createSandbox([ROW]);
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'strike', attempt_id: 999, assessment_id: 9, count: 1, limit: 5, reason: 'x' }) });
    await s.flush();
    assert.ok(rowFor(s, ROW.key));
});

test('the overview lists exams as tiles and keeps student rows inside the exam view', async () => {
    const s = createSandbox([ROW], '');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    assert.equal(find(box, '[data-student-key="9:1"]').length, 0, 'no student rows on the overview');
    assert.ok(JSON.stringify(box.children).includes('Buka ujian'), 'exam tile rendered');
});

test('opening an exam shows its student rows and its own controls', async () => {
    const s = createSandbox([ROW], '#ujian-9');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    assert.equal(find(box, '[data-student-key="9:1"]').length, 1, 'student row inside the exam view');
    assert.ok(JSON.stringify(box.children).includes('Semua ujian'), 'back link to the overview');
});

test('snapshot change blinks the row: new rows and status changes', async () => {
    const s = createSandbox([{ ...ROW, status: 'waiting', count: 0, attempt_id: null, last_heartbeat_at: null }]);
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    assert.ok(rowFor(s, ROW.key).className.includes('blink'), 'a new student blinks on arrival');

    // a second identical snapshot must not blink again
    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'resync' }) });
    await s.flush();
    assert.equal(rowFor(s, ROW.key).className.includes('blink'), false, 'unchanged rows stay calm');
});
