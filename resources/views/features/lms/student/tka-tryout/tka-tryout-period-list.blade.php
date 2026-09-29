@include('components/sidebar-beranda', [
    'headerSideNav' => 'Tryout TKA',
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                
                <!-- HEADER SKELETON -->
                <section id="header-skeleton"
                    class="relative overflow-hidden rounded-2xl bg-linear-to-r from-[#0071BC] via-[#0A84D8] to-[#3AA0E8] text-white p-6 sm:p-8">

                    <!-- Background Decoration -->
                    <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-white/10"></div>
                    <div class="absolute right-20 bottom-0 w-28 h-28 rounded-full bg-white/5"></div>

                    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-8">

                        <!-- Left -->
                        <div class="flex-1 max-w-3xl">

                            <!-- Badge -->
                            <div class="h-8 w-48 rounded-full bg-white/20 animate-pulse"></div>

                            <!-- Title -->
                            <div class="h-10 w-64 rounded-lg bg-white/20 mt-6 animate-pulse"></div>

                            <!-- Description -->
                            <div class="space-y-3 mt-6">
                                <div class="h-4 w-full rounded bg-white/20 animate-pulse"></div>
                                <div class="h-4 w-11/12 rounded bg-white/20 animate-pulse"></div>
                                <div class="h-4 w-8/12 rounded bg-white/20 animate-pulse"></div>
                            </div>

                        </div>

                        <!-- Right -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 xl:min-w-85">
                            @for ($i = 0; $i < 2; $i++)

                                <!-- Card -->
                                <div class="bg-white/15 backdrop-blur rounded-xl px-5 py-5">
                                    <div class="w-9 h-9 rounded-full bg-white/20 mx-auto animate-pulse"></div>
                                    <div class="h-7 w-{{ $i === 0 ? '12' : '20' }} rounded bg-white/20 mx-auto mt-4 animate-pulse"></div>
                                    <div class="h-3 w-28 rounded bg-white/20 mx-auto mt-3 animate-pulse"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </section>

                <!-- HEADER CONTENT -->
                <section id="header-content" class="relative overflow-hidden rounded-2xl bg-linear-to-r from-[#0071BC] via-[#0A84D8] to-[#3AA0E8] text-white p-6 sm:p-8 hidden">

                    <!-- Background Decoration -->
                    <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-white/10"></div>
                    <div class="absolute right-20 bottom-0 w-28 h-28 rounded-full bg-white/5"></div>

                    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-8">

                        <!-- Left -->
                        <div class="flex-1 max-w-3xl">

                            <!-- Badge -->
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-sm mb-5">
                                <i class="fa-solid fa-clipboard-list"></i>
                                <span>Pusat Tryout TKA</span>
                            </div>

                            <!-- Title -->
                            <h1 class="text-3xl font-bold">
                                Tryout TKA
                            </h1>

                            <!-- Description -->
                            <p class="mt-3 text-white/90 leading-7">
                                Ikuti rangkaian Tryout TKA melalui berbagai gelombang
                                yang tersedia. Pilih gelombang yang sedang berlangsung,
                                ikuti setiap sesi, dan kerjakan mata pelajaran sesuai jadwal.
                            </p>
                        </div>

                        <!-- Right -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4 xl:min-w-85">

                            <!-- Total Period -->
                            <div class="bg-white/15 backdrop-blur rounded-xl px-5 py-4 text-center">
                                <i class="fa-solid fa-layer-group text-2xl mb-2"></i>

                                <div class="font-bold text-xl" id="total-period">
                                    0
                                </div>

                                <div class="text-sm text-white/80">
                                    Gelombang Tryout
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="bg-white/15 backdrop-blur rounded-xl px-5 py-4 text-center">
                                <i class="fa-solid fa-calendar-check text-2xl mb-2"></i>

                                <div class="font-bold text-xl">
                                    TKA
                                </div>

                                <div class="text-sm text-white/80">
                                    Program Tryout
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION GELOMBANG TRYOUT -->
                <section class="mt-10">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">
                                Gelombang Tryout
                            </h2>

                            <p class="text-gray-500 text-sm mt-1">
                                Pilih gelombang Tryout TKA yang ingin kamu ikuti.
                            </p>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 text-[#0071BC] text-sm font-medium">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>
                                <span id="total-period-list">0</span> Gelombang
                            </span>
                        </div>
                    </div>

                    <!-- PERIOD LIST SKELETON -->
                    <div id="period-list-skeleton" class="space-y-5">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden animate-pulse">
                                <div class="p-6 sm:p-7">

                                    <!-- Top -->
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                        <div class="flex items-start gap-4">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-200 shrink-0"></div>

                                            <div>
                                                <div class="h-4 w-24 rounded-full bg-gray-200"></div>
                                                <div class="h-7 w-64 max-w-full rounded-lg bg-gray-200 mt-3"></div>
                                                <div class="h-4 w-48 rounded bg-gray-200 mt-3"></div>
                                            </div>
                                        </div>

                                        <div class="h-8 w-36 rounded-full bg-gray-200"></div>
                                    </div>

                                    <!-- Date -->
                                    <div class="flex flex-wrap items-center gap-3 mt-6">
                                        <div class="h-9 w-48 rounded-lg bg-gray-200"></div>
                                        <div class="h-9 w-32 rounded-lg bg-gray-200"></div>
                                    </div>

                                    <!-- Statistics -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5">
                                        <div class="flex items-center gap-3 rounded-xl bg-gray-100 px-4 py-3">
                                            <div class="w-9 h-9 rounded-lg bg-gray-200"></div>

                                            <div>
                                                <div class="h-3 w-20 rounded bg-gray-200"></div>
                                                <div class="h-4 w-12 rounded bg-gray-200 mt-2"></div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 rounded-xl bg-gray-100 px-4 py-3">
                                            <div class="w-9 h-9 rounded-lg bg-gray-200"></div>

                                            <div>
                                                <div class="h-3 w-28 rounded bg-gray-200"></div>
                                                <div class="h-4 w-12 rounded bg-gray-200 mt-2"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottom -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-6 pt-5 border-t border-gray-100">
                                        <div class="space-y-2">
                                            <div class="h-3 w-56 rounded bg-gray-200"></div>
                                            <div class="h-3 w-40 rounded bg-gray-200"></div>
                                        </div>

                                        <div class="h-11 w-full sm:w-40 rounded-xl bg-gray-200"></div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <!-- PERIOD LIST CONTENT -->
                    <div id="container-period-list" class="hidden">
                        <div id="period-list" class="space-y-5">
                            <!-- SHOW DATA IN AJAX -->
                        </div>
                    </div>

                    <!-- EMPTY STATE -->
                    <div id="empty-message-period-list" class="hidden">
                        <div class="w-full rounded-2xl border border-gray-300 bg-white shadow-lg py-20">
                            <div class="mx-auto max-w-xl px-6 text-center">

                                <!-- Icon -->
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-50">
                                    <i class="fa-solid fa-calendar-xmark text-3xl text-[#0071BC]"></i>
                                </div>

                                <!-- Title -->
                                <h2 class="mt-6 text-xl font-semibold text-gray-800">
                                    Belum Ada Gelombang Tryout
                                </h2>

                                <!-- Description -->
                                <p class="mt-3 text-gray-500 leading-7">
                                    Saat ini belum ada gelombang Tryout TKA yang tersedia untuk kamu. Silahkan cek kembali nanti.
                                </p>
                            </div>
                        </div>
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

<script src="{{ asset('assets/js/features/lms/student/tka-tryout/period-list/paginate-tka-tryout-period-list.js') }}"></script> <!--- paginate period list ---->

<script>
    $(document).ready(function () {
        @if (session('period_status') === 'upcoming')
            Swal.fire({
                icon: 'info',
                title: 'Gelombang Belum Dimulai',
                text: 'Gelombang Tryout TKA ini belum dimulai.',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#0071BC'
            });
        @elseif (session('period_status') === 'finished')
            Swal.fire({
                icon: 'info',
                title: 'Gelombang Telah Berakhir',
                text: 'Gelombang Tryout TKA ini sudah berakhir.',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#0071BC'
            });
        @endif
    });
</script>