@include('components.sidebar-beranda', [
    'headerSideNav' => 'Contract Detail',
    'linkBackButton' => route('lms.finance.manage-contract.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role == 'Finance')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-10 mx-6 space-y-6">

            <main id="container" data-role="{{ $role }}" data-school-id="{{ $schoolId }}">

                <!-- SCHOOL HEADER -->
                <section class="mt-8">
                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">

                        <div class="absolute right-0 top-0 opacity-10">
                            <i class="fa-solid fa-file-signature text-[220px] translate-x-8 -translate-y-4"></i>
                        </div>

                        <div class="relative z-10">

                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                                <div>

                                    <div class="flex items-center gap-3 mb-3">

                                        <button onclick="window.history.back()" class="bg-white/15 hover:bg-white/25 transition px-4 py-2 rounded-xl text-sm
                                            cursor-pointer">
                                            
                                            <i class="fa-solid fa-arrow-left mr-2"></i>
                                            Kembali
                                        </button>

                                    </div>

                                    <h1 class="text-xl font-bold">
                                        {{ $schoolPartner->nama_sekolah ?? '-' }}
                                    </h1>

                                    <p class="text-slate-200 mt-2">
                                        Seluruh histori kontrak dan pembayaran sekolah.
                                    </p>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section class="mt-8">
                    <div id="kpi-skeleton" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                        @for ($i = 0; $i < 4; $i++)
                            <div class="bg-white border border-slate-200 rounded-3xl p-5 animate-pulse">

                                <div class="flex justify-between">

                                    <div class="flex-1">

                                        <div class="h-4 bg-slate-200 rounded w-24"></div>

                                        <div class="h-8 bg-slate-200 rounded w-32 mt-4"></div>

                                    </div>

                                    <div class="w-14 h-14 rounded-2xl bg-slate-200"></div>

                                </div>

                            </div>
                        @endfor

                    </div>
                    
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
    
                            <!-- TOTAL CONTRACT -->
                            <div class="bg-white border border-slate-200 rounded-3xl p-5 relative overflow-hidden">
    
                                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-10 -mt-10"></div>
    
                                <div class="flex items-center justify-between relative">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Jumlah Kontrak
                                        </p>
    
                                        <h3 id="total-contracts"
                                            class="text-xl font-black mt-2">
                                            -
                                        </h3>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <i class="fa-solid fa-file-signature text-xl"></i>
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- CONTRACT VALUE -->
                            <div class="bg-white border border-slate-200 rounded-3xl p-5 relative overflow-hidden">
    
                                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-10 -mt-10"></div>
    
                                <div class="flex items-center justify-between relative">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Nilai Kontrak Keseluruhan
                                        </p>
    
                                        <h3 id="lifetime-contract-value"
                                            class="text-xl font-black mt-2 text-blue-600">
                                            -
                                        </h3>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <i class="fa-solid fa-wallet text-xl"></i>
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- TOTAL PAID -->
                            <div class="bg-white border border-slate-200 rounded-3xl p-5 relative overflow-hidden">
    
                                <div class="absolute top-0 right-0 w-24 h-24 bg-green-50 rounded-full -mr-10 -mt-10"></div>
    
                                <div class="flex items-center justify-between relative">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Sudah Dibayarkan
                                        </p>
    
                                        <h3 id="total-paid"
                                            class="text-xl font-black mt-2 text-green-600">
                                            -
                                        </h3>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center">
                                        <i class="fa-solid fa-circle-check text-xl"></i>
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- OUTSTANDING -->
                            <div class="bg-white border border-slate-200 rounded-3xl p-5 relative overflow-hidden">
    
                                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-full -mr-10 -mt-10"></div>
    
                                <div class="flex items-center justify-between relative">
    
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Belum Dibayar
                                        </p>
    
                                        <h3 id="total-outstanding"
                                            class="text-xl font-black mt-2 text-orange-500">
                                            -
                                        </h3>
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-500 flex items-center justify-center">
                                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                                    </div>
    
                                </div>
    
                            </div>
    
                        </div>

                    </div>
                </section>

                <!-- CONTRACT LIST -->
                <section class="mt-8">
                    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">

                            <div>

                                <h2 class="text-xl font-bold">
                                    Riwayat Kontrak
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Seluruh kontrak yang pernah dibuat oleh sekolah ini.
                                </p>

                            </div>

                            <input type="text" placeholder="Cari Nomor Kontrak..." class="border border-slate-200 rounded-xl px-4 py-2 text-sm">
                        </div>

                    <div id="contract-list-skeleton" class="space-y-4">

                        @for ($i = 0; $i < 5; $i++)
                            <div class="bg-white border border-slate-200 rounded-2xl p-5 animate-pulse">

                                <!-- HEADER -->
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                                    <!-- LEFT -->
                                    <div class="flex items-start gap-4">

                                        <!-- ICON -->
                                        <div class="w-12 h-12 rounded-xl bg-slate-200"></div>

                                        <!-- TEXT -->
                                        <div class="space-y-2">
                                            <div class="h-5 w-40 bg-slate-200 rounded"></div>
                                            <div class="h-4 w-56 bg-slate-200 rounded"></div>
                                        </div>

                                    </div>

                                    <!-- ACTION -->
                                    <div class="flex items-center gap-3 mt-2 md:mt-0">

                                        <div class="w-10 h-6 bg-slate-200 rounded-full"></div>

                                        <div class="h-9 w-20 bg-slate-200 rounded-lg"></div>

                                    </div>
                                </div>

                                <!-- KPI GRID -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mt-5">

                                    @for ($j = 0; $j < 4; $j++)
                                        <div class="rounded-xl p-4 border border-slate-200 bg-slate-50">

                                            <div class="flex items-center justify-between">
                                                <div class="h-3 w-20 bg-slate-200 rounded"></div>
                                                <div class="w-5 h-5 bg-slate-200 rounded"></div>
                                            </div>

                                            <div class="h-5 w-28 bg-slate-200 rounded mt-3"></div>

                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                        <div id="grid-contract-list" class="divide-y divide-slate-100"></div>

                        <div class="pagination-container-contract-list"></div>

                        <div id="empty-message-contract-list" class="w-full h-80 hidden">
                            <span class="flex h-full items-center justify-center text-gray-500">
                                Tidak ada kontrak yang terdaftar.
                            </span>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/finance/contract/contract-detail/paginate-contract-list.js') }}"></script> <!--- paginate contract list ---->