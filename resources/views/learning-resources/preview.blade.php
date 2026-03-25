<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - {{ $resource->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Preview Modal Container -->
    <div class="fixed inset-0 z-[9999] flex items-stretch justify-center p-0 md:p-6" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/82 backdrop-blur-[6px]" onclick="window.location.href='{{ route('library.show', $resource->id) }}'"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.18),_transparent_32%),radial-gradient(circle_at_bottom_left,_rgba(59,130,246,0.14),_transparent_28%)]"></div>

        <!-- Modal Content -->
        <div class="relative z-10 flex h-[100svh] w-full max-w-7xl flex-col overflow-hidden bg-slate-50 shadow-[0_40px_120px_-52px_rgba(15,23,42,0.88)] md:h-[min(94svh,920px)] md:rounded-[32px] md:border md:border-white/70">
            
            <!-- Desktop Header -->
            <div class="hidden shrink-0 overflow-hidden border-b border-white/10 bg-[linear-gradient(135deg,_#020617_0%,_#0f172a_42%,_#0b5d8d_100%)] text-white md:block">
                <div class="relative flex flex-col gap-3 px-3 py-3 md:gap-4 md:px-6 md:py-5">
                    <div class="flex items-start justify-between gap-3 md:gap-4">
                        <div class="flex min-w-0 items-start gap-3 md:gap-4">
                            <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/12 text-white ring-1 ring-white/15 backdrop-blur md:h-11 md:w-11">
                                @if($resource->resource_type === 'library_series')
                                    <i class="fa-solid fa-book text-lg"></i>
                                @elseif($resource->resource_type === 'ppt')
                                    <i class="fa-solid fa-file-powerpoint text-lg"></i>
                                @elseif($resource->resource_type === 'lkpd')
                                    <i class="fa-solid fa-file-lines text-lg"></i>
                                @endif
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-100">
                                        Learning Resource
                                    </span>
                                    <span class="rounded-full bg-white/12 px-2.5 py-1 text-[11px] font-semibold text-white/90 ring-1 ring-white/15">
                                        @if($resource->resource_type === 'library_series')
                                            Library Series
                                        @elseif($resource->resource_type === 'ppt')
                                            Presentation
                                        @elseif($resource->resource_type === 'lkpd')
                                            Worksheet
                                        @endif
                                    </span>
                                </div>

                                <h3 class="mt-2 max-w-full truncate text-[15px] font-semibold text-white md:text-xl">
                                    {{ $resource->title }}
                                </h3>
                                <p class="mt-1 max-w-3xl text-[11px] leading-5 text-slate-300 md:text-sm">
                                    {{ $resource->subject }} • Kelas {{ $resource->class_level }} • {{ $resource->preview_pages }} halaman preview
                                </p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="hidden rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-200 md:inline-flex">
                                Esc untuk tutup
                            </span>

                            <a href="{{ route('library.show', $resource->id) }}"
                                class="group inline-flex h-10 items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-3 text-xs font-semibold text-white backdrop-blur transition hover:bg-white/16 focus:outline-none focus:ring-2 focus:ring-white/25 md:h-11 md:px-3.5">
                                <i class="fa-solid fa-times text-white/75 transition group-hover:text-white"></i>
                                <span class="hidden sm:inline">Tutup</span>
                            </a>
                        </div>
                    </div>

                    <div class="flex gap-2 overflow-x-auto pb-1 text-[11px] text-slate-200/90">
                        <span class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-2.5 py-1 font-semibold">
                            <i class="fa-solid fa-desktop mr-1"></i>Preview responsif
                        </span>
                        <span class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-2.5 py-1 font-semibold">
                            <i class="fa-solid fa-arrows-alt mr-1"></i>Scroll untuk navigasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Mobile Header -->
            <div class="relative shrink-0 overflow-hidden border-b border-slate-200 bg-[linear-gradient(135deg,_#020617_0%,_#0f172a_55%,_#0b5d8d_100%)] text-white md:hidden">
                <div class="relative flex items-start gap-3 px-4 py-3">
                    <a href="{{ route('library.show', $resource->id) }}"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-white backdrop-blur transition hover:bg-white/16 focus:outline-none focus:ring-2 focus:ring-white/25"
                        aria-label="Tutup preview">
                        <i class="fa-solid fa-times"></i>
                    </a>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-300">Mobile Preview</p>
                                <h3 class="mt-1 truncate text-sm font-semibold text-white">{{ $resource->title }}</h3>
                            </div>

                            <span class="inline-flex shrink-0 rounded-full bg-white/12 px-2.5 py-1 text-[11px] font-semibold text-white/90 ring-1 ring-white/15">
                                @if($resource->resource_type === 'library_series')
                                    Library Series
                                @elseif($resource->resource_type === 'ppt')
                                    PPT
                                @elseif($resource->resource_type === 'lkpd')
                                    LKPD
                                @endif
                            </span>
                        </div>

                        <p class="mt-1 text-[11px] leading-5 text-slate-300">
                            {{ $resource->subject }} • Kelas {{ $resource->class_level }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="min-h-0 flex-1 overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12),_transparent_28%),linear-gradient(to_bottom,_#f8fafc,_#edf2f7)]">
                <div class="grid h-full min-h-0 md:grid-cols-[minmax(0,1fr)_280px]">
                    <!-- Preview Stage -->
                    <section class="flex min-h-0 flex-col border-b border-slate-200/80 bg-white/45 backdrop-blur md:border-b-0 md:border-r md:border-slate-200/80">
                        <div class="hidden flex-col items-start gap-2 border-b border-slate-200/80 px-3 py-3 sm:flex-row sm:items-center sm:justify-between md:flex md:px-5">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Preview Stage</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500 sm:truncate">
                                    Viewer menyesuaikan tipe file yang dibuka.
                                </p>
                            </div>

                            <span class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 shadow-sm">
                                <i class="fa-solid fa-circle-check text-green-500 mr-1"></i>Live Preview
                            </span>
                        </div>

                        <!-- PDF Viewer -->
                        <div class="min-h-0 flex-1 bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.08),_transparent_35%),linear-gradient(to_bottom,_#e2e8f0,_#cbd5e1)] p-4">
                            <div class="h-full w-full overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-lg">
                                <iframe 
                                    src="{{ route('library.preview-file', $resource->id) }}" 
                                    class="h-full w-full"
                                    frameborder="0"
                                >
                                </iframe>
                            </div>
                        </div>
                    </section>

                    <!-- Sidebar -->
                    <aside class="hidden min-h-0 overflow-auto bg-white/85 backdrop-blur-xl md:block">
                        <div class="border-b border-slate-200/80 px-4 py-4 md:px-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Detail Resource</p>

                            <div class="mt-3 grid grid-cols-1 gap-2">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Tipe</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">
                                        @if($resource->resource_type === 'library_series')
                                            Library Series
                                        @elseif($resource->resource_type === 'ppt')
                                            Presentation
                                        @elseif($resource->resource_type === 'lkpd')
                                            Worksheet
                                        @endif
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Subject</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $resource->subject }}</p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Kelas</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $resource->class_level }}</p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Preview</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $resource->preview_pages }} halaman</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-b border-slate-200/80 px-4 py-4 md:px-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Interaksi</p>

                            <div class="mt-3 space-y-2">
                                <div class="flex items-start gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700">
                                        <i class="fa-solid fa-up-down text-[10px]"></i>
                                    </span>
                                    <p class="text-xs leading-5 text-slate-600">Scroll untuk navigasi halaman di preview.</p>
                                </div>

                                <div class="flex items-start gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                                    </span>
                                    <p class="text-xs leading-5 text-slate-600">Zoom in/out untuk melihat detail.</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-4 md:px-5">
                            <div class="rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,_rgba(255,255,255,0.96),_rgba(248,250,252,0.96))] p-3 shadow-[0_20px_38px_-30px_rgba(15,23,42,0.3)]">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Aksi</p>

                                <div class="mt-3 grid grid-cols-1 gap-2">
                                    <a href="{{ route('library.download', $resource->id) }}" 
                                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,_#0284c7,_#0369a1)] px-4 py-3 text-xs font-semibold text-white shadow-[0_18px_34px_-20px_rgba(2,132,199,0.8)] transition hover:-translate-y-px hover:shadow-[0_22px_38px_-22px_rgba(2,132,199,0.8)] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/35">
                                        <i class="fa-solid fa-download h-4 w-4"></i>
                                        <span>Download</span>
                                    </a>
                                </div>
                            </div>

                            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Info</p>
                                <p class="mt-1 text-xs leading-5 text-amber-800">
                                    Preview {{ $resource->preview_pages }} halaman. Download untuk akses {{ $resource->preview_pages + 5 }} halaman lengkap.
                                </p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.location.href = '{{ route('library.show', $resource->id) }}';
            }
        });
    </script>
</body>
</html>
