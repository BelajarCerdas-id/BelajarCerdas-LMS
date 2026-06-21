@include('components.sidebar-beranda', [
    'headerSideNav' => 'Student List',
    'linkBackButton' => route('lms.finance.contract.payment.detail', [$role, $schoolId, $contractId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role == 'Finance')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-10 mx-6 space-y-6">

            <!-- ALERT -->
            <div id="alert-success-insert-contract-students"></div>

            <main id="container" data-role="{{ $role }}" data-school-id="{{ $schoolId }}" data-contract-id="{{ $contractId }}" data-term-id="{{ $termId }}" 
                class="space-y-6">

                <!-- HEADER -->
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
                                        Daftar Siswa
                                    </p>

                                    <h1 class="text-xl md:text-3xl font-bold break-all">
                                        {{ $schContractTerm->SchContract->contract_number ?? '-' }}
                                    </h1>

                                </div>

                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">

                                <span class="px-3 py-1.5 rounded-xl bg-white/20 text-xs md:text-sm">
                                    {{ $schContractTerm->SchContract->SchoolPartner->nama_sekolah ?? '-' }}
                                </span>

                                <span class="px-3 py-1.5 rounded-xl bg-white/20 text-xs md:text-sm">
                                    Termin {{ $schContractTerm->term_number ?? '-' }}
                                </span>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILTER -->
                <div class="flex flex-row items-center justify-between gap-6">

                    <div>
                        <!-- contract info -->
                    </div>

                    <div class="flex gap-3">

                        <button onclick="my_modal_1.showModal()"
                            class="btn bg-emerald-500 hover:bg-emerald-600 text-white border-0 rounded-2xl">

                            <i class="fa-solid fa-upload"></i>
                            Unggah Data Siswa

                        </button>
                    </div>
                </div>

                <!-- KPI SECTION -->
                <section class="space-y-6">
                    <div
                        id="kpi-skeleton"
                        class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                        @for($i = 0; $i < 4; $i++)

                            <div class="bg-white border border-slate-200 rounded-3xl p-5">

                                <div class="animate-pulse">

                                    <div class="flex justify-between">

                                        <div class="w-full">

                                            <div class="h-3 w-28 bg-slate-200 rounded"></div>

                                            <div class="h-10 w-20 bg-slate-200 rounded mt-4"></div>

                                        </div>

                                        <div
                                            class="w-14 h-14 rounded-2xl bg-slate-200">
                                        </div>

                                    </div>

                                    <div class="mt-5">

                                        <div
                                            class="h-2 bg-slate-200 rounded-full">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    
                            <!-- TOTAL -->
                            <div class="bg-white border border-blue-200 rounded-3xl p-5">
    
                                <div class="flex justify-between">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Total Siswa
                                        </p>
    
                                        <h3
                                            id="total-students"
                                            class="text-4xl font-black mt-2">
                                            -
                                        </h3>
    
                                    </div>
    
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-users text-blue-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- ACTIVE -->
                            <div class="bg-white border border-green-200 rounded-3xl p-5">
    
                                <div class="flex justify-between">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Siswa Aktif
                                        </p>
    
                                        <h3
                                            id="active-students"
                                            class="text-4xl font-black text-green-600 mt-2">
                                            -
                                        </h3>
    
                                    </div>
    
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-user-check text-green-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- INACTIVE -->
                            <div class="bg-white border border-red-200 rounded-3xl p-5">
    
                                <div class="flex justify-between">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Siswa Tidak Aktif
                                        </p>
    
                                        <h3
                                            id="inactive-students"
                                            class="text-4xl font-black text-red-600 mt-2">
                                            -
                                        </h3>
    
                                    </div>
    
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-user-xmark text-red-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- RATE -->
                            <div class="bg-white border border-violet-200 rounded-3xl p-5">
    
                                <div class="flex justify-between">
    
                                    <div>
    
                                        <p class="text-sm text-slate-500">
                                            Persentase aktif
                                        </p>
    
                                        <h3
                                            id="activation-rate"
                                            class="text-4xl font-black text-violet-600 mt-2">
                                            -
                                        </h3>
    
                                    </div>
    
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-violet-100 flex items-center justify-center">
    
                                        <i class="fa-solid fa-chart-line text-violet-600 text-xl"></i>
    
                                    </div>
    
                                </div>
    
                                <div class="mt-5">
    
                                    <div class="w-full bg-slate-100 rounded-full h-2">
    
                                        <div
                                            id="activation-progress"
                                            class="bg-violet-600 h-2 rounded-full"
                                            style="width:0%">
                                        </div>
    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- STUDENT LIST -->
                <section class="space-y-6">

                    <!-- TABLE CARD -->
                    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

                        <!-- HEADER -->
                        <div class="p-5 border-b border-slate-200">

                            <div class="flex flex-col xl:flex-row gap-4 justify-between">

                                <div>

                                    <div class="flex items-center gap-3">

                                        <h3 class="font-bold text-lg">
                                            Daftar Siswa
                                        </h3>

                                    </div>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Daftar siswa yang terdaftar pada termin kontrak ini.
                                    </p>

                                </div>

                                <div class="flex flex-col md:flex-row gap-3">

                                <!--- search bar --->
                                <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-40 sm:w-66">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                    </svg>
                                    <input id="search_student" type="search" class="grow text-sm"
                                        placeholder="Cari Siswa..." autocomplete="OFF" />
                                </label>

                                    <select id="filter_status"
                                        class="select select-bordered rounded-2xl cursor-pointer outline-none">

                                        <option value="" selected>
                                            Semua Status
                                        </option>

                                        <option value="active">
                                            Aktif
                                        </option>

                                        <option value="inactive">
                                            Tidak Aktif
                                        </option>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- TABLE SKELETON -->
                        <div id="student-table-skeleton" class="hidden overflow-x-auto">

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Siswa</th>
                                        <th>Status Kontrak</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @for ($i = 0; $i < 8; $i++)
                                        <tr>
                                            <td>
                                                <div class="skeleton h-4 w-6"></div>
                                            </td>

                                            <td>

                                                <div class="flex items-center gap-3">

                                                    <div class="space-y-2">

                                                        <div
                                                            class="skeleton h-4 w-40">
                                                        </div>

                                                        <div
                                                            class="skeleton h-3 w-56">
                                                        </div>

                                                    </div>

                                                </div>
                                            </td>

                                            <td>
                                                <div class="skeleton h-7 w-24 rounded-xl"></div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- TABLE CONTENT -->
                        <div id="student-table-content" class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="w-16">
                                            #
                                        </th>

                                        <th>
                                            Student
                                        </th>

                                        <th>
                                            Status Kontrak
                                        </th>
                                    </tr>
                                </thead>

                                <!-- DATA -->
                                <tbody id="student-table-body"></tbody>

                                <!-- EMPTY STATE -->
                                <tbody id="student-empty-state" class="hidden">
                                    <tr>
                                        <td colspan="5" class="py-16">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center mb-4">

                                                    <i
                                                        class="fa-solid fa-user-graduate text-3xl text-slate-400">
                                                    </i>

                                                </div>

                                                <h3
                                                    class="font-bold text-lg text-slate-700">

                                                    Siswa tidak ditemukan

                                                </h3>

                                                <p
                                                    class="text-sm text-slate-500 mt-2 max-w-md">

                                                    Tidak ada siswa yang terdaftar pada termin kontrak ini.

                                                </p>

                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- PAGINATION -->
                            <div class="pagination-container-student-list p-5 border-t border-slate-200"></div>
                        </div>
                    </div>
                </section>
            </main>

            <!--- form action bulk upload --->
            <form id="contract-students-form" enctype="multipart/form-data">

                <!--- modal bulkupload --->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white w-max max-h-150">
                        <span class="text-md flex justify-center font-bold opacity-70">Upload Students</span>

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
                                                <div id="excelPreviewContainer-bulkUpload-excel" class="bg-white shadow-lg rounded-lg w-max py-2 pr-4 border 
                                                    border-gray-200 hidden">
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
                                <input id="file-bulkUpload-excel" name="bulkUpload-contract-students" class="hidden" 
                                    onchange="previewExcel(event, 'bulkUpload-excel')" type="file" accept=".xlsx">
                                <span id="error-bulkUpload-contract-students" class="text-red-500 font-bold text-xs pt-2"></span>
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

<script src="{{ asset('assets/js/features/lms/finance/contract/contract-term/paginate-student-list.js') }}"></script> <!--- paginate student list ---->
<script src="{{ asset('assets/js/features/lms/finance/contract/contract-term/upload-contract-students.js') }}"></script> <!--- upload contract students ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel preview ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->