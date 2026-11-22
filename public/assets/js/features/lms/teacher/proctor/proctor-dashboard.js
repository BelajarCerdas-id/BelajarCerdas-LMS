// Proctor dashboard: overview of today's exams; open one exam (#ujian-ID) for a student table and controls.
// Laravel snapshot is the source of truth; WS events only update the view in place.
// Rows that changed blink: changed keys are collected between renders and applied after the DOM is rebuilt.
(function () {
    const root = document.getElementById('proctor-root');
    if (!root) return;

    const cfg = root.dataset;
    const canApprove = cfg.canApprove === '1';
    const students = new Map();    // "examId:userId" -> student row
    const assessments = new Map(); // assessment_id -> { assessment_id, assessment_title, time, state }
    const lateQueue = [];
    let showingLate = false;
    let ws = null;
    let snapshotTimer = null;
    let renderTimer = null;
    let sortMode = 'status';
    const blinkKeys = new Set();

    const REASONS = {
        devtools_open: 'membuka developer tools',
        visibility_hidden: 'pindah aplikasi',
        window_blur: 'keluar dari jendela ujian',
        tab_shortcut: 'menekan shortcut tab',
        reload_shortcut: 'memuat ulang halaman',
        heartbeat_gap: 'koneksi terputus',
        page_hide: 'meninggalkan halaman',
        fullscreen_exit: 'keluar dari layar penuh',
    };
    const reasonLabel = (r) => REASONS[r] || 'aktivitas mencurigakan';

    const STATUS_LABEL = {
        waiting: 'Menunggu izin',
        denied: 'Ditolak',
        not_joined: 'Belum masuk',
        in_progress: 'Mengerjakan',
        cheating: 'Terkunci',
        submitted: 'Selesai',
        timeout: 'Waktu habis',
    };

    const STATE_BADGE = {
        not_started: ['Belum dimulai', 'bg-gray-100 text-gray-700'],
        running: ['Berjalan', 'bg-green-100 text-green-800'],
        paused: ['Dijeda', 'bg-amber-100 text-amber-800'],
        stopped: ['Dihentikan', 'bg-red-100 text-red-800'],
    };

    const setStatus = (t) => { document.getElementById('proctor-status').textContent = t; };

    async function api(url, options = {}) {
        const res = await fetch(url, {
            ...options,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'Content-Type': 'application/json', ...(options.headers || {}) },
            credentials: 'same-origin',
        });
        const body = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, body };
    }

    // Snapshots are throttled, not debounced: a burst of events must not postpone the refresh forever.
    function scheduleSnapshot() {
        if (snapshotTimer) return;
        snapshotTimer = setTimeout(() => { snapshotTimer = null; loadSnapshot(); }, 500);
    }

    function renderSoon() {
        if (renderTimer) return;
        renderTimer = setTimeout(() => { renderTimer = null; render(); }, 150);
    }

    function noticeOf(s) {
        if (s.status === 'cheating') return s.unlock_request_id ? 'Menunggu persetujuan buka kunci' : 'Terkunci';
        if (s.status === 'waiting') return 'Menunggu izin masuk dari guru';
        if (s.status === 'denied') return 'Tidak diizinkan mengikuti ujian ini';
        if (s.notice) return s.notice;
        if (s.count) return `Pindah tab (${s.count}/${s.limit})`;
        return '';
    }

    async function loadSnapshot() {
        const { ok, body } = await api(cfg.snapshotUrl);
        if (!ok) return setStatus('Gagal memuat data siswa.');
        applySnapshot(body);
        render();
    }

    function applySnapshot(body) {
        const next = new Map();
        assessments.clear();
        body.assessments.forEach((a) => assessments.set(a.assessment_id, a));
        body.students.forEach((s) => next.set(s.key, s));

        for (const [key, before] of students) {
            const after = next.get(key);
            if (after && rowChanged(before, after)) blinkKeys.add(key);
            else if (!after) blinkKeys.delete(key);
        }
        for (const key of next.keys()) {
            if (!students.has(key)) blinkKeys.add(key);
        }
        const notices = new Map([...students].filter(([, s]) => s.notice).map(([k, s]) => [k, s.notice]));
        students.clear();
        next.forEach((v, k) => students.set(k, { ...v, notice: notices.get(k) }));
    }

    function rowChanged(a, b) {
        const keys = ['status', 'count', 'limit', 'last_heartbeat_at', 'unlock_request_id', 'entry_request_id'];
        return keys.some((k) => (a[k] ?? null) !== (b[k] ?? null));
    }

    // ---- navigation: overview or one exam, kept in the URL hash so a reload stays on the same exam ----

    function openedExam() {
        const m = location.hash.match(/^#ujian-(\d+)$/);
        return m ? assessments.get(Number(m[1])) || null : null;
    }

    function render() {
        const box = document.getElementById('proctor-cards');
        box.replaceChildren();
        const exam = openedExam();
        box.append(exam ? examDetailEl(exam) : dashboardEl());
        applyBlink();
    }

    // Re-render replaces the DOM; blinking is a CSS animation that restarts when the node is created.
    function applyBlink() {
        blinkKeys.forEach((key) => {
            const row = document.querySelector(`[data-student-key="${CSS.escape(key)}"]`);
            if (row) row.classList.add('proctor-blink');
        });
        if (blinkKeys.size) setTimeout(() => blinkKeys.clear(), 4000);
    }

    function openExam(id) { location.hash = `ujian-${id}`; }
    function backToDashboard() { location.hash = ''; }

    function dashboardEl() {
        const wrap = document.createElement('div');
        wrap.className = 'space-y-6';
        const all = [...assessments.values()];

        const waiting = [...students.values()].filter((s) => s.status === 'waiting').length;
        const locked = [...students.values()].filter((s) => s.status === 'cheating').length;
        const working = [...students.values()].filter((s) => s.status === 'in_progress').length;
        wrap.append(statRow([
            ['Ujian hari ini', all.length],
            ['Sedang berjalan', all.filter((a) => a.state === 'running').length],
            ['Menunggu masuk', waiting],
            ['Terkunci', locked],
            ['Mengerjakan', working],
        ]));

        if (all.length === 0) {
            wrap.append(text('p', 'Tidak ada ujian yang dijadwalkan hari ini.', 'text-gray-500 text-sm'));
            return wrap;
        }

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4';
        all.forEach((a) => grid.append(examTileEl(a)));
        wrap.append(grid);
        return wrap;
    }

    function statRow(items) {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4';
        items.forEach(([label, value]) => {
            const box = document.createElement('div');
            box.className = 'rounded-xl bg-gray-50 border border-gray-200 p-4';
            box.append(text('p', label, 'text-xs text-gray-500'));
            box.append(text('p', String(value), 'text-2xl font-bold text-gray-800 mt-1'));
            row.append(box);
        });
        return row;
    }

    function examStats(a) {
        const rows = [...students.values()].filter((s) => s.assessment_id === a.assessment_id);
        return {
            rows,
            waiting: rows.filter((s) => s.status === 'waiting'),
            working: rows.filter((s) => s.status === 'in_progress').length,
            locked: rows.filter((s) => s.status === 'cheating').length,
            finished: rows.filter((s) => s.status === 'submitted' || s.status === 'timeout').length,
        };
    }

    function examTileEl(a) {
        const s = examStats(a);
        const el = document.createElement('div');
        el.setAttribute('role', 'button');
        el.tabIndex = 0;
        el.className = 'cursor-pointer rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition';
        el.addEventListener('click', () => openExam(a.assessment_id));
        el.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') openExam(a.assessment_id); });

        const head = document.createElement('div');
        head.className = 'flex items-start justify-between gap-2';
        head.append(text('h3', a.assessment_title || 'Ujian', 'font-semibold text-gray-800'));
        head.append(stateBadge(a.state));
        el.append(head);
        el.append(text('p', a.time || '', 'text-xs text-gray-500 mt-1'));

        const mini = document.createElement('div');
        mini.className = 'grid grid-cols-4 gap-2 mt-4 text-center';
        [['Menunggu', s.waiting.length], ['Mengerjakan', s.working], ['Terkunci', s.locked], ['Selesai', s.finished]].forEach(([label, value]) => {
            const cell = document.createElement('div');
            cell.append(text('p', String(value), 'text-lg font-bold text-gray-800'));
            cell.append(text('p', label, 'text-[11px] text-gray-500'));
            mini.append(cell);
        });
        el.append(mini);
        el.append(text('p', 'Buka ujian →', 'mt-4 text-sm font-medium text-blue-700'));
        return el;
    }

    function stateBadge(state) {
        const [label, badge] = STATE_BADGE[state] || STATE_BADGE.not_started;
        return text('span', label, `shrink-0 text-xs px-2 py-1 rounded-full ${badge}`);
    }

    function examDetailEl(a) {
        const s = examStats(a);
        const wrap = document.createElement('div');
        wrap.className = 'space-y-6';

        const top = document.createElement('div');
        top.className = 'flex flex-wrap items-center justify-between gap-3';
        const titleBox = document.createElement('div');
        titleBox.append(button('← Semua ujian', 'bg-transparent text-blue-700 hover:underline px-0', backToDashboard));
        const title = document.createElement('div');
        title.className = 'flex flex-wrap items-center gap-2 mt-1';
        title.append(text('h2', a.assessment_title || 'Ujian', 'text-xl font-bold text-gray-800'));
        title.append(stateBadge(a.state));
        titleBox.append(title);
        titleBox.append(text('p', a.time || '', 'text-sm text-gray-500'));
        top.append(titleBox);

        const actions = document.createElement('div');
        actions.className = 'flex flex-wrap gap-2';
        if (a.state === 'not_started') actions.append(button('Mulai ujian', 'bg-blue-600 hover:bg-blue-700 text-white', () => control(a, 'start')));
        if (a.state === 'running') actions.append(button('Jeda', 'bg-amber-500 hover:bg-amber-600 text-white', () => control(a, 'pause')));
        if (a.state === 'paused') actions.append(button('Lanjutkan', 'bg-blue-600 hover:bg-blue-700 text-white', () => control(a, 'resume')));
        if (a.state === 'running' || a.state === 'paused') actions.append(button('Hentikan', 'bg-red-600 hover:bg-red-700 text-white', () => confirmStop(a)));
        top.append(actions);
        wrap.append(top);

        wrap.append(statRow([
            ['Menunggu masuk', s.waiting.length],
            ['Mengerjakan', s.working],
            ['Terkunci', s.locked],
            ['Selesai', s.finished],
            ['Total siswa', s.rows.length],
        ]));

        if (s.waiting.length) wrap.append(waitingEl(a, s.waiting));
        wrap.append(tableEl(s.rows));
        return wrap;
    }

    function waitingEl(a, waiting) {
        const box = document.createElement('div');
        box.className = 'rounded-lg bg-amber-50 border border-amber-200 p-3 space-y-2';
        box.append(text('p', `Menunggu masuk: ${waiting.length} siswa`, 'text-sm font-medium text-amber-800'));

        if (a.state !== 'running') {
            const note = a.state === 'paused' ? 'Izin masuk ditangguhkan selama ujian dijeda.' : 'Siswa diperiksa setelah ujian dimulai.';
            box.append(text('p', note, 'text-xs text-amber-700'));
            return box;
        }

        waiting.forEach((e) => {
            const row = document.createElement('div');
            row.className = 'flex flex-wrap items-center justify-between gap-2';
            row.append(text('span', e.student_name, 'text-sm text-gray-800'));
            const actions = document.createElement('div');
            actions.className = 'flex gap-2';
            actions.append(button('Izinkan', 'bg-green-600 hover:bg-green-700 text-white', () => decideEntries([{ request_id: e.entry_request_id, approve: true }])));
            actions.append(button('Tolak', 'bg-gray-200 hover:bg-gray-300 text-gray-800', () => decideEntries([{ request_id: e.entry_request_id, approve: false }])));
            row.append(actions);
            box.append(row);
        });
        return box;
    }

    // ---- student list (table) ----

    const STATUS_ORDER = { cheating: 0, waiting: 1, in_progress: 2, denied: 3, not_joined: 4, submitted: 5, timeout: 6 };

    function tableEl(rows) {
        const wrap = document.createElement('div');
        wrap.className = 'overflow-x-auto';
        if (rows.length === 0) {
            wrap.append(text('p', 'Belum ada siswa pada kelas ujian ini.', 'text-gray-500 text-sm py-2'));
            return wrap;
        }

        const sortSel = document.createElement('select');
        sortSel.className = 'mb-2 border border-gray-300 rounded-lg px-2 py-1 text-sm';
        sortSel.append(new Option('Urutkan: status', 'status'), new Option('Urutkan: nama', 'name'), new Option('Urutkan: waktu terakhir aktif', 'heartbeat'));
        sortSel.value = sortMode;
        sortSel.addEventListener('change', () => { sortMode = sortSel.value; render(); });

        const table = document.createElement('table');
        table.className = 'w-full text-sm';
        const head = document.createElement('tr');
        head.className = 'text-left text-gray-500 border-b border-gray-200';
        [['Siswa', 'name'], ['Status', 'status'], ['Info', null], ['Terakhir aktif', 'heartbeat'], ['Aksi', null]].forEach(([label, mode]) => {
            const th = document.createElement('th');
            th.className = 'py-2 pr-3 cursor-pointer hover:text-gray-800';
            th.textContent = label;
            if (mode) {
                th.addEventListener('click', () => { sortMode = mode; render(); });
                if (sortMode === mode) th.className += ' text-gray-800';
            }
            head.append(th);
        });
        const thead = document.createElement('thead');
        const headerWrap = document.createElement('div');
        headerWrap.append(sortSel, table);
        thead.append(head);
        table.append(thead);

        const tbody = document.createElement('tbody');
        sortedRows(rows).forEach((s) => tbody.append(rowEl(s)));
        table.append(tbody);
        wrap.append(headerWrap);
        return wrap;
    }

    function sortedRows(rows) {
        const copy = [...rows];
        if (sortMode === 'name') copy.sort((a, b) => a.student_name.localeCompare(b.student_name));
        else if (sortMode === 'heartbeat') {
            copy.sort((a, b) => (b.last_heartbeat_at ?? '').localeCompare(a.last_heartbeat_at ?? ''));
        } else {
            copy.sort((a, b) => (STATUS_ORDER[a.status] ?? 9) - (STATUS_ORDER[b.status] ?? 9) || a.student_name.localeCompare(b.student_name));
        }
        return copy;
    }

    function rowEl(s) {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-gray-100';
        tr.dataset.studentKey = s.key;

        const name = document.createElement('td');
        name.className = 'py-2 pr-3 font-medium text-gray-800';
        name.textContent = s.student_name;
        tr.append(name);

        const st = document.createElement('td');
        st.className = 'py-2 pr-3';
        st.append(text('span', STATUS_LABEL[s.status] || s.status, `text-xs px-2 py-1 rounded-full ${s.status === 'cheating' ? 'bg-red-100 text-red-800' : s.status === 'in_progress' ? 'bg-green-100 text-green-800' : s.status === 'waiting' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700'}`));
        tr.append(st);

        const info = document.createElement('td');
        info.className = 'py-2 pr-3 text-gray-600';
        info.textContent = noticeOf(s);
        if (s.count) info.textContent = `Pindah tab (${s.count}/${s.limit}): ${noticeOf(s)}`;
        tr.append(info);

        const hb = document.createElement('td');
        hb.className = 'py-2 pr-3 text-gray-600 whitespace-nowrap';
        hb.textContent = s.last_heartbeat_at ? new Date(s.last_heartbeat_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : '—';
        tr.append(hb);

        const actions = document.createElement('td');
        actions.className = 'py-2 pr-3';
        const box = document.createElement('div');
        box.className = 'flex flex-wrap gap-2';
        box.dataset.role = 'actions';
        if (s.entry_request_id && openedExam()?.state === 'running') {
            box.append(button('Izinkan', 'bg-green-600 hover:bg-green-700 text-white', () => decideEntries([{ request_id: s.entry_request_id, approve: true }])));
            box.append(button('Tolak', 'bg-gray-200 hover:bg-gray-300 text-gray-800', () => decideEntries([{ request_id: s.entry_request_id, approve: false }])));
        } else if (s.status === 'cheating') {
            if (s.unlock_request_id && s.unlock_expires_at && new Date(s.unlock_expires_at) > Date.now()) {
                if (canApprove) {
                    box.append(button('Setujui', 'bg-green-600 hover:bg-green-700 text-white', () => resolveUnlock(s, 'approve')));
                    box.append(button('Tolak', 'bg-gray-200 hover:bg-gray-300 text-gray-800', () => resolveUnlock(s, 'deny')));
                    const span = document.createElement('span');
                    span.className = 'text-sm text-amber-700 self-center';
                    box.append(span);
                    box.dataset.role = 'actions';
                    box.dataset.expiresAt = new Date(s.unlock_expires_at).getTime();
                }
            } else {
                box.append(button('Minta buka kunci', 'bg-blue-600 hover:bg-blue-700 text-white', () => requestUnlock(s)));
            }
        }
        actions.append(box);
        tr.append(actions);
        return tr;
    }

    function button(label, classes, onClick) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = `px-3 py-1.5 rounded-lg text-sm font-medium ${classes}`;
        b.textContent = label;
        b.addEventListener('click', onClick);
        return b;
    }

    function text(tag, value, className, role) {
        const el = document.createElement(tag);
        el.textContent = value;
        if (className) el.className = className;
        if (role) el.dataset.role = role;
        return el;
    }

    // ---- exam session: start, pause, resume, stop ----

    async function control(a, action) {
        const { ok, body } = await api(`${cfg.sessionUrl}/${a.assessment_id}/${action}`, { method: 'POST' });
        if (!ok) return alert(body.message || 'Aksi gagal.');

        a.state = body.state;
        if (action === 'start') {
            render();
            return showChecklist(a);
        }
        render();
    }

    function confirmStop(a) {
        Swal.fire({
            icon: 'warning',
            title: 'Hentikan ujian?',
            text: 'Ujian berakhir untuk semua siswa yang sedang mengerjakan. Jawaban yang sudah tersimpan akan dikirim otomatis.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hentikan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
        }).then((res) => {
            if (res.isConfirmed) control(a, 'stop');
        });
    }

    // Everyone waiting is listed and checked; unchecking a student denies them.
    function showChecklist(a) {
        const waiting = [...students.values()].filter((s) => s.assessment_id === a.assessment_id && s.status === 'waiting');
        if (waiting.length === 0) {
            return Swal.fire({
                icon: 'info',
                title: 'Ujian dimulai',
                text: 'Belum ada siswa yang menunggu. Siswa yang masuk setelah ini akan muncul satu per satu.',
            });
        }

        const list = document.createElement('div');
        list.className = 'text-left max-h-80 overflow-y-auto space-y-2';
        waiting.forEach((e) => {
            const label = document.createElement('label');
            label.className = 'flex items-center gap-2 text-sm text-gray-800';
            const box = document.createElement('input');
            box.type = 'checkbox';
            box.checked = true;
            box.dataset.requestId = e.entry_request_id;
            label.append(box, document.createTextNode(e.student_name));
            list.append(label);
        });

        Swal.fire({
            title: `Izinkan siswa masuk: ${a.assessment_title}`,
            html: list,
            confirmButtonText: 'Terapkan',
            allowOutsideClick: false,
        }).then((res) => {
            if (!res.isConfirmed) return;
            decideEntries(waiting.map((e) => ({
                request_id: e.entry_request_id,
                approve: list.querySelector(`[data-request-id="${e.entry_request_id}"]`).checked,
            })));
        });
    }

    // Students who arrive after the exam started get one modal each.
    function queueLate(e) {
        lateQueue.push(e);
        showNextLate();
    }

    function showNextLate() {
        if (showingLate) return;
        const e = lateQueue.shift();
        if (!e) return;
        const row = [...students.values()].find((s) => s.user_id === e.user_id && s.assessment_id === e.assessment_id && s.status === 'waiting');
        if (!row) return showNextLate();

        showingLate = true;
        const title = assessments.get(e.assessment_id)?.assessment_title || 'ujian';
        Swal.fire({
            icon: 'question',
            title: 'Siswa terlambat',
            text: `${row.student_name} ingin masuk ${title} yang sudah dimulai.`,
            showConfirmButton: true,
            showDenyButton: true,
            confirmButtonText: 'Izinkan',
            denyButtonText: 'Tolak',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(async (res) => {
            if (res.isConfirmed || res.isDenied) {
                await decideEntries([{ request_id: row.entry_request_id, approve: res.isConfirmed }]);
            }
            showingLate = false;
            showNextLate();
        });
    }

    async function decideEntries(decisions) {
        const { ok, body } = await api(cfg.decideUrl, { method: 'POST', body: JSON.stringify({ decisions }) });
        if (!ok) alert(body.message || 'Keputusan gagal disimpan.');
        scheduleSnapshot();
    }

    // ---- unlock (strike-locked students) ----

    async function requestUnlock(s) {
        const { ok, body } = await api(`${cfg.unlockUrl}/${s.attempt_id}/unlock-requests`, { method: 'POST' });
        if (!ok) return alert(body.message || 'Permintaan gagal dikirim.');
        scheduleSnapshot();
    }

    async function resolveUnlock(s, action) {
        const { ok, body } = await api(`${cfg.resolveUrl}/${s.unlock_request_id}/${action}`, { method: 'POST' });
        if (!ok) alert(body.message || 'Permintaan gagal diproses.');
        scheduleSnapshot();
    }

    // Countdown for pending unlock requests; the server enforces the deadline, this only shows it.
    setInterval(() => {
        document.querySelectorAll('[data-role="actions"][data-expires-at]').forEach((box) => {
            const left = Math.ceil((Number(box.dataset.expiresAt) - Date.now()) / 1000);
            const label = box.querySelector('span.text-amber-700');
            if (!label) return;
            if (left <= 0) return scheduleSnapshot();
            label.textContent = `Sisa ${left} detik`;
        });
    }, 500);

    function onEvent(e) {
        if (e.type === 'resync') return scheduleSnapshot();

        if (['session_started', 'exam_paused', 'exam_resumed', 'exam_stopped'].includes(e.type)) {
            const a = assessments.get(e.assessment_id);
            const next = { session_started: 'running', exam_paused: 'paused', exam_resumed: 'running', exam_stopped: 'stopped' }[e.type];
            if (a) { a.state = next; return renderSoon(); }
            return scheduleSnapshot();
        }
        if (e.type === 'entry_requested') {
            markBlinkByUser(e.assessment_id, e.user_id);
            if (assessments.get(e.assessment_id)?.state === 'running') queueLate(e);
            return scheduleSnapshot();
        }
        if (e.type === 'entry_resolved') {
            markBlinkByUser(e.assessment_id, e.user_id);
            return scheduleSnapshot();
        }

        const key = e.attempt_id ? [...students.values()].find((s) => s.attempt_id === e.attempt_id)?.key : null;
        if (!key) return scheduleSnapshot();
        blinkKeys.add(key);

        const s = students.get(key);
        if (e.type === 'strike') {
            s.notice = `Pindah tab (${e.count}/${e.limit}): ${reasonLabel(e.reason)}`;
        } else if (e.type === 'warning') {
            s.notice = `Peringatan: ${reasonLabel(e.reason)}`;
        } else if (e.type === 'blocked') {
            s.notice = 'Terkunci';
        }
        refreshRow(s);
    }

    function markBlinkByUser(assessmentId, userId) {
        const row = [...students.values()].find((s) => s.assessment_id === assessmentId && s.user_id === userId);
        if (row) blinkKeys.add(row.key);
    }

    function refreshRow(s) {
        const tr = document.querySelector(`[data-student-key="${CSS.escape(s.key)}"]`);
        if (!tr) return renderSoon();

        const cells = tr.children;
        cells[2].textContent = noticeOf(s) || (s.count ? `Pindah tab (${s.count}/${s.limit})` : '');
        const badge = cells[1].firstElementChild;
        badge.textContent = STATUS_LABEL[s.status] || s.status;
        badge.className = `text-xs px-2 py-1 rounded-full ${s.status === 'cheating' ? 'bg-red-100 text-red-800' : s.status === 'in_progress' ? 'bg-green-100 text-green-800' : s.status === 'waiting' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700'}`;
        tr.classList.add('proctor-blink');
    }

    async function connect() {
        const { ok, body } = await api(cfg.tokenUrl);
        if (!ok) {
            setStatus('Gagal menghubungkan. Mencoba lagi...');
            return setTimeout(connect, 3000);
        }

        const scheme = location.protocol === 'https:' ? 'wss' : 'ws';
        ws = new WebSocket(`${scheme}://${location.host}${body.url}?token=${encodeURIComponent(body.token)}`);
        ws.onopen = () => { setStatus('Terhubung. Pembaruan langsung aktif.'); loadSnapshot(); };
        ws.onmessage = (msg) => { try { onEvent(JSON.parse(msg.data)); } catch (_) { /* ignore malformed frame */ } };
        ws.onclose = () => { setStatus('Koneksi terputus. Menghubungkan ulang...'); setTimeout(connect, 2000); };
    }

    function setupApprovers() {
        const input = document.getElementById('proctor-approver-search');
        if (!input) return;
        const list = document.getElementById('proctor-approver-list');
        let timer = null;

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                list.replaceChildren();
                if (input.value.trim().length < 3) return;
                const { body } = await api(`${cfg.approversUrl}?q=${encodeURIComponent(input.value.trim())}`);
                body.users.forEach((u) => {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between gap-3 border border-gray-200 rounded-lg px-3 py-2';
                    li.append(text('span', `${u.email} (${u.role})`, 'text-gray-700'));
                    li.append(button(u.can_approve_unlock ? 'Cabut izin' : 'Beri izin', u.can_approve_unlock ? 'bg-gray-200 text-gray-800' : 'bg-green-600 text-white', async () => {
                        await api(`${cfg.approverToggleUrl}/${u.id}`, { method: 'POST', body: JSON.stringify({ enabled: !u.can_approve_unlock }) });
                        input.dispatchEvent(new Event('input'));
                    }));
                    list.append(li);
                });
            }, 300);
        });
    }

    window.addEventListener('hashchange', render);
    setupApprovers();
    connect();
})();
