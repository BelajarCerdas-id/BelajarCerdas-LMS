{{-- ========================================================================
     BAGAN ANALISIS / ANALISIS CP HINGGA ATP

     File:
     resources/views/features/lms/school-admin/registrasi-guru/tampilan/tamp-analisis.blade.php

     FITUR:
     - Tampilan Excel
     - Worksheet selector
     - Guru dapat edit cell
     - Semua role dapat komentar
     - Revisi
     - Resolve revisi
     - Panel komentar dapat ditutup
     - Saat panel komentar ditutup, tabel utama melebar
     - Route komentar memakai /cells/{cell}/comments, bukan URL tampilan
     - DB komentar memakai academic_document_sheet_id + coordinate + comment
     - Cell ID dipakai untuk menemukan sheet/cell; bukan kolom komentar
     - Tidak menggunakan $comment->cell
     ======================================================================== --}}

@php

    use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

    /*
    |--------------------------------------------------------------------------
    | DATA DASAR
    |--------------------------------------------------------------------------
    */

    $mapel = $mapel ?? null;

    $document = $document ?? null;

    $comments = $comments ?? collect();

    /*
    |--------------------------------------------------------------------------
    | IDENTITAS PEMILIK KOMENTAR
    |--------------------------------------------------------------------------
    |
    | academic_document_comments.user_id berasal dari akun aplikasi.
    | Relasi legacy ke tabel users dapat tidak memiliki nama/jabatan,
    | sehingga nama dan jabatan diambil langsung dari user_accounts.
    |
    */
    $commentAuthorDirectory = collect();

    if (
        \Illuminate\Support\Facades\Schema::hasTable('user_accounts')
        && \Illuminate\Support\Facades\Schema::hasColumn('user_accounts', 'id')
    ) {
        $authorIds = collect($comments)
            ->flatMap(function ($comment) {
                return collect([
                    $comment->user_id ?? null,
                    ...collect($comment->replies ?? [])
                        ->pluck('user_id')
                        ->all(),
                ]);
            })
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($authorIds->isNotEmpty()) {
            $commentAuthorDirectory =
                \Illuminate\Support\Facades\DB::table('user_accounts')
                    ->whereIn('id', $authorIds->all())
                    ->get()
                    ->keyBy('id');
        }
    }

    $resolveCommentAuthorFromDirectory = function ($comment) use (
        $commentAuthorDirectory
    ) {
        $account =
            $commentAuthorDirectory->get(
                (int) ($comment->user_id ?? 0)
            );

        $name = null;
        $role = null;

        if ($account) {
            foreach (
                [
                    'name',
                    'nama',
                    'nama_lengkap',
                    'full_name',
                    'display_name',
                    'username',
                    'email',
                ] as $field
            ) {
                $value = $account->{$field} ?? null;

                if (
                    is_string($value)
                    && trim($value) !== ''
                ) {
                    $name = trim($value);
                    break;
                }
            }

            foreach (
                [
                    'role',
                    'jabatan',
                    'position',
                ] as $field
            ) {
                $value = $account->{$field} ?? null;

                if (
                    is_string($value)
                    && trim($value) !== ''
                ) {
                    $role = trim($value);
                    break;
                }
            }
        }

        $name =
            $name
            ?? $comment->user?->name
            ?? $comment->user?->nama
            ?? $comment->user?->username
            ?? $comment->user?->email
            ?? 'Pengguna';

        $role =
            $role
            ?? $comment->user?->role
            ?? $comment->user?->jabatan
            ?? $comment->role
            ?? '';

        return [
            'name' => $name,
            'role' => $role,
        ];
    };


    $role = $role
        ?? optional(Auth::user())->role
        ?? 'guru';

    /*
     * ROLE GURU HARUS TETAP TERDETEKSI SEBAGAI GURU.
     *
     * Jangan hanya bergantung pada $isGuru yang dikirim controller,
     * karena halaman dapat dibuka kembali dari URL/tab lama.
     */
    $isGuru = (bool) ($isGuru ?? false)
        || strtolower(trim((string) $role)) === 'guru';

    $schoolName = $schoolName ?? '';

    $schoolId = $schoolId ?? '';

    /*
    |--------------------------------------------------------------------------
    | ROUTE UTAMA BAGAN ANALISIS
    |--------------------------------------------------------------------------
    |
    | Halaman tampilan hanya menerima GET. Form upload wajib menuju
    | endpoint POST baganAnalisis.upload.
    |--------------------------------------------------------------------------
    */

    $uploadRoute = $uploadRoute ?? route(
        'lms.schoolAdmin.registrasiGuru.baganAnalisis.upload',
        [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
        ]
    );

    $downloadRoute = $downloadRoute ?? (
        $document
            ? route(
                'lms.schoolAdmin.registrasiGuru.baganAnalisis.download',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'document' => $document->id,
                ]
            )
            : '#'
    );

    $deleteRoute = $deleteRoute ?? (
        $document
            ? route(
                'lms.schoolAdmin.registrasiGuru.baganAnalisis.delete',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'document' => $document->id,
                ]
            )
            : '#'
    );

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC DRIVE - ANALISIS
    |--------------------------------------------------------------------------
    | Tambahan ini hanya untuk menyimpan dokumen dan membuka arsip dokumen.
    | Tidak mengubah alur komentar, revisi, atau autosave cell.
    */
    $archiveDocumentId = $document?->id;

    $saveArchiveUrl = $archiveDocumentId
        ? url('/lms/'
            . rawurlencode((string) $role)
            . '/'
            . rawurlencode((string) $schoolName)
            . '/'
            . rawurlencode((string) $schoolId)
            . '/registrasi-guru/academic-document/analisis/'
            . $archiveDocumentId
            . '/save-archive')
        : null;

    $browseDocumentsUrl = url('/lms/'
        . rawurlencode((string) $role)
        . '/'
        . rawurlencode((string) $schoolName)
        . '/'
        . rawurlencode((string) $schoolId)
        . '/registrasi-guru/academic-drive/browse/analisis');

    $sharedDocumentBaseUrl = url('/lms/'
        . rawurlencode((string) $role)
        . '/'
        . rawurlencode((string) $schoolName)
        . '/'
        . rawurlencode((string) $schoolId)
        . '/registrasi-guru/academic-document/analisis');



    /*
    |--------------------------------------------------------------------------
    | ROLE LABEL
    |--------------------------------------------------------------------------
    */

    $roleLabel = $isGuru
        ? 'Guru'
        : ucfirst((string) $role);


    /*
    |--------------------------------------------------------------------------
    | MAPEL NAME
    |--------------------------------------------------------------------------
    */

    $mapelName =
        $mapel?->mata_pelajaran
        ?? $mapel?->nama_mapel
        ?? $mapel?->nama
        ?? $mapel?->name
        ?? 'Mata Pelajaran';


    /*
    |--------------------------------------------------------------------------
    | ROUTE TEMPLATE
    |--------------------------------------------------------------------------
    */

    // Endpoint dibuat langsung agar form KOMENTAR tidak pernah jatuh ke
    // URL halaman /bagan-analisis/tampilan walaupun route cache lama masih ada.
    $commentUrlTemplate = url(
        '/lms/'
        . rawurlencode((string) $role)
        . '/'
        . rawurlencode((string) $schoolName)
        . '/'
        . rawurlencode((string) $schoolId)
        . '/registrasi-guru/bagan-analisis/cells/__CELL__/comments'
    );

    $resolveCommentUrlTemplate = route(
        'lms.schoolAdmin.registrasiGuru.baganAnalisis.comments.resolve',
        [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
            'comment' => '__COMMENT__',
        ]
    );




    $updateCellUrlTemplate = route(
        'lms.schoolAdmin.registrasiGuru.baganAnalisis.cells.update',
        [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
            'cell' => '__CELL__',
        ]
    );

    $autosaveCellUrlTemplate = route(
        'lms.schoolAdmin.registrasiGuru.baganAnalisis.cells.autosave',
        [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
            'cell' => '__CELL__',
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | GROUP COMMENT BY SHEET
    |--------------------------------------------------------------------------
    */

    $commentsBySheet = collect($comments)->groupBy(
        function ($comment) {
            return (int) (
                $comment->academic_document_sheet_id
                ?? 0
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | GROUP COMMENT BY CELL
    |--------------------------------------------------------------------------
    */

    $commentsByCell = collect($comments)->groupBy(
        function ($comment) {
            return (int) ($comment->academic_document_sheet_id ?? 0)
                . ':'
                . strtoupper(trim((string) ($comment->coordinate ?? '')));
        }
    );

@endphp


{{-- ========================================================================
     SIDEBAR
     ======================================================================== --}}

@include('components.sidebar-beranda', [
    'linkBackButton' => url()->previous(),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Analisis CP hingga ATP',
    'schoolName' => $schoolName,
    'schoolId' => $schoolId,
])


{{-- ========================================================================
     MAIN
     ======================================================================== --}}

<div class="min-h-screen bg-slate-50">

    <div class="ml-[280px] min-h-screen w-[calc(100%-280px)] min-w-0">

        <main class="w-full min-w-0 px-4 py-5 sm:px-6 lg:px-8 xl:px-10">


            {{-- =================================================================
                 SUCCESS
                 ================================================================= --}}

            @if(session('success'))

                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 shadow-sm">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-bold text-emerald-700">
                            Berhasil
                        </p>

                        <p class="mt-1 break-words text-sm text-emerald-600">
                            {{ session('success') }}
                        </p>

                    </div>

                </div>

            @endif


            {{-- =================================================================
                 ERROR
                 ================================================================= --}}

            @if(session('error'))

                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 shadow-sm">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <i class="fa-solid fa-exclamation"></i>
                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-bold text-red-700">
                            Terjadi Kesalahan
                        </p>

                        <p class="mt-1 break-words text-sm text-red-600">
                            {{ session('error') }}
                        </p>

                    </div>

                </div>

            @endif


            {{-- =================================================================
                 VALIDATION
                 ================================================================= --}}

            @if($errors->any())

                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm">

                    <div class="flex items-center gap-2">

                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>

                        <p class="text-sm font-bold text-red-700">
                            Periksa kembali data yang dimasukkan
                        </p>

                    </div>

                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-600">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- =================================================================
                 PAGE HEADER
                 ================================================================= --}}

            <section class="mb-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="relative overflow-hidden px-5 py-6 sm:px-7 lg:px-8">

                    <div class="pointer-events-none absolute -right-16 -top-20 h-52 w-52 rounded-full bg-blue-50"></div>

                    <div class="pointer-events-none absolute -bottom-24 right-24 h-40 w-40 rounded-full bg-indigo-50"></div>

                    <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">

                        <div class="flex min-w-0 items-center gap-4">

                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-100">

                                <i class="fa-solid fa-diagram-project text-2xl"></i>

                            </div>

                            <div class="min-w-0">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h1 class="text-xl font-extrabold tracking-tight text-slate-800 sm:text-2xl">
                                        Analisis CP hingga ATP
                                    </h1>

                                    @if($document)

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">

                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                            Dokumen tersedia

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">

                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>

                                            Belum ada dokumen

                                        </span>

                                    @endif

                                </div>

                                <p class="mt-1 text-sm text-slate-500">
                                    Bagan Analisis Capaian Pembelajaran hingga Alur Tujuan Pembelajaran
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2">

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">

                                        <i class="fa-solid fa-user text-[10px]"></i>

                                        {{ $roleLabel }}

                                    </span>

                                    @if($mapel)

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">

                                            <i class="fa-solid fa-book-open text-blue-500"></i>

                                            {{ $mapelName }}

                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        @if($isGuru)

                            <div class="w-full rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 xl:max-w-[390px]">

                                <div class="flex items-start gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm">

                                        <i class="fa-solid fa-user-gear"></i>

                                    </div>

                                    <div>

                                        <p class="text-xs font-bold uppercase tracking-wide text-indigo-500">
                                            Akses Guru
                                        </p>

                                        <p class="mt-1 text-sm font-extrabold text-indigo-700">
                                            Upload dan Kelola Dokumen
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-indigo-500">
                                            Klik satu kali untuk memilih cell.
                                            Klik dua kali untuk mengedit isi cell.
                                        </p>

                                    </div>

                                </div>


                            </div>

                        @endif

                    </div>

                </div>

            </section>


            {{-- =================================================================
                 GURU UPLOAD
                 ================================================================= --}}

            @if($isGuru && !$document)

                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                    <div class="px-5 py-10 sm:px-8 lg:px-12">

                        <div class="mx-auto max-w-3xl">

                            <div class="text-center">

                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-indigo-50 text-indigo-600">

                                    <i class="fa-solid fa-cloud-arrow-up text-4xl"></i>

                                </div>

                                <h2 class="mt-6 text-xl font-extrabold text-slate-800 sm:text-2xl">
                                    Upload Bagan Analisis
                                </h2>

                                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                                    Upload file Excel Bagan Analisis / Analisis CP hingga ATP
                                    untuk mulai menampilkan isi dokumen.
                                </p>

                            </div>

                            <form
                                action="{{ $uploadRoute }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="mt-8 space-y-6"
                            >

                                @csrf

                                <div>

                                    <label
                                        for="title"
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Judul Dokumen
                                    </label>

                                    <input
                                        id="title"
                                        type="text"
                                        name="title"
                                        value="{{ old('title', 'Analisis CP hingga ATP') }}"
                                        required
                                        maxlength="255"
                                        class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                    >

                                </div>


                                <input
                                    type="hidden"
                                    name="document_type"
                                    value="bagan_analisis"
                                >


                                <div>

                                    <label
                                        for="excelFile"
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        File Excel
                                    </label>

                                    <label
                                        for="excelFile"
                                        class="group flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center transition hover:border-indigo-400 hover:bg-indigo-50"
                                    >

                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">

                                            <i class="fa-solid fa-file-excel text-3xl"></i>

                                        </div>

                                        <span class="mt-5 text-sm font-extrabold text-slate-700">
                                            Pilih File Excel
                                        </span>

                                        <span class="mt-1 text-xs text-slate-400">
                                            XLS atau XLSX, maksimal 20 MB
                                        </span>

                                        <input
                                            id="excelFile"
                                            type="file"
                                            name="file"
                                            accept=".xlsx,.xls"
                                            required
                                            class="hidden"
                                        >

                                    </label>


                                    <div
                                        id="selectedFileBox"
                                        class="mt-3 hidden items-center gap-3 rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3"
                                    >

                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm">

                                            <i class="fa-solid fa-file-excel"></i>

                                        </div>

                                        <div class="min-w-0">

                                            <p class="text-xs font-semibold text-indigo-500">
                                                File dipilih
                                            </p>

                                            <p
                                                id="selectedFileNameValue"
                                                class="mt-0.5 break-all text-sm font-bold text-indigo-700"
                                            ></p>

                                        </div>

                                    </div>

                                </div>


                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm leading-6 text-blue-800">

                                    <div class="flex items-start gap-3">

                                        <i class="fa-solid fa-circle-info mt-1"></i>

                                        <p>
                                            File Excel akan dibaca berdasarkan worksheet
                                            dan ditampilkan sebagai tabel Excel.
                                            Guru dapat melakukan double-click pada cell
                                            untuk mengedit isi cell.
                                        </p>

                                    </div>

                                </div>


                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-indigo-100 transition hover:bg-indigo-700"
                                >

                                    <i class="fa-solid fa-cloud-arrow-up"></i>

                                    Upload Bagan Analisis

                                </button>

                            </form>

                        </div>

                    </div>

                </section>


            @elseif($document)


                {{-- =================================================================
                     DOCUMENT HEADER
                     ================================================================= --}}

                <section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex min-w-0 items-center gap-3">

                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">

                                <i class="fa-solid fa-file-excel text-xl"></i>

                            </div>

                            <div class="min-w-0">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h2 class="truncate text-lg font-extrabold text-slate-800">
                                        {{ $document->title ?? 'Dokumen Analisis CP hingga ATP' }}
                                    </h2>

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">

                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        Aktif

                                    </span>

                                </div>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ $document->original_filename ?? '-' }}
                                </p>

                            </div>

                        </div>


                        <div class="flex flex-wrap gap-2">

                            @if($downloadRoute !== '#')

                                <a
                                    href="{{ $downloadRoute }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-200"
                                >

                                    <i class="fa-solid fa-download"></i>

                                    Download

                                </a>

                            @endif


                            @if($isGuru)

                                <button
                                    type="button"
                                    id="btnSaveToAcademicDrive"
                                    data-save-url="{{ $saveArchiveUrl }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700"
                                >
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span id="btnSaveToAcademicDriveText">Simpan ke Drive</span>
                                </button>


                                <button
                                    type="button"
                                    id="openReuploadModal"
                                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-indigo-700"
                                >
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    Upload Ulang
                                </button>

                                @if($deleteRoute !== '#')
                                    <form
                                        action="{{ $deleteRoute }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            onclick="return confirm('Yakin ingin menghapus dokumen Analisis CP hingga ATP ini? Setelah dihapus, halaman akan kembali ke form upload.')"
                                            class="inline-flex items-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                @endif

                            @endif

                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-3 bg-slate-50 px-5 py-4 text-xs sm:grid-cols-3 sm:px-6">

                        <div>

                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Mata Pelajaran
                            </p>

                            <p class="mt-1 font-bold text-slate-700">
                                {{ $mapelName }}
                            </p>

                        </div>


                        <div>

                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Diunggah
                            </p>

                            <p class="mt-1 font-bold text-slate-700">
                                {{ $document->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Pengunggah
                            </p>

                            <p class="mt-1 font-bold text-slate-700">
                                {{ $document->uploader?->name ?? '-' }}
                            </p>

                        </div>

                    </div>

                </section>


                {{-- =================================================================
                     SHEET SELECTOR
                     ================================================================= --}}

                @if($document->sheets && $document->sheets->count())

                    <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                            <div class="flex items-center gap-3">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">

                                    <i class="fa-solid fa-table"></i>

                                </div>

                                <div>

                                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                        Worksheet
                                    </p>

                                    <p class="text-sm font-extrabold text-slate-700">
                                        Pilih Sheet Excel
                                    </p>

                                </div>

                            </div>


                            <div class="flex w-full flex-wrap items-center justify-end gap-2 lg:w-auto">

                                <select
                                    id="sheetSelector"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-xs font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 sm:w-[220px] lg:w-[250px]"
                                >

                                    @foreach($document->sheets as $sheet)

                                        <option
                                            value="sheet-{{ $sheet->id }}"
                                            @selected($loop->first)
                                        >
                                            {{ $sheet->name ?? 'Sheet '.$loop->iteration }}
                                        </option>

                                    @endforeach

                                </select>

                                @if($isGuru)
                                    <select
                                        id="academicTeacherDocumentsDropdown"
                                        class="w-full rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-xs font-bold text-violet-700 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100 sm:w-[220px] lg:w-[250px]"
                                    >
                                        <option value="">Dokumen Guru</option>
                                    </select>

                                    <select
                                        id="academicArchiveDocumentsDropdown"
                                        class="w-full rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 sm:w-[220px] lg:w-[250px]"
                                    >
                                        <option value="">Arsip Tersimpan</option>
                                    </select>
                                @endif

                            </div>

                        </div>

                    </section>


                    {{-- =================================================================
                         SHEETS
                         ================================================================= --}}

                    @foreach($document->sheets as $sheet)

                        @php

                            $cells = collect($sheet->cells ?? []);

                            $cellMap = $cells->keyBy(
                                function ($cell) {
                                    return strtoupper(
                                        trim(
                                            (string) (
                                                $cell->coordinate ?? ''
                                            )
                                        )
                                    );
                                }
                            );

                            $actualMaxRow = (int) (
                                $cells->max('row_number')
                                ?? 0
                            );

                            $actualMaxColumn = (int) (
                                $cells->max('column_number')
                                ?? 0
                            );

                            if ($actualMaxRow <= 0) {

                                $actualMaxRow = (int) (
                                    $sheet->max_row
                                    ?? 0
                                );

                            }

                            if ($actualMaxColumn <= 0) {

                                $actualMaxColumn = (int) (
                                    $sheet->max_column
                                    ?? 0
                                );

                            }

                            $renderMaxRow = min(
                                max($actualMaxRow, 0),
                                500
                            );

                            $renderMaxColumn = min(
                                max($actualMaxColumn, 0),
                                100
                            );

                            $sheetComments =
                                $commentsBySheet->get(
                                    (int) $sheet->id,
                                    collect()
                                );

                            $sheetCommentsByCell =
                                $sheetComments->groupBy(
                                    function ($comment) {
                                        return strtoupper(
                                            trim((string) ($comment->coordinate ?? ''))
                                        );
                                    }
                                );

                        @endphp


                        <section
                            id="sheet-{{ $sheet->id }}"
                            class="{{ $loop->first ? '' : 'hidden' }}"
                        >


                            {{-- ======================================================
                                 SHEET HEADER
                                 ====================================================== --}}

                            <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">

                                        <i class="fa-solid fa-table-list"></i>

                                    </div>

                                    <div>

                                        <h2 class="text-sm font-extrabold text-slate-800">
                                            {{ $sheet->name ?? 'Sheet '.$loop->iteration }}
                                        </h2>

                                        <p class="mt-0.5 text-[10px] text-slate-400">

                                            {{ number_format($actualMaxRow) }}
                                            baris
                                            ×
                                            {{ number_format($actualMaxColumn) }}
                                            kolom

                                        </p>

                                    </div>

                                </div>


                                <div class="flex flex-wrap gap-2">

                                    @if($isGuru)

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1.5 text-[10px] font-bold text-indigo-700">

                                            <i class="fa-solid fa-hand-pointer text-[9px]"></i>

                                            Double-click untuk edit

                                        </span>

                                    @endif


                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-semibold text-slate-500">

                                        <i class="fa-regular fa-comment"></i>

                                        Klik cell untuk komentar

                                    </span>


                                    <button
                                        type="button"
                                        class="toggle-comment-panel inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                        data-sheet-id="{{ $sheet->id }}"
                                        data-comment-panel-target="comment-panel-{{ $sheet->id }}"
                                        data-workspace-target="workspace-{{ $sheet->id }}"
                                    >

                                        <span class="toggle-comment-icon">
                                            ◀
                                        </span>

                                        <span class="toggle-comment-text">
                                            Sembunyikan Komentar
                                        </span>

                                    </button>

                                </div>

                            </div>


                            {{-- ======================================================
                                 LARGE SHEET WARNING
                                 ====================================================== --}}

                            @if(
                                $actualMaxRow > $renderMaxRow ||
                                $actualMaxColumn > $renderMaxColumn
                            )

                                <div class="mb-3 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">

                                    <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-500"></i>

                                    <div>

                                        <p class="text-[11px] font-bold text-amber-700">
                                            Ukuran worksheet sangat besar
                                        </p>

                                        <p class="mt-1 text-[10px] leading-5 text-amber-600">

                                            Worksheet memiliki
                                            {{ number_format($actualMaxRow) }}
                                            baris dan
                                            {{ number_format($actualMaxColumn) }}
                                            kolom.

                                            Sistem menampilkan maksimal
                                            {{ number_format($renderMaxRow) }}
                                            baris dan
                                            {{ number_format($renderMaxColumn) }}
                                            kolom.

                                        </p>

                                    </div>

                                </div>

                            @endif


                            {{-- ======================================================
                                 WORKSPACE
                                 ====================================================== --}}

                            <div
                                id="workspace-{{ $sheet->id }}"
                                class="sheet-workspace grid min-h-0 min-w-0 grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]"
                                data-comment-open="1"
                            >


                                {{-- ==================================================
                                     EXCEL TABLE
                                     ================================================== --}}

                                <div class="excel-workspace-table min-w-0 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">

                                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">

                                        <div class="flex items-center gap-2">

                                            <i class="fa-solid fa-table text-xs text-indigo-500"></i>

                                            <span class="text-[11px] font-extrabold text-slate-700">
                                                Isi Worksheet
                                            </span>

                                        </div>

                                        <span class="text-[9px] text-slate-400">
                                            Excel View
                                        </span>

                                    </div>


                                    <div
                                        id="tableWrapper-{{ $sheet->id }}"
                                        class="relative max-h-[680px] overflow-auto"
                                    >

                                        @if(
                                            $renderMaxRow > 0 &&
                                            $renderMaxColumn > 0
                                        )

                                            <table
                                                class="excel-table w-max min-w-full border-separate border-spacing-0 table-auto bg-white text-[11px] text-slate-800"
                                                data-sheet-id="{{ $sheet->id }}"
                                            >

                                                <thead>

                                                    <tr>

                                                        <th
                                                            class="sticky left-0 top-0 z-50 h-[34px] w-12 min-w-12 border-b border-r border-slate-300 bg-slate-100 p-0 text-center text-[10px] font-bold text-slate-500"
                                                        >
                                                            #
                                                        </th>


                                                        @for(
                                                            $column = 1;
                                                            $column <= $renderMaxColumn;
                                                            $column++
                                                        )

                                                            @php

                                                                $columnLetter =
                                                                    Coordinate::stringFromColumnIndex(
                                                                        $column
                                                                    );

                                                            @endphp

                                                            <th
                                                                class="sticky top-0 z-30 h-[34px] min-w-[150px] border-b border-r border-slate-300 bg-slate-100 px-3 py-2 text-center text-[10px] font-bold text-slate-600"
                                                            >

                                                                {{ $columnLetter }}

                                                            </th>

                                                        @endfor

                                                    </tr>

                                                </thead>


                                                <tbody>

                                                    @for(
                                                        $row = 1;
                                                        $row <= $renderMaxRow;
                                                        $row++
                                                    )

                                                        <tr>

                                                            <th
                                                                class="sticky left-0 z-20 h-9 w-12 min-w-12 border-b border-r border-slate-300 bg-slate-100 px-2 py-2 text-center text-[10px] font-semibold text-slate-500"
                                                            >

                                                                {{ $row }}

                                                            </th>


                                                            @for(
                                                                $column = 1;
                                                                $column <= $renderMaxColumn;
                                                                $column++
                                                            )

                                                                @php

                                                                    $coordinate =
                                                                        strtoupper(
                                                                            Coordinate::stringFromColumnIndex(
                                                                                $column
                                                                            )
                                                                        )
                                                                        . $row;


                                                                    $cell =
                                                                        $cellMap->get(
                                                                            $coordinate
                                                                        );


                                                                    $cellId =
                                                                        $cell?->id
                                                                        ?? null;


                                                                    $cellComments =
                                                                        $sheetCommentsByCell->get(
                                                                            strtoupper($coordinate),
                                                                            collect()
                                                                        );


                                                                    $hasComments =
                                                                        $cellComments->count() > 0;


                                                                    $hasRevision =
                                                                        ($cell ? (bool) ($cell->has_revision ?? false) : false)
                                                                        || $cellComments->contains(
                                                                            function ($comment) {
                                                                                return (bool) ($comment->is_revision ?? false)
                                                                                    && !((bool) ($comment->is_resolved ?? false));
                                                                            }
                                                                        );


                                                                    $displayValue = '';


                                                                    if ($cell) {

                                                                        $displayValue =
                                                                            $cell->value
                                                                            ?? '';


                                                                        if (
                                                                            $displayValue === ''
                                                                            &&
                                                                            $cell->calculated_value !== null
                                                                            &&
                                                                            $cell->calculated_value !== ''
                                                                        ) {

                                                                            $displayValue =
                                                                                $cell->calculated_value;

                                                                        }

                                                                    }


                                                                    $formula =
                                                                        $cell?->formula
                                                                        ?? null;


                                                                    $dataType =
                                                                        $cell?->data_type
                                                                        ?? null;


                                                                    $cellStyle = [];


                                                                    if ($cell) {

                                                                        $rawStyle =
                                                                            $cell->style
                                                                            ?? null;


                                                                        if (
                                                                            is_array(
                                                                                $rawStyle
                                                                            )
                                                                        ) {

                                                                            $cellStyle =
                                                                                $rawStyle;

                                                                        } elseif (
                                                                            is_string(
                                                                                $rawStyle
                                                                            )
                                                                            &&
                                                                            $rawStyle !== ''
                                                                        ) {

                                                                            $decodedStyle =
                                                                                json_decode(
                                                                                    $rawStyle,
                                                                                    true
                                                                                );


                                                                            if (
                                                                                is_array(
                                                                                    $decodedStyle
                                                                                )
                                                                            ) {

                                                                                $cellStyle =
                                                                                    $decodedStyle;

                                                                            }

                                                                        }

                                                                    }


                                                                    $inlineStyle = '';


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['background_color']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'background-color:' .
                                                                            e(
                                                                                $cellStyle['background_color']
                                                                            ) .
                                                                            ';';

                                                                    }


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['font_color']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'color:' .
                                                                            e(
                                                                                $cellStyle['font_color']
                                                                            ) .
                                                                            ';';

                                                                    }


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['font_size']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'font-size:' .
                                                                            e(
                                                                                $cellStyle['font_size']
                                                                            ) .
                                                                            'px;';

                                                                    }


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['font_weight']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'font-weight:' .
                                                                            e(
                                                                                $cellStyle['font_weight']
                                                                            ) .
                                                                            ';';

                                                                    }


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['text_align']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'text-align:' .
                                                                            e(
                                                                                $cellStyle['text_align']
                                                                            ) .
                                                                            ';';

                                                                    }


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['vertical_align']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'vertical-align:' .
                                                                            e(
                                                                                $cellStyle['vertical_align']
                                                                            ) .
                                                                            ';';

                                                                    }


                                                                    if (
                                                                        !empty(
                                                                            $cellStyle['white_space']
                                                                        )
                                                                    ) {

                                                                        $inlineStyle .=
                                                                            'white-space:' .
                                                                            e(
                                                                                $cellStyle['white_space']
                                                                            ) .
                                                                            ';';

                                                                    }


                                                                    $dataTypeLabel =
                                                                        match (
                                                                            strtolower(
                                                                                (string) $dataType
                                                                            )
                                                                        ) {

                                                                            'f',
                                                                            'formula'
                                                                                => 'Formula',

                                                                            'n',
                                                                            'numeric',
                                                                            'number'
                                                                                => 'Angka',

                                                                            'b',
                                                                            'boolean'
                                                                                => 'Boolean',

                                                                            'd',
                                                                            'date'
                                                                                => 'Tanggal',

                                                                            'e',
                                                                            'error'
                                                                                => 'Error',

                                                                            default
                                                                                => null,

                                                                        };

                                                                @endphp


                                                                <td
                                                                    data-cell-id="{{ $cellId ?? '' }}"
                                                                    data-coordinate="{{ $coordinate }}"
                                                                    data-row="{{ $row }}"
                                                                    data-column="{{ $column }}"
                                                                    data-has-comments="{{ $hasComments ? '1' : '0' }}"
                                                                    data-has-revision="{{ $hasRevision ? '1' : '0' }}"
                                                                    data-formula="{{ $formula ? e($formula) : '' }}"
                                                                    class="excel-cell relative min-h-[52px] min-w-[150px] max-w-[500px] cursor-cell border-b border-r border-slate-300 bg-white p-0 align-top transition hover:bg-blue-50 {{ $hasRevision ? 'has-revision-border' : '' }}"
                                                                    @if($inlineStyle)
                                                                        style="{{ $inlineStyle }}"
                                                                    @endif
                                                                >

                                                                    @if($cell)

                                                                        <div class="cell-content min-h-[52px] px-3 py-2 leading-6">

                                                                            @if(
                                                                                $hasComments ||
                                                                                $hasRevision ||
                                                                                $formula ||
                                                                                $dataTypeLabel
                                                                            )

                                                                                <div class="mb-1 flex items-center justify-between gap-2">

                                                                                    <div class="flex min-w-0 items-center gap-1">

                                                                                        @if($formula)

                                                                                            <span
                                                                                                title="Cell memiliki formula"
                                                                                                class="inline-flex h-4 w-4 items-center justify-center rounded bg-purple-50 text-[8px] text-purple-600"
                                                                                            >

                                                                                                <i class="fa-solid fa-function"></i>

                                                                                            </span>

                                                                                        @endif


                                                                                        @if($dataTypeLabel)

                                                                                            <span class="text-[7px] font-semibold text-slate-400">
                                                                                                {{ $dataTypeLabel }}
                                                                                            </span>

                                                                                        @endif

                                                                                    </div>


                                                                                    <div class="flex shrink-0 items-center gap-1">

                                                                                        @if($hasComments)

                                                                                            <span
                                                                                                title="{{ $cellComments->count() }} komentar"
                                                                                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-1.5 py-0.5 text-[8px] font-bold text-blue-600"
                                                                                            >

                                                                                                <i class="fa-regular fa-comment"></i>

                                                                                                {{ $cellComments->count() }}

                                                                                            </span>

                                                                                        @endif


                                                                                        @if($hasRevision)

                                                                                            <span
                                                                                                title="Cell memiliki revisi"
                                                                                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[8px] font-extrabold text-amber-700"
                                                                                            >

                                                                                                <i class="fa-solid fa-rotate"></i>

                                                                                            </span>

                                                                                        @endif

                                                                                    </div>

                                                                                </div>

                                                                            @endif


                                                                            <div class="cell-value whitespace-pre-wrap break-words [overflow-wrap:anywhere] text-slate-700">
                                                                                {{ $displayValue }}
                                                                            </div>


                                                                            @if($formula)

                                                                                <div class="formula-preview mt-1 hidden rounded-md bg-purple-50 px-2 py-1 text-[8px] leading-4 text-purple-600">
                                                                                    {{ $formula }}
                                                                                </div>

                                                                            @endif


                                                                            @if($isGuru)

                                                                                <div class="cell-editor hidden"></div>

                                                                            @endif

                                                                        </div>

                                                                    @else

                                                                        <div class="min-h-[52px]"></div>

                                                                    @endif

                                                                </td>

                                                            @endfor

                                                        </tr>

                                                    @endfor

                                                </tbody>

                                            </table>

                                        @else

                                            <div class="flex min-h-[350px] items-center justify-center">

                                                <div class="px-6 text-center">

                                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                                                        <i class="fa-solid fa-table text-xl"></i>

                                                    </div>

                                                    <p class="mt-4 text-sm font-bold text-slate-600">
                                                        Worksheet kosong
                                                    </p>

                                                    <p class="mt-1 text-xs text-slate-400">
                                                        Tidak ada cell yang tersimpan dari worksheet ini.
                                                    </p>

                                                </div>

                                            </div>

                                        @endif

                                    </div>

                                </div>


                                {{-- ==================================================
                                     COMMENT PANEL
                                     ================================================== --}}

                                <aside
                                    id="comment-panel-{{ $sheet->id }}"
                                    class="comment-panel flex min-h-0 min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm xl:sticky xl:top-5 xl:max-h-[680px]"
                                >

                                    {{-- COMMENT HEADER --}}

                                    <div class="shrink-0 border-b border-slate-200 bg-white px-4 py-4">

                                        <div class="flex items-start gap-3">

                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">

                                                <i class="fa-solid fa-comments"></i>

                                            </div>


                                            <div>

                                                <h3 class="text-sm font-extrabold text-slate-800">
                                                    Komentar & Revisi
                                                </h3>

                                                <p class="mt-1 text-[10px] leading-5 text-slate-400">
                                                    Klik cell pada tabel untuk memilih
                                                    cell yang ingin diberi komentar.
                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- COMMENT BODY --}}

                                    <div class="min-h-0 flex-1 overflow-y-auto p-3">


                                        {{-- SELECTED CELL --}}

                                        <div class="mb-3 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-3">

                                            <p class="text-[9px] font-bold uppercase tracking-wide text-indigo-400">
                                                Cell dipilih
                                            </p>

                                            <div class="mt-1 flex items-center justify-between gap-2">

                                                <p class="selected-cell-label text-sm font-extrabold text-indigo-700">
                                                    Belum memilih cell
                                                </p>

                                                <span class="selected-cell-badge hidden items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[9px] font-bold text-indigo-600 shadow-sm">

                                                    <i class="fa-solid fa-location-dot"></i>

                                                    Dipilih

                                                </span>

                                            </div>

                                        </div>


                                        {{-- ==================================================
                                             COMMENT FORM
                                             ================================================== --}}

                                        <form
                                            id="comment-form-{{ $sheet->id }}"
                                            method="POST"
                                            action="{{ $commentUrlTemplate }}"
                                            data-comment-url-template="{{ $commentUrlTemplate }}"
                                            class="comment-form"
                                            data-sheet-id="{{ $sheet->id }}"
                                        >

                                            @csrf


                                            {{-- CELL ID --}}

                                            <input
                                                type="hidden"
                                                name="academic_document_cell_id"
                                                class="comment-cell-id"
                                                value=""
                                            >

                                            <input
                                                type="hidden"
                                                name="academic_document_sheet_id"
                                                class="comment-sheet-id"
                                                value="{{ $sheet->id }}"
                                            >

                                            <input
                                                type="hidden"
                                                name="academic_document_id"
                                                class="comment-document-id"
                                                value="{{ $document->id }}"
                                            >


                                            {{-- COORDINATE --}}

                                            <input
                                                type="hidden"
                                                name="coordinate"
                                                class="comment-cell-coordinate"
                                                value=""
                                            >


                                            {{-- SELECTED TEXT --}}

                                            <input
                                                type="hidden"
                                                name="selected_text"
                                                class="comment-selected-text"
                                                value=""
                                            >


                                            {{-- ANCHOR KEY --}}

                                            <input
                                                type="hidden"
                                                name="anchor_key"
                                                class="comment-anchor-key"
                                                value=""
                                            >


                                            <div class="rounded-xl border border-slate-200 bg-white p-3">

                                                <p class="text-[10px] font-extrabold text-slate-700">
                                                    Berikan Komentar
                                                </p>

                                                <p class="mt-0.5 text-[9px] text-slate-400">
                                                    Tulis komentar untuk cell yang dipilih atau komentar umum.
                                                </p>


                                                {{-- COMMENT TEXT --}}

                                                <textarea
                                                    name="comment_text"
                                                    class="comment-input comment-textarea mt-3 block min-h-[100px] w-full resize-y rounded-lg border border-slate-300 bg-white p-2.5 text-[10px] leading-5 text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                                    rows="5"
                                                    minlength="1"
                                                    maxlength="5000"
                                                    required
                                                    placeholder="Tuliskan komentar atau masukan..."
                                                ></textarea>


                                                {{-- REVISION --}}

                                                <label class="mt-3 flex cursor-pointer items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-[10px] font-semibold text-slate-600">

                                                    <input
                                                        type="checkbox"
                                                        name="is_revision"
                                                        value="1"
                                                        class="h-3.5 w-3.5 rounded border-slate-300 text-red-600 focus:ring-red-500"
                                                    >

                                                    <span>
                                                        Tandai sebagai revisi
                                                    </span>

                                                </label>


                                                {{-- SUBMIT --}}

                                                <button
                                                    type="submit"
                                                    class="comment-submit-button mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2.5 text-[10px] font-extrabold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                >

                                                    <i class="fa-solid fa-paper-plane"></i>

                                                    Berikan Komentar

                                                </button>

                                            </div>

                                        </form>


                                        {{-- ==================================================
                                             COMMENT HISTORY
                                             ================================================== --}}

                                        <div class="mt-4">

                                            <div class="mb-3 flex items-center justify-between">

                                                <h4 class="text-[11px] font-extrabold text-slate-700">
                                                    Riwayat Komentar
                                                </h4>

                                                <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-bold text-slate-400">

                                                    {{ $sheetComments->count() }}

                                                    komentar

                                                </span>

                                            </div>


                                            <div class="space-y-2.5">

                                                @forelse($sheetComments as $comment)

                                                    @php

                                                        $commentCell = null;

                                                        $commentCoordinate = strtoupper(
                                                            trim((string) ($comment->coordinate ?? ''))
                                                        );

                                                        $commentCell =
                                                            $commentCoordinate !== ''
                                                                ? $cellMap->get($commentCoordinate)
                                                                : null;

                                                        $commentCellId = (int) ($commentCell?->id ?? 0);

                                                    @endphp


                                                    <div
                                                        class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm {{ ($comment->is_revision ?? false) ? (($comment->is_resolved ?? false) ? 'comment-card-resolved' : 'comment-card-revision') : '' }}"
                                                        data-comment-cell-id="{{ $commentCellId }}"
                                                        data-is-revision="{{ ($comment->is_revision ?? false) ? '1' : '0' }}"
                                                        data-is-resolved="{{ ($comment->is_resolved ?? false) ? '1' : '0' }}"
                                                        data-coordinate="{{ $commentCoordinate }}"
                                                    >


                                                        {{-- COMMENT HEADER --}}

                                                        <div class="flex items-start justify-between gap-2">

                                                            <div class="flex min-w-0 items-start gap-2">

                                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">

                                                                    <i class="fa-solid fa-user text-[10px]"></i>

                                                                </div>


                                                                <div class="min-w-0">

                                                                    @php
                                                                        $commentAuthor =
                                                                            $resolveCommentAuthorFromDirectory($comment);
                                                                    @endphp

                                                                    <p class="truncate text-[10px] font-extrabold text-slate-800">
                                                                        {{ $commentAuthor['name'] }}
                                                                    </p>

                                                                    @if(!empty($commentAuthor['role']))
                                                                        <p class="mt-0.5 text-[8px] font-semibold text-indigo-500">
                                                                            {{ $commentAuthor['role'] }}
                                                                        </p>
                                                                    @endif


                                                                    <p class="mt-0.5 text-[8px] text-slate-400">

                                                                        Cell:

                                                                        <span class="font-bold text-slate-500">

                                                                            {{ $commentCell?->coordinate ?? '-' }}

                                                                        </span>


                                                                        @if($comment->created_at)

                                                                            <span class="mx-1">
                                                                                •
                                                                            </span>

                                                                            {{ $comment->created_at->format('d M Y H:i') }}

                                                                        @endif

                                                                    </p>

                                                                </div>

                                                            </div>


                                                            {{-- REVISION STATUS --}}

                                                            @if($comment->is_revision)

                                                                @if($comment->is_resolved)

                                                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[8px] font-extrabold text-emerald-700">

                                                                        <i class="fa-solid fa-circle-check"></i>

                                                                        Selesai

                                                                    </span>

                                                                @else

                                                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[8px] font-extrabold text-red-700">

                                                                        <i class="fa-solid fa-rotate"></i>

                                                                        Revisi

                                                                    </span>

                                                                @endif

                                                            @endif

                                                        </div>


                                                        {{-- ==================================================
                                                             COMMENT CONTENT
                                                             ================================================== --}}

                                                        <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2.5">

                                                            <p class="whitespace-pre-wrap break-words text-[10px] leading-5 text-slate-600">
                                                                {{ $comment->comment ?? $comment->comment_text ?? '' }}
                                                            </p>

                                                        </div>


                                                        {{-- ==================================================
                                                             SELECTED TEXT
                                                             ================================================== --}}

                                                        @if(!empty($comment->selected_text))

                                                            <div class="mt-2 rounded-lg border-l-2 border-indigo-300 bg-indigo-50 px-3 py-2">

                                                                <p class="text-[8px] font-bold uppercase tracking-wide text-indigo-400">
                                                                    Bagian yang dikomentari
                                                                </p>

                                                                <p class="mt-1 whitespace-pre-wrap break-words text-[9px] leading-4 text-indigo-700">
                                                                    {{ $comment->selected_text }}
                                                                </p>

                                                            </div>

                                                        @endif


                                                        {{-- ==================================================
                                                             RESOLVE
                                                             ================================================== --}}

                                                        @if(
                                                            $isGuru &&
                                                            $comment->is_revision &&
                                                            !$comment->is_resolved
                                                        )

                                                            <button
                                                                type="button"
                                                                class="resolve-revision-button mt-2 inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-[9px] font-extrabold text-emerald-700 transition hover:bg-emerald-100"
                                                                data-comment-id="{{ $comment->id }}"
                                                            >

                                                                <i class="fa-solid fa-check"></i>

                                                                Tandai selesai

                                                            </button>

                                                        @endif

                                                    </div>

                                                @empty

                                                    <div class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-8 text-center">

                                                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400">

                                                            <i class="fa-regular fa-comments text-lg"></i>

                                                        </div>


                                                        <p class="mt-3 text-[10px] font-bold text-slate-500">
                                                            Belum ada komentar
                                                        </p>


                                                        <p class="mt-1 text-[9px] text-slate-400">
                                                            Komentar pada sheet ini akan tampil di sini.
                                                        </p>

                                                    </div>

                                                @endforelse

                                            </div>

                                        </div>

                                    </div>

                                </aside>

                            </div>


                            {{-- ======================================================
                                 INSTRUCTION
                                 ====================================================== --}}

                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 px-1 text-[9px] text-slate-400">

                                @if($isGuru)

                                    <span>

                                        <i class="fa-solid fa-hand-pointer mr-1"></i>

                                        <strong class="text-slate-500">
                                            Klik satu kali
                                        </strong>

                                        untuk memilih cell.

                                    </span>


                                    <span>

                                        <i class="fa-solid fa-pen-to-square mr-1"></i>

                                        <strong class="text-slate-500">
                                            Double-click
                                        </strong>

                                        untuk mengedit cell.

                                    </span>

                                @else

                                    <span>

                                        <i class="fa-solid fa-hand-pointer mr-1"></i>

                                        <strong class="text-slate-500">
                                            Klik cell
                                        </strong>

                                        untuk memilih cell dan memberikan komentar.

                                    </span>

                                @endif

                            </div>

                        </section>

                    @endforeach


                @else

                    {{-- =================================================================
                         DOCUMENT WITHOUT SHEETS
                         ================================================================= --}}

                    <section class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                            <i class="fa-solid fa-table text-2xl"></i>

                        </div>


                        <h3 class="mt-5 text-base font-extrabold text-slate-700">
                            Dokumen belum memiliki sheet
                        </h3>


                        <p class="mt-2 text-sm text-slate-400">
                            Tidak ada worksheet yang tersimpan dari file Excel.
                        </p>

                    </section>

                @endif


            @else

                {{-- =================================================================
                     FALLBACK
                     ================================================================= --}}

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="px-6 py-14 text-center sm:px-10">

                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">

                            <i class="fa-solid fa-diagram-project text-3xl"></i>

                        </div>


                        <h2 class="mt-6 text-xl font-extrabold text-slate-800">
                            Belum Ada File Analisis CP hingga ATP
                        </h2>


                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">

                            Guru belum mengupload file Bagan Analisis.
                            Setelah file diupload, dokumen akan tampil
                            pada halaman ini.

                        </p>


                        <div class="mx-auto mt-6 inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-xs font-bold text-amber-600">

                            <i class="fa-solid fa-clock"></i>

                            Menunggu upload dari guru

                        </div>

                    </div>

                </section>

            @endif

        </main>

    </div>

