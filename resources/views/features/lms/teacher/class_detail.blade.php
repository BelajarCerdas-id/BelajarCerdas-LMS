@include('components/sidebar-beranda', ['headerSideNav' => 'Ruang Kelas'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">

        <div class="bg-white px-6 py-6 md:px-8 border-b border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-slate-50 to-transparent rounded-full -translate-y-1/2 translate-x-1/4 opacity-80 pointer-events-none"></div>

            <div class="relative z-10">
                <a href="{{ route('lms.teacher.view', ['role' => $role ?? 'guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 mb-5 text-sm font-bold transition-colors group">
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
                            <i class="fas fa-file-alt"></i> Content
                        </button>
                        <button onclick="switchTab('tugas')" id="btn-tab-tugas" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-[1px] hover:bg-white hover:text-indigo-500 flex items-center gap-2 transition-all shadow-inner">
                            <i class="fas fa-tasks"></i> Assessments
                        </button>
                        <button onclick="switchTab('ujian')" id="btn-tab-ujian" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-[1px] hover:bg-white hover:text-indigo-500 flex items-center gap-2 transition-all shadow-inner">
                            <i class="fas fa-stopwatch"></i> Questions
                        </button>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-b-3xl rounded-tr-3xl shadow-sm p-6 md:p-8 h-[600px] flex flex-col relative z-0">
                        
                        {{-- TAB PENGUMUMAN --}}
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
                                <button onclick="openSpecificModal('pengumumanModal')" class="bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
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
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- TAB MATERI --}}
                        <div id="tab-materi" class="tab-pane hidden flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Materi Pembelajaran</h3>
                                        <p class="text-xs text-slate-500 font-medium">Klik untuk membuka file</p>
                                    </div>
                                </div>
                                <a href="{{ route('lms.teacherContentForRelease.view', ['role' => 'guru', 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-upload"></i> Upload Materi
                                </a>
                            </div>

                            <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                @forelse($materiKelas ?? [] as $materi)
                                    <div onclick="openMateriModal('{{ addslashes(str_replace(["\r", "\n"], '', $materi->judul ?? 'Judul Materi')) }}', '{{ isset($materi->tanggal_rilis) ? \Carbon\Carbon::parse($materi->tanggal_rilis)->format('d M Y, H:i') : '-' }}', '{{ $materi->file_url ?? '' }}')" class="cursor-pointer flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-indigo-300 hover:shadow-md transition-all shrink-0 group">
                                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-indigo-500 border border-slate-100 shadow-sm shrink-0 group-hover:bg-indigo-50 transition-colors"><i class="fas fa-file-pdf text-lg"></i></div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-slate-700 text-sm truncate group-hover:text-indigo-600 transition-colors">{{ $materi->judul ?? 'Judul Materi' }}</h4>
                                            <p class="text-[11px] text-slate-400 mt-1">Dirilis: {{ isset($materi->tanggal_rilis) ? \Carbon\Carbon::parse($materi->tanggal_rilis)->format('d M Y, H:i') : 'Belum dirilis' }}</p>
                                        </div>
                                        <button class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full">
                                        <i class="fas fa-file-alt text-4xl mb-4 text-slate-300"></i>
                                        <p class="text-sm font-bold text-slate-600">Belum Ada Materi</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- TAB TUGAS --}}
                        <div id="tab-tugas" class="tab-pane hidden flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-list-ul"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Tugas & PR</h3>
                                        <p class="text-xs text-slate-500 font-medium">Buka untuk menilai & melihat soal</p>
                                    </div>
                                </div>
                                <a href="{{ route('lms.teacherAssessmentManagement.view', ['role' => 'guru', 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="bg-amber-50 border border-amber-100 text-amber-600 hover:bg-amber-500 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-plus"></i> Beri Tugas
                                </a>
                            </div>

                            <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                @forelse($tugasKelas ?? [] as $tugas)
                                    <div onclick="openNilaiTugasModal({{ $tugas->id }}, '{{ addslashes(str_replace(["\r", "\n"], '', $tugas->judul ?? 'Tugas')) }}', '{{ isset($tugas->deadline) ? \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') : '-' }}', '{{ $tugas->file_url ?? '' }}')" class="cursor-pointer flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-amber-300 hover:shadow-md transition-all shrink-0 group">
                                        <div class="w-12 h-12 bg-white group-hover:bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 border border-slate-100 shadow-sm shrink-0 transition-colors"><i class="fas fa-tasks text-lg"></i></div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-slate-700 text-sm truncate group-hover:text-amber-500 transition-colors">{{ $tugas->judul ?? 'Nama Tugas' }}</h4>
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
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- TAB UJIAN --}}
                        <div id="tab-ujian" class="tab-pane hidden flex flex-col h-full flex-1">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-100 shrink-0 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-stopwatch"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">Jadwal Ujian</h3>
                                        <p class="text-xs text-slate-500 font-medium">Buka untuk pratinjau soal ujian</p>
                                    </div>
                                </div>
                                <a href="{{ route('lms.teacherQuestionBankManagement.view', ['role' => 'guru', 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}" class="bg-purple-50 border border-purple-100 text-purple-600 hover:bg-purple-600 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <i class="fas fa-calendar-plus"></i> Jadwalkan Ujian
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 overflow-y-auto pr-2 custom-scrollbar content-start flex-1">
                                @forelse($ujianKelas ?? [] as $ujian)
                                    <div onclick="openUjianModal('{{ addslashes(str_replace(["\r", "\n"], '', $ujian->judul ?? 'Ujian')) }}', '{{ $ujian->tipe ?? 'Ujian' }}', '{{ isset($ujian->tanggal_ujian) ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->translatedFormat('l, d F Y') : '-' }}', '{{ isset($ujian->tanggal_ujian) ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('H:i') : '08:00' }}', {{ $ujian->id ?? 0 }})" class="cursor-pointer p-5 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-purple-300 hover:shadow-md transition-all shrink-0">
                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="font-bold text-slate-800 text-sm line-clamp-1 pr-2">{{ $ujian->judul ?? 'Ujian Tengah Semester' }}</h4>
                                            <span class="text-[10px] font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded-md border border-purple-200 uppercase whitespace-nowrap">{{ $ujian->tipe ?? 'Ujian' }}</span>
                                        </div>
                                        <div class="flex flex-col gap-2 text-xs text-slate-500 mt-3 border-t border-slate-200/60 pt-3">
                                            <span class="flex items-center gap-2"><i class="far fa-calendar-alt text-purple-400 w-4"></i> {{ isset($ujian->tanggal_ujian) ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->translatedFormat('l, d F Y') : '-' }}</span>
                                            <span class="flex items-center gap-2"><i class="far fa-clock text-purple-400 w-4"></i> Pukul {{ isset($ujian->tanggal_ujian) ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('H:i') : '08:00' }} WIB</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full">
                                        <i class="fas fa-stopwatch text-4xl mb-4 text-slate-300"></i>
                                        <p class="text-sm font-bold text-slate-600">Tidak Ada Jadwal Ujian</p>
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

