@include('components/sidebar-beranda', ['headerSideNav' => 'LMS Subscription'])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!--- show alerts --->
            <div id="alert-success-insert-school-partner"></div>

            <main>
                <div class="flex justify-between gap-8">
                    <div class="">
                        <!--- search bar --->
                        <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-40 sm:w-66 md:w-max">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                            </svg>
                            <input id="search_school" type="search" class="grow text-sm"
                                placeholder="Cari sekolah..." autocomplete="OFF" />
                        </label>
                    </div>
                    
                    <!--- button bulkupload school partner --->
                    <button type="button" onclick="my_modal_1.showModal()"
                        class="w-max bg-green-500 hover:bg-green-600 text-white font-bold h-10 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2 items-center justify-center 
                            cursor-pointer">
                        <i class="fa-solid fa-circle-plus"></i>
                        Bulk Upload
                    </button>
                </div>

                <!---- table list school partner lms subscription ---->
                <section class="relative pb-6 mt-6">
                    <div class="overflow-x-auto">
                        <table id="table-school-partner-list" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-school-partner-list hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Sekolah</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">NPSN</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Kepala Sekolah</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">NIK Kepala Sekolah</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Masa Aktif LMS</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Manajemen Akademik</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-school-partner-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-school-partner-list flex justify-center my-10"></div>

                    <div id="empty-message-school-partner-list" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada sekolah yang terdaftar.
                        </span>
                    </div>
                </section>

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
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/administrator/paginate-lms-school-subscription.js') }}"></script> <!--- paginate school partner ---->
<script src="{{ asset('assets/js/school-partner/form-action-school-partner.js') }}"></script> <!--- form action school partner ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel preview ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/management-school-subscription.js') }}"></script> <!--- pusher listener pada saat crud school subscription ---->