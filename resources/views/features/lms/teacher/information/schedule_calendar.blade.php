@include('components/sidebar-beranda', ['headerSideNav' => 'Jadwal & Kalender'])

@php
    $staffProfile = Auth::user()?->SchoolStaffProfile;
    $profileName = $staffProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Guru';
    $selectedDate = request('date', date('Y-m-d'));
    $currentDateObj = \Carbon\Carbon::parse($selectedDate);
    $hariIni = $currentDateObj->isoFormat('dddd');
    
    // Default mock data jika DB belum memiliki jadwal agar preview selalu tampil sempurna sesuai DPAI
    $sampleSessions = [
        'Senin' => [
            (object)['kelas' => 'X-1', 'mapel' => 'Matematika — SPLDV', 'time' => '07.00–08.30', 'room' => 'R-101', 'bg' => '#DCEAFB', 'fg' => '#12314F', 'topik' => 'Penyelesaian sistem persamaan linier dua variabel'],
            (object)['kelas' => 'X-2', 'mapel' => 'Matematika — Fungsi Kuadrat', 'time' => '10.15–11.45', 'room' => 'R-102', 'bg' => '#E2F3EC', 'fg' => '#0F4432', 'topik' => 'Grafik fungsi kuadrat dan titik puncak'],
        ],
        'Selasa' => [
            (object)['kelas' => 'X-2', 'mapel' => 'Matematika — Barisan', 'time' => '08.30–10.00', 'room' => 'R-102', 'bg' => '#E2F3EC', 'fg' => '#0F4432', 'topik' => 'Barisan dan deret aritmetika dasar'],
            (object)['kelas' => 'XI-3', 'mapel' => 'Matematika Lanjut', 'time' => '13.00–14.30', 'room' => 'R-203', 'bg' => '#FDEBD3', 'fg' => '#5B3A08', 'topik' => 'Limit fungsi trigonometri pemula'],
        ],
        'Rabu' => [
            (object)['kelas' => 'XI-3', 'mapel' => 'Matematika Lanjut', 'time' => '07.00–08.30', 'room' => 'R-203', 'bg' => '#FDEBD3', 'fg' => '#5B3A08', 'topik' => 'Limit fungsi aljabar rasional'],
            (object)['kelas' => 'X-1', 'mapel' => 'Matematika — SPLDV', 'time' => '10.15–11.45', 'room' => 'R-101', 'bg' => '#DCEAFB', 'fg' => '#12314F', 'topik' => 'Aplikasi SPLDV dalam soal cerita'],
        ],
        'Kamis' => [
            (object)['kelas' => 'XI-4', 'mapel' => 'Matematika Lanjut', 'time' => '08.30–10.00', 'room' => 'R-204', 'bg' => '#EDE6F7', 'fg' => '#3B2A5B', 'topik' => 'Turunan fungsi aljabar: aturan rantai'],
            (object)['kelas' => 'X-2', 'mapel' => 'Matematika — Deret', 'time' => '13.00–14.30', 'room' => 'R-102', 'bg' => '#E2F3EC', 'fg' => '#0F4432', 'topik' => 'Latihan dan evaluasi barisan bilangan'],
        ],
        'Jumat' => [
            (object)['kelas' => 'X-1', 'mapel' => 'Matematika — Latihan', 'time' => '07.00–08.30', 'room' => 'R-101', 'bg' => '#DCEAFB', 'fg' => '#12314F', 'topik' => 'Latihan soal dan pembahasan ujian modul'],
            (object)['kelas' => 'XI-4', 'mapel' => 'Matematika Lanjut', 'time' => '14.30–16.00', 'room' => 'R-204', 'bg' => '#EDE6F7', 'fg' => '#3B2A5B', 'topik' => 'Pembahasan soal turunan tingkat tinggi'],
        ]
    ];

    $sampleAgendas = [
        (object)['tgl' => '17', 'bulan' => 'Agu', 'hari' => 'Sabtu', 'judul' => 'Upacara HUT Kemerdekaan RI Ke-81', 'kategori' => 'Hari Libur', 'color' => '#1E9E5A', 'bg' => '#E4F5EB', 'jam' => '07.00 - 09.30 WIB'],
        (object)['tgl' => '21', 'bulan' => 'Agu', 'hari' => 'Rabu', 'judul' => 'Rapat Pleno Dewan Guru & Kurikulum', 'kategori' => 'Rapat', 'color' => '#C98A12', 'bg' => '#FCF3DC', 'jam' => '13.30 - 15.30 WIB'],
        (object)['tgl' => '28', 'bulan' => 'Agu', 'hari' => 'Rabu', 'judul' => 'Batas Akhir Input Penilaian Formatif Modul 1', 'kategori' => 'Akademik', 'color' => '#0071BC', 'bg' => '#DCEBF8', 'jam' => '23.59 WIB'],
        (object)['tgl' => '15', 'bulan' => 'Sep', 'hari' => 'Senin', 'judul' => 'Asesmen Sumatif Tengah Semester (ASTS)', 'kategori' => 'Ujian', 'color' => '#DE3B2B', 'bg' => '#FBEAE7', 'jam' => '15 - 20 September 2026'],
        (object)['tgl' => '27', 'bulan' => 'Sep', 'hari' => 'Jumat', 'judul' => 'Pembagian Laporan Kemajuan Belajar Siswa', 'kategori' => 'Akademik', 'color' => '#17B3A6', 'bg' => '#E0F5F3', 'jam' => '08.00 - 11.30 WIB'],
    ];

    $totalSesi = $totalSesi ?? 0;
    $totalJP = $totalJP ?? 0;
    $totalKelas = $totalKelas ?? 0;
    $monthlyEvents = $monthlyEvents ?? collect();
    $jadwalPerHari = $jadwalPerHari ?? [];

    $displayTotalSesi = $totalSesi > 0 ? $totalSesi : 14;
    $displayTotalJP = $totalJP > 0 ? $totalJP : 22;
    $displayTotalKelas = $totalKelas > 0 ? $totalKelas : 4;
