@include('components/sidebar-beranda', [
    'linkBackButton' => route('schoolBabManagement.view', [$role, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Sub Bab',
]);
@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-insert-data-sub-bab"></div>
            <div id="alert-success-edit-data-sub-bab"></div>
            <div id="alert-success-import-syllabus-sub-bab"></div>

            <!-- DETAIL SEKOLAH -->
            <div id="school-detail-card"
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
            </div>

            <main class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                <section id="container-create-sub-bab" class="border-b-2 border-gray-200 pb-8 w-full hidden">
                    <div class="flex justify-end">
                        <!--- button bulkupload school partner --->
                        <button type="button" onclick="my_modal_3.showModal()"
                            class="w-max bg-[#4189E0] text-white font-bold h-10 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2
                                items-center justify-center cursor-pointer">
                            <i class="fa-solid fa-circle-plus"></i>
                            Bulk Upload
                        </button>
                    </div>

                    <!---- Form input sub bab  ---->
                        <form id="create-sub-bab-form" autocomplete="OFF">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">
    
                                <div class="w-full">
                                    <label class="text-sm">
                                        Sub Bab
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <div class="flex relative max-w-lg mt-2">
                                        <div class="flex gap-2 w-full">
                                            <div class="w-full">
                                                <input type="text" name="sub_bab"
                                                    class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2" placeholder="Masukkan sub bab">
                                                <span id="error-sub_bab" class="text-red-500 text-xs mt-1 font-bold"></span>
                                            </div>
                                            <button id="submit-button-create-sub-bab" type="button"
                                                class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-full shadow-md transition-all h-max text-md cursor-pointer
                                                disabled:cursor-default">
                                                Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                    
                    <section>
                        <!---- Table list data sub bab  ---->
                        <div id="container-sub-bab-management" class="overflow-x-auto mt-8 pb-24" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" 
                                data-school-id="{{ $schoolId }}" data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" 
                                data-fase-id="{{ $faseId }}" data-kelas-id="{{ $kelasId }}" data-mapel-id="{{ $mapelId }}" data-bab-id="{{ $babId }}">
                            <table id="table-sub-bab-management" class="min-w-full text-sm border-collapse">
                                <thead class="thead-table-sub-bab-management hidden bg-gray-50 shadow-inner">
                                    <tr>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[85%]">
                                            Sub Bab
                                        </th>
                                        <th class="thead-action border border-gray-300 px-3 py-2 opacity-70 text-xs hidden">
                                            Action
                                        </th>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </th>
                                    </tr>
                                </thead>
            
                                <tbody id="tbody-sub-bab-management">
                                    <!-- show data in ajax -->
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-container-sub-bab-management flex justify-center my-4 sm:my-0"></div>

                        <div id="empty-message-sub-bab-management" class="w-full h-96 hidden">
                            <span class="w-full h-full flex items-center justify-center">
                                Tidak ada sub bab.
                            </span>
                        </div>
                </section>
            </main>

            <!---- modal edit sub bab ---->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max">
                    <form id="edit-sub-bab-form" autocomplete="OFF">
                        <span class="text-xl font-bold flex justify-center">Edit Sub Bab</span>

                        <input type="hidden" id="edit-school-partner-id" name="school_partner_id">
                        <input type="hidden" id="edit-curriculum-id">
                        <input type="hidden" id="edit-fase-id">
                        <input type="hidden" id="edit-kelas-id">
                        <input type="hidden" id="edit-mapel-id">
                        <input type="hidden" id="edit-bab-id">
                        <input type="hidden" id="edit-sub-bab-id">

                        <div class="mt-4 w-80">
                            <label class="text-sm">
                                Sub Bab
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <input type="text" id="edit-sub_bab" name="sub_bab"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2" placeholder="Masukkan sub bab">
                            <span id="error-sub_bab" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button id="submit-button-edit-sub-bab" type="button"
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

            <!---- modal history sub bab ---->
            <dialog id="my_modal_2" class="modal">
                <div class="modal-box bg-white max-w-md">
                    <h3 class="font-bold text-lg mb-6 text-center">Histori Sub Bab</h3>

                    <!-- USER / PUBLISHER -->
                    <div class="flex items-center gap-4 mb-5">
                        <i class="fa-solid fa-circle-user text-5xl text-gray-400"></i>

                        <div class="flex flex-col gap-1 flex-1">
                            <span id="text-nama_lengkap" class="font-semibold text-gray-800"></span>
                            <span id="text-role" class="text-sm text-gray-500"></span>
                            <span id="text-updated_at" class="text-xs text-gray-400"></span>
                        </div>

                        <div class="">
                            <span class="text-[#0071BC] font-bold text-sm">Publisher</span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-30">

                    <!-- INFO -->
                    <div class="flex flex-col gap-3">
                        
                        <!-- SOURCE -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold opacity-70">Source</span>
                            <span id="text-publisher"
                                class="text-xs font-semibold px-3 py-1 rounded-full">
                            </span>
                        </div>
                    </div>
                </div>

                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>

            <!---- modal BulkUpload  ---->
            <dialog id="my_modal_3" class="modal">
                <div class="modal-box bg-white w-max">

                    <div class="flex justify-center font-bold opacity-70">
                        <span class="">Upload Sub Bab</span>
                        <sup class="text-red-500 pl-1 pt-4 text-md">&#42;</sup>
                    </div>

                    <form id="bulkUpload-syllabus-sub-bab-form" enctype="multipart/form-data">
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
                                <input id="bulkUpload-excel" name="bulkUpload-sub-bab" class="hidden" onchange="previewExcel(event, 'bulkUpload-excel')" type="file" accept=".xlsx">
                                <span id="error-bulkUpload-sub-bab" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>
                        </div>
                        <!-- Tombol Kirim -->
                        <div class="flex justify-end mt-8">
                            <button id="submit-button-bulkUpload-sub-bab" type="button"
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

        </div>
    </div>
@else
    <p>You do not have access to this pages.</p>
@endif

<script src="{{ asset('assets/js/syllabus-services/school/sub-bab-management.js') }}"></script> <!--- paginate sub bab ---->
<script src="{{ asset('assets/js/syllabus-services/school/bulkUpload/form-action-syllabus-sub-bab.js') }}"></script> <!--- form action syllabus sub bab ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/school/list-sub-bab-listener.js') }}"></script> <!--- pusher listener list sub bab ---->