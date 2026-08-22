@include('components/sidebar-beranda', [
    'headerSideNav' => 'Ekstrakurikuler'
])

@if(Auth::user()->role == 'Wakil Kesiswaan' || Auth::user()->role == 'Admin')

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

    <div class="mx-6 my-8">

        <div id="alert-success"></div>  

        <main
    id="container"
    data-role="{{ request()->route('role') }}"
    data-school-name="{{ request()->route('schoolName') }}"
    data-school-id="{{ request()->route('schoolId') }}">

            {{-- ================= HEADER ================= --}}

            <section class="mt-8">

                <div
                    class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] rounded-3xl p-8 text-white shadow-xl overflow-hidden relative">

                    <div class="absolute right-0 top-0 opacity-10">

                        <i class="fa-solid fa-users text-[220px] translate-x-8 -translate-y-4"></i>

                    </div>

                    <div
                        class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                        <div>

                            <h1 class="text-3xl font-bold">
                                Manajemen Ekstrakurikuler
                            </h1>

                            <p class="mt-2 text-slate-300 max-w-3xl">

                                Kelola seluruh ekstrakurikuler sekolah,
                                anggota ekstrakurikuler,
                                serta absensi setiap pertemuan.

                            </p>

                        </div>

                        <button onclick="modal_add_extracurricular.showModal()"
                            class="px-5 py-3 rounded-2xl bg-white text-slate-900 font-semibold hover:scale-105 transition cursor-pointer">

                            <i class="fa-solid fa-plus mr-2"></i>

                            Tambah Ekstrakurikuler

                        </button>

                    </div>

                </div>

            </section>

            {{-- ================= KPI ================= --}}

<section class="mt-8">

    <div id="kpi-content">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- =====================================================
                 TOTAL EKSKUL
                 ===================================================== --}}
            <div
                class="bg-linear-to-br from-blue-50 to-white
                       border border-blue-200 rounded-3xl p-6">

                <div class="flex justify-between items-start">

                    <div>

                        <p class="text-sm text-slate-500">
                            Jumlah Ekstrakurikuler
                        </p>

                        <h3
                            id="total-extracurricular"
                            class="text-3xl font-black mt-2 text-blue-700"
                        >
                            {{ $extracurriculars->count() }}
                        </h3>

                        <p class="text-xs text-slate-500 mt-2">
                            Total ekstrakurikuler aktif sekolah
                        </p>

                    </div>

                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-100
                               flex items-center justify-center"
                    >
                        <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                    </div>

                </div>

            </div>


            {{-- ================= TOTAL SISWA ================= --}}

<div
    onclick="openMemberKpiModal()"
    class="
        bg-linear-to-br from-green-50 to-white
        border border-green-200
        rounded-3xl
        p-6
        cursor-pointer
        hover:shadow-lg
        hover:-translate-y-1
        transition-all
        duration-200
    "
>

    <div class="flex justify-between items-start">

        <div>

            <p class="text-sm text-slate-500">
                Jumlah Anggota
            </p>

            <h3
                id="total-member"
                class="text-3xl font-black mt-2 text-green-700"
            >
                {{ $totalMember ?? 0 }}
            </h3>

            <p class="text-xs text-green-600 mt-2">
                Klik untuk melihat rincian anggota
            </p>

        </div>

        <div
            class="
                w-14 h-14
                rounded-2xl
                bg-green-100
                flex
                items-center
                justify-center
            "
        >

            <i class="fa-solid fa-user-group text-green-600 text-xl"></i>

        </div>

    </div>

</div>

{{-- ================= TOTAL PERTEMUAN ================= --}}

<div
    onclick="openMeetingKpiModal()"
    class="
        bg-linear-to-br from-orange-50 to-white
        border border-orange-200
        rounded-3xl
        p-6
        cursor-pointer
        hover:shadow-lg
        hover:-translate-y-1
        transition-all
        duration-200
    "
>

    <div class="flex justify-between items-start">

        <div>

            <p class="text-sm text-slate-500">
                Jumlah Pertemuan
            </p>

            <h3
                id="total-meeting"
                class="text-3xl font-black mt-2 text-orange-700"
            >
                {{ $totalMeeting ?? 0 }}
            </h3>

            <p class="text-xs text-orange-600 mt-2">
                Klik untuk melihat rincian pertemuan
            </p>

        </div>

        <div
            class="
                w-14 h-14
                rounded-2xl
                bg-orange-100
                flex
                items-center
                justify-center
            "
        >

            <i class="fa-solid fa-calendar-check text-orange-600 text-xl"></i>

        </div>

    </div>

</div>

        </div>

    </div>

</section>
                        {{-- ================= LIST EKSTRAKURIKULER ================= --}}

            <section class="mt-8">

                <div
                    class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

                    {{-- HEADER TABLE --}}

                    <div
                        class="p-6 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                        <div>

                            <h2 class="text-xl font-bold">

                                Daftar Ekstrakurikuler

                            </h2>

                            <p class="text-sm text-slate-500">

                                Kelola seluruh ekstrakurikuler sekolah beserta anggota dan absensinya.

                            </p>

                        </div>

                        <div class="flex items-center gap-3">

    <div class="relative">

        <input
            id="search_extracurricular"
            type="text"
            placeholder="Cari ekstrakurikuler..."
            class="input input-bordered w-80">

    </div>

    <button
        id="btnDeleteMode"
        type="button"
        class="btn btn-error">

        <i class="fa-solid fa-trash"></i>

        Hapus

    </button>

    <button
        id="btnDeleteSelected"
        type="button"
        class="btn btn-error hidden">

        <i class="fa-solid fa-trash"></i>

        Hapus (0)

    </button>

    <button
        id="btnCancelDelete"
        type="button"
        class="btn hidden">

        Batal

    </button>

</div>

                    </div>

                    {{-- ================= SKELETON ================= --}}

                    <div class="overflow-x-auto">

                        <div
                            id="extracurricular-skeleton"
                            class="hidden">

                            <table class="min-w-full">

                                <tbody id="extracurricular_table_body">

                                    @for($i=0;$i<7;$i++)

                                    <tr class="animate-pulse border-t border-slate-100">

                                        <td class="p-4">

                                            <div class="space-y-2">

                                                <div class="h-4 w-48 bg-slate-200 rounded"></div>

                                                <div class="h-3 w-24 bg-slate-100 rounded"></div>

                                            </div>

                                        </td>

                                        <td class="p-4">

                                            <div class="h-4 w-24 bg-slate-200 rounded mx-auto"></div>

                                        </td>

                                        <td class="p-4">

                                            <div class="h-4 w-16 bg-slate-200 rounded mx-auto"></div>

                                        </td>

                                        <td class="p-4">

                                            <div class="h-4 w-16 bg-slate-200 rounded mx-auto"></div>

                                        </td>

                                        <td class="p-4">

                                            <div class="h-6 w-20 bg-slate-200 rounded-full mx-auto"></div>

                                        </td>

                                        <td class="p-4">

                                            <div class="h-10 w-24 bg-slate-200 rounded-xl mx-auto"></div>

                                        </td>

                                    </tr>

                                    @endfor

                                </tbody>

                            </table>

                        </div>

                        {{-- ================= TABLE ================= --}}

                        <table
                            id="table-extracurricular"
                            class="min-w-full text-sm border-collapse">

                            <thead class="bg-slate-50">

                                <tr class="text-slate-600">
                                   
                                    <th
                                        id="selectHeader"
                                        class="hidden w-12">

                                    </th>

                                   <th class="p-4 text-left">
                                        Nama Ekstrakurikuler
                                    </th>

                                    <th class="p-4 text-center">
                                        Tipe
                                    </th>

                                    <th class="p-4 text-center">
                                        Jumlah Peserta
                                    </th>

                                    <th class="p-4 text-center">
                                        Detail Ekstrakurikuler
                                    </th>

                                    <th
                                        id="selectHeader"
                                        class="hidden w-20 text-center">

                                        Pilih

                                    </th>

                                </tr>

                            </thead>

                           <tbody id="extracurricular_table_body">