{{-- MODAL PENILAIAN TUGAS (DENGAN PREVIEW PDF SPLIT SCREEN) --}}
<div id="nilaiTugasModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-[95vw] max-w-[1600px] h-[95vh] overflow-hidden transform scale-95 transition-all duration-300 flex flex-col" id="nilaiTugasContent">
        
        <div class="bg-amber-500 p-4 md:p-5 flex justify-between items-start text-white shrink-0">
            <div>
                <h3 class="font-bold text-xl flex items-center gap-2"><i class="fas fa-check-double"></i> Detail & Penilaian Tugas</h3>
                <p class="text-amber-100 text-sm mt-1 font-medium" id="nilaiTugasTitle">Memuat judul tugas...</p>
                <p class="text-amber-100 text-xs mt-0.5 flex items-center gap-1.5"><i class="far fa-clock"></i> Deadline: <span id="nilaiTugasDeadline" class="font-bold"></span></p>
            </div>
            <button onclick="closeModal('nilaiTugasModal')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
        </div>

        <div class="flex flex-1 overflow-hidden bg-slate-50">
            <div id="tugasPreviewSection" class="w-7/12 border-r border-slate-200 hidden flex-col bg-slate-100 relative">
                <div class="p-3 bg-white border-b border-slate-200 text-xs font-bold text-slate-600 flex justify-between items-center shadow-sm z-10">
                    <span class="flex items-center gap-2"><i class="fas fa-file-pdf text-amber-500 text-lg"></i> Lampiran Soal / Instruksi</span>
                    <a id="tugasFileLinkFull" href="#" target="_blank" class="text-amber-600 hover:text-amber-700 hover:underline bg-amber-50 px-3 py-1 rounded-md border border-amber-100 transition-colors">Buka Fullscreen</a>
                </div>
                <div id="tugasIframeContainer" class="flex-1 w-full h-full relative">
                </div>
            </div>

            <div id="tugasGradingSection" class="w-full flex flex-col bg-white transition-all duration-300">
                <div class="bg-amber-50/50 px-6 py-3 border-b border-amber-100 flex justify-between items-center text-xs font-bold text-amber-700 shadow-inner">
                    <span>Daftar Pengumpulan Siswa</span>
                    <span>Berikan Nilai 0 - 100</span>
                </div>
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar" id="submissionListContainer">
                    <div class="flex flex-col items-center justify-center py-10 gap-3">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-amber-500"></i>
                        <p class="text-sm font-medium text-slate-400">Memuat data pengumpulan...</p>
                    </div>
                </div>
                <div class="p-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                    <button onclick="closeModal('nilaiTugasModal')" class="px-6 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Tutup</button>
                    <button onclick="submitNilaiTugas()" class="px-8 py-2.5 bg-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-200 hover:bg-amber-600 transition-all btn-simpan-nilai">Simpan Penilaian</button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL DETAIL & PREVIEW MATERI --}}
