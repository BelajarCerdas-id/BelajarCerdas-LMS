@include('components.sidebar-beranda', [
    'headerSideNav' => 'Payment Detail',
    'linkBackButton' => route('lms.finance.manage-contract-detail.view', [$role, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role == 'Finance')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">

        <div class="my-10 mx-6 space-y-6">
            <main id="container" data-role="{{ $role }}" data-school-id="{{ $schoolId }}" data-contract-id="{{ $contractId }}">

                <!-- HEADER -->
                <section>
                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] rounded-2xl md:rounded-3xl p-5 md:p-8 text-white shadow-xl">
                        <div class="flex flex-col gap-4">
                            <div>
                                <div class="flex items-start gap-3">

                                    <button onclick="window.history.back()"
                                        class="shrink-0 w-9 h-9 md:w-10 md:h-10 rounded-xl bg-white/20 flex items-center justify-center hover:bg-white/30 transition
                                        cursor-pointer">

                                        <i class="fa-solid fa-arrow-left text-sm"></i>

                                    </button>

                                    <div class="min-w-0">

                                        <p class="text-slate-200 text-xs md:text-sm">
                                            Detail Pembayaran Kontrak
                                        </p>

                                        <h1 class="text-xl md:text-3xl font-bold break-all">
                                            {{ $contract->contract_number ?? '-' }}
                                        </h1>

                                    </div>

                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">

                                    <span class="px-3 py-1.5 rounded-xl bg-green-500/20 text-green-200 text-xs md:text-sm">
                                        {{ $contract->status ?? '-' }}
                                    </span>

                                    <span class="px-3 py-1.5 rounded-xl bg-white/20 text-xs md:text-sm">
                                        {{ $contract->SchoolPartner->nama_sekolah ?? '-' }}
                                    </span>

                                    <span class="px-3 py-1.5 rounded-xl bg-white/20 text-xs md:text-sm">
                                        {{ \Carbon\Carbon::parse($contract->start_contract)->locale('id')->translatedFormat('F Y') }}
                                        -
                                        {{ \Carbon\Carbon::parse($contract->end_contract)->locale('id')->translatedFormat('F Y') }}
                                    </span>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section class="mt-8">
                    <div id="kpi-skeleton" class="grid md:grid-cols-3 gap-6">
            
                        @for($i = 0; $i < 3; $i++)
            
                            <div class="bg-white border border-gray-300 rounded-3xl p-6 animate-pulse">
            
                                <div class="flex justify-between">
            
                                    <div class="flex-1">
            
                                        <div class="h-4 w-28 bg-slate-200 rounded"></div>
            
                                        <div class="h-8 w-40 bg-slate-200 rounded mt-4"></div>
            
                                    </div>
            
                                    <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
            
                                </div>
            
                            </div>
            
                        @endfor
            
                    </div>
    
                    <div id="kpi-content" class="hidden">
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-3xl p-6">
                
                                <div class="flex justify-between">
                
                                    <div>
                
                                        <p class="text-slate-500 text-sm">
                                            Total Nilai Kontrak
                                        </p>
                
                                        <h3 id="total-contract-value" class="text-2xl font-black mt-2">
                                            0
                                        </h3>
                
                                    </div>
                
                                    <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-file-contract text-blue-600"></i>
                                    </div>
                
                                </div>
                
                            </div>
                
                            <div class="bg-green-50 border border-green-200 rounded-3xl p-6">
                
                                <div class="flex justify-between">
                
                                    <div>
                
                                        <p class="text-slate-500 text-sm">
                                            Sudah Dibayarkan
                                        </p>
                
                                        <h3 id="total-paid" class="text-2xl font-black mt-2 text-green-600">
                                            0
                                        </h3>
                
                                    </div>
                
                                    <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center">
                                        <i class="fa-solid fa-circle-check text-green-600"></i>
                                    </div>
                
                                </div>
                
                            </div>
                
                            <div class="bg-red-50 border border-red-200 rounded-3xl p-6">
                
                                <div class="flex justify-between">
                
                                    <div>
                
                                        <p class="text-slate-500 text-sm">
                                            Sisa Tagihan
                                        </p>
                
                                        <h3 id="outstanding-amount" class="text-2xl font-black mt-2 text-red-600">
                                            0
                                        </h3>
                
                                    </div>
                
                                    <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center">
                                        <i class="fa-solid fa-clock text-red-600"></i>
                                    </div>
                
                                </div>
                
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PAYMENT PROGRESS -->
                <section class="mt-8">
                    <div class="bg-white border border-gray-300 rounded-3xl p-6 mt-6">
        
                        <div class="flex justify-between items-center mb-3">
        
                            <h3 class="font-bold text-lg">
                                Progress Pembayaran
                            </h3>
        
                            <span id="payment-progress-label"
                                class="font-bold text-blue-600 text-lg">
                                0%
                            </span>
        
                        </div>
        
                        <!-- SKELETON -->
                        <div id="progress-skeleton">
        
                            <div class="animate-pulse">
        
                                <div class="h-5 bg-slate-200 rounded-full"></div>
        
                                <div class="grid md:grid-cols-3 gap-4 mt-5">
        
                                    <div>
                                        <div class="h-3 w-24 bg-slate-200 rounded"></div>
                                        <div class="h-5 w-32 bg-slate-200 rounded mt-2"></div>
                                    </div>
        
                                    <div>
                                        <div class="h-3 w-16 bg-slate-200 rounded"></div>
                                        <div class="h-5 w-32 bg-slate-200 rounded mt-2"></div>
                                    </div>
        
                                    <div>
                                        <div class="h-3 w-20 bg-slate-200 rounded"></div>
                                        <div class="h-5 w-32 bg-slate-200 rounded mt-2"></div>
                                    </div>
        
                                </div>
        
                            </div>
        
                        </div>
        
                        <!-- CONTENT -->
                        <div id="progress-content" class="hidden">
        
                            <div class="w-full bg-slate-100 rounded-full h-5 overflow-hidden">
        
                                <div id="payment-progress-bar"
                                    class="relative h-5 rounded-full transition-all duration-1500 ease-out"
                                    style="width:0%">
        
                                    <!-- shimmer -->
                                    <div class="progress-shimmer absolute inset-0"></div>
        
                                </div>
        
                            </div>
        
                            <div class="grid md:grid-cols-3 gap-4 mt-5">
        
                                <div>
        
                                    <p class="text-sm text-slate-500">
                                        Nilai Kontrak
                                    </p>
        
                                    <h4 id="contract-value-summary"
                                        class="font-bold text-lg">
                                        0
                                    </h4>
        
                                </div>
        
                                <div>
        
                                    <p class="text-sm text-slate-500">
                                        Sudah Dibayar
                                    </p>
        
                                    <h4 id="paid-summary"
                                        class="font-bold text-lg text-green-600">
                                        0
                                    </h4>
        
                                </div>
        
                                <div>
        
                                    <p class="text-sm text-slate-500">
                                        Tersisa
                                    </p>
        
                                    <h4 id="remaining-summary"
                                        class="font-bold text-lg text-red-600">
                                        0
                                    </h4>
        
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section class="mt-8">
                    <div class="bg-white border border-gray-300 rounded-3xl shadow-sm overflow-hidden p-5">
    
                        <!-- HEADER -->
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
    
                            <div>
    
                                <h2 class="text-2xl font-bold">
                                    Daftar Termin Kontrak
                                </h2>
    
                                <p class="text-slate-500 text-sm">
                                    Kelola siswa dan pembayaran setiap termin kontrak.
                                </p>
    
                            </div>
                        </div>
    
                        <div id="contract-list-skeleton" class="space-y-5 my-5">
    
                            @for ($i = 0; $i < 4; $i++)
    
                                <div class="rounded-3xl border border-gray-300 p-5 lg:p-6 animate-pulse">
    
                                    <!-- HEADER -->
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    
                                        <div class="flex items-start gap-3">
    
                                            <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
    
                                            <div>
    
                                                <div class="flex items-center gap-2">
    
                                                    <div class="h-6 w-24 bg-slate-200 rounded-lg"></div>
    
                                                    <div class="h-5 w-20 bg-slate-200 rounded-full"></div>
    
                                                </div>
    
                                                <div class="h-4 w-48 bg-slate-200 rounded mt-2"></div>
    
                                            </div>
    
                                        </div>
    
                                        <div class="h-10 w-full lg:w-32 bg-slate-200 rounded-xl"></div>
    
                                    </div>
    
                                    <!-- KPI CARDS -->
                                    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
    
                                        @for ($j = 0; $j < 5; $j++)
    
                                            <div class="rounded-2xl border border-gray-300 p-4">
    
                                                <div class="flex justify-between">
    
                                                    <div class="flex-1">
    
                                                        <div class="h-3 w-24 bg-slate-200 rounded"></div>
    
                                                        <div class="h-7 w-28 bg-slate-200 rounded mt-3"></div>
    
                                                    </div>
    
                                                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endfor
                        </div>
    
                        <div id="grid-term-list" class="divide-y divide-slate-100 my-5 max-h-120 overflow-y-auto"></div>
    
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

<script src="{{ asset('assets/js/features/lms/finance/contract/contract-payment-detail/paginate-payment-detail.js') }}"></script> <!--- paginate payment detail ---->
