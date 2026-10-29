@php
    $studentProfile = Auth::user()?->StudentProfile;
    $schoolPartner = $studentProfile?->SchoolPartner;
    $schoolName = $schoolPartner?->nama_sekolah ?? 'sekolah';
    $schoolId = $schoolPartner?->id ?? 0;
    $schoolLogo = $schoolPartner?->logo;
    $profileName = $studentProfile?->nama_lengkap ?? Auth::user()?->name ?? 'Siswa';
@endphp
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72">

        <!-- HEADER PUTIH (MAIN LOGO) -->
        <div class="h-40 bg-white flex flex-col items-center justify-center border-b text-gray-500 relative group">

            <!-- LOGO WRAPPER -->
            <div id="logo-wrapper" class="flex items-center justify-center overflow-hidden">

                @if(!empty($schoolLogo))
                    <img id="school-logo-preview" src="{{ asset($schoolLogo) }}" class="h-26 object-cover">
                @else
                    <i class="fa-solid fa-school text-4xl mb-2"></i>
                @endif
            </div>

            <!-- TEXT -->
            @if(empty($schoolLogo))
                <span class="text-center text-sm">
                    Logo sekolah tidak tersedia.
                </span>
            @endif
        </div>

        <div class="flex-1 bg-[#0071BC] text-white flex flex-col">
            <ul class="mt-14 space-y-4 px-2">
                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.dashboard', [
                        'role'       => Auth::user()->role,
                        'schoolName' => $schoolName,
                        'schoolId'   => $schoolId
                    ]) }}" class="flex items-center gap-3 px-4 py-3 text-md hover:bg-[#FFFFFF26] rounded-lg transition">
                        <i class="fa-solid fa-gauge"></i>
                        <span> Beranda </span>
                    </a>
                </li>
                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-md hover:bg-[#FFFFFF26] rounded-lg transition">
                        <i class="fa-solid fa-school-flag"></i>
                        <span>LMS</span>
                    </a>
                </li>

                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.tka-tryout-period.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-md hover:bg-[#FFFFFF26] rounded-lg transition">
                        <i class="fa-solid fa-stopwatch"></i>
                        <span>Tryout Tka</span>
                    </a>
                </li>
                
                <!-- Menu Library -->
                <!-- LIBRARY -->
                <li class="list-item">
                    <div class="flex flex-col">

                        @php
                            $isLibraryActive =
                                request()->routeIs('teacher.library') ||
                                request()->routeIs('teacher.library.ppt') ||
                                request()->routeIs('teacher.library.lks') ||
                                request()->routeIs('teacher.library.video') ||
                                request()->routeIs('student.tka-subject-list.view');
                        @endphp

                        <!-- MAIN MENU -->
                        <a href="{{ route('teacher.library') }}"
                        class="{{ $isLibraryActive
                                ? 'cursor-pointer flex items-center justify-between px-4 py-3 bg-[#FFFFFF26] rounded-lg transition'
                                : 'cursor-pointer flex items-center justify-between px-4 py-3 hover:bg-[#FFFFFF26] rounded-lg transition' }}">

                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-book"></i>
                                <span>Library</span>
                            </div>

                            <i class="fa-solid fa-chevron-down text-xs
                                {{ $isLibraryActive ? 'rotate-180' : '' }}"></i>
                        </a>

                        <!-- DROPDOWN -->
                        <div class="{{ $isLibraryActive
                                ? 'ml-7 mt-1 flex flex-col gap-1'
                                : 'hidden ml-7 mt-1 flex flex-col gap-1' }}">

                            <a href="{{ route('teacher.library.ppt') }}"
                            class="{{ request()->routeIs('teacher.library.ppt')
                                    ? 'px-3 py-2 text-sm rounded-md bg-[#FFFFFF26]'
                                    : 'px-3 py-2 text-sm rounded-md hover:bg-[#FFFFFF26]' }}">
                                Power Point
                            </a>

                            <a href="{{ route('teacher.library.lks') }}"
                            class="{{ request()->routeIs('teacher.library.lks')
                                    ? 'px-3 py-2 text-sm rounded-md bg-[#FFFFFF26]'
                                    : 'px-3 py-2 text-sm rounded-md hover:bg-[#FFFFFF26]' }}">
                                LKPD
                            </a>

                            <a href="{{ route('teacher.library.video') }}"
                            class="{{ request()->routeIs('teacher.library.video')
                                    ? 'px-3 py-2 text-sm rounded-md bg-[#FFFFFF26]'
                                    : 'px-3 py-2 text-sm rounded-md hover:bg-[#FFFFFF26]' }}">
                                Video
                            </a>

                            <a href="{{ route('student.tka-subject-list.view', [
                                    'role' => Auth::user()->role,
                                ]) }}"
                            class="{{ request()->routeIs('student.tka-subject-list.view')
                                    ? 'px-3 py-2 text-sm rounded-md bg-[#FFFFFF26]'
                                    : 'px-3 py-2 text-sm rounded-md hover:bg-[#FFFFFF26]' }}">
                                Simulasi Soal TKA
                            </a>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- FOOTER -->
            <div class="mt-auto">
                <hr class="border-white border opacity-60 mb-10 mx-6">

                <div class="pb-16 -ml-2 flex justify-center">
                    <div class="flex flex-col w-max">
                        <span class="text-[13px] mb-3">
                            Powered By:
                        </span>
                        <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-12 object-contain">
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="relative left-72.5 w-[calc(100%-290px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <div class="w-full h-16 sm:h-18 bg-[#0071BC] shadow-sm flex items-center justify-between px-6 sm:px-8">
                <header class="text-[20px] font-bold flex items-center gap-3.5">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}">
                            @if (isset($backButton))
                                <div class="flex items-center gap-2">
                                    <button class="font-bold text-xl cursor-pointer text-white">{!! $backButton !!}</button>
                                    <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                                </div>
                            @endif
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                <div class="list-item-button-profile m-2 z-40">
                    <div class="dropdown-menu hidden lg:block">
                        <div class="flex items-center gap-3.5 relative">
                            <div class="flex flex-col">
                                <span class="text-[12px] text-white font-semibold leading-6">
                                    {{ Str::limit($profileName ?? '', 20) }}
                                </span>
                                <span class="text-[11px] text-white font-semibold leading-6">
                                    {{ Str::limit(Auth::user()->role ?? '', 20) }}
                                </span>
                            </div>
                            <i class="fas fa-circle-user text-4xl text-white opacity-85 toggle-menu-button-profile cursor-pointer"></i>

                            <div class="content-dropdown-button-profile absolute right-0 top-full mt-2 bg-white border border-gray-200 shadow-lg w-55 rounded-lg">
                                <a href="{{ route('lms.student.dashboard', [
                                    'role'       => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId'   => $schoolId
                                ]) }}">
                                    <div class="flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-solid fa-house"></i> Beranda
                                    </div>
                                </a>
                                <a href="{{ route('profile-account-school-partner.view', [
                                    'role'       => Auth::user()->role,
                                    'schoolName' => $schoolName,
                                    'schoolId'   => $schoolId
                                ]) }}">
                                    <div class="flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-regular fa-circle-user text-base"></i> Profil Akun
                                    </div>
                                </a>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="w-full flex items-center pl-3.5 py-3 gap-2 text-[13px] hover:bg-gray-100 cursor-pointer">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-base"></i> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dropdown Profile (Mobile Version Inside Desktop Wrapper?) --}}
                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-white"></i>
                        </div>
                        <div class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('lms.student.dashboard', [
                                'role'       => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId'   => $schoolId
                            ]) }}">
                                <div class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i> Beranda
                                </div>
                            </a>
                            <a href="{{ route('profile-account-school-partner.view', [
                                'role'       => Auth::user()->role,
                                'schoolName' => $schoolName,
                                'schoolId'   => $schoolId
                            ]) }}">
                                <div class="flex items-center pl-2 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                    <i class="fa-regular fa-circle-user text-base"></i> Profil Akun
                                </div>
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-base ml-0.75"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
    </div>

    <nav class="navbar-beranda-phone w-full h-20 flex justify-between items-center md:hidden bg-white shadow-lg px-6">
        <div class="flex items-center h-full">
            <label for="my-drawer-1">
                <i class="fas fa-bars text-2xl relative top-1 cursor-pointer"></i>
            </label>
            <a href="{{ route('lms.student.dashboard', [
                'role'       => Auth::user()->role,
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
            ]) }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="" class="w-30 ml-4">
            </a>
        </div>
        <div class="flex items-center gap-8 text-2xl relative top-1 z-40">
            <div class="list-item-button-profile relative md:hidden">
                <div class="dropdown-menu">
                    <div class="toggle-menu-button-profile cursor-pointer">
                        <i class="fas fa-circle-user text-4xl text-[#0071BC] font-bold"></i>
                    </div>
                    <div class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('lms.student.dashboard', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}">
                            <div class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i> Beranda
                            </div>
                        </a>
                        <a href="{{ route('profile-account-school-partner.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}">
                            <div class="flex items-center pl-2 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                <i class="fa-regular fa-circle-user text-base"></i> Profil Akun
                            </div>
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-base ml-0.75"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="drawer md:hidden z-9999">
        <input id="my-drawer-1" type="checkbox" class="drawer-toggle"/>

        <div class="drawer-side">
            <label for="my-drawer-1" class="drawer-overlay"></label>

            <div class="bg-gray-50 min-h-full min-w-[65vw] flex flex-col">
                <header class="h-20 px-4 bg-[#0071BC] flex items-center justify-between shadow-sm">
                    <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" class="h-9 object-contain" alt="Belajar Cerdas">
                    <label for="my-drawer-1">
                        <i class="fas fa-xmark text-2xl text-white cursor-pointer"></i>
                    </label>
                </header>

                <!-- CARD SEKOLAH -->
                <div class="relative flex flex-col items-center">

                    <div class="mx-4 mt-4 p-4 flex flex-col items-center">
                        <!-- LOGO -->
                        @if(!empty($schoolLogo))
                            <img src="{{ asset($schoolLogo) }}"
                                class="w-32 h-20 rounded-full object-contain bg-white" alt="Logo Sekolah">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200 mb-2">
                                <i class="fa-solid fa-school text-3xl text-gray-400"></i>
                            </div>

                            <span class="text-center text-sm">
                                Logo sekolah tidak tersedia.
                            </span>
                        @endif
                    </div>

                </div>

                <div class="border border-gray-200"></div>

                <div class="flex items-center gap-3 px-4 py-4 mt-2">
                    <i class="fas fa-circle-user text-3xl text-gray-400"></i>
                    <div>
                        <p class="text-sm font-semibold leading-tight">
                            {{ Str::limit($profileName ?? '', 20) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ Auth::user()->role }}
                        </p>
                    </div>
                </div>

                <div class="border border-gray-200"></div>

                <!-- MENU -->
                <ul class="px-3 py-2 space-y-1 text-sm mt-6 flex flex-col gap-4">
                    <li class="list-menu-sidebar-mobile-student">
                        <a href="{{ route('lms.student.dashboard', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-md hover:bg-gray-200 transition">
                            <i class="fa-solid fa-home"></i>
                            <span>Beranda</span>
                        </a>
                    </li>

                    <li class="list-menu-sidebar-mobile-student">
                        <a href="{{ route('lms.student.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-md hover:bg-gray-200 transition">
                            <i class="fa-solid fa-school-flag"></i>
                            <span>LMS</span>
                        </a>
                    </li>

                    <li class="list-menu-sidebar-mobile-student">
                        <a href="{{ route('lms.student.tka-tryout-period.view', [
                            'role'       => Auth::user()->role,
                            'schoolName' => $schoolName,
                            'schoolId'   => $schoolId
                        ]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-md hover:bg-gray-200 transition">
                            <i class="fa-solid fa-stopwatch"></i>
                            <span>Tryout TKA</span>
                        </a>
                    </li>

                    <!-- LIBRARY -->
                    @php
                        $isLibraryActive =
                            request()->routeIs('student.library') ||
                            request()->routeIs('teacher.library') ||
                            request()->routeIs('teacher.library.ppt') ||
                            request()->routeIs('teacher.library.lks') ||
                            request()->routeIs('teacher.library.video') ||
                            request()->routeIs('student.tka-subject-list.view');
                    @endphp

                    <li class="list-menu-sidebar-mobile-student">
                        <div class="flex flex-col">

                            <!-- MAIN MENU LIBRARY -->
                            <a href="{{ route('student.library') }}"
                            class="flex items-center justify-between gap-3 px-4 py-3 text-md hover:bg-gray-200 transition">

                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-book"></i>
                                    <span>Library</span>
                                </div>

                                <i class="fa-solid fa-chevron-down text-xs {{ $isLibraryActive ? 'rotate-180' : '' }}"></i>
                            </a>

                            <!-- DROPDOWN LIBRARY -->
                            <div class="{{ $isLibraryActive
                                    ? 'ml-7 mt-1 flex flex-col gap-1'
                                    : 'hidden ml-7 mt-1 flex flex-col gap-1' }}">

                                <a href="{{ route('teacher.library.ppt') }}"
                                class="{{ request()->routeIs('teacher.library.ppt')
                                        ? 'px-3 py-2 text-sm rounded-md bg-[#0071BC] text-white'
                                        : 'px-3 py-2 text-sm rounded-md hover:bg-gray-200' }}">
                                    Power Point
                                </a>

                                <a href="{{ route('teacher.library.lks') }}"
                                class="{{ request()->routeIs('teacher.library.lks')
                                        ? 'px-3 py-2 text-sm rounded-md bg-[#0071BC] text-white'
                                        : 'px-3 py-2 text-sm rounded-md hover:bg-gray-200' }}">
                                    LKPD
                                </a>

                                <a href="{{ route('teacher.library.video') }}"
                                class="{{ request()->routeIs('teacher.library.video')
                                        ? 'px-3 py-2 text-sm rounded-md bg-[#0071BC] text-white'
                                        : 'px-3 py-2 text-sm rounded-md hover:bg-gray-200' }}">
                                    Video
                                </a>

                                <a href="{{ route('student.tka-subject-list.view', [
                                    'role' => Auth::user()->role
                                ]) }}"
                                class="{{ request()->routeIs('student.tka-subject-list.view')
                                        ? 'px-3 py-2 text-sm rounded-md bg-[#0071BC] text-white'
                                        : 'px-3 py-2 text-sm rounded-md hover:bg-gray-200' }}">
                                    Simulasi Soal TKA
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>

            <div class="p-4 border-t border-gray-300">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button
                        class="w-full flex items-center justify-center py-3 bg-red-300 rounded-full gap-2 cursor-pointer transition-all duration-300 hover:bg-red-400 focus:ring-2 focus:ring-red-400 active:scale-95 text-slate-900 font-medium">
                        <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                        Keluar
                    </button>
                </form>
            </div>

            </div>
        </div>
    </div>

