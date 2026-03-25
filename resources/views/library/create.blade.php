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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah Resource Baru</h1>
            <p class="text-gray-600">Upload konten Library Series, PPT, atau LKPD</p>
        </div>

        <!-- Form -->
        <form action="{{ route('library.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-heading text-[#0071BC] mr-2"></i>Judul *
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-align-left text-[#0071BC] mr-2"></i>Deskripsi *
                        </label>
                        <textarea name="description" rows="4" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Resource Type -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-shapes text-[#0071BC] mr-2"></i>Tipe Resource *
                        </label>
                        <select name="resource_type" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition @error('resource_type') border-red-500 @enderror">
                            <option value="">Pilih Tipe</option>
                            <option value="library_series" {{ old('resource_type') == 'library_series' ? 'selected' : '' }}>Library Series</option>
                            <option value="ppt" {{ old('resource_type') == 'ppt' ? 'selected' : '' }}>PPT (PowerPoint)</option>
                            <option value="lkpd" {{ old('resource_type') == 'lkpd' ? 'selected' : '' }}>LKPD</option>
                        </select>
                        @error('resource_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-graduation-cap text-[#0071BC] mr-2"></i>Kelas *
                        </label>
                        <select name="kelas_id" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition @error('kelas_id') border-red-500 @enderror">
                            <option value="">Pilih Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Subject -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-book text-[#0071BC] mr-2"></i>Mata Pelajaran *
                        </label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition @error('subject') border-red-500 @enderror">
                        @error('subject')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Author -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-user text-[#0071BC] mr-2"></i>Penulis
                        </label>
                        <input type="text" name="author" value="{{ old('author') }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                    </div>

                    <!-- Publisher -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-building text-[#0071BC] mr-2"></i>Penerbit
                        </label>
                        <input type="text" name="publisher" value="{{ old('publisher') }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#0071BC] transition">
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-file-upload text-[#0071BC] mr-2"></i>File *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#0071BC] transition">
                            <input type="file" name="file" id="file" required accept=".pdf,.ppt,.pptx,.doc,.docx"
                                class="hidden" onchange="updateFileName()">
                            <label for="file" class="cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up text-4xl text-[#0071BC] mb-3"></i>
                                <p class="font-bold text-gray-700">Klik untuk upload file</p>
                                <p class="text-sm text-gray-500 mt-1">PDF, PPT, PPTX, DOC, DOCX (Max 10MB)</p>
                                <p id="fileName" class="text-sm text-[#0071BC] font-semibold mt-2"></p>
                            </label>
                        </div>
                        @error('file')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Thumbnail -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-image text-[#0071BC] mr-2"></i>Thumbnail (Opsional)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#0071BC] transition">
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="hidden" onchange="updateThumbnailName()">
                            <label for="thumbnail" class="cursor-pointer">
                                <i class="fa-solid fa-image text-4xl text-[#0071BC] mb-3"></i>
                                <p class="font-bold text-gray-700">Klik untuk upload thumbnail</p>
                                <p class="text-sm text-gray-500 mt-1">JPG, PNG (Max 2MB)</p>
                                <p id="thumbnailName" class="text-sm text-[#0071BC] font-semibold mt-2"></p>
                            </label>
                        </div>
                        @error('thumbnail')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 mt-8 pt-8 border-t">
                <a href="{{ route('library.manage') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-center rounded-xl hover:bg-gray-300 transition font-bold">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-[#0071BC] to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold shadow-lg">
                    <i class="fa-solid fa-save mr-2"></i>Simpan Resource
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName() {
    const input = document.getElementById('file');
    const fileName = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        fileName.textContent = 'File terpilih: ' + input.files[0].name;
    }
}

function updateThumbnailName() {
    const input = document.getElementById('thumbnail');
    const thumbnailName = document.getElementById('thumbnailName');
    if (input.files && input.files[0]) {
        thumbnailName.textContent = 'File terpilih: ' + input.files[0].name;
    }
}
</script>
