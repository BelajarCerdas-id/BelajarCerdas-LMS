{{-- ========================================================================
     PROTA DAN PROSEM
     File:
     resources/views/features/lms/school-admin/registrasi-guru/
     tampilan/tamp-prota-prosem.blade.php

     LOGIKA:
     - AcademicDocument adalah sumber utama keberadaan file.
     - document_type = prota_prosem.
     - file_path wajib tersedia.
     - Guru hanya melihat file miliknya.
     - Admin/Kepala/Wakil melihat file yang tersedia pada sekolah.
     - TeacherMapel digunakan sebagai informasi dan FALLBACK MAPEL.
     - JUMLAH GURU dihitung dari OWNER DOKUMEN.
     - JUMLAH MAPEL dihitung dari MAPEL DOKUMEN.
     - Card MAPEL hanya muncul jika sudah ada file PROTA/PROSEM.
     - Tidak ada mapel hard-code.
     - URL detail WAJIB menggunakan ID MAPEL ASLI.
     - Tidak menggunakan $loop->index sebagai mapel_id.
     ======================================================================== --}}

@include('components/sidebar-beranda', [
    'linkBackButton' => url()->previous(),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'PROTA dan PROSEM',

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

    $currentRole = strtolower(
        trim(
            (string) (
                optional($user)->role ?? ''
            )
        )
    );


    /* =====================================================================
       DEFAULT DATA
       ===================================================================== */

    $teacherMapels = collect();

    $jumlahGuru = 0;
    $jumlahMapel = 0;

    $mapelGroups = collect();
    $protaDocuments = collect();


    /* =====================================================================
       ROLE YANG DIIZINKAN
       ===================================================================== */

    $allowedRoles = [
        'guru',
        'admin sekolah',
        'kepala sekolah',
        'wakil kepala sekolah',
    ];


    /* =====================================================================
       FUNGSI NAMA MAPEL
       ===================================================================== */

    $getMapelName = function ($mapel) {
        if (!$mapel) {
            return 'Mata Pelajaran';
        }

        return
            $mapel->nama_mapel
            ?? $mapel->mata_pelajaran
            ?? $mapel->nama
            ?? $mapel->name
            ?? 'Mata Pelajaran';
    };


    /* =====================================================================
       AMBIL TEACHER MAPEL

       TeacherMapel BUKAN sumber jumlah guru/file.

       TeacherMapel digunakan untuk:
       - mengetahui guru yang mengajar mapel
       - mencari mapel asli
       - fallback apabila AcademicDocument mempunyai mapel_id lama/salah
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
             * Fallback apabila relasi SchoolClass bermasalah.
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
       FILTER SEKOLAH PADA FALLBACK TEACHER MAPEL
       ===================================================================== */

    if (
        $schoolId &&
        $teacherMapels->isNotEmpty()
    ) {
        $teacherMapels = $teacherMapels
            ->filter(function ($teacherMapel) use ($schoolId) {

                $schoolClass =
                    $teacherMapel->SchoolClass ?? null;

                /*
                 * Jika SchoolClass tidak tersedia,
                 * jangan langsung membuang data.
                 */
                if (!$schoolClass) {
                    return true;
                }

                return (int) (
                    $schoolClass->school_partner_id ?? 0
                ) === (int) $schoolId;
            })
            ->values();
    }


    /* =====================================================================
       AMBIL ACADEMIC DOCUMENT

       AcademicDocument adalah SUMBER UTAMA.

       Hanya:
       - sekolah ini
       - document_type = prota_prosem
       - file_path tidak null
       - file_path tidak kosong
       ===================================================================== */

    if ($schoolId) {
        try {

            $documentQuery = \App\Models\AcademicDocument::query()
                ->where(
                    'school_partner_id',
                    $schoolId
                )
                ->where(
                    'document_type',
                    'prota_prosem'
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
               GURU

               Guru hanya melihat dokumennya sendiri.
               ============================================================= */

            if (
                $user &&
                $currentRole === 'guru'
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
                }
            }


            $protaDocuments = $documentQuery
                ->latest('id')
                ->get();

        } catch (\Throwable $e) {

            /*
             * Fallback apabila document_type belum tersedia
             * atau database masih menggunakan struktur lama.
             */

            try {

                $documentQuery = \App\Models\AcademicDocument::query()
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
                        '%prota-prosem%'
                    );


                if (
                    $user &&
                    $currentRole === 'guru'
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
                    }
                }


                $protaDocuments = $documentQuery
                    ->latest('id')
                    ->get();

            } catch (\Throwable $fallbackError) {

                $protaDocuments = collect();
            }
        }
    }


    /* =====================================================================
       FUNGSI OWNER DOKUMEN

       Prioritas:
       1. owner_user_id
       2. uploaded_by
       3. created_by
       ===================================================================== */

    $getDocumentOwnerId = function ($document) {

        if (
            isset($document->owner_user_id) &&
            !empty($document->owner_user_id)
        ) {
            return (int) $document->owner_user_id;
        }

        if (
            isset($document->uploaded_by) &&
            !empty($document->uploaded_by)
        ) {
            return (int) $document->uploaded_by;
        }

        if (
            isset($document->created_by) &&
            !empty($document->created_by)
        ) {
            return (int) $document->created_by;
        }

        return null;
    };


    /* =====================================================================
       FUNGSI MAPEL ID MENTAH DARI DOKUMEN

       Prioritas:
       1. subject_id
       2. mapel_id

       CATATAN:
       Nilai ini TIDAK langsung dipercaya sebagai ID mapel.
       Akan diverifikasi terhadap tabel Mapel dan TeacherMapel.
       ===================================================================== */

    $getRawDocumentMapelId = function ($document) {

        if (
            isset($document->subject_id) &&
            !empty($document->subject_id)
        ) {
            return (int) $document->subject_id;
        }

        if (
            isset($document->mapel_id) &&
            !empty($document->mapel_id)
        ) {
            return (int) $document->mapel_id;
        }

        return null;
    };


    /* =====================================================================
       NORMALISASI DOKUMEN

       Pastikan hanya dokumen yang benar-benar punya:
       - owner
       - file_path
       ===================================================================== */

    $protaDocuments = $protaDocuments
        ->filter(function ($document) use (
            $getDocumentOwnerId
        ) {

            $ownerId =
                $getDocumentOwnerId($document);

            return
                !empty($ownerId) &&
                !empty($document->file_path);
        })
        ->values();


    /* =====================================================================
       LOAD MAPEL YANG VALID

       Kita ambil seluruh ID mapel yang muncul di AcademicDocument,
       lalu cek apakah benar-benar ada pada tabel mapels.
       ===================================================================== */

    $rawMapelIds = $protaDocuments
        ->map(function ($document) use (
            $getRawDocumentMapelId
        ) {
            return $getRawDocumentMapelId($document);
        })
        ->filter(function ($id) {
            return !empty($id);
        })
        ->unique()
        ->values();


    $mapels = collect();

    if ($rawMapelIds->isNotEmpty()) {

        try {

            $mapels = \App\Models\Mapel::query()
                ->whereIn(
                    'id',
                    $rawMapelIds->all()
                )
                ->get();

        } catch (\Throwable $e) {

            $mapels = collect();
        }
    }


    /* =====================================================================
       INDEX MAPEL VALID

       Contoh:
       [
           7 => Mapel Bahasa Indonesia,
           9 => Mapel Bahasa Inggris
       ]
       ===================================================================== */

    $validMapelById = $mapels->keyBy('id');


    /* =====================================================================
       INDEX TEACHER MAPEL

       Struktur:

       owner_user_id
       =>
       daftar mapel asli milik guru tersebut

       Ini penting untuk memperbaiki data lama seperti:

       AcademicDocument:
       mapel_id = 0
       mapel_id = 1

       tetapi TeacherMapel menunjukkan guru sebenarnya mengajar:
       mapel_id = 7
       atau
       mapel_id = 9
       ===================================================================== */

    $teacherMapelByUser = $teacherMapels
        ->groupBy(function ($teacherMapel) {
            return (int) (
                $teacherMapel->user_id ?? 0
            );
        });


    /* =====================================================================
       RESOLVE MAPEL ASLI DOKUMEN

       URUTAN:
       1. subject_id/mapel_id jika benar-benar ada di tabel Mapel
       2. Cek apakah mapel tersebut sesuai TeacherMapel owner
       3. Jika tidak valid, cari dari TeacherMapel owner
       4. Jika masih tidak ditemukan, abaikan dokumen dari grouping

       Dengan ini mapel_id 0/1 tidak akan otomatis dianggap
       sebagai index array.
       ===================================================================== */

    $resolvedDocuments = collect();


    foreach ($protaDocuments as $document) {

        $ownerId =
            $getDocumentOwnerId($document);

        if (!$ownerId) {
            continue;
        }


        $rawMapelId =
            $getRawDocumentMapelId($document);


        $resolvedMapelId = null;
        $resolvedMapel = null;


        /* ================================================================
           STEP 1
           Cek ID dokumen apakah benar-benar ada di tabel Mapel.
           ================================================================ */

        if (
            $rawMapelId &&
            $validMapelById->has($rawMapelId)
        ) {

            $candidateMapel =
                $validMapelById->get($rawMapelId);


            /*
             * Cek TeacherMapel milik owner.
             *
             * Jika owner memang mengajar mapel tersebut,
             * maka mapel ini sangat valid.
             */

            $ownerTeacherMapels =
                $teacherMapelByUser->get(
                    (int) $ownerId,
                    collect()
                );


            $isAssignedToOwner =
                $ownerTeacherMapels->contains(
                    function ($teacherMapel) use (
                        $rawMapelId
                    ) {

                        return (int) (
                            $teacherMapel->mapel_id ?? 0
                        ) === (int) $rawMapelId;
                    }
                );


            /*
             * Jika guru tidak mempunyai TeacherMapel,
             * tetap izinkan mapel valid dari dokumen.
             *
             * Jika guru punya TeacherMapel,
             * prioritaskan yang memang sesuai penugasan.
             */

            if (
                $ownerTeacherMapels->isEmpty() ||
                $isAssignedToOwner
            ) {

                $resolvedMapelId =
                    (int) $rawMapelId;

                $resolvedMapel =
                    $candidateMapel;
            }
        }


        /* ================================================================
           STEP 2
           Jika mapel dari dokumen tidak valid,
           cari dari TeacherMapel owner.
           ================================================================ */

        if (!$resolvedMapelId) {

            $ownerTeacherMapels =
                $teacherMapelByUser->get(
                    (int) $ownerId,
                    collect()
                );


            /*
             * Ambil TeacherMapel yang memiliki Mapel.
             */

            $ownerTeacherMapel =
                $ownerTeacherMapels
                    ->first(function ($teacherMapel) {

                        return
                            !empty(
                                $teacherMapel->mapel_id
                            ) &&
                            !empty(
                                $teacherMapel->Mapel
                            );
                    });


            if ($ownerTeacherMapel) {

                $resolvedMapelId =
                    (int) $ownerTeacherMapel->mapel_id;

                $resolvedMapel =
                    $ownerTeacherMapel->Mapel;
            }
        }


        /* ================================================================
           STEP 3
           Jika masih tidak ditemukan, cari berdasarkan mapel_id
           TeacherMapel walaupun relasi Mapel tidak eager-load.
           ================================================================ */

        if (!$resolvedMapelId) {

            $ownerTeacherMapels =
                $teacherMapelByUser->get(
                    (int) $ownerId,
                    collect()
                );


            $ownerTeacherMapel =
                $ownerTeacherMapels
                    ->first(function ($teacherMapel) {

                        return !empty(
                            $teacherMapel->mapel_id
                        );
                    });


            if ($ownerTeacherMapel) {

                $candidateId =
                    (int) $ownerTeacherMapel->mapel_id;


                try {

                    $candidateMapel =
                        \App\Models\Mapel::find(
                            $candidateId
                        );

                } catch (\Throwable $e) {

                    $candidateMapel = null;
                }


                if ($candidateMapel) {

                    $resolvedMapelId =
                        $candidateId;

                    $resolvedMapel =
                        $candidateMapel;
                }
            }
        }


        /* ================================================================
           DOKUMEN BERHASIL DI-RESOLVE
           ================================================================ */

        if ($resolvedMapelId) {

            $resolvedDocuments->push([
                'document' =>
                    $document,

                'owner_id' =>
                    (int) $ownerId,

                'mapel_id' =>
                    (int) $resolvedMapelId,

                'mapel' =>
                    $resolvedMapel,
            ]);
        }
    }


    /* =====================================================================
       JUMLAH MAPEL

       Hanya mapel yang benar-benar mempunyai file.
       ===================================================================== */

    $documentMapelIds = $resolvedDocuments
        ->pluck('mapel_id')
        ->filter()
        ->unique()
        ->values();


    $jumlahMapel =
        $documentMapelIds->count();


    /* =====================================================================
       JUMLAH GURU

       SANGAT PENTING:

       Tidak menggunakan TeacherMapel.

       Yang dihitung adalah owner dokumen.

       Contoh:

       Guru A upload Bahasa Inggris
       Guru B punya TeacherMapel Bahasa Inggris
       tetapi belum upload.

       Hasil:
       Guru = 1
       ===================================================================== */

    $documentOwnerIds = $resolvedDocuments
        ->pluck('owner_id')
        ->filter()
        ->unique()
        ->values();


    $jumlahGuru =
        $documentOwnerIds->count();


    /* =====================================================================
       KELOMPOKKAN DOKUMEN BERDASARKAN MAPEL

       KEY = ID MAPEL ASLI

       BUKAN:
       $loop->index

       BUKAN:
       nomor urutan card.
       ===================================================================== */

    foreach ($documentMapelIds as $documentMapelId) {

        $documentsResolved =
            $resolvedDocuments
                ->filter(function ($item) use (
                    $documentMapelId
                ) {

                    return
                        (int) $item['mapel_id']
                        ===
                        (int) $documentMapelId;
                })
                ->values();


        $documents =
            $documentsResolved
                ->pluck('document')
                ->values();


        $documentTeachers =
            $documentsResolved
                ->pluck('owner_id')
                ->filter()
                ->unique()
                ->values();


        $mapel =
            $documentsResolved
                ->pluck('mapel')
                ->filter()
                ->first();


        /*
         * Jika mapel belum ditemukan dari resolved document,
         * coba cari dari Mapel asli.
         */

        if (!$mapel) {

            $mapel =
                $validMapelById->get(
                    (int) $documentMapelId
                );
        }


        /*
         * TeacherMapel hanya sebagai informasi tambahan.
         */

        $teachers =
            $teacherMapels
                ->filter(function ($teacherMapel) use (
                    $documentMapelId
                ) {

                    return
                        (int) (
                            $teacherMapel->mapel_id ?? 0
                        )
                        ===
                        (int) $documentMapelId;
                })
                ->filter(function ($teacherMapel) use (
                    $documentTeachers
                ) {

                    return $documentTeachers->contains(
                        (int) (
                            $teacherMapel->user_id ?? 0
                        )
                    );
                })
                ->values();


        /* ================================================================
           SIMPAN GROUP
           ================================================================ */

        $mapelGroups->put(
            (int) $documentMapelId,
            [
                /*
                 * ID MAPEL ASLI.
                 */
                'mapel_id' =>
                    (int) $documentMapelId,

                'mapel' =>
                    $mapel,

                'teachers' =>
                    $teachers,

                'documentTeachers' =>
                    $documentTeachers,

                'documents' =>
                    $documents,

                /*
                 * JUMLAH GURU = OWNER DOKUMEN.
                 */
                'jumlahGuru' =>
                    $documentTeachers->count(),

                /*
                 * JUMLAH FILE = JUMLAH DOKUMEN.
                 */
                'jumlahDokumen' =>
                    $documents->count(),
            ]
        );
    }


    /* =====================================================================
       SORT MAPEL
       ===================================================================== */

    $mapelGroups = $mapelGroups
        ->sortBy(function ($group) {

            $mapel =
                $group['mapel'] ?? null;

            if (!$mapel) {
                return '';
            }

            return strtolower(
                (string) (
                    $mapel->nama_mapel
                    ?? $mapel->mata_pelajaran
                    ?? $mapel->nama
                    ?? $mapel->name
                    ?? ''
                )
            );
        })
        ->values();

