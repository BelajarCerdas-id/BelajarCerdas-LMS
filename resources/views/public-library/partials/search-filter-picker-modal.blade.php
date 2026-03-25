@php
    $filterOptionMeta = [
        'all' => 'Cari di semua metadata materi.',
        'title' => 'Fokus pada judul materi.',
        'publisher' => 'Cari berdasarkan nama publisher.',
        'subject' => 'Saring berdasarkan mata pelajaran.',
        'class_level' => 'Temukan materi untuk kelas tertentu.',
        'description' => 'Cari isi ringkasan atau deskripsi.',
        'file_extension' => 'Cari berdasarkan tipe file.',
    ];
@endphp

<div id="publicLibraryFilterPickerModal" class="fixed inset-0 z-[9998] hidden items-end justify-center p-0 md:items-center md:p-6" role="dialog" aria-modal="true" aria-labelledby="publicLibraryFilterPickerTitle">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[4px]" data-filter-picker-close></div>

    <div class="relative z-10 flex w-full max-w-xl flex-col overflow-hidden rounded-t-[30px] border border-white/60 bg-[linear-gradient(180deg,_rgba(255,255,255,0.98),_rgba(248,250,252,0.98))] shadow-[0_32px_80px_-38px_rgba(15,23,42,0.48)] md:rounded-[30px]">
        <div class="relative overflow-hidden border-b border-slate-200 bg-[linear-gradient(135deg,_#020617_0%,_#0f172a_52%,_#0b5d8d_100%)] px-5 py-5 text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(103,232,249,0.22),_transparent_34%)]"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-200">Filter Picker</p>
                    <h3 id="publicLibraryFilterPickerTitle" class="mt-1 text-lg font-semibold text-white">Pilih filter pencarian</h3>
                    <p class="mt-1 text-sm text-slate-300">
                        Scope aktif:
                        <span id="publicLibraryFilterPickerCurrent" class="font-semibold text-white">Semua Field</span>
                    </p>
                </div>

                <button type="button" data-filter-picker-close
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-white transition hover:bg-white/16 focus:outline-none focus:ring-2 focus:ring-white/25"
                    aria-label="Tutup filter picker">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="max-h-[70svh] overflow-auto px-4 py-4 md:px-5">
            <div class="space-y-2">
                @foreach ($searchFilterOptions as $value => $label)
                    <button type="button"
                        data-filter-option
                        data-value="{{ $value }}"
                        data-label="{{ $label }}"
                        class="flex w-full items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-slate-300 hover:bg-slate-50">
                        <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-600 ring-1 ring-slate-100" aria-hidden="true">
                            @switch($value)
                                @case('all')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm0 1.5h10.5c.414 0 .75.336.75.75v2.5H4V5.75c0-.414.336-.75.75-.75Zm-.75 4.75h12v4.5a.75.75 0 0 1-.75.75H4.75a.75.75 0 0 1-.75-.75v-4.5Z" clip-rule="evenodd" />
                                    </svg>
                                    @break
                                @case('title')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4 5.75A1.75 1.75 0 0 1 5.75 4h8.5A1.75 1.75 0 0 1 16 5.75v.5a.75.75 0 0 1-1.5 0v-.5a.25.25 0 0 0-.25-.25h-3.5v9.75h1.25a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1 0-1.5h1.25V5.5h-3.5a.25.25 0 0 0-.25.25v.5a.75.75 0 0 1-1.5 0v-.5Z" />
                                    </svg>
                                    @break
                                @case('publisher')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 2.5a3.25 3.25 0 1 0 0 6.5 3.25 3.25 0 0 0 0-6.5ZM5.5 12A3.5 3.5 0 0 0 2 15.5v.25c0 .69.56 1.25 1.25 1.25h13.5c.69 0 1.25-.56 1.25-1.25v-.25A3.5 3.5 0 0 0 14.5 12h-9Z" clip-rule="evenodd" />
                                    </svg>
                                    @break
                                @case('subject')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm0 1.5h4.5v10h-4.5a.75.75 0 0 1-.75-.75v-8.5c0-.414.336-.75.75-.75Zm6 0h4.5c.414 0 .75.336.75.75v8.5a.75.75 0 0 1-.75.75h-4.5V5Z" />
                                    </svg>
                                    @break
                                @case('class_level')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 2 2 6l8 4 6-3v4h1.5V6L10 2Zm-4.75 7.277v3.473c0 .346.178.667.47.85 2.415 1.514 5.146 1.514 7.56 0a1 1 0 0 0 .47-.85V9.277L10 11.138 5.25 9.277Z" />
                                    </svg>
                                    @break
                                @case('description')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4.75 3.5A2.25 2.25 0 0 0 2.5 5.75v8.5A2.25 2.25 0 0 0 4.75 16.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.5A2.25 2.25 0 0 0 15.25 3.5H4.75Zm1.5 3h7a.75.75 0 0 1 0 1.5h-7a.75.75 0 0 1 0-1.5Zm0 3h7a.75.75 0 0 1 0 1.5h-7a.75.75 0 0 1 0-1.5Zm0 3h4a.75.75 0 0 1 0 1.5h-4a.75.75 0 0 1 0-1.5Z" />
                                    </svg>
                                    @break
                                @case('file_extension')
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M5.5 2.5A2.5 2.5 0 0 0 3 5v10a2.5 2.5 0 0 0 2.5 2.5h9A2.5 2.5 0 0 0 17 15V8.914a2.5 2.5 0 0 0-.732-1.768l-3.414-3.414A2.5 2.5 0 0 0 11.086 3H5.5Zm5 .75v3a1.75 1.75 0 0 0 1.75 1.75h3.25V15A1 1 0 0 1 14.5 16h-9a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5Z" />
                                    </svg>
                                    @break
                            @endswitch
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $filterOptionMeta[$value] ?? 'Pilih scope filter ini untuk pencarian.' }}</span>
                        </span>

                        <span data-filter-selected class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white opacity-0 scale-90 transition" aria-hidden="true">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.334 7.397a1 1 0 0 1-1.42-.003L3.29 9.438a1 1 0 0 1 1.42-1.408l3.955 3.99 6.625-6.676a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
            <p class="text-xs leading-5 text-slate-500">
                Pilih filter yang paling relevan, lalu lanjutkan pencarian dengan kata kunci yang lebih spesifik.
            </p>
        </div>
    </div>
