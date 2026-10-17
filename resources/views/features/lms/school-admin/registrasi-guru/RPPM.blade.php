{{-- ========================================================================
     RPPM
     File:
     resources/views/features/lms/school-admin/registrasi-guru/
     tampilan/tamp-rppm.blade.php

     LOGIKA:
     - Controller lama TIDAK diubah.
     - Struktur folder: Guru → Tahun Ajaran → Kelas → Tipe Kelas → Mata Pelajaran.
     - Nama guru ditarik secara tuntas (mendukung Profil Staf & Tabel Users).
     - Card MAPEL hanya muncul jika sudah ada file RPPM.
     - Sumber utama keberadaan file = AcademicDocument.
     - document_type = rppm.
     - Guru hanya melihat file miliknya.
     - Admin/Kepala/Wakil melihat file yang tersedia pada sekolah.
     - Tidak ada mapel hard-code.
     - Klik Tambah File membawa mapel_id.
     ======================================================================== --}}

@include('components/sidebar-beranda', [
    'linkBackButton' => url()->previous(),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'RPPM',

    'schoolName' => optional(
        optional(Auth::user())->SchoolStaffProfile
    )->SchoolPartner->nama_sekolah ?? '',

    'schoolId' => optional(
        optional(Auth::user())->SchoolStaffProfile
    )->SchoolPartner->id ?? '',
])

