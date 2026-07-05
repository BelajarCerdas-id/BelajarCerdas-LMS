@include('components/sidebar-beranda', [
    'linkBackButton' => route('schoolFaseManagement.view', [$role, $schoolName, $schoolId, $curriculumName, $curriculumId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Kelas',
]);
@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-import-syllabus"></div>

            <!-- DETAIL SEKOLAH -->
            <div id="school-detail-card"
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
            </div>
            
            <div class="flex justify-end">
                <!--- button bulkupload school partner --->
                <button type="button" onclick="my_modal_1.showModal()"
                    class="w-max bg-[#4189E0] text-white font-bold h-10 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2
                        items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-circle-plus"></i>
                    Bulk Upload
                </button>
            </div>

            <!---- Table list data kelas  ---->
            <div id="container-kelas-management" class="overflow-x-auto mt-8 pb-24" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                    data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" data-fase-id="{{ $faseId }}">
                <table id="table-kelas-management" class="min-w-full text-sm border-collapse">
                    <thead class="thead-table-kelas-management hidden bg-gray-50 shadow-inner">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                Kelas
                            </th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                Detail
                            </th>
                        </tr>
                    </thead>
                        <tbody id="tbody-kelas-management">
                            <!-- show data in ajax -->
                        </tbody>
                </table>
            </div>
            <div class="pagination-container-kelas-management flex justify-center my-4 sm:my-0"></div>

            <div id="empty-message-kelas-management" class="w-full h-96 hidden">
                <span class="w-full h-full flex items-center justify-center">
                    Tidak ada kelas.
                </span>
            </div>

            <!---- modal BulkUpload  ---->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max">

                    <div class="flex justify-center font-bold opacity-70">
                        <span class="">Upload Silabus</span>
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
        </div>
    </div>
@else
    <p>You do not have access to this pages.</p>
@endif

<script src="{{ asset('assets/js/syllabus-services/school/kelas-management.js') }}"></script> <!--- paginate kelas ---->
<script src="{{ asset('assets/js/syllabus-services/school/bulkUpload/form-action-syllabus.js') }}"></script> <!--- form action syllabus ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel ---->