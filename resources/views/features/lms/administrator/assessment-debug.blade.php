@include('components/sidebar-beranda', ['headerSideNav' => 'Exam Debugger'])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out z-20 pb-20">
        <div class="mt-4 sm:mt-6 mx-4 sm:mx-8 space-y-6">

            <!-- HERO HEADER -->
            <section>
                <div class="bg-[linear-gradient(135deg,#0071BC_0%,#004f84_60%,#002b49_100%)] rounded-3xl p-6 sm:p-8 text-white shadow-xl overflow-hidden relative border border-white/10">
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

                        <div class="flex items-center gap-3 shrink-0">
                            <button id="btn-refresh-attempts"
                                class="cursor-pointer inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/25 active:scale-95 text-white font-medium text-sm transition-all shadow-md backdrop-blur-md border border-white/20">
                                <i class="fa-solid fa-arrows-rotate text-sm" id="icon-refresh"></i>
                                <span>Segarkan Data</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

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
            </section>

            <!-- ATTEMPTS TABLE CONTAINER -->
            <section class="bg-white rounded-3xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800">Daftar Sesi Ujian Siswa</h2>
                        <p class="text-xs sm:text-sm text-slate-500">Klik "Buka Kunci" untuk langsung membebaskan siswa yang terblokir.</p>
                    </div>
                    <div class="text-xs text-slate-500" id="table-info">Memuat data...</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Siswa & Kelas</th>
                                <th class="px-6 py-4">Assessment & Mapel</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Pelanggaran</th>
                                <th class="px-6 py-4">Waktu & Sisa Durasi</th>
                                <th class="px-6 py-4 text-center">Aksi Debugger</th>
                            </tr>
                        </thead>
                        <tbody id="attempts-table-body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
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

        </div>
    </div>

    <!-- MODAL EDIT ATTEMPT -->
    <dialog id="modal-edit-attempt" class="modal">
        <div class="modal-box bg-white max-w-lg p-0 rounded-3xl overflow-hidden shadow-2xl border border-slate-100">
            <!-- Modal Header -->
            <div class="bg-[linear-gradient(135deg,#0071BC,#004f84)] p-6 text-white flex items-start justify-between">
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

    <!-- CLIENT JAVASCRIPT LOGIC -->
    <script>
        let currentStatusFilter = 'all';
        let currentPage = 1;
        let searchTimeout = null;

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

        function loadAttempts(page = 1) {
            currentPage = page;
            const search = $('#filter-search').val();
            const schoolId = $('#filter-school').val();

            $('#attempts-table-body').html(`
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#0071BC]"></i>
                        <div>Memuat data attempt ujian...</div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ route('admin.assessmentDebug.data') }}",
                type: 'GET',
                data: {
                    page: page,
                    status: currentStatusFilter,
                    school_id: schoolId,
                    search: search
                },
                success: function (res) {
                    // Update KPI counters
                    if (res.summary) {
                        $('#stat-total').text(res.summary.total);
                        $('#stat-cheating').text(res.summary.cheating);
                        $('#stat-in-progress').text(res.summary.in_progress);
                        $('#stat-submitted').text(res.summary.submitted);
                        $('#stat-timeout').text(res.summary.timeout);
                    }

                    renderAttemptsTable(res.data, res.pagination);
                },
                error: function (xhr) {
                    $('#attempts-table-body').html(`
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-red-500">
                                <i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i>
                                <div>Gagal memuat data. Pastikan Anda memiliki akses administrator.</div>
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
                        <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-3 text-slate-300"></i>
                            <div class="font-semibold text-slate-600">Tidak ada sesi ujian yang ditemukan</div>
                            <div class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian atau filter status.</div>
                        </td>
                    </tr>
                `);
                $('#table-info').text('0 data');
                $('#pagination-container').addClass('hidden');
                return;
            }

            $('#pagination-container').removeClass('hidden');
            $('#table-info').text(`Menampilkan ${items.length} dari ${pagination.total} sesi`);

            const now = new Date();

            items.forEach(item => {
                const student = item.user_account?.student_profile;
                const studentName = student?.nama_lengkap || 'Tanpa Nama';
                const nis = student?.nis || student?.nisn || '-';
                const schoolName = student?.school_partner?.nama_sekolah || item.school_assessment?.school_partner?.nama_sekolah || '-';
                const className = item.user_account?.student_school_class?.[0]?.school_class?.class_name || item.school_assessment?.school_class?.class_name || '-';

                const assessmentTitle = item.school_assessment?.school_assessment_type?.name || 'Assessment';
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
                let switchBadgeClass = switchCount >= 3 ? 'bg-red-50 text-red-700 border-red-200 font-bold' : (switchCount > 0 ? 'bg-amber-50 text-amber-700 border-amber-200 font-semibold' : 'bg-slate-50 text-slate-600 border-slate-200');

                // Quick Unlock button only for blocked/cheating/timeout
                const isLocked = (st === 'cheating' || st === 'timeout');

                const row = `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">${escapeHtml(studentName)}</div>
                            <div class="text-xs text-slate-500 mt-0.5">NIS: ${escapeHtml(nis)} • Kelas: <span class="font-semibold text-slate-700">${escapeHtml(className)}</span></div>
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
                        <td class="px-6 py-4">
                            ${timeInfo}
                            <div class="text-[11px] text-slate-400 mt-0.5">Mulai: ${formatDateTime(item.start_time)}</div>
                            <div class="text-[11px] text-slate-400">Batas: ${formatDateTime(item.expire_time)}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                ${isLocked ? `
                                    <button type="button" onclick="quickUnlock(${item.id}, '${escapeHtml(studentName)}', ${duration})"
                                        class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition active:scale-95" title="Buka Kunci Ujian">
                                        <i class="fa-solid fa-lock-open text-xs"></i>
                                        <span>Buka Kunci</span>
                                    </button>
                                ` : ''}

                                <button type="button" onclick='openEditModal(${JSON.stringify(item)})'
                                    class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#0071BC] hover:text-white text-slate-700 font-semibold text-xs transition active:scale-95" title="Edit Status & Pelanggaran">
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

            renderPagination(pagination);
        }

        function renderPagination(pagination) {
            $('#pagination-text').text(`Halaman ${pagination.current_page} dari ${pagination.last_page} (Total ${pagination.total} data)`);
            const btnContainer = $('#pagination-buttons');
            btnContainer.empty();

            if (pagination.last_page <= 1) return;

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
        function openEditModal(item) {
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

        // Trigger manual answer reset
        function triggerResetAnswers(type) {
            const id = $('#edit-attempt-id').val();
            if (!id) return;

            const isDeleteAll = type === 'delete_all';
            const label = isDeleteAll 
                ? 'Hapus SEMUA jawaban siswa untuk ujian ini? Siswa harus mengerjakan ulang dari nomor 1.' 
                : 'Bersihkan semua jawaban kosong / auto-submit? Soal-soal yang belum diisi akan dapat dikerjakan kembali oleh siswa.';

            Swal.fire({
                title: isDeleteAll ? 'Hapus Seluruh Jawaban?' : 'Bersihkan Soal Kosong?',
                text: label,
                icon: isDeleteAll ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: isDeleteAll ? 'Ya, Hapus Semua' : 'Ya, Bersihkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: isDeleteAll ? '#EF4444' : '#0071BC'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/administrator/assessment-debug/${id}/reset-answers`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            reset_type: type
                        },
                        success: function (res) {
                            Swal.fire('Berhasil', res.message, 'success');
                            loadAttempts(currentPage);
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memproses jawaban.', 'error');
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

        // Filter Actions
        $('.status-filter-btn').on('click', function () {
            $('.status-filter-btn').removeClass('bg-white text-[#0071BC] font-bold shadow-xs').addClass('text-slate-600 font-semibold');
            $(this).addClass('bg-white text-[#0071BC] font-bold shadow-xs').removeClass('text-slate-600');
            currentStatusFilter = $(this).data('status');
            loadAttempts(1);
        });

        $('#filter-school').on('change', function () {
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
