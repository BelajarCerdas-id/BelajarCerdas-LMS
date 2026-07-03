@include('components/sidebar-beranda', [
    'headerSideNav' => 'Simulasi TKA',
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main id="container" data-role="{{ $role }}">

                <!-- HEADER SKELETON -->
                <section id="header-skeleton"
                    class="relative overflow-hidden rounded-2xl bg-linear-to-r from-[#0071BC] via-[#0A84D8] to-[#3AA0E8] text-white p-8">

                    <!-- Background Decoration -->
                    <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-white/10"></div>
                    <div class="absolute right-20 bottom-0 w-28 h-28 rounded-full bg-white/5"></div>

                    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-8">

                        <!-- Left -->
                        <div class="flex-1 max-w-3xl">

                            <!-- Badge -->
                            <div
                                class="h-8 w-48 rounded-full bg-white/20 animate-pulse">
                            </div>

                            <!-- Title -->
                            <div
                                class="h-10 w-64 rounded-lg bg-white/20 mt-6 animate-pulse">
                            </div>

                            <!-- Description -->
                            <div class="space-y-3 mt-6">
                                <div
                                    class="h-4 w-full rounded bg-white/20 animate-pulse">
                                </div>

                                <div
                                    class="h-4 w-11/12 rounded bg-white/20 animate-pulse">
                                </div>

                                <div
                                    class="h-4 w-8/12 rounded bg-white/20 animate-pulse">
                                </div>
                            </div>
                        </div>

                        <!-- Right -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 xl:min-w-85">

                            <!-- Card 1 -->
                            <div class="bg-white/15 backdrop-blur rounded-xl px-5 py-5">
                                <div
                                    class="w-9 h-9 rounded-full bg-white/20 mx-auto animate-pulse">
                                </div>

                                <div
                                    class="h-7 w-12 rounded bg-white/20 mx-auto mt-4 animate-pulse">
                                </div>

                                <div
                                    class="h-3 w-28 rounded bg-white/20 mx-auto mt-3 animate-pulse">
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="bg-white/15 backdrop-blur rounded-xl px-5 py-5">
                                <div
                                    class="w-9 h-9 rounded-full bg-white/20 mx-auto animate-pulse">
                                </div>

                                <div
                                    class="h-7 w-12 rounded bg-white/20 mx-auto mt-4 animate-pulse">
                                </div>

                                <div
                                    class="h-3 w-28 rounded bg-white/20 mx-auto mt-3 animate-pulse">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- HEADER CONTENT -->
                <section id="header-content" class="relative overflow-hidden rounded-2xl bg-linear-to-r from-[#0071BC] via-[#0A84D8] to-[#3AA0E8] text-white p-8 hidden">

                    <!-- Background Decoration -->
                    <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-white/10"></div>
                    <div class="absolute right-20 bottom-0 w-28 h-28 rounded-full bg-white/5"></div>

                    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-8">

                        <div class="flex-1 max-w-3xl">

                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-sm mb-5">

                                <i class="fa-solid fa-graduation-cap"></i>
                                <span>Pusat Simulasi TKA</span>

                            </div>

                            <h1 class="text-3xl font-bold">
                                Simulasi TKA
                            </h1>

                            <p class="mt-3 text-white/90 leading-7">
                                Latih kemampuanmu melalui simulasi TKA berdasarkan
                                mata pelajaran yang tersedia. Pilih satu mata pelajaran,
                                kerjakan soal dengan serius, dan evaluasi hasilmu.
                            </p>

                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4 xl:min-w-85">

                            <div
                                class="bg-white/15 backdrop-blur rounded-xl px-5 py-4 text-center">

                                <i class="fa-solid fa-book text-2xl mb-2"></i>

                                <div class="font-bold text-xl" id="total-subject">
                                    0
                                </div>

                                <div class="text-sm text-white/80">
                                    Mata Pelajaran
                                </div>

                            </div>

                            <div
                                class="bg-white/15 backdrop-blur rounded-xl px-5 py-4 text-center">

                                <i class="fa-solid fa-file-lines text-2xl mb-2"></i>

                                <div class="font-bold text-xl">
                                    TKA
                                </div>

                                <div class="text-sm text-white/80">
                                    Bank Soal
                                </div>

                            </div>
                        </div>
                    </div>
                </section>

                <!-- GRID MAPEL -->
                <section>
                    <div class="flex items-center justify-between mt-10 mb-5">
                        <div>

                            <h2 class="text-xl font-bold text-gray-800">
                                Mata Pelajaran
                            </h2>

                            <p class="text-gray-500 text-sm mt-1">
                                Pilih mata pelajaran yang ingin disimulasikan.
                            </p>

                        </div>
                    </div>

                    <div id="grid-subject-skeleton" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-14 mt-8">
                        @for ($i = 0; $i < 3; $i++)

                            <div
                                class="bg-white rounded-2xl border border-gray-200 overflow-hidden animate-pulse">

                                <div class="h-2 bg-gray-200"></div>

                                <div class="p-6">

                                    <div class="flex items-center justify-between">

                                        <div class="w-16 h-16 rounded-2xl bg-gray-200"></div>

                                        <div class="h-6 w-16 rounded-full bg-gray-200"></div>

                                    </div>

                                    <div class="h-7 w-40 rounded bg-gray-200 mt-6"></div>

                                    <div class="space-y-2 mt-4">

                                        <div class="h-4 rounded bg-gray-200"></div>

                                        <div class="h-4 w-5/6 rounded bg-gray-200"></div>

                                    </div>

                                    <div class="flex flex-wrap gap-2 mt-5">
                                        <div class="h-8 w-32 rounded-full bg-gray-200"></div>
                                        <div class="h-8 w-36 rounded-full bg-gray-200"></div>
                                        <div class="h-8 w-28 rounded-full bg-gray-200"></div>
                                        <div class="h-8 w-30 rounded-full bg-gray-200"></div>
                                    </div>

                                    <div class="mt-5 space-y-2">
                                        <div class="h-3 rounded bg-gray-200"></div>
                                        <div class="h-3 w-5/6 rounded bg-gray-200"></div>
                                    </div>

                                    <div class="mt-6 h-12 rounded-xl bg-gray-200"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div id="container-subject-list" class="hidden">
                        <div id="grid-subject-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-14 mt-8">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-subject-list" class="hidden">

                        <div class="w-full rounded-2xl border border-gray-300 bg-white shadow-lg py-20">

                            <div class="mx-auto max-w-xl px-6 text-center">

                                <!-- Icon -->
                                <div
                                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-50">

                                    <i class="fa-solid fa-book-open text-3xl text-[#0071BC]"></i>

                                </div>

                                <!-- Title -->
                                <h2 class="mt-6 text-2xl font-semibold text-gray-800">
                                    Mata Pelajaran Belum Tersedia
                                </h2>

                                <!-- Description -->
                                <p class="mt-3 text-gray-500 leading-7">
                                    Tidak ada mata pelajaran yang dapat digunakan untuk
                                    <strong>Simulasi TKA</strong> pada jenjang kelasmu.
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

<script src="{{ asset('assets/js/features/lms/student/library/tka/paginate-tka-subject.js') }}"></script> <!--- paginate subject list ---->
