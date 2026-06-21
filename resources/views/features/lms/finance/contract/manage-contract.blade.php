@include('components/sidebar-beranda', ['headerSideNav' => 'Contract'])

@if (Auth::user()->role == 'Finance')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-10 mx-6 space-y-6">

            <!--- show alerts --->
            <div id="alert-success-insert-school-partner"></div>

            <main id="container" data-role="{{ $role }}">
                <section class="mt-8">

                    <!-- HEADER -->
                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] rounded-3xl p-8 text-white shadow-xl overflow-hidden relative">
        
                        <div class="absolute right-0 top-0 opacity-10">
                            <i class="fa-solid fa-file-signature text-[220px] translate-x-8 -translate-y-4"></i>
                        </div>
        
                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
        
                            <div>
                                <h1 class="text-3xl font-bold">
                                    Manajemen Kontrak Sekolah
                                </h1>
        
                                <p class="mt-2 text-slate-300 max-w-3xl">
                                    Kelola kontrak kerja sama, status pembayaran, dan nilai tagihan seluruh sekolah mitra.
                                </p>
                            </div>
        
                            <button onclick="my_modal_1.showModal()"
                                class="px-5 py-3 rounded-2xl bg-white text-slate-900 font-semibold hover:scale-105 transition cursor-pointer">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Tambah Kontrak
                            </button>
        
                        </div>
        
                    </div>
                </section>

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

                        <!-- COLLECTION PROGRESS -->
                        <div class="mt-6 bg-white border border-slate-200 rounded-3xl p-7">
                            <div
                                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                                <!-- Left -->
                                <div>

                                    <div class="h-6 w-64 bg-slate-200 rounded"></div>

                                    <div class="h-4 w-80 bg-slate-100 rounded mt-3"></div>

                                </div>

                                <!-- Right -->
                                <div class="text-right">

                                    <div class="h-10 w-24 bg-slate-300 rounded ml-auto"></div>

                                    <div class="h-4 w-28 bg-slate-100 rounded mt-3 ml-auto"></div>

                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-6">

                                <div class="h-5 bg-slate-100 rounded-full overflow-hidden">

                                    <div class="h-full w-2/3 bg-slate-200 rounded-full"></div>

                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="grid md:grid-cols-2 gap-4 mt-6">

                                @for ($i = 0; $i < 2; $i++)
                                <div class="border border-slate-200 rounded-2xl p-4">

                                    <div class="flex items-center justify-between">

                                        <div>

                                            <div class="h-3 w-32 bg-slate-200 rounded"></div>

                                            <div class="h-7 w-40 bg-slate-300 rounded mt-3"></div>

                                        </div>

                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-200">
                                        </div>

                                    </div>
                                </div>
                                @endfor

                            </div>
                        </div>
                    </div>

                    <!-- KPI -->
                    <div id="kpi-content">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
    
                            <!-- CONTRACT VALUE -->
                            <div class="bg-linear-to-br from-violet-50 to-white border border-violet-200 rounded-3xl p-6">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Nilai Kontrak
                                        </p>
    
                                        <h3 id="total-contract-value"
                                            class="text-3xl font-black mt-2 text-violet-700">
                                            Rp 0
                                        </h3>
    
                                        <p class="text-xs text-slate-500 mt-2">
                                            Total nilai kontrak seluruh sekolah
                                        </p>
    
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-violet-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-file-contract text-violet-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- Pembayaran Diterima -->
                            <div class="bg-linear-to-br from-green-50 to-white border border-green-200 rounded-3xl p-6">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Pembayaran Diterima
                                        </p>
    
                                        <h3 id="revenue-collected"
                                            class="text-3xl font-black mt-2 text-green-600">
                                            Rp 0
                                        </h3>
    
                                        <p class="text-xs text-green-600 mt-2">
                                            Total pembayaran yang telah diterima
                                        </p>
    
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-circle-check text-green-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- Tagihan Belum Dibayar -->
                            <div class="bg-linear-to-br from-red-50 to-white border border-red-200 rounded-3xl p-6">
    
                                <div class="flex justify-between items-start">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Tagihan Belum Dibayar
                                        </p>
    
                                        <h3 id="outstanding"
                                            class="text-3xl font-black mt-2 text-red-600">
                                            Rp 0
                                        </h3>
    
                                        <p class="text-xs text-red-500 mt-2">
                                            Tagihan dengan status belum dibayar atau terlambat
                                        </p>
    
                                    </div>
    
                                    <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-clock text-red-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
                        </div>
                    </div>

                    <!-- COLLECTION PROGRESS -->
                    <div class="mt-6 bg-white border border-slate-200 rounded-3xl p-7">

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                            <div>

                                <h3 class="font-bold text-xl">
                                    Progress Pembayaran
                                </h3>

                                <p class="text-sm text-slate-500 mt-1">
                                    Persentase pembayaran dari seluruh tagihan yang telah terbentuk
                                </p>

                            </div>

                            <div class="text-right">

                                <h2 id="collection-rate"
                                    class="text-xl font-black text-green-600">
                                    0%
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Persentase Pembayaran
                                </p>

                            </div>

                        </div>

                        <!-- BAR -->
                        <div class="mt-6">

                            <div class="h-5 bg-slate-100 rounded-full overflow-hidden">

                                <div id="collection-progress-bar"
                                    class="h-full rounded-full bg-linear-to-r from-green-500 via-emerald-500 to-green-600 transition-all duration-1000"
                                    style="width:0%">
                                </div>

                            </div>

                        </div>

                        <!-- SUMMARY -->
                        <div class="grid md:grid-cols-2 gap-4 mt-6">

                            <div class="bg-green-50 rounded-2xl p-4 border border-green-200">

                                <div class="flex items-center justify-between">

                                    <div>

                                        <p class="text-xs text-green-600 uppercase">
                                            Pembayaran Diterima
                                        </p>

                                        <h4 id="collected-summary"
                                            class="font-black text-lg mt-1 text-green-700">
                                            Rp 0
                                        </h4>

                                    </div>

                                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">

                                        <i class="fa-solid fa-circle-check text-green-600"></i>

                                    </div>

                                </div>

                            </div>

                            <div class="bg-red-50 rounded-2xl p-4 border border-red-200">

                                <div class="flex items-center justify-between">

                                    <div>

                                        <p class="text-xs text-red-600 uppercase">
                                            Belum Dibayar
                                        </p>

                                        <h4 id="outstanding-summary"
                                            class="font-black text-lg mt-1 text-red-700">
                                            Rp 0
                                        </h4>

                                    </div>

                                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">

                                        <i class="fa-solid fa-clock text-red-600"></i>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8">

                    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

                        <!-- HEADER -->
                        <div class="p-6 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                            <div>
                                <h2 class="text-xl font-bold">
                                    Daftar Sekolah
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Ringkasan kontrak, tagihan, dan status kerja sama setiap sekolah
                                </p>
                            </div>

                            <div class="flex gap-3">

                                <!--- search bar --->
                                <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-40 sm:w-66 md:w-max">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                    </svg>
                                    <input id="search_school" type="search" class="grow text-sm"
                                        placeholder="Cari nam sekolah..." autocomplete="OFF" />
                                </label>
                            </div>
                        </div>

                        <!-- TABLE -->
                        <div class="overflow-x-auto">

                            <!-- SKELETON TABLE -->
                            <div id="school-contract-list-skeleton" class="hidden">

                                <table class="min-w-full">

                                    <tbody>

                                        @for ($i = 0; $i < 7; $i++)
                                        <tr class="animate-pulse border-t border-slate-100">

                                            <td class="p-4">
                                                <div class="space-y-2">
                                                    <div class="h-4 w-48 bg-slate-200 rounded"></div>
                                                    <div class="h-3 w-20 bg-slate-100 rounded"></div>
                                                </div>
                                            </td>

                                            <td class="p-4">
                                                <div class="h-4 w-12 bg-slate-200 rounded mx-auto"></div>
                                            </td>

                                            <td class="p-4">
                                                <div class="flex flex-col items-center gap-2">
                                                    <div class="h-3 w-24 bg-slate-200 rounded"></div>
                                                    <div class="h-3 w-24 bg-slate-100 rounded"></div>
                                                </div>
                                            </td>

                                            <td class="p-4">
                                                <div class="h-4 w-24 bg-slate-200 rounded mx-auto"></div>
                                            </td>

                                            <td class="p-4">
                                                <div class="h-6 w-20 bg-slate-200 rounded-full mx-auto"></div>
                                            </td>

                                            <td class="p-4">
                                                <div class="h-9 w-20 bg-slate-200 rounded-xl mx-auto"></div>
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>

                            <!-- REAL TABLE -->
                            <table id="table-school-contract-list" class="min-w-full text-sm border-collapse">

                                <thead class="thead-table-school-contract-list hidden bg-slate-50">

                                    <tr class="text-slate-600">

                                        <th class="p-4 text-left">Sekolah</th>

                                        <th class="p-4 text-center">
                                            Jumlah Kontrak
                                        </th>

                                        <th class="p-4 text-center">
                                            Nilai Kontrak
                                        </th>

                                        <th class="p-4 text-center">
                                            Belum Dibayar
                                        </th>

                                        <th class="p-4 text-center">
                                            Termin Terlambat
                                        </th>

                                        <th class="p-4 text-center">
                                            Status
                                        </th>

                                        <th class="p-4 text-center">
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="tbody-school-contract-list">
                                </tbody>

                            </table>

                        </div>

                        <div class="pagination-container-school-contract-list flex justify-center py-5"></div>

                        <!-- EMPTY -->
                        <div id="empty-message-school-contract-list"
                            class="hidden h-96 bg-slate-50 rounded-2xl border border-dashed border-slate-200">

                            <div class="flex flex-col items-center justify-center h-full px-6">

                                <div
                                    class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-4">

                                    <i class="fas fa-file-signature text-3xl text-blue-500"></i>

                                </div>

                                <h4 id="empty-title"
                                    class="text-lg font-bold text-slate-700 text-center">

                                    Belum Ada Data Kontrak

                                </h4>

                                <p id="empty-description"
                                    class="text-sm text-slate-500 text-center max-w-md mt-2">

                                    Belum terdapat kontrak yang terdaftar. Tambahkan kontrak baru untuk mulai memonitor pembayaran dan masa aktif sekolah.

                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!--- form action bulk upload school partner --->
            <form id="school-partner-form" enctype="multipart/form-data">
                <!--- modal bulkupload school partner --->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white w-max max-h-150">
                        <span class="text-md flex justify-center font-bold opacity-70">Upload School Partner</span>

                        <!--- show bulkUpload excel errors --->
                        <div id="error-bulkUpload" class="w-96.25 my-4 max-h-42 overflow-y-auto"></div>

                        <div class="w-full mt-8">
                            <div class="w-full h-auto">
                                <div class="text-xs mt-1">
                                    <span>Maksimum ukuran file 100MB. <br> File dapat dalam format .xlsx.</span>
                                </div>
                                <div class="upload-icon">
                                        <div class="flex flex-col max-w-65">
                                            <div id="excelPreview" class="max-w-70 cursor-pointer mt-4">
                                                <div id="excelPreviewContainer-bulkUpload-excel" class="bg-white shadow-lg rounded-lg w-max py-2 pr-4 border border-gray-200 hidden">
                                                <div class="flex items-center">
                                                        <img id="logo-bulkUpload-excel" class="w-14 h-max">
                                                    <div class="mt-2 leading-5">
                                                        <span id="textPreview-bulkUpload-excel" class="font-bold text-sm"></span><br>
                                                        <span id="textSize-bulkUpload-excel" class="text-xs"></span>
                                                        <span id="textCircle-bulkUpload-excel" class="relative -top-0.5 text-[5px]"></span>
                                                        <span id="textPages-bulkUpload-excel" class="text-xs"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content-upload w-96.25 h-9 bg-[#4189e0] hover:bg-blue-500 text-white font-bold rounded-lg mt-6 mb-2">
                                <label for="file-bulkUpload-excel"
                                    class="w-full h-full flex justify-center items-center cursor-pointer gap-2">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                    <span>Upload File</span>
                                </label>
                                <input id="file-bulkUpload-excel" name="bulkUpload-school-partner" class="hidden" onchange="previewExcel(event, 'bulkUpload-excel')" type="file" accept=".xlsx">
                                <span id="error-bulkUpload-school-partner" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>
                        </div>

                        <!-- Tombol Kirim -->
                        <div class="flex justify-end mt-8 z-[-1]">
                            <button id="submit-button" type="button"
                                class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all outline-none 
                                    cursor-pointer disabled:cursor-default">
                                Kirim
                            </button>
                        </div>
                    </div>
                </form>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/school-partner/form-action-school-partner.js') }}"></script> <!--- form action school partner ---->
<script src="{{ asset('assets/js/features/lms/finance/contract/manage-contract.js') }}"></script> <!--- paginate school contract list ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel preview ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->