@include('components/sidebar-beranda', [
    'headerSideNav' => 'Polling'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">
        <div class="pt-8 mx-6 lg:mx-10">

            {{-- HEADER MANAJEMEN POLLING --}}
            <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 mb-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-transparent rounded-full -translate-y-1/2 translate-x-1/3 opacity-70 pointer-events-none"></div>
                
                <div class="relative z-10 flex-1">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-12 h-12 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-blue-100 shrink-0">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Manajemen Polling</h1>
                    </div>
                    <p class="text-slate-500 text-sm font-medium md:ml-16">Buat jajak pendapat untuk siswa dan orang tua di kelas yang Anda ajar.</p>
                </div>

                {{-- FILTER TAHUN AJARAN --}}
                <form action="{{ route('lms.teacherPolling.view', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" method="GET" class="relative z-10 w-full lg:w-auto mt-2 lg:mt-0">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#0071BC] transition-colors">
                            <i class="fas fa-calendar-alt text-lg"></i>
                        </div>
                        <select name="tahun_ajaran" onchange="this.form.submit()" class="w-full lg:w-64 bg-slate-50 border-2 border-slate-200 text-slate-700 text-sm font-bold rounded-2xl pl-12 pr-10 py-3.5 focus:outline-none focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] appearance-none cursor-pointer transition-all hover:bg-white">
                            <option value="">-- Semua Tahun Ajaran --</option>
                            @foreach($tahunAjaranList ?? [] as $tahun)
                                <option value="{{ $tahun }}" {{ ($filterTahun ?? '') == $tahun ? 'selected' : '' }}>
                                    TA. {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                {{-- KOLOM KIRI (FORM BUAT POLLING) --}}
                <div class="xl:col-span-2 flex flex-col gap-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-[#0071BC]"></div>

                        <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-5">
                            <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[#0071BC]">
                                    <i class="fas fa-pen-to-square"></i>
                                </div>
                                Buat Polling Kelas
                            </h2>
                        </div>

                        <form id="form-polling" class="space-y-7">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- TARGET AUDIENS --}}
                                <div class="group">
                                    <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                        Target Polling <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#0071BC] transition-colors">
                                            <i class="fas fa-bullseye text-lg"></i>
                                        </div>
                                        <select id="poll-target" required class="w-full border-2 border-slate-200 rounded-2xl pl-12 pr-10 py-3.5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-slate-50 focus:bg-white appearance-none cursor-pointer">
                                            <option value="" disabled selected>-- Pilih Target Audiens --</option>
                                            <option value="Semua Siswa">🎓 Khusus Siswa di Kelas</option>
                                            <option value="Semua Orang Tua">👨‍👩‍👧‍👦 Khusus Orang Tua</option>
                                            <option value="Semua Warga Sekolah">🏫 Keduanya (Siswa & Ortu)</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- TARGET KELAS (HANYA KELAS YANG DIAJAR) --}}
                                <div class="group">
                                    <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                        Target Kelas <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#0071BC] transition-colors">
                                            <i class="fas fa-chalkboard text-lg"></i>
                                        </div>
                                        <select id="poll-class" required class="w-full border-2 border-slate-200 rounded-2xl pl-12 pr-10 py-3.5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-slate-50 focus:bg-white appearance-none cursor-pointer">
                                            <option value="" disabled selected>-- Pilih Kelas Tujuan --</option>
                                            <option value="0" data-name="Semua Kelas Yang Saya Ajar">Semua Kelas Yang Saya Ajar</option>
                                            @foreach($classes ?? [] as $cls)
                                                <option value="{{ $cls->class_id }}" data-name="{{ $cls->class_name }}">Kelas {{ $cls->class_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- PERTANYAAN POLLING --}}
                            <div class="group">
                                <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                    Pertanyaan Polling <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute top-4 left-4 text-slate-400 pointer-events-none group-focus-within:text-[#0071BC] transition-colors">
                                        <i class="fas fa-circle-question text-lg"></i>
                                    </div>
                                    <textarea id="poll-question" required rows="3" placeholder="Contoh: Bagaimana pendapat Anda tentang materi pembelajaran hari ini?" class="w-full border-2 border-slate-200 rounded-2xl pl-12 pr-4 py-3.5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all resize-none bg-slate-50 focus:bg-white placeholder:font-normal"></textarea>
                                </div>
                            </div>

                            {{-- PILIHAN JAWABAN --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center justify-between">
                                    <span>Pilihan Jawaban <span class="text-red-500">*</span></span>
                                    <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-1 rounded-md">Min. 2 Pilihan</span>
                                </label>
                                
                                <div id="options-container" class="space-y-3">
                                    <div class="flex items-center gap-3 group/option">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                                <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">A</span>
                                            </div>
                                            <input type="text" required placeholder="Sangat Baik" class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 group/option">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                                <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">B</span>
                                            </div>
                                            <input type="text" required placeholder="Perlu Ditingkatkan" class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" id="btn-add-option" onclick="addOption()" class="mt-4 w-full py-3.5 border-2 border-dashed border-slate-300 rounded-xl text-sm font-bold text-slate-500 hover:text-[#0071BC] hover:border-[#0071BC] hover:bg-blue-50/50 flex items-center justify-center gap-2 transition-all">
                                    <i class="fas fa-plus"></i> Tambah Pilihan Lain
                                </button>
                            </div>

                            <div class="pt-6 border-t border-slate-100 flex justify-end">
                                <button type="submit" id="btn-publish-poll" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-[#0071BC] to-[#005B94] hover:shadow-lg hover:shadow-blue-500/30 font-bold text-white rounded-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <i class="fas fa-paper-plane"></i> Publikasikan Polling
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN (TABS RIWAYAT POLLING) --}}
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 flex flex-col h-[750px]">
                        
                        {{-- KETERANGAN WARNA TARGET --}}
                        <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-200 mb-5 shrink-0">
                            <span class="text-[10px] font-bold text-slate-500 w-full"><i class="fas fa-info-circle"></i> Info Target:</span>
                            <span class="text-[9px] font-bold bg-amber-50 text-amber-600 border border-amber-200 px-2 py-1 rounded flex items-center gap-1"><i class="fas fa-user-graduate"></i> Siswa</span>
                            <span class="text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 px-2 py-1 rounded flex items-center gap-1"><i class="fas fa-user-friends"></i> Orang Tua</span>
                            <span class="text-[9px] font-bold bg-purple-50 text-purple-600 border border-purple-200 px-2 py-1 rounded flex items-center gap-1"><i class="fas fa-users"></i> Keduanya</span>
                        </div>

                        {{-- TABS NAVIGASI --}}
                        <div class="flex space-x-2 border-b border-slate-200 mb-5 shrink-0">
                            <button onclick="switchPollTab('dibuat')" id="tab-btn-dibuat" class="pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-[#0071BC] text-[#0071BC] transition-colors">
                                Polling Kelas Saya
                            </button>
                            <button onclick="switchPollTab('masuk')" id="tab-btn-masuk" class="pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors relative">
                                Dari Sekolah
                                @if(count($pollingDariSekolah ?? []) > 0)
                                    <span class="absolute top-0 right-2 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                                @endif
                            </button>
                        </div>

                        {{-- KONTEN TAB 1: POLLING DIBUAT (Oleh Guru Ini) --}}
                        <div id="tab-content-dibuat" class="flex flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2 pb-4">
                            @forelse($polls ?? [] as $poll)
                                @php
                                    $targetAudiens = $poll->target ?? 'Semua Warga Sekolah';
                                    
                                    if (str_contains(strtolower($targetAudiens), 'siswa')) {
                                        $bgTarget = 'bg-amber-50 text-amber-600 border-amber-200';
                                        $iconTarget = 'fa-user-graduate';
                                    } elseif (str_contains(strtolower($targetAudiens), 'orang tua')) {
                                        $bgTarget = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                        $iconTarget = 'fa-user-friends';
                                    } else {
                                        $bgTarget = 'bg-purple-50 text-purple-600 border-purple-200';
                                        $iconTarget = 'fa-users';
                                    }
                                @endphp
                                
                                <div onclick="openGraphModal({{ $poll->id }}, '{{ addslashes($poll->question) }}', '{{ $poll->nama_kelas ?? 'Semua Kelas (Yang Saya Ajar)' }}')"
                                     class="shrink-0 p-5 border-2 border-slate-100 rounded-2xl bg-white hover:border-[#0071BC]/30 hover:shadow-md hover:-translate-y-1 transition-all cursor-pointer group relative overflow-hidden">
                                    
                                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                                        <button onclick="event.stopPropagation(); hapusPolling({{ $poll->id }})" class="w-7 h-7 bg-red-50 text-red-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors shadow-sm" title="Hapus Polling">
                                            <i class="fas fa-trash-alt text-[10px]"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-[#0071BC]"></div>
                                    <div class="pl-2">
                                        <div class="flex items-center justify-between mb-3 pr-6">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[10px] font-bold text-[#0071BC] bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md flex items-center gap-1.5 w-max">
                                                    <i class="fas fa-chalkboard"></i> {{ $poll->nama_kelas ?? 'Semua Kelas (Yang Saya Ajar)' }}
                                                </span>
                                                <span class="text-[9px] font-bold {{ $bgTarget }} px-2 py-0.5 rounded-md flex items-center gap-1 w-max border shadow-sm">
                                                    <i class="fas {{ $iconTarget }}"></i> {{ $targetAudiens }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-400">
                                                {{ \Carbon\Carbon::parse($poll->created_at)->format('d M') }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700 leading-relaxed break-words group-hover:text-[#0071BC] transition-colors pr-4">{{ $poll->question }}</p>
                                    </div>

                                    <div class="absolute inset-0 bg-white/80 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all duration-300 pointer-events-none z-10 rounded-2xl">
                                        <span class="px-4 py-2 bg-[#0071BC] text-white text-xs font-bold rounded-lg shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">Lihat Detail Grafik</span>
                                    </div>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 mt-2">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 text-slate-300 text-3xl">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-700 mb-1">Belum Ada Polling</h4>
                                    <p class="text-xs font-medium text-slate-400">Polling yang Anda buat akan muncul di sini.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- KONTEN TAB 2: POLLING DARI SEKOLAH (Kepsek -> Guru) --}}
                        <div id="tab-content-masuk" class="hidden flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2 pb-4">
                            @forelse($pollingDariSekolah ?? [] as $pollSekolah)
                                @php
                                    $pilihanJawabanSekolah = \App\Models\PollOption::where('poll_id', $pollSekolah->id)->get();
                                    $targetSekolah = $pollSekolah->target ?? 'Semua Warga Sekolah';
                                @endphp
                                
                                <div onclick='openVoteModal({{ $pollSekolah->id }}, "{!! addslashes($pollSekolah->question) !!}", {!! json_encode($pilihanJawabanSekolah) !!}, {{ $pollSekolah->has_voted ? "true" : "false" }}, {{ $pollSekolah->voted_option_id ?? "null" }})' 
                                     class="shrink-0 p-5 border-2 border-amber-100 rounded-2xl bg-gradient-to-br {{ ($pollSekolah->has_voted ?? false) ? 'from-slate-50 to-white hover:border-slate-300' : 'from-amber-50/50 to-white hover:border-amber-300' }} hover:shadow-md hover:-translate-y-1 transition-all cursor-pointer group relative overflow-hidden">
                                    
                                    <div class="absolute top-0 left-0 w-1.5 h-full {{ ($pollSekolah->has_voted ?? false) ? 'bg-emerald-500' : 'bg-amber-400' }}"></div>
                                    <div class="pl-2">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[10px] font-bold {{ ($pollSekolah->has_voted ?? false) ? 'text-emerald-700 bg-emerald-100' : 'text-amber-700 bg-amber-100' }} px-2.5 py-1 rounded-md flex items-center gap-1.5 w-max">
                                                    <i class="fas fa-building"></i> Dari: {{ $pollSekolah->author_role ?? 'Manajemen Sekolah' }}
                                                </span>
                                                <span class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 px-2 py-0.5 rounded-md flex items-center gap-1.5 w-max">
                                                    <i class="fas fa-bullseye text-blue-500"></i> Untuk: {{ $targetSekolah }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-400">
                                                {{ \Carbon\Carbon::parse($pollSekolah->created_at)->format('d M Y') }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 leading-relaxed break-words group-hover:text-amber-600 transition-colors">{{ $pollSekolah->question }}</p>
                                    </div>
                                    <div class="absolute bottom-3 right-4 opacity-0 group-hover:opacity-100 transition-opacity {{ ($pollSekolah->has_voted ?? false) ? 'text-emerald-600' : 'text-amber-600' }} text-xs font-bold">
                                        @if($pollSekolah->has_voted ?? false)
                                            <i class="fas fa-check-circle mr-1"></i> Lihat Jawaban
                                        @else
                                            <i class="fas fa-vote-yea mr-1"></i> Isi Polling
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 mt-2">
                                    <i class="fas fa-building text-3xl text-slate-300 mb-3"></i>
                                    <h4 class="font-bold text-slate-700 mb-1">Kotak Masuk Kosong</h4>
                                    <p class="text-xs font-medium text-slate-400">Tidak ada polling masuk dari pihak manajemen sekolah.</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL GRAFIK HASIL POLLING (CHART.JS)      --}}
    {{-- ========================================== --}}
    <div id="graph-modal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0 px-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl transform scale-95 transition-transform duration-300 overflow-hidden flex flex-col" id="graph-modal-content">
            
            <div class="px-6 md:px-8 py-5 md:py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-lg shadow-sm border border-blue-100">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">Detail Analisis Polling</h3>
                    </div>
                </div>
                <button onclick="closeGraphModal()" class="w-10 h-10 rounded-xl bg-white text-slate-400 hover:text-red-500 hover:bg-red-50 shadow-sm border border-slate-200 transition-all flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6 md:p-8 flex-1 overflow-y-auto custom-scrollbar relative">
                {{-- LOADER --}}
                <div id="chart-loader" class="absolute inset-0 bg-white z-10 flex flex-col items-center justify-center hidden">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-[#0071BC] mb-3"></i>
                    <p class="text-slate-500 font-bold text-sm">Mengambil Data Suara...</p>
                </div>

                <div class="mb-6">
                    <span id="modal-class-name" class="text-[10px] font-bold text-[#0071BC] bg-blue-50 px-2.5 py-1 rounded-md uppercase tracking-wider mb-3 flex items-center gap-1.5 w-fit border border-blue-100 shadow-sm"></span>
                    <p id="graph-modal-question" class="text-lg font-extrabold text-slate-800 leading-snug"></p>
                </div>
                
                <div class="relative h-72 md:h-80 w-full">
                    <canvas id="pollChart"></canvas>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
                <button onclick="closeGraphModal()" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-100 transition-colors shadow-sm">
                    Tutup Grafik
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL VOTING (MENGISI POLLING DARI SEKOLAH)--}}
    {{-- ========================================== --}}
    <div id="vote-modal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0 px-4">
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
                
                <form id="voteForm">
                    <input type="hidden" id="votePollId" name="poll_id">
                    <div id="voteOptionsContainer" class="space-y-3 mb-8 max-h-64 overflow-y-auto custom-scrollbar pr-2"></div>
                    
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
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // FUNGSI SWITCH TAB
    function switchPollTab(tab) {
        const btnDibuat = document.getElementById('tab-btn-dibuat');
        const btnMasuk = document.getElementById('tab-btn-masuk');
        const contentDibuat = document.getElementById('tab-content-dibuat');
        const contentMasuk = document.getElementById('tab-content-masuk');

        if (tab === 'dibuat') {
            btnDibuat.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-[#0071BC] text-[#0071BC] transition-colors";
            btnMasuk.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors relative";
            contentDibuat.classList.remove('hidden');
            contentDibuat.classList.add('flex');
            contentMasuk.classList.add('hidden');
            contentMasuk.classList.remove('flex');
        } else {
            btnMasuk.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-[#0071BC] text-[#0071BC] transition-colors relative";
            btnDibuat.className = "pb-3 px-2 flex-1 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors";
            contentMasuk.classList.remove('hidden');
            contentMasuk.classList.add('flex');
            contentDibuat.classList.add('hidden');
            contentDibuat.classList.remove('flex');
        }
    }

    // ==========================================
    // LOGIKA MODAL GRAFIK CHART.JS (TAB KELAS SAYA)
    // ==========================================
    let currentChart = null; 

    async function openGraphModal(pollId, question, className) {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        const loader = document.getElementById('chart-loader');
        
        document.getElementById('graph-modal-question').textContent = question;
        document.getElementById('modal-class-name').innerHTML = `<i class="fas fa-users"></i> ` + className;

        modal.classList.remove('hidden');
        loader.classList.remove('hidden'); 
        
        setTimeout(() => {
            modal.classList.replace('opacity-0', 'opacity-100');
            modalContent.classList.replace('scale-95', 'scale-100');
        }, 10);

        try {
            // Menggunakan route name bawaan Laravel agar anti-nyasar
            let url = "{{ route('lms.teacherPolling.breakdown', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'id' => ':id']) }}";
            url = url.replace(':id', pollId);

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const result = await response.json();
            
            if(result.success) {
                renderChart(result.labels, result.datasets);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error(error);
            Swal.fire({icon: 'error', title: 'Oops...', text: 'Gagal mengambil data breakdown polling.', confirmButtonColor: '#0071BC'});
        } finally {
            loader.classList.add('hidden'); 
        }
    }

    function closeGraphModal() {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        
        modal.classList.replace('opacity-100', 'opacity-0');
        modalContent.classList.replace('scale-100', 'scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); 
    }

    function renderChart(labels, datasetsObj) {
        const ctx = document.getElementById('pollChart').getContext('2d');

        if (currentChart) {
            currentChart.destroy();
        }

        const datasetsArray = [
            {
                label: 'Siswa',
                data: datasetsObj['Siswa'],
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                borderColor: 'rgb(245, 158, 11)',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Orang Tua',
                data: datasetsObj['Orang Tua'],
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Guru/Manajemen',
                data: datasetsObj['Guru/Manajemen'],
                backgroundColor: 'rgba(0, 113, 188, 0.8)',
                borderColor: 'rgb(0, 113, 188)',
                borderWidth: 1,
                borderRadius: 4
            }
        ];
        
        currentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasetsArray
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            font: { family: "'Inter', sans-serif", weight: 'bold' },
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' Suara';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: "'Inter', sans-serif" } },
                        grid: { color: 'rgba(241, 245, 249, 1)', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { family: "'Inter', sans-serif", weight: 'bold' } },
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
        
        document.getElementById('votePollId').value = pollId;
        document.getElementById('vote-question-text').textContent = question;
        
        const container = document.getElementById('voteOptionsContainer');
        container.innerHTML = ''; 
        
        const submitBtn = document.getElementById('btn-submit-vote');
        
        if (hasVoted) {
            const selectedOpt = options.find(opt => opt.id == votedOptionId);
            if (selectedOpt) {
                const text = selectedOpt.option_text || selectedOpt.text || selectedOpt.name;
                const div = document.createElement('div');
                div.className = 'flex items-center gap-4 p-5 rounded-2xl border-2 border-emerald-200 bg-emerald-50 mb-2 cursor-default';
                div.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white text-lg flex items-center justify-center shrink-0 shadow-md"><i class="fas fa-check"></i></div>
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
                        <input type="radio" name="option_id" value="${opt.id}" class="w-4 h-4 text-amber-500 focus:ring-amber-400 border-slate-300" required>
                        <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 group-hover:bg-amber-100 group-hover:text-amber-600 text-xs font-bold flex items-center justify-center shrink-0 transition-colors">${alphabet[index] || (index + 1)}</div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-amber-700 transition-colors">${text}</span>
                    `;
                    container.appendChild(div);
                });
            }
            submitBtn.style.display = 'flex';
        }
        
        modal.classList.remove('hidden');
        void modal.offsetWidth; 
        modal.classList.replace('opacity-0', 'opacity-100');
        content.classList.replace('scale-95', 'scale-100');
    }

    function closeVoteModal() {
        const modal = document.getElementById('vote-modal');
        const content = document.getElementById('vote-modal-content');
        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300); 
    }

    // SUBMIT JAWABAN GURU MENGISI POLLING KEPSEK VIA AJAX
    document.getElementById('voteForm').onsubmit = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-vote');
        const originalText = btn.innerHTML;
        
        const pollId = document.getElementById('votePollId').value;
        const selectedOption = document.querySelector('input[name="option_id"]:checked');

        if (!selectedOption) {
            Swal.fire({icon: 'warning', title: 'Pilih Jawaban', text: 'Silakan pilih salah satu opsi terlebih dahulu.', confirmButtonColor: '#F59E0B'});
            return;
        }

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
                body: JSON.stringify({ poll_id: pollId, option_id: selectedOption.value })
            });

            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || "Gagal mengirim suara");

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

    // FORM BUAT POLLING BAWAAN
    let opsiCount = 2;
    function tambahOpsi() {
        if(opsiCount >= 6) {
            alert('Maksimal 6 pilihan jawaban untuk menjaga kemudahan membaca.');
            return;
        }
        const container = document.getElementById('options-container');
        const letter = alphabet[opsiCount];
        const newDiv = document.createElement('div');
        newDiv.className = 'flex items-center gap-3 group animate-slide-up group/option';
        newDiv.innerHTML = `
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">${letter}</span>
                </div>
                <input type="text" name="options[]" required class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300" placeholder="Pilihan Tambahan">
            </div>
            <button type="button" onclick="this.parentElement.remove(); reindexOptions();" class="w-12 h-[46px] shrink-0 border-2 border-red-100 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white hover:border-red-500 transition-all cursor-pointer shadow-sm">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
        container.appendChild(newDiv);
        opsiCount++;
    }

    function reindexOptions() {
        const divs = document.getElementById('options-container').querySelectorAll('.group\\/option');
        opsiCount = divs.length;
        divs.forEach((opt, index) => {
            const span = opt.querySelector('span');
            if(span) span.textContent = alphabet[index] || (index + 1); 
        });
    }

    function openModalBuatPolling() {
        const modal = document.getElementById('modalBuatPolling');
        const content = document.getElementById('modalBuatPollingContent');
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.replace('opacity-0', 'opacity-100'); 
            content.classList.replace('scale-95', 'scale-100'); 
        }, 10);
    }

    function closeModalBuatPolling() {
        const modal = document.getElementById('modalBuatPolling');
        const content = document.getElementById('modalBuatPollingContent');
        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // SUBMIT FORM BUAT POLLING AJAX
    document.getElementById('form-polling').onsubmit = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-publish-poll');
        const originalText = btn.innerHTML;

        const targetRole = document.getElementById('poll-target').value;
        const classSelect = document.getElementById('poll-class');
        const classId = classSelect.value === '0' ? null : classSelect.value;
        const question = document.getElementById('poll-question').value;
        
        const optionsInputs = document.querySelectorAll('.poll-option');
        let options = [];
        optionsInputs.forEach(input => {
            if(input.value.trim() !== '') options.push(input.value.trim());
        });

        if(options.length < 2) {
            Swal.fire({icon: 'warning', title: 'Pilihan Kurang', text: 'Minimal 2 pilihan jawaban!', confirmButtonColor: '#0071BC'});
            return;
        }

        btn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...`;
        btn.disabled = true;

        try {
            const url = `{{ route('lms.teacherPolling.save', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ target: targetRole, class_id: classId, question: question, options: options })
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Gagal menyimpan ke database");

            Swal.fire({icon: 'success', title: 'Berhasil!', text: result.message, confirmButtonColor: '#0071BC', timer: 2000, showConfirmButton: false})
                .then(() => window.location.reload());
            
        } catch (error) {
            Swal.fire({icon: 'error', title: 'Oops...', text: error.message, confirmButtonColor: '#0071BC'});
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    // FUNGSI HAPUS POLLING
    function hapusPolling(pollId) {
        Swal.fire({
            title: 'Hapus Polling ini?',
            text: "Data jawaban juga akan ikut terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    let url = "{{ route('lms.teacherPolling.destroy', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'id' => ':id']) }}";
                    url = url.replace(':id', pollId);

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });

                    const res = await response.json();
                    if (!response.ok) throw new Error(res.message);

                    Swal.fire({icon: 'success', title: 'Terhapus!', text: res.message, showConfirmButton: false, timer: 1500})
                        .then(() => window.location.reload());

                } catch (error) {
                    Swal.fire({icon: 'error', title: 'Gagal!', text: error.message, confirmButtonColor: '#0071BC'});
                }
            }
        });
    }
</script>