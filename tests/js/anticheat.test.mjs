// Run: node --test tests/js/
// Loads the real anticheat core + platform files into a vm sandbox with stubbed
// DOM / jQuery / SweetAlert and a controllable clock and timer queue.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const dir = path.join(path.dirname(fileURLToPath(import.meta.url)), '../../public/assets/js/features/lms/student/assessment/anticheat');

const UA = {
    windows: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
    ipad: 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1',
    ipadDesktopMode: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/17.0 Safari/605.1.15',
    iphone: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1',
    android: 'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36',
};

// ua: user agent; touch: maxTouchPoints (iPadOS desktop mode needs > 1)
function createSandbox({ ua = UA.windows, touch = 0, started = false } = {}) {
    const listeners = {};
    const addListener = (target) => (type, fn) => {
        (listeners[target] ||= {})[type] ||= [];
        listeners[target][type].push(fn);
    };
    const timers = [];
    const ajaxCalls = [];
    const beacons = [];
    const sockets = [];
    const prevented = [];
    const intervals = [];
    const bodyChildren = [];
    let clock = 1_000_000;

    const makeElement = () => {
        const button = { addEventListener(type, fn) { button.onClick = fn; } };
        return {
            style: {},
            setAttribute() {},
            querySelector: () => button,
            button,
        };
    };

    const document = {
        hidden: false,
        activeElement: null,
        fullscreenEnabled: true,
        fullscreenElement: null,
        documentElement: { requestFullscreen: () => Promise.resolve() },
        addEventListener: addListener('document'),
        hasFocus: () => true,
        querySelector: () => null,
        getElementById: (id) => bodyChildren.find((el) => el.id === id) || null,
        createElement: () => makeElement(),
        head: { appendChild() {} },
        body: { appendChild: (el) => bodyChildren.push(el) },
    };

    const sandbox = {
        console,
        document,
        navigator: {
            userAgent: ua,
            maxTouchPoints: touch,
            sendBeacon: (url, body) => { beacons.push({ url, body }); return true; },
        },
        FormData: class {
            constructor() { this.fields = {}; }
            append(k, v) { this.fields[k] = v; }
        },
        screen: {},
        localStorage: { getItem: () => (started ? 'started' : null), setItem() {}, removeItem() {} },
        Date: { now: () => clock },
        setTimeout: (fn, ms) => { timers.push({ fn, at: clock + (ms || 0) }); return timers.length; },
        clearTimeout: () => {},
        setInterval: (fn) => { intervals.push(fn); return intervals.length; },
        clearInterval: () => {},
        Swal: { fire: () => ({ then() { return this; } }), close: () => { sandbox.swalClosed = true; } },
        WebSocket: class { constructor(url) { this.url = url; sockets.push(this); } close() { this.closed = true; } },
        location: { protocol: 'https:', host: 'example.test', href: '' },
        $: () => ({ attr: () => 'csrf' }),
        getTotalExamDuration: () => 0, // defined by the student form script in the page
        role: 'student', schoolName: 'sekolah', schoolId: 1, curriculumId: 1,
        mapelId: 1, assessmentTypeId: 1, semester: 1, assessmentId: 1,
    };
    sandbox.$.ajax = (opts) => { ajaxCalls.push(opts); };
    sandbox.window = sandbox;
    sandbox.addEventListener = addListener('window');
    vm.createContext(sandbox);

    for (const file of ['anticheat-core.js', 'anticheat-desktop.js', 'anticheat-mobile.js', 'anticheat-ios.js']) {
        vm.runInContext(fs.readFileSync(path.join(dir, file), 'utf8'), sandbox, { filename: file });
    }

    const fire = (target, type, event = {}) => {
        for (const fn of listeners[target]?.[type] || []) {
            fn({ preventDefault: () => prevented.push(type), ...event });
        }
    };
    const runTimers = () => {
        for (const t of timers.splice(0)) t.fn();
    };
    const advance = (ms) => { clock += ms; };

    return {
        sandbox, document, ajaxCalls, beacons, sockets, prevented, intervals, bodyChildren, fire, runTimers, advance,
        get: (expr) => vm.runInContext(expr, sandbox),
        run: (code) => vm.runInContext(code, sandbox),
        // full: real initAntiCheatSystem (overlay, heartbeat, cooldown release); otherwise platform layer only
        init: ({ full = true } = {}) => vm.runInContext(
            full ? 'initAntiCheatSystem()' : 'AntiCheatPlatforms.forEach(function (p) { p.init(); })', sandbox),
        lastReason: () => ajaxCalls.at(-1)?.data.reason,
        overlay: () => bodyChildren.find((el) => el.id === 'anticheat-fullscreen-overlay') || null,
    };
}