@forelse($extracurriculars as $item)

<tr class="hover:bg-slate-50 transition">

    <td class="p-5">

        <div class="font-semibold text-slate-800">

            {{ $item->name }}

        </div>

        <div class="text-sm text-slate-500">

            {{ $item->description }}

        </div>

    </td>

    <td class="text-center">

        @if($item->type == 'wajib')

            <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">

                WAJIB

            </span>

        @else

            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">

                PILIHAN

            </span>

        @endif

    </td>

    <td class="text-center">

        <span class="font-semibold text-green-600">

            {{ $item->students()->count() }} Siswa

        </span>

    </td>

    <td class="text-center">
    <div class="flex items-center justify-center gap-2">

        {{-- DETAIL --}}
        <a
            href="{{ route('lms.student-vice-principal.extracurricular-management.detail',[
                'role'=>Auth::user()->role,
                'schoolName'=>Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                'schoolId'=>Auth::user()->SchoolStaffProfile->SchoolPartner->id,
                'extracurricularId'=>$item->id
            ]) }}"
            class="px-5 py-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition"
        >
            Detail Ekskul
        </a>

        {{-- MENU --}}
        <div class="relative">
            <button
                type="button"
                onclick="toggleExtracurricularMenu({{ $item->id }})"
                class="w-9 h-9 rounded-full hover:bg-slate-100 transition flex items-center justify-center"
            >
                <i class="fa-solid fa-ellipsis-vertical text-slate-500"></i>
            </button>

            <div
                id="extracurricular-menu-{{ $item->id }}"
                class="hidden absolute right-0 top-10 z-50 w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden"
            >

                <button
                    type="button"
                    onclick='openEditExtracurricular(@json($item))'
                    class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50 flex items-center gap-3"
                >
                    <i class="fa-solid fa-pen text-blue-500"></i>
                    <span>Edit Ekskul</span>
                </button>

            </div>
        </div>

    </div>
</td>
<td class="selectColumn hidden w-10 p-1 text-center align-middle">

    <input
        type="checkbox"
        class="checkbox checkbox-error checkbox-sm extracurricular-checkbox"
        value="{{ $item->id }}">

</td>

</tr>

@empty

<tr>

<td colspan="4" class="py-12 text-center text-slate-400">

Belum ada ekstrakurikuler.

</td>

</tr>

@endforelse

</tbody>

                        </table>

                    </div>

                    {{-- PAGINATION --}}

                    <div
                        class="pagination-container flex justify-center py-5">

                    </div>

                    {{-- EMPTY STATE --}}

                    <div
                        id="empty-message"
                        class="hidden h-96 bg-slate-50 rounded-2xl border border-dashed border-slate-200">

                        <div
                            class="flex flex-col items-center justify-center h-full px-6">

                            <div
                                class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-4">

                                <i class="fas fa-users text-3xl text-blue-500"></i>

                            </div>

                            <h4
                                class="text-lg font-bold text-slate-700 text-center">

                                Belum Ada Ekstrakurikuler

                            </h4>

                            <p
                                class="text-sm text-slate-500 text-center max-w-md mt-2">

                                Tambahkan ekstrakurikuler terlebih dahulu agar
                                anggota dan absensi dapat dikelola.

                            </p>

                        </div>

                    </div>

                </div>

            </section>

            
                        {{-- ================= MODAL ================= --}}

@include(
'features.lms.student-vice-principal.extracurricular.components.modal-add-extracurricular'
)

{{-- @include('features.lms.student-vice-principal.extracurricular.components.modal-upload-members') --}}

{{-- @include('features.lms.student-vice-principal.extracurricular.components.modal-upload-attendance') --}}

        </main>

    </div>

</div>

{{-- ============================================================
     MODAL KPI ANGGOTA
============================================================ --}}

<dialog id="modalKpiMember" class="modal">

    <div
    class="
        modal-box
        max-w-6xl
        w-11/12
        rounded-3xl
        p-0
        overflow-hidden
        max-h-[90vh]
        flex
        flex-col
    "
>

        {{-- ================================================= --}}
        {{-- HEADER --}}
        {{-- ================================================= --}}

       <div
    class="text-white p-6"
    style="background: linear-gradient(90deg, #a3e635 0%, #34d399 100%);"
>
    <div class="flex justify-between items-start">

        <div>
            <div class="flex items-center gap-3">

                <div
                    class="
                        w-12 h-12
                        rounded-2xl
                        bg-white/20
                        flex items-center justify-center
                    "
                >
                    <i class="fa-solid fa-users text-xl"></i>
                </div>

                <div>
                    <h3 class="text-2xl font-black">
                        KPI Anggota
                    </h3>

                    <p class="text-sm text-green-100">
                        Rincian siswa yang mengikuti ekstrakurikuler
                    </p>
                </div>

            </div>
        </div>

        <button
            type="button"
            onclick="modalKpiMember.close()"
            class="
                w-10 h-10
                rounded-xl
                bg-white/20
                hover:bg-white/30
                transition
                flex items-center justify-center
            "
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>
</div>

        {{-- ================================================= --}}
        {{-- CONTENT --}}
        {{-- ================================================= --}}

        <div
    class="
        p-6
        space-y-6
        overflow-y-auto
        flex-1
        min-h-0
    "
