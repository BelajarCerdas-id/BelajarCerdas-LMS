<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'Virtual Lab'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen">
    <div class="my-15 mx-7.5 pb-8">
        <!-- Hero -->
        <div class="bg-[#0071BC] rounded-3xl shadow-xl p-8 mb-8 text-white relative overflow-hidden sticky top-0 z-50">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="p-4 bg-white bg-opacity-20 rounded-2xl backdrop-blur-sm">
                    <i class="fa-solid fa-flask text-4xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-bold mb-2">Koleksi Virtual Lab</h1>
                    <p class="text-white text-opacity-90 text-lg">Eksperimen virtual interaktif untuk pembelajaran IPA</p>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-purple-500 rounded-2xl p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fa-solid fa-video text-purple-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 mb-2">Virtual Lab - Eksperimen Tanpa Batas</h3>
                    <p class="text-gray-600 text-sm">Lakukan eksperimen sains secara virtual dengan aman dan interaktif. Tersedia eksperimen untuk Fisika, Kimia, dan Biologi dengan video panduan lengkap.</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <form action="{{ route('virtual-labs.index') }}" method="GET" class="relative">
                        <input type="text" name="search" placeholder="Cari virtual lab..." value="{{ $search }}"
                            class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2 bg-[#0071BC] text-white rounded-lg hover:bg-[#005a9e]">Cari</button>
                    </form>
                </div>
                <select onchange="window.location.href=this.value" class="px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="{{ route('virtual-labs.index') }}">🔬 Semua Mapel</option>
                    @foreach($subjects as $subj)
                        <option value="{{ route('virtual-labs.index', ['subject' => $subj]) }}" {{ $subject == $subj ? 'selected' : '' }}>{{ $subj }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Virtual Labs Grid -->
        @if($virtualLabs->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($virtualLabs as $lab)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        <!-- Thumbnail -->
                        <div class="relative h-48 bg-gradient-to-br from-purple-500 to-pink-500 overflow-hidden">
                            <img src="{{ $lab->thumbnail_url }}" alt="{{ $lab->title }}" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/400x250/9333ea/ffffff?text={{ urlencode($lab->subject) }}'">
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1.5 bg-red-500 text-white text-xs font-bold rounded-full shadow-lg">
                                    <i class="fa-solid fa-play mr-1"></i>Video
                                </span>
                            </div>
                            <div class="absolute bottom-3 left-3">
                                <span class="px-3 py-1.5 bg-black bg-opacity-70 text-white text-xs font-bold rounded-lg">
                                    <i class="fa-solid fa-clock mr-1"></i>{{ gmdate('i:s', $lab->duration_seconds) }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">{{ $lab->subject }}</span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">Kelas {{ $lab->class_level }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">{{ $lab->title }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $lab->description }}</p>
                            
                            @if($lab->experiment_type)
                                <p class="text-xs text-gray-500 mb-3">
                                    <i class="fa-solid fa-flask mr-1"></i>{{ $lab->experiment_type }}
                                </p>
                            @endif

                            <!-- Progress -->
                            @if($lab->user_view)
                                <div class="mb-3">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-600">Progress</span>
                                        <span class="font-bold text-purple-600">{{ $lab->user_view->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full" style="width: {{ $lab->user_view->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <a href="{{ route('virtual-labs.show', $lab->id) }}" class="block w-full px-4 py-3 bg-[#0071BC] text-white text-center rounded-xl hover:bg-[#005a9e] transition font-bold shadow-lg hover:shadow-xl">
                                <i class="fa-solid fa-play mr-2"></i>Tonton Video
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $virtualLabs->links() }}</div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-16 text-center">
                <div class="w-32 h-32 mx-auto mb-6 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-flask text-6xl text-purple-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Belum Ada Virtual Lab</h3>
                <p class="text-gray-500">Virtual lab akan segera ditambahkan.</p>
            </div>
        @endif
    </div>
</div>
