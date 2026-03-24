@include('components/sidebar-beranda', [
    'linkBackButton' => route('schoolKelasManagement.view', [$schoolName, $schoolId, $curriculumName, $curriculumId, $faseId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Mata Pelajaran',
]);
@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-insert-data-mapel"></div>
            <div id="alert-success-edit-data-mapel"></div>

            <!-- DETAIL SEKOLAH -->
            <div id="school-detail-card"
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
            </div>

            <section class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                <!---- Form input mapel  ---->
                    <form id="create-mapel-form" autocomplete="OFF">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                            <div class="w-full">
                                <label class="text-sm">
                                    Mata Pelajaran
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <div class="flex relative max-w-lg mt-2">
                                    <div class="flex gap-2 w-full">
                                        <div class="w-full">
                                            <input type="text" name="mata_pelajaran"
                                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2" placeholder="Masukkan mata pelajaran">
                                            <span id="error-mata_pelajaran" class="text-red-500 text-xs mt-1 font-bold"></span>
                                        </div>
                                        <button id="submit-button-create-mapel" type="button"
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

                    <!---- Table list data mapel  ---->
                    <div id="container-mapel-management" class="overflow-x-auto mt-8 pb-24" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                            data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" data-fase-id="{{ $faseId }}" data-kelas-id="{{ $kelasId }}">
                        <table id="table-mapel-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-mapel-management hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Mata Pelajaran
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Detail
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </th>
                                </tr>
                            </thead>
        
                            <tbody id="tbody-mapel-management">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
        
                    <div class="pagination-container-mapel-management flex justify-center my-4 sm:my-0"></div>
        
                    <div id="empty-message-mapel-management" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada mata pelajaran.
                        </span>
                    </div>
            </section>

            <!---- modal edit mapel ---->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max">
                    <form id="edit-mapel-form" autocomplete="OFF">
                        <span class="text-xl font-bold flex justify-center">Edit Mata Pelajaran</span>

                        <input type="hidden" id="edit-school-partner-id" name="school_partner_id">
                        <input type="hidden" id="edit-curriculum-id">
                        <input type="hidden" id="edit-fase-id">
                        <input type="hidden" id="edit-kelas-id">
                        <input type="hidden" id="edit-mapel-id">

                        <div class="mt-4 w-80">
                            <label class="text-sm">
                                Mata Pelajaran
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <input type="text" id="edit-mapel" name="mata_pelajaran"
                            class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2" placeholder="Masukkan mata pelajaran">
                            <span id="error-mata_pelajaran" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button id="submit-button-edit-mapel" type="button"
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

            <!---- modal history mapel ---->
            <dialog id="my_modal_2" class="modal">
                <div class="modal-box bg-white max-w-md">
                    <h3 class="font-bold text-lg mb-6 text-center">Histori Mata Pelajaran</h3>

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

        </div>
    </div>
@else
    <p>You do not have access to this pages.</p>
@endif

<script src="{{ asset('assets/js/syllabus-services/school/mapel-management.js') }}"></script> <!--- paginate mapel ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/school/list-mapel-listener.js') }}"></script> <!--- pusher listener list mapel ---->