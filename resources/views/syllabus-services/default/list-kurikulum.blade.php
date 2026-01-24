@include('components/sidebar-beranda', ['headerSideNav' => 'Kurikulum'])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!---- alert success from ajax ---->
            <div id="alert-success-insert-data-curriculum"></div>
            <div id="alert-success-edit-data-curriculum"></div>
            <div id="alert-success-import-syllabus"></div>

            <main>
                <section class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                    <!---- BulkUpload Button  ---->
                    <div class="flex justify-end mb-10 lg:mb-0">
                        <button
                            class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold h-8 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2 items-center justify-center cursor-pointer"
                            onclick="my_modal_2.showModal()">
                            <i class="fa-solid fa-circle-plus"></i>
                            Bulk Upload
                        </button>
                    </div>

                    <!---- Form input kurikulum  ---->
                    <form id="create-curriculum-form" autocomplete="OFF">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                            <div class="w-full">
                                <label class="text-sm">
                                    Nama Kurikulum
                                    <sup class="text-red-500 pl-1">&#42;</sup>
                                </label>
                                <div class="flex relative max-w-lg mt-2">
                                    <div class="flex gap-2 w-full">
                                        <div class="w-full">
                                            <input type="text" name="nama_kurikulum"
                                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2" placeholder="Masukkan Nama Kurikulum">
                                            <span id="error-nama_kurikulum" class="text-red-500 text-xs mt-1 font-bold"></span>
                                        </div>
                                        <button id="submit-button-create-curriculum" type="button"
                                            class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-full shadow-md transition-all h-max text-md cursor-pointer
                                            disabled:cursor-default">
                                            Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data kurikulum  ---->
                    <div class="overflow-x-auto mt-8 pb-24">
                        <table id="tableSyllabusCurriculum" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-syllabus-curriculum hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                        Kurikulum
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Detail
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableListSyllabusCurriculum">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container-syllabus-curriculum flex justify-center my-4 sm:my-0"></div>

                    <div id="emptyMessageSyllabusCurriculum" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada Kurikulum.
                        </span>
                    </div>

                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white w-max">
                            <form id="edit-curriculum-form" autocomplete="OFF">
                                <span class="text-xl font-bold flex justify-center">Edit Kurikulum</span>

                                <input type="hidden" id="edit-curriculum-id">

                                <div class="mt-4 w-80">
                                    <label class="text-sm">Nama Kurikulum</label>
                                    <input type="text" id="edit-curriculum-name" name="nama_kurikulum"
                                        class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2" placeholder="Masukkan Nama Kurikulum">
                                    <span id="error-nama_kurikulum" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-edit-curriculum" type="button"
                                        class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all cursor-pointer disabled:cursor-default">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <form method="dialog" class="modal-backdrop">
                            <button>Close</button>
                        </form>
                    </dialog>

                    <!---- modal BulkUpload  ---->
                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-white w-max">

                            <div class="flex justify-center font-bold opacity-70">
                                <span class="">Upload Syllabus</span>
                                <sup class="text-red-500 pl-1 pt-4 text-md">&#42;</sup>
                            </div>

                            <form id="bulkUpload-syllabus-form" enctype="multipart/form-data">
                                <div class="w-full mt-4">
                                    <div class="w-full h-auto">

                                        <!--- show bulkUpload word errors --->
                                        <div id="error-bulkUpload" class="w-96.25 my-4 max-h-42 overflow-y-auto"></div>

                                        <div class="text-xs mt-1">
                                            <span>Maksimum ukuran file 100MB. <br> File dapat dalam format .xlsx.</span>
                                        </div>
                                        <div class="upload-icon">
                                            <div class="flex flex-col max-w-65">
                                                <div id="excelPreview" class="max-w-70 cursor-pointer mt-4">
                                                    <div id="excelPreviewContainer-bulkUpload-excel"
                                                        class="bg-white shadow-lg rounded-lg w-max py-2 pr-4 border border-gray-200 hidden">
                                                        <div class="flex items-center">
                                                            <img id="logo-bulkUpload-excel" class="w-14 h-max">
                                                            <div class="mt-2 leading-5">
                                                                <span id="textPreview-bulkUpload-excel"
                                                                    class="font-bold text-sm"></span><br>
                                                                <span id="textSize-bulkUpload-excel"
                                                                    class="text-xs"></span>
                                                                <span id="textCircle-bulkUpload-excel"
                                                                    class="relative -top-0.5 text-[5px]"></span>
                                                                <span id="textPages-bulkUpload-excel"
                                                                    class="text-xs"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="content-upload w-96.25 h-9 bg-[#4189e0] hover:bg-blue-500 text-white font-bold rounded-lg mt-6 mb-2">
                                        <label for="bulkUpload-excel"
                                            class="w-full h-full flex justify-center items-center cursor-pointer gap-2">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                            <span>Upload File</span>
                                        </label>
                                        <input id="bulkUpload-excel" name="bulkUpload-syllabus" class="hidden" onchange="previewExcel(event, 'bulkUpload-excel')" type="file" accept=".xlsx">
                                        <span id="error-bulkUpload-syllabus" class="text-red-500 font-bold text-xs pt-2"></span>
                                    </div>
                                </div>
                                <!-- Tombol Kirim -->
                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-bulkUpload-syllabus" type="button"
                                        class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all outline-none cursor-pointer disabled:cursor-default">
                                        Kirim
                                    </button>
                                </div>
                            </form>
                        </div>
                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>
                </section>
            </main>
        </div>
    </div>
@else
    <p>You do not have access to this pages.</p>
@endif


<script src="{{ asset('assets/js/syllabus-services/management-curriculum.js') }}"></script> <!--- paginate kurikulum ---->
<script src="{{ asset('assets/js/syllabus-services/form-action-bulkUpload-syllabus.js') }}"></script> <!--- form action bulkUpload syllabus ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/list-kurikulum-listener.js') }}"></script> <!--- pusher listener list kurikulum ---->