@php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;

    /* =====================================================================
       USER & SEKOLAH
       ===================================================================== */

    $user = Auth::user();

    $schoolProfile = optional($user)->SchoolStaffProfile;
    $schoolPartner = optional($schoolProfile)->SchoolPartner;

    $schoolName = $schoolPartner->nama_sekolah ?? '';
    $schoolId = $schoolPartner->id ?? null;

    $teacherMapels = collect();
    $jumlahGuru = 0;
    $jumlahMapel = 0;
    $rppmDocuments = collect();

    $currentRole = strtolower(
        trim((string) (optional($user)->role ?? ''))
    );

    /* =====================================================================
       HELPER NAMA KELAS
       ===================================================================== */

    $extractClassName = function ($value) {
        if (is_array($value)) {
            return trim((string) (
                $value['kelas']
                ?? $value['nama_kelas']
                ?? $value['class_name']
                ?? $value['name']
                ?? ''
            ));
        }

        if (is_object($value)) {
            return trim((string) (
                $value->kelas
                ?? $value->nama_kelas
                ?? $value->class_name
                ?? $value->name
                ?? ''
            ));
        }

        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (
            str_starts_with($value, '{')
            || str_starts_with($value, '[')
        ) {
            $decoded = json_decode($value, true);

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {
                return trim((string) (
                    $decoded['kelas']
                    ?? $decoded['nama_kelas']
                    ?? $decoded['class_name']
                    ?? $decoded['name']
                    ?? ''
                ));
            }
        }

        return $value;
    };

    /* =====================================================================
       HELPER PENARIKAN NAMA GURU SECARA TUNTAS (SUPPORT PROFIL STAF & USERS)
       ===================================================================== */

    $resolveTeacherName = function ($assignment) {
        // 1. Cek dari nama langsung di objek penugasan
        if (!empty($assignment->teacher_name)) return $assignment->teacher_name;
        if (!empty($assignment->guru_name)) return $assignment->guru_name;
        if (!empty($assignment->nama_guru)) return $assignment->nama_guru;

        // 2. Cek relasi Eloquent User
        if (!empty($assignment->User->name)) return $assignment->User->name;
        if (!empty($assignment->User->nama)) return $assignment->User->nama;
        if (!empty($assignment->user->name)) return $assignment->user->name;
        if (!empty($assignment->user->nama)) return $assignment->user->nama;

        // 3. Cek relasi Eloquent SchoolStaffProfile
        if (!empty($assignment->SchoolStaffProfile->User->name)) return $assignment->SchoolStaffProfile->User->name;
        if (!empty($assignment->SchoolStaffProfile->User->nama)) return $assignment->SchoolStaffProfile->User->nama;
        if (!empty($assignment->SchoolStaffProfile->nama)) return $assignment->SchoolStaffProfile->nama;
        if (!empty($assignment->SchoolStaffProfile->name)) return $assignment->SchoolStaffProfile->name;
        if (!empty($assignment->schoolStaffProfile->nama)) return $assignment->schoolStaffProfile->nama;

        // 4. Kumpulkan ID yang ada di objek $assignment (termasuk ID Staf seperti 15, dll)
        $ids = array_filter(array_unique([
            $assignment->school_staff_profile_id ?? null,
            $assignment->teacher_id ?? null,
            $assignment->user_id ?? null,
            $assignment->guru_id ?? null,
            $assignment->staff_id ?? null,
        ]));

        foreach ($ids as $id) {
            // A. Cek ke tabel school_staff_profiles (Profil Staf Sekolah)
            if (Schema::hasTable('school_staff_profiles')) {
                $staff = DB::table('school_staff_profiles')->where('id', $id)->first();
                if ($staff) {
                    // Cari via user_id jika terhubung ke tabel users
                    if (!empty($staff->user_id)) {
                        $u = DB::table('users')->where('id', $staff->user_id)->first();
                        if ($u) {
                            $name = $u->name ?? $u->nama ?? $u->username ?? $u->fullname ?? null;
                            if (!empty($name)) return $name;
                        }
                    }
                    // Cari langsung di kolom profil staf
                    $staffName = $staff->nama ?? $staff->name ?? $staff->nama_lengkap ?? $staff->nama_guru ?? null;
                    if (!empty($staffName)) return $staffName;
                }
            }

            // B. Cek langsung ke tabel users
            $uDb = DB::table('users')->where('id', $id)->first();
            if ($uDb) {
                $name = $uDb->name ?? $uDb->nama ?? $uDb->username ?? $uDb->fullname ?? null;
                if (!empty($name)) return $name;
            }

            // C. Cek ke tabel teachers
            if (Schema::hasTable('teachers')) {
                $tDb = DB::table('teachers')->where('id', $id)->first();
                if ($tDb) {
                    if (!empty($tDb->user_id)) {
                        $uT = DB::table('users')->where('id', $tDb->user_id)->first();
                        if ($uT) {
                            $name = $uT->name ?? $uT->nama ?? null;
                            if (!empty($name)) return $name;
                        }
                    }
                    $tName = $tDb->nama ?? $tDb->name ?? $tDb->nama_lengkap ?? null;
                    if (!empty($tName)) return $tName;
                }
            }
        }

        $firstId = reset($ids) ?: 'unknown';
        return 'Guru (ID: ' . $firstId . ')';
    };

    /* =====================================================================
       AMBIL TEACHER MAPEL
       ===================================================================== */

    if ($schoolId) {
        try {
            $teacherMapels = \App\Models\TeacherMapel::query()
                ->with(['Mapel', 'SchoolClass', 'User', 'SchoolStaffProfile'])
                ->where('is_active', true)
                ->whereHas('SchoolClass', function ($query) use ($schoolId) {
                    $query->where('school_partner_id', $schoolId);
                })
                ->get();
        } catch (\Throwable $e) {
            try {
                $teacherMapels = \App\Models\TeacherMapel::query()
                    ->with(['Mapel', 'SchoolClass'])
                    ->where('is_active', true)
                    ->get();
            } catch (\Throwable $fallbackError) {
                $teacherMapels = collect();
            }
        }
    }

    /* =====================================================================
       FILTER SEKOLAH
       ===================================================================== */

    if ($schoolId && $teacherMapels->isNotEmpty()) {
        $teacherMapels = $teacherMapels
            ->filter(function ($assignment) use ($schoolId) {
                $schoolClass = $assignment->SchoolClass ?? null;

                if (!$schoolClass) {
                    return false;
                }

                return (int) ($schoolClass->school_partner_id ?? 0)
                    === (int) $schoolId;
            })
            ->values();
    }

    /* =====================================================================
       AMBIL ACADEMIC DOCUMENT RPPM
       ===================================================================== */

    if ($schoolId) {
        try {
            $documentQuery = \App\Models\AcademicDocument::query()
                ->where('school_partner_id', $schoolId)
                ->where('document_type', 'rppm')
                ->whereNotNull('file_path')
                ->where('file_path', '!=', '');

            if ($currentRole === 'guru') {
                if (Schema::hasColumn('academic_documents', 'owner_user_id')) {
                    $documentQuery->where('owner_user_id', $user->id);
                } elseif (Schema::hasColumn('academic_documents', 'uploaded_by')) {
                    $documentQuery->where('uploaded_by', $user->id);
                } elseif (Schema::hasColumn('academic_documents', 'created_by')) {
                    $documentQuery->where('created_by', $user->id);
                } else {
                    $documentQuery->whereRaw('1 = 0');
                }
            }

            $rppmDocuments = $documentQuery
                ->latest('id')
                ->get();

        } catch (\Throwable $e) {
            try {
                $documentQuery = \App\Models\AcademicDocument::query()
                    ->where('school_partner_id', $schoolId)
                    ->whereNotNull('file_path')
                    ->where('file_path', '!=', '')
                    ->where('file_path', 'like', '%rppm%');

                if ($currentRole === 'guru') {
                    if (Schema::hasColumn('academic_documents', 'owner_user_id')) {
                        $documentQuery->where('owner_user_id', $user->id);
                    } elseif (Schema::hasColumn('academic_documents', 'uploaded_by')) {
                        $documentQuery->where('uploaded_by', $user->id);
                    } elseif (Schema::hasColumn('academic_documents', 'created_by')) {
                        $documentQuery->where('created_by', $user->id);
                    } else {
                        $documentQuery->whereRaw('1 = 0');
                    }
                }

                $rppmDocuments = $documentQuery
                    ->latest('id')
                    ->get();

            } catch (\Throwable $fallbackError) {
                $rppmDocuments = collect();
            }
        }
    }

    /* =====================================================================
       HIERARKI FOLDER (GURU → TAHUN AJARAN → KELAS → TIPE KELAS → MAPEL)
       ===================================================================== */

    $folderHierarchy = [];

    $folderAssignments = $teacherMapels;

    if ($user && $currentRole === 'guru') {
        $folderAssignments = $folderAssignments
            ->filter(function ($assignment) use ($user) {
                $uid =$assignment->user_id ?? $assignment->teacher_id ?? $assignment->guru_id ?? null;
                return (int) ($uid ?? 0) === (int) $user->id;
            })
            ->values();
    }

    foreach ($folderAssignments as$assignment) {
        $schoolClass =$assignment->SchoolClass ?? null;
        $mapelModel =$assignment->Mapel ?? null;

        if (!$schoolClass || !$mapelModel) {
            continue;
        }

        // Penarikan Nama Guru Asli
        $teacherName = $resolveTeacherName($assignment);

        $yearName = trim((string) ($schoolClass->tahun_ajaran
            ?? $assignment->tahun_ajaran
            ?? ''
        ));

        $className = $extractClassName($schoolClass->kelas ?? null
        );

        if ($className === '') {$className = $extractClassName($schoolClass->nama_kelas
                ?? $schoolClass->class_name
                ?? $schoolClass->name
                ?? ''
            );
        }

        $typeName = trim((string) ($schoolClass->tipe_kelas
            ?? $schoolClass->jenis_kelas
            ?? $schoolClass->class_type
            ?? $schoolClass->tipe
            ?? 'Umum'
        ));

        $mapelName = trim((string) ($mapelModel->nama_mapel
            ?? $mapelModel->mata_pelajaran
            ?? $mapelModel->nama
            ?? $mapelModel->name
            ?? ''
        ));

        if (
            $yearName === ''
            || $className === ''
            || $mapelName === ''
        ) {
            continue;
        }

        // 1. Level Guru
        $teacherKey = 'teacher_' . md5($teacherName);
        if (!isset($folderHierarchy[$teacherKey])) {
            $folderHierarchy[$teacherKey] = [
                'id' => $teacherKey,
                'name' => $teacherName,
                'kind' => 'folder',
                'children' => [],
            ];
        }

        // 2. Level Tahun Ajaran
        $yearKey = 'year_' . md5($teacherKey . '|' .$yearName);
        if (!isset($folderHierarchy[$teacherKey]['children'][$yearKey])) {$folderHierarchy[$teacherKey]['children'][$yearKey] = [
                'id' => $yearKey,
                'name' => $yearName,
                'kind' => 'folder',
                'children' => [],
            ];
        }

        // 3. Level Kelas
        $classKey = 'class_' . md5($yearKey . '|' .$className);
        if (!isset($folderHierarchy[$teacherKey]['children'][$yearKey]['children'][$classKey])) {
            $folderHierarchy[$teacherKey]['children'][$yearKey]['children'][$classKey] = [
                'id' => $classKey,
                'name' => $className,
                'kind' => 'folder',
                'children' => [],
            ];
        }

        // 4. Level Tipe Kelas
        $typeKey = 'type_' . md5($classKey . '|' .$typeName);
        if (!isset($folderHierarchy[$teacherKey]['children'][$yearKey]['children'][$classKey]['children'][$typeKey])) {$folderHierarchy[$teacherKey]['children'][$yearKey]['children'][$classKey]['children'][$typeKey] = [
                'id' => $typeKey,
                'name' => $typeName,
                'kind' => 'folder',
                'children' => [],
            ];
        }

        // 5. Level Mapel
        $mapelId = $assignment->mapel_id ?? $mapelModel->id ?? null;
        $mapelKey = 'mapel_' . md5($typeKey . '|' . $mapelName . '|' . ($mapelId ?? ''));

        $folderHierarchy[$teacherKey]['children'][$yearKey]['children'][$classKey]['children'][$typeKey]['children'][$mapelKey] = [
            'id' => $mapelKey,
            'name' => $mapelName,
            'kind' => 'mapel',
            'mapel_id' => $mapelId,
            'children' => [],
        ];
    }

    /* =====================================================================
       FORMAT HIERARKI MENJADI ARRAY
       ===================================================================== */

    $folderHierarchy = collect($folderHierarchy)
        ->map(function ($teacher) {
            $teacher['children'] = collect($teacher['children'])
                ->map(function ($year) {
                    $year['children'] = collect($year['children'])
                        ->map(function ($class) {
                            $class['children'] = collect($class['children'])
                                ->map(function ($type) {
                                    $type['children'] = collect($type['children'])
                                        ->values()
                                        ->all();

                                    return $type;
                                })
                                ->values()
                                ->all();

                            return $class;
                        })
                        ->values()
                        ->all();

                    return $year;
                })
                ->values()
                ->all();

            return $teacher;
        })
        ->values()
        ->all();

    /* =====================================================================
       JUMLAH GURU & MAPEL
       ===================================================================== */

    $jumlahGuru = count($folderHierarchy);

    $jumlahMapel =$rppmDocuments
        ->map(function ($document) {
            if (!empty($document->subject_id)) {
                return (int) $document->subject_id;
            }
            if (!empty($document->mapel_id)) {
                return (int) $document->mapel_id;
            }
            return null;
        })
        ->filter()
        ->unique()
        ->count();

    /* =====================================================================
       URL HALAMAN RPPM
       ===================================================================== */

    $emptyTampUrl = route(
        'lms.schoolAdmin.registrasiGuru.rppm.tampilan',
        [
            'role' => $user->role ?? $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
        ]
    );
