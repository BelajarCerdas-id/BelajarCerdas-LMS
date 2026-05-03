@include('components/sidebar-beranda', ['headerSideNav' => 'Pusat Kendali'])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F1F5F9] min-h-screen pb-12">

        <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">
            
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
                        Halo, {{ Str::before(Auth::user()->SchoolStaffProfile->nama_lengkap ?? 'Kepala', ' ') }}!
                    </h1>
                    <p class="text-slate-500 mt-2 font-medium">Ringkasan operasional sekolah untuk <span class="text-slate-800 font-bold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span></p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                        <i class="fas fa-download"></i> Unduh Laporan
                    </button>
                    <button class="px-5 py-2.5 bg-[#0071BC] text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Buat Pengumuman
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-[2rem] p-7 border border-white shadow-sm hover:shadow-xl transition-all duration-500 group">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">+2.5%</span>
                    </div>
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Murid</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-800">{{ $stats->total_siswa ?? 0 }}</span>
                        <span class="text-xs font-bold text-slate-400">Aktif</span>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-7 border border-white shadow-sm hover:shadow-xl transition-all duration-500 group">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-1 rounded-lg">Tetap</span>
                    </div>
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Guru</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-800">{{ $stats->total_guru ?? 0 }}</span>
                        <span class="text-xs font-bold text-slate-400">Pendidik</span>
                    </div>
                </div>

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
                    <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Ruang Kelas</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-800">{{ $stats->total_kelas ?? 0 }}</span>
                        <span class="text-xs font-bold text-slate-400">Rombel</span>
                    </div>
                </div>

                <div class="bg-[#0071BC] rounded-[2rem] p-7 shadow-lg shadow-blue-200 group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <h4 class="text-blue-100 text-xs font-bold uppercase tracking-wider relative z-10">Rata-rata Hadir</h4>
                    <div class="flex items-baseline gap-1 mt-1 relative z-10">
                        <span class="text-3xl font-black text-white">{{ $stats->rata_kehadiran ?? 0 }}</span>
                        <span class="text-lg font-bold text-blue-200">%</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                <div class="xl:col-span-8 space-y-8">
                    <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="h-10 w-1 bg-[#0071BC] rounded-full"></div>
                            <h3 class="text-xl font-black text-slate-800">Modul Monitoring Strategis</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            <a href="{{ route('lms.kepsek.laporan.akademik') }}" class="group block">
                                <div class="relative rounded-[2rem] bg-slate-50 p-8 border border-transparent hover:border-[#0071BC] hover:bg-white hover:shadow-2xl hover:shadow-blue-100 transition-all duration-500">
                                    <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center text-2xl text-[#0071BC] mb-6 group-hover:rotate-6 transition-transform">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h4 class="text-lg font-black text-slate-800 mb-2">Laporan Akademik</h4>
                                    <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6">Analisis performa nilai siswa per kelas dan pemetaan mata pelajaran kritis.</p>
                                    <span class="inline-flex items-center gap-2 text-[#0071BC] font-black text-xs uppercase tracking-widest">
                                        Buka Analitik <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                                    </span>
                                </div>
                            </a>

                            <a href="{{ route('lms.kepsek.aktivitas.guru') }}" class="group block">
                                <div class="relative rounded-[2rem] bg-slate-50 p-8 border border-transparent hover:border-emerald-500 hover:bg-white hover:shadow-2xl hover:shadow-emerald-100 transition-all duration-500">
                                    <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center text-2xl text-emerald-500 mb-6 group-hover:rotate-6 transition-transform">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <h4 class="text-lg font-black text-slate-800 mb-2">Aktivitas Guru</h4>
                                    <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6">Pantau jurnal mengajar harian, kehadiran pendidik, dan jadwal aktif sekolah.</p>
                                    <span class="inline-flex items-center gap-2 text-emerald-500 font-black text-xs uppercase tracking-widest">
                                        Monitoring <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 space-y-8">
                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full min-h-[500px]">
                        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                            <h3 class="font-black text-slate-800 tracking-tight flex items-center gap-2">
                                <i class="fas fa-bolt text-amber-500"></i> Informasi Terbaru
                            </h3>
                            <div class="w-2 h-2 rounded-full bg-red-500 animate-ping"></div>
                        </div>
                        
                        <div class="p-8 space-y-8 overflow-y-auto custom-scrollbar flex-1">
                            @forelse($pengumuman as $info)
                                <div class="relative pl-6 border-l-2 border-slate-100 hover:border-[#0071BC] transition-colors group cursor-pointer">
                                    <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-200 group-hover:bg-[#0071BC] transition-colors"></div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($info->created_at)->diffForHumans() }}</span>
                                    <h4 class="font-bold text-sm text-slate-700 mt-1 leading-snug group-hover:text-[#0071BC] transition-colors">{{ $info->judul }}</h4>
                                </div>
                            @empty
                                <div class="h-full flex flex-col items-center justify-center text-center opacity-40 py-20">
                                    <i class="fas fa-comment-slash text-4xl mb-4"></i>
                                    <p class="text-sm font-bold">Belum ada info terbaru</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <div class="p-6 bg-slate-50/80">
                            <button class="w-full py-3 rounded-2xl bg-white border border-slate-200 text-xs font-black text-slate-600 hover:bg-[#0071BC] hover:text-white hover:border-[#0071BC] transition-all uppercase tracking-widest">
                                Lihat Semua Arsip
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif

<style>
    /* Custom Scrollbar for better UI */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
    
    /* Smooth transitions for scale and shadow */
    .group:hover .group-hover\:rotate-6 { transform: rotate(6deg); }
</style>