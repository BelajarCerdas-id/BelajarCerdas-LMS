@include('components/sidebar-beranda', ['headerSideNav' => 'Profil Akun']);

@if (Auth::user()->StudentProfile)
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!--- alert success --->
            <div id="alert-success-update-personal-information"></div>

            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-schoolId="{{ $schoolId }}" data-user-id="{{ Auth::user()->id }}">
                <section class="flex flex-col lg:flex-row gap-14">

                    <!--- left profil --->
                    <div class="bg-white w-full lg:w-125 h-max lg:h-156.75 shadow-lg rounded-lg py-10 border-2 border-gray-200">

                        <!--- image user --->
                        <div class="flex justify-center">
                            <i class="fas fa-user-circle text-6xl pb-4"></i>
                        </div>

                        <!--- name & role --->
                        <div class="flex flex-col items-center">
                            <span class="p-px px-2 bg-[#4189e0] text-white text-sm">{{ Auth::user()->role }}</span>
                        </div>

                        <!--- pengaturan akun --->
                        <div class="flex flex-col gap-4 jsutify-center pt-10 px-6">

                            <!--- label --->
                            <div class="flex items-center gap-2 text-[#4189e0] font-bold">
                                <i class="fa-solid fa-user-gear text-xl"></i>
                                <span class="text-lg">Pengaturan Akun</span>
                            </div>

                            <!--- items --->
                            <ul class="lsit-style-none">
                                <li class="text-sm pl-8 pr-2">
                                    <a href="{{ route('profile-account-school-partner.reset-password.view', [
                                        'role' => $role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId,
                                    ]) }}" class="flex justify-between w-full">
                                        Atur Ulang Kata Sandi
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!--- right profil --->
                    <div class="w-full flex flex-col gap-4">

                        <!--- Persnal Information --->
                        <div>
                            <div class="flex justify-between">
                                <span class="font-bold text-2xl opacity-60">Informasi Pribadi</span>
                                <div onclick="my_modal_1.showModal()" class="flex gap-2 items-center cursor-pointer text-[#4189e0] font-bold">
                                    <span>Edit</span>
                                    <i class="fas fa-pen"></i>
                                </div>
                            </div>

                            <div class="bg-white h-full shadow-lg rounded-lg px-6 py-6 md:py-2 border-2 border-gray-200">
                                <div class="grid grid-cols-2 gap-4 mt-4">

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Nama Lengkap</label>
                                        <div id="view-nama-lengkap"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->StudentProfile->nama_lengkap ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">No. HP</label>
                                        <div id="view-no-hp"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->no_hp ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Email</label>
                                        <div id="view-personal-email"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->StudentProfile->personal_email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!--- Pendidikan --->
                        <div class="mt-12">
                            <div class="flex justify-between">
                                <span class="font-bold text-2xl opacity-60">Pendidikan</span>
                            </div>

                            <div class="bg-white h-full shadow-lg rounded-lg px-6 py-2 border-2 border-gray-200">
                                <div class="grid grid-cols-2 gap-4 mt-4">

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Sekolah</label>
                                        <div id="view-school-name"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->StudentProfile->SchoolPartner->nama_sekolah ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Kelas</label>
                                        <div id="view-class-name"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ $studentSchoolClass->SchoolClass->class_name ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">NISN</label>
                                        <div id="view-class-name"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->StudentProfile->nisn ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!--- modal edit personal information --->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max focus:outline-none" tabindex="-1">

                    <!---- Form edit personal information ---->
                    <form id="update-personal-information-form" data-url="{{ route('profile-account-school-partner.personal-information-student.update', [
                        'role' => $role,
                        'schoolName' => $schoolName,
                        'schoolId' => $schoolId,
                        'userId' => Auth::user()->id
                    ]) }}">
                        <span class="text-xl font-bold flex justify-center">Informasi Pribadi</span>

                        <!---- Nama Lengkap ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                Nama Lengkap
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->StudentProfile->nama_lengkap ?? '' }}"
                                placeholder="Masukkan Nama Lengkap">
                            <span id="error-nama_lengkap" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- No.HP ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                No.HP
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="no_hp" name="no_hp"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->no_hp }}"
                                placeholder="Masukkan Nmmor HP">
                            <span id="error-no_hp" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- Email Pribadi ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                Email Pribadi
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="personal_email" name="personal_email"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->StudentProfile->personal_email }}"
                                placeholder="Masukkan Email Pribadi">
                            <span id="error-personal_email" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- button submit ---->
                        <div class="flex justify-end mt-8">
                            <button type="button" id="submit-button-update-personal-information"
                                class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all cursor-pointer disabled:cursor-default">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </div>