@endphp

{{-- ========================================================================
     AKSES
     ======================================================================== --}}

@if (
    $user &&
    in_array($currentRole, [
        'guru',
        'admin sekolah',
        'kepala sekolah',
        'wakil kepala sekolah',
    ], true)
)

<div class="relative z-20 w-full transition-all duration-500 ease-in-out md:ml-[280px] md:w-[calc(100%-280px)]">
    <div class="my-15 mx-7.5">
        <main>

            {{-- HEADER RPPM --}}

            <section class="mb-7 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">

                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A2 2 0 0114 3.586L19.414 9A2 2 0 0120 10.414V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-xl font-bold text-gray-800">
                                RPPM
                            </h1>
                            <p class="text-sm text-gray-500">
                                Rencana Pelaksanaan Pembelajaran Mingguan
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="min-w-[140px] rounded-xl bg-blue-50 px-6 py-4">
                            <p class="text-xs text-blue-500">Jumlah Guru</p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ $jumlahGuru }}
                            </p>
                            <p class="mt-1 text-[11px] text-blue-400">
                                Guru dengan file
                            </p>
                        </div>

                        <div class="min-w-[140px] rounded-xl bg-gray-50 px-6 py-4">
                            <p class="text-xs text-gray-500">Jumlah Mapel</p>
                            <p class="text-2xl font-bold text-gray-700">
                                {{ $jumlahMapel }}
                            </p>
                            <p class="mt-1 text-[11px] text-gray-400">
                                Mapel dengan file
                            </p>
                        </div>
                    </div>

                </div>
            </section>

            {{-- FOLDER DOKUMEN RPPM --}}

            <section class="mb-7 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">
                            Dokumen RPPM
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Guru → Tahun Ajaran → Kelas → Tipe Kelas → Mata Pelajaran → File.
                        </p>
                    </div>

                    <button
                        type="button"
                        onclick="kembaliFolder()"
                        id="btnKembaliFolder"
                        class="hidden rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        ← Kembali
                    </button>
                </div>

                <div id="breadcrumbFolder" class="mb-4 text-sm text-gray-500">
                    Guru
                </div>

                <div
                    id="folderDokumenGrid"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                ></div>

                <p
                    id="folderDokumenKosong"
                    class="hidden mt-4 rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500"
                >
                    Tidak ada data guru atau penugasan yang tersedia untuk akun ini.
                </p>

            </section>

        </main>
    </div>
