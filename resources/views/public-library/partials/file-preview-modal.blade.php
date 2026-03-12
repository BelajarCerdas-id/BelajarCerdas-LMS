<div id="libraryPreviewModal" class="fixed inset-0 z-[9999] hidden items-stretch justify-center p-0 md:p-6" role="dialog" aria-modal="true" aria-labelledby="libraryPreviewTitle">
    <div class="absolute inset-0 bg-slate-950/82 backdrop-blur-[6px]" data-preview-close></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.18),_transparent_32%),radial-gradient(circle_at_bottom_left,_rgba(59,130,246,0.14),_transparent_28%)]"></div>

    <div class="relative z-10 flex h-[100svh] w-full max-w-7xl flex-col overflow-hidden bg-slate-50 shadow-[0_40px_120px_-52px_rgba(15,23,42,0.88)] md:h-[min(94svh,920px)] md:rounded-[32px] md:border md:border-white/70">
        <div class="hidden shrink-0 overflow-hidden border-b border-white/10 bg-[linear-gradient(135deg,_#020617_0%,_#0f172a_42%,_#0b5d8d_100%)] text-white md:block">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(103,232,249,0.25),_transparent_34%)]"></div>
            <div class="pointer-events-none absolute -top-24 right-0 h-56 w-56 rounded-full bg-cyan-300/18 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-8 h-24 w-24 rounded-full bg-sky-400/18 blur-3xl"></div>

            <div class="relative flex flex-col gap-3 px-3 py-3 md:gap-4 md:px-6 md:py-5">
                <div class="flex items-start justify-between gap-3 md:gap-4">
                    <div class="flex min-w-0 items-start gap-3 md:gap-4">
                        <span id="libraryPreviewFileIcon" class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/12 text-white ring-1 ring-white/15 backdrop-blur md:h-11 md:w-11">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                                <path d="M5.5 2.5A2.5 2.5 0 0 0 3 5v10a2.5 2.5 0 0 0 2.5 2.5h9A2.5 2.5 0 0 0 17 15V8.914a2.5 2.5 0 0 0-.732-1.768l-3.414-3.414A2.5 2.5 0 0 0 11.086 3H5.5Zm5 .75v3a1.75 1.75 0 0 0 1.75 1.75h3.25V15A1 1 0 0 1 14.5 16h-9a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5Z" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="libraryPreviewWorkspaceBadge" class="inline-flex rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-100">
                                    Workspace
                                </span>
                                <span id="libraryPreviewTypeBadge" class="rounded-full bg-white/12 px-2.5 py-1 text-[11px] font-semibold text-white/90 ring-1 ring-white/15">
                                    -
                                </span>
                            </div>

                            <h3 id="libraryPreviewTitle" class="mt-2 max-w-full truncate text-[15px] font-semibold text-white md:text-xl">
                                Preview File
                            </h3>
                            <p id="libraryPreviewSubtitle" class="mt-1 max-w-3xl text-[11px] leading-5 text-slate-300 md:text-sm">
                                Pratinjau file materi pembelajaran.
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span id="libraryPreviewKeyboardHint" class="hidden rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-200 md:inline-flex">
                            Esc untuk tutup
                        </span>

                        <button type="button" data-preview-close
                            class="group inline-flex h-10 items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-3 text-xs font-semibold text-white backdrop-blur transition hover:bg-white/16 focus:outline-none focus:ring-2 focus:ring-white/25 md:h-11 md:px-3.5">
                            <span class="inline-flex h-4 w-4 items-center justify-center text-white/75 transition group-hover:text-white" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                    <path d="M5 5L15 15M15 5L5 15" stroke-linecap="round" />
                                </svg>
                            </span>
                            <span class="hidden sm:inline">Tutup</span>
                        </button>
                    </div>
                </div>

                <div class="flex gap-2 overflow-x-auto pb-1 text-[11px] text-slate-200/90">
                    <span id="libraryPreviewMetaChip" class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-2.5 py-1 font-semibold">
                        Workspace responsif
                    </span>
                    <span id="libraryPreviewInteractionChip" class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-2.5 py-1 font-semibold">
                        Scroll di mobile, drag di desktop
                    </span>
                </div>
            </div>
        </div>

        <div class="relative shrink-0 overflow-hidden border-b border-slate-200 bg-[linear-gradient(135deg,_#020617_0%,_#0f172a_55%,_#0b5d8d_100%)] text-white md:hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(103,232,249,0.24),_transparent_36%)]"></div>

            <div class="relative flex items-start gap-3 px-4 py-3">
                <button type="button" data-preview-close
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-white backdrop-blur transition hover:bg-white/16 focus:outline-none focus:ring-2 focus:ring-white/25"
                    aria-label="Tutup preview">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke-linecap="round" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-300">Mobile Preview</p>
                            <h3 id="libraryPreviewMobileTitle" class="mt-1 truncate text-sm font-semibold text-white">Preview File</h3>
                        </div>

                        <span id="libraryPreviewMobileTypeBadge" class="inline-flex shrink-0 rounded-full bg-white/12 px-2.5 py-1 text-[11px] font-semibold text-white/90 ring-1 ring-white/15">
                            -
                        </span>
                    </div>

                    <p id="libraryPreviewMobileSubtitle" class="mt-1 text-[11px] leading-5 text-slate-300">
                        Pratinjau file materi pembelajaran.
                    </p>
                </div>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12),_transparent_28%),linear-gradient(to_bottom,_#f8fafc,_#edf2f7)]">
            <div class="grid h-full min-h-0 md:grid-cols-[minmax(0,1fr)_320px]">
                <section class="flex min-h-0 flex-col border-b border-slate-200/80 bg-white/45 backdrop-blur md:border-b-0 md:border-r md:border-slate-200/80">
                    <div class="hidden flex-col items-start gap-2 border-b border-slate-200/80 px-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3 md:flex md:px-5">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Preview Stage</p>
                            <p id="libraryPreviewStageCaption" class="mt-1 text-xs leading-5 text-slate-500 sm:truncate">
                                Viewer menyesuaikan tipe file yang dibuka.
                            </p>
                        </div>

                        <span id="libraryPreviewStagePill" class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 shadow-sm">
                            Live Preview
                        </span>
                    </div>

                    <div id="libraryPreviewBody" class="min-h-0 flex-1 bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.08),_transparent_35%),linear-gradient(to_bottom,_#e2e8f0,_#cbd5e1)]"></div>
                </section>

                <aside class="hidden min-h-0 overflow-auto bg-white/85 backdrop-blur-xl md:block">
                    <div class="border-b border-slate-200/80 px-4 py-4 md:px-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Detail File</p>

                        <div class="mt-3 grid grid-cols-1 gap-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Format</p>
                                <p id="libraryPreviewMetaFormat" class="mt-1 text-sm font-semibold text-slate-800">-</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Mode</p>
                                <p id="libraryPreviewMetaMode" class="mt-1 text-sm font-semibold text-slate-800">-</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Experience</p>
                                <p id="libraryPreviewMetaExperience" class="mt-1 text-sm font-semibold text-slate-800">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200/80 px-4 py-4 md:px-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Interaksi</p>

                        <div class="mt-3 space-y-2">
                            <div class="flex items-start gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm">
                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path d="M10 2.75a.75.75 0 0 1 .75.75v9.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 0 1 1.06-1.06l2.22 2.22V3.5a.75.75 0 0 1 .75-.75Z" />
                                    </svg>
                                </span>
                                <p id="libraryPreviewInteractionPrimary" class="text-xs leading-5 text-slate-600">Scroll secara natural di mobile dan desktop.</p>
                            </div>

                            <div class="flex items-start gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm">
                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v3.69l-1.72 1.72a.75.75 0 1 0 1.06 1.06l1.94-1.94A.75.75 0 0 0 10.75 11V6.75Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <p id="libraryPreviewInteractionSecondary" class="text-xs leading-5 text-slate-600">Kontrol menyesuaikan tipe file yang sedang dibuka.</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-4 md:px-5">
                        <div class="rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,_rgba(255,255,255,0.96),_rgba(248,250,252,0.96))] p-3 shadow-[0_20px_38px_-30px_rgba(15,23,42,0.3)]">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Aksi</p>

                            <div class="mt-3 grid grid-cols-1 gap-2">
                                <a id="libraryPreviewOpenTab" href="#" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-px hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30">
                                    <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                            <path d="M12 4h4v4" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 12l8-8" stroke-linecap="round" />
                                            <path d="M16 11v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span>Buka Tab Baru</span>
                                </a>

                                <a id="libraryPreviewDownload" href="#"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,_#0284c7,_#0369a1)] px-4 py-3 text-xs font-semibold text-white shadow-[0_18px_34px_-20px_rgba(2,132,199,0.8)] transition hover:-translate-y-px hover:shadow-[0_22px_38px_-22px_rgba(2,132,199,0.8)] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/35">
                                    <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                            <path d="M10 3v9" stroke-linecap="round" />
                                            <path d="M6.5 9.5L10 13l3.5-3.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M4 15h12" stroke-linecap="round" />
                                        </svg>
                                    </span>
                                    <span>Download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div class="shrink-0 border-t border-slate-200/80 bg-white/95 px-4 pb-[calc(0.75rem+env(safe-area-inset-bottom))] pt-3 backdrop-blur-xl md:hidden">
            <div class="rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,_rgba(255,255,255,0.98),_rgba(248,250,252,0.98))] p-3 shadow-[0_20px_38px_-30px_rgba(15,23,42,0.25)]">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p id="libraryPreviewMobileMode" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Viewer</p>
                        <p id="libraryPreviewMobileMeta" class="mt-1 text-xs leading-5 text-slate-600">
                            Layout mobile dibuat fokus ke area preview.
                        </p>
                    </div>

                    <span id="libraryPreviewMobileStagePill" class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 shadow-sm">
                        Live
                    </span>
                </div>

                <p id="libraryPreviewMobileInteraction" class="mt-3 text-[11px] leading-5 text-slate-500">
                    Scroll area preview lalu gunakan action cepat di bawah untuk buka atau unduh file.
                </p>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a id="libraryPreviewMobileOpenTab" href="#" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30">
                        <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                <path d="M12 4h4v4" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M8 12l8-8" stroke-linecap="round" />
                                <path d="M16 11v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span>Tab Baru</span>
                    </a>

                    <a id="libraryPreviewMobileDownload" href="#"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,_#0284c7,_#0369a1)] px-4 py-3 text-xs font-semibold text-white shadow-[0_18px_34px_-20px_rgba(2,132,199,0.8)] transition focus:outline-none focus:ring-2 focus:ring-[#0071BC]/35">
                        <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                <path d="M10 3v9" stroke-linecap="round" />
                                <path d="M6.5 9.5L10 13l3.5-3.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M4 15h12" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span>Download</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    if (window.__libraryPreviewModalInit) {
        return;
    }
    window.__libraryPreviewModalInit = true;

    const modal = document.getElementById('libraryPreviewModal');
    if (!modal) {
        return;
    }

    const modalBody = document.getElementById('libraryPreviewBody');
    const modalTitle = document.getElementById('libraryPreviewTitle');
    const modalMobileTitle = document.getElementById('libraryPreviewMobileTitle');
    const modalSubtitle = document.getElementById('libraryPreviewSubtitle');
    const modalMobileSubtitle = document.getElementById('libraryPreviewMobileSubtitle');
    const modalTypeBadge = document.getElementById('libraryPreviewTypeBadge');
    const modalMobileTypeBadge = document.getElementById('libraryPreviewMobileTypeBadge');
    const modalFileIcon = document.getElementById('libraryPreviewFileIcon');
    const modalWorkspaceBadge = document.getElementById('libraryPreviewWorkspaceBadge');
    const modalMetaChip = document.getElementById('libraryPreviewMetaChip');
    const modalInteractionChip = document.getElementById('libraryPreviewInteractionChip');
    const modalKeyboardHint = document.getElementById('libraryPreviewKeyboardHint');
    const modalStageCaption = document.getElementById('libraryPreviewStageCaption');
    const modalStagePill = document.getElementById('libraryPreviewStagePill');
    const modalMetaFormat = document.getElementById('libraryPreviewMetaFormat');
    const modalMetaMode = document.getElementById('libraryPreviewMetaMode');
    const modalMetaExperience = document.getElementById('libraryPreviewMetaExperience');
    const modalInteractionPrimary = document.getElementById('libraryPreviewInteractionPrimary');
    const modalInteractionSecondary = document.getElementById('libraryPreviewInteractionSecondary');
    const modalMobileMode = document.getElementById('libraryPreviewMobileMode');
    const modalMobileMeta = document.getElementById('libraryPreviewMobileMeta');
    const modalMobileStagePill = document.getElementById('libraryPreviewMobileStagePill');
    const modalMobileInteraction = document.getElementById('libraryPreviewMobileInteraction');
    const modalDownload = document.getElementById('libraryPreviewDownload');
    const modalMobileDownload = document.getElementById('libraryPreviewMobileDownload');
    const modalOpenTab = document.getElementById('libraryPreviewOpenTab');
    const modalMobileOpenTab = document.getElementById('libraryPreviewMobileOpenTab');
    const closeButtons = modal.querySelectorAll('[data-preview-close]');

    const videoExtensions = new Set(['mp4', 'mov', 'avi', 'mkv', 'webm']);
    const textExtensions = new Set(['txt', 'md', 'log', 'csv', 'json']);
    const imageExtensions = new Set(['jpg', 'jpeg', 'png', 'webp']);
    const pdfExtensions = new Set(['pdf']);
    const officeExtensions = new Set(['ppt', 'pptx']);

    const pdfJsVersion = '3.11.174';
    const pdfJsScriptUrl = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/${pdfJsVersion}/pdf.min.js`;
    const pdfJsWorkerUrl = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/${pdfJsVersion}/pdf.worker.min.js`;

    let modalToken = 0;
    let activePdfControls = null;
    let activePdfCleanup = null;

    const isModalOpen = () => !modal.classList.contains('hidden');

    const resetModalBody = () => {
        modalBody.innerHTML = '';
    };

    const setActionLinks = (fileUrl, fileName) => {
        modalDownload.href = fileUrl;
        modalDownload.setAttribute('download', fileName || 'download');
        modalMobileDownload.href = fileUrl;
        modalMobileDownload.setAttribute('download', fileName || 'download');

        modalOpenTab.href = fileUrl;
        modalMobileOpenTab.href = fileUrl;
    };

    const toTitleCase = (value) => {
        const text = String(value || '').toLowerCase();
        if (!text) {
            return 'Unknown';
        }

        return text.charAt(0).toUpperCase() + text.slice(1);
    };

    const getFileTheme = (extension) => {
        const normalized = String(extension || '').toLowerCase();

        if (pdfExtensions.has(normalized)) {
            return {
                badgeClass: 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
                iconClass: 'bg-rose-500/15 text-rose-600 ring-1 ring-rose-400/20',
                iconSvg: '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M5.5 2.5A2.5 2.5 0 0 0 3 5v10a2.5 2.5 0 0 0 2.5 2.5h9A2.5 2.5 0 0 0 17 15V8.914a2.5 2.5 0 0 0-.732-1.768l-3.414-3.414A2.5 2.5 0 0 0 11.086 3H5.5Zm5 .75v3a1.75 1.75 0 0 0 1.75 1.75h3.25V15A1 1 0 0 1 14.5 16h-9a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5Z" /></svg>',
                label: 'PDF',
                workspaceLabel: 'Document Workspace',
                stageCaption: 'Navigasi halaman, zoom, dan drag-to-pan untuk dokumen PDF.',
                metaChip: 'Kontrol halaman dan zoom aktif untuk PDF.',
                interactionChip: 'Arrow key untuk halaman, drag saat zoom.',
                metaMode: 'PDF Viewer',
                metaExperience: 'Dokumen multipage dengan kontrol interaktif.',
                interactionPrimary: 'Gunakan panah kiri/kanan atau input halaman untuk berpindah.',
                interactionSecondary: 'Setelah zoom in, drag area halaman di desktop atau swipe-scroll di mobile.',
            };
        }

        if (officeExtensions.has(normalized)) {
            return {
                badgeClass: 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
                iconClass: 'bg-amber-500/15 text-amber-600 ring-1 ring-amber-400/20',
                iconSvg: '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M5 3.5A1.5 1.5 0 0 0 3.5 5v10A1.5 1.5 0 0 0 5 16.5h10A1.5 1.5 0 0 0 16.5 15V7.56a1.5 1.5 0 0 0-.44-1.06l-2.56-2.56a1.5 1.5 0 0 0-1.06-.44H5Zm5 .75v2.5c0 .69.56 1.25 1.25 1.25h2.5V15a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5h5Z" /></svg>',
                label: 'Presentation',
                workspaceLabel: 'Presentation Workspace',
                stageCaption: 'Slide deck dibuka lewat Office viewer dengan layout responsif.',
                metaChip: 'Viewer presentasi disiapkan untuk layar mobile dan desktop.',
                interactionChip: 'Scroll natural, buka tab baru untuk mode penuh.',
                metaMode: 'Office Embed',
                metaExperience: 'Preview presentasi memakai Office online viewer.',
                interactionPrimary: 'Scroll di dalam viewer untuk menjelajahi isi presentasi.',
                interactionSecondary: 'Jika file belum tersedia via HTTPS publik, gunakan Buka Tab Baru atau Download.',
            };
        }

        if (videoExtensions.has(normalized)) {
            return {
                badgeClass: 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
                iconClass: 'bg-sky-500/15 text-sky-600 ring-1 ring-sky-400/20',
                iconSvg: '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M4.5 4A2.5 2.5 0 0 0 2 6.5v7A2.5 2.5 0 0 0 4.5 16h6A2.5 2.5 0 0 0 13 13.5v-.734l3.219 2.012A1.25 1.25 0 0 0 18 13.719V6.28a1.25 1.25 0 0 0-1.781-1.06L13 7.234V6.5A2.5 2.5 0 0 0 10.5 4h-6Z" /></svg>',
                label: 'Video',
                workspaceLabel: 'Media Workspace',
                stageCaption: 'Player video responsif dengan kontrol native browser.',
                metaChip: 'Playback tetap nyaman untuk touch maupun mouse.',
                interactionChip: 'Gunakan kontrol player bawaan browser.',
                metaMode: 'Video Player',
                metaExperience: 'Preview media dengan kontrol native dan fullscreen.',
                interactionPrimary: 'Tap atau klik video untuk play, pause, volume, dan fullscreen.',
                interactionSecondary: 'Gunakan Download jika ingin menyimpan file untuk diputar offline.',
            };
        }

        if (imageExtensions.has(normalized)) {
            return {
                badgeClass: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
                iconClass: 'bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-400/20',
                iconSvg: '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path fill-rule="evenodd" d="M3 5.5A2.5 2.5 0 0 1 5.5 3h9A2.5 2.5 0 0 1 17 5.5v9a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 3 14.5v-9Zm9.38 1.63a1.13 1.13 0 1 0 0 2.26 1.13 1.13 0 0 0 0-2.26ZM5 13.7V14.5c0 .28.22.5.5.5h9a.5.5 0 0 0 .5-.5v-2.379l-2.67-2.67a.75.75 0 0 0-1.06 0L7.4 13.32l-.9-.9a.75.75 0 0 0-1.06 0L5 13.7Z" clip-rule="evenodd" /></svg>',
                label: 'Image',
                workspaceLabel: 'Image Workspace',
                stageCaption: 'Preview visual fokus dengan ruang pandang luas dan proporsional.',
                metaChip: 'Gambar ditampilkan besar tanpa memaksa layout pecah.',
                interactionChip: 'Scroll untuk area besar, drag di desktop.',
                metaMode: 'Image Viewer',
                metaExperience: 'Viewer gambar fokus dengan preview lebar.',
                interactionPrimary: 'Scroll jika ukuran gambar melebihi area viewport.',
                interactionSecondary: 'Desktop mendukung drag pada area preview yang overflow.',
            };
        }

        if (textExtensions.has(normalized)) {
            return {
                badgeClass: 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                iconClass: 'bg-slate-500/15 text-slate-600 ring-1 ring-slate-400/20',
                iconSvg: '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm0 1.5h10.5c.414 0 .75.336.75.75v8.5a.75.75 0 0 1-.75.75H4.75a.75.75 0 0 1-.75-.75v-8.5c0-.414.336-.75.75-.75Zm1.5 2.25h7v1.5h-7v-1.5Zm0 3h5v1.5h-5v-1.5Z" /></svg>',
                label: 'Text',
                workspaceLabel: 'Text Workspace',
                stageCaption: 'Mode baca teks bersih untuk file plain text dan turunannya.',
                metaChip: 'Konten teks bisa diseleksi dan dibaca tanpa browser chrome.',
                interactionChip: 'Scroll, seleksi, dan copy dengan mudah.',
                metaMode: 'Text Reader',
                metaExperience: 'Viewer teks biasa dengan statistik file sederhana.',
                interactionPrimary: 'Scroll untuk membaca konten panjang dengan nyaman.',
                interactionSecondary: 'Teks dapat diseleksi langsung saat perlu copy atau review.',
            };
        }

        return {
            badgeClass: 'bg-cyan-100 text-cyan-700 ring-1 ring-cyan-200',
            iconClass: 'bg-cyan-500/15 text-cyan-600 ring-1 ring-cyan-400/20',
            iconSvg: '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M5.5 2.5A2.5 2.5 0 0 0 3 5v10a2.5 2.5 0 0 0 2.5 2.5h9A2.5 2.5 0 0 0 17 15V8.914a2.5 2.5 0 0 0-.732-1.768l-3.414-3.414A2.5 2.5 0 0 0 11.086 3H5.5Zm5 .75v3a1.75 1.75 0 0 0 1.75 1.75h3.25V15A1 1 0 0 1 14.5 16h-9a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5Z" /></svg>',
            label: toTitleCase(normalized || 'file'),
            workspaceLabel: 'File Workspace',
            stageCaption: 'Preview dasar untuk tipe file umum.',
            metaChip: 'Preview default aktif untuk file ini.',
            interactionChip: 'Gunakan tombol aksi jika preview terbatas.',
            metaMode: 'Standard Preview',
            metaExperience: 'Mode fallback untuk file non-standar.',
            interactionPrimary: 'Buka Tab Baru jika ingin melihat dengan aplikasi browser bawaan.',
            interactionSecondary: 'Download tetap tersedia sebagai fallback utama.',
        };
    };

    const updateHeader = (fileName, extension) => {
        const theme = getFileTheme(extension);
        modalTitle.textContent = fileName || 'Preview File';
        modalMobileTitle.textContent = fileName || 'Preview File';
        modalSubtitle.textContent = fileName ? `Pratinjau ${theme.label.toLowerCase()} untuk ${fileName}` : 'Pratinjau file materi pembelajaran.';
        modalMobileSubtitle.textContent = theme.stageCaption;
        modalWorkspaceBadge.textContent = theme.workspaceLabel;
        modalTypeBadge.textContent = theme.label;
        modalMobileTypeBadge.textContent = theme.label;
        modalTypeBadge.className = `rounded-full px-2.5 py-1 text-[11px] font-semibold ${theme.badgeClass}`;
        modalMobileTypeBadge.className = `inline-flex shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ${theme.badgeClass}`;
        modalFileIcon.className = `inline-flex h-10 w-10 items-center justify-center rounded-2xl md:h-11 md:w-11 ${theme.iconClass}`;
        modalFileIcon.innerHTML = theme.iconSvg;
        modalMetaChip.textContent = theme.metaChip;
        modalInteractionChip.textContent = theme.interactionChip;
        modalKeyboardHint.textContent = pdfExtensions.has(String(extension || '').toLowerCase()) ? 'Esc, <-, ->, +, -, 0' : 'Esc untuk tutup';
        modalStageCaption.textContent = theme.stageCaption;
        modalStagePill.textContent = theme.metaMode;
        modalMetaFormat.textContent = theme.label;
        modalMetaMode.textContent = theme.metaMode;
        modalMetaExperience.textContent = theme.metaExperience;
        modalInteractionPrimary.textContent = theme.interactionPrimary;
        modalInteractionSecondary.textContent = theme.interactionSecondary;
        modalMobileMode.textContent = theme.metaMode;
        modalMobileMeta.textContent = theme.metaChip;
        modalMobileStagePill.textContent = theme.label;
        modalMobileInteraction.textContent = theme.interactionPrimary;
    };

    const resolveAbsoluteUrl = (value) => {
        try {
            return new URL(value, window.location.origin).href;
        } catch (error) {
            return '';
        }
    };

    const isPublicHttpsUrl = (value) => {
        try {
            const parsed = new URL(value);
            const hostname = parsed.hostname.toLowerCase();

            if (parsed.protocol !== 'https:') {
                return false;
            }

            if (hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1') {
                return false;
            }

            if (hostname.endsWith('.local') || hostname.endsWith('.test')) {
                return false;
            }

            return true;
        } catch (error) {
            return false;
        }
    };

    const renderInfoBox = (titleText, descriptionText, showSpinner = false) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex h-full items-center justify-center p-3 md:p-7';

        const box = document.createElement('div');
        box.className = 'w-full max-w-lg rounded-[28px] border border-slate-200/90 bg-white/92 p-6 text-center shadow-[0_28px_60px_-36px_rgba(15,23,42,0.38)] backdrop-blur-xl';

        const icon = document.createElement('div');
        icon.className = 'mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700';
        icon.innerHTML = `
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-7.25-3.25a.75.75 0 0 0-1.5 0V10c0 .414.336.75.75.75h2a.75.75 0 0 0 0-1.5H10.75V6.75Z" clip-rule="evenodd" />
            </svg>
        `;
        box.appendChild(icon);

        if (showSpinner) {
            const spinner = document.createElement('div');
            spinner.className = 'mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-slate-300 border-t-[#0071BC]';
            box.appendChild(spinner);
        }

        const title = document.createElement('p');
        title.className = 'text-sm font-semibold text-slate-700';
        title.textContent = titleText;

        const description = document.createElement('p');
        description.className = 'mt-2 text-xs text-slate-500';
        description.textContent = descriptionText;

        box.appendChild(title);
        box.appendChild(description);
        wrapper.appendChild(box);

        resetModalBody();
        modalBody.appendChild(wrapper);
    };

    const renderFramedNode = (node, frameClassName, freeMoveOptions = {}) => {
        const {
            enablePan = true,
            ...panOptions
        } = freeMoveOptions;

        const wrapper = document.createElement('div');
        wrapper.className = 'flex h-full min-h-0 items-center justify-center overflow-auto overscroll-contain bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.10),_transparent_30%),linear-gradient(to_bottom,_#f4f7fb,_#dde7f1)] p-3 md:p-7';
        wrapper.style.touchAction = 'pan-x pan-y';
        wrapper.style.webkitOverflowScrolling = 'touch';
        if (enablePan) {
            enableFreeMove(wrapper, panOptions);
        }

        node.className = frameClassName;

        resetModalBody();
        wrapper.appendChild(node);
        modalBody.appendChild(wrapper);
    };

    const enableFreeMove = (container, {
        showGrabCursor = true,
        allowTouch = true,
        interactiveSelector = 'a, button, input, textarea, select, option, video, iframe, [data-free-move-ignore]',
    } = {}) => {
        let isDragging = false;
        let activePointerId = null;
        let startX = 0;
        let startY = 0;
        let startScrollLeft = 0;
        let startScrollTop = 0;

        container.style.touchAction = allowTouch ? 'none' : 'pan-x pan-y';
        container.style.webkitOverflowScrolling = 'touch';

        if (showGrabCursor) {
            container.classList.add('cursor-grab');
        }

        const canStartFromTarget = (target) => {
            return !(target instanceof Element && target.closest(interactiveSelector));
        };

        const stopDragging = (pointerId = null) => {
            if (!isDragging) {
                return;
            }

            isDragging = false;

            if (showGrabCursor) {
                container.classList.remove('cursor-grabbing');
                container.classList.add('cursor-grab');
            }

            if (pointerId !== null && typeof container.releasePointerCapture === 'function') {
                try {
                    container.releasePointerCapture(pointerId);
                } catch (error) {
                    // Ignore release errors when the pointer is no longer active.
                }
            }
        };

        container.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            if (event.pointerType === 'touch' && !allowTouch) {
                return;
            }

            if (!canStartFromTarget(event.target)) {
                return;
            }

            const hasHorizontalOverflow = container.scrollWidth > container.clientWidth + 1;
            const hasVerticalOverflow = container.scrollHeight > container.clientHeight + 1;
            if (!hasHorizontalOverflow && !hasVerticalOverflow) {
                return;
            }

            isDragging = true;
            activePointerId = event.pointerId;
            startX = event.clientX;
            startY = event.clientY;
            startScrollLeft = container.scrollLeft;
            startScrollTop = container.scrollTop;

            if (showGrabCursor) {
                container.classList.remove('cursor-grab');
                container.classList.add('cursor-grabbing');
            }

            if (typeof container.setPointerCapture === 'function') {
                container.setPointerCapture(event.pointerId);
            }

            event.preventDefault();
        }, { passive: false });

        container.addEventListener('pointermove', (event) => {
            if (!isDragging || event.pointerId !== activePointerId) {
                return;
            }

            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            container.scrollLeft = startScrollLeft - deltaX;
            container.scrollTop = startScrollTop - deltaY;
            event.preventDefault();
        }, { passive: false });

        container.addEventListener('pointerup', (event) => {
            activePointerId = null;
            stopDragging(event.pointerId);
        });

        container.addEventListener('pointercancel', (event) => {
            activePointerId = null;
            stopDragging(event.pointerId);
        });

        container.addEventListener('lostpointercapture', (event) => {
            activePointerId = null;
            stopDragging(event.pointerId);
        });
    };

    const ensurePdfJsLoaded = async () => {
        if (window.pdfjsLib) {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = pdfJsWorkerUrl;
            return window.pdfjsLib;
        }

        if (window.__libraryPdfJsPromise) {
            return window.__libraryPdfJsPromise;
        }

        window.__libraryPdfJsPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = pdfJsScriptUrl;
            script.async = true;

            script.onload = () => {
                if (!window.pdfjsLib) {
                    reject(new Error('pdfjsLib tidak tersedia setelah script dimuat.'));
                    return;
                }

                window.pdfjsLib.GlobalWorkerOptions.workerSrc = pdfJsWorkerUrl;
                resolve(window.pdfjsLib);
            };

            script.onerror = () => {
                reject(new Error('Gagal memuat script PDF.js.'));
            };

            document.head.appendChild(script);
        });

        return window.__libraryPdfJsPromise;
    };

    const renderPdfPreview = async (fileUrl, fileName, currentToken) => {
        renderInfoBox('Memuat preview PDF...', 'Mohon tunggu sebentar.', true);

        try {
            const pdfjsLib = await ensurePdfJsLoaded();
            if (currentToken !== modalToken) {
                return;
            }

            const loadingTask = pdfjsLib.getDocument({ url: fileUrl });
            const pdfDocument = await loadingTask.promise;
            if (currentToken !== modalToken) {
                return;
            }

            resetModalBody();

            const container = document.createElement('div');
            container.className = 'flex h-full flex-col';

            const toolbar = document.createElement('div');
            toolbar.className = 'flex flex-col gap-2 border-b border-slate-200/80 bg-white/78 px-3 py-3 backdrop-blur-xl sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:px-5 sm:py-4';

            const leftControls = document.createElement('div');
            leftControls.className = 'inline-flex w-full items-center justify-between gap-1 rounded-[18px] border border-slate-200 bg-white/90 p-1.5 shadow-[0_14px_28px_-24px_rgba(15,23,42,0.45)] sm:w-auto sm:justify-start';

            const icons = {
                first: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M6 4v12" stroke-linecap="round"/><path d="M14 5l-5 5 5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                prev: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M12.5 5L7.5 10l5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                next: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M7.5 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                last: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M14 4v12" stroke-linecap="round"/><path d="M6 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                zoomOut: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M4 10h8" stroke-linecap="round"/><path d="M11 11l4 4" stroke-linecap="round"/><circle cx="9" cy="9" r="5"/></svg>',
                zoomIn: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M4 10h8" stroke-linecap="round"/><path d="M8 6v8" stroke-linecap="round"/><path d="M11 11l4 4" stroke-linecap="round"/><circle cx="9" cy="9" r="5"/></svg>',
                fit: '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M5 7V5h2M13 5h2v2M15 13v2h-2M7 15H5v-2" stroke-linecap="round" stroke-linejoin="round"/><rect x="7" y="7" width="6" height="6" rx="0.6"/></svg>',
            };

            const createButton = ({
                label = '',
                titleText = '',
                iconSvg = '',
                compact = false,
                hotkey = '',
            } = {}) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = compact
                    ? 'inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700 transition hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/25 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-white disabled:hover:text-slate-700'
                    : 'inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 transition hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/25 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-white disabled:hover:text-slate-700 sm:gap-1.5 sm:px-3';
                if (titleText) {
                    button.title = titleText;
                }

                button.innerHTML = `
                    ${iconSvg ? `<span class="inline-flex h-3.5 w-3.5 items-center justify-center">${iconSvg}</span>` : ''}
                    ${label ? `<span class="hidden sm:inline">${label}</span>` : ''}
                    ${hotkey ? `<span class="hidden rounded bg-slate-100 px-1 py-0.5 text-[10px] font-bold text-slate-500 md:inline">${hotkey}</span>` : ''}
                `;

                return button;
            };

            const firstButton = createButton({
                titleText: 'Halaman pertama',
                iconSvg: icons.first,
                compact: true,
            });
            const prevButton = createButton({
                titleText: 'Halaman sebelumnya',
                iconSvg: icons.prev,
                compact: true,
            });
            const nextButton = createButton({
                titleText: 'Halaman berikutnya',
                iconSvg: icons.next,
                compact: true,
            });
            const lastButton = createButton({
                titleText: 'Halaman terakhir',
                iconSvg: icons.last,
                compact: true,
            });

            const pageInput = document.createElement('input');
            pageInput.type = 'number';
            pageInput.min = '1';
            pageInput.value = '1';
            pageInput.title = 'Nomor halaman';
            pageInput.className = 'h-9 w-12 rounded-xl border border-slate-200 bg-white px-1 text-center text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0071BC]/25 sm:w-16 sm:px-2';

            const pageTotal = document.createElement('span');
            pageTotal.className = 'inline-flex h-9 items-center rounded-xl bg-slate-50 px-2.5 text-xs font-semibold text-slate-600';

            leftControls.appendChild(firstButton);
            leftControls.appendChild(prevButton);
            leftControls.appendChild(pageInput);
            leftControls.appendChild(pageTotal);
            leftControls.appendChild(nextButton);
            leftControls.appendChild(lastButton);

            const rightControls = document.createElement('div');
            rightControls.className = 'inline-flex w-full items-center justify-between gap-1 rounded-[18px] border border-slate-200 bg-white/90 p-1.5 shadow-[0_14px_28px_-24px_rgba(15,23,42,0.45)] sm:w-auto sm:justify-start';

            const zoomOutButton = createButton({
                label: 'Zoom -',
                titleText: 'Perkecil',
                iconSvg: icons.zoomOut,
                hotkey: '-',
            });
            const zoomInButton = createButton({
                label: 'Zoom +',
                titleText: 'Perbesar',
                iconSvg: icons.zoomIn,
                hotkey: '+',
            });
            const fitButton = createButton({
                label: 'Fit',
                titleText: 'Sesuaikan lebar',
                iconSvg: icons.fit,
                hotkey: '0',
            });

            const zoomLabel = document.createElement('span');
            zoomLabel.className = 'inline-flex h-9 items-center rounded-xl bg-slate-50 px-2.5 text-xs font-semibold text-slate-600';

            rightControls.appendChild(zoomOutButton);
            rightControls.appendChild(zoomLabel);
            rightControls.appendChild(zoomInButton);
            rightControls.appendChild(fitButton);

            toolbar.appendChild(leftControls);
            toolbar.appendChild(rightControls);

            const canvasContainer = document.createElement('div');
            canvasContainer.className = 'min-h-0 flex-1 overflow-auto overscroll-contain bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12),_transparent_30%),linear-gradient(to_bottom,_#f6f9fc,_#d7e3ef)] p-3 md:p-7';
            enableFreeMove(canvasContainer, {
                showGrabCursor: true,
                allowTouch: true,
            });

            const canvas = document.createElement('canvas');
            canvas.className = 'mx-auto block rounded-[24px] bg-white shadow-[0_26px_64px_-34px_rgba(15,23,42,0.5)]';
            canvasContainer.appendChild(canvas);

            container.appendChild(toolbar);
            container.appendChild(canvasContainer);
            modalBody.appendChild(container);

            const state = {
                document: pdfDocument,
                pageNumber: 1,
                isRendering: false,
                pendingPage: null,
                scale: 1,
                fitScale: 1,
                minScale: 0.5,
                maxScale: 3,
                forceFitNextRender: true,
            };

            const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

            const updateControls = () => {
                pageInput.value = String(state.pageNumber);
                pageTotal.textContent = `/ ${state.document.numPages}`;

                firstButton.disabled = state.pageNumber <= 1;
                prevButton.disabled = state.pageNumber <= 1;
                nextButton.disabled = state.pageNumber >= state.document.numPages;
                lastButton.disabled = state.pageNumber >= state.document.numPages;

                zoomLabel.textContent = `${Math.round(state.scale * 100)}%`;
                zoomOutButton.disabled = state.scale <= state.minScale;
                zoomInButton.disabled = state.scale >= state.maxScale;
            };

            const renderPage = async (targetPageNumber) => {
                if (currentToken !== modalToken) {
                    return;
                }

                const safePage = clamp(targetPageNumber, 1, state.document.numPages);

                if (state.isRendering) {
                    state.pendingPage = safePage;
                    return;
                }

                state.isRendering = true;

                try {
                    const page = await state.document.getPage(safePage);

                    if (currentToken !== modalToken) {
                        return;
                    }

                    if (state.forceFitNextRender) {
                        const baseViewport = page.getViewport({ scale: 1 });
                        const availableWidth = Math.max(canvasContainer.clientWidth - 24, 320);
                        state.fitScale = clamp(availableWidth / baseViewport.width, state.minScale, 2.5);
                        state.scale = state.fitScale;
                        state.forceFitNextRender = false;
                    }

                    const viewport = page.getViewport({ scale: state.scale });
                    const context = canvas.getContext('2d');

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    await page.render({
                        canvasContext: context,
                        viewport,
                    }).promise;

                    state.pageNumber = safePage;
                    updateControls();
                } catch (error) {
                    renderInfoBox('Gagal render halaman PDF.', 'Silakan gunakan tombol Download untuk membuka file.');
                } finally {
                    state.isRendering = false;

                    if (state.pendingPage !== null) {
                        const pending = state.pendingPage;
                        state.pendingPage = null;
                        renderPage(pending);
                    }
                }
            };

            const goToPage = (pageNumber) => {
                renderPage(pageNumber);
            };

            const adjustZoom = (delta) => {
                state.scale = clamp(state.scale + delta, state.minScale, state.maxScale);
                renderPage(state.pageNumber);
            };

            const applyFitScale = () => {
                state.forceFitNextRender = true;
                renderPage(state.pageNumber);
            };

            firstButton.addEventListener('click', () => goToPage(1));
            prevButton.addEventListener('click', () => goToPage(state.pageNumber - 1));
            nextButton.addEventListener('click', () => goToPage(state.pageNumber + 1));
            lastButton.addEventListener('click', () => goToPage(state.document.numPages));

            pageInput.addEventListener('change', () => {
                const value = Number(pageInput.value);
                if (!Number.isFinite(value)) {
                    pageInput.value = String(state.pageNumber);
                    return;
                }

                goToPage(value);
            });

            pageInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                pageInput.dispatchEvent(new Event('change'));
            });

            zoomOutButton.addEventListener('click', () => adjustZoom(-0.2));
            zoomInButton.addEventListener('click', () => adjustZoom(0.2));
            fitButton.addEventListener('click', applyFitScale);

            const onResize = () => {
                if (!isModalOpen()) {
                    return;
                }

                applyFitScale();
            };

            window.addEventListener('resize', onResize);

            activePdfCleanup = () => {
                window.removeEventListener('resize', onResize);
                activePdfCleanup = null;
            };

            activePdfControls = {
                prev: () => goToPage(state.pageNumber - 1),
                next: () => goToPage(state.pageNumber + 1),
                zoomIn: () => adjustZoom(0.2),
                zoomOut: () => adjustZoom(-0.2),
                fit: () => applyFitScale(),
            };

            updateControls();
            await renderPage(1);
        } catch (error) {
            if (currentToken !== modalToken) {
                return;
            }

            renderInfoBox('Gagal memuat preview PDF.', 'Silakan gunakan tombol Download untuk membuka file.');
        }
    };

    const renderOfficePreview = (fileUrl, fileName, currentToken) => {
        const absoluteFileUrl = resolveAbsoluteUrl(fileUrl);
        if (!absoluteFileUrl) {
            renderInfoBox('URL file tidak valid.', 'Silakan gunakan tombol Download atau Buka Tab Baru.');
            return;
        }

        if (!isPublicHttpsUrl(absoluteFileUrl)) {
            renderInfoBox(
                'Preview PPTX butuh URL HTTPS publik.',
                'Jika masih di localhost/non-HTTPS, gunakan Download atau Buka Tab Baru.'
            );
            return;
        }

        const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteFileUrl)}`;

        resetModalBody();

        const wrapper = document.createElement('div');
        wrapper.className = 'relative h-full min-h-0 overflow-auto overscroll-contain bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.10),_transparent_30%),linear-gradient(to_bottom,_#f4f7fb,_#dde7f1)] p-3 md:p-7';
        wrapper.style.touchAction = 'pan-x pan-y';
        wrapper.style.webkitOverflowScrolling = 'touch';
        enableFreeMove(wrapper, {
            showGrabCursor: false,
            allowTouch: false,
        });

        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'absolute inset-4 z-10 flex items-center justify-center rounded-[24px] bg-slate-100/86 backdrop-blur md:inset-8 md:rounded-[28px]';
        loadingOverlay.innerHTML = `
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center shadow-[0_20px_40px_-28px_rgba(15,23,42,0.35)]">
                <div class="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-slate-300 border-t-[#0071BC]"></div>
                <p class="text-sm font-semibold text-slate-700">Memuat preview PPTX...</p>
                <p class="mt-1 text-xs text-slate-500">Viewer Office sedang menyiapkan dokumen.</p>
            </div>
        `;

        const iframe = document.createElement('iframe');
        iframe.src = officeViewerUrl;
        iframe.className = 'h-full w-full rounded-[24px] border border-slate-200 bg-white shadow-[0_26px_64px_-34px_rgba(15,23,42,0.45)] md:rounded-[28px]';
        iframe.setAttribute('title', fileName || 'Preview PPTX');
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');

        const removeLoadingOverlay = () => {
            if (currentToken !== modalToken) {
                return;
            }
            loadingOverlay.remove();
        };

        iframe.addEventListener('load', removeLoadingOverlay, { once: true });

        const timeoutId = window.setTimeout(removeLoadingOverlay, 10000);
        activePdfCleanup = () => {
            window.clearTimeout(timeoutId);
            iframe.removeEventListener('load', removeLoadingOverlay);
            activePdfCleanup = null;
        };

        wrapper.appendChild(iframe);
        wrapper.appendChild(loadingOverlay);
        modalBody.appendChild(wrapper);
    };

    const renderTextPreview = async (fileUrl, fileName, currentToken, fileExtension) => {
        renderInfoBox('Memuat file teks...', 'Sedang menyiapkan isi dokumen.', true);

        const abortController = new AbortController();
        activePdfCleanup = () => {
            abortController.abort();
            activePdfCleanup = null;
        };

        try {
            const response = await fetch(fileUrl, {
                method: 'GET',
                signal: abortController.signal,
                headers: {
                    Accept: 'text/plain, text/*;q=0.9, */*;q=0.8',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const textContent = await response.text();
            if (currentToken !== modalToken) {
                return;
            }

            resetModalBody();

            const wrapper = document.createElement('div');
            wrapper.className = 'flex h-full min-h-0 flex-col overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.10),_transparent_30%),linear-gradient(to_bottom,_#f4f7fb,_#dde7f1)] p-3 md:p-7';

            const header = document.createElement('div');
            header.className = 'mb-3 flex flex-wrap items-center justify-between gap-2 rounded-[22px] border border-slate-200 bg-white/92 px-3.5 py-3 shadow-[0_20px_44px_-32px_rgba(15,23,42,0.35)] backdrop-blur-xl md:mb-4 md:rounded-[24px] md:px-4';

            const meta = document.createElement('div');
            meta.className = 'min-w-0';
            const metaTitle = document.createElement('p');
            metaTitle.className = 'truncate text-sm font-semibold text-slate-800';
            metaTitle.textContent = fileName || 'File teks';

            const metaDescription = document.createElement('p');
            metaDescription.className = 'mt-1 text-[11px] text-slate-500';
            metaDescription.textContent = `Preview ${toTitleCase(fileExtension || 'txt')} dalam mode teks biasa.`;

            meta.appendChild(metaTitle);
            meta.appendChild(metaDescription);

            const lineCount = textContent ? textContent.split(/\r\n|\r|\n/).length : 0;
            const stats = document.createElement('div');
            stats.className = 'inline-flex items-center gap-2 text-[11px] font-semibold text-slate-500';
            const linesBadge = document.createElement('span');
            linesBadge.className = 'rounded-full bg-slate-100 px-2.5 py-1 ring-1 ring-slate-200';
            linesBadge.textContent = `${lineCount} baris`;

            const charsBadge = document.createElement('span');
            charsBadge.className = 'rounded-full bg-slate-100 px-2.5 py-1 ring-1 ring-slate-200';
            charsBadge.textContent = `${textContent.length} karakter`;

            stats.appendChild(linesBadge);
            stats.appendChild(charsBadge);

            header.appendChild(meta);
            header.appendChild(stats);

            const frame = document.createElement('div');
            frame.className = 'min-h-0 flex-1 overflow-auto overscroll-contain rounded-[24px] border border-slate-200 bg-slate-950 p-3.5 shadow-[0_26px_64px_-34px_rgba(15,23,42,0.5)] md:rounded-[28px] md:p-5';
            frame.style.touchAction = 'pan-x pan-y';
            frame.style.webkitOverflowScrolling = 'touch';
            enableFreeMove(frame, {
                showGrabCursor: false,
                allowTouch: false,
            });

            const content = document.createElement('textarea');
            content.readOnly = true;
            content.spellcheck = false;
            content.value = textContent;
            content.className = 'h-full min-h-full w-full resize-none border-0 bg-transparent p-0 font-mono text-[12px] leading-6 text-slate-100 outline-none focus:ring-0 sm:text-[13px]';

            frame.appendChild(content);
            wrapper.appendChild(header);
            wrapper.appendChild(frame);
            modalBody.appendChild(wrapper);

            activePdfCleanup = null;
        } catch (error) {
            if (error?.name === 'AbortError' || currentToken !== modalToken) {
                return;
            }

            renderInfoBox('Gagal memuat file teks.', 'Silakan gunakan tombol Download atau Buka Tab Baru.');
        }
    };

    const openModal = (fileUrl, fileName, fileExtension) => {
        const extension = (fileExtension || '').toLowerCase();
        const currentToken = ++modalToken;

        activePdfControls = null;
        if (activePdfCleanup) {
            activePdfCleanup();
        }

        updateHeader(fileName, extension);
        setActionLinks(fileUrl, fileName);
        resetModalBody();

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        if (pdfExtensions.has(extension)) {
            renderPdfPreview(fileUrl, fileName, currentToken);
            return;
        }

        if (officeExtensions.has(extension)) {
            renderOfficePreview(fileUrl, fileName, currentToken);
            return;
        }

        if (videoExtensions.has(extension)) {
            const video = document.createElement('video');
            video.src = fileUrl;
            video.controls = true;
            renderFramedNode(
                video,
                'h-full w-full rounded-[24px] border border-slate-200 bg-black shadow-[0_26px_64px_-34px_rgba(15,23,42,0.5)] md:rounded-[28px]',
                { enablePan: false }
            );
            return;
        }

        if (imageExtensions.has(extension)) {
            const image = document.createElement('img');
            image.src = fileUrl;
            image.alt = fileName || 'Preview';
            image.draggable = false;
            renderFramedNode(
                image,
                'block max-w-none shrink-0 rounded-[24px] border border-slate-200 bg-white shadow-[0_26px_64px_-34px_rgba(15,23,42,0.34)] select-none md:rounded-[28px]',
                {
                    showGrabCursor: true,
                    allowTouch: true,
                }
            );
            return;
        }

        if (textExtensions.has(extension)) {
            renderTextPreview(fileUrl, fileName, currentToken, extension);
            return;
        }

        renderInfoBox('Preview tidak tersedia untuk tipe file ini.', 'Silakan gunakan tombol Download atau Buka Tab Baru.');
    };

    const closeModal = () => {
        modalToken += 1;

        if (activePdfCleanup) {
            activePdfCleanup();
        }
        activePdfControls = null;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        resetModalBody();
    };

    document.querySelectorAll('[data-preview-trigger]').forEach((button) => {
        button.addEventListener('click', () => {
            const fileUrl = button.getAttribute('data-file-url') || '';
            const fileName = button.getAttribute('data-file-name') || '';
            const fileExtension = button.getAttribute('data-file-ext') || '';

            if (!fileUrl) {
                return;
            }

            openModal(fileUrl, fileName, fileExtension);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (!isModalOpen()) {
            return;
        }

        if (event.key === 'Escape') {
            closeModal();
            return;
        }

        if (!activePdfControls) {
            return;
        }

        const activeTag = (document.activeElement?.tagName || '').toLowerCase();
        const isInputFocused = activeTag === 'input' || activeTag === 'textarea';

        if (isInputFocused) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            activePdfControls.prev();
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            activePdfControls.next();
        } else if (event.key === '+' || event.key === '=') {
            event.preventDefault();
            activePdfControls.zoomIn();
        } else if (event.key === '-') {
            event.preventDefault();
            activePdfControls.zoomOut();
        } else if (event.key === '0') {
            event.preventDefault();
            activePdfControls.fit();
        }
    });
})();
</script>
