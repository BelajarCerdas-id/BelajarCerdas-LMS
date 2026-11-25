// Run: node --test tests/js/
// Exam browser-state behaviour: per-user scoping and purge of foreign state
// (shared lab machines), plus the server-verified expiry guard in
// start-timer-assessment-test.js. Loads the real files into a vm sandbox with
// stubbed DOM / jQuery / SweetAlert and a controllable clock and timer queue.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const dir = path.join(path.dirname(fileURLToPath(import.meta.url)), '../../public/assets/js/features/lms/student/assessment');

const examFiles = {
    core: 'anticheat/anticheat-core.js',
    timer: 'start-timer-assessment-test.js',
    perQuestion: 'start-timer-assessment-test-by-question.js',
    form: 'student-form-assessment-test.js',
};

function createStorage(initial = {}) {
    const store = new Map(Object.entries(initial));
    return {
        getItem: (k) => (store.has(k) ? store.get(k) : null),
        setItem: (k, v) => { store.set(k, String(v)); },
        removeItem: (k) => { store.delete(k); },
        key: (i) => Array.from(store.keys())[i] ?? null,
        get length() { return store.size; },
    };
}

// minimal chainable jQuery stand-in: enough for the exam files' top-level
// statements ($(...).off().on(...), $(document).ready(...)) and the runtime
// calls used by the flows under test
function jqFactory(document, containerData, onAjax) {
    const containerJq = {
        length: 1,
        data: (name) => (name in containerData ? containerData[name] : null),
        attr: () => 'csrf',
        off() { return this; },
        on() { return this; },
    };
    const generic = () => {
        const obj = {
            length: 0,
            off() { return obj; },
            on() { return obj; },
            ready() {},
            attr: () => 'csrf',
            data: () => null,
            val() { return obj; },
            text() { return obj; },
            html() { return obj; },
            hide() { return obj; },
            show() { return obj; },
            addClass() { return obj; },
            removeClass() { return obj; },
            removeProp() { return obj; },
            prop() { return obj; },
            empty() { return obj; },
            append() { return obj; },
            each() { return obj; },
            find: () => generic(),
            offAll: null,
        };
        return obj;
    };
    const docJq = { off() { return docJq; }, on() { return docJq; }, ready() {} };
    const $ = (sel) => {
        if (sel === document) return docJq;
        if (typeof sel === 'string' && sel.includes('container-assessment-test-form')) return containerJq;
        return generic();
    };
    $.ajax = (opts) => onAjax(opts);
    return $;
}

const containerData = {
    'role': 'student', 'school-name': 'sekolah', 'school-id': 1,
    'curriculum-id': 1, 'mapel-id': 1, 'assessment-type-id': 1,
    'semester': 1, 'assessment-id': 516, 'user-id': '42',
};

