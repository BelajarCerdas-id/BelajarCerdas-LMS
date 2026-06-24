@include('components/sidebar-beranda', [
    'headerSideNav' => 'Children List',
    'linkBackButton' => route('lms.managementAccount.view', [$role, $schoolName, $schoolId, $managedRole]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-8 md:my-15 mx-4 md:mx-7.5 space-y-6">

            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-managed-role="{{ $managedRole }}"
                data-parent-id="{{ $parentId }}" class="space-y-6">

                <!-- HEADER -->
                <section>
                    <div class="relative bg-[#0071BC] text-white rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg flex flex-col lg:flex-row lg:items-center 
                        lg:justify-between gap-6" style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}');">
        
                        <!-- decorative -->
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        
                        <!-- LEFT -->
                        <div class="flex items-start gap-4 relative z-10">
        
                            <div class="p-6 md:w-14 md:h-14 rounded-2xl bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-children text-white text-lg md:text-xl"></i>
                            </div>
        
                            <div>
                                <h1 class="text-xl md:text-2xl font-bold">
                                    Daftar Anak
                                </h1>
        
                                <p class="text-xs md:text-sm text-white/80 max-w-xl mt-1">
                                    Kelola dan pantau seluruh anak yang terhubung dengan akun orang tua,
                                    termasuk status aktif dan informasi kelas mereka.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section>

                    <!-- SKELETON LOADING -->
                    <div id="skeleton-children-list"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">

                        @for ($i = 0; $i < 6; $i++)
                            <div class="bg-white border border-gray-200 rounded-2xl p-4 md:p-5 shadow-sm animate-pulse">

                                <div class="flex items-center gap-3 md:gap-4">

                                    <!-- Avatar -->
                                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-full bg-gray-200 shrink-0"></div>

                                    <!-- Content -->
                                    <div class="flex-1">

                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>

                                        <div class="h-3 bg-gray-100 rounded w-1/3 mb-2"></div>

                                        <div class="h-3 bg-gray-100 rounded w-2/3"></div>

                                    </div>

                                    <!-- Status -->
                                    <div class="h-6 w-16 rounded-full bg-gray-200"></div>

                                </div>

                            </div>
                        @endfor

                    </div>

                    <!-- DATA -->
                    <div id="container-children-list" class="hidden">
                        <div id="grid-children-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <!-- EMPTY MESSAGE -->
                    <div id="empty-message-children-list" class="hidden">
                        <div class="bg-white border border-gray-300 rounded-3xl shadow-sm">

                            <div class="flex flex-col items-center justify-center py-20 px-8 text-center">

                                <div class="w-28 h-28 rounded-full bg-linear-to-br from-blue-100 to-cyan-100 flex items-center justify-center mb-6">
                                    <i class="fa-solid fa-user-graduate text-5xl text-[#005B94]"></i>
                                </div>

                                <h3 class="text-2xl font-bold text-gray-800 mb-3">
                                    Tidak Ada Anak Terdaftar
                                </h3>

                                <p class="text-gray-500 text-base max-w-lg leading-relaxed">
                                    Saat ini belum terdapat data siswa yang terhubung dengan akun orang tua ini.
                                </p>

                            </div>

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

<script src="{{ asset('assets/js/features/lms/administrator/lms-school-subscription-parent-children-list.js') }}"></script>