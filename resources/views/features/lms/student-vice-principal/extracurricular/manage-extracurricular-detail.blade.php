@include('components/sidebar-beranda', [
    'headerSideNav' => 'Detail Ekstrakurikuler'
])

@if(Auth::user()->role == 'Wakil Kesiswaan' || Auth::user()->role == 'Admin')

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

    <div class="mx-6 my-8 space-y-8">

        <main
            id="container"
            data-role="{{ $role }}"
            data-school-name="{{ $schoolName }}"
            data-school-id="{{ $schoolId }}"
            data-extracurricular-id="{{ $extracurricular->id }}">

            {{-- ================= HEADER ================= --}}
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8">

    <div class="flex justify-between items-start gap-6">

        {{-- ================= KIRI ================= --}}
        <div class="min-w-0">

            <a
                href="{{ route(
                    'lms.student-vice-principal.extracurricular-management.view',
                    [
                        'role' => $role,
                        'schoolName' => $schoolName,
                        'schoolId' => $schoolId
                    ]
                ) }}"
                class="text-sm text-blue-600 hover:underline"
            >
                ← Kembali
            </a>

            <h1 class="text-3xl font-bold mt-2 text-slate-800">
                {{ $extracurricular->name }}
            </h1>

            <p class="text-slate-500 mt-1">
                {{ $extracurricular->description }}
            </p>

            <div class="mt-3 flex gap-2 flex-wrap">

                {{-- TIPE EKSKUL --}}
                @if($extracurricular->type == 'wajib')

                    <span class="badge badge-error">
                        WAJIB
                    </span>

                @else

                    <span class="badge badge-info">
                        PILIHAN
                    </span>

                @endif

                {{-- PEMBINA --}}
                <span class="badge badge-success">
                    Pembina :
                    {{ $extracurricular->coach ?: '-' }}
                </span>

            </div>

        </div>


        {{-- ================= KANAN ================= --}}
        <div class="flex items-center gap-3 shrink-0">

    {{-- ================= SEMESTER SEARCHABLE ================= --}}
    <div
        id="semesterDropdown"
        class="relative w-64"
    >

        {{-- TOMBOL DROPDOWN --}}
        <button
            type="button"
            id="semesterDropdownButton"
            class="
                w-full
                flex
                items-center
                justify-between
                gap-3
                px-4
                py-2.5
                bg-white
                border
                border-slate-300
                rounded-xl
                text-sm
                text-slate-700
                hover:border-slate-400
                focus:outline-none
                focus:ring-2
                focus:ring-primary/20
            "
        >

            <span
                id="semesterSelectedText"
                class="truncate text-left"
            >
                @if($selectedPeriod)
                    {{ $selectedPeriod->created_at
                        ? $selectedPeriod->created_at->translatedFormat('d F Y')
                        : 'Semester Aktif'
                    }}
                @else
                    Semester Aktif
                @endif
            </span>

            <i
                id="semesterDropdownIcon"
                class="fa-solid fa-chevron-down text-xs text-slate-400 shrink-0"
            ></i>

        </button>


        {{-- PANEL DROPDOWN --}}
        <div
            id="semesterDropdownPanel"
            class="
                hidden
                absolute
                z-[100]
                top-full
                left-0
                right-0
                mt-2
                bg-white
                border
                border-slate-200
                rounded-xl
                shadow-xl
                overflow-hidden
            "
        >

            {{-- SEARCH --}}
            <div class="p-2 border-b border-slate-100">

                <div class="relative">

                    <i
                        class="
                            fa-solid
                            fa-magnifying-glass
                            absolute
                            left-3
                            top-1/2
                            -translate-y-1/2
                            text-slate-400
                            text-sm
                        "
                    ></i>

                    <input
                        type="text"
                        id="semesterSearch"
                        placeholder="Cari semester..."
                        autocomplete="off"
                        class="
                            w-full
                            pl-9
                            pr-3
                            py-2
                            text-sm
                            bg-slate-50
                            border
                            border-slate-200
                            rounded-lg
                            outline-none
                            focus:border-primary
                            focus:ring-1
                            focus:ring-primary/20
                        "
                    >

                </div>

            </div>


            {{-- LIST SEMESTER --}}
            <div
                id="semesterOptions"
                class="
                    max-h-64
                    overflow-y-auto
                    p-1
                "
            >

                {{-- SEMESTER AKTIF --}}
                @if($activePeriod)

                    <button
                        type="button"
                        class="
                            semester-option
                            w-full
                            flex
                            items-center
                            justify-between
                            gap-3
                            px-3
                            py-2.5
                            rounded-lg
                            text-sm
                            text-left
                            hover:bg-slate-50
                            transition
                        "
                        data-value="{{ $activePeriod->id }}"
                        data-search="{{ strtolower(
                            $activePeriod->created_at
                                ? $activePeriod->created_at->translatedFormat('d F Y')
                                : 'semester aktif'
                        ) }}"
                    >

                        <span class="truncate">

                            {{ $activePeriod->created_at
                                ? $activePeriod->created_at->translatedFormat('d F Y')
                                : 'Semester Aktif'
                            }}

                        </span>

                        <span
                            class="
                                shrink-0
                                text-xs
                                px-2
                                py-1
                                rounded-md
                                bg-emerald-50
                                text-emerald-600
                            "
                        >
                            Aktif
                        </span>

                    </button>

                @endif


                {{-- SEMESTER LAIN / ARSIP --}}
                @foreach($semesters as $semester)

                    @if(
                        !$activePeriod ||
                        $semester->id != $activePeriod->id
                    )

                        <button
                            type="button"
                            class="
                                semester-option
                                w-full
                                flex
                                items-center
                                justify-between
                                gap-3
                                px-3
                                py-2.5
                                rounded-lg
                                text-sm
                                text-left
                                hover:bg-slate-50
                                transition
                            "
                            data-value="{{ $semester->id }}"
                            data-search="{{ strtolower(
                                $semester->created_at
                                    ? $semester->created_at->translatedFormat('d F Y')
                                    : '-'
                            ) }}"
                        >

                            <span class="truncate">

                                {{ $semester->created_at
                                    ? $semester->created_at->translatedFormat('d F Y')
                                    : '-'
                                }}

                            </span>

                            @if(!$semester->is_active)

                                <span
                                    class="
                                        shrink-0
                                        text-xs
                                        px-2
                                        py-1
                                        rounded-md
                                        bg-slate-100
                                        text-slate-500
                                    "
                                >
                                    Arsip
                                </span>

                            @endif

                        </button>

                    @endif

                @endforeach


                {{-- TIDAK DITEMUKAN --}}
                <div
                    id="semesterNoResult"
                    class="
                        hidden
                        px-3
                        py-6
                        text-center
                        text-sm
                        text-slate-400
                    "
                >
                    <i class="fa-solid fa-magnifying-glass mb-2"></i>

                    <div>
                        Semester tidak ditemukan
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- ================= STATUS ================= --}}
    @if($nilaiCycleLocked)

        <div
            class="
                px-3
                py-2
                rounded-xl
                bg-amber-50
                text-amber-700
                text-sm
                font-medium
                whitespace-nowrap
            "
        >
            <i class="fa-solid fa-lock mr-1"></i>
            Menunggu Upload Nilai
        </div>

    @else

        <div
            class="
                px-3
                py-2
                rounded-xl
                bg-emerald-50
                text-emerald-700
                text-sm
                font-medium
                whitespace-nowrap
            "
        >
            <i class="fa-solid fa-circle-check mr-1"></i>
            Semester Aktif
        </div>

    @endif

</div>

    </div>


    {{-- ================= ACTION BUTTON ================= --}}
    <div class="mt-6 flex justify-end gap-3">

        {{-- BULK PESERTA --}}
        <button
            type="button"
            onclick="modal_upload_member.showModal()"
            class="
                btn
                bg-blue-600
                hover:bg-blue-700
                text-white
                rounded-xl
                border-0
            "
            @if($nilaiCycleLocked)
                disabled
            @endif
        >
            <i class="fa-solid fa-file-arrow-up mr-2"></i>
            Bulk Upload Peserta
        </button>


        {{-- BULK ABSENSI --}}
        <button
            type="button"
            onclick="modal_upload_attendance.showModal()"
            class="
                btn
                bg-green-600
                hover:bg-green-700
                text-white
                rounded-xl
                border-0
            "
            @if($nilaiCycleLocked)
                disabled
            @endif
        >
            <i class="fa-solid fa-calendar-check mr-2"></i>
            Bulk Upload Absensi
        </button>


        {{-- TAMBAHKAN NILAI --}}
        <button
            type="button"
            id="btnTambahNilai"
            onclick="modalTambahNilai.showModal()"
            class="
                btn
                bg-purple-600
                hover:bg-purple-700
                text-white
                rounded-xl
                border-0
            "
        >
            <i class="fa-solid fa-file-excel mr-2"></i>
            Tambahkan Nilai
        </button>

    </div>

</div>

<br>

            {{-- ================= KPI ================= --}}

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

                    <p class="text-slate-500">

                        Jumlah Peserta

                    </p>

                    <h2
                        id="total-member"
                        class="text-4xl font-bold text-blue-600 mt-2">

                        {{ $members->count() }}

                    </h2>

                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

                    <p class="text-slate-500">

                        Jumlah Pertemuan

                    </p>

                    <h2
                        id="total-meeting"
                        class="text-4xl font-bold text-orange-500 mt-2">

                        {{ $meetings->count() }}

                    </h2>

                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

                    <p class="text-slate-500">

                        Tingkat Kehadiran

                    </p>

                    <h2
                        id="attendance-percent"
                        class="text-4xl font-bold text-green-600 mt-2">

                        {{ $attendancePercent ?? 0 }}%

                    </h2>

                </div>

            </div>
            <br>

            {{-- ================= PESERTA ================= --}}

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

    {{-- HEADER --}}
    <div class="flex items-center justify-between p-6 border-b border-slate-100">

        <div>
            <h2 class="text-xl font-bold text-slate-800">
                Absensi Peserta
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Centang kotak pada setiap pertemuan jika siswa hadir.
            </p>
        </div>

       <div class="flex items-center gap-3 shrink-0">

    <div
        id="attendanceSaveStatus"
        class="text-sm text-slate-400 flex items-center gap-2"
    >
        <i class="fa-solid fa-cloud text-slate-400"></i>
        Tersimpan otomatis
    </div>

