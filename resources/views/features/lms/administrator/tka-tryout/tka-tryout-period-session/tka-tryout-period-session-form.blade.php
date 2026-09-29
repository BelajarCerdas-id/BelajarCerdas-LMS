@include('components/sidebar-beranda', [
    'headerSideNav' => 'Sesi Tryout TKA',
    'linkBackButton' => route('lms.office.tka-tryout-period-management.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);


@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}" data-period-id="{{ $getPeriod->id ?? null }}" data-start-date="{{ $getPeriod->start_date ?? null }}" 
                data-end-date="{{ $getPeriod->end_date ?? null }}" data-period-override-id="{{ $getPeriodOverride->id ?? null }}" 
                data-override-start-date="{{ $getPeriodOverride->start_date ?? null }}"  data-override-end-date="{{ $getPeriodOverride->end_date ?? null }}">

                <!-- ALERTS -->
                <div id="alert-success-create-tka-tryout-session"></div>
                
                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 shadow-xl lg:p-8">
                        <i class="fa-solid fa-clock absolute -right-6 -top-8 rotate-12 text-[120px] text-white/5 pointer-events-none lg:text-[180px]"></i>
                        <i class="fa-solid fa-calendar-days absolute -bottom-10 -left-6 -rotate-12 text-[90px] text-white/5 pointer-events-none lg:text-[140px]"></i>

                        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/15 shadow-lg backdrop-blur-sm lg:h-16 lg:w-16">
                                        <i class="fa-solid fa-clock text-xl text-white lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold leading-tight text-white">
                                            Atur Sesi Tryout TKA
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">
                                    Atur tanggal dan waktu pelaksanaan setiap sesi Tryout TKA dalam periode yang telah ditentukan.
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

                <!-- FORM CREATE SESSION TRYOUT TKA -->
                <section class="mb-6">
                    <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                                    <i class="fa-solid fa-clock text-emerald-600"></i>
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Jadwal Sesi Tryout TKA
                                    </h2>

                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Tentukan tanggal dan waktu pelaksanaan sesi Tryout TKA untuk periode yang dipilih.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- FORM CONTENT -->
                        <div class="px-5 py-6 sm:px-6">
                            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4">
                                <div class="flex items-start gap-3">

                                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                        <i class="fa-solid fa-calendar-days text-blue-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-blue-800">
                                            Periode Tryout TKA
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-blue-700">
                                            @if(isset($getPeriodOverride) && $getPeriodOverride)
                                                Periode {{ $getPeriodOverride->TkaTryoutPeriod->period_number ?? 'Periode tidak tersedia' }}

                                                <i class="fa-solid fa-circle mx-1 text-[4px] align-middle"></i>
                                                Tahun Ajaran {{ $getPeriodOverride->TkaTryoutPeriod->tahun_ajaran ?? 'Tahun Ajaran tidak tersedia' }}

                                                <i class="fa-solid fa-circle mx-1 text-[4px] align-middle"></i>
                                                {{ $getPeriodOverride->SchoolPartner->nama_sekolah ?? 'Sekolah tidak tersedia' }}

                                            @else 
                                                Periode {{ $getPeriod->period_number ?? 'Periode tidak tersedia' }}

                                                <i class="fa-solid fa-circle mx-1 text-[4px] align-middle"></i>
                                                Tahun Ajaran {{ $getPeriod->tahun_ajaran ?? 'Tahun Ajaran tidak tersedia' }}
                                            @endif
                                        </p>

                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-medium text-blue-700 sm:text-sm">
                                            @if(isset($getPeriodOverride) && $getPeriodOverride)
                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-regular fa-calendar text-[11px]"></i>
                                                    {{ $getPeriodOverride->start_date
                                                        ? \Carbon\Carbon::parse($getPeriodOverride->start_date)->locale('id')->translatedFormat('d F Y')
                                                        : 'Tanggal tidak tersedia' }}
                                                </span>

                                                <span class="text-blue-400">
                                                    -
                                                </span>

                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-regular fa-calendar text-[11px]"></i>
                                                    {{ $getPeriodOverride->end_date 
                                                        ? \Carbon\Carbon::parse($getPeriodOverride->end_date)->locale('id')->translatedFormat('d F Y') 
                                                        : 'Tanggal tidak tersedia' }}
                                                </span>
                                            @else
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-regular fa-calendar text-[11px]"></i>
                                                {{ $getPeriod->start_date
                                                    ? \Carbon\Carbon::parse($getPeriod->start_date)->locale('id')->translatedFormat('d F Y')
                                                    : 'Tanggal tidak tersedia' }}
                                            </span>

                                            <span class="text-blue-400">
                                                -
                                            </span>

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-regular fa-calendar text-[11px]"></i>
                                                {{ $getPeriod->end_date 
                                                    ? \Carbon\Carbon::parse($getPeriod->end_date)->locale('id')->translatedFormat('d F Y') 
                                                    : 'Tanggal tidak tersedia' }}
                                            </span>
                                            @endif
                                        </div>

                                        <p class="mt-2 text-xs leading-relaxed text-blue-600 sm:text-sm">
                                            Sesi yang dibuat akan menjadi jadwal pelaksanaan untuk periode ini.
                                            Pastikan tanggal sesi berada dalam rentang periode.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="create-tka-tryout-session-form">
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Waktu Pelaksanaan
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Tentukan tanggal dan rentang waktu siswa dapat mengikuti sesi Tryout TKA.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">

                                        <!-- TANGGAL -->
                                        <div class="w-full">
                                            <label for="session-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Sesi
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="session-date" name="session_date" class="w-full rounded-lg border border-gray-300 bg-white px-3 
                                                    py-4 pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih tanggal" autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-session_date" class="text-xs font-semibold text-red-500"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal pelaksanaan sesi Tryout TKA.
                                            </p>
                                        </div>

                                        <!-- JAM MULAI -->
                                        <div class="w-full">
                                            <label for="start-time" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jam Mulai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="start-time" name="start_time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 
                                                    pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih jam mulai" autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-start_time" class="text-xs font-semibold text-red-500"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu dimulainya sesi.
                                            </p>
                                        </div>

                                        <!-- JAM SELESAI -->
                                        <div class="w-full">
                                            <label for="end-time" class="mb-2 block text-sm font-medium text-slate-700">
                                                Jam Selesai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input type="text" id="end-time" name="end_time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-4 
                                                    pr-10 text-sm shadow-sm outline-none transition duration-200" placeholder="Pilih jam selesai" autocomplete="off">

                                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-end_time" class="text-xs font-semibold text-red-500"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Waktu berakhirnya sesi.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FORM ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                                    <button type="button" id="button-save-tka-tryout-session" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] 
                                        text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Sesi
                                    </button>
                                </div>
                            </form>
                        </div>
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

<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-session/tka-tryout-period-session-form.js') }}"></script> <!--- tryout tka period session form ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->