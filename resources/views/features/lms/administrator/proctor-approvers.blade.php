@include('components/sidebar-beranda', ['headerSideNav' => 'PIC Buka Kunci'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
    <div class="mt-4 sm:mt-6 mb-10 mx-7.5">
        <main class="bg-white rounded-2xl shadow-lg p-8 border border-gray-300">
            <div id="pic-root"
                data-data-url="{{ route('admin.proctorApprovers.data') }}"
                data-toggle-url="{{ url('/administrator/proctor-approvers') }}"
                data-csrf="{{ csrf_token() }}">

                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">PIC Buka Kunci Ujian</h1>
                    <p class="text-gray-500 mt-1 text-sm">
                        Hanya pengguna yang diberi izin di sini yang dapat menyetujui permintaan buka kunci dari guru.
                        Guru yang mengajukan tidak dapat menyetujui permintaannya sendiri.
                    </p>
                </div>

                <input id="pic-search" type="text" placeholder="Cari email pengguna..."
                    class="w-full md:w-96 border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4">

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-gray-200">
                                <th class="py-2">Email</th>
                                <th class="py-2">Peran</th>
                                <th class="py-2">Status PIC</th>
                                <th class="py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pic-list"></tbody>
                    </table>
                </div>

                <p id="pic-empty" class="text-gray-500 text-sm mt-4 hidden">Tidak ada pengguna yang cocok.</p>
            </div>
        </main>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('pic-root');
    const list = document.getElementById('pic-list');
    const search = document.getElementById('pic-search');
    const empty = document.getElementById('pic-empty');
    const cfg = root.dataset;

    async function api(url, options = {}) {
        const res = await fetch(url, {
            ...options,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'Content-Type': 'application/json' },
            credentials: 'same-origin',
        });
        return { ok: res.ok, body: await res.json().catch(() => ({})) };
    }

    function cell(text, className) {
        const td = document.createElement('td');
        td.className = className || 'py-2';
        td.textContent = text;
        return td;
    }

    function render(users) {
        list.replaceChildren();
        empty.classList.toggle('hidden', users.length > 0);

        users.forEach((user) => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.append(cell(user.email));
            tr.append(cell(user.role, 'py-2 text-gray-600'));

            const status = document.createElement('td');
            status.className = 'py-2';
            const badge = document.createElement('span');
            badge.className = user.can_approve_unlock
                ? 'px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs'
                : 'px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs';
            badge.textContent = user.can_approve_unlock ? 'PIC' : 'Bukan PIC';
            status.append(badge);
            tr.append(status);

            const action = document.createElement('td');
            action.className = 'py-2 text-right';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = user.can_approve_unlock
                ? 'px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-800'
                : 'px-3 py-1.5 rounded-lg text-sm font-medium bg-green-600 hover:bg-green-700 text-white';
            button.textContent = user.can_approve_unlock ? 'Cabut izin' : 'Jadikan PIC';
            button.addEventListener('click', async () => {
                const { ok } = await api(`${cfg.toggleUrl}/${user.id}`, {
                    method: 'POST',
                    body: JSON.stringify({ enabled: !user.can_approve_unlock }),
                });
                if (!ok) return alert('Gagal menyimpan perubahan.');
                load();
            });
            action.append(button);
            tr.append(action);
            list.append(tr);
        });
    }

    async function load() {
        const { ok, body } = await api(`${cfg.dataUrl}?q=${encodeURIComponent(search.value.trim())}`);
        if (!ok) return alert('Gagal memuat daftar pengguna.');
        render(body.users || []);
    }

    let timer = null;
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(load, 300);
    });

    load();
})();
</script>
