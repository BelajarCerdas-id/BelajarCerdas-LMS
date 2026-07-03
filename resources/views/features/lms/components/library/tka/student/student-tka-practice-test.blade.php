@include('components.navbar-tka-practice-test')

@if (Auth::user()->role === 'Siswa')
    <main>
        <section id="container-tka-practice-test-form" data-role="{{ $role }}" data-kelas-id="{{ $kelasId }}" data-mapel-id="{{ $mapelId }}">

            <!-- Loading -->
            <div id="practice-loading" class="hidden">

                <div class="max-w-450 mx-auto px-4 sm:px-6 lg:px-8 mt-6 lg:mt-10">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14">

                        <!-- LEFT -->
                        <div class="lg:col-span-8">

                            <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.04)]
                                min-h-140 flex flex-col justify-center items-center px-8">

                                <div
                                    class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center">

                                    <i class="fa-solid fa-spinner fa-spin text-4xl text-[#0071BC]"></i>

                                </div>

                                <h2 class="text-2xl font-bold text-gray-800 mt-8">

                                    Memuat Data Latihan

                                </h2>

                                <p class="text-center text-gray-500 mt-4 max-w-lg leading-relaxed">
                                    Mohon tunggu beberapa saat.
                                </p>
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="lg:col-span-4">

                            <div class="bg-white border border-gray-200 rounded-3xl
                                shadow-[0_10px_40px_rgba(0,0,0,0.05)]
                                p-6 animate-pulse">

                                <!-- Profile Skeleton -->

                                <div class="border border-gray-200 rounded-2xl p-5">

                                    <div class="h-4 bg-gray-200 rounded w-36"></div>

                                    <div class="h-3 bg-gray-200 rounded w-24 mt-3"></div>

                                </div>

                                <!-- Number Skeleton -->

                                <div class="grid grid-cols-5 gap-3 mt-8">
                                    @for($i = 0; $i < 20; $i++)

                                        <div class="h-10 rounded-lg bg-gray-200"></div>

                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Placeholder -->
            <div id="practice-placeholder" class="hidden">

                <div class="max-w-450 mx-auto px-4 sm:px-6 lg:px-8 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14">

                    <!-- LEFT -->

                    <div class="lg:col-span-8">

                        <div class="bg-white rounded-2xl border border-gray-200
                            shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-8">

                            <div
                                class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mx-auto">

                                <i class="fa-solid fa-book-open text-3xl text-[#0071BC]"></i>

                            </div>

                            <h2 class="text-2xl font-bold text-center mt-6">

                                Latihan Belum Dimulai

                            </h2>

                            <p class="text-gray-500 text-center mt-4 leading-relaxed max-w-2xl mx-auto">

                                Soal belum ditampilkan karena latihan belum dimulai.
                                Setelah memilih <strong>Mulai Latihan</strong>,
                                soal akan diacak dan waktu pengerjaan akan mulai dihitung.
                            </p>

                            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-5">

                                <div class="flex items-center gap-2 mb-4">

                                    <i class="fa-solid fa-circle-info text-[#0071BC]"></i>

                                    <span class="font-semibold text-[#0071BC]">
                                        Informasi Latihan
                                    </span>
                                </div>

                                <ul class="space-y-3 text-sm text-gray-700">

                                    <li class="flex gap-3">
                                        <i class="fa-solid fa-check text-green-600 mt-1"></i>
                                        <span>Soal akan diacak secara otomatis ketika latihan dimulai.</span>
                                    </li>

                                    <li class="flex gap-3">
                                        <i class="fa-solid fa-check text-green-600 mt-1"></i>
                                        <span>Waktu pengerjaan mulai dihitung setelah latihan dimulai.</span>
                                    </li>

                                    <li class="flex gap-3">
                                        <i class="fa-solid fa-check text-green-600 mt-1"></i>
                                        <span>Nomor soal akan ditampilkan setelah latihan dimulai.</span>
                                    </li>

                                    <li class="flex gap-3">
                                        <i class="fa-solid fa-check text-green-600 mt-1"></i>
                                        <span>Jawaban yang telah disimpan tidak dapat diubah kembali.</span>
                                    </li>

                                    <li class="flex gap-3">
                                        <i class="fa-solid fa-check text-green-600 mt-1"></i>
                                        <span>Pembahasan dapat dilihat setelah seluruh soal selesai dikerjakan.</span>
                                    </li>

                                </ul>

                            </div>

                            <p class="text-center text-sm text-gray-400 mt-8">
                                Pilih <strong>Mulai Latihan</strong> pada jendela konfirmasi untuk memulai.
                            </p>
                        </div>
                    </div>

                    <!-- RIGHT -->

                    <div class="lg:col-span-4">

                        <div class="bg-white rounded-3xl border border-gray-200
                            shadow-[0_10px_40px_rgba(0,0,0,0.05)]
                            p-6 flex items-center h-full">

                            <div class="text-center w-full">

                                <div
                                    class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto">

                                    <i class="fa-solid fa-list-ol text-2xl text-gray-500"></i>
                                </div>

                                <h3 class="font-semibold text-lg mt-5">
                                    Nomor Soal
                                </h3>

                                <p class="text-sm text-gray-500 mt-3 leading-relaxed">
                                    Daftar nomor soal akan tersedia setelah latihan dimulai.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="practice-content" class="hidden">
                <div id="form-tka-practice-test">
                    <!-- AJAX -->
                </div>
            </div>

            <div id="practice-result" class="hidden mx-10 my-8">

                <!-- ================= HERO SECTION ================= -->
                <div class="relative w-full min-h-100 bg-[#0071BC] p-10 text-white overflow-visible flex items-center shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
                    style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}'); background-size: cover; background-position: center;">

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-center w-full">
                                <!-- LEFT -->
                                <div class="text-center lg:text-left">
                                    <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center mx-auto lg:mx-0">
                                        <i class="fa-solid fa-trophy text-4xl text-yellow-300"></i>
                                    </div>

                                    <h1 class="text-2xl font-black mt-2 leading-tight">
                                        Kamu Berhasil Menyelesaikan
                                        <br>
                                        Latihan TKA
                                    </h1>

                                    <p class="text-blue-100 mt-5 max-w-xl leading-relaxed">
                                        Seluruh soal telah berhasil dikerjakan.
                                        Kamu dapat melihat hasil latihan,
                                        mempelajari pembahasan setiap soal,
                                        atau mengulang latihan dengan soal yang akan
                                        diacak kembali.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ================= MAIN STATS ================= -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mt-8">

                            <!-- LEFT COLUMN -->
                            <div class="space-y-8 lg:col-span-2 xl:col-span-1">

                                <div class="text-sm font-bold bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-xl p-5 flex gap-4 items-center justify-between
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">
                                    <span>Jumlah Soal Yang Harus Dijawab</span>
                                    <span id="result-total-question" class="font-bold text-lg">0</span>
                                </div>

                                <div class="text-sm font-bold bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-xl p-5 flex gap-4 items-center justify-between
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">
                                    
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                            <i class="fa-solid fa-minus text-white text-xs"></i>
                                        </div>
                                        <span>Jumlah Soal Terjawab</span>
                                    </div>

                                    <span id="result-total-answered" class="font-bold text-lg">
                                        0
                                    </span>
                                </div>

                                <div class="text-sm font-bold bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-xl p-5 flex gap-4 items-center justify-between
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-gray-500 flex items-center justify-center">
                                            <i class="fa-solid fa-minus text-white text-xs"></i>
                                        </div>
                                        <span>Jumlah Soal Tidak Terjawab</span>
                                    </div>

                                    <span id="result-total-unanswered" class="font-bold text-lg">
                                        0
                                    </span>
                                </div>
                            </div>

                            <!-- MIDDLE COLUMN -->
                            <div class="space-y-8">

                                <div class="text-center flex flex-col items-center justify-center bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-xl px-6 h-32
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                            <i class="fa-solid fa-check text-white text-xs"></i>
                                        </div>
                                        <p id="result-total-correct" class="text-2xl font-bold">0</p>
                                    </div>

                                    <p class="text-sm mt-2 font-bold">Jumlah Soal Benar</p>
                                </div>

                                <div class="text-center flex flex-col items-center justify-center bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-xl px-6 h-32
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center">
                                            <i class="fa-solid fa-xmark text-white text-xs"></i>
                                        </div>
                                        <p id="result-total-wrong" class="text-2xl font-bold">0</p>
                                    </div>

                                    <p class="text-sm mt-2 font-bold">Jumlah Soal Salah</p>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN (SCORE CARD) -->
                            <div class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-8 flex flex-col justify-center items-center
                                shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)] relative">

                                <!-- STATUS BADGE -->
                                <div class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Nilai Final
                                </div>

                                <div id="result-total-score" class="text-6xl font-extrabold mt-6">
                                    0
                                </div>

                                <p class="text-md font-bold mt-2 text-center">
                                    Nilai Siswa
                                </p>
                            </div>
                        </div>

                        <!-- ================= ACTION BUTTON ================= -->
                        <div class="border-t border-gray-200 mt-10 pt-8">
                            <div class="flex flex-col lg:flex-row justify-center gap-5">

                                <!-- Lihat Pembahasan -->
                                <button id="btn-review-practice" class="bg-[#0071BC] hover:bg-[#005f99] text-white px-8 py-4 rounded-xl font-semibold shadow-lg 
                                    transition-all duration-200 hover:scale-105 flex items-center justify-center gap-3 cursor-pointer">

                                    <i class="fa-solid fa-book-open text-lg"></i>
                                    <span>Lihat Pembahasan</span>
                                </button>

                                <!-- Mulai Latihan Lagi -->
                                <button
                                    id="btn-restart-practice" class="border-2 border-[#0071BC] text-[#0071BC] hover:bg-[#0071BC] hover:text-white px-8 py-4 rounded-xl 
                                    font-semibold transition-all duration-200 hover:scale-105 flex items-center justify-center gap-3 cursor-pointer">

                                    <i class="fa-solid fa-rotate-right text-lg"></i>
                                    <span>Mulai Latihan Lagi</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="empty-message-assessment-form" class="hidden">

                <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">

                    <div class="bg-white border border-gray-200 rounded-2xl
                        shadow-[0_8px_30px_rgba(0,0,0,0.04)]
                        p-10 text-center">

                        <div
                            class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto">

                            <i class="fa-solid fa-folder-open text-4xl text-[#0071BC]"></i>

                        </div>

                        <h2 class="text-2xl font-bold text-gray-800 mt-6">

                            Soal Belum Tersedia

                        </h2>

                        <p class="text-gray-500 leading-relaxed max-w-xl mx-auto mt-4">
                            Belum terdapat soal latihan yang dapat dikerjakan pada mata pelajaran ini.
                        </p>
                    </div>
                </div>
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

<script src="{{ asset('assets/js/features/lms/student/library/tka/practice-test/student-form-tka-practice-test.js') }}"></script> <!--- student form tka practice test ---->