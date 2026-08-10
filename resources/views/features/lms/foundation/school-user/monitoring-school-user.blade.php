@include('components/sidebar-beranda', ['headerSideNav' => 'Warga Sekolah'])

@if (Auth::user()->role === 'Yayasan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="p-6 md:p-8">
            <main id="container" data-role="{{ $role }}" data-foundation-id="{{ $foundationId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-users absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>

                        <i class="fa-solid fa-school absolute -bottom-10 -left-6 text-[90px] lg:text-[140px] text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg shrink-0">
                                        <i class="fa-solid fa-users text-white text-xl lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white leading-tight">
                                            Warga Sekolah
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-3xl text-sm sm:text-base text-white/80 leading-relaxed">
                                    Monitoring jumlah dan distribusi warga sekolah dari seluruh sekolah
                                    yang berada di bawah naungan yayasan.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI SUMMARY -->
                <section class="mb-6">

                    <!-- Skeleton Loading -->
                    <div id="kpi-loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

                        <!-- Card 1 -->
                        <div class="rounded-2xl border border-cyan-100 bg-cyan-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-32 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-20 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>

                                </div>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-28 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-20 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>

                                </div>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 shadow-sm">
                            <div class="p-5">
                                <div class="flex items-center justify-between">

                                    <div class="flex-1">
                                        <div class="skeleton h-3 w-30 rounded-full"></div>
                                        <div class="skeleton mt-3 h-7 w-20 rounded-lg"></div>
                                    </div>

                                    <div class="skeleton h-12 w-12 rounded-xl shrink-0"></div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- KPI Content -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    
                            <!-- Total Pengguna -->
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 shadow-sm hover:shadow-md transition-all duration-300">
    
                                <div class="p-5">
    
                                    <div class="flex items-center justify-between">
    
                                        <div>
    
                                            <p class="text-sm font-medium text-slate-500">
                                                Total Warga Sekolah Aktif
                                            </p>
    
                                            <h2 id="total-user" class="mt-1 text-xl font-bold text-slate-800">
                                                0
                                            </h2>
    
                                        </div>
    
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10">
                                            <i class="fa-solid fa-users text-xl text-cyan-600"></i>
                                        </div>
    
                                    </div>
                                </div>
                            </div>
    
                            <!-- Guru -->
                            <div class="rounded-2xl border border-blue-100 bg-blue-50 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-slate-500">
                                                Total Guru Aktif
                                            </p>
    
                                            <h2 id="total-teacher" class="mt-1 text-xl font-bold text-slate-800">
                                                0
                                            </h2>
                                        </div>
    
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                                            <i class="fa-solid fa-chalkboard-user text-xl text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Siswa -->
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-slate-500">
                                                Total Siswa Aktif
                                            </p>
    
                                            <h2 id="total-student" class="mt-1 text-xl font-bold text-slate-800">
                                                0
                                            </h2>
                                        </div>
    
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                                            <i class="fa-solid fa-user-graduate text-xl text-emerald-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Filtering -->
                <section class="mb-6">
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- Left -->
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                                    <i class="fa-solid fa-filter text-blue-600 text-lg"></i>
                                </div>

                                <div>

                                    <h2 class="text-lg font-bold text-slate-800">
                                        Filter Warga Sekolah
                                    </h2>

                                    <p class="text-sm text-slate-500">
                                        Filter yang dipilih akan diterapkan pada seluruh grafik di bawah.
                                    </p>
                                </div>
                            </div>

                            <!-- Right -->
                            <div class="flex flex-wrap items-center gap-3">

                                <!-- School Selector -->
                                <div class="w-full sm:w-64">
    
                                    <label for="school-user-by-school-select" class="mb-2 block text-xs font-semibold text-slate-500">
                                        Pilih Sekolah
                                    </label>
    
                                    <div class="relative">
    
                                        <select id="school-user-by-school-select" class="w-full appearance-none rounded-xl border border-slate-200 
                                            bg-white px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 shadow-sm outline-none transition 
                                            hover:border-slate-300 cursor-pointer">

                                            <option value="">
                                                Semua Sekolah
                                            </option>
                                        </select>
    
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                            <i class="fa-solid fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- WARGA SEKOLAH -->
                <section class="mb-6">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

                        <!-- USERS BY ROLE -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                        <i class="fa-solid fa-users text-blue-600"></i>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-800">
                                            Pengguna Berdasarkan Role
                                        </h2>

                                        <p class="mt-0.5 text-sm text-slate-500">
                                            Jumlah pengguna aktif berdasarkan peran.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart -->
                            <div class="relative h-105">
                                <div id="school-user-by-role-content" class="h-full overflow-x-auto overflow-y-hidden px-6 py-5">
                                    <div class="relative h-full min-w-150">
                                        <canvas id="school-user-by-role-chart"></canvas>
                                    </div>
                                </div>

                                <!-- Loading -->
                                <div id="school-user-by-role-loading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/95 backdrop-blur-sm">
                                    <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-blue-500"></div>

                                    <p class="mt-3 text-sm font-semibold text-slate-600">
                                        Memuat data role...
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Mohon tunggu sebentar.
                                    </p>
                                </div>

                                <!-- Empty Message -->
                                <div id="school-user-by-role-empty" class="absolute inset-0 z-10 hidden flex-col items-center justify-center bg-white px-6">

                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                                        <i class="fa-solid fa-users text-xl text-slate-400"></i>
                                    </div>

                                    <p class="mt-3 text-sm font-semibold text-slate-600">
                                        Belum Ada Data Role
                                    </p>

                                    <p class="mt-1 max-w-xs text-center text-xs leading-relaxed text-slate-400">
                                        Belum terdapat pengguna aktif yang tercatat
                                        berdasarkan role.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ACCOUNT STATUS -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                        <i class="fa-solid fa-user-check text-emerald-600"></i>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-800">
                                            Status Akun
                                        </h2>

                                        <p class="mt-0.5 text-sm text-slate-500">
                                            Perbandingan akun aktif dan nonaktif.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart -->
                            <div class="relative h-105">
                                <div id="school-user-by-status-content" class="h-full overflow-x-auto overflow-y-hidden px-6 py-5">
                                    <div class="relative h-full min-w-125">
                                        <canvas id="school-user-by-status-chart"></canvas>
                                    </div>
                                </div>

                                <!-- Loading -->
                                <div id="school-user-by-status-loading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/95 backdrop-blur-sm">
                                    <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-emerald-500"></div>

                                    <p class="mt-3 text-sm font-semibold text-slate-600">
                                        Memuat status akun...
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Mohon tunggu sebentar.
                                    </p>
                                </div>

                                <!-- Empty Message -->
                                <div id="school-user-by-status-empty" class="absolute inset-0 z-10 hidden flex-col items-center justify-center bg-white px-6">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                                        <i class="fa-solid fa-user-check text-xl text-slate-400"></i>
                                    </div>

                                    <p class="mt-3 text-sm font-semibold text-slate-600">
                                        Belum Ada Data
                                    </p>

                                    <p class="mt-1 max-w-xs text-center text-xs leading-relaxed text-slate-400">
                                        Belum terdapat data status akun warga sekolah.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- USERS BY SCHOOL -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50">
                                            <i class="fa-solid fa-school text-cyan-600"></i>
                                        </div>

                                        <div>
                                            <h2 class="text-lg font-semibold text-slate-800">
                                                Pengguna Berdasarkan Sekolah
                                            </h2>

                                            <p class="mt-0.5 text-sm text-slate-500">
                                                Jumlah warga sekolah aktif pada setiap sekolah di bawah naungan yayasan.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart -->
                            <div class="relative h-100">

                                <div id="school-user-by-school-content" class="h-full overflow-x-auto overflow-y-hidden px-6 py-5">
                                    <div class="relative h-full min-w-162.5">
                                        <canvas id="school-user-by-school-chart"></canvas>
                                    </div>
                                </div>

                                <!-- Loading -->
                                <div id="school-user-by-school-loading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/95 backdrop-blur-sm">
                                    <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-cyan-500"></div>

                                    <p class="mt-3 text-sm font-semibold text-slate-600">
                                        Memuat data sekolah...
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Mohon tunggu sebentar.
                                    </p>
                                </div>

                                <!-- Empty Message -->
                                <div id="school-user-by-school-empty" class="absolute inset-0 z-10 hidden flex-col items-center justify-center bg-white px-6">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                                        <i class="fa-solid fa-school text-2xl text-slate-400"></i>
                                    </div>

                                    <p class="mt-4 text-sm font-semibold text-slate-600">
                                        Belum Ada Data Sekolah
                                    </p>

                                    <p class="mt-1 max-w-md text-center text-xs leading-relaxed text-slate-400">
                                        Belum terdapat pengguna aktif yang tercatat
                                        pada sekolah di bawah naungan yayasan.
                                    </p>
                                </div>
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

<script src="{{ asset('assets/js/features/lms/foundation/school-user/load-kpi.js') }}"></script> <!--- school user kpi ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-user/school-user-chart-helper.js') }}"></script> <!--- school user chart helper ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-user/load-school-user-by-school-chart.js') }}"></script> <!--- school user by school chart ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-user/load-school-user-by-role-chart.js') }}"></script> <!--- school user by role chart ---->
<script src="{{ asset('assets/js/features/lms/foundation/school-user/load-school-user-by-status-account-chart.js') }}"></script> <!--- school user by status account chart ---->