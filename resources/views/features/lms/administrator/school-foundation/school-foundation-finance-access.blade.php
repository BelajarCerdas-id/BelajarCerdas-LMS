@include('components/sidebar-beranda', [
    'headerSideNav' => 'Link Keuangan',
    'linkBackButton' => route('lms.schoolFoundation.manage.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);


@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-create-finance-access-link"></div>
            <div id="alert-success-edit-finance-access-link"></div>

            <main id="container" data-role="{{ $role }}" data-school-foundation-id="{{ $schoolFoundationId }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-6 lg:p-8 shadow-xl border border-gray-200">

                        <!-- Background Decoration -->
                        <i class="fa-solid fa-folder-open absolute -top-6 -right-6 text-[150px] text-white/5 rotate-12"></i>
                        <i class="fa-solid fa-file-invoice-dollar absolute -bottom-8 -left-6 text-[110px] text-white/5 -rotate-12"></i>

                        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT -->
                            <div>
                                <div class="flex items-center gap-4">

                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/15 backdrop-blur-sm shadow-lg">
                                        <i class="fa-solid fa-folder-open text-3xl text-white"></i>
                                    </div>

                                    <div class="inline-block">

                                        <h1 class="text-xl font-bold text-white">
                                            Kelola Link Keuangan
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>

                                    </div>

                                </div>

                                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-white/85 lg:text-base">
                                    Kelola link dokumen keuangan untuk setiap sekolah yang
                                    terhubung dengan yayasan serta atur hak aksesnya.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div class="flex w-full justify-start lg:w-auto lg:justify-end">
                                <a
                                    href="{{ route('lms.schoolFoundation.manage.view', [
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

                <!-- LINK KEUANGAN SEKOLAH -->
                <section class="mb-6">

                    <!-- Header -->
                    <div class="mb-5 flex justify-end">
                        <button type="button" onclick="my_modal_1.showModal()" class="btn btn-sm w-full border-none bg-[#0071BC] text-white hover:bg-[#005f9f] sm:w-auto">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Link
                        </button>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-base-100 shadow-sm">
                        
                        <!-- TABLE LOADING -->
                        <div id="table-loading" class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="border border-gray-300 px-2 py-3 text-xs font-semibold text-center">
                                            No
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-left w-[45%]">
                                            Sekolah
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Link Keuangan
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Status Akses
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @for ($i = 0; $i < 4; $i++)
                                        <tr>
                                            <td class="border border-gray-300 px-3 py-4 text-center">
                                                <div class="skeleton mx-auto h-5 w-6"></div>
                                            </td>

                                            <td class="border border-gray-300 px-4 py-4">
                                                <div class="skeleton h-3 w-32"></div>
                                            </td>

                                            <td class="border border-gray-300 px-4 py-4 text-center">
                                                <div class="skeleton mx-auto h-5 w-14"></div>
                                            </td>

                                            <td class="border border-gray-300 px-4 py-4 text-center">
                                                <div class="skeleton mx-auto h-5 w-16"></div>
                                            </td>

                                            <td class="border border-gray-300 px-4 py-4 text-center">
                                                <div class="skeleton mx-auto h-5 w-16"></div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- TABLE CONTENT -->
                        <div id="table-content" class="overflow-x-auto hidden">
                            <table id="table-school-foundation-finance-access-link" class="min-w-full text-sm border-collapse">
                                <thead class="bg-gray-50 shadow-inner">
                                    <tr>
                                        <th class="border border-gray-300 px-2 py-3 text-xs font-semibold text-center">
                                            No
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-left w-[45%]">
                                            Sekolah
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Link Keuangan
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Status Akses
                                        </th>

                                        <th class="border border-gray-300 px-4 py-3 text-xs font-semibold text-center w-[15%]">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody id="tbody-school-foundation-finance-access-link">
                                    <!-- Ajax -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty Message -->
                        <div id="empty-message-school-foundation-finance-access-link" class="hidden rounded-2xl border-2 border-dashed border-gray-300 bg-base-100 py-20">
                            <div class="mx-auto flex w-full max-w-md flex-col items-center text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                                    <i class="fa-solid fa-folder-open text-2xl text-slate-400"></i>
                                </div>

                                <h3 class="mt-4 text-sm font-semibold text-slate-800">
                                    Belum Ada Link Keuangan
                                </h3>

                                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-base-content/60">
                                    Belum ada sekolah yang memiliki link dokumen keuangan.
                                    Tambahkan link keuangan untuk mulai mengelola akses dokumen setiap sekolah.
                                </p>

                                <button id="btn-empty-create-finance-access" type="button" class="btn btn-sm mt-5 border-none bg-[#0071BC] text-white hover:bg-[#005f9f]"
                                    onclick="my_modal_1.showModal()">
                                    <i class="fa-solid fa-plus"></i>
                                    Tambah Link Keuangan
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- MODAL TAMBAHLINK KEUANGAN -->
                <dialog id="my_modal_1" class="modal">

                    <div class="modal-box w-11/12 max-w-2xl overflow-hidden p-0">

                        <!-- HEADER -->
                        <div class="border-b border-gray-200 bg-base-100 px-5 py-4 sm:px-6">

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                        <i class="fa-solid fa-folder-open text-lg text-emerald-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 id="finance-access-modal-title"
                                            class="text-base font-semibold text-slate-800">
                                            Tambah Link Keuangan
                                        </h3>

                                        <p class="mt-1 text-xs text-base-content/60">
                                            Kelola link dokumen keuangan sekolah.
                                        </p>
                                    </div>

                                </div>

                                <button type="button" class="btn btn-sm btn-circle btn-ghost shrink-0" onclick="my_modal_1.close()">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <!-- FORM -->
                        <form id="create-finance-access-link-form">

                            <!-- SCROLLABLE CONTENT -->
                            <div class="max-h-[calc(100vh-180px)] overflow-y-auto px-5 py-5 sm:px-6">

                                <!-- INFORMATION -->
                                <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 p-4">

                                    <div class="flex items-start gap-3">

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                            <i class="fa-solid fa-circle-info text-sm text-blue-600"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-800">
                                                Informasi akses
                                            </p>

                                            <p class="mt-1 text-xs leading-relaxed text-slate-600">
                                                Masukkan link folder Google Drive yang digunakan untuk menyimpan dokumen keuangan sekolah.
                                                Yayasan hanya dapat mengakses link selama status akses diaktifkan.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEKOLAH -->
                                <div class="my-5">
                                    <label for="finance-school-id" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Sekolah
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>

                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-11 items-center justify-center">
                                            <i class="fa-solid fa-school text-slate-400"></i>
                                        </div>

                                        <select id="finance-school-id" name="school_partner_id" class="select select-bordered w-full border-slate-300 bg-slate-50 
                                            pl-11 text-sm transition-all focus:border-[#4189E0] focus:bg-white focus:outline-none focus:ring-2 
                                            focus:ring-primary/10 cursor-pointer">

                                            <option value="" class="hidden">Pilih sekolah</option>

                                            @foreach ($schools as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->nama_sekolah }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <span id="error-school_partner_id" class="mt-1 block text-xs font-bold text-red-500"></span>
                                </div>

                                <!-- LINK -->
                                <div>
                                    <label for="finance-access-link" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Link Google Drive
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>

                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-11 items-center justify-center">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50">
                                                <i class="fa-brands fa-google-drive text-sm text-emerald-600"></i>
                                            </div>
                                        </div>

                                        <input id="finance-access-link" name="link" type="url" class="input input-bordered w-full border-slate-300 bg-slate-50 pl-11 
                                            text-sm transition-all focus:border-[#4189E0] focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/10" 
                                            placeholder="https://drive.google.com/..." autocomplete="off">
                                    </div>

                                    <span id="error-link" class="mt-1 block text-xs font-bold text-red-500"></span>

                                    <p class="mt-2 flex items-start gap-1.5 text-xs leading-relaxed text-slate-500">
                                        <i class="fa-solid fa-circle-info mt-0.5 text-[10px]"></i>

                                        <span>
                                            Pastikan link folder sudah memiliki izin berbagi yang sesuai agar dapat dibuka oleh pengguna yang diberikan akses.
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="border-t border-gray-200 bg-base-100 px-5 py-4 sm:px-6">
                                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-300bg-slate-100 text-sm 
                                        font-medium text-slate-600 hover:bg-slate-200 hover:text-slate-700 active:bg-slate-300 transition-all duration-200 
                                        cursor-pointer" onclick="my_modal_1.close()">
                                        Tutup
                                    </button>

                                    <button id="btn-create-finance-access-link" type="button" class="btn w-full border-none bg-[#0071BC] text-white hover:bg-[#005f9f] sm:w-auto
                                        cursor-pointer disabled:cursor-not-default">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Link
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- BACKDROP -->
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!-- MODAL EDIT LINK KEUANGAN -->
                <dialog id="my_modal_2" class="modal">
                    <div class="modal-box w-11/12 max-w-lg p-0 overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                            <div>
                                <h3 class="text-base font-semibold text-slate-800">
                                    Edit Link Keuangan
                                </h3>

                                <p class="mt-1 text-xs text-slate-500">
                                    Perbarui link dokumen keuangan sekolah.
                                </p>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-circle btn-ghost shrink-0" onclick="my_modal_2.close()">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <form id="edit-finance-access-link-form">
                            <input type="hidden" name="school_partner_id" id="edit-finance-school-partner-id">
                            <input type="hidden" name="link_id" id="edit-finance-access-link-id">

                            <div class="px-6 py-5 space-y-5">

                                <!-- Sekolah -->
                                <div>
                                    <label class="block mb-2 text-xs font-semibold text-slate-600">
                                        Sekolah
                                    </label>

                                    <div class="flex items-center gap-3 px-3 py-3
                                                rounded-lg bg-slate-50 border border-slate-200">

                                        <div class="flex items-center justify-center
                                                    w-9 h-9 rounded-lg bg-blue-50 text-blue-600">
                                            <i class="fa-solid fa-school"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                id="edit-finance-school-name"
                                                class="text-sm font-medium text-slate-700 truncate">
                                                -
                                            </p>

                                            <p class="text-[11px] text-slate-400">
                                                Link keuangan sekolah
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Link Keuangan -->
                                <div>
                                    <label for="edit-finance-link" class="mb-2 block text-sm font-semibold text-slate-700">
                                        Link Google Drive
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>

                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-11 items-center justify-center">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50">
                                                <i class="fa-brands fa-google-drive text-sm text-emerald-600"></i>
                                            </div>
                                        </div>

                                        <input id="edit-finance-link" name="edit_link" type="url" class="input input-bordered w-full border-slate-300 bg-slate-50 pl-11 
                                            text-sm transition-all focus:border-[#4189E0] focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/10" 
                                            placeholder="https://drive.google.com/..." autocomplete="off">
                                    </div>

                                    <span id="error-edit_link" class="mt-1 block text-xs font-bold text-red-500"></span>

                                    <p class="mt-2 flex items-start gap-1.5 text-xs leading-relaxed text-slate-500">
                                        <i class="fa-solid fa-circle-info mt-0.5 text-[10px]"></i>

                                        <span>
                                            Pastikan link folder sudah memiliki izin berbagi yang sesuai agar dapat dibuka oleh pengguna yang diberikan akses.
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="border-t border-gray-200 bg-base-100 px-5 py-4 sm:px-6">
                                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-300bg-slate-100 text-sm 
                                        font-medium text-slate-600 hover:bg-slate-200 hover:text-slate-700 active:bg-slate-300 transition-all duration-200 
                                        cursor-pointer" onclick="my_modal_2.close()">
                                        <span>Tutup</span>
                                    </button>

                                    <button id="submit-button-edit-finance-access-link" type="button" class="btn w-full border-none bg-[#0071BC] text-white 
                                        hover:bg-[#005f9f] sm:w-auto cursor-pointer disabled:cursor-not-default">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
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

<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/finance-access/school-foundation-finance-access-form.js') }}"></script> <!--- school foundation finance access form ---->
<script src="{{ asset('assets/js/features/lms/administrator/school-foundation/management/finance-access/school-foundation-finance-access-list.js') }}"></script> <!--- school foundation finance access list ---->