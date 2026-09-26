@include('components/sidebar-beranda', [
    'linkBackButton' => route('mapel.view', [$curriculumName, $curriculumId, $faseId, $kelasId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Bab',
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">

            <!---- alert success from ajax ---->
            <div id="alert-success-insert-data-bab"></div>
            <div id="alert-success-edit-data-bab"></div>

            <main>
                <section class="bg-white shadow-sm p-6 rounded-2xl border-gray-200 border">
                    <!---- Form input bab  ---->
                    <form id="create-bab-form" autocomplete="OFF">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">

                            <div class="w-full">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Bab
                                    <sup class="text-red-500 pl-0.5">&#42;</sup>
                                </label>
                                <div class="w-full">
                                    <input type="text" name="nama_bab"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 placeholder:text-gray-400" placeholder="Masukkan nama bab">
                                    <span id="error-nama_bab" class="text-red-500 text-xs mt-1 font-bold block"></span>
                                </div>
                            </div>

                            <div class="w-full">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Semester
                                </label>
                                <div class="flex flex-col sm:flex-row gap-3 items-start">
                                    <div class="w-full">
                                        <select name="semester"
                                            class="w-full h-11 px-4 pr-10 rounded-xl border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 outline-none cursor-pointer">
                                            <option value="" class="hidden">Pilih Semester</option>
                                            <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>
                                                1
                                            </option>
                                            <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>
                                                2
                                            </option>
                                        </select>
                                        <span id="error-semester" class="text-red-500 text-xs mt-1 font-bold block"></span>
                                    </div>
                                    <button id="submit-button-create-bab" type="button"
                                        class="bg-[#0071BC] hover:bg-blue-600 text-white font-semibold h-11 px-6 rounded-xl shadow-sm hover:shadow transition-all text-sm flex items-center justify-center cursor-pointer shrink-0 disabled:cursor-default">
                                        Tambah
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data bab  ---->
                    <div id="container-management-bab" data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}" data-fase-id="{{ $faseId }}" 
                        data-kelas-id="{{ $kelasId }}" data-mapel-id="{{ $mapelId }}"
                        class="overflow-x-auto mt-8 pb-24">
                        <table id="tableSyllabusBab" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-syllabus-bab hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                        Bab
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Semester
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Detail
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableListSyllabusBab">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container-syllabus-bab flex justify-center my-4 sm:my-0"></div>

                    <div id="emptyMessageSyllabusBab" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada bab.
                        </span>
                    </div>

                    <!---- modal edit bab ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white rounded-2xl w-max">
                            <form id="edit-bab-form" autocomplete="OFF">
                                <span class="text-xl font-bold flex justify-center text-gray-800">Edit Bab</span>

                                <input type="hidden" id="edit-curriculum-id">
                                <input type="hidden" id="edit-fase-id">
                                <input type="hidden" id="edit-kelas-id">
                                <input type="hidden" id="edit-mapel-id">
                                <input type="hidden" id="edit-bab-id">

                                <div class="mt-4 w-80">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Bab
                                        <sup class="text-red-500 pl-0.5">&#42;</sup>
                                    </label>
                                    <input type="text" id="edit-nama_bab" name="nama_bab"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 placeholder:text-gray-400 mt-1" placeholder="Masukkan nama bab">
                                    <span id="error-nama_bab" class="text-red-500 text-xs mt-1 font-bold block"></span>
                                </div>

                                <div class="mt-4 w-80">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Semester
                                    </label>
                                    <select id="edit-semester" name="semester"
                                        class="w-full h-11 px-4 pr-10 rounded-xl border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 outline-none cursor-pointer mt-1">
                                        <option value="" class="hidden">Pilih Semester</option>
                                        <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>
                                            1
                                        </option>
                                        <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>
                                            2
                                        </option>
                                    </select>
                                    <span id="error-semester" class="text-red-500 text-xs mt-1 font-bold block"></span>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-edit-bab" type="button"
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

<script src="{{ asset('assets/js/syllabus-services/default/management-bab.js') }}"></script> <!--- paginate bab ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->


<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/syllabus-services/list-bab-listener.js') }}"></script> <!--- pusher listener list bab ---->