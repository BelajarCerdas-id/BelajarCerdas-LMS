@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Management Majors',
    'linkBackButton' => route('lms.managementRoles.view', [$schoolName, $schoolId, $role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!---- alert success from ajax ---->
            <div id="alert-success-create-majors"></div>
            <div id="alert-success-edit-major"></div>

            <main>
                <section class="relative pb-6">
                    <div id="container-management-major-list" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-role="{{ $role }}">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Manajemen Jurusan
                            </h2>

                            <div>
                                <button
                                    class="btn-create-major px-4 py-2 rounded-lg bg-[#4189E0] text-white text-sm font-semibold flex items-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-plus"></i>
                                    Tambah Jurusan
                                </button>
                            </div>
                        </div>

                        <!-- DETAIL SEKOLAH -->
                        <div id="school-detail-card" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
                            <!-- show data in ajax -->
                        </div>

                        <div class="w-full flex items-center justify-end gap-4">
                            <div id="container-dropdown-tahun-ajaran">
                                <!-- show data in ajax -->
                            </div>
    
                            <div id="container-dropdown-class">
                                <!-- show data in ajax -->
                            </div>
                        </div>

                        <div id="grid-management-major-list" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-management-majors-list" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada jurusan yang terdafatar pada sekolah ini.
                        </span>
                    </div>
                </section>

                <!---- modal tambah jurusan ---->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white max-w[800px]">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-lg font-bold text-center mb-4">Tambah Jurusan Baru</h3>

                        <form id="form-create-major-lms-subscription" class="space-y-4" autocomplete="OFF">
                            <input type="hidden" name="school_partner_id" value="{{ $schoolId }}">

                            <div>
                                <label class="text-sm font-bold opacity-70 flex gap-1 pt-1 items-center">
                                    Nama Jurusan
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <input type="text" name="major_name" placeholder="Masukkan Nama Jurusan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                                <span id="error-major_name" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div>
                                <label class="text-sm font-bold opacity-70 flex gap-1 pt-1 items-center">
                                    Kode Jurusan
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <input type="text" name="major_code" placeholder="Ex: TKJ / IPA" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                                <span id="error-major_code" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-create-major" type="button"
                                    class="px-4 py-2 text-sm bg-[#4189E0] text-white font-bold rounded-lg cursor-pointer disabled:cursor-default">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!---- modal edit jurusan ---->
                <dialog id="my_modal_2" class="modal">
                    <div class="modal-box bg-white max-w[800px]">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-lg font-bold text-center mb-4">Edit Jurusan</h3>

                        <form id="form-edit-major-lms-subscription" class="space-y-4" autocomplete="OFF">
                            <input type="hidden" name="school_partner_id" value="{{ $schoolId }}">
                            <input type="hidden" id="edit-major-id" name="major_id" value="">

                            <div>
                                <label class="text-sm font-bold opacity-70 flex gap-1 pt-1 items-center">
                                    Nama Jurusan
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <input type="text" id="edit-major-name" name="major_name" placeholder="Masukkan Nama Jurusan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                                <span id="error-major_name" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div>
                                <label class="text-sm font-bold opacity-70 flex gap-1 pt-1 items-center">
                                    Kode Jurusan
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <input type="text" id="edit-major-code" name="major_code" placeholder="Ex: TKJ / IPA" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                                <span id="error-major_code" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-edit-major" type="button"
                                    class="px-4 py-2 text-sm bg-[#4189E0] text-white font-bold rounded-lg cursor-pointer disabled:cursor-default">
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

<!---- paginate lms subscription management majors ---->
<script src="{{ asset('assets/js/Features/lms/administrator/lms-subscription-management-majors.js') }}"></script> <!--- lms subscription management majors ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/crud-major-listener.js') }}"></script> <!--- pusher listener pada saat crud major ---->