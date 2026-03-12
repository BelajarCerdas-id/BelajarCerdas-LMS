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
                <h2 class="inline-flex items-center gap-2 text-lg font-bold text-slate-700">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm0 1.5h4.5v10h-4.5a.75.75 0 0 1-.75-.75v-8.5c0-.414.336-.75.75-.75Zm6 0h4.5c.414 0 .75.336.75.75v8.5a.75.75 0 0 1-.75.75h-4.5V5Z" />
                        </svg>
                    </span>
                    Daftar Materi
                </h2>

                <form method="GET" action="{{ route('public-library.manage') }}"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/85 p-2.5 shadow-[0_18px_34px_-30px_rgba(15,23,42,0.22)] md:max-w-3xl">
                    <div class="grid gap-2 md:grid-cols-[190px_minmax(0,1fr)_auto]">
                        <div class="relative">
                            <div class="relative" data-filter-picker-root>
                                <input type="hidden" name="filter" value="{{ $searchFilter }}" data-filter-input>
                                <button type="button" data-filter-trigger
                                    class="flex w-full items-center justify-between gap-3 rounded-xl border border-white bg-white py-2.5 pl-10 pr-3 text-left text-sm font-medium text-slate-700 outline-none transition hover:border-slate-200 focus:border-sky-300 focus:ring-4 focus:ring-sky-100">
                                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .984 2.02l-4.61 5.846a1.25 1.25 0 0 0-.264.774v3.11a1.25 1.25 0 0 1-1.83 1.108l-2.5-1.25a1.25 1.25 0 0 1-.69-1.118V12.14a1.25 1.25 0 0 0-.264-.774L2.766 5.52A1.25 1.25 0 0 1 2.5 4.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span data-filter-label>{{ $searchFilterOptions[$searchFilter] ?? 'Semua Field' }}</span>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.768l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.631-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Cari materi sesuai filter..."
                                class="w-full rounded-xl border border-white bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-300 focus:ring-4 focus:ring-sky-100">
                        </div>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[linear-gradient(135deg,_#0284c7,_#0369a1)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_16px_30px_-20px_rgba(2,132,199,0.72)] transition hover:-translate-y-0.5">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.768l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.631-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                            </svg>
                            Cari
                        </button>
                    </div>
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
@include('public-library.partials.search-filter-picker-modal')