</div>


@if($isGuru)
<div
    id="reuploadModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 px-4 py-6 backdrop-blur-sm"
>
    <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-800">Upload Ulang Bagan Analisis</h3>
                <p class="mt-1 text-xs text-slate-500">Upload file Excel baru untuk membuat dokumen Analisis CP hingga ATP yang baru.</p>
            </div>
            <button type="button" id="closeReuploadModal" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ $uploadRoute }}" method="POST" enctype="multipart/form-data" class="space-y-5 px-5 py-5">
            @csrf
            <input type="hidden" name="document_type" value="bagan_analisis">

            <div>
                <label for="reuploadTitle" class="mb-2 block text-sm font-bold text-slate-700">Judul Dokumen</label>
                <input
                    id="reuploadTitle"
                    type="text"
                    name="title"
                    value="{{ $document->title ?? 'Analisis CP hingga ATP' }}"
                    required
                    maxlength="255"
                    class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >
            </div>

            <div>
                <label for="reuploadFile" class="mb-2 block text-sm font-bold text-slate-700">File Excel Baru</label>
                <input
                    id="reuploadFile"
                    type="file"
                    name="file"
                    accept=".xlsx,.xls"
                    required
                    class="block w-full cursor-pointer rounded-xl border border-slate-300 bg-white px-4 py-3 text-xs text-slate-600"
                >
                <p class="mt-2 text-[10px] text-slate-400">Format XLS/XLSX, maksimal 20 MB.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="cancelReuploadModal" class="rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-200">Batal</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-extrabold text-white hover:bg-indigo-700">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Upload Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endif



