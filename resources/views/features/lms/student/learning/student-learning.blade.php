@include('components/sidebar-beranda', [
    'headerSideNav' => 'Learning',
    'linkBackButton' => route('lms.student.view', [$role, $schoolName, $schoolId, $mapelId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>
                <section>
                    @include('features/lms/student/components/subject-header-progress')

                    <div id="container-paginate-student-learning" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                        data-curriculum-id="{{ $curriculumId }}" data-mapel-id="{{ $mapelId }}">

                        <div id="grid-list-student-learning" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-14 mt-10">
                            <!-- show data in ajax -->
                        </div>

                    </div>

                    <div id="empty-message-list-student-learning" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada data.
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

<script src="{{ asset('assets/js/features/lms/student/learning/paginate-student-learning.js') }}"></script> <!-- - paginate student learning ---->