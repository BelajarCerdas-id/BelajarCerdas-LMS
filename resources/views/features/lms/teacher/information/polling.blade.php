@include('components/sidebar-beranda', [
    'headerSideNav' => 'Polling'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">
        <div class="pt-8 mx-6 lg:mx-10">

            {{-- HEADER MANAJEMEN POLLING --}}
            <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-transparent rounded-full -translate-y-1/2 translate-x-1/3 opacity-70 pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-12 h-12 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-blue-100 shrink-0">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Manajemen Polling</h1>
                    </div>
                    <p class="text-slate-500 text-sm font-medium md:ml-16">Buat jajak pendapat untuk siswa dan orang tua, serta lihat polling dari pihak sekolah.</p>
                </div>
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
                                {{-- TARGET AUDIENS (DIPERJELAS) --}}
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

                                {{-- TARGET KELAS --}}
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
                                            <option value="0" data-name="Semua Kelas">Semua Kelas (Global)</option>
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
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 flex flex-col h-[600px]">
                        
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
                        <div id="tab-content-dibuat" class="flex flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            @forelse($pollingSaya ?? $polls ?? [] as $poll)
                                @php
                                    $pilihanJawaban = \App\Models\PollOption::where('poll_id', $poll->id)->get();
                                    $targetAudiens = $poll->target ?? 'Semua Warga Sekolah';
                                    
                                    // LOGIKA WARNA BADGE TARGET
                                    if (str_contains(strtolower($targetAudiens), 'siswa')) {
                                        $bgTarget = 'bg-blue-100 text-blue-700';
                                        $iconTarget = 'fa-user-graduate';
                                    } elseif (str_contains(strtolower($targetAudiens), 'orang tua')) {
                                        $bgTarget = 'bg-purple-100 text-purple-700';
                                        $iconTarget = 'fa-user-friends';
                                    } else {
                                        $bgTarget = 'bg-emerald-100 text-emerald-700';
                                        $iconTarget = 'fa-globe';
                                    }
                                @endphp
                                
                                <div data-poll='{{ json_encode([
                                        "poll_id" => $poll->id,
                                        "question" => $poll->question,
                                        "class_name" => $poll->nama_kelas ?? "Semua Kelas", {{-- 👈 PERBAIKAN 1: nama_kelas --}}
                                        "target" => $targetAudiens,
                                        "date" => \Carbon\Carbon::parse($poll->created_at)->format("d M Y, H:i"),
                                        "options" => $pilihanJawaban,
                                        "is_voting" => false,
                                        "has_voted" => false,
                                        "voted_option_id" => null
                                    ]) }}' 
                                    onclick="openPollDetailModal(this)"
                                    class="shrink-0 p-5 border-2 border-slate-100 rounded-2xl bg-white hover:border-[#0071BC]/30 hover:shadow-md hover:-translate-y-1 transition-all cursor-pointer group relative overflow-hidden">
                                    
                                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                        <button onclick="event.stopPropagation(); hapusPolling({{ $poll->id }})" class="w-7 h-7 bg-red-50 text-red-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors shadow-sm" title="Hapus Polling">
                                            <i class="fas fa-trash-alt text-[10px]"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-[#0071BC]"></div>
                                    <div class="pl-2">
                                        <div class="flex items-center justify-between mb-3 pr-6">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md flex items-center gap-1.5 w-max">
                                                    <i class="fas fa-chalkboard"></i> {{ $poll->nama_kelas ?? 'Semua Kelas' }} {{-- 👈 PERBAIKAN 2: nama_kelas --}}
                                                </span>
                                                {{-- BADGE TARGET DINAMIS --}}
                                                <span class="text-[9px] font-bold {{ $bgTarget }} px-2.5 py-1 rounded-md flex items-center gap-1.5 w-max">
                                                    <i class="fas {{ $iconTarget }}"></i> {{ $targetAudiens }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-400">
                                                {{ \Carbon\Carbon::parse($poll->created_at)->format('d M') }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700 leading-relaxed break-words group-hover:text-[#0071BC] transition-colors pr-4">{{ $poll->question }}</p>
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
                        <div id="tab-content-masuk" class="hidden flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            @forelse($pollingDariSekolah ?? [] as $pollSekolah)
                                @php
                                    $pilihanJawabanSekolah = \App\Models\PollOption::where('poll_id', $pollSekolah->id)->get();
                                    $targetSekolah = $pollSekolah->target ?? 'Semua Warga Sekolah';
                                @endphp
                                
                                <div data-poll='{{ json_encode([
                                        "poll_id" => $pollSekolah->id,
                                        "question" => $pollSekolah->question,
                                        "class_name" => "Pengirim: " . ($pollSekolah->author_role ?? "Manajemen Sekolah"),
                                        "target" => $targetSekolah,
                                        "date" => \Carbon\Carbon::parse($pollSekolah->created_at)->format("d M Y, H:i"),
                                        "options" => $pilihanJawabanSekolah,
                                        "is_voting" => true,
                                        "has_voted" => $pollSekolah->has_voted ?? false,
                                        "voted_option_id" => $pollSekolah->voted_option_id ?? null
                                    ]) }}' 
                                    onclick="openPollDetailModal(this)"
                                    class="shrink-0 p-5 border-2 border-amber-100 rounded-2xl bg-gradient-to-br {{ ($pollSekolah->has_voted ?? false) ? 'from-slate-50 to-white hover:border-slate-300' : 'from-amber-50/50 to-white hover:border-amber-300' }} hover:shadow-md hover:-translate-y-1 transition-all cursor-pointer group relative overflow-hidden">
                                    
                                    <div class="absolute top-0 left-0 w-1.5 h-full {{ ($pollSekolah->has_voted ?? false) ? 'bg-emerald-500' : 'bg-amber-400' }}"></div>
                                    <div class="pl-2">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[10px] font-bold {{ ($pollSekolah->has_voted ?? false) ? 'text-emerald-700 bg-emerald-100' : 'text-amber-700 bg-amber-100' }} px-2.5 py-1 rounded-md flex items-center gap-1.5 w-max">
                                                    <i class="fas fa-building"></i> Dari Sekolah
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
    {{-- MODAL DETAIL / VOTING POLLING --}}
    {{-- ========================================== --}}
    <div id="pollDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300 px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer" onclick="closePollDetailModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 md:p-8 transform scale-95 transition-all duration-300" id="pollDetailContent">
            <button onclick="closePollDetailModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors z-10">
                <i class="fas fa-times text-sm"></i>
            </button>
            
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-blue-50 text-[#0071BC] rounded-xl flex items-center justify-center text-lg shadow-inner border border-blue-100 shrink-0" id="modal-icon">
                    <i class="fas fa-poll"></i>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-800" id="modal-title">Detail Polling</h3>
                    <p class="text-[10px] md:text-xs font-medium text-slate-500" id="modal-subtitle">Pratinjau jajak pendapat</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-1 border-r border-slate-100 pr-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Target Audiens</span>
                        <span id="detailPollTarget" class="text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-md inline-block">Target</span>
                    </div>
                    <div class="col-span-1 border-r border-slate-100 px-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kelas / Pengirim</span>
                        <span id="detailPollClass" class="text-[11px] font-bold text-[#0071BC] bg-blue-50 border border-blue-100 px-2 py-1 rounded-md inline-block truncate w-full">Kelas</span>
                    </div>
                    <div class="col-span-1 pl-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Dibuat Pada</span>
                        <span id="detailPollDate" class="text-[11px] font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded-md inline-block">Tanggal</span>
                    </div>
                </div>

                <div class="pt-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Pertanyaan</span>
                    <p id="detailPollQuestion" class="text-sm font-bold text-slate-800 leading-relaxed p-4 bg-slate-50 rounded-xl border border-slate-100"></p>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Pilihan Jawaban</span>
                    
                    {{-- FORM UNTUK VOTING --}}
                    <form id="voteForm" class="hidden">
                        <input type="hidden" id="votePollId" name="poll_id">
                        <div id="voteOptionsContainer" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2 mb-4"></div>
                        <button type="submit" id="btn-submit-vote" class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i> Kirim Suara Saya
                        </button>
                    </form>

                    {{-- TAMPILAN READ-ONLY --}}
                    <div id="detailPollOptions" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2"></div>
                </div>
            </div>

            <button id="btn-close-modal" onclick="closePollDetailModal()" class="w-full py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all transform hover:-translate-y-0.5">
                Tutup Detail
            </button>
        </div>
    </div>
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

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

    // FUNGSI MODAL DETAIL & VOTING
    function openPollDetailModal(element) {
        try {
            const data = JSON.parse(element.getAttribute('data-poll'));
            
            document.getElementById('detailPollQuestion').innerText = data.question;
            document.getElementById('detailPollClass').innerText = data.class_name;
            document.getElementById('detailPollTarget').innerText = data.target; 
            document.getElementById('detailPollDate').innerText = data.date;

            const readOnlyContainer = document.getElementById('detailPollOptions');
            const voteForm = document.getElementById('voteForm');
            const voteContainer = document.getElementById('voteOptionsContainer');
            const btnClose = document.getElementById('btn-close-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalSubtitle = document.getElementById('modal-subtitle');
            const modalIcon = document.getElementById('modal-icon');
            const submitBtn = document.getElementById('btn-submit-vote');

            readOnlyContainer.innerHTML = ''; 
            voteContainer.innerHTML = '';

            // JIKA MODE VOTING (Polling dari Sekolah) DAN BELUM VOTE
            if(data.is_voting && !data.has_voted) {
                modalTitle.innerText = "Berikan Suara";
                modalSubtitle.innerText = "Pilih salah satu jawaban di bawah ini";
                modalIcon.className = "w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-lg shadow-inner border border-amber-100 shrink-0";
                modalIcon.innerHTML = '<i class="fas fa-vote-yea"></i>';
                
                document.getElementById('votePollId').value = data.poll_id;
                voteForm.classList.remove('hidden');
                readOnlyContainer.classList.add('hidden');
                btnClose.classList.add('hidden'); 

                if (data.options && data.options.length > 0) {
                    data.options.forEach((opt, index) => {
                        const text = opt.option_text || opt.text || opt.name || opt; 
                        const div = document.createElement('label');
                        div.className = 'flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white shadow-sm cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition-all';
                        div.innerHTML = `
                            <input type="radio" name="option_id" value="${opt.id}" class="w-4 h-4 text-amber-500 focus:ring-amber-400" required>
                            <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 text-xs font-bold flex items-center justify-center shrink-0 border border-amber-200">
                                ${alphabet[index] || (index + 1)}
                            </div>
                            <span class="text-sm font-medium text-slate-700">${text}</span>
                        `;
                        voteContainer.appendChild(div);
                    });
                }
            } 
            // JIKA MODE PRATINJAU ATAU SUDAH VOTE
            else {
                modalTitle.innerText = data.has_voted ? "Jawaban Anda" : "Detail Polling";
                modalSubtitle.innerText = data.has_voted ? "Anda telah mengisi polling ini" : "Pratinjau jajak pendapat kelas Anda";
                modalIcon.className = data.has_voted 
                    ? "w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-lg shadow-inner border border-emerald-100 shrink-0"
                    : "w-10 h-10 bg-blue-50 text-[#0071BC] rounded-xl flex items-center justify-center text-lg shadow-inner border border-blue-100 shrink-0";
                modalIcon.innerHTML = data.has_voted ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-poll"></i>';

                voteForm.classList.add('hidden');
                readOnlyContainer.classList.remove('hidden');
                btnClose.classList.remove('hidden');

                if (data.options && data.options.length > 0) {
                    data.options.forEach((opt, index) => {
                        // Jika guru sudah vote, hanya tampilkan yang dia pilih
                        if (data.has_voted && opt.id != data.voted_option_id) return;

                        const text = opt.option_text || opt.text || opt.name || opt; 
                        const div = document.createElement('div');
                        
                        if(data.has_voted && opt.id == data.voted_option_id) {
                            div.className = 'flex items-center gap-3 p-4 rounded-xl border-2 border-emerald-200 bg-emerald-50 shadow-sm';
                            div.innerHTML = `
                                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold flex items-center justify-center shrink-0">
                                    <i class="fas fa-check"></i>
                                </div>
                                <span class="text-sm font-bold text-slate-800">${text}</span>
                            `;
                        } else {
                            div.className = 'flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white shadow-sm';
                            div.innerHTML = `
                                <div class="w-6 h-6 rounded-full bg-blue-50 text-[#0071BC] text-xs font-bold flex items-center justify-center shrink-0 border border-blue-100">
                                    ${alphabet[index] || (index + 1)}
                                </div>
                                <span class="text-xs font-medium text-slate-700">${text}</span>
                            `;
                        }
                        readOnlyContainer.appendChild(div);
                    });
                } else {
                    readOnlyContainer.innerHTML = '<p class="text-xs text-slate-500 italic px-2">Opsi tidak tersedia.</p>';
                }
            }

            const modal = document.getElementById('pollDetailModal');
            const content = document.getElementById('pollDetailContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.replace('opacity-0', 'opacity-100');
                content.classList.replace('scale-95', 'scale-100');
            }, 10);
            
        } catch (error) {
            console.error('Error parsing poll data:', error);
            Swal.fire({icon: 'error', title: 'Oops...', text: 'Gagal memuat detail polling.', confirmButtonColor: '#0071BC'});
        }
    }

    function closePollDetailModal() {
        const modal = document.getElementById('pollDetailModal');
        const content = document.getElementById('pollDetailContent');
        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // FUNGSI SUBMIT VOTE (GURU NGISI POLLING KEPSEK)
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
            if (!response.ok || !result.success) throw new Error(result.message || "Gagal mengirim suara");

            closePollDetailModal();
            Swal.fire({icon: 'success', title: 'Berhasil!', text: result.message, confirmButtonColor: '#10B981', timer: 2000, showConfirmButton: false})
                .then(() => window.location.reload());
            
        } catch (error) {
            Swal.fire({icon: 'warning', title: 'Informasi', text: error.message, confirmButtonColor: '#F59E0B'});
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    // FUNGSI TAMBAH OPSI
    function addOption() {
        const container = document.getElementById('options-container');
        if (container.children.length >= 10) return; 
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 animate-fade-in group/option';
        div.innerHTML = `
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600"></span>
                </div>
                <input type="text" required placeholder="Pilihan Tambahan" class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300">
            </div>
            <button type="button" onclick="removeOption(this)" class="w-12 h-[46px] shrink-0 border-2 border-red-100 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white hover:border-red-500 transition-all cursor-pointer shadow-sm">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
        container.appendChild(div);
        updateNumbers();
    }

    function removeOption(btn) {
        btn.parentElement.remove();
        updateNumbers(); 
    }

    function updateNumbers() {
        const container = document.getElementById('options-container');
        const options = container.querySelectorAll('.group\\/option');
        const btnAdd = document.getElementById('btn-add-option');

        options.forEach((opt, index) => {
            const span = opt.querySelector('span');
            if(span) span.textContent = alphabet[index] || (index + 1); 
        });
        btnAdd.style.display = options.length >= 10 ? 'none' : 'flex';
    }

    // SUBMIT FORM BUAT POLLING AJAX
    document.getElementById('form-polling').onsubmit = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-publish-poll');
        const originalText = btn.innerHTML;

        const targetRole = document.getElementById('poll-target').value;
        const classSelect = document.getElementById('poll-class');
        const classId = classSelect.value === '0' ? null : classSelect.value;
        const className = classSelect.options[classSelect.selectedIndex]?.getAttribute('data-name');
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
                body: JSON.stringify({ 
                    target: targetRole,
                    class_id: classId,
                    question: question, 
                    options: options 
                })
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Gagal menyimpan ke database");

            Swal.fire({icon: 'success', title: 'Berhasil!', text: result.message, confirmButtonColor: '#0071BC', timer: 2000, showConfirmButton: false})
                .then(() => window.location.reload());
            
        } catch (error) {
            console.error(error);
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