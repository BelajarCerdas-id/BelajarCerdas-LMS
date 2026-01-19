@include('components/sidebar-beranda', [
    'linkBackButton' => route('bab.view', [$curriculumName, $curriculumId, $faseId, $kelasId, $mapelId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Sub Bab',
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!---- alert success from ajax ---->
            <div id="alert-success-insert-data-sub-bab"></div>
            <div id="alert-success-edit-data-sub-bab"></div>

            <main>
                <section class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                    <!---- Form input sub bab  ---->
                    <form id="create-sub-bab-form" autocomplete="OFF">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                            <div class="w-full">
                                <label class="text-sm">
                                    Sub Bab 
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <div class="flex relative max-w-lg mt-2">
                                    <div class="flex gap-2 w-full">
                                        <div class="w-full">
                                            <input type="text" name="sub_bab"
                                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2" placeholder="Masukkan Sub Bab">
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

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data sub bab  ---->
                    <div id="container-management-sub-bab" data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" data-fase-id="{{ $faseId }}" 
                        data-kelas-id="{{ $kelasId }}" data-mapel-id="{{ $mapelId }}" data-bab-id="{{ $babId }}"
                        class="overflow-x-auto mt-8 pb-24">
                        <table id="tableSyllabusSubBab" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-syllabus-sub-bab hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                        Sub Bab
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableListSyllabusSubBab">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container-syllabus-sub-bab flex justify-center my-4 sm:my-0"></div>

                    <div id="emptyMessageSyllabusSubBab" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada sub bab.
                        </span>
                    </div>

                    <!---- modal edit sub bab ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white w-max">
                            <form id="edit-sub-bab-form" autocomplete="OFF">
                                <span class="text-xl font-bold flex justify-center">Edit sub bab</span>

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
                </section>
            </main>
        </div>
    </div>
@else
    <p>You do not have access to this pages.</p>
@endif

<script src="{{ asset('assets/js/syllabus-services/management-sub-bab.js') }}"></script> <!--- paginate sub bab ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->


<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/list-sub-bab-listener.js') }}"></script> <!--- pusher listener list sub bab ---->