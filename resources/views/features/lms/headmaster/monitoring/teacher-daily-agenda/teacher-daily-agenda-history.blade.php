@include('components/sidebar-beranda', [
    'headerSideNav' => 'Riwayat',
    'linkBackButton' => route('lms.headmaster.teacherDailyAgernda.monitoring', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Kepala Sekolah')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5 space-y-8">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-create-feedback"></div>

            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="space-y-6">

                <!-- HERO -->
                <section class="overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] shadow-xl">
                    <div class="flex min-h-67.5 flex-col justify-between p-8 lg:p-10">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">

                            <!-- LEFT -->
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium text-white">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Riwayat Agenda
                                </span>

                                <h1 class="mt-4 text-2xl font-bold text-white">
                                    Riwayat Agenda Harian Guru
                                </h1>

                                <p class="mt-4 max-w-3xl text-sm leading-7 text-blue-100">
                                    Telusuri seluruh histori agenda pembelajaran guru,
                                    status pengisian agenda harian, serta aktivitas mengajar
                                    berdasarkan data absensi mata pelajaran.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div
                                class="inline-flex items-center gap-3 rounded-xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">

                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                                    <i class="fa-solid fa-calendar-days text-blue-200"></i>
                                </div>

                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-blue-200">
                                        Riwayat
                                    </p>

                                    <p class="text-sm font-semibold text-white">
                                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- TAB -->
                        <div class="mt-8">
                            <div
                                class="inline-flex w-full flex-col gap-2 rounded-2xl border border-white/10 bg-white/10 p-2 backdrop-blur
                                    sm:w-auto sm:flex-row sm:gap-0 sm:p-1">

                                <a href="{{ route('lms.headmaster.teacherDailyAgernda.monitoring', [
                                    'role'=> $role,
                                    'schoolName'=> $schoolName,
                                    'schoolId'=> $schoolId
                                ]) }}"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-medium text-white transition hover:bg-white/10
                                        sm:w-auto sm:justify-start">
                                    <i class="fa-solid fa-chart-column"></i>
                                    Dashboard Monitoring
                                </a>

                                <a href=""
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow
                                        sm:w-auto sm:justify-start">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Riwayat Agenda Guru
                                </a>

                            </div>
                        </div>
                    </div>
                </section>

                <!-- SUMMARY -->
                <section class="relative">

                    {{-- Skeleton --}}
                    <div id="teacher-agenda-summary-skeleton"
                        class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                        @for ($i = 0; $i < 4; $i++)
                            <div class="animate-pulse rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="space-y-3">
                                        <div class="h-4 w-24 rounded bg-slate-200"></div>
                                        <div class="h-8 w-16 rounded bg-slate-300"></div>
                                    </div>

                                    <div class="h-14 w-14 rounded-2xl bg-slate-200"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <!-- Content -->
                    <div id="teacher-agenda-summary" class="hidden">
                        <div id="teacher-agenda-summary-content" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    
                            <!-- Total Agenda -->
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Total Agenda (Pertemuan)
                                        </p>
    
                                        <h3 id="summary-total-agenda" class="mt-2 text-3xl font-bold text-slate-800">
                                            0
                                        </h3>
                                    </div>
    
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100">
                                        <i class="fa-solid fa-book-open text-2xl text-blue-600"></i>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Sudah Mengisi -->
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    
                                <div class="flex items-center justify-between">
    
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Sudah Mengisi
                                        </p>
    
                                        <h3 id="summary-filled" class="mt-2 text-3xl font-bold text-green-600">
                                            0
                                        </h3>
                                    </div>
    
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-green-100">
                                        <i class="fa-solid fa-circle-check text-2xl text-green-600"></i>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Belum Mengisi -->
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    
                                <div class="flex items-center justify-between">
    
                                    <div>
    
                                        <p class="text-sm font-medium text-slate-500">
                                            Belum Mengisi
                                        </p>
    
                                        <h3 id="summary-unfilled" class="mt-2 text-3xl font-bold text-red-600">
                                            0
                                        </h3>
    
                                    </div>
    
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100">
    
                                        <i class="fa-solid fa-triangle-exclamation text-2xl text-red-600"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- Persentase Agenda Guru -->
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    
                                <div class="flex items-center justify-between">
    
                                    <div>
    
                                        <p class="text-sm font-medium text-slate-500">
                                            Persentase Agenda Guru
                                        </p>
    
                                        <h3 id="summary-compliance" class="mt-2 text-3xl font-bold text-slate-800">
                                            0%
                                        </h3>
    
                                    </div>
    
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100">
    
                                        <i class="fa-solid fa-chart-pie text-2xl text-amber-600"></i>
    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FILTER -->
                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">

                    <!-- HEADER -->
                    <div class="border-b border-slate-200 px-6 py-5">

                        <div class="flex items-center justify-between">

                            <div>
                                <h3 class="text-lg font-semibold text-slate-800">
                                    Riwayat Agenda Guru
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Menampilkan histori agenda harian guru berdasarkan jadwal mengajar,
                                    absensi mata pelajaran, dan agenda yang telah diisi.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FILTER -->
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        
                        
                        

                            <!-- Tanggal -->
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    Tanggal Pertemuan
                                </label>

                                <div class="relative">

                                    <input id="search_date" type="text" value="" placeholder="Pilih Tanggal" readonly 
                                        class="h-12 w-full rounded-xl border border-slate-300 bg-white pl-4 pr-12 text-sm text-slate-700 transition-all 
                                        duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer">

                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                        <i class="fa-regular fa-calendar text-slate-400"></i>
                                    </div>

                                </div>

                            </div>

                            <!-- Guru -->
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    Guru Mata Pelajaran
                                </label>

                                <div class="relative">

                                    <select id="search_teacher" class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 
                                        text-sm text-slate-700 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none 
                                        cursor-pointer">

                                        <option value="">
                                            Semua Guru
                                        </option>

                                    </select>

                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                        <i class="fa-solid fa-user-group text-slate-400"></i>
                                    </div>

                                </div>

                            </div>

                            <!-- Status -->
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    Status Agenda
                                </label>

                                <div class="relative">

                                    <select
                                        id="search_status"
                                        class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 text-sm text-slate-700 
                                            transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer">

                                        <option value="">
                                            Semua Status
                                        </option>

                                        <option value="submitted">
                                            Sudah Mengisi
                                        </option>

                                        <option value="pending">
                                            Belum Mengisi
                                        </option>

                                    </select>

                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                        <i class="fa-solid fa-circle-check text-slate-400"></i>
                                    </div>

                                </div>
                            </div>

                            <!-- Status Feedback -->
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    Status Feedback
                                </label>

                                <div class="relative">

                                    <select
                                        id="search_feedback"
                                        class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 text-sm text-slate-700
                                            transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer">

                                        <option value="">
                                            Semua Feedback
                                        </option>

                                        <option value="given">
                                            Sudah Diberikan
                                        </option>

                                        <option value="pending">
                                            Belum Diberikan
                                        </option>

                                    </select>

                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex w-12 items-center justify-center border-l border-slate-200">
                                        <i class="fa-solid fa-comments text-slate-400"></i>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- TABLE SKELETON -->
                    <div class="overflow-x-auto">

                        <!-- Skeleton -->
                        <div id="teacher-daily-agenda-history-skeleton" class="hidden overflow-x-auto">
                            <table class="min-w-362.5 w-full table-auto border-separate border-spacing-0">
                                <tbody>
                                    @for ($i = 0; $i < 7; $i++)
                                        <tr class="animate-pulse border-b border-slate-100">

                                            <!-- Tanggal -->
                                            <td class="px-5 py-4">
                                                <div class="h-4 w-24 rounded bg-slate-200"></div>
                                            </td>

                                            <!-- Guru -->
                                            <td class="px-5 py-4">
                                                <div class="h-4 w-36 rounded bg-slate-200"></div>
                                            </td>

                                            <!-- Mapel -->
                                            <td class="px-5 py-4">
                                                <div class="h-4 w-28 rounded bg-slate-200"></div>
                                            </td>

                                            <!-- Kelas -->
                                            <td class="px-5 py-4">
                                                <div class="space-y-2">
                                                    <div class="h-4 w-24 rounded bg-slate-200"></div>
                                                    <div class="h-3 w-16 rounded bg-slate-100"></div>
                                                </div>
                                            </td>

                                            <!-- Jam -->
                                            <td class="px-5 py-4 text-center">
                                                <div class="mx-auto h-4 w-20 rounded bg-slate-200"></div>
                                            </td>

                                            <!-- Absensi -->
                                            <td class="px-5 py-4 text-center">
                                                <div class="mx-auto h-7 w-24 rounded-full bg-slate-200"></div>
                                            </td>

                                            <!-- Agenda -->
                                            <td class="px-5 py-4 text-center">
                                                <div class="mx-auto h-7 w-28 rounded-full bg-slate-200"></div>
                                            </td>

                                            <!-- Uraian KBM -->
                                            <td class="px-5 py-4">
                                                <div class="space-y-2">
                                                    <div class="h-3 w-full rounded bg-slate-200"></div>
                                                    <div class="h-3 w-5/6 rounded bg-slate-100"></div>
                                                </div>
                                            </td>

                                            <!-- Feedback -->
                                            <td class="px-5 py-4 text-center">
                                                <div class="mx-auto h-7 w-24 rounded-full bg-slate-200"></div>
                                            </td>

                                            <!-- Aksi -->
                                            <td class="px-5 py-4 text-center">
                                                <div class="mx-auto h-10 w-24 rounded-xl bg-slate-200"></div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- CONTENT -->
                        <table id="table-teacher-daily-agenda-history" class="hidden min-w-362.5 w-full table-auto border-separate border-spacing-0">
                            <thead class="thead-table-teacher-daily-agenda-history hidden bg-slate-50 border-b border-slate-200">

                                <tr class="text-sm font-semibold text-slate-600">
                                    <th class="w-56 px-5 py-4 text-left">Tanggal</th>
                                    <th class="w-48 px-5 py-4 text-left">Guru</th>
                                    <th class="w-44 px-5 py-4 text-left">Mapel</th>
                                    <th class="w-40 px-5 py-4 text-left">Kelas</th>
                                    <th class="w-36 px-5 py-4 text-center">Jam</th>
                                    <th class="w-44 px-5 py-4 text-center">Absensi Siswa</th>
                                    <th class="w-44 px-5 py-4 text-center">Agenda</th>
                                    <th class="w-44 px-5 py-4 text-center">Uraian KBM</th>
                                    <th class="w-44 px-5 py-4 text-center">Feedback</th>
                                    <th class="w-28 px-5 py-4 text-center">Aksi</th>
                                </tr>

                            </thead>

                            <tbody id="tbody-teacher-daily-agenda-history">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div class="pagination-teacher-daily-agenda-history flex justify-center border-t border-slate-200 py-5"></div>

                    <!-- EMPTY -->
                    <div
                        id="empty-message-teacher-daily-agenda-history"
                        class="hidden h-96 rounded-b-3xl bg-slate-50">

                        <div
                            class="flex h-full flex-col items-center justify-center px-8">

                            <div
                                class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-blue-100">

                                <i
                                    class="fas fa-clipboard-list text-3xl text-blue-500"></i>

                            </div>

                            <h4
                                class="text-xl font-bold text-slate-700">

                                Belum Ada Riwayat Agenda Guru

                            </h4>

                            <p
                                class="mt-3 max-w-lg text-center text-sm leading-relaxed text-slate-500">

                                Belum ditemukan riwayat agenda mengajar berdasarkan filter yang dipilih.
                                Riwayat akan muncul setelah guru memiliki jadwal mengajar dan pertemuan
                                pembelajaran telah dibuat.

                            </p>
                        </div>
                    </div>
                </section>

                <!-- MODAL DETAIL -->
                <dialog id="my_modal_1" class="modal p-4 sm:p-6 lg:p-8">

                    <div class="modal-box max-w-5xl w-full max-h-[90vh] flex flex-col overflow-hidden rounded-3xl p-0">

                        <!-- Header -->
                        <div class="shrink-0 border-b border-slate-200 bg-white px-8 py-6">

                            <div class="flex items-start justify-between">

                                <div>

                                    <h3 class="text-xl font-bold text-slate-800">
                                        Detail Agenda Harian Guru
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Tinjau kegiatan pembelajaran yang telah dilakukan guru kemudian
                                        berikan masukan sebagai bahan evaluasi.
                                    </p>

                                </div>

                                <form method="dialog">
                                    <button class="btn btn-circle btn-ghost">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </form>

                            </div>

                        </div>

                        <!-- Scroll Area -->
                        <div class="flex-1 overflow-y-auto p-8">

                            <div class="space-y-8">

                                <!-- Informasi -->
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Guru
                                        </p>

                                        <p id="detail_teacher_name"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Mata Pelajaran
                                        </p>

                                        <p id="detail_subject"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Kelas
                                        </p>

                                        <p id="detail_class"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Tanggal
                                        </p>

                                        <p id="detail_date"
                                            class="mt-2 font-semibold text-slate-700">
                                            -
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200"></div>

                                <!-- KBM -->
                                <div>
                                    <div class="mb-4 flex items-center gap-3">

                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100">
                                            <i class="fa-solid fa-book-open text-blue-600"></i>
                                        </div>

                                        <div>
                                            <h4 class="font-semibold text-slate-800">
                                                Uraian Kegiatan Belajar Mengajar
                                            </h4>

                                            <p class="text-sm text-slate-500">
                                                Aktivitas pembelajaran yang diinput oleh guru.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <div id="detail_learning_activity" class="prose prose-sm max-w-none whitespace-pre-line text-slate-700 leading-7"></div>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200"></div>

                                <!-- Feedback -->
                                <form id="create-feedback-form">

                                    <input type="hidden" id="detail_teacher_agenda_id" name="teacher_daily_agenda_id">

                                    <div>
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100">
                                                <i class="fa-solid fa-comments text-amber-600"></i>
                                            </div>

                                            <div>
                                                <h4 class="font-semibold text-slate-800">
                                                    Feedback
                                                    <sup class="text-red-500">*</sup>
                                                </h4>

                                                <p class="text-sm text-slate-500">
                                                    Berikan apresiasi, saran maupun evaluasi terhadap kegiatan pembelajaran guru.
                                                </p>
                                            </div>
                                        </div>

                                        <textarea id="feedback" name="feedback" rows="6" class="textarea textarea-bordered w-full rounded-2xl 
                                            border-gray-300 resize-none min-h-40 max-h-80 outline-none"
                                            placeholder="Tuliskan feedback di sini..."></textarea>

                                        <span id="error-feedback" class="mt-2 block text-xs font-semibold text-red-500"></span>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-8 py-5">

                            <div class="flex items-center justify-end gap-3">

                                <form method="dialog">
                                    <button class="btn btn-ghost rounded-xl">
                                        Tutup
                                    </button>
                                </form>

                                <button type="button" id="btn-submit-feedback" class="btn rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    Simpan Feedback
                                </button>
                            </div>
                        </div>
                    </div>
                </dialog>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/headmaster/monitoring/teacher-daily-agenda/history/teacher-daily-agenda-list-history.js') }}"></script> <!--- teacher daily agenda list history ---->
<script src="{{ asset('assets/js/features/lms/headmaster/monitoring/teacher-daily-agenda/history/teacher-daily-agenda-kpi-history.js') }}"></script> <!--- teacher daily agenda kpi history ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->