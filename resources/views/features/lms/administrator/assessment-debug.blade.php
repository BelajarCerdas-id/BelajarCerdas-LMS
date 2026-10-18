@include('components/sidebar-beranda', ['headerSideNav' => 'Exam Debugger'])

@if (Auth::user()->role === 'Administrator')
    <style>
        dialog .swal2-container,
        .swal2-top-modal-container,
        .swal2-container {
            z-index: 9999999 !important;
        }
        dialog .swal2-container,
        .swal2-top-modal-container {
            position: fixed !important;
            inset: 0 !important;
        }
    </style>
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out z-20 pb-20">
        <div class="mt-4 sm:mt-6 mx-4 sm:mx-8 space-y-6">

            <!-- HERO HEADER -->
            <section>
                <div class="rounded-3xl p-6 sm:p-8 text-white shadow-xl overflow-hidden relative border border-white/10 bg-[#0071BC]" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 60%, #002b49 100%) !important;">
                    <div class="absolute right-0 top-0 opacity-10 pointer-events-none select-none">
                        <i class="fa-solid fa-wrench text-[260px] translate-x-12 -translate-y-8"></i>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold tracking-wider uppercase mb-2">
                                <i class="fa-solid fa-shield-halved text-xs"></i>
                                Site Admin Tools
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                Exam & Attempt Debugger
                            </h1>
                            <p class="mt-2 text-sky-100/90 text-sm sm:text-base max-w-2xl leading-relaxed">
                                Buka kunci ujian siswa yang terblokir (<span class="font-semibold text-white">Cheating / Timeout</span>), ubah status ujian secara langsung, dan atur ulang batas pelanggaran tab switch.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 shrink-0">
                            <button id="btn-open-cache-manager" type="button"
                                class="cursor-pointer inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-semibold text-sm transition-all shadow-md backdrop-blur-md border border-white/20">
                                <i class="fa-solid fa-bolt text-sm"></i>
                                <span>RAM Cache Manager</span>
                            </button>
                            <button id="btn-refresh-attempts" type="button"
                                class="cursor-pointer inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/25 active:scale-95 text-white font-medium text-sm transition-all shadow-md backdrop-blur-md border border-white/20">
                                <i class="fa-solid fa-arrows-rotate text-sm" id="icon-refresh"></i>
                                <span>Segarkan Data</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PRIMARY NAVIGATION TABS -->
            <section class="flex flex-wrap items-center gap-3">
                <button type="button" id="tab-nav-attempts" onclick="switchMainTab('attempts')"
                    class="cursor-pointer inline-flex items-center gap-2.5 px-6 py-3.5 rounded-2xl font-bold text-sm transition-all shadow-xs bg-[#0071BC] text-white">
                    <i class="fa-solid fa-user-graduate text-base"></i>
                    <span>Sesi Ujian Siswa (Attempts)</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs bg-white/20 text-white font-semibold" id="badge-total-attempts">0</span>
                </button>
                <button type="button" id="tab-nav-assessments" onclick="switchMainTab('assessments')"
                    class="cursor-pointer inline-flex items-center gap-2.5 px-6 py-3.5 rounded-2xl font-bold text-sm transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-200">
                    <i class="fa-solid fa-book-open text-base text-[#0071BC]"></i>
                    <span>Daftar Assessment</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 font-semibold" id="badge-total-assessments">0</span>
                </button>
            </section>

            <!-- ======================================================== -->
            <!-- ATTEMPTS VIEW CONTAINER -->
            <!-- ======================================================== -->
            <div id="view-attempts" class="space-y-6">
                <!-- KPI SUMMARY CARDS -->
                <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Total Attempts -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sesi</div>
                            <div class="text-xl sm:text-2xl font-bold text-slate-800" id="stat-total">0</div>
                        </div>
                    </div>

                    <!-- Cheating / Blocked -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-red-200 bg-red-50/20 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-red-600 uppercase tracking-wider">Terblokir (Cheat)</div>
                            <div class="text-xl sm:text-2xl font-bold text-red-700" id="stat-cheating">0</div>
                        </div>
                    </div>

                    <!-- In Progress -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-blue-200 bg-blue-50/20 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#0071BC] flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Sedang Ujian</div>
                            <div class="text-xl sm:text-2xl font-bold text-blue-700" id="stat-in-progress">0</div>
                        </div>
                    </div>

                    <!-- Submitted -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-emerald-200 bg-emerald-50/20 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Selesai</div>
                            <div class="text-xl sm:text-2xl font-bold text-emerald-700" id="stat-submitted">0</div>
                        </div>
                    </div>

                    <!-- Timeout -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-amber-200 bg-amber-50/20 shadow-xs flex items-center gap-4 col-span-2 lg:col-span-1">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Waktu Habis</div>
                            <div class="text-xl sm:text-2xl font-bold text-amber-700" id="stat-timeout">0</div>
                        </div>
                    </div>
                </section>

                <!-- FILTERS & SEARCH BAR -->
                <section class="bg-white rounded-3xl p-4 sm:p-6 shadow-xs border border-slate-200 space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Status Filter Tabs -->
                        <div class="flex flex-wrap items-center bg-slate-100 p-1.5 rounded-2xl border border-slate-200/80 gap-1">
                            <button type="button" data-status="all"
                                class="status-filter-btn px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all cursor-pointer bg-white text-[#0071BC] shadow-xs">
                                Semua Sesi
                            </button>
                            <button type="button" data-status="cheating"
                                class="status-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-red-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-lock text-xs"></i>
                                Terblokir (Cheat)
                            </button>
                            <button type="button" data-status="in_progress"
                                class="status-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-blue-600">
                                Sedang Ujian
                            </button>
                            <button type="button" data-status="submitted"
                                class="status-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-emerald-600">
                                Selesai
                            </button>
                            <button type="button" data-status="timeout"
                                class="status-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-amber-600">
                                Timeout
                            </button>
                        </div>

                        <!-- Search and School Selector -->
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                            <div class="relative w-full sm:w-64">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="filter-search" placeholder="Cari siswa, NIS, judul ujian..."
                                    class="w-full h-11 pl-9 pr-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition">
                            </div>

                            <div class="w-full sm:w-56">
                                <select id="filter-school"
                                    class="w-full h-11 px-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition cursor-pointer">
                                    <option value="all">Semua Sekolah Mitra</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->nama_sekolah }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECONDARY FILTER BAR: DATE FILTER, PRESETS, SORT, CHEATING, ASSESSMENT, PER PAGE -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4 text-xs">
                        <!-- Date Filter & Quick Presets -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-slate-600 flex items-center gap-1.5 shrink-0">
                                <i class="fa-regular fa-calendar text-[#0071BC]"></i>
                                <span>Tanggal Sesi:</span>
                            </span>
                            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                                <input type="date" id="filter-date-start" class="bg-transparent text-xs text-slate-700 font-semibold outline-none cursor-pointer" title="Dari Tanggal">
                                <span class="text-slate-400 font-medium">s/d</span>
                                <input type="date" id="filter-date-end" class="bg-transparent text-xs text-slate-700 font-semibold outline-none cursor-pointer" title="Sampai Tanggal">
                            </div>
                            <button type="button" id="btn-quick-today" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition active:scale-95 cursor-pointer">
                                Hari Ini
                            </button>
                            <button type="button" id="btn-quick-yesterday" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition active:scale-95 cursor-pointer">
                                Kemarin
                            </button>
                            <button type="button" id="btn-quick-7days" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition active:scale-95 cursor-pointer">
                                7 Hari
                            </button>
                            <button type="button" id="btn-clear-date" class="hidden px-2 py-1.5 rounded-xl text-red-500 hover:bg-red-50 font-semibold transition cursor-pointer" title="Reset Filter Tanggal">
                                <i class="fa-solid fa-xmark mr-1"></i>Reset
                            </button>
                            <span id="date-filter-badge" class="hidden px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-bold text-[11px] border border-amber-300">
                                <i class="fa-solid fa-bolt mr-1 text-amber-600"></i>Throw All Data
                            </span>
                        </div>

                        <!-- Secondary Controls: Assessment, Sort, Cheating, Paginate -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Filter Assessment -->
                            <div class="w-full sm:w-48">
                                <select id="filter-assessment"
                                    class="w-full h-9 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-700 outline-none focus:border-[#0071BC] transition cursor-pointer">
                                    <option value="all">Semua Ujian</option>
                                    @if(isset($assessmentsList))
                                        @foreach($assessmentsList as $asm)
                                            <option value="{{ $asm->id }}" data-school="{{ $asm->school_partner_id }}">{{ Str::limit($asm->title, 26) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Cheating filter -->
                            <div class="w-auto">
                                <select id="filter-cheating-only"
                                    class="h-9 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-700 outline-none focus:border-[#0071BC] transition cursor-pointer">
                                    <option value="all">Semua Sesi</option>
                                    <option value="with_cheating">Ada Pelanggaran (>0x)</option>
                                    <option value="clean">Bersih (0x)</option>
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-slate-500 shrink-0">Urut:</span>
                                <select id="filter-sort-by"
                                    class="h-9 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-700 outline-none focus:border-[#0071BC] transition cursor-pointer">
                                    <option value="latest">Terbaru</option>
                                    <option value="oldest">Terlama</option>
                                    <option value="cheating">Pelanggaran Terbanyak</option>
                                    <option value="start_desc">Waktu Mulai (Terbaru)</option>
                                    <option value="start_asc">Waktu Mulai (Terlama)</option>
                                </select>
                            </div>

                            <!-- Paginate Selector -->
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-slate-500 shrink-0">Per Hal:</span>
                                <select id="filter-per-page"
                                    class="h-9 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-[#0071BC] transition cursor-pointer">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">Semua (Throw All)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ATTEMPTS TABLE CONTAINER -->
                <section class="bg-white rounded-3xl shadow-xs border border-slate-200 overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-800">Daftar Sesi Ujian Siswa</h2>
                            <p class="text-xs sm:text-sm text-slate-500">Centang kotak pada baris sesi untuk menghitung ulang (recount) beberapa sesi sekaligus, atau klik tombol Recount di setiap baris.</p>
                        </div>
                        <div class="text-xs text-slate-500" id="table-info">Memuat data...</div>
                    </div>

                    <!-- BATCH RECOUNT ACTION BAR (Hidden by default, shown when >= 1 checkbox is selected) -->
                    <div id="batch-action-bar" class="hidden px-5 py-3.5 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200/80 items-center justify-between gap-3 transition-all">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center min-w-[32px] h-7 px-2 rounded-lg bg-amber-500 text-slate-900 font-black text-xs shadow-xs" id="selected-attempts-count">0</span>
                            <div>
                                <span class="text-xs font-bold text-slate-800">Sesi Ujian Siswa Dipilih</span>
                                <span class="text-[11px] text-slate-500 ml-1.5 hidden sm:inline">• Klik tombol di samping untuk evaluasi ulang skor secara serentak</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-recount-selected" class="cursor-pointer inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-slate-900 font-bold text-xs shadow-xs transition">
                                <i class="fa-solid fa-calculator text-xs"></i>
                                <span>Recount Sesi Terpilih</span>
                            </button>
                            <button type="button" id="btn-clear-selected-attempts" class="cursor-pointer inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200 transition">
                                <i class="fa-solid fa-xmark text-xs"></i>
                                <span>Batal</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50/80 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-4 w-12 text-center">
                                        <input type="checkbox" id="check-all-attempts" class="w-4 h-4 rounded text-[#0071BC] focus:ring-sky-200 border-slate-300 cursor-pointer" title="Pilih Semua Sesi di Halaman Ini">
                                    </th>
                                    <th class="px-6 py-4">Siswa & Kelas</th>
                                    <th class="px-6 py-4">Assessment & Mapel</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Pelanggaran</th>
                                    <th class="px-6 py-4 text-center">Skor & Jawaban</th>
                                    <th class="px-6 py-4">Waktu & Sisa Durasi</th>
                                    <th class="px-6 py-4 text-center">Aksi Debugger</th>
                                </tr>
                            </thead>
                            <tbody id="attempts-table-body" class="divide-y divide-slate-100">
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                                        <div>Memuat data attempt ujian...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION BAR -->
                    <div class="p-4 sm:p-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4" id="pagination-container">
                        <div class="text-xs sm:text-sm text-slate-500" id="pagination-text"></div>
                        <div class="flex items-center gap-2" id="pagination-buttons"></div>
                    </div>
                </section>
            </div> <!-- end #view-attempts -->

            <!-- ======================================================== -->
            <!-- ASSESSMENT VIEWS CONTAINER -->
            <!-- ======================================================== -->
            <div id="view-assessments" class="space-y-6 hidden">
                <!-- KPI SUMMARY CARDS FOR ASSESSMENTS -->
                <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Assessments -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Assessment</div>
                            <div class="text-xl sm:text-2xl font-bold text-slate-800" id="stat-asm-total">0</div>
                        </div>
                    </div>

                    <!-- Ongoing / Active -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-blue-200 bg-blue-50/20 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#0071BC] flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Sedang Aktif</div>
                            <div class="text-xl sm:text-2xl font-bold text-blue-700" id="stat-asm-active">0</div>
                        </div>
                    </div>

                    <!-- Remedial & Susulan -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-purple-200 bg-purple-50/20 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-arrows-split-up-and-left"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Remedial / Susulan</div>
                            <div class="text-xl sm:text-2xl font-bold text-purple-700" id="stat-asm-remedial">0</div>
                        </div>
                    </div>

                    <!-- Total Attempts / Participants -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-emerald-200 bg-emerald-50/20 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Total Sesi Ujian</div>
                            <div class="text-xl sm:text-2xl font-bold text-emerald-700" id="stat-asm-attempts">0</div>
                        </div>
                    </div>
                </section>

                <!-- FILTERS & SEARCH FOR ASSESSMENTS -->
                <section class="bg-white rounded-3xl p-4 sm:p-6 shadow-xs border border-slate-200 space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Category Filter Tabs -->
                        <div class="flex flex-wrap items-center bg-slate-100 p-1.5 rounded-2xl border border-slate-200/80 gap-1">
                            <button type="button" data-category="all"
                                class="asm-category-filter-btn px-4 py-2 text-xs sm:text-sm font-bold rounded-xl transition-all cursor-pointer bg-white text-[#0071BC] shadow-xs">
                                Semua Kategori
                            </button>
                            <button type="button" data-category="main"
                                class="asm-category-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-blue-600">
                                Utama (Main)
                            </button>
                            <button type="button" data-category="remedial"
                                class="asm-category-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-purple-600">
                                Remedial
                            </button>
                            <button type="button" data-category="susulan"
                                class="asm-category-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-amber-600">
                                Susulan
                            </button>
                            <button type="button" data-category="pengayaan"
                                class="asm-category-filter-btn px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-all cursor-pointer text-slate-600 hover:text-emerald-600">
                                Pengayaan
                            </button>
                        </div>

                        <!-- Search, Status and School Selector -->
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                            <!-- Status Exam Selector -->
                            <div class="w-full sm:w-44">
                                <select id="filter-asm-status"
                                    class="w-full h-11 px-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition cursor-pointer">
                                    <option value="all">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="upcoming">Mendatang</option>
                                    <option value="expired">Berakhir</option>
                                </select>
                            </div>

                            <div class="relative w-full sm:w-56">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="filter-asm-search" placeholder="Cari judul, mapel..."
                                    class="w-full h-11 pl-9 pr-4 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition">
                            </div>

                            <div class="w-full sm:w-52">
                                <select id="filter-asm-school"
                                    class="w-full h-11 px-3 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-medium text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition cursor-pointer">
                                    <option value="all">Semua Sekolah Mitra</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->nama_sekolah }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECONDARY FILTER BAR FOR ASSESSMENTS: DATE & PER PAGE -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-slate-600 flex items-center gap-1.5 shrink-0">
                                <i class="fa-regular fa-calendar text-[#0071BC]"></i>
                                <span>Tanggal Assessment:</span>
                            </span>
                            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                                <input type="date" id="filter-asm-date-start" class="bg-transparent text-xs text-slate-700 font-semibold outline-none cursor-pointer" title="Dari Tanggal">
                                <span class="text-slate-400 font-medium">s/d</span>
                                <input type="date" id="filter-asm-date-end" class="bg-transparent text-xs text-slate-700 font-semibold outline-none cursor-pointer" title="Sampai Tanggal">
                            </div>
                            <button type="button" id="btn-asm-quick-today" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition active:scale-95 cursor-pointer">
                                Hari Ini
                            </button>
                            <button type="button" id="btn-asm-clear-date" class="hidden px-2 py-1.5 rounded-xl text-red-500 hover:bg-red-50 font-semibold transition cursor-pointer" title="Reset Filter Tanggal">
                                <i class="fa-solid fa-xmark mr-1"></i>Reset
                            </button>
                            <span id="asm-date-filter-badge" class="hidden px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-bold text-[11px] border border-amber-300">
                                <i class="fa-solid fa-bolt mr-1 text-amber-600"></i>Throw All
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="font-medium text-slate-500 shrink-0">Per Hal:</span>
                            <select id="filter-asm-per-page"
                                class="h-9 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-[#0071BC] transition cursor-pointer">
                                <option value="15" selected>15</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">Semua (Throw All)</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- ASSESSMENTS TABLE CONTAINER -->
                <section class="bg-white rounded-3xl shadow-xs border border-slate-200 overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-800">Daftar Assessment & Rekapitulasi Nilai</h2>
                            <p class="text-xs sm:text-sm text-slate-500">Lihat rincian butir soal, daftar peserta, dan hitung ulang (recount) nilai per assessment.</p>
                        </div>
                        <div class="text-xs text-slate-500" id="asm-table-info">Memuat data...</div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50/80 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4">Assessment & Mapel</th>
                                    <th class="px-6 py-4">Kelas & Sekolah</th>
                                    <th class="px-6 py-4">Jadwal & Status</th>
                                    <th class="px-6 py-4 text-center">Soal & Bobot</th>
                                    <th class="px-6 py-4 text-center">Peserta & Rekap Nilai</th>
                                    <th class="px-6 py-4 text-center">Aksi Assessment</th>
                                </tr>
                            </thead>
                            <tbody id="assessments-table-body" class="divide-y divide-slate-100">
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                                        <div>Memuat data assessment...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION BAR FOR ASSESSMENTS -->
                    <div class="p-4 sm:p-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4" id="asm-pagination-container">
                        <div class="text-xs sm:text-sm text-slate-500" id="asm-pagination-text"></div>
                        <div class="flex items-center gap-2" id="asm-pagination-buttons"></div>
                    </div>
                </section>
            </div> <!-- end #view-assessments -->

        </div>
    </div>

    <!-- MODAL EDIT ATTEMPT -->
    <dialog id="modal-edit-attempt" class="modal">
        <div class="modal-box bg-white max-w-lg p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100">
            <!-- Modal Header -->
            <div class="bg-[#0071BC] p-6 text-white flex items-start justify-between" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 100%) !important;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Edit Status & Attempt Siswa</h3>
                        <p class="text-xs text-sky-100" id="modal-student-name">Nama Siswa</p>
                    </div>
                </div>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>

            <!-- Modal Form -->
            <form id="form-edit-attempt" class="p-6 space-y-4">
                <input type="hidden" id="edit-attempt-id">

                <!-- Info Ujian -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs space-y-1">
                    <div class="text-slate-500 font-medium">Ujian: <span class="font-bold text-slate-800" id="modal-assessment-name">-</span></div>
                    <div class="text-slate-500 font-medium">Sekolah: <span class="font-semibold text-slate-700" id="modal-school-name">-</span></div>
                </div>

                <!-- Status Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Attempt
                    </label>
                    <select id="edit-status" class="w-full h-11 px-4 text-sm bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 cursor-pointer">
                        <option value="in_progress">in_progress (Sedang Berjalan / Aktif)</option>
                        <option value="cheating">cheating (Terblokir Curang)</option>
                        <option value="submitted">submitted (Telah Diselesaikan)</option>
                        <option value="timeout">timeout (Waktu Berakhir)</option>
                    </select>
                </div>

                <!-- Tab Switch / Attempt Count -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jumlah Pelanggaran Tab Switch
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="edit-tab-switch" min="0" max="999" class="w-full h-11 px-4 text-sm bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100">
                        <button type="button" onclick="$('#edit-tab-switch').val(0)" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-600 transition cursor-pointer whitespace-nowrap">
                            Reset ke 0
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Jika >= 3, siswa akan otomatis terdeteksi curang oleh frontend.</p>
                </div>

                <!-- Tambah Waktu Ujian (Menit) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tambah Durasi Waktu Ekstra (Menit)
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="edit-add-minutes" min="0" max="600" value="0" placeholder="0" class="w-full h-11 px-4 text-sm bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100">
                        <div class="flex gap-1 shrink-0">
                            <button type="button" onclick="$('#edit-add-minutes').val(15)" class="px-2.5 py-2.5 rounded-xl bg-blue-50 text-[#0071BC] hover:bg-blue-100 text-xs font-bold transition cursor-pointer">+15m</button>
                            <button type="button" onclick="$('#edit-add-minutes').val(30)" class="px-2.5 py-2.5 rounded-xl bg-blue-50 text-[#0071BC] hover:bg-blue-100 text-xs font-bold transition cursor-pointer">+30m</button>
                            <button type="button" onclick="$('#edit-add-minutes').val(60)" class="px-2.5 py-2.5 rounded-xl bg-blue-50 text-[#0071BC] hover:bg-blue-100 text-xs font-bold transition cursor-pointer">+60m</button>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Menambah menit dari sekarang jika waktu sudah habis, atau memperpanjang waktu berakhir yang ada.</p>
                </div>

                <!-- Opsi Pemulihan Jawaban Siswa -->
                <div class="p-3.5 bg-blue-50/60 border border-blue-200/80 rounded-2xl space-y-2">
                    <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-[#0071BC]"></i>
                        <span>Opsi Jawaban Siswa</span>
                    </div>
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="edit-reset-to-draft" class="mt-0.5 rounded text-[#0071BC] focus:ring-sky-200 cursor-pointer">
                        <span class="text-xs text-slate-600 leading-tight">
                            <strong>Kembalikan Jawaban ke Draft:</strong> Mengubah status jawaban siswa yang sudah tersimpan menjadi 'draft' agar siswa dapat merevisi jawabannya.
                        </span>
                    </label>
                    <div class="pt-2 border-t border-blue-200/60 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-500 font-medium">Aksi cepat jawaban:</span>
                        <div class="flex gap-1.5">
                            <button type="button" onclick="triggerResetAnswers('empty')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 hover:bg-slate-100 text-[11px] font-bold text-slate-700 transition cursor-pointer">
                                Bersihkan Soal Kosong
                            </button>
                            <button type="button" onclick="triggerResetAnswers('delete_all')" class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-100 border border-red-200 text-[11px] font-bold text-red-600 transition cursor-pointer">
                                Reset Semua Jawaban
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Shortcut Ubah Nilai Ujian (Per Exam Basis) -->
                <div class="p-3.5 bg-amber-50/60 border border-amber-200/80 rounded-2xl flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-ruler text-amber-600"></i>
                            <span>Ubah Nilai Ujian Siswa</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ubah total nilai atau jawaban butir soal dengan verifikasi 2 langkah.</p>
                    </div>
                    <button type="button" id="btn-edit-exam-from-attempt-modal" class="px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-slate-900 font-bold text-xs transition cursor-pointer shadow-xs shrink-0">
                        Ubah Nilai Ujian
                    </button>
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit-attempt').close()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-edit-attempt" class="px-5 py-2.5 rounded-xl bg-[#0071BC] hover:bg-[#005A96] text-white text-sm font-bold shadow-md transition cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- MODAL RAM CACHE MANAGER -->
    <dialog id="modal-cache-manager" class="modal p-3 sm:p-6 backdrop:bg-slate-900/50 backdrop:backdrop-blur-xs">
        <div class="modal-box w-11/12 max-w-6xl max-h-[92vh] bg-white p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
            <!-- Header (Pinned) -->
            <div class="bg-emerald-700 p-5 sm:p-6 text-white flex items-start justify-between shrink-0 shadow-xs" style="background: linear-gradient(135deg, #059669 0%, #047857 50%, #064e3b 100%) !important;">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fa-solid fa-bolt text-yellow-300"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider mb-1" style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff;">
                            <i class="fa-solid fa-microchip"></i>
                            In-Memory RAM Acceleration
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white" style="color: #ffffff !important;">RAM Cache Soal Ujian</h3>
                    </div>
                </div>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20 text-base cursor-pointer" style="color: #ffffff !important;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>

            <!-- Content Area (Scrollable body) -->
            <div class="p-5 sm:p-6 space-y-5 overflow-y-auto flex-1 min-h-0 bg-slate-50/50">
                <!-- Summary Metrics -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Ujian</div>
                            <div class="text-xl font-extrabold text-slate-800" id="cache-stat-total">0</div>
                        </div>
                    </div>

                    <div class="bg-white border border-emerald-200/80 rounded-2xl p-4 shadow-2xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Tersimpan di RAM</div>
                            <div class="text-xl font-extrabold text-emerald-700" id="cache-stat-cached">0</div>
                        </div>
                    </div>

                    <div class="bg-white border border-amber-200/80 rounded-2xl p-4 shadow-2xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-hourglass-start"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Belum di-Cache</div>
                            <div class="text-xl font-extrabold text-amber-700" id="cache-stat-uncached">0</div>
                        </div>
                    </div>

                    <div class="bg-white border border-blue-200/80 rounded-2xl p-4 shadow-2xs flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-[#0071BC] flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Cache Driver</div>
                            <div class="text-base font-extrabold text-blue-800 uppercase" id="cache-stat-driver">-</div>
                        </div>
                    </div>
                </div>

                <!-- Global Action Bar -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 bg-white border border-slate-200 rounded-2xl shadow-2xs">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="btn-warm-all-cache" class="cursor-pointer inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition shadow-xs">
                            <i class="fa-solid fa-fire text-xs"></i>
                            <span>Pre-load Semua Ujian Aktif</span>
                        </button>
                        <button type="button" id="btn-clear-all-cache" class="cursor-pointer inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 active:scale-95 text-red-600 border border-red-200 text-xs font-bold transition">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                            <span>Bersihkan Seluruh Cache Soal</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </div>
                            <input type="text" id="cache-search" placeholder="Cari judul ujian / mapel..." class="w-full h-10 pl-10 pr-4 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium outline-none focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-200 transition">
                        </div>
                        <button type="button" id="btn-refresh-cache-status" class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center cursor-pointer transition shrink-0" title="Segarkan Status Cache">
                            <i class="fa-solid fa-arrows-rotate text-xs" id="icon-refresh-cache"></i>
                        </button>
                    </div>
                </div>

                <!-- Assessment Cache Table -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-100/80 text-[11px] uppercase font-bold text-slate-600 border-b border-slate-200">
                                <tr>
                                    <th class="px-5 py-3.5 min-w-[220px]">Assessment & Mapel</th>
                                    <th class="px-5 py-3.5 min-w-[150px]">Kelas & Sekolah</th>
                                    <th class="px-5 py-3.5 min-w-[160px]">Jadwal Ujian</th>
                                    <th class="px-5 py-3.5 min-w-[170px] text-center">Status Cache</th>
                                    <th class="px-5 py-3.5 min-w-[140px] text-center">Aksi RAM</th>
                                </tr>
                            </thead>
                            <tbody id="cache-table-body" class="divide-y divide-slate-100">
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-spinner fa-spin text-xl mb-1.5 text-emerald-600"></i>
                                        <div>Memeriksa status cache RAM...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer (Pinned) -->
            <div class="p-4 sm:p-5 bg-white border-t border-slate-200/80 flex items-center justify-between shrink-0">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-emerald-600 text-sm"></i>
                    <span>Soal ujian otomatis dimuat ke RAM pada kunjungan siswa pertama jika belum di-cache.</span>
                </div>
                <form method="dialog">
                    <button class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Tutup
                    </button>
                </form>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- MODAL ASSESSMENT DETAIL -->
    <dialog id="modal-assessment-detail" class="modal p-3 sm:p-6 backdrop:bg-slate-900/50 backdrop:backdrop-blur-xs">
        <div class="modal-box w-11/12 max-w-6xl max-h-[92vh] bg-white p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
            <!-- Header (Pinned) -->
            <div class="bg-[#0071BC] p-5 sm:p-6 text-white flex items-start justify-between shrink-0 shadow-xs" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 50%, #002b49 100%) !important;">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fa-solid fa-book-open text-white"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white" id="modal-asm-badge-category">
                                Main
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/10 text-sky-100" id="modal-asm-badge-mapel">
                                Mapel
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white" id="modal-asm-title">Judul Assessment</h3>
                        <p class="text-xs text-sky-100/90 mt-0.5" id="modal-asm-subtitle">Kelas • Sekolah • Durasi</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-recount-all-in-modal" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-500 active:scale-95 text-slate-900 font-bold text-xs shadow-md transition">
                        <i class="fa-solid fa-calculator text-xs"></i>
                        <span>Recount Seluruh Siswa</span>
                    </button>
                    <form method="dialog">
                        <button class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20 text-base cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sub Navigation Tabs inside Modal -->
            <div class="flex items-center gap-2 px-6 pt-3 bg-slate-50 border-b border-slate-200 shrink-0">
                <button type="button" id="subtab-btn-participants" onclick="switchModalSubTab('participants')"
                    class="modal-subtab-btn px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 border-[#0071BC] text-[#0071BC] transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-users text-xs"></i>
                    <span>Peserta & Nilai (<span id="modal-count-participants">0</span>)</span>
                </button>
                <button type="button" id="subtab-btn-questions" onclick="switchModalSubTab('questions')"
                    class="modal-subtab-btn px-4 py-2.5 text-xs sm:text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-clipboard-question text-xs"></i>
                    <span>Daftar Butir Soal (<span id="modal-count-questions">0</span>)</span>
                </button>
                <button type="button" id="subtab-btn-info" onclick="switchModalSubTab('info')"
                    class="modal-subtab-btn px-4 py-2.5 text-xs sm:text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-circle-info text-xs"></i>
                    <span>Informasi & Jadwal</span>
                </button>
            </div>

            <!-- Content Area (Scrollable body) -->
            <div class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 bg-slate-50/50 space-y-4">
                <!-- SUBTAB: PARTICIPANTS -->
                <div id="subtab-content-participants" class="space-y-4">
                    <!-- Participant Metrics -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-slate-500 uppercase">Peserta</div>
                                <div class="text-lg font-extrabold text-slate-800" id="modal-stat-participants">0</div>
                            </div>
                        </div>
                        <div class="bg-white border border-emerald-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-emerald-700 uppercase">Rata-rata Skor</div>
                                <div class="text-lg font-extrabold text-emerald-700" id="modal-stat-avg">0</div>
                            </div>
                        </div>
                        <div class="bg-white border border-blue-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#0071BC] flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-blue-700 uppercase">Tertinggi</div>
                                <div class="text-lg font-extrabold text-blue-800" id="modal-stat-max">0</div>
                            </div>
                        </div>
                        <div class="bg-white border border-amber-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-arrow-trend-down"></i>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-amber-700 uppercase">Terendah</div>
                                <div class="text-lg font-extrabold text-amber-800" id="modal-stat-min">0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants Table -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead class="bg-slate-100/80 text-[11px] uppercase font-bold text-slate-600 border-b border-slate-200">
                                    <tr>
                                        <th class="px-5 py-3">Siswa & NISN</th>
                                        <th class="px-5 py-3">Kelas</th>
                                        <th class="px-5 py-3 text-center">Status</th>
                                        <th class="px-5 py-3 text-center">Pelanggaran</th>
                                        <th class="px-3 py-3 text-center text-emerald-700">Benar</th>
                                        <th class="px-3 py-3 text-center text-red-600">Salah</th>
                                        <th class="px-3 py-3 text-center text-slate-500">Kosong</th>
                                        <th class="px-5 py-3 text-center">Skor Ujian</th>
                                        <th class="px-5 py-3 text-center min-w-[210px]">Aksi Siswa</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-participants-table-body" class="divide-y divide-slate-100">
                                    <tr>
                                        <td colspan="9" class="px-5 py-8 text-center text-slate-400">
                                            <i class="fa-solid fa-spinner fa-spin text-xl mb-1.5 text-[#0071BC]"></i>
                                            <div>Memuat data peserta...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB: QUESTIONS -->
                <div id="subtab-content-questions" class="space-y-4 hidden">
                    <div id="modal-questions-list" class="space-y-3">
                        <div class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-xl mb-1.5 text-[#0071BC]"></i>
                            <div>Memuat butir soal...</div>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB: INFO -->
                <div id="subtab-content-info" class="space-y-4 hidden">
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="font-bold text-slate-500 uppercase">Jadwal Mulai:</span>
                                <div class="text-sm font-semibold text-slate-800" id="modal-info-start">-</div>
                            </div>
                            <div>
                                <span class="font-bold text-slate-500 uppercase">Jadwal Selesai:</span>
                                <div class="text-sm font-semibold text-slate-800" id="modal-info-end">-</div>
                            </div>
                            <div>
                                <span class="font-bold text-slate-500 uppercase">Durasi Pengerjaan:</span>
                                <div class="text-sm font-semibold text-slate-800" id="modal-info-duration">-</div>
                            </div>
                            <div>
                                <span class="font-bold text-slate-500 uppercase">Ujian Induk (Parent):</span>
                                <div class="text-sm font-semibold text-slate-800" id="modal-info-parent">-</div>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-100">
                            <span class="font-bold text-slate-500 uppercase">Pengaturan Ujian:</span>
                            <div class="flex flex-wrap gap-2 mt-2" id="modal-info-settings"></div>
                        </div>
                        <div class="pt-3 border-t border-slate-100">
                            <span class="font-bold text-slate-500 uppercase">Petunjuk / Instruksi Ujian:</span>
                            <div class="mt-1 text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 leading-relaxed" id="modal-info-instruction">
                                -
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer (Pinned) -->
            <div class="p-4 sm:p-5 bg-white border-t border-slate-200/80 flex items-center justify-between shrink-0">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-calculator text-amber-500 text-sm"></i>
                    <span>Tombol Recount mengevaluasi ulang butir soal objektif. Soal ESSAY tidak di-recount karena dinilai manual oleh guru.</span>
                </div>
                <form method="dialog">
                    <button class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Tutup
                    </button>
                </form>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- MODAL SPECIFIC STUDENT EXAM DETAIL -->
    <dialog id="modal-student-exam-detail" class="modal p-2 sm:p-5 backdrop:bg-slate-900/60 backdrop:backdrop-blur-xs z-[60]">
        <div class="modal-box w-11/12 max-w-5xl max-h-[92vh] bg-white p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
            <!-- Modal Header (Pinned) -->
            <div class="p-5 sm:p-6 text-white flex items-start justify-between shrink-0 shadow-xs" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 50%, #002b49 100%) !important;">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fa-solid fa-file-signature text-white"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white" id="sed-badge-status">
                                Status
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/10 text-sky-100" id="sed-badge-mapel">
                                Mapel
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/10 text-sky-100" id="sed-badge-category">
                                Category
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white" id="sed-student-name">Nama Siswa</h3>
                        <p class="text-xs text-sky-100/90 mt-0.5" id="sed-subtitle">NISN • Kelas • Ujian</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-sed-edit-exam" class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 active:scale-95 text-white font-bold text-xs shadow-md transition">
                        <i class="fa-solid fa-pen-ruler text-xs"></i>
                        <span>Ubah Nilai Ujian (Per Exam)</span>
                    </button>
                    <button type="button" id="btn-sed-recount" class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-500 active:scale-95 text-slate-900 font-bold text-xs shadow-md transition">
                        <i class="fa-solid fa-calculator text-xs"></i>
                        <span>Recount Nilai Siswa</span>
                    </button>
                    <form method="dialog">
                        <button class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20 text-base cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Body Area (Scrollable) -->
            <div class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 bg-slate-50/50 space-y-4">
                <!-- Summary Metrics (5 cards) -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Skor Ujian</div>
                            <div class="text-base sm:text-lg font-extrabold text-slate-800" id="sed-stat-score">0</div>
                        </div>
                    </div>
                    <div class="bg-white border border-emerald-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-emerald-700 uppercase">Benar</div>
                            <div class="text-base sm:text-lg font-extrabold text-emerald-700" id="sed-stat-correct">0</div>
                        </div>
                    </div>
                    <div class="bg-white border border-red-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-red-700 uppercase">Salah</div>
                            <div class="text-base sm:text-lg font-extrabold text-red-600" id="sed-stat-wrong">0</div>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-minus"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Kosong</div>
                            <div class="text-base sm:text-lg font-extrabold text-slate-600" id="sed-stat-empty">0</div>
                        </div>
                    </div>
                    <div class="bg-white border border-purple-200/80 rounded-2xl p-3.5 shadow-2xs flex items-center gap-3 col-span-2 sm:col-span-1">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-base shrink-0">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-purple-700 uppercase">Tab Switch</div>
                            <div class="text-base sm:text-lg font-extrabold text-purple-800" id="sed-stat-switch">0x</div>
                        </div>
                    </div>
                </div>

                <!-- Timing & Duration Info Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-clock text-[#0071BC]"></i>
                        <span>Mulai: <strong id="sed-time-start" class="text-slate-800">-</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-hourglass-end text-amber-500"></i>
                        <span>Batas/Selesai: <strong id="sed-time-expire" class="text-slate-800">-</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-stopwatch text-slate-400"></i>
                        <span>Durasi Ujian: <strong id="sed-time-duration" class="text-slate-800">-</strong></span>
                    </div>
                </div>

                <!-- Question Filter Tabs -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="flex flex-wrap items-center bg-slate-100 p-1.5 rounded-2xl border border-slate-200/80 gap-1">
                        <button type="button" onclick="filterStudentExamQuestions('all')" data-sed-filter="all"
                            class="sed-filter-btn px-3.5 py-1.5 text-xs font-bold rounded-xl transition cursor-pointer bg-white text-[#0071BC] shadow-xs">
                            Semua Soal (<span id="sed-count-all">0</span>)
                        </button>
                        <button type="button" onclick="filterStudentExamQuestions('correct')" data-sed-filter="correct"
                            class="sed-filter-btn px-3.5 py-1.5 text-xs font-semibold rounded-xl transition cursor-pointer text-slate-600 hover:text-emerald-700">
                            <i class="fa-solid fa-check text-[10px] text-emerald-600 mr-1"></i> Benar (<span id="sed-count-correct">0</span>)
                        </button>
                        <button type="button" onclick="filterStudentExamQuestions('wrong')" data-sed-filter="wrong"
                            class="sed-filter-btn px-3.5 py-1.5 text-xs font-semibold rounded-xl transition cursor-pointer text-slate-600 hover:text-red-600">
                            <i class="fa-solid fa-xmark text-[10px] text-red-600 mr-1"></i> Salah (<span id="sed-count-wrong">0</span>)
                        </button>
                        <button type="button" onclick="filterStudentExamQuestions('empty')" data-sed-filter="empty"
                            class="sed-filter-btn px-3.5 py-1.5 text-xs font-semibold rounded-xl transition cursor-pointer text-slate-600 hover:text-slate-800">
                            <i class="fa-solid fa-minus text-[10px] text-slate-400 mr-1"></i> Kosong (<span id="sed-count-empty">0</span>)
                        </button>
                    </div>
                    <div class="text-xs text-slate-500 font-medium">
                        Perbandingan jawaban siswa terhadap kunci jawaban sistem
                    </div>
                </div>

                <!-- Questions List Container -->
                <div id="sed-questions-list" class="space-y-3">
                    <div class="p-12 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                        <div>Memuat butir soal dan jawaban siswa...</div>
                    </div>
                </div>
            </div>

            <!-- Footer (Pinned) -->
            <div class="p-4 sm:p-5 bg-white border-t border-slate-200/80 flex items-center justify-between shrink-0">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-[#0071BC] text-sm"></i>
                    <span>Hijau = Kunci/Pilihan Benar, Merah = Pilihan Salah Siswa, Abu-abu = Belum/Tidak Dijawab.</span>
                </div>
                <form method="dialog">
                    <button class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Tutup
                    </button>
                </form>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- MODAL EDIT NILAI & JAWABAN PER SOAL (PER QUESTION BASIS) -->
    <dialog id="modal-edit-question-grade" class="modal p-3 sm:p-6 backdrop:bg-slate-900/60 backdrop:backdrop-blur-xs z-[70]">
        <div class="modal-box w-11/12 max-w-2xl bg-white p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
            <!-- Header -->
            <div class="p-5 text-white flex items-center justify-between shrink-0" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 100%) !important;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-pen-to-square text-white"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/20 text-white" id="eqm-badge-number">Soal #1</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/20 text-sky-100" id="eqm-badge-type">MCQ</span>
                        </div>
                        <h4 class="text-base sm:text-lg font-bold text-white">Ubah Nilai & Jawaban Butir Soal</h4>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modal-edit-question-grade').close()" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20 text-base cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 max-h-[75vh]">
                <!-- Info Siswa & Ujian -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div>Siswa: <strong class="text-slate-800" id="eqm-student-name">-</strong></div>
                    <div>Bobot Maksimal: <strong class="text-[#0071BC]" id="eqm-max-weight">0</strong></div>
                </div>

                <!-- Cuplikan Soal -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pertanyaan:</label>
                    <div id="eqm-question-text" class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-800 leading-relaxed max-h-32 overflow-y-auto font-medium">
                        -
                    </div>
                </div>

                <!-- Input Nilai / Skor -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>Nilai / Skor Butir Soal:</span>
                        </label>
                        <span class="text-xs text-slate-400">Skor saat ini: <strong id="eqm-current-score" class="text-slate-700">0</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" id="eqm-input-score" step="0.01" min="0" class="w-full h-11 px-3.5 text-sm font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-[#0071BC] focus:bg-white transition" placeholder="Masukkan nilai skor">
                        <button type="button" id="eqm-btn-full-score" class="px-3 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200 shrink-0 transition cursor-pointer">
                            Nilai Penuh
                        </button>
                        <button type="button" id="eqm-btn-zero-score" class="px-3 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 shrink-0 transition cursor-pointer">
                            Skor 0
                        </button>
                    </div>
                </div>

                <!-- Input Jawaban Siswa -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-check-to-slot text-[#0071BC]"></i>
                            <span>Jawaban Siswa:</span>
                        </label>
                        <span class="text-[11px] text-slate-400" id="eqm-answer-guide">Pilih jawaban</span>
                    </div>
                    <!-- Container dinamis jawaban berdasarkan tipe soal -->
                    <div id="eqm-answer-input-container" class="space-y-2">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Status Koreksi & Catatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Penilaian:</label>
                        <select id="eqm-select-grading-status" class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-700 outline-none focus:border-[#0071BC]">
                            <option value="graded">Graded (Sudah Dinilai)</option>
                            <option value="pending">Pending (Menunggu Penilaian)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Jawaban:</label>
                        <select id="eqm-select-status-answer" class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-700 outline-none focus:border-[#0071BC]">
                            <option value="submitted">Submitted (Terkirim)</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>

                <!-- Catatan / Feedback Guru -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Feedback Guru (Opsional):</label>
                    <input type="text" id="eqm-input-feedback" class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-700 outline-none focus:border-[#0071BC]" placeholder="Catatan untuk evaluasi...">
                </div>

                <!-- 2-Step Warning Notice Banner -->
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-2.5 text-xs text-amber-900 leading-relaxed">
                    <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong>Pengamanan 2 Langkah:</strong> Klik tombol simpan akan membuka peringatan langkah 1 dan konfirmasi kata kunci verifikasi langkah 2 sebelum perubahan disimpan secara permanen ke database.
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 sm:p-5 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
                <button type="button" onclick="document.getElementById('modal-edit-question-grade').close()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btn-save-question-edit" class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0071BC] hover:bg-sky-700 active:scale-95 text-white font-bold text-xs shadow-md transition">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan (2-Step Warning)</span>
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- MODAL EDIT NILAI UJIAN MENYELURUH (PER EXAM BASIS) -->
    <dialog id="modal-edit-exam-grades" class="modal p-3 sm:p-6 backdrop:bg-slate-900/60 backdrop:backdrop-blur-xs z-[70]">
        <div class="modal-box w-11/12 max-w-4xl max-h-[92vh] bg-white p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
            <!-- Header -->
            <div class="p-5 text-white flex items-center justify-between shrink-0" style="background: linear-gradient(135deg, #0071BC 0%, #004f84 100%) !important;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-pen-ruler text-white"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/20 text-white">Per Exam Basis</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/20 text-sky-100" id="eem-badge-total-q">0 Butir Soal</span>
                        </div>
                        <h4 class="text-base sm:text-lg font-bold text-white">Ubah Nilai Ujian (Per Exam Basis)</h4>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modal-edit-exam-grades').close()" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20 text-base cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Mode Selector Tabs inside Modal -->
            <div class="flex items-center border-b border-slate-200 bg-slate-50 px-5 pt-2 gap-2 shrink-0">
                <button type="button" onclick="switchExamEditTab('scale')" id="eem-tab-btn-scale" class="eem-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-[#0071BC] text-[#0071BC] transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Set Total Nilai (Skala Proporsional)</span>
                </button>
                <button type="button" onclick="switchExamEditTab('grid')" id="eem-tab-btn-grid" class="eem-tab-btn px-4 py-2.5 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-table-list"></i>
                    <span>Tabel Edit Seluruh Soal</span>
                </button>
                <button type="button" onclick="switchExamEditTab('preset')" id="eem-tab-btn-preset" class="eem-tab-btn px-4 py-2.5 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Aksi Preset Cepat</span>
                </button>
            </div>

            <!-- Body Area -->
            <div class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 space-y-4">
                <!-- Meta Info Siswa & Ujian -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="text-slate-500">Siswa:</div>
                        <div class="font-bold text-slate-800 text-sm" id="eem-student-name">-</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Ujian:</div>
                        <div class="font-bold text-slate-800" id="eem-assessment-title">-</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Total Nilai Saat Ini:</div>
                        <div class="font-extrabold text-[#0071BC] text-base" id="eem-current-total-score">0 / 0</div>
                    </div>
                </div>

                <!-- TAB 1: SCALE / TARGET TOTAL SCORE -->
                <div id="eem-pane-scale" class="space-y-4">
                    <div class="p-4 bg-sky-50/60 border border-sky-200 rounded-2xl text-xs text-sky-950 leading-relaxed">
                        <i class="fa-solid fa-circle-info text-[#0071BC] mr-1.5"></i>
                        Mode ini akan mendistribusikan total nilai yang Anda tentukan secara proporsional ke setiap butir soal berdasarkan bobot masing-masing butir soal.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Target Total Nilai Baru:
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" id="eem-input-target-score" step="0.01" min="0" class="w-full h-12 px-4 text-lg font-extrabold text-slate-800 bg-slate-50 border border-slate-300 rounded-2xl outline-none focus:border-[#0071BC] focus:bg-white transition" placeholder="Contoh: 85.00">
                            <span class="text-slate-500 text-sm font-bold shrink-0">/ <span id="eem-max-score-label">100</span></span>
                        </div>
                    </div>

                    <!-- Quick preset score buttons -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="text-xs font-semibold text-slate-500 mr-1">Preset Nilai:</span>
                        <button type="button" onclick="setEemTargetScore(100)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer">100 (Maksimal)</button>
                        <button type="button" onclick="setEemTargetScore(90)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer">90</button>
                        <button type="button" onclick="setEemTargetScore(85)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer">85</button>
                        <button type="button" onclick="setEemTargetScore(75)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer">75 (Standar KKM)</button>
                        <button type="button" onclick="setEemTargetScore(60)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer">60</button>
                        <button type="button" onclick="setEemTargetScore(0)" class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold transition active:scale-95 cursor-pointer">0 (Nol)</button>
                    </div>
                </div>

                <!-- TAB 2: BATCH ALL QUESTIONS GRID -->
                <div id="eem-pane-grid" class="hidden space-y-3">
                    <div class="p-3 bg-slate-100 rounded-xl text-xs text-slate-600 flex items-center justify-between">
                        <span>Edit nilai masing-masing soal secara langsung pada tabel di bawah:</span>
                        <span class="font-bold text-slate-800">Total Terhitung: <span id="eem-grid-calculated-total" class="text-[#0071BC]">0.00</span></span>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-2xl max-h-[50vh]">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2.5 text-center w-12">No</th>
                                    <th class="px-3 py-2.5 w-24">Tipe</th>
                                    <th class="px-4 py-2.5">Pertanyaan</th>
                                    <th class="px-3 py-2.5 w-44">Jawaban Siswa</th>
                                    <th class="px-3 py-2.5 text-center w-40">Nilai / Bobot</th>
                                </tr>
                            </thead>
                            <tbody id="eem-grid-table-body" class="divide-y divide-slate-100 bg-white">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: PRESETS -->
                <div id="eem-pane-preset" class="hidden space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 border-2 border-emerald-200 rounded-2xl bg-emerald-50/50 space-y-2.5">
                            <div class="flex items-center gap-2 font-bold text-emerald-800 text-sm">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                                <span>Semua Jawaban Benar (100%)</span>
                            </div>
                            <p class="text-xs text-emerald-950 leading-relaxed">
                                Memberikan jawaban kunci yang benar pada semua butir soal dan memberikan nilai penuh pada setiap butir soal.
                            </p>
                            <button type="button" onclick="applyExamPreset('preset_all_correct')" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition active:scale-95 cursor-pointer shadow-xs">
                                Terapkan Semua Benar (100%)
                            </button>
                        </div>

                        <div class="p-4 border-2 border-red-200 rounded-2xl bg-red-50/50 space-y-2.5">
                            <div class="flex items-center gap-2 font-bold text-red-800 text-sm">
                                <i class="fa-solid fa-circle-xmark text-red-600 text-lg"></i>
                                <span>Semua Jawaban Salah (Skor 0)</span>
                            </div>
                            <p class="text-xs text-red-950 leading-relaxed">
                                Mengatur skor setiap butir soal menjadi 0 sehingga total nilai ujian siswa bernilai 0.
                            </p>
                            <button type="button" onclick="applyExamPreset('preset_all_wrong')" class="w-full py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition active:scale-95 cursor-pointer shadow-xs">
                                Terapkan Semua Salah (Skor 0)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2-Step Warning Notice Banner -->
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-2.5 text-xs text-amber-900 leading-relaxed">
                    <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong>Pengamanan 2 Langkah:</strong> Klik tombol simpan akan membuka peringatan langkah 1 dan konfirmasi kata sandi verifikasi langkah 2 sebelum perubahan disimpan ke database.
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 sm:p-5 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
                <button type="button" onclick="document.getElementById('modal-edit-exam-grades').close()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btn-save-exam-edit" class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0071BC] hover:bg-sky-700 active:scale-95 text-white font-bold text-xs shadow-md transition">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan Ujian (2-Step Warning)</span>
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- CLIENT JAVASCRIPT LOGIC -->
    <script>
        let currentStatusFilter = 'all';
        let currentPage = 1;
        let searchTimeout = null;

        // ==========================================
        // MAIN TAB NAVIGATION (ATTEMPTS vs ASSESSMENTS)
        // ==========================================
        let currentMainTab = 'attempts';
        let assessmentsLoaded = false;

        function switchMainTab(tab) {
            currentMainTab = tab;
            if (tab === 'attempts') {
                $('#view-attempts').removeClass('hidden');
                $('#view-assessments').addClass('hidden');
                $('#tab-nav-attempts')
                    .removeClass('bg-white text-slate-700 border hover:bg-slate-100')
                    .addClass('bg-[#0071BC] text-white shadow-xs');
                $('#tab-nav-assessments')
                    .removeClass('bg-[#0071BC] text-white shadow-xs')
                    .addClass('bg-white text-slate-700 border hover:bg-slate-100');
            } else {
                $('#view-attempts').addClass('hidden');
                $('#view-assessments').removeClass('hidden');
                $('#tab-nav-assessments')
                    .removeClass('bg-white text-slate-700 border hover:bg-slate-100')
                    .addClass('bg-[#0071BC] text-white shadow-xs');
                $('#tab-nav-attempts')
                    .removeClass('bg-[#0071BC] text-white shadow-xs')
                    .addClass('bg-white text-slate-700 border hover:bg-slate-100');

                if (!assessmentsLoaded) {
                    loadAssessments(1);
                }
            }
        }

        function escapeHtml(text) {
            if (!text) return '-';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getLocalDateString(d = new Date()) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function loadAttempts(page = 1) {
            currentPage = page;
            const search = $('#filter-search').val();
            const schoolId = $('#filter-school').val();
            const dateStart = $('#filter-date-start').val();
            const dateEnd = $('#filter-date-end').val();
            const sortBy = $('#filter-sort-by').val() || 'latest';
            const cheatingFilter = $('#filter-cheating-only').val() || 'all';
            const assessmentId = $('#filter-assessment').val() || 'all';
            const perPage = $('#filter-per-page').val() || '20';

            const hasDate = Boolean(dateStart);
            if (hasDate) {
                $('#btn-clear-date').removeClass('hidden');
                $('#date-filter-badge').removeClass('hidden');
            } else {
                $('#btn-clear-date').addClass('hidden');
                $('#date-filter-badge').addClass('hidden');
            }

            $('#attempts-table-body').html(`
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                        <div>Memuat data attempt ujian...</div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ url('/administrator/assessment-debug/data') }}",
                type: 'GET',
                data: {
                    page: page,
                    status: currentStatusFilter,
                    school_id: schoolId,
                    search: search,
                    date_start: dateStart,
                    date_end: dateEnd,
                    sort_by: sortBy,
                    cheating_filter: cheatingFilter,
                    assessment_id: assessmentId,
                    per_page: perPage
                },
                success: function (res) {
                    // Update KPI counters
                    if (res.summary) {
                        $('#stat-total').text(res.summary.total);
                        $('#badge-total-attempts').text(res.summary.total);
                        $('#stat-cheating').text(res.summary.cheating);
                        $('#stat-in-progress').text(res.summary.in_progress);
                        $('#stat-submitted').text(res.summary.submitted);
                        $('#stat-timeout').text(res.summary.timeout);
                        if (res.summary.total_assessments !== undefined) {
                            $('#badge-total-assessments').text(res.summary.total_assessments);
                        }
                    }

                    renderAttemptsTable(res.data, res.pagination);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || (xhr.status === 403 
                        ? 'Akses ditolak. Pastikan Anda memiliki akses administrator.' 
                        : 'Gagal memuat data (' + (xhr.statusText || 'Error ' + xhr.status) + ').');
                    $('#attempts-table-body').html(`
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-red-500">
                                <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                                <div class="font-medium">${escapeHtml(message)}</div>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function renderAttemptsTable(items, pagination) {
            const tbody = $('#attempts-table-body');
            tbody.empty();

            if (!items || items.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-3 text-slate-300"></i>
                            <div class="font-semibold text-slate-600">Tidak ada sesi ujian yang ditemukan</div>
                            <div class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian, filter tanggal, atau filter sekolah.</div>
                        </td>
                    </tr>
                `);
                $('#table-info').text('0 data');
                $('#pagination-container').addClass('hidden');
                updateBatchActionBar();
                return;
            }

            $('#pagination-container').removeClass('hidden');
            if (pagination.is_all) {
                $('#table-info').html(`<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-900 border border-amber-300 font-bold"><i class="fa-solid fa-bolt text-amber-500"></i>Throw All: Menampilkan seluruh ${pagination.total} sesi</span>`);
            } else {
                $('#table-info').text(`Menampilkan ${items.length} dari ${pagination.total} sesi`);
            }

            const now = new Date();

            items.forEach(item => {
                const student = item.user_account?.student_profile;
                const studentName = student?.nama_lengkap || 'Tanpa Nama';
                const nis = student?.nis || student?.nisn || '-';
                const schoolName = student?.school_partner?.nama_sekolah || item.school_assessment?.school_partner?.nama_sekolah || '-';
                const className = item.user_account?.student_school_class?.[0]?.school_class?.class_name || item.school_assessment?.school_class?.class_name || '-';

                const assessmentTitle = item.school_assessment?.title || item.school_assessment?.school_assessment_type?.name || 'Assessment';
                const mapel = item.school_assessment?.mapel?.mata_pelajaran || '-';
                const duration = item.school_assessment?.duration || 0;

                // Status Badge
                let statusBadge = '';
                const st = String(item.status).toLowerCase();
                if (st === 'cheating') {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                        <i class="fa-solid fa-lock text-[10px]"></i> Cheating
                    </span>`;
                } else if (st === 'in_progress') {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                        <i class="fa-solid fa-spinner fa-spin text-[10px]"></i> In Progress
                    </span>`;
                } else if (st === 'submitted') {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-check text-[10px]"></i> Submitted
                    </span>`;
                } else if (st === 'timeout') {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                        <i class="fa-solid fa-clock text-[10px]"></i> Timeout
                    </span>`;
                } else {
                    statusBadge = `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">${escapeHtml(item.status)}</span>`;
                }

                // Expiration & Time Remaining
                let timeInfo = '';
                if (item.expire_time) {
                    const exp = new Date(item.expire_time);
                    const diffMs = exp - now;
                    const diffMin = Math.round(diffMs / 60000);

                    if (diffMs > 0) {
                        timeInfo = `<div class="text-xs font-bold text-emerald-600"><i class="fa-regular fa-clock mr-1"></i>Sisa ${diffMin} menit</div>`;
                    } else {
                        timeInfo = `<div class="text-xs font-bold text-red-500"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Kadaluarsa (${Math.abs(diffMin)}m lalu)</div>`;
                    }
                }

                // Tab switch badge
                const switchCount = parseInt(item.tab_switch_count) || 0;
                let switchBadgeClass = switchCount >= 5 ? 'bg-red-50 text-red-700 border-red-200 font-bold' : (switchCount > 0 ? 'bg-amber-50 text-amber-700 border-amber-200 font-semibold' : 'bg-slate-50 text-slate-600 border-slate-200');

                // Quick Unlock button only for blocked/cheating/timeout
                const isLocked = (st === 'cheating' || st === 'timeout');

                // Score & Answer Stats
                const scoreVal = (item.current_score !== undefined && item.current_score !== null) ? Number(item.current_score).toFixed(2) : '0.00';
                const ansCount = item.answered_count || 0;
                const totAns = item.total_answers || 0;

                const isChecked = selectedAttemptIds.has(Number(item.id));

                const row = `
                    <tr class="hover:bg-slate-50/80 transition-colors ${isChecked ? 'bg-amber-50/40' : ''}">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" class="attempt-checkbox w-4 h-4 rounded text-[#0071BC] focus:ring-sky-200 border-slate-300 cursor-pointer" value="${item.id}" ${isChecked ? 'checked' : ''}>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">${escapeHtml(studentName)}</div>
                            <div class="text-xs text-slate-500 mt-0.5">NISN: ${escapeHtml(nis)} • Kelas: <span class="font-semibold text-slate-700">${escapeHtml(className)}</span></div>
                            <div class="text-[11px] text-slate-400">${escapeHtml(schoolName)}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">${escapeHtml(assessmentTitle)}</div>
                            <div class="text-xs text-[#0071BC] font-medium">${escapeHtml(mapel)}</div>
                            <div class="text-[11px] text-slate-400">Durasi: ${duration} Menit</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            ${statusBadge}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs border ${switchBadgeClass}">
                                <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>
                                ${switchCount} kali
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 font-extrabold text-slate-800 text-sm">
                                <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                                <span>${scoreVal}</span>
                            </div>
                            <div class="flex items-center justify-center gap-1 text-[11px] font-bold mt-1.5">
                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200" title="Benar">${item.correct_count ?? 0}B</span>
                                <span class="px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-200" title="Salah">${item.wrong_count ?? 0}S</span>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200" title="Kosong">${item.empty_count ?? 0}K</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            ${timeInfo}
                            <div class="text-[11px] text-slate-400 mt-0.5">Mulai: ${formatDateTime(item.start_time)}</div>
                            <div class="text-[11px] text-slate-400">Batas: ${formatDateTime(item.expire_time)}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openStudentExamDetail(${item.school_assessment_id}, ${item.student_id}, '${escapeHtml(studentName)}')"
                                    class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-sky-50 hover:bg-[#0071BC] text-[#0071BC] hover:text-white font-semibold text-xs transition active:scale-95 border border-sky-200" title="Lihat Lembar Jawaban Siswa">
                                    <i class="fa-solid fa-file-lines text-xs"></i>
                                    <span>Detail</span>
                                </button>

                                ${isLocked ? `
                                    <button type="button" onclick="quickUnlock(${item.id}, '${escapeHtml(studentName)}', ${duration})"
                                        class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition active:scale-95" title="Buka Kunci Ujian">
                                        <i class="fa-solid fa-lock-open text-xs"></i>
                                        <span>Buka Kunci</span>
                                    </button>
                                ` : ''}

                                <button type="button" onclick="recountSingleAttempt(${item.id}, '${escapeHtml(studentName)}', '${escapeHtml(assessmentTitle)}')"
                                    class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-slate-900 font-semibold text-xs transition active:scale-95 border border-amber-200" title="Hitung Ulang Nilai Siswa">
                                    <i class="fa-solid fa-calculator text-xs"></i>
                                    <span>Recount</span>
                                </button>

                                <button type="button" onclick='openEditModal(${JSON.stringify(item)})'
                                    class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-[#0071BC] hover:text-white text-slate-700 font-semibold text-xs transition active:scale-95" title="Edit Status & Pelanggaran">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    <span>Edit</span>
                                </button>

                                <button type="button" onclick="deleteAttemptConfirm(${item.id}, '${escapeHtml(studentName)}')"
                                    class="cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-50 hover:bg-red-500 hover:text-white text-red-600 text-xs transition active:scale-95" title="Hapus Attempt (Reset Ulang)">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            updateBatchActionBar();
            renderPagination(pagination);
        }

        function renderPagination(pagination) {
            const btnContainer = $('#pagination-buttons');
            const textContainer = $('#pagination-text');
            btnContainer.empty();

            if (!pagination || pagination.total === 0) {
                textContainer.text('0 data');
                return;
            }

            if (pagination.is_all || pagination.last_page <= 1) {
                textContainer.html(`Menampilkan seluruh <strong class="text-slate-800">${pagination.total}</strong> sesi ujian`);
                btnContainer.html(`
                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-emerald-600"></i>Semua Data Ditampilkan
                    </span>
                `);
                return;
            }

            textContainer.html(`Halaman <strong class="text-slate-800">${pagination.current_page}</strong> dari <strong class="text-slate-800">${pagination.last_page}</strong> (Total ${pagination.total} data)`);

            const prevDisabled = pagination.current_page <= 1 ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer hover:bg-slate-200';
            btnContainer.append(`
                <button type="button" onclick="loadAttempts(${pagination.current_page - 1})" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 font-semibold text-slate-700 ${prevDisabled}">
                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                </button>
            `);

            // Page numbers
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const active = i === pagination.current_page ? 'bg-[#0071BC] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold cursor-pointer';
                btnContainer.append(`
                    <button type="button" onclick="loadAttempts(${i})" class="px-3 py-1.5 text-xs rounded-lg transition ${active}">
                        ${i}
                    </button>
                `);
            }

            const nextDisabled = pagination.current_page >= pagination.last_page ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer hover:bg-slate-200';
            btnContainer.append(`
                <button type="button" onclick="loadAttempts(${pagination.current_page + 1})" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 font-semibold text-slate-700 ${nextDisabled}">
                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                </button>
            `);
        }

        // Recount Single Attempt Action
        function recountSingleAttempt(attemptId, studentName, assessmentTitle) {
            Swal.fire({
                title: 'Hitung Ulang Nilai Siswa?',
                html: `Sistem akan mengevaluasi ulang seluruh butir jawaban untuk:<br>
                       <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-left text-xs space-y-1">
                           <div>• Siswa: <strong class="text-slate-800">${escapeHtml(studentName)}</strong></div>
                           <div>• Ujian: <strong class="text-slate-800">${escapeHtml(assessmentTitle)}</strong></div>
                           <div>• Algoritma: <strong>Kunci & Bobot Terbaru (Soal ESSAY dinilai manual guru & tidak diubah)</strong></div>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0071BC',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="fa-solid fa-calculator mr-1.5"></i> Ya, Recount Nilai',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Menghitung Ulang...',
                        text: 'Mengevaluasi butir jawaban dan menyinkronkan rekap nilai...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/administrator/assessment-debug/${attemptId}/recount`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (resp) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Recount Selesai!',
                                html: `<div class="text-sm text-slate-700">${escapeHtml(resp.message)}</div>
                                       <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 font-semibold">
                                           Skor Sebelum: <span class="line-through text-red-500">${resp.data.old_total_score}</span> &rarr;
                                           Skor Baru: <span class="text-emerald-600 font-extrabold text-sm">${resp.data.new_total_score}</span>
                                       </div>`,
                                timer: 3500
                            });
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Recount',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghitung ulang nilai.'
                            });
                        }
                    });
                }
            });
        }

        // Quick Unlock Action
        function quickUnlock(id, studentName, defaultDuration) {
            Swal.fire({
                title: 'Buka Kunci Ujian?',
                html: `Siswa <strong>${studentName}</strong> akan dipulihkan:<br>
                       <ul class="text-left text-xs bg-slate-50 p-3 rounded-xl border mt-2 space-y-1">
                           <li>• Status diubah menjadi: <strong class="text-blue-600">in_progress</strong></li>
                           <li>• Pelanggaran tab direset menjadi: <strong>0</strong></li>
                           <li>• Waktu ujian diperpanjang: <strong>${defaultDuration || 60} menit</strong> dari sekarang</li>
                           <li>• Soal kosong hasil auto-submit dibersihkan agar siswa dapat langsung melanjutkan ujian</li>
                       </ul>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="fa-solid fa-lock-open mr-1"></i> Ya, Buka Kunci',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/administrator/assessment-debug/${id}/unlock`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            extra_minutes: defaultDuration || 60
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dibuka!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat membuka kunci.', 'error');
                        }
                    });
                }
            });
        }

        // Open Edit Modal
        let currentEditModalItem = null;
        function openEditModal(item) {
            currentEditModalItem = item;
            const student = item.user_account?.student_profile;
            $('#edit-attempt-id').val(item.id);
            $('#modal-student-name').text(student?.nama_lengkap || 'Siswa');
            $('#modal-assessment-name').text(item.school_assessment?.school_assessment_type?.name || 'Assessment');
            $('#modal-school-name').text(student?.school_partner?.nama_sekolah || '-');

            $('#edit-status').val(item.status);
            $('#edit-tab-switch').val(item.tab_switch_count || 0);
            $('#edit-add-minutes').val(0);
            $('#edit-reset-to-draft').prop('checked', false);

            document.getElementById('modal-edit-attempt').showModal();
        }

        function getActiveTopDialog() {
            const dialogIds = [
                'modal-edit-question-grade',
                'modal-edit-exam-grades',
                'modal-student-exam-detail',
                'modal-assessment-detail',
                'modal-cache-manager',
                'modal-edit-attempt'
            ];
            for (const id of dialogIds) {
                const el = document.getElementById(id);
                if (el && el.open) {
                    return el;
                }
            }
            const openDialogs = document.querySelectorAll('dialog[open]');
            if (openDialogs.length > 0) {
                return openDialogs[openDialogs.length - 1];
            }
            return document.body;
        }

        function swalInModal(options, modalId = null) {
            let modalEl = modalId ? document.getElementById(modalId) : null;
            if (!modalEl || !modalEl.open) {
                modalEl = getActiveTopDialog();
            }
            if (typeof options === 'string') {
                const title = arguments[0];
                const text = arguments[1] || '';
                const icon = arguments[2] || 'info';
                options = { title, text, icon };
            }
            const defaults = {
                target: modalEl || document.body,
                customClass: {
                    container: 'swal2-top-modal-container'
                }
            };
            return Swal.fire(Object.assign({}, defaults, options));
        }

        // Trigger manual answer reset
        function triggerResetAnswers(type) {
            const id = $('#edit-attempt-id').val();
            if (!id) return;

            const isDeleteAll = type === 'delete_all';
            const label = isDeleteAll 
                ? 'Hapus SEMUA jawaban siswa untuk ujian ini? Siswa harus mengerjakan ulang dari nomor 1.' 
                : 'Bersihkan semua jawaban kosong / auto-submit? Soal-soal yang belum diisi akan dapat dikerjakan kembali oleh siswa.';

            swalInModal({
                title: isDeleteAll ? 'Hapus Seluruh Jawaban?' : 'Bersihkan Soal Kosong?',
                text: label,
                icon: isDeleteAll ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: isDeleteAll ? 'Ya, Hapus Semua' : 'Ya, Bersihkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: isDeleteAll ? '#EF4444' : '#0071BC'
            }, 'modal-edit-attempt').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/administrator/assessment-debug/${id}/reset-answers`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            reset_type: type
                        },
                        success: function (res) {
                            swalInModal({ icon: 'success', title: 'Berhasil', text: res.message }, 'modal-edit-attempt');
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            swalInModal({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Gagal memproses jawaban.' }, 'modal-edit-attempt');
                        }
                    });
                }
            });
        }

        // Submit Edit Attempt
        $('#form-edit-attempt').on('submit', function (e) {
            e.preventDefault();
            const id = $('#edit-attempt-id').val();
            const status = $('#edit-status').val();
            const tabSwitch = $('#edit-tab-switch').val();
            const addMinutes = $('#edit-add-minutes').val();
            const resetToDraft = $('#edit-reset-to-draft').is(':checked') ? 1 : 0;

            const $btn = $('#btn-save-edit-attempt');
            $btn.prop('disabled', true).addClass('opacity-50');

            $.ajax({
                url: `/administrator/assessment-debug/${id}/update`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    status: status,
                    tab_switch_count: tabSwitch,
                    add_minutes: addMinutes,
                    reset_to_draft: resetToDraft
                },
                success: function (res) {
                    document.getElementById('modal-edit-attempt').close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadAttempts(currentPage);
                },
                error: function (xhr) {
                    Swal.fire('Gagal Menyimpan', xhr.responseJSON?.message || 'Terjadi kesalahan validasi.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).removeClass('opacity-50');
                }
            });
        });

        // Delete / Reset Attempt
        function deleteAttemptConfirm(id, studentName) {
            Swal.fire({
                title: 'Hapus Sesi Ujian?',
                html: `Sesi attempt untuk <strong>${studentName}</strong> akan dihapus permanen.<br>Siswa dapat memulai ujian baru seperti belum pernah membuka halaman ujian.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus Permanen',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/administrator/assessment-debug/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        }

        // ==========================================
        // CHECKBOX SELECTION & BATCH RECOUNT LOGIC
        // ==========================================
        let selectedAttemptIds = new Set();

        function updateBatchActionBar() {
            const count = selectedAttemptIds.size;
            $('#selected-attempts-count').text(count);
            if (count > 0) {
                $('#batch-action-bar').removeClass('hidden').addClass('flex');
            } else {
                $('#batch-action-bar').addClass('hidden').removeClass('flex');
            }

            const currentCheckboxes = $('.attempt-checkbox');
            if (currentCheckboxes.length > 0) {
                const checkedOnPage = currentCheckboxes.filter(':checked').length;
                const checkAll = $('#check-all-attempts');
                if (checkedOnPage === 0) {
                    checkAll.prop('checked', false);
                    checkAll.prop('indeterminate', false);
                } else if (checkedOnPage === currentCheckboxes.length) {
                    checkAll.prop('checked', true);
                    checkAll.prop('indeterminate', false);
                } else {
                    checkAll.prop('checked', false);
                    checkAll.prop('indeterminate', true);
                }
            }
        }

        $(document).on('change', '#check-all-attempts', function () {
            const isChecked = $(this).is(':checked');
            $('.attempt-checkbox').each(function () {
                const id = parseInt($(this).val(), 10);
                $(this).prop('checked', isChecked);
                const $tr = $(this).closest('tr');
                if (isChecked) {
                    selectedAttemptIds.add(id);
                    $tr.addClass('bg-amber-50/40');
                } else {
                    selectedAttemptIds.delete(id);
                    $tr.removeClass('bg-amber-50/40');
                }
            });
            updateBatchActionBar();
        });

        $(document).on('change', '.attempt-checkbox', function () {
            const id = parseInt($(this).val(), 10);
            const $tr = $(this).closest('tr');
            if ($(this).is(':checked')) {
                selectedAttemptIds.add(id);
                $tr.addClass('bg-amber-50/40');
            } else {
                selectedAttemptIds.delete(id);
                $tr.removeClass('bg-amber-50/40');
            }
            updateBatchActionBar();
        });

        $(document).on('click', '#btn-clear-selected-attempts', function () {
            selectedAttemptIds.clear();
            $('.attempt-checkbox').prop('checked', false);
            $('.attempt-checkbox').closest('tr').removeClass('bg-amber-50/40');
            $('#check-all-attempts').prop('checked', false).prop('indeterminate', false);
            updateBatchActionBar();
        });

        $(document).on('click', '#btn-recount-selected', function () {
            const ids = Array.from(selectedAttemptIds);
            if (ids.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada Sesi Dipilih',
                    text: 'Silakan centang minimal satu sesi ujian untuk dihitung ulang.'
                });
                return;
            }

            Swal.fire({
                title: `Recount ${ids.length} Sesi Terpilih?`,
                html: `Sistem akan mengevaluasi dan menghitung ulang skor untuk <strong>${ids.length} sesi ujian</strong> yang dicentang.<br><span class="text-xs text-slate-500 mt-1 inline-block"><i class="fa-solid fa-circle-info mr-1 text-amber-600"></i>Soal tipe ESSAY tidak akan di-recount karena dinilai manual oleh guru.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="fa-solid fa-calculator mr-1.5"></i>Ya, Recount Terpilih',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghitung Ulang Sesi Terpilih...',
                        text: 'Sedang mengevaluasi butir soal dan merekap skor ke database...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: "{{ url('/administrator/assessment-debug/recount-selected') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            attempt_ids: ids
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Recount Berhasil!',
                                text: res.message || 'Sesi ujian berhasil dihitung ulang.',
                                timer: 3000,
                                showConfirmButton: true
                            });

                            selectedAttemptIds.clear();
                            updateBatchActionBar();
                            loadAttempts(currentPage);
                            if (typeof assessmentsLoaded !== 'undefined' && assessmentsLoaded) {
                                loadAssessments(currentAsmPage);
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Recount',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses recount batch.'
                            });
                        }
                    });
                }
            });
        });

        // Filter Actions
        $('.status-filter-btn').on('click', function () {
            $('.status-filter-btn').removeClass('bg-white text-[#0071BC] font-bold shadow-xs').addClass('text-slate-600 font-semibold');
            $(this).addClass('bg-white text-[#0071BC] font-bold shadow-xs').removeClass('text-slate-600');
            currentStatusFilter = $(this).data('status');
            loadAttempts(1);
        });

        $('#filter-school').on('change', function () {
            const selectedSchool = $(this).val();
            // Filter options in assessment dropdown to match school
            $('#filter-assessment option').each(function () {
                const optSchool = $(this).data('school');
                if (!optSchool || selectedSchool === 'all' || String(optSchool) === String(selectedSchool)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#filter-assessment').val('all');
            loadAttempts(1);
        });

        // Date Filter Events - If date filter is chosen, automatically set per-page to 'all' (throw all data)
        $('#filter-date-start, #filter-date-end').on('change', function () {
            const dStart = $('#filter-date-start').val();
            if (dStart) {
                $('#filter-per-page').val('all');
            }
            loadAttempts(1);
        });

        $('#btn-quick-today').on('click', function () {
            const today = getLocalDateString(new Date());
            $('#filter-date-start').val(today);
            $('#filter-date-end').val(today);
            $('#filter-per-page').val('all');
            loadAttempts(1);
        });

        $('#btn-quick-yesterday').on('click', function () {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const yStr = getLocalDateString(yesterday);
            $('#filter-date-start').val(yStr);
            $('#filter-date-end').val(yStr);
            $('#filter-per-page').val('all');
            loadAttempts(1);
        });

        $('#btn-quick-7days').on('click', function () {
            const past = new Date();
            past.setDate(past.getDate() - 6);
            $('#filter-date-start').val(getLocalDateString(past));
            $('#filter-date-end').val(getLocalDateString(new Date()));
            $('#filter-per-page').val('all');
            loadAttempts(1);
        });

        $('#btn-clear-date').on('click', function () {
            $('#filter-date-start').val('');
            $('#filter-date-end').val('');
            $('#filter-per-page').val('20');
            loadAttempts(1);
        });

        $('#filter-assessment, #filter-cheating-only, #filter-sort-by, #filter-per-page').on('change', function () {
            loadAttempts(1);
        });

        $('#filter-search').on('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadAttempts(1);
            }, 350);
        });

        $('#btn-refresh-attempts').on('click', function () {
            const icon = $('#icon-refresh');
            icon.addClass('fa-spin');
            loadAttempts(currentPage);
            setTimeout(() => {
                icon.removeClass('fa-spin');
            }, 700);
        });

        // =========================================================
        // RAM CACHE MANAGER CLIENT LOGIC
        // =========================================================
        let cacheSearchTimeout = null;
        let currentCacheItems = [];

        function openCacheManagerModal() {
            const modal = document.getElementById('modal-cache-manager');
            if (!modal) return;
            if (typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                $(modal).addClass('modal-open');
            }
            loadCacheStatus();
        }

        $('#btn-open-cache-manager').on('click', function (e) {
            e.preventDefault();
            openCacheManagerModal();
        });

        $('#btn-refresh-cache-status').on('click', function () {
            const icon = $('#icon-refresh-cache');
            icon.addClass('fa-spin');
            loadCacheStatus();
            setTimeout(() => {
                icon.removeClass('fa-spin');
            }, 700);
        });

        $('#cache-search').on('input', function () {
            clearTimeout(cacheSearchTimeout);
            cacheSearchTimeout = setTimeout(() => {
                loadCacheStatus();
            }, 300);
        });

        function loadCacheStatus() {
            const search = $('#cache-search').val();
            const tbody = $('#cache-table-body');

            tbody.html(`
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-xl mb-1.5 text-emerald-600"></i>
                        <div>Memeriksa status cache RAM...</div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ url('/administrator/assessment-debug/cache/status') }}",
                type: 'GET',
                data: {
                    search: search
                },
                success: function (res) {
                    if (res.summary) {
                        $('#cache-stat-total').text(res.summary.total);
                        $('#cache-stat-cached').text(res.summary.cached);
                        $('#cache-stat-uncached').text(res.summary.uncached);
                        $('#cache-stat-driver').text(res.summary.cache_driver);
                    }
                    currentCacheItems = res.data || [];
                    renderCacheTable(currentCacheItems);
                },
                error: function (xhr) {
                    const errMsg = xhr.responseJSON?.message || 'Gagal memuat status cache RAM. Pastikan Anda memiliki akses administrator.';
                    tbody.html(`
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-red-500">
                                <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                                <div class="font-bold">${escapeHtml(errMsg)}</div>
                                <div class="text-xs text-slate-400 mt-1">Status HTTP: ${xhr.status}</div>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function renderCacheTable(items) {
            const tbody = $('#cache-table-body');
            tbody.empty();

            if (!items || items.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                            <div class="font-semibold text-slate-600">Tidak ada assessment ditemukan</div>
                            <div class="text-xs text-slate-400 mt-1">Pastikan ada ujian yang telah memiliki butir soal.</div>
                        </td>
                    </tr>
                `);
                return;
            }

            items.forEach(item => {
                // Exam Schedule Badge
                let scheduleBadge = '';
                if (item.status_exam === 'active') {
                    scheduleBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle text-[6px]"></i> Aktif</span>`;
                } else if (item.status_exam === 'upcoming') {
                    scheduleBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200"><i class="fa-solid fa-clock text-[9px]"></i> Akan Datang</span>`;
                } else {
                    scheduleBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Selesai</span>`;
                }

                // Cache Status Badge
                let cacheBadge = '';
                if (item.is_cached) {
                    cacheBadge = `
                        <div class="flex flex-col items-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-2xs">
                                <i class="fa-solid fa-circle-check text-[11px]"></i> Tersimpan (${item.cached_questions_count ?? item.total_questions} soal)
                            </span>
                            <span class="text-[10px] font-mono text-slate-400 mt-1 truncate max-w-[190px]" title="${escapeHtml(item.cache_key)}">
                                ${escapeHtml(item.cache_key)}
                            </span>
                        </div>
                    `;
                } else {
                    cacheBadge = `
                        <div class="flex flex-col items-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fa-solid fa-hourglass-start text-[10px]"></i> Belum di-Cache
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1">Dimuat otomatis pada request ke-1</span>
                        </div>
                    `;
                }

                // Actions
                const warmBtn = `
                    <button type="button" class="btn-warm-cache-item cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 font-bold text-xs transition active:scale-95 border border-emerald-200" data-id="${item.id}" title="Muat / Segarkan ke RAM">
                        <i class="fa-solid fa-fire text-xs"></i>
                        <span>Pre-load</span>
                    </button>
                `;

                const clearBtn = item.is_cached ? `
                    <button type="button" class="btn-clear-cache-item cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-600 hover:text-white text-red-600 font-bold text-xs transition active:scale-95 border border-red-200" data-id="${item.id}" title="Hapus RAM Cache (Misal setelah edit soal)">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Hapus</span>
                    </button>
                ` : '';

                const row = `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-800 leading-snug">${escapeHtml(item.title)}</div>
                            <div class="text-[11px] text-[#0071BC] font-medium mt-0.5">${escapeHtml(item.mapel)} • <span class="text-slate-500 font-normal">${item.total_questions} butir soal</span></div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-700">${escapeHtml(item.class_name)}</div>
                            <div class="text-[11px] text-slate-400">${escapeHtml(item.school_name)}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="mb-1">${scheduleBadge}</div>
                            <div class="text-[11px] text-slate-500">${escapeHtml(item.start_date)} s/d ${escapeHtml(item.end_date)}</div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            ${cacheBadge}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                ${warmBtn}
                                ${clearBtn}
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Delegate item actions
        $(document).on('click', '.btn-warm-cache-item', function () {
            const id = $(this).data('id');
            const item = currentCacheItems.find(x => String(x.id) === String(id));
            const title = item ? item.title : `Assessment #${id}`;
            warmAssessmentCache(id, title);
        });

        $(document).on('click', '.btn-clear-cache-item', function () {
            const id = $(this).data('id');
            const item = currentCacheItems.find(x => String(x.id) === String(id));
            const title = item ? item.title : `Assessment #${id}`;
            clearAssessmentCache(id, title);
        });

        // Warm single cache
        function warmAssessmentCache(id, title) {
            swalInModal({
                title: 'Pre-load ke RAM Cache?',
                html: `Memuat seluruh butir soal ujian <strong>${escapeHtml(title)}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="fa-solid fa-fire mr-1"></i> Ya, Muat ke RAM',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    swalInModal({
                        title: 'Memuat Soal ke RAM...',
                        text: 'Harap tunggu...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: `/administrator/assessment-debug/cache/warm/${id}`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            swalInModal({
                                icon: 'success',
                                title: 'Berhasil di-Cache!',
                                text: res.message,
                                timer: 2500,
                                showConfirmButton: false
                            });
                            loadCacheStatus();
                        },
                        error: function (xhr) {
                            swalInModal({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat cache.'
                            });
                        }
                    });
                }
            });
        }

        // Clear single cache
        function clearAssessmentCache(id, title) {
            swalInModal({
                title: 'Hapus RAM Cache Soal?',
                html: `Menghapus cache soal untuk ujian <strong>${escapeHtml(title)}</strong> dari RAM.<br><br>Gunakan opsi ini jika Anda atau guru baru saja memperbarui/mengedit soal di database, agar perubahan segera tercermin pada siswa.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Bersihkan Cache',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.ajax({
                        url: `/administrator/assessment-debug/cache/clear/${id}`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            swalInModal({
                                icon: 'success',
                                title: 'Cache Dibersihkan!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadCacheStatus();
                        },
                        error: function (xhr) {
                            swalInModal({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Gagal menghapus cache.'
                            });
                        }
                    });
                }
            });
        }

        // Warm all active assessments
        $('#btn-warm-all-cache').on('click', function () {
            swalInModal({
                title: 'Pre-load Semua Ujian Aktif?',
                html: `Sistem akan memuat seluruh soal dari ujian yang sedang berlangsung atau aktif ke dalam RAM cache server.<br><br>Ini berguna sebelum jam ujian serentak dimulai.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="fa-solid fa-fire mr-1"></i> Pre-load Semua',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    swalInModal({
                        title: 'Memproses Pre-load RAM...',
                        text: 'Sedang membaca seluruh soal dari database ke RAM...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: `/administrator/assessment-debug/cache/warm/all`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            swalInModal({
                                icon: 'success',
                                title: 'Selesai!',
                                text: res.message,
                                timer: 2500,
                                showConfirmButton: false
                            });
                            loadCacheStatus();
                        },
                        error: function (xhr) {
                            swalInModal({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                            });
                        }
                    });
                }
            });
        });

        // Clear all caches
        $('#btn-clear-all-cache').on('click', function () {
            swalInModal({
                title: 'Bersihkan Seluruh RAM Cache Soal?',
                html: `Seluruh cache soal ujian yang tersimpan di RAM akan dikosongkan.<br><br>Siswa yang mengakses ujian berikutnya akan otomatis memuat ulang versi terbaru dari database.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Kosongkan Semua',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.ajax({
                        url: `/administrator/assessment-debug/cache/clear/all`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            swalInModal({
                                icon: 'success',
                                title: 'Cache RAM Bersih!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadCacheStatus();
                        },
                        error: function (xhr) {
                            swalInModal({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                            });
                        }
                    });
                }
            });
        });

        // ==========================================
        // ASSESSMENT VIEWS LOGIC & RECOUNT
        // ==========================================
        let currentAsmCategory = 'all';
        let currentAsmPage = 1;
        let asmSearchTimeout = null;
        let currentDetailAssessmentId = null;

        function loadAssessments(page = 1) {
            currentAsmPage = page;
            assessmentsLoaded = true;
            const search = $('#filter-asm-search').val();
            const schoolId = $('#filter-asm-school').val();
            const status = $('#filter-asm-status').val();
            const dateStart = $('#filter-asm-date-start').val();
            const dateEnd = $('#filter-asm-date-end').val();
            const perPage = $('#filter-asm-per-page').val() || '15';

            const hasDate = Boolean(dateStart);
            if (hasDate) {
                $('#btn-asm-clear-date').removeClass('hidden');
                $('#asm-date-filter-badge').removeClass('hidden');
            } else {
                $('#btn-asm-clear-date').addClass('hidden');
                $('#asm-date-filter-badge').addClass('hidden');
            }

            $('#assessments-table-body').html(`
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                        <div>Memuat data assessment...</div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ url('/administrator/assessment-debug/assessments') }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    page: page,
                    category: currentAsmCategory,
                    status: status,
                    school_id: schoolId,
                    search: search,
                    date_start: dateStart,
                    date_end: dateEnd,
                    per_page: perPage
                },
                success: function (res) {
                    if (res.status !== 'success') {
                        $('#assessments-table-body').html(`
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-amber-500">
                                    <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                                    <div class="font-medium">${escapeHtml(res.message || 'Respons tidak valid dari server.')}</div>
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    if (res.summary) {
                        $('#stat-asm-total').text(res.summary.total_assessments);
                        $('#stat-asm-remedial').text(res.summary.remedial_susulan);
                        $('#stat-asm-attempts').text(res.summary.total_attempts);
                        $('#badge-total-assessments').text(res.summary.total_assessments);
                    }

                    renderAssessmentsTable(res.data, res.pagination);
                },
                error: function (xhr) {
                    let message = 'Gagal memuat data assessment.';
                    if (xhr.status === 404) {
                        message = 'Endpoint /administrator/assessment-debug/assessments belum terdaftar atau masih ter-cache lama di server. Silakan upload file routes/web.php & jalankan "php artisan route:clear" di server.';
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        message = 'Error ' + xhr.status + ': ' + xhr.statusText;
                    }
                    $('#assessments-table-body').html(`
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-red-500">
                                <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                                <div class="font-medium">${escapeHtml(message)}</div>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function renderAssessmentsTable(items, pagination) {
            const tbody = $('#assessments-table-body');
            tbody.empty();

            if (!items || items.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-3 text-slate-300"></i>
                            <div class="font-semibold text-slate-600">Tidak ada assessment yang ditemukan</div>
                            <div class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian, filter tanggal, atau filter sekolah.</div>
                        </td>
                    </tr>
                `);
                $('#asm-table-info').text('0 data');
                $('#asm-pagination-container').addClass('hidden');
                return;
            }

            $('#asm-pagination-container').removeClass('hidden');
            if (pagination.is_all) {
                $('#asm-table-info').html(`<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-900 border border-amber-300 font-bold"><i class="fa-solid fa-bolt text-amber-500"></i>Throw All: Menampilkan seluruh ${pagination.total} assessment</span>`);
            } else {
                $('#asm-table-info').text(`Menampilkan ${items.length} dari ${pagination.total} assessment`);
            }

            let activeCount = 0;

            items.forEach(item => {
                if (item.status_exam === 'active') activeCount++;

                // Status Badge
                let statusBadge = '';
                if (item.status_exam === 'active') {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-circle-dot text-[9px] animate-pulse"></i> Aktif
                    </span>`;
                } else if (item.status_exam === 'upcoming') {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                        <i class="fa-regular fa-clock text-[10px]"></i> Mendatang
                    </span>`;
                } else {
                    statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <i class="fa-solid fa-calendar-xmark text-[10px]"></i> Berakhir
                    </span>`;
                }

                // Category Badge
                let catBadge = '';
                const cat = String(item.category).toLowerCase();
                if (cat === 'remedial') {
                    catBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200 uppercase">Remedial</span>`;
                } else if (cat === 'susulan') {
                    catBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 uppercase">Susulan</span>`;
                } else if (cat === 'pengayaan') {
                    catBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 text-teal-700 border border-teal-200 uppercase">Pengayaan</span>`;
                } else {
                    catBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200 uppercase">Utama</span>`;
                }

                // Cache Badge
                const cacheBadge = item.is_cached
                    ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-semibold" title="Soal tersimpan di RAM Cache"><i class="fa-solid fa-bolt text-[9px] text-amber-500"></i> RAM</span>`
                    : '';

                const row = `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 mb-1">
                                ${catBadge}
                                ${cacheBadge}
                                <span class="text-xs text-slate-400 font-medium">• ${escapeHtml(item.type)}</span>
                            </div>
                            <div class="font-bold text-slate-800 text-sm">${escapeHtml(item.title)}</div>
                            <div class="text-xs text-[#0071BC] font-medium mt-0.5">${escapeHtml(item.mapel)}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 text-xs sm:text-sm">${escapeHtml(item.class_name)}</div>
                            <div class="text-xs text-slate-500 mt-0.5">${escapeHtml(item.school_name)}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="mb-1.5">${statusBadge}</div>
                            <div class="text-[11px] text-slate-600 font-medium">Mulai: ${item.start_date}</div>
                            <div class="text-[11px] text-slate-400">Selesai: ${item.end_date}</div>
                            <div class="text-[11px] text-slate-400">Durasi: ${item.duration} Menit</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-sm font-extrabold text-slate-800">${item.total_questions} Soal</div>
                            <div class="text-xs text-slate-500 mt-0.5">Bobot: <strong class="text-slate-700">${item.total_weight}</strong></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-blue-50 border border-blue-200 font-bold text-blue-900 text-xs">
                                <i class="fa-solid fa-users text-[10px]"></i>
                                <span>${item.participants_count} Peserta</span>
                            </div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-1.5">
                                Rata-rata: <span class="text-emerald-600 font-bold">${item.avg_score}</span>
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Max: ${item.max_score} • Min: ${item.min_score}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openAssessmentDetail(${item.id})"
                                    class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#0071BC] text-slate-700 hover:text-white font-semibold text-xs transition active:scale-95 shadow-2xs" title="Lihat Detail & Daftar Soal">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>Detail</span>
                                </button>
                                <button type="button" onclick="recountAllAssessment(${item.id}, '${escapeHtml(item.title)}')"
                                    class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-slate-900 font-semibold text-xs transition active:scale-95 border border-amber-200 shadow-2xs" title="Hitung Ulang Nilai Seluruh Peserta">
                                    <i class="fa-solid fa-calculator text-xs"></i>
                                    <span>Recount Semua</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            $('#stat-asm-active').text(activeCount);
            renderAsmPagination(pagination);
        }

        function renderAsmPagination(pagination) {
            const btnContainer = $('#asm-pagination-buttons');
            const textContainer = $('#asm-pagination-text');
            btnContainer.empty();

            if (!pagination || pagination.total === 0) {
                textContainer.text('0 data');
                return;
            }

            if (pagination.is_all || pagination.last_page <= 1) {
                textContainer.html(`Menampilkan seluruh <strong class="text-slate-800">${pagination.total}</strong> assessment`);
                btnContainer.html(`
                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-emerald-600"></i>Semua Data Ditampilkan
                    </span>
                `);
                return;
            }

            textContainer.html(`Halaman <strong class="text-slate-800">${pagination.current_page}</strong> dari <strong class="text-slate-800">${pagination.last_page}</strong> (Total ${pagination.total} data)`);

            const prevDisabled = pagination.current_page <= 1 ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer hover:bg-slate-200';
            btnContainer.append(`
                <button type="button" onclick="loadAssessments(${pagination.current_page - 1})" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 font-semibold text-slate-700 ${prevDisabled}">
                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                </button>
            `);

            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const active = i === pagination.current_page ? 'bg-[#0071BC] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold cursor-pointer';
                btnContainer.append(`
                    <button type="button" onclick="loadAssessments(${i})" class="px-3 py-1.5 text-xs rounded-lg transition ${active}">
                        ${i}
                    </button>
                `);
            }

            const nextDisabled = pagination.current_page >= pagination.last_page ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer hover:bg-slate-200';
            btnContainer.append(`
                <button type="button" onclick="loadAssessments(${pagination.current_page + 1})" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 font-semibold text-slate-700 ${nextDisabled}">
                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                </button>
            `);
        }

        // ==========================================
        // ASSESSMENT DETAIL MODAL LOGIC
        // ==========================================
        function switchModalSubTab(subtab) {
            $('.modal-subtab-btn').removeClass('border-[#0071BC] text-[#0071BC]').addClass('border-transparent text-slate-500');
            $('#subtab-btn-' + subtab).removeClass('border-transparent text-slate-500').addClass('border-[#0071BC] text-[#0071BC]');

            $('#subtab-content-participants').addClass('hidden');
            $('#subtab-content-questions').addClass('hidden');
            $('#subtab-content-info').addClass('hidden');

            $('#subtab-content-' + subtab).removeClass('hidden');
        }

        function openAssessmentDetail(assessmentId) {
            currentDetailAssessmentId = assessmentId;
            switchModalSubTab('participants');
            document.getElementById('modal-assessment-detail').showModal();
            fetchAssessmentDetail(assessmentId);
        }

        function fetchAssessmentDetail(assessmentId) {
            $('#modal-participants-table-body').html(`
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-xl mb-1.5 text-[#0071BC]"></i>
                        <div>Memuat data peserta & nilai...</div>
                    </td>
                </tr>
            `);
            $('#modal-questions-list').html(`
                <div class="p-8 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-xl mb-1.5 text-[#0071BC]"></i>
                    <div>Memuat butir soal...</div>
                </div>
            `);

            $.ajax({
                url: `/administrator/assessment-debug/assessment/${assessmentId}`,
                type: 'GET',
                success: function (res) {
                    const data = res.data;
                    const asm = data.assessment;

                    $('#modal-asm-title').text(asm.title);
                    $('#modal-asm-subtitle').text(`${asm.class_name} • ${asm.school_name} • Durasi: ${asm.duration} Menit`);
                    $('#modal-asm-badge-category').text(asm.category.toUpperCase());
                    $('#modal-asm-badge-mapel').text(asm.mapel);

                    // Bind Recount All Button in Modal Header
                    $('#btn-recount-all-in-modal').off('click').on('click', function () {
                        recountAllAssessment(asm.id, asm.title, true);
                    });

                    // Update Subtab counts
                    $('#modal-count-participants').text(data.participants ? data.participants.length : 0);
                    $('#modal-count-questions').text(data.questions ? data.questions.length : 0);

                    // Update Summary Metrics
                    if (data.summary) {
                        $('#modal-stat-participants').text(data.summary.total_participants);
                        $('#modal-stat-avg').text(data.summary.avg_score);
                        $('#modal-stat-max').text(data.summary.highest_score);
                        $('#modal-stat-min').text(data.summary.lowest_score);
                    }

                    // Render Participants Table
                    renderModalParticipants(data.participants, asm.id);

                    // Render Questions List
                    renderModalQuestions(data.questions);

                    // Render Info & Settings
                    renderModalInfo(asm);
                },
                error: function (xhr) {
                    swalInModal({
                        icon: 'error',
                        title: 'Gagal Memuat Detail',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat detail assessment.'
                    });
                }
            });
        }

        function renderModalParticipants(participants, assessmentId) {
            const tbody = $('#modal-participants-table-body');
            tbody.empty();

            if (!participants || participants.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-slate-400">
                            <i class="fa-solid fa-users-slash text-2xl mb-2 text-slate-300"></i>
                            <div>Belum ada peserta yang memulai atau mengerjakan assessment ini.</div>
                        </td>
                    </tr>
                `);
                return;
            }

            participants.forEach(p => {
                let statusBadge = '';
                const st = String(p.status).toLowerCase();
                if (st === 'cheating') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Cheating</span>`;
                } else if (st === 'in_progress') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">In Progress</span>`;
                } else if (st === 'submitted') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Submitted</span>`;
                } else if (st === 'timeout') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Timeout</span>`;
                } else {
                    statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">${escapeHtml(p.status)}</span>`;
                }

                const switchBadgeClass = p.tab_switch_count >= 5 ? 'text-red-600 font-bold' : (p.tab_switch_count > 0 ? 'text-amber-600 font-semibold' : 'text-slate-500');

                const row = `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-bold text-slate-800 text-xs">${escapeHtml(p.name)}</div>
                            <div class="text-[11px] text-slate-400">NISN: ${escapeHtml(p.nis)}</div>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-700">
                            ${escapeHtml(p.class_name)}
                        </td>
                        <td class="px-5 py-3 text-center">
                            ${statusBadge}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="${switchBadgeClass}">${p.tab_switch_count}x</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center min-w-[34px] px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-xs" title="${p.correct_count} Jawaban Benar">
                                <i class="fa-solid fa-check text-[10px] mr-1"></i>${p.correct_count}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center min-w-[34px] px-2 py-0.5 rounded-lg bg-red-50 text-red-600 font-bold border border-red-200 text-xs" title="${p.wrong_count} Jawaban Salah">
                                <i class="fa-solid fa-xmark text-[10px] mr-1"></i>${p.wrong_count}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center min-w-[34px] px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-bold border border-slate-200 text-xs" title="${p.empty_count} Butir Kosong">
                                <i class="fa-solid fa-minus text-[10px] mr-1"></i>${p.empty_count}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 font-extrabold text-slate-800 text-xs">
                                <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                ${Number(p.total_score).toFixed(2)}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openStudentExamDetail(${assessmentId}, ${p.student_id}, '${escapeHtml(p.name)}')"
                                    class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-sky-50 hover:bg-[#0071BC] text-[#0071BC] hover:text-white font-semibold text-xs transition active:scale-95 border border-sky-200 shadow-2xs" title="Lihat Lembar Jawaban Siswa">
                                    <i class="fa-solid fa-file-lines text-xs"></i>
                                    <span>Detail Ujian</span>
                                </button>
                                <button type="button" onclick="recountUserFromDetail(${p.student_id}, ${assessmentId}, '${escapeHtml(p.name)}')"
                                    class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-slate-900 font-semibold text-xs transition active:scale-95 border border-amber-200 shadow-2xs" title="Hitung Ulang Nilai Siswa Ini">
                                    <i class="fa-solid fa-calculator text-[10px]"></i>
                                    <span>Recount</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function renderModalQuestions(questions) {
            const container = $('#modal-questions-list');
            container.empty();

            if (!questions || questions.length === 0) {
                container.html(`
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300"></i>
                        <div>Belum ada butir soal yang dihubungkan ke assessment ini.</div>
                    </div>
                `);
                return;
            }

            questions.forEach(q => {
                let optionsHtml = '';
                if (q.options && q.options.length > 0) {
                    optionsHtml = `<div class="mt-2.5 space-y-1.5 pl-2 border-l-2 border-slate-200">`;
                    const optionLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                    q.options.forEach((opt, optIndex) => {
                        const optKeyStr = String(opt.key).trim();
                        let displayLetter = optionLetters[optIndex] || '';
                        if (/^[A-Za-z]$/.test(optKeyStr)) {
                            displayLetter = optKeyStr.toUpperCase();
                        } else if (/^option_?(\d+)$/i.test(optKeyStr)) {
                            const num = parseInt(optKeyStr.replace(/\D/g, ''), 10);
                            if (num >= 1 && num <= 26) {
                                displayLetter = String.fromCharCode(64 + num);
                            }
                        }
                        if (!displayLetter) {
                            displayLetter = optionLetters[optIndex] || optKeyStr;
                        }

                        const correctClass = opt.is_correct
                            ? 'bg-emerald-50 text-emerald-800 font-bold border-emerald-300'
                            : 'bg-white text-slate-600 border-slate-200';
                        const checkIcon = opt.is_correct
                            ? '<i class="fa-solid fa-circle-check text-emerald-600 mr-1.5"></i>'
                            : '<i class="fa-regular fa-circle text-slate-300 mr-1.5"></i>';

                        let extraDetail = '';
                        if (opt.extra_data) {
                            if (opt.extra_data.pair_with) {
                                extraDetail = ` <span class="text-xs text-blue-600">&rarr; Pasangan: <strong>${escapeHtml(opt.extra_data.pair_with)}</strong></span>`;
                            } else if (opt.extra_data.answer) {
                                extraDetail = ` <span class="text-xs text-blue-600">&rarr; Jawaban: <strong>${escapeHtml(opt.extra_data.answer)}</strong></span>`;
                            }
                        }

                        optionsHtml += `
                            <div class="px-3 py-1.5 rounded-lg border text-xs flex items-center justify-between ${correctClass}">
                                <div>${checkIcon}<strong>${escapeHtml(displayLetter)}:</strong> ${escapeHtml(opt.value)}${extraDetail}</div>
                                ${opt.is_correct ? '<span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-200 text-emerald-800 uppercase font-bold">Kunci Benar</span>' : ''}
                            </div>
                        `;
                    });
                    optionsHtml += `</div>`;
                }

                const itemCard = `
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 font-bold text-slate-700 text-xs flex items-center justify-center">${q.number}</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200">${escapeHtml(q.tipe_soal)}</span>
                                <span class="text-[11px] text-slate-400 font-medium">Difficulty: ${escapeHtml(q.difficulty)}</span>
                            </div>
                            <div class="text-xs font-bold text-slate-700">
                                Bobot: <span class="text-[#0071BC] font-extrabold text-sm">${q.weight}</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-800 leading-relaxed font-medium">
                            ${escapeHtml(q.question_text)}
                        </div>
                        ${optionsHtml}
                    </div>
                `;
                container.append(itemCard);
            });
        }

        function renderModalInfo(asm) {
            $('#modal-info-start').text(asm.start_date || '-');
            $('#modal-info-end').text(asm.end_date || '-');
            $('#modal-info-duration').text(`${asm.duration} Menit`);
            $('#modal-info-parent').text(asm.parent_title || 'Tidak ada (Assessment Utama)');

            const settingsDiv = $('#modal-info-settings');
            settingsDiv.empty();

            settingsDiv.append(`
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold ${asm.shuffle_questions ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'}">
                    <i class="fa-solid fa-${asm.shuffle_questions ? 'check text-emerald-600' : 'xmark text-slate-400'}"></i> Acak Soal
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold ${asm.shuffle_options ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'}">
                    <i class="fa-solid fa-${asm.shuffle_options ? 'check text-emerald-600' : 'xmark text-slate-400'}"></i> Acak Pilihan
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold ${asm.show_score ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'}">
                    <i class="fa-solid fa-${asm.show_score ? 'check text-emerald-600' : 'xmark text-slate-400'}"></i> Tampilkan Nilai Siswa
                </span>
            `);

            $('#modal-info-instruction').text(asm.instruction || 'Tidak ada petunjuk khusus.');
        }

        // ==========================================
        // RECOUNT PER ASSESSMENT (BATCH RECOUNT)
        // ==========================================
        function recountAllAssessment(assessmentId, title, isInModal = false) {
            const swalFn = (opts) => isInModal ? swalInModal(opts, 'modal-assessment-detail') : Swal.fire(opts);

            swalFn({
                title: 'Hitung Ulang Seluruh Peserta?',
                html: `Anda akan menghitung ulang nilai seluruh siswa pada:<br>
                       <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-left text-xs space-y-1">
                           <div>• Assessment: <strong class="text-slate-800">${escapeHtml(title)}</strong></div>
                           <div>• Cakupan: <strong>Seluruh butir jawaban objektif seluruh peserta</strong></div>
                           <div>• Penilaian Parsial: <strong>Termasuk opsi C pada soal MCMA</strong></div>
                           <div>• Soal ESSAY: <strong>Dikecualikan (dinilai manual oleh guru)</strong></div>
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280',
                confirmButtonText: '<i class="fa-solid fa-calculator mr-1"></i> Ya, Hitung Ulang Semua',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    swalFn({
                        title: 'Sedang Menghitung Ulang...',
                        text: 'Mengevaluasi jawaban seluruh siswa dan memperbarui rekapitulasi nilai...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/administrator/assessment-debug/assessment/${assessmentId}/recount`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (resp) {
                            swalFn({
                                icon: 'success',
                                title: 'Recount Sukses!',
                                text: resp.message,
                                timer: 3000
                            });

                            if (isInModal) {
                                fetchAssessmentDetail(assessmentId);
                            }
                            if (assessmentsLoaded) {
                                loadAssessments(currentAsmPage);
                            }
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            swalFn({
                                icon: 'error',
                                title: 'Gagal Recount',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghitung ulang.'
                            });
                        }
                    });
                }
            });
        }

        // ==========================================
        // RECOUNT USER FROM DETAIL MODAL
        // ==========================================
        function recountUserFromDetail(studentId, assessmentId, studentName) {
            swalInModal({
                title: 'Recount Nilai Siswa Ini?',
                html: `Hitung ulang butir jawaban untuk <strong>${escapeHtml(studentName)}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0071BC',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hitung Ulang',
                cancelButtonText: 'Batal'
            }, 'modal-assessment-detail').then((res) => {
                if (res.isConfirmed) {
                    swalInModal({
                        title: 'Menghitung Ulang...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }, 'modal-assessment-detail');

                    $.ajax({
                        url: `/administrator/assessment-debug/recount-user`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            student_id: studentId,
                            assessment_id: assessmentId
                        },
                        success: function (resp) {
                            swalInModal({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: `<div class="text-xs text-slate-700">${escapeHtml(resp.message)}</div>
                                       <div class="mt-2 text-xs font-bold text-emerald-600">Skor Baru: ${resp.data.new_total_score}</div>`,
                                timer: 2500
                            }, 'modal-assessment-detail');
                            fetchAssessmentDetail(assessmentId);
                            if (assessmentsLoaded) {
                                loadAssessments(currentAsmPage);
                            }
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            swalInModal({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                            }, 'modal-assessment-detail');
                        }
                    });
                }
            });
        }

        // ==========================================
        // SPECIFIC STUDENT EXAM DETAIL MODAL LOGIC
        // ==========================================
        let currentSedQuestions = [];
        let currentSedFilter = 'all';
        let currentSedData = null;

        function openStudentExamDetail(assessmentId, studentId, studentName) {
            document.getElementById('modal-student-exam-detail').showModal();
            $('#sed-student-name').text(studentName || 'Memuat...');
            $('#sed-subtitle').text('Memuat lembar jawaban dan butir soal...');
            $('#sed-stat-score').text('-');
            $('#sed-stat-correct').text('-');
            $('#sed-stat-wrong').text('-');
            $('#sed-stat-empty').text('-');
            $('#sed-stat-switch').text('-');

            $('#sed-questions-list').html(`
                <div class="p-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                    <div>Memuat butir soal dan rincian lembar jawaban siswa...</div>
                </div>
            `);

            $.ajax({
                url: `/administrator/assessment-debug/assessment/${assessmentId}/student/${studentId}`,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.status !== 'success') {
                        $('#sed-questions-list').html(`
                            <div class="p-8 text-center text-red-500">
                                <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                                <div>${escapeHtml(res.message || 'Gagal memuat detail')}</div>
                            </div>
                        `);
                        return;
                    }

                    currentSedData = res.data;
                    currentSedQuestions = res.data.questions || [];

                    renderStudentExamDetail(res.data);
                },
                error: function (xhr) {
                    $('#sed-questions-list').html(`
                        <div class="p-8 text-center text-red-500">
                            <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                            <div>${escapeHtml(xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat lembar jawaban.')}</div>
                        </div>
                    `);
                }
            });
        }

        function renderStudentExamDetail(data) {
            const stu = data.student;
            const asm = data.assessment;
            const att = data.attempt;
            const sum = data.summary;

            $('#sed-student-name').text(stu?.name || 'Siswa');
            $('#sed-subtitle').text(`NISN: ${stu?.nis || '-'} • Kelas: ${stu?.class_name || '-'} • Sekolah: ${stu?.school_name || '-'} • Ujian: ${asm?.title || '-'}`);
            $('#sed-badge-status').text((att?.status || 'SUBMITTED').toUpperCase());
            $('#sed-badge-mapel').text(asm?.mapel || '-');
            $('#sed-badge-category').text((asm?.category || 'MAIN').toUpperCase());

            $('#sed-stat-score').text(`${sum?.total_score ?? 0} / ${sum?.max_score ?? 100}`);
            $('#sed-stat-correct').text(`${sum?.correct_count ?? 0} Soal`);
            $('#sed-stat-wrong').text(`${sum?.wrong_count ?? 0} Soal`);
            $('#sed-stat-empty').text(`${sum?.empty_count ?? 0} Soal`);
            $('#sed-stat-switch').text(`${att?.tab_switch_count ?? 0}x`);

            $('#sed-time-start').text(att?.start_time || '-');
            $('#sed-time-expire').text(att?.expire_time || '-');
            $('#sed-time-duration').text(`${asm?.duration ?? 0} Menit`);

            // Counts for filter pills
            $('#sed-count-all').text(sum?.total_questions ?? 0);
            $('#sed-count-correct').text(sum?.correct_count ?? 0);
            $('#sed-count-wrong').text(sum?.wrong_count ?? 0);
            $('#sed-count-empty').text(sum?.empty_count ?? 0);

            // Bind recount button inside exam detail modal
            $('#btn-sed-recount').off('click').on('click', function () {
                swalInModal({
                    title: 'Recount Lembar Ujian Ini?',
                    html: `Hitung ulang nilai untuk <strong>${escapeHtml(stu.name)}</strong>?<br><span class="text-xs text-slate-500 mt-1 inline-block"><i class="fa-solid fa-circle-info mr-1 text-[#0071BC]"></i>Soal tipe ESSAY tidak akan diubah karena dinilai manual oleh guru.</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#F59E0B',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hitung Ulang',
                    cancelButtonText: 'Batal'
                }, 'modal-student-exam-detail').then((res) => {
                    if (res.isConfirmed) {
                        swalInModal({
                            title: 'Menghitung Ulang...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        }, 'modal-student-exam-detail');

                        $.ajax({
                            url: `/administrator/assessment-debug/recount-user`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                student_id: stu.id,
                                assessment_id: asm.id
                            },
                            success: function (resp) {
                                swalInModal({
                                    icon: 'success',
                                    title: 'Recount Berhasil!',
                                    html: `<div class="text-xs text-slate-700">${escapeHtml(resp.message)}</div>
                                           <div class="mt-2 text-xs font-bold text-emerald-600">Skor Baru: ${resp.data.new_total_score}</div>`,
                                    timer: 2500
                                }, 'modal-student-exam-detail');

                                openStudentExamDetail(asm.id, stu.id, stu.name);
                                if (currentDetailAssessmentId) {
                                    fetchAssessmentDetail(currentDetailAssessmentId);
                                }
                                if (assessmentsLoaded) {
                                    loadAssessments(currentAsmPage);
                                }
                                loadAttempts(currentPage);
                            },
                            error: function (xhr) {
                                swalInModal({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                                }, 'modal-student-exam-detail');
                            }
                        });
                    }
                });
            });

            filterStudentExamQuestions('all');
        }

        function filterStudentExamQuestions(filter) {
            currentSedFilter = filter;
            $('.sed-filter-btn').removeClass('bg-white text-[#0071BC] font-bold shadow-xs').addClass('text-slate-600 font-semibold');
            $(`[data-sed-filter="${filter}"]`).removeClass('text-slate-600 font-semibold').addClass('bg-white text-[#0071BC] font-bold shadow-xs');

            const container = $('#sed-questions-list');
            container.empty();

            let filtered = currentSedQuestions;
            if (filter === 'correct') {
                filtered = currentSedQuestions.filter(q => q.eval_status === 'correct' || q.eval_status === 'partial');
            } else if (filter === 'wrong') {
                filtered = currentSedQuestions.filter(q => q.eval_status === 'wrong');
            } else if (filter === 'empty') {
                filtered = currentSedQuestions.filter(q => q.eval_status === 'empty');
            }

            if (filtered.length === 0) {
                container.html(`
                    <div class="p-8 text-center text-slate-400 bg-white border border-slate-200 rounded-2xl">
                        <i class="fa-solid fa-inbox text-2xl mb-2 text-slate-300"></i>
                        <div>Tidak ada butir soal pada kategori filter ini.</div>
                    </div>
                `);
                return;
            }

            filtered.forEach(q => {
                let statusBadge = '';
                let borderClass = 'border-slate-200';
                if (q.eval_status === 'correct') {
                    statusBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fa-solid fa-check mr-1"></i>Benar (${q.score}/${q.weight})</span>`;
                    borderClass = 'border-emerald-300 bg-emerald-50/10';
                } else if (q.eval_status === 'partial') {
                    statusBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300"><i class="fa-solid fa-circle-half-stroke mr-1"></i>Sebagian (${q.score}/${q.weight})</span>`;
                    borderClass = 'border-amber-300 bg-amber-50/10';
                } else if (q.eval_status === 'pending') {
                    statusBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300"><i class="fa-solid fa-hourglass-half mr-1"></i>Menunggu Guru (0/${q.weight})</span>`;
                    borderClass = 'border-amber-300 bg-amber-50/10';
                } else if (q.eval_status === 'wrong') {
                    statusBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-red-100 text-red-700 border border-red-300"><i class="fa-solid fa-xmark mr-1"></i>Salah (0/${q.weight})</span>`;
                    borderClass = 'border-red-200 bg-red-50/10';
                } else {
                    statusBadge = `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-minus mr-1"></i>Kosong (0/${q.weight})</span>`;
                    borderClass = 'border-slate-200';
                }

                let answerBodyHtml = '';

                if (q.tipe_soal === 'MCQ' || q.tipe_soal === 'MCMA') {
                    const studentAnswerArr = Array.isArray(q.student_answer)
                        ? q.student_answer.map(String).map(s => s.trim().toLowerCase())
                        : (q.student_answer !== null && q.student_answer !== undefined ? [String(q.student_answer).trim().toLowerCase()] : []);

                    let optionsListHtml = '<div class="mt-2.5 space-y-2">';
                    if (q.options && q.options.length > 0) {
                        const optionLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                        q.options.forEach((opt, optIndex) => {
                            const optKey = String(opt.key).trim();
                            const isChosen = studentAnswerArr.includes(optKey.toLowerCase());
                            const isCorrect = Boolean(opt.is_correct);

                            let displayLetter = optionLetters[optIndex] || '';
                            if (/^[A-Za-z]$/.test(optKey)) {
                                displayLetter = optKey.toUpperCase();
                            } else if (/^option_?(\d+)$/i.test(optKey)) {
                                const num = parseInt(optKey.replace(/\D/g, ''), 10);
                                if (num >= 1 && num <= 26) {
                                    displayLetter = String.fromCharCode(64 + num);
                                }
                            }
                            if (!displayLetter) {
                                displayLetter = optionLetters[optIndex] || optKey;
                            }

                            let optStyle = 'bg-white text-slate-700 border-slate-200';
                            let optTag = '';
                            let badgeStyle = 'bg-slate-100 text-slate-600 border border-slate-200';

                            if (isChosen && isCorrect) {
                                optStyle = 'bg-emerald-50/90 text-emerald-950 border-emerald-400 font-bold shadow-2xs';
                                optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-emerald-200/90 text-emerald-900 font-extrabold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-check mr-1 text-emerald-700"></i>Pilihan Siswa & Kunci Benar</span>';
                                badgeStyle = 'bg-emerald-600 text-white shadow-2xs';
                            } else if (isChosen && !isCorrect) {
                                optStyle = 'bg-red-50/90 text-red-950 border-red-300 font-bold shadow-2xs';
                                optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-red-200/90 text-red-900 font-extrabold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-xmark mr-1 text-red-700"></i>Pilihan Siswa (Salah)</span>';
                                badgeStyle = 'bg-red-600 text-white shadow-2xs';
                            } else if (!isChosen && isCorrect) {
                                optStyle = 'bg-emerald-50/40 text-emerald-900 border-dashed border-emerald-300 font-medium';
                                optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-bold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-key mr-1 text-emerald-600"></i>Kunci Benar</span>';
                                badgeStyle = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                            }

                            optionsListHtml += `
                                <div class="px-3.5 py-2.5 rounded-xl border text-xs flex items-center justify-between gap-3 transition-colors ${optStyle}">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-7 h-7 min-w-[28px] rounded-lg shrink-0 flex items-center justify-center text-xs font-black select-none ${badgeStyle}" title="Key: ${escapeHtml(opt.key)}">
                                            ${escapeHtml(displayLetter)}
                                        </span>
                                        <div class="font-medium leading-relaxed break-words text-slate-800">${escapeHtml(opt.value)}</div>
                                    </div>
                                    <div class="shrink-0">${optTag}</div>
                                </div>
                            `;
                        });
                    }
                    optionsListHtml += '</div>';

                    if (studentAnswerArr.length === 0) {
                        answerBodyHtml = `
                            <div class="mt-2 p-2.5 rounded-xl bg-slate-100 text-slate-500 text-xs font-semibold flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-slate-400"></i>
                                <span>Siswa tidak memilih jawaban apapun untuk butir soal ini.</span>
                            </div>
                            ${optionsListHtml}
                        `;
                    } else {
                        answerBodyHtml = optionsListHtml;
                    }
                } else if (q.tipe_soal === 'ESSAY') {
                    answerBodyHtml = `
                        <div class="mt-2.5 space-y-2">
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jawaban Siswa:</div>
                            <div class="p-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 font-medium leading-relaxed">
                                ${q.student_answer ? escapeHtml(String(q.student_answer)) : '<em class="text-slate-400">Siswa tidak memberikan jawaban esai.</em>'}
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-500 font-medium">Status Penilaian Guru:</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold ${q.grading_status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}">
                                    ${q.grading_status === 'graded' ? 'Sudah Dinilai' : 'Menunggu Penilaian Manual'}
                                </span>
                                ${q.teacher_feedback ? `<span class="text-slate-600">Catatan: "${escapeHtml(q.teacher_feedback)}"</span>` : ''}
                            </div>
                        </div>
                    `;
                } else {
                    // MATCHING / PG_KOMPLEKS / DLL
                    let chosenStr = '-';
                    if (q.student_answer) {
                        try {
                            chosenStr = typeof q.student_answer === 'object' ? JSON.stringify(q.student_answer, null, 2) : String(q.student_answer);
                        } catch (e) {
                            chosenStr = String(q.student_answer);
                        }
                    }

                    answerBodyHtml = `
                        <div class="mt-2.5 space-y-2 text-xs">
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                                <div class="font-bold text-slate-700">Data Pilihan Siswa:</div>
                                <pre class="font-mono text-[11px] text-slate-800 bg-white p-2 rounded-lg border border-slate-200 overflow-x-auto">${escapeHtml(chosenStr)}</pre>
                            </div>
                        </div>
                    `;
                }

                const itemCard = `
                    <div class="border rounded-2xl p-4 shadow-2xs space-y-2.5 bg-white transition hover:shadow-xs ${borderClass}">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 font-extrabold text-slate-700 text-xs flex items-center justify-center">${q.number}</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-50 text-[#0071BC] border border-blue-200">${escapeHtml(q.tipe_soal)}</span>
                                <span class="text-[11px] text-slate-400 font-medium">Difficulty: ${escapeHtml(q.difficulty)}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                ${statusBadge}
                                <span class="text-xs font-bold text-slate-700">
                                    Skor: <strong class="text-[#0071BC] font-extrabold">${q.score}</strong> / ${q.weight}
                                </span>
                                <button type="button" onclick="openEditQuestionGradeModal(${q.number})"
                                    class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-[#0071BC] text-[#0071BC] hover:text-white font-bold text-[11px] border border-sky-200 transition active:scale-95 ml-1"
                                    title="Ubah nilai dan jawaban butir soal ini secara manual">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                    <span>Edit Soal</span>
                                </button>
                            </div>
                        </div>

                        <div class="text-xs text-slate-800 leading-relaxed font-medium">
                            ${escapeHtml(q.question_text)}
                        </div>

                        ${answerBodyHtml}
                    </div>
                `;

                container.append(itemCard);
            });
        }

        // Filter Handlers for Assessments View
        $(document).on('click', '.asm-category-filter-btn', function () {
            $('.asm-category-filter-btn').removeClass('bg-white text-[#0071BC] font-bold shadow-xs').addClass('text-slate-600 font-semibold hover:text-blue-600');
            $(this).removeClass('text-slate-600 font-semibold hover:text-blue-600 hover:text-purple-600 hover:text-amber-600 hover:text-emerald-600').addClass('bg-white text-[#0071BC] font-bold shadow-xs');
            currentAsmCategory = $(this).data('category');
            loadAssessments(1);
        });

        $('#filter-asm-school, #filter-asm-status, #filter-asm-per-page').on('change', function () {
            loadAssessments(1);
        });

        $('#filter-asm-date-start, #filter-asm-date-end').on('change', function () {
            const dStart = $('#filter-asm-date-start').val();
            if (dStart) {
                $('#filter-asm-per-page').val('all');
            }
            loadAssessments(1);
        });

        $('#btn-asm-quick-today').on('click', function () {
            const today = getLocalDateString(new Date());
            $('#filter-asm-date-start').val(today);
            $('#filter-asm-date-end').val(today);
            $('#filter-asm-per-page').val('all');
            loadAssessments(1);
        });

        $('#btn-asm-clear-date').on('click', function () {
            $('#filter-asm-date-start').val('');
            $('#filter-asm-date-end').val('');
            $('#filter-asm-per-page').val('15');
            loadAssessments(1);
        });

        $('#filter-asm-search').on('input', function () {
            clearTimeout(asmSearchTimeout);
            asmSearchTimeout = setTimeout(() => {
                loadAssessments(1);
            }, 400);
        });

        $('#btn-refresh-attempts').on('click', function () {
            const icon = $('#icon-refresh');
            icon.addClass('fa-spin');
            if (currentMainTab === 'assessments') {
                loadAssessments(currentAsmPage);
            } else {
                loadAttempts(currentPage);
            }
            setTimeout(() => icon.removeClass('fa-spin'), 600);
        });

        // ==========================================
        // 2-STEP WARNING CONFIRMATION SYSTEM
        // ==========================================
        function executeWithTwoStepWarning(options, onConfirmed) {
            const targetEl = options.target || getActiveTopDialog();

            // STEP 1: Peringatan Awal Konsekuensi
            swalInModal({
                target: targetEl,
                title: options.step1Title || '⚠️ Peringatan (Langkah 1 dari 2)',
                html: `
                    <div class="text-left text-xs space-y-3">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 leading-relaxed font-semibold">
                            <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600"></i>
                            ${options.step1WarningText || 'Anda akan melakukan perubahan nilai atau jawaban secara manual. Tindakan ini berdampak langsung pada rapor dan riwayat ujian siswa.'}
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5 font-medium text-slate-700">
                            <div>• <strong>Siswa:</strong> ${escapeHtml(options.studentName || '-')}</div>
                            <div>• <strong>Ujian:</strong> ${escapeHtml(options.assessmentTitle || '-')}</div>
                            <div>• <strong>Cakupan:</strong> <span class="text-[#0071BC] font-bold">${options.scopeText || '-'}</span></div>
                            <div>• <strong>Detail Perubahan:</strong> ${options.changeDetailHtml || '-'}</div>
                        </div>
                        <div class="text-slate-500 text-[11px]">
                            Langkah ini memastikan Anda tidak mengubah data siswa secara tidak sengaja. Klik <strong>Lanjut ke Langkah 2</strong> untuk verifikasi akhir.
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Lanjut ke Langkah 2 <i class="fa-solid fa-arrow-right ml-1"></i>',
                cancelButtonText: 'Batal',
                focusCancel: true
            }, targetEl).then((res1) => {
                if (!res1.isConfirmed) return;

                // STEP 2: Verifikasi Kata Kunci Keamanan "UBAH"
                swalInModal({
                    target: targetEl,
                    title: options.step2Title || '🛑 Konfirmasi Akhir (Langkah 2 dari 2)',
                    html: `
                        <div class="text-left text-xs space-y-3">
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-900 leading-relaxed font-semibold">
                                <i class="fa-solid fa-shield-halved mr-1.5 text-red-600"></i>
                                Konfirmasi terakhir untuk menerapkan perubahan secara permanen ke database LMS.
                            </div>
                            <p class="text-slate-700">
                                Untuk mengonfirmasi bahwa perubahan ini benar-benar disengaja, silakan ketik kata 
                                <strong class="text-red-600 font-mono text-sm bg-red-100 px-1.5 py-0.5 rounded border border-red-200">UBAH</strong> 
                                pada kolom input di bawah ini:
                            </p>
                        </div>
                    `,
                    icon: 'error',
                    input: 'text',
                    inputPlaceholder: 'Ketik UBAH di sini...',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i>Ya, Simpan Perubahan Permanen',
                    cancelButtonText: 'Batalkan',
                    focusCancel: true,
                    inputValidator: (value) => {
                        if (!value || value.trim().toUpperCase() !== 'UBAH') {
                            return 'Anda harus mengetik "UBAH" dengan benar untuk melanjutkan.';
                        }
                    }
                }, targetEl).then((res2) => {
                    if (res2.isConfirmed && res2.value) {
                        onConfirmed(res2.value.trim().toUpperCase());
                    }
                });
            });
        }

        // ==========================================
        // MANUAL GRADE & ANSWER EDIT: PER QUESTION
        // ==========================================
        let currentEditingQuestionNumber = null;

        function openEditQuestionGradeModal(qNumber) {
            const q = currentSedQuestions.find(item => item.number === qNumber);
            if (!q || !currentSedData) {
                swalInModal({ icon: 'error', title: 'Error', text: 'Data butir soal tidak ditemukan.' }, 'modal-student-exam-detail');
                return;
            }

            currentEditingQuestionNumber = qNumber;
            const stu = currentSedData.student;
            const asm = currentSedData.assessment;

            $('#eqm-badge-number').text(`Soal #${q.number}`);
            $('#eqm-badge-type').text(q.tipe_soal);
            $('#eqm-student-name').text(stu.name);
            $('#eqm-max-weight').text(`${q.weight} Poin`);
            $('#eqm-question-text').text(q.question_text || '(Tidak ada teks pertanyaan)');
            $('#eqm-current-score').text(q.score);
            $('#eqm-input-score').val(q.score);
            $('#eqm-select-grading-status').val(q.grading_status || 'graded');
            $('#eqm-select-status-answer').val(q.status_answer || 'submitted');
            $('#eqm-input-feedback').val(q.teacher_feedback || '');

            // Render input jawaban dinamis
            const container = $('#eqm-answer-input-container');
            container.empty();

            const studentAnsArr = Array.isArray(q.student_answer)
                ? q.student_answer.map(v => String(v).trim().toLowerCase())
                : (q.student_answer !== null && q.student_answer !== undefined ? [String(q.student_answer).trim().toLowerCase()] : []);

            if (q.tipe_soal === 'MCQ' || q.tipe_soal === 'PILIHAN GANDA' || (q.options && q.options.length > 0 && q.tipe_soal !== 'MCMA')) {
                $('#eqm-answer-guide').text('Pilih 1 opsi jawaban');
                let radioListHtml = '<div class="space-y-1.5">';
                q.options.forEach((opt, idx) => {
                    const optKey = String(opt.key).trim();
                    const isChecked = studentAnsArr.includes(optKey.toLowerCase());
                    const isKeyCorrect = Boolean(opt.is_correct);

                    radioListHtml += `
                        <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-xs transition ${isChecked ? 'bg-sky-50/70 border-sky-300 font-bold' : ''}">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="eqm-radio-answer" value="${escapeHtml(optKey)}" class="w-4 h-4 text-[#0071BC] focus:ring-sky-200" ${isChecked ? 'checked' : ''}>
                                <span class="font-extrabold text-slate-800">[${escapeHtml(optKey.toUpperCase())}]</span>
                                <span class="text-slate-700 font-medium">${escapeHtml(opt.value)}</span>
                            </div>
                            ${isKeyCorrect ? '<span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold border border-emerald-300"><i class="fa-solid fa-key mr-1"></i>Kunci Benar</span>' : ''}
                        </label>
                    `;
                });
                radioListHtml += `
                    <label class="flex items-center gap-2 p-2 rounded-xl text-xs text-slate-500 hover:bg-slate-100 cursor-pointer">
                        <input type="radio" name="eqm-radio-answer" value="__EMPTY__" class="w-4 h-4" ${studentAnsArr.length === 0 ? 'checked' : ''}>
                        <span>(Kosongkan / Siswa Tidak Menjawab)</span>
                    </label>
                </div>`;
                container.html(radioListHtml);
            } else if (q.tipe_soal === 'MCMA') {
                $('#eqm-answer-guide').text('Pilih satu atau beberapa opsi jawaban');
                let checkListHtml = '<div class="space-y-1.5">';
                q.options.forEach((opt, idx) => {
                    const optKey = String(opt.key).trim();
                    const isChecked = studentAnsArr.includes(optKey.toLowerCase());
                    const isKeyCorrect = Boolean(opt.is_correct);

                    checkListHtml += `
                        <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-xs transition ${isChecked ? 'bg-sky-50/70 border-sky-300 font-bold' : ''}">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" class="eqm-check-answer w-4 h-4 rounded text-[#0071BC] focus:ring-sky-200 cursor-pointer" value="${escapeHtml(optKey)}" ${isChecked ? 'checked' : ''}>
                                <span class="font-extrabold text-slate-800">[${escapeHtml(optKey.toUpperCase())}]</span>
                                <span class="text-slate-700 font-medium">${escapeHtml(opt.value)}</span>
                            </div>
                            ${isKeyCorrect ? '<span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold border border-emerald-300"><i class="fa-solid fa-key mr-1"></i>Kunci Benar</span>' : ''}
                        </label>
                    `;
                });
                checkListHtml += '</div>';
                container.html(checkListHtml);
            } else if (q.tipe_soal === 'ESSAY') {
                $('#eqm-answer-guide').text('Tuliskan teks jawaban esai siswa');
                const textVal = q.student_answer ? String(q.student_answer) : '';
                container.html(`
                    <textarea id="eqm-textarea-answer" rows="4" class="w-full p-3 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-[#0071BC] focus:bg-white font-medium" placeholder="Ketik teks jawaban esai...">${escapeHtml(textVal)}</textarea>
                `);
            } else {
                $('#eqm-answer-guide').text('Format JSON atau teks string');
                let rawStr = '';
                if (q.student_answer !== null && q.student_answer !== undefined) {
                    rawStr = typeof q.student_answer === 'object' ? JSON.stringify(q.student_answer, null, 2) : String(q.student_answer);
                }
                container.html(`
                    <textarea id="eqm-textarea-answer" rows="4" class="w-full p-3 font-mono text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-[#0071BC] focus:bg-white" placeholder="Ketik data jawaban...">${escapeHtml(rawStr)}</textarea>
                `);
            }

            document.getElementById('modal-edit-question-grade').showModal();
        }

        // Quick score buttons in Question Modal
        $('#eqm-btn-full-score').on('click', function () {
            const q = currentSedQuestions.find(item => item.number === currentEditingQuestionNumber);
            if (q) $('#eqm-input-score').val(q.weight);
        });

        $('#eqm-btn-zero-score').on('click', function () {
            $('#eqm-input-score').val(0);
        });

        // Save Question Edit with 2-Step Warning
        $('#btn-save-question-edit').on('click', function () {
            const q = currentSedQuestions.find(item => item.number === currentEditingQuestionNumber);
            if (!q || !currentSedData) return;

            const newScore = parseFloat($('#eqm-input-score').val());
            if (isNaN(newScore) || newScore < 0) {
                swalInModal({ icon: 'error', title: 'Input Tidak Valid', text: 'Nilai skor harus berupa angka minimal 0.' }, 'modal-edit-question-grade');
                return;
            }

            // Kumpulkan data jawaban
            let newAnswer = null;
            if (q.tipe_soal === 'MCQ' || q.tipe_soal === 'PILIHAN GANDA' || (q.options && q.options.length > 0 && q.tipe_soal !== 'MCMA')) {
                const selectedRadio = $('input[name="eqm-radio-answer"]:checked').val();
                if (selectedRadio && selectedRadio !== '__EMPTY__') {
                    newAnswer = [selectedRadio];
                } else {
                    newAnswer = null;
                }
            } else if (q.tipe_soal === 'MCMA') {
                const checked = [];
                $('.eqm-check-answer:checked').each(function () {
                    checked.push($(this).val());
                });
                newAnswer = checked.length > 0 ? checked : null;
            } else {
                const textVal = $('#eqm-textarea-answer').val();
                newAnswer = textVal ? textVal.trim() : null;
            }

            const gradingStatus = $('#eqm-select-grading-status').val();
            const statusAnswer = $('#eqm-select-status-answer').val();
            const feedback = $('#eqm-input-feedback').val();

            const stu = currentSedData.student;
            const asm = currentSedData.assessment;

            // Trigger 2-Step Warning
            executeWithTwoStepWarning({
                target: document.getElementById('modal-edit-question-grade'),
                step1Title: `⚠️ Peringatan (Langkah 1/2): Ubah Soal #${q.number}`,
                step1WarningText: `Anda akan mengubah nilai/jawaban butir soal nomor #${q.number} secara manual untuk siswa <strong>${escapeHtml(stu.name)}</strong>.`,
                studentName: stu.name,
                assessmentTitle: asm.title,
                scopeText: `Butir Soal #${q.number} (${escapeHtml(q.tipe_soal)})`,
                changeDetailHtml: `
                    <div>Nilai Skor: <strong class="text-slate-500">${q.score}</strong> → <strong class="text-[#0071BC]">${newScore}</strong> (Bobot: ${q.weight})</div>
                    <div>Status Penilaian: <strong class="text-slate-800">${escapeHtml(gradingStatus)}</strong></div>
                `,
                step2Title: `🛑 Konfirmasi Akhir (Langkah 2/2): Ubah Soal #${q.number}`
            }, function (confirmText) {
                // User confirmed step 2
                swalInModal({
                    title: 'Menyimpan Perubahan...',
                    text: 'Memperbarui database nilai siswa...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                }, 'modal-edit-question-grade');

                $.ajax({
                    url: `/administrator/assessment-debug/assessment/${asm.id}/student/${stu.id}/question/${q.question_id}/update`,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        question_score: newScore,
                        answer_value: newAnswer,
                        grading_status: gradingStatus,
                        status_answer: statusAnswer,
                        teacher_feedback: feedback,
                        confirm_text: confirmText
                    },
                    success: function (res) {
                        document.getElementById('modal-edit-question-grade').close();
                        swalInModal({
                            icon: 'success',
                            title: 'Tersimpan!',
                            html: `<div class="text-xs text-slate-700">${escapeHtml(res.message)}</div>
                                   <div class="mt-2 text-xs font-bold text-emerald-600">Total Nilai Baru Ujian: ${res.data.new_total_score}</div>`,
                            timer: 2500,
                            showConfirmButton: false
                        }, 'modal-student-exam-detail');

                        // Refresh Exam Detail & Attempt List
                        openStudentExamDetail(asm.id, stu.id, stu.name);
                        loadAttempts(currentPage);
                        if (currentDetailAssessmentId) {
                            fetchAssessmentDetail(currentDetailAssessmentId);
                        }
                    },
                    error: function (xhr) {
                        swalInModal({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan pada server.'
                        }, 'modal-edit-question-grade');
                    }
                });
            });
        });

        // ==========================================
        // MANUAL GRADE & ANSWER EDIT: PER EXAM BASIS
        // ==========================================
        let currentExamEditMode = 'scale';

        function switchExamEditTab(tab) {
            currentExamEditMode = tab;
            $('.eem-tab-btn').removeClass('border-[#0071BC] text-[#0071BC] font-bold').addClass('border-transparent text-slate-500 font-semibold');
            $(`#eem-tab-btn-${tab}`).addClass('border-[#0071BC] text-[#0071BC] font-bold').removeClass('border-transparent text-slate-500');

            $('#eem-pane-scale, #eem-pane-grid, #eem-pane-preset').addClass('hidden');
            $(`#eem-pane-${tab}`).removeClass('hidden');

            if (tab === 'preset') {
                $('#btn-save-exam-edit').addClass('hidden');
            } else {
                $('#btn-save-exam-edit').removeClass('hidden');
            }
        }

        function setEemTargetScore(score) {
            if (!currentSedData) return;
            const max = currentSedData.summary.max_score || 100;
            const finalScore = Math.min(score, max);
            $('#eem-input-target-score').val(finalScore);
        }

        function openEditExamModal() {
            if (!currentSedData) {
                swalInModal({ icon: 'warning', title: 'Data Tidak Tersedia', text: 'Buka detail lembar ujian siswa terlebih dahulu.' }, 'modal-student-exam-detail');
                return;
            }

            const stu = currentSedData.student;
            const asm = currentSedData.assessment;
            const sum = currentSedData.summary;

            $('#eem-student-name').text(stu.name);
            $('#eem-assessment-title').text(asm.title);
            $('#eem-current-total-score').text(`${sum.total_score} / ${sum.max_score}`);
            $('#eem-badge-total-q').text(`${sum.total_questions} Butir Soal`);

            // Tab 1 setup
            $('#eem-input-target-score').val(sum.total_score);
            $('#eem-max-score-label').text(sum.max_score);

            // Tab 2 setup (Grid)
            renderEemBatchGrid();

            switchExamEditTab('scale');
            document.getElementById('modal-edit-exam-grades').showModal();
        }

        function renderEemBatchGrid() {
            const tbody = $('#eem-grid-table-body');
            tbody.empty();

            let totalCalc = 0;

            currentSedQuestions.forEach((q) => {
                totalCalc += parseFloat(q.score) || 0;

                let answerSelectorHtml = '';
                const studentAnsArr = Array.isArray(q.student_answer)
                    ? q.student_answer.map(v => String(v).trim().toLowerCase())
                    : (q.student_answer !== null && q.student_answer !== undefined ? [String(q.student_answer).trim().toLowerCase()] : []);

                if (q.options && q.options.length > 0 && q.tipe_soal !== 'MCMA') {
                    answerSelectorHtml = `<select class="eem-grid-ans w-full h-8 px-2 text-xs bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-[#0071BC]" data-qid="${q.question_id}">`;
                    answerSelectorHtml += `<option value="" ${studentAnsArr.length === 0 ? 'selected' : ''}>-- Kosong --</option>`;
                    q.options.forEach((opt) => {
                        const optKey = String(opt.key).trim();
                        const isSelected = studentAnsArr.includes(optKey.toLowerCase());
                        answerSelectorHtml += `<option value="${escapeHtml(optKey)}" ${isSelected ? 'selected' : ''}>[${escapeHtml(optKey.toUpperCase())}] ${escapeHtml(opt.value.substring(0, 18))}${opt.is_correct ? ' (Kunci)' : ''}</option>`;
                    });
                    answerSelectorHtml += `</select>`;
                } else {
                    const textVal = q.student_answer ? (typeof q.student_answer === 'object' ? JSON.stringify(q.student_answer) : String(q.student_answer)) : '';
                    answerSelectorHtml = `<input type="text" class="eem-grid-ans w-full h-8 px-2 text-xs bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-[#0071BC]" data-qid="${q.question_id}" value="${escapeHtml(textVal)}" placeholder="Jawaban...">`;
                }

                const row = `
                    <tr class="hover:bg-slate-50 transition" data-qid="${q.question_id}">
                        <td class="px-3 py-2 text-center font-bold text-slate-700">${q.number}</td>
                        <td class="px-3 py-2">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-[#0071BC] border border-blue-200">${escapeHtml(q.tipe_soal)}</span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="text-[11px] font-medium text-slate-800 line-clamp-1" title="${escapeHtml(q.question_text)}">${escapeHtml(q.question_text)}</div>
                        </td>
                        <td class="px-3 py-2">
                            ${answerSelectorHtml}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" step="0.01" min="0" max="${q.weight}" class="eem-grid-score w-16 h-8 text-center text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-[#0071BC]" data-qid="${q.question_id}" data-weight="${q.weight}" value="${q.score}">
                                <span class="text-[11px] text-slate-400">/${q.weight}</span>
                                <button type="button" onclick="setGridRowScore(${q.question_id}, ${q.weight})" class="px-1.5 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[10px] font-bold border border-emerald-200 transition" title="Beri Nilai Penuh">✓</button>
                                <button type="button" onclick="setGridRowScore(${q.question_id}, 0)" class="px-1.5 py-0.5 rounded bg-red-50 hover:bg-red-100 text-red-600 text-[10px] font-bold border border-red-200 transition" title="Beri Skor 0">✕</button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            $('#eem-grid-calculated-total').text(totalCalc.toFixed(2));

            // Re-calculate total on grid score changes
            $('.eem-grid-score').off('input').on('input', function () {
                let sum = 0;
                $('.eem-grid-score').each(function () {
                    sum += parseFloat($(this).val()) || 0;
                });
                $('#eem-grid-calculated-total').text(sum.toFixed(2));
            });
        }

        function setGridRowScore(qid, score) {
            $(`.eem-grid-score[data-qid="${qid}"]`).val(score).trigger('input');
        }

        // Apply Exam Preset (100% or 0%)
        function applyExamPreset(presetMode) {
            const stu = currentSedData.student;
            const asm = currentSedData.assessment;
            const isCorrect = presetMode === 'preset_all_correct';

            executeWithTwoStepWarning({
                target: document.getElementById('modal-edit-exam-grades'),
                step1Title: `⚠️ Peringatan (Langkah 1/2): Preset Ujian`,
                step1WarningText: isCorrect
                    ? `Anda akan mengatur seluruh butir soal menjadi <strong>BENAR (100% Nilai Maksimal)</strong> untuk siswa <strong>${escapeHtml(stu.name)}</strong>.`
                    : `Anda akan mengatur seluruh butir soal menjadi <strong>SALAH (Skor 0)</strong> untuk siswa <strong>${escapeHtml(stu.name)}</strong>.`,
                studentName: stu.name,
                assessmentTitle: asm.title,
                scopeText: `Seluruh Ujian (${currentSedQuestions.length} Butir Soal)`,
                changeDetailHtml: isCorrect
                    ? `<span class="text-emerald-600 font-bold">Semua jawaban diatur ke kunci benar dengan skor maksimal ${currentSedData.summary.max_score}.</span>`
                    : `<span class="text-red-600 font-bold">Semua butir soal diatur ke skor 0.</span>`,
                step2Title: `🛑 Konfirmasi Akhir (Langkah 2/2): Preset Ujian`
            }, function (confirmText) {
                dispatchExamGradesUpdate({
                    mode: presetMode,
                    confirm_text: confirmText
                });
            });
        }

        // Save Exam Grades Edit (Scale or Batch Grid)
        $('#btn-save-exam-edit').on('click', function () {
            if (!currentSedData) return;
            const stu = currentSedData.student;
            const asm = currentSedData.assessment;

            if (currentExamEditMode === 'scale') {
                const targetScore = parseFloat($('#eem-input-target-score').val());
                if (isNaN(targetScore) || targetScore < 0) {
                    swalInModal({ icon: 'error', title: 'Input Tidak Valid', text: 'Target total nilai harus berupa angka minimal 0.' }, 'modal-edit-exam-grades');
                    return;
                }

                executeWithTwoStepWarning({
                    target: document.getElementById('modal-edit-exam-grades'),
                    step1Title: `⚠️ Peringatan (Langkah 1/2): Skala Total Nilai Ujian`,
                    step1WarningText: `Anda akan mengubah total nilai ujian untuk siswa <strong>${escapeHtml(stu.name)}</strong> secara menyeluruh dengan mendistribusikan nilai secara proporsional.`,
                    studentName: stu.name,
                    assessmentTitle: asm.title,
                    scopeText: `Skala Proporsional Seluruh Ujian (${currentSedQuestions.length} Butir Soal)`,
                    changeDetailHtml: `Total Nilai: <strong class="text-slate-500">${currentSedData.summary.total_score}</strong> → <strong class="text-[#0071BC] text-sm">${targetScore}</strong> (dari maks ${currentSedData.summary.max_score})`,
                    step2Title: `🛑 Konfirmasi Akhir (Langkah 2/2): Ubah Nilai Ujian`
                }, function (confirmText) {
                    dispatchExamGradesUpdate({
                        mode: 'target_total_score',
                        target_total_score: targetScore,
                        confirm_text: confirmText
                    });
                });
            } else if (currentExamEditMode === 'grid') {
                const batchQuestions = [];
                let totalCalc = 0;

                $('#eem-grid-table-body tr').each(function () {
                    const qid = $(this).data('qid');
                    const score = parseFloat($(this).find('.eem-grid-score').val()) || 0;
                    const ansVal = $(this).find('.eem-grid-ans').val();
                    totalCalc += score;

                    batchQuestions.push({
                        question_id: qid,
                        score: score,
                        answer_value: ansVal ? [ansVal] : null
                    });
                });

                executeWithTwoStepWarning({
                    target: document.getElementById('modal-edit-exam-grades'),
                    step1Title: `⚠️ Peringatan (Langkah 1/2): Batch Edit Seluruh Soal`,
                    step1WarningText: `Anda akan memperbarui rincian nilai dan jawaban ${batchQuestions.length} butir soal untuk siswa <strong>${escapeHtml(stu.name)}</strong>.`,
                    studentName: stu.name,
                    assessmentTitle: asm.title,
                    scopeText: `Batch Edit (${batchQuestions.length} Butir Soal)`,
                    changeDetailHtml: `Total Nilai Baru: <strong class="text-[#0071BC] text-sm">${totalCalc.toFixed(2)}</strong> (Sebelumnya: ${currentSedData.summary.total_score})`,
                    step2Title: `🛑 Konfirmasi Akhir (Langkah 2/2): Batch Edit Ujian`
                }, function (confirmText) {
                    dispatchExamGradesUpdate({
                        mode: 'batch_questions',
                        questions: batchQuestions,
                        confirm_text: confirmText
                    });
                });
            }
        });

        function dispatchExamGradesUpdate(payload) {
            const stu = currentSedData.student;
            const asm = currentSedData.assessment;

            swalInModal({
                title: 'Menyimpan Perubahan Ujian...',
                text: 'Memproses pembaruan nilai & jawaban siswa...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            }, 'modal-edit-exam-grades');

            $.ajax({
                url: `/administrator/assessment-debug/assessment/${asm.id}/student/${stu.id}/update-exam-grades`,
                type: 'POST',
                data: Object.assign({
                    _token: "{{ csrf_token() }}"
                }, payload),
                success: function (res) {
                    document.getElementById('modal-edit-exam-grades').close();
                    swalInModal({
                        icon: 'success',
                        title: 'Berhasil Diperbarui!',
                        html: `<div class="text-xs text-slate-700">${escapeHtml(res.message)}</div>
                               <div class="mt-2 text-xs font-bold text-emerald-600">Total Nilai Baru: ${res.data.new_total_score}</div>`,
                        timer: 2500,
                        showConfirmButton: false
                    }, 'modal-student-exam-detail');

                    // Refresh
                    openStudentExamDetail(asm.id, stu.id, stu.name);
                    loadAttempts(currentPage);
                    if (currentDetailAssessmentId) {
                        fetchAssessmentDetail(currentDetailAssessmentId);
                    }
                },
                error: function (xhr) {
                    swalInModal({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan pada server.'
                    }, 'modal-edit-exam-grades');
                }
            });
        }

        // Open Exam Edit from Detail Header
        $('#btn-sed-edit-exam').on('click', function () {
            openEditExamModal();
        });

        // Open Exam Edit from Attempt Edit Modal
        $('#btn-edit-exam-from-attempt-modal').on('click', function () {
            const attemptId = $('#edit-attempt-id').val();
            if (!attemptId) return;

            document.getElementById('modal-edit-attempt').close();
            $.ajax({
                url: `/administrator/assessment-debug/data`,
                type: 'GET',
                data: { page: 1, search: '', per_page: 'all' },
                success: function (res) {
                    const item = res.data?.find(a => a.id == attemptId);
                    if (item) {
                        const studentName = item.user_account?.student_profile?.nama_lengkap || 'Siswa';
                        openStudentExamDetail(item.school_assessment_id, item.student_id, studentName);
                        setTimeout(() => openEditExamModal(), 600);
                    } else {
                        swalInModal({ icon: 'error', title: 'Error', text: 'Sesi ujian tidak ditemukan.' });
                    }
                }
            });
        });

        $(document).ready(function () {
            loadAttempts(1);
        });
    </script>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p class="text-xl font-bold text-red-600">Akses Ditolak</p>
        <p class="text-sm text-gray-500">Halaman ini hanya dapat diakses oleh Administrator Utama.</p>
    </div>
@endif