>


            {{-- ================================================= --}}
            {{-- FILTER --}}
            {{-- ================================================= --}}

            <div
                class="
                    grid
                    grid-cols-1
                    md:grid-cols-3
                    gap-4
                    bg-slate-50
                    rounded-2xl
                    p-4
                "
            >


                {{-- ================================================= --}}
                {{-- MODE --}}
                {{-- ================================================= --}}

                <div>

                    <label
                        class="
                            text-sm
                            font-semibold
                            text-slate-600
                        "
                    >
                        Tampilan
                    </label>

                    <select
                        id="memberKpiMode"
                        class="
                            select
                            select-bordered
                            w-full
                            mt-1
                            rounded-xl
                        "
                    >

                        <option value="all">
                            Semua Ekstrakurikuler
                        </option>

                        <option value="extracurricular">
                            Berdasarkan Ekstrakurikuler
                        </option>

                    </select>

                </div>


                {{-- ================================================= --}}
                {{-- EKSTRAKURIKULER --}}
                {{-- ================================================= --}}

                <div id="memberKpiExtracurricularWrapper">

                    <label
                        class="
                            text-sm
                            font-semibold
                            text-slate-600
                        "
                    >
                        Ekstrakurikuler
                    </label>

                    <select
                        id="memberKpiExtracurricular"
                        class="
                            select
                            select-bordered
                            w-full
                            mt-1
                            rounded-xl
                        "
                    >

                        <option value="">
                            Semua Ekstrakurikuler
                        </option>


                        @foreach($extracurriculars as $extracurricular)

                            <option
                                value="{{ $extracurricular->id }}"
                            >
                                {{ $extracurricular->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ================================================= --}}
                {{-- KELAS --}}
                {{-- ================================================= --}}

                <div>

                    <label
                        class="
                            text-sm
                            font-semibold
                            text-slate-600
                        "
                    >
                        Kelas
                    </label>

                    <select
                        id="memberKpiClass"
                        class="
                            select
                            select-bordered
                            w-full
                            mt-1
                            rounded-xl
                        "
                    >

                        <option value="">
                            Semua Kelas
                        </option>

                    </select>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- SUMMARY --}}
            {{-- ================================================= --}}

            <div
                class="
                    grid
                    grid-cols-1
                    md:grid-cols-3
                    gap-4
                "
            >


                {{-- ================================================= --}}
                {{-- SISWA TERDAFTAR --}}
                {{-- ================================================= --}}

                <div
                    class="
                        rounded-2xl
                        border
                        border-green-200
                        bg-green-50
                        p-5
                    "
                >

                    <p class="text-sm text-green-700">
                        Siswa Terdaftar
                    </p>

                    <h3
                        id="memberKpiJoined"
                        class="
                            text-3xl
                            font-black
                            text-green-700
                            mt-1
                        "
                    >
                        0
                    </h3>

                </div>


                {{-- ================================================= --}}
                {{-- TOTAL SISWA --}}
                {{-- ================================================= --}}

                <div
                    class="
                        rounded-2xl
                        border
                        border-blue-200
                        bg-blue-50
                        p-5
                    "
                >

                    <p class="text-sm text-blue-700">
                        Total Siswa
                    </p>

                    <h3
                        id="memberKpiTotal"
                        class="
                            text-3xl
                            font-black
                            text-blue-700
                            mt-1
                        "
                    >
                        0
                    </h3>

                </div>


                {{-- ================================================= --}}
                {{-- PERSENTASE --}}
                {{-- ================================================= --}}

                <div
                    class="
                        rounded-2xl
                        border
                        border-purple-200
                        bg-purple-50
                        p-5
                    "
                >

                    <p class="text-sm text-purple-700">
                        Persentase
                    </p>

                    <h3
                        id="memberKpiPercentage"
                        class="
                            text-3xl
                            font-black
                            text-purple-700
                            mt-1
                        "
                    >
                        0%
                    </h3>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- DIAGRAM --}}
            {{-- ================================================= --}}

            <div
                class="
                    border
                    border-slate-200
                    rounded-2xl
                    p-5
                "
            >

                <div class="mb-4">

                    <h4
                        class="
                            font-bold
                            text-lg
                            text-slate-800
                        "
                    >
                        Distribusi Anggota
                    </h4>

                    <p
                        id="memberChartDescription"
                        class="
                            text-sm
                            text-slate-500
                        "
                    >
                        Jumlah siswa berdasarkan kelas
                    </p>

                </div>


                <div class="h-[400px]">

                    <canvas
                        id="memberKpiChart"
                    ></canvas>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- DETAIL KELAS --}}
            {{-- ================================================= --}}

            <div>

                <h4
                    class="
                        font-bold
                        text-lg
                        text-slate-800
                        mb-3
                    "
                >
                    Rincian Kelas
                </h4>


                <div
                    id="memberKpiClassList"
                    class="
                        space-y-2
                        max-h-[350px]
                        overflow-y-auto
                        pr-2
                    "
                >
                </div>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- FOOTER --}}
        {{-- ================================================= --}}

        <div
            class="
                p-5
                bg-slate-50
                border-t
                border-slate-200
                flex
                justify-end
            "
        >

            <button
                type="button"
                onclick="modalKpiMember.close()"
                class="
                    btn
                    rounded-xl
                "
            >
                Tutup
            </button>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- BACKDROP --}}
    {{-- ================================================= --}}

    <form
        method="dialog"
        class="modal-backdrop"
    >

        <button>
            close
        </button>

    </form>

</dialog>
{{-- ============================================================
     MODAL KPI PERTEMUAN
============================================================ --}}

<dialog id="modalKpiMeeting" class="modal">

    <div
        class="
            modal-box
            max-w-5xl
            w-11/12
            rounded-3xl
            p-0
            overflow-hidden
            max-h-[90vh]
            flex
            flex-col
        "
    >

        {{-- HEADER --}}

        <div
            class="
                bg-linear-to-r
                from-orange-500
                to-amber-500
                text-white
                p-6
            "
        >

            <div class="flex justify-between items-start">

                <div class="flex items-center gap-3">

                    <div
                        class="
                            w-12 h-12
                            rounded-2xl
                            bg-white/20
                            flex
                            items-center
                            justify-center
                        "
                    >

                        <i class="fa-solid fa-calendar-days text-xl"></i>

                    </div>

                    <div>

                        <h3 class="text-2xl font-black">
                            KPI Jumlah Pertemuan
                        </h3>

                        <p class="text-sm text-orange-100">
                            Rincian pertemuan ekstrakurikuler
                        </p>

                    </div>

                </div>

                <button
                    type="button"
                    onclick="modalKpiMeeting.close()"
                    class="
                        w-10 h-10
                        rounded-xl
                        bg-white/20
                        hover:bg-white/30
                    "
                >

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        </div>


        {{-- CONTENT --}}

        <div class="p-6 space-y-6 overflow-y-auto
        flex-1
        min-h-0">


            {{-- TOTAL --}}

            <div
                class="
                    bg-orange-50
                    border
                    border-orange-200
                    rounded-3xl
                    p-6
                "
            >

                <p class="text-sm text-orange-700">
                    Total Pertemuan Pada Sesi Ini
                </p>

                <div class="flex items-end gap-3">

                    <h3
                        id="meetingKpiTotal"
                        class="
                            text-5xl
                            font-black
                            text-orange-700
                            mt-2
                        "
                    >
                        0
                    </h3>

                    <span
                        class="
                            text-sm
                            text-orange-600
                            mb-2
                        "
                    >
                        pertemuan
                    </span>

                </div>

            </div>


            {{-- DIAGRAM --}}

            <div
                class="
                    border
                    border-slate-200
                    rounded-2xl
                    p-5
                "
            >

                <h4 class="font-bold text-lg text-slate-800">
                    Pertemuan Per Ekstrakurikuler
                </h4>

                <p class="text-sm text-slate-500 mb-4">
                    Jumlah pertemuan masing-masing ekstrakurikuler
                </p>

                <div class="h-[400px]">

                    <canvas id="meetingKpiChart"></canvas>

                </div>

            </div>


            {{-- RINCIAN --}}

            <div>

                <h4 class="font-bold text-lg text-slate-800 mb-3">
                    Rincian Ekstrakurikuler
                </h4>

                <div
                    id="meetingKpiList"
                    class="space-y-3"
                >
                </div>

            </div>

        </div>


        {{-- FOOTER --}}

        <div
            class="
                p-5
                bg-slate-50
                border-t
                border-slate-200
                flex
                justify-end
            "
        >

            <button
                type="button"
                onclick="modalKpiMeeting.close()"
                class="btn rounded-xl"
            >
                Tutup
            </button>

        </div>

    </div>


    <form method="dialog" class="modal-backdrop">

        <button>
            close
        </button>

    </form>

</dialog>   
{{-- ========================================================= --}}
{{-- MODAL EDIT EKSTRAKURIKULER --}}
{{-- ========================================================= --}}

<div
    id="modal-edit-extracurricular"
    class="hidden fixed inset-0 z-[9999] bg-black/50 items-center justify-center p-4"
