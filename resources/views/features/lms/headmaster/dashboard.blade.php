@include('components/sidebar-beranda', ['headerSideNav' => 'Pusat Kendali'])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))

    @php
        $user = Auth::user();
        $profile = $user->SchoolStaffProfile;

        $jk = $profile?->jenis_kelamin;
        $sapaan = $jk == 'L' ? 'Bapak ' : ($jk == 'P' ? 'Ibu ' : '');
        $namaLengkap = $profile?->nama_lengkap ?? 'Kepala';

        $schoolRole = $user->role;
        $schoolName = $profile?->SchoolPartner?->nama_sekolah;
        $schoolId = $profile?->SchoolPartner?->id;

        $academicDriveUrl = url(
            '/lms/' .
            rawurlencode($schoolRole) . '/' .
            rawurlencode($schoolName) . '/' .
            $schoolId .
            '/registrasi-guru/academic-drive'
        );
    @endphp

    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F1F5F9] min-h-screen pb-12">

        <div class="p-6 md:p-10 space-y-8">

            {{-- ========================================================= --}}
            {{-- HEADER --}}
            {{-- ========================================================= --}}
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">

                <div>

                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-bold uppercase tracking-widest text-slate-400">
                            <li class="inline-flex items-center">LMS</li>
                            <li><i class="fas fa-chevron-right mx-2 text-[8px]"></i></li>
                            <li class="text-[#0071BC]">Dashboard</li>
                        </ol>
                    </nav>

                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                        Halo, {{ $sapaan }}{{ $namaLengkap }}
                    </h1>

                    <p class="text-slate-500 mt-2 font-medium">
                        Ringkasan operasional sekolah untuk
                        <span class="text-slate-800 font-bold">
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                    </p>

                </div>

                <div class="flex items-center gap-3">

                    <button
                        onclick="openModalPengumuman()"
                        class="px-5 py-2.5 bg-[#0071BC] text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center gap-2"
                    >
                        <i class="fas fa-plus"></i>
                        Buat Pengumuman
                    </button>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- KPI --}}
            {{-- ========================================================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-6">

                {{-- TOTAL MURID --}}
                <div class="bg-white rounded-[2rem] p-7 border border-white shadow-sm hover:shadow-xl transition-all duration-500 group">

                    <div class="flex items-center justify-between mb-6">

                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-users"></i>
                        </div>

                    </div>

                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">
                        Total Murid
                    </h4>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-800">
                            {{ $stats->total_siswa ?? 0 }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            Aktif
                        </span>
                    </div>

                </div>


                {{-- TOTAL GURU --}}
                <div class="bg-white rounded-[2rem] p-7 border border-white shadow-sm hover:shadow-xl transition-all duration-500 group">

                    <div class="flex items-center justify-between mb-6">

                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-tie"></i>
                        </div>

                        <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-1 rounded-lg">
                            Tetap
                        </span>

                    </div>

                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">
                        Total Guru
                    </h4>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-800">
                            {{ $stats->total_guru ?? 0 }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            Pendidik
                        </span>
                    </div>

                </div>


                {{-- RUANG KELAS --}}
                <div class="bg-white rounded-[2rem] p-7 border border-white shadow-sm hover:shadow-xl transition-all duration-500 group">

                    <div class="flex items-center justify-between mb-6">

                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-door-open"></i>
                        </div>

                        <div class="flex -space-x-2">
                            <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-200"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-300"></div>
                        </div>

                    </div>

                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">
                        Ruang Kelas
                    </h4>

                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-800">
                            {{ $stats->total_kelas ?? 0 }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            Rombel
                        </span>
                    </div>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- MAIN GRID --}}
            {{-- KIRI = MONITORING + PERANGKAT --}}
            {{-- KANAN = PENGUMUMAN --}}
            {{-- ========================================================= --}}
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

                {{-- ===================================================== --}}
                {{-- KOLOM KIRI --}}
                {{-- ===================================================== --}}
                <div class="xl:col-span-8 space-y-8">

                    {{-- ================================================= --}}
                    {{-- MODUL MONITORING STRATEGIS --}}
                    {{-- ================================================= --}}
                    <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100">

                        <div class="flex items-center gap-4 mb-10">
                            <div class="h-10 w-1 bg-[#0071BC] rounded-full"></div>
                            <h3 class="text-xl font-black text-slate-800">
                                Modul Monitoring Strategis
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">

                            {{-- LAPORAN AKADEMIK --}}
                            <a
                                href="{{ route('lms.headmaster.academic.report', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                    'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                ]) }}"
                                class="group block"
                            >
                                <div class="relative rounded-[2rem] bg-slate-50 p-8 border border-transparent hover:border-[#0071BC] hover:bg-white hover:shadow-2xl hover:shadow-blue-100 transition-all duration-500">

                                    <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center text-2xl text-[#0071BC] mb-6 group-hover:rotate-6 transition-transform">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>

                                    <h4 class="text-lg font-black text-slate-800 mb-2">
                                        Laporan Akademik
                                    </h4>

                                    <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6">
                                        Analisis performa nilai siswa per kelas dan pemetaan mata pelajaran kritis.
                                    </p>

                                    <span class="inline-flex items-center gap-2 text-[#0071BC] font-black text-xs uppercase tracking-widest">
                                        Buka Analitik
                                        <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                                    </span>

                                </div>
                            </a>


                            {{-- AKTIVITAS GURU --}}
                            <a
                                href="{{ route('lms.headmaster.teacher.activity', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                    'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                ]) }}"
                                class="group block"
                            >
                                <div class="relative rounded-[2rem] bg-slate-50 p-8 border border-transparent hover:border-emerald-500 hover:bg-white hover:shadow-2xl hover:shadow-emerald-100 transition-all duration-500">

                                    <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center text-2xl text-emerald-500 mb-6 group-hover:rotate-6 transition-transform">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>

                                    <h4 class="text-lg font-black text-slate-800 mb-2">
                                        Aktivitas Guru
                                    </h4>

                                    <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6">
                                        Pantau jurnal mengajar harian, kehadiran pendidik, dan jadwal aktif sekolah.
                                    </p>

                                    <span class="inline-flex items-center gap-2 text-emerald-500 font-black text-xs uppercase tracking-widest">
                                        Monitoring
                                        <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                                    </span>

                                </div>
                            </a>

                        </div>

                    </div>


                    {{-- ================================================= --}}
                    {{-- PERANGKAT PEMBELAJARAN GURU --}}
                    {{-- ================================================= --}}
                    <section
                        id="dashboardPerangkatGuru"
                        class="group cursor-pointer bg-white rounded-3xl shadow-sm border border-gray-200 p-7 transition-all duration-300 hover:shadow-lg hover:border-blue-300 hover:-translate-y-0.5"
                        onclick="openDashboardPerangkatGuru()"
                    >

                        {{-- HEADER --}}
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                            {{-- BAGIAN KIRI --}}
                            <div class="flex items-center gap-5">

                                <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition duration-300">
                                    <i class="fa-solid fa-book-open text-2xl"></i>
                                </div>

                                <div>

                                    <div class="flex items-center gap-3">

                                        <h2 class="text-xl md:text-2xl font-bold text-gray-800 group-hover:text-blue-600 transition">
                                            Perangkat Pembelajaran Guru
                                        </h2>

                                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">
                                            Dashboard
                                        </span>

                                    </div>

                                    <p class="text-sm text-gray-500 mt-1">
                                        Pengelolaan perangkat pembelajaran sekolah
                                    </p>

                                    <p class="text-xs text-gray-400 mt-3">
                                        Klik untuk membuka dashboard perangkat pembelajaran
                                    </p>

                                </div>

                            </div>


                            {{-- BAGIAN KANAN --}}
                            <div class="flex items-center gap-4">

                                <button
                                    type="button"
                                    onclick="event.stopPropagation(); openDashboardPerangkatGuru();"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-4 text-sm font-semibold text-white shadow-sm transition duration-300 hover:bg-blue-700 hover:shadow-md"
                                >

                                    <i class="fa-solid fa-chart-pie"></i>

                                    <span>
                                        Buka Dashboard
                                    </span>

                                    <i class="fa-solid fa-arrow-right text-xs transition-transform duration-300"></i>

                                </button>

                            </div>

                        </div>


                        {{-- PEMBATAS --}}
                        <div class="border-t border-gray-100 my-6"></div>


                        {{-- MENU --}}
                        <div class="flex flex-wrap items-center gap-3">

                            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm text-gray-600">
                                <i class="fa-solid fa-chart-line text-blue-500"></i>
                                <span>Analisis CP hingga ATP</span>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm text-gray-600">
                                <i class="fa-solid fa-calendar-days text-blue-500"></i>
                                <span>PROTA &amp; PROSEM</span>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm text-gray-600">
                                <i class="fa-solid fa-file-lines text-blue-500"></i>
                                <span>RPPM</span>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm text-gray-600">
                                <i class="fa-solid fa-comments text-blue-500"></i>
                                <span>Refleksi Guru</span>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-sm text-blue-600 font-medium">
                                <i class="fa-solid fa-box-archive"></i>
                                <span>Academic Drive</span>
                            </div>

                        </div>

                    </section>

                </div>


                {{-- ===================================================== --}}
                {{-- KOLOM KANAN --}}
                {{-- ===================================================== --}}
                <div class="xl:col-span-4 space-y-8">

                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full min-h-[500px]">

                        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">

                            <h3 class="font-black text-slate-800 tracking-tight flex items-center gap-2">
                                <i class="fas fa-bullhorn text-amber-500"></i>
                                Pengumuman ke Guru
                            </h3>

                            <div class="w-2 h-2 rounded-full bg-red-500 animate-ping"></div>

                        </div>

                        <div class="p-8 space-y-8 overflow-y-auto custom-scrollbar flex-1">

                            @forelse($pengumuman as $info)

                                <div class="relative pl-6 border-l-2 border-slate-100 hover:border-[#0071BC] transition-colors group cursor-pointer">

                                    <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-200 group-hover:bg-[#0071BC] transition-colors"></div>

                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">
                                        {{ \Carbon\Carbon::parse($info->created_at)->diffForHumans() }}
                                    </span>

                                    <h4 class="font-bold text-sm text-slate-700 mt-1 leading-snug group-hover:text-[#0071BC] transition-colors">
                                        {{ $info->judul }}
                                    </h4>

                                </div>

                            @empty

                                <div class="h-full flex flex-col items-center justify-center text-center opacity-40 py-20">

                                    <i class="fas fa-comment-slash text-4xl mb-4"></i>

                                    <p class="text-sm font-bold">
                                        Belum ada info terbaru
                                    </p>

                                </div>

                            @endforelse

                        </div>

                        <div class="p-6 bg-slate-50/80">

                            <button class="cursor-pointer w-full py-3 rounded-2xl bg-white border border-slate-200 text-xs font-black text-slate-600 hover:bg-[#0071BC] hover:text-white hover:border-[#0071BC] transition-all uppercase tracking-widest">
                                Lihat Semua Arsip
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>


    {{-- ============================================================= --}}
    {{-- MODAL DASHBOARD PERANGKAT GURU --}}
    {{-- ============================================================= --}}
    <div id="modalDashboardPerangkatGuru" class="fixed inset-0 z-[9999] hidden" aria-hidden="true">

        {{-- BACKDROP --}}
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            onclick="closeDashboardPerangkatGuru()"
        ></div>

        {{-- WRAPPER --}}
        <div class="relative min-h-screen flex items-center justify-center p-4 sm:p-6">

            {{-- CONTENT --}}
            <div
                id="dashboardModalContent"
                class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-white rounded-3xl shadow-2xl border border-gray-200 transform scale-95 opacity-0 transition-all duration-300"
            >

                {{-- HEADER --}}
                <div class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-gray-200 px-6 py-5 flex items-center justify-between">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-book-open text-xl"></i>
                        </div>

                        <div>

                            <h2 class="text-lg md:text-xl font-bold text-gray-800">
                                Perangkat Pembelajaran Guru
                            </h2>

                            <p class="text-sm text-gray-500">
                                Pilih perangkat pembelajaran yang ingin dikelola
                            </p>

                        </div>

                    </div>

                    <button
                        type="button"
                        onclick="closeDashboardPerangkatGuru()"
                        class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition"
                    >
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                </div>


                {{-- BODY --}}
                <div class="p-6">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                        {{-- 1. ANALISIS CP HINGGA ATP --}}
                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div class="h-full bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-300 transition duration-300">

                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-5 group-hover:bg-blue-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-chart-line text-xl"></i>
                                </div>

                                <h3 class="text-base font-bold text-gray-800 group-hover:text-blue-600 transition">
                                    Analisis CP hingga ATP
                                </h3>

                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                    Analisis Capaian Pembelajaran hingga Alur Tujuan Pembelajaran.
                                </p>

                                <div class="flex items-center gap-2 mt-5 text-sm font-medium text-blue-600">
                                    <span>Lihat Detail</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </div>

                            </div>

                        </a>


                        {{-- 2. PROTA DAN PROSEM --}}
                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div class="h-full bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-300 transition duration-300">

                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-5 group-hover:bg-blue-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-calendar-days text-xl"></i>
                                </div>

                                <h3 class="text-base font-bold text-gray-800 group-hover:text-blue-600 transition">
                                    PROTA dan PROSEM
                                </h3>

                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                    Pengelolaan Program Tahunan dan Program Semester guru.
                                </p>

                                <div class="flex items-center gap-2 mt-5 text-sm font-medium text-blue-600">
                                    <span>Lihat Detail</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </div>

                            </div>

                        </a>


                        {{-- 3. RPPM --}}
                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div class="h-full bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-300 transition duration-300">

                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-5 group-hover:bg-blue-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-file-lines text-xl"></i>
                                </div>

                                <h3 class="text-base font-bold text-gray-800 group-hover:text-blue-600 transition">
                                    Analisis RPPM
                                </h3>

                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                    Analisis dan pengelolaan perangkat RPPM guru.
                                </p>

                                <div class="flex items-center gap-2 mt-5 text-sm font-medium text-blue-600">
                                    <span>Lihat Detail</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </div>

                            </div>

                        </a>


                        {{-- 4. REFLEKSI GURU --}}
                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div class="h-full bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-300 transition duration-300">

                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-5 group-hover:bg-blue-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-comments text-xl"></i>
                                </div>

                                <h3 class="text-base font-bold text-gray-800 group-hover:text-blue-600 transition">
                                    Refleksi Guru
                                </h3>

                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                    Refleksi guru terhadap proses dan hasil pembelajaran.
                                </p>

                                <div class="flex items-center gap-2 mt-5 text-sm font-medium text-blue-600">
                                    <span>Lihat Detail</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </div>

                            </div>

                        </a>


                        {{-- 5. ACADEMIC DRIVE --}}
                        <a
                            href="{{ $academicDriveUrl }}"
                            class="group block"
                        >

                            <div class="h-full bg-white border border-blue-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-400 transition duration-300">

                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-5 group-hover:bg-blue-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-box-archive text-xl"></i>
                                </div>

                                <h3 class="text-base font-bold text-gray-800 group-hover:text-blue-600 transition">
                                    Academic Drive
                                </h3>

                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                    Akses arsip dan penyimpanan perangkat pembelajaran guru.
                                </p>

                                <div class="flex items-center gap-2 mt-5 text-sm font-medium text-blue-600">
                                    <span>Masuk ke Arsip</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </div>

                            </div>

                        </a>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="border-t border-gray-200 px-6 py-4 flex justify-end">

                    <button
                        type="button"
                        onclick="closeDashboardPerangkatGuru()"
                        class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition"
                    >
                        Tutup
                    </button>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- MODAL PENGUMUMAN --}}
    {{-- ============================================================= --}}
    <div
        id="pengumumanModal"
        class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300"
    >

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden transform scale-95 transition-all duration-300 flex flex-col">

            <div class="bg-[#0071BC] p-6 text-white flex justify-between items-center">

                <h3 class="font-bold text-lg">
                    <i class="fas fa-bullhorn mr-2"></i>
                    Buat Pengumuman ke Guru
                </h3>

                <button
                    onclick="closeModalPengumuman()"
                    class="hover:rotate-90 transition-transform"
                >
                    <i class="fas fa-times"></i>
                </button>

            </div>


            <form
                id="formPengumumanKepsek"
                onsubmit="submitPengumumanKepsek(event)"
                class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar"
            >

                @csrf

                <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">


                {{-- TARGET --}}
                <div>

                    <label class="block text-sm font-bold text-slate-700 mb-3">
                        Penerima Pengumuman
                    </label>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">

                        <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-[#0071BC] hover:bg-blue-50 transition cursor-pointer">

                            <input
                                type="checkbox"
                                name="target[]"
                                value="Guru"
                                class="w-5 h-5 rounded border-slate-300 text-[#0071BC]"
                            >

                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#0071BC] flex items-center justify-center">
                                    <i class="fas fa-user-tie"></i>
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Guru
                                    </p>
                                </div>

                            </div>

                        </label>


                        <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition cursor-pointer">

                            <input
                                type="checkbox"
                                name="target[]"
                                value="Siswa"
                                class="w-5 h-5 rounded border-slate-300 text-amber-500"
                            >

                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <i class="fas fa-user-graduate"></i>
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Siswa
                                    </p>
                                </div>

                            </div>

                        </label>


                        <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-green-500 hover:bg-green-50 transition cursor-pointer">

                            <input
                                type="checkbox"
                                name="target[]"
                                value="Orang Tua"
                                class="w-5 h-5 rounded border-slate-300 text-green-600"
                            >

                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center">
                                    <i class="fas fa-people-roof"></i>
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Orang Tua
                                    </p>
                                </div>

                            </div>

                        </label>

                    </div>

                    <span id="error-target" class="text-red-500 text-xs font-medium mt-2 block"></span>

                    <p class="mt-2 text-xs text-slate-500">
                        <i class="fas fa-circle-info mr-1"></i>
                        Anda dapat memilih lebih dari satu penerima.
                    </p>

                </div>


                {{-- JUDUL --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Judul Pengumuman
                    </label>

                    <input
                        id="title"
                        type="text"
                        name="title"
                        class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all"
                        placeholder="Contoh: Rapat Evaluasi Mingguan"
                    >

                    <span id="error-title" class="text-red-500 text-xs mt-1 font-bold"></span>

                </div>


                {{-- JENIS --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jenis Pengumuman
                    </label>

                    <select
                        id="type"
                        name="type"
                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer"
                    >

                        <option value="">
                            Pilih Jenis Pengumuman
                        </option>

                        <option value="info">
                            Info Biasa
                        </option>

                        <option value="penting">
                            Penting / Urgent
                        </option>

                    </select>

                    <span id="error-type" class="text-red-500 text-xs mt-1 font-bold"></span>

                </div>


                {{-- CONTENT --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Isi Pengumuman
                    </label>

                    <textarea
                        id="content"
                        name="content"
                        rows="4"
                        class="w-full bg-white rounded-xl border border-gray-300 p-3.5 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all custom-scrollbar"
                        placeholder="Tuliskan isi pengumuman di sini..."
                    ></textarea>

                    <span id="error-content" class="text-red-500 text-xs mt-1 font-bold"></span>

                </div>


                <button
                    type="submit"
                    class="cursor-pointer btn-submit-pengumuman w-full h-11 rounded-xl bg-[#0071BC] hover:bg-blue-600 transition text-white font-semibold text-sm shadow-sm hover:shadow"
                >
                    Kirim Pengumuman
                </button>

            </form>

        </div>

    </div>

@endif


{{-- COMPONENTS --}}
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script>


<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #E2E8F0;
        border-radius: 20px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #CBD5E1;
    }

    /* Smooth transitions */
    .group:hover .group-hover\:rotate-6 {
        transform: rotate(6deg);
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    function openModalPengumuman() {

        const modal = document.getElementById('pengumumanModal');
        const content = modal.querySelector('div');

        modal.classList.remove('hidden');

        setTimeout(() => {
            modal.classList.replace('opacity-0', 'opacity-100');
            content.classList.replace('scale-95', 'scale-100');
        }, 10);
    }


    function closeModalPengumuman() {

        const modal = document.getElementById('pengumumanModal');
        const content = modal.querySelector('div');

        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');

            document
                .getElementById('formPengumumanKepsek')
                .reset();

        }, 300);
    }


    async function submitPengumumanKepsek(event) {

        event.preventDefault();

        const form = event.target;
        const btn = form.querySelector('.btn-submit-pengumuman');

        const originalText = btn.innerHTML;

        btn.disabled = true;

        btn.innerHTML =
            `<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...`;

        const token = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');

        try {

            const response = await fetch(
                "{{ route('lms.kepsek.pengumuman.store', [
                    'role' => Auth::user()->role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId
                ]) }}",
                {
                    method: "POST",

                    headers: {
                        "X-CSRF-TOKEN": token,
                        "Accept": "application/json"
                    },

                    body: new FormData(form)
                }
            );

            const result = await response.json();


            document
                .querySelectorAll("[id^='error-']")
                .forEach(el => el.innerHTML = "");


            if (response.status === 422) {

                Object.keys(result.errors).forEach(function(key) {

                    let id = key
                        .replace('.', '-')
                        .replace('[]', '');

                    if (key === 'target') {
                        id = 'target';
                    }

                    const error =
                        document.getElementById('error-' + id);

                    if (error) {
                        error.innerHTML =
                            result.errors[key][0];
                    }

                });


                document
                    .querySelectorAll('input[name="target[]"]')
                    .forEach(function(checkbox) {

                        checkbox.addEventListener(
                            'change',
                            function() {

                                const errorTarget =
                                    document.getElementById(
                                        'error-target'
                                    );

                                if (
                                    document.querySelectorAll(
                                        'input[name="target[]"]:checked'
                                    ).length > 0
                                ) {
                                    errorTarget.innerHTML = '';
                                }

                            }
                        );

                    });


                btn.disabled = false;
                btn.innerHTML = originalText;

                return;
            }


            if (!response.ok) {
                throw new Error(
                    result.message ?? 'Terjadi kesalahan'
                );
            }


            closeModalPengumuman();


            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: result.message,
                timer: 1800,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });


        } catch (error) {

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.message
            });

        } finally {

            btn.disabled = false;
            btn.innerHTML = originalText;

        }

    }
