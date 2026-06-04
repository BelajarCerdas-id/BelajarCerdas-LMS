@include('components/sidebar-beranda', [
    'headerSideNav' => 'Detail',
    'linkBackButton' => route('lms.student-vice-principal.reflection-management-history.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role == 'Wakil Kesiswaan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out overflow-x-hidden">

        <div id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-reflection-question-id="{{ $reflectionQuestionId }}"
            class="p-4 sm:p-6 md:p-8 xl:p-10 mx-auto space-y-8">

            <!-- HERO -->
            <div class="relative overflow-hidden rounded-4xl md:rounded-[2.5rem] bg-[#0071BC] p-6 sm:p-8 md:p-10 text-white
                shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
                style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}');
                background-size: cover;
                background-position: center;">

                <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-20 w-40 h-40 rounded-full bg-cyan-300/10 blur-3xl"></div>

                <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-8">

                    <div class="max-w-4xl">

                        <div class="flex flex-wrap items-center gap-3 mb-4">

                            <div class="px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20
                                text-[10px] sm:text-xs font-bold uppercase tracking-widest">

                                Detail Refleksi

                            </div>

                            <div class="flex items-center gap-2 text-xs sm:text-sm text-blue-100">

                                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>

                                Data Refleksi Tersimpan

                            </div>
                        </div>

                        <h1 id="reflection-title"
                            class="text-3xl md:text-4xl font-black leading-tight">
                            <div class="h-10 w-80 bg-white/20 rounded animate-pulse"></div>
                        </h1>

                        <p id="reflection-question"
                            class="mt-4 text-blue-100 max-w-3xl leading-relaxed text-sm md:text-base font-medium">
                            <span class="block h-4 w-full bg-white/20 rounded animate-pulse"></span>
                            <span class="block h-4 w-4/5 bg-white/20 rounded animate-pulse mt-2"></span>
                        </p>

                        <!-- META -->
                        <div id="reflection-meta-wrapper" class="flex flex-wrap gap-3 mt-6">

                            <div class="h-11 w-40 rounded-2xl bg-white/15 animate-pulse"></div>

                            <div class="h-11 w-44 rounded-2xl bg-white/15 animate-pulse"></div>

                            <div class="h-11 w-64 rounded-2xl bg-white/15 animate-pulse"></div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- SUMMARY -->
            <div id="reflection-summary-wrapper"
                class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                <!-- TOTAL RESPONDEN -->
                <div class="bg-white rounded-4xl border border-gray-300 p-6">

                    <div id="summary-skeleton-total">
                        <div class="animate-pulse">
                            <div class="h-4 w-28 bg-slate-200 rounded mb-4"></div>
                            <div class="h-10 w-32 bg-slate-200 rounded mb-3"></div>
                            <div class="h-3 w-24 bg-slate-200 rounded"></div>
                        </div>
                    </div>

                    <div id="summary-total-content" class="hidden">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 font-medium">
                                    Total Responden
                                </p>

                                <h3 id="summary-total-responden"
                                    class="text-3xl font-black text-slate-800 mt-2">
                                </h3>

                                <p id="summary-participation"
                                    class="text-xs text-emerald-600 font-semibold mt-2">
                                </p>

                            </div>

                            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center">
                                <i class="fa-solid fa-users text-xl text-[#2563EB]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POSITIVE -->
                <div class="bg-white rounded-4xl border border-gray-300 p-6">

                    <div id="summary-skeleton-positive">
                        <div class="animate-pulse">
                            <div class="h-4 w-28 bg-slate-200 rounded mb-4"></div>
                            <div class="h-10 w-24 bg-slate-200 rounded"></div>
                        </div>
                    </div>

                    <div id="summary-positive-content" class="hidden">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 font-medium flex items-center gap-2">
                                    Sangat Positif

                                    <i
                                        class="fa-solid fa-circle-info text-slate-400"
                                        title="{{ $emotionTitles['positive'] ?? '-' }}">
                                    </i>
                                </p>

                                <h3 id="summary-positive"
                                    class="text-3xl font-black text-emerald-600 mt-2">
                                </h3>

                            </div>

                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center">
                                <i class="fa-solid fa-face-smile text-xl text-emerald-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEUTRAL -->
                <div class="bg-white rounded-4xl border border-gray-300 p-6">

                    <div id="summary-skeleton-neutral">
                        <div class="animate-pulse">
                            <div class="h-4 w-20 bg-slate-200 rounded mb-4"></div>
                            <div class="h-10 w-24 bg-slate-200 rounded"></div>
                        </div>
                    </div>

                    <div id="summary-neutral-content" class="hidden">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 font-medium flex items-center gap-2">
                                    Netral

                                    <i
                                        class="fa-solid fa-circle-info text-slate-400"
                                        title="{{ $emotionTitles['neutral'] ?? '-' }}">
                                    </i>
                                </p>

                                <h3 id="summary-neutral"
                                    class="text-3xl font-black text-amber-500 mt-2">
                                </h3>

                            </div>

                            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center">
                                <i class="fa-solid fa-face-meh text-xl text-amber-500"></i>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- ATTENTION -->
                <div class="bg-white rounded-4xl border border-gray-300 p-6">

                    <div id="summary-skeleton-attention">
                        <div class="animate-pulse">
                            <div class="h-4 w-32 bg-slate-200 rounded mb-4"></div>
                            <div class="h-10 w-24 bg-slate-200 rounded"></div>
                        </div>
                    </div>

                    <div id="summary-attention-content" class="hidden">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 font-medium flex items-center gap-2">
                                    Perlu Perhatian

                                    <i
                                        class="fa-solid fa-circle-info text-slate-400"
                                        title="{{ $emotionTitles['attention'] ?? '-' }}">
                                    </i>
                                </p>

                                <h3 id="summary-attention"
                                    class="text-3xl font-black text-red-500 mt-2">
                                </h3>

                            </div>

                            <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center">
                                <i class="fa-solid fa-triangle-exclamation text-xl text-red-500"></i>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- CHART -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                <!-- BAR -->
                <div class="xl:col-span-2 bg-white rounded-4xl md:rounded-[2.5rem] p-6 md:p-8 border border-gray-300">

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">

                        <div>
                            <h3 class="text-2xl font-black text-slate-800">
                                Tren Respon Siswa
                            </h3>

                            <p class="text-sm text-slate-500 mt-2">
                                Perkembangan jumlah siswa yang mengisi refleksi.
                            </p>

                        </div>
                    </div>

                    <div class="relative mt-6">

                        <!-- LOADING SCREEN -->
                        <div id="reflection-chart-loading"
                            class="flex flex-col items-center justify-center h-87.5 bg-slate-50 rounded-2xl border border-slate-100">

                            <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-300 border-t-blue-500"></div>

                            <h4 class="mt-4 text-slate-700 font-bold text-lg">
                                Memuat data grafik
                            </h4>

                            <p class="text-sm text-slate-500 mt-1 text-center max-w-md">
                                Sedang mengambil data respon siswa dari server. Mohon tunggu sebentar...
                            </p>

                        </div>

                        <!-- CHART -->
                        <div id="reflection-chart-content" class="hidden w-full h-87.5">
                            <canvas id="studentReflectionChart"></canvas>
                        </div>

                        <div id="empty-message-student-reflection-chart" class="hidden">
                            <div class="flex flex-col items-center justify-center h-87.5 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                                    <i class="fas fa-face-smile text-2xl text-emerald-500"></i>
                                </div>
        
                                <h4 class="text-lg font-bold text-slate-700">
                                    Tidak ada data
                                </h4>

                                <p class="text-sm text-slate-500 text-center max-w-md mt-2">
                                    Data refleksi tidak ditemukan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DOUGHNUT -->
                <div class="bg-white rounded-4xl md:rounded-[2.5rem] p-6 md:p-8 border border-gray-300">

                    <h3 class="text-2xl font-black text-slate-800">
                        Distribusi Emosi
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Ringkasan kondisi emosional siswa berdasarkan hasil refleksi.
                    </p>

                    <div class="relative mt-6">

                        <!-- LOADING SCREEN -->
                        <div id="emotion-chart-loading"
                            class="flex flex-col items-center justify-center h-87.5 bg-slate-50 rounded-2xl border border-slate-100">

                            <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-300 border-t-emerald-500"></div>

                            <h4 class="mt-4 text-slate-700 font-bold text-lg">
                                Menyusun distribusi emosi
                            </h4>

                            <p class="text-sm text-slate-500 mt-1 text-center max-w-md">
                                Sedang mengambil data respon siswa dari server. Mohon tunggu sebentar...
                            </p>

                        </div>

                        <!-- CHART -->
                        <div id="emotion-chart-content" class="hidden w-full h-87.5">
                            <canvas id="emotionChart"></canvas>
                        </div>
                        
                        <div id="empty-message-emotion-chart" class="hidden">
                            <div class="flex flex-col items-center justify-center h-87.5 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                                    <i class="fas fa-face-smile text-2xl text-emerald-500"></i>
                                </div>
        
                                <h4 class="text-lg font-bold text-slate-700">
                                    Belum ada data emosi siswa
                                </h4>
        
                                <p class="text-sm text-slate-500 text-center max-w-md mt-2">
                                    Distribusi emosi akan ditampilkan setelah siswa mengirimkan refleksi beserta kondisi emosinya.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="bg-white rounded-4xl md:rounded-[2.5rem] border border-gray-300">

                <div class="p-6 md:p-8 border-b border-gray-200">

                    <h3 class="text-2xl font-black text-slate-800">
                        Daftar Jawaban Siswa
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Detail jawaban refleksi dan kondisi emosional masing-masing siswa.
                    </p>
                </div>
                
                <div class="overflow-x-auto p-6 md:p-8">

                    <!-- TABLE SKELETON -->
                    <div id="reflection-student-answer-skeleton" class="hidden">

                        <div class="overflow-hidden">

                            <table class="min-w-full text-sm border-collapse">

                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="border border-gray-200 px-3 py-3"></th>
                                        <th class="border border-gray-200 px-3 py-3"></th>
                                        <th class="border border-gray-200 px-3 py-3"></th>
                                        <th class="border border-gray-200 px-3 py-3"></th>
                                        <th class="border border-gray-200 px-3 py-3"></th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @for ($i = 0; $i < 6; $i++)
                                        <tr class="animate-pulse">

                                            <td class="border border-gray-200 px-3 py-4">
                                                <div class="h-4 w-8 bg-slate-200 rounded mx-auto"></div>
                                            </td>

                                            <td class="border border-gray-200 px-3 py-4">
                                                <div class="h-4 w-32 bg-slate-200 rounded"></div>
                                            </td>

                                            <td class="border border-gray-200 px-3 py-4">
                                                <div class="h-4 w-20 bg-slate-200 rounded"></div>
                                            </td>

                                            <td class="border border-gray-200 px-3 py-4">
                                                <div class="h-4 w-full bg-slate-200 rounded"></div>
                                            </td>

                                            <td class="border border-gray-200 px-3 py-4">
                                                <div class="h-6 w-24 bg-slate-200 rounded-full"></div>
                                            </td>

                                        </tr>
                                    @endfor

                                </tbody>

                            </table>

                        </div>

                        <div class="flex flex-col items-center py-6">

                            <div class="animate-spin rounded-full h-6 w-6 border-2 border-slate-300 border-t-blue-500"></div>

                            <p class="text-sm text-slate-500 mt-3">
                                Memuat jawaban refleksi siswa...
                            </p>

                        </div>

                    </div>

                    <table id="table-reflection-student-answer" class="min-w-full text-sm border-collapse">
                        <thead class="thead-table-reflection-student-answer hidden bg-gray-50 shadow-inner">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Siswa</th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kelas</th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Jawaban Refleksi</th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status Emosi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-reflection-student-answer">
                            <!-- show data in ajax -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container-reflection-student-answer flex justify-center"></div>

                <div id="empty-message-reflection-student-answer" class="hidden h-96 bg-slate-50 rounded-2xl border border-dashed border-slate-200">

                    <div class="flex flex-col items-center justify-center h-full px-6">

                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <i class="fas fa-comment-dots text-2xl text-blue-500"></i>
                        </div>

                        <h4 class="text-lg font-bold text-slate-700 text-center">
                            Belum ada jawaban refleksi
                        </h4>

                        <p class="text-sm text-slate-500 text-center max-w-md mt-2">
                            Belum ada siswa yang mengirimkan jawaban refleksi untuk pertanyaan ini.
                            Jawaban siswa akan muncul di tabel setelah mereka mengisi refleksi harian.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/history-detail/load-reflection-detail-header.js') }}"></script> <!--- load detail header ---->
<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/history-detail/load-reflection-detail-summary.js') }}"></script> <!--- load detail summary ---->
<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/history-detail/load-reflection-detail-chart.js') }}"></script> <!--- load detail chart ---->
<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/history-detail/paginate-reflection-detail-student-answer.js') }}"></script> <!--- load detail student answer ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/student-vice-principal/daily-reflection/daily-reflection-history-detail-listener.js') }}"></script> <!--- pusher listener daily reflection history detail ---->