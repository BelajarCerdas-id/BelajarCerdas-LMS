@include('components/sidebar-beranda', [
    'headerSideNav' => 'Leger Nilai',
    'linkBackButton' => route('lms.teacherClassListGradeLedger.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

        <div class="my-15 mx-7.5">
            <main>
                <!-- HEADER -->
                <div id="header-ledger-grade-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-6 md:p-8 mb-8 shadow-lg hidden">
                    <!-- show header in ajax -->
                </div>

                <section>
                    <div id="container-teacher-grade-ledger-management" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                        data-subject-teacher-id="{{ $subjectTeacherId }}" class="overflow-x-auto mt-6 pb-20">
                        <table id="table-teacher-grade-ledger-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-teacher-grade-ledger-management bg-gray-50 shadow-inner">
                                <!-- show th in ajax -->
                            </thead>
                            <tbody id="tbody-teacher-grade-ledger-management">  
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
        
                    <div class="pagination-container-teacher-teacher-grade-ledger-management flex justify-center my-10"></div>
        
                    <div id="empty-message-teacher-grade-ledger-management" class="w-full h-80 hidden">
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

<script src="{{ asset('assets/js/features/lms/teacher/grade-ledger/paginate-teacher-grade-ledger-management.js') }}"></script> <!--- paginate teacher grade ledger management ---->