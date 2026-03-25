<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'Simulasi TKA'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen">
    <div class="py-8 px-4 md:px-8">
        <!-- Hero Section -->
        <div class="relative bg-[#0071BC] rounded-3xl shadow-2xl overflow-hidden mb-8">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2"></div>
                <!-- Decorative Icons -->
                <div class="absolute top-10 right-20 text-blue-200">
                    <i class="fa-solid fa-graduation-cap text-8xl"></i>
                </div>
                <div class="absolute bottom-10 left-20 text-blue-200">
                    <i class="fa-solid fa-trophy text-8xl"></i>
                </div>
                <div class="absolute top-1/2 left-1/4 text-blue-300">
                    <i class="fa-solid fa-clock text-6xl"></i>
                </div>
                <div class="absolute top-1/3 right-1/4 text-blue-300">
                    <i class="fa-solid fa-circle-question text-6xl"></i>
                </div>
                <div class="absolute bottom-1/3 left-1/3 text-blue-300">
                    <i class="fa-solid fa-pen-to-square text-6xl"></i>
                </div>
            </div>

            <div class="relative z-10 px-6 py-12 md:px-12 md:py-16">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <!-- Title Section -->
                    <div class="flex items-start gap-4">
                        <div class="p-4 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl backdrop-blur-sm shadow-lg">
                            <i class="fa-solid fa-graduation-cap text-4xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl md:text-5xl font-bold text-white mb-2">Simulasi TKA</h1>
                            <p class="text-blue-100 text-base md:text-lg">Tes Kompetensi Akademik - Persiapan UTBK-SBMPTN</p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="flex gap-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center border border-white/30 shadow-lg min-w-[140px]">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl mb-2 shadow-md">
                                <i class="fa-solid fa-layer-group text-2xl text-white"></i>
                            </div>
                            <p class="text-3xl font-bold text-white mb-1">{{ $exams->total() }}</p>
                            <p class="text-xs text-blue-100 font-medium">Total Exam</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center border border-white/30 shadow-lg min-w-[140px]">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-xl mb-2 shadow-md">
                                <i class="fa-solid fa-clock text-2xl text-white"></i>
                            </div>
                            <p class="text-3xl font-bold text-white mb-1">60-150</p>
                            <p class="text-xs text-blue-100 font-medium">Menit Durasi</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center border border-white/30 shadow-lg min-w-[140px]">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-xl mb-2 shadow-md">
                                <i class="fa-solid fa-trophy text-2xl text-white"></i>
                            </div>
                            <p class="text-3xl font-bold text-white mb-1">60-75%</p>
                            <p class="text-xs text-blue-100 font-medium">Passing Score</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-8 border border-gray-100">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-2xl shadow-lg">
                        <i class="fa-solid fa-filter text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Filter & Pencarian</h2>
                        <p class="text-sm text-gray-500 mt-1">Temukan simulasi TKA yang sesuai dengan kebutuhanmu</p>
                    </div>
                </div>

                @if($search || $subject)
                    <a href="{{ route('tka-exams.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-semibold">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fa-solid fa-magnifying-glass text-[#0071BC] mr-2"></i>Pencarian
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400 text-xl group-focus-within:text-[#0071BC] transition"></i>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari simulasi TKA berdasarkan judul..."
                            class="w-full pl-14 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-blue-500/20 transition"
                        >
                    </div>
                </div>

                <!-- Subject Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fa-solid fa-book text-[#0071BC] mr-2"></i>Mata Pelajaran
                    </label>
                    <select
                        name="subject"
                        onchange="window.location.href=this.value"
                        class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-blue-500/20 transition cursor-pointer bg-white"
                    >
                        <option value="{{ route('tka-exams.index') }}">📚 Semua Mapel</option>
                        @foreach($subjects as $subj)
                            <option value="{{ route('tka-exams.index', ['subject' => $subj]) }}" {{ $subject == $subj ? 'selected' : '' }}>
                                {{ $subj }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Exams Grid -->
        @if($exams->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($exams as $exam)
                    <div class="group bg-white rounded-3xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <!-- Header with Gradient -->
                        <div class="bg-gradient-to-br from-[#0071BC] to-blue-600 p-6 text-white relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl transform translate-x-20 -translate-y-20"></div>
                            <div class="absolute left-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full blur-3xl transform -translate-x-16 translate-y-16"></div>

                            <div class="relative z-10">
                                <!-- Subject Badges -->
                                <div class="flex items-center gap-2 mb-3 flex-wrap">
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
                                        <span class="px-3 py-1.5 bg-gradient-to-br {{ $badgeColor }} backdrop-blur-sm text-white text-xs font-bold rounded-full border border-white/30 shadow-sm">
                                            {{ $subj }}
                                        </span>
                                    @endforeach
                                </div>

                                <!-- Title -->
                                <h3 class="text-xl font-bold mb-2 line-clamp-2">{{ $exam->title }}</h3>

                                <!-- Description -->
                                <p class="text-white text-opacity-90 text-sm line-clamp-2">{{ $exam->description }}</p>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-4 gap-3 mb-6">
                                <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
                                    <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl mb-2 shadow-md">
                                        <i class="fa-solid fa-clock text-white text-sm"></i>
                                    </div>
                                    <p class="text-xs text-gray-500 font-medium">Durasi</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $exam->duration_minutes }}'</p>
                                </div>
                                <div class="text-center p-3 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-2xl border border-cyan-100">
                                    <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-xl mb-2 shadow-md">
                                        <i class="fa-solid fa-circle-question text-white text-sm"></i>
                                    </div>
                                    <p class="text-xs text-gray-500 font-medium">Soal</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $exam->total_questions }}</p>
                                </div>
                                <div class="text-center p-3 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl border border-indigo-100">
                                    <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-xl mb-2 shadow-md">
                                        <i class="fa-solid fa-trophy text-white text-sm"></i>
                                    </div>
                                    <p class="text-xs text-gray-500 font-medium">Passing</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $exam->passing_score }}%</p>
                                </div>
                                <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl border border-blue-100">
                                    <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl mb-2 shadow-md">
                                        <i class="fa-solid fa-gauge-high text-white text-sm"></i>
                                    </div>
                                    <p class="text-xs text-gray-500 font-medium">Level</p>
                                    <p class="font-bold text-gray-800 text-sm capitalize">{{ $exam->difficulty }}</p>
                                </div>
                            </div>

                            <!-- User Attempt Status -->
                            @if($exam->user_attempt)
                                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl shadow-md">
                                                @if($exam->user_attempt->is_completed)
                                                    <i class="fa-solid fa-circle-check text-white text-lg"></i>
                                                @else
                                                    <i class="fa-solid fa-spinner fa-spin text-white text-lg"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-bold text-blue-800 text-sm">
                                                    @if($exam->user_attempt->is_completed)
                                                        Selesai - Score: {{ number_format($exam->user_attempt->score, 1) }}
                                                    @else
                                                        Dalam Progress
                                                    @endif
                                                </p>
                                                <p class="text-xs text-blue-600 mt-1">
                                                    @if($exam->user_attempt->is_completed)
                                                        <i class="fa-solid fa-clock mr-1"></i>{{ $exam->user_attempt->formatted_time_spent }}
                                                    @else
                                                        <i class="fa-solid fa-chart-pie mr-1"></i>{{ $exam->user_attempt->progress_percentage }}% completed
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        @if(!$exam->user_attempt->is_completed)
                                            <a href="{{ route('tka-exams.take', $exam->user_attempt->id) }}" class="px-4 py-2.5 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold text-sm shadow-lg">
                                                <i class="fa-solid fa-play mr-1"></i>Lanjut
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <a href="{{ route('tka-exams.show', $exam->id) }}"
                                   class="flex-1 px-6 py-3.5 bg-gradient-to-br from-[#0071BC] to-blue-600 text-white text-center rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold shadow-lg hover:shadow-xl hover:scale-105">
                                    <i class="fa-solid fa-eye mr-2"></i>Detail
                                </a>
                                @if($exam->isAvailable() && (!$exam->user_attempt || $exam->user_attempt->is_completed))
                                    <a href="{{ route('tka-exams.start', $exam->id) }}"
                                       class="flex-1 px-6 py-3.5 bg-gradient-to-r from-green-500 to-green-600 text-white text-center rounded-xl hover:from-green-600 hover:to-green-700 transition font-bold shadow-lg hover:shadow-xl hover:scale-105">
                                        <i class="fa-solid fa-play mr-2"></i>Mulai
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
                {{ $exams->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-3xl shadow-xl p-16 text-center border border-gray-100">
                <div class="w-40 h-40 mx-auto mb-6 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center shadow-2xl">
                    <i class="fa-solid fa-graduation-cap text-7xl text-[#0071BC]"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 mb-3">Belum Ada Simulasi TKA</h3>
                <p class="text-gray-500 mb-8 text-lg">Simulasi TKA akan segera ditambahkan. Stay tuned!</p>
                <a href="{{ route('tka-exams.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white rounded-2xl hover:from-blue-600 hover:to-blue-700 transition font-bold shadow-xl hover:shadow-2xl hover:scale-105">
                    <i class="fa-solid fa-rotate-right"></i>Refresh
                </a>
            </div>
        @endif
    </div>
</div>
