@include('components.sidebar-beranda', [
    'linkBackButton' => url()->previous(),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Academic Drive',
    'schoolName' => $schoolName,
])

<div
    class="relative left-0 md:left-[250px] w-full md:w-[calc(100%-250px)] min-h-screen transition-all duration-300 ease-in-out"
>
    <div
        id="academicDrive"
        class="mx-6 my-8 space-y-6"
    data-role="{{ $role }}"
    data-school-name="{{ $schoolName }}"
    data-school-id="{{ $schoolId }}"
    data-files-url-template="{{ url('/lms/' . rawurlencode($role) . '/' . rawurlencode($schoolName) . '/' . $schoolId . '/registrasi-guru/academic-drive/files/__SUBJECT__/__TEACHER__/__FOLDER__') }}"
    data-download-url-template="{{ url('/lms/' . rawurlencode($role) . '/' . rawurlencode($schoolName) . '/' . $schoolId . '/registrasi-guru/academic-drive/download/__DOCUMENT__') }}"
    data-download-selected-url="{{ url('/lms/' . rawurlencode($role) . '/' . rawurlencode($schoolName) . '/' . $schoolId . '/registrasi-guru/academic-drive/download-selected') }}"
    data-delete-selected-url="{{ url('/lms/' . rawurlencode($role) . '/' . rawurlencode($schoolName) . '/' . $schoolId . '/registrasi-guru/academic-drive/delete-selected') }}"
>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0071BC] text-white shadow-md">
                    <i class="fas fa-hard-drive text-lg"></i>
                </div>

                <div>
                    <h1 class="text-2xl font-black text-slate-800">
                        Academic Drive
                    </h1>
                    <p class="text-sm text-slate-500">
                        Arsip dokumen akademik berdasarkan mata pelajaran dan guru.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                id="btnBack"
                type="button"
                class="hidden rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </button>

            <button
                id="btnRefresh"
                type="button"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50"
            >
                <i class="fas fa-rotate-right mr-2"></i>
                Refresh
            </button>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div id="breadcrumb" class="flex flex-wrap items-center gap-2 text-sm"></div>
                    <p id="driveDescription" class="mt-1 text-xs text-slate-400">
                        Pilih mata pelajaran untuk melihat guru.
                    </p>
                </div>

                <div class="relative w-full lg:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        id="driveSearch"
                        type="search"
                        placeholder="Cari mapel, guru, atau file..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#0071BC] focus:bg-white focus:ring-2 focus:ring-blue-100"
                    >
                </div>
            </div>
        </div>

        <div class="px-6 py-5">
            <div
                id="driveToolbar"
                class="mb-5 hidden items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0071BC] text-white">
                        <i class="fas fa-check"></i>
                    </div>

                    <div>
                        <div class="text-sm font-black text-slate-700">
                            <span id="selectedCount">0</span> dipilih
                        </div>
                        <div class="text-xs text-slate-500">
                            File dapat diunduh sekaligus atau dihapus sekaligus.
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        id="btnDownloadSelected"
                        type="button"
                        class="rounded-xl bg-[#0071BC] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700"
                    >
                        <i class="fas fa-download mr-2"></i>
                        Download
                    </button>

                    <button
                        id="btnDeleteSelected"
                        type="button"
                        class="rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-600"
                    >
                        <i class="fas fa-trash mr-2"></i>
                        Hapus
                    </button>

                    <button
                        id="btnClearSelected"
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600"
                    >
                        Batal
                    </button>
                </div>
            </div>

            <div
                id="driveGrid"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            ></div>

            <div
                id="driveEmpty"
                class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 py-16 text-center"
            >
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-slate-300 shadow-sm">
                    <i class="fas fa-folder-open text-2xl"></i>
                </div>

                <h3 id="emptyTitle" class="font-black text-slate-700">
                    Belum ada data
                </h3>

                <p id="emptyDescription" class="mt-1 text-sm text-slate-400">
                    Tidak ada data untuk ditampilkan.
                </p>
            </div>

            <div
                id="driveLoading"
                class="hidden py-16 text-center"
            >
                <i class="fas fa-spinner fa-spin text-3xl text-[#0071BC]"></i>
                <p class="mt-3 text-sm font-semibold text-slate-500">
                    Memuat dokumen...
                </p>
            </div>
        </div>
    </div>
    </div>
