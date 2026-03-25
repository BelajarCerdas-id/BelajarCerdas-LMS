<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'Library'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen bg-gray-50">
    <div class="py-8 px-4 md:px-8">
        <!-- Hero Section -->
        <div class="relative bg-[#0071BC] rounded-3xl shadow-2xl overflow-hidden mb-8">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2"></div>
                <!-- Decorative Icons -->
                <div class="absolute top-10 right-20 text-blue-200">
                    <i class="fa-solid fa-book-open text-8xl"></i>
                </div>
                <div class="absolute bottom-10 left-20 text-blue-200">
                    <i class="fa-solid fa-graduation-cap text-8xl"></i>
                </div>
                <div class="absolute top-1/2 left-1/4 text-blue-300">
                    <i class="fa-solid fa-pen-to-square text-6xl"></i>
                </div>
                <div class="absolute top-1/3 right-1/4 text-blue-300">
                    <i class="fa-solid fa-file-powerpoint text-6xl"></i>
                </div>
                <div class="absolute bottom-1/3 left-1/3 text-blue-300">
                    <i class="fa-solid fa-file-lines text-6xl"></i>
                </div>
            </div>
            
            <div class="relative z-10 px-6 py-12 md:px-12 md:py-16">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="p-4 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl backdrop-blur-sm shadow-lg">
                            <i class="fa-solid fa-book-open text-4xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl md:text-5xl font-bold text-white mb-2">Library</h1>
                            <p class="text-blue-100 text-base md:text-lg">Library Series, PPT, dan LKPD untuk mendukung pembelajaranmu</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="bg-gradient-to-br from-white/30 to-white/20 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/30 shadow-lg min-w-[160px]">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl mb-3 shadow-lg">
                                <i class="fa-solid fa-layer-group text-3xl text-white"></i>
                            </div>
                            <p class="text-4xl font-bold text-white mb-1">{{ $resources->total() }}</p>
                            <p class="text-sm text-blue-100 font-medium">Total Resources</p>
                        </div>
                        <div class="bg-gradient-to-br from-white/30 to-white/20 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/30 shadow-lg min-w-[160px]">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-2xl mb-3 shadow-lg">
                                <i class="fa-solid fa-shapes text-3xl text-white"></i>
                            </div>
                            <p class="text-4xl font-bold text-white mb-1">3</p>
                            <p class="text-sm text-blue-100 font-medium">Tipe Konten</p>
                        </div>
                        <div class="bg-gradient-to-br from-white/30 to-white/20 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/30 shadow-lg min-w-[160px]">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-2xl mb-3 shadow-lg">
                                <i class="fa-solid fa-download text-3xl text-white"></i>
                            </div>
                            <p class="text-4xl font-bold text-white mb-1">∞</p>
                            <p class="text-sm text-blue-100 font-medium">Download</p>
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
                        <p class="text-sm text-gray-500 mt-1">Temukan materi pembelajaran yang kamu butuhkan</p>
                    </div>
                </div>
                
                @if($resourceType !== 'all' || $kelasId || $search)
                    <a href="{{ route('library.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-semibold">
                        <i class="fa-solid fa-rotate-left"></i>Reset
                    </a>
                @endif
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div class="lg:col-span-2">
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
                            placeholder="Ketik kata kunci materi pembelajaran..." 
                            value="{{ $search }}"
                            class="w-full pl-14 pr-6 py-4 border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-[#0071BC]/20 transition font-medium bg-gray-50 hover:bg-white focus:bg-white"
                        >
                        @if($search)
                            <a href="{{ route('library.index', array_merge(request()->except('search'), ['search' => ''])) }}" 
                               class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-red-500 transition">
                                <i class="fa-solid fa-circle-xmark text-xl"></i>
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fa-solid fa-shapes text-[#0071BC] mr-2"></i>Tipe
                    </label>
                    <div class="relative">
                        <select 
                            name="type" 
                            onchange="window.location.href=this.value"
                            class="w-full pl-5 pr-10 py-4 border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-[#0071BC]/20 transition cursor-pointer font-semibold bg-white appearance-none"
                        >
                            <option value="{{ route('library.index') }}">📚 Semua Tipe</option>
                            <option value="{{ route('library.index', ['type' => 'library_series']) }}" {{ $resourceType === 'library_series' ? 'selected' : '' }}>
                                📖 Library Series
                            </option>
                            <option value="{{ route('library.index', ['type' => 'ppt']) }}" {{ $resourceType === 'ppt' ? 'selected' : '' }}>
                                📊 PPT Presentasi
                            </option>
                            <option value="{{ route('library.index', ['type' => 'lkpd']) }}" {{ $resourceType === 'lkpd' ? 'selected' : '' }}>
                                📝 LKPD
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Class Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fa-solid fa-graduation-cap text-[#0071BC] mr-2"></i>Kelas
                    </label>
                    <div class="relative">
                        <select 
                            name="kelas_id" 
                            onchange="window.location.href=this.value"
                            class="w-full pl-5 pr-10 py-4 border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-[#0071BC]/20 transition cursor-pointer font-semibold bg-white appearance-none"
                        >
                            <option value="{{ route('library.index') }}">🎓 Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ route('library.index', ['kelas_id' => $kelas->id]) }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                    📚 Kelas {{ $kelas->kelas }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Filters -->
            @if($resourceType !== 'all' || $kelasId || $search)
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-sm font-semibold text-gray-500">
                            <i class="fa-solid fa-tags mr-1"></i>Filter aktif:
                        </span>
                        @if($resourceType !== 'all')
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fa-solid fa-filter"></i> {{ ucfirst($resourceType) }}
                                <a href="{{ route('library.index') }}" class="hover:text-white/80 transition">
                                    <i class="fa-solid fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if($kelasId)
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fa-solid fa-graduation-cap"></i> Kelas {{ request()->input('kelas_id') }}
                                <a href="{{ route('library.index') }}" class="hover:text-white/80 transition">
                                    <i class="fa-solid fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if($search)
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fa-solid fa-magnifying-glass"></i> "{{ $search }}"
                                <a href="{{ route('library.index') }}" class="hover:text-white/80 transition">
                                    <i class="fa-solid fa-times"></i>
                                </a>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Resources Grid -->
        @if($resources->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($resources as $resource)
                    <div class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <!-- Thumbnail -->
                        <div class="relative h-48 overflow-hidden bg-[#0071BC]">
                            <!-- Type Badge -->
                            <div class="absolute top-3 right-3 z-10">
                                @if($resource->resource_type === 'library_series')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                        <i class="fa-solid fa-book"></i> E-book
                                    </span>
                                @elseif($resource->resource_type === 'ppt')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
                                        <i class="fa-solid fa-file-powerpoint"></i> PPT
                                    </span>
                                @elseif($resource->resource_type === 'lkpd')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-500 text-white text-xs font-bold rounded-full shadow-lg">
                                        <i class="fa-solid fa-file-lines"></i> LKPD
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Icon -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fa-solid 
                                    @if($resource->resource_type === 'library_series') fa-book
                                    @elseif($resource->resource_type === 'ppt') fa-file-powerpoint
                                    @elseif($resource->resource_type === 'lkpd') fa-file-lines
                                    @endif 
                                    text-7xl text-white opacity-20 group-hover:opacity-30 transition-all duration-300 transform group-hover:scale-110"
                                ></i>
                            </div>
                            
                            <!-- Preview Badge -->
                            <div class="absolute bottom-3 left-3 z-10">
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white bg-opacity-95 text-gray-700 text-xs font-bold rounded-lg shadow-lg">
                                    <i class="fa-solid fa-eye"></i> {{ $resource->preview_pages }} halaman
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                    <i class="fa-solid fa-book-open"></i> {{ $resource->subject }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">
                                    <i class="fa-solid fa-graduation-cap"></i> Kelas {{ $resource->class_level }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2 group-hover:text-[#0071BC] transition-colors">
                                {{ $resource->title }}
                            </h3>

                            <!-- Author -->
                            <p class="text-sm text-gray-500 mb-4">
                                <i class="fa-solid fa-user-pen mr-1"></i>
                                {{ $resource->author ?? $resource->publisher ?? 'Anonymous' }}
                            </p>

                            <!-- Description -->
                            @if($resource->description)
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                    {{ $resource->description }}
                                </p>
                            @endif

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button onclick="openPreviewModal({{ $resource->id }}, '{{ addslashes($resource->title) }}', '{{ $resource->subject }}', '{{ $resource->class_level }}', {{ $resource->preview_pages }})"
                                   class="flex-1 px-4 py-3 bg-[#0071BC] text-white text-center rounded-xl hover:bg-[#005a9e] transition font-bold text-sm shadow-lg hover:shadow-xl">
                                    <i class="fa-solid fa-eye mr-1"></i> Preview
                                </button>
                                <a href="{{ route('library.download', $resource->id) }}"
                                   class="px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white text-center rounded-xl hover:from-green-700 hover:to-green-800 transition font-bold text-sm shadow-lg hover:shadow-xl">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
                {{ $resources->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-16 text-center">
                <div class="w-40 h-40 mx-auto mb-6 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-book-open text-7xl text-[#0071BC]"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 mb-3">
                    <i class="fa-solid fa-circle-exclamation text-[#0071BC] mr-2"></i>
                    Belum Ada Learning Resources
                </h3>
                <p class="text-gray-500 mb-6 text-lg">
                    <i class="fa-solid fa-clock mr-1"></i>Learning resources akan segera ditambahkan. Stay tuned!
                </p>
                <a href="{{ route('library.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-[#0071BC] text-white rounded-xl hover:bg-[#005a9e] transition font-bold shadow-lg">
                    <i class="fa-solid fa-rotate-right"></i>Refresh
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-md" onclick="closePreviewModal()"></div>

    <!-- Modal Content -->
    <div class="relative z-10 flex h-[100svh] w-full max-w-7xl flex-col overflow-hidden bg-white md:h-[min(94svh,920px)] md:rounded-[32px] md:border-2 md:border-gray-200 mx-auto shadow-2xl">

        <!-- Header -->
        <div class="shrink-0 overflow-hidden bg-gradient-to-r from-[#0071BC] via-blue-600 to-[#0071BC] text-white">
            <div class="flex items-start justify-between gap-3 px-4 py-4 md:px-6 md:py-5">
                <div class="flex items-start gap-3">
                    <div class="p-3 bg-blue-500 bg-opacity-30 rounded-2xl backdrop-blur-sm shadow-lg border border-blue-400/30">
                        <i id="modalIcon" class="fa-solid fa-book text-xl"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-500 bg-opacity-30 backdrop-blur-sm rounded-full text-[10px] font-semibold uppercase tracking-[0.18em] border border-blue-400/20">
                                <i class="fa-solid fa-eye"></i> Preview
                            </span>
                            <span id="modalTypeBadge" class="px-3 py-1 bg-blue-500 bg-opacity-30 backdrop-blur-sm rounded-full text-[11px] font-semibold border border-blue-400/20">
                                Library Series
                            </span>
                        </div>
                        <h3 id="modalTitle" class="mt-2 text-base md:text-lg font-bold truncate max-w-md">
                            Title
                        </h3>
                        <p id="modalSubtitle" class="text-xs text-blue-100 mt-1">
                            Subject • Class • Pages
                        </p>
                    </div>
                </div>

                <button onclick="closePreviewModal()"
                    class="group inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-red-500 bg-opacity-30 backdrop-blur-sm border border-red-400/30 text-white transition hover:bg-red-500 hover:bg-opacity-50 focus:outline-none focus:ring-2 focus:ring-white/50">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="min-h-0 flex-1 overflow-hidden bg-gray-50">
            <div class="grid h-full min-h-0 md:grid-cols-[minmax(0,1fr)_300px]">
                <!-- Preview Stage -->
                <section class="flex min-h-0 flex-col bg-white">
                    <div class="min-h-0 flex-1 p-4 bg-gradient-to-br from-gray-50 to-gray-100">
                        <div class="h-full w-full overflow-hidden rounded-2xl border-2 border-gray-200 bg-white shadow-2xl">
                            <iframe id="previewIframe" src="" class="h-full w-full" frameborder="0"></iframe>
                        </div>
                    </div>
                </section>

                <!-- Sidebar -->
                <aside class="hidden min-h-0 overflow-auto bg-white border-l border-gray-200 md:block">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-xl shadow-lg">
                                <i class="fa-solid fa-circle-info text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Detail Resource</p>
                                <p class="text-xs text-gray-500">Informasi lengkap</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div class="group p-4 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 hover:border-[#0071BC] hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <i class="fa-solid fa-shapes text-[#0071BC]"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</p>
                                        <p id="sidebarType" class="text-sm font-bold text-gray-800">-</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group p-4 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 hover:border-[#0071BC] hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <i class="fa-solid fa-book-open text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</p>
                                        <p id="sidebarSubject" class="text-sm font-bold text-gray-800">-</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group p-4 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 hover:border-[#0071BC] hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <i class="fa-solid fa-graduation-cap text-purple-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</p>
                                        <p id="sidebarClass" class="text-sm font-bold text-gray-800">-</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group p-4 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 hover:border-[#0071BC] hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-orange-100 rounded-lg">
                                        <i class="fa-solid fa-file-lines text-orange-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Preview</p>
                                        <p id="sidebarPages" class="text-sm font-bold text-gray-800">- halaman</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 bg-gradient-to-br from-gray-50 to-white">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg">
                                <i class="fa-solid fa-bolt text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Aksi Cepat</p>
                                <p class="text-xs text-gray-500">Download file</p>
                            </div>
                        </div>
                        
                        <a id="downloadLink" href="#" 
                           class="group inline-flex items-center justify-center gap-2 w-full px-6 py-4 bg-gradient-to-r from-[#0071BC] to-blue-600 hover:from-[#005a9e] hover:to-blue-700 text-white text-sm font-bold rounded-2xl shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-[#0071BC]/30">
                            <i class="fa-solid fa-download text-lg group-hover:rotate-12 transition-transform"></i>
                            <span>Download Full Version</span>
                        </a>
                    </div>

                    <div class="p-6">
                        <div class="rounded-2xl border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-yellow-50 p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-amber-100 rounded-lg flex-shrink-0">
                                    <i class="fa-solid fa-lightbulb text-amber-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-amber-800 uppercase tracking-wide mb-1">Info Preview</p>
                                    <p class="text-xs text-amber-700 leading-relaxed">
                                        Anda sedang melihat preview <strong class="font-bold">3</strong> halaman. 
                                        Download untuk mengakses file lengkap dengan semua konten.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

<script>
function openPreviewModal(id, title, subject, classLevel, pages) {
    // Set modal content
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalSubtitle').textContent = subject + ' • Kelas ' + classLevel + ' • ' + pages + ' halaman preview';
    document.getElementById('sidebarType').textContent = 'Resource';
    document.getElementById('sidebarSubject').textContent = subject;
    document.getElementById('sidebarClass').textContent = 'Kelas ' + classLevel;
    document.getElementById('sidebarPages').textContent = pages + ' halaman';
    document.getElementById('downloadLink').href = '/library/' + id + '/download';

    // Set iframe src
    document.getElementById('previewIframe').src = '/library/' + id + '/preview-file';

    // Show modal
    document.getElementById('previewModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('previewIframe').src = '';
}

// Close on Esc key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreviewModal();
    }
});
</script>