>

    <div
        class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden"
    >

        {{-- HEADER --}}
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">

            <div>
                <h3 class="text-xl font-bold text-slate-800">
                    Edit Ekstrakurikuler
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    Ubah informasi ekstrakurikuler
                </p>
            </div>

            <button
                type="button"
                onclick="closeEditExtracurricular()"
                class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center"
            >
                <i class="fa-solid fa-xmark text-slate-500"></i>
            </button>

        </div>


        {{-- FORM --}}
        <form id="form-edit-extracurricular">

            @csrf

            <input
                type="hidden"
                id="edit_extracurricular_id"
            >

            <div class="p-6 space-y-5">

                {{-- NAMA --}}
                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Ekstrakurikuler
                    </label>

                    <input
                        type="text"
                        id="edit_extracurricular_name"
                        class="input input-bordered w-full rounded-xl"
                        required
                    >

                </div>


                {{-- DESKRIPSI --}}
                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Deskripsi
                    </label>

                    <textarea
                        id="edit_extracurricular_description"
                        rows="3"
                        class="textarea textarea-bordered w-full rounded-xl"
                        placeholder="Masukkan deskripsi ekstrakurikuler..."
                    ></textarea>

                </div>


                {{-- TIPE --}}
                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Tipe Ekstrakurikuler
                    </label>

                    <select
                        id="edit_extracurricular_type"
                        class="select select-bordered w-full rounded-xl"
                        required
                    >

                        <option value="wajib">
                            Wajib
                        </option>

                        <option value="pilihan">
                            Pilihan
                        </option>

                    </select>

                </div>


                {{-- PEMBINA --}}
                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Pembina
                    </label>

                    <input
                        type="text"
                        id="edit_extracurricular_coach"
                        class="input input-bordered w-full rounded-xl"
                        placeholder="Nama pembina"
                    >

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">

                <button
                    type="button"
                    onclick="closeEditExtracurricular()"
                    class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-600 hover:bg-slate-100"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    id="btn-save-edit-extracurricular"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                >
                    <i class="fa-solid fa-save mr-2"></i>
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

/* ============================================================
   EXTRACURRICULAR MANAGEMENT
   PART 1
============================================================ */

const STORE_URL = $('#form_add_extracurricular').attr('action');
const IMPORT_URL = $('#form_add_extracurricular').data('import-url');

function appendExtracurricular(row) {

    if (!row) return;

    $('#extracurricular_table_body').prepend(row);

}

function updateTotalExtracurricular() {

    let total = parseInt(
        $('#total-extracurricular').text()
    );

    if (isNaN(total)) total = 0;

    $('#total-extracurricular').text(
        total + 1
    );

}

function resetSubmitButton(form) {

    $(form)
        .find('button[type="submit"]')
        .prop('disabled', false)
        .html(`
            <i class="fa-solid fa-plus"></i>
            Simpan
        `);

}

function loadingSubmitButton(form) {

    $(form)
        .find('button[type="submit"]')
        .prop('disabled', true)
        .html(`
            <span class="loading loading-spinner loading-sm"></span>
            Menyimpan...
        `);

}

$(document).ready(function () {

    const form = $('#form_add_extracurricular');

    if (!form.length) return;

    $(document).on(
        'submit',
        '#form_add_extracurricular',
        function (e) {

            e.preventDefault();

            const form = this;

            const formData = new FormData(form);

            let url = STORE_URL;

            const isImport =
                $('#excel_file')[0].files.length > 0;

            if (isImport) {

                url = IMPORT_URL;

            }

            $.ajax({

                url: url,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                beforeSend: function () {

                    loadingSubmitButton(form);

                },

               success: function (res) {

    // Reset form
    form.reset();

    // Tutup modal
    const modal = document.getElementById('modal_add_extracurricular');
    if (modal && modal.open) {
        modal.close();
    }

    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: res.message,
        timer: 1500,
        showConfirmButton: false
    });

    if (!isImport && res.row) {
        appendExtracurricular(res.row);
        updateTotalExtracurricular();
    }

    setTimeout(function () {
        location.reload();
    }, 1500);

},

                error: function (xhr) {

                    console.log(xhr);

                    let message =
                        'Terjadi kesalahan server.';

                    if (xhr.status === 422) {

                        message = '';

                        $.each(
                            xhr.responseJSON.errors,
                            function (k, v) {

                                message +=
                                    v[0] + "<br>";

                            }
                        );

                    }
                    else if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
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

                    resetSubmitButton(form);

                }

            });

        }

    );

});

/* ============================================================
   EXCEL FILE NAME
============================================================ */

$(document).on(
    'change',
    '#excel_file',
    function () {

        const text =
            $('#excel_file_name');

        if (this.files.length) {

            text.text(
                this.files[0].name
            );

        } else {

            text.text('');

        }

    }
);

/* ============================================================
   PAGINATION + KPI + TABLE
============================================================ */

let extracurricularLoaded = false;

function paginateExtracurricular(page = 1, loadKpi = false) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    $.ajax({

        url: `/lms/${role}/${schoolName}/${schoolId}/extracurricular-management/paginate`,

        method: "GET",

        data: {
            page: page
        },

        beforeSend: function () {

            if (loadKpi) {

                $('#kpi-skeleton').removeClass('hidden');
                $('#kpi-content').addClass('hidden');

            }

            $('#table-extracurricular').addClass('hidden');
            $('#extracurricular-skeleton').removeClass('hidden');

        },

        success: function (response) {

            $('#extracurricular-skeleton').addClass('hidden');
            $('#table-extracurricular').removeClass('hidden');

            if (loadKpi) {

                $('#kpi-skeleton').addClass('hidden');
                $('#kpi-content').removeClass('hidden');

                renderExtracurricularKpi(response.kpi);

            }

            renderExtracurricularList(

                response.data ?? [],
                response.links ?? '',
                response.detailRoute ?? '',
                response.current_page ?? 1

            );

        },

        error: function (xhr) {

            console.log(xhr);

            $('#extracurricular-skeleton').addClass('hidden');
            $('#table-extracurricular').removeClass('hidden');

            $('#kpi-skeleton').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            Swal.fire({

                icon: 'error',

                title: 'Gagal',

                text: 'Tidak dapat memuat data.'

            });

        }

    });

}

/* ============================================================
   KPI
============================================================ */

function renderExtracurricularKpi(kpi) {

    $('#total-extracurricular').text(
        kpi.total_extracurricular ?? 0
    );

    $('#total-member').text(
        kpi.total_member ?? 0
    );

    $('#total-meeting').text(
        kpi.total_meeting ?? 0
    );

}

/* ============================================================
   TABLE
============================================================ */

function renderExtracurricularList(

    data,
    links,
    detailRoute,
    currentPage

) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    $('#extracurricular_table_body').empty();

    $('.pagination-container').empty();

    if (!data || data.length === 0) {

        $('#empty-message').removeClass('hidden');

        return;

    }

    $('#empty-message').addClass('hidden');

    $.each(data, function (index, item) {

        let detail = '#';

        if (detailRoute) {

            detail = detailRoute
                .replace(':role', role)
                .replace(':schoolName', schoolName)
                .replace(':schoolId', schoolId)
                .replace(':extracurricularId', item.id);

        }

        let badge = item.type === 'wajib'
            ? `<span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">
                    WAJIB
               </span>`
            : `<span class="px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">
                    PILIHAN
               </span>`;

        $('#extracurricular_table_body').append(`

<tr class="hover:bg-slate-50 transition">

    <td class="p-5">

        <div class="font-semibold text-slate-800">
            ${item.name}
        </div>

        <div class="text-sm text-slate-500">
            ${item.description ?? ''}
        </div>

    </td>

    <td class="text-center">

        ${badge}

    </td>

    <td class="text-center">

        <span class="font-semibold text-green-600">

            ${item.students_count ?? 0} Siswa

        </span>

    </td>

    <td class="text-center">

        <a

            href="${detail}"

            class="px-5 py-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition">

            Detail Ekskul

        </a>

    </td>

</tr>

        `);

    });

    $('.pagination-container').html(links);

    bindPaginationLinks();

}