<style>
.excel-cell.has-revision-border {
    border: 2px solid #ef4444 !important;
    background-color: #fef2f2 !important;
}

.excel-cell.has-revision-border:hover {
    background-color: #fee2e2 !important;
}
</style>

{{-- ========================================================================
     JAVASCRIPT
     ======================================================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | ROUTES
    |--------------------------------------------------------------------------
    */

    const routes = {
        comment: @json($commentUrlTemplate),
        resolve: @json($resolveCommentUrlTemplate),
        updateCell: @json($updateCellUrlTemplate),
        autosaveCell: @json($autosaveCellUrlTemplate),
        saveArchive: @json($saveArchiveUrl),
        browseDocuments: @json($browseDocumentsUrl),
        sharedDocumentBase: @json($sharedDocumentBaseUrl)
    };

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC DRIVE
    |--------------------------------------------------------------------------
    | Hanya menambahkan penyimpanan ke Drive dan daftar dokumen.
    | Tidak menyentuh handler komentar/revisi/autosave yang sudah ada.
    */
    const teacherDocumentsDropdown = document.getElementById('academicTeacherDocumentsDropdown');
    const archiveDocumentsDropdown = document.getElementById('academicArchiveDocumentsDropdown');
    const saveDriveButton = document.getElementById('btnSaveToAcademicDrive');
    const saveDriveText = document.getElementById('btnSaveToAcademicDriveText');

    function appendDocumentOptions(select, items, placeholder) {
        if (!select) return;

        select.innerHTML = '';

        const firstOption = document.createElement('option');
        firstOption.value = '';
        firstOption.textContent = placeholder;
        select.appendChild(firstOption);

        items.forEach(function (item) {
            const option = document.createElement('option');
            option.value = item.view_url || '';
            option.textContent = (item.title || item.original_filename || 'Tanpa judul')
                + (item.owner_name ? ' — ' + item.owner_name : '');
            select.appendChild(option);
        });
    }

    async function loadAcademicDocumentDropdowns() {
        if (!routes.browseDocuments) return;

        try {
            const response = await fetch(routes.browseDocuments, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Gagal mengambil daftar dokumen.');
            }

            const items = Array.isArray(data.documents) ? data.documents : [];

            const teacherItems = items.filter(function (item) {
                return item.is_saved !== true;
            });

            const archiveItems = items.filter(function (item) {
                return item.is_saved === true;
            });

            appendDocumentOptions(
                teacherDocumentsDropdown,
                teacherItems,
                teacherItems.length
                    ? 'Dokumen Guru'
                    : 'Dokumen Guru — kosong'
            );

            appendDocumentOptions(
                archiveDocumentsDropdown,
                archiveItems,
                archiveItems.length
                    ? 'Arsip Tersimpan'
                    : 'Arsip Tersimpan — kosong'
            );

        } catch (error) {
            console.error('ACADEMIC DRIVE DROPDOWN ERROR:', error);

            appendDocumentOptions(
                teacherDocumentsDropdown,
                [],
                'Dokumen Guru — gagal dimuat'
            );

            appendDocumentOptions(
                archiveDocumentsDropdown,
                [],
                'Arsip Tersimpan — gagal dimuat'
            );
        }
    }

    function openSelectedAcademicDocument(select) {
        const url = select?.value || '';

        if (!url) return;

        window.location.href = url;
    }

    teacherDocumentsDropdown?.addEventListener('change', function () {
        openSelectedAcademicDocument(this);
        this.selectedIndex = 0;
    });

    archiveDocumentsDropdown?.addEventListener('change', function () {
        openSelectedAcademicDocument(this);
        this.selectedIndex = 0;
    });

    loadAcademicDocumentDropdowns();

    saveDriveButton?.addEventListener('click', async function () {
        if (!routes.saveArchive) return;
        const title = window.prompt('Masukkan judul dokumen untuk disimpan ke Drive:', @json($document?->title ?? 'Analisis CP hingga ATP'));
        if (title === null) return;
        if (!title.trim()) { alert('Judul dokumen wajib diisi.'); return; }
        saveDriveButton.disabled = true;
        if (saveDriveText) saveDriveText.textContent = 'Menyimpan...';
        try {
            const body = new URLSearchParams();
            body.set('_token', csrfToken);
            body.set('title', title.trim());
            body.set('publish', '1');
            const response = await fetch(routes.saveArchive, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Dokumen gagal disimpan ke Drive.');
            if (saveDriveText) saveDriveText.textContent = 'Tersimpan di Drive';
            showSaveNotification(data.message || 'Dokumen berhasil disimpan ke Drive.', 'success');
        } catch (error) {
            if (saveDriveText) saveDriveText.textContent = 'Simpan ke Drive';
            alert(error.message || 'Dokumen gagal disimpan ke Drive.');
        } finally {
            saveDriveButton.disabled = false;
        }
    });



    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    /* AUTO SAVE NOTIFICATION */
    const saveNotification = document.createElement('div');
    saveNotification.id = 'cell-save-notification';
    saveNotification.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:none;padding:10px 16px;border-radius:10px;background:#111827;color:#fff;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.18);';
    document.body.appendChild(saveNotification);

    let saveNotificationTimer = null;
    function showSaveNotification(message, type = 'saving') {
        saveNotification.textContent = message;
        saveNotification.style.display = 'block';
        saveNotification.style.background = type === 'success' ? '#166534' : (type === 'error' ? '#b91c1c' : '#111827');
        if (saveNotificationTimer) clearTimeout(saveNotificationTimer);
        if (type !== 'saving') {
            saveNotificationTimer = setTimeout(() => {
                saveNotification.style.display = 'none';
            }, 1800);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT
    |--------------------------------------------------------------------------
    */

    const fileInput =
        document.getElementById('excelFile');

    const selectedFileBox =
        document.getElementById('selectedFileBox');

    const selectedFileName =
        document.getElementById('selectedFileNameValue');


    if (
        fileInput &&
        selectedFileBox &&
        selectedFileName
    ) {

        fileInput.addEventListener(
            'change',
            function () {

                if (
                    this.files &&
                    this.files.length
                ) {

                    selectedFileName.textContent =
                        this.files[0].name;

                    selectedFileBox.classList.remove(
                        'hidden'
                    );

                    selectedFileBox.classList.add(
                        'flex'
                    );

                } else {

                    selectedFileName.textContent = '';

                    selectedFileBox.classList.add(
                        'hidden'
                    );

                    selectedFileBox.classList.remove(
                        'flex'
                    );

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SHEET SELECTOR
    |--------------------------------------------------------------------------
    */

    const sheetSelector =
        document.getElementById('sheetSelector');

    const sheetContainers =
        document.querySelectorAll(
            'section[id^="sheet-"]'
        );


    function clearSelection() {

        document
            .querySelectorAll(
                '.excel-cell.selected-cell'
            )
            .forEach(
                function (cell) {

                    cell.classList.remove(
                        'selected-cell',
                        'ring-2',
                        'ring-inset',
                        'ring-blue-600',
                        'bg-blue-50',
                        'z-10'
                    );

                }
            );


        document
            .querySelectorAll(
                '.selected-cell-badge'
            )
            .forEach(
                function (badge) {

                    badge.classList.add(
                        'hidden'
                    );

                    badge.classList.remove(
                        'inline-flex'
                    );

                }
            );

    }


    function showSheet(id) {

        sheetContainers.forEach(
            function (sheet) {

                if (sheet.id === id) {

                    sheet.classList.remove(
                        'hidden'
                    );

                } else {

                    sheet.classList.add(
                        'hidden'
                    );

                }

            }
        );

        clearSelection();

    }


    if (sheetSelector) {

        sheetSelector.addEventListener(
            'change',
            function () {

                showSheet(
                    this.value
                );

            }
        );


        showSheet(
            sheetSelector.value
        );

    }

    window.showSheet = showSheet;


    /*
    |--------------------------------------------------------------------------
    | COMMENT PANEL TOGGLE
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.toggle-comment-panel'
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function (event) {

                        event.preventDefault();
                        event.stopPropagation();


                        const panelId =
                            this.dataset.commentPanelTarget;

                        const workspaceId =
                            this.dataset.workspaceTarget;


                        const panel =
                            document.getElementById(
                                panelId
                            );

                        const workspace =
                            document.getElementById(
                                workspaceId
                            );


                        if (
                            !panel ||
                            !workspace
                        ) {
                            return;
                        }


                        const icon =
                            this.querySelector(
                                '.toggle-comment-icon'
                            );

                        const text =
                            this.querySelector(
                                '.toggle-comment-text'
                            );


                        const isOpen =
                            workspace.dataset.commentOpen !== '0';


                        if (isOpen) {

                            panel.classList.add(
                                'hidden'
                            );

                            workspace.classList.remove(
                                'xl:grid-cols-[minmax(0,1fr)_360px]'
                            );

                            workspace.classList.add(
                                'xl:grid-cols-1'
                            );

                            workspace.dataset.commentOpen =
                                '0';


                            if (icon) {
                                icon.textContent = '▶';
                            }

                            if (text) {
                                text.textContent =
                                    'Tampilkan Komentar';
                            }

                        } else {

                            panel.classList.remove(
                                'hidden'
                            );

                            workspace.classList.remove(
                                'xl:grid-cols-1'
                            );

                            workspace.classList.add(
                                'xl:grid-cols-[minmax(0,1fr)_360px]'
                            );

                            workspace.dataset.commentOpen =
                                '1';


                            if (icon) {
                                icon.textContent = '◀';
                            }

                            if (text) {
                                text.textContent =
                                    'Sembunyikan Komentar';
                            }

                        }

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | SELECT CELL
    |--------------------------------------------------------------------------
    */

    function selectCell(cell) {

        if (!cell) {
            return;
        }


        const cellId =
            cell.dataset.cellId;


        if (!cellId) {

            console.error(
                'Cell tidak memiliki data-cell-id:',
                cell
            );

            clearSelection();

            return;
        }


        clearSelection();


        cell.classList.add(
            'selected-cell',
            'ring-2',
            'ring-inset',
            'ring-blue-600',
            'bg-blue-50',
            'z-10'
        );


        const sheet =
            cell.closest(
                'section[id^="sheet-"]'
            );


        if (!sheet) {
            return;
        }


        const form =
            sheet.querySelector(
                '.comment-form'
            );


        if (!form) {

            console.warn(
                'Form komentar tidak ditemukan:',
                sheet.id
            );

            return;
        }


        /*
        |----------------------------------------------------------------------
        | CELL ID
        |----------------------------------------------------------------------
        */

        let cellInput =
            form.querySelector(
                '.comment-cell-id'
            );


        if (!cellInput) {

            cellInput =
                document.createElement(
                    'input'
                );

            cellInput.type =
                'hidden';

            cellInput.name =
                'academic_document_cell_id';

            cellInput.className =
                'comment-cell-id';

            form.appendChild(
                cellInput
            );

        }


        cellInput.name =
            'academic_document_cell_id';

        cellInput.value =
            cellId;


        /*
        |----------------------------------------------------------------------
        | COORDINATE
        |----------------------------------------------------------------------
        */

        const coordinate =
            cell.dataset.coordinate || '';


        let coordinateInput =
            form.querySelector(
                '.comment-cell-coordinate'
            );


        if (!coordinateInput) {

            coordinateInput =
                document.createElement(
                    'input'
                );

            coordinateInput.type =
                'hidden';

            coordinateInput.name =
                'coordinate';

            coordinateInput.className =
                'comment-cell-coordinate';

            form.appendChild(
                coordinateInput
            );

        }


        coordinateInput.value =
            coordinate;


        /*
        |----------------------------------------------------------------------
        | SELECTED TEXT
        |----------------------------------------------------------------------
        */

        const selectedTextInput =
            form.querySelector(
                '.comment-selected-text'
            );


        if (selectedTextInput) {

            const valueElement =
                cell.querySelector(
                    '.cell-value'
                );

            selectedTextInput.value =
                valueElement?.textContent?.trim() || '';

        }


        /*
        |----------------------------------------------------------------------
        | ANCHOR KEY
        |----------------------------------------------------------------------
        */

        const anchorInput =
            form.querySelector(
                '.comment-anchor-key'
            );


        if (anchorInput) {

            anchorInput.value =
                `${sheet.dataset?.sheetId || sheet.id}:${coordinate}`;

        }


        /*
        |----------------------------------------------------------------------
        | LABEL
        |----------------------------------------------------------------------
        */

        const selectedLabel =
            sheet.querySelector(
                '.selected-cell-label'
            );


        if (selectedLabel) {

            selectedLabel.textContent =
                coordinate ||
                cellId;

        }


        const badge =
            sheet.querySelector(
                '.selected-cell-badge'
            );


        if (badge) {

            badge.classList.remove(
                'hidden'
            );

            badge.classList.add(
                'inline-flex'
            );

        }


        // Pastikan panel komentar terbuka ketika cell dipilih.
        const panelButton = sheet.querySelector('.toggle-comment-panel');
        const workspace = sheet.querySelector('.sheet-workspace');
        const panel = sheet.querySelector('.comment-panel');

        if (panel && workspace && workspace.dataset.commentOpen === '0') {
            panel.classList.remove('hidden');
            workspace.classList.remove('xl:grid-cols-1');
            workspace.classList.add('xl:grid-cols-[minmax(0,1fr)_360px]');
            workspace.dataset.commentOpen = '1';

            const icon = panelButton?.querySelector('.toggle-comment-icon');
            const text = panelButton?.querySelector('.toggle-comment-text');
            if (icon) icon.textContent = '◀';
            if (text) text.textContent = 'Sembunyikan Komentar';
        }

        console.log(
            'CELL TERPILIH:',
            {
                cellId: cellId,
                coordinate: coordinate
            }
        );

    }


    // Dipakai juga oleh script handler di bawah yang berada pada scope berbeda.
    window.selectCell = selectCell;

    /*
    |--------------------------------------------------------------------------
    | CELL CLICK
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.excel-cell'
        )
        .forEach(
            function (cell) {

                cell.addEventListener(
                    'click',
                    function (event) {

                        if (
                            event.target.closest(
                                'textarea, button, input'
                            )
                        ) {
                            return;
                        }


                        selectCell(
                            this
                        );

                    }
                );


                @if($isGuru)

                cell.addEventListener(
                    'dblclick',
                    function (event) {

                        event.preventDefault();
                        event.stopPropagation();


                        const currentCell =
                            this;


                        const cellId =
                            currentCell.dataset.cellId;


                        if (!cellId) {
                            return;
                        }


                        if (typeof hideQuickMenu === 'function') {
                            hideQuickMenu();
                        }

                        selectCell(
                            currentCell
                        );


                        openCellEditor(
                            currentCell
                        );

                    }
                );

                @endif

            }
        );


    /*
    |--------------------------------------------------------------------------
    | CREATE CELL EDITOR
    |--------------------------------------------------------------------------
    */

    function createEditor(cell) {

        const editor =
            cell.querySelector(
                '.cell-editor'
            );

        const valueElement =
            cell.querySelector(
                '.cell-value'
            );

        if (!editor || !valueElement) {
            return null;
        }

        if (editor.dataset.created === '1') {
            return editor.querySelector(
                '.excel-cell-editor'
            );
        }

        const currentValue =
            valueElement.textContent || '';

        editor.innerHTML = '';

        const textarea =
            document.createElement('textarea');

        textarea.className =
            'excel-cell-editor block min-h-[110px] w-full resize-y rounded-lg border border-blue-400 bg-white p-2.5 text-[11px] leading-5 text-slate-700 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100';

        textarea.rows = 4;
        textarea.value = currentValue.trim();
        textarea.dataset.cellId = cell.dataset.cellId;

        editor.appendChild(textarea);
        editor.dataset.created = '1';

        attachEditorEvents(
            cell,
            textarea
        );

        return textarea;
    }

    /*
    |--------------------------------------------------------------------------
    | OPEN EDITOR
    |--------------------------------------------------------------------------
    */

    function openCellEditor(cell) {

        @if(!$isGuru)
            return;
        @endif


        if (!cell) {
            return;
        }


        const valueElement =
            cell.querySelector(
                '.cell-value'
            );


        const editor =
            cell.querySelector(
                '.cell-editor'
            );


        if (
            !valueElement ||
            !editor
        ) {
            return;
        }


        if (
            !editor.classList.contains(
                'hidden'
            )
        ) {

            const textarea =
                editor.querySelector(
                    '.excel-cell-editor'
                );


            if (textarea) {
                textarea.focus();
            }


            return;

        }


        const textarea =
            createEditor(
                cell
            );


        if (!textarea) {
            return;
        }


        valueElement.classList.add(
            'hidden'
        );


        editor.classList.remove(
            'hidden'
        );


        cell.classList.remove(
            'bg-blue-50'
        );


        cell.classList.add(
            'ring-2',
            'ring-inset',
            'ring-blue-600',
            'bg-white',
            'z-40'
        );


        textarea.focus();


        // Saat double-click, seluruh isi langsung terseleksi sehingga
        // pengguna cukup mengetik untuk mengganti isi cell.
        textarea.select();

    }


    // Dipakai juga oleh quick action menu di script enhancement paling bawah.
    window.openCellEditor = openCellEditor;


    /*
    |--------------------------------------------------------------------------
    | CLOSE EDITOR
    |--------------------------------------------------------------------------
    */

    function closeEditor(cell) {

        if (!cell) {
            return;
        }


        const valueElement =
            cell.querySelector(
                '.cell-value'
            );


        const editor =
            cell.querySelector(
                '.cell-editor'
            );


        if (valueElement) {

            valueElement.classList.remove(
                'hidden'
            );

        }


        if (editor) {

            editor.classList.add(
                'hidden'
            );

        }


        cell.classList.remove(
            'ring-2',
            'ring-inset',
            'ring-blue-600',
            'z-40'
        );


        if (
            cell.classList.contains(
                'selected-cell'
            )
        ) {

            cell.classList.add(
                'bg-blue-50'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | EDITOR EVENTS
    |--------------------------------------------------------------------------
    */

    function attachEditorEvents(
        cell,
        textarea
    ) {

        // Setiap perubahan akan disimpan otomatis setelah pengguna
        // berhenti mengetik sebentar. Tidak ada tombol Simpan/Batal.
        textarea.addEventListener(
            'input',
            function () {
                scheduleCellAutoSave(
                    cell,
                    textarea
                );
            }
        );

        // Saat fokus keluar, simpan perubahan terakhir segera.
        // Editor tidak ditutup otomatis agar teks tidak terlihat hilang.
        textarea.addEventListener(
            'blur',
            function () {
                flushCellAutoSave(
                    cell,
                    textarea
                );
            }
        );

        // Ctrl + Enter hanya mempercepat penyimpanan; tetap tanpa tombol.
        textarea.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.ctrlKey &&
                    event.key === 'Enter'
                ) {
                    event.preventDefault();
                    flushCellAutoSave(
                        cell,
                        textarea
                    );
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE CELL
    |--------------------------------------------------------------------------
    */

    let cellSaveTimers = new Map();
    let cellSaveControllers = new Map();

    function saveCell(cell, textarea, options = {}) {

        const cellId = cell?.dataset?.cellId;
        if (!cellId) return Promise.reject(new Error('Cell tidak memiliki ID.'));

        const value = textarea.value;
        const url = routes.autosaveCell.replace('__CELL__', encodeURIComponent(cellId));
        const silent = options.silent === true;
        showSaveNotification('Menyimpan perubahan...', 'saving');

        const previousController = cellSaveControllers.get(cellId);
        if (previousController) previousController.abort();

        const controller = new AbortController();
        cellSaveControllers.set(cellId, controller);

        cell.classList.add('cell-saving');
        cell.dataset.saveStatus = 'saving';

        const body = new URLSearchParams();
        body.set('value', value);
        body.set('_token', csrfToken);

        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString(),
            signal: controller.signal
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || `Gagal menyimpan cell (${response.status}).`);
            }
            if (!data.success) {
                throw new Error(data.message || 'Cell gagal disimpan.');
            }
            return data;
        })
        .then(data => {
            const savedValue = data.cell?.value ?? value;
            const valueElement = cell.querySelector('.cell-value');
            if (valueElement) valueElement.textContent = savedValue;

            cell.dataset.saveStatus = 'saved';
            cell.dataset.lastSavedValue = savedValue;
            cell.classList.remove('cell-save-error');
            cell.classList.add('cell-save-ok');

            setTimeout(() => cell.classList.remove('cell-save-ok'), 900);
            showSaveNotification('✓ Perubahan telah disimpan', 'success');
            return data;
        })
        .catch(error => {
            if (error.name === 'AbortError') return null;

            console.error('AUTO SAVE CELL ERROR:', error);
            cell.dataset.saveStatus = 'error';
            cell.classList.add('cell-save-error');
            showSaveNotification('✕ Gagal menyimpan: ' + (error.message || 'Terjadi kesalahan.'), 'error');
            throw error;
        })
        .finally(() => {
            if (cellSaveControllers.get(cellId) === controller) {
                cellSaveControllers.delete(cellId);
            }
            cell.classList.remove('cell-saving');


        });
    }

    function scheduleCellAutoSave(cell, textarea) {
        const cellId = cell?.dataset?.cellId;
        if (!cellId) return;

        const oldTimer = cellSaveTimers.get(cellId);
        if (oldTimer) clearTimeout(oldTimer);

        cell.dataset.saveStatus = 'pending';

        const timer = setTimeout(() => {
            cellSaveTimers.delete(cellId);
            saveCell(cell, textarea, { silent: true }).catch(() => {});
        }, 450);

        cellSaveTimers.set(cellId, timer);
    }

    function flushCellAutoSave(cell, textarea) {
        const cellId = cell?.dataset?.cellId;
        if (!cellId) return Promise.resolve();

        const timer = cellSaveTimers.get(cellId);
        if (timer) {
            clearTimeout(timer);
            cellSaveTimers.delete(cellId);
        }

        return saveCell(cell, textarea, { silent: true }).catch(() => {});
    }


    /*
    |--------------------------------------------------------------------------
    | COMMENT SUBMIT
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.comment-form'
        )
        .forEach(
            function (form) {

                form.addEventListener(
                    'submit',
                    function (event) {

                        event.preventDefault();


                        /*
                        |----------------------------------------------------------
                        | FIND CELL ID
                        |----------------------------------------------------------
                        */

                        let cellInput =
                            form.querySelector(
                                '.comment-cell-id'
                            );


                        let cellId =
                            cellInput?.value?.trim() || '';


                        if (!cellId) {

                            const sheet =
                                form.closest(
                                    'section[id^="sheet-"]'
                                );


                            const selectedCell =
                                sheet?.querySelector(
                                    '.excel-cell.selected-cell[data-cell-id]'
                                );


                            if (selectedCell) {

                                cellId =
                                    selectedCell.dataset.cellId;


                                if (cellInput) {

                                    cellInput.value =
                                        cellId;

                                }

                            }

                        }


                        /*
                        |----------------------------------------------------------
                        | KOMENTAR UMUM
                        |----------------------------------------------------------
                        |
                        | Jika user tidak memilih cell, gunakan 0 sebagai
                        | penanda komentar umum. Controller akan mengambil
                        | sheet dari academic_document_sheet_id.
                        |
                        */
                        if (!cellId) {

                            cellId = '0';

                            if (cellInput) {
                                cellInput.value = '0';
                            }

                        }


                        /*
                        |----------------------------------------------------------
                        | FIND COMMENT INPUT
                        |----------------------------------------------------------
                        */

                        let commentInput =
                            form.querySelector(
                                '[name="comment_text"]'
                            );


                        if (!commentInput) {

                            commentInput =
                                form.querySelector(
                                    '.comment-input'
                                );

                        }


                        if (!commentInput) {

                            commentInput =
                                form.querySelector(
                                    '.comment-textarea'
                                );

                        }


                        if (!commentInput) {

                            commentInput =
                                form.querySelector(
                                    'textarea'
                                );

                        }


                        if (!commentInput) {

                            console.error(
                                'Textarea komentar tidak ditemukan.',
                                form
                            );


                            alert(
                                'Kolom komentar tidak ditemukan.'
                            );


                            return;

                        }


                        const commentText =
                            commentInput.value.trim();


                        if (!commentText) {

                            alert(
                                'Komentar tidak boleh kosong.'
                            );


                            commentInput.focus();


                            return;

                        }


                        /*
                        |----------------------------------------------------------
                        | URL
                        |----------------------------------------------------------
                        */

                        const urlTemplate =
                            form.dataset.commentUrlTemplate
                            || routes.comment
                            || '';

                        const url =
                            urlTemplate.replace(
                                '__CELL__',
                                encodeURIComponent(cellId)
                            );

                        if (!url || url === window.location.href) {
                            throw new Error(
                                'Endpoint komentar tidak valid. Form tidak boleh dikirim ke halaman tampilan.'
                            );
                        }

                        form.action = url;


                        /*
                        |----------------------------------------------------------
                        | FORM DATA
                        |----------------------------------------------------------
                        */

                        const formData =
                            new FormData(
                                form
                            );


                        /*
                        |----------------------------------------------------------
                        | FORCE REQUIRED FIELD NAMES
                        |----------------------------------------------------------
                        */

                        formData.set(
                            'academic_document_cell_id',
                            cellId
                        );


                        const sheetIdInput =
                            form.querySelector(
                                '.comment-sheet-id'
                            );

                        if (sheetIdInput) {
                            formData.set(
                                'academic_document_sheet_id',
                                sheetIdInput.value || ''
                            );
                        }


                        const documentIdInput =
                            form.querySelector(
                                '.comment-document-id'
                            );

                        if (documentIdInput) {
                            formData.set(
                                'academic_document_id',
                                documentIdInput.value || ''
                            );
                        }


                        /*
                         * Komentar umum tidak boleh menjadi revisi.
                         */
                        const revisionInput =
                            form.querySelector(
                                '[name="is_revision"]'
                            );

                        if (String(cellId) === '0') {
                            formData.set(
                                'is_revision',
                                '0'
                            );

                            formData.set(
                                'selected_text',
                                ''
                            );

                            formData.set(
                                'anchor_key',
                                ''
                            );
                        }


                        formData.set(
                            'comment_text',
                            commentText
                        );


                        /*
                        |----------------------------------------------------------
                        | COORDINATE
                        |----------------------------------------------------------
                        */

                        const coordinateInput =
                            form.querySelector(
                                '.comment-cell-coordinate'
                            );


                        if (
                            coordinateInput &&
                            coordinateInput.value
                        ) {

                            formData.set(
                                'coordinate',
                                coordinateInput.value
                            );

                        }


                        /*
                        |----------------------------------------------------------
                        | SELECTED TEXT
                        |----------------------------------------------------------
                        */

                        const selectedTextInput =
                            form.querySelector(
                                '.comment-selected-text'
                            );


                        if (selectedTextInput) {

                            formData.set(
                                'selected_text',
                                selectedTextInput.value || ''
                            );

                        }


                        /*
                        |----------------------------------------------------------
                        | ANCHOR KEY
                        |----------------------------------------------------------
                        */

                        const anchorInput =
                            form.querySelector(
                                '.comment-anchor-key'
                            );


                        if (anchorInput) {

                            formData.set(
                                'anchor_key',
                                anchorInput.value || ''
                            );

                        }


                        /*
                        |----------------------------------------------------------
                        | DEBUG
                        |----------------------------------------------------------
                        */

                        console.log(
                            'COMMENT DATA:',
                            {
                                academic_document_cell_id:
                                    formData.get(
                                        'academic_document_cell_id'
                                    ),

                                comment_text:
                                    formData.get(
                                        'comment_text'
                                    ),

                                coordinate:
                                    formData.get(
                                        'coordinate'
                                    ),

                                selected_text:
                                    formData.get(
                                        'selected_text'
                                    ),

                                anchor_key:
                                    formData.get(
                                        'anchor_key'
                                    ),

                                is_revision:
                                    formData.get(
                                        'is_revision'
                                    )
                            }
                        );


                        /*
                        |----------------------------------------------------------
                        | SUBMIT BUTTON
                        |----------------------------------------------------------
                        */

                        const button =
                            form.querySelector(
                                '.comment-submit-button'
                            );


                        const originalHtml =
                            button?.innerHTML ||
                            '';


                        if (button) {

                            button.disabled =
                                true;


                            button.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

                        }


                        /*
                        |----------------------------------------------------------
                        | FETCH
                        |----------------------------------------------------------
                        */

                        fetch(
                            url,
                            {
                                method: 'POST',

                                headers: {
                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrfToken,

                                    'X-Requested-With':
                                        'XMLHttpRequest'
                                },

                                body:
                                    formData
                            }
                        )
                        .then(
                            async function (response) {

                                let data = {};

                                try {

                                    data =
                                        await response.json();

                                } catch (error) {

                                    throw new Error(
                                        'Server tidak mengembalikan response JSON yang valid.'
                                    );

                                }


                                if (!response.ok) {

                                    console.error(
                                        'SERVER ERROR:',
                                        data
                                    );


                                    /*
                                    |--------------------------------------------------
                                    | VALIDATION ERROR
                                    |--------------------------------------------------
                                    */

                                    if (
                                        data.errors &&
                                        typeof data.errors === 'object'
                                    ) {

                                        const messages =
                                            Object.values(
                                                data.errors
                                            )
                                            .flat()
                                            .join('\n');


                                        throw new Error(
                                            messages ||
                                            data.message ||
                                            'Validasi gagal.'
                                        );

                                    }


                                    throw new Error(
                                        data.message ||
                                        'Gagal menyimpan komentar.'
                                    );

                                }


                                return data;

                            }
                        )
                        .then(
                            function (data) {

                                if (
                                    data.success === false
                                ) {

                                    throw new Error(
                                        data.message ||
                                        'Komentar gagal disimpan.'
                                    );

                                }


                                window.location.reload();

                            }
                        )
                        .catch(
                            function (error) {

                                console.error(
                                    'COMMENT ERROR:',
                                    error
                                );


                                alert(
                                    error.message ||
                                    'Terjadi kesalahan saat menyimpan komentar.'
                                );


                                if (button) {

                                    button.disabled =
                                        false;


                                    button.innerHTML =
                                        originalHtml;

                                }

                            }
                        );

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | RESOLVE REVISION
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.resolve-revision-button'
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const currentButton =
                            this;


                        const commentId =
                            currentButton.dataset.commentId;


                        if (!commentId) {
                            return;
                        }


                        if (
                            !confirm(
                                'Tandai revisi ini sebagai selesai?'
                            )
                        ) {

                            return;

                        }


                        const url =
                            routes.resolve.replace(
                                '__COMMENT__',
                                encodeURIComponent(
                                    commentId
                                )
                            );


                        const originalHtml =
                            currentButton.innerHTML;


                        currentButton.disabled =
                            true;


                        currentButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';


                        fetch(
                            url,
                            {
                                method: 'POST',

                                headers: {
                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrfToken,

                                    'X-Requested-With':
                                        'XMLHttpRequest'
                                }
                            }
                        )
                        .then(
                            async function (response) {

                                let data = {};

                                try {

                                    data =
                                        await response.json();

                                } catch (error) {

                                    throw new Error(
                                        'Server tidak mengembalikan response JSON yang valid.'
                                    );

                                }


                                if (!response.ok) {

                                    throw new Error(
                                        data.message ||
                                        'Gagal menyelesaikan revisi.'
                                    );

                                }


                                return data;

                            }
                        )
                        .then(
                            function (data) {

                                if (!data.success) {

                                    throw new Error(
                                        data.message ||
                                        'Revisi gagal diselesaikan.'
                                    );

                                }


                                // Komentar menjadi hijau, tetapi CELL kembali normal.
                                const card = currentButton.closest('[data-comment-cell-id]');
                                const cellId = card?.dataset.commentCellId || data.cell_id || '';
                                const targetCell = cellId
                                    ? document.querySelector('.excel-cell[data-cell-id=\"' + CSS.escape(String(cellId)) + '\"]')
                                    : null;

                                if (card) {
                                    card.dataset.isResolved = '1';
                                    card.classList.remove('comment-card-revision');
                                    card.classList.add('comment-card-resolved');
                                }

                                currentButton.remove();

                                if (targetCell) {
                                    targetCell.dataset.hasRevision = '0';
                                    targetCell.dataset.hasResolvedRevision = '0';
                                    targetCell.classList.remove('has-revision-border');
                                    targetCell.classList.remove('revision-resolved');
                                    targetCell.style.border = '';
                                    targetCell.style.backgroundColor = '';
                                }

                                // Sinkronkan dengan database setelah tampilan diperbarui.
                                setTimeout(function () {
                                    window.location.reload();
                                }, 250);

                            }
                        )
                        .catch(
                            function (error) {

                                console.error(
                                    'RESOLVE ERROR:',
                                    error
                                );


                                alert(
                                    error.message ||
                                    'Terjadi kesalahan saat menyelesaikan revisi.'
                                );


                                currentButton.disabled =
                                    false;


                                currentButton.innerHTML =
                                    originalHtml;

                            }
                        );

                    }
                );

            }
        );

});

@if($isGuru)
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('reuploadModal');
    const openButton = document.getElementById('openReuploadModal');
    const closeButton = document.getElementById('closeReuploadModal');
    const cancelButton = document.getElementById('cancelReuploadModal');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    openButton?.addEventListener('click', openModal);
    closeButton?.addEventListener('click', closeModal);
    cancelButton?.addEventListener('click', closeModal);

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
});

@endif
</script>
{{-- ========================================================================
     ENHANCEMENT: QUICK ACTION CELL / REVISION / JUMP TO REVISION
     ======================================================================== --}}
<style>
    .excel-cell.quick-target {
        outline: 3px solid #6366f1 !important;
        outline-offset: -3px;
        z-index: 25;
    }
    .excel-cell[data-cell-id]:has(.cell-editor) {
        cursor: cell !important;
    }
    .excel-cell.has-revision-border {
        border: 2px solid #ef4444 !important;
        background-color: #fef2f2 !important;
    }
    .comment-card-revision {
        border-color: #fecaca !important;
        background: #fff7f7 !important;
    }
    .comment-card-resolved {
        border-color: #bbf7d0 !important;
        background: #f0fdf4 !important;
    }
    #cellQuickActionMenu {
        position: fixed;
        z-index: 10050;
        display: none;
        min-width: 190px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(15,23,42,.18);
    }
    #cellQuickActionMenu button {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 9px;
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
    }
    #cellQuickActionMenu button:hover { background:#f8fafc; }
    #cellQuickActionMenu .qa-revision { color:#dc2626; }
    #cellQuickActionMenu .qa-comment { color:#4f46e5; }

