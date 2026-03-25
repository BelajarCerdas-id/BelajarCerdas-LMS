<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Latihan Soal'])
<div class="relative left-0 md:left-62.5 min-h-screen w-full md:w-[calc(100%-250px)]">
    <div class="my-15 mx-7.5">
        @if($remainingSeconds)
        <div class="bg-red-600 text-white p-4 rounded-xl shadow-lg mb-6 sticky top-4 z-50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold">{{ $exam->title }}</h3>
                    <p class="text-sm text-red-100">Soal {{ $currentQuestion ?? 1 }} dari {{ $attempt->Answers->count() }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-red-100">Waktu Tersisa</p>
                    <p class="text-3xl font-mono font-bold" id="timer">{{ gmdate('H:i:s', $remainingSeconds) }}</p>
                </div>
                <form action="{{ route('practice-exams.submit', $attempt->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2 bg-white text-red-600 rounded-lg hover:bg-red-50 font-bold">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Submit
                    </button>
                </form>
            </div>
        </div>
        @endif
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="mb-6">
                <p class="text-sm text-gray-500 mb-2">Pertanyaan <span class="font-bold">{{ $currentQuestion ?? 1 }}</span></p>
                <p class="text-lg font-semibold text-gray-800 mb-4">Loading question...</p>
                <div class="space-y-3">
                    <div class="p-4 border rounded-lg hover:bg-blue-50 cursor-pointer">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="answer" value="A" class="w-5 h-5">
                            <span>Option A</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-between mt-6">
                <button class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Previous
                </button>
                <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Next <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 mt-6">
            <h3 class="font-bold text-lg mb-4">Navigator Soal</h3>
            <div class="grid grid-cols-10 gap-2">
                @foreach($attempt->Answers as $index => $answer)
                    <button class="w-10 h-10 rounded-lg font-semibold {{ $answer->student_answer ? 'bg-green-500 text-white' : 'bg-gray-200' }}">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
