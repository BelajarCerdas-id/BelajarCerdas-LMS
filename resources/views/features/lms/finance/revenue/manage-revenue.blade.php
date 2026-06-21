@include('components/sidebar-beranda', ['headerSideNav' => 'Revenue'])

@if (Auth::user()->role == 'Finance')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-10 mx-6 space-y-6">

            <main id="container" data-role="{{ $role }}">

                <!-- HEADER -->
                <section>
                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] rounded-3xl p-8 text-white shadow-xl overflow-hidden relative">
        
                        <div class="absolute right-0 top-0 opacity-10">
                            <i class="fa-solid fa-trophy text-[250px] translate-x-10 -translate-y-5"></i>
                        </div>
        
                        <div class="relative z-10">
        
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
        
                                <div>
                                    <h1 class="text-3xl font-bold">
                                        Revenue Management
                                    </h1>
        
                                    <p class="mt-2 text-slate-200 max-w-3xl">
                                        Monitor seluruh revenue sekolah partner, kontribusi revenue terbesar,
                                        pertumbuhan revenue, dan lifetime value setiap sekolah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section class="mt-8">
                    <div id="kpi-skeleton" class="animate-pulse">
    
                        <!-- KPI CARDS -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
    
                            @for ($i = 0; $i < 3; $i++)
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

                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
    
                            <!-- Lifetime Revenue -->
                            <div class="bg-white rounded-3xl border border-gray-300 p-6 shadow-sm">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Total Pendapatan
                                        </p>
    
                                        <h2 id="lifetime-revenue" class="text-3xl font-bold mt-2">
                                            Rp 0
                                        </h2>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
                                        <i class="fa-solid fa-money-bill-trend-up text-green-600"></i>
                                    </div>
    
                                </div>
                            </div>
    
                            <!-- Revenue Tahun Ini -->
                            <div class="bg-white rounded-3xl border border-gray-300 p-6 shadow-sm">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Pendapatan Tahun Ini
                                        </p>
    
                                        <h2 id="revenue-by-year" class="text-3xl font-bold mt-2">
                                            Rp 0
                                        </h2>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-chart-line text-blue-600"></i>
                                    </div>
    
                                </div>
                            </div>
    
                            <!-- Avg Revenue / school -->
                            <div class="bg-white rounded-3xl border border-gray-300 p-6 shadow-sm">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Rata - rata pendapatan dari tiap sekolah
                                        </p>
    
                                        <h2 id="avg-revenue-by-school" class="text-3xl font-bold mt-2">
                                            Rp 0
                                        </h2>
    
                                        <p id="school-count" class="text-slate-500 text-sm mt-2">
                                            0 sekolah
                                        </p>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center">
                                        <i class="fa-solid fa-school text-purple-600"></i>
                                    </div>
    
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!-- CHART -->
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

                    <div class="flex flex-wrap items-center gap-3">

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
            
            <!-- TOP LEADERBOARD -->
            <div class="bg-linear-to-br from-slate-50 via-white to-blue-50 border border-gray-300 rounded-3xl p-6 shadow-sm overflow-hidden relative">

                <div class="absolute -top-10 -right-10 w-52 h-52 bg-blue-100 rounded-full blur-3xl opacity-40"></div>

                <div class="relative z-10">

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-10">

                        <div>
                            <h2 class="text-2xl font-bold">
                                Top Lifetime Revenue Schools
                            </h2>

                            <p class="text-slate-500 text-sm mt-1">
                                Sekolah dengan kontribusi revenue terbesar sepanjang kerja sama.
                            </p>
                        </div>
                    </div>

                    <!-- SKELETON LEADERBOARD -->
                    <div id="leaderboard-skeleton" class="hidden animate-pulse">

                        <!-- HEADER SKELETON -->
                        <div class="flex flex-col lg:flex-row justify-between gap-4 mb-6">

                            <div class="space-y-3">
                                <div class="h-6 w-72 bg-slate-200 rounded-lg"></div>
                                <div class="h-4 w-96 bg-slate-200 rounded-lg"></div>
                            </div>

                            <div class="flex gap-3">
                                <div class="h-16 w-40 bg-slate-200 rounded-2xl"></div>
                                <div class="h-16 w-40 bg-slate-200 rounded-2xl"></div>
                            </div>
                        </div>

                        <!-- PODIUM SKELETON -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">

                            <!-- RANK 2 -->
                            <div class="pt-12 space-y-4">
                                <div class="mx-auto w-24 h-24 bg-slate-200 rounded-full"></div>
                                <div class="h-40 bg-slate-200 rounded-3xl"></div>
                                <div class="h-12 bg-slate-200 rounded-b-3xl"></div>
                            </div>

                            <!-- RANK 1 -->
                            <div class="pt-12 space-y-4">
                                <div class="mx-auto w-28 h-28 bg-slate-200 rounded-full"></div>
                                <div class="h-52 bg-slate-200 rounded-3xl"></div>
                                <div class="h-16 bg-slate-200 rounded-b-3xl"></div>
                            </div>

                            <!-- RANK 3 -->
                            <div class="pt-12 space-y-4">
                                <div class="mx-auto w-24 h-24 bg-slate-200 rounded-full"></div>
                                <div class="h-40 bg-slate-200 rounded-3xl"></div>
                                <div class="h-12 bg-slate-200 rounded-b-3xl"></div>
                            </div>

                        </div>

                        <!-- QUICK STATS SKELETON -->
                        <div class="grid md:grid-cols-3 gap-4 mt-6">

                            <div class="h-20 bg-slate-200 rounded-2xl"></div>
                            <div class="h-20 bg-slate-200 rounded-2xl"></div>
                            <div class="h-20 bg-slate-200 rounded-2xl"></div>

                        </div>

                    </div>
                    
                    <!-- PODIUM -->
                    <div id="leaderboard-content" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:gap-6 items-end">
    
                            <!-- JUARA 2 -->
                            <div class="order-2 lg:order-1 relative pt-8 sm:pt-10 lg:pt-12">

                                <!-- CROWN -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                    <div class="w-24 h-24 sm:w-24 sm:h-24 rounded-full bg-linear-to-br from-white via-slate-100 to-slate-200 border-[3px] border-slate-300
                                        shadow-[0_8px_25px_rgba(148,163,184,.35)] flex items-center justify-center relative">

                                        <!-- CROWN + LOGO -->
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                            <div class="relative">

                                                <!-- CROWN -->
                                                <div class="absolute -top-7 left-1/2 -translate-x-1/2">
                                                    <i class="fa-solid fa-crown text-slate-400 text-3xl sm:text-4xl drop-shadow-lg"></i>
                                                </div>

                                                <!-- LOGO -->
                                                <div class="w-24 h-24 rounded-full bg-white border-4 border-slate-300 shadow-lg overflow-hidden
                                                    relative flex items-center justify-center">

                                                    <img id="rank-2-logo"
                                                        class="school-logo w-full h-full object-contain hidden"
                                                        alt="School Logo">

                                                    <!-- FALLBACK -->
                                                    <div id="rank-2-fallback"
                                                        class="fallback-logo w-full h-full flex items-center justify-center bg-linear-to-br
                                                        from-slate-100 to-slate-200 text-slate-500">

                                                        <i class="fa-solid fa-school text-2xl sm:text-3xl"></i>
                                                    </div>

                                                </div>

                                                <!-- RANK BADGE -->
                                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-linear-to-b from-slate-400 to-slate-600 text-white
                                                    px-3 sm:px-5 py-1 rounded-xl font-bold shadow-lg text-xs sm:text-sm">
                                                    #2
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- CARD -->
                                <div class="bg-linear-to-b from-white to-slate-200 border border-slate-300 rounded-t-3xl
                                    pt-26 sm:pt-20 pb-6 px-4 sm:px-6 text-center shadow-lg">

                                    <h3 id="rank-2-name"
                                        class="school-name font-bold text-lg sm:text-xl text-slate-800 wrap-break-word leading-tight">
                                        -
                                    </h3>

                                    <p id="rank-2-meta"
                                    class="school-meta text-slate-500 mt-2 text-xs sm:text-sm">
                                        -
                                    </p>

                                    <h4 id="rank-2-revenue"
                                        class="school-revenue font-black text-2xl sm:text-3xl md:text-lg text-slate-600 mt-4 sm:mt-5 wrap-break-word">
                                        0
                                    </h4>

                                    <!-- Progress -->
                                    <div class="mt-5 sm:mt-6">

                                        <div class="flex justify-between text-xs sm:text-sm mb-2">

                                            <span class="text-slate-600">
                                                Revenue Contribution
                                            </span>

                                            <span id="rank-2-contribution-text"
                                                class="font-semibold text-xs sm:text-sm">
                                                0%
                                            </span>

                                        </div>

                                        <div class="w-full bg-slate-300 h-2 sm:h-3 rounded-full overflow-hidden">
                                            <div id="rank-2-contribution-bar"
                                                class="bg-linear-to-r from-slate-400 to-slate-600 h-2 sm:h-3 rounded-full"
                                                style="width: 0%;">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- PODIUM -->
                                <div class="h-12 sm:h-14 lg:h-15 bg-linear-to-b from-slate-400 to-slate-600 rounded-b-3xl flex items-center justify-center relative">

                                    <i class="fa-solid fa-award absolute left-4 sm:left-8 text-white/30 text-lg sm:text-2xl"></i>

                                    <span class="text-3xl sm:text-4xl lg:text-5xl font-black text-white">
                                        2
                                    </span>

                                    <i class="fa-solid fa-award absolute right-4 sm:right-8 text-white/30 text-lg sm:text-2xl"></i>

                                </div>

                            </div>
    
                            <!-- JUARA 1 -->
                            <div class="order-1 lg:order-2 relative pt-8 md:pt-10 lg:pt-12">

                                <!-- CROWN -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                    <div class="w-24 h-24 sm:w-24 sm:h-24 rounded-full bg-linear-to-br from-[#FFF8E8] to-[#F1D58A] border-[3px] border-[#D8A847] 
                                        shadow-[0_10px_30px_rgba(216,168,71,.35)] #1flex items-center justify-center relative">

                                        <!-- CROWN + LOGO -->
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                            <div class="relative">

                                                <!-- CROWN -->
                                                <div class="absolute -top-7 left-1/2 -translate-x-1/2">
                                                    <i class="fa-solid fa-crown text-[#C9972C] text-3xl sm:text-4xl drop-shadow-lg"></i>
                                                </div>

                                                <!-- LOGO -->
                                                <div class="w-24 h-24 rounded-full bg-white border-4 border-[#D8A847] shadow-[0_10px_30px_rgba(216,168,71,.35)] 
                                                    overflow-hidden relative flex items-center justify-center">

                                                    <img id="rank-1-logo" class="school-logo w-full h-full object-contain hidden" alt="School Logo">

                                                    <!-- FALLBACK -->
                                                    <div id="rank-1-fallback"
                                                        class="fallback-logo w-full h-full flex items-center justify-center bg-linear-to-br
                                                        from-orange-100 to-orange-200 text-[#C9972C]">

                                                        <i class="fa-solid fa-school text-2xl sm:text-3xl"></i>
                                                    </div>

                                                </div>

                                                <!-- RANK BADGE -->
                                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-linear-to-b from-[#E7C56D] to-[#C9972C] text-white
                                                    px-3 sm:px-5 py-1 rounded-xl font-bold shadow-lg text-xs sm:text-sm">
                                                    #1
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- CARD -->
                                <div class="bg-linear-to-b from-[#FFF9EA] to-[#F9F1DD] border border-[#D8A847] rounded-t-3xl shadow-xl text-center 
                                    pt-26 sm:pt-20 pb-5 sm:pb-6 px-4 sm:px-6 overflow-hidden">

                                    <!-- NAME -->
                                    <h3 id="rank-1-name"
                                        class="school-name font-bold text-lg sm:text-xl text-slate-800 wrap-break-word leading-tight">
                                        -
                                    </h3>

                                    <!-- META -->
                                    <p id="rank-1-meta"
                                        class="school-meta text-xs sm:text-sm text-slate-500 mt-1">
                                        -
                                    </p>

                                    <!-- REVENUE -->
                                    <h4 id="rank-1-revenue"
                                        class="school-revenue text-2xl sm:text-3xl md:text-lg font-black mt-5 text-slate-800 wrap-break-word">
                                        Rp 0
                                    </h4>

                                    <!-- Progress -->
                                    <div class="mt-6">

                                        <div class="flex items-center text-start justify-between text-sm mb-2">

                                            <span class="text-slate-600">
                                                Revenue Contribution
                                            </span>

                                            <span id="rank-1-contribution-text" class="font-semibold">
                                                0%
                                            </span>

                                        </div>

                                        <div class="w-full bg-[#EFE4C4] h-3 rounded-full">
                                            <div id="rank-1-contribution-bar"
                                                class="bg-linear-to-r from-[#D8A847] to-[#C9972C] h-3 rounded-full w-[14%]"
                                                style="width: 0%;">
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <!-- PODIUM -->
                                <div class="h-16 sm:h-18 md:h-20 bg-linear-to-b from-[#D8A847] to-[#B98520] rounded-b-3xl flex items-center justify-center 
                                    relative overflow-hidden">

                                    <i class="fa-solid fa-award absolute left-8 text-white/30 text-3xl"></i>

                                    <span class="text-4xl sm:text-5xl md:text-6xl font-black text-white drop-shadow-lg">
                                        1
                                    </span>

                                    <i class="fa-solid fa-award absolute right-8 text-white/30 text-3xl"></i>

                                </div>

                            </div>
    
                            <!-- JUARA 3 -->
                            <div class="order-3 relative pt-8 sm:pt-10 lg:pt-12">

                                <!-- CROWN -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">

                                    <div class="w-24 h-24 rounded-full bg-linear-to-br from-[#FFF5EF] via-[#FFDCC7] to-[#F7B489] border-[3px] border-[#E28B54]
                                        shadow-[0_8px_25px_rgba(226,139,84,.35)] flex items-center justify-center relative">

                                        <!-- CROWN + LOGO -->
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                            <div class="relative">

                                                <!-- CROWN -->
                                                <div class="absolute -top-7 left-1/2 -translate-x-1/2">
                                                    <i class="fa-solid fa-crown text-[#C96F38] text-3xl sm:text-4xl drop-shadow-lg"></i>
                                                </div>

                                                <!-- LOGO -->
                                                <div class="w-24 h-24 rounded-full bg-white border-4 border-[#E28B54] shadow-lg overflow-hidden relative
                                                    flex items-center justify-center">

                                                    <img id="rank-3-logo"
                                                        class="school-logo w-full h-full object-contain hidden"
                                                        alt="School Logo">

                                                    <!-- FALLBACK -->
                                                    <div id="rank-3-fallback"
                                                        class="fallback-logo w-full h-full flex items-center justify-center bg-linear-to-br
                                                        from-[#FFF3EA] to-[#F7C6A0] text-[#C96F38]">

                                                        <i class="fa-solid fa-school text-2xl sm:text-3xl"></i>
                                                    </div>

                                                </div>

                                                <!-- RANK BADGE -->
                                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-linear-to-b from-[#E28B54] to-[#C96F38]
                                                    text-white px-3 sm:px-5 py-1 rounded-xl font-bold shadow-lg text-xs sm:text-sm">
                                                    #3
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- CARD -->
                                <div class="bg-linear-to-b from-white to-orange-100 border border-orange-300
                                    rounded-t-3xl pt-26 sm:pt-20 pb-6 px-4 sm:px-6 text-center shadow-lg">

                                    <h3 id="rank-3-name"
                                        class="school-name font-bold text-lg sm:text-xl text-slate-800 wrap-break-word leading-tight">
                                        -
                                    </h3>

                                    <p id="rank-3-meta"
                                    class="school-meta text-slate-500 mt-2 text-xs sm:text-sm">
                                        -
                                    </p>

                                    <h4 id="rank-3-revenue"
                                        class="school-revenue text-2xl sm:text-3xl md:text-lg font-black text-[#C96F38] mt-4 sm:mt-5 wrap-break-word">
                                        Rp 0
                                    </h4>

                                    <!-- Progress -->
                                    <div class="mt-5 sm:mt-6">

                                        <div class="flex justify-between text-xs sm:text-sm mb-2">

                                            <span class="text-slate-600">
                                                Revenue Contribution
                                            </span>

                                            <span id="rank-3-contribution-text"
                                                class="font-semibold text-xs sm:text-sm">
                                                0%
                                            </span>

                                        </div>

                                        <div class="w-full bg-orange-200 h-2 sm:h-3 rounded-full overflow-hidden">

                                            <div id="rank-3-contribution-bar"
                                                class="bg-linear-to-r from-[#E28B54] to-[#C96F38] h-2 sm:h-3 rounded-full"
                                                style="width: 0%;">
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                <!-- PODIUM -->
                                <div class="h-12 sm:h-14 lg:h-12.5 bg-linear-to-b from-[#E28B54] to-[#C96F38]
                                    rounded-b-3xl flex items-center justify-center relative">

                                    <i class="fa-solid fa-award absolute left-4 sm:left-8 text-white/30 text-lg sm:text-2xl"></i>

                                    <span class="text-3xl sm:text-4xl lg:text-5xl font-black text-white">
                                        3
                                    </span>

                                    <i class="fa-solid fa-award absolute right-4 sm:right-8 text-white/30 text-lg sm:text-2xl"></i>

                                </div>

                            </div>
                        </div>
                        
                        <!-- QUICK STATS -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mt-6">

                            <!-- TOTAL TOP 3 -->
                            <div class="bg-linear-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-4 sm:p-5">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-dollar-sign text-green-600 text-sm sm:text-base"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-[11px] sm:text-xs text-slate-500">
                                            Total Top 3 Revenue
                                        </p>

                                        <h4 id="top-3-total"
                                            class="font-bold text-green-600 mt-1
                                            text-lg sm:text-xl xl:text-2xl
                                            wrap-break-word leading-tight">
                                            Rp 0
                                        </h4>

                                    </div>

                                </div>

                            </div>

                            <!-- AVERAGE -->
                            <div class="bg-linear-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-4 sm:p-5">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-chart-column text-blue-600 text-sm sm:text-base"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-[11px] sm:text-xs text-slate-500">
                                            Average Revenue
                                        </p>

                                        <h4 id="avg-top-3-revenue"
                                            class="font-bold text-blue-600 mt-1
                                            text-lg sm:text-xl xl:text-2xl
                                            wrap-break-word leading-tight">
                                            Rp 0
                                        </h4>

                                    </div>

                                </div>

                            </div>

                            <!-- CONTRIBUTION -->
                            <div class="bg-linear-to-r from-purple-50 to-violet-50 border border-purple-200 rounded-2xl p-4 sm:p-5">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-chart-pie text-purple-600 text-sm sm:text-base"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-[11px] sm:text-xs text-slate-500">
                                            Revenue Contribution
                                        </p>

                                        <h4 id="top-3-contribution"
                                            class="font-bold text-purple-600 mt-1
                                            text-lg sm:text-xl xl:text-2xl
                                            wrap-break-word leading-tight">
                                            0%
                                        </h4>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="leaderboard-empty"
                        class="hidden h-96 bg-slate-50 rounded-3xl border border-dashed border-slate-200">

                        <div class="flex flex-col items-center justify-center h-full px-6">

                            <div class="w-20 h-20 rounded-full bg-amber-100 flex items-center justify-center mb-4">

                                <i class="fa-solid fa-trophy text-3xl text-amber-500"></i>

                            </div>

                            <h4 class="text-lg font-bold text-slate-700 text-center">
                                Leaderboard Belum Tersedia
                            </h4>

                            <p class="text-sm text-slate-500 text-center max-w-md mt-2">
                                Belum terdapat sekolah yang memiliki revenue. Peringkat Top 3 akan muncul secara otomatis setelah pembayaran termin kontrak berhasil diproses.
                            </p>

                        </div>

                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="bg-white border border-gray-300 rounded-3xl shadow-sm overflow-hidden">

                <!-- HEADER -->
                <div class="p-6 border-b border-slate-200">

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                        <div>
                            <h2 class="font-bold text-xl">
                                Lifetime Revenue Ranking
                            </h2>

                            <p class="text-slate-500 text-sm mt-1">
                                Ranking seluruh sekolah berdasarkan total revenue yang dihasilkan.
                            </p>
                        </div>

                        <div class="flex flex-col md:flex-row gap-3">

                            <!--- search bar --->
                            <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-full lg:w-max">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                </svg>
                                <input id="search_school" type="search" class="grow text-sm"
                                    placeholder="Cari nama sekolah..." autocomplete="OFF" />
                            </label>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">

                    <!-- SKELETON TABLE -->
                    <div id="lifetime-revenue-ranking-skeleton">
                        <table class="min-w-full">
                            <tbody>

                                @for ($i = 0; $i < 5; $i++)
                                <tr class="animate-pulse border-b border-slate-100">

                                    <!-- Rank -->
                                    <td class="px-4 md:px-6 py-4 w-20">
                                        <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                                    </td>

                                    <!-- School -->
                                    <td class="px-4 md:px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 md:w-14 md:h-14 rounded-full bg-slate-200 shrink-0"></div>
                                            <div class="flex-1">
                                                <div class="h-4 w-40 bg-slate-200 rounded mb-2"></div>
                                                <div class="h-3 w-24 bg-slate-100 rounded"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Revenue -->
                                    <td class="px-4 md:px-6 py-4">
                                        <div class="space-y-2">
                                            <div class="h-4 w-28 bg-slate-200 rounded"></div>
                                            <div class="h-3 w-20 bg-slate-100 rounded"></div>
                                        </div>
                                    </td>

                                    <!-- Contribution -->
                                    <td class="px-4 md:px-6 py-4 min-w-40">
                                        <div>
                                            <div class="flex justify-between mb-2">
                                                <div class="h-3 w-16 bg-slate-200 rounded"></div>
                                                <div class="h-3 w-10 bg-slate-200 rounded"></div>
                                            </div>

                                            <div class="h-2 w-full bg-slate-200 rounded-full"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- REAL TABLE -->
                    <table id="table-lifetime-revenue-ranking" class="min-w-full text-sm border-collapse">

                        <thead class="thead-table-lifetime-revenue-ranking hidden bg-slate-50">

                            <tr class="text-slate-600">
                                <th class="text-left px-6 py-4 text-xs uppercase text-slate-500 font-semibold">
                                    Rank
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-slate-500 font-semibold">
                                    School
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-slate-500 font-semibold">
                                    Revenue
                                </th>

                                <th class="text-left px-6 py-4 text-xs uppercase text-slate-500 font-semibold">
                                    Contribution
                                </th>
                            </tr>

                        </thead>

                        <tbody id="tbody-lifetime-revenue-ranking">
                            <!-- show data in ajax -->
                        </tbody>

                    </table>

                </div>

                <div class="pagination-container-lifetime-revenue-ranking flex justify-center py-5"></div>

                <!-- EMPTY -->
                <div id="empty-message-lifetime-revenue-ranking"
                    class="hidden h-96 bg-slate-50 rounded-2xl border border-dashed border-slate-200">

                    <div class="flex flex-col items-center justify-center h-full px-6">

                        <div
                            class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-4">

                            <i class="fas fa-file-signature text-3xl text-blue-500"></i>

                        </div>

                        <h4 id="empty-title"
                            class="text-lg font-bold text-slate-700 text-center">

                            Ranking Revenue Belum Tersedia

                        </h4>

                        <p id="empty-description"
                            class="text-sm text-slate-500 text-center max-w-md mt-2">
                            Belum ada sekolah yang memiliki revenue tercatat. Ranking akan muncul setelah pembayaran termin kontrak berhasil diproses.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/finance/revenue/revenue-management-kpi.js') }}"></script> <!--- render kpi ---->
<script src="{{ asset('assets/js/features/lms/finance/revenue/revenue-management-leaderboard.js') }}"></script> <!--- render leaderboard ---->
<script src="{{ asset('assets/js/features/lms/finance/revenue/revenue-management-chart.js') }}"></script> <!--- render chart ---->
<script src="{{ asset('assets/js/features/lms/finance/revenue/revenue-management-ranking.js') }}"></script> <!--- revenue management ranking ---->