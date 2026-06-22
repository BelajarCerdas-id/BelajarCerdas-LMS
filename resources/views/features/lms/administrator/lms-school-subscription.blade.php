@include('components/sidebar-beranda', ['headerSideNav' => 'LMS Subscription'])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!--- show alerts --->
            <div id="alert-success-insert-school-partner"></div>

            <main>
                <div class="flex justify-end gap-8">
                    <div class="">
                        <!--- search bar --->
                        <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-40 sm:w-66 md:w-max">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                            </svg>
                            <input id="search_school" type="search" class="grow text-sm"
                                placeholder="Cari sekolah..." autocomplete="OFF" />
                        </label>
                    </div>
                </div>

                <!---- table list school partner lms subscription ---->
                <section id="container-school-partner-list" data-role="{{ $role }}" class="relative pb-6 mt-6">
                    <div class="overflow-x-auto">
                        <table id="table-school-partner-list" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-school-partner-list hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Sekolah</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">NPSN</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Kepala Sekolah</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">NIK Kepala Sekolah</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Manajemen Akademik</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-school-partner-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-school-partner-list flex justify-center my-10"></div>

                    <div id="empty-message-school-partner-list" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada sekolah yang terdaftar.
                        </span>
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

<script src="{{ asset('assets/js/features/lms/administrator/paginate-lms-school-subscription.js') }}"></script> <!--- paginate school partner ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel preview ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/management-school-subscription.js') }}"></script> <!--- pusher listener pada saat crud school subscription ---->