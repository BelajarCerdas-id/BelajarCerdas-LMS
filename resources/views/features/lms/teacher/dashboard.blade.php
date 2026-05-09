@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda Guru'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">

        <div class="p-6 md:p-8">
            
            {{-- HEADER BERANDA --}}
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                        Halo, Bapak/Ibu <span class="text-indigo-600">{{ Auth::user()->SchoolStaffProfile->nama_lengkap ?? Auth::user()->name ?? 'Guru' }}</span> 👋
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">Selamat datang di ruang kendali kelas Anda hari ini.</p>
                </div>

                <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-indigo-100 flex items-center gap-4 shrink-0 self-end md:self-auto ml-auto">
                    <div class="w-12 h-12 bg-indigo-600 rounded-xl flex flex-col items-center justify-center text-white shadow-md shadow-indigo-100">
                        <span class="text-[10px] font-bold uppercase leading-none">{{ \Carbon\Carbon::now()->translatedFormat('M') }}</span>
                        <span class="text-lg font-black leading-none">{{ \Carbon\Carbon::now()->translatedFormat('d') }}</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">{{ \Carbon\Carbon::now()->translatedFormat('l') }}</p>
                        <p class="text-sm font-extrabold text-gray-800">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- KOTAK STATISTIK --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-orange-50 flex items-center gap-5 hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-inner border border-orange-100">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 mb-1">Sesi Mengajar Hari Ini</p>
                        <h4 class="text-2xl font-extrabold text-orange-900">{{ $totalJadwalHariIni ?? 0 }} <span class="text-sm font-medium text-gray-400">Sesi</span></h4>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-blue-50 flex items-center gap-5 hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-inner border border-blue-100">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 mb-1">Total Kelas Diampu</p>
                        <h4 class="text-2xl font-extrabold text-blue-900">{{ $totalKelas ?? 0 }} <span class="text-sm font-medium text-gray-400">Kelas</span></h4>
                    </div>
                </div>
            </div>

            {{-- KONTEN UTAMA --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10">
                
                {{-- KOLOM KIRI (Jadwal, Polling, Pengawasan) --}}
                <div class="xl:col-span-2 flex flex-col gap-8">
                    
                    {{-- 1. JADWAL MENGAJAR DENGAN NAVIGASI NEXT/PREV --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-indigo-50 p-6 md:p-8 flex flex-col max-h-[600px]">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 gap-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center shadow-inner shadow-indigo-100/50 font-bold text-lg">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-indigo-900 text-lg leading-tight">Jadwal Mengajar</h3>
                                    <p class="text-xs text-indigo-600/70 font-medium">Sesuai Kalender</p>
                                </div>
                            </div>

                            {{-- NAVIGASI NEXT PREV HARI --}}
                            @php
                                $currentDateObj = \Carbon\Carbon::parse($tanggalDipilih ?? request('date', date('Y-m-d')));
                                $prevDay = $currentDateObj->copy()->subDay()->format('Y-m-d');
                                $nextDay = $currentDateObj->copy()->addDay()->format('Y-m-d');
                                
                                // Cek apakah tanggal yang dipilih adalah hari ini
                                $isToday = $currentDateObj->isToday();
                                // Format tanggal jadi dd/mm (contoh: 08/05)
                                $tanggalFormat = $currentDateObj->format('d/m');
                            @endphp

                            <div class="flex items-center gap-2">
                                <div class="flex bg-slate-100 p-1 rounded-xl mr-2">
                                    <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'date' => $prevDay]) }}" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-white hover:text-indigo-600 hover:shadow-sm rounded-lg transition-all" title="Hari Sebelumnya">
                                        <i class="fas fa-chevron-left text-xs"></i>
                                    </a>
                                    <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'date' => date('Y-m-d')]) }}" class="px-3 flex items-center justify-center text-[10px] font-bold text-slate-500 hover:text-indigo-600 transition-all uppercase" title="Kembali ke Hari Ini">
                                        Hari Ini
                                    </a>
                                    <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'date' => $nextDay]) }}" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-white hover:text-indigo-600 hover:shadow-sm rounded-lg transition-all" title="Hari Berikutnya">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                </div>
                                
                                {{-- LOGIKA TEKS HARI DINAMIS --}}
                                <span class="text-sm font-semibold text-indigo-600 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-100 shadow-sm">
                                    {{ $isToday ? 'Hari Ini' : $hariIni . ', ' . $tanggalFormat }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($jadwalMengajar ?? [] as $group)
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-5 rounded-2xl border border-indigo-100 bg-white hover:border-indigo-300 hover:shadow-lg transition-all group mb-2">
                                    <div class="flex items-start gap-5">
                                        <div class="flex flex-col gap-3 shrink-0 border-r border-gray-100 pr-5">
                                            @foreach($group->details as $waktu)
                                                <div class="text-center">
                                                    <span class="block text-sm font-bold text-gray-800">{{ $waktu->jam_mulai }}</span>
                                                    <span class="block text-[10px] text-gray-400 font-medium leading-none">{{ $waktu->jam_selesai }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="pt-1">
                                            <h4 class="font-extrabold text-gray-800 text-lg group-hover:text-indigo-700 transition-colors">
                                                {{ $group->mapel }}
                                            </h4>
                                            <p class="text-xs text-gray-500 mt-2 flex flex-wrap items-center gap-3">
                                                <span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-lg font-bold border border-indigo-100">
                                                    <i class="fas fa-users mr-1.5"></i> {{ $group->kelas }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i class="fas fa-door-open text-indigo-300 mr-1.5"></i> {{ $group->ruangan }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="relative w-full md:w-auto">
                                        <a href="{{ route('lms.teacher.class.detail', ['schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'scheduleId' => explode(',', $group->ids)[0]]) }}" class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-bold rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                                            <i class="fas fa-sign-in-alt"></i> Buka Kelas
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 px-6 text-center h-full border-2 border-dashed border-indigo-50 rounded-2xl bg-indigo-50/30">
                                    <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-full bg-white shadow-sm border border-indigo-100 text-indigo-300">
                                        <i class="fas fa-mug-hot text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-indigo-800 mb-1">Waktunya Bersantai</h3>
                                    <p class="text-indigo-600/70 text-sm font-medium">Bapak/Ibu tidak memiliki jadwal mengajar di hari {{ $hariIni }}.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    {{-- 2. RIWAYAT POLLING TERKINI --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-emerald-50 p-6 md:p-8 flex flex-col">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 gap-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner shadow-emerald-100/50 font-bold text-lg">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-emerald-900 text-lg leading-tight">Riwayat Polling Terkini</h3>
                                    <p class="text-xs text-emerald-600/70 font-medium">Tinjau hasil jajak pendapat kelas Anda</p>
                                </div>
                            </div>
                            <a href="{{ route('lms.teacherPolling.view', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800 transition-colors shrink-0">
                                Kelola Polling <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>

                        {{-- TABS NAVIGASI --}}
                        <div class="flex space-x-2 border-b border-slate-200 mb-5 shrink-0">
                            <button onclick="switchDashboardPollTab('dibuat')" id="dash-tab-btn-dibuat" class="pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 transition-colors">
                                Polling Kelas Saya
                            </button>
                            <button onclick="switchDashboardPollTab('masuk')" id="dash-tab-btn-masuk" class="pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors relative">
                                Dari Sekolah
                                @if(count($pollingDariSekolah ?? []) > 0)
                                    <span class="absolute top-0 right-2 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                                @endif
                            </button>
                        </div>

                        {{-- KONTEN TAB 1: POLLING DIBUAT (Oleh Guru Ini) --}}
                        <div id="dash-tab-content-dibuat" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @forelse($recentPolls ?? [] as $poll)
                                @php
                                    $targetAudiens = $poll->target ?? 'Semua Warga Sekolah';
                                    
                                    $namaKelas = 'Semua Kelas (Global)';
                                    if (!empty($poll->nama_kelas) && $poll->nama_kelas !== 'Semua Kelas (Global)') {
                                        $namaKelas = $poll->nama_kelas;
                                    } elseif (!empty($poll->class_id)) {
                                        $kelasObj = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $poll->class_id)->first();
                                        $namaKelas = $kelasObj ? 'Kelas ' . $kelasObj->class_name : 'Semua Kelas (Global)';
                                    }
                                    
                                    if (str_contains(strtolower($targetAudiens), 'siswa')) {
                                        $bgTarget = 'bg-blue-50 text-blue-600 border-blue-200';
                                        $iconTarget = 'fa-user-graduate';
                                    } elseif (str_contains(strtolower($targetAudiens), 'orang tua')) {
                                        $bgTarget = 'bg-purple-50 text-purple-600 border-purple-200';
                                        $iconTarget = 'fa-user-friends';
                                    } elseif (str_contains(strtolower($targetAudiens), 'guru')) {
                                        $bgTarget = 'bg-orange-50 text-orange-600 border-orange-200';
                                        $iconTarget = 'fa-chalkboard-teacher';
                                    } else {
                                        $bgTarget = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                        $iconTarget = 'fa-globe';
                                    }
                                @endphp

                                <div class="p-5 border border-emerald-100 rounded-2xl bg-white hover:shadow-md transition-all group flex flex-col">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="text-[10px] font-bold text-[#0071BC] bg-blue-50 px-2.5 py-1 rounded-md flex items-center gap-1.5 border border-blue-100 w-fit shadow-sm">
                                                <i class="fas fa-chalkboard-user"></i> {{ $namaKelas }}
                                            </span>
                                            <span class="text-[9px] font-bold {{ $bgTarget }} px-2 py-0.5 rounded-md flex items-center gap-1.5 w-fit border shadow-sm">
                                                <i class="fas {{ $iconTarget }}"></i> Untuk: {{ $targetAudiens }}
                                            </span>
                                        </div>
                                        <span class="text-[11px] font-bold text-gray-400 shrink-0">
                                            {{ \Carbon\Carbon::parse($poll->created_at)->format('d M') }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug mb-4 h-10 flex-1">{{ $poll->question }}</p>
                                    
                                    <button 
                                        onclick='openGraphModal("{!! addslashes($poll->question) !!}", "{{ $namaKelas }}", {!! $poll->chart_labels ?? "[]" !!}, {!! $poll->chart_data ?? "[]" !!})' 
                                        class="w-full py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 border border-emerald-100 hover:border-emerald-600 mt-auto">
                                        <i class="fas fa-chart-bar"></i> Lihat Grafik
                                    </button>
                                </div>
                            @empty
                                <div class="md:col-span-2 flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-emerald-50 rounded-2xl bg-emerald-50/20">
                                    <i class="fas fa-box-open text-3xl mb-3 text-emerald-200"></i>
                                    <p class="text-sm font-bold text-emerald-800">Belum Ada Polling</p>
                                    <p class="text-xs font-medium text-emerald-500 mt-1">Polling yang Anda buat akan muncul di sini.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- KONTEN TAB 2: POLLING DARI SEKOLAH --}}
                        <div id="dash-tab-content-masuk" class="hidden grid-cols-1 md:grid-cols-2 gap-5">
                            @forelse($pollingDariSekolah ?? [] as $pollSekolah)
                                @php
                                    $optionsSekolah = \App\Models\PollOption::where('poll_id', $pollSekolah->id)->get();
                                    $targetSekolah = $pollSekolah->target ?? 'Semua Warga Sekolah';

                                    $namaKelasSekolah = 'Semua Kelas (Global)';
                                    if (!empty($pollSekolah->nama_kelas) && $pollSekolah->nama_kelas !== 'Semua Kelas (Global)') {
                                        $namaKelasSekolah = $pollSekolah->nama_kelas;
                                    } elseif (!empty($pollSekolah->class_id)) {
                                        $kelasObj = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $pollSekolah->class_id)->first();
                                        $namaKelasSekolah = $kelasObj ? 'Kelas ' . $kelasObj->class_name : 'Semua Kelas (Global)';
                                    }

                                    if (str_contains(strtolower($targetSekolah), 'siswa')) {
                                        $bgTargetSekolah = 'bg-blue-50 text-blue-600 border-blue-200';
                                        $iconTargetSekolah = 'fa-user-graduate';
                                    } elseif (str_contains(strtolower($targetSekolah), 'orang tua')) {
                                        $bgTargetSekolah = 'bg-purple-50 text-purple-600 border-purple-200';
                                        $iconTargetSekolah = 'fa-user-friends';
                                    } elseif (str_contains(strtolower($targetSekolah), 'guru')) {
                                        $bgTargetSekolah = 'bg-orange-50 text-orange-600 border-orange-200';
                                        $iconTargetSekolah = 'fa-chalkboard-teacher';
                                    } else {
                                        $bgTargetSekolah = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                        $iconTargetSekolah = 'fa-globe';
                                    }
                                @endphp
                                
                                <div onclick='openVoteModal({{ $pollSekolah->id }}, "{!! addslashes($pollSekolah->question) !!}", {!! json_encode($optionsSekolah) !!}, {{ $pollSekolah->has_voted ? "true" : "false" }}, {{ $pollSekolah->voted_option_id ?? "null" }})' 
                                    class="shrink-0 p-5 border-2 border-amber-100 rounded-2xl bg-gradient-to-br {{ $pollSekolah->has_voted ? 'from-slate-50 to-white' : 'from-amber-50/30 to-white' }} hover:shadow-md transition-all cursor-pointer group relative overflow-hidden flex flex-col">
                                    
                                    <div class="absolute top-0 left-0 w-1.5 h-full {{ $pollSekolah->has_voted ? 'bg-emerald-500' : 'bg-amber-400' }}"></div>
                                    <div class="pl-2 flex-1 flex flex-col">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[10px] font-bold {{ $pollSekolah->has_voted ? 'text-emerald-700 bg-emerald-100 border-emerald-200' : 'text-amber-700 bg-amber-100 border-amber-200' }} px-2.5 py-1 rounded-md flex items-center gap-1.5 border w-fit shadow-sm">
                                                    <i class="fas fa-building"></i> Dari: {{ $pollSekolah->author_role }}
                                                </span>
                                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                                    <span class="text-[9px] font-bold {{ $bgTargetSekolah }} px-2 py-0.5 rounded-md flex items-center gap-1.5 w-fit border shadow-sm">
                                                        <i class="fas {{ $iconTargetSekolah }}"></i> Untuk: {{ $targetSekolah }}
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="text-[11px] font-bold text-gray-400 shrink-0">
                                                {{ \Carbon\Carbon::parse($pollSekolah->created_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug mb-4 h-10 mt-1 flex-1">
                                            {{ $pollSekolah->question }}
                                        </p>
                                        
                                        @if($pollSekolah->has_voted)
                                            <button type="button" class="w-full py-2.5 bg-emerald-50 text-emerald-600 text-sm font-bold rounded-xl flex items-center justify-center gap-2 border border-emerald-100 pointer-events-none mt-auto">
                                                <i class="fas fa-check-circle"></i> Sudah Mengisi
                                            </button>
                                        @else
                                            <button type="button" class="w-full py-2.5 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 border border-amber-100 hover:border-amber-600 pointer-events-none mt-auto">
                                                <i class="fas fa-vote-yea"></i> Berikan Suara <i class="fas fa-arrow-right ml-1"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="md:col-span-2 flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                                    <i class="fas fa-inbox text-3xl mb-3 text-slate-300"></i>
                                    <p class="text-sm font-bold text-slate-600">Kotak Masuk Kosong</p>
                                    <p class="text-xs font-medium text-slate-400 mt-1">Tidak ada polling masuk dari manajemen sekolah.</p>
                                </div>
                            @endforelse
                        </div>

                    </div>

                    {{-- 3. PENGAWASAN ASESMEN --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 flex flex-col">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-blue-100 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-950 text-white rounded-xl flex items-center justify-center shadow-md font-bold text-lg border border-blue-900">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-blue-950 text-lg leading-tight">Pengawasan Asesmen</h3>
                                    <p class="text-xs text-slate-500 font-medium">Log aktivitas siswa selama pengerjaan asesmen</p>
                                </div>
                            </div>
                        </div>

                        <div id="container-teacher-assessment-cheating-history" data-school-name="{{ $schoolName ?? 'sekolah' }}" data-school-id="{{ $schoolId ?? '1' }}" class="flex flex-col gap-5">
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-sliders text-blue-900"></i>
                                        <h3 class="text-sm font-bold text-blue-950">Filter Riwayat Pengawasan</h3>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
                                    <div id="container-dropdown-school-year-cheating-history" class="w-full relative [&_select]:w-full [&_select]:border-2 [&_select]:border-slate-200 [&_select]:rounded-xl [&_select]:px-4 [&_select]:py-2.5 [&_select]:text-sm [&_select]:font-medium [&_select]:text-slate-700 [&_select]:focus:ring-4 [&_select]:focus:ring-blue-900/10 [&_select]:focus:border-blue-900 [&_select]:outline-none [&_select]:transition-all [&_select]:bg-white [&_select]:cursor-pointer [&_select]:appearance-none"></div>
                                    <div id="container-dropdown-rombel-class-cheating-history" class="w-full relative [&_select]:w-full [&_select]:border-2 [&_select]:border-slate-200 [&_select]:rounded-xl [&_select]:px-4 [&_select]:py-2.5 [&_select]:text-sm [&_select]:font-medium [&_select]:text-slate-700 [&_select]:focus:ring-4 [&_select]:focus:ring-blue-900/10 [&_select]:focus:border-blue-900 [&_select]:outline-none [&_select]:transition-all [&_select]:bg-white [&_select]:cursor-pointer [&_select]:appearance-none"></div>
                                    <div id="container-dropdown-subject-teacher-cheating-history" class="w-full relative [&_select]:w-full [&_select]:border-2 [&_select]:border-slate-200 [&_select]:rounded-xl [&_select]:px-4 [&_select]:py-2.5 [&_select]:text-sm [&_select]:font-medium [&_select]:text-slate-700 [&_select]:focus:ring-4 [&_select]:focus:ring-blue-900/10 [&_select]:focus:border-blue-900 [&_select]:outline-none [&_select]:transition-all [&_select]:bg-white [&_select]:cursor-pointer [&_select]:appearance-none"></div>
                                    <div id="container-dropdown-assessment-type-cheating-history" class="w-full relative [&_select]:w-full [&_select]:border-2 [&_select]:border-slate-200 [&_select]:rounded-xl [&_select]:px-4 [&_select]:py-2.5 [&_select]:text-sm [&_select]:font-medium [&_select]:text-slate-700 [&_select]:focus:ring-4 [&_select]:focus:ring-blue-900/10 [&_select]:focus:border-blue-900 [&_select]:outline-none [&_select]:transition-all [&_select]:bg-white [&_select]:cursor-pointer [&_select]:appearance-none"></div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                <div id="grid-list-teacher-assessment-cheating-history" class="flex flex-col gap-4"></div>
                                <div id="empty-message-teacher-assessment-cheating-history" class="py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white hidden">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-14 h-14 bg-blue-50 text-blue-400 rounded-full flex items-center justify-center text-2xl mb-3 border border-blue-100 shadow-sm">
                                            <i class="fas fa-shield"></i>
                                        </div>
                                        <p class="text-sm font-bold text-blue-950">Aman & Terkendali</p>
                                        <p class="text-xs font-medium text-slate-500 mt-1">Tidak ada pelanggaran yang tercatat pada kelas ini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- KOLOM KANAN (Agenda & Pengumuman) --}}
                <div class="xl:col-span-1 flex flex-col gap-8">
                    
                    {{-- AGENDA KALENDER --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col max-h-[600px] h-full">
                        @php
                            $selectedDate = request('date', \Carbon\Carbon::today()->format('Y-m-d'));
                            $selectedCarbon = \Carbon\Carbon::parse($selectedDate);
                            $prevWeek = $selectedCarbon->copy()->subWeek()->format('Y-m-d');
                            $nextWeek = $selectedCarbon->copy()->addWeek()->format('Y-m-d');
                        @endphp

                        <div class="flex items-center justify-between mb-5 shrink-0">
                            <div class="flex items-center gap-2">
                                <i class="far fa-calendar-alt text-[#0071BC] text-lg"></i>
                                <h3 class="font-bold text-[#0071BC] uppercase tracking-wider text-sm">Event & Agenda</h3>
                            </div>
                            <div class="flex gap-4">
                                <a href="{{ request()->fullUrlWithQuery(['date' => $prevWeek]) }}" class="text-gray-400 hover:text-[#0071BC] transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['date' => $nextWeek]) }}" class="text-gray-400 hover:text-[#0071BC] transition">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="flex justify-between gap-1 sm:gap-2 shrink-0 mb-6">
                            @php
                                $startOfWeek = \Carbon\Carbon::parse($tanggalDipilih ?? $selectedDate)->startOfWeek();
                                $hariLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                            @endphp
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $loopDate = $startOfWeek->copy()->addDays($i);
                                    $isSel = $loopDate->format('Y-m-d') === ($tanggalDipilih ?? $selectedDate);
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['date' => $loopDate->format('Y-m-d')]) }}" 
                                   class="flex flex-col items-center justify-center w-full py-2 border rounded-md transition-colors cursor-pointer 
                                   {{ $isSel ? 'bg-[#0071BC] border-[#0071BC] text-white shadow-md' : 'bg-white border-[#0071BC] text-[#0071BC] hover:bg-blue-50' }}">
                                    <span class="text-sm font-medium">{{ $loopDate->format('d') }}</span>
                                    <span class="text-[9px] font-medium mt-0.5 uppercase">{{ $hariLabels[$i] }}</span>
                                </a>
                            @endfor
                        </div>

                        <div class="w-full h-8 bg-[#0071BC] mb-2 rounded-sm shrink-0 flex items-center justify-center">
                            <span class="text-[10px] font-bold text-white uppercase tracking-widest">Detail Agenda 7 Hari</span>
                        </div>

                        <div class="flex flex-col flex-1 overflow-y-auto custom-scrollbar mt-2 pr-1">
                            @php
                                $endOfWeek = $startOfWeek->copy()->addDays(6);
                                $weeklyEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
                                                ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                                                ->orderBy('date', 'asc')
                                                ->get();
                            @endphp

                            @forelse($weeklyEvents ?? [] as $event)
                                <div class="mb-3 p-3 border-l-4 hover:bg-gray-50 transition rounded-r bg-white shadow-sm flex flex-col gap-1 {{ $event->date == date('Y-m-d') ? 'border-indigo-500 bg-indigo-50/30' : '' }}" style="border-color: {{ $event->color ?? '#0071BC' }}">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-black text-gray-800 text-[10px] uppercase tracking-widest">
                                            {{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d F') }}
                                        </span>
                                        @if($event->date == date('Y-m-d'))
                                            <span class="bg-indigo-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full uppercase animate-pulse">Hari Ini</span>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-700 block font-bold leading-tight">
                                        {{ $event->title }}
                                    </span>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center py-10 opacity-50">
                                    <i class="far fa-calendar-times text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-gray-400 text-sm font-medium">Tidak ada Agenda/Event</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- PAPAN PENGUMUMAN --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col max-h-[600px]">
                        <div class="flex items-center justify-between mb-5 border-b border-gray-100 pb-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center shadow-inner font-bold text-lg border border-amber-100">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-amber-900 text-lg leading-tight">Pengumuman</h3>
                                    <p class="text-xs text-amber-600/70 font-medium">Informasi masuk & keluar</p>
                                </div>
                            </div>
                            <button onclick="openPengumumanGlobalModal()" class="bg-amber-50 border border-amber-100 text-amber-600 hover:bg-amber-500 hover:text-white px-3 py-2 rounded-xl font-bold text-xs transition-colors shadow-sm flex items-center gap-2">
                                <i class="fas fa-plus"></i> Buat Info
                            </button>
                        </div>

                        {{-- TABS PENGUMUMAN --}}
                        <div class="flex space-x-2 border-b border-slate-200 mb-4 shrink-0">
                            <button onclick="switchPengumumanTab('masuk')" id="tab-pengumuman-masuk" class="pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-amber-500 text-amber-600 transition-colors relative">
                                Kotak Masuk
                                @if(count($pengumumanDariSekolah ?? []) > 0)
                                    <span class="absolute top-0 right-2 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                                @endif
                            </button>
                            <button onclick="switchPengumumanTab('keluar')" id="tab-pengumuman-keluar" class="pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors">
                                Terkirim
                            </button>
                        </div>

                        {{-- CONTENT TAB: KOTAK MASUK --}}
                        <div id="content-pengumuman-masuk" class="flex flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            @forelse($pengumumanDariSekolah ?? [] as $info)
                                <div class="p-4 border border-slate-100 rounded-xl hover:border-amber-200 hover:bg-amber-50/30 transition-colors group cursor-pointer">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100"><i class="fas fa-building mr-1"></i> Sekolah</span>
                                        <span class="text-[10px] font-medium text-slate-400">{{ \Carbon\Carbon::parse($info->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-amber-700 transition-colors">{{ $info->title ?? $info->judul }}</h4>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center text-center py-10 opacity-50">
                                    <i class="fas fa-envelope-open text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-slate-400 text-sm font-medium">Belum ada pengumuman masuk</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- CONTENT TAB: TERKIRIM --}}
                        <div id="content-pengumuman-keluar" class="hidden flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            @forelse($pengumumanKeSiswa ?? [] as $info)
                                @php
                                    $namaKelasTujuan = 'Semua Kelas (Global)';
                                    if (!empty($info->target_class_id)) {
                                        $kelasObj = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $info->target_class_id)->first();
                                        $namaKelasTujuan = $kelasObj ? 'Kelas ' . $kelasObj->class_name : 'Kelas Dihapus';
                                    }
                                @endphp
                                <div class="p-4 border border-slate-100 rounded-xl hover:border-[#0071BC]/30 hover:bg-blue-50/30 transition-colors group cursor-pointer flex flex-col">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex gap-1.5 flex-wrap">
                                            <span class="text-[9px] font-bold text-white bg-[#0071BC] px-2 py-0.5 rounded uppercase tracking-wider shadow-sm"><i class="fas fa-paper-plane mr-1"></i> Terkirim</span>
                                            <span class="text-[9px] font-bold text-[#0071BC] bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase tracking-wider"><i class="fas fa-chalkboard-user mr-1"></i> {{ $namaKelasTujuan }}</span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-400 shrink-0">{{ \Carbon\Carbon::parse($info->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-[#0071BC] transition-colors mt-1 line-clamp-2 leading-snug">{{ $info->title ?? $info->judul }}</h4>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center text-center py-10 opacity-50">
                                    <i class="fas fa-paper-plane text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-slate-400 text-sm font-medium">Belum ada pengumuman yang dikirim</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center bg-gray-50">
        <i class="fas fa-lock text-5xl text-red-400 mb-4"></i>
        <p class="font-bold text-xl text-red-500">Akses Ditolak</p>
        <p class="text-gray-500">Halaman ini khusus untuk Guru.</p>
    </div>
@endif

{{-- MODAL GRAFIK HASIL POLLING (DIBUAT GURU) --}}
<div id="graph-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 transform scale-95 transition-transform duration-300 overflow-hidden" id="graph-modal-content">
        <div class="bg-[#0071BC] px-6 py-4 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-chart-pie"></i> Hasil Polling
            </h3>
            <button onclick="closeGraphModal()" class="text-blue-100 hover:text-white w-8 h-8 rounded-full bg-blue-800/30 flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 md:p-8">
            <div class="mb-6">
                <span id="modal-class-name" class="text-xs font-bold text-[#0071BC] bg-blue-50 px-2.5 py-1 rounded-md uppercase tracking-wider mb-2 flex items-center gap-1.5 w-fit border border-blue-100">
                    <i class="fas fa-users"></i> Kelas
                </span>
                <p id="modal-question" class="text-lg font-extrabold text-gray-800 leading-snug"></p>
            </div>
            
            <div class="relative h-64 w-full">
                <canvas id="pollChart"></canvas>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button onclick="closeGraphModal()" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition-colors shadow-sm">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

{{-- MODAL VOTING (MENGISI POLLING DARI SEKOLAH) --}}
<div id="vote-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0 px-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 transform scale-95 transition-transform duration-300 overflow-hidden" id="vote-modal-content">
        <div class="bg-amber-500 px-6 py-4 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-vote-yea"></i> Berikan Suara Anda
            </h3>
            <button onclick="closeVoteModal()" class="text-amber-100 hover:text-white w-8 h-8 rounded-full bg-amber-600/30 flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 md:p-8">
            <div class="mb-6">
                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md uppercase tracking-wider mb-2 flex items-center gap-1.5 w-fit border border-amber-100">
                    <i class="fas fa-question-circle"></i> Pertanyaan
                </span>
                <p id="vote-question-text" class="text-lg font-extrabold text-gray-800 leading-snug"></p>
            </div>
            
            <form id="form-submit-vote">
                <input type="hidden" id="vote-poll-id">
                <div id="vote-options-container" class="space-y-3 mb-8 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeVoteModal()" class="flex-1 py-3 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-bold rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-vote" class="flex-1 py-3 bg-amber-500 text-white hover:bg-amber-600 text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Suara
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PENGUMUMAN (DARI BERANDA) --}}
<div id="pengumumanGlobalModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[90vh]" id="pengumumanGlobalContent">
        <div class="bg-amber-500 p-6 text-white flex justify-between items-center shrink-0">
            <h3 class="font-bold text-lg"><i class="fas fa-bullhorn mr-2"></i> Buat Pengumuman</h3>
            <button type="button" onclick="closePengumumanGlobalModal()" class="hover:rotate-90 transition-transform"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="formPengumumanGlobal" onsubmit="submitPengumumanGlobal(event)" class="p-6 space-y-4 overflow-y-auto custom-scrollbar">
            <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">

            <div class="w-full px-4 py-3 bg-amber-50 border border-amber-100 rounded-xl text-amber-700 text-xs font-bold flex items-center gap-2 mb-2">
                <i class="fas fa-info-circle text-amber-500"></i> Pilih kelas tujuan. Centang 'Semua Kelas' untuk mengirim info global.
            </div>

            {{-- OPSI PILIH KELAS DENGAN CHECKBOX --}}
            <div class="mb-2">
                <label class="block text-sm font-bold text-slate-700 mb-2">Target Kelas</label>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 max-h-40 overflow-y-auto custom-scrollbar space-y-1">
                    
                    <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition-colors border border-transparent hover:border-slate-200">
                        <input type="checkbox" id="checkAllClasses" class="w-4 h-4 text-amber-500 border-slate-300 rounded focus:ring-amber-500" checked>
                        <span class="text-sm font-extrabold text-slate-800">Semua Kelas (Global)</span>
                    </label>
                    
                    <hr class="border-slate-200 my-1">
                    
                    <div id="class-checkbox-container" class="space-y-1">
                        {{-- 
                            Pastikan variabel $daftarKelas dikirim dari Controller TeacherInformationController/LmsController 
                            yang berisi daftar kelas yang diajar oleh guru ini.
                        --}}
                        @forelse($daftarKelas ?? [] as $kelas)
                            <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition-colors border border-transparent hover:border-slate-200">
                                <input type="checkbox" name="class_id[]" value="{{ $kelas->id }}" class="w-4 h-4 text-amber-500 border-slate-300 rounded focus:ring-amber-500 class-checkbox" checked>
                                <span class="text-sm font-semibold text-slate-700">Kelas {{ $kelas->class_name ?? $kelas->nama_kelas }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-slate-500 italic p-2">Data kelas belum tersedia.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Judul Pengumuman</label>
                <input type="text" name="title" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500 outline-none" placeholder="Contoh: Info Tugas Kelompok">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Pengumuman</label>
                <select name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500 outline-none bg-white text-sm text-slate-700 cursor-pointer">
                    <option value="info">Info Biasa</option>
                    <option value="penting">Penting / Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Isi Pengumuman</label>
                <textarea name="content" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500 outline-none custom-scrollbar text-sm" rows="3" placeholder="Tuliskan isi pengumuman di sini..."></textarea>
            </div>
            
            <div class="pt-2 shrink-0">
                <button type="submit" class="w-full py-3 bg-amber-500 text-white font-bold rounded-xl shadow-md shadow-amber-200 hover:bg-amber-600 transition-all btn-submit-pengumuman">Kirim Pengumuman</button>
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
    // JS UNTUK SWITCH TAB PENGUMUMAN
    function switchPengumumanTab(tab) {
        const btnMasuk = document.getElementById('tab-pengumuman-masuk');
        const btnKeluar = document.getElementById('tab-pengumuman-keluar');
        const contentMasuk = document.getElementById('content-pengumuman-masuk');
        const contentKeluar = document.getElementById('content-pengumuman-keluar');

        if (tab === 'masuk') {
            btnMasuk.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-amber-500 text-amber-600 transition-colors relative";
            btnKeluar.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors";
            contentMasuk.classList.remove('hidden');
            contentMasuk.classList.add('flex');
            contentKeluar.classList.add('hidden');
            contentKeluar.classList.remove('flex');
        } else {
            btnKeluar.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-[#0071BC] text-[#0071BC] transition-colors";
            btnMasuk.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors relative";
            contentKeluar.classList.remove('hidden');
            contentKeluar.classList.add('flex');
            contentMasuk.classList.add('hidden');
            contentMasuk.classList.remove('flex');
        }
    }

    // JS UNTUK SWITCH TAB POLLING DI DASHBOARD
    function switchDashboardPollTab(tab) {
        const btnDibuat = document.getElementById('dash-tab-btn-dibuat');
        const btnMasuk = document.getElementById('dash-tab-btn-masuk');
        const contentDibuat = document.getElementById('dash-tab-content-dibuat');
        const contentMasuk = document.getElementById('dash-tab-content-masuk');

        if (tab === 'dibuat') {
            btnDibuat.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 transition-colors";
            btnMasuk.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors relative";
            contentDibuat.classList.remove('hidden');
            contentDibuat.classList.add('grid');
            contentMasuk.classList.add('hidden');
            contentMasuk.classList.remove('grid');
        } else {
            btnMasuk.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 transition-colors relative";
            btnDibuat.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors";
            contentMasuk.classList.remove('hidden');
            contentMasuk.classList.add('grid');
            contentDibuat.classList.add('hidden');
            contentDibuat.classList.remove('grid');
        }
    }

    // ==========================================
    // LOGIKA MODAL GRAFIK (TAB KELAS SAYA)
    // ==========================================
    let currentChart = null; 

    function openGraphModal(question, className, labelsArr, dataArr) {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        
        document.getElementById('modal-question').textContent = question;
        document.getElementById('modal-class-name').innerHTML = `<i class="fas fa-users"></i> ` + className;

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
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); 
    }

    function renderChart(labels, data) {
        const ctx = document.getElementById('pollChart').getContext('2d');

        if (currentChart) {
            currentChart.destroy();
        }

        const totalVotes = data.reduce((a, b) => a + b, 0);
        
        currentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: data,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)', 
                        'rgba(0, 113, 188, 0.8)',  
                        'rgba(245, 158, 11, 0.8)', 
                        'rgba(239, 68, 68, 0.8)', 
                        'rgba(139, 92, 246, 0.8)'  
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(0, 113, 188)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6, 
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Suara';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: totalVotes === 0 ? 5 : undefined,
                        ticks: { stepSize: 1, font: { family: "'Inter', sans-serif" } },
                        grid: { color: 'rgba(241, 245, 249, 1)', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { family: "'Inter', sans-serif", weight: '500' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ==========================================
    // LOGIKA MODAL VOTING (TAB DARI SEKOLAH)
    // ==========================================
    function openVoteModal(pollId, question, options, hasVoted, votedOptionId) {
        const modal = document.getElementById('vote-modal');
        const content = document.getElementById('vote-modal-content');
        
        document.getElementById('vote-poll-id').value = pollId;
        document.getElementById('vote-question-text').textContent = question;
        
        const container = document.getElementById('vote-options-container');
        container.innerHTML = ''; 
        
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const submitBtn = document.getElementById('btn-submit-vote');
        
        if (hasVoted) {
            const selectedOpt = options.find(opt => opt.id == votedOptionId);
            
            if (selectedOpt) {
                const text = selectedOpt.option_text || selectedOpt.text || selectedOpt.name;
                const div = document.createElement('div');
                div.className = 'flex items-center gap-4 p-5 rounded-2xl border-2 border-emerald-200 bg-emerald-50 mb-2 cursor-default';
                div.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white text-lg flex items-center justify-center shrink-0 shadow-md">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-0.5">Jawaban Anda</span>
                        <span class="text-sm font-bold text-slate-800">${text}</span>
                    </div>
                `;
                container.appendChild(div);
            }
            submitBtn.style.display = 'none';
        } else {
            if (options && options.length > 0) {
                options.forEach((opt, index) => {
                    const text = opt.option_text || opt.text || opt.name || opt;
                    const div = document.createElement('label');
                    
                    div.className = 'flex items-center gap-3 p-4 rounded-xl border-2 border-slate-100 bg-white shadow-sm cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition-all group mb-2';
                    div.innerHTML = `
                        <input type="radio" name="vote_option_id" value="${opt.id}" class="w-4 h-4 text-amber-500 focus:ring-amber-400 border-slate-300" required>
                        <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 group-hover:bg-amber-100 group-hover:text-amber-600 text-xs font-bold flex items-center justify-center shrink-0 transition-colors">
                            ${alphabet[index] || (index + 1)}
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-amber-700 transition-colors">${text}</span>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<p class="text-sm text-slate-500 italic px-2">Tidak ada opsi jawaban tersedia.</p>';
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
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); 
    }

    // SUBMIT JAWABAN GURU VIA AJAX
    document.getElementById('form-submit-vote').onsubmit = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-vote');
        const originalText = btn.innerHTML;
        
        const pollId = document.getElementById('vote-poll-id').value;
        const selectedOption = document.querySelector('input[name="vote_option_id"]:checked');

        if (!selectedOption) {
            Swal.fire({icon: 'warning', title: 'Pilih Jawaban', text: 'Silakan pilih salah satu opsi terlebih dahulu.', confirmButtonColor: '#F59E0B'});
            return;
        }

        const optionId = selectedOption.value;

        btn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Mengirim...`;
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
                body: JSON.stringify({ 
                    poll_id: pollId,
                    option_id: optionId
                })
            });

            const result = await response.json();
            
            if (!response.ok || !result.success) {
                throw new Error(result.message || "Gagal mengirim suara");
            }

            closeVoteModal();
            
            Swal.fire({icon: 'success', title: 'Berhasil!', text: result.message, confirmButtonColor: '#F59E0B', timer: 2000, showConfirmButton: false})
                .then(() => window.location.reload());
            
        } catch (error) {
            Swal.fire({icon: 'warning', title: 'Perhatian', text: error.message, confirmButtonColor: '#F59E0B'});
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    // ================= FUNGSI PENGUMUMAN GLOBAL (BERANDA) =================
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
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...`; 
        btn.disabled = true;

        let csrfToken = document.querySelector('meta[name="csrf-token"]');
        let token = csrfToken ? csrfToken.getAttribute('content') : '';

        try {
            // Sesuaikan URL ini dengan routing yang sudah dibuat di web.php (TeacherInformationController@storePengumuman)
            const response = await fetch("{{ route('lms.teacher.pengumuman.store', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: new FormData(form) 
            });
            
            const result = await response.json();
            
            if(result.success || response.ok) {
                closePengumumanGlobalModal();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message || 'Pengumuman ke semua siswa terkirim!', timer: 2000, showConfirmButton: false }).then(() => { window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Terjadi kesalahan' });
                btn.innerHTML = originalText; btn.disabled = false;
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: "Terjadi kesalahan jaringan." });
            btn.innerHTML = originalText; btn.disabled = false;
        }
    }

    // ================= LOGIKA CHECKBOX KELAS =================
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAllClasses');
        const classCheckboxes = document.querySelectorAll('.class-checkbox');

        if(checkAll) {
            // Jika "Semua Kelas" dicentang/dihapus centangnya
            checkAll.addEventListener('change', function() {
                classCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });

            // Jika salah satu kelas dihapus centangnya, hapus juga centang "Semua Kelas"
            classCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(classCheckboxes).every(c => c.checked);
                    checkAll.checked = allChecked;
                });
            });
        }
    });
</script>