</div>

<script>
(() => {
    if (window.__publicLibraryFilterPickerInit) {
        return;
    }
    window.__publicLibraryFilterPickerInit = true;

    const modal = document.getElementById('publicLibraryFilterPickerModal');
    if (!modal) {
        return;
    }

    const currentLabel = document.getElementById('publicLibraryFilterPickerCurrent');
    const optionButtons = Array.from(modal.querySelectorAll('[data-filter-option]'));
    const closeButtons = modal.querySelectorAll('[data-filter-picker-close]');
    let activeRoot = null;

    const getRootInput = (root) => root?.querySelector('[data-filter-input]');
    const getRootLabel = (root) => root?.querySelector('[data-filter-label]');

    const syncOptionStates = (value) => {
        optionButtons.forEach((button) => {
            const isSelected = button.dataset.value === value;
            const selectedIcon = button.querySelector('[data-filter-selected]');

            button.classList.toggle('border-sky-300', isSelected);
            button.classList.toggle('bg-sky-50/80', isSelected);
            button.classList.toggle('shadow-[0_18px_32px_-28px_rgba(2,132,199,0.55)]', isSelected);
            button.classList.toggle('border-slate-200', !isSelected);
            button.classList.toggle('bg-white', !isSelected);
            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');

            if (selectedIcon) {
                selectedIcon.classList.toggle('opacity-100', isSelected);
                selectedIcon.classList.toggle('scale-100', isSelected);
                selectedIcon.classList.toggle('opacity-0', !isSelected);
                selectedIcon.classList.toggle('scale-90', !isSelected);
            }
        });
    };

    const openModal = (root) => {
        activeRoot = root;

        const input = getRootInput(root);
        const label = getRootLabel(root);
        const activeValue = input?.value || 'all';

        syncOptionStates(activeValue);
        currentLabel.textContent = label?.textContent?.trim() || 'Semua Field';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        activeRoot = null;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-filter-picker-root] [data-filter-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            openModal(trigger.closest('[data-filter-picker-root]'));
        });
    });

    optionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!activeRoot) {
                return;
            }

            const input = getRootInput(activeRoot);
            const label = getRootLabel(activeRoot);

            if (input) {
                input.value = button.dataset.value || 'all';
            }

            if (label) {
                label.textContent = button.dataset.label || 'Semua Field';
            }

            closeModal();
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (modal.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            closeModal();
        }
    });
})();
</script>