</div>

    </div>


    {{-- TABLE WRAPPER --}}
    <div class="overflow-x-auto">

        <table class="w-max min-w-full border-collapse">

            {{-- ================= WIDTH KOLOM ================= --}}
            <colgroup>

                {{-- No --}}
                <col class="w-[64px]">

                {{-- Nama --}}
                <col class="w-[260px]">

                {{-- Kelas --}}
                <col class="w-[120px]">

                {{-- Tipe Kelas --}}
                <col class="w-[150px]">

                {{-- Absensi --}}
                <col class="w-[360px]">

                {{-- Nilai --}}
                <col class="w-[120px]">

                {{-- Deskripsi --}}
                <col class="w-[360px]">

                {{-- Hapus --}}
                <col class="w-[100px]">

            </colgroup>


            {{-- ================= HEADER TABLE ================= --}}

            <thead>

                <tr class="bg-slate-50">

                    {{-- NO --}}
                    <th
                        class="
                            sticky left-0 z-40
                            w-[64px] min-w-[64px]
                            bg-slate-50
                            border-b border-slate-200
                            text-center
                        "
                    >
                        No
                    </th>


                    {{-- NAMA --}}
                    <th
                        class="
                            sticky left-[64px] z-40
                            w-[260px] min-w-[260px]
                            bg-slate-50
                            border-b border-slate-200
                            text-left
                        "
                    >
                        Nama Siswa
                    </th>


                    {{-- KELAS --}}
                    <th
                        class="
                            sticky left-[324px] z-40
                            w-[120px] min-w-[120px]
                            bg-slate-50
                            border-b border-slate-200
                            text-center
                        "
                    >
                        Kelas
                    </th>


                    {{-- TIPE KELAS --}}
                    <th
                        class="
                            sticky left-[444px] z-40
                            w-[150px] min-w-[150px]
                            bg-slate-50
                            border-b border-slate-200
                            text-center
                        "
                    >
                        Tipe Kelas
                    </th>


                    {{-- ABSENSI --}}
                    <th
                        class="
                            w-[360px] min-w-[360px]
                            border-b border-slate-200
                            text-center
                        "
                    >
                        Absen
                    </th>


                    {{-- NILAI --}}
                    <th
                        class="
                            w-[120px] min-w-[120px]
                            border-b border-slate-200
                            text-center
                        "
                    >
                        Nilai
                    </th>


                    {{-- DESKRIPSI --}}
                    <th
                        class="
                            w-[360px] min-w-[360px]
                            border-b border-slate-200
                            text-left
                        "
                    >
                        Deskripsi
                    </th>


                    {{-- HAPUS --}}
                    <th
                        class="
                            w-[100px] min-w-[100px]
                            border-b border-slate-200
                            text-center
                        "
                    >
                        Hapus
                    </th>

                </tr>

            </thead>


            {{-- ================= BODY ================= --}}

            <tbody>

                @foreach($members as $i => $member)

                    <tr class="hover:bg-slate-50">

                        {{-- NO --}}
                        <td
                            class="
                                sticky left-0 z-30
                                w-[64px] min-w-[64px]
                                bg-white
                                border-b border-slate-100
                                text-center
                                font-medium
                            "
                        >
                            {{ $i + 1 }}
                        </td>


                        {{-- NAMA --}}
                        <td
                            class="
                                sticky left-[64px] z-30
                                w-[260px] min-w-[260px]
                                bg-white
                                border-b border-slate-100
                                font-medium
                                whitespace-nowrap
                            "
                        >
                            {{ $member->student_name }}
                        </td>


                        {{-- KELAS --}}
                        <td
                            class="
                                sticky left-[324px] z-30
                                w-[120px] min-w-[120px]
                                bg-white
                                border-b border-slate-100
                                text-center
                            "
                        >
                            {{ $member->kelas }}
                        </td>


                        {{-- TIPE KELAS --}}
                        <td
                            class="
                                sticky left-[444px] z-30
                                w-[150px] min-w-[150px]
                                bg-white
                                border-b border-slate-100
                                text-center
                                whitespace-nowrap
                            "
                        >
                            {{ $member->tipe_kelas ?: '-' }}
                        </td>


                        {{-- ================= ABSENSI ================= --}}

                        <td
                            class="
                                w-[360px] min-w-[360px]
                                border-b border-slate-100
                                px-4
                            "
                        >

                            <div
                                class="
                                    flex gap-4
                                    overflow-x-auto
                                    max-w-[340px]
                                    py-2
                                "
                            >

                                @foreach($meetings as $meeting)

                                    @php

                                        $attendance = $member
                                            ->attendances()
                                            ->where(
                                                'meeting_id',
                                                $meeting->id
                                            )
                                            ->first();

                                    @endphp


                                    <label
                                        class="
                                            relative
                                            flex
                                            flex-col
                                            items-center
                                            justify-center
                                            min-w-[52px]
                                            shrink-0
                                            cursor-pointer
                                            group
                                        "
                                    >

                                        {{-- PERTEMUAN --}}
                                        <span
                                            class="
                                                text-[11px]
                                                font-semibold
                                                text-slate-500
                                                mb-1
                                            "
                                        >
                                            P{{ $loop->iteration }}
                                        </span>


                                        {{-- CHECKBOX --}}
                                        <input
                                            type="checkbox"
                                            class="attendance-checkbox peer sr-only"

                                            data-student="{{ $member->student_profile_id }}"

                                            data-meeting="{{ $meeting->id }}"

                                            {{ $attendance && $attendance->status === 'present'
                                                ? 'checked'
                                                : ''
                                            }}
                                        >


                                        {{-- CHECK --}}
                                        <div
                                            class="
                                                w-8 h-8
                                                rounded-lg
                                                border-2
                                                border-slate-300
                                                bg-white
                                                flex
                                                items-center
                                                justify-center
                                                transition-all
                                                duration-200

                                                group-hover:border-emerald-400
                                                group-hover:bg-emerald-50
                                                group-hover:scale-105

                                                peer-checked:bg-emerald-500
                                                peer-checked:border-emerald-500
                                                peer-checked:shadow-md
                                                peer-checked:shadow-emerald-200
                                            "
                                        >

                                            <i
                                                class="
                                                    fa-solid fa-check
                                                    text-white text-sm
                                                    opacity-0
                                                    scale-50
                                                    transition-all
                                                    duration-200

                                                    peer-checked:opacity-100
                                                    peer-checked:scale-100
                                                "
                                            ></i>

                                        </div>

                                    </label>

                                @endforeach

                            </div>

                        </td>


                        {{-- ================= NILAI ================= --}}

                       <td
    class="
        w-[120px] min-w-[120px]
        border-b border-slate-100
        px-3
        text-center
    "
>
    <span
        class="nilai-display font-semibold text-slate-700"
        data-student-id="{{ $member->student_profile_id }}"
    >
        {{ $member->nilai !== null ? $member->nilai : '-' }}
    </span>
</td>


                        {{-- ================= DESKRIPSI ================= --}}

                        <td
    class="
        w-[360px] min-w-[360px]
        border-b border-slate-100
        px-3
        py-3
    "
>
    <div
        class="
            min-h-[80px]
            w-full
            rounded-lg
            bg-slate-50
            border
            border-slate-200
            px-3
            py-2
            text-sm
            text-slate-600
            whitespace-pre-wrap
        "
    >
        {{ $member->deskripsi ?: '-' }}
    </div>
</td>


                        {{-- ================= HAPUS ================= --}}

                        <td
                            class="
                                w-[100px] min-w-[100px]
                                border-b border-slate-100
                                text-center
                            "
                        >

                            <button
                                class="
                                    btn
                                    btn-sm
                                    btn-error
                                    btn-delete-member
                                "
                                data-id="{{ $member->id }}"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

<br>
{{-- ================= RIWAYAT PERTEMUAN ================= --}}

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

    {{-- HEADER --}}
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">

        <div>

            <h2 class="text-xl font-bold text-slate-800">
                Riwayat Pertemuan
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Daftar seluruh pertemuan ekstrakurikuler.
            </p>

        </div>


    </div>


    {{-- TABLE --}}
    <div class="overflow-x-auto">

        <table class="table w-full">

            <thead class="bg-slate-50">

                <tr>

                    <th class="w-20 text-center">
                        No
                    </th>

                    <th>
                        Nama Pertemuan
                    </th>

                    <th>
                        Tanggal
                    </th>

                    <th class="text-center">
                        Hadir
                    </th>

                    <th class="text-center">
                        Tidak Hadir
                    </th>

                    <th class="text-center w-52">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody id="meetingTableBody">

    @forelse($meetings as $meeting)

        @php

    $hadir = $meeting
        ->attendances()
        ->where('status', 'present')
        ->count();

    $tidak = $meeting
        ->attendances()
        ->where('status', 'absent')
        ->count();

@endphp

        <tr>

            <td class="text-center font-semibold">
                {{ $loop->iteration }}
            </td>

            <td>
                <div class="font-semibold">
                    {{ $meeting->title }}
                </div>
            </td>

            <td>
                {{ \Carbon\Carbon::parse($meeting->meeting_date)
                    ->translatedFormat('d F Y') }}
            </td>

            <td class="text-center">
                <span class="badge badge-success">
                    {{ $hadir }}
                </span>
            </td>

            <td class="text-center">
                <span class="badge badge-error">
                    {{ $tidak }}
                </span>
            </td>

            <td>
                <div class="flex justify-center gap-2">

                    <button
                        class="btn btn-error btn-sm deleteMeeting"
                        data-id="{{ $meeting->id }}"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>

                </div>
            </td>

        </tr>

    @empty

        <tr>
            <td
                colspan="6"
                class="py-14 text-center text-slate-400"
            >
                Belum ada pertemuan.
            </td>
        </tr>

    @endforelse