@elseif (Auth::user()->OfficeProfile)
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!--- alert success --->
            <div id="alert-success-update-personal-information"></div>

            <main>
                <section class="flex flex-col lg:flex-row gap-14">

                    <!--- left profil --->
                    <div class="bg-white w-full lg:w-125 h-max lg:h-156.75 shadow-lg rounded-lg py-10 border-2 border-gray-200">

                        <!--- image user --->
                        <div class="flex justify-center">
                            <i class="fas fa-user-circle text-6xl pb-4"></i>
                        </div>

                        <!--- name & role --->
                        <div class="flex flex-col items-center">
                            <span class="p-px px-2 bg-[#4189e0] text-white text-sm">{{ Auth::user()->role }}</span>
                        </div>

                        <!--- pengaturan akun --->
                        <div class="flex flex-col gap-4 jsutify-center pt-10 px-6">

                            <!--- label --->
                            <div class="flex items-center gap-2 text-[#4189e0] font-bold">
                                <i class="fa-solid fa-user-gear text-xl"></i>
                                <span class="text-lg">Pengaturan Akun</span>
                            </div>

                            <!--- items --->
                            <ul class="lsit-style-none">
                                <li class="text-sm pl-8 pr-2">
                                    <a href="{{ route('profile-account-office.reset-password.view', [
                                        'role' => $role,
                                    ]) }}" class="flex justify-between w-full">
                                        Atur Ulang Kata Sandi
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!--- right profil --->
                    <div class="w-full flex flex-col gap-4">

                        <!--- Persnal Information --->
                        <div>
                            <div class="flex justify-between">
                                <span class="font-bold text-2xl opacity-60">Informasi Pribadi</span>
                                <div onclick="my_modal_1.showModal()" class="flex gap-2 items-center cursor-pointer text-[#4189e0] font-bold">
                                    <span>Edit</span>
                                    <i class="fas fa-pen"></i>
                                </div>
                            </div>

                            <div class="bg-white h-full shadow-lg rounded-lg px-6 py-6 md:py-2 border-2 border-gray-200">
                                <div class="grid grid-cols-2 gap-4 mt-4">

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Nama Lengkap</label>
                                        <div id="view-nama-lengkap"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->OfficeProfile->nama_lengkap ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">No. HP</label>
                                        <div id="view-no-hp"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->no_hp ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!--- modal edit personal information --->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max focus:outline-none" tabindex="-1">

                    <!---- Form edit personal information ---->
                    <form id="update-personal-information-form" data-url="{{ route('profile-account-school-partner.personal-information-office.update', [
                        'role' => $role,
                        'userId' => Auth::user()->id
                    ]) }}">
                        <span class="text-xl font-bold flex justify-center">Informasi Pribadi</span>

                        <!---- Nama Lengkap ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                Nama Lengkap
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->OfficeProfile->nama_lengkap ?? '' }}"
                                placeholder="Masukkan Nama Lengkap">
                            <span id="error-nama_lengkap" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- No.HP ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                No.HP
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="no_hp" name="no_hp"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->no_hp }}"
                                placeholder="Masukkan Nmmor HP">
                            <span id="error-no_hp" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- button submit ---->
                        <div class="flex justify-end mt-8">
                            <button type="button" id="submit-button-update-personal-information"
                                class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all cursor-pointer disabled:cursor-default">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </div>
