// Proctor timeline: all exams, one exam, or one student. Read-only.
(function () {
    const root = document.getElementById('timeline-root');
    if (!root) return;

    const cfg = root.dataset;
    const rows = document.getElementById('timeline-rows');
    const status = document.getElementById('timeline-status');
    const examSelect = document.getElementById('timeline-exam');
    const studentSearch = document.getElementById('timeline-student-search');
    const studentList = document.getElementById('timeline-student-list');
    const studentSelected = document.getElementById('timeline-student-selected');
    let scope = 'all';
    let studentId = null;
    let searchTimer = null;
    let loadToken = 0;
    // The dashboard's date filter links here with ?date=YYYY-MM-DD; the data endpoint
    // defaults to today when it is absent or malformed.
    const filterDate = /^\d{4}-\d{2}-\d{2}$/.test(new URLSearchParams(location.search).get('date') || '')
        ? new URLSearchParams(location.search).get('date')
        : '';

    async function getJson(url) {
        const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        const body = await res.json().catch(() => ({}));
        return { ok: res.ok, body };
    }

    function cell(value, className) {
        const td = document.createElement('td');
        td.className = `py-2 pr-3 align-top ${className || ''}`;
        td.textContent = value ?? '—';
        return td;
    }

    function render(events, truncated) {
        rows.replaceChildren();
        events.forEach((e) => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.append(cell(e.at, 'whitespace-nowrap text-gray-600'));

            const kind = cell(e.label, 'font-medium text-gray-800');
            tr.append(kind);
            tr.append(cell(e.actor ? `${e.actor}${e.actor_role ? ` (${e.actor_role})` : ''}` : 'Siswa', 'text-gray-600'));
            tr.append(cell(e.student, 'text-gray-700'));
            tr.append(cell(e.exam, 'text-gray-600'));
            tr.append(cell(e.detail, 'text-gray-500'));
            rows.append(tr);
        });

        if (events.length === 0) status.textContent = 'Belum ada kejadian untuk filter ini.';
        else status.textContent = truncated ? `Menampilkan ${events.length} kejadian terbaru.` : `${events.length} kejadian.`;
    }

    async function load() {
        const token = ++loadToken;
        const params = new URLSearchParams();

        if (scope === 'exam') {
            if (!examSelect.value) {
                rows.replaceChildren();
                status.textContent = 'Pilih ujian untuk melihat timeline-nya.';
                return;
            }
            params.set('assessment_id', examSelect.value);
        }
        if (scope === 'student') {
            if (!studentId) {
                rows.replaceChildren();
                status.textContent = 'Cari dan pilih siswa untuk melihat timeline-nya.';
                return;
            }
            params.set('student_id', studentId);
        }
        if (filterDate) params.set('date', filterDate);

        status.textContent = 'Memuat...';
        const { ok, body } = await getJson(`${cfg.dataUrl}?${params}`);
        if (token !== loadToken) return;
        if (!ok) {
            rows.replaceChildren();
            status.textContent = 'Gagal memuat timeline.';
            return;
        }
        render(body.events, body.truncated);
    }

    function setScope(next) {
        scope = next;
        document.querySelectorAll('.scope-btn').forEach((b) => {
            const active = b.dataset.scope === next;
            b.className = `scope-btn px-3 py-1.5 rounded-lg text-sm font-medium ${active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'}`;
        });
        document.getElementById('scope-exam').classList.toggle('hidden', next !== 'exam');
        document.getElementById('scope-student').classList.toggle('hidden', next !== 'student');
        load();
    }

    document.querySelectorAll('.scope-btn').forEach((b) => b.addEventListener('click', () => setScope(b.dataset.scope)));
    examSelect.addEventListener('change', load);

    studentSearch.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            studentList.replaceChildren();
            const term = studentSearch.value.trim();
            if (term.length < 2) return;

            const { ok, body } = await getJson(`${cfg.studentsUrl}?q=${encodeURIComponent(term)}`);
            if (!ok) return;
            body.students.forEach((s) => {
                const li = document.createElement('li');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'text-left text-blue-700 hover:underline';
                btn.textContent = s.name;
                btn.addEventListener('click', () => {
                    studentId = s.id;
                    studentSelected.textContent = `Siswa: ${s.name}`;
                    studentList.replaceChildren();
                    load();
                });
                li.append(btn);
                studentList.append(li);
            });
        }, 300);
    });

    setScope('all');
})();
