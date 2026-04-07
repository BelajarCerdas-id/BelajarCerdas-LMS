@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda Siswa'])

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-slate-100 min-h-screen">

        <div class="p-6 md:p-8">
            
            <div class="mb-8 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-[2rem] p-8 shadow-lg shadow-blue-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/4 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 right-32 translate-y-1/2 w-40 h-40 bg-blue-400/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute top-8 left-1/2 w-32 h-32 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                        Halo, {{ explode(' ', Auth::user()->StudentProfile->nama_lengkap)[0] ?? 'Siswa' }} 👋
                    </h1>
                    <p class="text-blue-100 mt-2 text-sm font-medium">Mari bersiap untuk kegiatan belajarmu hari ini. Jangan lupa cek tugasmu!</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <div class="xl:col-span-2 flex flex-col gap-8">
                    
                    <div class="bg-gradient-to-br from-white to-blue-50/50 rounded-[2rem] shadow-sm border border-blue-100 p-6 md:p-8 flex flex-col max-h-[500px]">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b border-blue-100/50 gap-4 shrink-0">
                            <h2 class="text-xl font-bold text-[#0071BC] flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-blue-200">
                                    <i class="fas fa-book-open text-lg"></i>
                                </div>
                                Jadwal Pelajaran
                            </h2>
                            <span class="text-sm font-semibold text-blue-700 bg-white px-5 py-2 rounded-full border border-blue-200 shadow-sm">
                                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-3 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @if(in_array($hariIni, ['Sabtu', 'Minggu']))
                                <div class="flex flex-col items-center justify-center py-10 px-6 text-center bg-white/60 rounded-2xl border border-blue-100 h-full backdrop-blur-sm">
                                    <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-full bg-blue-50 shadow-inner border border-blue-100 text-[#0071BC]">
                                        <i class="fas fa-umbrella-beach text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-extrabold text-[#0071BC] mb-2">Hore, hari ini Weekend! 🎉</h3>
                                    <p class="text-blue-600/70 max-w-md font-medium text-xs">Tidak ada jadwal pelajaran untuk hari Sabtu dan Minggu. Selamat beristirahat dan jangan lupa kerjakan tugas!</p>
                                </div>
                            @else
                                @forelse($jadwalHariIni as $jadwal)
                                    <div onclick="openMapelModal('{{ addslashes($jadwal['mapel']) }}', '{{ addslashes($jadwal['jam']) }}', '{{ addslashes($jadwal['guru'] ?? '-') }}', '{{ addslashes($jadwal['ruang'] ?? '-') }}', {{ $jadwal['is_break'] ? 'true' : 'false' }})" class="cursor-pointer flex flex-col md:flex-row md:items-center gap-3 p-3 md:py-2.5 md:px-4 rounded-xl border {{ $jadwal['mapel'] == 'ISTIRAHAT' || str_contains($jadwal['mapel'], 'ISTIRAHAT') ? 'bg-gradient-to-r from-orange-50 to-white border-orange-200' : 'bg-white border-blue-50 hover:border-blue-300 hover:shadow-md transition-all group' }}">
                                        <div class="w-full md:w-28 shrink-0">
                                            <span class="text-sm font-bold text-gray-800 {{ $jadwal['mapel'] == 'ISTIRAHAT' || str_contains($jadwal['mapel'], 'ISTIRAHAT') ? 'text-orange-600' : 'group-hover:text-[#0071BC] transition-colors' }}">
                                                <i class="far fa-clock mr-1 text-gray-400 group-hover:text-blue-400 transition-colors"></i> {{ $jadwal['jam'] }}
                                            </span>
                                        </div>
                                        <div class="hidden md:block w-1 h-8 {{ $jadwal['mapel'] == 'ISTIRAHAT' || str_contains($jadwal['mapel'], 'ISTIRAHAT') ? 'bg-orange-300' : 'bg-blue-100 group-hover:bg-blue-400 transition-colors' }} rounded-full"></div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-base {{ $jadwal['is_break'] ? 'text-orange-600' : 'text-gray-800 group-hover:text-[#0071BC] transition-colors' }}">
                                                {{ $jadwal['mapel'] }}
                                            </h3>
                                            @if(!$jadwal['is_break'])
                                                <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-4">
                                                    <span><i class="fas fa-user-tie text-blue-400 mr-1"></i> {{ $jadwal['guru'] ?? '-' }}</span>
                                                    <span><i class="fas fa-door-open text-blue-400 mr-1"></i> {{ $jadwal['ruang'] ?? '-' }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-10 px-6 text-center border-2 border-dashed border-blue-200 bg-white/50 rounded-2xl h-full">
                                        <div class="w-14 h-14 mb-3 flex items-center justify-center rounded-full bg-blue-50 shadow-inner border border-blue-100 text-blue-400">
                                            <i class="fas fa-calendar-xmark text-xl"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-blue-800 mb-1">Jadwal Belum Tersedia</h3>
                                        <p class="text-blue-600/70 text-xs font-medium">Guru belum mempublikasikan jadwal pelajaran untuk kelasmu.</p>
                                    </div>
                                @endforelse
                            @endif
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-purple-50/60 rounded-[2rem] shadow-sm border border-purple-100 p-6 md:p-8 flex flex-col">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-purple-100/50 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-purple-200 font-bold text-lg">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <h3 class="font-bold text-purple-900 text-lg">Modul Belum Dibaca 📖</h3>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="prevModule()" id="btnPrevModule" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-purple-100 text-purple-600 hover:bg-purple-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                                    <i class="fas fa-chevron-left text-sm"></i>
                                </button>
                                <button onclick="nextModule()" id="btnNextModule" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-purple-100 text-purple-600 hover:bg-purple-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="w-full overflow-hidden">
                            <div id="moduleSlider" class="flex transition-transform duration-500 ease-out w-full">
                                @forelse($unreadModules ?? [] as $modul)
                                    <div class="w-full shrink-0 px-1">
                                        <div class="bg-white border border-purple-100 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5 group hover:border-purple-300 hover:shadow-md transition-all">
                                            <div class="w-14 h-14 bg-purple-50 rounded-xl shadow-inner border border-purple-100 flex items-center justify-center text-purple-500 text-2xl shrink-0 group-hover:scale-105 transition-transform">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs font-extrabold text-purple-600 mb-1 uppercase tracking-wider">{{ $modul->mapel ?? 'Mata Pelajaran' }}</p>
                                                <h4 class="font-bold text-slate-800 text-lg mb-1 leading-tight">{{ $modul->judul ?? 'Judul Modul Materi' }}</h4>
                                                <p class="text-sm text-slate-500 line-clamp-2">{{ $modul->deskripsi ?? 'Silakan baca modul ini untuk mempersiapkan materi pembelajaran selanjutnya.' }}</p>
                                            </div>
                                            <a href="{{ isset($modul->id) ? route('student.module.read', $modul->id) : '#' }}" class="mt-2 sm:mt-0 w-full sm:w-auto text-center py-2.5 px-6 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-sm font-bold rounded-xl shadow-md shadow-purple-200 transition-all shrink-0">
                                                Baca Sekarang
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="w-full shrink-0 flex flex-col items-center justify-center py-8 text-center bg-white/50 rounded-2xl border-2 border-dashed border-purple-200">
                                        <div class="w-14 h-14 bg-purple-50 shadow-inner border border-purple-100 rounded-full flex items-center justify-center mb-3 text-purple-400">
                                            <i class="fas fa-check-circle text-2xl"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-purple-800 mb-1">Hebat!</h4>
                                        <p class="text-sm text-purple-600/70 font-medium">Semua modul pembelajaran sudah kamu baca.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-sky-50/60 rounded-[2rem] shadow-sm border border-sky-100 p-6 md:p-8 flex flex-col">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-sky-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-sky-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-sky-200 font-bold text-lg">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sky-900 text-lg leading-tight">Papan Pengumuman</h3>
                                <p class="text-xs text-sky-600/70 font-medium">Informasi penting dari guru & sekolah</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @forelse($pengumumanTerkini ?? [] as $info)
                                <div class="border border-sky-100 rounded-2xl p-5 hover:shadow-lg hover:border-sky-300 transition-all bg-white flex flex-col group">
                                    <div class="flex justify-between items-start mb-3">
                                        @if(($info->type ?? 'info') == 'penting')
                                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200 uppercase tracking-wider shadow-sm">
                                                Penting
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-md border border-sky-200 uppercase tracking-wider shadow-sm">
                                                Info Kelas
                                            </span>
                                        @endif
                                        <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                                            {{ isset($info->created_at) ? \Carbon\Carbon::parse($info->created_at)->diffForHumans() : 'Baru saja' }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-sm mb-2 line-clamp-1 group-hover:text-sky-600 transition-colors">{{ $info->title ?? 'Judul Pengumuman' }}</h4>
                                    <p class="text-xs text-slate-500 mb-5 line-clamp-2 h-8">{{ $info->content ?? 'Isi pengumuman tidak tersedia.' }}</p>
                                    
                                    {{-- Tambahkan $info->id sebagai parameter PERTAMA --}}
                                    <button onclick="bacaPengumuman('{{ $info->id }}', '{!! addslashes($info->title) !!}', '{!! addslashes($info->content) !!}', '{{ \Carbon\Carbon::parse($info->created_at)->format('d M Y, H:i') }}', '{{ $info->type }}')" 
                                            class="mt-auto w-full py-2.5 bg-sky-50 text-sky-700 text-xs font-bold rounded-xl border border-sky-100 hover:bg-sky-600 hover:text-white transition-all shadow-sm">
                                        Baca Selengkapnya
                                    </button>
                                </div>
                            @empty
                                <div class="col-span-full flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-sky-200 rounded-2xl bg-white/50">
                                    <i class="fas fa-box-open text-3xl mb-3 text-sky-300"></i>
                                    <p class="text-sm font-bold text-sky-800 mb-1">Belum Ada Pengumuman</p>
                                    <p class="text-xs font-medium text-sky-500">Tidak ada informasi terbaru untuk kelasmu.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-indigo-50/60 rounded-[2rem] shadow-sm border border-indigo-100 p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-indigo-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-indigo-200 font-bold text-lg">
                                <i class="fas fa-person-circle-question"></i>
                            </div>
                            <h3 class="font-bold text-indigo-900 text-lg">Polling Berlangsung 📢</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($activePolls as $poll)
                                <div class="border border-indigo-100 rounded-2xl p-5 hover:border-indigo-300 hover:shadow-lg transition-all bg-white flex flex-col">
                                    <h4 class="font-bold text-slate-800 mb-4 leading-snug">{{ $poll->question }}</h4>
                                    <form class="mt-auto space-y-2.5" onsubmit="submitSiswaVote(event, {{ $poll->id }})">
                                        @foreach($poll->options as $opt)
                                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50 hover:border-indigo-300 hover:bg-indigo-50/50 shadow-sm cursor-pointer transition-all group">
                                                <input type="radio" name="option_{{ $poll->id }}" value="{{ $opt->id }}" required class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600">
                                                <span class="text-sm font-semibold text-slate-600 group-hover:text-indigo-700 transition-colors">{{ $opt->text }}</span>
                                            </label>
                                        @endforeach
                                        <button type="submit" class="w-full mt-4 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 shadow-md shadow-indigo-200 font-bold text-white rounded-xl transition-all btn-submit-vote text-sm transform hover:-translate-y-0.5">
                                            Kirim Suara
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="col-span-full flex flex-col items-center justify-center py-10 px-6 text-center border-2 border-dashed border-indigo-200 bg-white/50 rounded-2xl">
                                    <div class="w-16 h-16 mb-3 flex items-center justify-center rounded-full bg-indigo-50 shadow-inner border border-indigo-100 text-indigo-400">
                                        <i class="fas fa-box-open text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-indigo-800 mb-1">Belum Ada Polling</h3>
                                    <p class="text-indigo-600/70 text-sm font-medium">Saat ini belum ada jejak pendapat dari Guru yang perlu diisi.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1 flex flex-col gap-8">
                    
                    <div class="bg-gradient-to-br from-white to-rose-50/60 rounded-[2rem] shadow-sm border border-rose-100 p-6 flex flex-col max-h-[350px]">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-rose-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-rose-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-rose-200 font-bold text-lg">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h3 class="font-bold text-rose-900 text-lg leading-tight">Tugas & PR 📝</h3>
                        </div>

                        <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($pendingTasks ?? [] as $task)
                                <div class="flex items-start gap-3 p-4 rounded-2xl border border-rose-100 bg-white hover:border-rose-300 hover:shadow-md transition-all group">
                                    <div class="mt-1">
                                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse shadow-sm shadow-rose-300"></div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-slate-800 text-sm group-hover:text-rose-600 transition-colors">{{ $task->judul_tugas ?? 'Tugas Belum Dinamai' }}</h4>
                                        <p class="text-[11px] font-bold text-rose-500 mt-1.5 flex items-center gap-1.5 bg-rose-50 w-fit px-2 py-0.5 rounded-md">
                                            <i class="far fa-clock"></i> Deadline: {{ isset($task->deadline) ? \Carbon\Carbon::parse($task->deadline)->translatedFormat('d M, H:i') : 'Segera' }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 flex flex-col items-center justify-center h-full bg-white/50 rounded-2xl border-2 border-dashed border-rose-200">
                                    <div class="w-14 h-14 bg-rose-50 shadow-inner border border-rose-100 rounded-full flex items-center justify-center mb-3 text-rose-400">
                                        <i class="fas fa-check-double text-xl"></i>
                                    </div>
                                    <p class="text-sm text-rose-600/70 font-bold">Yeay! Tidak ada PR<br>yang belum dikerjakan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-orange-50/60 rounded-[2rem] shadow-sm border border-orange-100 p-6 flex flex-col max-h-[500px]">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-orange-100/50 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-500 text-white rounded-xl flex items-center justify-center shadow-md shadow-orange-200 font-bold text-lg">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-orange-900 text-lg leading-tight">Agenda Kegiatan</h3>
                                    <p class="text-xs text-orange-700/70 font-medium">Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-5 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($monthlyEvents as $event)
                                <div class="flex gap-4 items-start group">
                                    <div class="flex flex-col items-center mt-1">
                                        <div class="w-4 h-4 rounded-full shadow-md border-2 border-white z-10" style="background-color: {{ $event->color }}"></div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-16 bg-orange-200 -mt-2 group-hover:bg-orange-400 transition-colors"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-2 bg-white p-3 rounded-xl border border-orange-50 shadow-sm hover:border-orange-200 transition-colors -mt-2">
                                        <span class="font-bold text-slate-800 block text-sm mb-0.5">
                                            {{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d F') }}
                                        </span>
                                        <span class="text-xs text-slate-500 block leading-snug">
                                            {{ $event->title }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 flex flex-col items-center justify-center h-full border-2 border-dashed border-orange-200 rounded-2xl bg-white/50">
                                    <div class="w-16 h-16 bg-orange-50 shadow-inner border border-orange-100 rounded-full flex items-center justify-center mb-3 text-orange-400">
                                        <i class="fas fa-calendar-check text-2xl"></i>
                                    </div>
                                    <p class="text-sm text-orange-700/70 font-bold">Belum ada agenda<br>di bulan ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-emerald-50/60 rounded-[2rem] shadow-sm border border-emerald-100 p-6 flex flex-col max-h-[500px]">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-emerald-100/50 shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-emerald-200 font-bold text-lg">
                                <i class="fas fa-chart-simple"></i>
                            </div>
                            <h3 class="font-bold text-emerald-900 text-lg leading-tight">Hasil Polling 📊</h3>
                        </div>

                        <div class="flex flex-col gap-6 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            @forelse($votedPolls as $poll)
                                <div class="border border-emerald-100 bg-white rounded-2xl p-5 hover:shadow-lg hover:border-emerald-300 transition-all">
                                    <p class="text-sm font-bold text-slate-800 mb-4 leading-snug">{{ $poll->question }}</p>
                                    <div class="space-y-3.5">
                                        @foreach($poll->options as $opt)
                                            <div class="relative w-full">
                                                <div class="flex justify-between text-xs font-bold mb-1.5">
                                                    <span class="text-slate-600 truncate pr-2">{{ $opt->text }}</span>
                                                    <span class="text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-md">{{ $opt->percentage }}%</span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden shadow-inner">
                                                    <div class="bg-gradient-to-r from-emerald-400 to-emerald-500 h-2 rounded-full" style="width: {{ $opt->percentage }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-[10px] text-emerald-600/70 font-bold mt-5 text-right bg-emerald-50 px-2 py-1 w-fit ml-auto rounded-md border border-emerald-100">
                                        <i class="fas fa-users mr-1"></i> {{ $poll->total_votes }} Suara Masuk
                                    </p>
                                </div>
                            @empty
                                <div class="text-center py-8 flex flex-col items-center justify-center h-full border-2 border-dashed border-emerald-200 rounded-2xl bg-white/50">
                                    <div class="w-14 h-14 bg-emerald-50 shadow-inner border border-emerald-100 rounded-full flex items-center justify-center mb-3 text-emerald-400">
                                        <i class="fas fa-chart-pie text-xl"></i>
                                    </div>
                                    <p class="text-sm text-emerald-700/70 font-bold">Belum ada hasil polling<br>yang tersedia.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div id="announcementModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm cursor-pointer" onclick="closeModal()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-[90%] max-w-md p-6 md:p-8 transform scale-95 transition-transform duration-300" id="modalContent">
                <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
                <div class="w-16 h-16 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-3xl mb-5 mx-auto shadow-inner border border-blue-100">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Pemberitahuan Baru!</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Selamat datang di Dashboard Siswa. Pastikan untuk selalu memeriksa Jadwal Pelajaran, Modul, dan Tugas barumu hari ini!
                    </p>
                </div>
                <button onclick="closeModal()" class="w-full py-3 px-4 bg-gradient-to-r from-[#0071BC] to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-200 transition-all transform hover:-translate-y-0.5">
                    Baik, Saya Mengerti
                </button>
            </div>
        </div>

        <div id="mapelDetailModal" class="fixed inset-0 z-[110] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm cursor-pointer" onclick="closeMapelModal()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-[90%] max-w-sm p-6 md:p-8 transform scale-95 transition-transform duration-300" id="mapelModalContent">
                <button onclick="closeMapelModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
                <div id="mapelIconContainer" class="w-16 h-16 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-3xl mb-5 mx-auto shadow-inner border border-blue-100">
                    <i id="mapelIcon" class="fas fa-book-open"></i>
                </div>
                <div class="text-center mb-6">
                    <h3 id="modalMapelTitle" class="text-xl font-bold text-gray-800 mb-2">Nama Mapel</h3>
                    <div class="inline-flex items-center gap-2 bg-blue-50 px-4 py-1.5 rounded-full border border-blue-100">
                        <i class="far fa-clock text-[#0071BC]"></i>
                        <span id="modalMapelJam" class="text-sm font-bold text-[#0071BC]">00:00 - 00:00</span>
                    </div>
                </div>
                <div id="modalMapelInfo" class="space-y-4 mb-6 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-blue-500 shadow-sm shrink-0 border border-gray-100">
                            <i class="fas fa-user-tie text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-0.5">Guru Pengajar</p>
                            <p id="modalMapelGuru" class="text-sm font-bold text-gray-800">Nama Guru</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-blue-500 shadow-sm shrink-0 border border-gray-100">
                            <i class="fas fa-door-open text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-0.5">Ruang Kelas</p>
                            <p id="modalMapelRuang" class="text-sm font-bold text-gray-800">Nama Ruangan</p>
                        </div>
                    </div>
                </div>
                <button onclick="closeMapelModal()" class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">
                    Tutup Detail
                </button>
            </div>
        </div>

        <div id="bacaPengumumanModal" class="fixed inset-0 z-[110] flex items-center justify-center hidden opacity-0 transition-opacity duration-300 p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm cursor-pointer" onclick="closeBacaPengumuman()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-[90%] max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[85vh]" id="bacaPengumumanContent">
                
                <div id="headerPengumuman" class="px-6 py-5 bg-blue-600 flex justify-between items-center text-white shrink-0">
                    <h3 class="font-bold text-lg flex items-center gap-2"><i class="fas fa-bullhorn"></i> Detail Pengumuman</h3>
                    <button onclick="closeBacaPengumuman()" class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
                </div>
                
                <div class="p-6 md:p-8 overflow-y-auto custom-scrollbar">
                    <div class="flex justify-between items-center mb-4">
                        <span id="badgePengumuman" class="text-[10px] font-bold px-3 py-1 rounded-md border uppercase tracking-wider"></span>
                        <span id="tglPengumuman" class="text-xs font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-md"></span>
                    </div>
                    
                    <h2 id="judulPengumuman" class="text-xl md:text-2xl font-extrabold text-slate-800 mb-4 leading-snug"></h2>
                    
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                        <p id="isiPengumuman" class="text-sm text-slate-600 leading-relaxed whitespace-pre-line"></p>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
                    <button onclick="closeBacaPengumuman()" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-100 transition-colors shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center bg-gray-50">
        <i class="fas fa-lock text-5xl text-red-400 mb-4"></i>
        <p class="font-bold text-xl text-red-500">Akses Ditolak</p>
        <p class="text-gray-500">Halaman ini khusus untuk Siswa.</p>
    </div>
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    // ================= FUNGSI SLIDER MODUL =================
    let currentModuleIndex = 0;
    
    function updateModuleSlider() {
        const slider = document.getElementById('moduleSlider');
        if (!slider) return;

        const items = slider.children;
        const totalItems = items.length;
        const btnPrev = document.getElementById('btnPrevModule');
        const btnNext = document.getElementById('btnNextModule');
        
        if(totalItems <= 1) {
            if(btnPrev) btnPrev.disabled = true;
            if(btnNext) btnNext.disabled = true;
            return;
        }

        slider.style.transform = `translateX(-${currentModuleIndex * 100}%)`;

        if(btnPrev) btnPrev.disabled = currentModuleIndex === 0;
        if(btnNext) btnNext.disabled = currentModuleIndex === totalItems - 1;
    }

    function nextModule() {
        const slider = document.getElementById('moduleSlider');
        if(slider && currentModuleIndex < slider.children.length - 1) {
            currentModuleIndex++;
            updateModuleSlider();
        }
    }

    function prevModule() {
        if(currentModuleIndex > 0) {
            currentModuleIndex--;
            updateModuleSlider();
        }
    }

    // ================= INISIALISASI SAAT DOM LOADED =================
    document.addEventListener("DOMContentLoaded", function() {
        updateModuleSlider();

        // Munculkan Modal Notifikasi Awal
        setTimeout(function() {
            const modal = document.getElementById('announcementModal');
            const modalContent = document.getElementById('modalContent');
            
            if(modal && modalContent) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                }, 10);
            }
        }, 500); 
    });

    // ================= FUNGSI BACA PENGUMUMAN =================
    async function bacaPengumuman(id, judul, isi, tanggal, tipe) {
        // 1. Tampilkan modal seperti biasa
        document.getElementById('judulPengumuman').innerText = judul;
        document.getElementById('isiPengumuman').innerText = isi;
        document.getElementById('tglPengumuman').innerText = tanggal;
        // ... (logika badge dan header tetap sama) ...

        const modal = document.getElementById('bacaPengumumanModal');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.replace('opacity-0', 'opacity-100'); }, 10);

        // 2. Kirim data ke server bahwa ini sudah "DIBACA"
        let token = document.querySelector('meta[name="csrf-token"]');
        let csrfToken = token ? token.getAttribute('content') : '{{ csrf_token() }}';

        try {
            const response = await fetch("{{ route('lms.student.announcement.read') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: id })
            });
            
            // PERUBAHAN DI SINI: Kita baca hasil dari server
            const result = await response.json();
            
            if (!result.success) {
                // Jika gagal di database, munculkan pesan errornya
                console.error("Error Database:", result.message);
                alert("Gagal mencatat! Error: " + result.message); 
            } else {
                console.log("Berhasil tercatat dilihat!");
            }
            
        } catch (e) {
            console.log("Gagal melakukan request jaringan.", e);
        }
    }

    function closeBacaPengumuman() {
        const modal = document.getElementById('bacaPengumumanModal');
        const modalContent = document.getElementById('bacaPengumumanContent');
        
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); 
    }

    // ================= FUNGSI UNTUK MODAL NOTIFIKASI AWAL =================
    function closeModal() {
        const modal = document.getElementById('announcementModal');
        const modalContent = document.getElementById('modalContent');
        
        if(modal && modalContent) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); 
        }
    }

    // ================= FUNGSI UNTUK MODAL DETAIL MAPEL =================
    function openMapelModal(mapel, jam, guru, ruang, isBreak) {
        document.getElementById('modalMapelTitle').innerText = mapel;
        document.getElementById('modalMapelJam').innerText = jam;
        document.getElementById('modalMapelGuru').innerText = guru;
        document.getElementById('modalMapelRuang').innerText = ruang;

        const infoSection = document.getElementById('modalMapelInfo');
        const iconContainer = document.getElementById('mapelIconContainer');
        const icon = document.getElementById('mapelIcon');
        const title = document.getElementById('modalMapelTitle');

        if (isBreak) {
            infoSection.classList.add('hidden');
            iconContainer.className = "w-16 h-16 bg-gradient-to-br from-orange-400 to-orange-500 text-white rounded-2xl flex items-center justify-center text-3xl mb-5 mx-auto shadow-md";
            icon.className = "fas fa-utensils";
            title.className = "text-xl font-bold text-orange-600 mb-2";
        } else {
            infoSection.classList.remove('hidden');
            iconContainer.className = "w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl mb-5 mx-auto shadow-md";
            icon.className = "fas fa-book-open";
            title.className = "text-xl font-bold text-gray-800 mb-2";
        }

        const modal = document.getElementById('mapelDetailModal');
        const modalContent = document.getElementById('mapelModalContent');
        
        if(modal && modalContent) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }
    }

    function closeMapelModal() {
        const modal = document.getElementById('mapelDetailModal');
        const modalContent = document.getElementById('mapelModalContent');
        
        if(modal && modalContent) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); 
        }
    }

    // ================= FUNGSI AJAX UNTUK POLLING SISWA =================
    async function submitSiswaVote(event, pollId) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('.btn-submit-vote');
        const selectedOption = form.querySelector(`input[name="option_${pollId}"]:checked`);

        if(!selectedOption) {
            alert("Silakan pilih salah satu jawaban terlebih dahulu!");
            return;
        }

        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...`;
        btn.disabled = true;

        try {
            const response = await fetch(`{{ route('lms.studentPolling.vote') }}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ poll_id: pollId, option_id: selectedOption.value })
            });

            const result = await response.json();
            
            if(result.success) {
                window.location.reload(); 
            } else {
                alert(result.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            alert("Terjadi kesalahan jaringan saat mengirim suara.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
    
</script>