</tbody>

        </table>

    </div>

</div>

<br>

{{-- ===========================================================
     NILAI ARSIP
     =========================================================== --}}

<div
    class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden"
>

    {{-- =======================================================
         HEADER
         ======================================================= --}}

    <div
        class="
            p-6
            border-b
            border-slate-100
            flex
            items-center
            justify-between
            gap-6
        "
    >

        {{-- ================= KIRI ================= --}}

        <div>

            <h2 class="text-xl font-bold text-slate-800">
                Riwayat Nilai
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Nilai yang telah diupload dari sesi sebelumnya.
            </p>

        </div>


        {{-- ================= KANAN ================= --}}

        <div class="shrink-0">

            <button
                type="button"
                id="btnEditNilai"
                class="
                    btn
                    bg-purple-600
                    hover:bg-purple-700
                    text-white
                    border-0
                    rounded-xl
                "
            >

                <i
                    id="btnEditNilaiIcon"
                    class="fa-solid fa-pen-to-square mr-2"
                ></i>

                <span id="btnEditNilaiText">
                    Edit Nilai
                </span>

            </button>

        </div>

    </div>


    {{-- =======================================================
         TABLE
         ======================================================= --}}

    <div class="overflow-x-auto">

        <table
            id="riwayatNilaiTable"
            class="table w-full"
        >

            {{-- ================= HEADER ================= --}}

            <thead class="bg-slate-50">

                <tr>

                    <th class="text-center">
                        No
                    </th>

                    <th>
                        Nama
                    </th>

                    <th>
                        NISN
                    </th>

                    <th>
                        Kelas
                    </th>

                    <th>
                        Tipe Kelas
                    </th>

                    <th class="text-center">
                        Jumlah Absen
                    </th>

                    <th class="text-center">
                        Pertemuan
                    </th>

                    <th class="text-center">
                        Nilai
                    </th>

                    <th>
                        Deskripsi
                    </th>

                </tr>

            </thead>


            {{-- ================= BODY ================= --}}

            <tbody id="nilaiHistoryTableBody">

                @forelse($nilai as $item)

                    <tr
                        class="
                            hover:bg-slate-50
                            nilai-history-row
                        "
                        data-member-id="{{ $item->id }}"
                        data-student-id="{{ $item->student_profile_id }}"
                    >

                        {{-- ================= NO ================= --}}

                        <td class="text-center">

                            {{ $loop->iteration }}

                        </td>


                        {{-- ================= NAMA ================= --}}

                        <td class="font-semibold">

                            {{ $item->student_name }}

                        </td>


                        {{-- ================= NISN ================= --}}

                        <td>

                            {{ $item->nisn ?: '-' }}

                        </td>


                        {{-- ================= KELAS ================= --}}

                        <td>

                            {{ $item->kelas ?: '-' }}

                        </td>


                        {{-- ================= TIPE KELAS ================= --}}

                        <td>

                            {{ $item->tipe_kelas ?: '-' }}

                        </td>


                        {{-- ================= JUMLAH ABSEN ================= --}}

                        <td class="text-center">

                            {{ $item->total_absen ?? 0 }}

                        </td>


                        {{-- ================= PERTEMUAN ================= --}}

                        <td class="text-center">

                            {{ $item->total_pertemuan ?? 0 }}

                        </td>


                        {{-- =================================================
                             NILAI
                             ================================================= --}}

                        <td
                            class="
                                nilai-column
                                text-center
                                px-3
                            "
                        >

                            {{-- ================= VIEW ================= --}}

                            <div class="nilai-view">

                                @if(
                                    $item->nilai !== null &&
                                    $item->nilai !== ''
                                )

                                    <span
                                        class="
                                            badge
                                            badge-primary
                                            font-semibold
                                        "
                                    >
                                        {{ $item->nilai }}
                                    </span>

                                @else

                                    <span
                                        class="
                                            text-slate-400
                                        "
                                    >
                                        -
                                    </span>

                                @endif

                            </div>


                            {{-- ================= EDIT ================= --}}

                            <div
                                class="
                                    nilai-edit
                                    hidden
                                "
                            >

                                <input
                                    type="text"
                                    class="
                                        input
                                        input-bordered
                                        input-sm
                                        w-24
                                        text-center
                                        rounded-lg
                                        nilai-input
                                    "
                                    value="{{ $item->nilai }}"
                                    data-field="nilai"
                                    data-id="{{ $item->id }}"
                                    data-student-id="{{ $item->student_profile_id }}"
                                    placeholder="Nilai"
                                    autocomplete="off"
                                >

                            </div>

                        </td>


                        {{-- =================================================
                             DESKRIPSI
                             ================================================= --}}

                        <td
                            class="
                                deskripsi-column
                                px-3
                            "
                        >

                            {{-- ================= VIEW ================= --}}

                            <div
                                class="
                                    deskripsi-view
                                    max-w-md
                                    whitespace-pre-wrap
                                "
                            >
                                {{ $item->deskripsi ?: '-' }}
                            </div>


                            {{-- ================= EDIT ================= --}}

                            <div
                                class="
                                    deskripsi-edit
                                    hidden
                                "
                            >

                                <textarea
                                    class="
                                        textarea
                                        textarea-bordered
                                        w-full
                                        min-w-[320px]
                                        min-h-[80px]
                                        rounded-lg
                                        text-sm
                                        deskripsi-input
                                    "
                                    data-field="deskripsi"
                                    data-id="{{ $item->id }}"
                                    data-student-id="{{ $item->student_profile_id }}"
                                    placeholder="Tulis deskripsi..."
                                >{{ $item->deskripsi }}</textarea>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="9"
                            class="
                                py-14
                                text-center
                                text-slate-400
                            "
                        >

                            Belum ada nilai pada sesi ini.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

{{-- ================= MODAL ================= --}}

@include(
'features.lms.student-vice-principal.extracurricular.components.modal-add-member'
)


{{-- MODAL NILAI --}}
@include('features.lms.student-vice-principal.extracurricular.components.modal-add-nilai')

@include(
'features.lms.student-vice-principal.extracurricular.components.modal-upload-attendance'
) 

<script>
/* ===========================================================
 * DETAIL EXTRACURRICULAR
 * FULL JAVASCRIPT
 * =========================================================== */

