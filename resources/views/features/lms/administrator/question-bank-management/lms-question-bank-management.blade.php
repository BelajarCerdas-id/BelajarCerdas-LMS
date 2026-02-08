@include('components/sidebar-beranda', array_merge(
    [
        'headerSideNav' => 'LMS Bank Soal',
    ],
    $schoolId
        ? [
            'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
            'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
        ]
        : []
))

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!---- alerts success ---->
            <div id="alert-success-insert-bank-soal"></div>

            <!-- DETAIL SEKOLAH -->
            <div id="school-detail-card"
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
            </div>

            <main class="bg-white shadow-lg h-max rounded-lg border border-gray-200">
                <section id="container" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="border-b border-gray-200">
                    <form id="bank-soal-form">

                        <input type="hidden" name="school_partner_id" value="{{ $schoolId }}">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
                            <!--- Kurikulum --->
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

                <!---- list bank soal ---->
                <section class="relative p-6">

                    <h3 class="font-bold opacity-70 text-xl pb-6">Question List</h3>

                    <div id="container-bank-soal-list" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="overflow-x-auto pb-14">
                        <table id="table-bank-soal-list" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-bank-soal-list hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kurkulum</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Bab</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Sub Bab</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Soal</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status Bank Soal</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Sumber Soal</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-bank-soal-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-bank-soal-list flex justify-center my-10"></div>

                    <div id="empty-message-bank-soal-list" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada bank soal yang terdaftar pada sekolah ini.
                        </span>
                    </div>
                </section>

                <!---- modal history question  ---->
                <dialog id="my_modal_2" class="modal">
                    <div class="modal-box bg-white">
                        <h3 class="font-bold text-lg mb-5 text-center">History Question</h3>

                        <div class="flex items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-4">
                                <i class="fa-solid fa-circle-user text-5xl text-gray-400"></i>
    
                                <div class="flex flex-col gap-1">
                                    <span id="text-nama_lengkap" class="font-semibold"></span>
                                    <span id="text-status" class="text-sm text-gray-500"></span>
                                    <span id="text-created_at" class="text-xs text-gray-400"></span>
                                    <span id="text-updated_at" class="text-xs text-gray-400"></span>
                                </div>
                            </div>

                            <div class="">
                                <span class="text-[#0071BC] font-bold text-sm">Publisher</span>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="flex flex-col gap-3">

                            <!-- SOURCE -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold opacity-70">Source</span>
                                <span id="text-publisher"
                                    class="text-xs font-semibold px-3 py-1 rounded-full">
                                </span>
                            </div>
                            
                            <!-- STATUS -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold opacity-70">Status Global</span>
                                <span id="badge-global" class="text-xs font-semibold px-3 py-1 rounded-full"></span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold opacity-70">Status Sekolah</span>
                                <span id="badge-school" class="text-xs font-semibold px-3 py-1 rounded-full"></span>
                            </div>

                        </div>

                        <!-- INFO -->
                        <div id="text-info"
                            class="mt-5 text-sm px-4 py-3 rounded-lg">
                        </div>
                    </div>

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

<script src="{{ asset('assets/js/Features/lms/administrator/question-bank-management/paginate-question-bank-management.js') }}"></script> <!--- paginate lms question bank ---->
<script src="{{ asset('assets/js/Features/lms/bank-soal/form-action-bank-soal.js') }}"></script> <!--- form action upload bank soal ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/preview/word-upload-preview.js') }}"></script> <!--- show word ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/question-bank/bulk-upload-question-pg-listener.js') }}"></script> <!--- pusher listener pada saat create bank soal ---->
<script src="{{ asset('assets/js/pusher-listener/lms/question-bank/activate-question-pg-listener.js') }}"></script> <!--- pusher listener pada saat activate bank soal ---->