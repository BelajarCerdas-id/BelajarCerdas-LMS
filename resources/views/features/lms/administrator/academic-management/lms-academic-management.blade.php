@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Manajemen Akademik',
    'linkBackButton' => route('lms.schoolSubscription.view'),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
                <!---- list card academic management ---->
                <section class="relative">
                    <div id="container-academic-management-list" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                        <!-- DETAIL SEKOLAH -->
                        <div id="school-detail-card"
                            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
                        </div>

                        <div id="grid-academic-management-list" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8 items-stretch">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-academic-management-list" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada role yang terdafatar pada sekolah ini.
                        </span>
                    </div>
                </section>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/administrator/academic-management/lms-academic-management.js') }}"></script> <!--- paginate academic management ---->