(function () {

    'use strict';

    /* ===========================================================
     * 1. CONTAINER & CONFIG
     * =========================================================== */

    const container = document.getElementById('container');

    if (!container) {
        console.error('Container extracurricular tidak ditemukan.');
        return;
    }

    const ROLE = container.dataset.role || '';
    const SCHOOL_NAME = container.dataset.schoolName || '';
    const SCHOOL_ID = container.dataset.schoolId || '';
    const EXTRACURRICULAR_ID = container.dataset.extracurricularId || '';

    const TOKEN =
        document.querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    const BASE_URL =
        `/lms/${encodeURIComponent(ROLE)}` +
        `/${encodeURIComponent(SCHOOL_NAME)}` +
        `/${encodeURIComponent(SCHOOL_ID)}` +
        `/extracurricular-management`;

    console.log('EXTRACURRICULAR CONFIG:', {
        ROLE,
        SCHOOL_NAME,
        SCHOOL_ID,
        EXTRACURRICULAR_ID,
        BASE_URL
    });

/* ===========================================================
 * 2. URL
 * =========================================================== */

const SAVE_ATTENDANCE_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/attendance/save`;

const MEETING_STORE_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/meeting/store`;

const MEETING_UPDATE_URL =
    `${BASE_URL}/meeting/:id/update`;

const MEETING_DELETE_URL =
    `${BASE_URL}/meeting/:id/delete`;

const MEMBER_STORE_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/member/store`;

const MEMBER_IMPORT_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/member/import`;

const NILAI_IMPORT_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/nilai/import`;

const ATTENDANCE_IMPORT_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/attendance/import`;

const KPI_URL =
    `${BASE_URL}/${EXTRACURRICULAR_ID}/kpi`;


console.log('URL CONFIG:', {
    SAVE_ATTENDANCE_URL,
    MEETING_STORE_URL,
    MEETING_UPDATE_URL,
    MEETING_DELETE_URL,
    MEMBER_STORE_URL,
    MEMBER_IMPORT_URL,
    ATTENDANCE_IMPORT_URL,
    KPI_URL
});

    /* ===========================================================
     * 3. HELPER
     * =========================================================== */

    function escapeHtml(text) {

        const div = document.createElement('div');

        div.textContent = text ?? '';

        return div.innerHTML;
    }


    function showSuccessToast(message) {

        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true
        });
    }


    function showErrorToast(message) {

        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }


    /* ===========================================================
     * 4. CHECKBOX ABSENSI
     *
     * 1 checkbox = 1 request
     *
     * checked   = present
     * unchecked = absent
     * =========================================================== */

    $(document)
        .off(
            'change.extracurricularAttendance',
            '.attendance-checkbox'
        )
        .on(
            'change.extracurricularAttendance',
            '.attendance-checkbox',
            function () {

                const checkbox = this;

                const studentProfileId =
                    checkbox.dataset.student;

                const meetingId =
                    checkbox.dataset.meeting;

                const status =
                    checkbox.checked
                        ? 'present'
                        : 'absent';


                console.log('ABSENSI CHECKBOX:', {
                    student_profile_id: studentProfileId,
                    meeting_id: meetingId,
                    status: status,
                    url: SAVE_ATTENDANCE_URL
                });


                /* ---------------------------------------------------
                 * VALIDASI
                 * --------------------------------------------------- */

                if (!studentProfileId) {

                    console.error(
                        'student_profile_id tidak ditemukan pada checkbox.'
                    );

                    return;
                }


                if (!meetingId) {

                    console.error(
                        'meeting_id tidak ditemukan pada checkbox.'
                    );

                    return;
                }


                /* ---------------------------------------------------
                 * CEGAH DOUBLE REQUEST
                 * --------------------------------------------------- */

                if (checkbox.dataset.saving === '1') {
                    return;
                }

                checkbox.dataset.saving = '1';


                /* ---------------------------------------------------
                 * LOADING
                 * --------------------------------------------------- */

                const wrapper =
                    checkbox.closest('label');

                if (wrapper) {
                    wrapper.classList.add('opacity-70');
                }


                /* ---------------------------------------------------
                 * SIMPAN ABSENSI
                 * --------------------------------------------------- */

                $.ajax({

                    url: SAVE_ATTENDANCE_URL,

                    type: 'POST',

                    data: {

                        _token: TOKEN,

                        student_profile_id:
                            studentProfileId,

                        meeting_id:
                            meetingId,

                        status:
                            status
                    },


                    /* ---------------------------------------------------
                     * SUCCESS
                     * --------------------------------------------------- */

                    success: function (response) {

                        console.log(
                            'Attendance saved:',
                            response
                        );


                        showSuccessToast(
                            status === 'present'
                                ? 'Siswa ditandai hadir'
                                : 'Kehadiran dibatalkan'
                        );


                        /* ---------------------------------------------------
                         * UPDATE RIWAYAT
                         * --------------------------------------------------- */

                        if (
                            typeof refreshMeetingAttendance ===
                            'function'
                        ) {

                            refreshMeetingAttendance(
                                meetingId
                            );
                        }
                    },


                    /* ---------------------------------------------------
                     * ERROR
                     * --------------------------------------------------- */

                    error: function (xhr) {

                        console.error(
                            'Attendance error:',
                            xhr
                        );

                        console.error(
                            'STATUS:',
                            xhr.status
                        );

                        console.error(
                            'RESPONSE:',
                            xhr.responseText
                        );


                        /* ---------------------------------------------------
                         * KEMBALIKAN CHECKBOX
                         * --------------------------------------------------- */

                        checkbox.checked =
                            !checkbox.checked;


                        let message =
                            'Absensi gagal disimpan.';


                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }


                        if (typeof Swal !== 'undefined') {

                            Swal.fire({

                                toast: true,

                                position: 'top-end',

                                icon: 'error',

                                title:
                                    'Gagal menyimpan',

                                text:
                                    message,

                                showConfirmButton:
                                    false,

                                timer:
                                    2000,

                                timerProgressBar:
                                    true
                            });
                        }
                    },


                    /* ---------------------------------------------------
                     * COMPLETE
                     * --------------------------------------------------- */

                    complete: function () {

                        checkbox.dataset.saving = '0';


                        if (wrapper) {

                            wrapper.classList.remove(
                                'opacity-70'
                            );
                        }
                    }

                });

            }
        );


    /* ===========================================================
     * 5. TOMBOL SIMPAN ABSENSI
     *
     * Karena checkbox sudah AUTO SAVE,
     * tombol ini hanya memberikan informasi.
     * =========================================================== */

    const btnSaveAttendance =
        document.getElementById(
            'btnSaveAttendance'
        );


    if (btnSaveAttendance) {

        $(btnSaveAttendance)
            .off('click.manualAttendance')
            .on(
                'click.manualAttendance',
                function () {

                    if (typeof Swal === 'undefined') {
                        return;
                    }

                    Swal.fire({

                        icon: 'info',

                        title: 'Absensi Otomatis',

                        text:
                            'Setiap perubahan checkbox absensi langsung disimpan.',

                        timer: 1800,

                        showConfirmButton: false,

                        timerProgressBar: true
                    });
                }
            );
    }


    /* ===========================================================
     * 6. CRUD PERTEMUAN
     * =========================================================== */


    /* -----------------------------------------------------------
     * TAMBAH PERTEMUAN
     * ----------------------------------------------------------- */

    $(document)
        .off(
            'submit.addMeeting',
            '#form_add_meeting'
        )
        .on(
            'submit.addMeeting',
            '#form_add_meeting',
            function (e) {

                e.preventDefault();

                const form = this;


                $.ajax({

                    url: MEETING_STORE_URL,

                    type: 'POST',

                    data: new FormData(form),

                    processData: false,

                    contentType: false,


                    beforeSend: function () {

                        $('#btnSaveMeeting')
                            .prop(
                                'disabled',
                                true
                            )
                            .html(`
                                <span class="loading loading-spinner loading-sm"></span>
                                Menyimpan...
                            `);
                    },


                    success: function (res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text:
                                res.message ||
                                'Pertemuan berhasil ditambahkan.',

                            timer: 1500,

                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });
                    },


                    error: function (xhr) {

                        let message =
                            'Terjadi kesalahan.';


                        if (
                            xhr.status === 422 &&
                            xhr.responseJSON?.errors
                        ) {

                            message = '';

                            $.each(
                                xhr.responseJSON.errors,
                                function (
                                    key,
                                    value
                                ) {

                                    message +=
                                        value[0] +
                                        '<br>';
                                }
                            );

                        } else if (
                            xhr.responseJSON?.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            html: message
                        });
                    },


                    complete: function () {

                        $('#btnSaveMeeting')
                            .prop(
                                'disabled',
                                false
                            )
                            .html(`
                                <i class="fa-solid fa-floppy-disk mr-2"></i>
                                Simpan
                            `);
                    }

                });

            }
        );


    /* -----------------------------------------------------------
     * EDIT PERTEMUAN
     * ----------------------------------------------------------- */

    $(document)
        .off(
            'click.editMeeting',
            '.editMeeting'
        )
        .on(
            'click.editMeeting',
            '.editMeeting',
            function () {

                const button = $(this);


                $('#meeting_id')
                    .val(
                        button.data('id')
                    );


                $('#meeting_title')
                    .val(
                        button.data('title')
                    );


                $('#meeting_date')
                    .val(
                        button.data('date')
                    );


                if (
                    typeof modal_edit_meeting !==
                    'undefined'
                ) {

                    modal_edit_meeting.showModal();
                }
            }
        );


    /* -----------------------------------------------------------
     * UPDATE PERTEMUAN
     * ----------------------------------------------------------- */

    $(document)
        .off(
            'submit.updateMeeting',
            '#form_edit_meeting'
        )
        .on(
            'submit.updateMeeting',
            '#form_edit_meeting',
            function (e) {

                e.preventDefault();

                const id =
                    $('#meeting_id').val();


                if (!id) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text:
                            'ID pertemuan tidak ditemukan.'
                    });

                    return;
                }


                $.ajax({

                    url:
                        MEETING_UPDATE_URL.replace(
                            ':id',
                            id
                        ),

                    type: 'POST',

                    data: new FormData(this),

                    processData: false,

                    contentType: false,


                    beforeSend: function () {

                        $('#btnUpdateMeeting')
                            .prop(
                                'disabled',
                                true
                            );
                    },


                    success: function (res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text:
                                res.message ||
                                'Pertemuan berhasil diperbarui.',

                            timer: 1500,

                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });
                    },


                    error: function (xhr) {

                        let message =
                            'Gagal memperbarui pertemuan.';


                        if (
                            xhr.status === 422 &&
                            xhr.responseJSON?.errors
                        ) {

                            message = '';

                            $.each(
                                xhr.responseJSON.errors,
                                function (
                                    key,
                                    value
                                ) {

                                    message +=
                                        value[0] +
                                        '<br>';
                                }
                            );

                        } else if (
                            xhr.responseJSON?.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            html: message
                        });
                    },


                    complete: function () {

                        $('#btnUpdateMeeting')
                            .prop(
                                'disabled',
                                false
                            );
                    }

                });

            }
        );


    /* -----------------------------------------------------------
     * DELETE PERTEMUAN
     * ----------------------------------------------------------- */

    $(document)
        .off(
            'click.deleteMeeting',
            '.deleteMeeting'
        )
        .on(
            'click.deleteMeeting',
            '.deleteMeeting',
            async function () {

                const id =
                    $(this).data('id');


                if (!id) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text:
                            'ID pertemuan tidak ditemukan.'
                    });

                    return;
                }


                const confirm =
                    await Swal.fire({

                        icon: 'warning',

                        title: 'Hapus Pertemuan?',

                        text:
                            'Data absensi pada pertemuan ini juga akan ikut terhapus.',

                        showCancelButton: true,

                        confirmButtonText:
                            'Ya, Hapus',

                        cancelButtonText:
                            'Batal',

                        confirmButtonColor:
                            '#ef4444',

                        cancelButtonColor:
                            '#64748b'
                    });


                if (!confirm.isConfirmed) {
                    return;
                }


                try {

                    const response =
                        await fetch(

                            MEETING_DELETE_URL.replace(
                                ':id',
                                id
                            ),

                            {

                                method: 'DELETE',

                                headers: {

                                    'X-CSRF-TOKEN':
                                        TOKEN,

                                    'Accept':
                                        'application/json'
                                }
                            }
                        );


                    const result =
                        await response.json();


                    if (!response.ok) {

                        throw new Error(
                            result.message ||
                            'Gagal menghapus pertemuan.'
                        );
                    }


                    Swal.fire({

                        icon: 'success',

                        title: 'Berhasil',

                        text:
                            result.message ||
                            'Pertemuan berhasil dihapus.',

                        timer: 1200,

                        showConfirmButton: false

                    }).then(function () {

                        location.reload();

                    });


                } catch (error) {

                    console.error(
                        'DELETE MEETING ERROR:',
                        error
                    );


                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text:
                            error.message ||
                            'Gagal menghapus pertemuan.'
                    });
                }

            }
        );


    /* ===========================================================
     * 7. CRUD PESERTA
     * =========================================================== */


    /* -----------------------------------------------------------
     * TAMBAH PESERTA
     * ----------------------------------------------------------- */

    $(document)
        .off(
            'submit.addMember',
            '#form_add_member'
        )
        .on(
            'submit.addMember',
            '#form_add_member',
            function (e) {

                e.preventDefault();

                const form = this;


                $.ajax({

                    url: MEMBER_STORE_URL,

                    type: 'POST',

                    data: new FormData(form),

                    processData: false,

                    contentType: false,


                    beforeSend: function () {

                        $('#btnSaveMember')
                            .prop(
                                'disabled',
                                true
                            )
                            .html(`
                                <span class="loading loading-spinner loading-sm"></span>
                                Menyimpan...
                            `);
                    },


                    success: function (res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text:
                                res.message ||
                                'Peserta berhasil ditambahkan.',

                            timer: 1500,

                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });
                    },


                    error: function (xhr) {

                        let message =
                            'Terjadi kesalahan.';


                        if (
                            xhr.responseJSON?.errors
                        ) {

                            message = '';

                            $.each(
                                xhr.responseJSON.errors,
                                function (
                                    key,
                                    value
                                ) {

                                    message +=
                                        value[0] +
                                        '<br>';
                                }
                            );

                        } else if (
                            xhr.responseJSON?.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            html: message
                        });
                    },


                    complete: function () {

                        $('#btnSaveMember')
                            .prop(
                                'disabled',
                                false
                            )
                            .html(`
                                <i class="fa-solid fa-floppy-disk mr-2"></i>
                                Simpan
                            `);
                    }

                });

            }
        );


    /* -----------------------------------------------------------
     * IMPORT PESERTA
     * ----------------------------------------------------------- */

    $(document)
        .off(
            'submit.importMember',
            '#form_import_member'
        )
        .on(
            'submit.importMember',
            '#form_import_member',
            function (e) {

                e.preventDefault();

                const form = this;


                $.ajax({

                    url: MEMBER_IMPORT_URL,

                    type: 'POST',

                    data: new FormData(form),

                    processData: false,

                    contentType: false,


                    beforeSend: function () {

                        $('#btnImportMember')
                            .prop(
                                'disabled',
                                true
                            )
                            .html(`
                                <span class="loading loading-spinner loading-sm"></span>
                                Mengimport...
                            `);
                    },


                    success: function (res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text:
                                res.message ||
                                'Peserta berhasil diimport.',

                            timer: 1500,

                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });
                    },


                    error: function (xhr) {

                        let message =
                            'Import peserta gagal.';


                        if (
                            xhr.responseJSON?.errors
                        ) {

                            message = '';

                            $.each(
                                xhr.responseJSON.errors,
                                function (
                                    key,
                                    value
                                ) {

                                    message +=
                                        value[0] +
                                        '<br>';
                                }
                            );

                        } else if (
                            xhr.responseJSON?.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            html: message
                        });
                    },


                    complete: function () {

                        $('#btnImportMember')
                            .prop(
                                'disabled',
                                false
                            )
                            .html(`
                                <i class="fa-solid fa-file-import mr-2"></i>
                                Import
                            `);
                    }

                });

            }
        );


    /* ===========================================================
     * 8. HAPUS PESERTA
     * =========================================================== */

    $(document)
        .off(
            'click.deleteMember',
            '.btn-delete-member'
        )
        .on(
            'click.deleteMember',
            '.btn-delete-member',
            async function () {

                const button = $(this);

                const id =
                    button.data('id');


                if (!id) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text:
                            'ID peserta tidak ditemukan.'
                    });

                    return;
                }


                const confirm =
                    await Swal.fire({

                        icon: 'warning',

                        title: 'Hapus Anggota?',

                        text:
                            'Anggota akan dikeluarkan dari ekstrakurikuler.',

                        showCancelButton: true,

                        confirmButtonText:
                            'Ya, Hapus',

                        cancelButtonText:
                            'Batal',

                        confirmButtonColor:
                            '#ef4444',

                        cancelButtonColor:
                            '#64748b'
                    });


                if (!confirm.isConfirmed) {
                    return;
                }


                const deleteUrl =
                    `${BASE_URL}/${EXTRACURRICULAR_ID}/member/${id}`;


                console.log(
                    'DELETE MEMBER:',
                    deleteUrl
                );


                $.ajax({

                    url: deleteUrl,

                    type: 'DELETE',

                    headers: {

                        'X-CSRF-TOKEN':
                            TOKEN,

                        'Accept':
                            'application/json'
                    },


                    success: function (res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text:
                                res.message ||
                                'Peserta berhasil dihapus.',

                            timer: 1200,

                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });
                    },


                    error: function (xhr) {

                        console.error(
                            'DELETE MEMBER ERROR:',
                            xhr.responseText
                        );


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            text:
                                xhr.responseJSON?.message ||
                                'Tidak dapat menghapus anggota.'
                        });
                    }

                });

            }
        );


    /* ===========================================================
     * 9. IMPORT ABSENSI EXCEL
     * =========================================================== */

    const attendanceImport = {

        url:
            ATTENDANCE_IMPORT_URL,

        form:
            document.getElementById(
                'form_import_attendance'
            ),

        dropZone:
            document.getElementById(
                'dropZoneAttendance'
            ),

        fileInput:
            document.getElementById(
                'attendanceFile'
            ),

        fileName:
            document.getElementById(
                'attendanceFileName'
            ),

        button:
            document.getElementById(
                'btnImportAttendance'
            )
    };


    if (
        attendanceImport.form &&
        attendanceImport.dropZone &&
        attendanceImport.fileInput
    ) {


        /* -----------------------------------------------------------
         * SHOW FILE NAME
         * ----------------------------------------------------------- */

        function showAttendanceFile(file) {

            if (!attendanceImport.fileName) {
                return;
            }


            attendanceImport.fileName.classList.remove(
                'hidden'
            );


            attendanceImport.fileName.innerHTML = `
                <i class="fa-solid fa-file-excel text-green-600 mr-2"></i>
                ${escapeHtml(file.name)}
            `;
        }


        /* -----------------------------------------------------------
         * DROPZONE CLICK
         * ----------------------------------------------------------- */

        $(attendanceImport.dropZone)
            .off('click.attendanceImport')
            .on(
                'click.attendanceImport',
                function () {

                    attendanceImport.fileInput.click();

                }
            );


        /* -----------------------------------------------------------
         * FILE CHANGE
         * ----------------------------------------------------------- */

        $(attendanceImport.fileInput)
            .off('change.attendanceImport')
            .on(
                'change.attendanceImport',
                function () {

                    if (
                        this.files &&
                        this.files.length
                    ) {

                        showAttendanceFile(
                            this.files[0]
                        );
                    }
                }
            );


        /* -----------------------------------------------------------
         * DRAG OVER
         * ----------------------------------------------------------- */

        $(attendanceImport.dropZone)
            .off('dragover.attendanceImport')
            .on(
                'dragover.attendanceImport',
                function (e) {

                    e.preventDefault();

                    $(this).addClass(
                        'border-blue-600',
                        'bg-blue-50',
                        'scale-[1.02]'
                    );
                }
            );


        /* -----------------------------------------------------------
         * DRAG LEAVE
         * ----------------------------------------------------------- */

        $(attendanceImport.dropZone)
            .off('dragleave.attendanceImport')
            .on(
                'dragleave.attendanceImport',
                function () {

                    $(this).removeClass(
                        'border-blue-600',
                        'bg-blue-50',
                        'scale-[1.02]'
                    );
                }
            );


        /* -----------------------------------------------------------
         * DROP
         * ----------------------------------------------------------- */

        $(attendanceImport.dropZone)
            .off('drop.attendanceImport')
            .on(
                'drop.attendanceImport',
                function (e) {

                    e.preventDefault();


                    $(this).removeClass(
                        'border-blue-600',
                        'bg-blue-50',
                        'scale-[1.02]'
                    );


                    const files =
                        e.originalEvent
                            .dataTransfer
                            .files;


                    if (
                        files &&
                        files.length
                    ) {

                        try {

                            /*
                             * FileList biasanya readonly.
                             * Jika browser mengizinkan DataTransfer,
                             * kita gunakan untuk memasukkan file.
                             */

                            const dataTransfer =
                                new DataTransfer();

                            dataTransfer.items.add(
                                files[0]
                            );

                            attendanceImport.fileInput.files =
                                dataTransfer.files;

                        } catch (error) {

                            console.warn(
                                'Tidak dapat mengatur file input:',
                                error
                            );
                        }


                        showAttendanceFile(
                            files[0]
                        );
                    }
                }
            );


        /* -----------------------------------------------------------
         * SUBMIT IMPORT
         * ----------------------------------------------------------- */

        $(attendanceImport.form)
            .off('submit.attendanceImport')
            .on(
                'submit.attendanceImport',
                function (e) {

                    e.preventDefault();


                    if (
                        !attendanceImport.fileInput.files.length
                    ) {

                        Swal.fire({

                            icon: 'warning',

                            title: 'Pilih File',

                            text:
                                'Silakan pilih file Excel terlebih dahulu.'
                        });

                        return;
                    }


                    const formData =
                        new FormData();


                    formData.append(
                        '_token',
                        TOKEN
                    );


                    formData.append(
                        'file',
                        attendanceImport
                            .fileInput
                            .files[0]
                    );


                    $.ajax({

                        url:
                            attendanceImport.url,

                        type:
                            'POST',

                        data:
                            formData,

                        processData:
                            false,

                        contentType:
                            false,

                        cache:
                            false,


                        beforeSend: function () {

                            if (
                                attendanceImport.button
                            ) {

                                attendanceImport.button.disabled =
                                    true;

                                attendanceImport.button.innerHTML = `
                                    <span class="loading loading-spinner loading-sm"></span>
                                    Mengupload...
                                `;
                            }
                        },


                        success: function (res) {

                            Swal.fire({

                                icon: 'success',

                                title: 'Berhasil',

                                text:
                                    res.message ||
                                    'Absensi berhasil diimport.',

                                timer: 1500,

                                showConfirmButton:
                                    false

                            }).then(function () {

                                location.reload();

                            });
                        },


                        error: function (xhr) {

                            console.error(
                                'IMPORT ABSENSI ERROR:',
                                xhr
                            );

                            console.error(
                                xhr.responseText
                            );


                            let message =
                                'Upload gagal.';


                            if (
                                xhr.responseJSON?.message
                            ) {

                                message =
                                    xhr.responseJSON.message;
                            }


                            if (
                                xhr.responseJSON?.errors
                            ) {

                                message = '';

                                $.each(
                                    xhr.responseJSON.errors,
                                    function (
                                        key,
                                        value
                                    ) {

                                        message +=
                                            value[0] +
                                            '\n';
                                    }
                                );
                            }


                            Swal.fire({

                                icon: 'error',

                                title: 'Gagal',

                                text: message
                            });
                        },


                        complete: function () {

                            if (
                                attendanceImport.button
                            ) {

                                attendanceImport.button.disabled =
                                    false;

                                attendanceImport.button.innerHTML = `
                                    <i class="fa-solid fa-file-import mr-2"></i>
                                    Upload
                                `;
                            }
                        }

                    });

                }
            );
    }

