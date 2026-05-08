@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda Siswa'])

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">

        <div class="p-4 sm:p-6 md:p-8">
            
            {{-- ======================================= --}}
            {{-- 1. HERO SECTION                         --}}
            {{-- ======================================= --}}
            <div class="mb-6 md:mb-8 bg-gradient-to-r from-[#0071BC] to-[#005B94] rounded-[1.5rem] md:rounded-[2rem] p-6 md:p-8 shadow-lg shadow-blue-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/4 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 right-32 translate-y-1/2 w-40 h-40 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute top-8 left-1/2 w-32 h-32 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                        Halo, {{ Auth::user()->StudentProfile->nama_lengkap ?? 'Siswa' }} 👋
                    </h1>
                    <p class="text-blue-100 mt-1 md:mt-2 text-xs md:text-sm font-medium">Mari bersiap untuk kegiatan belajarmu hari ini. Jangan lupa cek tugasmu!</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8">
                
                {{-- ======================================= --}}
                {{-- KOLOM KIRI (Lebar 2/3)                  --}}
                {{-- ======================================= --}}
                <div class="xl:col-span-2 flex flex-col gap-6 md:gap-8">
                    
                    {{-- A. JADWAL PELAJARAN --}}
                    <div class="bg-gradient-to-br from-white to-blue-50/50 rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-blue-100 p-5 md:p-8 flex flex-col max-h-[450px] md:max-h-[500px]">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 pb-4 border-b border-blue-100/50 gap-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-blue-200">
                                    <i class="fas fa-book-open text-lg"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg md:text-xl font-bold text-[#0071BC] leading-tight">Jadwal Pelajaran</h2>
                                    <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mt-0.5">Hari {{ $hariDipilih }}</p>
                                </div>
                            </div>
                            
                            {{-- NAVIGASI JADWAL --}}
                            <div class="flex items-center gap-2 md:gap-3 bg-white px-2 py-1.5 md:px-3 md:py-2 rounded-2xl border border-blue-100 shadow-sm w-full sm:w-auto justify-between sm:justify-start">
                                <a href="{{ request()->fullUrlWithQuery(['jadwal_date' => \Carbon\Carbon::parse($selectedJadwalDate)->subDay()->format('Y-m-d')]) }}" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-600 transition-colors">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </a>
                                <span class="text-xs md:text-sm font-bold text-slate-700 px-2 text-center flex-1 sm:min-w-[110px]">
                                    {{ \Carbon\Carbon::parse($selectedJadwalDate)->translatedFormat('d M Y') }}
                                </span>
                                <a href="{{ request()->fullUrlWithQuery(['jadwal_date' => \Carbon\Carbon::parse($selectedJadwalDate)->addDay()->format('Y-m-d')]) }}" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-600 transition-colors">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @if(in_array($hariDipilih, ['Sabtu', 'Minggu']))
                                <div class="flex flex-col items-center justify-center py-8 px-4 text-center bg-white/60 rounded-2xl border border-blue-100 h-full backdrop-blur-sm">
                                    <div class="w-14 h-14 md:w-16 md:h-16 mb-3 md:mb-4 flex items-center justify-center rounded-full bg-blue-50 shadow-inner border border-blue-100 text-[#0071BC]">
                                        <i class="fas fa-umbrella-beach text-xl md:text-2xl"></i>
                                    </div>
                                    <h3 class="text-base md:text-lg font-extrabold text-[#0071BC] mb-1 md:mb-2">Hore, hari ini Weekend! 🎉</h3>
                                    <p class="text-blue-600/70 max-w-sm font-medium text-[11px] md:text-xs">Tidak ada jadwal pelajaran untuk hari Sabtu dan Minggu. Selamat beristirahat!</p>
                                </div>
                            @else
                                @forelse($jadwalHariIni ?? [] as $jadwal)
                                    <div onclick="openMapelModal('{{ addslashes($jadwal['mapel']) }}', '{{ addslashes($jadwal['jam']) }}', '{{ addslashes($jadwal['guru'] ?? '-') }}', '{{ addslashes($jadwal['ruang'] ?? '-') }}', {{ $jadwal['is_break'] ? 'true' : 'false' }})" class="cursor-pointer flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 p-3 md:py-2.5 md:px-4 rounded-xl border {{ $jadwal['mapel'] == 'ISTIRAHAT' || str_contains($jadwal['mapel'], 'ISTIRAHAT') ? 'bg-gradient-to-r from-amber-50 to-white border-amber-200' : 'bg-white border-blue-50 hover:border-blue-300 hover:shadow-md transition-all group' }}">
                                        <div class="w-full sm:w-28 shrink-0">
                                            <span class="text-xs md:text-sm font-bold text-slate-700 {{ $jadwal['mapel'] == 'ISTIRAHAT' || str_contains($jadwal['mapel'], 'ISTIRAHAT') ? 'text-amber-600' : 'group-hover:text-[#0071BC] transition-colors' }} flex items-center">
                                                <i class="far fa-clock mr-1.5 text-slate-400 group-hover:text-blue-400 transition-colors"></i> {{ $jadwal['jam'] }}
                                            </span>
                                        </div>
                                        <div class="hidden sm:block w-1 h-8 {{ $jadwal['mapel'] == 'ISTIRAHAT' || str_contains($jadwal['mapel'], 'ISTIRAHAT') ? 'bg-amber-300' : 'bg-blue-100 group-hover:bg-blue-400 transition-colors' }} rounded-full"></div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-sm md:text-base {{ $jadwal['is_break'] ? 'text-amber-600' : 'text-slate-800 group-hover:text-[#0071BC] transition-colors' }} leading-tight">
                                                {{ $jadwal['mapel'] }}
                                            </h3>
                                            @if(!$jadwal['is_break'])
                                                <p class="text-[10px] md:text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                                                    <span class="flex items-center"><i class="fas fa-user-tie text-blue-400 mr-1.5"></i> {{ $jadwal['guru'] ?? '-' }}</span>
                                                    <span class="flex items-center"><i class="fas fa-door-open text-blue-400 mr-1.5"></i> {{ $jadwal['ruang'] ?? '-' }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center border-2 border-dashed border-blue-200 bg-white/50 rounded-2xl h-full">
                                        <div class="w-12 h-12 md:w-14 md:h-14 mb-3 flex items-center justify-center rounded-full bg-blue-50 shadow-inner border border-blue-100 text-blue-400">
                                            <i class="fas fa-calendar-xmark text-lg md:text-xl"></i>
                                        </div>
                                        <h3 class="text-sm md:text-base font-bold text-blue-800 mb-1">Jadwal Belum Tersedia</h3>
                                        <p class="text-blue-600/70 text-[10px] md:text-xs font-medium">Guru belum mempublikasikan jadwal pelajaran.</p>
                                    </div>
                                @endforelse
                            @endif
                        </div>
                    </div>

                    {{-- B. MODUL BELUM DIBACA --}}
                    <div class="bg-gradient-to-br from-white to-sky-50/60 rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-sky-100 p-5 md:p-8 flex flex-col">
                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-sky-100/50 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-sky-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-sky-200 font-bold text-lg">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <h3 class="font-bold text-sky-900 text-base md:text-lg">Modul Belum Dibaca 📖</h3>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="prevModule()" id="btnPrevModule" class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center rounded-full bg-white border border-sky-200 text-sky-600 hover:bg-sky-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                                    <i class="fas fa-chevron-left text-[10px] md:text-xs"></i>
                                </button>
                                <button onclick="nextModule()" id="btnNextModule" class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center rounded-full bg-white border border-sky-200 text-sky-600 hover:bg-sky-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                                    <i class="fas fa-chevron-right text-[10px] md:text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="w-full overflow-hidden">
                            <div id="moduleSlider" class="flex transition-transform duration-500 ease-out w-full">
                                @forelse($unreadModules ?? [] as $modul)
                                    <div class="w-full shrink-0 px-1">
                                        <div onclick="window.location.href='/lms/student/materi/{{ $modul->id ?? 0 }}'" class="cursor-pointer relative bg-white rounded-2xl p-4 md:p-5 border border-sky-100 shadow-sm hover:shadow-md hover:border-sky-300 hover:-translate-y-1 transition-all flex flex-col sm:flex-row gap-4 md:gap-5 items-start sm:items-center group">
                                            <div class="w-12 h-12 md:w-14 md:h-14 bg-gradient-to-br from-sky-50 to-white border border-sky-100 rounded-xl flex items-center justify-center text-sky-500 shadow-sm shrink-0 group-hover:scale-110 transition-transform">
                                                <i class="fas fa-file-pdf text-xl md:text-2xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0 w-full">
                                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-md border border-sky-100 uppercase tracking-wider">{{ $modul->mapel ?? 'Mata Pelajaran' }}</span>
                                                </div>
                                                <h4 class="font-bold text-slate-800 text-sm md:text-base leading-snug group-hover:text-sky-700 transition-colors line-clamp-1">{{ $modul->judul ?? 'Judul Modul Materi' }}</h4>
                                                <p class="text-[11px] md:text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $modul->deskripsi ?? 'Silakan baca modul ini untuk mempersiapkan materi pembelajaran selanjutnya.' }}</p>
                                            </div>
                                            <button class="mt-2 sm:mt-0 w-full sm:w-auto text-center py-2 px-5 md:py-2.5 md:px-6 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-xs md:text-sm font-bold rounded-xl shadow-md shadow-sky-200 transition-all shrink-0 pointer-events-none group-hover:from-sky-600 group-hover:to-sky-700">
                                                Buka Materi <i class="fas fa-external-link-alt ml-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="w-full shrink-0 flex flex-col items-center justify-center py-6 md:py-8 text-center bg-white/50 rounded-2xl border-2 border-dashed border-sky-200">
                                        <div class="w-12 h-12 md:w-14 md:h-14 bg-sky-50 shadow-inner border border-sky-100 rounded-full flex items-center justify-center mb-3 text-sky-500">
                                            <i class="fas fa-check-circle text-xl md:text-2xl"></i>
                                        </div>
                                        <h4 class="text-sm md:text-base font-bold text-sky-800 mb-1">Hebat!</h4>
                                        <p class="text-[11px] md:text-sm text-sky-600/70 font-medium">Semua modul pembelajaran sudah dibaca.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- C. PAPAN PENGUMUMAN --}}
                    <div class="bg-gradient-to-br from-white to-cyan-50/60 rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-cyan-100 p-5 md:p-8 flex flex-col">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-cyan-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-cyan-200 font-bold text-lg">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-cyan-900 text-base md:text-lg leading-tight">Papan Pengumuman</h3>
                                <p class="text-[10px] md:text-xs text-cyan-700/70 font-medium">Informasi penting dari guru & sekolah</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                           @forelse($pengumumanTerkini ?? [] as $info)
                                <div class="relative border {{ !$info->is_read ? 'border-blue-400 shadow-md shadow-blue-100' : 'border-cyan-100' }} rounded-2xl p-4 md:p-5 hover:shadow-lg transition-all bg-white flex flex-col group h-full">
                                    
                                    {{-- Indikator Titik Biru jika Belum Dibaca --}}
                                    @if(!$info->is_read)
                                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-600 rounded-full border-2 border-white animate-pulse z-10"></div>
                                    @endif

                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex gap-2 items-center">
                                            @if(($info->type ?? 'info') == 'penting')
                                                <span class="text-[9px] md:text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-md border border-amber-200 uppercase tracking-wider">Penting</span>
                                            @else
                                                <span class="text-[9px] md:text-[10px] font-bold text-cyan-600 bg-cyan-50 px-2 py-1 rounded-md border border-cyan-200 uppercase tracking-wider">Info Kelas</span>
                                            @endif
                                            
                                            {{-- Badge Baru --}}
                                            @if(!$info->is_read)
                                                <span class="text-[8px] font-black text-white bg-blue-600 px-1.5 py-0.5 rounded uppercase">Baru</span>
                                            @endif
                                        </div>
                                        <span class="text-[9px] md:text-[10px] font-bold text-slate-400">
                                            {{ isset($info->created_at) ? \Carbon\Carbon::parse($info->created_at)->diffForHumans() : 'Baru saja' }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-xs md:text-sm mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ $info->title ?? 'Judul Pengumuman' }}</h4>
                                    
                                    {{-- Menampilkan Nama Guru Pengirim --}}
                                    <p class="text-[9px] text-blue-500 font-bold mb-2 flex items-center gap-1">
                                        <i class="fas fa-user-chalkboard"></i> {{ $info->nama_pengirim ?? 'Guru' }}
                                    </p>

                                    <p class="text-[11px] md:text-xs text-slate-500 mb-4 line-clamp-2 flex-1">{{ $info->content ?? 'Isi pengumuman tidak tersedia.' }}</p>
                                    
                                    <button onclick="bacaPengumuman('{{ $info->id }}', '{!! addslashes($info->title) !!}', '{!! addslashes($info->content) !!}', '{{ \Carbon\Carbon::parse($info->created_at)->format('d M Y, H:i') }}', '{{ $info->type }}')" class="mt-auto w-full py-2 {{ !$info->is_read ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-cyan-50 text-cyan-700 hover:bg-cyan-600 hover:text-white' }} text-[11px] md:text-xs font-bold rounded-xl border border-transparent transition-all shadow-sm">
                                        Baca Selengkapnya
                                    </button>
                                </div>
                            @empty
                                {{-- Empty state tetap sama --}}
                            @endforelse
                        </div>
                    </div>

                    {{-- D. POLLING BERLANGSUNG (DENGAN LABEL PEMBUAT YANG JELAS) --}}
                    <div class="bg-gradient-to-br from-white to-indigo-50/60 rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-indigo-100 p-5 md:p-8">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-indigo-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-indigo-200 font-bold text-lg">
                                <i class="fas fa-person-circle-question"></i>
                            </div>
                            <h3 class="font-bold text-indigo-900 text-base md:text-lg">Polling Berlangsung 📢</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 md:gap-6">
                            @forelse($activePolls ?? [] as $poll)
                                @php
                                    $pembuat = $poll->pembuat ?? $poll->author_role ?? 'Sekolah';
                                    
                                    // LOGIKA WARNA BADGE BERDASARKAN PEMBUAT (Guru vs Kepsek/Wakasek)
                                    if (str_contains(strtolower($pembuat), 'guru')) {
                                        $bgPembuat = 'bg-blue-50 text-blue-600 border-blue-200';
                                        $iconPembuat = 'fa-chalkboard-teacher';
                                        $labelPembuat = 'Guru Mapel / Wali Kelas';
                                        $garisAksen = 'bg-blue-400';
                                    } elseif (str_contains(strtolower($pembuat), 'kepala sekolah') || str_contains(strtolower($pembuat), 'wakil')) {
                                        $bgPembuat = 'bg-amber-50 text-amber-600 border-amber-200';
                                        $iconPembuat = 'fa-building-columns';
                                        $labelPembuat = 'Manajemen Sekolah (' . $pembuat . ')';
                                        $garisAksen = 'bg-amber-400';
                                    } else {
                                        $bgPembuat = 'bg-indigo-50 text-indigo-600 border-indigo-200';
                                        $iconPembuat = 'fa-user-tie';
                                        $labelPembuat = $pembuat;
                                        $garisAksen = 'bg-indigo-400';
                                    }
                                @endphp
                                
                                <div class="border border-slate-200 rounded-2xl p-4 md:p-5 hover:border-indigo-300 hover:shadow-md transition-all bg-white flex flex-col h-full relative overflow-hidden group">
                                    {{-- Garis Aksen Sebelah Kiri --}}
                                    <div class="absolute top-0 left-0 w-1.5 h-full {{ $garisAksen }}"></div>
                                    
                                    <div class="flex justify-between items-start mb-3 pl-2">
                                        <span class="text-[9px] md:text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider {{ $bgPembuat }} border shadow-sm flex items-center gap-1.5">
                                            <i class="fas {{ $iconPembuat }}"></i> {{ $labelPembuat }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 shrink-0 ml-2">
                                            {{ isset($poll->created_at) ? \Carbon\Carbon::parse($poll->created_at)->format('d M') : '' }}
                                        </span>
                                    </div>
                                    
                                    <h4 class="font-bold text-slate-800 text-sm mb-3 md:mb-4 leading-snug pl-2">{{ $poll->pertanyaan ?? $poll->question }}</h4>
                                    
                                    <form class="mt-auto space-y-2 pl-2" onsubmit="submitSiswaVote(event, {{ $poll->id }})">
                                        @foreach($poll->options ?? $poll->opsi as $opt)
                                            <label class="flex items-center gap-2 md:gap-3 p-2.5 md:p-3 rounded-xl border border-slate-100 bg-slate-50 hover:border-indigo-300 hover:bg-indigo-50/50 shadow-sm cursor-pointer transition-all group/opt">
                                                <input type="radio" name="option_{{ $poll->id }}" value="{{ $opt->id }}" required class="w-3.5 h-3.5 md:w-4 md:h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600">
                                                <span class="text-xs md:text-sm font-semibold text-slate-600 group-hover/opt:text-indigo-700 transition-colors leading-tight">{{ $opt->text ?? $opt->option_text }}</span>
                                            </label>
                                        @endforeach
                                        <button type="submit" class="w-full mt-3 md:mt-4 py-2 md:py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 shadow-md shadow-indigo-200 font-bold text-white rounded-xl transition-all btn-submit-vote text-[11px] md:text-sm transform hover:-translate-y-0.5">
                                            Kirim Suara
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="col-span-full flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-indigo-200 bg-white/50 rounded-2xl">
                                    <div class="w-12 h-12 md:w-16 md:h-16 mb-2 md:mb-3 flex items-center justify-center rounded-full bg-indigo-50 shadow-inner border border-indigo-100 text-indigo-400">
                                        <i class="fas fa-box-open text-xl md:text-2xl"></i>
                                    </div>
                                    <h3 class="text-sm md:text-lg font-bold text-indigo-800 mb-1">Belum Ada Polling</h3>
                                    <p class="text-indigo-600/70 text-[10px] md:text-sm font-medium">Saat ini belum ada jejak pendapat yang perlu diisi.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- E. STUDENT ASSESSMENT CHEATING HISTORY (PENGAWASAN) --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 flex flex-col">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-blue-100 gap-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-950 text-white rounded-xl flex items-center justify-center shadow-md font-bold text-lg border border-blue-900">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-blue-950 text-lg leading-tight">Pengawasan Asesmen</h3>
                                    <p class="text-xs text-slate-500 font-medium">Log aktivitasmu selama pengerjaan asesmen</p>
                                </div>
                            </div>
                        </div>

                        <div id="container-student-assessment-cheating-history" class="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            <div id="grid-list-student-assessment-cheating-history" class="flex flex-col gap-4">
                            </div>

                            <div id="empty-message-student-assessment-cheating-history" class="py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white hidden">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-blue-50 text-blue-400 rounded-full flex items-center justify-center text-2xl mb-3 border border-blue-100 shadow-sm">
                                        <i class="fas fa-shield-check"></i>
                                    </div>
                                    <p class="text-sm font-bold text-blue-950">Aman & Terkendali</p>
                                    <p class="text-xs font-medium text-slate-500 mt-1">Kamu menyelesaikan asesmen dengan jujur dan tertib.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ======================================= --}}
                {{-- KOLOM KANAN (Lebar 1/3)                 --}}
                {{-- ======================================= --}}
                <div class="xl:col-span-1 flex flex-col gap-6 md:gap-8">
                    
                    {{-- A. TUGAS & PR --}}
                    <div class="bg-gradient-to-br from-white to-blue-50/60 rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-blue-100 p-5 md:p-6 flex flex-col max-h-[350px]">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-blue-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#0071BC] to-blue-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-blue-200 font-bold text-lg">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h3 class="font-bold text-blue-900 text-base md:text-lg leading-tight">Tugas & PR 📝</h3>
                        </div>

                        <div class="flex flex-col gap-3 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($pendingTasks ?? [] as $task)
                                <div onclick="openTugasSiswaModal('{{ addslashes($task->judul_tugas) }}', '{{ addslashes($task->mapel) }}', '{{ isset($task->deadline) ? \Carbon\Carbon::parse($task->deadline)->translatedFormat('l, d M Y - H:i') : 'Segera' }}', {{ $task->id }})" 
                                     class="cursor-pointer flex items-center justify-between p-3 md:p-4 rounded-xl border border-blue-100 bg-white hover:border-blue-300 hover:shadow-md hover:-translate-x-1 transition-all group">
                                    
                                    <div class="flex-1 min-w-0 pr-3">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="text-[9px] md:text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-wider border border-blue-200/60 truncate max-w-full">
                                                {{ $task->mapel ?? 'Mata Pelajaran' }}
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-slate-800 text-xs md:text-sm group-hover:text-[#0071BC] transition-colors line-clamp-1 leading-tight mb-1">
                                            {{ $task->judul_tugas ?? 'Tugas Belum Dinamai' }}
                                        </h4>
                                        <p class="text-[9px] md:text-[10px] font-bold text-slate-500 flex items-center gap-1.5">
                                            <i class="far fa-clock text-amber-500"></i> Bts: <span class="text-rose-500">{{ isset($task->deadline) ? \Carbon\Carbon::parse($task->deadline)->format('d M Y, H:i') : 'Segera' }}</span>
                                        </p>
                                    </div>
                                    
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 border border-blue-100 group-hover:bg-[#0071BC] group-hover:text-white transition-colors shadow-sm">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </div>
                                    
                                </div>
                            @empty
                                <div class="text-center py-6 flex flex-col items-center justify-center h-full bg-white/50 rounded-2xl border-2 border-dashed border-blue-200">
                                    <div class="w-12 h-12 md:w-14 md:h-14 bg-blue-50 shadow-inner border border-blue-100 rounded-full flex items-center justify-center mb-2 md:mb-3 text-blue-400">
                                        <i class="fas fa-check-double text-lg md:text-xl"></i>
                                    </div>
                                    <p class="text-[11px] md:text-sm text-blue-600/70 font-bold leading-tight">Yeay! Tidak ada PR<br>yang belum dikerjakan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- A.2. JADWAL UJIAN / QUESTION --}}
                    <div class="bg-gradient-to-br from-white to-indigo-50/60 rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-indigo-100 p-5 md:p-6 flex flex-col max-h-[350px]">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-indigo-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-indigo-200 font-bold text-lg">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <h3 class="font-bold text-indigo-900 text-base md:text-lg leading-tight">Jadwal Ujian ⏰</h3>
                        </div>

                        <div class="flex flex-col gap-3 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($jadwalUjian ?? [] as $ujian)
                                <div onclick="openUjianSiswaModal('{{ addslashes($ujian->tipe) }}', '{{ addslashes($ujian->mapel) }}', '{{ $ujian->tanggal }}', '{{ $ujian->waktu }}', '{{ $ujian->h_min }}', {{ $ujian->id }})" class="cursor-pointer flex items-start gap-3 p-3 md:p-4 rounded-xl border border-indigo-100 bg-white hover:border-indigo-300 hover:shadow-md hover:-translate-x-1 transition-all group">
                                    <div class="flex-1 w-full">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="font-bold text-slate-800 text-xs md:text-sm group-hover:text-indigo-600 transition-colors line-clamp-1 leading-tight">{{ $ujian->tipe }} {{ $ujian->mapel }}</h4>
                                            <span class="text-[9px] md:text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider {{ $ujian->h_min == 'Hari Ini' || $ujian->h_min == 'Berlangsung' ? 'bg-amber-100 text-amber-600 border border-amber-200 shadow-sm' : 'bg-indigo-100 text-indigo-600 border border-indigo-200' }}">{{ $ujian->h_min }}</span>
                                        </div>
                                        <p class="text-[9px] md:text-[11px] font-bold text-slate-500 flex items-center gap-1.5 mt-1">
                                            <i class="far fa-calendar-alt text-indigo-400"></i> {{ $ujian->tanggal }} | {{ $ujian->waktu }} WIB
                                        </p>
                                    </div>
                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500 transition-colors mt-2"></i>
                                </div>
                            @empty
                                <div class="text-center py-6 flex flex-col items-center justify-center h-full bg-white/50 rounded-2xl border-2 border-dashed border-indigo-200">
                                    <div class="w-12 h-12 md:w-14 md:h-14 bg-indigo-50 shadow-inner border border-indigo-100 rounded-full flex items-center justify-center mb-2 md:mb-3 text-indigo-400">
                                        <i class="fas fa-shield-alt text-lg md:text-xl"></i>
                                    </div>
                                    <p class="text-[11px] md:text-sm text-indigo-600/70 font-bold leading-tight">Aman!<br>Belum ada jadwal ujian.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- B. AGENDA MINGGUAN --}}
                    <div class="bg-white rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-slate-200/60 p-5 md:p-6 flex flex-col max-h-[500px] md:max-h-[600px] h-full">
                        <div class="flex items-center justify-between mb-4 md:mb-5 shrink-0">
                            <div class="flex items-center gap-2">
                                <i class="far fa-calendar-alt text-[#0071BC] text-base md:text-lg"></i>
                                <h3 class="font-bold text-[#0071BC] uppercase tracking-wider text-xs md:text-sm">Event & Agenda</h3>
                            </div>
                            <div class="flex gap-2 md:gap-3">
                                @php
                                    $selectedDate = request('date', \Carbon\Carbon::today()->format('Y-m-d'));
                                    $selectedCarbon = \Carbon\Carbon::parse($selectedDate);
                                    $prevWeek = $selectedCarbon->copy()->subWeek()->format('Y-m-d');
                                    $nextWeek = $selectedCarbon->copy()->addWeek()->format('Y-m-d');
                                    $startOfWeek = $selectedCarbon->copy()->startOfWeek();
                                    $hariLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['date' => $prevWeek]) }}" class="w-7 h-7 md:w-8 md:h-8 rounded flex items-center justify-center border border-slate-200 bg-slate-50 text-gray-500 hover:bg-[#0071BC] hover:text-white hover:border-[#0071BC] transition-colors shadow-sm">
                                    <i class="fas fa-chevron-left text-[10px] md:text-xs"></i>
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['date' => $nextWeek]) }}" class="w-7 h-7 md:w-8 md:h-8 rounded flex items-center justify-center border border-slate-200 bg-slate-50 text-gray-500 hover:bg-[#0071BC] hover:text-white hover:border-[#0071BC] transition-colors shadow-sm">
                                    <i class="fas fa-chevron-right text-[10px] md:text-xs"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Kalender Pemilih Hari --}}
                        <div class="flex justify-between gap-1 shrink-0 mb-5">
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $loopDate = $startOfWeek->copy()->addDays($i);
                                    $isSel = $loopDate->format('Y-m-d') === $selectedDate;
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['date' => $loopDate->format('Y-m-d')]) }}" 
                                   class="flex flex-col items-center justify-center w-full py-1.5 md:py-2 border rounded-lg transition-all cursor-pointer 
                                   {{ $isSel ? 'bg-[#0071BC] border-[#0071BC] text-white shadow-md transform scale-105' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-blue-50 hover:border-blue-200 hover:text-[#0071BC]' }}">
                                    <span class="text-xs md:text-sm font-bold">{{ $loopDate->format('d') }}</span>
                                    <span class="text-[8px] md:text-[9px] font-bold mt-0.5 uppercase">{{ $hariLabels[$i] }}</span>
                                </a>
                            @endfor
                        </div>

                        <div class="w-full py-1.5 md:py-2 bg-[#0071BC] mb-3 md:mb-4 rounded-lg shrink-0 flex items-center justify-center shadow-inner">
                            <span class="text-[9px] md:text-[10px] font-bold text-white uppercase tracking-widest">Detail Agenda 7 Hari</span>
                        </div>

                        {{-- List Agenda --}}
                        <div class="flex flex-col flex-1 overflow-y-auto custom-scrollbar pr-1">
                            @forelse($weeklyEvents ?? $agendaSekolah ?? [] as $event)
                                <div class="mb-2.5 p-3 border-l-[3px] md:border-l-[4px] hover:bg-blue-50/30 transition-colors rounded-r-xl bg-white shadow-sm flex flex-col gap-1 border-y border-r border-slate-100 group {{ $event->date == date('Y-m-d') ? 'border-l-[#0071BC] bg-blue-50/20' : '' }}" style="border-left-color: {{ $event->color ?? '#0071BC' }}">
                                    <div class="flex justify-between items-center mb-0.5 md:mb-1">
                                        <span class="font-black text-slate-400 text-[9px] md:text-[10px] uppercase tracking-widest group-hover:text-slate-600 transition-colors truncate pr-2">
                                            {{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d F') }}
                                        </span>
                                        @if($event->date == date('Y-m-d'))
                                            <span class="bg-[#0071BC] text-white text-[7px] md:text-[8px] font-bold px-1.5 py-0.5 md:px-2 md:py-0.5 rounded-full uppercase animate-pulse shadow-sm shrink-0">Hari Ini</span>
                                        @endif
                                    </div>
                                    <span class="text-xs md:text-sm text-slate-800 block font-bold leading-snug">
                                        {{ $event->title }}
                                    </span>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center py-8 opacity-50">
                                    <i class="far fa-calendar-times text-3xl md:text-4xl text-slate-300 mb-2 md:mb-3"></i>
                                    <p class="text-slate-500 text-[10px] md:text-xs font-bold uppercase tracking-wider">Tidak ada agenda</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODALS SECTION                          --}}
        {{-- ======================================= --}}
        
        {{-- 1. Modal Notifikasi Awal --}}
        <div id="announcementModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300 px-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer" onclick="closeModal()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 md:p-8 transform scale-95 transition-all duration-300" id="modalContent">
                <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-amber-50 hover:text-amber-500 transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
                <div class="w-16 h-16 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-5 mx-auto shadow-inner border border-blue-100">
                    <i class="fas fa-bell animate-wiggle"></i>
                </div>
                <div class="text-center mb-6 md:mb-8">
                    <h3 class="text-lg md:text-xl font-extrabold text-slate-800 mb-2">Pemberitahuan Baru!</h3>
                    <p class="text-xs md:text-sm text-slate-500 leading-relaxed px-2">
                        Selamat datang di Dashboard Siswa. Pastikan untuk selalu memeriksa Jadwal Pelajaran, Modul, dan Tugas barumu hari ini!
                    </p>
                </div>
                <button onclick="closeModal()" class="w-full py-3 px-4 bg-gradient-to-r from-[#0071BC] to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                    Baik, Saya Mengerti
                </button>
            </div>
        </div>

        {{-- 2. Modal Detail Jadwal --}}
        <div id="mapelDetailModal" class="fixed inset-0 z-[110] flex items-center justify-center hidden opacity-0 transition-opacity duration-300 px-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer" onclick="closeMapelModal()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 md:p-8 transform scale-95 transition-all duration-300" id="mapelModalContent">
                <button onclick="closeMapelModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-amber-50 hover:text-amber-500 transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
                <div id="mapelIconContainer" class="w-16 h-16 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-5 mx-auto shadow-inner border border-blue-100">
                    <i id="mapelIcon" class="fas fa-book-open"></i>
                </div>
                <div class="text-center mb-6">
                    <h3 id="modalMapelTitle" class="text-lg md:text-xl font-extrabold text-slate-800 mb-2 line-clamp-2 px-4">Nama Mapel</h3>
                    <div class="inline-flex items-center gap-2 bg-blue-50 px-4 py-1.5 rounded-full border border-blue-100 shadow-sm">
                        <i class="far fa-clock text-[#0071BC] text-sm"></i>
                        <span id="modalMapelJam" class="text-xs md:text-sm font-bold text-[#0071BC]">00:00 - 00:00</span>
                    </div>
                </div>
                <div id="modalMapelInfo" class="space-y-3 md:space-y-4 mb-6 md:mb-8 bg-slate-50 p-4 md:p-5 rounded-2xl border border-slate-100">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white flex items-center justify-center text-blue-500 shadow-sm shrink-0 border border-slate-200">
                            <i class="fas fa-user-tie text-xs md:text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] md:text-xs text-slate-500 font-semibold mb-0.5">Guru Pengajar</p>
                            <p id="modalMapelGuru" class="text-xs md:text-sm font-bold text-slate-800 truncate">Nama Guru</p>
                        </div>
                    </div>
                    <div class="w-full h-px bg-slate-200/60 my-1"></div>
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white flex items-center justify-center text-blue-500 shadow-sm shrink-0 border border-slate-200">
                            <i class="fas fa-door-open text-xs md:text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] md:text-xs text-slate-500 font-semibold mb-0.5">Ruang Kelas</p>
                            <p id="modalMapelRuang" class="text-xs md:text-sm font-bold text-slate-800 truncate">Nama Ruangan</p>
                        </div>
                    </div>
                </div>
                <button onclick="closeMapelModal()" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-colors">
                    Tutup Detail
                </button>
            </div>
        </div>

        {{-- 3. Modal Baca Pengumuman --}}
        <div id="bacaPengumumanModal" class="fixed inset-0 z-[110] flex items-center justify-center hidden opacity-0 transition-opacity duration-300 p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer" onclick="closeBacaPengumuman()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[85vh] md:max-h-[80vh]" id="bacaPengumumanContent">
                
                <div id="headerPengumuman" class="px-5 py-4 md:px-6 md:py-5 bg-[#0071BC] flex justify-between items-center text-white shrink-0 transition-colors">
                    <h3 class="font-bold text-base md:text-lg flex items-center gap-2.5"><i class="fas fa-bullhorn"></i> Detail Pengumuman</h3>
                    <button onclick="closeBacaPengumuman()" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>                
                </div>
                
                <div class="p-5 md:p-8 overflow-y-auto custom-scrollbar bg-white">
                    <div class="flex justify-between items-center mb-4 md:mb-5">
                        <span id="badgePengumuman" class="text-[9px] md:text-[10px] font-bold px-3 py-1 rounded-md border uppercase tracking-wider shadow-sm"></span>
                        <span id="tglPengumuman" class="text-[10px] md:text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 md:py-1.5 rounded-md"></span>
                    </div>
                    
                    <h2 id="judulPengumuman" class="text-lg md:text-2xl font-extrabold text-slate-800 mb-4 md:mb-6 leading-snug"></h2>
                    
                    <div class="bg-slate-50 p-4 md:p-6 rounded-2xl border border-slate-200/60 shadow-inner">
                        <p id="isiPengumuman" class="text-xs md:text-sm text-slate-600 leading-relaxed whitespace-pre-line"></p>
                    </div>
                </div>
                
                <div class="px-5 py-4 md:px-6 md:py-5 bg-slate-50 border-t border-slate-200/60 flex justify-end shrink-0">
                    <button onclick="closeBacaPengumuman()" class="w-full sm:w-auto px-8 py-2.5 md:py-3 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-100 hover:text-slate-800 transition-colors shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- 4. MODAL DETAIL TUGAS SISWA (TEMA BIRU) --}}
        <div id="modalSiswaTugas" class="fixed inset-0 z-[120] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-all duration-300" id="modalSiswaTugasContent">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white flex justify-between items-start relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 text-blue-400/30 text-8xl"><i class="fas fa-clipboard-list"></i></div>
                    <div class="relative z-10">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider mb-2 inline-block shadow-sm" id="modalTugasMapel">Mata Pelajaran</span>
                        <h3 class="font-bold text-xl leading-tight" id="modalTugasJudul">Judul Tugas</h3>
                    </div>
                    <button onclick="closeModalSiswa('modalSiswaTugas', 'modalSiswaTugasContent')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors relative z-10"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-500 shadow-sm shrink-0 border border-blue-200"><i class="far fa-clock text-lg"></i></div>
                        <div>
                            <p class="text-[10px] font-bold text-blue-400 uppercase tracking-wider">Batas Pengumpulan (Deadline)</p>
                            <p class="text-sm font-bold text-blue-600" id="modalTugasDeadline">-</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="closeModalSiswa('modalSiswaTugas', 'modalSiswaTugasContent')" class="flex-1 py-3 px-4 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-colors">Tutup</button>
                        <a href="#" id="btnKerjakanTugas" class="flex-1 py-3 px-4 bg-blue-500 text-white text-center font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-600 transition-all flex items-center justify-center gap-2">
                            Mulai Kerjakan <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. MODAL DETAIL UJIAN SISWA (TEMA INDIGO) --}}
        <div id="modalSiswaUjian" class="fixed inset-0 z-[120] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-all duration-300" id="modalSiswaUjianContent">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 p-6 text-white flex justify-between items-start relative overflow-hidden">
                    <div class="absolute -top-6 -right-6 text-indigo-400/30 text-8xl"><i class="fas fa-stopwatch"></i></div>
                    <div class="relative z-10">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider mb-2 inline-block shadow-sm" id="modalUjianTipe">Tipe Ujian</span>
                        <h3 class="font-bold text-xl leading-tight" id="modalUjianMapel">Mata Pelajaran</h3>
                    </div>
                    <button onclick="closeModalSiswa('modalSiswaUjian', 'modalSiswaUjianContent')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors relative z-10"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col items-center justify-center text-center shadow-inner">
                            <i class="far fa-calendar-alt text-indigo-500 text-2xl mb-2"></i>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</p>
                            <p class="text-sm font-bold text-slate-700 mt-0.5" id="modalUjianTanggal">-</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col items-center justify-center text-center shadow-inner">
                            <i class="far fa-clock text-indigo-500 text-2xl mb-2"></i>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Waktu Mulai</p>
                            <p class="text-sm font-bold text-slate-700 mt-0.5" id="modalUjianWaktu">-</p>
                        </div>
                    </div>
                    
                    <div id="ujianStatusContainer" class="text-center mb-6">
                        </div>

                    <div class="flex gap-3">
                        <button onclick="closeModalSiswa('modalSiswaUjian', 'modalSiswaUjianContent')" class="flex-1 py-3 px-4 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-colors">Tutup</button>
                        <a href="#" id="btnMulaiUjian" class="flex-1 py-3 px-4 bg-indigo-600 text-white text-center font-bold rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2 pointer-events-none opacity-50">
                            Masuk Ruang Ujian <i class="fas fa-sign-in-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center bg-slate-50">
        <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center mb-6 shadow-inner border border-amber-100">
            <i class="fas fa-lock text-4xl text-amber-400"></i>
        </div>
        <h1 class="font-extrabold text-2xl text-slate-800 mb-2">Akses Dibatasi</h1>
        <p class="text-slate-500 font-medium">Halaman ini dirancang khusus untuk akun Siswa.</p>
        <a href="/" class="mt-8 px-6 py-2.5 bg-slate-800 text-white text-sm font-bold rounded-xl hover:bg-slate-700 transition-colors shadow-md">Kembali ke Beranda</a>
    </div>
@endif

{{-- SCRIPT DEPENDENCIES --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/features/lms/student/dashboard/paginate-student-assessment-cheating-history.js') }}"></script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    @keyframes wiggle {
        0%, 100% { transform: rotate(-3deg); }
        50% { transform: rotate(3deg); }
    }
    .animate-wiggle { animation: wiggle 1s ease-in-out infinite; }
</style>

<script>
    // ================= FUNGSI SLIDER MODUL =================
    let currentModuleIndex = 0;
    
    function updateModuleSlider() {
        const slider = document.getElementById('moduleSlider');
        if (!slider) return;

        const items = slider.children;
        const totalItems = items.length;
        const btnPrev = document.getElementById('btnPrevModule');
        const btnNext = document.getElementById('btnNextModule');
        
        if(totalItems <= 1) {
            if(btnPrev) btnPrev.disabled = true;
            if(btnNext) btnNext.disabled = true;
            return;
        }

        slider.style.transform = `translateX(-${currentModuleIndex * 100}%)`;

        if(btnPrev) {
            btnPrev.disabled = currentModuleIndex === 0;
            btnPrev.classList.toggle('opacity-50', currentModuleIndex === 0);
        }
        if(btnNext) {
            btnNext.disabled = currentModuleIndex === totalItems - 1;
            btnNext.classList.toggle('opacity-50', currentModuleIndex === totalItems - 1);
        }
    }

    function nextModule() {
        const slider = document.getElementById('moduleSlider');
        if(slider && currentModuleIndex < slider.children.length - 1) {
            currentModuleIndex++;
            updateModuleSlider();
        }
    }

    function prevModule() {
        if(currentModuleIndex > 0) {
            currentModuleIndex--;
            updateModuleSlider();
        }
    }

    // ================= INISIALISASI SAAT DOM LOADED =================
    document.addEventListener("DOMContentLoaded", function() {
        updateModuleSlider();

        // Munculkan Modal Notifikasi Awal HANYA SEKALI per sesi login
        const userId = "{{ Auth::id() }}";
        const sessionKey = 'welcome_modal_shown_' + userId;

        // Cek apakah key sessionKey belum ada di sessionStorage
        if (!sessionStorage.getItem(sessionKey)) {
            setTimeout(function() {
                const modal = document.getElementById('announcementModal');
                const modalContent = document.getElementById('modalContent');
                
                if(modal && modalContent) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        modal.classList.add('opacity-100');
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                        
                        // Tandai bahwa modal sudah ditampilkan agar tidak muncul lagi saat di-refresh
                        sessionStorage.setItem(sessionKey, 'true');
                    }, 10);
                }
            }, 500); 
        }
    });

    function bacaPengumuman(id, title, content, date, type) {
        document.getElementById('judulPengumuman').innerText = title;
        document.getElementById('isiPengumuman').innerText = content;
        document.getElementById('tglPengumuman').innerText = date;

        const badge = document.getElementById('badgePengumuman');
        const header = document.getElementById('headerPengumuman');

        if (type === 'penting') {
            badge.innerText = 'PENTING';
            badge.className = 'text-[9px] md:text-[10px] font-bold px-3 py-1 rounded-md border uppercase tracking-wider shadow-sm text-red-600 bg-red-50 border-red-100';
            header.className = 'px-5 py-4 md:px-6 md:py-5 bg-red-600 flex justify-between items-center text-white shrink-0 transition-colors';
        } else {
            badge.innerText = 'INFO KELAS';
            badge.className = 'text-[9px] md:text-[10px] font-bold px-3 py-1 rounded-md border uppercase tracking-wider shadow-sm text-blue-600 bg-blue-50 border-blue-100';
            header.className = 'px-5 py-4 md:px-6 md:py-5 bg-[#0071BC] flex justify-between items-center text-white shrink-0 transition-colors';
        }

        // KIRIM STATUS SUDAH DIBACA KE SERVER
        fetch("{{ route('lms.studentAnnouncement.markRead') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ announcement_id: id })
        });

        const modal = document.getElementById('bacaPengumumanModal');
        const contentModal = document.getElementById('bacaPengumumanContent');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.replace('opacity-0', 'opacity-100');
            contentModal.classList.replace('scale-95', 'scale-100');
        }, 10);
    }

    // Fungsi untuk menutup modal
    function closeBacaPengumuman() {
        const modal = document.getElementById('bacaPengumumanModal');
        const contentModal = document.getElementById('bacaPengumumanContent');
        
        modal.classList.replace('opacity-100', 'opacity-0');
        contentModal.classList.replace('scale-100', 'scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            // Refresh otomatis agar status "Baru" ter-update di dashboard
            window.location.reload(); 
        }, 300);
    }

    // ================= FUNGSI UNTUK MODAL NOTIFIKASI AWAL =================
    function closeModal() {
        const modal = document.getElementById('announcementModal');
        const modalContent = document.getElementById('modalContent');
        
        if(modal && modalContent) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); 
        }
    }

    // ================= FUNGSI UNTUK MODAL DETAIL MAPEL =================
    function openMapelModal(mapel, jam, guru, ruang, isBreak) {
        document.getElementById('modalMapelTitle').innerText = mapel;
        document.getElementById('modalMapelJam').innerText = jam;
        document.getElementById('modalMapelGuru').innerText = guru;
        document.getElementById('modalMapelRuang').innerText = ruang;

        const infoSection = document.getElementById('modalMapelInfo');
        const iconContainer = document.getElementById('mapelIconContainer');
        const icon = document.getElementById('mapelIcon');
        const title = document.getElementById('modalMapelTitle');

        if (isBreak) {
            infoSection.classList.add('hidden');
            iconContainer.className = "w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-500 text-white rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-5 mx-auto shadow-md";
            icon.className = "fas fa-utensils";
            title.className = "text-lg md:text-xl font-extrabold text-amber-600 mb-2 px-4";
        } else {
            infoSection.classList.remove('hidden');
            iconContainer.className = "w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-5 mx-auto shadow-md";
            icon.className = "fas fa-book-open";
            title.className = "text-lg md:text-xl font-extrabold text-slate-800 mb-2 px-4";
        }

        const modal = document.getElementById('mapelDetailModal');
        const modalContent = document.getElementById('mapelModalContent');
        
        if(modal && modalContent) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }
    }

    function closeMapelModal() {
        const modal = document.getElementById('mapelDetailModal');
        const modalContent = document.getElementById('mapelModalContent');
        
        if(modal && modalContent) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); 
        }
    }

    // ================= FUNGSI UNTUK MODAL SISWA (TUGAS & UJIAN) =================
    function openTugasSiswaModal(judul, mapel, deadline, id) {
        document.getElementById('modalTugasJudul').innerText = judul;
        document.getElementById('modalTugasMapel').innerText = mapel;
        document.getElementById('modalTugasDeadline').innerText = deadline;
        
        document.getElementById('btnKerjakanTugas').href = `/lms/student/assessment/${id}/kerjakan`;

        const modal = document.getElementById('modalSiswaTugas');
        const content = document.getElementById('modalSiswaTugasContent');
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.replace('opacity-0', 'opacity-100'); 
            content.classList.replace('scale-95', 'scale-100'); 
        }, 10);
    }

    function openUjianSiswaModal(tipe, mapel, tanggal, waktu, h_min, id) {
        document.getElementById('modalUjianTipe').innerText = tipe;
        document.getElementById('modalUjianMapel').innerText = mapel;
        document.getElementById('modalUjianTanggal').innerText = tanggal;
        document.getElementById('modalUjianWaktu').innerText = waktu + ' WIB';

        const btnMulai = document.getElementById('btnMulaiUjian');
        const statusContainer = document.getElementById('ujianStatusContainer');

        if (h_min === 'Hari Ini' || h_min === 'Berlangsung') {
            statusContainer.innerHTML = `<span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg text-xs font-bold animate-pulse shadow-sm"><i class="fas fa-circle text-[8px]"></i> Ujian Tersedia Sekarang</span>`;
            btnMulai.classList.remove('pointer-events-none', 'opacity-50');
            btnMulai.href = `/lms/student/assessment/${id}/kerjakan`; 
        } else {
            statusContainer.innerHTML = `<span class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-xs font-bold shadow-sm"><i class="fas fa-lock text-[10px]"></i> Ujian Belum Tersedia</span>`;
            btnMulai.classList.add('pointer-events-none', 'opacity-50');
            btnMulai.href = "#";
        }

        const modal = document.getElementById('modalSiswaUjian');
        const content = document.getElementById('modalSiswaUjianContent');
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.replace('opacity-0', 'opacity-100'); 
            content.classList.replace('scale-95', 'scale-100'); 
        }, 10);
    }

    function closeModalSiswa(modalId, contentId) {
        const modal = document.getElementById(modalId);
        const content = document.getElementById(contentId);
        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // ================= FUNGSI AJAX UNTUK POLLING SISWA (DENGAN SWEETALERT2) =================
    async function submitSiswaVote(event, pollId) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-vote');
        const selectedOption = form.querySelector(`input[name="option_${pollId}"]:checked`);

        if(!selectedOption) {
            Swal.fire({
                icon: 'warning', 
                title: 'Pilih Jawaban', 
                text: 'Silakan pilih salah satu jawaban terlebih dahulu!', 
                confirmButtonColor: '#0071BC'
            });
            return;
        }

        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;
        btn.disabled = true;

        try {
            const response = await fetch(`{{ route('lms.studentPolling.vote') }}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ poll_id: pollId, option_id: selectedOption.value })
            });

            const result = await response.json();
            
            if(result.success) {
                Swal.fire({
                    icon: 'success', 
                    title: 'Berhasil!', 
                    text: result.message, 
                    showConfirmButton: false, 
                    timer: 1500
                }).then(() => window.location.reload());
            } else {
                Swal.fire({
                    icon: 'error', 
                    title: 'Gagal', 
                    text: result.message, 
                    confirmButtonColor: '#0071BC'
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            Swal.fire({
                icon: 'error', 
                title: 'Error', 
                text: "Terjadi kesalahan jaringan saat mengirim suara.", 
                confirmButtonColor: '#0071BC'
            });
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>