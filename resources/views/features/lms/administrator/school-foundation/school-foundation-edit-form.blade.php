@include('components/sidebar-beranda', [
    'headerSideNav' => 'Edit Yayasan',
    'linkBackButton' => route('lms.schoolFoundation.manage.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-edit-school-foundation"></div>

            <main id="container" data-role="{{ $role }}" data-school-foundation-id="{{ $schoolFoundationId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-6 lg:p-8 shadow-xl border border-gray-200">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-building-columns absolute -top-6 -right-6 text-[150px] text-white/5 rotate-12"></i>
                        <i class="fa-solid fa-pen-to-square absolute -bottom-8 -left-6 text-[110px] text-white/5 -rotate-12"></i>

                        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT -->
                            <div>
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/15 backdrop-blur-sm shadow-lg">
                                        <i class="fa-solid fa-pen-to-square text-3xl text-white"></i>
                                    </div>

                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold text-white">
                                            Edit Yayasan
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-white/85 lg:text-base">
                                    Perbarui identitas yayasan seperti nama dan logo agar.
                                </p>

                            </div>

                            <!-- RIGHT -->
                            <div class="flex w-full justify-start lg:w-auto lg:justify-end">
                                <a href="{{ route('lms.schoolFoundation.manage.view', [
                                        'role' => Auth::user()->role
                                    ]) }}"
                                    class="btn w-full lg:w-auto backdrop-blur-sm transition-all duration-300
                                    border-white bg-white text-[#0071BC] hover:bg-slate-100">

                                    <i class="fa-solid fa-arrow-left"></i>
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FORM -->
                <form id="edit-school-foundation-form" autocomplete="OFF">
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                        <!-- LEFT - Skeleton -->
                        <div id="school-foundation-form-loading" class="xl:col-span-2 space-y-6">
                            <div class="card bg-base-100 border border-gray-200 shadow-sm">
                                <div class="card-body animate-pulse">

                                    <!-- Header -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded bg-base-300"></div>

                                        <div class="h-6 w-52 rounded bg-base-300"></div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="divider my-1"></div>

                                    <!-- Upload Logo -->
                                    <div>

                                        <!-- Label -->
                                        <div class="h-4 w-36 rounded bg-base-300 mb-3"></div>

                                        <!-- Upload Area -->
                                        <div
                                            class="flex min-h-65 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-base-300 bg-base-100">

                                            <!-- Fake Preview -->
                                            <div class="w-32 h-32 rounded-2xl bg-base-300"></div>

                                            <!-- Text -->
                                            <div class="h-5 w-48 rounded bg-base-300 mt-6"></div>

                                            <div class="h-3 w-72 rounded bg-base-300 mt-3"></div>

                                            <div class="h-3 w-56 rounded bg-base-300 mt-2"></div>

                                        </div>

                                        <!-- File Info -->
                                        <div class="mt-4 flex gap-6">

                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 rounded bg-base-300"></div>
                                                <div class="w-24 h-3 rounded bg-base-300"></div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <div class="w-4 h-4 rounded bg-base-300"></div>
                                                <div class="w-28 h-3 rounded bg-base-300"></div>
                                            </div>

                                        </div>

                                    </div>

                                    <!-- Nama Yayasan -->
                                    <div class="mt-6">

                                        <!-- Label -->
                                        <div class="h-4 w-40 rounded bg-base-300 mb-3"></div>

                                        <!-- Input -->
                                        <div class="h-12 w-full rounded-lg bg-base-300"></div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- LEFT -->
                        <div id="school-foundation-form-content" class="xl:col-span-2 space-y-6 hidden">
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
                            </div>
                        </div>

                        <!-- RIGHT - Skeleton -->
                        <div id="school-foundation-summary-loading">
                            <div class="card bg-base-100 shadow-sm sticky top-5 border border-gray-200">
                                <div class="card-body animate-pulse">

                                    <!-- Header -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded bg-base-300"></div>
                                        <div class="h-6 w-32 rounded bg-base-300"></div>
                                    </div>

                                    <div class="divider my-1"></div>

                                    <!-- Nama Yayasan -->
                                    <div class="space-y-2">
                                        <div class="h-3 w-28 rounded bg-base-300"></div>
                                        <div class="h-5 w-48 rounded bg-base-300"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <!-- Tombol -->
                                    <div class="h-11 w-full rounded-lg bg-base-300"></div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT CONTENT-->
                        <div id="school-foundation-summary-content" class="hidden">
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

<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/form/edit-school-foundation.js') }}"></script> <!--- edit-school-foundation ---->

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