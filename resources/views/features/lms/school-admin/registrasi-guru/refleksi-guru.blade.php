{{-- ========================================================================
     REFLEKSI GURU
     File:
     resources/views/features/lms/school-admin/registrasi-guru/refleksi-guru.blade.php

     REFERENSI:
     - Mengikuti struktur tampilan ANALISIS CP HINGGA ATP
     - Card MAPEL hanya muncul jika sudah ada file Refleksi Guru
     - Sumber utama keberadaan file = AcademicDocument
     - document_type = refleksi_guru
     - Guru hanya melihat file miliknya
     - Admin/Kepala/Wakil melihat file yang tersedia pada sekolah
     - Tidak ada mapel hard-code
     - Klik Lihat Detail membawa mapel_id
     ======================================================================== --}}

@include('components/sidebar-beranda', [
    'linkBackButton' => url()->previous(),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Refleksi Guru',

    'schoolName' => optional(
        optional(Auth::user())->SchoolStaffProfile
    )->SchoolPartner->nama_sekolah ?? '',

    'schoolId' => optional(
        optional(Auth::user())->SchoolStaffProfile
    )->SchoolPartner->id ?? '',
])


@php
    /* =====================================================================
       USER & SEKOLAH
       ===================================================================== */

    $user = Auth::user();

    $schoolProfile = optional($user)->SchoolStaffProfile;

    $schoolPartner = optional($schoolProfile)->SchoolPartner;

    $schoolName = $schoolPartner->nama_sekolah ?? '';

    $schoolId = $schoolPartner->id ?? null;


    /* =====================================================================
       DEFAULT DATA
       ===================================================================== */

    $teacherMapels = collect();

    $jumlahGuru = 0;

    $jumlahMapel = 0;

    $mapelGroups = collect();

    $refleksiDocuments = collect();


    /* =====================================================================
       ROLE
       ===================================================================== */

    $currentRole = strtolower(
        trim(
            (string) (
                optional($user)->role ?? ''
            )
        )
    );

    $isGuru = $currentRole === 'guru';


    /* =====================================================================
       AMBIL TEACHER MAPEL
       ---------------------------------------------------------------------
       TeacherMapel tetap digunakan untuk mengetahui:
       - guru
       - mapel
       - penugasan

       TETAPI TeacherMapel BUKAN sumber card.

       Card hanya berdasarkan AcademicDocument yang sudah memiliki file.
       ===================================================================== */

    if ($schoolId) {

        try {

            $teacherMapels = \App\Models\TeacherMapel::query()
                ->with([
                    'Mapel',
                    'SchoolClass',
                ])
                ->where('is_active', true)
                ->whereHas(
                    'SchoolClass',
                    function ($query) use ($schoolId) {

                        $query->where(
                            'school_partner_id',
                            $schoolId
                        );
                    }
                )
                ->get();

        } catch (\Throwable $e) {

            /*
             * Fallback apabila relasi SchoolClass
             * tidak tersedia / query gagal.
             */

            try {

                $teacherMapels = \App\Models\TeacherMapel::query()
                    ->with([
                        'Mapel',
                        'SchoolClass',
                    ])
                    ->where('is_active', true)
                    ->get();

            } catch (\Throwable $fallbackError) {

                $teacherMapels = collect();
            }
        }
    }


    /* =====================================================================
       FILTER SEKOLAH PADA FALLBACK
       ===================================================================== */

    if (
        $schoolId &&
        $teacherMapels->isNotEmpty()
    ) {

        $teacherMapels = $teacherMapels
            ->filter(
                function ($teacherMapel) use ($schoolId) {

                    $schoolClass =
                        $teacherMapel->SchoolClass
                        ?? null;

                    /*
                     * Jika SchoolClass tidak tersedia,
                     * jangan langsung membuang data.
                     */

                    if (!$schoolClass) {
                        return true;
                    }

                    return (int) (
                        $schoolClass->school_partner_id
                        ?? 0
                    ) === (int) $schoolId;
                }
            )
            ->values();
    }


    /* =====================================================================
       AMBIL ACADEMIC DOCUMENT
       ---------------------------------------------------------------------
       INI BAGIAN UTAMA.

       Hanya dokumen:
       - milik sekolah ini
       - document_type = refleksi_guru
       - mempunyai file_path
       ===================================================================== */

    if ($schoolId) {

        try {

            $documentQuery =
                \App\Models\AcademicDocument::query()
                    ->where(
                        'school_partner_id',
                        $schoolId
                    )
                    ->where(
                        'document_type',
                        'refleksi_guru'
                    )
                    ->whereNotNull(
                        'file_path'
                    )
                    ->where(
                        'file_path',
                        '!=',
                        ''
                    );


            /* =============================================================
               JIKA USER ADALAH GURU
               -------------------------------------------------------------
               Guru hanya melihat dokumen yang dia upload.
               ============================================================= */

            if (
                $user &&
                $isGuru
            ) {

                /*
                 * Database terbaru menggunakan owner_user_id.
                 */

                if (
                    \Illuminate\Support\Facades\Schema::hasColumn(
                        'academic_documents',
                        'owner_user_id'
                    )
                ) {

                    $documentQuery->where(
                        'owner_user_id',
                        $user->id
                    );

                }

                /*
                 * Fallback database lama.
                 */

                elseif (
                    \Illuminate\Support\Facades\Schema::hasColumn(
                        'academic_documents',
                        'uploaded_by'
                    )
                ) {

                    $documentQuery->where(
                        'uploaded_by',
                        $user->id
                    );

                }

                elseif (
                    \Illuminate\Support\Facades\Schema::hasColumn(
                        'academic_documents',
                        'created_by'
                    )
                ) {

                    $documentQuery->where(
                        'created_by',
                        $user->id
                    );

                }

                elseif (
                    \Illuminate\Support\Facades\Schema::hasColumn(
                        'academic_documents',
                        'user_id'
                    )
                ) {

                    $documentQuery->where(
                        'user_id',
                        $user->id
                    );
                }
            }


            $refleksiDocuments = $documentQuery
                ->latest('id')
                ->get();

        } catch (\Throwable $e) {

            /*
             * Fallback apabila document_type belum tersedia.
             *
             * Untuk Refleksi Guru, lokasi file:
             * academic-documents/refleksi-guru
             */

            try {

                $documentQuery =
                    \App\Models\AcademicDocument::query()
                        ->where(
                            'school_partner_id',
                            $schoolId
                        )
                        ->whereNotNull(
                            'file_path'
                        )
                        ->where(
                            'file_path',
                            '!=',
                            ''
                        )
                        ->where(
                            'file_path',
                            'like',
                            '%refleksi-guru%'
                        );


                if (
                    $user &&
                    $isGuru
                ) {

                    if (
                        \Illuminate\Support\Facades\Schema::hasColumn(
                            'academic_documents',
                            'owner_user_id'
                        )
                    ) {

                        $documentQuery->where(
                            'owner_user_id',
                            $user->id
                        );

                    } elseif (
                        \Illuminate\Support\Facades\Schema::hasColumn(
                            'academic_documents',
                            'uploaded_by'
                        )
                    ) {

                        $documentQuery->where(
                            'uploaded_by',
                            $user->id
                        );

                    } elseif (
                        \Illuminate\Support\Facades\Schema::hasColumn(
                            'academic_documents',
                            'created_by'
                        )
                    ) {

                        $documentQuery->where(
                            'created_by',
                            $user->id
                        );

                    } elseif (
                        \Illuminate\Support\Facades\Schema::hasColumn(
                            'academic_documents',
                            'user_id'
                        )
                    ) {

                        $documentQuery->where(
                            'user_id',
                            $user->id
                        );
                    }
                }


                $refleksiDocuments =
                    $documentQuery
                        ->latest('id')
                        ->get();

            } catch (\Throwable $fallbackError) {

                $refleksiDocuments = collect();
            }
        }
    }


    /* =====================================================================
       HELPER OWNER DOCUMENT
       ---------------------------------------------------------------------
       Prioritas:
       1. owner_user_id
       2. user_id
       3. created_by
       4. uploaded_by
       ===================================================================== */

    $getDocumentOwnerId = function ($document) {

        if (
            !empty($document->owner_user_id)
        ) {
            return (int) $document->owner_user_id;
        }

        if (
            !empty($document->user_id)
        ) {
            return (int) $document->user_id;
        }

        if (
            !empty($document->created_by)
        ) {
            return (int) $document->created_by;
        }

        if (
            !empty($document->uploaded_by)
        ) {
            return (int) $document->uploaded_by;
        }

        return null;
    };


    /* =====================================================================
       HELPER MAPEL DOCUMENT
       ---------------------------------------------------------------------
       Prioritas:
       1. subject_id
       2. mapel_id
       ===================================================================== */

    $getDocumentMapelId = function ($document) {

        if (
            !empty($document->subject_id)
        ) {

            return (int) $document->subject_id;
        }

        if (
            !empty($document->mapel_id)
        ) {

            return (int) $document->mapel_id;
        }

        return null;
    };


    /* =====================================================================
       FILTER DOKUMEN VALID
       ---------------------------------------------------------------------
       Dokumen harus mempunyai:
       - owner
       - mapel
       ===================================================================== */

    $refleksiDocuments = $refleksiDocuments
        ->filter(
            function ($document) use (
                $getDocumentOwnerId,
                $getDocumentMapelId
            ) {

                $ownerId =
                    $getDocumentOwnerId(
                        $document
                    );

                $mapelId =
                    $getDocumentMapelId(
                        $document
                    );

                return
                    $ownerId !== null
                    &&
                    $ownerId > 0
                    &&
                    $mapelId !== null
                    &&
                    $mapelId > 0;
            }
        )
        ->values();


    /* =====================================================================
       MAPEL ID DARI DOKUMEN
       ===================================================================== */

    $documentMapelIds = $refleksiDocuments
        ->map(
            $getDocumentMapelId
        )
        ->filter()
        ->unique()
        ->values();


    /* =====================================================================
       JUMLAH MAPEL
       ---------------------------------------------------------------------
       Berdasarkan file yang benar-benar ada.
       ===================================================================== */

    $jumlahMapel =
        $documentMapelIds->count();


    /* =====================================================================
       JUMLAH GURU
       ---------------------------------------------------------------------
       Berdasarkan owner dokumen.
       BUKAN berdasarkan semua TeacherMapel.
       ===================================================================== */

    $jumlahGuru = $refleksiDocuments
        ->map(
            $getDocumentOwnerId
        )
        ->filter()
        ->unique()
        ->count();


    /* =====================================================================
       FILTER TEACHER MAPEL
       ---------------------------------------------------------------------
       Hanya TeacherMapel yang mapelnya memiliki file Refleksi Guru.
       ===================================================================== */

    if (
        $documentMapelIds->isNotEmpty()
        &&
        $teacherMapels->isNotEmpty()
    ) {

        $teacherMapels = $teacherMapels
            ->filter(
                function ($teacherMapel) use (
                    $documentMapelIds
                ) {

                    return $documentMapelIds->contains(
                        (int) (
                            $teacherMapel->mapel_id
                            ?? 0
                        )
                    );
                }
            )
            ->values();

    } else {

        $teacherMapels = collect();
    }


    /* =====================================================================
       AMBIL MODEL MAPEL
       ===================================================================== */

    $mapels = collect();

    if (
        $documentMapelIds->isNotEmpty()
    ) {

        try {

            $mapels =
                \App\Models\Mapel::query()
                    ->whereIn(
                        'id',
                        $documentMapelIds->all()
                    )
                    ->get();

        } catch (\Throwable $e) {

            $mapels = collect();
        }
    }


    /* =====================================================================
       KELOMPOKKAN BERDASARKAN MAPEL
       ---------------------------------------------------------------------
       Group dibuat dari MAPEL YANG SUDAH PUNYA FILE.
       ===================================================================== */

    foreach (
        $documentMapelIds as $documentMapelId
    ) {

        /* -----------------------------------------------------------------
           CARI MAPEL
           ----------------------------------------------------------------- */

        $mapel =
            $mapels->firstWhere(
                'id',
                $documentMapelId
            );


        /* -----------------------------------------------------------------
           CARI TEACHER MAPEL
           ----------------------------------------------------------------- */

        $teachers =
            $teacherMapels
                ->filter(
                    function ($teacherMapel) use (
                        $documentMapelId
                    ) {

                        return (int) (
                            $teacherMapel->mapel_id
                            ?? 0
                        ) ===
                        (int) $documentMapelId;
                    }
                )
                ->values();


        /* -----------------------------------------------------------------
           CARI DOKUMEN
           ----------------------------------------------------------------- */

        $documents =
            $refleksiDocuments
                ->filter(
                    function ($document) use (
                        $documentMapelId,
                        $getDocumentMapelId
                    ) {

                        return
                            (int) $getDocumentMapelId(
                                $document
                            ) ===
                            (int) $documentMapelId;
                    }
                )
                ->values();


        /* -----------------------------------------------------------------
           JUMLAH GURU PER MAPEL
           -----------------------------------------------------------------
           PENTING:
           Hitung berdasarkan owner dokumen, bukan TeacherMapel.
           ----------------------------------------------------------------- */

        $jumlahGuruMapel =
            $documents
                ->map(
                    $getDocumentOwnerId
                )
                ->filter()
                ->unique()
                ->count();


        /* -----------------------------------------------------------------
           SIMPAN GROUP
           ----------------------------------------------------------------- */

        $mapelGroups->put(
            $documentMapelId,
            [
                'mapel' =>
                    $mapel,

                'teachers' =>
                    $teachers,

                'documents' =>
                    $documents,

                'jumlahGuru' =>
                    $jumlahGuruMapel,

                'jumlahDokumen' =>
                    $documents->count(),
            ]
        );
    }


    /* =====================================================================
       SORT MAPEL
       ===================================================================== */

    $mapelGroups =
        $mapelGroups
            ->sortBy(
                function ($group) {

                    $mapel =
                        $group['mapel']
                        ?? null;

                    if (!$mapel) {
                        return '';
                    }

                    return strtolower(
                        (string) (
                            $mapel->nama_mapel
                            ??
                            $mapel->mata_pelajaran
                            ??
                            $mapel->nama
                            ??
                            $mapel->name
                            ??
                            ''
                        )
                    );
                }
            );


    /* =====================================================================
       AKSES
       ===================================================================== */

    $allowedRoles = [
        'guru',
        'admin sekolah',
        'kepala sekolah',
        'wakil kepala sekolah',
    ];

    $hasAccess =
        $user &&
        in_array(
            $currentRole,
            $allowedRoles,
            true
        );

