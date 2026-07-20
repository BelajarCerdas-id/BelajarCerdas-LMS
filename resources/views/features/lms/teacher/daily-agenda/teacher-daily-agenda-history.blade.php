@include('components/sidebar-beranda', ['headerSideNav' => 'Riwayat']);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="space-y-6">

                <!-- HERO -->
                <section class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-8 py-8 text-white shadow-xl">

                    <!-- Background Decoration -->
                    <div class="absolute -top-16 -right-16 h-52 w-52 rounded-full bg-white/10"></div>

                    <div class="absolute -bottom-20 left-24 h-36 w-36 rounded-full bg-white/5"></div>

                    <div class="relative z-10">
                        <div class="flex flex-col gap-8 xl:flex-row xl:items-center xl:justify-between">

                            <!-- Left -->
                            <div class="max-w-3xl">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Riwayat Agenda Guru
                                </span>

                                <h1 class="mt-5 text-3xl font-bold leading-tight">
                                    Riwayat Agenda Pembelajaran
                                </h1>

                                <p class="mt-3 max-w-2xl text-sm leading-7 text-blue-100">
                                    Seluruh agenda pembelajaran yang telah Anda isi akan tersimpan sebagai
                                    dokumentasi kegiatan mengajar. Anda juga dapat melihat status review
                                    serta feedback dari kepala sekolah apabila tersedia.
                                </p>
                            </div>

                            <!-- Right -->
                            <div class="hidden xl:flex">
                                <div class="flex h-36 w-36 items-center justify-center rounded-full border border-white/10 bg-white/10 backdrop-blur">
                                    <i class="fa-solid fa-book-bookmark text-6xl text-white/80"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="mt-8">

                            <div
                                class="inline-flex w-full flex-col gap-2 rounded-2xl border border-white/10 bg-white/10 p-2 backdrop-blur
                                sm:w-auto sm:flex-row sm:gap-0 sm:p-1">

                                <a href="{{ route('lms.teacherDailyAgenda.view', [
                                    'role' => $role, 
                                    'schoolName' => $schoolName,
                                    'schoolId' => $schoolId 
                                ]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-medium text-white transition 
                                    hover:bg-white/10 sm:w-auto">
                                    
                                    <i class="fa-solid fa-chart-column"></i>
                                    Dashboard Agenda
                                </a>

                                <a href="" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 
                                    text-sm font-semibold text-blue-700 shadow sm:w-auto">

                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Riwayat Agenda
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SUMMARY -->
                <section class="relative">

                    <!-- Skeleton -->
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
                        
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

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

                            <!-- Status -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    Status Agenda
                                </label>

                                <div class="relative">

                                    <select id="search_status" class="h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-12 
                                        text-sm text-slate-700 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none cursor-pointer">

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
                    <div id="empty-message-teacher-daily-agenda-history" class="hidden h-96 rounded-b-3xl bg-slate-50">

                        <div class="flex h-full flex-col items-center justify-center px-8">
                            <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-blue-100">
                                <i class="fas fa-clipboard-list text-3xl text-blue-500"></i>
                            </div>

                            <h4 class="text-xl font-bold text-slate-700">
                                Belum Ada Riwayat Agenda Guru
                            </h4>

                            <p class="mt-3 max-w-lg text-center text-sm leading-relaxed text-slate-500">
                                Belum ditemukan riwayat agenda mengajar berdasarkan filter yang dipilih.
                                Riwayat akan muncul setelah kamu memiliki jadwal mengajar dan pertemuan
                                pembelajaran telah dibuat.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- MODAL DETAIL -->
                <dialog id="my_modal_1" class="modal p-4 sm:p-6 lg:p-8">
                    <div class="modal-box max-w-5xl w-full max-h-[90vh] overflow-hidden rounded-3xl p-0">

                        <!-- Header -->
                        <div class="border-b border-slate-200 bg-white px-8 py-6">
                            <div class="flex items-start justify-between gap-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-slate-800">
                                        Detail Agenda Pembelajaran
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Lihat kembali agenda pembelajaran yang telah Anda isi beserta hasil
                                        review dan masukan dari kepala sekolah apabila tersedia.
                                    </p>
                                </div>

                                <form method="dialog">
                                    <button class="btn btn-circle btn-ghost">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Status -->
                            <div class="mt-6 flex flex-wrap gap-3">

                                <span id="detail_status_badge" class="inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-2 text-sm font-medium text-green-700">
                                    <i class="fa-solid fa-circle-check"></i>
                                    -
                                </span>
                            </div>
                        </div>

                        <!-- BODY -->
                        <div class="overflow-y-auto p-8 pb-20 space-y-8 max-h-[calc(90vh-180px)]">

                            <!-- Informasi -->
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Guru
                                    </p>

                                    <p id="detail_teacher_name" class="mt-3 font-semibold text-slate-700">
                                        -
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Mata Pelajaran
                                    </p>

                                    <p id="detail_subject" class="mt-3 font-semibold text-slate-700">
                                        -
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Kelas
                                    </p>

                                    <p id="detail_class" class="mt-3 font-semibold text-slate-700">
                                        -
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Tanggal
                                    </p>

                                    <p id="detail_date" class="mt-3 font-semibold text-slate-700">
                                        -
                                    </p>
                                </div>
                            </div>

                            <!-- KBM -->
                            <section>
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                                        <i class="fa-solid fa-book-open text-blue-600"></i>
                                    </div>

                                    <div>

                                        <h4 class="font-semibold text-slate-800">
                                            Uraian Kegiatan Pembelajaran
                                        </h4>

                                        <p class="text-sm text-slate-500">
                                            Dokumentasi kegiatan pembelajaran yang telah Anda isi.
                                        </p>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                                    <div id="detail_learning_activity" class="whitespace-pre-line leading-8 text-slate-700">
                                        -
                                    </div>
                                </div>
                            </section>

                            <!-- Feedback -->
                            <section>
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                                        <i class="fa-solid fa-comments text-amber-600"></i>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-slate-800">
                                            Review Kepala Sekolah
                                        </h4>

                                        <p class="text-sm text-slate-500">
                                            Review akan ditampilkan setelah kepala sekolah melakukan peninjauan terhadap agenda pembelajaran.
                                        </p>
                                    </div>
                                </div>

                                <div id="detail_feedback_card" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p id="detail_feedback" class="leading-7 text-slate-700 whitespace-pre-line">
                                        -
                                    </p>
                                </div>
                            </section>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-slate-200 bg-slate-50 px-8 py-5">
                            <div class="flex justify-end">
                                <form method="dialog">
                                    <button class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-medium 
                                        text-slate-700 transition hover:bg-slate-100 cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i>
                                        Tutup
                                    </button>
                                </form>
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

<script src="{{ asset('assets/js/features/lms/teacher/daily-agenda/teacher-daily-agenda-list-history.js') }}"></script> <!--- paginate daily agenda history ---->
<script src="{{ asset('assets/js/features/lms/teacher/daily-agenda/teacher-daily-agenda-kpi-history.js') }}"></script> <!--- paginate daily agenda kpi history ---->