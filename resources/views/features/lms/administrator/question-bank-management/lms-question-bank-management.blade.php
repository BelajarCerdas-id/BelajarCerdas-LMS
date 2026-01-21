@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Bank Soal',
    'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main class="bg-white shadow-lg h-max rounded-lg border border-gray-200">
                <section class="border-b border-gray-200">
                    <form id="bank-soal-form" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 py-10 px-6">
                            <!--- Kurikulum  (order untuk mengurutkan posisi input mana yang duluan)--->
                            <div class="flex flex-col order-1 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Kurikulum
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="kurikulum_id" id="id_kurikulum"
                                    class="w-full bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 focus:border cursor-pointer">
                                    <option value="" class="hidden">Pilih Kurikulum</option>
                                    @foreach ($getCurriculum as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_kurikulum }}</option>
                                    @endforeach
                                </select>
                                <span id="error-kurikulum_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- Mapel --->
                            <div class="flex flex-col order-3 lg:order-2 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Mata Pelajaran
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="mapel_id" id="id_mapel"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Mata Pelajaran</option>
                                </select>
                                <span id="error-mapel_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- Sub Bab --->
                            <div class="flex flex-col order-5 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Sub Bab
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="sub_bab_id" id="id_sub_bab"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Sub Bab</option>
                                </select>
                                <span id="error-sub_bab_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- Kelas --->
                            <div class="flex flex-col order-2 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Kelas
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="kelas_id" id="id_kelas"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Kelas</option>
                                </select>
                                <span id="error-kelas_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- Bab --->
                            <div class="flex flex-col order-4 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Bab
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="bab_id" id="id_bab"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Bab</option>
                                </select>
                                <span id="error-bab_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- button bulkupload lms --->
                            <div class="flex flex-col order-6 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Soal
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <button type="button"
                                    class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold h-10 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2 items-center justify-center cursor-pointer"
                                    onclick="my_modal_1.showModal()">
                                    <i class="fa-solid fa-circle-plus"></i>
                                    Bulk Upload Soal
                                </button>
                            </div>
                        </div>

                        <!--- modal bulkupload lms --->
                        <dialog id="my_modal_1" class="modal">
                            <div class="modal-box bg-white w-max max-h-150">
                                <span class="text-md flex justify-center font-bold opacity-70">
                                    Upload Soal
                                    <sup class="text-red-500 pt-3 pl-1">&#42;</sup>
                                </span>

                                <!--- show bulkUpload word errors --->
                                <div id="error-bulkUpload" class="my-4 max-h-42 overflow-y-auto"></div>

                                <div class="w-full mt-8">
                                    <div class="w-full h-auto">
                                        <div class="text-xs mt-1">
                                            <span>Maksimum ukuran file 100MB. <br> File dapat dalam format .docx.</span>
                                        </div>
                                        <div class="upload-icon">
                                            <div class="flex flex-col max-w-65">
                                                <div id="wordPreview" class="max-w-70 cursor-pointer mt-4">
                                                    <div id="wordPreviewContainer-bulkUpload-word"
                                                        class="bg-white shadow-lg rounded-lg w-max py-2 pr-4 border border-gray-200 hidden">
                                                        <div class="flex items-center">
                                                            <img id="logo-bulkUpload-word" class="w-14 h-max">
                                                            <div class="mt-2 leading-5">
                                                                <span id="textPreview-bulkUpload-word"
                                                                    class="font-bold text-sm"></span><br>
                                                                <span id="textSize-bulkUpload-word"
                                                                    class="text-xs"></span>
                                                                <span id="textCircle-bulkUpload-word"
                                                                    class="relative -top-0.5 text-[5px]"></span>
                                                                <span id="textPages-bulkUpload-word"
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
                                        <label for="file-bulkUpload-word"
                                            class="w-full h-full flex justify-center items-center cursor-pointer gap-2">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                            <span>Upload File</span>
                                        </label>
                                        <input id="file-bulkUpload-word" name="bulkUpload-lms"
                                            class="hidden" onchange="previewWord(event, 'bulkUpload-word')"
                                            type="file" accept=".docx">
                                        <span id="error-bulkUpload-lms"
                                            class="text-red-500 font-bold text-xs pt-2"></span>
                                    </div>
                                </div>

                                <!-- Tombol Kirim -->
                                <div class="flex justify-end mt-8 z-[-1]">
                                    <button type="button" id="btn-submit-bank-soal"
                                        class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all outline-none cursor-pointer disabled:cursor-default">
                                        Kirim
                                    </button>
                                </div>
                            </div>
                    </form>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                    </dialog>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/bank-soal/form-action-bank-soal.js') }}"></script> <!--- form action upload bank soal ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/preview/word-upload-preview.js') }}"></script> <!--- show word ---->