@elseif (Auth::user()->SchoolStaffProfile)
    <div class="relative left-0 w-full {{ Auth::user()->role === 'Admin Sekolah' ? 'md:left-62.5 md:w-[calc(100%-250px)]' : 'md:left-72.5 md:w-[calc(100%-290px)]' }} 
        transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!--- alert success --->
            <div id="alert-success-update-personal-information"></div>

            <main>
                <section class="flex flex-col lg:flex-row gap-14">

                    <!--- left profil --->
                    <div class="bg-white w-full lg:w-125 h-max lg:h-156.75 shadow-lg rounded-lg py-10 border-2 border-gray-200">

                        <!--- image user --->
                        <div class="flex justify-center">
                            <i class="fas fa-user-circle text-6xl pb-4"></i>
                        </div>

                        <!--- name & role --->
                        <div class="flex flex-col items-center">
                            <span class="p-px px-2 bg-[#4189e0] text-white text-sm">{{ Auth::user()->role }}</span>
                        </div>

                        <!--- pengaturan akun --->
                        <div class="flex flex-col gap-4 jsutify-center pt-10 px-6">

                            <!--- label --->
                            <div class="flex items-center gap-2 text-[#4189e0] font-bold">
                                <i class="fa-solid fa-user-gear text-xl"></i>
                                <span class="text-lg">Pengaturan Akun</span>
                            </div>

                            <!--- items --->
                            <ul class="lsit-style-none">
                                <li class="text-sm pl-8 pr-2">
                                    <a href="{{ route('profile-account-school-partner.reset-password.view', [
                                        'role' => $role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId,
                                    ]) }}" class="flex justify-between w-full">
                                        Atur Ulang Kata Sandi
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!--- right profil --->
                    <div class="w-full flex flex-col gap-4">

                        <!--- Personal Information --->
                        <div>
                            <div class="flex justify-between">
                                <span class="font-bold text-2xl opacity-60">Informasi Pribadi</span>
                                <div onclick="my_modal_1.showModal()"
                                    class="flex gap-2 items-center cursor-pointer text-[#4189e0] font-bold">
                                    <span>Edit</span>
                                    <i class="fas fa-pen"></i>
                                </div>
                            </div>

                            <div class="bg-white h-full shadow-lg rounded-lg px-6 py-6 border-2 border-gray-200">
                                <div class="grid grid-cols-2 gap-4 mt-4">

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Nama Lengkap</label>
                                        <div id="view-nama-lengkap"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->SchoolStaffProfile->nama_lengkap ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">No. HP</label>
                                        <div id="view-no-hp"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->no_hp ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Email</label>
                                        <div id="view-personal-email"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->SchoolStaffProfile->personal_email ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">NIK</label>
                                        <div id="view-nik"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->SchoolStaffProfile->nik ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!--- modal edit personal information --->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max focus:outline-none" tabindex="-1">

                    <!---- Form edit personal information ---->
                    <form id="update-personal-information-form" data-url="{{ route('profile-account-school-partner.personal-information-schoolStaff.update', [
                        'role' => $role,
                        'schoolName' => $schoolName,
                        'schoolId' => $schoolId,
                        'userId' => Auth::user()->id
                    ]) }}">
                        <span class="text-xl font-bold flex justify-center">Informasi Pribadi</span>

                        <!---- Nama Lengkap ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                Nama Lengkap
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->SchoolStaffProfile->nama_lengkap ?? '' }}"
                                placeholder="Masukkan Nama Lengkap">
                            <span id="error-nama_lengkap" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- No.HP ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                No.HP
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="no_hp" name="no_hp"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->no_hp }}"
                                placeholder="Masukkan Nmmor HP">
                            <span id="error-no_hp" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- Email Pribadi ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                Email Pribadi
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="personal_email" name="personal_email"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->SchoolStaffProfile->personal_email }}"
                                placeholder="Masukkan Email Pribadi">
                            <span id="error-personal_email" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- NIK ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                NIK
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="nik" name="nik"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->SchoolStaffProfile->nik }}"
                                placeholder="Masukkan NIK">
                            <span id="error-nik" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- button submit ---->
                        <div class="flex justify-end mt-8">
                            <button type="button" id="submit-button-update-personal-information"
                                class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all cursor-pointer disabled:cursor-default">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </div>
