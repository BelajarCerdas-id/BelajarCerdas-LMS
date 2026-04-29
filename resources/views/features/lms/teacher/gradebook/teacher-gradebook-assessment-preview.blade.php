@include('components/sidebar-beranda', [
    'headerSideNav' => 'Gradebook Preview',
    'linkBackButton' => route('lms.teacherGradebook.view', [$role, $schoolName, $schoolId, $subjectTeacherId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
    <div class="my-10 mx-6">

        <div id="alert-success-final-score-input"></div>

        <!-- HEADER -->
        <div id="header-gradebook-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-6 md:p-8 mb-8 shadow-lg hidden">
            <!-- show header in ajax -->
        </div>

        <div id="container-bulk-action-final-score-input" class="hidden"></div>

        <section>
            <div id="container-teacher-gradebook-assessment-preview" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                data-subject-teacher-id="{{ $subjectTeacherId }}" data-assessment-type-id="{{ $assessmentTypeId }}" data-student-id="{{ $studentId }}" data-semester="{{ $semester }}"
                class="overflow-x-auto mt-6 pb-20">
                <table id="table-teacher-gradebook-assessment-preview" class="min-w-full text-sm border-collapse">
                    <thead class="thead-table-teacher-gradebook-assessment-preview bg-gray-50 shadow-inner">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                <input type="checkbox" id="check-all" class="cursor-pointer">
                            </th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Siswa</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Asesmen</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Judul Asesmen</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tanggal Ujian</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-teacher-gradebook-assessment-preview">
                        <!-- show data in ajax -->
                    </tbody>
                </table>
            </div>

            <div id="empty-message-teacher-gradebook-assessment-preview" class="w-full h-80 hidden">
                <span class="flex h-full items-center justify-center text-gray-500">
                    Tidak ada asesmen yang terdaftar.
                </span>
            </div>
        </section>
    </div>
</div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/teacher/gradebook/paginate-teacher-gradebook-assessment-preview.js') }}"></script> <!--- paginate teacher gradebook assessment preview ---->