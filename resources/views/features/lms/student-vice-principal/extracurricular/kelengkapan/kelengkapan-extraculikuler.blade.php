@include('components/sidebar-beranda', [
    'headerSideNav' => 'Ekstrakurikuler'
])

@if(Auth::user()->role == 'Wakil Kesiswaan' || Auth::user()->role == 'Admin')

<div
    class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]
    transition-all duration-500 ease-in-out z-20"
>

   <div class="mx-6 my-8">

    <div id="alert-success"></div>

    <main
        id="container"

        data-kpi-url="{{ route(
            'lms.student-vice-principal.extracurricular-management.kelengkapan.kpi',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
            ]
        ) }}"

        data-extracurricular='@json($extracurriculars)'

        data-comment-urls='@json(
            $extracurriculars->mapWithKeys(function ($item) use ($role, $schoolName, $schoolId) {
                return [
                    $item->id => url(
                        '/lms/' .
                        rawurlencode($role) . '/' .
                        rawurlencode($schoolName) . '/' .
                        $schoolId .
                        '/extracurricular-management/' .
                        $item->id .
                        '/kelengkapan/comment'
                    )
                ];
            })->toArray()
        )'
    >

            {{-- =========================================================
                 HEADER
            ========================================================== --}}

            <section class="mt-8">

                <div
                    class="
                        bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)]
                        rounded-3xl
                        p-8
                        text-white
                        shadow-xl
                        overflow-hidden
                        relative
                    "
                >

                    <div
                        class="
                            absolute
                            right-0
                            top-0
                            opacity-10
                        "
                    >
                        <i
                            class="
                                fa-solid
                                fa-users
                                text-[220px]
                                translate-x-8
                                -translate-y-4
                            "
                        ></i>
                    </div>


                    <div
                        class="
                            relative
                            z-10
                            flex
                            flex-col
                            lg:flex-row
                            lg:items-center
                            lg:justify-between
                            gap-5
                        "
                    >

                        <div>

                            <h1 class="text-3xl font-bold">
                                Manajemen Ekstrakurikuler
                            </h1>

                            <p
                                class="
                                    mt-2
                                    text-slate-300
                                    max-w-3xl
                                "
                            >
                                Kelola seluruh ekstrakurikuler sekolah.
                                Setiap ekstrakurikuler memiliki halaman administrasi sendiri
                                yang berisi Silabus, PROTA, PROSEM, RPP,
                                daftar peserta serta absensi.
                            </p>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                 KPI
            ========================================================== --}}

            <section class="mt-8">

                <div id="kpi-content">

                    <div
                        class="
                            grid
                            grid-cols-1
                            lg:grid-cols-3
                            gap-5
                        "
                    >

                        {{-- =================================================
                             TOTAL EKSTRAKURIKULER
                        ================================================== --}}

                        <div
                            class="
                                bg-linear-to-br
                                from-blue-50
                                to-white
                                border
                                border-blue-200
                                rounded-3xl
                                p-6
                            "
                        >

                            <div
                                class="
                                    flex
                                    justify-between
                                    items-start
                                "
                            >

                                <div>

                                    <p
                                        class="
                                            text-sm
                                            text-slate-500
                                        "
                                    >
                                        Jumlah Ekstrakurikuler
                                    </p>

                                    <h3
                                        id="total-extracurricular"
                                        class="
                                            text-3xl
                                            font-black
                                            mt-2
                                            text-blue-700
                                        "
                                    >
                                        {{ $extracurriculars->count() }}
                                    </h3>

                                    <p
                                        class="
                                            text-xs
                                            text-slate-500
                                            mt-2
                                        "
                                    >
                                        Total ekstrakurikuler aktif sekolah
                                    </p>

                                </div>


                                <div
                                    class="
                                        w-14
                                        h-14
                                        rounded-2xl
                                        bg-blue-100
                                        flex
                                        items-center
                                        justify-center
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-users
                                            text-blue-600
                                            text-xl
                                        "
                                    ></i>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             SUDAH LENGKAP
                        ================================================== --}}

                        <button
                            type="button"
                            onclick="openKelengkapanModal('complete')"
                            class="
                                text-left
                                bg-linear-to-br
                                from-green-50
                                to-white
                                border
                                border-green-200
                                rounded-3xl
                                p-6
                                hover:shadow-lg
                                hover:-translate-y-0.5
                                transition
                                cursor-pointer
                            "
                        >

                            <div
                                class="
                                    flex
                                    justify-between
                                    items-start
                                "
                            >

                                <div>

                                    <p
                                        class="
                                            text-sm
                                            text-slate-500
                                        "
                                    >
                                        Sudah Lengkap
                                    </p>

                                    <h3
                                        id="total-complete"
                                        class="
                                            text-3xl
                                            font-black
                                            mt-2
                                            text-green-700
                                        "
                                    >
                                        {{
                                            $extracurriculars->filter(function ($item) {
                                                return $item->kelengkapan
                                                    && $item->kelengkapan->is_complete;
                                            })->count()
                                        }}
                                    </h3>

                                    <p
                                        class="
                                            text-xs
                                            text-green-600
                                            mt-2
                                        "
                                    >
                                        Klik untuk melihat ekstrakurikuler lengkap
                                    </p>

                                </div>


                                <div
                                    class="
                                        w-14
                                        h-14
                                        rounded-2xl
                                        bg-green-100
                                        flex
                                        items-center
                                        justify-center
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-circle-check
                                            text-green-600
                                            text-xl
                                        "
                                    ></i>

                                </div>

                            </div>

                        </button>


                        {{-- =================================================
                             BELUM LENGKAP
                        ================================================== --}}

                        <button
                            type="button"
                            onclick="openKelengkapanModal('incomplete')"
                            class="
                                text-left
                                bg-linear-to-br
                                from-red-50
                                to-white
                                border
                                border-red-200
                                rounded-3xl
                                p-6
                                hover:shadow-lg
                                hover:-translate-y-0.5
                                transition
                                cursor-pointer
                            "
                        >

                            <div
                                class="
                                    flex
                                    justify-between
                                    items-start
                                "
                            >

                                <div>

                                    <p
                                        class="
                                            text-sm
                                            text-slate-500
                                        "
                                    >
                                        Belum Lengkap
                                    </p>

                                    <h3
                                        id="total-incomplete"
                                        class="
                                            text-3xl
                                            font-black
                                            mt-2
                                            text-red-700
                                        "
                                    >
                                        {{
                                            $extracurriculars->filter(function ($item) {
                                                return !$item->kelengkapan
                                                    || !$item->kelengkapan->is_complete;
                                            })->count()
                                        }}
                                    </h3>

                                    <p
                                        class="
                                            text-xs
                                            text-red-600
                                            mt-2
                                        "
                                    >
                                        Klik untuk melihat ekstrakurikuler belum lengkap
                                    </p>

                                </div>


                                <div
                                    class="
                                        w-14
                                        h-14
                                        rounded-2xl
                                        bg-red-100
                                        flex
                                        items-center
                                        justify-center
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-circle-xmark
                                            text-red-600
                                            text-xl
                                        "
                                    ></i>

                                </div>

                            </div>

                        </button>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                 LIST EKSTRAKURIKULER
            ========================================================== --}}

            <section class="mt-8">

                <div
                    class="
                        bg-white
                        border
                        border-slate-200
                        rounded-3xl
                        overflow-hidden
                        shadow-sm
                    "
                >

                    {{-- =================================================
                         HEADER TABLE
                    ================================================== --}}

                    <div
                        class="
                            p-6
                            border-b
                            border-slate-200
                            flex
                            flex-col
                            lg:flex-row
                            lg:items-center
                            lg:justify-between
                            gap-4
                        "
                    >

                        <div>

                            <h2 class="text-xl font-bold">
                                Daftar Ekstrakurikuler
                            </h2>

                            <p class="text-sm text-slate-500">
                                Pilih salah satu ekstrakurikuler untuk membuka
                                halaman detail administrasi.
                            </p>

                        </div>


                        <div class="flex gap-3">

                            <label
                                class="
                                    input
                                    input-bordered
                                    outline-none
                                    border-gray-300
                                    flex
                                    items-center
                                    gap-2
                                    w-40
                                    sm:w-66
                                    md:w-max
                                "
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 opacity-70"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z"
                                    />
                                </svg>

                                <input
                                    id="search_extracurricular"
                                    type="search"
                                    class="
                                        grow
                                        text-sm
                                    "
                                    placeholder="Cari ekstrakurikuler..."
                                    autocomplete="off"
                                >

                            </label>

                        </div>

                    </div>


                    {{-- =================================================
                         SKELETON
                    ================================================== --}}

                    <div class="overflow-x-auto">

                        <div
                            id="extracurricular-list-skeleton"
                            class="hidden"
                        >

                            <table class="min-w-full">

                                <tbody>

                                    @for($i = 0; $i < 7; $i++)

                                        <tr
                                            class="
                                                animate-pulse
                                                border-t
                                                border-slate-100
                                            "
                                        >

                                            <td class="p-4">

                                                <div class="space-y-2">

                                                    <div
                                                        class="
                                                            h-4
                                                            w-48
                                                            bg-slate-200
                                                            rounded
                                                        "
                                                    ></div>

                                                    <div
                                                        class="
                                                            h-3
                                                            w-24
                                                            bg-slate-100
                                                            rounded
                                                        "
                                                    ></div>

                                                </div>

                                            </td>


                                            <td class="p-4">

                                                <div
                                                    class="
                                                        h-4
                                                        w-24
                                                        bg-slate-200
                                                        rounded
                                                        mx-auto
                                                    "
                                                ></div>

                                            </td>


                                            <td class="p-4">

                                                <div
                                                    class="
                                                        h-4
                                                        w-16
                                                        bg-slate-200
                                                        rounded
                                                        mx-auto
                                                    "
                                                ></div>

                                            </td>


                                            <td class="p-4">

                                                <div
                                                    class="
                                                        h-4
                                                        w-16
                                                        bg-slate-200
                                                        rounded
                                                        mx-auto
                                                    "
                                                ></div>

                                            </td>


                                            <td class="p-4">

                                                <div
                                                    class="
                                                        h-6
                                                        w-20
                                                        bg-slate-200
                                                        rounded-full
                                                        mx-auto
                                                    "
                                                ></div>

                                            </td>


                                            <td class="p-4">

                                                <div
                                                    class="
                                                        h-10
                                                        w-24
                                                        bg-slate-200
                                                        rounded-xl
                                                        mx-auto
                                                    "
                                                ></div>

                                            </td>

                                        </tr>

                                    @endfor

                                </tbody>

                            </table>

                        </div>


                        {{-- =================================================
                             TABLE
                        ================================================== --}}

                        <table
                            id="table-extracurricular-list"
                            class="
                                min-w-full
                                border-separate
                                border-spacing-y-5
                            "
                        >

                            <thead class="bg-slate-50">

                                <tr class="text-slate-600">

                                    <th class="p-4 text-left">
                                        Ekstrakurikuler
                                    </th>

                                    <th
                                        class="p-4 text-center"
                                        width="160"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="p-4 text-center"
                                        width="150"
                                    >
                                        Detail
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($extracurriculars as $item)

                                    @php

                                        $kelengkapan =
                                            $item->kelengkapan;

                                        $totalDocument =
                                            $kelengkapan?->total_document ?? 0;

                                        $isComplete =
                                            $totalDocument === 4;

                                    @endphp


                                    <tr
                                        class="
                                            bg-white
                                            shadow-sm
                                            hover:shadow-md
                                            transition
                                        "
                                    >

                                        {{-- =================================================
                                             EKSTRAKURIKULER
                                        ================================================== --}}

                                        <td class="p-6 align-top">

                                            <div class="flex gap-4">

                                                <div
                                                    class="
                                                        w-14
                                                        h-14
                                                        rounded-2xl
                                                        bg-slate-100
                                                        flex
                                                        items-center
                                                        justify-center
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-users
                                                            text-2xl
                                                            text-blue-600
                                                        "
                                                    ></i>

                                                </div>


                                                <div class="flex-1">

                                                    <h3
                                                        class="
                                                            text-lg
                                                            font-bold
                                                            text-slate-800
                                                        "
                                                    >
                                                        {{ $item->name }}
                                                    </h3>


                                                    <p
                                                        class="
                                                            text-sm
                                                            text-slate-500
                                                        "
                                                    >
                                                        {{
                                                            $item->description
                                                            ?? 'Ekstrakurikuler sekolah.'
                                                        }}
                                                    </p>


                                                    {{-- =========================================
                                                         DOKUMEN
                                                    ========================================== --}}

                                                    <div
                                                        class="
                                                            flex
                                                            flex-wrap
                                                            gap-3
                                                            mt-5
                                                        "
                                                    >

                                                        {{-- SILABUS --}}
                                                        <span
                                                            class="
                                                                px-4
                                                                py-2
                                                                rounded-xl
                                                                {{
                                                                    $kelengkapan?->silabus
                                                                        ? 'bg-green-50 text-green-700'
                                                                        : 'bg-red-50 text-red-700'
                                                                }}
                                                            "
                                                        >

                                                            <i
                                                                class="
                                                                    fa-solid
                                                                    {{
                                                                        $kelengkapan?->silabus
                                                                            ? 'fa-circle-check'
                                                                            : 'fa-circle-xmark'
                                                                    }}
                                                                    mr-1
                                                                "
                                                            ></i>

                                                            Silabus

                                                        </span>


                                                        {{-- PROTA --}}
                                                        <span
                                                            class="
                                                                px-4
                                                                py-2
                                                                rounded-xl
                                                                {{
                                                                    $kelengkapan?->prota
                                                                        ? 'bg-green-50 text-green-700'
                                                                        : 'bg-red-50 text-red-700'
                                                                }}
                                                            "
                                                        >

                                                            <i
                                                                class="
                                                                    fa-solid
                                                                    {{
                                                                        $kelengkapan?->prota
                                                                            ? 'fa-circle-check'
                                                                            : 'fa-circle-xmark'
                                                                    }}
                                                                    mr-1
                                                                "
                                                            ></i>

                                                            PROTA

                                                        </span>


                                                        {{-- PROSEM --}}
                                                        <span
                                                            class="
                                                                px-4
                                                                py-2
                                                                rounded-xl
                                                                {{
                                                                    $kelengkapan?->prosem
                                                                        ? 'bg-green-50 text-green-700'
                                                                        : 'bg-red-50 text-red-700'
                                                                }}
                                                            "
                                                        >

                                                            <i
                                                                class="
                                                                    fa-solid
                                                                    {{
                                                                        $kelengkapan?->prosem
                                                                            ? 'fa-circle-check'
                                                                            : 'fa-circle-xmark'
                                                                    }}
                                                                    mr-1
                                                                "
                                                            ></i>

                                                            PROSEM

                                                        </span>


                                                        {{-- RPP --}}
                                                        <span
                                                            class="
                                                                px-4
                                                                py-2
                                                                rounded-xl
                                                                {{
                                                                    $kelengkapan?->rpp
                                                                        ? 'bg-green-50 text-green-700'
                                                                        : 'bg-red-50 text-red-700'
                                                                }}
                                                            "
                                                        >

                                                            <i
                                                                class="
                                                                    fa-solid
                                                                    {{
                                                                        $kelengkapan?->rpp
                                                                            ? 'fa-circle-check'
                                                                            : 'fa-circle-xmark'
                                                                    }}
                                                                    mr-1
                                                                "
                                                            ></i>

                                                            RPP

                                                        </span>

                                                    </div>


                                                    {{-- =========================================
                                                         KOMENTAR
                                                    ========================================== --}}

                                                    <div class="mt-6">

                                                        <label
                                                            class="
                                                                text-xs
                                                                font-semibold
                                                                text-blue-600
                                                                mb-2
                                                                block
                                                            "
                                                        >
                                                            Komentar Wakil Kesiswaan
                                                        </label>


                                                        <textarea
    rows="2"
    data-extracurricular-id="${escapeAttribute(item.id)}"
    class="
        kelengkapan-comment
        w-full
        bg-slate-50
        rounded-xl
        border-0
        resize-none
        focus:outline-none
        focus:ring-2
        focus:ring-blue-300
    "
    placeholder="Tulis komentar..."