</div>

<style>
    .drive-card {
        cursor: pointer;
        transition: .2s ease;
    }

    .drive-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
    }

    .drive-file-row {
        transition: .15s ease;
    }

    .drive-file-row:hover {
        background: #f8fafc;
    }

    .drive-file-row.selected {
        background: #eff6ff;
    }
</style>

@php
    /*
     * Gabungkan mata pelajaran yang namanya sama.
     * Contoh: Bahasa Indonesia yang memiliki 2-3 record
     * tetap ditampilkan sebagai 1 folder/mapel.
     * Guru dari semua record yang digabung juga disatukan.
     */
    $mergedTeachersByMapel = [];
    $groupedMapels = collect($mapels)->groupBy(function ($mapel) {
        $name = method_exists($mapel, 'getAttribute')
            ? (
                $mapel->mata_pelajaran
                ?? $mapel->nama_mapel
                ?? $mapel->nama
                ?? $mapel->name
                ?? ('Mata Pelajaran #' . $mapel->id)
            )
            : ('Mata Pelajaran #' . $mapel->id);

        return mb_strtolower(trim((string) $name));
    });

    $mapelPayload = $groupedMapels->map(function ($group) use ($teachersByMapel, &$mergedTeachersByMapel) {
        $firstMapel = $group->first();

        $name = method_exists($firstMapel, 'getAttribute')
            ? (
                $firstMapel->mata_pelajaran
                ?? $firstMapel->nama_mapel
                ?? $firstMapel->nama
                ?? $firstMapel->name
                ?? ('Mata Pelajaran #' . $firstMapel->id)
            )
            : ('Mata Pelajaran #' . $firstMapel->id);

        $teachers = collect();

        foreach ($group as $mapel) {
            $rows = $teachersByMapel[$mapel->id]
                ?? $teachersByMapel[(string) $mapel->id]
                ?? [];

            $teachers = $teachers->concat($rows);
        }

        $teachers = $teachers
            ->filter(fn ($teacher) => !empty($teacher['id']))
            ->unique(fn ($teacher) => (string) $teacher['id'])
            ->values()
            ->all();

        /* Gunakan ID record pertama sebagai ID gabungan. */
        $mergedId = $firstMapel->id;
        $mergedTeachersByMapel[$mergedId] = $teachers;

        return [
            'id' => $mergedId,
            'name' => trim((string) $name),
            'teacher_count' => count($teachers),
        ];
    })->values();
@endphp

