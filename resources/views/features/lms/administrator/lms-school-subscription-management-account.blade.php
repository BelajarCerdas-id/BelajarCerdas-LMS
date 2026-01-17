@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Management Account',
    'linkBackButton' => route('lms.managementRoles.view', [$schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>

                <!-- DETAIL SEKOLAH -->
                <div id="school-detail-card" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
                    <!-- show data in ajax -->
                </div>

                <div class="my-8 flex justify-end">
                    <!--- search bar --->
                    <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-66 md:w-max">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                        </svg>
                        <input id="search_user" type="search" class="grow text-sm"
                            placeholder="Cari nama pengguna..." autocomplete="OFF"/>
                    </label>
                </div>

                <!---- table list school staff ---->
                <section class="relative">
                    <div id="container-management-staff-list" class="overflow-x-auto pb-6"
                        data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-role="{{ $role }}">

                        <table id="table-management-staff-list" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-management-staff-list hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Role</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Personal Email</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Email Akun</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No.HP</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status Akun</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-management-staff-list">
                                {{-- show data in ajax --}}
                            </tbody>
                        </table>

                        <div class="pagination-container-management-staff-list flex justify-center my-10"></div>

                        <div id="empty-message-management-staff-list" class="w-full h-96 hidden">
                            <span class="w-full h-full flex items-center justify-center">
                                Tidak ada pengguna yang terdaftar.
                            </span>
                        </div>
                    </div>
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

<script src="{{ asset('assets/js/Features/lms/administrator/lms-subscription-management-account.js') }}"></script> <!--- lms subscription management account ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/management-account.js') }}"></script> <!--- pusher listener pada saat manage account ---->
