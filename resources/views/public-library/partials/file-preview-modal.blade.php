<div id="libraryPreviewModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 md:p-6" role="dialog" aria-modal="true" aria-labelledby="libraryPreviewTitle">
    <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-[2px]" data-preview-close></div>

    <div class="relative z-10 w-full max-w-6xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-[#0071BC]/10 text-[#0071BC]">F</span>
                    <h3 id="libraryPreviewTitle" class="truncate text-sm font-semibold text-slate-700">
                        Preview File
                    </h3>
                    <span id="libraryPreviewTypeBadge" class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                        -
                    </span>
                </div>
                <p id="libraryPreviewSubtitle" class="mt-1 truncate text-xs text-slate-500">
                    Pratinjau file materi pembelajaran.
                </p>
            </div>

            <button type="button" data-preview-close
                class="group inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30">
                <span class="inline-flex h-4 w-4 items-center justify-center text-slate-500 transition group-hover:text-[#0071BC]" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                        <path d="M5 5L15 15M15 5L5 15" stroke-linecap="round" />
                    </svg>
                </span>
                <span>Tutup</span>
                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 transition group-hover:bg-[#0071BC]/10 group-hover:text-[#0071BC]">Esc</span>
            </button>
        </div>

        <div id="libraryPreviewBody" class="h-[72vh] min-h-[320px] bg-slate-100"></div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] text-slate-500">
                Esc untuk tutup modal. Arrow kiri/kanan untuk navigasi PDF.
            </p>

            <div class="flex items-center gap-2">
                <a id="libraryPreviewOpenTab" href="#" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-px hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30">
                    <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                            <path d="M12 4h4v4" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8 12l8-8" stroke-linecap="round" />
                            <path d="M16 11v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    Buka Tab Baru
                </a>
                <a id="libraryPreviewDownload" href="#"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#0071BC] px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-px hover:bg-[#005b94] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/35">
                    <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                            <path d="M10 3v9" stroke-linecap="round" />
                            <path d="M6.5 9.5L10 13l3.5-3.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M4 15h12" stroke-linecap="round" />
                        </svg>
                    </span>
                    Download
                </a>
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
    const modalSubtitle = document.getElementById('libraryPreviewSubtitle');
    const modalTypeBadge = document.getElementById('libraryPreviewTypeBadge');
    const modalDownload = document.getElementById('libraryPreviewDownload');
    const modalOpenTab = document.getElementById('libraryPreviewOpenTab');
    const closeButtons = modal.querySelectorAll('[data-preview-close]');

    const videoExtensions = new Set(['mp4', 'mov', 'avi', 'mkv', 'webm']);
    const iframeExtensions = new Set(['txt']);
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

        modalOpenTab.href = fileUrl;
    };

    const toTitleCase = (value) => {
        const text = String(value || '').toLowerCase();
        if (!text) {
            return 'Unknown';
        }

        return text.charAt(0).toUpperCase() + text.slice(1);
    };

    const updateHeader = (fileName, extension) => {
        modalTitle.textContent = fileName || 'Preview File';
        modalSubtitle.textContent = fileName ? `File: ${fileName}` : 'Pratinjau file materi pembelajaran.';
        modalTypeBadge.textContent = toTitleCase(extension || '-');
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
        wrapper.className = 'flex h-full items-center justify-center p-6';

        const box = document.createElement('div');
        box.className = 'w-full max-w-md rounded-xl border border-slate-300 bg-white p-5 text-center shadow-sm';

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
            toolbar.className = 'flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-white/95 px-3 py-2 backdrop-blur-sm';

            const leftControls = document.createElement('div');
            leftControls.className = 'inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 shadow-sm';

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
                    ? 'inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-slate-300 bg-white px-2 text-xs font-semibold text-slate-700 transition hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-300 disabled:hover:bg-white disabled:hover:text-slate-700'
                    : 'inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 text-xs font-semibold text-slate-700 transition hover:border-[#0071BC] hover:bg-[#0071BC]/5 hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-300 disabled:hover:bg-white disabled:hover:text-slate-700';
                if (titleText) {
                    button.title = titleText;
                }

                button.innerHTML = `
                    ${iconSvg ? `<span class="inline-flex h-3.5 w-3.5 items-center justify-center">${iconSvg}</span>` : ''}
                    ${label ? `<span>${label}</span>` : ''}
                    ${hotkey ? `<span class="rounded bg-slate-100 px-1 py-0.5 text-[10px] font-bold text-slate-500">${hotkey}</span>` : ''}
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
            pageInput.className = 'h-8 w-16 rounded-md border border-slate-300 bg-white px-2 text-center text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0071BC]/30';

            const pageTotal = document.createElement('span');
            pageTotal.className = 'inline-flex h-8 items-center rounded-md bg-white px-2 text-xs font-semibold text-slate-600';

            leftControls.appendChild(firstButton);
            leftControls.appendChild(prevButton);
            leftControls.appendChild(pageInput);
            leftControls.appendChild(pageTotal);
            leftControls.appendChild(nextButton);
            leftControls.appendChild(lastButton);

            const rightControls = document.createElement('div');
            rightControls.className = 'inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 shadow-sm';

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
            zoomLabel.className = 'inline-flex h-8 items-center rounded-md bg-white px-2 text-xs font-semibold text-slate-600';

            rightControls.appendChild(zoomOutButton);
            rightControls.appendChild(zoomLabel);
            rightControls.appendChild(zoomInButton);
            rightControls.appendChild(fitButton);

            toolbar.appendChild(leftControls);
            toolbar.appendChild(rightControls);

            const canvasContainer = document.createElement('div');
            canvasContainer.className = 'flex-1 overflow-auto bg-gradient-to-b from-slate-200 to-slate-300 p-3 md:p-5';

            const canvas = document.createElement('canvas');
            canvas.className = 'mx-auto block rounded bg-white shadow';
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
        wrapper.className = 'relative h-full bg-slate-100';

        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'absolute inset-0 z-10 flex items-center justify-center bg-slate-100/90';
        loadingOverlay.innerHTML = `
            <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 text-center shadow-sm">
                <div class="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-slate-300 border-t-[#0071BC]"></div>
                <p class="text-sm font-semibold text-slate-700">Memuat preview PPTX...</p>
                <p class="mt-1 text-xs text-slate-500">Viewer Office sedang menyiapkan dokumen.</p>
            </div>
        `;

        const iframe = document.createElement('iframe');
        iframe.src = officeViewerUrl;
        iframe.className = 'h-full w-full border-0 bg-white';
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
            video.className = 'h-full w-full bg-black';
            modalBody.appendChild(video);
            return;
        }

        if (imageExtensions.has(extension)) {
            const image = document.createElement('img');
            image.src = fileUrl;
            image.alt = fileName || 'Preview';
            image.className = 'h-full w-full object-contain bg-white';
            modalBody.appendChild(image);
            return;
        }

        if (iframeExtensions.has(extension)) {
            const iframe = document.createElement('iframe');
            iframe.src = fileUrl;
            iframe.className = 'h-full w-full border-0 bg-white';
            iframe.setAttribute('title', fileName || 'Preview File');
            modalBody.appendChild(iframe);
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