/* ============================================================
   PAGINATION LINK
============================================================ */

function bindPaginationLinks() {

    $('.pagination-container')

        .off('click', 'a')

        .on('click', 'a', function (e) {

            e.preventDefault();

            let page = new URL(
                this.href
            ).searchParams.get('page');

            paginateExtracurricular(
                page,
                false
            );

        });

}

/* ============================================================
   SEARCH
============================================================ */

let searchTimer = null;

$('#search_extracurricular').on('keyup', function () {

    clearTimeout(searchTimer);

    searchTimer = setTimeout(function () {

        const keyword = $('#search_extracurricular')
            .val()
            .toLowerCase()
            .trim();

        $('#extracurricular_table_body tr').each(function () {

            const text = $(this)
                .text()
                .toLowerCase();

            $(this).toggle(
                text.indexOf(keyword) > -1
            );

        });

        let visible = $('#extracurricular_table_body tr:visible').length;

        if (visible === 0) {

            $('#empty-message').removeClass('hidden');

        } else {

            $('#empty-message').addClass('hidden');

        }

    }, 300);

});

/* ============================================================
   REFRESH LIST
============================================================ */

function refreshExtracurricular() {

    paginateExtracurricular(
        1,
        true
    );

}

/* ============================================================
   ALERT
============================================================ */

function successAlert(message) {

    Swal.fire({

        icon: 'success',

        title: 'Berhasil',

        text: message,

        timer: 1800,

        showConfirmButton: false

    });

}

function errorAlert(message) {

    Swal.fire({

        icon: 'error',

        title: 'Gagal',

        html: message

    });

}

/* ============================================================
   DOCUMENT READY
============================================================ */

$(document).ready(function () {

    paginateExtracurricular(
        1,
        true
    );

});

/* ============================================================
   OPTIONAL GLOBAL HELPERS
============================================================ */

window.refreshExtracurricular = refreshExtracurricular;
window.paginateExtracurricular = paginateExtracurricular;
window.renderExtracurricularList = renderExtracurricularList;
window.renderExtracurricularKpi = renderExtracurricularKpi;
window.appendExtracurricular = appendExtracurricular;

let deleteMode = false;

const btnDeleteMode = document.getElementById('btnDeleteMode');
const btnDeleteSelected = document.getElementById('btnDeleteSelected');
const btnCancelDelete = document.getElementById('btnCancelDelete');

btnDeleteMode.addEventListener('click', function () {

    deleteMode = true;

    document
        .querySelectorAll('.selectColumn')
        .forEach(td => td.classList.remove('hidden'));

    document
        .getElementById('selectHeader')
        .classList.remove('hidden');

    btnDeleteMode.classList.add('hidden');

    btnDeleteSelected.classList.remove('hidden');

    btnCancelDelete.classList.remove('hidden');

});

btnCancelDelete.addEventListener('click', function () {

    deleteMode = false;

    document
        .querySelectorAll('.selectColumn')
        .forEach(td => td.classList.add('hidden'));

    document
        .getElementById('selectHeader')
        .classList.add('hidden');

    document
        .querySelectorAll('.extracurricular-checkbox')
        .forEach(cb => cb.checked = false);

    btnDeleteMode.classList.remove('hidden');

    btnDeleteSelected.classList.add('hidden');

    btnCancelDelete.classList.add('hidden');

    btnDeleteSelected.innerHTML = `
        <i class="fa-solid fa-trash"></i>
        Hapus (0)
    `;

});

document.addEventListener('change', function(e){

    if(!e.target.classList.contains('extracurricular-checkbox'))
        return;

    const total = document.querySelectorAll(
        '.extracurricular-checkbox:checked'
    ).length;

    btnDeleteSelected.innerHTML = `
        <i class="fa-solid fa-trash"></i>
        Hapus (${total})
    `;

});

btnDeleteSelected.addEventListener('click', async function(){

    const checked = document.querySelectorAll(
        '.extracurricular-checkbox:checked'
    );

    if(checked.length === 0){
        Swal.fire({
            icon:'warning',
            title:'Pilih data terlebih dahulu.'
        });
        return;
    }

    const confirmDelete = await Swal.fire({
        icon:'warning',
        title:'Hapus Data?',
        text:`Yakin ingin menghapus ${checked.length} data?`,
        showCancelButton:true,
        confirmButtonText:'Ya, Hapus',
        cancelButtonText:'Batal'
    });

    if(!confirmDelete.isConfirmed) return;

    for(const item of checked){

        const response = await fetch(
    DELETE_URL.replace(':id', item.value),
    {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }
);

        console.log(response.status);

        const result = await response.json();

        console.log(result);
    }

    Swal.fire({
        icon:'success',
        title:'Berhasil',
        text:'Data berhasil dihapus.'
    }).then(()=>{
        location.reload();
    });

});
</script>

<script>

const DELETE_URL = "{{ route(
'lms.student-vice-principal.extracurricular-management.delete',
[
'role'=>request()->route('role'),
'schoolName'=>request()->route('schoolName'),
'schoolId'=>request()->route('schoolId'),
'id'=>':id'
]
) }}";

</script>

