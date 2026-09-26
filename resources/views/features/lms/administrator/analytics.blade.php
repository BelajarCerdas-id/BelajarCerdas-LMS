@include('components/sidebar-beranda', ['headerSideNav' => 'Site Analytics'])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out z-20 pb-20">
        <div class="mt-4 sm:mt-6 mx-4 sm:mx-8 space-y-6">

            <!-- HERO HEADER -->
            <section>
                <div class="bg-[linear-gradient(135deg,#0071BC_0%,#004f84_60%,#002b49_100%)] rounded-3xl p-6 sm:p-8 text-white shadow-xl overflow-hidden relative border border-white/10">
                    <div class="absolute right-0 top-0 opacity-10 pointer-events-none select-none">
                        <i class="fa-solid fa-chart-line text-[260px] translate-x-12 -translate-y-8"></i>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                Platform Analytics Dashboard
                            </h1>
                            <p class="mt-2 text-sky-100/90 text-sm sm:text-base max-w-2xl leading-relaxed">
                                Pantau siapa saja yang sedang menggunakan platform, sekolah asal, peran pengguna (<span class="font-medium text-white">Siswa, Guru, Kepsek, Orang Tua</span>), serta modul dan sub-modul yang aktif diakses secara real-time.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <button id="btn-refresh-analytics"
                                class="cursor-pointer inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/25 active:scale-95 text-white font-medium text-sm transition-all shadow-md backdrop-blur-md border border-white/20">
                                <i class="fa-solid fa-arrows-rotate text-sm" id="icon-refresh"></i>
                                <span>Segarkan Data</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- GLOBAL FILTERS BAR -->
            <section class="bg-white rounded-3xl p-4 sm:p-5 shadow-sm border border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <!-- Periode Toggle Buttons -->
                        <div class="flex items-center bg-slate-100 p-1 rounded-2xl border border-slate-200/80">
                            <button type="button" data-period="today"
                                class="period-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer bg-white text-[#0071BC] shadow-sm">
                                Hari Ini
                            </button>
                            <button type="button" data-period="7days"
                                class="period-btn px-4 py-2 text-xs sm:text-sm font-medium rounded-xl transition-all cursor-pointer text-slate-600 hover:text-slate-900">
                                7 Hari
                            </button>
                            <button type="button" data-period="30days"
                                class="period-btn px-4 py-2 text-xs sm:text-sm font-medium rounded-xl transition-all cursor-pointer text-slate-600 hover:text-slate-900">
                                30 Hari
                            </button>
                        </div>

                        <!-- Filter Sekolah -->
                        <div class="min-w-[180px] flex-1 sm:flex-initial">
                            <select id="filter-school"
                                class="w-full h-11 px-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition cursor-pointer">
                                <option value="all">Semua Sekolah Mitra</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">
                                        {{ $school->nama_sekolah }} ({{ $school->npsn }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Role -->
                        <div class="min-w-[150px] flex-1 sm:flex-initial">
                            <select id="filter-role"
                                class="w-full h-11 px-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition cursor-pointer">
                                <option value="all">Semua Role</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Modul -->
                        <div class="min-w-[160px] flex-1 sm:flex-initial">
                            <select id="filter-module"
                                class="w-full h-11 px-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition cursor-pointer">
                                <option value="all">Semua Modul LMS</option>
                                @foreach ($modules as $mod)
                                    <option value="{{ $mod }}">{{ $mod }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Reset Filter -->
                    <div class="flex items-center gap-2 self-end lg:self-center">
                        <button id="btn-reset-filters"
                            class="cursor-pointer text-xs sm:text-sm text-slate-500 hover:text-[#0071BC] font-medium px-3 py-2 rounded-xl hover:bg-slate-100 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-filter-circle-xmark"></i>
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- KPI STATS CARDS -->
            <section>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                    <!-- Card 1: Live Online Users -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 mb-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    <span>Online Sekarang</span>
                                </span>
                                <h2 id="kpi-online-users" class="text-3xl font-extrabold text-slate-900 mt-1">
                                    <span class="inline-block w-16 h-8 bg-slate-200 rounded animate-pulse"></span>
                                </h2>
                                <p class="text-xs text-slate-500 mt-2">
                                    Pengguna aktif 15 menit terakhir
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
                    </div>

                    <!-- Card 2: Activities Today -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Aktivitas Modul Hari Ini
                                </p>
                                <h2 id="kpi-activities-today" class="text-3xl font-extrabold text-slate-900 mt-1">
                                    <span class="inline-block w-20 h-8 bg-slate-200 rounded animate-pulse"></span>
                                </h2>
                                <p class="text-xs text-slate-500 mt-2 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-trend-up text-blue-600"></i>
                                    <span>Total akses modul & halaman</span>
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-blue-100 text-[#0071BC] flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#0071BC] to-sky-400"></div>
                    </div>

                    <!-- Card 3: Unique Active Users Today -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Pengguna Aktif Hari Ini
                                </p>
                                <h2 id="kpi-active-users-today" class="text-3xl font-extrabold text-slate-900 mt-1">
                                    <span class="inline-block w-20 h-8 bg-slate-200 rounded animate-pulse"></span>
                                </h2>
                                <p class="text-xs text-slate-500 mt-2">
                                    Dari total <span id="kpi-total-registered" class="font-bold text-slate-700">-</span> akun terdaftar
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    </div>

                    <!-- Card 4: Top Module & Active Schools -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm relative overflow-hidden group hover:border-amber-300 hover:shadow-md transition-all duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Modul Paling Sering Dibuka
                                </p>
                                <h2 id="kpi-top-module" class="text-2xl font-bold text-slate-900 mt-1 truncate max-w-[200px]" title="Modul Populer">
                                    <span class="inline-block w-24 h-8 bg-slate-200 rounded animate-pulse"></span>
                                </h2>
                                <p class="text-xs text-slate-500 mt-2 flex items-center gap-1.5">
                                    <i class="fa-solid fa-school text-amber-600"></i>
                                    <span><strong id="kpi-active-schools">-</strong> sekolah aktif hari ini</span>
                                </p>
                            </div>
                            <div class="w-13 h-13 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-fire"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                    </div>

                </div>
            </section>

            <!-- CHARTS SECTION 1: TIMELINE TREND & ROLE COMPOSITION -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Tren Aktivitas Timeline -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-chart-area text-[#0071BC]"></i>
                                <span>Tren Penggunaan Modul</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Volume interaksi pengguna terhadap fitur dan materi platform
                            </p>
                        </div>
                        <span id="trend-period-label" class="text-xs font-semibold text-slate-500 px-3 py-1 bg-slate-100 rounded-xl border border-slate-200">
                            Hari Ini
                        </span>
                    </div>

                    <div class="h-72 sm:h-80 w-full relative mt-4">
                        <div id="chart-trend-loader" class="absolute inset-0 flex items-center justify-center bg-white/70 z-10">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-[#0071BC]"></i>
                        </div>
                        <canvas id="canvas-activity-trend"></canvas>
                    </div>
                </div>

                <!-- Komposisi Role Pengguna -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-pie-chart text-purple-600"></i>
                            <span>Distribusi Role Pengguna</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Proporsi aktivitas berdasarkan peran akun
                        </p>
                    </div>

                    <div class="h-64 sm:h-72 w-full relative mt-4 flex items-center justify-center">
                        <div id="chart-roles-loader" class="absolute inset-0 flex items-center justify-center bg-white/70 z-10">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-purple-600"></i>
                        </div>
                        <canvas id="canvas-role-breakdown"></canvas>
                    </div>

                    <div id="roles-legend-container" class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-2 text-xs">
                        <!-- Dynamic Role Badges will appear here -->
                    </div>
                </div>

            </section>

            <!-- CHARTS SECTION 2: MODUL & SUB-MODUL BREAKDOWN -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Popularitas Modul -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-cubes text-emerald-600"></i>
                                <span>Peringkat Penggunaan Modul</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Modul yang paling banyak diakses oleh seluruh pengguna
                            </p>
                        </div>
                    </div>

                    <div class="h-72 w-full relative mt-4">
                        <div id="chart-modules-loader" class="absolute inset-0 flex items-center justify-center bg-white/70 z-10">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-600"></i>
                        </div>
                        <canvas id="canvas-module-usage"></canvas>
                    </div>
                </div>

                <!-- Sub-Modul Populer -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-blue-600"></i>
                                <span>Eksplorasi Sub-Modul Terpopuler</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5" id="submodule-parent-desc">
                                Rincian sub-modul pada modul teratas
                            </p>
                        </div>
                    </div>

                    <div class="h-72 w-full relative mt-4">
                        <div id="chart-submodules-loader" class="absolute inset-0 flex items-center justify-center bg-white/70 z-10">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600"></i>
                        </div>
                        <canvas id="canvas-submodule-usage"></canvas>
                    </div>
                </div>

            </section>

            <!-- SCHOOL LEADERBOARD & LIVE USERS FEED -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Leaderboard Sekolah Aktif -->
                <div class="lg:col-span-1 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-ranking-star text-amber-500"></i>
                                    <span>Sekolah Paling Aktif</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Peringkat aktivitas sekolah mitra
                                </p>
                            </div>
                        </div>

                        <div id="school-leaderboard-list" class="mt-4 space-y-3 max-h-[380px] overflow-y-auto pr-1">
                            <!-- Populated by JavaScript -->
                            <div class="text-center py-8 text-slate-400 text-sm">
                                <i class="fa-solid fa-spinner fa-spin text-xl text-[#0071BC] mb-2"></i>
                                <p>Memuat data sekolah...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengguna Sedang Online (Live Presensi Platform) -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                                    <span>Pengguna Sedang Menggunakan Platform</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Aktivitas terkini (15 menit terakhir) beserta modul yang sedang dibuka
                                </p>
                            </div>
                        </div>

                        <div id="live-online-users-list" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[380px] overflow-y-auto pr-1">
                            <!-- Populated by JavaScript -->
                            <div class="col-span-full text-center py-8 text-slate-400 text-sm">
                                <i class="fa-solid fa-spinner fa-spin text-xl text-[#0071BC] mb-2"></i>
                                <p>Memuat pengguna online...</p>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            <!-- DETAILED AUDIT LOG TABLE -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                            <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                            <span>Catatan Lengkap Riwayat Akses Modul</span>
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Daftar riil setiap pengguna yang mengakses modul dan sub-modul di platform Belajar Cerdas
                        </p>
                    </div>

                    <!-- Search Box -->
                    <div class="flex items-center gap-3">
                        <div class="relative w-full sm:w-72">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                            <input type="text" id="table-search" placeholder="Cari nama, email, modul..."
                                class="w-full h-11 pl-11 pr-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto mt-4 rounded-2xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Pengguna</th>
                                <th class="py-3.5 px-4">Role</th>
                                <th class="py-3.5 px-4">Sekolah Mitra</th>
                                <th class="py-3.5 px-4">Modul & Sub-Modul</th>
                                <th class="py-3.5 px-4">Aksi / URL</th>
                                <th class="py-3.5 px-4">Waktu Akses</th>
                            </tr>
                        </thead>
                        <tbody id="table-activity-body" class="divide-y divide-slate-100 text-slate-700">
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    <i class="fa-solid fa-spinner fa-spin text-2xl text-[#0071BC] mb-2"></i>
                                    <p>Memuat catatan aktivitas...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Pagination -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4 pt-4 border-t border-slate-100">
                    <div id="table-pagination-info" class="text-xs text-slate-500">
                        Menampilkan data riwayat akses
                    </div>

                    <div id="table-pagination-controls" class="flex items-center gap-1.5">
                        <!-- Injected by JavaScript -->
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- Chart.js & Analytics Javascript Controller -->
    <script src="{{ asset('assets/js/features/lms/administrator/analytics.js') }}"></script>

@else
    <div class="flex flex-col min-h-screen items-center justify-center bg-slate-50 px-4 text-center">
        <div class="w-20 h-20 rounded-3xl bg-red-100 text-red-600 flex items-center justify-center text-3xl mb-4 shadow-sm">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">Akses Terbatas</h2>
        <p class="text-sm text-slate-500 max-w-md mt-2">
            Halaman ini khusus untuk Administrator Utama platform. Akun Anda tidak memiliki izin untuk melihat laporan analitik platform.
        </p>
        <a href="/" class="mt-6 px-6 py-2.5 rounded-xl bg-[#0071BC] text-white font-medium text-sm hover:bg-[#005a96] transition shadow-md">
            Kembali ke Beranda
        </a>
    </div>
@endif
