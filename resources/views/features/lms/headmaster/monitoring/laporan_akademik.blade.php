@include('components/sidebar-beranda', ['headerSideNav' => 'Laporan Akademik'])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">

        {{-- HEADER --}}
        <div class="bg-white border-b border-slate-200 px-6 py-6 md:px-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sticky top-0 z-30">
            <div>
                <a href="{{ route('lms.kepsek.dashboard', [
                    'role'       => Auth::user()->role,
                    'schoolName' => \Illuminate\Support\Str::slug(Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah ?? 'sekolah'),
                    'schoolId'   => Auth::user()->SchoolStaffProfile->school_partner_id ?? 0
                ]) }}" class="text-sm font-bold text-[#0071BC] hover:text-blue-800 flex items-center gap-2 mb-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Laporan Akademik & Aktivitas</h1>
                <p class="text-xs font-medium text-slate-500 mt-1">Pantau keterlibatan siswa dalam mengakses materi dan menyelesaikan tugas secara real-time.</p>
            </div>
            
            <form action="{{ route('lms.kepsek.laporan.akademik') }}" method="GET" class="flex items-center gap-3">
                <div class="relative">
                    <select name="tahun_ajaran" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0071BC]/20 outline-none cursor-pointer appearance-none pr-10">
                        @foreach($tahunAjaranList ?? [] as $tahun)
                            <option value="{{ $tahun }}" {{ ($filterTahun ?? '') == $tahun ? 'selected' : '' }}>TA. {{ $tahun }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button type="button" onclick="window.print()" class="w-10 h-10 bg-blue-50 text-[#0071BC] rounded-xl flex items-center justify-center hover:bg-[#0071BC] hover:text-white transition-all shadow-sm tooltip" title="Cetak Laporan">
                    <i class="fas fa-print"></i>
                </button>
            </form>
        </div>

        <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-8">

            @php
                $avgContentGlobal = count($chartDataContent ?? []) > 0 ? round(array_sum($chartDataContent) / count($chartDataContent)) : 0;
                $avgAssessmentGlobal = count($chartDataAssessment ?? []) > 0 ? round(array_sum($chartDataAssessment) / count($chartDataAssessment)) : 0;
            @endphp

            {{-- 1. KPI QUICK STATS --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Literasi Materi</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_materi ?? 0 }}</h3>
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">Modul</span>
                    </div>
                </div>
                <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Asesmen</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_tugas ?? 0 }}</h3>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">Tugas/Ujian</span>
                    </div>
                </div>
                <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Avg. Keaktifan</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-purple-600">{{ $stats->avg_keaktifan ?? 0 }}%</h3>
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
                
                {{-- KARTU SISWA PASIF DENGAN POP-UP --}}
                <div onclick="openPasifModal()" class="bg-white rounded-3xl p-5 border {{ ($stats->siswa_pasif ?? 0) > 0 ? 'border-red-200 bg-red-50/20 cursor-pointer hover:bg-red-50' : 'border-slate-200' }} shadow-sm transition-all group">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 group-hover:text-red-400">Siswa Pasif</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black {{ ($stats->siswa_pasif ?? 0) > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $stats->siswa_pasif ?? 0 }}</h3>
                        <div class="w-8 h-8 rounded-lg {{ ($stats->siswa_pasif ?? 0) > 0 ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                            <i class="fas fa-eye text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. RINGKASAN KEGIATAN --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- KARTU ASSESSMENT --}}
                <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-200/60">
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-emerald-100">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-slate-800 leading-none">Assessment & Tugas</h4>
                                <p class="text-xs font-medium text-slate-400 mt-1">Status pengumpulan seluruh siswa</p>
                            </div>
                        </div>
                        <span class="text-2xl font-black text-emerald-600">{{ $avgAssessmentGlobal }}%</span>
                    </div>
                    <div class="space-y-3">
                        <div class="w-full bg-slate-100 rounded-full h-4 relative overflow-hidden shadow-inner">
                            <div class="bg-emerald-500 h-4 rounded-full transition-all duration-1000 relative" style="width: {{ $avgAssessmentGlobal }}%">
                                <div class="absolute inset-0 bg-white/20 w-full animate-[shimmer_2s_infinite]" style="background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent); background-size: 1rem 1rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KARTU MATERI --}}
                <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-200/60">
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-blue-100">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-slate-800 leading-none">Materi Literasi</h4>
                                <p class="text-xs font-medium text-slate-400 mt-1">Interaksi siswa terhadap konten</p>
                            </div>
                        </div>
                        <span class="text-2xl font-black text-[#0071BC]">{{ $avgContentGlobal }}%</span>
                    </div>
                    <div class="space-y-3">
                        <div class="w-full bg-slate-100 rounded-full h-4 relative overflow-hidden shadow-inner">
                            <div class="bg-[#0071BC] h-4 rounded-full transition-all duration-1000 relative" style="width: {{ $avgContentGlobal }}%">
                                <div class="absolute inset-0 bg-white/20 w-full animate-[shimmer_2s_infinite]" style="background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent); background-size: 1rem 1rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. GRAFIK AKTIVITAS --}}
            <div class="bg-white rounded-[2.5rem] p-6 md:p-10 shadow-sm border border-slate-200/60">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-xl">Perbandingan Aktivitas per Rombel</h3>
                        <p class="text-xs text-slate-500 mt-1">Data statistik interaksi belajar mengajar di tiap kelas.</p>
                    </div>
                    <div class="flex gap-4 p-2 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-2 px-3">
                            <span class="w-3 h-3 rounded-full bg-[#0071BC]"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Materi</span>
                        </div>
                        <div class="flex items-center gap-2 px-3">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Tugas</span>
                        </div>
                    </div>
                </div>
                <div class="relative h-[450px] w-full">
                    <canvas id="aktivitasDoubleChart"></canvas>
                </div>
            </div>

            {{-- 4. TABEL DETAIL --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800">Rincian Performa Kelas</h3>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-slate-400 bg-white border-b border-slate-200">
                                <th class="px-8 py-5 font-black">Nama Rombel</th>
                                <th class="px-8 py-5 font-black text-center">Progress Materi</th>
                                <th class="px-8 py-5 font-black text-center">Progress Tugas</th>
                                <th class="px-8 py-5 font-black text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-50">
                            @forelse($chartLabelKelas ?? [] as $index => $label)
                                @php 
                                    $valContent = $chartDataContent[$index] ?? 0;
                                    $valTask = $chartDataAssessment[$index] ?? 0;
                                    $avg = ($valContent + $valTask) / 2; 
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all"><i class="fas fa-users-rectangle"></i></div>
                                            <span class="font-bold text-slate-700">{{ $label }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="font-black text-[#0071BC]">{{ $valContent }}%</span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="font-black text-emerald-600">{{ $valTask }}%</span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        @if($avg >= 85)
                                            <span class="bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border border-emerald-200">Sangat Aktif</span>
                                        @elseif($avg >= 60)
                                            <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border border-blue-200">Aktif</span>
                                        @else
                                            <span class="bg-red-50 text-red-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border border-red-200">Kritis</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-8 py-20 text-center text-slate-400 font-bold">Data belum tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL SISWA PASIF --}}
    <div id="pasifModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closePasifModal()"></div>
        <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300" id="pasifModalContent">
            <div class="bg-red-600 p-6 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg flex items-center gap-3">
                    <i class="fas fa-user-slash"></i> Daftar Siswa Pasif
                </h3>
                <button onclick="closePasifModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-400 border-b border-slate-100">
                            <th class="pb-3">Nama Siswa</th>
                            <th class="pb-3 text-right">Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($listSiswaPasif ?? [] as $pasif)
                            <tr class="border-b border-slate-50 last:border-0">
                                <td class="py-3 font-bold text-slate-700">{{ $pasif['nama'] }}</td>
                                <td class="py-3 text-right font-medium text-slate-500">{{ $pasif['kelas'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-10 text-center">
                                    <i class="fas fa-check-circle text-emerald-400 text-3xl mb-2"></i>
                                    <p class="text-slate-500 font-medium">Semua siswa aktif melakukan kegiatan akademik.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closePasifModal()" class="px-6 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-100 transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        @keyframes shimmer { 0% { background-position: -2rem 0; } 100% { background-position: 2rem 0; } }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function openPasifModal() {
            const modal = document.getElementById('pasifModal');
            const content = document.getElementById('pasifModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function closePasifModal() {
            const modal = document.getElementById('pasifModal');
            const content = document.getElementById('pasifModalContent');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 200);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('aktivitasDoubleChart').getContext('2d');
            const gradBlue = ctx.createLinearGradient(0, 0, 0, 400);
            gradBlue.addColorStop(0, '#0071BC'); gradBlue.addColorStop(1, '#3b82f6');
            const gradGreen = ctx.createLinearGradient(0, 0, 0, 400);
            gradGreen.addColorStop(0, '#10B981'); gradGreen.addColorStop(1, '#34d399');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabelKelas ?? []) !!},
                    datasets: [
                        { label: 'Akses Materi (%)', data: {!! json_encode($chartDataContent ?? []) !!}, backgroundColor: gradBlue, borderRadius: 8 },
                        { label: 'Tugas Selesai (%)', data: {!! json_encode($chartDataAssessment ?? []) !!}, backgroundColor: gradGreen, borderRadius: 8 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });
        });
    </script>
@endif