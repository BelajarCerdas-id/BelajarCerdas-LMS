<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Latihan Soal'])
<div class="relative left-0 md:left-62.5 min-h-screen w-full md:w-[calc(100%-250px)]">
    <div class="my-15 mx-7.5">
        <div class="bg-white rounded-xl shadow-md p-8 text-center">
            <div class="mb-6">
                @if($attempt->passed)
                    <i class="fa-solid fa-circle-check text-green-500 text-6xl mb-4"></i>
                    <h1 class="text-3xl font-bold text-green-600 mb-2">Selamat! Anda Lulus</h1>
                @else
                    <i class="fa-solid fa-circle-exclamation text-orange-500 text-6xl mb-4"></i>
                    <h1 class="text-3xl font-bold text-orange-600 mb-2">Belum Berhasil</h1>
                @endif
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-blue-50 p-6 rounded-xl">
                    <p class="text-sm text-gray-600 mb-2">Nilai Kamu</p>
                    <p class="text-4xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-orange-600' }}">{{ $attempt->score }}</p>
                </div>
                <div class="bg-green-50 p-6 rounded-xl">
                    <p class="text-sm text-gray-600 mb-2">Benar</p>
                    <p class="text-4xl font-bold text-green-600">{{ $attempt->correct_answers }}</p>
                </div>
                <div class="bg-red-50 p-6 rounded-xl">
                    <p class="text-sm text-gray-600 mb-2">Salah</p>
                    <p class="text-4xl font-bold text-red-600">{{ $attempt->wrong_answers }}</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-xl">
                    <p class="text-sm text-gray-600 mb-2">Tidak Dijawab</p>
                    <p class="text-4xl font-bold text-gray-600">{{ $attempt->unanswered }}</p>
                </div>
            </div>
            
            <div class="flex gap-4 justify-center">
                <a href="{{ route('practice-exams.index') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    <i class="fa-solid fa-list mr-2"></i> Latihan Lain
                </a>
                @if(!$attempt->passed && $attempt->PracticeExam->allow_retry)
                    <a href="{{ route('practice-exams.start', $attempt->practice_exam_id) }}" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                        <i class="fa-solid fa-rotate-right mr-2"></i> Coba Lagi
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
