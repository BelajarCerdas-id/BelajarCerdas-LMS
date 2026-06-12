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
                    @php
                        $user = Auth::user();
                        $profile = $user->SchoolStaffProfile;
                        
                        // 1. Cek Sapaan berdasarkan jenis kelamin (sesuaikan dengan database)
                        $jk = $profile?->jenis_kelamin; 
                        $sapaan = $jk == 'L' ? 'Bapak ' : ($jk == 'P' ? 'Ibu ' : '');
                        
                        // 2. Ambil Nama Lengkap
                        $namaLengkap = $profile?->nama_lengkap ?? 'Kepala';

                    @endphp

                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                        Halo, {{ $sapaan }}{{ $namaLengkap }}!
                    </h1>
                    <p class="text-slate-500 mt-2 font-medium">Ringkasan operasional sekolah untuk <span class="text-slate-800 font-bold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span></p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                        <i class="fas fa-download"></i> Unduh Laporan
                    </button>
                    {{-- TOMBOL TRIGGER MODAL PENGUMUMAN --}}
                    <button onclick="openModalPengumuman()" class="px-5 py-2.5 bg-[#0071BC] text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Buat Pengumuman
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-6">
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
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                <div class="xl:col-span-8 space-y-8">
                    <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="h-10 w-1 bg-[#0071BC] rounded-full"></div>
                            <h3 class="text-xl font-black text-slate-800">Modul Monitoring Strategis</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            <a href="{{ route('lms.headmaster.academic.report', [
                                'role' => Auth::user()->role,
                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                            ]) }}" class="group block">
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

                            <a href="{{ route('lms.headmaster.teacher.activity', [
                                'role' => Auth::user()->role,
                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                            ]) }}" class="group block">
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
                                <i class="fas fa-bullhorn text-amber-500"></i> Pengumuman ke Guru
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

    {{-- MODAL PENGUMUMAN --}}
    <div id="pengumumanModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300">
            <div class="bg-[#0071BC] p-6 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg"><i class="fas fa-bullhorn mr-2"></i> Buat Pengumuman ke Guru</h3>
                <button onclick="closeModalPengumuman()" class="hover:rotate-90 transition-transform"><i class="fas fa-times"></i></button>
            </div>
            <form id="formPengumumanKepsek" onsubmit="submitPengumumanKepsek(event)" class="p-6 space-y-4">
                <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">
                <input type="hidden" name="target" value="Guru">

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Penerima Pengumuman</label>
                    <div class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-sm font-bold flex items-center gap-3 cursor-not-allowed shadow-inner">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#0071BC] flex items-center justify-center">
                            <i class="fas fa-user-tie text-[10px]"></i>
                        </div>
                        Khusus Seluruh Jajaran Guru
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Judul Pengumuman</label>
                    <input type="text" name="title" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#0071BC] outline-none" placeholder="Contoh: Rapat Evaluasi Mingguan">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Pengumuman</label>
                    <select name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#0071BC] outline-none bg-white text-sm text-slate-700">
                        <option value="info">Info Biasa</option>
                        <option value="penting">Penting / Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Isi Pengumuman</label>
                    <textarea name="content" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#0071BC] outline-none custom-scrollbar text-sm" rows="4" placeholder="Tuliskan isi pengumuman di sini..."></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-[#0071BC] text-white font-bold rounded-xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all btn-submit-pengumuman">Kirim Pengumuman</button>
            </form>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openModalPengumuman() {
        const modal = document.getElementById('pengumumanModal');
        const content = modal.querySelector('div');
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.replace('opacity-0', 'opacity-100'); 
            content.classList.replace('scale-95', 'scale-100'); 
        }, 10);
    }

    function closeModalPengumuman() {
        const modal = document.getElementById('pengumumanModal');
        const content = modal.querySelector('div');
        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => { 
            modal.classList.add('hidden'); 
            document.getElementById('formPengumumanKepsek').reset();
        }, 300);
    }

    async function submitPengumumanKepsek(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-pengumuman');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...`; 
        btn.disabled = true;

        let csrfToken = document.querySelector('meta[name="csrf-token"]');
        let token = csrfToken ? csrfToken.getAttribute('content') : '';

        try {
            // Pastikan kamu punya route ini di web.php untuk HeadmasterController
            const response = await fetch("{{ route('lms.kepsek.pengumuman.store', ['role' => Auth::user()->role, 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '0']) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: new FormData(form) 
            });
            const result = await response.json();
            
            if(result.success || response.ok) {
                closeModalPengumuman();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message || 'Pengumuman ke Guru berhasil dikirim.', timer: 1500, showConfirmButton: false }).then(() => { window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Terjadi kesalahan' });
                btn.innerHTML = originalText; btn.disabled = false;
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: "Terjadi kesalahan jaringan." });
            btn.innerHTML = originalText; btn.disabled = false;
        }
    }
</script>