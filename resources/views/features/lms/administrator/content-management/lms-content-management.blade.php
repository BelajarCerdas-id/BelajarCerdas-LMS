@include('components/sidebar-beranda', array_merge(
    [
        'headerSideNav' => 'Content Management',
    ],
    $schoolId
        ? [
            'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
            'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
        ]
        : []
))

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-create-content"></div>

            <main class="bg-white shadow-lg h-max rounded-lg border border-gray-200">
                <section id="container" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="border-b border-gray-200">
                    <form id="content-management-form">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
                            <!--- Kurikulum --->
                            <div class="flex flex-col order-1 xl:order-0">
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

                            <!--- Kelas --->
                            <div class="flex flex-col order-3 lg:order-5 xl:order-0">
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

                            <!--- Bab --->
                            <div class="flex flex-col order-4 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Bab
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="bab_id" id="id_bab"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Bab</option>
                                </select>
                                <span id="error-bab_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- Service --->
                            <div class="flex flex-col order-2 lg:order-3 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Service
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="service_id" id="id_service"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Service</option>
                                </select>
                                <span id="error-service_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!--- Mapel --->
                            <div class="flex flex-col order-3 lg:order-2 xl:order-0">
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

                            <!--- Sub Bab --->
                            <div class="flex flex-col order-5 xl:order-0">
                                <label class="mb-2 text-sm">
                                    Sub Bab
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <select name="sub_bab_id" id="id_sub_bab"
                                    class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                    <option class="hidden">Pilih Sub Bab</option>
                                </select>
                                <span id="error-sub_bab_id" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div id="dynamic-form" class="order-6 xl:order-0"></div>
                        </div>

                        <!--- button bulkupload content --->
                        <div class="flex justify-end w-full px-6 pb-6">
                            <button type="button" id="submit-button-create-content"
                                class="bg-[#0071BC] text-white text-md font-bold h-10 px-10 rounded-lg shadow-md flex gap-2 items-center justify-center cursor-pointer disabled:cursor-default">
                                <i class="fa-solid fa-circle-plus"></i>
                                Simpan
                            </button>
                        </div>
                    </form>
                </section>

                <section class="mt-6 px-6">
                    <span class="text-lg font-bold opacity-70">List Content</span>
                    <div class="overflow-x-auto mt-6 pb-20">
                        <table id="table-content-management-list" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-content-management-list hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kurikulum</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Service</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Bab</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Sub Bab</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Sumber Content</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-content-management-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-content-management-list flex justify-center my-10"></div>

                    <div id="empty-message-content-management-list" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada content yang terdaftar.
                        </span>
                    </div>
                </section>

                <!---- modal history content  ---->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white">
                        <h3 class="font-bold text-lg mb-5 text-center">History Content</h3>

                        <div class="flex items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-4">
                                <i class="fa-solid fa-circle-user text-5xl text-gray-400"></i>
    
                                <div class="flex flex-col gap-1">
                                    <span id="text-nama_lengkap" class="font-semibold"></span>
                                    <span id="text-role" class="text-sm text-gray-500"></span>
                                    <span id="text-updated_at" class="text-xs text-gray-400"></span>
                                </div>
                            </div>

                            <div class="">
                                <span class="text-[#0071BC] font-bold text-sm">Publisher</span>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

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
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/administrator/content-management/lms-content-management.js') }}"></script> <!--- lms content management ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-service-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/content-management/lms-content-management-listener.js') }}"></script>