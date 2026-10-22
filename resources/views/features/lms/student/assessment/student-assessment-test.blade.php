@include('components.navbar-assessment-test')

@if (Auth::user()->role === 'Siswa')
    <main translate="no" class="notranslate">
        <section id="container-assessment-test-form" translate="no" class="notranslate" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-curriculum-id="{{ $curriculumId }}" 
            data-mapel-id="{{ $mapelId }}" data-assessment-type-id="{{ $assessmentTypeId }}" data-semester="{{ $semester }}" data-assessment-id="{{ $assessmentId }}"
            data-upload-url="{{ route('assessment-test.storeImage', ['_token' => csrf_token()]) }}"
            data-delete-url="{{ route('assessment-test.deleteImage') }}">
            
            <div id="form-assessment-test">
                <!-- form in ajax -->
            </div>

            <div id="empty-message-assessment-form" class="w-full h-96 hidden">
                <span class="flex h-full items-center justify-center text-gray-500">
                    Tidak ada soal yang terdaftar pada assessment ini.
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

        <!-- Modal Preview Gambar Jawaban -->
        <dialog id="modal-image-preview" class="modal">
            <div class="modal-box bg-white max-w-4xl p-0 overflow-hidden rounded-2xl shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#0071BC] flex items-center justify-center text-sm">
                            <i class="fa-solid fa-image"></i>
                        </div>
                        <div>
                            <h3 id="modal-preview-title" class="font-bold text-gray-800 text-sm">Pratinjau Gambar Jawaban</h3>
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

        <!-- Modal Kamera Web (In-Web Camera Capture Only) -->
        <dialog id="modal-web-camera" class="modal">
            <div class="modal-box bg-slate-900 text-white max-w-2xl p-0 overflow-hidden rounded-2xl shadow-2xl border border-slate-700">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-3.5 bg-slate-800/90 border-b border-slate-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center text-sm border border-blue-500/30">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm text-white">Ambil Foto Jawaban Langsung</h3>
                                <span id="modal-web-camera-count" class="hidden text-[10px] px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 font-medium">
                                    0 foto terlampir
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400">Anda dapat mengambil dan melampirkan lebih dari satu foto lembar jawaban</p>
                        </div>
                    </div>
                    <button type="button" id="btn-close-web-camera" class="btn btn-circle btn-sm btn-ghost text-slate-400 hover:text-white hover:bg-slate-700">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Camera Viewfinder / Preview Container -->
                <div class="relative bg-black flex items-center justify-center min-h-[340px] sm:min-h-[420px] max-h-[62vh] overflow-hidden select-none">
                    <!-- Live Video Element -->
                    <video id="web-camera-video" autoplay playsinline muted class="w-full h-full max-h-[62vh] object-contain"></video>

                    <!-- Captured Canvas (Hidden, used for processing) -->
                    <canvas id="web-camera-canvas" class="hidden"></canvas>

                    <!-- Captured Image Preview (Shown after taking photo) -->
                    <img id="web-camera-captured-img" src="" alt="Hasil Foto" class="hidden w-full h-full max-h-[62vh] object-contain">

                    <!-- Camera Framing Overlay (Visible during live stream) -->
                    <div id="web-camera-overlay" class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-4 sm:p-6">
                        <div class="bg-black/60 backdrop-blur-xs text-white text-xs px-3 py-1.5 rounded-full border border-white/20">
                            <i class="fa-solid fa-crop-simple mr-1"></i> Arahkan kamera ke lembar jawaban Anda
                        </div>
                        <div class="w-full max-w-md h-44 sm:h-56 border-2 border-dashed border-white/40 rounded-xl pointer-events-none"></div>
                        <div class="text-[11px] text-white/80 bg-black/50 px-3 py-1 rounded-full">
                            Pastikan tulisan terbaca jelas dan pencahayaan cukup
                        </div>
                    </div>

                    <!-- Camera Error / Permission Notice -->
                    <div id="web-camera-error" class="hidden absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-6 text-center">
                        <div class="w-14 h-14 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center text-2xl mb-3 border border-red-500/30">
                            <i class="fa-solid fa-video-slash"></i>
                        </div>
                        <h4 class="font-bold text-base text-white mb-1">Kamera Tidak Dapat Diakses</h4>
                        <p id="web-camera-error-msg" class="text-xs text-slate-300 max-w-sm mb-4">
                            Harap izinkan akses kamera pada peramban Anda untuk mengambil foto jawaban langsung.
                        </p>
                        <button type="button" id="btn-retry-web-camera" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold transition cursor-pointer">
                            <i class="fa-solid fa-rotate mr-1.5"></i> Coba Lagi
                        </button>
                    </div>

                    <!-- Loading / Processing Spinner -->
                    <div id="web-camera-loading" class="hidden absolute inset-0 bg-black/75 flex flex-col items-center justify-center gap-2.5 z-20">
                        <div class="animate-spin text-blue-400 text-3xl">
                            <i class="fa-solid fa-circle-notch"></i>
                        </div>
                        <span id="web-camera-loading-text" class="text-xs text-white font-medium">Menghubungkan kamera...</span>
                    </div>
                </div>

                <!-- Footer / Camera Controls -->
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 bg-slate-800 border-t border-slate-700 flex flex-wrap sm:flex-nowrap items-center justify-between gap-3">
                    <!-- Left: Switch camera button (front/back on phone or multiple webcams) -->
                    <div class="order-1 sm:order-none">
                        <button type="button" id="btn-switch-camera" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-medium transition cursor-pointer border border-slate-600" title="Ganti Kamera Depan / Belakang">
                            <i class="fa-solid fa-camera-rotate"></i>
                            <span class="hidden sm:inline">Ganti Kamera</span>
                        </button>
                    </div>

                    <!-- Center: Action Controls -->
                    <div class="w-full sm:w-auto order-3 sm:order-none flex items-center justify-center gap-2 sm:gap-3">
                        <!-- Live State: Shutter Button & Finish (if photos exist) -->
                        <div id="web-camera-live-controls" class="flex items-center gap-2.5">
                            <button type="button" id="btn-capture-photo" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-600/30 transition cursor-pointer">
                                <i class="fa-solid fa-camera text-base"></i>
                                <span>Ambil Foto</span>
                            </button>
                            <button type="button" id="btn-finish-web-camera" class="hidden inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/30 transition cursor-pointer">
                                <i class="fa-solid fa-check"></i>
                                <span>Selesai</span>
                            </button>
                        </div>

                        <!-- Preview State: Retake / Save & Take Another / Save & Finish -->
                        <div id="web-camera-preview-controls" class="hidden flex flex-wrap items-center justify-center gap-2">
                            <button type="button" id="btn-retake-photo" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-xs font-semibold transition cursor-pointer border border-slate-600">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Foto Ulang</span>
                            </button>
                            <button type="button" id="btn-use-photo-and-continue" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white rounded-lg text-xs font-semibold shadow-md shadow-blue-600/30 transition cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                                <span>Simpan & Ambil Lagi</span>
                            </button>
                            <button type="button" id="btn-use-photo" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white rounded-lg text-xs font-semibold shadow-md shadow-emerald-600/30 transition cursor-pointer">
                                <i class="fa-solid fa-check"></i>
                                <span>Simpan & Selesai</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Cancel / Close Button -->
                    <div class="order-2 sm:order-none">
                        <button type="button" id="btn-cancel-web-camera" class="px-3.5 py-2 text-slate-400 hover:text-white rounded-lg text-xs font-medium transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-black/70">
                <button id="btn-backdrop-web-camera">close</button>
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
<script src="{{ asset('assets/js/features/lms/student/components/question-types/matching-question-renderer.js') }}"></script> <!--- matching question renderer ---->