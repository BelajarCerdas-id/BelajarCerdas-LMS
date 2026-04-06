@include('components/sidebar-beranda', [
    'headerSideNav' => 'Assessment',
    'linkBackButton' => route('lms.studentLearning.view', [$role, $schoolName, $schoolId, $curriculumId, $mapelId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
        
            <div id="alert-success-project-submission"></div>

            <main>
                <section>
                    @include('features/lms/student/components/subject-header-progress')

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

                    <!-- POPUP CARD -->
                    <div id="container-load-assessment-schedule" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                        data-curriculum-id="{{ $curriculumId }}" data-mapel-id="{{ $mapelId }}" data-assessment-type-id="{{ $assessmentTypeId }}"
                        data-mode="{{ $mode }}" data-parent-assessment-id="{{ $parentAssessmentId }}">

                        <!-- CONTENT -->
                        <div id="load-content-assessment-schedule" class="flex flex-col gap-6"> 
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-load-assessment-schedule" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada asesmen yang terjadwal.
                        </span>
                    </div>
                </section>
            </main>

            <dialog id="studentFilePreviewModal" class="modal">
                <div class="modal-box max-w-6xl">

                    <h3 class="font-bold text-lg mb-4">
                        Preview File
                    </h3>

                    <div id="student-file-preview-content"></div>

                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn">Tutup</button>
                        </form>
                    </div>

                </div>
            </dialog>

        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student/assessment/student-preview-assessment-schedule.js') }}"></script> <!--- student preview assessment schedule ---->