{{-- -diagram dan modal --}}
<script>(function () {

    'use strict';


    /* =========================================================
       CONFIG
    ========================================================= */

    const KPI_CONTAINER =
        document.getElementById('container');


    if (!KPI_CONTAINER) {

        console.error(
            'Element #container tidak ditemukan.'
        );

        return;
    }


    const ROLE =
        KPI_CONTAINER.dataset.role || '';

    const SCHOOL_NAME =
        KPI_CONTAINER.dataset.schoolName || '';

    const SCHOOL_ID =
        KPI_CONTAINER.dataset.schoolId || '';


    const BASE_URL =
        `/lms/${encodeURIComponent(ROLE)}` +
        `/${encodeURIComponent(SCHOOL_NAME)}` +
        `/${encodeURIComponent(SCHOOL_ID)}` +
        `/extracurricular-management`;


    const MEMBER_KPI_URL =
        `${BASE_URL}/kpi/member`;


    const MEETING_KPI_URL =
        `${BASE_URL}/kpi/meeting`;


    console.log(
        '======================================'
    );

    console.log(
        'EXTRACURRICULAR KPI CONFIG'
    );

    console.log(
        'ROLE:',
        ROLE
    );

    console.log(
        'SCHOOL NAME:',
        SCHOOL_NAME
    );

    console.log(
        'SCHOOL ID:',
        SCHOOL_ID
    );

    console.log(
        'BASE URL:',
        BASE_URL
    );

    console.log(
        'MEMBER KPI URL:',
        MEMBER_KPI_URL
    );

    console.log(
        'MEETING KPI URL:',
        MEETING_KPI_URL
    );

    console.log(
        '======================================'
    );


    /* =========================================================
       CHART
    ========================================================= */

    let memberChart = null;

    let meetingChart = null;


    /* =========================================================
       MODAL
    ========================================================= */

    const memberModal =
        document.getElementById(
            'modalKpiMember'
        );


    const meetingModal =
        document.getElementById(
            'modalKpiMeeting'
        );


    /* =========================================================
       OPEN MEMBER
    ========================================================= */

    window.openMemberKpiModal =
        function () {

            console.log(
                'OPEN MEMBER KPI MODAL'
            );


            if (!memberModal) {

                console.error(
                    'modalKpiMember tidak ditemukan.'
                );

                return;
            }


            if (
                typeof memberModal.showModal ===
                'function'
            ) {

                memberModal.showModal();

            } else {

                memberModal.classList
                    .remove('hidden');

            }


            loadMemberKpi();

        };


    /* =========================================================
       CLOSE MEMBER
    ========================================================= */

    window.closeMemberKpiModal =
        function () {

            if (!memberModal) {
                return;
            }


            if (
                typeof memberModal.close ===
                'function'
            ) {

                memberModal.close();

            } else {

                memberModal.classList
                    .add('hidden');

            }

        };


    /* =========================================================
       OPEN MEETING
    ========================================================= */

    window.openMeetingKpiModal =
        function () {

            console.log(
                'OPEN MEETING KPI MODAL'
            );


            if (!meetingModal) {

                console.error(
                    'modalKpiMeeting tidak ditemukan.'
                );

                return;
            }


            if (
                typeof meetingModal.showModal ===
                'function'
            ) {

                meetingModal.showModal();

            } else {

                meetingModal.classList
                    .remove('hidden');

            }


            loadMeetingKpi();

        };


    /* =========================================================
       CLOSE MEETING
    ========================================================= */

    window.closeMeetingKpiModal =
        function () {

            if (!meetingModal) {
                return;
            }


            if (
                typeof meetingModal.close ===
                'function'
            ) {

                meetingModal.close();

            } else {

                meetingModal.classList
                    .add('hidden');

            }

        };


    /* =========================================================
       LOAD MEMBER KPI
    ========================================================= */

    function loadMemberKpi() {

        const mode =
            document.getElementById(
                'memberKpiMode'
            )?.value || 'all';


        const extracurricularId =
            document.getElementById(
                'memberKpiExtracurricular'
            )?.value || '';


        const tipeKelas =
            document.getElementById(
                'memberKpiClass'
            )?.value || '';


        console.log(
            '================================'
        );

        console.log(
            'LOAD MEMBER KPI'
        );

        console.log(
            'URL:',
            MEMBER_KPI_URL
        );

        console.log(
            'MODE:',
            mode
        );

        console.log(
            'EXTRACURRICULAR:',
            extracurricularId
        );

        console.log(
            'TIPE KELAS:',
            tipeKelas
        );

        console.log(
            '================================'
        );


        $('#memberKpiJoined')
            .text('...');

        $('#memberKpiTotal')
            .text('...');

        $('#memberKpiPercentage')
            .text('...');


        $.ajax({

            url:
                MEMBER_KPI_URL,

            method:
                'GET',

            dataType:
                'json',

            data: {

                mode:
                    mode,

                extracurricular_id:
                    extracurricularId,

                tipe_kelas:
                    tipeKelas

            },


            success:
                function (response) {

                    console.log(
                        '===== MEMBER KPI RESPONSE ====='
                    );

                    console.log(
                        response
                    );


                    if (
                        !response ||
                        response.status ===
                        'error'
                    ) {

                        console.error(
                            'Response KPI tidak valid:',
                            response
                        );

                        renderMemberEmpty();

                        return;
                    }


                    /* =================================================
                       SUMMARY
                    ================================================= */

                    const joined =
                        Number(
                            response.joined ??
                            0
                        );


                    const total =
                        Number(
                            response.total ??
                            0
                        );


                    const percentage =
                        Number(
                            response.percentage ??
                            0
                        );


                    $('#memberKpiJoined')
                        .text(joined);


                    $('#memberKpiTotal')
                        .text(total);


                    $('#memberKpiPercentage')
                        .text(
                            Math.round(
                                percentage
                            ) + '%'
                        );


                    /* =================================================
                       DATA TIPE KELAS
                    ================================================= */

                    const classes =
                        Array.isArray(
                            response.classes
                        )
                            ? response.classes
                            : [];


                    console.log(
                        'DATA TIPE KELAS:',
                        classes
                    );


                    console.log(
                        'JUMLAH DATA TIPE KELAS:',
                        classes.length
                    );


                    /* =================================================
                       DETAIL
                    ================================================= */

                    renderMemberClasses(
                        classes
                    );


                    /* =================================================
                       DROPDOWN
                    ================================================= */

                    updateMemberClassDropdown(
                        classes
                    );


                    /* =================================================
                       CHART
                    ================================================= */

                    renderMemberChart(
                        classes
                    );

                },


            error:
                function (xhr) {

                    console.error(
                        '================================'
                    );

                    console.error(
                        'MEMBER KPI ERROR'
                    );

                    console.error(
                        'STATUS:',
                        xhr.status
                    );

                    console.error(
                        'URL:',
                        MEMBER_KPI_URL
                    );

                    console.error(
                        'RESPONSE:',
                        xhr.responseText
                    );

                    console.error(
                        '================================'
                    );


                    renderMemberEmpty();

                }

        });

    }


    /* =========================================================
       EMPTY MEMBER
    ========================================================= */

    function renderMemberEmpty() {

        $('#memberKpiJoined')
            .text('0');

        $('#memberKpiTotal')
            .text('0');

        $('#memberKpiPercentage')
            .text('0%');


        renderMemberClasses([]);

        updateMemberClassDropdown([]);

        renderMemberChart([]);

    }


    /* =========================================================
       GET TIPE KELAS
    ========================================================= */

    function getTipeKelas(item) {

        if (!item) {
            return '';
        }


        return String(
            item.tipe_kelas ?? ''
        ).trim();

    }


    /* =========================================================
       DROPDOWN TIPE KELAS
    ========================================================= */

    function updateMemberClassDropdown(
        classes
    ) {

        const select =
            document.getElementById(
                'memberKpiClass'
            );


        if (!select) {

            console.warn(
                '#memberKpiClass tidak ditemukan.'
            );

            return;
        }


        const currentValue =
            select.value;


        select.innerHTML = `
            <option value="">
                Semua Tipe Kelas
            </option>
        `;


        if (
            !Array.isArray(classes) ||
            classes.length === 0
        ) {

            return;
        }


        const types =
            new Set();


        classes.forEach(
            function (item) {

                const tipeKelas =
                    getTipeKelas(item);


                if (!tipeKelas) {
                    return;
                }


                types.add(
                    tipeKelas
                );

            }
        );


        Array.from(types)
            .sort()
            .forEach(
                function (tipeKelas) {

                    const option =
                        document.createElement(
                            'option'
                        );


                    option.value =
                        tipeKelas;


                    option.textContent =
                        tipeKelas;


                    select.appendChild(
                        option
                    );

                }
            );


        if (
            currentValue &&
            Array.from(
                select.options
            ).some(
                function (option) {

                    return (
                        option.value ===
                        currentValue
                    );

                }
            )
        ) {

            select.value =
                currentValue;

        }

    }


    /* =========================================================
       DETAIL TIPE KELAS
    ========================================================= */

    function renderMemberClasses(
        classes
    ) {

        const container =
            document.getElementById(
                'memberKpiClassList'
            );


        if (!container) {

            console.warn(
                '#memberKpiClassList tidak ditemukan.'
            );

            return;
        }


        if (
            !Array.isArray(classes) ||
            classes.length === 0
        ) {

            container.innerHTML = `
                <div class="
                    p-5
                    text-center
                    text-slate-400
                    bg-slate-50
                    rounded-2xl
                ">
                    Belum ada data tipe kelas.
                </div>
            `;

            return;
        }


        container.innerHTML =
            classes
                .map(
                    function (item) {

                        const tipeKelas =
                            getTipeKelas(
                                item
                            ) ||
                            'Tidak Ada Tipe Kelas';


                        const joined =
                            Number(
                                item.joined ??
                                0
                            );


                        const total =
                            Number(
                                item.total ??
                                0
                            );


                        const percent =
                            total > 0
                                ? Math.round(
                                    (
                                        joined /
                                        total
                                    ) * 100
                                )
                                : 0;


                        return `

                            <div class="
                                border
                                border-slate-200
                                rounded-2xl
                                p-4
                                bg-white
                            ">

                                <div class="
                                    flex
                                    justify-between
                                    items-center
                                    gap-4
                                ">

                                    <div>

                                        <p class="
                                            font-bold
                                            text-slate-800
                                        ">
                                            ${escapeHtml(
                                                tipeKelas
                                            )}
                                        </p>

                                        <p class="
                                            text-sm
                                            text-slate-500
                                            mt-1
                                        ">
                                            ${joined}
                                            dari
                                            ${total}
                                            siswa
                                        </p>

                                    </div>


                                    <div class="
                                        text-right
                                    ">

                                        <p class="
                                            font-black
                                            text-green-600
                                        ">
                                            ${joined}/${total}
                                        </p>

                                        <p class="
                                            text-xs
                                            text-slate-400
                                        ">
                                            ${percent}%
                                        </p>

                                    </div>

                                </div>


                                <div class="
                                    mt-3
                                    h-2
                                    bg-slate-100
                                    rounded-full
                                    overflow-hidden
                                ">

                                    <div class="
                                        h-full
                                        bg-green-500
                                        rounded-full
                                    "
                                    style="
                                        width:${percent}%
                                    "></div>

                                </div>

                            </div>

                        `;

                    }
                )
                .join('');

    }


    /* =========================================================
       MEMBER CHART
       BERDASARKAN TIPE KELAS
    ========================================================= */

    function renderMemberChart(
        classes
    ) {

        const canvas =
            document.getElementById(
                'memberKpiChart'
            );


        if (!canvas) {

            console.warn(
                '#memberKpiChart tidak ditemukan.'
            );

            return;
        }


        if (memberChart) {

            memberChart.destroy();

            memberChart =
                null;

        }


        if (
            !Array.isArray(classes) ||
            classes.length === 0
        ) {

            console.log(
                'Chart member tidak memiliki data tipe kelas.'
            );

            return;
        }


        const labels =
            classes.map(
                function (item) {

                    return (
                        getTipeKelas(
                            item
                        ) ||
                        'Tidak Ada'
                    );

                }
            );


        const values =
            classes.map(
                function (item) {

                    return Number(
                        item.joined ??
                        0
                    );

                }
            );


        console.log(
            'CHART LABEL TIPE KELAS:',
            labels
        );


        console.log(
            'CHART VALUE:',
            values
        );


        if (
            typeof Chart ===
            'undefined'
        ) {

            console.error(
                'Chart.js belum dimuat.'
            );

            return;
        }


        memberChart =
            new Chart(
                canvas.getContext('2d'),
                {

                    type:
                        'bar',

                    data: {

                        labels:
                            labels,

                        datasets: [

                            {

                                label:
                                    'Siswa Mengikuti Ekskul',

                                data:
                                    values,

                                borderWidth:
                                    0,

                                borderRadius:
                                    8,

                                barThickness:
                                    35

                            }

                        ]

                    },


                    options: {

                        responsive:
                            true,

                        maintainAspectRatio:
                            false,


                        plugins: {

                            legend: {

                                display:
                                    false

                            },


                            tooltip: {

                                callbacks: {

                                    label:
                                        function (
                                            context
                                        ) {

                                            return (
                                                ' ' +
                                                context
                                                    .parsed
                                                    .y +
                                                ' siswa'
                                            );

                                        }

                                }

                            }

                        },


                        scales: {

                            x: {

                                grid: {

                                    display:
                                        false

                                },

                                ticks: {

                                    autoSkip:
                                        false

                                }

                            },


                            y: {

                                beginAtZero:
                                    true,

                                suggestedMax:
                                    Math.max(
                                        ...values,
                                        1
                                    ) + 1,

                                ticks: {

                                    precision:
                                        0,

                                    stepSize:
                                        1

                                }

                            }

                        }

                    }

                }
            );

    }


    /* =========================================================
       MEETING KPI
    ========================================================= */

    function loadMeetingKpi() {

        $('#meetingKpiTotal')
            .text('...');


        $.ajax({

            url:
                MEETING_KPI_URL,

            method:
                'GET',

            dataType:
                'json',


            success:
                function (response) {

                    console.log(
                        'MEETING KPI RESPONSE:',
                        response
                    );


                    const total =
                        Number(
                            response.total_meeting ??
                            response.total ??
                            0
                        );


                    $('#meetingKpiTotal')
                        .text(total);


                    const items =
                        response.extracurriculars ??
                        response.data ??
                        [];


                    renderMeetingList(
                        items
                    );


                    renderMeetingChart(
                        items
                    );

                },


            error:
                function (xhr) {

                    console.error(
                        'MEETING KPI ERROR:',
                        xhr.status,
                        xhr.responseText
                    );


                    $('#meetingKpiTotal')
                        .text('0');


                    renderMeetingList([]);

                    renderMeetingChart([]);

                }

        });

    }


    /* =========================================================
       MEETING LIST
    ========================================================= */

    function renderMeetingList(
        items
    ) {

        const container =
            document.getElementById(
                'meetingKpiList'
            );


        if (!container) {
            return;
        }


        if (
            !Array.isArray(items) ||
            items.length === 0
        ) {

            container.innerHTML = `
                <div class="
                    p-5
                    text-center
                    text-slate-400
                    bg-slate-50
                    rounded-2xl
                ">
                    Belum ada pertemuan pada sesi ini.
                </div>
            `;

            return;
        }


        container.innerHTML =
            items
                .map(
                    function (item) {

                        const name =
                            item.name ??
                            item.extracurricular_name ??
                            '-';


                        const total =
                            Number(
                                item.total_meeting ??
                                item.meetings ??
                                item.total ??
                                0
                            );


                        return `

                            <div class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                p-4
                                border
                                border-slate-200
                                rounded-2xl
                                bg-white
                            ">

                                <div class="
                                    flex
                                    items-center
                                    gap-3
                                ">

                                    <div class="
                                        w-10
                                        h-10
                                        rounded-xl
                                        bg-orange-100
                                        text-orange-600
                                        flex
                                        items-center
                                        justify-center
                                    ">

                                        <i class="
                                            fa-solid
                                            fa-calendar-check
                                        "></i>

                                    </div>


                                    <div>

                                        <p class="
                                            font-bold
                                            text-slate-800
                                        ">
                                            ${escapeHtml(name)}
                                        </p>

                                        <p class="
                                            text-xs
                                            text-slate-400
                                        ">
                                            Jumlah pertemuan
                                        </p>

                                    </div>

                                </div>


                                <div class="
                                    text-xl
                                    font-black
                                    text-orange-600
                                ">
                                    ${total}
                                </div>

                            </div>

                        `;

                    }
                )
                .join('');

    }


    /* =========================================================
       MEETING CHART
    ========================================================= */

    function renderMeetingChart(
        items
    ) {

        const canvas =
            document.getElementById(
                'meetingKpiChart'
            );


        if (!canvas) {
            return;
        }


        if (meetingChart) {

            meetingChart.destroy();

            meetingChart =
                null;

        }


        if (
            !Array.isArray(items) ||
            items.length === 0
        ) {

            return;
        }


        const labels =
            items.map(
                function (item) {

                    return (
                        item.name ??
                        item.extracurricular_name ??
                        '-'
                    );

                }
            );


        const values =
            items.map(
                function (item) {

                    return Number(
                        item.total_meeting ??
                        item.meetings ??
                        item.total ??
                        0
                    );

                }
            );


        if (
            typeof Chart ===
            'undefined'
        ) {

            console.error(
                'Chart.js belum dimuat.'
            );

            return;
        }


        meetingChart =
            new Chart(
                canvas.getContext('2d'),
                {

                    type:
                        'bar',

                    data: {

                        labels:
                            labels,

                        datasets: [

                            {

                                label:
                                    'Jumlah Pertemuan',

                                data:
                                    values,

                                borderWidth:
                                    0,

                                borderRadius:
                                    8

                            }

                        ]

                    },


                    options: {

                        responsive:
                            true,

                        maintainAspectRatio:
                            false,


                        plugins: {

                            legend: {

                                display:
                                    false

                            }

                        },


                        scales: {

                            x: {

                                grid: {

                                    display:
                                        false

                                },

                                ticks: {

                                    autoSkip:
                                        false

                                }

                            },


                            y: {

                                beginAtZero:
                                    true,

                                ticks: {

                                    precision:
                                        0

                                }

                            }

                        }

                    }

                }
            );

    }


    /* =========================================================
       FILTER MEMBER
    ========================================================= */

    $(document)
        .off(
            'change.kpiMember',
            '#memberKpiMode, #memberKpiExtracurricular, #memberKpiClass'
        )
        .on(
            'change.kpiMember',
            '#memberKpiMode, #memberKpiExtracurricular, #memberKpiClass',
            function () {

                loadMemberKpi();

            }
        );


    /* =========================================================
       MODE MEMBER
    ========================================================= */

    $('#memberKpiMode')
        .off('change.kpiMode')
        .on(
            'change.kpiMode',
            function () {

                const mode =
                    this.value;


                if (
                    mode ===
                    'extracurricular'
                ) {

                    $('#memberKpiExtracurricularWrapper')
                        .removeClass(
                            'opacity-50'
                        );

                } else {

                    $('#memberKpiExtracurricularWrapper')
                        .addClass(
                            'opacity-50'
                        );

                }


                loadMemberKpi();

            }
        );


    /* =========================================================
       ESCAPE HTML
    ========================================================= */

    function escapeHtml(text) {

        const div =
            document.createElement(
                'div'
            );


        div.textContent =
            text ?? '';


        return div.innerHTML;

    }


    /* =========================================================
       GLOBAL DEBUG
    ========================================================= */

    window.ExtracurricularKPI = {

        memberUrl:
            MEMBER_KPI_URL,

        meetingUrl:
            MEETING_KPI_URL,

        reloadMember:
            loadMemberKpi,

        reloadMeeting:
            loadMeetingKpi

    };


})();

