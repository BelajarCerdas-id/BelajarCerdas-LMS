<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'Library Management'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="py-8 px-4 md:px-8">
        <!-- Header with Stats -->
        <div class="relative bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-3xl shadow-2xl overflow-hidden mb-8">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2"></div>
            </div>
            
            <div class="relative z-10 px-6 py-8 md:px-10 md:py-10">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="p-4 bg-white/30 rounded-2xl backdrop-blur-sm shadow-lg">
                            <i class="fa-solid fa-book text-4xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">Library Management</h1>
                            <p class="text-blue-100 text-base">Kelola konten Library Series, PPT, dan LKPD</p>
                        </div>
                    </div>
                    <a href="{{ route('library.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-[#0071BC] rounded-xl hover:bg-blue-50 transition font-bold shadow-lg hover:shadow-xl hover:scale-105">
                        <i class="fa-solid fa-plus"></i>
                        <span>Upload Resource</span>
                    </a>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center border border-white/30">
                        <p class="text-3xl font-bold text-white">{{ $resources->total() }}</p>
                        <p class="text-sm text-blue-100">Total Resources</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center border border-white/30">
                        <p class="text-3xl font-bold text-white">{{ $resources->lastPage() }}</p>
                        <p class="text-sm text-blue-100">Halaman</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center border border-white/30">
                        <p class="text-3xl font-bold text-white">{{ $resources->perPage() }}</p>
                        <p class="text-sm text-blue-100">Per Halaman</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 rounded-xl mb-8 shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-500 rounded-lg">
                        <i class="fa-solid fa-circle-check text-white text-xl"></i>
                    </div>
                    <p class="text-green-800 font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-8 border border-gray-100">
            <form action="{{ route('library.manage') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-magnifying-glass text-[#0071BC] mr-2"></i>Pencarian
                    </label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari berdasarkan judul..." 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-blue-500/20 transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-filter text-[#0071BC] mr-2"></i>Tipe
                    </label>
                    <select name="type" onchange="this.form.submit()" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] focus:ring-4 focus:ring-blue-500/20 transition bg-white">
                        <option value="all">Semua Tipe</option>
                        @foreach(['library_series' => 'Library Series', 'ppt' => 'PowerPoint', 'lkpd' => 'LKPD'] as $val => $label)
                            <option value="{{ $val }}" {{ $resourceType == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold shadow-lg hover:shadow-xl">
                        <i class="fa-solid fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Resources Table -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-[#0071BC] to-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold">No</th>
                            <th class="px-6 py-4 text-left font-bold">Resource</th>
                            <th class="px-6 py-4 text-left font-bold">Tipe</th>
                            <th class="px-6 py-4 text-left font-bold">Kelas</th>
                            <th class="px-6 py-4 text-left font-bold">Mapel</th>
                            <th class="px-6 py-4 text-left font-bold">File</th>
                            <th class="px-6 py-4 text-center font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resources as $index => $resource)
                            <tr class="border-b hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition">
                                <td class="px-6 py-4 text-gray-600 font-semibold">{{ $resources->firstItem() + $index }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="p-2 bg-gradient-to-br from-[#0071BC] to-blue-600 rounded-lg">
                                            <i class="fa-solid fa-book text-white"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800">{{ $resource->title }}</p>
                                            <p class="text-sm text-gray-500 mt-1">{{ Str::limit($resource->description, 60) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeColors = [
                                            'library_series' => 'bg-blue-100 text-blue-700',
                                            'ppt' => 'bg-orange-100 text-orange-700',
                                            'lkpd' => 'bg-green-100 text-green-700'
                                        ];
                                    @endphp
                                    <span class="px-3 py-1.5 {{ $typeColors[$resource->resource_type] ?? 'bg-gray-100 text-gray-700' }} rounded-full text-sm font-bold">
                                        {{ ucfirst(str_replace('_', ' ', $resource->resource_type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">{{ $resource->Kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-600 font-medium">{{ $resource->subject ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-file-pdf text-red-500 text-xl"></i>
                                        <span class="text-sm text-gray-600 font-medium">{{ Str::limit($resource->file_name, 25) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('library.edit', $resource->id) }}" 
                                            class="p-2.5 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white rounded-xl hover:from-yellow-500 hover:to-yellow-600 transition shadow-lg hover:shadow-xl hover:scale-110" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('library.delete', $resource->id) }}" method="POST" 
                                            onsubmit="return confirm('Yakin ingin menghapus resource ini?')">
                                            @csrf
                                            <button type="submit" class="p-2.5 bg-gradient-to-br from-red-400 to-red-500 text-white rounded-xl hover:from-red-500 hover:to-red-600 transition shadow-lg hover:shadow-xl hover:scale-110" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fa-solid fa-folder-open text-5xl text-blue-400"></i>
                                        </div>
                                        <p class="text-gray-500 font-bold text-lg mb-2">Belum ada resource</p>
                                        <p class="text-gray-400 text-sm mb-4">Mulai upload konten Library Series, PPT, atau LKPD</p>
                                        <a href="{{ route('library.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold shadow-lg">
                                            <i class="fa-solid fa-plus"></i>Upload Resource Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($resources->hasPages())
                <div class="px-6 py-4 border-t bg-gradient-to-r from-gray-50 to-blue-50">
                    {{ $resources->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
