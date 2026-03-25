<x-script></x-script>

@include('components/sidebar-beranda', ['headerSideNav' => 'Library Management'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20 min-h-screen bg-gray-50">
    <div class="py-8 px-4 md:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('library.manage') }}" class="inline-flex items-center gap-2 text-[#0071BC] hover:text-blue-700 font-semibold mb-4">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Resource</h1>
            <p class="text-gray-600">Update informasi resource</p>
        </div>

        <!-- Form -->
        <form action="{{ route('library.update', $resource->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul *</label>
                        <input type="text" name="title" value="{{ old('title', $resource->title) }}" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi *</label>
                        <textarea name="description" rows="4" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">{{ old('description', $resource->description) }}</textarea>
                    </div>

                    <!-- Resource Type -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipe Resource *</label>
                        <select name="resource_type" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                            <option value="library_series" {{ old('resource_type', $resource->resource_type) == 'library_series' ? 'selected' : '' }}>Library Series</option>
                            <option value="ppt" {{ old('resource_type', $resource->resource_type) == 'ppt' ? 'selected' : '' }}>PPT</option>
                            <option value="lkpd" {{ old('resource_type', $resource->resource_type) == 'lkpd' ? 'selected' : '' }}>LKPD</option>
                        </select>
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kelas *</label>
                        <select name="kelas_id" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas_id', $resource->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Subject -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mata Pelajaran *</label>
                        <input type="text" name="subject" value="{{ old('subject', $resource->subject) }}" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                    </div>

                    <!-- Author -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Penulis</label>
                        <input type="text" name="author" value="{{ old('author', $resource->author) }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                    </div>

                    <!-- Publisher -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Penerbit</label>
                        <input type="text" name="publisher" value="{{ old('publisher', $resource->publisher) }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                    </div>

                    <!-- Current File -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">File Saat Ini</label>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>
                                <span class="text-sm text-gray-600">{{ $resource->file_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- New File Upload -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Upload File Baru (Opsional)</label>
                        <input type="file" name="file" accept=".pdf,.ppt,.pptx,.doc,.docx"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                        <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti file</p>
                    </div>

                    <!-- Thumbnail -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Upload Thumbnail Baru (Opsional)</label>
                        <input type="file" name="thumbnail" accept="image/*"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                        <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti thumbnail</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 mt-8 pt-8 border-t">
                <a href="{{ route('library.manage') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-center rounded-xl hover:bg-gray-300 transition font-bold">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold shadow-lg">
                    <i class="fa-solid fa-save mr-2"></i>Update Resource
                </button>
            </div>
        </form>
    </div>
</div>
