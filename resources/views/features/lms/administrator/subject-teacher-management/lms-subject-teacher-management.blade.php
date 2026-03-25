@include('components/sidebar-beranda', [
    'headerSideNav' => 'Subject Teacher',
    'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition cursor-pointer-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-insert-data-teacher-mapel"></div>
            <div id="alert-success-edit-data-teacher-mapel"></div>

            <main class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <section id="container" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="space-y-8 border-b border-gray-300 pb-8">

                    <!-- Header -->
                    <div>
                        <h1 class="text-md md:text-xl font-bold opacity-70">
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
                                <div class="flex flex-col order-2">
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

                                <!--- Kelas --->
                                <div class="flex flex-col order-2">
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
            
                                <!-- rombel class -->
                                <div class="flex flex-col order-2">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Rombel Kelas
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <select id="school_class_id" name="school_class_id"
                                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                            <option>Pilih Rombel Kelas</option>
                                    </select>
                                    <span id="error-school_class_id" class="text-red-500 font-bold text-xs pt-2"></span>
                                </div>
                                
                                <!-- search teacher -->
                                <div class="flex flex-col order-2">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Guru
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <input
                                        type="text" name="teacher" placeholder="Masukkan akun guru sekolah" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 pr-10 text-sm 
                                            outline-none">
                                    <span id="error-teacher" class="text-red-500 font-bold text-xs pt-2"></span>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button id="submit-button-create-subject-teacher" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg bg-[#0071BC] px-6 py-2.5 text-sm font-bold text-white cursor-pointer disabled:cursor-default">
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