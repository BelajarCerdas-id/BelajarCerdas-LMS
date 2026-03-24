<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => $resource->title])

<div class="relative left-0 md:left-62.5 min-h-screen w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
    <div class="my-15 mx-7.5">
        <!-- Back Button -->
        <a href="{{ route('library.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>

        <!-- Resource Detail -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-8">
                <div class="flex items-center gap-3 mb-4">
                    @if($resource->resource_type === 'library_series')
                        <span class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-full">
                            <i class="fa-solid fa-book mr-2"></i>Library Series
                        </span>
                    @elseif($resource->resource_type === 'ppt')
                        <span class="px-4 py-2 bg-orange-500 text-white text-sm font-bold rounded-full">
                            <i class="fa-solid fa-file-powerpoint mr-2"></i>PPT
                        </span>
                    @elseif($resource->resource_type === 'lkpd')
                        <span class="px-4 py-2 bg-purple-500 text-white text-sm font-bold rounded-full">
                            <i class="fa-solid fa-file-lines mr-2"></i>LKPD
                        </span>
                    @endif
                </div>

                <h1 class="text-3xl font-bold mb-4">{{ $resource->title }}</h1>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-white/70">Subject:</span>
                        <p class="font-semibold">{{ $resource->subject }}</p>
                    </div>
                    <div>
                        <span class="text-white/70">Kelas:</span>
                        <p class="font-semibold">{{ $resource->class_level }}</p>
                    </div>
                    <div>
                        <span class="text-white/70">Author:</span>
                        <p class="font-semibold">{{ $resource->author ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-white/70">Preview:</span>
                        <p class="font-semibold">{{ $resource->preview_pages }} halaman</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                @if($resource->description)
                    <div class="mb-6">
                        <h3 class="font-bold text-gray-800 mb-2">Deskripsi</h3>
                        <p class="text-gray-600">{{ $resource->description }}</p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex gap-4 mb-6">
                    <a href="{{ route('library.preview', $resource->id) }}"
                       class="flex-1 px-6 py-4 bg-blue-600 text-white text-center rounded-xl hover:bg-blue-700 transition font-bold text-lg">
                        <i class="fa-solid fa-eye mr-2"></i>Lihat Preview
                    </a>
                    <a href="{{ route('library.download', $resource->id) }}"
                       class="flex-1 px-6 py-4 bg-green-600 text-white text-center rounded-xl hover:bg-green-700 transition font-bold text-lg">
                        <i class="fa-solid fa-download mr-2"></i>Download
                    </a>
                </div>

                <!-- Info Banner -->
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-blue-600 text-xl mt-0.5"></i>
                        <div>
                            <h4 class="font-bold text-blue-800 mb-1">Preview & Download</h4>
                            <p class="text-blue-700 text-sm">
                                Klik "Lihat Preview" untuk melihat {{ $resource->preview_pages }} halaman preview,
                                atau "Download" untuk mengunduh file lengkap.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Resources -->
        @if($relatedResources->count() > 0)
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Resource Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedResources as $related)
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                            <div class="h-32 bg-gradient-to-br from-blue-500 to-blue-600"></div>
                            <div class="p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded">
                                        {{ $related->subject }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-gray-800 mb-2 line-clamp-2">
                                    {{ $related->title }}
                                </h3>
                                <a href="{{ route('library.show', $related->id) }}"
                                   class="block w-full px-3 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                                    <i class="fa-solid fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