</script>

<script>

const UPDATE_EXTRACURRICULAR_URL =
    "{{ route(
        'lms.student-vice-principal.extracurricular-management.update-detail',
        [
            'role' => Auth::user()->role,
            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id,
            'extracurricularId' => '__ID__'
        ]
    ) }}";


// =========================================================
// MENU TITIK 3
// =========================================================

function toggleExtracurricularMenu(id)
{
    const currentMenu =
        document.getElementById('extracurricular-menu-' + id);

    if (!currentMenu) {
        return;
    }

    document
        .querySelectorAll('[id^="extracurricular-menu-"]')
        .forEach(menu => {

            if (menu !== currentMenu) {
                menu.classList.add('hidden');
            }

        });

    currentMenu.classList.toggle('hidden');
}


// =========================================================
// CLICK DI LUAR MENU
// =========================================================

document.addEventListener('click', function(event) {

    if (
        !event.target.closest('[id^="extracurricular-menu-"]') &&
        !event.target.closest('button[onclick^="toggleExtracurricularMenu"]')
    ) {

        document
            .querySelectorAll('[id^="extracurricular-menu-"]')
            .forEach(menu => {
                menu.classList.add('hidden');
            });

    }

});


// =========================================================
// OPEN MODAL EDIT
// =========================================================