/* ===========================================================
 * IMPORT NILAI EXCEL
 * =========================================================== */

const nilaiImport = {

    form:
        document.getElementById(
            'form_import_nilai'
        ),

    fileInput:
        document.getElementById(
            'nilaiFile'
        ),

    button:
        document.getElementById(
            'btnImportNilai'
        ),

    url:
        NILAI_IMPORT_URL
};


if (
    nilaiImport.form &&
    nilaiImport.fileInput
) {

    $(nilaiImport.form)
        .off('submit.nilaiImport')
        .on(
            'submit.nilaiImport',
            function (e) {

                e.preventDefault();


                const file =
                    nilaiImport
                        .fileInput
                        .files[0];


                if (!file) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Pilih File',

                        text:
                            'Silakan pilih file Excel nilai.'
                    });

                    return;
                }


                console.log(
                    'UPLOAD NILAI URL:',
                    nilaiImport.url
                );


                const formData =
                    new FormData();


                formData.append(
                    '_token',
                    TOKEN
                );


                formData.append(
                    'file',
                    file
                );


                $.ajax({

                    url:
                        nilaiImport.url,

                    type:
                        'POST',

                    data:
                        formData,

                    processData:
                        false,

                    contentType:
                        false,

                    cache:
                        false,


                    beforeSend: function () {

                        if (
                            nilaiImport.button
                        ) {

                            nilaiImport.button.disabled =
                                true;

                            nilaiImport.button.innerHTML = `
                                <span class="loading loading-spinner loading-sm"></span>
                                Mengupload...
                            `;
                        }
                    },


                    success: function (res) {

                        console.log(
                            'UPLOAD NILAI SUCCESS:',
                            res
                        );


                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text:
                                res.message ||
                                'Nilai berhasil diupload.',

                            timer: 1800,

                            showConfirmButton:
                                false

                        }).then(function () {

                            location.reload();

                        });
                    },


                    error: function (xhr) {

                        console.error(
                            'UPLOAD NILAI ERROR:',
                            xhr
                        );

                        console.error(
                            'STATUS:',
                            xhr.status
                        );

                        console.error(
                            'RESPONSE:',
                            xhr.responseText
                        );


                        let message =
                            'Upload nilai gagal.';


                        if (
                            xhr.responseJSON?.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }


                        if (
                            xhr.responseJSON?.errors
                        ) {

                            message = '';

                            $.each(
                                xhr.responseJSON.errors,
                                function (
                                    key,
                                    value
                                ) {

                                    message +=
                                        value[0] +
                                        '<br>';
                                }
                            );
                        }


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            html:
                                message

                        });
                    },


                    complete: function () {

                        if (
                            nilaiImport.button
                        ) {

                            nilaiImport.button.disabled =
                                false;

                            nilaiImport.button.innerHTML = `
                                <i class="fa-solid fa-file-excel mr-2"></i>
                                Upload
                            `;
                        }
                    }

                });

            }
        );
}

