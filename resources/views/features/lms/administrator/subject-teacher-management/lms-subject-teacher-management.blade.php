@include('components/sidebar-beranda', [
    'headerSideNav' => 'Subject Teacher',
    'linkBackButton' => route('lms.academicManagement.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-7.5">

            <div id="alert-success-insert-data-teacher-mapel"></div>
            <div id="alert-success-edit-data-teacher-mapel"></div>

            <main class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <section id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                    class="space-y-8 border-b border-gray-100 pb-8">

                    <!-- Header -->
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-gray-800">
                            Subject Teacher Management
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Penugasan guru ke mata pelajaran dan rombel kelas
                        </p>
                    </div>

                    <div>
                        <form id="create-teacher-mapel-form" autocomplete="OFF">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                <!--- Kurikulum --->
                                <div class="flex flex-col order-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Kurikulum
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <select name="kurikulum_id" id="id_kurikulum"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed">
                                        <option value="" class="hidden">Pilih Kurikulum</option>
                                        @foreach ($getCurriculum as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_kurikulum }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error-kurikulum_id" class="text-red-500 font-bold text-xs pt-1"></span>
                                </div>

                                <!--- Mapel --->
                                <div class="flex flex-col order-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Mata Pelajaran
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <select name="mapel_id" id="id_mapel"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed" disabled>
                                        <option class="hidden">Pilih Mata Pelajaran</option>
                                    </select>
                                    <span id="error-mapel_id" class="text-red-500 font-bold text-xs pt-1"></span>
                                </div>

                                <!--- Kelas --->
                                <div class="flex flex-col order-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Kelas
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <select name="kelas_id" id="id_kelas"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed" disabled>
                                        <option class="hidden">Pilih Kelas</option>
                                    </select>
                                    <span id="error-kelas_id" class="text-red-500 font-bold text-xs pt-1"></span>
                                </div>
            
                                <!-- rombel class -->
                                <div class="flex flex-col order-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Rombel Kelas
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <select id="school_class_id" name="school_class_id"
                                        class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default disabled:bg-gray-50 disabled:border-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed" disabled>
                                            <option>Pilih Rombel Kelas</option>
                                    </select>
                                    <span id="error-school_class_id" class="text-red-500 font-bold text-xs pt-1"></span>
                                </div>
                                
                                <!-- search teacher -->
                                <div class="flex flex-col order-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Guru
                                        <sup class="text-red-500 font-bold ml-0.5">&#42;</sup>
                                    </label>
                                    <input
                                        type="text" name="teacher" placeholder="Masukkan akun guru sekolah" class="w-full h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">
                                    <span id="error-teacher" class="text-red-500 font-bold text-xs pt-1"></span>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button id="submit-button-create-subject-teacher" type="button"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#0071BC] hover:bg-blue-600 h-11 px-8 text-sm font-semibold text-white shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                        Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!---- table list school partner lms subscription ---->
                <section class="relative pb-6 mt-6">
                    <h1 class="text-md md:text-xl font-bold opacity-70">
                        Subject Teacher List
                    </h1>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6">

                        <div id="container-dropdown-tahun-ajaran">
                            <!-- show data in ajax -->
                        </div>

                        <div id="container-dropdown-class">
                            <!-- show data in ajax -->
                        </div>

                        <!-- Search Guru -->
                        <div id="container-search-teacher" class="hidden">
                            <div class="flex flex-col w-full mb-2">
                                <label class="text-sm font-medium text-gray-600 mb-1">
                                    Filter Guru
                                </label>
                                <input type="text" id="search_teacher" class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-3" placeholder="Masukkan Nama Guru"
                                autocomplete="OFF">
                            </div>
                        </div>

                    </div>

                    <div class="overflow-x-auto pb-20 mt-8">
                        <table id="table-subject-teacher-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-subject-teacher-management hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Guru</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tahun Ajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fas fa-ellipsis-vertical"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-subject-teacher-management">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-subject-teacher-management flex justify-center my-10"></div>

                    <div id="empty-message-subject-teacher-management" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada guru yang terdaftar.
                        </span>
                    </div>
                </section>

                <!---- modal edit subject-teacher ---->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white w-max">
                        <form id="edit-subject-teacher-form" autocomplete="OFF">
                            <span class="text-xl font-bold flex justify-center">Edit Teacher Subject</span>

                            <input type="hidden" id="edit-subject-teacher-id" name="subject-teacher-id">
                            <input type="hidden" id="edit-mapel-id" name="mapel_id">
                            <input type="hidden" id="edit-school-class-id" name="school_class_id">

                            <div class="mt-4 w-80">
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    Guru
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <input
                                    type="text" id="edit-teacher" name="teacher" placeholder="Masukkan akun guru sekolah" class="w-full rounded-lg border border-gray-300 
                                        bg-white px-3 py-2.5 pr-10 text-sm outline-none">
                                <span id="error-teacher" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div class="flex justify-end mt-8">
                                <button id="submit-button-edit-subject-teacher" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg bg-[#0071BC] px-6 py-2.5 text-sm font-bold text-white cursor-pointer disabled:cursor-default">
                                        Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>Close</button>
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

<script src="{{ asset('assets/js/features/lms/administrator/teacher-mapel-management/lms-teacher-mapel-management.js') }}"></script> <!--- lms teacher mapel management ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown curriculum core ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kelas-rombel-dropdown.js') }}"></script> <!--- dependent dropdown rombel by kelas ---->