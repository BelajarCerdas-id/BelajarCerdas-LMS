@include('components/sidebar-beranda', [
    'headerSideNav' => 'Form',
    'linkBackButton' => route('lms.office.tka-tryout-period-management.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERTS -->
            <div id="alert-success-tka-tryout-period-session-save-students"></div>

            <main id="container" data-role="{{ $role }}" data-period-id="{{ $periodId }}" data-session-id="{{ $sessionId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">

                        <i class="fa-solid fa-users absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>
                        <i class="fa-solid fa-user-check absolute -bottom-10 -left-6 text-[90px] lg:text-[140px] text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 lg:gap-8">

                            <!-- LEFT -->
                            <div class="flex-1">

                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg shrink-0">
                                        <i class="fa-solid fa-users text-white text-xl lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white leading-tight">
                                            Kelola Sesi Siswa
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>

                                </div>

                                <p class="mt-5 max-w-2xl text-sm sm:text-base text-white/80 leading-relaxed">
                                    Atur siswa yang mengikuti sesi Tryout TKA ini dengan memilih sekolah, kelas, dan peserta yang akan terdaftar.
                                </p>

                            </div>

                            <!-- RIGHT -->
                            <div class="w-full lg:w-auto">
                                <div class="rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15 px-5 py-4 text-white shadow-lg">

                                    <div class="flex items-center gap-3">

                                        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-clock text-white"></i>
                                        </div>

                                        <div>
                                            <p class="text-xs text-white/60">
                                                Sesi {{ $session->session_number ?? '-' }}
                                            </p>

                                            <p class="mt-0.5 text-sm font-semibold">
                                                {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->locale('id')->translatedFormat('d F Y') : '-' }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-white/70">
                                                {{ $session->start_time ? $session->start_time->format('H:i') : '-' }}
                                                -
                                                {{ $session->end_time ? $session->end_time->format('H:i') : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- TARGET PESERTA -->
                <section class="mb-6">
                    <form id="tka-tryout-period-session-manage-student-form">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-5 py-5 lg:px-6">
                                <div class="flex items-start gap-3">
    
                                    <!-- ICON -->
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                        <i class="fa-solid fa-users-viewfinder text-lg text-[#0071BC]"></i>
                                    </div>
    
                                    <!-- TITLE -->
                                    <div class="min-w-0">
                                        <h2 class="text-base font-bold text-slate-800">
                                            Target Peserta
                                        </h2>
    
                                        <p class="mt-1 max-w-3xl text-sm leading-relaxed text-slate-500">
                                            Tentukan sekolah dan kelas, kemudian pilih siswa yang akan mengikuti sesi Tryout TKA ini.
                                        </p>
                                    </div>
    
                                </div>
                            </div>
    
                            <div class="border-b border-slate-100 p-5 lg:p-6">
                                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                    <div class="form-control w-full">
                                        <label for="select-school" class="mb-2 block text-sm font-semibold text-slate-700">
                                            Sekolah
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>
    
                                        @if ($session->tka_tryout_period_sch_override_id)
                                            <div class="flex min-h-12 w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4">
    
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white">
                                                    <i class="fa-solid fa-school text-sm text-[#0071BC]"></i>
                                                </div>
    
                                                <div class="min-w-0 flex-1">
    
                                                    <p class="truncate text-sm font-semibold text-slate-700">
                                                        {{ $session->TkaTryoutPeriodSchOverride->SchoolPartner->nama_sekolah ?? '-' }}
                                                    </p>
    
                                                    <p class="mt-0.5 text-xs text-slate-400">
                                                        Sekolah tujuan sesi
                                                    </p>
    
                                                </div>
    
                                                <div class="shrink-0">
                                                    <i class="fa-solid fa-lock text-xs text-slate-400"></i>
                                                </div>
    
                                            </div>
    
                                            <input type="hidden" id="select-school" name="school_partner_id" value="{{ $schoolId ?? '' }}">
                                        @else
                                            <div class="relative">
                                                <select id="select-school" name="school_partner_id" class="select select-bordered h-12 w-full rounded-xl border-slate-200 
                                                    bg-white text-sm text-slate-700 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100 cursor-pointer">
    
                                                    <option value="" class="hidden">
                                                        Pilih sekolah
                                                    </option>
    
                                                    @foreach ($schools ?? [] as $school)
                                                        <option value="{{ $school->id }}">
                                                            {{ $school->nama_sekolah }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
    
                                        <p id="error-school_partner_id" class="mt-1 hidden text-xs text-red-500"></p>
                                    </div>
    
                                    <div class="form-control w-full">
    
                                        <label for="select-class" class="mb-2 block text-sm font-semibold text-slate-700">
                                            Kelas
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>
    
                                        <select id="select-class" name="kelas_id" disabled class="select select-bordered h-12 w-full rounded-xl border-slate-200 bg-slate-50 
                                            text-sm text-slate-400 outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100 disabled:cursor-default
                                            disabled:bg-slate-50 disabled:text-slate-400">
    
                                            <option value="">
                                                Pilih sekolah terlebih dahulu
                                            </option>
                                        </select>
    
                                        <p id="error-kelas_id" class="mt-1 hidden text-xs text-red-500"></p>
                                    </div>
                                </div>
    
                                <div id="target-summary" class="mt-5 hidden rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm">
                                            <i class="fa-solid fa-circle-check text-sm text-[#0071BC]"></i>
                                        </div>
    
                                        <div class="min-w-0 flex-1">
    
                                            <p class="text-xs font-medium text-blue-600">
                                                Target peserta
                                            </p>
    
                                            <p id="target-summary-text" class="mt-1 text-sm font-semibold text-slate-700">
                                                -
                                            </p>
    
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                Siswa yang tersedia pada kelas tersebut akan ditampilkan di bawah.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <div id="student-content" class="hidden">
                                <div class="border-b border-slate-100 px-5 py-5 lg:px-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-start gap-3">
    
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100">
                                                <i class="fa-solid fa-user-group text-sm text-slate-600"></i>
                                            </div>
    
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-800">
                                                    Daftar Siswa
                                                    <sup class="text-red-500">&#42;</sup>
                                                </h3>
    
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Pilih siswa yang akan mengikuti sesi ini.
                                                </p>
                                            </div>
                                        </div>
    
                                        <div class="flex w-fit items-center gap-2 rounded-xl bg-blue-50 px-3.5 py-2.5">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white">
                                                <i class="fa-solid fa-check text-xs text-[#0071BC]"></i>
                                            </div>
    
                                            <div class="text-sm">
                                                <span id="student-selected-count" class="font-bold text-[#0071BC]">
                                                    0
                                                </span>
    
                                                <span class="text-slate-500">
                                                    dipilih
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="border-b border-slate-100 px-5 py-4 lg:px-6">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="relative w-full lg:max-w-md">
    
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <i class="fa-solid fa-magnifying-glass text-sm text-slate-400"></i>
                                            </div>
    
                                            <!--- search bar --->
                                            <label class="input input-bordered flex h-11 w-full items-center gap-2 rounded-xl border-slate-200 bg-white px-3 shadow-none 
                                                transition focus-within:border-[#0071BC] focus-within:outline-none focus-within:ring-2 focus-within:ring-[#0071BC]/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                                </svg>
                                                <input id="student-search" type="search" class="grow text-sm"
                                                    placeholder="Cari sekolah..." autocomplete="OFF" />
                                            </label>
                                        </div>
    
                                        <div class="flex items-center gap-2 text-xs text-slate-500">
                                            <i class="fa-solid fa-users text-slate-400"></i>
    
                                            <span>
                                                <span id="student-total" class="font-semibold text-slate-700">
                                                    0
                                                </span>
                                                siswa tersedia
                                            </span>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="px-5 pt-4 lg:px-6">
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <label class="flex cursor-pointer items-center gap-3">
    
                                            <input type="checkbox" id="student-select-all" class="checkbox checkbox-sm rounded-md border-slate-300 
                                                [--chkbg:#0071BC] [--chkfg:white]">
    
                                            <div>
                                                <p class="text-sm font-semibold text-slate-700">
                                                    Pilih semua siswa
                                                </p>
    
                                                <p class="mt-0.5 text-xs text-slate-400">
                                                    Pilih seluruh siswa yang tersedia pada kelas ini.
                                                </p>
                                            </div>
                                        </label>
    
                                        <span id="student-visible-count" class="rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-slate-500 shadow-sm">
                                            0 siswa
                                        </span>
                                    </div>
                                </div>

                                <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
                                    
                                    <p id="error-student_ids" class="mt-1 hidden text-xs text-red-500"></p>

                                    <div id="student-list" class="overflow-hidden rounded-xl border border-slate-200">
                                        <div class="max-h-105 overflow-y-auto">
                                            <div id="student-skeleton" class="hidden divide-y divide-slate-100">
                                                @for ($i = 0; $i < 6; $i++)
                                                    <div class="flex items-center gap-3 px-4 py-3.5">
                                                        <div class="skeleton h-5 w-5 shrink-0 rounded-md"></div>
                                                        <div class="skeleton h-10 w-10 shrink-0 rounded-xl"></div>
    
                                                        <div class="min-w-0 flex-1 space-y-2">
                                                            <div class="skeleton h-3.5 w-40 rounded"></div>
                                                            <div class="skeleton h-3 w-24 rounded"></div>
                                                        </div>
    
                                                        <div class="skeleton hidden h-7 w-16 rounded-lg sm:block"></div>
                                                    </div>
                                                @endfor
                                            </div>
    
                                            <div id="student-list-items" class="divide-y divide-slate-100">
                                                <!-- show data in ajax -->
                                            </div>
    
                                            <div id="student-empty" class="hidden px-5 py-12 text-center">
                                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                                                    <i class="fa-solid fa-user-slash text-lg text-slate-400"></i>
                                                </div>
    
                                                <h4 class="mt-3 text-sm font-semibold text-slate-700">
                                                    Siswa tidak ditemukan
                                                </h4>
    
                                                <p class="mx-auto mt-1 max-w-sm text-xs leading-relaxed text-slate-400">
                                                    Tidak ada siswa yang dapat ditemukan pada target peserta.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="border-t border-slate-100 px-5 py-4 lg:px-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                                                <i class="fa-solid fa-circle-info text-sm text-[#0071BC]"></i>
                                            </div>
    
                                            <div>
    
                                                <p class="text-sm font-semibold text-slate-700">
                                                    Siap menyimpan peserta?
                                                </p>
    
                                                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                                                    <span id="save-selected-count">
                                                        0
                                                    </span>
                                                    siswa akan ditambahkan ke sesi ini.
                                                </p>
                                            </div>
                                        </div>
    
                                        <button type="button" id="btn-save-students" class="btn h-11 min-h-11 w-full rounded-xl border-0 bg-[#0071BC] px-5 text-sm 
                                            font-semibold text-white shadow-sm transition hover:bg-[#005f9e] sm:w-auto">
                                            <i class="fa-solid fa-floppy-disk text-sm"></i>
                                            <span class="save-button-text">Simpan Peserta</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
    
                            <div id="student-initial-state" class="px-5 py-12 lg:px-6">
                                <div class="mx-auto max-w-md text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                        <i class="fa-solid fa-user-group text-xl text-slate-400"></i>
                                    </div>
    
                                    <h3 class="mt-4 text-sm font-semibold text-slate-700">
                                        Tentukan target peserta terlebih dahulu
                                    </h3>
    
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-400">
                                        Pilih sekolah dan kelas untuk menampilkan daftar siswa
                                        yang dapat mengikuti sesi ini.
                                    </p>
                                </div>
                            </div>
    
                            <div id="student-loading" class="hidden px-5 py-12 lg:px-6">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <span class="loading loading-spinner loading-md text-[#0071BC]"></span>
    
                                    <p class="mt-4 text-sm font-semibold text-slate-700">
                                        Memuat daftar siswa...
                                    </p>
    
                                    <p class="mt-1 text-xs text-slate-400">
                                        Mohon tunggu sebentar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
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

<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period-session/tka-tryout-period-session-manage-user-form.js') }}"></script> <!--- tryout tka manage user form ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->