const flush = () => new Promise((resolve) => setImmediate(resolve));

test('window blur while typing in a text field is logged as a warning, not a strike', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');
    s.document.activeElement = { tagName: 'INPUT', isContentEditable: false, closest: () => null };
    s.document.hasFocus = () => false;

    s.fire('window', 'blur');
    s.advance(600);
    s.runTimers();

    assert.equal(s.ajaxCalls.length, 1);
    assert.equal(s.lastReason(), 'window_blur');
    assert.equal(s.ajaxCalls[0].data.warning, true);
});

test('window blur from a button is still a strike', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');
    s.document.activeElement = { tagName: 'BUTTON', isContentEditable: false, closest: () => null };
    s.document.hasFocus = () => false;

    s.fire('window', 'blur');
    s.advance(600);
    s.runTimers();

    assert.equal(s.ajaxCalls[0].data.warning, false);
});

test('window blur while a SweetAlert dialog is focused is not reported', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');
    s.document.activeElement = { tagName: 'BUTTON', isContentEditable: false, closest: (sel) => (sel.includes('swal2') ? {} : null) };
    s.document.hasFocus = () => false;

    s.fire('window', 'blur');
    s.advance(600);
    s.runTimers();

    assert.equal(s.ajaxCalls.length, 0);
});

test('a duplicate (ok) response re-arms detection instead of leaving it stuck', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');

    s.run("reportCheating('visibility_hidden')");
    assert.equal(s.get('antiCheatCooldown'), true);

    s.ajaxCalls[0].success({ status: 'ok' });
    assert.equal(s.get('antiCheatCooldown'), false);
});

test('reportCheating reports whether a request was actually sent', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');

    assert.equal(s.run("reportCheating('a')"), true);
    assert.equal(s.run("reportCheating('b')"), false, 'inside the 4s debounce');
});

test('Grammarly strike is retried when the debounce drops the first report', () => {
    const s = createSandbox();
    s.init({ full: false });
    s.run('antiCheatCooldown = false');
    s.document.querySelector = (sel) => (sel.includes('grammarly') ? {} : null);

    s.run("reportCheating('visibility_hidden')");
    s.ajaxCalls[0].success({ status: 'ok' });
    s.intervals.forEach((fn) => fn());
    assert.equal(s.ajaxCalls.length, 1, 'dropped by debounce, nothing sent');

    s.advance(5000);
    s.intervals.forEach((fn) => fn());
    assert.equal(s.ajaxCalls.length, 2);
    assert.equal(s.lastReason(), 'grammarly_extension');

    s.ajaxCalls[1].success({ status: 'ok' });
    s.advance(5000);
    s.intervals.forEach((fn) => fn());
    assert.equal(s.ajaxCalls.length, 2);
});

test('F5 and Ctrl+R reload is allowed and beaconed as a warning, never a strike', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');

    s.fire('document', 'keydown', { key: 'F5', ctrlKey: false, metaKey: false, shiftKey: false, altKey: false });
    s.fire('document', 'keydown', { key: 'r', ctrlKey: true, metaKey: false, shiftKey: false, altKey: false });

    assert.equal(s.prevented.length, 0, 'reload is not blocked');
    assert.equal(s.ajaxCalls.length, 0, 'no strike request');
    assert.equal(s.beacons.length, 2);
    assert.match(s.beacons[0].url, /report-tab-switch$/);
    assert.equal(s.beacons[0].body.fields.warning, '1');
    assert.equal(s.beacons[0].body.fields.reason, 'reload_shortcut');
});

test('Ctrl+L is still blocked and struck as a navigation shortcut', () => {
    const s = createSandbox();
    s.init();
    s.run('antiCheatCooldown = false');

    s.fire('document', 'keydown', { key: 'l', ctrlKey: true, metaKey: false, shiftKey: false, altKey: false });

    assert.equal(s.prevented.length, 1);
    assert.equal(s.ajaxCalls.length, 1);
    assert.equal(s.lastReason(), 'tab_shortcut');
    assert.equal(s.ajaxCalls[0].data.warning, false);
});