<script>
(function () {
    'use strict';

    const root = document.getElementById('academicDrive');

    if (!root) {
        return;
    }

    const data = {
        role: root.dataset.role || '',
        schoolName: root.dataset.schoolName || '',
        schoolId: root.dataset.schoolId || '',
        filesUrlTemplate: root.dataset.filesUrlTemplate || '',
        downloadUrlTemplate: root.dataset.downloadUrlTemplate || '',
        downloadSelectedUrl: root.dataset.downloadSelectedUrl || '',
        deleteSelectedUrl: root.dataset.deleteSelectedUrl || '',
    };

    const teachersByMapel = @json($mergedTeachersByMapel);

    const mapels = @json($mapelPayload);
    // Empat folder dokumen yang selalu tersedia untuk setiap guru.
    // Key ini harus sama dengan nilai folder yang diterima endpoint controller.
    const folders = {
        analisis: {
            label: 'Analisis',
            icon: 'fa-chart-line',
            color: 'blue',
        },
        prota: {
            label: 'PROTA',
            icon: 'fa-calendar-days',
            color: 'emerald',
        },
        rppm: {
            label: 'RPPM',
            icon: 'fa-file-lines',
            color: 'amber',
        },
        refleksi: {
            label: 'Refleksi',
            icon: 'fa-comments',
            color: 'violet',
        },
    };

    const grid = document.getElementById('driveGrid');
    const empty = document.getElementById('driveEmpty');
    const loading = document.getElementById('driveLoading');
    const search = document.getElementById('driveSearch');
    const breadcrumb = document.getElementById('breadcrumb');
    const description = document.getElementById('driveDescription');
    const toolbar = document.getElementById('driveToolbar');
    const selectedCount = document.getElementById('selectedCount');
    const btnBack = document.getElementById('btnBack');
    const btnRefresh = document.getElementById('btnRefresh');
    const btnDownloadSelected = document.getElementById('btnDownloadSelected');
    const btnDeleteSelected = document.getElementById('btnDeleteSelected');
    const btnClearSelected = document.getElementById('btnClearSelected');

    let state = {
        level: 'folder',
        subject: null,
        teacher: null,
        folder: null,
        files: [],
        selected: new Set(),
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function folderColor(folder) {
        const colors = {
            blue: 'bg-blue-50 text-blue-600 border-blue-100',
            emerald: 'bg-emerald-50 text-emerald-600 border-emerald-100',
            amber: 'bg-amber-50 text-amber-600 border-amber-100',
            violet: 'bg-violet-50 text-violet-600 border-violet-100',
        };

        return colors[folder?.color] || colors.blue;
    }

    function fileIcon(type) {
        if (String(type).includes('word') || String(type).includes('refleksi') || String(type).includes('rppm')) {
            return 'fa-file-word text-blue-600';
        }

        return 'fa-file-excel text-emerald-600';
    }

    function renderBreadcrumb() {
        const parts = [
            `<button type="button" data-nav="folder" class="font-black text-[#0071BC] hover:underline">Academic Drive</button>`
        ];

        if (state.folder) {
            parts.push(`<i class="fas fa-chevron-right text-[10px] text-slate-300"></i>`);
            parts.push(`<button type="button" data-nav="mapel" class="font-bold text-[#0071BC] hover:underline">${escapeHtml(folders[state.folder].label)}</button>`);
        }

        if (state.subject) {
            parts.push(`<i class="fas fa-chevron-right text-[10px] text-slate-300"></i>`);
            parts.push(`<button type="button" data-nav="teacher" class="font-bold text-[#0071BC] hover:underline">${escapeHtml(state.subject.name)}</button>`);
        }

        if (state.teacher) {
            parts.push(`<i class="fas fa-chevron-right text-[10px] text-slate-300"></i>`);
            parts.push(`<span class="font-black text-slate-700">${escapeHtml(state.teacher.name)}</span>`);
        }

        breadcrumb.innerHTML = parts.join('');
    }

    function setEmpty(title, desc) {
        empty.classList.remove('hidden');
        document.getElementById('emptyTitle').textContent = title;
        document.getElementById('emptyDescription').textContent = desc;
    }

    function clearEmpty() {
        empty.classList.add('hidden');
    }

    /* ================================================================
       LEVEL 1: 4 FOLDER UTAMA
       ================================================================ */
    function renderFolders() {
        state.level = 'folder';
        state.subject = null;
        state.teacher = null;
        state.files = [];
        state.selected.clear();

        renderBreadcrumb();

        description.textContent = 'Pilih jenis dokumen untuk melihat mata pelajaran dan guru yang berkaitan.';
        btnBack.classList.add('hidden');
        toolbar.classList.add('hidden');

        const keyword = search.value.trim().toLowerCase();

        const rows = Object.entries(folders).filter(([key, folder]) =>
            !keyword || folder.label.toLowerCase().includes(keyword)
        );

        grid.className = 'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4';

        grid.innerHTML = rows.map(([key, folder]) => `
            <button
                type="button"
                class="drive-card rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm"
                data-folder="${key}"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border ${folderColor(folder)}">
                        <i class="fas ${folder.icon} text-xl"></i>
                    </div>

                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase text-slate-500">
                        Folder
                    </span>
                </div>

                <div class="mt-5">
                    <div class="text-base font-black text-slate-800">
                        ${escapeHtml(folder.label)}
                    </div>

                    <div class="mt-1 text-xs text-slate-400">
                        Arsip ${escapeHtml(folder.label)} semua guru
                    </div>
                </div>

                <div class="mt-5 flex items-center gap-2 text-xs font-bold text-[#0071BC]">
                    Buka folder
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </div>
            </button>
        `).join('');

        if (!rows.length) {
            setEmpty('Folder tidak ditemukan', 'Tidak ada folder dokumen yang sesuai dengan pencarian.');
        } else {
            clearEmpty();
        }

        grid.querySelectorAll('[data-folder]').forEach(button => {
            button.addEventListener('click', () => {
                state.folder = button.dataset.folder;
                renderMapels();
            });
        });
    }

    /* ================================================================
       LEVEL 2: MAPEL DI DALAM FOLDER
       ================================================================ */
    function renderMapels() {
        state.level = 'mapel';
        state.subject = null;
        state.teacher = null;
        state.files = [];
        state.selected.clear();

        renderBreadcrumb();

        description.textContent = `Pilih mata pelajaran untuk melihat guru yang berkaitan dengan folder ${folders[state.folder]?.label || ''}.`;
        btnBack.classList.remove('hidden');
        toolbar.classList.add('hidden');

        const keyword = search.value.trim().toLowerCase();

        const rows = mapels.filter(item =>
            !keyword || item.name.toLowerCase().includes(keyword)
        );

        grid.className = 'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4';

        grid.innerHTML = rows.map(item => `
            <button
                type="button"
                class="drive-card rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm"
                data-mapel="${item.id}"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-[#0071BC]">
                        <i class="fas fa-folder text-2xl"></i>
                    </div>

                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase text-slate-500">
                        ${item.teacher_count} Guru
                    </span>
                </div>

                <div class="mt-5">
                    <div class="truncate text-base font-black text-slate-800">
                        ${escapeHtml(item.name)}
                    </div>

                    <div class="mt-1 text-xs text-slate-400">
                        Mata pelajaran dalam folder ${escapeHtml(folders[state.folder]?.label || '')}
                    </div>
                </div>
            </button>
        `).join('');

        if (!rows.length) {
            setEmpty('Mata pelajaran tidak ditemukan', 'Belum ada mata pelajaran yang tersedia.');
        } else {
            clearEmpty();
        }

        grid.querySelectorAll('[data-mapel]').forEach(button => {
            button.addEventListener('click', () => {
                const id = Number(button.dataset.mapel);
                state.subject = mapels.find(item => Number(item.id) === id) || null;
                renderTeachers();
            });
        });
    }

    /* ================================================================
       LEVEL 3: AKUN GURU YANG BERKAITAN DENGAN MAPEL
       ================================================================ */
    function renderTeachers() {
        state.level = 'teacher';
        state.teacher = null;
        state.files = [];
        state.selected.clear();

        renderBreadcrumb();

        description.textContent = `Akun guru yang mengajar ${state.subject?.name || 'mata pelajaran ini'}.`;
        btnBack.classList.remove('hidden');
        toolbar.classList.add('hidden');

        const keyword = search.value.trim().toLowerCase();
        const teachers = teachersByMapel[String(state.subject.id)] || teachersByMapel[state.subject.id] || [];

        const rows = teachers.filter(item =>
            !keyword ||
            String(item.name || '').toLowerCase().includes(keyword) ||
            String(item.username || '').toLowerCase().includes(keyword) ||
            String(item.email || '').toLowerCase().includes(keyword)
        );

        grid.className = 'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4';

        grid.innerHTML = rows.map(teacher => `
            <button
                type="button"
                class="drive-card rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm"
                data-teacher="${teacher.id}"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-sky-50 text-sky-600">
                        <i class="fas fa-user text-2xl"></i>
                    </div>

                    <i class="fas fa-chevron-right text-slate-300"></i>
                </div>

                <div class="mt-5">
                    <div class="truncate text-base font-black text-slate-800">
                        ${escapeHtml(teacher.name || 'Guru')}
                    </div>

                    <div class="mt-1 truncate text-xs text-slate-400">
                        ${escapeHtml(teacher.email || teacher.username || 'Akun Guru')}
                    </div>

                    <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-[11px] font-bold text-[#0071BC]">
                        <i class="fas fa-folder-open"></i>
                        Buka arsip guru
                    </div>
                </div>
            </button>
        `).join('');

        if (!rows.length) {
            setEmpty('Belum ada akun guru', 'Tidak ada guru yang berkaitan dengan mata pelajaran ini.');
        } else {
            clearEmpty();
        }

        grid.querySelectorAll('[data-teacher]').forEach(button => {
            button.addEventListener('click', () => {
                const id = Number(button.dataset.teacher);
                state.teacher = teachers.find(item => Number(item.id) === id) || null;
                loadFiles();
            });
        });
    }

    async function loadFiles() {
        state.level = 'file';
        state.files = [];
        state.selected.clear();

        renderBreadcrumb();

        description.textContent = `Semua file ${folders[state.folder].label} milik ${state.teacher?.name || 'guru'}.`;
        btnBack.classList.remove('hidden');

        grid.innerHTML = '';
        clearEmpty();
        loading.classList.remove('hidden');
        toolbar.classList.add('hidden');

        try {
            const url = data.filesUrlTemplate
                .replace('__SUBJECT__', encodeURIComponent(state.subject.id))
                .replace('__TEACHER__', encodeURIComponent(state.teacher.id))
                .replace('__FOLDER__', encodeURIComponent(state.folder));

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Gagal mengambil daftar file.');
            }

            state.files = result.documents || [];
            renderFiles();

        } catch (error) {
            setEmpty('Gagal memuat file', error.message || 'Terjadi kesalahan.');
        } finally {
            loading.classList.add('hidden');
        }
    }

    function renderFiles() {
        const keyword = search.value.trim().toLowerCase();

        const rows = state.files.filter(file =>
            !keyword ||
            String(file.title || '').toLowerCase().includes(keyword) ||
            String(file.original_filename || '').toLowerCase().includes(keyword)
        );

        if (!rows.length) {
            grid.innerHTML = '';
            setEmpty(
                'Folder masih kosong',
                'Belum ada file yang pernah disimpan oleh guru ini.'
            );
            toolbar.classList.add('hidden');
            return;
        }

        clearEmpty();

        grid.className = 'overflow-hidden rounded-2xl border border-slate-200';

        grid.innerHTML = `
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="w-12 px-4 py-4 text-center">
                                <input
                                    id="selectAllFiles"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-[#0071BC] focus:ring-[#0071BC]"
                                >
                            </th>
                            <th class="px-4 py-4 text-left">Nama File</th>
                            <th class="px-4 py-4 text-left">Tipe</th>
                            <th class="px-4 py-4 text-left">Ukuran</th>
                            <th class="px-4 py-4 text-left">Terakhir Diubah</th>
                            <th class="px-4 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${rows.map(file => `
                            <tr class="drive-file-row ${state.selected.has(Number(file.id)) ? 'selected' : ''}" data-row="${file.id}">
                                <td class="px-4 py-4 text-center">
                                    <input
                                        type="checkbox"
                                        class="file-checkbox h-4 w-4 rounded border-slate-300 text-[#0071BC] focus:ring-[#0071BC]"
                                        data-file-id="${file.id}"
                                        ${state.selected.has(Number(file.id)) ? 'checked' : ''}
                                    >
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50">
                                            <i class="fas ${fileIcon(file.document_type)} text-lg"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="truncate font-bold text-slate-800">
                                                ${escapeHtml(file.original_filename || file.title || 'Dokumen')}
                                            </div>

                                            <div class="truncate text-xs text-slate-400">
                                                ${escapeHtml(file.title || '')}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-slate-500">
                                    ${escapeHtml(file.document_type || '-')}
                                </td>

                                <td class="px-4 py-4 text-slate-500">
                                    ${escapeHtml(file.file_size || '-')}
                                </td>

                                <td class="px-4 py-4 text-slate-500">
                                    ${escapeHtml(file.updated_at || file.created_at || '-')}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="${escapeHtml(file.file_url || '#')}"
                                            target="_blank"
                                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 ${file.file_url ? '' : 'pointer-events-none opacity-40'}"
                                            title="Buka"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a
                                            href="${data.downloadUrlTemplate.replace('__DOCUMENT__', encodeURIComponent(file.id))}"
                                            class="rounded-lg bg-[#0071BC] px-3 py-2 text-xs font-bold text-white hover:bg-blue-700"
                                            title="Download"
                                        >
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        updateToolbar();

        document.getElementById('selectAllFiles')?.addEventListener('change', function () {
            rows.forEach(file => {
                const id = Number(file.id);

                if (this.checked) {
                    state.selected.add(id);
                } else {
                    state.selected.delete(id);
                }
            });

            renderFiles();
        });

        grid.querySelectorAll('.file-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const id = Number(this.dataset.fileId);

                if (this.checked) {
                    state.selected.add(id);
                } else {
                    state.selected.delete(id);
                }

                updateToolbar();

                const row = grid.querySelector(`[data-row="${id}"]`);

                if (row) {
                    row.classList.toggle('selected', this.checked);
                }
            });
        });
    }

    function updateToolbar() {
        const count = state.selected.size;

        selectedCount.textContent = count;
        toolbar.classList.toggle('hidden', count === 0);
        toolbar.classList.toggle('flex', count > 0);
    }

    async function downloadSelected() {
        if (!state.selected.size) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = data.downloadSelectedUrl;
        form.target = '_blank';

        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        if (token) {
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = token;
            form.appendChild(csrf);
        }

        state.selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'document_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    async function deleteSelected() {
        if (!state.selected.size) {
            return;
        }

        const count = state.selected.size;

        if (!confirm(`Hapus ${count} dokumen yang dipilih? File fisik juga akan dihapus dari storage.`)) {
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const response = await fetch(data.deleteSelectedUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    document_ids: Array.from(state.selected),
                }),
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Gagal menghapus dokumen.');
            }

            state.selected.clear();

            alert(result.message || 'Dokumen berhasil dihapus.');

            await loadFiles();

        } catch (error) {
            alert(error.message || 'Gagal menghapus dokumen.');
        }
    }

    function goBack() {
        if (state.level === 'file') {
            renderTeachers();
            return;
        }

        if (state.level === 'teacher') {
            renderMapels();
            return;
        }

        if (state.level === 'mapel') {
            renderFolders();
            return;
        }
    }

    breadcrumb.addEventListener('click', function (event) {
        const button = event.target.closest('[data-nav]');

        if (!button) {
            return;
        }

        const target = button.dataset.nav;

        if (target === 'folder') {
            renderFolders();
        } else if (target === 'mapel' && state.folder) {
            renderMapels();
        } else if (target === 'teacher' && state.folder && state.subject) {
            renderTeachers();
        }
    });

    btnBack.addEventListener('click', goBack);

    btnRefresh.addEventListener('click', function () {
        if (state.level === 'file') {
            loadFiles();
        } else if (state.level === 'teacher') {
            renderTeachers();
        } else if (state.level === 'mapel') {
            renderMapels();
        } else {
            renderFolders();
        }
    });

    btnDownloadSelected.addEventListener('click', downloadSelected);
    btnDeleteSelected.addEventListener('click', deleteSelected);

    btnClearSelected.addEventListener('click', function () {
        state.selected.clear();

        if (state.level === 'file') {
            renderFiles();
        } else {
            updateToolbar();
        }
    });

    search.addEventListener('input', function () {
        if (state.level === 'folder') {
            renderFolders();
        } else if (state.level === 'mapel') {
            renderMapels();
        } else if (state.level === 'teacher') {
            renderTeachers();
        } else {
            renderFiles();
        }
    });

    renderFolders();
})();
</script>