<div id="materiDetailModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-[95vw] max-w-[1400px] h-[95vh] overflow-hidden transform scale-95 transition-all duration-300 flex flex-col">
        
        <div class="bg-indigo-600 p-4 md:p-5 flex justify-between items-center text-white shrink-0">
            <div>
                <h3 class="font-bold text-lg flex items-center gap-2"><i class="fas fa-file-alt"></i> Preview Dokumen Materi</h3>
                <p class="text-indigo-200 text-xs mt-1" id="detailMateriTanggal"></p>
            </div>
            <button onclick="closeModal('materiDetailModal')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="p-6 bg-slate-50 flex-1 overflow-y-auto custom-scrollbar flex flex-col">
            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 flex items-center gap-4 mb-4 shrink-0 shadow-sm">
                <div class="w-12 h-12 bg-white text-indigo-500 rounded-xl flex items-center justify-center text-2xl shadow-sm shrink-0">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-1">Judul Materi Pembelajaran</p>
                    <h4 class="text-base md:text-lg font-bold text-slate-800 leading-tight" id="detailMateriJudul">Judul</h4>
                </div>
            </div>

            <div id="materiPreviewContainer" class="w-full flex-1 bg-slate-100 rounded-xl border border-slate-200 shadow-inner overflow-hidden relative flex items-center justify-center">
            </div>
        </div>
        
        <div class="p-4 md:p-5 bg-white border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button onclick="closeModal('materiDetailModal')" class="px-8 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Tutup Jendela</button>
        </div>

    </div>
</div>

{{-- MODAL DETAIL & PREVIEW UJIAN (NATIVE BARU) --}}
<div id="ujianDetailModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-[95vw] max-w-[1200px] h-[90vh] overflow-hidden transform scale-95 transition-all duration-300 flex flex-col">
        <div class="bg-purple-600 p-4 md:p-5 flex justify-between items-center text-white shrink-0">
            <div>
                <h3 class="font-bold text-lg flex items-center gap-2"><i class="fas fa-stopwatch"></i> Preview Soal Ujian</h3>
                <p class="text-purple-200 text-xs mt-1 font-medium">
                    <span id="detailUjianJudul" class="font-bold border-r border-purple-400 pr-2 mr-1"></span>
                    <span id="detailUjianTipe" class="font-bold uppercase border-r border-purple-400 pr-2 mr-1"></span>
                    <span id="detailUjianTanggal"></span> | <span id="detailUjianWaktu"></span>
                </p>
            </div>
            <button onclick="closeModal('ujianDetailModal')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="flex flex-1 overflow-hidden bg-slate-50" id="ujianNativeContainer">
        </div>

        <div class="p-4 bg-white border-t border-slate-100 flex justify-between items-center shrink-0">
            <div id="ujianPaginationInfo" class="text-sm text-slate-500 font-medium"></div>
            <button onclick="closeModal('ujianDetailModal')" class="px-8 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Tutup Pratinjau</button>
        </div>
    </div>