@endphp


{{-- ========================================================================
     AKSES
     ======================================================================== --}}

@if (
    $user &&
    in_array(
        $currentRole,
        $allowedRoles,
        true
    )
)

    <div
    class="relative w-full md:ml-[280px] md:w-[calc(100%-280px)] transition-all duration-500 ease-in-out z-20"
>

        <div class="my-15 mx-7.5">

            <main>

                {{-- =========================================================
                     HEADER PROTA DAN PROSEM
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

                        <div class="flex items-center gap-4">

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
                                    Program Tahunan dan Program Semester
                                </h1>

                                <p
                                    class="text-sm text-gray-500"
                                >
                                    Kelola dokumen PROTA dan PROSEM guru
                                </p>

                            </div>

                        </div>


                        {{-- =================================================
                             INFORMASI GURU DAN MAPEL
                             ================================================= --}}

                        <div class="flex gap-4">

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

                    <div class="mb-6">

                        <h2
                            class="text-lg font-bold text-gray-800"
                        >
                            Mata Pelajaran
                        </h2>

                        <p
                            class="text-sm text-gray-500 mt-1"
                        >
                            Pilih mata pelajaran yang sudah memiliki file
                            PROTA dan PROSEM.
                        </p>

                    </div>


                    {{-- =====================================================
                         JIKA ADA MAPEL
                         ===================================================== --}}

                    @if ($mapelGroups->isNotEmpty())

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5"
                        >

                            @foreach (
                                $mapelGroups as $group
                            )

                                @php

                                    /*
                                     * =================================================
                                     * ID MAPEL ASLI
                                     *
                                     * PENTING:
                                     * Tidak menggunakan:
                                     * $loop->index
                                     *
                                     * Tidak menggunakan:
                                     * nomor urutan card
                                     * =================================================
                                     */

                                    $mapelId =
                                        (int) (
                                            $group['mapel_id']
                                            ?? 0
                                        );


                                    $mapel =
                                        $group['mapel']
                                        ?? null;


                                    $teachers =
                                        $group['teachers']
                                        ?? collect();


                                    $documentTeachers =
                                        $group['documentTeachers']
                                        ?? collect();


                                    $documents =
                                        $group['documents']
                                        ?? collect();


                                    /*
                                     * Nama mapel.
                                     */

                                    $mapelName =
                                        $getMapelName(
                                            $mapel
                                        );


                                    /*
                                     * Jumlah guru WAJIB berasal
                                     * dari owner dokumen.
                                     */

                                    $jumlahGuruMapel =
                                        $group['jumlahGuru']
                                        ?? $documentTeachers->count();


                                    /*
                                     * Jumlah dokumen.
                                     */

                                    $jumlahDokumen =
                                        $group['jumlahDokumen']
                                        ?? $documents->count();


                                    /*
                                     * Dokumen terbaru.
                                     */

                                    $latestDocument =
                                        $documents->first();


                                    /*
                                     * =================================================
                                     * URL DETAIL
                                     *
                                     * WAJIB menggunakan ID MAPEL ASLI.
                                     *
                                     * Contoh:
                                     *
                                     * Bahasa Indonesia:
                                     * ?mapel_id=7
                                     *
                                     * Bahasa Inggris:
                                     * ?mapel_id=9
                                     *
                                     * BUKAN:
                                     * ?mapel_id=0
                                     * ?mapel_id=1
                                     * =================================================
                                     */

                                    $protaUrl = '#';

                                    if (
                                        $schoolId &&
                                        $mapelId > 0
                                    ) {

                                        try {

                                            $protaUrl = route(
                                                'lms.schoolAdmin.registrasiGuru.prota-prosem.tampilan',
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
                                             * Query parameter sengaja
                                             * ditambahkan manual.
                                             *
                                             * Karena mapel_id bukan
                                             * parameter path route.
                                             */

                                            $separator =
                                                str_contains(
                                                    $protaUrl,
                                                    '?'
                                                )
                                                ? '&'
                                                : '?';


                                            $protaUrl .=
                                                $separator .
                                                'mapel_id=' .
                                                urlencode(
                                                    (string) $mapelId
                                                );

                                        } catch (
                                            \Throwable $routeError
                                        ) {

                                            $protaUrl = '#';
                                        }
                                    }

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
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332 0-4.5 1.253"
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
                                        Program Tahunan & Program Semester
                                    </p>


                                    {{-- STATUS FILE --}}

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
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0"
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
                                         JUMLAH DOKUMEN
                                         ================================================= --}}

                                    @if ($jumlahDokumen > 0)

                                        <div
                                            class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-600"
                                        >

                                            <i
                                                class="fa-solid fa-file-excel text-xs"
                                            ></i>

                                            <span
                                                class="text-xs font-semibold"
                                            >
                                                {{ $jumlahDokumen }}
                                                File
                                            </span>

                                        </div>

                                    @endif


                                    {{-- =================================================
                                         FILE TERBARU
                                         ================================================= --}}

                                    @if ($latestDocument)

                                        <p
                                            class="text-[11px] text-gray-400 mt-3 truncate"
                                            title="{{ $latestDocument->original_filename ?? '' }}"
                                        >

                                            <i
                                                class="fa-solid fa-file-excel mr-1"
                                            ></i>

                                            {{
                                                $latestDocument->original_filename
                                                ?? 'File PROTA dan PROSEM'
                                            }}

                                        </p>

                                    @endif


                                    {{-- =================================================
                                         BUTTON
                                         ================================================= --}}

                                    <div
                                        class="border-t border-dashed border-gray-200 mt-5 pt-4"
                                    >

                                        @if ($protaUrl !== '#')

                                            <a
                                                href="{{ $protaUrl }}"
                                                class="inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:text-blue-800 transition"
                                            >

                                                Lihat Detail

                                                <i
                                                    class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"
                                                ></i>

                                            </a>

                                        @else

                                            <span
                                                class="inline-flex items-center gap-2 text-gray-400 text-sm font-semibold cursor-not-allowed"
                                            >

                                                Detail tidak tersedia

                                            </span>

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        </div>


                    @else

                        {{-- =================================================
                             BELUM ADA FILE PROTA / PROSEM
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
                                Belum Ada File PROTA dan PROSEM
                            </h3>


                            <p
                                class="text-sm text-gray-500 mt-1 max-w-md mx-auto"
                            >
                                Belum terdapat file Program Tahunan dan
                                Program Semester yang diupload pada
                                sekolah ini.
                            </p>


                            <p
                                class="text-xs text-gray-400 mt-3"
                            >
                                Menunggu upload dari guru
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

        <div class="text-center">

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