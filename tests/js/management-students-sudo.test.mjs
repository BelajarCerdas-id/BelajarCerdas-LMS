// Run: node --test tests/js/
// Loads the students-in-class lister into a vm sandbox with a jQuery stub and
// captures the row markup appended when the paginate call answers, checking the
// admin-gated "Masuk sebagai" (sudo) action. Mirrors anticheat-settings.test.mjs.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const file = path.join(path.dirname(fileURLToPath(import.meta.url)), '../../public/assets/js/features/lms/administrator/lms-subscription-management-students.js');

function pageElement() {
    return {
        classList: { add() {}, remove() {}, toggle() {} },
        innerHTML: '',
    };
}

function createSandbox({ isAdmin = '1' } = {}) {
    const rowsMarkup = [];
    const ajaxCalls = [];
    const pageData = { role: 'administrator', schoolName: 'SMA Sendiri', schoolId: '1', managedRole: 'Siswa', classId: '2', majorId: '', isAdmin };

    const container = { dataset: pageData };
    const byId = {
        'container-management-lms-students-list': container,
        'school-detail-card': pageElement(),
        'class-teacher-card': pageElement(),
        'container-bulk-action-promote-to-next-class': pageElement(),
    };

    const generic = () => ({
        length: 0,
        on() {}, off() {},
        each() {},
        map() { return { get: () => [] }; },
        get: () => [],
        prop() {}, val() { return ''; }, is() { return false; },
        text() {}, data() { return undefined; },
        addClass() {}, removeClass() {}, toggleClass() {},
        show() {}, hide() {}, empty() {},
        append() {},
    });

    const sandbox = {
        console,
        document: {
            getElementById: (id) => byId[id] || null,
        },
        setTimeout: (fn) => fn(),
    };
    sandbox.$ = (sel) => {
        if (sel === sandbox.document) {
            const doc = { ready(fn) { fn(); }, on() {}, off() { return doc; } };
            return doc;
        }
        if (sel === '#table-management-lms-students-list') {
            return {
                empty() {},
                append(markup) { rowsMarkup.push(markup); },
            };
        }
        return generic();
    };
    sandbox.$.ajax = (opts) => { ajaxCalls.push(opts); };
    sandbox.$.each = (obj, fn) => Object.entries(obj).forEach(([k, v]) => fn(k, v));
    sandbox.window = sandbox;
    vm.createContext(sandbox);
    vm.runInContext(fs.readFileSync(file, 'utf8'), sandbox, { filename: 'lms-subscription-management-students.js' });

    const respond = () => {
        assert.ok(ajaxCalls.length, 'paginate call issued on load');
        ajaxCalls.at(-1).success({
            data: [{
                id: 11,
                user_account: { id: 77, student_profile: { nama_lengkap: 'Budi', enrollment_type: 'Reguler' } },
                academic_action: null,
                student_class_status: 'active',
                has_academic_action: false,
                school_class: {
                    class_name: 'X-A', tahun_ajaran: '2026/2027',
                    school_major: { major_name: 'IPA' },
                    user_account: { school_staff_profile: { nama_lengkap: 'Pak Sri' } },
                },
            }],
            schoolIdentity: {
                nama_sekolah: 'SMA Sendiri', npsn: '123',
                user_account: { school_staff_profile: { nik: '321' } },
            },
            activeStudentCount: 1,
        });
    };

    return { rowsMarkup, respond, lastCall: () => ajaxCalls.at(-1) };
}

test('admins get a Masuk sebagai (sudo) link per student row pointing at the user account', () => {
    const s = createSandbox({ isAdmin: '1' });
    s.respond();
    const row = s.rowsMarkup.at(-1);
    assert.ok(row, 'student row rendered');
    assert.ok(row.includes('href="/lms/administrator/sudo/77"'), 'sudo link points at the user account id');
    assert.ok(row.includes('fa-user-secret'), 'same icon as the manage-account rows page');
    assert.ok(row.includes('Masuk sebagai pengguna ini'), 'same tooltip as the manage-account rows page');
});

test('non-admin viewers get no sudo link on the students rows', () => {
    const s = createSandbox({ isAdmin: '0' });
    s.respond();
    const row = s.rowsMarkup.at(-1);
    assert.ok(row, 'student row still rendered');
    assert.equal(row.includes('sudo'), false, 'no sudo entry point for school admins');
    assert.ok(row.includes('Budi'), 'the row itself is unchanged');
});

test('the lister still paginates the same endpoint', () => {
    const s = createSandbox({ isAdmin: '1' });
    s.respond();
    assert.equal(
        s.lastCall().url,
        '/lms/administrator/school-subscription/SMA Sendiri/1/role-account/Siswa/management-class/2/management-students/paginate'
    );
});
