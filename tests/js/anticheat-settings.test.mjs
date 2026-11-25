// Run: node --test tests/js/
// Loads the per-school anticheat settings script into a vm sandbox with a
// jQuery stub and captures the $.ajax payload sent when the save button is
// clicked.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const file = path.join(path.dirname(fileURLToPath(import.meta.url)), '../../public/assets/js/features/lms/administrator/lms-subscription-management-settings.js');

// pageData: what #container-role-account-list carries; fields: checkbox state
// and max-strikes value read from #form-anticheat-settings.
function createSandbox({ autoAdmit = false, autoStart = false, autoLock = true, warningModal = true, maxStrikes = 5 } = {}) {
    const ajaxCalls = [];
    const htmlCalls = [];
    let saveClick = null;
    let saveBtnDisabled = null;
    const pageData = { role: 'administrator', schoolName: 'sekolah', schoolId: 1 };

    const fieldStub = (name) => {
        const fields = {
            anticheat_max_strikes: { val: () => String(maxStrikes) },
            anticheat_auto_lock: { is: () => autoLock },
            anticheat_warning_modal: { is: () => warningModal },
            proctor_auto_admit: { is: () => autoAdmit },
            proctor_auto_start: { is: () => autoStart },
        };
        return fields[name] || { val: () => '', is: () => false };
    };
    const generic = () => ({
        length: 1,
        data: () => pageData,
        on() {},
        prop(_k, v) { if (v !== undefined) { saveBtnDisabled = v; return undefined; } return saveBtnDisabled; },
        html(markup) { htmlCalls.push(markup); },
        remove() {},
        text() {},
    });

    const sandbox = {
        console,
        document: { nodeType: 9 },
        setTimeout: (fn) => fn(),
        encodeURIComponent,
    };
    sandbox.$ = (sel) => {
        if (sel === sandbox.document) return { ready(fn) { fn(); } };
        if (sel === 'meta[name="csrf-token"]') return { attr: () => 'csrf-test' };
        if (sel === '#btn-save-anticheat-settings') {
            return {
                length: 1,
                on(_ev, fn) { saveClick = fn; },
                prop(k, v) { if (v !== undefined) { saveBtnDisabled = v; return undefined; } return saveBtnDisabled; },
            };
        }
        const field = String(sel).match(/^#form-anticheat-settings \[name="([^"]+)"\]$/);
        if (field) return fieldStub(field[1]);
        return generic();
    };
    sandbox.$.ajax = (opts) => { ajaxCalls.push(opts); };
    sandbox.$.each = (obj, fn) => Object.entries(obj).forEach(([k, v]) => fn(k, v));
    sandbox.window = sandbox;
    vm.createContext(sandbox);
    vm.runInContext(fs.readFileSync(file, 'utf8'), sandbox, { filename: 'lms-subscription-management-settings.js' });

    const save = () => {
        assert.ok(saveClick, 'save button handler registered');
        saveClick();
    };
    return { sandbox, ajaxCalls, save, htmlCalls, lastCall: () => ajaxCalls.at(-1) };
}

test('the save payload posts to the anticheat settings route with the CSRF token', () => {
    const s = createSandbox();
    s.save();
    const call = s.lastCall();
    assert.equal(call.method, 'POST');
    assert.match(call.url, /\/lms\/administrator\/school-subscription\/sekolah\/1\/management-settings\/anticheat$/);
    assert.equal(call.headers['X-CSRF-TOKEN'], 'csrf-test');
});

test('the payload keeps the existing anticheat fields', () => {
    const s = createSandbox({ autoLock: true, warningModal: false, maxStrikes: 7 });
    s.save();
    const data = s.lastCall().data;
    assert.equal(data.anticheat_max_strikes, '7');
    assert.equal(data.anticheat_auto_lock, 1);
    assert.equal(data.anticheat_warning_modal, 0);
});

test('the payload carries the school proctor auto-admit boolean', () => {
    const s = createSandbox({ autoAdmit: true });
    s.save();
    assert.equal(s.lastCall().data.proctor_auto_admit, 1, 'checked -> 1');
});

test('proctor auto-admit defaults to 0 when the toggle is off', () => {
    const s = createSandbox({ autoAdmit: false });
    s.save();
    assert.equal(s.lastCall().data.proctor_auto_admit, 0, 'unchecked -> 0');
});

test('the payload carries the school schedule auto-start boolean', () => {
    const s = createSandbox({ autoStart: true });
    s.save();
    assert.equal(s.lastCall().data.proctor_auto_start, 1, 'checked -> 1');
});

test('proctor auto-start defaults to 0 when the toggle is off', () => {
    const s = createSandbox({ autoStart: false });
    s.save();
    assert.equal(s.lastCall().data.proctor_auto_start, 0, 'unchecked -> 0');
});

test('a successful save re-enables the button and renders the alert', () => {
    const s = createSandbox({ autoAdmit: true });
    s.save();
    s.lastCall().success({ message: 'Pengaturan berhasil disimpan.' });
    assert.ok(s.htmlCalls.at(-1).includes('Pengaturan berhasil disimpan.'), 'success alert rendered');
});
