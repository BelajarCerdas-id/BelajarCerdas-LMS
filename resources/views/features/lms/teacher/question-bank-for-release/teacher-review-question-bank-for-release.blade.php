@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Questions',
    'linkBackButton' => route('lms.teacherQuestionBankForRelease.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main class="mb-10 space-y-6">
                <section id="container-paginate-teacher-review-question-bank-for-release-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                    data-assessment-question-id="{{ $assessmentQuestionId }}"
                    class="mt-10">

                    <h2 class="text-xl font-semibold text-gray-800">
                        Review Question Bank For Release
                    </h2>

                    <div id="grid-list-soal" class="container-accordion mb-8">
                        <!-- show data in ajax -->
                    </div>

                    <div class="pagination-container-paginate-teacher-review-question-bank-for-release-list flex justify-center my-10"></div>

                    <div id="empty-message-paginate-teacher-review-question-bank-for-release-list" class="w-full h-96 hidden">
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

<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/paginate-teacher-review-question-bank-for-release.js') }}"></script> <!--- paginate review question bank for release ---->