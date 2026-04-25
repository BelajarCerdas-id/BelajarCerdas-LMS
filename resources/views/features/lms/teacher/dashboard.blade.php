@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda Guru'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">

        <div class="p-6 md:p-8">
            
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                        Halo, Bapak/Ibu {{ Auth::user()->name ?? 'Guru' }} 👋
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">Selamat datang di ruang kendali kelas Anda hari ini.</p>
                </div>

                <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-indigo-100 flex items-center gap-4 shrink-0 self-end md:self-auto ml-auto">
                    <div class="w-12 h-12 bg-indigo-600 rounded-xl flex flex-col items-center justify-center text-white shadow-md shadow-indigo-100">
                        <span class="text-[10px] font-bold uppercase leading-none">{{ \Carbon\Carbon::now()->translatedFormat('M') }}</span>
                        <span class="text-lg font-black leading-none">{{ \Carbon\Carbon::now()->translatedFormat('d') }}</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">{{ \Carbon\Carbon::now()->translatedFormat('l') }}</p>
                        <p class="text-sm font-extrabold text-gray-800">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-orange-50 flex items-center gap-5 hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-inner border border-orange-100">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 mb-1">Sesi Mengajar Hari Ini</p>
                        <h4 class="text-2xl font-extrabold text-orange-900">{{ $totalJadwalHariIni ?? 0 }} <span class="text-sm font-medium text-gray-400">Sesi</span></h4>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-blue-50 flex items-center gap-5 hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-inner border border-blue-100">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 mb-1">Total Kelas Diampu</p>
                        <h4 class="text-2xl font-extrabold text-blue-900">{{ $totalKelas ?? 0 }} <span class="text-sm font-medium text-gray-400">Kelas</span></h4>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10">
                
                <div class="xl:col-span-2 flex flex-col gap-8">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-indigo-50 p-6 md:p-8 flex flex-col max-h-[600px]">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 gap-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center shadow-inner shadow-indigo-100/50 font-bold text-lg">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-indigo-900 text-lg leading-tight">Jadwal Mengajar</h3>
                                    <p class="text-xs text-indigo-600/70 font-medium">Sesuai Kalender</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-indigo-600 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-100">
                                Hari {{ $hariIni }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($jadwalMengajar ?? [] as $group)
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-5 rounded-2xl border border-indigo-100 bg-white hover:border-indigo-300 hover:shadow-lg transition-all group mb-2">
                                    <div class="flex items-start gap-5">
                                        <div class="flex flex-col gap-3 shrink-0 border-r border-gray-100 pr-5">
                                            @foreach($group->details as $waktu)
                                                <div class="text-center">
                                                    <span class="block text-sm font-bold text-gray-800">{{ $waktu->jam_mulai }}</span>
                                                    <span class="block text-[10px] text-gray-400 font-medium leading-none">{{ $waktu->jam_selesai }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="pt-1">
                                            <h4 class="font-extrabold text-gray-800 text-lg group-hover:text-indigo-700 transition-colors">
                                                {{ $group->mapel }}
                                            </h4>
                                            <p class="text-xs text-gray-500 mt-2 flex flex-wrap items-center gap-3">
                                                <span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-lg font-bold border border-indigo-100">
                                                    <i class="fas fa-users mr-1.5"></i> {{ $group->kelas }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i class="fas fa-door-open text-indigo-300 mr-1.5"></i> {{ $group->ruangan }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="relative w-full md:w-auto">
                                        <a href="{{ route('lms.teacher.class.detail', ['schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1', 'scheduleId' => explode(',', $group->ids)[0]]) }}" class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-bold rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                                            <i class="fas fa-sign-in-alt"></i> Buka Kelas
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 px-6 text-center h-full border-2 border-dashed border-indigo-50 rounded-2xl bg-indigo-50/30">
                                    <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-full bg-white shadow-sm border border-indigo-100 text-indigo-300">
                                        <i class="fas fa-mug-hot text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-indigo-800 mb-1">Waktunya Bersantai</h3>
                                    <p class="text-indigo-600/70 text-sm font-medium">Bapak/Ibu tidak memiliki jadwal mengajar di hari ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-emerald-50 p-6 md:p-8 flex flex-col">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 gap-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shadow-inner shadow-emerald-100/50 font-bold text-lg">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-emerald-900 text-lg leading-tight">Riwayat Polling Terkini</h3>
                                    <p class="text-xs text-emerald-600/70 font-medium">Tinjau hasil jajak pendapat siswa</p>
                                </div>
                            </div>
                            <a href="{{ route('lms.teacherPolling.view', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800 transition-colors">
                                Kelola Polling <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @forelse($recentPolls ?? [] as $poll)
                                <div class="p-5 border border-emerald-100 rounded-2xl bg-white hover:shadow-md transition-all group">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-[10px] font-bold text-[#0071BC] bg-blue-50 px-2.5 py-1 rounded-md flex items-center gap-1.5 border border-blue-100">
                                            <i class="fas fa-users"></i> {{ $poll->class_name ?? 'Semua Kelas' }}
                                        </span>
                                        <span class="text-[11px] font-bold text-gray-400">
                                            {{ \Carbon\Carbon::parse($poll->created_at)->format('d M') }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug mb-4 h-10">{{ $poll->question }}</p>
                                    
                                    <button 
                                        onclick='openGraphModal("{!! addslashes($poll->question) !!}", "{{ $poll->class_name ?? 'Semua Kelas' }}", {!! $poll->chart_labels !!}, {!! $poll->chart_data !!})' 
                                        class="w-full py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 border border-emerald-100 hover:border-emerald-600">
                                        <i class="fas fa-chart-bar"></i> Lihat Grafik
                                    </button>
                                </div>
                            @empty
                                <div class="md:col-span-2 flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-emerald-50 rounded-2xl bg-emerald-50/20">
                                    <i class="fas fa-box-open text-3xl mb-3 text-emerald-200"></i>
                                    <p class="text-sm font-bold text-emerald-800">Belum Ada Polling</p>
                                    <p class="text-xs font-medium text-emerald-500 mt-1">Polling yang Anda buat akan muncul di sini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TEACHER ASSESSMENT CHEATING HISTORY -->
                    <div class="bg-white rounded-3xl shadow-sm border border-red-50 p-6 md:p-8 flex flex-col">
                        
                        <!-- HEADER -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-linear-to-br from-red-50 to-red-100 text-red-600 rounded-xl flex items-center justify-center shadow-inner font-bold text-lg">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-red-900 text-lg leading-tight">Pelanggaran Asesmen</h3>
                                    <p class="text-xs text-red-600/70 font-medium">Aktivitas mencurigakan saat asesmen berlangsung</p>
                                </div>
                            </div>
                        </div>

                        <!-- LIST -->
                        <div id="container-teacher-assessment-cheating-history" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="flex flex-col gap-4">

                            <!-- FILTER CONTAINER -->
                            <div class="my-6 bg-gray-50 shadow-sm border border-gray-300 rounded-2xl p-6">

                                <!-- Header Filter -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                        <h3 class="text-base font-semibold text-gray-800">
                                            Filter Riwayat
                                        </h3>
                                    </div>
                                </div>

                                <!-- Filter Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-4">

                                    <div id="container-dropdown-school-year-cheating-history"></div>

                                    <div id="container-dropdown-rombel-class-cheating-history"></div>

                                    <div id="container-dropdown-subject-teacher-cheating-history"></div>

                                    <div id="container-dropdown-assessment-type-cheating-history"></div>

                                </div>
                            </div>

                            <div class="flex flex-col gap-4 max-h-87.5 overflow-y-auto pr-2 custom-scrollbar">

                                <div id="grid-list-teacher-assessment-cheating-history" class="flex flex-col gap-8">
                                    <!-- show data in ajax -->
                                </div>

                                <div id="empty-message-teacher-assessment-cheating-history" class="py-8 text-center border-2 border-dashed border-red-50 rounded-2xl bg-red-50/20 hidden">
                                    <div class="flex flex-col items-center justify-center ">
                                        <i class="fas fa-check-circle text-3xl mb-3 text-green-400"></i>
                                        <p class="text-sm font-bold text-green-800">Tidak Ada Pelanggaran</p>
                                        <p class="text-xs font-bold text-green-800 mt-1">Semua siswa tertib</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1 flex flex-col gap-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col max-h-[600px] h-full sticky top-24">
                        
                        @php
                            $selectedDate = request('date', \Carbon\Carbon::today()->format('Y-m-d'));
                            $selectedCarbon = \Carbon\Carbon::parse($selectedDate);
                            $prevWeek = $selectedCarbon->copy()->subWeek()->format('Y-m-d');
                            $nextWeek = $selectedCarbon->copy()->addWeek()->format('Y-m-d');
                        @endphp

                        <div class="flex items-center justify-between mb-5 shrink-0">
                            <div class="flex items-center gap-2">
                                <i class="far fa-calendar-alt text-[#0071BC] text-lg"></i>
                                <h3 class="font-bold text-[#0071BC] uppercase tracking-wider text-sm">Event & Agenda</h3>
                            </div>
                            <div class="flex gap-4">
                                <a href="{{ request()->fullUrlWithQuery(['date' => $prevWeek]) }}" class="text-gray-400 hover:text-[#0071BC] transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['date' => $nextWeek]) }}" class="text-gray-400 hover:text-[#0071BC] transition">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="flex justify-between gap-1 sm:gap-2 shrink-0 mb-6">
                            @php
                                $startOfWeek = \Carbon\Carbon::parse($tanggalDipilih ?? $selectedDate)->startOfWeek();
                                $hariLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                            @endphp
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $loopDate = $startOfWeek->copy()->addDays($i);
                                    $isSel = $loopDate->format('Y-m-d') === ($tanggalDipilih ?? $selectedDate);
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['date' => $loopDate->format('Y-m-d')]) }}" 
                                   class="flex flex-col items-center justify-center w-full py-2 border rounded-md transition-colors cursor-pointer 
                                    {{ $isSel ? 'bg-[#0071BC] border-[#0071BC] text-white shadow-md' : 'bg-white border-[#0071BC] text-[#0071BC] hover:bg-blue-50' }}">
                                    <span class="text-sm font-medium">{{ $loopDate->format('d') }}</span>
                                    <span class="text-[9px] font-medium mt-0.5 uppercase">{{ $hariLabels[$i] }}</span>
                                </a>
                            @endfor
                        </div>

                        <div class="w-full h-8 bg-[#0071BC] mb-2 rounded-sm shrink-0 flex items-center justify-center">
                            <span class="text-[10px] font-bold text-white uppercase tracking-widest">Detail Agenda 7 Hari</span>
                        </div>

                        <div class="flex flex-col flex-1 overflow-y-auto custom-scrollbar mt-2 pr-1">
                            @php
                                $endOfWeek = $startOfWeek->copy()->addDays(6);
                                $weeklyEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
                                                ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                                                ->orderBy('date', 'asc')
                                                ->get();
                            @endphp

                            @forelse($weeklyEvents ?? [] as $event)
                                <div class="mb-3 p-3 border-l-4 hover:bg-gray-50 transition rounded-r bg-white shadow-sm flex flex-col gap-1 {{ $event->date == date('Y-m-d') ? 'border-indigo-500 bg-indigo-50/30' : '' }}" style="border-color: {{ $event->color ?? '#0071BC' }}">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-black text-gray-800 text-[10px] uppercase tracking-widest">
                                            {{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d F') }}
                                        </span>
                                        @if($event->date == date('Y-m-d'))
                                            <span class="bg-indigo-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full uppercase animate-pulse">Hari Ini</span>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-700 block font-bold leading-tight">
                                        {{ $event->title }}
                                    </span>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center py-10 opacity-50">
                                    <i class="far fa-calendar-times text-3xl text-gray-300 mb-2"></i>
                                    <p class="text-gray-400 text-sm font-medium">Tidak ada Agenda/Event</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center bg-gray-50">
        <i class="fas fa-lock text-5xl text-red-400 mb-4"></i>
        <p class="font-bold text-xl text-red-500">Akses Ditolak</p>
        <p class="text-gray-500">Halaman ini khusus untuk Guru.</p>
    </div>
@endif

<div id="graph-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 transform scale-95 transition-transform duration-300 overflow-hidden" id="graph-modal-content">
        <div class="bg-[#0071BC] px-6 py-4 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-chart-pie"></i> Hasil Polling
            </h3>
            <button onclick="closeGraphModal()" class="text-blue-100 hover:text-white w-8 h-8 rounded-full bg-blue-800/30 flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 md:p-8">
            <div class="mb-6">
                <span id="modal-class-name" class="text-xs font-bold text-[#0071BC] bg-blue-50 px-2.5 py-1 rounded-md uppercase tracking-wider mb-2 flex items-center gap-1.5 w-fit border border-blue-100">
                    <i class="fas fa-users"></i> Kelas
                </span>
                <p id="modal-question" class="text-lg font-extrabold text-gray-800 leading-snug"></p>
            </div>
            
            <div class="relative h-64 w-full">
                <canvas id="pollChart"></canvas>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button onclick="closeGraphModal()" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition-colors shadow-sm">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="{{ asset('assets/js/features/lms/teacher/dashboard/paginate-teacher-assessment-cheating-history.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let currentChart = null; 

    function openGraphModal(question, className, labelsArr, dataArr) {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        

        document.getElementById('modal-question').textContent = question;
        document.getElementById('modal-class-name').innerHTML = `<i class="fas fa-users"></i> Kelas ` + className;


        modal.classList.remove('hidden');
        void modal.offsetWidth; 
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');


        renderChart(labelsArr, dataArr);
    }

    function closeGraphModal() {
        const modal = document.getElementById('graph-modal');
        const modalContent = document.getElementById('graph-modal-content');
        

        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); 
    }

    function renderChart(labels, data) {
        const ctx = document.getElementById('pollChart').getContext('2d');


        if (currentChart) {
            currentChart.destroy();
        }


        const totalVotes = data.reduce((a, b) => a + b, 0);
        
        currentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: data,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)', 
                        'rgba(0, 113, 188, 0.8)',  
                        'rgba(245, 158, 11, 0.8)', 
                        'rgba(239, 68, 68, 0.8)', 
                        'rgba(139, 92, 246, 0.8)'  
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(0, 113, 188)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6, 
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false 
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Suara Siswa';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: totalVotes === 0 ? 5 : undefined,
                        ticks: {
                            stepSize: 1, 
                            font: { family: "'Inter', sans-serif" }
                        },
                        grid: {
                            color: 'rgba(241, 245, 249, 1)',
                            drawBorder: false,
                        }
                    },
                    x: {
                        ticks: {
                            font: { family: "'Inter', sans-serif", weight: '500' }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }
</script>