function openEditExtracurricular(data)
{
    console.log('EDIT EKSKUL:', data);

    // Tutup menu
    document
        .querySelectorAll('[id^="extracurricular-menu-"]')
        .forEach(menu => {
            menu.classList.add('hidden');
        });

    document.getElementById('edit_extracurricular_id').value =
        data.id ?? '';

    document.getElementById('edit_extracurricular_name').value =
        data.name ?? '';

    document.getElementById('edit_extracurricular_description').value =
        data.description ?? '';

    document.getElementById('edit_extracurricular_type').value =
        data.type ?? 'pilihan';

    document.getElementById('edit_extracurricular_coach').value =
        data.coach ?? '';

    const modal =
        document.getElementById('modal-edit-extracurricular');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}


// =========================================================
// CLOSE MODAL
// =========================================================

function closeEditExtracurricular()
{
    const modal =
        document.getElementById('modal-edit-extracurricular');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}


// =========================================================
// SUBMIT EDIT
// =========================================================

document
    .getElementById('form-edit-extracurricular')
    .addEventListener('submit', function(e) {

        e.preventDefault();

        const id =
            document.getElementById('edit_extracurricular_id').value;

        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'ID ekstrakurikuler tidak ditemukan.'
            });

            return;
        }

        const url =
            UPDATE_EXTRACURRICULAR_URL.replace('__ID__', id);

        const button =
            document.getElementById('btn-save-edit-extracurricular');

        button.disabled = true;

        button.innerHTML = `
            <span class="loading loading-spinner loading-sm"></span>
            Menyimpan...
        `;


        $.ajax({

            url: url,

            method: 'PUT',

            data: {

                _token: '{{ csrf_token() }}',

                name:
                    document.getElementById(
                        'edit_extracurricular_name'
                    ).value,

                description:
                    document.getElementById(
                        'edit_extracurricular_description'
                    ).value,

                type:
                    document.getElementById(
                        'edit_extracurricular_type'
                    ).value,

                coach:
                    document.getElementById(
                        'edit_extracurricular_coach'
                    ).value

            },

            success: function(response) {

                console.log(
                    'UPDATE EKSKUL SUCCESS:',
                    response
                );

                closeEditExtracurricular();

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message ?? 'Data berhasil diperbarui.',
                    timer: 1600,
                    showConfirmButton: false
                });

                // Reload data supaya nama,
                // tipe dan pembina langsung berubah.
                setTimeout(function() {
                    window.location.reload();
                }, 1600);

            },

            error: function(xhr) {

                console.error(
                    'UPDATE EKSKUL ERROR:',
                    xhr.status,
                    xhr.responseText
                );

                let message =
                    'Gagal memperbarui ekstrakurikuler.';

                if (xhr.responseJSON?.message) {
                    message =
                        xhr.responseJSON.message;
                }

                if (xhr.responseJSON?.errors) {

                    message = Object
                        .values(xhr.responseJSON.errors)
                        .flat()
                        .join('<br>');

                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: message
                });

            },

            complete: function() {

                button.disabled = false;

                button.innerHTML = `
                    <i class="fa-solid fa-save mr-2"></i>
                    Simpan Perubahan
                `;

            }

        });

    });


// =========================================================
// ESC UNTUK TUTUP MODAL
// =========================================================

document.addEventListener('keydown', function(e) {

    if (e.key === 'Escape') {
        closeEditExtracurricular();
    }

});

</script>
@endif