</div>

{{-- MODAL ATTENDANCE (Presensi - TABEL) --}}
<div id="attendanceModal" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[90vh]" id="attendanceContent">
        <div class="bg-emerald-600 p-5 md:p-6 flex justify-between items-center text-white shrink-0">
            <div>
                <h3 class="font-bold text-xl flex items-center gap-2"><i class="fas fa-user-check"></i> Input Kehadiran Siswa</h3>
                <p class="text-emerald-100 text-sm mt-1">Kelas {{ $jadwal->class_name ?? '' }} - Tanggal {{ date('d M Y') }}</p>
            </div>
            <button onclick="closeModal('attendanceModal')" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="overflow-y-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-emerald-50 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="py-4 px-6 font-bold text-emerald-800 border-b border-emerald-100 w-16 text-center">No</th>
                        <th class="py-4 px-6 font-bold text-emerald-800 border-b border-emerald-100">Nama Siswa</th>
                        <th class="py-4 px-6 font-bold text-emerald-800 border-b border-emerald-100 text-center w-1/2">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody id="studentListContainer" class="divide-y divide-slate-100">
                </tbody>
            </table>
        </div>

        <div class="p-5 md:p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button onclick="closeModal('attendanceModal')" class="px-6 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
            <button onclick="submitAttendance()" class="px-8 py-2.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all">Simpan Presensi</button>
        </div>
    </div>
</div>

