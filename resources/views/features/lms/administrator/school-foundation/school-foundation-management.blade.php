@include('components/sidebar-beranda', ['headerSideNav' => 'Manajemen Yayasan']);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-add-school-to-foundation"></div>
            <div id="alert-success-remove-school-to-foundation"></div>

            <main id="container" data-role="{{ $role }}">

                <!-- HEADER - MANAJEMEN YAYASAN -->
                <section class="mb-6">

                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 lg:p-8 shadow-xl">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-building-columns absolute -top-8 -right-6 text-[120px] lg:text-[180px] text-white/5 rotate-12 pointer-events-none"></i>

                        <i class="fa-solid fa-school absolute -bottom-10 -left-6 text-[90px] lg:text-[140px] text-white/5 -rotate-12 pointer-events-none"></i>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg shrink-0">
                                        <i class="fa-solid fa-building-columns text-white text-xl lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white leading-tight">
                                            Manajemen Yayasan
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm sm:text-base text-white/80 leading-relaxed">
                                    Kelola data yayasan beserta sekolah-sekolah yang berada di bawah naungannya.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div class="w-full lg:w-auto">
                                <a href="{{ route('lms.schoolFoundation.form.view', [
                                    'role' => $role
                                ]) }}">
                                    <button
                                        class="btn w-full lg:w-auto bg-white border-white text-[#005A9C] hover:bg-slate-100 hover:border-slate-100 shadow-lg">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Yayasan
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section class="mb-6">

                    <!-- Skeleton -->
                    <div id="kpi-loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                        @for ($i = 0; $i < 4; $i++)
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">

                                        <div class="flex-1">
                                            <!-- Title -->
                                            <div class="skeleton h-4 w-24 mb-4"></div>

                                            <!-- Number -->
                                            <div class="skeleton h-9 w-16 mb-4"></div>
                                        </div>

                                        <!-- Icon -->
                                        <div class="skeleton w-14 h-14 rounded-xl"></div>

                                    </div>
                                </div>
                            </div>
                        @endfor

                    </div>

                    <!-- Content -->
                    <div id="kpi-content" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                            <!-- Total Yayasan -->
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-base-content/70">
                                                Total Yayasan
                                            </p>

                                            <h2 id="total-school-foundation" class="text-3xl font-bold mt-2">
                                                0
                                            </h2>
                                        </div>

                                        <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center">
                                            <i class="fa-solid fa-building-columns text-primary text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Sekolah -->
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-base-content/70">
                                                Total Sekolah
                                            </p>

                                            <h2 id="total-school" class="text-3xl font-bold mt-2">
                                                0
                                            </h2>
                                        </div>

                                        <div class="w-14 h-14 rounded-xl bg-success/10 flex items-center justify-center">
                                            <i class="fa-solid fa-school text-success text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Guru -->
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-base-content/70">
                                                Total Guru
                                            </p>

                                            <h2 id="total-teacher" class="text-3xl font-bold mt-2">
                                                0
                                            </h2>
                                        </div>

                                        <div class="w-14 h-14 rounded-xl bg-warning/10 flex items-center justify-center">
                                            <i class="fa-solid fa-user-tie text-warning text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Siswa -->
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-base-content/70">
                                                Total Siswa
                                            </p>

                                            <h2 id="total-student" class="text-3xl font-bold mt-2">
                                                0
                                            </h2>
                                        </div>

                                        <div class="w-14 h-14 rounded-xl bg-info/10 flex items-center justify-center">
                                            <i class="fa-solid fa-users text-info text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </section>

                <!-- DAFTAR YAYASAN -->
                <section class="mb-8">
                    <div class="bg-base-100 border border-base-300 rounded-2xl shadow-sm">

                        <!-- Header -->
                        <div class="border-b border-base-300 p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div>
                                    <h2 class="text-2xl font-bold flex items-center gap-3">
                                        <i class="fa-solid fa-building-columns text-primary"></i>
                                        Daftar Yayasan
                                    </h2>

                                    <p class="text-base-content/60 mt-1">
                                        Kelola seluruh yayasan beserta sekolah yang berada di bawah naungannya.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div id="container-school-foundation-list-content" class="p-6 hidden">

                            <!-- CARD -->
                            <div id="grid-school-foundation-list" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                <!-- show data in ajax -->
                            </div>
                        </div>

                        <!-- Empty Message -->
                        <div id="empty-message-school-foundation-list" class="hidden">

                            <div class="flex flex-col items-center justify-center py-20 px-6 text-center">

                                <!-- Icon -->
                                <div class="w-24 h-24 rounded-full bg-blue-50 flex items-center justify-center border border-blue-100">
                                    <i class="fa-solid fa-building-columns text-4xl text-primary"></i>
                                </div>

                                <!-- Title -->
                                <h3 class="mt-6 text-xl font-bold text-base-content">
                                    Belum Ada Yayasan
                                </h3>

                                <!-- Description -->
                                <p class="mt-2 max-w-lg text-sm text-base-content/60 leading-relaxed">
                                    Saat ini belum ada yayasan yang terdaftar. Buat yayasan baru untuk mulai
                                    mengelola sekolah, akun yayasan, dan akses sekolah.
                                </p>

                                <!-- Action -->
                                <a href="{{ route('lms.schoolFoundation.form.view', [
                                    'role' => $role
                                ]) }}">
                                    <button type="button" id="btn-create-first-foundation" class="btn mt-6 bg-[#0071BC] border-none text-white hover:bg-[#005f9f]">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Yayasan
                                    </button>
                                </a>
                            </div>
                        </div>

                        <div class="pagination-container-school-foundation-list flex justify-center my-10"></div>
                    </div>
                </section>

                <dialog id="modal-add-school-foundation" class="modal">
                    <div class="modal-box w-[95%] max-w-5xl p-0 rounded-2xl overflow-hidden">

                        <!-- HEADER -->
                        <div class="bg-[#0071BC] text-white px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-school text-xl"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-xl font-bold">
                                            Tambah Sekolah
                                        </h3>

                                        <p class="text-sm text-blue-100 mt-1">
                                            Pilih satu atau beberapa sekolah yang akan bergabung ke yayasan ini.
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button type="submit" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/15">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- BODY -->
                        <form id="add-school-to-foundation-form">
                            <div class="p-6">

                                <!-- Header Section -->
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">

                                    <div>
                                        <h4 class="font-semibold text-base flex items-center gap-2">
                                            <i class="fa-solid fa-school text-primary"></i>
                                            Pilih Sekolah
                                        </h4>

                                        <p class="text-sm text-base-content/60 mt-1">
                                            Cari dan pilih sekolah yang akan ditambahkan ke yayasan.
                                        </p>
                                    </div>

                                    <!-- Search -->
                                    <label class="input input-bordered border-gray-300 flex items-center gap-2 w-full lg:w-80 outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                        </svg>

                                        <input id="search_school" type="search" class="grow text-sm" placeholder="Cari sekolah..." autocomplete="off">
                                    </label>
                                </div>

                                <!-- Summary -->
                                <div class="mb-5 rounded-xl border border-blue-100 bg-linear-to-r from-blue-50 via-sky-50 to-white px-4 py-3">
                                    <div class="flex items-start gap-3">

                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 shrink-0">
                                            <i class="fa-solid fa-circle-info text-blue-600"></i>
                                        </div>

                                        <div>
                                            <h5 class="text-sm font-semibold text-slate-800">
                                                Informasi
                                            </h5>

                                            <p class="mt-1 text-sm text-slate-600 leading-relaxed">
                                                Hanya sekolah yang belum tergabung ke yayasan yang ditampilkan pada daftar ini.
                                            </p>
                                        </div>

                                    </div>
                                </div>

                                <div class="flex justify-end mb-5 pr-6">
                                    <span id="available-school-count" class="badge badge-outline badge-primary">
                                        Memuat data...
                                    </span>
                                </div>

                                <!-- Error -->
                                <span id="error-school_partner_id" class="text-xs font-semibold text-red-500"></span>

                                <!-- Loading -->
                                <div id="loading-school-foundation" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @for($i = 0; $i < 4; $i++)
                                        <div class="rounded-xl border border-gray-200 p-4 animate-pulse">
                                            <div class="flex gap-3">
                                                <div class="w-14 h-14 rounded-xl bg-gray-200"></div>

                                                <div class="flex-1">
                                                    <div class="h-4 w-40 rounded bg-gray-200"></div>
                                                    <div class="h-3 w-24 rounded bg-gray-200 mt-3"></div>

                                                    <div class="grid grid-cols-2 gap-2 mt-4">
                                                        <div class="h-10 rounded bg-gray-200"></div>
                                                        <div class="h-10 rounded bg-gray-200"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                <!-- List -->
                                <div id="school-foundation-list" class="hidden max-h-74 overflow-y-auto pr-2 space-y-3">
                                    <!-- show data in ajax -->
                                </div>

                                <!-- Empty -->
                                <div id="empty-school-foundation" class="hidden py-16">
    
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center">
                                            <i class="fa-solid fa-school text-3xl text-slate-400"></i>
                                        </div>
    
                                        <h4 class="mt-5 font-semibold text-base">
                                            Tidak Ada Sekolah
                                        </h4>
    
                                        <p class="mt-2 text-sm text-base-content/60 max-w-md">
                                            Tidak ada data sekolah yang dapat ditemukan.
                                        </p>
                                    </div>
                                </div>
                            </div>
    
                            <!-- FOOTER -->
                            <div class="sticky bottom-0 bg-base-100 border-t border-gray-200 px-6 py-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
    
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                            <i class="fa-solid fa-check text-primary"></i>
                                        </div>
    
                                        <div>
                                            <p class="text-xs text-base-content/60">
                                                Sekolah Dipilih
                                            </p>
    
                                            <span id="selected-school-count" class="font-semibold text-primary">
                                                0 Sekolah
                                            </span>
                                        </div>
                                    </div>
    
                                    <div class="flex gap-3">
                                        <button type="button" id="btn-close-add-school-foundation" class="btn btn-outline bg-primary text-white font-bold">
                                            Tutup
                                        </button>
    
                                        <button id="submit-button-add-school-to-foundation" class="btn bg-[#0071BC] border-none text-white hover:bg-[#005f9f]">
                                            <i class="fa-solid fa-plus"></i>
                                            Tambahkan Sekolah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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

<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/school-foundation-list.js') }}"></script> <!--- school foundation list ---->
<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/school-foundation-kpi.js') }}"></script> <!--- school foundation kpi ---->