</script>


<script>
    function openDashboardPerangkatGuru() {

        const modal =
            document.getElementById(
                'modalDashboardPerangkatGuru'
            );

        const content =
            document.getElementById(
                'dashboardModalContent'
            );

        if (!modal || !content) {
            return;
        }


        modal.classList.remove('hidden');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );


        document.body.classList.add(
            'overflow-hidden'
        );


        requestAnimationFrame(() => {

            content.classList.remove(
                'scale-95',
                'opacity-0'
            );

            content.classList.add(
                'scale-100',
                'opacity-100'
            );

        });

    }


    function closeDashboardPerangkatGuru() {

        const modal =
            document.getElementById(
                'modalDashboardPerangkatGuru'
            );

        const content =
            document.getElementById(
                'dashboardModalContent'
            );

        if (!modal || !content) {
            return;
        }


        content.classList.remove(
            'scale-100',
            'opacity-100'
        );

        content.classList.add(
            'scale-95',
            'opacity-0'
        );


        setTimeout(() => {

            modal.classList.add(
                'hidden'
            );

            modal.setAttribute(
                'aria-hidden',
                'true'
            );

            document.body.classList.remove(
                'overflow-hidden'
            );

        }, 200);

    }


    document.addEventListener(
        'keydown',
        function(event) {

            if (event.key !== 'Escape') {
                return;
            }


            const modal =
                document.getElementById(
                    'modalDashboardPerangkatGuru'
                );


            if (
                modal &&
                !modal.classList.contains(
                    'hidden'
                )
            ) {

                closeDashboardPerangkatGuru();

            }

        }
    );
</script>