{{-- MODAL PENGUMUMAN --}}
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
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
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

    // Variabel Global untuk Preview Native Ujian
    let currentQuestions = [];
    let currentActiveQuestionIndex = 0;

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
        if(typeof Chart === 'undefined') return;

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
            const ctx = document.getElementById('absensiChart');
            if(!ctx) return;
            
            myAbsensiChart = new Chart(ctx.getContext('2d'), {
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
        if(activeBtn) {
            activeBtn.classList.remove('bg-slate-100', 'text-slate-500', 'z-0', 'shadow-inner');
            activeBtn.classList.add('bg-white', 'text-indigo-600', 'z-10', 'shadow-none');
        }
    }

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
        setTimeout(() => {
            modal.classList.add('hidden');
            const iframes = modal.querySelectorAll('iframe');
            iframes.forEach(ifr => ifr.src = '');
            const videos = modal.querySelectorAll('video');
            videos.forEach(vid => vid.pause());
        }, 300);
    }

    // ================= FUNGSI UNTUK DETAIL MATERI =================
    function openMateriModal(judul, tanggal, fileUrl) {
        document.getElementById('detailMateriJudul').innerText = judul;
        document.getElementById('detailMateriTanggal').innerText = 'Dirilis pada: ' + tanggal;
        
        const container = document.getElementById('materiPreviewContainer');
        container.innerHTML = ''; 

        if (fileUrl && fileUrl.trim() !== '') {
            if(fileUrl.toLowerCase().endsWith('.mp4') || fileUrl.toLowerCase().endsWith('.webm')) {
                container.innerHTML = `
                    <video controls class="w-full h-full object-contain bg-black absolute inset-0">
                        <source src="${fileUrl}" type="video/mp4">
                        Browser Anda tidak mendukung pemutar video.
                    </video>
                `;
            } else {
                container.innerHTML = `
                    <iframe src="${fileUrl}" class="w-full h-full absolute inset-0 border-0 bg-slate-100"></iframe>
                `;
            }
        } else {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center text-slate-400 p-10 text-center">
                    <i class="fas fa-file-alt text-6xl mb-4 opacity-30"></i>
                    <p class="font-bold text-lg text-slate-600">Tidak Ada File Lampiran</p>
                    <p class="text-sm mt-1">Materi ini hanya berisi teks instruksi atau link eksternal.</p>
                </div>
            `;
        }

        openSpecificModal('materiDetailModal');
    }

    // ================= FUNGSI UNTUK PREVIEW UJIAN NATIVE =================
    async function openUjianModal(judul, tipe, tanggal, waktu, assessmentId) {
        document.getElementById('detailUjianJudul').innerText = judul;
        document.getElementById('detailUjianTipe').innerText = tipe;
        document.getElementById('detailUjianTanggal').innerText = tanggal;
        document.getElementById('detailUjianWaktu').innerText = waktu + ' WIB';
        
        const container = document.getElementById('ujianNativeContainer');
        const paginationInfo = document.getElementById('ujianPaginationInfo');
        
        container.innerHTML = `<div class="w-full flex flex-col items-center justify-center h-full gap-3 bg-white"><i class="fas fa-circle-notch fa-spin text-4xl text-purple-500"></i><p class="text-sm font-medium text-slate-400">Mengambil data soal ujian...</p></div>`;
        paginationInfo.innerText = '';
        openSpecificModal('ujianDetailModal');

        const role = '{{ strtolower(Auth::user()->role) === "guru" ? "guru" : "siswa" }}';
        const schoolName = encodeURIComponent('{{ $schoolName }}');
        const schoolId = '{{ $schoolId }}';
        
        const fetchUrl = `{{ url('/') }}/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/review/${assessmentId}/paginate`;

        try {
            const response = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Data API tidak ditemukan atau URL salah.');
            
            const result = await response.json();
            currentQuestions = result.data || []; 
            currentActiveQuestionIndex = 0;

            if (!currentQuestions || currentQuestions.length === 0) {
                container.innerHTML = `<div class="w-full flex flex-col items-center justify-center h-full bg-white text-center p-6"><i class="fas fa-box-open text-6xl text-slate-200 mb-4"></i><p class="text-lg font-bold text-slate-700">Belum ada soal</p></div>`;
            } else {
                renderNativeQuestionUI();
            }
        } catch (error) {
            container.innerHTML = `<div class="w-full flex flex-col items-center justify-center h-full bg-white text-center p-6"><i class="fas fa-exclamation-triangle text-5xl text-red-400 mb-4"></i><p class="text-lg font-bold text-red-600">Gagal Memuat Soal</p><p class="text-sm text-slate-500 mt-1">${error.message}</p></div>`;
        }
    }

    function renderNativeQuestionUI() {
        const container = document.getElementById('ujianNativeContainer');
        const paginationInfo = document.getElementById('ujianPaginationInfo');
        
        let navHtml = `<div class="w-1/4 min-w-[200px] border-r border-slate-200 bg-slate-50 p-4 overflow-y-auto custom-scrollbar flex flex-col gap-2 shadow-[2px_0_10px_rgba(0,0,0,0.02)] z-10">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 px-1">Navigasi Soal</h4>
            <div class="flex flex-wrap gap-2">`;
        
        currentQuestions.forEach((q, idx) => {
            const isActive = idx === currentActiveQuestionIndex;
            navHtml += `<button onclick="changeNativeQuestion(${idx})" class="w-10 h-10 rounded-xl font-bold flex items-center justify-center transition-all border ${isActive ? 'bg-purple-600 text-white border-purple-600 shadow-md shadow-purple-200 scale-105' : 'bg-white text-slate-600 border-slate-200 hover:border-purple-300 hover:text-purple-600'}">${idx + 1}</button>`;
        });
        navHtml += `</div></div>`;

        const activeQ = currentQuestions[currentActiveQuestionIndex] || {};
        const bankData = activeQ.lms_question_bank || activeQ.LmsQuestionBank || {}; 
        const options = bankData.lms_question_option || bankData.LmsQuestionOption || [];

        let questionHtml = `<div class="w-3/4 bg-white p-6 md:p-8 overflow-y-auto custom-scrollbar relative">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                <span class="bg-purple-50 text-purple-700 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border border-purple-100">Soal No. ${currentActiveQuestionIndex + 1}</span>
                <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">Bobot: ${activeQ.question_weight || '-'} Poin</span>
            </div>`;

        questionHtml += bankData.questions ? `<div class="prose max-w-none text-slate-800 mb-8 leading-relaxed">${bankData.questions}</div>` : `<p class="text-slate-400 italic mb-8">Teks soal tidak tersedia.</p>`;

        if (options.length > 0) {
            questionHtml += `<div class="space-y-3 mt-4">`;
            const labels = ['A', 'B', 'C', 'D', 'E'];
            options.forEach((opt, idx) => {
                const isCorrect = opt.is_correct == 1 || opt.is_correct === true;
                questionHtml += `
                    <div class="flex items-start gap-4 p-4 rounded-xl border ${isCorrect ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 bg-white'}">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 font-bold text-sm ${isCorrect ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600'}">${labels[idx] || '-'}</div>
                        <div class="flex-1 mt-1 prose prose-sm text-slate-700 leading-normal">${opt.option_text || ''}</div>
                        ${isCorrect ? '<div class="shrink-0 text-emerald-500"><i class="fas fa-check-circle text-xl"></i></div>' : ''}
                    </div>`;
            });
            questionHtml += `</div>`;
        }

        if (bankData.explanation) {
            questionHtml += `<div class="mt-8 p-5 bg-blue-50 rounded-2xl border border-blue-100"><h5 class="text-sm font-bold text-blue-800 mb-2 flex items-center gap-2"><i class="fas fa-lightbulb text-yellow-500"></i> Pembahasan / Penjelasan</h5><div class="prose prose-sm max-w-none text-blue-900 leading-relaxed">${bankData.explanation}</div></div>`;
        }

        questionHtml += `</div>`;
        container.innerHTML = navHtml + questionHtml;
        paginationInfo.innerText = `Menampilkan soal ${currentActiveQuestionIndex + 1} dari total ${currentQuestions.length} soal`;
    }

    function changeNativeQuestion(idx) {
        currentActiveQuestionIndex = idx;
        renderNativeQuestionUI();
    }

    // ================= FUNGSI UNTUK PENILAIAN TUGAS =================
    async function openNilaiTugasModal(taskId, title, deadline, fileUrl) {
        currentTaskId = taskId;
        taskGradesData = {};
        document.getElementById('nilaiTugasTitle').innerText = title;
        document.getElementById('nilaiTugasDeadline').innerText = deadline;
        
        const previewSection = document.getElementById('tugasPreviewSection');
        const gradingSection = document.getElementById('tugasGradingSection');
        const iframeContainer = document.getElementById('tugasIframeContainer');

        if (fileUrl && fileUrl.trim() !== '' && fileUrl !== '{{ url("/") }}') {
            previewSection.classList.remove('hidden'); previewSection.classList.add('flex');
            gradingSection.classList.remove('w-full'); gradingSection.classList.add('w-5/12');
            document.getElementById('tugasFileLinkFull').href = fileUrl;
            iframeContainer.innerHTML = fileUrl.toLowerCase().endsWith('.mp4') || fileUrl.toLowerCase().endsWith('.webm') 
                ? `<video controls class="w-full h-full object-contain bg-black absolute inset-0"><source src="${fileUrl}" type="video/mp4"></video>` 
                : `<iframe src="${fileUrl}" class="w-full h-full absolute inset-0 border-0 bg-slate-100"></iframe>`;
        } else {
            previewSection.classList.add('hidden'); previewSection.classList.remove('flex');
            gradingSection.classList.remove('w-5/12'); gradingSection.classList.add('w-full');
            iframeContainer.innerHTML = '';
        }

        openSpecificModal('nilaiTugasModal');
        
        const container = document.getElementById('submissionListContainer');
        container.innerHTML = `<div class="flex flex-col items-center justify-center py-10 gap-3"><i class="fas fa-circle-notch fa-spin text-4xl text-amber-500"></i><p class="text-sm font-medium text-slate-400">Memuat data pengumpulan...</p></div>`;
        
        try {
            const fetchUrl = `{{ url('/lms/teacher/tugas') }}/${taskId}/submissions`;
            const response = await fetch(fetchUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Data pengumpulan tidak ditemukan (Tugas tidak valid untuk kelas ini).');

            const result = await response.json();
            if (result.error) throw new Error(result.error);

            const students = result.students || [];
            const task = result.task || { max_score: 100 };
            let html = '<div class="space-y-3">';
            
            if(students.length === 0) {
                html += '<p class="text-center py-5 text-slate-500">Tidak ada pengumpulan siswa di kelas ini.</p>';
            } else {
                students.forEach(s => {
                    const score = s.score !== null ? s.score : '';
                    taskGradesData[s.student_id] = score; 
                    const isSubmitted = s.submission_id ? true : false;
                    const badgeText = isSubmitted ? 'Sudah Kirim' : 'Belum Kirim';
                    const badgeColor = isSubmitted ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-500 border-red-200';
                    const inputState = isSubmitted ? '' : 'disabled';
                    const inputStyle = isSubmitted ? 'bg-white border-slate-200 text-slate-700 focus:border-amber-500' : 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60';

                    html += `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-slate-200 rounded-2xl gap-4 hover:border-amber-300 transition-colors shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-200 shrink-0"><i class="fas fa-user"></i></div>
                            <div>
                                <span class="font-bold text-slate-700 block text-sm">${s.name}</span>
                                <div class="flex items-center gap-2 mt-1"><span class="text-[9px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider ${badgeColor}">${badgeText}</span></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-bold text-slate-400 hidden sm:block">Skor:</span>
                            <input type="number" ${inputState} min="0" max="${task.max_score}" value="${score}" oninput="updateGradeData(${s.student_id}, this.value)" class="w-16 sm:w-20 px-2 py-1.5 text-center font-bold rounded-xl focus:outline-none focus:ring-2 transition-all border ${inputStyle}" placeholder="-">
                        </div>
                    </div>`;
                });
            }
            html += '</div>';
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = `<div class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-amber-200 rounded-2xl bg-amber-50/50 m-2"><i class="fas fa-user-graduate text-4xl mb-3 text-amber-300"></i><p class="text-sm font-bold text-amber-700">Daftar Pengumpulan Gagal Dimuat</p><p class="text-xs text-amber-600 mt-1 px-4">${error.message}</p></div>`;
        }
    }

    function updateGradeData(studentId, score) { taskGradesData[studentId] = score; }

    async function submitNilaiTugas() {
        const btn = document.querySelector('.btn-simpan-nilai');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`;
        btn.disabled = true;

        let token = document.querySelector('meta[name="csrf-token"]');
        let csrfToken = token ? token.getAttribute('content') : '';

        try {
            const response = await fetch("{{ url('/lms/teacher/tugas/grades') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ task_id: currentTaskId, grades: taskGradesData })
            });
            const result = await response.json();
            if (response.ok && result.success !== false) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message || 'Nilai tersimpan', timer: 1500, showConfirmButton: false });
                closeModal('nilaiTugasModal');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Gagal menyimpan nilai.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // ================= FUNGSI UNTUK ATTENDANCE =================
    async function openAttendanceModal() {
        openSpecificModal('attendanceModal');
        const container = document.getElementById('studentListContainer');
        container.innerHTML = `<tr><td colspan="3" class="py-10 text-center"><i class="fas fa-circle-notch fa-spin text-3xl text-emerald-500"></i><p class="mt-2 text-slate-400">Memuat data siswa...</p></td></tr>`;
        
        try {
            const fetchUrl = `{{ route('lms.guru.get_students', ['classId' => $jadwal->class_id ?? 0]) }}?schedule_id={{ $jadwal->id ?? 0 }}`;
            const response = await fetch(fetchUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            
            if (!response.ok) throw new Error(`Server bermasalah (HTTP ${response.status})`);
            const result = await response.json();
            if (result.error) throw new Error(result.error);
            
            const students = result || [];
            let html = '';
            
            if (!Array.isArray(students) || students.length === 0) {
                html = `<tr><td colspan="3" class="py-8 text-center text-slate-500">Belum ada data siswa aktif di kelas ini.</td></tr>`;
            } else {
                students.forEach((s, index) => {
                    const savedStatus = s.status || 'hadir'; 
                    studentAttendanceData[s.id] = savedStatus; 
                    
                    html += `
                        <tr class="hover:bg-emerald-50/30 transition-colors">
                            <td class="py-3 px-6 border-b border-slate-100 text-center font-medium text-slate-400 text-sm">${index + 1}</td>
                            <td class="py-3 px-6 border-b border-slate-100 font-bold text-slate-700">${s.name}</td>
                            <td class="py-3 px-6 border-b border-slate-100">
                                <div class="flex justify-center gap-1.5 sm:gap-2">
                                    ${['hadir', 'izin', 'sakit', 'alpa'].map(st => `
                                        <button onclick="setStatus(${s.id}, '${st}', this)" 
                                            class="status-btn-${s.id} px-3 py-1.5 rounded-lg text-[10px] sm:text-xs font-extrabold uppercase tracking-wider transition-all border flex-1 max-w-[80px]
                                            ${st === savedStatus ? getBtnColor(st) : 'bg-white text-slate-400 border-slate-200 hover:border-emerald-200 hover:bg-emerald-50'}">
                                            ${st}
                                        </button>
                                    `).join('')}
                                </div>
                            </td>
                        </tr>`;
                });
            }
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = `<tr><td colspan="3" class="py-10 text-center border-2 border-dashed border-red-200 m-4 rounded-xl bg-red-50"><i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-2"></i><p class="text-red-700 font-bold">Data Tidak Dapat Dimuat</p><p class="text-xs text-red-600 mt-1">${e.message}</p></td></tr>`;
        }
    }

    function getBtnColor(status) {
        const colors = { hadir: 'bg-emerald-500 text-white border-emerald-500', izin: 'bg-blue-500 text-white border-blue-500', sakit: 'bg-amber-500 text-white border-amber-500', alpa: 'bg-red-500 text-white border-red-500' };
        return colors[status];
    }

    function setStatus(studentId, status, btn) {
        studentAttendanceData[studentId] = status;
        document.querySelectorAll(`.status-btn-${studentId}`).forEach(b => {
            b.className = `status-btn-${studentId} px-3 py-1.5 rounded-lg text-[10px] sm:text-xs font-extrabold uppercase tracking-wider transition-all border flex-1 max-w-[80px] bg-white text-slate-400 border-slate-200 hover:border-emerald-200 hover:bg-emerald-50`;
        });
        btn.className = `status-btn-${studentId} px-3 py-1.5 rounded-lg text-[10px] sm:text-xs font-extrabold uppercase tracking-wider transition-all border flex-1 max-w-[80px] ${getBtnColor(status)}`;
    }

    async function submitAttendance() {
        const btn = document.querySelector('#attendanceContent .bg-slate-50.border-t button:nth-child(2)');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`; btn.disabled = true;

        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let scheduleId = '{{ $jadwal->id ?? 0 }}';

        try {
            const response = await fetch("{{ url('/lms/teacher/attendance/store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ schedule_id: scheduleId, attendance: studentAttendanceData })
            });

            if (response.ok) {
                const summary = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
                Object.values(studentAttendanceData).forEach(st => summary[st]++);
                initOrUpdateChart(summary);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Presensi tersimpan!', timer: 1500, showConfirmButton: false });
                closeModal('attendanceModal');
            } else {
                const result = await response.json();
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Gagal menyimpan data.' });
            }
        } catch (error) { Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Gagal terhubung dengan server.' }); } 
        finally { btn.innerHTML = originalText; btn.disabled = false; }
    }

    // ================= FUNGSI UNTUK PENGUMUMAN =================
    async function submitPengumuman(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-pengumuman');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...`; btn.disabled = true;

        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch("{{ url('/lms/teacher/pengumuman/store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: new FormData(form) 
            });
            const result = await response.json();
            if(result.success) {
                closeModal('pengumumanModal');
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 1500, showConfirmButton: false }).then(() => { window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
                btn.innerHTML = originalText; btn.disabled = false;
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: "Terjadi kesalahan jaringan." });
            btn.innerHTML = originalText; btn.disabled = false;
        }
    }
</script>