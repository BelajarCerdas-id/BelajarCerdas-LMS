@include('components/sidebar-beranda', [
    'headerSideNav' => 'Assessment Type',
    'linkBackButton' => route('lms.academicManagement.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">

            <div id="alert-success-insert-data-assessment-type"></div>
            <div id="alert-success-edit-data-assessment-type"></div>

            <main>
                <section>
                    <!---- DETAIL SEKOLAH ---->
                    <div id="school-detail-card" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden"></div>
                </section>

                <section class="bg-white shadow-sm p-4 sm:p-6 rounded-2xl border-gray-200 border">

                    <!---- Form input assessment type  ---->
                    <form id="create-assessment-type-form" autocomplete="off">
                        <div class="py-4 space-y-6">

                            <!-- ================= HEADER ================= -->
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-800">
                                    Buat Jenis Asesmen
                                </h2>
                            </div>

                            <!-- ================= BASIC INFO ================= -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Nama Asesmen 
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <input type="text" name="name" placeholder="Contoh: ASTS, ASAS, Quiz, Homework, Project" 
                                        class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                    <span id="error-name" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Mode Asesmen
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <select name="assessment_mode_id"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                        <option value="" class="hidden">Pilih Mode Asesmen</option>
                                        @foreach ($getAssessmentMode as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error-assessment_mode_id" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                            </div>

                            <!-- ================= REMEDIAL POLICY ================= -->
                            <div class="border-t border-gray-200 pt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">
                                    Kebijakan Remedial
                                </label>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <!-- IZINKAN REMEDIAL -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Izinkan Remedial
                                            <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                        </label>
                                        <select name="is_remedial_allowed" id="is_remedial_allowed"
                                            class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                            <option value="" class="hidden">Pilih Kebijakan</option>
                                            <option value="0">Tidak</option>
                                            <option value="1">Ya</option>
                                        </select>
                                        <span id="error-is_remedial_allowed" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    </div>

                                    <!-- MAKSIMAL REMEDIAL -->
                                    <div id="max-remedial-wrapper" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Maksimal Remedial
                                            <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                        </label>
                                        <input type="number" name="max_remedial_attempt" id="max_remedial_attempt" min="1" value="1" placeholder="Masukkan jumlah maksimal remedial"
                                            class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                        <span id="error-max_remedial_attempt" class="text-red-500 text-xs mt-1 font-bold"></span>
                                        <p class="text-[11px] text-gray-500 mt-1">
                                            Contoh: 2 berarti siswa boleh remedial maksimal 2 kali.
                                        </p>
                                    </div>

                                </div>
                            </div>

                            <!-- ================= SUBMIT ================= -->
                            <div class="flex justify-end pt-4">
                                <button type="button" id="submit-button-create-assessment-type"
                                    class="h-11 px-8 bg-[#0071BC] hover:bg-blue-600 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default">
                                    Simpan Jenis Asesmen
                                </button>
                            </div>

                        </div>
                    </form>

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data assessment type  ---->
                    <div id="container-assessment-type-management" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="overflow-x-auto mt-8 pb-24">

                        <h2 class="text-lg font-bold text-gray-800 pb-6">
                            Assessment Type List
                        </h2>
                                
                        <table id="table-assessment-type-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-assessment-type-management hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Nama Asesmen
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Mode Asesmen
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Kebijakan Remedial
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Maksimal Remedial
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Action
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="table-list-assessment-type-management">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container-assessment-type-management flex justify-center my-4 sm:my-0"></div>

                    <div id="empty-message-assessment-type-management" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada data.
                        </span>
                    </div>

                    <!---- modal edit assessment type ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white max-w-[800px] rounded-2xl p-6 shadow-xl">
                            <form id="edit-assessment-type-form" autocomplete="OFF">
                                <h3 class="text-base sm:text-lg font-bold text-center mb-6 text-gray-800">Edit Assessment Type</h3>

                                <input type="hidden" id="edit-assessment-type-id">

                                <div class="flex flex-col gap-6 w-full">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Nama Asesmen
                                            <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                        </label>
                                        <input type="text" id="edit-assessment-type-name" name="name"
                                            class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all" placeholder="Masukkan Nama Asesmen">
                                        <span id="error-name" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    </div>
    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Mode Asesmen
                                            <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                        </label>
                                        <select id="edit-assessment-mode-id" name="assessment_mode_id"
                                            class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                            <option value="" class="hidden">Pilih Mode Asesmen</option>
                                            @foreach ($getAssessmentMode as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error-assessment_mode_id" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    </div>

                                    <!-- REMEDIAL -->
                                    <div class="border-t border-gray-200 pt-6">
                                        <label class="block text-sm font-semibold text-gray-700 mb-4">
                                            Kebijakan Remedial
                                        </label>

                                        <!-- IZINKAN REMEDIAL -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                Izinkan Remedial
                                                <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                            </label>
                                            <select id="edit-is-remedial-allowed" name="is_remedial_allowed"
                                                class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                                <option value="" class="hidden">Pilih Kebijakan</option>
                                                <option value="0">Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                            <span id="error-is_remedial_allowed" class="text-red-500 text-xs mt-1 font-bold"></span>
                                        </div>

                                        <!-- MAKSIMAL REMEDIAL -->
                                        <div id="edit-max-remedial-wrapper" class="hidden mt-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                Maksimal Remedial
                                                <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                            </label>
                                            <input type="number" id="edit-max-remedial-attempt" name="max_remedial_attempt" min="1" value="1" placeholder="Masukkan jumlah maksimal remedial"
                                                class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                            <span id="error-max_remedial_attempt" class="text-red-500 text-xs mt-1 font-bold"></span>
                                            <p class="text-[11px] text-gray-500 mt-1">
                                                Contoh: 2 berarti siswa boleh remedial maksimal 2 kali.
                                            </p>
                                        </div>
                                    </div>

                                </div>

                                <div class="flex justify-end gap-2 mt-8">
                                    <button id="submit-button-edit-assessment-type" type="button"
                                        class="h-11 px-6 bg-[#0071BC] hover:bg-blue-600 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <form method="dialog" class="modal-backdrop">
                            <button>Close</button>
                        </form>
                    </dialog>

                    <!---- modal history assessment type ---->
                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-white max-w-md">
                            <h3 class="font-bold text-lg mb-6 text-center">History</h3>

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
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/administrator/assessment-type-management/lms-assessment-type-management.js') }}"></script>

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/assessment-type-management/lms-assessment-type-management-listener.js') }}"></script>