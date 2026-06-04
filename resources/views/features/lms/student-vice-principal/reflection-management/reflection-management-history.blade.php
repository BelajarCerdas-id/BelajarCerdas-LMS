@include('components/sidebar-beranda', [
    'headerSideNav' => 'History',
    'linkBackButton' => route('lms.student-vice-principal.reflection-management.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role == 'Wakil Kesiswaan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out overflow-x-hidden">
        <div class="p-4 sm:p-6 md:p-8 xl:p-10 mx-auto space-y-8">

        <div class="relative overflow-hidden rounded-4xl md:rounded-[2.5rem] bg-[#0071BC] p-6 sm:p-8 md:p-10 text-white 
            shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
            style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}'); background-size: cover; background-position: center;">

            <!-- DECOR -->
            <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-20 w-40 h-40 rounded-full bg-cyan-300/10 blur-3xl"></div>

            <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-8">

                <div class="max-w-3xl">

                    <!-- STATUS -->
                    <div class="flex flex-wrap items-center gap-3 mb-4">

                        <div class="px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 
                            text-[10px] sm:text-xs font-bold uppercase tracking-widest">
                            Pusat Manajemen Refleksi
                        </div>

                        <div class="flex items-center gap-2 text-xs sm:text-sm text-blue-100">

                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            Pemantauan Riwayat Aktif
                        </div>
                    </div>

                    <!-- TITLE -->
                    <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight">
                        Riwayat Refleksi Siswa
                    </h1>

                    <!-- DESCRIPTION -->
                    <p class="mt-4 text-blue-100 max-w-2xl leading-relaxed text-sm md:text-base font-medium">
                        Kelola seluruh riwayat refleksi siswa, pantau perkembangan respons emosional,
                        serta analisis aktivitas refleksi harian yang telah dilakukan di sekolah secara terpusat dan real-time.
                    </p>

                    <!-- ACTIONS -->
                    <div class="flex flex-wrap gap-3 mt-6">

                        <!-- BUAT REFLEKSI -->
                        <a href="{{ route('lms.student-vice-principal.create.reflection.view', [
                            'role' => $role,
                            'schoolName' => $schoolName,
                            'schoolId' => $schoolId,
                        ]) }}"
                            class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white text-[#0071BC] font-black shadow-lg hover:scale-105 transition-all">

                            <i class="fas fa-plus"></i>
                            Buat Refleksi
                        </a>

                        <!-- STATUS LIVE -->
                        <div class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-sm font-semibold">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            Pemantauan Real-time Aktif
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <div class="relative">
                <div id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead class="thead-table-reflection-history hidden bg-gray-50 shadow-inner">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                    No
                                </th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                    Judul
                                </th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                    Pertanyaan
                                </th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                    Total Respon
                                </th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                    Jenjang Kelas
                                </th>
                                <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                    Detail
                                </th>
                            </tr>
                        </thead>
        
                        <tbody id="table-list-reflection-history">
                            <!-- show data in ajax -->
                        </tbody>
                    </table>
                </div>
        
                <div class="pagination-container-reflection-history flex justify-center my-4 sm:my-0"></div>
        
                <!-- EMPTY MESSAGE -->
                <div id="empty-message-reflection-history" class="hidden">
                    <div
                        class="mx-auto bg-linear-to-br from-blue-50 via-white to-slate-50 border border-blue-100 rounded-4xl p-10 shadow-sm">

                        <div class="flex flex-col items-center text-center">

                            <!-- ICON -->
                            <div
                                class="w-24 h-24 rounded-full bg-white border border-blue-100 shadow-lg flex items-center justify-center">

                                <div
                                    class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center">

                                    <i class="fas fa-book-open text-3xl text-[#2563EB]"></i>

                                </div>
                            </div>

                            <!-- TITLE -->
                            <h2 class="text-2xl font-black text-slate-800 mt-6">
                                Belum Ada Riwayat Refleksi
                            </h2>

                            <!-- DESCRIPTION -->
                            <p class="text-sm text-slate-500 mt-3 max-w-xl leading-relaxed">
                                Belum terdapat refleksi yang pernah dipublikasikan kepada siswa.
                                Mulailah membuat refleksi pertama untuk mengumpulkan masukan,
                                kondisi belajar, serta perkembangan siswa secara berkala.
                            </p>

                            <!-- ACTION -->
                            <button
                                onclick="window.location.href='{{ route('lms.student-vice-principal.create.reflection.view', ['role' => $role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}'"
                                class="mt-8 px-7 py-3 rounded-2xl bg-[#2563EB] hover:bg-blue-700
                                text-white font-bold shadow-lg hover:scale-105 transition-all cursor-pointer">

                                <i class="fas fa-plus mr-2"></i>
                                Buat Refleksi Pertama
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- LOADING SCREEN -->
                <div id="loading-reflection-history">
                    <div class="mx-auto bg-linear-to-br from-blue-50 to-white border border-blue-100 rounded-3xl p-10 shadow-sm">
                        <div class="flex flex-col items-center justify-center text-center">

                            <!-- loading animation -->
                            <div class="relative">

                                <!-- outer pulse -->
                                <div class="w-20 h-20 rounded-full bg-blue-100 animate-pulse"></div>

                                <!-- inner -->
                                <div
                                    class="absolute inset-0 flex items-center justify-center">

                                    <div
                                        class="w-14 h-14 rounded-full border-4 border-blue-200 border-t-[#4189e0] animate-spin">
                                    </div>
                                </div>
                            </div>

                            <!-- title -->
                            <h2 class="text-2xl font-bold text-gray-700 mt-7">
                                Memuat Riwayat Refleksi
                            </h2>

                            <!-- subtitle -->
                            <p class="text-sm text-gray-500 mt-3 max-w-md leading-relaxed">
                                Mohon tunggu sebentar, sistem sedang mengambil data riwayat refleksi,
                                jumlah respon siswa, serta informasi refleksi yang telah dipublikasikan.
                            </p>

                            <!-- skeleton info -->
                            <div class="mt-8 w-full max-w-lg space-y-3">

                                <div class="h-4 rounded-full bg-gray-200 animate-pulse"></div>

                                <div class="h-4 rounded-full bg-gray-200 animate-pulse w-11/12 mx-auto"></div>

                                <div class="h-4 rounded-full bg-gray-200 animate-pulse w-10/12 mx-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/paginate-reflection-management-history.js') }}"></script> <!--- paginate reflection management history ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/student-vice-principal/daily-reflection/daily-reflection-history-listener.js') }}"></script> <!--- pusher listener daily reflection history ---->