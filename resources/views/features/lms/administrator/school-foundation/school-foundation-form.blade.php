@include('components/sidebar-beranda', [
    'headerSideNav' => 'Form Yayasan',
    'linkBackButton' => route('lms.schoolFoundation.manage.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-create-school-foundation"></div>

            <main id="container" data-role="{{ $role }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-6 lg:p-8 shadow-xl border border-gray-200">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-building-columns absolute -top-6 -right-6 text-[150px] text-white/5 rotate-12"></i>
                        <i class="fa-solid fa-school absolute -bottom-8 -left-6 text-[110px] text-white/5 -rotate-12"></i>

                        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT -->
                            <div>
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/15 backdrop-blur-sm shadow-lg">
                                        <i class="fa-solid fa-building-columns text-3xl text-white"></i>
                                    </div>

                                    <div class="inline-block">
                                        <h1 class="text-2xl font-bold text-white lg:text-3xl">
                                            Tambah Yayasan
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-white/85 lg:text-base">
                                    Lengkapi informasi yayasan beserta sekolah-sekolah yang akan berada di bawah naungan yayasan.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div class="flex w-full justify-start lg:w-auto lg:justify-end">
                                <a href="{{ route('lms.schoolFoundation.manage.view', [
                                    'role' => Auth::user()->role
                                ])}}"
                                    class="btn w-full lg:w-auto backdrop-blur-sm transition-all duration-300 
                                    border-white bg-white text-[#0071BC]">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FORM -->
                <form id="create-school-foundation-form" autocomplete="OFF">
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                        <!-- LEFT -->
                        <div class="xl:col-span-2 space-y-6">
                            <div class="card bg-base-100 border border-gray-200 shadow-sm">
                                <div class="card-body">

                                    <h2 class="card-title">
                                        <i class="fa-solid fa-building-columns text-primary"></i>
                                        Informasi Yayasan
                                    </h2>

                                    <div class="divider my-1"></div>

                                    <!-- Logo Yayasan -->
                                    <div>
                                        <label class="label">
                                            <span class="label-text font-semibold">Logo Yayasan</span>
                                        </label>

                                        <label for="logoInput" class="group relative flex min-h-65 cursor-pointer flex-col items-center justify-center 
                                            rounded-2xl border-2 border-dashed border-base-300 bg-base-100 transition-all duration-300 hover:border-[#0071BC] hover:bg-primary/5">

                                            <!-- Preview -->
                                            <img id="logoPreview" src="" alt="Preview Logo" class="hidden h-60 w-60 rounded-3xl border border-base-300 bg-white object-contain 
                                                shadow-xl">

                                            <!-- Placeholder -->
                                            <div id="logoPlaceholder" class="flex flex-col items-center text-center transition duration-300 group-hover:scale-105">

                                                <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                                                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-primary"></i>
                                                </div>

                                                <h3 class="text-lg font-semibold">
                                                    Klik untuk mengunggah logo
                                                </h3>

                                                <p class="mt-2 max-w-sm text-sm text-base-content/60">
                                                    Pilih logo yayasan dari perangkat Anda atau seret gambar ke area ini.
                                                </p>

                                            </div>

                                            <!-- Overlay ketika hover -->
                                            <div class="absolute inset-0 hidden items-center justify-center rounded-2xl bg-black/40 text-white opacity-0 transition-all 
                                                duration-300 group-hover:flex group-hover:opacity-100">

                                                <div class="rounded-xl bg-white/20 px-5 py-3 backdrop-blur-sm">
                                                    <i class="fa-solid fa-camera mr-2"></i>
                                                    Ganti Logo
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Keterangan -->
                                        <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-base-content/60">

                                            <div class="flex items-center gap-2">
                                                <i class="fa-regular fa-image text-primary"></i>
                                                JPG, PNG, SVG
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-hard-drive text-primary"></i>
                                                Maksimal 2 MB
                                            </div>
                                        </div>

                                        <input type="file" id="logoInput" name="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" class="hidden">
                                        <span id="error-logo" class="text-red-500 text-xs font-semibold"></span>
                                    </div>

                                    <!-- Nama Yayasan -->

                                    <div class="mt-4">

                                        <label class="label">
                                            <span class="label-text font-semibold">
                                                Nama Yayasan
                                                <sup class="text-red-500">&#42;</sup>
                                            </span>
                                        </label>

                                        <input type="text" id="nama_yayasan" name="nama_yayasan" class="w-full border border-gray-200 rounded-lg px-4 h-12 outline-none text-sm" 
                                            placeholder="Contoh: Yayasan Harapan Bangsa">
                                        <span id="error-nama_yayasan" class="text-red-500 text-xs font-semibold"></span>
                                    </div>
                                </div>

                                <!-- AKUN YAYASAN -->
                                <div class="card-body">

                                    <h2 class="card-title">
                                        <i class="fa-solid fa-user-shield text-primary"></i>
                                        Akun Yayasan
                                    </h2>

                                    <div class="divider my-1"></div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                        <!-- Nama Lengkap -->
                                        <div class="md:col-span-2">
                                            <label class="label">
                                                <span class="label-text font-semibold">
                                                    Nama Lengkap
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="w-full border border-gray-200 rounded-lg px-4 
                                                h-12 outline-none text-sm" placeholder="Masukkan Nama Lengkap">

                                            <span id="error-nama_lengkap" class="text-red-500 text-xs font-semibold"></span>
                                        </div>

                                        <!-- Nomor HP -->
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-semibold">
                                                    Nomor HP
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <input type="text" id="no_hp" name="no_hp" class="w-full border border-gray-200 rounded-lg px-4 h-12 
                                                outline-none text-sm" placeholder="Masukkan Nomor HP" oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                                            <span id="error-no_hp" class="text-red-500 text-xs font-semibold"></span>
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label class="label">
                                                <span class="label-text font-semibold">
                                                    Email Akun
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <input type="email" id="email" name="email" class="w-full border border-gray-200 rounded-lg px-4 
                                                h-12 outline-none text-sm" placeholder="yayasan@belajarcerdas.id">

                                            <span id="error-email" class="text-red-500 text-xs font-semibold"></span>
                                        </div>

                                        <!-- Password -->
                                        <div class="md:col-span-2">
                                            <label class="label">
                                                <span class="label-text font-semibold">
                                                    Password
                                                    <sup class="text-red-500">&#42;</sup>
                                                </span>
                                            </label>

                                            <div class="relative">
                                                <input type="password" id="password" name="password" class="w-full border border-gray-200 rounded-lg px-4 pr-12 h-12 
                                                    outline-none text-sm" placeholder="Masukkan Password">

                                                <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 
                                                    text-gray-500 hover:text-primary">

                                                    <i class="fa-solid fa-eye-slash cursor-pointer"></i>
                                                </button>
                                            </div>

                                            <span id="error-password" class="text-red-500 text-xs font-semibold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEKOLAH -->
                            <div class="card bg-base-100 border border-gray-200 shadow-sm">
                                <div class="card-body">

                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                                        <div>

                                            <h2 class="card-title">
                                                <i class="fa-solid fa-school text-primary"></i>
                                                Pilih Sekolah
                                                <sup class="text-red-500 relative right-1">&#42;</sup>
                                            </h2>

                                            <p class="text-base-content/60 text-sm">
                                                Pilih sekolah yang akan berada di bawah yayasan ini.
                                            </p>

                                        </div>

                                        <!-- Search -->
                                        <label class="input input-bordered border-gray-300 flex items-center gap-2 w-full lg:w-80">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                            </svg>

                                            <input id="search_school" type="search" class="grow text-sm" placeholder="Cari sekolah..." autocomplete="off">
                                        </label>
                                    </div>

                                    <div class="divider"></div>

                                    <span id="error-school_partner_id"
                                        class="text-red-500 text-xs font-semibold hidden">
                                    </span>

                                    <!-- SCROLL AREA -->
                                    <div class="max-h-137.5 overflow-y-auto pr-2">

                                        <!-- Skeleton -->
                                        <div
                                            id="school-list-skeleton"
                                            class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-4">

                                            @for ($i = 0; $i < 6; $i++)
                                                <div class="rounded-2xl border border-base-300 p-5">
                                                    <div class="animate-pulse">
                                                        <div class="flex gap-4">
                                                            <div class="h-16 w-16 rounded-2xl bg-base-300"></div>

                                                            <div class="flex-1">
                                                                <div class="h-4 w-2/3 rounded bg-base-300"></div>

                                                                <div class="mt-3 flex gap-2">
                                                                    <div class="h-5 w-14 rounded-full bg-base-300"></div>
                                                                    <div class="h-5 w-24 rounded-full bg-base-300"></div>
                                                                </div>

                                                                <div class="mt-5 flex gap-4">
                                                                    <div class="h-4 w-20 rounded bg-base-300"></div>
                                                                    <div class="h-4 w-20 rounded bg-base-300"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endfor

                                        </div>

                                        <div id="container-grid-school-list" class="hidden">
                                            <div id="grid-school-list" class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-4">
                                                <!-- AJAX -->
                                            </div>
                                        </div>

                                        <!-- Empty -->
                                        <div id="empty-message-school-list" class="hidden px-6 py-16">
                                            <div class="mx-auto max-w-md text-center">
                                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100">
                                                    <i class="fa-solid fa-school-circle-xmark text-3xl text-slate-400"></i>
                                                </div>

                                                <h3 class="mt-6 text-lg font-semibold text-slate-800">
                                                    Belum Ada Sekolah yang Tersedia
                                                </h3>

                                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                                    Saat ini belum terdapat sekolah yang dapat dipilih untuk dinaungi oleh yayasan.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT-->
                        <div>
                            <div class="card bg-base-100 shadow-sm sticky top-5 border border-gray-200">
                                <div class="card-body">

                                    <h2 class="card-title">
                                        <i class="fa-solid fa-list-check text-primary"></i>
                                        Ringkasan
                                    </h2>

                                    <div class="divider my-1"></div>

                                    <div class="space-y-5">

                                        <div>
                                            <p class="text-sm text-base-content/60">
                                                Nama Yayasan
                                            </p>

                                            <p id="summary-foundation-name" class="font-semibold">
                                                Belum diisi
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-base-content/60">
                                                Sekolah Dipilih
                                            </p>

                                            <p id="summary-school-count" class="font-semibold text-primary">
                                                0 Sekolah
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-base-content/60 mb-2">
                                                Daftar Sekolah
                                            </p>

                                            <div id="summary-school-list" class="space-y-2">

                                                <div class="text-base-content/50 text-sm">
                                                    Belum ada sekolah dipilih.
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="divider"></div>

                                    <div class="flex flex-col gap-3">

                                        <button type="button" id="submit-button-create-school-foundation" class="w-full md:w-auto px-6 py-2 rounded-lg
                                            border border-gray-300 bg-[#0071BC] text-white transition whitespace-nowrap cursor-pointer disabled:cursor-default">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                            Simpan Yayasan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/form/paginate-school-list.js') }}"></script> <!--- school list ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/show-password-input.js') }}"></script> <!--- show password input ---->

<script>
    const input = document.getElementById('logoInput');
    const preview = document.getElementById('logoPreview');
    const placeholder = document.getElementById('logoPlaceholder');

    input.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {

            preview.src = e.target.result;
            preview.classList.remove('hidden');

            placeholder.classList.add('hidden');

        }

        reader.readAsDataURL(file);

    });
</script>

<script>
    document.querySelectorAll('#create-school-foundation-form input').forEach(input => {

        input.addEventListener('input', function () {

            this.classList.remove('border-red-500');

            const error = document.getElementById(`error-${this.id}`);

            if (error) {
                error.textContent = '';
            }

        });

    });
</script>