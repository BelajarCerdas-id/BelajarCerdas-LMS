<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Latihan Soal'])
<div class="relative left-0 md:left-62.5 min-h-screen w-full md:w-[calc(100%-250px)]">
    <div class="my-15 mx-7.5">
        <a href="{{ route('practice-exams.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>
        
        <div class="bg-white rounded-xl shadow-md p-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-full">{{ $examTypes[$exam->exam_type] }}</span>
                <span class="px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-full">{{ $exam->Mapel->mata_pelajaran }}</span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $exam->title }}</h1>
            <p class="text-gray-600 mb-6">{{ $exam->description }}</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <i class="fa-solid fa-clock text-blue-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Durasi</p>
                    <p class="font-bold">{{ $exam->duration_minutes }} menit</p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg text-center">
                    <i class="fa-solid fa-circle-question text-orange-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Soal</p>
                    <p class="font-bold">{{ $exam->total_questions }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <i class="fa-solid fa-trophy text-green-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Passing</p>
                    <p class="font-bold">{{ $exam->passing_score }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <i class="fa-solid fa-gauge text-purple-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Level</p>
                    <p class="font-bold capitalize">{{ $exam->difficulty }}</p>
                </div>
            </div>
            
            @if($bestAttempt)
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="font-semibold text-green-800">Best Score: {{ $bestAttempt->score }}</p>
                </div>
            @endif
            
            <div class="flex gap-4">
                <a href="{{ route('practice-exams.start', $exam->id) }}" class="flex-1 px-6 py-4 bg-green-600 text-white text-center rounded-lg hover:bg-green-700 font-bold text-lg">
                    <i class="fa-solid fa-play mr-2"></i> Mulai Kerjakan
                </a>
            </div>
        </div>
    </div>
</div>
