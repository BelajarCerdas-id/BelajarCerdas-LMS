@include('components/sidebar-beranda', ['headerSideNav' => 'Dashboard Kesiswaan'])

@if (Auth::user()->role == 'Wakil Kesiswaan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out overflow-x-hidden">

        <div class="p-4 sm:p-6 md:p-8 xl:p-10 mx-auto">

            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="space-y-8">

                {{-- HERO SECTION --}}
                <div class="relative overflow-hidden rounded-4xl md:rounded-[2.5rem] bg-[#0071BC] text-white p-6 sm:p-8 md:p-10 shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
                    style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}'); background-size: cover; background-position: center;">
    
                    <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute bottom-0 right-20 w-40 h-40 rounded-full bg-cyan-300/10 blur-3xl"></div>
    
                    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-8">

                        <div class="w-full">

                            <div class="flex flex-wrap items-center gap-3 mb-4">

                                <div class="px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-[10px] sm:text-xs font-bold uppercase tracking-widest">
                                    Sistem Pemantauan Siswa Aktif
                                </div>

                                <div class="flex items-center gap-2 text-xs sm:text-sm text-blue-100">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                    Pemantauan Waktu Nyata
                                </div>

                            </div>

                            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight tracking-tight">
                                Dashboard Kesiswaan
                            </h1>

                            <p class="mt-4 text-blue-100 max-w-2xl leading-relaxed text-sm md:text-base font-medium">
                                Pantau kondisi emosional siswa, hasil refleksi harian, serta berbagai aktivitas kesiswaan secara terpadu dalam satu pusat informasi yang modern dan interaktif.
                            </p>

                            <div class="flex flex-wrap items-center gap-3 mt-6">

                                <a href="{{ route('lms.student-vice-principal.create.reflection.view', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                    'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                ]) }}">

                                    <button class="px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 font-bold text-sm 
                                        hover:bg-white/20 transition-all duration-300 cursor-pointer">

                                        <i class="fas fa-plus-circle mr-2"></i>
                                        Buat Refleksi Siswa

                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
    
                {{-- GRID CONTENT --}}
                <div class="grid grid-cols-1 2xl:grid-cols-12 gap-8">
    
                    {{-- LEFT CONTENT --}}
                    <div class="2xl:col-span-12 space-y-8 min-w-0">
    
                        {{-- CHART SECTION --}}
                        <div class="bg-white rounded-4xl md:rounded-[2.5rem] p-5 md:p-8 shadow-sm border border-gray-300">
    
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
    
                                <div>
                                    <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">
                                        Trend Kondisi Siswa
                                    </h3>
    
                                    <p class="text-slate-500 mt-1 text-sm font-medium">
                                        Statistik refleksi dan kondisi emosional siswa.
                                    </p>
                                </div>
    
                                <div class="flex flex-wrap items-center gap-3">

                                    <!-- Periode -->
                                    <select id="chartPeriod"
                                        class="h-11 px-4 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm 
                                        transition-all duration-200 hover:border-slate-300 focus:outline-none focus:ring-0 focus:border-slate-300 cursor-pointer
                                        hidden">

                                        <option value="daily">Harian</option>
                                        <option value="weekly">Mingguan</option>
                                        <option value="monthly" selected>Bulanan</option>
                                        <option value="yearly">Tahunan</option>

                                    </select>

                                    <!-- Bulan -->
                                    <select id="chartMonth"
                                        class="hidden h-11 px-4 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm 
                                        transition-all duration-200 hover:border-slate-300 focus:outline-none focus:ring-0 focus:border-slate-300 cursor-pointer">

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

                                    <!-- Tahun -->
                                    <select id="chartYear"
                                        class="hidden h-11 px-4 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm 
                                        transition-all duration-200 hover:border-slate-300 focus:outline-none focus:ring-0 focus:border-slate-300 cursor-pointer">
                                        <!-- show data in ajax -->
                                    </select>
                                </div>
                            </div>
                            
                            <!-- LOADING SCREEN -->
                            <div id="reflection-chart-loading"
                                class="flex flex-col items-center justify-center h-112 bg-slate-50 rounded-3xl border border-dashed border-slate-200">

                                <div class="relative">

                                    <div class="w-16 h-16 rounded-full border-4 border-slate-200"></div>

                                    <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-[#2563EB]"></div>

                                </div>

                                <h4 class="mt-6 text-lg font-black text-slate-800">
                                    Menyiapkan Visualisasi Data Refleksi
                                </h4>

                                <p class="text-sm text-slate-500 text-center max-w-md mt-2 leading-relaxed">
                                    Sistem sedang mengumpulkan dan menganalisis data refleksi siswa
                                    berdasarkan periode yang dipilih untuk menampilkan tren partisipasi
                                    dan kondisi emosional secara akurat.
                                </p>

                                <div class="flex items-center gap-2 mt-5 text-xs font-semibold text-slate-400">
                                    <i class="fas fa-chart-line"></i>
                                    Memproses statistik refleksi siswa...
                                </div>

                            </div>

                            <!-- EMPTY STATE -->
                            <div id="empty-message-reflection-chart"
                                class="hidden h-112 flex flex-col items-center justify-center text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200 px-8">

                                <div class="w-20 h-20 rounded-3xl bg-blue-100 flex items-center justify-center text-[#2563EB] text-3xl">
                                    <i class="fas fa-chart-line"></i>
                                </div>

                                <h4 class="mt-6 text-xl font-black text-slate-800">
                                    Belum Ada Data Refleksi
                                </h4>

                                <p class="max-w-lg mt-3 text-sm text-slate-500 leading-relaxed">
                                    Belum terdapat jawaban refleksi siswa pada periode yang dipilih.
                                    Setelah siswa mulai mengisi refleksi, statistik partisipasi dan tren
                                    kondisi emosional akan ditampilkan di sini secara otomatis.
                                </p>

                                <div class="flex items-center gap-2 mt-5 px-4 py-2 rounded-full bg-blue-50 text-[#2563EB] text-xs font-semibold">
                                    <i class="fas fa-info-circle"></i>
                                    Menunggu data refleksi siswa
                                </div>

                                <a href="{{ route('lms.student-vice-principal.create.reflection.view', [
                                    'role' => Auth::user()->role,
                                    'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                    'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                ]) }}"
                                    class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#2563EB] text-white font-bold hover:scale-105 transition-all">

                                    <i class="fas fa-plus-circle"></i>
                                    Buat Refleksi Baru

                                </a>

                            </div>

                            <div id="reflection-chart-content" class="h-80 md:h-112.5">
                                <canvas id="studentReflectionChart"></canvas>
                            </div>
                        </div>
    
                        {{-- REFLEKSI CTA --}}
                        <div class="relative overflow-hidden bg-white rounded-4xl md:rounded-[2.5rem] p-6 md:p-8 shadow-sm border border-gray-300 group hover:shadow-2xl hover:shadow-blue-100 
                            transition-all duration-500">
    
                            <div class="absolute top-0 right-0 w-52 h-52 bg-blue-100 rounded-full blur-3xl opacity-40 group-hover:scale-125 transition-all duration-700"></div>
    
                            <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-8">
    
                                <div class="max-w-xl">
    
                                    <div class="w-16 h-16 rounded-3xl bg-[#DBEAFE] text-[#2563EB] flex items-center justify-center text-3xl mb-6 shadow-inner">
                                        <i class="fas fa-book-open"></i>
                                    </div>
    
                                    <h3 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight leading-tight">
                                        Pusat Manajemen Refleksi
                                    </h3>
    
                                    <p class="text-slate-500 mt-4 leading-relaxed font-medium text-sm md:text-base">
                                        Buat refleksi baru, pantau jawaban siswa secara realtime melalui live preview,
                                        serta kelola dan telusuri riwayat refleksi sekolah dalam satu dashboard terintegrasi.
                                    </p>
                                </div>
    
                                <div class="w-full xl:w-auto">
                                    <a href="{{ route('lms.student-vice-principal.reflection-management.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                        'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                    ]) }}"
                                        class="w-full xl:w-auto justify-center inline-flex items-center gap-3 px-7 py-4 rounded-2xl bg-[#2563EB] text-white font-black shadow-xl 
                                        shadow-blue-200 hover:scale-105 transition-all duration-300">
    
                                        Kelola Refleksi
    
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
    
                        {{-- QUICK ACCESS --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-6 items-stretch">
    
                            {{-- REFLEKSI --}}
                            <a href="{{ route('lms.student-vice-principal.reflection-management-history.view', [
                                'role' => Auth::user()->role,
                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                            ]) }}"
                            class="group bg-white rounded-4xl p-6 border border-gray-300 hover:border-[#2563EB] hover:shadow-xl hover:shadow-blue-100 
                            transition-all ease-out duration-800 flex flex-col min-h-72.5">
    
                                <div class="w-14 h-14 rounded-2xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center text-2xl mb-5 
                                    group-hover:rotate-6 transition-transform">
                                    <i class="fas fa-history"></i>
                                </div>
    
                                <h4 class="font-black text-slate-800 text-lg">
                                    Riwayat Refleksi
                                </h4>
    
                                <p class="text-sm text-slate-500 mt-2 leading-relaxed flex-1">
                                    Lihat seluruh refleksi yang pernah dipublikasikan, pantau target kelas, dan tinjau histori refleksi harian sekolah.
                                </p>
    
                                {{-- BUTTON --}}
                                <div class="mt-6">
                                    <div class="w-[90%] group-hover:w-full inline-flex items-center justify-between px-4 py-3 rounded-2xl bg-[#EFF6FF] text-[#2563EB] font-black text-sm 
                                        group-hover:bg-[#2563EB] group-hover:text-white transition-all ease-out duration-800">
    
                                        <span>Lihat Riwayat</span>
    
                                        <div class="w-8 h-8 rounded-xl bg-white text-[#2563EB] flex items-center justify-center transition-all duration-500 group-hover:bg-white/20
                                            group-hover:text-white group-hover:translate-x-1.5">
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student-vice-principal/dashboard/load-student-reflection-chart.js') }}"></script> <!--- load student reflection chart ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/student-vice-principal/daily-reflection/daily-reflection-live-preview-dashboard-listener.js') }}"></script> <!--- pusher listener daily reflection dashboard ---->