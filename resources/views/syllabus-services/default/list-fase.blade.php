@include('components/sidebar-beranda', [
    'linkBackButton' => route('kurikulum.view'),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Fase',
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!---- alert success from ajax ---->
            <div id="alert-success-insert-data-fase"></div>
            <div id="alert-success-edit-data-fase"></div>

            <main>
                <section class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                    <!---- Form input fase  ---->
                    <form id="create-fase-form" autocomplete="OFF">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                            <div class="w-full">
                                <label class="text-sm">
                                    Nama Fase
                                    <sup class="text-red-500 pl-1">&#42;</sup>
                                </label>
                                <div class="flex relative max-w-lg mt-2">
                                    <div class="flex gap-2 w-full">
                                        <div class="w-full">
                                            <input type="text" name="nama_fase"
                                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2" placeholder="Masukkan Nama fase">
                                            <span id="error-nama_fase" class="text-red-500 text-xs mt-1 font-bold"></span>
                                        </div>
                                        <button id="submit-button-create-fase" type="button"
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

                    <!---- Table list data fase  ---->
                    <div id="container-management-fase" data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" class="overflow-x-auto mt-8 pb-24">
                        <table id="tableSyllabusFase" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-syllabus-fase hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                        Fase
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Detail
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableListSyllabusFase">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container-syllabus-fase flex justify-center my-4 sm:my-0"></div>

                    <div id="emptyMessageSyllabusFase" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada fase.
                        </span>
                    </div>

                    <!---- modal edit fase ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white w-max">
                            <form id="edit-fase-form" autocomplete="OFF">
                                <span class="text-xl font-bold flex justify-center">Edit Fase</span>

                                <input type="hidden" id="edit-curriculum-id">
                                <input type="hidden" id="edit-fase-id">

                                <div class="mt-4 w-80">
                                    <label class="text-sm">Nama Fase</label>
                                    <input type="text" id="edit-fase-name" name="nama_fase"
                                        class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2" placeholder="Masukkan Nama fase">
                                    <span id="error-nama_fase" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-edit-fase" type="button"
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

<script src="{{ asset('assets/js/syllabus-services/default/management-fase.js') }}"></script> <!--- paginate fase ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->


<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/list-fase-listener.js') }}"></script> <!--- pusher listener list fase ---->