@elseif (Auth::user()->ParentProfile)
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>
                <section class="flex flex-col lg:flex-row gap-14">

                    <!--- left profil --->
                    <div class="bg-white w-full lg:w-125 h-max lg:h-156.75 shadow-lg rounded-lg py-10 border-2 border-gray-200">

                        <!--- image user --->
                        <div class="flex justify-center">
                            <i class="fas fa-user-circle text-6xl pb-4"></i>
                        </div>

                        <!--- name & role --->
                        <div class="flex flex-col items-center">
                            <span class="p-px px-2 bg-[#4189e0] text-white text-sm">{{ Auth::user()->role }}</span>
                        </div>

                        <!--- pengaturan akun --->
                        <div class="flex flex-col gap-4 jsutify-center pt-10 px-6">

                            <!--- label --->
                            <div class="flex items-center gap-2 text-[#4189e0] font-bold">
                                <i class="fa-solid fa-user-gear text-xl"></i>
                                <span class="text-lg">Pengaturan Akun</span>
                            </div>

                            <!--- items --->
                            <ul class="lsit-style-none">
                                <li class="text-sm pl-8 pr-2">
                                    <a href="{{ route('profile-account-school-partner.reset-password.view', [
                                        'role' => $role,
                                        'schoolName' => $schoolName,
                                        'schoolId' => $schoolId,
                                    ]) }}" class="flex justify-between w-full">
                                        Atur Ulang Kata Sandi
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!--- right profil --->
                    <div class="w-full flex flex-col gap-4">

                        <!--- Persnal Information --->
                        <div>
                            <div class="flex justify-between">
                                <span class="font-bold text-2xl opacity-60">Informasi Pribadi</span>
                                <div onclick="my_modal_1.showModal()" class="flex gap-2 items-center cursor-pointer text-[#4189e0] font-bold">
                                    <span>Edit</span>
                                    <i class="fas fa-pen"></i>
                                </div>
                            </div>

                            <div class="bg-white h-full shadow-lg rounded-lg px-6 py-6 md:py-2 border-2 border-gray-200">
                                <div class="grid grid-cols-2 gap-4 mt-4">

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">Nama Lengkap</label>
                                        <div id="view-nama-lengkap"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->ParentProfile->nama_lengkap ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-500">No. HP</label>
                                        <div id="view-no-hp"
                                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-md px-3 py-3 text-sm">
                                            {{ Auth::user()->no_hp ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!--- modal edit personal information --->
            <dialog id="my_modal_1" class="modal">
                <div class="modal-box bg-white w-max focus:outline-none" tabindex="-1">

                    <!---- Form edit personal information ---->
                    <form id="update-personal-information-form" data-url="{{ route('profile-account-school-partner.personal-information-parent.update', [
                        'role' => $role,
                        'schoolName' => $schoolName,
                        'schoolId' => $schoolId,
                        'userId' => Auth::user()->id
                    ]) }}">
                        <span class="text-xl font-bold flex justify-center">Informasi Pribadi</span>

                        <!---- Nama Lengkap ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                Nama Lengkap
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->ParentProfile->nama_lengkap ?? '' }}"
                                placeholder="Masukkan Nama Lengkap">
                            <span id="error-nama_lengkap" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- No.HP ---->
                        <div class="mt-4 w-96 md:w-112.5">
                            <label class="text-sm">
                                No.HP
                                <sup class="text-red-500 top-0 text-lg">&#42;</sup>
                            </label>
                            <input type="text" id="no_hp" name="no_hp"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-white shadow-lg h-11 border-gray-200 border outline-none rounded-full text-xs px-2 mt-2"
                                value="{{ Auth::user()->no_hp }}"
                                placeholder="Masukkan Nmmor HP">
                            <span id="error-no_hp" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!---- button submit ---->
                        <div class="flex justify-end mt-8">
                            <button type="button" id="submit-button-update-personal-information"
                                class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all cursor-pointer disabled:cursor-default">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
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

<script src="{{ asset('assets/js/features/lms/profile-account/personal-information/update-personal-information.js') }}"></script> <!--- update personal information ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->