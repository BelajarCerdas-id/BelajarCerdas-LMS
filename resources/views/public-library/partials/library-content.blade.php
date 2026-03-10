<div class="mb-8 rounded-3xl bg-[#0071BC] p-6 text-white shadow-lg">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-white/80">Belajar Cerdas</p>
            <h1 class="text-2xl font-bold md:text-3xl">Public Library</h1>
            <p class="mt-1 text-sm text-white/85">Semua orang dapat melihat dan mengunduh materi pembelajaran.</p>
        </div>

        <div class="flex items-center gap-3">
            @if ($isAdministrator)
                <a href="{{ route('public-library.manage') }}"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-[#0071BC]">
                    Kelola Library
                </a>
            @endif

            @if ($isAuthenticated)
                <a href="{{ route('beranda') }}" class="rounded-xl border border-white/70 px-4 py-2 text-sm font-semibold text-white">
                    Beranda
                </a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl border border-white/70 px-4 py-2 text-sm font-semibold text-white">
                    Masuk
                </a>
            @endif
        </div>
    </div>
</div>

<form method="GET" action="{{ route('public-library.index') }}" class="mb-6">
    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="text" name="search" value="{{ $search }}"
            placeholder="Cari judul, author, mapel, atau kelas..."
            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:outline-none">
        <button type="submit"
            class="rounded-xl bg-[#0071BC] px-5 py-3 text-sm font-semibold text-white">
            Cari
        </button>
    </div>
</form>

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

@if ($items->count() === 0)
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
        Materi belum tersedia.
    </div>
@else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($items as $item)
            <article class="overflow-hidden rounded-2xl bg-white shadow">
                <img src="{{ asset($item->thumbnail_path) }}" alt="{{ $item->title }}"
                    class="h-48 w-full object-cover">

                <div class="space-y-3 p-4">
                    <h2 class="text-lg font-bold text-slate-800">{{ $item->title }}</h2>

                    <div class="space-y-1 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-700">Author:</span> {{ $item->publisher }}</p>
                        <p><span class="font-semibold text-slate-700">Mata Pelajaran:</span> {{ $item->subject }}</p>
                        <p><span class="font-semibold text-slate-700">Kelas:</span> {{ $item->class_level }}</p>
                        <p><span class="font-semibold text-slate-700">Tipe File:</span> {{ strtoupper($item->file_extension ?? '-') }}</p>
                        <p><span class="font-semibold text-slate-700">Ukuran:</span> {{ $formatFileSize($item->file_size) }}</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button"
                            data-preview-trigger
                            data-file-url="{{ asset($item->file_path) }}"
                            data-file-name="{{ $item->original_file_name }}"
                            data-file-ext="{{ strtolower((string) ($item->file_extension ?? '')) }}"
                            class="inline-flex flex-1 items-center justify-center rounded-lg border border-[#0071BC] px-3 py-2 text-sm font-semibold text-[#0071BC]">
                            Lihat File
                        </button>
                        <a href="{{ route('public-library.download', $item->id) }}"
                            class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#0071BC] px-3 py-2 text-sm font-semibold text-white">
                            Download
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $items->links() }}
    </div>
@endif

@include('public-library.partials.file-preview-modal')