/* ===========================================================
 * 7. KPI
 * =========================================================== */

function refreshKPI() {

    console.log('================================');
    console.log('REQUEST KPI');
    console.log('URL:', KPI_URL);
    console.log('================================');

    $.ajax({
        url: KPI_URL,
        type: 'GET',
        dataType: 'json',

        success: function (res) {

            console.log('================================');
            console.log('KPI RESPONSE ASLI:');
            console.log(res);
            console.log('JSON STRING:');
            console.log(JSON.stringify(res, null, 2));
            console.log('================================');

            /*
             * JANGAN HITUNG APA-APA DULU.
             * Tampilkan semua kemungkinan field.
             */

            const totalMember =
                res.total_member ??
                res.totalMember ??
                res.member_count ??
                res.members ??
                0;

            const totalMeeting =
                res.total_meeting ??
                res.totalMeeting ??
                res.meeting_count ??
                res.meetings ??
                0;

            const totalAttendance =
                res.total_attendance ??
                res.totalAttendance ??
                res.attendance_count ??
                res.attendances ??
                0;

            const attendancePercent =
                res.attendance_percent ??
                res.attendancePercent ??
                res.attendance_percentage ??
                res.percentage ??
                0;


            console.log('KPI PARSED:', {
                totalMember,
                totalMeeting,
                totalAttendance,
                attendancePercent
            });


            $('#total-member').text(
                totalMember
            );

            $('#total-meeting').text(
                totalMeeting
            );

            $('#totalAttendance').text(
                totalAttendance
            );

            $('#attendance-percent').text(
                attendancePercent + '%'
            );

        },

        error: function (xhr) {

            console.error('================================');
            console.error('KPI ERROR');
            console.error('STATUS:', xhr.status);
            console.error('URL:', KPI_URL);
            console.error('RESPONSE:', xhr.responseText);
            console.error('================================');

        }
    });
}

    /* ===========================================================
 * SEMESTER / PERIODE SELECTOR
 * =========================================================== */

$(document)
    .off(
        'change.semesterSelector',
        '#semesterSelector'
    )
    .on(
        'change.semesterSelector',
        '#semesterSelector',
        function () {

            const periodId =
                this.value;

            if (!periodId) {
                return;
            }


            const url =
                new URL(
                    window.location.href
                );


            url.searchParams.set(
                'period_id',
                periodId
            );


            window.location.href =
                url.toString();
        }
    );


    /* ===========================================================
     * 11. SEARCH PESERTA
     * =========================================================== */

    $(document)
        .off(
            'keyup.searchMember',
            '#searchMember'
        )
        .on(
            'keyup.searchMember',
            '#searchMember',
            function () {

                const keyword =
                    $(this)
                        .val()
                        .toLowerCase();


                $('#memberTable tbody tr')
                    .each(function () {

                        $(this).toggle(

                            $(this)
                                .text()
                                .toLowerCase()
                                .includes(
                                    keyword
                                )
                        );
                    });
            }
        );


    /* ===========================================================
     * 12. SEARCH PERTEMUAN
     * =========================================================== */

    $(document)
        .off(
            'keyup.searchMeeting',
            '#searchMeeting'
        )
        .on(
            'keyup.searchMeeting',
            '#searchMeeting',
            function () {

                const keyword =
                    $(this)
                        .val()
                        .toLowerCase();


                $('#meetingTable tbody tr')
                    .each(function () {

                        $(this).toggle(

                            $(this)
                                .text()
                                .toLowerCase()
                                .includes(
                                    keyword
                                )
                        );
                    });
            }
        );


    /* ===========================================================
     * 13. SEARCH ABSENSI
     * =========================================================== */

    $(document)
        .off(
            'keyup.searchAttendance',
            '#searchAttendance'
        )
        .on(
            'keyup.searchAttendance',
            '#searchAttendance',
            function () {

                const keyword =
                    $(this)
                        .val()
                        .toLowerCase();


                $('#attendanceTable tbody tr')
                    .each(function () {

                        $(this).toggle(

                            $(this)
                                .text()
                                .toLowerCase()
                                .includes(
                                    keyword
                                )
                        );
                    });
            }
        );


    /* ===========================================================
     * 14. EXPORT EXCEL
     * =========================================================== */

    $(document)
        .off(
            'click.exportExcel',
            '#btnExportExcel'
        )
        .on(
            'click.exportExcel',
            '#btnExportExcel',
            function () {

                window.location.href =
                    `${BASE_URL}/${EXTRACURRICULAR_ID}/export/excel`;

            }
        );


    /* ===========================================================
     * 15. EXPORT PDF
     * =========================================================== */

    $(document)
        .off(
            'click.exportPdf',
            '#btnExportPdf'
        )
        .on(
            'click.exportPdf',
            '#btnExportPdf',
            function () {

                window.location.href =
                    `${BASE_URL}/${EXTRACURRICULAR_ID}/export/pdf`;

            }
        );


    /* ===========================================================
     * 16. PRINT
     * =========================================================== */

    $(document)
        .off(
            'click.printPage',
            '#btnPrint'
        )
        .on(
            'click.printPage',
            '#btnPrint',
            function () {

                window.print();

            }
        );


    /* ===========================================================
     * 17. REFRESH KPI
     * =========================================================== */

    setInterval(
        function () {

            refreshKPI();

        },
        30000
    );


    /* ===========================================================
     * 18. INITIAL LOAD
     * =========================================================== */

    $(function () {

        refreshKPI();

    });


    /* ===========================================================
     * 19. PUBLIC HELPER
     * =========================================================== */

    window.refreshExtracurricularKPI =
        refreshKPI;


    /* ===========================================================
     * 20. DEBUG CONFIG
     * =========================================================== */

    console.log(
        'Extracurricular JavaScript berhasil dimuat.'
    );

})();


