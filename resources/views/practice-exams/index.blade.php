<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'Latihan Soal'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen">
    <div class="my-15 mx-7.5 pb-8">
        <!-- Hero -->
        <div class="bg-[#0071BC] rounded-3xl shadow-xl p-8 mb-8 text-white relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative z-10 sticky top-0">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                        <i class="fa-solid fa-pen-to-square text-4xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Koleksi Latihan Soal/Ujian</h1>
                        <p class="text-white text-opacity-90 text-lg">Latihan Harian, Ujian Bab, UTS, UAS, dan Ujian Sekolah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <form action="{{ route('practice-exams.index') }}" method="GET" class="relative">
                        <input type="text" name="search" placeholder="Cari latihan soal..." value="{{ $search }}"
                            class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2 bg-[#0071BC] text-white rounded-lg hover:bg-[#005a9e]">Cari</button>
                    </form>
                </div>
                <select onchange="window.location.href=this.value" class="px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="{{ route('practice-exams.index') }}">📝 Semua Tipe</option>
                    @foreach($examTypes as $key => $label)
                        <option value="{{ route('practice-exams.index', ['type' => $key]) }}" {{ $examType == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Exams Grid -->
        @if($exams->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($exams as $exam)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="bg-gradient-to-r from-green-500 to-teal-500 p-6 text-white">
                            <div class="flex items-center gap-2 mb-3 flex-wrap">
                                <span class="px-3 py-1 bg-white bg-opacity-20 backdrop-blur-sm text-xs font-bold rounded-full">{{ $examTypes[$exam->exam_type] }}</span>
                                <span class="px-3 py-1 bg-white bg-opacity-20 backdrop-blur-sm text-xs font-bold rounded-full">{{ $exam->subject ?? $exam->Mapel->mata_pelajaran }}</span>
                            </div>
                            <h3 class="text-xl font-bold">{{ $exam->title }}</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4 line-clamp-2">{{ $exam->description }}</p>
                            <div class="grid grid-cols-4 gap-3 mb-6">
                                <div class="text-center p-3 bg-[#0071BC] bg-opacity-10 rounded-xl">
                                    <i class="fa-solid fa-clock text-[#0071BC] text-xl mb-1"></i>
                                    <p class="text-xs text-gray-500">Durasi</p>
                                    <p class="font-bold text-gray-800">{{ $exam->duration_minutes }}'</p>
                                </div>
                                <div class="text-center p-3 bg-[#0071BC] bg-opacity-10 rounded-xl">
                                    <i class="fa-solid fa-circle-question text-[#0071BC] text-xl mb-1"></i>
                                    <p class="text-xs text-gray-500">Soal</p>
                                    <p class="font-bold text-gray-800">{{ $exam->total_questions }}</p>
                                </div>
                                <div class="text-center p-3 bg-[#0071BC] bg-opacity-10 rounded-xl">
                                    <i class="fa-solid fa-trophy text-[#0071BC] text-xl mb-1"></i>
                                    <p class="text-xs text-gray-500">Passing</p>
                                    <p class="font-bold text-gray-800">{{ $exam->passing_score }}</p>
                                </div>
                                <div class="text-center p-3 bg-[#0071BC] bg-opacity-10 rounded-xl">
                                    <i class="fa-solid fa-gauge text-[#0071BC] text-xl mb-1"></i>
                                    <p class="text-xs text-gray-500">Level</p>
                                    <p class="font-bold text-gray-800 capitalize">{{ $exam->difficulty }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <a href="{{ route('practice-exams.show', $exam->id) }}" class="flex-1 px-4 py-3 bg-[#0071BC] text-white text-center rounded-xl hover:bg-[#005a9e] transition font-bold">
                                    <i class="fa-solid fa-eye mr-2"></i>Detail
                                </a>
                                <a href="{{ route('practice-exams.start', $exam->id) }}" class="flex-1 px-4 py-3 bg-green-600 text-white text-center rounded-xl hover:bg-green-700 transition font-bold">
                                    <i class="fa-solid fa-play mr-2"></i>Kerjakan
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $exams->links() }}</div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-16 text-center">
                <div class="w-32 h-32 mx-auto mb-6 bg-gradient-to-br from-green-100 to-teal-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-6xl text-green-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Belum Ada Latihan Soal</h3>
                <p class="text-gray-500">Latihan soal akan segera ditambahkan.</p>
            </div>
        @endif
    </div>
</div>
