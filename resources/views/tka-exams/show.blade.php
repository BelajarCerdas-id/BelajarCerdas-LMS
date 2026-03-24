<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Simulasi TKA'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="py-8 px-4 md:px-8">
        <!-- Back Button with Animation -->
        <div class="mb-6">
            <a href="{{ route('tka-exams.index') }}" class="inline-flex items-center gap-2 text-[#0071BC] hover:text-blue-700 font-semibold transition group">
                <div class="p-2 bg-white rounded-lg shadow-md group-hover:shadow-lg transition-shadow">
                    <i class="fa-solid fa-arrow-left"></i>
                </div>
                <span class="group-hover:underline">Kembali ke Daftar TKA</span>
            </a>
        </div>

        <!-- Hero Header with Enhanced Design -->
        <div class="relative bg-[#0071BC] rounded-3xl shadow-2xl overflow-hidden mb-8">
            <!-- Animated Background Pattern -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2 animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2 animate-pulse"></div>
            </div>

            <!-- Floating Icons -->
            <div class="absolute inset-0 overflow-hidden">
                <i class="fa-solid fa-graduation-cap absolute top-10 right-20 text-6xl text-blue-200/50 animate-bounce"></i>
                <i class="fa-solid fa-book-open absolute bottom-10 left-20 text-5xl text-blue-200/50 animate-bounce" style="animation-delay: 0.5s;"></i>
                <i class="fa-solid fa-pencil absolute top-1/2 left-1/4 text-4xl text-blue-300/50 animate-bounce" style="animation-delay: 1s;"></i>
            </div>

            <div class="relative z-10 px-6 py-10 md:px-10 md:py-14">
                <div class="flex flex-col md:flex-row items-start gap-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-500 rounded-3xl blur-lg opacity-50 animate-pulse"></div>
                        <div class="relative p-5 bg-gradient-to-br from-blue-400 to-blue-500 rounded-3xl backdrop-blur-sm shadow-2xl">
                            <i class="fa-solid fa-graduation-cap text-5xl text-white"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-4 py-1.5 bg-white/20 backdrop-blur-sm text-white text-xs font-bold rounded-full border border-white/30">
                                {{ ucfirst($exam->difficulty) }}
                            </span>
                            @if($exam->isAvailable())
                                <span class="px-4 py-1.5 bg-green-500/80 backdrop-blur-sm text-white text-xs font-bold rounded-full border border-green-300/30 animate-pulse">
                                    <i class="fa-solid fa-circle-check mr-1"></i>Tersedia
                                </span>
                            @else
                                <span class="px-4 py-1.5 bg-red-500/80 backdrop-blur-sm text-white text-xs font-bold rounded-full border border-red-300/30">
                                    <i class="fa-solid fa-circle-xmark mr-1"></i>Tidak Tersedia
                                </span>
                            @endif
                        </div>
                        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-3 leading-tight">{{ $exam->title }}</h1>
                        <p class="text-blue-100 text-base md:text-lg leading-relaxed max-w-4xl">{{ $exam->description }}</p>
                        
                        <!-- Quick Stats -->
                        <div class="flex flex-wrap gap-4 mt-6">
                            <div class="flex items-center gap-2 text-white/90">
                                <i class="fa-solid fa-users text-xl"></i>
                                <span class="text-sm font-semibold">{{ $exam->total_questions }} Soal</span>
                            </div>
                            <div class="flex items-center gap-2 text-white/90">
                                <i class="fa-solid fa-clock text-xl"></i>
                                <span class="text-sm font-semibold">{{ $exam->duration_minutes }} Menit</span>
                            </div>
                            <div class="flex items-center gap-2 text-white/90">
                                <i class="fa-solid fa-trophy text-xl"></i>
                                <span class="text-sm font-semibold">{{ $exam->passing_score }}% Passing Score</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="group bg-white rounded-3xl shadow-xl p-5 border border-gray-100 hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl blur-md opacity-30 group-hover:opacity-50 transition-opacity"></div>
                        <div class="relative inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl shadow-lg">
                            <i class="fa-solid fa-clock text-2xl text-white"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500 font-semibold mb-1 truncate">Durasi</p>
                        <p class="text-2xl font-extrabold text-gray-800">{{ $exam->duration_minutes }} <span class="text-sm font-normal text-gray-500">menit</span></p>
                    </div>
                </div>
            </div>
            
            <div class="group bg-white rounded-3xl shadow-xl p-5 border border-gray-100 hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-2xl blur-md opacity-30 group-hover:opacity-50 transition-opacity"></div>
                        <div class="relative inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-2xl shadow-lg">
                            <i class="fa-solid fa-circle-question text-2xl text-white"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500 font-semibold mb-1 truncate">Total Soal</p>
                        <p class="text-2xl font-extrabold text-gray-800">{{ $exam->total_questions }} <span class="text-sm font-normal text-gray-500">soal</span></p>
                    </div>
                </div>
            </div>
            
            <div class="group bg-white rounded-3xl shadow-xl p-5 border border-gray-100 hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-2xl blur-md opacity-30 group-hover:opacity-50 transition-opacity"></div>
                        <div class="relative inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-2xl shadow-lg">
                            <i class="fa-solid fa-trophy text-2xl text-white"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500 font-semibold mb-1 truncate">Passing Score</p>
                        <p class="text-2xl font-extrabold text-gray-800">{{ $exam->passing_score }}<span class="text-sm font-normal text-gray-500">%</span></p>
                    </div>
                </div>
            </div>
            
            <div class="group bg-white rounded-3xl shadow-xl p-5 border border-gray-100 hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl blur-md opacity-30 group-hover:opacity-50 transition-opacity"></div>
                        <div class="relative inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg">
                            <i class="fa-solid fa-gauge-high text-2xl text-white"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500 font-semibold mb-1 truncate">Difficulty</p>
                        <p class="text-2xl font-extrabold text-gray-800 capitalize">{{ $exam->difficulty }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Subjects with Enhanced Design -->
                <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100 hover:shadow-2xl transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl blur-md opacity-30"></div>
                            <div class="relative p-3 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl shadow-lg">
                                <i class="fa-solid fa-book text-white text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-xl">Mata Pelajaran</h3>
                            <p class="text-sm text-gray-500">Materi yang akan diujikan</p>
                        </div>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        @foreach(json_decode($exam->subjects, true) as $subj)
                            @php
                                $badgeColor = match($subj) {
                                    'Matematika' => 'from-blue-400 to-blue-500',
                                    'IPA' => 'from-cyan-400 to-cyan-500',
                                    'Fisika' => 'from-indigo-400 to-indigo-500',
                                    'Kimia' => 'from-blue-400 to-blue-600',
                                    'Biologi' => 'from-blue-500 to-blue-600',
                                    'Bahasa Indonesia' => 'from-cyan-500 to-cyan-600',
                                    'Bahasa Inggris' => 'from-indigo-500 to-indigo-600',
                                    'Ekonomi' => 'from-blue-400 to-cyan-500',
                                    'Geografi' => 'from-cyan-400 to-blue-500',
                                    'Sejarah' => 'from-indigo-400 to-blue-500',
                                    'Sosiologi' => 'from-blue-400 to-indigo-500',
                                    default => 'from-gray-400 to-gray-500',
                                };
                            @endphp
                            <div class="group relative">
                                <div class="absolute inset-0 bg-gradient-to-br {{ $badgeColor }} rounded-full blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
                                <span class="relative px-5 py-2.5 bg-gradient-to-br {{ $badgeColor }} backdrop-blur-sm text-white text-sm font-bold rounded-full border border-white/40 shadow-lg inline-block">
                                    <i class="fa-solid fa-star text-xs mr-2"></i>{{ $subj }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Exam Info with Timeline -->
                <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100 hover:shadow-2xl transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl blur-md opacity-30"></div>
                            <div class="relative p-3 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl shadow-lg">
                                <i class="fa-solid fa-circle-info text-white text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-xl">Informasi Exam</h3>
                            <p class="text-sm text-gray-500">Detail pelaksanaan ujian</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Timeline Style -->
                        <div class="relative pl-8 border-l-4 border-blue-200">
                            <div class="absolute -left-3 top-0 w-6 h-6 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full border-4 border-white shadow-lg"></div>
                            <div class="bg-gradient-to-r from-blue-50 to-transparent p-4 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-calendar-check text-[#0071BC] text-2xl"></i>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Tanggal Mulai</p>
                                        <p class="text-lg font-bold text-gray-800">{{ $exam->start_date->format('d F Y') }}</p>
                                        <p class="text-sm text-gray-600">{{ $exam->start_date->format('H:i') }} WIB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative pl-8 border-l-4 border-cyan-200">
                            <div class="absolute -left-3 top-0 w-6 h-6 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-full border-4 border-white shadow-lg"></div>
                            <div class="bg-gradient-to-r from-cyan-50 to-transparent p-4 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-calendar-xmark text-cyan-600 text-2xl"></i>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Tanggal Berakhir</p>
                                        <p class="text-lg font-bold text-gray-800">{{ $exam->end_date->format('d F Y') }}</p>
                                        <p class="text-sm text-gray-600">{{ $exam->end_date->format('H:i') }} WIB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative pl-8 border-l-4 border-indigo-200">
                            <div class="absolute -left-3 top-0 w-6 h-6 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-full border-4 border-white shadow-lg"></div>
                            <div class="bg-gradient-to-r from-indigo-50 to-transparent p-4 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-shuffle text-indigo-600 text-2xl"></i>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Pengacakan Soal</p>
                                        <p class="text-lg font-bold text-gray-800">
                                            @if($exam->randomize_questions)
                                                <span class="text-green-600"><i class="fa-solid fa-check-circle mr-2"></i>Diacak</span>
                                            @else
                                                <span class="text-gray-600"><i class="fa-solid fa-lock mr-2"></i>Tetap</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-600">Urutan soal akan {{ $exam->randomize_questions ? 'diacak' : 'tetap' }} saat ujian</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Exam Tips -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl shadow-xl p-8 border-2 border-blue-200">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="p-3 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl shadow-lg">
                            <i class="fa-solid fa-lightbulb text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-xl mb-2">Tips Mengerjakan</h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-blue-500 mt-1"></i>
                                    <span>Pastikan koneksi internet stabil sebelum memulai ujian</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-blue-500 mt-1"></i>
                                    <span>Siapkan waktu yang cukup ({{ $exam->duration_minutes }} menit)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-blue-500 mt-1"></i>
                                    <span>Baca setiap soal dengan teliti sebelum menjawab</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-blue-500 mt-1"></i>
                                    <span>Kerjakan soal yang mudah terlebih dahulu</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sticky Sidebar -->
            <div class="space-y-6">
                <!-- Previous Attempts -->
                @if($previousAttempts->count() > 0)
                    <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100 hover:shadow-2xl transition-shadow">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-3 bg-gradient-to-br from-green-400 to-green-500 rounded-xl shadow-lg">
                                <i class="fa-solid fa-clock-rotate-left text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Riwayat Attempt</h3>
                                <p class="text-sm text-gray-500">{{ $previousAttempts->count() }}x Percobaan</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            @foreach($previousAttempts as $attempt)
                                <div class="group p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl hover:border-green-400 hover:shadow-lg transition-all">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                {{ $loop->iteration }}
                                            </div>
                                            <span class="text-sm font-bold text-green-800">Attempt</span>
                                        </div>
                                        <div class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-bold rounded-full shadow-lg">
                                            Score: {{ $attempt->score }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-green-600 flex items-center gap-2">
                                        <i class="fa-solid fa-calendar"></i>
                                        <span>{{ $attempt->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Enhanced Action Card -->
                <div class="relative bg-gradient-to-br from-green-400 via-green-500 to-green-600 rounded-3xl shadow-2xl p-8 text-white overflow-hidden">
                    <!-- Animated Background -->
                    <div class="absolute inset-0 opacity-20">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full blur-2xl animate-pulse"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s;"></div>
                    </div>
                    
                    <!-- Floating Icons -->
                    <div class="absolute inset-0 overflow-hidden">
                        <i class="fa-solid fa-rocket absolute top-4 right-4 text-4xl text-white/20 animate-bounce"></i>
                        <i class="fa-solid fa-star absolute bottom-4 left-4 text-3xl text-white/20 animate-bounce" style="animation-delay: 0.3s;"></i>
                        <i class="fa-solid fa-trophy absolute top-1/2 left-2 text-3xl text-white/20 animate-bounce" style="animation-delay: 0.6s;"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="mb-6">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/30 backdrop-blur-sm rounded-3xl mb-4 shadow-xl">
                                <i class="fa-solid fa-rocket text-4xl"></i>
                            </div>
                            <h3 class="font-extrabold text-2xl mb-2">Siap Mengerjakan?</h3>
                            <p class="text-sm text-green-100">Pastikan kamu sudah siap dengan waktu {{ $exam->duration_minutes }} menit</p>
                        </div>
                        
                        <!-- Progress Indicator -->
                        <div class="mb-6 p-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold">Target Score</span>
                                <span class="text-xs font-bold">{{ $exam->passing_score }}%</span>
                            </div>
                            <div class="w-full bg-white/30 rounded-full h-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-yellow-400 to-green-400 h-full rounded-full" style="width: {{ $exam->passing_score }}%"></div>
                            </div>
                        </div>
                        
                        @if($exam->isAvailable())
                            <a href="{{ route('tka-exams.start', $exam->id) }}" class="group block w-full px-6 py-5 bg-white text-green-600 text-center rounded-2xl hover:bg-green-50 transition-all font-extrabold text-lg shadow-2xl hover:shadow-3xl hover:scale-105">
                                <i class="fa-solid fa-play mr-2 group-hover:animate-pulse"></i> 
                                <span class="group-hover:underline">Mulai TKA Sekarang</span>
                            </a>
                        @else
                            <button disabled class="block w-full px-6 py-5 bg-gray-400/50 backdrop-blur-sm text-white text-center rounded-2xl cursor-not-allowed font-bold text-lg border-2 border-white/30">
                                <i class="fa-solid fa-circle-exclamation mr-2"></i> Exam Tidak Tersedia
                            </button>
                        @endif
                        
                        <!-- Quick Info -->
                        <div class="mt-6 pt-6 border-t border-white/30">
                            <div class="flex items-center justify-center gap-4 text-xs text-green-100">
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-wifi"></i>
                                    <span>Butuh Internet</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-laptop"></i>
                                    <span>Bisa di HP/Laptop</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Need Help Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl shadow-xl p-6 border-2 border-blue-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-gradient-to-br from-blue-400 to-blue-500 rounded-lg">
                            <i class="fa-solid fa-headset text-white"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">Butuh Bantuan?</h4>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Jika mengalami kendala teknis, segera hubungi tim support.</p>
                    <a href="#" class="inline-flex items-center gap-2 text-[#0071BC] font-semibold hover:underline text-sm">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Hubungi Support</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
