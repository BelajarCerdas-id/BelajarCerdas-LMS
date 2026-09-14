@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Management Class',
    'linkBackButton' => $majorId
        ? route('lms.managementMajors.view', [$role, $schoolName, $schoolId, $managedRole])
        : route('lms.managementRoles.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5">

            <div id="alert-success-create-class"></div>
            <div id="alert-success-edit-class"></div>

            <main>
                <section class="relative pb-6">
                    <div id="container-management-class-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                        data-managed-role="{{ $managedRole }}" data-major-id="{{ $majorId }}">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-base sm:text-lg font-bold text-gray-800">
                                Manajemen Kelas
                            </h2>

                            <div>
                                <button
                                    class="btn-create-class px-6 h-11 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white text-sm font-semibold flex items-center gap-2 shadow-sm hover:shadow transition-all cursor-pointer">
                                    <i class="fa-solid fa-plus"></i>
                                    Tambah Kelas
                                </button>
                            </div>
                        </div>

                        <!-- FILTER CONTAINER -->
                        <div class="my-6 bg-white shadow-sm border border-gray-300 rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                    <h3 class="text-sm font-bold text-gray-700">Filter Kelas</h3>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div id="container-dropdown-tahun-ajaran"></div>
                                <div id="container-dropdown-class"></div>
                            </div>
                        </div>

                        <!---- TABLE LIST CLASS ---->
                        <div class="overflow-x-auto mt-6 pb-20">
                            <table id="table-management-class-list" class="min-w-full text-sm border-collapse">
                                <thead class="thead-table-management-class-list bg-gray-50 shadow-inner">
                                    <tr>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Fase</th>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tingkat Kelas</th>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Kelas</th>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tahun Ajaran</th>
                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-management-class-list">
                                    <!-- show data in ajax -->
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-container-management-class-list flex justify-center my-10"></div>

                        <div id="empty-message-management-class-list" class="w-full h-80 hidden">
                            <span class="flex h-full items-center justify-center text-gray-500">
                                Tidak ada kelas yang terdaftar.
                            </span>
                        </div>
                    </div>
                </section>

                <!---- MODAL CREATE CLASS ---->
                <dialog id="modal-create-class-lms-subscription" class="modal">
                    <div class="modal-box bg-white rounded-2xl max-w-lg">
                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div>

                        <h3 class="text-base sm:text-lg font-bold text-center mb-4 text-gray-800">Tambah Kelas Baru</h3>

                        <form id="form-create-class-lms-subscription" class="space-y-4" autocomplete="OFF">
                            <input type="hidden" name="school_partner_id" value="{{ $schoolId }}">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Fase
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <select name="fase_id" class="fase-id w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                    <option value="" class="hidden">Pilih Fase</option>
                                    @foreach ($phases as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_fase }}</option>
                                    @endforeach
                                </select>
                                <span id="error-fase_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <select name="kelas_id" class="kelas-id w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Kelas</option>
                                </select>
                                <span id="error-kelas_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Nama Kelas
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <input type="text" name="class_name" placeholder="Masukkan Nama Kelas" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                <span id="error-class_name" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tahun Ajaran
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <input type="text" name="tahun_ajaran" placeholder="Ex: 2026/2027" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                <span id="error-tahun_ajaran" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Akun Wali Kelas
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <input type="text" name="akun_wali_kelas" placeholder="Ex: abc@belajacerdas.id" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                <span id="error-akun_wali_kelas" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-create-class" type="button"
                                    class="h-11 px-6 bg-[#0071BC] hover:bg-blue-600 text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default disabled:opacity-50">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!---- modal edit kelas ---->
                <dialog id="my_modal_2" class="modal">
                    <div class="modal-box bg-white max-w-[800px] rounded-2xl p-6">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-base sm:text-lg font-bold text-center mb-4 text-gray-800">Edit Kelas</h3>

                        <form id="form-edit-class-lms-subscription" class="space-y-4" autocomplete="OFF">
                            <input type="hidden" name="school_partner_id" value="{{ $schoolId }}">
                            <input type="hidden" id="edit-class-id" name="class_id" value="">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Fase
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <select name="fase_id" class="fase-id w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                    <option value="" class="hidden">Pilih Fase</option>
                                    @foreach ($phases as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_fase }}</option>
                                    @endforeach
                                </select>
                                <span id="error-fase_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <select name="kelas_id" class="kelas-id w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Kelas</option>
                                    <!-- show data in ajax -->
                                </select>
                                <span id="error-kelas_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Nama Kelas
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <input type="text" id="edit-class-name" name="class_name" placeholder="Masukkan Nama Kelas" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                <span id="error-class_name" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tahun Ajaran
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <input type="text" id="edit-tahun-ajaran" name="tahun_ajaran" placeholder="Ex: 2026/2027" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                <span id="error-tahun_ajaran" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Akun Wali Kelas
                                    <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                </label>
                                <input type="text" id="edit-akun-wali-kelas" name="akun_wali_kelas" placeholder="Ex: abc@belajacerdas.id" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                <span id="error-akun_wali_kelas" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-edit-class" type="button"
                                    class="h-11 px-6 bg-[#0071BC] hover:bg-blue-600 text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default disabled:opacity-50">
                                    Simpan
                                </button>
                            </div>
                        </form>
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

<!---- paginate lms subscription management class ---->
<script src="{{ asset('assets/js/features/lms/administrator/lms-subscription-management-class.js') }}"></script> <!--- lms subscription management class ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/crud-class-listener.js') }}"></script> <!--- pusher listener pada saat crud class ---->