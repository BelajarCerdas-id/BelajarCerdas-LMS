@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role == 'Finance')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-10 mx-6 space-y-6">

            <main id="container" data-role="{{ $role }}">

                <!-- HERO -->
                <section>
                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] rounded-3xl p-8 text-white shadow-xl overflow-hidden relative">
        
                        <div class="absolute right-0 top-0 opacity-10">
                            <i class="fa-solid fa-chart-line text-[250px] translate-x-10 -translate-y-5"></i>
                        </div>
        
                        <div class="relative z-10">
                            <h1 class="text-3xl font-bold">
                                Finance Dashboard
                            </h1>
        
                            <p class="mt-2 text-indigo-100 max-w-2xl">
                                Monitor revenue, kontrak sekolah, jumlah siswa aktif, dan performa bisnis dalam satu dashboard.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section class="mt-6">
                    <div id="kpi-skeleton" class="animate-pulse">
            
                        <!-- KPI CARDS -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-5">
            
                            @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white border border-slate-200 rounded-3xl p-6">
            
                                <div class="flex justify-between items-start">
            
                                    <div class="flex-1">
            
                                        <!-- Title -->
                                        <div class="h-4 w-32 bg-slate-200 rounded"></div>
            
                                        <!-- Value -->
                                        <div class="h-10 w-44 bg-slate-300 rounded mt-4"></div>
            
                                        <!-- Description -->
                                        <div class="h-3 w-40 bg-slate-100 rounded mt-4"></div>
            
                                    </div>
            
                                    <!-- Icon -->
                                    <div class="w-14 h-14 rounded-2xl bg-slate-200"></div>
            
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
    
                    <!-- KPI CONTENT -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-5">
                            
                            <!-- Lifetime Revenue -->
                            <div class="bg-white rounded-3xl border border-gray-300 p-6 shadow-sm">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Total Pendapatan
                                        </p>
    
                                        <h2 id="lifetime-revenue" class="text-xl font-bold mt-2">
                                            Rp 0
                                        </h2>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
                                        <i class="fa-solid fa-money-bill-trend-up text-green-600"></i>
                                    </div>
    
                                </div>
                            </div>
                
                            <div class="border border-gray-300 bg-white rounded-3xl p-5 shadow-sm hover:shadow-xl transition-all duration-300">
                
                                <div class="flex justify-between items-start">
                
                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Kontrak Aktif
                                        </p>
                
                                        <h2 id="contract-active" class="text-xl font-bold mt-2">
                                            0
                                        </h2>
                                    </div>
                
                                    <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-file-signature text-blue-600 text-xl"></i>
                                    </div>
                
                                </div>
                
                            </div>
                
                            <div class="border border-gray-300 bg-white rounded-3xl p-5 shadow-sm hover:shadow-xl transition-all duration-300">
                
                                <div class="flex justify-between items-start">
                
                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Total Sekolah
                                        </p>
                
                                        <h2 id="total-schools" class="text-xl font-bold mt-2">
                                            0
                                        </h2>
                
                                        <p class="text-slate-500 text-sm mt-2">
                                            Partner aktif
                                        </p>
                                    </div>
                
                                    <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center">
                                        <i class="fa-solid fa-school text-purple-600 text-xl"></i>
                                    </div>
                
                                </div>
                
                            </div>
                
                            <div class="border border-gray-300 bg-white rounded-3xl p-5 shadow-sm hover:shadow-xl transition-all duration-300">
                
                                <div class="flex justify-between items-start">
                
                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Total Siswa Aktif
                                        </p>
                
                                        <h2 id="total-students" class="text-xl font-bold mt-2">
                                            0
                                        </h2>
                
                                        <p class="text-slate-500 text-sm mt-2">
                                            Dari seluruh sekolah
                                        </p>
                                    </div>
                
                                    <div class="w-14 h-14 rounded-2xl bg-orange-100 flex items-center justify-center">
                                        <i class="fa-solid fa-user-graduate text-orange-600 text-xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!-- CHART -->
            <section>
                <div class="bg-white rounded-4xl md:rounded-[2.5rem] p-5 md:p-8 shadow-sm border border-gray-300">

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">

                        <div>
                            <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">
                                Revenue Trend
                            </h3>

                            <p class="text-slate-500 mt-1 text-sm font-medium">
                                Statistik pendapatan berdasarkan periode yang dipilih.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-3">

                            <!-- Period -->
                            <select id="chartPeriod"
                                class="h-11 px-4 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 hidden">
                                <option value="monthly" selected>Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>

                            <!-- Year -->
                            <select id="chartYear"
                                class="hidden h-11 px-4 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                            </select>

                        </div>
                    </div>
                    
                    <!-- LOADING SCREEN -->
                    <div id="revenue-chart-loading"
                        class="flex flex-col items-center justify-center h-112 bg-slate-50 rounded-3xl border border-dashed border-slate-200">

                        <!-- Spinner -->
                        <div class="relative">
                            <div class="w-16 h-16 rounded-full border-4 border-slate-200"></div>
                            <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-[#2563EB]"></div>
                        </div>

                        <!-- Title -->
                        <h4 class="mt-6 text-lg font-semibold text-slate-800">
                            Memuat Grafik Pendapatan
                        </h4>

                        <!-- Description -->
                        <p class="text-sm text-slate-500 text-center max-w-md mt-2 leading-relaxed">
                            Menampilkan data sesuai periode yang dipilih.
                        </p>

                        <!-- Small status -->
                        <div class="flex items-center gap-2 mt-5 text-xs text-slate-400">
                            <i class="fas fa-chart-line"></i>
                            Mengambil data...
                        </div>

                    </div>

                    <!-- EMPTY -->
                    <div id="empty-message-revenue-chart" class="hidden">
                        <div class="h-112 flex flex-col items-center justify-center text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200 px-8">

                            <div class="w-20 h-20 rounded-3xl bg-blue-100 flex items-center justify-center text-[#2563EB] text-3xl">
                                <i class="fas fa-chart-line"></i>
                            </div>

                            <h4 class="mt-6 text-xl font-black text-slate-800">
                                Belum Ada Data Pendapatan
                            </h4>

                            <p class="max-w-lg mt-3 text-sm text-slate-500">
                                Tidak ditemukan data revenue pada periode ini.
                            </p>
                        </div>
                    </div>

                    <!-- CHART -->
                    <div id="revenue-chart-container" class="h-80 md:h-112.5 hidden">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>

                </div>
            </section>

            <!-- EXPIRING + TOP SCHOOL -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                <!-- CONTRACT EXPIRING -->
                <div class="border border-gray-300 bg-white rounded-3xl shadow-sm p-6 flex flex-col">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg">
                            Contract Akan Berakhir
                        </h3>
                    </div>

                    <!-- SKELETON -->
                    <div id="contract-expiring-skeleton" class="hidden space-y-3">

                        <!-- item 1 -->
                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 animate-pulse">

                            <div class="flex items-start justify-between gap-3">

                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-slate-200 rounded w-2/3"></div>
                                    <div class="h-3 bg-slate-200 rounded w-1/2"></div>
                                </div>

                                <div class="h-6 w-16 bg-slate-200 rounded-full"></div>

                            </div>

                            <div class="mt-3 h-2 bg-slate-200 rounded-full w-full"></div>

                        </div>

                        <!-- item 2 -->
                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 animate-pulse">

                            <div class="flex items-start justify-between gap-3">

                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                                    <div class="h-3 bg-slate-200 rounded w-2/3"></div>
                                </div>

                                <div class="h-6 w-16 bg-slate-200 rounded-full"></div>

                            </div>

                            <div class="mt-3 h-2 bg-slate-200 rounded-full w-full"></div>

                        </div>

                        <!-- item 3 -->
                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 animate-pulse">

                            <div class="flex items-start justify-between gap-3">

                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                                    <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                                </div>

                                <div class="h-6 w-14 bg-slate-200 rounded-full"></div>

                            </div>

                            <div class="mt-3 h-2 bg-slate-200 rounded-full w-full"></div>

                        </div>

                    </div>

                    <!-- LIST -->
                    <div id="contract-expiring-list"
                        class="space-y-3 max-h-80 overflow-y-auto pr-1 hidden">
                    </div>

                    <!-- EMPTY -->
                    <div id="contract-expiring-empty" class="hidden">

                        <div class="flex flex-col items-center justify-center py-12 text-center px-4">

                            <!-- ICON -->
                            <div class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                <i class="fa-solid fa-calendar-check text-slate-400 text-lg md:text-xl"></i>
                            </div>

                            <!-- TITLE -->
                            <p class="text-base md:text-lg font-semibold text-slate-700">
                                Tidak ada kontrak yang akan berakhir dalam 30 hari
                            </p>
                        </div>

                    </div>
                </div>

                <!-- TOP REVENUE -->
                <section>
                    <div class="border border-gray-300 bg-white rounded-3xl shadow-sm p-6">

                        <!-- HEADER -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg">
                                Top Revenue Schools
                            </h3>
                        </div>

                        <!-- SKELETON -->
                        <div id="top-revenue-skeleton" class="space-y-4 hidden">

                            @for ($i = 0; $i < 3; $i++)
                            <div class="flex items-center justify-between p-4 rounded-2xl animate-pulse">

                                <div class="flex items-center gap-4">

                                    <div class="w-12 h-12 bg-slate-200 rounded-full"></div>

                                    <div class="space-y-2">
                                        <div class="h-4 w-40 bg-slate-200 rounded"></div>
                                        <div class="h-3 w-24 bg-slate-100 rounded"></div>
                                    </div>

                                </div>

                                <div class="text-right space-y-2">
                                    <div class="h-4 w-24 bg-slate-200 rounded"></div>
                                    <div class="h-3 w-20 bg-slate-100 rounded"></div>
                                </div>

                            </div>
                            @endfor

                        </div>

                        <!-- LIST -->
                        <div id="grid-list-top-revenue" class="space-y-4 hidden max-h-70 overflow-y-auto"></div>

                        <!-- EMPTY -->
                        <div id="empty-message-top-revenue"
                            class="hidden text-center py-10 text-slate-500">

                            <i class="fa-solid fa-chart-line text-3xl mb-3 text-slate-300"></i>

                            <p class="text-sm">
                                Belum ada data revenue sekolah.
                            </p>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/finance/dashboard/kpi-dashboard.js') }}"></script> <!--- render kpi ---->
<script src="{{ asset('assets/js/features/lms/finance/dashboard/chart-dashboard.js') }}"></script> <!--- render chart ---->
<script src="{{ asset('assets/js/features/lms/finance/dashboard/top-revenue-dashboard.js') }}"></script> <!--- render top revenue ---->
<script src="{{ asset('assets/js/features/lms/finance/dashboard/contract-expiring-dashboard.js') }}"></script> <!--- render contract expiring ---->