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
                    {{ $guruTerpilih ? 'Menampilkan detail aktivitas untuk: ' . $guruTerpilih->nama_lengkap : 'Pemantauan target jatah upload Materi, Assessment, dan Bank Soal.' }}
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

            {{-- Indikator Kinerja Utama (KPI) JATAH UPLOAD --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                
                {{-- KPI ASSESSMENT --}}
                <div onclick='openKpiModal("Assessment & Tugas", @json($stats->breakdown_assessment), "text-indigo-500", "bg-indigo-50", "fa-file-signature")' class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md cursor-pointer group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-indigo-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center gap-3 mb-3 relative z-10">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors"><i class="fas fa-file-signature"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Assessment Dibuat</p>
                    </div>
                    <div class="flex items-baseline gap-2 relative z-10">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_assessment }}</h3>
                        <span class="text-xs font-bold text-slate-400">/ {{ $stats->target_assessment }} Target</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $stats->target_assessment > 0 ? min(100, ($stats->total_assessment / $stats->target_assessment) * 100) : 0 }}%"></div>
                    </div>
                </div>

                {{-- KPI CONTENT --}}
                <div onclick='openKpiModal("Materi Pembelajaran", @json($stats->breakdown_content), "text-emerald-500", "bg-emerald-50", "fa-photo-video")' class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md cursor-pointer group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full -z-0 opacity-50 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center gap-3 mb-3 relative z-10">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors"><i class="fas fa-photo-video"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Materi Terkirim</p>
                    </div>
                    <div class="flex items-baseline gap-2 relative z-10">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_content }}</h3>
                        <span class="text-xs font-bold text-slate-400">/ {{ $stats->target_content }} Target</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $stats->target_content > 0 ? min(100, ($stats->total_content / $stats->target_content) * 100) : 0 }}%"></div>
                    </div>
                </div>

                {{-- KPI QUESTION BANK (Per File/Bank) --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1 relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center"><i class="fas fa-list-ol"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bank Soal (Berkas)</p>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_question_banks }}</h3>
                        <span class="text-xs font-bold text-slate-400">/ {{ $stats->target_question }} Target</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                        <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $stats->target_question > 0 ? min(100, ($stats->total_question_banks / $stats->target_question) * 100) : 0 }}%"></div>
                    </div>
                </div>

                {{-- KPI GURU AKTIF --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 transition-transform hover:-translate-y-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0071BC] flex items-center justify-center"><i class="fas fa-id-badge"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Guru Aktif (Bulan Ini)</p>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-[#0071BC]">{{ $stats->guru_aktif }}</h3>
                        <span class="text-xs font-bold text-slate-400">Orang</span>
                    </div>
                </div>
            </div>

            {{-- PANEL: PROGRESS JATAH UPLOAD PER MAPEL (MUNCUL JIKA GURU DIPILIH) --}}
            @if($guruTerpilih)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 p-6 md:p-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 text-[#0071BC] border border-blue-200 flex items-center justify-center text-xl shadow-inner shrink-0">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-lg leading-tight">Target Kinerja Bpk/Ibu {{ $guruTerpilih->nama_lengkap }}</h3>
                            <p class="text-xs font-medium text-slate-500 mt-1">Detail jatah materi & asesmen per mata pelajaran yang diajar</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @forelse($targetUploadGuru ?? [] as $target)
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 relative overflow-hidden group hover:border-blue-300 transition-colors flex flex-col gap-4">
                            
                            {{-- Judul Mapel --}}
                            <h4 class="font-black text-slate-700 text-sm flex items-center gap-2 border-b border-slate-200 pb-3">
                                <i class="fas fa-book text-slate-400 group-hover:text-[#0071BC] transition-colors"></i> 
                                {{ $target->mapel }}
                            </h4>
                            
                            {{-- Baris 1: Progress MATERI --}}
                            @php
                                $pctContent = $target->content->target > 0 ? min(100, round(($target->content->tercapai / $target->content->target) * 100)) : 0;
                                $colorContent = $pctContent >= 100 ? 'bg-emerald-500' : ($pctContent >= 50 ? 'bg-[#0071BC]' : 'bg-amber-500');
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5"><i class="fas fa-photo-video text-emerald-500"></i> Materi Pembelajaran</span>
                                    <span class="text-[11px] font-extrabold text-slate-700">{{ $target->content->tercapai }} / {{ $target->content->target }}</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 mb-2.5 shadow-inner overflow-hidden">
                                    <div class="{{ $colorContent }} h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $pctContent }}%"></div>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($target->content->detail as $jenis => $jml)
                                        <span class="text-[9px] font-bold bg-white border border-slate-200 text-slate-500 px-2 py-0.5 rounded shadow-sm">{{ $jenis }}: {{ $jml }}</span>
                                    @empty
                                        <span class="text-[9px] font-medium text-slate-400 italic">Belum ada materi</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Baris 2: Progress ASSESSMENT --}}
                            @php
                                $pctAss = $target->assessment->target > 0 ? min(100, round(($target->assessment->tercapai / $target->assessment->target) * 100)) : 0;
                                $colorAss = $pctAss >= 100 ? 'bg-emerald-500' : ($pctAss >= 50 ? 'bg-indigo-500' : 'bg-amber-500');
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5"><i class="fas fa-tasks text-indigo-500"></i> Tugas & Ujian</span>
                                    <span class="text-[11px] font-extrabold text-slate-700">{{ $target->assessment->tercapai }} / {{ $target->assessment->target }}</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 mb-2.5 shadow-inner overflow-hidden">
                                    <div class="{{ $colorAss }} h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $pctAss }}%"></div>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($target->assessment->detail as $jenis => $jml)
                                        <span class="text-[9px] font-bold bg-white border border-slate-200 text-slate-500 px-2 py-0.5 rounded shadow-sm">{{ $jenis }}: {{ $jml }}</span>
                                    @empty
                                        <span class="text-[9px] font-medium text-slate-400 italic">Belum ada assessment</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="lg:col-span-2 text-center py-6 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl">
                            <p class="text-sm font-bold text-slate-500">Guru ini belum di-assign ke mata pelajaran apapun di semester ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- Grid Detail Monitoring List Terkini --}}
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
                            <p class="text-[10px] text-slate-500 font-medium">Berkas soal yang telah diinput</p>
                        </div>
                    </div>
                    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                        @forelse($recentQuestions as $item)
                            <div class="p-4 rounded-2xl border border-slate-100 hover:border-amber-200 hover:bg-amber-50/30 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-sm text-slate-800">{{ $item->guru }}</h4>
                                    <span class="text-[10px] font-bold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-md">{{ $item->jumlah_soal }} Butir</span>
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

    {{-- MODAL POP UP DETAIL KPI (BREAKDOWN) --}}
    <div id="kpiDetailModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300" id="kpiDetailContent">
            <div id="kpiModalHeader" class="p-6 flex items-center gap-4 border-b border-slate-100">
                <div id="kpiModalIconBox" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i id="kpiModalIcon" class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-800 text-lg leading-tight">Rincian Tipe</h3>
                    <p id="kpiModalTitle" class="text-xs font-medium text-slate-500 mt-0.5">Detail</p>
                </div>
            </div>
            
            <div class="p-6">
                <div id="kpiBreakdownList" class="space-y-3">
                    </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeKpiModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-100 transition-colors shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>

    <script>
        // Logika Pop-Up Rincian Tipe KPI
        function openKpiModal(title, breakdownData, textColorClass, bgColorClass, faIconClass) {
            const modal = document.getElementById('kpiDetailModal');
            const content = document.getElementById('kpiDetailContent');
            const listBox = document.getElementById('kpiBreakdownList');
            const iconBox = document.getElementById('kpiModalIconBox');
            
            // Set Header Styling
            document.getElementById('kpiModalTitle').innerText = title;
            document.getElementById('kpiModalIcon').className = 'fas ' + faIconClass;
            iconBox.className = `w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 ${bgColorClass} ${textColorClass}`;
            
            // Generate List
            listBox.innerHTML = '';
            if (Object.keys(breakdownData).length === 0) {
                listBox.innerHTML = `<p class="text-sm text-slate-500 italic text-center py-4">Belum ada data ${title} yang diunggah.</p>`;
            } else {
                for (const [tipe, jumlah] of Object.entries(breakdownData)) {
                    listBox.innerHTML += `
                        <div class="flex justify-between items-center p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
                            <span class="text-sm font-bold text-slate-700">${tipe}</span>
                            <span class="text-xs font-black ${textColorClass} ${bgColorClass} px-3 py-1 rounded-lg">${jumlah}</span>
                        </div>
                    `;
                }
            }

            // Show Modal
            modal.classList.remove('hidden');
            void modal.offsetWidth; 
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }

        function closeKpiModal() {
            const modal = document.getElementById('kpiDetailModal');
            const content = document.getElementById('kpiDetailContent');
            
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    </script>
@else
    {{-- HALAMAN JIKA ROLE TIDAK DIIZINKAN --}}
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 min-h-screen flex flex-col items-center justify-center bg-slate-50 px-4">
        </div>
@endif