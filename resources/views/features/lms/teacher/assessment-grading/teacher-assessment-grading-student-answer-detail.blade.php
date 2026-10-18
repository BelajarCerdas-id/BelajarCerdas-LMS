@include('components/sidebar-beranda', [
    'headerSideNav' => 'Scoring',
    'linkBackButton' => route('lms.assessmentGradingStudentList.view', [$role, $schoolName, $schoolId, $rootAssessmentId, 'main']),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5">

            <div id="alert-success-assessment-grading"></div>

            <main>
                <section id="container-assessment-grading-student-answer" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                    data-assessment-id="{{ $assessmentId }}" data-mode="{{ $mode }}" data-student-id="{{ $studentId }}">

                    <!-- HEADER -->
                    <div id="header-assessment-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-5 md:p-7 mb-8 shadow-lg">
                        <!-- show assessment info in ajax -->
                    </div>

                    <div id="form-assessment-grading">
                        <!-- show form in ajax -->
                    </div>
                </section>

                <div id="empty-message-school-assessment-question" class="w-full h-80 hidden">
                    <span class="flex h-full items-center justify-center text-gray-500">
                        Tidak ada soal yang terdaftar pada Assessment ini.
                    </span>
                </div>
            </main>

            <!-- Modal Preview Gambar Jawaban -->
            <dialog id="modal-image-preview" class="modal">
                <div class="modal-box bg-white max-w-4xl p-0 overflow-hidden rounded-2xl shadow-2xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#0071BC] flex items-center justify-center text-sm">
                                <i class="fa-solid fa-image"></i>
                            </div>
                            <div>
                                <h3 id="modal-preview-title" class="font-bold text-gray-800 text-sm">Pratinjau Gambar Jawaban Siswa</h3>
                                <p class="text-[11px] text-gray-500">Klik tombol tutup atau latar belakang untuk kembali</p>
                            </div>
                        </div>
                        <form method="dialog">
                            <button class="btn btn-circle btn-sm btn-ghost text-gray-500 hover:bg-gray-200">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </form>
                    </div>
                    <div class="p-4 bg-gray-900/5 flex items-center justify-center min-h-[300px] max-h-[75vh] overflow-auto">
                        <img id="modal-preview-img" src="" alt="Pratinjau Gambar" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-sm border border-gray-200">
                    </div>
                    <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                        <a id="modal-preview-open-tab" href="" target="_blank" rel="noopener noreferrer" class="text-xs text-[#0071BC] hover:underline flex items-center gap-1.5 font-medium">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka di Tab Baru
                        </a>
                        <form method="dialog">
                            <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                Tutup
                            </button>
                        </form>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop bg-black/50">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/teacher/assessment-grading/paginate-teacher-assessment-grading-student-answer-detail.js') }}"></script> <!--- paginate teacher assessment grading student answer ---->