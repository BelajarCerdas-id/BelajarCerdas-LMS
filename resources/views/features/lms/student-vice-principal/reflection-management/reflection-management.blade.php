@include('components/sidebar-beranda', [
    'headerSideNav' => 'Reflection Management',
    'linkBackButton' => route('lms.student-vice-principal.dashboard.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role == 'Wakil Kesiswaan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out overflow-x-hidden">
        <div class="p-4 sm:p-6 md:p-8 xl:p-10 mx-auto space-y-8">

            <!---- ALERT SUCCESS ---->
            <div id="alert-success-create-reflection"></div>

            <!-- HERO SECTION -->
            <div class="relative overflow-hidden rounded-4xl md:rounded-[2.5rem] bg-[#0071BC] p-6 sm:p-8 md:p-10 text-white 
                shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
                style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}'); background-size: cover; background-position: center;">

                <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-20 w-40 h-40 rounded-full bg-cyan-300/10 blur-3xl"></div>

                <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-8">

                    <div class="max-w-3xl">

                        <div class="flex flex-wrap items-center gap-3 mb-4">

                            <div class="px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 
                                text-[10px] sm:text-xs font-bold uppercase tracking-widest">
                                Pusat Manajemen Refleksi
                            </div>

                            <div class="flex items-center gap-2 text-xs sm:text-sm text-blue-100">

                                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>

                                Pemantauan Refleksi Aktif

                            </div>

                        </div>

                        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight">
                            Manajemen Refleksi
                        </h1>

                        <p class="mt-4 text-blue-100 max-w-2xl leading-relaxed text-sm md:text-base font-medium">
                            Pantau hasil refleksi siswa secara langsung, analisis kondisi emosional siswa,
                            serta kelola seluruh aktivitas refleksi harian sekolah melalui satu pusat
                            pemantauan yang terintegrasi.
                        </p>

                        <div class="flex flex-wrap gap-3 mt-6">

                            <a href="{{ route('lms.student-vice-principal.create.reflection.view', [
                                'role' => $role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId,
                            ]) }}"
                                class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white text-[#0071BC] font-black shadow-lg hover:scale-105 transition-all">

                                <i class="fas fa-plus"></i>
                                Buat Refleksi Baru
                            </a>

                            <div class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-sm font-semibold">
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                                Pemantauan Berlangsung
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                class="grid grid-cols-1 2xl:grid-cols-12 gap-8">

                <!-- LIVE PREVIEW -->
                <div class="2xl:col-span-6 self-start">

                    <div class="sticky top-6">

                        <div class="bg-white rounded-4xl md:rounded-[2.5rem] border border-gray-200 
                            shadow-[0_10px_40px_rgba(15,23,42,0.06)] overflow-hidden p-6 md:p-8">

                            <!-- HEADER -->
                            <div class="border-b border-slate-100 pb-6">

                                <div class="flex items-center justify-between">

                                    <div class="flex items-center gap-3">

                                        <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-[#0071BC] to-[#003456] flex items-center justify-center shadow-lg shadow-blue-100">

                                            <i class="fas fa-wave-square text-white text-lg"></i>
                                        </div>

                                        <div>

                                            <h3 class="text-lg font-black text-slate-800 leading-tight">
                                                Live Preview
                                            </h3>

                                            <p class="text-xs text-slate-500 mt-1">
                                                Statistik refleksi siswa hari ini
                                            </p>
                                        </div>
                                    </div>

                                    <!-- LIVE INDICATOR -->
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100">

                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>

                                        <span class="text-[11px] font-black tracking-wide text-emerald-700">
                                            LIVE
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- SCROLLABLE CONTENT -->
                            <div id="daily-reflection-live-preview"
                                class="max-h-[calc(100vh-220px)] overflow-y-auto my-6 pb-6 md:pb-6">
                                <!-- show data in ajax -->
                            </div>

                            <div class="pagination-container-daily-reflection-live-preview"></div>

                            <div id="empty-message-daily-reflection-live-preview" class="hidden">

                                <div class="sticky top-6 bg-white rounded-4xl md:rounded-[2.5rem] p-8 border border-dashed border-slate-300 text-center">

                                    <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-5">

                                        <i class="fas fa-inbox text-3xl text-slate-400"></i>
                                    </div>

                                    <h3 class="text-lg font-black text-slate-700">
                                        Belum Ada Refleksi Aktif
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                                        Refleksi harian yang dipublikasikan hari ini akan muncul secara realtime pada panel ini.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HISTORY RECENT -->
                <div class="2xl:col-span-6 self-start bg-white rounded-4xl md:rounded-[2.5rem] p-6 md:p-8 border border-gray-300 shadow-sm">

                    <div class="flex items-start justify-between gap-4 mb-8">

                        <div>
                            <h3 class="text-2xl font-black text-slate-800">
                                Riwayat Terbaru
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                Refleksi yang baru dipublikasikan dalam beberapa waktu terakhir.
                            </p>
                        </div>

                        <a href="{{ route('lms.student-vice-principal.reflection-management-history.view', [
                            'role' => $role,
                            'schoolName' => $schoolName,
                            'schoolId' => $schoolId
                        ]) }}"
                            class="shrink-0 inline-flex items-center gap-2 text-sm font-black text-[#2563EB] hover:gap-3 transition-all">

                            Lihat Semua

                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                    <div id="reflection-history-recent"
                        class="relative max-h-140 overflow-y-auto">
                        <!-- show data in ajax -->
                    </div>

                    <div id="empty-message-reflection-history" class="hidden">
                        <div class="rounded-4xl border border-dashed border-gray-300 bg-slate-50 px-6 py-14 text-center">

                            <div class="w-18 h-18 mx-auto rounded-3xl bg-white border border-gray-200 
                                flex items-center justify-center text-3xl text-slate-400 shadow-sm mb-5">

                                <i class="fas fa-comment-dots"></i>
                            </div>

                            <h4 class="text-lg font-black text-slate-700">
                                Belum Ada Riwayat
                            </h4>

                            <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto leading-relaxed">
                                Saat ini belum terdapat data refleksi yang tercatat.
                                Refleksi akan muncul setelah aktivitas pembelajaran dimulai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/paginate-reflection-management-history-recent.js') }}"></script> <!--- paginate reflection management history recent ---->
<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/daily-reflection-live-preview.js') }}"></script> <!--- paginate reflection management history recent ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/student-vice-principal/daily-reflection/daily-reflection-live-preview-listener.js') }}"></script> <!--- pusher listener daily reflection live preview ---->