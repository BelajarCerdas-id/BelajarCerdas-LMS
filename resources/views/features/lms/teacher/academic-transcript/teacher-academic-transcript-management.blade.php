@include('components/sidebar-beranda', [
    'headerSideNav' => 'Transkrip Nilai',
    'linkBackButton' => route('lms.teacherClassListAcademicTranscript.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

        <div class="my-15 mx-7.5">
            <main>
                <!-- HEADER -->
                <div id="header-academic-transcript-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-6 md:p-8 mb-8 shadow-lg hidden">
                    <!-- show header in ajax -->
                </div>

                <section class="bg-white border border-gray-300 shadow-lg rounded-lg ">

                    <!-- filter -->
                    <div class="bg-gray-50 shadow-sm border border-gray-300 rounded-lg p-6">

                        <!-- Header Filter -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                <h3 class="text-base font-semibold text-gray-800">
                                    Filter Transkrip
                                </h3>
                            </div>
                        </div>

                        <!-- Filter Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

                            <div id="container-dropdown-class-paginate-question-bank-for-release"></div>

                        </div>
                    </div>

                    <div id="container-teacher-academic-transcript-management" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                        data-subject-teacher-id="{{ $subjectTeacherId }}" class="overflow-x-auto mt-6 pb-20">
                        <table id="table-teacher-academic-transcript-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-teacher-academic-transcript-management bg-gray-50 shadow-inner">
                                <!-- show th in ajax -->
                            </thead>
                            <tbody id="tbody-teacher-academic-transcript-management">  
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
        
                    <div class="pagination-container-teacher-teacher-academic-transcript-management flex justify-center my-10"></div>
        
                    <div id="empty-message-teacher-academic-transcript-management" class="w-full h-80 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada data yang ditemukan.
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

<script src="{{ asset('assets/js/features/lms/teacher/academic-transcript/paginate-teacher-academic-transcript-management.js') }}"></script> <!--- paginate teacher academic transcript management ---->