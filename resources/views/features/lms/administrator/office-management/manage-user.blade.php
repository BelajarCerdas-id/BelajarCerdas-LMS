@include('components.sidebar-beranda', [
    'headerSideNav' => 'Manage Office User',
])

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT SUCCESS -->
            <div id="alert-success-insert-data-manage-user"></div>
            <div id="alert-success-edit-data-manage-user"></div>

            <main>
                <section id="container" data-role="{{ $role }}" class="bg-white shadow-lg p-6 rounded-2xl border border-gray-300">

                    <!-- Header -->
                    <div class="flex flex-col gap-5">

                        <!-- TITLE -->
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">
                                Manajemen User Office
                            </h1>

                            <p class="text-sm text-gray-500 mt-1 leading-relaxed">
                                Kelola data user office, status akun, dan permission akses sistem.
                            </p>
                        </div>

                        <!-- ACTION -->
                        <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">

                            <!--- search bar --->
                            <div class="">
                                <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                    </svg>
                                    <input id="search_account" type="search" class="grow text-sm" placeholder="Cari Nama Pengguna..." autocomplete="OFF" />
                                </label>
                            </div>
                            
                            <!-- Button -->
                            <button onclick="my_modal_create.showModal()" class="h-11 px-5 rounded-2xl bg-[#4189e0] hover:bg-blue-500 
                                text-white text-sm font-semibold transition-all cursor-pointer w-full sm:w-auto shrink-0">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Tambah Pengguna
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto mt-8">
                        <table class="min-w-full text-sm border-separate border-spacing-y-3">
                            <thead class="thead-table-manage-user hidden">
                                <tr class="text-gray-500">
                                    <th class="text-center px-4">
                                        No
                                    </th>
                                    <th class="text-left px-4">
                                        Pengguna
                                    </th>
                                    <th class="text-center px-4">
                                        No.HP
                                    </th>
                                    <th class="text-center px-4">
                                        Role
                                    </th>
                                    <th class="text-center px-4">
                                        Status Akun
                                    </th>
                                    <th class="text-center px-4">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="table-list-manage-user">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-manage-user flex justify-center my-4 sm:my-0"></div>

                    <!-- EMPTY MESSAGE -->
                    <div id="empty-message-manage-user" class="hidden py-16">

                    <div class="mx-auto bg-linear-to-br from-gray-50 to-gray-100 border-2 border-dashed border-gray-300 rounded-3xl p-10">

                            <div class="flex flex-col items-center justify-center text-center">

                                <!-- icon -->
                                <div
                                    class="w-24 h-24 rounded-full bg-white shadow-md flex items-center justify-center">

                                    <i class="fa-solid fa-users text-4xl text-blue-400"></i>

                                </div>

                                <!-- title -->
                                <h2 class="text-2xl font-bold text-gray-700 mt-6">
                                    Belum Ada User Office
                                </h2>

                                <!-- subtitle -->
                                <p class="text-sm text-gray-500 mt-3 max-w-lg leading-relaxed">
                                    Data user office masih kosong.
                                    Tambahkan user baru untuk mulai mengatur akun dan hak akses office management.
                                </p>

                                <!-- action -->
                                <button onclick="my_modal_create.showModal()"
                                    class="mt-7 h-11 px-6 rounded-xl bg-[#4189e0] hover:bg-blue-500 text-white text-sm font-semibold transition-all shadow-md
                                    cursor-pointer">

                                    <i class="fa-solid fa-plus mr-2"></i>
                                    Tambah Pengguna

                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- LOADING SCREEN -->
                    <div id="loading-manage-user" class="py-16">
                        <div class="mx-auto bg-linear-to-br from-blue-50 to-white border border-blue-100 rounded-3xl p-10 shadow-sm">
                            <div class="flex flex-col items-center justify-center text-center">

                                <!-- loading animation -->
                                <div class="relative">

                                    <!-- outer pulse -->
                                    <div class="w-20 h-20 rounded-full bg-blue-100 animate-pulse"></div>

                                    <!-- inner -->
                                    <div
                                        class="absolute inset-0 flex items-center justify-center">

                                        <div
                                            class="w-14 h-14 rounded-full border-4 border-blue-200 border-t-[#4189e0] animate-spin">
                                        </div>
                                    </div>
                                </div>

                                <!-- title -->
                                <h2 class="text-2xl font-bold text-gray-700 mt-7">
                                    Memuat Data User
                                </h2>

                                <!-- subtitle -->
                                <p class="text-sm text-gray-500 mt-3 max-w-md leading-relaxed">
                                    Mohon tunggu sebentar, sistem sedang mengambil data user office dan menyiapkan tampilan.
                                </p>

                                <!-- skeleton info -->
                                <div class="mt-8 w-full max-w-lg space-y-3">

                                    <div class="h-4 rounded-full bg-gray-200 animate-pulse"></div>

                                    <div class="h-4 rounded-full bg-gray-200 animate-pulse w-11/12 mx-auto"></div>

                                    <div class="h-4 rounded-full bg-gray-200 animate-pulse w-10/12 mx-auto"></div>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <dialog id="my_modal_create" class="modal">

                <div class="modal-box bg-white w-11/12 max-w-2xl p-0 rounded-3xl overflow-hidden h-auto max-h-[92vh] flex flex-col">

                    <!-- HEADER -->
                    <div class="shrink-0 bg-linear-to-r from-[#1d4ed8] via-[#2563eb] to-[#3b82f6] px-5 md:px-8 py-6 md:py-7 relative overflow-hidden">

                        <!-- close -->
                        <form method="dialog">
                            <button
                                class="absolute right-4 top-4 md:right-5 md:top-5 w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 text-white transition-all 
                                z-10 cursor-pointer flex items-center justify-center">

                                <i class="fa-solid fa-xmark"></i>

                            </button>
                        </form>

                        <div class="relative z-9 flex items-start sm:items-center gap-4 pr-10">

                            <!-- icon -->
                            <div
                                class="w-13 h-13 md:w-15 md:h-15 shrink-0 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">

                                <i class="fa-solid fa-user-plus text-white text-xl md:text-2xl"></i>

                            </div>

                            <!-- title -->
                            <div>

                                <h3 class="text-xl md:text-2xl font-bold text-white leading-tight">
                                    Tambah User Office
                                </h3>

                                <p class="text-blue-100 text-xs md:text-sm mt-1 leading-relaxed max-w-md">
                                    Tambahkan akun baru untuk staff office dan atur hak akses dashboard management.
                                </p>

                            </div>

                        </div>

                    </div>

                    <!-- SCROLLABLE BODY -->
                    <div class="overflow-y-auto px-5 md:px-8 py-6 md:py-7">

                        <form id="create-manage-user-form" class="space-y-7" autocomplete="OFF">

                            <!-- INFORMASI AKUN -->
                            <div>

                                <div class="flex items-center gap-2 mb-5">

                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-circle-info text-[#2563eb] text-sm"></i>
                                    </div>

                                    <div>

                                        <h4 class="font-semibold text-gray-800">
                                            Informasi Akun
                                        </h4>

                                        <p class="text-xs text-gray-400 mt-0.5">
                                            Lengkapi data user office di bawah ini.
                                        </p>

                                    </div>

                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                    <!-- NAMA LENGKAP -->
                                    <div class="md:col-span-2">

                                        <label class="text-sm font-semibold text-gray-700">
                                            Nama Lengkap
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-user absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input type="text" name="nama_lengkap"
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-[#2563eb] focus:ring-4 focus:ring-blue-100 transition-all"
                                                placeholder="Masukkan nama lengkap">
                                            <span id="error-nama_lengkap" class="text-red-500 font-bold text-xs pt-2"></span>
                                        </div>

                                    </div>

                                    <!-- Email Akun -->
                                    <div>

                                        <label class="text-sm font-semibold text-gray-700">
                                            Email Akun
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-at absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input type="text" name="email"
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-8 outline-none text-sm
                                                focus:border-[#2563eb] focus:ring-4 focus:ring-blue-100 transition-all"
                                                placeholder="Masukkan email akun">
                                            <span id="error-email" class="text-red-500 font-bold text-xs pt-2"></span>
                                        </div>

                                    </div>

                                    <!-- NOMOR HP -->
                                    <div>

                                        <label class="text-sm font-semibold text-gray-700">
                                            No.HP
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-phone absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input type="text" name="no_hp"
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-[#2563eb] focus:ring-4 focus:ring-blue-100 transition-all"
                                                placeholder="Masukkan nomor telepon"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                            <span id="error-no_hp" class="text-red-500 font-bold text-xs pt-2"></span>
                                        </div>

                                    </div>

                                    <!-- PASSWORD -->
                                    <div>

                                        <label class="text-sm font-semibold text-gray-700">
                                            Password
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-lock absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input id="passwordInput" type="password" name="password"
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-12 outline-none 
                                                text-sm focus:border-[#2563eb] focus:ring-4 focus:ring-blue-100 transition-all"
                                                placeholder="Masukkan password"
                                                maxlength="16">
                                            <span id="error-password" class="text-red-500 font-bold text-xs pt-2"></span>
                                            <button type="button"
                                                onclick="togglePassword('passwordInput', this)"
                                                class="absolute right-4 top-4.75 text-gray-600 focus:outline-none">

                                                <i class="fa-solid fa-eye-slash cursor-pointer"></i>

                                            </button>
                                        </div>
                                    </div>

                                    <!-- ROLE -->
                                    <div>

                                        <label class="text-sm font-semibold text-gray-700">
                                            Role
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-user-shield absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <select id="role" name="role"
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-[#2563eb] focus:ring-4 focus:ring-blue-100 transition-all appearance-none cursor-pointer">

                                                <option value="" class="hidden">
                                                    Pilih Role
                                                </option>

                                                @foreach (config('office-account.roles') as $role)
                                                    <option value="{{ $role }}">
                                                        {{ $role }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span id="error-role" class="text-red-500 font-bold text-xs pt-2"></span>
                                            <i
                                                class="fa-solid fa-chevron-down absolute right-4 top-4.75 text-gray-400 text-xs">
                                            </i>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ACTION -->
                            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-100">
                                <button id="submit-button-create-manage-user" type="button"
                                    class="h-12 w-full sm:w-auto px-7 rounded-2xl bg-linear-to-r from-[#2563eb] to-[#3b82f6]
                                    hover:from-[#1d4ed8] hover:to-[#2563eb]
                                    text-white text-sm font-semibold transition-all shadow-lg hover:shadow-xl cursor-pointer disabled:cursor-default">

                                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                                    Simpan User

                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <form method="dialog" class="modal-backdrop">
                    <button>Close</button>
                </form>
            </dialog>

            <!-- MODAL EDIT USER -->
            <dialog id="my_modal_edit" class="modal">

                <div class="modal-box bg-white w-11/12 max-w-2xl p-0 rounded-3xl overflow-hidden h-auto max-h-[92vh] flex flex-col">

                    <!-- HEADER -->
                    <div class="shrink-0 bg-linear-to-r from-orange-600 via-orange-500 to-amber-500 px-5 md:px-8 py-6 md:py-7 relative overflow-hidden">

                        <!-- close -->
                        <form method="dialog">
                            <button
                                class="absolute right-4 top-4 md:right-5 md:top-5 w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 text-white transition-all 
                                z-10 cursor-pointer flex items-center justify-center">

                                <i class="fa-solid fa-xmark"></i>

                            </button>
                        </form>

                        <div class="relative z-9 flex items-start sm:items-center gap-4 pr-10">

                            <!-- icon -->
                            <div
                                class="w-13 h-13 md:w-15 md:h-15 shrink-0 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">

                                <i class="fa-solid fa-user-pen text-white text-xl md:text-2xl"></i>

                            </div>

                            <!-- title -->
                            <div>

                                <h3 class="text-xl md:text-2xl font-bold text-white leading-tight">
                                    Edit User Office
                                </h3>

                                <p class="text-orange-100 text-xs md:text-sm mt-1 leading-relaxed max-w-md">
                                    Perbarui data user office dan atur akses dashboard management.
                                </p>

                            </div>
                        </div>
                    </div>

                    <!-- SCROLLABLE BODY -->
                    <div class="overflow-y-auto px-5 md:px-8 py-6 md:py-7">

                        <form id="edit-manage-user-form" class="space-y-7" autocomplete="OFF">

                            <input type="text" id="edit-user_account_id" name="user_account_id" class="hidden">

                            <!-- INFORMASI AKUN -->
                            <div>
                                <div class="flex items-center gap-2 mb-5">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                        <i class="fa-solid fa-circle-info text-orange-500 text-sm"></i>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-gray-800">
                                            Informasi Akun
                                        </h4>

                                        <p class="text-xs text-gray-400 mt-0.5">
                                            Perbarui data user office di bawah ini.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                    <!-- NAMA LENGKAP -->
                                    <div class="md:col-span-2">

                                        <label class="text-sm font-semibold text-gray-700">
                                            Nama Lengkap
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-user absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input type="text" id="edit-nama_lengkap" name="nama_lengkap" value=""
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all"
                                                placeholder="Masukkan nama lengkap">

                                            <span id="error-edit-nama_lengkap"
                                                class="text-red-500 font-bold text-xs pt-2">
                                            </span>

                                        </div>

                                    </div>

                                    <!-- EMAIL AKUN -->
                                    <div>

                                        <label class="text-sm font-semibold text-gray-700">
                                            Email Akun
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-at absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input type="text" id="edit-email" name="email" value=""
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all"
                                                placeholder="Masukkan email akun">

                                            <span id="error-edit-email"
                                                class="text-red-500 font-bold text-xs pt-2">
                                            </span>

                                        </div>

                                    </div>

                                    <!-- NOMOR HP -->
                                    <div>

                                        <label class="text-sm font-semibold text-gray-700">
                                            No.HP
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-phone absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <input type="text" id="edit-no_hp" name="no_hp" value=""
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all"
                                                placeholder="Masukkan nomor telepon"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                                            <span id="error-edit-no_hp"
                                                class="text-red-500 font-bold text-xs pt-2">
                                            </span>

                                        </div>

                                    </div>

                                    <!-- ROLE -->
                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">
                                            Role
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>

                                        <div class="relative mt-2">

                                            <i
                                                class="fa-solid fa-user-shield absolute left-4 top-4.75 text-gray-400 text-sm">
                                            </i>

                                            <select id="edit-role" name="role"
                                                class="w-full h-13 rounded-2xl border border-gray-200 pl-11 pr-4 outline-none text-sm
                                                focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition-all appearance-none cursor-pointer">

                                                <option value="" class="hidden">
                                                    Pilih Role
                                                </option>

                                                @foreach (config('office-account.roles') as $role)
                                                    <option value="{{ $role }}">
                                                        {{ $role }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <span id="error-edit-role"
                                                class="text-red-500 font-bold text-xs pt-2">
                                            </span>

                                            <i
                                                class="fa-solid fa-chevron-down absolute right-4 top-4.75 text-gray-400 text-xs">
                                            </i>

                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- ACTION -->
                            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-100">
                                <button id="submit-button-edit-manage-user" type="button"
                                    class="h-12 w-full sm:w-auto px-7 rounded-2xl bg-linear-to-r from-orange-500 to-amber-500
                                    hover:from-orange-600 hover:to-amber-600
                                    text-white text-sm font-semibold transition-all shadow-lg hover:shadow-xl cursor-pointer disabled:cursor-default">

                                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <form method="dialog" class="modal-backdrop">
                    <button>Close</button>
                </form>
            </dialog>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/administrator/office-management/manage-user.js') }}"></script>

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/show-password-input.js') }}"></script> <!--- show password input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/office-account-management/office-account-management-listener.js') }}"></script> <!--- pusher listener manage user ---->