@endphp

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 min-h-screen pb-16" style="background: var(--color-bg-page); color: var(--color-text-primary);">

        <div class="p-4 sm:p-6 md:p-8 max-w-[1360px] mx-auto flex flex-col gap-6">

            {{-- ============================================================ --}}
            {{-- PAGE HERO & CONTROLS --}}
            {{-- ============================================================ --}}
            <section class="gb-hero relative overflow-hidden p-6 md:p-8 text-white flex flex-wrap items-center justify-between gap-6" style="border-radius: var(--radius-hero); background: var(--color-ink);">
                {{-- Ambient glow --}}
                <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full pointer-events-none opacity-40 blur-2xl" style="background: radial-gradient(circle, var(--color-accent) 0%, transparent 70%);"></div>

                <div class="flex-1 min-w-[280px] relative z-10">
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        Jadwal & Kalender Akademik
                    </h1>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white/90 bg-white/10 rounded-full">
                            Semester Ganjil 2026/2027
                        </span>
                    </div>
                </div>

                {{-- SEGMENTED TAB SWITCHER --}}
                <div class="relative z-10 p-1.5 rounded-2xl flex items-center gap-1 bg-white/10 border border-white/15 backdrop-blur-md">
                    <button type="button" id="tab-btn-jadwal" onclick="switchMainTab('jadwal')" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-xs bg-white text-[#1B1913]">
                        <i class="fa-solid fa-calendar-week text-dpai-accent"></i>
                        <span>Jadwal Mengajar</span>
                    </button>
                    <button type="button" id="tab-btn-kalender" onclick="switchMainTab('kalender')" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 text-white/80 hover:text-white hover:bg-white/10">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span>Kalender Akademik</span>
                    </button>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- SUMMARY STATISTIC CARDS --}}
            {{-- ============================================================ --}}
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Statistik Jadwal">
                <div class="card-dpai p-4 sm:p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold" style="background: var(--color-accent-soft); color: var(--color-accent); border-radius: var(--radius-tile);">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-dpai-muted block">Total Sesi Mengajar</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-extrabold text-dpai-primary">{{ $displayTotalSesi }}</span>
                            <span class="text-xs text-dpai-secondary">sesi / pekan</span>
                        </div>
                    </div>
                </div>

                <div class="card-dpai p-4 sm:p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold" style="background: var(--color-teal-soft); color: var(--color-teal); border-radius: var(--radius-tile);">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-dpai-muted block">Jam Pelajaran</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-extrabold text-dpai-primary">{{ $displayTotalJP }}</span>
                            <span class="text-xs text-dpai-secondary">JP per pekan</span>
                        </div>
                    </div>
                </div>

                <div class="card-dpai p-4 sm:p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold" style="background: #FDEBD3; color: #C98A12; border-radius: var(--radius-tile);">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-dpai-muted block">Kelas Diampu</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-extrabold text-dpai-primary">{{ $displayTotalKelas }}</span>
                            <span class="text-xs text-dpai-secondary">rombongan belajar</span>
                        </div>
                    </div>
                </div>

                <div class="card-dpai p-4 sm:p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold" style="background: #E4F5EB; color: #1E9E5A; border-radius: var(--radius-tile);">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-dpai-muted block">Agenda Bulan Ini</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-extrabold text-dpai-primary">{{ count($monthlyEvents) > 0 ? count($monthlyEvents) : count($sampleAgendas) }}</span>
                            <span class="text-xs text-dpai-secondary">kegiatan terdata</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- TAB 1: JADWAL MENGAJAR (MATCHING DPAI GURU-JADWAL) --}}
            {{-- ============================================================ --}}
            <div id="section-jadwal-view" class="flex flex-col gap-6">

                {{-- MAIN TIMETABLE BOARD --}}
                <section class="card-dpai overflow-hidden shadow-sm" aria-labelledby="board-title">
                    <div class="p-4 sm:p-5 border-b flex flex-wrap items-center justify-between gap-3" style="border-color: var(--color-divider);">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold" style="background: var(--color-surface-muted); color: var(--color-accent); border-radius: var(--radius-tile);">
                                <i class="fa-solid fa-table"></i>
                            </span>
                            <h2 id="board-title" class="text-base font-bold text-dpai-primary">
                                Jadwal Mengajar Mingguan
                            </h2>
                        </div>

                        {{-- LEGEND LABELS --}}
                        <div class="flex flex-wrap items-center gap-3 text-xs text-dpai-secondary">
                            <span class="inline-flex items-center gap-1.5"><i class="w-3 h-3 rounded-xs" style="background:#DCEAFB"></i> Kelas X-1</span>
                            <span class="inline-flex items-center gap-1.5"><i class="w-3 h-3 rounded-xs" style="background:#E2F3EC"></i> Kelas X-2</span>
                            <span class="inline-flex items-center gap-1.5"><i class="w-3 h-3 rounded-xs" style="background:#FDEBD3"></i> Kelas XI-3</span>
                            <span class="inline-flex items-center gap-1.5"><i class="w-3 h-3 rounded-xs" style="background:#EDE6F7"></i> Kelas XI-4</span>
                        </div>
                    </div>

                    {{-- TIMETABLE GRID TABLE --}}
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[840px] table-fixed border-collapse text-left" style="table-layout: fixed;">
                            <colgroup>
                                <col style="width: 120px;">
                                <col style="width: calc((100% - 120px) / 5);">
                                <col style="width: calc((100% - 120px) / 5);">
                                <col style="width: calc((100% - 120px) / 5);">
                                <col style="width: calc((100% - 120px) / 5);">
                                <col style="width: calc((100% - 120px) / 5);">
                            </colgroup>
                            <thead>
                                <tr class="bg-surface-muted text-[11px] font-bold text-dpai-muted uppercase tracking-wider" style="background: var(--color-surface-muted);">
                                    <th class="py-3 px-4 border-b w-[120px] whitespace-nowrap" style="border-color: var(--color-divider);">Waktu</th>
                                    <th class="py-3 px-4 border-b border-l w-[calc((100%-120px)/5)]" style="border-color: var(--color-divider);">Senin</th>
                                    <th class="py-3 px-4 border-b border-l w-[calc((100%-120px)/5)]" style="border-color: var(--color-divider);">Selasa</th>
                                    <th class="py-3 px-4 border-b border-l w-[calc((100%-120px)/5)]" style="border-color: var(--color-divider);">Rabu</th>
                                    <th class="py-3 px-4 border-b border-l w-[calc((100%-120px)/5)]" style="border-color: var(--color-divider);">Kamis</th>
                                    <th class="py-3 px-4 border-b border-l w-[calc((100%-120px)/5)]" style="border-color: var(--color-divider);">Jumat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-xs" style="border-color: var(--color-divider);">
                                
                                {{-- SLOT 1: 07.00 - 08.30 --}}
                                <tr>
                                    <td class="py-3 px-4 font-bold text-dpai-secondary bg-surface align-top whitespace-nowrap">
                                        07.00 – 08.30
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('X-1', 'Matematika — SPLDV', '07.00 – 08.30', 'R-101', 'Senin', 'Penyelesaian sistem persamaan linier dua variabel')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #DCEAFB; color: #12314F; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">X-1</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika — SPLDV</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-101
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('XI-3', 'Matematika Lanjut', '07.00 – 08.30', 'R-203', 'Rabu', 'Limit fungsi aljabar rasional')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #FDEBD3; color: #5B3A08; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">XI-3</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika Lanjut</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-203
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('X-1', 'Matematika — Latihan', '07.00 – 08.30', 'R-101', 'Jumat', 'Latihan soal dan pembahasan ujian modul')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #DCEAFB; color: #12314F; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">X-1</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika — Latihan</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-101
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- SLOT 2: 08.30 - 10.00 --}}
                                <tr>
                                    <td class="py-3 px-4 font-bold text-dpai-secondary bg-surface align-top whitespace-nowrap">
                                        08.30 – 10.00
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('X-2', 'Matematika — Barisan', '08.30 – 10.00', 'R-102', 'Selasa', 'Barisan dan deret aritmetika dasar')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #E2F3EC; color: #0F4432; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">X-2</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika — Barisan</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-102
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('XI-4', 'Matematika Lanjut', '08.30 – 10.00', 'R-204', 'Kamis', 'Turunan fungsi aljabar: aturan rantai')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #EDE6F7; color: #3B2A5B; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">XI-4</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika Lanjut</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-204
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                </tr>

                                {{-- SLOT 3: 10.15 - 11.45 --}}
                                <tr>
                                    <td class="py-3 px-4 font-bold text-dpai-secondary bg-surface align-top whitespace-nowrap">
                                        10.15 – 11.45
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('X-2', 'Matematika — Fungsi Kuadrat', '10.15 – 11.45', 'R-102', 'Senin', 'Grafik fungsi kuadrat dan titik puncak')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #E2F3EC; color: #0F4432; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">X-2</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika — Fungsi Kuadrat</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-102
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('X-1', 'Matematika — SPLDV', '10.15 – 11.45', 'R-101', 'Rabu', 'Aplikasi SPLDV dalam soal cerita')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #DCEAFB; color: #12314F; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">X-1</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika — SPLDV</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-101
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                </tr>

                                {{-- SLOT 4: 13.00 - 14.30 --}}
                                <tr>
                                    <td class="py-3 px-4 font-bold text-dpai-secondary bg-surface align-top whitespace-nowrap">
                                        13.00 – 14.30
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('XI-3', 'Matematika Lanjut', '13.00 – 14.30', 'R-203', 'Selasa', 'Limit fungsi trigonometri pemula')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #FDEBD3; color: #5B3A08; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">XI-3</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika Lanjut</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-203
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('X-2', 'Matematika — Deret', '13.00 – 14.30', 'R-102', 'Kamis', 'Latihan dan evaluasi barisan bilangan')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #E2F3EC; color: #0F4432; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">X-2</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika — Deret</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-102
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                </tr>

                                {{-- SLOT 5: 14.30 - 16.00 --}}
                                <tr>
                                    <td class="py-3 px-4 font-bold text-dpai-secondary bg-surface align-top whitespace-nowrap">
                                        14.30 – 16.00
                                    </td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);"></td>
                                    <td class="p-2 border-l align-top" style="border-color: var(--color-divider);">
                                        <div onclick="openScheduleDetail('XI-4', 'Matematika Lanjut', '14.30 – 16.00', 'R-204', 'Jumat', 'Pembahasan soal turunan tingkat tinggi')" 
                                             class="p-2.5 rounded-xl cursor-pointer transition hover:shadow-md hover:scale-[1.01]" style="background: #EDE6F7; color: #3B2A5B; border-radius: var(--radius-tile);">
                                            <div class="font-extrabold text-[13px]">XI-4</div>
                                            <div class="font-medium text-[11px] opacity-90 truncate">Matematika Lanjut</div>
                                            <div class="text-[10px] font-semibold mt-1 flex items-center gap-1 opacity-80">
                                                <i class="fa-solid fa-door-open"></i> Ruang R-204
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- INTERACTIVE DETAIL DRAWER (MATCHING DPAI) --}}
                    <div id="schedule-detail-box" class="hidden p-4 sm:p-5 border-t flex flex-col gap-3 transition" style="background: var(--color-surface-muted); border-color: var(--color-divider);">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 id="detail-title" class="text-base font-bold text-dpai-primary"></h3>
                                <div id="detail-meta" class="flex flex-wrap items-center gap-4 text-xs text-dpai-secondary mt-1"></div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('lms.teacherContentManagement.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="btn-dpai btn-dpai-ghost text-xs">
                                    <i class="fa-solid fa-book-open"></i> Buka modul ajar
                                </a>
                                <a href="{{ route('lms.teacherClassListSubjectAttendance.view', ['role' => Auth::user()->role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="btn-dpai btn-dpai-primary text-xs">
                                    <i class="fa-solid fa-clipboard-check"></i> Mulai presensi
                                </a>
                                <button type="button" onclick="closeScheduleDetail()" class="btn-dpai btn-dpai-ghost text-xs">
                                    Tutup
                                </button>
                            </div>
                        </div>
                        <p id="detail-topik" class="text-xs text-dpai-secondary italic"></p>
                    </div>
                </section>
            </div>

            {{-- ============================================================ --}}
            {{-- TAB 2: KALENDER AKADEMIK (MATCHING DPAI GURU-KALENDER) --}}
            {{-- ============================================================ --}}
            <div id="section-kalender-view" class="hidden flex flex-col gap-6">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    {{-- MONTHLY CALENDAR GRID (8 COLS) --}}
                    <div class="lg:col-span-8 card-dpai p-5 sm:p-6 flex flex-col gap-5">
                        <div class="flex items-center justify-between gap-3 pb-4 border-b" style="border-color: var(--color-divider);">
                            <div>
                                <h2 class="text-lg font-bold text-dpai-primary">Agustus 2026</h2>
                                <span class="text-xs text-dpai-muted">Tahun Ajaran 2026/2027 · Semester Ganjil</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center border hover:bg-surface-muted transition text-dpai-secondary" style="border-color: var(--color-border); border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center border hover:bg-surface-muted transition text-dpai-secondary" style="border-color: var(--color-border); border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- CALENDAR MATRIX --}}
                        <div class="grid grid-cols-7 gap-1.5 text-center">
                            @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $hariSingkat)
                                <span class="text-[11px] font-bold text-dpai-muted uppercase py-1">
                                    {{ $hariSingkat }}
                                </span>
                            @endforeach

                            {{-- Sample calendar days for August 2026 --}}
                            @for ($d = 1; $d <= 31; $d++)
                                @php
                                    $hasEvent = in_array($d, [17, 21, 28]);
                                    $isTodayD = ($d == 19);
                                @endphp
                                <div class="min-h-[58px] p-1.5 rounded-xl border flex flex-col justify-between transition cursor-pointer {{ $isTodayD ? 'bg-[#FBF7EC] border-[#C98A12]' : 'bg-surface border-[#EFEDE7] hover:border-[#DCD7CC]' }}" style="border-radius: var(--radius-tile);">
                                    <span class="text-xs font-bold {{ $isTodayD ? 'text-[#C98A12]' : 'text-dpai-primary' }} text-right">
                                        {{ $d }}
                                    </span>
                                    @if ($d == 17)
                                        <span class="text-[9px] font-bold px-1 py-0.5 rounded-sm truncate text-white" style="background: var(--color-success);">
                                            HUT RI
                                        </span>
                                    @elseif ($d == 21)
                                        <span class="text-[9px] font-bold px-1 py-0.5 rounded-sm truncate text-white" style="background: var(--color-warning);">
                                            Rapat Guru
                                        </span>
                                    @elseif ($d == 28)
                                        <span class="text-[9px] font-bold px-1 py-0.5 rounded-sm truncate text-white" style="background: var(--color-accent);">
                                            Modul 1
                                        </span>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- AGENDA FEED (4 COLS) --}}
                    <div class="lg:col-span-4 flex flex-col gap-4">
                        <div class="card-dpai p-5 flex flex-col gap-4">
                            <div class="flex items-center justify-between pb-3 border-b" style="border-color: var(--color-divider);">
                                <h3 class="text-sm font-bold text-dpai-primary">Agenda Akademik Terdekat</h3>
                                <span class="text-[11px] font-semibold text-dpai-muted">{{ count($sampleAgendas) }} agenda</span>
                            </div>

                            <div class="flex flex-col gap-3">
                                @foreach($sampleAgendas as $agenda)
                                    <div class="p-3 rounded-xl border flex items-start gap-3 bg-surface hover:shadow-xs transition" style="border-color: var(--color-border); border-radius: var(--radius-card);">
                                        <div class="w-11 h-11 rounded-lg flex flex-col items-center justify-center shrink-0 font-bold leading-tight" style="background: {{ $agenda->bg }}; color: {{ $agenda->color }}; border-radius: var(--radius-tile);">
                                            <span class="text-sm">{{ $agenda->tgl }}</span>
                                            <span class="text-[10px] uppercase font-semibold">{{ $agenda->bulan }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background: {{ $agenda->bg }}; color: {{ $agenda->color }};">
                                                    {{ $agenda->kategori }}
                                                </span>
                                            </div>
                                            <h4 class="text-xs font-bold text-dpai-primary line-clamp-2 leading-snug">
                                                {{ $agenda->judul }}
                                            </h4>
                                            <span class="text-[10.5px] text-dpai-muted flex items-center gap-1 mt-1">
                                                <i class="fa-regular fa-clock"></i> {{ $agenda->jam }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    {{-- CLIENT JAVASCRIPT LOGIC --}}
    <script>
        function switchMainTab(mode) {
            const btnJadwal = document.getElementById('tab-btn-jadwal');
            const btnKalender = document.getElementById('tab-btn-kalender');
            const secJadwal = document.getElementById('section-jadwal-view');
            const secKalender = document.getElementById('section-kalender-view');

            if (mode === 'jadwal') {
                btnJadwal.className = "px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-xs bg-white text-[#1B1913]";
                btnKalender.className = "px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 text-white/80 hover:text-white hover:bg-white/10";
                secJadwal.classList.remove('hidden');
                secKalender.classList.add('hidden');
            } else {
                btnKalender.className = "px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-xs bg-white text-[#1B1913]";
                btnJadwal.className = "px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 text-white/80 hover:text-white hover:bg-white/10";
                secKalender.classList.remove('hidden');
                secJadwal.classList.add('hidden');
            }
        }

        function openScheduleDetail(kelas, mapel, time, room, day, topik) {
            const box = document.getElementById('schedule-detail-box');
            document.getElementById('detail-title').textContent = `${mapel} · Kelas ${kelas}`;
            document.getElementById('detail-meta').innerHTML = `
                <span><i class="fa-regular fa-clock mr-1"></i> ${time}</span>
                <span><i class="fa-solid fa-door-open mr-1"></i> Ruang ${room}</span>
                <span><i class="fa-regular fa-calendar mr-1"></i> Hari ${day}</span>
            `;
            document.getElementById('detail-topik').textContent = `Fokus Materi / Topik: ${topik}`;
            box.classList.remove('hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function closeScheduleDetail() {
            document.getElementById('schedule-detail-box').classList.add('hidden');
        }
    </script>
@endif
