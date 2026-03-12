<div class="relative mb-8 overflow-hidden rounded-3xl bg-slate-900 px-6 py-7 text-white shadow-xl md:px-8 md:py-9">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.35),_transparent_45%)]"></div>
    <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-cyan-400/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-16 bottom-0 h-44 w-44 rounded-full bg-blue-500/15 blur-3xl"></div>

    <div class="relative flex flex-wrap items-start justify-between gap-6">
        <div class="max-w-2xl">
            <p class="text-[11px] font-semibold tracking-[0.18em] text-sky-200 uppercase">Belajar Cerdas</p>
            <h1 class="mt-2 text-2xl font-bold md:text-3xl">Public Library</h1>
            <p class="mt-2 text-sm text-slate-200">
                Katalog materi belajar yang bisa diakses semua pengguna. Cari, pratinjau, lalu unduh materi sesuai kebutuhan.
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1">
                    <svg class="h-3.5 w-3.5 text-sky-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm0 1.5h4.5v10h-4.5a.75.75 0 0 1-.75-.75v-8.5c0-.414.336-.75.75-.75Zm6 0h4.5c.414 0 .75.336.75.75v8.5a.75.75 0 0 1-.75.75h-4.5V5Z" />
                    </svg>
                    {{ number_format($items->total()) }} Materi
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1">
                    <svg class="h-3.5 w-3.5 text-emerald-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm3.78 6.28a.75.75 0 0 0-1.06-1.06L9.25 10.69 7.78 9.22a.75.75 0 0 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd" />
                    </svg>
                    Akses Publik
                </span>
                @if ($search !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1">
                        <svg class="h-3.5 w-3.5 text-sky-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.768l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.631-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>
                        Pencarian: {{ $search }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1">
                        <svg class="h-3.5 w-3.5 text-amber-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .984 2.02l-4.61 5.846a1.25 1.25 0 0 0-.264.774v3.11a1.25 1.25 0 0 1-1.83 1.108l-2.5-1.25a1.25 1.25 0 0 1-.69-1.118V12.14a1.25 1.25 0 0 0-.264-.774L2.766 5.52A1.25 1.25 0 0 1 2.5 4.75Z" clip-rule="evenodd" />
                        </svg>
                        Filter: {{ $searchFilterOptions[$searchFilter] ?? 'Semua Field' }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($isAdministrator)
                <a href="{{ route('public-library.manage') }}"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-[#005f9f] transition hover:bg-slate-100">
                    Kelola Library
                </a>
            @endif

            @if ($isAuthenticated)
                <a href="{{ route('beranda') }}"
                    class="rounded-xl border border-white/40 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
                    Beranda
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="rounded-xl border border-white/40 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
                    Masuk
                </a>
            @endif
        </div>
    </div>
</div>

<form method="GET" action="{{ route('public-library.index') }}"
    class="relative mb-7 overflow-hidden rounded-[28px] border border-slate-200 bg-[linear-gradient(180deg,_rgba(255,255,255,0.98),_rgba(248,250,252,0.96))] p-4 shadow-[0_24px_48px_-34px_rgba(15,23,42,0.22)] md:p-5">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.10),_transparent_32%)]"></div>
    <div class="relative">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Search Console</p>
                <h2 class="mt-1 text-lg font-semibold text-slate-800">Temukan materi lebih cepat</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih scope pencarian, lalu masukkan kata kunci yang ingin dicari.</p>
            </div>

            @if ($search !== '')
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-sky-700">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .984 2.02l-4.61 5.846a1.25 1.25 0 0 0-.264.774v3.11a1.25 1.25 0 0 1-1.83 1.108l-2.5-1.25a1.25 1.25 0 0 1-.69-1.118V12.14a1.25 1.25 0 0 0-.264-.774L2.766 5.52A1.25 1.25 0 0 1 2.5 4.75Z" clip-rule="evenodd" />
                        </svg>
                        {{ $searchFilterOptions[$searchFilter] ?? 'Semua Field' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600">
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.768l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.631-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>
                        "{{ $search }}"
                    </span>
                </div>
            @endif
        </div>

        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-[220px_minmax(0,1fr)_auto_auto]">
            <label class="block">
                <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                    Filter
                </span>
                <div class="relative" data-filter-picker-root>
                    <input type="hidden" name="filter" value="{{ $searchFilter }}" data-filter-input>
                    <button type="button" data-filter-trigger
                        class="flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 py-3 pl-11 pr-4 text-left text-sm font-medium text-slate-700 shadow-[0_14px_28px_-24px_rgba(15,23,42,0.28)] outline-none transition hover:border-slate-300 focus:border-sky-300 focus:ring-4 focus:ring-sky-100">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .984 2.02l-4.61 5.846a1.25 1.25 0 0 0-.264.774v3.11a1.25 1.25 0 0 1-1.83 1.108l-2.5-1.25a1.25 1.25 0 0 1-.69-1.118V12.14a1.25 1.25 0 0 0-.264-.774L2.766 5.52A1.25 1.25 0 0 1 2.5 4.75Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <span data-filter-label>{{ $searchFilterOptions[$searchFilter] ?? 'Semua Field' }}</span>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                </div>
            </label>

            <label class="block">
                <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                    Kata Kunci
                </span>
                <span class="relative block">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.768l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.631-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari materi sesuai filter yang dipilih..."
                        class="w-full rounded-2xl border border-slate-200 bg-white/90 py-3 pl-11 pr-4 text-sm text-slate-700 shadow-[0_14px_28px_-24px_rgba(15,23,42,0.28)] outline-none transition placeholder:text-slate-400 focus:border-sky-300 focus:ring-4 focus:ring-sky-100">
                </span>
            </label>

            @if ($search !== '')
                <a href="{{ route('public-library.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-[0_14px_28px_-24px_rgba(15,23,42,0.18)] transition hover:border-slate-300 hover:bg-slate-50 lg:self-end">
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.78 4.22a.75.75 0 0 1 1.06 0L10 8.44l4.16-4.22a.75.75 0 0 1 1.08 1.04L11.06 9.5l4.18 4.24a.75.75 0 1 1-1.08 1.04L10 10.56l-4.16 4.22a.75.75 0 1 1-1.08-1.04L8.94 9.5 4.76 5.26a.75.75 0 0 1 .02-1.04Z" clip-rule="evenodd" />
                    </svg>
                    Reset
                </a>
            @endif

            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,_#0284c7,_#0369a1)] px-5 py-3 text-sm font-semibold text-white shadow-[0_20px_38px_-24px_rgba(2,132,199,0.7)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_42px_-26px_rgba(2,132,199,0.72)] lg:self-end">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.768l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.631-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                </svg>
                Cari Materi
            </button>
        </div>
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
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
        <p class="text-lg font-semibold text-slate-700">Materi belum ditemukan</p>
        <p class="mt-2 text-sm text-slate-500">
            @if ($search !== '')
                Coba gunakan kata kunci lain, ganti filter dropdown, atau reset pencarian.
            @else
                Materi belum tersedia saat ini.
            @endif
        </p>
        @if ($search !== '')
            <div class="mt-4">
                <a href="{{ route('public-library.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400">
                    Tampilkan Semua Materi
                </a>
            </div>
        @endif
    </div>
@else
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($items as $item)
            <article
                class="group overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_16px_34px_-24px_rgba(15,23,42,0.18)] transition duration-300 hover:-translate-y-1 hover:border-sky-200 hover:shadow-[0_26px_44px_-24px_rgba(2,132,199,0.22)]">
                <div class="relative h-[340px] overflow-hidden bg-slate-100 sm:h-[360px]">
                    <img src="{{ asset($item->thumbnail_path) }}" alt="{{ $item->title }}"
                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/82 via-slate-950/24 to-transparent"></div>

                    <div class="absolute left-4 right-4 top-4 flex items-start justify-between gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[11px] font-semibold tracking-[0.08em] text-white backdrop-blur">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm0 1.5h4.5v10h-4.5a.75.75 0 0 1-.75-.75v-8.5c0-.414.336-.75.75-.75Zm6 0h4.5c.414 0 .75.336.75.75v8.5a.75.75 0 0 1-.75.75h-4.5V5Z" />
                            </svg>
                            {{ $item->subject }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-slate-700 shadow-sm">
                            <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M5.5 2.5A2.5 2.5 0 0 0 3 5v10a2.5 2.5 0 0 0 2.5 2.5h9A2.5 2.5 0 0 0 17 15V8.914a2.5 2.5 0 0 0-.732-1.768l-3.414-3.414A2.5 2.5 0 0 0 11.086 3H5.5Zm5 .75v3a1.75 1.75 0 0 0 1.75 1.75h3.25V15A1 1 0 0 1 14.5 16h-9a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5Z" />
                            </svg>
                            {{ strtoupper($item->file_extension ?? '-') }}
                        </span>
                    </div>

                    <div class="absolute inset-x-5 bottom-5">
                        <h2 class="max-w-[90%] text-[22px] leading-tight font-bold text-white drop-shadow-sm">
                            {{ $item->title }}
                        </h2>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-semibold text-white/90">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/18 px-3 py-1 backdrop-blur">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2 2 6l8 4 6-3v4h1.5V6L10 2Zm-4.75 7.277v3.473c0 .346.178.667.47.85 2.415 1.514 5.146 1.514 7.56 0a1 1 0 0 0 .47-.85V9.277L10 11.138 5.25 9.277Z" />
                                </svg>
                                Kelas {{ $item->class_level }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-950/45 px-3 py-1 backdrop-blur">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2.5a.75.75 0 0 1 .75.75v6.19l1.72-1.72a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 0 1 1.06-1.06l1.72 1.72V3.25A.75.75 0 0 1 10 2.5Z" />
                                    <path d="M4.5 12.25a.75.75 0 0 1 .75.75v1.5c0 .414.336.75.75.75h8a.75.75 0 0 0 .75-.75V13a.75.75 0 0 1 1.5 0v1.5A2.25 2.25 0 0 1 14 16.75H6A2.25 2.25 0 0 1 3.75 14.5V13a.75.75 0 0 1 .75-.75Z" />
                                </svg>
                                {{ $formatFileSize($item->file_size) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex min-h-[190px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sm font-bold text-sky-700 ring-4 ring-sky-50/60">
                            {{ strtoupper(substr((string) $item->publisher, 0, 1)) }}
                        </span>

                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Publisher</p>
                            <p class="truncate text-sm font-semibold text-slate-700" title="{{ $item->publisher }}">
                                {{ $item->publisher }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 text-[11px] font-semibold">
                        <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm4.12 6.5h-2.094a13.16 13.16 0 0 0-.62-3.1A6.52 6.52 0 0 1 14.12 8.5ZM10 4.08c.38.49.93 1.72 1.24 4.42H8.76C9.07 5.8 9.62 4.57 10 4.08ZM8.594 5.4a13.16 13.16 0 0 0-.62 3.1H5.88A6.52 6.52 0 0 1 8.594 5.4ZM5.88 11.5h2.094c.08 1.094.292 2.142.62 3.1A6.52 6.52 0 0 1 5.88 11.5Zm2.88 0h2.48c-.31 2.7-.86 3.93-1.24 4.42-.38-.49-.93-1.72-1.24-4.42Zm2.646 3.1c.328-.958.54-2.006.62-3.1h2.094a6.52 6.52 0 0 1-2.714 3.1Z" clip-rule="evenodd" />
                            </svg>
                            Public Access
                        </span>
                    </div>

                    <div class="mt-auto grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
                        <button type="button"
                            data-preview-trigger
                            data-file-url="{{ asset($item->file_path) }}"
                            data-file-name="{{ $item->original_file_name }}"
                            data-file-ext="{{ strtolower((string) ($item->file_extension ?? '')) }}"
                            class="group/preview inline-flex h-12 items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/85 px-3 text-left transition hover:border-sky-200 hover:bg-sky-50">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm transition group-hover/preview:bg-sky-100 group-hover/preview:text-sky-700">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 4.5c4.032 0 7.398 2.503 8.68 6.033a1.25 1.25 0 0 1 0 .934C17.398 15.0 14.032 17.5 10 17.5c-4.032 0-7.398-2.503-8.68-6.033a1.25 1.25 0 0 1 0-.934C2.602 7.003 5.968 4.5 10 4.5Zm0 2a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Zm0 1.5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z" />
                                    </svg>
                                </span>
                                <span class="text-sm font-semibold text-slate-700">Preview</span>
                            </span>
                            <svg class="h-4 w-4 text-slate-300 transition group-hover/preview:translate-x-0.5 group-hover/preview:text-sky-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M11.22 4.22a.75.75 0 0 1 1.06 0l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3a.75.75 0 0 1 0-1.5h11.94l-3.72-3.72a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <a href="{{ route('public-library.download', $item->id) }}"
                            class="group/download inline-flex h-12 items-center justify-between rounded-2xl bg-gradient-to-r from-[#0071BC] via-[#008ad1] to-[#00a0ea] px-3 text-left text-white shadow-[0_16px_30px_-18px_rgba(0,113,188,0.8)] transition hover:from-[#005f9f] hover:via-[#007fba] hover:to-[#0090d4]">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/18 text-white ring-1 ring-white/20">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 2.5a.75.75 0 0 1 .75.75v7.44l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.25A.75.75 0 0 1 10 2.5Z" />
                                        <path d="M4.5 13.25a.75.75 0 0 1 .75.75v1a.75.75 0 0 0 .75.75h8a.75.75 0 0 0 .75-.75v-1a.75.75 0 0 1 1.5 0v1A2.25 2.25 0 0 1 14 17.25H6A2.25 2.25 0 0 1 3.75 15v-1a.75.75 0 0 1 .75-.75Z" />
                                    </svg>
                                </span>
                                <span class="text-sm font-semibold">Download</span>
                            </span>
                            <svg class="h-4 w-4 text-white/75 transition group-hover/download:translate-y-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 2.5a.75.75 0 0 1 .75.75v7.44l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.25A.75.75 0 0 1 10 2.5Z" />
                                <path d="M4.5 13.25a.75.75 0 0 1 .75.75v1a.75.75 0 0 0 .75.75h8a.75.75 0 0 0 .75-.75v-1a.75.75 0 0 1 1.5 0v1A2.25 2.25 0 0 1 14 17.25H6A2.25 2.25 0 0 1 3.75 15v-1a.75.75 0 0 1 .75-.75Z" />
                            </svg>
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
@include('public-library.partials.search-filter-picker-modal')
