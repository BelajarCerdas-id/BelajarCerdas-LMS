@include('components/sidebar-beranda', [
    'headerSideNav' => 'Form',
    'linkBackButton' => route('lms.teacherDailyAgenda.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- Alert -->
            <div id="alert-success-insert-data-daily-agenda"></div>

            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-day-of-week="{{ $dayOfWeek }}"
                data-class-id="{{ $classId }}" data-subject-id="{{ $subjectId }}">

                <!-- Header Form Agenda -->
                <section
                    class="mt-8 overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] shadow-xl">

                    <div class="p-6 lg:p-8">

                        <!-- Header -->
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                            <div>

                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium text-white">

                                    <i class="fa-solid fa-pen-to-square"></i>

                                    Form Agenda Pembelajaran

                                </span>

                                <h2 class="mt-4 text-3xl font-bold text-white">
                                    Isi Agenda Mengajar
                                </h2>

                                <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100">
                                    Lengkapi agenda pembelajaran sesuai kegiatan yang telah
                                    dilaksanakan pada jadwal mengajar berikut.
                                </p>

                            </div>

                            <div class="w-full flex jsutify-start sm:justify-end">
                                <span id="agenda-status" class="inline-flex w-full sm:w-fit items-center gap-2 rounded-full bg-amber-400/20 px-4 py-2 text-sm font-medium text-amber-200">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                </span>
                            </div>

                        </div>

                        <!-- Skeleton -->
                        <div id="daily-agenda-header-skeleton" class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                            @for($i=0;$i<4;$i++)
                                <div class="animate-pulse rounded-2xl border border-white/10 bg-white/10 p-5">
                                    <div class="flex gap-4">
                                        <div class="h-12 w-12 rounded-xl bg-white/20"></div>
                                        <div class="flex-1">
                                            <div class="h-3 w-20 rounded bg-white/20"></div>
                                            <div class="mt-3 h-5 w-32 rounded bg-white/20"></div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        {{-- Schedule --}}
                        <div id="daily-agenda-header" class="hidden">
                            <div
                                class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    
                                <!-- Tanggal -->
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
    
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
    
                                            <i class="fa-solid fa-calendar-days text-xl text-white"></i>
                                        </div>
    
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-blue-200">
                                                Tanggal
                                            </p>
    
                                            <h3 id="agenda-date" class="mt-1 font-semibold text-white"></h3>
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Jam -->
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
    
                                    <div class="flex items-center gap-4">
    
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                                            <i class="fa-solid fa-clock text-xl text-white"></i>
                                        </div>
    
                                        <div>
    
                                            <p class="text-xs uppercase tracking-wider text-blue-200">
    
                                                Jam Mengajar
    
                                            </p>
    
                                            <h3 id="agenda-time" class="mt-1 font-semibold text-white"></h3>
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Kelas -->
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
    
                                    <div class="flex items-center gap-4">
    
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                                            <i class="fa-solid fa-users text-xl text-white"></i>
                                        </div>
    
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-blue-200">
                                                Kelas
                                            </p>
    
                                            <h3 id="agenda-class" class="mt-1 font-semibold text-white"></h3>
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Mapel -->
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
    
                                    <div class="flex items-center gap-4">
    
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
    
                                            <i class="fa-solid fa-book-open text-xl text-white"></i>
    
                                        </div>
    
                                        <div>
    
                                            <p class="text-xs uppercase tracking-wider text-blue-200">
    
                                                Mata Pelajaran
    
                                            </p>
    
                                            <h3 id="agenda-subject" class="mt-1 font-semibold text-white"></h3>
    
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Feedback Kepala Sekolah -->
                @if($teacherDailyAgenda?->feedback)
                    <section class="mt-6">
                        <div class="overflow-hidden rounded-3xl border border-amber-200 bg-amber-50 shadow-sm">

                            <!-- Header -->
                            <div class="border-b border-amber-200 px-6 py-5">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                                        <i class="fa-solid fa-comments text-amber-600"></i>
                                    </div>

                                    <div class="flex-1">

                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-semibold text-slate-800">
                                                Feedback Kepala Sekolah
                                            </h3>
                                        </div>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Berikut merupakan masukan yang diberikan oleh kepala sekolah terhadap agenda pembelajaran ini.
                                        </p>

                                    </div>

                                </div>
                            </div>

                            <!-- Body -->
                            <div class="space-y-5 p-6">
                                <div class="rounded-2xl border border-amber-200 bg-white p-5">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 text-amber-500">
                                            <i class="fa-solid fa-quote-left text-xl"></i>
                                        </div>

                                        <div class="flex-1">

                                            <p class="whitespace-pre-line text-sm leading-7 text-slate-700 text-justify">
                                                {{ $teacherDailyAgenda->feedback }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                <!-- Form Agenda -->
                <section class="mt-8">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <form id="create-daily-agenda-form">
                            <input type="hidden" name="teacher_daily_agenda_id" value="{{ $teacherDailyAgenda?->id }}">

                            <!-- Header -->
                            <div class="border-b border-slate-200 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                                        <i class="fa-solid fa-book-open text-blue-600"></i>
                                    </div>
    
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-800">
                                            Uraian Pembelajaran
                                        </h3>
    
                                        <p class="mt-1 text-sm text-slate-500">
                                            Tuliskan kegiatan pembelajaran yang telah dilaksanakan pada sesi ini.
                                        </p>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Body -->
                            <div class="space-y-8 p-6">
    
                                <!-- Materi -->
                                <div>
    
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700">
    
                                        Uraian KBM
                                        <span class="text-red-500">*</span>
    
                                    </label>
                                    <textarea id="learning_activity" name="learning_activity" rows="8" class="w-full rounded-2xl border border-slate-300 px-5 py-4 text-sm 
                                        leading-7 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                        placeholder="Contoh:&#10;• Menjelaskan Sistem Persamaan Linear Dua Variabel.&#10;• Memberikan contoh penyelesaian soal.&#10;• Diskusi kelompok.&#10;• Latihan mandiri halaman 25."></textarea>
                                    <span id="error-learning_activity" class="text-red-500 text-xs mt-1 font-bold"></span>
    
                                    <p class="mt-2 text-xs text-slate-500">
                                        Tuliskan materi atau aktivitas pembelajaran dalam bentuk poin agar lebih mudah dibaca.
                                    </p>
                                </div>
                            </div>
    
                            <!-- Footer -->
                            <div
                                class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">
    
                                <button type="button" id="submit-button-create-daily-agenda"
                                    class="rounded-xl bg-blue-600 px-6 py-3 font-medium text-white transition hover:bg-blue-700
                                    cursor-pointer disabled:cursor-default">
    
                                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                                    Simpan Agenda
                                </button>
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

<script src="{{ asset('assets/js/features/lms/teacher/daily-agenda/teacher-daily-agenda-form.js') }}"></script> <!--- paginate daily agenda form ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->