>${escapeHtml(kelengkapan?.comment || '')}</textarea>

<div
    class="comment-status text-xs text-slate-400 mt-1"
    data-status-for="${escapeAttribute(item.id)}"
>
    Perubahan akan tersimpan otomatis.
</div>


                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- =================================================
                                             STATUS
                                        ================================================== --}}

                                        <td
                                            class="
                                                w-44
                                                text-center
                                                align-top
                                                pt-8
                                            "
                                        >

                                            @if($isComplete)

                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        px-5
                                                        py-2
                                                        rounded-full
                                                        bg-green-100
                                                        text-green-700
                                                        font-semibold
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-circle-check
                                                            mr-2
                                                        "
                                                    ></i>

                                                    Lengkap

                                                </span>

                                            @else

                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        px-5
                                                        py-2
                                                        rounded-full
                                                        bg-red-100
                                                        text-red-700
                                                        font-semibold
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-circle-xmark
                                                            mr-2
                                                        "
                                                    ></i>

                                                    Belum Lengkap

                                                </span>

                                            @endif


                                            <div
                                                class="
                                                    mt-3
                                                    text-sm
                                                    text-slate-500
                                                "
                                            >
                                                {{ $totalDocument }} / 4 Dokumen
                                            </div>

                                        </td>


                                        {{-- =================================================
                                             DETAIL
                                        ================================================== --}}

                                        <td
                                            class="
                                                w-44
                                                text-center
                                                align-top
                                                pt-8
                                            "
                                        >

                                            <a
                                                href="{{
                                                    route(
                                                        'lms.student-vice-principal.extracurricular-management.kelengkapan.detail',
                                                        [
                                                            'role' => $role,
                                                            'schoolName' => $schoolName,
                                                            'schoolId' => $schoolId,
                                                            'extracurricularId' => $item->id,
                                                        ]
                                                    )
                                                }}"
                                                class="
                                                    inline-flex
                                                    items-center
                                                    gap-2
                                                    px-6
                                                    py-3
                                                    rounded-xl
                                                    bg-blue-600
                                                    hover:bg-blue-700
                                                    text-white
                                                    transition
                                                "
                                            >

                                                <i
                                                    class="
                                                        fa-solid
                                                        fa-arrow-up-right-from-square
                                                    "
                                                ></i>

                                                Detail

                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="py-16 text-center"
                                        >

                                            <div
                                                class="
                                                    flex
                                                    flex-col
                                                    items-center
                                                    justify-center
                                                "
                                            >

                                                <div
                                                    class="
                                                        w-20
                                                        h-20
                                                        rounded-full
                                                        bg-blue-100
                                                        flex
                                                        items-center
                                                        justify-center
                                                        mb-4
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-users
                                                            text-3xl
                                                            text-blue-500
                                                        "
                                                    ></i>

                                                </div>


                                                <h4
                                                    class="
                                                        text-lg
                                                        font-bold
                                                        text-slate-700
                                                    "
                                                >
                                                    Belum Ada Ekstrakurikuler
                                                </h4>


                                                <p
                                                    class="
                                                        text-sm
                                                        text-slate-500
                                                        max-w-md
                                                        mt-2
                                                    "
                                                >
                                                    Belum ada ekstrakurikuler
                                                    yang ditambahkan untuk sekolah ini.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    {{-- =================================================
                         PAGINATION
                    ================================================== --}}

                    <div
                        class="
                            pagination-container-extracurricular-list
                            flex
                            justify-center
                            py-5
                        "
                    >
                    </div>


                    {{-- =================================================
                         EMPTY STATE
                    ================================================== --}}

                    <div
                        id="empty-message-extracurricular-list"
                        class="
                            hidden
                            h-96
                            bg-slate-50
                            rounded-2xl
                            border
                            border-dashed
                            border-slate-200
                        "
                    >

                        <div
                            class="
                                flex
                                flex-col
                                items-center
                                justify-center
                                h-full
                                px-6
                            "
                        >

                            <div
                                class="
                                    w-20
                                    h-20
                                    rounded-full
                                    bg-blue-100
                                    flex
                                    items-center
                                    justify-center
                                    mb-4
                                "
                            >

                                <i
                                    class="
                                        fas
                                        fa-users
                                        text-3xl
                                        text-blue-500
                                    "
                                ></i>

                            </div>


                            <h4
                                class="
                                    text-lg
                                    font-bold
                                    text-slate-700
                                    text-center
                                "
                            >
                                Belum Ada Ekstrakurikuler
                            </h4>


                            <p
                                class="
                                    text-sm
                                    text-slate-500
                                    text-center
                                    max-w-md
                                    mt-2
                                "
                            >
                                Tambahkan ekstrakurikuler terlebih dahulu agar
                                anggota dan absensi dapat dikelola.
                            </p>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                 MODAL KPI KELENGKAPAN
            ========================================================== --}}

            <dialog
                id="modal_kelengkapan_kpi"
                class="modal"
            >

                <div
                    class="
                        modal-box
                        max-w-2xl
                        w-11/12
                        rounded-3xl
                        p-0
                        overflow-hidden
                    "
                >

                    {{-- =================================================
                         HEADER MODAL
                    ================================================== --}}

                    <div
                        id="kelengkapan-modal-header"
                        class="
                            p-6
                            text-white
                        "
                        style="
                            background: linear-gradient(
                                90deg,
                                #16a34a 0%,
                                #22c55e 100%
                            );
                        "
                    >

                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-4
                            "
                        >

                            {{-- TITLE --}}
                            <div class="min-w-0">

                                <div
                                    class="
                                        flex
                                        items-center
                                        gap-3
                                    "
                                >

                                    {{-- ICON --}}
                                    <div
                                        id="kelengkapan-modal-icon"
                                        class="
                                            w-12
                                            h-12
                                            shrink-0
                                            rounded-2xl
                                            flex
                                            items-center
                                            justify-center
                                        "
                                        style="
                                            background: rgba(
                                                255,
                                                255,
                                                255,
                                                0.20
                                            );
                                        "
                                    >

                                        <i
                                            id="kelengkapan-modal-icon-symbol"
                                            class="
                                                fa-solid
                                                fa-circle-check
                                                text-xl
                                            "
                                        ></i>

                                    </div>


                                    {{-- TEXT --}}
                                    <div class="min-w-0">

                                        <h2
                                            id="kelengkapan-modal-title"
                                            class="
                                                text-2xl
                                                font-black
                                                text-white
                                            "
                                        >
                                            Sudah Lengkap
                                        </h2>


                                        <p
                                            id="kelengkapan-modal-description"
                                            class="
                                                text-sm
                                                text-white/80
                                                mt-1
                                            "
                                        >
                                            Daftar ekstrakurikuler yang sudah
                                            memiliki seluruh dokumen administrasi.
                                        </p>

                                    </div>

                                </div>

                            </div>


                            {{-- CLOSE --}}
                            <button
                                type="button"
                                onclick="closeKelengkapanModal()"
                                class="
                                    w-10
                                    h-10
                                    shrink-0
                                    rounded-xl
                                    flex
                                    items-center
                                    justify-center
                                    text-white
                                    transition
                                "
                                style="
                                    background: rgba(
                                        255,
                                        255,
                                        255,
                                        0.20
                                    );
                                "
                                onmouseover="
                                    this.style.background =
                                    'rgba(255,255,255,0.30)'
                                "
                                onmouseout="
                                    this.style.background =
                                    'rgba(255,255,255,0.20)'
                                "
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-xmark
                                        text-lg
                                    "
                                ></i>

                            </button>

                        </div>

                    </div>


                    {{-- =================================================
                         CONTENT
                    ================================================== --}}

                    <div class="p-6">

                        <div
                            id="kelengkapan-modal-count"
                            class="
                                text-sm
                                font-semibold
                                text-slate-500
                                mb-4
                            "
                        >
                            0 ekstrakurikuler
                        </div>


                        <div
                            id="kelengkapan-modal-list"
                            class="
                                space-y-3
                                max-h-[55vh]
                                overflow-y-auto
                                pr-1
                            "
                        >
                        </div>

                    </div>


                    {{-- =================================================
                         FOOTER
                    ================================================== --}}

                    <div
                        class="
                            px-6
                            py-4
                            bg-slate-50
                            border-t
                            border-slate-200
                            flex
                            justify-end
                        "
                    >

                        <button
                            type="button"
                            onclick="closeKelengkapanModal()"
                            class="
                                px-5
                                py-2.5
                                rounded-xl
                                bg-slate-200
                                hover:bg-slate-300
                                text-slate-700
                                font-semibold
                                transition
                            "
                        >
                            Tutup
                        </button>

                    </div>

                </div>


                {{-- =================================================
                     BACKDROP
                ================================================== --}}

                <form
                    method="dialog"
                    class="modal-backdrop"
                >
                    <button>close</button>
                </form>

            </dialog>

        </main>

    </div>