<style>
.cell-saving { outline: 2px solid rgb(245 158 11); outline-offset: -2px; }
.cell-save-ok { outline: 2px solid rgb(34 197 94); outline-offset: -2px; }
.cell-save-error { outline: 2px solid rgb(239 68 68); outline-offset: -2px; }
</style>

@if(!$isGuru)
<div id="cellQuickActionMenu" aria-hidden="true">
    <button type="button" class="qa-comment" data-action="comment">
        <i class="fa-regular fa-comment"></i>
        <span>Beri Komentar</span>
    </button>
    <button type="button" class="qa-revision" data-action="revision">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Buat Revisi</span>
    </button>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const isGuru = @json($isGuru);
    const quickMenu = document.getElementById('cellQuickActionMenu');
    let activeCell = null;

    function hideQuickMenu() {
        if (!quickMenu) return;
        quickMenu.style.display = 'none';
        quickMenu.setAttribute('aria-hidden', 'true');
    }

    function showQuickMenu(cell, event) {
        if (isGuru || !quickMenu || !cell) return;
        activeCell = cell;
        const rect = cell.getBoundingClientRect();
        const left = Math.min(
            Math.max(8, (event?.clientX || rect.left) - 10),
            window.innerWidth - 205
        );
        const top = Math.min(
            Math.max(8, (event?.clientY || rect.bottom) + 8),
            window.innerHeight - 105
        );
        quickMenu.style.left = left + 'px';
        quickMenu.style.top = top + 'px';
        quickMenu.style.display = 'block';
        quickMenu.setAttribute('aria-hidden', 'false');
    }

    function getCellInfo(cell) {
        if (!cell) return null;
        return {
            id: cell.dataset.cellId || '',
            coordinate: cell.dataset.coordinate || '',
            value: cell.querySelector('.cell-content')?.innerText?.trim() || ''
        };
    }

    function activateCell(cell) {
        if (!cell) return;
        const sheet = cell.closest('section[id^="sheet-"]');
        if (sheet && typeof window.showSheet === 'function') {
            window.showSheet(sheet.id);
        }
        document.querySelectorAll('.excel-cell.quick-target').forEach(function (el) {
            el.classList.remove('quick-target');
        });
        cell.classList.add('quick-target');
        cell.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });

        const form = cell.closest('.sheet-workspace')?.querySelector('.comment-form');
        if (!form) return;
        const idInput = form.querySelector('.comment-cell-id');
        const coordInput = form.querySelector('.comment-cell-coordinate');
        const selectedInput = form.querySelector('.comment-selected-text');
        const anchorInput = form.querySelector('.comment-anchor-key');
        if (idInput) idInput.value = cell.dataset.cellId || '';
        if (coordInput) coordInput.value = cell.dataset.coordinate || '';
        if (selectedInput) selectedInput.value = cell.querySelector('.cell-content')?.innerText?.trim() || '';
        if (anchorInput) anchorInput.value = (cell.dataset.sheetId || '') + ':' + (cell.dataset.coordinate || '');

        const label = form.closest('.comment-panel')?.querySelector('.selected-cell-label');
        if (label) label.textContent = cell.dataset.coordinate || 'Cell dipilih';
        const badge = form.closest('.comment-panel')?.querySelector('.selected-cell-badge');
        if (badge) { badge.classList.remove('hidden'); badge.classList.add('inline-flex'); }
    }

    function setRevisionState(cell, enabled) {
        if (!cell) return;
        cell.dataset.hasRevision = enabled ? '1' : '0';
        cell.classList.toggle('has-revision-border', enabled);
        if (!enabled) cell.classList.remove('revision-resolved');
    }

    function openCommentForm(cell, revision) {
        activateCell(cell);
        hideQuickMenu();
        const form = cell.closest('.sheet-workspace')?.querySelector('.comment-form');
        if (!form) return;

        const revisionInput = form.querySelector('[name="is_revision"]');
        const textarea = form.querySelector('[name="comment_text"]');
        if (revisionInput) revisionInput.checked = !!revision;
        if (revision) setRevisionState(cell, true);
        textarea?.focus();

        const panel = form.closest('.comment-panel');
        const workspace = form.closest('.sheet-workspace');
        if (panel && workspace) {
            panel.classList.remove('hidden');
            workspace.classList.remove('xl:grid-cols-1');
            workspace.classList.add('xl:grid-cols-[minmax(0,1fr)_360px]');
            workspace.dataset.commentOpen = '1';
        }
    }

    document.querySelectorAll('.excel-cell').forEach(function (cell) {
        cell.addEventListener('click', function (event) {
            if (event.target.closest('input, textarea, button, a, .excel-cell-editor')) return;

            if (isGuru) {
                selectCell(cell);
                hideQuickMenu();
                return;
            }

            showQuickMenu(cell, event);
        });
    });

    if (quickMenu) {
        quickMenu.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-action]');
            if (!button || !activeCell) return;

            const action = button.dataset.action || '';
            const targetCell = activeCell;
            hideQuickMenu();

            openCommentForm(
                targetCell,
                action === 'revision'
            );
        });
    }

    // Shortcut keyboard untuk Guru: Enter atau F2 saat cell dipilih = edit.
    if (isGuru) {
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== 'F2') return;
            if (event.target.closest('textarea, input, select, button')) return;

            const selectedCell = document.querySelector(
                '.excel-cell.selected-cell[data-cell-id]'
            );

            if (!selectedCell) return;

            event.preventDefault();
            hideQuickMenu();
            window.openCellEditor(selectedCell);
        });
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#cellQuickActionMenu') && !event.target.closest('.excel-cell')) {
            hideQuickMenu();
        }
    });

    window.addEventListener('scroll', hideQuickMenu, true);
    window.addEventListener('resize', hideQuickMenu);

    // Revisi yang belum selesai selalu merah. Revisi yang sudah selesai tetap
    // tampil di riwayat dan diberi status hijau.
    document.querySelectorAll('[data-comment-cell-id]').forEach(function (card) {
        const isRevision = card.dataset.isRevision === '1' || card.classList.contains('is-revision');
        const isResolved = card.dataset.isResolved === '1' || card.classList.contains('is-resolved');
        if (!isRevision) return;
        card.classList.add(isResolved ? 'comment-card-resolved' : 'comment-card-revision');
    });

    // Klik kartu revisi -> fokus ke cell terkait.
    document.querySelectorAll('[data-comment-cell-id]').forEach(function (card) {
        const cellId = card.dataset.commentCellId;
        if (!cellId || cellId === '0') return;
        const isRevision = card.dataset.isRevision === '1' || card.querySelector('.resolve-revision-button');
        if (!isRevision) return;
        card.style.cursor = 'pointer';
        card.addEventListener('click', function (event) {
            if (event.target.closest('button, a, form')) return;
            const target = document.querySelector('.excel-cell[data-cell-id="' + CSS.escape(cellId) + '"]');
            if (!target) return;
            activateCell(target);
            target.classList.add('quick-target');
            setTimeout(function () { target.classList.remove('quick-target'); }, 1800);
        });
    });

    // Untuk role non-Guru, tombol resolve disembunyikan di client juga.
    if (!isGuru) {
        document.querySelectorAll('.resolve-revision-button').forEach(function (button) {
            button.remove();
        });
    }
});
</script>
