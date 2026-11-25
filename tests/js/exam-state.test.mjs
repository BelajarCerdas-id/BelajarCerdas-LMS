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

const timerEl = { textContent: '-' };

// userId: data-user-id exposed by the exam blade containers
function createSandbox({ userId = '42', storage = createStorage(), clockStart = 1_000_000 } = {}) {
    const ajaxCalls = [];
    const timers = [];
    const intervals = [];
    const swalTitles = [];
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
            fire: (opts) => { swalTitles.push(opts && opts.title); return { then() { return this; } }; },
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

    vm.runInContext(fs.readFileSync(path.join(dir, 'anticheat/anticheat-core.js'), 'utf8'), sandbox, { filename: 'anticheat-core.js' });
    vm.runInContext(fs.readFileSync(path.join(dir, 'start-timer-assessment-test.js'), 'utf8'), sandbox, { filename: 'start-timer-assessment-test.js' });

    const runTimers = () => { for (const t of timers.splice(0)) t.fn(); };
    const timerRequests = () => ajaxCalls.filter((c) => /start-timer$/.test(c.url));

    return {
        sandbox, storage, ajaxCalls, intervals, swalTitles, timerEl,
        get submitCount() { return submitCount; },
        advance: (ms) => { clock += ms; },
        get clock() { return clock; },
        runTimers,
        timerRequests,
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