</div>

@endif
    

{{-- ================= JAVASCRIPT ================= --}}
<script>
(function () {

    'use strict';

    /* =========================================================
       KELENGKAPAN EKSTRAKURIKULER
       FULL JAVASCRIPT
    ========================================================= */

    document.addEventListener('DOMContentLoaded', function () {

        /* =====================================================
           ELEMENT UTAMA
        ===================================================== */

        const containerElement =
            document.getElementById('container');

        if (!containerElement) {
            console.error(
                '[Kelengkapan] #container tidak ditemukan.'
            );
            return;
        }


        /* =====================================================
           CONFIG
        ===================================================== */

        const kpiUrl =
            containerElement.dataset.kpiUrl || '';

        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '';


        /* =====================================================
           DATA EKSTRAKURIKULER
        ===================================================== */

        let extracurricularData = [];

        try {

            extracurricularData = JSON.parse(
                containerElement.dataset.extracurricular || '[]'
            );

            if (!Array.isArray(extracurricularData)) {
                extracurricularData = [];
            }

        } catch (error) {

            console.error(
                '[Kelengkapan] Gagal membaca data ekstrakurikuler:',
                error
            );

            extracurricularData = [];

        }


        let extracurricularList = [
            ...extracurricularData
        ];


        /* =====================================================
           COMMENT URL
        ===================================================== */

        let commentUrls = {};

        try {

            commentUrls = JSON.parse(
                containerElement.dataset.commentUrls || '{}'
            );

            if (
                !commentUrls ||
                typeof commentUrls !== 'object'
            ) {
                commentUrls = {};
            }

        } catch (error) {

            console.error(
                '[Kelengkapan] Gagal membaca comment URLs:',
                error
            );

            commentUrls = {};

        }


        /* =====================================================
           STATE
        ===================================================== */

        let kelengkapanKpiData = null;

        let currentPage = 1;

        const ITEMS_PER_PAGE = 5;


        /* =====================================================
           DOM
        ===================================================== */

        const searchInput =
            document.getElementById(
                'search_extracurricular'
            );


        const table =
            document.getElementById(
                'table-extracurricular-list'
            );


        const tableBody =
            table
                ? table.querySelector('tbody')
                : null;


        const paginationContainer =
            document.querySelector(
                '.pagination-container-extracurricular-list'
            );


        const emptyMessage =
            document.getElementById(
                'empty-message-extracurricular-list'
            );


        /* =====================================================
           MODAL
        ===================================================== */

        const modal =
            document.getElementById(
                'modal_kelengkapan_kpi'
            );


        const modalHeader =
            document.getElementById(
                'kelengkapan-modal-header'
            );


        const modalTitle =
            document.getElementById(
                'kelengkapan-modal-title'
            );


        const modalDescription =
            document.getElementById(
                'kelengkapan-modal-description'
            );


        const modalCount =
            document.getElementById(
                'kelengkapan-modal-count'
            );


        const modalList =
            document.getElementById(
                'kelengkapan-modal-list'
            );


        /* =====================================================
           UTILITY
        ===================================================== */

        function escapeHtml(value) {

            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }


        function escapeAttribute(value) {

            return escapeHtml(value)
                .replace(/`/g, '&#096;');

        }


        /* =====================================================
           COMMENT URL
        ===================================================== */

        function getCommentUrl(extracurricularId) {

            const id =
                String(
                    extracurricularId ?? ''
                );

            return commentUrls[id] || '';

        }


        /* =====================================================
           DETAIL URL
           
           Tidak menggunakan route() di JavaScript.
           Menghindari error Blade route/template.
        ===================================================== */

        function getDetailUrl(extracurricularId) {

            const id =
                encodeURIComponent(
                    extracurricularId
                );

            /*
             * URL saat ini:
             *
             * /lms/{role}/{schoolName}/{schoolId}
             *
             * Kita ambil dari URL halaman sekarang.
             */

            const currentPath =
                window.location.pathname;


            /*
             * Buang bagian:
             *
             * /extracurricular-management/kelengkapan
             */

            const base =
                currentPath.replace(
                    /\/extracurricular-management\/kelengkapan\/?$/,
                    ''
                );


            return (
                base +
                '/extracurricular-management/kelengkapan/' +
                id
            );

        }


        /* =====================================================
           DOCUMENT STATUS
        ===================================================== */

        function getDocumentStatus(
            kelengkapan,
            field
        ) {

            if (!kelengkapan) {
                return false;
            }

            const value =
                kelengkapan[field];

            return Boolean(
                value !== null &&
                value !== undefined &&
                value !== '' &&
                value !== 0 &&
                value !== false
            );

        }


        /* =====================================================
           TOTAL DOCUMENT
        ===================================================== */

        function getTotalDocument(
            kelengkapan
        ) {

            if (!kelengkapan) {
                return 0;
            }

            let total = 0;

            [
                'silabus',
                'prota',
                'prosem',
                'rpp'
            ].forEach(function (field) {

                if (
                    getDocumentStatus(
                        kelengkapan,
                        field
                    )
                ) {
                    total++;
                }

            });

            return total;

        }


        /* =====================================================
           COMPLETE STATUS
        ===================================================== */

        function isComplete(
            extracurricular
        ) {

            const kelengkapan =
                extracurricular?.kelengkapan;


            if (
                kelengkapan &&
                typeof kelengkapan.is_complete !== 'undefined'
            ) {

                return Boolean(
                    kelengkapan.is_complete
                );

            }


            return (
                getTotalDocument(
                    kelengkapan
                ) === 4
            );

        }


        /* =====================================================
           DOCUMENT BADGE
        ===================================================== */

        function documentBadge(
            kelengkapan,
            field,
            label
        ) {

            const exists =
                getDocumentStatus(
                    kelengkapan,
                    field
                );


            return `
                <span
                    class="
                        inline-flex
                        items-center
                        gap-1.5
                        px-4
                        py-2
                        rounded-xl
                        text-sm
                        font-medium
                        ${
                            exists
                                ? 'bg-green-50 text-green-700'
                                : 'bg-red-50 text-red-700'
                        }
                    "
                >
                    <i
                        class="
                            fa-solid
                            ${
                                exists
                                    ? 'fa-circle-check'
                                    : 'fa-circle-xmark'
                            }
                        "
                    ></i>

                    ${label}
                </span>
            `;

        }


        /* =====================================================
           RENDER TABLE
        ===================================================== */

        function renderExtracurricularTable(
            data
        ) {

            if (!tableBody) {

                console.error(
                    '[Kelengkapan] tbody tabel tidak ditemukan.'
                );

                return;

            }


            if (!Array.isArray(data)) {
                data = [];
            }


            const totalItems =
                data.length;


            const totalPages =
                Math.max(
                    1,
                    Math.ceil(
                        totalItems /
                        ITEMS_PER_PAGE
                    )
                );


            if (
                currentPage < 1
            ) {
                currentPage = 1;
            }


            if (
                currentPage > totalPages
            ) {
                currentPage = totalPages;
            }


            const startIndex =
                (
                    currentPage - 1
                ) *
                ITEMS_PER_PAGE;


            const endIndex =
                startIndex +
                ITEMS_PER_PAGE;


            const pageItems =
                data.slice(
                    startIndex,
                    endIndex
                );


            /* =================================================
               EMPTY
            ================================================= */

            if (!pageItems.length) {

                tableBody.innerHTML = `

                    <tr>
                        <td
                            colspan="3"
                            class="py-16 text-center"
                        >

                            <div
                                class="
                                    flex
                                    flex-col
                                    items-center
                                    justify-center
                                "
                            >

                                <div
                                    class="
                                        w-20
                                        h-20
                                        rounded-full
                                        bg-blue-100
                                        flex
                                        items-center
                                        justify-center
                                        mb-4
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-users
                                            text-3xl
                                            text-blue-500
                                        "
                                    ></i>

                                </div>


                                <h4
                                    class="
                                        text-lg
                                        font-bold
                                        text-slate-700
                                    "
                                >
                                    ${
                                        searchInput?.value?.trim()
                                            ? 'Ekstrakurikuler tidak ditemukan'
                                            : 'Belum Ada Ekstrakurikuler'
                                    }
                                </h4>


                                <p
                                    class="
                                        text-sm
                                        text-slate-500
                                        max-w-md
                                        mt-2
                                    "
                                >
                                    ${
                                        searchInput?.value?.trim()
                                            ? 'Tidak ada ekstrakurikuler yang sesuai dengan pencarian.'
                                            : 'Belum ada ekstrakurikuler yang ditambahkan untuk sekolah ini.'
                                    }
                                </p>

                            </div>

                        </td>
                    </tr>

                `;


                if (emptyMessage) {
                    emptyMessage.classList.add('hidden');
                }


                renderPagination(data);

                return;

            }


            if (emptyMessage) {
                emptyMessage.classList.add('hidden');
            }


            /* =================================================
               ROW
            ================================================= */

            tableBody.innerHTML =
                pageItems
                    .map(function (item) {

                        const kelengkapan =
                            item.kelengkapan || null;


                        const totalDocument =
                            getTotalDocument(
                                kelengkapan
                            );


                        const complete =
                            isComplete(
                                item
                            );


                        const itemId =
                            item.id ?? '';


                        const detailUrl =
                            getDetailUrl(
                                itemId
                            );


                        const comment =
                            kelengkapan?.comment || '';


                        const canComment =
                            item.can_comment !== false;


                        return `

                            <tr
                                class="
                                    bg-white
                                    shadow-sm
                                    hover:shadow-md
                                    transition
                                "
                            >

                                <!-- EKSTRAKURIKULER -->

                                <td
                                    class="
                                        p-6
                                        align-top
                                    "
                                >

                                    <div
                                        class="
                                            flex
                                            gap-4
                                        "
                                    >

                                        <div
                                            class="
                                                w-14
                                                h-14
                                                rounded-2xl
                                                bg-slate-100
                                                flex
                                                items-center
                                                justify-center
                                                shrink-0
                                            "
                                        >

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-users
                                                    text-2xl
                                                    text-blue-600
                                                "
                                            ></i>

                                        </div>


                                        <div
                                            class="
                                                flex-1
                                                min-w-0
                                            "
                                        >

                                            <h3
                                                class="
                                                    text-lg
                                                    font-bold
                                                    text-slate-800
                                                "
                                            >
                                                ${escapeHtml(
                                                    item.name
                                                )}
                                            </h3>


                                            <p
                                                class="
                                                    text-sm
                                                    text-slate-500
                                                    mt-1
                                                "
                                            >
                                                ${escapeHtml(
                                                    item.description ||
                                                    'Ekstrakurikuler sekolah.'
                                                )}
                                            </p>


                                            <!-- DOKUMEN -->

                                            <div
                                                class="
                                                    flex
                                                    flex-wrap
                                                    gap-3
                                                    mt-5
                                                "
                                            >

                                                ${documentBadge(
                                                    kelengkapan,
                                                    'silabus',
                                                    'Silabus'
                                                )}

                                                ${documentBadge(
                                                    kelengkapan,
                                                    'prota',
                                                    'PROTA'
                                                )}

                                                ${documentBadge(
                                                    kelengkapan,
                                                    'prosem',
                                                    'PROSEM'
                                                )}

                                                ${documentBadge(
                                                    kelengkapan,
                                                    'rpp',
                                                    'RPP'
                                                )}

                                            </div>


                                            <!-- KOMENTAR -->

                                            <div
                                                class="mt-6"
                                            >

                                                <label
                                                    class="
                                                        text-xs
                                                        font-semibold
                                                        text-blue-600
                                                        mb-2
                                                        block
                                                    "
                                                >
                                                    Komentar Wakil Kesiswaan
                                                </label>


                                                <textarea
                                                    rows="2"
                                                    data-extracurricular-id="${escapeAttribute(
                                                        itemId
                                                    )}"
                                                    class="
                                                        kelengkapan-comment
                                                        w-full
                                                        bg-slate-50
                                                        rounded-xl
                                                        border-0
                                                        resize-none
                                                        focus:outline-none
                                                        ${
                                                            !canComment
                                                                ? 'cursor-not-allowed text-slate-500'
                                                                : 'focus:ring-2 focus:ring-blue-300'
                                                        }
                                                    "
                                                    placeholder="Belum ada komentar..."
                                                    ${
                                                        !canComment
                                                            ? 'readonly'
                                                            : ''
                                                    }
                                                >${escapeHtml(
                                                    comment
                                                )}</textarea>


                                                ${
                                                    canComment
                                                        ? `
                                                            <div
                                                                class="
                                                                    comment-status
                                                                    text-xs
                                                                    text-slate-400
                                                                    mt-1
                                                                "
                                                                data-status-for="${escapeAttribute(
                                                                    itemId
                                                                )}"
                                                            >
                                                                Perubahan akan tersimpan otomatis.
                                                            </div>
                                                        `
                                                        : ''
                                                }

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- STATUS -->

                                <td
                                    class="
                                        w-44
                                        text-center
                                        align-top
                                        pt-8
                                    "
                                >

                                    ${
                                        complete

                                            ? `

                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        px-5
                                                        py-2
                                                        rounded-full
                                                        bg-green-100
                                                        text-green-700
                                                        font-semibold
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-circle-check
                                                            mr-2
                                                        "
                                                    ></i>

                                                    Lengkap

                                                </span>

                                            `

                                            : `

                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        px-5
                                                        py-2
                                                        rounded-full
                                                        bg-red-100
                                                        text-red-700
                                                        font-semibold
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-circle-xmark
                                                            mr-2
                                                        "
                                                    ></i>

                                                    Belum Lengkap

                                                </span>

                                            `
                                    }


                                    <div
                                        class="
                                            mt-3
                                            text-sm
                                            text-slate-500
                                        "
                                    >
                                        ${totalDocument} / 4 Dokumen
                                    </div>

                                </td>


                                <!-- DETAIL -->

                                <td
                                    class="
                                        w-44
                                        text-center
                                        align-top
                                        pt-8
                                    "
                                >

                                    <a
                                        href="${escapeAttribute(
                                            detailUrl
                                        )}"
                                        class="
                                            inline-flex
                                            items-center
                                            gap-2
                                            px-6
                                            py-3
                                            rounded-xl
                                            bg-blue-600
                                            hover:bg-blue-700
                                            text-white
                                            transition
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-arrow-up-right-from-square
                                            "
                                        ></i>

                                        Detail

                                    </a>

                                </td>

                            </tr>

                        `;

                    })
                    .join('');


            bindCommentInputs();

            renderPagination(data);

        }


        /* =====================================================
           PAGINATION
        ===================================================== */

        function renderPagination(
            data
        ) {

            if (!paginationContainer) {
                return;
            }


            if (!Array.isArray(data)) {
                data = [];
            }


            const totalPages =
                Math.ceil(
                    data.length /
                    ITEMS_PER_PAGE
                );


            if (totalPages <= 1) {

                paginationContainer.innerHTML = '';

                return;

            }


            let html = '';


            /* PREVIOUS */

            html += `

                <button
                    type="button"
                    data-page="${currentPage - 1}"
                    class="
                        w-10
                        h-10
                        rounded-xl
                        flex
                        items-center
                        justify-center
                        border
                        transition
                        ${
                            currentPage === 1
                                ? 'text-slate-300 border-slate-200 cursor-not-allowed'
                                : 'text-slate-600 border-slate-200 hover:bg-slate-100'
                        }
                    "
                    ${
                        currentPage === 1
                            ? 'disabled'
                            : ''
                    }
                >
                    <i
                        class="fa-solid fa-chevron-left text-xs"
                    ></i>
                </button>

            `;


            /* PAGE */

            for (
                let page = 1;
                page <= totalPages;
                page++
            ) {

                html += `

                    <button
                        type="button"
                        data-page="${page}"
                        class="
                            w-10
                            h-10
                            rounded-xl
                            flex
                            items-center
                            justify-center
                            font-semibold
                            transition
                            ${
                                page === currentPage
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'border border-slate-200 text-slate-600 hover:bg-slate-100'
                            }
                        "
                    >
                        ${page}
                    </button>

                `;

            }


            /* NEXT */

            html += `

                <button
                    type="button"
                    data-page="${currentPage + 1}"
                    class="
                        w-10
                        h-10
                        rounded-xl
                        flex
                        items-center
                        justify-center
                        border
                        transition
                        ${
                            currentPage === totalPages
                                ? 'text-slate-300 border-slate-200 cursor-not-allowed'
                                : 'text-slate-600 border-slate-200 hover:bg-slate-100'
                        }
                    "
                    ${
                        currentPage === totalPages
                            ? 'disabled'
                            : ''
                    }
                >
                    <i
                        class="fa-solid fa-chevron-right text-xs"
                    ></i>
                </button>

            `;


            paginationContainer.innerHTML =
                html;


            paginationContainer
                .querySelectorAll(
                    'button[data-page]'
                )
                .forEach(function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const page =
                                Number(
                                    this.dataset.page
                                );


                            if (
                                !Number.isInteger(page) ||
                                page < 1 ||
                                page > totalPages
                            ) {
                                return;
                            }


                            currentPage =
                                page;


                            renderExtracurricularTable(
                                extracurricularList
                            );

                        }
                    );

                });

        }


        /* =====================================================
           SEARCH
        ===================================================== */

        if (searchInput) {

            searchInput.addEventListener(
                'input',
                function () {

                    const keyword =
                        this.value
                            .toLowerCase()
                            .trim();


                    extracurricularList =
                        extracurricularData.filter(
                            function (item) {

                                const name =
                                    String(
                                        item.name || ''
                                    ).toLowerCase();


                                const coach =
                                    String(
                                        item.coach || ''
                                    ).toLowerCase();


                                const description =
                                    String(
                                        item.description || ''
                                    ).toLowerCase();


                                return (
                                    name.includes(keyword) ||
                                    coach.includes(keyword) ||
                                    description.includes(keyword)
                                );

                            }
                        );


                    currentPage = 1;


                    renderExtracurricularTable(
                        extracurricularList
                    );

                }
            );

        }


        /* =====================================================
           KPI
        ===================================================== */

        async function loadKelengkapanKpi() {

            if (!kpiUrl) {

                console.error(
                    '[Kelengkapan] URL KPI tidak ditemukan.'
                );

                return null;

            }


            try {

                const response =
                    await fetch(
                        kpiUrl,
                        {
                            method: 'GET',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            credentials:
                                'same-origin'
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        `HTTP ${response.status}`
                    );

                }


                const data =
                    await response.json();


                kelengkapanKpiData =
                    data;


                updateKelengkapanKpi();


                return data;

            } catch (error) {

                console.error(
                    '[Kelengkapan] Gagal load KPI:',
                    error
                );

                return null;

            }

        }


        /* =====================================================
           UPDATE KPI
        ===================================================== */

        function updateKelengkapanKpi() {

            if (!kelengkapanKpiData) {
                return;
            }


            const totalElement =
                document.getElementById(
                    'total-extracurricular'
                );


            const completeElement =
                document.getElementById(
                    'total-complete'
                );


            const incompleteElement =
                document.getElementById(
                    'total-incomplete'
                );


            const total =
                Number(
                    kelengkapanKpiData.total ?? 0
                );


            const complete =
                Array.isArray(
                    kelengkapanKpiData.complete
                )
                    ? kelengkapanKpiData.complete.length
                    : Number(
                        kelengkapanKpiData.complete ?? 0
                    );


            const incomplete =
                Array.isArray(
                    kelengkapanKpiData.incomplete
                )
                    ? kelengkapanKpiData.incomplete.length
                    : Number(
                        kelengkapanKpiData.incomplete ?? 0
                    );


            if (totalElement) {
                totalElement.textContent =
                    total;
            }


            if (completeElement) {
                completeElement.textContent =
                    complete;
            }


            if (incompleteElement) {
                incompleteElement.textContent =
                    incomplete;
            }

        }


        /* =====================================================
           MODAL HEADER
        ===================================================== */

        function setModalHeaderColor(
            type
        ) {

            if (!modalHeader) {
                return;
            }


            modalHeader.style.removeProperty(
                'background'
            );


            if (type === 'complete') {

                modalHeader.style.setProperty(
                    'background',
                    'linear-gradient(90deg, #15803d 0%, #22c55e 100%)',
                    'important'
                );

            } else {

                modalHeader.style.setProperty(
                    'background',
                    'linear-gradient(90deg, #b91c1c 0%, #ef4444 100%)',
                    'important'
                );

            }

        }


        /* =====================================================
           MODAL LOADING
        ===================================================== */

        function showModalLoading(
            type
        ) {

            const isComplete =
                type === 'complete';


            setModalHeaderColor(
                type
            );


            if (modalTitle) {

                modalTitle.textContent =
                    isComplete
                        ? 'Sudah Lengkap'
                        : 'Belum Lengkap';

            }


            if (modalDescription) {

                modalDescription.textContent =
                    'Memuat data ekstrakurikuler...';

            }


            if (modalCount) {

                modalCount.textContent =
                    'Memuat...';

            }


            if (modalList) {

                modalList.innerHTML = `

                    <div
                        class="
                            py-12
                            flex
                            flex-col
                            items-center
                            justify-center
                        "
                    >

                        <span
                            class="
                                loading
                                loading-spinner
                                loading-lg
                                ${
                                    isComplete
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                }
                            "
                        ></span>


                        <p
                            class="
                                text-sm
                                text-slate-500
                                mt-4
                            "
                        >
                            Memuat data...
                        </p>

                    </div>

                `;

            }

        }


        /* =====================================================
           RENDER MODAL
        ===================================================== */

        function renderKelengkapanModal(
            type,
            items
        ) {

            const isComplete =
                type === 'complete';


            setModalHeaderColor(
                type
            );


            if (modalTitle) {

                modalTitle.textContent =
                    isComplete
                        ? 'Sudah Lengkap'
                        : 'Belum Lengkap';

            }


            if (modalDescription) {

                modalDescription.textContent =
                    isComplete
                        ? 'Daftar ekstrakurikuler dengan seluruh dokumen administrasi lengkap.'
                        : 'Daftar ekstrakurikuler yang dokumen administrasinya belum lengkap.';

            }


            if (modalCount) {

                modalCount.textContent =
                    `${items.length} ekstrakurikuler`;

            }


            if (!modalList) {
                return;
            }


            if (!items.length) {

                modalList.innerHTML = `

                    <div
                        class="
                            py-12
                            text-center
                        "
                    >

                        <div
                            class="
                                w-16
                                h-16
                                mx-auto
                                rounded-full
                                ${
                                    isComplete
                                        ? 'bg-green-100'
                                        : 'bg-red-100'
                                }
                                flex
                                items-center
                                justify-center
                                mb-4
                            "
                        >

                            <i
                                class="
                                    fa-solid
                                    ${
                                        isComplete
                                            ? 'fa-circle-check text-green-600'
                                            : 'fa-circle-xmark text-red-600'
                                    }
                                    text-2xl
                                "
                            ></i>

                        </div>


                        <h4
                            class="
                                font-bold
                                text-slate-700
                            "
                        >

                            ${
                                isComplete
                                    ? 'Belum ada ekstrakurikuler yang lengkap.'
                                    : 'Semua ekstrakurikuler sudah lengkap.'
                            }

                        </h4>

                    </div>

                `;

                return;

            }


            modalList.innerHTML =
                items
                    .map(
                        function (item, index) {

                            return `

                                <div
                                    class="
                                        flex
                                        items-center
                                        gap-4
                                        p-4
                                        rounded-2xl
                                        border
                                        ${
                                            isComplete
                                                ? 'border-green-100 bg-green-50'
                                                : 'border-red-100 bg-red-50'
                                        }
                                    "
                                >

                                    <div
                                        class="
                                            w-10
                                            h-10
                                            rounded-xl
                                            bg-white
                                            flex
                                            items-center
                                            justify-center
                                            shrink-0
                                            shadow-sm
                                        "
                                    >

                                        <span
                                            class="
                                                font-bold
                                                ${
                                                    isComplete
                                                        ? 'text-green-700'
                                                        : 'text-red-700'
                                                }
                                            "
                                        >
                                            ${index + 1}
                                        </span>

                                    </div>


                                    <div
                                        class="
                                            flex-1
                                            min-w-0
                                        "
                                    >

                                        <p
                                            class="
                                                font-bold
                                                text-slate-800
                                                truncate
                                            "
                                        >
                                            ${escapeHtml(
                                                item.name
                                            )}
                                        </p>


                                        ${
                                            item.description
                                                ? `
                                                    <p
                                                        class="
                                                            text-xs
                                                            text-slate-500
                                                            truncate
                                                            mt-0.5
                                                        "
                                                    >
                                                        ${escapeHtml(
                                                            item.description
                                                        )}
                                                    </p>
                                                `
                                                : ''
                                        }

                                    </div>


                                    <div
                                        class="shrink-0"
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                ${
                                                    isComplete
                                                        ? 'fa-circle-check text-green-600'
                                                        : 'fa-circle-xmark text-red-600'
                                                }
                                                text-xl
                                            "
                                        ></i>

                                    </div>

                                </div>

                            `;

                        }
                    )
                    .join('');

        }


        /* =====================================================
           MODAL ERROR
        ===================================================== */

        function renderKelengkapanError() {

            setModalHeaderColor(
                'incomplete'
            );


            if (modalTitle) {

                modalTitle.textContent =
                    'Gagal Memuat Data';

            }


            if (modalDescription) {

                modalDescription.textContent =
                    'Terjadi kesalahan saat mengambil data kelengkapan.';

            }


            if (modalCount) {
                modalCount.textContent = '';
            }


            if (modalList) {

                modalList.innerHTML = `

                    <div
                        class="
                            py-12
                            text-center
                            text-red-500
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-triangle-exclamation
                                text-3xl
                                mb-3
                            "
                        ></i>


                        <p
                            class="
                                font-semibold
                                text-slate-700
                            "
                        >
                            Gagal mengambil data kelengkapan.
                        </p>


                        <p
                            class="
                                text-sm
                                text-slate-500
                                mt-1
                            "
                        >
                            Silakan coba lagi.
                        </p>

                    </div>

                `;

            }

        }


        /* =====================================================
           OPEN MODAL
           
           PENTING:
           Fungsi dibuat ke window supaya onclick=""
           di Blade bisa menemukannya.
        ===================================================== */

        window.openKelengkapanModal =
            async function (type) {

                if (
                    type !== 'complete' &&
                    type !== 'incomplete'
                ) {

                    type = 'incomplete';

                }


                if (!modal) {

                    console.error(
                        '[Kelengkapan] Modal tidak ditemukan.'
                    );

                    return;

                }


                showModalLoading(
                    type
                );


                if (
                    typeof modal.showModal ===
                    'function'
                ) {

                    modal.showModal();

                } else {

                    modal.setAttribute(
                        'open',
                        ''
                    );

                }


                const data =
                    await loadKelengkapanKpi();


                if (!data) {

                    renderKelengkapanError();

                    return;

                }


                const items =
                    type === 'complete'
                        ? (
                            Array.isArray(
                                data.complete
                            )
                                ? data.complete
                                : []
                        )
                        : (
                            Array.isArray(
                                data.incomplete
                            )
                                ? data.incomplete
                                : []
                        );


                renderKelengkapanModal(
                    type,
                    items
                );

            };


        /* =====================================================
           CLOSE MODAL
        ===================================================== */

        window.closeKelengkapanModal =
            function () {

                if (!modal) {
                    return;
                }


                if (
                    typeof modal.close ===
                    'function'
                ) {

                    modal.close();

                } else {

                    modal.removeAttribute(
                        'open'
                    );

                }

            };


        /* =====================================================
           AUTO SAVE COMMENT
        ===================================================== */

        function bindCommentInputs() {

            const commentInputs =
                document.querySelectorAll(
                    '.kelengkapan-comment'
                );


            commentInputs.forEach(
                function (textarea) {

                    if (
                        textarea.dataset.commentBound ===
                        'true'
                    ) {
                        return;
                    }


                    textarea.dataset.commentBound =
                        'true';


                    let saveTimer = null;


                    textarea.addEventListener(
                        'input',
                        function () {

                            const textareaElement =
                                this;


                            const extracurricularId =
                                textareaElement.dataset
                                    .extracurricularId;


                            const comment =
                                textareaElement.value;


                            const status =
                                document.querySelector(
                                    `[data-status-for="${extracurricularId}"]`
                                );


                            clearTimeout(
                                saveTimer
                            );


                            if (status) {

                                status.textContent =
                                    'Mengetik...';

                                status.className =
                                    'comment-status text-xs text-slate-400 mt-1';

                            }


                            /*
                             * Auto save setelah
                             * berhenti mengetik 700ms.
                             */

                            saveTimer =
                                setTimeout(
                                    async function () {

                                        await saveKelengkapanComment(
                                            extracurricularId,
                                            comment,
                                            textareaElement
                                        );

                                    },
                                    700
                                );

                        }
                    );

                }
            );

        }


        /* =====================================================
           SAVE COMMENT
        ===================================================== */

        async function saveKelengkapanComment(
            extracurricularId,
            comment,
            textarea
        ) {

            const url =
                getCommentUrl(
                    extracurricularId
                );


            if (!url) {

                console.error(
                    '[Kelengkapan] URL komentar tidak ditemukan:',
                    extracurricularId
                );

                return;

            }


            const status =
                document.querySelector(
                    `[data-status-for="${extracurricularId}"]`
                );


            if (status) {

                status.textContent =
                    'Menyimpan...';

                status.className =
                    'comment-status text-xs text-slate-400 mt-1';

            }


            try {

                const response =
                    await fetch(
                        url,
                        {
                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest'

                            },

                            credentials:
                                'same-origin',

                            body:
                                JSON.stringify({
                                    comment: comment
                                })
                        }
                    );


                let result = {};


                try {

                    result =
                        await response.json();

                } catch (error) {

                    result = {};

                }


                if (!response.ok) {

                    throw new Error(
                        result.message ||
                        `HTTP ${response.status}`
                    );

                }


                if (textarea) {

                    textarea.dataset.savedValue =
                        comment;

                }


                if (status) {

                    status.textContent =
                        '✓ Tersimpan otomatis';

                    status.className =
                        'comment-status text-xs text-green-500 mt-1';

                }


                /*
                 * Update data lokal juga.
                 */

                const item =
                    extracurricularData.find(
                        function (item) {

                            return String(item.id) ===
                                String(extracurricularId);

                        }
                    );


                if (
                    item &&
                    item.kelengkapan
                ) {

                    item.kelengkapan.comment =
                        comment;

                }


            } catch (error) {

                console.error(
                    '[Kelengkapan] Gagal menyimpan komentar:',
                    error
                );


                if (status) {

                    status.textContent =
                        'Gagal menyimpan';

                    status.className =
                        'comment-status text-xs text-red-500 mt-1';

                }

            }

        }


        /* =====================================================
           EXPOSE FUNCTION
        ===================================================== */

        window.renderExtracurricularTable =
            renderExtracurricularTable;


        /* =====================================================
           INITIAL RENDER
        ===================================================== */

        renderExtracurricularTable(
            extracurricularList
        );


        /* =====================================================
           INITIAL KPI
        ===================================================== */

        loadKelengkapanKpi();

    });

})();
</script>