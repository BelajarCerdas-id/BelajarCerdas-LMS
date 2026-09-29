@include('components/sidebar-beranda', [
    'headerSideNav' => 'Mata Pelajaran Tryout TKA',
    'linkBackButton' => route('lms.office.tka-tryout-period-management.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);


@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}" data-period-id="{{ $periodId }}" data-override-id="{{ $periodOverrideId ?? '' }}">

                <!-- ALERTS -->
                <div id="alert-success-assign-tka-tryout-subject"></div>
                
                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 shadow-xl lg:p-8">
                        <i class="fa-solid fa-book-open absolute -right-6 -top-8 rotate-12 text-[120px] text-white/5 pointer-events-none lg:text-[180px]"></i>
                        <i class="fa-solid fa-book absolute -bottom-10 -left-6 -rotate-12 text-[90px] text-white/5 pointer-events-none lg:text-[140px]"></i>

                        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- ICON -->
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/15 shadow-lg backdrop-blur-sm lg:h-16 lg:w-16">
                                        <i class="fa-solid fa-book-open text-xl text-white lg:text-3xl"></i>
                                    </div>

                                    <!-- TITLE -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold leading-tight text-white">
                                            Atur Mata Pelajaran Tryout TKA
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">
                                    Atur mata pelajaran yang akan diujikan pada setiap sesi Tryout TKA dalam periode yang telah ditentukan.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div class="w-full lg:w-auto">
                                <a href="{{ route('lms.office.tka-tryout-period-management.view', [
                                    'role' => $role
                                ]) }}">
                                    <button type="button" class="btn w-full border-white bg-white text-[#005A9C] shadow-lg hover:border-slate-100 
                                        hover:bg-slate-100 lg:w-auto">
                                        <i class="fa-solid fa-arrow-left"></i>
                                        Kembali ke Daftar Periode
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PERIOD INFORMATION -->
                <section class="mb-6">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <!-- PERIOD INFORMATION HEADER -->
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-calendar-days text-[#0071BC]"></i>
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                                        Informasi Periode
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                                        Informasi periode Tryout TKA yang sedang dikonfigurasi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- PERIOD INFORMATION SKELETON -->
                        <div id="tka-tryout-period-information-skeleton" class="px-5 py-5 sm:px-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">

                                <!-- PERIOD -->
                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <div class="h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                    <div class="mt-3 h-5 w-28 animate-pulse rounded bg-slate-200"></div>
                                    <div class="mt-2 h-3 w-20 animate-pulse rounded bg-slate-100"></div>
                                </div>

                                <!-- ACADEMIC YEAR -->
                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <div class="h-3 w-24 animate-pulse rounded bg-slate-200"></div>
                                    <div class="mt-3 h-5 w-32 animate-pulse rounded bg-slate-200"></div>
                                    <div class="mt-2 h-3 w-24 animate-pulse rounded bg-slate-100"></div>
                                </div>

                                <!-- PERIOD DATE -->
                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <div class="h-3 w-28 animate-pulse rounded bg-slate-200"></div>
                                    <div class="mt-3 h-5 w-36 animate-pulse rounded bg-slate-200"></div>
                                    <div class="mt-2 h-3 w-24 animate-pulse rounded bg-slate-100"></div>
                                </div>
                            </div>
                        </div>

                        <!-- PERIOD INFORMATION CONTENT -->
                        <div id="tka-tryout-period-information" class="hidden px-5 py-5 sm:px-6">
                            <div id="tka-tryout-period-information-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">

                                <!-- PERIOD -->
                                <div class="rounded-xl border border-slate-200 bg-white p-4 transition-colors hover:border-blue-200 hover:bg-blue-50/30">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                            <i class="fa-solid fa-layer-group text-sm text-[#0071BC]"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-400">
                                                Periode
                                            </p>

                                            <p id="tka-tryout-period-number" class="mt-1 text-sm font-bold text-slate-800">
                                                -
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-400">
                                                Periode Tryout TKA
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACADEMIC YEAR -->
                                <div class="rounded-xl border border-slate-200 bg-white p-4 transition-colors hover:border-blue-200 hover:bg-blue-50/30">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100">
                                            <i class="fa-solid fa-graduation-cap text-sm text-indigo-600"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-400">
                                                Tahun Ajaran
                                            </p>

                                            <p id="tka-tryout-period-academic-year" class="mt-1 text-sm font-bold text-slate-800">
                                                -
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-400">
                                                Tahun ajaran periode
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- PERIOD DATE -->
                                <div class="rounded-xl border border-slate-200 bg-white p-4 transition-colors hover:border-blue-200 hover:bg-blue-50/30">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                                            <i class="fa-solid fa-calendar-days text-sm text-emerald-600"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-400">
                                                Periode Pelaksanaan
                                            </p>

                                            <p id="tka-tryout-period-date" class="mt-1 text-sm font-bold text-slate-800">
                                                -
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-400">
                                                Tanggal pelaksanaan Tryout
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- SCHOOL OVERRIDE -->
                                <div id="tka-tryout-period-school-information" class="hidden rounded-xl border border-slate-200 bg-white p-4 transition-colors 
                                    hover:border-blue-200 hover:bg-blue-50/30">
                                    <div class="flex items-start gap-3">
                                        <div id="tka-tryout-period-school-logo" class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-violet-100">
                                            <i class="fa-solid fa-school text-sm text-violet-600"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-400">
                                                Sekolah
                                            </p>

                                            <p id="tka-tryout-period-school-name" class="mt-1 truncate text-sm font-bold text-slate-800">
                                                -
                                            </p>

                                            <p id="tka-tryout-period-school-npsn" class="mt-0.5 text-xs text-slate-400">
                                                NPSN -
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PERIOD INFORMATION ERROR -->
                        <div id="empty-message-tka-tryout-period-information" class="hidden px-5 py-8 sm:px-6">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                                    <i class="fa-solid fa-calendar-xmark text-lg text-slate-400"></i>
                                </div>

                                <h3 class="mt-3 text-sm font-bold text-slate-700">
                                    Informasi Periode Tidak Ditemukan
                                </h3>

                                <p class="mt-1 max-w-md text-xs leading-relaxed text-slate-500 sm:text-sm">
                                    Informasi periode Tryout TKA tidak dapat dimuat. Silakan coba kembali atau periksa periode yang dipilih.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ASSIGN SUBJECT FORM -->
                <section class="mb-6">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <!-- FORM HEADER -->
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-book-open text-[#0071BC]"></i>
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                                        Tambah Mata Pelajaran
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                                        Pilih sesi dan mata pelajaran yang akan diujikan pada sesi tersebut.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- FORM CONTENT -->
                        <form id="tka-tryout-period-subject-form">
                            <div class="px-5 py-5 sm:px-6">

                                <!-- SUBJECT DATE SELECT -->
                                <div class="mb-6">
                                    <label for="tka-tryout-subject-date" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Tanggal Pelaksanaan
                                        <span class="text-red-500">&#42;</span>
                                    </label>

                                    <select id="tka-tryout-subject-date" name="subject_date" class="select w-full cursor-pointer rounded-xl border-slate-300 
                                        bg-white text-sm text-slate-700 focus:border-[#0071BC] focus:outline-none focus:ring-1 focus:ring-[#0071BC]">
                                        <option value="" class="hidden">Pilih tanggal pelaksanaan</option>

                                        @if ($isOverride)
                                            @foreach ($periodOverrideDateList as $date)
                                                <option value="{{ $date->format('Y-m-d') }}">
                                                    {{ $date->locale('id')->translatedFormat('d F Y') }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach ($periodDateList as $date)
                                                <option value="{{ $date->format('Y-m-d') }}">
                                                    {{ $date->locale('id')->translatedFormat('d F Y') }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                    <p id="error-tka-tryout-subject-date" class="mt-1 hidden text-xs font-medium text-red-500"></p>

                                    <p class="mt-1.5 text-xs text-slate-400">
                                        Pilih tanggal tempat mata pelajaran akan diujikan.
                                    </p>
                                </div>

                                <!-- SUBJECT LIST -->
                                <div>
                                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <h3 class="text-sm font-semibold text-slate-700">
                                                Mata Pelajaran
                                                <span class="text-red-500">&#42;</span>
                                            </h3>

                                            <p class="text-xs text-slate-400">
                                                Pilih mata pelajaran yang akan ditambahkan ke sesi.
                                            </p>
                                        </div>

                                        <span id="tka-tryout-selected-subject-count"
                                            class="inline-flex w-fit items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-[#0071BC]">
                                            0 Dipilih
                                        </span>
                                    </div>

                                    <p id="error-subject-ids" class="mt-1 hidden text-xs font-medium text-red-500"></p>

                                    <!-- SUBJECT LIST SKELETON -->
                                    <div id="tka-tryout-subject-selection-skeleton" class="overflow-hidden rounded-xl border border-slate-200">
                                        @for ($i = 0; $i < 5; $i++)
                                            <div @class(['flex items-center gap-3 px-4 py-3.5', 'border-b border-slate-200' => $i < 4])>
                                                <div class="h-5 w-5 shrink-0 animate-pulse rounded-md bg-slate-200"></div>

                                                <div class="flex h-9 w-9 shrink-0 animate-pulse rounded-lg bg-slate-200"></div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="h-4 w-40 animate-pulse rounded bg-slate-200"></div>
                                                    <div class="mt-1.5 h-3 w-24 animate-pulse rounded bg-slate-100"></div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>

                                    <!-- SUBJECT LIST -->
                                    <div id="tka-tryout-subject-selection" class="hidden overflow-hidden rounded-xl border border-slate-200">
                                        <!-- show data in ajax -->
                                    </div>

                                    <!-- EMPTY SUBJECT -->
                                    <div id="empty-message-tka-tryout-subject-selection"
                                        class="hidden overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10">
                                        <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-200">
                                                <i class="fa-solid fa-book-open text-lg text-slate-400"></i>
                                            </div>

                                            <h4 class="mt-3 text-sm font-bold text-slate-700">
                                                Belum Ada Mata Pelajaran
                                            </h4>

                                            <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                                                Belum ada mata pelajaran yang tersedia untuk dipilih.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- SELECTED SUBJECT SUMMARY -->
                                <div id="tka-tryout-selected-subject-summary" class="mt-6 hidden overflow-hidden rounded-xl border border-blue-100 bg-blue-50">

                                    <!-- SUMMARY HEADER -->
                                    <div class="border-b border-blue-100 px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                                <i class="fa-solid fa-list-check text-sm text-[#0071BC]"></i>
                                            </div>

                                            <div>
                                                <h3 class="text-sm font-bold text-slate-800">
                                                    Ringkasan Pilihan
                                                </h3>

                                                <p class="text-xs text-slate-500">
                                                    Atur jumlah soal dan durasi untuk setiap mata pelajaran.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SUMMARY CONTENT -->
                                    <div class="px-4 py-4">
                                        <div class="mb-4 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-medium text-slate-400">
                                                    Tanggal Pelaksanaan
                                                </p>

                                                <p id="tka-tryout-summary-date" class="mt-0.5 text-sm font-bold text-slate-700">
                                                    -
                                                </p>
                                            </div>

                                            <span id="tka-tryout-summary-count" class="inline-flex shrink-0 items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-[#0071BC]">
                                                0 Mata Pelajaran
                                            </span>
                                        </div>

                                        <!-- SELECTED SUBJECT LIST -->
                                        <div id="tka-tryout-selected-subject-list" class="space-y-3">
                                            <!-- show data in ajax -->
                                        </div>

                                        <p id="error-tka-tryout-subject-config" class="mt-3 hidden text-xs font-medium text-red-500"></p>
                                    </div>
                                </div>

                                <!-- ACTION -->
                                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                                    <button type="button" id="button-save-tka-tryout-subject" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] 
                                        text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Mata Pelajaran
                                    </button>
                                </div>
                            </div>
                        </form>
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

<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-subject/tka-tryout-period-subject-form.js') }}"></script> <!--- tryout tka subject form ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->