</script>
<script>
(function () {

    'use strict';

    /* ============================================================
     * CONFIG
     * ============================================================ */

    const container = document.getElementById('container');

    if (!container) {
        console.error('Container extracurricular tidak ditemukan.');
        return;
    }

    const ROLE =
        container.dataset.role || '';

    const SCHOOL_NAME =
        container.dataset.schoolName || '';

    const SCHOOL_ID =
        container.dataset.schoolId || '';

    const EXTRACURRICULAR_ID =
        container.dataset.extracurricularId || '';

    const TOKEN =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';


    /* ============================================================
     * AMBIL PERIOD ID DARI URL
     * ============================================================ */

    const urlParams =
        new URLSearchParams(window.location.search);

    const PERIOD_ID =
        urlParams.get('period_id') || '';


    console.log('========================================');
    console.log('EDIT NILAI CONFIG');
    console.log('ROLE:', ROLE);
    console.log('SCHOOL NAME:', SCHOOL_NAME);
    console.log('SCHOOL ID:', SCHOOL_ID);
    console.log('EXTRACURRICULAR ID:', EXTRACURRICULAR_ID);
    console.log('PERIOD ID:', PERIOD_ID);
    console.log('CURRENT URL:', window.location.href);
    console.log('========================================');


    /* ============================================================
     * VALIDASI PERIOD ID
     * ============================================================ */

    if (!PERIOD_ID) {

        console.error(
            'PERIOD ID TIDAK DITEMUKAN.'
        );

        console.error(
            'URL saat ini:',
            window.location.href
        );

        /*
         * Jangan langsung return.
         * Kita tetap membiarkan halaman bekerja,
         * tetapi saat simpan akan diberi pesan jelas.
         */

    }


    /* ============================================================
     * BASE URL
     * ============================================================ */

    const BASE_URL =
        `/lms/${encodeURIComponent(ROLE)}` +
        `/${encodeURIComponent(SCHOOL_NAME)}` +
        `/${encodeURIComponent(SCHOOL_ID)}` +
        `/extracurricular-management`;


    /* ============================================================
     * URL UPDATE NILAI
     * ============================================================ */

    const NILAI_UPDATE_URL =
        `${BASE_URL}/${EXTRACURRICULAR_ID}/nilai/update`;


    console.log(
        'NILAI UPDATE URL:',
        NILAI_UPDATE_URL
    );


    /* ============================================================
     * ELEMENT
     * ============================================================ */

    const editButton =
        document.getElementById('btnEditNilai');

    const editButtonText =
        document.getElementById('btnEditNilaiText');

    const editButtonIcon =
        document.getElementById('btnEditNilaiIcon');

    const table =
        document.getElementById('riwayatNilaiTable');

    const tableBody =
        document.getElementById('nilaiHistoryTableBody');


    if (!editButton) {

        console.warn(
            'Tombol #btnEditNilai tidak ditemukan.'
        );

        return;
    }


    if (!tableBody) {

        console.warn(
            'Body #nilaiHistoryTableBody tidak ditemukan.'
        );

        return;
    }


    /* ============================================================
     * STATE
     * ============================================================ */

    let editMode = false;


    /* ============================================================
     * TOAST
     * ============================================================ */

    function successToast(message) {

        if (typeof Swal === 'undefined') {

            alert(message);

            return;
        }

        Swal.fire({

            toast: true,

            position: 'top-end',

            icon: 'success',

            title: message,

            showConfirmButton: false,

            timer: 1800,

            timerProgressBar: true

        });

    }


    function errorToast(message) {

        if (typeof Swal === 'undefined') {

            alert(message);

            return;
        }

        Swal.fire({

            toast: true,

            position: 'top-end',

            icon: 'error',

            title: message,

            showConfirmButton: false,

            timer: 3000,

            timerProgressBar: true

        });

    }


    /* ============================================================
     * AMBIL ROW
     * ============================================================ */

    function getRows() {

        return tableBody.querySelectorAll(
            '.nilai-history-row'
        );

    }


    /* ============================================================
     * AMBIL STUDENT PROFILE ID
     *
     * Mendukung beberapa kemungkinan nama dataset:
     *
     * data-student-id
     * data-student-profile-id
     * data-student_profile_id
     * ============================================================ */

    function getStudentProfileId(row) {

        let studentId =
            row.dataset.studentId || '';

        if (!studentId) {

            studentId =
                row.dataset.studentProfileId || '';

        }

        if (!studentId) {

            studentId =
                row.getAttribute(
                    'data-student_profile_id'
                ) || '';

        }

        return String(studentId).trim();

    }


    /* ============================================================
     * MASUK MODE EDIT
     * ============================================================ */

    function enterEditMode() {

        const rows = getRows();

        console.log(
            'Jumlah row nilai:',
            rows.length
        );


        if (!rows.length) {

            errorToast(
                'Tidak ada data nilai yang dapat diedit.'
            );

            return;

        }


        let editableRows = 0;


        rows.forEach(function (row) {

            const nilaiView =
                row.querySelector('.nilai-view');

            const nilaiEdit =
                row.querySelector('.nilai-edit');

            const deskripsiView =
                row.querySelector('.deskripsi-view');

            const deskripsiEdit =
                row.querySelector('.deskripsi-edit');


            /*
             * NILAI
             */

            if (nilaiView) {

                nilaiView.classList.add('hidden');

            }


            if (nilaiEdit) {

                nilaiEdit.classList.remove('hidden');

            }


            /*
             * DESKRIPSI
             */

            if (deskripsiView) {

                deskripsiView.classList.add('hidden');

            }


            if (deskripsiEdit) {

                deskripsiEdit.classList.remove('hidden');

            }


            /*
             * Highlight
             */

            row.classList.add(
                'bg-purple-50/30'
            );


            /*
             * Debug student ID
             */

            const studentId =
                getStudentProfileId(row);


            console.log(
                'ROW STUDENT ID:',
                studentId
            );


            if (studentId) {

                editableRows++;

            }

        });


        console.log(
            'Row yang memiliki student_profile_id:',
            editableRows
        );


        editMode = true;


        /* ========================================================
         * UBAH TOMBOL MENJADI SIMPAN
         * ======================================================== */

        editButton.classList.remove(
            'bg-purple-600',
            'hover:bg-purple-700'
        );

        editButton.classList.add(
            'bg-green-600',
            'hover:bg-green-700'
        );


        if (editButtonIcon) {

            editButtonIcon.className =
                'fa-solid fa-floppy-disk mr-2';

        }


        if (editButtonText) {

            editButtonText.textContent =
                'Simpan';

        }

    }


    /* ============================================================
     * KELUAR MODE EDIT
     * ============================================================ */

    function exitEditMode() {

        const rows = getRows();


        rows.forEach(function (row) {

            const nilaiView =
                row.querySelector('.nilai-view');

            const nilaiEdit =
                row.querySelector('.nilai-edit');

            const deskripsiView =
                row.querySelector('.deskripsi-view');

            const deskripsiEdit =
                row.querySelector('.deskripsi-edit');


            if (nilaiView) {

                nilaiView.classList.remove(
                    'hidden'
                );

            }


            if (nilaiEdit) {

                nilaiEdit.classList.add(
                    'hidden'
                );

            }


            if (deskripsiView) {

                deskripsiView.classList.remove(
                    'hidden'
                );

            }


            if (deskripsiEdit) {

                deskripsiEdit.classList.add(
                    'hidden'
                );

            }


            row.classList.remove(
                'bg-purple-50/30'
            );

        });


        editMode = false;


        /* ========================================================
         * KEMBALIKAN TOMBOL
         * ======================================================== */

        editButton.classList.remove(
            'bg-green-600',
            'hover:bg-green-700'
        );

        editButton.classList.add(
            'bg-purple-600',
            'hover:bg-purple-700'
        );


        if (editButtonIcon) {

            editButtonIcon.className =
                'fa-solid fa-pen-to-square mr-2';

        }


        if (editButtonText) {

            editButtonText.textContent =
                'Edit Nilai';

        }

    }


    /* ============================================================
     * COLLECT DATA NILAI
     *
     * NILAI BEBAS:
     *
     * 100
     * A
     * bagus
     * A+
     * 90/100
     * hadir
     * kosong
     * simbol
     *
     * semuanya diterima.
     * ============================================================ */

    function collectNilaiData() {

        const rows = getRows();

        const members = [];


        rows.forEach(function (row, index) {

            const studentProfileId =
                getStudentProfileId(row);


            /*
             * Kalau ID siswa tidak ada,
             * jangan masukkan row tersebut.
             */

            if (!studentProfileId) {

                console.warn(
                    `Student profile ID row ke-${index + 1} tidak ditemukan.`,
                    row
                );

                return;

            }


            const nilaiInput =
                row.querySelector('.nilai-input');


            const deskripsiInput =
                row.querySelector('.deskripsi-input');


            /*
             * Nilai.
             *
             * TIDAK menggunakan parseFloat().
             * TIDAK menggunakan Number().
             * TIDAK menggunakan validasi numeric.
             *
             * Jadi semua karakter diterima.
             */

            let nilai = null;


            if (nilaiInput) {

                nilai =
                    nilaiInput.value;

                /*
                 * Jika benar-benar kosong,
                 * kirim null.
                 */

                if (
                    nilai === null ||
                    nilai === undefined ||
                    nilai.trim() === ''
                ) {

                    nilai = null;

                }

            }


            /*
             * DESKRIPSI
             */

            let deskripsi = null;


            if (deskripsiInput) {

                deskripsi =
                    deskripsiInput.value;

                if (
                    deskripsi === null ||
                    deskripsi === undefined ||
                    deskripsi.trim() === ''
                ) {

                    deskripsi = null;

                }

            }


            members.push({

                student_profile_id:
                    studentProfileId,

                nilai:
                    nilai,

                deskripsi:
                    deskripsi

            });

        });


        return members;

    }


    /* ============================================================
     * DEBUG DATA
     * ============================================================ */

    function debugData(data) {

        console.log(
            '========================================'
        );

        console.log(
            'DATA NILAI YANG AKAN DIKIRIM'
        );

        console.log(
            'PERIOD ID:',
            PERIOD_ID
        );

        console.log(
            'EXTRACURRICULAR ID:',
            EXTRACURRICULAR_ID
        );

        console.log(
            'MEMBERS:',
            data
        );

        console.log(
            '========================================'
        );

    }


    /* ============================================================
     * SIMPAN SEMUA NILAI
     * ============================================================ */

    async function saveAllNilai() {

        /*
         * Cek period ID dari URL SEKALI LAGI.
         *
         * Ini penting supaya tidak bergantung
         * pada variabel lain.
         */

        const currentUrlParams =
            new URLSearchParams(
                window.location.search
            );


        const periodId =
            currentUrlParams.get(
                'period_id'
            );


        console.log(
            'Period ID saat save:',
            periodId
        );


        if (!periodId) {

            errorToast(
                'Period ID tidak ditemukan. Pastikan URL memiliki ?period_id=4'
            );

            return;

        }


        /*
         * Ambil data
         */

        const members =
            collectNilaiData();


        debugData(members);


        if (!members.length) {

            errorToast(
                'Tidak ada data siswa yang dapat disimpan.'
            );

            return;

        }


        /*
         * Konfirmasi
         */

        let confirmed = true;


        if (typeof Swal !== 'undefined') {

            const result =
                await Swal.fire({

                    icon: 'question',

                    title: 'Simpan perubahan?',

                    text:
                        `Perubahan nilai ${members.length} siswa akan disimpan.`,

                    showCancelButton: true,

                    confirmButtonText:
                        'Ya, Simpan',

                    cancelButtonText:
                        'Batal',

                    confirmButtonColor:
                        '#16a34a',

                    cancelButtonColor:
                        '#64748b'

                });


            confirmed =
                result.isConfirmed;

        }


        if (!confirmed) {

            return;

        }


        /* ========================================================
         * LOADING
         * ======================================================== */

        editButton.disabled = true;


        if (editButtonIcon) {

            editButtonIcon.className =
                'loading loading-spinner loading-sm mr-2';

        }


        if (editButtonText) {

            editButtonText.textContent =
                'Menyimpan...';

        }


        try {

            /*
             * Payload
             *
             * period_id DIKIRIM.
             */

            const payload = {

                period_id:
                    periodId,

                extracurricular_id:
                    EXTRACURRICULAR_ID,

                members:
                    members

            };


            console.log(
                'PAYLOAD UPDATE NILAI:',
                payload
            );


            /* ====================================================
             * REQUEST
             * ==================================================== */

            const response =
                await fetch(
                    NILAI_UPDATE_URL,
                    {

                        method:
                            'POST',

                        credentials:
                            'same-origin',

                        headers: {

                            'X-CSRF-TOKEN':
                                TOKEN,

                            'Accept':
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'

                        },

                        body:
                            JSON.stringify(
                                payload
                            )

                    }
                );


            /*
             * Ambil response
             */

            const responseText =
                await response.text();


            console.log(
                'HTTP STATUS:',
                response.status
            );

            console.log(
                'RAW RESPONSE:',
                responseText
            );


            let result;


            try {

                result =
                    JSON.parse(
                        responseText
                    );

            } catch (e) {

                throw new Error(
                    responseText ||
                    'Server tidak mengembalikan JSON.'
                );

            }


            console.log(
                'SERVER RESPONSE:',
                result
            );


            /*
             * HTTP ERROR
             */

            if (!response.ok) {

                /*
                 * Laravel validation errors
                 */

                if (
                    result.errors
                ) {

                    const firstError =
                        Object.values(
                            result.errors
                        )[0];

                    if (
                        Array.isArray(
                            firstError
                        )
                    ) {

                        throw new Error(
                            firstError[0]
                        );

                    }

                }


                throw new Error(
                    result.message ||
                    'Gagal menyimpan nilai.'
                );

            }


            /*
             * STATUS ERROR
             */

            if (
                result.status &&
                result.status !== 'success'
            ) {

                throw new Error(
                    result.message ||
                    'Gagal menyimpan nilai.'
                );

            }


            /* ====================================================
             * BERHASIL
             * ==================================================== */

            editMode = false;


            if (typeof Swal !== 'undefined') {

                await Swal.fire({

                    icon:
                        'success',

                    title:
                        'Berhasil',

                    text:
                        result.message ||
                        'Nilai berhasil diperbarui.',

                    timer:
                        1500,

                    showConfirmButton:
                        false

                });

            } else {

                successToast(
                    result.message ||
                    'Nilai berhasil diperbarui.'
                );

            }


            /*
             * Reload supaya data terbaru
             * diambil kembali dari database.
             */

            window.location.reload();


        } catch (error) {

            console.error(
                'UPDATE NILAI ERROR:',
                error
            );


            editButton.disabled =
                false;


            if (editButtonIcon) {

                editButtonIcon.className =
                    'fa-solid fa-floppy-disk mr-2';

            }


            if (editButtonText) {

                editButtonText.textContent =
                    'Simpan';

            }


            errorToast(
                error.message ||
                'Gagal menyimpan nilai.'
            );

        }

    }


    /* ============================================================
     * CLICK EDIT / SIMPAN
     * ============================================================ */

    if (
        typeof window.jQuery !== 'undefined'
    ) {

        $(document)
            .off(
                'click.editNilai',
                '#btnEditNilai'
            )
            .on(
                'click.editNilai',
                '#btnEditNilai',
                function (event) {

                    event.preventDefault();

                    console.log(
                        'BUTTON EDIT/SIMPAN NILAI DIKLIK'
                    );


                    if (!editMode) {

                        enterEditMode();

                    } else {

                        saveAllNilai();

                    }

                }
            );

    } else {

        /*
         * Fallback kalau jQuery tidak tersedia.
         */

        editButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();


                if (!editMode) {

                    enterEditMode();

                } else {

                    saveAllNilai();

                }

            }
        );

    }


    /* ============================================================
     * DEBUG AKHIR
     * ============================================================ */

    console.log(
        'Edit Nilai JavaScript berhasil dimuat.'
    );

    console.log(
        'Period ID dari URL:',
        PERIOD_ID
    );

    console.log(
        'Endpoint:',
        NILAI_UPDATE_URL
    );


})();

