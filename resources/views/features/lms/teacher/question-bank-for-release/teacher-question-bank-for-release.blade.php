@include('components/sidebar-beranda', ['headerSideNav' => 'Question Bank For Release']);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5">

            <div id="alert-success-create-question-for-release"></div>

            <main id="container-form-teacher-question-bank-for-release" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                class="mb-10 space-y-6">
                <section id="question-for-release-wizard" class="mb-10 space-y-5">

                    <!-- STEPPER -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mx-auto w-full max-w-5xl">

                            <div class="flex w-full items-start">

                                <!-- STEP 1 -->
                                <button type="button" id="question-release-step-1" data-step="1" class="question-release-step group flex min-w-0 flex-1 flex-col 
                                    items-center text-center">
                                    
                                    <!-- NUMBER -->
                                    <div class="question-release-step-number relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full 
                                        bg-[#0071BC] text-sm font-bold text-white ring-4 ring-[#EAF6FF] transition-all duration-200 sm:h-11 sm:w-11">
                                        1
                                    </div>

                                    <!-- LABEL -->
                                    <div class="mt-2.5 min-w-0">
                                        <div class="question-release-step-label text-[9px] font-bold uppercase tracking-[0.08em] text-[#0071BC] sm:text-[10px]">
                                            Langkah 1
                                        </div>

                                        <div class="mt-0.5 text-[11px] font-semibold leading-tight text-gray-800 sm:text-xs md:text-sm">
                                            Pilih Ujian / Kelas
                                        </div>
                                    </div>
                                </button>

                                <!-- CONNECTOR 1 -->
                                <div class="flex w-full max-w-30 flex-1 items-center pt-5 sm:max-w-45 md:pt-5.5">
                                    <div id="question-release-connector-1" class="question-release-connector h-0.5 w-full rounded-full bg-gray-200 
                                        transition-all duration-300"></div>
                                </div>

                                <!-- STEP 2 -->
                                <button type="button" id="question-release-step-2" data-step="2" disabled class="question-release-step group flex min-w-0 flex-1 
                                    flex-col items-center text-center opacity-60 cursor-not-allowed">

                                    <!-- NUMBER -->
                                    <div class="question-release-step-number relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full 
                                        bg-gray-100 text-sm font-bold text-gray-400 transition-all duration-200 sm:h-11 sm:w-11">
                                        2
                                    </div>

                                    <!-- LABEL -->
                                    <div class="mt-2.5 min-w-0">
                                        <div class="question-release-step-label text-[9px] font-bold uppercase tracking-[0.08em] text-gray-400 sm:text-[10px]">
                                            Langkah 2
                                        </div>

                                        <div class="mt-0.5 text-[11px] font-semibold leading-tight text-gray-800 sm:text-xs md:text-sm">
                                            Pilih Soal
                                        </div>
                                    </div>
                                </button>

                                <!-- CONNECTOR 2 -->
                                <div class="flex w-full max-w-30 flex-1 items-center pt-5 sm:max-w-45 md:pt-5.5">
                                    <div id="question-release-connector-2" class="question-release-connector h-0.5 w-full rounded-full bg-gray-200 
                                        transition-all duration-300"></div>
                                </div>

                                <!-- STEP 3 -->
                                <button type="button" id="question-release-step-3" data-step="3" disabled class="question-release-step group flex min-w-0 flex-1 
                                    flex-col items-center text-center opacity-60 cursor-not-allowed">

                                    <!-- NUMBER -->
                                    <div class="question-release-step-number relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full 
                                        bg-gray-100 text-sm font-bold text-gray-400 transition-all duration-200 sm:h-11 sm:w-11">
                                        3
                                    </div>

                                    <!-- LABEL -->
                                    <div class="mt-2.5 min-w-0">
                                        <div class="question-release-step-label text-[9px] font-bold uppercase tracking-[0.08em] text-gray-400 sm:text-[10px]">
                                            Langkah 3
                                        </div>

                                        <div class="mt-0.5 text-[11px] font-semibold leading-tight text-gray-800 sm:text-xs md:text-sm">
                                            Review & Terbitkan
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <form id="teacher-create-question-bank-for-release-form">
                        <!-- STEP 1 (ASSESSMENT LIST) -->
                        <div id="question-release-panel-1" class="question-release-panel bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    
                            <!-- HEADER -->
                            <div class="p-5 sm:p-6 border-b border-gray-100">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#EAF6FF] text-[#0071BC]">
                                                <i class="fa-solid fa-clipboard-list text-xs"></i>
                                            </div>
    
                                            <h2 class="text-base sm:text-lg font-bold text-gray-800">
                                                Pilih Ujian / Kelas
                                            </h2>
                                        </div>
    
                                        <p class="mt-2 text-xs sm:text-sm leading-relaxed text-gray-400">
                                            Pilih asesmen yang ingin kamu gunakan untuk mengisi soal dari bank soal.
                                            Gunakan filter di bawah untuk menemukan asesmen dengan lebih cepat.
                                        </p>
                                    </div>
                                </div>
                            </div>
    
                            <!-- FILTER -->
                            <div class="p-5 sm:p-6 bg-gray-50/60 border-b border-gray-100">
                                <div class="mb-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-filter text-[11px] text-[#0071BC]"></i>
    
                                        <h3 class="text-xs font-bold text-gray-700">
                                            Filter Asesmen
                                        </h3>
                                    </div>
    
                                    <p class="mt-1 text-[11px] text-gray-400">
                                        Sesuaikan filter untuk menampilkan asesmen yang ingin kamu cari.
                                    </p>
                                </div>
    
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    
                                    <!-- TAHUN AJARAN -->
                                    <div>
                                        <label
                                            for="dropdown-filter-tahun-ajaran"
                                            class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Tahun Ajaran
                                        </label>
    
                                        <div id="container-dropdown-tahun-ajaran"></div>
                                    </div>
    
                                    <!-- ROMBEL / KELAS -->
                                    <div>
                                        <label
                                            for="dropdown-filter-class"
                                            class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Rombel / Kelas
                                        </label>
    
                                        <div id="container-dropdown-class"></div>
                                    </div>
    
                                    <!-- JENIS ASESMEN -->
                                    <div>
                                        <label
                                            for="dropdown-filter-assessment-type"
                                            class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Jenis Asesmen
                                        </label>
    
                                        <div id="container-dropdown-assessment-type"></div>
                                    </div>
    
                                    <!-- MATA PELAJARAN -->
                                    <div>
                                        <label
                                            for="dropdown-filter-subject-rombel-class"
                                            class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Mata Pelajaran
                                        </label>
    
                                        <div id="container-dropdown-subject-rombel-class"></div>
                                    </div>
                                </div>
                            </div>
    
                            <!-- ASSESSMENT LIST -->
                            <div class="p-5 sm:p-6">
    
                                <!-- LIST HEADER -->
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800">
                                            Asesmen Tersedia
                                        </h3>
    
                                        <p class="mt-0.5 text-xs text-gray-400">
                                            Pilih satu asesmen yang ingin kamu isi dengan soal dari bank soal.
                                        </p>
                                    </div>
    
                                    <span
                                        id="question-release-assessment-count"
                                        class="shrink-0 rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-500">
                                        Memuat...
                                    </span>
                                </div>
    
                                <!-- LOADING -->
                                <div id="question-release-assessment-loading" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 animate-pulse">
    
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-6 w-24 rounded-lg bg-gray-200"></div>
                                                    <div class="h-6 w-28 rounded-lg bg-gray-100"></div>
                                                </div>
    
                                                <div class="h-5 w-20 rounded-full bg-gray-100"></div>
                                            </div>
    
                                            <div class="mt-5">
                                                <div class="h-4 w-3/4 rounded bg-gray-200"></div>
    
                                                <div class="mt-4 space-y-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-4 w-4 rounded bg-gray-100"></div>
                                                        <div class="h-3 w-40 rounded bg-gray-100"></div>
                                                    </div>
    
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-4 w-4 rounded bg-gray-100"></div>
                                                        <div class="h-3 w-32 rounded bg-gray-100"></div>
                                                    </div>
    
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-4 w-4 rounded bg-gray-100"></div>
                                                        <div class="h-3 w-28 rounded bg-gray-100"></div>
                                                    </div>
                                                </div>
                                            </div>
    
                                            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-3">
                                                <div>
                                                    <div class="h-2.5 w-20 rounded bg-gray-100"></div>
                                                    <div class="mt-2 h-3 w-24 rounded bg-gray-200"></div>
                                                </div>
    
                                                <div class="h-3 w-14 rounded bg-gray-100"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
    
                                <!-- DATA -->
                                <div id="question-release-assessment-list" class="hidden max-h-[min(520px,55vh)] overflow-y-auto pr-1">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
                                </div>
    
                                <!-- EMPTY -->
                                <div id="question-release-assessment-empty" class="hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-14 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-400 shadow-sm">
                                        <i class="fa-regular fa-calendar-xmark text-lg"></i>
                                    </div>
    
                                    <h3 class="mt-4 text-sm font-bold text-gray-700">
                                        Belum ada asesmen yang tersedia
                                    </h3>
    
                                    <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-gray-400">
                                        Tidak ada asesmen yang sesuai dengan filter yang kamu pilih.
                                        Coba ubah tahun ajaran, rombel, jenis asesmen, atau mata pelajaran untuk melihat pilihan lainnya.
                                    </p>
    
                                    <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2.5 text-[11px] font-medium text-gray-500 border border-gray-200">
                                        <i class="fa-solid fa-circle-info text-[10px] text-gray-400"></i>
                                        <span>Pastikan asesmen sudah dibuat dan jadwalnya telah ditentukan.</span>
                                    </div>
                                </div>
                            </div>
    
                            <!-- FOOTER -->
                            <div class="flex items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/50 px-5 py-4 sm:px-6">
    
                                <div class="hidden sm:flex items-center gap-2 text-[11px] text-gray-400">
                                    <i class="fa-solid fa-circle-info text-[10px]"></i>
                                    <span>Pilih satu asesmen untuk melanjutkan ke langkah berikutnya.</span>
                                </div>
    
                                <button type="button" id="question-release-next-step-1" disabled class="ml-auto inline-flex items-center justify-center gap-2 
                                    rounded-xl bg-[#0071BC] px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#005F9E] 
                                    disabled:cursor-not-allowed disabled:opacity-40">
    
                                    <span>Lanjut ke Pilih Soal</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            </div>
                        </div>
    
                        <!-- STEP 2 -->
                        <div id="question-release-panel-2" class="question-release-panel hidden">
                            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                                <div class="px-5 py-5 sm:px-6 border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#EAF6FF] text-[#0071BC]">
                                            <i class="fa-solid fa-list-check text-sm"></i>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#0071BC]">Langkah 2</span>
                                            <h2 class="text-base sm:text-lg font-bold text-gray-800">Pilih Soal dari Bank Soal</h2>
                                            <p class="text-xs text-gray-500 mt-0.5">Pilih kelas, buka bank soal, lalu tentukan soal yang ingin dimasukkan ke dalam asesmen.</p>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="p-5 sm:p-6">
                                    <!-- SELECTED ASSESSMENT -->
                                    <div id="question-release-selected-assessment" class="mb-5 rounded-xl border border-[#CFEAFF] bg-[#EAF6FF] p-4"></div>
    
                                    <!-- CLASS LEVEL -->
                                    <div class="mb-5 rounded-2xl border border-gray-200 bg-gray-50/70 p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h3 class="text-sm font-bold text-gray-800">Pilih Jenjang Kelas</h3>
                                                <p class="mt-0.5 text-xs text-gray-400">Pilih tingkat kelas untuk menampilkan bank soal yang tersedia.</p>
                                            </div>
                                        </div>
    
                                        <div id="question-release-class-level-list" class="mt-4 flex flex-wrap gap-2">
                                            <div class="h-9 w-16 rounded-xl bg-gray-200 animate-pulse"></div>
                                            <div class="h-9 w-16 rounded-xl bg-gray-200 animate-pulse"></div>
                                            <div class="h-9 w-16 rounded-xl bg-gray-200 animate-pulse"></div>
                                        </div>
                                    </div>
    
                                    <!-- MAIN CONTENT -->
                                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
                                        <!-- LEFT: QUESTION BANK -->
                                        <div class="xl:col-span-7 min-w-0">
                                            <div class="mb-4 flex items-center justify-between gap-3">
                                                <div>
                                                    <h3 class="text-sm font-semibold text-gray-800">Pilih Butir Soal per Topik Bank</h3>
                                                    <p class="mt-0.5 text-xs text-gray-400">Klik baris topik untuk membuka atau menutup butir soal.</p>
                                                </div>
                                                <div class="hidden sm:flex items-center gap-2 text-[10px] text-gray-400">
                                                    <button type="button" id="question-release-expand-all" class="font-semibold text-[#0071BC] hover:underline">Buka Semua</button>
                                                    <span>•</span>
                                                    <button type="button" id="question-release-collapse-all" class="font-semibold text-gray-500 hover:underline">Tutup Semua</button>
                                                </div>
                                            </div>
    
                                            <!-- BANK HEADER -->
                                            <div class="mb-3 flex items-center justify-between gap-3">
                                                <span id="question-release-question-bank-count" class="rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-500">Memuat...</span>
                                            </div>
    
                                            <!-- BANK LOADING -->
                                            <div id="question-release-question-bank-loading" class="space-y-3">
                                                @for ($i = 0; $i < 4; $i++)
                                                    <div class="rounded-2xl border border-gray-200 bg-white p-4 animate-pulse">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div class="flex items-center gap-3 min-w-0">
                                                                <div class="h-9 w-9 rounded-xl bg-gray-200"></div>
                                                                <div class="min-w-0">
                                                                    <div class="h-3.5 w-48 rounded bg-gray-200"></div>
                                                                    <div class="mt-2 h-2.5 w-32 rounded bg-gray-100"></div>
                                                                </div>
                                                            </div>
                                                            <div class="h-7 w-24 rounded-lg bg-gray-100"></div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
    
                                            <!-- BANK DATA -->
                                            <div id="question-release-question-bank-list" class="hidden max-h-155 overflow-y-auto pr-1 space-y-3"></div>
    
                                            <!-- BANK EMPTY -->
                                            <div id="question-release-question-bank-empty" class="hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-14 text-center">
                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-400 shadow-sm">
                                                    <i class="fa-regular fa-folder-open text-lg"></i>
                                                </div>
                                                <h3 class="mt-4 text-sm font-bold text-gray-700">Belum ada bank soal yang tersedia</h3>
                                                <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-gray-400">Belum ditemukan bank soal untuk kelas dan mata pelajaran yang dipilih.</p>
                                            </div>
    
                                            <!-- BANK ERROR -->
                                            <div id="question-release-question-bank-error" class="hidden rounded-2xl border border-red-100 bg-red-50 px-5 py-12 text-center">
                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-red-100 bg-white text-red-400 shadow-sm">
                                                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                                                </div>
                                                <h3 class="mt-4 text-sm font-bold text-red-700">Bank soal belum dapat dimuat</h3>
                                                <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-red-500/80">Terjadi kendala saat mengambil daftar bank soal. Silakan coba lagi.</p>
                                                <button type="button" id="question-release-question-bank-retry" class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                                    <i class="fa-solid fa-rotate-right text-[10px]"></i>
                                                    <span>Coba Lagi</span>
                                                </button>
                                            </div>
                                        </div>
    
                                        <!-- RIGHT: SELECTED QUESTIONS -->
                                        <div class="xl:col-span-5 min-w-0">
                                            <div class="sticky top-5 rounded-2xl border border-gray-200 bg-white overflow-hidden">
                                                <!-- RESULT HEADER -->
                                                <div class="border-b border-gray-100 px-4 py-4">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <h3 class="text-sm font-bold text-gray-800">Paket Soal Ujian</h3>
                                                            <p class="mt-0.5 text-[10px] text-gray-400">Daftar soal yang akan dimasukkan ke asesmen.</p>
                                                        </div>
                                                    </div>
    
                                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                                        <span class="rounded-lg bg-[#EAF6FF] px-2.5 py-1 text-[11px] font-semibold text-[#0071BC]">
                                                            <span id="question-release-selected-question-count">0</span> butir
                                                        </span>
    
                                                        <span id="question-release-weight-status" class="rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-500">
                                                            Total Bobot: <span id="question-release-selected-question-weight">0</span>
                                                        </span>
    
                                                        <button type="button" id="question-release-normalize-weight" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-[10px] font-semibold text-gray-500 hover:bg-gray-50">
                                                            Ratakan Bobot
                                                        </button>
                                                    </div>
                                                </div>
    
                                                <!-- RESULT LIST -->
                                                <div id="question-release-selected-question-list" class="max-h-145 overflow-y-auto p-3 space-y-2.5">
                                                    <div id="question-release-selected-question-empty" class="flex min-h-75 flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 px-5 text-center">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 shadow-sm">
                                                            <i class="fa-regular fa-file-lines text-lg"></i>
                                                        </div>
                                                        <h4 class="mt-4 text-xs font-bold text-gray-700">Belum ada soal dipilih</h4>
                                                        <p class="mt-1.5 max-w-xs text-[10px] leading-relaxed text-gray-400">Pilih soal dari bank soal di sebelah kiri untuk memasukkannya ke dalam paket ujian.</p>
                                                    </div>
                                                </div>
    
                                                <!-- RESULT FOOTER -->
                                                <div class="border-t border-gray-100 bg-gray-50/70 px-4 py-3">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div>
                                                            <div class="text-[10px] text-gray-400">Total Bobot</div>
                                                            <div id="question-release-total-weight-label" class="mt-0.5 text-sm font-black text-gray-700">0</div>
                                                        </div>
    
                                                        <div id="question-release-weight-warning" class="hidden rounded-lg bg-red-50 px-2.5 py-1.5 text-[10px] font-semibold text-red-600">
                                                            Bobot harus 100
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <!-- FOOTER -->
                                <div class="border-t border-gray-100 bg-gray-50/70 px-5 py-4 sm:px-6">
                                    <div class="flex items-center justify-between gap-3">
                                        <button type="button" id="question-release-prev-step-2" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-800">
                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                            <span>Kembali</span>
                                        </button>
    
                                        <div class="flex items-center gap-3">
                                            <div class="hidden sm:flex items-center gap-2 text-[11px] text-gray-400">
                                                <i class="fa-solid fa-circle-info text-[10px]"></i>
                                                <span>Pastikan total bobot soal adalah 100.</span>
                                            </div>
    
                                            <button type="button" id="question-release-next-step-2" disabled class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0071BC] px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#005F9E] disabled:cursor-not-allowed disabled:opacity-40">
                                                <span>Lanjut ke Review</span>
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- STEP 3 -->
                        <div
                            id="question-release-panel-3"
                            class="question-release-panel hidden">
    
                            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    
                                <div class="px-5 py-5 sm:px-6 border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                            <i class="fa-solid fa-check-double text-sm"></i>
                                        </div>
    
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">
                                                Langkah 3
                                            </span>
    
                                            <h2 class="text-base sm:text-lg font-bold text-gray-800">
                                                Review & Terbitkan
                                            </h2>
    
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                Periksa kembali konfigurasi dan soal sebelum diterbitkan.
                                            </p>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="p-5 sm:p-6 space-y-5">
    
                                    <div
                                        id="question-release-review-loading"
                                        class="space-y-4">
    
                                        <div class="animate-pulse rounded-2xl border border-gray-200 bg-white p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-gray-200"></div>
                                                <div class="flex-1 space-y-2">
                                                    <div class="h-3 w-40 rounded bg-gray-200"></div>
                                                    <div class="h-2.5 w-64 rounded bg-gray-100"></div>
                                                </div>
                                            </div>
                                        </div>
    
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            <div class="animate-pulse rounded-xl border border-gray-200 bg-white p-4">
                                                <div class="h-7 w-12 rounded bg-gray-200"></div>
                                                <div class="mt-2 h-2.5 w-20 rounded bg-gray-100"></div>
                                            </div>
    
                                            <div class="animate-pulse rounded-xl border border-gray-200 bg-white p-4">
                                                <div class="h-7 w-12 rounded bg-gray-200"></div>
                                                <div class="mt-2 h-2.5 w-20 rounded bg-gray-100"></div>
                                            </div>
    
                                            <div class="animate-pulse rounded-xl border border-gray-200 bg-white p-4 col-span-2 sm:col-span-1">
                                                <div class="h-7 w-16 rounded bg-gray-200"></div>
                                                <div class="mt-2 h-2.5 w-24 rounded bg-gray-100"></div>
                                            </div>
                                        </div>
    
                                        <div class="animate-pulse rounded-2xl border border-gray-200 bg-white overflow-hidden">
                                            <div class="border-b border-gray-100 px-4 py-3">
                                                <div class="h-3 w-40 rounded bg-gray-200"></div>
                                            </div>
    
                                            <div class="space-y-3 p-4">
                                                <div class="h-12 rounded-xl bg-gray-100"></div>
                                                <div class="h-12 rounded-xl bg-gray-100"></div>
                                                <div class="h-12 rounded-xl bg-gray-100"></div>
                                            </div>
                                        </div>
                                    </div>
    
                                    <div
                                        id="question-release-review-empty"
                                        class="hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-10 text-center">
    
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                                            <i class="fa-regular fa-file-circle-question text-lg"></i>
                                        </div>
    
                                        <h3 class="mt-3 text-sm font-bold text-gray-700">
                                            Belum ada soal untuk direview
                                        </h3>
    
                                        <p class="mx-auto mt-1 max-w-sm text-xs leading-relaxed text-gray-500">
                                            Pilih minimal satu soal pada langkah sebelumnya untuk melanjutkan proses penerbitan.
                                        </p>
    
                                        <button
                                            type="button"
                                            id="question-release-review-empty-back"
                                            class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                            Kembali Pilih Soal
                                        </button>
                                    </div>
    
                                    <div
                                        id="question-release-review-content"
                                        class="hidden space-y-5">
                                    </div>
    
                                </div>
    
                                <div class="border-t border-gray-100 bg-gray-50/70 px-5 py-4 sm:px-6">
                                    <div class="flex items-center justify-between gap-3">
    
                                        <button
                                            type="button"
                                            id="question-release-prev-step-3"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-800">
                                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                            <span>Kembali</span>
                                        </button>
    
                                        <button
                                            type="button"
                                            id="question-release-publish"
                                            disabled
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">
                                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                            <span>Terbitkan Paket Soal</span>
                                        </button>
    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box max-w-3xl max-h-[80vh] p-0 overflow-hidden bg-white">
        
                        <div class="bg-[#0071BC] text-white px-6 py-4">
                            <h3 class="font-semibold text-lg m-0">
                                Question Preview
                            </h3>
                        </div>
        
                        <!-- Content -->
                        <div class="p-6 overflow-y-auto max-h-[60vh]">
                            <div id="modal-preview-content"class="prose max-w-none text-sm leading-relaxed"></div>
                        </div>
        
                        <!-- Footer  -->
                        <div class="px-6 py-4 border-t border-gray-300 flex justify-end bg-white">
                            <form method="dialog">
                                <button class="btn btn-sm">Tutup</button>
                            </form>
                        </div>
                    </div>
        
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <section id="container-paginate-teacher-question-bank-for-release-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                    class="mt-10 bg-white shadow-sm border border-gray-300 rounded-2xl p-6">

                    <h2 class="text-xl font-semibold text-gray-800">
                        Question For Release List
                    </h2>

                    <!-- FILTER CONTAINER -->
                    <div class="my-6 bg-gray-50 shadow-sm border border-gray-300 rounded-2xl p-6">

                        <!-- Header Filter -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                <h3 class="text-base font-semibold text-gray-800">
                                    Filter Soal
                                </h3>
                            </div>
                        </div>

                        <!-- Filter Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

                            <div id="container-dropdown-tahun-ajaran-paginate-question-bank-for-release"></div>

                            <div id="container-dropdown-class-paginate-question-bank-for-release"></div>

                            <div id="container-dropdown-assessment-type-paginate-question-bank-for-release"></div>

                        </div>
                    </div>

                    <div class="overflow-x-auto mt-6 pb-5">
                        <table id="table-paginate-teacher-question-bank-for-release-list" class="min-w-175 lg:min-w-full text-sm border-collapse">
                            <thead class="thead-table-paginate-teacher-question-bank-for-release-list bg-gray-50 hidden shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tahun Ajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Judul Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Semester</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tanggal Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Total Soal</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-paginate-teacher-question-bank-for-release-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-paginate-teacher-question-bank-for-release-list flex justify-center my-10"></div>

                    <div id="empty-message-paginate-teacher-question-bank-for-release-list" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada soal yang terdaftar.
                        </span>
                    </div>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/paginate-teacher-question-bank-for-release.js') }}"></script> <!--- paginate question bank for release ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/form-teacher-question-bank-for-release.js') }}"></script> <!--- form question bank for reelase ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/teacher-question-bank-for-release-weight-calculator.js') }}"></script> <!--- question bank for reelase weight calculator ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/teacher-question-bank-for-release-selection.js') }}"></script> <!--- question bank for reelase selection ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/teacher-question-bank-for-release-matching-renderer.js') }}"></script> <!--- question bank for reelase matching renderer ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown curriculum core ---->