@endphp


{{-- ========================================================================
     AKSES
     ======================================================================== --}}

@if ($hasAccess)

    <div
    class="relative w-full md:ml-[280px] md:w-[calc(100%-280px)] transition-all duration-500 ease-in-out z-20"
>

        <div class="my-15 mx-7.5">

            <main>

                {{-- =========================================================
                     HEADER REFLEKSI GURU
                     ========================================================= --}}

                <section
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-7"
                >

                    <div
                        class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6"
                    >

                        {{-- =================================================
                             BAGIAN KIRI
                             ================================================= --}}

                        <div
                            class="flex items-center gap-4"
                        >

                            {{-- ICON --}}

                            <div
                                class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shrink-0"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-7 h-7"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A2 2 0 0114 3.586L19.414 9A2 2 0 0120 10.414V19a2 2 0 01-2 2z"
                                    />

                                </svg>

                            </div>


                            {{-- JUDUL --}}

                            <div>

                                <h1
                                    class="text-xl font-bold text-gray-800"
                                >
                                    Refleksi Guru
                                </h1>

                                <p
                                    class="text-sm text-gray-500"
                                >
                                    Dokumen Refleksi Guru
                                </p>

                            </div>

                        </div>


                        {{-- =================================================
                             INFORMASI GURU DAN MAPEL
                             ================================================= --}}

                        <div
                            class="flex gap-4"
                        >

                            {{-- JUMLAH GURU --}}

                            <div
                                class="bg-blue-50 rounded-xl px-6 py-4 min-w-[140px]"
                            >

                                <p
                                    class="text-xs text-blue-500"
                                >
                                    Jumlah Guru
                                </p>

                                <p
                                    class="text-2xl font-bold text-blue-600"
                                >
                                    {{ $jumlahGuru }}
                                </p>

                                <p
                                    class="text-[11px] text-blue-400 mt-1"
                                >
                                    Guru dengan file
                                </p>

                            </div>


                            {{-- JUMLAH MAPEL --}}

                            <div
                                class="bg-gray-50 rounded-xl px-6 py-4 min-w-[140px]"
                            >

                                <p
                                    class="text-xs text-gray-500"
                                >
                                    Jumlah Mapel
                                </p>

                                <p
                                    class="text-2xl font-bold text-gray-700"
                                >
                                    {{ $jumlahMapel }}
                                </p>

                                <p
                                    class="text-[11px] text-gray-400 mt-1"
                                >
                                    Mapel dengan file
                                </p>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =========================================================
                     CARD MATA PELAJARAN
                     ========================================================= --}}

                <section
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6"
                >

                    {{-- JUDUL BAGIAN --}}

                    <div
                        class="mb-6"
                    >

                        <h2
                            class="text-lg font-bold text-gray-800"
                        >
                            Mata Pelajaran
                        </h2>

                        <p
                            class="text-sm text-gray-500 mt-1"
                        >
                            Pilih mata pelajaran yang sudah memiliki file
                            Refleksi Guru.
                        </p>

                    </div>


                    {{-- =====================================================
                         JIKA ADA MAPEL YANG SUDAH MEMILIKI FILE
                         ===================================================== --}}

                    @if ($mapelGroups->isNotEmpty())

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5"
                        >

                            @foreach (
                                $mapelGroups as $mapelId => $group
                            )

                                @php

                                    $mapel =
                                        $group['mapel']
                                        ?? null;

                                    $teachers =
                                        $group['teachers']
                                        ?? collect();

                                    $documents =
                                        $group['documents']
                                        ?? collect();


                                    /*
                                    |--------------------------------------------------------------------------
                                    | NAMA MAPEL
                                    |--------------------------------------------------------------------------
                                    */

                                    $mapelName =
                                        $mapel->nama_mapel
                                        ?? $mapel->mata_pelajaran
                                        ?? $mapel->nama
                                        ?? $mapel->name
                                        ?? 'Mata Pelajaran';


                                    /*
                                    |--------------------------------------------------------------------------
                                    | JUMLAH GURU
                                    |--------------------------------------------------------------------------
                                    */

                                    $jumlahGuruMapel =
                                        $group['jumlahGuru']
                                        ??
                                        $documents
                                            ->map(
                                                $getDocumentOwnerId
                                            )
                                            ->filter()
                                            ->unique()
                                            ->count();


                                    /*
                                    |--------------------------------------------------------------------------
                                    | JUMLAH DOKUMEN
                                    |--------------------------------------------------------------------------
                                    */

                                    $jumlahDokumen =
                                        $group['jumlahDokumen']
                                        ??
                                        $documents->count();


                                    /*
                                    |--------------------------------------------------------------------------
                                    | URL DETAIL
                                    |--------------------------------------------------------------------------
                                    */

                                    $refleksiUrl =
                                        route(
                                            'lms.schoolAdmin.registrasiGuru.refleksi-guru.tampilan',
                                            [
                                                'role' =>
                                                    $user->role,

                                                'schoolName' =>
                                                    $schoolName,

                                                'schoolId' =>
                                                    $schoolId,
                                            ]
                                        );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | MAPEL ID
                                    |--------------------------------------------------------------------------
                                    */

                                    $refleksiUrl .=
                                        '?mapel_id=' .
                                        urlencode(
                                            $mapelId
                                        );

                                @endphp


                                {{-- =================================================
                                     CARD MAPEL
                                     ================================================= --}}

                                <div
                                    class="border border-gray-200 rounded-2xl p-5 bg-white hover:shadow-md hover:border-blue-200 transition duration-200 group"
                                >

                                    {{-- ICON --}}

                                    <div
                                        class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 mb-4 group-hover:bg-blue-600 group-hover:text-white transition"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-6 h-6"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332 0 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332 0-4.5 1.253"
                                            />

                                        </svg>

                                    </div>


                                    {{-- NAMA MAPEL --}}

                                    <h3
                                        class="text-base font-bold text-gray-800 truncate"
                                        title="{{ $mapelName }}"
                                    >

                                        {{ $mapelName }}

                                    </h3>


                                    {{-- KETERANGAN --}}

                                    <p
                                        class="text-xs text-gray-500 mt-1"
                                    >
                                        Refleksi Guru
                                    </p>


                                    {{-- =================================================
                                         STATUS FILE
                                         ================================================= --}}

                                    <div
                                        class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-50 text-green-600"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />

                                        </svg>

                                        <span
                                            class="text-xs font-semibold"
                                        >
                                            File tersedia
                                        </span>

                                    </div>


                                    {{-- =================================================
                                         JUMLAH GURU
                                         ================================================= --}}

                                    @if ($jumlahGuruMapel > 0)

                                        <div
                                            class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600"
                                        >

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                />

                                            </svg>

                                            <span
                                                class="text-xs font-semibold"
                                            >

                                                {{ $jumlahGuruMapel }}

                                                Guru

                                            </span>

                                        </div>

                                    @endif


                                    {{-- =================================================
                                         FILE TERBARU
                                         ================================================= --}}

                                    @php

                                        $latestDocument =
                                            $documents->first();

                                    @endphp


                                    @if ($latestDocument)

                                        <p
                                            class="text-[11px] text-gray-400 mt-3 truncate"
                                            title="{{ $latestDocument->original_filename ?? '' }}"
                                        >

                                            <i
                                                class="fa-solid fa-file-word mr-1"
                                            ></i>

                                            {{
                                                $latestDocument->original_filename
                                                ?? 'File Refleksi Guru'
                                            }}

                                        </p>

                                    @endif


                                    {{-- =================================================
                                         BUTTON
                                         ================================================= --}}

                                    <div
                                        class="border-t border-dashed border-gray-200 mt-5 pt-4"
                                    >

                                        <a
                                            href="{{ $refleksiUrl }}"
                                            class="inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:text-blue-800 transition"
                                        >

                                            Lihat Detail

                                            <i
                                                class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"
                                            ></i>

                                        </a>

                                    </div>

                                </div>

                            @endforeach

                        </div>


                    @else

                        {{-- =================================================
                             BELUM ADA FILE REFLEKSI GURU
                             ================================================= --}}

                        <div
                            class="border border-dashed border-gray-300 rounded-2xl p-10 text-center"
                        >

                            <div
                                class="mx-auto w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 mb-4"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-7 h-7"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 13h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A2 2 0 0114 3.586L19.414 9A2 2 0 0120 10.414V19a2 2 0 01-2 2z"
                                    />

                                </svg>

                            </div>


                            <h3
                                class="text-base font-semibold text-gray-700"
                            >
                                Belum Ada File Refleksi Guru
                            </h3>


                            <p
                                class="text-sm text-gray-500 mt-1 max-w-md mx-auto"
                            >
                                Belum terdapat file Refleksi Guru yang
                                diupload pada sekolah ini.
                            </p>

                        </div>

                    @endif

                </section>

            </main>

        </div>

    </div>


@else

    {{-- ====================================================================
         AKSES DITOLAK
         ==================================================================== --}}

    <div
        class="flex flex-col min-h-screen items-center justify-center"
    >

        <div
            class="text-center"
        >

            <div
                class="w-16 h-16 mx-auto rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mb-4"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-8 h-8"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M5.071 19h13.858c1.54 0 2.503-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.339 16c-.77 1.333.192 3 1.732 3z"
                    />

                </svg>

            </div>


            <p
                class="text-lg font-semibold text-gray-800"
            >
                ALERT SEMENTARA
            </p>


            <p
                class="text-sm text-gray-500 mt-2"
            >
                You do not have access to this page.
            </p>

        </div>

    </div>

@endif