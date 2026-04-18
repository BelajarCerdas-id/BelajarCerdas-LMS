@include('components/sidebar-beranda', ['headerSideNav' => 'Ruang Kelas'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">

        <div class="bg-white px-6 py-6 md:px-8 border-b border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-slate-50 to-transparent rounded-full -translate-y-1/2 translate-x-1/4 opacity-80 pointer-events-none"></div>

            <div class="relative z-10">
                <a href="{{ route('lms.teacher.view', ['schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 mb-5 text-sm font-bold transition-colors group">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Kembali ke Dashboard
                </a>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl shadow-sm border border-indigo-100 shrink-0">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="flex flex-col justify-center">
                            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                Kelas {{ $jadwal->class_name ?? 'Siswa' }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                                    <i class="fas fa-book-open"></i> {{ $jadwal->subject_name ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 bg-white text-slate-600 px-3 py-1 rounded-lg text-xs font-bold border border-slate-200 shadow-sm">
                                    <i class="fas fa-door-open text-slate-400"></i> {{ $jadwal->room_name ?? 'Ruang Kelas' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 md:mt-0">
                        <button class="w-full md:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-cog"></i> Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 relative z-20">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8">
                
                <div class="xl:col-span-2 flex flex-col">
                    <div class="flex overflow-x-auto custom-scrollbar pt-2 pl-4 gap-1">
                        <button onclick="switchTab('pengumuman')" id="btn-tab-pengumuman" class="tab-btn px-6 py-3.5 font-bold text-sm rounded-t-2xl border-t border-x border-slate-200 bg-white text-indigo-600 relative z-10 -mb-[1px] flex items-center gap-2 transition-all">
                            <i class="fas fa-bullhorn"></i> Pengumuman
                        </button>
                        <button onclick="switchTab('materi')" id="btn-tab-materi" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-[1px] hover:bg-white hover:text-indigo-500 flex items-center gap-2 transition-all shadow-inner">
                            <i class="fas fa-file-alt"></i> Materi
                        </button>
                        <button onclick="switchTab('tugas')" id="btn-tab-tugas" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-[1px] hover:bg-white hover:text-indigo-500 flex items-center gap-2 transition-all shadow-inner">
                            <i class="fas fa-tasks"></i> Tugas
                        </button>
                        <button onclick="switchTab('ujian')" id="btn-tab-ujian" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-[1px] hover:bg-white hover:text-indigo-500 flex items-center gap-2 transition-all shadow-inner">
                            <i class="fas fa-stopwatch"></i> Ujian
                        </button>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-b-3xl rounded-tr-3xl shadow-sm p-6 md:p-8 h-[600px] flex flex-col relative z-0">
                        
                        <div id="tab-pengumuman" class="tab-pane flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Papan Pengumuman</h3>
                                        <p class="text-xs text-slate-500 font-medium">Riwayat informasi kelas</p>
                                    </div>
                                </div>
                                <button onclick="openPengumumanModal()" class="bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-plus"></i> Tambah Pengumuman
                                </button>
                            </div>

                            <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                @forelse($pengumumanTerkini ?? [] as $info)
                                    @php
                                        $totalSiswa = $statistik->totalSiswa ?? 1;
                                        $dilihat = $info->views_count ?? 0;
                                        $persentase = $totalSiswa > 0 ? round(($dilihat / $totalSiswa) * 100) : 0;
                                    @endphp
                                    <div class="border border-slate-100 rounded-2xl p-5 hover:shadow-md hover:border-blue-200 transition-all bg-slate-50/50 flex flex-col shrink-0">
                                        <div class="flex justify-between items-start mb-3">
                                            @if(($info->type ?? 'info') == 'penting')
                                                <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded-md border border-red-100 uppercase tracking-wider">Penting</span>
                                            @else
                                                <span class="text-[10px] font-bold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200 uppercase tracking-wider">Info Biasa</span>
                                            @endif
                                            <span class="text-[11px] font-bold text-slate-400">{{ isset($info->created_at) ? \Carbon\Carbon::parse($info->created_at)->diffForHumans() : 'Baru saja' }}</span>
                                        </div>
                                        <h4 class="font-bold text-slate-800 text-sm mb-2 truncate">{{ $info->title ?? 'Judul Pengumuman' }}</h4>
                                        <p class="text-xs text-slate-500 mb-4 line-clamp-2">{{ $info->content ?? 'Isi pengumuman tidak tersedia.' }}</p>
                                        
                                        <div class="mt-auto flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-eye text-slate-400 text-sm"></i>
                                                <span class="text-xs font-bold text-slate-600">Dilihat: <span class="text-emerald-600">{{ $dilihat }}/{{ $statistik->totalSiswa ?? 0 }}</span> Siswa</span>
                                            </div>
                                            <div class="w-20 bg-slate-200 rounded-full h-1.5"><div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $persentase }}%"></div></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full">
                                        <i class="fas fa-bullhorn text-4xl mb-4 text-slate-300"></i>
                                        <p class="text-sm font-bold text-slate-600">Belum Ada Pengumuman</p>
                                        <p class="text-xs text-slate-400 mt-1">Klik tombol tambah untuk membuat pengumuman.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div id="tab-materi" class="tab-pane hidden flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Materi Pembelajaran</h3>
                                        <p class="text-xs text-slate-500 font-medium">Bahan ajar yang dibagikan</p>
                                    </div>
                                </div>
                                <button onclick="openMateriModal()" class="bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-upload"></i> Upload Materi
                                </button>
                            </div>

                            <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                @forelse($materiKelas ?? [] as $materi)
                                    <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:border-indigo-200 hover:shadow-md transition-all shrink-0 group">
                                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-indigo-500 border border-slate-100 shadow-sm shrink-0"><i class="fas fa-file-pdf text-lg"></i></div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-slate-700 text-sm truncate group-hover:text-indigo-600 transition-colors">{{ $materi->judul ?? 'Judul Materi' }}</h4>
                                            <p class="text-[11px] text-slate-400 mt-1">{{ isset($materi->created_at) ? \Carbon\Carbon::parse($materi->created_at)->format('d M Y, H:i') : 'Baru saja' }}</p>
                                        </div>
                                        <button class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full">
                                        <i class="fas fa-file-alt text-4xl mb-4 text-slate-300"></i>
                                        <p class="text-sm font-bold text-slate-600">Belum Ada Materi</p>
                                        <p class="text-xs text-slate-400 mt-1">Silakan upload bahan ajar untuk siswa.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div id="tab-tugas" class="tab-pane hidden flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-list-ul"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Tugas & PR</h3>
                                        <p class="text-xs text-slate-500 font-medium">Klik pada tugas untuk menilai</p>
                                    </div>
                                </div>
                                <button onclick="openTugasModal()" class="bg-amber-50 border border-amber-100 text-amber-600 hover:bg-amber-500 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-plus"></i> Beri Tugas
                                </button>
                            </div>

                            <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                @forelse($tugasKelas ?? [] as $tugas)
                                    <div onclick="openNilaiTugasModal({{ $tugas->id }}, '{{ addslashes($tugas->judul_tugas) }}')" class="cursor-pointer flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-amber-300 hover:shadow-md transition-all shrink-0 group">
                                        <div class="w-12 h-12 bg-white group-hover:bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 border border-slate-100 shadow-sm shrink-0 transition-colors"><i class="fas fa-tasks text-lg"></i></div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-slate-700 text-sm truncate group-hover:text-amber-500 transition-colors">{{ $tugas->judul_tugas ?? 'Nama Tugas' }}</h4>
                                            <p class="text-[11px] text-slate-400 mt-1">Deadline: <span class="font-bold text-amber-500">{{ isset($tugas->deadline) ? \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') : '-' }}</span></p>
                                        </div>
                                        <div class="text-right bg-white px-4 py-2.5 rounded-xl border border-slate-100 shadow-sm group-hover:border-amber-200 transition-colors">
                                            <span class="block text-sm font-bold text-slate-800 text-center">{{ $tugas->terkumpul ?? 0 }}<span class="text-xs text-slate-400 font-normal">/{{ $statistik->totalSiswa ?? 0 }}</span></span>
                                            <span class="block text-[9px] text-slate-400 uppercase tracking-wider font-bold mt-0.5">Terkumpul</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full">
                                        <i class="fas fa-check-double text-4xl mb-4 text-slate-300"></i>
                                        <p class="text-sm font-bold text-slate-600">Belum Ada Tugas Aktif</p>
                                        <p class="text-xs text-slate-400 mt-1">Berikan PR atau tugas baru untuk siswa.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div id="tab-ujian" class="tab-pane hidden flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-stopwatch"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Jadwal Ujian</h3>
                                        <p class="text-xs text-slate-500 font-medium">Ujian dan kuis terdekat</p>
                                    </div>
                                </div>
                                <button onclick="openUjianModal()" class="bg-purple-50 border border-purple-100 text-purple-600 hover:bg-purple-600 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-calendar-plus"></i> Jadwalkan Ujian
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 overflow-y-auto pr-2 custom-scrollbar content-start flex-1">
                                @forelse($ujianKelas ?? [] as $ujian)
                                    <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50 hover:border-purple-300 hover:shadow-md transition-all shrink-0">
                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="font-bold text-slate-800 text-sm line-clamp-1 pr-2">{{ $ujian->judul_ujian ?? 'Ujian Tengah Semester' }}</h4>
                                            <span class="text-[10px] font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded-md border border-purple-200 uppercase whitespace-nowrap">{{ $ujian->durasi ?? 90 }} Menit</span>
                                        </div>
                                        <div class="flex flex-col gap-2 text-xs text-slate-500 mt-3 border-t border-slate-200/60 pt-3">
                                            <span class="flex items-center gap-2"><i class="far fa-calendar-alt text-purple-400 w-4"></i> {{ isset($ujian->tanggal) ? \Carbon\Carbon::parse($ujian->tanggal)->format('l, d F Y') : '-' }}</span>
                                            <span class="flex items-center gap-2"><i class="far fa-clock text-purple-400 w-4"></i> Pukul {{ $ujian->waktu_mulai ?? '08:00' }} WIB</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full">
                                        <i class="fas fa-stopwatch text-4xl mb-4 text-slate-300"></i>
                                        <p class="text-sm font-bold text-slate-600">Tidak Ada Jadwal Ujian</p>
                                        <p class="text-xs text-slate-400 mt-1">Buat jadwal kuis atau ujian baru.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                <div class="xl:col-span-1 flex flex-col h-full">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col h-full relative overflow-hidden">
                        
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 mb-6 relative">
                            <h4 class="font-bold text-slate-800 text-center mb-4 flex items-center justify-center gap-2">
                                <i class="fas fa-chart-pie text-slate-400"></i> Statistik Kehadiran
                            </h4>
                            <div class="relative w-40 h-40 mx-auto transition-transform duration-500 hover:scale-105">
                                <canvas id="absensiChart"></canvas>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                <h4 class="text-2xl font-black text-blue-500 leading-none">{{ $statistik->totalSiswa ?? 0 }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">Total Siswa</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                <h4 class="text-2xl font-black text-emerald-500 leading-none">{{ $statistik->totalMateri ?? 0 }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">Materi</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                <h4 class="text-2xl font-black text-amber-500 leading-none">{{ $statistik->totalPr ?? 0 }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">Tugas/PR</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                <h4 class="text-2xl font-black text-purple-500 leading-none">{{ $statistik->totalAssessment ?? 0 }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">Ujian</p>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <button onclick="openAttendanceModal()" class="w-full bg-emerald-50 text-emerald-600 font-bold py-4 rounded-2xl border border-emerald-100 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-2 text-base shadow-sm">
                                <i class="fas fa-user-check text-lg"></i> Input Presensi Kelas
                            </button>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif

<div id="nilaiTugasModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden transform scale-95 transition-all duration-300" id="nilaiTugasContent">
        <div class="bg-amber-500 p-6 flex justify-between items-center text-white">
            <div>
                <h3 class="font-bold text-xl flex items-center gap-2"><i class="fas fa-check-double"></i> Penilaian Tugas</h3>
                <p class="text-amber-100 text-sm mt-1 font-medium" id="nilaiTugasTitle">Memuat judul tugas...</p>
            </div>
            <button onclick="closeModal('nilaiTugasModal')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar" id="submissionListContainer">
            <div class="flex flex-col items-center justify-center py-10 gap-3">
                <i class="fas fa-circle-notch fa-spin text-4xl text-amber-500"></i>
                <p class="text-sm font-medium text-slate-400">Memuat data pengumpulan...</p>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="closeModal('nilaiTugasModal')" class="px-6 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Tutup</button>
            <button onclick="submitNilaiTugas()" class="px-8 py-2.5 bg-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-200 hover:bg-amber-600 transition-all btn-simpan-nilai">Simpan Penilaian</button>
        </div>
    </div>
</div>

<div id="attendanceModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-all duration-300" id="attendanceContent">
        <div class="bg-emerald-600 p-6 flex justify-between items-center text-white">
            <h3 class="font-bold text-xl flex items-center gap-2"><i class="fas fa-user-check"></i> Input Kehadiran Siswa</h3>
            <button onclick="closeModal('attendanceModal')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar" id="studentListContainer"></div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="closeModal('attendanceModal')" class="px-6 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
            <button onclick="submitAttendance()" class="px-8 py-2.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all">Simpan Presensi</button>
        </div>
    </div>
</div>

<div id="materiModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300">
        <div class="bg-indigo-600 p-6 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg"><i class="fas fa-upload mr-2"></i> Upload Materi Baru</h3>
            <button onclick="closeModal('materiModal')" class="hover:rotate-90 transition-transform"><i class="fas fa-times"></i></button>
        </div>
        <form class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Judul Materi</label>
                <input type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Contoh: Bab 1 - Pengenalan Laravel">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File (PDF/PPT/Video)</label>
                <input type="file" class="w-full px-4 py-2 border border-dashed border-slate-300 rounded-xl bg-slate-50 text-sm">
            </div>
            <button type="button" onclick="submitAksi('Materi berhasil diunggah.', 'materiModal')" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Publish Materi</button>
        </form>
    </div>
</div>

<div id="tugasModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300">
        <div class="bg-amber-500 p-6 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg"><i class="fas fa-list-ul mr-2"></i> Berikan Tugas / PR</h3>
            <button onclick="closeModal('tugasModal')" class="hover:rotate-90 transition-transform"><i class="fas fa-times"></i></button>
        </div>
        <form id="formTugas" onsubmit="submitTugas(event)" class="p-6 space-y-4">
            <input type="hidden" name="class_id" value="{{ $jadwal->class_id ?? '' }}">
            <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Tugas</label>
                <input type="text" name="judul_tugas" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500 outline-none" placeholder="Contoh: Latihan Soal Bab 1">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Tenggat</label>
                    <input type="date" name="deadline_date" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Jam Tenggat</label>
                    <input type="time" name="deadline_time" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Skor Maksimal</label>
                <input type="number" name="max_score" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500 outline-none" value="100">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Instruksi Tugas</label>
                <textarea name="instructions" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500 outline-none custom-scrollbar" rows="3" placeholder="Kerjakan halaman 12 lalu unggah foto..."></textarea>
            </div>
            <button type="submit" class="w-full py-3 bg-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-100 hover:bg-amber-600 transition-all btn-submit-tugas">Simpan Tugas</button>
        </form>
    </div>
</div>

<div id="ujianModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300">
        <div class="bg-purple-600 p-6 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg"><i class="fas fa-stopwatch mr-2"></i> Jadwalkan Ujian / Kuis</h3>
            <button onclick="closeModal('ujianModal')" class="hover:rotate-90 transition-transform"><i class="fas fa-times"></i></button>
        </div>
        <form class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Judul Ujian</label>
                <input type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Contoh: Ujian Tengah Semester">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Pelaksanaan</label>
                    <input type="date" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Durasi (Menit)</label>
                    <input type="number" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none" value="90">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Mulai</label>
                    <input type="time" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai</label>
                    <input type="time" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
            </div>
            <button type="button" onclick="submitAksi('Jadwal ujian berhasil dibuat.', 'ujianModal')" class="w-full py-3 bg-purple-600 text-white font-bold rounded-xl shadow-lg shadow-purple-100 hover:bg-purple-700 transition-all">Buat Jadwal Ujian</button>
        </form>
    </div>
</div>

<div id="pengumumanModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all duration-300">
        <div class="bg-blue-600 p-6 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg"><i class="fas fa-bullhorn mr-2"></i> Buat Pengumuman Baru</h3>
            <button onclick="closeModal('pengumumanModal')" class="hover:rotate-90 transition-transform"><i class="fas fa-times"></i></button>
        </div>
        <form id="formPengumuman" onsubmit="submitPengumuman(event)" class="p-6 space-y-4">
            <input type="hidden" name="class_id" value="{{ $jadwal->class_id ?? '' }}">
            <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Judul Pengumuman</label>
                <input type="text" name="title" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Info Tugas Kelompok">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Pengumuman</label>
                <select name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none bg-white text-sm text-slate-700">
                    <option value="info">Info Biasa</option>
                    <option value="penting">Penting / Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Isi Pengumuman</label>
                <textarea name="content" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none custom-scrollbar text-sm" rows="4" placeholder="Tuliskan isi pengumuman di sini..."></textarea>
            </div>
            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all btn-submit-pengumuman">Kirim Pengumuman</button>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let studentAttendanceData = {}; 
    let myAbsensiChart = null;

    let currentTaskId = null;
    let taskGradesData = {}; 

    document.addEventListener('DOMContentLoaded', function() {
        const dataStats = {
            hadir: {{ $statistik->hadir ?? 0 }},
            izin: {{ $statistik->izin ?? 0 }},
            sakit: {{ $statistik->sakit ?? 0 }},
            alpa: {{ $statistik->alpa ?? 0 }}
        };

        initOrUpdateChart(dataStats);
    });

    function initOrUpdateChart(summary) {
        const allLabels = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
        const allData = [summary.hadir, summary.izin, summary.sakit, summary.alpa];
        const allColors = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'];

        const activeLabels = [];
        const activeData = [];
        const activeColors = [];

        let totalSiswa = 0;

        for(let i=0; i<allData.length; i++) {
            if(allData[i] > 0) {
                activeLabels.push(allLabels[i]);
                activeData.push(allData[i]);
                activeColors.push(allColors[i]);
                totalSiswa += allData[i];
            }
        }

        const isDataEmpty = totalSiswa === 0;

        if (myAbsensiChart) {
            myAbsensiChart.data.labels = isDataEmpty ? ['Belum ada data'] : activeLabels;
            myAbsensiChart.data.datasets[0].data = isDataEmpty ? [1] : activeData;
            myAbsensiChart.data.datasets[0].backgroundColor = isDataEmpty ? ['#F1F5F9'] : activeColors;
            myAbsensiChart.options.plugins.legend.display = !isDataEmpty;
            myAbsensiChart.update();
        } else {
            const ctx = document.getElementById('absensiChart').getContext('2d');
            myAbsensiChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: isDataEmpty ? ['Belum ada data'] : activeLabels,
                    datasets: [{
                        data: isDataEmpty ? [1] : activeData,
                        backgroundColor: isDataEmpty ? ['#F1F5F9'] : activeColors,
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '75%', 
                    plugins: {
                        legend: { position: 'bottom', display: !isDataEmpty, labels: { usePointStyle: true, font: { size: 11, weight: 'bold' } } },
                        tooltip: { enabled: !isDataEmpty }
                    }
                }
            });
        }
    }

    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });
        
        document.getElementById('tab-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-indigo-600', 'z-10', 'shadow-none');
            btn.classList.add('bg-slate-100', 'text-slate-500', 'z-0', 'shadow-inner');
        });

        const activeBtn = document.getElementById('btn-tab-' + tabId);
        activeBtn.classList.remove('bg-slate-100', 'text-slate-500', 'z-0', 'shadow-inner');
        activeBtn.classList.add('bg-white', 'text-indigo-600', 'z-10', 'shadow-none');
    }

    function openMateriModal() { openSpecificModal('materiModal'); }
    function openTugasModal() { openSpecificModal('tugasModal'); }
    function openUjianModal() { openSpecificModal('ujianModal'); }
    function openPengumumanModal() { openSpecificModal('pengumumanModal'); }

    function openSpecificModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('div');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.replace('opacity-0', 'opacity-100'); content.classList.replace('scale-95', 'scale-100'); }, 10);
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('div');
        modal.classList.replace('opacity-100', 'opacity-0');
        content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    async function openNilaiTugasModal(taskId, title) {
        currentTaskId = taskId;
        taskGradesData = {};
        document.getElementById('nilaiTugasTitle').innerText = title;
        
        openSpecificModal('nilaiTugasModal');
        
        const container = document.getElementById('submissionListContainer');
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-10 gap-3">
                <i class="fas fa-circle-notch fa-spin text-4xl text-amber-500"></i>
                <p class="text-sm font-medium text-slate-400">Memuat data pengumpulan...</p>
            </div>`;
        
        try {
            const response = await fetch(`/lms/teacher/tugas/${taskId}/submissions`);
            const result = await response.json();
            
            if(response.ok) {
                const students = result.students;
                const task = result.task;
                let html = '<div class="space-y-3">';
                
                if(students.length === 0) {
                    html += '<p class="text-center py-5 text-slate-500">Tidak ada siswa di kelas ini.</p>';
                } else {
                    students.forEach(s => {
                        const score = s.score !== null ? s.score : '';
                        taskGradesData[s.student_id] = score; 
                        
                        const isSubmitted = s.submission_id ? true : false;
                        const badgeText = isSubmitted ? 'Sudah Kirim' : 'Belum Kirim';
                        const badgeColor = isSubmitted ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-500 border-red-200';
                        
                        const inputState = isSubmitted ? '' : 'disabled';
                        const inputStyle = isSubmitted 
                            ? 'bg-white border-slate-200 text-slate-700 focus:border-amber-500 focus:ring-amber-200' 
                            : 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60';

                        html += `
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-2xl gap-4 hover:border-amber-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 border border-slate-200 shrink-0">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-700 block">${s.name}</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider ${badgeColor}">
                                            ${badgeText}
                                        </span>
                                        ${s.status === 'graded' ? '<span class="text-[10px] text-emerald-500 font-bold uppercase tracking-wider">• Sudah Dinilai</span>' : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-400">Skor:</span>
                                <input type="number" 
                                        ${inputState}
                                        min="0" max="${task.max_score}"
                                        value="${score}" 
                                        oninput="updateGradeData(${s.student_id}, this.value)"
                                        class="w-20 px-3 py-2 text-center font-bold rounded-xl focus:outline-none focus:ring-2 transition-all border ${inputStyle}"
                                        placeholder="-">
                                <span class="text-xs font-bold text-slate-400">/ ${task.max_score}</span>
                            </div>
                        </div>`;
                    });
                }
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `<div class="text-center py-10 text-red-500 font-bold">${result.error || 'Gagal memuat data.'}</div>`;
            }
        } catch (error) {
            container.innerHTML = '<div class="text-center py-10 text-red-500 font-bold">Terjadi kesalahan jaringan.</div>';
        }
    }

    function updateGradeData(studentId, score) {
        taskGradesData[studentId] = score;
    }

    async function submitNilaiTugas() {
        const btn = document.querySelector('.btn-simpan-nilai');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`;
        btn.disabled = true;

        let token = document.querySelector('meta[name="csrf-token"]');
        let csrfToken = token ? token.getAttribute('content') : '{{ csrf_token() }}';

        try {
            const response = await fetch("{{ route('lms.teacher.tugas.grades') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    task_id: currentTaskId,
                    grades: taskGradesData 
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 1500, showConfirmButton: false });
                closeModal('nilaiTugasModal');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Gagal menyimpan nilai.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Gagal terhubung ke server.' });
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    function submitAksi(message, modalId) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            closeModal(modalId);
        });
    }

    async function openAttendanceModal() {
        openSpecificModal('attendanceModal');
        try {
            const response = await fetch(`{{ route('lms.guru.get_students', ['classId' => $jadwal->class_id ?? 0]) }}?schedule_id={{ $jadwal->id ?? 0 }}`);
            const students = await response.json();
            
            let html = '<div class="space-y-3">';
            students.forEach(s => {
                const savedStatus = s.status || 'hadir'; 
                studentAttendanceData[s.id] = savedStatus; 
                
                html += `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl gap-4">
                        <span class="font-bold text-slate-700">${s.name}</span>
                        <div class="flex flex-wrap gap-2">
                            ${['hadir', 'izin', 'sakit', 'alpa'].map(st => `
                                <button onclick="setStatus(${s.id}, '${st}', this)" 
                                    class="status-btn-${s.id} px-3 py-1.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all border 
                                    ${st === savedStatus ? getBtnColor(st) : 'bg-slate-50 text-slate-400 border-slate-200'}">
                                    ${st}
                                </button>
                            `).join('')}
                        </div>
                    </div>`;
            });
            html += '</div>';
            document.getElementById('studentListContainer').innerHTML = html || '<p class="text-center py-5 text-slate-600">Tidak ada siswa.</p>';
        } catch (e) {
            document.getElementById('studentListContainer').innerHTML = '<div class="text-center py-10 text-red-500 font-bold">Gagal memuat daftar siswa.</div>';
        }
    }

    function getBtnColor(status) {
        const colors = { 
            hadir: 'bg-emerald-500 text-white border-emerald-500', 
            izin: 'bg-blue-500 text-white border-blue-500', 
            sakit: 'bg-amber-500 text-white border-amber-500', 
            alpa: 'bg-red-500 text-white border-red-500' 
        };
        return colors[status];
    }

    function setStatus(studentId, status, btn) {
        studentAttendanceData[studentId] = status;
        document.querySelectorAll(`.status-btn-${studentId}`).forEach(b => {
            b.classList.remove('bg-emerald-500', 'bg-blue-500', 'bg-amber-500', 'bg-red-500', 'text-white', 'border-emerald-500', 'border-blue-500', 'border-amber-500', 'border-red-500');
            b.classList.add('bg-slate-50', 'text-slate-400', 'border-slate-200');
        });
        const colors = { hadir: 'emerald', izin: 'blue', sakit: 'amber', alpa: 'red' };
        btn.classList.replace('bg-slate-50', `bg-${colors[status]}-500`);
        btn.classList.replace('text-slate-400', 'text-white');
        btn.classList.replace('border-slate-200', `border-${colors[status]}-500`);
    }

    async function submitAttendance() {
        const btnContainer = document.querySelector('#attendanceContent .bg-slate-50.border-t');
        const btn = btnContainer.querySelectorAll('button')[1];
        const originalText = btn.innerHTML;
        
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`;
        btn.disabled = true;

        let token = document.querySelector('meta[name="csrf-token"]');
        let csrfToken = token ? token.getAttribute('content') : '{{ csrf_token() }}';
        let scheduleId = '{{ $jadwal->id ?? 0 }}';

        try {
            const response = await fetch("{{ route('lms.teacher.attendance.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    schedule_id: scheduleId,
                    attendance: studentAttendanceData 
                })
            });

            if (response.ok) {
                const summary = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
                Object.values(studentAttendanceData).forEach(st => summary[st]++);
                initOrUpdateChart(summary);

                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Presensi tersimpan permanen di Database!', timer: 1500, showConfirmButton: false });
                closeModal('attendanceModal');
            } else {
                const result = await response.json();
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Gagal menyimpan data.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Gagal terhubung ke server.' });
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    async function submitPengumuman(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-pengumuman');
        const originalText = btn.innerHTML;

        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...`;
        btn.disabled = true;

        const formData = new FormData(form);
        let token = document.querySelector('meta[name="csrf-token"]');
        let csrfToken = token ? token.getAttribute('content') : '{{ csrf_token() }}';

        try {
            const response = await fetch("{{ route('lms.teacher.pengumuman.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData 
            });

            const result = await response.json();

            if(result.success) {
                closeModal('pengumumanModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload(); 
                });
            } else {
                alert(result.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            alert("Terjadi kesalahan jaringan saat mengirim pengumuman.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    async function submitTugas(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-tugas');
        const originalText = btn.innerHTML;

        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`;
        btn.disabled = true;

        const formData = new FormData(form);
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const datePart = formData.get('deadline_date');
        const timePart = formData.get('deadline_time');
        if (datePart && timePart) {
            formData.append('deadline', `${datePart} ${timePart}:00`);
        }

        try {
            const response = await fetch("{{ route('lms.teacher.tugas.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData 
            });

            const result = await response.json();

            if(result.success) {
                closeModal('tugasModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload(); 
                });
            } else {
                alert(result.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            alert("Terjadi kesalahan jaringan saat menyimpan tugas.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>