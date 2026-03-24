<x-script></x-script>
@include('components/sidebar-beranda', ['headerSideNav' => 'Virtual Lab'])
<div class="relative left-0 md:left-62.5 min-h-screen w-full md:w-[calc(100%-250px)]">
    <div class="my-15 mx-7.5">
        <a href="{{ route('virtual-labs.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content - Video -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <!-- Video Player -->
                    <div class="bg-black aspect-video">
                        <video id="virtualLabVideo" class="w-full h-full" controls preload="metadata" poster="{{ $virtualLab->thumbnail_url }}">
                            <source src="{{ $virtualLab->video_url }}" type="video/mp4">
                            Browser Anda tidak mendukung tag video.
                        </video>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 font-semibold rounded-full">{{ $virtualLab->subject }}</span>
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 font-semibold rounded-full">{{ $virtualLab->experiment_type }}</span>
                        </div>
                        
                        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $virtualLab->title }}</h1>
                        <p class="text-gray-600 mb-6">{{ $virtualLab->description }}</p>
                        
                        <!-- Video Info -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <i class="fa-solid fa-clock text-blue-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Durasi</p>
                                <p class="font-bold">{{ $virtualLab->formatted_duration }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <i class="fa-solid fa-eye text-green-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Preview</p>
                                <p class="font-bold">{{ $virtualLab->preview_duration }} detik</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <i class="fa-solid fa-star text-yellow-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Rating</p>
                                <p class="font-bold">{{ number_format($averageRating ?? 0, 1) }}</p>
                            </div>
                        </div>
                        
                        <!-- Learning Objectives -->
                        @php
                            $objectives = is_array($virtualLab->learning_objectives) ? $virtualLab->learning_objectives : json_decode($virtualLab->learning_objectives, true);
                        @endphp
                        @if($objectives && is_array($objectives))
                        <div class="mb-6">
                            <h3 class="font-bold text-lg mb-3"><i class="fa-solid fa-bullseye text-blue-600"></i> Tujuan Pembelajaran</h3>
                            <ul class="space-y-2">
                                @foreach($objectives as $objective)
                                    <li class="flex items-start gap-2">
                                        <i class="fa-solid fa-check-circle text-green-500 mt-1"></i>
                                        <span class="text-gray-700">{{ $objective }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Materials -->
                        @php
                            $materials = is_array($virtualLab->materials_needed) ? $virtualLab->materials_needed : json_decode($virtualLab->materials_needed, true);
                        @endphp
                        @if($materials && is_array($materials))
                        <div class="mb-6">
                            <h3 class="font-bold text-lg mb-3"><i class="fa-solid fa-flask text-orange-600"></i> Alat dan Bahan</h3>
                            <ul class="space-y-2">
                                @foreach($materials as $material)
                                    <li class="flex items-start gap-2">
                                        <i class="fa-solid fa-circle text-orange-400 text-xs mt-2"></i>
                                        <span class="text-gray-700">{{ $material }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <!-- Safety Notes -->
                        @if($virtualLab->safety_notes)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <h4 class="font-bold text-red-800 mb-2"><i class="fa-solid fa-triangle-exclamation"></i> Catatan Keselamatan</h4>
                            <p class="text-red-700 text-sm">{{ $virtualLab->safety_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Info Card -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Lab</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Mata Pelajaran</span>
                            <p class="font-semibold">{{ $virtualLab->subject }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Kelas</span>
                            <p class="font-semibold">{{ $virtualLab->class_level }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Jenis Eksperimen</span>
                            <p class="font-semibold">{{ $virtualLab->experiment_type }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Reviews -->
                @if($virtualLab->Reviews->count() > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Review</h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($virtualLab->Reviews->take(5) as $review)
                            <div class="border-b pb-3 last:border-0">
                                <div class="flex items-center gap-1 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-sm text-gray-700">{{ $review->comment ?? 'Tanpa komentar' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $review->Student->nama_lengkap }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Related Labs -->
                @if($relatedLabs->count() > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Virtual Lab Terkait</h3>
                    <div class="space-y-3">
                        @foreach($relatedLabs->take(4) as $related)
                            <a href="{{ route('virtual-labs.show', $related->id) }}" class="block group">
                                <div class="flex gap-3">
                                    <div class="w-20 h-14 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                        <img src="{{ $related->thumbnail_url }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 line-clamp-2">{{ $related->title }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">{{ $related->subject }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Track video progress
const video = document.getElementById('virtualLabVideo');
let lastPosition = 0;

video.addEventListener('timeupdate', () => {
    lastPosition = Math.floor(video.currentTime);
});

video.addEventListener('pause', () => {
    fetch('{{ route('virtual-labs.track-progress', $virtualLab->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            watched_duration: lastPosition,
            last_position: lastPosition,
            is_completed: video.ended
        })
    });
});

video.addEventListener('ended', () => {
    fetch('{{ route('virtual-labs.track-progress', $virtualLab->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            watched_duration: Math.floor(video.duration),
            last_position: Math.floor(video.duration),
            is_completed: true
        })
    });
});
</script>
