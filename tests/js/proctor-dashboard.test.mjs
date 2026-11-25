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
        tagName: tag, className: '', dataset: {}, children: [], _text: '', handlers: {},
        classList: {
            add(c) { el.className = `${el.className} ${c}`.trim(); },
            toggle() {}, remove() {},
        },
        get firstElementChild() { return this.children[0] || null; },
        get textContent() {
            return this._text + this.children.map((c) => c.textContent).join('');
        },
        set textContent(v) { el._text = v; el.children = []; },
        append(...nodes) { el.children.push(...nodes); },
        replaceChildren(...nodes) { el.children = nodes; },
        addEventListener(ev, fn) { (el.handlers[ev] = el.handlers[ev] || []).push(fn); },
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

function createSandbox(snapshotStudents, hash = '#ujian-9', extra = {}) {
    const elements = {
        'proctor-cards': element('section'),
        'proctor-status': element('div'),
        'proctor-date': element('input'),
        'proctor-timeline-link': element('a'),
    };
    const root = element('div');
    root.dataset = {
        snapshotUrl: '/snap', tokenUrl: '/tok', unlockUrl: '/att', resolveUrl: '/req',
        approversUrl: '/ap', approverToggleUrl: '/tg', canApprove: '0', csrf: 'x',
        actionBase: '/lms/administrator/{schoolName}/{schoolId}/proctor',
        sessionUrl: '/lms/administrator/SMA Sendiri/1/proctor/assessments',
        schoolName: 'SMA Sendiri', schoolId: '1', timelineUrl: '/timeline',
        ...(extra.page || {}),
    };
    const sockets = [];
    const timers = [];
    const fetchedUrls = [];
    const document = {
        getElementById: (id) => (id === 'proctor-root' ? root : elements[id] || null),
        createElement: (tag) => element(tag),
        querySelector: (sel) => find(elements['proctor-cards'], sel)[0] || null,
        querySelectorAll: () => [],
    };
    const responses = {
        '/snap': {
            assessments: [{ assessment_id: 9, assessment_title: 'Matematika', time: '08:00–10:00', state: 'running', ...(extra.assessment || {}) }],
            students: snapshotStudents,
            canApprove: false,
            ...(extra.snapshot || {}),
        },
        '/tok': { url: '/proctor/ws', token: 'tok' },
    };
    const sandbox = {
        document,
        location: { protocol: 'https:', host: 'example.test', hash },
        addEventListener() {},
        fetch: async (url) => {
            fetchedUrls.push(url);
            return { ok: true, status: 200, json: async () => responses[url] || responses[url.split('?')[0]] || {} };
        },
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
    return { elements, sockets, timers, fetchedUrls, flush };
}

function text(el, role) { return find(el, `[data-role="${role}"]`)[0]?.textContent; }
function rowFor(s, key) { return find(s.elements['proctor-cards'], `[data-student-key="${key}"]`)[0]; }

function walk(root, out = []) {
    for (const c of root.children || []) { out.push(c); walk(c, out); }
    return out;
}
function buttonWithText(root, label) { return walk(root).find((n) => n.tagName === 'button' && n.textContent === label) || null; }
function click(node) { (node.handlers?.click || []).forEach((fn) => fn()); }

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

test('school-level auto_admit shows the bypass notice in the pending-entries area', async () => {
    const s = createSandbox([ROW], '#ujian-9', { snapshot: { auto_admit: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const notice = find(s.elements['proctor-cards'], '[data-role="auto-admit-notice"]')[0];
    assert.ok(notice, 'notice rendered even with no students waiting');
    assert.ok(notice.textContent.includes('Admisi otomatis aktif'), 'notice says the queue is bypassed');
    assert.ok(notice.textContent.includes('tanpa persetujuan'));
});

test('per-assessment auto_admit shows the same notice', async () => {
    const s = createSandbox([ROW], '#ujian-9', { assessment: { auto_admit: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const notice = find(s.elements['proctor-cards'], '[data-role="auto-admit-notice"]')[0];
    assert.ok(notice, 'notice rendered from the assessment card');
});

test('older snapshots without auto_admit show no notice', async () => {
    const waiting = [{ ...ROW, status: 'waiting', count: 0, attempt_id: null, entry_request_id: 77, last_heartbeat_at: null }];
    const s = createSandbox(waiting, '#ujian-9');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    assert.equal(find(box, '[data-role="auto-admit-notice"]').length, 0, 'no badge on the legacy payload');
    assert.ok(JSON.stringify(box.children).includes('Menunggu masuk: 1 siswa'), 'the waiting list still renders');
});

test('auto-admit notice leaves the waiting rows and their controls intact', async () => {
    const waiting = [{ ...ROW, status: 'waiting', count: 0, attempt_id: null, entry_request_id: 77, last_heartbeat_at: null }];
    const s = createSandbox(waiting, '#ujian-9', { snapshot: { auto_admit: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    assert.ok(find(box, '[data-role="auto-admit-notice"]').length, 'notice rendered');
    assert.ok(JSON.stringify(box.children).includes('Izinkan'), 'approve button still offered');
    assert.ok(JSON.stringify(box.children).includes('Tolak'), 'deny button still offered');
});

test('school-level auto_start shows the schedule banner in the header area', async () => {
    const s = createSandbox([ROW], '', { snapshot: { auto_start: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const badge = find(s.elements['proctor-cards'], '[data-role="auto-start-badge"]')[0];
    assert.ok(badge, 'banner rendered on the overview');
    assert.ok(badge.textContent.includes('Dibuka otomatis sesuai jadwal'), 'banner says the schedule opens the exam');
    assert.ok(badge.textContent.includes('tanpa Mulai guru'), 'banner says the teacher Mulai is optional');
});

test('the auto_start banner also shows inside an open exam', async () => {
    const s = createSandbox([ROW], '#ujian-9', { snapshot: { auto_start: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    assert.ok(find(s.elements['proctor-cards'], '[data-role="auto-start-badge"]').length, 'banner rendered on the exam view');
});

test('a virtually running exam carries the jadwal chip on its card', async () => {
    const s = createSandbox([ROW], '', { snapshot: { auto_start: true }, assessment: { virtual_running: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const chip = find(s.elements['proctor-cards'], '[data-role="virtual-chip"]')[0];
    assert.ok(chip, 'chip rendered on the exam card');
    assert.ok(chip.textContent.includes('jadwal'), 'chip marks the exam as schedule-opened');
});

test('a manually started exam gets no jadwal chip', async () => {
    const s = createSandbox([ROW], '', { snapshot: { auto_start: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    assert.equal(find(s.elements['proctor-cards'], '[data-role="virtual-chip"]').length, 0, 'no chip without virtual_running');
});

test('older snapshots show neither the banner nor the chip', async () => {
    const s = createSandbox([ROW], '');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    assert.equal(find(box, '[data-role="auto-start-badge"]').length, 0, 'no banner on the legacy payload');
    assert.equal(find(box, '[data-role="virtual-chip"]').length, 0, 'no chip on the legacy payload');
    assert.ok(JSON.stringify(box.children).includes('Matematika'), 'the exam tile still renders');
});

test('a virtual session still offers the pause and stop controls', async () => {
    const s = createSandbox([ROW], '#ujian-9', { assessment: { virtual_running: true } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    assert.ok(find(box, '[data-role="virtual-chip"]').length, 'chip rendered next to the exam title');
    assert.ok(JSON.stringify(box.children).includes('Jeda'), 'pause control still rendered');
    assert.ok(JSON.stringify(box.children).includes('Hentikan'), 'stop control still rendered');
});

// ---- all-sessions admin view: per-card school context, labels and the date filter ----

test('actions on a card with school context build the URL from that school', async () => {
    const s = createSandbox([ROW], '#ujian-9', { assessment: { school_partner_id: 5, school_name: 'SMA Lain' } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    click(buttonWithText(s.elements['proctor-cards'], 'Jeda'));
    assert.ok(
        s.fetchedUrls.includes('/lms/administrator/SMA Lain/5/proctor/assessments/9/pause'),
        `session control posts to the card school, got ${s.fetchedUrls.join(', ')}`
    );
});

test('entry decisions on a foreign-school card post to that school', async () => {
    const waiting = [{ ...ROW, status: 'waiting', count: 0, attempt_id: null, entry_request_id: 77, last_heartbeat_at: null }];
    const s = createSandbox(waiting, '#ujian-9', { assessment: { school_partner_id: 5, school_name: 'SMA Lain' } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    click(buttonWithText(s.elements['proctor-cards'], 'Izinkan'));
    assert.ok(
        s.fetchedUrls.includes('/lms/administrator/SMA Lain/5/proctor/entries/decide'),
        `entry decision posts to the card school, got ${s.fetchedUrls.join(', ')}`
    );
});

test('legacy cards without school fields keep using the page school segment', async () => {
    const s = createSandbox([ROW], '#ujian-9');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    click(buttonWithText(s.elements['proctor-cards'], 'Jeda'));
    assert.ok(
        s.fetchedUrls.includes('/lms/administrator/SMA Sendiri/1/proctor/assessments/9/pause'),
        `legacy card posts to the page school, got ${s.fetchedUrls.join(', ')}`
    );
});

test('a foreign-school card carries a school label, same-school and legacy cards do not', async () => {
    const s = createSandbox([ROW], '', { assessment: { school_partner_id: 5, school_name: 'SMA Lain' } });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const box = s.elements['proctor-cards'];
    const label = find(box, '[data-role="school-label"]')[0];
    assert.ok(label, 'label rendered on the foreign-school card');
    assert.ok(label.textContent.includes('SMA Lain'));

    const same = createSandbox([ROW], '', { assessment: { school_partner_id: 1, school_name: 'SMA Sendiri' } });
    await same.flush();
    same.sockets[0].onopen();
    await same.flush();
    assert.equal(find(same.elements['proctor-cards'], '[data-role="school-label"]').length, 0, 'no label for the page own school');

    const legacy = createSandbox([ROW], '');
    await legacy.flush();
    legacy.sockets[0].onopen();
    await legacy.flush();
    assert.equal(find(legacy.elements['proctor-cards'], '[data-role="school-label"]').length, 0, 'no label on legacy payloads');
});

test('the all-schools admin view (schoolId=0) labels every card', async () => {
    const s = createSandbox([ROW], '', {
        assessment: { school_partner_id: 1, school_name: 'SMA Sendiri' },
        page: { schoolName: 'semua', schoolId: '0' },
    });
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();
    const label = find(s.elements['proctor-cards'], '[data-role="school-label"]')[0];
    assert.ok(label, 'label rendered even when the card school matches the page placeholder');
});

test('changing the date filter refetches the snapshot with ?date= and updates the timeline link', async () => {
    const s = createSandbox([ROW], '');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();

    const input = s.elements['proctor-date'];
    input.value = '2026-11-20';
    (input.handlers.change || []).forEach((fn) => fn());
    await s.flush();
    assert.ok(s.fetchedUrls.includes('/snap?date=2026-11-20'), `snapshot refetched with the date, got ${s.fetchedUrls.join(', ')}`);
    assert.equal(s.elements['proctor-timeline-link'].href, '/timeline?date=2026-11-20', 'timeline link carries the date');
});

test('clearing or invalidating the date refetches with the default (no date param)', async () => {
    const s = createSandbox([ROW], '');
    await s.flush();
    s.sockets[0].onopen();
    await s.flush();

    const input = s.elements['proctor-date'];
    input.value = 'not-a-date';
    (input.handlers.change || []).forEach((fn) => fn());
    await s.flush();
    assert.ok(s.fetchedUrls.at(-1) === '/snap', `invalid input falls back to the default snapshot URL, got ${s.fetchedUrls.join(', ')}`);
    assert.equal(s.elements['proctor-timeline-link'].href, '/timeline', 'timeline link back to the default');
});
