@include('components/sidebar-beranda', ['headerSideNav' => 'Refleksi'])

@if (Auth::user()->role === 'Yayasan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="p-6 md:p-8">
            <main id="container" data-role="{{ $role }}" data-foundation-id="{{ $foundationId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-face-smile-beam absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>

                        <i class="fa-solid fa-graduation-cap absolute -bottom-10 -left-6 text-[90px] lg:text-35 text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg shrink-0">
                                        <i class="fa-solid fa-face-smile-beam text-white text-xl lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white leading-tight">
                                            Monitoring Refleksi Siswa
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-3xl text-sm sm:text-base text-white/80 leading-relaxed">
                                    Monitoring kondisi emosional dan refleksi siswa dari seluruh sekolah yang berada di bawah naungan yayasan.
                                </p>
                            </div>
                            
                            <!-- FILTER TAHUN REFLEKSI -->
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
                                                Tahun
                                            </label>

                                            <!-- Loading -->
                                            <div id="filter-loading" class="mt-1 flex items-center gap-2">
                                                <div class="h-4 w-24 rounded bg-white/20 animate-pulse"></div>

                                                <div class="h-3 w-3 rounded-full bg-white/20 animate-pulse"></div>
                                            </div>

                                            <!-- Select -->
                                            <div id="filter-content" class="relative mt-0.5 hidden">
                                                <select id="chartYear" class="w-full appearance-none bg-transparent border-0 p-0 
                                                    pr-6 text-sm font-semibold text-white cursor-pointer focus:outline-none focus:ring-0">
                                                </select>

                                                <i class="fa-solid fa-chevron-down absolute right-1 top-1/2 -translate-y-1/2 text-white/70 text-[10px] pointer-events-none"></i>
                                            </div>
                                            
                                            <!-- Empty Message -->
                                            <div id="filter-empty" class="hidden mt-1">
                                                <div class="flex items-center gap-2 text-xs text-white/60">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span>Tidak ada data tahunan.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI SUMMARY -->
                <section class="mb-6">

                    <!-- Skeleton Loading -->
                    <div id="kpi-loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                        <!-- Total Reflection -->
                        <div class="rounded-2xl border border-cyan-100 bg-cyan-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-32 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-20 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Dominant Emotion -->
                        <div class="rounded-2xl border border-green-100 bg-green-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-28 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-24 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Positive Condition -->
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-32 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-20 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Completion Percentage -->
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-32 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-20 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Content -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                            <!-- Total Reflection -->
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800  ">
                                                Total Refleksi
                                            </p>

                                            <h2 id="total-reflection" class="mt-1 text-sm font-bold text-slate-800">
                                                0
                                            </h2>

                                            <p class="mt-1 text-xs text-slate-500">
                                                Pertanyaan refleksi
                                            </p>
                                        </div>

                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10">
                                            <i class="fa-solid fa-comments text-xl text-cyan-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dominant Emotion -->
                            <div id="dominant-emotion-card" class="rounded-2xl border border-gray-300 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800  ">
                                                Emosi Dominan
                                            </p>

                                            <h2 id="dominant-emotion" class="mt-1 text-sm font-bold text-slate-800">
                                                -
                                            </h2>

                                            <p id="dominant-percentage" class="mt-1 text-xs">
                                                0%
                                            </p>
                                        </div>

                                        <div id="dominant-emotion-icon-wrapper" class="flex h-12 w-12 items-center justify-center rounded-xl">
                                            <i id="dominant-emotion-icon" class="fa-solid fa-face-meh text-xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Positive Condition -->
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800  ">
                                                Kondisi Positif
                                            </p>

                                            <h2 id="positive-condition" class="mt-1 text-sm font-bold text-slate-800">
                                                0%
                                            </h2>

                                            <p id="positive-category" class="mt-1 text-xs text-emerald-600">
                                                -
                                            </p>
                                        </div>

                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                                            <i class="fa-solid fa-heart text-xl text-emerald-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Completion Percentage -->
                            <div class="rounded-2xl border border-blue-100 bg-blue-50 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">
                                                Pengisian Refleksi
                                            </p>

                                            <h2 id="completion-percentage" class="mt-1 text-sm font-bold text-slate-800">
                                                0%
                                            </h2>

                                            <p class="mt-1 text-xs text-blue-600">
                                                Persentase refleksi yang telah diisi
                                            </p>
                                        </div>

                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                                            <i class="fa-solid fa-user-check text-xl text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- EMOTION OVERVIEW -->
                <section class="mb-6">

                    <!-- Skeleton Loading -->
                    <div id="emotion-overview-loading" class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                        <!-- Emotion Chart Skeleton -->
                        <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="p-6 border-b border-slate-100">
                                <div class="flex items-center gap-3">

                                    <div class="h-11 w-11 rounded-xl bg-slate-200 animate-pulse"></div>

                                    <div class="space-y-2">
                                        <div class="h-4 w-48 rounded bg-slate-200 animate-pulse"></div>
                                        <div class="h-3 w-72 rounded bg-slate-200 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">

                                    <!-- Circle Chart Skeleton -->
                                    <div class="flex justify-center">
                                        <div class="relative h-64 w-64">
                                            <div class="h-full w-full rounded-full bg-slate-200 animate-pulse"></div>
                                            <div class="absolute inset-12 rounded-full bg-white"></div>
                                        </div>
                                    </div>

                                    <!-- Emotion List Skeleton -->
                                    <div class="space-y-4">
                                        @for($i = 0; $i < 5; $i++)
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-slate-200 animate-pulse"></div>

                                                <div class="flex-1 space-y-2">
                                                    <div class="flex justify-between">
                                                        <div class="h-3 w-24 rounded bg-slate-200 animate-pulse"></div>
                                                        <div class="h-3 w-12 rounded bg-slate-200 animate-pulse"></div>
                                                    </div>

                                                    <div class="h-2 w-full rounded-full bg-slate-200 animate-pulse"></div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Insight Skeleton -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="p-6 border-b border-gray-300">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 rounded-xl bg-slate-200 animate-pulse"></div>

                                    <div class="space-y-2">
                                        <div class="h-4 w-40 rounded bg-slate-200 animate-pulse"></div>
                                        <div class="h-3 w-56 rounded bg-slate-200 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 space-y-4">
                                @for($i = 0; $i < 2; $i++)
                                    <div class="rounded-xl border border-slate-100 p-4 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg bg-slate-200 animate-pulse"></div>
                                            <div class="h-3 w-32 rounded bg-slate-200 animate-pulse"></div>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="h-3 w-full rounded bg-slate-200 animate-pulse"></div>
                                            <div class="h-3 w-4/5 rounded bg-slate-200 animate-pulse"></div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div id="emotion-overview-content" class="hidden">

                        <div class="grid grid-cols-1 xl:grid-cols-3 items-start gap-6">

                            <!-- EMOTION CHART -->
                            <div class="xl:col-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                                <!-- Header -->
                                <div class="border-b border-slate-100 p-6">
                                    <div class="flex items-center gap-3">

                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50">
                                            <i class="fa-solid fa-chart-pie text-cyan-600"></i>
                                        </div>

                                        <div>
                                            <h2 class="text-lg font-semibold text-slate-800">
                                                Distribusi Emosi Siswa
                                            </h2>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Gambaran kondisi emosional siswa dari seluruh sekolah.
                                            </p>
                                        </div>

                                    </div>
                                </div>

                                <!-- CONTENT -->
                                <div id="emotion-chart-content" class="p-6">
                                    <div class="grid grid-cols-1 items-center gap-6 lg:grid-cols-2">
                                        <div class="flex min-h-64 w-full items-center justify-center">
                                            <div class="w-full max-w-xs">
                                                <canvas id="emotion-chart"></canvas>
                                            </div>
                                        </div>

                                        <!-- Emotion List -->
                                        <div class="min-w-0">
                                            <div id="emotion-list" class="max-h-86 space-y-3 overflow-y-auto pr-1">
                                                <!-- Ajax Render -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- EMPTY STATE -->
                                <div id="emotion-chart-empty" class="hidden p-5 sm:p-6">
                                    <div class="flex min-h-64 w-full flex-col items-center justify-center rounded-2xl border border-cyan-100 bg-linear-to-br 
                                        from-cyan-50 via-slate-50 to-white px-6 py-10 text-center">

                                        <!-- Icon -->
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-cyan-100 bg-white shadow-sm">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50">
                                                <i class="fa-solid fa-chart-pie text-lg text-cyan-500"></i>
                                            </div>
                                        </div>

                                        <!-- Title -->
                                        <h3 class="mt-4 text-base font-semibold text-slate-700">
                                            Belum Ada Data Emosi
                                        </h3>

                                        <!-- Description -->
                                        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                                            Belum tersedia data refleksi siswa untuk menampilkan
                                            distribusi emosi pada tahun ajaran yang dipilih.
                                        </p>

                                        <!-- Info -->
                                        <div class="mt-4 inline-flex items-center gap-2 rounded-xl border border-cyan-100 bg-white px-4 py-2.5 text-xs text-slate-500 
                                            shadow-sm">

                                            <i class="fa-solid fa-circle-info shrink-0 text-cyan-500"></i>

                                            <span>
                                                Data akan muncul setelah siswa mengisi refleksi.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- INSIGHT -->
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-100 p-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                            <i class="fa-solid fa-lightbulb text-emerald-600"></i>
                                        </div>

                                        <div>
                                            <h2 class="text-lg font-semibold text-slate-800">
                                                Insight Refleksi
                                            </h2>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Ringkasan kondisi siswa.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Insight Empty -->
                                <div
                                    id="insight-empty"
                                    class="hidden p-5 sm:p-6"
                                >

                                    <div class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-emerald-100 bg-linear-to-br 
                                        from-emerald-50 via-slate-50 to-white px-6 py-10 text-center">

                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-100 bg-white shadow-sm">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50">
                                                <i class="fa-solid fa-lightbulb text-lg text-emerald-500"></i>
                                            </div>
                                        </div>

                                        <h3 class="mt-4 text-base font-semibold text-slate-700">
                                            Belum Ada Insight
                                        </h3>

                                        <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">
                                            Belum tersedia data refleksi pada tahun
                                            ajaran yang dipilih untuk menghasilkan insight.
                                        </p>

                                        <div class="mt-4 inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-white px-4 py-2.5 text-xs text-slate-500 
                                            shadow-sm">

                                            <i class="fa-solid fa-circle-info shrink-0 text-emerald-500"></i>

                                            <span>
                                                Insight akan muncul setelah data refleksi tersedia.
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Insight List -->
                                <div id="insight-list" class="hidden space-y-4 p-6">
                                    <!-- Ajax Render -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Filtering -->
                <section class="mb-6">
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- Left -->
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                                    <i class="fa-solid fa-filter text-blue-600 text-lg"></i>
                                </div>

                                <div>

                                    <h2 class="text-lg font-bold text-slate-800">
                                        Filter Visualisasi Refleksi
                                    </h2>

                                    <p class="text-sm text-slate-500">
                                        Periode yang dipilih akan diterapkan pada seluruh grafik di bawah.
                                    </p>
                                </div>
                            </div>

                            <!-- Right -->
                            <div class="flex flex-wrap items-center gap-3">

                                <!-- Periode -->
                                <select id="chartPeriod" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold cursor-pointer outline-none
                                    disabled:cursor-default disabled:opacity-50 disabled:bg-slate-50" disabled>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly" selected>Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>

                                <!-- Bulan -->
                                <select id="chartMonth" class="hidden h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold cursor-pointer outline-none
                                    disabled:cursor-default disabled:opacity-50 disabled:bg-slate-50" disabled>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- REFLECTION TREND -->
                <section class="mb-6">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                        <!-- CHART SECTION -->
                        <div class="bg-white rounded-4xl md:rounded-[2.5rem] p-5 md:p-8 shadow-sm border border-gray-300">

                            <!-- Header -->
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 mb-6">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50">
                                        <i class="fa-solid fa-chart-line text-cyan-600"></i>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-800">
                                            Tren Jumlah Refleksi
                                        </h2>

                                        <p class="text-sm text-slate-500">
                                            Perkembangan jumlah refleksi siswa.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Wrapper -->
                            <div class="relative h-90 sm:h-195 lg:h-120">

                                <!-- LOADING -->
                                <div id="reflection-chart-loading"
                                    class="absolute inset-0 flex h-full w-full flex-col items-center justify-center rounded-3xl
                                    border border-dashed border-slate-200 bg-slate-50">

                                    <div class="relative">
                                        <div class="h-16 w-16 rounded-full border-4 border-slate-200"></div>
                                        <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-[#2563EB]"></div>
                                    </div>

                                    <h4 class="mt-6 text-center text-lg font-black text-slate-800">
                                        Memuat Tren Refleksi
                                    </h4>

                                    <p class="mt-2 max-w-md px-6 text-center text-sm leading-relaxed text-slate-500">
                                        Menyiapkan data jumlah refleksi siswa sesuai periode yang dipilih.
                                    </p>

                                    <div class="mt-5 flex items-center gap-2 text-center text-xs font-semibold text-slate-400">
                                        <i class="fas fa-chart-line"></i>
                                        Memuat data...
                                    </div>

                                </div>

                                <!-- EMPTY -->
                                <div id="empty-message-reflection-chart" class="absolute inset-0 hidden">

                                    <div class="flex h-full w-full flex-col items-center justify-center rounded-3xl
                                        border border-dashed border-slate-200 bg-slate-50 px-8 text-center">

                                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-3xl text-[#2563EB]">
                                            <i class="fas fa-chart-line"></i>
                                        </div>

                                        <h4 class="mt-6 text-xl font-black text-slate-800">
                                            Belum Ada Data Refleksi
                                        </h4>

                                        <p class="mt-3 max-w-lg text-sm leading-relaxed text-slate-500">
                                            Belum ada jawaban refleksi pada periode yang dipilih.
                                            Pilih periode lain atau tunggu hingga siswa mengisi refleksi.
                                        </p>

                                        <div class="mt-5 flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold text-[#2563EB]">
                                            <i class="fas fa-info-circle"></i>
                                            Tidak ada data untuk ditampilkan
                                        </div>

                                    </div>
                                </div>

                                <!-- CHART -->
                                <div id="reflection-chart-content" class="absolute inset-0 hidden">
                                    <canvas id="studentReflectionChart"
                                        class="h-full w-full">
                                    </canvas>
                                </div>
                            </div>
                        </div>

                        <!-- EMOTION TREND -->
                        <div class="bg-white rounded-4xl md:rounded-[2.5rem] p-5 md:p-8 shadow-sm border border-gray-300">

                            <!-- Header -->
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 mb-6">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50">
                                        <i class="fa-solid fa-heart-pulse text-violet-600"></i>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-800">
                                            Tren Kondisi Emosional
                                        </h2>

                                        <p class="text-sm text-slate-500">
                                            Perubahan distribusi emosi siswa berdasarkan hasil refleksi pada setiap periode.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Wrapper -->
                            <div class="relative h-90 sm:h-195 lg:h-120">

                                <!-- LOADING -->
                                <div id="emotion-trend-loading" class="absolute inset-0 flex h-full w-full flex-col items-center justify-center rounded-3xl 
                                    border border-dashed border-slate-200 bg-slate-50">

                                    <div class="relative">
                                        <div class="h-16 w-16 rounded-full border-4 border-slate-200"></div>
                                        <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-violet-600"></div>
                                    </div>

                                    <h4 class="mt-6 text-center text-lg font-black text-slate-800">
                                        Memuat Tren Emosi
                                    </h4>

                                    <p class="mt-2 max-w-md px-6 text-center text-sm leading-relaxed text-slate-500">
                                        Menyiapkan visualisasi perubahan kondisi emosional siswa berdasarkan periode yang dipilih.
                                    </p>

                                    <div class="mt-5 flex items-center gap-2 text-center text-xs font-semibold text-slate-400">
                                        <i class="fa-solid fa-heart-pulse"></i>
                                        Memuat data...
                                    </div>
                                </div>

                                <!-- EMPTY -->
                                <div id="empty-message-emotion-trend" class="absolute inset-0 hidden">
                                    <div class="flex h-full w-full flex-col items-center justify-center rounded-3xl border border-dashed border-slate-200 
                                        bg-slate-50 px-8 text-center">

                                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-violet-100 text-3xl text-violet-600">
                                            <i class="fa-solid fa-heart-pulse"></i>
                                        </div>

                                        <h4 class="mt-6 text-xl font-black text-slate-800">
                                            Belum Ada Data Emosi
                                        </h4>

                                        <p class="mt-3 max-w-lg text-sm leading-relaxed text-slate-500">
                                            Belum terdapat data kondisi emosional siswa pada periode yang dipilih.
                                            Grafik akan ditampilkan setelah tersedia hasil refleksi siswa.
                                        </p>

                                        <div class="mt-5 flex items-center gap-2 rounded-full bg-violet-50 px-4 py-2 text-xs font-semibold text-violet-600">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Menunggu data refleksi
                                        </div>
                                    </div>
                                </div>

                                <!-- CHART -->
                                <div id="emotion-trend-content" class="absolute inset-0 hidden">
                                    <canvas id="emotionTrendChart" class="h-full w-full"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SCHOOL REFLECTION COMPARISON -->
                <section class="mb-6">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <!-- Header -->
                        <div class="border-b border-slate-100 p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
                                        <i class="fa-solid fa-school text-xl text-primary"></i>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-800">
                                            Monitoring Refleksi Sekolah
                                        </h2>

                                        <p class="text-sm text-slate-500">
                                            Ringkasan kondisi refleksi seluruh sekolah dalam yayasan.
                                        </p>
                                    </div>
                                </div>

                                <div class="w-full md:w-72">
                                    <label class="mb-2 block text-sm font-medium text-slate-600">
                                        Tahun Ajaran
                                    </label>

                                    <!-- Loading -->
                                    <div id="academic-year-loading" class="hidden">
                                        <div class="skeleton h-12 w-full rounded-xl"></div>
                                    </div>

                                    <!-- Select -->
                                    <div id="filter-tahun-ajaran-wrapper" class="hidden">
                                        <select
                                            id="filter-tahun-ajaran"
                                            class="select select-bordered w-full rounded-xl bg-white font-medium text-slate-700 cursor-pointer">
                                            <!-- Ajax -->
                                        </select>
                                    </div>

                                    <!-- Empty -->
                                    <div id="filter-tahun-ajaran-empty" class="hidden">
                                        <div class="flex h-12 items-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4">
                                            <i class="fa-solid fa-calendar-xmark text-slate-400"></i>

                                            <span class="text-sm text-slate-500">
                                                Tahun ajaran tidak tersedia
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 p-6">

                            <!-- TABLE SUMMARY -->
                            <div class="xl:col-span-2">

                                <div class="mb-4">

                                    <h3 class="font-semibold text-slate-800">
                                        Ringkasan Sekolah
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Menampilkan jumlah refleksi, tingkat partisipasi, dan persentase emosi positif setiap sekolah.
                                    </p>

                                </div>

                                <!-- TABLE LOADING -->
                                <div id="table-loading" class="overflow-x-auto">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="border border-gray-300 px-2 py-3 text-xs font-semibold text-center">
                                                    No
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-left w-[45%]">
                                                    Sekolah
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                                    Total Refleksi
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                                    Pengisian Refleksi
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                                    Positif
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @for ($i = 0; $i < 4; $i++)
                                                <tr>
                                                    <td class="border border-gray-300 px-3 py-4 text-center">
                                                        <div class="skeleton mx-auto h-5 w-6"></div>
                                                    </td>

                                                    <td class="border border-gray-300 px-4 py-4">
                                                        <div class="skeleton h-3 w-32"></div>
                                                    </td>

                                                    <td class="border border-gray-300 px-4 py-4 text-center">
                                                        <div class="skeleton mx-auto h-5 w-14"></div>
                                                    </td>

                                                    <td class="border border-gray-300 px-4 py-4 text-center">
                                                        <div class="skeleton mx-auto h-5 w-16"></div>
                                                    </td>

                                                    <td class="border border-gray-300 px-4 py-4 text-center">
                                                        <div class="skeleton mx-auto h-5 w-16"></div>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>

                                <!-- TABLE CONTENT -->
                                <div id="table-content" class="overflow-x-auto hidden">
                                    <table id="table-school-reflection-summary" class="min-w-full text-sm border-collapse">
                                        <thead class="bg-gray-50 shadow-inner">
                                            <tr>
                                                <th class="border border-gray-300 px-2 py-3 text-xs font-semibold text-center">
                                                    No
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-left w-[45%]">
                                                    Sekolah
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                                    Total Refleksi
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                                    Pengisian Refleksi
                                                </th>

                                                <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                                    Positif
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody id="tbody-school-reflection-summary">
                                            <!-- Ajax -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Empty Message -->
                                <div id="empty-message-school-reflection-summary"
                                    class="hidden rounded-2xl border-2 border-dashed border-gray-300 bg-base-100 py-20">

                                    <div class="mx-auto flex w-full max-w-md flex-col items-center text-center">

                                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                                            <i class="fa-solid fa-face-smile-beam text-3xl text-primary"></i>
                                        </div>

                                        <h3 class="mt-6 text-xl font-semibold">
                                            Belum Ada Data Refleksi
                                        </h3>

                                        <p class="mt-2 text-sm leading-6 text-base-content/60">
                                            Belum ada data refleksi sekolah yang dapat ditampilkan
                                            untuk tahun ajaran yang dipilih.
                                        </p>

                                    </div>
                                </div>

                                <div class="pagination-container-school-reflection-summary mt-5"></div>
                            </div>

                            <!-- SCHOOL ATTENTION -->
                            <div>
                                <div class="mb-4">
                                    <h3 class="font-semibold text-slate-800">
                                        Perlu Perhatian
                                    </h3>

                                    <p id="school-attention-total" class="mt-1 text-sm text-slate-500">
                                        Memuat data sekolah...
                                    </p>
                                </div>

                                <!-- Loading -->
                                <div id="school-attention-loading" class="space-y-4">
                                    @for ($i = 0; $i < 2; $i++)
                                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="skeleton h-4 w-40"></div>
                                                    <div class="mt-2 skeleton h-3 w-24"></div>
                                                </div>

                                                <div class="skeleton h-6 w-16 rounded-full"></div>
                                            </div>

                                            <div class="mt-4">
                                                <div class="skeleton h-2 w-full rounded-full"></div>
                                            </div>

                                            <div class="mt-4 flex justify-between">
                                                <div class="skeleton h-3 w-20"></div>
                                                <div class="skeleton h-3 w-20"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                <!-- Content -->
                                <div id="school-attention-content" class="hidden">
                                    <div id="school-attention-list" class="space-y-4 max-h-105 overflow-y-auto pr-2">
                                        <!-- Ajax -->
                                    </div>
                                </div>

                                <!-- Empty -->
                                <div id="school-attention-empty" class="hidden">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-25 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
                                            <i class="fa-solid fa-circle-check text-xl text-emerald-600"></i>
                                        </div>

                                        <h3 class="mt-4 text-base font-semibold text-slate-700">
                                            Tidak Ada Sekolah yang Memerlukan Perhatian
                                        </h3>

                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            Tidak ada sekolah yang memerlukan perhatian pada tahun ajaran ini.
                                        </p>
                                    </div>
                                </div>
                            </div>
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

<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/load-kpi.js') }}"></script> <!--- student reflection kpi ---->
<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/load-emotion-overview.js') }}"></script> <!--- student reflection emotion overview ---->
<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/load-reflection-trend.js') }}"></script> <!--- student reflection trend ---->
<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/load-emotion-trend.js') }}"></script> <!--- student emotion trend ---->
<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/filter.js') }}"></script> <!--- load all filters ---->
<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/paginate-school-reflection-summary.js') }}"></script> <!--- paginate school reflection summary ---->
<script src="{{ asset('assets/js/features/lms/foundation/student-reflection/paginate-school-reflection-attention.js') }}"></script> <!--- paginate school reflection attention ---->