@include('components/sidebar-beranda', ['headerSideNav' => 'Public Library Management'])

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] z-20">
    <div class="my-15 mx-4 md:mx-7.5">
        <div class="rounded-2xl bg-white p-5 shadow">
            <h1 class="text-xl font-bold text-slate-700">Upload Materi Public Library</h1>
            <p class="mt-1 text-sm text-slate-500">
                Hanya administrator yang dapat menambah, mengubah, dan menghapus materi.
            </p>
            <p class="mt-1 text-sm text-slate-500">
                Author akan otomatis diambil dari akun yang sedang login.
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('public-library.index') }}"
                    class="rounded-lg border border-[#0071BC] px-4 py-2 text-sm font-semibold text-[#0071BC]">
                    Buka Halaman Public
                </a>
            </div>

            @if (session('success'))
                <div class="mt-4 rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('public-library.store') }}" method="POST" enctype="multipart/form-data"
                class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Judul</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Mata Pelajaran</label>
                    <input type="text" name="subject" value="{{ old('subject') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Kelas</label>
                    <input type="text" name="class_level" value="{{ old('class_level') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none" required>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Deskripsi</label>
                    <textarea name="description" rows="4" maxlength="2000"
                        placeholder="Tulis ringkasan materi, tujuan belajar, atau poin penting..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Thumbnail</label>
                    <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:outline-none" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Original File</label>
                    <input type="file" name="file"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.mov,.avi,.mkv,.webm,.txt,.zip,.rar"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:outline-none" required>
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                        class="rounded-xl bg-[#0071BC] px-5 py-3 text-sm font-semibold text-white">
                        Upload Materi
                    </button>
                </div>
            </form>
        </div>

        @php
            $formatFileSize = function (?int $size): string {
                if (!$size) {
                    return '-';
                }

                $units = ['B', 'KB', 'MB', 'GB'];
                $value = $size;
                $unitIndex = 0;

                while ($value >= 1024 && $unitIndex < count($units) - 1) {
                    $value /= 1024;
                    $unitIndex++;
                }

                return number_format($value, $unitIndex === 0 ? 0 : 1) . ' ' . $units[$unitIndex];
            };
        @endphp

        <div class="mt-6 rounded-2xl bg-white p-5 shadow">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-700">Daftar Materi</h2>

                <form method="GET" action="{{ route('public-library.manage') }}" class="flex w-full max-w-md gap-2">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari judul, author, mapel, kelas, atau deskripsi..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none">
                    <button type="submit"
                        class="rounded-xl bg-[#0071BC] px-4 py-2.5 text-sm font-semibold text-white">
                        Cari
                    </button>
                </form>
            </div>

            @if ($items->count() === 0)
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                    Data materi belum tersedia.
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($items as $item)
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex gap-3">
                                    <img src="{{ asset($item->thumbnail_path) }}" alt="{{ $item->title }}"
                                        class="h-24 w-24 rounded-lg object-cover">

                                    <div class="space-y-1 text-sm text-slate-600">
                                        <p class="text-base font-bold text-slate-700">{{ $item->title }}</p>
                                        <p><span class="font-semibold">Author:</span> {{ $item->publisher }}</p>
                                        <p><span class="font-semibold">Mata Pelajaran:</span> {{ $item->subject }}</p>
                                        <p><span class="font-semibold">Kelas:</span> {{ $item->class_level }}</p>
                                        <p><span class="font-semibold">File:</span> {{ $item->original_file_name }}</p>
                                        <p><span class="font-semibold">Ukuran:</span> {{ $formatFileSize($item->file_size) }}</p>
                                        <p><span class="font-semibold">Deskripsi:</span> {{ $item->description ?: '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button"
                                        data-preview-trigger
                                        data-file-url="{{ asset($item->file_path) }}"
                                        data-file-name="{{ $item->original_file_name }}"
                                        data-file-ext="{{ strtolower((string) ($item->file_extension ?? '')) }}"
                                        class="rounded-lg border border-[#0071BC] px-3 py-2 text-xs font-semibold text-[#0071BC]">
                                        Lihat File
                                    </button>
                                    <a href="{{ route('public-library.download', $item->id) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">
                                        Download
                                    </a>

                                    <form action="{{ route('public-library.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus materi ini?')">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <details class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                                    Edit Materi
                                </summary>

                                <form action="{{ route('public-library.update', $item->id) }}" method="POST"
                                    enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                    @csrf
                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-600">Judul</label>
                                        <input type="text" name="title" value="{{ $item->title }}"
                                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none" required>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-600">Mata Pelajaran</label>
                                        <input type="text" name="subject" value="{{ $item->subject }}"
                                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none" required>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-600">Kelas</label>
                                        <input type="text" name="class_level" value="{{ $item->class_level }}"
                                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none" required>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-sm font-semibold text-slate-600">Deskripsi</label>
                                        <textarea name="description" rows="4" maxlength="2000"
                                            placeholder="Tulis ringkasan materi, tujuan belajar, atau poin penting..."
                                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none">{{ $item->description }}</textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-600">Ganti Thumbnail (Opsional)</label>
                                        <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:outline-none">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-600">Ganti File (Opsional)</label>
                                        <input type="file" name="file"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.mov,.avi,.mkv,.webm,.txt,.zip,.rar"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm focus:outline-none">
                                    </div>

                                    <div class="md:col-span-2">
                                        <button type="submit"
                                            class="rounded-lg bg-[#0071BC] px-4 py-2.5 text-sm font-semibold text-white">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@include('public-library.partials.file-preview-modal')