document.addEventListener('DOMContentLoaded', function () {

    const dropdown = document.getElementById(
        'semesterDropdown'
    );

    const button = document.getElementById(
        'semesterDropdownButton'
    );

    const panel = document.getElementById(
        'semesterDropdownPanel'
    );

    const search = document.getElementById(
        'semesterSearch'
    );

    const selectedText = document.getElementById(
        'semesterSelectedText'
    );

    const icon = document.getElementById(
        'semesterDropdownIcon'
    );

    const options = document.querySelectorAll(
        '.semester-option'
    );

    const noResult = document.getElementById(
        'semesterNoResult'
    );


    if (
        !dropdown ||
        !button ||
        !panel ||
        !search
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Buka / tutup dropdown
    |--------------------------------------------------------------------------
    */

    button.addEventListener('click', function (event) {

        event.stopPropagation();

        const isHidden = panel.classList.contains(
            'hidden'
        );

        panel.classList.toggle(
            'hidden'
        );

        if (isHidden) {

            search.value = '';

            filterSemester('');

            setTimeout(() => {
                search.focus();
            }, 50);

            icon.classList.remove(
                'fa-chevron-down'
            );

            icon.classList.add(
                'fa-chevron-up'
            );

        } else {

            icon.classList.remove(
                'fa-chevron-up'
            );

            icon.classList.add(
                'fa-chevron-down'
            );
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Search semester
    |--------------------------------------------------------------------------
    */

    search.addEventListener(
        'input',
        function () {

            filterSemester(
                this.value
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Filter
    |--------------------------------------------------------------------------
    */

    function filterSemester(keyword) {

    const query = keyword
        .toLowerCase()
        .trim();

    let visibleCount = 0;
    let matchCount = 0;

    options.forEach(function (option) {

        const searchText =
            option.dataset.search
                ?.toLowerCase()
                ?? '';

        const match =
            searchText.includes(query);

        if (match) {

            matchCount++;

            /*
            |--------------------------------------------------------------------------
            | Batasi tampilan maksimal 5
            |--------------------------------------------------------------------------
            */

            if (visibleCount < 5) {

                option.classList.remove(
                    'hidden'
                );

                visibleCount++;

            } else {

                option.classList.add(
                    'hidden'
                );
            }

        } else {

            option.classList.add(
                'hidden'
            );
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Tidak ada hasil
    |--------------------------------------------------------------------------
    */

    if (matchCount === 0) {

        noResult.classList.remove(
            'hidden'
        );

    } else {

        noResult.classList.add(
            'hidden'
        );
    }

}


    /*
    |--------------------------------------------------------------------------
    | Pilih semester
    |--------------------------------------------------------------------------
    */

    options.forEach(function (option) {

        option.addEventListener(
            'click',
            function () {

                const periodId =
                    this.dataset.value;

                const text =
                    this.querySelector(
                        'span'
                    )?.textContent.trim()
                    || 'Semester';


                /*
                |--------------------------------------------------------------------------
                | Update tampilan
                |--------------------------------------------------------------------------
                */

                selectedText.textContent =
                    text;


                /*
                |--------------------------------------------------------------------------
                | Tutup dropdown
                |--------------------------------------------------------------------------
                */

                panel.classList.add(
                    'hidden'
                );

                icon.classList.remove(
                    'fa-chevron-up'
                );

                icon.classList.add(
                    'fa-chevron-down'
                );


                /*
                |--------------------------------------------------------------------------
                | Pindah ke periode yang dipilih
                |--------------------------------------------------------------------------
                */

                if (!periodId) {
                    return;
                }


                const url =
                    new URL(
                        window.location.href
                    );

                url.searchParams.set(
                    'period_id',
                    periodId
                );


                /*
                |--------------------------------------------------------------------------
                | Reload halaman
                |--------------------------------------------------------------------------
                */

                window.location.href =
                    url.toString();

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Klik di luar dropdown
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            if (
                !dropdown.contains(
                    event.target
                )
            ) {

                panel.classList.add(
                    'hidden'
                );

                icon.classList.remove(
                    'fa-chevron-up'
                );

                icon.classList.add(
                    'fa-chevron-down'
                );
            }

        }
    );

});
</script>
@endif