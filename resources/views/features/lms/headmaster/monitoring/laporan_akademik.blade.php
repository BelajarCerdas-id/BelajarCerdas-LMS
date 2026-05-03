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
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Laporan Aktivitas Digital</h1>
                <p class="text-xs font-medium text-slate-500 mt-1">Pantau keterlibatan siswa dalam mengakses materi dan menyelesaikan tugas.</p>
            </div>
            
            <form action="{{ route('lms.kepsek.laporan.akademik') }}" method="GET" class="flex items-center gap-3">
                <select name="tahun_ajaran" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0071BC]/20 outline-none">
                    @foreach($tahunAjaranList ?? [] as $tahun)
                        <option value="{{ $tahun }}" {{ ($filterTahun ?? '') == $tahun ? 'selected' : '' }}>TA. {{ $tahun }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="window.print()" class="w-10 h-10 bg-blue-50 text-[#0071BC] rounded-xl flex items-center justify-center hover:bg-[#0071BC] hover:text-white transition-all shadow-sm">
                    <i class="fas fa-print"></i>
                </button>
            </form>
        </div>

        <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-8">

            {{-- 1. KPI CARDS --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Materi</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_materi ?? 0 }}</h3>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0071BC] flex items-center justify-center"><i class="fas fa-book"></i></div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Asesmen</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->total_tugas ?? 0 }}</h3>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center"><i class="fas fa-tasks"></i></div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Avg. Keaktifan</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-3xl font-black text-slate-800">{{ $stats->avg_keaktifan ?? 0 }}%</h3>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-red-50 bg-red-50/20">
                    <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-2">Siswa Pasif</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-3xl font-black text-red-600">{{ $stats->siswa_pasif ?? 0 }}</h3>
                        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center"><i class="fas fa-user-slash"></i></div>
                    </div>
                </div>
            </div>

            {{-- 2. GRAFIK UTAMA: MATERI VS ASESMEN --}}
            <div class="bg-white rounded-[2.5rem] p-6 md:p-10 shadow-sm border border-slate-200/60">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-xl">Komparasi Aktivitas Belajar per Kelas</h3>
                        <p class="text-xs text-slate-500 mt-1">Membandingkan persentase siswa membaca materi vs mengumpulkan tugas.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#0071BC]"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Akses Materi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Penyelesaian Tugas</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative h-[400px] w-full">
                    <canvas id="aktivitasDoubleChart"></canvas>
                </div>
            </div>

            {{-- 3. TABEL REKAPITULASI --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
                    <i class="fas fa-list-check text-[#0071BC]"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Data Detail Aktivitas Kelas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                <th class="px-8 py-4 font-bold">Nama Kelas</th>
                                <th class="px-8 py-4 font-bold text-center">Akses Materi</th>
                                <th class="px-8 py-4 font-bold text-center">Tugas Selesai</th>
                                <th class="px-8 py-4 font-bold text-center">Status Keaktifan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($chartLabelKelas as $index => $label)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-4 font-bold text-slate-700">{{ $label }}</td>
                                <td class="px-8 py-4 text-center font-bold text-[#0071BC]">{{ $chartDataContent[$index] }}%</td>
                                <td class="px-8 py-4 text-center font-bold text-emerald-600">{{ $chartDataAssessment[$index] }}%</td>
                                <td class="px-8 py-4 text-center">
                                    @php $avg = ($chartDataContent[$index] + $chartDataAssessment[$index]) / 2; @endphp
                                    @if($avg >= 80)
                                        <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border border-emerald-100">Sangat Aktif</span>
                                    @elseif($avg >= 50)
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border border-blue-100">Cukup Aktif</span>
                                    @else
                                        <span class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border border-red-100">Perlu Evaluasi</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('aktivitasDoubleChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabelKelas) !!},
                    datasets: [
                        {
                            label: 'Akses Materi (%)',
                            data: {!! json_encode($chartDataContent) !!},
                            backgroundColor: '#0071BC',
                            borderRadius: 6,
                            barPercentage: 0.7,
                            categoryPercentage: 0.6
                        },
                        {
                            label: 'Tugas Selesai (%)',
                            data: {!! json_encode($chartDataAssessment) !!},
                            backgroundColor: '#10B981',
                            borderRadius: 6,
                            barPercentage: 0.7,
                            categoryPercentage: 0.6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 13 },
                            bodyFont: { size: 14, weight: 'bold' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { 
                                callback: value => value + '%',
                                color: '#94a3b8',
                                font: { weight: 'bold' }
                            },
                            grid: { color: '#f1f5f9', drawBorder: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569', font: { weight: 'bold' } }
                        }
                    }
                }
            });
        });
    </script>
@endif