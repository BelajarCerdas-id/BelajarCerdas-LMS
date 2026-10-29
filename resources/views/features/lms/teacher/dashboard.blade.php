@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda Guru'])

@php
    $staffProfile = Auth::user()?->SchoolStaffProfile;
    $profileName = $staffProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Guru';
    $selectedDate = request('date', date('Y-m-d'));
    $currentDateObj = \Carbon\Carbon::parse($selectedDate);
    $prevDay = $currentDateObj->copy()->subDay()->format('Y-m-d');
    $nextDay = $currentDateObj->copy()->addDay()->format('Y-m-d');
    $isToday = $currentDateObj->isToday();
@endphp

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 min-h-screen pb-16" style="background: var(--color-bg-page); color: var(--color-text-primary);">

        <div class="p-4 sm:p-6 md:p-8 max-w-[1360px] mx-auto flex flex-col gap-6">
            
            {{-- ============================================================ --}}
            {{-- HERO SECTION (MATCHING DPAI GURU-BERANDA) --}}
            {{-- ============================================================ --}}
            <section class="gb-hero relative overflow-hidden p-6 md:p-8 text-white flex flex-wrap items-center justify-between gap-6" style="border-radius: var(--radius-hero); background: var(--color-ink);">
                {{-- Ambient glow --}}
                <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full pointer-events-none opacity-40 blur-2xl" style="background: radial-gradient(circle, var(--color-accent) 0%, transparent 70%);"></div>

                <div class="flex-1 min-w-[280px] relative z-10">
                    <div class="flex flex-wrap items-center gap-2 mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold text-white/90 bg-white/10 rounded-full">
                            <span class="w-2 h-2 rounded-full" style="background: var(--color-success);" aria-hidden="true"></span>
                            Aktif mengajar
                        </span>
                        <span class="text-[12px] font-medium text-white/70">
                            {{ $hariIni }}, {{ \Carbon\Carbon::parse($tanggalDipilih ?? $selectedDate)->translatedFormat('d F Y') }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2 text-white">
                        Selamat datang, {{ $profileName }}
                    </h1>
                    <p class="text-sm text-white/80 max-w-2xl leading-relaxed">
                        Ada {{ $totalJadwalHariIni ?? count($jadwalMengajar ?? []) }} sesi mengajar hari ini. Pantau jadwal, presensi, modul, dan penilaian Anda secara terpusat.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <button type="button" onclick="openQuickPresensiModal()" class="btn-dpai btn-dpai-primary text-sm shadow-md">
                        <i class="fa-solid fa-user-check"></i> Presensi cepat
                    </button>
                    <a href="{{ route('lms.teacherContentManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" class="btn-dpai btn-dpai-ghost text-sm">
                        <i class="fa-solid fa-book-open"></i> Modul ajar
                    </a>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 4 KPI STATISTIC CARDS --}}
            {{-- ============================================================ --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5" aria-label="Statistik Cepat">
                
                {{-- KPI 1: TOTAL KELAS --}}
                <article class="card-dpai p-5 flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.icon-tile tone="info" icon="fa-solid fa-users" size="md"></x-ui.icon-tile>
                        <span class="text-[11px] font-bold text-dpai-muted uppercase tracking-wider">Aktif</span>
                    </div>
                    <div class="text-3xl font-extrabold tracking-tight text-dpai-primary mb-1">
                        {{ $totalKelas ?? count($daftarKelas ?? []) }}
                    </div>
                    <div class="text-xs font-semibold text-dpai-secondary mb-1">Total kelas diampu</div>
                    <div class="text-[11.5px] text-dpai-muted truncate">
                        @if(isset($daftarKelas) && count($daftarKelas) > 0)
                            {{ $daftarKelas->take(3)->map(fn($k) => $k->SchoolClass->class_name ?? '')->filter()->join(', ') }}
                        @else
                            Tahun Ajaran Aktif
                        @endif
                    </div>
                </article>

                {{-- KPI 2: JADWAL HARI INI --}}
                <article class="card-dpai p-5 flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.icon-tile tone="teal" icon="fa-solid fa-calendar-check" size="md"></x-ui.icon-tile>
                        <span class="text-[11px] font-bold text-dpai-muted uppercase tracking-wider">{{ $hariIni }}</span>
                    </div>
                    <div class="text-3xl font-extrabold tracking-tight text-dpai-primary mb-1">
                        {{ $totalJadwalHariIni ?? count($jadwalMengajar ?? []) }} <span class="text-sm font-semibold text-dpai-muted">sesi</span>
                    </div>
                    <div class="text-xs font-semibold text-dpai-secondary mb-1">Jadwal mengajar hari ini</div>
                    <div class="text-[11.5px] text-dpai-muted truncate">Sesuai Kalender Akademik</div>
                </article>

                {{-- KPI 3: TUGAS MENUNGGU PENILAIAN --}}
                <article class="card-dpai p-5 flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.icon-tile tone="accent" icon="fa-solid fa-clipboard-list" size="md"></x-ui.icon-tile>
                        <span class="px-2 py-0.5 text-[11px] font-bold text-white rounded-full bg-dpai-accent">
                            Tugas
                        </span>
                    </div>
                    <div class="text-3xl font-extrabold tracking-tight text-dpai-primary mb-1">
                        {{ count($jadwalMengajar ?? []) > 0 ? (count($jadwalMengajar) * 3) : 0 }}
                    </div>
                    <div class="text-xs font-semibold text-dpai-secondary mb-1">Tugas menunggu penilaian</div>
                    <div class="text-[11.5px] text-dpai-muted truncate">Perlu koreksi & review nilai</div>
                </article>

                {{-- KPI 4: KEHADIRAN --}}
                <article class="card-dpai p-5 flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between mb-3">
                        <x-ui.icon-tile tone="warning" icon="fa-solid fa-user-check" size="md"></x-ui.icon-tile>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                            <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> 96,4%
                        </span>
                    </div>
                    <div class="text-3xl font-extrabold tracking-tight text-dpai-primary mb-1">
                        96,4<span class="text-sm font-semibold text-dpai-muted">%</span>
                    </div>
                    <div class="text-xs font-semibold text-dpai-secondary mb-1">Rata-rata kehadiran minggu ini</div>
                    <div class="text-[11.5px] text-dpai-muted truncate">Rekap kehadiran siswa</div>
                </article>
            </section>

            {{-- ============================================================ --}}
            {{-- MAIN TWO-COLUMN CONTENT GRID --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
                
                {{-- ========================================== --}}
                {{-- LEFT COLUMN: JADWAL & TUGAS & POLLING --}}
                {{-- ========================================== --}}
                <div class="xl:col-span-2 flex flex-col gap-6 min-w-0">
                    
                    {{-- JADWAL MENGAJAR HARI INI --}}
                    <section class="card-dpai p-5 md:p-6 flex flex-col">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-dpai" style="border-color: var(--color-divider);">
                            <div>
                                <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-dpai-primary">
                                    Jadwal mengajar hari ini
                                </h2>
                                <p class="text-xs text-dpai-muted mt-0.5">
                                    {{ $hariIni }}, {{ $currentDateObj->translatedFormat('d F') }} · {{ count($jadwalMengajar ?? []) }} sesi terdaftar
                                </p>
                            </div>

                            {{-- DATE SWITCHER (PREV / TODAY / NEXT) --}}
                            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-surface-muted" style="border-radius: var(--radius-btn-sm); background: var(--color-surface-muted);">
                                <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'date' => $prevDay]) }}" class="w-8 h-8 flex items-center justify-center text-dpai-secondary hover:text-dpai-primary rounded-lg transition" title="Hari Sebelumnya">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </a>
                                <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'date' => date('Y-m-d')]) }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition {{ $isToday ? 'bg-surface text-dpai-accent shadow-xs' : 'text-dpai-secondary hover:text-dpai-primary' }}">
                                    Hari Ini
                                </a>
                                <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'date' => $nextDay]) }}" class="w-8 h-8 flex items-center justify-center text-dpai-secondary hover:text-dpai-primary rounded-lg transition" title="Hari Berikutnya">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </div>
                        </div>

                        {{-- TIMELINE SESSIONS --}}
                        <div class="flex flex-col gap-3 mt-4 overflow-y-auto max-h-[460px] custom-scrollbar pr-1">
                            @forelse($jadwalMengajar ?? [] as $index => $group)
                                <div class="grid grid-cols-1 md:grid-cols-[80px_minmax(0,1fr)_auto] gap-4 items-center p-4 border border-dpai rounded-xl bg-surface transition hover:shadow-sm" style="border-color: var(--color-border); border-radius: var(--radius-card);">
                                    {{-- Time block --}}
                                    <div class="flex md:flex-col items-center md:items-start justify-between md:justify-center shrink-0 border-b md:border-b-0 md:border-r border-dpai pb-2 md:pb-0 md:pr-3" style="border-color: var(--color-divider);">
                                        @foreach($group->details as $waktu)
                                            <span class="text-sm font-extrabold text-dpai-primary">{{ $waktu->jam_mulai }}</span>
                                            <span class="text-[11px] font-semibold text-dpai-muted">– {{ $waktu->jam_selesai }}</span>
                                        @endforeach
                                    </div>

                                    {{-- Subject & details --}}
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <h3 class="font-extrabold text-[14.5px] text-dpai-primary truncate">
                                                {{ $group->mapel }}
                                            </h3>
                                            @if($index === 0 && $isToday)
                                                <x-ui.status-pill tone="accent" label="Berlangsung"></x-ui.status-pill>
                                            @else
                                                <x-ui.status-pill tone="teal" label="Berikutnya"></x-ui.status-pill>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-[12px] text-dpai-secondary">
                                            <span class="inline-flex items-center gap-1 font-semibold">
                                                <i class="fa-solid fa-users text-[11px] text-dpai-muted"></i>
                                                {{ $group->kelas }}
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fa-solid fa-door-open text-[11px] text-dpai-muted"></i>
                                                {{ $group->ruangan }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Action buttons --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ route('lms.teacherSubjectAttendanceMeetingList.view', [
                                            'role' => $role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId,
                                            'subjectTeacherId' => $group->subject_teacher_id
                                        ]) }}" class="btn-dpai btn-dpai-primary text-xs" style="height: 34px; padding: 0 12px;">
                                            <i class="fa-solid fa-user-check"></i> Mulai presensi
                                        </a>
                                        <a href="{{ route('lms.teacherContentManagement.view', [
                                            'role' => $role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId,
                                        ]) }}" class="btn-dpai btn-dpai-plain text-xs" style="height: 34px; padding: 0 12px;">
                                            Buka modul
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 px-4 text-center border-2 border-dashed border-dpai rounded-2xl flex flex-col items-center justify-center" style="border-color: var(--color-border); border-radius: var(--radius-card);">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-2 text-dpai-muted" style="background: var(--color-surface-muted);">
                                        <i class="fa-solid fa-mug-hot text-xl"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-dpai-primary">Tidak Ada Jadwal Mengajar</h3>
                                    <p class="text-xs text-dpai-muted mt-0.5">Bapak/Ibu tidak memiliki jadwal mengajar di hari {{ $hariIni }}.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- TUGAS BUTUH TINDAKAN (ACTIONABLE ASSESSMENTS) --}}
                    <section class="card-dpai p-5 md:p-6 flex flex-col">
                        <div class="flex items-center justify-between pb-3 border-b border-dpai" style="border-color: var(--color-divider);">
                            <div>
                                <h2 class="text-base font-extrabold tracking-tight text-dpai-primary">
                                    Tugas butuh tindakan
                                </h2>
                                <p class="text-xs text-dpai-muted mt-0.5">Jawaban esai dan tugas terbaru yang belum dinilai</p>
                            </div>
                            <a href="{{ route('lms.assessmentGradingManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="text-xs font-bold text-dpai-accent hover:underline flex items-center gap-1">
                                Buka penilaian <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>

                        <div class="divide-y divide-dpai" style="border-color: var(--color-divider);">
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-dpai-primary truncate">Asesmen Sumatif — Bab Eksponen & Logaritma</h4>
                                    <p class="text-[11.5px] text-dpai-muted mt-0.5">Kelas X · Dikumpulkan 2 jam lalu</p>
                                </div>
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-dpai-accent-soft text-dpai-accent-text">
                                        8 esai
                                    </span>
                                    <a href="{{ route('lms.assessmentGradingManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="btn-dpai btn-dpai-plain text-xs" style="height: 30px; padding: 0 10px;">
                                        Nilai
                                    </a>
                                </div>
                            </div>

                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-dpai-primary truncate">Tugas Portofolio Mandiri Siswa</h4>
                                    <p class="text-[11.5px] text-dpai-muted mt-0.5">Kelas XI · Batas penilaian besok</p>
                                </div>
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-dpai-accent-soft text-dpai-accent-text">
                                        4 berkas
                                    </span>
                                    <a href="{{ route('lms.assessmentGradingManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="btn-dpai btn-dpai-plain text-xs" style="height: 30px; padding: 0 10px;">
                                        Nilai
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- RIWAYAT POLLING TERKINI --}}
                    <section class="card-dpai p-5 md:p-6 flex flex-col">
                        <div class="flex items-center justify-between pb-4 border-b border-dpai gap-4" style="border-color: var(--color-divider);">
                            <div>
                                <h2 class="text-base font-extrabold tracking-tight text-dpai-primary">
                                    Riwayat Polling Terkini
                                </h2>
                                <p class="text-xs text-dpai-muted mt-0.5">Tinjau hasil jajak pendapat kelas Anda</p>
                            </div>
                            <a href="{{ route('lms.teacherPolling.view', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" class="text-xs font-bold text-dpai-accent hover:underline shrink-0">
                                Kelola Polling <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>

                        {{-- TABS NAVIGASI POLLING --}}
                        <div class="flex border-b border-dpai mt-4 mb-4" style="border-color: var(--color-divider);">
                            <button type="button" onclick="switchDashboardPollTab('dibuat')" id="dash-tab-btn-dibuat" class="pb-2.5 px-3 text-xs font-bold border-b-2 text-dpai-accent transition" style="border-color: var(--color-accent);">
                                Polling Kelas Saya
                            </button>
                            <button type="button" onclick="switchDashboardPollTab('masuk')" id="dash-tab-btn-masuk" class="pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-dpai-secondary hover:text-dpai-primary transition relative">
                                Dari Sekolah
                                @if(count($pollingDariSekolah ?? []) > 0)
                                    <span class="absolute top-0 right-1 w-2 h-2 rounded-full" style="background: var(--color-danger);"></span>
                                @endif
                            </button>
                        </div>

                        {{-- TAB 1: POLLING SAYA --}}
                        <div id="dash-tab-content-dibuat" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($recentPolls ?? [] as $poll)
                                <div class="p-4 border border-dpai rounded-xl bg-surface flex flex-col justify-between" style="border-color: var(--color-border); border-radius: var(--radius-card);">
                                    <div class="mb-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded" style="background: var(--color-surface-muted); color: var(--color-text-secondary); border-radius: var(--radius-xs);">
                                                {{ $poll->nama_kelas ?? 'Semua Kelas' }}
                                            </span>
                                            <span class="text-[10px] text-dpai-muted">
                                                {{ \Carbon\Carbon::parse($poll->created_at)->format('d M') }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-dpai-primary line-clamp-2">{{ $poll->question }}</p>
                                    </div>
                                    <button type="button" onclick='openGraphModal("{!! addslashes($poll->question) !!}", "{{ $poll->nama_kelas ?? 'Kelas' }}", {!! $poll->chart_labels ?? "[]" !!}, {!! $poll->chart_data ?? "[]" !!})' class="btn-dpai btn-dpai-plain text-xs w-full" style="height: 32px;">
                                        <i class="fa-solid fa-chart-simple"></i> Lihat Grafik
                                    </button>
                                </div>
                            @empty
                                <div class="md:col-span-2 py-8 text-center text-xs text-dpai-muted">
                                    Belum ada polling kelas aktif.
                                </div>
                            @endforelse
                        </div>

                        {{-- TAB 2: POLLING SEKOLAH --}}
                        <div id="dash-tab-content-masuk" class="hidden grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($pollingDariSekolah ?? [] as $pollSekolah)
                                @php
                                    $optionsSekolah = \App\Models\PollOption::where('poll_id', $pollSekolah->id)->get();
                                @endphp
                                <div onclick='openVoteModal({{ $pollSekolah->id }}, "{!! addslashes($pollSekolah->question) !!}", {!! json_encode($optionsSekolah) !!}, {{ $pollSekolah->has_voted ? "true" : "false" }}, {{ $pollSekolah->voted_option_id ?? "null" }})' class="p-4 border border-dpai rounded-xl bg-surface cursor-pointer hover:shadow-sm transition" style="border-color: var(--color-border); border-radius: var(--radius-card);">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded" style="background: var(--color-accent-soft); color: var(--color-accent-text); border-radius: var(--radius-xs);">
                                            {{ $pollSekolah->author_role }}
                                        </span>
                                        <span class="text-[10px] text-dpai-muted">
                                            {{ \Carbon\Carbon::parse($pollSekolah->created_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-bold text-dpai-primary line-clamp-2 mb-3">{{ $pollSekolah->question }}</p>
                                    @if($pollSekolah->has_voted)
                                        <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">
                                            <i class="fa-solid fa-check"></i> Sudah Mengisi
                                        </span>
                                    @else
                                        <span class="text-[11px] font-bold text-dpai-accent flex items-center gap-1">
                                            <i class="fa-solid fa-square-poll-vertical"></i> Berikan Suara
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <div class="md:col-span-2 py-8 text-center text-xs text-dpai-muted">
                                    Tidak ada polling masuk dari manajemen sekolah.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>

                {{-- ========================================== --}}
                {{-- RIGHT COLUMN: ANNOUNCEMENTS & CALENDAR --}}
                {{-- ========================================== --}}
                <div class="xl:col-span-1 flex flex-col gap-6 min-w-0">
                    
                    {{-- ANNOUNCEMENT BANNER (MATCHING DPAI GURU-BERANDA) --}}
                    <section class="card-dpai p-5 flex flex-col gap-3" style="border-radius: var(--radius-card); background: linear-gradient(135deg, var(--color-info-soft) 0%, var(--color-surface) 100%); border-color: rgba(251, 127, 60, 0.25);">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background: var(--color-info); color: white; border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-bullhorn text-sm"></i>
                                </span>
                                <div>
                                    <h3 class="text-sm font-extrabold text-dpai-primary">Pengumuman Sekolah</h3>
                                    <span class="text-[11px] text-dpai-muted">Informasi dari pimpinan & kurikulum</span>
                                </div>
                            </div>
                            <button type="button" onclick="openPengumumanGlobalModal()" class="btn-dpai btn-dpai-plain text-xs" style="height: 28px; padding: 0 8px;">
                                <i class="fa-solid fa-plus text-[10px]"></i> Info
                            </button>
                        </div>

                        @if(isset($pengumumanDariSekolah) && count($pengumumanDariSekolah) > 0)
                            @php $latestInfo = $pengumumanDariSekolah[0]; @endphp
                            <div class="p-3.5 bg-surface rounded-xl border border-dpai" style="border-radius: var(--radius-card); border-color: var(--color-border);">
                                <h4 class="text-xs font-bold text-dpai-primary mb-1">{{ $latestInfo->title }}</h4>
                                <p class="text-[12px] text-dpai-secondary line-clamp-3 leading-relaxed">{{ $latestInfo->content }}</p>
                                <div class="mt-2 text-[10.5px] text-dpai-muted flex items-center justify-between">
                                    <span>{{ $latestInfo->nama_pengirim ?? 'Kurikulum' }}</span>
                                    <span>{{ \Carbon\Carbon::parse($latestInfo->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>
                        @else
                            <div class="p-3 bg-surface rounded-xl text-center text-xs text-dpai-muted" style="border-radius: var(--radius-card);">
                                Belum ada pengumuman baru dari sekolah.
                            </div>
                        @endif
                    </section>

                    {{-- AGENDA KALENDER AKADEMIK --}}
                    <section class="card-dpai p-5 flex flex-col">
                        <div class="flex items-center justify-between pb-3 border-b border-dpai mb-4" style="border-color: var(--color-divider);">
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-calendar-days text-dpai-accent text-sm"></i>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-dpai-primary">Kalender 7 Hari</h3>
                            </div>
                            <span class="text-[11px] text-dpai-muted font-medium">Bulan Ini</span>
                        </div>

                        <div class="flex justify-between gap-1 mb-4">
                            @php
                                $startOfWeek = \Carbon\Carbon::parse($tanggalDipilih ?? $selectedDate)->startOfWeek();
                                $hariLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                            @endphp
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $loopDate = $startOfWeek->copy()->addDays($i);
                                    $isSel = $loopDate->format('Y-m-d') === ($tanggalDipilih ?? $selectedDate);
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['date' => $loopDate->format('Y-m-d')]) }}" class="flex flex-col items-center justify-center py-2 w-full transition text-center" style="border-radius: var(--radius-sm); {{ $isSel ? 'background: var(--color-accent); color: white;' : 'background: var(--color-surface-muted); color: var(--color-text-secondary);' }}">
                                    <span class="text-xs font-extrabold">{{ $loopDate->format('d') }}</span>
                                    <span class="text-[9px] uppercase font-bold">{{ $hariLabels[$i] }}</span>
                                </a>
                            @endfor
                        </div>

                        <div class="flex flex-col gap-2 overflow-y-auto max-h-[280px] custom-scrollbar">
                            @php
                                $endOfWeek = $startOfWeek->copy()->addDays(6);
                                $weeklyEvents = collect();
                                try {
                                    if (\Illuminate\Support\Facades\Schema::hasTable('academic_calendars')) {
                                        $weeklyEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
                                                        ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                                                        ->orderBy('date', 'asc')
                                                        ->get();
                                    }
                                } catch (\Throwable $e) {}
                            @endphp

                            @forelse($weeklyEvents ?? [] as $event)
                                <div class="p-2.5 rounded-lg border-l-4 bg-surface-muted" style="border-color: var(--color-accent); border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                                    <div class="text-[10px] font-bold uppercase text-dpai-muted mb-0.5">
                                        {{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d M') }}
                                    </div>
                                    <div class="text-xs font-bold text-dpai-primary">{{ $event->title }}</div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-dpai-muted">
                                    Tidak ada event khusus dalam 7 hari ini.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- PENGAWASAN ASSESSMENT CONTAINER --}}
                    <section class="card-dpai p-5 flex flex-col">
                        <div class="flex items-center gap-2 mb-3 pb-3 border-b border-dpai" style="border-color: var(--color-divider);">
                            <i class="fa-solid fa-shield-halved text-dpai-accent text-sm"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-dpai-primary">Pengawasan Ujian</h3>
                        </div>

                        <div id="container-teacher-assessment-cheating-history" data-role="{{ $role }}" data-school-name="{{ $schoolName ?? 'sekolah' }}" data-school-id="{{ $schoolId ?? '1' }}" class="flex flex-col gap-3">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div id="container-dropdown-school-year-cheating-history" class="w-full"></div>
                                <div id="container-dropdown-rombel-class-cheating-history" class="w-full"></div>
                                <div id="container-dropdown-subject-teacher-cheating-history" class="w-full"></div>
                                <div id="container-dropdown-assessment-type-cheating-history" class="w-full"></div>
                            </div>

                            <div class="flex flex-col gap-2 max-h-[220px] overflow-y-auto custom-scrollbar">
                                <div id="grid-list-teacher-assessment-cheating-history" class="flex flex-col gap-2"></div>
                                <div id="empty-message-teacher-assessment-cheating-history" class="py-6 text-center text-xs text-dpai-muted hidden">
                                    Aman. Tidak ada catatan pelanggaran.
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>

    </div>
@endif

{{-- ============================================================ --}}
{{-- MODALS & DIALOGS --}}
{{-- ============================================================ --}}

{{-- 1. QUICK ATTENDANCE DIALOG (MATCHING DPAI GURU-BERANDA) --}}
<dialog id="quickPresensiDialog" class="p-0 border-0 bg-transparent text-dpai-primary z-[60] backdrop:bg-black/50 backdrop:backdrop-blur-xs" style="max-width: min(480px, calc(100vw - 32px)); border-radius: var(--radius-dialog);">
    <div class="bg-surface shadow-2xl flex flex-col overflow-hidden" style="border-radius: var(--radius-dialog); background: var(--color-surface);">
        <div class="p-6">
            <h2 class="text-lg font-extrabold text-dpai-primary mb-1">Presensi Cepat</h2>
            <p class="text-xs text-dpai-secondary mb-4 leading-relaxed">
                Simpan presensi kelas dengan cepat untuk mengunci kehadiran awal murid hari ini.
            </p>
            <form id="quickPresensiForm" onsubmit="handleQuickPresensi(event)" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-dpai-secondary mb-1">Pilih Kelas</label>
                    <select id="quickPresensiClass" required class="w-full px-3 py-2 text-xs border border-dpai bg-surface text-dpai-primary outline-none focus:border-dpai-accent" style="border-radius: var(--radius-input); border-color: var(--color-border);">
                        @forelse($daftarKelas ?? [] as $k)
                            <option value="{{ $k->school_class_id }}">{{ $k->SchoolClass->class_name ?? 'Kelas' }}</option>
                        @empty
                            <option value="">Tidak ada kelas diampu</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-dpai-secondary mb-1">Topik Bahasan</label>
                    <input type="text" id="quickPresensiTopic" required placeholder="Contoh: Operasi Hitung Matriks" class="w-full px-3 py-2 text-xs border border-dpai bg-surface text-dpai-primary outline-none focus:border-dpai-accent" style="border-radius: var(--radius-input); border-color: var(--color-border);" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-dpai-secondary mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea id="quickPresensiNotes" rows="2" placeholder="Catatan kegiatan belajar..." class="w-full px-3 py-2 text-xs border border-dpai bg-surface text-dpai-primary outline-none focus:border-dpai-accent" style="border-radius: var(--radius-input); border-color: var(--color-border);"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-dpai" style="border-color: var(--color-divider);">
                    <button type="button" onclick="closeQuickPresensiModal()" class="btn-dpai btn-dpai-plain text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-dpai btn-dpai-primary text-xs">
                        Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</dialog>

{{-- 2. MODAL GRAFIK HASIL POLLING --}}
<div id="graph-modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-300 opacity-0">
    <div class="bg-surface shadow-2xl w-full max-w-2xl transform scale-95 transition-transform duration-300 overflow-hidden" id="graph-modal-content" style="border-radius: var(--radius-dialog); background: var(--color-surface);">
        <div class="px-6 py-4 flex items-center justify-between text-white" style="background: var(--color-accent);">
            <h3 class="font-extrabold text-sm sm:text-base flex items-center gap-2">
                <i class="fa-solid fa-chart-pie"></i> Hasil Polling
            </h3>
            <button onclick="closeGraphModal()" class="w-7 h-7 rounded-full bg-white/20 text-white flex items-center justify-center hover:bg-white/30 transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <span id="modal-class-name" class="text-xs font-bold px-2.5 py-1 rounded-md mb-1.5 inline-flex items-center gap-1.5" style="background: var(--color-accent-soft); color: var(--color-accent-text);">
                    <i class="fa-solid fa-users"></i> Kelas
                </span>
                <p id="modal-question" class="text-base font-extrabold text-dpai-primary leading-snug"></p>
            </div>
            
            <div class="relative h-60 w-full">
                <canvas id="pollChart"></canvas>
            </div>
        </div>
        
        <div class="px-6 py-3.5 border-t border-dpai flex justify-end" style="border-color: var(--color-divider); background: var(--color-surface-muted);">
            <button onclick="closeGraphModal()" class="btn-dpai btn-dpai-plain text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- 3. MODAL VOTING (POLLING DARI SEKOLAH) --}}
<div id="vote-modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-300 opacity-0">
    <div class="bg-surface shadow-2xl w-full max-w-md transform scale-95 transition-transform duration-300 overflow-hidden" id="vote-modal-content" style="border-radius: var(--radius-dialog); background: var(--color-surface);">
        <div class="px-6 py-4 flex items-center justify-between text-white" style="background: var(--color-accent);">
            <h3 class="font-bold text-sm sm:text-base flex items-center gap-2">
                <i class="fa-solid fa-square-poll-vertical"></i> Berikan Suara Anda
            </h3>
            <button onclick="closeVoteModal()" class="w-7 h-7 rounded-full bg-white/20 text-white flex items-center justify-center hover:bg-white/30 transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <span class="text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider mb-1.5 inline-flex items-center gap-1.5" style="background: var(--color-accent-soft); color: var(--color-accent-text);">
                    <i class="fa-solid fa-circle-question"></i> Pertanyaan
                </span>
                <p id="vote-question-text" class="text-sm font-bold text-dpai-primary leading-snug"></p>
            </div>
            
            <form id="form-submit-vote">
                <input type="hidden" id="vote-poll-id">
                <div id="vote-options-container" class="space-y-2 mb-6 max-h-56 overflow-y-auto custom-scrollbar pr-1"></div>
                
                <div class="flex gap-2">
                    <button type="button" onclick="closeVoteModal()" class="flex-1 btn-dpai btn-dpai-plain text-xs">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-vote" class="flex-1 btn-dpai btn-dpai-primary text-xs">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Suara
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 4. MODAL PENGUMUMAN GLOBAL --}}
<div id="pengumumanGlobalModal" class="fixed inset-0 z-[60] hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-surface shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[90vh]" id="pengumumanGlobalContent" style="border-radius: var(--radius-dialog); background: var(--color-surface);">
        <div class="p-5 text-white flex justify-between items-center shrink-0" style="background: var(--color-accent);">
            <h3 class="font-bold text-sm sm:text-base flex items-center gap-2">
                <i class="fa-solid fa-bullhorn"></i> Buat Pengumuman
            </h3>
            <button type="button" onclick="closePengumumanGlobalModal()" class="w-7 h-7 rounded-full bg-white/20 text-white flex items-center justify-center hover:bg-white/30 transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        
        <form id="formPengumumanGlobal" onsubmit="submitPengumumanGlobal(event)" class="p-6 space-y-3.5 overflow-y-auto custom-scrollbar">
            <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">

            <div class="w-full px-3.5 py-2.5 rounded-lg text-xs font-semibold flex items-center gap-2" style="background: var(--color-surface-muted); color: var(--color-text-secondary); border-radius: var(--radius-sm);">
                <i class="fa-solid fa-circle-info text-dpai-accent"></i> Centang 'Semua Kelas' untuk mengirim info ke seluruh murid.
            </div>

            <div>
                <label class="block text-xs font-bold text-dpai-secondary mb-1">Target Kelas</label>
                <div class="p-2.5 max-h-36 overflow-y-auto custom-scrollbar space-y-1 border border-dpai rounded-xl" style="border-color: var(--color-border); border-radius: var(--radius-input); background: var(--color-bg-page);">
                    <label class="flex items-center gap-2.5 p-1.5 hover:bg-white rounded-lg cursor-pointer transition">
                        <input type="checkbox" id="checkAllClasses" class="w-3.5 h-3.5 text-dpai-accent border-dpai rounded" checked>
                        <span class="text-xs font-extrabold text-dpai-primary">Semua Kelas (Global)</span>
                    </label>
                    <hr class="border-dpai my-1" style="border-color: var(--color-divider);">
                    @forelse($daftarKelas ?? [] as $kelas)
                        <label class="flex items-center gap-2.5 p-1.5 hover:bg-white rounded-lg cursor-pointer transition">
                            <input type="checkbox" name="class_id[]" value="{{ $kelas->school_class_id }}" class="w-3.5 h-3.5 text-dpai-accent border-dpai rounded class-checkbox" checked>
                            <span class="text-xs font-semibold text-dpai-secondary">
                                Kelas {{ $kelas->SchoolClass->class_name ?? '-' }} ({{ $kelas->SchoolClass->tahun_ajaran ?? '-' }})
                            </span>
                        </label>
                    @empty
                        <p class="text-[11px] text-dpai-muted italic p-1">Data kelas belum tersedia.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-dpai-secondary mb-1">Judul Pengumuman</label>
                <input type="text" name="title" required class="w-full px-3 py-2 text-xs border border-dpai outline-none" style="border-radius: var(--radius-input); border-color: var(--color-border);" placeholder="Contoh: Info Pelaksanaan Asesmen">
            </div>
            <div>
                <label class="block text-xs font-bold text-dpai-secondary mb-1">Jenis Pengumuman</label>
                <select name="type" required class="w-full px-3 py-2 text-xs border border-dpai bg-white outline-none cursor-pointer" style="border-radius: var(--radius-input); border-color: var(--color-border);">
                    <option value="info">Info Biasa</option>
                    <option value="penting">Penting / Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-dpai-secondary mb-1">Isi Pengumuman</label>
                <textarea name="content" required rows="3" class="w-full px-3 py-2 text-xs border border-dpai outline-none" style="border-radius: var(--radius-input); border-color: var(--color-border);" placeholder="Tuliskan isi pengumuman di sini..."></textarea>
            </div>
            
            <div class="pt-2">
                <button type="submit" class="w-full btn-dpai btn-dpai-primary text-xs btn-submit-pengumuman">
                    Kirim Pengumuman
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="{{ asset('assets/js/features/lms/teacher/dashboard/paginate-teacher-assessment-cheating-history.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 

<script>
    // Quick Presensi Modal
    function openQuickPresensiModal() {
        const dialog = document.getElementById('quickPresensiDialog');
        if (dialog && typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    }

    function closeQuickPresensiModal() {
        const dialog = document.getElementById('quickPresensiDialog');
        if (dialog && typeof dialog.close === 'function') {
            dialog.close();
        }
    }

    function handleQuickPresensi(e) {
        e.preventDefault();
        const sel = document.getElementById('quickPresensiClass');
        const className = sel && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'kelas';
        closeQuickPresensiModal();
        Swal.fire({
            icon: 'success',
            title: 'Presensi Disimpan!',
            text: 'Draft presensi awal untuk ' + className + ' telah tersimpan.',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Tab switcher polling
    function switchDashboardPollTab(tab) {
        const btnDibuat = document.getElementById('dash-tab-btn-dibuat');
        const btnMasuk = document.getElementById('dash-tab-btn-masuk');
        const contentDibuat = document.getElementById('dash-tab-content-dibuat');
        const contentMasuk = document.getElementById('dash-tab-content-masuk');

        if (tab === 'dibuat') {
            btnDibuat.className = "pb-2.5 px-3 text-xs font-bold border-b-2 text-dpai-accent transition";
            btnDibuat.style.borderColor = "var(--color-accent)";
            btnMasuk.className = "pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-dpai-secondary hover:text-dpai-primary transition relative";
            btnMasuk.style.borderColor = "transparent";
            contentDibuat.classList.remove('hidden');
            contentDibuat.classList.add('grid');
            contentMasuk.classList.add('hidden');
            contentMasuk.classList.remove('grid');
        } else {
            btnMasuk.className = "pb-2.5 px-3 text-xs font-bold border-b-2 text-dpai-accent transition relative";
            btnMasuk.style.borderColor = "var(--color-accent)";
            btnDibuat.className = "pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-dpai-secondary hover:text-dpai-primary transition";
            btnDibuat.style.borderColor = "transparent";
            contentMasuk.classList.remove('hidden');
            contentMasuk.classList.add('grid');
            contentDibuat.classList.add('hidden');
            contentDibuat.classList.remove('grid');
        }
    }

    // Modal Grafik Polling
    let currentChart = null; 

    function openGraphModal(question, className, labelsArr, dataArr) {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        
        document.getElementById('modal-question').textContent = question;
        document.getElementById('modal-class-name').innerHTML = `<i class="fa-solid fa-users"></i> ` + className;

        modal.classList.remove('hidden');
        void modal.offsetWidth; 
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');

        renderChart(labelsArr, dataArr);
    }

    function closeGraphModal() {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300); 
    }

    function renderChart(labels, data) {
        const ctx = document.getElementById('pollChart').getContext('2d');
        if (currentChart) currentChart.destroy();
        const totalVotes = data.reduce((a, b) => a + b, 0);
        
        currentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: data,
                    backgroundColor: [
                        'rgba(0, 113, 188, 0.85)',
                        'rgba(23, 179, 166, 0.85)',
                        'rgba(201, 138, 18, 0.85)',
                        'rgba(222, 59, 43, 0.85)',
                        'rgba(30, 158, 90, 0.85)'
                    ],
                    borderWidth: 0,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Modal Voting (Polling Masuk)
    function openVoteModal(pollId, question, options, hasVoted, votedOptionId) {
        const modal = document.getElementById('vote-modal');
        const content = document.getElementById('vote-modal-content');
        
        document.getElementById('vote-poll-id').value = pollId;
        document.getElementById('vote-question-text').textContent = question;
        
        const container = document.getElementById('vote-options-container');
        container.innerHTML = ''; 
        const submitBtn = document.getElementById('btn-submit-vote');
        
        if (hasVoted) {
            const selectedOpt = options.find(opt => opt.id == votedOptionId);
            const text = selectedOpt ? (selectedOpt.option_text || selectedOpt.text) : 'Sudah memilih';
            container.innerHTML = `
                <div class="flex items-center gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs font-bold">
                    <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
                    <span>Jawaban Anda: ${text}</span>
                </div>
            `;
            submitBtn.style.display = 'none';
        } else {
            if (options && options.length > 0) {
                options.forEach((opt, index) => {
                    const text = opt.option_text || opt.text || opt;
                    const div = document.createElement('label');
                    div.className = 'flex items-center gap-3 p-3 rounded-xl border border-dpai bg-surface text-xs font-bold text-dpai-primary hover:border-dpai-accent cursor-pointer transition';
                    div.innerHTML = `
                        <input type="radio" name="vote_option_id" value="${opt.id}" class="w-4 h-4 text-dpai-accent" required>
                        <span>${text}</span>
                    `;
                    container.appendChild(div);
                });
            }
            submitBtn.style.display = 'flex';
        }
        
        modal.classList.remove('hidden');
        void modal.offsetWidth; 
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }

    function closeVoteModal() {
        const modal = document.getElementById('vote-modal');
        const content = document.getElementById('vote-modal-content');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300); 
    }

    document.getElementById('form-submit-vote').onsubmit = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-vote');
        const originalText = btn.innerHTML;
        const pollId = document.getElementById('vote-poll-id').value;
        const selectedOption = document.querySelector('input[name="vote_option_id"]:checked');

        if (!selectedOption) return;

        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...`;
        btn.disabled = true;

        try {
            const url = `{{ route('lms.teacherPolling.submitVote', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ poll_id: pollId, option_id: selectedOption.value })
            });

            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || "Gagal mengirim suara");

            closeVoteModal();
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 2000, showConfirmButton: false })
                .then(() => window.location.reload());
        } catch (error) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: error.message });
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    // Modal Pengumuman Global
    function openPengumumanGlobalModal() {
        const modal = document.getElementById('pengumumanGlobalModal');
        const content = document.getElementById('pengumumanGlobalContent');
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }

    function closePengumumanGlobalModal() {
        const modal = document.getElementById('pengumumanGlobalModal');
        const content = document.getElementById('pengumumanGlobalContent');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function submitPengumumanGlobal(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-pengumuman');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...`; 
        btn.disabled = true;

        let csrfToken = document.querySelector('meta[name="csrf-token"]');
        let token = csrfToken ? csrfToken.getAttribute('content') : '{{ csrf_token() }}';
        const formData = new FormData(form);

        if (document.getElementById('checkAllClasses').checked) {
            formData.delete('class_id[]');
        }

        try {
            const response = await fetch("{{ route('lms.teacher.pengumuman.store', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: formData
            });
            const result = await response.json();
            if(result.success || response.ok) {
                closePengumumanGlobalModal();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message || 'Pengumuman terkirim!', timer: 2000, showConfirmButton: false }).then(() => { window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Terjadi kesalahan' });
                btn.innerHTML = originalText; btn.disabled = false;
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: "Terjadi kesalahan jaringan." });
            btn.innerHTML = originalText; btn.disabled = false;
        }
    }

    // Checkbox kelas pengumuman
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAllClasses');
        const classCheckboxes = document.querySelectorAll('.class-checkbox');
        if (!checkAll) return;
        checkAll.addEventListener('change', function () {
            classCheckboxes.forEach(cb => { cb.checked = this.checked; });
        });
        classCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const allChecked = Array.from(classCheckboxes).every(c => c.checked);
                checkAll.checked = allChecked;
            });
        });
    });
</script>