@include('components/sidebar-beranda', [
    'headerSideNav' => 'Hasil Tryout TKA'
])

@if (Auth::user()->role === 'Orang Tua')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">
        <div class="pt-6 sm:pt-8 mx-4 sm:mx-6 lg:mx-10">
            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-student-id="{{ $studentId ?? null }}">

                <!-- STUDENT INFORMATION -->
                <section class="mb-6">
                    <!-- SKELETON -->
                    <div id="student-information-skeleton" class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-5 py-5 shadow-lg sm:px-6 sm:py-6 lg:px-7 lg:py-6">
                        <div class="absolute inset-0 -translate-x-full animate-[shimmer_1.8s_infinite] bg-linear-to-r from-transparent via-white/10 to-transparent"></div>

                        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5 lg:gap-6">
                            <!-- AVATAR SKELETON -->
                            <div class="h-14 w-14 shrink-0 animate-pulse rounded-2xl bg-white/15 sm:h-16 sm:w-16 lg:h-17 lg:w-17"></div>

                            <!-- STUDENT INFO SKELETON -->
                            <div class="min-w-0 flex-1">
                                <div class="h-2.5 w-28 animate-pulse rounded-full bg-white/20 sm:w-32"></div>
                                <div class="mt-2.5 h-5 w-40 animate-pulse rounded-md bg-white/25 sm:h-6 sm:w-48 lg:h-7 lg:w-52"></div>

                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                                    <div class="h-3 w-20 animate-pulse rounded-full bg-white/15 sm:w-24"></div>
                                    <div class="hidden h-1 w-1 rounded-full bg-white/20 sm:block"></div>
                                    <div class="h-3 w-20 animate-pulse rounded-full bg-white/15 sm:w-24"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DATA -->
                    <div id="student-information-content" class="relative hidden overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-5 py-5 shadow-lg sm:px-6 sm:py-6 lg:px-7 lg:py-6">
                        <!-- Decorative Icons -->
                        <i class="fa-solid fa-user-graduate absolute -right-5 -top-7 text-[90px] text-white/5 rotate-12 pointer-events-none sm:text-[120px] lg:text-[145px]"></i>
                        <i class="fa-solid fa-book-open absolute -bottom-8 -left-5 text-[75px] text-white/5 -rotate-12 pointer-events-none sm:text-[95px] lg:text-[115px]"></i>

                        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5 lg:gap-6">
                            <!-- AVATAR -->
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/20 bg-white/15 text-white shadow-lg backdrop-blur-sm sm:h-16 sm:w-16 lg:h-17 lg:w-17">
                                <img id="student-information-avatar" src="" alt="Foto Anak" class="hidden h-full w-full object-cover">
                                <i id="student-information-avatar-icon" class="fa-solid fa-user-graduate text-xl sm:text-2xl lg:text-[26px]"></i>
                            </div>

                            <!-- STUDENT INFO -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[10px] font-medium uppercase tracking-wider text-white/60 sm:text-[11px]">
                                        Informasi Anak
                                    </span>

                                    <span class="h-1 w-1 rounded-full bg-white/30"></span>

                                    <span class="text-[10px] font-medium text-white/70 sm:text-[11px]">
                                        Peserta Tryout TKA
                                    </span>
                                </div>

                                <h2 id="student-information-name" class="mt-1 text-base font-bold leading-tight text-white sm:text-lg">
                                    -
                                </h2>

                                <div id="student-information-meta" class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-white/70 sm:text-sm">
                                    <div id="student-information-class" class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-school text-[10px] text-white/50 sm:text-xs"></i>
                                        <span id="student-information-class-value">-</span>
                                    </div>

                                    <span id="student-information-class-separator" class="hidden h-1 w-1 rounded-full bg-white/30 sm:block"></span>

                                    <div id="student-information-school-year" class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar text-[10px] text-white/50 sm:text-xs"></i>
                                        <span id="student-information-school-year-value">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- TRYOUT SCHEDULE -->
                <section class="mb-6">
                    <div class="mb-4">
                        <h2 class="text-base font-bold text-slate-800 sm:text-lg">
                            Jadwal Tryout
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                            Lihat jadwal setiap sesi tryout TKA yang telah, sedang, dan akan diikuti anak Anda.
                        </p>
                    </div>

                    <!-- SCHEDULE SKELETON -->
                    <div id="tka-tryout-schedule-skeleton" class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <i class="fa-solid fa-calendar-days pointer-events-none absolute -right-6 -top-8 rotate-12 text-[110px] text-slate-100 sm:text-[140px]"></i>

                        <div class="relative z-10 p-5 sm:p-6 lg:p-7">
                            <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="h-5 w-24 animate-pulse rounded-full bg-slate-200"></div>
                                        <div class="h-3 w-16 animate-pulse rounded bg-slate-100"></div>
                                    </div>
                                    <div class="mt-3 h-5 w-56 animate-pulse rounded bg-slate-200 sm:w-64"></div>
                                    <div class="mt-2 h-3 w-48 animate-pulse rounded bg-slate-100 sm:w-56"></div>
                                </div>

                                <div class="flex w-full animate-pulse items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-3.5 py-2.5 sm:w-auto">
                                    <div class="h-8 w-8 shrink-0 rounded-lg bg-slate-200"></div>
                                    <div>
                                        <div class="h-2 w-12 rounded bg-slate-200"></div>
                                        <div class="mt-1.5 h-3 w-28 rounded bg-slate-200"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 max-h-130 overflow-hidden pr-1 sm:max-h-150 lg:max-h-162.5">
                                <div class="animate-pulse">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="flex gap-3.5 sm:gap-4">
                                            <div class="flex w-8 shrink-0 flex-col items-center sm:w-9">
                                                <div class="h-8 w-8 rounded-full bg-slate-200 sm:h-9 sm:w-9"></div>
                                                @if ($i < 3)
                                                    <div class="mt-1 h-full min-h-26.25 w-px bg-slate-100"></div>
                                                @endif
                                            </div>

                                            <div class="min-w-0 flex-1 {{ $i < 3 ? 'pb-5' : '' }}">
                                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 sm:p-5">
                                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="h-3 w-48 rounded bg-slate-200"></div>
                                                            <div class="mt-2.5 h-4 w-36 rounded bg-slate-200"></div>
                                                            <div class="mt-2.5 flex flex-wrap gap-4">
                                                                <div class="h-3 w-20 rounded bg-slate-100"></div>
                                                                <div class="h-3 w-16 rounded bg-slate-100"></div>
                                                                <div class="h-3 w-20 rounded bg-slate-100"></div>
                                                            </div>
                                                        </div>
                                                        <div class="h-3 w-24 rounded bg-slate-100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="mt-6 border-t border-slate-100 pt-5">
                                <div class="flex items-start gap-2.5 animate-pulse">
                                    <div class="h-7 w-7 shrink-0 rounded-lg bg-slate-200"></div>
                                    <div class="space-y-1.5">
                                        <div class="h-2.5 w-64 rounded bg-slate-100 sm:w-80"></div>
                                        <div class="h-2.5 w-48 rounded bg-slate-100 sm:w-60"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SCHEDULE CONTENT -->
                    <div id="tka-tryout-schedule-content" class="hidden">
                        <!-- show data in ajax -->
                    </div>
                </section>

                <!-- TRYOUT RESULTS -->
                <section id="tka-tryout-results-section" class="mb-6">
                    <div class="mb-4">
                        <h2 class="text-base font-bold text-slate-800 sm:text-lg">
                            Hasil Tryout
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                            Lihat hasil tryout TKA yang telah diselesaikan anak Anda.
                        </p>
                    </div>

                    <!-- RESULTS CONTAINER -->
                    <div id="tka-tryout-results-container">
                        <!-- SKELETON LOADING -->
                        <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                                <div class="absolute -right-6 -top-8 h-32 w-32 rounded-full bg-slate-100/70 blur-3xl sm:h-40 sm:w-40"></div>
                            </div>

                            <div class="relative z-10 animate-pulse p-5 sm:p-6 lg:p-7">
                                <!-- PERIOD SELECTOR SKELETON -->
                                <div class="mb-5 overflow-hidden">
                                    <div class="flex gap-2">
                                        <div class="h-9 w-24 shrink-0 rounded-xl bg-slate-200"></div>
                                        <div class="h-9 w-24 shrink-0 rounded-xl bg-slate-100"></div>
                                        <div class="h-9 w-24 shrink-0 rounded-xl bg-slate-100"></div>
                                        <div class="h-9 w-24 shrink-0 rounded-xl bg-slate-100"></div>
                                    </div>
                                </div>

                                <!-- HEADER SKELETON -->
                                <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="h-5 w-16 rounded-full bg-slate-200"></div>
                                            <div class="h-4 w-20 rounded bg-slate-100"></div>
                                        </div>

                                        <div class="mt-3 h-5 w-56 rounded bg-slate-200 sm:h-6 sm:w-64"></div>

                                        <div class="mt-3 flex flex-wrap gap-4">
                                            <div class="h-3 w-28 rounded bg-slate-100"></div>
                                            <div class="h-3 w-24 rounded bg-slate-100"></div>
                                        </div>
                                    </div>

                                    <!-- AVERAGE SKELETON -->
                                    <div class="flex shrink-0 items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <div class="h-10 w-10 shrink-0 rounded-xl bg-slate-200"></div>

                                        <div>
                                            <div class="h-3 w-20 rounded bg-slate-200"></div>
                                            <div class="mt-2 h-7 w-16 rounded bg-slate-200"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SUMMARY SKELETON -->
                                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5 sm:p-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-8 w-8 shrink-0 rounded-lg bg-slate-200"></div>

                                            <div class="min-w-0">
                                                <div class="h-3 w-16 rounded bg-slate-200"></div>
                                                <div class="mt-2 h-4 w-12 rounded bg-slate-100"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5 sm:p-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-8 w-8 shrink-0 rounded-lg bg-slate-200"></div>

                                            <div class="min-w-0">
                                                <div class="h-3 w-20 rounded bg-slate-200"></div>
                                                <div class="mt-2 h-4 w-10 rounded bg-slate-100"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-span-2 rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5 sm:col-span-1 sm:p-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-8 w-8 shrink-0 rounded-lg bg-slate-200"></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="h-3 w-28 rounded bg-slate-200"></div>
                                                <div class="mt-2 h-4 w-32 rounded bg-slate-100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SESSION TITLE SKELETON -->
                                <div class="mt-6">
                                    <div class="h-4 w-32 rounded bg-slate-200"></div>
                                    <div class="mt-2 h-3 w-64 rounded bg-slate-100"></div>
                                </div>

                                <!-- SESSION SKELETON -->
                                <div class="mt-4 space-y-3">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="rounded-2xl border border-slate-100 bg-white p-3.5 sm:p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-9 w-9 shrink-0 rounded-xl bg-slate-200"></div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="h-3 w-28 rounded bg-slate-100"></div>
                                                            <div class="mt-2 h-3 w-36 rounded bg-slate-200"></div>
                                                        </div>

                                                        <div class="h-5 w-12 shrink-0 rounded bg-slate-200"></div>
                                                    </div>

                                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                                        <div class="h-full w-[70%] rounded-full bg-slate-200"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                <!-- NOTE SKELETON -->
                                <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                    <div class="h-7 w-7 shrink-0 rounded-lg bg-slate-200"></div>

                                    <div class="min-w-0 flex-1">
                                        <div class="h-3 w-40 rounded bg-slate-200"></div>
                                        <div class="mt-2 h-3 w-full max-w-xl rounded bg-slate-100"></div>
                                        <div class="mt-1 h-3 w-3/4 rounded bg-slate-100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-6">
                    <div class="mb-4">
                        <h2 class="text-base font-bold text-slate-800 sm:text-lg">Insight & Rekomendasi</h2>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">Ringkasan perkembangan belajar dan rekomendasi berdasarkan hasil tryout TKA anak Anda.</p>
                    </div>

                    <div id="tka-tryout-insight-container">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            @for ($i = 0; $i < 3; $i++)
                                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
                                    <div class="flex items-start gap-3">
                                        <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>

                                        <div class="min-w-0 flex-1">
                                            <div class="h-2.5 w-24 animate-pulse rounded bg-slate-200"></div>
                                            <div class="mt-2 h-4 w-40 max-w-full animate-pulse rounded bg-slate-200"></div>
                                            <div class="mt-3 h-3 w-full animate-pulse rounded bg-slate-100"></div>
                                            <div class="mt-1.5 h-3 w-4/5 animate-pulse rounded bg-slate-100"></div>
                                        </div>
                                    </div>

                                    <div class="mt-4 h-10 animate-pulse rounded-xl bg-slate-100"></div>
                                </div>
                            @endfor
                        </div>

                        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="h-4 w-48 animate-pulse rounded bg-slate-200"></div>
                                        <div class="mt-2 h-3 w-72 max-w-full animate-pulse rounded bg-slate-100"></div>
                                    </div>

                                    <div class="h-6 w-28 animate-pulse rounded-full bg-slate-100"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="flex items-center gap-3 px-4 py-3.5 sm:px-5">
                                        <div class="h-9 w-9 shrink-0 animate-pulse rounded-lg bg-slate-200"></div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="h-3 w-32 max-w-[70%] animate-pulse rounded bg-slate-200"></div>
                                                <div class="h-3 w-8 animate-pulse rounded bg-slate-200"></div>
                                            </div>

                                            <div class="mt-2 flex items-center gap-2">
                                                <div class="h-1.5 flex-1 animate-pulse rounded-full bg-slate-100"></div>
                                                <div class="h-2.5 w-8 animate-pulse rounded bg-slate-100"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <div class="border-t border-slate-100 bg-slate-50/70 px-4 py-3.5 sm:px-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-2.5">
                                        <div class="mt-0.5 h-3 w-3 shrink-0 animate-pulse rounded-full bg-slate-200"></div>
                                        <div class="flex-1">
                                            <div class="h-2.5 w-full max-w-xl animate-pulse rounded bg-slate-100"></div>
                                            <div class="mt-1.5 h-2.5 w-3/4 max-w-lg animate-pulse rounded bg-slate-100"></div>
                                        </div>
                                    </div>

                                    <div class="h-9 w-28 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>
                                </div>
                            </div>
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

<script src="{{ asset('assets/js/features/lms/parent/tka-tryout/load-tka-tryout-student-information.js') }}"></script> <!--- load student information ---->
<script src="{{ asset('assets/js/features/lms/parent/tka-tryout/load-tka-tryout-schedule.js') }}"></script> <!--- load schedule ---->
<script src="{{ asset('assets/js/features/lms/parent/tka-tryout/load-tka-tryout-results.js') }}"></script> <!--- load results ---->
<script src="{{ asset('assets/js/features/lms/parent/tka-tryout/load-tka-tryout-insights.js') }}"></script> <!--- load insights ---->