</div>

<script>
(function () {
    'use strict';

    const emptyTampUrl = @json($emptyTampUrl);
    const data = @json($folderHierarchy);

    const grid = document.getElementById('folderDokumenGrid');
    const crumb = document.getElementById('breadcrumbFolder');
    const empty = document.getElementById('folderDokumenKosong');
    const back = document.getElementById('btnKembaliFolder');

    let path = [];

    function currentList() {
        let list = data;

        for (const id of path) {
            const node = list.find(item => item.id === id);

            if (!node) {
                return [];
            }

            list = node.children || [];
        }

        return list;
    }

    function currentNode() {
        let list = data;
        let node = null;

        for (const id of path) {
            node = list.find(item => item.id === id);

            if (!node) {
                return null;
            }

            list = node.children || [];
        }

        return node;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function addCard(
        title,
        subtitle,
        icon,
        action,
        color = 'blue'
    ) {
        const card = document.createElement('button');

        card.type = 'button';

        card.className =
            'min-h-[130px] rounded-2xl border border-gray-200 bg-white p-5 text-left transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md';

        const iconColor = color === 'indigo'
            ? 'bg-indigo-50 text-indigo-600'
            : 'bg-blue-50 text-blue-600';

        card.innerHTML = `
            <span class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl ${iconColor} text-xl">
                <i class="${escapeHtml(icon)}"></i>
            </span>

            <span class="block break-words text-sm font-bold text-gray-800">
                ${escapeHtml(title)}
            </span>

            <span class="mt-1 block text-xs text-gray-500">
                ${escapeHtml(subtitle || '')}
            </span>
        `;

        card.addEventListener('click', action);

        grid.appendChild(card);
    }

    function render() {
        grid.innerHTML = '';

        const list = currentList();
        const node = currentNode();

        const labels = [
            'Guru',
            'Tahun Ajaran',
            'Kelas',
            'Tipe Kelas',
            'Mata Pelajaran'
        ];

        const names = [];
        let walking = data;

        for (const id of path) {
            const item = walking.find(entry => entry.id === id);

            if (!item) {
                break;
            }

            names.push(item.name);
            walking = item.children || [];
        }

        crumb.textContent = names.length
            ? labels.slice(0, names.length).join(' / ')
                + ': ' + names.join(' / ')
            : 'Guru';

        back.classList.toggle('hidden', path.length === 0);

        list.forEach(item => {
            const isMapel = item.kind === 'mapel';

            addCard(
                item.name,
                isMapel
                    ? 'Buka folder dokumen RPPM'
                    : (item.children || []).length + ' pilihan',
                isMapel
                    ? 'fa-solid fa-book-open'
                    : 'fa-solid fa-folder-open',
                function () {
                    path.push(item.id);
                    render();
                },
                isMapel ? 'indigo' : 'blue'
            );
        });

        /*
         * Tombol Tambah File muncul ketika pengguna membuka folder mata pelajaran.
         */
        if (node && node.kind === 'mapel') {
            addCard(
                '+ Tambah File',
                'Buka halaman RPPM',
                'fa-solid fa-cloud-arrow-up',
                function () {
                    const url = new URL(
                        emptyTampUrl,
                        window.location.origin
                    );

                    if (node.mapel_id) {
                        url.searchParams.set(
                            'mapel_id',
                            node.mapel_id
                        );
                    }

                    window.location.href = url.toString();
                },
                'indigo'
            );
        }

        empty.classList.toggle(
            'hidden',
            list.length > 0 || (node && node.kind === 'mapel')
        );
    }

    window.kembaliFolder = function () {
        if (path.length > 0) {
            path.pop();
            render();
        }
    };

    render();
})();
</script>

@else

{{-- AKSES DITOLAK --}}

<div class="flex min-h-screen flex-col items-center justify-center">
    <div class="text-center">

        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.071 19h13.858c1.54 0 2.503-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.339 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <p class="text-lg font-semibold text-gray-800">
            AKSES DITOLAK
        </p>

        <p class="mt-2 text-sm text-gray-500">
            You do not have access to this page.
        </p>

    </div>
</div>

@endif