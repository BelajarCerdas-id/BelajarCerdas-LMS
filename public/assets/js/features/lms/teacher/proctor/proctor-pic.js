// PIC overview: today's exams + pending unlock requests; approve/deny and per-class detail links.
(function () {
    const container = document.getElementById('container-pic-exams');
    if (!container) return;

    const cfg = container.dataset;
    const base = `/lms/${cfg.role}/${cfg.schoolName}/${cfg.schoolId}/proctor`;
    const timer = setInterval(tick, 500);

    async function load() {
        const res = await fetch(`${base}/pic/data`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const body = await res.json();
        renderExams(body.exams);
        renderRequests(body.requests);
    }

    function renderExams(exams) {
        const grid = document.getElementById('grid-pic-exams');
        grid.replaceChildren();
        exams.forEach((a) => {
            const card = document.createElement('div');
            card.className = 'border border-gray-200 rounded-2xl shadow-sm p-5';
            const state = { not_started: ['Belum dimulai', 'bg-gray-100 text-gray-700'], running: ['Berjalan', 'bg-green-100 text-green-800'], paused: ['Dijeda', 'bg-amber-100 text-amber-800'], stopped: ['Dihentikan', 'bg-red-100 text-red-800'] }[a.state] || ['', ''];
            const badge = document.createElement('span');
            badge.className = `text-xs px-2 py-1 rounded-full ${state[1]}`;
            badge.textContent = state[0];
            const title = document.createElement('p');
            title.className = 'font-semibold text-gray-800 mt-2';
            title.textContent = a.assessment_title;
            const meta = document.createElement('p');
            meta.className = 'text-xs text-gray-500 mt-1';
            meta.textContent = `${a.class_name} · ${a.time}`;
            const foot = document.createElement('div');
            foot.className = 'flex items-center justify-between mt-3';
            if (a.pending_unlocks > 0) {
                const pending = document.createElement('span');
                pending.className = 'text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-1 rounded-full';
                pending.textContent = `${a.pending_unlocks} permintaan menunggu`;
                foot.append(pending);
            }
            const link = document.createElement('a');
            link.href = `${base}#ujian-${a.assessment_id}`;
            link.className = 'text-xs text-[#0071BC] hover:underline font-semibold';
            link.textContent = 'Detail kelas →';
            foot.append(link);
            card.append(badge, title, meta, foot);
            grid.append(card);
        });
    }

    function renderRequests(requests) {
        const tbody = document.getElementById('tbody-pic-requests');
        tbody.replaceChildren();
        document.getElementById('empty-message-pic-requests').classList.toggle('hidden', requests.length > 0);

        requests.forEach((r) => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.dataset.requestId = r.request_id;
            tr.dataset.type = r.type;

            const name = document.createElement('td');
            name.className = 'px-3 py-2 font-medium text-gray-800';
            name.textContent = r.student_name;

            const exam = document.createElement('td');
            exam.className = 'px-3 py-2 text-gray-600';
            exam.textContent = r.exam_title;

            const cls = document.createElement('td');
            cls.className = 'px-3 py-2 text-gray-600';
            cls.textContent = r.class_name;

            const type = document.createElement('td');
            type.className = 'px-3 py-2';
            const badge = document.createElement('span');
            badge.className = r.type === 'unlock'
                ? 'text-xs px-2 py-1 rounded-full bg-red-100 text-red-800'
                : 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800';
            badge.textContent = r.type === 'unlock' ? 'Buka kunci' : 'Batal tolak masuk';
            type.append(badge);

            const count = document.createElement('td');
            count.className = 'px-3 py-2 text-gray-600';
            count.textContent = r.type === 'unlock' ? `${r.count}/${r.limit}` : '-';

            const left = document.createElement('td');
            left.className = 'px-3 py-2 text-amber-700';
            left.dataset.expiresAt = new Date(r.expires_at).getTime();
            left.textContent = `${Math.max(0, Math.ceil(r.expires_in))} dtk`;

            const actions = document.createElement('td');
            actions.className = 'px-3 py-2';
            actions.append(
                button('Setujui', 'bg-green-600 hover:bg-green-700 text-white', () => decide(r, true)),
                button('Tolak', 'bg-gray-200 hover:bg-gray-300 text-gray-800', () => decide(r, false)),
            );

            tr.append(name, exam, cls, type, count, left, actions);
            tbody.append(tr);
        });
    }

    function button(label, classes, onClick) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = `px-3 py-1.5 rounded-lg text-xs font-medium mr-1 ${classes}`;
        b.textContent = label;
        b.addEventListener('click', onClick);
        return b;
    }

    async function decide(r, approve) {
        const path = r.type === 'unlock'
            ? `${base}/unlock-requests/${r.request_id}/${approve ? 'approve' : 'deny'}`
            : `${base}/entry-revocations/${r.request_id}/${approve ? 'approve' : 'deny'}`;
        const res = await fetch(path, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
        });
        const body = await res.json().catch(() => ({}));
        if (!res.ok && body.message) alert(body.message);
        load();
    }

    // countdown for each pending request; expired rows disappear on the next refresh
    function tick() {
        document.querySelectorAll('#tbody-pic-requests [data-expires-at]').forEach((td) => {
            const left = Math.ceil((Number(td.dataset.expiresAt) - Date.now()) / 1000);
            if (left <= 0) return load();
            td.textContent = `${left} dtk`;
        });
    }

    load();
    setInterval(load, 5000);
})();
