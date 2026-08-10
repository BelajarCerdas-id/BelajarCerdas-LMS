@include('components/sidebar-beranda', [
    'headerSideNav' => 'Kelola Akses',
    'linkBackButton' => route('lms.schoolFoundation.manage.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT -->
            <div id="alert-success-school-foundation-create-user"></div>

            <main id="container" data-role="{{ $role }}" data-school-foundation-id="{{ $schoolFoundationId }}">

                <section>

                    <!-- HERO -->
                    <div class="rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 md:p-8 text-white shadow-lg">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- Left -->
                            <div class="flex items-start gap-3 md:gap-5">

                                <div class="flex h-14 w-14 md:h-18 md:w-18 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                    <i class="fa-solid fa-users-gear text-xl md:text-3xl"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="mb-2 text-xs md:text-sm text-white/80">
                                        Yayasan
                                    </div>

                                    <h1 class="text-lg font-bold">
                                        Kelola Akses Yayasan
                                    </h1>

                                    <!-- Desktop -->
                                    <p class="hidden md:block mt-3 text-sm leading-6 text-white/90 max-w-2xl">
                                        Kelola akun yang memiliki akses ke yayasan. Pengguna yang
                                        terdaftar dapat mengelola dan memantau seluruh data sekolah
                                        yang berada di bawah yayasan.
                                    </p>
                                </div>
                            </div>

                            <!-- Mobile -->
                            <p class="flex md:hidden text-sm leading-6 text-white/90">
                                Kelola akun yang memiliki akses ke yayasan. Pengguna yang
                                terdaftar dapat mengelola dan memantau seluruh data sekolah
                                yang berada di bawah yayasan.
                            </p>

                            <!-- Right -->
                            <div class="flex w-full lg:w-auto">
                                <a href="{{ route('lms.schoolFoundation.manage.view', [
                                        'role' => Auth::user()->role
                                    ]) }}" class="btn btn-sm md:btn-md w-full lg:w-auto border-none bg-white text-[#0071BC] hover:bg-gray-100">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="mt-6 flex justify-end w-full">
                        <button type="button" id="btn-open-create-foundation-access" class="w-max btn btn-sm md:btn-md border-none bg-[#0071BC] text-white">
                            <i class="fa-solid fa-user-plus"></i>
                            Tambah Pengguna
                        </button>
                    </div>
                </section>
                
                <section>

                    <!-- USER LIST -->
                    <div class="mt-8">

                        <!-- Header -->
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-5">
                            <div>
                                <h2 class="text-xl font-bold">
                                    Daftar Pengguna
                                </h2>

                                <p class="text-sm text-base-content/60">
                                    Pengguna yang memiliki akses ke dashboard yayasan.
                                </p>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto mt-8">
                            <table id="table-list-account" class="min-w-full text-sm border-collapse">
                                <thead class="thead-table-list-account hidden bg-gray-50 shadow-inner">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-left w-[35%]">
                                            Pengguna
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Role
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Status Akses
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Status Akun
                                        </th>
                                    </tr>
                                </thead>

                                <tbody id="tbody-list-account">
                                    <!-- Ajax Render -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Empty Message -->
                        <div id="empty-message-list-account" class="hidden rounded-2xl border-2 border-dashed border-gray-300 bg-base-100 py-20">

                            <div class="mx-auto flex w-full max-w-md flex-col items-center text-center">
                                
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                                    <i class="fa-solid fa-users text-3xl text-primary"></i>
                                </div>

                                <h3 class="mt-6 text-xl font-semibold">
                                    Belum Ada Pengguna
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-base-content/60">
                                    Belum ada pengguna yang terdaftar pada yayasan ini. Tambahkan pengguna untuk memberikan akses ke yayasan.
                                </p>
                            </div>
                        </div>

                        <div class="pagination-container-list-account flex justify-center my-5"></div>
                    </div>
                </section>

                <!-- Modal Pilih Metode Tambah Pengguna -->
                <dialog id="modal-add-user-method" class="modal">
                    <div class="modal-box w-11/12 max-w-3xl max-h-[90vh] rounded-3xl p-0 overflow-hidden">

                        <!-- Header -->
                        <div class="bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-5 py-5 md:px-7 md:py-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                        <i class="fa-solid fa-user-plus text-xl text-white"></i>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-bold text-white">
                                            Tambah Pengguna
                                        </h2>

                                        <p class="mt-2 text-sm leading-6 text-white/85">
                                            Pilih metode untuk menambahkan pengguna ke dalam yayasan.
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button class="btn btn-circle btn-sm border-none bg-white/15 text-white hover:bg-white/25">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="max-h-[calc(90vh-110px)] overflow-y-auto p-5 md:p-7">

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                                <!-- Card Create -->
                                <button type="button" id="btn-create-foundation-user" class="group rounded-2xl border border-gray-300 p-6 text-left transition-all 
                                    duration-300 hover:-translate-y-1 hover:border-primary hover:shadow-lg cursor-pointer">

                                    <div class="flex items-center justify-between">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-primary/10 transition group-hover:bg-primary">
                                            <i class="fa-solid fa-user-plus text-xl text-primary group-hover:text-white"></i>
                                        </div>

                                        <i class="fa-solid fa-arrow-right text-gray-300 transition group-hover:translate-x-1 group-hover:text-primary"></i>
                                    </div>

                                    <h3 class="mt-5 text-base font-semibold">
                                        Buat Akun Baru
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-base-content/60">
                                        Daftarkan pengguna baru beserta informasi akun, kemudian akses yayasan akan diberikan.
                                    </p>
                                </button>

                                <!-- Card Existing -->
                                <button type="button" id="btn-assign-foundation-user" class="group rounded-2xl border border-gray-300 p-6 text-left transition-all 
                                    duration-300 hover:-translate-y-1 hover:border-primary hover:shadow-lg cursor-pointer">

                                    <div class="flex items-center justify-between">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100 transition group-hover:bg-emerald-500">
                                            <i class="fa-solid fa-link text-xl text-emerald-600 group-hover:text-white"></i>
                                        </div>

                                        <i class="fa-solid fa-arrow-right text-gray-300 transition group-hover:translate-x-1 group-hover:text-primary"></i>
                                    </div>

                                    <h3 class="mt-5 text-base font-semibold">
                                        Gunakan Akun yang Sudah Ada
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-base-content/60">
                                        Cari akun yang sudah tersedia kemudian berikan akses ke yayasan tanpa membuat akun baru.
                                    </p>
                                </button>

                            </div>

                            <!-- Information -->
                            <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                                <div class="flex items-start gap-4">
                                    <div class="mt-0.5">
                                        <i class="fa-solid fa-circle-info text-base text-primary"></i>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-semibold text-[#003456]">
                                            Informasi
                                        </h4>

                                        <p class="mt-2 text-sm leading-6 text-base-content/70">
                                            Pilih <strong>Buat Akun Baru</strong> apabila pengguna belum memiliki akun.
                                            Jika akun telah tersedia, Gunakan
                                            <strong>Akun yang Sudah Ada</strong>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>Tutup</button>
                    </form>

                </dialog>

                <!-- Modal Create Foundation User -->
                <dialog id="modal-create-foundation-user" class="modal">
                    <div class="modal-box w-[95%] max-w-3xl max-h-[90dvh] p-0 rounded-3xl overflow-hidden flex flex-col">

                        <!-- Header -->
                        <div class="shrink-0 border-b border-white/10 bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-4 py-4 sm:px-6 sm:py-5 text-white">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3 sm:gap-4">

                                    <div class="flex h-11 w-11 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                                        <i class="fa-solid fa-user-plus text-lg sm:text-2xl text-white"></i>
                                    </div>

                                    <div class="min-w-0">

                                        <h3 class="text-base sm:text-lg font-bold wrap-break-word">
                                            Buat Akun Baru
                                        </h3>

                                        <p class="mt-1 text-xs sm:text-sm leading-relaxed text-white/75">
                                            Buat akun baru dan akses ke yayasan akan diberikan secara otomatis.
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog" class="shrink-0">
                                    <button class="btn btn-circle btn-sm border-0 bg-white/10 text-white hover:bg-white/20">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>

                            </div>

                        </div>

                        <!-- Body -->
                        <div class="flex-1 flex flex-col overflow-hidden">

                            <!-- Foundation Information-->
                            <div class="shrink-0 border-b border-gray-300 bg-white px-4 py-4 sm:px-6 sm:py-5">
                                <div class="rounded-2xl border border-primary/20 bg-primary/5 p-4 sm:p-5">
                                    <div class="flex items-start gap-3 sm:gap-4">

                                        <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-primary text-white">
                                            <i class="fa-solid fa-building-columns"></i>
                                        </div>

                                        <div class="min-w-0">

                                            <p class="text-[11px] sm:text-xs uppercase tracking-wide text-primary font-semibold">
                                                Akses Yayasan
                                            </p>

                                            <h4 id="foundation-name" class="mt-1 text-sm sm:text-base font-semibold wrap-break-word">
                                                {{ $schoolFoundation->nama_yayasan ?? '-' }}
                                            </h4>

                                            <p class="mt-2 text-xs sm:text-sm leading-relaxed text-base-content/70">
                                                Akun yang dibuat akan langsung memperoleh akses ke yayasan ini.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form -->
                            <div class="flex-1 overflow-y-auto px-4 pb-6 sm:px-6">
                                <form id="school-foundation-create-user-form" autocomplete="OFF">
                                    <div class="pt-6 space-y-6">

                                        <!-- Nama Lengkap -->
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-semibold text-sm">
                                                    Nama Lengkap
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="w-full h-12 rounded-lg border border-gray-200 px-4 
                                                outline-none focus:border-primary text-xs" placeholder="Masukkan Nama Lengkap">

                                            <span id="error-nama_lengkap" class="text-xs font-semibold text-red-500"></span>
                                        </div>

                                        <!-- Nomor HP -->
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-semibold text-sm">
                                                    Nomor HP
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <input type="text" id="no_hp" name="no_hp" class="w-full h-12 rounded-lg border border-gray-200 px-4 outline-none 
                                                focus:border-primary text-xs" placeholder="Masukkan Nomor HP" oninput="this.value=this.value.replace(/[^0-9]/g,'')">

                                            <span id="error-no_hp" class="text-xs font-semibold text-red-500"></span>
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-semibold text-sm">
                                                    Email Akun
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <input type="email" id="email" name="email" class="w-full h-12 rounded-lg border border-gray-200 px-4 outline-none 
                                                focus:border-primary text-xs" placeholder="yayasan@belajarcerdas.id">

                                            <span id="error-email" class="text-xs font-semibold text-red-500"></span>
                                        </div>

                                        <!-- Password -->
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-semibold text-sm">
                                                    Password
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <div class="relative">

                                                <input type="password" id="password" name="password" class="w-full h-12 rounded-lg border border-gray-200 px-4 pr-12 
                                                    text-sm outline-none focus:border-primary" placeholder="Masukkan Password">

                                                <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 
                                                    text-gray-500 hover:text-primary">
                                                    <i class="fa-solid fa-eye-slash cursor-pointer"></i>
                                                </button>
                                            </div>

                                            <span id="error-password" class="text-xs font-semibold text-red-500"></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-4 sm:px-6 sm:py-5">
                            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <form method="dialog">
                                    <button class="btn btn-primary w-full sm:w-auto">
                                        Tutup
                                    </button>
                                </form>

                                <button type="button" id="btn-submit-create-foundation-user" class="btn w-full sm:w-auto border-none 
                                    bg-[#0071BC] text-white hover:bg-[#005f9f]">

                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Simpan Akun
                                </button>
                            </div>
                        </div>
                    </div>
                </dialog>

                <!-- Modal Existing User -->
                <dialog id="modal-assign-foundation-user" class="modal">

                    <div class="modal-box w-[95vw] max-w-4xl p-0 rounded-xl sm:rounded-2xl">

                        <!-- Header -->
                        <div class="bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] px-4 py-4 sm:px-6 sm:py-5 text-white">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">

                                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-white/15">
                                        <i class="fa-solid fa-user-check text-lg text-white"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-base sm:text-lg font-semibold">
                                            Gunakan Akun yang Sudah Ada
                                        </h3>

                                        <p class="mt-1 text-xs sm:text-sm leading-5 text-white/80">
                                            Pilih akun yayasan yang sudah tersedia untuk diberikan akses ke yayasan ini.
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button class="btn btn-circle btn-sm border-0 bg-white/10 text-white hover:bg-white/20">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="max-h-[75vh] overflow-y-auto">

                            <!-- Search -->
                            <div class="sticky top-0 z-20 border-b border-gray-300 bg-base-100 px-4 py-3 sm:px-6 sm:py-5">
                                <div class="rounded-xl sm:rounded-2xl border border-primary/10 bg-primary/5 p-3 sm:p-4">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <div class="flex-1">
                                            <label class="input input-bordered border-gray-300 flex items-center gap-2 w-full sm:max-w-sm outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                                </svg>

                                                <input id="search-existing-account" type="search" class="grow text-sm" placeholder="Cari Pengguna..." autocomplete="off">
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-start gap-2 text-xs text-base-content/70">
                                        <i class="fa-solid fa-circle-info mt-0.5 text-primary"></i>
                                        <span>Hanya akun yang belum memiliki akses ke yayasan ini yang akan ditampilkan.</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Result -->
                            <div id="existing-account-list" class="space-y-3 p-4 sm:space-y-4 sm:p-6">
                                <!-- Ajax Render -->
                            </div>

                            <!-- Empty -->
                            <div id="empty-existing-account" class="hidden px-8 pb-20">
                                <div class="mx-auto max-w-sm text-center">

                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-base-200">
                                        <i class="fa-solid fa-users-slash text-3xl text-gray-400"></i>
                                    </div>

                                    <h3 class="mt-5 text-lg font-semibold">
                                        Tidak Ada Akun
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-base-content/60">
                                        Tidak ada akun yang dapat ditambahkan ke yayasan. 
                                    </p>
                                </div>
                            </div>
                        </div>
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

<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/access-control/school-foundation-list-account.js') }}"></script> <!--- school foundation list account ---->
<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/access-control/school-foundation-add-user.js') }}"></script> <!--- school foundation add user ---->
<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/access-control/school-foundation-assign-user.js') }}"></script> <!--- school foundation assign user ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/show-password-input.js') }}"></script> <!--- show password input ---->

<script>
    document.querySelectorAll('#modal-create-foundation-user input').forEach(input => {

        input.addEventListener('input', function () {

            this.classList.remove('border-red-500');

            const error = document.getElementById(`error-${this.id}`);

            if (error) {
                error.textContent = '';
            }

        });

    });
</script>