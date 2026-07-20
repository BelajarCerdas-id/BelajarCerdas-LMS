@include('components/sidebar-beranda', ['headerSideNav' => 'Agenda Harian']);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">

                {{-- HERO --}}
                <section
                    class="relative overflow-hidden rounded-2xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-8 py-6 text-white shadow-lg">

                    {{-- Background Decoration --}}
                    <div class="absolute -top-12 -right-12 h-40 w-40 rounded-full bg-white/10"></div>

                    <div class="absolute -bottom-16 left-20 h-28 w-28 rounded-full bg-white/5"></div>

                    <div class="relative z-10 flex items-center justify-between">

                        {{-- Left --}}
                        <div class="max-w-2xl">

                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1.5 text-xs font-medium">

                                <i class="fa-solid fa-book-open"></i>

                                Agenda Harian Guru

                            </div>

                            <h1 class="mt-4 text-2xl font-bold">
                                Catatan Kegiatan Pembelajaran
                            </h1>

                            <p class="mt-2 text-sm leading-6 text-blue-100">
                                Catat kegiatan pembelajaran yang telah dilaksanakan sebagai dokumentasi
                                proses belajar mengajar dan pelaporan.
                            </p>

                        </div>

                        {{-- Right --}}
                        <div class="hidden lg:flex">

                            <div
                                class="flex h-28 w-28 items-center justify-center rounded-full bg-white/10">

                                <i class="fa-solid fa-chalkboard-user text-5xl text-white/80"></i>

                            </div>

                        </div>

                    </div>
                    
                    <!-- TAB -->
                    <div class="mt-8">
                        <div
                            class="inline-flex w-full flex-col gap-2 rounded-2xl border border-white/10 bg-white/10 p-2 backdrop-blur
                                sm:w-auto sm:flex-row sm:gap-0 sm:p-1">

                            <a href=""
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow
                                    sm:w-auto sm:justify-start">
                                <i class="fa-solid fa-chart-column"></i>
                                Dashboard Monitoring
                            </a>

                            <a href="{{ route('lms.teacherDailyAgenda.history.view', [
                                'role' => $role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-medium text-white transition hover:bg-white/10
                                    sm:w-auto sm:justify-start">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Riwayat Agenda
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mt-8 rounded-3xl border border-gray-300 bg-slate-50 p-6">

                    <!-- Header -->
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    
                        <div>
                    
                            <h2 class="text-2xl font-bold text-slate-800">
                                Jadwal Mengajar Hari Ini
                            </h2>
                    
                            <p class="mt-1 text-sm text-slate-500">
                                Pilih jadwal yang sedang berlangsung atau telah selesai untuk mengisi agenda pembelajaran.
                            </p>
                    
                        </div>
                    
                        <div class="rounded-full bg-white px-5 py-2 shadow-sm border border-slate-300">
                            <span id="total-daily-agenda-header-info" class="font-semibold text-blue-600">
                                0 Jadwal
                            </span>
                        </div>
                    </div>

                    <!-- Skeleton Loading -->
                    <div id="daily-agenda-skeleton" class="hidden">

                        @for ($i = 0; $i < 3; $i++)
                            <div class="mt-8 animate-pulse">
                                <div class="rounded-2xl border border-slate-300 bg-white p-6 shadow-sm">
                                    <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                                        <div class="flex gap-5 flex-1">

                                            <!-- Time -->
                                            <div class="flex h-18 w-18 shrink-0 flex-col items-center justify-center rounded-2xl bg-slate-100">
                                                <div class="h-5 w-10 rounded bg-slate-300"></div>
                                                <div class="mt-2 h-3 w-8 rounded bg-slate-200"></div>
                                            </div>

                                            <!-- Information -->
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-6 w-44 rounded bg-slate-300"></div>
                                                    <div class="h-6 w-24 rounded-full bg-slate-200"></div>
                                                </div>

                                                <div class="mt-5 flex flex-wrap gap-6">
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-4 w-4 rounded-full bg-slate-300"></div>
                                                        <div class="h-4 w-28 rounded bg-slate-200"></div>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <div class="h-4 w-4 rounded-full bg-slate-300"></div>
                                                        <div class="h-4 w-20 rounded bg-slate-200"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Button -->
                                        <div class="h-12 w-40 rounded-xl bg-slate-200"></div>
                                    </div>
                                </div>
                            </div>
                        @endfor

                    </div>

                    <!-- Data -->
                    <div id="daily-agenda-list"></div>

                    <!-- Empty State -->
                    <div id="daily-agenda-empty"
                        class="hidden rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center mt-4">

                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-50">

                            <i class="fa-solid fa-calendar-xmark text-3xl text-blue-500"></i>

                        </div>

                        <h3 class="mt-6 text-xl font-semibold text-slate-800">
                            Belum Ada Jadwal Mengajar Hari Ini
                        </h3>

                        <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-slate-500">
                            Jadwal mengajar untuk hari ini belum tersedia.
                        </p>
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

<script src="{{ asset('assets/js/features/lms/teacher/daily-agenda/teacher-daily-agenda-list.js') }}"></script> <!--- paginate daily agenda ---->