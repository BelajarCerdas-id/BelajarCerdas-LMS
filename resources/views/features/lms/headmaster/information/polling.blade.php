@include('components/sidebar-beranda', ['headerSideNav' => 'Manajemen Polling'])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-b border-slate-200 px-6 py-6 md:px-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sticky top-0 z-30">
            <div>
                <a href="{{ route('lms.kepsek.dashboard', [
                    'role'       => Auth::user()->role,
                    'schoolName' => \Illuminate\Support\Str::slug(Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah ?? 'sekolah'),
                    'schoolId'   => Auth::user()->SchoolStaffProfile->school_partner_id ?? 0
                ]) }}" class="text-sm font-bold text-[#0071BC] hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Pusat Suara & Aspirasi</h1>
                <p class="text-xs font-medium text-slate-500 mt-1">Kelola dan pantau jejak pendapat warga sekolah secara real-time.</p>
            </div>
            
            <button onclick="openModalBuatPolling()" class="px-6 py-3 bg-[#0071BC] hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-200 transition-all flex items-center gap-2 active:scale-95 shrink-0">
                <i class="fas fa-plus"></i> Buat Polling Baru
            </button>
        </div>

        <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">

            {{-- FORM FILTER --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center gap-4">
                <div class="flex items-center gap-2 text-slate-600 font-bold text-sm shrink-0">
                    <i class="fas fa-filter text-[#0071BC]"></i> Filter Riwayat:
                </div>
                <form action="{{ route('kepsek.polling.index') }}" method="GET" class="flex flex-1 flex-col sm:flex-row gap-3 w-full">
                    
                    {{-- Filter Pembuat --}}
                    <select name="pembuat" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 focus:ring-[#0071BC] flex-1 appearance-none cursor-pointer">
                        <option value="">-- Semua Pembuat --</option>
                        <option value="Kepala Sekolah" {{ request('pembuat') == 'Kepala Sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                        <option value="Wakil Kepala Sekolah" {{ request('pembuat') == 'Wakil Kepala Sekolah' ? 'selected' : '' }}>Wakil Kepala Sekolah</option>
                        <option value="Guru" {{ request('pembuat') == 'Guru' ? 'selected' : '' }}>Guru</option>
                    </select>

                    {{-- Filter Target --}}
                    <select name="target" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 focus:ring-[#0071BC] flex-1 appearance-none cursor-pointer">
                        <option value="">-- Semua Target Audiens --</option>
                        <option value="Semua Warga Sekolah" {{ request('target') == 'Semua Warga Sekolah' ? 'selected' : '' }}>Semua Warga Sekolah</option>
                        <option value="Semua Guru" {{ request('target') == 'Semua Guru' ? 'selected' : '' }}>Semua Guru</option>
                        <option value="Semua Siswa" {{ request('target') == 'Semua Siswa' ? 'selected' : '' }}>Semua Siswa</option>
                        <option value="Semua Orang Tua" {{ request('target') == 'Semua Orang Tua' ? 'selected' : '' }}>Semua Orang Tua</option>
                    </select>

                    <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-bold text-xs transition-colors shrink-0">
                        Terapkan
                    </button>
                    
                    {{-- Tombol Reset hanya muncul jika ada filter yang aktif --}}
                    @if(request()->has('pembuat') && request('pembuat') != '' || request()->has('target') && request('target') != '')
                        <a href="{{ route('kepsek.polling.index') }}" class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-500 rounded-xl font-bold text-xs transition-colors shrink-0 flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            {{-- NOTIFIKASI --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl flex items-center gap-3 animate-fade-in">
                    <i class="fas fa-check-circle text-xl"></i> 
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl flex items-center gap-3 animate-fade-in">
                    <i class="fas fa-exclamation-triangle text-xl"></i> 
                    <span class="font-bold text-sm">{{ session('error') }}</span>
                </div>
            @endif

            {{-- KETERANGAN WARNA TARGET --}}
            <div class="flex flex-wrap items-center gap-2 md:gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-2">
                <span class="text-xs font-bold text-slate-500 mr-2"><i class="fas fa-info-circle"></i> Keterangan Target:</span>
                <span class="text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200 px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm"><i class="fas fa-chalkboard-teacher"></i> Guru</span>
                <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm"><i class="fas fa-user-friends"></i> Orang Tua</span>
                <span class="text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm"><i class="fas fa-user-graduate"></i> Siswa</span>
                <span class="text-[10px] font-bold bg-purple-50 text-purple-600 border border-purple-200 px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm"><i class="fas fa-users"></i> Semua Warga</span>
            </div>

            {{-- GRID DAFTAR POLLING --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($polls as $poll)
                    @php
                        $namaKelas = isset($poll->nama_kelas) && $poll->nama_kelas !== 'Semua Kelas (Global)' ? $poll->nama_kelas : 'Semua Kelas (Global)';
                        
                        // LOGIKA WARNA TARGET
                        $targetAudiens = $poll->target ?? 'Semua Warga Sekolah';
                        if (str_contains(strtolower($targetAudiens), 'siswa')) {
                            $bgTarget = 'bg-amber-50 text-amber-600 border-amber-200';
                            $iconTarget = 'fa-user-graduate';
                        } elseif (str_contains(strtolower($targetAudiens), 'guru')) {
                            $bgTarget = 'bg-blue-50 text-blue-600 border-blue-200';
                            $iconTarget = 'fa-chalkboard-teacher';
                        } elseif (str_contains(strtolower($targetAudiens), 'orang tua')) {
                            $bgTarget = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                            $iconTarget = 'fa-user-friends';
                        } else {
                            $bgTarget = 'bg-purple-50 text-purple-600 border-purple-200';
                            $iconTarget = 'fa-users';
                        }
                    @endphp

                    {{-- Card bisa diklik untuk membuka grafik AJAX --}}
                    <div onclick='openGraphModal({{ $poll->id }}, "{!! addslashes($poll->question) !!}", "{!! $namaKelas !!}")' 
                         class="cursor-pointer bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 hover:shadow-xl hover:border-[#0071BC] transition-all duration-300 flex flex-col h-full group relative overflow-hidden">
                        
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <span class="text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-wider {{ $poll->status == 'active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                {{ $poll->status == 'active' ? '● Berlangsung' : 'Selesai' }}
                            </span>
                            
                            <form action="{{ route('kepsek.polling.destroy', $poll->id) }}" method="POST" onsubmit="return confirm('Hapus polling ini? Seluruh data suara akan hilang.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation();" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm tooltip" title="Hapus Polling">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <h3 class="font-bold text-slate-800 text-base md:text-lg leading-snug mb-3 group-hover:text-[#0071BC] transition-colors pr-2 relative z-10">{{ $poll->question }}</h3>
                        
                        <div class="flex flex-wrap gap-2 mb-5 relative z-10">
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-md uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-user-edit"></i> {{ $poll->author_role }}
                            </span>
                            <span class="text-[10px] font-bold {{ $bgTarget }} px-2.5 py-1 rounded-md uppercase tracking-wider flex items-center gap-1 shadow-sm">
                                <i class="fas {{ $iconTarget }}"></i> {{ $targetAudiens }}
                            </span>
                            @if($namaKelas !== 'Semua Kelas (Global)')
                                <span class="text-[10px] font-bold text-slate-600 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-md uppercase tracking-wider flex items-center gap-1 shadow-sm">
                                    <i class="fas fa-chalkboard-teacher"></i> {{ $namaKelas }}
                                </span>
                            @endif
                        </div>

                        {{-- HASIL VISUAL GLOBAL (Preview Singkat) --}}
                        <div class="space-y-4 mb-6 flex-1 relative z-10">
                            @foreach($poll->PollOptions as $index => $opt)
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1.5">
                                        <span class="text-slate-600 truncate pr-4">{{ $opt->option_text }}</span>
                                        <span class="text-[#0071BC] shrink-0">{{ $opt->percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-50">
                                        <div class="bg-gradient-to-r from-[#0071BC] to-blue-400 h-full rounded-full transition-all duration-1000" style="width: {{ $opt->percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-auto pt-4 border-t border-slate-100 flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest relative z-10">
                            <span><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($poll->created_at)->translatedFormat('d M') }}</span>
                            <span class="text-slate-700 bg-slate-100 px-3 py-1 rounded-md shadow-sm">Total: {{ $poll->total_votes }} Suara</span>
                        </div>
                        
                        {{-- Hover Overlay untuk memperjelas bisa diklik --}}
                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all duration-300 pointer-events-none z-20">
                            <span class="px-5 py-2.5 bg-[#0071BC] text-white font-bold rounded-xl shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300"><i class="fas fa-chart-bar mr-2"></i>Lihat Detail Grafik</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-[3rem] bg-white/50">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-5 text-slate-300">
                            <i class="fas fa-poll-h text-4xl"></i>
                        </div>
                        <h3 class="text-slate-600 font-bold text-lg">Belum Ada Polling Aktif</h3>
                        <p class="text-slate-400 text-sm mt-1 mb-6 text-center max-w-xs">Gunakan fitur ini untuk mendengarkan aspirasi dari guru, siswa, maupun orang tua.</p>
                    </div>
                @endforelse
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
                    <p id="modal-question" class="text-lg font-extrabold text-slate-800 leading-snug"></p>
                </div>
                
                {{-- Canvas untuk Chart.js (Grouped Bar Chart) --}}
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
    
    {{-- MODAL CREATE POLLING --}}
    <div id="modalBuatPolling" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300" id="modalBuatPollingContent">
            
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="font-black text-slate-800 text-xl flex items-center gap-3">
                        <span class="w-10 h-10 bg-[#0071BC] text-white rounded-xl flex items-center justify-center shadow-md shadow-blue-200"><i class="fas fa-plus"></i></span>
                        Buat Polling Baru
                    </h3>
                </div>
                <button type="button" onclick="closeModalBuatPolling()" class="w-10 h-10 rounded-xl bg-white text-slate-400 hover:text-red-500 hover:bg-red-50 shadow-sm border border-slate-200 transition-all flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('kepsek.polling.store') }}" method="POST" class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                @csrf
                
                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.1em] mb-2.5">Pertanyaan Utama</label>
                    <textarea name="question" required rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-2xl px-5 py-4 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] focus:bg-white transition-all resize-none" placeholder="Apa yang ingin Anda tanyakan?"></textarea>
                </div>

                {{-- Target & Kelas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.1em] mb-2.5">Target Audiens</label>
                        <select name="target" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-2xl px-4 py-3.5 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] appearance-none cursor-pointer">
                            <option value="Semua Warga Sekolah">Semua Warga Sekolah</option>
                            <option value="Semua Guru">Semua Guru</option>
                            <option value="Semua Siswa">Semua Siswa</option>
                            <option value="Semua Orang Tua">Semua Orang Tua</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.1em] mb-2.5">Spesifik Kelas (Opsional)</label>
                        <select name="class_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-2xl px-4 py-3.5 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] appearance-none cursor-pointer">
                            <option value="">Semua Kelas (Global)</option>
                            @foreach($daftarKelas as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Opsi Jawaban --}}
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.1em] mb-3 flex justify-between items-center">
                        Pilihan Jawaban
                        <span class="text-[9px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded">Min. 2 Opsi</span>
                    </label>
                    <div id="optionsContainer" class="space-y-3">
                        <div class="flex items-center gap-3 group">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0071BC] font-black text-xs flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">A</div>
                            <input type="text" name="options[]" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-5 py-3 focus:bg-white focus:border-[#0071BC] transition-all" placeholder="Masukkan jawaban A">
                        </div>
                        <div class="flex items-center gap-3 group">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0071BC] font-black text-xs flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">B</div>
                            <input type="text" name="options[]" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-5 py-3 focus:bg-white focus:border-[#0071BC] transition-all" placeholder="Masukkan jawaban B">
                        </div>
                    </div>
                    
                    <button type="button" onclick="tambahOpsi()" class="mt-4 text-xs font-bold text-[#0071BC] hover:text-blue-800 flex items-center gap-2 group transition-all">
                        <i class="fas fa-plus-circle group-hover:rotate-90 transition-transform"></i> Tambah Opsi Lainnya
                    </button>
                </div>

                <div class="pt-6 border-t border-slate-100 flex gap-3">
                    <button type="button" onclick="closeModalBuatPolling()" class="flex-1 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" class="flex-[2] py-4 bg-[#0071BC] hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl shadow-blue-200 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Publikasikan 
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- CHART.JS SCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Modal Buat Polling Bawaan
        let opsiCount = 2;
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        function tambahOpsi() {
            if(opsiCount >= 6) {
                alert('Maksimal 6 pilihan jawaban untuk menjaga kemudahan membaca.');
                return;
            }
            const container = document.getElementById('optionsContainer');
            const letter = alphabet[opsiCount];
            const newDiv = document.createElement('div');
            newDiv.className = 'flex items-center gap-3 group animate-slide-up';
            newDiv.innerHTML = `
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0071BC] font-black text-xs flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">${letter}</div>
                <input type="text" name="options[]" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-5 py-3 focus:bg-white focus:border-[#0071BC] transition-all" placeholder="Masukkan jawaban ${letter}">
                <button type="button" onclick="this.parentElement.remove(); reindexOptions();" class="w-10 h-10 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all flex items-center justify-center shrink-0 border border-transparent hover:border-red-100">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            `;
            container.appendChild(newDiv);
            opsiCount++;
        }

        function reindexOptions() {
            const divs = document.getElementById('optionsContainer').children;
            opsiCount = divs.length;
            for(let i=0; i<divs.length; i++) {
                divs[i].querySelector('div').innerText = alphabet[i];
            }
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

        // ==========================================
        // LOGIKA AJAX & CHART.JS MULTI-BAR
        // ==========================================
        let currentChart = null; 

        async function openGraphModal(pollId, question, className) {
            const modal = document.getElementById('graph-modal');
            const modalContent = document.getElementById('graph-modal-content');
            const loader = document.getElementById('chart-loader');
            
            document.getElementById('modal-question').textContent = question;
            document.getElementById('modal-class-name').innerHTML = `<i class="fas fa-users"></i> ` + className;

            modal.classList.remove('hidden');
            loader.classList.remove('hidden'); // Munculkan loading
            
            setTimeout(() => {
                modal.classList.replace('opacity-0', 'opacity-100');
                modalContent.classList.replace('scale-95', 'scale-100');
            }, 10);

            try {
                // Ambil data AJAX
                const response = await fetch(`/lms/kepsek/polling/${pollId}/breakdown`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.json();
                
                if(result.success) {
                    renderChart(result.labels, result.datasets);
                } else {
                    alert("Gagal mengambil data: " + result.message);
                }
            } catch (error) {
                console.error(error);
                alert("Terjadi kesalahan jaringan.");
            } finally {
                loader.classList.add('hidden'); // Sembunyikan loading
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

            // Meracik dataset untuk Chart.js berdasarkan Role (Siswa, Ortu, Guru)
            const datasetsArray = [
                {
                    label: 'Siswa',
                    data: datasetsObj['Siswa'],
                    backgroundColor: 'rgba(245, 158, 11, 0.8)', // Kuning/Amber
                    borderColor: 'rgb(245, 158, 11)',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Orang Tua',
                    data: datasetsObj['Orang Tua'],
                    backgroundColor: 'rgba(16, 185, 129, 0.8)', // Hijau/Emerald
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Guru/Sekolah',
                    data: datasetsObj['Guru/Manajemen'],
                    backgroundColor: 'rgba(0, 113, 188, 0.8)', // Biru
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
                            ticks: { 
                                stepSize: 1, 
                                font: { family: "'Inter', sans-serif" }
                            },
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
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
    </style>
@endif