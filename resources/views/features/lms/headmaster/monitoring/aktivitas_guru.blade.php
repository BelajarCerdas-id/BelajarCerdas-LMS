@include('components/sidebar-beranda', ['headerSideNav' => 'Aktivitas Guru'])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">

        <div class="bg-white border-b border-slate-200 px-6 py-6 md:px-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 sticky top-0 z-30 shadow-sm">
            <div>
                <a href="{{ route('lms.kepsek.dashboard', [
                    'role'       => Auth::user()->role,
                    'schoolName' => \Illuminate\Support\Str::slug(Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah ?? 'sekolah'),
                    'schoolId'   => Auth::user()->SchoolStaffProfile->school_partner_id ?? 0
                ]) }}" class="text-sm font-bold text-[#0071BC] hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Monitoring Aktivitas Guru</h1>
                <p class="text-xs font-medium text-slate-500 mt-1">
                    {{ $guruTerpilih ? 'Menampilkan detail aktivitas untuk: ' . $guruTerpilih->nama_lengkap : 'Pemantauan penyusunan Assessment, Content, dan Question Bank secara keseluruhan.' }}
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('lms.kepsek.aktivitas.guru') }}" method="GET" class="flex items-center gap-3 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 shadow-inner">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#0071BC] shadow-sm shrink-0">
                        <i class="fas fa-filter"></i>
                    </div>
                    <select name="guru_id" onchange="this.form.submit()" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer outline-none w-48 md:w-56 py-2">
                        <option value="">-- Semua Guru --</option>
                        @foreach($daftarGuru as $guru)
                            <option value="{{ $guru->id }}" {{ $filterGuruId == $guru->id ? 'selected' : '' }}>
                                {{ $guru->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    @if($filterGuruId)
                        <a href="{{ route('lms.kepsek.aktivitas.guru') }}" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-500 hover:text-white text-red-500 flex items-center justify-center transition-colors shrink-0" title="Hapus Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">

            {{-- Indikator Kinerja Utama (KPI) Dinamis --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center"><i class="fas fa-file-signature"></i></div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Assessment Dibuat</p>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_assessment }}</h3>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center"><i class="fas fa-photo-video"></i></div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Materi Terkirim</p>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_content }}</h3>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center"><i class="fas fa-list-ol"></i></div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Soal (Questions)</p>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_question }}</h3>
                        <span class="text-xs font-bold text-slate-400">Butir</span>
                    </div>
                </div>

                {{-- KOTAK KE-4 DIUBAH MENJADI GURU AKTIF --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0071BC] flex items-center justify-center"><i class="fas fa-id-badge"></i></div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Guru Aktif Bulan Ini</p>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-[#0071BC]">{{ $stats->guru_aktif }}</h3>
                        <span class="text-xs font-bold text-slate-400">Orang</span>
                    </div>
                </div>
            </div>

            {{-- Grid Detail Monitoring --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Modul 1: Assessment --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 flex flex-col h-[550px]">
                    <div class="p-5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50 rounded-t-3xl">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i class="fas fa-tasks text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Assessment</h3>
                            <p class="text-[10px] text-slate-500 font-medium">Ujian & Tugas yang dikelola</p>
                        </div>
                    </div>
                    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                        @forelse($recentAssessments as $item)
                            <div class="p-4 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-sm text-slate-800">{{ $item->guru }}</h4>
                                    <span class="text-[10px] font-bold {{ strtolower($item->status) === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-md uppercase">{{ $item->status }}</span>
                                </div>
                                <p class="text-xs font-semibold text-slate-600 mb-2">{{ $item->tipe }}</p>
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-400">
                                    <span class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded-md">{{ $item->mapel }}</span>
                                    <span><i class="far fa-clock mr-1"></i> {{ $item->waktu }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center opacity-50 text-center">
                                <i class="fas fa-folder-open text-3xl mb-3 text-slate-300"></i>
                                <p class="text-xs font-bold text-slate-500">Tidak ada data Assessment.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Modul 2: Content --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 flex flex-col h-[550px]">
                    <div class="p-5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50 rounded-t-3xl">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-photo-video text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Materi</h3>
                            <p class="text-[10px] text-slate-500 font-medium">Modul yang telah diunggah</p>
                        </div>
                    </div>
                    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                        @forelse($recentContents as $item)
                            <div class="p-4 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-sm text-slate-800">{{ $item->guru }}</h4>
                                    <span class="text-[10px] font-black bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">{{ $item->format }}</span>
                                </div>
                                <p class="text-xs font-semibold text-slate-600 mb-2 line-clamp-1">{{ $item->judul }}</p>
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-400">
                                    <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded-md">{{ $item->mapel }}</span>
                                    <span><i class="far fa-clock mr-1"></i> {{ $item->waktu }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center opacity-50 text-center">
                                <i class="fas fa-folder-open text-3xl mb-3 text-slate-300"></i>
                                <p class="text-xs font-bold text-slate-500">Tidak ada data Materi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Modul 3: Question --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 flex flex-col h-[550px]">
                    <div class="p-5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50 rounded-t-3xl">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-list-ol text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Bank Soal</h3>
                            <p class="text-[10px] text-slate-500 font-medium">Butir soal yang telah diinput</p>
                        </div>
                    </div>
                    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                        @forelse($recentQuestions as $item)
                            <div class="p-4 rounded-2xl border border-slate-100 hover:border-amber-200 hover:bg-amber-50/30 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-sm text-slate-800">{{ $item->guru }}</h4>
                                    <span class="text-[10px] font-bold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-md">{{ $item->jumlah_soal }} Soal</span>
                                </div>
                                <p class="text-xs font-semibold text-slate-600 mb-2">Topik: {{ $item->topik }}</p>
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-400">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md">{{ $item->mapel }}</span>
                                    <span><i class="far fa-clock mr-1"></i> {{ $item->waktu }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center opacity-50 text-center">
                                <i class="fas fa-folder-open text-3xl mb-3 text-slate-300"></i>
                                <p class="text-xs font-bold text-slate-500">Tidak ada data Bank Soal.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
@else
    {{-- HALAMAN JIKA ROLE TIDAK DIIZINKAN --}}
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 min-h-screen flex flex-col items-center justify-center bg-slate-50 px-4">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 flex flex-col items-center text-center max-w-sm w-full">
            <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-triangle-exclamation text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Akses Ditolak</h1>
            <p class="text-sm text-slate-500 mb-8 leading-relaxed">Maaf, Anda tidak memiliki hak akses (role) untuk melihat halaman ini.</p>
            
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center py-3.5 bg-red-500 text-white font-bold rounded-xl gap-2 cursor-pointer transition-all duration-300 hover:bg-red-600 hover:shadow-lg hover:shadow-red-200 focus:ring-4 focus:ring-red-100 active:scale-95">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Kembali & Keluar
                </button>
            </form>
        </div>
    </div>
@endif