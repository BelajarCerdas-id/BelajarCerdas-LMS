@include('components.navbar-assessment-test')

@if (Auth::user()->role === 'Siswa')
    <main>
        <section id="container-assessment-test-form" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-curriculum-id="{{ $curriculumId }}" 
            data-mapel-id="{{ $mapelId }}" data-assessment-type-id="{{ $assessmentTypeId }}" data-semester="{{ $semester }}" data-assessment-id="{{ $assessmentId }}"
            data-upload-url="{{ route('assessment-test.storeImage', ['_token' => csrf_token()]) }}"
            data-delete-url="{{ route('assessment-test.deleteImage') }}">
            
            <div id="form-assessment-test">
                <!-- form in ajax -->
            </div>

            <div id="empty-message-assessment-form" class="w-full h-96 hidden">
                <span class="flex h-full items-center justify-center text-gray-500">
                    Tidak ada soal yang terdaftar pada asesmen ini.
                </span>
            </div>
        </section>

        <dialog id="modal-explanation" class="modal">

            <div class="modal-box bg-white w-11/12 max-w-4xl p-0 overflow-hidden">

                <!-- Header -->
                <div
                    class="relative bg-linear-to-r from-[#0071BC] via-[#1D8FE1] to-[#4189E0] px-6 py-5">

                    <div class="absolute top-0 right-0 opacity-10 text-white text-8xl">
                        <i class="fas fa-book-open"></i>
                    </div>

                    <div class="flex justify-between items-start relative z-10">

                        <div class="flex gap-4">

                            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white">
                                <i class="fas fa-lightbulb text-xl"></i>
                            </div>

                            <div>

                                <h2 class="text-xl font-bold text-white">
                                    Pembahasan Soal
                                </h2>

                                <p class="text-blue-100 text-sm mt-1">
                                    Pelajari konsep, langkah pengerjaan, dan alasan mengapa jawaban tersebut benar.
                                </p>

                            </div>

                        </div>

                        <form method="dialog">
                            <button class="btn btn-circle btn-sm btn-ghost text-white hover:bg-white/20">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Body -->
                <div class="max-h-[70vh] overflow-y-auto">

                    <div class="p-6 space-y-5">

                        <!-- Pembahasan -->
                        <div class="border border-gray-200 rounded-2xl overflow-hidden">
                            <div class="bg-gray-50 border-b border-gray-200 px-5 py-3">

                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-book text-[#0071BC]"></i>
                                    Pembahasan Lengkap
                                </h3>
                            </div>

                            <div
                                id="explanation-content"
                                class="p-5 text-gray-700 leading-8 list-style">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex justify-end items-center">
                    <form method="dialog">
                        <button class="btn bg-[#0071BC] hover:bg-[#005A96] border-0 text-white">
                            Tutup
                        </button>
                    </form>
                </div>
            </div>
            
            <form method="dialog" class="modal-backdrop">
                <button>Close</button>
            </form>
        </dialog>
    </main>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student/assessment/student-form-assessment-test.js') }}"></script> <!--- student form assessment test ---->
<script src="{{ asset('assets/js/features/lms/student/assessment/start-timer-assessment-test-by-question.js') }}"></script> <!--- start timer assessment test by question ---->
<script src="{{ asset('assets/js/features/lms/student/assessment/student-assessment-matching-renderer.js') }}"></script> <!--- student assessment matching renderer ---->