// timer-only sandbox: anticheat-core + start-timer (the countdown flows)
function createSandbox({ userId = '42', storage = createStorage(), clockStart = 1_000_000 } = {}) {
    const ajaxCalls = [];
    const timers = [];
    const intervals = [];
    const swalShots = [];
    const swalTitles = [];
    const timerEl = { textContent: '-' };
    let clock = clockStart;
    let submitCount = 0;

    const document = {
        getElementById: (id) => {
            if (id === 'timer-assessment-test') return timerEl;
            if (id === 'container-assessment-test-form' || id === 'container-timer-assessment-test') {
                return { getAttribute: (name) => (name === 'data-user-id' ? userId : null) };
            }
            return null;
        },
        addEventListener() {},
    };

    const sandbox = {
        console,
        document,
        navigator: { userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0' },
        localStorage: storage,
        sessionStorage: createStorage(),
        Date: { now: () => clock },
        performance: { now: () => clock },
        setTimeout: (fn, ms) => { timers.push({ fn, at: clock + (ms || 0) }); return timers.length; },
        clearTimeout: () => {},
        setInterval: (fn, ms) => { intervals.push(fn); return intervals.length; },
        clearInterval: () => {},
        Swal: {
            fire: (opts) => { swalShots.push(opts); swalTitles.push(opts && opts.title); return { then() { return this; } }; },
            close: () => {},
        },
        $: (sel) => ({ attr: () => 'csrf', length: 1 }),
        // globals normally provided by student-form-assessment-test.js
        containerFormAssessment: { length: 1 },
        role: 'student', schoolName: 'sekolah', schoolId: 1, curriculumId: 1,
        mapelId: 1, assessmentTypeId: 1, semester: 1, assessmentId: 516,
        // stubs for functions living in files not loaded here
        saveQuestionDuration: () => {},
        autoSubmitUnSavedQuestions: () => { submitCount++; },
    };
    sandbox.$.ajax = (opts) => { ajaxCalls.push(opts); };
    sandbox.window = sandbox;
    sandbox.addEventListener = () => {};
    sandbox.removeEventListener = () => {};
    vm.createContext(sandbox);

    vm.runInContext(fs.readFileSync(path.join(dir, examFiles.core), 'utf8'), sandbox, { filename: examFiles.core });
    vm.runInContext(fs.readFileSync(path.join(dir, examFiles.timer), 'utf8'), sandbox, { filename: examFiles.timer });

    const runTimers = () => { for (const t of timers.splice(0)) t.fn(); };
    const timerRequests = () => ajaxCalls.filter((c) => /start-timer$/.test(c.url));

    return {
        sandbox, storage, ajaxCalls, intervals, swalShots, swalTitles, timerEl,
        get submitCount() { return submitCount; },
        advance: (ms) => { clock += ms; },
        get clock() { return clock; },
        runTimers,
        timerRequests,
        get: (expr) => vm.runInContext(expr, sandbox),
        run: (code) => vm.runInContext(code, sandbox),
    };
}

// full exam sandbox: anticheat-core + start-timer + per-question timer +
// student-form, so autoSubmitUnSavedQuestions / the /end flow run for real
function createExamSandbox({ userId = '42', storage = createStorage(), clockStart = 1_000_000 } = {}) {
    const ajaxCalls = [];
    const timers = [];
    const intervals = [];
    const swalShots = [];
    const swalTitles = [];
    const timerEl = { textContent: '-' };
    let clock = clockStart;

    const document = {
        getElementById: (id) => {
            if (id === 'timer-assessment-test') return timerEl;
            if (id === 'container-assessment-test-form' || id === 'container-timer-assessment-test') {
                return { getAttribute: (name) => (name === 'data-user-id' ? userId : null) };
            }
            return null;
        },
        addEventListener() {},
    };

    const sandbox = {
        console,
        document,
        navigator: { userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0' },
        localStorage: storage,
        sessionStorage: createStorage(),
        Date: { now: () => clock },
        performance: { now: () => clock },
        setTimeout: (fn, ms) => { timers.push({ fn, at: clock + (ms || 0) }); return timers.length; },
        clearTimeout: () => {},
        setInterval: (fn, ms) => { intervals.push(fn); return intervals.length; },
        clearInterval: () => {},
        Swal: {
            fire: (opts) => { swalShots.push(opts); swalTitles.push(opts && opts.title); return { then() { return this; } }; },
            close: () => {},
        },
        location: { protocol: 'https:', host: 'example.test', href: '' },
        history: { pushState() {}, go() {} },
    };
    sandbox.$ = jqFactory(document, containerData, (opts) => { ajaxCalls.push(opts); });
    sandbox.window = sandbox;
    sandbox.addEventListener = () => {};
    sandbox.removeEventListener = () => {};
    vm.createContext(sandbox);

    for (const key of ['core', 'timer', 'perQuestion', 'form']) {
        vm.runInContext(fs.readFileSync(path.join(dir, examFiles[key]), 'utf8'), sandbox, { filename: examFiles[key] });
    }

    const runTimers = () => { for (const t of timers.splice(0)) t.fn(); };
    const timerRequests = () => ajaxCalls.filter((c) => /start-timer$/.test(c.url));
    const endRequests = () => ajaxCalls.filter((c) => /\/end$/.test(c.url));

    return {
        sandbox, storage, ajaxCalls, intervals, swalShots, swalTitles, timerEl,
        advance: (ms) => { clock += ms; },
        get clock() { return clock; },
        runTimers, timerRequests, endRequests,
        get: (expr) => vm.runInContext(expr, sandbox),
        run: (code) => vm.runInContext(code, sandbox),
    };
}

test('purgeForeignExamState removes legacy and other-user exam keys, keeps the current user\'s', () => {
    const s = createSandbox({ userId: '42' });
    Object.assign(s.storage, {});
    const seed = {
        // legacy (pre-scoping) keys — the "switch ID" contamination
        'timer_assessment_test_start_516': '1000',
        'timer_assessment_test_expire_516': '2000',
        'assessment_516_all_answered': '1',
        'duration_9': '5',
        // another student on the same machine
        'timer_assessment_test_start_516_u7': '3000',
        'timer_assessment_test_expire_516_u7': '4000',
        'assessment_516_all_answered_u7': '1',
        'duration_9_u7': '6',
        'timer_assessment_test_start_517_u7': '5000',
        // the current user's own state — must survive
        'timer_assessment_test_start_516_u42': '6000',
        'timer_assessment_test_expire_516_u42': '7000',
        'assessment_516_all_answered_u42': '1',
        'duration_9_u42': '7',
        // unrelated keys must never be touched
        'bc_sidebar_width': '280',
        'app_client_cache_version': '3',
    };
    for (const [k, v] of Object.entries(seed)) s.storage.setItem(k, v);

    s.run('purgeForeignExamState()');

    for (const k of Object.keys(seed)) {
        const expected = k.includes('_u42') || !/_u\d|timer_assessment|all_answered|^duration_/.test(k)
            ? seed[k] : null;
        assert.equal(s.storage.getItem(k), expected, `key ${k}`);
    }
});

test('exam state keys are written scoped to the current user', () => {
    const s = createSandbox({ userId: '42' });
    const expire = s.clock + 90 * 60 * 1000;
    s.run('startTimer()');

    s.timerRequests()[0].success({ status: 'success', start_time: s.clock, expire_time: expire, duration: 90 });

    assert.equal(s.storage.getItem('timer_assessment_test_start_516_u42'), String(s.clock));
    assert.equal(s.storage.getItem('timer_assessment_test_expire_516_u42'), String(expire));
    assert.equal(s.storage.getItem('timer_assessment_test_start_516'), null, 'no unscoped key written');
    assert.equal(s.swalTitles.length, 0, 'no modal when time remains');
});

test('countdown hitting zero does not auto-submit while the server says time remains', () => {
    const s = createSandbox();
    const expire = s.clock + 90 * 60 * 1000;

    s.run('startTimer()');
    s.timerRequests()[0].success({ status: 'success', start_time: s.clock, expire_time: expire, duration: 90 });
    const countdown = s.intervals.at(-1);
    assert.ok(countdown, 'countdown registered');

    // the lab machine clock runs 60 minutes fast; the countdown drains
    // while the server still has half the exam left
    s.advance(60 * 60 * 1000);
    // the form fetch anchors the client clock to the server mid-exam
    s.run(`setExamClockOffset(${s.clock - 60 * 60 * 1000}, ${s.clock})`);
    for (let i = 0; i < 5401; i++) countdown();

    assert.ok(s.swalTitles.includes('Memeriksa sisa waktu'), 'visible verification modal');
    s.timerRequests()[1].success({ status: 'success', start_time: s.clock - 60 * 60 * 1000, expire_time: expire, duration: 90 });

    assert.equal(s.submitCount, 0, 'no auto-submit while the server says time remains');
    assert.notEqual(s.timerEl.textContent, 'Waktu Habis');
    assert.equal(s.get('examFinished'), false);
    assert.ok(s.intervals.at(-1), 'countdown resynced to the server expire');
    assert.equal(s.get('verifyingExpiry'), false);
    assert.equal(s.get('isWarningModalOpen'), false, 'verification modal closed');
});

test('an unanchored clock never ends the exam off local state alone (grace pulse)', () => {
    const s = createSandbox();

    // expire already past on the client, server still within its
    // 1-minute admit grace, and no server clock anchor yet
    s.run('startTimer()');
    const expire = s.clock - 30 * 1000;
    s.timerRequests()[0].success({ status: 'success', start_time: s.clock - 60 * 1000, expire_time: expire, duration: 90 });

    s.timerRequests()[1].success({ status: 'success', start_time: s.clock - 60 * 1000, expire_time: expire, duration: 90 });

    assert.equal(s.submitCount, 0, 'no auto-submit without a server confirmation');
    assert.notEqual(s.timerEl.textContent, 'Waktu Habis');
    assert.ok(s.intervals.at(-1), 'grace pulse re-checks instead of submitting');
});

test('auto-submit still fires when the server confirms real expiry', () => {
    const s = createSandbox();
    const expire = s.clock + 60 * 1000;

    s.run(`setExamClockOffset(${s.clock}, ${s.clock})`); // trusted clock
    s.run('startTimer()');
    s.timerRequests()[0].success({ status: 'success', start_time: s.clock, expire_time: expire, duration: 1 });
    const countdown = s.intervals.at(-1);

    for (let i = 0; i < 61; i++) countdown();

    // the re-check: the server itself reports the assessment as expired
    s.timerRequests()[1].error({ status: 422, responseJSON: { status: 'expired', message: 'Assessment sudah berakhir.' } });

    assert.equal(s.submitCount, 1, 'auto-submit fires on confirmed expiry');
    assert.equal(s.get('examFinished'), true);
    assert.equal(s.timerEl.textContent, 'Waktu Habis');
    assert.ok(s.swalTitles.includes('Oops...'), 'the "Waktu Habis" modal is shown');
    assert.equal(s.storage.getItem('timer_assessment_test_start_516_u42'), null, 'timer state cleaned');
    assert.equal(s.storage.getItem('timer_assessment_test_expire_516_u42'), null);
});

test('a trusted server-anchored clock past a fresh server expire finishes immediately', () => {
    const s = createSandbox();
    const expire = s.clock - 30 * 1000;

    s.run(`setExamClockOffset(${s.clock}, ${s.clock})`);
    s.run('startTimer()');
    s.timerRequests()[0].success({ status: 'success', start_time: s.clock - 60 * 1000, expire_time: expire, duration: 90 });

    assert.equal(s.submitCount, 1, 'real expiry at open still auto-submits');
    assert.equal(s.timerEl.textContent, 'Waktu Habis');
    assert.equal(s.timerRequests().length, 1, 'no extra roundtrip needed when the clock is trusted');
});

test('when the server is unreachable the fail-safe still submits after retries', () => {
    const s = createSandbox();

    s.run('startTimer()');
    const expire = s.clock - 30 * 1000;
    s.timerRequests()[0].success({ status: 'success', start_time: s.clock - 60 * 1000, expire_time: expire, duration: 90 });

    // six failed verification attempts (~15s) then the fail-safe fires
    for (let i = 0; i < 6; i++) {
        s.timerRequests().at(-1).error({ status: 0 });
        s.runTimers();
    }

    assert.equal(s.submitCount, 1, 'answers are sent even when the server cannot confirm');
    assert.equal(s.timerEl.textContent, 'Waktu Habis');
});

// ---- /end 422 still_running: the server refused a premature timeout claim ----

test('/end 422 still_running resumes the exam instead of retrying or finishing', () => {
    const s = createExamSandbox();
    s.run('window.__endFinished = 0');

    s.run('autoSubmitUnSavedQuestions(function () { window.__endFinished++; }, "timeout", 0)');
    s.runTimers(); // jitterMs 0 → the /end POST goes out immediately

    assert.equal(s.endRequests().length, 1);
    assert.equal(s.get('examFinished'), true, 'locally finished while the claim is in flight');

    s.endRequests()[0].error({
        status: 422,
        responseJSON: {
            status: 'still_running',
            message: 'Waktu ujian masih berjalan menurut jam server. Ujian dilanjutkan.',
            expire_time: s.clock + 30 * 60 * 1000,
            remaining_seconds: 1800,
        },
    });

    assert.equal(s.get('examFinished'), false, 'exam is live again');
    assert.equal(s.get('isWarningModalOpen'), false);
    assert.ok(s.swalTitles.includes('Waktu belum habis — ujian dilanjutkan.'), 'short toast shown');
    assert.equal(s.endRequests().length, 1, 'no /end retry loop');
    assert.equal(s.run('window.__endFinished'), 0, 'onFinish not called — the exam continues');
    assert.notEqual(s.get('heartbeatInterval'), null, 'heartbeat resumed');
    assert.notEqual(s.timerEl.textContent, 'Waktu Habis');

    // the countdown resyncs from the server (fresh expire + clock anchor)
    const resync = s.timerRequests().at(-1);
    assert.ok(resync, 'start-timer resync requested');
    resync.success({
        status: 'success', start_time: s.clock, expire_time: s.clock + 30 * 60 * 1000,
        duration: 90, user_id: 42, server_now_ms: s.clock,
    });
    assert.ok(s.intervals.length > 0, 'countdown running again');
    assert.equal(s.storage.getItem('timer_assessment_test_start_516_u42'), String(s.clock));
    assert.equal(s.get('examFinished'), false);
});

test('/end success still finishes the exam and reports onFinish', () => {
    const s = createExamSandbox();
    s.run('window.__endFinished = 0');

    s.run('autoSubmitUnSavedQuestions(function () { window.__endFinished++; }, "timeout", 0)');
    s.runTimers();
    s.endRequests()[0].success({ status: 'success' });

    assert.equal(s.run('window.__endFinished'), 1);
    assert.equal(s.get('examFinished'), true, 'real expiry is untouched');
});

test('/end network failure still retries before giving up', () => {
    const s = createExamSandbox();

    s.run('autoSubmitUnSavedQuestions(null, "timeout", 0)');
    s.runTimers();

    for (let i = 0; i < 3; i++) {
        s.endRequests().at(-1).error({ status: 0 });
        s.runTimers();
    }

    assert.equal(s.endRequests().length, 3, 'three attempts, then the give-up modal');
    assert.ok(s.swalTitles.includes('Jawaban Belum Terkirim'));
    assert.equal(s.get('examFinished'), true);
});

// ---- pre-start window: stay on the page, poll, never redirect ----

test('a not_started exam keeps the student on the page with a dismissible dialog and a 10s poll', () => {
    const s = createSandbox();

    s.run('startTimer()');
    s.timerRequests()[0].error({ status: 422, responseJSON: { status: 'not_started', message: 'Assessment belum dimulai.' } });

    const modal = s.swalShots.find((o) => o.title === 'Assessment Belum Dimulai');
    assert.ok(modal, 'pre-start dialog shown');
    assert.equal(modal.allowOutsideClick, true, 'dismissible');
    assert.equal(modal.allowEscapeKey, true);
    assert.ok(s.intervals.length > 0, '10s poll registered');

    // still not started: keep waiting silently, no dialog spam
    s.intervals.at(-1)();
    s.ajaxCalls.find((c) => /check-assessment-status/.test(c.url)).success({ status: 'not_started' });
    assert.equal(s.swalShots.filter((o) => o.title === 'Assessment Belum Dimulai').length, 1);

    // the window opens: hand off to the normal start flow (student-form is
    // not loaded in this sandbox, so the fallback drives the countdown)
    s.intervals.at(-1)();
    s.ajaxCalls.filter((c) => /check-assessment-status/.test(c.url)).at(-1).success({ status: 'active' });
    const resync = s.timerRequests().at(-1);
    assert.ok(resync, 'the start flow takes over');
    resync.success({
        status: 'success', start_time: s.clock, expire_time: s.clock + 90 * 60 * 1000,
        duration: 90, user_id: 42, server_now_ms: s.clock,
    });

    assert.ok(s.intervals.length > 0, 'countdown running');
    assert.equal(s.get('examFinished'), false);
    assert.equal(s.get('isWarningModalOpen'), false, 'pre-start dialog closed');
});

test('a pre-start poll that reports expired stops waiting without redirecting', () => {
    const s = createSandbox();

    s.run('startTimer()');
    s.timerRequests()[0].error({ status: 422, responseJSON: { status: 'not_started', message: 'Assessment belum dimulai.' } });
    s.intervals.at(-1)();
    s.ajaxCalls.find((c) => /check-assessment-status/.test(c.url)).success({ status: 'expired' });

    assert.ok(s.swalShots.some((o) => o.title === 'Assessment Telah Berakhir'));
    assert.equal(s.get('isWarningModalOpen'), true);

    // the poll stopped: firing the interval again issues no new request
    const checks = s.ajaxCalls.filter((c) => /check-assessment-status/.test(c.url)).length;
    s.intervals.at(-1)();
    assert.equal(s.ajaxCalls.filter((c) => /check-assessment-status/.test(c.url)).length, checks);
});
