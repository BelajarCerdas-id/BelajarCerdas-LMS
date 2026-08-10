@include('components/sidebar-beranda', ['headerSideNav' => 'Kinerja Guru'])

@if (Auth::user()->role === 'Yayasan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="p-6 md:p-8">
            <main id="container" data-role="{{ $role }}" data-foundation-id="{{ $foundationId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-chart-column absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>
                        <i class="fa-solid fa-chalkboard-user absolute -bottom-10 -left-6 text-[90px] lg:text-[140px] text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10">

                            <!-- HEADER CONTENT -->
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">

                                <!-- LEFT -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 lg:gap-4">

                                        <!-- Icon -->
                                        <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 
                                            flex items-center justify-center shadow-lg shrink-0">
                                            <i class="fa-solid fa-chart-column text-white text-xl lg:text-3xl"></i>
                                        </div>

                                        <!-- Title -->
                                        <div class="inline-block">
                                            <h1 class="text-xl font-bold text-white leading-tight">
                                                Monitoring Kinerja Guru
                                            </h1>

                                            <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                        </div>
                                    </div>

                                    <p class="mt-5 max-w-3xl text-sm sm:text-base text-white/80 leading-relaxed">
                                        Memantau aktivitas pembelajaran guru melalui jumlah
                                        assessmen dan materi yang dibuat serta dipublikasikan
                                        kepada siswa di seluruh sekolah dalam naungan yayasan.
                                    </p>
                                </div>

                                <!-- FILTER TAHUN AJARAN -->
                                <div class="w-full lg:w-64">
                                    <div class="rounded-2xl border border-white/15 bg-white/10 backdrop-blur-sm p-3">
                                        <div class="flex items-center gap-3">

                                            <!-- Icon -->
                                            <div class="w-10 h-10 shrink-0 rounded-xl bg-white/15 border border-white/10 flex items-center justify-center">
                                                <i class="fa-solid fa-calendar-days text-white text-sm"></i>
                                            </div>

                                            <!-- Label + Select -->
                                            <div class="min-w-0 flex-1">

                                                <label class="block text-[11px] font-medium text-white/60 uppercase tracking-wide">
                                                    Tahun Ajaran
                                                </label>

                                                <!-- Loading -->
                                                <div id="filter-loading" class="mt-1 flex items-center gap-2">
                                                    <div class="h-4 w-24 rounded bg-white/20 animate-pulse"></div>

                                                    <div class="h-3 w-3 rounded-full bg-white/20 animate-pulse"></div>
                                                </div>

                                                <!-- Select -->
                                                <div id="filter-content" class="relative mt-0.5 hidden">
                                                    <select id="filter-tahun-ajaran" name="filter_tahun_ajaran" class="w-full appearance-none bg-transparent border-0 p-0 
                                                        pr-6 text-sm font-semibold text-white cursor-pointer focus:outline-none focus:ring-0">
                                                    </select>

                                                    <i class="fa-solid fa-chevron-down absolute right-1 top-1/2 -translate-y-1/2 text-white/70 text-[10px] pointer-events-none"></i>
                                                </div>

                                                <!-- Empty Message -->
                                                <div id="filter-empty" class="hidden mt-1">
                                                    <div class="flex items-center gap-2 text-xs text-white/60">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                        <span>Tidak ada data tahun ajaran.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SUMMARY -->
                <section class="mb-6">

                    <!-- KPI LOADING -->
                    <div id="kpi-loading">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                            <!-- SKELETON ASSESSMENT -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">

                                        <!-- title -->
                                        <div class="h-4 w-36 rounded bg-slate-200 animate-pulse"></div>

                                        <!-- percentage -->
                                        <div class="mt-3 h-9 w-20 rounded-lg bg-slate-200 animate-pulse"></div>

                                        <!-- count -->
                                        <div class="mt-2 h-3 w-24 rounded bg-slate-100 animate-pulse"></div>

                                        <!-- description -->
                                        <div class="mt-3 h-3 w-44 rounded bg-slate-100 animate-pulse"></div>
                                    </div>

                                    <!-- icon -->
                                    <div class="h-11 w-11 shrink-0 rounded-xl bg-slate-200 animate-pulse"></div>
                                </div>

                                <!-- progress -->
                                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full w-1/3 rounded-full bg-slate-200 animate-pulse"></div>
                                </div>
                            </div>

                            <!-- SKELETON MATERI -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">

                                        <div class="h-4 w-32 rounded bg-slate-200 animate-pulse"></div>
                                        <div class="mt-3 h-9 w-20 rounded-lg bg-slate-200 animate-pulse"></div>
                                        <div class="mt-2 h-3 w-24 rounded bg-slate-100 animate-pulse"></div>
                                        <div class="mt-3 h-3 w-48 rounded bg-slate-100 animate-pulse"></div>
                                    </div>
                                    <div class="h-11 w-11 shrink-0 rounded-xl bg-slate-200 animate-pulse"></div>
                                </div>

                                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full w-1/3 rounded-full bg-slate-200 animate-pulse"></div>
                                </div>
                            </div>


                            <!-- SKELETON RATA-RATA -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">

                                        <div class="h-4 w-40 rounded bg-slate-200 animate-pulse"></div>
                                        <div class="mt-3 h-9 w-20 rounded-lg bg-slate-200 animate-pulse"></div>
                                        <div class="mt-2 h-3 w-24 rounded bg-slate-100 animate-pulse"></div>
                                        <div class="mt-3 h-3 w-52 rounded bg-slate-100 animate-pulse"></div>
                                    </div>
                                    <div class="h-11 w-11 shrink-0 rounded-xl bg-slate-200 animate-pulse"></div>
                                </div>

                                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full w-1/3 rounded-full bg-slate-200 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI CONTENT -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                            <!-- ASSESSMENT -->
                            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="absolute -right-5 -top-5 h-20 w-20 rounded-full bg-emerald-50"></div>
                                <div class="relative flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Assessmen Terpublikasi
                                        </p>

                                        <div class="mt-2 flex items-baseline gap-2">
                                            <p id="assessment-published-percentage" class="text-3xl font-bold tracking-tight text-slate-800">
                                                0%
                                            </p>

                                            <span id="assessment-published-count" class="text-xs font-medium text-emerald-600">
                                                0 / 0
                                            </span>
                                        </div>
                                        <p class="mt-2 text-xs text-slate-500">
                                            Assessmen yang sudah terkirim.
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                        <i class="fa-solid fa-file-circle-check text-lg"></i>
                                    </div>
                                </div>

                                <div class="relative mt-4">
                                    <div class="h-1.5 overflow-hidden rounded-full bg-emerald-100">
                                        <div id="assessment-published-progress" class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: 0%">
                                            <!-- show data in ajax -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MATERI -->
                            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">

                                <div class="absolute -right-5 -top-5 h-20 w-20 rounded-full bg-violet-50"></div>

                                <div class="relative flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Materi Terpublikasi
                                        </p>

                                        <div class="mt-2 flex items-baseline gap-2">
                                            <p id="content-published-percentage" class="text-3xl font-bold tracking-tight text-slate-800">
                                                0%
                                            </p>

                                            <span id="content-published-count" class="text-xs font-medium text-violet-600">
                                                0 / 0
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs text-slate-500">
                                            Materi yang sudah tersedia untuk siswa.
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                        <i class="fa-solid fa-book-open text-lg"></i>
                                    </div>
                                </div>

                                <div class="relative mt-4">
                                    <div class="h-1.5 overflow-hidden rounded-full bg-violet-100">
                                        <div id="content-active-progress" class="h-full rounded-full bg-violet-500 transition-all duration-500" style="width: 0%">
                                            <!-- show data in ajax -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RATA-RATA -->
                            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">

                                <div class="absolute -right-5 -top-5 h-20 w-20 rounded-full bg-orange-50"></div>

                                <div class="relative flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">
                                            Rata-rata Publikasi
                                        </p>

                                        <div class="mt-2 flex items-baseline gap-2">
                                            <p id="average-published-percentage" class="text-3xl font-bold tracking-tight text-slate-800">
                                                0%
                                            </p>

                                            <span class="text-xs font-medium text-orange-600">
                                                Assessmen + Materi
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs text-slate-500">
                                            Rata-rata konten yang sudah tersedia untuk siswa.
                                        </p>
                                    </div>

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                                        <i class="fa-solid fa-chart-column text-lg"></i>
                                    </div>
                                </div>

                                <div class="relative mt-4">
                                    <div class="h-1.5 overflow-hidden rounded-full bg-orange-100">
                                        <div id="average-published-progress" class="h-full rounded-full bg-orange-500 transition-all duration-500" style="width: 0%">
                                            <!-- show data in ajax -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SCHOOL PERFORMANCE TABLE -->
                <section class="mb-6 rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm lg:p-6">

                    <!-- Section Header -->
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">
                                Kinerja Guru Sekolah
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Ringkasan aktivitas assessmen dan materi dari setiap sekolah.
                            </p>
                        </div>
                    </div>

                    <!-- TABLE CONTENT -->
                    <div id="table-content" class="overflow-x-auto hidden">

                        <table id="table-school-teacher-performance" class="min-w-full text-sm border-collapse">

                            <!-- SKELETON TABLE HEADER -->
                            <thead id="thead-school-teacher-performance-skeleton" class="hidden">
                                <tr class="bg-slate-50">
                                    <th rowspan="2" class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-6 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th rowspan="2" class="border border-slate-200 px-4 py-3 text-left">
                                        <div class="h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th colspan="2" class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th colspan="2" class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-16 animate-pulse rounded bg-slate-200"></div>
                                    </th>
                                </tr>

                                <tr class="bg-slate-50">

                                    <!-- Assessment -->
                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-14 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-16 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <!-- Materi -->
                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-12 animate-pulse rounded bg-slate-200"></div>
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center">
                                        <div class="mx-auto h-3 w-16 animate-pulse rounded bg-slate-200"></div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- THEAD TABLE CONTENT -->
                            <thead id="thead-school-teacher-performance" class="hidden">
                                <tr class="bg-slate-50">
                                    <th rowspan="2" class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        No
                                    </th>

                                    <th rowspan="2" class="border border-slate-200 px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600">
                                        Sekolah
                                    </th>

                                    <th colspan="2" class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        Assessmen
                                    </th>

                                    <th colspan="2" class="border border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-600">
                                        Materi
                                    </th>
                                </tr>

                                <tr class="bg-slate-50">
                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-semibold text-slate-500">
                                        Asesmen
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-semibold text-slate-500">
                                        Persentase
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-semibold text-slate-500">
                                        Materi
                                    </th>

                                    <th class="border border-slate-200 px-4 py-3 text-center text-xs font-semibold text-slate-500">
                                        Persentase
                                    </th>
                                </tr>
                            </thead>

                            <!-- SKELETON TABLE BODY -->
                            <tbody id="tbody-school-teacher-performance-skeleton" class="hidden">

                                @for ($i = 0; $i < 6; $i++)
                                    <tr class="animate-pulse text-xs">

                                        <!-- No -->
                                        <td class="border border-slate-200 px-3 py-4">
                                            <div class="mx-auto h-4 w-5 rounded bg-slate-200"></div>
                                        </td>

                                        <!-- Sekolah -->
                                        <td class="border border-slate-200 px-4 py-4">
                                            <div class="flex items-center gap-3">

                                                <div class="h-10 w-10 shrink-0 rounded-xl bg-slate-200"></div>

                                                <div class="min-w-0 flex-1 space-y-2">
                                                    <div class="h-4 w-40 rounded bg-slate-200"></div>
                                                    <div class="h-3 w-24 rounded bg-slate-200"></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Assessment -->
                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-4 w-16 rounded bg-slate-200"></div>
                                        </td>

                                        <!-- Assessment Progress -->
                                        <td class="border border-slate-200 px-4 py-4">
                                            <div class="min-w-32.5">
                                                <div class="mb-2 flex items-center justify-end">
                                                    <div class="h-3 w-10 rounded bg-slate-200"></div>
                                                </div>

                                                <div class="h-2 w-full rounded-full bg-slate-200"></div>
                                            </div>
                                        </td>

                                        <!-- Materi -->
                                        <td class="border border-slate-200 px-4 py-4 text-center">
                                            <div class="mx-auto h-4 w-16 rounded bg-slate-200"></div>
                                        </td>

                                        <!-- Materi Progress -->
                                        <td class="border border-slate-200 px-4 py-4">
                                            <div class="min-w-32.5">
                                                <div class="mb-2 flex items-center justify-end">
                                                    <div class="h-3 w-10 rounded bg-slate-200"></div>
                                                </div>

                                                <div class="h-2 w-full rounded-full bg-slate-200"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>

                            <!-- TBODY TABLE CONTENT -->
                            <tbody id="tbody-school-teacher-performance">
                                <!-- Ajax -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty Message -->
                    <div id="empty-message-school-teacher-performance" class="hidden rounded-2xl border-2 border-dashed border-gray-300 bg-base-100 py-20">
                        <div class="mx-auto flex w-full max-w-md flex-col items-center text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                                <i class="fa-solid fa-chart-column text-3xl text-primary"></i>
                            </div>

                            <h3 class="mt-6 text-xl font-semibold">
                                Belum Ada Data Kinerja Guru Sekolah
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-base-content/60">
                                Belum ada data assessmen atau materi pembelajaran yang tercatat
                                pada tahun ajaran yang dipilih.
                            </p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container-school-teacher-performance mt-5"></div>
                </section>

                <!-- CHART -->
                <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm lg:p-6">

                    <!-- Section Header -->
                    <div class="mb-6">

                        <h2 class="text-base font-bold text-slate-800 sm:text-lg">
                            Publikasi Pembelajaran per Sekolah
                        </h2>

                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            Persentase assessmen dan materi yang telah dipublikasikan
                            dibandingkan dengan jumlah yang dibuat.
                        </p>

                    </div>

                    <!-- Chart -->
                    <div class="relative h-125 w-full sm:h-140">

                        <!-- Canvas -->
                        <canvas id="school-teacher-performance-chart"></canvas>
                        
                        <!-- Loading -->
                        <div id="school-teacher-performance-chart-loading"
                            class="absolute inset-0 flex flex-col items-center justify-center bg-base-100">

                            <div
                                class="h-16 w-16 animate-spin rounded-full border-[5px] border-slate-200 border-t-primary sm:h-20 sm:w-20 sm:border-[6px]">
                            </div>

                            <p class="mt-5 text-base font-semibold text-slate-700 sm:text-lg">
                                Memuat data publikasi pembelajaran...
                            </p>

                            <p class="mt-2 max-w-md px-4 text-center text-sm leading-6 text-slate-400 sm:text-base">
                                Data assessmen dan materi dari setiap sekolah sedang diproses.
                            </p>
                        </div>

                        <!-- Empty -->
                        <div
                            id="empty-message-school-teacher-performance-chart"
                            class="absolute inset-0 hidden flex-col items-center justify-center bg-base-100">

                            <!-- Icon -->
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 sm:h-24 sm:w-24">
                                <i class="fa-solid fa-chart-column text-3xl text-slate-400 sm:text-4xl"></i>
                            </div>

                            <!-- Empty Title -->
                            <p
                                class="mt-5 text-base font-semibold text-slate-700 sm:text-lg">
                                Belum ada data publikasi pembelajaran
                            </p>

                            <!-- Empty Description -->
                            <p class="mt-2 max-w-md px-4 text-center text-sm leading-6 text-slate-400 sm:text-base">
                                Belum ada assessmen atau materi yang tercatat pada tahun ajaran ini.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/foundation/school-teacher-performance/filter.js') }}"></script> <!--- filter ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-teacher-performance/load-kpi.js') }}"></script> <!--- teacher performance kpi ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-teacher-performance/paginate-school-teacher-performance.js') }}"></script> <!--- paginate school teacher performance ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-teacher-performance/load-school-teacher-performance-chart.js') }}"></script> <!--- load school teacher performance chart ---->