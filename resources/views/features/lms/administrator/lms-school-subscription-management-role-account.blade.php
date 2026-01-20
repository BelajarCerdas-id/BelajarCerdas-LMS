@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Management Role',
    'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!--- show alerts --->
            <div id="alert-success-insert-add-users"></div>

            <main>

                <!--- button bulk upload add users --->
                <div class="w-full flex justify-end mb-6">
                    <button type="button" onclick="my_modal_1.showModal()"
                        class="w-max bg-[#0071BC] text-white font-bold h-10 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2 items-center justify-center 
                            cursor-pointer">
                        <i class="fa-solid fa-circle-plus"></i>
                        Tambah Pengguna
                    </button>
                </div>

                <!---- table list school partner lms subscription ---->
                <section class="relative">
                    <div id="container-role-account-list" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                        <!-- DETAIL SEKOLAH -->
                        <div id="school-detail-card"
                            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
                        </div>

                        <div id="grid-role-account-list" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-role-account-list" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada role yang terdafatar pada sekolah ini.
                        </span>
                    </div>
                </section>

                <!--- form action bulk upload add users --->
                <form id="school-partner-add-users-form" enctype="multipart/form-data">
                    <!--- modal bulkupload add users --->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white w-max max-h-150">
                            <span class="text-md flex justify-center font-bold opacity-70">Upload Accounts</span>

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
                                    <input id="file-bulkUpload-excel" name="bulkUpload-add-users" class="hidden" onchange="previewExcel(event, 'bulkUpload-excel')" type="file" accept=".xlsx">
                                    <span id="error-bulkUpload-add-users" class="text-red-500 font-bold text-xs pt-2"></span>
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

<script src="{{ asset('assets/js/Features/lms/administrator/lms-subscription-management-role-account.js') }}"></script> <!--- lms subscription management role account ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel preview ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/bulk-upload-create-account.js') }}"></script> <!--- pusher listener pada saat bulkupload create account ---->