test('iPad (iPadOS UA and desktop-mode Mac UA) has no fullscreen overlay and is not locked out', () => {
    for (const [ua, touch] of [[UA.ipad, 0], [UA.ipadDesktopMode, 5]]) {
        const s = createSandbox({ ua, touch, started: true });
        s.init();

        // fullscreenEnabled is true on iPad, but the overlay must not exist
        assert.equal(s.get('isFullscreenSupported()'), false);
        assert.equal(s.overlay(), null);

        // detection is released after the startup buffer so leave-events count
        s.runTimers();
        assert.equal(s.get('antiCheatCooldown'), false);
    }
});

test('overlay button refused by the browser releases the exam instead of locking it', async () => {
    const s = createSandbox();
    s.document.documentElement.requestFullscreen = () => Promise.reject(new Error('not allowed'));
    s.init();
    await flush(); // the auto-request on load settles before the student clicks

    assert.ok(s.overlay(), 'desktop shows the overlay before fullscreen');
    s.overlay().style.display = 'flex';
    s.overlay().querySelector().onClick();
    await flush();

    assert.equal(s.get('isFullscreenSupported()'), false);
    assert.equal(s.overlay().style.display, 'none');
    assert.equal(s.get('antiCheatCooldown'), false);
});

test('a fullscreen request refused without a click (auto on load) does not disable fullscreen', async () => {
    const s = createSandbox();
    s.document.documentElement.requestFullscreen = () => Promise.reject(new Error('no gesture'));
    s.init();
    await flush();

    assert.equal(s.get('isFullscreenSupported()'), true);
});

test('leaving and returning on iPad counts one strike, not one per return event', () => {
    const s = createSandbox({ ua: UA.ipad });
    s.init();
    s.run('antiCheatCooldown = false');

    s.document.hidden = true;
    s.fire('document', 'visibilitychange');
    s.fire('window', 'pagehide');
    s.ajaxCalls[0].success({ status: 'ok' });

    s.advance(30_000);
    s.document.hidden = false;
    s.fire('document', 'visibilitychange');
    s.fire('window', 'pageshow', { persisted: true });

    assert.equal(s.ajaxCalls.length, 1);
    assert.equal(s.lastReason(), 'visibility_hidden');
});

test('Android freeze/resume cycle counts one strike, not one per return event', () => {
    const s = createSandbox({ ua: UA.android, touch: 5 });
    s.init();
    s.run('antiCheatCooldown = false');

    s.fire('document', 'freeze');
    s.ajaxCalls[0].success({ status: 'ok' });

    s.advance(30_000);
    s.fire('document', 'resume');
    s.fire('window', 'pageshow', { persisted: true });

    assert.equal(s.ajaxCalls.length, 1);
    assert.equal(s.lastReason(), 'page_freeze');
});

test('a blocked attempt stays on the page and opens the unlock socket instead of redirecting', () => {
    const s = createSandbox();
    s.init();
    s.run('handleBlocked()');

    assert.equal(s.get('examFinished'), true);
    assert.equal(s.sandbox.location.href, '', 'no redirect while waiting for unlock');
    assert.equal(s.ajaxCalls.length, 1, 'requests a student token');
    assert.match(s.ajaxCalls[0].url, /exam-token/);

    s.ajaxCalls[0].success({ url: '/proctor/ws', token: 'tok' });
    assert.equal(s.sockets.length, 1);
    assert.match(s.sockets[0].url, /^wss:\/\/example\.test\/proctor\/ws\?token=tok$/);
});

test('an approved unlock resumes the exam in place', () => {
    const s = createSandbox();
    s.init();
    s.run('handleBlocked()');
    s.ajaxCalls[0].success({ url: '/proctor/ws', token: 'tok' });

    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'unlock_resolved', attempt_id: 1, status: 'approved' }) });

    assert.equal(s.get('examFinished'), false, 'exam continues');
    assert.equal(s.get('isWarningModalOpen'), false);
    assert.equal(s.get('antiCheatCooldown'), false, 'detection re-armed');
    assert.equal(s.sandbox.location.href, '', 'no redirect');
    assert.notEqual(s.sockets[0].closed, true, 'the exam socket stays open for pause, resume and stop');
});

test('a denied unlock redirects to the assessment list', () => {
    const s = createSandbox();
    s.init();
    s.run('handleBlocked()');
    s.ajaxCalls[0].success({ url: '/proctor/ws', token: 'tok' });

    s.sockets[0].onmessage({ data: JSON.stringify({ type: 'unlock_resolved', attempt_id: 1, status: 'denied' }) });

    assert.match(s.sandbox.location.href, /learning\/assessment/);
});
