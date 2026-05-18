@include('components/sidebar-beranda', [
    'linkBackButton' => route('lms.teacherClassListSubjectAttendance.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Meeting List',
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <section>
            <div class="bg-white px-6 py-6 md:px-8 border-b border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-125 h-125 bg-linear-to-bl from-slate-50 to-transparent rounded-full -translate-y-1/2 
                    translate-x-1/4 opacity-80 pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl shadow-sm border border-indigo-100 shrink-0">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="flex flex-col justify-center">
                                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                    Kelas {{ $subjectTeacher->SchoolClass->class_name ?? 'Siswa' }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
    
                                    <!-- MAPEL -->
                                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                                        <i class="fas fa-book-open"></i>
                                        {{ $subjectTeacher->Mapel->mata_pelajaran ?? '-' }}
                                    </span>
    
                                    <!-- TAHUN AJARAN -->
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold border border-emerald-100">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $subjectTeacher->SchoolClass->tahun_ajaran ?? '-' }}
                                    </span>
    
                                    <!-- WALI KELAS -->
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1 rounded-lg text-xs font-bold border border-amber-100">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        {{ $subjectTeacher->UserAccount->SchoolStaffProfile->nama_lengkap ?? '-' }}
                                    </span>
    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="my-15 mx-7.5">
            <main>
                <section>
                    <!-- TOP NAV -->
                    <div class="w-full mb-6 mt-10">
                        <!-- Semester Tabs -->
                        <div class="flex my-10 max-w-xl mx-auto">
                            <div class="w-full hover:bg-gray-100" onclick="changeSemester(1)">
                                <input type="radio" class="hidden" name="radio" id="radio1" checked>
                                <div class="checked-timeline">
                                    <label for="radio1" class="cursor-pointer flex flex-col gap-2">
                                        <span class="text-md flex justify-center relative top-1 font-bold opacity-70">Semester 1</span>
                                        <div class="w-full border-b border-gray-300 h-2"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="w-full hover:bg-gray-100" onclick="changeSemester(2)">
                                <input type="radio" class="hidden" name="radio" id="radio2">
                                <div class="checked-timeline">
                                    <label for="radio2" class="cursor-pointer flex flex-col gap-2">
                                        <span class="text-md flex justify-center relative top-1 font-bold opacity-70">Semester 2</span>
                                        <div class="w-full border-b border-gray-300 h-2"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="container-review-meeting" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                        data-subject-teacher-id="{{ $subjectTeacherId }}">

                        <div id="grid-list-review-meeting" class="grid grid-cols-1 lg:grid-cols-2 gap-14 mt-10">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-review-content" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada content yang terdaftar.
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

<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/paginate/paginate-subject-attendance-meeting-list.js') }}"></script> <!--- paginate subject attendance meeting list ---->