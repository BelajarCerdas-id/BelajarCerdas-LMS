@include('components/sidebar-beranda', [
    'headerSideNav' => 'Beranda Orang Tua'
])

@if (Auth::user()->role === 'Orang Tua')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">
        
        <div class="pt-6 sm:pt-8 mx-4 sm:mx-6 lg:mx-10">
            
            <div class="bg-gradient-to-r from-[#0071BC] to-[#005B94] rounded-3xl p-6 sm:p-8 shadow-lg mb-8 text-white flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 pointer-events-none transform translate-x-1/4 -translate-y-1/4">
                    <i class="fas fa-users text-[15rem]"></i>
                </div>
                
                <div class="relative z-10 w-full md:w-2/3">
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-1.5">Portal Akademik</p>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">
                        Halo, {{ $profilOrangTua->nama_lengkap ?? 'Bapak/Ibu' }} 👋
                    </h1>
                    
                    <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm border border-white/20 px-4 py-1.5 rounded-full mb-6 text-sm font-medium text-blue-50">
                        <i class="fas fa-child text-amber-300"></i>
                        <span>Orang Tua / Wali dari <strong class="text-white ml-1">{{ $dataAnak->nama_lengkap ?? 'Siswa Belum Terhubung' }}</strong></span>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 bg-white text-[#0071BC] rounded-full flex items-center justify-center text-xl font-bold shadow-sm shrink-0">
                            <i class="fas fa-school"></i>
                        </div>
                        <div>
                            <p class="text-xs text-blue-100 font-medium uppercase tracking-wider mb-0.5">Status Siswa:</p>
                            <h3 class="font-bold text-lg leading-tight">Kelas {{ $dataAnak->kelas ?? '-' }}</h3>
                        </div>
                        
                        <div class="ml-auto text-right pl-4 border-l border-white/20 hidden sm:block">
                            <p class="text-xs text-blue-100 font-medium uppercase tracking-wider mb-0.5">Kehadiran Hari Ini:</p>
                            @if(strtolower($dataAnak->kehadiran_hari_ini ?? '') === 'hadir')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30">
                                    <i class="fas fa-check-circle"></i> Hadir
                                </span>
                            @elseif(strtolower($dataAnak->kehadiran_hari_ini ?? '') === 'alpa')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-bold bg-red-400/20 text-red-100 border border-red-400/30">
                                    <i class="fas fa-times-circle"></i> Alpa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-bold bg-slate-400/20 text-slate-100 border border-slate-400/30">
                                    <i class="fas fa-clock"></i> {{ $dataAnak->kehadiran_hari_ini }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ALERT SUCCESS / ERROR UNTUK POLLING --}}
            @if(session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl mb-6 shadow-sm flex items-center justify-between">
                    <p class="font-bold text-sm"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center justify-between">
                    <p class="font-bold text-sm"><i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}</p>
                </div>
            @endif

            {{-- KPI CARDS UNTUK ANAK --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
                {{-- Kehadiran --}}
                <div class="bg-white rounded-[2rem] p-7 shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">Bulan Ini</span>
                    </div>
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Persentase Hadir</h4>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-3xl font-black text-slate-800">{{ $statsAnak->persentase_hadir ?? 0 }}</span>
                        <span class="text-lg font-bold text-slate-400">%</span>
                    </div>
                </div>

                {{-- Nilai Rata-Rata --}}
                <div class="bg-white rounded-[2rem] p-7 shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-xl">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Rata-Rata Nilai</h4>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-3xl font-black text-slate-800">{{ $statsAnak->rata_nilai ?? 0 }}</span>
                    </div>
                </div>

                {{-- Tugas Belum Selesai --}}
                <div class="bg-white rounded-[2rem] p-7 shadow-sm border border-slate-100 hover:shadow-lg hover:border-amber-200 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="text-[10px] font-black text-amber-600 bg-amber-100 px-2 py-1 rounded-lg">Pending</span>
                    </div>
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tugas Belum Dikumpul</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-amber-500">{{ $statsAnak->tugas_pending ?? 0 }}</span>
                        <span class="text-xs font-bold text-slate-400">Tugas</span>
                    </div>
                </div>

                {{-- Peringatan Alpa/Bolos --}}
                <div class="bg-white rounded-[2rem] p-7 shadow-sm border border-red-50 hover:shadow-lg hover:border-red-200 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-xl">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tanpa Keterangan</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-red-600">{{ $statsAnak->alpa ?? 0 }}</span>
                        <span class="text-xs font-bold text-slate-400">Hari</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 sm:gap-8">
                
                {{-- KOLOM KIRI (LEBAR) --}}
                <div class="xl:col-span-2 flex flex-col gap-8">
                    
                    {{-- JADWAL & TUGAS TERBARU --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Jadwal Hari Ini --}}
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col">
                            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                                <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                                    <i class="far fa-calendar-alt text-[#0071BC]"></i> Jadwal Anak Hari Ini
                                </h2>
                                <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded">{{ \Carbon\Carbon::now()->translatedFormat('l') }}</span>
                            </div>
                            <div class="space-y-4 overflow-y-auto custom-scrollbar flex-1 max-h-[300px]">
                                @forelse($jadwalHariIni ?? [] as $jadwal)
                                    <div class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 bg-slate-50">
                                        <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-sm text-xs font-bold text-slate-400">
                                            {{ substr($jadwal->start_time, 0, 5) }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-slate-700 text-sm">{{ $jadwal->mata_pelajaran }}</h4>
                                            <p class="text-[10px] font-medium text-slate-500"><i class="fas fa-user-tie mr-1 text-slate-300"></i> {{ $jadwal->nama_guru }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-10">
                                        <i class="fas fa-mug-hot text-3xl text-slate-300 mb-3"></i>
                                        <p class="font-bold text-slate-500 text-sm">Tidak ada jadwal.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Tugas Belum Selesai --}}
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col">
                            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                                <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-tasks text-amber-500"></i> Tugas & PR
                                </h2>
                            </div>
                            <div class="space-y-4 overflow-y-auto custom-scrollbar flex-1 max-h-[300px]">
                                @forelse($tugasAnak ?? [] as $tugas)
                                    <div class="p-3.5 rounded-xl border border-slate-100 bg-white shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[9px] font-black bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded uppercase">{{ $tugas->mata_pelajaran }}</span>
                                            @if(!$tugas->sudah_dikumpul)
                                                <span class="text-[9px] font-black bg-amber-50 text-amber-600 border border-amber-200 px-2 py-0.5 rounded uppercase animate-pulse">Belum Selesai</span>
                                            @endif
                                        </div>
                                        <h4 class="font-bold text-slate-700 text-sm leading-snug">{{ $tugas->judul_tugas }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 mt-2"><i class="far fa-clock text-red-400"></i> Deadline: {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y') }}</p>
                                    </div>
                                @empty
                                    <div class="text-center py-10 border-2 border-dashed border-slate-100 rounded-2xl">
                                        <i class="fas fa-check-double text-2xl text-emerald-300 mb-2"></i>
                                        <p class="font-bold text-slate-600 text-xs">Semua Tugas Beres!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- STATISTIK BELAJAR --}}
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                            <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-chart-line text-[#0071BC]"></i> Statistik Belajar Anak per Mapel
                            </h2>
                        </div>
                        
                        <div class="space-y-6">
                            @forelse($statistikMapel ?? [] as $stat)
                                @php
                                    $persenTugas = $stat->tugas_total > 0 ? round(($stat->tugas_selesai / $stat->tugas_total) * 100) : 0;
                                    $persenMateri = $stat->materi_total > 0 ? round(($stat->materi_dibaca / $stat->materi_total) * 100) : 0;
                                @endphp
                                <div class="group">
                                    <h3 class="text-sm font-bold text-slate-800 mb-3">{{ $stat->mapel }}</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <div class="flex justify-between text-xs font-bold mb-1.5">
                                                <span class="text-slate-500">Tugas Dikerjakan ({{ $stat->tugas_selesai }}/{{ $stat->tugas_total }})</span>
                                                <span class="{{ $persenTugas >= 80 ? 'text-emerald-500' : ($persenTugas >= 50 ? 'text-amber-500' : 'text-red-500') }}">{{ $persenTugas }}%</span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                                <div class="{{ $persenTugas >= 80 ? 'bg-emerald-500' : ($persenTugas >= 50 ? 'bg-amber-500' : 'bg-red-500') }} h-2.5 rounded-full transition-all duration-1000" style="width: {{ $persenTugas }}%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-xs font-bold mb-1.5">
                                                <span class="text-slate-500">Materi Dibaca</span>
                                                <span class="text-[#0071BC]">{{ $persenMateri }}%</span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                                <div class="bg-[#0071BC] h-2.5 rounded-full transition-all duration-1000" style="width: {{ $persenMateri }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(!$loop->last) <hr class="border-dashed border-slate-200"> @endif
                            @empty
                                <div class="text-center text-slate-500 text-sm py-4">Belum ada statistik belajar di kelas ini.</div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN (SEMPIT) --}}
                <div class="xl:col-span-1 flex flex-col gap-8">
                    
                    {{-- 1. AGENDA SEKOLAH TIMELINE --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5 md:p-6 flex flex-col max-h-[400px] md:max-h-[500px]">
                        <div class="flex-shrink-0">
                            <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                                <i class="fas fa-calendar-alt text-[#0071BC]"></i> Agenda Sekolah
                            </h2>
                        </div>
                        
                        <div class="relative border-l-[3px] border-slate-100 ml-3 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                            <div class="space-y-4 pt-2 pb-4">
                                @forelse(collect($agendaSekolah ?? [])->reverse() as $agenda)
                                    <div class="relative pl-5 group">
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 shadow-sm group-hover:shadow-md group-hover:bg-white group-hover:border-blue-100 transition-all relative overflow-hidden">
                                            <div class="absolute left-0 top-0 bottom-0 w-1" style="background-color: {{ $agenda->color ?? '#0071BC' }};"></div>
                                            
                                            <div class="flex items-center gap-1.5 mb-1.5">
                                                <i class="far fa-calendar-check text-[10px] text-slate-400"></i>
                                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $agenda->tanggal }}</p>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-800 leading-snug group-hover:text-[#0071BC] transition-colors">{{ $agenda->kegiatan }}</h4>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-8 text-center opacity-60">
                                        <i class="far fa-calendar-xmark text-3xl text-slate-300 mb-3"></i>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum ada agenda bulan ini.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- 2. POLLING ORANG TUA --}}
                    <div class="bg-gradient-to-b from-[#005B94] to-[#003B64] rounded-3xl shadow-md p-6 text-white relative overflow-hidden flex flex-col max-h-[500px]">
                        <div class="absolute -right-4 -bottom-4 opacity-10 text-8xl pointer-events-none">
                            <i class="fas fa-poll"></i>
                        </div>
                        <div class="relative z-10 flex-shrink-0">
                            <h2 class="text-lg font-extrabold mb-4 flex items-center gap-2 border-b border-white/20 pb-3">
                                <i class="fas fa-bullhorn text-amber-400"></i> Suara & Aspirasi
                            </h2>
                        </div>
                        
                        <div class="relative z-10 flex-1 overflow-y-auto pr-2 custom-scrollbar-light pb-2">
                            @php
                                // FILTER: Hanya tampilkan polling yang ditujukan untuk Orang Tua atau Semua
                                $parentPolls = collect($polls ?? [])->filter(function($p) {
                                    $target = strtolower($p->target ?? 'orang tua');
                                    return str_contains($target, 'orang tua') || str_contains($target, 'semua');
                                });
                            @endphp

                            @forelse($parentPolls as $poll)
                                <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm border border-white/10 mb-4 hover:bg-white/15 transition-colors group">
                                    
                                    {{-- 👇 PERBAIKAN: BADGE PENGIRIM DAN TARGET 👇 --}}
                                    <div class="flex flex-col gap-2 mb-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            {{-- Badge Pengirim --}}
                                            <span class="text-[9px] md:text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/20 text-blue-50 uppercase tracking-wider border border-white/10 flex items-center gap-1.5 shadow-sm">
                                                <i class="fas fa-building"></i> Dari: {{ $poll->pengirim ?? 'Sekolah' }}
                                            </span>
                                            {{-- Badge Target --}}
                                            <span class="text-[9px] md:text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-400/20 text-amber-200 uppercase tracking-wider border border-amber-400/20 flex items-center gap-1.5 shadow-sm">
                                                <i class="fas fa-bullseye"></i> Untuk: {{ $poll->target ?? 'Orang Tua' }}
                                            </span>
                                        </div>
                                        
                                        @if(isset($poll->nama_kelas) && $poll->nama_kelas !== 'Semua Kelas (Global)')
                                            <div>
                                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-md bg-sky-400/20 text-sky-100 uppercase tracking-wider border border-sky-400/20 flex items-center gap-1.5 w-fit shadow-sm">
                                                    <i class="fas fa-chalkboard"></i> {{ $poll->nama_kelas }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- 👆 SELESAI PERBAIKAN BADGE 👆 --}}

                                    <p class="text-sm font-bold leading-relaxed mb-4 text-white group-hover:text-amber-100 transition-colors">{{ $poll->pertanyaan ?? $poll->question }}</p>
                                    
                                    @if($poll->sudah_vote ?? false)
                                        <div class="space-y-3">
                                            @foreach($poll->options ?? $poll->opsi as $opt)
                                                @php $isUserChoice = isset($opt->is_selected) && $opt->is_selected; @endphp
                                                <div class="relative w-full">
                                                    <div class="flex justify-between text-[10px] md:text-xs font-bold mb-1 {{ $isUserChoice ? 'text-amber-300' : 'text-blue-100' }}">
                                                        <span class="truncate pr-2 flex items-center gap-1.5">
                                                            @if($isUserChoice) <i class="fas fa-check-circle"></i> @endif
                                                            {{ $opt->text ?? $opt->option_text }}
                                                        </span>
                                                        <span>{{ $opt->percentage ?? 0 }}%</span>
                                                    </div>
                                                    <div class="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                                                        <div class="{{ $isUserChoice ? 'bg-amber-400' : 'bg-blue-400' }} h-full rounded-full transition-all duration-1000" style="width: {{ $opt->percentage ?? 0 }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4 flex justify-between items-center text-[10px]">
                                            <span class="text-blue-200 italic">*Ceklis kuning: pilihan Anda</span>
                                            <span class="font-bold bg-white/10 px-2 py-1 rounded text-blue-100"><i class="fas fa-users"></i> {{ $poll->total_votes ?? 0 }} Suara</span>
                                        </div>
                                    @else
                                        <form onsubmit="submitParentVote(event, {{ $poll->id }})" class="space-y-2">
                                            @csrf
                                            @foreach($poll->opsi ?? $poll->options as $opsi)
                                                <label class="block w-full bg-white/10 hover:bg-white/20 border border-white/20 text-blue-50 text-xs font-medium p-3 rounded-xl cursor-pointer transition-colors">
                                                    <input type="radio" name="option_id" value="{{ $opsi->id }}" class="mr-2 text-blue-500 bg-white/50 border-none" required>
                                                    {{ $opsi->text ?? $opsi->option_text }}
                                                </label>
                                            @endforeach
                                            <button type="submit" class="w-full mt-3 bg-amber-400 text-[#005B94] text-xs font-extrabold py-2.5 rounded-xl hover:bg-amber-300 transition shadow-sm transform hover:-translate-y-0.5">
                                                Kirim Jawaban
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm text-center text-sm font-medium mb-4 border border-white/10 text-blue-100">
                                    <i class="fas fa-box-open text-2xl mb-2 opacity-50"></i><br>
                                    Belum ada polling aktif untuk Anda.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@else
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 min-h-screen flex flex-col items-center justify-center bg-slate-50 px-4">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 flex flex-col items-center text-center max-w-sm w-full">
            <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-shield-alt text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Akses Terbatas</h1>
            <p class="text-sm text-slate-500 mb-8 leading-relaxed">Halaman ini khusus diperuntukkan bagi Orang Tua siswa.</p>
            
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center py-3.5 bg-[#0071BC] text-white font-bold rounded-xl gap-2 cursor-pointer transition-all duration-300 hover:bg-blue-800 hover:shadow-lg hover:shadow-blue-200">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Kembali & Keluar
                </button>
            </form>
        </div>
    </div>
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    .custom-scrollbar-light::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar-light::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar-light::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
    .custom-scrollbar-light::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    async function submitParentVote(event, pollId) {
        event.preventDefault(); 
        
        const form = event.target;
        const formData = new FormData(form);
        const optionId = formData.get('option_id');

        if (!optionId) {
            Swal.fire({ icon: 'warning', title: 'Pilih Jawaban', text: 'Silakan pilih salah satu opsi.' });
            return;
        }

        try {
            const url = `{{ url('/') }}/lms/parent/polling/${pollId}/vote`;
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ option_id: optionId })
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.reload(); 
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Oops...', text: result.message });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
        }
    }
</script>