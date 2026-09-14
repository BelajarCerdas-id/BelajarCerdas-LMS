@include('components/sidebar-beranda', [
    'linkBackButton' => route('fase.view', [$curriculumName, $curriculumId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Kelas',
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">

            <!---- alert success from ajax ---->
            <div id="alert-success-insert-data-kelas"></div>
            <div id="alert-success-edit-data-kelas"></div>

            <main>
                <section class="bg-white shadow-sm p-6 rounded-2xl border-gray-200 border">
                    <!---- Form input kelas  ---->
                    <form id="create-kelas-form" autocomplete="OFF">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                            <div class="w-full">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas
                                    <sup class="text-red-500 pl-0.5">&#42;</sup>
                                </label>
                                <div class="flex relative max-w-lg mt-1">
                                    <div class="flex gap-3 w-full items-start">
                                        <div class="w-full">
                                            <input type="text" name="kelas"
                                                class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 placeholder:text-gray-400" placeholder="Masukkan Kelas">
                                            <span id="error-kelas" class="text-red-500 text-xs mt-1 font-bold block"></span>
                                        </div>
                                        <button id="submit-button-create-kelas" type="button"
                                            class="bg-[#0071BC] hover:bg-blue-600 text-white font-semibold h-11 px-6 rounded-xl shadow-sm hover:shadow transition-all text-sm flex items-center justify-center cursor-pointer shrink-0 disabled:cursor-default">
                                            Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data kelas  ---->
                    <div id="container-management-kelas" data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" data-fase-id="{{ $faseId }}" 
                        class="overflow-x-auto mt-8 pb-24">
                        <table id="tableSyllabusKelas" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-syllabus-kelas hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                        Kelas
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Detail
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableListSyllabusKelas">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container-syllabus-kelas flex justify-center my-4 sm:my-0"></div>

                    <div id="emptyMessageSyllabusKelas" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada kelas.
                        </span>
                    </div>

                    <!---- modal edit kelas ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white rounded-2xl w-max">
                            <form id="edit-kelas-form" autocomplete="OFF">
                                <span class="text-xl font-bold flex justify-center text-gray-800">Edit Kelas</span>

                                <input type="hidden" id="edit-curriculum-id">
                                <input type="hidden" id="edit-fase-id">
                                <input type="hidden" id="edit-kelas-id">

                                <div class="mt-4 w-80">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                                    <input type="text" id="edit-kelas" name="kelas"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 placeholder:text-gray-400 mt-1" placeholder="Masukkan Kelas">
                                    <span id="error-kelas" class="text-red-500 text-xs mt-1 font-bold block"></span>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-edit-kelas" type="button"
                                        class="bg-[#0071BC] hover:bg-blue-600 text-white font-semibold h-11 px-6 rounded-xl shadow-sm hover:shadow transition-all text-sm flex items-center justify-center cursor-pointer disabled:cursor-default">
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

<script src="{{ asset('assets/js/syllabus-services/default/management-kelas.js') }}"></script> <!--- paginate kelas ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->


<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/list-kelas-listener.js') }}"></script> <!--- pusher listener list kelas ---->
